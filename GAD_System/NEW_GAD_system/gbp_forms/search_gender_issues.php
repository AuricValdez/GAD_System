<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get search term and campus
if (!isset($_GET['term']) || empty($_GET['term'])) {
    echo json_encode(['success' => false, 'message' => 'Search term is required']);
    exit();
}

$searchTerm = $_GET['term'];
$campus = $_SESSION['username']; // User's campus from session

// Check if we're in edit mode (exclude current entry from check)
$currentId = isset($_GET['current_id']) ? intval($_GET['current_id']) : null;

// Include database configuration
require_once '../config.php';

try {
    // Search for gender issues in the database - DISTINCT on gender_issue only
    $sql = "SELECT DISTINCT gender_issue FROM gpb_entries 
            WHERE gender_issue LIKE ? 
            ORDER BY gender_issue ASC
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $searchParam = "%" . $searchTerm . "%";
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $issues = [];
    $used_issues = [];
    
    // First, gather all gender issues with no duplicates
    while ($row = $result->fetch_assoc()) {
        $issues[] = [
            'value' => $row['gender_issue'],
            'campus' => '' // Not storing campus as we only want distinct values
        ];
    }
    
    $stmt->close();
    
    // Now check which ones are used by this campus
    if (!empty($issues)) {
        $placeholders = str_repeat('?,', count($issues) - 1) . '?';
        $issueValues = array_column($issues, 'value');
        
        // Build SQL to exclude current entry if in edit mode
        $usedSql = "SELECT DISTINCT gender_issue FROM gpb_entries 
                    WHERE gender_issue IN ($placeholders) 
                    AND campus = ?";
        
        // If editing, exclude the current entry ID
        if ($currentId) {
            $usedSql .= " AND id != ?";
        }
        
        $usedStmt = $conn->prepare($usedSql);
        
        // Create parameter types string and combine parameters
        if ($currentId) {
            // Add one more parameter type for the ID (i)
            $types = str_repeat('s', count($issueValues) + 1) . 'i';
            $params = array_merge($issueValues, [$campus, $currentId]);
        } else {
            $types = str_repeat('s', count($issueValues) + 1);
            $params = array_merge($issueValues, [$campus]);
        }
        
        // Bind parameters using reference trick
        $usedStmt->bind_param($types, ...$params);
        $usedStmt->execute();
        $usedResult = $usedStmt->get_result();
        
        while ($usedRow = $usedResult->fetch_assoc()) {
            $used_issues[] = $usedRow['gender_issue'];
        }
        
        $usedStmt->close();
    }
    
    echo json_encode([
        'success' => true, 
        'issues' => $issues,
        'used_issues' => $used_issues
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 