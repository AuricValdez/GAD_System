<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Debug session state
error_log("Session state in get_personnel.php: " . print_r($_SESSION, true));

if (!isset($_SESSION['username'])) {
    error_log("No username in session");
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Not logged in'
    ]);
    exit;
}

try {
    $currentUser = $_SESSION['username'];
    
    // Debug logging
    error_log("Current user: " . $currentUser);
    
    // Test database connection
    try {
        $pdo->query('SELECT 1');
        error_log("Database connection successful");
    } catch (PDOException $e) {
        error_log("Database connection test failed: " . $e->getMessage());
        throw new Exception("Database connection failed");
    }
    
    if ($currentUser === 'Central') {
        // For Central user, get all personnel without WHERE clause
        $sql = "SELECT * FROM personnel ORDER BY campus ASC, name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else {
        // For campus users, filter by their campus
        $sql = "SELECT * FROM personnel WHERE campus = ? ORDER BY name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$currentUser]);
    }
    
    $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug logging
    error_log("SQL Query: " . $sql);
    error_log("Number of records found: " . count($personnel));
    error_log("Personnel data: " . print_r($personnel, true));
    
    if (empty($personnel)) {
        error_log("No personnel records found in database");
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $personnel
    ]);
} catch(Exception $e) {
    error_log("Error in get_personnel.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>