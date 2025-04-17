<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: text/html; charset=utf-8');

echo '<h1>Admin User Check</h1>';

// Check if we can load database configuration
if (!file_exists(__DIR__ . '/config/database.php')) {
    die('<p style="color: red">Error: Database configuration file not found</p>');
}

// Include database configuration
require_once __DIR__ . '/config/database.php';

try {
    // Connect to database
    $conn = getConnection();
    echo '<p style="color: green">✓ Connected to database successfully</p>';
    
    // Check if users table exists
    $tablesExist = false;
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result && $result->num_rows > 0) {
        $tablesExist = true;
        echo '<p style="color: green">✓ Users table exists</p>';
    } else {
        echo '<p style="color: orange">! Users table does not exist</p>';
    }
    
    // If table exists, check for admin user
    if ($tablesExist) {
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username = 'admin' OR email = 'admin@example.com'");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo '<p style="color: green">✓ Admin user exists</p>';
            echo '<p>Username: ' . htmlspecialchars($user['username']) . '<br>';
            echo 'Email: ' . htmlspecialchars($user['email']) . '<br>';
            echo 'Password hash: ' . substr($user['password'], 0, 15) . '...</p>';
        } else {
            echo '<p style="color: red">✗ Admin user does not exist</p>';
            
            // Ask if we should create admin user
            echo '<form method="post">';
            echo '<h2>Create Admin User</h2>';
            echo '<p><input type="submit" name="create_admin" value="Create Default Admin User"></p>';
            echo '</form>';
            
            // Create admin user if requested
            if (isset($_POST['create_admin'])) {
                // Hash the password
                $password = 'admin123';
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
                $username = 'admin';
                $email = 'admin@example.com';
                $fullName = 'Administrator';
                $role = 'admin';
                $stmt->bind_param('sssss', $username, $email, $hashedPassword, $fullName, $role);
                
                if ($stmt->execute()) {
                    echo '<p style="color: green">✓ Admin user created successfully!</p>';
                    echo '<p>Username: admin<br>';
                    echo 'Email: admin@example.com<br>';
                    echo 'Password: admin123</p>';
                } else {
                    echo '<p style="color: red">✗ Failed to create admin user: ' . $stmt->error . '</p>';
                }
            }
        }
    }
    
    // If table doesn't exist, offer to create it
    if (!$tablesExist) {
        echo '<form method="post">';
        echo '<h2>Create Database Tables</h2>';
        echo '<p><input type="submit" name="create_tables" value="Create Database Tables"></p>';
        echo '</form>';
        
        // Create tables if requested
        if (isset($_POST['create_tables'])) {
            $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(100),
                phone VARCHAR(20),
                country VARCHAR(50),
                role VARCHAR(20) DEFAULT 'user',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            
            if ($conn->query($sql)) {
                echo '<p style="color: green">✓ Users table created successfully!</p>';
                echo '<p>Please refresh this page to create an admin user.</p>';
            } else {
                echo '<p style="color: red">✗ Failed to create users table: ' . $conn->error . '</p>';
            }
        }
    }
    
    // Close connection
    $conn->close();
    
} catch (Exception $e) {
    echo '<p style="color: red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// Show link to login test
echo '<p><a href="login_test.html" style="display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">Go to Login Test</a></p>';
?> 