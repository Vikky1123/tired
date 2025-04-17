@echo off
echo ==============================
echo Fix Team Member Images
echo ==============================
echo.
echo This script will fix the broken team member images
echo on pages like "teams", "our-team", etc.
echo.

powershell -ExecutionPolicy Bypass -File fix_team_images.ps1

echo.
echo Process completed! The team member images should now display correctly.
echo.
echo Press any key to exit...
pause > nul 