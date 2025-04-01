<?php
require_once '../config.php';

$campus = $_GET['campus'] ?? 'Lipa';
$centerYear = $_GET['centerYear'] ?? date('Y');

// Convert center year to integer
$centerYear = intval($centerYear);

// Calculate the range of years (2 before and 2 after the center year)
$startYear = $centerYear - 2;
$endYear = $centerYear + 2;

$data = [];

try {
    // Fetch data for all years in the range
    $stmt = $pdo->prepare("
        SELECT campus, year, total_gaa, total_gad_fund 
        FROM target 
        WHERE campus = ? AND year BETWEEN ? AND ?
        ORDER BY year
    ");
    $stmt->execute([$campus, $startYear, $endYear]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create an array with all years in range, filling in missing years with null values
    for ($year = $startYear; $year <= $endYear; $year++) {
        $yearData = null;
        foreach ($results as $row) {
            if ($row['year'] == $year) {
                $yearData = $row;
                break;
            }
        }
        $data[] = [
            'year' => $year,
            'hasTarget' => $yearData !== null,
            'total_gaa' => $yearData ? $yearData['total_gaa'] : null,
            'total_gad_fund' => $yearData ? $yearData['total_gad_fund'] : null
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'centerYear' => $centerYear
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
    error_log("Error fetching multi-year data: " . $e->getMessage());
}
