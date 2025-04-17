<?php
/**
 * Debug Injection Script
 * 
 * This script injects the debug JavaScript into the login page
 * when accessed through a special parameter (?debug=true)
 * 
 * Place this at the root of the application to modify pages on the fly
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuration
$enabled = isset($_GET['debug']) && $_GET['debug'] === 'true';
$debug_script_path = __DIR__ . '/js/debug-login.js';
$target_path = isset($_GET['target']) ? $_GET['target'] : '../Signup-Signin/index.html';
$full_path = realpath(__DIR__ . '/' . $target_path);

// Security check - only allow access to files within the project directory
$base_dir = realpath(__DIR__ . '/..');
if (!$full_path || strpos($full_path, $base_dir) !== 0) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Invalid target path']);
    exit;
}

// Function to inject script into HTML
function injectDebugScript($html, $script_path) {
    // Check if the debug script exists
    if (!file_exists($script_path)) {
        echo "<!-- Error: Debug script not found at $script_path -->";
        return $html;
    }
    
    // Add the script right before the closing body tag
    $injection = '<script src="http://localhost/PROJECT-BITRADER/backend/js/debug-login.js"></script>';
    
    // Add a small, discreet indicator for admins that debug mode is available
    $indicator = '
    <div id="debug-indicator" style="position: fixed; bottom: 5px; right: 5px; background: rgba(0,0,0,0.5); color: #aaa; 
    font-size: 10px; padding: 3px 6px; border-radius: 3px; z-index: 9998; font-family: monospace; cursor: pointer;">
        Debug: Press Ctrl+Shift+D
    </div>
    <script>
        // Make the indicator dismissible
        document.addEventListener("DOMContentLoaded", function() {
            const indicator = document.getElementById("debug-indicator");
            if (indicator) {
                // Hide after 10 seconds
                setTimeout(function() {
                    indicator.style.opacity = "0.2";
                }, 5000);
                
                // Show on hover
                indicator.addEventListener("mouseenter", function() {
                    indicator.style.opacity = "1";
                });
                
                // Hide on click
                indicator.addEventListener("click", function() {
                    indicator.style.display = "none";
                });
            }
        });
    </script>
    ';
    
    $html = str_replace('</body>', "$indicator\n$injection\n</body>", $html);
    
    // Add indicator that this is a debug version
    $html = str_replace('<body', '<body data-debug="true"', $html);
    
    return $html;
}

// Check if debug mode is enabled
if ($enabled) {
    // Read the target HTML file
    if (file_exists($full_path)) {
        $html = file_get_contents($full_path);
        
        // Inject the debug script
        $html = injectDebugScript($html, $debug_script_path);
        
        // Output the modified HTML
        header('Content-Type: text/html');
        echo $html;
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Target file not found']);
    }
} else {
    // Show usage instructions
    header('Content-Type: text/html');
    echo '<!DOCTYPE html>
<html>
<head>
    <title>BiTrader Debug Tool</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #333; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .info { background: #e8f4f8; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .button { display: inline-block; background: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-top: 10px; }
        .note { font-size: 0.9em; color: #666; margin-top: 20px; padding: 10px; border-left: 3px solid #ccc; }
    </style>
</head>
<body>
    <h1>BiTrader Debug Tool</h1>
    <div class="info">
        <p>This tool injects debug JavaScript into your login page to help diagnose issues.</p>
        <p>The debug panel is hidden by default and can be activated with <strong>Ctrl+Shift+D</strong> keyboard shortcut.</p>
    </div>
    
    <h2>Available Debug Options:</h2>
    <ul>
        <li><a href="?debug=true&target=../Signup-Signin/index.html" class="button">Debug Login Page</a></li>
    </ul>
    
    <h2>How it works:</h2>
    <p>This tool injects a hidden debug console that shows:</p>
    <ul>
        <li>Network requests and responses</li>
        <li>API connectivity issues</li>
        <li>JSON parsing problems</li>
        <li>localStorage access</li>
        <li>Detailed error messages</li>
    </ul>
    
    <h2>Usage:</h2>
    <pre>http://localhost/PROJECT-BITRADER/backend/debug-injection.php?debug=true&target=../Signup-Signin/index.html</pre>
    
    <div class="note">
        <p><strong>Note:</strong> The debug panel is invisible to regular users. Only you will see a small indicator in the bottom right corner.</p>
        <p>Press Ctrl+Shift+D to toggle the debug panel, or click on the indicator to hide it.</p>
    </div>
</body>
</html>';
} 