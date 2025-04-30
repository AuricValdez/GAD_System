<?php
require_once '../config.php';

$campus = $_GET['campus'] ?? '';
$year = $_GET['year'] ?? '';

error_log("Fetching target data for Campus: $campus, Year: $year");

$data = null;

if ($campus && $year) {
    try {
        // Convert year to integer for comparison
        $year = intval($year);
        
        $stmt = $pdo->prepare("SELECT campus, year, total_gaa, total_gad_fund FROM target WHERE campus = ? AND year = ?");
        $stmt->execute([$campus, $year]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("Query result: " . print_r($data, true));
        
        if (!$data) {
            http_response_code(404);
            $data = ['error' => 'No target found for the specified campus and year'];
        }
    } catch(PDOException $e) {
        http_response_code(500);
        $data = ['error' => 'Database error occurred'];
        error_log("Error fetching target data: " . $e->getMessage());
    }
} else {
    http_response_code(400);
    $data = ['error' => 'Campus and year are required'];
}

header('Content-Type: application/json');
echo json_encode($data);