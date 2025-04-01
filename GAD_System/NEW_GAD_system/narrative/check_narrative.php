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

// Check if PPAS ID is provided
if (!isset($_GET['ppas_id']) || empty($_GET['ppas_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'PPAS ID is required'
    ]);
    exit();
}

$ppasId = $_GET['ppas_id'];
$username = $_SESSION['username'];

try {
    // Check if narrative_forms table exists
    $tableCheckQuery = "SHOW TABLES LIKE 'narrative_forms'";
    $tableCheckStmt = $conn->prepare($tableCheckQuery);
    $tableCheckStmt->execute();
    
    if ($tableCheckStmt->rowCount() == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'The narrative_forms table does not exist. Please run the setup script.'
        ]);
        exit();
    }
    
    // Check if a narrative form already exists for this PPAS form
    $query = "SELECT * FROM narrative_forms WHERE ppas_id = :ppas_id AND username = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':ppas_id', $ppasId);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Narrative exists, return it
        $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'exists' => true,
            'data' => $narrativeData
        ]);
    } else {
        // No narrative exists
        echo json_encode([
            'status' => 'success',
            'exists' => false
        ]);
    }
} catch (Exception $e) {
    // Log error
    file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - Error in check_narrative.php: " . $e->getMessage() . "\n", FILE_APPEND);
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// Close connection
$conn = null;
?> 