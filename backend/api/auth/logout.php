<?php
/**
 * Logout API
 * Clears the user's session and authentication tokens
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include auth utilities
require_once __DIR__ . '/check_auth.php';

// Clear the authentication token
clearAuthToken();

// Clear any other session data
$_SESSION = array();

// Destroy the session
session_destroy();

// Check if this is an API call
$isApi = isset($_GET['api']) && $_GET['api'] === 'true';

if ($isApi) {
    // Return JSON response for API calls
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
} else {
    // For regular page requests, redirect to login page
    header('Location: /PROJECT-BITRADER/bitrader.thetork.com/Signup-Signin/index.html');
}
exit;
?> 