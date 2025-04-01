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
    'issues' => [],
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
    
    // Build query to get all gender issues
    $sql = "SELECT id, gender_issue FROM gpb_entries";
    
    // Add campus filter for non-central users
    if (!$isCentral) {
        $sql .= " WHERE campus = ?";
    }
    
    $sql .= " ORDER BY id ASC";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    // Bind parameters if needed
    if (!$isCentral) {
        $stmt->bind_param('s', $userCampus);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Error executing statement: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    // Fetch all gender issues
    $issues = [];
    while ($row = $result->fetch_assoc()) {
        $issues[] = $row;
    }
    
    // Add some additional info
    $moreInfo = [
        'total_count' => count($issues),
        'campus' => $userCampus,
        'is_central' => $isCentral
    ];
    
    // Success response
    $response = [
        'success' => true,
        'issues' => $issues,
        'info' => $moreInfo,
        'message' => 'Gender issues retrieved successfully'
    ];
    
} catch (Exception $e) {
    // Error response
    $response = [
        'success' => false,
        'issues' => [],
        'message' => $e->getMessage()
    ];
    
    // Log error
    error_log("Error in check_gender_issues.php: " . $e->getMessage());
} finally {
    // Return response
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}
?> 