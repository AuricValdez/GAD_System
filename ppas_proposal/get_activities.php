<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include an error log file
ini_set('log_errors', 1);
ini_set('error_log', '../php_errors.log');

// Start session for accessing user information
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Debug all variables
error_log("SESSION in get_activities.php: " . print_r($_SESSION, true));
error_log("GET in get_activities.php: " . print_r($_GET, true));

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in");
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// Get the campus from GET parameter, session, or username as fallback
$campus = isset($_GET['campus']) && !empty($_GET['campus']) 
    ? $_GET['campus'] 
    : (isset($_SESSION['campus']) && !empty($_SESSION['campus']) 
        ? $_SESSION['campus'] 
        : $_SESSION['username']);

// If campus is still empty, return an error
if (empty($campus)) {
    error_log("Campus information not available");
    echo json_encode([
        'success' => false,
        'message' => 'Campus information not available'
    ]);
    exit;
}

// Get year and quarter from request
$year = isset($_GET['year']) ? $_GET['year'] : '';
$quarter = isset($_GET['quarter']) ? $_GET['quarter'] : '';

// Debug parameters
error_log("Fetching activities: Year=$year, Quarter=$quarter, Campus=$campus");

// Validate year and quarter
if (empty($year) || empty($quarter)) {
    error_log("Missing required parameters: year=$year, quarter=$quarter");
    echo json_encode([
        'success' => false,
        'message' => 'Year and quarter are required parameters'
    ]);
    exit;
}

try {
    // Include database connection
    require_once '../config.php';
    
    // Use a simpler query that doesn't exclude activities with GAD proposals
    $query = "SELECT DISTINCT p.id, p.activity 
              FROM ppas_forms p
              WHERE p.year = ? AND p.quarter = ? AND p.campus = ? 
              ORDER BY p.activity";
              
    error_log("Using modified SQL Query to show ALL activities: $query with params: [$year, $quarter, $campus]");
    
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $stmt->bind_param("sss", $year, $quarter, $campus);
    $success = $stmt->execute();
    if (!$success) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Get result
    $result = $stmt->get_result();
    
    // First, let's check which activities already have GAD proposals
    $existingProposals = [];
    $checkProposalsQuery = "SELECT ppas_form_id FROM gad_proposals";
    $proposalsResult = $conn->query($checkProposalsQuery);
    
    if ($proposalsResult) {
        while ($row = $proposalsResult->fetch_assoc()) {
            $existingProposals[$row['ppas_form_id']] = true;
        }
        error_log("Found " . count($existingProposals) . " existing GAD proposals");
    } else {
        error_log("Error checking existing proposals: " . $conn->error);
    }
    
    // Fetch all activities - identify duplicates but don't remove them
    $activitiesMap = [];
    $allActivities = [];
    $duplicatesFound = false;
    
    // First pass - identify all unique activities by title
    while ($row = $result->fetch_assoc()) {
        $activityTitle = trim($row['activity']);
        $activityId = $row['id'];
        
        // Track the first occurrence of each activity title
        if (!isset($activitiesMap[$activityTitle])) {
            $activitiesMap[$activityTitle] = $activityId;
        }
    }
    
    // Reset result pointer to beginning
    $result->data_seek(0);
    
    // Second pass - mark duplicates and build final array
    while ($row = $result->fetch_assoc()) {
        $activityTitle = trim($row['activity']);
        $activityId = $row['id'];
        
        // Check if this is a duplicate (same title but not the first occurrence)
        $isDuplicate = isset($activitiesMap[$activityTitle]) && $activitiesMap[$activityTitle] != $activityId;
        
        // Check if this activity already has a proposal
        $hasProposal = isset($existingProposals[$activityId]);
        
        // Add to our activities list with appropriate flags
        $allActivities[] = [
            'id' => $activityId,
            'title' => $activityTitle,
            'is_duplicate' => $isDuplicate,
            'has_proposal' => $hasProposal
        ];
        
        // Track duplicates for reporting
        if ($isDuplicate) {
            error_log("Duplicate activity found: '" . $activityTitle . "' with ID: " . $activityId . 
                     " (original ID: " . $activitiesMap[$activityTitle] . ")");
            $duplicatesFound = true;
        }
    }
    
    error_log("Found " . count($allActivities) . " total activities for $campus, year $year, quarter $quarter" . 
              ($duplicatesFound ? " (including duplicates)" : ""));
    
    // Return success with all activities including duplicates
    echo json_encode([
        'success' => true,
        'activities' => $allActivities,
        'count' => count($allActivities),
        'unique_count' => count($activitiesMap),
        'duplicates_found' => $duplicatesFound,
        'campus' => $campus,
        'year' => $year,
        'quarter' => $quarter
    ]);
    
    // Close statement and connection
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Database error in get_activities.php: " . $e->getMessage());
    // Return error
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'campus' => $campus,
        'year' => $year,
        'quarter' => $quarter
    ]);
}
?> 