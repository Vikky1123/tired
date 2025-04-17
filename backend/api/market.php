<?php
/**
 * Market API Endpoints
 */
require_once __DIR__ . '/../models/Trading.php';

// Check authentication for all market endpoints
$payload = authenticateUser();

if (!$payload) {
    sendUnauthorizedResponse('Authentication required');
}

// Get action from endpoint
$action = isset($endpoints[1]) ? $endpoints[1] : '';

// Handle different market actions
switch ($action) {
    case '':
    case 'summary':
        handleMarketSummary();
        break;
        
    case 'recent':
        handleRecentTrades();
        break;
        
    case 'prices':
        handleCryptoPrices();
        break;
        
    default:
        sendNotFoundResponse('Market action not found');
}

/**
 * Handle market summary
 */
function handleMarketSummary() {
    global $requestMethod;
    
    if ($requestMethod !== 'GET') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    $summary = Trading::getMarketSummary();
    
    sendSuccessResponse(['summary' => $summary]);
}

/**
 * Handle recent trades
 */
function handleRecentTrades() {
    global $requestMethod;
    
    if ($requestMethod !== 'GET') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    // Get limit parameter
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    
    $recentTrades = Trading::getRecentTrades($limit);
    
    sendSuccessResponse(['trades' => $recentTrades]);
}

/**
 * Handle cryptocurrency prices
 */
function handleCryptoPrices() {
    global $requestMethod;
    
    if ($requestMethod !== 'GET') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    // Get cryptocurrency from query parameter
    $currency = isset($_GET['currency']) ? $_GET['currency'] : null;
    
    // This would typically call an external API or a database with current prices
    // For this example, we'll return dummy data
    $prices = [
        'BTC' => [
            'price' => 50000.00,
            'change_24h' => 2.5,
            'volume_24h' => 1000000,
            'market_cap' => 950000000000
        ],
        'ETH' => [
            'price' => 3500.00,
            'change_24h' => 1.8,
            'volume_24h' => 500000,
            'market_cap' => 400000000000
        ],
        'USDT' => [
            'price' => 1.00,
            'change_24h' => 0.01,
            'volume_24h' => 2000000,
            'market_cap' => 70000000000
        ],
        'BNB' => [
            'price' => 450.00,
            'change_24h' => -0.5,
            'volume_24h' => 300000,
            'market_cap' => 70000000000
        ],
        'SOL' => [
            'price' => 150.00,
            'change_24h' => 5.2,
            'volume_24h' => 200000,
            'market_cap' => 50000000000
        ],
        'XRP' => [
            'price' => 1.10,
            'change_24h' => 0.8,
            'volume_24h' => 150000,
            'market_cap' => 55000000000
        ],
        'ADA' => [
            'price' => 1.20,
            'change_24h' => -1.2,
            'volume_24h' => 180000,
            'market_cap' => 40000000000
        ],
        'DOGE' => [
            'price' => 0.12,
            'change_24h' => 3.5,
            'volume_24h' => 120000,
            'market_cap' => 16000000000
        ],
        'DOT' => [
            'price' => 20.00,
            'change_24h' => -0.3,
            'volume_24h' => 90000,
            'market_cap' => 20000000000
        ],
        'AVAX' => [
            'price' => 30.00,
            'change_24h' => 2.1,
            'volume_24h' => 70000,
            'market_cap' => 10000000000
        ]
    ];
    
    if ($currency) {
        if (isset($prices[strtoupper($currency)])) {
            sendSuccessResponse(['price' => $prices[strtoupper($currency)]]);
        } else {
            sendNotFoundResponse('Cryptocurrency not found');
        }
    } else {
        sendSuccessResponse(['prices' => $prices]);
    }
} 