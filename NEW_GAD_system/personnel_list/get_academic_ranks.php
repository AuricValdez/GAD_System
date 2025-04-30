<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable HTML error output

header('Content-Type: application/json');

require_once '../config.php';

try {
    // Use $pdo instead of $conn to match your config.php
    $stmt = $pdo->prepare("SELECT id, academic_rank FROM academic_ranks ORDER BY academic_rank");
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
        'message' => $e->getMessage()
    ]);
}
?>