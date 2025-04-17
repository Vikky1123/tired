# Script to remove Utilities section from all HTML files
# This script finds all HTML files and removes the utilities section from the sidebar

Write-Host "Starting to remove Utilities section from all HTML files..."

# Get all HTML files
$htmlFiles = Get-ChildItem -Path . -Filter "*.html" -Recurse

foreach ($file in $htmlFiles) {
    Write-Host "Processing file: $($file.FullName)"
    
    # Read the content of the file
    $content = Get-Content -Path $file.FullName -Raw
    
    # Define patterns to match the Utilities section
    # Starting from the nav-item tag containing "Utilities" until the end of its ul section
    $pattern = '(?s)<li class="nav-item">\s*<a class="nav-link" data-bs-toggle="collapse" href="#utilities-error".*?<span class="item-name">Utilities</span>.*?</ul>\s*</li>'
    
    # Check if the pattern is found
    if ($content -match $pattern) {
        Write-Host "  Utilities section found in $($file.Name)"
        
        # Replace the Utilities section with empty string
        $newContent = $content -replace $pattern, ''
        
        # Create a backup of the original file
        $backupPath = "$($file.FullName).util-bak"
        if (-not (Test-Path $backupPath)) {
            Copy-Item -Path $file.FullName -Destination $backupPath
            Write-Host "  Created backup at $backupPath"
        }
        
        # Write the modified content back to the file
        Set-Content -Path $file.FullName -Value $newContent
        Write-Host "  Utilities section removed from $($file.Name)"
    } else {
        Write-Host "  No Utilities section found in $($file.Name)"
    }
}

Write-Host "Completed processing all HTML files" 