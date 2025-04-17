@echo off
echo ==================================
echo Website Image URL Fixer
echo ==================================
echo.
echo This script will fix broken image URLs in your website.
echo.

echo Running image URL fixer...
powershell -ExecutionPolicy Bypass -File fix_image_urls.ps1
echo.

echo Process completed!
echo Check image_fix_log.txt for details.
echo.
echo Press any key to exit...
pause > nul 