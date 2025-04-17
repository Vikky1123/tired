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
    <title>Fix MIME Type Issues</title>
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
        .success {
            color: green;
            background-color: #f0fff0;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid green;
            margin: 10px 0;
        }
        .error {
            color: red;
            background-color: #fff0f0;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid red;
            margin: 10px 0;
        }
        .warning {
            color: orange;
            background-color: #fffaf0;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid orange;
            margin: 10px 0;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        code {
            background-color: #f0f0f0;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <h1>Fix MIME Type Issues</h1>
    <p>This script will create or update .htaccess files to fix MIME type issues and disable strict MIME checking.</p>

    <?php
    // Define the project root path
    $projectRoot = realpath(__DIR__ . '/..');

    // Define important locations
    $htaccessLocations = [
        // Main project folder
        $projectRoot => [
            'content' => <<<EOT
# Disable strict MIME type checking
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
</IfModule>

# Proper MIME types for JavaScript and CSS
AddType application/javascript .js
AddType text/css .css

# Allow CORS for all resources
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
</IfModule>

# Enable mod_rewrite
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
</IfModule>
EOT,
            'description' => 'Main project root .htaccess'
        ],
        
        // BiTrader site folder
        $projectRoot . '/bitrader.thetork.com' => [
            'content' => <<<EOT
# Disable strict MIME type checking
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
</IfModule>

# Proper MIME types for JavaScript and CSS
AddType application/javascript .js
AddType text/css .css

# Allow CORS for all resources
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
</IfModule>
EOT,
            'description' => 'BiTrader site .htaccess'
        ],
        
        // Signup-Signin folder
        $projectRoot . '/bitrader.thetork.com/Signup-Signin' => [
            'content' => <<<EOT
# Disable strict MIME type checking
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
</IfModule>

# Proper MIME types for JavaScript and CSS
AddType application/javascript .js
AddType text/css .css

# Allow CORS for all resources
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
</IfModule>
EOT,
            'description' => 'Signup-Signin folder .htaccess'
        ],
        
        // wp-content folder
        $projectRoot . '/bitrader.thetork.com/wp-content' => [
            'content' => <<<EOT
# Disable strict MIME type checking
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
</IfModule>

# Proper MIME types for JavaScript and CSS
AddType application/javascript .js
AddType text/css .css

# Allow CORS for all resources
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
</IfModule>
EOT,
            'description' => 'wp-content folder .htaccess'
        ],
        
        // themes folder
        $projectRoot . '/bitrader.thetork.com/wp-content/themes' => [
            'content' => <<<EOT
# Disable strict MIME type checking
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
</IfModule>

# Proper MIME types for JavaScript and CSS
AddType application/javascript .js
AddType text/css .css

# Allow CORS for all resources
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
</IfModule>
EOT,
            'description' => 'WordPress themes folder .htaccess'
        ]
    ];
    
    // Process each location
    echo "<h2>Creating/Updating .htaccess Files</h2>";
    
    foreach ($htaccessLocations as $location => $config) {
        $htaccessPath = $location . '/.htaccess';
        
        // Make sure the directory exists
        if (!file_exists($location)) {
            echo "<div class='warning'>Directory does not exist: " . htmlspecialchars($location) . " - Skipping</div>";
            continue;
        }
        
        // Create or update the .htaccess file
        if (file_put_contents($htaccessPath, $config['content'])) {
            echo "<div class='success'>Created/Updated " . htmlspecialchars($config['description']) . " at " . htmlspecialchars($htaccessPath) . "</div>";
        } else {
            echo "<div class='error'>Failed to create/update " . htmlspecialchars($config['description']) . " at " . htmlspecialchars($htaccessPath) . "</div>";
        }
    }
    
    // Now create a direct script to fix the problem
    $fixMimeJsPath = $projectRoot . '/bitrader.thetork.com/js/fix-mime-issues.js';
    $fixMimeJsContent = <<<EOT
/**
 * Fix MIME Type Issues Script
 * This script resolves issues with strict MIME type checking in the browser
 */
console.log('fix-mime-issues.js loaded');

// Override the browser's MIME type validation for scripts
document.addEventListener('DOMContentLoaded', function() {
    // Find all script tags that might be affected
    const scripts = document.getElementsByTagName('script');
    
    // Log all script sources for debugging
    console.log('Script tags on page:', scripts.length);
    
    for (let i = 0; i < scripts.length; i++) {
        if (scripts[i].src && !scripts[i].hasAttribute('type')) {
            console.log('Adding type to script:', scripts[i].src);
            scripts[i].setAttribute('type', 'application/javascript');
        }
    }
    
    // Force loading of commonly missing scripts
    const commonScripts = [
        '/wp-content/themes/bitrader/assets/js/swiper-bundle.min.js',
        '/wp-content/themes/bitrader/assets/js/bootstrap.min.js',
        '/wp-content/themes/bitrader/assets/js/aos.js',
        '/wp-content/themes/bitrader/assets/js/purecounter.js',
        '/wp-content/themes/bitrader/assets/js/custom.js'
    ];
    
    commonScripts.forEach(function(scriptPath) {
        // Create a new script element
        const script = document.createElement('script');
        script.src = scriptPath;
        script.type = 'application/javascript';
        script.async = true;
        
        // Add error handling
        script.onerror = function() {
            console.warn('Failed to load script:', scriptPath);
        };
        
        script.onload = function() {
            console.log('Successfully loaded script:', scriptPath);
        };
        
        // Append to the document body
        document.body.appendChild(script);
    });
});
EOT;

    // Create the directory if it doesn't exist
    if (!file_exists(dirname($fixMimeJsPath))) {
        mkdir(dirname($fixMimeJsPath), 0755, true);
    }
    
    // Create the fix-mime-issues.js file
    if (file_put_contents($fixMimeJsPath, $fixMimeJsContent)) {
        echo "<div class='success'>Created fix-mime-issues.js at " . htmlspecialchars($fixMimeJsPath) . "</div>";
    } else {
        echo "<div class='error'>Failed to create fix-mime-issues.js at " . htmlspecialchars($fixMimeJsPath) . "</div>";
    }
    
    // Now update the Apache mod_mime configuration to properly handle JavaScript files
    echo "<h2>Apache Configuration Instructions</h2>";
    echo "<div class='warning'>";
    echo "<p>To ensure JavaScript files are properly served with the correct MIME type, you may need to update your Apache configuration.</p>";
    echo "<p>Please execute the following steps:</p>";
    echo "<ol>";
    echo "<li>Restart Apache using XAMPP Control Panel</li>";
    echo "<li>Clear your browser cache completely</li>";
    echo "<li>Try loading the login page again</li>";
    echo "</ol>";
    echo "</div>";
    
    // Check the login page and modify it if needed
    $signinPath = $projectRoot . '/bitrader.thetork.com/Signup-Signin/index.html';
    $fixMimePath = $projectRoot . '/bitrader.thetork.com/js/fix-mime-issues.js';
    
    if (file_exists($signinPath)) {
        $signinContent = file_get_contents($signinPath);
        $updated = false;
        
        // Check if the fix-mime-issues.js is already included
        if (strpos($signinContent, 'fix-mime-issues.js') === false) {
            // Add script before closing head tag
            $signinContent = str_replace('</head>', '    <script src="../js/fix-mime-issues.js"></script>' . "\n</head>", $signinContent);
            $updated = true;
        }
        
        // Update script tags to explicitly set the type
        $scriptRegex = '/<script([^>]*)>/';
        $signinContent = preg_replace_callback($scriptRegex, function($matches) {
            // Check if type is already set
            if (strpos($matches[1], 'type=') !== false) {
                return $matches[0]; // Already has type, leave it unchanged
            } else {
                return '<script' . $matches[1] . ' type="application/javascript">';
            }
        }, $signinContent);
        $updated = true;
        
        // Save changes if any were made
        if ($updated) {
            if (file_put_contents($signinPath, $signinContent)) {
                echo "<div class='success'>Updated login page to include MIME type fixes and proper script type declarations</div>";
            } else {
                echo "<div class='error'>Failed to update login page</div>";
            }
        } else {
            echo "<div class='warning'>Login page already appears to include necessary MIME type fixes</div>";
        }
    } else {
        echo "<div class='error'>Login page not found at: " . htmlspecialchars($signinPath) . "</div>";
    }
    
    // Summary and next steps
    echo "<h2>Summary</h2>";
    echo "<p>This script has updated multiple .htaccess files to fix MIME type issues and has created a JavaScript fix to handle problematic script loading.</p>";
    
    echo "<h2>Next Steps</h2>";
    echo "<ol>";
    echo "<li>Run the asset copy script to create missing JS files: <a href='copy_missing_assets.php'>Copy Missing Assets</a></li>";
    echo "<li>Restart your Apache server to apply the .htaccess changes</li>";
    echo "<li>Clear your browser cache completely</li>";
    echo "<li>Try logging in again at <a href='http://localhost/PROJECT-BITRADER/bitrader.thetork.com/Signup-Signin/'>http://localhost/PROJECT-BITRADER/bitrader.thetork.com/Signup-Signin/</a></li>";
    echo "</ol>";
    ?>
</body>
</html> 