<?php
/**
 * File Permissions Checker
 * This script helps verify if the web server has proper permissions for important files
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>File Permissions Checker</h1>";
echo "<p>This tool checks if the web server has proper permissions for important files and directories.</p>";

// Function to check if a file or directory is readable
function check_readable($path) {
    return is_readable($path);
}

// Function to check if a file or directory is writable
function check_writable($path) {
    return is_writable($path);
}

// Function to get file owner (works on Linux/Mac, may not work on Windows)
function get_owner($path) {
    if (function_exists('posix_getpwuid')) {
        $owner = posix_getpwuid(fileowner($path));
        return $owner['name'] ?? 'Unknown';
    } else {
        return 'Function not available (Windows)';
    }
}

// Function to format results
function display_result($path, $exists, $readable, $writable, $owner = null) {
    $status = $exists ? ($readable && $writable ? 'success' : 'warning') : 'danger';
    $colors = [
        'success' => '#d4edda',
        'warning' => '#fff3cd',
        'danger' => '#f8d7da'
    ];
    
    echo "<div style='margin-bottom: 10px; padding: 10px; background-color: {$colors[$status]}; border-radius: 5px;'>";
    echo "<strong>Path:</strong> " . htmlspecialchars($path) . "<br>";
    echo "<strong>Exists:</strong> " . ($exists ? 'Yes' : 'No') . "<br>";
    
    if ($exists) {
        echo "<strong>Readable:</strong> " . ($readable ? 'Yes' : 'No') . "<br>";
        echo "<strong>Writable:</strong> " . ($writable ? 'Yes' : 'No') . "<br>";
        
        if ($owner !== null) {
            echo "<strong>Owner:</strong> " . $owner . "<br>";
        }
    }
    
    // Add recommendations
    if (!$exists) {
        echo "<strong>Recommendation:</strong> Create this file or directory.<br>";
    } elseif (!$readable) {
        echo "<strong>Recommendation:</strong> Make sure the web server has read permissions on this path.<br>";
    } elseif (!$writable) {
        echo "<strong>Recommendation:</strong> Make sure the web server has write permissions on this path.<br>";
    }
    
    echo "</div>";
}

// Get the current user running the script
echo "<h2>Web Server Information</h2>";
echo "<div style='margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;'>";
echo "<strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>Current User:</strong> " . (function_exists('get_current_user') ? get_current_user() : 'Unknown') . "<br>";
echo "<strong>Script Owner:</strong> " . get_owner(__FILE__) . "<br>";
echo "<strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "</div>";

// Files and directories to check
$paths = [
    // Config files
    __DIR__ . '/.env',
    __DIR__ . '/config',
    __DIR__ . '/config/database.php',
    __DIR__ . '/config/env.php',
    
    // Directory for logs
    __DIR__ . '/logs',
    
    // Models and utils
    __DIR__ . '/models',
    __DIR__ . '/models/User.php',
    __DIR__ . '/utils',
    __DIR__ . '/utils/db_utils.php',
    __DIR__ . '/utils/auth_utils.php',
    __DIR__ . '/utils/api_utils.php',
    
    // API endpoints
    __DIR__ . '/api',
    __DIR__ . '/api/auth',
    __DIR__ . '/api/auth/login.php',
    __DIR__ . '/api/auth/register.php',
];

// Check each path
echo "<h2>Critical Files & Directories</h2>";
foreach ($paths as $path) {
    $exists = file_exists($path);
    $readable = $exists && check_readable($path);
    $writable = $exists && check_writable($path);
    $owner = $exists ? get_owner($path) : null;
    
    display_result($path, $exists, $readable, $writable, $owner);
}

// Check if logs directory is writable
echo "<h2>Logs Directory Write Test</h2>";
$logsDir = __DIR__ . '/logs';
if (!file_exists($logsDir)) {
    echo "<div style='padding: 10px; background-color: #f8d7da; border-radius: 5px;'>";
    echo "Logs directory does not exist. Attempting to create it...<br>";
    
    $created = mkdir($logsDir, 0755, true);
    
    if ($created) {
        echo "Successfully created logs directory.<br>";
    } else {
        echo "Failed to create logs directory. Please check permissions.<br>";
    }
    
    echo "</div>";
} else {
    $testFile = $logsDir . '/test_write_' . time() . '.txt';
    $success = file_put_contents($testFile, 'Test write at ' . date('Y-m-d H:i:s'));
    
    if ($success !== false) {
        echo "<div style='padding: 10px; background-color: #d4edda; border-radius: 5px;'>";
        echo "Successfully wrote test file to logs directory.<br>";
        echo "Test file: " . $testFile . "<br>";
        
        // Delete the test file
        unlink($testFile);
        echo "Test file deleted.<br>";
    } else {
        echo "<div style='padding: 10px; background-color: #f8d7da; border-radius: 5px;'>";
        echo "Failed to write test file to logs directory. Please check permissions.<br>";
    }
    
    echo "</div>";
}

// Recommendations for Windows users
echo "<h2>Recommendations for Windows Users</h2>";
echo "<div style='padding: 15px; background-color: #f8f9fa; border-radius: 5px;'>";
echo "<p>On Windows systems, you may need to adjust folder permissions for the user that the web server runs as.</p>";
echo "<p>For XAMPP/WAMP, this is typically:</p>";
echo "<ul>";
echo "<li>XAMPP: The user that started the Apache service</li>";
echo "<li>WAMP: The SYSTEM user or the user that started the Apache service</li>";
echo "</ul>";
echo "<p>To fix permissions issues on Windows:</p>";
echo "<ol>";
echo "<li>Right-click on the backend folder</li>";
echo "<li>Select Properties</li>";
echo "<li>Go to the Security tab</li>";
echo "<li>Click Edit to change permissions</li>";
echo "<li>Add the appropriate user and give Full Control permissions</li>";
echo "<li>Apply the changes</li>";
echo "</ol>";
echo "</div>";
?> 