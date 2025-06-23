<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

try {
    // Check if the academic_ranks table exists (plural form)
    $checkTable = $pdo->query("SHOW TABLES LIKE 'academic_ranks'");
    $tableExists = $checkTable->rowCount() > 0;
    
    if (!$tableExists) {
        // Create the academic_ranks table (plural form)
        $pdo->exec("
            CREATE TABLE academic_ranks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                academic_rank VARCHAR(100) NOT NULL,
                salary_grade INT NOT NULL,
                monthly_salary DECIMAL(10,2) NOT NULL,
                hourly_rate DECIMAL(10,2) GENERATED ALWAYS AS (monthly_salary / 176) STORED
            )
        ");
        
        // Insert the academic ranks data
        $insertStmt = $pdo->prepare("
            INSERT INTO academic_ranks (academic_rank, salary_grade, monthly_salary) 
            VALUES (?, ?, ?)
        ");
        
        // Academic ranks data
        $academicRanks = [
            ['Instructor I', 8, 31000],
            ['Instructor II', 9, 35000],
            ['Instructor III', 10, 43000],
            ['College Lecturer', 11, 50000],
            ['Senior Lecturer', 12, 55000],
            ['Master Lecturer', 13, 60000],
            ['Assistant Professor II', 16, 65000],
            ['Associate Professor I', 19, 70000],
            ['Associate Professor II', 20, 75000],
            ['Professor I', 22, 80000],
            ['Professor II', 23, 85000],
            ['Professor III', 24, 90000],
            ['Professor IV', 25, 95000]
        ];
        
        // Insert each rank
        foreach ($academicRanks as $rank) {
            $insertStmt->execute($rank);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Academic ranks table created and populated successfully.'
        ]);
    } else {
        // Check if the table has data
        $checkData = $pdo->query("SELECT COUNT(*) as count FROM academic_ranks");
        $dataCount = $checkData->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($dataCount == 0) {
            // Table exists but is empty, insert data
            $insertStmt = $pdo->prepare("
                INSERT INTO academic_ranks (academic_rank, salary_grade, monthly_salary) 
                VALUES (?, ?, ?)
            ");
            
            // Academic ranks data
            $academicRanks = [
                ['Instructor I', 8, 31000],
                ['Instructor II', 9, 35000],
                ['Instructor III', 10, 43000],
                ['College Lecturer', 11, 50000],
                ['Senior Lecturer', 12, 55000],
                ['Master Lecturer', 13, 60000],
                ['Assistant Professor II', 16, 65000],
                ['Associate Professor I', 19, 70000],
                ['Associate Professor II', 20, 75000],
                ['Professor I', 22, 80000],
                ['Professor II', 23, 85000],
                ['Professor III', 24, 90000],
                ['Professor IV', 25, 95000]
            ];
            
            // Insert each rank
            foreach ($academicRanks as $rank) {
                $insertStmt->execute($rank);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Academic ranks data inserted successfully.'
            ]);
        } else {
            // Table exists and has data
            echo json_encode([
                'success' => true,
                'message' => 'Academic ranks table already exists with data.'
            ]);
        }
    }
} catch(PDOException $e) {
    error_log("Database error in create_academic_ranks.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 