<?php
/**
 * Login API Test
 * This file helps debug issues with the login API
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to HTML for better display
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login API Debug Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }
        h1, h2 {
            color: #333;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 4px;
        }
        .response {
            margin-top: 20px;
        }
        #responseData {
            white-space: pre-wrap;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .code {
            font-family: monospace;
        }
    </style>
</head>
<body>
    <h1>Login API Debug Tool</h1>
    
    <h2>Test Login with Existing Users</h2>
    
    <?php
    // Function to safely show database entries
    function showDatabaseUsers() {
        try {
            require_once __DIR__ . '/config/database.php';
            
            $conn = getConnection();
            $result = $conn->query("SELECT id, username, email, role FROM users ORDER BY id");
            
            if ($result->num_rows > 0) {
                echo '<h3>Existing Users in Database</h3>';
                echo '<table>';
                echo '<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Action</th></tr>';
                
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['username'] . '</td>';
                    echo '<td>' . $row['email'] . '</td>';
                    echo '<td>' . $row['role'] . '</td>';
                    echo '<td><button onclick="testLogin(\'' . htmlspecialchars($row['username']) . '\', \'password123\')">Test Login</button></td>';
                    echo '</tr>';
                }
                
                echo '</table>';
            } else {
                echo '<p class="error">No users found in database.</p>';
            }
            
            $conn->close();
        } catch (Exception $e) {
            echo '<p class="error">Error connecting to database: ' . $e->getMessage() . '</p>';
        }
    }
    
    // Display users
    showDatabaseUsers();
    ?>
    
    <h2>Manual Login Test</h2>
    
    <div class="form-group">
        <label for="username">Username or Email:</label>
        <input type="text" id="username" name="username" value="admin">
    </div>
    
    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" value="admin123">
    </div>
    
    <button id="testButton">Test Login API</button>
    
    <div class="response">
        <h3>API Response:</h3>
        <pre id="responseData">Results will appear here...</pre>
    </div>
    
    <h2>Login API Details</h2>
    <p>Current API URL: <span class="code">http://localhost/PROJECT-BITRADER/backend/direct-login.php</span></p>
    
    <h2>JavaScript Integration in login.js</h2>
    <pre class="code">
fetch(LOGIN_API, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        username: username,
        password: password
    })
})
.then(response => response.json())
.then(data => {
    console.log("API response:", data);
    
    if (data.success) {
        // Login successful
        showSuccess('Login successful! Redirecting...');
        
        // Save token and user data to local storage
        localStorage.setItem('authToken', data.data.token);
        localStorage.setItem('userData', JSON.stringify(data.data.user));
        
        // Redirect to dashboard after a short delay
        setTimeout(() => {
            window.location.href = '../../coinex/dashboard/index.php';
        }, 1500);
    } else {
        // Login failed
        showError(data.message || 'Invalid username or password. Please try again.');
    }
})
.catch(error => {
    console.error("API error:", error);
    showError('Unable to connect to server. Please try again later.');
})
    </pre>
    
    <h2>Fix For Frontend</h2>
    <p>If the login API is working correctly but the frontend is showing "Unable to connect to server", the issue may be in how your frontend JavaScript is handling the API response. Make sure that:</p>
    <ol>
        <li>The API URL in your frontend is correct</li>
        <li>The response is properly formatted as JSON</li>
        <li>CORS headers are properly set on the server</li>
        <li>The browser console doesn't show any JavaScript errors</li>
    </ol>
    
    <script>
        document.getElementById('testButton').addEventListener('click', function() {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            testLogin(username, password);
        });
        
        function testLogin(username, password) {
            const responseData = document.getElementById('responseData');
            responseData.textContent = 'Testing login...';
            
            const url = 'http://localhost/PROJECT-BITRADER/backend/direct-login.php';
            
            fetch(url, {
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
                // Get status and headers
                const status = response.status;
                const headers = {};
                for (const [key, value] of response.headers) {
                    headers[key] = value;
                }
                
                // Get response body as text first to make sure it's valid JSON
                return response.text().then(text => {
                    try {
                        // Try to parse as JSON
                        const data = JSON.parse(text);
                        return {
                            status,
                            headers,
                            body: data,
                            rawBody: text
                        };
                    } catch (e) {
                        // If not valid JSON, return the raw text
                        return {
                            status,
                            headers,
                            body: null,
                            rawBody: text,
                            error: 'Invalid JSON: ' + e.message
                        };
                    }
                });
            })
            .then(result => {
                // Display the full response
                responseData.innerHTML = '';
                
                // Status
                responseData.innerHTML += '<strong>Status:</strong> ' + result.status + '\n\n';
                
                // Headers
                responseData.innerHTML += '<strong>Headers:</strong>\n';
                for (const [key, value] of Object.entries(result.headers)) {
                    responseData.innerHTML += key + ': ' + value + '\n';
                }
                responseData.innerHTML += '\n';
                
                // Body
                if (result.error) {
                    responseData.innerHTML += '<strong class="error">Error:</strong> ' + result.error + '\n\n';
                    responseData.innerHTML += '<strong>Raw Response:</strong>\n' + result.rawBody;
                } else {
                    responseData.innerHTML += '<strong>Response Body:</strong>\n' + JSON.stringify(result.body, null, 2);
                    
                    // Expected structure check
                    if (result.body) {
                        responseData.innerHTML += '\n\n<strong>Structure Check:</strong>\n';
                        
                        // Check for success property
                        if (result.body.hasOwnProperty('success')) {
                            responseData.innerHTML += '✅ Has "success" property\n';
                        } else {
                            responseData.innerHTML += '❌ Missing "success" property\n';
                        }
                        
                        // Check for message property
                        if (result.body.hasOwnProperty('message')) {
                            responseData.innerHTML += '✅ Has "message" property\n';
                        } else {
                            responseData.innerHTML += '❌ Missing "message" property\n';
                        }
                        
                        // Check for data property if success is true
                        if (result.body.success === true) {
                            if (result.body.hasOwnProperty('data')) {
                                responseData.innerHTML += '✅ Has "data" property\n';
                                
                                const data = result.body.data;
                                
                                // Check for token
                                if (data.hasOwnProperty('token')) {
                                    responseData.innerHTML += '✅ Has "token" property\n';
                                } else {
                                    responseData.innerHTML += '❌ Missing "token" property\n';
                                }
                                
                                // Check for user data
                                if (data.hasOwnProperty('user')) {
                                    responseData.innerHTML += '✅ Has "user" property\n';
                                } else {
                                    responseData.innerHTML += '❌ Missing "user" property\n';
                                }
                            } else {
                                responseData.innerHTML += '❌ Missing "data" property\n';
                            }
                        }
                    }
                }
            })
            .catch(error => {
                responseData.textContent = 'Error: ' + error.message;
            });
        }
    </script>
</body>
</html> 