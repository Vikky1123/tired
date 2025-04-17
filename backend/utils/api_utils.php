<?php
/**
 * API Utilities
 * Functions for standardized API responses
 */

/**
 * Send a success response
 * 
 * @param mixed $data Response data
 * @param string $message Success message
 * @param int $statusCode HTTP status code
 */
function sendSuccessResponse($data = null, $message = 'Success', $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Send an error response
 * 
 * @param string $message Error message
 * @param int $statusCode HTTP status code
 * @param mixed $errors Optional error details
 */
function sendErrorResponse($message = 'Error', $statusCode = 400, $errors = null) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    $response = [
        'success' => false,
        'message' => $message,
    ];
    
    if ($errors !== null) {
        $response['errors'] = $errors;
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Send a server error response (500)
 * 
 * @param string $message Error message
 * @param mixed $errors Optional error details
 */
function sendServerErrorResponse($message = 'Internal Server Error', $errors = null) {
    sendErrorResponse($message, 500, $errors);
}

/**
 * Send an unauthorized response (401)
 * 
 * @param string $message Error message
 */
function sendUnauthorizedResponse($message = 'Unauthorized') {
    sendErrorResponse($message, 401);
}

/**
 * Send a not found response (404)
 * 
 * @param string $message Error message
 */
function sendNotFoundResponse($message = 'Not Found') {
    sendErrorResponse($message, 404);
}

/**
 * Send a forbidden response (403)
 * 
 * @param string $message Error message
 */
function sendForbiddenResponse($message = 'Forbidden') {
    sendErrorResponse($message, 403);
} 