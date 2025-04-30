<?php
// Simple database table checker with direct output
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Tables Check</h1>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

try {
    // Include database connection
    require_once '../config/database.php';
    
    if (!isset($conn)) {
        throw new Exception("Database connection not established");
    }
    
    echo "<p>✅ Database connection successful</p>";
    
    // Check MySQL version
    $versionResult = $conn->query("SELECT VERSION() as version");
    $versionRow = $versionResult->fetch_assoc();
    echo "<p>MySQL Version: " . $versionRow['version'] . "</p>";
    
    // Get all tables
    echo "<h2>Database Tables:</h2>";
    $tablesResult = $conn->query("SHOW TABLES");
    
    if ($tablesResult->num_rows === 0) {
        echo "<p>No tables found in the database.</p>";
    } else {
        echo "<ul>";
        while ($row = $tablesResult->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    }
    
    // Check ppas_forms specifically
    echo "<h2>PPAS Forms Table Check:</h2>";
    
    // Check if table exists
    $checkTable = $conn->query("SHOW TABLES LIKE 'ppas_forms'");
    if ($checkTable->num_rows === 0) {
        echo "<p style='color:red;'>❌ Table 'ppas_forms' does not exist!</p>";
    } else {
        echo "<p>✅ Table 'ppas_forms' exists</p>";
        
        // Check table structure
        echo "<h3>Table Structure:</h3>";
        $columnsResult = $conn->query("DESCRIBE ppas_forms");
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        
        $hasQuarterColumn = false;
        while ($row = $columnsResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>";
            
            if ($row['Field'] === 'quarter') {
                $hasQuarterColumn = true;
            }
        }
        echo "</table>";
        
        if (!$hasQuarterColumn) {
            echo "<p style='color:red;'>❌ Column 'quarter' not found in ppas_forms table!</p>";
        } else {
            echo "<p>✅ Column 'quarter' exists in ppas_forms table</p>";
            
            // Check data by quarter
            echo "<h3>Data by Quarter:</h3>";
            $quarters = ["Q1", "Q2", "Q3", "Q4"];
            
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Quarter</th><th>Count</th></tr>";
            
            foreach ($quarters as $quarter) {
                $countQuery = $conn->prepare("SELECT COUNT(*) as count FROM ppas_forms WHERE quarter = ?");
                $countQuery->bind_param("s", $quarter);
                $countQuery->execute();
                $countResult = $countQuery->get_result();
                $countRow = $countResult->fetch_assoc();
                
                echo "<tr>";
                echo "<td>" . $quarter . "</td>";
                echo "<td>" . $countRow['count'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Show a few sample records if they exist
            $sampleQuery = $conn->query("SELECT id, quarter, activity, start_date FROM ppas_forms LIMIT 5");
            
            if ($sampleQuery->num_rows > 0) {
                echo "<h3>Sample Records (first 5):</h3>";
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>ID</th><th>Quarter</th><th>Activity</th><th>Start Date</th></tr>";
                
                while ($row = $sampleQuery->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['quarter'] . "</td>";
                    echo "<td>" . $row['activity'] . "</td>";
                    echo "<td>" . $row['start_date'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No records found in ppas_forms table.</p>";
            }
        }
    }
    
    echo "<hr><p>Check completed successfully.</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
}
?> 