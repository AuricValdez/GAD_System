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
    
    // Add campus filter if provided
    if ($campus) {
        $sql .= " AND campus = :campus";
        $params[':campus'] = $campus;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $ppasData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ppasData) {
        // If no data found, return an error
        echo json_encode([
            'status' => 'error',
            'message' => 'No PPAS form data found for the specified ID',
            'code' => 'NOT_FOUND'
        ]);
        exit();
    }
    
    // Log the retrieved PPAS data for debugging
    error_log("Retrieved PPAS data for ID $ppasFormId: " . json_encode($ppasData));
    
    // Explicitly get key fields from ppas_forms
    $ppasActivity = $ppasData['activity'] ?? 'Untitled Activity';
    $ppasYear = $ppasData['year'];

    // Initialize the response data structure with PPAS data
    $responseData = [
        'id' => $ppasData['id'],
        'ppas_form_id' => $ppasData['id'],
        'activity_title' => $ppasActivity, // Always use activity from ppas_forms
        'implementing_office' => $ppasData['implementing_office'] ?? null,
        'campus' => $ppasData['campus'],
        'year' => $ppasYear, // Always use year from ppas_forms
        'date_venue' => [
            'date' => $ppasData['date'] ?? null,
            'venue' => $ppasData['venue'] ?? null
        ],
        'sdg' => $ppasData['sdgs'] ?? null,
        'location' => $ppasData['location'] ?? null,
        'duration' => [
            'start_date' => $ppasData['start_date'] ?? null,
            'end_date' => $ppasData['end_date'] ?? null,
            'start_time' => $ppasData['start_time'] ?? null,
            'end_time' => $ppasData['end_time'] ?? null,
            'total_duration_hours' => $ppasData['total_duration_hours'] ?? null
        ],
        'beneficiary_data' => [
            'students_male' => $ppasData['students_male'] ?? 0,
            'students_female' => $ppasData['students_female'] ?? 0,
            'faculty_male' => $ppasData['faculty_male'] ?? 0,
            'faculty_female' => $ppasData['faculty_female'] ?? 0,
            'total_internal_male' => $ppasData['total_internal_male'] ?? 0,
            'total_internal_female' => $ppasData['total_internal_female'] ?? 0,
            'external_type' => $ppasData['external_type'] ?? '',
            'external_male' => $ppasData['external_male'] ?? 0,
            'external_female' => $ppasData['external_female'] ?? 0,
            'total_male' => $ppasData['total_male'] ?? 0,
            'total_female' => $ppasData['total_female'] ?? 0,
            'total_beneficiaries' => $ppasData['total_beneficiaries'] ?? 0
        ],
        'project_team' => [
            'project_leaders' => [
                'names' => $ppasData['project_leader'] ?? null,
                'responsibilities' => $ppasData['leader_responsibilities'] ?? null,
                'designation' => $ppasData['project_leader_designation'] ?? 'Project Leader'
            ],
            'assistant_project_leaders' => [
                'names' => $ppasData['assistant_leader'] ?? null,
                'responsibilities' => $ppasData['assistant_responsibilities'] ?? null,
                'designation' => $ppasData['assistant_leader_designation'] ?? 'Assistant Project Leader'
            ],
            'project_staff' => [
                'names' => $ppasData['project_staff'] ?? null,
                'responsibilities' => $ppasData['staff_responsibilities'] ?? null,
                'designation' => $ppasData['project_staff_designation'] ?? 'Project Staff'
            ]
        ],
        'activity_ratings' => null,
        'timeliness_ratings' => null,
        'activity_images' => []
    ];
    
    // Now check if there's a matching narrative entry to supplement the data
    try {
        // Check if ppas_form_id column exists in narrative_entries
        $hasPpasFormId = false;
        try {
            $checkStmt = $conn->prepare("SHOW COLUMNS FROM narrative_entries LIKE 'ppas_form_id'");
            $checkStmt->execute();
            $hasPpasFormId = ($checkStmt->rowCount() > 0);
        } catch (Exception $e) {
            error_log("Error checking for ppas_form_id column: " . $e->getMessage());
        }
        
        $narrativeData = null;
        
        // First try direct match by ppas_form_id if column exists
        if ($hasPpasFormId) {
            $stmt = $conn->prepare("SELECT * FROM narrative_entries WHERE ppas_form_id = :ppas_id LIMIT 1");
            $stmt->execute([':ppas_id' => $ppasFormId]);
            $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($narrativeData) {
                error_log("Found matching narrative entry by ppas_form_id for PPAS ID $ppasFormId");
            }
        }
        
        // If no direct match, try by title similarity
        if (!$narrativeData) {
            error_log("Attempting title-based match between ppas_forms.activity ('$ppasActivity') and narrative_entries.title");
            $stmt = $conn->prepare("SELECT * FROM narrative_entries WHERE title LIKE :title AND campus = :campus LIMIT 1");
            $stmt->execute([
                ':title' => '%' . $ppasActivity . '%',
                ':campus' => $ppasData['campus']
            ]);
            $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($narrativeData) {
                error_log("Found matching narrative entry by title similarity for PPAS activity: $ppasActivity");
            } else {
                error_log("No matching narrative entry found for activity: $ppasActivity");
            }
        }
        
        if ($narrativeData) {
            error_log("Found matching narrative entry for PPAS ID $ppasFormId: " . json_encode($narrativeData));
            
            // Add narrative title to response data for reference
            $responseData['narrative_title'] = $narrativeData['title'] ?? null;
            
            // Supplement PPAS data with narrative data for narrative-specific fields
            if (!empty($narrativeData['background'])) {
                $responseData['background_rationale'] = $narrativeData['background'];
            }
            
            if (!empty($narrativeData['participants'])) {
                $responseData['participants_description'] = $narrativeData['participants'];
            }
            
            if (!empty($narrativeData['topics'])) {
                $responseData['narrative_topics'] = $narrativeData['topics'];
            }
            
            if (!empty($narrativeData['results'])) {
                $responseData['expected_results'] = $narrativeData['results'];
            }
            
            if (!empty($narrativeData['lessons'])) {
                $responseData['lessons_learned'] = $narrativeData['lessons'];
            }
            
            if (!empty($narrativeData['what_worked'])) {
                $responseData['what_worked'] = $narrativeData['what_worked'];
            }
            
            if (!empty($narrativeData['issues'])) {
                $responseData['issues_concerns'] = $narrativeData['issues'];
            }
            
            if (!empty($narrativeData['recommendations'])) {
                $responseData['recommendations'] = $narrativeData['recommendations'];
            }
            
            // Process ratings data if available
            if (isset($narrativeData['activity_ratings']) && !empty($narrativeData['activity_ratings'])) {
                // DIRECTLY use the raw activity ratings data without any processing
                        $responseData['activity_ratings'] = $narrativeData['activity_ratings'];
                error_log("Using RAW activity_ratings as-is: " . (is_string($narrativeData['activity_ratings']) ? $narrativeData['activity_ratings'] : json_encode($narrativeData['activity_ratings'])));
            }
            
            if (isset($narrativeData['timeliness_ratings']) && !empty($narrativeData['timeliness_ratings'])) {
                // DIRECTLY use the raw timeliness ratings data without any processing
                        $responseData['timeliness_ratings'] = $narrativeData['timeliness_ratings'];
                error_log("Using RAW timeliness_ratings as-is: " . (is_string($narrativeData['timeliness_ratings']) ? $narrativeData['timeliness_ratings'] : json_encode($narrativeData['timeliness_ratings'])));
            }
            
            // Try to find images using multiple field names
            $possibleImageFields = ['photo_paths', 'photo_path', 'activity_images', 'images', 'photos'];
            foreach ($possibleImageFields as $field) {
                if (isset($narrativeData[$field]) && !empty($narrativeData[$field])) {
                    try {
                        error_log("Trying to get images from field: $field with value: " . (is_string($narrativeData[$field]) ? $narrativeData[$field] : json_encode($narrativeData[$field])));
                        
                        if (is_string($narrativeData[$field])) {
                            // First see if it's a JSON array
                            if (strpos($narrativeData[$field], '[') === 0) {
                                $images = json_decode($narrativeData[$field], true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($images)) {
                                    $responseData['activity_images'] = $images;
                                    error_log("Successfully decoded $field from JSON array: " . json_encode($responseData['activity_images']));
                                    break;
                                } else {
                                    error_log("JSON decode error for $field: " . json_last_error_msg());
                                }
                            }
                            
                            // If it's a JSON object
                            if (strpos($narrativeData[$field], '{') === 0) {
                                $imagesObj = json_decode($narrativeData[$field], true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($imagesObj)) {
                                    // Convert object to array
                                    $responseData['activity_images'] = array_values($imagesObj);
                                    error_log("Successfully decoded $field from JSON object: " . json_encode($responseData['activity_images']));
                                    break;
                                } else {
                                    error_log("JSON decode error for $field object: " . json_last_error_msg());
                                }
                            }
                            
                            // If it's a comma-separated string
                            if (strpos($narrativeData[$field], ',') !== false) {
                                $responseData['activity_images'] = array_map('trim', explode(',', $narrativeData[$field]));
                                error_log("Split $field by commas: " . json_encode($responseData['activity_images']));
                                break;
                            }
                            
                            // Treat as a single path
                            $responseData['activity_images'] = [$narrativeData[$field]];
                            error_log("Using $field as single image path: " . $narrativeData[$field]);
                            break;
                        } else if (is_array($narrativeData[$field])) {
                            $responseData['activity_images'] = $narrativeData[$field];
                            error_log("Using $field array directly: " . json_encode($responseData['activity_images']));
                            break;
                        }
                    } catch (Exception $e) {
                        error_log("Error processing $field: " . $e->getMessage());
                    }
                }
            }
            
            // If still no images found, check for any field with 'photo' or 'image' in the name as a last resort
            if (empty($responseData['activity_images'])) {
                foreach ($narrativeData as $key => $value) {
                    if ((strpos($key, 'photo') !== false || strpos($key, 'image') !== false) && !empty($value)) {
                        try {
                            error_log("Last resort: trying to get images from field: $key");
                            if (is_string($value)) {
                                // Try to decode JSON
                                $images = json_decode($value, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($images)) {
                                    $responseData['activity_images'] = $images;
                                } else {
                                    // Use as single image path
                                    $responseData['activity_images'] = [$value];
                                }
                            } else if (is_array($value)) {
                                $responseData['activity_images'] = $value;
                            }
                            if (!empty($responseData['activity_images'])) {
                                error_log("Found images in field $key: " . json_encode($responseData['activity_images']));
                                break;
                            }
                        } catch (Exception $e) {
                            error_log("Error processing $key as image field: " . $e->getMessage());
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error checking for matching narrative entry: " . $e->getMessage());
    }
    
    // Set default ratings structure if not available from narrative
    if (empty($responseData['activity_ratings'])) {
        // Log the fact that we're not using real data
        error_log("WARNING: No real activity_ratings data found, response will be null");
        $responseData['activity_ratings'] = null;
    }
    
    if (empty($responseData['timeliness_ratings'])) {
        // Log the fact that we're not using real data
        error_log("WARNING: No real timeliness_ratings data found, response will be null");
        $responseData['timeliness_ratings'] = null;
    }
    
    // Return the combined data
    echo json_encode([
        'status' => 'success',
        'data' => $responseData,
        'ppas_data_found' => true,
        'narrative_data_found' => !empty($narrativeData)
    ]);

} catch (Exception $e) {
    // Log the error and return an error response
    error_log("Error fetching narrative data: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching narrative data: ' . $e->getMessage(),
        'code' => 'SERVER_ERROR'
    ]);
} 