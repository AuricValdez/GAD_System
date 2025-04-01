<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing database connection...<br>";

// Check config file
$configFile = '../../includes/config.php';
echo "Config file path: " . realpath($configFile) . "<br>";
echo "Config file exists: " . (file_exists($configFile) ? 'Yes' : 'No') . "<br>";

if (file_exists($configFile)) {
    require_once $configFile;
    
    echo "<br>Database Configuration:<br>";
    echo "Server: " . (isset($servername) ? $servername : 'Not set') . "<br>";
    echo "Database: " . (isset($dbname) ? $dbname : 'Not set') . "<br>";
    echo "Username: " . (isset($username) ? $username : 'Not set') . "<br>";
    echo "Password: " . (isset($password) ? 'Is set' : 'Not set') . "<br>";

    try {
        $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
        $db = new PDO($dsn, $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<br>Database connection successful!<br>";
        
        // Test query
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<br>Available tables:<br>";
        foreach ($tables as $table) {
            echo "- $table<br>";
        }
        
    } catch(PDOException $e) {
        echo "<br>Connection failed: " . $e->getMessage();
    }
} else {
    echo "<br>Error: Config file not found!";
} 