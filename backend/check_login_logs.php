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
    <title>Login Debug Logs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
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
            white-space: pre-wrap;
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
        .log-entry {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .log-entry h3 {
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
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
    </style>
</head>
<body>
    <h1>Login Debug Logs</h1>
    
    <?php
    // Log file path
    $logFile = __DIR__ . '/logs/login_debug.log';
    
    // Check if the log file exists
    if (!file_exists($logFile)) {
        echo "<p class='error'>Log file not found at: " . htmlspecialchars($logFile) . "</p>";
        
        // Check if the directory exists
        $logDir = dirname($logFile);
        if (!file_exists($logDir)) {
            echo "<p class='error'>Log directory does not exist: " . htmlspecialchars($logDir) . "</p>";
            echo "<form action='' method='post'>";
            echo "<input type='hidden' name='create_log_dir' value='1'>";
            echo "<button type='submit'>Create Log Directory</button>";
            echo "</form>";
            
            if (isset($_POST['create_log_dir'])) {
                try {
                    mkdir($logDir, 0755, true);
                    echo "<p class='success'>Log directory created successfully!</p>";
                } catch (Exception $e) {
                    echo "<p class='error'>Failed to create log directory: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
        } else {
            echo "<p class='warning'>Log directory exists but the log file does not.</p>";
            echo "<form action='' method='post'>";
            echo "<input type='hidden' name='create_test_log' value='1'>";
            echo "<button type='submit'>Create Test Log Entry</button>";
            echo "</form>";
            
            if (isset($_POST['create_test_log'])) {
                try {
                    $testLogData = "=== Test log entry at " . date('Y-m-d H:i:s') . " ===\n";
                    $testLogData .= "This is a test log entry created to verify logging functionality.\n\n";
                    file_put_contents($logFile, $testLogData, FILE_APPEND);
                    echo "<p class='success'>Test log entry created successfully!</p>";
                    echo "<meta http-equiv='refresh' content='1'>"; // Refresh the page
                } catch (Exception $e) {
                    echo "<p class='error'>Failed to create test log entry: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
        }
    } else {
        // Log file exists, display its contents
        echo "<p>Log file found: " . htmlspecialchars($logFile) . "</p>";
        echo "<p>File size: " . filesize($logFile) . " bytes</p>";
        echo "<p>Last modified: " . date("Y-m-d H:i:s", filemtime($logFile)) . "</p>";
        
        // Add button to clear log file
        echo "<form action='' method='post' style='display: inline;'>";
        echo "<input type='hidden' name='clear_log' value='1'>";
        echo "<button type='submit'>Clear Log File</button>";
        echo "</form>";
        
        // Add button to refresh the page
        echo "<form action='' method='get' style='display: inline; margin-left: 10px;'>";
        echo "<button type='submit'>Refresh</button>";
        echo "</form>";
        
        // Process clear log request
        if (isset($_POST['clear_log'])) {
            file_put_contents($logFile, '');
            echo "<p class='success'>Log file cleared successfully!</p>";
            echo "<meta http-equiv='refresh' content='1'>"; // Refresh the page
        }
        
        // Read log file
        $logContent = file_get_contents($logFile);
        
        if (empty($logContent)) {
            echo "<p class='warning'>Log file is empty.</p>";
        } else {
            // Split log into separate login attempts
            $loginAttempts = explode("=== Login attempt at ", $logContent);
            
            // Remove the first empty element
            array_shift($loginAttempts);
            
            // Display log entries in reverse chronological order (newest first)
            $loginAttempts = array_reverse($loginAttempts);
            
            echo "<h2>Login Attempts (" . count($loginAttempts) . ")</h2>";
            
            foreach ($loginAttempts as $index => $attempt) {
                // Reconstruct the timestamp
                $attempt = "=== Login attempt at " . $attempt;
                
                // Extract timestamp
                $timestamp = "";
                if (preg_match('/=== Login attempt at (.+?) ===/', $attempt, $matches)) {
                    $timestamp = $matches[1];
                }
                
                echo "<div class='log-entry'>";
                echo "<h3>Attempt #" . (count($loginAttempts) - $index) . " - " . htmlspecialchars($timestamp) . "</h3>";
                
                // Detect success or failure
                $colorClass = "warning";
                if (strpos($attempt, "Success: User authenticated successfully") !== false) {
                    $colorClass = "success";
                    echo "<p class='success'>✅ Login succeeded</p>";
                } elseif (strpos($attempt, "Error:") !== false) {
                    $colorClass = "error";
                    
                    // Extract specific error
                    $errorMsg = "Unknown error";
                    if (preg_match('/Error: (.+?)\n/', $attempt, $matches)) {
                        $errorMsg = $matches[1];
                    }
                    echo "<p class='error'>❌ Login failed: " . htmlspecialchars($errorMsg) . "</p>";
                }
                
                // Extract username if available
                $username = "";
                if (preg_match('/username.: (.+?)[,\n]/', $attempt, $matches)) {
                    $username = $matches[1];
                    echo "<p>Username: <strong>" . htmlspecialchars($username) . "</strong></p>";
                }
                
                // Check which password verification method was used
                if (strpos($attempt, "Using User::verifyPassword method:") !== false) {
                    echo "<p>Password verification method: <code>User::verifyPassword()</code></p>";
                } elseif (strpos($attempt, "Using global verifyPassword function:") !== false) {
                    echo "<p>Password verification method: <code>verifyPassword()</code></p>";
                } elseif (strpos($attempt, "Using direct password_verify function:") !== false) {
                    echo "<p>Password verification method: <code>password_verify()</code></p>";
                }
                
                // Format the log content
                echo "<pre class='{$colorClass}'>";
                
                // Highlight certain keywords
                $formattedLog = htmlspecialchars($attempt);
                $formattedLog = preg_replace('/Error:.*/', '<span class="error">$0</span>', $formattedLog);
                $formattedLog = preg_replace('/Success:.*/', '<span class="success">$0</span>', $formattedLog);
                $formattedLog = preg_replace('/Exception:.*/', '<span class="error">$0</span>', $formattedLog);
                $formattedLog = preg_replace('/Using.*: (Success|Failed)/', 'Using$0', $formattedLog);
                $formattedLog = str_replace('Using<span class="success">Success', '<span class="success">Using', $formattedLog);
                $formattedLog = str_replace('Using<span class="error">Failed', '<span class="error">Using', $formattedLog);
                
                echo $formattedLog;
                echo "</pre>";
                echo "</div>";
            }
        }
    }
    
    // Show other useful debug information
    echo "<h2>Login API Files</h2>";
    echo "<table>";
    echo "<tr><th>File</th><th>Exists</th><th>Size</th><th>Last Modified</th></tr>";
    
    $filesToCheck = [
        'direct-login.php' => __DIR__ . '/direct-login.php',
        'models/User.php' => __DIR__ . '/models/User.php',
        'utils/api_utils.php' => __DIR__ . '/utils/api_utils.php',
        '.env' => __DIR__ . '/.env',
        'config/database.php' => __DIR__ . '/config/database.php',
    ];
    
    foreach ($filesToCheck as $name => $path) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($name) . "</td>";
        
        if (file_exists($path)) {
            echo "<td class='success'>Yes</td>";
            echo "<td>" . filesize($path) . " bytes</td>";
            echo "<td>" . date("Y-m-d H:i:s", filemtime($path)) . "</td>";
        } else {
            echo "<td class='error'>No</td>";
            echo "<td>-</td>";
            echo "<td>-</td>";
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Show other debug tools
    echo "<h2>Related Debug Tools</h2>";
    echo "<ul>";
    echo "<li><a href='test_login_direct.php'>Test Login API Directly</a></li>";
    echo "<li><a href='debug_connection.php'>Comprehensive Debug</a></li>";
    echo "<li><a href='verify_api_response.php'>API Response Verification</a></li>";
    echo "<li><a href='check_password.php'>Password Verification Tool</a></li>";
    echo "</ul>";
    ?>
</body>
</html> 