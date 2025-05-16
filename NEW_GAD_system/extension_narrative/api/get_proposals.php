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
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $campus = isset($_GET['campus']) ? $_GET['campus'] : null;
    $year = isset($_GET['year']) ? $_GET['year'] : null;

    // Validate required parameters
    if (!$campus || !$year) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Campus and year are required parameters',
            'code' => 'MISSING_PARAM'
        ]);
        exit();
    }

    // Get database connection
    $conn = isset($pdo) ? $pdo : getConnection();
    
    // First check what tables exist in the database
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    error_log("Database tables: " . implode(", ", $tables));
    
    // Look for ppas table (could be ppas_form, ppas_forms, etc.)
    $ppasTable = null;
    foreach ($tables as $table) {
        if (stripos($table, 'ppas') !== false) {
            try {
                // Check if this table has id column
                $stmt = $conn->query("DESCRIBE `$table`");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                if (in_array('id', $columns)) {
                    $ppasTable = $table;
                    error_log("Found PPAS table: $ppasTable with columns: " . implode(", ", $columns));
                    break;
                }
            } catch (Exception $e) {
                error_log("Error checking table $table: " . $e->getMessage());
            }
        }
    }
    
    // If no PPAS table found, log this important fact
    if (!$ppasTable) {
        error_log("CRITICAL: No PPAS table found in the database!");
    }
    
    // Get all narrative entries matching the criteria
    $sql = "SELECT * FROM narrative_entries WHERE campus = :campus";
    $params = [':campus' => $campus];
    
    if ($year) {
        $sql .= " AND year = :year";
        $params[':year'] = $year;
    }
    
    if (!empty($search)) {
        $sql .= " AND (title LIKE :search 
                OR background LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $narratives = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Found " . count($narratives) . " narrative_entries records matching criteria");
    
    // Prepare the final proposals array
    $proposals = [];
    
    // For each narrative entry, add to proposals
    foreach ($narratives as $narrative) {
        // Get PPAS form if it exists
        $ppasFormId = $narrative['id']; // Default to narrative ID as fallback
        $activityTitle = $narrative['title'] ?: 'Untitled Activity';
        
        // Add to proposals array
        $proposals[] = [
            'id' => $ppasFormId,
            'activity_title' => $activityTitle, 
            'campus' => $narrative['campus'],
            'year' => $narrative['year']
        ];
    }
    
    // Return success response with debug info
    echo json_encode([
        'status' => 'success',
        'data' => $proposals,
        'ppas_table_found' => $ppasTable ? $ppasTable : 'none',
        'narrative_count' => count($narratives),
        'proposal_count' => count($proposals)
    ]);

} catch (Exception $e) {
    // Log the error and return an error response
    error_log("Error fetching proposals: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching proposals: ' . $e->getMessage(),
        'code' => 'SERVER_ERROR'
    ]);
} 