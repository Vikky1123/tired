<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Comprehensive Connection Debug</h1>";

// Function to check if a URL is accessible
function checkUrl($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'url' => $url,
        'accessible' => ($responseCode >= 200 && $responseCode < 400),
        'response_code' => $responseCode,
        'error' => $error
    ];
}

// Function to test a POST request to the login API
function testLoginApi($url, $username, $password) {
    $ch = curl_init($url);
    
    $data = json_encode([
        'username' => $username,
        'password' => $password
    ]);
    
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    $result = [
        'url' => $url,
        'success' => ($info['http_code'] >= 200 && $info['http_code'] < 300),
        'status_code' => $info['http_code'],
        'error' => $error,
        'response' => $response
    ];
    
    // Try to parse response if it looks like JSON
    if (!empty($response) && $response[0] === '{') {
        try {
            $result['parsed_response'] = json_decode($response, true);
        } catch (Exception $e) {
            $result['parse_error'] = $e->getMessage();
        }
    }
    
    return $result;
}

// 1. System Information
echo "<h2>System Information</h2>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "CURL Enabled: " . (function_exists('curl_version') ? 'Yes' : 'No') . "\n";
if (function_exists('curl_version')) {
    $curlInfo = curl_version();
    echo "CURL Version: " . $curlInfo['version'] . "\n";
}
echo "Server Protocol: " . $_SERVER['SERVER_PROTOCOL'] . "\n";
echo "Current Script: " . $_SERVER['PHP_SELF'] . "\n";
echo "Server Port: " . $_SERVER['SERVER_PORT'] . "\n";
echo "Remote Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
echo "</pre>";

// 2. Database Connection
echo "<h2>Database Connection</h2>";

