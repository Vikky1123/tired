<?php
/**
 * Direct Fix Script
 * 
 * This script directly replaces the HTML files with redirects to their PHP versions
 * without making backups - use with caution
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Direct Fix for BitTrader Dashboard</h1>";

// Function to create a redirect file
function createRedirect($filename, $targetFile) {
    $redirectContent = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="0;url={$targetFile}">
    <title>Redirecting...</title>
</head>
<body>
    <p>Redirecting to dynamic version... <a href="{$targetFile}">Click here if you are not redirected</a></p>
    <script>window.location.href = "{$targetFile}";</script>
</body>
</html>
HTML;

    if (file_put_contents($filename, $redirectContent)) {
        echo "<p>Successfully created redirect for {$filename} to {$targetFile}</p>";
        return true;
    } else {
        echo "<p>Error: Could not create redirect for {$filename}</p>";
        return false;
    }
}

// Create redirect for main dashboard
createRedirect(__DIR__ . '/index.html', 'index.php');

// Create redirect for user profile
createRedirect(__DIR__ . '/app/user-profile.html', 'user-profile.php');

echo "<h2>Done!</h2>";
echo "<p>Now you can access the original HTML URLs and they will redirect to the dynamic PHP versions.</p>";
echo "<p><a href='index.html'>Try the dashboard</a> or <a href='app/user-profile.html'>check your profile</a>.</p>";
?> 