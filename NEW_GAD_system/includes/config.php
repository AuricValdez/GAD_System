<?php
// Database configuration
$servername = "localhost";
$dbname = "gad_db";
$username = "root";
$password = "";

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

// For backward compatibility with older code
define('DB_HOST', $servername);
define('DB_NAME', $dbname);
define('DB_USER', $username);
define('DB_PASS', $password);

// Function to get a database connection
function getConnection() {
    global $servername, $username, $password, $dbname;
    try {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
        $conn = new PDO($dsn, $username, $password, $options);
        
        // Test the connection
        $conn->query("SELECT 1");
        
        return $conn;
    } catch(PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed. Please check your database settings.");
    }
}

// Legacy connection object for backward compatibility
try {
    $conn = getConnection();
} catch(Exception $e) {
    error_log("Database connection failed in config.php: " . $e->getMessage());
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
} 