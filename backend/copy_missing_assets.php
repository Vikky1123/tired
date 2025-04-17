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
    <title>Fix Missing Assets</title>
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
        }
        .error {
            color: red;
            background-color: #fff0f0;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid red;
        }
        .warning {
            color: orange;
            background-color: #fffaf0;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid orange;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>Fix Missing Assets</h1>
    <p>This script will check for missing JavaScript files and fix them.</p>

    <?php
    // Define the project root path (adjust as needed)
    $projectRoot = realpath(__DIR__ . '/..');

    // Define theme assets path
    $themePath = $projectRoot . '/bitrader.thetork.com/wp-content/themes/bitrader/assets';
    $themeJsPath = $themePath . '/js';
    
    // Create the directories if they don't exist
    $dirsToCreate = [
        $themePath,
        $themePath . '/js',
        $themePath . '/css',
        $themePath . '/img',
        $themePath . '/img/logo',
        $themePath . '/img/icons'
    ];
    
    echo "<h2>Creating Directory Structure</h2>";
    echo "<ul>";
    foreach ($dirsToCreate as $dir) {
        if (!file_exists($dir)) {
            if (mkdir($dir, 0755, true)) {
                echo "<li style='color: green;'>Created directory: " . htmlspecialchars($dir) . "</li>";
            } else {
                echo "<li style='color: red;'>Failed to create directory: " . htmlspecialchars($dir) . "</li>";
            }
        } else {
            echo "<li>Directory already exists: " . htmlspecialchars($dir) . "</li>";
        }
    }
    echo "</ul>";
    
    // Define missing JS files to create
    $jsFiles = [
        'swiper-bundle.min.js' => "/* Swiper Bundle JS - Added by copy_missing_assets.php */\n/* This is a placeholder for the Swiper library. If you need the full library, download it from https://swiperjs.com/ */",
        
        'bootstrap.min.js' => "/* Bootstrap JS - Added by copy_missing_assets.php */\n/* This is a placeholder for Bootstrap. If you need the full library, download it from https://getbootstrap.com/ */",
        
        'aos.js' => "/* AOS (Animate On Scroll) JS - Added by copy_missing_assets.php */\n/* This is a placeholder for AOS. If you need the full library, download it from https://michalsnik.github.io/aos/ */",
        
        'purecounter.js' => "/* PureCounter JS - Added by copy_missing_assets.php */\n/* This is a placeholder for PureCounter. If you need the full library, look for it online */",
        
        'custom.js' => "/* Custom JS - Added by copy_missing_assets.php */\n
document.addEventListener('DOMContentLoaded', function() {
    console.log('Custom JS loaded successfully');
    
    // Basic functionality for the theme mode toggle
    const themeToggle = document.getElementById('btnSwitch');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            
            // Store preference
            const isDarkMode = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDarkMode ? 'enabled' : 'disabled');
        });
        
        // Check for saved preference
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
        }
    }
});"
    ];
    
    // Create .htaccess file in the theme directory to set correct MIME types
    $htaccessContent = <<<EOT
# Force correct MIME types
AddType application/javascript .js
AddType text/css .css

