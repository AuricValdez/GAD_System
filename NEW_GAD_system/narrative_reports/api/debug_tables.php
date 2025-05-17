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
    // Get database connection
    $conn = isset($pdo) ? $pdo : getConnection();
    
    // 1. Get all tables in the database
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 2. Get structure of the narrative table
    $narrativeColumns = [];
    try {
        $stmt = $conn->query("DESCRIBE narrative");
        $narrativeColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting narrative structure: " . $e->getMessage());
    }
    
    // 3. Try to find ppas-related tables and their structure
    $ppasRelatedTables = [];
    foreach ($tables as $table) {
        if (stripos($table, 'ppas') !== false) {
            try {
                $stmt = $conn->query("DESCRIBE `$table`");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Check if this table has an activity_title column
                $hasActivityTitle = false;
                foreach ($columns as $column) {
                    if ($column['Field'] === 'activity_title') {
                        $hasActivityTitle = true;
                        break;
                    }
                }
                
                $ppasRelatedTables[$table] = [
                    'columns' => $columns,
                    'has_activity_title' => $hasActivityTitle
                ];
            } catch (Exception $e) {
                error_log("Error getting structure for $table: " . $e->getMessage());
            }
        }
    }
    
    // 4. Get sample data from narrative table
    $narrativeData = [];
    try {
        $stmt = $conn->query("SELECT * FROM narrative LIMIT 1");
        $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting narrative sample: " . $e->getMessage());
    }
    
    // 5. Check if we can find the corresponding ppas entry for the sample
    $ppasData = null;
    if (!empty($narrativeData) && isset($narrativeData['ppas_form_id'])) {
        $ppas_form_id = $narrativeData['ppas_form_id'];
        
        foreach ($ppasRelatedTables as $tableName => $tableInfo) {
            try {
                $stmt = $conn->prepare("SELECT * FROM `$tableName` WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $ppas_form_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $ppasData = [
                        'table' => $tableName,
                        'data' => $result
                    ];
                    break;
                }
            } catch (Exception $e) {
                error_log("Error querying $tableName: " . $e->getMessage());
            }
        }
    }
    
    // Return the diagnostic information
    echo json_encode([
        'status' => 'success',
        'database_info' => [
            'all_tables' => $tables,
            'narrative_structure' => $narrativeColumns,
            'ppas_related_tables' => $ppasRelatedTables,
            'narrative_sample' => $narrativeData,
            'matching_ppas_data' => $ppasData
        ]
    ]);

} catch (Exception $e) {
    error_log("Debug error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching database information: ' . $e->getMessage(),
        'code' => 'SERVER_ERROR'
    ]);
} 