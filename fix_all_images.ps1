# Comprehensive fix for all images in the site

Write-Host "Starting comprehensive image path fix for all HTML files..."

function Fix-AllImages {
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
        
        if ($null -eq $content) {
            return $false
        }
        
        Write-Host "Processing $FilePath"
        $originalContent = $content
        
        # Determine path to root based on directory structure
        $relativePath = $FilePath.Replace("$PWD\bitrader.thetork.com\", "")
        $parts = $relativePath.Split("\")
        $depth = $parts.Count - 1
        
        Write-Host "  File depth: $depth"
        
        # Skip main index.html - it's already working
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
        
        if ($depth -gt 0) {
            # Fix all image src attributes with ./wp-content prefix
            $content = $content -replace 'src="./wp-content/', "src=`"${prefix}wp-content/"
            
            # Fix srcset attributes that start directly with wp-content
            $content = $content -replace 'srcset="wp-content/', "srcset=`"${prefix}wp-content/"
            
            # Fix URLs in srcset that start with ./ after a comma and space
            $content = $content -replace '(srcset="[^"]+), ./wp-content/', "`$1, ${prefix}wp-content/"
            
            # Fix URLs for blog images in src attributes
            $content = $content -replace 'src="./wp-content/uploads/(\d{4}/\d{2}/[^"]+)"', "src=`"${prefix}wp-content/uploads/`$1`""
            
            # Fix other relative URLs
            $content = $content -replace '"./wp-includes/', "`"${prefix}wp-includes/"
            $content = $content -replace '"./wp-content/', "`"${prefix}wp-content/"
            
            # Fix blog images with specific date patterns
            $content = $content -replace 'src="./wp-content/uploads/2023/10/blog_post(\d+)-2\.(jpg|png|jpeg)"', "src=`"${prefix}wp-content/uploads/2023/10/blog_post`$1-2.`$2`""
            
            # Fix blog images referencing team images in srcset (THIS IS THE KEY FIX FOR BLOG IMAGES)
            $content = $content -replace 'srcset="[^"]*team_\\-2\.png[^"]*', "srcset=`"${prefix}wp-content/uploads/2023/10/blog_post"
            $content = $content -replace '(src="[^"]*blog_post\d+-2\.jpg"[^>]*srcset=")[^"]*', "`$1${prefix}wp-content/uploads/2023/10/blog_post"
            
            # Fix specific blog image references in srcset
            $content = $content -replace '(src="[^"]*blog_post01-2\.jpg"[^>]*srcset=")[^"]*', "`$1${prefix}wp-content/uploads/2023/10/blog_post01-2.jpg 710w, ${prefix}wp-content/uploads/2023/10/blog_post01-2-300x203.jpg 300w"
            $content = $content -replace '(src="[^"]*blog_post02-2\.jpg"[^>]*srcset=")[^"]*', "`$1${prefix}wp-content/uploads/2023/10/blog_post02-2.jpg 710w, ${prefix}wp-content/uploads/2023/10/blog_post02-2-300x203.jpg 300w"
            $content = $content -replace '(src="[^"]*blog_post03-2\.jpg"[^>]*srcset=")[^"]*', "`$1${prefix}wp-content/uploads/2023/10/blog_post03-2.jpg 710w, ${prefix}wp-content/uploads/2023/10/blog_post03-2-300x203.jpg 300w"
            $content = $content -replace '(src="[^"]*blog_post04-2\.jpg"[^>]*srcset=")[^"]*', "`$1${prefix}wp-content/uploads/2023/10/blog_post04-2.jpg 710w, ${prefix}wp-content/uploads/2023/10/blog_post04-2-300x203.jpg 300w"
            $content = $content -replace '(src="[^"]*blog_post05-2\.jpg"[^>]*srcset=")[^"]*', "`$1${prefix}wp-content/uploads/2023/10/blog_post05-2.jpg 710w, ${prefix}wp-content/uploads/2023/10/blog_post05-2-300x203.jpg 300w"
            $content = $content -replace '(src="[^"]*blog_post06-2\.jpg"[^>]*srcset=")[^"]*', "`$1${prefix}wp-content/uploads/2023/10/blog_post06-2.jpg 710w, ${prefix}wp-content/uploads/2023/10/blog_post06-2-300x203.jpg 300w"
            $content = $content -replace '(src="[^"]*blog_post07-2\.jpg"[^>]*srcset=")[^"]*', "`$1${prefix}wp-content/uploads/2023/10/blog_post07-2.jpg 710w, ${prefix}wp-content/uploads/2023/10/blog_post07-2-300x203.jpg 300w"
            
            # Fix options trading images
            $content = $content -replace 'src="./wp-content/uploads/2023/10/options_trading[^"]+"', "src=`"${prefix}wp-content/uploads/2023/10/options_trading`$1`""
            
            # Fix JSON-LD image references - Fixed escaping
            $content = $content -replace 'thumbnailUrl":"./wp-content/', "thumbnailUrl`":`"${prefix}wp-content/"
            $content = $content -replace '"url":"./wp-content/', "`"url`":`"${prefix}wp-content/"
            $content = $content -replace '"contentUrl":"./wp-content/', "`"contentUrl`":`"${prefix}wp-content/"
            
            # Fix meta tag image URLs
            $content = $content -replace 'content="./wp-content/uploads/', "content=`"${prefix}wp-content/uploads/"
        }
        
        # Only save if content changed
        if ($content -ne $originalContent) {
            Set-Content -Path $FilePath -Value $content -Encoding UTF8
            Write-Host "  Updated file with image fixes" -ForegroundColor Green
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

# Process all HTML files
$htmlFiles = Get-ChildItem -Path "bitrader.thetork.com" -Include "*.html" -Recurse | 
             Where-Object { $_.DirectoryName -notlike "*bitrader.thetork.com\wp-*" }

$totalFiles = $htmlFiles.Count
$fixedFiles = 0

Write-Host "Found $totalFiles HTML files to process"

foreach ($file in $htmlFiles) {
    $isFixed = Fix-AllImages -FilePath $file.FullName
    if ($isFixed) {
        $fixedFiles++
    }
}

Write-Host "Completed! Fixed $fixedFiles out of $totalFiles files." -ForegroundColor Green 