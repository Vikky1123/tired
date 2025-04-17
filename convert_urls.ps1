# Convert Absolute URLs to Relative Paths
# This script searches for absolute URLs in HTML files and converts them to relative paths

$domain = "bitrader.thetork.com"
$fullDomain = "https://$domain"
$folderToProcess = ".\bitrader.thetork.com"
$backupFolder = ".\url_conversion_backup"
$logFile = ".\url_conversion_log.txt"
$excludePatterns = @(
    "https://www.schema.org",
    "https://schema.org",
    "http://www.w3.org",
    "https://yoast.com",
    "http://creativecommons.org"
)

# Create backup folder if it doesn't exist
if (-not (Test-Path $backupFolder)) {
    New-Item -Path $backupFolder -ItemType Directory
}

# Start logging
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
"URL Conversion started at $timestamp" | Out-File -FilePath $logFile -Force

# Function to check if URL should be excluded
function Should-Exclude {
    param (
        [string]$Url
    )
    
    foreach ($pattern in $excludePatterns) {
        if ($Url -like "$pattern*") {
            return $true
        }
    }
    
    return $false
}

# Function to process a file
function Process-File {
    param (
        [string]$FilePath
    )
    
    # Skip non-HTML/CSS/JS files
    if ($FilePath -notmatch "\.(html|htm|css|js|json|xml)$") {
        return $false
    }
    
    # Read the content
    $content = Get-Content -Path $FilePath -Raw
    
    # Skip if no domain references
    if ($content -notlike "*$fullDomain*") {
        return $false
    }
    
    # Make backup of original file
    $relativePath = $FilePath.Substring($folderToProcess.Length + 1)
    $backupPath = Join-Path -Path $backupFolder -ChildPath $relativePath
    $backupDir = Split-Path -Parent $backupPath
    
    if (-not (Test-Path $backupDir)) {
        New-Item -Path $backupDir -ItemType Directory -Force | Out-Null
    }
    
    Copy-Item -Path $FilePath -Destination $backupPath -Force
    
    $modified = $false
    $originalContent = $content
    
    # Special handling for wp-json files
    if ($FilePath -like "*\wp-json\*") {
        # We'll use a different approach for these files with special escaping
        try {
            # Read the file as raw text instead of using PowerShell's content handling
            $fileContent = [System.IO.File]::ReadAllText($FilePath)
            $originalContent = $fileContent
            
            # These patterns need to use string literals to avoid escaping issues
            $patterns = @(
                @{Old = '<provider_url>https://bitrader.thetork.com</provider_url>'; New = '<provider_url>../</provider_url>'},
                @{Old = '<author_url>https://bitrader.thetork.com/'; New = '<author_url>../'},
                @{Old = 'href="https://bitrader.thetork.com/'; New = 'href="../'},
                @{Old = 'src="https://bitrader.thetork.com/'; New = 'src="../'},
                @{Old = '<thumbnail_url>https://bitrader.thetork.com/'; New = '<thumbnail_url>../'}
            )
            
            foreach ($pattern in $patterns) {
                if ($fileContent.Contains($pattern.Old)) {
                    $fileContent = $fileContent.Replace($pattern.Old, $pattern.New)
                    $modified = $true
                }
            }
            
            # Save only if changed
            if ($fileContent -ne $originalContent) {
                [System.IO.File]::WriteAllText($FilePath, $fileContent)
                Write-Host "Modified: $FilePath" -ForegroundColor Cyan
                return $true
            }
        }
        catch {
            Write-Host "Error processing $FilePath : $_" -ForegroundColor Red
        }
        
        return $modified
    }
    else {
        # Simple string replacement for absolute URLs with double quotes
        $newContent = $content.Replace("href=`"$fullDomain/", "href=`"/")
        if ($newContent -ne $content) { $modified = $true }
        $content = $newContent
        
        $newContent = $content.Replace("src=`"$fullDomain/", "src=`"/")
        if ($newContent -ne $content) { $modified = $true }
        $content = $newContent
        
        # Handle URLs with single quotes
        $newContent = $content.Replace("href='$fullDomain/", "href='/")
        if ($newContent -ne $content) { $modified = $true }
        $content = $newContent
        
        $newContent = $content.Replace("src='$fullDomain/", "src='/")
        if ($newContent -ne $content) { $modified = $true }
        $content = $newContent
        
        # Handle canonical and meta tags
        $newContent = $content.Replace("<link rel=`"canonical`" href=`"$fullDomain/", "<link rel=`"canonical`" href=`"/")
        if ($newContent -ne $content) { $modified = $true }
        $content = $newContent
        
        $newContent = $content.Replace("<meta property=`"og:url`" content=`"$fullDomain/", "<meta property=`"og:url`" content=`"/")
        if ($newContent -ne $content) { $modified = $true }
        $content = $newContent
        
        # Handle escaped URLs in JS/JSON
        $newContent = $content.Replace("\`"$fullDomain/", "\`"/")
        if ($newContent -ne $content) { $modified = $true }
        $content = $newContent
    }
    
    # Save changes if the file was modified
    if ($modified) {
        Set-Content -Path $FilePath -Value $content
        "Modified: $FilePath" | Out-File -FilePath $logFile -Append
        return $true
    }
    
    return $false
}

# Special test for wp-json files
$wpJsonOnly = $true
if ($wpJsonOnly) {
    # Just process wp-json files
    $wpJsonFiles = Get-ChildItem -Path "$folderToProcess\wp-json" -Recurse -File
    $totalFiles = $wpJsonFiles.Count
    $processedFiles = 0
    $modifiedFiles = 0

    Write-Host "Found $totalFiles wp-json files to process" -ForegroundColor Cyan

    foreach ($file in $wpJsonFiles) {
        $result = Process-File -FilePath $file.FullName
        $processedFiles++
        
        if ($result) {
            $modifiedFiles++
            Write-Host "Modified: $($file.FullName)" -ForegroundColor Green
        }
    }

    # Finish logging
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    "WP-JSON Conversion completed at $timestamp - Processed $processedFiles files, Modified $modifiedFiles files" | Out-File -FilePath $logFile -Append
    Write-Host "WP-JSON Conversion completed - Processed $processedFiles files, Modified $modifiedFiles files" -ForegroundColor Green
    Write-Host "A backup of all processed files is available in $backupFolder" -ForegroundColor Yellow
    Write-Host "See $logFile for details" -ForegroundColor Yellow
    exit
}

# Standard processing for all files
$files = Get-ChildItem -Path $folderToProcess -Recurse -File
$totalFiles = $files.Count
$processedFiles = 0
$modifiedFiles = 0

Write-Host "Found $totalFiles files to process" -ForegroundColor Cyan

foreach ($file in $files) {
    $result = Process-File -FilePath $file.FullName
    $processedFiles++
    
    if ($result) {
        $modifiedFiles++
    }
    
    # Show progress
    if ($processedFiles % 50 -eq 0 -or $processedFiles -eq $totalFiles) {
        $percentage = [Math]::Round(($processedFiles / $totalFiles) * 100, 2)
        "Progress: $processedFiles of $totalFiles files ($percentage%) - Modified: $modifiedFiles" | Out-File -FilePath $logFile -Append
        Write-Host "Progress: $processedFiles of $totalFiles files ($percentage%) - Modified: $modifiedFiles" -ForegroundColor Green
    }
}

# Finish logging
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
"URL Conversion completed at $timestamp - Processed $processedFiles files, Modified $modifiedFiles files" | Out-File -FilePath $logFile -Append
Write-Host "URL Conversion completed - Processed $processedFiles files, Modified $modifiedFiles files" -ForegroundColor Green
Write-Host "A backup of all processed files is available in $backupFolder" -ForegroundColor Yellow
Write-Host "See $logFile for details" -ForegroundColor Yellow
