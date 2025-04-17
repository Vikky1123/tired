@echo off
echo Running URL Fixer Tool...
powershell -ExecutionPolicy Bypass -File fix_urls.ps1
echo.
echo URL fixing completed.
echo Press any key to exit...
pause > nul 