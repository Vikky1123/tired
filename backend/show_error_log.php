<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to plain text for better readability
header('Content-Type: text/plain');

echo "=== Apache Error Log Viewer ===\n\n";

// Default log paths for XAMPP
$possibleLogPaths = [
    'C:/xampp/apache/logs/error.log',
    'C:/xampp/logs/error.log',
    '../../../apache/logs/error.log',
    '../../../logs/error.log'
];

$logFound = false;

foreach ($possibleLogPaths as $logPath) {
    if (file_exists($logPath)) {
        echo "Found log file at: " . $logPath . "\n\n";
        echo "=== Last 50 lines of error log ===\n\n";
        
        // Get the last 50 lines of the log file
        $log = file($logPath);
        $lines = array_slice($log, -50);
        
        echo implode('', $lines);
        
        $logFound = true;
        break;
    }
}

if (!$logFound) {
    echo "Error: Could not find Apache error log file.\n";
    echo "Common locations checked:\n";
    foreach ($possibleLogPaths as $path) {
        echo "- " . $path . "\n";
    }
    
    echo "\nYou might need to check your XAMPP configuration to locate the correct log path.\n";
    
    // List all files in the apache/logs directory if it exists
    $logsDir = 'C:/xampp/apache/logs';
    if (is_dir($logsDir)) {
        echo "\nFiles in " . $logsDir . ":\n";
        $files = scandir($logsDir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "- " . $file . " (" . filesize($logsDir . '/' . $file) . " bytes)\n";
            }
        }
    }
}

// Also check direct-login.php file
echo "\n\n=== Direct Login PHP File Status ===\n";
$loginFilePath = __DIR__ . '/direct-login.php';

if (file_exists($loginFilePath)) {
    echo "Login file exists at: " . $loginFilePath . "\n";
    echo "File size: " . filesize($loginFilePath) . " bytes\n";
    echo "Last modified: " . date("Y-m-d H:i:s", filemtime($loginFilePath)) . "\n";
    
    // Show first few lines (usually includes headers)
    echo "\nFirst 10 lines of direct-login.php:\n";
    $loginFile = file($loginFilePath);
    $firstLines = array_slice($loginFile, 0, 10);
    echo implode('', $firstLines);
} else {
    echo "Error: direct-login.php file not found at expected location: " . $loginFilePath . "\n";
}
?> 