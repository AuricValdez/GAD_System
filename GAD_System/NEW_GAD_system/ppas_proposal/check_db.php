<?php
// Enable debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection file
require_once '../includes/db_connection.php';

try {
    // Get database connection
    $conn = getConnection();
    
    // Check tables in the database
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Tables in the database:</h2>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Check gad_proposals structure
    if (in_array('gad_proposals', $tables)) {
        $stmt = $conn->query("DESCRIBE gad_proposals");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Structure of gad_proposals table:</h2>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . ($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>The gad_proposals table does not exist.</p>";
    }
    
    // Close connection
    $conn = null;
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 