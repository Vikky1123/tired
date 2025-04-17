<?php
// Test Password Verification

// Include the necessary files
require_once __DIR__ . '/utils/auth_utils.php';

// The password from your SQL file
$stored_hash = '$2y$10$OgFfyWhkKkwwzSl.L5EGWeE/sE4tMNBFZ06soB0JyfKQM/FNcQCr.';
$password_to_verify = 'password123';

// Test verification
$is_valid = verifyPassword($password_to_verify, $stored_hash);

echo "Testing password verification:<br>";
echo "Password: $password_to_verify<br>";
echo "Hash: $stored_hash<br>";
echo "Result: " . ($is_valid ? "VALID" : "INVALID") . "<br><br>";

// Generate a new hash to compare
$new_hash = hashPassword($password_to_verify);
echo "New hash generated: $new_hash<br>";
echo "Verifies against original password: " . (verifyPassword($password_to_verify, $new_hash) ? "YES" : "NO") . "<br>";

// Update the database directly
require_once __DIR__ . '/config/database.php';
$conn = getConnection();

$sql = "UPDATE users SET password = ? WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $new_hash);
$result = $stmt->execute();

if ($result) {
    echo "Password updated successfully for user 'admin'";
} else {
    echo "Error updating password: " . $conn->error;
}

$stmt->close();
$conn->close();
?> 