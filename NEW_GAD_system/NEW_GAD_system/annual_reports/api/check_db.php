<?php
header('Content-Type: application/json');

// Database connection
require_once '../config.php';

try {
    // Use the PDO connection from config.php
    // Check if the gpb_entries table exists
    $query = "SHOW TABLES LIKE 'gpb_entries'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        // Create the gpb_entries table
        $createTableSQL = "CREATE TABLE `gpb_entries` (
            `id` int NOT NULL AUTO_INCREMENT,
            `category` varchar(50) NOT NULL,
            `gender_issue` text NOT NULL,
            `cause_of_issue` text NOT NULL,
            `gad_objective` text NOT NULL,
            `relevant_agency` varchar(255) NOT NULL,
            `generic_activity` text NOT NULL,
            `specific_activities` text NOT NULL,
            `total_activities` int NOT NULL,
            `male_participants` int NOT NULL,
            `female_participants` int NOT NULL,
            `total_participants` int NOT NULL,
            `gad_budget` decimal(15,2) NOT NULL,
            `source_of_budget` varchar(255) NOT NULL,
            `responsible_unit` varchar(255) NOT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `campus` varchar(255) DEFAULT NULL,
            `year` int DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";
        
        $pdo->exec($createTableSQL);
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Database check completed successfully.',
        'tableExists' => $tableExists
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// PDO connections are automatically closed when the script ends
$stmt = null;
$pdo = null; 