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

// Function to find PPAS table in the database
function findPpasTable($conn) {
    try {
        // Get all tables in the database
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Look for tables with 'ppas' in the name
        foreach ($tables as $table) {
            if (stripos($table, 'ppas') !== false) {
                // Check if this table has id column
                $stmt = $conn->query("DESCRIBE `$table`");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                if (in_array('id', $columns)) {
                    return $table;
                }
            }
        }
        
        return null;
    } catch (Exception $e) {
        error_log("Error finding PPAS table: " . $e->getMessage());
        return null;
    }
}

try {
    // Get parameters from request
    $ppasFormId = isset($_GET['ppas_form_id']) ? $_GET['ppas_form_id'] : null;
    $campus = isset($_GET['campus']) ? $_GET['campus'] : null;

    // Validate required parameters
    if (!$ppasFormId) {
        echo json_encode([
            'status' => 'error',
            'message' => 'PPAS Form ID is required',
            'code' => 'MISSING_PARAM'
        ]);
        exit();
    }

    // Get database connection
    $conn = isset($pdo) ? $pdo : getConnection();
    
    // Find the PPAS table in the database
    $ppasTable = findPpasTable($conn);
    error_log("Found PPAS table: " . ($ppasTable ? $ppasTable : "No PPAS table found"));
    
    // First, try to get the narrative entry directly by ID
    // This is the new approach where we're treating the narrative_entries ID as the primary key
    $sql = "SELECT * FROM narrative_entries WHERE id = :id AND campus = :campus";
    $params = [':id' => $ppasFormId, ':campus' => $campus];
    
    error_log("Fetching narrative_entries with ID: $ppasFormId, Campus: $campus");
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If no data found, check if this is a PPAS form ID that we need to match with a narrative entry
    if (!$narrativeData && $ppasTable) {
        error_log("No direct match found by ID. Checking if this is a PPAS form ID...");
        
        // Get PPAS form details to find matching narrative entry
        $stmt = $conn->prepare("SELECT activity, year FROM `$ppasTable` WHERE id = :id");
        $stmt->execute([':id' => $ppasFormId]);
        $ppasData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ppasData) {
            error_log("Found PPAS form with activity: {$ppasData['activity']}, year: {$ppasData['year']}");
            
            // Try to find matching narrative entry by title and year
            $sql = "SELECT * FROM narrative_entries WHERE title = :title AND year = :year AND campus = :campus LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':title' => $ppasData['activity'],
                ':year' => $ppasData['year'],
                ':campus' => $campus
            ]);
            $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($narrativeData) {
                error_log("Found matching narrative entry by title and year.");
            } else {
                error_log("No matching narrative entry found by title and year.");
            }
        } else {
            error_log("No PPAS form found with ID: $ppasFormId");
        }
    }
    
    if (!$narrativeData) {
        // If no data found, return an error
        echo json_encode([
            'status' => 'error',
            'message' => 'No narrative data found for the specified PPAS form',
            'code' => 'NOT_FOUND'
        ]);
        exit();
    }
    
    // Log what we found
    error_log("Found narrative data: ID: {$narrativeData['id']}, Title: {$narrativeData['title']}");
    
    // Process photo paths
    if (isset($narrativeData['photo_paths']) && !empty($narrativeData['photo_paths'])) {
        try {
            $photoArray = json_decode($narrativeData['photo_paths'], true);
            if (is_array($photoArray)) {
                // Clean up JSON string quotes if present and ensure proper paths
                $photoArray = array_map(function($path) {
                    if (is_string($path)) {
                        $path = trim($path, '"');
                        // Ensure path has the correct format
                        if (strpos($path, 'photos/') !== 0) {
                            return 'photos/' . $path;
                        }
                    }
                    return $path;
                }, $photoArray);
                $narrativeData['activity_images'] = $photoArray;
            } else {
                $narrativeData['activity_images'] = [];
            }
        } catch (Exception $e) {
            error_log("Error decoding photo_paths JSON: " . $e->getMessage());
            $narrativeData['activity_images'] = [];
        }
    } else {
        $narrativeData['activity_images'] = [];
    }
    
    // Process ratings
    if (isset($narrativeData['activity_ratings']) && !empty($narrativeData['activity_ratings'])) {
        try {
            $narrativeData['activity_ratings'] = json_decode($narrativeData['activity_ratings'], true);
        } catch (Exception $e) {
            error_log("Error decoding activity_ratings JSON: " . $e->getMessage());
            $narrativeData['activity_ratings'] = null;
        }
    }
    
    if (isset($narrativeData['timeliness_ratings']) && !empty($narrativeData['timeliness_ratings'])) {
        try {
            $narrativeData['timeliness_ratings'] = json_decode($narrativeData['timeliness_ratings'], true);
        } catch (Exception $e) {
            error_log("Error decoding timeliness_ratings JSON: " . $e->getMessage());
            $narrativeData['timeliness_ratings'] = null;
        }
    }
    
    // Format data for return
    $formattedData = [
        'id' => $narrativeData['id'],
        'activity_title' => $narrativeData['title'],
        'campus' => $narrativeData['campus'],
        'year' => $narrativeData['year'],
        'activity_narrative' => $narrativeData['background'] ?? '',
        'narrative_sections' => [
            'background_rationale' => $narrativeData['background'] ?? '',
            'description_participants' => $narrativeData['participants'] ?? '',
            'narrative_topics' => $narrativeData['topics'] ?? '',
            'expected_results' => $narrativeData['results'] ?? '',
            'lessons_learned' => $narrativeData['lessons'] ?? '',
            'what_worked' => $narrativeData['what_worked'] ?? '',
            'issues_concerns' => $narrativeData['issues'] ?? '',
            'recommendations' => $narrativeData['recommendations'] ?? ''
        ],
        'financial_requirements' => $narrativeData['ps_attribution'] ?? '0',
        'ps_attribution' => $narrativeData['ps_attribution'] ?? '0',
        'activity_images' => $narrativeData['activity_images'],
        'extension_service_agenda' => isset($narrativeData['gender_issue']) ? explode(',', $narrativeData['gender_issue']) : [],
        'activity_ratings' => $narrativeData['activity_ratings'],
        'timeliness_ratings' => $narrativeData['timeliness_ratings'],
        'date_range' => [
            'date_conducted' => $narrativeData['year'] ?? '',
            'venue' => $narrativeData['campus'] ?? ''
        ],
        'general_objectives' => $narrativeData['general_objectives'] ?? 'To successfully implement ' . $narrativeData['title'],
        'specific_objectives' => $narrativeData['specific_objectives'] ?? ['To implement the activity successfully'],
        'type_participants' => [
            'internal' => 'Students, Faculty, University Staff',
            'external' => 'Community members and stakeholders'
        ]
    ];
    
    // Add project team data - get from narrative_entries if available, otherwise use defaults
    $formattedData['project_team'] = [
        'project_leaders' => [
            [
                'name' => !empty($narrativeData['project_leader']) ? $narrativeData['project_leader'] : 'Dr. Juan Dela Cruz',
                'role' => 'Project Leader'
            ]
        ],
        'assistant_project_leaders' => [
            [
                'name' => !empty($narrativeData['assistant_leader']) ? $narrativeData['assistant_leader'] : 'Prof. Maria Santos',
                'role' => 'Assistant Project Leader'
            ]
        ],
        'project_staff' => [
            [
                'name' => !empty($narrativeData['project_staff']) ? $narrativeData['project_staff'] : 'Mr. Pedro Reyes',
                'role' => 'Staff'
            ]
        ]
    ];
    
    // Add partner office data
    $formattedData['partner_office'] = !empty($narrativeData['partner_office']) ? 
        $narrativeData['partner_office'] : 'College of Informatics and Computing Sciences';
    
    // Add financial data
    $formattedData['financial_requirements_detail'] = [
        'budget' => !empty($narrativeData['total_budget']) ? $narrativeData['total_budget'] : '10000',
        'ps_attribution' => !empty($narrativeData['ps_attribution']) ? $narrativeData['ps_attribution'] : '5000',
        'source_of_fund' => ['GAD']
    ];
    
    // Prepare signatories data - include specific names instead of placeholders
    $formattedData['signatories'] = [
        'name1' => 'Ms. RICHELLE M. SULIT',
        'name2' => 'Dr. TIRSO A. RONQUILLO',
        'name3' => 'Mr. REXON S. HERNANDEZ',
        'name4' => 'Dr. FRANCIS G. BALAZON'
    ];
    
    // Check if we have PPAS form data to use (ONLY using ppas_forms as requested)
    if (isset($ppasData) && $ppasTable) {
        // Get more details from the PPAS form
        $stmt = $conn->prepare("SELECT * FROM `$ppasTable` WHERE id = :id");
        $stmt->execute([':id' => $ppasFormId]);
        $ppasFullData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ppasFullData) {
            // Use PPAS data for financial information if available
            if (isset($ppasFullData['approved_budget']) && !empty($ppasFullData['approved_budget'])) {
                $formattedData['financial_requirements_detail']['budget'] = $ppasFullData['approved_budget'];
                $formattedData['financial_requirements'] = $ppasFullData['approved_budget'];
            }
            
            if (isset($ppasFullData['ps_attribution']) && !empty($ppasFullData['ps_attribution'])) {
                $formattedData['financial_requirements_detail']['ps_attribution'] = $ppasFullData['ps_attribution'];
                $formattedData['ps_attribution'] = $ppasFullData['ps_attribution'];
            }
            
            if (isset($ppasFullData['source_of_fund']) && !empty($ppasFullData['source_of_fund'])) {
                try {
                    $source = json_decode($ppasFullData['source_of_fund'], true);
                    if (is_array($source)) {
                        $formattedData['financial_requirements_detail']['source_of_fund'] = $source;
                    } else {
                        $formattedData['financial_requirements_detail']['source_of_fund'] = [$ppasFullData['source_of_fund']];
                    }
                } catch (Exception $e) {
                    error_log("Error decoding source_of_fund: " . $e->getMessage());
                }
            }
            
            // Use PPAS data for date information if available
            if (isset($ppasFullData['start_date']) && !empty($ppasFullData['start_date'])) {
                $formattedData['date_range']['start'] = $ppasFullData['start_date'];
            }
            
            if (isset($ppasFullData['end_date']) && !empty($ppasFullData['end_date'])) {
                $formattedData['date_range']['end'] = $ppasFullData['end_date'];
            }
            
            if (isset($ppasFullData['location']) && !empty($ppasFullData['location'])) {
                $formattedData['date_range']['venue'] = $ppasFullData['location'];
                $formattedData['location'] = $ppasFullData['location'];
            }
            
            if (isset($ppasFullData['mode_of_delivery']) && !empty($ppasFullData['mode_of_delivery'])) {
                $formattedData['mode_of_delivery'] = $ppasFullData['mode_of_delivery'];
            }
            
            // Get objectives from ppas_forms if available
            if (isset($ppasFullData['general_objectives']) && !empty($ppasFullData['general_objectives'])) {
                $formattedData['general_objectives'] = $ppasFullData['general_objectives'];
            }
            
            if (isset($ppasFullData['specific_objectives']) && !empty($ppasFullData['specific_objectives'])) {
                try {
                    $specific = json_decode($ppasFullData['specific_objectives'], true);
                    if (is_array($specific) && !empty($specific)) {
                        $formattedData['specific_objectives'] = $specific;
                    }
                } catch (Exception $e) {
                    error_log("Error decoding specific_objectives: " . $e->getMessage());
                }
            }
            
            // Get participant information if available
            if (isset($ppasFullData['type_participants']) && !empty($ppasFullData['type_participants'])) {
                $formattedData['type_participants']['description'] = $ppasFullData['type_participants'];
            }
            
            // Get project team information if available
            if (isset($ppasFullData['project_leader']) && !empty($ppasFullData['project_leader'])) {
                $formattedData['project_team']['project_leaders'][0]['name'] = $ppasFullData['project_leader'];
            }
            
            if (isset($ppasFullData['assistant_leader']) && !empty($ppasFullData['assistant_leader'])) {
                $formattedData['project_team']['assistant_project_leaders'][0]['name'] = $ppasFullData['assistant_leader'];
            }
            
            if (isset($ppasFullData['project_staff']) && !empty($ppasFullData['project_staff'])) {
                $formattedData['project_team']['project_staff'][0]['name'] = $ppasFullData['project_staff'];
            }
            
            if (isset($ppasFullData['partner_office']) && !empty($ppasFullData['partner_office'])) {
                $formattedData['partner_office'] = $ppasFullData['partner_office'];
            }
        }
    }
    
    // Try to get signatories from the database
    try {
        $signatories_sql = "SELECT * FROM signatories WHERE campus = :campus LIMIT 1";
        $signatories_stmt = $conn->prepare($signatories_sql);
        $signatories_stmt->bindParam(':campus', $formattedData['campus']);
        $signatories_stmt->execute();
        $signatories_data = $signatories_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($signatories_data) {
            // Map database fields to our signatories array
            if (!empty($signatories_data['name1'])) $formattedData['signatories']['name1'] = $signatories_data['name1'];
            if (!empty($signatories_data['name2'])) $formattedData['signatories']['name2'] = $signatories_data['name2'];
            if (!empty($signatories_data['name3'])) $formattedData['signatories']['name3'] = $signatories_data['name3'];
            if (!empty($signatories_data['name4'])) $formattedData['signatories']['name4'] = $signatories_data['name4'];
        }
    } catch (Exception $e) {
        error_log("Error fetching signatories: " . $e->getMessage());
    }
    
    // Return success response with defaults merged in for any missing values
    echo json_encode([
        'status' => 'success',
        'data' => array_merge($formattedData, [
            // Add these default values that will be used if they don't exist
            'project_team' => $formattedData['project_team'] ?? [
                'project_leaders' => [
                    ['name' => 'Dr. Juan Dela Cruz', 'role' => 'Project Leader']
                ],
                'assistant_project_leaders' => [
                    ['name' => 'Prof. Maria Santos', 'role' => 'Assistant Project Leader']
                ],
                'project_staff' => [
                    ['name' => 'Mr. Pedro Reyes', 'role' => 'Staff']
                ]
            ],
            'partner_office' => $formattedData['partner_office'] ?? 'College of Informatics and Computing Sciences',
            'financial_requirements' => $formattedData['financial_requirements'] ?? '10000',
            'ps_attribution' => $formattedData['ps_attribution'] ?? '5000',
            'financial_requirements_detail' => $formattedData['financial_requirements_detail'] ?? [
                'budget' => '10000',
                'ps_attribution' => '5000',
                'source_of_fund' => ['GAD']
            ],
            'source_of_budget' => $formattedData['source_of_budget'] ?? ['GAD'],
            'type_participants' => $formattedData['type_participants'] ?? [
                'internal' => 'Students, Faculty, University Staff',
                'external' => 'Community members and stakeholders'
            ],
            'signatories' => $formattedData['signatories'] ?? [
                'name1' => 'Ms. RICHELLE M. SULIT',
                'name2' => 'Dr. TIRSO A. RONQUILLO',
                'name3' => 'Mr. REXON S. HERNANDEZ',
                'name4' => 'Dr. FRANCIS G. BALAZON'
            ]
        ])
    ]);

} catch (Exception $e) {
    error_log("Error in get_narrative.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while retrieving narrative data',
        'error' => $e->getMessage()
    ]);
} 