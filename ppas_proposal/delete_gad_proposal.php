<?php
// Start session
session_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_errors.log');

// Always set the content type to JSON
header('Content-Type: application/json');

// Log the request
error_log("DELETE GAD PROPOSAL: Request started");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("DELETE GAD PROPOSAL: User not logged in");
    echo json_encode(['success' => false, 'message' => 'You must be logged in to delete a proposal.']);
    exit();
}

try {
    // Include database configuration
    require_once '../config.php';
    
    // Get the raw POST data and decode it
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Log the data received
    error_log("DELETE GAD PROPOSAL: Data received: " . print_r($data, true));
    
    // Check if ID is provided
    if (!isset($data['id']) || empty($data['id'])) {
        error_log("DELETE GAD PROPOSAL: No ID provided");
        echo json_encode(['success' => false, 'message' => 'Proposal ID is required.']);
        exit();
    }
    
    $proposalId = $data['id'];
    $userCampus = $_SESSION['username'];
    $isCentral = ($userCampus === 'Central');
    
    error_log("DELETE GAD PROPOSAL: Processing delete for ID $proposalId by user $userCampus");
    
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
    
    error_log("DELETE GAD PROPOSAL: Using ID field: $idField");
    
    // First, check if the user has permission to delete this proposal
    // (Users can only delete proposals from their own campus, Central can delete from any)
    $checkSql = "SELECT campus FROM gad_proposals WHERE $idField = ?";
    $checkStmt = $conn->prepare($checkSql);
    
    if (!$checkStmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    $checkStmt->bind_param('i', $proposalId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("DELETE GAD PROPOSAL: Proposal not found with ID: $proposalId");
        echo json_encode(['success' => false, 'message' => 'Proposal not found.']);
        $checkStmt->close();
        exit();
    }
    
    $row = $result->fetch_assoc();
    $proposalCampus = $row['campus'];
    
    // Check if user has permission (their campus matches the proposal's campus)
    if (!$isCentral && $userCampus !== $proposalCampus) {
        error_log("DELETE GAD PROPOSAL: Permission denied for user $userCampus to delete proposal from campus $proposalCampus");
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this proposal.']);
        $checkStmt->close();
        exit();
    }
    
    // Now perform the delete operation
    $deleteSql = "DELETE FROM gad_proposals WHERE $idField = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    
    if (!$deleteStmt) {
        throw new Exception("Error preparing delete statement: " . $conn->error);
    }
    
    $deleteStmt->bind_param('i', $proposalId);
    
    if ($deleteStmt->execute()) {
        error_log("DELETE GAD PROPOSAL: Successfully deleted proposal with ID: $proposalId");
        echo json_encode(['success' => true, 'message' => 'Proposal deleted successfully.']);
    } else {
        throw new Exception("Failed to delete proposal: " . $deleteStmt->error);
    }
    
    // Close statements
    $checkStmt->close();
    $deleteStmt->close();

} catch (Exception $e) {
    error_log("DELETE GAD PROPOSAL ERROR: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    // Close connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
    error_log("DELETE GAD PROPOSAL: Request completed");
}
exit();
?> 