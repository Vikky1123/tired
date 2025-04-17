<?php
/**
 * Register API Endpoint
 * Creates a new user in the database
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../utils/api_utils.php';

// Enable CORS for API requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
    exit;
}

try {
    // Get JSON request body
    $requestBody = file_get_contents('php://input');
    if (!$requestBody) {
        sendErrorResponse('Empty request body', 400);
        exit;
    }
    
    $requestData = json_decode($requestBody, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendErrorResponse('Invalid JSON: ' . json_last_error_msg(), 400);
        exit;
    }

    // Check for required fields
    $requiredFields = ['username', 'email', 'password'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($requestData[$field]) || empty($requestData[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        sendErrorResponse('Missing required fields: ' . implode(', ', $missingFields), 400);
        exit;
    }
    
    // Check if username or email already exists
    $existingUser = User::getByUsername($requestData['username']);
    if ($existingUser) {
        sendErrorResponse('Username already exists', 409);
        exit;
    }
    
    $existingEmail = User::getByEmail($requestData['email']);
    if ($existingEmail) {
        sendErrorResponse('Email already exists', 409);
        exit;
    }
    
    // Prepare user data
    $userData = [
        'username' => $requestData['username'],
        'email' => $requestData['email'],
        'password' => $requestData['password'],
        'fullName' => $requestData['full_name'] ?? null,
        'phone' => $requestData['phone'] ?? null,
        'country' => $requestData['country'] ?? null,
        'role' => 'user' // Default role
    ];
    
    // Create user
    $userId = User::create($userData);
    
    if (!$userId) {
        sendErrorResponse('Failed to create user', 500);
        exit;
    }
    
    // Get created user
    $user = User::getById($userId);
    
    // Generate token
    $token = User::generateToken($user);
    
    // Return success with token and user data
    sendSuccessResponse([
        'token' => $token,
        'user' => $user
    ], 'User registered successfully');
    
} catch (Exception $e) {
    // Log the error
    error_log('Registration error: ' . $e->getMessage());
    sendErrorResponse('Server error: ' . $e->getMessage(), 500);
} 