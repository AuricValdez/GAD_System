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

// Function to get database connection
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

// Define required fields and their SQL definitions
$required_fields = [
    'ppas_form_id' => 'INT DEFAULT NULL',
    'results' => 'TEXT DEFAULT NULL',
    'expected_results' => 'TEXT DEFAULT NULL',
    'lessons' => 'TEXT DEFAULT NULL',
    'lessons_learned' => 'TEXT DEFAULT NULL',
    'what_worked' => 'TEXT DEFAULT NULL',
    'issues' => 'TEXT DEFAULT NULL',
    'issues_concerns' => 'TEXT DEFAULT NULL',
    'recommendations' => 'TEXT DEFAULT NULL'
];

// Field updates status
$field_updates = [];

// Check if narrative_entries table exists
try {
    $conn = getConnection();
    $table_check = $conn->query("SHOW TABLES LIKE 'narrative_entries'");
    $table_exists = $table_check->rowCount() > 0;
    
    if (!$table_exists) {
        // Create the table if it doesn't exist
        $sql = "CREATE TABLE `narrative_entries` (
            `id` int NOT NULL AUTO_INCREMENT,
            `campus` varchar(255) NOT NULL,
            `year` varchar(10) NOT NULL,
            `title` varchar(255) NOT NULL,
            `background` text DEFAULT NULL,
            `participants` text DEFAULT NULL,
            `topics` text DEFAULT NULL,
            `results` text DEFAULT NULL,
            `lessons` text DEFAULT NULL,
            `what_worked` text DEFAULT NULL,
            `issues` text DEFAULT NULL,
            `recommendations` text DEFAULT NULL,
            `ps_attribution` varchar(255) DEFAULT NULL,
            `evaluation` text DEFAULT NULL,
            `activity_ratings` text DEFAULT NULL,
            `timeliness_ratings` text DEFAULT NULL,
            `photo_path` varchar(255) DEFAULT NULL,
            `photo_paths` text DEFAULT NULL,
            `photo_caption` text DEFAULT NULL,
            `gender_issue` text DEFAULT NULL,
            `ppas_form_id` int DEFAULT NULL,
            `created_by` varchar(100) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_by` varchar(100) DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_ppas_form_id` (`ppas_form_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $conn->exec($sql);
        $field_updates[] = "Created narrative_entries table with all required fields";
    } else {
        // Get current table structure
        $columns_query = $conn->query("DESCRIBE narrative_entries");
        $existing_columns = $columns_query->fetchAll(PDO::FETCH_COLUMN);
        
        // Check for missing fields and add them
        foreach ($required_fields as $field => $definition) {
            if (!in_array($field, $existing_columns)) {
                $sql = "ALTER TABLE `narrative_entries` ADD COLUMN `$field` $definition";
                $conn->exec($sql);
                $field_updates[] = "Added missing field: $field ($definition)";
            }
        }
        
        // Add index on ppas_form_id if it doesn't exist
        $indexes_query = $conn->query("SHOW INDEX FROM narrative_entries WHERE Key_name = 'idx_ppas_form_id'");
        if ($indexes_query->rowCount() === 0) {
            $sql = "ALTER TABLE `narrative_entries` ADD INDEX `idx_ppas_form_id` (`ppas_form_id`)";
            $conn->exec($sql);
            $field_updates[] = "Added index on ppas_form_id field";
        }
    }
    
    // Check for existing entries and update them if needed
    $entries_query = $conn->query("SELECT * FROM narrative_entries");
    $entries = $entries_query->fetchAll(PDO::FETCH_ASSOC);
    
    $updated_entries = 0;
    
    // If there are entries, check for alternative field names and copy data if needed
    if (count($entries) > 0) {
        $field_mappings = [
            'expected_results' => 'results',
            'lessons_learned' => 'lessons',
            'issues_concerns' => 'issues'
        ];
        
        foreach ($entries as $entry) {
            $updates = [];
            
            foreach ($field_mappings as $alt_field => $standard_field) {
                // If standard field is empty but alternative field has data, or vice versa
                if (
                    (empty($entry[$standard_field]) && !empty($entry[$alt_field])) ||
                    (!empty($entry[$standard_field]) && empty($entry[$alt_field]))
                ) {
                    $value = !empty($entry[$standard_field]) ? $entry[$standard_field] : $entry[$alt_field];
                    
                    // Update both fields to have the same value
                    $update_sql = "UPDATE narrative_entries SET 
                        `$standard_field` = :value,
                        `$alt_field` = :value
                        WHERE id = :id";
                    
                    $stmt = $conn->prepare($update_sql);
                    $stmt->execute([
                        ':value' => $value,
                        ':id' => $entry['id']
                    ]);
                    
                    $updates[] = "$standard_field and $alt_field";
                }
            }
            
            if (!empty($updates)) {
                $updated_entries++;
            }
        }
        
        if ($updated_entries > 0) {
            $field_updates[] = "Updated $updated_entries entries to sync data between standard and alternative field names";
        }
    }
    
    $success = true;
    $message = "Table structure updated successfully";
    
} catch (Exception $e) {
    $success = false;
    $message = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Narrative Table Structure</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
        }
        .container {
            max-width: 800px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Update Narrative Table Structure</h1>
        
        <?php if ($success): ?>
        <div class="alert alert-success">
            <h4>Success</h4>
            <p><?php echo $message; ?></p>
        </div>
        <?php else: ?>
        <div class="alert alert-danger">
            <h4>Error</h4>
            <p><?php echo $message; ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($field_updates)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5>Updates Made</h5>
            </div>
            <div class="card-body">
                <ul>
                    <?php foreach ($field_updates as $update): ?>
                    <li><?php echo $update; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>Current Table Structure</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    $conn = getConnection();
                    $columns_query = $conn->query("DESCRIBE narrative_entries");
                    $columns = $columns_query->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($columns) > 0):
                ?>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Null</th>
                            <th>Key</th>
                            <th>Default</th>
                            <th>Extra</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($columns as $column): ?>
                        <tr class="<?php echo in_array($column['Field'], array_keys($required_fields)) ? 'table-success' : ''; ?>">
                            <td><?php echo $column['Field']; ?></td>
                            <td><?php echo $column['Type']; ?></td>
                            <td><?php echo $column['Null']; ?></td>
                            <td><?php echo $column['Key']; ?></td>
                            <td><?php echo $column['Default']; ?></td>
                            <td><?php echo $column['Extra']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
                    else:
                        echo "<p>No table structure found.</p>";
                    endif;
                } catch (Exception $e) {
                    echo "<p class='text-danger'>Error fetching table structure: " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="test_narrative_fields.php" class="btn btn-primary">Go to Test Narrative Fields</a>
            <a href="check_narrative_entries.php" class="btn btn-secondary">Check Narrative Entries</a>
            <a href="add_test_narrative.php" class="btn btn-info">Add Test Narrative</a>
        </div>
    </div>
</body>
</html> 