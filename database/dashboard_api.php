<?php
/**
 * Dashboard API
 * 
 * This file provides the data for the dashboard UI shown in the image
 * with the 4 account summary values and 3 cryptocurrency balances
 */

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Database connection
$host = 'localhost';
$username = 'root';
$password = ''; // Default XAMPP has no password
$database = 'bitrader_db';

// Get user ID from request
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1; // Default to user 1

try {
    // Connect to database
    $db = new mysqli($host, $username, $password, $database);
    
    // Check connection
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
    
    // ========== Get account summary ==========
    $summaryQuery = "
        SELECT 
            FORMAT(total_balance, 2) AS total_balance,
            FORMAT(total_profit, 2) AS total_profit,
            FORMAT(total_deposit, 2) AS total_deposit,
            FORMAT(total_withdrawal, 2) AS total_withdrawal
        FROM 
            user_account_summary
        WHERE 
            user_id = ?
    ";
    
    $stmt = $db->prepare($summaryQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $summaryResult = $stmt->get_result();
    
    if ($summaryResult->num_rows === 0) {
        // If no summary exists, create a default one with zeros
        $defaultSummary = [
            'total_balance' => '0.00',
            'total_profit' => '0.00',
            'total_deposit' => '0.00',
            'total_withdrawal' => '0.00'
        ];
        $summary = $defaultSummary;
    } else {
        $summary = $summaryResult->fetch_assoc();
    }
    
    // ========== Get cryptocurrency balances ==========
    $cryptoQuery = "
        SELECT 
            cb.currency,
            cb.balance,
            CONCAT('$', FORMAT(cb.usd_value, 2)) AS usd_value,
            cp.trend
        FROM 
            user_crypto_balances cb
        JOIN 
            crypto_prices cp ON cb.currency = cp.currency
        WHERE 
            cb.user_id = ?
        ORDER BY 
            cb.currency
    ";
    
    $stmt = $db->prepare($cryptoQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $cryptoResult = $stmt->get_result();
    
    $cryptoBalances = [];
    
    // If no crypto balances exist, create defaults
    if ($cryptoResult->num_rows === 0) {
        $cryptoBalances = [
            'BTC' => [
                'balance' => 0,
                'usd_value' => '$0.00',
                'trend' => 'up'
            ],
            'DASH' => [
                'balance' => 0,
                'usd_value' => '$0.00',
                'trend' => 'up'
            ],
            'ETH' => [
                'balance' => 0,
                'usd_value' => '$0.00',
                'trend' => 'up'
            ]
        ];
    } else {
        while ($row = $cryptoResult->fetch_assoc()) {
            $cryptoBalances[$row['currency']] = [
                'balance' => $row['balance'],
                'usd_value' => $row['usd_value'],
                'trend' => $row['trend']
            ];
        }
    }
    
    // ========== Combine all data ==========
    $dashboardData = [
        'account_summary' => $summary,
        'crypto_balances' => $cryptoBalances
    ];
    
    // Output data as JSON
    echo json_encode([
        'success' => true,
        'data' => $dashboardData
    ]);
    
} catch (Exception $e) {
    // Output error as JSON
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 