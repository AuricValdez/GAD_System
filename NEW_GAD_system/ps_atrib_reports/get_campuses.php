<?php
// Include database connection
require_once('../includes/db_connect.php');
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in and is Central
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'Central') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

try {
    // Check if the database connection is valid
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Database connection not available");
    }
    
    // Check if the ppa table exists - modify this to match your actual table name
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'ppas'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        // Try alternative table name
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'ppa'");
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            throw new Exception("PPAs table not found in database");
        } else {
            $table = 'ppa';
        }
    } else {
        $table = 'ppas';
    }
    
    // Now check if the campus column exists
    $stmt = $pdo->prepare("DESCRIBE `{$table}` 'campus'");
    try {
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            throw new Exception("Campus column not found in {$table} table");
        }
    } catch (PDOException $columnEx) {
        // If this fails, just proceed with the query and handle any errors
    }
    
    // Query to get distinct campuses from PPAs table
    $query = "SELECT DISTINCT campus FROM `{$table}` WHERE campus IS NOT NULL AND campus != '' ORDER BY campus";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    // Fetch all campuses
    $campuses = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    // If no campuses found, provide some defaults
    if (empty($campuses)) {
        $campuses = ['Main Campus', 'Alangilan', 'Lemery', 'Lipa', 'Balayan', 'Lobo', 'Mabini', 'Malvar', 'Nasugbu', 'Pablo Borbon', 'Rosario', 'San Juan'];
    }
    
    // Return as JSON
    echo json_encode([
        'success' => true,
        'campuses' => $campuses
    ]);
    
} catch (PDOException $e) {
    // Log error
    error_log("Database error in get_campuses.php: " . $e->getMessage());
    
    // Return error
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'campuses' => ['Main Campus', 'Alangilan', 'Lemery', 'Lipa', 'Balayan', 'Lobo', 'Mabini', 'Malvar', 'Nasugbu', 'Pablo Borbon', 'Rosario', 'San Juan']
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("Error in get_campuses.php: " . $e->getMessage());
    
    // Return error but still provide default campuses
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'campuses' => ['Main Campus', 'Alangilan', 'Lemery', 'Lipa', 'Balayan', 'Lobo', 'Mabini', 'Malvar', 'Nasugbu', 'Pablo Borbon', 'Rosario', 'San Juan']
    ]);
}
?> 