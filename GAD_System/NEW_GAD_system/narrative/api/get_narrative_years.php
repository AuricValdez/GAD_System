<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in'
    ]);
    exit;
}

// Include database connection
require_once '../../includes/db_connect.php';

// Get campus parameter
$campus = isset($_GET['campus']) ? $_GET['campus'] : '';

// Validate parameters
if (empty($campus)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Campus parameter is required'
    ]);
    exit;
}

try {
    // Build SQL query to get years with narratives for the selected campus
    // According to the database structure, we should use p.campus instead of username
    $sql = "SELECT DISTINCT YEAR(n.created_at) as year
            FROM narrative_forms n
            JOIN ppas_forms p ON n.ppas_id = p.id
            WHERE p.campus = ?
            ORDER BY year DESC";
    
    // Prepare and execute statement
    $stmt = $conn->prepare($sql);
    $stmt->execute([$campus]);
    
    // Fetch results
    $years = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'data' => $years
    ]);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
} 