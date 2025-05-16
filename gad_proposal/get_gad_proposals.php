<?php
// Enable debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to application/json
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        throw new Exception("User not logged in");
    }

    // Check if db_connection.php exists
    if (!file_exists('../includes/db_connection.php')) {
        throw new Exception("Database connection file not found");
    }

    // Include database connection
    require_once '../includes/db_connection.php';

    // Get the current user's role and campus
    $username = $_SESSION['username'];
    $userRole = $_SESSION['role'] ?? null;
    $userCampus = $_SESSION['campus_id'] ?? null;

    // Use the getConnection function from db_connection.php
    $conn = getConnection();

    if (!$conn) {
        throw new Exception("Failed to establish database connection");
    }

    // Query to get GAD proposals - Rename columns to match what frontend expects
    $sql = "SELECT p.id, p.activity_title, p.project, p.program, 
                  p.year, p.quarter, p.created_at as date_created,
                  p.start_date, p.end_date
           FROM gad_proposals p
           ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $proposals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Always return an array, even if empty
    if ($proposals === false) {
        $proposals = [];
    }

    // Return JSON response with success:true
    echo json_encode([
        'success' => true,
        'proposals' => $proposals
    ]);

    // Close connection by setting to null
    $conn = null;
} catch (PDOException $e) {
    // Log the database error for debugging
    error_log("Database error in get_gad_proposals.php: " . $e->getMessage());
    
    // Return error message as JSON
    echo json_encode([
        'success' => false,
        'message' => "Database error occurred. Please try again later.",
        'debug' => $e->getMessage()
    ]);
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Error in get_gad_proposals.php: " . $e->getMessage());
    
    // Return error message as JSON
    echo json_encode([
        'success' => false,
        'message' => "Error: " . $e->getMessage()
    ]);
}
?> 