<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get database connection
function getConnection() {
    try {
        $conn = new PDO(
            "mysql:host=localhost;dbname=gad_db;charset=utf8mb4",
            "root",
            "",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        return $conn;
    } catch (PDOException $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

// Check database connection
try {
    $pdo = getConnection();
    $connection_status = "Connected to database successfully";
} catch (Exception $e) {
    $connection_status = "Failed to connect to database: " . $e->getMessage();
}

// Check if narrative_entries table exists
try {
    $table_check = $pdo->query("SHOW TABLES LIKE 'narrative_entries'");
    $table_exists = $table_check->rowCount() > 0;
} catch (Exception $e) {
    $table_exists = false;
    $table_error = $e->getMessage();
}

// Get table structure if it exists
$columns = [];
if ($table_exists) {
    try {
        $columns_query = $pdo->query("DESCRIBE narrative_entries");
        $columns = $columns_query->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $columns_error = $e->getMessage();
    }
}

// Count entries in the table
$entry_count = 0;
if ($table_exists) {
    try {
        $count_query = $pdo->query("SELECT COUNT(*) as count FROM narrative_entries");
        $count_result = $count_query->fetch(PDO::FETCH_ASSOC);
        $entry_count = $count_result['count'];
    } catch (Exception $e) {
        $count_error = $e->getMessage();
    }
}

// Get sample entries
$entries = [];
if ($table_exists && $entry_count > 0) {
    try {
        $entries_query = $pdo->query("SELECT * FROM narrative_entries ORDER BY id DESC LIMIT 10");
        $entries = $entries_query->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $entries_error = $e->getMessage();
    }
}

// Check required fields
$field_names = [];
if (!empty($columns)) {
    foreach ($columns as $column) {
        $field_names[] = $column['Field'];
    }
}

$required_fields = ['results', 'lessons', 'what_worked', 'issues', 'recommendations'];
$missing_fields = array_diff($required_fields, $field_names);

// Check if fields have alternative names in the table
$field_alternatives = [
    'results' => ['expected_results'],
    'lessons' => ['lessons_learned'],
    'issues' => ['issues_concerns']
];

$field_status = [];
foreach ($required_fields as $field) {
    if (in_array($field, $field_names)) {
        $field_status[$field] = 'Found';
    } else {
        $alternatives = $field_alternatives[$field] ?? [];
        $alternative_found = false;
        
        foreach ($alternatives as $alt) {
            if (in_array($alt, $field_names)) {
                $field_status[$field] = 'Using alternative field: ' . $alt;
                $alternative_found = true;
                break;
            }
        }
        
        if (!$alternative_found) {
            $field_status[$field] = 'Missing';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Narrative Entries Database Check</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1, h2, h3 {
            color: #333;
        }
        .section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .error {
            color: #d9534f;
            background-color: #f9f2f2;
            padding: 10px;
            border-radius: 3px;
        }
        .success {
            color: #5cb85c;
            background-color: #f2f9f2;
            padding: 10px;
            border-radius: 3px;
        }
        .warning {
            color: #f0ad4e;
            background-color: #f9f8f2;
            padding: 10px;
            border-radius: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .field-found {
            color: #5cb85c;
        }
        .field-alternative {
            color: #f0ad4e;
        }
        .field-missing {
            color: #d9534f;
        }
    </style>
</head>
<body>
    <h1>Narrative Entries Database Check</h1>
    
    <div class="section">
        <h2>Database Connection</h2>
        <?php if (strpos($connection_status, 'successfully') !== false): ?>
            <p class="success"><?php echo $connection_status; ?></p>
        <?php else: ?>
            <p class="error"><?php echo $connection_status; ?></p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>Table Status</h2>
        <?php if ($table_exists): ?>
            <p class="success">The table 'narrative_entries' exists in the database.</p>
            <p>Total entries: <strong><?php echo $entry_count; ?></strong></p>
        <?php else: ?>
            <p class="error">The table 'narrative_entries' does not exist in the database.</p>
            <?php if (isset($table_error)): ?>
                <p>Error: <?php echo $table_error; ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php if ($table_exists): ?>
        <div class="section">
            <h2>Table Structure</h2>
            <?php if (!empty($columns)): ?>
                <table>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                        <th>Extra</th>
                    </tr>
                    <?php foreach ($columns as $column): ?>
                    <tr>
                        <td><?php echo $column['Field']; ?></td>
                        <td><?php echo $column['Type']; ?></td>
                        <td><?php echo $column['Null']; ?></td>
                        <td><?php echo $column['Key']; ?></td>
                        <td><?php echo $column['Default']; ?></td>
                        <td><?php echo $column['Extra']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p class="error">Failed to retrieve table structure.</p>
                <?php if (isset($columns_error)): ?>
                    <p>Error: <?php echo $columns_error; ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Required Fields Check</h2>
            <p>Checking for the required fields in the narrative_entries table:</p>
            <ul>
                <?php foreach ($field_status as $field => $status): ?>
                    <?php if ($status === 'Found'): ?>
                        <li><span class="field-found">✓</span> <?php echo $field; ?>: <?php echo $status; ?></li>
                    <?php elseif (strpos($status, 'alternative') !== false): ?>
                        <li><span class="field-alternative">⚠</span> <?php echo $field; ?>: <?php echo $status; ?></li>
                    <?php else: ?>
                        <li><span class="field-missing">✗</span> <?php echo $field; ?>: <?php echo $status; ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
            
            <?php if (!empty($missing_fields)): ?>
                <p class="warning">Some required fields are missing. You may need to add these columns to the table or update the code to use existing columns.</p>
            <?php endif; ?>
        </div>
        
        <?php if ($entry_count > 0): ?>
            <div class="section">
                <h2>Sample Entries</h2>
                <p>Showing up to 10 most recent entries from the narrative_entries table:</p>
                
                <?php foreach ($entries as $index => $entry): ?>
                    <div style="margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        <h3>Entry #<?php echo $entry['id']; ?></h3>
                        <table>
                            <tr>
                                <th>Field</th>
                                <th>Value</th>
                            </tr>
                            <?php foreach ($entry as $field => $value): ?>
                                <tr>
                                    <td><?php echo $field; ?></td>
                                    <td><?php echo strlen($value) > 100 ? substr(htmlspecialchars($value), 0, 100) . '...' : htmlspecialchars($value); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="section">
                <h2>Sample Entries</h2>
                <p class="warning">No entries found in the narrative_entries table. You need to add data to the table.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="section">
        <h2>Recommendations</h2>
        <?php if (!$table_exists): ?>
            <p>You need to create the narrative_entries table first.</p>
            <p>Example SQL:</p>
            <pre>
CREATE TABLE `narrative_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ppas_form_id` int(11) DEFAULT NULL,
  `campus` varchar(255) DEFAULT NULL,
  `year` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `results` text DEFAULT NULL,
  `lessons` text DEFAULT NULL,
  `what_worked` text DEFAULT NULL,
  `issues` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ppas_form_id` (`ppas_form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            </pre>
        <?php elseif ($entry_count === 0): ?>
            <p>The narrative_entries table exists but contains no data. You need to insert records into the table.</p>
        <?php elseif (!empty($missing_fields)): ?>
            <p>The table exists and has data, but some required fields are missing. Consider adding these fields:</p>
            <ul>
                <?php foreach ($missing_fields as $field): ?>
                    <li><?php echo $field; ?></li>
                <?php endforeach; ?>
            </ul>
            <p>Example SQL to add missing fields:</p>
            <pre>
<?php
foreach ($missing_fields as $field) {
    echo "ALTER TABLE narrative_entries ADD COLUMN $field TEXT DEFAULT NULL;\n";
}
?>
            </pre>
        <?php else: ?>
            <p class="success">Everything looks good! The narrative_entries table exists, has the required fields, and contains data.</p>
        <?php endif; ?>
    </div>
    
    <p><a href="javascript:history.back()">« Back</a></p>
</body>
</html> 