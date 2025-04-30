<?php
// Prevent PHP errors from being output - must be at the top
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '../php_errors.log');

// Start session to get user info
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'entry' => null,
    'message' => ''
];

try {
    // Include database configuration
    require_once '../config.php';
    
    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        throw new Exception('User not logged in');
    }
    
    // Get current user's campus
    $userCampus = $_SESSION['username'];
    $isCentral = ($userCampus === 'Central');
    
    // Get the entry ID from the request
    $entryId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($entryId <= 0) {
        throw new Exception('Invalid entry ID');
    }
    
    // Build the SQL query with JOIN to get gender issue text
    $sql = "SELECT p.*, g.gender_issue 
            FROM ppas_forms p
            LEFT JOIN gpb_entries g ON p.gender_issue_id = g.id
            WHERE p.id = ?";
    
    $params = [$entryId];
    $types = 'i';
    
    // Add campus check for non-Central users
    if (!$isCentral) {
        $sql .= " AND p.campus = ?";
        $params[] = $userCampus;
        $types .= 's';
    }
    
    // Prepare and execute the SQL query
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    // Bind parameters
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("Error executing statement: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    // Fetch the entry
    if ($result->num_rows === 0) {
        throw new Exception("Entry not found");
    }
    
    $entry = $result->fetch_assoc();
    
    // Debug log
    error_log("Fetched PPAS entry: " . json_encode($entry));
    error_log("Source of budget field value: " . ($entry['source_of_budget'] ?? 'NOT FOUND'));
    error_log("SDGs value: " . ($entry['sdgs'] ?? 'NO SDGs FOUND'));
    
    // Ensure gender_issue_id is explicitly included in the response
    // Sometimes the JOIN can cause the original ID to be lost if NULL in the related table
    if (!isset($entry['gender_issue_id']) || $entry['gender_issue_id'] === null) {
        // Query again to get just the gender_issue_id
        $idSql = "SELECT gender_issue_id FROM ppas_forms WHERE id = ?";
        $idStmt = $conn->prepare($idSql);
        $idStmt->bind_param('i', $entryId);
        $idStmt->execute();
        $idResult = $idStmt->get_result();
        
        if ($idResult->num_rows > 0) {
            $idRow = $idResult->fetch_assoc();
            $entry['gender_issue_id'] = $idRow['gender_issue_id'];
        }
        
        $idStmt->close();
    }
    
    // Close statement
    $stmt->close();
    
    // Success response
    $response = [
        'success' => true,
        'entry' => $entry,
        'message' => 'Entry retrieved successfully'
    ];
    
} catch (Exception $e) {
    // Error response
    $response = [
        'success' => false,
        'entry' => null,
        'message' => $e->getMessage()
    ];
    
    // Log error
    error_log("Error in get_ppas_entry.php: " . $e->getMessage());
} finally {
    // Return response
    echo json_encode($response);
    exit;
} 