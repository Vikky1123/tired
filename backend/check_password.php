<?php
/**
 * Password Checking Tool
 * This script helps verify and create properly hashed passwords
 */

// Include required files
require_once __DIR__ . '/utils/auth_utils.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Password Hash Checker & Generator</h1>";

// Function to create a password hash
function createPasswordHash($password) {
    if (function_exists('hashPassword')) {
        return hashPassword($password);
    } else {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}

// Function to verify a password against a hash
function checkPasswordHash($password, $hash) {
    if (function_exists('verifyPassword')) {
        return verifyPassword($password, $hash);
    } else {
        return password_verify($password, $hash);
    }
}

// Check existing password hash from the form
if (isset($_POST['check_password'])) {
    $password = $_POST['password'] ?? '';
    $passwordHash = $_POST['hash'] ?? '';
    
    echo "<div style='margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px;'>";
    echo "<h3>Password Verification Result:</h3>";
    
    if (empty($password) || empty($passwordHash)) {
        echo "<p style='color: red;'>Please enter both password and hash.</p>";
    } else {
        $result = checkPasswordHash($password, $passwordHash);
        if ($result) {
            echo "<p style='color: green;'><strong>SUCCESS:</strong> The password matches the hash.</p>";
        } else {
            echo "<p style='color: red;'><strong>FAILED:</strong> The password does not match the hash.</p>";
        }
    }
    echo "</div>";
}

// Generate new password hash from the form
if (isset($_POST['generate_hash'])) {
    $newPassword = $_POST['new_password'] ?? '';
    
    echo "<div style='margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px;'>";
    echo "<h3>Generated Password Hash:</h3>";
    
    if (empty($newPassword)) {
        echo "<p style='color: red;'>Please enter a password to hash.</p>";
    } else {
        $newHash = createPasswordHash($newPassword);
        echo "<p><strong>Password:</strong> " . htmlspecialchars($newPassword) . "</p>";
        echo "<p><strong>Hash:</strong> <code>" . htmlspecialchars($newHash) . "</code></p>";
        echo "<p>You can use this hash in your database for the user.</p>";
    }
    echo "</div>";
}

// Display form for checking existing hash
echo "<div style='margin: 20px 0;'>";
echo "<h2>Check If Password Matches Hash</h2>";
echo "<form method='post' action='' style='background-color: #f5f5f5; padding: 20px; border-radius: 5px;'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='password'>Password:</label><br>";
echo "<input type='text' id='password' name='password' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='hash'>Password Hash (from database):</label><br>";
echo "<input type='text' id='hash' name='hash' style='padding: 8px; width: 100%;'>";
echo "</div>";
echo "<div>";
echo "<button type='submit' name='check_password' style='padding: 8px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;'>Check Password</button>";
echo "</div>";
echo "</form>";
echo "</div>";

// Display form for generating new hash
echo "<div style='margin: 20px 0;'>";
echo "<h2>Generate New Password Hash</h2>";
echo "<form method='post' action='' style='background-color: #f5f5f5; padding: 20px; border-radius: 5px;'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='new_password'>Password:</label><br>";
echo "<input type='text' id='new_password' name='new_password' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div>";
echo "<button type='submit' name='generate_hash' style='padding: 8px 15px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;'>Generate Hash</button>";
echo "</div>";
echo "</form>";
echo "</div>";

// Create SQL statement for updating password
if (isset($_POST['generate_hash']) && !empty($_POST['new_password'])) {
    $username = $_POST['username'] ?? 'admin';
    $newPassword = $_POST['new_password'];
    $newHash = createPasswordHash($newPassword);
    
    echo "<div style='margin: 20px 0;'>";
    echo "<h2>SQL Update Statement</h2>";
    echo "<div style='background-color: #f5f5f5; padding: 20px; border-radius: 5px;'>";
    echo "<p>To update the password in the database directly, you can use this SQL:</p>";
    echo "<pre style='background-color: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto;'>";
    echo "UPDATE users SET password = '" . $newHash . "' WHERE username = '" . $username . "';";
    echo "</pre>";
    echo "</div>";
    echo "</div>";
}

// Display password hashing info
echo "<div style='margin: 20px 0;'>";
echo "<h2>About Password Hashing</h2>";
echo "<div style='background-color: #f5f5f5; padding: 20px; border-radius: 5px;'>";
echo "<p>The system uses PHP's bcrypt password hashing to securely store passwords.</p>";
echo "<p>Properties of bcrypt hashes:</p>";
echo "<ul>";
echo "<li>They start with <code>\$2y\$</code> for modern PHP versions</li>";
echo "<li>The hash includes the salt, so no separate salt storage is needed</li>";
echo "<li>Each hash of the same password will be different due to random salt</li>";
echo "<li>Proper length is typically 60 characters</li>";
echo "</ul>";
echo "</div>";
echo "</div>";
?> 