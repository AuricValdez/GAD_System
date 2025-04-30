<?php
header('Content-Type: application/json');
session_start();

// Include database connection
require_once '../config.php';

// Get campus from session
$campus = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Get requested campus parameter (for central users)
$selectedCampus = isset($_GET['campus']) ? $_GET['campus'] : '';
error_log("get_all_signatories.php - User: $campus, Selected Campus: $selectedCampus");

try {
    $query = "SELECT * FROM signatories";
    $params = [];
    
    // If not Central user, filter by campus from session
    if ($campus !== 'Central') {
        $query .= " WHERE campus = ?";
        $params[] = $campus;
        error_log("Regular user filter: $campus");
    } 
    // If Central user with campus parameter, filter by that campus
    else if (!empty($selectedCampus)) {
        $query .= " WHERE campus = ?";
        $params[] = $selectedCampus;
        error_log("Central user with filter: $selectedCampus");
    }
    // Otherwise, Central user with no filter - will get all signatories
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    $signatories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($signatories && count($signatories) > 0) {
        error_log("Found " . count($signatories) . " signatories");
        echo json_encode([
            'status' => 'success',
            'data' => $signatories
        ]);
    } else {
        $campusName = ($campus === 'Central' && !empty($selectedCampus)) ? $selectedCampus : 
                      ($campus === 'Central' ? 'any campus' : "campus '$campus'");
        error_log("No signatories found for $campusName");
        echo json_encode([
            'status' => 'error',
            'message' => 'No signatories found for ' . $campusName
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