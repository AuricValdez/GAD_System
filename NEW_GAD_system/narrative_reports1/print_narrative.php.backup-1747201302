<?php
session_start();

// Debug session information
error_log("Session data in ppas.php: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in - redirecting to login");
    header("Location: ../login.php");
    exit();
}

// Check if user is Central or a specific campus user
$isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';

// For non-Central users, their username is their campus
$userCampus = $isCentral ? '' : $_SESSION['username'];

// Store campus in session for consistency
$_SESSION['campus'] = $userCampus;

// Note: There are JavaScript versions of getDefaultRatings() inside <script> tags that should not be removed

/**
 * Returns default ratings structure for cases where no ratings data exists in the database
 * 
 * @return array Default ratings with 1 for BatStateU and 2 for Others in each category
 */
function getDefaultRatings() {
    return [
        'Excellent' => [
            'BatStateU' => 50,
            'Others' => 60
        ],
        'Very Satisfactory' => [
            'BatStateU' => 70,
            'Others' => 80
        ],
        'Satisfactory' => [
            'BatStateU' => 90,
            'Others' => 100
        ],
        'Fair' => [
            'BatStateU' => 110,
            'Others' => 120
        ],
        'Poor' => [
            'BatStateU' => 130,
            'Others' => 140
        ]
    ];
}

// Add this function before the HTML section
function getSignatories($campus) {
    try {
        $conn = getConnection();
        
        // Fetch signatories data for the specified campus
        $sql = "SELECT * FROM signatories WHERE campus = :campus";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':campus', $campus);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If no results found for the campus, try a default fetch
        if (!$result) {
            error_log("No signatories found for campus: $campus - trying to fetch default");
            
            // Try to fetch any record to use as default
            $defaultSql = "SELECT * FROM signatories LIMIT 1";
            $defaultStmt = $conn->prepare($defaultSql);
            $defaultStmt->execute();
            $result = $defaultStmt->fetch(PDO::FETCH_ASSOC);
            
            // If still no records found, return structured defaults
            if (!$result) {
                error_log("No signatories found at all - using empty defaults");
                return [
                    'name1' => '',
                    'name3' => '',
                    'name4' => ''
                ];
            }
            
            error_log("Using default signatory record");
        }
        
        // Log the signatories data retrieved
        error_log('Retrieved signatories data: ' . print_r($result, true));
        
        return $result;
    } catch (Exception $e) {
        error_log('Error fetching signatories: ' . $e->getMessage());
        return [
            'name1' => '',
            'name3' => '',
            'name4' => ''
        ];
    }
}

// Get signatories for the current campus
$signatories = getSignatories(isset($_SESSION['username']) ? $_SESSION['username'] : '');

// Debug log the signatories data
error_log('Signatories data in print_narrative.php: ' . print_r($signatories, true));

// Debug: Add a comment with all field names from the database
$signatories['debug_all_fields'] = json_encode($signatories);

// Add this function at the top of the file, after any existing includes
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

