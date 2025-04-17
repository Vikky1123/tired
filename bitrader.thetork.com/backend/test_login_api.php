<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiTrader Login API Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
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
        .section {
            background: #f9f9f9;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 5px solid #3498db;
        }
        .success {
            color: #27ae60;
            font-weight: bold;
        }
        .error {
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
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .test-result {
            padding: 10px;
            margin-top: 10px;
            border-radius: 4px;
        }
        .test-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .test-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .code {
            font-family: monospace;
            background: #f1f1f1;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .actions {
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h1>BiTrader Login API Test</h1>
    
    <div class="section">
        <h2>Login API Configuration</h2>
        <?php
        // Define the login API path
        $login_api_path = __DIR__ . '/direct-login.php';
        
        // Check if the login API file exists
        if (file_exists($login_api_path)) {
            echo "<p class='success'>✅ Login API file found at: {$login_api_path}</p>";
            
            // Get file info
            $fileInfo = [
                'Size' => filesize($login_api_path) . ' bytes',
                'Last Modified' => date('Y-m-d H:i:s', filemtime($login_api_path)),
                'Permissions' => substr(sprintf('%o', fileperms($login_api_path)), -4)
            ];
            
            echo "<table>";
            echo "<tr><th>Property</th><th>Value</th></tr>";
            foreach ($fileInfo as $property => $value) {
                echo "<tr><td>{$property}</td><td>{$value}</td></tr>";
            }
            echo "</table>";
            
            // Read first few lines of the file to confirm it's the right file
            $contents = file_get_contents($login_api_path, false, null, 0, 500);
            $firstLines = implode("\n", array_slice(explode("\n", $contents), 0, 10));
            echo "<h3>File Preview:</h3>";
            echo "<pre>" . htmlspecialchars($firstLines) . "...</pre>";
        } else {
            echo "<p class='error'>❌ Login API file not found at: {$login_api_path}</p>";
            echo "<p>Please ensure the direct-login.php file exists in the backend directory.</p>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>Server-Side Login Test</h2>
        <?php
        // Define test credentials - use default admin if available
        $testUsername = 'admin';
        $testPassword = 'admin123';
        
        // Function to test the login API
        function testLoginAPI($username, $password) {
            // Create the request data
            $requestData = [
                'username' => $username,
                'password' => $password
            ];
            
            // Encode the request data as JSON
            $jsonData = json_encode($requestData);
            
            // Initialize cURL
            $ch = curl_init();
            
            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, 'http://localhost/PROJECT-BITRADER/backend/direct-login.php');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ]);
            
            // Execute cURL request
            $response = curl_exec($ch);
            $info = curl_getinfo($ch);
            $error = curl_error($ch);
            
            // Close cURL
            curl_close($ch);
            
            return [
                'response' => $response,
                'info' => $info,
                'error' => $error
            ];
        }
        
        // Run the test if login API exists
        if (file_exists($login_api_path)) {
            // Test login
            $testResult = testLoginAPI($testUsername, $testPassword);
            
            // Display request
            echo "<h3>Test Request:</h3>";
            echo "<pre>" . json_encode(['username' => $testUsername, 'password' => '***********'], JSON_PRETTY_PRINT) . "</pre>";
            
            // Display response info
            echo "<h3>Response Info:</h3>";
            echo "<table>";
            echo "<tr><th>Property</th><th>Value</th></tr>";
            echo "<tr><td>HTTP Status</td><td>{$testResult['info']['http_code']}</td></tr>";
            echo "<tr><td>Response Time</td><td>{$testResult['info']['total_time']} seconds</td></tr>";
            echo "<tr><td>Content Type</td><td>{$testResult['info']['content_type']}</td></tr>";
            echo "</table>";
            
            // Check for cURL errors
            if (!empty($testResult['error'])) {
                echo "<div class='test-result test-error'>";
                echo "<p class='error'>❌ cURL Error: {$testResult['error']}</p>";
                echo "</div>";
            } else {
                // Try to decode the response
                $decodedResponse = json_decode($testResult['response'], true);
                
                if ($decodedResponse === null) {
                    echo "<div class='test-result test-error'>";
                    echo "<p class='error'>❌ Invalid JSON response</p>";
                    echo "<pre>" . htmlspecialchars(substr($testResult['response'], 0, 500)) . (strlen($testResult['response']) > 500 ? '...' : '') . "</pre>";
                    echo "</div>";
                } else {
                    // Check if login was successful
                    if (isset($decodedResponse['success']) && $decodedResponse['success'] === true) {
                        echo "<div class='test-result test-success'>";
                        echo "<p class='success'>✅ Login successful!</p>";
                        
                        // Verify token exists
                        if (isset($decodedResponse['data']['token'])) {
                            echo "<p class='success'>✅ Token received</p>";
                            // Mask token for display
                            $token = $decodedResponse['data']['token'];
                            $maskedToken = substr($token, 0, 5) . '...' . substr($token, -5);
                            echo "<p>Token: {$maskedToken} (Length: " . strlen($token) . " characters)</p>";
                        } else {
                            echo "<p class='error'>❌ No token received in response</p>";
                        }
                        
                        // Verify user data exists
                        if (isset($decodedResponse['data']['user'])) {
                            echo "<p class='success'>✅ User data received</p>";
                            // Display user data without sensitive info
                            $userData = $decodedResponse['data']['user'];
                            if (isset($userData['password'])) {
                                $userData['password'] = '**********';
                            }
                            echo "<pre>" . json_encode($userData, JSON_PRETTY_PRINT) . "</pre>";
                        } else {
                            echo "<p class='error'>❌ No user data received in response</p>";
                        }
                        
                        echo "</div>";
                    } else {
                        echo "<div class='test-result test-error'>";
                        echo "<p class='error'>❌ Login failed</p>";
                        echo "<pre>" . json_encode($decodedResponse, JSON_PRETTY_PRINT) . "</pre>";
                        echo "</div>";
                        
                        echo "<p class='warning'>Note: If you're seeing an authentication failure, verify that the admin user exists in the database or try creating it using the <a href='admin_check.php'>admin_check.php</a> tool.</p>";
                    }
                }
            }
        } else {
            echo "<p class='error'>Cannot perform login test as the API file is missing.</p>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>JavaScript Client Test</h2>
        <p>Test the login API from the client side using JavaScript fetch:</p>
        
        <div>
            <div style="margin-bottom: 15px;">
                <label for="js-username">Username:</label><br>
                <input type="text" id="js-username" value="admin" style="padding: 8px; width: 300px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="js-password">Password:</label><br>
                <input type="password" id="js-password" value="admin123" style="padding: 8px; width: 300px;">
            </div>
            
            <button id="test-login" class="btn">Test Login</button>
        </div>
        
        <div id="js-result" style="margin-top: 15px;"></div>
        
        <script>
            document.getElementById('test-login').addEventListener('click', function() {
                const username = document.getElementById('js-username').value.trim();
                const password = document.getElementById('js-password').value;
                const resultElement = document.getElementById('js-result');
                
                if (!username || !password) {
                    resultElement.innerHTML = '<div class="test-result test-error">Please enter both username and password</div>';
                    return;
                }
                
                // Show loading
                resultElement.innerHTML = '<div style="padding: 10px;">Testing login, please wait...</div>';
                
                // Send login request
                fetch('http://localhost/PROJECT-BITRADER/backend/direct-login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        username: username,
                        password: password
                    })
                })
                .then(response => {
                    // Get status and headers
                    const status = response.status;
                    const headers = {};
                    response.headers.forEach((value, name) => {
                        headers[name] = value;
                    });
                    
                    // Get response text
                    return response.text().then(text => {
                        return {
                            status: status,
                            headers: headers,
                            text: text
                        };
                    });
                })
                .then(data => {
                    let output = `<div class="test-result ${data.status >= 200 && data.status < 300 ? 'test-success' : 'test-error'}">`;
                    
                    // Status information
                    output += `<p><strong>Status:</strong> ${data.status}</p>`;
                    
                    // Headers (important for CORS)
                    output += `<p><strong>Headers:</strong></p>`;
                    output += `<pre>${JSON.stringify(data.headers, null, 2)}</pre>`;
                    
                    // Try to parse response as JSON
                    try {
                        const json = JSON.parse(data.text);
                        output += `<p><strong>Response (JSON):</strong></p>`;
                        output += `<pre>${JSON.stringify(json, null, 2)}</pre>`;
                        
                        // Check for success
                        if (json.success) {
                            output += `<p class="success">✅ Login successful!</p>`;
                            
                            // If we got a token, show it
                            if (json.data && json.data.token) {
                                const token = json.data.token;
                                const maskedToken = token.substring(0, 5) + '...' + token.substring(token.length - 5);
                                output += `<p><strong>Token:</strong> ${maskedToken} (Length: ${token.length})</p>`;
                                
                                // Test storing token in localStorage
                                try {
                                    localStorage.setItem('test_token', token);
                                    const retrievedToken = localStorage.getItem('test_token');
                                    if (retrievedToken === token) {
                                        output += `<p class="success">✅ localStorage storage test successful</p>`;
                                    } else {
                                        output += `<p class="error">❌ localStorage test failed: stored value doesn't match</p>`;
                                    }
                                    localStorage.removeItem('test_token');
                                } catch (e) {
                                    output += `<p class="error">❌ localStorage test failed: ${e.message}</p>`;
                                }
                            } else {
                                output += `<p class="error">❌ No token received in response</p>`;
                            }
                        } else {
                            output += `<p class="error">❌ Login failed: ${json.message || 'No error message provided'}</p>`;
                        }
                    } catch (e) {
                        output += `<p class="error">❌ Invalid JSON response: ${e.message}</p>`;
                        output += `<pre>${data.text.substring(0, 500)}${data.text.length > 500 ? '...' : ''}</pre>`;
                    }
                    
                    output += `</div>`;
                    resultElement.innerHTML = output;
                })
                .catch(error => {
                    resultElement.innerHTML = `
                        <div class="test-result test-error">
                            <p class="error">❌ Fetch error: ${error.message}</p>
                            <p>This could indicate a CORS issue. Check your browser console for more details.</p>
                        </div>
                    `;
                });
            });
        </script>
    </div>
    
    <div class="section">
        <h2>Troubleshooting Steps</h2>
        <ul>
            <li><strong>Database Connection:</strong> Ensure your database is connected properly by visiting <a href="fix_database_config.php">Database Configuration Tool</a></li>
            <li><strong>Admin User:</strong> Make sure the admin user exists in the database by visiting <a href="admin_check.php">Admin User Check</a></li>
            <li><strong>CORS Issues:</strong> Test CORS configuration with <a href="cors_test.php">CORS Test</a></li>
            <li><strong>Login Debug:</strong> Use the visual login debugger at <a href="../Signup-Signin/login-debug.html">Login Debug Tool</a></li>
            <li><strong>API Logs:</strong> Check login logs with <a href="check_login_logs.php">Login Logs Viewer</a></li>
        </ul>
    </div>
    
    <div class="actions">
        <a href="index.php" class="btn">Back to Tools</a>
        <a href="../Signup-Signin/index.html" class="btn">Go to Login Page</a>
    </div>
</body>
</html> 