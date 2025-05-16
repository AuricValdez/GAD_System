<?php
// Prevent PHP errors from being output
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '../php_errors.log');

// Include database configuration
require_once '../config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'entries' => []
];

try {
    // Get a list of PPAS forms with their gender issue details
    $sql = "SELECT p.id, p.activity, p.gender_issue_id, g.gender_issue, g.id as g_id 
            FROM ppas_forms p
            LEFT JOIN gpb_entries g ON p.gender_issue_id = g.id
            ORDER BY p.id ASC
            LIMIT 10";
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        throw new Exception("Error executing query: " . $conn->error);
    }
    
    // Fetch the entries
    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }
    
    // Success response
    $response = [
        'success' => true,
        'message' => 'Entries retrieved successfully',
        'entries' => $entries,
        'entry_count' => count($entries)
    ];
    
    // Check the structure of the gpb_entries table
    $gpbQuery = "DESCRIBE gpb_entries";
    $gpbResult = $conn->query($gpbQuery);
    
    if ($gpbResult !== false) {
        $columns = [];
        while ($row = $gpbResult->fetch_assoc()) {
            $columns[] = $row;
        }
        $response['gpb_columns'] = $columns;
    }
    
    // Check the structure of the ppas_forms table
    $ppasQuery = "DESCRIBE ppas_forms";
    $ppasResult = $conn->query($ppasQuery);
    
    if ($ppasResult !== false) {
        $columns = [];
        while ($row = $ppasResult->fetch_assoc()) {
            $columns[] = $row;
        }
        $response['ppas_columns'] = $columns;
    }
    
} catch (Exception $e) {
    // Error response
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    // Log error
    error_log("Error in check_database.php: " . $e->getMessage());
} finally {
    // Return response
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}
?> 