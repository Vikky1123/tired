# Universal Image URL Fixer

This tool helps fix broken image paths in websites downloaded using HTTrack or similar website copiers. It automatically adjusts relative paths based on the file's location in the directory structure.

## Features

- Fixes broken image paths in HTML files
- Supports multiple file types (HTML, PHP, etc.)
- Handles srcset attributes for responsive images
- Fixes JSON-LD structured data references
- Repairs meta tag image references
- Works with any website structure
- Creates detailed logs of changes made

## How to Use

### Option 1: Easy Mode (Using Batch File)

1. Copy both `universal_image_fix.ps1` and `universal_image_fix.bat` to your computer
2. Double-click on `universal_image_fix.bat`
3. Follow the on-screen prompts:
   - Enter the path to your downloaded website folder
   - Optionally provide the original domain name for fixing absolute URLs
   - Specify file extensions to process (default: html,htm,php)
4. The script will fix all image paths and generate a log file

### Option 2: Advanced Mode (PowerShell)

Run the script directly with custom parameters:

```powershell
.\universal_image_fix.ps1 -WebsiteRootFolder "C:\path\to\website" -DomainName "example.com" -ExtensionsToProcess "html,htm,php" -FixSrcsetAttributes $true -FixJsonLd $true
```

### Parameters

| Parameter | Description | Default |
|-----------|-------------|---------|
| WebsiteRootFolder | Path to the downloaded website | Current directory |
| DomainName | Original domain name (optional) | Empty |
| ExtensionsToProcess | File extensions to process | html,htm,php |
| FixSrcsetAttributes | Fix srcset attributes | $true |
| FixJsonLd | Fix JSON-LD image references | $true |
| LogDetails | Create detailed logs | $true |

## Common Problems Fixed

1. **Broken images in subdirectories**: Adjusts relative paths based on file depth
2. **Missing responsive images**: Fixes srcset attributes for all resolutions
3. **Broken blog images**: Corrects paths for blog post images and thumbnails
4. **Missing meta images**: Fixes Open Graph and other meta tag images
5. **Broken structured data**: Repairs image references in JSON-LD markup

## Troubleshooting

If images are still broken after running the script:

1. Check the log file (`image_fix_log.txt`) for error messages
2. Ensure you've specified the correct website root folder
3. Try providing the original domain name if absolute URLs are used

## Customizing for Specific Sites

For websites with unique structures or naming patterns, you may need to add additional regex patterns. Edit the `universal_image_fix.ps1` file and add new replacements in the appropriate sections. 