<?php
/**
 * Test Connection File
 * This file tests if the database connection works with the absolute paths
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the path to backend
$projectRoot = $_SERVER['DOCUMENT_ROOT'] . '/PROJECT-BITRADER';
$backendDir = $projectRoot . '/backend';

echo "<h2>Testing Database Connection</h2>";
echo "<p>Project Root: " . $projectRoot . "</p>";
echo "<p>Backend Directory: " . $backendDir . "</p>";

try {
    // Include database configuration
    require_once $backendDir . '/config/database.php';
    
    echo "<p>Successfully included database.php</p>";
    
    // Try to get a connection
    if (function_exists('getConnection')) {
        $conn = getConnection();
        
        if ($conn instanceof mysqli) {
            echo "<p style='color:green'>✓ Connection successful!</p>";
            echo "<p>Server Info: " . $conn->server_info . "</p>";
            
            // Check if required tables exist
            $tables = ['users', 'wallets', 'wallet_transactions', 'exchange_rates'];
            echo "<h3>Table Status:</h3>";
            echo "<ul>";
            
            foreach ($tables as $table) {
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                $exists = $result->num_rows > 0;
                
                if ($exists) {
                    echo "<li style='color:green'>✓ $table exists</li>";
                } else {
                    echo "<li style='color:red'>✗ $table does not exist</li>";
                }
            }
            
            echo "</ul>";
            
            $conn->close();
        } else {
            echo "<p style='color:red'>✗ Failed to get a valid database connection</p>";
        }
    } else {
        echo "<p style='color:red'>✗ getConnection function not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Exception: " . $e->getMessage() . "</p>";
}
?> 