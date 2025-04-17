$directories = @('.\special-pages', '.\auth', '.\errors', '.\app')

# Define regex patterns for the six sections to remove
$pattern1 = '(?s)<li class="nav-item">\s*<a class="nav-link" data-bs-toggle="collapse" href="#ui".*?</ul>\s*</li>'
$pattern2 = '(?s)<li class="nav-item">\s*<a class="nav-link" data-bs-toggle="collapse" href="#sidebar-widget".*?</ul>\s*</li>'
$pattern3 = '(?s)<li class="nav-item">\s*<a class="nav-link" data-bs-toggle="collapse" href="#sidebar-maps".*?</ul>\s*</li>'
$pattern4 = '(?s)<li class="nav-item">\s*<a class="nav-link" data-bs-toggle="collapse" href="#sidebar-form".*?</ul>\s*</li>'
$pattern5 = '(?s)<li class="nav-item">\s*<a class="nav-link" data-bs-toggle="collapse" href="#sidebar-table".*?</ul>\s*</li>'
$pattern6 = '(?s)<li class="nav-item">\s*<a class="nav-link" data-bs-toggle="collapse" href="#sidebar-icons".*?</ul>\s*</li>'

# Process each directory
foreach ($dir in $directories) {
    Write-Host "Processing directory: $dir"
    
    # Get all HTML files in the directory
    $files = Get-ChildItem -Path $dir -Filter "*.html"
    
    foreach ($file in $files) {
        try {
            Write-Host "Processing file: $($file.FullName)"
            
            # Read the file content
            $content = Get-Content -Path $file.FullName -Raw -ErrorAction Stop
            
            # Skip files that are too large (might be binary or not text files)
            if ($content.Length -gt 10MB) {
                Write-Host "Skipping large file: $($file.FullName)"
                continue
            }
            
            # Create a backup
            Copy-Item -Path $file.FullName -Destination "$($file.FullName).bak" -Force -ErrorAction Stop
            
            # Apply all regex replacements
            $newContent = $content
            $newContent = $newContent -replace $pattern1, ""
            $newContent = $newContent -replace $pattern2, ""
            $newContent = $newContent -replace $pattern3, ""
            $newContent = $newContent -replace $pattern4, ""
            $newContent = $newContent -replace $pattern5, ""
            $newContent = $newContent -replace $pattern6, ""
            
            # Save the modified content back to the file
            $newContent | Set-Content -Path $file.FullName -Force -ErrorAction Stop
            
            Write-Host "Successfully updated: $($file.FullName)"
        }
        catch {
            Write-Host "Error processing file $($file.FullName): $_"
        }
    }
}

Write-Host "Processing complete!"
