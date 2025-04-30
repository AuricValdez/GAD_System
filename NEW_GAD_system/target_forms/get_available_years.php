<?php
require_once 'config.php';

$campus = $_GET['campus'] ?? '';
$availableYears = [];

if ($campus) {
    try {
        // Get current year
        $currentYear = date('Y');
        
        // Get years that are already used for this campus
        $stmt = $pdo->prepare("SELECT year FROM target WHERE campus = ?");
        $stmt->execute([$campus]);
        $usedYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Generate available years (current year and 4 future years)
        $year = $currentYear;
        while (count($availableYears) < 5) {
            if (!in_array($year, $usedYears)) {
                $availableYears[] = $year;
            }
            $year++;
        }
        
    } catch(PDOException $e) {
        error_log("Error fetching years: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($availableYears); 