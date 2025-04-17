# Script to remove User List tab from all HTML files
Write-Host "Starting to remove User List tab from all HTML files..."

# Get all HTML files
$htmlFiles = Get-ChildItem -Path . -Filter "*.html" -Recurse

foreach ($file in $htmlFiles) {
    Write-Host "Processing file: $($file.FullName)"
    
    # Read the content of the file
    $content = Get-Content -Path $file.FullName -Raw
    
    # Define pattern to match the User List navigation item - more flexible pattern
    $pattern = '(?s)<li class="nav-item">\s*<a class="nav-link[^"]*"[^>]*>.*?<span class="item-name">User List</span>\s*</a>\s*</li>'
    
    # Check if the pattern is found
    if ($content -match $pattern) {
        Write-Host "  User List tab found in $($file.Name)"
        
        # Replace the User List tab with empty string
        $newContent = $content -replace $pattern, ''
        
        # Create a backup of the original file
        $backupPath = "$($file.FullName).userlist-bak"
        if (-not (Test-Path $backupPath)) {
            Copy-Item -Path $file.FullName -Destination $backupPath
            Write-Host "  Created backup at $backupPath"
        }
        
        # Write the modified content back to the file
        Set-Content -Path $file.FullName -Value $newContent
        Write-Host "  User List tab removed from $($file.Name)"
    } else {
        Write-Host "  No User List tab found in $($file.Name) (or already removed)"
    }
}

# Check if we need to try another pattern for any remaining occurrences
$remainingFiles = Get-ChildItem -Path . -Filter "*.html" -Recurse | Where-Object {
    $content = Get-Content -Path $_.FullName -Raw
    $content -match '<span class="item-name">User List</span>'
}

if ($remainingFiles.Count -gt 0) {
    Write-Host "Found remaining occurrences of User List in some files. Trying alternative pattern..."
    
    foreach ($file in $remainingFiles) {
        Write-Host "Processing with alternative pattern: $($file.FullName)"
        
        # Read the content of the file
        $content = Get-Content -Path $file.FullName -Raw
        
        # Alternative pattern that's more general
        $altPattern = '(?s)<li class="nav-item">[^<]*<a[^>]*>[^<]*<i[^>]*>.*?</i>[^<]*<i[^>]*>.*?</i>[^<]*<span class="item-name">User List</span>[^<]*</a>[^<]*</li>'
        
        # Check if the alternative pattern is found
        if ($content -match $altPattern) {
            Write-Host "  User List tab found with alternative pattern in $($file.Name)"
            
            # Replace the User List tab with empty string
            $newContent = $content -replace $altPattern, ''
            
            # Create a backup of the original file if not already done
            $backupPath = "$($file.FullName).userlist-bak2"
            if (-not (Test-Path $backupPath)) {
                Copy-Item -Path $file.FullName -Destination $backupPath
                Write-Host "  Created backup at $backupPath"
            }
            
            # Write the modified content back to the file
            Set-Content -Path $file.FullName -Value $newContent
            Write-Host "  User List tab removed with alternative pattern from $($file.Name)"
        }
    }
}

# Remove the user-list.html file if it exists
$userListFile = Join-Path -Path $PSScriptRoot -ChildPath "app\user-list.html"
if (Test-Path $userListFile) {
    # Create a backup of the file before deleting
    $backupFile = "$userListFile.bak"
    if (-not (Test-Path $backupFile)) {
        Copy-Item -Path $userListFile -Destination $backupFile
        Write-Host "Created backup of user-list.html at $backupFile"
    }
    
    # Delete the file
    Remove-Item -Path $userListFile -Force
    Write-Host "Deleted user-list.html file"
} else {
    Write-Host "user-list.html file not found (may have been already removed)"
}

Write-Host "Completed removing User List tab and file" 