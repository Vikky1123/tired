<?php
/**
 * Response Utilities
 */

/**
 * Send a JSON response
 * 
 * @param mixed $data The data to include in the response
 * @param int $status HTTP status code
 * @param bool $success Whether the request was successful
 * @param string $message Optional message
 */
function sendJsonResponse($data, $status = 200, $success = true, $message = '') {
    // Set the HTTP status code
    http_response_code($status);
    
    // Set JSON content type
    header('Content-Type: application/json');
    
    // Create response array
    $response = [
        'success' => $success,
        'data' => $data
    ];
    
    // Add message if provided
    if (!empty($message)) {
        $response['message'] = $message;
    }
    
    // Output JSON
    echo json_encode($response);
    exit;
}

/**
 * Send a success response
 * 
 * @param mixed $data The data to include in the response
 * @param string $message Optional success message
 * @param int $status HTTP status code (default 200)
 */
function sendSuccessResponse($data = null, $message = '', $status = 200) {
    sendJsonResponse($data, $status, true, $message);
}

/**
 * Send an error response
 * 
 * @param string $message Error message
 * @param int $status HTTP status code (default 400)
 * @param mixed $data Additional error data
 */
function sendErrorResponse($message, $status = 400, $data = null) {
    sendJsonResponse($data, $status, false, $message);
}

/**
 * Send a 404 Not Found response
 * 
 * @param string $message Error message (default "Resource not found")
 */
function sendNotFoundResponse($message = 'Resource not found') {
    sendErrorResponse($message, 404);
}

/**
 * Send a 401 Unauthorized response
 * 
 * @param string $message Error message (default "Unauthorized access")
 */
function sendUnauthorizedResponse($message = 'Unauthorized access') {
    sendErrorResponse($message, 401);
}

/**
 * Send a 403 Forbidden response
 * 
 * @param string $message Error message (default "Access forbidden")
 */
function sendForbiddenResponse($message = 'Access forbidden') {
    sendErrorResponse($message, 403);
}

/**
 * Send a 500 Internal Server Error response
 * 
 * @param string $message Error message (default "Server error")
 * @param mixed $data Additional error data
 */
function sendServerErrorResponse($message = 'Server error', $data = null) {
    sendErrorResponse($message, 500, $data);
} 