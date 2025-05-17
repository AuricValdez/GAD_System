<?php 
// Create a simple script to test signatory retrieval
$rootPath = realpath(__DIR__ . '/../../');
$configPath = $rootPath . '/config.php';

echo "Starting signatory test script...\n\n";
echo "Root path: {$rootPath}\n";
echo "Config path: {$configPath}\n\n";

// Set up mock $_SERVER values to avoid errors in the config file
$_SERVER['REQUEST_URI'] = '/test_signatories.php';

// Try to load the config file with error handling
try {
    if (file_exists($configPath)) {
        echo "Config file found, including it...\n";
        // Use include instead of require_once to continue even if it fails
        include $configPath;
        echo "Database settings from config:\n";
        echo "Server: " . (isset($servername) ? $servername : 'Not defined') . "\n";
        echo "Database: " . (isset($dbname) ? $dbname : 'Not defined') . "\n";
        echo "Username: " . (isset($username) ? $username : 'Not defined') . "\n\n";
    } else {
        echo "Config file not found at {$configPath}, using hardcoded connection details...\n";
    }
} catch (Exception $e) {
    echo "Error loading config file: " . $e->getMessage() . "\n";
}

// Ensure we have database connection details regardless of config file
if (!isset($servername) || !isset($dbname) || !isset($username)) {
    echo "Using default database connection settings...\n";
    $servername = "localhost";
    $dbname = "gad_db";
    $username = "root";
    $password = "";
}

try {
    echo "Attempting database connection...\n";
    // Create database connection
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
    
    echo "Checking for tables in database...\n";
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Available tables: " . implode(", ", $tables) . "\n\n";
    
    // Check table existence
    echo "Checking if signatories table exists...\n";
    $tableCheckSql = "SHOW TABLES LIKE 'signatories'";
    $tableCheckStmt = $db->query($tableCheckSql);
    $tableExists = ($tableCheckStmt->rowCount() > 0);
    
    if (!$tableExists) {
        echo "ERROR: The 'signatories' table does not exist in the database!\n\n";
        
        echo "Would you like to create a simple signatories table? (This is just a suggestion)\n";
        echo "Sample CREATE TABLE statement:\n";
        echo "CREATE TABLE signatories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            campus VARCHAR(255) NOT NULL,
            name1 VARCHAR(255) NULL,
            name2 VARCHAR(255) NULL,
            name3 VARCHAR(255) NULL,
            name4 VARCHAR(255) NULL,
            name5 VARCHAR(255) NULL,
            name6 VARCHAR(255) NULL,
            name7 VARCHAR(255) NULL,
            chancellor VARCHAR(255) NULL,
            vice_chancellor_rde VARCHAR(255) NULL,
            head_extension_services VARCHAR(255) NULL,
            gad_head_secretariat VARCHAR(255) NULL,
            asst_director_gad VARCHAR(255) NULL,
            dean VARCHAR(255) NULL,
            vice_chancellor_admin_finance VARCHAR(255) NULL,
            date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );\n\n";
    } else {
        echo "Signatories table exists!\n\n";
        
        echo "Getting table structure...\n";
        $columnSql = "SHOW COLUMNS FROM signatories";
        $columnStmt = $db->query($columnSql);
        $columns = $columnStmt->fetchAll(PDO::FETCH_COLUMN, 0);
        echo "Columns in signatories table: " . implode(", ", $columns) . "\n\n";
        
        // Get all rows from signatories table
        echo "Fetching data from signatories table...\n";
        $signatoriesSql = "SELECT * FROM signatories LIMIT 10";
        $stmt = $db->query($signatoriesSql);
        $signatories = $stmt->fetchAll();
        
        echo "Found " . count($signatories) . " signatory records:\n";
        foreach($signatories as $idx => $sig) {
            echo "\nRecord #" . ($idx + 1) . ":\n";
            print_r($sig);
        }
        
        // Test with specific campus
        echo "\n\nTesting with specific campus:\n";
        $testCampuses = ['Central', 'ARASOF', 'JPLPC', 'Alangilan'];
        
        foreach($testCampuses as $testCampus) {
            echo "\nLooking up signatories for campus '$testCampus'...\n";
            
            $sql = "SELECT * FROM signatories WHERE campus = :campus";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':campus', $testCampus);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                echo "✓ Found record for $testCampus:\n";
                print_r($result);
            } else {
                echo "✗ No record found for campus '$testCampus'\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?> 