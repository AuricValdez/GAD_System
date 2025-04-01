<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Include database connection
include_once '../includes/db_connection.php';

// Log access for debugging
file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - User: " . $_SESSION['username'] . " accessed get_ppas_forms.php\n", FILE_APPEND);

try {
    // Test database connection
    if (!isset($conn)) {
        throw new Exception("Database connection not established");
    }

    file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - DB Connection successful\n", FILE_APPEND);

    // Check if ppas_forms table exists
    $tableCheckQuery = "SHOW TABLES LIKE 'ppas_forms'";
    $tableCheckStmt = $conn->prepare($tableCheckQuery);
    $tableCheckStmt->execute();
    $tableExists = $tableCheckStmt->rowCount() > 0;

    if (!$tableExists) {
        throw new Exception("The ppas_forms table does not exist in the database");
    }

    file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - Table ppas_forms exists\n", FILE_APPEND);

    // Get the structure of the ppas_forms table to confirm columns
    $tableStructureQuery = "DESCRIBE ppas_forms";
    $tableStructureStmt = $conn->prepare($tableStructureQuery);
    $tableStructureStmt->execute();
    $columns = $tableStructureStmt->fetchAll(PDO::FETCH_COLUMN);
    
    file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - Table columns: " . implode(", ", $columns) . "\n", FILE_APPEND);

    // Count records in ppas_forms table
    $countQuery = "SELECT COUNT(*) as total FROM ppas_forms";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute();
    $countRow = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalRecords = $countRow['total'];

    file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - Total records found: " . $totalRecords . "\n", FILE_APPEND);

    // Get the user's campus
    $campus = $_SESSION['campus_id'] ?? null;

    // Modified query to create a title field from project, program, and activity
    $sql = "SELECT 
                id, 
                CONCAT(project, ' - ', program, ' - ', activity) AS title,
                activity,
                project,
                program,
                location,
                CONCAT(start_date, ' to ', end_date) AS date_range,
                CONCAT(start_time, ' - ', end_time) AS time_range
            FROM 
                ppas_forms";
    
    // Filter by campus if set
    if ($campus) {
        $sql .= " WHERE campus = :campus";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':campus', $campus);
    } else {
        $stmt = $conn->prepare($sql);
    }
    
    $stmt->execute();
    $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - Found " . count($forms) . " forms" . ($campus ? " for campus: " . $campus : "") . "\n", FILE_APPEND);

    $response = [
        'status' => 'success',
        'data' => $forms
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Close connection
$conn = null;
?> 