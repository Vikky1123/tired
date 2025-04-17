<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for HTML output
header('Content-Type: text/html; charset=utf-8');

// Test credentials
$username = isset($_GET['username']) ? $_GET['username'] : 'admin';
$password = isset($_GET['password']) ? $_GET['password'] : 'admin123';

echo "<h1>Direct Login API Test</h1>";
echo "<p>Testing login API with username: <strong>{$username}</strong></p>";

// Function to test direct login API
function testDirectLogin($username, $password) {
    // The API URL
    $apiUrl = 'http://localhost/PROJECT-BITRADER/backend/direct-login.php';
    
    // Prepare the data
    $data = [
        'username' => $username,
        'password' => $password
    ];
    
    // Initialize cURL
    $ch = curl_init($apiUrl);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    // Execute the request
    $response = curl_exec($ch);
    
    // Get info about the request
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    
    // Close cURL
    curl_close($ch);
    
    // Return results
    return [
        'status_code' => $info['http_code'],
        'response' => $response,
        'error' => $error,
        'info' => $info
    ];
}

// Run the test
$result = testDirectLogin($username, $password);

// Display results
echo "<h2>API Response</h2>";
echo "<p>Status Code: <strong>{$result['status_code']}</strong></p>";

if ($result['error']) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($result['error']) . "</p>";
} else {
    echo "<h3>Raw Response:</h3>";
    echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars($result['response']);
    echo "</pre>";
    
    // Try to parse JSON
    $json = json_decode($result['response'], true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<h3>Parsed JSON:</h3>";
        echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
        print_r($json);
        echo "</pre>";
        
        // Check if login succeeded
        if (isset($json['success']) && $json['success'] === true) {
            echo "<p style='color: green;'>✅ Login Successful!</p>";
            if (isset($json['data']['token'])) {
                echo "<p>Token: <code>" . substr($json['data']['token'], 0, 20) . "...</code></p>";
            }
            if (isset($json['data']['user'])) {
                echo "<p>User data received.</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Login Failed</p>";
            if (isset($json['message'])) {
                echo "<p>Message: " . htmlspecialchars($json['message']) . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>Failed to parse response as JSON: " . json_last_error_msg() . "</p>";
    }
}

// Display form to test with other credentials
echo "<h2>Test with Different Credentials</h2>";
echo "<form method='get'>";
echo "<p><label>Username: <input type='text' name='username' value='" . htmlspecialchars($username) . "'></label></p>";
echo "<p><label>Password: <input type='password' name='password' value='" . htmlspecialchars($password) . "'></label></p>";
echo "<p><button type='submit'>Test Login</button></p>";
echo "</form>";

// Check if direct-login.php file exists
$apiFile = __DIR__ . '/direct-login.php';
if (file_exists($apiFile)) {
    echo "<h2>API File Information</h2>";
    echo "<p>API file exists at: " . htmlspecialchars($apiFile) . "</p>";
    echo "<p>File size: " . filesize($apiFile) . " bytes</p>";
    echo "<p>Last modified: " . date("Y-m-d H:i:s", filemtime($apiFile)) . "</p>";
} else {
    echo "<h2>API File Information</h2>";
    echo "<p style='color: red;'>API file does not exist at: " . htmlspecialchars($apiFile) . "</p>";
}

echo "<h2>Test Links</h2>";
echo "<ul>";
echo "<li><a href='debug_connection.php'>Run Comprehensive Debug</a></li>";
echo "<li><a href='verify_api_response.php'>API Response Verification</a></li>";
echo "<li><a href='login_test.html'>Login Test Page</a></li>";
echo "</ul>";
?> 