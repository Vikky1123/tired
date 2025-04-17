<?php
// Debug Authentication

// Include required files
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/utils/db_utils.php';

// Test credentials
$username = 'demo';
$password = 'password123';

// Step 1: Check if we can connect to the database
echo "<h2>Database Connection</h2>";
try {
    $conn = getConnection();
    echo "Database connection: SUCCESS<br>";
    $conn->close();
} catch (Exception $e) {
    echo "Database connection: FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

// Step 2: Manually query the database to see if user exists
echo "<h2>User Lookup</h2>";
$sql = "SELECT * FROM users WHERE username = ?";
$result = executeQuery($sql, [$username]);
echo "User query result: ";
if (!empty($result)) {
    echo "FOUND<br>";
    echo "Username: " . $result[0]['username'] . "<br>";
    echo "Email: " . $result[0]['email'] . "<br>";
    echo "Password hash: " . $result[0]['password'] . "<br>";
    echo "Role: " . $result[0]['role'] . "<br>";
    
    // Step 3: Test password verification directly
    echo "<h2>Password Verification</h2>";
    $stored_hash = $result[0]['password'];
    $is_password_valid = password_verify($password, $stored_hash);
    echo "Password verification: " . ($is_password_valid ? "VALID" : "INVALID") . "<br>";
} else {
    echo "NOT FOUND<br>";
}

// Step 4: Use the authenticate method
echo "<h2>User Authentication</h2>";
$user = User::authenticate($username, $password);
if ($user) {
    echo "Authentication: SUCCESS<br>";
    echo "Authenticated user: " . $user['username'] . "<br>";
    echo "Role: " . $user['role'] . "<br>";
} else {
    echo "Authentication: FAILED<br>";
}
?> 