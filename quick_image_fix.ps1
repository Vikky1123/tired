# Quick fix for image paths in subdirectories

Write-Host "Starting quick image path fix for subdirectory HTML files..."

# Function to fix paths in a single HTML file
function Fix-ImagePaths {
    param (
        [string]$FilePath
    )
    
    # Skip main index.html since it already works
    if ($FilePath -like "*bitrader.thetork.com/index.html") {
        Write-Host "Skipping main index.html as it's already working"
        return $false
    }
    
    # Skip if file doesn't exist
    if (-not (Test-Path $FilePath)) {
        Write-Host "File doesn't exist: $FilePath"
        return $false
    }
    
    try {
        Write-Host "Processing $FilePath"
        
        # Read content
        $content = Get-Content -Path $FilePath -Raw -Encoding UTF8
        
        if ($null -eq $content) {
            Write-Host "Empty file: $FilePath"
            return $false
        }
        
        $originalContent = $content
        
        # Calculate directory depth to determine correct path prefix
        $relativePath = $FilePath.Replace("$PWD\bitrader.thetork.com\", "")
        $depth = ($relativePath.Split("\")).Count - 1
        
        # Determine prefix based on depth
        $prefix = "../" * $depth
        
        Write-Host "  File depth: $depth - Using prefix: $prefix"
        
        # Fix src attributes using relative paths - The main issue!
        if ($depth -gt 0) {
            # Fix image src attributes 
            $content = $content -replace 'src="./wp-content/', "src=`"${prefix}wp-content/"
            
            # Fix image srcset attributes
            # First part (before the first comma)
            $content = $content -replace 'srcset="wp-content/', "srcset=`"${prefix}wp-content/"
            
            # Second part (after the comma, starting with ./)
            $content = $content -replace 'srcset="[^"]+, ./wp-content/', "srcset=`"${prefix}wp-content/uploads/2023/10/team_\$1-2.png 450w, ${prefix}wp-content/"
            
            # Fix other URLs
            $content = $content -replace '"./wp-includes/', "`"${prefix}wp-includes/"
            $content = $content -replace '"./wp-content/', "`"${prefix}wp-content/"
        }
        
        # Only save if content changed
        if ($content -ne $originalContent) {
            Set-Content -Path $FilePath -Value $content -Encoding UTF8
            Write-Host "  Updated file: $FilePath" -ForegroundColor Green
            return $true
        } else {
            Write-Host "  No changes needed: $FilePath" -ForegroundColor Yellow
            return $false
        }
    }
    catch {
        Write-Host "Error processing $FilePath : $_" -ForegroundColor Red
        return $false
    }
}

# Process all HTML files in subdirectories
$htmlFiles = Get-ChildItem -Path "bitrader.thetork.com" -Include "*.html" -Recurse | 
             Where-Object { $_.DirectoryName -notlike "*bitrader.thetork.com\wp-*" }

$totalFiles = $htmlFiles.Count
$fixedFiles = 0

Write-Host "Found $totalFiles HTML files to process"

foreach ($file in $htmlFiles) {
    $isFixed = Fix-ImagePaths -FilePath $file.FullName
    if ($isFixed) {
        $fixedFiles++
    }
}

Write-Host "Completed! Fixed $fixedFiles out of $totalFiles files." -ForegroundColor Green 