<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

try {
    $result = [
        'academic_rank_exists' => false,
        'academic_ranks_exists' => false,
        'ppas_forms_exists' => false
    ];
    
    // Check academic_rank table
    $academicRankExists = $pdo->query("SHOW TABLES LIKE 'academic_rank'")->rowCount() > 0;
    $result['academic_rank_exists'] = $academicRankExists;
    
    if ($academicRankExists) {
        // Get columns for academic_rank
        $columns = $pdo->query("SHOW COLUMNS FROM academic_rank")->fetchAll(PDO::FETCH_ASSOC);
        $result['academic_rank_columns'] = $columns;
        
        // Get sample data from academic_rank
        $sample = $pdo->query("SELECT * FROM academic_rank LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
        $result['academic_rank_sample'] = $sample;
    }
    
    // Check academic_ranks table
    $academicRanksExists = $pdo->query("SHOW TABLES LIKE 'academic_ranks'")->rowCount() > 0;
    $result['academic_ranks_exists'] = $academicRanksExists;
    
    if ($academicRanksExists) {
        // Get columns for academic_ranks
        $columns = $pdo->query("SHOW COLUMNS FROM academic_ranks")->fetchAll(PDO::FETCH_ASSOC);
        $result['academic_ranks_columns'] = $columns;
        
        // Get sample data from academic_ranks
        $sample = $pdo->query("SELECT * FROM academic_ranks LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
        $result['academic_ranks_sample'] = $sample;
    }
    
    // Check ppas_forms table
    $ppasFormsExists = $pdo->query("SHOW TABLES LIKE 'ppas_forms'")->rowCount() > 0;
    $result['ppas_forms_exists'] = $ppasFormsExists;
    
    if ($ppasFormsExists) {
        // Get columns for ppas_forms
        $columns = $pdo->query("SHOW COLUMNS FROM ppas_forms")->fetchAll(PDO::FETCH_ASSOC);
        $result['ppas_forms_columns'] = $columns;
        
        // Get total_duration column info specifically
        $durationColumn = $pdo->query("SHOW COLUMNS FROM ppas_forms LIKE 'total_duration'")->fetch(PDO::FETCH_ASSOC);
        $result['total_duration_info'] = $durationColumn;
        
        // Get a sample row showing total_duration values
        $sample = $pdo->query("SELECT id, title, total_duration FROM ppas_forms LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
        $result['ppas_forms_duration_samples'] = $sample;
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?> 