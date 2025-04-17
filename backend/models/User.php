<?php
/**
 * User Model
 */
require_once __DIR__ . '/../utils/db_utils.php';
require_once __DIR__ . '/../utils/auth_utils.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple debug logger
function debug_log($message, $data = null) {
    $log_file = __DIR__ . '/../logs/auth_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] {$message}";
    
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $log_message .= " - " . json_encode($data);
        } else {
            $log_message .= " - " . $data;
        }
    }
    
    // Create logs directory if it doesn't exist
    if (!is_dir(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    // Append to log file
    file_put_contents($log_file, $log_message . PHP_EOL, FILE_APPEND);
}

class User {
    // User properties
    private $id;
    private $username;
    private $email;
    private $password;
    private $fullName;
    private $phone;
    private $country;
    private $role;
    private $createdAt;
    private $updatedAt;
    
    /**
     * Get user by ID
     * 
     * @param int $userId User ID
     * @return array|null User data or null if not found
     */
    public static function getById($userId) {
        debug_log("getById called", $userId);
        $sql = "SELECT id, username, email, full_name, phone, country, role, created_at, updated_at FROM users WHERE id = ?";
        $result = executeQuery($sql, [$userId]);
        
        debug_log("getById result", !empty($result) ? "User found" : "User not found");
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Get user by username
     * 
     * @param string $username Username
     * @return array|null User data or null if not found
     */
    public static function getByUsername($username) {
        debug_log("getByUsername called", $username);
        $sql = "SELECT * FROM users WHERE username = ?";
        $result = executeQuery($sql, [$username]);
        
        debug_log("getByUsername result", !empty($result) ? "User found" : "User not found");
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Get user by email
     * 
     * @param string $email Email address
     * @return array|null User data or null if not found
     */
    public static function getByEmail($email) {
        debug_log("getByEmail called", $email);
        $sql = "SELECT * FROM users WHERE email = ?";
        $result = executeQuery($sql, [$email]);
        
        debug_log("getByEmail result", !empty($result) ? "User found" : "User not found");
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Create a new user
     * 
     * @param array $userData User data (username, email, password, fullName, phone, country, role)
     * @return int|false The inserted ID or false on failure
     */
    public static function create($userData) {
        debug_log("create called", $userData);
        
        try {
            // Hash the password
            $hashedPassword = hashPassword($userData['password']);
            
            $sql = "INSERT INTO users (username, email, password, full_name, phone, country, role, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $params = [
                $userData['username'],
                $userData['email'],
                $hashedPassword,
                $userData['fullName'] ?? null,
                $userData['phone'] ?? null,
                $userData['country'] ?? null,
                $userData['role'] ?? 'user'
            ];
            
            $userId = executeInsert($sql, $params);
            debug_log("User created with ID", $userId);
            return $userId;
        } catch (Exception $e) {
            debug_log("Error creating user", $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update an existing user
     * 
     * @param int $userId User ID
     * @param array $userData User data to update
     * @return bool True on success, false on failure
     */
    public static function update($userId, $userData) {
        $sets = [];
        $params = [];
        
        // Build the SET clause and parameters
        if (isset($userData['username'])) {
            $sets[] = "username = ?";
            $params[] = $userData['username'];
        }
        
        if (isset($userData['email'])) {
            $sets[] = "email = ?";
            $params[] = $userData['email'];
        }
        
        if (isset($userData['password'])) {
            $sets[] = "password = ?";
            $params[] = hashPassword($userData['password']);
        }
        
        if (isset($userData['fullName'])) {
            $sets[] = "full_name = ?";
            $params[] = $userData['fullName'];
        }
        
        if (isset($userData['phone'])) {
            $sets[] = "phone = ?";
            $params[] = $userData['phone'];
        }
        
        if (isset($userData['country'])) {
            $sets[] = "country = ?";
            $params[] = $userData['country'];
        }
        
        if (isset($userData['role'])) {
            $sets[] = "role = ?";
            $params[] = $userData['role'];
        }
        
        // Add the updated_at timestamp
        $sets[] = "updated_at = NOW()";
        
        // If no fields to update, return false
        if (empty($sets)) {
            return false;
        }
        
        // Add the user ID to the parameters
        $params[] = $userId;
        
        $sql = "UPDATE users SET " . implode(", ", $sets) . " WHERE id = ?";
        
        return executeNonQuery($sql, $params) > 0;
    }
    
    /**
     * Delete a user by ID
     * 
     * @param int $userId User ID
     * @return bool True on success, false on failure
     */
    public static function delete($userId) {
        $sql = "DELETE FROM users WHERE id = ?";
        return executeNonQuery($sql, [$userId]) > 0;
    }
    
    /**
     * Authenticate a user with username/email and password
     * 
     * @param string $usernameOrEmail Username or email
     * @param string $password Password
     * @return array|false User data on success, false on failure
     */
    public static function authenticate($usernameOrEmail, $password) {
        debug_log("authenticate called", ["username" => $usernameOrEmail, "password_length" => strlen($password)]);
        
        try {
            // Check if input is an email
            $isEmail = filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL);
            debug_log("Input is an email", $isEmail);
            
            if ($isEmail) {
                $user = self::getByEmail($usernameOrEmail);
            } else {
                $user = self::getByUsername($usernameOrEmail);
            }
            
            // If user not found
            if (!$user) {
                debug_log("Authentication failed: user not found");
                return false;
            }
            
            debug_log("User found, checking password");
            
            // Check if password matches
            $passwordMatches = verifyPassword($password, $user['password']);
            debug_log("Password verification result", $passwordMatches ? "Password matches" : "Password does not match");
            
            if (!$passwordMatches) {
                return false;
            }
            
            // Remove sensitive data
            unset($user['password']);
            
            debug_log("Authentication successful", ["user_id" => $user['id'], "username" => $user['username']]);
            return $user;
            
        } catch (Exception $e) {
            debug_log("Authentication exception", $e->getMessage());
            // Log the error but don't expose it to the caller
            return false;
        } catch (Error $e) {
            debug_log("Authentication error", $e->getMessage());
            // Log the error but don't expose it to the caller
            return false;
        }
    }
    
    /**
     * Generate a JWT token for a user
     * 
     * @param array $user User data
     * @return string JWT token
     */
    public static function generateToken($user) {
        debug_log("generateToken called", ["user_id" => $user['id']]);
        
        try {
            $payload = [
                'uid' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
            ];
            
            $token = generateJWT($payload);
            debug_log("Token generated successfully");
            return $token;
        } catch (Exception $e) {
            debug_log("Error generating token", $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get all users with optional pagination
     * 
     * @param int $limit Maximum number of users to return
     * @param int $offset Offset for pagination
     * @return array Users data
     */
    public static function getAll($limit = 10, $offset = 0) {
        $sql = "SELECT id, username, email, full_name, phone, country, role, created_at, updated_at 
                FROM users 
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";
        
        return executeQuery($sql, [$limit, $offset]);
    }
    
    /**
     * Count total number of users
     * 
     * @return int Total count of users
     */
    public static function count() {
        $sql = "SELECT COUNT(*) as count FROM users";
        $result = executeQuery($sql);
        
        return isset($result[0]['count']) ? (int)$result[0]['count'] : 0;
    }
    
    /**
     * Verify if a password matches a hash
     * 
     * @param string $password The password to verify
     * @param string $hash The hash to verify against
     * @return bool True if the password matches the hash, false otherwise
     */
    public static function verifyPassword($password, $hash) {
        debug_log("verifyPassword called", "Password length: " . strlen($password) . ", Hash length: " . strlen($hash));
        
        try {
            $result = password_verify($password, $hash);
            debug_log("Password verification result", $result ? "Match" : "No match");
            return $result;
        } catch (Exception $e) {
            debug_log("Password verification exception", $e->getMessage());
            return false;
        }
    }
} 