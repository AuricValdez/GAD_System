<?php
require_once '../config.php';

// Ensure we're sending JSON response
header('Content-Type: application/json');

// Prevent any PHP errors from being output
error_reporting(0);
ini_set('display_errors', 0);

try {
    // Debug: Get database name and table info
    $dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();
    error_log('Current database: ' . $dbName);
    
    // Debug: Get table info
    $tableInfo = $pdo->query("SHOW TABLES LIKE 'academic_ranks'")->fetch(PDO::FETCH_ASSOC);
    error_log('Table info: ' . print_r($tableInfo, true));
    
    // Debug: Get full table structure
    $tableStructure = $pdo->query("SHOW CREATE TABLE academic_ranks")->fetch(PDO::FETCH_ASSOC);
    error_log('Full table structure: ' . print_r($tableStructure, true));
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // If no JSON data, try getting from POST
    if (empty($data)) {
        $data = $_POST;
    }
    
    // Debug: Log the received data
    error_log('Received data: ' . print_r($data, true));
    
    // Map the received field names to the expected names
    $mappedData = [
        'id' => $data['editId'],
        'academic_rank' => $data['academicRank'],
        'salary_grade' => $data['salaryGrade'],
        'monthly_salary' => $data['monthlySalary']
    ];
    
    // Debug: Log the mapped data
    error_log('Mapped data: ' . print_r($mappedData, true));
    
    // Validate required fields
    if (!isset($mappedData['id']) || !isset($mappedData['academic_rank']) || 
        !isset($mappedData['salary_grade']) || !isset($mappedData['monthly_salary'])) {
        throw new Exception('Missing required fields: ' . print_r($mappedData, true));
    }
    
    // Check for duplicate academic rank (case-insensitive) excluding the current record
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM academic_ranks WHERE LOWER(academic_rank) = LOWER(?) AND id != ?");
    $stmt->execute([$mappedData['academic_rank'], $mappedData['id']]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Academic rank already exists'
        ]);
        exit;
    }
    
    // Update academic rank - excluding hourly_rate as it's a generated column
    $sql = "UPDATE academic_ranks 
            SET academic_rank = ?, 
                salary_grade = ?, 
                monthly_salary = ? 
            WHERE id = ?";
            
    error_log('SQL Query: ' . $sql);
    error_log('Values: ' . print_r([
        $mappedData['academic_rank'],
        $mappedData['salary_grade'],
        $mappedData['monthly_salary'],
        $mappedData['id']
    ], true));
    
    $stmt = $pdo->prepare($sql);
    
    $result = $stmt->execute([
        $mappedData['academic_rank'],
        $mappedData['salary_grade'],
        $mappedData['monthly_salary'],
        $mappedData['id']
    ]);
    
    if (!$result) {
        throw new Exception('Failed to update record: ' . print_r($stmt->errorInfo(), true));
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Academic rank updated successfully'
    ]);
    
} catch(Exception $e) {
    error_log('Error in save_academic_rank.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
