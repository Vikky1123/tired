function Process-HtmlFile {
    param (
        [string]$filePath
    )
    
    Write-Host "Processing file: $filePath"
    
    # Read the file content
    $content = Get-Content -Path $filePath -Raw
    
    # Remove mirror comments
    $content = $content -replace '<!-- Mirrored from.*?-->\r?\n', ''
    
    # Fix font URLs
    $content = $content -replace 'href="\.\./\.\./\.\./fonts\.googleapis\.com/index\.html"', 'href="https://fonts.googleapis.com"'
    $content = $content -replace 'href="\.\./\.\./\.\./fonts\.gstatic\.com/index\.html"', 'href="https://fonts.gstatic.com"'
    $content = $content -replace 'href="\.\./\.\./\.\./fonts\.googleapis\.com/css2b83f\.css\?family=([^"]+)"', 'href="https://fonts.googleapis.com/css2?family=$1"'
    
    # Save the modified content back to the file
    $content | Set-Content -Path $filePath -NoNewline
    
    Write-Host "Completed processing file: $filePath"
}

# Main script
$sourceDir = "templates.iqonic.design/coinex-dist/dashboard"
$targetDir = "dashboard"

# Create target directory if it doesn't exist
if (-not (Test-Path $targetDir)) {
    New-Item -ItemType Directory -Path $targetDir | Out-Null
}

# Copy all files from source to target
Copy-Item -Path "$sourceDir\*" -Destination $targetDir -Recurse -Force

# Process all HTML files in the target directory
Get-ChildItem -Path $targetDir -Filter "*.html" -Recurse | ForEach-Object {
    Process-HtmlFile -filePath $_.FullName
}

Write-Host "All files processed successfully!" 