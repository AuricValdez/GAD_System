<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    // Log received data for debugging
    $rawData = file_get_contents('php://input');
    error_log("Received data: " . $rawData);
    
    // Get POST data
    $data = json_decode($rawData, true);
    
    // Validate required fields - use academicRank instead of academic_rank
    if (!isset($data['name']) || !isset($data['category']) || !isset($data['status']) || 
        !isset($data['gender']) || !isset($data['academicRank']) || !isset($data['campus'])) {
        throw new Exception('Missing required fields');
    }
    
    // Check for duplicate name within the same campus (case-insensitive)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM personnel WHERE LOWER(name) = LOWER(?) AND campus = ?");
    $stmt->execute([$data['name'], $data['campus']]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Personnel with this name already exists in this campus'
        ]);
        exit;
    }
    
    // Insert query - convert academicRank to academic_rank for database
    $stmt = $pdo->prepare("INSERT INTO personnel (
        name, 
        category, 
        status, 
        gender, 
        academic_rank,
        campus
    ) VALUES (?, ?, ?, ?, ?, ?)");
    
    $result = $stmt->execute([
        $data['name'],
        $data['category'],
        $data['status'],
        $data['gender'],
        $data['academicRank'], // Using academicRank from form data
        $data['campus']
    ]);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Personnel added successfully'
        ]);
    } else {
        throw new Exception('Failed to insert record');
    }
    
} catch(Exception $e) {
    error_log("Error in add_personnel.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>