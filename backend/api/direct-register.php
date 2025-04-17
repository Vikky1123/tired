<?php
// Set CORS headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Only POST requests are accepted.']);
    exit;
}

// Try-catch block to catch any errors
try {
    // Include necessary files
    $utils_path = __DIR__ . '/../utils/api_utils.php';
    $db_path = __DIR__ . '/../config/database.php';
    $user_model_path = __DIR__ . '/../models/User.php';
    
    if (!file_exists($utils_path)) {
        throw new Exception("API utils file not found at: $utils_path");
    }
    if (!file_exists($db_path)) {
        throw new Exception("Database config file not found at: $db_path");
    }
    if (!file_exists($user_model_path)) {
        throw new Exception("User model file not found at: $user_model_path");
    }
    
    require_once $utils_path;
    require_once $db_path;
    require_once $user_model_path;

    // Get the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error parsing JSON request: " . json_last_error_msg());
    }
    
    if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
        throw new Exception("Missing required fields: username, email, and password");
    }
    
    // Forward the request to the actual registration file
    $register_path = __DIR__ . '/auth/register.php';
    if (!file_exists($register_path)) {
        throw new Exception("Registration handler file not found at: $register_path");
    }
    
    require_once $register_path;
    
} catch (Exception $e) {
    // If any error occurs, return a JSON error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit;
} 