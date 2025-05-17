<?php
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You must be logged in to access this data'
    ]);
    exit;
}

// Get parameters from request
$campus = isset($_GET['campus']) ? trim($_GET['campus']) : '';
$year = isset($_GET['year']) ? trim($_GET['year']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$position = isset($_GET['position']) ? trim($_GET['position']) : '';

// Validate parameters
if (empty($campus) || empty($year)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Campus and year parameters are required'
    ]);
    exit;
}

try {
    // Include database connection file
    require_once 'db_connection.php';
    
    // Get connection
    $conn = getConnection();
    
    // First, get all proposals regardless of having a narrative match
    // This will show data even if there's not an exact match
    $query = "SELECT p.id, p.activity as activity_title, p.program, p.project, p.quarter 
              FROM ppas_forms p
              WHERE p.campus = :campus AND p.year = :year";
    
    // Add search condition if provided
    if (!empty($search)) {
        $query .= " AND p.activity LIKE :search";
    }
    
    $query .= " ORDER BY p.activity ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':campus', $campus);
    $stmt->bindParam(':year', $year);
    
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam);
    }
    
    $stmt->execute();
    $proposals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Optionally, you could add a field to indicate if there's a narrative match
    foreach ($proposals as &$proposal) {
        // Check if there's a matching narrative entry
        $checkQuery = "SELECT COUNT(*) as count FROM narrative_entries 
                      WHERE campus = :campus AND year = :year 
                      AND title LIKE :title";
        
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindParam(':campus', $campus);
        $checkStmt->bindParam(':year', $year);
        
        // Use LIKE with % at the beginning and end for more flexible matching
        $titleSearch = '%' . trim($proposal['activity_title']) . '%';
        $checkStmt->bindParam(':title', $titleSearch);
        
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        $proposal['has_narrative'] = ($result['count'] > 0);
    }
    
    // Return the proposals
    echo json_encode([
        'status' => 'success',
        'data' => $proposals
    ]);
    
} catch (PDOException $e) {
    // Log the error
    error_log("Error fetching proposals: " . $e->getMessage());
    
    // Return an error response
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 