<?php
require_once '../config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    if (!isset($data['id'])) {
        throw new Exception('No signatory ID provided');
    }
    
    // Get campus from session
    $campus = $_SESSION['username'];
    
    // Check if signatory exists and belongs to the current campus
    $checkStmt = $pdo->prepare("SELECT id FROM signatories WHERE id = ? AND campus = ?");
    $checkStmt->execute([$data['id'], $campus]);
    
    if ($checkStmt->rowCount() === 0) {
        throw new Exception('Signatory not found or you do not have permission to delete it');
    }
    
    // Delete signatory
    $stmt = $pdo->prepare("DELETE FROM signatories WHERE id = ? AND campus = ?");
    $result = $stmt->execute([$data['id'], $campus]);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Signatory deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete signatory');
    }
    
} catch (Exception $e) {
    error_log("Error in delete_signatory.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while deleting the signatory'
    ]);
}
?> 