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

// Check if field is provided
if (!isset($_GET['field']) || empty($_GET['field'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Field parameter is required'
    ]);
    exit();
}

// Check if query term is provided
if (!isset($_GET['query'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Query parameter is required'
    ]);
    exit();
}

// Validate field parameter (only allow specific fields)
$field = $_GET['field'];
$allowedFields = ['program', 'project', 'activity'];
if (!in_array($field, $allowedFields)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid field parameter'
    ]);
    exit();
}

$query = $_GET['query'];
$userCampus = $_SESSION['username'];
$isCentral = ($userCampus === 'Central');

// Get the optional ID parameter (for edit mode)
$currentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get the current value for comparison
$currentValue = isset($_GET['current_value']) ? trim($_GET['current_value']) : '';

// Include database configuration
require_once '../config.php';

try {
    $suggestions = [];
    $currentValueFound = false;
    $currentActivityValue = ''; // Will store the actual value from database
    
    // If we're in edit mode for activities, get the current activity first to ensure it's always included
    if ($field === 'activity' && $currentId > 0 && !empty($currentValue)) {
        // Get the activity for the current entry being edited
        if ($isCentral) {
            $getCurrentSql = "SELECT activity FROM ppas_forms WHERE id = ? AND campus = 'Central' LIMIT 1";
            $getCurrentStmt = $conn->prepare($getCurrentSql);
            $getCurrentStmt->bind_param("i", $currentId);
        } else {
            $getCurrentSql = "SELECT activity FROM ppas_forms WHERE id = ? AND campus = ? LIMIT 1";
            $getCurrentStmt = $conn->prepare($getCurrentSql);
            $getCurrentStmt->bind_param("is", $currentId, $userCampus);
        }
        
        $getCurrentStmt->execute();
        $getCurrentResult = $getCurrentStmt->get_result();
        
        if ($getCurrentRow = $getCurrentResult->fetch_assoc()) {
            // Store the actual activity value from database
            $currentActivityValue = $getCurrentRow['activity'];
            
            // Add the current activity to the suggestions first
            $suggestions[] = [
                'value' => $currentActivityValue,
                'isDuplicate' => false, // Not a duplicate since it's the current one
                'isCurrent' => true
            ];
            $currentValueFound = true;
        }
        
        $getCurrentStmt->close();
    }
    
    // Now get regular suggestions
    if ($isCentral) {
        // For Central user, fetch from all campuses
        $sql = "SELECT DISTINCT $field FROM ppas_forms WHERE $field LIKE ? LIMIT 10";
        $stmt = $conn->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $stmt->bind_param("s", $searchTerm);
    } else {
        // For campus users, fetch only from their campus
        $sql = "SELECT DISTINCT $field FROM ppas_forms WHERE campus = ? AND $field LIKE ? LIMIT 10";
        $stmt = $conn->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $stmt->bind_param("ss", $userCampus, $searchTerm);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    // Process the results from the database
    while ($row = $result->fetch_assoc()) {
        if (!empty($row[$field])) {
            // Skip if this value matches the current activity that we already added
            if ($currentValueFound && $row[$field] === $currentActivityValue) {
                continue;
            }
            
            // For activity field, check if it already exists in the database for the current campus
            $isDuplicate = false;
            if ($field === 'activity') {
                // If in edit mode and there's a current ID, exclude it from the duplicate check
                if ($currentId > 0) {
                    $checkSql = $isCentral ? 
                        "SELECT COUNT(*) as count FROM ppas_forms WHERE campus = 'Central' AND activity = ? AND id != ?" :
                        "SELECT COUNT(*) as count FROM ppas_forms WHERE campus = ? AND activity = ? AND id != ?";
                    
                    $checkStmt = $conn->prepare($checkSql);
                    
                    if ($isCentral) {
                        $checkStmt->bind_param("si", $row[$field], $currentId);
                    } else {
                        $checkStmt->bind_param("ssi", $userCampus, $row[$field], $currentId);
                    }
                } else {
                    $checkSql = $isCentral ? 
                        "SELECT COUNT(*) as count FROM ppas_forms WHERE campus = 'Central' AND activity = ?" :
                        "SELECT COUNT(*) as count FROM ppas_forms WHERE campus = ? AND activity = ?";
                    
                    $checkStmt = $conn->prepare($checkSql);
                    
                    if ($isCentral) {
                        $checkStmt->bind_param("s", $row[$field]);
                    } else {
                        $checkStmt->bind_param("ss", $userCampus, $row[$field]);
                    }
                }
                
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                $checkRow = $checkResult->fetch_assoc();
                $isDuplicate = $checkRow['count'] > 0;
                $checkStmt->close();
            }
            
            $suggestions[] = [
                'value' => $row[$field],
                'isDuplicate' => $isDuplicate,
                'isCurrent' => false
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'suggestions' => $suggestions
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