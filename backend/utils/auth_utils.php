<?php
/**
 * Authentication Utilities
 */

// Include environment loader if not already loaded
if (!function_exists('loadEnv')) {
    require_once __DIR__ . '/../config/env.php';
}

// Secret key for JWT token - only define if not already defined
if (!defined('JWT_SECRET')) {
    define('JWT_SECRET', getenv('JWT_SECRET') ?: 'H7fP9tR3mK5xL2qY8sZ6vB4nG1jD0cE3wA7bV9');
}

/**
 * Generate a JWT token
 * 
 * @param array $payload Data to be encoded in the token
 * @return string The generated JWT token
 */
function generateJWT($payload) {
    // Header
    $header = json_encode([
        'typ' => 'JWT',
        'alg' => 'HS256'
    ]);
    
    // Encode Header
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    
    // Set expiry time (1 day from now)
    $payload['exp'] = time() + 86400;
    
    // Encode Payload
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
    
    // Create Signature
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    
    // Encode Signature
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    // Create JWT
    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    
    return $jwt;
}

/**
 * Validate a JWT token
 * 
 * @param string $jwt The JWT token to validate
 * @return array|false The decoded payload if valid, false otherwise
 */
function validateJWT($jwt) {
    // Split the token
    $tokenParts = explode('.', $jwt);
    
    if (count($tokenParts) != 3) {
        return false;
    }
    
    $header = base64_decode($tokenParts[0]);
    $payload = json_decode(base64_decode($tokenParts[1]), true);
    
    // Check if token is expired
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return false;
    }
    
    // Verify signature
    $base64UrlHeader = $tokenParts[0];
    $base64UrlPayload = $tokenParts[1];
    $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[2]));
    
    $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    
    if ($signature !== $expectedSignature) {
        return false;
    }
    
    return $payload;
}

/**
 * Check if user is authenticated based on JWT in Authorization header
 * 
 * @return array|false User data if authenticated, false otherwise
 */
function authenticateUser() {
    // Get all headers
    $headers = getallheaders();
    
    // Check for Authorization header
    if (!isset($headers['Authorization'])) {
        return false;
    }
    
    // Extract token from Bearer
    $authHeader = $headers['Authorization'];
    $token = null;
    
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    }
    
    if (!$token) {
        return false;
    }
    
    // Validate token
    $payload = validateJWT($token);
    
    return $payload;
}

/**
 * Hash a password
 * 
 * @param string $password The password to hash
 * @return string The hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify a password against a hash
 * 
 * @param string $password The password to verify
 * @param string $hash The hash to verify against
 * @return bool True if the password matches the hash, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
} 