-- Create database
CREATE DATABASE IF NOT EXISTS bitrader_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use database
USE bitrader_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

-- Wallets table
CREATE TABLE IF NOT EXISTS wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    currency VARCHAR(10) NOT NULL,
    balance DECIMAL(18, 8) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY unique_user_currency (user_id, currency),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Wallet transactions table
CREATE TABLE IF NOT EXISTS wallet_transactions (
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
    created_at DATETIME NOT NULL,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Trades table
CREATE TABLE IF NOT EXISTS trades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    trade_type ENUM('buy', 'sell') NOT NULL,
    crypto_currency VARCHAR(10) NOT NULL,
    amount DECIMAL(18, 8) NOT NULL,
    price DECIMAL(18, 8) NOT NULL,
    total_value DECIMAL(18, 8) NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'cancelled') NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Exchange rates table
CREATE TABLE IF NOT EXISTS exchange_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    currency VARCHAR(10) NOT NULL,
    price DECIMAL(18, 8) NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_currency (currency)
);

-- Insert initial exchange rates
INSERT INTO exchange_rates (currency, price, updated_at) VALUES
    ('BTC', 50000.00, NOW()),
    ('ETH', 3500.00, NOW()),
    ('USDT', 1.00, NOW()),
    ('BNB', 450.00, NOW()),
    ('SOL', 150.00, NOW()),
    ('XRP', 1.10, NOW()),
    ('ADA', 1.20, NOW()),
    ('DOGE', 0.12, NOW()),
    ('DOT', 20.00, NOW()),
    ('AVAX', 30.00, NOW());

-- Insert admin user
INSERT INTO users (username, email, password, full_name, role, created_at, updated_at) VALUES
    ('admin', 'admin@bitrader.com', '$2y$10$OgFfyWhkKkwwzSl.L5EGWeE/sE4tMNBFZ06soB0JyfKQM/FNcQCr.', 'Admin User', 'admin', NOW(), NOW());

-- Create admin wallets
INSERT INTO wallets (user_id, currency, balance, created_at, updated_at) VALUES
    (1, 'USD', 100000.00, NOW(), NOW()),
    (1, 'BTC', 10.00, NOW(), NOW()),
    (1, 'ETH', 50.00, NOW(), NOW());

-- Create demo user
INSERT INTO users (username, email, password, full_name, role, created_at, updated_at) VALUES
    ('demo', 'demo@bitrader.com', '$2y$10$OgFfyWhkKkwwzSl.L5EGWeE/sE4tMNBFZ06soB0JyfKQM/FNcQCr.', 'Demo User', 'user', NOW(), NOW());

-- Create demo wallets
INSERT INTO wallets (user_id, currency, balance, created_at, updated_at) VALUES
    (2, 'USD', 10000.00, NOW(), NOW()),
    (2, 'BTC', 1.00, NOW(), NOW()),
    (2, 'ETH', 5.00, NOW(), NOW());

-- Note: Both admin and demo users have the password 'password123' 