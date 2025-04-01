<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');
header('Content-Type: application/json');

try {
    if (!$pdo) {
        throw new PDOException("Database connection failed");
    }
    
    $stmt = $pdo->prepare("SELECT id, academic_rank, salary_grade, monthly_salary, hourly_rate FROM academic_ranks ORDER BY academic_rank ASC");
    $stmt->execute();
    $ranks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($ranks === false) {
        throw new PDOException("Failed to fetch results");
    }
    
    echo json_encode($ranks);
} catch (PDOException $e) {
    error_log("Database error in get_academic_ranks.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error in get_academic_ranks.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
