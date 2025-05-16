<?php
header('Content-Type: application/json');
require_once '../config.php';

// Create database connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit();
}

// Sanitize inputs
$campus = isset($_GET['campus']) ? $conn->real_escape_string($_GET['campus']) : '';
$year = isset($_GET['year']) ? $conn->real_escape_string($_GET['year']) : '';
$all_campuses = isset($_GET['all_campuses']) && $_GET['all_campuses'] == 1;

// Return error if campus or year not provided (except when all_campuses=1 and year provided)
if (empty($year) || (empty($campus) && !$all_campuses)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Year and either campus or all_campuses flag are required parameters'
    ]);
    exit();
}

// Debug info for request
$debug_info = [
    'campus' => $campus,
    'year' => $year,
    'all_campuses' => $all_campuses ? 'true' : 'false',
    'timestamp' => date('Y-m-d H:i:s')
];

// Different query based on whether we want data for all campuses or a specific one
if ($all_campuses) {
    // When requesting all campuses, sum up the totals
    $query = "SELECT SUM(total_gaa) as total_gaa, SUM(total_gad_fund) as total_gad_fund 
              FROM `target` 
              WHERE year = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $year);
} else {
    // Standard query for single campus
    $query = "SELECT total_gaa, total_gad_fund 
              FROM `target` 
              WHERE campus = ? AND year = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $campus, $year);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Get the budget data
    $budget_data = $result->fetch_assoc();
    
    // Cast numeric values explicitly
    $total_gaa = floatval($budget_data['total_gaa']);
    $total_gad_fund = floatval($budget_data['total_gad_fund']);
    
    // Add debug to response
    $debug_info['original_gaa'] = $budget_data['total_gaa'];
    $debug_info['original_gad_fund'] = $budget_data['total_gad_fund'];
    $debug_info['parsed_gaa'] = $total_gaa;
    $debug_info['parsed_gad_fund'] = $total_gad_fund;
    
    // Return success with data
    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_gaa' => $total_gaa,
            'total_gad_fund' => $total_gad_fund
        ],
        'debug' => $debug_info
    ]);
} else {
    // No budget data found
    $error_msg = $all_campuses ? 
        "No budget data found for year $year across any campus" : 
        "No budget data found for campus $campus and year $year";
    
    echo json_encode([
        'status' => 'error',
        'message' => $error_msg,
        'debug' => $debug_info,
        'query' => $all_campuses ? 
            "SELECT SUM(total_gaa), SUM(total_gad_fund) FROM target WHERE year = '$year'" : 
            "SELECT total_gaa, total_gad_fund FROM target WHERE campus = '$campus' AND year = '$year'"
    ]);
}

// Close connections
$stmt->close();
$conn->close(); 