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

// Debug all variables - explicit output to track what's happening
error_log("************************ GET_YEARS.PHP CALLED ************************");
error_log("SESSION in get_years.php: " . print_r($_SESSION, true));
error_log("GET in get_years.php: " . print_r($_GET, true));
error_log("COOKIE in get_years.php: " . print_r($_COOKIE, true));

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in");
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// IMPORTANT: For years, ONLY use the username as the campus value
// This ensures we only get years for the logged-in campus
$sessionUsername = $_SESSION['username'];
$campus = $sessionUsername;
error_log("####### CRITICAL CHECK: SESSION USERNAME = '$sessionUsername', USING CAMPUS = '$campus' #######");

// Debug
error_log("Fetching years ONLY for logged in campus: $campus (ignoring any overrides)");

try {
    // Include database connection
    require_once '../config.php';
    
    // First, let's do a check to see what campuses exist in the database with complete row counts
    error_log("FULL DATABASE CHECK - All campuses in ppas_forms table:");
    $checkQuery = "SELECT campus, COUNT(*) as record_count FROM ppas_forms GROUP BY campus ORDER BY campus";
    $checkResult = $conn->query($checkQuery);
    if ($checkResult) {
        error_log("CAMPUS COUNTS IN DATABASE:");
        while ($row = $checkResult->fetch_assoc()) {
            error_log("===> CAMPUS: '" . $row['campus'] . "' has " . $row['record_count'] . " records");
        }
    }
    
    // Now check if there are any records for the current user's campus
    $countQuery = "SELECT COUNT(*) as count FROM ppas_forms WHERE campus = ?";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param("s", $campus);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $countRow = $countResult->fetch_assoc();
    $count = $countRow['count'];
    error_log("DETAILED COUNT: Found $count records for campus '$campus' in ppas_forms table");
    $countStmt->close();
    
    // Show sample records if any exist
    if ($count > 0) {
        $sampleQuery = "SELECT id, campus, year, quarter, activity FROM ppas_forms WHERE campus = ? LIMIT 5";
        $sampleStmt = $conn->prepare($sampleQuery);
        $sampleStmt->bind_param("s", $campus);
        $sampleStmt->execute();
        $sampleResult = $sampleStmt->get_result();
        
        error_log("SAMPLE RECORDS FOR CAMPUS '$campus':");
        while ($row = $sampleResult->fetch_assoc()) {
            error_log("RECORD: ID=" . $row['id'] . ", CAMPUS='" . $row['campus'] . "', YEAR=" . $row['year'] . 
                     ", QUARTER=" . $row['quarter'] . ", ACTIVITY='" . $row['activity'] . "'");
        }
        $sampleStmt->close();
    }
    
    // Prepare and execute query to get distinct years
    $query = "SELECT DISTINCT year FROM ppas_forms WHERE campus = ? ORDER BY year DESC";
    error_log("SQL Query: $query with strict campus parameter: [$campus]");
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $campus);
    $success = $stmt->execute();
    if (!$success) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Get result
    $result = $stmt->get_result();
    
    // Fetch all years
    $years = [];
    while ($row = $result->fetch_assoc()) {
        $years[] = $row['year'];
        error_log("RAW DB FETCH: Found year '" . $row['year'] . "' for campus '$campus'");
    }
    
    error_log("FETCH SUMMARY: Found " . count($years) . " years for logged-in campus: $campus");
    foreach ($years as $index => $year) {
        error_log("YEAR[$index]: $year for campus: $campus");
    }
    
    // If no years found, we'll just return an empty array rather than using fallback
    if (empty($years)) {
        error_log("NO YEARS FOUND: No years found in database for $campus, returning empty array");
        // No fallback - just return empty array
    }
    
    // Return success with years
    $response = [
        'success' => true,
        'years' => $years,
        'campus' => $campus,
        'username' => $sessionUsername, // Add username explicitly for debugging
        'message' => 'Years retrieved successfully' . (empty($result->num_rows) ? ' (using fallback)' : ''),
        'debug_time' => date('Y-m-d H:i:s'),
        'record_count' => $count
    ];
    
    error_log("FINAL RESPONSE: " . json_encode($response));
    echo json_encode($response);
    
    // Close statement and connection
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("DATABASE ERROR: " . $e->getMessage());
    
    // Return error without fallback
    $response = [
        'success' => false,
        'years' => [],
        'campus' => $campus,
        'username' => $sessionUsername,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug_time' => date('Y-m-d H:i:s')
    ];
    
    error_log("ERROR RESPONSE: " . json_encode($response));
    echo json_encode($response);
}
?> 