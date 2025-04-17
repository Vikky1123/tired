<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to log test results
function logTest($test, $result, $details = '') {
    $timestamp = date('Y-m-d H:i:s');
    $logDir = __DIR__ . '/logs';
    
    // Create logs directory if it doesn't exist
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/network_test.log';
    $status = $result ? 'PASS' : 'FAIL';
    $log = "[$timestamp] [$status] $test" . ($details ? ": $details" : "") . "\n";
    
    file_put_contents($logFile, $log, FILE_APPEND);
    return $status;
}

// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');

// Set headers for CORS - this should match your regular API endpoints
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiTrader Network & API Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #2c3e50;
            margin-top: 25px;
        }
        .test-section {
            background: #f9f9f9;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 5px solid #3498db;
        }
        .pass {
            color: #27ae60;
            font-weight: bold;
        }
        .fail {
            color: #e74c3c;
            font-weight: bold;
        }
        .warning {
            color: #f39c12;
            font-weight: bold;
        }
        pre {
            background: #f1f1f1;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .button {
            display: inline-block;
            padding: 8px 16px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <h1>BiTrader Network & API Test</h1>
    
    <div class="test-section">
        <h2>Server Information</h2>
        <?php
        echo "<table>";
        echo "<tr><th>Property</th><th>Value</th></tr>";
        
        // PHP Version
        $phpVersion = phpversion();
        echo "<tr><td>PHP Version</td><td>{$phpVersion}</td></tr>";
        
        // Web Server Software
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
        echo "<tr><td>Web Server</td><td>{$serverSoftware}</td></tr>";
        
        // Document Root
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown';
        echo "<tr><td>Document Root</td><td>{$docRoot}</td></tr>";
        
        // Script Path
        $scriptPath = $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown';
        echo "<tr><td>Script Path</td><td>{$scriptPath}</td></tr>";
        
        // HTTP Protocol
        $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown';
        echo "<tr><td>HTTP Protocol</td><td>{$protocol}</td></tr>";
        
        // Server Name & Port
        $serverName = $_SERVER['SERVER_NAME'] ?? 'Unknown';
        $serverPort = $_SERVER['SERVER_PORT'] ?? 'Unknown';
        echo "<tr><td>Server Name & Port</td><td>{$serverName}:{$serverPort}</td></tr>";
        
        // Request Method
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'Unknown';
        echo "<tr><td>Request Method</td><td>{$requestMethod}</td></tr>";
        
        // Remote Address
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        echo "<tr><td>Remote Address</td><td>{$remoteAddr}</td></tr>";
        
        echo "</table>";
        ?>
    </div>
    
    <div class="test-section">
        <h2>PHP Configuration</h2>
        <?php
        echo "<table>";
        echo "<tr><th>Feature</th><th>Status</th><th>Details</th></tr>";
        
        // Check if file_get_contents works for URLs
        $allowUrlFopen = ini_get('allow_url_fopen');
        $allowUrlFopenStatus = $allowUrlFopen ? 'PASS' : 'FAIL';
        echo "<tr><td>allow_url_fopen</td><td class='" . strtolower($allowUrlFopenStatus) . "'>{$allowUrlFopenStatus}</td><td>{$allowUrlFopen}</td></tr>";
        
        // Check for curl extension
        $curlEnabled = function_exists('curl_version');
        $curlStatus = $curlEnabled ? 'PASS' : 'FAIL';
        echo "<tr><td>cURL Extension</td><td class='" . strtolower($curlStatus) . "'>{$curlStatus}</td><td>" . ($curlEnabled ? 'Available' : 'Not available') . "</td></tr>";
        
        // Check for JSON extension
        $jsonEnabled = function_exists('json_encode');
        $jsonStatus = $jsonEnabled ? 'PASS' : 'FAIL';
        echo "<tr><td>JSON Extension</td><td class='" . strtolower($jsonStatus) . "'>{$jsonStatus}</td><td>" . ($jsonEnabled ? 'Available' : 'Not available') . "</td></tr>";
        
        // Check for PDO extension
        $pdoEnabled = class_exists('PDO');
        $pdoStatus = $pdoEnabled ? 'PASS' : 'FAIL';
        echo "<tr><td>PDO Extension</td><td class='" . strtolower($pdoStatus) . "'>{$pdoStatus}</td><td>" . ($pdoEnabled ? 'Available' : 'Not available') . "</td></tr>";
        
        // Check PDO MySQL driver
        $pdoMysqlEnabled = in_array('mysql', PDO::getAvailableDrivers());
        $pdoMysqlStatus = $pdoMysqlEnabled ? 'PASS' : 'FAIL';
        echo "<tr><td>PDO MySQL Driver</td><td class='" . strtolower($pdoMysqlStatus) . "'>{$pdoMysqlStatus}</td><td>" . ($pdoMysqlEnabled ? 'Available' : 'Not available') . "</td></tr>";
        
        // Check for mysqli extension
        $mysqliEnabled = function_exists('mysqli_connect');
        $mysqliStatus = $mysqliEnabled ? 'PASS' : 'FAIL';
        echo "<tr><td>MySQLi Extension</td><td class='" . strtolower($mysqliStatus) . "'>{$mysqliStatus}</td><td>" . ($mysqliEnabled ? 'Available' : 'Not available') . "</td></tr>";
        
        // Check memory limit
        $memoryLimit = ini_get('memory_limit');
        echo "<tr><td>Memory Limit</td><td>INFO</td><td>{$memoryLimit}</td></tr>";
        
        // Check max execution time
        $maxExecutionTime = ini_get('max_execution_time');
        echo "<tr><td>Max Execution Time</td><td>INFO</td><td>{$maxExecutionTime} seconds</td></tr>";
        
        echo "</table>";
        ?>
    </div>
    
    <div class="test-section">
        <h2>Network Connectivity Tests</h2>
        <?php
        echo "<table>";
        echo "<tr><th>Test</th><th>Status</th><th>Details</th></tr>";
        
        // Test localhost connectivity with cURL
        $curlTest = false;
        $curlDetails = '';
        
        if ($curlEnabled) {
            $ch = curl_init('http://localhost');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            $curlTest = ($httpCode >= 200 && $httpCode < 400);
            $curlDetails = $curlTest ? "HTTP Code: {$httpCode}" : "Failed: {$curlError}";
        } else {
            $curlDetails = "cURL extension not available";
        }
        
        $curlStatus = $curlTest ? 'PASS' : 'FAIL';
        echo "<tr><td>localhost cURL Test</td><td class='" . strtolower($curlStatus) . "'>{$curlStatus}</td><td>{$curlDetails}</td></tr>";
        
        // Test localhost with file_get_contents
        $fopenTest = false;
        $fopenDetails = '';
        
        if ($allowUrlFopen) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'GET'
                ]
            ]);
            
            try {
                $response = @file_get_contents('http://localhost', false, $context);
                $fopenTest = ($response !== false);
                $fopenDetails = $fopenTest ? "Successfully connected" : "Failed to connect";
            } catch (Exception $e) {
                $fopenDetails = "Error: " . $e->getMessage();
            }
        } else {
            $fopenDetails = "allow_url_fopen is disabled";
        }
        
        $fopenStatus = $fopenTest ? 'PASS' : 'FAIL';
        echo "<tr><td>localhost fopen Test</td><td class='" . strtolower($fopenStatus) . "'>{$fopenStatus}</td><td>{$fopenDetails}</td></tr>";
        
        // Test database connectivity
        $dbTest = false;
        $dbDetails = '';
        
        // Check for database config file
        $dbConfigFile = __DIR__ . '/config/database.php';
        
        if (file_exists($dbConfigFile)) {
            // Include the database config
            try {
                require_once $dbConfigFile;
                
                if (isset($db_config) && is_array($db_config)) {
                    $host = $db_config['host'] ?? 'localhost';
                    $port = $db_config['port'] ?? '3306';
                    $username = $db_config['username'] ?? '';
                    $password = $db_config['password'] ?? '';
                    $database = $db_config['database'] ?? '';
                    
                    // Try PDO connection first
                    if ($pdoEnabled && $pdoMysqlEnabled) {
                        try {
                            $dsn = "mysql:host={$host};port={$port};dbname={$database}";
                            $pdo = new PDO($dsn, $username, $password, [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_TIMEOUT => 5
                            ]);
                            
                            $dbTest = true;
                            $dbDetails = "PDO connection successful to {$host}:{$port}";
                            
                            // Check if database exists
                            $stmt = $pdo->query("SELECT DATABASE() as db");
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($result && $result['db']) {
                                $dbDetails .= " (Database: {$result['db']})";
                            }
                        } catch (PDOException $e) {
                            $dbDetails = "PDO Error: " . $e->getMessage();
                        }
                    }
                    // Fall back to mysqli if PDO failed
                    else if ($mysqliEnabled && !$dbTest) {
                        try {
                            $mysqli = new mysqli($host, $username, $password, $database, $port);
                            
                            if ($mysqli->connect_errno) {
                                $dbDetails = "MySQLi Error: " . $mysqli->connect_error;
                            } else {
                                $dbTest = true;
                                $dbDetails = "MySQLi connection successful to {$host}:{$port} (Database: {$database})";
                                $mysqli->close();
                            }
                        } catch (Exception $e) {
                            $dbDetails = "MySQLi Error: " . $e->getMessage();
                        }
                    }
                } else {
                    $dbDetails = "Invalid database configuration format";
                }
            } catch (Exception $e) {
                $dbDetails = "Error loading database config: " . $e->getMessage();
            }
        } else {
            $dbDetails = "Database config file not found at: {$dbConfigFile}";
        }
        
        $dbStatus = $dbTest ? 'PASS' : 'FAIL';
        echo "<tr><td>Database Connectivity</td><td class='" . strtolower($dbStatus) . "'>{$dbStatus}</td><td>{$dbDetails}</td></tr>";
        
        // Log test results 
        logTest("localhost cURL Test", $curlTest, $curlDetails);
        logTest("localhost fopen Test", $fopenTest, $fopenDetails);
        logTest("Database Connectivity", $dbTest, $dbDetails);
        
        echo "</table>";
        ?>
    </div>
    
    <div class="test-section">
        <h2>API Endpoint Tests</h2>
        <p>Testing connection to key API endpoints:</p>
        <?php
        // Define API endpoints to test
        $endpoints = [
            '/direct-login.php' => 'Login API',
            '/direct-register.php' => 'Registration API',
            '/cors_test.php' => 'CORS Test API'
        ];
        
        echo "<table>";
        echo "<tr><th>Endpoint</th><th>Status</th><th>Details</th></tr>";
        
        // Test each endpoint
        foreach ($endpoints as $endpoint => $description) {
            $url = 'http://localhost/PROJECT-BITRADER/backend' . $endpoint;
            $endpointTest = false;
            $endpointDetails = '';
            
            if ($curlEnabled) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                $endpointTest = ($httpCode > 0); // Any response is better than no response
                $endpointDetails = $endpointTest ? "HTTP Code: {$httpCode}" : "Failed: {$curlError}";
            } else {
                $endpointDetails = "cURL extension not available";
            }
            
            $endpointStatus = $endpointTest ? 'PASS' : 'FAIL';
            echo "<tr><td>{$description}</td><td class='" . strtolower($endpointStatus) . "'>{$endpointStatus}</td><td>{$endpointDetails}</td></tr>";
            
            // Log results
            logTest("API Endpoint: {$description}", $endpointTest, $endpointDetails);
        }
        
        echo "</table>";
        ?>
        
        <h3>Test CORS Headers</h3>
        <div id="cors-test-result">
            <p>Click the button below to test CORS from JavaScript:</p>
            <button id="test-cors" class="button">Test CORS Headers</button>
        </div>
        
        <script>
            document.getElementById('test-cors').addEventListener('click', function() {
                const resultDiv = document.getElementById('cors-test-result');
                
                // Add loading indicator
                resultDiv.innerHTML = '<p>Testing CORS headers...</p>';
                
                // Attempt CORS request to the CORS test endpoint
                fetch('http://localhost/PROJECT-BITRADER/backend/cors_test.php', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    // Get all headers
                    const headers = {};
                    response.headers.forEach((value, name) => {
                        headers[name] = value;
                    });
                    
                    // Create result HTML
                    let html = '<h4>CORS Test Result:</h4>';
                    html += `<p class="pass">✅ CORS request successful (Status: ${response.status})</p>`;
                    html += '<h4>Response Headers:</h4>';
                    html += '<pre>' + JSON.stringify(headers, null, 2) + '</pre>';
                    
                    // Check for specific CORS headers
                    const corsHeaders = [
                        'access-control-allow-origin',
                        'access-control-allow-methods',
                        'access-control-allow-headers'
                    ];
                    
                    html += '<h4>CORS Headers Check:</h4>';
                    html += '<ul>';
                    
                    corsHeaders.forEach(header => {
                        const hasHeader = headers[header] !== undefined;
                        html += `<li class="${hasHeader ? 'pass' : 'fail'}">
                            ${hasHeader ? '✅' : '❌'} ${header}: 
                            ${hasHeader ? headers[header] : 'Not present'}
                        </li>`;
                    });
                    
                    html += '</ul>';
                    
                    // Add the button back
                    html += '<button id="test-cors" class="button">Test Again</button>';
                    
                    resultDiv.innerHTML = html;
                    
                    // Re-attach event listener
                    document.getElementById('test-cors').addEventListener('click', arguments.callee);
                    
                    return response.text();
                })
                .then(text => {
                    // Try to parse as JSON
                    try {
                        const data = JSON.parse(text);
                        const pre = document.createElement('pre');
                        pre.textContent = JSON.stringify(data, null, 2);
                        resultDiv.appendChild(pre);
                    } catch (e) {
                        // If not JSON, show text
                        const div = document.createElement('div');
                        div.innerHTML = '<h4>Response Body:</h4>';
                        const pre = document.createElement('pre');
                        pre.textContent = text.substring(0, 500);
                        div.appendChild(pre);
                        resultDiv.appendChild(div);
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `
                        <h4>CORS Test Result:</h4>
                        <p class="fail">❌ CORS request failed</p>
                        <p>Error: ${error.message}</p>
                        <p>This could indicate a CORS issue. Check your .htaccess file and server configuration.</p>
                        <button id="test-cors" class="button">Test Again</button>
                    `;
                    
                    // Re-attach event listener
                    document.getElementById('test-cors').addEventListener('click', arguments.callee);
                });
            });
        </script>
    </div>
    
    <div class="test-section">
        <h2>Browser Information</h2>
        <div id="browser-info">
            <script>
                // Function to get and display browser info
                function displayBrowserInfo() {
                    const browserInfoDiv = document.getElementById('browser-info');
                    
                    // Create table
                    let html = '<table>';
                    html += '<tr><th>Property</th><th>Value</th></tr>';
                    
                    // User Agent
                    html += `<tr><td>User Agent</td><td>${navigator.userAgent}</td></tr>`;
                    
                    // Browser Name & Version
                    const browserInfo = getBrowserInfo();
                    html += `<tr><td>Browser</td><td>${browserInfo}</td></tr>`;
                    
                    // Platform
                    html += `<tr><td>Platform</td><td>${navigator.platform}</td></tr>`;
                    
                    // Cookies Enabled
                    html += `<tr><td>Cookies Enabled</td><td>${navigator.cookieEnabled ? 'Yes' : 'No'}</td></tr>`;
                    
                    // Language
                    html += `<tr><td>Language</td><td>${navigator.language}</td></tr>`;
                    
                    // localStorage & sessionStorage
                    const localStorageAvailable = isStorageAvailable('localStorage');
                    const sessionStorageAvailable = isStorageAvailable('sessionStorage');
                    
                    html += `<tr><td>localStorage</td><td>${localStorageAvailable ? 'Available' : 'Not available'}</td></tr>`;
                    html += `<tr><td>sessionStorage</td><td>${sessionStorageAvailable ? 'Available' : 'Not available'}</td></tr>`;
                    
                    html += '</table>';
                    
                    browserInfoDiv.innerHTML = html;
                }
                
                // Function to detect browser name and version
                function getBrowserInfo() {
                    const ua = navigator.userAgent;
                    let browser = 'Unknown';
                    
                    // Detect Chrome
                    if (ua.indexOf('Chrome') > -1) {
                        browser = 'Google Chrome';
                    }
                    // Detect Firefox
                    else if (ua.indexOf('Firefox') > -1) {
                        browser = 'Mozilla Firefox';
                    }
                    // Detect Safari
                    else if (ua.indexOf('Safari') > -1) {
                        browser = 'Apple Safari';
                    }
                    // Detect Edge
                    else if (ua.indexOf('Edge') > -1) {
                        browser = 'Microsoft Edge';
                    }
                    // Detect IE
                    else if (ua.indexOf('MSIE') > -1 || ua.indexOf('Trident/') > -1) {
                        browser = 'Internet Explorer';
                    }
                    
                    return browser;
                }
                
                // Function to check if storage is available
                function isStorageAvailable(type) {
                    try {
                        const storage = window[type];
                        const testKey = '__storage_test__';
                        storage.setItem(testKey, testKey);
                        storage.removeItem(testKey);
                        return true;
                    } catch (e) {
                        return false;
                    }
                }
                
                // Display browser info when page loads
                displayBrowserInfo();
            </script>
        </div>
    </div>
    
    <div class="test-section">
        <h2>Next Steps & Troubleshooting</h2>
        <p>Based on the tests above, here are some troubleshooting steps you can take:</p>
        <ul>
            <li>If database connectivity failed, check your database configuration in <code>config/database.php</code></li>
            <li>If CORS tests failed, verify your <code>.htaccess</code> file has correct CORS headers</li>
            <li>If API endpoints are not accessible, check file permissions and server configuration</li>
            <li>If local storage is not available, browser restrictions may prevent login from working</li>
        </ul>
        
        <h3>Other Diagnostic Tools:</h3>
        <ul>
            <li><a href="test_login_api.php">Login API Test</a> - Test the login API directly</li>
            <li><a href="fix_database_config.php">Database Configuration Tool</a> - Check and fix database settings</li>
            <li><a href="cors_test.php">CORS Test</a> - Verify CORS headers are being sent correctly</li>
            <li><a href="admin_check.php">Admin User Check</a> - Verify admin user exists in the database</li>
            <li><a href="../Signup-Signin/login-debug.html">Login Debug Tool</a> - Interactive login debugging interface</li>
        </ul>
    </div>
    
    <div>
        <a href="index.php" class="button">Back to Tools</a>
    </div>
</body>
</html> 