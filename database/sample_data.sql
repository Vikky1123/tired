-- BiTrader Sample Data

USE bitrader_db;

-- Sample Users (passwords are 'password123' hashed with bcrypt)
INSERT INTO users (username, email, password, full_name, phone, user_role, is_verified, is_active) VALUES
('johndoe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', '+1234567890', 'user', TRUE, TRUE),
('janedoe', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Doe', '+0987654321', 'user', TRUE, TRUE),
('mikesmith', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Smith', '+1122334455', 'user', TRUE, TRUE),
('sarahjones', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Jones', '+5566778899', 'user', TRUE, TRUE);

-- Create wallets for users
INSERT INTO wallets (user_id, currency, balance, wallet_address) VALUES
-- John Doe wallets
(2, 'USD', 10000.00, 'wallet_usd_john'),
(2, 'BTC', 0.5, 'wallet_btc_john'),
(2, 'ETH', 5.0, 'wallet_eth_john'),
(2, 'DASH', 25.0, 'wallet_dash_john'),
-- Jane Doe wallets
(3, 'USD', 15000.00, 'wallet_usd_jane'),
(3, 'BTC', 0.75, 'wallet_btc_jane'),
(3, 'ETH', 8.0, 'wallet_eth_jane'),
(3, 'DASH', 35.0, 'wallet_dash_jane'),
-- Mike Smith wallets
(4, 'USD', 5000.00, 'wallet_usd_mike'),
(4, 'BTC', 0.25, 'wallet_btc_mike'),
(4, 'ETH', 3.0, 'wallet_eth_mike'),
(4, 'DASH', 15.0, 'wallet_dash_mike'),
-- Sarah Jones wallets
(5, 'USD', 20000.00, 'wallet_usd_sarah'),
(5, 'BTC', 1.0, 'wallet_btc_sarah'),
(5, 'ETH', 10.0, 'wallet_eth_sarah'),
(5, 'DASH', 50.0, 'wallet_dash_sarah');

-- Sample wallet transactions
INSERT INTO wallet_transactions (transaction_hash, from_user_id, to_user_id, from_wallet_id, to_wallet_id, amount, fee, currency, transaction_type, status, description) VALUES
-- Deposits
('tx_dep_john_1', NULL, 2, NULL, 2, 5000.00, 0.00, 'USD', 'deposit', 'completed', 'Initial deposit'),
('tx_dep_john_2', NULL, 2, NULL, 2, 5000.00, 0.00, 'USD', 'deposit', 'completed', 'Additional deposit'),
('tx_dep_jane_1', NULL, 3, NULL, 4, 15000.00, 0.00, 'USD', 'deposit', 'completed', 'Initial deposit'),
('tx_dep_mike_1', NULL, 4, NULL, 7, 5000.00, 0.00, 'USD', 'deposit', 'completed', 'Initial deposit'),
('tx_dep_sarah_1', NULL, 5, NULL, 10, 20000.00, 0.00, 'USD', 'deposit', 'completed', 'Initial deposit'),

-- Crypto purchases (from exchange)
('tx_buy_btc_john', NULL, 2, NULL, 3, 0.5, 0.01, 'BTC', 'deposit', 'completed', 'BTC purchase from exchange'),
('tx_buy_eth_john', NULL, 2, NULL, 3, 5.0, 0.1, 'ETH', 'deposit', 'completed', 'ETH purchase from exchange'),
('tx_buy_dash_john', NULL, 2, NULL, 4, 25.0, 0.5, 'DASH', 'deposit', 'completed', 'DASH purchase from exchange'),
('tx_buy_btc_jane', NULL, 3, NULL, 5, 0.75, 0.015, 'BTC', 'deposit', 'completed', 'BTC purchase from exchange'),
('tx_buy_eth_jane', NULL, 3, NULL, 6, 8.0, 0.16, 'ETH', 'deposit', 'completed', 'ETH purchase from exchange'),
('tx_buy_dash_jane', NULL, 3, NULL, 7, 35.0, 0.7, 'DASH', 'deposit', 'completed', 'DASH purchase from exchange'),
('tx_buy_btc_mike', NULL, 4, NULL, 8, 0.25, 0.005, 'BTC', 'deposit', 'completed', 'BTC purchase from exchange'),
('tx_buy_eth_mike', NULL, 4, NULL, 9, 3.0, 0.06, 'ETH', 'deposit', 'completed', 'ETH purchase from exchange'),
('tx_buy_dash_mike', NULL, 4, NULL, 10, 15.0, 0.3, 'DASH', 'deposit', 'completed', 'DASH purchase from exchange'),
('tx_buy_btc_sarah', NULL, 5, NULL, 11, 1.0, 0.02, 'BTC', 'deposit', 'completed', 'BTC purchase from exchange'),
('tx_buy_eth_sarah', NULL, 5, NULL, 12, 10.0, 0.2, 'ETH', 'deposit', 'completed', 'ETH purchase from exchange'),
('tx_buy_dash_sarah', NULL, 5, NULL, 13, 50.0, 1.0, 'DASH', 'deposit', 'completed', 'DASH purchase from exchange'),

-- Transfers between users
('tx_transfer_1', 2, 3, 2, 4, 1000.00, 10.00, 'USD', 'transfer', 'completed', 'Payment for services'),
('tx_transfer_2', 3, 5, 5, 11, 0.1, 0.001, 'BTC', 'transfer', 'completed', 'Repayment of loan'),
('tx_transfer_3', 5, 4, 12, 9, 1.0, 0.01, 'ETH', 'transfer', 'completed', 'Gift');

-- Sample trades
INSERT INTO trades (user_id, trade_type, crypto_currency, amount, price, total_value, status) VALUES
-- John's trades
(2, 'buy', 'BTC', 0.1, 48000.00, 4800.00, 'completed'),
(2, 'buy', 'ETH', 2.0, 3300.00, 6600.00, 'completed'),
(2, 'buy', 'DASH', 10.0, 115.00, 1150.00, 'completed'),
(2, 'sell', 'BTC', 0.05, 52000.00, 2600.00, 'completed'),
-- Jane's trades
(3, 'buy', 'BTC', 0.2, 47500.00, 9500.00, 'completed'),
(3, 'buy', 'ETH', 3.0, 3350.00, 10050.00, 'completed'),
(3, 'buy', 'DASH', 15.0, 118.00, 1770.00, 'completed'),
(3, 'sell', 'ETH', 1.0, 3600.00, 3600.00, 'completed'),
-- Mike's trades
(4, 'buy', 'BTC', 0.15, 49000.00, 7350.00, 'completed'),
(4, 'buy', 'DASH', 5.0, 120.00, 600.00, 'completed'),
(4, 'sell', 'BTC', 0.05, 51000.00, 2550.00, 'completed'),
-- Sarah's trades
(5, 'buy', 'ETH', 5.0, 3250.00, 16250.00, 'completed'),
(5, 'buy', 'DASH', 20.0, 122.00, 2440.00, 'completed'),
(5, 'sell', 'BTC', 0.2, 50500.00, 10100.00, 'completed'),
(5, 'buy', 'BTC', 0.5, 49500.00, 24750.00, 'completed');

-- Sample user investments - Using DATETIME for end_date
INSERT INTO user_investments (user_id, plan_id, amount, start_date, end_date, status, expected_return) VALUES
(2, 1, 500.00, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_ADD(DATE_SUB(NOW(), INTERVAL 20 DAY), INTERVAL 30 DAY), 'active', 525.00),
(3, 2, 5000.00, DATE_SUB(NOW(), INTERVAL 40 DAY), DATE_ADD(DATE_SUB(NOW(), INTERVAL 40 DAY), INTERVAL 60 DAY), 'active', 5400.00),
(4, 1, 800.00, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_ADD(DATE_SUB(NOW(), INTERVAL 15 DAY), INTERVAL 30 DAY), 'active', 840.00),
(5, 3, 15000.00, DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_ADD(DATE_SUB(NOW(), INTERVAL 30 DAY), INTERVAL 90 DAY), 'active', 16800.00);

-- Sample KYC information
INSERT INTO kyc_information (user_id, document_type, document_number, document_front_path, document_back_path, selfie_path, verification_status) VALUES
(2, 'passport', 'P123456789', '/uploads/kyc/john_passport_front.jpg', '/uploads/kyc/john_passport_back.jpg', '/uploads/kyc/john_selfie.jpg', 'verified'),
(3, 'national_id', 'ID987654321', '/uploads/kyc/jane_id_front.jpg', '/uploads/kyc/jane_id_back.jpg', '/uploads/kyc/jane_selfie.jpg', 'verified'),
(4, 'drivers_license', 'DL123456789', '/uploads/kyc/mike_license_front.jpg', '/uploads/kyc/mike_license_back.jpg', '/uploads/kyc/mike_selfie.jpg', 'pending'),
(5, 'passport', 'P987654321', '/uploads/kyc/sarah_passport_front.jpg', '/uploads/kyc/sarah_passport_back.jpg', '/uploads/kyc/sarah_selfie.jpg', 'verified');

-- Sample support tickets
INSERT INTO support_tickets (user_id, subject, description, status, priority) VALUES
(2, 'Withdrawal Issue', 'I submitted a withdrawal request 2 days ago but it is still pending. Can you please check?', 'open', 'medium'),
(3, 'Account Verification', 'I submitted my documents for verification 3 days ago. When will it be completed?', 'in_progress', 'medium'),
(4, 'Failed Transaction', 'I tried to make a trade but it failed. The funds were deducted from my account.', 'open', 'high'),
(5, 'Password Reset', 'I need to reset my password but I am not receiving the reset email.', 'resolved', 'low');

-- Sample ticket replies
INSERT INTO ticket_replies (ticket_id, user_id, admin_id, message) VALUES
(1, 2, NULL, 'My withdrawal has been pending for 2 days now. Please help.'),
(1, NULL, 1, 'We are checking your withdrawal request. It should be processed within 24 hours.'),
(2, 3, NULL, 'Can you please expedite my verification process?'),
(2, NULL, 1, 'Your documents are being reviewed. We will update you once the verification is complete.'),
(3, 4, NULL, 'The transaction failed but the funds were deducted from my account.'),
(4, 5, NULL, 'I am not receiving the password reset email.'),
(4, NULL, 1, 'We have sent a new password reset email. Please check your spam folder as well.');

-- Sample notifications
INSERT INTO notifications (user_id, title, message, type, is_read) VALUES
(2, 'Welcome to BiTrader', 'Thank you for joining BiTrader. Complete your profile to get started.', 'info', TRUE),
(2, 'KYC Verified', 'Your KYC verification is complete. You can now access all features.', 'success', TRUE),
(2, 'New Login', 'New login detected from a different device. If this was not you, please contact support.', 'warning', FALSE),
(3, 'Welcome to BiTrader', 'Thank you for joining BiTrader. Complete your profile to get started.', 'info', TRUE),
(3, 'KYC Verified', 'Your KYC verification is complete. You can now access all features.', 'success', TRUE),
(3, 'Investment Maturity', 'Your investment plan will mature in 7 days.', 'info', FALSE),
(4, 'Welcome to BiTrader', 'Thank you for joining BiTrader. Complete your profile to get started.', 'info', TRUE),
(4, 'KYC Pending', 'Your KYC documents are pending verification. We will notify you once verified.', 'info', FALSE),
(5, 'Welcome to BiTrader', 'Thank you for joining BiTrader. Complete your profile to get started.', 'info', TRUE),
(5, 'KYC Verified', 'Your KYC verification is complete. You can now access all features.', 'success', TRUE),
(5, 'High Value Trade', 'You have made a high value trade. Please review the transaction details.', 'warning', FALSE); 