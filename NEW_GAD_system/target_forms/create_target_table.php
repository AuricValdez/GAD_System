<?php
require_once '../config.php';

try {
    // Create target table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS target (
        id INT AUTO_INCREMENT PRIMARY KEY,
        campus VARCHAR(255) NOT NULL,
        year INT NOT NULL,
        total_gaa DECIMAL(20,2) NOT NULL,
        gad_fund DECIMAL(20,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY campus_year_unique (campus, year)
    )";
    
    $pdo->exec($sql);
    echo "Target table created successfully";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
