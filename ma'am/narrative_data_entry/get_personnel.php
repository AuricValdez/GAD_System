<?php
require_once '../config.php';
header('Content-Type: application/json');

try {
    $activityId = isset($_GET['activity_id']) ? intval($_GET['activity_id']) : 0;
    
    // Base query to get all personnel
    $query = "SELECT DISTINCT p.id, p.name, p.academic_rank 
              FROM personnel p 
              WHERE p.name NOT IN (
                  -- Get all project leaders from all arrays
                  SELECT DISTINCT ppl.name
                  FROM ppas_forms pf
                  CROSS JOIN JSON_TABLE(
                      pf.project_leader,
                      '$[*]' COLUMNS(name VARCHAR(255) PATH '$')
                  ) ppl
                  WHERE pf.project_leader IS NOT NULL
                  
                  UNION
                  
                  -- Get all assistant project leaders from all arrays
                  SELECT DISTINCT apl.name
                  FROM ppas_forms pf
                  CROSS JOIN JSON_TABLE(
                      pf.assistant_project_leader,
                      '$[*]' COLUMNS(name VARCHAR(255) PATH '$')
                  ) apl
                  WHERE pf.assistant_project_leader IS NOT NULL
                  
                  UNION
                  
                  -- Get all project staff coordinators from all arrays
                  SELECT DISTINCT psc.name
                  FROM ppas_forms pf
                  CROSS JOIN JSON_TABLE(
                      pf.project_staff_coordinator,
                      '$[*]' COLUMNS(name VARCHAR(255) PATH '$')
                  ) psc
                  WHERE pf.project_staff_coordinator IS NOT NULL
              )
              ORDER BY p.name ASC";

    // If activity ID is provided, modify query to exclude personnel from other activities
    // but include those from the current activity
    if ($activityId > 0) {
        $query = "SELECT DISTINCT p.id, p.name, p.academic_rank 
                 FROM personnel p 
                 WHERE p.name NOT IN (
                     -- Get all project leaders from other activities
                     SELECT DISTINCT ppl.name
                     FROM ppas_forms pf
                     CROSS JOIN JSON_TABLE(
                         pf.project_leader,
                         '$[*]' COLUMNS(name VARCHAR(255) PATH '$')
                     ) ppl
                     WHERE pf.id != ? AND pf.project_leader IS NOT NULL
                     
                     UNION
                     
                     -- Get all assistant project leaders from other activities
                     SELECT DISTINCT apl.name
                     FROM ppas_forms pf
                     CROSS JOIN JSON_TABLE(
                         pf.assistant_project_leader,
                         '$[*]' COLUMNS(name VARCHAR(255) PATH '$')
                     ) apl
                     WHERE pf.id != ? AND pf.assistant_project_leader IS NOT NULL
                     
                     UNION
                     
                     -- Get all project staff coordinators from other activities
                     SELECT DISTINCT psc.name
                     FROM ppas_forms pf
                     CROSS JOIN JSON_TABLE(
                         pf.project_staff_coordinator,
                         '$[*]' COLUMNS(name VARCHAR(255) PATH '$')
                     ) psc
                     WHERE pf.id != ? AND pf.project_staff_coordinator IS NOT NULL
                 )
                 AND p.name NOT IN (
                     -- Exclude personnel already in current activity
                     SELECT DISTINCT ppl.name
                     FROM ppas_forms pf
                     CROSS JOIN JSON_TABLE(
                         pf.project_leader,
                         '$[*]' COLUMNS(name VARCHAR(255) PATH '$')
                     ) ppl
                     WHERE pf.id = ? AND pf.project_leader IS NOT NULL
                     
                     UNION
                     
                     SELECT DISTINCT apl.name
                     FROM ppas_forms pf
                     CROSS JOIN JSON_TABLE(
                         pf.assistant_project_leader,
                         '$[*]' COLUMNS(name VARCHAR(255) PATH '$')
                     ) apl
                     WHERE pf.id = ? AND pf.assistant_project_leader IS NOT NULL
                     
                     UNION
                     
                     SELECT DISTINCT psc.name
                     FROM ppas_forms pf
                     CROSS JOIN JSON_TABLE(
                         pf.project_staff_coordinator,
                         '$[*]' COLUMNS(name VARCHAR(255) PATH '$')
                     ) psc
                     WHERE pf.id = ? AND pf.project_staff_coordinator IS NOT NULL
                 )
                 ORDER BY p.name ASC";
    }

    $stmt = $pdo->prepare($query);
    
    if ($activityId > 0) {
        // Bind the activity ID six times for all the conditions
        $stmt->execute([$activityId, $activityId, $activityId, $activityId, $activityId, $activityId]);
    } else {
        $stmt->execute();
    }

    $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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