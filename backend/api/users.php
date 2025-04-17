<?php
/**
 * Users API Endpoints
 */
require_once __DIR__ . '/../models/User.php';

// Check authentication for all user endpoints
$payload = authenticateUser();

if (!$payload) {
    sendUnauthorizedResponse('Authentication required');
}

// Get user ID from endpoint
$userId = isset($endpoints[1]) ? $endpoints[1] : null;
$action = isset($endpoints[2]) ? $endpoints[2] : '';

// Handle different user actions
if ($userId === 'me') {
    // Current user operations
    $userId = $payload['uid'];
    handleCurrentUser($action);
} elseif ($userId === 'all') {
    // Listing all users (admin only)
    if ($payload['role'] !== 'admin') {
        sendForbiddenResponse('Admin access required');
    }
    handleAllUsers();
} elseif (is_numeric($userId)) {
    // Specific user operations
    handleSpecificUser($userId, $action);
} else {
    sendNotFoundResponse('Invalid user endpoint');
}

/**
 * Handle current user operations
 */
function handleCurrentUser($action) {
    global $requestMethod, $payload;
    
    $userId = $payload['uid'];
    
    switch ($action) {
        case '':
            // Get current user profile
            if ($requestMethod !== 'GET') {
                sendErrorResponse('Method not allowed', 405);
            }
            
            $user = User::getById($userId);
            
            if (!$user) {
                sendNotFoundResponse('User not found');
            }
            
            // Remove password from response
            unset($user['password']);
            
            sendSuccessResponse(['user' => $user]);
            break;
            
        case 'update':
            // Update current user profile
            if ($requestMethod !== 'PUT') {
                sendErrorResponse('Method not allowed', 405);
            }
            
            $requestBody = json_decode(file_get_contents('php://input'), true);
            
            // Don't allow role updates for non-admin users
            if (isset($requestBody['role']) && $payload['role'] !== 'admin') {
                unset($requestBody['role']);
            }
            
            $success = User::update($userId, $requestBody);
            
            if (!$success) {
                sendErrorResponse('Failed to update user', 400);
            }
            
            $user = User::getById($userId);
            
            // Remove password from response
            unset($user['password']);
            
            sendSuccessResponse(['user' => $user], 'User updated successfully');
            break;
            
        case 'password':
            // Change password
            if ($requestMethod !== 'PUT') {
                sendErrorResponse('Method not allowed', 405);
            }
            
            $requestBody = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($requestBody['currentPassword']) || !isset($requestBody['newPassword'])) {
                sendErrorResponse('Current password and new password are required', 400);
            }
            
            // Verify current password
            $user = User::getById($userId);
            
            if (!verifyPassword($requestBody['currentPassword'], $user['password'])) {
                sendErrorResponse('Current password is incorrect', 401);
            }
            
            // Update password
            $success = User::update($userId, ['password' => $requestBody['newPassword']]);
            
            if (!$success) {
                sendErrorResponse('Failed to update password', 400);
            }
            
            sendSuccessResponse(null, 'Password updated successfully');
            break;
            
        default:
            sendNotFoundResponse('Action not found');
    }
}

/**
 * Handle operations for all users (admin only)
 */
function handleAllUsers() {
    global $requestMethod;
    
    if ($requestMethod !== 'GET') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    // Get pagination parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Get users
    $users = User::getAll($limit, $offset);
    $total = User::count();
    
    // Remove passwords from response
    foreach ($users as &$user) {
        unset($user['password']);
    }
    
    sendSuccessResponse([
        'users' => $users,
        'pagination' => [
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]
    ]);
}

/**
 * Handle operations for a specific user
 */
function handleSpecificUser($userId, $action) {
    global $requestMethod, $payload;
    
    // Only admin can access other users' data
    if ($payload['role'] !== 'admin' && $payload['uid'] != $userId) {
        sendForbiddenResponse('Admin access required');
    }
    
    switch ($action) {
        case '':
            // Get user by ID
            if ($requestMethod !== 'GET') {
                sendErrorResponse('Method not allowed', 405);
            }
            
            $user = User::getById($userId);
            
            if (!$user) {
                sendNotFoundResponse('User not found');
            }
            
            // Remove password from response
            unset($user['password']);
            
            sendSuccessResponse(['user' => $user]);
            break;
            
        case 'update':
            // Update user (admin only)
            if ($requestMethod !== 'PUT') {
                sendErrorResponse('Method not allowed', 405);
            }
            
            if ($payload['role'] !== 'admin') {
                sendForbiddenResponse('Admin access required');
            }
            
            $requestBody = json_decode(file_get_contents('php://input'), true);
            
            $success = User::update($userId, $requestBody);
            
            if (!$success) {
                sendErrorResponse('Failed to update user', 400);
            }
            
            $user = User::getById($userId);
            
            // Remove password from response
            unset($user['password']);
            
            sendSuccessResponse(['user' => $user], 'User updated successfully');
            break;
            
        case 'delete':
            // Delete user (admin only)
            if ($requestMethod !== 'DELETE') {
                sendErrorResponse('Method not allowed', 405);
            }
            
            if ($payload['role'] !== 'admin') {
                sendForbiddenResponse('Admin access required');
            }
            
            $success = User::delete($userId);
            
            if (!$success) {
                sendErrorResponse('Failed to delete user', 400);
            }
            
            sendSuccessResponse(null, 'User deleted successfully');
            break;
            
        default:
            sendNotFoundResponse('Action not found');
    }
} 