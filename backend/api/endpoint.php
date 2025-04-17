<?php
// Set CORS headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include necessary files
require_once '../utils/api_utils.php';
require_once '../config/database.php';

// Get the requested endpoint from the query string
if (!isset($_GET['route'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No route specified']);
    exit;
}

$route = $_GET['route'];
$method = $_SERVER['REQUEST_METHOD'];

// Define valid routes and their corresponding files
$validRoutes = [
    'auth/login' => 'auth/login.php',
    'auth/register' => 'auth/register.php',
    'auth/verify' => 'auth/verify.php',
    // Add more routes as needed
];

// Check if the requested route exists
if (!isset($validRoutes[$route])) {
    http_response_code(404);
    echo json_encode(['error' => 'Route not found']);
    exit;
}

// Get the raw request data
$requestData = json_decode(file_get_contents('php://input'), true);

// Forward the request to the appropriate endpoint
require_once $validRoutes[$route]; 