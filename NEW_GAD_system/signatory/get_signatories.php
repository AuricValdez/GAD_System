<?php
require_once '../config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    // Get campus from session
    $campus = $_SESSION['username'];
    $isCentral = ($campus === 'Central');
    
    // Check if the signatories table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'signatories'");
    if ($tableCheck->rowCount() == 0) {
        // Table doesn't exist, create it
        $createTableSql = "CREATE TABLE IF NOT EXISTS signatories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name1 VARCHAR(255) NOT NULL,
            gad_head_secretariat VARCHAR(255) NOT NULL,
            name2 VARCHAR(255) NOT NULL,
            vice_chancellor_rde VARCHAR(255) NOT NULL,
            name3 VARCHAR(255) NOT NULL,
            chancellor VARCHAR(255) NOT NULL,
            name4 VARCHAR(255) NOT NULL,
            asst_director_gad VARCHAR(255) NOT NULL,
            name5 VARCHAR(255) NOT NULL,
            head_extension_services VARCHAR(255) NOT NULL,
            name6 VARCHAR(255) DEFAULT NULL,
            vice_chancellor_admin_finance VARCHAR(255) DEFAULT NULL,
            name7 VARCHAR(255) DEFAULT NULL,
            dean VARCHAR(255) DEFAULT 'Dean',
            campus VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($createTableSql);
    }
    
    // Get the current table structure
    $columnCheck = $pdo->query("SHOW COLUMNS FROM signatories");
    $columns = $columnCheck->fetchAll(PDO::FETCH_COLUMN);
    
    // Check if table has the right structure
    $hasNewestStructure = in_array('gad_head_secretariat', $columns) && in_array('name6', $columns) && in_array('name7', $columns);
    
    if (!$hasNewestStructure) {
        // Update table structure if needed
        $pdo->exec("DROP TABLE signatories");
        $createTableSql = "CREATE TABLE signatories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name1 VARCHAR(255) NOT NULL,
            gad_head_secretariat VARCHAR(255) NOT NULL,
            name2 VARCHAR(255) NOT NULL,
            vice_chancellor_rde VARCHAR(255) NOT NULL,
            name3 VARCHAR(255) NOT NULL,
            chancellor VARCHAR(255) NOT NULL,
            name4 VARCHAR(255) NOT NULL,
            asst_director_gad VARCHAR(255) NOT NULL,
            name5 VARCHAR(255) NOT NULL,
            head_extension_services VARCHAR(255) NOT NULL,
            name6 VARCHAR(255) DEFAULT NULL,
            vice_chancellor_admin_finance VARCHAR(255) DEFAULT NULL,
            name7 VARCHAR(255) DEFAULT NULL,
            dean VARCHAR(255) DEFAULT 'Dean',
            campus VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($createTableSql);
    }
    
    // Execute query based on user type
    if ($isCentral) {
        // For Central user, get ALL campus signatories
        $stmt = $pdo->prepare("SELECT id, name1, gad_head_secretariat, name2, vice_chancellor_rde, 
                              name3, chancellor, name4, asst_director_gad, name5, head_extension_services, 
                              name6, vice_chancellor_admin_finance, name7, dean,
                              campus, created_at, updated_at 
                              FROM signatories");
        $stmt->execute();
    } else {
        // For campus-specific users, get only their campus
        $stmt = $pdo->prepare("SELECT id, name1, gad_head_secretariat, name2, vice_chancellor_rde, 
                              name3, chancellor, name4, asst_director_gad, name5, head_extension_services, 
                              name6, vice_chancellor_admin_finance, name7, dean,
                              campus, created_at, updated_at 
                              FROM signatories WHERE campus = ?");
        $stmt->execute([$campus]);
    }
    
    $signatories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no data found for current campus, add default structure
    if (empty($signatories) && !$isCentral) {
        $signatories = [
            [
                'id' => 0,
                'name1' => '',
                'gad_head_secretariat' => 'GAD Head Secretariat',
                'name2' => '',
                'vice_chancellor_rde' => 'Vice Chancellor For Research, Development and Extension',
                'name3' => '',
                'chancellor' => 'Chancellor',
                'name4' => '',
                'asst_director_gad' => 'Assistant Director For GAD Advocacies',
                'name5' => '',
                'head_extension_services' => 'Head of Extension Services',
                'name6' => '',
                'vice_chancellor_admin_finance' => 'Vice Chancellor for Administration and Finance',
                'name7' => '',
                'dean' => 'Dean',
                'campus' => $campus,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    // Debug output
    error_log("get_signatories.php - User: $campus, Central: " . ($isCentral ? 'Yes' : 'No') . ", Record count: " . count($signatories));
    foreach ($signatories as $index => $sig) {
        error_log("  Record $index: ID=" . $sig['id'] . ", Campus=" . $sig['campus']);
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $signatories
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_signatories.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching signatories: ' . $e->getMessage()
    ]);
}
?> 