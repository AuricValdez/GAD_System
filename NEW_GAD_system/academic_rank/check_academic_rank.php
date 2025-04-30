<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    // Parse JSON input
    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No input data provided');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    if (!isset($data['academic_rank'])) {
        throw new Exception('Academic rank is required');
    }

    $academic_rank = $data['academic_rank'];
    $is_update = isset($data['is_update']) ? $data['is_update'] : false;

    // Check if academic rank exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM academic_ranks WHERE academic_rank = ?");
    $stmt->execute([$academic_rank]);
    $count = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'exists' => $count > 0 && !$is_update
    ]);

} catch (Exception $e) {
    error_log("Error in check_academic_rank.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
