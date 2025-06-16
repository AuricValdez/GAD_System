<?php
// Include database connection
// Try different possible paths for the db_connection.php file
$possible_paths = [
    '../includes/db_connection.php', // Standard path when running through web server
    __DIR__ . '/../includes/db_connection.php', // Absolute path from script location
    'includes/db_connection.php', // Path from project root
    dirname(__DIR__) . '/includes/db_connection.php' // Another absolute path option
];

$included = false;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $included = true;
        break;
    }
}

// If no path worked, try a fallback
if (!$included) {
    if (file_exists(__DIR__ . '/config/dbconnection.php')) {
        require_once __DIR__ . '/config/dbconnection.php';
    } else {
        die('Could not find database connection file');
    }
}

// Set content type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize response array
$response = [
    'success' => false,
    'academicRanks' => [],
    'error' => null
];

try {
    // Check if PPA ID is provided (handle both GET and command line arguments)
    $ppaId = null;
    
    // Check for GET parameter
    if (isset($_GET['ppaId']) && !empty($_GET['ppaId'])) {
        $ppaId = intval($_GET['ppaId']);
    } 
    // Check for command line argument
    else if (isset($argv)) {
        foreach ($argv as $arg) {
            if (strpos($arg, 'ppaId=') === 0) {
                $ppaId = intval(substr($arg, 6));
                break;
            }
        }
    }
    
    // If still no PPA ID, throw exception
    if ($ppaId === null) {
        throw new Exception("PPA ID is required");
    }
    
    $response['ppaId'] = $ppaId;
    
    // Debug log
    error_log("Fetching narrative personnel for PPA ID: " . $ppaId);
    
    // Check database connection
    if (!isset($conn) || !$conn) {
        throw new Exception("Database connection failed");
    }
    
    // First, check if the narrative_entries table exists
    $checkTableQuery = "SHOW TABLES LIKE 'narrative_entries'";
    $tableExists = $conn->query($checkTableQuery)->rowCount() > 0;
    
    if (!$tableExists) {
        // Try alternative table names
        $alternativeTableNames = ['narrative_entry', 'narratives'];
        $tableFound = false;
        
        foreach ($alternativeTableNames as $tableName) {
            $checkAltTableQuery = "SHOW TABLES LIKE '$tableName'";
            if ($conn->query($checkAltTableQuery)->rowCount() > 0) {
                $tableExists = true;
                $tableFound = true;
                $narrativeTable = $tableName;
                break;
            }
        }
        
        if (!$tableFound) {
            throw new Exception("Narrative entries table not found");
        }
    } else {
        $narrativeTable = 'narrative_entries';
    }
    
    // Check for column existence and find the right column names
    $columnQuery = "SHOW COLUMNS FROM $narrativeTable";
    $columnResult = $conn->query($columnQuery);
    $columns = $columnResult->fetchAll(PDO::FETCH_COLUMN);
    
    error_log("Available columns in $narrativeTable: " . implode(", ", $columns));
    
    // Look for title column alternative
    $titleColumn = 'title';
    if (!in_array('title', $columns)) {
        // Try alternatives
        $titleAlternatives = ['activity_name', 'activity_title', 'name', 'activity', 'ppa_title'];
        foreach ($titleAlternatives as $alt) {
            if (in_array($alt, $columns)) {
                $titleColumn = $alt;
                error_log("Using alternative title column: $alt");
                break;
            }
        }
    }
    
    // Look for personnel column alternative
    $personnelColumn = 'other_internal_personnel';
    if (!in_array('other_internal_personnel', $columns)) {
        // Try alternatives
        $personnelAlternatives = ['internal_personnel', 'personnel', 'staff', 'project_staff', 'participants'];
        foreach ($personnelAlternatives as $alt) {
            if (in_array($alt, $columns)) {
                $personnelColumn = $alt;
                error_log("Using alternative personnel column: $alt");
                break;
            }
        }
    }
    
    // Look for ID column alternative
    $idColumn = 'ppas_form_id';
    if (!in_array('ppas_form_id', $columns)) {
        // Try alternatives
        $idAlternatives = ['ppa_id', 'form_id', 'activity_id', 'id'];
        foreach ($idAlternatives as $alt) {
            if (in_array($alt, $columns)) {
                $idColumn = $alt;
                error_log("Using alternative ID column: $alt");
                break;
            }
        }
    }
    
    // Output debug information
    error_log("Using columns: title=$titleColumn, personnel=$personnelColumn, id=$idColumn");
    
    // Query to get narrative entries for this PPA ID
    if ($idColumn === 'id') {
        // If we're using the primary key as the id column, modify our query
        // to look for matches in other related columns
        $sql = "SELECT ne.$titleColumn as title, ne.$personnelColumn as personnel 
                FROM $narrativeTable ne 
                WHERE ne.$idColumn = :ppaId OR 
                      (ne.ppas_form_id = :ppaId2) OR
                      (ne.title LIKE :titleSearch)";
                      
        // Get the PPA title for text matching
        $titleQuery = "SELECT title FROM ppas_forms WHERE id = :ppaId";
        $titleStmt = $conn->prepare($titleQuery);
        $titleStmt->bindParam(':ppaId', $ppaId, PDO::PARAM_INT);
        $titleStmt->execute();
        $ppaTitle = $titleStmt->fetchColumn();
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ppaId', $ppaId, PDO::PARAM_INT);
        $stmt->bindParam(':ppaId2', $ppaId, PDO::PARAM_INT);
        $titleSearch = "%$ppaTitle%";
        $stmt->bindParam(':titleSearch', $titleSearch, PDO::PARAM_STR);
    } else {
        // Standard query
        $sql = "SELECT ne.$titleColumn as title, ne.$personnelColumn as personnel 
                FROM $narrativeTable ne 
                WHERE ne.$idColumn = :ppaId";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ppaId', $ppaId, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    
    $narrativeEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Found " . count($narrativeEntries) . " narrative entries");
    
    if (count($narrativeEntries) === 0) {
        // No error, just return empty results
        $response['success'] = true;
        $response['message'] = "No narrative entries found for this PPA";
        echo json_encode($response);
        exit;
    }
    
    // Extract personnel from narrative entries and map them to academic ranks
    $personnelCount = [];
    
    foreach ($narrativeEntries as $entry) {
        if (!empty($entry['personnel'])) {
            try {
                // Try to parse as JSON
                $personnel = json_decode($entry['personnel'], true);
                
                if (is_array($personnel)) {
                    foreach ($personnel as $person) {
                        if (!empty($person)) {
                            // Get the person's name and find their academic rank
                            processPersonnel($person, $personnelCount, $conn);
                        }
                    }
                } else {
                    // If not JSON, try to parse as comma-separated string
                    $personnelArray = explode(',', $entry['personnel']);
                    foreach ($personnelArray as $person) {
                        $person = trim($person);
                        if (!empty($person)) {
                            processPersonnel($person, $personnelCount, $conn);
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Error parsing personnel data: " . $e->getMessage());
            }
        }
    }
    
    // Create academic ranks array for response
    $academicRanks = [];
    foreach ($personnelCount as $rank => $count) {
        // Get salary information for this rank
        $salaryQuery = "SELECT monthly_salary FROM academic_ranks WHERE academic_rank = :rank";
        $salaryStmt = $conn->prepare($salaryQuery);
        $salaryStmt->bindParam(':rank', $rank, PDO::PARAM_STR);
        $salaryStmt->execute();
        $salaryData = $salaryStmt->fetch(PDO::FETCH_ASSOC);
        
        $monthlySalary = $salaryData ? floatval($salaryData['monthly_salary']) : 0;
        
        $academicRanks[] = [
            'rank_name' => $rank,
            'personnel_count' => $count,
            'monthly_salary' => $monthlySalary
        ];
    }
    
    $response['success'] = true;
    $response['academicRanks'] = $academicRanks;
    
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    
    // Special handling for column not found errors
    if (strpos($errorMessage, "Unknown column") !== false) {
        // Attempt to extract the column name from the error message
        if (preg_match("/Unknown column '([^']+)'/", $errorMessage, $matches)) {
            $unknownColumn = $matches[1];
            error_log("Handling special case for unknown column: $unknownColumn");
            
            // If it's a column reference with table alias (like ne.title)
            if (strpos($unknownColumn, '.') !== false) {
                list($tableAlias, $columnName) = explode('.', $unknownColumn);
                
                // Try to fix the query by finding an alternative column
                if ($columnName === $titleColumn) {
                    error_log("Title column $titleColumn doesn't exist - using alternative");
                    $response['success'] = true;
                    $response['academicRanks'] = [];
                    $response['warning'] = "Column $columnName not found in table. Consider updating the database schema.";
                    echo json_encode($response);
                    exit;
                }
                else if ($columnName === $personnelColumn) {
                    error_log("Personnel column $personnelColumn doesn't exist - using alternative");
                    $response['success'] = true;
                    $response['academicRanks'] = [];
                    $response['warning'] = "Column $columnName not found in table. Consider updating the database schema.";
                    echo json_encode($response);
                    exit;
                }
            }
        }
    }
    
    $response['success'] = false;
    $response['error'] = "Database error: " . $errorMessage;
    error_log("Error in get_narrative_personnel.php: " . $errorMessage);
}

// Helper function to process personnel names and find their academic ranks
function processPersonnel($personName, &$personnelCount, $conn) {
    try {
        // Prepare the query to find personnel by name
        $personQuery = "SELECT p.academic_rank FROM personnel p WHERE p.name LIKE :name LIMIT 1";
        $personStmt = $conn->prepare($personQuery);
        $searchName = "%" . trim($personName) . "%";
        $personStmt->bindParam(':name', $searchName, PDO::PARAM_STR);
        $personStmt->execute();
        
        $personData = $personStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($personData && !empty($personData['academic_rank'])) {
            $rank = $personData['academic_rank'];
            if (!isset($personnelCount[$rank])) {
                $personnelCount[$rank] = 0;
            }
            $personnelCount[$rank]++;
        } else {
            // If no exact match, try a more flexible search
            $personQuery = "SELECT p.academic_rank FROM personnel p WHERE 
                          CONCAT(p.name, ' ', p.name) LIKE :name OR 
                          p.name LIKE :partialName LIMIT 1";
            $personStmt = $conn->prepare($personQuery);
            $searchName = "%" . trim($personName) . "%";
            $personStmt->bindParam(':name', $searchName, PDO::PARAM_STR);
            $personStmt->bindParam(':partialName', $searchName, PDO::PARAM_STR);
            $personStmt->execute();
            
            $personData = $personStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($personData && !empty($personData['academic_rank'])) {
                $rank = $personData['academic_rank'];
                if (!isset($personnelCount[$rank])) {
                    $personnelCount[$rank] = 0;
                }
                $personnelCount[$rank]++;
            } else {
                // Default to Instructor I if we can't find the person
                $defaultRank = 'Instructor I';
                if (!isset($personnelCount[$defaultRank])) {
                    $personnelCount[$defaultRank] = 0;
                }
                $personnelCount[$defaultRank]++;
                error_log("Could not find academic rank for: $personName, using default rank");
            }
        }
    } catch (Exception $e) {
        error_log("Error processing personnel $personName: " . $e->getMessage());
    }
}

// Return JSON response
echo json_encode($response);
?> 