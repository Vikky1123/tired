<?php
/**
 * Trading Model
 */
require_once __DIR__ . '/../utils/db_utils.php';

class Trading {
    // Trade properties
    private $id;
    private $userId;
    private $tradeType; // buy or sell
    private $cryptoCurrency; // BTC, ETH, etc.
    private $amount;
    private $price;
    private $totalValue;
    private $status; // pending, completed, cancelled
    private $createdAt;
    private $updatedAt;
    
    /**
     * Create a new trade
     * 
     * @param array $tradeData Trade data
     * @return int|false The inserted ID or false on failure
     */
    public static function createTrade($tradeData) {
        $sql = "INSERT INTO trades (user_id, trade_type, crypto_currency, amount, price, total_value, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        // Calculate total value if not provided
        if (!isset($tradeData['totalValue']) && isset($tradeData['amount']) && isset($tradeData['price'])) {
            $tradeData['totalValue'] = $tradeData['amount'] * $tradeData['price'];
        }
        
        $params = [
            $tradeData['userId'],
            $tradeData['tradeType'],
            $tradeData['cryptoCurrency'],
            $tradeData['amount'],
            $tradeData['price'],
            $tradeData['totalValue'],
            $tradeData['status'] ?? 'pending'
        ];
        
        return executeInsert($sql, $params);
    }
    
    /**
     * Get trade by ID
     * 
     * @param int $tradeId Trade ID
     * @return array|null Trade data or null if not found
     */
    public static function getTradeById($tradeId) {
        $sql = "SELECT * FROM trades WHERE id = ?";
        $result = executeQuery($sql, [$tradeId]);
        
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Get trades by user ID with optional filters
     * 
     * @param int $userId User ID
     * @param array $filters Optional filters (trade_type, crypto_currency, status)
     * @param int $limit Maximum number of trades to return
     * @param int $offset Offset for pagination
     * @return array Trades data
     */
    public static function getTradesByUserId($userId, $filters = [], $limit = 10, $offset = 0) {
        $conditions = ["user_id = ?"];
        $params = [$userId];
        
        // Add filters if provided
        if (isset($filters['tradeType'])) {
            $conditions[] = "trade_type = ?";
            $params[] = $filters['tradeType'];
        }
        
        if (isset($filters['cryptoCurrency'])) {
            $conditions[] = "crypto_currency = ?";
            $params[] = $filters['cryptoCurrency'];
        }
        
        if (isset($filters['status'])) {
            $conditions[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        // Add limit and offset parameters
        $params[] = $limit;
        $params[] = $offset;
        
        $sql = "SELECT * FROM trades 
                WHERE " . implode(" AND ", $conditions) . " 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        return executeQuery($sql, $params);
    }
    
    /**
     * Update a trade status
     * 
     * @param int $tradeId Trade ID
     * @param string $status New status
     * @return bool True on success, false on failure
     */
    public static function updateTradeStatus($tradeId, $status) {
        $sql = "UPDATE trades SET status = ?, updated_at = NOW() WHERE id = ?";
        return executeNonQuery($sql, [$status, $tradeId]) > 0;
    }
    
    /**
     * Get user portfolio (summary of holdings)
     * 
     * @param int $userId User ID
     * @return array Portfolio data
     */
    public static function getUserPortfolio($userId) {
        $sql = "SELECT 
                    crypto_currency,
                    SUM(CASE WHEN trade_type = 'buy' THEN amount ELSE -amount END) as total_amount,
                    SUM(CASE WHEN trade_type = 'buy' THEN total_value ELSE -total_value END) as total_value
                FROM 
                    trades
                WHERE 
                    user_id = ? 
                    AND status = 'completed'
                GROUP BY 
                    crypto_currency
                HAVING 
                    total_amount > 0";
        
        return executeQuery($sql, [$userId]);
    }
    
    /**
     * Get trade statistics for a user
     * 
     * @param int $userId User ID
     * @return array Trade statistics
     */
    public static function getUserTradeStats($userId) {
        $sql = "SELECT 
                    COUNT(*) as total_trades,
                    SUM(CASE WHEN trade_type = 'buy' THEN 1 ELSE 0 END) as buy_trades,
                    SUM(CASE WHEN trade_type = 'sell' THEN 1 ELSE 0 END) as sell_trades,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_trades,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_trades,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_trades
                FROM 
                    trades
                WHERE 
                    user_id = ?";
        
        $result = executeQuery($sql, [$userId]);
        
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Get total value of user trades
     * 
     * @param int $userId User ID
     * @param string $tradeType Optional trade type filter (buy or sell)
     * @return float Total value
     */
    public static function getTotalTradeValue($userId, $tradeType = null) {
        $conditions = ["user_id = ?", "status = 'completed'"];
        $params = [$userId];
        
        if ($tradeType) {
            $conditions[] = "trade_type = ?";
            $params[] = $tradeType;
        }
        
        $sql = "SELECT 
                    SUM(total_value) as total_value
                FROM 
                    trades
                WHERE 
                    " . implode(" AND ", $conditions);
        
        $result = executeQuery($sql, $params);
        
        return isset($result[0]['total_value']) ? (float)$result[0]['total_value'] : 0;
    }
    
    /**
     * Get recent trades (global)
     * 
     * @param int $limit Maximum number of trades to return
     * @return array Recent trades
     */
    public static function getRecentTrades($limit = 10) {
        $sql = "SELECT 
                    t.id, t.trade_type, t.crypto_currency, t.amount, t.price, 
                    t.total_value, t.status, t.created_at,
                    u.username
                FROM 
                    trades t
                JOIN 
                    users u ON t.user_id = u.id
                WHERE 
                    t.status = 'completed'
                ORDER BY 
                    t.created_at DESC
                LIMIT ?";
        
        return executeQuery($sql, [$limit]);
    }
    
    /**
     * Get market summary (24h)
     * 
     * @return array Market summary
     */
    public static function getMarketSummary() {
        $sql = "SELECT 
                    crypto_currency,
                    COUNT(*) as total_trades,
                    SUM(amount) as total_volume,
                    MAX(price) as highest_price,
                    MIN(price) as lowest_price,
                    AVG(price) as average_price
                FROM 
                    trades
                WHERE 
                    status = 'completed'
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY 
                    crypto_currency";
        
        return executeQuery($sql);
    }
} 