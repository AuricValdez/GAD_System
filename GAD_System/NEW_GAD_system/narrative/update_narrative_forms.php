<?php
// This script will add the ppas_activity_title column to the narrative_forms table

// Include database connection
require_once '../includes/db_connect.php';

try {
    // Check if column exists first to prevent errors
    $checkSql = "SHOW COLUMNS FROM narrative_forms LIKE 'ppas_activity_title'";
    $stmt = $conn->prepare($checkSql);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Column doesn't exist, so add it
        $alterSql = "ALTER TABLE narrative_forms 
                    ADD COLUMN ppas_activity_title VARCHAR(255) COLLATE utf8mb4_general_ci DEFAULT NULL 
                    AFTER ppas_id";
        
        $conn->exec($alterSql);
        echo "Successfully added ppas_activity_title column to narrative_forms table<br>";
        
        // Now populate the column with data from ppas_forms
        $updateSql = "UPDATE narrative_forms n 
                     JOIN ppas_forms p ON n.ppas_id = p.id 
                     SET n.ppas_activity_title = p.activity";
        
        $conn->exec($updateSql);
        echo "Successfully populated ppas_activity_title column with data from ppas_forms";
    } else {
        echo "Column ppas_activity_title already exists in narrative_forms table";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?> 