@echo off
echo ======================================
echo Quick Fix for Subdirectory Image Paths
echo ======================================
echo.
echo This script will fix image paths in subdirectories.
echo The index.html file already works correctly.
echo.

powershell -ExecutionPolicy Bypass -File quick_image_fix.ps1

echo.
echo Process completed!
echo.
echo Press any key to exit...
pause > nul 