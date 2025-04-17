<?php
/**
 * Database Creation Script
 * This script will create the database and required tables if they don't exist
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to plain text for better readability
header('Content-Type: text/plain');

echo "=== BiTrader Database Setup ===\n\n";

// Step 1: Connect to MySQL without selecting a database
try {
    $host = '127.0.0.1';
    $username = 'root';
    $password = '';
    $port = 3306;
    
    echo "Connecting to MySQL server...\n";
    $conn = new mysqli($host, $username, $password, '', $port);
    
    if ($conn->connect_error) {
        die("✗ Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✓ Connected to MySQL server successfully\n";
    echo "  MySQL Version: " . $conn->server_info . "\n\n";
    
    // Step 2: Check if bitrader_db exists
    echo "Checking if database 'bitrader_db' exists...\n";
    $result = $conn->query("SHOW DATABASES LIKE 'bitrader_db'");
    
    if ($result->num_rows == 0) {
        // Create the database
        echo "Database doesn't exist, creating...\n";
        if ($conn->query("CREATE DATABASE bitrader_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
            echo "✓ Database 'bitrader_db' created successfully\n\n";
        } else {
            die("✗ Error creating database: " . $conn->error . "\n");
        }
    } else {
        echo "✓ Database 'bitrader_db' already exists\n\n";
    }
    
    // Step 3: Select the database
    echo "Selecting database 'bitrader_db'...\n";
    if (!$conn->select_db('bitrader_db')) {
        die("✗ Error selecting database: " . $conn->error . "\n");
    }
    echo "✓ Database selected successfully\n\n";
    
    // Step 4: Check if users table exists
    echo "Checking if 'users' table exists...\n";
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    
    if ($result->num_rows == 0) {
        // Create the table
        echo "Table doesn't exist, creating...\n";
        
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            phone VARCHAR(20),
            country VARCHAR(50),
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        if ($conn->query($sql)) {
            echo "✓ Table 'users' created successfully\n\n";
        } else {
            die("✗ Error creating table: " . $conn->error . "\n");
        }
    } else {
        echo "✓ Table 'users' already exists\n\n";
    }
    
    // Step 5: Check if admin user exists
    echo "Checking if 'admin' user exists...\n";
    $result = $conn->query("SELECT id FROM users WHERE username = 'admin'");
    
    if ($result->num_rows == 0) {
        // Create admin user
        echo "Admin user doesn't exist, creating...\n";
        
        // Hash the password (admin123)
        $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
        
        // Prepare the SQL for better security
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $hashedPassword, $fullName, $role);
        
        $username = 'admin';
        $email = 'admin@example.com';
        $fullName = 'Administrator';
        $role = 'admin';
        
        if ($stmt->execute()) {
            echo "✓ Admin user created successfully\n";
            echo "  Username: admin\n";
            echo "  Password: admin123\n\n";
        } else {
            echo "✗ Error creating admin user: " . $stmt->error . "\n\n";
        }
        
        $stmt->close();
    } else {
        echo "✓ Admin user already exists\n\n";
    }
    
    // Step 6: Fix any potential issues with the users table
    echo "Running table maintenance...\n";
    
    // Check if any columns are missing
    $result = $conn->query("SHOW COLUMNS FROM users");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Check and add missing columns
    if (!in_array('role', $columns)) {
        echo "Adding missing 'role' column...\n";
        $conn->query("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER country");
    }
    
    if (!in_array('created_at', $columns)) {
        echo "Adding missing 'created_at' column...\n";
        $conn->query("ALTER TABLE users ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
    }
    
    if (!in_array('updated_at', $columns)) {
        echo "Adding missing 'updated_at' column...\n";
        $conn->query("ALTER TABLE users ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }
    
    echo "✓ Table maintenance completed\n\n";
    
    // Step 7: Display information about the database
    echo "=== Database Information ===\n";
    
    // Count users
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    echo "Total users: " . $row['count'] . "\n";
    
    // Show admin user details (if exists)
    $result = $conn->query("SELECT id, username, email, role FROM users WHERE username = 'admin'");
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        echo "Admin user ID: " . $admin['id'] . "\n";
        echo "Admin email: " . $admin['email'] . "\n";
    }
    
    $conn->close();
    
    echo "\n=== Setup Completed Successfully ===\n";
    echo "You can now login with username 'admin' and password 'admin123'\n";
    echo "Visit the login test page at: http://localhost/PROJECT-BITRADER/backend/fetch_test.html\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?> 