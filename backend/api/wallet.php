<?php
/**
 * Wallet API Endpoints
 */
require_once __DIR__ . '/../models/Wallet.php';

// Check authentication for all wallet endpoints
$payload = authenticateUser();

if (!$payload) {
    sendUnauthorizedResponse('Authentication required');
}

// Get action from endpoint
$action = isset($endpoints[1]) ? $endpoints[1] : '';

// Current user ID from token
$userId = $payload['uid'];

// Handle different wallet actions
switch ($action) {
    case '':
    case 'list':
        handleGetWallets();
        break;
        
    case 'balance':
        handleGetBalance();
        break;
        
    case 'deposit':
        handleDeposit();
        break;
        
    case 'withdraw':
        handleWithdraw();
        break;
        
    case 'transfer':
        handleTransfer();
        break;
        
    case 'exchange':
        handleExchange();
        break;
        
    case 'transactions':
        handleGetTransactions();
        break;
        
    default:
        sendNotFoundResponse('Wallet action not found');
}

/**
 * Handle get wallets
 */
function handleGetWallets() {
    global $requestMethod, $userId;
    
    if ($requestMethod !== 'GET') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    $wallets = Wallet::getUserWallets($userId);
    
    sendSuccessResponse(['wallets' => $wallets]);
}

/**
 * Handle get balance
 */
function handleGetBalance() {
    global $requestMethod, $userId;
    
    if ($requestMethod !== 'GET') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    // Get currency from query parameter
    $currency = isset($_GET['currency']) ? $_GET['currency'] : null;
    
    if ($currency) {
        // Get specific wallet
        $wallet = Wallet::getWallet($userId, $currency);
        
        if (!$wallet) {
            sendNotFoundResponse('Wallet not found');
        }
        
        sendSuccessResponse(['wallet' => $wallet]);
    } else {
        // Get total balance in USD equivalent
        $totalBalance = Wallet::getTotalBalanceInUSD($userId);
        
        sendSuccessResponse(['total_balance_usd' => $totalBalance]);
    }
}

/**
 * Handle deposit
 */
function handleDeposit() {
    global $requestMethod, $userId, $payload;
    
    if ($requestMethod !== 'POST') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    $requestBody = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($requestBody['currency']) || !isset($requestBody['amount'])) {
        sendErrorResponse('Currency and amount are required', 400);
    }
    
    $currency = $requestBody['currency'];
    $amount = (float)$requestBody['amount'];
    
    if ($amount <= 0) {
        sendErrorResponse('Amount must be positive', 400);
    }
    
    // Allow deposits only for specific currencies or by admin users
    $allowedCurrencies = ['USD', 'EUR', 'GBP'];
    if (!in_array($currency, $allowedCurrencies) && $payload['role'] !== 'admin') {
        sendErrorResponse('Direct deposits are only allowed for fiat currencies', 400);
    }
    
    $success = Wallet::updateBalance($userId, $currency, $amount);
    
    if (!$success) {
        sendErrorResponse('Failed to deposit funds', 400);
    }
    
    $wallet = Wallet::getWallet($userId, $currency);
    
    sendSuccessResponse([
        'wallet' => $wallet,
        'amount' => $amount,
        'transaction_type' => 'deposit'
    ], 'Deposit successful');
}

/**
 * Handle withdraw
 */
function handleWithdraw() {
    global $requestMethod, $userId, $payload;
    
    if ($requestMethod !== 'POST') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    $requestBody = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($requestBody['currency']) || !isset($requestBody['amount'])) {
        sendErrorResponse('Currency and amount are required', 400);
    }
    
    $currency = $requestBody['currency'];
    $amount = (float)$requestBody['amount'];
    
    if ($amount <= 0) {
        sendErrorResponse('Amount must be positive', 400);
    }
    
    // Allow withdrawals only for specific currencies or by admin users
    $allowedCurrencies = ['USD', 'EUR', 'GBP'];
    if (!in_array($currency, $allowedCurrencies) && $payload['role'] !== 'admin') {
        sendErrorResponse('Direct withdrawals are only allowed for fiat currencies', 400);
    }
    
    // Check if user has sufficient balance
    $wallet = Wallet::getWallet($userId, $currency);
    
    if (!$wallet || $wallet['balance'] < $amount) {
        sendErrorResponse('Insufficient funds', 400);
    }
    
    $success = Wallet::updateBalance($userId, $currency, -$amount);
    
    if (!$success) {
        sendErrorResponse('Failed to withdraw funds', 400);
    }
    
    $wallet = Wallet::getWallet($userId, $currency);
    
    sendSuccessResponse([
        'wallet' => $wallet,
        'amount' => $amount,
        'transaction_type' => 'withdraw'
    ], 'Withdrawal successful');
}

