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
    // Build the WHERE clause and params array based on filters
    $whereClause = [];
    $params = [];
    $types = '';
    
    // Campus filter - Fixed to include filter for Central campus
    $campus = isset($_GET['campus']) && !empty($_GET['campus']) ? $_GET['campus'] : $_SESSION['campus'];
    if ($campus) {
        $whereClause[] = "campus = ?";
        $params[] = $campus;
        $types .= 's';
    }
    
    // Year filter
    if (isset($_GET['year']) && !empty($_GET['year'])) {
        $whereClause[] = "year = ?";
        $params[] = $_GET['year'];
        $types .= 's';
    }
    
    // Category filter
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $whereClause[] = "category = ?";
        $params[] = $_GET['category'];
        $types .= 's';
    }
    
    // Gender issue filter (partial match)
    if (isset($_GET['gender_issue']) && !empty($_GET['gender_issue'])) {
        $whereClause[] = "gender_issue LIKE ?";
        $params[] = '%' . $_GET['gender_issue'] . '%';
        $types .= 's';
    }
    
    // Status filter
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $whereClause[] = "status = ?";
        $params[] = $_GET['status'];
        $types .= 's';
    }
    
    // Build the SQL query
    $sql = "SELECT id, year, campus, gender_issue, category, cause_of_issue, gad_objective, gad_budget, status, feedback 
            FROM gpb_entries";
    
    if (!empty($whereClause)) {
        $sql .= " WHERE " . implode(" AND ", $whereClause);
    }
    
    // Order by year desc, then gender_issue
    $sql .= " ORDER BY year DESC, gender_issue ASC";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $entries = [];
    
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true, 
        'entries' => $entries,
        'count' => count($entries)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
} 