<?php
// Prevent PHP errors from being displayed
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '../../error.log');

header('Content-Type: application/json');
session_start();

// Check if config file exists
if (!file_exists('../../includes/config.php')) {
    error_log("Config file not found at: " . realpath('../../includes/config.php'));
    echo json_encode(['status' => 'error', 'message' => 'Configuration file not found']);
    exit;
}

require_once '../../includes/config.php';

// Debug database configuration
error_log("Database config - Server: $servername, DB: $dbname, User: $username");

// Verify database configuration variables
if (!isset($servername) || !isset($username) || !isset($password) || !isset($dbname)) {
    error_log("Database configuration variables not set properly");
    echo json_encode(['status' => 'error', 'message' => 'Database configuration error']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

// Get campus parameter
$campus = isset($_GET['campus']) ? $_GET['campus'] : null;

if (!$campus) {
    echo json_encode(['status' => 'error', 'message' => 'Campus parameter is required']);
    exit;
}

try {
    // Test database connection
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    $db = new PDO($dsn, $username, $password, $options);
    error_log("Connected to database successfully");

    // Simplify the query to just get distinct years 
    $query = "SELECT DISTINCT pf.year 
              FROM gad_proposals gp
              JOIN ppas_forms pf ON gp.ppas_form_id = pf.id
              WHERE pf.campus = :campus 
              ORDER BY pf.year DESC";
    error_log("Executing query: $query with campus: $campus");
              
    $stmt = $db->prepare($query);
    $stmt->execute(['campus' => $campus]);
    
    $years = [];
    while ($row = $stmt->fetch()) {
        $years[] = ['year' => $row['year']];
    }

    error_log("Query returned " . count($years) . " years");

    if (empty($years)) {
        error_log("No years found for campus: $campus");
        echo json_encode([
            'status' => 'success',
            'data' => [],
            'message' => 'No proposals found for this campus'
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'data' => $years
        ]);
    }

} catch(PDOException $e) {
    error_log("Database error in get_proposal_years.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    error_log("General error in get_proposal_years.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An unexpected error occurred: ' . $e->getMessage()
    ]);
} 