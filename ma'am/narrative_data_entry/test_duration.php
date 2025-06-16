<?php
require_once('../includes/db_connection.php');

try {
    // Get all activities with their durations
    $query = "SELECT id, activity, total_duration FROM ppas_forms ORDER BY id DESC LIMIT 10";
    $stmt = $conn->query($query);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Recent Activities and Their Durations:\n";
    echo "=====================================\n";
    foreach ($activities as $activity) {
        echo "ID: {$activity['id']}\n";
        echo "Activity: {$activity['activity']}\n";
        echo "Raw Duration: " . var_export($activity['total_duration'], true) . "\n";
        echo "Type: " . gettype($activity['total_duration']) . "\n";
        echo "Length: " . strlen($activity['total_duration']) . "\n";
        echo "Numeric? " . (is_numeric($activity['total_duration']) ? 'Yes' : 'No') . "\n";
        echo "Float Value: " . floatval($activity['total_duration']) . "\n";
        echo "-------------------------------------\n";
    }

    // Check table structure
    $query = "SHOW CREATE TABLE ppas_forms";
    $stmt = $conn->query($query);
    $tableInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\nTable Structure:\n";
    echo "================\n";
    echo $tableInfo['Create Table'] . "\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 