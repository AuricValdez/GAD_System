<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if year and campus are provided
if (!isset($_GET['year']) || !isset($_GET['campus'])) {
    echo json_encode(['success' => false, 'message' => 'Year and campus are required']);
    exit();
}

// Include database configuration
require_once '../config.php';

$year = $_GET['year'];
$campus = $_GET['campus'];

try {
    // Prepare SQL statement to get target data
    $sql = "SELECT total_gaa, total_gad_fund FROM target WHERE year = ? AND campus = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $year, $campus);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No target data found for the selected year and campus']);
        exit();
    }
    
    $targetData = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode([
        'success' => true, 
        'total_gaa' => $targetData['total_gaa'],
        'total_gad_fund' => $targetData['total_gad_fund']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
} 