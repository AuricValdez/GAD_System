<?php
// Enable debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>User Table Structure Debugger</h1>";

try {
    // Include database connection
    require_once '../includes/db_connection.php';
    echo "<p>✅ db_connection.php included successfully</p>";
    
    // Get database connection
    $conn = getConnection();
    
    // Check for users table
    $usersTables = ['users', 'user', 'accounts', 'account', 'login', 'faculty'];
    $foundTable = null;
    
    echo "<h2>Checking for user-related tables:</h2>";
    echo "<ul>";
    
    foreach ($usersTables as $table) {
        try {
            $stmt = $conn->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<li>✅ Table <strong>$table</strong> exists</li>";
                $foundTable = $table;
            } else {
                echo "<li>❌ Table <strong>$table</strong> not found</li>";
            }
        } catch (Exception $e) {
            echo "<li>❌ Error checking for table $table: " . $e->getMessage() . "</li>";
        }
    }
    echo "</ul>";
    
    // If we found a user table, show its structure
    if ($foundTable) {
        echo "<h2>Structure of table: $foundTable</h2>";
        
        $stmt = $conn->query("DESCRIBE $foundTable");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . ($value ?? "NULL") . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Check specifically for campus_id column
        $hasCampusId = false;
        foreach ($columns as $column) {
            if ($column['Field'] == 'campus_id') {
                $hasCampusId = true;
                echo "<p>✅ The <strong>$foundTable</strong> table has a <strong>campus_id</strong> column.</p>";
                break;
            }
        }
        
        if (!$hasCampusId) {
            echo "<p>❌ The <strong>$foundTable</strong> table does NOT have a <strong>campus_id</strong> column.</p>";
        }
        
        // Show a few sample rows
        echo "<h2>Sample data from $foundTable table:</h2>";
        
        try {
            $stmt = $conn->query("SELECT * FROM $foundTable LIMIT 5");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($users) > 0) {
                echo "<table border='1' cellpadding='5'>";
                
                // Table header
                echo "<tr>";
                foreach (array_keys($users[0]) as $key) {
                    echo "<th>$key</th>";
                }
                echo "</tr>";
                
                // Table data
                foreach ($users as $user) {
                    echo "<tr>";
                    foreach ($user as $value) {
                        // Mask passwords or sensitive data
                        if (in_array(strtolower($key), ['password', 'pass', 'passwd'])) {
                            echo "<td>********</td>";
                        } else {
                            echo "<td>" . ($value ?? "NULL") . "</td>";
                        }
                    }
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p>No data found in the $foundTable table.</p>";
            }
        } catch (Exception $e) {
            echo "<p>Error retrieving sample data: " . $e->getMessage() . "</p>";
        }
    }
    
    // Check for session variables
    echo "<h2>Current Session Variables:</h2>";
    
    // Start session if not started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (count($_SESSION) > 0) {
        echo "<ul>";
        foreach ($_SESSION as $key => $value) {
            // Mask potential sensitive information
            if (in_array(strtolower($key), ['password', 'pass', 'passwd', 'token'])) {
                echo "<li><strong>$key</strong>: ********</li>";
            } else {
                $valueDisplay = is_array($value) ? 'Array(' . count($value) . ')' : (is_object($value) ? get_class($value) : $value);
                echo "<li><strong>$key</strong>: $valueDisplay</li>";
            }
        }
        echo "</ul>";
        
        // Check for campus_id in session
        if (isset($_SESSION['campus_id'])) {
            echo "<p>✅ <strong>campus_id</strong> is set in the session with value: " . $_SESSION['campus_id'] . "</p>";
        } else {
            echo "<p>❌ <strong>campus_id</strong> is NOT set in the session.</p>";
        }
    } else {
        echo "<p>No session variables found.</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 