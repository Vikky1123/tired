<?php
/**
 * Authentication API Endpoints
 */
require_once __DIR__ . '/../models/User.php';

// Get the remaining endpoint parts
$action = isset($endpoints[1]) ? $endpoints[1] : '';

// Handle different authentication actions
switch ($action) {
    case 'login':
        handleLogin();
        break;
        
    case 'register':
        handleRegister();
        break;
        
    case 'verify':
        handleVerifyToken();
        break;
        
    default:
        sendNotFoundResponse('Auth endpoint not found');
}

/**
 * Handle user login
 */
function handleLogin() {
    global $requestMethod;
    
    if ($requestMethod !== 'POST') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    // Get request body
    $requestBody = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($requestBody['username']) || !isset($requestBody['password'])) {
        sendErrorResponse('Username and password are required', 400);
    }
    
    // Authenticate user
    $user = User::authenticate($requestBody['username'], $requestBody['password']);
    
    if (!$user) {
        sendErrorResponse('Invalid username or password', 401);
    }
    
    // Generate JWT token
    $token = User::generateToken($user);
    
    sendSuccessResponse([
        'token' => $token,
        'user' => $user
    ], 'Login successful');
}

/**
 * Handle user registration
 */
function handleRegister() {
    global $requestMethod;
    
    if ($requestMethod !== 'POST') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    // Get request body
    $requestBody = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($requestBody['username']) || !isset($requestBody['email']) || !isset($requestBody['password'])) {
        sendErrorResponse('Username, email, and password are required', 400);
    }
    
    // Validate email format
    if (!filter_var($requestBody['email'], FILTER_VALIDATE_EMAIL)) {
        sendErrorResponse('Invalid email format', 400);
    }
    
    // Check if username or email already exists
    if (User::getByUsername($requestBody['username'])) {
        sendErrorResponse('Username already exists', 409);
    }
    
    if (User::getByEmail($requestBody['email'])) {
        sendErrorResponse('Email already exists', 409);
    }
    
    // Create user
    $userData = [
        'username' => $requestBody['username'],
        'email' => $requestBody['email'],
        'password' => $requestBody['password'],
        'fullName' => $requestBody['fullName'] ?? null,
        'role' => 'user' // Default role
    ];
    
    $userId = User::create($userData);
    
    if (!$userId) {
        sendServerErrorResponse('Failed to create user');
    }
    
    // Get the created user
    $user = User::getById($userId);
    
    if (!$user) {
        sendServerErrorResponse('User created but unable to retrieve');
    }
    
    // Remove password from response
    unset($user['password']);
    
    // Generate JWT token
    $token = User::generateToken($user);
    
    sendSuccessResponse([
        'token' => $token,
        'user' => $user
    ], 'Registration successful', 201);
}

/**
 * Handle token verification
 */
function handleVerifyToken() {
    global $requestMethod;
    
    if ($requestMethod !== 'GET') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    // Check authentication
    $payload = authenticateUser();
    
    if (!$payload) {
        sendUnauthorizedResponse('Invalid or expired token');
    }
    
    // Get user by ID
    $user = User::getById($payload['uid']);
    
    if (!$user) {
        sendUnauthorizedResponse('User not found');
    }
    
    // Remove password from response
    unset($user['password']);
    
    sendSuccessResponse([
        'user' => $user
    ], 'Token is valid');
} 