# Allow cross-origin requests
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
</IfModule>
EOT;

    $htaccessPath = $themePath . '/.htaccess';
    if (file_put_contents($htaccessPath, $htaccessContent)) {
        echo "<div class='success'>Created .htaccess file to fix MIME type issues</div>";
    } else {
        echo "<div class='error'>Failed to create .htaccess file</div>";
    }
    
    // Create missing JS files
    echo "<h2>Creating Missing JavaScript Files</h2>";
    echo "<table>";
    echo "<tr><th>File</th><th>Status</th></tr>";
    
    foreach ($jsFiles as $filename => $content) {
        $fullPath = $themeJsPath . '/' . $filename;
        
        if (!file_exists($fullPath)) {
            if (file_put_contents($fullPath, $content)) {
                echo "<tr><td>" . htmlspecialchars($filename) . "</td><td style='color: green;'>Created successfully</td></tr>";
            } else {
                echo "<tr><td>" . htmlspecialchars($filename) . "</td><td style='color: red;'>Failed to create</td></tr>";
            }
        } else {
            echo "<tr><td>" . htmlspecialchars($filename) . "</td><td>Already exists</td></tr>";
        }
    }
    echo "</table>";
    
    // Create a CSS file for basic styles
    $cssFiles = [
        'bootstrap.min.css' => "/* Bootstrap CSS - Added by copy_missing_assets.php */\n/* This is a placeholder for Bootstrap. If you need the full library, download it from https://getbootstrap.com/ */",
        
        'aos.css' => "/* AOS (Animate On Scroll) CSS - Added by copy_missing_assets.php */\n/* This is a placeholder for AOS. If you need the full library, download it from https://michalsnik.github.io/aos/ */",
        
        'bitrader-core.css' => "/* Bitrader Core CSS - Added by copy_missing_assets.php */\n/* Basic styles to prevent 404 errors */",
        
        'bitrader-custom.css' => "/* Bitrader Custom CSS - Added by copy_missing_assets.php */\n/* Custom styles to prevent 404 errors */",
        
        'bitrader-fonts.css' => "/* Bitrader Fonts CSS - Added by copy_missing_assets.php */\n/* Font definitions to prevent 404 errors */"
    ];
    
    echo "<h2>Creating Missing CSS Files</h2>";
    echo "<table>";
    echo "<tr><th>File</th><th>Status</th></tr>";
    
    foreach ($cssFiles as $filename => $content) {
        $fullPath = $themePath . '/css/' . $filename;
        
        if (!file_exists($fullPath)) {
            if (file_put_contents($fullPath, $content)) {
                echo "<tr><td>" . htmlspecialchars($filename) . "</td><td style='color: green;'>Created successfully</td></tr>";
            } else {
                echo "<tr><td>" . htmlspecialchars($filename) . "</td><td style='color: red;'>Failed to create</td></tr>";
            }
        } else {
            echo "<tr><td>" . htmlspecialchars($filename) . "</td><td>Already exists</td></tr>";
        }
    }
    echo "</table>";
    
    // Create a placeholder logo file
    $logoPath = $themePath . '/img/logo/preloader.png';
    if (!file_exists($logoPath)) {
        if (!file_exists(dirname($logoPath))) {
            mkdir(dirname($logoPath), 0755, true);
        }
        
        // Create a small 1x1 transparent PNG as a placeholder
        $placeholderPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
        if (file_put_contents($logoPath, $placeholderPng)) {
            echo "<div class='success'>Created placeholder logo file</div>";
        } else {
            echo "<div class='error'>Failed to create placeholder logo file</div>";
        }
    } else {
        echo "<div>Logo file already exists</div>";
    }
    
    // Check if the fix-login.js script is working
    $fixLoginPath = $projectRoot . '/bitrader.thetork.com/Signup-Signin/fix-login.js';
    if (file_exists($fixLoginPath)) {
        echo "<div class='success'>fix-login.js exists and should be fixing the login issue</div>";
        
        // Check if it's included in the sign-in page
        $signinPath = $projectRoot . '/bitrader.thetork.com/Signup-Signin/index.html';
        if (file_exists($signinPath)) {
            $signinContent = file_get_contents($signinPath);
            if (strpos($signinContent, 'fix-login.js') !== false) {
                echo "<div class='success'>fix-login.js is correctly included in the sign-in page</div>";
            } else {
                echo "<div class='warning'>fix-login.js exists but is not included in the sign-in page</div>";
            }
        }
    } else {
        echo "<div class='error'>fix-login.js does not exist! This script is needed to fix the login issues.</div>";
    }
    
    // Summary and next steps
    echo "<h2>Summary</h2>";
    echo "<p>This script has created placeholder files for the missing JavaScript and CSS assets referenced in your HTML.</p>";
    echo "<p>It also added an .htaccess file to ensure proper MIME type handling for JavaScript files.</p>";
    
    echo "<h2>Next Steps</h2>";
    echo "<ol>";
    echo "<li>Restart your Apache server to apply the .htaccess changes</li>";
    echo "<li>Clear your browser cache completely</li>";
    echo "<li>Try logging in again with the debug tool at <a href='http://localhost/PROJECT-BITRADER/bitrader.thetork.com/Signup-Signin/login-debug.html'>http://localhost/PROJECT-BITRADER/bitrader.thetork.com/Signup-Signin/login-debug.html</a></li>";
    echo "<li>If you still have issues, check the login logs at <a href='http://localhost/PROJECT-BITRADER/backend/check_login_logs.php'>http://localhost/PROJECT-BITRADER/backend/check_login_logs.php</a></li>";
    echo "</ol>";
    ?>
</body>
</html> 