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
file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - User: " . $_SESSION['username'] . " accessed delete_narrative.php\n", FILE_APPEND);

// Check if ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Narrative ID is required'
    ]);
    exit();
}

$narrativeId = $_POST['id'];
$username = $_SESSION['username'];

try {
    // Check if narrative_forms table exists
    $tableCheckQuery = "SHOW TABLES LIKE 'narrative_forms'";
    $tableCheckStmt = $conn->prepare($tableCheckQuery);
    $tableCheckStmt->execute();
    
    if ($tableCheckStmt->rowCount() == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'The narrative_forms table does not exist'
        ]);
        exit();
    }
    
    // Check if narrative exists and belongs to the user
    $checkQuery = "SELECT id FROM narrative_forms WHERE id = :id AND username = :username";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':id', $narrativeId);
    $checkStmt->bindParam(':username', $username);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Narrative not found or you do not have permission to delete it'
        ]);
        exit();
    }
    
    // Delete narrative
    $deleteQuery = "DELETE FROM narrative_forms WHERE id = :id AND username = :username";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bindParam(':id', $narrativeId);
    $deleteStmt->bindParam(':username', $username);
    $deleteStmt->execute();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Narrative form deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Log error
    file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - Error in delete_narrative.php: " . $e->getMessage() . "\n", FILE_APPEND);
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// Close connection
$conn = null;
?> 