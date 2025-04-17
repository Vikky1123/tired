# Script to find and replace absolute URLs with relative URLs in the downloaded website
# This script fixes URLs in HTML, CSS, and JavaScript files

# Define the domain to replace
$originalDomain = "bitrader.thetork.com"

# Function to process file
function Process-File {
    param (
        [string]$FilePath
    )
    
    Write-Host "Processing file: $FilePath"

    # Skip if file doesn't exist or is empty
    if (-not (Test-Path $FilePath) -or (Get-Item $FilePath).Length -eq 0) {
        Write-Host "Skipping empty or non-existent file: $FilePath"
        return
    }
    
    try {
        # Read file content
        $content = Get-Content -Path $FilePath -Raw -Encoding UTF8
        
        if ($null -eq $content) {
            Write-Host "Skipping empty file: $FilePath"
            return
        }
        
        $originalContent = $content
        
        # Replace full URLs with relative paths
        # Pattern 1: https://bitrader.thetork.com/path/to/resource
        $content = $content -replace "https?://$originalDomain/", "./"
        
        # Pattern 2: //bitrader.thetork.com/path/to/resource (protocol-relative URLs)
        $content = $content -replace "\/\/$originalDomain/", "./"
        
        # Fix links in the main HTTrack index.html file
        $content = $content -replace "<A HREF=""$originalDomain/", "<A HREF=""bitrader.thetork.com/"
        
        # Fix canonical and meta links
        $content = $content -replace "<link rel=""canonical"" href=""https?://$originalDomain/([^""]+)""", "<link rel=""canonical"" href=""./`$1"""
        $content = $content -replace "content=""https?://$originalDomain/", "content=""./"
        
        # Fix meta property og:url and similar tags
        $content = $content -replace "property=""og:url"" content=""https?://$originalDomain/([^""]+)""", "property=""og:url"" content=""./`$1"""
        $content = $content -replace "property=""og:image"" content=""https?://$originalDomain/([^""]+)""", "property=""og:image"" content=""./`$1"""
        
        # Fix schema.org URLs in JSON-LD scripts
        if ($FilePath -match "\.html$") {
            # Handle JSON data in script tags carefully
            $content = $content -replace """url"":""https?://$originalDomain/([^""]+)""", """url"":""./`$1"""
            $content = $content -replace """@id"":""https?://$originalDomain/([^""]+)""", """@id"":""./`$1"""
            $content = $content -replace """target"":\[""https?://$originalDomain/([^""]+)""\]", """target"":[""./`$1""]"
        }
        
        # Fix internal links that are not HTML (e.g., /about/ to about/index.html)
        $content = $content -replace "href=""/$originalDomain/([^/""]+)/""", "href=""./`$1/index.html"""
        $content = $content -replace "href=""/([^/""]+)/""", "href=""./`$1/index.html"""
        
        # Fix any remaining absolute URLs for WordPress site structure
        $content = $content -replace "href=""/$originalDomain/wp-content/", "href=""./wp-content/"
        $content = $content -replace "src=""/$originalDomain/wp-content/", "src=""./wp-content/"
        $content = $content -replace "url\(/$originalDomain/wp-content/", "url(./wp-content/"
        
        # Fix URLs in CSS files
        if ($FilePath -match "\.css$") {
            $content = $content -replace "url\(https?://$originalDomain/", "url(./"
            $content = $content -replace "url\(\/\/", "url(./"
            $content = $content -replace "url\(/", "url(./"
        }
        
        # Fix WordPress internal paths
        $content = $content -replace """wp-json/", """./wp-json/"
        $content = $content -replace """wp-content/", """./wp-content/"
        $content = $content -replace """wp-includes/", """./wp-includes/"
        
        # Only write if content changed
        if ($content -ne $originalContent) {
            Set-Content -Path $FilePath -Value $content -Encoding UTF8
            Write-Host "Updated: $FilePath"
        }
        else {
            Write-Host "No changes needed in: $FilePath"
        }
    }
    catch {
        Write-Host "Error processing file $FilePath : $_"
    }
}

# Create a log file
$logFile = "url_fix_log.txt"
"URL Fixing Log - $(Get-Date)" | Out-File -FilePath $logFile -Encoding UTF8

# Get all HTML, CSS, and JS files
Write-Host "Finding all HTML, CSS, and JS files..."
$htmlFiles = Get-ChildItem -Path "." -Include "*.html", "*.htm" -Recurse
$cssFiles = Get-ChildItem -Path "." -Include "*.css" -Recurse
$jsFiles = Get-ChildItem -Path "." -Include "*.js" -Recurse

# Log file counts
"Found $($htmlFiles.Count) HTML files" | Out-File -FilePath $logFile -Append
"Found $($cssFiles.Count) CSS files" | Out-File -FilePath $logFile -Append
"Found $($jsFiles.Count) JS files" | Out-File -FilePath $logFile -Append

# Process HTML files
Write-Host "Processing HTML files..."
foreach ($file in $htmlFiles) {
    Process-File -FilePath $file.FullName
    "$($file.FullName) processed" | Out-File -FilePath $logFile -Append
}

# Process CSS files
Write-Host "Processing CSS files..."
foreach ($file in $cssFiles) {
    Process-File -FilePath $file.FullName
    "$($file.FullName) processed" | Out-File -FilePath $logFile -Append
}

# Process JS files
Write-Host "Processing JS files..."
foreach ($file in $jsFiles) {
    Process-File -FilePath $file.FullName
    "$($file.FullName) processed" | Out-File -FilePath $logFile -Append
}

Write-Host "URL fixing complete! See $logFile for details." 