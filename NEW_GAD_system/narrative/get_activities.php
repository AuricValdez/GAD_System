<?php
// Disable all error display before any output
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start(); // Start output buffering to catch any unexpected output

// Global try-catch to ensure JSON response
try {
    session_start();
    
    // Debug log function
    function debug_log($message, $data = null) {
        error_log("GET_ACTIVITIES DEBUG: " . $message . ($data !== null ? " - " . print_r($data, true) : ""));
    }
    
    // Debug request information
    debug_log("SESSION", $_SESSION);
    debug_log("POST", $_POST);
    
    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        debug_log("User not logged in");
        throw new Exception("User not logged in");
    }
    
    // Include database configuration
    require_once '../config.php';
    
    // Get the logged-in user's campus (username)
    $userCampus = $_SESSION['username'];
    debug_log("User campus", $userCampus);
    
    // Validate inputs
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    $quarter = isset($_POST['quarter']) ? $_POST['quarter'] : '';
    
    debug_log("Received parameters - Year: $year, Quarter: $quarter");
    
    if (empty($year) || empty($quarter)) {
        debug_log("Invalid parameters - Year or Quarter is empty");
        throw new Exception("Invalid year or quarter");
    }
    
    // Ensure quarter has 'Q' prefix
    $queryQuarter = $quarter;
    if (strpos($quarter, 'Q') !== 0) {
        $queryQuarter = 'Q' . $quarter; // Add the 'Q' prefix
        debug_log("Added Q prefix to quarter: $queryQuarter");
    }
    debug_log("Using quarter format for query: $queryQuarter");
    
    // Prepare and execute query
    $activities = array();
    
// Modified query to include all activities but flag those without GAD proposals or with existing narratives
$narrativeId = isset($_POST['narrative_id']) ? intval($_POST['narrative_id']) : 0;

$sql = "SELECT p.id, p.activity, 
        CASE WHEN g.proposal_id IS NULL THEN 1 ELSE 0 END AS missing_gad,
        CASE 
            WHEN n.id IS NOT NULL AND n.id <> ? THEN 1 
            WHEN n.id IS NOT NULL AND n.id = ? THEN 2 
            ELSE 0 
        END AS has_narrative
        FROM ppas_forms p 
        LEFT JOIN gad_proposals g ON p.id = g.ppas_form_id 
        LEFT JOIN narrative n ON p.id = n.ppas_form_id 
        WHERE p.campus = ? AND p.year = ? AND p.quarter = ?";
    debug_log("SQL Query", $sql);
    debug_log("Parameters", [$userCampus, $year, $queryQuarter]);
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("iisss", $narrativeId, $narrativeId, $userCampus, $year, $queryQuarter);
    
    $success = $stmt->execute();
    if (!$success) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    debug_log("Query result count: " . $result->num_rows);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        // Close the statement since we're done with it
        $stmt->close();
        $stmtClosed = true;
    } else {
        // Close the statement since we're done with it
        $stmt->close();
        $stmtClosed = true;
        debug_log("No activities found for the specified year and quarter");
    }
    
    // Also check what quarters exist for this year and campus (keep ALL quarters)
    $quartSql = "SELECT DISTINCT p.quarter 
                FROM ppas_forms p 
                WHERE p.campus = ? AND p.year = ? 
                ORDER BY p.quarter";
    $quartStmt = $conn->prepare($quartSql);
    $quartStmt->bind_param("ss", $userCampus, $year);
    $quartStmt->execute();
    $quartResult = $quartStmt->get_result();
    
    $quarters = [];
    while($row = $quartResult->fetch_assoc()) {
        $quarters[] = $row['quarter'];
    }
    $quartStmt->close();
    
    debug_log("Available quarters for this year and campus", $quarters);
    
    // Close the main statement if not already closed
    if (!isset($stmtClosed)) {
        $stmt->close();
    }
    
    // Build response array
    $response = [
        'success' => true, 
        'activities' => $activities,
        'debug' => true,
        'params' => [
            'campus' => $userCampus,
            'year' => $year,
            'original_quarter' => $quarter,
            'query_quarter' => $queryQuarter
        ]
    ];
    
    if (!empty($quarters)) {
        $response['available_quarters'] = $quarters;
    }

    // Clear any previous output
    ob_end_clean();
    
    // Set JSON header and return response
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    // Clear any buffered output that might contain HTML or errors
    ob_end_clean();
    
    debug_log("Exception occurred: " . $e->getMessage());
    
    // Set JSON header and return error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => true,
        'params' => [
            'campus' => $userCampus ?? 'unknown',
            'year' => $year ?? '',
            'original_quarter' => $quarter ?? '',
            'query_quarter' => $queryQuarter ?? ''
        ]
    ]);
}
exit(); // Ensure no additional output
?> 