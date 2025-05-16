<?php
// Prevent any output before we're ready
ob_start();

// Set the content type to JSON
header('Content-Type: application/json');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't output errors to the browser
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

// Log request for debugging
error_log("get_personnel.php accessed with search term: " . (isset($_GET['term']) ? $_GET['term'] : 'none'));

require_once '../config.php';

// Prevent direct access if not AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access not allowed.');
}

// Get search term and sanitize
$searchTerm = isset($_GET['term']) ? $_GET['term'] : '';
$searchTerm = trim(filter_var($searchTerm, FILTER_SANITIZE_STRING));

// Get user's campus from session
session_start();
if (!isset($_SESSION['username'])) {
    // Clean any output buffer
    ob_end_clean();
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userCampus = $_SESSION['username'];
error_log("User campus: $userCampus");

// Connect to database
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4"); // Set character encoding
    
    // Join personnel and academic_ranks tables to get all needed data
    $query = "SELECT p.id, p.name, p.gender, p.academic_rank, a.monthly_salary, a.hourly_rate 
              FROM personnel p
              LEFT JOIN academic_ranks a ON p.academic_rank = a.academic_rank
              WHERE p.campus = :campus 
              AND p.name LIKE :searchTerm
              ORDER BY p.name ASC
              LIMIT 10";
    
    error_log("Executing query for campus: $userCampus with search term: $searchTerm");
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':campus' => $userCampus,
        ':searchTerm' => "%$searchTerm%"
    ]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Results count for campus=$userCampus: " . count($results));
    
    // If no results found, return an empty array
    if (count($results) === 0) {
        error_log("No records found for campus=$userCampus with search term=$searchTerm");
    }
    
    // Clean any output buffer
    ob_end_clean();
    echo json_encode($results);
    
} catch (PDOException $e) {
    // Log the error but don't expose details to the client
    error_log("Database error in get_personnel.php: " . $e->getMessage());
    
    // Clean any output buffer
    ob_end_clean();
    echo json_encode(['error' => 'Database error occurred: ' . $e->getMessage()]);
}
?> 