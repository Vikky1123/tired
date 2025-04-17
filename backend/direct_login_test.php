<?php
/**
 * Direct Login Test
 * This script bypasses the API layer to directly test the User class
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once __DIR__ . '/models/User.php';

echo "<h1>Direct Login Test</h1>";

// Test credentials - change these to match a valid user in your database
$username = "admin";
$password = "admin123";

echo "<p>Testing credentials: username = '$username', password = [HIDDEN]</p>";

try {
    // Try to authenticate
    echo "<p>Calling User::authenticate()...</p>";
    $user = User::authenticate($username, $password);
    
    if ($user) {
        echo "<div style='padding: 15px; background-color: #d4edda; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Authentication successful!</strong>";
        echo "<pre>" . print_r($user, true) . "</pre>";
        
        // Generate token
        $token = User::generateToken($user);
        echo "<p>Generated token: " . substr($token, 0, 20) . "...</p>";
        echo "</div>";
    } else {
        echo "<div style='padding: 15px; background-color: #f8d7da; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Authentication failed!</strong> Invalid username or password.";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div style='padding: 15px; background-color: #f8d7da; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Exception:</strong> " . $e->getMessage();
    echo "</div>";
    
    // Show stack trace
    echo "<h2>Stack Trace:</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<div style='padding: 15px; background-color: #f8d7da; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>PHP Error:</strong> " . $e->getMessage();
    echo "</div>";
    
    // Show stack trace
    echo "<h2>Stack Trace:</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Create a simple form to test different credentials
echo "<h2>Try Different Credentials</h2>";
echo "<form method='post' action=''>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='test_username'>Username:</label><br>";
echo "<input type='text' name='test_username' id='test_username' value='admin' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='test_password'>Password:</label><br>";
echo "<input type='password' name='test_password' id='test_password' value='' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div>";
echo "<button type='submit' name='test_login' style='padding: 8px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;'>Test Login</button>";
echo "</div>";
echo "</form>";

// Process form submission
if (isset($_POST['test_login'])) {
    $test_username = $_POST['test_username'] ?? '';
    $test_password = $_POST['test_password'] ?? '';
    
    echo "<h3>Testing submitted credentials:</h3>";
    echo "<p>Username: $test_username</p>";
    
    try {
        $test_user = User::authenticate($test_username, $test_password);
        
        if ($test_user) {
            echo "<div style='padding: 15px; background-color: #d4edda; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>Authentication successful!</strong>";
            echo "<pre>" . print_r($test_user, true) . "</pre>";
            echo "</div>";
        } else {
            echo "<div style='padding: 15px; background-color: #f8d7da; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>Authentication failed!</strong> Invalid username or password.";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div style='padding: 15px; background-color: #f8d7da; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Exception:</strong> " . $e->getMessage();
        echo "</div>";
    }
}

// Check if User class exists
echo "<h2>Diagnostics</h2>";
echo "<p>User class exists: " . (class_exists('User') ? 'Yes' : 'No') . "</p>";
echo "<p>authenticate method exists: " . (method_exists('User', 'authenticate') ? 'Yes' : 'No') . "</p>";
echo "<p>verifyPassword function exists: " . (function_exists('verifyPassword') ? 'Yes' : 'No') . "</p>";

// Display included files
echo "<h3>Included Files:</h3>";
echo "<pre>" . print_r(get_included_files(), true) . "</pre>";
?> 