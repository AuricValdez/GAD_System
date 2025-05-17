<?php
session_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Include database configuration
require_once '../config.php';

// Get activity title from request
$activity = isset($_GET['activity']) ? $_GET['activity'] : '';

if (empty($activity)) {
    echo json_encode(['success' => false, 'message' => 'Activity title is required']);
    exit();
}

try {
    // Connect to database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    
    // Prepare SQL to find narrative by activity title
    $sql = "SELECT n.id FROM narrative n 
            INNER JOIN ppas_forms p ON n.ppas_form_id = p.id 
            WHERE p.activity = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $activity);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'narrative_id' => $row['id']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Narrative not found for this activity']);
    }
    
} catch (Exception $e) {
    error_log("Error finding narrative by activity: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

exit();
?> 