@echo off
echo ==============================
echo Fix ALL Website Images
echo ==============================
echo.
echo This script will fix ALL image types in the website:
echo - Blog images
echo - Options trading images
echo - Team member images
echo - All other image types
echo.

powershell -ExecutionPolicy Bypass -File fix_all_images.ps1

echo.
echo Process completed! All images should now display correctly.
echo.
echo Press any key to exit...
pause > nul 