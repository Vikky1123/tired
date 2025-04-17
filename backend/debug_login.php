<?php
/**
 * Debug Login Script
 * This script will help identify exactly where the login process is failing
 */

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start an output buffer to capture errors
ob_start();

echo "<h1>BiTrader Login Debug Tool</h1>";
echo "<p>This tool helps diagnose issues with the login API</p>";

// Function to print a step with status
function debugStep($step, $success = true, $details = '') {
    $status = $success ? '✅' : '❌';
    echo "<div style='margin-bottom: 10px;'>";
    echo "<strong>$status $step</strong>";
    if ($details) {
        echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto;'>";
        if (is_array($details) || is_object($details)) {
            print_r($details);
        } else {
            echo htmlspecialchars($details);
        }
        echo "</pre>";
    }
    echo "</div>";
}

// 1. Check file exists
$files_to_check = [
    '/models/User.php' => false,
    '/utils/api_utils.php' => false,
    '/utils/db_utils.php' => false,
    '/utils/auth_utils.php' => false,
    '/config/database.php' => false,
    '/config/env.php' => false,
    '/.env' => false
];

echo "<h2>1. Checking Required Files</h2>";

foreach ($files_to_check as $file => $exists) {
    $fullPath = __DIR__ . $file;
    $files_to_check[$file] = file_exists($fullPath);
    debugStep("Checking {$file}", $files_to_check[$file], $files_to_check[$file] ? "File exists at {$fullPath}" : "File MISSING at {$fullPath}");
}

// 2. Include necessary files
echo "<h2>2. Including Required Files</h2>";

try {
    debugStep("Including files", true, "Starting to include files...");
    
    // Include config files first
    if ($files_to_check['/config/env.php']) {
        require_once __DIR__ . '/config/env.php';
        debugStep("Included env.php", true);
    }
    
    if ($files_to_check['/config/database.php']) {
        require_once __DIR__ . '/config/database.php';
        debugStep("Included database.php", true);
    }
    
    // Include utility files
    if ($files_to_check['/utils/db_utils.php']) {
        if (!function_exists('executeQuery')) {
            require_once __DIR__ . '/utils/db_utils.php';
            debugStep("Included db_utils.php", true);
        } else {
            debugStep("db_utils.php already included", true);
        }
    }
    
    if ($files_to_check['/utils/auth_utils.php']) {
        if (!function_exists('generateJWT')) {
            require_once __DIR__ . '/utils/auth_utils.php';
            debugStep("Included auth_utils.php", true);
        } else {
            debugStep("auth_utils.php already included", true);
        }
    }
    
    if ($files_to_check['/utils/api_utils.php']) {
        if (!function_exists('sendSuccessResponse')) {
            require_once __DIR__ . '/utils/api_utils.php';
            debugStep("Included api_utils.php", true);
        } else {
            debugStep("api_utils.php already included", true);
        }
    }
    
    // Include User model
    if ($files_to_check['/models/User.php']) {
        if (!class_exists('User')) {
            require_once __DIR__ . '/models/User.php';
            debugStep("Included User.php", true);
        } else {
            debugStep("User.php already included", true);
        }
    }
    
} catch (Exception $e) {
    debugStep("Error including files", false, "Exception: " . $e->getMessage());
} catch (Error $e) {
    debugStep("Fatal error including files", false, "Error: " . $e->getMessage());
}

// 3. Check database connection
echo "<h2>3. Testing Database Connection</h2>";

