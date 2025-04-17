-- Dashboard-specific tables for BiTrader
-- These tables are streamlined specifically for the data shown in the dashboard UI

-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS bitrader_db;
USE bitrader_db;

-- Users table (simplified)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User Account Summary table - directly stores the 4 main values shown in dashboard
CREATE TABLE IF NOT EXISTS user_account_summary (
    user_id INT PRIMARY KEY,
    total_balance DECIMAL(18, 2) DEFAULT 0.00,
    total_profit DECIMAL(18, 2) DEFAULT 0.00,
    total_deposit DECIMAL(18, 2) DEFAULT 0.00,
    total_withdrawal DECIMAL(18, 2) DEFAULT 0.00,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Cryptocurrency Prices table
CREATE TABLE IF NOT EXISTS crypto_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    currency VARCHAR(10) NOT NULL UNIQUE,
    price DECIMAL(18, 2) NOT NULL,
    trend ENUM('up', 'down', 'neutral') DEFAULT 'neutral',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User Cryptocurrency Balances - specifically for the 3 currencies shown in dashboard
CREATE TABLE IF NOT EXISTS user_crypto_balances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    currency VARCHAR(10) NOT NULL,
    balance DECIMAL(18, 8) DEFAULT 0,
    usd_value DECIMAL(18, 2) DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_currency (user_id, currency)
);

-- Insert initial data for cryptocurrency prices (using IGNORE to prevent duplicate errors)
INSERT IGNORE INTO crypto_prices (currency, price, trend) VALUES 
('BTC', 50000.00, 'up'),
('ETH', 3500.00, 'up'),
('DASH', 120.00, 'up');

-- Alternative method with conditional insert
-- Only insert BTC if it doesn't exist
SET @btc_exists = (SELECT COUNT(*) FROM crypto_prices WHERE currency = 'BTC');
SET @eth_exists = (SELECT COUNT(*) FROM crypto_prices WHERE currency = 'ETH');
SET @dash_exists = (SELECT COUNT(*) FROM crypto_prices WHERE currency = 'DASH');

INSERT INTO crypto_prices (currency, price, trend)
SELECT 'BTC', 50000.00, 'up' FROM dual WHERE @btc_exists = 0;

INSERT INTO crypto_prices (currency, price, trend)
SELECT 'ETH', 3500.00, 'up' FROM dual WHERE @eth_exists = 0;

INSERT INTO crypto_prices (currency, price, trend)
SELECT 'DASH', 120.00, 'up' FROM dual WHERE @dash_exists = 0;

-- Create sample user if not exists
INSERT IGNORE INTO users (username, email, password, full_name, is_active) VALUES
('demo_user', 'demo@bitrader.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo User', TRUE);

-- Create sample account summary (all zeros as shown in the image)
INSERT IGNORE INTO user_account_summary (user_id, total_balance, total_profit, total_deposit, total_withdrawal) VALUES
(1, 0.00, 0.00, 0.00, 0.00);

-- Create sample cryptocurrency balances (all zeros as shown in the image)
INSERT IGNORE INTO user_crypto_balances (user_id, currency, balance, usd_value) VALUES
(1, 'BTC', 0, 0),
(1, 'ETH', 0, 0),
(1, 'DASH', 0, 0);

-- Create triggers to auto-calculate USD values when balance changes
DELIMITER //

CREATE TRIGGER IF NOT EXISTS update_btc_usd_value BEFORE INSERT ON user_crypto_balances
FOR EACH ROW
BEGIN
    IF NEW.currency = 'BTC' THEN
        SET NEW.usd_value = NEW.balance * (SELECT price FROM crypto_prices WHERE currency = 'BTC');
    END IF;
END //

CREATE TRIGGER IF NOT EXISTS update_eth_usd_value BEFORE INSERT ON user_crypto_balances
FOR EACH ROW
BEGIN
    IF NEW.currency = 'ETH' THEN
        SET NEW.usd_value = NEW.balance * (SELECT price FROM crypto_prices WHERE currency = 'ETH');
    END IF;
END //