// Add this function to fetch personnel data
function getPersonnelData($ppas_form_id) {
    try {
        $conn = getConnection();
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
        $stmt->bindParam(':ppas_form_id', $ppas_form_id);
        $stmt->execute();
        
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
        
        // If no personnel found, try alternative tables and fallback options
        if (empty($personnel_by_role['project_leaders']) && empty($personnel_by_role['assistant_project_leaders']) && empty($personnel_by_role['project_staff'])) {
            // Try the personnel_list or other tables if they exist
            try {
                // Check if a PPAS form exists with personnel data
                $sql = "SELECT * FROM ppas_forms WHERE id = :ppas_form_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':ppas_form_id', $ppas_form_id);
                $stmt->execute();
                $ppasForm = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($ppasForm) {
                    // Try to get project leaders and other personnel from proposal data
                    $proSql = "SELECT * FROM gad_proposals WHERE ppas_form_id = :ppas_form_id";
                    $proStmt = $conn->prepare($proSql);
                    $proStmt->bindParam(':ppas_form_id', $ppas_form_id);
                    $proStmt->execute();
                    $proposal = $proStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($proposal) {
                        // Check for project_leader_responsibilities
                        if (!empty($proposal['project_leader_responsibilities'])) {
                            $leaders = json_decode($proposal['project_leader_responsibilities'], true);
                            if (is_array($leaders)) {
                                foreach ($leaders as $leader) {
                                    $personnel_by_role['project_leaders'][] = [
                                        'name' => $leader,
                                        'role' => 'Project Leader'
                                    ];
                                }
                            }
                        }
                        
                        // Check for assistant_leader_responsibilities
                        if (!empty($proposal['assistant_leader_responsibilities'])) {
                            $assistants = json_decode($proposal['assistant_leader_responsibilities'], true);
                            if (is_array($assistants)) {
                                foreach ($assistants as $assistant) {
                                    $personnel_by_role['assistant_project_leaders'][] = [
                                        'name' => $assistant,
                                        'role' => 'Assistant Project Leader'
                                    ];
                                }
                            }
                        }
                        
                        // Check for staff_responsibilities
                        if (!empty($proposal['staff_responsibilities'])) {
                            $staff = json_decode($proposal['staff_responsibilities'], true);
                            if (is_array($staff)) {
                                foreach ($staff as $member) {
                                    $personnel_by_role['project_staff'][] = [
                                        'name' => $member,
                                        'role' => 'Staff'
                                    ];
                                }
                            }
                        }
                    }
                }
                
                // Try narrative_entries table as fallback
                $sql = "SELECT * FROM narrative_entries WHERE ppas_form_id = :ppas_form_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':ppas_form_id', $ppas_form_id);
                $stmt->execute();
                $narrative = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($narrative) {
                    // Process leader_tasks
                    if (!empty($narrative['leader_tasks'])) {
                        $leaderTasks = json_decode($narrative['leader_tasks'], true);
                        if (is_array($leaderTasks) && empty($personnel_by_role['project_leaders'])) {
                            foreach ($leaderTasks as $task) {
                                $personnel_by_role['project_leaders'][] = [
                                    'name' => $task,
                                    'role' => 'Project Leader'
                                ];
                            }
                        }
                    }
                    
                    // Process assistant_tasks
                    if (!empty($narrative['assistant_tasks'])) {
                        $assistantTasks = json_decode($narrative['assistant_tasks'], true);
                        if (is_array($assistantTasks) && empty($personnel_by_role['assistant_project_leaders'])) {
                            foreach ($assistantTasks as $task) {
                                $personnel_by_role['assistant_project_leaders'][] = [
                                    'name' => $task,
                                    'role' => 'Assistant Project Leader'
                                ];
                            }
                        }
                    }
                    
                    // Process staff_tasks
                    if (!empty($narrative['staff_tasks'])) {
                        $staffTasks = json_decode($narrative['staff_tasks'], true);
                        if (is_array($staffTasks) && empty($personnel_by_role['project_staff'])) {
                            foreach ($staffTasks as $task) {
                                $personnel_by_role['project_staff'][] = [
                                    'name' => $task,
                                    'role' => 'Staff'
                                ];
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                error_log('Error fetching alternative personnel data: ' . $e->getMessage());
            }
        }
        
        return $personnel_by_role;
    } catch (Exception $e) {
        error_log('Error fetching personnel data: ' . $e->getMessage());
        return null;
    }
}

// Add this function to get narrative data with filtered extension_service_agenda = 1
function getNarrativeData($ppas_form_id) {
    try {
        $conn = getConnection();
        
        // Get database schema info to be more adaptive
        $table_info = [];
        
        // Check if narrative_entries table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'narrative_entries'");
        $table_info['narrative_entries_exists'] = ($stmt->rowCount() > 0);
        
        // Get columns in narrative_entries
        if ($table_info['narrative_entries_exists']) {
            $stmt = $conn->query("DESCRIBE narrative_entries");
            $table_info['narrative_entries_columns'] = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $table_info['narrative_entries_columns'][] = $row['Field'];
            }
            
            // Log the columns found
            error_log("Columns in narrative_entries: " . implode(", ", $table_info['narrative_entries_columns']));
        }
        
        // Check if ppas_entries table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'ppas_entries'");
        $table_info['ppas_entries_exists'] = ($stmt->rowCount() > 0);
        
        // Get columns in ppas_entries if it exists
        if ($table_info['ppas_entries_exists']) {
            $stmt = $conn->query("DESCRIBE ppas_entries");
            $table_info['ppas_entries_columns'] = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $table_info['ppas_entries_columns'][] = $row['Field'];
            }
            
            // Log the columns found
            error_log("Columns in ppas_entries: " . implode(", ", $table_info['ppas_entries_columns']));
        }
        
        // Initialize the response array with defaults
        $narrative = [
            'general_objectives' => '',
            'specific_objectives' => [],
            // Add new fields with default empty values
            'background' => '',
            'participants' => '',
            'topics' => '',
            'results' => '',
            'lessons' => '',
            'what_worked' => '',
            'issues' => '',
            'recommendations' => '',
            'activity_ratings' => [],
            'timeliness_ratings' => [],
            'activity_images' => []
        ];

        // Debug to error log - important for tracking
        error_log("Getting narrative data for PPAS form ID: $ppas_form_id");
        
        // First try to get data from ppas_entries if it exists
        if ($table_info['ppas_entries_exists']) {
            try {
                error_log("Checking ppas_entries table for ppas_form_id: $ppas_form_id");
                $ppas_entries_sql = "SELECT * FROM ppas_entries WHERE ppas_form_id = :ppas_form_id LIMIT 1";
                $ppas_entries_stmt = $conn->prepare($ppas_entries_sql);
                $ppas_entries_stmt->bindParam(':ppas_form_id', $ppas_form_id);
                $ppas_entries_stmt->execute();
                $ppas_entries_data = $ppas_entries_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($ppas_entries_data) {
                    error_log("Found data in ppas_entries for ppas_form_id: $ppas_form_id");
                    
                    // Extract activity_ratings from ppas_entries if available
                    if (array_key_exists('activity_ratings', $ppas_entries_data) && !empty($ppas_entries_data['activity_ratings'])) {
                        try {
                            $activity_ratings = $ppas_entries_data['activity_ratings'];
                            if (is_string($activity_ratings)) {
                                $parsed_activity_ratings = json_decode($activity_ratings, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $narrative['activity_ratings'] = $parsed_activity_ratings;
                                    error_log("Successfully parsed activity_ratings JSON from ppas_entries: " . substr($activity_ratings, 0, 100) . "...");
                                } else {
                                    error_log("JSON parse error for activity_ratings from ppas_entries: " . json_last_error_msg());
                                }
                            } else if (is_array($activity_ratings)) {
                                $narrative['activity_ratings'] = $activity_ratings;
                                error_log("Used array data for activity_ratings from ppas_entries");
                            }
                        } catch (Exception $e) {
                            error_log("Exception processing activity_ratings from ppas_entries: " . $e->getMessage());
                        }
                    }
                    
                    // Extract timeliness_ratings from ppas_entries if available
                    if (array_key_exists('timeliness_ratings', $ppas_entries_data) && !empty($ppas_entries_data['timeliness_ratings'])) {
                        try {
                            $timeliness_ratings = $ppas_entries_data['timeliness_ratings'];
                            if (is_string($timeliness_ratings)) {
                                $parsed_timeliness_ratings = json_decode($timeliness_ratings, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $narrative['timeliness_ratings'] = $parsed_timeliness_ratings;
                                    error_log("Successfully parsed timeliness_ratings JSON from ppas_entries: " . substr($timeliness_ratings, 0, 100) . "...");
                                } else {
                                    error_log("JSON parse error for timeliness_ratings from ppas_entries: " . json_last_error_msg());
                                }
                            } else if (is_array($timeliness_ratings)) {
                                $narrative['timeliness_ratings'] = $timeliness_ratings;
                                error_log("Used array data for timeliness_ratings from ppas_entries");
                            }
                        } catch (Exception $e) {
                            error_log("Exception processing timeliness_ratings from ppas_entries: " . $e->getMessage());
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Error fetching from ppas_entries: " . $e->getMessage());
            }
        }
        
        // Continue with existing code to get the main activity data from ppas_forms
        try {
            $ppas_sql = "SELECT * FROM ppas_forms WHERE id = :id";
            $ppas_stmt = $conn->prepare($ppas_sql);
            $ppas_stmt->bindParam(':id', $ppas_form_id);
            $ppas_stmt->execute();
            $ppas_data = $ppas_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($ppas_data) {
                error_log("Found data in ppas_forms for ID $ppas_form_id");
                // Store key PPAS data for later use
                $ppas_activity = $ppas_data['activity'] ?? ($ppas_data['activity_title'] ?? 'Untitled Activity');
                $ppas_year = $ppas_data['year'] ?? date('Y');
                $ppas_campus = $ppas_data['campus'] ?? '';
                
                error_log("PPAS data - Activity: $ppas_activity, Year: $ppas_year, Campus: $ppas_campus");
                
                // Set some default objectives based on ppas_forms data if needed
                if (empty($narrative['general_objectives'])) {
                    $narrative['general_objectives'] = "To successfully implement " . $ppas_activity;
                }
                if (empty($narrative['specific_objectives'])) {
                    $narrative['specific_objectives'] = ["To achieve the goals of " . $ppas_activity];
                }
                
                // Now look for matching narrative_entries using ppas_form_id or activity title
                try {
                    // Variable to track if we found a matching narrative entry
                    $foundMatch = false;
                    $entry_data = null;
                    
                    // First try to match by ppas_form_id if the column exists
                    if (in_array('ppas_form_id', $table_info['narrative_entries_columns'])) {
                        error_log("Found ppas_form_id column in narrative_entries, attempting direct match");
                        
                        $entry_sql = "SELECT * FROM narrative_entries WHERE ppas_form_id = :ppas_id LIMIT 1";
                        $entry_stmt = $conn->prepare($entry_sql);
                        $entry_stmt->bindParam(':ppas_id', $ppas_form_id);
                        $entry_stmt->execute();
                        $entry_data = $entry_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($entry_data) {
                            error_log("Found matching narrative_entries record by ppas_form_id!");
                            $foundMatch = true;
                        }
                    } else {
                        error_log("No ppas_form_id column found in narrative_entries table");
                    }
                    
                    // If no match by ppas_form_id, try by exact title
                    if (!$foundMatch && !empty($ppas_activity)) {
                        error_log("Attempting exact title match for: '$ppas_activity'");
                        $title_sql = "SELECT * FROM narrative_entries WHERE title = :title AND campus = :campus LIMIT 1";
                        $title_stmt = $conn->prepare($title_sql);
                        $title_stmt->bindParam(':title', $ppas_activity);
                        $title_stmt->bindParam(':campus', $ppas_campus);
                        $title_stmt->execute();
                        $entry_data = $title_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($entry_data) {
                            error_log("Found matching narrative_entries record by exact title match!");
                            $foundMatch = true;
                        }
                    }
                    
                    // If still no match, try by title similarity
                    if (!$foundMatch && !empty($ppas_activity)) {
                        error_log("Attempting title-based LIKE match for: '$ppas_activity'");
                        $title_sql = "SELECT * FROM narrative_entries WHERE title LIKE :title AND campus = :campus LIMIT 1";
                        $title_stmt = $conn->prepare($title_sql);
                        $title_param = '%' . $ppas_activity . '%';
                        $title_stmt->bindParam(':title', $title_param);
                        $title_stmt->bindParam(':campus', $ppas_campus);
                        $title_stmt->execute();
                        $entry_data = $title_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($entry_data) {
                            error_log("Found matching narrative_entries record by title LIKE match!");
                            $foundMatch = true;
                        }
                    }
                    
                    // If still no match, try keyword matching (split title into words and find any match)
                    if (!$foundMatch && !empty($ppas_activity)) {
                        error_log("Attempting keyword matching for: '$ppas_activity'");
                        $keywords = preg_split('/\s+/', $ppas_activity);
                        
                        // Build a query with multiple LIKE conditions
                        $conditions = [];
                        $params = [];
                        $index = 0;
                        
                        foreach ($keywords as $keyword) {
                            if (strlen($keyword) > 3) { // Only use keywords longer than 3 chars
                                $paramName = ":keyword" . $index;
                                $conditions[] = "title LIKE $paramName";
                                $params[$paramName] = '%' . $keyword . '%';
                                $index++;
                            }
                        }
                        
                        if (!empty($conditions)) {
                            $keyword_sql = "SELECT * FROM narrative_entries WHERE (" . implode(" OR ", $conditions) . ") AND campus = :campus LIMIT 1";
                            $keyword_stmt = $conn->prepare($keyword_sql);
                            $keyword_stmt->bindParam(':campus', $ppas_campus);
                            
                            foreach ($params as $param => $value) {
                                $keyword_stmt->bindValue($param, $value);
                            }
                            
                            $keyword_stmt->execute();
                            $entry_data = $keyword_stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($entry_data) {
                                error_log("Found matching narrative_entries record by keyword matching!");
                                $foundMatch = true;
                            }
                        }
                    }
                    
                    // Process the found narrative entry if any
                    if ($foundMatch && $entry_data) {
                        error_log("Processing matched narrative entry data with ID: " . ($entry_data['id'] ?? 'unknown'));
                        
                        // Map fields from narrative_entries to our narrative array - check if each column exists
                        $narrative_field_mappings = [
                            'background' => ['background', 'activity_background'],
                            'participants' => ['participants', 'participant_description', 'activity_participants'],
                            'topics' => ['topics', 'discussion_topics', 'activity_topics'],
                            'results' => ['results', 'activity_results', 'outputs_outcomes', 'expected_results'],
                            'lessons' => ['lessons', 'lessons_learned', 'activity_lessons'],
                            'what_worked' => ['what_worked', 'worked_not_worked'],
                            'issues' => ['issues', 'issues_concerns', 'issues_addressed'],
                            'recommendations' => ['recommendations', 'activity_recommendations']
                        ];
                        
                        // Check and map fields
                        foreach ($narrative_field_mappings as $narrative_field => $possible_db_fields) {
                            foreach ($possible_db_fields as $db_field) {
                                if (array_key_exists($db_field, $entry_data) && !empty($entry_data[$db_field])) {
                                    $narrative[$narrative_field] = $entry_data[$db_field];
                                    error_log("Mapped $db_field to $narrative_field: " . substr($entry_data[$db_field], 0, 50) . "...");
                                    break;
                                }
                            }
                        }
                        
                        // Process photos - check for various possible column names
                        $photo_field_names = ['photo_path', 'photo_paths', 'activity_images', 'images'];
                        foreach ($photo_field_names as $field) {
                            if (array_key_exists($field, $entry_data) && !empty($entry_data[$field])) {
                                $photo_data = $entry_data[$field];
                                
                                if (is_string($photo_data)) {
                                    try {
                                        // Try to parse as JSON
                                        $parsed_photos = json_decode($photo_data, true);
                                        if (is_array($parsed_photos)) {
                                            $narrative['activity_images'] = $parsed_photos;
                                        } else {
                                            // Single path as string
                                            $narrative['activity_images'] = [$photo_data];
                                        }
                                    } catch (Exception $e) {
                                        // If not valid JSON, treat as a single photo path
                                        $narrative['activity_images'] = [$photo_data];
                                    }
                                } elseif (is_array($photo_data)) {
                                    $narrative['activity_images'] = $photo_data;
                                }
                            }
                        }
                        
                        // DIRECT ACCESS TO RATINGS FIELDS
                        // Process activity_ratings directly from the field
                        if (array_key_exists('activity_ratings', $entry_data) && !empty($entry_data['activity_ratings'])) {
                            $rating_data = $entry_data['activity_ratings'];
                            
                            if (is_string($rating_data)) {
                                try {
                                    $parsed_ratings = json_decode($rating_data, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($parsed_ratings)) {
                                        error_log("Successfully parsed activity_ratings JSON: " . substr($rating_data, 0, 100) . "...");
                                        $narrative['activity_ratings'] = $parsed_ratings;
                                    } else {
                                        error_log("JSON parse error for activity_ratings: " . json_last_error_msg() . " - Data: " . substr($rating_data, 0, 100) . "...");
                                    }
                                } catch (Exception $e) {
                                    error_log("Exception parsing activity_ratings JSON: " . $e->getMessage());
                                }
                            } elseif (is_array($rating_data)) {
                                $narrative['activity_ratings'] = $rating_data;
                                error_log("Used array data for activity_ratings");
                            }
                        }

                        // Process timeliness_ratings directly from the field
                        if (array_key_exists('timeliness_ratings', $entry_data) && !empty($entry_data['timeliness_ratings'])) {
                            $rating_data = $entry_data['timeliness_ratings'];
                            
                            if (is_string($rating_data)) {
                                try {
                                    $parsed_ratings = json_decode($rating_data, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($parsed_ratings)) {
                                        error_log("Successfully parsed timeliness_ratings JSON: " . substr($rating_data, 0, 100) . "...");
                                        $narrative['timeliness_ratings'] = $parsed_ratings;
                                    } else {
                                        error_log("JSON parse error for timeliness_ratings: " . json_last_error_msg() . " - Data: " . substr($rating_data, 0, 100) . "...");
                                    }
                                } catch (Exception $e) {
                                    error_log("Exception parsing timeliness_ratings JSON: " . $e->getMessage());
                                }
                            } elseif (is_array($rating_data)) {
                                $narrative['timeliness_ratings'] = $rating_data;
                                error_log("Used array data for timeliness_ratings");
                            }
                        }
                        
                        // Process ratings data from other field names (backup approach)
                        $ratings_field_mappings = [
                            'activity_ratings' => ['activity_rating', 'evaluation_ratings'],
                            'timeliness_ratings' => ['timeliness_rating', 'timeliness']
                        ];
                        
                        // Check for ratings data in various field formats
                        foreach ($ratings_field_mappings as $ratings_field => $possible_db_fields) {
                            // Only process if the target ratings field is still empty
                            if (empty($narrative[$ratings_field])) {
                                foreach ($possible_db_fields as $db_field) {
                                    if (array_key_exists($db_field, $entry_data) && !empty($entry_data[$db_field])) {
                                        // Try to handle various formats of rating data
                                        $rating_data = $entry_data[$db_field];
                                        
                                        if (is_string($rating_data)) {
                                            try {
                                                // Try to parse as JSON
                                                $parsed_ratings = json_decode($rating_data, true);
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($parsed_ratings)) {
                                                    error_log("Successfully parsed $db_field JSON: " . substr($rating_data, 0, 100) . "...");
                                                    $narrative[$ratings_field] = $parsed_ratings;
                                                    break;
                                                } else {
                                                    error_log("JSON parse error for $db_field: " . json_last_error_msg() . " - Data: " . substr($rating_data, 0, 100) . "...");
                                                }
                                            } catch (Exception $e) {
                                                error_log("Exception parsing $db_field JSON: " . $e->getMessage());
                                            }
                                        } elseif (is_array($rating_data)) {
                                            $narrative[$ratings_field] = $rating_data;
                                            error_log("Used array data for $ratings_field from $db_field");
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Handle special case for evaluation which might contain both ratings
                        if ((empty($narrative['activity_ratings']) || empty($narrative['timeliness_ratings'])) && 
                            array_key_exists('evaluation', $entry_data) && !empty($entry_data['evaluation'])) {
                            try {
                                $evaluation = $entry_data['evaluation'];
                                if (is_string($evaluation)) {
                                    $parsed_evaluation = json_decode($evaluation, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($parsed_evaluation)) {
                                        error_log("Successfully parsed evaluation JSON");
                                        
                                        // Check for expected structure
                                        if (empty($narrative['activity_ratings']) && isset($parsed_evaluation['activity']) && is_array($parsed_evaluation['activity'])) {
                                            $narrative['activity_ratings'] = $parsed_evaluation['activity'];
                                            error_log("Extracted activity ratings from evaluation");
                                        }
                                        
                                        if (empty($narrative['timeliness_ratings']) && isset($parsed_evaluation['timeliness']) && is_array($parsed_evaluation['timeliness'])) {
                                            $narrative['timeliness_ratings'] = $parsed_evaluation['timeliness'];
                                            error_log("Extracted timeliness ratings from evaluation");
                                        }
                                    }
                                }
                            } catch (Exception $e) {
                                error_log("Exception parsing evaluation JSON: " . $e->getMessage());
                            }
                        }
                        
                        // If no rating data found, use default ratings
                        if (empty($narrative['activity_ratings'])) {
                            $narrative['activity_ratings'] = getDefaultRatings();
                            error_log("No activity ratings found, using defaults");
                        }
                        if (empty($narrative['timeliness_ratings'])) {
                            $narrative['timeliness_ratings'] = getDefaultRatings();
                            error_log("No timeliness ratings found, using defaults");
                        }
                        
                        // Debug the final narrative data being returned
                        error_log("Final narrative data from narrative_entries: " . json_encode(array_keys($narrative)));
                    } else {
                        error_log("No matching narrative_entries record found for PPAS form ID: $ppas_form_id or title: $ppas_activity");
                    }
                } catch (Exception $e) {
                    error_log("Exception in narrative_entries lookup: " . $e->getMessage());
                }
            } else {
                error_log("PPAS form with ID $ppas_form_id not found");
            }
        } catch (Exception $e) {
            error_log("Exception in main PPAS form lookup: " . $e->getMessage());
        }
        
        // Final default values for any fields that are still missing
        return [
            'general_objectives' => $narrative['general_objectives'] ?: '',
            'specific_objectives' => $narrative['specific_objectives'] ?: [],
            'background' => $narrative['background'] ?: '',
            'participants' => $narrative['participants'] ?: '',
            'topics' => $narrative['topics'] ?: '',
            'results' => $narrative['results'] ?: '',
            'lessons' => $narrative['lessons'] ?: '',
            'what_worked' => $narrative['what_worked'] ?: '',
            'issues' => $narrative['issues'] ?: '',
            'recommendations' => $narrative['recommendations'] ?: '',
            'activity_ratings' => $narrative['activity_ratings'] ?: [],
            'timeliness_ratings' => $narrative['timeliness_ratings'] ?: [],
            'activity_images' => $narrative['activity_images'] ?: []
        ];
        
    } catch (Exception $e) {
        error_log("Overall exception in getNarrativeData: " . $e->getMessage());
        return [
            'general_objectives' => '',
            'specific_objectives' => [],
            'background' => '',
            'participants' => '',
            'topics' => '',
            'results' => '',
            'lessons' => '',
            'what_worked' => '',
            'issues' => '',
            'recommendations' => '',
            'activity_ratings' => [],
            'timeliness_ratings' => [],
            'activity_images' => []
        ];
    }
}

// Debug helper function to find objective fields in the database
function findObjectiveFields($ppas_form_id) {
    try {
        $conn = getConnection();
        $results = [];
        
        // Get all tables in the database
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Look for tables that might contain objective data
        foreach ($tables as $table) {
            // Get columns for this table
            $stmt = $conn->query("DESCRIBE `$table`");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Check if table has potentially relevant columns
            $relevant_columns = array_filter($columns, function($col) {
                return stripos($col, 'objective') !== false || 
                       $col === 'id' || 
                       stripos($col, 'ppas') !== false;
            });
            
            if (!empty($relevant_columns)) {
                $results[$table] = ['columns' => $relevant_columns];
                
                // If this table has id or ppas_form_id column, query for our specific record
                if (in_array('id', $columns) || in_array('ppas_form_id', $columns)) {
                    try {
                        $where_clause = in_array('ppas_form_id', $columns) ? 
                            "WHERE ppas_form_id = :id" : 
                            (in_array('id', $columns) && $table !== 'users' ? "WHERE id = :id" : "LIMIT 0");
                        
                        if ($where_clause !== "LIMIT 0") {
                            $query = "SELECT * FROM `$table` $where_clause LIMIT 1";
                            $stmt = $conn->prepare($query);
                            $stmt->bindParam(':id', $ppas_form_id);
                            $stmt->execute();
                            $data = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($data) {
                                // Extract only objective-related fields to keep the log smaller
                                $objective_data = array_filter($data, function($value, $key) {
                                    return stripos($key, 'objective') !== false && !empty($value);
                                }, ARRAY_FILTER_USE_BOTH);
                                
                                if (!empty($objective_data)) {
                                    $results[$table]['data'] = $objective_data;
                                }
                            }
                        }
    } catch (Exception $e) {
                        // Skip if query fails
                        $results[$table]['error'] = $e->getMessage();
                    }
                }
            }
        }
        
        error_log("[DEBUG] Potential objective fields in database: " . json_encode($results));
        return $results;
    } catch (Exception $e) {
        error_log("[ERROR] Error searching for objective fields: " . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
}

// Get the PPAS form ID from the URL for direct querying
$ppas_form_id = isset($_GET['id']) ? $_GET['id'] : null;

// Find objective fields if we have a PPAS form ID
if ($ppas_form_id) {
    $objective_fields = findObjectiveFields($ppas_form_id);
}

// Check if user is logged in, etc...
// ... existing code ...
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Narrative Reports - GAD System</title>
    <link rel="icon" type="image/x-icon" href="../images/Batangas_State_Logo.ico">
    <script src="../js/common.js"></script>
    <!-- Immediate theme loading to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = savedTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        })();
    </script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --accent-color: #6a1b9a;
            --accent-hover: #4a148c;
        }
        
        /* Light Theme Variables */
        [data-bs-theme="light"] {
            --bg-primary: #f0f0f0;
            --bg-secondary: #e9ecef;
            --sidebar-bg: #ffffff;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --hover-color: rgba(106, 27, 154, 0.1);
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --horizontal-bar: rgba(33, 37, 41, 0.125);
            --input-placeholder: rgba(33, 37, 41, 0.75);
            --input-bg: #ffffff;
            --input-text: #212529;
            --card-title: #212529;
            --scrollbar-thumb: rgba(156, 39, 176, 0.4);
            --scrollbar-thumb-hover: rgba(156, 39, 176, 0.7);
        }

        /* Dark Theme Variables */
        [data-bs-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --sidebar-bg: #2d2d2d;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --hover-color: #8a4ebd;
            --card-bg: #2d2d2d;
            --border-color: #404040;
            --horizontal-bar: rgba(255, 255, 255, 0.1);
            --input-placeholder: rgba(255, 255, 255, 0.7);
            --input-bg: #404040;
            --input-text: #ffffff;
            --card-title: #ffffff;
            --scrollbar-thumb: #6a1b9a;
            --scrollbar-thumb-hover: #9c27b0;
            --accent-color: #9c27b0;
            --accent-hover: #7b1fa2;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
            opacity: 1;
            transition: opacity 0.05s ease-in-out; /* Changed from 0.05s to 0.01s - make it super fast */
        }

        body.fade-out {
    opacity: 0;
}

        

        .sidebar {
            width: var(--sidebar-width);
            height: calc(100vh - 40px);
            position: fixed;
            left: 20px;
            top: 20px;
            padding: 20px;
            background: var(--sidebar-bg);
            color: var(--text-primary);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 5px 0 15px rgba(0,0,0,0.05), 0 5px 15px rgba(0,0,0,0.05);
            z-index: 1;
        }

        .main-content {
    margin-left: calc(var(--sidebar-width) + 20px);
    padding: 15px;
    height: calc(100vh - 30px);
    max-height: calc(100vh - 30px);
    background: var(--bg-primary);
    border-radius: 20px;
    position: relative;
    overflow-y: auto;
    scrollbar-width: none;  /* Firefox */
    -ms-overflow-style: none;  /* IE and Edge */
}

/* Hide scrollbar for Chrome, Safari and Opera */
.main-content::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for Chrome, Safari and Opera */
body::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for Firefox */
html {
    scrollbar-width: none;
}

        .nav-link {
            color: var(--text-primary);
            padding: 12px 15px;
            border-radius: 12px;
            margin-bottom: 5px;
            position: relative;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 12px;
        }

        .nav-link:hover {
            background: var(--hover-color);
            color: white;
        }

        /* Restore light mode hover color */
        [data-bs-theme="light"] .nav-link:hover {
            color: var(--accent-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-menu .dropdown-item:hover {
            color: var(--accent-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--accent-color) !important;
        }

        .nav-link.active {
            color: var(--accent-color);
            position: relative;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: var(--accent-color);
            border-radius: 0 2px 2px 0;
        }

        /* Add hover state for active nav links in dark mode */
        [data-bs-theme="dark"] .nav-link.active:hover {
            color: white;
        }

        .nav-item {
            position: relative;
        }

        .nav-item .dropdown-menu {
            position: static !important;
            background: var(--sidebar-bg);
            border: 1px solid var(--border-color);
            padding: 8px 0;
            margin: 5px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            min-width: 200px;
            transform: none !important;
            display: none;
            overflow: visible;
            max-height: none;
        }

        /* Dropdown submenu styles */
        /* Dropdown submenu styles */
.dropdown-submenu {
    position: relative;
}

.dropdown-submenu .dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: -8px;
    margin-left: 1px;
    border-radius: 0 6px 6px 6px;
    display: none;
}

/* Add click-based display */
.dropdown-submenu.show > .dropdown-menu {
    display: block;
}

.dropdown-submenu > a:after {
    display: block;
    content: " ";
    float: right;
    width: 0;
    height: 0;
    border-color: transparent;
    border-style: solid;
    border-width: 5px 0 5px 5px;
    border-left-color: var(--text-primary);
    margin-top: 5px;
    margin-right: -10px;
}

/* Update hover effect for arrow */
.dropdown-submenu.show > a:after {
    border-left-color: var(--accent-color);
}

/* Mobile styles for dropdown submenu */
@media (max-width: 991px) {
    .dropdown-submenu .dropdown-menu {
        position: static !important;
        left: 0;
        margin-left: 20px;
        margin-top: 0;
        border-radius: 0;
        border-left: 2px solid var(--accent-color);
    }
    
    .dropdown-submenu > a:after {
        transform: rotate(90deg);
        margin-top: 8px;
    }
}
        
        /* End of dropdown submenu styles */

        .nav-item .dropdown-menu.show {
            display: block;
        }

        .nav-item .dropdown-menu .dropdown-item {
            padding: 8px 48px;
            color: var(--text-primary);
            position: relative;
            opacity: 0.85;
            background: transparent;
        }

        .nav-item .dropdown-menu .dropdown-item::before {
            content: '•';
            position: absolute;
            left: 35px;
            color: var(--accent-color);
        }

        .nav-item .dropdown-menu .dropdown-item:hover {
            background: var(--hover-color);
            color: white;
            opacity: 1;
        }

        [data-bs-theme="light"] .nav-item .dropdown-menu .dropdown-item:hover {
            color: var(--accent-color);
        }

        .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: white !important;
            background: var(--hover-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--accent-color) !important;
        }

        .logo-container {
            padding: 20px 0;
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-title {
            font-size: 24px;
            font-weight: bold;
            color: var(--text-primary);
            margin-bottom: 15px;
        }

        .logo-image {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            margin-bottom: -25px;
        }

        .logo-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .datetime-container {
            text-align: center;
            padding: 15px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--horizontal-bar);
        }

        .datetime-container .date {
            font-size: 1.1rem;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .datetime-container .time {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--accent-color);
        }

        .nav-content {
            flex-grow: 1;
            overflow-y: auto;
            max-height: calc(100vh - 470px);
            margin-bottom: 20px;
            padding-right: 5px;
            scrollbar-width: thin;
            scrollbar-color: rgba(106, 27, 154, 0.4) transparent;
            overflow-x: hidden; 
        }

        .nav-content::-webkit-scrollbar {
            width: 5px;
        }

        .nav-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .nav-content::-webkit-scrollbar-thumb {
            background-color: rgba(106, 27, 154, 0.4);
            border-radius: 1px;
        }

        .nav-content::-webkit-scrollbar-thumb:hover {
            background-color: rgba(106, 27, 154, 0.7);
        }

        .nav-link:focus,
        .dropdown-toggle:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        .dropdown-menu {
            outline: none !important;
            border: none !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        }

        .dropdown-item:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        /* Bottom controls container */
        .bottom-controls {
            position: absolute;
            bottom: 20px;
            width: calc(var(--sidebar-width) - 40px);
            display: flex;
            gap: 5px;
            align-items: center;
        }

        /* Logout button styles */
        .logout-button {
            flex: 1;
            background: var(--bg-primary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Theme switch button */
        .theme-switch-button {
            width: 46.5px;
            height: 50px;
            padding: 12px 0;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

                /* Light theme specific styles for bottom controls */
                [data-bs-theme="light"] .logout-button,
        [data-bs-theme="light"] .theme-switch-button {
            background: #f2f2f2;
            border-width: 1.5px;
        }

        /* Hover effects */
        .logout-button:hover,
        .theme-switch-button:hover {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .logout-button:active,
        .theme-switch-button:active {
            transform: translateY(0);
            box-shadow: 
                0 4px 6px rgba(0, 0, 0, 0.1),
                0 2px 4px rgba(0, 0, 0, 0.06),
                inset 0 1px 2px rgba(255, 255, 255, 0.2);
        }

        /* Theme switch button icon size */
        .theme-switch-button i {
            font-size: 1rem; 
        }

        .theme-switch-button:hover i {
            transform: scale(1.1);
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 1.5rem;
        }

        .page-title i {
            color: var(--accent-color);
            font-size: 2.2rem;
        }

        .page-title h2 {
            margin: 0;
            font-weight: 600;
        }

        .show>.nav-link {
            background: transparent !important;
            color: var(--accent-color) !important;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 991px) {
            :root {
                --sidebar-width: 240px;
            }

            body {
                padding: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
                left: 0;
                top: 0;
                height: 100vh;
                position: fixed;
                padding-top: 70px;
                border-radius: 0;
                box-shadow: 5px 0 25px rgba(0,0,0,0.1);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 70px 15px 15px 15px;
                border-radius: 0;
                box-shadow: none;
            }

            .mobile-nav-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
                background: var(--card-bg);
                border: none;
                border-radius: 8px;
                color: var(--text-primary);
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                cursor: pointer;
            }

            .mobile-nav-toggle:hover {
                background: var(--hover-color);
                color: var(--accent-color);
            }

            body.sidebar-open {
                overflow: hidden;
            }

            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }

            .sidebar-backdrop.show {
                display: block;
            }

            .theme-switch {
                position: fixed;
                bottom: 30px;
                right: 30px;
            }

        }

        @media (max-width: 576px) {
            :root {
                --sidebar-width: 100%;
            }

            .sidebar {
                left: 0;
                top: 0;
                width: 100%;
                height: 100vh;
                padding-top: 60px;
            }

            .mobile-nav-toggle {
                width: 40px;
                height: 40px;
                top: 10px;
                left: 10px;
            }

            .theme-switch {
                top: 10px;
                right: 10px;
            }

            .theme-switch-button {
                padding: 8px 15px;
            }

            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                margin-top: 10px;
            }

            .page-title h2 {
                font-size: 1.5rem;
            }
        }

        /* Modern Card Styles */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            position: relative;
            min-height: 465px;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        #ppasForm {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        #ppasForm.row {
            flex: 1;
        }

        #ppasForm .col-12.text-end {
            margin-top: auto !important;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        /* Dark Theme Colors */
        [data-bs-theme="dark"] {
            --dark-bg: #212529;
            --dark-input: #2b3035;
            --dark-text: #e9ecef;
            --dark-border: #495057;
            --dark-sidebar: #2d2d2d;
        }

        /* Dark mode card */
        [data-bs-theme="dark"] .card {
            background-color: var(--dark-sidebar) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .card-header {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
            overflow: hidden;
        }

        /* Fix for card header corners */
        .card-header {
            border-top-left-radius: inherit !important;
            border-top-right-radius: inherit !important;
            padding-bottom: 0.5rem !important;
        }

        .card-title {
            margin-bottom: 0;
        }

        /* Form Controls */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1 1 200px;
        }


        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 25px;
            margin-bottom: 20px;
        }

        .btn-icon {
            width: 45px;
            height: 45px;
            padding: 0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-icon i {
            font-size: 1.2rem;
        }

        /* Add button */
        #addBtn {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        #addBtn:hover {
            background: #198754;
            color: white;
        }

        /* Edit button */
        #editBtn {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        #editBtn:hover {
            background: #ffc107;
            color: white;
        }

        /* Edit button in cancel mode */
        #editBtn.editing {
            background: rgba(220, 53, 69, 0.1) !important;
            color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        #editBtn.editing:hover {
            background: #dc3545 !important;
            color: white !important;
        }

        /* Delete button */
        #deleteBtn {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        #deleteBtn:hover {
            background: #dc3545;
            color: white;
        }

        /* Delete button disabled state */
        #deleteBtn.disabled {
            background: rgba(108, 117, 125, 0.1) !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        /* Update button state */
        #addBtn.btn-update {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        #addBtn.btn-update:hover {
            background: #198754;
            color: white;
        }

#viewBtn {
    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

#viewBtn:hover {
    background: #0d6efd;
    color: white;
}

/* Optional: Add disabled state for view button */
#viewBtn.disabled {
    background: rgba(108, 117, 125, 0.1) !important;
    color: #6c757d !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
}

/* Add these styles for disabled buttons */
.btn-disabled {
    border-color: #6c757d !important;
    background: rgba(108, 117, 125, 0.1) !important;
    color: #6c757d !important;
    opacity: 0.65 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
}

/* Dark mode styles */
[data-bs-theme="dark"] .btn-disabled {
    background-color: #495057 !important;
    border-color: #495057 !important;
    color: #adb5bd !important;
}

.swal-blur-container {
    backdrop-filter: blur(5px);
}

/* Add print-specific styles */
@media print {
    @page {
        size: 8.5in 13in;
        margin-top: 1.52cm;
        margin-bottom: 2cm;
        margin-left: 1.78cm;
        margin-right: 2.03cm;
        border-top: 3px solid black !important;
        border-bottom: 3px solid black !important;
    }
    
    /* Force ALL colors to black - no exceptions */
    *, p, span, div, td, th, li, ul, ol, strong, em, b, i, a, h1, h2, h3, h4, h5, h6,
    [style*="color:"], [style*="color="], [style*="color :"], [style*="color ="],
    [style*="color: brown"], [style*="color: blue"], [style*="color: red"], 
    .brown-text, .blue-text, .sustainability-plan, .sustainability-plan p, .sustainability-plan li,
    .signature-label, .signature-position {
        color: black !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    
    /* First page footer with tracking number and page number */
    @page:first {
        margin-top: 1.52cm;
        margin-bottom: 2cm;
        margin-left: 1.78cm;
        margin-right: 2.03cm;
        border-top: 3px solid black !important;
        border-bottom: 3px solid black !important;
    }
    
    /* Ensure proper spacing for the footer */
    .proposal-container {
        margin-bottom: 1.5cm !important;
    }

    /* Disable "keep-with-next" behavior */
    * {
        orphans: 2 !important;
        widows: 2 !important;
        page-break-after: auto !important;
        page-break-before: auto !important;
        page-break-inside: auto !important;
        break-inside: auto !important;
        break-before: auto !important;
        break-after: auto !important;
    }
    
    /* Specific overrides for elements that should break */
    p, h1, h2, h3, h4, h5, h6, li, tr, div {
        page-break-after: auto !important;
        page-break-before: auto !important;
        page-break-inside: auto !important;
        break-inside: auto !important;
        break-before: auto !important;
        break-after: auto !important;
    }
    
    /* Tables should break naturally */
    table {
        page-break-inside: auto !important;
        break-inside: auto !important;
    }
    
    td, th {
        page-break-inside: auto !important;
        break-inside: auto !important;
    }
    
    /* Override any avoid settings */
    [style*="page-break-inside: avoid"], 
    [style*="break-inside: avoid"] {
        page-break-inside: auto !important;
        break-inside: auto !important;
    }

    body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        /* Remove border */
        border: none;
        box-sizing: border-box;
        min-height: calc(100% - 2cm);
        width: calc(100% - 3.81cm);
        margin-top: 1.52cm !important;
        margin-bottom: 2cm !important;
        margin-left: 1.78cm !important;
        margin-right: 2.03cm !important;
        background-clip: padding-box;
        box-shadow: none;
    }
}

/* Add these styles for compact form */
.compact-form .form-group {
    margin-bottom: 0.5rem !important;
}

.compact-form label {
    margin-bottom: 0.25rem !important;
    font-size: 0.85rem !important;
}

.compact-form .form-control-sm {
    height: calc(1.5em + 0.5rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.compact-form .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Additional styles to match get_gpb_report.php */
.compact-form select.form-control-sm,
.compact-form input.form-control-sm {
    font-size: 1rem !important;
    height: 38px !important;
    padding: 0.375rem 0.75rem !important;
}

#campus, #year, #proposal {
    font-size: 1rem !important;
    height: 38px !important;
}

.compact-form .btn-sm {
    font-size: 1rem !important;
    height: 38px !important;
    padding: 0.375rem 0.75rem !important;
}

.form-group label, .form-label {
    font-size: 1rem !important;
    margin-bottom: 0.5rem !important;
}

/* Make the card more compact */
.card {
    min-height: auto !important;
}
    </style>
    <style>
        /* Specific styles for GAD Proposal preview */
        .proposal-container {
            border: 1px solid #000;
            padding: 20px;
            margin: 20px auto;
            max-width: 1100px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            background-color: #fff;
            color: #000;
        }

        /* Header table styles */
        .header-table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 5px;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        /* Section heading styles */
        .section-heading {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        /* Table styles */
        .data-table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 10px;
        }

        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }

        /* Checkbox styles */
        .checkbox-container {
            display: flex;
            justify-content: center;
            margin: 10px 0;
        }

        .checkbox-option {
            margin: 0 20px;
            font-size: 12pt;
        }

        /* Signature table styles */
        .signatures-table {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border-collapse: collapse !important;
            page-break-inside: avoid !important;
            position: relative !important;
            left: 0 !important;
            right: 0 !important;
        }

        .signatures-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            vertical-align: top;
            height: 80px;
        }

        /* Heading styles */
        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* List styles */
        .proposal-container ol, .proposal-container ul {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 25px;
        }

        .proposal-container li {
            margin-bottom: 2px;
        }

        /* Responsibilities section */
        .responsibilities {
            margin-left: 20px;
        }

        /* Sustainability Plan - blue text */
        .sustainability-plan {
            color: blue;
        }

        .sustainability-plan ol li {
            color: blue;
        }

        /* Signature name styles */
        .signature-name {
            font-weight: bold;
            margin-top: 30px;
            margin-bottom: 0;
        }

        .signature-position {
            color: blue !important;
            margin-top: 0;
        }

        .signature-label {
            font-weight: bold;
            color: brown !important;
        }

        /* Page numbering and tracking */
        .page-footer {
            text-align: right;
            margin-top: 20px;
            font-size: 10pt;
        }

        /* Gantt chart cell styling */
        .gantt-filled {
            background-color: black !important;
        }

        /* Brown text for labels */
        .brown-text {
            color: brown !important;
        }

        /* Add page break styles */
        .page-break {
            page-break-before: always;
        }
        
        /* Print-specific styles */
        @media print {
            @page {
                size: 8.5in 13in;
                margin-top: 1.52cm;
                margin-bottom: 2cm;
                margin-left: 1.78cm;
                margin-right: 2.03cm;
                border-top: 3px solid black !important;
                border-bottom: 3px solid black !important;
            }
            
            /* Force ALL colors to black - no exceptions */
            *, p, span, div, td, th, li, ul, ol, strong, em, b, i, a, h1, h2, h3, h4, h5, h6,
            [style*="color:"], [style*="color="], [style*="color :"], [style*="color ="],
            [style*="color: brown"], [style*="color: blue"], [style*="color: red"], 
            .brown-text, .blue-text, .sustainability-plan, .sustainability-plan p, .sustainability-plan li,
            .signature-label, .signature-position {
                color: black !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            /* First page footer with tracking number and page number */
            @page:first {
                margin-top: 1.52cm;
                margin-bottom: 2cm;
                margin-left: 1.78cm;
                margin-right: 2.03cm;
                border-top: 3px solid black !important;
                border-bottom: 3px solid black !important;
            }
            
            /* Ensure proper spacing for the footer */
            .proposal-container {
                margin-bottom: 1.5cm !important;
            }

            /* Disable "keep-with-next" behavior */
            * {
                orphans: 2 !important;
                widows: 2 !important;
                page-break-after: auto !important;
                page-break-before: auto !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
                break-before: auto !important;
                break-after: auto !important;
            }
            
            /* Specific overrides for elements that should break */
            p, h1, h2, h3, h4, h5, h6, li, tr, div {
                page-break-after: auto !important;
                page-break-before: auto !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
                break-before: auto !important;
                break-after: auto !important;
            }
            
            /* Tables should break naturally */
            table {
                page-break-inside: auto !important;
                break-inside: auto !important;
            }
            
            td, th {
                page-break-inside: auto !important;
                break-inside: auto !important;
            }
            
            /* Override any avoid settings */
            [style*="page-break-inside: avoid"], 
            [style*="break-inside: avoid"] {
                page-break-inside: auto !important;
                break-inside: auto !important;
            }

            body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                /* Remove border */
                border: none;
                box-sizing: border-box;
                min-height: calc(100% - 2cm);
                width: calc(100% - 3.81cm);
                margin-top: 1.52cm !important;
                margin-bottom: 2cm !important;
                margin-left: 1.78cm !important;
                margin-right: 2.03cm !important;
                background-clip: padding-box;
                box-shadow: none;
            }
        }

        /* Specific dark mode styles */
        [data-bs-theme="dark"] .proposal-container {
            background-color: #333 !important;
            color: #fff !important;
            border: 1px solid #555 !important;
        }

        [data-bs-theme="dark"] .header-table td,
        [data-bs-theme="dark"] .data-table th,
        [data-bs-theme="dark"] .data-table td,
        [data-bs-theme="dark"] .signatures-table td {
            border-color: #555 !important;
            background-color: #333 !important;
            color: #fff !important;
        }

        /* Override colors for dark mode */
        @media (prefers-color-scheme: dark) {
<?php
session_start();

// Debug session information
error_log("Session data in ppas.php: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in - redirecting to login");
    header("Location: ../login.php");
    exit();
}

