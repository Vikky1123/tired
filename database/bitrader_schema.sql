-- BiTrader Database Schema

-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS bitrader_db;
USE bitrader_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    user_role ENUM('user', 'admin') DEFAULT 'user',
    is_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(100),
    reset_token VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Exchange rates table
CREATE TABLE IF NOT EXISTS exchange_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    currency VARCHAR(10) NOT NULL,
    price DECIMAL(18, 8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Wallets table
CREATE TABLE IF NOT EXISTS wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    currency VARCHAR(10) NOT NULL,
    balance DECIMAL(18, 8) DEFAULT 0,
    wallet_address VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_currency (user_id, currency)
);

-- Wallet transactions table
CREATE TABLE IF NOT EXISTS wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_hash VARCHAR(100) UNIQUE,
    from_user_id INT,
    to_user_id INT,
    from_wallet_id INT,
    to_wallet_id INT,
    amount DECIMAL(18, 8) NOT NULL,
    fee DECIMAL(18, 8) DEFAULT 0,
    currency VARCHAR(10) NOT NULL,
    transaction_type ENUM('deposit', 'withdraw', 'transfer', 'trade', 'fee') NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (from_wallet_id) REFERENCES wallets(id) ON DELETE SET NULL,
    FOREIGN KEY (to_wallet_id) REFERENCES wallets(id) ON DELETE SET NULL
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
    status ENUM('pending', 'completed', 'cancelled', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Investment plans table
CREATE TABLE IF NOT EXISTS investment_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    min_amount DECIMAL(18, 2) NOT NULL,
    max_amount DECIMAL(18, 2),
    duration_days INT NOT NULL,
    interest_rate DECIMAL(5, 2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User investments table
CREATE TABLE IF NOT EXISTS user_investments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    amount DECIMAL(18, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date DATETIME NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    expected_return DECIMAL(18, 2),
    actual_return DECIMAL(18, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES investment_plans(id) ON DELETE CASCADE
);

-- User account summary view
-- This view provides quick access to user balance information for the dashboard
CREATE OR REPLACE VIEW user_account_summary AS
SELECT 
    u.id AS user_id,
    u.username,
    u.full_name,
    -- Total Balance (all currencies converted to USD)
    (
        SELECT COALESCE(SUM(w.balance * 
            CASE 
                WHEN w.currency = 'USD' THEN 1 
                ELSE (SELECT price FROM exchange_rates WHERE currency = w.currency ORDER BY updated_at DESC LIMIT 1)
            END), 0)
        FROM wallets w 
        WHERE w.user_id = u.id
    ) AS total_balance,
    
    -- Total Profit (simplified calculation)
    (
        SELECT COALESCE(
            (SELECT COALESCE(SUM(w.balance * 
                CASE 
                    WHEN w.currency = 'USD' THEN 1 
                    ELSE (SELECT price FROM exchange_rates WHERE currency = w.currency ORDER BY updated_at DESC LIMIT 1)
                END), 0)
            FROM wallets w 
            WHERE w.user_id = u.id)
            -
            (SELECT COALESCE(SUM(
                CASE WHEN transaction_type = 'deposit' AND status = 'completed' THEN 
                    amount 
                ELSE 0 
                END), 0) 
            FROM wallet_transactions 
            WHERE to_user_id = u.id)
            +
            (SELECT COALESCE(SUM(
                CASE WHEN transaction_type = 'withdraw' AND status = 'completed' THEN 
                    amount 
                ELSE 0 
                END), 0) 
            FROM wallet_transactions 
            WHERE from_user_id = u.id)
        , 0)
    ) AS total_profit,
    
    -- Total Deposit
    (
        SELECT COALESCE(SUM(
            CASE WHEN transaction_type = 'deposit' AND status = 'completed' THEN 
                amount 
            ELSE 0 
            END), 0) 
        FROM wallet_transactions 
        WHERE to_user_id = u.id
    ) AS total_deposit,
    
    -- Total Withdrawal
    (
        SELECT COALESCE(SUM(
            CASE WHEN transaction_type = 'withdraw' AND status = 'completed' THEN 
                amount 
            ELSE 0 
            END), 0) 
        FROM wallet_transactions 
        WHERE from_user_id = u.id
    ) AS total_withdrawal
FROM 
    users u;

-- Create an admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, user_role, is_verified, is_active)
VALUES ('admin', 'admin@bitrader.com', '$2y$10$PJBuZ8PGTnvP.Ug1KqF1Q.tRtw4RH0a2RcyWQ0w/VXCsZYgAQ2mcy', 'Admin User', 'admin', TRUE, TRUE);

-- Insert exchange rates for cryptocurrencies shown in the dashboard
INSERT INTO exchange_rates (currency, price) VALUES 
('BTC', 50000.00),
('ETH', 3500.00),
('USDT', 1.00),
('DASH', 120.00),
('BNB', 450.00),
('SOL', 150.00),
('XRP', 1.10),
('ADA', 1.20),
('DOGE', 0.12),
('DOT', 20.00),
('AVAX', 30.00);

-- Create default investment plans
INSERT INTO investment_plans (name, description, min_amount, max_amount, duration_days, interest_rate, is_active)
VALUES 
('Starter Plan', 'Perfect for beginners to cryptocurrency investment', 100, 1000, 30, 5.00, TRUE),
('Growth Plan', 'Balanced risk and reward for intermediate investors', 1000, 10000, 60, 8.00, TRUE),
('Premium Plan', 'High returns for experienced investors', 10000, 100000, 90, 12.00, TRUE),
('Elite Plan', 'Maximum returns for high-value investors', 100000, NULL, 180, 18.00, TRUE); 