CREATE TRIGGER IF NOT EXISTS update_dash_usd_value BEFORE INSERT ON user_crypto_balances
FOR EACH ROW
BEGIN
    IF NEW.currency = 'DASH' THEN
        SET NEW.usd_value = NEW.balance * (SELECT price FROM crypto_prices WHERE currency = 'DASH');
    END IF;
END //

CREATE TRIGGER IF NOT EXISTS update_btc_usd_value_on_update BEFORE UPDATE ON user_crypto_balances
FOR EACH ROW
BEGIN
    IF NEW.currency = 'BTC' AND (NEW.balance != OLD.balance OR NEW.usd_value = 0) THEN
        SET NEW.usd_value = NEW.balance * (SELECT price FROM crypto_prices WHERE currency = 'BTC');
    END IF;
END //

CREATE TRIGGER IF NOT EXISTS update_eth_usd_value_on_update BEFORE UPDATE ON user_crypto_balances
FOR EACH ROW
BEGIN
    IF NEW.currency = 'ETH' AND (NEW.balance != OLD.balance OR NEW.usd_value = 0) THEN
        SET NEW.usd_value = NEW.balance * (SELECT price FROM crypto_prices WHERE currency = 'ETH');
    END IF;
END //

CREATE TRIGGER IF NOT EXISTS update_dash_usd_value_on_update BEFORE UPDATE ON user_crypto_balances
FOR EACH ROW
BEGIN
    IF NEW.currency = 'DASH' AND (NEW.balance != OLD.balance OR NEW.usd_value = 0) THEN
        SET NEW.usd_value = NEW.balance * (SELECT price FROM crypto_prices WHERE currency = 'DASH');
    END IF;
END //

DELIMITER ;

-- Create stored procedure to refresh all dashboard data for a user
DROP PROCEDURE IF EXISTS refresh_dashboard_data;
DELIMITER //
CREATE PROCEDURE refresh_dashboard_data(IN p_user_id INT)
BEGIN
    -- Update USD values for all cryptocurrencies
    UPDATE user_crypto_balances ucb
    JOIN crypto_prices cp ON ucb.currency = cp.currency
    SET ucb.usd_value = ucb.balance * cp.price,
        ucb.last_updated = NOW()
    WHERE ucb.user_id = p_user_id;
    
    -- Update account summary based on crypto balances
    UPDATE user_account_summary
    SET total_balance = (
            SELECT COALESCE(SUM(usd_value), 0)
            FROM user_crypto_balances
            WHERE user_id = p_user_id
        ),
        last_updated = NOW()
    WHERE user_id = p_user_id;
END //
DELIMITER ;

-- Create view to get complete dashboard data
DROP VIEW IF EXISTS dashboard_view;
CREATE VIEW dashboard_view AS
SELECT 
    u.id AS user_id,
    u.username,
    u.full_name,
    s.total_balance,
    s.total_profit,
    s.total_deposit,
    s.total_withdrawal,
    s.last_updated AS summary_updated,
    (SELECT JSON_OBJECTAGG(currency, JSON_OBJECT(
        'balance', balance, 
        'usd_value', usd_value,
        'trend', (SELECT trend FROM crypto_prices cp WHERE cp.currency = cb.currency)
    ))
    FROM user_crypto_balances cb
    WHERE cb.user_id = u.id) AS crypto_balances
FROM 
    users u
JOIN 
    user_account_summary s ON u.id = s.user_id;

-- Sample queries to get dashboard data

-- 1. Get the 4 main account values
SELECT 
    FORMAT(total_balance, 2) AS total_balance,
    FORMAT(total_profit, 2) AS total_profit,
    FORMAT(total_deposit, 2) AS total_deposit,
    FORMAT(total_withdrawal, 2) AS total_withdrawal
FROM 
    user_account_summary
WHERE 
    user_id = 1;

-- 2. Get individual crypto balances
SELECT 
    cb.currency,
    cb.balance,
    CONCAT('$', FORMAT(cb.usd_value, 2)) AS usd_value,
    cp.trend
FROM 
    user_crypto_balances cb
JOIN 
    crypto_prices cp ON cb.currency = cp.currency
WHERE 
    cb.user_id = 1
ORDER BY 
    cb.currency; 