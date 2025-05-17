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

    // First try to get campuses from ppas_forms
    $query = "SELECT DISTINCT campus as name FROM ppas_forms WHERE campus IS NOT NULL ORDER BY campus";
    error_log("Executing query: $query");
              
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $campuses = [];
    while ($row = $stmt->fetch()) {
        if (!empty($row['name']) && $row['name'] !== 'null' && $row['name'] !== 'Default Campus') {
            $campuses[] = ['name' => $row['name']];
        }
    }

    // If no campuses found, try the target table as fallback
    if (empty($campuses)) {
        $query = "SELECT DISTINCT campus as name FROM target WHERE campus IS NOT NULL ORDER BY campus";
        error_log("No campuses found in ppas_forms, trying target table with query: $query");
                
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        while ($row = $stmt->fetch()) {
            if (!empty($row['name']) && $row['name'] !== 'null' && $row['name'] !== 'Default Campus') {
                $campuses[] = ['name' => $row['name']];
            }
        }
    }

    error_log("Query returned " . count($campuses) . " campuses");

    if (empty($campuses)) {
        error_log("No campuses found in database");
        echo json_encode([
            'status' => 'success',
            'data' => [],
            'message' => 'No campuses found in database'
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'data' => $campuses
        ]);
    }

} catch(PDOException $e) {
    error_log("Database error in get_campuses.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    error_log("General error in get_campuses.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An unexpected error occurred: ' . $e->getMessage()
    ]);
} 