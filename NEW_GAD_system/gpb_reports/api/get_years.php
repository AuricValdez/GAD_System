<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug log
error_log("get_years.php accessed");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in in get_years.php");
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Database connection
require_once '../config.php';

try {
    // Get campus parameter
    $campus_id = isset($_GET['campus_id']) ? $_GET['campus_id'] : '';
    
    if (empty($campus_id)) {
        throw new Exception("Campus ID is required");
    }
    
    // Debug log
    error_log("Campus ID: " . $campus_id);
    
    // Query to get years for the specified campus
    $query = "SELECT DISTINCT year FROM gpb_entries WHERE campus = ? ORDER BY year DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$campus_id]);
    
    $years = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'data' => $years
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_years.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// PDO connections are automatically closed when the script ends
$stmt = null;
$pdo = null; 