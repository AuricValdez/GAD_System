<?php
require_once '../config.php';

try {
    // Create signatories table
    $sql = "CREATE TABLE IF NOT EXISTS signatories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name1 VARCHAR(255) NOT NULL,
        gad_head_secretariat VARCHAR(255) NOT NULL,
        name2 VARCHAR(255) NOT NULL,
        vice_chancellor_rde VARCHAR(255) NOT NULL,
        name3 VARCHAR(255) NOT NULL,
        chancellor VARCHAR(255) NOT NULL,
        name4 VARCHAR(255) NOT NULL,
        asst_director_gad VARCHAR(255) NOT NULL,
        name5 VARCHAR(255) NOT NULL,
        head_extension_services VARCHAR(255) NOT NULL,
        name6 VARCHAR(255) DEFAULT NULL,
        vice_chancellor_admin_finance VARCHAR(255) DEFAULT NULL,
        name7 VARCHAR(255) DEFAULT NULL,
        dean VARCHAR(255) DEFAULT 'Dean',
        campus VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "Signatories table created successfully";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?> 