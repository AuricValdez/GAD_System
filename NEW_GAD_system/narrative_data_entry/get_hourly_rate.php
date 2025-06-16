<?php
session_start();
$rootDir = dirname(dirname(__FILE__));
require_once($rootDir . '/includes/db_connection.php');

header('Content-Type: application/json');

if (!isset($_GET['academic_rank'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No academic rank provided',
        'hourly_rate' => '0.00'
    ]);
    exit;
}

$academic_rank = $_GET['academic_rank'];

try {
    // Get monthly salary from academic_ranks table
    $query = "SELECT monthly_salary FROM academic_ranks WHERE academic_rank = :academic_rank";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':academic_rank', $academic_rank, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Calculate hourly rate (monthly salary / 176 standard hours)
        $monthly_salary = floatval($row['monthly_salary']);
        $hourly_rate = $monthly_salary / 176;
        
        echo json_encode([
            'status' => 'success',
            'hourly_rate' => number_format($hourly_rate, 2, '.', ''),
            'monthly_salary' => number_format($monthly_salary, 2, '.', '')
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Academic rank not found',
            'hourly_rate' => '0.00'
        ]);
    }
} catch (Exception $e) {
    error_log("Error in get_hourly_rate.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred',
        'hourly_rate' => '0.00'
    ]);
} 