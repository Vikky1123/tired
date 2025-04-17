# Bitrader Backend API

This is the backend API for the Bitrader cryptocurrency trading platform.

## Setup Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server with mod_rewrite enabled
- XAMPP, WAMP, or MAMP (for local development)

### Database Setup
1. Start MySQL server through XAMPP, WAMP, or MAMP
2. Create a database named `bitrader_db`
3. Import the database schema from `bitrader_db.sql` using phpMyAdmin

### Configuration
1. Open `config/database.php` and update the database connection details if needed
2. Open `utils/auth_utils.php` and change the `JWT_SECRET` to a secure random string

### API Setup
1. Place the entire `backend` folder in your web server's document root
2. Configure Apache to enable .htaccess by setting `AllowOverride All` in your virtual host configuration
3. Restart Apache server

## Environment Configuration

The application now uses a `.env` file for configuration. Follow these steps:

1. Make sure the `.env` file exists in the `backend` directory
2. Set the following variables in the `.env` file:
   ```
   # Database Configuration
   DB_HOST=localhost
   DB_PORT=3306
   DB_USER=your_db_username
   DB_PASS=your_db_password
   DB_NAME=bitrader_db
   
   # JWT Authentication
   JWT_SECRET=your_secure_random_string
   
   # API Settings
   API_URL=http://localhost/backend/api
   CORS_ALLOW_ORIGIN=*
   
   # Debug Mode (set to false in production)
   DEBUG_MODE=true
   ```
3. Make sure the `.env` file is protected and not accessible from the web

**Important:** Never commit the `.env` file to version control. It contains sensitive information.

## API Documentation

### Authentication Endpoints
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `GET /api/auth/verify` - Verify JWT token

### User Endpoints
- `GET /api/users/me` - Get current user profile
- `PUT /api/users/me/update` - Update current user profile
- `PUT /api/users/me/password` - Change password
- `GET /api/users/all` - List all users (admin only)
- `GET /api/users/{userId}` - Get user by ID
- `PUT /api/users/{userId}/update` - Update user (admin only)
- `DELETE /api/users/{userId}/delete` - Delete user (admin only)

### Wallet Endpoints
- `GET /api/wallet/list` - Get user wallets
- `GET /api/wallet/balance` - Get wallet balance
- `POST /api/wallet/deposit` - Deposit funds
- `POST /api/wallet/withdraw` - Withdraw funds
- `POST /api/wallet/transfer` - Transfer funds to another user
- `POST /api/wallet/exchange` - Exchange between currencies
- `GET /api/wallet/transactions` - Get transaction history

### Trading Endpoints
- `GET /api/trades/list` - Get user trades
- `POST /api/trades/create` - Create a new trade
- `POST /api/trades/cancel/{tradeId}` - Cancel a pending trade
- `GET /api/trades/stats` - Get trade statistics
- `GET /api/trades/portfolio` - Get user portfolio

### Market Endpoints
- `GET /api/market/summary` - Get market summary
- `GET /api/market/recent` - Get recent trades
- `GET /api/market/prices` - Get cryptocurrency prices

## Testing the API

### Default User Accounts
- Admin: 
  - Username: `admin`
  - Password: `password123`
- Demo User:
  - Username: `demo`
  - Password: `password123`

### How to Test
1. Use a tool like Postman or cURL to send requests to the API
2. Login with one of the default accounts to get a JWT token
3. Include the token in the Authorization header for subsequent requests
4. Follow the API documentation for specific endpoints

## Security Notes
- Change the JWT secret key before deploying to production
- Update the default user passwords
- Use HTTPS in production to secure data transmission
- Implement rate limiting for production deployments 