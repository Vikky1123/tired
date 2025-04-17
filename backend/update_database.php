<?php
/**
 * Database Update Script
 * Run this script to update the users table structure
 */

// Include database connection
require_once __DIR__ . '/config/database.php';

// SQL to add phone and country columns if they don't exist
$alterSql = "
-- Check if phone column exists
SELECT COUNT(*) as col_exists 
FROM information_schema.columns 
WHERE table_schema = 'bitrader_db' AND table_name = 'users' AND column_name = 'phone';
";

$conn = getConnection();
$result = $conn->query($alterSql);
$row = $result->fetch_assoc();

// If phone column doesn't exist, add it
if ($row['col_exists'] == 0) {
    $addPhoneSQL = "ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER full_name;";
    
    if ($conn->query($addPhoneSQL) === TRUE) {
        echo "Phone column added successfully<br>";
    } else {
        echo "Error adding phone column: " . $conn->error . "<br>";
    }
}

// Check if country column exists
$checkCountrySql = "
SELECT COUNT(*) as col_exists 
FROM information_schema.columns 
WHERE table_schema = 'bitrader_db' AND table_name = 'users' AND column_name = 'country';
";

$result = $conn->query($checkCountrySql);
$row = $result->fetch_assoc();

// If country column doesn't exist, add it
if ($row['col_exists'] == 0) {
    $addCountrySQL = "ALTER TABLE users ADD COLUMN country VARCHAR(50) NULL AFTER phone;";
    
    if ($conn->query($addCountrySQL) === TRUE) {
        echo "Country column added successfully<br>";
    } else {
        echo "Error adding country column: " . $conn->error . "<br>";
    }
}

echo "Database update completed!";

$conn->close(); 