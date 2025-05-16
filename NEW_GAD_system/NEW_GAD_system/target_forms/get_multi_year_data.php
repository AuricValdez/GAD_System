<?php
require_once '../config.php';

$campus = $_GET['campus'] ?? '';
$centerYear = isset($_GET['centerYear']) ? intval($_GET['centerYear']) : date('Y');

// Determine start and end years (2 years before and 2 years after center year)
$startYear = $centerYear - 2;
$endYear = $centerYear + 2;

$response = [
    'success' => true,
    'data' => []
];

try {
    if ($campus === 'All') {
        // Get all years in the specified range
        $yearsStmt = $pdo->prepare("SELECT DISTINCT year FROM target WHERE year BETWEEN ? AND ? ORDER BY year");
        $yearsStmt->execute([$startYear, $endYear]);
        $years = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // For each year, get the consolidated data
        foreach ($years as $year) {
            // Get sum of total_gaa and total_gad_fund for this year across all campuses
            $dataStmt = $pdo->prepare("
                SELECT 
                    SUM(total_gaa) as total_gaa, 
                    SUM(total_gad_fund) as total_gad_fund,
                    COUNT(*) as campus_count 
                FROM target 
                WHERE year = ?
            ");
            $dataStmt->execute([$year]);
            $yearData = $dataStmt->fetch(PDO::FETCH_ASSOC);
            
            // Add to response
            $response['data'][] = [
                'year' => $year,
                'hasTarget' => $yearData['campus_count'] > 0,
                'total_gaa' => $yearData['total_gaa'],
                'total_gad_fund' => $yearData['total_gad_fund'],
                'campus_count' => $yearData['campus_count'],
            ];
        }
        
        // Fill in missing years
        $allYears = range($startYear, $endYear);
        $existingYears = array_column($response['data'], 'year');
        $missingYears = array_diff($allYears, $existingYears);
        
        foreach ($missingYears as $missingYear) {
            $response['data'][] = [
                'year' => $missingYear,
                'hasTarget' => false,
                'total_gaa' => null,
                'total_gad_fund' => null,
                'campus_count' => 0,
            ];
        }
        
        // Sort the response by year
        usort($response['data'], function($a, $b) {
            return $a['year'] <=> $b['year'];
        });
    } else {
        // The original logic for a specific campus
        $stmt = $pdo->prepare("
            SELECT year, total_gaa, total_gad_fund 
            FROM target 
            WHERE campus = ? AND year BETWEEN ? AND ? 
            ORDER BY year
        ");
        $stmt->execute([$campus, $startYear, $endYear]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create array of years with target data or placeholders for years without data
        for ($year = $startYear; $year <= $endYear; $year++) {
            $yearData = [
                'year' => $year,
                'hasTarget' => false,
                'total_gaa' => null,
                'total_gad_fund' => null
            ];
            
            // Check if this year has target data
            foreach ($rows as $row) {
                if ($row['year'] == $year) {
                    $yearData['hasTarget'] = true;
                    $yearData['total_gaa'] = $row['total_gaa'];
                    $yearData['total_gad_fund'] = $row['total_gad_fund'];
                    break;
                }
            }
            
            $response['data'][] = $yearData;
        }
    }
} catch (PDOException $e) {
    $response = [
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ];
    error_log('Error fetching multi-year data: ' . $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);
