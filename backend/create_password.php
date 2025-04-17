<?php
// Include the password hashing function
require_once __DIR__ . '/utils/auth_utils.php';

// Create a new hash for password123
$password = 'password123';
$new_hash = hashPassword($password);

echo "New password hash for 'password123': <br>";
echo $new_hash . "<br><br>";

// Update the database directly
require_once __DIR__ . '/config/database.php';
$conn = getConnection();

$sql = "UPDATE users SET password = ? WHERE username = 'demo'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $new_hash);
$result = $stmt->execute();

if ($result) {
    echo "Password updated successfully for user 'demo'";
} else {
    echo "Error updating password: " . $conn->error;
}

$stmt->close();
$conn->close();
?> 