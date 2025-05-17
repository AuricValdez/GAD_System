<?php
// Direct database test script
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

echo "<pre>";
echo "Database Connection Test\n";
echo "======================\n\n";

// Try to load config file
$rootPath = realpath(__DIR__ . '/../../');
$configPath = $rootPath . '/config.php';
echo "Root path: $rootPath\n";
echo "Config file path: $configPath\n\n";

// Set default database parameters
$servername = "localhost";
$dbname = "gad_db";
$username = "root";
$password = "";

if (file_exists($configPath)) {
    echo "Config file found! Including it...\n";
    include $configPath;
    echo "Checking for database constants...\n";
    
    // Check for and use constants from config file
    if (defined('DB_HOST')) {
        echo "Found DB_HOST: " . DB_HOST . "\n";
        $servername = DB_HOST;
    }
    
    if (defined('DB_NAME')) {
        echo "Found DB_NAME: " . DB_NAME . "\n";
        $dbname = DB_NAME;
    }
    
    if (defined('DB_USER')) {
        echo "Found DB_USER: " . DB_USER . "\n";
        $username = DB_USER;
    }
    
    if (defined('DB_PASS')) {
        echo "Found DB_PASS: " . (empty(DB_PASS) ? "(empty)" : "(set)") . "\n";
        $password = DB_PASS;
    }
    
    // Check for existing connection
    if (isset($pdo) && $pdo instanceof PDO) {
        echo "\nExisting PDO connection found in config!\n";
        $db = $pdo;
        echo "Using the existing connection for tests.\n\n";
    }
} else {
    echo "Config file NOT found! Using default values.\n\n";
}

echo "Testing connection with:\n";
echo "Host: $servername\n";
echo "Database: $dbname\n";
echo "Username: $username\n";
echo "Password: " . (empty($password) ? "(empty)" : "(set)") . "\n\n";

try {
    echo "Attempting database connection...\n";
    // Create PDO connection
    $db = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    echo "Connection successful!\n\n";
    
    echo "Checking tables in the database...\n";
    $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Found " . count($tables) . " tables:\n";
    echo implode(", ", $tables) . "\n\n";
    
    // Check for signatories table
    echo "Looking for signatories table...\n";
    if (in_array('signatories', $tables)) {
        echo "Signatories table found! Checking contents...\n";
        $stmt = $db->query('SELECT COUNT(*) FROM signatories');
        $count = $stmt->fetchColumn();
        echo "The signatories table contains $count records.\n\n";
        
        // Get a sample row
        if ($count > 0) {
            $row = $db->query('SELECT * FROM signatories LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            echo "Sample record:\n";
            print_r($row);
        }
    } else {
        echo "Signatories table not found!\n";
        echo "Would you like to create it? Here's the SQL:\n\n";
        echo "CREATE TABLE `signatories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campus` varchar(255) NOT NULL,
  `name1` varchar(255) DEFAULT NULL,
  `name2` varchar(255) DEFAULT NULL,
  `name3` varchar(255) DEFAULT NULL,
  `name4` varchar(255) DEFAULT NULL,
  `name5` varchar(255) DEFAULT NULL,
  `name6` varchar(255) DEFAULT NULL,
  `name7` varchar(255) DEFAULT NULL,
  `chancellor` varchar(255) DEFAULT NULL,
  `vice_chancellor_rde` varchar(255) DEFAULT NULL,
  `head_extension_services` varchar(255) DEFAULT NULL,
  `gad_head_secretariat` varchar(255) DEFAULT NULL,
  `asst_director_gad` varchar(255) DEFAULT NULL,
  `dean` varchar(255) DEFAULT NULL,
  `vice_chancellor_admin_finance` varchar(255) DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n";
    }
    
} catch (PDOException $e) {
    echo "ERROR: Database connection failed!\n";
    echo "Error message: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n\n";
    
    echo "Troubleshooting suggestions:\n";
    echo "1. Verify your MySQL/MariaDB service is running\n";
    echo "2. Check if the username and password are correct\n";
    echo "3. Ensure the database '$dbname' exists\n";
    echo "4. Try connecting with a tool like phpMyAdmin or MySQL Workbench\n\n";
    
    echo "For XAMPP/WAMP users:\n";
    echo "- Default username is typically 'root'\n";
    echo "- Default password is often empty\n";
    echo "- Make sure your server is running\n";
}
echo "</pre>"; 