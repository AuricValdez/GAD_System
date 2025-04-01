<?php
// Enable error logging but don't display to users
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../error_log.txt');

// Clear any previous output
if (ob_get_level()) ob_end_clean();
header('Content-Type: application/json');

try {
    // Include database connection - using the correct path at root level
    if (!file_exists('../config.php')) {
        error_log("Database config file not found at ../config.php");
        throw new Exception("Database configuration file not found");
    }
    
    require_once '../config.php';
    
    if (!isset($conn) || $conn->connect_error) {
        error_log("Database connection failed: " . ($conn->connect_error ?? "Connection variable not set"));
        throw new Exception("Database connection failed");
    }
    
    // Get the PPA ID from the request
    $ppaId = isset($_GET['ppaId']) ? intval($_GET['ppaId']) : 0;
    
    if (!$ppaId) {
        throw new Exception('PPA ID is required');
    }

    error_log("Getting academic ranks for PPA ID: " . $ppaId);

    // First, get all academic ranks
    $rankQuery = "SELECT ar.id, ar.academic_rank as rank_name, ar.monthly_salary 
                 FROM academic_ranks ar 
                 ORDER BY ar.monthly_salary DESC";
    $rankResult = $conn->query($rankQuery);

    if (!$rankResult) {
        throw new Exception("Error executing rank query: " . $conn->error);
    }

    $allRanks = [];
    while ($row = $rankResult->fetch_assoc()) {
        $allRanks[] = $row;
    }
    
    // Then, get the count of personnel for each rank in this PPA
    $personnelQuery = "SELECT ar.id, COUNT(pp.id) as personnel_count
                      FROM academic_ranks ar
                      LEFT JOIN personnel p ON p.academic_rank = ar.academic_rank
                      LEFT JOIN ppas_personnel pp ON pp.personnel_id = p.id AND pp.ppas_form_id = ?
                      GROUP BY ar.id";
    
    $stmt = $conn->prepare($personnelQuery);
    if (!$stmt) {
        throw new Exception("Error preparing personnel query: " . $conn->error);
    }

    $stmt->bind_param("i", $ppaId);
    if (!$stmt->execute()) {
        throw new Exception("Error executing personnel query: " . $stmt->error);
    }

    $personnelResult = $stmt->get_result();
    $personnelCounts = [];
    
    while ($row = $personnelResult->fetch_assoc()) {
        $personnelCounts[$row['id']] = $row['personnel_count'];
    }
    
    // Combine the data
    $academicRanks = array_map(function($rank) use ($personnelCounts) {
        return [
            'id' => $rank['id'],
            'rank_name' => $rank['rank_name'],
            'monthly_salary' => floatval($rank['monthly_salary']),
            'personnel_count' => isset($personnelCounts[$rank['id']]) ? intval($personnelCounts[$rank['id']]) : 0
        ];
    }, $allRanks);
    
    error_log("Found " . count($academicRanks) . " academic ranks for PPA ID: " . $ppaId);
    
    echo json_encode([
        'success' => true,
        'academicRanks' => $academicRanks
    ]);
    
} catch (Exception $e) {
    error_log("ERROR in get_academic_ranks_simple.php: " . $e->getMessage());
    http_response_code(200); // Setting to 200 to allow client to read the error message
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 