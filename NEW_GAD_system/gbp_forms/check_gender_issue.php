<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get gender issue and campus
if (!isset($_GET['issue']) || empty($_GET['issue'])) {
    echo json_encode(['success' => false, 'message' => 'Gender issue is required']);
    exit();
}

$genderIssue = $_GET['issue'];
$campus = $_SESSION['campus']; // User's campus from session

// Check if we're in edit mode (exclude current entry from check)
$currentId = isset($_GET['current_id']) ? intval($_GET['current_id']) : null;

// Include database configuration
require_once '../config.php';

try {
    // SQL query base
    $sql = "SELECT COUNT(*) as count FROM gpb_entries WHERE gender_issue = ? AND campus = ?";
    $params = [$genderIssue, $campus];
    $types = "ss";
    
    // If in edit mode, exclude the current entry
    if ($currentId) {
        $sql .= " AND id != ?";
        $params[] = $currentId;
        $types .= "i";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $isUsed = ($row['count'] > 0);
    
    $stmt->close();
    
    echo json_encode([
        'success' => true, 
        'isUsed' => $isUsed
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 