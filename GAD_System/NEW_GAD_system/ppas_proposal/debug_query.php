<?php
// Enable debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection file
require_once '../includes/db_connection.php';

try {
    // Get database connection
    $conn = getConnection();
    
    // Try running the original query that's failing
    $sql = "SELECT p.id, p.activity_title as title, p.project, p.program, 
                 p.year as fiscalYear, p.quarter, p.created_at as date_created
          FROM gad_proposals p
          ORDER BY p.created_at DESC";
    
    echo "<h2>Testing SQL Query:</h2>";
    echo "<pre>$sql</pre>";
    
    // Execute query
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Fetch results
    $proposals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Results:</h2>";
    echo "<p>Found " . count($proposals) . " proposals</p>";
    
    if (count($proposals) > 0) {
        echo "<table border='1'>";
        echo "<tr>";
        foreach (array_keys($proposals[0]) as $header) {
            echo "<th>$header</th>";
        }
        echo "</tr>";
        
        foreach ($proposals as $proposal) {
            echo "<tr>";
            foreach ($proposal as $value) {
                echo "<td>" . ($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Close connection
    $conn = null;
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 