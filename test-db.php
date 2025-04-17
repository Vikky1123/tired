<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Test</h1>";

try {
    // Include database config
    require_once __DIR__ . '/backend/config/database.php';
    
    echo "<p>Database configuration loaded successfully.</p>";
    
    // Try to connect
    $conn = getConnection();
    
    echo "<p>Database connection successful! Connected to " . DB_NAME . " at " . DB_HOST . ":" . DB_PORT . "</p>";
    
    // Try a simple query
    $result = $conn->query("SHOW TABLES");
    
    if ($result) {
        echo "<h2>Tables in database:</h2>";
        echo "<ul>";
        
        while ($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        
        echo "</ul>";
    }
    
    // Check if users table exists and has records
    $usersResult = $conn->query("SELECT COUNT(*) as count FROM users");
    
    if ($usersResult) {
        $row = $usersResult->fetch_assoc();
        echo "<p>Number of users in database: " . $row['count'] . "</p>";
    } else {
        echo "<p>Error checking users table: " . $conn->error . "</p>";
    }
    
    // Close connection
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Check for .env file
$envPath = __DIR__ . '/backend/.env';
if (file_exists($envPath)) {
    echo "<p>.env file exists at: " . $envPath . "</p>";
} else {
    echo "<p style='color: red;'>.env file does not exist at: " . $envPath . "</p>";
    echo "<p>Please create a .env file with the following content:</p>";
    echo "<pre>
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=bitrader_db
DB_PORT=3306
JWT_SECRET=your_jwt_secret_key
</pre>";
}
?> 