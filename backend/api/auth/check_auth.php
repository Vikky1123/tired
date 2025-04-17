<?php
/**
 * Authentication check utility
 * Used by API endpoints and pages to verify that a user is logged in
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include JWT utilities
require_once __DIR__ . '/../../utils/jwt_utils.php';

/**
 * Check if user is authenticated
 * 
 * @return array|bool Returns user data if authenticated, false otherwise
 */
function isAuthenticated() {
    // First, check for JWT token in Authorization header
    $headers = getallheaders();
    $token = null;
    
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        if (strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
        }
    }
    
    // If no token in header, check for token in session
    if (!$token && isset($_SESSION['token'])) {
        $token = $_SESSION['token'];
    }
    
    // If no token in session, check for token in cookie
    if (!$token && isset($_COOKIE['auth_token'])) {
        $token = $_COOKIE['auth_token'];
    }
    
    // If token found, validate it
    if ($token) {
        try {
            $userData = decodeJWT($token);
            if ($userData) {
                return $userData;
            }
        } catch (Exception $e) {
            // Token validation failed
            return false;
        }
    }
    
    return false;
}

/**
 * Require authentication
 * If not authenticated, redirects to login page or returns error JSON response
 * 
 * @param bool $apiResponse Whether to return JSON response (for APIs) or redirect
 * @return array|void Returns user data if authenticated, otherwise redirects or returns error
 */
function requireAuthentication($apiResponse = false) {
    $userData = isAuthenticated();
    
    if (!$userData) {
        if ($apiResponse) {
            // Return JSON error for API calls
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required',
                'code' => 401
            ]);
            exit;
        } else {
            // Redirect to login page for regular pages
            header('Location: /PROJECT-BITRADER/bitrader.thetork.com/Signup-Signin/index.html');
            exit;
        }
    }
    
    return $userData;
}

/**
 * Set authentication token
 * 
 * @param string $token JWT token
 * @param int $expiresIn Expiration time in seconds
 */
function setAuthToken($token, $expiresIn = 86400) {
    $_SESSION['token'] = $token;
    
    // Also set as cookie for cross-page compatibility
    setcookie('auth_token', $token, time() + $expiresIn, '/', '', false, true);
}

/**
 * Clear authentication token
 */
function clearAuthToken() {
    unset($_SESSION['token']);
    setcookie('auth_token', '', time() - 3600, '/', '', false, true);
}
?> 