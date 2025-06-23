<?php
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You must be logged in to access this data'
    ]);
    exit;
}

// Get parameters from request
$campus = isset($_GET['campus']) ? trim($_GET['campus']) : '';
$year = isset($_GET['year']) ? trim($_GET['year']) : '';
$proposal_id = isset($_GET['proposal_id']) ? trim($_GET['proposal_id']) : '';

// Validate parameters
if (empty($campus) || empty($year) || empty($proposal_id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Campus, year, and proposal ID are required'
    ]);
    exit;
}

try {
    // Include database connection file
    require_once 'db_connection.php';
    
    // Get connection
    $conn = getConnection();
    
    // First try to get data from ppas_forms
    $ppas_query = "SELECT * FROM ppas_forms WHERE id = :id";
    $ppas_stmt = $conn->prepare($ppas_query);
    $ppas_stmt->bindParam(':id', $proposal_id);
    $ppas_stmt->execute();
    $ppas_data = $ppas_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if we have PPAS data
    if (!$ppas_data) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No PPAS form found with the given ID'
        ]);
        exit;
    }
    
    // Get activity title from PPAS form
    $activity_title = $ppas_data['activity'] ?? '';
    
    // Try to find corresponding narrative entry using a more flexible match
    // First try direct ID match if ppas_form_id exists in narrative_entries
    $narrative_data = null;
    
    // Check if ppas_form_id column exists in narrative_entries
    try {
        $checkStmt = $conn->prepare("SHOW COLUMNS FROM narrative_entries LIKE 'ppas_form_id'");
        $checkStmt->execute();
        $hasPpasFormId = ($checkStmt->rowCount() > 0);
        
        if ($hasPpasFormId) {
            // First try to match by ppas_form_id
            $id_query = "SELECT * FROM narrative_entries WHERE ppas_form_id = :ppas_id LIMIT 1";
            $id_stmt = $conn->prepare($id_query);
            $id_stmt->bindParam(':ppas_id', $proposal_id);
            $id_stmt->execute();
            $narrative_data = $id_stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        // Silently continue to the next method if this fails
    }
    
    // If no match by ID, try exact title match
    if (!$narrative_data) {
        $narrative_query = "SELECT * FROM narrative_entries WHERE campus = :campus AND year = :year AND title = :title LIMIT 1";
        $narrative_stmt = $conn->prepare($narrative_query);
        $narrative_stmt->bindParam(':campus', $campus);
        $narrative_stmt->bindParam(':year', $year);
        $narrative_stmt->bindParam(':title', $activity_title);
        $narrative_stmt->execute();
        $narrative_data = $narrative_stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // If no exact match, try a more flexible LIKE query
    if (!$narrative_data) {
        $narrative_query = "SELECT * FROM narrative_entries WHERE campus = :campus AND year = :year AND title LIKE :title LIMIT 1";
        $narrative_stmt = $conn->prepare($narrative_query);
        $narrative_stmt->bindParam(':campus', $campus);
        $narrative_stmt->bindParam(':year', $year);
        
        // Use LIKE with % at the beginning and end for more flexible matching
        $titleSearch = '%' . trim($activity_title) . '%';
        $narrative_stmt->bindParam(':title', $titleSearch);
        
        $narrative_stmt->execute();
        $narrative_data = $narrative_stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Reverse LIKE match (if activity is contained within a narrative title)
    if (!$narrative_data) {
        $reverse_query = "SELECT * FROM narrative_entries WHERE 
                         :activity_title LIKE CONCAT('%', title, '%') 
                         AND campus = :campus AND year = :year LIMIT 1";
        $reverse_stmt = $conn->prepare($reverse_query);
        $reverse_stmt->bindParam(':activity_title', $activity_title);
        $reverse_stmt->bindParam(':campus', $campus);
        $reverse_stmt->bindParam(':year', $year);
        $reverse_stmt->execute();
        $narrative_data = $reverse_stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Create a merged data response with consistent field names
    $merged_data = $ppas_data; // Start with PPAS data as base
    
    // Add title for display - from narrative_entries.title
    $merged_data['title'] = $narrative_data['title'] ?? $ppas_data['activity'] ?? $ppas_data['activity_title'] ?? '';
    
    // Map all fields according to fieldcorrction.txt mapping
    
    // Location - from ppas_forms.location
    $merged_data['venue'] = $ppas_data['location'] ?? null;
    
    // Duration fields - from ppas_forms
    $merged_data['start_date'] = $ppas_data['start_date'] ?? null;
    $merged_data['end_date'] = $ppas_data['end_date'] ?? null;
    $merged_data['start_time'] = $ppas_data['start_time'] ?? null;
    $merged_data['end_time'] = $ppas_data['end_time'] ?? null;
    $merged_data['total_duration'] = $ppas_data['total_duration'] ?? null;
    
    // Implementing Office - from ppas_forms.office_college_organization
    $merged_data['implementing_office'] = $ppas_data['office_college_organization'] ?? $narrative_data['implementing_office'] ?? null;
    
    // Partner Agency - from ppas_forms.partner_agencies first, then narrative_entries.partner_agencies
    $merged_data['partner_agencies'] = $ppas_data['partner_agencies'] ?? $narrative_data['partner_agencies'] ?? $ppas_data['partner_agency'] ?? $narrative_data['partner_agency'] ?? null;
    
    // SDG - from ppas_forms.sdg
    $merged_data['sdg'] = $ppas_data['sdg'] ?? $narrative_data['sdg'] ?? null;
    
    // Beneficiaries - from ppas_forms
    $merged_data['external_type'] = $ppas_data['external_type'] ?? null;
    $merged_data['external_male'] = $ppas_data['external_male'] ?? null;
    $merged_data['external_female'] = $ppas_data['external_female'] ?? null;
    $merged_data['external_total'] = $ppas_data['external_total'] ?? null;
    $merged_data['internal_type'] = $ppas_data['internal_type'] ?? 'BatStateU';
    $merged_data['internal_male'] = $ppas_data['internal_male'] ?? $ppas_data['total_internal_male'] ?? null;
    $merged_data['internal_female'] = $ppas_data['internal_female'] ?? $ppas_data['total_internal_female'] ?? null;
    $merged_data['internal_total'] = $ppas_data['internal_total'] ?? $ppas_data['total_internal'] ?? null;
    
    // Personnel data is handled separately through joins
    
    // Tasks - JSON data from ppas_forms
    $merged_data['project_leader_responsibilities'] = $ppas_data['project_leader_responsibilities'] ?? $narrative_data['leader_tasks'] ?? null;
    $merged_data['assistant_project_leader_responsibilities'] = $ppas_data['assistant_project_leader_responsibilities'] ?? $narrative_data['assistant_tasks'] ?? null;
    $merged_data['project_staff_coordinator_responsibilities'] = $ppas_data['project_staff_coordinator_responsibilities'] ?? $narrative_data['staff_tasks'] ?? null;
    
    // Leader tasks (use either source)
    $merged_data['leader_tasks'] = $narrative_data['leader_tasks'] ?? $ppas_data['project_leader_responsibilities'] ?? null;
    $merged_data['assistant_tasks'] = $narrative_data['assistant_tasks'] ?? $ppas_data['assistant_project_leader_responsibilities'] ?? null;
    $merged_data['staff_tasks'] = $narrative_data['staff_tasks'] ?? $ppas_data['project_staff_coordinator_responsibilities'] ?? null;
    
    // Narrative fields - from narrative_entries
    $merged_data['activity_narrative'] = $narrative_data['activity_narrative'] ?? null;
    $merged_data['background'] = $narrative_data['background'] ?? null;
    $merged_data['participants_description'] = $narrative_data['participants'] ?? null;
    $merged_data['narrative_topics'] = $narrative_data['topics'] ?? null;
    $merged_data['expected_results'] = $narrative_data['results'] ?? null;
    $merged_data['lessons_learned'] = $narrative_data['lessons'] ?? null;
    $merged_data['what_worked'] = $narrative_data['what_worked'] ?? null;
    $merged_data['issues_concerns'] = $narrative_data['issues'] ?? null;
    $merged_data['recommendations'] = $narrative_data['recommendations'] ?? null;
    
    // Ratings - from narrative_entries
    $merged_data['activity_ratings'] = $narrative_data['activity_ratings'] ?? null;
    $merged_data['timeliness_ratings'] = $narrative_data['timeliness_ratings'] ?? null;
    
    // Extension service agenda - from narrative_entries or ppas_forms
    $merged_data['extension_service_agenda'] = $narrative_data['extension_service_agenda'] ?? $ppas_data['extension_service_agenda'] ?? null;
    $merged_data['extension_type'] = $narrative_data['extension_type'] ?? $ppas_data['extension_type'] ?? null;
    
    // Financial fields - from both tables
    $merged_data['ps_attribution'] = $narrative_data['ps_attribution'] ?? null;
    $merged_data['source_of_fund'] = $ppas_data['source_of_fund'] ?? null;
    
    // Add any remaining narrative fields that might be used
    if ($narrative_data) {
        foreach ($narrative_data as $key => $value) {
            if (!isset($merged_data[$key]) && $value !== null) {
                $merged_data[$key] = $value;
            }
        }
    }
    
    // Return success response with merged data
    echo json_encode([
        'status' => 'success',
        'data' => $merged_data
    ]);
    
} catch (PDOException $e) {
    // Log the error
    error_log("Error fetching narrative data: " . $e->getMessage());
    
    // Return an error response
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 