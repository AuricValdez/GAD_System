<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Include database configuration
require_once '../config.php';

// Check if all required fields are present
$requiredFields = [
    'year', 'campus', 'gender_issue', 'cause_of_issue', 'gad_objective',
    'relevant_agency', 'category', 'generic_activity', 'specific_activities',
    'total_activities', 'male_participants', 'female_participants',
    'total_participants', 'gad_budget', 'source_of_budget', 'responsible_unit'
];

foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

try {
    // Prepare program and activity data in the correct format
    $programData = [];
    $activityData = [];
    
    // Process the programs and activities data
    $genericActivityObj = json_decode($_POST['generic_activity'], true);
    
    if (!$genericActivityObj) {
        throw new Exception("Failed to parse program and activity data");
    }
    
    // Keep the generic_activity as JSON to preserve the array of program names
    $generic_activity = $_POST['generic_activity'];
    
    // Use the specific_activities directly from the POST data
    $specific_activities = $_POST['specific_activities'];
    
    // Prepare the SQL statement
    $sql = "INSERT INTO gpb_entries (
        year, campus, gender_issue, cause_of_issue, gad_objective,
        relevant_agency, category, generic_activity, specific_activities,
        total_activities, male_participants, female_participants,
        total_participants, gad_budget, source_of_budget, responsible_unit,
        status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // Set status to "Pending"
    $status = 'Pending';
    
    // Debug statement to check variable value before binding
    error_log("Status value before binding when saving: " . $status);
    
    // Bind parameters
    $stmt->bind_param(
        "sssssssssiiiidsss",
        $_POST['year'],
        $_POST['campus'],
        $_POST['gender_issue'],
        $_POST['cause_of_issue'],
        $_POST['gad_objective'],
        $_POST['relevant_agency'],
        $_POST['category'],
        $generic_activity,
        $specific_activities,
        $_POST['total_activities'],
        $_POST['male_participants'],
        $_POST['female_participants'],
        $_POST['total_participants'],
        $_POST['gad_budget'],
        $_POST['source_of_budget'],
        $_POST['responsible_unit'],
        $status
    );
    
    // Execute the statement
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Get the ID of the newly inserted record
    $newEntryId = $stmt->insert_id;
    
    $stmt->close();
    
    // Additional direct update for status to ensure it's correctly set
    if ($newEntryId) {
        try {
            $statusUpdateSql = "UPDATE gpb_entries SET status = 'Pending' WHERE id = ?";
            $statusStmt = $conn->prepare($statusUpdateSql);
            if (!$statusStmt) {
                error_log("Failed to prepare status update after insert: " . $conn->error);
            } else {
                $statusStmt->bind_param('i', $newEntryId);
                $statusResult = $statusStmt->execute();
                if (!$statusResult) {
                    error_log("Failed to execute status update after insert: " . $statusStmt->error);
                } else {
                    error_log("Status update successful for new entry ID: " . $newEntryId);
                }
                $statusStmt->close();
            }
        } catch (Exception $e) {
            error_log("Exception in status update after insert: " . $e->getMessage());
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'GBP entry saved successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
} 