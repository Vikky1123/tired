<?php
/**
 * Populate Test Data
 * 
 * This script adds wallet data and transaction history for a test user
 * to demonstrate the dynamic functionality
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database utilities
require_once '../backend/utils/db_utils.php';
require_once '../backend/models/User.php';
require_once '../backend/models/Wallet.php';

echo "<h1>Adding Test Data for BitTrader Dashboard</h1>";

// Initialize demo data for user ID
$userId = null;

// Get token from localStorage via JavaScript
echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userDataStr = localStorage.getItem('userData');
        
        if (userDataStr) {
            try {
                const userData = JSON.parse(userDataStr);
                document.getElementById('user-id').value = userData.id || '';
                document.getElementById('submit-btn').click();
            } catch (error) {
                console.error('Error parsing user data:', error);
            }
        }
    });
</script>";

echo '<form method="post">';
echo '<input type="hidden" id="user-id" name="user_id" value="">';
echo '<p>This will add test wallet data for your account.</p>';
echo '<button type="submit" id="submit-btn">Add Test Data</button>';
echo '</form>';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = intval($_POST['user_id']);
    
    if ($userId > 0) {
        try {
            // Get user data to verify the user exists
            $userData = User::getById($userId);
            
            if (!$userData) {
                echo "<p>Error: User not found. Please log in first.</p>";
                echo "<p><a href='../bitrader.thetork.com/Signup-Signin/index.html'>Go to login page</a></p>";
                exit;
            }
            
            echo "<p>Adding test data for user: {$userData['username']} (ID: {$userId})</p>";
            
            // Add or update wallets with sample data
            $wallets = [
                ['currency' => 'BTC', 'balance' => 0.5],
                ['currency' => 'ETH', 'balance' => 3.2],
                ['currency' => 'USD', 'balance' => 2500],
                ['currency' => 'DASH', 'balance' => 10.5],
            ];
            
            foreach ($wallets as $wallet) {
                // Check if wallet exists
                $existingWallet = Wallet::getWallet($userId, $wallet['currency']);
                
                if ($existingWallet) {
                    // Update existing wallet
                    Wallet::updateBalance($userId, $wallet['currency'], $wallet['balance'] - $existingWallet['balance']);
                    echo "<p>Updated {$wallet['currency']} wallet: {$wallet['balance']}</p>";
                } else {
                    // Create new wallet
                    Wallet::createWallet($userId, $wallet['currency'], $wallet['balance']);
                    echo "<p>Created new {$wallet['currency']} wallet: {$wallet['balance']}</p>";
                }
            }
            
            // Add some sample transactions
            $transactions = [
                [
                    'from_user_id' => $userId,
                    'from_currency' => 'USD',
                    'to_user_id' => $userId,
                    'to_currency' => 'BTC',
                    'amount' => 500,
                    'destination_amount' => 0.1,
                    'exchange_rate' => 0.0002,
                    'transaction_type' => 'exchange',
                    'status' => 'completed'
                ],
                [
                    'from_user_id' => $userId,
                    'from_currency' => 'USD',
                    'to_user_id' => $userId,
                    'to_currency' => 'ETH',
                    'amount' => 700,
                    'destination_amount' => 1.5,
                    'exchange_rate' => 0.00214,
                    'transaction_type' => 'exchange',
                    'status' => 'completed'
                ],
                [
                    'from_user_id' => 0,
                    'from_currency' => 'USD',
                    'to_user_id' => $userId,
                    'to_currency' => 'USD',
                    'amount' => 1000,
                    'destination_amount' => 1000,
                    'exchange_rate' => 1,
                    'transaction_type' => 'deposit',
                    'status' => 'completed'
                ]
            ];
            
            foreach ($transactions as $transaction) {
                Wallet::recordTransaction($transaction);
                echo "<p>Added sample {$transaction['transaction_type']} transaction: {$transaction['amount']} {$transaction['from_currency']}</p>";
            }
            
            echo "<h2>Success!</h2>";
            echo "<p>Test data has been added successfully.</p>";
            echo "<p>Now you can check <a href='index.php'>your dashboard</a> or <a href='app/user-profile.php'>your profile</a> to see the dynamic data.</p>";
            
        } catch (Exception $e) {
            echo "<p>Error: {$e->getMessage()}</p>";
        }
    } else {
        echo "<p>Error: Invalid user ID. Please log in first.</p>";
        echo "<p><a href='../bitrader.thetork.com/Signup-Signin/index.html'>Go to login page</a></p>";
    }
}
?> 