<?php
/**
 * Database Connection Test
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to plain text for better readability
header('Content-Type: text/plain');

echo "=== Database Connection Test ===\n\n";

// Test direct mysqli connection first
echo "Testing direct mysqli connection...\n";
try {
    $host = '127.0.0.1';
    $username = 'root';
    $password = '';
    $database = 'bitrader_db';
    $port = 3306;
    
    $conn = new mysqli($host, $username, $password, $database, $port);
    
    if ($conn->connect_error) {
        echo "✗ Direct connection failed: " . $conn->connect_error . "\n";
    } else {
        echo "✓ Direct connection successful!\n";
        echo "  MySQL Version: " . $conn->server_info . "\n";
        
        // Check if users table exists
        $result = $conn->query("SHOW TABLES LIKE 'users'");
        if ($result->num_rows > 0) {
            echo "✓ Users table exists\n";
            
            // Count users
            $result = $conn->query("SELECT COUNT(*) as count FROM users");
            $row = $result->fetch_assoc();
            echo "  Total users: " . $row['count'] . "\n";
            
            // Check for admin user
            $result = $conn->query("SELECT * FROM users WHERE username = 'admin' LIMIT 1");
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                echo "✓ Admin user exists (ID: " . $admin['id'] . ")\n";
                echo "  Username: " . $admin['username'] . "\n";
                echo "  Email: " . $admin['email'] . "\n";
                echo "  Password hash: " . substr($admin['password'], 0, 20) . "...\n";
            } else {
                echo "✗ Admin user does not exist\n";
            }
        } else {
            echo "✗ Users table does not exist\n";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n";

// Test connection through the database.php file
echo "Testing connection through database.php...\n";
try {
    require_once __DIR__ . '/config/database.php';
    
    $conn = getConnection();
    echo "✓ Connection through database.php successful!\n";
    
    // Try a simple query
    $result = $conn->query("SELECT DATABASE() as db");
    $row = $result->fetch_assoc();
    echo "  Connected to database: " . $row['db'] . "\n";
    
    $conn->close();
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n";

// Load environment variables and show database connection settings
echo "=== Database Configuration ===\n";
require_once __DIR__ . '/config/env.php';

echo "DB_HOST: " . getenv('DB_HOST') . "\n";
echo "DB_USER: " . getenv('DB_USER') . "\n";
echo "DB_PASS: " . (getenv('DB_PASS') ? '[password set]' : '[empty]') . "\n";
echo "DB_NAME: " . getenv('DB_NAME') . "\n";
echo "DB_PORT: " . getenv('DB_PORT') . "\n";

echo "\n=== End of Test ===\n";
?> 