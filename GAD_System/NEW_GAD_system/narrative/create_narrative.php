<?php
// This script will update narrative_forms to store ppas_activity_title

// Include database connection
require_once '../includes/db_connect.php';

try {
    // First, get all narrative entries that don't have ppas_activity_title set
    $selectSql = "SELECT n.id, n.ppas_id, p.activity 
                 FROM narrative_forms n 
                 JOIN ppas_forms p ON n.ppas_id = p.id 
                 WHERE n.ppas_activity_title IS NULL";
    
    $stmt = $conn->prepare($selectSql);
    $stmt->execute();
    $narratives = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($narratives) . " narratives to update<br>";
    
    // Update each narrative with the corresponding ppas_forms activity title
    $updateSql = "UPDATE narrative_forms SET ppas_activity_title = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    
    $updatedCount = 0;
    foreach ($narratives as $narrative) {
        $updateStmt->execute([$narrative['activity'], $narrative['id']]);
        $updatedCount++;
        echo "Updated narrative ID " . $narrative['id'] . " with activity title: " . $narrative['activity'] . "<br>";
    }
    
    echo "Successfully updated $updatedCount narratives";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?> 