<?php
/**
 * Database Connection Test Script
 * 
 * This script tests the connection to the BiTrader database.
 * Place this file in your PROJECT-BITRADER directory and access it through:
 * http://localhost/PROJECT-BITRADER/database/db_test.php
 */

// Display errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters - modify these as needed
$host = 'localhost';
$username = 'root';
$password = ''; // Default XAMPP has no password
$database = 'bitrader_db';

echo "<h1>BiTrader Database Connection Test</h1>";

// Test database connection
try {
    // Attempt to connect to MySQL
    $conn = new mysqli($host, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color:green'>✅ MySQL Connection: SUCCESS</p>";
    
    // Check if database exists
    $dbExists = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'");
    
    if ($dbExists->num_rows > 0) {
        echo "<p style='color:green'>✅ Database '$database': EXISTS</p>";
        
        // Connect to the specific database
        $conn->select_db($database);
        
        // Get all tables
        $result = $conn->query("SHOW TABLES");
        
        if ($result->num_rows > 0) {
            echo "<p style='color:green'>✅ Tables found: " . $result->num_rows . "</p>";
            
            echo "<h2>Database Tables:</h2>";
            echo "<ul>";
            while ($row = $result->fetch_row()) {
                echo "<li>" . $row[0] . "</li>";
            }
            echo "</ul>";
            
            // Check if users table has the admin user
            $adminCheck = $conn->query("SELECT * FROM users WHERE username = 'admin'");
            
            if ($adminCheck->num_rows > 0) {
                echo "<p style='color:green'>✅ Admin user: FOUND</p>";
            } else {
                echo "<p style='color:orange'>⚠️ Admin user: NOT FOUND - You may need to add an admin user manually</p>";
            }
            
            // Test a simple query from each main table
            echo "<h2>Table Status:</h2>";
            
            $tables = ['users', 'exchange_rates', 'wallets', 'wallet_transactions', 'trades', 'investment_plans'];
            
            foreach ($tables as $table) {
                $tableCheck = $conn->query("SELECT 1 FROM $table LIMIT 1");
                if ($tableCheck !== false) {
                    echo "<p style='color:green'>✅ Table '$table': OK</p>";
                } else {
                    echo "<p style='color:red'>❌ Table '$table': ERROR - " . $conn->error . "</p>";
                }
            }
            
        } else {
            echo "<p style='color:orange'>⚠️ No tables found. You need to import the database schema.</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Database '$database': NOT FOUND - Please create the database first</p>";
    }
    
    // Close connection
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ ERROR: " . $e->getMessage() . "</p>";
    echo "<p>Please check your MySQL connection parameters:</p>";
    echo "<ul>";
    echo "<li>Host: $host</li>";
    echo "<li>Username: $username</li>";
    echo "<li>Password: [hidden]</li>";
    echo "<li>Database: $database</li>";
    echo "</ul>";
}

// Show instructions for fixing common issues
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>If you see errors above, make sure your XAMPP MySQL service is running</li>";
echo "<li>If the database doesn't exist, create it using phpMyAdmin</li>";
echo "<li>Import the database schema from <code>database/bitrader_schema.sql</code></li>";
echo "<li>Make sure your database connection parameters in <code>backend/utils/db_utils.php</code> match your XAMPP setup</li>";
echo "</ol>";

echo "<p><a href='http://localhost/phpmyadmin/' target='_blank'>Open phpMyAdmin</a></p>";
?>