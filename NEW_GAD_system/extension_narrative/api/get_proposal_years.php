<?php
session_start();
header('Content-Type: application/json');

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
    
    // Query to fetch unique years from narrative table directly
    $sql = "SELECT DISTINCT YEAR(created_at) as year FROM narrative";
    
    $params = [];
    
    // Add campus filter if provided
    if ($campus) {
        $sql .= " WHERE campus = :campus";
        $params[':campus'] = $campus;
    }
    
    $sql .= " ORDER BY year DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $years = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If there's no created_at field in narrative, try using a different approach
    if (empty($years) || $years[0]['year'] === null) {
        // Alternative approach: just get all distinct years available
        $sql = "SELECT DISTINCT EXTRACT(YEAR FROM NOW()) as year";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $years = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // For older years, manually add them
        $currentYear = (int)date('Y');
        $additionalYears = [];
        for ($i = 1; $i <= 5; $i++) {
            $additionalYears[] = ['year' => $currentYear - $i];
        }
        $years = array_merge($years, $additionalYears);
    }
    
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