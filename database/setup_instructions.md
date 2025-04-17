# BiTrader Database Setup Instructions

This document provides instructions for setting up the BiTrader database using XAMPP to support the dashboard balances shown in the application.

## Prerequisites
- XAMPP installed and running
- Apache and MySQL services started

## Setup Steps

### 1. Access phpMyAdmin
1. Start XAMPP Control Panel
2. Start Apache and MySQL services
3. Click on "Admin" button next to MySQL, or navigate to `http://localhost/phpmyadmin` in your browser

### 2. Create Database
1. In phpMyAdmin, click on "New" in the left sidebar
2. Enter "bitrader" as the database name
3. Select "utf8mb4_general_ci" as the collation
4. Click "Create"

### 3. Import Database Schema
1. Select the "bitrader" database from the left sidebar
2. Click on the "Import" tab at the top
3. Click "Browse" and navigate to the `database/bitrader_schema.sql` file
4. Scroll down and click "Import"

### 4. Import Sample Data (Optional)
To populate the database with sample data for testing:
1. Select the "bitrader" database from the left sidebar
2. Click on the "Import" tab at the top
3. Click "Browse" and navigate to the `database/sample_data.sql` file
4. Scroll down and click "Import"

### 5. Verify Dashboard Data
After import, you should be able to query the user account information that powers the dashboard:
```sql
SELECT * FROM user_account_summary;
```

The view should provide the following fields for each user:
- `user_id` - User identifier
- `username` - User login name
- `full_name` - User's full name
- `total_balance` - Total value of all wallets (converted to USD)
- `total_profit` - Calculated profit
- `total_deposit` - Sum of all deposit transactions
- `total_withdrawal` - Sum of all withdrawal transactions

### 6. Default Admin Account
The database comes with a default admin account:
- Username: admin
- Password: admin123
- Email: admin@bitrader.com

### 7. Cryptocurrency Wallets
For the cryptocurrency balances shown in the dashboard (Bitcoin, Dash, Ethereum), you can check the individual wallet balances with:
```sql
SELECT u.username, w.currency, w.balance, w.balance * e.price as usd_value 
FROM wallets w 
JOIN users u ON w.user_id = u.id
JOIN exchange_rates e ON w.currency = e.currency
ORDER BY u.id, w.currency;
```

## Note on XAMPP Environment
The BiTrader project is configured to run in an XAMPP environment. The backend PHP files will access this database using the root MySQL user by default. If you've set a password for your MySQL root user, you'll need to update the database configuration in `backend/utils/db_utils.php`. 