<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

$db_config_path = '../../includes/db_connection.php';
echo "Database config path: " . realpath($db_config_path) . "\n";
echo "File exists: " . (file_exists($db_config_path) ? 'Yes' : 'No') . "\n";

if (!file_exists($db_config_path)) {
    die("Database configuration file not found!");
}

require_once $db_config_path;

echo "Database configuration loaded.\n";
echo "Host: " . DB_HOST . "\n";
echo "Database: " . DB_NAME . "\n";
echo "User: " . DB_USER . "\n";

try {
    echo "Attempting database connection...\n";
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    echo "Database connection successful!\n";

    // Test query
    $query = "SELECT COUNT(*) as count FROM gad_proposals";
    $stmt = $db->query($query);
    $result = $stmt->fetch();
    echo "Number of proposals in database: " . $result['count'] . "\n";

} catch (PDOException $e) {
    echo "Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
} 