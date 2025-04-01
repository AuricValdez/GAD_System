<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Set content type to JSON
header('Content-Type: application/json');

// Include database connection
include_once '../includes/db_connection.php';

// Log access for debugging
file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - User: " . $_SESSION['username'] . " accessed save_narrative.php\n", FILE_APPEND);

// Check if required fields are provided
if (!isset($_POST['ppas_id']) || empty($_POST['ppas_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'PPAS ID is required'
    ]);
    exit();
}

// Get username
$username = $_SESSION['username'];

try {
    // Check if narrative_forms table exists
    $tableCheckQuery = "SHOW TABLES LIKE 'narrative_forms'";
    $tableCheckStmt = $conn->prepare($tableCheckQuery);
    $tableCheckStmt->execute();
    
    if ($tableCheckStmt->rowCount() == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'The narrative_forms table does not exist. Please run the setup script.'
        ]);
        exit();
    }
    
    // Process form data
    $narrativeId = isset($_POST['narrative_id']) && !empty($_POST['narrative_id']) ? $_POST['narrative_id'] : null;
    $ppasId = $_POST['ppas_id'];
    $implementingOffice = $_POST['implementing_office'] ?? '';
    $partnerAgency = $_POST['partner_agency'] ?? '';
    
    // Process multi-select checkboxes
    $serviceAgenda = isset($_POST['service_agenda']) ? json_encode($_POST['service_agenda']) : '[]';
    $sdg = isset($_POST['sdg']) ? json_encode($_POST['sdg']) : '[]';
    
    // Process beneficiaries data
    $beneficiaries = [];
    if (isset($_POST['participant_type']) && is_array($_POST['participant_type'])) {
        for ($i = 0; $i < count($_POST['participant_type']); $i++) {
            if (!empty($_POST['participant_type'][$i])) {
                $beneficiaries[] = [
                    'type' => $_POST['participant_type'][$i],
                    'internal_male' => $_POST['internal_male'][$i] ?? 0,
                    'internal_female' => $_POST['internal_female'][$i] ?? 0,
                    'external_male' => $_POST['external_male'][$i] ?? 0,
                    'external_female' => $_POST['external_female'][$i] ?? 0
                ];
            }
        }
    }
    $beneficiariesJson = json_encode($beneficiaries);
    
    // Process task data
    $tasks = [];
    if (isset($_POST['task_name']) && is_array($_POST['task_name'])) {
        for ($i = 0; $i < count($_POST['task_name']); $i++) {
            if (!empty($_POST['task_name'][$i]) && !empty($_POST['task_description'][$i])) {
                $tasks[] = [
                    'name' => $_POST['task_name'][$i],
                    'task' => $_POST['task_description'][$i]
                ];
            }
        }
    }
    $tasksJson = json_encode($tasks);
    
    // Get other form fields
    $generalObjective = $_POST['general_objective'] ?? '';
    $specificObjective = $_POST['specific_objective'] ?? '';
    $activityTitle = $_POST['activity_title'] ?? '';
    $activityNarrative = $_POST['activity_narrative'] ?? '';
    $evaluationResult = $_POST['evaluation_result'] ?? '';
    $surveyResult = $_POST['survey_result'] ?? '';
    
    // TODO: Handle photo uploads
    // This is a placeholder for photo processing
    $photos = []; // Will store photo URLs
    
    // Convert to JSON for storage
    $photosJson = json_encode($photos);
    
    // Check if narrative already exists
    if ($narrativeId) {
        // Update existing narrative
        $query = "UPDATE narrative_forms SET 
            ppas_id = :ppas_id,
            implementing_office = :implementing_office,
            partner_agency = :partner_agency,
            service_agenda = :service_agenda,
            sdg = :sdg,
            beneficiaries = :beneficiaries,
            tasks = :tasks,
            general_objective = :general_objective,
            specific_objective = :specific_objective,
            activity_title = :activity_title,
            activity_narrative = :activity_narrative,
            evaluation_result = :evaluation_result,
            survey_result = :survey_result,
            photos = :photos,
            updated_at = NOW()
            WHERE id = :id AND username = :username";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $narrativeId);
    } else {
        // Insert new narrative
        $query = "INSERT INTO narrative_forms (
            ppas_id, username, implementing_office, partner_agency, 
            service_agenda, sdg, beneficiaries, tasks,
            general_objective, specific_objective, activity_title, 
            activity_narrative, evaluation_result, survey_result, photos,
            created_at, updated_at
        ) VALUES (
            :ppas_id, :username, :implementing_office, :partner_agency,
            :service_agenda, :sdg, :beneficiaries, :tasks,
            :general_objective, :specific_objective, :activity_title,
            :activity_narrative, :evaluation_result, :survey_result, :photos,
            NOW(), NOW()
        )";
        
        $stmt = $conn->prepare($query);
    }
    
    // Bind parameters
    $stmt->bindParam(':ppas_id', $ppasId);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':implementing_office', $implementingOffice);
    $stmt->bindParam(':partner_agency', $partnerAgency);
    $stmt->bindParam(':service_agenda', $serviceAgenda);
    $stmt->bindParam(':sdg', $sdg);
    $stmt->bindParam(':beneficiaries', $beneficiariesJson);
    $stmt->bindParam(':tasks', $tasksJson);
    $stmt->bindParam(':general_objective', $generalObjective);
    $stmt->bindParam(':specific_objective', $specificObjective);
    $stmt->bindParam(':activity_title', $activityTitle);
    $stmt->bindParam(':activity_narrative', $activityNarrative);
    $stmt->bindParam(':evaluation_result', $evaluationResult);
    $stmt->bindParam(':survey_result', $surveyResult);
    $stmt->bindParam(':photos', $photosJson);
    
    $stmt->execute();
    
    if (!$narrativeId) {
        $narrativeId = $conn->lastInsertId();
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Narrative form saved successfully',
        'id' => $narrativeId
    ]);
    
} catch (Exception $e) {
    // Log error
    file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - Error in save_narrative.php: " . $e->getMessage() . "\n", FILE_APPEND);
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// Close connection
$conn = null;
?> 