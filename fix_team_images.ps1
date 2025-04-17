# Direct fix for team member images

Write-Host "Starting fix for team member images..."

function Fix-TeamImages {
    param (
        [string]$FilePath
    )
    
    # Skip if not an HTML file
    if (-not $FilePath.EndsWith(".html")) {
        return $false
    }
    
    # Skip if file doesn't exist
    if (-not (Test-Path $FilePath)) {
        return $false
    }
    
    try {
        # Read content
        $content = Get-Content -Path $FilePath -Raw -Encoding UTF8
        
        # Skip if file is empty or doesn't contain team images
        if ($null -eq $content -or -not ($content -match "team_\d-2\.png")) {
            return $false
        }
        
        Write-Host "Processing $FilePath"
        $originalContent = $content
        
        # Determine path to root based on directory structure
        $relativePath = $FilePath.Replace("$PWD\bitrader.thetork.com\", "")
        $parts = $relativePath.Split("\")
        $depth = $parts.Count - 1
        
        Write-Host "  File depth: $depth"
        
        # Skip main index.html
        if ($depth -eq 0 -and $parts[-1] -eq "index.html") {
            Write-Host "  Skipping main index.html"
            return $false
        }
        
        # Create the correct path prefix
        $prefix = ""
        for ($i = 0; $i -lt $depth; $i++) {
            $prefix += "../"
        }
        
        Write-Host "  Using prefix: $prefix"
        
        # Fix team image src attributes
        # This matches src="./wp-content/uploads/2023/10/team_X-2.png" and replaces with correct prefix
        $content = $content -replace 'src="\.?/wp-content/uploads/2023/10/team_(\d)-2\.png"', "src=`"${prefix}wp-content/uploads/2023/10/team_`$1-2.png`""
        
        # Fix srcset attributes which have two parts (before and after comma)
        $content = $content -replace 'srcset="wp-content/uploads/2023/10/team_(\d)-2\.png', "srcset=`"${prefix}wp-content/uploads/2023/10/team_`$1-2.png"
        $content = $content -replace 'srcset=".*team_\d-2\.png \d+w, ./wp-content/uploads/2023/10/team_(\d)-2-228x300\.png', "srcset=`"${prefix}wp-content/uploads/2023/10/team_`$1-2.png 450w, ${prefix}wp-content/uploads/2023/10/team_`$1-2-228x300.png"
        
        # Only save if content changed
        if ($content -ne $originalContent) {
            Set-Content -Path $FilePath -Value $content -Encoding UTF8
            Write-Host "  Updated file with team image fixes" -ForegroundColor Green
            return $true
        } else {
            Write-Host "  No changes needed" -ForegroundColor Yellow
            return $false
        }
    }
    catch {
        Write-Host "Error processing $FilePath : $_" -ForegroundColor Red
        return $false
    }
}

# Find all HTML files
$htmlFiles = Get-ChildItem -Path "bitrader.thetork.com" -Include "*.html" -Recurse

$totalFiles = $htmlFiles.Count
$fixedFiles = 0

Write-Host "Found $totalFiles HTML files to check for team images"

foreach ($file in $htmlFiles) {
    $isFixed = Fix-TeamImages -FilePath $file.FullName
    if ($isFixed) {
        $fixedFiles++
    }
}

Write-Host "Completed! Fixed team images in $fixedFiles files." -ForegroundColor Green 