<?php
// Prevent PHP errors from being output
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '../php_errors.log');

// Include database configuration
require_once '../config.php';

// Set content type to JSON
header('Content-Type: application/json');
// Set cache control headers to prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
// Prevent browser caching
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Initialize response array
$response = [
    'success' => false,
    'exists' => false,
    'message' => ''
];

try {
    // Check required parameters
    if (!isset($_GET['activity']) || !isset($_GET['campus'])) {
        throw new Exception('Missing required parameters');
    }
    
    $activity = trim($_GET['activity']);
    $campus = trim($_GET['campus']);
    
    // Year and quarter are still requested but not used in the query
    $year = isset($_GET['year']) ? trim($_GET['year']) : '';
    $quarter = isset($_GET['quarter']) ? trim($_GET['quarter']) : '';
    
    // Get the current entry ID if provided (for edit mode)
    $currentId = isset($_GET['currentId']) ? intval($_GET['currentId']) : 0;
    
    // Validate that activity and campus are not empty
    if (empty($activity) || empty($campus)) {
        throw new Exception('Activity or campus parameter is empty');
    }
    
    // Log the check for debugging
    error_log("Checking duplicate: Activity='$activity', Campus='$campus', CurrentID=$currentId (ignoring year and quarter)");
    
    // Prepare query to check for duplicates - using case-insensitive comparison with LOWER()
    // and exact string comparison with trimming
    // Exclude the current entry if in edit mode
    $sql = "SELECT id FROM ppas_forms WHERE LOWER(TRIM(activity)) = LOWER(?) AND campus = ?";
    
    // Add condition to exclude current entry if editing
    if ($currentId > 0) {
        $sql .= " AND id != ?";
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception('Error preparing statement: ' . $conn->error);
    }
    
    // Bind parameters, including the current ID if provided
    if ($currentId > 0) {
        $stmt->bind_param('ssi', $activity, $campus, $currentId);
    } else {
        $stmt->bind_param('ss', $activity, $campus);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Error executing statement: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    // Set response based on query result
    $response['success'] = true;
    $response['exists'] = $result->num_rows > 0;
    $response['message'] = $response['exists'] 
        ? 'An activity with the same name already exists for this campus.'
        : 'Activity name is available.';
    
    // Add query details for debugging
    $response['debug'] = [
        'activity' => $activity,
        'campus' => $campus,
        'currentId' => $currentId,
        'query' => $sql,
        'rowCount' => $result->num_rows
    ];
    
    // Log the result for debugging
    error_log("Duplicate check result: " . ($response['exists'] ? 'EXISTS' : 'UNIQUE') . " (rows: {$result->num_rows})");
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log('Error in check_duplicate_activity.php: ' . $e->getMessage());
} finally {
    // Close statement if it exists
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
    
    // Close connection if it exists
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
}

// Output the JSON response
echo json_encode($response);
?> 