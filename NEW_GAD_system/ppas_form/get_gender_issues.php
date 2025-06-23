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

// Check if year is provided
if (!isset($_GET['year']) || empty($_GET['year'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Year parameter is required'
    ]);
    exit();
}

// Check if quarter is provided
if (!isset($_GET['quarter']) || empty($_GET['quarter'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Quarter parameter is required'
    ]);
    exit();
}

// Get parameters
$year = $_GET['year'];
$quarter = $_GET['quarter']; // We'll capture quarter even if not used in the query
$userCampus = $_SESSION['username'];
$isCentral = ($userCampus === 'Central');

// Include database configuration
require_once '../config.php';

try {
    // SQL query to fetch gender issues with their status
    if ($isCentral) {
        // If Central user, fetch gender issues for Central campus only
        $query = "SELECT id, gender_issue, status FROM gpb_entries WHERE campus = 'Central' AND year = ? GROUP BY gender_issue, status ORDER BY gender_issue";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $year);
    } else {
        // For campus users, fetch gender issues for their campus and selected year
        $query = "SELECT id, gender_issue, status FROM gpb_entries WHERE campus = ? AND year = ? GROUP BY gender_issue, status ORDER BY gender_issue";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $userCampus, $year);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    $issues = [];
    while ($row = $result->fetch_assoc()) {
        $issues[] = [
            'id' => $row['id'],
            'gender_issue' => $row['gender_issue'],
            'status' => $row['status']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'issues' => $issues
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