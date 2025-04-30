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
    
    // Query to fetch narrative data
    $sql = "SELECT * FROM narrative WHERE ppas_form_id = :ppas_form_id";
    $params = [':ppas_form_id' => $ppasFormId];
    
    // Add campus filter if provided
    if ($campus) {
        $sql .= " AND campus = :campus";
        $params[':campus'] = $campus;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$narrativeData) {
        // If no data found, return an error
        echo json_encode([
            'status' => 'error',
            'message' => 'No narrative data found for the specified PPAS form',
            'code' => 'NOT_FOUND'
        ]);
        exit();
    }
    
    // If we have activity_images as a string, try to decode it
    if (isset($narrativeData['activity_images']) && !empty($narrativeData['activity_images'])) {
        try {
            $narrativeData['activity_images'] = json_decode($narrativeData['activity_images'], true);
        } catch (Exception $e) {
            // If decoding fails, keep it as is
            error_log("Error decoding activity_images JSON: " . $e->getMessage());
        }
    }
    
    // If PPAS table exists, retrieve additional data
    $ppasData = [];
    if ($ppasTable && $ppasFormId) {
        try {
            $stmt = $conn->prepare("SELECT * FROM `$ppasTable` WHERE id = :id");
            $stmt->execute([':id' => $ppasFormId]);
            $ppasData = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Successfully fetched PPAS data for ID: $ppasFormId");
            
            if ($ppasData) {
                // Add relevant PPAS data to the narrative data
                $narrativeData['activity_title'] = $ppasData['activity'] ?? null;
                $narrativeData['date_venue'] = [
                    'date' => $ppasData['date'] ?? null,
                    'venue' => $ppasData['venue'] ?? null
                ];
                $narrativeData['sdg'] = $ppasData['sdgs'] ?? null;
                $narrativeData['location'] = $ppasData['location'] ?? null;
                $narrativeData['duration'] = [
                    'start_date' => $ppasData['start_date'] ?? null,
                    'end_date' => $ppasData['end_date'] ?? null,
                    'start_time' => $ppasData['start_time'] ?? null,
                    'end_time' => $ppasData['end_time'] ?? null,
                    'total_duration_hours' => $ppasData['total_duration_hours'] ?? null
                ];
                $narrativeData['beneficiary_data'] = [
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
                ];
                
                // Enhanced project team data with more details from the PPAS form
                $narrativeData['project_team'] = [
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
                ];
                
                // Extension type data
                $narrativeData['extension_type'] = [
                    'inclusive_social_innovation' => $narrativeData['inclusive_social_innovation'] ?? 0,
                    'livelihood' => $narrativeData['livelihood'] ?? 0,
                    'environment' => $narrativeData['environment'] ?? 0,
                    'smart_analytics' => $narrativeData['smart_analytics'] ?? 0,
                    'adopt_barangay' => $narrativeData['adopt_barangay'] ?? 0,
                    'community_outreach' => $narrativeData['community_outreach'] ?? 0,
                    'technical_vocational' => $narrativeData['technical_vocational'] ?? 0,
                    'technology_transfer' => $narrativeData['technology_transfer'] ?? 0,
                    'technical_assistance' => $narrativeData['technical_assistance'] ?? 0,
                    'social_development' => $narrativeData['social_development'] ?? 0,
                    'gender_development' => $narrativeData['gender_development'] ?? 0,
                    'disaster_risk' => $narrativeData['disaster_risk'] ?? 0
                ];
                
                // Get evaluation survey results
                try {
                    // Get activity ratings from survey table
                    $sql = "SELECT rating, participant_type, COUNT(*) as count 
                            FROM survey_responses 
                            WHERE ppas_form_id = :ppas_form_id AND question_type = 'activity' 
                            GROUP BY rating, participant_type";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([':ppas_form_id' => $ppasFormId]);
                    $activityRatings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Format ratings into a structured object
                    $formattedActivityRatings = [
                        'excellent' => ['batstateu' => 0, 'other' => 0],
                        'very_satisfactory' => ['batstateu' => 0, 'other' => 0],
                        'satisfactory' => ['batstateu' => 0, 'other' => 0],
                        'fair' => ['batstateu' => 0, 'other' => 0],
                        'poor' => ['batstateu' => 0, 'other' => 0]
                    ];
                    
                    foreach ($activityRatings as $rating) {
                        $ratingKey = strtolower(str_replace(' ', '_', $rating['rating']));
                        $participantType = strtolower($rating['participant_type']);
                        $count = (int)$rating['count'];
                        
                        if (isset($formattedActivityRatings[$ratingKey])) {
                            if ($participantType === 'batstateu') {
                                $formattedActivityRatings[$ratingKey]['batstateu'] = $count;
                            } else {
                                $formattedActivityRatings[$ratingKey]['other'] = $count;
                            }
                        }
                    }
                    
                    $narrativeData['activity_ratings'] = $formattedActivityRatings;
                    
                    // Get timeliness ratings from survey table
                    $sql = "SELECT rating, participant_type, COUNT(*) as count 
                            FROM survey_responses 
                            WHERE ppas_form_id = :ppas_form_id AND question_type = 'timeliness' 
                            GROUP BY rating, participant_type";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([':ppas_form_id' => $ppasFormId]);
                    $timelinessRatings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Format timeliness ratings into a structured object
                    $formattedTimelinessRatings = [
                        'excellent' => ['batstateu' => 0, 'other' => 0],
                        'very_satisfactory' => ['batstateu' => 0, 'other' => 0],
                        'satisfactory' => ['batstateu' => 0, 'other' => 0],
                        'fair' => ['batstateu' => 0, 'other' => 0],
                        'poor' => ['batstateu' => 0, 'other' => 0]
                    ];
                    
                    foreach ($timelinessRatings as $rating) {
                        $ratingKey = strtolower(str_replace(' ', '_', $rating['rating']));
                        $participantType = strtolower($rating['participant_type']);
                        $count = (int)$rating['count'];
                        
                        if (isset($formattedTimelinessRatings[$ratingKey])) {
                            if ($participantType === 'batstateu') {
                                $formattedTimelinessRatings[$ratingKey]['batstateu'] = $count;
                            } else {
                                $formattedTimelinessRatings[$ratingKey]['other'] = $count;
                            }
                        }
                    }
                    
                    $narrativeData['timeliness_ratings'] = $formattedTimelinessRatings;
                } catch (Exception $e) {
                    error_log("Error fetching survey data: " . $e->getMessage());
                    // Set default empty ratings structure if query fails
                    $narrativeData['activity_ratings'] = [
                        'excellent' => ['batstateu' => 0, 'other' => 0],
                        'very_satisfactory' => ['batstateu' => 0, 'other' => 0],
                        'satisfactory' => ['batstateu' => 0, 'other' => 0],
                        'fair' => ['batstateu' => 0, 'other' => 0],
                        'poor' => ['batstateu' => 0, 'other' => 0]
                    ];
                    $narrativeData['timeliness_ratings'] = [
                        'excellent' => ['batstateu' => 0, 'other' => 0],
                        'very_satisfactory' => ['batstateu' => 0, 'other' => 0],
                        'satisfactory' => ['batstateu' => 0, 'other' => 0],
                        'fair' => ['batstateu' => 0, 'other' => 0],
                        'poor' => ['batstateu' => 0, 'other' => 0]
                    ];
                }
                
                // Add any other relevant fields from PPAS table
            } else {
                error_log("No PPAS data found for ID: $ppasFormId");
            }
        } catch (Exception $e) {
            error_log("Error retrieving PPAS data: " . $e->getMessage());
        }
    }
    
    // Get personnel data for the PPAS form
    try {
        $sql = "
            SELECT 
                pp.personnel_id,
                pp.role,
                p.name,
                p.gender,
                p.academic_rank
            FROM ppas_personnel pp 
            JOIN personnel p ON pp.personnel_id = p.id
            WHERE pp.ppas_form_id = :ppas_form_id
            ORDER BY pp.role, p.name
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':ppas_form_id' => $ppasFormId]);
        $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group personnel by role
        $personnel_by_role = [
            'project_leaders' => [],
            'assistant_project_leaders' => [],
            'project_staff' => []
        ];
        
        foreach ($personnel as $person) {
            if ($person['role'] == 'Project Leader') {
                $personnel_by_role['project_leaders'][] = $person;
            } elseif ($person['role'] == 'Assistant Project Leader') {
                $personnel_by_role['assistant_project_leaders'][] = $person;
            } elseif ($person['role'] == 'Staff' || $person['role'] == 'Other Internal Participants') {
                $personnel_by_role['project_staff'][] = $person;
            }
        }
        
        // Add personnel data to narrative data
        $narrativeData['personnel'] = $personnel_by_role;
    } catch (Exception $e) {
        error_log("Error fetching personnel data: " . $e->getMessage());
        // Don't return error - just log it and continue without personnel data
    }
    
    // Instead of including the getNarrativeData function, define objectives directly
    // to avoid circular dependencies
    try {
        // Directly query the gad_proposals table for objectives
        $sql_objectives = "SELECT general_objectives, specific_objectives FROM gad_proposals WHERE ppas_form_id = :ppas_form_id";
        $stmt_objectives = $conn->prepare($sql_objectives);
        $stmt_objectives->execute([':ppas_form_id' => $ppasFormId]);
        $objectives_data = $stmt_objectives->fetch(PDO::FETCH_ASSOC);
        
        if ($objectives_data && !empty($objectives_data['general_objectives'])) {
            $narrativeData['general_objectives'] = $objectives_data['general_objectives'];
            
            // Parse specific objectives
            if (!empty($objectives_data['specific_objectives'])) {
                // Try to parse as JSON
                $specific_obj = $objectives_data['specific_objectives'];
                if (is_string($specific_obj)) {
                    $decoded = json_decode($specific_obj, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $narrativeData['specific_objectives'] = $decoded;
                    } else {
                        // Try splitting by newlines
                        if (strpos($specific_obj, "\n") !== false) {
                            $narrativeData['specific_objectives'] = array_filter(explode("\n", $specific_obj));
                        } else if (strpos($specific_obj, ",") !== false) {
                            // Try splitting by commas
                            $narrativeData['specific_objectives'] = array_filter(explode(",", $specific_obj));
                        } else {
                            $narrativeData['specific_objectives'] = [$specific_obj];
                        }
                    }
                } else if (is_array($specific_obj)) {
                    $narrativeData['specific_objectives'] = $specific_obj;
                }
            }
        } else {
            // Try the ppas_forms table as fallback
            $sql_ppas = "SELECT activity_title, objectives, general_objectives FROM `$ppasTable` WHERE id = :id";
            $stmt_ppas = $conn->prepare($sql_ppas);
            $stmt_ppas->execute([':id' => $ppasFormId]);
            $ppas_objectives = $stmt_ppas->fetch(PDO::FETCH_ASSOC);
            
            $title = $ppas_objectives['activity_title'] ?? 'the activity';
            
            // Set defaults based on activity title
            $narrativeData['general_objectives'] = $ppas_objectives['general_objectives'] ?? 
                                                  $ppas_objectives['objectives'] ?? 
                                                  "To successfully implement $title";
            
            $narrativeData['specific_objectives'] = [
                "To achieve the goals of $title",
                "To ensure effective implementation of all activities"
            ];
        }
        
        error_log("Added objectives data to API response - general: " . $narrativeData['general_objectives']);
        if (isset($narrativeData['specific_objectives'])) {
            error_log("Added objectives data to API response - specific: " . json_encode($narrativeData['specific_objectives']));
        }
    } catch (Exception $e) {
        error_log("Error getting objectives data: " . $e->getMessage());
        // Provide fallback values if the function fails
        $narrativeData['general_objectives'] = 'To successfully conduct the activity';
        $narrativeData['specific_objectives'] = ['To implement the activity successfully'];
    }
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'data' => $narrativeData,
        'ppas_data_found' => !empty($ppasData)
    ]);

} catch (Exception $e) {
    error_log("Error in get_narrative.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while retrieving narrative data',
        'error' => $e->getMessage()
    ]);
} 