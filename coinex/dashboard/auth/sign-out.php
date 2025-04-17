<?php
/**
 * Sign out script
 * Clears user session and redirects to login page
 */

// Define the path to user_session.php
$projectRoot = $_SERVER['DOCUMENT_ROOT'] . '/PROJECT-BITRADER';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear session data
$_SESSION = array();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Clear auth token cookie
setcookie('auth_token', '', time() - 3600, '/', '', false, true);

// Also clear localStorage via JavaScript
echo '
<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
</head>
<body>
    <script>
        // Clear localStorage auth data
        localStorage.removeItem("authToken");
        localStorage.removeItem("userData");
        
        // Redirect to login page
        window.location.href = "/PROJECT-BITRADER/bitrader.thetork.com/Signup-Signin/index.html";
    </script>
    <p>Logging out...</p>
</body>
</html>
';
exit;
?> 