<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
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
        handleFormSubmission();
        break;
}

// Handle form submission
function handleFormSubmission() {
    try {
        global $conn;
        
        // Connect to database if not already connected
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
        }
        
        // Get the POST data
        $narrativeId = isset($_POST['narrative_id']) ? intval($_POST['narrative_id']) : 0;
        $campus = isset($_POST['campus']) ? sanitize_input($_POST['campus']) : '';
        
        // Ensure campus is set for central users
        $isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';
        if ($isCentral && empty($campus)) {
            throw new Exception("Campus must be selected for Central users");
        }
        
        $year = isset($_POST['year']) ? sanitize_input($_POST['year']) : '';
        $activity = isset($_POST['title']) ? sanitize_input($_POST['title']) : ''; // Use 'title' for backward compatibility
        $background = isset($_POST['background']) ? sanitize_input($_POST['background']) : '';
        $participants = isset($_POST['participants']) ? sanitize_input($_POST['participants']) : '';
        $topics = isset($_POST['topics']) ? sanitize_input($_POST['topics']) : '';
        $results = isset($_POST['results']) ? sanitize_input($_POST['results']) : '';
        $lessons = isset($_POST['lessons']) ? sanitize_input($_POST['lessons']) : '';
        $what_worked = isset($_POST['whatWorked']) ? sanitize_input($_POST['whatWorked']) : '';
        $issues = isset($_POST['issues']) ? sanitize_input($_POST['issues']) : '';
        $recommendations = isset($_POST['recommendations']) ? sanitize_input($_POST['recommendations']) : '';
        $ps_attribution = isset($_POST['psAttribution']) ? sanitize_input($_POST['psAttribution']) : '';
        // Don't sanitize evaluation data since it's JSON
        $evaluation = isset($_POST['evaluation']) ? $_POST['evaluation'] : '';
        // Debug log the evaluation data
        error_log("Raw evaluation data: " . (isset($_POST['evaluation']) ? $_POST['evaluation'] : 'not set'));
        // Validate that it's valid JSON
        if (!empty($evaluation)) {
            $json_valid = json_decode($evaluation) !== null;
            error_log("Evaluation JSON valid: " . ($json_valid ? 'yes' : 'no'));
        }
        $gender_issue = isset($_POST['genderIssue']) ? sanitize_input($_POST['genderIssue']) : '';
        $photo_caption = isset($_POST['photoCaption']) ? sanitize_input($_POST['photoCaption']) : '';
        
        $username = $_SESSION['username'];
        
        // Get temporary photos from session for new narratives
        $photoPath = '[]'; // Default empty JSON array
        if ($narrativeId == 0 && isset($_SESSION['temp_photos']) && !empty($_SESSION['temp_photos'])) {
            $photoPath = json_encode($_SESSION['temp_photos']);
        }
        
        // Add photo paths if available from session
        if (isset($_SESSION['temp_narrative_uploads']) && is_array($_SESSION['temp_narrative_uploads'])) {
            $photoPathsArray = $_SESSION['temp_narrative_uploads'];
            $photoPath = !empty($photoPathsArray) ? $photoPathsArray[0] : '';
            $photoPathsJson = json_encode($photoPathsArray);
            
            $query = "UPDATE narrative_entries SET 
                      campus = ?, 
                      year = ?, 
                      title = ?, 
                      background = ?, 
                      participants = ?, 
                      topics = ?, 
                      results = ?, 
                      lessons = ?, 
                      what_worked = ?, 
                      issues = ?, 
                      recommendations = ?, 
                      ps_attribution = ?, 
                      evaluation = ?, 
                      photo_caption = ?, 
                      gender_issue = ?,
                      updated_by = ?,
                      updated_at = NOW(),
                      photo_path = ?,
                      photo_paths = ?
                    WHERE id = ?";
            
            $types = "ssssssssssssssssss";
            $params = [
                $campus, $year, $activity, $background, $participants, 
                $topics, $results, $lessons, $what_worked, $issues, 
                $recommendations, $ps_attribution, $evaluation, $photo_caption, $gender_issue,
                $username, $photoPath, $photoPathsJson, $narrativeId
            ];
            
            // Clear the session variable after use
            unset($_SESSION['temp_narrative_uploads']);
        }
        
        // If editing an existing record
        if ($narrativeId > 0) {
            // Get the current photo_path for this narrative
            $query = "SELECT photo_path FROM narrative_entries WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $narrativeId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                // Use existing photo path for updates
                $photoPath = $row['photo_path'];
            }
            
            // Update the record
            // First check if updated_by column exists
            $columnExistsQuery = "SHOW COLUMNS FROM narrative_entries LIKE 'updated_by'";
            $columnResult = $conn->query($columnExistsQuery);
            $updatedByExists = $columnResult && $columnResult->num_rows > 0;
            
            if ($updatedByExists) {
                $query = "UPDATE narrative_entries SET 
                          campus = ?, 
                          year = ?, 
                          title = ?, 
                          background = ?, 
                          participants = ?, 
                          topics = ?, 
                          results = ?, 
                          lessons = ?, 
                          what_worked = ?, 
                          issues = ?, 
                          recommendations = ?, 
                          ps_attribution = ?, 
                          evaluation = ?, 
                          photo_caption = ?, 
                          gender_issue = ?,
                          updated_by = ?,
                          updated_at = NOW()
                        WHERE id = ?";
                        
                $stmt = $conn->prepare($query);
                
                if (!$stmt) {
                    throw new Exception("Database prepare error: " . $conn->error);
                }
                
                $stmt->bind_param(
                    "sssssssssssssssi", 
                    $campus, $year, $activity, $background, $participants, 
                    $topics, $results, $lessons, $what_worked, $issues, 
                    $recommendations, $ps_attribution, $evaluation, $photo_caption, $gender_issue,
                    $username, $narrativeId
                );
            } else {
                $query = "UPDATE narrative_entries SET 
                          campus = ?, 
                          year = ?, 
                          title = ?, 
                          background = ?, 
                          participants = ?, 
                          topics = ?, 
                          results = ?, 
                          lessons = ?, 
                          what_worked = ?, 
                          issues = ?, 
                          recommendations = ?, 
                          ps_attribution = ?, 
                          evaluation = ?, 
                          photo_caption = ?, 
                          gender_issue = ?
                        WHERE id = ?";
                        
                $stmt = $conn->prepare($query);
                
                if (!$stmt) {
                    throw new Exception("Database prepare error: " . $conn->error);
                }
                
                $stmt->bind_param(
                    "sssssssssssssssi", 
                    $campus, $year, $activity, $background, $participants, 
                    $topics, $results, $lessons, $what_worked, $issues, 
                    $recommendations, $ps_attribution, $evaluation, $photo_caption, $gender_issue,
                    $narrativeId
                );
            }
            
        } else {
            // Check if created_by column exists before using it
            $columnExistsQuery = "SHOW COLUMNS FROM narrative_entries LIKE 'created_by'";
            $columnResult = $conn->query($columnExistsQuery);
            $createdByExists = $columnResult && $columnResult->num_rows > 0;
            
            if ($createdByExists) {
                // Insert a new record with created_by
                $query = "INSERT INTO narrative_entries (
                          campus, year, title, background, participants, 
                          topics, results, lessons, what_worked, issues, 
                          recommendations, ps_attribution, evaluation, photo_path, photo_caption, gender_issue,
                          created_by, created_at
                        ) VALUES (
                          ?, ?, ?, ?, ?, 
                          ?, ?, ?, ?, ?, 
                          ?, ?, ?, ?, ?, ?,
                          ?, NOW()
                        )";
                        
                $stmt = $conn->prepare($query);
                
                if (!$stmt) {
                    throw new Exception("Database prepare error: " . $conn->error);
                }
                
                $stmt->bind_param(
                    "sssssssssssssssss", 
                    $campus, $year, $activity, $background, $participants, 
                    $topics, $results, $lessons, $what_worked, $issues, 
                    $recommendations, $ps_attribution, $evaluation, $photoPath, $photo_caption, $gender_issue,
                    $username
                );
            } else {
                // Insert a new record without created_by
                $query = "INSERT INTO narrative_entries (
                          campus, year, title, background, participants, 
                          topics, results, lessons, what_worked, issues, 
                          recommendations, ps_attribution, evaluation, photo_path, photo_caption, gender_issue,
                          created_at
                        ) VALUES (
                          ?, ?, ?, ?, ?, 
                          ?, ?, ?, ?, ?, 
                          ?, ?, ?, ?, ?, ?,
                          NOW()
                        )";
                        
                $stmt = $conn->prepare($query);
                
                if (!$stmt) {
                    throw new Exception("Database prepare error: " . $conn->error);
                }
                
                $stmt->bind_param(
                    "ssssssssssssssss", 
                    $campus, $year, $activity, $background, $participants, 
                    $topics, $results, $lessons, $what_worked, $issues, 
                    $recommendations, $ps_attribution, $evaluation, $photoPath, $photo_caption, $gender_issue
                );
            }
        }
        
        if ($stmt->execute()) {
            // Get the ID of the inserted record
            $newId = $narrativeId > 0 ? $narrativeId : $conn->insert_id;
            
            // Log success
            error_log("Successfully saved narrative with ID: " . $newId);
            
            // Check if the evaluation was saved correctly
            $checkQuery = "SELECT evaluation FROM narrative_entries WHERE id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("i", $newId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            if ($checkResult->num_rows > 0) {
                $row = $checkResult->fetch_assoc();
                error_log("Saved evaluation data: " . $row['evaluation']);
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Narrative ' . ($narrativeId > 0 ? 'updated' : 'added') . ' successfully',
                'narrative_id' => $newId
            ]);
            
            // Clear any temporary session data
            if (isset($_SESSION['temp_photos'])) {
                unset($_SESSION['temp_photos']);
            }
        } else {
            throw new Exception("Error saving record: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        // Return error response
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
        
        $query = "SELECT * FROM narrative_entries WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $entry = $result->fetch_assoc();
            
            // Process photo paths if they exist
            if (!empty($entry['photo_path'])) {
                // Check if it's a JSON string (new format)
                if (substr($entry['photo_path'], 0, 1) === '[') {
                    $photoPaths = json_decode($entry['photo_path'], true);
                    
                    // Convert filenames to full paths for display
                    $fullPaths = [];
                    foreach ($photoPaths as $path) {
                        // Just a filename, add photos/ directory prefix
                        $fullPaths[] = 'photos/' . $path;
                    }
                    
                    $entry['photo_paths'] = $fullPaths; // Add as separate array property
                    $entry['photo_path'] = isset($fullPaths[0]) ? $fullPaths[0] : ''; // Compatibility with old code
                } else {
                    // Single path (old format) - ensure it has photos/ prefix
                    if (strpos($entry['photo_path'], 'photos/') !== 0 && strpos($entry['photo_path'], 'http') !== 0) {
                        $entry['photo_path'] = 'photos/' . $entry['photo_path'];
                    } 
                }
            }
            
            echo json_encode(['success' => true, 'data' => $entry]);
        } else {
            throw new Exception("Narrative not found");
        }
        
    } catch (Exception $e) {
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
                $campuses = ['Alangilan', 'Arasof-Nasugbu', 'Balayan', 'Lemery', 'Lipa', 'Lobo', 'Mabini', 'Malvar', 'Pablo Borbon', 'Rosario', 'San Juan'];
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
                $campuses = ['Alangilan', 'Arasof-Nasugbu', 'Balayan', 'Lemery', 'Lipa', 'Lobo', 'Mabini', 'Malvar', 'Pablo Borbon', 'Rosario', 'San Juan'];
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
        
        $titles = [];
        
        if ($tableResult && $tableResult->num_rows > 0) {
            // Build query based on filters
            $query = "SELECT DISTINCT activity FROM ppas_forms WHERE 1=1";
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
                    $titles[] = $row['activity'];
                }
            }
        }
        
        // Add default titles if no results
        if (empty($titles)) {
            $titles = [
                'Gender and Development Training',
                'Women Empowerment Workshop',
                'Gender Sensitivity Seminar',
                'Gender Integration Workshop',
                'Diversity and Inclusion Conference'
            ];
        }
        
        echo json_encode(['success' => true, 'data' => $titles]);
        
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
        
        if (empty($activity)) {
            throw new Exception("Activity parameter is required");
        }
        
        // Check if ppas_forms table exists
        $tableCheckQuery = "SHOW TABLES LIKE 'ppas_forms'";
        $tableResult = $conn->query($tableCheckQuery);
        
        if ($tableResult && $tableResult->num_rows > 0) {
            // Get PS attribution and gender issue ID
            $query = "SELECT ps_attribution, gender_issue_id FROM ppas_forms WHERE activity = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $activity);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $psAttribution = $row['ps_attribution'];
                $genderIssueId = $row['gender_issue_id'];
                
                // Now get the gender issue text
                $genderIssue = '';
                
                // Check if it's a string already
                if (is_string($genderIssueId) && !is_numeric($genderIssueId)) {
                    $genderIssue = $genderIssueId;
                } 
                // Or if it's a reference to gpb_entries
                else if (!empty($genderIssueId)) {
                    // Check if gpb_entries table exists
                    $genderTableCheckQuery = "SHOW TABLES LIKE 'gpb_entries'";
                    $genderTableResult = $conn->query($genderTableCheckQuery);
                    
                    if ($genderTableResult && $genderTableResult->num_rows > 0) {
                        $query = "SELECT gender_issue FROM gpb_entries WHERE id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $genderIssueId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result && $result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $genderIssue = $row['gender_issue'];
                        } else {
                            $genderIssue = $genderIssueId; // Use ID as fallback
                        }
                    } else {
                        $genderIssue = $genderIssueId; // Use ID as fallback
                    }
                }
                
                echo json_encode([
                    'success' => true, 
                    'data' => [
                        'ps_attribution' => $psAttribution,
                        'gender_issue' => $genderIssue
                    ]
                ]);
                
            } else {
                throw new Exception("Activity not found");
            }
        } else {
            throw new Exception("PPAS forms table not found");
        }
        
    } catch (Exception $e) {
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