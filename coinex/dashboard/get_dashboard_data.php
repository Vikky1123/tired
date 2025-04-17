<?php
/**
 * API to fetch dashboard data
 * Used by the dashboard page to get user-specific data
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');

// Include database utilities
require_once '../backend/utils/db_utils.php';
require_once '../backend/models/User.php';
require_once '../backend/models/Wallet.php';
require_once '../backend/models/Trading.php';

// Get user ID from the request
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

// If no user ID provided, return error
if (!$userId) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit;
}

try {
    // Get user data
    $userData = User::getById($userId);
    
    if (!$userData) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    // Get user wallets - updated to use wallets table instead of user_wallets
    $userWallets = Wallet::getUserWallets($userId);
    
    // Calculate total balance in USD - updated to use wallets table
    $totalBalanceUSD = Wallet::getTotalBalanceInUSD($userId);
    
    // Calculate total deposits - updated to use wallet_transactions table
    $totalDeposit = 0;
    $transactions = Wallet::getTransactionHistory($userId);
    foreach ($transactions as $transaction) {
        if ($transaction['transaction_type'] === 'deposit' && $transaction['status'] === 'completed') {
            $totalDeposit += $transaction['amount'];
        }
    }
    
    // Calculate total withdrawals - updated to use wallet_transactions table
    $totalWithdrawal = 0;
    foreach ($transactions as $transaction) {
        if ($transaction['transaction_type'] === 'withdraw' && $transaction['status'] === 'completed') {
            $totalWithdrawal += $transaction['amount'];
        }
    }
    
    // Calculate total profit (simplified version)
    $totalProfit = $totalBalanceUSD - $totalDeposit + $totalWithdrawal;
    if ($totalProfit < 0) $totalProfit = 0; // Ensure we don't show negative profit
    
    // Return the dashboard data
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $userData['id'],
            'username' => $userData['username'],
            'full_name' => $userData['full_name'],
            'email' => $userData['email']
        ],
        'wallets' => $userWallets,
        'total_balance_usd' => $totalBalanceUSD,
        'total_deposit' => $totalDeposit,
        'total_withdrawal' => $totalWithdrawal,
        'total_profit' => $totalProfit
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching dashboard data: ' . $e->getMessage()
    ]);
}
?> 