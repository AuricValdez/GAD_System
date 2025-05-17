<?php
// Script to create signatories table if it doesn't exist
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to load config file
$rootPath = realpath(__DIR__ . '/../../');
$configPath = $rootPath . '/config.php';

echo "<h1>Signatories Table Creator</h1>";
echo "<p>This script will check if the signatories table exists and create it if it doesn't.</p>";

// Set default database parameters
$servername = "localhost";
$dbname = "gad_db";
$username = "root";
$password = "";

if (file_exists($configPath)) {
    echo "<p>Config file found at: $configPath</p>";
    include $configPath;
    
    // Use constants from config file if they exist
    if (defined('DB_HOST')) $servername = DB_HOST;
    if (defined('DB_NAME')) $dbname = DB_NAME;
    if (defined('DB_USER')) $username = DB_USER;
    if (defined('DB_PASS')) $password = DB_PASS;
    
    echo "<p>Using database connection parameters from config file.</p>";
} else {
    echo "<p>Config file not found! Using default database connection parameters.</p>";
}

try {
    // Connect to database
    echo "<p>Connecting to database: $dbname on $servername as $username...</p>";
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
    echo "<p style='color:green'>Connection successful!</p>";
    
    // Check if table exists
    $stmt = $db->query("SHOW TABLES LIKE 'signatories'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p>The 'signatories' table already exists.</p>";
        
        // Check structure
        $stmt = $db->query("DESCRIBE signatories");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Current Table Structure</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check content
        $stmt = $db->query("SELECT * FROM signatories LIMIT 5");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Sample Data (up to 5 rows)</h2>";
        if (count($rows) > 0) {
            echo "<table border='1' cellpadding='5'>";
            // Headers
            echo "<tr>";
            foreach (array_keys($rows[0]) as $key) {
                echo "<th>$key</th>";
            }
            echo "</tr>";
            
            // Rows
            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No data found in the table.</p>";
        }
    } else {
        echo "<p>The 'signatories' table does not exist.</p>";
        echo "<h2>Creating Table</h2>";
        
        // Create the table
        $createTableSQL = "CREATE TABLE `signatories` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $db->exec($createTableSQL);
        echo "<p style='color:green'>Table created successfully!</p>";
        
        // Insert sample data
        $insertSQL = "INSERT INTO `signatories` 
            (`campus`, `name1`, `name2`, `name3`, `name5`, `chancellor`, `vice_chancellor_rde`, `head_extension_services`) 
        VALUES
            ('Central', 'DR.wwwwww', 'DR. MARIA GONZALES', 'ATTY. ROBERT WILLIAMS', 'MS. JANE SMITH', 
             'Chancellor', 'Vice Chancellor for Research, Development and Extension Services', 'Head, Extension Services'),
            ('ARASOF', 'DR. ALAN SANTOS', 'DR. ELENA REYES', 'ATTY. ROBERT WILLIAMS', 'PROF. SARAH JOHNSON',
             'Chancellor', 'Vice Chancellor for Research, Development and Extension Services', 'Head, Extension Services'),
            ('JPLPC', 'DR. MICHAEL LEE', 'DR. PATRICIA CRUZ', 'ATTY. ROBERT WILLIAMS', 'DR. JAMES WILSON',
             'Chancellor', 'Vice Chancellor for Research, Development and Extension Services', 'Head, Extension Services'),
            ('Alangilan', 'DR. DAVID GARCIA', 'DR. SOPHIA TORRES', 'ATTY. ROBERT WILLIAMS', 'PROF. THOMAS BROWN',
             'Chancellor', 'Vice Chancellor for Research, Development and Extension Services', 'Head, Extension Services')";
             
        $db->exec($insertSQL);
        echo "<p style='color:green'>Sample data inserted successfully!</p>";
        
        // Show the newly created table
        $stmt = $db->query("SELECT * FROM signatories");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>New Table Data</h2>";
        echo "<table border='1' cellpadding='5'>";
        // Headers
        echo "<tr>";
        foreach (array_keys($rows[0]) as $key) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        
        // Rows
        foreach ($rows as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>What's Next?</h2>";
    echo "<p>Now that you can see the signatories table:</p>";
    echo "<ol>";
    echo "<li>The extension proposal system will use this data for the signatures section.</li>";
    echo "<li>You can update this data using your database admin tool (e.g., phpMyAdmin).</li>";
    echo "<li>Make sure each campus has its own entry with the correct names and positions.</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Database connection failed! Please check your connection parameters:</p>";
    echo "<ul>";
    echo "<li>Server: $servername</li>";
    echo "<li>Database: $dbname</li>";
    echo "<li>Username: $username</li>";
    echo "<li>Password: " . (empty($password) ? "(empty)" : "(not shown)") . "</li>";
    echo "</ul>";
    
    echo "<h2>Troubleshooting</h2>";
    echo "<ol>";
    echo "<li>Make sure your MySQL/MariaDB server is running</li>";
    echo "<li>Verify the database credentials in your config file</li>";
    echo "<li>Check that the database '$dbname' exists</li>";
    echo "<li>Ensure your MySQL user has permissions to create tables</li>";
    echo "</ol>";
}
?> 