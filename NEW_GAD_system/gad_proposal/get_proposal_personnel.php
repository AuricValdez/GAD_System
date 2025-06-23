<?php
session_start();
// Enable debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Set content type to application/json
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        throw new Exception("User not authenticated");
    }
    
    // Check if DB connection file exists
    if (!file_exists('../includes/db_connection.php')) {
        throw new Exception("Database connection file not found");
    }
    
    // Include database connection
    require_once '../includes/db_connection.php';
    
    // Get proposal ID from query string
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("No proposal ID provided");
    }
    
    $proposalId = intval($_GET['id']);
    
    // Get database connection
    $conn = getConnection();
    
    // Query to get personnel for the proposal
    $sql = "SELECT p.*, pl.name as personnel_name, pl.category, pl.status, pl.gender, pl.academic_rank
            FROM gad_proposal_personnel p
            LEFT JOIN personnel pl ON p.personnel_id = pl.id
            WHERE p.proposal_id = :proposal_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':proposal_id', $proposalId, PDO::PARAM_INT);
    $stmt->execute();
    
    $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'personnel' => $personnel
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("Error in get_proposal_personnel.php: " . $e->getMessage());
    
    // Return error message
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 