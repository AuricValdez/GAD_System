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
    
    // Get parameters
    $campus = isset($_GET['campus']) ? $_GET['campus'] : '';
    $year = isset($_GET['year']) ? $_GET['year'] : '';

    // Validate parameters
    if (empty($campus) || empty($year)) {
        echo json_encode([]);
        exit;
    }
    
    // Escape the inputs to prevent SQL injection
    $campus_safe = $conn->real_escape_string($campus);
    $year_safe = $conn->real_escape_string($year);
    
    // Prepare and execute the query to get gender issues
    $query = "SELECT id, gender_issue FROM gpb_entries WHERE campus = '$campus_safe' AND year = '$year_safe'";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    // Fetch all results as an array
    $issues = [];
    while ($row = $result->fetch_assoc()) {
        $issues[] = $row;
    }
    
    // Return gender issues directly as an array
    echo json_encode($issues);

} catch (Exception $e) {
    // For errors, return an empty array
    echo json_encode([]);
    // Log the error for server-side debugging
    error_log('Error in get_gender_issues.php: ' . $e->getMessage());
}

// Close the connection if it exists
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?> 