<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in',
        'code' => 'AUTH_ERROR'
    ]);
    exit();
}

// Include the database connection
require_once('../../includes/db_connection.php');

// Function to get database connection if include fails
if (!function_exists('getConnection')) {
    function getConnection() {
        try {
            $conn = new PDO(
                "mysql:host=localhost;dbname=gad_db;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            return $conn;
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
}

try {
    // Get parameters from request
    $ppasFormId = isset($_GET['ppas_form_id']) ? $_GET['ppas_form_id'] : null;

    // Validate required parameters
    if (!$ppasFormId) {
        echo json_encode([
            'status' => 'error',
            'message' => 'PPAS Form ID is required',
            'code' => 'MISSING_PARAM'
        ]);
        exit();
    }

    // Get database connection
    $conn = isset($pdo) ? $pdo : getConnection();
    
    // Get personnel data
    $sql = "
        SELECT 
            pp.personnel_id,
            pp.role,
            p.name,
            p.gender,
            p.academic_rank
        FROM ppas_personnel pp 
        JOIN personnel p ON pp.personnel_id = p.id
        WHERE pp.ppas_form_id = :ppas_form_id
        ORDER BY pp.role, p.name
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(['ppas_form_id' => $ppasFormId]);
    $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group personnel by role
    $personnel_by_role = [
        'project_leaders' => [],
        'assistant_project_leaders' => [],
        'project_staff' => []
    ];
    
    foreach ($personnel as $person) {
        if ($person['role'] == 'Project Leader') {
            $personnel_by_role['project_leaders'][] = $person;
        } elseif ($person['role'] == 'Assistant Project Leader') {
            $personnel_by_role['assistant_project_leaders'][] = $person;
        } elseif ($person['role'] == 'Staff') {
            $personnel_by_role['project_staff'][] = $person;
        }
    }
    
    // Return the personnel data
    echo json_encode([
        'status' => 'success',
        'data' => $personnel_by_role
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching personnel data: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching personnel data',
        'error' => $e->getMessage()
    ]);
} 