// Check if user is Central or a specific campus user
$isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';

// For non-Central users, their username is their campus
$userCampus = $isCentral ? '' : $_SESSION['username'];

// Store campus in session for consistency
$_SESSION['campus'] = $userCampus;

/**
 * Returns default ratings structure for cases where no ratings data exists in the database
 * 
 * @return array Default ratings with 1 for BatStateU and 2 for Others in each category
 */
function getDefaultRatings() {
    return [
        'Excellent' => [
            'BatStateU' => 50,
            'Others' => 60
        ],
        'Very Satisfactory' => [
            'BatStateU' => 70,
            'Others' => 80
        ],
        'Satisfactory' => [
            'BatStateU' => 90,
            'Others' => 100
        ],
        'Fair' => [
            'BatStateU' => 110,
            'Others' => 120
        ],
        'Poor' => [
            'BatStateU' => 130,
            'Others' => 140
        ]
    ];
}

// Add this function before the HTML section
function getSignatories($campus) {
    try {
        $conn = getConnection();
        
        // Fetch signatories data for the specified campus
        $sql = "SELECT * FROM signatories WHERE campus = :campus";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':campus', $campus);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If no results found for the campus, try a default fetch
        if (!$result) {
            error_log("No signatories found for campus: $campus - trying to fetch default");
            
            // Try to fetch any record to use as default
            $defaultSql = "SELECT * FROM signatories LIMIT 1";
            $defaultStmt = $conn->prepare($defaultSql);
            $defaultStmt->execute();
            $result = $defaultStmt->fetch(PDO::FETCH_ASSOC);
            
            // If still no records found, return structured defaults
            if (!$result) {
                error_log("No signatories found at all - using empty defaults");
                return [
                    'name1' => '',
                    'name3' => '',
                    'name4' => ''
                ];
            }
            
            error_log("Using default signatory record");
        }
        
        // Log the signatories data retrieved
        error_log('Retrieved signatories data: ' . print_r($result, true));
        
        return $result;
    } catch (Exception $e) {
        error_log('Error fetching signatories: ' . $e->getMessage());
        return [
            'name1' => '',
            'name3' => '',
            'name4' => ''
        ];
    }
}

