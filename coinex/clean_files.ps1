# PowerShell script to clean up HTML files

# Function to process a single HTML file
function Process-HtmlFile {
    param (
        [string]$filePath
    )
    
    Write-Host "Processing $filePath"
    
    # Read the content
    $content = Get-Content -Path $filePath -Raw
    
    # Remove mirror comments
    $content = $content -replace '<!-- Mirrored from.*?-->\r?\n', ''
    
    # Fix font URLs
    $content = $content -replace 'href="\.\.+/fonts\.googleapis\.com/.*?"', 'href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&display=swap"'
    $content = $content -replace 'href="\.\.+/fonts\.gstatic\.com/.*?"', 'href="https://fonts.gstatic.com" crossorigin'
    
    # Save the modified content
    $content | Set-Content -Path $filePath -Force
    Write-Host "Completed processing $filePath"
}

# Main script
$sourceDir = "templates.iqonic.design/coinex-dist/dashboard"
$targetDir = "dashboard"

# Create target directory if it doesn't exist
if (-not (Test-Path $targetDir)) {
    New-Item -ItemType Directory -Path $targetDir
}

# Copy all files from source to target
Copy-Item -Path "$sourceDir/*" -Destination $targetDir -Recurse -Force

# Process all HTML files in the target directory and its subdirectories
Get-ChildItem -Path $targetDir -Filter "*.html" -Recurse | ForEach-Object {
    Process-HtmlFile -filePath $_.FullName
}

Write-Host "All files have been processed successfully!" 