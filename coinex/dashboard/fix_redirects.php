<?php
/**
 * Fix Redirects Script
 * 
 * This script creates redirect files for HTML pages to their PHP equivalents
 * and updates links in existing HTML files to point to PHP versions
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fixing Redirects for BitTrader Dashboard</h1>";

// Create redirect for index.html
$indexRedirect = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="0;url=index.php">
    <title>Redirecting...</title>
</head>
<body>
    <p>Redirecting to dynamic dashboard... <a href="index.php">Click here if you are not redirected</a></p>
    <script>window.location.href = "index.php";</script>
</body>
</html>
HTML;

// Create redirect for user-profile.html
$profileRedirect = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="0;url=user-profile.php">
    <title>Redirecting...</title>
</head>
<body>
    <p>Redirecting to dynamic user profile... <a href="user-profile.php">Click here if you are not redirected</a></p>
    <script>window.location.href = "user-profile.php";</script>
</body>
</html>
HTML;

// Files to modify
$filesToUpdate = [
    __DIR__ . '/index.html.original' => $indexRedirect,
    __DIR__ . '/app/user-profile.html.original' => $profileRedirect,
];

// Make backup of original files and create redirects
foreach ($filesToUpdate as $file => $content) {
    $originalFile = str_replace('.original', '', $file);
    
    // Skip if original doesn't exist
    if (!file_exists($originalFile)) {
        echo "<p>Warning: {$originalFile} does not exist, skipping.</p>";
        continue;
    }
    
    // Backup the original if not already backed up
    if (!file_exists($file)) {
        if (copy($originalFile, $file)) {
            echo "<p>Created backup of {$originalFile} to {$file}</p>";
        } else {
            echo "<p>Error: Could not create backup of {$originalFile}</p>";
            continue;
        }
    }
    
    // Create the redirect
    if (file_put_contents($originalFile, $content)) {
        echo "<p>Successfully created redirect for {$originalFile}</p>";
    } else {
        echo "<p>Error: Could not create redirect for {$originalFile}</p>";
    }
}

// Find files containing links to user-profile.html and update them
$dashboardFiles = glob(__DIR__ . '/**/*.html');
$updateCount = 0;

foreach ($dashboardFiles as $file) {
    // Skip redirects we just created
    if (in_array($file, array_keys($filesToUpdate))) {
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Update links to user-profile.html
    $newContent = str_replace(
        ['href="app/user-profile.html"', 'href="../app/user-profile.html"'], 
        ['href="app/user-profile.php"', 'href="../app/user-profile.php"'], 
        $content
    );
    
    // Save the file if changes were made
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        $updateCount++;
        echo "<p>Updated links in {$file}</p>";
    }
}

echo "<p>Updated {$updateCount} files with links to user-profile.php</p>";

// Create a .htaccess file to automatically redirect HTML to PHP
$htaccessContent = <<<HTACCESS
# Automatically redirect HTML to PHP if PHP exists
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME}.php -f
    RewriteRule ^(.*)\.html$ \$1.php [L,R=301]
</IfModule>
HTACCESS;

if (file_put_contents(__DIR__ . '/.htaccess', $htaccessContent)) {
    echo "<p>Created .htaccess file for automatic HTML to PHP redirects</p>";
} else {
    echo "<p>Error: Could not create .htaccess file</p>";
}

echo "<h2>Done!</h2>";
echo "<p>Now you should be able to use the original HTML URLs and they will redirect to the dynamic PHP versions.</p>";
echo "<p><a href='index.html'>Try the dashboard</a> or <a href='app/user-profile.html'>check your profile</a>.</p>";
?> 