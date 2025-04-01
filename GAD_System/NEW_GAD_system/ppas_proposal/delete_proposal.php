<?php
session_start();
// Enable debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Set content type to application/json
header('Content-Type: application/json');

// Create debug log
error_log("DELETE PROPOSAL REQUEST: " . date('Y-m-d H:i:s'));
error_log("REQUEST METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("RAW INPUT: " . file_get_contents('php://input'));

try {
    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        throw new Exception("User not authenticated");
    }
    
    // Check if DB connection file exists
    if (!file_exists('../includes/db_connection.php')) {
        throw new Exception("Database connection file not found");
    }
    
    // Include database connection
    require_once '../includes/db_connection.php';
    
    // Parse input data - handle both POST and JSON input
    $inputData = [];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle form data
        if (isset($_POST['id'])) {
            $inputData['id'] = $_POST['id'];
        } 
        // Handle JSON data in request body
        else {
            $jsonInput = file_get_contents('php://input');
            if (!empty($jsonInput)) {
                $inputData = json_decode($jsonInput, true);
            }
        }
    }
    
    // Validate proposal ID
    if (!isset($inputData['id']) || empty($inputData['id'])) {
        throw new Exception("No proposal ID provided");
    }
    
    $proposalId = intval($inputData['id']);
    error_log("Attempting to delete proposal with ID: " . $proposalId);
    
    // Get database connection
    $conn = getConnection();
    
    // Start transaction
    $conn->beginTransaction();
    
    // Check if proposal exists before deleting
    $checkSql = "SELECT id FROM gad_proposals WHERE id = :id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':id', $proposalId, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        throw new Exception("Proposal not found with ID: " . $proposalId);
    }
    
    // Delete proposal - all related records will be deleted by cascade
    $sql = "DELETE FROM gad_proposals WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $proposalId, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Failed to delete proposal. Database error.");
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Proposal deleted successfully'
    ]);
    
} catch (Exception $e) {
    // If a transaction is active, roll it back
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Log error
    error_log("Error in delete_proposal.php: " . $e->getMessage());
    
    // Return error message
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 