// Get signatories for the current campus
$signatories = getSignatories(isset($_SESSION['username']) ? $_SESSION['username'] : '');

// Debug log the signatories data
error_log('Signatories data in print_narrative.php: ' . print_r($signatories, true));

// Debug: Add a comment with all field names from the database
$signatories['debug_all_fields'] = json_encode($signatories);

// Add this function at the top of the file, after any existing includes
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

// Add this function to fetch personnel data
function getPersonnelData($ppas_form_id) {
    try {
        $conn = getConnection();
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
        $stmt->bindParam(':ppas_form_id', $ppas_form_id);
        $stmt->execute();
        
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
        
        // If no personnel found, try alternative tables and fallback options
        if (empty($personnel_by_role['project_leaders']) && empty($personnel_by_role['assistant_project_leaders']) && empty($personnel_by_role['project_staff'])) {
            // Try the personnel_list or other tables if they exist
            try {
                // Check if a PPAS form exists with personnel data
                $sql = "SELECT * FROM ppas_forms WHERE id = :ppas_form_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':ppas_form_id', $ppas_form_id);
                $stmt->execute();
                $ppasForm = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($ppasForm) {
                    // Try to get project leaders and other personnel from proposal data
                    $proSql = "SELECT * FROM gad_proposals WHERE ppas_form_id = :ppas_form_id";
                    $proStmt = $conn->prepare($proSql);
                    $proStmt->bindParam(':ppas_form_id', $ppas_form_id);
                    $proStmt->execute();
                    $proposal = $proStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($proposal) {
                        // Check for project_leader_responsibilities
                        if (!empty($proposal['project_leader_responsibilities'])) {
                            $leaders = json_decode($proposal['project_leader_responsibilities'], true);
                            if (is_array($leaders)) {
                                foreach ($leaders as $leader) {
                                    $personnel_by_role['project_leaders'][] = [
                                        'name' => $leader,
                                        'role' => 'Project Leader'
                                    ];
                                }
                            }
                        }
                        
                        // Check for assistant_leader_responsibilities
                        if (!empty($proposal['assistant_leader_responsibilities'])) {
                            $assistants = json_decode($proposal['assistant_leader_responsibilities'], true);
                            if (is_array($assistants)) {
                                foreach ($assistants as $assistant) {
                                    $personnel_by_role['assistant_project_leaders'][] = [
                                        'name' => $assistant,
                                        'role' => 'Assistant Project Leader'
                                    ];
                                }
                            }
                        }
                        
                        // Check for staff_responsibilities
                        if (!empty($proposal['staff_responsibilities'])) {
                            $staff = json_decode($proposal['staff_responsibilities'], true);
                            if (is_array($staff)) {
                                foreach ($staff as $member) {
                                    $personnel_by_role['project_staff'][] = [
                                        'name' => $member,
                                        'role' => 'Staff'
                                    ];
                                }
                            }
                        }
                    }
                }
                
                // Try narrative_entries table as fallback
                $sql = "SELECT * FROM narrative_entries WHERE ppas_form_id = :ppas_form_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':ppas_form_id', $ppas_form_id);
                $stmt->execute();
                $narrative = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($narrative) {
                    // Process leader_tasks
                    if (!empty($narrative['leader_tasks'])) {
                        $leaderTasks = json_decode($narrative['leader_tasks'], true);
                        if (is_array($leaderTasks) && empty($personnel_by_role['project_leaders'])) {
                            foreach ($leaderTasks as $task) {
                                $personnel_by_role['project_leaders'][] = [
                                    'name' => $task,
                                    'role' => 'Project Leader'
                                ];
                            }
                        }
                    }
                    
                    // Process assistant_tasks
                    if (!empty($narrative['assistant_tasks'])) {
                        $assistantTasks = json_decode($narrative['assistant_tasks'], true);
                        if (is_array($assistantTasks) && empty($personnel_by_role['assistant_project_leaders'])) {
                            foreach ($assistantTasks as $task) {
                                $personnel_by_role['assistant_project_leaders'][] = [
                                    'name' => $task,
                                    'role' => 'Assistant Project Leader'
                                ];
                            }
                        }
                    }
                    
                    // Process staff_tasks
                    if (!empty($narrative['staff_tasks'])) {
                        $staffTasks = json_decode($narrative['staff_tasks'], true);
                        if (is_array($staffTasks) && empty($personnel_by_role['project_staff'])) {
                            foreach ($staffTasks as $task) {
                                $personnel_by_role['project_staff'][] = [
                                    'name' => $task,
                                    'role' => 'Staff'
                                ];
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                error_log('Error fetching alternative personnel data: ' . $e->getMessage());
            }
        }
        
        return $personnel_by_role;
    } catch (Exception $e) {
        error_log('Error fetching personnel data: ' . $e->getMessage());
        return null;
    }
}

// Add this function to get narrative data with filtered extension_service_agenda = 1
function getNarrativeData($ppas_form_id) {
    try {
        $conn = getConnection();
        
        // Get database schema info to be more adaptive
        $table_info = [];
        
        // Check if narrative_entries table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'narrative_entries'");
        $table_info['narrative_entries_exists'] = ($stmt->rowCount() > 0);
        
        // Get columns in narrative_entries
        if ($table_info['narrative_entries_exists']) {
            $stmt = $conn->query("DESCRIBE narrative_entries");
            $table_info['narrative_entries_columns'] = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $table_info['narrative_entries_columns'][] = $row['Field'];
            }
            
            // Log the columns found
            error_log("Columns in narrative_entries: " . implode(", ", $table_info['narrative_entries_columns']));
        }
        
        // Check if ppas_entries table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'ppas_entries'");
        $table_info['ppas_entries_exists'] = ($stmt->rowCount() > 0);
        
        // Get columns in ppas_entries if it exists
        if ($table_info['ppas_entries_exists']) {
            $stmt = $conn->query("DESCRIBE ppas_entries");
            $table_info['ppas_entries_columns'] = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $table_info['ppas_entries_columns'][] = $row['Field'];
            }
            
            // Log the columns found
            error_log("Columns in ppas_entries: " . implode(", ", $table_info['ppas_entries_columns']));
        }
        
        // Initialize the response array with defaults
        $narrative = [
            'general_objectives' => '',
            'specific_objectives' => [],
            // Add new fields with default empty values
            'background' => '',
            'participants' => '',
            'topics' => '',
            'results' => '',
            'lessons' => '',
            'what_worked' => '',
            'issues' => '',
            'recommendations' => '',
            'activity_ratings' => [],
            'timeliness_ratings' => [],
            'activity_images' => []
        ];

        // Debug to error log - important for tracking
        error_log("Getting narrative data for PPAS form ID: $ppas_form_id");
        
        // First try to get data from ppas_entries if it exists
        if ($table_info['ppas_entries_exists']) {
            try {
                error_log("Checking ppas_entries table for ppas_form_id: $ppas_form_id");
                $ppas_entries_sql = "SELECT * FROM ppas_entries WHERE ppas_form_id = :ppas_form_id LIMIT 1";
                $ppas_entries_stmt = $conn->prepare($ppas_entries_sql);
                $ppas_entries_stmt->bindParam(':ppas_form_id', $ppas_form_id);
                $ppas_entries_stmt->execute();
                $ppas_entries_data = $ppas_entries_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($ppas_entries_data) {
                    error_log("Found data in ppas_entries for ppas_form_id: $ppas_form_id");
                    
                    // Extract activity_ratings from ppas_entries if available
                    if (array_key_exists('activity_ratings', $ppas_entries_data) && !empty($ppas_entries_data['activity_ratings'])) {
                        try {
                            $activity_ratings = $ppas_entries_data['activity_ratings'];
                            if (is_string($activity_ratings)) {
                                $parsed_activity_ratings = json_decode($activity_ratings, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $narrative['activity_ratings'] = $parsed_activity_ratings;
                                    error_log("Successfully parsed activity_ratings JSON from ppas_entries: " . substr($activity_ratings, 0, 100) . "...");
                                } else {
                                    error_log("JSON parse error for activity_ratings from ppas_entries: " . json_last_error_msg());
                                }
                            } else if (is_array($activity_ratings)) {
                                $narrative['activity_ratings'] = $activity_ratings;
                                error_log("Used array data for activity_ratings from ppas_entries");
                            }
                        } catch (Exception $e) {
                            error_log("Exception processing activity_ratings from ppas_entries: " . $e->getMessage());
                        }
                    }
                    
                    // Extract timeliness_ratings from ppas_entries if available
                    if (array_key_exists('timeliness_ratings', $ppas_entries_data) && !empty($ppas_entries_data['timeliness_ratings'])) {
                        try {
                            $timeliness_ratings = $ppas_entries_data['timeliness_ratings'];
                            if (is_string($timeliness_ratings)) {
                                $parsed_timeliness_ratings = json_decode($timeliness_ratings, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $narrative['timeliness_ratings'] = $parsed_timeliness_ratings;
                                    error_log("Successfully parsed timeliness_ratings JSON from ppas_entries: " . substr($timeliness_ratings, 0, 100) . "...");
                                } else {
                                    error_log("JSON parse error for timeliness_ratings from ppas_entries: " . json_last_error_msg());
                                }
                            } else if (is_array($timeliness_ratings)) {
                                $narrative['timeliness_ratings'] = $timeliness_ratings;
                                error_log("Used array data for timeliness_ratings from ppas_entries");
                            }
                        } catch (Exception $e) {
                            error_log("Exception processing timeliness_ratings from ppas_entries: " . $e->getMessage());
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Error fetching from ppas_entries: " . $e->getMessage());
            }
        }
        
        // Continue with existing code to get the main activity data from ppas_forms
        try {
            $ppas_sql = "SELECT * FROM ppas_forms WHERE id = :id";
            $ppas_stmt = $conn->prepare($ppas_sql);
            $ppas_stmt->bindParam(':id', $ppas_form_id);
            $ppas_stmt->execute();
            $ppas_data = $ppas_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($ppas_data) {
                error_log("Found data in ppas_forms for ID $ppas_form_id");
                // Store key PPAS data for later use
                $ppas_activity = $ppas_data['activity'] ?? ($ppas_data['activity_title'] ?? 'Untitled Activity');
                $ppas_year = $ppas_data['year'] ?? date('Y');
                $ppas_campus = $ppas_data['campus'] ?? '';
                
                error_log("PPAS data - Activity: $ppas_activity, Year: $ppas_year, Campus: $ppas_campus");
                
                // Set some default objectives based on ppas_forms data if needed
                if (empty($narrative['general_objectives'])) {
                    $narrative['general_objectives'] = "To successfully implement " . $ppas_activity;
                }
                if (empty($narrative['specific_objectives'])) {
                    $narrative['specific_objectives'] = ["To achieve the goals of " . $ppas_activity];
                }
                
                // Now look for matching narrative_entries using ppas_form_id or activity title
                try {
                    // Variable to track if we found a matching narrative entry
                    $foundMatch = false;
                    $entry_data = null;
                    
                    // First try to match by ppas_form_id if the column exists
                    if (in_array('ppas_form_id', $table_info['narrative_entries_columns'])) {
                        error_log("Found ppas_form_id column in narrative_entries, attempting direct match");
                        
                        $entry_sql = "SELECT * FROM narrative_entries WHERE ppas_form_id = :ppas_id LIMIT 1";
                        $entry_stmt = $conn->prepare($entry_sql);
                        $entry_stmt->bindParam(':ppas_id', $ppas_form_id);
                        $entry_stmt->execute();
                        $entry_data = $entry_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($entry_data) {
                            error_log("Found matching narrative_entries record by ppas_form_id!");
                            $foundMatch = true;
                        }
                    } else {
                        error_log("No ppas_form_id column found in narrative_entries table");
                    }
                    
                    // If no match by ppas_form_id, try by exact title
                    if (!$foundMatch && !empty($ppas_activity)) {
                        error_log("Attempting exact title match for: '$ppas_activity'");
                        $title_sql = "SELECT * FROM narrative_entries WHERE title = :title AND campus = :campus LIMIT 1";
                        $title_stmt = $conn->prepare($title_sql);
                        $title_stmt->bindParam(':title', $ppas_activity);
                        $title_stmt->bindParam(':campus', $ppas_campus);
                        $title_stmt->execute();
                        $entry_data = $title_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($entry_data) {
                            error_log("Found matching narrative_entries record by exact title match!");
                            $foundMatch = true;
                        }
                    }
                    
                    // If still no match, try by title similarity
                    if (!$foundMatch && !empty($ppas_activity)) {
                        error_log("Attempting title-based LIKE match for: '$ppas_activity'");
                        $title_sql = "SELECT * FROM narrative_entries WHERE title LIKE :title AND campus = :campus LIMIT 1";
                        $title_stmt = $conn->prepare($title_sql);
                        $title_param = '%' . $ppas_activity . '%';
                        $title_stmt->bindParam(':title', $title_param);
                        $title_stmt->bindParam(':campus', $ppas_campus);
                        $title_stmt->execute();
                        $entry_data = $title_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($entry_data) {
                            error_log("Found matching narrative_entries record by title LIKE match!");
                            $foundMatch = true;
                        }
                    }
                    
                    // If still no match, try keyword matching (split title into words and find any match)
                    if (!$foundMatch && !empty($ppas_activity)) {
                        error_log("Attempting keyword matching for: '$ppas_activity'");
                        $keywords = preg_split('/\s+/', $ppas_activity);
                        
                        // Build a query with multiple LIKE conditions
                        $conditions = [];
                        $params = [];
                        $index = 0;
                        
                        foreach ($keywords as $keyword) {
                            if (strlen($keyword) > 3) { // Only use keywords longer than 3 chars
                                $paramName = ":keyword" . $index;
                                $conditions[] = "title LIKE $paramName";
                                $params[$paramName] = '%' . $keyword . '%';
                                $index++;
                            }
                        }
                        
                        if (!empty($conditions)) {
                            $keyword_sql = "SELECT * FROM narrative_entries WHERE (" . implode(" OR ", $conditions) . ") AND campus = :campus LIMIT 1";
                            $keyword_stmt = $conn->prepare($keyword_sql);
                            $keyword_stmt->bindParam(':campus', $ppas_campus);
                            
                            foreach ($params as $param => $value) {
                                $keyword_stmt->bindValue($param, $value);
                            }
                            
                            $keyword_stmt->execute();
                            $entry_data = $keyword_stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($entry_data) {
                                error_log("Found matching narrative_entries record by keyword matching!");
                                $foundMatch = true;
                            }
                        }
                    }
                    
                    // Process the found narrative entry if any
                    if ($foundMatch && $entry_data) {
                        error_log("Processing matched narrative entry data with ID: " . ($entry_data['id'] ?? 'unknown'));
                        
                        // Map fields from narrative_entries to our narrative array - check if each column exists
                        $narrative_field_mappings = [
                            'background' => ['background', 'activity_background'],
                            'participants' => ['participants', 'participant_description', 'activity_participants'],
                            'topics' => ['topics', 'discussion_topics', 'activity_topics'],
                            'results' => ['results', 'activity_results', 'outputs_outcomes', 'expected_results'],
                            'lessons' => ['lessons', 'lessons_learned', 'activity_lessons'],
                            'what_worked' => ['what_worked', 'worked_not_worked'],
                            'issues' => ['issues', 'issues_concerns', 'issues_addressed'],
                            'recommendations' => ['recommendations', 'activity_recommendations']
                        ];
                        
                        // Check and map fields
                        foreach ($narrative_field_mappings as $narrative_field => $possible_db_fields) {
                            foreach ($possible_db_fields as $db_field) {
                                if (array_key_exists($db_field, $entry_data) && !empty($entry_data[$db_field])) {
                                    $narrative[$narrative_field] = $entry_data[$db_field];
                                    error_log("Mapped $db_field to $narrative_field: " . substr($entry_data[$db_field], 0, 50) . "...");
                                    break;
                                }
                            }
                        }
                        
                        // Process photos - check for various possible column names
                        $photo_field_names = ['photo_path', 'photo_paths', 'activity_images', 'images'];
                        foreach ($photo_field_names as $field) {
                            if (array_key_exists($field, $entry_data) && !empty($entry_data[$field])) {
                                $photo_data = $entry_data[$field];
                                
                                if (is_string($photo_data)) {
                                    try {
                                        // Try to parse as JSON
                                        $parsed_photos = json_decode($photo_data, true);
                                        if (is_array($parsed_photos)) {
                                            $narrative['activity_images'] = $parsed_photos;
                                        } else {
                                            // Single path as string
                                            $narrative['activity_images'] = [$photo_data];
                                        }
                                    } catch (Exception $e) {
                                        // If not valid JSON, treat as a single photo path
                                        $narrative['activity_images'] = [$photo_data];
                                    }
                                } elseif (is_array($photo_data)) {
                                    $narrative['activity_images'] = $photo_data;
                                }
                            }
                        }
                        
                        // DIRECT ACCESS TO RATINGS FIELDS
                        // Process activity_ratings directly from the field
                        if (array_key_exists('activity_ratings', $entry_data) && !empty($entry_data['activity_ratings'])) {
                            $rating_data = $entry_data['activity_ratings'];
                            
                            if (is_string($rating_data)) {
                                try {
                                    $parsed_ratings = json_decode($rating_data, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($parsed_ratings)) {
                                        error_log("Successfully parsed activity_ratings JSON: " . substr($rating_data, 0, 100) . "...");
                                        $narrative['activity_ratings'] = $parsed_ratings;
                                    } else {
                                        error_log("JSON parse error for activity_ratings: " . json_last_error_msg() . " - Data: " . substr($rating_data, 0, 100) . "...");
                                    }
                                } catch (Exception $e) {
                                    error_log("Exception parsing activity_ratings JSON: " . $e->getMessage());
                                }
                            } elseif (is_array($rating_data)) {
                                $narrative['activity_ratings'] = $rating_data;
                                error_log("Used array data for activity_ratings");
                            }
                        }

                        // Process timeliness_ratings directly from the field
                        if (array_key_exists('timeliness_ratings', $entry_data) && !empty($entry_data['timeliness_ratings'])) {
                            $rating_data = $entry_data['timeliness_ratings'];
                            
                            if (is_string($rating_data)) {
                                try {
                                    $parsed_ratings = json_decode($rating_data, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($parsed_ratings)) {
                                        error_log("Successfully parsed timeliness_ratings JSON: " . substr($rating_data, 0, 100) . "...");
                                        $narrative['timeliness_ratings'] = $parsed_ratings;
                                    } else {
                                        error_log("JSON parse error for timeliness_ratings: " . json_last_error_msg() . " - Data: " . substr($rating_data, 0, 100) . "...");
                                    }
                                } catch (Exception $e) {
                                    error_log("Exception parsing timeliness_ratings JSON: " . $e->getMessage());
                                }
                            } elseif (is_array($rating_data)) {
                                $narrative['timeliness_ratings'] = $rating_data;
                                error_log("Used array data for timeliness_ratings");
                            }
                        }
                        
                        // Process ratings data from other field names (backup approach)
                        $ratings_field_mappings = [
                            'activity_ratings' => ['activity_rating', 'evaluation_ratings'],
                            'timeliness_ratings' => ['timeliness_rating', 'timeliness']
                        ];
                        
                        // Check for ratings data in various field formats
                        foreach ($ratings_field_mappings as $ratings_field => $possible_db_fields) {
                            // Only process if the target ratings field is still empty
                            if (empty($narrative[$ratings_field])) {
                                foreach ($possible_db_fields as $db_field) {
                                    if (array_key_exists($db_field, $entry_data) && !empty($entry_data[$db_field])) {
                                        // Try to handle various formats of rating data
                                        $rating_data = $entry_data[$db_field];
                                        
                                        if (is_string($rating_data)) {
                                            try {
                                                // Try to parse as JSON
                                                $parsed_ratings = json_decode($rating_data, true);
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($parsed_ratings)) {
                                                    error_log("Successfully parsed $db_field JSON: " . substr($rating_data, 0, 100) . "...");
                                                    $narrative[$ratings_field] = $parsed_ratings;
                                                    break;
                                                } else {
                                                    error_log("JSON parse error for $db_field: " . json_last_error_msg() . " - Data: " . substr($rating_data, 0, 100) . "...");
                                                }
                                            } catch (Exception $e) {
                                                error_log("Exception parsing $db_field JSON: " . $e->getMessage());
                                            }
                                        } elseif (is_array($rating_data)) {
                                            $narrative[$ratings_field] = $rating_data;
                                            error_log("Used array data for $ratings_field from $db_field");
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Handle special case for evaluation which might contain both ratings
                        if ((empty($narrative['activity_ratings']) || empty($narrative['timeliness_ratings'])) && 
                            array_key_exists('evaluation', $entry_data) && !empty($entry_data['evaluation'])) {
                            try {
                                $evaluation = $entry_data['evaluation'];
                                if (is_string($evaluation)) {
                                    $parsed_evaluation = json_decode($evaluation, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($parsed_evaluation)) {
                                        error_log("Successfully parsed evaluation JSON");
                                        
                                        // Check for expected structure
                                        if (empty($narrative['activity_ratings']) && isset($parsed_evaluation['activity']) && is_array($parsed_evaluation['activity'])) {
                                            $narrative['activity_ratings'] = $parsed_evaluation['activity'];
                                            error_log("Extracted activity ratings from evaluation");
                                        }
                                        
                                        if (empty($narrative['timeliness_ratings']) && isset($parsed_evaluation['timeliness']) && is_array($parsed_evaluation['timeliness'])) {
                                            $narrative['timeliness_ratings'] = $parsed_evaluation['timeliness'];
                                            error_log("Extracted timeliness ratings from evaluation");
                                        }
                                    }
                                }
                            } catch (Exception $e) {
                                error_log("Exception parsing evaluation JSON: " . $e->getMessage());
                            }
                        }
                        
                        // If no rating data found, use default ratings
                        if (empty($narrative['activity_ratings'])) {
                            $narrative['activity_ratings'] = getDefaultRatings();
                            error_log("No activity ratings found, using defaults");
                        }
                        if (empty($narrative['timeliness_ratings'])) {
                            $narrative['timeliness_ratings'] = getDefaultRatings();
                            error_log("No timeliness ratings found, using defaults");
                        }
                        
                        // Debug the final narrative data being returned
                        error_log("Final narrative data from narrative_entries: " . json_encode(array_keys($narrative)));
                    } else {
                        error_log("No matching narrative_entries record found for PPAS form ID: $ppas_form_id or title: $ppas_activity");
                    }
                } catch (Exception $e) {
                    error_log("Exception in narrative_entries lookup: " . $e->getMessage());
                }
            } else {
                error_log("PPAS form with ID $ppas_form_id not found");
            }
        } catch (Exception $e) {
            error_log("Exception in main PPAS form lookup: " . $e->getMessage());
        }
        
        // Final default values for any fields that are still missing
        return [
            'general_objectives' => $narrative['general_objectives'] ?: '',
            'specific_objectives' => $narrative['specific_objectives'] ?: [],
            'background' => $narrative['background'] ?: '',
            'participants' => $narrative['participants'] ?: '',
            'topics' => $narrative['topics'] ?: '',
            'results' => $narrative['results'] ?: '',
            'lessons' => $narrative['lessons'] ?: '',
            'what_worked' => $narrative['what_worked'] ?: '',
            'issues' => $narrative['issues'] ?: '',
            'recommendations' => $narrative['recommendations'] ?: '',
            'activity_ratings' => $narrative['activity_ratings'] ?: [],
            'timeliness_ratings' => $narrative['timeliness_ratings'] ?: [],
            'activity_images' => $narrative['activity_images'] ?: []
        ];
        
    } catch (Exception $e) {
        error_log("Overall exception in getNarrativeData: " . $e->getMessage());
        return [
            'general_objectives' => '',
            'specific_objectives' => [],
            'background' => '',
            'participants' => '',
            'topics' => '',
            'results' => '',
            'lessons' => '',
            'what_worked' => '',
            'issues' => '',
            'recommendations' => '',
            'activity_ratings' => [],
            'timeliness_ratings' => [],
            'activity_images' => []
        ];
    }
}

// Get the PPAS form ID from the URL for direct querying
$ppas_form_id = isset($_GET['id']) ? $_GET['id'] : null;

// Find objective fields if we have a PPAS form ID
if ($ppas_form_id) {
    $objective_fields = findObjectiveFields($ppas_form_id);
}

// Check if user is logged in, etc...
// ... existing code ...
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Narrative Reports - GAD System</title>
    <link rel="icon" type="image/x-icon" href="../images/Batangas_State_Logo.ico">
    <script src="../js/common.js"></script>
    <!-- Immediate theme loading to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = savedTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        })();
    </script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --accent-color: #6a1b9a;
            --accent-hover: #4a148c;
        }
        
        /* Light Theme Variables */
        [data-bs-theme="light"] {
            --bg-primary: #f0f0f0;
            --bg-secondary: #e9ecef;
            --sidebar-bg: #ffffff;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --hover-color: rgba(106, 27, 154, 0.1);
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --horizontal-bar: rgba(33, 37, 41, 0.125);
            --input-placeholder: rgba(33, 37, 41, 0.75);
            --input-bg: #ffffff;
            --input-text: #212529;
            --card-title: #212529;
            --scrollbar-thumb: rgba(156, 39, 176, 0.4);
            --scrollbar-thumb-hover: rgba(156, 39, 176, 0.7);
        }

        /* Dark Theme Variables */
        [data-bs-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --sidebar-bg: #2d2d2d;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --hover-color: #8a4ebd;
            --card-bg: #2d2d2d;
            --border-color: #404040;
            --horizontal-bar: rgba(255, 255, 255, 0.1);
            --input-placeholder: rgba(255, 255, 255, 0.7);
            --input-bg: #404040;
            --input-text: #ffffff;
            --card-title: #ffffff;
            --scrollbar-thumb: #6a1b9a;
            --scrollbar-thumb-hover: #9c27b0;
            --accent-color: #9c27b0;
            --accent-hover: #7b1fa2;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
            opacity: 1;
            transition: opacity 0.05s ease-in-out; /* Changed from 0.05s to 0.01s - make it super fast */
        }

        body.fade-out {
    opacity: 0;
}

        

        .sidebar {
            width: var(--sidebar-width);
            height: calc(100vh - 40px);
            position: fixed;
            left: 20px;
            top: 20px;
            padding: 20px;
            background: var(--sidebar-bg);
            color: var(--text-primary);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 5px 0 15px rgba(0,0,0,0.05), 0 5px 15px rgba(0,0,0,0.05);
            z-index: 1;
        }

        .main-content {
    margin-left: calc(var(--sidebar-width) + 20px);
    padding: 15px;
    height: calc(100vh - 30px);
    max-height: calc(100vh - 30px);
    background: var(--bg-primary);
    border-radius: 20px;
    position: relative;
    overflow-y: auto;
    scrollbar-width: none;  /* Firefox */
    -ms-overflow-style: none;  /* IE and Edge */
}

/* Hide scrollbar for Chrome, Safari and Opera */
.main-content::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for Chrome, Safari and Opera */
body::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for Firefox */
html {
    scrollbar-width: none;
}

        .nav-link {
            color: var(--text-primary);
            padding: 12px 15px;
            border-radius: 12px;
            margin-bottom: 5px;
            position: relative;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 12px;
        }

        .nav-link:hover {
            background: var(--hover-color);
            color: white;
        }

        /* Restore light mode hover color */
        [data-bs-theme="light"] .nav-link:hover {
            color: var(--accent-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-menu .dropdown-item:hover {
            color: var(--accent-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--accent-color) !important;
        }

        .nav-link.active {
            color: var(--accent-color);
            position: relative;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: var(--accent-color);
            border-radius: 0 2px 2px 0;
        }

        /* Add hover state for active nav links in dark mode */
        [data-bs-theme="dark"] .nav-link.active:hover {
            color: white;
        }

        .nav-item {
            position: relative;
        }

        .nav-item .dropdown-menu {
            position: static !important;
            background: var(--sidebar-bg);
            border: 1px solid var(--border-color);
            padding: 8px 0;
            margin: 5px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            min-width: 200px;
            transform: none !important;
            display: none;
            overflow: visible;
            max-height: none;
        }

        /* Dropdown submenu styles */
        /* Dropdown submenu styles */
.dropdown-submenu {
    position: relative;
}

.dropdown-submenu .dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: -8px;
    margin-left: 1px;
    border-radius: 0 6px 6px 6px;
    display: none;
}

/* Add click-based display */
.dropdown-submenu.show > .dropdown-menu {
    display: block;
}

.dropdown-submenu > a:after {
    display: block;
    content: " ";
    float: right;
    width: 0;
    height: 0;
    border-color: transparent;
    border-style: solid;
    border-width: 5px 0 5px 5px;
    border-left-color: var(--text-primary);
    margin-top: 5px;
    margin-right: -10px;
}

/* Update hover effect for arrow */
.dropdown-submenu.show > a:after {
    border-left-color: var(--accent-color);
}

/* Mobile styles for dropdown submenu */
@media (max-width: 991px) {
    .dropdown-submenu .dropdown-menu {
        position: static !important;
        left: 0;
        margin-left: 20px;
        margin-top: 0;
        border-radius: 0;
        border-left: 2px solid var(--accent-color);
    }
    
    .dropdown-submenu > a:after {
        transform: rotate(90deg);
        margin-top: 8px;
    }
}
        
        /* End of dropdown submenu styles */

        .nav-item .dropdown-menu.show {
            display: block;
        }

        .nav-item .dropdown-menu .dropdown-item {
            padding: 8px 48px;
            color: var(--text-primary);
            position: relative;
            opacity: 0.85;
            background: transparent;
        }

        .nav-item .dropdown-menu .dropdown-item::before {
            content: '•';
            position: absolute;
            left: 35px;
            color: var(--accent-color);
        }

        .nav-item .dropdown-menu .dropdown-item:hover {
            background: var(--hover-color);
            color: white;
            opacity: 1;
        }

        [data-bs-theme="light"] .nav-item .dropdown-menu .dropdown-item:hover {
            color: var(--accent-color);
        }

        .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: white !important;
            background: var(--hover-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--accent-color) !important;
        }

        .logo-container {
            padding: 20px 0;
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-title {
            font-size: 24px;
            font-weight: bold;
            color: var(--text-primary);
            margin-bottom: 15px;
        }

        .logo-image {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            margin-bottom: -25px;
        }

        .logo-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .datetime-container {
            text-align: center;
            padding: 15px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--horizontal-bar);
        }

        .datetime-container .date {
            font-size: 1.1rem;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .datetime-container .time {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--accent-color);
        }

        .nav-content {
            flex-grow: 1;
            overflow-y: auto;
            max-height: calc(100vh - 470px);
            margin-bottom: 20px;
            padding-right: 5px;
            scrollbar-width: thin;
            scrollbar-color: rgba(106, 27, 154, 0.4) transparent;
            overflow-x: hidden; 
        }

        .nav-content::-webkit-scrollbar {
            width: 5px;
        }

        .nav-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .nav-content::-webkit-scrollbar-thumb {
            background-color: rgba(106, 27, 154, 0.4);
            border-radius: 1px;
        }

        .nav-content::-webkit-scrollbar-thumb:hover {
            background-color: rgba(106, 27, 154, 0.7);
        }

        .nav-link:focus,
        .dropdown-toggle:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        .dropdown-menu {
            outline: none !important;
            border: none !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        }

        .dropdown-item:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        /* Bottom controls container */
        .bottom-controls {
            position: absolute;
            bottom: 20px;
            width: calc(var(--sidebar-width) - 40px);
            display: flex;
            gap: 5px;
            align-items: center;
        }

        /* Logout button styles */
        .logout-button {
            flex: 1;
            background: var(--bg-primary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Theme switch button */
        .theme-switch-button {
            width: 46.5px;
            height: 50px;
            padding: 12px 0;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

                /* Light theme specific styles for bottom controls */
                [data-bs-theme="light"] .logout-button,
        [data-bs-theme="light"] .theme-switch-button {
            background: #f2f2f2;
            border-width: 1.5px;
        }

        /* Hover effects */
        .logout-button:hover,
        .theme-switch-button:hover {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .logout-button:active,
        .theme-switch-button:active {
            transform: translateY(0);
            box-shadow: 
                0 4px 6px rgba(0, 0, 0, 0.1),
                0 2px 4px rgba(0, 0, 0, 0.06),
                inset 0 1px 2px rgba(255, 255, 255, 0.2);
        }

        /* Theme switch button icon size */
        .theme-switch-button i {
            font-size: 1rem; 
        }

        .theme-switch-button:hover i {
            transform: scale(1.1);
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 1.5rem;
        }

        .page-title i {
            color: var(--accent-color);
            font-size: 2.2rem;
        }

        .page-title h2 {
            margin: 0;
            font-weight: 600;
        }

        .show>.nav-link {
            background: transparent !important;
            color: var(--accent-color) !important;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 991px) {
            :root {
                --sidebar-width: 240px;
            }

            body {
                padding: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
                left: 0;
                top: 0;
                height: 100vh;
                position: fixed;
                padding-top: 70px;
                border-radius: 0;
                box-shadow: 5px 0 25px rgba(0,0,0,0.1);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 70px 15px 15px 15px;
                border-radius: 0;
                box-shadow: none;
            }

            .mobile-nav-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
                background: var(--card-bg);
                border: none;
                border-radius: 8px;
                color: var(--text-primary);
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                cursor: pointer;
            }

            .mobile-nav-toggle:hover {
                background: var(--hover-color);
                color: var(--accent-color);
            }

            body.sidebar-open {
                overflow: hidden;
            }

            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }

            .sidebar-backdrop.show {
                display: block;
            }

            .theme-switch {
                position: fixed;
                bottom: 30px;
                right: 30px;
            }

        }

        @media (max-width: 576px) {
            :root {
                --sidebar-width: 100%;
            }

            .sidebar {
                left: 0;
                top: 0;
                width: 100%;
                height: 100vh;
                padding-top: 60px;
            }

            .mobile-nav-toggle {
                width: 40px;
                height: 40px;
                top: 10px;
                left: 10px;
            }

            .theme-switch {
                top: 10px;
                right: 10px;
            }

            .theme-switch-button {
                padding: 8px 15px;
            }

            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                margin-top: 10px;
            }

            .page-title h2 {
                font-size: 1.5rem;
            }
        }

        /* Modern Card Styles */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            position: relative;
            min-height: 465px;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        #ppasForm {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        #ppasForm.row {
            flex: 1;
        }

        #ppasForm .col-12.text-end {
            margin-top: auto !important;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        /* Dark Theme Colors */
        [data-bs-theme="dark"] {
            --dark-bg: #212529;
            --dark-input: #2b3035;
            --dark-text: #e9ecef;
            --dark-border: #495057;
            --dark-sidebar: #2d2d2d;
        }

        /* Dark mode card */
        [data-bs-theme="dark"] .card {
            background-color: var(--dark-sidebar) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .card-header {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
            overflow: hidden;
        }

        /* Fix for card header corners */
        .card-header {
            border-top-left-radius: inherit !important;
            border-top-right-radius: inherit !important;
            padding-bottom: 0.5rem !important;
        }

        .card-title {
            margin-bottom: 0;
        }

        /* Form Controls */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1 1 200px;
        }


        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 25px;
            margin-bottom: 20px;
        }

        .btn-icon {
            width: 45px;
            height: 45px;
            padding: 0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-icon i {
            font-size: 1.2rem;
        }

        /* Add button */
        #addBtn {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        #addBtn:hover {
            background: #198754;
            color: white;
        }

        /* Edit button */
        #editBtn {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        #editBtn:hover {
            background: #ffc107;
            color: white;
        }

        /* Edit button in cancel mode */
        #editBtn.editing {
            background: rgba(220, 53, 69, 0.1) !important;
            color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        #editBtn.editing:hover {
            background: #dc3545 !important;
            color: white !important;
        }

        /* Delete button */
        #deleteBtn {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        #deleteBtn:hover {
            background: #dc3545;
            color: white;
        }

        /* Delete button disabled state */
        #deleteBtn.disabled {
            background: rgba(108, 117, 125, 0.1) !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        /* Update button state */
        #addBtn.btn-update {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        #addBtn.btn-update:hover {
            background: #198754;
            color: white;
        }

#viewBtn {
    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

#viewBtn:hover {
    background: #0d6efd;
    color: white;
}

