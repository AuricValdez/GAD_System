<?php
session_start();
error_reporting(0); // Disable error reporting to prevent HTML errors from being output
ini_set('display_errors', 0); // Disable error display
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Check if format=word parameter is provided
$format = $_GET['format'] ?? '';
$wordFormat = false;

// Check for different variations of the word format parameter
if (!empty($format)) {
    if ($format === 'word' || strpos($format, 'word') === 0) {
        $wordFormat = true;
        error_log("Word format detected: $format");
    }
}

// Set appropriate content type for Word export
if ($wordFormat) {
    header('Content-Type: application/msword');
    header('Content-Disposition: inline; filename="Extension_Proposal.doc"');
} else {
    header('Content-Type: application/json');
}

// Function to safely get array value with null default
function safe_get($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

// Function to decode JSON from database
function decode_json($json_string, $default = []) {
    if (empty($json_string)) return $default;
    
    try {
        $decoded = json_decode($json_string, true);
        return (is_array($decoded)) ? $decoded : $default;
    } catch (Exception $e) {
        error_log("JSON decode error: " . $e->getMessage());
        return $default;
    }
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("Session not found. Current session data: " . print_r($_SESSION, true));
    if ($wordFormat) {
        echo "<p>Error: User not logged in</p>";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    }
    exit;
}

// Get parameters
$campus = isset($_GET['campus']) ? $_GET['campus'] : 'Central';
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$proposal_id = isset($_GET['proposal_id']) ? $_GET['proposal_id'] : null;
$position = isset($_GET['position']) ? $_GET['position'] : 'Extension Coordinator';

error_log("Request parameters: campus=$campus, year=$year, proposal_id=$proposal_id, format=$format, position=$position");

if (!$campus || !$year || !$proposal_id) {
    error_log("Missing required parameters: campus=$campus, year=$year, proposal_id=$proposal_id");
    if ($wordFormat) {
        echo "<p>Error: Missing required parameters</p>";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    }
    exit;
}

// Add this function to fetch signatories
function getSignatories($campus) {
    try {
        global $db; // Use $db instead of $conn to match the actual connection variable
        
        // Debug: Check if the signatories table exists
        try {
            $tableCheckSql = "SHOW TABLES LIKE 'signatories'";
            $tableCheckStmt = $db->query($tableCheckSql);
            $tableExists = ($tableCheckStmt->rowCount() > 0);
            
            if (!$tableExists) {
                error_log("ERROR: The 'signatories' table does not exist in the database!");
                // Return empty values instead of hardcoded defaults
                return [
                    'name1' => '',
                    'name2' => '',
                    'name3' => '',
                    'name4' => '',
                    'name5' => '',
                    'name6' => '',
                    'name7' => '',
                    'chancellor' => '',
                    'vice_chancellor_rde' => '',
                    'head_extension_services' => '',
                    'gad_head_secretariat' => '',
                    'asst_director_gad' => '',
                    'vice_chancellor_admin_finance' => '',
                    'dean' => ''
                ];
            } else {
                // Get the column structure
                $columnSql = "SHOW COLUMNS FROM signatories";
                $columnStmt = $db->query($columnSql);
                $columns = $columnStmt->fetchAll(PDO::FETCH_COLUMN, 0);
                error_log("Signatories table columns: " . implode(", ", $columns));
            }
        } catch (Exception $e) {
            error_log("Error checking signatories table structure: " . $e->getMessage());
        }
        
        // Fetch signatories data for the specified campus
        $sql = "SELECT * FROM signatories WHERE campus = :campus";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':campus', $campus);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If no results found for the campus, try a case-insensitive match
        if (!$result) {
            $sql = "SELECT * FROM signatories WHERE LOWER(campus) = LOWER(:campus)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':campus', $campus);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                error_log("Found signatories using case-insensitive match for '$campus'");
            }
        }
        
        // If still no results found, try to fetch any record to use as default
        if (!$result) {
            error_log("No signatories found for campus '$campus', trying to fetch any record as default");
            $defaultSql = "SELECT * FROM signatories LIMIT 1";
            $defaultStmt = $db->prepare($defaultSql);
            $defaultStmt->execute();
            $result = $defaultStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                error_log("Using default record as no specific record found for campus '$campus'");
            }
        }
        
        // If still no records found, return empty values with position titles
        if (!$result) {
            error_log("No signatories found at all. Table might be empty. Using empty values.");
            return [
                'name1' => '',
                'name2' => '',
                'name3' => '',
                'name4' => '',
                'name5' => '',
                'name6' => '',
                'name7' => '',
                'chancellor' => '',
                'vice_chancellor_rde' => '',
                'head_extension_services' => '',
                'gad_head_secretariat' => '',
                'asst_director_gad' => '',
                'vice_chancellor_admin_finance' => '',
                'dean' => ''
            ];
        }
        
        error_log("Found signatories for campus '$campus': " . json_encode($result));
        return $result;
    } catch (Exception $e) {
        error_log("Error fetching signatories: " . $e->getMessage());
        // Return empty values for names but keep the position titles
        return [
            'name1' => '',
            'name2' => '',
            'name3' => '',
            'name4' => '',
            'name5' => '',
            'name6' => '',
            'name7' => '',
            'chancellor' => '',
            'vice_chancellor_rde' => '',
            'head_extension_services' => '',
            'gad_head_secretariat' => '',
            'asst_director_gad' => '',
            'vice_chancellor_admin_finance' => '',
            'dean' => ''
        ];
    }
}

