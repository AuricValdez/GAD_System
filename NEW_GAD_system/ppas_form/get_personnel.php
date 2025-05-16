<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable output to browser
ini_set('log_errors', 1);

// Log the start of the script
error_log("get_personnel.php - Starting to fetch personnel data");

// Get campus parameter for filtering
$campus = isset($_GET['campus']) ? $_GET['campus'] : null;
error_log("get_personnel.php - Campus filter: " . ($campus ? $campus : "None"));

// Define sample data for testing (this will be returned if real data not available)
$sampleData = [
    [
        'id' => 'sample_1',
        'name' => 'John Doe (Sample)',
        'gender' => 'Male',
        'academic_rank' => 'Professor',
        'monthly_salary' => '50000',
        'hourly_rate' => '350'
    ],
    [
        'id' => 'sample_2',
        'name' => 'Jane Smith (Sample)',
        'gender' => 'Female', 
        'academic_rank' => 'Associate Professor',
        'monthly_salary' => '45000',
        'hourly_rate' => '325'
    ]
];

// Set a flag to track if we need to use sample data
$useSampleData = false;
$errorReason = "";

try {
    // Check if config file exists
    if (!file_exists('../config.php')) {
        error_log("get_personnel.php - Error: config.php file not found");
        $useSampleData = true;
        $errorReason = "Config file not found";
    } else {
        // Include database configuration
        require_once '../config.php';
        error_log("get_personnel.php - Config file included");
        
        // Check if we have a database connection
        if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
            error_log("get_personnel.php - Database connection issue");
            $useSampleData = true;
            $errorReason = "Database connection issue";
        } else {
            error_log("get_personnel.php - Database connection verified");
            
            // Check if academic_ranks table exists
            $academicRanksCheck = $conn->query("SHOW TABLES LIKE 'academic_ranks'");
            $hasAcademicRanks = $academicRanksCheck && $academicRanksCheck->num_rows > 0;
            
            if ($hasAcademicRanks) {
                error_log("get_personnel.php - academic_ranks table exists");
            } else {
                error_log("get_personnel.php - academic_ranks table doesn't exist, will use personnel data directly");
            }
            
            // First attempt: Use a query that joins personnel with academic_ranks (if available)
            if ($hasAcademicRanks) {
                $sql = "SELECT p.id, p.name, p.gender, p.academic_rank, p.campus, ";
                $sql .= "ar.monthly_salary, ar.hourly_rate ";
                $sql .= "FROM personnel p ";
                $sql .= "LEFT JOIN academic_ranks ar ON p.academic_rank = ar.academic_rank ";
                
                // Add campus filter if provided
                if ($campus) {
                    $sql .= "WHERE p.campus = '" . $conn->real_escape_string($campus) . "' ";
                }
                
                $sql .= "ORDER BY p.name ASC";
            } else {
                // Fallback query without join if academic_ranks doesn't exist
                $sql = "SELECT * FROM personnel ";
                
                // Add campus filter if provided
                if ($campus) {
                    $sql .= "WHERE campus = '" . $conn->real_escape_string($campus) . "' ";
                }
                
                $sql .= "ORDER BY name ASC";
            }
            
            error_log("get_personnel.php - Attempting query: {$sql}");
            
            $result = $conn->query($sql);
            
            if ($result === false) {
                error_log("get_personnel.php - Query failed: " . $conn->error);
                
                // Check if personnel table exists
                $tableCheckQuery = "SHOW TABLES LIKE 'personnel'";
                $tableCheckResult = $conn->query($tableCheckQuery);
                
                if ($tableCheckResult === false || $tableCheckResult->num_rows === 0) {
                    error_log("get_personnel.php - Personnel table doesn't exist");
                    $useSampleData = true;
                    $errorReason = "Personnel table doesn't exist";
                    
                    // List all tables for debugging
                    $tablesQuery = "SHOW TABLES";
                    $tablesResult = $conn->query($tablesQuery);
                    if ($tablesResult) {
                        $tables = [];
                        while ($row = $tablesResult->fetch_array()) {
                            $tables[] = $row[0];
                        }
                        error_log("get_personnel.php - Available tables: " . implode(", ", $tables));
                    }
                } else {
                    // Try a simpler query without joins
                    $simpleQuery = "SELECT * FROM personnel";
                    if ($campus) {
                        $simpleQuery .= " WHERE campus = '" . $conn->real_escape_string($campus) . "'";
                    }
                    $simpleQuery .= " LIMIT 10";
                    
                    error_log("get_personnel.php - Trying simpler query: {$simpleQuery}");
                    $simpleResult = $conn->query($simpleQuery);
                    
                    if ($simpleResult === false) {
                        error_log("get_personnel.php - Simple query failed: " . $conn->error);
                        $useSampleData = true;
                        $errorReason = "Query error: " . $conn->error;
                    } else {
                        $data = [];
                        while ($row = $simpleResult->fetch_assoc()) {
                            $data[] = $row;
                        }
                        
                        if (count($data) > 0) {
                            // If we have personnel data but no academic_ranks, try to get ranks data separately
                            if ($hasAcademicRanks) {
                                error_log("get_personnel.php - Enhancing personnel data with academic_ranks");
                                
                                // Get all academic ranks data
                                $ranksQuery = "SELECT * FROM academic_ranks";
                                $ranksResult = $conn->query($ranksQuery);
                                
                                if ($ranksResult !== false) {
                                    $ranksData = [];
                                    while ($rankRow = $ranksResult->fetch_assoc()) {
                                        $ranksData[$rankRow['academic_rank']] = $rankRow;
                                    }
                                    
                                    // Enhance personnel data
                                    foreach ($data as &$person) {
                                        if (isset($person['academic_rank']) && isset($ranksData[$person['academic_rank']])) {
                                            $person['monthly_salary'] = $ranksData[$person['academic_rank']]['monthly_salary'];
                                            $person['hourly_rate'] = $ranksData[$person['academic_rank']]['hourly_rate'];
                                        }
                                    }
                                }
                            }
                            
                            error_log("get_personnel.php - Found " . count($data) . " personnel using simpler query");
                            header('Content-Type: application/json');
                            echo json_encode($data);
                            exit;
                        } else {
                            error_log("get_personnel.php - Personnel table exists but is empty or no results for campus filter");
                            $useSampleData = true;
                            $errorReason = "No personnel found" . ($campus ? " for campus: $campus" : "");
                        }
                    }
                }
            } else {
                // Query succeeded - process the data
                $data = [];
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
                
                error_log("get_personnel.php - Successfully retrieved " . count($data) . " personnel records");
                
                if (count($data) > 0) {
                    header('Content-Type: application/json');
                    echo json_encode($data);
                    exit;
                } else {
                    error_log("get_personnel.php - No personnel found" . ($campus ? " for campus: $campus" : ""));
                    $useSampleData = true;
                    $errorReason = "No personnel found" . ($campus ? " for campus: $campus" : "");
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("get_personnel.php - Exception: " . $e->getMessage());
    $useSampleData = true;
    $errorReason = "Exception: " . $e->getMessage();
}

// If we reached here, we need to use sample data
if ($useSampleData) {
    error_log("get_personnel.php - Using sample data. Reason: " . $errorReason);
    header('Content-Type: application/json');
    echo json_encode($sampleData);
}

// Close the connection if it exists
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
    error_log("get_personnel.php - Database connection closed");
}
?> 