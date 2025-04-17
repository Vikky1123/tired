<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for HTML output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Response Verification</title>
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
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        #result {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>API Response Verification</h1>
    <p>This tool verifies that the login API is responding correctly and returning data in the expected format.</p>
    
    <h2>Test Login API</h2>
    <p>Enter credentials to test the login API response:</p>
    
    <form id="test-form">
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="admin" required>
        </div>
        <div style="margin-top: 10px;">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" value="admin123" required>
        </div>
        <button type="submit">Test Login API</button>
    </form>
    
    <div id="result"></div>
    
    <h2>PHP Direct Test</h2>
    <?php
    // Direct PHP test using curl
    function testLoginApi($username, $password) {
        $apiUrl = 'http://localhost/PROJECT-BITRADER/backend/direct-login.php';
        
        $data = [
            'username' => $username,
            'password' => $password
        ];
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'status_code' => $httpCode,
            'response' => $response,
            'error' => $error
        ];
    }
    
    // Run the test
    $testResult = testLoginApi('admin', 'admin123');
    
    echo "<h3>Server-side API Test Result:</h3>";
    echo "<p>Status Code: " . $testResult['status_code'] . "</p>";
    
    if ($testResult['error']) {
        echo "<p class='error'>Error: " . htmlspecialchars($testResult['error']) . "</p>";
    } elseif ($testResult['status_code'] >= 200 && $testResult['status_code'] < 300) {
        echo "<p class='success'>API Request Successful</p>";
        
        // Parse and validate response structure
        $responseData = json_decode($testResult['response'], true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p>Response is valid JSON</p>";
            
            // Check response structure
            $hasSuccess = isset($responseData['success']);
            $hasData = isset($responseData['data']);
            $hasToken = $hasData && isset($responseData['data']['token']);
            $hasUser = $hasData && isset($responseData['data']['user']);
            
            echo "<p>Response structure check:</p>";
            echo "<ul>";
            echo "<li>Has 'success' field: " . ($hasSuccess ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</li>";
            echo "<li>Has 'data' field: " . ($hasData ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</li>";
            echo "<li>Has 'token' field: " . ($hasToken ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</li>";
            echo "<li>Has 'user' field: " . ($hasUser ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</li>";
            echo "</ul>";
            
            echo "<p>Raw response:</p>";
            echo "<pre>" . htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT)) . "</pre>";
        } else {
            echo "<p class='error'>Response is not valid JSON</p>";
            echo "<p>Raw response:</p>";
            echo "<pre>" . htmlspecialchars($testResult['response']) . "</pre>";
        }
    } else {
        echo "<p class='error'>API Request Failed</p>";
        echo "<p>Raw response:</p>";
        echo "<pre>" . htmlspecialchars($testResult['response']) . "</pre>";
    }
    ?>
    
    <h2>JavaScript Fetch Test</h2>
    <p>This tests the login API using JavaScript fetch, which is how your login form works:</p>
    <button id="js-test-btn">Test Login API via JavaScript</button>
    <div id="js-result"></div>
    
    <script>
        // Form submission handler
        document.getElementById('test-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const resultDiv = document.getElementById('result');
            
            resultDiv.innerHTML = '<p>Testing...</p>';
            
            // Make API request
            fetch('http://localhost/PROJECT-BITRADER/backend/direct-login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    username: username,
                    password: password
                })
            })
            .then(response => {
                const statusInfo = `<p>Status: <strong>${response.status}</strong></p>`;
                
                return response.text().then(text => {
                    return {
                        status: response.status,
                        text: text
                    };
                });
            })
            .then(data => {
                let output = `<p>Status: <strong>${data.status}</strong></p>`;
                
                try {
                    const json = JSON.parse(data.text);
                    output += '<p class="success">Response is valid JSON</p>';
                    output += '<p>Response structure check:</p><ul>';
                    output += `<li>Has 'success' field: ${json.success !== undefined ? '<span class="success">Yes</span>' : '<span class="error">No</span>'}</li>`;
                    output += `<li>Has 'data' field: ${json.data !== undefined ? '<span class="success">Yes</span>' : '<span class="error">No</span>'}</li>`;
                    
                    if (json.data) {
                        output += `<li>Has 'token' field: ${json.data.token !== undefined ? '<span class="success">Yes</span>' : '<span class="error">No</span>'}</li>`;
                        output += `<li>Has 'user' field: ${json.data.user !== undefined ? '<span class="success">Yes</span>' : '<span class="error">No</span>'}</li>`;
                    }
                    
                    output += '</ul>';
                    output += '<p>Raw response:</p>';
                    output += `<pre>${JSON.stringify(json, null, 2)}</pre>`;
                } catch (e) {
                    output += '<p class="error">Response is not valid JSON</p>';
                    output += '<p>Raw response:</p>';
                    output += `<pre>${data.text}</pre>`;
                }
                
                resultDiv.innerHTML = output;
            })
            .catch(error => {
                resultDiv.innerHTML = `<p class="error">Error: ${error.message}</p>`;
            });
        });
        
        // JavaScript test button
        document.getElementById('js-test-btn').addEventListener('click', function() {
            const resultDiv = document.getElementById('js-result');
            resultDiv.innerHTML = '<p>Testing...</p>';
            
            fetch('http://localhost/PROJECT-BITRADER/backend/direct-login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    username: 'admin',
                    password: 'admin123'
                })
            })
            .then(response => {
                return response.text().then(text => {
                    return {
                        status: response.status,
                        text: text
                    };
                });
            })
            .then(data => {
                let output = `<p>Status: <strong>${data.status}</strong></p>`;
                
                try {
                    const json = JSON.parse(data.text);
                    output += '<p class="success">Successfully parsed JSON response</p>';
                    output += '<p>Raw response:</p>';
                    output += `<pre>${JSON.stringify(json, null, 2)}</pre>`;
                    
                    // Test localStorage
                    if (json.success && json.data && json.data.token) {
                        output += '<p>Testing localStorage...</p>';
                        try {
                            localStorage.setItem('test_token', json.data.token);
                            localStorage.setItem('test_user', JSON.stringify(json.data.user));
                            
                            const savedToken = localStorage.getItem('test_token');
                            if (savedToken === json.data.token) {
                                output += '<p class="success">localStorage working correctly!</p>';
                            } else {
                                output += '<p class="error">localStorage not working correctly</p>';
                            }
                            
                            // Clean up
                            localStorage.removeItem('test_token');
                            localStorage.removeItem('test_user');
                        } catch (e) {
                            output += `<p class="error">localStorage error: ${e.message}</p>`;
                        }
                    }
                } catch (e) {
                    output += '<p class="error">Failed to parse JSON response</p>';
                    output += '<p>Raw response:</p>';
                    output += `<pre>${data.text}</pre>`;
                }
                
                resultDiv.innerHTML = output;
            })
            .catch(error => {
                resultDiv.innerHTML = `<p class="error">Fetch error: ${error.message}</p>`;
            });
        });
    </script>
    
    <h2>Recommendations</h2>
    <ul>
        <li>If the PHP test works but the JavaScript test fails, you likely have a CORS issue.</li>
        <li>If both tests work but the login page still doesn't redirect, the issue is in the login form's JavaScript.</li>
        <li>Check that your login page's JavaScript successfully sets items in localStorage and redirects to the dashboard.</li>
    </ul>
    
    <p>
        <a href="fix_database_config.php" style="display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;">Database Config</a>
        <a href="debug_connection.php" style="display: inline-block; padding: 10px 15px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;">Debug Connection</a>
        <a href="login_test.html" style="display: inline-block; padding: 10px 15px; background-color: #FF9800; color: white; text-decoration: none; border-radius: 4px;">Login Test</a>
    </p>
</body>
</html> 