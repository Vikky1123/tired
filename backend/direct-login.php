<?php
/**
 * Direct Login API Endpoint
 * A simplified version of the login endpoint that handles both JSON and POST data
 */

// Make sure we always respond with JSON, even if there are fatal errors
function shutdown_handler() {
    $error = error_get_last();
    if ($error !== null && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $error['message'],
            'error_details' => [
                'file' => $error['file'],
                'line' => $error['line']
            ]
        ]);
    }
}
register_shutdown_function('shutdown_handler');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable output of errors to the browser

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
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Start log file for debugging
$logFile = __DIR__ . '/logs/login_debug.log';
$logDir = dirname($logFile);
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

$logData = "=== Login attempt at " . date('Y-m-d H:i:s') . " ===\n";
$logData .= "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
file_put_contents($logFile, $logData, FILE_APPEND);

try {
    // Include necessary files with error handling
    if (!file_exists(__DIR__ . '/utils/api_utils.php')) {
        throw new Exception('Required file api_utils.php not found');
    }
    require_once __DIR__ . '/utils/api_utils.php';

    if (!file_exists(__DIR__ . '/models/User.php')) {
        throw new Exception('Required file User.php not found');
    }
    require_once __DIR__ . '/models/User.php';

    // Get JSON input data
    $requestBody = file_get_contents('php://input');
    $requestData = [];
    
    // Try to parse as JSON
    if (!empty($requestBody)) {
        $jsonData = json_decode($requestBody, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $requestData = $jsonData;
            $logData = "Data source: JSON input\n";
            file_put_contents($logFile, $logData, FILE_APPEND);
        } else {
            $logData = "JSON parsing error: " . json_last_error_msg() . "\n";
            file_put_contents($logFile, $logData, FILE_APPEND);
        }
    }
    
    // If no JSON data, try POST data
    if (empty($requestData) && !empty($_POST)) {
        $requestData = $_POST;
        $logData = "Data source: POST data\n";
        file_put_contents($logFile, $logData, FILE_APPEND);
    }
    
    // Log the received data (but mask the password)
    $logDisplayData = $requestData;
    if (isset($logDisplayData['password'])) {
        $logDisplayData['password'] = '********';
    }
    $logData = "Received data: " . print_r($logDisplayData, true) . "\n";
    file_put_contents($logFile, $logData, FILE_APPEND);
    
    // Check if we have any data
    if (empty($requestData)) {
        $logData = "Error: No data received\n";
        file_put_contents($logFile, $logData, FILE_APPEND);
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No data received'
        ]);
        exit;
    }
    
    // Validate required fields
    $username = $requestData['username'] ?? '';
    $password = $requestData['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $logData = "Error: Missing username or password\n";
        file_put_contents($logFile, $logData, FILE_APPEND);
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Username and password are required'
        ]);
        exit;
    }
    
    // Check if input is an email
    $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
    
    // Get user by username or email
    if ($isEmail) {
        $user = User::getByEmail($username);
        if (!$user) {
            $logData = "Error: No user found with email: $username\n";
            file_put_contents($logFile, $logData, FILE_APPEND);
        }
    } else {
        $user = User::getByUsername($username);
        if (!$user) {
            $logData = "Error: No user found with username: $username\n";
            file_put_contents($logFile, $logData, FILE_APPEND);
        }
    }
    
    // Verify if user exists and password is correct
    if (!$user) {
        $logData = "Error: No user found\n";
        file_put_contents($logFile, $logData, FILE_APPEND);
        
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
        exit;
    }
    
    // Try different methods to verify password
    $passwordMatches = false;
    
    try {
        // First try the User class method
        if (method_exists('User', 'verifyPassword')) {
            $passwordMatches = User::verifyPassword($password, $user['password']);
            $logData = "Using User::verifyPassword method: " . ($passwordMatches ? "Success" : "Failed") . "\n";
            file_put_contents($logFile, $logData, FILE_APPEND);
        } 
        // Fallback to the global function if available
        else if (function_exists('verifyPassword')) {
            $passwordMatches = verifyPassword($password, $user['password']);
            $logData = "Using global verifyPassword function: " . ($passwordMatches ? "Success" : "Failed") . "\n";
            file_put_contents($logFile, $logData, FILE_APPEND);
        }
        // Last resort: direct password_verify function
        else {
            $passwordMatches = password_verify($password, $user['password']);
            $logData = "Using direct password_verify function: " . ($passwordMatches ? "Success" : "Failed") . "\n";
            file_put_contents($logFile, $logData, FILE_APPEND);
        }
    } catch (Exception $e) {
        $logData = "Password verification exception: " . $e->getMessage() . "\n";
        file_put_contents($logFile, $logData, FILE_APPEND);
        
        // Try direct password_verify as a last resort
        $passwordMatches = password_verify($password, $user['password']);
    }
    
    if (!$passwordMatches) {
        $logData = "Error: Invalid credentials\n";
        file_put_contents($logFile, $logData, FILE_APPEND);
        
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
        exit;
    }
    
    // Generate token
    $token = User::generateToken($user);
    
    // Remove password from user data
    unset($user['password']);
    
    $logData = "Success: User authenticated successfully, ID: " . $user['id'] . "\n";
    file_put_contents($logFile, $logData, FILE_APPEND);
    
    // Return success with token and user data
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'data' => [
            'token' => $token,
            'user' => $user
        ]
    ]);
    
} catch (Exception $e) {
    // Log the error
    $logData = "Exception: " . $e->getMessage() . "\n";
    $logData .= "Trace: " . $e->getTraceAsString() . "\n";
    file_put_contents($logFile, $logData, FILE_APPEND);
    
    error_log('Login error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 