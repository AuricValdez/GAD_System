<?php
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You must be logged in to access this data'
    ]);
    exit;
}

// Get the campus from query parameter
$campus = isset($_GET['campus']) ? trim($_GET['campus']) : '';

// Validate campus parameter
if (empty($campus)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Campus parameter is required'
    ]);
    exit;
}

try {
    // Include database connection file
    require_once 'db_connection.php';
    
    // Get connection
    $conn = getConnection();
    
    // First, let's try to fetch all years regardless of title matching
    // This will help us identify if there's data at all
    $query = "SELECT DISTINCT p.year 
              FROM ppas_forms p
              INNER JOIN narrative_entries n ON p.year = n.year AND p.campus = n.campus
              WHERE p.campus = :campus
              ORDER BY p.year DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':campus', $campus);
    $stmt->execute();
    $yearsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $years = [];
    foreach ($yearsData as $year) {
        $years[] = ['year' => $year['year']];
    }
    
    // Return the years - with more lenient matching
    echo json_encode([
        'status' => 'success',
        'data' => $years
    ]);
    
} catch (PDOException $e) {
    // Log the error
    error_log("Error fetching years: " . $e->getMessage());
    
    // Return an error response
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 