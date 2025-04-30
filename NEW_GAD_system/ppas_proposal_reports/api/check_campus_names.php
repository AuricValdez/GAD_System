<?php
session_start();
error_log("check_campus_names.php - Script started");

// Direct authentication check instead of requiring a potentially missing file
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    error_log("check_campus_names.php - Authentication failed: No valid session");
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit;
}

// Check user type - multiple ways to determine if user is central
$userType = $_SESSION['user_type'] ?? '';
$username = $_SESSION['username'] ?? '';
error_log("check_campus_names.php - User type: '$userType', Username: '$username'");

// Consider user central if:
// 1. user_type is 'central' OR
// 2. username is 'central' OR  
// 3. username contains 'central' (case insensitive)
$isCentral = 
    (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'central') || 
    (isset($_SESSION['username']) && $_SESSION['username'] === 'central') ||
    (isset($_SESSION['username']) && stripos($_SESSION['username'], 'central') !== false);

error_log("check_campus_names.php - Is central (expanded check): " . ($isCentral ? 'Yes' : 'No'));

// Validate user has access
if (!$isCentral) {
    error_log("check_campus_names.php - Access denied for non-central user");
    echo json_encode([
        'status' => 'error',
        'message' => 'You do not have permission to access this data'
    ]);
    exit;
}

error_log("check_campus_names.php - Access granted for central user");

// Connect to the database
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
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
    exit;
}

// Fetch all campus names from signatories table
try {
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'signatories'");
    if ($tableCheck->rowCount() === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Signatories table does not exist'
        ]);
        exit;
    }
    
    // Get all campus names
    $stmt = $conn->query("SELECT DISTINCT campus FROM signatories");
    $campuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Also get all signatories data for debugging
    $allSignatories = $conn->query("SELECT id, campus, name1, name2, name3, name4, name5 FROM signatories")->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'data' => $campuses,
        'all_signatories' => $allSignatories
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching campus names: ' . $e->getMessage()
    ]);
}
?> 