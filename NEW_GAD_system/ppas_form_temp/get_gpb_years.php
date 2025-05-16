<?php
// Disable error reporting to the browser
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set response header to JSON
header('Content-Type: application/json');

try {
    // Include database connection from config.php
    if (!file_exists('../config.php')) {
        throw new Exception("Database configuration file not found");
    }
    require_once '../config.php';
    
    // Check if connection is mysqli
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception("Database connection not found or not mysqli");
    }
    
    // Get campus parameter
    $campus = isset($_GET['campus']) ? $_GET['campus'] : '';

    // Validate campus parameter
    if (empty($campus)) {
        echo json_encode([]);
        exit;
    }
    
    // Escape the input to prevent SQL injection
    $campus_safe = $conn->real_escape_string($campus);
    
    // Prepare and execute the query to get distinct years
    $query = "SELECT DISTINCT year FROM gpb_entries WHERE campus = '$campus_safe' ORDER BY year DESC";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    // Fetch all results as an array
    $years = [];
    while ($row = $result->fetch_row()) {
        $years[] = $row[0];
    }
    
    // Return years as a simple JSON array
    echo json_encode($years);

} catch (Exception $e) {
    // For errors, return an empty array so forEach won't break
    echo json_encode([]);
    // Log the error for server-side debugging
    error_log('Error in get_gpb_years.php: ' . $e->getMessage());
}

// Close the connection if it exists
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?> 