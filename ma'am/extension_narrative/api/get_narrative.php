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
    
    // For extension narratives, always use ppas_forms table directly
    $ppasTable = "ppas_forms";
    error_log("Using ppas_forms table directly for extension narratives");
    
    // First, try to get the narrative entry directly by ID
    // This is the new approach where we're treating the narrative_entries ID as the primary key
    $sql = "SELECT * FROM narrative_entries WHERE id = :id AND campus = :campus";
    $params = [':id' => $ppasFormId, ':campus' => $campus];
    
    error_log("Fetching narrative_entries with ID: $ppasFormId, Campus: $campus");
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If no data found, check if this is a PPAS form ID that we need to match with a narrative entry
    if (!$narrativeData) {
        error_log("No direct match found by ID. Checking if this is a PPAS form ID...");
        
        // Get PPAS form details from ppas_forms table to find matching narrative entry
        $stmt = $conn->prepare("SELECT * FROM ppas_forms WHERE id = :id");
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
                
                // Try to find by ppas_form_id
                $sql = "SELECT * FROM narrative_entries WHERE ppas_form_id = :ppas_form_id AND campus = :campus LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':ppas_form_id' => $ppasFormId,
                    ':campus' => $campus
                ]);
                $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($narrativeData) {
                    error_log("Found matching narrative entry by ppas_form_id.");
                } else {
                    error_log("No matching narrative entry found by ppas_form_id either.");
                }
            }
        } else {
            error_log("No PPAS form found with ID: $ppasFormId");
        }
    }
    
    if (!$narrativeData) {
        // For extension narratives, retrieve data directly from ppas_forms even if no matching narrative
        $stmt = $conn->prepare("SELECT * FROM ppas_forms WHERE id = :id");
        $stmt->execute([':id' => $ppasFormId]);
        $ppasFullData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ppasFullData) {
            error_log("No narrative entry found, but retrieved data directly from ppas_forms");
            
            // Use this data to create a minimal narrative data structure
            $narrativeData = [
                'id' => $ppasFormId,
                'title' => $ppasFullData['activity'] ?? 'Unknown Activity',
                'campus' => $campus ?? 'Lipa',
                'year' => $ppasFullData['year'] ?? date('Y'),
                'background' => '',
                'participants' => '',
                'topics' => '',
                'results' => '',
                'lessons' => '',
                'what_worked' => '',
                'issues' => '',
                'recommendations' => '',
                'photo_paths' => '[]',
                'ppas_form_id' => $ppasFormId // Add this to link back to the PPAS form
            ];
        } else {
            // If no data found at all, return an error
            echo json_encode([
                'status' => 'error',
                'message' => 'No data found for the specified PPAS form ID',
                'code' => 'NOT_FOUND'
            ]);
            exit();
        }
    }
    
    // Log what we found
    error_log("Found narrative data: ID: {$narrativeData['id']}, Title: {$narrativeData['title']}");
    
    // Debug the raw narrative data
    error_log("Raw narrative data: " . json_encode($narrativeData));
    error_log("PS Attribution in narrative_entries: " . (isset($narrativeData['ps_attribution']) ? $narrativeData['ps_attribution'] : 'NOT FOUND'));
    
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
    
    // Directly query the narrative_entries table to get ps_attribution
    if (isset($narrativeData['id'])) {
        $stmt = $conn->prepare("SELECT ps_attribution FROM narrative_entries WHERE id = :id");
        $stmt->execute([':id' => $narrativeData['id']]);
        $ps_attr_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $ps_attribution = isset($ps_attr_data['ps_attribution']) && !empty($ps_attr_data['ps_attribution']) ? $ps_attr_data['ps_attribution'] : '333333';
        error_log("PS Attribution from direct query: " . $ps_attribution);
    } else {
        $ps_attribution = '333333';
        error_log("No narrative ID found, using default PS Attribution: " . $ps_attribution);
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
        'financial_requirements' => $narrativeData['total_budget'] ?? '0',
        'ps_attribution' => $ps_attribution,
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
                'role' => 'Project Leader',
                'responsibilities' => ''
            ]
        ],
        'assistant_project_leaders' => [
            [
                'name' => !empty($narrativeData['assistant_leader']) ? $narrativeData['assistant_leader'] : 'Prof. Maria Santos',
                'role' => 'Assistant Project Leader',
                'responsibilities' => ''
            ]
        ],
        'project_staff' => [
            [
                'name' => !empty($narrativeData['project_staff']) ? $narrativeData['project_staff'] : 'Mr. Pedro Reyes',
                'role' => 'Staff',
                'responsibilities' => ''
            ]
        ]
    ];
    
    // Add partner office data
    $formattedData['partner_office'] = !empty($narrativeData['partner_office']) ? 
        $narrativeData['partner_office'] : 'College of Informatics and Computing Sciences';
    
    // Add financial data
    $formattedData['financial_requirements_detail'] = [
        'budget' => !empty($narrativeData['total_budget']) ? $narrativeData['total_budget'] : '10000',
        'ps_attribution' => $ps_attribution, // Use the directly queried value
        'source_of_fund' => ['GAD']
    ];
    
    // Debug the PS Attribution value
    error_log("PS Attribution value from narrative_entries: " . (isset($narrativeData['ps_attribution']) ? $narrativeData['ps_attribution'] : 'Not set'));
    
    // Prepare signatories data - include specific names instead of placeholders
    $formattedData['signatories'] = [
        'name1' => '',
        'name2' => '',
        'name3' => '',
        'name4' => '',
        'name5' => '',
        'name6' => '',
        'name7' => ''
    ];
    
    // Check if we have PPAS form data to use (ONLY using ppas_forms as requested)
    if (isset($ppasData)) {
        // Get more details from the ppas_forms table directly
        $stmt = $conn->prepare("SELECT * FROM ppas_forms WHERE id = :id");
        $stmt->execute([':id' => $ppasFormId]);
        $ppasFullData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log what we retrieved for debugging
        error_log("PPAS form data retrieved: " . ($ppasFullData ? "Yes, with ID {$ppasFullData['id']}" : "No data found"));
        if ($ppasFullData) {
            error_log("PPAS form key fields: Activity: {$ppasFullData['activity']}, Start Date: {$ppasFullData['start_date']}, Location: {$ppasFullData['location']}");
        }
        
        if ($ppasFullData) {
            // Use PPAS data for financial information if available
            if (isset($ppasFullData['approved_budget']) && !empty($ppasFullData['approved_budget'])) {
                $formattedData['financial_requirements_detail']['budget'] = $ppasFullData['approved_budget'];
                $formattedData['financial_requirements'] = $ppasFullData['approved_budget'];
            }
            
            // We NEVER use ps_attribution from ppas_forms - narrative_data should be the only source of truth
            // We've already set ps_attribution from direct query above, don't override it here
            // These lines are kept for documentation purposes but commented out
            // $formattedData['financial_requirements_detail']['ps_attribution'] = $ps_attribution;
            // $formattedData['ps_attribution'] = $ps_attribution;
            
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
                    // Clean up the data - remove brackets and quotes if it's a string
                    if (is_string($ppasFullData['specific_objectives'])) {
                        // First try to decode as JSON
                        $specific = json_decode($ppasFullData['specific_objectives'], true);
                        
                        // If JSON decoding failed, clean it up manually
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $cleaned = trim($ppasFullData['specific_objectives'], '[]"\'');
                            $items = explode(',', $cleaned);
                            $specific = array_map('trim', $items);
                        }
                        
                        if (is_array($specific) && !empty($specific)) {
                            $formattedData['specific_objectives'] = $specific;
                        } else {
                            // If it's still not an array, make it one
                            $formattedData['specific_objectives'] = [$ppasFullData['specific_objectives']];
                        }
                    } else if (is_array($ppasFullData['specific_objectives'])) {
                        $formattedData['specific_objectives'] = $ppasFullData['specific_objectives'];
                    }
                } catch (Exception $e) {
                    error_log("Error processing specific_objectives: " . $e->getMessage());
                    // Fallback to using the raw value as a single item array
                    $formattedData['specific_objectives'] = [$ppasFullData['specific_objectives']];
                }
            }
            
            // Get participant information if available
            if (isset($ppasFullData['type_participants']) && !empty($ppasFullData['type_participants'])) {
                $formattedData['type_participants']['description'] = $ppasFullData['type_participants'];
            }
            
            // Get project team information if available
            if (isset($ppasFullData['project_leader']) && !empty($ppasFullData['project_leader'])) {
                $formattedData['project_team']['project_leaders'][0]['name'] = $ppasFullData['project_leader'];
                
                // Add project leader responsibilities from ppas_forms
                if (isset($ppasFullData['project_leader_responsibilities']) && !empty($ppasFullData['project_leader_responsibilities'])) {
                    // Try to decode JSON, or clean the string as needed
                    try {
                        if (is_string($ppasFullData['project_leader_responsibilities'])) {
                            $resp = json_decode($ppasFullData['project_leader_responsibilities'], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($resp)) {
                                $formattedData['project_team']['project_leaders'][0]['responsibilities'] = implode("\n", $resp);
                            } else {
                                // Clean up the string
                                $cleaned = trim($ppasFullData['project_leader_responsibilities'], '[]"\'');
                                $formattedData['project_team']['project_leaders'][0]['responsibilities'] = $cleaned;
                            }
                        } else {
                            $formattedData['project_team']['project_leaders'][0]['responsibilities'] = $ppasFullData['project_leader_responsibilities'];
                        }
                    } catch (Exception $e) {
                        error_log("Error processing project leader responsibilities: " . $e->getMessage());
                    }
                }
            }
            
            if (isset($ppasFullData['assistant_project_leader']) && !empty($ppasFullData['assistant_project_leader'])) {
                $formattedData['project_team']['assistant_project_leaders'][0]['name'] = $ppasFullData['assistant_project_leader'];
                
                // Add assistant project leader responsibilities
                if (isset($ppasFullData['assistant_project_leader_responsibilities']) && !empty($ppasFullData['assistant_project_leader_responsibilities'])) {
                    try {
                        if (is_string($ppasFullData['assistant_project_leader_responsibilities'])) {
                            $resp = json_decode($ppasFullData['assistant_project_leader_responsibilities'], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($resp)) {
                                $formattedData['project_team']['assistant_project_leaders'][0]['responsibilities'] = implode("\n", $resp);
                            } else {
                                // Clean up the string
                                $cleaned = trim($ppasFullData['assistant_project_leader_responsibilities'], '[]"\'');
                                $formattedData['project_team']['assistant_project_leaders'][0]['responsibilities'] = $cleaned;
                            }
                        } else {
                            $formattedData['project_team']['assistant_project_leaders'][0]['responsibilities'] = $ppasFullData['assistant_project_leader_responsibilities'];
                        }
                    } catch (Exception $e) {
                        error_log("Error processing assistant project leader responsibilities: " . $e->getMessage());
                    }
                }
            }
            
            if (isset($ppasFullData['project_staff_coordinator']) && !empty($ppasFullData['project_staff_coordinator'])) {
                $formattedData['project_team']['project_staff'][0]['name'] = $ppasFullData['project_staff_coordinator'];
                
                // Add project staff responsibilities
                if (isset($ppasFullData['project_staff_coordinator_responsibilities']) && !empty($ppasFullData['project_staff_coordinator_responsibilities'])) {
                    try {
                        if (is_string($ppasFullData['project_staff_coordinator_responsibilities'])) {
                            $resp = json_decode($ppasFullData['project_staff_coordinator_responsibilities'], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($resp)) {
                                $formattedData['project_team']['project_staff'][0]['responsibilities'] = implode("\n", $resp);
                            } else {
                                // Clean up the string
                                $cleaned = trim($ppasFullData['project_staff_coordinator_responsibilities'], '[]"\'');
                                $formattedData['project_team']['project_staff'][0]['responsibilities'] = $cleaned;
                            }
                        } else {
                            $formattedData['project_team']['project_staff'][0]['responsibilities'] = $ppasFullData['project_staff_coordinator_responsibilities'];
                        }
                    } catch (Exception $e) {
                        error_log("Error processing project staff responsibilities: " . $e->getMessage());
                    }
                }
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
            if (!empty($signatories_data['name5'])) $formattedData['signatories']['name5'] = $signatories_data['name5'];
            if (!empty($signatories_data['name6'])) $formattedData['signatories']['name6'] = $signatories_data['name6'];
            if (!empty($signatories_data['name7'])) $formattedData['signatories']['name7'] = $signatories_data['name7'];
        }
    } catch (Exception $e) {
        error_log("Error fetching signatories: " . $e->getMessage());
    }
    
    // Add prepared by position from query parameter if available
    $preparedByPosition = isset($_GET['prepared_by']) ? $_GET['prepared_by'] : 'GAD Head Secretariat';
    $formattedData['preparedByPosition'] = $preparedByPosition;
    
    // Always get the most up-to-date data from ppas_forms for extension narratives
    // This ensures we're using the correct data even if ppasFullData wasn't set above
    if (!isset($ppasFullData) || !is_array($ppasFullData)) {
        $stmt = $conn->prepare("SELECT * FROM ppas_forms WHERE id = :id");
        $stmt->execute([':id' => $ppasFormId]);
        $ppasFullData = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Retrieved ppas_forms data directly for raw fields");
    }
    
    // Add direct access to all raw PPAS data for debugging
    if (isset($ppasFullData) && is_array($ppasFullData)) {
        // Add all PPAS data directly with "ppas_" prefix for clarity
        foreach ($ppasFullData as $key => $value) {
            // Skip complex data fields that might cause JSON issues
            if (!is_object($value) && !is_resource($value)) {
                $formattedData['ppas_' . $key] = $value;
            }
        }
        
        // Add a field specifically for JavaScript to check
        $formattedData['raw_data_available'] = true;
        
        // Make direct copies of critical fields with a 'raw_' prefix
        $formattedData['raw_activity'] = $ppasFullData['activity'] ?? '';
        $formattedData['raw_title'] = $ppasFullData['activity'] ?? '';
        $formattedData['raw_start_date'] = $ppasFullData['start_date'] ?? '';
        $formattedData['raw_end_date'] = $ppasFullData['end_date'] ?? '';
        $formattedData['raw_location'] = $ppasFullData['location'] ?? '';
        $formattedData['raw_venue'] = $ppasFullData['location'] ?? '';
        $formattedData['raw_mode_of_delivery'] = $ppasFullData['mode_of_delivery'] ?? '';
        $formattedData['raw_project_leader'] = $ppasFullData['project_leader'] ?? '';
        $formattedData['raw_assistant_project_leader'] = $ppasFullData['assistant_project_leader'] ?? '';
        $formattedData['raw_project_staff_coordinator'] = $ppasFullData['project_staff_coordinator'] ?? '';
        $formattedData['raw_external_type'] = $ppasFullData['external_type'] ?? '';
        $formattedData['raw_internal_type'] = $ppasFullData['internal_type'] ?? '';
        
        // Process objectives directly to clean format
        if (isset($ppasFullData['general_objectives']) && !empty($ppasFullData['general_objectives'])) {
            // Clean up general objectives
            $cleanedGeneral = trim($ppasFullData['general_objectives'], '[]"\'');
            $formattedData['raw_general_objectives'] = $cleanedGeneral;
            $formattedData['general_objectives'] = $cleanedGeneral; // Set directly
        } else {
            $formattedData['raw_general_objectives'] = '';
        }
        
        if (isset($ppasFullData['specific_objectives']) && !empty($ppasFullData['specific_objectives'])) {
            // Process specific objectives
            if (is_string($ppasFullData['specific_objectives'])) {
                // Try JSON decode first
                $specificObj = json_decode($ppasFullData['specific_objectives'], true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($specificObj)) {
                    $formattedData['raw_specific_objectives'] = $specificObj;
                    $formattedData['specific_objectives'] = $specificObj; // Set directly
                } else {
                    // Manual processing for comma-separated list in brackets
                    $cleanedSpecific = trim($ppasFullData['specific_objectives'], '[]"\'');
                    $items = explode(',', $cleanedSpecific);
                    $cleanItems = array_map('trim', $items);
                    $formattedData['raw_specific_objectives'] = $cleanItems;
                    $formattedData['specific_objectives'] = $cleanItems; // Set directly
                }
            } else if (is_array($ppasFullData['specific_objectives'])) {
                $formattedData['raw_specific_objectives'] = $ppasFullData['specific_objectives'];
                $formattedData['specific_objectives'] = $ppasFullData['specific_objectives']; // Set directly
            } else {
                $formattedData['raw_specific_objectives'] = [$ppasFullData['specific_objectives']];
                $formattedData['specific_objectives'] = [$ppasFullData['specific_objectives']]; // Set directly
            }
        } else {
            $formattedData['raw_specific_objectives'] = [];
        }
        
        // Process responsibilities to clean them up
        if (isset($ppasFullData['project_leader_responsibilities']) && !empty($ppasFullData['project_leader_responsibilities'])) {
            if (is_string($ppasFullData['project_leader_responsibilities'])) {
                $cleanedResp = trim($ppasFullData['project_leader_responsibilities'], '[]"\'');
                $formattedData['raw_project_leader_responsibilities'] = $cleanedResp;
            } else {
                $formattedData['raw_project_leader_responsibilities'] = $ppasFullData['project_leader_responsibilities'];
            }
        }
        
        if (isset($ppasFullData['assistant_project_leader_responsibilities']) && !empty($ppasFullData['assistant_project_leader_responsibilities'])) {
            if (is_string($ppasFullData['assistant_project_leader_responsibilities'])) {
                $cleanedResp = trim($ppasFullData['assistant_project_leader_responsibilities'], '[]"\'');
                $formattedData['raw_assistant_project_leader_responsibilities'] = $cleanedResp;
            } else {
                $formattedData['raw_assistant_project_leader_responsibilities'] = $ppasFullData['assistant_project_leader_responsibilities'];
            }
        }
        
        if (isset($ppasFullData['project_staff_coordinator_responsibilities']) && !empty($ppasFullData['project_staff_coordinator_responsibilities'])) {
            if (is_string($ppasFullData['project_staff_coordinator_responsibilities'])) {
                $cleanedResp = trim($ppasFullData['project_staff_coordinator_responsibilities'], '[]"\'');
                $formattedData['raw_project_staff_coordinator_responsibilities'] = $cleanedResp;
            } else {
                $formattedData['raw_project_staff_coordinator_responsibilities'] = $ppasFullData['project_staff_coordinator_responsibilities'];
            }
        }
        $formattedData['raw_internal_male'] = $ppasFullData['internal_male'] ?? 0;
        $formattedData['raw_internal_female'] = $ppasFullData['internal_female'] ?? 0;
        $formattedData['raw_external_male'] = $ppasFullData['external_male'] ?? 0;
        $formattedData['raw_external_female'] = $ppasFullData['external_female'] ?? 0;
    } else {
        error_log("No PPAS data available to pass to client");
        // Even if we don't have PPAS data, still provide raw data from narrative if available
        $formattedData['raw_data_available'] = true;
        $formattedData['raw_title'] = $narrativeData['title'] ?? '';
    }
    
    // Return success response with defaults merged in for any missing values
    echo json_encode([
        'status' => 'success',
        'data' => array_merge($formattedData, [
            // Add these default values that will be used if they don't exist
            'project_team' => $formattedData['project_team'] ?? [
                'project_leaders' => [
                    [
                        'name' => 'Dr. Juan Dela Cruz', 
                        'role' => 'Project Leader',
                        'responsibilities' => 'Spearhead the activity; Identify the overall goal, outcome, and objectives; Monitor the flow of the activity; Conceptualize and prepare project/activity proposal'
                    ]
                ],
                'assistant_project_leaders' => [
                    [
                        'name' => 'Prof. Maria Santos', 
                        'role' => 'Assistant Project Leader',
                        'responsibilities' => 'Assist the project leader in the planning, implementation, monitoring, and evaluation of the project; Delegate work to the project coordinators and staff; Assist in coordination with the cooperating agency; Conceptualize content and information of infographic materials'
                    ]
                ],
                'project_staff' => [
                    [
                        'name' => 'Mr. Pedro Reyes', 
                        'role' => 'Staff',
                        'responsibilities' => 'Act as a technical team in the social media campaign; Coordinate with the rest of the project management team; Assist in communication with the cooperating agencies; Assist in the organization of the beneficiaries; Assist in the preparation and implementation of the activity; Prepare infographic materials; Prepare required reports/documentation; Assist in the monitoring and evaluation of the activity'
                    ]
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
                'name1' => '',
                'name2' => '',
                'name3' => '',
                'name4' => '',
                'name5' => '',
                'name6' => '',
                'name7' => ''
            ],
            'preparedByPosition' => $formattedData['preparedByPosition'] ?? 'GAD Head Secretariat'
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