/**
 * Handle transfer
 */
function handleTransfer() {
    global $requestMethod, $userId;
    
    if ($requestMethod !== 'POST') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    $requestBody = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($requestBody['to_user']) || !isset($requestBody['currency']) || !isset($requestBody['amount'])) {
        sendErrorResponse('Recipient, currency, and amount are required', 400);
    }
    
    $toUser = $requestBody['to_user'];
    $currency = $requestBody['currency'];
    $amount = (float)$requestBody['amount'];
    
    if ($amount <= 0) {
        sendErrorResponse('Amount must be positive', 400);
    }
    
    // Check if recipient exists
    $recipient = User::getByUsername($toUser);
    
    if (!$recipient) {
        sendErrorResponse('Recipient not found', 404);
    }
    
    $recipientId = $recipient['id'];
    
    // Check if sender has sufficient balance
    $wallet = Wallet::getWallet($userId, $currency);
    
    if (!$wallet || $wallet['balance'] < $amount) {
        sendErrorResponse('Insufficient funds', 400);
    }
    
    $success = Wallet::transferFunds($userId, $currency, $recipientId, $currency, $amount);
    
    if (!$success) {
        sendErrorResponse('Failed to transfer funds', 400);
    }
    
    $wallet = Wallet::getWallet($userId, $currency);
    
    sendSuccessResponse([
        'wallet' => $wallet,
        'amount' => $amount,
        'recipient' => $toUser,
        'transaction_type' => 'transfer'
    ], 'Transfer successful');
}

/**
 * Handle exchange
 */
function handleExchange() {
    global $requestMethod, $userId;
    
    if ($requestMethod !== 'POST') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    $requestBody = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($requestBody['from_currency']) || !isset($requestBody['to_currency']) || !isset($requestBody['amount'])) {
        sendErrorResponse('Source currency, destination currency, and amount are required', 400);
    }
    
    $fromCurrency = $requestBody['from_currency'];
    $toCurrency = $requestBody['to_currency'];
    $amount = (float)$requestBody['amount'];
    $exchangeRate = isset($requestBody['exchange_rate']) ? (float)$requestBody['exchange_rate'] : null;
    
    if ($amount <= 0) {
        sendErrorResponse('Amount must be positive', 400);
    }
    
    if ($fromCurrency === $toCurrency) {
        sendErrorResponse('Source and destination currencies must be different', 400);
    }
    
    // Check if user has sufficient balance
    $wallet = Wallet::getWallet($userId, $fromCurrency);
    
    if (!$wallet || $wallet['balance'] < $amount) {
        sendErrorResponse('Insufficient funds', 400);
    }
    
    // If exchange rate is not provided, get it from the database
    if (!$exchangeRate) {
        // In a real application, you would use a service to get the current exchange rate
        // For now, we'll use a dummy implementation
        $sql = "SELECT price FROM exchange_rates WHERE currency = ? ORDER BY updated_at DESC LIMIT 1";
        $result = executeQuery($sql, [$toCurrency]);
        
        if (empty($result)) {
            sendErrorResponse('Exchange rate not available', 400);
        }
        
        $exchangeRate = (float)$result[0]['price'];
    }
    
    $success = Wallet::transferFunds($userId, $fromCurrency, $userId, $toCurrency, $amount, $exchangeRate);
    
    if (!$success) {
        sendErrorResponse('Failed to exchange funds', 400);
    }
    
    $sourceWallet = Wallet::getWallet($userId, $fromCurrency);
    $destWallet = Wallet::getWallet($userId, $toCurrency);
    
    sendSuccessResponse([
        'source_wallet' => $sourceWallet,
        'destination_wallet' => $destWallet,
        'amount' => $amount,
        'exchange_rate' => $exchangeRate,
        'transaction_type' => 'exchange'
    ], 'Exchange successful');
}

/**
 * Handle get transactions
 */
function handleGetTransactions() {
    global $requestMethod, $userId;
    
    if ($requestMethod !== 'GET') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    // Get pagination parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    $transactions = Wallet::getTransactionHistory($userId, $limit, $offset);
    
    sendSuccessResponse(['transactions' => $transactions]);
} 