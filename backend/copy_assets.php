<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: text/html; charset=utf-8');

echo '<h1>Asset Files Check and Copy</h1>';

// Define paths
$sourceDir = __DIR__ . '/../bitrader.thetork.com';
$targetDir = $_SERVER['DOCUMENT_ROOT'] . '/PROJECT-BITRADER/bitrader.thetork.com';

// Define required asset files to check and copy
$requiredAssets = [
    'assets/js/bootstrap.bundle.min.js',
    'assets/js/swiper-bundle.min.js',
    'assets/js/aos.js',
    'assets/js/custom.js',
    'assets/js/purecounter.js'
];

// Check if source directory exists
if (!file_exists($sourceDir)) {
    echo '<p style="color: red">Source directory not found: ' . htmlspecialchars($sourceDir) . '</p>';
    
    // Try alternative location
    $sourceDir = $_SERVER['DOCUMENT_ROOT'] . '/bitrader.thetork.com';
    echo '<p>Trying alternative source: ' . htmlspecialchars($sourceDir) . '</p>';
    
    if (!file_exists($sourceDir)) {
        echo '<p style="color: red">Alternative source directory not found either.</p>';
        
        // Show form to specify source directory
        echo '<form method="post">';
        echo '<h2>Specify Source Directory</h2>';
        echo '<p><label>Path to bitrader.thetork.com directory: <input type="text" name="source_path" size="60" value="C:/xampp/htdocs/PROJECT-BITRADER/bitrader.thetork.com"></label></p>';
        echo '<p><input type="submit" name="specify_source" value="Use This Source"></p>';
        echo '</form>';
        
        if (isset($_POST['specify_source'])) {
            $sourceDir = $_POST['source_path'];
            echo '<p>Using specified source: ' . htmlspecialchars($sourceDir) . '</p>';
        } else {
            die('<p>Please specify the source directory.</p>');
        }
    }
}

echo '<p>Source directory: ' . htmlspecialchars($sourceDir) . '</p>';
echo '<p>Target directory: ' . htmlspecialchars($targetDir) . '</p>';

// Create target directory if it doesn't exist
if (!file_exists($targetDir)) {
    echo '<p>Creating target directory...</p>';
    if (mkdir($targetDir, 0755, true)) {
        echo '<p style="color: green">✓ Target directory created</p>';
    } else {
        echo '<p style="color: red">✗ Failed to create target directory</p>';
    }
}

// Check if we need to copy files
$copyFiles = false;
foreach ($requiredAssets as $asset) {
    $targetFile = $targetDir . '/' . $asset;
    if (!file_exists($targetFile)) {
        $copyFiles = true;
        break;
    }
}

// Check and copy asset files
echo '<h2>Asset Files Status</h2>';
echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
echo '<tr><th>File</th><th>Source Status</th><th>Target Status</th><th>Action</th></tr>';

foreach ($requiredAssets as $asset) {
    $sourceFile = $sourceDir . '/' . $asset;
    $targetFile = $targetDir . '/' . $asset;
    
    echo '<tr>';
    echo '<td>' . htmlspecialchars($asset) . '</td>';
    
    // Check source file
    if (file_exists($sourceFile)) {
        echo '<td style="color: green">Exists</td>';
    } else {
        echo '<td style="color: red">Missing</td>';
    }
    
    // Check target file
    if (file_exists($targetFile)) {
        echo '<td style="color: green">Exists</td>';
    } else {
        echo '<td style="color: red">Missing</td>';
    }
    
    // Action
    if (!file_exists($targetFile) && file_exists($sourceFile)) {
        echo '<td><form method="post" style="margin: 0;"><input type="hidden" name="copy_file" value="' . htmlspecialchars($asset) . '"><input type="submit" value="Copy File"></form></td>';
    } elseif (!file_exists($sourceFile)) {
        echo '<td>Source file missing</td>';
    } else {
        echo '<td>No action needed</td>';
    }
    
    echo '</tr>';
}

echo '</table>';

// Handle file copy if requested
if (isset($_POST['copy_file'])) {
    $asset = $_POST['copy_file'];
    $sourceFile = $sourceDir . '/' . $asset;
    $targetFile = $targetDir . '/' . $asset;
    
    echo '<h2>Copying File</h2>';
    echo '<p>Source: ' . htmlspecialchars($sourceFile) . '</p>';
    echo '<p>Target: ' . htmlspecialchars($targetFile) . '</p>';
    
    // Create directory if it doesn't exist
    $targetDir = dirname($targetFile);
    if (!file_exists($targetDir)) {
        if (mkdir($targetDir, 0755, true)) {
            echo '<p style="color: green">✓ Created directory: ' . htmlspecialchars($targetDir) . '</p>';
        } else {
            echo '<p style="color: red">✗ Failed to create directory: ' . htmlspecialchars($targetDir) . '</p>';
        }
    }
    
    // Copy the file
    if (copy($sourceFile, $targetFile)) {
        echo '<p style="color: green">✓ File copied successfully</p>';
    } else {
        echo '<p style="color: red">✗ Failed to copy file</p>';
    }
    
    echo '<p><a href="' . $_SERVER['PHP_SELF'] . '">Refresh Page</a></p>';
}

// Option to copy all missing files
if ($copyFiles) {
    echo '<form method="post">';
    echo '<h2>Copy All Missing Files</h2>';
    echo '<p><input type="submit" name="copy_all" value="Copy All Missing Files"></p>';
    echo '</form>';
}

// Copy all missing files if requested
if (isset($_POST['copy_all'])) {
    echo '<h2>Copying All Missing Files</h2>';
    
    foreach ($requiredAssets as $asset) {
        $sourceFile = $sourceDir . '/' . $asset;
        $targetFile = $targetDir . '/' . $asset;
        
        // Skip if source doesn't exist or target already exists
        if (!file_exists($sourceFile)) {
            echo '<p>Skipping ' . htmlspecialchars($asset) . ' - Source file missing</p>';
            continue;
        }
        
        if (file_exists($targetFile)) {
            echo '<p>Skipping ' . htmlspecialchars($asset) . ' - Target file already exists</p>';
            continue;
        }
        
        // Create directory if it doesn't exist
        $targetAssetDir = dirname($targetFile);
        if (!file_exists($targetAssetDir)) {
            if (mkdir($targetAssetDir, 0755, true)) {
                echo '<p style="color: green">✓ Created directory: ' . htmlspecialchars($targetAssetDir) . '</p>';
            } else {
                echo '<p style="color: red">✗ Failed to create directory: ' . htmlspecialchars($targetAssetDir) . '</p>';
                continue;
            }
        }
        
        // Copy the file
        if (copy($sourceFile, $targetFile)) {
            echo '<p style="color: green">✓ Copied ' . htmlspecialchars($asset) . '</p>';
        } else {
            echo '<p style="color: red">✗ Failed to copy ' . htmlspecialchars($asset) . '</p>';
        }
    }
    
    echo '<p><a href="' . $_SERVER['PHP_SELF'] . '">Refresh Page</a></p>';
}

// Link to JavaScript test
echo '<p><a href="js_test.html" style="display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;">Test JavaScript Files</a>';

// Link to login page
echo '<a href="http://localhost/PROJECT-BITRADER/bitrader.thetork.com/Signup-Signin/index.html" style="display: inline-block; padding: 10px 15px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 4px;">Go to Login Page</a></p>';
?> 