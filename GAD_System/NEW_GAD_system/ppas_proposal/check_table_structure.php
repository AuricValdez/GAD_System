<?php
// Database connection
require_once __DIR__ . '/../includes/db_connection.php';

// Set content type to plain text for easier reading
header('Content-Type: text/plain');

try {
    // Check if the table exists
    $sql = "SHOW TABLES LIKE 'gad_proposals'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo "The table 'gad_proposals' does not exist.\n";
        exit;
    }
    
    // Get table structure
    $sql = "DESCRIBE gad_proposals";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    echo "Structure of 'gad_proposals' table:\n";
    echo str_repeat('-', 80) . "\n";
    echo sprintf("%-20s %-20s %-10s %-10s %-20s\n", "Field", "Type", "Null", "Key", "Default");
    echo str_repeat('-', 80) . "\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf(
            "%-20s %-20s %-10s %-10s %-20s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Key'], 
            $row['Default'] ?? 'NULL'
        );
    }
    
    echo "\n\n";
    
    // Check if project and program columns exist
    $projectColumnExists = false;
    $programColumnExists = false;
    
    $sql = "SHOW COLUMNS FROM gad_proposals LIKE 'project'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $projectColumnExists = $stmt->rowCount() > 0;
    
    $sql = "SHOW COLUMNS FROM gad_proposals LIKE 'program'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $programColumnExists = $stmt->rowCount() > 0;
    
    echo "Project column exists: " . ($projectColumnExists ? "Yes" : "No") . "\n";
    echo "Program column exists: " . ($programColumnExists ? "Yes" : "No") . "\n";
    
    // Additional check for ppas_forms table
    $sql = "SHOW TABLES LIKE 'ppas_forms'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo "\nThe table 'ppas_forms' does not exist.\n";
        exit;
    }
    
    // Get ppas_forms structure
    $sql = "DESCRIBE ppas_forms";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    echo "\nStructure of 'ppas_forms' table:\n";
    echo str_repeat('-', 80) . "\n";
    echo sprintf("%-20s %-20s %-10s %-10s %-20s\n", "Field", "Type", "Null", "Key", "Default");
    echo str_repeat('-', 80) . "\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf(
            "%-20s %-20s %-10s %-10s %-20s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Key'], 
            $row['Default'] ?? 'NULL'
        );
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 