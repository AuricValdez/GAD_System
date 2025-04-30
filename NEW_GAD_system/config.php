<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gad_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Error handling for API endpoints
if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
    // For API requests, disable error display and log errors instead
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/error.log');
} else {
    // For regular pages, keep error reporting enabled for development
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Also create mysqli connection for backward compatibility
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch(Exception $e) {
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        // For API requests, return JSON error response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ]);
        exit;
    } else {
        // For regular pages, show error message
        echo "Connection failed: " . $e->getMessage();
    }
    exit;
}

// Set character set
$conn->set_charset("utf8mb4");
$pdo->exec("SET NAMES utf8mb4");

// Function to safely handle JSON responses
function sendJsonResponse($data, $success = true) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success' => $success,
        'data' => $data
    ]);
    exit;
}

// Function to handle API errors
function handleApiError($message, $statusCode = 500) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
    }
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}
?>
