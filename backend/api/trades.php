<?php
/**
 * Trades API Endpoints
 */
require_once __DIR__ . '/../models/Trading.php';
require_once __DIR__ . '/../models/Wallet.php';

// Check authentication for all trade endpoints
$payload = authenticateUser();

if (!$payload) {
    sendUnauthorizedResponse('Authentication required');
}

// Get action from endpoint
$action = isset($endpoints[1]) ? $endpoints[1] : '';
$tradeId = isset($endpoints[2]) ? $endpoints[2] : null;

// Current user ID from token
$userId = $payload['uid'];

// Handle different trade actions
switch ($action) {
    case '':
    case 'list':
        handleGetTrades();
        break;
        
    case 'create':
        handleCreateTrade();
        break;
        
    case 'cancel':
        handleCancelTrade($tradeId);
        break;
        
    case 'stats':
        handleTradeStats();
        break;
        
    case 'portfolio':
        handlePortfolio();
        break;
        
    default:
        sendNotFoundResponse('Trade action not found');
}

/**
 * Handle get trades
 */
function handleGetTrades() {
    global $requestMethod, $userId;
    
    if ($requestMethod !== 'GET') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    // Get pagination parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Get filter parameters
    $filters = [];
    
    if (isset($_GET['trade_type'])) {
        $filters['tradeType'] = $_GET['trade_type'];
    }
    
    if (isset($_GET['crypto_currency'])) {
        $filters['cryptoCurrency'] = $_GET['crypto_currency'];
    }
    
    if (isset($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }
    
    $trades = Trading::getTradesByUserId($userId, $filters, $limit, $offset);
    
    sendSuccessResponse(['trades' => $trades]);
}

/**
 * Handle create trade
 */
function handleCreateTrade() {
    global $requestMethod, $userId;
    
    if ($requestMethod !== 'POST') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    $requestBody = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($requestBody['trade_type']) || !isset($requestBody['crypto_currency']) || 
        !isset($requestBody['amount']) || !isset($requestBody['price'])) {
        sendErrorResponse('Trade type, cryptocurrency, amount, and price are required', 400);
    }
    
    $tradeType = $requestBody['trade_type'];
    $cryptoCurrency = $requestBody['crypto_currency'];
    $amount = (float)$requestBody['amount'];
    $price = (float)$requestBody['price'];
    
    if ($amount <= 0) {
        sendErrorResponse('Amount must be positive', 400);
    }
    
    if ($price <= 0) {
        sendErrorResponse('Price must be positive', 400);
    }
    
    // Calculate total value
    $totalValue = $amount * $price;
    
    // Check if the user has sufficient balance
    if ($tradeType === 'buy') {
        // For buy orders, check USD balance
        $wallet = Wallet::getWallet($userId, 'USD');
        
        if (!$wallet || $wallet['balance'] < $totalValue) {
            sendErrorResponse('Insufficient USD balance', 400);
        }
    } else if ($tradeType === 'sell') {
        // For sell orders, check cryptocurrency balance
        $wallet = Wallet::getWallet($userId, $cryptoCurrency);
        
        if (!$wallet || $wallet['balance'] < $amount) {
            sendErrorResponse('Insufficient ' . $cryptoCurrency . ' balance', 400);
        }
    } else {
        sendErrorResponse('Invalid trade type', 400);
    }
    
    // Create trade data
    $tradeData = [
        'userId' => $userId,
        'tradeType' => $tradeType,
        'cryptoCurrency' => $cryptoCurrency,
        'amount' => $amount,
        'price' => $price,
        'totalValue' => $totalValue,
        'status' => 'pending'
    ];
    
    // Create the trade
    $tradeId = Trading::createTrade($tradeData);
    
    if (!$tradeId) {
        sendServerErrorResponse('Failed to create trade');
    }
    
    // Update wallet balances
    try {
        if ($tradeType === 'buy') {
            // Deduct USD and add cryptocurrency
            Wallet::updateBalance($userId, 'USD', -$totalValue);
            Wallet::updateBalance($userId, $cryptoCurrency, $amount);
        } else {
            // Deduct cryptocurrency and add USD
            Wallet::updateBalance($userId, $cryptoCurrency, -$amount);
            Wallet::updateBalance($userId, 'USD', $totalValue);
        }
        
        // Update trade status to completed
        Trading::updateTradeStatus($tradeId, 'completed');
    } catch (Exception $e) {
        // If something goes wrong, update trade status to failed
        Trading::updateTradeStatus($tradeId, 'failed');
        sendServerErrorResponse('Trade created but failed to update balances', ['error' => $e->getMessage()]);
    }
    
    // Get the created trade
    $trade = Trading::getTradeById($tradeId);
    
    sendSuccessResponse([
        'trade' => $trade,
        'trade_type' => $tradeType,
        'crypto_currency' => $cryptoCurrency,
        'amount' => $amount,
        'price' => $price,
        'total_value' => $totalValue
    ], 'Trade created successfully', 201);
}

