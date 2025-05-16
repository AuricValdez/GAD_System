<?php
session_start();
// Enable debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Log access time for debugging
error_log('Accessing get_gad_proposal.php at ' . date('Y-m-d H:i:s'));

// Set the content type to JSON
header('Content-Type: application/json');

// Check if DB connection file exists
if (!file_exists('../includes/db_connection.php')) {
    error_log('db_connection.php not found');
    echo json_encode(['error' => 'Database connection file not found']);
    exit;
}

// Include database connection
require_once '../includes/db_connection.php';
error_log('db_connection.php loaded successfully');

// Validate input parameters
if (!isset($_GET['id']) || empty($_GET['id'])) {
    error_log('No ID parameter provided');
    echo json_encode(['error' => 'No proposal ID provided']);
    exit;
}

$id = intval($_GET['id']);
error_log('Fetching proposal with ID: ' . $id);

try {
    // Get database connection
    $conn = getConnection();
    
    // Get proposal data
    $sql = "SELECT * FROM gad_proposals WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $proposal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$proposal) {
        error_log('No proposal found with ID: ' . $id);
        echo json_encode(['error' => 'Proposal not found']);
        exit;
    }
    
    // Get activities
    $sqlActivities = "SELECT * FROM gad_proposal_activities WHERE proposal_id = :proposal_id";
    $stmtActivities = $conn->prepare($sqlActivities);
    $stmtActivities->bindParam(':proposal_id', $id, PDO::PARAM_INT);
    $stmtActivities->execute();
    $activities = $stmtActivities->fetchAll(PDO::FETCH_ASSOC);
    
    // Get personnel - remove campus field
    $sqlPersonnel = "SELECT p.*, pl.name as personnel_name, pl.category, pl.status, pl.gender, pl.academic_rank
                    FROM gad_proposal_personnel p
                    LEFT JOIN personnel pl ON p.personnel_id = pl.id
                    WHERE p.proposal_id = :proposal_id";
    $stmtPersonnel = $conn->prepare($sqlPersonnel);
    $stmtPersonnel->bindParam(':proposal_id', $id, PDO::PARAM_INT);
    $stmtPersonnel->execute();
    $personnel = $stmtPersonnel->fetchAll(PDO::FETCH_ASSOC);
    
    // Get monitoring
    $sqlMonitoring = "SELECT * FROM gad_proposal_monitoring WHERE proposal_id = :proposal_id";
    $stmtMonitoring = $conn->prepare($sqlMonitoring);
    $stmtMonitoring->bindParam(':proposal_id', $id, PDO::PARAM_INT);
    $stmtMonitoring->execute();
    $monitoring = $stmtMonitoring->fetchAll(PDO::FETCH_ASSOC);
    
    // Get workplan
    $sqlWorkplan = "SELECT * FROM gad_proposal_workplan WHERE proposal_id = :proposal_id";
    $stmtWorkplan = $conn->prepare($sqlWorkplan);
    $stmtWorkplan->bindParam(':proposal_id', $id, PDO::PARAM_INT);
    $stmtWorkplan->execute();
    $workplan = $stmtWorkplan->fetchAll(PDO::FETCH_ASSOC);
    
    // Combine all data
    $response = [
        'success' => true,
        'proposal' => $proposal,
        'activities' => $activities,
        'personnel' => $personnel,
        'monitoring' => $monitoring,
        'workplan' => $workplan
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Error in get_gad_proposal.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} finally {
    if (isset($conn)) {
        $conn = null;
    }
    error_log("Request processing completed");
} 