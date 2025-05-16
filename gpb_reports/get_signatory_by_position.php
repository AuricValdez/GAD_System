<?php
header('Content-Type: application/json');
session_start();

// Include database connection
require_once '../config.php';

// Check if position parameter is provided
if (!isset($_GET['position'])) {
    echo json_encode(['status' => 'error', 'message' => 'Position parameter is required']);
    exit;
}

$position = $_GET['position'];
$campus = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// If campus is 'Central', use default campus (first one found)
if ($campus === 'Central') {
    try {
        // Get the first campus with signatories
        $stmt = $pdo->query("SELECT DISTINCT campus FROM signatories LIMIT 1");
        $firstCampus = $stmt->fetchColumn();
        
        if ($firstCampus) {
            $campus = $firstCampus;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No campuses found with signatories']);
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error getting first campus: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

try {
    $columnMap = [
        'GAD Head Secretariat' => ['name1', 'gad_head_secretariat'],
        'Vice Chancellor For Research, Development and Extension' => ['name2', 'vice_chancellor_rde'],
        'Chancellor' => ['name3', 'chancellor'],
        'Assistant Director, GAD' => ['name4', 'asst_director_gad'],
        'Head of Extension Services' => ['name5', 'head_extension_services']
    ];
    
    // Check if position exists in our mapping
    if (!isset($columnMap[$position])) {
        echo json_encode(['status' => 'error', 'message' => 'Unknown position: ' . $position]);
        exit;
    }
    
    // Get the column names for this position
    $nameColumn = $columnMap[$position][0];
    $positionColumn = $columnMap[$position][1];
    
    // Query to get signatory for the specified position and campus
    $stmt = $pdo->prepare("SELECT $nameColumn as name, $positionColumn as position FROM signatories WHERE campus = ? LIMIT 1");
    $stmt->execute([$campus]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'data' => $result
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'No signatory found for position: ' . $position . ' in campus: ' . $campus
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Error fetching signatory by position: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 