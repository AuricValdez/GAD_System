<?php
// Include database connection
require_once '../config.php';
header('Content-Type: application/json');

try {
    if (!isset($pdo) || !$pdo) {
        throw new Exception("Database connection failed");
    }
    
    // Query to get academic ranks from the academic_ranks table (not academic_rank)
    $query = "SELECT id, academic_rank as name, monthly_salary FROM academic_ranks ORDER BY academic_rank ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $ranks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return success response with ranks
    echo json_encode([
        'success' => true,
        'ranks' => $ranks
    ]);
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 