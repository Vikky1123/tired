-- BiTrader Dashboard Data Queries

-- This file contains SQL queries to retrieve the data shown in the dashboard 
-- with the 4 summary values and the cryptocurrency wallet balances

-- 1. Query to get the four main account summary values
SELECT 
    FORMAT(total_balance, 2) AS total_balance,
    FORMAT(total_profit, 2) AS total_profit,
    FORMAT(total_deposit, 2) AS total_deposit,
    FORMAT(total_withdrawal, 2) AS total_withdrawal
FROM 
    user_account_summary
WHERE 
    user_id = ?; -- Replace ? with the user ID

-- 2. Query to get Bitcoin balance
SELECT 
    CONCAT('$', FORMAT(w.balance * e.price, 2)) AS btc_balance_usd,
    CONCAT(w.balance, ' BTC') AS btc_balance
FROM 
    wallets w
JOIN 
    exchange_rates e ON w.currency = e.currency
WHERE 
    w.user_id = ? AND w.currency = 'BTC'; -- Replace ? with the user ID

-- 3. Query to get Dash balance
SELECT 
    CONCAT('$', FORMAT(w.balance * e.price, 2)) AS dash_balance_usd,
    CONCAT(w.balance, ' DASH') AS dash_balance
FROM 
    wallets w
JOIN 
    exchange_rates e ON w.currency = e.currency
WHERE 
    w.user_id = ? AND w.currency = 'DASH'; -- Replace ? with the user ID

-- 4. Query to get Ethereum balance
SELECT 
    CONCAT('$', FORMAT(w.balance * e.price, 2)) AS eth_balance_usd,
    CONCAT(w.balance, ' ETH') AS eth_balance
FROM 
    wallets w
JOIN 
    exchange_rates e ON w.currency = e.currency
WHERE 
    w.user_id = ? AND w.currency = 'ETH'; -- Replace ? with the user ID

-- 5. Combined query to get all cryptocurrency balances at once
SELECT 
    w.currency,
    w.balance,
    CONCAT('$', FORMAT(w.balance * e.price, 2)) AS usd_value
FROM 
    wallets w
JOIN 
    exchange_rates e ON w.currency = e.currency
WHERE 
    w.user_id = ? AND w.currency IN ('BTC', 'DASH', 'ETH')
ORDER BY 
    w.currency;

-- 6. Query to get specific user wallet balances with trend data
SELECT 
    w.currency,
    w.balance,
    e.price AS current_price,
    w.balance * e.price AS usd_value,
    CASE 
        WHEN w.currency = 'BTC' THEN 'up' -- Trend data would normally come from market analysis
        WHEN w.currency = 'DASH' THEN 'up'
        WHEN w.currency = 'ETH' THEN 'up'
        ELSE 'neutral'
    END AS trend
FROM 
    wallets w
JOIN 
    exchange_rates e ON w.currency = e.currency
WHERE 
    w.user_id = ? AND w.currency IN ('BTC', 'DASH', 'ETH')
ORDER BY 
    w.currency;

-- 7. PHP-ready function to format all dashboard data at once
/*
function getDashboardData($userId) {
    // Database connection code here
    
    // Get account summary
    $summaryQuery = "
        SELECT 
            total_balance,
            total_profit,
            total_deposit,
            total_withdrawal
        FROM 
            user_account_summary
        WHERE 
            user_id = ?
    ";
    
    $stmt = $conn->prepare($summaryQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $summaryResult = $stmt->get_result()->fetch_assoc();
    
    // Get crypto balances
    $walletsQuery = "
        SELECT 
            w.currency,
            w.balance,
            e.price AS current_price,
            w.balance * e.price AS usd_value,
            CASE 
                WHEN w.currency = 'BTC' THEN 'up'
                WHEN w.currency = 'DASH' THEN 'up'
                WHEN w.currency = 'ETH' THEN 'up'
                ELSE 'neutral'
            END AS trend
        FROM 
            wallets w
        JOIN 
            exchange_rates e ON w.currency = e.currency
        WHERE 
            w.user_id = ? AND w.currency IN ('BTC', 'DASH', 'ETH')
        ORDER BY 
            w.currency
    ";
    
    $stmt = $conn->prepare($walletsQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $walletsResult = $stmt->get_result();
    
    $wallets = [];
    while ($row = $walletsResult->fetch_assoc()) {
        $wallets[$row['currency']] = [
            'balance' => $row['balance'],
            'price' => $row['current_price'],
            'usd_value' => $row['usd_value'],
            'trend' => $row['trend']
        ];
    }
    
    return [
        'summary' => [
            'total_balance' => number_format($summaryResult['total_balance'], 2),
            'total_profit' => number_format($summaryResult['total_profit'], 2),
            'total_deposit' => number_format($summaryResult['total_deposit'], 2),
            'total_withdrawal' => number_format($summaryResult['total_withdrawal'], 2)
        ],
        'wallets' => $wallets
    ];
}
*/ 