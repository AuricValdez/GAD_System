<?php
// Prevent PHP errors from being output - must be at the top
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '../php_errors.log');

// Start session to get user info
session_start();

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
    
    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        throw new Exception('User not logged in');
    }
    
    // Get current user's campus
    $userCampus = $_SESSION['username'];
    $isCentral = ($userCampus === 'Central');
    
    // Only allow Central users or campus owners to delete entries
    
    // Get JSON data from request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Get the entry ID from the request
    $entryId = isset($data['id']) ? intval($data['id']) : 0;
    
    if ($entryId <= 0) {
        throw new Exception('Invalid entry ID');
    }
    
    // First, check if the entry exists and belongs to the user's campus (if not Central)
    $checkSql = "SELECT campus FROM ppas_forms WHERE id = ?";
    $checkParams = [$entryId];
    $checkTypes = 'i';
    
    $checkStmt = $conn->prepare($checkSql);
    
    if ($checkStmt === false) {
        throw new Exception("Error preparing check statement: " . $conn->error);
    }
    
    $checkStmt->bind_param($checkTypes, ...$checkParams);
    
    if (!$checkStmt->execute()) {
        throw new Exception("Error executing check statement: " . $checkStmt->error);
    }
    
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        throw new Exception("Entry not found");
    }
    
    $entryCampus = $checkResult->fetch_assoc()['campus'];
    
    // Non-Central users can only delete their own campus entries
    if (!$isCentral && $entryCampus !== $userCampus) {
        throw new Exception("You do not have permission to delete this entry");
    }
    
    $checkStmt->close();
    
    // If we get here, the user has permission to delete the entry
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // First delete related personnel records
        $deletePersonnelSql = "DELETE FROM ppas_personnel WHERE ppas_form_id = ?";
        $deletePersonnelStmt = $conn->prepare($deletePersonnelSql);
        
        if ($deletePersonnelStmt === false) {
            throw new Exception("Error preparing delete personnel statement: " . $conn->error);
        }
        
        $deletePersonnelStmt->bind_param('i', $entryId);
        
        if (!$deletePersonnelStmt->execute()) {
            throw new Exception("Error executing delete personnel statement: " . $deletePersonnelStmt->error);
        }
        
        $personnelDeleted = $deletePersonnelStmt->affected_rows;
        error_log("Deleted {$personnelDeleted} personnel records for PPAS form ID: {$entryId}");
        
        $deletePersonnelStmt->close();
        
        // Then delete the PPAS form record
        $sql = "DELETE FROM ppas_forms WHERE id = ?";
        $params = [$entryId];
        $types = 'i';
        
        // Prepare and execute the SQL query
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Error preparing delete statement: " . $conn->error);
        }
        
        // Bind parameters
        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            throw new Exception("Error executing delete statement: " . $stmt->error);
        }
        
        // Check if any rows were affected
        if ($stmt->affected_rows === 0) {
            throw new Exception("No entries were deleted");
        }
        
        // Close statement
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Success response
        $response = [
            'success' => true,
            'message' => 'Entry and related personnel deleted successfully'
        ];
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    // Error response
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    // Log error
    error_log("Error in delete_ppas_entry.php: " . $e->getMessage());
} finally {
    // Return response
    echo json_encode($response);
    exit;
} 