try {
    if (function_exists('getConnection')) {
        debugStep("getConnection function exists", true);
        
        // Try to connect
        debugStep("Attempting to connect to database", true, "Host: " . (defined('DB_HOST') ? DB_HOST : 'undefined') . 
                                                           ", User: " . (defined('DB_USER') ? DB_USER : 'undefined') . 
                                                           ", Database: " . (defined('DB_NAME') ? DB_NAME : 'undefined'));
        
        $conn = getConnection();
        debugStep("Database connection successful", true);
        
        // Check if users table exists
        $result = $conn->query("SHOW TABLES LIKE 'users'");
        if ($result->num_rows > 0) {
            debugStep("users table exists", true);
            
            // Count users
            $result = $conn->query("SELECT COUNT(*) as count FROM users");
            if ($result) {
                $row = $result->fetch_assoc();
                debugStep("User count", true, "Found " . $row['count'] . " users in the database");
                
                // Show user table structure
                $result = $conn->query("DESCRIBE users");
                $fields = [];
                while ($row = $result->fetch_assoc()) {
                    $fields[] = $row;
                }
                debugStep("User table structure", true, $fields);
            }
        } else {
            debugStep("users table not found", false, "The users table does not exist in the database");
        }
        
        $conn->close();
    } else {
        debugStep("getConnection function not defined", false, "The getConnection function was not found");
    }
} catch (Exception $e) {
    debugStep("Database connection error", false, "Exception: " . $e->getMessage());
} catch (Error $e) {
    debugStep("Fatal database error", false, "Error: " . $e->getMessage());
}

// 4. Test User class methods
echo "<h2>4. Testing User Class</h2>";

try {
    if (class_exists('User')) {
        debugStep("User class exists", true);
        
        // Check static methods
        $methods = [
            'getById', 'getByUsername', 'getByEmail', 'authenticate', 'generateToken'
        ];
        
        foreach ($methods as $method) {
            debugStep("Checking User::{$method}() method", method_exists('User', $method));
        }
        
        // Test some methods with dummy data
        if (method_exists('User', 'getByUsername') && function_exists('getConnection')) {
            // Try to get a user
            $result = User::getByUsername('admin');
            if ($result) {
                debugStep("User::getByUsername('admin')", true, "Found user: " . json_encode($result));
            } else {
                debugStep("User::getByUsername('admin')", false, "No user found with username 'admin'");
            }
        }
    } else {
        debugStep("User class not defined", false, "The User class was not found");
    }
} catch (Exception $e) {
    debugStep("User class error", false, "Exception: " . $e->getMessage());
} catch (Error $e) {
    debugStep("Fatal User class error", false, "Error: " . $e->getMessage());
}

// 5. Test simulated login
echo "<h2>5. Simulated Login Process</h2>";

try {
    if (class_exists('User') && method_exists('User', 'authenticate')) {
        // Test credentials
        $username = 'admin';  // Change this to a valid username in your database
        $password = 'admin123'; // Change this to the correct password
        
        debugStep("Attempting authentication", true, "Username: {$username}, Password: [HIDDEN]");
        
        // Try to authenticate
        $user = User::authenticate($username, $password);
        
        if ($user) {
            debugStep("Authentication successful", true, $user);
            
            // Try to generate a token
            if (method_exists('User', 'generateToken')) {
                $token = User::generateToken($user);
                debugStep("Token generated", !empty($token), substr($token, 0, 20) . "...");
            } else {
                debugStep("Token generation", false, "generateToken method not found");
            }
        } else {
            debugStep("Authentication failed", false, "Invalid credentials or user not found");
        }
    } else {
        debugStep("Cannot test authentication", false, "User class or authenticate method not found");
    }
} catch (Exception $e) {
    debugStep("Authentication error", false, "Exception: " . $e->getMessage());
} catch (Error $e) {
    debugStep("Fatal authentication error", false, "Error: " . $e->getMessage());
}

// 6. Display environment info
echo "<h2>6. Environment Information</h2>";

$server_info = [
    'PHP Version' => phpversion(),
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'Unknown',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown',
    'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
    'Current Directory' => __DIR__
];

debugStep("Server Information", true, $server_info);

// Display PHP extensions
$extensions = get_loaded_extensions();
sort($extensions);
debugStep("PHP Extensions", true, $extensions);

// Check for required extensions
$required_extensions = [
    'mysqli', 'json', 'openssl', 'mbstring'
];

foreach ($required_extensions as $ext) {
    $loaded = in_array($ext, $extensions);
    debugStep("Required extension: {$ext}", $loaded);
}

