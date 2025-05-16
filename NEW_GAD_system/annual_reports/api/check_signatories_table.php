<?php
header('Content-Type: application/json');
session_start();

// Include database connection
require_once '../../includes/db_connection.php';

try {
    $results = [];
    
    // Check if connection is successful
    if (!$conn) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database connection failed'
        ]);
        exit;
    }
    
    // 1. Get table structure
    $results['structure'] = [];
    $query = "DESCRIBE signatories";
    $result = $conn->query($query);
    
    if ($result) {
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row;
        }
        $results['structure']['columns'] = $columns;
    } else {
        $results['structure']['error'] = "Failed to get table structure: " . $conn->error;
        
        // Check if table exists
        $tableCheckQuery = "SHOW TABLES LIKE 'signatories'";
        $tableCheckResult = $conn->query($tableCheckQuery);
        if ($tableCheckResult && $tableCheckResult->num_rows > 0) {
            $results['structure']['table_exists'] = true;
        } else {
            $results['structure']['table_exists'] = false;
        }
    }
    
    // 2. Get sample data
    $results['sample_data'] = [];
    $sampleQuery = "SELECT * FROM signatories LIMIT 5";
    $sampleResult = $conn->query($sampleQuery);
    
    if ($sampleResult) {
        $samples = [];
        while ($row = $sampleResult->fetch_assoc()) {
            $samples[] = $row;
        }
        $results['sample_data']['rows'] = $samples;
        $results['sample_data']['count'] = count($samples);
    } else {
        $results['sample_data']['error'] = "Failed to get sample data: " . $conn->error;
    }
    
    // 3. Get available campuses
    $results['campuses'] = [];
    $campusQuery = "SELECT DISTINCT campus FROM signatories";
    $campusResult = $conn->query($campusQuery);
    
    if ($campusResult) {
        $campuses = [];
        while ($row = $campusResult->fetch_assoc()) {
            $campuses[] = $row['campus'];
        }
        $results['campuses']['list'] = $campuses;
        $results['campuses']['count'] = count($campuses);
    } else {
        $results['campuses']['error'] = "Failed to get campuses: " . $conn->error;
    }
    
    // 4. Get total count
    $countQuery = "SELECT COUNT(*) as total FROM signatories";
    $countResult = $conn->query($countQuery);
    
    if ($countResult) {
        $countRow = $countResult->fetch_assoc();
        $results['total_rows'] = $countRow['total'];
    } else {
        $results['total_rows_error'] = "Failed to get total count: " . $conn->error;
    }
    
    // 5. Check for null values in important columns
    $results['null_checks'] = [];
    $nullCheckColumns = ['campus', 'name1', 'name3', 'name4', 'gad_head_secretariat', 'chancellor', 'asst_director_gad'];
    
    foreach ($nullCheckColumns as $column) {
        $nullQuery = "SELECT COUNT(*) as null_count FROM signatories WHERE {$column} IS NULL OR {$column} = ''";
        $nullResult = $conn->query($nullQuery);
        
        if ($nullResult) {
            $nullRow = $nullResult->fetch_assoc();
            $results['null_checks'][$column] = $nullRow['null_count'];
        } else {
            $results['null_checks'][$column . '_error'] = "Failed to check for null values: " . $conn->error;
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $results
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 