<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug log
error_log("get_campuses.php accessed");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in in get_campuses.php");
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Database connection
require_once '../config.php';

try {
    // Use PDO connection from config.php
    $query = "SELECT DISTINCT campus as name FROM gpb_entries ORDER BY campus";
    
    // Debug log
    error_log("Query: " . $query);
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $campuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug log
    error_log("Found campuses: " . json_encode($campuses));
    
    echo json_encode([
        'status' => 'success',
        'data' => $campuses
    ]);
} catch (PDOException $e) {
    error_log("Database error in get_campuses.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// PDO connections are automatically closed when the script ends
$stmt = null;
$pdo = null; 