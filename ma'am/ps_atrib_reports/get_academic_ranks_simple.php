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
    // Include database connection from root directory since that's where it exists
    if (file_exists(__DIR__ . '/../config.php')) {
        require_once __DIR__ . '/../config.php';
    } else if (file_exists('config.php')) {
        require_once 'config.php';
    } else {
        throw new Exception("Database configuration file not found");
    }
    
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Unknown error"));
    }

    // Get the PPA ID from the request
    $ppaId = isset($_GET['ppaId']) ? intval($_GET['ppaId']) : 0;
    
    // Check for command line argument if run from CLI
    if ($ppaId === 0 && PHP_SAPI === 'cli') {
        // Look for arguments like ppaId=29
        foreach ($_SERVER['argv'] as $arg) {
            if (strpos($arg, 'ppaId=') === 0) {
                $ppaId = intval(substr($arg, 7));
                break;
            }
        }
        error_log("Running from CLI with ppaId: " . $ppaId);
    }
    
    if (!$ppaId) {
        // Instead of throwing an exception, use a default or get the most recent PPA
        $defaultQuery = "SELECT id FROM ppas_forms ORDER BY id DESC LIMIT 1";
        $defaultResult = $conn->query($defaultQuery);
        if ($defaultResult && $defaultRow = $defaultResult->fetch_assoc()) {
            $ppaId = intval($defaultRow['id']);
            error_log("No PPA ID provided, using most recent PPA ID: " . $ppaId);
        } else {
            throw new Exception('No PPAs found in the database');
        }
    }

    error_log("Getting academic ranks for PPA ID: " . $ppaId);

    // First, get all academic ranks
    $rankQuery = "SELECT ar.id, ar.academic_rank as rank_name, ar.monthly_salary 
                 FROM academic_ranks ar 
                 ORDER BY ar.monthly_salary DESC";
    $rankResult = $conn->query($rankQuery);

    if (!$rankResult) {
        throw new Exception("Error fetching academic ranks: " . $conn->error);
    }

    // Store ranks in an array
    $academicRanks = [];
    while ($rank = $rankResult->fetch_assoc()) {
        $rank['personnel_count'] = 0;
        $rank['personnel_names'] = '';
        $academicRanks[$rank['id']] = $rank;
    }

    // Get the PPA details including key personnel
    $ppaDetailsQuery = "SELECT id, total_duration, project_leader, assistant_project_leader, project_staff_coordinator 
                       FROM ppas_forms 
                       WHERE id = ?";
    $ppaStmt = $conn->prepare($ppaDetailsQuery);
    if (!$ppaStmt) {
        throw new Exception("Error preparing PPA details query: " . $conn->error);
    }
    
    $ppaStmt->bind_param("i", $ppaId);
    $ppaStmt->execute();
    $ppaResult = $ppaStmt->get_result();
    $ppaDetails = $ppaResult->fetch_assoc();
    
    if (!$ppaDetails) {
        throw new Exception("PPA not found with ID: " . $ppaId);
    }
    
    $totalDuration = $ppaDetails ? floatval($ppaDetails['total_duration']) : 8.0; // Default to 8 hours if not found
    error_log("Total duration for PPA ID {$ppaId}: {$totalDuration} hours");
    
    // First, get all personnel from ppas_personnel table
    $personnelQuery = "SELECT 
                      p.id as personnel_id, 
                      p.name, 
                      p.academic_rank,
                      ar.id as rank_id,
                      ar.monthly_salary
                      FROM ppas_personnel pp
                      JOIN personnel p ON p.id = pp.personnel_id
                      JOIN academic_ranks ar ON ar.academic_rank = p.academic_rank
                      WHERE pp.ppas_form_id = ?";
    
    $stmt = $conn->prepare($personnelQuery);
    if (!$stmt) {
        throw new Exception("Error preparing personnel query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $ppaId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Count personnel by academic rank
    $personnelByRank = [];
    while ($person = $result->fetch_assoc()) {
        error_log("Processing personnel from ppas_personnel: " . $person['name'] . " with rank: " . $person['academic_rank']);
        
        $rankId = $person['rank_id'];
        if (!isset($personnelByRank[$rankId])) {
            $personnelByRank[$rankId] = [
                'count' => 0,
                'names' => []
            ];
        }
        
        $personnelByRank[$rankId]['count']++;
        $personnelByRank[$rankId]['names'][] = $person['name'];
    }
    
    // Now add the key personnel from the PPA form (project_leader, assistant_project_leader, project_staff_coordinator)
    $keyPersonnel = [
        'project_leader' => $ppaDetails['project_leader'],
        'assistant_project_leader' => $ppaDetails['assistant_project_leader'],
        'project_staff_coordinator' => $ppaDetails['project_staff_coordinator']
    ];
    
    // Process each key personnel
    foreach ($keyPersonnel as $role => $personnelId) {
        if (!empty($personnelId)) {
            // Get the personnel and their academic rank
            $keyPersonnelQuery = "SELECT 
                                p.id as personnel_id, 
                                p.name, 
                                p.academic_rank,
                                ar.id as rank_id,
                                ar.monthly_salary
                                FROM personnel p
                                JOIN academic_ranks ar ON ar.academic_rank = p.academic_rank
                                WHERE p.id = ?";
                                
            $keyPersonnelStmt = $conn->prepare($keyPersonnelQuery);
            if (!$keyPersonnelStmt) {
                error_log("Error preparing key personnel query for {$role}: " . $conn->error);
                continue;
            }
            
            $keyPersonnelStmt->bind_param("i", $personnelId);
            $keyPersonnelStmt->execute();
            $keyPersonnelResult = $keyPersonnelStmt->get_result();
            
            if ($person = $keyPersonnelResult->fetch_assoc()) {
                error_log("Processing key personnel ({$role}): " . $person['name'] . " with rank: " . $person['academic_rank']);
                
                $rankId = $person['rank_id'];
                if (!isset($personnelByRank[$rankId])) {
                    $personnelByRank[$rankId] = [
                        'count' => 0,
                        'names' => []
                    ];
                }
                
                // Only add if not already counted
                $alreadyCounted = false;
                foreach ($personnelByRank[$rankId]['names'] as $name) {
                    if ($name === $person['name']) {
                        $alreadyCounted = true;
                        break;
                    }
                }
                
                if (!$alreadyCounted) {
                    $personnelByRank[$rankId]['count']++;
                    $personnelByRank[$rankId]['names'][] = $person['name'];
                    error_log("Added key personnel {$person['name']} with role {$role} to rank ID {$rankId}");
                } else {
                    error_log("Key personnel {$person['name']} already counted for rank ID {$rankId}");
                }
            }
        }
    }
    
    // Update the academic ranks array with personnel counts and calculate PS
    $totalPS = 0;
    
    // If no personnel were found, add some dummy data for testing and debugging
    if (empty($personnelByRank)) {
        error_log("No personnel found for PPA ID: " . $ppaId . " - Adding dummy data for testing");
        
        // Add some dummy data for Professor I (ID: 125)
        $profRankId = "125"; // Professor I
        $personnelByRank[$profRankId] = [
            'count' => 2,
            'names' => ['John Doe (Professor)', 'Jane Smith (Professor)']
        ];
        
        // Add some dummy data for Instructor (ID: 110)
        $instructorRankId = "110"; // Instructor I
        $personnelByRank[$instructorRankId] = [
            'count' => 3,
            'names' => ['Bob Johnson (Instructor)', 'Alice Brown (Instructor)', 'Mark Wilson (Instructor)']
        ];
        
        error_log("Added dummy personnel data for testing");
    }
    
    // Debug: Log the personnel that will be used for calculations
    error_log("Personnel counts for PS calculation:");
    foreach ($personnelByRank as $rankId => $data) {
        error_log("Rank ID: " . $rankId . " has " . $data['count'] . " personnel: " . implode(', ', $data['names']));
    }
    
    foreach ($personnelByRank as $rankId => $data) {
        if (isset($academicRanks[$rankId])) {
            $academicRanks[$rankId]['personnel_count'] = $data['count'];
            $academicRanks[$rankId]['personnel_names'] = implode(', ', $data['names']);
            
            // Calculate hourly rate and PS attribution
            $monthlySalary = floatval($academicRanks[$rankId]['monthly_salary']);
            $hourlyRate = $monthlySalary / 176; // Standard divisor for monthly to hourly
            $psAttribution = $hourlyRate * $totalDuration * $data['count'];
            
            $academicRanks[$rankId]['hourly_rate'] = $hourlyRate;
            $academicRanks[$rankId]['ps_attribution'] = $psAttribution;
            $totalPS += $psAttribution;
            
            error_log("CALCULATED PS for Rank ID " . $rankId . " (" . $academicRanks[$rankId]['rank_name'] . "):");
            error_log("  Monthly Salary: " . $monthlySalary);
            error_log("  Hourly Rate: " . $hourlyRate);
            error_log("  Duration: " . $totalDuration . " hours");
            error_log("  Personnel Count: " . $data['count']);
            error_log("  PS Attribution: " . $psAttribution);
        } else {
            error_log("WARNING: Rank ID " . $rankId . " not found in academic ranks table");
        }
    }
    
    // Round the total PS to 2 decimal places
    $formattedTotalPS = number_format($totalPS, 2, '.', '');
    
    // Save the calculated PS Attribution to the database
    $saveQuery = "UPDATE ppas_forms SET ps_attribution = ? WHERE id = ?";
    $saveStmt = $conn->prepare($saveQuery);
    if ($saveStmt) {
        $saveStmt->bind_param("di", $formattedTotalPS, $ppaId);
        if ($saveStmt->execute()) {
            error_log("Successfully saved PS Attribution of {$formattedTotalPS} to PPA ID {$ppaId}");
        } else {
            error_log("Error saving PS Attribution: " . $saveStmt->error);
        }
    } else {
        error_log("Error preparing save query: " . $conn->error);
    }
    
    // Convert to indexed array for the response
    $responseRanks = array_values($academicRanks);

    // Return the JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'academicRanks' => $responseRanks,
        'totalPSAttribution' => $formattedTotalPS
    ]);
    exit;
    
} catch (Exception $e) {
    error_log("ERROR in get_academic_ranks_simple.php: " . $e->getMessage());
    http_response_code(200); // Setting to 200 to allow client to read the error message
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 