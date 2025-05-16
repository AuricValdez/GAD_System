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
    $position = isset($_GET['position']) ? $_GET['position'] : null;

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
        echo json_encode([
            'status' => 'error',
            'message' => 'PPAS table not found in database',
            'code' => 'TABLE_NOT_FOUND'
        ]);
        exit();
    }
    
    // Get all PPAS activities matching the criteria
    $sql = "SELECT * FROM `$ppasTable` WHERE campus = :campus";
    $params = [':campus' => $campus];
    
    if ($year) {
        $sql .= " AND year = :year";
        $params[':year'] = $year;
    }
    
    if (!empty($search)) {
        $sql .= " AND activity LIKE :search";
        $params[':search'] = '%' . $search . '%';
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $ppasRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Found " . count($ppasRecords) . " PPAS records matching criteria");
    
    // Prepare the final proposals array
    $proposals = [];
    
    // For each PPAS record, check if there's a matching narrative entry
    foreach ($ppasRecords as $ppas) {
        $ppasId = $ppas['id'];
        $activityTitle = $ppas['activity'] ?? 'Untitled Activity';
        $ppasYear = $ppas['year']; // Explicitly get year from ppas_forms
        
        error_log("Processing PPAS record ID $ppasId with activity: $activityTitle, year: $ppasYear");
        
        // Check if there's a narrative entry for this PPAS form
        $narrativeEntry = null;
        if (in_array('narrative_entries', $tables)) {
            try {
                // First try to find by ppas_form_id if that field exists
                $stmt = $conn->prepare("SHOW COLUMNS FROM narrative_entries LIKE 'ppas_form_id'");
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $stmt = $conn->prepare("SELECT id, title FROM narrative_entries WHERE ppas_form_id = :ppas_id LIMIT 1");
                    $stmt->execute([':ppas_id' => $ppasId]);
                    $narrativeEntry = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($narrativeEntry) {
                        error_log("Found matching narrative entry ID: {$narrativeEntry['id']} for PPAS ID: $ppasId (by ppas_form_id)");
                    }
                }
                
                // If no match by ppas_form_id, try by title similarity
                if (!$narrativeEntry) {
                    $stmt = $conn->prepare("SELECT id, title FROM narrative_entries WHERE title LIKE :title AND campus = :campus LIMIT 1");
                    $stmt->execute([':title' => '%' . $activityTitle . '%', ':campus' => $ppas['campus']]);
                    $narrativeEntry = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($narrativeEntry) {
                        error_log("Found matching narrative entry ID: {$narrativeEntry['id']} for PPAS ID: $ppasId (by title similarity)");
                    }
                }
            } catch (Exception $e) {
                error_log("Error checking narrative_entries for PPAS ID $ppasId: " . $e->getMessage());
            }
        }
        
        // Add to proposals array - Using PPAS id as the primary id and ALWAYS using PPAS year
        $proposals[] = [
            'id' => $ppasId,  // This is the PPAS form id
            'ppas_form_id' => $ppasId, // Same as id for consistency
            'narrative_entry_id' => $narrativeEntry ? $narrativeEntry['id'] : null,
            'activity_title' => $activityTitle, // From ppas_forms.activity
            'narrative_title' => $narrativeEntry ? $narrativeEntry['title'] : null, // From narrative_entries.title
            'campus' => $ppas['campus'],
            'year' => $ppasYear // Always use year from ppas_forms
        ];
    }
    
    // Return success response with debug info
    echo json_encode([
        'status' => 'success',
        'data' => $proposals,
        'ppas_table_found' => $ppasTable,
        'ppas_record_count' => count($ppasRecords),
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