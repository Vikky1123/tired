<?php
/**
 * Database Utilities
 */
require_once __DIR__ . '/../config/database.php';

/**
 * Execute a select query and return the results
 */
function executeQuery($sql, $params = []) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $types = '';
        $bindParams = [];
        
        // Build types string and values array
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_double($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $bindParams[] = $param;
        }
        
        // Create references array for bind_param
        $bindParamsReferences = [];
        $bindParamsReferences[] = $types;
        
        for ($i = 0; $i < count($bindParams); $i++) {
            // Reference is required for bind_param
            $bindParamsReferences[] = &$bindParams[$i];
        }
        
        // Call bind_param with dynamic parameters
        call_user_func_array([$stmt, 'bind_param'], $bindParamsReferences);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    $stmt->close();
    $conn->close();
    
    return $data;
}

/**
 * Execute an insert, update, or delete query and return the affected rows
 */
function executeNonQuery($sql, $params = []) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $types = '';
        $bindParams = [];
        
        // Build types string and values array
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_double($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $bindParams[] = $param;
        }
        
        // Create references array for bind_param
        $bindParamsReferences = [];
        $bindParamsReferences[] = $types;
        
        for ($i = 0; $i < count($bindParams); $i++) {
            // Reference is required for bind_param
            $bindParamsReferences[] = &$bindParams[$i];
        }
        
        // Call bind_param with dynamic parameters
        call_user_func_array([$stmt, 'bind_param'], $bindParamsReferences);
    }
    
    $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    
    $stmt->close();
    $conn->close();
    
    return $affectedRows;
}

/**
 * Execute an insert operation and return the last inserted ID
 */
function executeInsert($sql, $params = []) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $types = '';
        $bindParams = [];
        
        // Build types string and values array
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_double($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $bindParams[] = $param;
        }
        
        // Create references array for bind_param
        $bindParamsReferences = [];
        $bindParamsReferences[] = $types;
        
        for ($i = 0; $i < count($bindParams); $i++) {
            // Reference is required for bind_param
            $bindParamsReferences[] = &$bindParams[$i];
        }
        
        // Call bind_param with dynamic parameters
        call_user_func_array([$stmt, 'bind_param'], $bindParamsReferences);
    }
    
    $stmt->execute();
    $insertId = $conn->insert_id;
    
    $stmt->close();
    $conn->close();
    
    return $insertId;
} 