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

$userCampus = $_SESSION['username'];

// Include database configuration
require_once '../config.php';

try {
    // SQL query to fetch years from ppas_forms for the user's campus
    $query = "SELECT DISTINCT year FROM ppas_forms WHERE campus = ? ORDER BY year DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userCampus);
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