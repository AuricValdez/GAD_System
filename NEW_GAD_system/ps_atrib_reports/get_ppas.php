<?php
// Enable error logging but don't display to users
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../error_log.txt');

// Log request starting
error_log("get_ppas.php started - " . date('Y-m-d H:i:s'));
error_log("GET parameters: " . json_encode($_GET));

// Clear any previous output
if (ob_get_level()) ob_end_clean();
header('Content-Type: application/json');

try {
    // Include database connection - using the correct path at root level
    if (!file_exists('../config.php')) {
        error_log("Database config file not found at ../config.php");
        throw new Exception("Database configuration file not found");
    }
    
    require_once '../config.php';
    
    if (!isset($conn) || $conn->connect_error) {
        error_log("Database connection failed: " . ($conn->connect_error ?? "Connection variable not set"));
        throw new Exception("Database connection failed");
    }
    
    error_log("Database connection successful");
    
    // Get and validate quarter parameter
    $quarter = isset($_GET['quarter']) ? (int)$_GET['quarter'] : null;
    error_log("Received quarter: " . $quarter);
    
    // Get campus parameter if available
    $campus = isset($_GET['campus']) ? $_GET['campus'] : null;
    error_log("Received campus: " . ($campus ?? "None"));
    
    if (!$quarter || $quarter < 1 || $quarter > 4) {
        error_log("Invalid quarter value: " . $quarter);
        throw new Exception("Invalid quarter parameter");
    }
    
    // Format quarter for database query
    $quarterFormat = 'Q' . $quarter;
    error_log("Formatted quarter: " . $quarterFormat);
    
    // Check if ppas_forms table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'ppas_forms'");
    if ($tableCheck->num_rows === 0) {
        error_log("ppas_forms table does not exist");
        throw new Exception("Required table does not exist");
    }
    
    // Check if campus column exists in the table
    $columnCheck = $conn->query("SHOW COLUMNS FROM ppas_forms LIKE 'campus'");
    $hasCampusColumn = $columnCheck->num_rows > 0;
    error_log("Has campus column: " . ($hasCampusColumn ? "Yes" : "No"));
    
    // Build the SQL query based on parameters
    if ($hasCampusColumn && $campus) {
        $sql = "SELECT id, activity as title, start_date, campus 
                FROM ppas_forms 
                WHERE quarter = ? AND campus = ?";
        error_log("SQL with campus filter: " . $sql);
    } else {
        $sql = "SELECT id, activity as title, start_date, 
                " . ($hasCampusColumn ? "campus" : "'Main Campus' as campus") . " 
                FROM ppas_forms 
                WHERE quarter = ?";
        error_log("SQL without campus filter: " . $sql);
    }
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Statement preparation failed: " . $conn->error);
        throw new Exception("Database query preparation failed");
    }
    
    if ($hasCampusColumn && $campus) {
        $stmt->bind_param('ss', $quarterFormat, $campus);
        error_log("Bound parameters: Quarter = " . $quarterFormat . ", Campus = " . $campus);
    } else {
        $stmt->bind_param('s', $quarterFormat);
        error_log("Bound parameter: Quarter = " . $quarterFormat);
    }
    
    if (!$stmt->execute()) {
        error_log("Query execution failed: " . $stmt->error);
        throw new Exception("Query execution failed");
    }
    
    error_log("Query executed successfully");
    $result = $stmt->get_result();
    
    $ppas = array();
    error_log("Fetching results");
    
    while ($row = $result->fetch_assoc()) {
        $item = array(
            'id' => (int)$row['id'],
            'title' => isset($row['title']) ? htmlspecialchars($row['title']) : '',
            'date' => isset($row['start_date']) ? date('F d, Y', strtotime($row['start_date'])) : '',
            'campus' => isset($row['campus']) ? htmlspecialchars($row['campus']) : 'Main Campus'
        );
        $ppas[] = $item;
    }
    
    error_log("Found " . count($ppas) . " records");
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $ppas
    ]);
    
} catch (Exception $e) {
    error_log("ERROR in get_ppas.php: " . $e->getMessage());
    
    // Send appropriate error response
    http_response_code(200); // Setting to 200 to allow client to read the error message
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
// No closing PHP tag to prevent accidental whitespace
?> 