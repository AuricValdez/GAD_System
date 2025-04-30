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
    // Get campus parameter
    $campus = isset($_GET['campus']) ? $_GET['campus'] : $_SESSION['campus'];
    
    // Prepare the SQL to get distinct years
    $sql = "SELECT DISTINCT year FROM target";
    
    // Add campus filter if not Central
    if ($campus && $campus !== 'Central') {
        $sql .= " WHERE campus = ?";
    }
    
    // Order by year descending
    $sql .= " ORDER BY year DESC";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters if needed
    if ($campus && $campus !== 'Central') {
        $stmt->bind_param('s', $campus);
    }
    
    // Execute the statement
    $stmt->execute();
    $result = $stmt->get_result();
    
    $years = [];
    
    while ($row = $result->fetch_assoc()) {
        $years[] = $row['year'];
    }
    
    $stmt->close();
    
    echo json_encode(['success' => true, 'years' => $years]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
} 