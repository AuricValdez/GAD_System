<?php
// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Checking for proposal_id column in ppas_forms ===\n\n";

try {
    // Connect to database
    $conn = new PDO("mysql:host=localhost;dbname=gad_db", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to database successfully\n\n";
    
    // Check if ppas_forms table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'ppas_forms'");
    if ($stmt->rowCount() == 0) {
        echo "❌ ppas_forms table does not exist\n";
        exit;
    }
    
    echo "✅ ppas_forms table exists\n\n";
    
    // Check for proposal_id column
    $stmt = $conn->query("SHOW COLUMNS FROM ppas_forms LIKE 'proposal_id'");
    if ($stmt->rowCount() > 0) {
        echo "✅ proposal_id column exists in ppas_forms\n";
    } else {
        echo "❌ proposal_id column does NOT exist in ppas_forms\n\n";
        
        // List actual columns
        $stmt = $conn->query("DESCRIBE ppas_forms");
        echo "Actual columns in ppas_forms:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    }
    
    // Count records in ppas_forms
    $stmt = $conn->query("SELECT COUNT(*) as count FROM ppas_forms");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nTotal records in ppas_forms: " . $result['count'] . "\n";
    
    // Get sample data
    $stmt = $conn->query("SELECT id, campus, year, activity FROM ppas_forms LIMIT 2");
    echo "\nSample records:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- ID: " . $row['id'] . ", Campus: " . $row['campus'] . ", Year: " . $row['year'] . ", Activity: " . $row['activity'] . "\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} 