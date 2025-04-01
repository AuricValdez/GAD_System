<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = new PDO(
        "mysql:host=localhost;dbname=gad_db;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "Database connection successful!\n";
    
    // Test query
    $stmt = $db->query("SELECT COUNT(*) as count FROM gad_proposals");
    $result = $stmt->fetch();
    echo "Number of proposals: " . $result['count'] . "\n";
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
} 