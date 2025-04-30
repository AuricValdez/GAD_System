<?php
// Start output buffering to catch any unwanted output
ob_start();

// Disable error display but keep logging
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    // Check if config file exists
    if (!file_exists('../config.php')) {
        throw new Exception('Configuration file not found');
    }

    // Include config file
    require_once '../config.php';

    // Check database connection
    if (!isset($conn) || !$conn) {
        throw new Exception('Database connection not established');
    }

    // Get campus from request parameter or session
    $campus = '';
    
    // First try to get campus from request parameter
    if (isset($_GET['campus']) && !empty($_GET['campus'])) {
        $campus = $_GET['campus'];
    }
    // If not in request, try to get from session
    else if (isset($_SESSION['username'])) {
        $campus = $_SESSION['username'];
    }
    
    // If still empty, throw an error
    if (empty($campus)) {
        throw new Exception('Campus not specified');
    }
    
    // Log for debugging
    error_log("Processing request for campus: " . $campus);

    // First, check if we have any records for this campus
    $checkQuery = "SELECT COUNT(*) as count FROM ppas_forms WHERE campus = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param('s', $campus);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $recordCount = $checkResult->fetch_assoc()['count'];
    
    error_log("Found {$recordCount} records for campus {$campus}");

    // Modified query to ensure we get all records
    $query = "SELECT DISTINCT year, quarter 
             FROM ppas_forms 
             WHERE campus = ?
             AND year IS NOT NULL 
             AND quarter IS NOT NULL 
             AND year != '' 
             AND quarter != ''
             ORDER BY year DESC, 
                      CASE quarter 
                          WHEN 'Q1' THEN 1 
                          WHEN 'Q2' THEN 2 
                          WHEN 'Q3' THEN 3 
                          WHEN 'Q4' THEN 4 
                      END ASC";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param('s', $campus);
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $periods = array();
    $rawData = array(); // For debugging
    
    while ($row = $result->fetch_assoc()) {
        $year = intval($row['year']);
        $quarter = $row['quarter'];
        
        // Store raw data for debugging
        $rawData[] = $row;
        
        if (!isset($periods[$year])) {
            $periods[$year] = array();
        }
        if (!in_array($quarter, $periods[$year])) {
            $periods[$year][] = $quarter;
        }
    }

    // Sort years in descending order
    krsort($periods);

    // Sort quarters within each year
    foreach ($periods as &$quarters) {
        sort($quarters);
    }

    // Clear any output that might have been generated
    ob_clean();

    // Send JSON response with detailed debug info
    echo json_encode([
        'success' => true,
        'data' => $periods,
        'debug' => [
            'campus' => $campus,
            'totalRecords' => $recordCount,
            'periodsFound' => count($periods),
            'rawData' => $rawData,
            'sql' => $query
        ]
    ]);

} catch (Exception $e) {
    // Clear any output that might have been generated
    ob_clean();
    
    error_log("Error in get_available_periods.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'campus' => $campus ?? 'not set',
            'trace' => $e->getTraceAsString()
        ]
    ]);
}

// End output buffering
ob_end_flush();
?> 