<?php
session_start();
error_log("get_campus_signatories.php - Script started");

// Log session information
error_log("get_campus_signatories.php - Session: " . json_encode($_SESSION));

// Direct authentication check instead of requiring a potentially missing file
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    error_log("get_campus_signatories.php - Authentication failed: No valid session");
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit;
}

error_log("get_campus_signatories.php - Auth check passed");

// Ensure we have campus parameter
if (!isset($_GET['campus']) || empty($_GET['campus'])) {
    error_log("get_campus_signatories.php - Missing campus parameter");
    echo json_encode([
        'status' => 'error',
        'message' => 'Campus parameter is required'
    ]);
    exit;
}

$campus = trim($_GET['campus']);
error_log("get_campus_signatories.php - Campus parameter: '$campus'");

// Check user type - multiple ways to determine if user is central
$userType = $_SESSION['user_type'] ?? '';
$username = $_SESSION['username'] ?? '';
error_log("get_campus_signatories.php - User type: '$userType', Username: '$username'");

// Consider user central if:
// 1. user_type is 'central' OR
// 2. username is 'central' OR  
// 3. username contains 'central' (case insensitive)
$isCentral = 
    (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'central') || 
    (isset($_SESSION['username']) && $_SESSION['username'] === 'central') ||
    (isset($_SESSION['username']) && stripos($_SESSION['username'], 'central') !== false);

error_log("get_campus_signatories.php - Is central (expanded check): " . ($isCentral ? 'Yes' : 'No'));

// Allow central user to access any campus, but restrict campus users to only their own campus
if (!$isCentral && $_SESSION['username'] !== $campus) {
    error_log("get_campus_signatories.php - Access denied for non-central user trying to access campus: $campus");
    echo json_encode([
        'status' => 'error',
        'message' => 'You do not have permission to access signatories for this campus'
    ]);
    exit;
}

error_log("get_campus_signatories.php - Access granted for user to campus: $campus");