// 7. Check for error logs
echo "<h2>7. PHP Error Logs</h2>";

try {
    $error_log_path = ini_get('error_log');
    debugStep("PHP error_log path", !empty($error_log_path), $error_log_path ?: "Not configured");
    
    // Check if the log file exists and try to read recent errors
    if (!empty($error_log_path) && file_exists($error_log_path) && is_readable($error_log_path)) {
        $log_size = filesize($error_log_path);
        debugStep("Error log exists", true, "Size: " . round($log_size / 1024, 2) . " KB");
        
        // Get last 20 lines from the error log
        $log_lines = [];
        $file = new SplFileObject($error_log_path, 'r');
        $file->seek(PHP_INT_MAX); // Seek to end of file
        $total_lines = $file->key(); // Get last line number
        
        $lines_to_read = min(20, $total_lines);
        $start_line = max(0, $total_lines - $lines_to_read);
        
        $file->seek($start_line);
        for ($i = 0; $i < $lines_to_read; $i++) {
            $log_lines[] = $file->current();
            $file->next();
        }
        
        debugStep("Recent errors", true, $log_lines);
    } else {
        debugStep("Cannot access error log", false, "File doesn't exist or is not readable");
    }
} catch (Exception $e) {
    debugStep("Error reading logs", false, "Exception: " . $e->getMessage());
}

// 8. Recommendations
echo "<h2>8. Recommendations</h2>";
echo "<ul>";
echo "<li>Check PHP version - should be 7.2 or higher</li>";
echo "<li>Ensure the MySQL database exists and the user has proper permissions</li>";
echo "<li>Make sure the users table is correctly structured with username, password fields</li>";
echo "<li>Verify the JWT_SECRET in the .env file</li>";
echo "<li>Check if all required PHP extensions are installed (mysqli, json, openssl, mbstring)</li>";
echo "<li>Look for any PHP errors in the error_log</li>";
echo "</ul>";

// Get any buffered errors
$errors = ob_get_clean();
echo $errors;

// Manual login test form 
echo "<h2>9. Try Manual Login Test</h2>";
echo "<p>Use this form to test the login process directly:</p>";
echo "<form id='login-form' style='background-color: #f5f5f5; padding: 20px; border-radius: 5px;'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='username'>Username:</label><br>";
echo "<input type='text' id='username' name='username' value='admin' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='password'>Password:</label><br>";
echo "<input type='password' id='password' name='password' value='admin123' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<button type='button' onclick='testLogin()' style='padding: 8px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;'>Test Login</button>";
echo "</div>";
echo "<div id='result' style='margin-top: 20px; padding: 10px; border-radius: 5px; display: none;'></div>";
echo "</form>";

// JavaScript to handle login test
echo "<script>
function testLogin() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const resultDiv = document.getElementById('result');
    
    resultDiv.style.display = 'block';
    resultDiv.style.backgroundColor = '#f8f9fa';
    resultDiv.innerHTML = 'Testing login... Please wait.';
    
    // Create login data
    const loginData = {
        username: username,
        password: password
    };
    
    // Make fetch request
    fetch('http://localhost/PROJECT-BITRADER/backend/api/auth/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(loginData)
    })
    .then(response => {
        resultDiv.innerHTML = 'Response status: ' + response.status + ' ' + response.statusText + '<br>';
        return response.text();
    })
    .then(data => {
        resultDiv.innerHTML += '<pre>' + data + '</pre>';
        
        try {
            const jsonData = JSON.parse(data);
            if (jsonData.success) {
                resultDiv.style.backgroundColor = '#d4edda';
            } else {
                resultDiv.style.backgroundColor = '#f8d7da';
            }
        } catch (e) {
            resultDiv.style.backgroundColor = '#f8d7da';
            resultDiv.innerHTML += '<p>Error parsing JSON response.</p>';
        }
    })
    .catch(error => {
        resultDiv.style.backgroundColor = '#f8d7da';
        resultDiv.innerHTML = 'Error: ' + error.message;
    });
}
</script>";
?> 