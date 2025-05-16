<?php
require_once '../config.php';

// Enable detailed error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header('Content-Type: application/json');

// Function to return error messages
function returnError($message) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    returnError('Unauthorized access');
}

try {
    // Get POST data
    $requestBody = file_get_contents('php://input');
    error_log("Received request body: " . $requestBody);
    
    $data = json_decode($requestBody, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        returnError('Invalid JSON data: ' . json_last_error_msg());
    }
    
    // Validate required fields
    $requiredFields = ['id', 'name1', 'name2', 'name3', 'name4', 'name5'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        returnError('Missing required fields: ' . implode(', ', $missingFields));
    }
    
    // Set predefined rank values
    $data['gad_head_secretariat'] = 'GAD Head Secretariat';
    $data['vice_chancellor_rde'] = 'Vice Chancellor For Research, Development and Extension';
    $data['chancellor'] = 'Chancellor';
    $data['asst_director_gad'] = 'Assistant Director For GAD Advocacies';
    $data['head_extension_services'] = 'Head of Extension Services';
    $data['vice_chancellor_admin_finance'] = 'Vice Chancellor for Administration and Finance';
    $data['dean'] = 'Dean';
    
    // Get campus from session
    $campus = $_SESSION['username'];
    error_log("Processing update for campus: " . $campus . ", ID: " . $data['id']);
    
    // Check if the signatories table exists with the correct structure
    try {
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'signatories'");
        $tableExists = $tableCheck->rowCount() > 0;
        error_log("Table exists check: " . ($tableExists ? 'Yes' : 'No'));
        
        if ($tableExists) {
            // Check if the table has the new column structure
            $columnCheck = $pdo->query("SHOW COLUMNS FROM signatories LIKE 'name1'");
            $hasNewStructure = $columnCheck->rowCount() > 0;
            error_log("Has new structure check: " . ($hasNewStructure ? 'Yes' : 'No'));
            
            if (!$hasNewStructure) {
                // Table exists but has old structure - drop and recreate
                error_log("Table has old structure, recreating...");
                $pdo->exec("DROP TABLE signatories");
                
                // Create table with new structure
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
                error_log("Table created or recreated successfully");
                
                // Since we just recreated the table, the record with this ID no longer exists
                returnError('The table structure has been updated. Please refresh the page and try again.');
            }
        } else {
            returnError('Signatories table does not exist');
        }
    } catch (PDOException $e) {
        error_log("Error checking/creating table: " . $e->getMessage());
        returnError('Database error when checking/creating table: ' . $e->getMessage());
    }
    
    // Check if signatory exists and belongs to the current campus
    try {
        $checkStmt = $pdo->prepare("SELECT id FROM signatories WHERE id = ? AND campus = ?");
        $checkStmt->execute([$data['id'], $campus]);
        
        if ($checkStmt->rowCount() === 0) {
            returnError('Signatory not found or you do not have permission to update it');
        }
        error_log("Record found, proceeding with update");
    } catch (PDOException $e) {
        error_log("Error checking record: " . $e->getMessage());
        returnError('Database error when checking record: ' . $e->getMessage());
    }
    
    // Update signatory in database
    try {
        $stmt = $pdo->prepare("UPDATE signatories SET 
                              name1 = ?, gad_head_secretariat = ?,
                              name2 = ?, vice_chancellor_rde = ?,
                              name3 = ?, chancellor = ?,
                              name4 = ?, asst_director_gad = ?,
                              name5 = ?, head_extension_services = ?,
                              name6 = ?, vice_chancellor_admin_finance = ?,
                              name7 = ?, dean = ?
                              WHERE id = ? AND campus = ?");
        
        $result = $stmt->execute([
            $data['name1'],
            $data['gad_head_secretariat'],
            $data['name2'],
            $data['vice_chancellor_rde'],
            $data['name3'],
            $data['chancellor'],
            $data['name4'],
            $data['asst_director_gad'],
            $data['name5'],
            $data['head_extension_services'],
            $data['name6'],
            $data['vice_chancellor_admin_finance'],
            $data['name7'],
            $data['dean'],
            $data['id'],
            $campus
        ]);
        
        error_log("Update executed with result: " . ($result ? 'success' : 'failed'));
    } catch (PDOException $e) {
        error_log("Database operation error: " . $e->getMessage());
        returnError('Database operation failed: ' . $e->getMessage());
    }
    
    if ($result) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Signatory updated successfully'
        ]);
    } else {
        $errorInfo = $stmt->errorInfo();
        error_log("SQL error: " . print_r($errorInfo, true));
        returnError('Failed to update record: ' . implode(' - ', $errorInfo));
    }
    
} catch (Exception $e) {
    error_log("General error in update_signatory.php: " . $e->getMessage());
    returnError('An error occurred: ' . $e->getMessage());
}
?> 