<?php
// Start session
session_start();

// Set error reporting to log errors instead of displaying them
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_errors.log');

// Add a clear marker for debugging
error_log("=============== GAD PROPOSALS DEBUGGING START ===============");
error_log("Time: " . date('Y-m-d H:i:s'));
error_log("SESSION: " . print_r($_SESSION, true));
error_log("POST data: " . file_get_contents('php://input'));

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("GAD PROPOSALS ERROR: User not logged in");
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

try {
    // Include database configuration
    require_once '../config.php';
    error_log("GAD PROPOSALS: Database include successful");
    
    // Get the raw POST data and decode it
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("GAD PROPOSALS ERROR: JSON decode error - " . json_last_error_msg());
        throw new Exception('Invalid JSON data received: ' . json_last_error_msg());
    }

    // Initialize filters
    $activityFilter = isset($data['activity']) ? $data['activity'] : '';
    $modeFilter = isset($data['mode']) ? $data['mode'] : '';
    $campusFilter = isset($data['campus']) ? $data['campus'] : '';

    // Default to current user's campus if not Central
    if ($_SESSION['username'] !== 'Central' && empty($campusFilter)) {
        $campusFilter = $_SESSION['username'];
    }
    
    error_log("GAD PROPOSALS: Filters - Activity: '$activityFilter', Mode: '$modeFilter', Campus: '$campusFilter'");

    // Check database connection
    if (!isset($conn) || $conn->connect_error) {
        error_log("GAD PROPOSALS ERROR: Database connection failed - " . ($conn->connect_error ?? 'unknown error'));
        throw new Exception("Database connection failed");
    }
    
    // Check if tables exist
    error_log("GAD PROPOSALS: Checking tables...");
    
    // Get list of all tables in database
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    error_log("GAD PROPOSALS: Available tables - " . implode(", ", $tables));
    
    // Check for specific tables
    if (!in_array('gad_proposals', $tables)) {
        error_log("GAD PROPOSALS ERROR: 'gad_proposals' table does not exist!");
        throw new Exception("Required table 'gad_proposals' does not exist");
    }
    
    if (!in_array('ppas_forms', $tables)) {
        error_log("GAD PROPOSALS ERROR: 'ppas_forms' table does not exist!");
        throw new Exception("Required table 'ppas_forms' does not exist");
    }

    // First, get the actual columns from the gad_proposals table
    $columnQuery = "SHOW COLUMNS FROM gad_proposals";
    $columnResult = $conn->query($columnQuery);
    $columns = [];
    while ($row = $columnResult->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    error_log("GAD PROPOSALS: Available columns in gad_proposals - " . implode(", ", $columns));
    
    // Find a suitable ID column - look for primary key or something with 'id' in the name
    $idColumn = null;
    foreach ($columns as $column) {
        if (strtolower($column) === 'id' || stripos($column, '_id') !== false || stripos($column, 'id_') !== false) {
            $idColumn = $column;
            break;
        }
    }
    
    if (!$idColumn) {
        // If no ID-like column found, use the first column as a fallback
        $idColumn = $columns[0];
    }
    
    error_log("GAD PROPOSALS: Using '$idColumn' as the ID column");

    // Adjust the SQL query based on available columns
    $sql = "SELECT gp.$idColumn as id, pf.activity as activity_name, ";
    
    // Add other columns if they exist
    if (in_array('mode_of_delivery', $columns)) {
        $sql .= "gp.mode_of_delivery, ";
    } else {
        $sql .= "'N/A' as mode_of_delivery, ";
    }
    
    if (in_array('partner_office', $columns)) {
        $sql .= "gp.partner_office, ";
    } else {
        $sql .= "'N/A' as partner_office, ";
    }
    
    $sql .= "gp.campus 
            FROM gad_proposals gp
            LEFT JOIN ppas_forms pf ON ";
    
    // Check if ppas_form_id exists
    if (in_array('ppas_form_id', $columns)) {
        $sql .= "gp.ppas_form_id = pf.id";
    } else {
        // Fallback to other potential foreign key columns
        $foundKey = false;
        foreach ($columns as $column) {
            if (stripos($column, 'ppas') !== false || stripos($column, 'form') !== false) {
                $sql .= "gp.$column = pf.id";
                $foundKey = true;
                break;
            }
        }
        
        // If no appropriate key found, use a simple 1=1 relation
        if (!$foundKey) {
            error_log("GAD PROPOSALS WARNING: No suitable foreign key found to link to ppas_forms");
            $sql .= "1=1";
        }
    }
    
    $sql .= " WHERE 1=1";

    // Add filters if provided
    $params = [];
    $types = "";

    if (!empty($activityFilter)) {
        $sql .= " AND pf.activity LIKE ?";
        $params[] = "%$activityFilter%";
        $types .= "s";
    }

    if (!empty($modeFilter)) {
        $sql .= " AND gp.mode_of_delivery = ?";
        $params[] = $modeFilter;
        $types .= "s";
    }

    if (!empty($campusFilter)) {
        $sql .= " AND gp.campus = ?";
        $params[] = $campusFilter;
        $types .= "s";
    }

    // Order by newest first
    $sql .= " ORDER BY gp.$idColumn DESC";
    
    error_log("GAD PROPOSALS: SQL Query - $sql");

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("GAD PROPOSALS ERROR: Prepare statement failed - " . $conn->error);
        throw new Exception("Prepare statement failed: " . $conn->error);
    }

    if (!empty($params)) {
        $bindResult = $stmt->bind_param($types, ...$params);
        if (!$bindResult) {
            error_log("GAD PROPOSALS ERROR: Parameter binding failed");
            throw new Exception("Parameter binding failed");
        }
        error_log("GAD PROPOSALS: Parameters bound successfully");
    }

    if (!$stmt->execute()) {
        error_log("GAD PROPOSALS ERROR: Execute failed - " . $stmt->error);
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $data = [];

    error_log("GAD PROPOSALS: Query returned " . $result->num_rows . " rows");

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'id' => $row['id'],
                'activity_name' => $row['activity_name'],
                'mode_of_delivery' => $row['mode_of_delivery'],
                'partner_office' => $row['partner_office'],
                'campus' => $row['campus']
            ];
        }
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
    
    error_log("GAD PROPOSALS: Success - returning " . count($data) . " records");
    error_log("=============== GAD PROPOSALS DEBUGGING END ===============");
    
    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    // Log the error
    error_log("GAD PROPOSALS CRITICAL ERROR: " . $e->getMessage());
    error_log("=============== GAD PROPOSALS DEBUGGING END ===============");
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching data',
        'error' => $e->getMessage()
    ]);
}
?> 