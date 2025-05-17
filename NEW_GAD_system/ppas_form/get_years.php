<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit();
}

// Include database configuration
require_once '../config.php';

// Get user's campus from session
$userCampus = $_SESSION['username'];
$isCentral = ($userCampus === 'Central');

try {
    // SQL query to fetch years
    if ($isCentral) {
        // If Central user, fetch years for Central campus only
        $query = "SELECT DISTINCT year FROM gpb_entries WHERE campus = 'Central' ORDER BY year DESC";
        $stmt = $conn->prepare($query);
    } else {
        // For campus users, fetch years for their campus only
        $query = "SELECT DISTINCT year FROM gpb_entries WHERE campus = ? ORDER BY year DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $userCampus);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    $years = [];
    while ($row = $result->fetch_assoc()) {
        $years[] = $row['year'];
    }
    
    echo json_encode([
        'success' => true,
        'years' => $years
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    // The connection closure is handled in config.php
}
?> 