<?php
// Start output buffering immediately
ob_start();

// Disable all error reporting and display
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');

try {
    // Check if config file exists
    $configFile = __DIR__ . '/../includes/db_connection.php';
    if (!file_exists($configFile)) {
        throw new Exception('System configuration error. Please contact administrator.');
    }

    require_once $configFile;

    // Verify database constants are defined
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new Exception('Database configuration error. Please contact administrator.');
    }

    // Test database connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if (isset($_GET['year'])) {
        // Fetch quarters for the selected year
        $year = $_GET['year'];
        $query = "SELECT DISTINCT quarter FROM ppas_forms WHERE year = ? ORDER BY quarter";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$year]);
        $quarters = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $response = [
            'success' => true,
            'quarters' => $quarters,
            'message' => empty($quarters) ? 'No quarters found for the selected year' : null
        ];
    } else {
        // Fetch all available years
        $query = "SELECT DISTINCT year FROM ppas_forms ORDER BY year DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $response = [
            'success' => true,
            'years' => $years,
            'message' => empty($years) ? 'No years found in the database' : null
        ];
    }
} catch (PDOException $e) {
    error_log("Database error in get_available_data.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Database connection error. Please contact administrator.'
    ];
} catch (Exception $e) {
    error_log("Error in get_available_data.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Clear any buffered output
ob_end_clean();

// Send JSON response
echo json_encode($response);
exit; 