// Connect to the database
try {
    error_log("get_campus_signatories.php - Connecting to database");
    $conn = new PDO(
        "mysql:host=localhost;dbname=gad_db;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    error_log("get_campus_signatories.php - Database connection successful");
} catch (PDOException $e) {
    error_log("get_campus_signatories.php - Database connection error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
    exit;
}

// First, debug check what campuses exist in the database
try {
    $checkCampuses = $conn->query("SELECT DISTINCT campus FROM signatories");
    $allCampuses = $checkCampuses->fetchAll(PDO::FETCH_COLUMN);
    error_log("get_campus_signatories.php - Available campuses in database: " . json_encode($allCampuses));
    
    // Check if our campus exists in the list - case insensitive
    $campusFound = false;
    $exactCampusName = null;
    
    foreach ($allCampuses as $dbCampus) {
        if (strcasecmp($campus, $dbCampus) === 0) {
            $campusFound = true;
            $exactCampusName = $dbCampus;
            break;
        }
    }
    
    error_log("get_campus_signatories.php - Case-insensitive match for campus '$campus'? " . ($campusFound ? "Yes, matched with '$exactCampusName'" : 'No'));
    
    // If we found a case-insensitive match, use the exact campus name from database
    if ($campusFound) {
        $campus = $exactCampusName;
        error_log("get_campus_signatories.php - Using exact campus name from database: '$campus'");
    }
} catch (Exception $e) {
    error_log("get_campus_signatories.php - Error checking campuses: " . $e->getMessage());
}

// Fetch signatories for the specified campus
try {
    error_log("get_campus_signatories.php - Preparing SQL query for campus: '$campus'");
    
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'signatories'");
    if ($tableCheck->rowCount() === 0) {
        error_log("get_campus_signatories.php - Signatories table does not exist");
        // Return default values since table doesn't exist
        echo json_encode([
            'status' => 'success',
            'data' => [
                'name1' => 'N/A (Table Missing)',
                'name2' => 'N/A (Table Missing)',
                'name3' => 'N/A (Table Missing)',
                'name4' => 'N/A (Table Missing)',
                'name5' => 'N/A (Table Missing)',
                'campus' => $campus
            ]
        ]);
        exit;
    }
    
    // Try a direct SQL query first to debug the issue
    $debugQuery = $conn->query("SELECT * FROM signatories WHERE campus = '$campus'");
    $debugResults = $debugQuery->fetchAll(PDO::FETCH_ASSOC);
    error_log("get_campus_signatories.php - Direct SQL query results: " . json_encode($debugResults));
    
    // If the exact match doesn't work, try a case-insensitive query
    if (empty($debugResults)) {
        error_log("get_campus_signatories.php - Exact match failed, trying case-insensitive match");
        $stmt = $conn->prepare("SELECT * FROM signatories WHERE LOWER(campus) = LOWER(:campus)");
        $stmt->bindParam(':campus', $campus);
        $stmt->execute();
        $signatories = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($signatories) {
            error_log("get_campus_signatories.php - Found match with case-insensitive query: " . json_encode($signatories));
        } else {
            error_log("get_campus_signatories.php - No results from case-insensitive query either");
        }
    } else {
        // Now use prepared statement for security
        $stmt = $conn->prepare("SELECT * FROM signatories WHERE campus = :campus");
        $stmt->bindParam(':campus', $campus);
        $stmt->execute();
        $signatories = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Log the exact SQL query executed
    $queryLog = $stmt->queryString;
    error_log("get_campus_signatories.php - Executed query: $queryLog with campus = '$campus'");
    
    // Log raw result from database
    if ($signatories) {
        error_log("get_campus_signatories.php - Raw signatories data from database: " . json_encode($signatories));
    } else {
        error_log("get_campus_signatories.php - No signatories found for campus: '$campus'");
    }
    
    // If no signatories found for the campus, return an empty object instead of error
    if (!$signatories) {
        // Return default values
        $defaultSignatories = [
            'name1' => 'N/A (Not Found)',
            'name2' => 'N/A (Not Found)',
            'name3' => 'N/A (Not Found)',
            'name4' => 'N/A (Not Found)',
            'name5' => 'N/A (Not Found)',
            'campus' => $campus
        ];
        
        error_log("get_campus_signatories.php - Returning default signatories: " . json_encode($defaultSignatories));
        echo json_encode([
            'status' => 'success',
            'data' => $defaultSignatories
        ]);
        exit;
    }
    
    // Ensure all required fields are present, even if missing in the database
    $completeSignatories = [
        'id' => $signatories['id'] ?? 0,
        'campus' => $signatories['campus'] ?? $campus,
        'name1' => $signatories['name1'] ?? 'N/A',
        'name2' => $signatories['name2'] ?? 'N/A',
        'name3' => $signatories['name3'] ?? 'N/A',
        'name4' => $signatories['name4'] ?? 'N/A',
        'name5' => $signatories['name5'] ?? 'N/A',
        'name6' => $signatories['name6'] ?? 'N/A',
        'gad_head_secretariat' => $signatories['gad_head_secretariat'] ?? 'N/A',
        'vice_chancellor_rde' => $signatories['vice_chancellor_rde'] ?? 'N/A',
        'chancellor' => $signatories['chancellor'] ?? 'N/A',
        'asst_director_gad' => $signatories['asst_director_gad'] ?? 'N/A',
        'head_extension_services' => $signatories['head_extension_services'] ?? 'N/A',
        'vice_chancellor_admin_finance' => $signatories['vice_chancellor_admin_finance'] ?? 'N/A'
    ];
    
    error_log("get_campus_signatories.php - Successfully found signatories: " . json_encode($completeSignatories));
    echo json_encode([
        'status' => 'success',
        'data' => $completeSignatories
    ]);
} catch (Exception $e) {
    error_log("get_campus_signatories.php - Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching signatories: ' . $e->getMessage()
    ]);
}
?> 