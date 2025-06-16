<?php
require_once '../config.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT id, academic_rank FROM academic_ranks ORDER BY academic_rank ASC");
    $stmt->execute();
    $ranks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'data' => $ranks
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 