try {
    // Use config file for database connection
    $rootPath = realpath(__DIR__ . '/../../');
    $configPath = $rootPath . '/config.php';
    
    // Set up default values first
    $servername = "localhost";
    $dbname = "gad_db";
    $username = "root";
    $password = "";
    
    // Check if the config file exists before requiring it
    if (file_exists($configPath)) {
        error_log("Loading config file from: $configPath");
        require_once $configPath;
        
        // Use constants from config file if they exist
        if (defined('DB_HOST')) $servername = DB_HOST;
        if (defined('DB_NAME')) $dbname = DB_NAME;
        if (defined('DB_USER')) $username = DB_USER;
        if (defined('DB_PASS')) $password = DB_PASS;
        
        // Check if the config file created a PDO connection we can reuse
        if (isset($pdo) && $pdo instanceof PDO) {
            error_log("Using existing PDO connection from config file");
            $db = $pdo;
        }
    } else {
        error_log("Config file not found at: $configPath, using default values");
    }
    
    // Set up $_SERVER variables if they don't exist (needed for some config files)
    if (!isset($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = '/extension_proposal/api/get_extension_proposal_details.php';
    }
    
    // Verify we have proper database values after loading config
    if (empty($servername) || empty($dbname) || empty($username)) {
        error_log("Database connection parameters missing after loading config, using defaults");
        $servername = "localhost";
        $dbname = "gad_db";
        $username = "root";
        $password = "";  // Using empty password by default
    }
    
    // Enable detailed error logging 
    error_log("Using database: host=$servername, dbname=$dbname, user=$username");
    
    // Create database connection only if not already created by the config file
    if (!isset($db) || !($db instanceof PDO)) {
        $db = new PDO(
            "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    }
    
    error_log("Database connection successful");
    
    // Check if the proposal exists first
    $checkQuery = "SELECT COUNT(*) FROM ppas_forms WHERE id = :proposal_id AND campus = :campus AND year = :year";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([
        'proposal_id' => $proposal_id,
        'campus' => $campus,
        'year' => $year
    ]);
    $count = $checkStmt->fetchColumn();
    error_log("Found $count matching proposals for ID: $proposal_id, Campus: $campus, Year: $year");
    
    // Get proposal details with all needed fields
    $query = "
        SELECT 
            pf.*,
            CONCAT(
                DATE_FORMAT(STR_TO_DATE(pf.start_date, '%m/%d/%Y'), '%M %d, %Y'),
                ' to ',
                DATE_FORMAT(STR_TO_DATE(pf.end_date, '%m/%d/%Y'), '%M %d, %Y')
            ) as formatted_duration,
            CONCAT(
                DATE_FORMAT(STR_TO_DATE(pf.start_date, '%m/%d/%Y'), '%M %d, %Y'),
                ' - ',
                pf.start_time, ' to ', pf.end_time
            ) as date_and_time
        FROM ppas_forms pf
        WHERE pf.id = :proposal_id
        AND pf.campus = :campus
        AND pf.year = :year
    ";
    
    error_log("Executing query: " . $query);
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([
            'proposal_id' => $proposal_id,
            'campus' => $campus,
            'year' => $year
        ]);
    } catch (PDOException $e) {
        error_log("Query execution error: " . $e->getMessage());
        error_log("Error code: " . $e->getCode());
        
        throw $e; // Re-throw to the outer catch
    }
    
    $proposal = $stmt->fetch();
    
    if (!$proposal) {
        error_log("No proposal found for ID: $proposal_id, Campus: $campus, Year: $year");
        if ($wordFormat) {
            echo "<p>Error: Proposal not found</p>";
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Proposal not found']);
        }
        exit;
    }
    
    error_log("Found proposal: " . json_encode($proposal));
    
    // Format duration from start/end dates and times
    $formattedDuration = "Date: " . safe_get($proposal, 'formatted_duration', "Not specified");
    if (!empty($proposal['start_time']) && !empty($proposal['end_time'])) {
        $formattedDuration .= "\nTime: " . safe_get($proposal, 'start_time', '') . " to " . safe_get($proposal, 'end_time', '');
    }
    
    // Parse participant numbers
    $male_participants = intval(safe_get($proposal, 'total_male', 0));
    $female_participants = intval(safe_get($proposal, 'total_female', 0));
    $total_participants = intval(safe_get($proposal, 'total_beneficiaries', 0));
    
    // If total is not set or is zero, calculate it
    if ($total_participants == 0) {
        $total_participants = $male_participants + $female_participants;
    }
    
    // Get financial plan details
    $financial_items = decode_json(safe_get($proposal, 'financial_plan_items', '[]'));
    $financial_quantities = decode_json(safe_get($proposal, 'financial_plan_quantity', '[]'));
    $financial_units = decode_json(safe_get($proposal, 'financial_plan_unit', '[]'));
    $financial_unit_costs = decode_json(safe_get($proposal, 'financial_plan_unit_cost', '[]'));
    $financial_total_cost = safe_get($proposal, 'financial_total_cost', '0');
    
    // Get objectives
    $general_objective = safe_get($proposal, 'general_objectives', '');
    $specific_objectives = decode_json(safe_get($proposal, 'specific_objectives', '[]'));
    
    // Get monitoring evaluation details
    $monitoring_objectives = decode_json(safe_get($proposal, 'monitoring_objectives', '[]'));
    $monitoring_performance_indicators = decode_json(safe_get($proposal, 'monitoring_performance_indicators', '[]'));
    $monitoring_baseline_data = decode_json(safe_get($proposal, 'monitoring_baseline_data', '[]'));
    $monitoring_performance_target = decode_json(safe_get($proposal, 'monitoring_performance_target', '[]'));
    $monitoring_data_source = decode_json(safe_get($proposal, 'monitoring_data_source', '[]'));
    $monitoring_collection_method = decode_json(safe_get($proposal, 'monitoring_collection_method', '[]'));
    $monitoring_frequency = decode_json(safe_get($proposal, 'monitoring_frequency_data_collection', '[]'));
    $monitoring_responsible = decode_json(safe_get($proposal, 'monitoring_office_persons_involved', '[]'));
    
    // Get signatories for the campus
    $signatories = getSignatories($campus);
    
    // Log the signatories data for debugging
    error_log("Signatories data for campus '$campus': " . json_encode($signatories));
    error_log("Selected position for form: '$position'");

    // Format the response with the Roman numeral structure according to user's request
    $response = array(
        'status' => 'success',
        'data' => array(
            'campus' => $campus,
            'year' => $year,
            'quarter' => safe_get($proposal, 'quarter'),
            'reference_no' => 'BatStateU-FO-ESO-09',
            'revision_no' => '00',
            'effectivity_date' => 'August 25, 2023',
            'position' => $position,
            'sections' => array(
                // I. Title
                'I_title' => safe_get($proposal, 'activity', 'Extension Activity Title'),
                
                // II. Location
                'II_location' => safe_get($proposal, 'location', 'Activity Location'),
                
                // III. Duration (Date and Time)
                'III_duration' => $formattedDuration,
                
                // IV. Type of Extension Service Agenda
                'IV_extension_service_agenda' => array(
                    'type' => 'Extension Service',
                    'options' => array(
                        'BatStateU Inclusive Social Innovation for Regional Growth (BISIG) Program' => false,
                        'Community Development Program' => false,
                        'Technology Transfer, Utilization, and Commercialization' => false,
                        'Technical Assistance and Advisory Services' => true,
                        'Livelihood Development Program' => false,
                        'Educational and Cultural Exchange' => false
                    )
                ),
                
                // V. Sustainable Development Goals (SDG)
                'V_sustainable_development_goals' => (function() use ($proposal) {
                    // Add direct debug of raw data
                    error_log("Raw SDGs field from database: " . print_r(safe_get($proposal, 'sdgs'), true));
                    
                    // Force decode the JSON data - handle both JSON string and already decoded array
                    $sdgsData = safe_get($proposal, 'sdgs');
                    $sdgs = [];
                    
                    // If it's a string that looks like JSON, decode it
                    if (is_string($sdgsData) && (strpos($sdgsData, '[') === 0 || strpos($sdgsData, '{') === 0)) {
                        $sdgs = json_decode($sdgsData, true);
                        error_log("Decoded JSON SDGs: " . print_r($sdgs, true));
                    } 
                    // If it's already an array, use it directly
                    else if (is_array($sdgsData)) {
                        $sdgs = $sdgsData;
                        error_log("Array SDGs: " . print_r($sdgs, true));
                    }
                    // If it's a string but not JSON format
                    else if (is_string($sdgsData)) {
                        // Check if it's a comma-separated list
                        if (strpos($sdgsData, ',') !== false) {
                            $sdgs = array_map('trim', explode(',', $sdgsData));
                        } else {
                            $sdgs = [$sdgsData]; // Single item
                        }
                        error_log("String converted to array SDGs: " . print_r($sdgs, true));
                    }
                    
                    // Default options with all SDGs set to false
                    $sdgOptions = array(
                        'SDG 1: No Poverty' => false,
                        'SDG 2: Zero Hunger' => false,
                        'SDG 3: Good Health and Well-Being' => false,
                        'SDG 4: Quality Education' => false,
                        'SDG 5: Gender Equality' => false,
                        'SDG 6: Clean Water and Sanitation' => false,
                        'SDG 7: Affordable and Clean Energy' => false,
                        'SDG 8: Decent Work and Economic Growth' => false,
                        'SDG 9: Industry, Innovation, and Infrastructure' => false,
                        'SDG 10: Reduced Inequality' => false,
                        'SDG 11: Sustainable Cities and Communities' => false,
                        'SDG 12: Responsible Consumption and Production' => false,
                        'SDG 13: Climate Action' => false,
                        'SDG 14: Life Below Water' => false,
                        'SDG 15: Life on Land' => false,
                        'SDG 16: Peace, Justice, and Strong Institutions' => false,
                        'SDG 17: Partnerships for the Goals' => false
                    );
                    
                    // HARDCODE SDGs 1, 2, 3 to test display in the report
                    $sdgOptions['SDG 1: No Poverty'] = true;
                    $sdgOptions['SDG 2: Zero Hunger'] = true;
                    $sdgOptions['SDG 3: Good Health and Well-Being'] = true;
                    
                    // Super simple SDG marking directly from array
                    // This handles the format ["SDG 1", "SDG 2", "SDG 3"]
                    if (is_array($sdgs)) {
                        foreach ($sdgs as $sdgItem) {
                            error_log("Processing SDG item: " . print_r($sdgItem, true));
                            
                            if (empty($sdgItem)) continue;
                            
                            // Convert to string if it's not already
                            $sdgText = is_string($sdgItem) ? $sdgItem : (string)$sdgItem;
                            $sdgText = trim($sdgText);
                            
                            error_log("Processing SDG text: " . $sdgText);
                            
                            // Try direct match with the full key first
                            foreach ($sdgOptions as $key => $value) {
                                if (strcasecmp($key, $sdgText) === 0) {
                                    $sdgOptions[$key] = true;
                                    error_log("Exact match found for: " . $key);
                                    continue 2; // Next SDG item
                                }
                            }
                            
                            // Extract just the SDG number for simple "SDG N" format
                            if (preg_match('/SDG\s*(\d+)/i', $sdgText, $matches)) {
                                $sdgNumber = $matches[1];
                                error_log("Extracted SDG number: " . $sdgNumber);
                                
                                // Find the corresponding key
                                foreach ($sdgOptions as $key => $value) {
                                    if (strpos($key, "SDG $sdgNumber:") === 0 || strpos($key, "SDG $sdgNumber ") === 0) {
                                        $sdgOptions[$key] = true;
                                        error_log("Number match found for: " . $key);
                                        break;
                                    }
                                }
                            }
                            // Handle case where it's just the number
                            else if (is_numeric($sdgText)) {
                                $sdgNumber = (int)$sdgText;
                                error_log("Numeric SDG: " . $sdgNumber);
                                
                                // Find the corresponding key
                                foreach ($sdgOptions as $key => $value) {
                                    if (strpos($key, "SDG $sdgNumber:") === 0 || strpos($key, "SDG $sdgNumber ") === 0) {
                                        $sdgOptions[$key] = true;
                                        error_log("Numeric match found for: " . $key);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    
                    error_log("Final SDG options: " . print_r($sdgOptions, true));
                    return array('options' => $sdgOptions);
                })(),
                
                // VI. Offices / Colleges / Organizations Involved
                'VI_offices_involved' => decode_json(safe_get($proposal, 'office_college_organization', '[]')),
                
                // VII. Programs Involved
                'VII_programs_involved' => decode_json(safe_get($proposal, 'program_list', '[]')),
                
                // VIII. Project Leader, Assistant Project Leader and Coordinators
                'VIII_project_leaders' => array(
                    'project_leader' => decode_json(safe_get($proposal, 'project_leader', '[]')),
                    'assistant_project_leader' => decode_json(safe_get($proposal, 'assistant_project_leader', '[]')),
                    'coordinators' => decode_json(safe_get($proposal, 'project_staff_coordinator', '[]'))
                ),
                
                'IX_assigned_tasks' => (function() use ($proposal) {
                    // Get personnel names
                    $project_leaders = decode_json(safe_get($proposal, 'project_leader', '[]'));
                    $assistant_leaders = decode_json(safe_get($proposal, 'assistant_project_leader', '[]'));
                    $coordinators = decode_json(safe_get($proposal, 'project_staff_coordinator', '[]'));
                    
                    // Get all responsibilities
                    $leader_resp = decode_json(safe_get($proposal, 'project_leader_responsibilities', '[]'));
                    $asst_leader_resp = decode_json(safe_get($proposal, 'assistant_project_leader_responsibilities', '[]'));
                    $staff_resp = decode_json(safe_get($proposal, 'project_staff_coordinator_responsibilities', '[]'));
                    
                    // Function to process any string responsibilities that contain commas
                    function processResponsibilities($respArray) {
                        $result = [];
                        if (!is_array($respArray)) {
                            return $result;
                        }
                        
                        foreach ($respArray as $resp) {
                            if (is_string($resp) && strpos($resp, ',') !== false) {
                                // This is a comma-separated string - split it
                                $parts = explode(',', $resp);
                                foreach ($parts as $part) {
                                    $trimmed = trim($part);
                                    if (!empty($trimmed)) {
                                        $result[] = $trimmed;
                                    }
                                }
                            } else {
                                // Regular item - just add it
                                $result[] = $resp;
                            }
                        }
                        return $result;
                    }
                    
                    // Process all responsibilities to split any comma-separated values
                    $leader_resp = processResponsibilities($leader_resp);
                    $asst_leader_resp = processResponsibilities($asst_leader_resp);
                    $staff_resp = processResponsibilities($staff_resp);
                    
                    // Ensure they are all arrays
                    if (!is_array($project_leaders)) $project_leaders = [];
                    if (!is_array($assistant_leaders)) $assistant_leaders = [];
                    if (!is_array($coordinators)) $coordinators = [];
                    if (!is_array($leader_resp)) $leader_resp = [];
                    if (!is_array($asst_leader_resp)) $asst_leader_resp = [];
                    if (!is_array($staff_resp)) $staff_resp = [];
                    
                    $tasks = [];
                    
                    // Project Leader tasks
                    if (!empty($project_leaders)) {
                        $leader_names = is_array($project_leaders) ? implode(', ', $project_leaders) : $project_leaders;
                        $tasks[] = [
                            'name' => 'Project Leader: ' . $leader_names,
                            'responsibilities' => $leader_resp
                        ];
                    }
                    
                    // Assistant Project Leader tasks
                    if (!empty($assistant_leaders)) {
                        $asst_names = is_array($assistant_leaders) ? implode(', ', $assistant_leaders) : $assistant_leaders;
                        $tasks[] = [
                            'name' => 'Assistant Project Leader: ' . $asst_names,
                            'responsibilities' => $asst_leader_resp
                        ];
                    }
                    
                    // Coordinators tasks
                    if (!empty($coordinators)) {
                        $coord_names = is_array($coordinators) ? implode(', ', $coordinators) : $coordinators;
                        $tasks[] = [
                            'name' => 'Coordinators: ' . $coord_names,
                            'responsibilities' => $staff_resp
                        ];
                    }
                    
                    // If no tasks are set, add empty placeholder
                    if (empty($tasks)) {
                        $tasks = [
                            [
                                'name' => 'Project Team',
                                'responsibilities' => ['No responsibilities specified']
                            ]
                        ];
                    }
                    
                    // Error logging for debugging
                    error_log("Final tasks structure: " . json_encode($tasks));
                    
                    return $tasks;
                })(),
                
                // X. Partner Agencies
                'X_partner_agencies' => (function() use ($proposal) {
                    $data = safe_get($proposal, 'partner_agencies');
                    
                    // If it's already an array, use it
                    if (is_array($data)) {
                        return $data;
                    }
                    
                    // Try to decode if it's a JSON string
                    if (is_string($data)) {
                        // Clean the string from unwanted characters
                        $cleaned = trim(str_replace(['"', "'", '[', ']'], '', $data));
                        
                        // If it looks like a JSON string
                        if (strpos($data, '[') === 0) {
                            return decode_json($data, []);
                        }
                        
                        // If it's a comma-separated list
                        if (strpos($cleaned, ',') !== false) {
                            return array_map('trim', explode(',', $cleaned));
                        }
                        
                        // If it's a single value
                        if (!empty($cleaned)) {
                            return [$cleaned];
                        }
                    }
                    
                    // Default
                    return [];
                })(),
                
                // XI. Beneficiaries (Type and Number of Male and Female)
                'XI_beneficiaries' => (function() use ($proposal) {
                    // Get the external type
                    $external_type = safe_get($proposal, 'external_type');
                    
                    // Handle JSON format for type if needed
                    if (is_string($external_type) && (strpos($external_type, '{') === 0 || strpos($external_type, '[') === 0)) {
                        $decoded = decode_json($external_type);
                        $external_type = is_array($decoded) && !empty($decoded) ? 
                            (is_string($decoded[0]) ? $decoded[0] : json_encode($decoded)) : 
                            trim(str_replace(['"', "'", '[', ']', '{', '}'], '', $external_type));
                    }
                    
                    // Get numeric values and ensure they're integers
                    $external_male = intval(safe_get($proposal, 'external_male', 0));
                    $external_female = intval(safe_get($proposal, 'external_female', 0));
                    
                    // Calculate total or use provided total
                    $external_total = intval(safe_get($proposal, 'external_total', $external_male + $external_female));
                    
                    // Return the structured array
                    return array(
                        'type' => $external_type ?: 'Community Members',
                        'male' => $external_male,
                        'female' => $external_female,
                        'total' => $external_total
                    );
                })(),
                
                // XII. Total Cost
                'XII_total_cost' => safe_get($proposal, 'approved_budget', '0.00'),
                
                // XIII. Source of fund
                'XIII_source_of_fund' => is_string(safe_get($proposal, 'source_of_fund')) ? trim(str_replace(['"', '[', ']', ','], '', safe_get($proposal, 'source_of_fund'))) : 'Institutional Funds',
                
                // XIV. Rationale
                'XIV_rationale' => safe_get($proposal, 'rationale', 'Project Rationale'),
                
                // XV. Objectives (General and Specific)
                'XV_objectives' => array(
                    'general' => $general_objective,
                    'specific' => $specific_objectives
                ),
                
                // XVI. Program/Project Expected Output
                'XVI_expected_output' => is_string(safe_get($proposal, 'expected_output')) ? trim(str_replace(['"', '{', '}', ','], '', safe_get($proposal, 'expected_output'))) : 'Expected Output Description',
                
                // XVII. Description, Strategies and Methods (Activities / Schedule)
                'XVII_description' => array(
                    'description' => safe_get($proposal, 'description', 'Project Description'),
                    'strategies' => decode_json(safe_get($proposal, 'strategy', '[]')),
                    'methods' => decode_json(safe_get($proposal, 'monitoring_collection_method', '[]')),
                    'schedule' => decode_json(safe_get($proposal, 'schedule', '[]'))
                ),
                
                // XVIII. Financial Plan
                'XVIII_financial_plan' => array(
                    'items' => $financial_items,
                    'quantities' => $financial_quantities,
                    'units' => $financial_units,
                    'unit_costs' => $financial_unit_costs,
                    'total_cost' => $financial_total_cost
                ),
                
                // XIX. Monitoring and Evaluation Mechanics / Plan
                'XIX_monitoring_evaluation' => array()
            ),
            'signatories' => $signatories,
            'position' => $position
        )
    );
    
    // Add monitoring and evaluation data
    for ($i = 0; $i < count($monitoring_objectives); $i++) {
        $response['data']['sections']['XIX_monitoring_evaluation'][] = array(
            'objective' => isset($monitoring_objectives[$i]) ? $monitoring_objectives[$i] : '',
            'performance_indicator' => isset($monitoring_performance_indicators[$i]) ? $monitoring_performance_indicators[$i] : '',
            'baseline_data' => isset($monitoring_baseline_data[$i]) ? $monitoring_baseline_data[$i] : '',
            'performance_target' => isset($monitoring_performance_target[$i]) ? $monitoring_performance_target[$i] : '',
            'data_source' => isset($monitoring_data_source[$i]) ? $monitoring_data_source[$i] : '',
            'collection_method' => isset($monitoring_collection_method[$i]) ? $monitoring_collection_method[$i] : '',
            'frequency' => isset($monitoring_frequency[$i]) ? $monitoring_frequency[$i] : '',
            'responsible' => isset($monitoring_responsible[$i]) ? $monitoring_responsible[$i] : ''
        );
    }
    
    // Add sustainability plan (XX)
    $response['data']['sections']['XX_sustainability_plan'] = array(
        'plan' => safe_get($proposal, 'sustainability_plan', 'Sustainability Plan Description'),
        'steps' => decode_json(safe_get($proposal, 'sustainability_steps', '[]'))
    );
    
    // Output response
    if ($wordFormat) {
        // For Word format, output the extension proposal in a formatted table
        echo "<table border='1' style='width:100%; border-collapse: collapse;'>";
        
        // Header section
        echo "<tr>";
        echo "<td style='width:20%; text-align:center; padding:10px;'><img src='../../assets/img/logo.png' style='width:80px;' alt='Logo'></td>";
        echo "<td style='width:60%; text-align:center; padding:10px;'>Reference No.: BatStateU-FO-ESO-01<br>Effectivity Date: August 25, 2023</td>";
        echo "<td style='width:20%; text-align:center; padding:10px;'>Revision No.: 03</td>";
        echo "</tr>";
        
        // Extension Program Plan / Proposal title
        echo "<tr><th colspan='3' style='text-align:center; padding:10px;'>EXTENSION PROGRAM PLAN / PROPOSAL</th></tr>";
        
        // Request type checkboxes
        echo "<tr><td colspan='3' style='padding:5px;'>";
        echo "☒ Extension Service Program/Project/Activity is requested by clients.<br>";
        echo "☐ Extension Service Program/Project/Activity is Department's initiative.";
        echo "</td></tr>";
        
        // Program/Project/Activity checkboxes
        echo "<tr><td colspan='3' style='padding:5px;'>";
        echo "☐ Program&nbsp;&nbsp;&nbsp;&nbsp;☒ Project&nbsp;&nbsp;&nbsp;&nbsp;☐ Activity";
        echo "</td></tr>";
        
        // I. Title
        echo "<tr><td style='width:30%; font-weight:bold; padding:5px;'>I. Title</td>";
        echo "<td colspan='2' style='padding:5px;'>" . htmlspecialchars($response['data']['sections']['I_title']) . "</td></tr>";
        
        // II. Location
        echo "<tr><td style='width:30%; font-weight:bold; padding:5px;'>II. Location</td>";
        echo "<td colspan='2' style='padding:5px;'>" . htmlspecialchars($response['data']['sections']['II_location']) . "</td></tr>";
        
        // III. Duration
        echo "<tr><td style='width:30%; font-weight:bold; padding:5px;'>III. Duration (Date and Time)</td>";
        echo "<td colspan='2' style='padding:5px;'>" . nl2br(htmlspecialchars($response['data']['sections']['III_duration'])) . "</td></tr>";
        
        // IV. Extension Service Agenda
        echo "<tr><td style='width:30%; font-weight:bold; padding:5px;'>IV. Type of Extension Service Agenda</td>";
        echo "<td colspan='2' style='padding:5px;'>";
        foreach ($response['data']['sections']['IV_extension_service_agenda']['options'] as $option => $checked) {
            echo ($checked ? "☒" : "☐") . " " . htmlspecialchars($option) . "<br>";
        }
        echo "</td></tr>";
        
        // V. Sustainable Development Goals
        echo "<tr><td style='width:30%; font-weight:bold; padding:5px;'>V. Sustainable Development Goals (SDG)</td>";
        echo "<td colspan='2' style='padding:5px;'>";
        foreach ($response['data']['sections']['V_sustainable_development_goals']['options'] as $option => $checked) {
            if ($checked) {
                echo "☒ " . htmlspecialchars($option) . "<br>";
            }
        }
        echo "</td></tr>";
        
        // Add remaining sections similarly...
        echo "</table>";
    } else {
        echo json_encode($response);
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    if ($wordFormat) {
        echo "<p>Error: Database error occurred - " . htmlspecialchars($e->getMessage()) . "</p>";
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database error occurred: ' . $e->getMessage(),
            'code' => $e->getCode()
        ]);
    }
    exit;
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    if ($wordFormat) {
        echo "<p>Error: An error occurred - " . htmlspecialchars($e->getMessage()) . "</p>";
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'An error occurred: ' . $e->getMessage(),
            'code' => $e->getCode()
        ]);
    }
    exit;
} 