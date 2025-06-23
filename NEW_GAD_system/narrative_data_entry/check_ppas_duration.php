<?php
require_once('../includes/db_connection.php');

try {
    // Check table structure
    $query = "DESCRIBE ppas_forms";
    $stmt = $conn->query($query);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Table Structure:\n";
    foreach ($columns as $column) {
        if (strpos(strtolower($column['Field']), 'duration') !== false) {
            echo "Duration column found: {$column['Field']} ({$column['Type']})\n";
        }
    }
    
    // Check sample data
    $query = "SELECT id, total_duration, total_duration_hours 
              FROM ppas_forms 
              WHERE id = :activity_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':activity_id', $_GET['activity_id'], PDO::PARAM_INT);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nSample Data for ID {$_GET['activity_id']}:\n";
    print_r($row);
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} 