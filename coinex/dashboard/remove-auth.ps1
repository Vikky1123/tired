# Script to remove Authentication section from all HTML files
# This script finds all HTML files and removes the authentication section from the sidebar

Write-Host "Starting to remove Authentication section from all HTML files..."

# Get all HTML files
$htmlFiles = Get-ChildItem -Path . -Filter "*.html" -Recurse

foreach ($file in $htmlFiles) {
    Write-Host "Processing file: $($file.FullName)"
    
    # Read the content of the file
    $content = Get-Content -Path $file.FullName -Raw
    
    # Define patterns to match the authentication section
    # Starting from the nav-item tag containing "Authentication" until the end of its ul section
    $pattern = '(?s)<li class="nav-item">\s*<a class="nav-link" data-bs-toggle="collapse" href="#sidebar-auth".*?<span class="item-name">Authentication</span>.*?</ul>\s*</li>'
    
    # Check if the pattern is found
    if ($content -match $pattern) {
        Write-Host "  Authentication section found in $($file.Name)"
        
        # Replace the authentication section with empty string
        $newContent = $content -replace $pattern, ''
        
        # Create a backup of the original file
        $backupPath = "$($file.FullName).auth-bak"
        if (-not (Test-Path $backupPath)) {
            Copy-Item -Path $file.FullName -Destination $backupPath
            Write-Host "  Created backup at $backupPath"
        }
        
        # Write the modified content back to the file
        Set-Content -Path $file.FullName -Value $newContent
        Write-Host "  Authentication section removed from $($file.Name)"
    } else {
        Write-Host "  No Authentication section found in $($file.Name)"
    }
}

Write-Host "Completed processing all HTML files" 