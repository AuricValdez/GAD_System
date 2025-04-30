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
    'personnel' => [],
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
    
    // Get the PPAS form ID from the request
    $ppasFormId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($ppasFormId <= 0) {
        throw new Exception('Invalid PPAS form ID');
    }
    
    // First verify that the user has permission to access this PPAS form
    if (!$isCentral) {
        $checkSql = "SELECT campus FROM ppas_forms WHERE id = ?";
        $checkStmt = $conn->prepare($checkSql);
        
        if ($checkStmt === false) {
            throw new Exception("Error preparing check statement: " . $conn->error);
        }
        
        $checkStmt->bind_param("i", $ppasFormId);
        
        if (!$checkStmt->execute()) {
            throw new Exception("Error executing check statement: " . $checkStmt->error);
        }
        
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            throw new Exception("PPAS form not found");
        }
        
        $formCampus = $checkResult->fetch_assoc()['campus'];
        
        if ($formCampus !== $userCampus) {
            throw new Exception("You do not have permission to access this form");
        }
        
        $checkStmt->close();
    }
    
    // Build the SQL query to get all personnel associated with the PPAS form
    $sql = "SELECT p.ppas_form_id, p.personnel_id, p.role, pl.name, pl.gender, pl.academic_rank, 
            ar.monthly_salary, ar.hourly_rate as rate_per_hour 
            FROM ppas_personnel p
            JOIN personnel pl ON p.personnel_id = pl.id
            LEFT JOIN academic_ranks ar ON pl.academic_rank = ar.academic_rank
            WHERE p.ppas_form_id = ?
            ORDER BY p.role, pl.name";
            
    error_log("PPAS Personnel SQL: " . $sql);
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $ppasFormId);
    
    if (!$stmt->execute()) {
        throw new Exception("Error executing statement: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $personnel = [];
    
    while ($row = $result->fetch_assoc()) {
        $personnel[] = $row;
    }
    
    $stmt->close();
    
    // Success response
    $response = [
        'success' => true,
        'personnel' => $personnel,
        'message' => 'Personnel data retrieved successfully'
    ];
    
} catch (Exception $e) {
    // Error response
    $response = [
        'success' => false,
        'personnel' => [],
        'message' => $e->getMessage()
    ];
    
    // Log error
    error_log("Error in get_ppas_personnel.php: " . $e->getMessage());
} finally {
    // Return response
    echo json_encode($response);
    exit;
} 