<?php
header('Content-Type: application/json');
session_start();

// Include database connection
require_once '../../includes/db_connection.php';

try {
    // Get all campuses with signatories
    $query = "SELECT * FROM signatories ORDER BY campus";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $allSignatories = [];
        
        while ($row = $result->fetch_assoc()) {
            $allSignatories[] = $row;
        }
        
        echo json_encode([
            'status' => 'success',
            'count' => count($allSignatories),
            'data' => $allSignatories
        ]);
    } else {
        echo json_encode([
            'status' => 'warning',
            'message' => 'No signatories found in the database.',
            'count' => 0,
            'data' => []
        ]);
    }
} catch (Exception $e) {
    error_log("Error listing signatories: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to list signatories: ' . $e->getMessage()
    ]);
}
?> 