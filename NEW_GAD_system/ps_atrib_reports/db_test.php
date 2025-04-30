<?php
// This is a database testing file for diagnostic purposes

// Turn on error reporting for diagnostics
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

try {
    echo "<p>Testing database connection...</p>";
    
    $dbFile = '../config/database.php';
    if (!file_exists($dbFile)) {
        throw new Exception("Database configuration file not found at: $dbFile");
    }
    
    echo "<p>✅ Database configuration file found</p>";
    
    // Include the database connection file
    require_once $dbFile;
    
    if (!isset($conn)) {
        throw new Exception("Database connection variable not set after including config file");
    }
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✅ Database connection successful</p>";
    
    // Test ppas_forms table existence
    $tablesResult = $conn->query("SHOW TABLES LIKE 'ppas_forms'");
    if ($tablesResult->num_rows == 0) {
        throw new Exception("Table 'ppas_forms' does not exist in the database");
    }
    
    echo "<p>✅ Table 'ppas_forms' exists</p>";
    
    // Get table structure
    $columnsResult = $conn->query("DESCRIBE ppas_forms");
    
    echo "<h2>ppas_forms Table Structure:</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $columnsResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Test a simple query with each quarter value
    echo "<h2>Testing Quarter Queries:</h2>";
    
    $quarters = ["Q1", "Q2", "Q3", "Q4"];
    
    foreach ($quarters as $quarter) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ppas_forms WHERE quarter = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
        
        $stmt->bind_param('s', $quarter);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute query for $quarter: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        echo "<p>Quarter $quarter: {$row['count']} records found</p>";
        
        $stmt->close();
    }
    
    // Test the exact query being used
    echo "<h2>Testing Actual Query:</h2>";
    
    foreach ($quarters as $quarter) {
        $stmt = $conn->prepare("SELECT id, activity as title, start_date as date 
                FROM ppas_forms 
                WHERE quarter = ? 
                ORDER BY start_date DESC");
                
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
        
        $stmt->bind_param('s', $quarter);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute query for $quarter: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $count = $result->num_rows;
        
        echo "<p>Quarter $quarter: $count records found</p>";
        
        if ($count > 0) {
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>ID: {$row['id']}, Title: {$row['title']}, Date: {$row['date']}</li>";
            }
            echo "</ul>";
        }
        
        $stmt->close();
    }
    
    echo "<p>All tests completed successfully!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
}
?> 