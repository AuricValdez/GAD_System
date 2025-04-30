<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gad_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Disable direct error output
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Function to get a database connection
function getConnection() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        // Log the error
        error_log("Database connection failed: " . $e->getMessage());
        // Throw the error instead of echoing it
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

// Create a connection to be used by including files
try {
    $conn = getConnection();
} catch(Exception $e) {
    // Log the error
    error_log("Database connection failed: " . $e->getMessage());
    // Don't expose error details in production
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // This is an AJAX request
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
}
?> 