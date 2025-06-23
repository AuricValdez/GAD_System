<?php
require_once '../config.php';

$campus = $_GET['campus'] ?? '';
$years = [];

if ($campus) {
    try {
        // Get years that exist for this specific campus
        $stmt = $pdo->prepare("SELECT DISTINCT year FROM target WHERE campus = ? ORDER BY year DESC");
        $stmt->execute([$campus]);
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch(PDOException $e) {
        error_log("Error getting years: " . $e->getMessage());
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Campus parameter is required']);
    exit;
}

header('Content-Type: application/json');
echo json_encode($years);