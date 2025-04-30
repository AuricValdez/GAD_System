<?php
require_once '../config.php';

// Turn off error display and log errors instead
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');

// Ensure no output before headers
ob_start();

session_start();
header('Content-Type: application/json');

// Function to return JSON error response
function returnError($message) {
    ob_clean(); // Clear any previous output
    error_log("Delete Personnel Error: " . $message); // Log the error
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    returnError('Unauthorized access');
}

// Get and validate JSON input
$jsonInput = file_get_contents('php://input');
if (empty($jsonInput)) {
    returnError('No input data received');
}

$input = json_decode($jsonInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    returnError('Invalid JSON input: ' . json_last_error_msg());
}

if (!isset($input['id'])) {
    returnError('No personnel ID provided');
}

if (!is_numeric($input['id'])) {
    returnError('Invalid personnel ID format');
}

$personnel_id = intval($input['id']);

try {
    // Use the existing PDO connection from config.php
    if (!isset($pdo)) {
        returnError('Database connection not available');
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Check if personnel exists before deleting
    $check_stmt = $pdo->prepare("SELECT id FROM personnel WHERE id = ?");
    if (!$check_stmt) {
        returnError('Could not prepare check statement');
    }
    
    $check_stmt->execute([$personnel_id]);
    if ($check_stmt->rowCount() === 0) {
        $pdo->rollBack();
        returnError('Personnel not found with ID: ' . $personnel_id);
    }
    
    // Delete from personnel table
    $stmt = $pdo->prepare("DELETE FROM personnel WHERE id = ?");
    if (!$stmt) {
        $pdo->rollBack();
        returnError('Could not prepare delete statement');
    }
    
    if ($stmt->execute([$personnel_id])) {
        // Check if any rows were affected
        if ($stmt->rowCount() === 0) {
            $pdo->rollBack();
            returnError('No personnel was deleted');
        }
        
        // Commit transaction
        if (!$pdo->commit()) {
            $pdo->rollBack();
            returnError('Could not commit transaction');
        }
        
        ob_clean(); // Clear any previous output
        echo json_encode([
            'success' => true, 
            'message' => 'Personnel deleted successfully',
            'personnel_id' => $personnel_id
        ]);
    } else {
        // Rollback on failure
        $pdo->rollBack();
        returnError('Failed to delete personnel');
    }

} catch (PDOException $e) {
    error_log("Delete Personnel Exception: " . $e->getMessage());
    if (isset($pdo)) {
        try {
            $pdo->rollBack();
        } catch (PDOException $rollbackError) {
            error_log("Error during rollback: " . $rollbackError->getMessage());
        }
    }
    returnError('Database error: ' . $e->getMessage());
}
?> 