/**
 * Handle cancel trade
 */
function handleCancelTrade($tradeId) {
    global $requestMethod, $userId;
    
    if ($requestMethod !== 'POST') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    if (!$tradeId) {
        sendErrorResponse('Trade ID is required', 400);
    }
    
    // Get the trade
    $trade = Trading::getTradeById($tradeId);
    
    if (!$trade) {
        sendNotFoundResponse('Trade not found');
    }
    
    // Check if the trade belongs to the user
    if ($trade['user_id'] != $userId) {
        sendForbiddenResponse('You can only cancel your own trades');
    }
    
    // Check if the trade is cancellable
    if ($trade['status'] !== 'pending') {
        sendErrorResponse('Only pending trades can be cancelled', 400);
    }
    
    // Update trade status to cancelled
    $success = Trading::updateTradeStatus($tradeId, 'cancelled');
    
    if (!$success) {
        sendErrorResponse('Failed to cancel trade', 400);
    }
    
    sendSuccessResponse(['trade_id' => $tradeId], 'Trade cancelled successfully');
}

/**
 * Handle trade stats
 */
function handleTradeStats() {
    global $requestMethod, $userId;
    
    if ($requestMethod !== 'GET') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    $stats = Trading::getUserTradeStats($userId);
    
    if (!$stats) {
        $stats = [
            'total_trades' => 0,
            'buy_trades' => 0,
            'sell_trades' => 0,
            'completed_trades' => 0,
            'pending_trades' => 0,
            'cancelled_trades' => 0
        ];
    }
    
    // Get total value of buy trades
    $totalBuyValue = Trading::getTotalTradeValue($userId, 'buy');
    
    // Get total value of sell trades
    $totalSellValue = Trading::getTotalTradeValue($userId, 'sell');
    
    // Calculate profit/loss
    $profitLoss = $totalSellValue - $totalBuyValue;
    
    // Add additional stats
    $stats['total_buy_value'] = $totalBuyValue;
    $stats['total_sell_value'] = $totalSellValue;
    $stats['profit_loss'] = $profitLoss;
    
    sendSuccessResponse(['stats' => $stats]);
}

/**
 * Handle portfolio
 */
function handlePortfolio() {
    global $requestMethod, $userId;
    
    if ($requestMethod !== 'GET') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    $portfolio = Trading::getUserPortfolio($userId);
    
    // Get total USD balance
    $usdWallet = Wallet::getWallet($userId, 'USD');
    $usdBalance = $usdWallet ? $usdWallet['balance'] : 0;
    
    // Get total value in USD equivalent
    $totalValue = Wallet::getTotalBalanceInUSD($userId);
    
    sendSuccessResponse([
        'portfolio' => $portfolio,
        'usd_balance' => $usdBalance,
        'total_value_usd' => $totalValue
    ]);
} 