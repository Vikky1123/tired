<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Configuration Fix</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #333;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .warning {
            color: orange;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Database Configuration Fix</h1>
    
    <?php
    // Check for existing configuration
    $envFile = __DIR__ . '/.env';
    $dbConfigFile = __DIR__ . '/config/database.php';
    
    // Current configuration values
    $currentConfig = [
        'DB_HOST' => 'localhost',
        'DB_USER' => 'root',
        'DB_PASS' => '',
        'DB_NAME' => 'bitrader_db',
        'DB_PORT' => '3306'
    ];
    
    // Read current .env file if exists
    if (file_exists($envFile)) {
        echo "<p>Found .env file at: " . htmlspecialchars($envFile) . "</p>";
        
        $envContent = file_get_contents($envFile);
        $lines = explode("\n", $envContent);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || $line[0] === '#') {
                continue;
            }
            
            // Check for key=value pattern
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if any
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') || 
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                // Save to current config if it's a DB setting
                if (array_key_exists($key, $currentConfig)) {
                    $currentConfig[$key] = $value;
                }
            }
        }
    } else {
        echo "<p class='warning'>No .env file found at: " . htmlspecialchars($envFile) . "</p>";
    }
    
    // Check database configuration file
    if (file_exists($dbConfigFile)) {
        echo "<p>Found database configuration file at: " . htmlspecialchars($dbConfigFile) . "</p>";
    } else {
        echo "<p class='error'>Database configuration file not found at: " . htmlspecialchars($dbConfigFile) . "</p>";
    }
    
    // Function to test database connection
    function testDbConnection($host, $user, $pass, $dbName, $port) {
        try {
            $conn = new mysqli($host, $user, $pass, $dbName, $port);
            
            if ($conn->connect_error) {
                return [
                    'success' => false, 
                    'message' => "Connection failed: " . $conn->connect_error
                ];
            }
            
            // Test query to verify connection works
            $result = $conn->query("SELECT 1");
            if ($result) {
                $conn->close();
                return [
                    'success' => true, 
                    'message' => "Connection successful!"
                ];
            } else {
                $conn->close();
                return [
                    'success' => false, 
                    'message' => "Connection established but query failed."
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false, 
                'message' => "Exception: " . $e->getMessage()
            ];
        }
    }
    
    // Test current connection
    $currentTest = testDbConnection(
        $currentConfig['DB_HOST'],
        $currentConfig['DB_USER'],
        $currentConfig['DB_PASS'],
        $currentConfig['DB_NAME'],
        $currentConfig['DB_PORT']
    );
    
    echo "<h2>Current Database Connection Test</h2>";
    if ($currentTest['success']) {
        echo "<p class='success'>✓ " . $currentTest['message'] . "</p>";
    } else {
        echo "<p class='error'>✗ " . $currentTest['message'] . "</p>";
    }
    
    // Try alternative connections if current one fails
    if (!$currentTest['success']) {
        echo "<h2>Trying Alternative Connections</h2>";
        echo "<ul>";
        
        // Try 127.0.0.1 instead of localhost
        if ($currentConfig['DB_HOST'] === 'localhost') {
            $altTest = testDbConnection(
                '127.0.0.1',
                $currentConfig['DB_USER'],
                $currentConfig['DB_PASS'],
                $currentConfig['DB_NAME'],
                $currentConfig['DB_PORT']
            );
            
            if ($altTest['success']) {
                echo "<li class='success'>✓ Connection with 127.0.0.1 instead of localhost works!</li>";
                $currentConfig['DB_HOST'] = '127.0.0.1';
            } else {
                echo "<li class='error'>✗ Connection with 127.0.0.1 failed: " . $altTest['message'] . "</li>";
            }
        }
        
        // Try creating the database if it doesn't exist
        $createDbTest = testDbConnection(
            $currentConfig['DB_HOST'] === 'localhost' ? '127.0.0.1' : $currentConfig['DB_HOST'],
            $currentConfig['DB_USER'],
            $currentConfig['DB_PASS'],
            '',  // Empty database name
            $currentConfig['DB_PORT']
        );
        
        if ($createDbTest['success']) {
            echo "<li class='success'>✓ Connection to MySQL server without database works!</li>";
            
            // Try to create the database
            try {
                $conn = new mysqli(
                    $currentConfig['DB_HOST'] === 'localhost' ? '127.0.0.1' : $currentConfig['DB_HOST'],
                    $currentConfig['DB_USER'],
                    $currentConfig['DB_PASS'],
                    '',  // Empty database name
                    $currentConfig['DB_PORT']
                );
                
                $createDbSql = "CREATE DATABASE IF NOT EXISTS " . $conn->real_escape_string($currentConfig['DB_NAME']);
                if ($conn->query($createDbSql) === TRUE) {
                    echo "<li class='success'>✓ Created database '{$currentConfig['DB_NAME']}'!</li>";
                    
                    // Update host if needed
                    if ($currentConfig['DB_HOST'] === 'localhost') {
                        $currentConfig['DB_HOST'] = '127.0.0.1';
                    }
                    
                    // Test again with the created database
                    $finalTest = testDbConnection(
                        $currentConfig['DB_HOST'],
                        $currentConfig['DB_USER'],
                        $currentConfig['DB_PASS'],
                        $currentConfig['DB_NAME'],
                        $currentConfig['DB_PORT']
                    );
                    
                    if ($finalTest['success']) {
                        echo "<li class='success'>✓ Connection to newly created database works!</li>";
                    } else {
                        echo "<li class='error'>✗ Connection to newly created database failed: " . $finalTest['message'] . "</li>";
                    }
                } else {
                    echo "<li class='error'>✗ Failed to create database: " . $conn->error . "</li>";
                }
                
                $conn->close();
            } catch (Exception $e) {
                echo "<li class='error'>✗ Error creating database: " . $e->getMessage() . "</li>";
            }
        } else {
            echo "<li class='error'>✗ Cannot connect to MySQL server: " . $createDbTest['message'] . "</li>";
        }
        
        echo "</ul>";
    }
    
    // Show form to update configuration
    echo "<h2>Update Database Configuration</h2>";
    echo "<p>You can update your database configuration here:</p>";
    
    echo "<form method='post'>";
    echo "<div class='form-group'>";
    echo "<label for='db_host'>Database Host:</label>";
    echo "<input type='text' id='db_host' name='db_host' value='" . htmlspecialchars($currentConfig['DB_HOST']) . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='db_port'>Database Port:</label>";
    echo "<input type='text' id='db_port' name='db_port' value='" . htmlspecialchars($currentConfig['DB_PORT']) . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='db_name'>Database Name:</label>";
    echo "<input type='text' id='db_name' name='db_name' value='" . htmlspecialchars($currentConfig['DB_NAME']) . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='db_user'>Database User:</label>";
    echo "<input type='text' id='db_user' name='db_user' value='" . htmlspecialchars($currentConfig['DB_USER']) . "'>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='db_pass'>Database Password:</label>";
    echo "<input type='password' id='db_pass' name='db_pass' value='" . htmlspecialchars($currentConfig['DB_PASS']) . "'>";
    echo "</div>";
    
    echo "<button type='submit' name='update_config'>Update Configuration</button>";
    echo "</form>";
    
    // Handle form submission
    if (isset($_POST['update_config'])) {
        echo "<h2>Updating Configuration</h2>";
        
        // Get form values
        $newConfig = [
            'DB_HOST' => $_POST['db_host'],
            'DB_PORT' => $_POST['db_port'],
            'DB_NAME' => $_POST['db_name'],
            'DB_USER' => $_POST['db_user'],
            'DB_PASS' => $_POST['db_pass']
        ];
        
        // Test new configuration
        $newTest = testDbConnection(
            $newConfig['DB_HOST'],
            $newConfig['DB_USER'],
            $newConfig['DB_PASS'],
            $newConfig['DB_NAME'],
            $newConfig['DB_PORT']
        );
        
        if ($newTest['success']) {
            echo "<p class='success'>✓ New configuration works: " . $newTest['message'] . "</p>";
            
            // Update .env file
            $envContent = "# BiTrader Environment Configuration\n\n";
            $envContent .= "# Database settings\n";
            $envContent .= "DB_HOST=" . $newConfig['DB_HOST'] . "\n";
            $envContent .= "DB_USER=" . $newConfig['DB_USER'] . "\n";
            $envContent .= "DB_PASS=" . $newConfig['DB_PASS'] . "\n";
            $envContent .= "DB_NAME=" . $newConfig['DB_NAME'] . "\n";
            $envContent .= "DB_PORT=" . $newConfig['DB_PORT'] . "\n\n";
            
            // Add JWT secret if exists in current .env
            if (file_exists($envFile)) {
                $currentEnv = file_get_contents($envFile);
                if (preg_match('/JWT_SECRET=([^\n]+)/', $currentEnv, $matches)) {
                    $envContent .= "# Authentication\n";
                    $envContent .= "JWT_SECRET=" . $matches[1] . "\n\n";
                } else {
                    $envContent .= "# Authentication\n";
                    $envContent .= "JWT_SECRET=H7fP9tR3mK5xL2qY8sZ6vB4nG1jD0cE3wA7bV9\n\n";
                }
                
                // Add other settings from current .env
                if (preg_match('/# App settings(.*?)(?=# |$)/s', $currentEnv, $matches)) {
                    $envContent .= "# App settings" . $matches[1];
                } else {
                    $envContent .= "# App settings\n";
                    $envContent .= "APP_ENV=development\n";
                    $envContent .= "APP_DEBUG=true\n\n";
                }
                
                if (preg_match('/# API Settings(.*?)(?=# |$)/s', $currentEnv, $matches)) {
                    $envContent .= "# API Settings" . $matches[1];
                } else {
                    $envContent .= "# API Settings\n";
                    $envContent .= "API_URL=http://localhost/PROJECT-BITRADER/backend/api\n";
                    $envContent .= "CORS_ALLOW_ORIGIN=*\n\n";
                }
                
                if (preg_match('/# Debug Mode(.*?)(?=# |$)/s', $currentEnv, $matches)) {
                    $envContent .= "# Debug Mode" . $matches[1];
                } else {
                    $envContent .= "# Debug Mode (set to false in production)\n";
                    $envContent .= "DEBUG_MODE=true\n";
                }
            } else {
                // Create default values for new .env
                $envContent .= "# Authentication\n";
                $envContent .= "JWT_SECRET=H7fP9tR3mK5xL2qY8sZ6vB4nG1jD0cE3wA7bV9\n\n";
                
                $envContent .= "# App settings\n";
                $envContent .= "APP_ENV=development\n";
                $envContent .= "APP_DEBUG=true\n\n";
                
                $envContent .= "# API Settings\n";
                $envContent .= "API_URL=http://localhost/PROJECT-BITRADER/backend/api\n";
                $envContent .= "CORS_ALLOW_ORIGIN=*\n\n";
                
                $envContent .= "# Debug Mode (set to false in production)\n";
                $envContent .= "DEBUG_MODE=true\n";
            }
            
            // Write new .env file
            if (file_put_contents($envFile, $envContent) !== false) {
                echo "<p class='success'>✓ Updated .env file successfully!</p>";
            } else {
                echo "<p class='error'>✗ Failed to write to .env file</p>";
            }
            
            echo "<h3>Next Steps</h3>";
            echo "<p>The database configuration has been updated. Now you need to:</p>";
            echo "<ol>";
            echo "<li><a href='admin_check.php'>Run Admin Check</a> to create the necessary tables and admin user</li>";
            echo "<li><a href='login_test.html'>Test Login</a> to verify that everything is working</li>";
            echo "</ol>";
        } else {
            echo "<p class='error'>✗ New configuration failed: " . $newTest['message'] . "</p>";
            echo "<p>Please check your database settings and try again.</p>";
        }
    }
    ?>
    
    <h2>Quick Links</h2>
    <p>
        <a href="debug_connection.php" style="display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;">Run Comprehensive Test</a>
        <a href="admin_check.php" style="display: inline-block; padding: 10px 15px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;">Run Admin Check</a>
        <a href="login_test.html" style="display: inline-block; padding: 10px 15px; background-color: #FF9800; color: white; text-decoration: none; border-radius: 4px;">Test Login</a>
    </p>
</body>
</html> 