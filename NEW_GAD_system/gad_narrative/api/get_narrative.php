<?php
session_start();
header('Content-Type: application/json');

// ENABLE FULL DEBUG MODE FOR TROUBLESHOOTING
$DEBUG_MODE = true;
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug function
function debug_log($message, $data = null) {
    global $DEBUG_MODE;
    if ($DEBUG_MODE) {
        $log = "[DEBUG] " . $message;
        if ($data !== null) {
            if (is_array($data) || is_object($data)) {
                $log .= ": " . json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR);
            } else {
                $log .= ": " . (string)$data;
            }
        }
        error_log($log);
    }
}

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

// Helper function to check if a string is valid JSON
function isJson($string) {
    if (!is_string($string)) return false;
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

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

    // Add debug for request parameters
    debug_log("REQUEST PARAMETERS", ['ppas_form_id' => $ppasFormId, 'campus' => $campus]);

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
    debug_log("DATABASE CONNECTION ESTABLISHED", $conn ? "SUCCESS" : "FAILED");
    
    // Find the PPAS table in the database
    $ppasTable = findPpasTable($conn);
    debug_log("PPAS TABLE DETECTION", ['table_found' => $ppasTable]);
    error_log("Found PPAS table: " . ($ppasTable ? $ppasTable : "No PPAS table found"));
    
    if (!$ppasTable) {
        echo json_encode([
            'status' => 'error',
            'message' => 'PPAS table not found in database',
            'code' => 'TABLE_NOT_FOUND'
        ]);
        exit();
    }
    
    // First fetch the PPAS form data
    $sql = "SELECT * FROM `$ppasTable` WHERE id = :id";
    $params = [':id' => $ppasFormId];
    debug_log("BUILDING SQL QUERY", ['sql' => $sql, 'params' => $params]);
    
    // Add campus filter if provided
    if ($campus) {
        $sql .= " AND campus = :campus";
        $params[':campus'] = $campus;
        debug_log("ADDING CAMPUS FILTER", ['campus' => $campus, 'updated_sql' => $sql]);
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    debug_log("EXECUTED PPAS QUERY", ['query' => $sql, 'parameters' => $params]);
    
    $ppasData = $stmt->fetch(PDO::FETCH_ASSOC);
    debug_log("PPAS QUERY RESULT COUNT", $ppasData ? 1 : 0);
    
    if (!$ppasData) {
        // If no data found, return an error
        debug_log("NO PPAS DATA FOUND", ['id' => $ppasFormId, 'campus' => $campus]);
        echo json_encode([
            'status' => 'error',
            'message' => 'No PPAS form data found for the specified ID',
            'code' => 'NOT_FOUND'
        ]);
        exit();
    }
    
    // Log each field from PPAS data
    debug_log("PPAS DATA FIELD COUNT", count($ppasData));
    foreach ($ppasData as $key => $value) {
        $displayValue = is_string($value) ? 
            (strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value) : 
            json_encode($value);
        debug_log("PPAS FIELD: $key", $displayValue);
    }
    
    // Explicitly get key fields from ppas_forms
    $ppasActivity = $ppasData['activity'] ?? 'Untitled Activity';
    $ppasYear = $ppasData['year'];
    debug_log("KEY PPAS FIELDS", ['activity' => $ppasActivity, 'year' => $ppasYear]);

    // Initialize the response data structure with PPAS data
    $responseData = [
        'id' => $ppasData['id'],
        'ppas_form_id' => $ppasData['id'],
        'activity_title' => $ppasActivity, // Always use activity from ppas_forms
        'implementing_office' => $ppasData['office_college_organization'] ?? null,
        'campus' => $ppasData['campus'],
        'year' => $ppasYear, // Always use year from ppas_forms
        'date_venue' => [
            'date' => $ppasData['date'] ?? null,
            'venue' => $ppasData['venue'] ?? null
        ],
        'sdg' => $ppasData['sdg'] ?? null,
        'location' => $ppasData['location'] ?? null,
        'duration' => [
            'start_date' => $ppasData['start_date'] ?? null,
            'end_date' => $ppasData['end_date'] ?? null,
            'start_time' => $ppasData['start_time'] ?? null,
            'end_time' => $ppasData['end_time'] ?? null,
            'total_duration_hours' => $ppasData['total_duration'] ?? null
        ],
        'beneficiary_data' => [
            'internal_type' => $ppasData['internal_type'] ?? '',
            'internal_male' => $ppasData['internal_male'] ?? 0,
            'internal_female' => $ppasData['internal_female'] ?? 0,
            'internal_total' => $ppasData['internal_total'] ?? 0,
            'external_type' => $ppasData['external_type'] ?? '',
            'external_male' => $ppasData['external_male'] ?? 0,
            'external_female' => $ppasData['external_female'] ?? 0,
            'external_total' => $ppasData['external_total'] ?? 0,
            'grand_total_male' => $ppasData['grand_total_male'] ?? 0,
            'grand_total_female' => $ppasData['grand_total_female'] ?? 0,
            'grand_total' => $ppasData['grand_total'] ?? 0
        ],
        'project_team' => [
            'project_leaders' => [
                'names' => $ppasData['project_leader'] ?? null,
                'responsibilities' => $ppasData['project_leader_responsibilities'] ?? null,
                'designation' => 'Project Leader'
            ],
            'assistant_project_leaders' => [
                'names' => $ppasData['assistant_project_leader'] ?? null,
                'responsibilities' => $ppasData['assistant_project_leader_responsibilities'] ?? null,
                'designation' => 'Assistant Project Leader'
            ],
            'project_staff' => [
                'names' => $ppasData['project_staff_coordinator'] ?? null,
                'responsibilities' => $ppasData['project_staff_coordinator_responsibilities'] ?? null,
                'designation' => 'Project Staff'
            ]
        ],
        'activity_ratings' => null,
        'timeliness_ratings' => null,
        'activity_images' => [],
        // Add the new required fields from ppas_forms
        'agenda' => $ppasData['agenda'] ?? null,
        'general_objectives' => $ppasData['general_objectives'] ?? null,
        'specific_objectives' => $ppasData['specific_objectives'] ?? null
    ];
    debug_log("INITIAL RESPONSE DATA STRUCTURE CREATED", array_keys($responseData));
    
    // First check the 'narrative_entries' table
    $narrativeData = null;
    
    try {
        // EMERGENCY FIX: Direct attempt to fetch ANY narrative_entries data
        $query = "SELECT * FROM narrative_entries";
        $conditions = [];
        $params = [];
        
        debug_log("BUILDING NARRATIVE ENTRIES QUERY", ['base_query' => $query]);
        
        // Add campus filter if available
        if (isset($ppasData['campus']) && !empty($ppasData['campus'])) {
            $conditions[] = "campus = :campus";
            $params[':campus'] = $ppasData['campus'];
            debug_log("ADDED CAMPUS FILTER TO NARRATIVE QUERY", ['campus' => $ppasData['campus']]);
        }
        
        // Add year filter if available
        if (isset($ppasData['year']) && !empty($ppasData['year'])) {
            $conditions[] = "(year = :year OR YEAR(date) = :year)";
            $params[':year'] = $ppasData['year'];
            debug_log("ADDED YEAR FILTER TO NARRATIVE QUERY", ['year' => $ppasData['year']]);
        }
        
        // Add title filter as a very loose match if available
        if (isset($ppasActivity) && !empty($ppasActivity)) {
            // Extract a few keywords from the activity title
            $keywords = preg_split('/\s+/', $ppasActivity, -1, PREG_SPLIT_NO_EMPTY);
            debug_log("KEYWORDS EXTRACTED FROM ACTIVITY TITLE", $keywords);
            
            if (count($keywords) > 0) {
                $titleConditions = [];
                foreach (array_slice($keywords, 0, min(3, count($keywords))) as $i => $keyword) {
                    if (strlen($keyword) > 3) { // Only use keywords longer than 3 chars
                        $paramName = ":keyword{$i}";
                        $titleConditions[] = "title LIKE {$paramName}";
                        $params[$paramName] = '%' . $keyword . '%';
                        debug_log("ADDED KEYWORD MATCH", ['keyword' => $keyword, 'param' => $paramName]);
                    }
                }
                if (!empty($titleConditions)) {
                    $conditions[] = '(' . implode(' OR ', $titleConditions) . ')';
                }
            }
        }
        
        // Build the final query
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        
        // Add ordering to get most recent entry first
        $query .= " ORDER BY id DESC LIMIT 1";
        
        debug_log("FINAL NARRATIVE QUERY", ['query' => $query, 'params' => $params]);
        
        // Execute the query
        $stmt = $conn->prepare($query);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
            debug_log("BINDING PARAMETER", ['param' => $param, 'value' => $value]);
        }
        $stmt->execute();
        debug_log("NARRATIVE QUERY EXECUTED");
        
        $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log the raw data found
        if ($narrativeData) {
            debug_log("FOUND DATA IN narrative_entries", ['row_count' => 1, 'field_count' => count($narrativeData), 'fields' => array_keys($narrativeData)]);
            
            // Dump all the data values for debugging
            foreach ($narrativeData as $key => $value) {
                $truncatedValue = is_string($value) ? 
                    (strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value) : 
                    json_encode($value);
                debug_log("NARRATIVE FIELD: $key", $truncatedValue);
            }
        } else {
            debug_log("NO DATA FOUND IN narrative_entries with the current query");
            
            // Try more direct approaches
            debug_log("ATTEMPTING ALTERNATIVE QUERY METHODS");
            
            // Try direct ID match if ppas_form_id column exists
            debug_log("TRYING DIRECT ppas_form_id MATCH");
            try {
                $checkStmt = $conn->prepare("SHOW COLUMNS FROM narrative_entries LIKE 'ppas_form_id'");
                $checkStmt->execute();
                debug_log("CHECKING FOR ppas_form_id COLUMN", ['exists' => ($checkStmt->rowCount() > 0)]);
                
                if ($checkStmt->rowCount() > 0) {
                    debug_log("ppas_form_id COLUMN EXISTS, QUERYING DIRECTLY", ['ppas_form_id' => $ppasFormId]);
                    $stmt = $conn->prepare("SELECT * FROM narrative_entries WHERE ppas_form_id = :ppas_id LIMIT 1");
                    $stmt->execute([':ppas_id' => $ppasFormId]);
                    $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($narrativeData) {
                        debug_log("FOUND RECORD BY DIRECT ppas_form_id MATCH", ['record_id' => $narrativeData['id'] ?? 'unknown']);
                        // Dump all found fields
                        foreach ($narrativeData as $key => $value) {
                            $truncatedValue = is_string($value) ? 
                                (strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value) : 
                                json_encode($value);
                            debug_log("DIRECT MATCH FIELD: $key", $truncatedValue);
                        }
                    } else {
                        debug_log("NO RECORD FOUND BY DIRECT ppas_form_id MATCH", ['ppas_form_id' => $ppasFormId]);
                    }
                } else {
                    debug_log("ppas_form_id COLUMN DOES NOT EXIST IN narrative_entries TABLE");
                }
            } catch (Exception $e) {
                debug_log("ERROR CHECKING FOR ppas_form_id COLUMN", $e->getMessage());
            }
            
            // If still not found, try to get any record
            if (!$narrativeData) {
                debug_log("TRYING TO FETCH ANY RECORD FROM narrative_entries");
                $stmt = $conn->prepare("SELECT * FROM narrative_entries LIMIT 1");
                $stmt->execute();
                $sampleRecord = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($sampleRecord) {
                    debug_log("SAMPLE RECORD FIELDS FROM narrative_entries", array_keys($sampleRecord));
                    
                    // As a last resort, get the most recent record
                    debug_log("FETCHING MOST RECENT RECORD AS FALLBACK");
                    $stmt = $conn->prepare("SELECT * FROM narrative_entries ORDER BY id DESC LIMIT 1");
                    $stmt->execute();
                    $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($narrativeData) {
                        debug_log("USING MOST RECENT RECORD AS FALLBACK", ['id' => $narrativeData['id'] ?? 'unknown']);
                        // Dump all found fields from fallback
                        foreach ($narrativeData as $key => $value) {
                            $truncatedValue = is_string($value) ? 
                                (strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value) : 
                                json_encode($value);
                            debug_log("FALLBACK FIELD: $key", $truncatedValue);
                        }
                    }
                } else {
                    debug_log("narrative_entries TABLE APPEARS TO BE EMPTY OR DOES NOT EXIST");
                    
                    // Check if the table exists
                    try {
                        $stmt = $conn->prepare("SHOW TABLES LIKE 'narrative_entries'");
                        $stmt->execute();
                        debug_log("CHECKING IF narrative_entries TABLE EXISTS", ['exists' => ($stmt->rowCount() > 0)]);
                        
                        if ($stmt->rowCount() == 0) {
                            debug_log("CRITICAL: narrative_entries TABLE DOES NOT EXIST IN THE DATABASE");
                        }
                    } catch (Exception $e) {
                        debug_log("ERROR CHECKING IF narrative_entries TABLE EXISTS", $e->getMessage());
                    }
                }
            }
        }
        
        // Map fields if we found data
        if ($narrativeData) {
            debug_log("PROCESSING FOUND narrative_entries DATA", ['record_id' => $narrativeData['id'] ?? 'unknown']);
            
            // Map all core fields from narrative_entries to responseData
            $fieldMappings = [
                // Direct field mappings (source => target)
                'background' => 'background_rationale',
                'participants' => 'description_participants',
                'topics' => 'narrative_topics',
                'results' => 'expected_results',
                'lessons' => 'lessons_learned',
                'what_worked' => 'what_worked',
                'issues' => 'issues_concerns',
                'recommendations' => 'recommendations',
                'photo_caption' => 'photo_caption',
                'gender_issue' => 'gender_issue',
                'partner_agency' => 'partner_agency',
                'extension_service_agenda' => 'extension_service_agenda',
                'title' => 'title',
                'created_at' => 'date',
                'activity_ratings' => 'activity_ratings',
                'timeliness_ratings' => 'timeliness_ratings',
                'evaluation' => 'evaluation',
                'implementing_office' => 'implementing_office',
                'summary' => 'summary',
                'narrative' => 'activity_narrative',
                // Add any additional fields that might be present
                'ppas_form_id' => 'ppas_form_id_from_narrative',
                'agenda' => 'agenda'
            ];
            
            debug_log("FIELD MAPPING DEFINITION", ['field_count' => count($fieldMappings)]);
            
            // Map fields from narrative_entries to responseData
            foreach ($fieldMappings as $sourceField => $targetField) {
                if (isset($narrativeData[$sourceField]) && !empty($narrativeData[$sourceField])) {
                    $responseData[$targetField] = $narrativeData[$sourceField];
                    $displayValue = is_string($narrativeData[$sourceField]) ? 
                        (strlen($narrativeData[$sourceField]) > 100 ? substr($narrativeData[$sourceField], 0, 100) . '...' : $narrativeData[$sourceField]) : 
                        json_encode($narrativeData[$sourceField]);
                    debug_log("MAPPED FIELD", ['source' => $sourceField, 'target' => $targetField, 'value' => $displayValue]);
                } else {
                    debug_log("FIELD NOT MAPPED (EMPTY OR NOT FOUND)", ['source' => $sourceField, 'target' => $targetField]);
                }
            }
            
            // Also ensure we directly map any extension_service_agenda field if it exists
            if (isset($narrativeData['extension_service_agenda']) && !empty($narrativeData['extension_service_agenda'])) {
                // Debug the raw value first
                debug_log("RAW extension_service_agenda FIELD", $narrativeData['extension_service_agenda']);
                
                // Check if it's valid JSON or wrap it in an array
                if (is_string($narrativeData['extension_service_agenda'])) {
                    debug_log("extension_service_agenda IS STRING, ATTEMPTING JSON DECODE");
                    $decodedAgenda = json_decode($narrativeData['extension_service_agenda'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedAgenda)) {
                        $responseData['extension_service_agenda'] = $decodedAgenda;
                        debug_log("SUCCESSFULLY DECODED extension_service_agenda JSON", $decodedAgenda);
                    } else {
                        // If not valid JSON, wrap it in an array to ensure it's processed properly on the client side
                        debug_log("INVALID JSON IN extension_service_agenda, WRAPPING AS ARRAY", ['json_error' => json_last_error_msg()]);
                        $responseData['extension_service_agenda'] = [$narrativeData['extension_service_agenda']];
                    }
                } else if (is_array($narrativeData['extension_service_agenda'])) {
                    debug_log("extension_service_agenda IS ALREADY AN ARRAY");
                    $responseData['extension_service_agenda'] = $narrativeData['extension_service_agenda'];
                } else {
                    debug_log("extension_service_agenda IS NEITHER STRING NOR ARRAY", ['type' => gettype($narrativeData['extension_service_agenda'])]);
                    // For any other type, convert to array
                    $responseData['extension_service_agenda'] = [$narrativeData['extension_service_agenda']];
                }
            } else {
                debug_log("extension_service_agenda FIELD NOT FOUND OR EMPTY");
            }

            // Handle the regular agenda field too
            if (isset($narrativeData['agenda']) && !empty($narrativeData['agenda'])) {
                // Debug the raw value first
                debug_log("RAW agenda FIELD", $narrativeData['agenda']);
                
                // Check if it's valid JSON or wrap it in an array
                if (is_string($narrativeData['agenda'])) {
                    debug_log("agenda IS STRING, ATTEMPTING JSON DECODE");
                    $decodedAgenda = json_decode($narrativeData['agenda'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedAgenda)) {
                        $responseData['agenda'] = $decodedAgenda;
                        debug_log("SUCCESSFULLY DECODED agenda JSON", $decodedAgenda);
                    } else {
                        // If not valid JSON, wrap it in an array to ensure it's processed properly on the client side
                        debug_log("INVALID JSON IN agenda, WRAPPING AS ARRAY", ['json_error' => json_last_error_msg()]);
                        $responseData['agenda'] = [$narrativeData['agenda']];
                    }
                } else if (is_array($narrativeData['agenda'])) {
                    debug_log("agenda IS ALREADY AN ARRAY");
                    $responseData['agenda'] = $narrativeData['agenda'];
                } else {
                    debug_log("agenda IS NEITHER STRING NOR ARRAY", ['type' => gettype($narrativeData['agenda'])]);
                    // For any other type, convert to array
                    $responseData['agenda'] = [$narrativeData['agenda']];
                }
            } else {
                debug_log("agenda FIELD NOT FOUND OR EMPTY");
            }
            
            // Enhanced image processing - check both photo_paths and photo_path fields
            $photoPathsArray = [];
            debug_log("STARTING IMAGE PATH PROCESSING");
            
            // First try photo_paths (JSON array)
            if (isset($narrativeData['photo_paths']) && !empty($narrativeData['photo_paths'])) {
                debug_log("FOUND photo_paths FIELD", $narrativeData['photo_paths']);
                if (is_string($narrativeData['photo_paths'])) {
                    // SPECIAL HANDLING FOR ESCAPED SLASHES - First try with a direct processing approach
                    debug_log("ATTEMPTING SPECIAL PROCESSING FOR ESCAPED SLASHES");
                    
                    // Replace escaped slashes with a temporary marker
                    $tempMarker = "##SLASH##";
                    $processedString = str_replace('\/', $tempMarker, $narrativeData['photo_paths']);
                    debug_log("PROCESSED STRING WITH TEMP MARKERS", $processedString);
                    
                    // Now try to decode
                    $decodedPaths = json_decode($processedString, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedPaths)) {
                        // Restore slashes
                        foreach ($decodedPaths as &$path) {
                            $path = str_replace($tempMarker, '/', $path);
                        }
                        $photoPathsArray = $decodedPaths;
                        debug_log("SUCCESSFULLY DECODED WITH SPECIAL PROCESSING", $photoPathsArray);
                    } else {
                        // If special processing fails, try standard decode
                        debug_log("SPECIAL PROCESSING FAILED, TRYING STANDARD JSON DECODE");
                        $decodedPaths = json_decode($narrativeData['photo_paths'], true);
                        
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedPaths)) {
                            $photoPathsArray = $decodedPaths;
                            debug_log("SUCCESSFULLY DECODED photo_paths JSON INTO ARRAY", $photoPathsArray);
                        } else {
                            // If both methods fail, try one more approach with stripslashes
                            debug_log("STANDARD JSON DECODE FAILED, TRYING WITH stripslashes");
                            $unescaped = stripslashes($narrativeData['photo_paths']);
                            debug_log("UNESCAPED STRING", $unescaped);
                            
                            $decodedPaths = json_decode($unescaped, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedPaths)) {
                                $photoPathsArray = $decodedPaths;
                                debug_log("SUCCESSFULLY DECODED AFTER stripslashes", $photoPathsArray);
                            } else {
                                // If all methods fail, try one final approach - direct string parsing
                                debug_log("ALL JSON DECODING METHODS FAILED", ['json_error' => json_last_error_msg()]);
                                debug_log("ATTEMPTING DIRECT STRING PARSING");
                                
                                // Check if it looks like a JSON array with the pattern we expect
                                if (preg_match('/^\[\s*"(.+?)"\s*(?:,\s*"(.+?)"\s*)*\]$/', $narrativeData['photo_paths'])) {
                                    // Remove brackets, split by commas, and clean up each path
                                    $strippedString = trim($narrativeData['photo_paths'], '[]');
                                    $pathParts = explode(',', $strippedString);
                                    
                                    foreach ($pathParts as $part) {
                                        $cleanPart = trim($part, ' "\'');
                                        // Replace escaped slashes
                                        $cleanPart = str_replace('\/', '/', $cleanPart);
                                        if (!empty($cleanPart)) {
                                            $photoPathsArray[] = $cleanPart;
                                        }
                                    }
                                    debug_log("EXTRACTED PATHS USING DIRECT STRING PARSING", $photoPathsArray);
                                } else {
                                    debug_log("STRING DOES NOT APPEAR TO BE A JSON ARRAY", $narrativeData['photo_paths']);
                                }
                            }
                        }
                    }
                } else if (is_array($narrativeData['photo_paths'])) {
                    $photoPathsArray = $narrativeData['photo_paths'];
                    debug_log("photo_paths IS ALREADY AN ARRAY", $photoPathsArray);
                } else {
                    debug_log("photo_paths IS NEITHER STRING NOR ARRAY", ['type' => gettype($narrativeData['photo_paths'])]);
                }
            } else {
                debug_log("photo_paths FIELD NOT FOUND OR EMPTY");
            }
            
            // Then check photo_path field (single string or legacy JSON array)
            if (isset($narrativeData['photo_path']) && !empty($narrativeData['photo_path'])) {
                debug_log("FOUND photo_path FIELD", $narrativeData['photo_path']);
                if (is_string($narrativeData['photo_path'])) {
                    // Check if it's a JSON array
                    if (substr($narrativeData['photo_path'], 0, 1) === '[') {
                        debug_log("photo_path APPEARS TO BE JSON ARRAY, ATTEMPTING DECODE");
                        
                        // SPECIAL HANDLING FOR ESCAPED SLASHES
                        // Replace escaped slashes with a temporary marker
                        $tempMarker = "##SLASH##";
                        $processedString = str_replace('\/', $tempMarker, $narrativeData['photo_path']);
                        
                        // Try to decode with the temp marker approach
                        $decodedPath = json_decode($processedString, true);
                        
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedPath)) {
                            // Restore slashes
                            foreach ($decodedPath as &$path) {
                                $path = str_replace($tempMarker, '/', $path);
                            }
                            
                            foreach ($decodedPath as $path) {
                                if (!in_array($path, $photoPathsArray)) {
                                    $photoPathsArray[] = $path;
                                    debug_log("ADDED PATH FROM JSON ARRAY (SPECIAL PROCESSING)", $path);
                                }
                            }
                        } else {
                            // If special processing fails, try standard decode
                            $decodedPath = json_decode($narrativeData['photo_path'], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedPath)) {
                                foreach ($decodedPath as $path) {
                                    if (!in_array($path, $photoPathsArray)) {
                                        $photoPathsArray[] = $path;
                                        debug_log("ADDED PATH FROM JSON ARRAY", $path);
                                    }
                                }
                            } else {
                                // Try with stripslashes as a last resort
                                $unescaped = stripslashes($narrativeData['photo_path']);
                                $decodedPath = json_decode($unescaped, true);
                                
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedPath)) {
                                    foreach ($decodedPath as $path) {
                                        if (!in_array($path, $photoPathsArray)) {
                                            $photoPathsArray[] = $path;
                                            debug_log("ADDED PATH FROM JSON ARRAY AFTER stripslashes", $path);
                                        }
                                    }
                                } else {
                                    debug_log("FAILED TO DECODE photo_path JSON", ['json_error' => json_last_error_msg()]);
                                    
                                    // If all methods fail, try direct string parsing as last resort
                                    if (preg_match('/^\[\s*"(.+?)"\s*(?:,\s*"(.+?)"\s*)*\]$/', $narrativeData['photo_path'])) {
                                        // Remove brackets, split by commas, and clean up each path
                                        $strippedString = trim($narrativeData['photo_path'], '[]');
                                        $pathParts = explode(',', $strippedString);
                                        
                                        foreach ($pathParts as $part) {
                                            $cleanPart = trim($part, ' "\'');
                                            // Replace escaped slashes
                                            $cleanPart = str_replace('\/', '/', $cleanPart);
                                            if (!empty($cleanPart) && !in_array($cleanPart, $photoPathsArray)) {
                                                $photoPathsArray[] = $cleanPart;
                                                debug_log("EXTRACTED PATH USING DIRECT STRING PARSING", $cleanPart);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else if (!in_array($narrativeData['photo_path'], $photoPathsArray)) {
                        // It's a single path, add if not already in array
                        $photoPathsArray[] = $narrativeData['photo_path'];
                        debug_log("ADDED SINGLE photo_path", $narrativeData['photo_path']);
                    } else {
                        debug_log("SINGLE photo_path ALREADY EXISTS IN ARRAY, SKIPPING", $narrativeData['photo_path']);
                    }
                } else {
                    debug_log("photo_path IS NOT A STRING", ['type' => gettype($narrativeData['photo_path'])]);
                }
            } else {
                debug_log("photo_path FIELD NOT FOUND OR EMPTY");
            }
            
            // Process image paths to ensure they're properly formatted
            debug_log("BEFORE PATH CORRECTION, PATHS ARRAY", $photoPathsArray);
            foreach ($photoPathsArray as $key => $path) {
                // Skip if empty
                if (empty($path)) {
                    debug_log("EMPTY PATH FOUND, SKIPPING", ['index' => $key]);
                    continue;
                }
                
                // Remove any escape characters that might be in the path
                $cleanPath = stripslashes($path);
                debug_log("PROCESSED PATH", ['original' => $path, 'cleaned' => $cleanPath]);
                
                // If it already has a valid prefix, make minor adjustments if needed
                if (strpos($cleanPath, 'photos/') === 0 || 
                    strpos($cleanPath, '../photos/') === 0 || 
                    strpos($cleanPath, 'uploads/') === 0 || 
                    strpos($cleanPath, '../uploads/') === 0 || 
                    strpos($cleanPath, 'http') === 0) {
                    
                    debug_log("PATH HAS VALID PREFIX", $cleanPath);
                    
                    // Fix potentially doubled slashes
                    $oldPath = $cleanPath;
                    $cleanPath = str_replace('//', '/', $cleanPath);
                    if ($oldPath !== $cleanPath) {
                        debug_log("FIXED DOUBLED SLASHES", ['from' => $oldPath, 'to' => $cleanPath]);
                    }
                    
                    if (strpos($cleanPath, 'http:/') === 0 && strpos($cleanPath, 'http://') !== 0) {
                        $oldPath = $cleanPath;
                        $cleanPath = str_replace('http:/', 'http://', $cleanPath);
                        debug_log("FIXED HTTP URL", ['from' => $oldPath, 'to' => $cleanPath]);
                    }
                    if (strpos($cleanPath, 'https:/') === 0 && strpos($cleanPath, 'https://') !== 0) {
                        $oldPath = $cleanPath;
                        $cleanPath = str_replace('https:/', 'https://', $cleanPath);
                        debug_log("FIXED HTTPS URL", ['from' => $oldPath, 'to' => $cleanPath]);
                    }
                    
                    $photoPathsArray[$key] = $cleanPath;
                    debug_log("FINAL CLEAN PATH", $cleanPath);
                    continue;
                }
                
                // For simple filename only, add the photos/ prefix
                $photoPathsArray[$key] = 'photos/' . $cleanPath;
                debug_log("ADDED photos/ PREFIX", ['from' => $cleanPath, 'to' => $photoPathsArray[$key]]);
            }
            
            // If no photos were found in the standard fields, directly query the database
            // to see if there are any photos related to this activity
            if (empty($photoPathsArray) && isset($ppasFormId)) {
                debug_log("NO PHOTOS FOUND IN STANDARD FIELDS, TRYING DIRECT DATABASE QUERY");
                try {
                    // First check if a photos table exists
                    $tables = ['narrative_photos', 'activity_photos', 'photos', 'images', 'uploads'];
                    debug_log("CHECKING POTENTIAL PHOTO TABLES", $tables);
                    
                    foreach ($tables as $table) {
                        try {
                            $stmt = $conn->prepare("SHOW TABLES LIKE :table");
                            $stmt->execute([':table' => $table]);
                            $tableExists = $stmt->rowCount() > 0;
                            debug_log("TABLE CHECK", ['table' => $table, 'exists' => $tableExists]);
                            
                            if ($tableExists) {
                                debug_log("FOUND POTENTIAL PHOTOS TABLE", $table);
                                
                                // Get all columns
                                $columnsResult = $conn->query("DESCRIBE `$table`");
                                $columns = $columnsResult->fetchAll(PDO::FETCH_COLUMN);
                                debug_log("COLUMNS IN TABLE $table", $columns);
                                
                                // Now check if this table has a ppas_form_id column or similar
                                $foreignKeyColumns = ['ppas_form_id', 'ppas_id', 'form_id', 'activity_id', 'reference_id'];
                                debug_log("LOOKING FOR FOREIGN KEY COLUMNS", $foreignKeyColumns);
                                
                                $foreignKeyColumn = null;
                                foreach ($foreignKeyColumns as $column) {
                                    if (in_array($column, $columns)) {
                                        $foreignKeyColumn = $column;
                                        debug_log("FOUND FOREIGN KEY COLUMN", $column);
                                        break;
                                    }
                                }
                                
                                // Check if we have a photo/path/file column
                                $photoColumns = ['photo_path', 'path', 'filename', 'file', 'image', 'photo'];
                                debug_log("LOOKING FOR PHOTO PATH COLUMNS", $photoColumns);
                                
                                $photoColumn = null;
                                foreach ($photoColumns as $column) {
                                    if (in_array($column, $columns)) {
                                        $photoColumn = $column;
                                        debug_log("FOUND PHOTO PATH COLUMN", $column);
                                        break;
                                    }
                                }
                                
                                // If we have both columns, try to fetch photos
                                if ($foreignKeyColumn && $photoColumn) {
                                    debug_log("TRYING TO FETCH PHOTOS FROM TABLE", ['table' => $table, 'foreign_key' => $foreignKeyColumn, 'photo_column' => $photoColumn, 'id' => $ppasFormId]);
                                    $stmt = $conn->prepare("SELECT `$photoColumn` FROM `$table` WHERE `$foreignKeyColumn` = :id");
                                    $stmt->execute([':id' => $ppasFormId]);
                                    
                                    $photoCount = 0;
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        if (!empty($row[$photoColumn])) {
                                            // Process the path
                                            $path = $row[$photoColumn];
                                            debug_log("FOUND RAW PHOTO PATH", $path);
                                            
                                            // If it doesn't already have a prefix, add photos/
                                            if (strpos($path, 'photos/') !== 0 && 
                                                strpos($path, '../photos/') !== 0 && 
                                                strpos($path, 'uploads/') !== 0 && 
                                                strpos($path, '../uploads/') !== 0 && 
                                                strpos($path, 'http') !== 0) {
                                                $oldPath = $path;
                                                $path = 'photos/' . $path;
                                                debug_log("ADDED PHOTOS PREFIX", ['from' => $oldPath, 'to' => $path]);
                                            }
                                            
                                            // Add to our array if not already there
                                            if (!in_array($path, $photoPathsArray)) {
                                                $photoPathsArray[] = $path;
                                                $photoCount++;
                                                debug_log("ADDED PHOTO TO ARRAY", ['path' => $path, 'total_so_far' => count($photoPathsArray)]);
                                            } else {
                                                debug_log("PHOTO ALREADY IN ARRAY, SKIPPING", $path);
                                            }
                                        } else {
                                            debug_log("EMPTY PHOTO PATH VALUE FOUND IN $table");
                                        }
                                    }
                                    debug_log("PHOTO FETCH RESULTS", ['table' => $table, 'photos_found' => $photoCount]);
                                } else {
                                    debug_log("MISSING REQUIRED COLUMNS", ['table' => $table, 'has_foreign_key' => (bool)$foreignKeyColumn, 'has_photo_column' => (bool)$photoColumn]);
                                }
                            }
                        } catch (Exception $e) {
                            debug_log("ERROR CHECKING TABLE $table", $e->getMessage());
                        }
                    }
                } catch (Exception $e) {
                    debug_log("ERROR DURING ADDITIONAL PHOTO SEARCH", $e->getMessage());
                }
            }
            
            // Check file existence and accessibility
            debug_log("CHECKING FILE EXISTENCE FOR " . count($photoPathsArray) . " PHOTOS");
            $accessiblePhotos = [];
            $filesFound = false;
            
            // Define all possible root directories to check for photos
            $possibleRoots = [
                './',
                '../',
                '../../',
                '../../../',
                './gad_narrative/',
                '../gad_narrative/',
                './photos/',
                '../photos/',
                '../../photos/',
                '../narrative_data_entry/'
            ];
            
            debug_log("CHECKING IN POSSIBLE ROOT DIRECTORIES", $possibleRoots);
            
            // First, look for photos in each possible root directory
            foreach ($photoPathsArray as $index => $path) {
                debug_log("CHECKING PHOTO #" . ($index + 1), $path);
                
                // Clean any path that might have multiple slashes or weird formatting
                $cleanPath = preg_replace('#/{2,}#', '/', $path);
                $cleanPath = str_replace('\/', '/', $cleanPath);
                
                // Remove any 'photos/' prefix temporarily to try different combinations
                $baseFilename = $cleanPath;
                if (strpos($cleanPath, 'photos/') === 0) {
                    $baseFilename = substr($cleanPath, 7); // Remove 'photos/' prefix
                }
                
                debug_log("NORMALIZED PATH", ['original' => $path, 'cleaned' => $cleanPath, 'base_filename' => $baseFilename]);
                
                // First try the original path
                $fileExists = false;
                $foundPath = null;
                
                // Try the original path first
                if (file_exists($cleanPath)) {
                    $fileExists = true;
                    $foundPath = $cleanPath;
                    debug_log("FILE EXISTS AT ORIGINAL PATH", $cleanPath);
                } else {
                    // Try all combinations of root directories and the filename
                    foreach ($possibleRoots as $root) {
                        // Try with photos/ directory
                        $testPath = $root . 'photos/' . $baseFilename;
                        debug_log("TRYING PATH", $testPath);
                        
                        if (file_exists($testPath)) {
                            $fileExists = true;
                            $foundPath = $testPath;
                            debug_log("FILE EXISTS AT", $testPath);
                            break;
                        }
                        
                        // Also try without photos/ prefix if it's not in the base filename
                        if (strpos($baseFilename, 'photos/') !== 0) {
                            $testPath = $root . $baseFilename;
                            debug_log("TRYING PATH WITHOUT PHOTOS PREFIX", $testPath);
                            
                            if (file_exists($testPath)) {
                                $fileExists = true;
                                $foundPath = $testPath;
                                debug_log("FILE EXISTS AT", $testPath);
                                break;
                            }
                        }
                    }
                }
                
                if ($fileExists) {
                    $filesFound = true;
                    
                    // Get file details for debugging
                    try {
                        $filesize = filesize($foundPath);
                        $filetype = mime_content_type($foundPath);
                        debug_log("FILE DETAILS", ['size' => $filesize, 'type' => $filetype, 'path' => $foundPath]);
                        
                        // For web access, use a path relative to the web root
                        // This is just a guess - might need to be adjusted based on your setup
                        $accessiblePath = str_replace('../', '', $cleanPath);
                        if (strpos($accessiblePath, 'photos/') !== 0 && strpos($foundPath, 'photos/') !== false) {
                            // Ensure photos/ prefix is included if it's in the found path
                            $accessiblePath = 'photos/' . $accessiblePath;
                        }
                        
                        // Add to our list of accessible photos
                        $accessiblePhotos[] = $accessiblePath;
                        debug_log("ADDED TO ACCESSIBLE PHOTOS", ['index' => count($accessiblePhotos) - 1, 'path' => $accessiblePath]);
                    } catch (Exception $e) {
                        debug_log("ERROR GETTING FILE DETAILS", $e->getMessage());
                        // Still add the path even if details can't be retrieved
                        $accessiblePhotos[] = $cleanPath;
                    }
                } else {
                    debug_log("FILE NOT FOUND FOR ANY PATH COMBINATION", $cleanPath);
                    
                    // Still include the path in case it's accessible via URL even if not found locally
                    // This can help if the file is served from another location
                    $accessiblePhotos[] = $cleanPath;
                    debug_log("ADDED UNFOUND PATH TO ACCESSIBLE PHOTOS ANYWAY", $cleanPath);
                }
            }
            
            // Additional emergency fallback: directly scan the photos directory if we found no images
            if (!$filesFound && count($photoPathsArray) === 0) {
                debug_log("NO IMAGES FOUND - TRYING EMERGENCY DIRECTORY SCAN");
                
                $possibleDirectories = [
                    'photos',
                    '../photos',
                    '../../photos',
                    '../narrative_data_entry/photos'
                ];
                
                foreach ($possibleDirectories as $dir) {
                    if (is_dir($dir)) {
                        debug_log("FOUND PHOTOS DIRECTORY", $dir);
                        
                        try {
                            $files = scandir($dir);
                            debug_log("DIRECTORY CONTENTS", ['dir' => $dir, 'files' => $files]);
                            
                            // Filter to just image files
                            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            foreach ($files as $file) {
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                if (in_array($ext, $imageExtensions) && $file !== '.' && $file !== '..') {
                                    $photoPath = "photos/" . $file;
                                    $accessiblePhotos[] = $photoPath;
                                    $filesFound = true;
                                    debug_log("FOUND IMAGE IN DIRECTORY SCAN", $photoPath);
                                }
                            }
                            
                            if ($filesFound) {
                                debug_log("FOUND IMAGES IN DIRECTORY SCAN", ['count' => count($accessiblePhotos)]);
                                break; // Stop once we've found images
                            }
                        } catch (Exception $e) {
                            debug_log("ERROR SCANNING DIRECTORY", ['dir' => $dir, 'error' => $e->getMessage()]);
                        }
                    } else {
                        debug_log("DIRECTORY NOT FOUND", $dir);
                    }
                }
            }
            
            // If we still have no images, try using ppas_form_id to query more aggressively
            if (!$filesFound && isset($ppasFormId)) {
                debug_log("LAST RESORT: QUERYING ALL TABLES FOR IMAGES RELATED TO ppas_form_id: " . $ppasFormId);
                
                try {
                    // Get all tables in the database
                    $tables = [];
                    $stmt = $conn->query("SHOW TABLES");
                    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                        $tables[] = $row[0];
                    }
                    
                    debug_log("ALL DATABASE TABLES", $tables);
                    
                    // Search for any tables that might contain image data
                    foreach ($tables as $table) {
                        try {
                            $columns = $conn->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_COLUMN);
                            
                            // Look for columns that might contain image paths
                            $imageColumns = array_filter($columns, function($col) {
                                return (
                                    strpos(strtolower($col), 'photo') !== false || 
                                    strpos(strtolower($col), 'image') !== false || 
                                    strpos(strtolower($col), 'file') !== false || 
                                    strpos(strtolower($col), 'path') !== false
                                );
                            });
                            
                            if (!empty($imageColumns)) {
                                debug_log("TABLE WITH POTENTIAL IMAGE COLUMNS", ['table' => $table, 'columns' => $imageColumns]);
                                
                                // Try to find a linking column to ppas_form_id
                                $linkColumns = array_filter($columns, function($col) {
                                    return (
                                        strpos(strtolower($col), 'id') !== false || 
                                        strpos(strtolower($col), 'form') !== false || 
                                        strpos(strtolower($col), 'ppas') !== false || 
                                        strpos(strtolower($col), 'reference') !== false
                                    );
                                });
                                
                                if (!empty($linkColumns)) {
                                    debug_log("FOUND POTENTIAL LINK COLUMNS", ['table' => $table, 'columns' => $linkColumns]);
                                    
                                    // Try each link column
                                    foreach ($linkColumns as $linkCol) {
                                        // Try each image column
                                        foreach ($imageColumns as $imgCol) {
                                            try {
                                                $query = "SELECT `$imgCol` FROM `$table` WHERE `$linkCol` = :id";
                                                $stmt = $conn->prepare($query);
                                                $stmt->execute([':id' => $ppasFormId]);
                                                
                                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    if (!empty($row[$imgCol])) {
                                                        $imgPath = $row[$imgCol];
                                                        debug_log("FOUND IMAGE PATH IN DATABASE", ['table' => $table, 'column' => $imgCol, 'path' => $imgPath]);
                                                        
                                                        // Process the image path
                                                        if (is_string($imgPath)) {
                                                            // Try to decode if it's JSON
                                                            if (substr($imgPath, 0, 1) === '[') {
                                                                $decodedPaths = json_decode(stripslashes($imgPath), true);
                                                                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedPaths)) {
                                                                    foreach ($decodedPaths as $p) {
                                                                        $cleanPath = str_replace('\/', '/', $p);
                                                                        if (!in_array($cleanPath, $accessiblePhotos)) {
                                                                            $accessiblePhotos[] = $cleanPath;
                                                                            $filesFound = true;
                                                                        }
                                                                    }
                                                                } else {
                                                                    // If not valid JSON, just add the path
                                                                    $cleanPath = str_replace('\/', '/', $imgPath);
                                                                    if (!in_array($cleanPath, $accessiblePhotos)) {
                                                                        $accessiblePhotos[] = $cleanPath;
                                                                        $filesFound = true;
                                                                    }
                                                                }
                                                            } else {
                                                                // Single path
                                                                $cleanPath = str_replace('\/', '/', $imgPath);
                                                                if (!in_array($cleanPath, $accessiblePhotos)) {
                                                                    $accessiblePhotos[] = $cleanPath;
                                                                    $filesFound = true;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            } catch (Exception $e) {
                                                debug_log("ERROR QUERYING TABLE", ['table' => $table, 'error' => $e->getMessage()]);
                                            }
                                        }
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            debug_log("ERROR EXAMINING TABLE", ['table' => $table, 'error' => $e->getMessage()]);
                        }
                    }
                } catch (Exception $e) {
                    debug_log("ERROR IN LAST RESORT IMAGE SEARCH", $e->getMessage());
                }
            }
            
            // Final check - if we still have no images, add a debugging flag to the response
            if (empty($accessiblePhotos)) {
                debug_log("CRITICAL: NO IMAGES FOUND BY ANY METHOD");
                $responseData['no_images_found'] = true;
                $responseData['image_debug_info'] = [
                    'photo_path_field_exists' => isset($narrativeData['photo_path']),
                    'photo_paths_field_exists' => isset($narrativeData['photo_paths']),
                    'photo_path_value' => $narrativeData['photo_path'] ?? 'not set',
                    'photo_paths_value' => $narrativeData['photo_paths'] ?? 'not set'
                ];
            } else {
                // We have some images
                $responseData['no_images_found'] = false;
                debug_log("FINAL IMAGE COUNT", count($accessiblePhotos));
            }
            
            // Assign the processed photo paths to the response
            $responseData['activity_images'] = $accessiblePhotos;
            debug_log("FINAL IMAGE PATHS ARRAY", ['count' => count($accessiblePhotos), 'paths' => $accessiblePhotos]);
        }
    } catch (Exception $e) {
        debug_log("ERROR checking for matching narrative data", $e->getMessage());
    }
    
    // Ensure proper handling of custom JSON fields - REMOVED DATA PROCESSING
    
    // Debug the final response data structure
    debug_log("Final response fields", array_keys($responseData));
    
    // Add special flags to prevent client-side overrides
    $responseData['disable_client_processing'] = true;
    $responseData['disable_ratings_transform'] = true;
    $responseData['disable_agenda_transform'] = true;
    $responseData['disable_objectives_transform'] = true;
    $responseData['use_raw_data_only'] = true;
    
    // Return the combined data
    $finalResponse = [
        'status' => 'success',
        'data' => $responseData,
        'ppas_data_found' => true,
        'narrative_data_found' => !empty($narrativeData),
        'debug_enabled' => $DEBUG_MODE,
        // Add the original narrative data as separate field to prevent overrides
        'original_narrative_data' => $narrativeData,
        // Add a flag to tell the client not to override the data
        'preserve_original_data' => true,
        // Prevent any client-side transformations
        'no_transform' => true,
        'disable_client_processing' => true
    ];
    
    debug_log("Sending final response with override prevention flags");
    echo json_encode($finalResponse);

} catch (Exception $e) {
    // Log the error and return an error response
    debug_log("CRITICAL ERROR fetching data", $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching narrative data: ' . $e->getMessage(),
        'code' => 'SERVER_ERROR',
        'debug_trace' => $DEBUG_MODE ? $e->getTraceAsString() : null
    ]);
} 