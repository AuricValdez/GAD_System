<?php
require_once '../config.php';

$campus = $_GET['campus'] ?? '';
$currentYear = date('Y');
$years = [];

if ($campus) {
    try {
        // Get years that already have targets for this campus
        $stmt = $pdo->prepare("SELECT DISTINCT year FROM target WHERE campus = ?");
        $stmt->execute([$campus]);
        $existingYears = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Generate the next 5 years that don't have targets
        $year = $currentYear;
        $count = 0;
        while ($count < 5) {
            if (!in_array($year, $existingYears)) {
                $years[] = $year;
                $count++;
            }
            $year++;
        }

    } catch(PDOException $e) {
        error_log("Error checking years: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Database error occurred']);
        exit;
    }
}

header('Content-Type: application/json');
echo json_encode($years);
?>
