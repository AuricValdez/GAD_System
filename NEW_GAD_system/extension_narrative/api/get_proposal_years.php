<?php
session_start();
header('Content-Type: application/json');

// For debugging - log all available years
error_log("DEBUG: Starting get_proposal_years.php");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in',
        'code' => 'AUTH_ERROR'
    ]);
    exit();
}

// Include the database connection
require_once('../../includes/db_connection.php');

// Function to get database connection if include fails
if (!function_exists('getConnection')) {
    function getConnection() {
        try {
            $conn = new PDO(
                "mysql:host=localhost;dbname=gad_db;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            return $conn;
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
}

try {
    // Get parameters from request
    $campus = isset($_GET['campus']) ? $_GET['campus'] : null;

    // Get database connection
    $conn = isset($pdo) ? $pdo : getConnection();
    
    // IMPROVED: Get years from both ppas_forms and narrative_entries
    $allYears = [];
    
    // 1. Get years from narrative_entries using the year field directly
    $sql1 = "SELECT DISTINCT year FROM narrative_entries";
    if ($campus) {
        $sql1 .= " WHERE campus = :campus";
    }
    $stmt1 = $conn->prepare($sql1);
    if ($campus) {
        $stmt1->bindParam(':campus', $campus);
    }
    $stmt1->execute();
    $narrativeYears = $stmt1->fetchAll(PDO::FETCH_COLUMN, 0);
    error_log("DEBUG: Years from narrative_entries: " . json_encode($narrativeYears));
    
    // 2. Get years from ppas_forms
    $sql2 = "SELECT DISTINCT year FROM ppas_forms";
    if ($campus) {
        $sql2 .= " WHERE campus = :campus";
    }
    $stmt2 = $conn->prepare($sql2);
    if ($campus) {
        $stmt2->bindParam(':campus', $campus);
    }
    $stmt2->execute();
    $ppasYears = $stmt2->fetchAll(PDO::FETCH_COLUMN, 0);
    error_log("DEBUG: Years from ppas_forms: " . json_encode($ppasYears));
    
    // 3. Combine and format years
    $combinedYears = array_unique(array_merge($narrativeYears, $ppasYears));
    rsort($combinedYears); // Sort in descending order (newest first)
    error_log("DEBUG: Combined years: " . json_encode($combinedYears));
    
    // Format into the expected array structure
    foreach ($combinedYears as $year) {
        if (!empty($year)) {
            $allYears[] = ['year' => $year];
        }
    }
    
    // If no years were found, fallback to the current year and a few previous years
    if (empty($allYears)) {
        error_log("DEBUG: No years found, using fallback");
        $currentYear = (int)date('Y');
        for ($i = 0; $i <= 5; $i++) {
            $allYears[] = ['year' => $currentYear - $i];
        }
    }
    
    // For debugging
    error_log("Narrative Years: " . json_encode($narrativeYears));
    error_log("PPAS Years: " . json_encode($ppasYears));
    error_log("Combined Years: " . json_encode($allYears));
    
    $years = $allYears;
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'data' => $years
    ]);

} catch (Exception $e) {
    // Log the error and return an error response
    error_log("Error fetching proposal years: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching proposal years: ' . $e->getMessage(),
        'code' => 'SERVER_ERROR'
    ]);
} 