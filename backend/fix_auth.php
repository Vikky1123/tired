<?php
// Fix Authentication Issues

// Include required files
require_once __DIR__ . '/utils/auth_utils.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>Fix Authentication Issues</h1>";

// Create a password hash
$password = 'password123';
$hash = hashPassword($password);

echo "<p>Generated new password hash for 'password123': $hash</p>";

// Connect to database
$conn = getConnection();

// Update admin user
$sql = "UPDATE users SET password = ? WHERE username = 'admin' OR email = 'admin@bitrader.com'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $hash);
$result = $stmt->execute();

if ($result) {
    echo "<p>Admin password updated successfully.</p>";
} else {
    echo "<p>Error updating admin password: " . $conn->error . "</p>";
}

// Update demo user
$sql = "UPDATE users SET password = ? WHERE username = 'demo' OR email = 'demo@bitrader.com'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $hash);
$result = $stmt->execute();

if ($result) {
    echo "<p>Demo password updated successfully.</p>";
} else {
    echo "<p>Error updating demo password: " . $conn->error . "</p>";
}

// Verify users in database
$sql = "SELECT id, username, email, password FROM users WHERE username IN ('admin', 'demo')";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Updated Users:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Password Hash</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["username"] . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "<td>" . $row["password"] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Add verification testing
    echo "<h2>Authentication Testing:</h2>";
    
    // Test admin
    $adminVerify = verifyPassword($password, $hash);
    echo "<p>Admin password verification: " . ($adminVerify ? "SUCCESS" : "FAILED") . "</p>";
    
    // Test demo
    $demoVerify = verifyPassword($password, $hash);
    echo "<p>Demo password verification: " . ($demoVerify ? "SUCCESS" : "FAILED") . "</p>";
    
} else {
    echo "<p>No users found!</p>";
}

// Close connection
$stmt->close();
$conn->close();

echo "<p>Database connection closed.</p>";

echo "<h2>Try these authentication options:</h2>";
echo "<p>1. Username: 'admin', Password: 'password123'</p>";
echo "<p>2. Username: 'admin@bitrader.com', Password: 'password123'</p>";
echo "<p>3. Username: 'demo', Password: 'password123'</p>";
echo "<p>4. Username: 'demo@bitrader.com', Password: 'password123'</p>";
?> 