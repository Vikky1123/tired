# Script to fix image paths in the downloaded website

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
        return $false
    }
    
    try {
        # Read file content
        $content = Get-Content -Path $FilePath -Raw -Encoding UTF8
        
        if ($null -eq $content) {
            Write-Host "Skipping empty file: $FilePath"
            return $false
        }
        
        $originalContent = $content
        
        # Step 1: Fix team images paths based on directory depth
        
        # For files in the main directory
        if ($FilePath -match "bitrader\.thetork\.com/index\.html$") {
            $content = $content -replace 'srcset="./wp-content', 'srcset="wp-content'
            $content = $content -replace 'src="./wp-content', 'src="wp-content'
        }
        
        # For files in subdirectories (1 level deep)
        elseif ($FilePath -match "bitrader\.thetork\.com/[^/]+/index\.html$") {
            $content = $content -replace 'srcset="./wp-content', 'srcset="../wp-content'
            $content = $content -replace 'src="./wp-content', 'src="../wp-content'
        }
        
        # For files in sub-subdirectories (2 levels deep)
        elseif ($FilePath -match "bitrader\.thetork\.com/[^/]+/[^/]+/index\.html$") {
            $content = $content -replace 'srcset="./wp-content', 'srcset="../../wp-content'
            $content = $content -replace 'src="./wp-content', 'src="../../wp-content'
        }
        
        # For files in sub-sub-subdirectories (3 levels deep)
        elseif ($FilePath -match "bitrader\.thetork\.com/[^/]+/[^/]+/[^/]+/index\.html$") {
            $content = $content -replace 'srcset="./wp-content', 'srcset="../../../wp-content'
            $content = $content -replace 'src="./wp-content', 'src="../../../wp-content'
        }
        
        # Step 2: Fix specific image URL patterns
        
        # Fix 1: URLs that use absolute paths with domain but point to local resources
        $content = $content -replace 'src="https?://bitrader\.thetork\.com/wp-content', 'src="../wp-content'
        
        # Fix 2: Fix incorrect relative references
        $content = $content -replace 'src="bitrader\.thetork\.com/wp-content', 'src="wp-content'
        
        # Fix 3: Fix team_*-2.png image references with incorrect paths
        $path = [System.IO.Path]::GetDirectoryName($FilePath)
        $depth = ([Regex]::Matches($path, "\\")).Count - ([Regex]::Matches("bitrader.thetork.com", "\\")).Count
        
        $prefix = ""
        if ($depth -eq 0) {
            $prefix = ""
        } elseif ($depth -eq 1) {
            $prefix = "../"
        } elseif ($depth -eq 2) {
            $prefix = "../../"
        } elseif ($depth -eq 3) {
            $prefix = "../../../"
        }
        
        # Fix team images srcset attributes
        $content = $content -replace 'srcset="./wp-content/uploads/2023/10/team_(\d)-2.png', "srcset=`"${prefix}wp-content/uploads/2023/10/team_`$1-2.png"
        $content = $content -replace 'src="../wp-content/uploads/2023/10/team_(\d)-2.png', "src=`"${prefix}wp-content/uploads/2023/10/team_`$1-2.png"
        
        # Fix 4: Fix other image formats (jpg, jpeg, gif)
        $content = $content -replace 'src="./wp-content/uploads/([^"]+)\.(jpg|jpeg|gif|svg|webp)"', "src=`"${prefix}wp-content/uploads/`$1.`$2`""
        
        # Fix 5: Fix for avatar images
        $content = $content -replace 'src="../../secure.gravatar.com', 'src="../secure.gravatar.com'
        
        # Fix 6: Fix preloader and icons
        $content = $content -replace 'src="./wp-content/themes/bitrader/assets/img/logo/preloader.png"', "src=`"${prefix}wp-content/themes/bitrader/assets/img/logo/preloader.png`""
        $content = $content -replace 'src="./wp-content/themes/bitrader/assets/img/icons/moon.svg"', "src=`"${prefix}wp-content/themes/bitrader/assets/img/icons/moon.svg`""
        
        # Fix 7: Fix breadcrumb images
        $content = $content -replace 'src="./wp-content/themes/bitrader/assets/img/bg/breadcrumb_shape.png"', "src=`"${prefix}wp-content/themes/bitrader/assets/img/bg/breadcrumb_shape.png`""
        
        # Fix 8: Fix footer shapes
        $content = $content -replace 'src="./wp-content/uploads/2023/10/footer_shape-2.png"', "src=`"${prefix}wp-content/uploads/2023/10/footer_shape-2.png`""
        
        # Only write if content changed
        if ($content -ne $originalContent) {
            Set-Content -Path $FilePath -Value $content -Encoding UTF8
            Write-Host "Updated: $FilePath"
            return $true
        }
        else {
            Write-Host "No changes needed in: $FilePath"
            return $false
        }
    }
    catch {
        Write-Host "Error processing file $FilePath : $_"
        return $false
    }
}

# Create a log file
$logFile = "image_fix_log.txt"
"Image URL Fixing Log - $(Get-Date)" | Out-File -FilePath $logFile -Encoding UTF8

# Get all HTML files
Write-Host "Finding all HTML files..."
$htmlFiles = Get-ChildItem -Path "." -Include "*.html" -Recurse

# Log file count
"Found $($htmlFiles.Count) HTML files" | Out-File -FilePath $logFile -Append

$fixedFilesCount = 0

# Process HTML files
Write-Host "Processing HTML files..."
foreach ($file in $htmlFiles) {
    $isFixed = Process-File -FilePath $file.FullName
    "$($file.FullName) processed" | Out-File -FilePath $logFile -Append
    if ($isFixed) {
        $fixedFilesCount++
    }
}

Write-Host "Fixed $fixedFilesCount HTML files with image URL issues."
"Fixed $fixedFilesCount HTML files with image URL issues." | Out-File -FilePath $logFile -Append
Write-Host "Image URL fixing complete! See $logFile for details." 