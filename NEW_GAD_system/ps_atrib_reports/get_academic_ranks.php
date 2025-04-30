<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

try {
    // Use academic_ranks (plural) table which has all the fields we need
    $checkTable = $pdo->query("SHOW TABLES LIKE 'academic_ranks'");
    $tableExists = $checkTable->rowCount() > 0;
    
    if ($tableExists) {
        // Fetch from academic_ranks table
        $stmt = $pdo->query("SELECT id, academic_rank as rank_name, monthly_salary FROM academic_ranks ORDER BY monthly_salary ASC");
        $academicRanks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($academicRanks) > 0) {
            echo json_encode([
                'success' => true,
                'academicRanks' => $academicRanks
            ]);
            exit;
        }
    }
    
    // If no data found, provide default academic ranks
    $academicRanks = [
        ['id' => 1, 'rank_name' => 'Instructor I', 'monthly_salary' => 31000],
        ['id' => 2, 'rank_name' => 'Instructor II', 'monthly_salary' => 35000],
        ['id' => 3, 'rank_name' => 'Instructor III', 'monthly_salary' => 43000],
        ['id' => 4, 'rank_name' => 'College Lecturer', 'monthly_salary' => 50000],
        ['id' => 5, 'rank_name' => 'Senior Lecturer', 'monthly_salary' => 55000],
        ['id' => 6, 'rank_name' => 'Master Lecturer', 'monthly_salary' => 60000],
        ['id' => 7, 'rank_name' => 'Assistant Professor II', 'monthly_salary' => 65000],
        ['id' => 8, 'rank_name' => 'Associate Professor I', 'monthly_salary' => 70000],
        ['id' => 9, 'rank_name' => 'Associate Professor II', 'monthly_salary' => 75000],
        ['id' => 10, 'rank_name' => 'Professor I', 'monthly_salary' => 80000],
        ['id' => 11, 'rank_name' => 'Professor II', 'monthly_salary' => 85000],
        ['id' => 12, 'rank_name' => 'Professor III', 'monthly_salary' => 90000],
        ['id' => 13, 'rank_name' => 'Professor IV', 'monthly_salary' => 95000]
    ];
    
    echo json_encode([
        'success' => true,
        'academicRanks' => $academicRanks,
        'message' => 'Using default academic ranks as no table data was found'
    ]);
    
} catch(PDOException $e) {
    error_log("Database error in get_academic_ranks.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 