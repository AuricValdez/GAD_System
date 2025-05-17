<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug log
error_log("get_gpb_report.php accessed");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in in get_gpb_report.php");
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

try {
    // Database connection
    require_once '../config.php';
    // Use PDO connection from config.php
    
    // Get parameters and clean them
    $campus = isset($_GET['campus']) ? trim($_GET['campus']) : '';
    $year = isset($_GET['year']) ? trim($_GET['year']) : '';
    $all_campuses = isset($_GET['all_campuses']) && $_GET['all_campuses'] == 1;

    // Debug log
    error_log("Raw parameters - campus: '" . $campus . "', year: '" . $year . "', all_campuses: " . ($all_campuses ? 'true' : 'false'));

    // Validate parameters
    if (empty($year) || (empty($campus) && !$all_campuses)) {
        error_log("Missing required parameters in get_gpb_report.php");
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        exit();
    }

    // Check if data exists for the campus/year
    if ($all_campuses) {
        // For all campuses, check if any data exists for any campus for the given year
        $checkQuery = "SELECT COUNT(*) FROM gpb_entries WHERE year = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$year]);
    } else {
        // For specific campus, check if data exists for that campus and year
        $checkQuery = "SELECT COUNT(*) FROM gpb_entries WHERE campus = ? AND year = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$campus, $year]);
    }
    
    $count = $checkStmt->fetchColumn();

    error_log("Found {$count} records for " . ($all_campuses ? "all campuses" : "campus '{$campus}'") . " and year '{$year}'");

    if ($count === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => "No data found for " . ($all_campuses ? "any campus" : "campus '{$campus}'") . " and year '{$year}'"
        ]);
        exit();
    }

    // Check if status field exists in gpb_entries table
    $checkStatusField = "SHOW COLUMNS FROM gpb_entries LIKE 'status'";
    $checkStatusStmt = $pdo->prepare($checkStatusField);
    $checkStatusStmt->execute();
    $statusFieldExists = $checkStatusStmt->rowCount() > 0;
    
    // Main query - using the actual column names from the table
    $query = "SELECT 
        g.category,
        g.gender_issue,
        g.cause_of_issue,
        g.gad_objective,
        g.relevant_agency,
        g.generic_activity,
        g.specific_activities,
        g.male_participants,
        g.female_participants,
        g.total_participants,
        g.gad_budget,
        g.source_of_budget,
        g.responsible_unit,
        g.created_at,
        g.campus,
        g.year,";
        
    // Only include status if the field exists
    if ($statusFieldExists) {
        $query .= "g.status,";
    }
    
    $query .= "t.total_gaa,
        t.total_gad_fund
    FROM gpb_entries g
    LEFT JOIN target t ON BINARY g.campus = BINARY t.campus 
        AND g.year = t.year
    WHERE ";
    
    // Adjust where clause based on all_campuses parameter
    if ($all_campuses) {
        $query .= "g.year = :year";
    } else {
        $query .= "g.campus = :campus AND g.year = :year";
    }
    
    // Add status filter to only show approved entries if the status field exists
    if ($statusFieldExists) {
        $query .= " AND (g.status = 'approved' OR g.status = 'Approved')";
    }
    
    $query .= " ORDER BY " . ($all_campuses ? "g.campus, " : "") . "g.id";

    error_log("Executing query: " . $query);
    error_log("With parameters - " . ($all_campuses ? "" : "campus: {$campus}, ") . "year: {$year}");

    $stmt = $pdo->prepare($query);
    
    if (!$stmt) {
        $error = $pdo->errorInfo();
        error_log("Prepare statement failed: " . print_r($error, true));
        throw new Exception("Failed to prepare statement: " . $error[2]);
    }

    // Bind parameters based on whether we're querying for all campuses or a specific one
    if (!$all_campuses) {
        $stmt->bindParam(':campus', $campus, PDO::PARAM_STR);
    }
    $stmt->bindParam(':year', $year, PDO::PARAM_STR);
    
    if (!$stmt->execute()) {
        $error = $stmt->errorInfo();
        error_log("Execute statement failed: " . print_r($error, true));
        throw new Exception("Failed to execute statement: " . $error[2]);
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Successfully fetched " . count($results) . " items");

    echo json_encode([
        'status' => 'success',
        'data' => $results
    ]);

} catch (PDOException $e) {
    error_log("PDO Error in get_gpb_report.php: " . $e->getMessage());
    error_log("PDO Error Code: " . $e->getCode());
    error_log("PDO Error Info: " . print_r($e->errorInfo, true));
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error in get_gpb_report.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

// PDO connections are automatically closed when the script ends
$stmt = null;
$pdo = null; 