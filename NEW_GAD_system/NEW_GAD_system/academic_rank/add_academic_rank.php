<?php
require_once '../config.php';
header('Content-Type: application/json');

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // If no JSON data, try getting from POST
    if (empty($data)) {
        $data = $_POST;
    }
    
    // Debug: Log received data
    error_log('Received data: ' . print_r($data, true));

    // Validate required fields
    if (!isset($data['academicRank']) || !isset($data['salaryGrade']) || !isset($data['monthlySalary'])) {
        throw new Exception('Missing required fields');
    }

    // Check for duplicate academic rank (case-insensitive)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM academic_ranks WHERE LOWER(academic_rank) = LOWER(?)");
    $stmt->execute([$data['academicRank']]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Academic rank already exists'
        ]);
        exit;
    }

    // Insert new academic rank
    $stmt = $pdo->prepare("INSERT INTO academic_ranks (academic_rank, salary_grade, monthly_salary) 
                          VALUES (?, ?, ?)");
    
    $result = $stmt->execute([
        $data['academicRank'],
        $data['salaryGrade'],
        $data['monthlySalary']
    ]);
    
    if (!$result) {
        throw new Exception('Failed to insert record: ' . print_r($stmt->errorInfo(), true));
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Academic rank added successfully'
    ]);

} catch (Exception $e) {
    error_log('Error in add_academic_rank.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
