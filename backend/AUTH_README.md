# Bitrader Authentication System Setup

This document provides instructions for setting up the Bitrader authentication system.

## Database Setup

1. Make sure you have a MySQL server running (using XAMPP) with the following credentials:
   - Username: `root`
   - Password: (empty)
   - Database name: `bitrader_db`

2. Import the database schema from `bitrader_db.sql`:
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create a database named `bitrader_db` if it doesn't exist
   - Select the database and import the `bitrader_db.sql` file

3. Update the database structure:
   - Navigate to: http://localhost/backend/update_database.php
   - This will add the required columns to the users table

## Authentication Endpoints

The following API endpoints are available for authentication:

1. **Login**
   - URL: `/backend/api/auth/login.php`
   - Method: POST
   - Body: 
     ```json
     {
       "username": "your_username_or_email",
       "password": "your_password"
     }
     ```
   - Response:
     ```json
     {
       "success": true,
       "message": "Login successful",
       "data": {
         "token": "jwt_token_here",
         "user": {
           "id": 1,
           "username": "user1",
           "email": "user@example.com",
           "full_name": "User Name",
           "phone": "1234567890",
           "country": "Country Name",
           "role": "user",
           "created_at": "2024-01-01 00:00:00",
           "updated_at": "2024-01-01 00:00:00"
         }
       }
     }
     ```

2. **Register**
   - URL: `/backend/api/auth/register.php`
   - Method: POST
   - Body:
     ```json
     {
       "username": "new_username",
       "email": "user@example.com",
       "password": "password123",
       "full_name": "User Full Name",
       "phone": "1234567890",
       "country": "Country Name"
     }
     ```
   - Response:
     ```json
     {
       "success": true,
       "message": "Registration successful",
       "data": {
         "token": "jwt_token_here",
         "user": {
           "id": 1,
           "username": "new_username",
           "email": "user@example.com",
           "full_name": "User Full Name",
           "phone": "1234567890",
           "country": "Country Name",
           "role": "user",
           "created_at": "2024-01-01 00:00:00",
           "updated_at": "2024-01-01 00:00:00"
         }
       }
     }
     ```

3. **Verify Token**
   - URL: `/backend/api/auth/verify.php`
   - Method: GET
   - Headers: 
     ```
     Authorization: Bearer jwt_token_here
     ```
   - Response:
     ```json
     {
       "success": true,
       "message": "Token is valid",
       "data": {
         "user": {
           "id": 1,
           "username": "username",
           "email": "user@example.com",
           "full_name": "User Full Name",
           "phone": "1234567890",
           "country": "Country Name",
           "role": "user",
           "created_at": "2024-01-01 00:00:00",
           "updated_at": "2024-01-01 00:00:00"
         }
       }
     }
     ```

## Testing the Authentication

To test if the authentication system is working correctly:

1. Navigate to: http://localhost/backend/test_auth.php
   - This will create a test user and attempt to authenticate
   - Check the output to verify everything is working

2. Test the login/registration forms at:
   - http://localhost/bitrader.thetork.com/Signup-Signin/index.html

## Troubleshooting

If you encounter issues:

1. Check that the database connection is correctly configured in `/backend/config/database.php`
2. Verify that the JWT secret key is set in `/backend/.env`
3. Check your web server error logs for any PHP errors
4. Make sure the `short_open_tag` is enabled in your PHP configuration

## Security Notes

- The system implements secure password hashing with PHP's `password_hash()` function
- JWT tokens are used for maintaining user sessions
- The authentication system includes protection against duplicate usernames/emails
- SQL query parameters are properly sanitized to prevent SQL injection attacks 