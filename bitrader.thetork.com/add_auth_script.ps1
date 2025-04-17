# PowerShell script to add auth-status.js to all HTML files
$directories = @(
    ".",
    "Signup-Signin",
    "about-us",
    "our-services",
    "our-team",
    "our-pricing",
    "contact-us",
    "Terms and conditions",
    "blog",
    "services",
    "teams",
    "home-two",
    "home-three",
    "home-four",
    "home-five",
    "main page folder"
)

foreach ($dir in $directories) {
    $htmlFiles = Get-ChildItem -Path $dir -Filter "*.html" -File -ErrorAction SilentlyContinue
    
    foreach ($file in $htmlFiles) {
        $content = Get-Content -Path $file.FullName -Raw
        
        # Skip if the script is already included
        if ($content -match "auth-status\.js") {
            Write-Host "Script already exists in $($file.FullName)"
            continue
        }
        
        # Calculate relative path to wp-content based on file depth
        $depth = ($file.DirectoryName -split '\\').Count - ($PWD.Path -split '\\').Count
        $relativePath = "../" * $depth
        if ($dir -eq ".") { $relativePath = "" }
        
        # Find the theme-mode.js script tag to insert our new script after it
        if ($content -match '(?ms)(.*<script src="[^"]*theme-mode\.js"[^>]*>)(.*)') {
            $beforeScript = $matches[1]
            $afterScript = $matches[2]
            
            # Prepare the script tag with correct relative path
            $scriptTag = "`n    <script src=`"$($relativePath)wp-content/themes/bitrader/assets/js/auth-status.js`"></script>"
            
            # Combine the content
            $newContent = $beforeScript + $scriptTag + $afterScript
            
            # Save the modified content
            $newContent | Set-Content -Path $file.FullName -Force -Encoding UTF8
            
            Write-Host "Added auth-status.js to $($file.FullName)"
        } else {
            Write-Host "Could not find theme-mode.js script tag in $($file.FullName)"
        }
    }
} 