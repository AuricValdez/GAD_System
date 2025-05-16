<?php
// Prevent PHP errors from being output
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '../php_errors.log');

// Include database configuration
require_once '../config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'data' => []
];

try {
    // Check if field and term parameters are provided
    if (!isset($_GET['field']) || !isset($_GET['term'])) {
        throw new Exception('Missing required parameters');
    }
    
    $field = $_GET['field'];
    $term = $_GET['term'];
    
    // Validate field parameter - only allow specific fields
    $allowedFields = ['program', 'project'];
    if (!in_array($field, $allowedFields)) {
        throw new Exception('Invalid field parameter');
    }
    
    // Prepare and execute query
    $sql = "SELECT DISTINCT $field FROM ppas_forms WHERE $field LIKE ? ORDER BY $field LIMIT 10";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception('Error preparing statement: ' . $conn->error);
    }
    
    $searchTerm = '%' . $term . '%';
    $stmt->bind_param('s', $searchTerm);
    
    if (!$stmt->execute()) {
        throw new Exception('Error executing statement: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row[$field];
    }
    
    $response['success'] = true;
    $response['data'] = $data;
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log('Error in get_autocomplete_data.php: ' . $e->getMessage());
} finally {
    // Close statement if it exists
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
    
    // Close connection if it exists
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
}

// Return JSON response
echo json_encode($response);
?> 