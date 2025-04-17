<?php
/**
 * Database Setup Tool
 * This script helps check and set up the database for BitRader
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>BitRader Database Setup Tool</h1>";
echo "<p>This tool helps verify your database connection and create the necessary tables.</p>";

// Include config files but handle errors
try {
    if (file_exists(__DIR__ . '/config/env.php')) {
        require_once __DIR__ . '/config/env.php';
    }
    
    if (file_exists(__DIR__ . '/config/database.php')) {
        require_once __DIR__ . '/config/database.php';
    }
} catch (Exception $e) {
    echo "<div style='padding: 15px; background-color: #f8d7da; border-radius: 5px; margin-bottom: 20px;'>";
    echo "<strong>Error loading configuration:</strong> " . $e->getMessage();
    echo "</div>";
}

// Function to test database connection
function testDatabaseConnection($host, $user, $pass, $dbname, $port = 3306) {
    try {
        $conn = new mysqli($host, $user, $pass, $dbname, $port);
        if ($conn->connect_error) {
            return [
                'success' => false,
                'error' => $conn->connect_error
            ];
        }
        
        $conn->close();
        return [
            'success' => true
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Function to check if a table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '{$tableName}'");
    return $result->num_rows > 0;
}

// Function to create tables if they don't exist
function createTables($conn) {
    $errors = [];
    $success = [];
    
    // Users table
    $usersTableSql = "CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL,
        `email` varchar(100) NOT NULL,
        `password` varchar(255) NOT NULL,
        `full_name` varchar(100) DEFAULT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `country` varchar(50) DEFAULT NULL,
        `role` enum('admin','user') NOT NULL DEFAULT 'user',
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($usersTableSql) === TRUE) {
        $success[] = "Users table created successfully";
    } else {
        $errors[] = "Error creating users table: " . $conn->error;
    }
    
    // Create admin user if users table is empty
    $countResult = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $countResult->fetch_assoc();
    
    if ($row['count'] == 0) {
        // No users, create admin user
        $adminPassword = password_hash('admin123', PASSWORD_BCRYPT);
        $createAdminSql = "INSERT INTO users (username, email, password, full_name, role, created_at, updated_at) 
                           VALUES ('admin', 'admin@example.com', '{$adminPassword}', 'Administrator', 'admin', NOW(), NOW())";
        
        if ($conn->query($createAdminSql) === TRUE) {
            $success[] = "Admin user created successfully (username: admin, password: admin123)";
        } else {
            $errors[] = "Error creating admin user: " . $conn->error;
        }
    }
    
    return [
        'success' => empty($errors),
        'messages' => $success,
        'errors' => $errors
    ];
}

// Function to run SQL from a file
function runSQLFile($conn, $filename) {
    $errors = [];
    $success = [];
    
    if (!file_exists($filename)) {
        $errors[] = "SQL file not found: {$filename}";
        return [
            'success' => false,
            'errors' => $errors
        ];
    }
    
    $sql = file_get_contents($filename);
    
    // Split SQL by semicolons
    $queries = array_filter(array_map('trim', explode(';', $sql)), 'strlen');
    
    foreach ($queries as $query) {
        if (empty(trim($query))) continue;
        
        if ($conn->query($query) !== TRUE) {
            $errors[] = "Error executing SQL: " . $conn->error . " in query: " . substr($query, 0, 100) . "...";
        }
    }
    
    if (empty($errors)) {
        $success[] = "SQL file executed successfully: {$filename}";
    }
    
    return [
        'success' => empty($errors),
        'messages' => $success,
        'errors' => $errors
    ];
}

// Display connection form
echo "<h2>Database Connection</h2>";
echo "<form method='post' action='' style='background-color: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px;'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='db_host'>Database Host:</label><br>";
echo "<input type='text' id='db_host' name='db_host' value='" . (defined('DB_HOST') ? DB_HOST : 'localhost') . "' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='db_port'>Database Port:</label><br>";
echo "<input type='text' id='db_port' name='db_port' value='" . (defined('DB_PORT') ? DB_PORT : '3306') . "' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='db_user'>Database User:</label><br>";
echo "<input type='text' id='db_user' name='db_user' value='" . (defined('DB_USER') ? DB_USER : 'root') . "' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='db_pass'>Database Password:</label><br>";
echo "<input type='password' id='db_pass' name='db_pass' value='" . (defined('DB_PASS') ? DB_PASS : '') . "' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='db_name'>Database Name:</label><br>";
echo "<input type='text' id='db_name' name='db_name' value='" . (defined('DB_NAME') ? DB_NAME : 'bitrader_db') . "' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div>";
echo "<button type='submit' name='test_connection' style='padding: 8px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;'>Test Connection</button>";
echo "<button type='submit' name='create_tables' style='padding: 8px 15px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;'>Create Tables</button>";
if (file_exists(__DIR__ . '/bitrader_db.sql')) {
    echo "<button type='submit' name='run_sql_file' style='padding: 8px 15px; margin-left: 10px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;'>Run SQL File</button>";
}
echo "</div>";
echo "</form>";

// Process connection test
if (isset($_POST['test_connection']) || isset($_POST['create_tables']) || isset($_POST['run_sql_file'])) {
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbPort = $_POST['db_port'] ?? '3306';
    $dbUser = $_POST['db_user'] ?? 'root';
    $dbPass = $_POST['db_pass'] ?? '';
    $dbName = $_POST['db_name'] ?? 'bitrader_db';
    
    echo "<div style='margin-bottom: 20px;'>";
    echo "<h3>Connection Test Results</h3>";
    
    // Test connection
    $testResult = testDatabaseConnection($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
    
    if ($testResult['success']) {
        echo "<div style='padding: 15px; background-color: #d4edda; border-radius: 5px; margin-bottom: 10px;'>";
        echo "<strong>Connection successful!</strong> Connected to database: {$dbName}";
        echo "</div>";
        
        // Create tables if requested
        if (isset($_POST['create_tables'])) {
            $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
            $tableResult = createTables($conn);
            
            if ($tableResult['success']) {
                echo "<div style='padding: 15px; background-color: #d4edda; border-radius: 5px; margin-bottom: 10px;'>";
                echo "<strong>Tables created successfully!</strong><br>";
                foreach ($tableResult['messages'] as $message) {
                    echo "✅ {$message}<br>";
                }
                echo "</div>";
            } else {
                echo "<div style='padding: 15px; background-color: #f8d7da; border-radius: 5px; margin-bottom: 10px;'>";
                echo "<strong>Error creating tables:</strong><br>";
                foreach ($tableResult['errors'] as $error) {
                    echo "❌ {$error}<br>";
                }
                echo "</div>";
            }
            
            $conn->close();
        }
        
        // Run SQL file if requested
        if (isset($_POST['run_sql_file']) && file_exists(__DIR__ . '/bitrader_db.sql')) {
            $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
            $sqlResult = runSQLFile($conn, __DIR__ . '/bitrader_db.sql');
            
            if ($sqlResult['success']) {
                echo "<div style='padding: 15px; background-color: #d4edda; border-radius: 5px; margin-bottom: 10px;'>";
                echo "<strong>SQL file executed successfully!</strong><br>";
                foreach ($sqlResult['messages'] as $message) {
                    echo "✅ {$message}<br>";
                }
                echo "</div>";
            } else {
                echo "<div style='padding: 15px; background-color: #f8d7da; border-radius: 5px; margin-bottom: 10px;'>";
                echo "<strong>Error executing SQL file:</strong><br>";
                foreach ($sqlResult['errors'] as $error) {
                    echo "❌ {$error}<br>";
                }
                echo "</div>";
            }
            
            $conn->close();
        }
        
        // Generate .env file content
        echo "<div style='padding: 15px; background-color: #f8f9fa; border-radius: 5px;'>";
        echo "<strong>Update your .env file with these settings:</strong><br>";
        echo "<pre>";
        echo "# Database Configuration\n";
        echo "DB_HOST={$dbHost}\n";
        echo "DB_PORT={$dbPort}\n";
        echo "DB_USER={$dbUser}\n";
        echo "DB_PASS={$dbPass}\n";
        echo "DB_NAME={$dbName}\n";
        echo "</pre>";
        echo "</div>";
        
    } else {
        echo "<div style='padding: 15px; background-color: #f8d7da; border-radius: 5px;'>";
        echo "<strong>Connection failed:</strong> " . $testResult['error'];
        echo "<p>Please check your database settings and try again.</p>";
        echo "</div>";
    }
    
    echo "</div>";
}

// Display recommendations
echo "<h2>Recommendations</h2>";
echo "<div style='padding: 15px; background-color: #f8f9fa; border-radius: 5px;'>";
echo "<ul>";
echo "<li>Make sure the database user has proper permissions (SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER)</li>";
echo "<li>If the database doesn't exist, create it using phpMyAdmin or MySQL command line</li>";
echo "<li>After successful setup, update your .env file with the correct database settings</li>";
echo "<li>For security, use a strong password for your database user</li>";
echo "<li>Default login credentials after setup: admin / admin123</li>";
echo "</ul>";
echo "</div>";
?> 