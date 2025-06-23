<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['ppaId']) || !isset($data['psAttribution'])) {
        throw new Exception('Missing required data');
    }
    
    $ppaId = (int)$data['ppaId'];
    $psAttribution = (float)$data['psAttribution'];
    
    // Update the PS attribution in ppas_forms table
    $query = "UPDATE ppas_forms 
              SET ps_attribution = ? 
              WHERE id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('di', $psAttribution, $ppaId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update PS attribution');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'PS attribution updated successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 