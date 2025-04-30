<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable HTML error output

header('Content-Type: application/json');

require_once '../config.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Rank ID is required'
    ]);
    exit;
}

try {
    // Changed from personnel to academic_ranks table
    $stmt = $pdo->prepare("SELECT salary_grade, monthly_salary, hourly_rate FROM academic_ranks WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($details) {
        echo json_encode([
            'status' => 'success',
            'data' => $details
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Rank not found'
        ]);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>