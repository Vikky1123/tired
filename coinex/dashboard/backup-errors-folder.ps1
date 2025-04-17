# Script to backup and remove errors folder and its files
Write-Host "Starting to backup and remove errors folder..."

# Define the path to the errors folder
$errorsFolder = Join-Path -Path $PSScriptRoot -ChildPath "errors"

# Check if the folder exists
if (Test-Path $errorsFolder) {
    Write-Host "Errors folder found at: $errorsFolder"
    
    # Create a backup folder for errors content
    $backupFolder = Join-Path -Path $PSScriptRoot -ChildPath "errors-backup"
    
    # Create backup folder if it doesn't exist
    if (-not (Test-Path $backupFolder)) {
        New-Item -Path $backupFolder -ItemType Directory | Out-Null
        Write-Host "Created backup folder: $backupFolder"
    }
    
    # Copy all files to backup folder
    Write-Host "Backing up errors files..."
    Copy-Item -Path "$errorsFolder\*" -Destination $backupFolder -Recurse
    
    # Remove the errors folder
    Write-Host "Removing errors folder..."
    Remove-Item -Path $errorsFolder -Recurse -Force
    
    Write-Host "Errors folder has been removed successfully!"
} else {
    Write-Host "Errors folder not found at: $errorsFolder"
}

Write-Host "Backup and removal process completed!" 