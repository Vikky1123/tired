<?php
/**
 * Authentication Test Script
 * This script tests the login and registration functionality
 */

// Set content type to JSON
header('Content-Type: application/json');

// Include necessary files
require_once __DIR__ . '/models/User.php';

// Create a test user if not exists
$testUser = User::getByUsername('testuser');

if (!$testUser) {
    echo "Creating test user...\n";
    
    $userData = [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password123',
        'fullName' => 'Test User',
        'phone' => '1234567890',
        'country' => 'United States',
        'role' => 'user'
    ];
    
    $userId = User::create($userData);
    
    if ($userId) {
        echo "Test user created with ID: $userId\n";
    } else {
        echo "Failed to create test user\n";
        exit;
    }
}

// Test authentication
$user = User::authenticate('testuser', 'password123');

if ($user) {
    echo "Authentication successful!\n";
    echo "User data: " . json_encode($user, JSON_PRETTY_PRINT) . "\n";
    
    // Generate a token
    $token = User::generateToken($user);
    echo "Generated token: $token\n";
} else {
    echo "Authentication failed!\n";
}

// Test invalid authentication
$invalidUser = User::authenticate('testuser', 'wrongpassword');

if (!$invalidUser) {
    echo "Invalid authentication test passed\n";
} else {
    echo "Invalid authentication test failed\n";
} 