<?php
// Enable debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Debugger</h1>";

// Check if db_connection.php exists
if (file_exists('../includes/db_connection.php')) {
    echo "<p>✅ db_connection.php file exists</p>";
    
    // Display the file contents (with sensitive info hidden)
    echo "<h2>Contents of db_connection.php</h2>";
    echo "<pre>";
    $content = file_get_contents('../includes/db_connection.php');
    
    // Replace password with asterisks
    $content = preg_replace("/(define\s*\(\s*['\"]DB_PASS['\"]\s*,\s*['\"]).+?(['\"])/", "$1*****$2", $content);
    
    // Display the content
    highlight_string($content);
    echo "</pre>";
    
    try {
        // Include database connection
        require_once '../includes/db_connection.php';
        
        echo "<p>✅ db_connection.php included successfully</p>";
        
        // Try to get connection
        echo "<h2>Testing database connection</h2>";
        
        // Get database connection
        $conn = getConnection();
        echo "<p>✅ Connection established successfully</p>";
        
        // Test running a simple query
        $stmt = $conn->query("SELECT DATABASE() as db, VERSION() as version");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>Connected to database: <strong>" . $result['db'] . "</strong></p>";
        echo "<p>MySQL version: <strong>" . $result['version'] . "</strong></p>";
        
        // List all tables
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h2>Tables in the database:</h2>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        // Close connection
        $conn = null;
        
    } catch (Exception $e) {
        echo "<h2>❌ Error:</h2>";
        echo "<p>" . $e->getMessage() . "</p>";
        
        echo "<h3>Stack Trace:</h3>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
} else {
    echo "<p>❌ db_connection.php file not found at '../includes/db_connection.php'</p>";
}
?> 