try {
    if (file_exists(__DIR__ . '/config/database.php')) {
        require_once __DIR__ . '/config/database.php';
        
        echo "<p>Database configuration file found.</p>";
        
        // Try to connect
        if (function_exists('getConnection')) {
            echo "<p>getConnection() function exists.</p>";
            
            try {
                $conn = getConnection();
                if ($conn instanceof mysqli) {
                    echo "<p style='color:green'>✓ Successfully connected to database!</p>";
                    echo "<pre>";
                    echo "MySQL Server Version: " . $conn->server_info . "\n";
                    echo "MySQL Client Version: " . $conn->client_info . "\n";
                    echo "Character Set: " . $conn->character_set_name() . "\n";
                    
                    // Check if users table exists
                    $result = $conn->query("SHOW TABLES LIKE 'users'");
                    if ($result && $result->num_rows > 0) {
                        echo "Users table exists in the database.\n";
                        
                        // Check for admin user
                        $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE username = 'admin'");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            $user = $result->fetch_assoc();
                            echo "Admin user exists (ID: {$user['id']}, Email: {$user['email']})\n";
                        } else {
                            echo "Admin user does not exist in the database!\n";
                        }
                    } else {
                        echo "Users table does not exist in the database!\n";
                    }
                    echo "</pre>";
                    
                    $conn->close();
                } else {
                    echo "<p style='color:red'>✗ getConnection() did not return a valid mysqli object.</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color:red'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p style='color:red'>✗ getConnection() function not found in database.php</p>";
        }
    } else {
        echo "<p style='color:red'>✗ Database configuration file not found at " . __DIR__ . "/config/database.php</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error checking database connection: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 3. File Permissions
echo "<h2>File Permissions</h2>";
echo "<pre>";

$filesToCheck = [
    __DIR__ . '/direct-login.php',
    __DIR__ . '/config/database.php',
    __DIR__ . '/.env',
    __DIR__ . '/logs/',
    __DIR__
];

foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        $isReadable = is_readable($file) ? 'Yes' : 'No';
        $isWritable = is_writable($file) ? 'Yes' : 'No';
        $type = is_dir($file) ? 'Directory' : 'File';
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        
        echo "$file ($type):\n";
        echo "  - Exists: Yes\n";
        echo "  - Readable: $isReadable\n";
        echo "  - Writable: $isWritable\n";
        echo "  - Permissions: $perms\n";
    } else {
        echo "$file: Does not exist\n";
    }
    echo "\n";
}

echo "</pre>";

// 4. API Endpoints Accessibility
echo "<h2>API Endpoints Accessibility</h2>";

$urlsToCheck = [
    'http://localhost/PROJECT-BITRADER/backend/direct-login.php',
    'http://localhost/PROJECT-BITRADER/backend/cors_test.php',
    'http://localhost/PROJECT-BITRADER/backend/admin_check.php',
    'http://localhost/PROJECT-BITRADER/backend/connection_test.php'
];

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>URL</th><th>Status</th><th>Response Code</th><th>Error (if any)</th></tr>";

foreach ($urlsToCheck as $url) {
    $check = checkUrl($url);
    $statusColor = $check['accessible'] ? 'green' : 'red';
    $statusText = $check['accessible'] ? '✓ Accessible' : '✗ Not Accessible';
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($url) . "</td>";
    echo "<td style='color:$statusColor'>" . $statusText . "</td>";
    echo "<td>" . $check['response_code'] . "</td>";
    echo "<td>" . htmlspecialchars($check['error']) . "</td>";
    echo "</tr>";
}

echo "</table>";

// 5. Direct API Test
echo "<h2>Direct Login API Test</h2>";

$loginUrl = 'http://localhost/PROJECT-BITRADER/backend/direct-login.php';
$result = testLoginApi($loginUrl, 'admin', 'admin123');

echo "<pre>";
if ($result['success']) {
    echo "API Call Successful (Status: {$result['status_code']})\n\n";
} else {
    echo "API Call Failed (Status: {$result['status_code']})\n";
    if (!empty($result['error'])) {
        echo "Error: " . $result['error'] . "\n";
    }
    echo "\n";
}

echo "Response:\n";
if (isset($result['parsed_response'])) {
    echo json_encode($result['parsed_response'], JSON_PRETTY_PRINT) . "\n";
} else {
    echo htmlspecialchars($result['response']) . "\n";
}
echo "</pre>";

// 6. Network Test
echo "<h2>Network Connectivity Test</h2>";

$hosts = [
    'localhost' => 'localhost',
    '127.0.0.1' => '127.0.0.1',
    'google.com' => 'google.com'
];

echo "<pre>";
foreach ($hosts as $name => $host) {
    echo "Ping $name ($host): ";
    $pingResult = exec("ping -n 1 $host", $output, $status);
    if ($status === 0) {
        echo "Successful\n";
    } else {
        echo "Failed (Status: $status)\n";
    }
}
echo "</pre>";

// 7. .htaccess Check
echo "<h2>.htaccess Check</h2>";
echo "<pre>";

$htaccessFile = __DIR__ . '/.htaccess';
if (file_exists($htaccessFile)) {
    echo ".htaccess file exists.\n";
    echo "Content of .htaccess:\n";
    echo htmlspecialchars(file_get_contents($htaccessFile));
} else {
    echo ".htaccess file does not exist at " . $htaccessFile . "\n";
}

echo "</pre>";

// 8. Error Log Check
echo "<h2>Error Log Check</h2>";
echo "<pre>";

$errorLogs = [
    __DIR__ . '/logs/login_debug.log',
    __DIR__ . '/logs/error_log'
];

foreach ($errorLogs as $log) {
    if (file_exists($log) && is_readable($log)) {
        echo "Last 20 lines of " . basename($log) . ":\n";
        $logContent = file($log);
        $lastLines = array_slice($logContent, -20);
        foreach ($lastLines as $line) {
            echo htmlspecialchars($line);
        }
        echo "\n";
    } else {
        echo "Log file " . basename($log) . " not found or not readable.\n";
    }
}

echo "</pre>";

// 9. CORS Test
echo "<h2>CORS Test</h2>";
echo "<div id='cors-test-result'>Running CORS test with JavaScript...</div>";
?>

<script>
// Test direct fetch to login API
document.getElementById('cors-test-result').innerHTML = 'Sending request to direct-login.php...';

fetch('http://localhost/PROJECT-BITRADER/backend/direct-login.php', {
    method: 'OPTIONS',
    headers: {
        'Origin': window.location.origin
    }
})
.then(response => {
    document.getElementById('cors-test-result').innerHTML += 
        '<p>CORS preflight response status: ' + response.status + '</p>';
    
    // Check for CORS headers
    const corsHeaders = [
        'access-control-allow-origin',
        'access-control-allow-methods',
        'access-control-allow-headers'
    ];
    
    let headersFound = '';
    corsHeaders.forEach(header => {
        if (response.headers.get(header)) {
            headersFound += '<p style="color:green">✓ ' + header + ': ' + 
                response.headers.get(header) + '</p>';
        } else {
            headersFound += '<p style="color:red">✗ ' + header + ' header not found</p>';
        }
    });
    
    document.getElementById('cors-test-result').innerHTML += headersFound;
})
.catch(error => {
    document.getElementById('cors-test-result').innerHTML += 
        '<p style="color:red">Error: ' + error.message + '</p>';
});
</script>

<h2>What to Do Next</h2>
<p>Based on the tests above, here are some things to check:</p>
<ol>
    <li>Make sure the database exists and has a 'users' table with an admin user.</li>
    <li>Check that your .htaccess file is properly set up for CORS.</li>
    <li>Verify that you're using the correct database credentials in your .env file.</li>
    <li>Look for errors in the PHP or Apache error logs.</li>
    <li>Try using 127.0.0.1 instead of localhost in your connection settings.</li>
</ol>

<p>
    <a href="admin_check.php" style="display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;">Run Admin Check</a>
    <a href="login_test.html" style="display: inline-block; padding: 10px 15px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 4px;">Run Login Test</a>
</p> 