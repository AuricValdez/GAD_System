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

// Check if activity value is provided
if (!isset($_GET['activity']) || empty($_GET['activity'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Activity parameter is required'
    ]);
    exit();
}

$activity = trim($_GET['activity']);
$userCampus = $_SESSION['username'];
$isCentral = ($userCampus === 'Central');

// Get the optional ID parameter (for edit mode)
$currentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Include database configuration
require_once '../config.php';

try {
    // Check if activity already exists, excluding the current entry being edited
    if ($isCentral) {
        // For Central user, check only within Central campus
        $sql = $currentId > 0 
            ? "SELECT COUNT(*) as count FROM ppas_forms WHERE campus = 'Central' AND activity = ? AND id != ?"
            : "SELECT COUNT(*) as count FROM ppas_forms WHERE campus = 'Central' AND activity = ?";
            
        $stmt = $conn->prepare($sql);
        
        if ($currentId > 0) {
            $stmt->bind_param("si", $activity, $currentId);
        } else {
            $stmt->bind_param("s", $activity);
        }
    } else {
        // For campus users, check only their campus
        $sql = $currentId > 0 
            ? "SELECT COUNT(*) as count FROM ppas_forms WHERE campus = ? AND activity = ? AND id != ?"
            : "SELECT COUNT(*) as count FROM ppas_forms WHERE campus = ? AND activity = ?";
            
        $stmt = $conn->prepare($sql);
        
        if ($currentId > 0) {
            $stmt->bind_param("ssi", $userCampus, $activity, $currentId);
        } else {
            $stmt->bind_param("ss", $userCampus, $activity);
        }
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $exists = $row['count'] > 0;
    
    echo json_encode([
        'success' => true,
        'exists' => $exists,
        'message' => $exists ? 'This activity already exists in your campus database.' : ''
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