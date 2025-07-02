<?php
// Start session and include database connection
session_start();
require_once('../includes/db_connection.php');

header('Content-Type: application/json');

// Check if activity ID is provided
if (!isset($_GET['activity_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No activity ID provided'
    ]);
    exit;
}

$activity_id = $_GET['activity_id'];

try {
    // Connect to database
    $conn = getConnection();
    
    // First check if selected_personnel column exists in ppas_forms
    $checkColumnQuery = "SELECT COLUMN_NAME 
                        FROM INFORMATION_SCHEMA.COLUMNS 
                        WHERE TABLE_NAME = 'ppas_forms' 
                        AND COLUMN_NAME = 'selected_personnel'";
    
    $checkColumnStmt = $conn->prepare($checkColumnQuery);
    $checkColumnStmt->execute();
    $columnExists = $checkColumnStmt->fetch(PDO::FETCH_ASSOC);
    
    // Prepare query to get project team members
    $query = "SELECT 
        id,
        project_leader,
        assistant_project_leader,
        project_staff_coordinator";
        
    // Only add selected_personnel to query if it exists
    if ($columnExists) {
        $query .= ", selected_personnel";
    }
    
    $query .= " FROM ppas_forms WHERE id = :activity_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':activity_id', $activity_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        echo json_encode([
            'success' => false,
            'message' => 'Activity not found'
        ]);
        exit;
    }
    
    // Initialize team members array
    $teamMembers = [];
    $selectedPersonnel = [];
    
    // Parse project_leader JSON if it exists
    if (!empty($row['project_leader'])) {
        $projectLeader = json_decode($row['project_leader'], true);
        if (is_array($projectLeader)) {
            // Check for different possible structures
            if (isset($projectLeader['id'])) {
                $teamMembers[] = [
                    'id' => $projectLeader['id'],
                    'name' => $projectLeader['name'] ?? 'Unknown',
                    'role' => 'Project Leader'
                ];
            } elseif (isset($projectLeader[0]) && is_array($projectLeader[0])) {
                foreach ($projectLeader as $leader) {
                    if (isset($leader['id']) || isset($leader['name'])) {
                        $teamMembers[] = [
                            'id' => $leader['id'] ?? null,
                            'name' => $leader['name'] ?? 'Unknown',
                            'role' => 'Project Leader'
                        ];
                    }
                }
            }
        } elseif (is_string($row['project_leader'])) {
            // Handle string value (possibly a single name)
            $teamMembers[] = [
                'id' => null,
                'name' => $row['project_leader'],
                'role' => 'Project Leader'
            ];
        }
    }
    
    // Parse assistant_project_leader JSON if it exists
    if (!empty($row['assistant_project_leader'])) {
        $assistantLeader = json_decode($row['assistant_project_leader'], true);
        if (is_array($assistantLeader)) {
            // Check for different possible structures
            if (isset($assistantLeader['id'])) {
                $teamMembers[] = [
                    'id' => $assistantLeader['id'],
                    'name' => $assistantLeader['name'] ?? 'Unknown',
                    'role' => 'Assistant Project Leader'
                ];
            } elseif (isset($assistantLeader[0]) && is_array($assistantLeader[0])) {
                foreach ($assistantLeader as $leader) {
                    if (isset($leader['id']) || isset($leader['name'])) {
                        $teamMembers[] = [
                            'id' => $leader['id'] ?? null,
                            'name' => $leader['name'] ?? 'Unknown',
                            'role' => 'Assistant Project Leader'
                        ];
                    }
                }
            }
        } elseif (is_string($row['assistant_project_leader'])) {
            // Handle string value (possibly a single name)
            $teamMembers[] = [
                'id' => null,
                'name' => $row['assistant_project_leader'],
                'role' => 'Assistant Project Leader'
            ];
        }
    }
    
    // Parse project_staff_coordinator JSON if it exists
    if (!empty($row['project_staff_coordinator'])) {
        $coordinator = json_decode($row['project_staff_coordinator'], true);
        if (is_array($coordinator)) {
            // Check for different possible structures
            if (isset($coordinator['id'])) {
                $teamMembers[] = [
                    'id' => $coordinator['id'],
                    'name' => $coordinator['name'] ?? 'Unknown',
                    'role' => 'Project Staff Coordinator'
                ];
            } elseif (isset($coordinator[0]) && is_array($coordinator[0])) {
                foreach ($coordinator as $staff) {
                    if (isset($staff['id']) || isset($staff['name'])) {
                        $teamMembers[] = [
                            'id' => $staff['id'] ?? null,
                            'name' => $staff['name'] ?? 'Unknown',
                            'role' => 'Project Staff Coordinator'
                        ];
                    }
                }
            }
        } elseif (is_string($row['project_staff_coordinator'])) {
            // Handle string value (possibly a single name)
            $teamMembers[] = [
                'id' => null,
                'name' => $row['project_staff_coordinator'],
                'role' => 'Project Staff Coordinator'
            ];
        }
    }
    
    // Parse selected_personnel JSON if it exists and the column exists
    if ($columnExists && isset($row['selected_personnel']) && !empty($row['selected_personnel'])) {
        $selected = json_decode($row['selected_personnel'], true);
        if (is_array($selected)) {
            // Handle array of personnel
            foreach ($selected as $person) {
                if (is_array($person) && isset($person['id'])) {
                    $selectedPersonnel[] = [
                        'id' => $person['id'],
                        'name' => $person['name'] ?? 'Unknown',
                        'type' => 'selected_personnel'
                    ];
                }
            }
        }
    }
    
    // Return team members list and selected personnel
    echo json_encode([
        'success' => true,
        'data' => $teamMembers,
        'selected_personnel' => $selectedPersonnel,
        'column_exists' => $columnExists ? true : false,
        'debug_info' => [
            'activity_id' => $activity_id,
            'team_members_count' => count($teamMembers),
            'selected_personnel_count' => count($selectedPersonnel)
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} 