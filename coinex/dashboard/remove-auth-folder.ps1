# Script to remove auth folder and its files
Write-Host "Starting to remove auth folder and related files..."

# Define the path to the auth folder
$authFolder = Join-Path -Path $PSScriptRoot -ChildPath "auth"

# Check if the folder exists
if (Test-Path $authFolder) {
    Write-Host "Auth folder found at: $authFolder"
    
    # Create a backup folder for auth content
    $backupFolder = Join-Path -Path $PSScriptRoot -ChildPath "auth-backup"
    
    # Create backup folder if it doesn't exist
    if (-not (Test-Path $backupFolder)) {
        New-Item -Path $backupFolder -ItemType Directory | Out-Null
        Write-Host "Created backup folder: $backupFolder"
    }
    
    # Copy all files to backup folder
    Write-Host "Backing up auth files..."
    Copy-Item -Path "$authFolder\*" -Destination $backupFolder -Recurse
    
    # Remove the auth folder
    Write-Host "Removing auth folder..."
    Remove-Item -Path $authFolder -Recurse -Force
    
    Write-Host "Auth folder has been removed successfully!"
} else {
    Write-Host "Auth folder not found at: $authFolder"
}

Write-Host "Removal process completed!" 