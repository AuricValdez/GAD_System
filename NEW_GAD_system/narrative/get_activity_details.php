<?php
// Disable error display but keep logging
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start session for accessing user information
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Debug log function
function debug_log($message, $data = null) {
    error_log("NARRATIVE_DETAILS DEBUG: " . $message . ($data !== null ? " - " . print_r($data, true) : ""));
}

try {
    // Check if the user is logged in
    if (!isset($_SESSION['username'])) {
        throw new Exception("User not logged in");
    }
    
    // Get the activity ID from request
    $activity_id = isset($_POST['activity_id']) ? $_POST['activity_id'] : '';
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    $quarter = isset($_POST['quarter']) ? $_POST['quarter'] : '';
    
    debug_log("Request params", ['activity_id' => $activity_id, 'year' => $year, 'quarter' => $quarter]);
    
    // Validate ID
    if (empty($activity_id)) {
        throw new Exception("Activity ID is required");
    }
    
    // Include database connection
    require_once '../config.php';
    
    // 1. Get data from ppas_forms table
    $ppasQuery = "SELECT 
                    id,
                    campus,
                    year,
                    quarter,
                    gender_issue_id,
                    project,
                    program,
                    activity,
                    location,
                    start_date,
                    end_date,
                    start_time,
                    end_time,
                    total_duration_hours,
                    lunch_break,
                    students_male,
                    students_female,
                    faculty_male,
                    faculty_female,
                    total_internal_male,
                    total_internal_female,
                    external_type,
                    external_male,
                    external_female,
                    total_male,
                    total_female,
                    total_beneficiaries,
                    approved_budget,
                    source_of_budget,
                    ps_attribution,
                    sdgs
                 FROM ppas_forms 
                 WHERE id = ?";
    
    $ppasStmt = $conn->prepare($ppasQuery);
    if (!$ppasStmt) {
        throw new Exception("Failed to prepare ppas_forms query: " . $conn->error);
    }
    
    $ppasStmt->bind_param("i", $activity_id);
    if (!$ppasStmt->execute()) {
        throw new Exception("Failed to execute ppas_forms query: " . $ppasStmt->error);
    }
    
    $ppasResult = $ppasStmt->get_result();
    if ($ppasResult->num_rows === 0) {
        throw new Exception("Activity not found");
    }
    
    $ppasData = $ppasResult->fetch_assoc();
    $ppasStmt->close();
    
    debug_log("PPAS data fetched", ['id' => $ppasData['id'], 'activity' => $ppasData['activity']]);
    
    // Format dates and times
    $formattedDates = [];
    
    if (!empty($ppasData['start_date'])) {
        $formattedDates['start_date'] = date('d/m/y', strtotime($ppasData['start_date']));
    } else {
        $formattedDates['start_date'] = '';
    }
    
    if (!empty($ppasData['end_date'])) {
        $formattedDates['end_date'] = date('d/m/y', strtotime($ppasData['end_date']));
    } else {
        $formattedDates['end_date'] = '';
    }
    
    if (!empty($ppasData['start_time'])) {
        $formattedDates['start_time'] = date('h:i A', strtotime($ppasData['start_time']));
    } else {
        $formattedDates['start_time'] = '';
    }
    
    if (!empty($ppasData['end_time'])) {
        $formattedDates['end_time'] = date('h:i A', strtotime($ppasData['end_time']));
    } else {
        $formattedDates['end_time'] = '';
    }
    
    // 2. Get personnel data
    $personnelQuery = "SELECT pp.role, p.name
                      FROM ppas_personnel pp
                      JOIN personnel p ON pp.personnel_id = p.id
                      WHERE pp.ppas_form_id = ?
                      ORDER BY pp.role, p.name";
    
    $personnelStmt = $conn->prepare($personnelQuery);
    if (!$personnelStmt) {
        throw new Exception("Failed to prepare personnel query: " . $conn->error);
    }
    
    $personnelStmt->bind_param("i", $activity_id);
    if (!$personnelStmt->execute()) {
        throw new Exception("Failed to execute personnel query: " . $personnelStmt->error);
    }
    
    $personnelResult = $personnelStmt->get_result();
    
    $personnel = [
        'project_leaders' => [],
        'assistant_leaders' => [],
        'staff' => []
    ];
    
    while ($row = $personnelResult->fetch_assoc()) {
        switch ($row['role']) {
            case 'Project Leader':
                $personnel['project_leaders'][] = $row['name'];
                break;
            case 'Assistant Project Leader':
                $personnel['assistant_leaders'][] = $row['name'];
                break;
            case 'Staff':
                $personnel['staff'][] = $row['name'];
                break;
        }
    }
    
    $personnelStmt->close();
    debug_log("Personnel data fetched", ['project_leaders_count' => count($personnel['project_leaders'])]);
    
    // 3. Get GAD proposal data
    $gadQuery = "SELECT 
                    proposal_id,
                    campus,
                    mode_of_delivery,
                    partner_office,
                    rationale,
                    general_objectives,
                    description,
                    budget_breakdown,
                    sustainability_plan,
                    project_leader_responsibilities,
                    assistant_leader_responsibilities,
                    staff_responsibilities,
                    specific_objectives,
                    strategies,
                    methods,
                    materials,
                    workplan,
                    monitoring_items,
                    specific_plans
                 FROM gad_proposals 
                 WHERE ppas_form_id = ?";
    
    $gadStmt = $conn->prepare($gadQuery);
    if (!$gadStmt) {
        throw new Exception("Failed to prepare GAD proposal query: " . $conn->error);
    }
    
    $gadStmt->bind_param("i", $activity_id);
    if (!$gadStmt->execute()) {
        throw new Exception("Failed to execute GAD proposal query: " . $gadStmt->error);
    }
    
    $gadResult = $gadStmt->get_result();
    $gadData = [];
    
    if ($gadResult->num_rows > 0) {
        $gadData = $gadResult->fetch_assoc();
        
        // Parse JSON fields if they exist
        $jsonFields = ['specific_objectives', 'strategies', 'methods', 'materials', 'workplan', 'monitoring_items', 'specific_plans'];
        
        foreach ($jsonFields as $field) {
            if (isset($gadData[$field]) && !empty($gadData[$field])) {
                try {
                    $gadData[$field] = json_decode($gadData[$field], true);
                } catch (Exception $e) {
                    debug_log("Error parsing JSON for field $field: " . $e->getMessage());
                    $gadData[$field] = [];
                }
            } else {
                $gadData[$field] = [];
            }
        }
    }
    
    $gadStmt->close();
    debug_log("GAD data fetched", ['found' => ($gadResult->num_rows > 0)]);
    
    // Parse SDGs data
    $sdgsArray = [];
    if (!empty($ppasData['sdgs'])) {
        // Handle various formats (JSON array, string array, comma-separated string)
        if (is_string($ppasData['sdgs'])) {
            // Try to parse as JSON first
            if (substr($ppasData['sdgs'], 0, 1) === '[') {
                try {
                    $sdgsArray = json_decode($ppasData['sdgs'], true);
                } catch (Exception $e) {
                    // If JSON parse fails, treat as comma-separated
                    $sdgsArray = array_map('trim', explode(',', $ppasData['sdgs']));
                }
            } else {
                // Treat as comma-separated
                $sdgsArray = array_map('trim', explode(',', $ppasData['sdgs']));
            }
        } elseif (is_array($ppasData['sdgs'])) {
            $sdgsArray = $ppasData['sdgs'];
        }
    }
    
    // Compile all data into a unified response
    $response = [
        'success' => true,
        'message' => 'Activity details fetched successfully',
        'details' => [
            // Basic info from ppas_forms
            'id' => $ppasData['id'],
            'activity' => $ppasData['activity'],
            'project' => $ppasData['project'],
            'program' => $ppasData['program'],
            'location' => $ppasData['location'],
            'start_date' => $formattedDates['start_date'],
            'end_date' => $formattedDates['end_date'],
            'start_time' => $formattedDates['start_time'],
            'end_time' => $formattedDates['end_time'],
            'total_duration_hours' => $ppasData['total_duration_hours'],
            'lunch_break' => $ppasData['lunch_break'],
            
            // Beneficiary data
            'students_male' => $ppasData['students_male'],
            'students_female' => $ppasData['students_female'],
            'faculty_male' => $ppasData['faculty_male'],
            'faculty_female' => $ppasData['faculty_female'],
            'total_internal_male' => $ppasData['total_internal_male'],
            'total_internal_female' => $ppasData['total_internal_female'],
            'external_type' => $ppasData['external_type'],
            'external_male' => $ppasData['external_male'],
            'external_female' => $ppasData['external_female'],
            'total_male' => $ppasData['total_male'],
            'total_female' => $ppasData['total_female'],
            'total_beneficiaries' => $ppasData['total_beneficiaries'],
            
            // Budget info
            'approved_budget' => $ppasData['approved_budget'],
            'source_of_budget' => $ppasData['source_of_budget'],
            'ps_attribution' => $ppasData['ps_attribution'],
            
            // SDGs data
            'sdgs' => $sdgsArray,
            
            // Personnel data
            'personnel' => $personnel,
            
            // GAD proposal data if available
            'gad_proposal' => $gadData ?: null
        ]
    ];
    
    // Output response as JSON
    echo json_encode($response);
    
} catch (Exception $e) {
    debug_log("Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 