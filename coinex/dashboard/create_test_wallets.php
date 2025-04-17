<?php
/**
 * Create Test Wallets Script
 * This script creates test wallets for a user who logs in but doesn't have any wallet data yet
 * Run this script once after a user has registered and logged in
 */

// Define the path to user_session.php and backend
$projectRoot = $_SERVER['DOCUMENT_ROOT'] . '/PROJECT-BITRADER';
$backendDir = $projectRoot . '/backend';
$dashboardDir = $projectRoot . '/coinex/dashboard';

// Include database utilities and user session with absolute paths
require_once $backendDir . '/utils/db_utils.php';
require_once $dashboardDir . '/user_session.php';

// Get current user ID
$userId = getCurrentUserId();
if (!$userId) {
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit;
}

// Check if user already has wallets
$checkSql = "SELECT COUNT(*) as count FROM wallets WHERE user_id = ?";
$checkResult = executeQuery($checkSql, [$userId]);
$walletCount = ($checkResult[0]['count'] ?? 0);

if ($walletCount > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'User already has wallet data',
        'wallet_count' => $walletCount
    ]);
    exit;
}

// Create demo wallets for the user
$wallets = [
    ['USD', 1000.00],
    ['BTC', 0.015],
    ['ETH', 0.25]
];

$conn = getConnection();
$conn->begin_transaction();

try {
    $insertSql = "INSERT INTO wallets (user_id, currency, balance, created_at, updated_at) 
                 VALUES (?, ?, ?, NOW(), NOW())";
    
    $stmt = $conn->prepare($insertSql);
    
    foreach ($wallets as $wallet) {
        $stmt->bind_param("isd", $userId, $wallet[0], $wallet[1]);
        $stmt->execute();
    }
    
    // Create a sample deposit transaction
    $depositSql = "INSERT INTO wallet_transactions 
                  (from_user_id, from_currency, to_user_id, to_currency, amount, destination_amount, 
                   exchange_rate, transaction_type, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $depositStmt = $conn->prepare($depositSql);
    
    $fromUserId = $userId;
    $fromCurrency = 'USD';
    $toUserId = $userId;
    $toCurrency = 'USD';
    $amount = 1000.00;
    $destinationAmount = 1000.00;
    $exchangeRate = 1.00;
    $transactionType = 'deposit';
    $status = 'completed';
    
    $depositStmt->bind_param("ississsss", 
        $fromUserId, $fromCurrency, $toUserId, $toCurrency, 
        $amount, $destinationAmount, $exchangeRate, $transactionType, $status
    );
    
    $depositStmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Test wallets created successfully',
        'wallets' => $wallets
    ]);
} catch (Exception $e) {
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error creating test wallets: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 