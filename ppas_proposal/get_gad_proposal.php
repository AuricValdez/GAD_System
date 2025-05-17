<?php
// Start session
session_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_errors.log');

// Log the request
error_log("GET GAD PROPOSAL: Request started");
error_log("SESSION: " . print_r($_SESSION, true));
error_log("GET params: " . print_r($_GET, true));

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("GET GAD PROPOSAL: User not logged in");
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    error_log("GET GAD PROPOSAL: Missing proposal ID");
    echo json_encode(['success' => false, 'message' => 'Proposal ID is required']);
    exit();
}

try {
    // Include database configuration
    require_once '../config.php';
    
    $proposalId = $_GET['id'];
    $userCampus = $_SESSION['username'];
    $isCentral = ($userCampus === 'Central');
    
    error_log("GET GAD PROPOSAL: Retrieving proposal ID: $proposalId for user: $userCampus");
    
    // First, verify the table structure to get the correct ID field name
    $tableInfo = $conn->query("SHOW COLUMNS FROM gad_proposals");
    $columns = [];
    $idField = 'id'; // Default ID field name
    
    while ($column = $tableInfo->fetch_assoc()) {
        $columns[] = $column['Field'];
        // Look for the primary key or a field containing 'id' in its name
        if ($column['Key'] === 'PRI' || strtolower($column['Field']) === 'proposal_id' || 
            strtolower($column['Field']) === 'id') {
            $idField = $column['Field'];
        }
    }
    
    error_log("GET GAD PROPOSAL: Using ID field: $idField");
    
    // Build the query to get the proposal data
    // First check if the user has permission to access this proposal
    $checkSql = "SELECT * FROM gad_proposals WHERE $idField = ?";
    
    $stmt = $conn->prepare($checkSql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param('i', $proposalId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("GET GAD PROPOSAL: Proposal not found with ID: $proposalId");
        echo json_encode(['success' => false, 'message' => 'Proposal not found']);
        $stmt->close();
        exit();
    }
    
    $proposal = $result->fetch_assoc();
    $proposalCampus = $proposal['campus'];
    
    // Check if user has permission (their campus matches the proposal's campus)
    if (!$isCentral && $userCampus !== $proposalCampus) {
        error_log("GET GAD PROPOSAL: Permission denied for user $userCampus to access proposal from campus $proposalCampus");
        echo json_encode(['success' => false, 'message' => 'You do not have permission to access this proposal']);
        $stmt->close();
        exit();
    }
    
    // Convert array fields from JSON to arrays
    $arrayFields = [
        'project_leader_responsibilities',
        'assistant_leader_responsibilities',
        'staff_responsibilities',
        'specific_objectives',
        'strategies',
        'methods',
        'materials',
        'workplan',
        'monitoring_items',
        'specific_plans'
    ];
    
    foreach ($arrayFields as $field) {
        if (isset($proposal[$field]) && !empty($proposal[$field])) {
            $proposal[$field] = json_decode($proposal[$field], true);
        } else {
            $proposal[$field] = [];
        }
    }
    
    // Check if we need to fetch additional information from ppas_forms and ppas_personnel
    if (isset($proposal['ppas_form_id']) && !empty($proposal['ppas_form_id'])) {
        // Get form details with specific focus on year and quarter
        $formSql = "SELECT f.*, 
                    f.year, 
                    f.quarter, 
                    f.activity 
                    FROM ppas_forms f WHERE f.id = ?";
        $formStmt = $conn->prepare($formSql);
        $formStmt->bind_param('i', $proposal['ppas_form_id']);
        $formStmt->execute();
        $formResult = $formStmt->get_result();
        
        if ($formResult->num_rows > 0) {
            $formData = $formResult->fetch_assoc();
            
            // Extract and add year, quarter, activity to main proposal data
            if (isset($formData['year'])) {
                $proposal['year'] = $formData['year'];
            }
            
            if (isset($formData['quarter'])) {
                $proposal['quarter'] = $formData['quarter'];
            }
            
            if (isset($formData['activity_title'])) {
                $proposal['activity_title'] = $formData['activity_title'];
            }
            
            // Store the full form data
            $proposal['form_data'] = $formData;
        }
        
        $formStmt->close();
        
        // Log the fields we're extracting
        error_log("GET GAD PROPOSAL: Extracted year: " . ($proposal['year'] ?? 'not found') . 
                ", quarter: " . ($proposal['quarter'] ?? 'not found') . 
                ", activity: " . ($proposal['activity_title'] ?? 'not found'));
        
        // Get personnel
        $personnelSql = "SELECT p.*, pp.role 
                        FROM ppas_personnel pp 
                        JOIN personnel p ON pp.personnel_id = p.id 
                        WHERE pp.ppas_form_id = ?";
        $personnelStmt = $conn->prepare($personnelSql);
        $personnelStmt->bind_param('i', $proposal['ppas_form_id']);
        $personnelStmt->execute();
        $personnelResult = $personnelStmt->get_result();
        
        $proposal['personnel'] = [];
        while ($person = $personnelResult->fetch_assoc()) {
            $proposal['personnel'][] = $person;
        }
        
        $personnelStmt->close();
    }
    
    error_log("GET GAD PROPOSAL: Successfully retrieved proposal data");
    echo json_encode([
        'success' => true, 
        'data' => $proposal, 
        'message' => 'Proposal retrieved successfully'
    ]);
    
    // Close statement
    $stmt->close();

} catch (Exception $e) {
    error_log("GET GAD PROPOSAL ERROR: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    // Close connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
    error_log("GET GAD PROPOSAL: Request completed");
}
?> 