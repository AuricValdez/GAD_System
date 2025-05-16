<?php
header('Content-Type: application/json');
session_start();

// Include database connection
require_once '../config.php';

// Get campus from session
$campus = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Add debug logging
error_log("get_all_signatories.php - User campus: " . $campus);

try {
    // First log all available signatories to troubleshoot
    $allQuery = "SELECT id, campus, name1, name3, name4 FROM signatories";
    $allStmt = $pdo->query($allQuery);
    $allSignatories = $allStmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Available signatories in database:");
    foreach ($allSignatories as $sig) {
        error_log("  ID: {$sig['id']}, Campus: {$sig['campus']}, Names: {$sig['name1']}, {$sig['name3']}, {$sig['name4']}");
    }
    
    $query = "SELECT * FROM signatories";
    $params = [];
    
    // If not Central user, filter by campus
    if ($campus !== 'Central') {
        $query .= " WHERE campus = ?";
        $params[] = $campus;
        error_log("Filtering signatories for campus: " . $campus);
    } else {
        error_log("Central user - returning all signatories");
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    $signatories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Query returned " . count($signatories) . " signatories");
    
    if ($signatories && count($signatories) > 0) {
        echo json_encode([
            'status' => 'success',
            'data' => $signatories
        ]);
    } else {
        error_log("No signatories found for campus: " . $campus);
        echo json_encode([
            'status' => 'error',
            'message' => 'No signatories found for ' . ($campus === 'Central' ? 'any campus' : "campus '$campus'")
        ]);
    }
} catch (PDOException $e) {
    error_log("Error fetching all signatories: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 