<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in',
        'code' => 'AUTH_ERROR'
    ]);
    exit();
}

// Include the database connection
require_once('../../includes/db_connection.php');

// Function to get database connection if include fails
if (!function_exists('getConnection')) {
    function getConnection() {
        try {
            $conn = new PDO(
                "mysql:host=localhost;dbname=gad_db;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            return $conn;
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
}

try {
    // Get database connection
    $conn = isset($pdo) ? $pdo : getConnection();
    
    // Query to fetch unique campuses from narrative table
    $sql = "SELECT DISTINCT campus as name FROM narrative_entries WHERE campus IS NOT NULL AND campus != '' ORDER BY campus";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $campuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no campuses found in narrative table, try fetching from other tables
    if (empty($campuses)) {
        // Try fetching from signatories table
        $sql = "SELECT DISTINCT campus as name FROM signatories WHERE campus IS NOT NULL AND campus != '' ORDER BY campus";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $campuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'data' => $campuses
    ]);

} catch (Exception $e) {
    // Log the error and return an error response
    error_log("Error fetching campuses: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching campuses: ' . $e->getMessage(),
        'code' => 'SERVER_ERROR'
    ]);
} 