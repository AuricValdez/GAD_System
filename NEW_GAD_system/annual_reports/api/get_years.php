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
    // Get parameters
    $campus_id = isset($_GET['campus_id']) ? $_GET['campus_id'] : '';
    $all_campuses = isset($_GET['all_campuses']) && $_GET['all_campuses'] == 1;
    
    // Check if either campus_id or all_campuses flag is provided
    if (empty($campus_id) && !$all_campuses) {
        throw new Exception("Either campus_id or all_campuses flag is required");
    }
    
    // Debug log
    error_log("Campus ID: " . $campus_id . ", All Campuses: " . ($all_campuses ? 'true' : 'false'));
    
    // Query to get years
    if ($all_campuses || $campus_id === 'All') {
        // For 'All' campus selection or all_campuses flag, get years across all campuses
        $query = "SELECT DISTINCT year FROM gpb_entries ORDER BY year DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        error_log("Executing query for all campuses");
    } else {
        // Get years for the specified campus
        $query = "SELECT DISTINCT year FROM gpb_entries WHERE campus = ? ORDER BY year DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$campus_id]);
        
        error_log("Executing query for campus: " . $campus_id);
    }
    
    $years = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Found " . count($years) . " distinct years");
    
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