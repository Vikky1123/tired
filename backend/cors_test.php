<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Log the request
$logFile = __DIR__ . '/logs/cors_test.log';
$logDir = dirname($logFile);
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

$logData = "=== CORS Test at " . date('Y-m-d H:i:s') . " ===\n";
$logData .= "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$logData .= "Request Headers: \n";
foreach (getallheaders() as $name => $value) {
    $logData .= "$name: $value\n";
}
file_put_contents($logFile, $logData, FILE_APPEND);

// Return success response
$response = [
    'success' => true,
    'message' => 'CORS is working properly',
    'timestamp' => time(),
    'server_info' => [
        'php_version' => phpversion(),
        'server_software' => $_SERVER['SERVER_SOFTWARE']
    ]
];

echo json_encode($response);
?> 