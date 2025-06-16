<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

try {
    // Get the current year
    $currentYear = date('Y');
    
    // Debug output
    error_log("Current year: " . $currentYear);
    error_log("Fetching all PPAs");
    
    $query = "
        SELECT 
            id,
            title,
            start_date as date,
            total_duration,
            approved_budget,
            source_of_budget,
            ps_attribution,
            QUARTER(start_date) as quarter_num
        FROM ppas_forms 
        WHERE YEAR(start_date) = :year 
        ORDER BY start_date ASC
    ";
    
    // Debug output
    error_log("SQL Query: " . $query);
    
    $stmt = $pdo->prepare($query);
    $params = [
        'year' => $currentYear
    ];
    error_log("Query parameters: " . print_r($params, true));
    
    $stmt->execute($params);
    
    $ppas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug output with count
    error_log("Found " . count($ppas) . " PPAs in total");
    
    if (empty($ppas)) {
        error_log("No PPAs found for year " . $currentYear);
        // Return empty array instead of error
        echo json_encode([]);
        exit;
    }
    
    // Add quarter information to each PPA
    foreach ($ppas as &$ppa) {
        $quarter = $ppa['quarter_num'] ?: 1; // Default to Q1 if quarter is null
        $ppa['quarter'] = $quarter;
        $ppa['quarter_text'] = [
            1 => "First Quarter",
            2 => "Second Quarter", 
            3 => "Third Quarter",
            4 => "Fourth Quarter"
        ][$quarter];
    }
    
    echo json_encode($ppas);
} catch(PDOException $e) {
    error_log("Database error in get_all_ppas.php: " . $e->getMessage());
    error_log("Error code: " . $e->getCode());
    error_log("Error trace: " . $e->getTraceAsString());
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
?> 