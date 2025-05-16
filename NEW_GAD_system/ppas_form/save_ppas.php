<?php
// Prevent PHP errors from being output - must be at the very top
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '../php_errors.log');

// Set content type to JSON
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

try {
    // Include database configuration
    require_once '../config.php';
    
    // Log start of request
    error_log("=== Starting PPAS form save process ===");
    error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
    
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Check database connection
    if (!isset($conn) || $conn->connect_error) {
        error_log("Database connection error: " . ($conn->connect_error ?? 'Connection not established'));
        throw new Exception('Database connection error');
    }
    
    error_log("Database connection successful");
    
    // Start transaction
    $conn->begin_transaction();
    error_log("Transaction started");
    
    // Check if we're updating an existing entry
    $entryId = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
    $isUpdate = ($entryId > 0);
    
    error_log("Operation: " . ($isUpdate ? "UPDATE entry #$entryId" : "INSERT new entry"));
    
    // Extract form data
    $campus = $_POST['campus'] ?? '';
    $year = $_POST['year'] ?? '';
    $quarter = $_POST['quarter'] ?? '';
    $gender_issue_id = $_POST['gender_issue'] ?? '';
    $project = $_POST['project'] ?? '';
    $program = $_POST['program'] ?? '';
    $activity = $_POST['activity'] ?? '';
    $location = $_POST['location'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $total_duration = $_POST['total_duration'] ?? '';
    $lunch_break = $_POST['lunch_break'] ?? '';
    
    // The duplicate activity check is now done on the client side
    // This improves user experience by providing immediate feedback
    // However, we'll also check on the server side as a fallback
    $duplicateCheckSql = "SELECT id FROM ppas_forms WHERE activity = ? AND campus = ? AND year = ? AND quarter = ?";
    
    // If updating, exclude the current entry from duplicate check
    if ($isUpdate) {
        $duplicateCheckSql .= " AND id != ?";
    }
    
    $duplicateStmt = $conn->prepare($duplicateCheckSql);
    
    if ($duplicateStmt === false) {
        throw new Exception("Error preparing duplicate check statement: " . $conn->error);
    }
    
    if ($isUpdate) {
        $duplicateStmt->bind_param("ssssi", $activity, $campus, $year, $quarter, $entryId);
    } else {
        $duplicateStmt->bind_param("ssss", $activity, $campus, $year, $quarter);
    }
    
    if (!$duplicateStmt->execute()) {
        throw new Exception("Error executing duplicate check: " . $duplicateStmt->error);
    }
    
    $duplicateResult = $duplicateStmt->get_result();
    
    if ($duplicateResult->num_rows > 0) {
        $duplicateStmt->close();
        throw new Exception("An activity with the same name already exists for this campus, year, and quarter. Please use a different activity name.");
    }
    
    $duplicateStmt->close();
    
    // Beneficiaries data
    $students_male = intval($_POST['students_male'] ?? 0);
    $students_female = intval($_POST['students_female'] ?? 0);
    $faculty_male = intval($_POST['faculty_male'] ?? 0);
    $faculty_female = intval($_POST['faculty_female'] ?? 0);
    $total_internal_male = intval($_POST['total_internal_male'] ?? 0);
    $total_internal_female = intval($_POST['total_internal_female'] ?? 0);
    $external_type = $_POST['external_type'] ?? '';
    $external_male = intval($_POST['external_male'] ?? 0);
    $external_female = intval($_POST['external_female'] ?? 0);
    $total_male = intval($_POST['total_male'] ?? 0);
    $total_female = intval($_POST['total_female'] ?? 0);
    $total_beneficiaries = intval($_POST['total_beneficiaries'] ?? 0);
    
    // Budget data
    $approved_budget = floatval($_POST['approved_budget'] ?? 0);
    $source_budget = $_POST['source_budget'] ?? '';
    $ps_attribution = floatval($_POST['ps_attribution'] ?? 0);
    
    // SDGs (optional)
    $sdgs = $_POST['sdgs'] ?? '[]';
    
    // Personnel data
    $personnel = $_POST['personnel'] ?? '[]';
    $personnelData = json_decode($personnel, true);
    
    // For debugging
    error_log("DATA TO SAVE: " . print_r($_POST, true));
    
    // Define column names
    $columnNames = [
        'campus', 'year', 'quarter', 'gender_issue_id', 'project', 'program', 'activity',
        'location', 'start_date', 'end_date', 'start_time', 'end_time', 'total_duration_hours',
        'lunch_break', 'students_male', 'students_female', 'faculty_male', 'faculty_female',
        'total_internal_male', 'total_internal_female', 'external_type', 'external_male',
        'external_female', 'total_male', 'total_female', 'total_beneficiaries',
        'approved_budget', 'source_of_budget', 'ps_attribution', 'sdgs'
    ];
    
    // Create parameter array in same order as columns
    $params = [
        $campus, $year, $quarter, $gender_issue_id, $project, $program, $activity,
        $location, $start_date, $end_date, $start_time, $end_time, $total_duration,
        $lunch_break, $students_male, $students_female, $faculty_male, $faculty_female,
        $total_internal_male, $total_internal_female, $external_type, $external_male,
        $external_female, $total_male, $total_female, $total_beneficiaries,
        $approved_budget, $source_budget, $ps_attribution, $sdgs
    ];
    
    // Create types string - must match exactly the number and types of parameters
    $types = 
        'sssssss' .  // campus, year, quarter, gender_issue_id, project, program, activity (7)
        'sssss' .    // location, start_date, end_date, start_time, end_time (5)
        's' .        // total_duration_hours (1)
        's' .        // lunch_break (1)
        'iiiiii' .   // students_male, students_female, faculty_male, faculty_female, total_internal_male, total_internal_female (6)
        'siiii' .    // external_type, external_male, external_female, total_male, total_female (5)
        'i' .        // total_beneficiaries (1)
        'dss' .      // approved_budget, source_of_budget, ps_attribution (3)
        's';         // sdgs (1)
    
    // Prepare and execute SQL based on operation type (INSERT or UPDATE)
    if ($isUpdate) {
        // Build UPDATE statement
        $updateParts = [];
        foreach ($columnNames as $column) {
            $updateParts[] = "$column = ?";
        }
        $sql = "UPDATE ppas_forms SET " . implode(", ", $updateParts) . " WHERE id = ?";
        
        // Add the ID parameter to the end for the WHERE clause
        $params[] = $entryId;
        $types .= 'i';
        
        error_log("UPDATE SQL: $sql");
    } else {
        // Build INSERT statement
        $placeholders = array_fill(0, count($columnNames), "?");
        $sql = "INSERT INTO ppas_forms (" . implode(", ", $columnNames) . ") VALUES (" . 
               implode(", ", $placeholders) . ")";
        
        error_log("INSERT SQL: $sql");
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        error_log("Prepare statement error: " . $conn->error);
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    // Bind parameters
    $bind_params = array_merge([$types], $params);
    $stmt->bind_param(...$bind_params);
    
    if (!$stmt->execute()) {
        error_log("Execute error: " . $stmt->error);
        throw new Exception("Error executing statement: " . $stmt->error);
    }
    
    error_log("SQL statement executed successfully");
    
    // Get the ID of the form we're working with
    $ppas_form_id = $isUpdate ? $entryId : $conn->insert_id;
    error_log("PPAS form ID: " . $ppas_form_id . " (" . ($isUpdate ? "updated" : "inserted") . ")");
    
    // For updates, first delete existing personnel records
    if ($isUpdate) {
        $deletePersonnelSql = "DELETE FROM ppas_personnel WHERE ppas_form_id = ?";
        $deleteStmt = $conn->prepare($deletePersonnelSql);
        
        if ($deleteStmt === false) {
            error_log("Error preparing delete personnel statement: " . $conn->error);
            throw new Exception("Error preparing delete personnel statement: " . $conn->error);
        }
        
        $deleteStmt->bind_param("i", $ppas_form_id);
        
        if (!$deleteStmt->execute()) {
            error_log("Error executing delete personnel statement: " . $deleteStmt->error);
            throw new Exception("Error executing delete personnel statement: " . $deleteStmt->error);
        }
        
        $deleteStmt->close();
        error_log("Existing personnel records deleted for PPAS form ID: $ppas_form_id");
    }
    
    // Insert personnel records if any
    if (!empty($personnelData)) {
        error_log("Processing " . count($personnelData) . " personnel records");
        $personnelSql = "INSERT INTO ppas_personnel (ppas_form_id, personnel_id, role) VALUES (?, ?, ?)";
        $personnelStmt = $conn->prepare($personnelSql);
        
        if ($personnelStmt === false) {
            error_log("Error preparing personnel statement: " . $conn->error);
            throw new Exception("Error preparing personnel statement: " . $conn->error);
        }
        
        foreach ($personnelData as $person) {
            error_log("Processing personnel: " . print_r($person, true));
            $personnelStmt->bind_param("iis", $ppas_form_id, $person['personnel_id'], $person['role']);
            
            if (!$personnelStmt->execute()) {
                error_log("Error executing personnel statement: " . $personnelStmt->error);
                throw new Exception("Error executing personnel statement: " . $personnelStmt->error);
            }
        }
        
        $personnelStmt->close();
        error_log("Personnel records inserted successfully");
    } else {
        error_log("No personnel records to insert");
    }
    
    // Commit transaction
    $conn->commit();
    error_log("Transaction committed");
    
    // Set success response
    $response['success'] = true;
    $response['message'] = 'PPAS form ' . ($isUpdate ? 'updated' : 'saved') . ' successfully';
    $response['ppas_form_id'] = $ppas_form_id;
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
        error_log("Transaction rolled back due to error");
    }
    
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Error in save_ppas.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
} finally {
    // Close database connection
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
    
    if (isset($conn) && $conn->ping()) {
        $conn->close();
        error_log("Database connection closed");
    }
    
    error_log("=== PPAS form save process complete ===");
}

// Return JSON response
error_log("Sending JSON response: " . json_encode($response));
echo json_encode($response); 