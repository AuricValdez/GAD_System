<?php
// Start output buffering immediately
ob_start();

// Disable all error reporting and display
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');

try {
    // Check if config file exists
    $configFile = __DIR__ . '/../includes/db_connection.php';
    if (!file_exists($configFile)) {
        throw new Exception('System configuration error. Please contact administrator.');
    }

    require_once $configFile;

    // Verify database constants are defined
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new Exception('Database configuration error. Please contact administrator.');
    }

    if (!isset($_GET['year']) || !isset($_GET['quarter'])) {
        throw new Exception('Missing required parameters');
    }

    $year = $_GET['year'];
    $quarter = $_GET['quarter'];

    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // First check if the year and quarter exist in ppas_forms
    $checkQuery = "SELECT id FROM ppas_forms WHERE year = ? AND quarter = ?";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([$year, $quarter]);
    
    if ($formRow = $checkStmt->fetch()) {
        $ppasId = $formRow['id'];
        
        // Force use of personnel_list table
        $query = "SELECT 
            GROUP_CONCAT(DISTINCT CASE 
                WHEN pp.role = 'project_leader' THEN COALESCE(pl.name, pp.personnel_name, pp.personnel_id) 
            END) as project_leaders,
            GROUP_CONCAT(DISTINCT CASE 
                WHEN pp.role = 'asst_project_leader' THEN COALESCE(pl.name, pp.personnel_name, pp.personnel_id)
            END) as assistant_project_leaders,
            GROUP_CONCAT(DISTINCT CASE 
                WHEN pp.role = 'project_staff' THEN COALESCE(pl.name, pp.personnel_name, pp.personnel_id)
            END) as project_staff
        FROM ppas_personnel pp
        LEFT JOIN personnel_list pl ON CAST(pp.personnel_id AS UNSIGNED) = pl.id
        WHERE pp.ppas_id = ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$ppasId]);
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $response = [
                'success' => true,
                'projectLeaders' => $row['project_leaders'] ?? '',
                'assistantProjectLeaders' => $row['assistant_project_leaders'] ?? '',
                'projectStaff' => $row['project_staff'] ?? '',
                'message' => 'Team data retrieved successfully'
            ];
        } else {
            $response = [
                'success' => true,
                'projectLeaders' => '',
                'assistantProjectLeaders' => '',
                'projectStaff' => '',
                'message' => 'No team members found for the selected period'
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'No PPAS form found for the selected year and quarter'
        ];
    }
} catch (PDOException $e) {
    error_log("Database error in get_project_team.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Database connection error. Please contact administrator.'
    ];
} catch (Exception $e) {
    error_log("Error in get_project_team.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Clear any buffered output
ob_end_clean();

// Send JSON response
echo json_encode($response);
exit; 