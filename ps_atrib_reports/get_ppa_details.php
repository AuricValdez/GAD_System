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
    
    if (!isset($pdo) || !$pdo) {
        error_log("Database connection failed");
        throw new Exception("Database connection failed");
    }

    // Get PPA ID from request
    $ppaId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($ppaId <= 0) {
        throw new Exception("Invalid PPA ID");
    }

    error_log("Getting details for PPA ID: " . $ppaId);

    // Query to get PPA details including personnel information
    $query = "SELECT 
                id, 
                activity as title, 
                campus, 
                CONCAT(start_date, ' - ', end_date) as date_range,
                DATE_FORMAT(start_date, '%m/%d/%Y') as date, 
                total_duration,
                project_leader,
                assistant_project_leader,
                project_staff_coordinator,
                ps_attribution
              FROM ppas_forms WHERE id = ?";
    
    error_log("Executing query: " . $query . " with ID: " . $ppaId);
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$ppaId]);
    
    $ppa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ppa) {
        error_log("No PPA found with ID: " . $ppaId);
        throw new Exception("PPA not found with ID: " . $ppaId);
    }
    
    // Convert JSON strings to arrays
    $ppa['project_leader'] = json_decode($ppa['project_leader'], true);
    $ppa['assistant_project_leader'] = json_decode($ppa['assistant_project_leader'], true);
    $ppa['project_staff_coordinator'] = json_decode($ppa['project_staff_coordinator'], true);
    
    // Calculate total personnel involved
    $ppa['total_personnel'] = 0;
    if (is_array($ppa['project_leader'])) {
        $ppa['total_personnel'] += count($ppa['project_leader']);
    }
    if (is_array($ppa['assistant_project_leader'])) {
        $ppa['total_personnel'] += count($ppa['assistant_project_leader']);
    }
    if (is_array($ppa['project_staff_coordinator'])) {
        $ppa['total_personnel'] += count($ppa['project_staff_coordinator']);
    }
    
    error_log("PPA details retrieved successfully: " . json_encode($ppa));
    
    // Return success response with PPA details
    echo json_encode([
        'success' => true,
        ...$ppa
    ]);
    
} catch (Exception $e) {
    error_log("ERROR in get_ppa_details.php: " . $e->getMessage());
    http_response_code(200); // Setting to 200 to allow client to read the error message
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage()
    ]);
}
?> 