# Website URL Fixer

This tool helps fix a website downloaded with HTTrack by replacing absolute URLs with relative ones and fixing broken links and images.

## What the Tools Do

1. **fix_urls.ps1** - The main PowerShell script that:
   - Replaces absolute URLs (e.g., `https://bitrader.thetork.com/page`) with relative paths (e.g., `./page`)
   - Fixes internal links
   - Updates CSS and JavaScript URL references
   - Creates a log file of all changes

2. **fix_urls.bat** - A simple batch file to run the PowerShell script with the right execution policy.

3. **verify_site.ps1** - A verification script to ensure no domain references remain.

4. **fix_image_urls.ps1** - A specialized script to fix broken image URLs, which:
   - Corrects image paths according to folder depth
   - Fixes team member images
   - Updates preloader, icons, and other static images
   - Ensures all images display properly

5. **fix_images.bat** - A batch file to run the image fixing script.

6. **fix_and_verify.bat** - A batch file that runs all scripts in sequence for complete site fixing.

## How to Use

### Step 1: Fix URLs and Links

First, run the general URL fixer:

```
.\fix_urls.bat
```

This will process all HTML, CSS, and JavaScript files, fixing general URL issues.

### Step 2: Fix Image URLs

Next, run the image URL fixer:

```
.\fix_images.bat
```

This will specifically address broken image paths and ensure all images display correctly.

### Step 3: Verify the Results

After fixing URLs, run the verification script to make sure no references to the original domain remain:

```
powershell -ExecutionPolicy Bypass -File verify_site.ps1
```

### Alternative: Run Everything at Once

You can also use the combined script to run all steps in sequence:

```
.\fix_and_verify.bat
```

### Step 4: Check for Any Remaining Issues

If the verification script finds any remaining references to the original domain, you can:

1. Check the files listed in the verification log
2. Manually edit those files, or
3. Refine the patterns in the scripts and run them again

## Important Notes

- These scripts are designed to run in the root directory of your downloaded website
- Back up your website before running these scripts
- The scripts create log files (`url_fix_log.txt`, `image_fix_log.txt`, and `site_verification_log.txt`) for troubleshooting
- If some images still don't display properly after running the scripts, check the browser console for specific paths that need fixing

## Customization

If your website uses a different domain, edit the `$originalDomain` variable in all scripts to match your domain. 