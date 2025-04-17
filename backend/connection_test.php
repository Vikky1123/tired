<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Log request details
$logFile = __DIR__ . '/logs/connection_test.log';
$logDir = dirname($logFile);
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

$logData = "=== Connection test at " . date('Y-m-d H:i:s') . " ===\n";
$logData .= "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$logData .= "Remote Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
$logData .= "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . "\n";
file_put_contents($logFile, $logData, FILE_APPEND);

// Test database connection
$dbConnectionStatus = 'Not tested';
$dbError = '';

try {
    // Include database configuration
    if (file_exists(__DIR__ . '/config/database.php')) {
        require_once __DIR__ . '/config/database.php';
        if (function_exists('getConnection')) {
            $conn = getConnection();
            if ($conn instanceof mysqli) {
                $dbConnectionStatus = 'Success';
                $conn->close();
            } else {
                $dbConnectionStatus = 'Failed';
                $dbError = 'Connection function did not return a valid mysqli object';
            }
        } else {
            $dbConnectionStatus = 'Failed';
            $dbError = 'getConnection function not found';
        }
    } else {
        $dbConnectionStatus = 'Failed';
        $dbError = 'Database configuration file not found';
    }
} catch (Exception $e) {
    $dbConnectionStatus = 'Failed';
    $dbError = $e->getMessage();
}

// Get PHP and server information
$serverInfo = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown',
    'request_time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time())
];

// Check if direct-login.php exists
$loginApiExists = file_exists(__DIR__ . '/direct-login.php');

// Check if mysqli extension is loaded
$mysqliLoaded = extension_loaded('mysqli');

// Create response
$response = [
    'success' => true,
    'message' => 'Connection test completed',
    'data' => [
        'server_info' => $serverInfo,
        'database_connection' => [
            'status' => $dbConnectionStatus,
            'error' => $dbError
        ],
        'api_endpoints' => [
            'login_api_exists' => $loginApiExists,
            'login_api_path' => __DIR__ . '/direct-login.php'
        ],
        'extensions' => [
            'mysqli_loaded' => $mysqliLoaded
        ]
    ]
];

// Add the response to the log
$logData = "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";
file_put_contents($logFile, $logData, FILE_APPEND);

// Send response
echo json_encode($response);
?> 