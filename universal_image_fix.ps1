param(
    [Parameter(Mandatory=$true)]
    [string]$WebsiteRootFolder,
    
    [Parameter(Mandatory=$false)]
    [string]$DomainName = "",
    
    [Parameter(Mandatory=$false)]
    [string]$ExtensionsToProcess = "html,htm,php",
    
    [Parameter(Mandatory=$false)]
    [switch]$FixSrcset = $true,
    
    [Parameter(Mandatory=$false)]
    [switch]$FixJsonLd = $true,
    
    [Parameter(Mandatory=$false)]
    [switch]$LogDetails = $false
)

# Initialize counters
$totalFiles = 0
$fixedFiles = 0
$errors = 0
$logFile = Join-Path $WebsiteRootFolder "image_fix_log.txt"

# Function to calculate relative path from current file to root
function Get-RelativePathToRoot {
    param(
        [string]$CurrentFilePath,
        [string]$RootFolder
    )
    
    $currentDir = Split-Path -Parent $CurrentFilePath
    $relativePath = ""
    
    while ($currentDir -ne $RootFolder -and $currentDir -ne "") {
        $relativePath = "..\" + $relativePath
        $currentDir = Split-Path -Parent $currentDir
    }
    
    return $relativePath
}

# Function to fix image paths in HTML content
function Fix-ImagePaths {
    param(
        [string]$Content,
        [string]$RelativePathToRoot,
        [string]$DomainName
    )
    
    $modified = $false
    
    # Fix basic image paths
    $Content = $Content -replace 'src="(?!http|https|data:)([^"]+)"', {
        param($match)
        $modified = $true
        $path = $match.Groups[1].Value
        if ($path.StartsWith("/")) {
            "src=`"$($RelativePathToRoot)$($path.Substring(1))`""
        } else {
            "src=`"$($RelativePathToRoot)$path`""
        }
    }
    
    # Fix srcset attributes if enabled
    if ($FixSrcset) {
        $Content = $Content -replace 'srcset="(?!http|https|data:)([^"]+)"', {
            param($match)
            $modified = $true
            $srcset = $match.Groups[1].Value
            $fixedSrcset = ($srcset -split ',') | ForEach-Object {
                $parts = $_ -split ' '
                if ($parts[0].StartsWith("/")) {
                    "$($RelativePathToRoot)$($parts[0].Substring(1)) $($parts[1])"
                } else {
                    "$($RelativePathToRoot)$($parts[0]) $($parts[1])"
                }
            } -join ', '
            "srcset=`"$fixedSrcset`""
        }
    }
    
    # Fix JSON-LD image references if enabled
    if ($FixJsonLd) {
        $Content = $Content -replace '"image":\s*"(?!http|https|data:)([^"]+)"', {
            param($match)
            $modified = $true
            $path = $match.Groups[1].Value
            if ($path.StartsWith("/")) {
                "`"image`": `"$($RelativePathToRoot)$($path.Substring(1))`""
            } else {
                "`"image`": `"$($RelativePathToRoot)$path`""
            }
        }
    }
    
    # Fix meta tags
    $Content = $Content -replace 'content="(?!http|https|data:)([^"]+)"', {
        param($match)
        $modified = $true
        $path = $match.Groups[1].Value
        if ($path.StartsWith("/")) {
            "content=`"$($RelativePathToRoot)$($path.Substring(1))`""
        } else {
            "content=`"$($RelativePathToRoot)$path`""
        }
    }
    
    return @{
        Content = $Content
        Modified = $modified
    }
}

# Process all HTML files recursively
try {
    $extensions = $ExtensionsToProcess -split ','
    $pattern = "*.{" + ($extensions -join ',') + "}"
    
    Get-ChildItem -Path $WebsiteRootFolder -Filter $pattern -Recurse | ForEach-Object {
        $totalFiles++
        $filePath = $_.FullName
        
        try {
            $relativePathToRoot = Get-RelativePathToRoot -CurrentFilePath $filePath -RootFolder $WebsiteRootFolder
            $content = Get-Content -Path $filePath -Raw
            
            $result = Fix-ImagePaths -Content $content -RelativePathToRoot $relativePathToRoot -DomainName $DomainName
            
            if ($result.Modified) {
                $result.Content | Set-Content -Path $filePath -NoNewline
                $fixedFiles++
                
                if ($LogDetails) {
                    "Fixed image paths in: $filePath" | Add-Content -Path $logFile
                }
            }
        }
        catch {
            $errors++
            if ($LogDetails) {
                "Error processing $filePath : $_" | Add-Content -Path $logFile
            }
        }
    }
    
    # Write summary
    $summary = @"
Image Path Fix Summary:
Total files processed: $totalFiles
Files fixed: $fixedFiles
Errors encountered: $errors
"@
    
    Write-Host $summary
    if ($LogDetails) {
        $summary | Add-Content -Path $logFile
    }
}
catch {
    Write-Host "Error: $_"
    if ($LogDetails) {
        "Fatal error: $_" | Add-Content -Path $logFile
    }
} 