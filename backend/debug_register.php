<?php
/**
 * Register API Endpoint Debugger
 * This script helps diagnose issues with the register API endpoint
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Register API Endpoint Debugger</h1>";
echo "<p>This tool tests the registration API endpoint directly.</p>";

// Include required files (but catch errors in case they don't exist)
try {
    if (file_exists(__DIR__ . '/config/env.php')) {
        require_once __DIR__ . '/config/env.php';
    }
    
    if (file_exists(__DIR__ . '/config/database.php')) {
        require_once __DIR__ . '/config/database.php';
    }
    
    if (file_exists(__DIR__ . '/utils/db_utils.php')) {
        require_once __DIR__ . '/utils/db_utils.php';
    }
    
    if (file_exists(__DIR__ . '/utils/auth_utils.php')) {
        require_once __DIR__ . '/utils/auth_utils.php';
    }
} catch (Exception $e) {
    echo "<div style='padding: 15px; background-color: #f8d7da; border-radius: 5px; margin-bottom: 20px;'>";
    echo "<strong>Error loading configuration:</strong> " . $e->getMessage();
    echo "</div>";
}

// Function to make an internal API call
function testRegisterAPI($userData) {
    // Save current error reporting settings
    $oldErrorReporting = error_reporting();
    $oldDisplayErrors = ini_get('display_errors');
    
    // Temporarily disable error reporting to capture output
    error_reporting(0);
    ini_set('display_errors', 0);
    
    // Start output buffering
    ob_start();
    
    // Create a mock $_POST array
    $_POST = $userData;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Include the register.php file if it exists
    $apiFile = __DIR__ . '/api/auth/register.php';
    if (file_exists($apiFile)) {
        // Include the file
        include $apiFile;
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Register API file not found: ' . $apiFile
        ]);
    }
    
    // Get the output
    $output = ob_get_clean();
    
    // Restore error reporting settings
    error_reporting($oldErrorReporting);
    ini_set('display_errors', $oldDisplayErrors);
    
    // Try to decode JSON response
    $jsonResponse = json_decode($output, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        return [
            'success' => ($jsonResponse['status'] === 'success'),
            'response' => $jsonResponse,
            'raw_output' => $output
        ];
    } else {
        return [
            'success' => false,
            'response' => null,
            'raw_output' => $output
        ];
    }
}

// Function to directly test database connection and user creation
function testDirectDatabaseRegistration($userData) {
    try {
        // Check if required functions exist
        if (!function_exists('getDBConnection')) {
            return [
                'success' => false,
                'message' => 'Database utility functions not found'
            ];
        }
        
        // Connect to database
        $conn = getDBConnection();
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Failed to connect to database'
            ];
        }
        
        // Check if user already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $userData['username'], $userData['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return [
                'success' => false,
                'message' => 'User with this username or email already exists'
            ];
        }
        
        // Hash password
        $hashedPassword = function_exists('hashPassword') 
            ? hashPassword($userData['password']) 
            : password_hash($userData['password'], PASSWORD_BCRYPT);
        
        // Create user
        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, full_name, phone, country, role, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'user', NOW(), NOW())
        ");
        
        $stmt->bind_param(
            "ssssss", 
            $userData['username'], 
            $userData['email'], 
            $hashedPassword, 
            $userData['full_name'], 
            $userData['phone'], 
            $userData['country']
        );
        
        if ($stmt->execute()) {
            $userId = $conn->insert_id;
            return [
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $userId
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Database error: ' . $stmt->error
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Exception: ' . $e->getMessage()
        ];
    }
}

// Check if the register.php file exists
$registerApiPath = __DIR__ . '/api/auth/register.php';
$registerApiExists = file_exists($registerApiPath);

echo "<div style='margin-bottom: 20px; padding: 15px; " . 
     ($registerApiExists ? "background-color: #d4edda;" : "background-color: #f8d7da;") . 
     " border-radius: 5px;'>";
echo "<strong>Register API File:</strong> " . 
     ($registerApiExists ? "Found at {$registerApiPath}" : "Not found at {$registerApiPath}");
echo "</div>";

// Display test form
echo "<h2>Test Registration</h2>";
echo "<form method='post' action='' style='background-color: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px;'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='username'>Username:</label><br>";
echo "<input type='text' id='username' name='username' value='testuser" . rand(1000, 9999) . "' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='email'>Email:</label><br>";
echo "<input type='email' id='email' name='email' value='test" . rand(1000, 9999) . "@example.com' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='password'>Password:</label><br>";
echo "<input type='password' id='password' name='password' value='Password123' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='confirm_password'>Confirm Password:</label><br>";
echo "<input type='password' id='confirm_password' name='confirm_password' value='Password123' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='full_name'>Full Name:</label><br>";
echo "<input type='text' id='full_name' name='full_name' value='Test User' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='phone'>Phone:</label><br>";
echo "<input type='text' id='phone' name='phone' value='1234567890' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='country'>Country:</label><br>";
echo "<input type='text' id='country' name='country' value='United States' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<div>";
echo "<button type='submit' name='test_api' style='padding: 8px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;'>Test API</button>";
echo "<button type='submit' name='test_db' style='padding: 8px 15px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;'>Test Direct DB</button>";
echo "</div>";
echo "</form>";

// Process test
if (isset($_POST['test_api']) || isset($_POST['test_db'])) {
    $userData = [
        'username' => $_POST['username'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'full_name' => $_POST['full_name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'country' => $_POST['country'] ?? ''
    ];
    
    echo "<h3>Test Results</h3>";
    
    // Validate input
    $errors = [];
    if (empty($userData['username'])) $errors[] = "Username is required";
    if (empty($userData['email'])) $errors[] = "Email is required";
    if (empty($userData['password'])) $errors[] = "Password is required";
    if ($userData['password'] !== $userData['confirm_password']) $errors[] = "Passwords do not match";
    
    if (!empty($errors)) {
        echo "<div style='padding: 15px; background-color: #f8d7da; border-radius: 5px; margin-bottom: 10px;'>";
        echo "<strong>Validation errors:</strong><br>";
        foreach ($errors as $error) {
            echo "- {$error}<br>";
        }
        echo "</div>";
    } else {
        // Test API or DB directly
        if (isset($_POST['test_api'])) {
            $apiResult = testRegisterAPI($userData);
            
            echo "<div style='padding: 15px; " . 
                 ($apiResult['success'] ? "background-color: #d4edda;" : "background-color: #f8d7da;") . 
                 " border-radius: 5px; margin-bottom: 10px;'>";
            echo "<strong>API Test Result:</strong> " . 
                 ($apiResult['success'] ? "Success" : "Failed") . "<br><br>";
            
            // Show JSON response if available
            if ($apiResult['response']) {
                echo "<strong>API Response:</strong><br>";
                echo "<pre>" . json_encode($apiResult['response'], JSON_PRETTY_PRINT) . "</pre>";
            }
            
            // Show raw output if JSON parsing failed
            if (!$apiResult['response'] && !empty($apiResult['raw_output'])) {
                echo "<strong>Raw API Output:</strong><br>";
                echo "<pre>" . htmlspecialchars($apiResult['raw_output']) . "</pre>";
            }
            
            echo "</div>";
        }
        
        if (isset($_POST['test_db'])) {
            $dbResult = testDirectDatabaseRegistration($userData);
            
            echo "<div style='padding: 15px; " . 
                 ($dbResult['success'] ? "background-color: #d4edda;" : "background-color: #f8d7da;") . 
                 " border-radius: 5px; margin-bottom: 10px;'>";
            echo "<strong>Direct DB Test Result:</strong> " . 
                 ($dbResult['success'] ? "Success" : "Failed") . "<br>";
            echo "<strong>Message:</strong> " . $dbResult['message'] . "<br>";
            
            if (isset($dbResult['user_id'])) {
                echo "<strong>Created User ID:</strong> " . $dbResult['user_id'] . "<br>";
            }
            
            echo "</div>";
        }
    }
}

// Check if main API files exist
echo "<h2>API File Check</h2>";
$apiDir = __DIR__ . '/api';
$apiAuthDir = $apiDir . '/auth';
$loginFile = $apiAuthDir . '/login.php';
$registerFile = $apiAuthDir . '/register.php';

$missingFiles = [];
if (!file_exists($apiDir)) $missingFiles[] = $apiDir;
if (!file_exists($apiAuthDir)) $missingFiles[] = $apiAuthDir;
if (!file_exists($loginFile)) $missingFiles[] = $loginFile;
if (!file_exists($registerFile)) $missingFiles[] = $registerFile;

if (empty($missingFiles)) {
    echo "<div style='padding: 15px; background-color: #d4edda; border-radius: 5px; margin-bottom: 10px;'>";
    echo "<strong>All API files found!</strong>";
    echo "</div>";
} else {
    echo "<div style='padding: 15px; background-color: #f8d7da; border-radius: 5px; margin-bottom: 10px;'>";
    echo "<strong>Missing API files or directories:</strong><br>";
    foreach ($missingFiles as $file) {
        echo "- {$file}<br>";
    }
    echo "</div>";
}

// Check API content if it exists
if (file_exists($registerFile)) {
    echo "<div style='margin-bottom: 20px;'>";
    echo "<h3>Register API Content Review</h3>";
    
    $fileContent = file_get_contents($registerFile);
    
    // Check for key components in the file
    $checkPoints = [
        'Request method check' => strpos($fileContent, 'REQUEST_METHOD') !== false,
        'Database connection' => strpos($fileContent, 'getDBConnection') !== false || strpos($fileContent, 'mysqli') !== false,
        'Password hashing' => strpos($fileContent, 'password_hash') !== false || strpos($fileContent, 'hashPassword') !== false,
        'Response headers' => strpos($fileContent, 'header') !== false,
        'JSON response' => strpos($fileContent, 'json_encode') !== false,
        'Error handling' => strpos($fileContent, 'try') !== false || strpos($fileContent, 'catch') !== false
    ];
    
    echo "<div style='background-color: #f5f5f5; padding: 15px; border-radius: 5px;'>";
    foreach ($checkPoints as $point => $found) {
        echo "<div style='" . ($found ? "color: green;" : "color: red;") . "'>";
        echo ($found ? "✅" : "❌") . " {$point}";
        echo "</div>";
    }
    echo "</div>";
    
    echo "</div>";
}

// Recommendations
echo "<h2>Recommendations</h2>";
echo "<div style='padding: 15px; background-color: #f8f9fa; border-radius: 5px;'>";
echo "<ol>";
echo "<li>Check that the register.php API file exists and has proper content</li>";
echo "<li>Ensure that proper CORS headers are set in the API</li>";
echo "<li>Verify that the client is making requests to the correct API endpoint URL</li>";
echo "<li>Check the browser's Network tab for any errors when submitting the registration form</li>";
echo "<li>Make sure the registration form submits data in the format expected by the API</li>";
echo "<li>Ensure the database connection is working and the users table exists</li>";
echo "</ol>";

echo "<h3>Front-end JavaScript To Check</h3>";
echo "<p>Look for the registration form submission code in your JavaScript files, typically something like:</p>";
echo "<pre style='background-color: #f0f0f0; padding: 10px; border-radius: 3px;'>";
echo "// Example fetch request to check
fetch('http://localhost/PROJECT-BITRADER/backend/api/auth/register.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        username: username,
        email: email,
        password: password,
        // other fields...
    })
})
.then(response => response.json())
.then(data => {
    // Success handling
})
.catch(error => {
    // Error handling
});";
echo "</pre>";
echo "</div>";

// View API endpoint URL
$apiUrl = "http://localhost/PROJECT-BITRADER/backend/api/auth/register.php";
echo "<div style='margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;'>";
echo "<h3>API Endpoint URL</h3>";
echo "<p>Your registration API endpoint should be accessible at:</p>";
echo "<code>" . $apiUrl . "</code>";
echo "</div>";
?> 