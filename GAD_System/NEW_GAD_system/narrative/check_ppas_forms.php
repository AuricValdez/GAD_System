<?php
session_start();

// Only allow access if logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PPAS Forms Table Check</h1>";

try {
    // Include database connection
    require_once('../includes/db_connection.php');
    
    if (!isset($conn)) {
        throw new Exception("Database connection not established");
    }
    
    echo "<p>Connected to database: " . $conn->host_info . "</p>";
    
    // Check if ppas_forms table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'ppas_forms'");
    if ($table_check->num_rows == 0) {
        echo "<p style='color:red'>ERROR: The table 'ppas_forms' does not exist!</p>";
        echo "<p>You need to create the ppas_forms table first.</p>";
        exit();
    }
    
    echo "<p style='color:green'>✓ ppas_forms table exists</p>";
    
    // Check table structure
    $structure = $conn->query("DESCRIBE ppas_forms");
    echo "<h2>Table Structure</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $required_fields = ['id', 'title', 'username'];
    $missing_fields = $required_fields;
    
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
        
        // Remove from missing fields if found
        if (in_array($row['Field'], $missing_fields)) {
            $missing_fields = array_diff($missing_fields, [$row['Field']]);
        }
    }
    
    echo "</table>";
    
    if (!empty($missing_fields)) {
        echo "<p style='color:red'>ERROR: Required fields missing: " . implode(", ", $missing_fields) . "</p>";
    }
    
    // Check if there are any records
    $count_query = $conn->query("SELECT COUNT(*) as count FROM ppas_forms");
    $count = $count_query->fetch_assoc()['count'];
    
    if ($count == 0) {
        echo "<p style='color:red'>ERROR: The ppas_forms table exists but has no records!</p>";
    } else {
        echo "<p style='color:green'>✓ Found $count records in ppas_forms table</p>";
        
        // Check if there are records for the current user
        if ($_SESSION['username'] !== 'Central') {
            $user_query = $conn->prepare("SELECT COUNT(*) as count FROM ppas_forms WHERE username = ?");
            $user_query->bind_param("s", $_SESSION['username']);
            $user_query->execute();
            $user_result = $user_query->get_result();
            $user_count = $user_result->fetch_assoc()['count'];
            
            if ($user_count == 0) {
                echo "<p style='color:red'>WARNING: No PPAS forms found for user '{$_SESSION['username']}'</p>";
                echo "<p>This is why no forms are showing up in your dropdown.</p>";
            } else {
                echo "<p style='color:green'>✓ Found $user_count PPAS forms for user '{$_SESSION['username']}'</p>";
            }
            
            $user_query->close();
        }
        
        // Show a sample of records
        $sample_query = $conn->query("SELECT id, title, username FROM ppas_forms LIMIT 5");
        
        echo "<h2>Sample Records</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Title</th><th>Username</th></tr>";
        
        while ($row = $sample_query->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // Provide troubleshooting steps
    echo "<h2>Troubleshooting</h2>";
    echo "<ol>";
    echo "<li>Make sure the ppas_forms table exists and has records</li>";
    echo "<li>If you're not Central, make sure there are PPAS forms associated with your username</li>";
    echo "<li>Check if your database connection in ../includes/db_connection.php is correct</li>";
    echo "<li>Check the server error logs for more detailed information</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
}

// Close the connection if it exists
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
    echo "<p>Database connection closed.</p>";
}
?> 