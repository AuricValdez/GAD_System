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
$narrative_id = isset($_GET['narrative_id']) ? $_GET['narrative_id'] : '';
$campus = isset($_GET['campus']) ? $_GET['campus'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

// Validate parameters
if (empty($narrative_id) || empty($campus) || empty($year)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Narrative ID, campus, and year parameters are required'
    ]);
    exit;
}

try {
    // Check if the ppas_activity_title column exists
    $checkColumnSql = "SHOW COLUMNS FROM narrative_forms LIKE 'ppas_activity_title'";
    $checkStmt = $conn->prepare($checkColumnSql);
    $checkStmt->execute();
    $columnExists = $checkStmt->rowCount() > 0;
    
    // Select all fields from narrative_forms regardless of column existence
    $sql = "SELECT n.*
        FROM narrative_forms n
        JOIN ppas_forms p ON n.ppas_id = p.id
        WHERE n.id = ? AND p.campus = ? AND YEAR(n.created_at) = ?";
    
    // Prepare and execute statement
    $stmt = $conn->prepare($sql);
    $stmt->execute([$narrative_id, $campus, $year]);
    
    // Fetch result
    $narrative = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$narrative) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Narrative not found'
        ]);
        exit;
    }
    
    // Process beneficiaries if needed
    if (isset($narrative['beneficiaries']) && !empty($narrative['beneficiaries'])) {
        $narrative['beneficiaries'] = json_decode($narrative['beneficiaries'], true);
    }
    
    // Process service_agenda if needed
    if (isset($narrative['service_agenda']) && !empty($narrative['service_agenda'])) {
        $narrative['service_agenda'] = json_decode($narrative['service_agenda'], true);
    }
    
    // Process sdg if needed
    if (isset($narrative['sdg']) && !empty($narrative['sdg'])) {
        $narrative['sdg'] = json_decode($narrative['sdg'], true);
    }
    
    // Process tasks if needed
    if (isset($narrative['tasks']) && !empty($narrative['tasks'])) {
        $narrative['tasks'] = json_decode($narrative['tasks'], true);
    }
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'data' => $narrative
    ]);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
} 