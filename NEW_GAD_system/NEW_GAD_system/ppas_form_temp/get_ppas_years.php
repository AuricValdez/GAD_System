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
    'years' => [],
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
    
    // Get campus filter parameter if provided
    $campusFilter = isset($_GET['campus']) ? $_GET['campus'] : '';
    
    // Build the SQL query to get distinct years
    $sql = "SELECT DISTINCT year FROM ppas_forms WHERE 1=1";
    
    $params = [];
    $types = '';
    
    // Add campus filter based on user role
    if ($isCentral) {
        if (!empty($campusFilter) && $campusFilter !== 'All Campuses') {
            $sql .= " AND campus = ?";
            $params[] = $campusFilter;
            $types .= 's';
        }
        // If Central user with no campus filter or 'All Campuses', show all years
    } else {
        // Non-Central users can only see their own campus
        $sql .= " AND campus = ?";
        $params[] = $userCampus;
        $types .= 's';
    }
    
    // Order by year descending (newest first)
    $sql .= " ORDER BY year DESC";
    
    // Prepare and execute the SQL query
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    // Bind parameters if any
    if (!empty($params)) {
        $bind_params = array_merge([$types], $params);
        $stmt->bind_param(...$bind_params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Error executing statement: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    // Fetch all years
    $years = [];
    while ($row = $result->fetch_assoc()) {
        $years[] = $row['year'];
    }
    
    // If no years found, add the current year as a default
    if (empty($years)) {
        $years[] = date('Y');
    }
    
    // Close statement
    $stmt->close();
    
    // Success response
    $response = [
        'success' => true,
        'years' => $years,
        'message' => 'Years retrieved successfully'
    ];
    
} catch (Exception $e) {
    // Error response
    $response = [
        'success' => false,
        'years' => [],
        'message' => $e->getMessage()
    ];
    
    // Log error
    error_log("Error in get_ppas_years.php: " . $e->getMessage());
} finally {
    // Return response
    echo json_encode($response);
    exit;
} 