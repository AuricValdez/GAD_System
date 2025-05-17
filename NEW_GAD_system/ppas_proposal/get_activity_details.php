<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session for accessing user information
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// Get the activity ID from request
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Validate ID
if (empty($id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Activity ID is required'
    ]);
    exit;
}

try {
    // Include database connection
    require_once '../config.php';
    
    // Prepare and execute query to get activity details from ppas_forms table
    $query = "SELECT 
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
             FROM 
                ppas_forms 
             WHERE 
                id = ?";
    
    error_log("SQL Query: $query with param: [$id]");
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    if (!$success) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Get result
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Activity not found'
        ]);
        exit;
    }
    
    // Fetch activity details
    $activity = $result->fetch_assoc();
    
    // Format start date and time separately
    if (!empty($activity['start_date'])) {
        $startDate = new DateTime($activity['start_date']);
        $activity['start_date_only'] = $startDate->format('Y-m-d');
    } else {
        $activity['start_date_only'] = '';
    }
    
    if (!empty($activity['end_date'])) {
        $endDate = new DateTime($activity['end_date']);
        $activity['end_date_only'] = $endDate->format('Y-m-d');
    } else {
        $activity['end_date_only'] = '';
    }
    
    if (!empty($activity['start_time'])) {
        $startTime = new DateTime($activity['start_time']);
        $activity['start_time_only'] = $startTime->format('H:i');
    } else {
        $activity['start_time_only'] = '';
    }
    
    if (!empty($activity['end_time'])) {
        $endTime = new DateTime($activity['end_time']);
        $activity['end_time_only'] = $endTime->format('H:i');
    } else {
        $activity['end_time_only'] = '';
    }
    
    // Also keep the combined datetime for backward compatibility
    if (!empty($activity['start_date']) && !empty($activity['start_time'])) {
        $startDate = new DateTime($activity['start_date']);
        $startTime = new DateTime($activity['start_time']);
        
        $startDate->setTime(
            (int)$startTime->format('H'),
            (int)$startTime->format('i')
        );
        
        $activity['formatted_start_date'] = $startDate->format('Y-m-d\TH:i');
    } else {
        $activity['formatted_start_date'] = '';
    }
    
    // Combine end_date and end_time
    if (!empty($activity['end_date']) && !empty($activity['end_time'])) {
        $endDate = new DateTime($activity['end_date']);
        $endTime = new DateTime($activity['end_time']);
        
        $endDate->setTime(
            (int)$endTime->format('H'),
            (int)$endTime->format('i')
        );
        
        $activity['formatted_end_date'] = $endDate->format('Y-m-d\TH:i');
    } else {
        $activity['formatted_end_date'] = '';
    }
    
    // Get project personnel from ppas_personnel table based on roles
    $personnel_query = "SELECT
                           pp.role,
                           p.name
                        FROM
                           ppas_personnel pp
                        JOIN
                           personnel p ON pp.personnel_id = p.id
                        WHERE
                           pp.ppas_form_id = ?
                        ORDER BY
                           pp.role, p.name";
    
    $personnel_stmt = $conn->prepare($personnel_query);
    if (!$personnel_stmt) {
        throw new Exception("Prepare personnel statement failed: " . $conn->error);
    }
    
    $personnel_stmt->bind_param("i", $id);
    $success = $personnel_stmt->execute();
    if (!$success) {
        throw new Exception("Execute personnel query failed: " . $personnel_stmt->error);
    }
    
    $personnel_result = $personnel_stmt->get_result();
    
    // Initialize arrays for different personnel roles
    $project_leaders = [];
    $assistant_project_leaders = [];
    $project_staff = [];
    
    // Group personnel by role
    while ($row = $personnel_result->fetch_assoc()) {
        switch ($row['role']) {
            case 'Project Leader':
                $project_leaders[] = $row['name'];
                break;
            case 'Assistant Project Leader':
                $assistant_project_leaders[] = $row['name'];
                break;
            case 'Staff':
                $project_staff[] = $row['name'];
                break;
        }
    }
    
    // Return success with activity details
    echo json_encode([
        'success' => true,
        'message' => 'Activity details fetched successfully',
        'project' => $activity['project'],
        'program' => $activity['program'],
        'venue' => $activity['location'],
        'start_date' => $activity['formatted_start_date'],
        'end_date' => $activity['formatted_end_date'],
        'start_date_only' => $activity['start_date_only'],
        'end_date_only' => $activity['end_date_only'],
        'start_time_only' => $activity['start_time_only'],
        'end_time_only' => $activity['end_time_only'],
        'project_leaders' => implode(", ", $project_leaders),
        'assistant_project_leaders' => implode(", ", $assistant_project_leaders),
        'project_staff' => implode(", ", $project_staff),
        'external_type' => $activity['external_type'],
        'total_male' => $activity['total_male'],
        'total_female' => $activity['total_female'],
        'total_beneficiaries' => $activity['total_beneficiaries'],
        'approved_budget' => $activity['approved_budget'],
        'source_of_budget' => $activity['source_of_budget']
    ]);
    
    // Close statements and connection
    $stmt->close();
    $personnel_stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    // Return error
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 