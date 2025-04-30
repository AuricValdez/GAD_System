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
    
    // Get all narratives matching the criteria
    $sql = "SELECT * FROM narrative WHERE campus = :campus";
    $params = [':campus' => $campus];
    
    if ($year) {
        $sql .= " AND YEAR(created_at) = :year";
        $params[':year'] = $year;
    }
    
    if (!empty($search)) {
        $sql .= " AND (implementing_office LIKE :search 
                OR partner_agency LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $narratives = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Found " . count($narratives) . " narrative records matching criteria");
    
    // Prepare the final proposals array
    $proposals = [];
    
    // For each narrative, try to get the matching ppas record
    foreach ($narratives as $narrative) {
        $ppasFormId = $narrative['ppas_form_id'];
        $activityValue = null;
        
        // Log each narrative record's ppas_form_id
        error_log("Narrative record ID {$narrative['id']} has ppas_form_id: " . ($ppasFormId ? $ppasFormId : "NULL"));
        
        // Only try to get activity if ppasTable was found and ppasFormId exists
        if ($ppasTable && $ppasFormId) {
            try {
                $stmt = $conn->prepare("SELECT id, activity FROM `$ppasTable` WHERE id = :id");
                $stmt->execute([':id' => $ppasFormId]);
                $ppasRecord = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($ppasRecord && !empty($ppasRecord['activity'])) {
                    $activityValue = $ppasRecord['activity'];
                    error_log("Found activity for ppas_form_id $ppasFormId: $activityValue");
                } else {
                    error_log("No matching record found in $ppasTable for ppas_form_id $ppasFormId");
                }
            } catch (Exception $e) {
                error_log("Error querying $ppasTable for id $ppasFormId: " . $e->getMessage());
            }
        }
        
        // If no activity was found, use implementing_office as fallback
        if (!$activityValue) {
            $activityValue = $narrative['implementing_office'] ?: 'Untitled Activity';
            error_log("Using implementing_office as fallback: $activityValue");
        }
        
        // Add to proposals array
        $proposals[] = [
            'id' => $ppasFormId,  // This should match the ppas_form_id
            'activity_title' => $activityValue,  // Keep this field name for consistency with frontend
            'campus' => $narrative['campus'],
            'year' => $year
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