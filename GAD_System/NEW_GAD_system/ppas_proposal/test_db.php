<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration
require_once '../includes/config.php';

// Output format (html or json)
$format = isset($_GET['format']) && $_GET['format'] === 'json' ? 'json' : 'html';

if ($format === 'json') {
    header('Content-Type: application/json');
}

function outputHtml($message, $success = true) {
    global $format;
    if ($format === 'html') {
        echo '<div style="margin: 10px; padding: 10px; border: 1px solid ' . ($success ? 'green' : 'red') . ';">';
        echo $message;
        echo '</div>';
    }
}

function outputJson($data) {
    global $format;
    if ($format === 'json') {
        echo json_encode($data);
        exit;
    }
}

$results = [
    'success' => true,
    'database' => [
        'connection' => false,
        'tables' => []
    ],
    'errors' => []
];

try {
    outputHtml("<h1>Database Connection Test</h1>");
    
    // Test database connection
    outputHtml("<h2>Testing Database Connection</h2>");
    try {
        $conn = getConnection();
        outputHtml("✅ Successfully connected to database: " . DB_NAME);
        $results['database']['connection'] = true;
    } catch (Exception $e) {
        outputHtml("❌ Failed to connect to database: " . $e->getMessage(), false);
        $results['success'] = false;
        $results['errors'][] = "Database connection error: " . $e->getMessage();
        $results['database']['connection'] = false;
        outputJson($results);
        exit;
    }
    
    // Check tables
    outputHtml("<h2>Checking Required Tables</h2>");
    
    $requiredTables = ['gad_proposals', 'gad_activities'];
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    outputHtml("<h3>Available Tables:</h3>");
    outputHtml("<ul>");
    foreach ($tables as $table) {
        outputHtml("<li>{$table}</li>");
        $results['database']['tables'][] = $table;
    }
    outputHtml("</ul>");
    
    outputHtml("<h3>Required Tables Status:</h3>");
    outputHtml("<ul>");
    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            outputHtml("<li>✅ {$table} - Found</li>");
        } else {
            outputHtml("<li>❌ {$table} - Missing</li>", false);
            $results['success'] = false;
            $results['errors'][] = "Missing required table: {$table}";
        }
    }
    outputHtml("</ul>");
    
    // Check table structure if tables exist
    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            outputHtml("<h3>Structure of '{$table}' table:</h3>");
            
            $columns = $conn->query("DESCRIBE {$table}")->fetchAll(PDO::FETCH_ASSOC);
            
            outputHtml("<table border='1' cellpadding='5'>");
            outputHtml("<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>");
            
            foreach ($columns as $column) {
                outputHtml("<tr>");
                outputHtml("<td>" . $column['Field'] . "</td>");
                outputHtml("<td>" . $column['Type'] . "</td>");
                outputHtml("<td>" . $column['Null'] . "</td>");
                outputHtml("<td>" . $column['Key'] . "</td>");
                outputHtml("<td>" . $column['Default'] . "</td>");
                outputHtml("</tr>");
            }
            
            outputHtml("</table>");
            
            // Check for required fields
            if ($table === 'gad_proposals') {
                $requiredFields = ['id', 'year', 'quarter', 'activity_title'];
                foreach ($requiredFields as $field) {
                    $found = false;
                    foreach ($columns as $column) {
                        if ($column['Field'] === $field) {
                            $found = true;
                            break;
                        }
                    }
                    
                    if (!$found) {
                        outputHtml("❌ Required field '{$field}' is missing in {$table}", false);
                        $results['success'] = false;
                        $results['errors'][] = "Missing required field '{$field}' in table {$table}";
                    }
                }
            }
        }
    }
    
    // Test sample query
    outputHtml("<h2>Testing Sample Query</h2>");
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM gad_proposals");
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        outputHtml("✅ Query successful. Found {$count} proposals in the database.");
        $results['database']['proposal_count'] = $count;
    } catch (Exception $e) {
        outputHtml("❌ Query failed: " . $e->getMessage(), false);
        $results['success'] = false;
        $results['errors'][] = "Query error: " . $e->getMessage();
    }
    
    // Overall status
    outputHtml("<h2>Overall Status</h2>");
    if ($results['success']) {
        outputHtml("✅ All tests passed successfully!");
    } else {
        outputHtml("❌ Some tests failed. Please review the errors above.", false);
    }
    
} catch (Exception $e) {
    outputHtml("❌ Critical error: " . $e->getMessage(), false);
    $results['success'] = false;
    $results['errors'][] = "Critical error: " . $e->getMessage();
}

// Output JSON if requested
outputJson($results); 