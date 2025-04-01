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

try {
    // Build SQL query to get distinct campuses
    $sql = "SELECT DISTINCT campus as name
            FROM ppas_forms p
            JOIN narratives n ON p.id = n.ppas_id
            WHERE campus IS NOT NULL AND campus <> ''
            ORDER BY campus ASC";
    
    // Prepare and execute statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Fetch results
    $campuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'data' => $campuses
    ]);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
} 