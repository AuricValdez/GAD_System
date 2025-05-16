<?php
// Enable error logging but don't display to users
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../error_log.txt');

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

    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if (!$id) {
        throw new Exception('PPA ID is required');
    }

    error_log("Getting details for PPA ID: " . $id);

    $query = "SELECT 
                id,
                activity as title,
                start_date as date,
                total_duration as total_duration,
                approved_budget,
                source_of_fund,
                ps_attribution
              FROM ppas_forms 
              WHERE id = ?";
              
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Statement preparation failed: " . $conn->error);
        throw new Exception("Database query preparation failed");
    }

    $stmt->bind_param('i', $id);
    
    if (!$stmt->execute()) {
        error_log("Query execution failed: " . $stmt->error);
        throw new Exception("Query execution failed");
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('PPA not found');
    }
    
    $ppa = $result->fetch_assoc();
    
    // Format date
    if (isset($ppa['date']) && $ppa['date']) {
        $ppa['date'] = date('F d, Y', strtotime($ppa['date']));
    }
    
    error_log("Successfully fetched PPA details");
    echo json_encode($ppa);
    
} catch (Exception $e) {
    error_log("ERROR in get_ppa_details.php: " . $e->getMessage());
    http_response_code(200); // Setting to 200 to allow client to read the error message
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 