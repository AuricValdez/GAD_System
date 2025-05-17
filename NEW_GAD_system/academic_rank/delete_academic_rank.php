<?php
error_reporting(0); // Disable error reporting to prevent HTML in JSON
header('Content-Type: application/json');

try {
    require_once '../config.php'; // Use the correct config file
    
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || empty($data['id'])) {
        throw new Exception('Academic rank ID is required');
    }

    $id = $data['id'];
    
    // Prepare and execute delete statement with correct table name
    $stmt = $pdo->prepare("DELETE FROM academic_ranks WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Academic rank deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Academic rank not found']);
        }
    } else {
        throw new Exception('Failed to delete academic rank');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
