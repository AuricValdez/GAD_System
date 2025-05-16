<?php
// Include database connection
include_once '../config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if request has required parameters
if (!isset($_POST['personnel_name']) || empty($_POST['personnel_name'])) {
    echo json_encode(['exists' => false, 'error' => 'Missing required parameters']);
    exit;
}

// Get parameters
$personnelName = trim($_POST['personnel_name']);
$campus = isset($_POST['campus']) ? trim($_POST['campus']) : '';

// Connect to database
$conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if (!$conn) {
    echo json_encode(['exists' => false, 'error' => 'Database connection failed']);
    exit;
}

try {
    // Escape input to prevent SQL injection
    $personnelName = mysqli_real_escape_string($conn, $personnelName);
    $campus = mysqli_real_escape_string($conn, $campus);
    
    // Build query
    $sql = "SELECT COUNT(*) AS count FROM personnel_list WHERE name = '$personnelName'";
    
    // Add campus filter if provided
    if (!empty($campus)) {
        $sql .= " AND campus = '$campus'";
    }
    
    // Execute query
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $count = $row['count'];
        
        // Return result
        echo json_encode(['exists' => ($count > 0)]);
    } else {
        echo json_encode(['exists' => false, 'error' => 'Query failed']);
    }
    
} catch (Exception $e) {
    // Log the error and return a generic error message
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(['exists' => false, 'error' => 'Database error']);
} finally {
    // Close connection
    mysqli_close($conn);
} 