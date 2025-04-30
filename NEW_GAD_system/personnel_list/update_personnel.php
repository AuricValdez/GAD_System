<?php
// Prevent any HTML output before JSON response
ob_start();

// Set content type to JSON
header('Content-Type: application/json');

// Disable error display in output - force this even with xdebug
ini_set('display_errors', 0);
ini_set('html_errors', 0); // Disable HTML formatting in error messages
error_reporting(0); // Turn off all error reporting for this file

// Log errors to file instead
ini_set('log_errors', 1);
ini_set('error_log', '../php_errors.log');

try {
    // Include database connection
    require_once '../config.php';
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        throw new Exception('User not logged in');
    }

    // Get JSON data from request
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // Log received data for debugging
    error_log("Received data: " . print_r($data, true));

    // Validate data
    if (!isset($data['id']) || !isset($data['name']) || !isset($data['category']) || 
        !isset($data['status']) || !isset($data['gender']) || !isset($data['academicRank'])) {
        throw new Exception('Missing required fields');
    }

    // Sanitize inputs - using modern alternatives to FILTER_SANITIZE_STRING
    $id = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);
    $name = htmlspecialchars(trim($data['name']), ENT_QUOTES, 'UTF-8');
    $category = htmlspecialchars(trim($data['category']), ENT_QUOTES, 'UTF-8');
    $status = htmlspecialchars(trim($data['status']), ENT_QUOTES, 'UTF-8');
    $gender = htmlspecialchars(trim($data['gender']), ENT_QUOTES, 'UTF-8');
    $academicRank = htmlspecialchars(trim($data['academicRank']), ENT_QUOTES, 'UTF-8');
    $campus = htmlspecialchars(trim($data['campus']), ENT_QUOTES, 'UTF-8');

    // Log sanitized data
    error_log("Sanitized data - ID: $id, Name: $name, Category: $category, Status: $status, Gender: $gender, Academic Rank: $academicRank, Campus: $campus");

    // Verify that the user has permission to update this record
    // For non-Central users, they can only update records for their own campus
    if ($_SESSION['username'] !== 'Central' && $campus !== $_SESSION['username']) {
        throw new Exception('You do not have permission to update this record');
    }

    // First, check if the record exists
    $checkStmt = $pdo->prepare("SELECT id FROM personnel WHERE id = ?");
    $checkStmt->execute([$id]);
    
    if ($checkStmt->rowCount() === 0) {
        throw new Exception('Personnel record not found');
    }
    
    // Update personnel in database
    $stmt = $pdo->prepare("UPDATE personnel SET 
                          category = ?, 
                          status = ?, 
                          gender = ?, 
                          academic_rank = ? 
                          WHERE id = ?");
    
    $result = $stmt->execute([$category, $status, $gender, $academicRank, $id]);
    
    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Personnel updated successfully']);
    } else {
        throw new Exception('Failed to update personnel: ' . implode(", ", $stmt->errorInfo()));
    }
} catch (Exception $e) {
    error_log("Exception in update_personnel.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    error_log("PDO Exception in update_personnel.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    error_log("Throwable in update_personnel.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Unexpected error: ' . $e->getMessage()]);
}

// Clear any buffered output
ob_end_flush();
?> 