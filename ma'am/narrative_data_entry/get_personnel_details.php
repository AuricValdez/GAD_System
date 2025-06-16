<?php
require_once '../config.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['personnel_id'])) {
        throw new Exception('Personnel ID is required');
    }

    $personnelId = intval($_GET['personnel_id']);

    // Query to get personnel details including academic rank and hourly rate
    $query = "SELECT p.id, p.name, p.academic_rank, ar.monthly_salary, 
              ROUND(ar.monthly_salary / 176, 2) as hourly_rate 
              FROM personnel p
              LEFT JOIN academic_ranks ar ON p.academic_rank = ar.academic_rank
              WHERE p.id = ?";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$personnelId]);
    $details = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($details) {
        // Log the details for debugging
        error_log("Personnel details for ID $personnelId: " . json_encode($details));
        
        echo json_encode([
            'status' => 'success',
            'data' => $details
        ]);
    } else {
        throw new Exception('Personnel not found');
    }

} catch (Exception $e) {
    error_log("Error in get_personnel_details.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 