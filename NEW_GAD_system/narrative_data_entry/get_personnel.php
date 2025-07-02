<?php
require_once '../config.php';
header('Content-Type: application/json');

try {
    $activityId = isset($_GET['activity_id']) ? intval($_GET['activity_id']) : 0;
    
    // Get all personnel without filtering
    $query = "SELECT DISTINCT p.id, p.name, p.academic_rank 
              FROM personnel p
              ORDER BY p.name ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If activity ID is provided, add a flag to indicate if personnel is used in the current activity
    if ($activityId > 0) {
        // Get all personnel from the current activity
        $usedQuery = "SELECT DISTINCT 
                          JSON_EXTRACT(pf.project_leader, '$[*]') as leaders,
                          JSON_EXTRACT(pf.assistant_project_leader, '$[*]') as assistants,
                          JSON_EXTRACT(pf.project_staff_coordinator, '$[*]') as staff
                      FROM ppas_forms pf
                      WHERE pf.id = ?";
        
        $usedStmt = $pdo->prepare($usedQuery);
        $usedStmt->execute([$activityId]);
        $usedData = $usedStmt->fetch(PDO::FETCH_ASSOC);
        
        // Extract personnel names from JSON arrays
        $usedNames = [];
        if ($usedData) {
            // Parse JSON strings into arrays
            $leaders = json_decode($usedData['leaders'], true) ?: [];
            $assistants = json_decode($usedData['assistants'], true) ?: [];
            $staff = json_decode($usedData['staff'], true) ?: [];
            
            // Merge all names
            $usedNames = array_merge($leaders, $assistants, $staff);
        }
        
        // Add flag to each personnel record
        foreach ($personnel as &$person) {
            $person['is_used_in_current'] = in_array($person['name'], $usedNames);
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $personnel
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 