/* Optional: Add disabled state for view button */
#viewBtn.disabled {
    background: rgba(108, 117, 125, 0.1) !important;
    color: #6c757d !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
}

/* Add these styles for disabled buttons */
.btn-disabled {
    border-color: #6c757d !important;
    background: rgba(108, 117, 125, 0.1) !important;
    color: #6c757d !important;
    opacity: 0.65 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
}

/* Dark mode styles */
[data-bs-theme="dark"] .btn-disabled {
    background-color: #495057 !important;
    border-color: #495057 !important;
    color: #adb5bd !important;
}

.swal-blur-container {
    backdrop-filter: blur(5px);
}

/* Add print-specific styles */
@media print {
    @page {
        size: 8.5in 13in;
        margin-top: 1.52cm;
        margin-bottom: 2cm;
        margin-left: 1.78cm;
        margin-right: 2.03cm;
        border-top: 3px solid black !important;
        border-bottom: 3px solid black !important;
    }
    
    /* Force ALL colors to black - no exceptions */
    *, p, span, div, td, th, li, ul, ol, strong, em, b, i, a, h1, h2, h3, h4, h5, h6,
    [style*="color:"], [style*="color="], [style*="color :"], [style*="color ="],
    [style*="color: brown"], [style*="color: blue"], [style*="color: red"], 
    .brown-text, .blue-text, .sustainability-plan, .sustainability-plan p, .sustainability-plan li,
    .signature-label, .signature-position {
        color: black !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    
    /* First page footer with tracking number and page number */
    @page:first {
        margin-top: 1.52cm;
        margin-bottom: 2cm;
        margin-left: 1.78cm;
        margin-right: 2.03cm;
        border-top: 3px solid black !important;
        border-bottom: 3px solid black !important;
    }
    
    /* Ensure proper spacing for the footer */
    .proposal-container {
        margin-bottom: 1.5cm !important;
    }

    /* Disable "keep-with-next" behavior */
    * {
        orphans: 2 !important;
        widows: 2 !important;
        page-break-after: auto !important;
        page-break-before: auto !important;
        page-break-inside: auto !important;
        break-inside: auto !important;
        break-before: auto !important;
        break-after: auto !important;
    }
    
    /* Specific overrides for elements that should break */
    p, h1, h2, h3, h4, h5, h6, li, tr, div {
        page-break-after: auto !important;
        page-break-before: auto !important;
        page-break-inside: auto !important;
        break-inside: auto !important;
        break-before: auto !important;
        break-after: auto !important;
    }
    
    /* Tables should break naturally */
    table {
        page-break-inside: auto !important;
        break-inside: auto !important;
    }
    
    td, th {
        page-break-inside: auto !important;
        break-inside: auto !important;
    }
    
    /* Override any avoid settings */
    [style*="page-break-inside: avoid"], 
    [style*="break-inside: avoid"] {
        page-break-inside: auto !important;
        break-inside: auto !important;
    }

    body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        /* Remove border */
        border: none;
        box-sizing: border-box;
        min-height: calc(100% - 2cm);
        width: calc(100% - 3.81cm);
        margin-top: 1.52cm !important;
        margin-bottom: 2cm !important;
        margin-left: 1.78cm !important;
        margin-right: 2.03cm !important;
        background-clip: padding-box;
        box-shadow: none;
    }
}

