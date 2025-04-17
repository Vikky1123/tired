<?php
/**
 * JWT Secret Checker
 * This script helps verify the JWT secret and test token generation/validation
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>JWT Secret & Token Checker</h1>";
echo "<p>This tool verifies that your JWT configuration is working correctly.</p>";

// Include required files
require_once __DIR__ . '/utils/auth_utils.php';

// Get the JWT secret
$jwtSecret = defined('JWT_SECRET') ? JWT_SECRET : 'Not defined';

// Mask the JWT secret for display
function maskSecret($secret) {
    if (strlen($secret) > 8) {
        return substr($secret, 0, 4) . str_repeat('*', strlen($secret) - 8) . substr($secret, -4);
    } else {
        return '********';
    }
}

// Display JWT configuration
echo "<h2>JWT Configuration</h2>";
echo "<div style='margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;'>";
echo "<strong>JWT_SECRET defined:</strong> " . (defined('JWT_SECRET') ? 'Yes' : 'No') . "<br>";
echo "<strong>JWT_SECRET value:</strong> " . maskSecret($jwtSecret) . "<br>";
echo "<strong>JWT_SECRET length:</strong> " . strlen($jwtSecret) . " characters<br>";
echo "</div>";

// Recommend secret strength
echo "<div style='margin-bottom: 20px; padding: 15px; border-radius: 5px; ";
if (strlen($jwtSecret) >= 32) {
    echo "background-color: #d4edda;'><strong>✅ Good:</strong> Your JWT secret is strong (32+ characters).";
} elseif (strlen($jwtSecret) >= 16) {
    echo "background-color: #fff3cd;'><strong>⚠️ Warning:</strong> Your JWT secret should be at least 32 characters for better security.";
} else {
    echo "background-color: #f8d7da;'><strong>❌ Weak:</strong> Your JWT secret is too short. It should be at least 32 characters.";
}
echo "</div>";

// Test JWT token generation and validation
echo "<h2>JWT Token Test</h2>";

if (function_exists('generateJWT') && function_exists('validateJWT')) {
    // Create a test payload
    $testPayload = [
        'user_id' => 1,
        'username' => 'test_user',
        'role' => 'user',
        'test' => true
    ];
    
    try {
        // Generate a test token
        $token = generateJWT($testPayload);
        
        echo "<div style='margin-bottom: 10px; padding: 15px; background-color: #d4edda; border-radius: 5px;'>";
        echo "<strong>Token generation:</strong> Success<br>";
        echo "<strong>Generated token:</strong><br><code style='word-break: break-all;'>" . $token . "</code><br>";
        
        // Validate the token
        $decodedPayload = validateJWT($token);
        
        if ($decodedPayload) {
            echo "<strong>Token validation:</strong> Success<br>";
            echo "<strong>Decoded payload:</strong><br><pre>";
            print_r($decodedPayload);
            echo "</pre>";
        } else {
            echo "<strong>Token validation:</strong> Failed<br>";
            echo "<strong>Error:</strong> Could not validate the token that was just generated.";
        }
        
        echo "</div>";
    } catch (Exception $e) {
        echo "<div style='margin-bottom: 10px; padding: 15px; background-color: #f8d7da; border-radius: 5px;'>";
        echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
        echo "</div>";
    }
} else {
    echo "<div style='margin-bottom: 10px; padding: 15px; background-color: #f8d7da; border-radius: 5px;'>";
    echo "<strong>Error:</strong> JWT functions (generateJWT, validateJWT) not found. Check if auth_utils.php is properly included.<br>";
    echo "</div>";
}

// Provide form to generate a new JWT secret
echo "<h2>Generate New JWT Secret</h2>";
echo "<form method='post' action='' style='background-color: #f5f5f5; padding: 20px; border-radius: 5px;'>";
echo "<p>If you need a new JWT secret, use the button below to generate a secure random string:</p>";
echo "<button type='submit' name='generate_secret' style='padding: 8px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;'>Generate New Secret</button>";
echo "</form>";

// Generate a new secret if requested
if (isset($_POST['generate_secret'])) {
    $newSecret = bin2hex(random_bytes(32)); // 64 character hex string (32 bytes)
    
    echo "<div style='margin-top: 20px; padding: 15px; background-color: #d4edda; border-radius: 5px;'>";
    echo "<strong>New JWT Secret:</strong><br>";
    echo "<code style='word-break: break-all;'>" . $newSecret . "</code><br><br>";
    echo "<p>To use this new secret:</p>";
    echo "<ol>";
    echo "<li>Open your .env file</li>";
    echo "<li>Find the line that starts with <code>JWT_SECRET=</code></li>";
    echo "<li>Replace the current value with the new secret above</li>";
    echo "</ol>";
    echo "<pre>";
    echo "JWT_SECRET=" . $newSecret;
    echo "</pre>";
    echo "<p><strong>Warning:</strong> Changing the JWT secret will invalidate all existing tokens!</p>";
    echo "</div>";
}

// Recommendations
echo "<h2>Recommendations</h2>";
echo "<div style='padding: 15px; background-color: #f8f9fa; border-radius: 5px;'>";
echo "<ul>";
echo "<li>The JWT secret should be at least 32 characters long for good security</li>";
echo "<li>Keep the JWT secret consistent across all environments</li>";
echo "<li>If you change the JWT secret, all existing tokens will be invalidated</li>";
echo "<li>Store the JWT secret in the .env file and never commit it to version control</li>";
echo "</ul>";
echo "</div>";
?> 