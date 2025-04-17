<?php
/**
 * User Session Management
 * Contains functions to handle user sessions and retrieve user data
 */

// Suppress all PHP errors from being displayed to the user
error_reporting(0);
ini_set('display_errors', 0);

// Define project paths
$projectRoot = $_SERVER['DOCUMENT_ROOT'] . '/PROJECT-BITRADER';
$backendDir = $projectRoot . '/backend';
$utilsDir = $backendDir . '/utils';

// Include necessary utilities
require_once($utilsDir . '/db_utils.php');
require_once($utilsDir . '/response_utils.php');
require_once($utilsDir . '/auth_utils.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get the current user ID from session or JWT
 */
function getCurrentUserId() {
    // First check the session
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    
    // Then check for JWT in cookie or Authorization header
    $token = null;
    
    // Check for token in cookie
    if (isset($_COOKIE['auth_token'])) {
        $token = $_COOKIE['auth_token'];
    }
    
    // Check for token in Authorization header
    if (!$token && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        if (strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
        }
    }
    
    // Validate token if found
    if ($token) {
        try {
            $payload = validateJwtToken($token);
            if ($payload && isset($payload['user_id'])) {
                // Store in session for future requests
                $_SESSION['user_id'] = $payload['user_id'];
                return $payload['user_id'];
            }
        } catch (Exception $e) {
            // Token validation failed
            return null;
        }
    }
    
    return null;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    $userId = getCurrentUserId();
    if (!$userId) {
        return null;
    }
    
    // Get user data from database
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    $userData = $result->fetch_assoc();
    $stmt->close();
    $db->close();
    
    // Remove sensitive data
    unset($userData['password']);
    
    return $userData;
}

/**
 * Get user financial data
 */
function getUserFinancialData($userId) {
    if (!$userId) {
        return [
            'total_balance_usd' => 0,
            'total_deposit' => 0,
            'total_withdrawal' => 0,
            'total_profit' => 0
        ];
    }
    
    $db = getDbConnection();
    
    // Get wallet balance
    $stmt = $db->prepare("SELECT SUM(w.balance * CASE WHEN w.currency = 'USD' THEN 1 ELSE (SELECT price FROM exchange_rates WHERE currency = w.currency ORDER BY updated_at DESC LIMIT 1) END) as total_balance FROM wallets w WHERE w.user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $balanceRow = $result->fetch_assoc();
    $totalBalance = $balanceRow['total_balance'] ?: 0;
    $stmt->close();
    
    // Get total deposits
    $stmt = $db->prepare("SELECT SUM(amount) as total_deposit FROM wallet_transactions WHERE to_user_id = ? AND transaction_type = 'deposit' AND status = 'completed'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $depositRow = $result->fetch_assoc();
    $totalDeposit = $depositRow['total_deposit'] ?: 0;
    $stmt->close();
    
    // Get total withdrawals
    $stmt = $db->prepare("SELECT SUM(amount) as total_withdrawal FROM wallet_transactions WHERE from_user_id = ? AND transaction_type = 'withdraw' AND status = 'completed'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $withdrawalRow = $result->fetch_assoc();
    $totalWithdrawal = $withdrawalRow['total_withdrawal'] ?: 0;
    $stmt->close();
    
    // Calculate profit (simplified version)
    $totalProfit = $totalBalance - $totalDeposit + $totalWithdrawal;
    if ($totalProfit < 0) $totalProfit = 0; // Ensure profit is not negative
    
    $db->close();
    
    return [
        'total_balance_usd' => $totalBalance,
        'total_deposit' => $totalDeposit,
        'total_withdrawal' => $totalWithdrawal,
        'total_profit' => $totalProfit
    ];
}
?> 