<?php
/**
 * Server Configuration Test
 * This script checks various server settings that might affect API requests
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Server Configuration Test</h1>";

// Check PHP version
echo "<h2>PHP Version</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Minimum Required: 7.4.0</p>";
echo "<p>Status: " . (version_compare(phpversion(), '7.4.0', '>=') ? 
    "<span style='color:green'>✓ OK</span>" : 
    "<span style='color:red'>✗ Upgrade Required</span>") . "</p>";

// Check loaded modules
echo "<h2>PHP Modules</h2>";
$requiredModules = ['mysqli', 'json', 'mbstring', 'openssl'];
echo "<ul>";
foreach ($requiredModules as $module) {
    echo "<li>$module: " . (extension_loaded($module) ? 
        "<span style='color:green'>✓ Loaded</span>" : 
        "<span style='color:red'>✗ Not Loaded</span>") . "</li>";
}
echo "</ul>";

// Check server software
echo "<h2>Server Software</h2>";
echo "<p>" . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Check CORS headers
echo "<h2>CORS Headers Test</h2>";
echo "<p>These headers should be present in API responses:</p>";
$corsHeaders = [
    'Access-Control-Allow-Origin',
    'Access-Control-Allow-Methods',
    'Access-Control-Allow-Headers'
];

echo "<ul>";
foreach ($corsHeaders as $header) {
    echo "<li>$header: ";
    
    // We can't check directly because this isn't an API endpoint
    echo "<span style='color:blue'>Will be checked by JavaScript test below</span>";
    
    echo "</li>";
}
echo "</ul>";

// Check if mod_rewrite is enabled
echo "<h2>Apache Modules</h2>";
$apacheModules = function_exists('apache_get_modules') ? apache_get_modules() : [];
$requiredApacheModules = ['mod_rewrite', 'mod_headers'];

echo "<ul>";
foreach ($requiredApacheModules as $module) {
    echo "<li>$module: " . (in_array($module, $apacheModules) ? 
        "<span style='color:green'>✓ Enabled</span>" : 
        "<span style='color:orange'>? Unknown (Can't detect or not Apache)</span>") . "</li>";
}
echo "</ul>";

// Directory permissions
echo "<h2>Directory Permissions</h2>";
$dirsToCheck = [
    __DIR__,
    __DIR__ . '/logs',
    __DIR__ . '/api',
    __DIR__ . '/models',
    __DIR__ . '/utils'
];

echo "<ul>";
foreach ($dirsToCheck as $dir) {
    $exists = file_exists($dir);
    $readable = $exists && is_readable($dir);
    $writable = $exists && is_writable($dir);
    
    echo "<li>" . basename($dir) . " directory: ";
    if (!$exists) {
        echo "<span style='color:red'>✗ Not Found</span>";
    } else {
        echo "<span style='color:green'>✓ Found</span>, ";
        echo "Read: " . ($readable ? 
            "<span style='color:green'>✓</span>" : 
            "<span style='color:red'>✗</span>") . ", ";
        echo "Write: " . ($writable ? 
            "<span style='color:green'>✓</span>" : 
            "<span style='color:red'>✗</span>");
    }
    echo "</li>";
}
echo "</ul>";

// Check API URLs
echo "<h2>API Endpoints</h2>";
$apiEndpoints = [
    'direct-login.php' => __DIR__ . '/direct-login.php',
    'direct-register.php' => __DIR__ . '/direct-register.php',
    'api/auth/login.php' => __DIR__ . '/api/auth/login.php',
    'api/auth/register.php' => __DIR__ . '/api/auth/register.php'
];

echo "<ul>";
foreach ($apiEndpoints as $endpoint => $path) {
    echo "<li>$endpoint: " . (file_exists($path) ? 
        "<span style='color:green'>✓ Exists</span>" : 
        "<span style='color:red'>✗ Not Found</span>") . "</li>";
}
echo "</ul>";

// JavaScript test
echo "<h2>CORS Test</h2>";
echo "<p>Testing API access from JavaScript:</p>";
echo "<div id='cors-test-result'>Running test...</div>";

// Output JavaScript to test an API endpoint
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const resultDiv = document.getElementById('cors-test-result');
    
    // Test OPTIONS request for CORS preflight
    fetch('http://localhost/PROJECT-BITRADER/backend/direct-register.php', {
        method: 'OPTIONS',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'test'
        }
    })
    .then(response => {
        if (response.ok) {
            resultDiv.innerHTML = '<span style="color:green">✓ CORS preflight successful!</span>';
            
            // Show the actual CORS headers
            const corsHeaders = [
                'access-control-allow-origin',
                'access-control-allow-methods',
                'access-control-allow-headers'
            ];
            
            let headerTable = '<table border="1" style="margin-top: 10px;"><tr><th>Header</th><th>Value</th></tr>';
            
            corsHeaders.forEach(header => {
                const value = response.headers.get(header);
                headerTable += `<tr><td>${header}</td><td>${value || '<span style="color:red">Not set</span>'}</td></tr>`;
            });
            
            headerTable += '</table>';
            resultDiv.innerHTML += headerTable;
        } else {
            resultDiv.innerHTML = '<span style="color:red">✗ CORS preflight failed! Status: ' + response.status + '</span>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<span style="color:red">✗ Error testing CORS: ' + error.message + '</span>';
    });
});
</script>

<h2>Recommendations</h2>
<ol>
    <li>Make sure Apache's mod_rewrite and mod_headers modules are enabled</li>
    <li>Check that .htaccess files are being read (AllowOverride All in httpd.conf)</li>
    <li>Verify all required PHP extensions are installed</li>
    <li>Ensure your virtual host configuration is correct for handling PHP files</li>
    <li>Check Apache error logs for additional information: C:/xampp/apache/logs/error.log</li>
</ol>

<h2>Next Steps</h2>
<p>After fixing any issues found here, try these test pages:</p>
<ul>
    <li><a href="http://localhost/PROJECT-BITRADER/backend/debug_register.php">Test Registration API</a></li>
    <li><a href="http://localhost/PROJECT-BITRADER/backend/setup_database.php">Check Database Setup</a></li>
    <li><a href="http://localhost/PROJECT-BITRADER/backend/check_permissions.php">Verify File Permissions</a></li>
</ul> 