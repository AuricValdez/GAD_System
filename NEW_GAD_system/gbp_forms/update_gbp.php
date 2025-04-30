<?php
// Turn off direct PHP error output to prevent HTML in JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

// Set up error handling for the entire script
function handleFatalErrors() {
    $error = error_get_last();
    if ($error !== null && $error['type'] === E_ERROR) {
        echo json_encode([
            'success' => false,
            'message' => 'Fatal error: ' . $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
        exit();
    }
}
register_shutdown_function('handleFatalErrors');

try {
    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }

    // Check if data is submitted
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit();
    }

    // Check if entry id is provided
    if (!isset($_POST['entry_id']) || empty($_POST['entry_id'])) {
        echo json_encode(['success' => false, 'message' => 'Entry ID is required']);
        exit();
    }

    // Get entry ID
    $entryId = intval($_POST['entry_id']);

    // Include database configuration
    require_once '../config.php';

    // Prepare program and activity data in the correct format
    $programData = [];
    
    // Process the programs and activities data
    foreach ($_POST as $key => $value) {
        if (preg_match('/^program_(\d+)$/', $key, $matches)) {
            $programId = $matches[1];
            $programData[$programId] = [
                'name' => $value,
                'activities' => []
            ];
        } elseif (preg_match('/^activity_(\d+)_(\d+)$/', $key, $matches)) {
            $programId = $matches[1];
            $activityId = $matches[2];
            
            if (!isset($programData[$programId])) {
                $programData[$programId] = [
                    'name' => '',
                    'activities' => []
                ];
            }
            
            $programData[$programId]['activities'][$activityId] = $value;
        }
    }
    
    // Extract program names for generic_activity
    $programNames = array_map(function($program) {
        return $program['name'];
    }, $programData);
    
    // Convert to JSON for storage
    $generic_activity = json_encode(array_values($programNames));
    
    // Extract activities for specific_activities
    $specificActivities = array_map(function($program) {
        return array_values($program['activities']);
    }, $programData);
    
    // Convert to JSON for storage
    $specific_activities = json_encode(array_values($specificActivities));
    
    // Calculate total number of activities
    $totalActivities = 0;
    foreach ($programData as $program) {
        foreach ($program['activities'] as $activity) {
            if (!empty($activity)) {
                $totalActivities++;
            }
        }
    }
    
    // Update the entry in gpb_entries table
    $sql = "UPDATE gpb_entries SET 
            year = ?, 
            gender_issue = ?, 
            category = ?, 
            cause_of_issue = ?, 
            gad_objective = ?, 
            relevant_agency = ?,
            generic_activity = ?,
            specific_activities = ?,
            total_activities = ?,
            gad_budget = ?, 
            source_of_budget = ?, 
            responsible_unit = ?, 
            male_participants = ?, 
            female_participants = ?,
            total_participants = ?,
            status = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }
    
    $totalParticipants = intval($_POST['male_participants']) + intval($_POST['female_participants']);
    $status = 'Pending'; // Set status to "Pending" with single quotes
    
    // Debug statement to check variable value before binding
    error_log("Status value before binding: " . $status);
    
    $stmt->bind_param(
        'ssssssssidssiiiis', 
        $_POST['year'],
        $_POST['gender_issue'],
        $_POST['category'],
        $_POST['cause_of_issue'],
        $_POST['gad_objective'],
        $_POST['relevant_agency'],
        $generic_activity,
        $specific_activities,
        $totalActivities,
        $_POST['gad_budget'],
        $_POST['source_of_budget'],
        $_POST['responsible_unit'],
        $_POST['male_participants'],
        $_POST['female_participants'],
        $totalParticipants,
        $status,
        $entryId
    );
    
    $result = $stmt->execute();
    if (!$result) {
        throw new Exception("Database execute error: " . $stmt->error);
    }
    
    $stmt->close();
    
    // Additional direct update for status to ensure it's correctly set
    try {
        $statusUpdateSql = "UPDATE gpb_entries SET status = 'Pending' WHERE id = ?";
        $statusStmt = $conn->prepare($statusUpdateSql);
        if (!$statusStmt) {
            error_log("Failed to prepare status update: " . $conn->error);
        } else {
            $statusStmt->bind_param('i', $entryId);
            $statusResult = $statusStmt->execute();
            if (!$statusResult) {
                error_log("Failed to execute status update: " . $statusStmt->error);
            } else {
                error_log("Status update successful for entry ID: " . $entryId);
            }
            $statusStmt->close();
        }
    } catch (Exception $e) {
        error_log("Exception in status update: " . $e->getMessage());
    }
    
    echo json_encode(['success' => true, 'message' => 'GBP entry updated successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
} catch (Error $e) {
    echo json_encode(['success' => false, 'message' => 'PHP Error: ' . $e->getMessage()]);
    exit();
} 