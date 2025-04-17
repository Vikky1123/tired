<?php
/**
 * Wallet Model
 */
require_once __DIR__ . '/../utils/db_utils.php';

class Wallet {
    // Wallet properties
    private $id;
    private $userId;
    private $currency; // USD, BTC, ETH, etc.
    private $balance;
    private $createdAt;
    private $updatedAt;
    
    /**
     * Get wallet by user ID and currency
     * 
     * @param int $userId User ID
     * @param string $currency Currency
     * @return array|null Wallet data or null if not found
     */
    public static function getWallet($userId, $currency) {
        $sql = "SELECT * FROM wallets WHERE user_id = ? AND currency = ?";
        $result = executeQuery($sql, [$userId, $currency]);
        
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Get all wallets for a user
     * 
     * @param int $userId User ID
     * @return array Wallets data
     */
    public static function getUserWallets($userId) {
        $sql = "SELECT * FROM wallets WHERE user_id = ? ORDER BY currency";
        return executeQuery($sql, [$userId]);
    }
    
    /**
     * Create a new wallet
     * 
     * @param int $userId User ID
     * @param string $currency Currency
     * @param float $initialBalance Initial balance (default 0)
     * @return int|false The inserted ID or false on failure
     */
    public static function createWallet($userId, $currency, $initialBalance = 0) {
        $sql = "INSERT INTO wallets (user_id, currency, balance, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())";
        
        return executeInsert($sql, [$userId, $currency, $initialBalance]);
    }
    
    /**
     * Update wallet balance
     * 
     * @param int $userId User ID
     * @param string $currency Currency
     * @param float $amount Amount to add (positive) or subtract (negative)
     * @return bool True on success, false on failure
     */
    public static function updateBalance($userId, $currency, $amount) {
        // Get current wallet
        $wallet = self::getWallet($userId, $currency);
        
        // If wallet doesn't exist, create it with the initial amount
        if (!$wallet) {
            return self::createWallet($userId, $currency, $amount) > 0;
        }
        
        // Update existing wallet
        $newBalance = $wallet['balance'] + $amount;
        
        // Don't allow negative balance
        if ($newBalance < 0) {
            return false;
        }
        
        $sql = "UPDATE wallets SET balance = ?, updated_at = NOW() WHERE user_id = ? AND currency = ?";
        return executeNonQuery($sql, [$newBalance, $userId, $currency]) > 0;
    }
    
    /**
     * Transfer funds between wallets
     * 
     * @param int $fromUserId Source user ID
     * @param string $fromCurrency Source currency
     * @param int $toUserId Destination user ID
     * @param string $toCurrency Destination currency
     * @param float $amount Amount to transfer
     * @param float $exchangeRate Exchange rate (if different currencies)
     * @return bool True on success, false on failure
     */
    public static function transferFunds($fromUserId, $fromCurrency, $toUserId, $toCurrency, $amount, $exchangeRate = 1) {
        // Get source wallet
        $sourceWallet = self::getWallet($fromUserId, $fromCurrency);
        
        if (!$sourceWallet || $sourceWallet['balance'] < $amount) {
            return false; // Insufficient funds
        }
        
        // Calculate destination amount with exchange rate
        $destinationAmount = $amount * $exchangeRate;
        
        // Start transaction
        $conn = getConnection();
        $conn->begin_transaction();
        
        try {
            // Deduct from source wallet
            $deducted = self::updateBalance($fromUserId, $fromCurrency, -$amount);
            
            if (!$deducted) {
                throw new Exception("Failed to deduct from source wallet");
            }
            
            // Add to destination wallet
            $added = self::updateBalance($toUserId, $toCurrency, $destinationAmount);
            
            if (!$added) {
                throw new Exception("Failed to add to destination wallet");
            }
            
            // Record the transaction
            $transactionData = [
                'from_user_id' => $fromUserId,
                'from_currency' => $fromCurrency,
                'to_user_id' => $toUserId,
                'to_currency' => $toCurrency,
                'amount' => $amount,
                'destination_amount' => $destinationAmount,
                'exchange_rate' => $exchangeRate,
                'transaction_type' => ($fromUserId == $toUserId) ? 'exchange' : 'transfer',
                'status' => 'completed'
            ];
            
            self::recordTransaction($transactionData);
            
            // Commit transaction
            $conn->commit();
            $conn->close();
            
            return true;
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $conn->close();
            return false;
        }
    }
    
    /**
     * Record a wallet transaction
     * 
     * @param array $transactionData Transaction data
     * @return int|false The inserted ID or false on failure
     */
    public static function recordTransaction($transactionData) {
        $sql = "INSERT INTO wallet_transactions 
                (from_user_id, from_currency, to_user_id, to_currency, amount, destination_amount, 
                exchange_rate, transaction_type, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $transactionData['from_user_id'],
            $transactionData['from_currency'],
            $transactionData['to_user_id'],
            $transactionData['to_currency'],
            $transactionData['amount'],
            $transactionData['destination_amount'],
            $transactionData['exchange_rate'],
            $transactionData['transaction_type'],
            $transactionData['status']
        ];
        
        return executeInsert($sql, $params);
    }
    
    /**
     * Get transaction history for a user
     * 
     * @param int $userId User ID
     * @param int $limit Maximum number of transactions to return
     * @param int $offset Offset for pagination
     * @return array Transaction history
     */
    public static function getTransactionHistory($userId, $limit = 10, $offset = 0) {
        $sql = "SELECT * FROM wallet_transactions
                WHERE from_user_id = ? OR to_user_id = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";
        
        return executeQuery($sql, [$userId, $userId, $limit, $offset]);
    }
    
    /**
     * Get total wallet balance in USD equivalent
     * 
     * @param int $userId User ID
     * @return float Total balance in USD
     */
    public static function getTotalBalanceInUSD($userId) {
        $sql = "SELECT 
                    w.currency,
                    w.balance,
                    CASE 
                        WHEN w.currency = 'USD' THEN 1 
                        ELSE (SELECT price FROM exchange_rates WHERE currency = w.currency ORDER BY updated_at DESC LIMIT 1) 
                    END as exchange_rate
                FROM 
                    wallets w
                WHERE 
                    w.user_id = ?";
        
        $wallets = executeQuery($sql, [$userId]);
        
        $totalUSD = 0;
        foreach ($wallets as $wallet) {
            $rate = isset($wallet['exchange_rate']) ? $wallet['exchange_rate'] : 0;
            $totalUSD += $wallet['balance'] * $rate;
        }
        
        return $totalUSD;
    }
} 