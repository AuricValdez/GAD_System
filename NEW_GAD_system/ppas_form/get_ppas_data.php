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
    'data' => [],
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
    
    // Get filter parameters
    $data = json_decode(file_get_contents('php://input'), true);
    
    $activityFilter = $data['activity'] ?? '';
    $yearFilter = $data['year'] ?? '';
    $quarterFilter = $data['quarter'] ?? '';
    $campusFilter = $data['campus'] ?? '';
    
    // If not Central user, can only view own campus
    if (!$isCentral && $campusFilter !== $userCampus) {
        $campusFilter = $userCampus;
    }
    
    // Build the SQL query with JOIN to get gender issue text
    $sql = "SELECT p.id, p.year, p.quarter, g.gender_issue, p.project, p.program, p.activity, p.approved_budget
            FROM ppas_forms p
            LEFT JOIN gpb_entries g ON p.gender_issue_id = g.id
            WHERE 1=1"; // 1=1 allows us to append filters conditionally
    
    $params = [];
    $types = '';
    
    // Add filters if provided
    if (!empty($activityFilter)) {
        $sql .= " AND p.activity LIKE ?";
        $activityFilter = "%$activityFilter%";
        $params[] = $activityFilter;
        $types .= 's';
    }
    
    if (!empty($yearFilter)) {
        $sql .= " AND p.year = ?";
        $params[] = $yearFilter;
        $types .= 's';
    }
    
    if (!empty($quarterFilter)) {
        $sql .= " AND p.quarter = ?";
        $params[] = $quarterFilter;
        $types .= 's';
    }
    
    // Add campus filter based on user role
    if ($isCentral) {
        if (!empty($campusFilter)) {
            $sql .= " AND p.campus = ?";
            $params[] = $campusFilter;
            $types .= 's';
        }
        // If Central user with no campus filter, show all campuses
    } else {
        // Non-Central users can only see their own campus
        $sql .= " AND p.campus = ?";
        $params[] = $userCampus;
        $types .= 's';
    }
    
    // Order by year, quarter, and activity
    $sql .= " ORDER BY p.year DESC, p.quarter ASC, p.activity ASC";
    
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
    
    // Fetch all rows
    $ppasData = [];
    while ($row = $result->fetch_assoc()) {
        // Format data as needed
        $ppasData[] = [
            'id' => $row['id'],
            'year' => $row['year'],
            'quarter' => $row['quarter'],
            'gender_issue' => $row['gender_issue'] ?? 'Unknown',
            'project' => $row['project'],
            'program' => $row['program'],
            'activity' => $row['activity'],
            'approved_budget' => $row['approved_budget']
        ];
    }
    
    // Close statement
    $stmt->close();
    
    // Success response
    $response = [
        'success' => true,
        'data' => $ppasData,
        'message' => 'Data retrieved successfully'
    ];
    
} catch (Exception $e) {
    // Error response
    $response = [
        'success' => false,
        'data' => [],
        'message' => $e->getMessage()
    ];
    
    // Log error
    error_log("Error in get_ppas_data.php: " . $e->getMessage());
} finally {
    // Return response
    echo json_encode($response);
    exit;
} 