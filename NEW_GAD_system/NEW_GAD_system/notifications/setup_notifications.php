<?php
// Create the notifications directory if it doesn't exist
if (!is_dir(__DIR__)) {
    mkdir(__DIR__, 0755, true);
}

// Include the database configuration
require_once '../config.php';

// Create gbp_notifications table
$sql = "CREATE TABLE IF NOT EXISTS gbp_notifications (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    gbp_id INT(11) NOT NULL,
    campus VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(20) NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gbp_id) REFERENCES gpb_entries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Table gbp_notifications created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Add missing count_pending function to gbp_api.php if it doesn't exist
$gbpApiFile = '../approval/gbp_api.php';
$gbpApiContent = file_get_contents($gbpApiFile);

if (strpos($gbpApiContent, 'function countPendingEntries') === false) {
    // Create the countPendingEntries function to be appended
    $countPendingFunction = <<<'EOD'

/**
 * Count the number of pending entries
 */
function countPendingEntries() {
    global $conn;
    
    try {
        $query = "SELECT COUNT(*) as count FROM gpb_entries WHERE status = 'Pending'";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'count' => $row['count']]);
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to count pending entries.']);
    }
}
?>
EOD;

    // Find the last function declaration in the file
    $lastFunctionPos = strrpos($gbpApiContent, 'function');
    if ($lastFunctionPos !== false) {
        // Find the end of the last function (assuming it's followed by the end PHP tag)
        $endPhpPos = strrpos($gbpApiContent, '?>');
        if ($endPhpPos !== false) {
            // Add the new function before the closing PHP tag
            $newContent = substr($gbpApiContent, 0, $endPhpPos) . $countPendingFunction;
            file_put_contents($gbpApiFile, $newContent);
            echo "Added countPendingEntries function to gbp_api.php<br>";
        } else {
            // No closing PHP tag, append to the end
            $newContent = $gbpApiContent . $countPendingFunction;
            file_put_contents($gbpApiFile, $newContent);
            echo "Added countPendingEntries function to gbp_api.php<br>";
        }
    } else {
        echo "Could not find position to add countPendingEntries function<br>";
    }
} else {
    echo "countPendingEntries function already exists in gbp_api.php<br>";
}

// Update the case statement in gbp_api.php to include count_pending action
if (strpos($gbpApiContent, "'count_pending'") === false) {
    // Find the switch statement
    $switchPos = strpos($gbpApiContent, 'switch ($action)');
    if ($switchPos !== false) {
        // Find the end of the case statements
        $defaultCasePos = strpos($gbpApiContent, 'default:', $switchPos);
        if ($defaultCasePos !== false) {
            // Add new case before the default case
            $newCase = "        case 'count_pending':\n            countPendingEntries();\n            break;\n";
            $newContent = substr($gbpApiContent, 0, $defaultCasePos) . $newCase . substr($gbpApiContent, $defaultCasePos);
            file_put_contents($gbpApiFile, $newContent);
            echo "Added 'count_pending' case to gbp_api.php<br>";
        } else {
            echo "Could not find default case in switch statement<br>";
        }
    } else {
        echo "Could not find switch statement in gbp_api.php<br>";
    }
} else {
    echo "'count_pending' case already exists in gbp_api.php<br>";
}

echo "<p>Notifications system setup completed. You can now use the notifications feature.</p>";
?> 