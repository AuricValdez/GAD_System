<?php
session_start();
require_once '../config.php';
require_once 'debug_logger.php'; // Include debug logger

// Only include db_connection if DB constants aren't already defined
if (!defined('DB_HOST')) {
    require_once '../includes/db_connection.php';
}

// Create a mysqli connection (always) - override any existing PDO connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Log the start of the request
debug_to_file('Request started', [
    'POST' => $_POST,
    'SESSION' => $_SESSION,
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD']
]);

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    debug_to_file('Unauthorized access attempt');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Function to sanitize input data
function sanitize_input($data) {
    if ($data === null) {
        return '';
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Get the action type from the request
$action = isset($_POST['action']) ? $_POST['action'] : '';
debug_to_file('Action received', $action);

// Handle different operations based on action
switch ($action) {
    case 'create':
        handleCreate();
        break;
    case 'read':
        handleRead();
        break;
    case 'update':
        handleUpdate();
        break;
    case 'delete':
        handleDelete();
        break;
    case 'get_single':
        getSingleNarrative();
        break;
    case 'get_campuses':
        getCampuses();
        break;
    case 'get_years':
        getYears();
        break;
    case 'get_titles':
        getTitles();
        break;
    case 'get_titles_from_ppas':
        getTitlesFromPPAS();
        break;
    case 'get_years_from_ppas':
        getYearsFromPPAS();
        break;
    case 'get_activity_details':
        getActivityDetails();
        break;
    case 'get_user_campus':
        getUserCampus();
        break;
    default:
        // For form submissions without an action (assuming it's a save operation)
        debug_to_file('No specific action, assuming default form submission');
        handleFormSubmission();
        break;
}

// Handle form submission
function handleFormSubmission() {
    debug_to_file('Form submission started');
    
    try {
        global $conn;
        
        // Debug data received
        debug_to_file('POST data', $_POST);
        
        // Extract form data
        $narrativeId = isset($_POST['narrative_id']) ? intval($_POST['narrative_id']) : 0;
        $campus = isset($_POST['campus']) ? sanitize_input($_POST['campus']) : '';
        $year = isset($_POST['year']) ? sanitize_input($_POST['year']) : '';
        
        // Get title and ppas_form_id - multiple sources possible
        $ppasFormId = isset($_POST['ppas_form_id']) ? intval($_POST['ppas_form_id']) : null;
        $title = isset($_POST['title']) ? sanitize_input($_POST['title']) : '';
        
        debug_to_file("Title initial value: " . $title);
        debug_to_file("PPAS Form ID initial value: " . ($ppasFormId ?? "NULL"));
        
        // If we're editing and ppas_form_id is not provided, try to get the existing one
        if ($narrativeId > 0 && (empty($ppasFormId) || $ppasFormId === 0)) {
            debug_to_file("Looking up existing ppas_form_id for narrative ID: " . $narrativeId);
            $existingQuery = "SELECT ppas_form_id, title FROM narrative_entries WHERE id = ?";
            $existingStmt = $conn->prepare($existingQuery);
            $existingStmt->bind_param("i", $narrativeId);
            $existingStmt->execute();
            $existingResult = $existingStmt->get_result();
            if ($row = $existingResult->fetch_assoc()) {
                if (!empty($row['ppas_form_id'])) {
                    $ppasFormId = intval($row['ppas_form_id']);
                    debug_to_file("Found existing ppas_form_id: " . $ppasFormId);
                }
                
                // Also get title if it's not set
                if (empty($title) && !empty($row['title'])) {
                    $title = $row['title'];
                    debug_to_file("Got existing title from DB: " . $title);
                }
            }
            $existingStmt->close();
        }
        
        // If we have a ppas_form_id, get the activity title from that
        if ($ppasFormId) {
            debug_to_file("Looking up title from PPAS Form ID: " . $ppasFormId);
            $titleQuery = "SELECT activity FROM ppas_forms WHERE id = ?";
            $titleStmt = $conn->prepare($titleQuery);
            $titleStmt->bind_param("i", $ppasFormId);
            $titleStmt->execute();
            $titleResult = $titleStmt->get_result();
            if ($row = $titleResult->fetch_assoc()) {
                $title = $row['activity'];
                debug_to_file("Found title from PPAS form: " . $title);
            }
            $titleStmt->close();
        }
        
        // If still no title and we're editing an existing entry, get existing title
        if (empty($title) && $narrativeId > 0) {
            debug_to_file("Title still empty, looking up existing title for narrative ID: " . $narrativeId);
            $existingTitleQuery = "SELECT title FROM narrative_entries WHERE id = ?";
            $existingTitleStmt = $conn->prepare($existingTitleQuery);
            $existingTitleStmt->bind_param("i", $narrativeId);
            $existingTitleStmt->execute();
            $existingTitleResult = $existingTitleStmt->get_result();
            if ($row = $existingTitleResult->fetch_assoc()) {
                $title = $row['title'];
                debug_to_file("Found existing title: " . $title);
            }
            $existingTitleStmt->close();
        }
        
        debug_to_file("Final title value: " . $title);
        
        $background = isset($_POST['background']) ? sanitize_input($_POST['background']) : '';
        $participants = isset($_POST['participants']) ? sanitize_input($_POST['participants']) : '';
        $topics = isset($_POST['topics']) ? sanitize_input($_POST['topics']) : '';
        
        // Get main form fields - use same value for alternative field names
        $results = isset($_POST['results']) ? sanitize_input($_POST['results']) : '';
        $lessons = isset($_POST['lessons']) ? sanitize_input($_POST['lessons']) : '';
        $what_worked = isset($_POST['whatWorked']) ? sanitize_input($_POST['whatWorked']) : '';
        $issues = isset($_POST['issues']) ? sanitize_input($_POST['issues']) : '';
        $recommendations = isset($_POST['recommendations']) ? sanitize_input($_POST['recommendations']) : '';
        $ps_attribution = isset($_POST['psAttribution']) ? floatval($_POST['psAttribution']) : 0;
        
        // Use same values for alternative field names
        $expected_results = $results;
        $lessons_learned = $lessons;
        $issues_concerns = $issues;
        
        // Process photo paths to ensure proper format
        $photo_paths = isset($_POST['photo_paths']) ? $_POST['photo_paths'] : '[]';
        $photo_paths_array = json_decode($photo_paths, true) ?: [];
        
        // Ensure all paths have photos/ prefix and are properly formatted
        $formatted_paths = array_map(function($path) {
            // Remove any existing photos/ prefix to avoid duplication
            $path = str_replace(['photos/', '../photos/'], '', $path);
            // Add photos/ prefix
            return 'photos/' . $path;
        }, $photo_paths_array);
        
        // Set main photo path as first image
        $photo_path = !empty($formatted_paths) ? $formatted_paths[0] : '';
        
        // Convert paths array to properly escaped JSON string
        $photo_paths = json_encode($formatted_paths);
        
        $photo_caption = isset($_POST['photoCaption']) ? sanitize_input($_POST['photoCaption']) : '';
        $gender_issue = isset($_POST['genderIssue']) ? sanitize_input($_POST['genderIssue']) : '';
        
        // Get selected personnel with full details
        $selected_personnel = [];
        debug_to_file("Raw POST data for personnel:", $_POST['selected_personnel'] ?? 'not set');
        
        if (isset($_POST['selected_personnel'])) {
            $personnel_data = $_POST['selected_personnel'];
            debug_to_file("Raw personnel data:", $personnel_data);
            
            try {
                // Handle different possible formats
                if (is_array($personnel_data)) {
                    debug_to_file("Personnel data is already an array:", $personnel_data);
                    $decoded = $personnel_data;
                } elseif (is_string($personnel_data)) {
                    // Try to decode JSON string 
                    $decoded = json_decode($personnel_data, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        debug_to_file("Failed to decode personnel JSON string, error: " . json_last_error_msg());
                        // Try to handle as serialized data or string array
                        if (strpos($personnel_data, '[') === 0) {
                            // Looks like a JSON array, try manual parsing
                            debug_to_file("Trying manual parsing of JSON-like array");
                            $personnel_data = str_replace(["'", "\\"], ["\"", ""], $personnel_data);
                            $decoded = json_decode($personnel_data, true);
                        }
                    }
                }
                
                debug_to_file("Decoded personnel data:", [
                    'success' => $decoded !== null,
                    'data' => $decoded,
                    'json_error' => json_last_error_msg()
                ]);
                
                // If we have an array, process it
                if (is_array($decoded)) {
                    // Extract personnel data, handling different formats
                    foreach ($decoded as $person) {
                        if (is_array($person) && isset($person['name'])) {
                            // Object format with name key
                            $selected_personnel[] = $person['name'];
                        } elseif (is_string($person)) {
                            // Simple string format
                            $selected_personnel[] = $person;
                        } elseif (is_array($person) && isset($person['id'])) {
                            // Try to get personnel name from ID
                            $stmt = $conn->prepare("SELECT name FROM personnel WHERE id = ?");
                            if ($stmt) {
                                $stmt->bind_param("i", $person['id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($row = $result->fetch_assoc()) {
                                    $selected_personnel[] = $row['name'];
                                }
                                $stmt->close();
                            }
                        }
                    }
                    
                    debug_to_file("Extracted personnel names:", $selected_personnel);
                } else {
                    debug_to_file("Personnel data is not an array, using existing data if available");
                    
                    // If this is an update, get existing personnel data
                    if ($narrativeId > 0) {
                        $existingPersonnelQuery = "SELECT other_internal_personnel FROM narrative_entries WHERE id = ?";
                        $existingPersonnelStmt = $conn->prepare($existingPersonnelQuery);
                        $existingPersonnelStmt->bind_param("i", $narrativeId);
                        $existingPersonnelStmt->execute();
                        $existingPersonnelResult = $existingPersonnelStmt->get_result();
                        if ($row = $existingPersonnelResult->fetch_assoc()) {
                            if (!empty($row['other_internal_personnel'])) {
                                $existing_personnel = json_decode($row['other_internal_personnel'], true);
                                if (is_array($existing_personnel)) {
                                    $selected_personnel = $existing_personnel;
                                    debug_to_file("Using existing personnel data:", $selected_personnel);
                                }
                            }
                        }
                        $existingPersonnelStmt->close();
                    }
                }
            } catch (Exception $e) {
                debug_to_file("Error processing personnel data:", $e->getMessage());
            }
        }
        
        // Store the personnel names as JSON
        $other_internal_personnel = json_encode($selected_personnel);
        
        debug_to_file("Final personnel data to store:", [
            'other_internal_personnel' => $other_internal_personnel,
            'is_json' => json_last_error() === JSON_ERROR_NONE,
            'count' => count($selected_personnel)
        ]);
        
        // Adding additional debug output for JSON encoding issues
        if (json_last_error() !== JSON_ERROR_NONE) {
            debug_to_file("JSON encoding error:", json_last_error_msg());
        }
        
        // Get evaluation and ratings with correct field names
        $evaluation = isset($_POST['evaluation']) ? $_POST['evaluation'] : '';
        $activity_ratings = isset($_POST['activity_ratings']) ? $_POST['activity_ratings'] : '';
        $timeliness_ratings = isset($_POST['timeliness_ratings']) ? $_POST['timeliness_ratings'] : '';
        
        debug_to_file('Processed data before save:', [
            'title' => $title,
            'personnel' => $selected_personnel,
            'results/expected_results' => $results,
            'lessons/lessons_learned' => $lessons,
            'issues/issues_concerns' => $issues,
            'photo_path' => $photo_path,
            'photo_paths' => $photo_paths,
            'photo_caption' => $photo_caption,
            'gender_issue' => $gender_issue,
            'activity_ratings' => $activity_ratings,
            'timeliness_ratings' => $timeliness_ratings
        ]);
        
        // Prepare query based on whether we're updating or inserting
        if ($narrativeId > 0) {
            // Update existing record
            $query = "UPDATE narrative_entries SET 
                      campus = ?, year = ?, title = ?, background = ?, participants = ?, 
                      topics = ?, results = ?, lessons = ?, what_worked = ?, issues = ?, 
                      recommendations = ?, ps_attribution = ?, other_internal_personnel = ?, evaluation = ?, activity_ratings = ?, 
                      timeliness_ratings = ?, photo_path = ?, photo_paths = ?, photo_caption = ?, gender_issue = ?,
                      ppas_form_id = ?, updated_by = ?, expected_results = ?, lessons_learned = ?, issues_concerns = ?,
                      updated_at = NOW()
                    WHERE id = ?";
                    
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                debug_to_file('Database prepare error', $conn->error);
                throw new Exception("Database prepare error");
            }
            
            // Bind parameters for update
            $stmt->bind_param(
                "sssssssssssssssssssssssssi",
                $campus, $year, $title, $background, $participants, 
                $topics, $results, $lessons, $what_worked, $issues, 
                $recommendations, $ps_attribution, $other_internal_personnel, $evaluation, $activity_ratings, 
                $timeliness_ratings, $photo_path, $photo_paths, $photo_caption, $gender_issue,
                $ppasFormId, $_SESSION['username'], $expected_results, $lessons_learned, $issues_concerns,
                $narrativeId
            );
        } else {
            // Insert new record
            $query = "INSERT INTO narrative_entries (
                      campus, year, title, background, participants, 
                      topics, results, lessons, what_worked, issues, 
                      recommendations, ps_attribution, other_internal_personnel, evaluation, activity_ratings, 
                      timeliness_ratings, photo_path, photo_paths, photo_caption, gender_issue,
                      ppas_form_id, created_by, expected_results, lessons_learned, issues_concerns,
                      created_at, updated_at
                    ) VALUES (
                      ?, ?, ?, ?, ?, 
                      ?, ?, ?, ?, ?, 
                      ?, ?, ?, ?, ?, 
                      ?, ?, ?, ?, ?,
                      ?, ?, ?, ?, ?,
                      NOW(), NOW()
                    )";
                    
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                debug_to_file('Database prepare error', $conn->error);
                throw new Exception("Database prepare error");
            }
            
            // Bind parameters for insert
            $stmt->bind_param(
                "sssssssssssssssssssssssss",
                $campus, $year, $title, $background, $participants, 
                $topics, $results, $lessons, $what_worked, $issues, 
                $recommendations, $ps_attribution, $other_internal_personnel, $evaluation, $activity_ratings, 
                $timeliness_ratings, $photo_path, $photo_paths, $photo_caption, $gender_issue,
                $ppasFormId, $_SESSION['username'], $expected_results, $lessons_learned, $issues_concerns
            );
        }
        
        debug_to_file("Query parameters:", [
            'campus' => $campus,
            'year' => $year,
            'title' => $title,
            'other_internal_personnel' => $other_internal_personnel,
            'photo_caption' => $photo_caption,
            'gender_issue' => $gender_issue
        ]);
        
        if (!$stmt->execute()) {
            debug_to_file('Database execute error', $stmt->error);
            throw new Exception("Failed to save narrative: " . $stmt->error);
        }
        
        // Get the ID of the inserted record
        $newId = $narrativeId > 0 ? $narrativeId : $conn->insert_id;
        debug_to_file('Record ID', $newId);
        
        // Check if the data was saved correctly
        $checkQuery = "SELECT id, title, photo_path, photo_paths, evaluation, activity_ratings, timeliness_ratings, other_internal_personnel FROM narrative_entries WHERE id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $newId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            debug_to_file('Saved data verification:', [
                'id' => $row['id'],
                'title' => $row['title'],
                'photo_path' => $row['photo_path'],
                'photo_paths' => $row['photo_paths'],
                'evaluation' => $row['evaluation'],
                'activity_ratings' => $row['activity_ratings'],
                'timeliness_ratings' => $row['timeliness_ratings'],
                'other_internal_personnel' => $row['other_internal_personnel']
            ]);
        }
        
        debug_to_file('Sending success response');
        echo json_encode([
            'success' => true, 
            'message' => 'Narrative ' . ($narrativeId > 0 ? 'updated' : 'added') . ' successfully',
            'narrative_id' => $newId
        ]);
        
        // Clear any temporary session data
        if (isset($_SESSION['temp_photos'])) {
            unset($_SESSION['temp_photos']);
            debug_to_file('Cleared temp_photos from session');
        }
        
    } catch (Exception $e) {
        // Return error response
        debug_to_file('Exception caught', $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Function to create a new narrative entry
function handleCreate() {
    try {
        global $conn;
        
        // Connect to database if not already connected
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
        }
        
        // Same as handleFormSubmission() but for API calls specifically using 'action' = 'create'
        handleFormSubmission();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Function to read all narrative entries
function handleRead() {
    try {
        global $conn;
        
        // Connect to database if not already connected
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
        }
        
        // Get the user's campus information
        $isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';
        
        // Get campus filter from POST request if provided
        $campusFilter = isset($_POST['campus']) ? sanitize_input($_POST['campus']) : '';
        
        // For non-Central users, force their campus as filter if no campus is specified
        if (!$isCentral && empty($campusFilter)) {
            $campusFilter = $_SESSION['username']; // Username is campus for non-central users
        }
        
        // Check if table exists first
        $tableCheckQuery = "SHOW TABLES LIKE 'narrative_entries'";
        $tableResult = $conn->query($tableCheckQuery);
        
        if (!$tableResult || $tableResult->num_rows === 0) {
            // Table doesn't exist, return empty data
            echo json_encode(['success' => true, 'data' => [], 'message' => 'Narrative table not found, please import SQL file.']);
            return;
        }
        
        // Debug campus filtering
        error_log("Read action - Campus filter: " . ($campusFilter ?: 'ALL CAMPUSES'));
        
        // Build query based on filters
        $query = "SELECT * FROM narrative_entries";
        $params = [];
        $paramTypes = "";
        
        // Apply campus filter if provided or for non-central users
        if (!empty($campusFilter)) {
            $query .= " WHERE campus = ?";
            $params[] = $campusFilter;
            $paramTypes .= "s";
            
            error_log("Filtering narratives by campus: $campusFilter");
        } else if (!$isCentral) {
            // Safety check - for non-central users, always filter by their campus
            $query .= " WHERE campus = ?";
            $params[] = $_SESSION['username'];
            $paramTypes .= "s";
            
            error_log("Non-central user - forcing campus filter: " . $_SESSION['username']);
        }
        
        // Order by most recent first
        $query .= " ORDER BY created_at DESC";
        
        // Prepare and execute the query
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        // Bind parameters if we have any
        if (!empty($params)) {
            $stmt->bind_param($paramTypes, ...$params);
        }
        
        $executeResult = $stmt->execute();
        if (!$executeResult) {
            throw new Exception("Query execution failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Failed to get result: " . $stmt->error);
        }
        
        $entries = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $entries[] = $row;
            }
        }
        
        error_log("Found " . count($entries) . " narratives");
        
        echo json_encode(['success' => true, 'data' => $entries]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Function to get a single narrative by ID
function getSingleNarrative() {
    try {
        global $conn;
        
        // Connect to database if not already connected
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id <= 0) {
            throw new Exception("Invalid narrative ID");
        }
        
        debug_to_file("Fetching single narrative ID: {$id}");
        
        $query = "SELECT * FROM narrative_entries WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $entry = $result->fetch_assoc();
            
            // Debug the retrieved entry
            debug_to_file("Retrieved narrative data:", [
                'id' => $entry['id'],
                'title' => $entry['title'],
                'other_internal_personnel' => $entry['other_internal_personnel']
            ]);
            
            // Process photo paths if they exist
            if (!empty($entry['photo_paths'])) {
                // First try to use the dedicated photo_paths column
                if (is_string($entry['photo_paths'])) {
                    try {
                        $photoPaths = json_decode($entry['photo_paths'], true);
                        if (is_array($photoPaths)) {
                            $entry['photo_paths'] = $photoPaths;
                        } else {
                            $entry['photo_paths'] = [];
                        }
                    } catch (Exception $e) {
                        debug_to_file("Error parsing photo_paths: " . $e->getMessage());
                        $entry['photo_paths'] = [];
                    }
                }
            } else {
                // Initialize as empty array if not set
                $entry['photo_paths'] = [];
            }

            // Also handle the legacy photo_path for backwards compatibility
            if (!empty($entry['photo_path'])) {
                // If photo_path is an array JSON string (older format), parse it
                if (is_string($entry['photo_path']) && substr($entry['photo_path'], 0, 1) === '[') {
                    try {
                        $legacyPaths = json_decode($entry['photo_path'], true);
                        
                        // Add these paths to photo_paths array if they're not already there
                        if (is_array($legacyPaths)) {
                            foreach ($legacyPaths as $path) {
                                if (!in_array($path, $entry['photo_paths'])) {
                                    $entry['photo_paths'][] = $path;
                                }
                            }
                        }
                        
                        // Use the first path as the main photo_path
                        $entry['photo_path'] = !empty($legacyPaths) ? $legacyPaths[0] : '';
                    } catch (Exception $e) {
                        debug_to_file("Error parsing legacy photo_path: " . $e->getMessage());
                    }
                } else {
                    // Single path - add to photo_paths if not already there
                    if (!in_array($entry['photo_path'], $entry['photo_paths'])) {
                        $entry['photo_paths'][] = $entry['photo_path'];
                    }
                }
                
                // Make sure photo_path has proper prefix
                if (!empty($entry['photo_path']) && strpos($entry['photo_path'], 'photos/') !== 0 && strpos($entry['photo_path'], 'http') !== 0) {
                    $entry['photo_path'] = 'photos/' . $entry['photo_path'];
                }
            }

            // Make sure all paths in photo_paths have proper prefix
            if (!empty($entry['photo_paths']) && is_array($entry['photo_paths'])) {
                foreach ($entry['photo_paths'] as $key => $path) {
                    if (strpos($path, 'photos/') !== 0 && strpos($path, 'http') !== 0) {
                        $entry['photo_paths'][$key] = 'photos/' . $path;
                    }
                }
            }

            // If we have a PPAS form ID, get the duration from the PPAS form
            if (!empty($entry['ppas_form_id'])) {
                $ppasQuery = "SELECT 
                    COALESCE(CAST(NULLIF(TRIM(total_duration), '') AS DECIMAL(10,2)), 0.00) as total_duration,
                    COALESCE(ps_attribution, 0.00) as ps_attribution 
                FROM ppas_forms 
                WHERE id = ?";
                    
                $ppasStmt = $conn->prepare($ppasQuery);
                $ppasStmt->bind_param("i", $entry['ppas_form_id']);
                $ppasStmt->execute();
                $ppasResult = $ppasStmt->get_result();
                
                if ($ppasResult->num_rows > 0) {
                    $ppasData = $ppasResult->fetch_assoc();
                    
                    // Set the total duration from PPAS form
                    $entry['total_duration'] = $ppasData['total_duration'];
                    
                    // Set PPAS PS attribution for calculations
                    $entry['ppas_ps_attribution'] = $ppasData['ps_attribution'];
                    
                    debug_to_file("Retrieved PPAS data for form ID {$entry['ppas_form_id']}:", [
                        'total_duration' => $entry['total_duration'],
                        'ps_attribution' => $entry['ppas_ps_attribution']
                    ]);
                }
                $ppasStmt->close();
            }

            echo json_encode(['success' => true, 'data' => $entry]);
        } else {
            throw new Exception("Narrative not found");
        }
        
    } catch (Exception $e) {
        debug_to_file("Error in getSingleNarrative:", $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Function to update a narrative
function handleUpdate() {
    try {
        global $conn;
        
        // Connect to database if not already connected
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
        }
        
        // Use the form submission handler since the logic is similar
        handleFormSubmission();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Function to delete a narrative
function handleDelete() {
    try {
        global $conn;
        
        // Connect to database if not already connected
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id <= 0) {
            throw new Exception("Invalid narrative ID");
        }
        
        // First get the photo path to delete the files if they exist
        $query = "SELECT photo_path FROM narrative_entries WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $photoPath = $row['photo_path'];
            
            // Delete the photo file(s) if they exist
            if (!empty($photoPath)) {
                // Check if it's a JSON array of paths
                if (substr($photoPath, 0, 1) === '[') {
                    $photoPaths = json_decode($photoPath, true);
                    if (is_array($photoPaths)) {
                        foreach ($photoPaths as $path) {
                            $fullPath = 'photos/' . $path;
                            if (file_exists($fullPath)) {
                                unlink($fullPath);
                            }
                        }
                    }
                } else {
                    // Single path (old format)
                    $fullPath = 'photos/' . $photoPath;
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
            }
        }
        
        // Now delete the record
        $query = "DELETE FROM narrative_entries WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Narrative entry deleted successfully']);
        } else {
            throw new Exception("Error deleting record: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Function to get all campuses
function getCampuses() {
    try {
        global $conn;
        
        // Connect to database if not already connected
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
        }
        
        // Check if user is central - they can access all campuses
        $isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';
        
        if ($isCentral) {
            // For central user, check if campuses table exists
            $tableCheckQuery = "SHOW TABLES LIKE 'campuses'";
            $tableResult = $conn->query($tableCheckQuery);
            
            if (!$tableResult || $tableResult->num_rows === 0) {
                // Campuses table doesn't exist yet, return a default list
                $campuses = ['Central', 'Alangilan', 'Arasof-Nasugbu', 'Balayan', 'Lemery', 'Lipa', 'Lobo', 'Mabini', 'Malvar', 'Pablo Borbon', 'Rosario', 'San Juan'];
                echo json_encode(['success' => true, 'data' => $campuses]);
                return;
            }
            
            // For central user, get all campuses from the database
            $query = "SELECT DISTINCT campus_name FROM campuses ORDER BY campus_name";
            $result = $conn->query($query);
            
            if (!$result) {
                throw new Exception("Error querying campuses: " . $conn->error);
            }
            
            $campuses = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $campuses[] = $row['campus_name'];
                }
            }
            
            // If no campuses found in database, use default list
            if (empty($campuses)) {
                $campuses = ['Central', 'Alangilan', 'Arasof-Nasugbu', 'Balayan', 'Lemery', 'Lipa', 'Lobo', 'Mabini', 'Malvar', 'Pablo Borbon', 'Rosario', 'San Juan'];
            }
            
            echo json_encode(['success' => true, 'data' => $campuses]);
        } else {
            // For regular users, only return their campus from the session
            $userCampus = isset($_SESSION['campus']) ? $_SESSION['campus'] : '';
            
            if (empty($userCampus)) {
                // If no campus in session, use the username as campus
                $userCampus = $_SESSION['username'];
            }
            
            echo json_encode(['success' => true, 'data' => [$userCampus]]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Function to get years from PPAS forms
function getYearsFromPPAS() {
    try {
        global $conn;
        
        // Connect to database if not already connected
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
        }
        
        $campus = isset($_POST['campus']) ? sanitize_input($_POST['campus']) : '';
        
        // Check if ppas_forms table exists
        $tableCheckQuery = "SHOW TABLES LIKE 'ppas_forms'";
        $tableResult = $conn->query($tableCheckQuery);
        
        $years = [];
        
        if ($tableResult && $tableResult->num_rows > 0) {
            // Get years based on campus filter if provided
            $query = "SELECT DISTINCT year FROM ppas_forms";
            
            if (!empty($campus)) {
                $query .= " WHERE campus = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $campus);
            } else {
                $stmt = $conn->prepare($query);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $years[] = $row['year'];
                }
            }
        }
        
        // Add current year range if no results
        if (empty($years)) {
            $currentYear = date('Y');
            $years = [
                $currentYear - 1,
                $currentYear,
                $currentYear + 1
            ];
        }
        
        echo json_encode(['success' => true, 'data' => $years]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Function to get titles/activities from PPAS forms
function getTitlesFromPPAS() {
    try {
        global $conn;
        
        // Connect to database if not already connected
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
        }
        
        $campus = isset($_POST['campus']) ? sanitize_input($_POST['campus']) : '';
        $year = isset($_POST['year']) ? sanitize_input($_POST['year']) : '';
        
        // Check if ppas_forms table exists
        $tableCheckQuery = "SHOW TABLES LIKE 'ppas_forms'";
        $tableResult = $conn->query($tableCheckQuery);
        
        $activities = [];
        
        if ($tableResult && $tableResult->num_rows > 0) {
            // Build query based on filters - now including id
            $query = "SELECT DISTINCT id, activity FROM ppas_forms WHERE 1=1";
            $params = [];
            $paramTypes = "";
            
            if (!empty($campus)) {
                $query .= " AND campus = ?";
                $params[] = $campus;
                $paramTypes .= "s";
            }
            
            if (!empty($year)) {
                $query .= " AND year = ?";
                $params[] = $year;
                $paramTypes .= "s";
            }
            
            $stmt = $conn->prepare($query);
            
            if (!empty($params)) {
                $stmt->bind_param($paramTypes, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Check if this activity has a narrative
                    $hasNarrative = false;
                    
                    // Check if narrative_entries table exists
                    $narrativeTableCheckQuery = "SHOW TABLES LIKE 'narrative_entries'";
                    $narrativeTableResult = $conn->query($narrativeTableCheckQuery);
                    
                    if ($narrativeTableResult && $narrativeTableResult->num_rows > 0) {
                        // Check if this activity has a narrative entry
                        $narrativeQuery = "SELECT COUNT(*) as count FROM narrative_entries WHERE title = ? AND campus = ? AND year = ?";
                        $narrativeStmt = $conn->prepare($narrativeQuery);
                        $narrativeStmt->bind_param("sss", $row['activity'], $campus, $year);
                        $narrativeStmt->execute();
                        $narrativeResult = $narrativeStmt->get_result();
                        
                        if ($narrativeResult && $narrativeResult->num_rows > 0) {
                            $narrativeRow = $narrativeResult->fetch_assoc();
                            $hasNarrative = ($narrativeRow['count'] > 0);
                        }
                    }
                    
                    // Add activity with has_narrative flag and id
                    $activities[] = [
                        'id' => $row['id'],
                        'title' => $row['activity'],
                        'has_narrative' => $hasNarrative
                    ];
                }
            }
        }
        
        // Add default titles if no results
        if (empty($activities)) {
            $defaultTitles = [
                'Gender and Development Training',
                'Women Empowerment Workshop',
                'Gender Sensitivity Seminar',
                'Gender Integration Workshop',
                'Diversity and Inclusion Conference'
            ];
            
            foreach ($defaultTitles as $title) {
                $activities[] = [
                    'id' => null,
                    'title' => $title,
                    'has_narrative' => false
                ];
            }
        }
        
        echo json_encode(['success' => true, 'data' => $activities]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Function to get activity details (PS attribution and gender issue)
function getActivityDetails() {
    try {
        global $conn;
        
        // Connect to database if not already connected
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
        }
        
        $activity = isset($_POST['activity']) ? sanitize_input($_POST['activity']) : '';
        $year = isset($_POST['year']) ? sanitize_input($_POST['year']) : '';
        
        if (empty($activity) || empty($year)) {
            throw new Exception("Activity and year parameters are required");
        }
        
        debug_to_file("Fetching activity details for: " . $activity . " (Year: " . $year . ")");
        
        // Get user's campus
        $userCampus = $_SESSION['username'] ?? '';
        $isCentral = ($userCampus === 'Central');
        
        debug_to_file("User Campus: " . $userCampus . ", Is Central: " . ($isCentral ? 'Yes' : 'No'));
        
        // First try to get from ppas_forms and join with gpb_entries to get the gender issue name
        $query = "SELECT p.id, p.ps_attribution, p.gender_issue_id, g.gender_issue, g.id as gpb_id 
                 FROM ppas_forms p 
                 LEFT JOIN gpb_entries g ON g.id = p.gender_issue_id 
                    AND g.year = p.year 
                    AND (g.campus = p.campus OR g.campus IS NULL)
                 WHERE (p.id = ? OR p.activity = ?) 
                 AND p.year = ? 
                 AND " . ($isCentral ? "1=1" : "p.campus = ?");
        
        debug_to_file("Query: " . $query);
        
        $stmt = $conn->prepare($query);
        if ($isCentral) {
            $stmt->bind_param("sss", $activity, $activity, $year);
        } else {
            $stmt->bind_param("ssss", $activity, $activity, $year, $userCampus);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute query: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $psAttribution = $row['ps_attribution'];
            $ppasFormId = $row['id'];
            $genderIssueId = $row['gender_issue_id'];
            $genderIssue = $row['gender_issue'];
            
            debug_to_file("Found PPAS form with ID: " . $ppasFormId);
            debug_to_file("Gender Issue ID: " . $genderIssueId);
            debug_to_file("Initial Gender Issue: " . ($genderIssue ?? 'NULL'));
            
            // If we don't have the gender issue from the join but have an ID, try to get it directly
            if (empty($genderIssue) && !empty($genderIssueId)) {
                debug_to_file("No gender issue from join, trying direct lookup with ID: " . $genderIssueId);
                
                $gpbQuery = "SELECT gender_issue FROM gpb_entries WHERE id = ?";
                $gpbStmt = $conn->prepare($gpbQuery);
                $gpbStmt->bind_param("i", $genderIssueId);
                
                if (!$gpbStmt->execute()) {
                    debug_to_file("Failed to execute direct lookup: " . $gpbStmt->error);
                } else {
                    $gpbResult = $gpbStmt->get_result();
                    
                    if ($gpbResult && $gpbResult->num_rows > 0) {
                        $gpbRow = $gpbResult->fetch_assoc();
                        $genderIssue = $gpbRow['gender_issue'];
                        debug_to_file("Found gender issue from direct lookup: " . $genderIssue);
                    } else {
                        debug_to_file("No results from direct lookup");
                    }
                }
            }
            
            // Check narrative_entries for any override
            $narrativeQuery = "SELECT gender_issue 
                             FROM narrative_entries 
                             WHERE (title = ? OR ppas_form_id = ?) 
                             AND year = ? 
                             AND " . ($isCentral ? "1=1" : "campus = ?");
            
            $narrativeStmt = $conn->prepare($narrativeQuery);
            if ($isCentral) {
                $narrativeStmt->bind_param("sis", $activity, $ppasFormId, $year);
            } else {
                $narrativeStmt->bind_param("siss", $activity, $ppasFormId, $year, $userCampus);
            }
            
            if (!$narrativeStmt->execute()) {
                debug_to_file("Failed to execute narrative query: " . $narrativeStmt->error);
            } else {
                $narrativeResult = $narrativeStmt->get_result();
                
                if ($narrativeResult && $narrativeResult->num_rows > 0) {
                    $narrativeRow = $narrativeResult->fetch_assoc();
                    if (!empty($narrativeRow['gender_issue'])) {
                        $genderIssue = $narrativeRow['gender_issue'];
                        debug_to_file("Found gender issue override from narrative: " . $genderIssue);
                    }
                }
            }
            
            // Always use the actual gender issue text if we have it
            if (!empty($genderIssue)) {
                debug_to_file("Using actual gender issue text: " . $genderIssue);
            }
            // Only use ID as fallback if we absolutely have no text
            else if (!empty($genderIssueId)) {
                // Try one more time to get the gender issue text from gpb_entries
                $finalQuery = "SELECT gender_issue FROM gpb_entries WHERE id = ?";
                $finalStmt = $conn->prepare($finalQuery);
                $finalStmt->bind_param("i", $genderIssueId);
                
                if ($finalStmt->execute()) {
                    $finalResult = $finalStmt->get_result();
                    if ($finalResult && $finalResult->num_rows > 0) {
                        $finalRow = $finalResult->fetch_assoc();
                        $genderIssue = $finalRow['gender_issue'];
                        debug_to_file("Found gender issue in final attempt: " . $genderIssue);
                    }
                }
                
                // If still no text, use ID as absolute last resort
                if (empty($genderIssue)) {
                    $genderIssue = "Gender Issue #" . $genderIssueId;
                    debug_to_file("Using fallback gender issue ID as last resort: " . $genderIssue);
                }
            }
            
            $response = [
                'success' => true, 
                'data' => [
                    'ps_attribution' => $psAttribution ?? '',
                    'gender_issue' => $genderIssue ?? '',
                    'ppas_form_id' => $ppasFormId ?? null,
                    'gender_issue_id' => $genderIssueId ?? null
                ]
            ];
            
            debug_to_file("Sending response: " . json_encode($response));
            echo json_encode($response);
            return;
        }
        
        throw new Exception("Activity not found for year: " . $year);
        
    } catch (Exception $e) {
        debug_to_file("Error in getActivityDetails: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Basic functions that are less commonly used

function getYears() {
    try {
        $currentYear = date('Y');
        $years = [
            $currentYear - 1,
            $currentYear,
            $currentYear + 1
        ];
        
        echo json_encode(['success' => true, 'data' => $years]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getTitles() {
    try {
        $titles = [
            'Gender and Development Training',
            'Women Empowerment Workshop',
            'Gender Sensitivity Seminar',
            'Gender Integration Workshop',
            'Diversity and Inclusion Conference'
        ];
        
        echo json_encode(['success' => true, 'data' => $titles]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Function to get user campus
function getUserCampus() {
    // Debug
    error_log("Getting user campus. Session username: " . ($_SESSION['username'] ?? 'Not set'));
    
    $campus = $_SESSION['username'] ?? '';
    // Exclude 'Central' as a campus
    if ($campus === 'Central') {
        $campus = '';
    }
    
    error_log("Returning campus: $campus");
    
    echo json_encode([
        'success' => true,
        'campus' => $campus
    ]);
}
?> 