# Script to verify that no references to the original domain remain in the site

# Define the domain to check for
$originalDomain = "bitrader.thetork.com"

# Create a log file
$logFile = "site_verification_log.txt"
"Site Verification Log - $(Get-Date)" | Out-File -FilePath $logFile -Encoding UTF8

Write-Host "Verifying site for remaining domain references..."

# Function to check file for domain references
function Check-File {
    param (
        [string]$FilePath
    )
    
    # Skip if file doesn't exist or is empty
    if (-not (Test-Path $FilePath) -or (Get-Item $FilePath).Length -eq 0) {
        return $false
    }
    
    try {
        # Read file content
        $content = Get-Content -Path $FilePath -Raw -Encoding UTF8
        
        if ($null -eq $content) {
            return $false
        }
        
        # Check for domain references
        if ($content -match $originalDomain) {
            return $true
        }
        
        # Check for https://domain
        if ($content -match "https?://$originalDomain") {
            return $true
        }
        
        # Check for //domain (protocol-relative URLs)
        if ($content -match "\/\/$originalDomain") {
            return $true
        }
        
        return $false
    }
    catch {
        Write-Host "Error checking file $FilePath : $_"
        return $false
    }
}

# Get all HTML, CSS, and JS files
$htmlFiles = Get-ChildItem -Path "." -Include "*.html", "*.htm" -Recurse
$cssFiles = Get-ChildItem -Path "." -Include "*.css" -Recurse
$jsFiles = Get-ChildItem -Path "." -Include "*.js" -Recurse

$problemFiles = @()

# Check HTML files
foreach ($file in $htmlFiles) {
    if (Check-File -FilePath $file.FullName) {
        $problemFiles += $file.FullName
        "Problem found in HTML file: $($file.FullName)" | Out-File -FilePath $logFile -Append
    }
}

# Check CSS files
foreach ($file in $cssFiles) {
    if (Check-File -FilePath $file.FullName) {
        $problemFiles += $file.FullName
        "Problem found in CSS file: $($file.FullName)" | Out-File -FilePath $logFile -Append
    }
}

# Check JS files
foreach ($file in $jsFiles) {
    if (Check-File -FilePath $file.FullName) {
        $problemFiles += $file.FullName
        "Problem found in JS file: $($file.FullName)" | Out-File -FilePath $logFile -Append
    }
}

# Output results
if ($problemFiles.Count -gt 0) {
    Write-Host "Found $($problemFiles.Count) files with remaining domain references."
    Write-Host "Please check the log file for details: $logFile"
    "Found $($problemFiles.Count) files with remaining domain references." | Out-File -FilePath $logFile -Append
    
    Write-Host "Problem files:"
    foreach ($file in $problemFiles) {
        Write-Host "  - $file"
    }
} else {
    Write-Host "Success! No domain references found."
    "Success! No domain references found." | Out-File -FilePath $logFile -Append
}

Write-Host "Verification complete! See $logFile for details." 