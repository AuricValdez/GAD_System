<?php
require_once __DIR__ . '/../config.php';

// Define the file path
$filePath = __DIR__ . '/narrative_handler.php';

// Check if the file exists
if (!file_exists($filePath)) {
    die("Error: narrative_handler.php not found at $filePath");
}

// Read the file content
$content = file_get_contents($filePath);
if ($content === false) {
    die("Error: Could not read narrative_handler.php");
}

// Make a backup of the original file
$backupPath = __DIR__ . '/backup/narrative_handler_' . date('Y-m-d_H-i-s') . '.php';
if (!is_dir(__DIR__ . '/backup')) {
    mkdir(__DIR__ . '/backup', 0755, true);
}

if (!file_put_contents($backupPath, $content)) {
    die("Error: Could not create backup file");
}

echo "<p>Backup created at: $backupPath</p>";

// Check if the file already has the fix
if (strpos($content, '$evaluation = isset($_POST[\'evaluation\']) ? $_POST[\'evaluation\'] : \'\';') !== false) {
    echo "<p>The file already has the fix applied.</p>";
} else {
    // Replace the sanitize_input call for evaluation
    $pattern = '/\$evaluation\s*=\s*isset\(\$_POST\[\'evaluation\'\]\)\s*\?\s*sanitize_input\(\$_POST\[\'evaluation\'\]\)\s*:\s*\'\';/';
    $replacement = '$evaluation = isset($_POST[\'evaluation\']) ? $_POST[\'evaluation\'] : \'\';';
    
    $updatedContent = preg_replace($pattern, $replacement, $content);
    
    // Check if the replacement was successful
    if ($updatedContent === $content) {
        echo "<p>Warning: Could not find the evaluation line to replace.</p>";
    } else {
        // Write the updated content back to the file
        if (file_put_contents($filePath, $updatedContent)) {
            echo "<p>Successfully updated narrative_handler.php to skip sanitizing the evaluation data.</p>";
        } else {
            echo "<p>Error: Could not write the updated content back to the file.</p>";
        }
    }
}

// Add debugging code to log evaluation data
if (strpos($content, "error_log(\"Raw evaluation data:") === false) {
    $pattern = '/\$evaluation\s*=\s*isset\(\$_POST\[\'evaluation\'\]\)\s*\?\s*\$_POST\[\'evaluation\'\]\s*:\s*\'\';/';
    $replacement = '$evaluation = isset($_POST[\'evaluation\']) ? $_POST[\'evaluation\'] : \'\';' . PHP_EOL . 
                   '        // Debug log the evaluation data' . PHP_EOL . 
                   '        error_log("Raw evaluation data: " . (isset($_POST[\'evaluation\']) ? $_POST[\'evaluation\'] : \'not set\'));' . PHP_EOL . 
                   '        // Validate that it\'s valid JSON' . PHP_EOL . 
                   '        if (!empty($evaluation)) {' . PHP_EOL . 
                   '            $json_valid = json_decode($evaluation) !== null;' . PHP_EOL . 
                   '            error_log("Evaluation JSON valid: " . ($json_valid ? \'yes\' : \'no\'));' . PHP_EOL . 
                   '        }';
    
    $updatedContent = preg_replace($pattern, $replacement, $updatedContent ?? $content);
    
    // Check if the replacement was successful
    if ($updatedContent === ($updatedContent ?? $content)) {
        echo "<p>Warning: Could not add debugging code.</p>";
    } else {
        // Write the updated content back to the file
        if (file_put_contents($filePath, $updatedContent)) {
            echo "<p>Successfully added debugging code to log evaluation data.</p>";
        } else {
            echo "<p>Error: Could not write the updated content with debugging code back to the file.</p>";
        }
    }
}

// Add code to log database operation results
$pattern = '/if \(\$stmt->execute\(\)\) \{/';
$replacement = 'if ($stmt->execute()) {' . PHP_EOL . 
               '            // Log success' . PHP_EOL . 
               '            error_log("Successfully saved narrative with ID: " . $newId);' . PHP_EOL . 
               '            ' . PHP_EOL . 
               '            // Check if the evaluation was saved correctly' . PHP_EOL . 
               '            $checkQuery = "SELECT evaluation FROM narrative_entries WHERE id = ?";' . PHP_EOL . 
               '            $checkStmt = $conn->prepare($checkQuery);' . PHP_EOL . 
               '            $checkStmt->bind_param("i", $newId);' . PHP_EOL . 
               '            $checkStmt->execute();' . PHP_EOL . 
               '            $checkResult = $checkStmt->get_result();' . PHP_EOL . 
               '            if ($checkResult->num_rows > 0) {' . PHP_EOL . 
               '                $row = $checkResult->fetch_assoc();' . PHP_EOL . 
               '                error_log("Saved evaluation data: " . $row[\'evaluation\']);' . PHP_EOL . 
               '            }';

$content = file_get_contents($filePath);
if (strpos($content, "Successfully saved narrative with ID:") === false) {
    $updatedContent = preg_replace($pattern, $replacement, $content);
    
    // Check if the replacement was successful
    if ($updatedContent === $content) {
        echo "<p>Warning: Could not add database operation logging code.</p>";
    } else {
        // Write the updated content back to the file
        if (file_put_contents($filePath, $updatedContent)) {
            echo "<p>Successfully added code to log database operation results.</p>";
        } else {
            echo "<p>Error: Could not write the updated content with logging code back to the file.</p>";
        }
    }
}

echo "<p>All updates completed.</p>";
?> 