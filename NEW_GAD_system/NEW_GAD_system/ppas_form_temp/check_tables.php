<?php
// Set up error reporting for troubleshooting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database configuration
require_once '../config.php';

echo "<h1>Database Table Check</h1>";

// Function to check if a table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Function to display table structure
function displayTableStructure($conn, $tableName) {
    echo "<h2>Structure for table: $tableName</h2>";
    
    if (!tableExists($conn, $tableName)) {
        echo "<p style='color:red'>Table does not exist!</p>";
        return;
    }
    
    $result = $conn->query("DESCRIBE $tableName");
    
    if ($result) {
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] === NULL ? 'NULL' : $row['Default']) . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        $result->free();
    } else {
        echo "<p style='color:red'>Error: " . $conn->error . "</p>";
    }
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<p>Database connection successful to " . DB_NAME . "</p>";

// Tables to check
$tables = ['ppas_forms', 'ppas_personnel'];

// Display tables status
echo "<h2>Tables Status</h2>";
echo "<ul>";
foreach ($tables as $table) {
    $exists = tableExists($conn, $table);
    $status = $exists ? "<span style='color:green'>Exists</span>" : "<span style='color:red'>Missing</span>";
    echo "<li>$table: $status</li>";
}
echo "</ul>";

// Display structure for each table
foreach ($tables as $table) {
    displayTableStructure($conn, $table);
}

// Close connection
$conn->close();
echo "<p>Database connection closed</p>";
?> 