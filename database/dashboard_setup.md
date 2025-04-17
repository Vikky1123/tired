# Dashboard Database Setup

This document provides setup instructions for the simplified dashboard database schema designed specifically to support the dashboard UI shown in the image.

## Overview

This simplified schema focuses only on the data needed to display:
- The 4 account summary values (Total Balance, Total Profit, Total Deposit, Total Withdrawal)
- The 3 cryptocurrency balances (Bitcoin, Dash, Ethereum)

## Setup Steps

### 1. Create the Database Schema

1. Start XAMPP and ensure MySQL is running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Import the `dashboard_tables.sql` file:
   - Click on the "Import" tab
   - Click "Browse" and select the file
   - Click "Go" to execute the SQL

The script will:
- Create a database called `bitrader_db`
- Create a minimal `users` table
- Create a `user_account_summary` table for the 4 main values
- Create a `crypto_prices` table for BTC, ETH, and DASH
- Create a `user_crypto_balances` table for user crypto holdings
- Create a view and stored procedure for easier data access
- Insert sample data with zero balances (matching the image)

### 2. Setting Up the API

Place the `dashboard_api.php` file in your web server's document root:

```
C:\xampp\htdocs\PROJECT-BITRADER\database\dashboard_api.php
```

This PHP file provides an API endpoint that returns the exact data needed for the dashboard UI in JSON format.

### 3. Testing the API

Access the API endpoint in your browser:

```
http://localhost/PROJECT-BITRADER/database/dashboard_api.php
```

You should see a JSON response similar to:

```json
{
  "success": true,
  "data": {
    "account_summary": {
      "total_balance": "0.00",
      "total_profit": "0.00",
      "total_deposit": "0.00",
      "total_withdrawal": "0.00"
    },
    "crypto_balances": {
      "BTC": {
        "balance": 0,
        "usd_value": "$0.00",
        "trend": "up"
      },
      "DASH": {
        "balance": 0,
        "usd_value": "$0.00",
        "trend": "up"
      },
      "ETH": {
        "balance": 0,
        "usd_value": "$0.00",
        "trend": "up"
      }
    }
  }
}
```

### 4. Adding Data

To add balance data for a user, run these SQL commands in phpMyAdmin:

```sql
-- First select the bitrader_db database
USE bitrader_db;

-- Update BTC balance
UPDATE user_crypto_balances 
SET balance = 0.5 -- 0.5 BTC
WHERE user_id = 1 AND currency = 'BTC';

-- Update ETH balance
UPDATE user_crypto_balances 
SET balance = 2.5 -- 2.5 ETH
WHERE user_id = 1 AND currency = 'ETH';

-- Update DASH balance
UPDATE user_crypto_balances 
SET balance = 10 -- 10 DASH
WHERE user_id = 1 AND currency = 'DASH';

-- Refresh account summary (this will calculate total_balance based on crypto values)
CALL refresh_dashboard_data(1);
```

### 5. Advantages of This Schema

1. **Performance**: Direct tables for dashboard data with minimal joins
2. **Simplicity**: Schema matches exactly what's displayed in the UI
3. **Automatic Calculations**: USD values are calculated automatically when crypto balances change
4. **Trend Data**: Includes trend indicators for cryptocurrencies

## Database Diagram

```
┌─────────────┐      ┌─────────────────────┐      ┌──────────────┐
│   users     │      │ user_account_summary│      │ crypto_prices│
├─────────────┤      ├─────────────────────┤      ├──────────────┤
│ id          │──┐   │ user_id             │      │ id           │
│ username    │  └──>│ total_balance       │      │ currency     │
│ email       │      │ total_profit        │      │ price        │
│ password    │      │ total_deposit       │      │ trend        │
│ full_name   │      │ total_withdrawal    │      │ last_updated │
│ is_active   │      │ last_updated        │      └──────┬───────┘
└─────────────┘      └─────────────────────┘             │
       ┬                                                  │
       │                                                  │
       │        ┌───────────────────────┐                 │
       │        │  user_crypto_balances │                 │
       │        ├───────────────────────┤                 │
       └───────>│ user_id               │                 │
                │ currency              │<────────────────┘
                │ balance               │
                │ usd_value             │
                │ last_updated          │
                └───────────────────────┘
```

## Important Note

The database name for this project is `bitrader_db`. Always use this name when connecting to the database. 