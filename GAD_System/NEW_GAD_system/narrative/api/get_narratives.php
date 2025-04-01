<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in'
    ]);
    exit;
}

// Include database connection
require_once '../../includes/db_connect.php';

// Get parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$campus = isset($_GET['campus']) ? $_GET['campus'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

// Validate required parameters
if (empty($campus) || empty($year)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Campus and year parameters are required'
    ]);
    exit;
}

try {
    // Check if the ppas_activity_title column exists
    $checkColumnSql = "SHOW COLUMNS FROM narrative_forms LIKE 'ppas_activity_title'";
    $checkStmt = $conn->prepare($checkColumnSql);
    $checkStmt->execute();
    $columnExists = $checkStmt->rowCount() > 0;
    
    // Build SQL query based on whether the column exists
    if ($columnExists) {
        $sql = "SELECT n.id, n.activity_title, n.implementing_office, n.created_at, 
                n.ppas_activity_title as activity_reference
                FROM narrative_forms n
                JOIN ppas_forms p ON n.ppas_id = p.id
                WHERE p.campus = ? 
                AND YEAR(n.created_at) = ?";
        
        $params = [$campus, $year];
        
        // Add search condition if provided
        if (!empty($search)) {
            $sql .= " AND (n.activity_title LIKE ? OR n.implementing_office LIKE ? OR n.ppas_activity_title LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $sql .= " ORDER BY n.created_at DESC";
    } else {
        // Fallback query without ppas_activity_title
        $sql = "SELECT n.id, n.activity_title, n.implementing_office, n.created_at, 
                n.ppas_id as activity_reference
                FROM narrative_forms n
                JOIN ppas_forms p ON n.ppas_id = p.id
                WHERE p.campus = ? 
                AND YEAR(n.created_at) = ?";
        
        $params = [$campus, $year];
        
        // Add search condition if provided
        if (!empty($search)) {
            $sql .= " AND (n.activity_title LIKE ? OR n.implementing_office LIKE ? OR CAST(n.ppas_id AS CHAR) LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $sql .= " ORDER BY n.created_at DESC";
    }
    
    // Execute the query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    // Fetch results
    $narratives = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'data' => $narratives
    ]);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
} 