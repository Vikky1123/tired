<?php
/**
 * API Entry Point
 * 
 * Handles routing of API requests to the appropriate endpoint
 */

// Set headers for CORS and content type
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include utility files
require_once __DIR__ . '/../utils/response_utils.php';
require_once __DIR__ . '/../utils/auth_utils.php';

// Get the request URI and method
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Parse the URI to determine the endpoint
$uriParts = explode('/', trim(parse_url($requestUri, PHP_URL_PATH), '/'));

// Find the API part of the URI
$apiIndex = array_search('api', $uriParts);
if ($apiIndex === false) {
    sendErrorResponse('Invalid API endpoint', 404);
}

// Remove everything before and including 'api'
$endpoints = array_slice($uriParts, $apiIndex + 1);

// Handle empty endpoint
if (empty($endpoints[0])) {
    sendJsonResponse(['message' => 'Bitrader API', 'version' => '1.0.0']);
}

// Route the request to the appropriate endpoint
switch ($endpoints[0]) {
    case 'auth':
        require_once __DIR__ . '/auth.php';
        break;
        
    case 'users':
        require_once __DIR__ . '/users.php';
        break;
        
    case 'trades':
        require_once __DIR__ . '/trades.php';
        break;
        
    case 'wallet':
        require_once __DIR__ . '/wallet.php';
        break;
        
    case 'market':
        require_once __DIR__ . '/market.php';
        break;
        
    default:
        sendNotFoundResponse('Endpoint not found');
} 