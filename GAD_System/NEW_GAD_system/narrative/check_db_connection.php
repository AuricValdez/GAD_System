<?php
session_start();

// Only allow access if logged in as admin or in debug mode
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'Central') {
    header("HTTP/1.1 403 Forbidden");
    echo "Access denied";
    exit();
}

echo "<h1>Database Connection Check</h1>";

// Check if the database connection file exists
echo "<h2>Checking connection file</h2>";
$connection_file = '../includes/db_connection.php';
if (file_exists($connection_file)) {
    echo "<p style='color:green'>✓ Connection file exists at: " . $connection_file . "</p>";
} else {
    echo "<p style='color:red'>✗ Connection file NOT found at: " . $connection_file . "</p>";
    exit();
}

// Include the database connection file
echo "<h2>Testing database connection</h2>";
try {
    require_once($connection_file);
    
    if (!isset($conn)) {
        echo "<p style='color:red'>✗ \$conn variable not defined in the connection file</p>";
        exit();
    }
    
    if ($conn->connect_error) {
        echo "<p style='color:red'>✗ Connection failed: " . $conn->connect_error . "</p>";
        exit();
    }
    
    echo "<p style='color:green'>✓ Connected to MySQL server successfully</p>";
    echo "<p>Server info: " . $conn->server_info . "</p>";
    echo "<p>Host info: " . $conn->host_info . "</p>";
    
    // Check if tables exist
    echo "<h2>Checking tables</h2>";
    $required_tables = ['ppas_forms', 'narrative_forms'];
    
    foreach ($required_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<p style='color:green'>✓ Table '$table' exists</p>";
            
            // Show table structure
            $structure = $conn->query("DESCRIBE $table");
            echo "<details><summary>Table structure</summary><table border='1' cellpadding='5'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            
            while ($row = $structure->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
                echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
                echo "</tr>";
            }
            
            echo "</table></details>";
            
            // For ppas_forms, check if it has any records
            if ($table === 'ppas_forms') {
                $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
                $count = $count_result->fetch_assoc()['count'];
                if ($count > 0) {
                    echo "<p style='color:green'>✓ Table '$table' has $count records</p>";
                } else {
                    echo "<p style='color:orange'>⚠ Table '$table' exists but has no records</p>";
                }
            }
        } else {
            echo "<p style='color:red'>✗ Table '$table' does NOT exist</p>";
        }
    }
    
    echo "<h2>Solution</h2>";
    echo "<p>If the narrative_forms table does not exist, run the SQL script from the file: <code>narrative_table.sql</code></p>";
    echo "<p>You can copy and paste it directly into your MySQL admin tool (like phpMyAdmin).</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Exception occurred: " . $e->getMessage() . "</p>";
}

// Close the database connection
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
    echo "<p style='color:green'>✓ Database connection closed successfully</p>";
}
?> 