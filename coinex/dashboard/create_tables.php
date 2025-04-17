<?php
/**
 * Create Tables Script
 * This script creates the necessary database tables for the application
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the path to backend
$projectRoot = $_SERVER['DOCUMENT_ROOT'] . '/PROJECT-BITRADER';
$backendDir = $projectRoot . '/backend';

echo "<h2>Creating Database Tables</h2>";

try {
    // Include database configuration
    require_once $backendDir . '/config/database.php';
    
    // Get connection
    $conn = getConnection();
    
    // Array to store results
    $results = [];
    
    // 1. Create users table
    $usersSql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        phone VARCHAR(20),
        country VARCHAR(50),
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($usersSql)) {
        $results[] = "<p style='color:green'>✓ Users table created or already exists.</p>";
    } else {
        $results[] = "<p style='color:red'>✗ Error creating users table: " . $conn->error . "</p>";
    }
    
    // 2. Create wallets table
    $walletsSql = "CREATE TABLE IF NOT EXISTS wallets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        currency VARCHAR(10) NOT NULL,
        balance DECIMAL(18, 8) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_currency (user_id, currency),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($walletsSql)) {
        $results[] = "<p style='color:green'>✓ Wallets table created or already exists.</p>";
    } else {
        $results[] = "<p style='color:red'>✗ Error creating wallets table: " . $conn->error . "</p>";
    }
    
    // 3. Create wallet_transactions table
    $transactionsSql = "CREATE TABLE IF NOT EXISTS wallet_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        from_user_id INT NOT NULL,
        from_currency VARCHAR(10) NOT NULL,
        to_user_id INT NOT NULL,
        to_currency VARCHAR(10) NOT NULL,
        amount DECIMAL(18, 8) NOT NULL,
        destination_amount DECIMAL(18, 8) NOT NULL,
        exchange_rate DECIMAL(18, 8) NOT NULL,
        transaction_type ENUM('deposit', 'withdraw', 'transfer', 'exchange') NOT NULL,
        status ENUM('pending', 'completed', 'failed', 'cancelled') NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($transactionsSql)) {
        $results[] = "<p style='color:green'>✓ Wallet transactions table created or already exists.</p>";
    } else {
        $results[] = "<p style='color:red'>✗ Error creating wallet transactions table: " . $conn->error . "</p>";
    }
    
    // 4. Create exchange_rates table
    $ratesSql = "CREATE TABLE IF NOT EXISTS exchange_rates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        currency VARCHAR(10) NOT NULL,
        price DECIMAL(18, 8) NOT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_currency (currency)
    )";
    
    if ($conn->query($ratesSql)) {
        $results[] = "<p style='color:green'>✓ Exchange rates table created or already exists.</p>";
    } else {
        $results[] = "<p style='color:red'>✗ Error creating exchange rates table: " . $conn->error . "</p>";
    }
    
    // 5. Insert default exchange rates if the table is empty
    $checkRatesSql = "SELECT COUNT(*) as count FROM exchange_rates";
    $ratesCount = $conn->query($checkRatesSql)->fetch_assoc()['count'];
    
    if ($ratesCount == 0) {
        $defaultRatesSql = "INSERT INTO exchange_rates (currency, price, updated_at) VALUES
            ('BTC', 50000.00, NOW()),
            ('ETH', 3500.00, NOW()),
            ('USDT', 1.00, NOW()),
            ('BNB', 450.00, NOW()),
            ('SOL', 150.00, NOW()),
            ('XRP', 1.10, NOW()),
            ('ADA', 1.20, NOW()),
            ('DOGE', 0.12, NOW()),
            ('DOT', 20.00, NOW()),
            ('AVAX', 30.00, NOW())";
        
        if ($conn->query($defaultRatesSql)) {
            $results[] = "<p style='color:green'>✓ Default exchange rates inserted.</p>";
        } else {
            $results[] = "<p style='color:red'>✗ Error inserting default exchange rates: " . $conn->error . "</p>";
        }
    }
    
    // 6. Check if admin user exists, create if not
    $checkAdminSql = "SELECT COUNT(*) as count FROM users WHERE username = 'admin'";
    $adminCount = $conn->query($checkAdminSql)->fetch_assoc()['count'];
    
    if ($adminCount == 0) {
        // Create admin user with hashed password (password123)
        $adminPassword = password_hash('password123', PASSWORD_DEFAULT);
        $createAdminSql = "INSERT INTO users (username, email, password, full_name, role, created_at, updated_at) VALUES
            ('admin', 'admin@example.com', '$adminPassword', 'Administrator', 'admin', NOW(), NOW())";
        
        if ($conn->query($createAdminSql)) {
            $results[] = "<p style='color:green'>✓ Admin user created (username: admin, password: password123).</p>";
            
            // Create admin wallets
            $adminId = $conn->insert_id;
            $adminWalletsSql = "INSERT INTO wallets (user_id, currency, balance, created_at, updated_at) VALUES
                ($adminId, 'USD', 100000.00, NOW(), NOW()),
                ($adminId, 'BTC', 10.00, NOW(), NOW()),
                ($adminId, 'ETH', 50.00, NOW(), NOW())";
            
            if ($conn->query($adminWalletsSql)) {
                $results[] = "<p style='color:green'>✓ Admin wallets created.</p>";
            } else {
                $results[] = "<p style='color:red'>✗ Error creating admin wallets: " . $conn->error . "</p>";
            }
        } else {
            $results[] = "<p style='color:red'>✗ Error creating admin user: " . $conn->error . "</p>";
        }
    }
    
    // Display results
    foreach ($results as $result) {
        echo $result;
    }
    
    // Close connection
    $conn->close();
    
    echo "<p>Database setup complete. You can now <a href='/PROJECT-BITRADER/coinex/dashboard/index.php'>go to the dashboard</a>.</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Exception: " . $e->getMessage() . "</p>";
}
?> 