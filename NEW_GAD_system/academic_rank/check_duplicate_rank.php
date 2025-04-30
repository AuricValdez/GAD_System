<?php
require_once('../config.php');
header('Content-Type: application/json');

if (isset($_POST['rank_name'])) {
    $rankName = trim($_POST['rank_name']);
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM academic_ranks WHERE academic_rank = ?");
        $stmt->execute([$rankName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['exists' => ($row['count'] > 0)]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No rank name provided']);
}
