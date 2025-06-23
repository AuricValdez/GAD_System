<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Include database configuration
require_once '../config.php';

try {
    // Get campus parameter, default to logged in user's campus
    $campus = isset($_GET['campus']) ? $_GET['campus'] : $_SESSION['campus'];
    
    // Handle Central user differently - they can see all campuses
    $isCentral = ($_SESSION['campus'] === 'Central');
    
    // Build the query
    $sql = "SELECT COUNT(*) as rejected_count FROM gpb_entries WHERE status = 'Rejected'";
    
    // Add campus filter if not Central
    if (!$isCentral && $campus !== 'Central') {
        $sql .= " AND campus = ?";
    }
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    
    // Bind campus parameter if needed
    if (!$isCentral && $campus !== 'Central') {
        $stmt->bind_param('s', $campus);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $count = $row['rejected_count'];
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'count' => $count,
        'campus' => $campus
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}
?> 