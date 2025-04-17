@echo off
echo ==================================
echo Website URL Fixer and Verification
echo ==================================
echo.

echo Step 1: Fixing URLs in website files...
powershell -ExecutionPolicy Bypass -File fix_urls.ps1
echo.

echo Step 2: Fixing image URLs...
powershell -ExecutionPolicy Bypass -File fix_image_urls.ps1
echo.

echo Step 3: Verifying no domain references remain...
powershell -ExecutionPolicy Bypass -File verify_site.ps1
echo.

echo All tasks completed!
echo Check log files for details:
echo - url_fix_log.txt: General URL fixes
echo - image_fix_log.txt: Image path fixes
echo - site_verification_log.txt: Verification results
echo.
echo Press any key to exit...
pause > nul 