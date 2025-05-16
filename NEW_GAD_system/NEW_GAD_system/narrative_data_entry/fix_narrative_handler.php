<?php
session_start();
require_once '../config.php';
require_once 'debug_logger.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Function to check if a table exists
function tableExists($tableName) {
    global $conn;
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Function to check if a column exists in a table
function columnExists($tableName, $columnName) {
    global $conn;
    $result = $conn->query("SHOW COLUMNS FROM `$tableName` LIKE '$columnName'");
    return $result->num_rows > 0;
}

// Function to create the narrative_entries table if it doesn't exist
function createNarrativeTable() {
    global $conn;
    
    $sql = "CREATE TABLE `narrative_entries` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `campus` varchar(255) NOT NULL,
        `year` varchar(10) NOT NULL,
        `title` varchar(255) NOT NULL,
        `background` text DEFAULT NULL,
        `participants` text DEFAULT NULL,
        `topics` text DEFAULT NULL,
        `results` text DEFAULT NULL,
        `lessons` text DEFAULT NULL,
        `what_worked` text DEFAULT NULL,
        `issues` text DEFAULT NULL,
        `recommendations` text DEFAULT NULL,
        `ps_attribution` varchar(255) DEFAULT NULL,
        `evaluation` text DEFAULT NULL,
        `photo_path` varchar(255) DEFAULT NULL,
        `photo_paths` text DEFAULT NULL,
        `photo_caption` text DEFAULT NULL,
        `gender_issue` text DEFAULT NULL,
        `created_by` varchar(100) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_by` varchar(100) DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        return false;
    }
}

// Function to add missing columns to the table
function addMissingColumns($tableName) {
    global $conn;
    $columnsToCheck = [
        'campus' => 'VARCHAR(255) NOT NULL',
        'year' => 'VARCHAR(10) NOT NULL',
        'title' => 'VARCHAR(255) NOT NULL',
        'background' => 'TEXT DEFAULT NULL',
        'participants' => 'TEXT DEFAULT NULL',
        'topics' => 'TEXT DEFAULT NULL',
        'results' => 'TEXT DEFAULT NULL',
        'lessons' => 'TEXT DEFAULT NULL',
        'what_worked' => 'TEXT DEFAULT NULL',
        'issues' => 'TEXT DEFAULT NULL',
        'recommendations' => 'TEXT DEFAULT NULL',
        'ps_attribution' => 'VARCHAR(255) DEFAULT NULL',
        'evaluation' => 'TEXT DEFAULT NULL',
        'photo_path' => 'VARCHAR(255) DEFAULT NULL',
        'photo_paths' => 'TEXT DEFAULT NULL',
        'photo_caption' => 'TEXT DEFAULT NULL',
        'gender_issue' => 'TEXT DEFAULT NULL',
        'created_by' => 'VARCHAR(100) DEFAULT NULL',
        'created_at' => 'TIMESTAMP NOT NULL DEFAULT current_timestamp()',
        'updated_by' => 'VARCHAR(100) DEFAULT NULL',
        'updated_at' => 'TIMESTAMP NULL DEFAULT NULL'
    ];
    
    $addedColumns = [];
    
    foreach ($columnsToCheck as $column => $definition) {
        if (!columnExists($tableName, $column)) {
            $sql = "ALTER TABLE `$tableName` ADD COLUMN `$column` $definition";
            if ($conn->query($sql) === TRUE) {
                $addedColumns[] = $column;
            }
        }
    }
    
    return $addedColumns;
}

// Function to test database insertion
function testDatabaseInsertion() {
    global $conn;
    
    // Create test data
    $testData = [
        'campus' => 'Test Campus',
        'year' => '2023',
        'title' => 'Test Activity',
        'background' => 'Test background information',
        'participants' => 'Test participants description',
        'topics' => 'Test topics discussed',
        'results' => 'Test results',
        'lessons' => 'Test lessons learned',
        'what_worked' => 'Test what worked',
        'issues' => 'Test issues',
        'recommendations' => 'Test recommendations',
        'ps_attribution' => 'Test PS attribution',
        'evaluation' => json_encode([
            'activity' => [
                'Excellent' => [
                    'BatStateU' => 5,
                    'Others' => 3
                ],
                'Very Satisfactory' => [
                    'BatStateU' => 4,
                    'Others' => 2
                ]
            ]
        ]),
        'photo_caption' => 'Test photo caption',
        'gender_issue' => 'Test gender issue',
        'created_by' => $_SESSION['username']
    ];
    
    // Prepare SQL statement
    $query = "INSERT INTO narrative_entries (
              campus, year, title, background, participants, 
              topics, results, lessons, what_worked, issues, 
              recommendations, ps_attribution, evaluation, photo_caption, gender_issue,
              created_by, created_at
            ) VALUES (
              ?, ?, ?, ?, ?, 
              ?, ?, ?, ?, ?, 
              ?, ?, ?, ?, ?,
              ?, NOW()
            )";
            
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        return ['success' => false, 'message' => "Database prepare error: " . $conn->error];
    }
    
    $stmt->bind_param(
        "sssssssssssssss", 
        $testData['campus'], $testData['year'], $testData['title'], $testData['background'], $testData['participants'], 
        $testData['topics'], $testData['results'], $testData['lessons'], $testData['what_worked'], $testData['issues'], 
        $testData['recommendations'], $testData['ps_attribution'], $testData['evaluation'], $testData['photo_caption'], $testData['gender_issue'],
        $testData['created_by']
    );
    
    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        
        // Verify the data was inserted correctly
        $checkQuery = "SELECT * FROM narrative_entries WHERE id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $newId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Clean up the test data
            $deleteQuery = "DELETE FROM narrative_entries WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $newId);
            $deleteStmt->execute();
            
            return ['success' => true, 'data' => $row];
        } else {
            return ['success' => false, 'message' => "Could not retrieve inserted data"];
        }
    } else {
        return ['success' => false, 'message' => "Error inserting test data: " . $stmt->error];
    }
}

// Main execution
try {
    // Connect to database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    $results = [
        'database_connection' => 'Success',
        'table_exists' => false,
        'table_created' => false,
        'columns_added' => [],
        'test_insertion' => null
    ];
    
    // Check if table exists
    if (tableExists('narrative_entries')) {
        $results['table_exists'] = true;
        
        // Check for missing columns and add them
        $addedColumns = addMissingColumns('narrative_entries');
        $results['columns_added'] = $addedColumns;
    } else {
        // Create the table
        $tableCreated = createNarrativeTable();
        $results['table_created'] = $tableCreated;
    }
    
    // Test database insertion
    $testResult = testDatabaseInsertion();
    $results['test_insertion'] = $testResult;
    
    // Return results
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'results' => $results]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 