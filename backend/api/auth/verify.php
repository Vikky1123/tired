<?php
/**
 * Token Verification API Endpoint
 * Validates a JWT token and returns user data if valid
 */

// Include necessary files
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../utils/api_utils.php';
require_once __DIR__ . '/../../utils/auth_utils.php';

// Only allow GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse('Method not allowed', 405);
    exit;
}

// Check for Authorization header
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    sendUnauthorizedResponse('No authorization token provided');
    exit;
}

// Extract token from Bearer
$authHeader = $headers['Authorization'];
$token = null;

if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
} else {
    sendUnauthorizedResponse('Invalid authorization format');
    exit;
}

// Validate token
$payload = validateJWT($token);

if (!$payload) {
    sendUnauthorizedResponse('Invalid or expired token');
    exit;
}

// Get user by ID
$user = User::getById($payload['uid']);

if (!$user) {
    sendUnauthorizedResponse('User not found');
    exit;
}

// Return success response with user data
sendSuccessResponse([
    'user' => $user
], 'Token is valid'); 