<?php
/**
 * Direct Register API Endpoint
 * A simplified version of the register endpoint that handles both JSON and POST data
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/utils/api_utils.php';

// Set headers for CORS and content type
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

// Start log file for debugging
$logFile = __DIR__ . '/logs/register_debug.log';
$logData = "=== Registration attempt at " . date('Y-m-d H:i:s') . " ===\n";
$logData .= "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";

// Function to safely append to log file
function appendToLog($logFile, $logData) {
    $dir = dirname($logFile);
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($logFile, $logData, FILE_APPEND);
}

try {
    // Get JSON input data
    $requestBody = file_get_contents('php://input');
    $requestData = [];
    
    // Try to parse as JSON
    if (!empty($requestBody)) {
        $jsonData = json_decode($requestBody, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $requestData = $jsonData;
            $logData .= "Data source: JSON input\n";
        } else {
            $logData .= "JSON parsing error: " . json_last_error_msg() . "\n";
        }
    }
    
    // If no JSON data, try POST data
    if (empty($requestData) && !empty($_POST)) {
        $requestData = $_POST;
        $logData .= "Data source: POST data\n";
    }
    
    // Log the received data
    $logData .= "Received data: " . print_r($requestData, true) . "\n";
    
    // Check if we have any data
    if (empty($requestData)) {
        $logData .= "Error: No data received\n";
        appendToLog($logFile, $logData);
        sendErrorResponse('No data received', 400);
        exit;
    }
    
    // Validate required fields
    $requiredFields = ['username', 'email', 'password'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($requestData[$field]) || empty($requestData[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        $logData .= "Error: Missing required fields: " . implode(', ', $missingFields) . "\n";
        appendToLog($logFile, $logData);
        sendErrorResponse('Missing required fields: ' . implode(', ', $missingFields), 400);
        exit;
    }
    
    // Normalize field names
    $userData = [
        'username' => $requestData['username'],
        'email' => $requestData['email'],
        'password' => $requestData['password'],
        'fullName' => $requestData['full_name'] ?? $requestData['fullName'] ?? null,
        'phone' => $requestData['phone'] ?? null,
        'country' => $requestData['country'] ?? null,
        'role' => 'user'
    ];
    
    // Check if username already exists
    $existingUser = User::getByUsername($userData['username']);
    if ($existingUser) {
        $logData .= "Error: Username already exists\n";
        appendToLog($logFile, $logData);
        sendErrorResponse('Username already exists', 409);
        exit;
    }
    
    // Check if email already exists
    $existingEmail = User::getByEmail($userData['email']);
    if ($existingEmail) {
        $logData .= "Error: Email already exists\n";
        appendToLog($logFile, $logData);
        sendErrorResponse('Email already exists', 409);
        exit;
    }
    
    // Create user
    $userId = User::create($userData);
    
    if (!$userId) {
        $logData .= "Error: Failed to create user\n";
        appendToLog($logFile, $logData);
        sendErrorResponse('Failed to create user', 500);
        exit;
    }
    
    // Get created user
    $user = User::getById($userId);
    
    // Generate token
    $token = User::generateToken($user);
    
    $logData .= "Success: User created with ID: $userId\n";
    appendToLog($logFile, $logData);
    
    // Return success with token and user data
    sendSuccessResponse([
        'token' => $token,
        'user' => $user
    ], 'User registered successfully');
    
} catch (Exception $e) {
    // Log the error
    $logData .= "Exception: " . $e->getMessage() . "\n";
    $logData .= "Trace: " . $e->getTraceAsString() . "\n";
    appendToLog($logFile, $logData);
    
    error_log('Registration error: ' . $e->getMessage());
    sendErrorResponse('Server error: ' . $e->getMessage(), 500);
}
?> 