/* Add these styles for compact form */
.compact-form .form-group {
    margin-bottom: 0.5rem !important;
}

.compact-form label {
    margin-bottom: 0.25rem !important;
    font-size: 0.85rem !important;
}

.compact-form .form-control-sm {
    height: calc(1.5em + 0.5rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.compact-form .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Additional styles to match get_gpb_report.php */
.compact-form select.form-control-sm,
.compact-form input.form-control-sm {
    font-size: 1rem !important;
    height: 38px !important;
    padding: 0.375rem 0.75rem !important;
}

#campus, #year, #proposal {
    font-size: 1rem !important;
    height: 38px !important;
}

.compact-form .btn-sm {
    font-size: 1rem !important;
    height: 38px !important;
    padding: 0.375rem 0.75rem !important;
}

.form-group label, .form-label {
    font-size: 1rem !important;
    margin-bottom: 0.5rem !important;
}

/* Make the card more compact */
.card {
    min-height: auto !important;
}
    </style>
</head>
<body>
    <script>
        // Immediately disable all buttons as soon as the page loads
        window.onload = function() {
            for (let quarter = 1; quarter <= 4; quarter++) {
                const printBtn = document.getElementById(`printBtn${quarter}`);
                const exportBtn = document.getElementById(`exportBtn${quarter}`);
                if (printBtn) printBtn.disabled = true;
                if (exportBtn) exportBtn.disabled = true;
            }
        };
    </script>

    <!-- Mobile Navigation Toggle -->
    <button class="mobile-nav-toggle d-lg-none">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Backdrop -->
    <div class="sidebar-backdrop"></div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo-title">GAD SYSTEM</div>
            <div class="logo-image">
                <img src="../images/Batangas_State_Logo.png" alt="Batangas State Logo">
            </div>
        </div>
        <div class="datetime-container">
            <div class="date" id="current-date"></div>
            <div class="time" id="current-time"></div>
        </div>
        <div class="nav-content">
            <nav class="nav flex-column">
                <a href="../dashboard/dashboard.php" class="nav-link">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users me-2"></i> Staff
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../academic_rank/academic.php">Academic Rank</a></li>
                        <li><a class="dropdown-item" href="../personnel_list/personnel_list.php">Personnel List</a></li>
                        <li><a class="dropdown-item" href="../signatory/sign.php">Signatory</a></li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="formsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-alt me-2"></i> Forms
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../target_forms/target.php">Target Form</a></li>
                        <li><a class="dropdown-item" href="../gbp_forms/gbp.php">GPB Form</a></li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" id="ppasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                PPAs Form
                            </a>
                            <ul class="dropdown-menu dropdown-submenu" aria-labelledby="ppasDropdown">
                                <li><a class="dropdown-item" href="../ppas_form/ppas.php">Main PPAs Form</a></li>
                                <li><a class="dropdown-item" href="../ppas_proposal/gad_proposal.php">GAD Proposal Form</a></li>
                                <li><a class="dropdown-item" href="../narrative/narrative.php">Narrative Form</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i> Reports
                    </a>
                    <ul class="dropdown-menu">                       
                    <li><a class="dropdown-item" href="../gpb_reports/gbp_reports.php">Annual GPB Reports</a></li>
                        <li><a class="dropdown-item" href="../ppas_reports/ppas_report.php">Quarterly PPAs Reports</a></li>
                        <li><a class="dropdown-item" href="../ps_atrib_reports/ps.php">PSA Reports</a></li>
                        <li><a class="dropdown-item" href="../ppas_proposal_reports/print_proposal.php">GAD Proposal Reports</a></li>
                        <li><a class="dropdown-item" href="#">Narrative Reports</a></li>
                    </ul>
                </div>
                <?php 
$currentPage = basename($_SERVER['PHP_SELF']);
if($isCentral): 
?>
<a href="../approval/approval.php" class="nav-link approval-link">
    <i class="fas fa-check-circle me-2"></i> Approval
    <span id="approvalBadge" class="notification-badge" style="display: none;">0</span>
</a>
```
</div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Narrative Reports</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="campusSelect">Select Campus:</label>
                                        <select class="form-select" id="campusSelect">
                                            <option value="">All Campuses</option>
                                            <?php
                                            $campuses = getCampuses();
                                            foreach ($campuses as $campus) {
                                                echo "<option value=\"{$campus['campus']}\">{$campus['campus']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="quarterSelect">Select Quarter:</label>
                                        <select class="form-select" id="quarterSelect">
                                            <option value="">All Quarters</option>
                                            <?php
                                            $quarters = getQuarters();
                                            foreach ($quarters as $quarter) {
                                                echo "<option value=\"{$quarter['quarter']}\">{$quarter['quarter']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="yearSelect">Select Year:</label>
                                        <select class="form-select" id="yearSelect">
                                            <option value="">All Years</option>
                                            <?php
                                            $years = getYears();
                                            foreach ($years as $year) {
                                                echo "<option value=\"{$year['year']}\">{$year['year']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="statusSelect">Select Status:</label>
                                        <select class="form-select" id="statusSelect">
                                            <option value="">All Statuses</option>
                                            <option value="Approved">Approved</option>
                                            <option value="Pending">Pending</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="searchInput">Search:</label>
                                        <input type="text" class="form-control" id="searchInput" placeholder="Search by title or description">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sortSelect">Sort By:</label>
                                        <select class="form-select" id="sortSelect">
                                            <option value="">None</option>
                                            <option value="title">Title</option>
                                            <option value="description">Description</option>
                                            <option value="date">Date</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="orderSelect">Order:</label>
                                        <select class="form-select" id="orderSelect">
                                            <option value="">None</option>
                                            <option value="asc">Ascending</option>
                                            <option value="desc">Descending</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="limitSelect">Limit:</label>
                                        <select class="form-select" id="limitSelect">
                                            <option value="">All</option>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pageSelect">Page:</label>
                                        <select class="form-select" id="pageSelect">
                                            <option value="1">1</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exportSelect">Export:</label>
                                        <select class="form-select" id="exportSelect">
                                            <option value="">None</option>
                                            <option value="csv">CSV</option>
                                            <option value="excel">Excel</option>
                                            <option value="pdf">PDF</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="filterSelect">Filter:</label>
                                        <select class="form-select" id="filterSelect">
                                            <option value="">None</option>
                                            <option value="completed">Completed</option>
                                            <option value="incomplete">Incomplete</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="groupSelect">Group By:</label>
                                        <select class="form-select" id="groupSelect">
                                            <option value="">None</option>
                                            <option value="campus">Campus</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Narrative Reports</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="campusSelect">Select Campus:</label>
                                        <select class="form-select" id="campusSelect">
                                            <option value="">All Campuses</option>
                                            <?php
                                            $campuses = getCampuses();
                                            foreach ($campuses as $campus) {
                                                echo "<option value=\"{$campus['campus']}\">{$campus['campus']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="quarterSelect">Select Quarter:</label>
                                        <select class="form-select" id="quarterSelect">
                                            <option value="">All Quarters</option>
                                            <?php
                                            $quarters = getQuarters();
                                            foreach ($quarters as $quarter) {
                                                echo "<option value=\"{$quarter['quarter']}\">{$quarter['quarter']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="yearSelect">Select Year:</label>
                                        <select class="form-select" id="yearSelect">
                                            <option value="">All Years</option>
                                            <?php
                                            $years = getYears();
                                            foreach ($years as $year) {
                                                echo "<option value=\"{$year['year']}\">{$year['year']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="statusSelect">Select Status:</label>
                                        <select class="form-select" id="statusSelect">
                                            <option value="">All Statuses</option>
                                            <option value="Approved">Approved</option>
                                            <option value="Pending">Pending</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="searchInput">Search:</label>
                                        <input type="text" class="form-control" id="searchInput" placeholder="Search by title or description">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sortSelect">Sort By:</label>
                                        <select class="form-select" id="sortSelect">
                                            <option value="">None</option>
                                            <option value="title">Title</option>
                                            <option value="description">Description</option>
                                            <option value="date">Date</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="orderSelect">Order:</label>
                                        <select class="form-select" id="orderSelect">
                                            <option value="">None</option>
                                            <option value="asc">Ascending</option>
                                            <option value="desc">Descending</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="limitSelect">Limit:</label>
                                        <select class="form-select" id="limitSelect">
                                            <option value="">All</option>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pageSelect">Page:</label>
                                        <select class="form-select" id="pageSelect">
                                            <option value="1">1</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exportSelect">Export:</label>
                                        <select class="form-select" id="exportSelect">
                                            <option value="">None</option>
                                            <option value="csv">CSV</option>
                                            <option value="excel">Excel</option>
                                            <option value="pdf">PDF</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="filterSelect">Filter:</label>
                                        <select class="form-select" id="filterSelect">
                                            <option value="">None</option>
                                            <option value="completed">Completed</option>
                                            <option value="incomplete">Incomplete</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="groupSelect">Group By:</label>
                                        <select class="form-select" id="groupSelect">
                                            <option value="">None</option>
                                            <option value="campus">Campus</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Narrative Reports</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="campusSelect">Select Campus:</label>
                                        <select class="form-select" id="campusSelect">
                                            <option value="">All Campuses</option>
                                            <?php
                                            $campuses = getCampuses();
                                            foreach ($campuses as $campus) {
                                                echo "<option value=\"{$campus['campus']}\">{$campus['campus']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="quarterSelect">Select Quarter:</label>
                                        <select class="form-select" id="quarterSelect">
                                            <option value="">All Quarters</option>
                                            <?php
                                            $quarters = getQuarters();
                                            foreach ($quarters as $quarter) {
                                                echo "<option value=\"{$quarter['quarter']}\">{$quarter['quarter']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="yearSelect">Select Year:</label>
                                        <select class="form-select" id="yearSelect">
                                            <option value="">All Years</option>
                                            <?php
                                            $years = getYears();
                                            foreach ($years as $year) {
                                                echo "<option value=\"{$year['year']}\">{$year['year']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="statusSelect">Select Status:</label>
                                        <select class="form-select" id="statusSelect">
                                            <option value="">All Statuses</option>
                                            <option value="Approved">Approved</option>
                                            <option value="Pending">Pending</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="searchInput">Search:</label>
                                        <input type="text" class="form-control" id="searchInput" placeholder="Search by title or description">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sortSelect">Sort By:</label>
                                        <select class="form-select" id="sortSelect">
                                            <option value="">None</option>
                                            <option value="title">Title</option>
                                            <option value="description">Description</option>
                                            <option value="date">Date</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="orderSelect">Order:</label>
                                        <select class="form-select" id="orderSelect">
                                            <option value="">None</option>
                                            <option value="asc">Ascending</option>
                                            <option value="desc">Descending</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="limitSelect">Limit:</label>
                                        <select class="form-select" id="limitSelect">
                                            <option value="">All</option>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pageSelect">Page:</label>
                                        <select class="form-select" id="pageSelect">
                    `;
                });
                
                imagesHtml += '</div>';
                
                return imagesHtml;
            } catch (e) {
                console.error('Error displaying additional images:', e);
                return '<p>Error displaying images</p>';
            }
        }
  
         // Generate proposal report
    </script>
    <script>
    function updateNotificationBadge(endpoint, action, badgeId) {
    const badge = document.getElementById(badgeId);
    if (!badge) return;
    
    const formData = new FormData();
    formData.append('action', action);
    
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    })
    .catch(error => console.error('Error fetching count:', error));
}

// Initial load and periodic updates
document.addEventListener('DOMContentLoaded', function() {
    // For approval badge
    updateNotificationBadge('../approval/gbp_api.php', 'count_pending', 'approvalBadge');
    
    // Set interval for updates (only if not on the page with that badge active)
    const isApprovalPage = document.querySelector('.approval-link.active');
    if (!isApprovalPage) {
        setInterval(() => {
            updateNotificationBadge('../approval/gbp_api.php', 'count_pending', 'approvalBadge');
        }, 30000); // Update every 30 seconds
    }
});

    </script>
  </body>
</html>
</html>