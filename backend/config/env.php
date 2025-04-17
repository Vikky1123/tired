<?php
/**
 * Environment Variables Loader
 * Loads environment variables from .env file
 */

function loadEnv() {
    $envFile = __DIR__ . '/../.env';
    
    // Log if .env file doesn't exist rather than terminating the script
    if (!file_exists($envFile)) {
        $errorMsg = 'Environment file (.env) not found. Using default values.';
        error_log($errorMsg);
        
        // Set default values
        putenv("DB_HOST=127.0.0.1");
        putenv("DB_USER=root");
        putenv("DB_PASS=");
        putenv("DB_NAME=bitrader_db");
        putenv("DB_PORT=3306");
        putenv("JWT_SECRET=H7fP9tR3mK5xL2qY8sZ6vB4nG1jD0cE3wA7bV9");
        return;
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Skip lines without equals sign
        if (strpos($line, '=') === false) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remove quotes if present
        if (strpos($value, '"') === 0 || strpos($value, "'") === 0) {
            $value = substr($value, 1, -1);
        }
        
        // Set as environment variable
        putenv("{$name}={$value}");
        
        // Only define constants if they don't already exist
        if (!defined($name)) {
            define($name, $value);
        }
    }
}

// Load environment variables
loadEnv(); 