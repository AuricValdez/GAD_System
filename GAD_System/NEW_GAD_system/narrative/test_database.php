<?php
// Display all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .section { margin-bottom: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
    .success { color: green; }
    .warning { color: orange; }
    .error { color: red; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .action-box { background-color: #f8f9fa; border: 1px solid #ddd; padding: 15px; margin-top: 20px; border-radius: 5px; }
    code { background-color: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
</style>";

// Check if includes/db_connection.php exists
echo "<div class='section'>";
echo "<h2>Checking connection file:</h2>";
if (file_exists('../includes/db_connection.php')) {
    echo "<p class='success'>✓ Connection file exists at ../includes/db_connection.php</p>";
} else {
    echo "<p class='error'>✗ Connection file NOT found at ../includes/db_connection.php</p>";
    echo "<p>Please make sure your database connection file exists.</p>";
    echo "<div class='action-box'>
        <h4>How to fix:</h4>
        <p>Create a file named <code>db_connection.php</code> in the <code>includes</code> directory with the following content:</p>
        <pre>
&lt;?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gad_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Function to get a database connection
function getConnection() {
    try {
        \$conn = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME, DB_USER, DB_PASS);
        \$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return \$conn;
    } catch(PDOException \$e) {
        error_log(\"Database connection failed: \" . \$e->getMessage());
        throw new Exception(\"Database connection failed: \" . \$e->getMessage());
    }
}

// Create a connection to be used by including files
try {
    \$conn = getConnection();
} catch(Exception \$e) {
    error_log(\"Database connection failed: \" . \$e->getMessage());
}
?&gt;
        </pre>
    </div>";
    exit;
}
echo "</div>";

// Try to connect to the database
echo "<div class='section'>";
echo "<h2>Testing database connection:</h2>";
try {
    include_once '../includes/db_connection.php';
    
    if (!isset($conn)) {
        echo "<p class='error'>✗ Connection variable not defined in the included file.</p>";
        echo "<p>Your db_connection.php file should define a variable named \$conn.</p>";
        exit;
    }
    
    echo "<p class='success'>✓ Database connection successful!</p>";
    echo "<p>Connection info: PDO connection to " . DB_HOST . "</p>";
    
    // Test if ppas_forms table exists
    echo "<h2>Checking for ppas_forms table:</h2>";
    $tableCheckQuery = "SHOW TABLES LIKE 'ppas_forms'";
    $tableCheckStmt = $conn->prepare($tableCheckQuery);
    $tableCheckStmt->execute();
    
    if ($tableCheckStmt->rowCount() == 0) {
        echo "<p class='error'>✗ The 'ppas_forms' table doesn't exist in the database.</p>";
        echo "<div class='action-box'>
            <h4>How to fix:</h4>
            <p>Run the SQL script in <code>narrative/ppas_forms_table.sql</code> to create the table.</p>
            <p>You can do this by:</p>
            <ol>
                <li>Opening phpMyAdmin (usually at <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a>)</li>
                <li>Selecting your database ('gad_db')</li>
                <li>Clicking on the 'SQL' tab</li>
                <li>Pasting the contents of the SQL file and clicking 'Go'</li>
            </ol>
        </div>";
    } else {
        echo "<p class='success'>✓ The 'ppas_forms' table exists!</p>";
        
        // Check table structure
        echo "<h3>Table structure:</h3>";
        $structureQuery = "DESCRIBE ppas_forms";
        $structureStmt = $conn->prepare($structureQuery);
        $structureStmt->execute();
        
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $structureStmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Check if table has data
        echo "<h3>Data check:</h3>";
        $countQuery = "SELECT COUNT(*) as total FROM ppas_forms";
        $countStmt = $conn->prepare($countQuery);
        $countStmt->execute();
        $countRow = $countStmt->fetch(PDO::FETCH_ASSOC);
        $totalRecords = $countRow['total'];
        
        if ($totalRecords == 0) {
            echo "<p class='warning'>⚠ The 'ppas_forms' table exists but has no records.</p>";
            echo "<div class='action-box'>
                <h4>How to fix:</h4>
                <p>Run the INSERT statements in <code>narrative/ppas_forms_table.sql</code> to add sample data.</p>
            </div>";
        } else {
            echo "<p class='success'>✓ The 'ppas_forms' table has " . $totalRecords . " records.</p>";
            
            // Show sample data
            echo "<h3>Sample records:</h3>";
            $sampleQuery = "SELECT * FROM ppas_forms LIMIT 5";
            $sampleStmt = $conn->prepare($sampleQuery);
            $sampleStmt->execute();
            
            echo "<table>";
            
            // Get the first row to use for headers
            $firstRow = $sampleStmt->fetch(PDO::FETCH_ASSOC);
            if ($firstRow) {
                echo "<tr>";
                foreach (array_keys($firstRow) as $column) {
                    echo "<th>" . htmlspecialchars($column) . "</th>";
                }
                echo "</tr>";
                
                // Output first row
                echo "<tr>";
                foreach ($firstRow as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
                
                // Output remaining rows
                while ($row = $sampleStmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                }
            }
            
            echo "</table>";
        }
    }
    
    // Check if narrative_forms table exists
    echo "<h2>Checking for narrative_forms table:</h2>";
    $tableCheckQuery = "SHOW TABLES LIKE 'narrative_forms'";
    $tableCheckStmt = $conn->prepare($tableCheckQuery);
    $tableCheckStmt->execute();
    
    if ($tableCheckStmt->rowCount() == 0) {
        echo "<p class='error'>✗ The 'narrative_forms' table doesn't exist in the database.</p>";
        echo "<div class='action-box'>
            <h4>How to fix:</h4>
            <p>Run the SQL script in <code>narrative/narrative_table.sql</code> to create the table.</p>
            <p>You can do this by:</p>
            <ol>
                <li>Opening phpMyAdmin (usually at <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a>)</li>
                <li>Selecting your database ('gad_db')</li>
                <li>Clicking on the 'SQL' tab</li>
                <li>Pasting the contents of the SQL file and clicking 'Go'</li>
            </ol>
        </div>";
    } else {
        echo "<p class='success'>✓ The 'narrative_forms' table exists!</p>";
        
        // Check table structure
        echo "<h3>Table structure:</h3>";
        $structureQuery = "DESCRIBE narrative_forms";
        $structureStmt = $conn->prepare($structureQuery);
        $structureStmt->execute();
        
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $structureStmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "<h2>Conclusion:</h2>";
    
    if ($tableCheckStmt->rowCount() == 0 || $totalRecords == 0) {
        echo "<p class='warning'>⚠ There are issues that need to be fixed. Please follow the instructions above.</p>";
        echo "<div class='action-box'>
            <h4>Next steps:</h4>
            <ol>
                <li>Run the SQL scripts to create missing tables or add data</li>
                <li>Refresh this page to verify the fixes</li>
                <li>Try using the narrative form again</li>
            </ol>
        </div>";
    } else {
        echo "<p class='success'>✓ Database setup looks good! You should be able to use the narrative form.</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<div class='action-box'>
        <h4>How to fix:</h4>
        <p>The error above indicates an issue with your database connection or configuration. Common solutions:</p>
        <ul>
            <li>Make sure your database server (MySQL/MariaDB) is running</li>
            <li>Check that the database name, username, and password in db_connection.php are correct</li>
            <li>Verify that the database exists and the user has proper permissions</li>
        </ul>
    </div>";
}
echo "</div>";

// Close connection if it exists
if (isset($conn)) {
    $conn = null;
}
?> 