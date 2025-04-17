<?php
/**
 * Login API Endpoint
 * Authenticates a user against the database
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../utils/api_utils.php';

// Enable CORS for API requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
    exit;
}

try {
    // Get JSON request body
    $requestBody = file_get_contents('php://input');
    if (!$requestBody) {
        sendErrorResponse('Empty request body', 400);
        exit;
    }
    
    $requestData = json_decode($requestBody, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendErrorResponse('Invalid JSON: ' . json_last_error_msg(), 400);
        exit;
    }

    // Check for required fields
    if (!isset($requestData['username']) || !isset($requestData['password'])) {
        sendErrorResponse('Username and password are required', 400);
        exit;
    }

    $username = $requestData['username'];
    $password = $requestData['password'];

    // Authenticate user
    $user = User::authenticate($username, $password);

    if (!$user) {
        sendErrorResponse('Invalid username or password', 401);
        exit;
    }

    // Generate JWT token
    $token = User::generateToken($user);

    // Return success with token and user data
    sendSuccessResponse([
        'token' => $token,
        'user' => $user
    ], 'Login successful');
    
} catch (Exception $e) {
    // Log the error
    error_log('Login error: ' . $e->getMessage());
    sendErrorResponse('Server error: ' . $e->getMessage(), 500);
} 