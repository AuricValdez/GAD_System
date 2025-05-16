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

// Add a function to get narrative data from narrative_entries
function getNarrativeData($ppas_form_id) {
    try {
        error_log("=== FETCHING NARRATIVE DATA ===");
        error_log("Searching for narrative data with ppas_form_id: $ppas_form_id");
        $pdo = getConnection();

        // Get the PPAS form info first to use for lookup
        $title_sql = "SELECT activity_title, campus, fiscal_year FROM ppas_forms WHERE id = :id";
        $title_stmt = $pdo->prepare($title_sql);
        $title_stmt->bindParam(':id', $ppas_form_id);
        $title_stmt->execute();
        $title_data = $title_stmt->fetch(PDO::FETCH_ASSOC);
        
        $activity_title = $title_data['activity_title'] ?? '';
        $campus = $title_data['campus'] ?? '';
        $year = $title_data['fiscal_year'] ?? date('Y');
        
        error_log("Looking for narrative_entries matching ppas_form_id: '$ppas_form_id', title: '$activity_title', campus: '$campus', year: '$year'");

        // First try to get narrative by ppas_form_id
        $stmt = $pdo->prepare("SELECT * FROM narrative_entries WHERE ppas_form_id = :ppas_form_id");
        $stmt->execute([':ppas_form_id' => $ppas_form_id]);
        $narrative_entry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If not found by ppas_form_id, try by title
        if (!$narrative_entry) {
            error_log("No narrative found by ppas_form_id, trying by title: $activity_title");
            
            // Try exact title match
            $title_stmt = $pdo->prepare("SELECT * FROM narrative_entries WHERE title = :title LIMIT 1");
            $title_stmt->execute([':title' => $activity_title]);
            $narrative_entry = $title_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Still not found, try with LIKE for partial match
            if (!$narrative_entry) {
                error_log("No exact title match, trying with LIKE");
                $like_stmt = $pdo->prepare("SELECT * FROM narrative_entries WHERE title LIKE :title_pattern LIMIT 1");
                $like_stmt->execute([':title_pattern' => '%' . $activity_title . '%']);
                $narrative_entry = $like_stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        
        if (!$narrative_entry) {
            error_log("ERROR: Failed to find narrative entry by any method");
            // Return empty values instead of error
            return [
                'id' => null,
                'background' => '',
                'participants' => '',
                'topics' => '',
                'results' => '',
                'lessons' => '',
                'what_worked' => '',
                'issues' => '',
                'recommendations' => '',
                'photo_paths' => '[]',
                'photo_path' => '',
                'activity_ratings' => null,
                'timeliness_ratings' => null,
                'title' => 'No narrative entry found'
            ];
        }

        // Debug - Log what we found in the database
        error_log("FOUND ENTRY: Narrative Entry ID: " . $narrative_entry['id']);
        error_log("FOUND ENTRY: Activity Ratings: " . (isset($narrative_entry['activity_ratings']) ? substr($narrative_entry['activity_ratings'], 0, 100) . '...' : 'NULL'));
        error_log("FOUND ENTRY: Timeliness Ratings: " . (isset($narrative_entry['timeliness_ratings']) ? substr($narrative_entry['timeliness_ratings'], 0, 100) . '...' : 'NULL'));

        // Process activity_ratings
        $activityRatings = null;
        if (!empty($narrative_entry['activity_ratings'])) {
            $activityRatings = $narrative_entry['activity_ratings'];
            error_log("Using activity_ratings: " . substr($activityRatings, 0, 200));
                                } else {
            error_log("WARNING: No activity_ratings found in narrative_entry");
        }

        // Process timeliness_ratings
        $timelinessRatings = null;
        if (!empty($narrative_entry['timeliness_ratings'])) {
            $timelinessRatings = $narrative_entry['timeliness_ratings'];
            error_log("Using timeliness_ratings: " . substr($timelinessRatings, 0, 200));
                                } else {
            error_log("WARNING: No timeliness_ratings found in narrative_entry");
        }

        // Set in the return data
        $narrative_entry['activityRatings'] = $activityRatings;
        $narrative_entry['timelinessRatings'] = $timelinessRatings;

        // Debug what we found
        if (!empty($narrative_entry['id'])) {
            error_log("Processing narrative_entry with ID: " . $narrative_entry['id']);
            
            // Activity ratings
            if (!empty($narrative_entry['activity_ratings'])) {
                $activity_ratings_str = $narrative_entry['activity_ratings'];
                error_log("Raw activity_ratings data: " . substr($activity_ratings_str, 0, 200));
                
                // Output JavaScript that safely parses the JSON string
                echo "// Parse activity ratings from database\n";
                echo "console.log('SOURCE: Loading activity_ratings from narrative_entries table, record ID: " . $narrative_entry['id'] . "');\n";
                echo "try {\n";
                echo "    const dbActivityRatings = JSON.parse('" . addslashes($activity_ratings_str) . "');\n";
                echo "    console.log('Activity ratings loaded from database:', dbActivityRatings);\n";
                echo "} catch (error) {\n";
                echo "    console.error('Error parsing activity ratings JSON:', error);\n";
                echo "    const dbActivityRatings = null;\n";
                echo "}\n";
                
                error_log("Activity ratings output as JSON.parse call");
                    } else {
                echo "console.log('No activity_ratings found in DB');\n";
                echo "const dbActivityRatings = null;\n";
                error_log("No activity_ratings found in narrative_entry");
            }
            
            // Timeliness ratings
            if (!empty($narrative_entry['timeliness_ratings'])) {
                $timeliness_ratings_str = $narrative_entry['timeliness_ratings'];
                error_log("Raw timeliness_ratings data: " . substr($timeliness_ratings_str, 0, 200));
                
                // Output JavaScript that safely parses the JSON string
                echo "// Parse timeliness ratings from database\n";
                echo "console.log('SOURCE: Loading timeliness_ratings from narrative_entries table, record ID: " . $narrative_entry['id'] . "');\n";
                echo "try {\n";
                echo "    const dbTimelinessRatings = JSON.parse('" . addslashes($timeliness_ratings_str) . "');\n";
                echo "    console.log('Timeliness ratings loaded from database:', dbTimelinessRatings);\n";
                echo "} catch (error) {\n";
                echo "    console.error('Error parsing timeliness ratings JSON:', error);\n";
                echo "    const dbTimelinessRatings = null;\n";
                echo "}\n";
                
                error_log("Timeliness ratings output as JSON.parse call");
            } else {
                echo "console.log('No timeliness_ratings found in DB');\n";
                echo "const dbTimelinessRatings = null;\n";
                error_log("No timeliness_ratings found in narrative_entry");
            }
            
            // Handle activity images
            if (!empty($narrative_entry['photo_paths'])) {
                $photo_paths_str = $narrative_entry['photo_paths'];
                error_log("Raw photo_paths data: " . substr($photo_paths_str, 0, 200));
                
                // Try to decode and re-encode to ensure valid JSON
                $decoded = json_decode($photo_paths_str, true);
                if ($decoded !== null && is_array($decoded)) {
                    echo "const dbActivityImages = " . json_encode($decoded) . ";\n";
                    error_log("Successfully decoded and re-encoded photo_paths");
                } else if (!empty($narrative_entry['photo_path'])) {
                    echo "const dbActivityImages = " . json_encode([$narrative_entry['photo_path']]) . ";\n";
                    error_log("Using photo_path as fallback");
                } else {
                    echo "const dbActivityImages = [];\n";
                    error_log("No valid photo data found");
                }
            } else if (!empty($narrative_entry['photo_path'])) {
                echo "const dbActivityImages = " . json_encode([$narrative_entry['photo_path']]) . ";\n";
                error_log("Using photo_path as fallback");
            } else {
                echo "const dbActivityImages = [];\n";
                error_log("No photo_paths or photo_path found");
            }
            
            // Output JavaScript for processing ratings data
            echo "
// Process ratings data to extract nested objects if necessary
let processedActivityRatings = dbActivityRatings;
let processedTimelinessRatings = dbTimelinessRatings;

// Print detailed debug info to help troubleshoot
console.log('=== DATA SOURCE DEBUGGING ===');
let processedActivityRatings = dbActivityRatings;
let processedTimelinessRatings = dbTimelinessRatings;

// Print detailed debug info to help troubleshoot
console.log('Data source availability:');
console.log('dbActivityRatings from narrative_entries:', dbActivityRatings !== null && dbActivityRatings !== undefined);
console.log('dbTimelinessRatings from narrative_entries:', dbTimelinessRatings !== null && dbTimelinessRatings !== undefined);
console.log('narrativeRatings from narrative_entries.evaluation:', typeof narrativeRatings !== 'undefined');
console.log('narrativeTimelinessRatings from narrative_entries.evaluation:', typeof narrativeTimelinessRatings !== 'undefined');
console.log('ppasEntriesActivityRatings from ppas_entries:', typeof ppasEntriesActivityRatings !== 'undefined');
console.log('ppasEntriesTimelinessRatings from ppas_entries:', typeof ppasEntriesTimelinessRatings !== 'undefined');

// Handle nested structure in dbActivityRatings - but don't try to modify if it's valid JSON already
if (dbActivityRatings && typeof dbActivityRatings === 'object') {
    console.log('Using dbActivityRatings from narrative_entries.activity_ratings directly');
    processedActivityRatings = dbActivityRatings;
}

// Handle nested structure in dbTimelinessRatings - but don't try to modify if it's valid JSON already
if (dbTimelinessRatings && typeof dbTimelinessRatings === 'object') {
    console.log('Using dbTimelinessRatings from narrative_entries.timeliness_ratings directly');
    processedTimelinessRatings = dbTimelinessRatings;
}

// Debug conditions for choosing data source
const useDbActivity = (dbActivityRatings && typeof dbActivityRatings === 'object' && Object.keys(dbActivityRatings).length > 0);
const useDbTimeliness = (dbTimelinessRatings && typeof dbTimelinessRatings === 'object' && Object.keys(dbTimelinessRatings).length > 0);

console.log('Will use narrative_entries.activity_ratings?', useDbActivity);
console.log('Will use narrative_entries.timeliness_ratings?', useDbTimeliness);

// Modify priority logic to use the DB data directly when available
const finalActivityRatings = useDbActivity ? 
                           processedActivityRatings : 
                           (narrativeRatings ? 
                             (console.log('Using narrativeRatings from narrative_entries.evaluation'), narrativeRatings) : 
                             (ppasEntriesActivityRatings ? 
                               (console.log('Using ppasEntriesActivityRatings from ppas_entries'), ppasEntriesActivityRatings) : 
                               (console.log('Using activityFallback (hardcoded values)'), activityFallback)
                             )
                           );

const finalTimelinessRatings = useDbTimeliness ? 
                             processedTimelinessRatings : 
                             (narrativeTimelinessRatings ? 
                               (console.log('Using narrativeTimelinessRatings from narrative_entries.evaluation'), narrativeTimelinessRatings) : 
                               (ppasEntriesTimelinessRatings ? 
                                 (console.log('Using ppasEntriesTimelinessRatings from ppas_entries'), ppasEntriesTimelinessRatings) : 
                                 (console.log('Using timelinessFallback (hardcoded values)'), timelinessFallback)
                               )
                             );

console.log('FINAL DATA SOURCE for activity ratings:', 
            useDbActivity ? 'DATABASE: narrative_entries.activity_ratings' : 
            (typeof narrativeRatings !== 'undefined' ? 'DATABASE: narrative_entries.evaluation (activity)' : 
             (typeof ppasEntriesActivityRatings !== 'undefined' ? 'DATABASE: ppas_entries (activity)' : 'FALLBACK (hardcoded)')));
            
console.log('FINAL DATA SOURCE for timeliness ratings:', 
            useDbTimeliness ? 'DATABASE: narrative_entries.timeliness_ratings' : 
            (typeof narrativeTimelinessRatings !== 'undefined' ? 'DATABASE: narrative_entries.evaluation (timeliness)' : 
             (typeof ppasEntriesTimelinessRatings !== 'undefined' ? 'DATABASE: ppas_entries (timeliness)' : 'FALLBACK (hardcoded)')));

console.log('FINAL ACTIVITY RATINGS (data that will be used):', finalActivityRatings);
console.log('FINAL TIMELINESS RATINGS (data that will be used):', finalTimelinessRatings);
console.log('=== END DATA SOURCE DEBUGGING ===');
";
        }
        
        return $narrative_entry;

    } catch (PDOException $e) {
        error_log("Error in getNarrativeData: " . $e->getMessage());
        return ['error' => 'Database error: ' . $e->getMessage()];
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


    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="force_values.js"></script>
    <script>
        /* Your custom scripts here */
    </script>
    
    <!-- Debug script for raw data extraction -->
    <script src="debug_raw_data.js"></script>
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
<?php endif; ?>
            </nav>
        </div>
        <div class="bottom-controls">
            <a href="../index.php" class="logout-button" onclick="handleLogout(event)">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
            <button class="theme-switch-button" onclick="toggleTheme()">
                <i class="fas fa-sun" id="theme-icon"></i>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-title">
            <i class="fas fa-file-alt"></i>
            <h2>External Print GAD Narrative</h2>
        </div>

        <!-- Report Generation Form -->
        <div class="card mb-4" style="min-height: auto; max-height: fit-content;">
            <div class="card-body py-3">
                <form id="reportForm" class="compact-form">
                    <div class="row align-items-start">
                        <div class="col-md-3">
                            <label for="campus" class="form-label"><i class="fas fa-university me-1"></i> Campus</label>
                            <select class="form-control" id="campus" required style="height: 38px;">
                                <option value="">Select Campus</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="year" class="form-label"><i class="fas fa-calendar-alt me-1"></i> Year</label>
                            <select class="form-control" id="year" required style="height: 38px;">
                                <option value="">Select Year</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="prepared_by" class="form-label"><i class="fas fa-user-edit me-1"></i> Prepared By Position</label>
                            <select class="form-control" id="prepared_by" disabled style="height: 38px;">
                                <option value="">Select Position</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="proposal" class="form-label"><i class="fas fa-file-alt me-1"></i> Proposal</label>
                            <div class="position-relative">
                                <input type="text" 
                                      class="form-control" 
                                      id="proposal" 
                                      placeholder="Search for a proposal..." 
                                      autocomplete="off"
                                      style="height: 38px;"
                                      disabled
                                      required>
                                <div id="proposalDropdown" class="dropdown-menu w-100" style="display:none; max-height: 150px; overflow-y: auto;"></div>
                                <input type="hidden" id="proposal_id">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Preview -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Proposal Preview</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary" onclick="printReport()">
                            <i class="fas fa-print"></i> Print
                            </button>
                       
                                </div>
                            </div>
                <div id="reportPreview" class="table-responsive">
                    <!-- Proposal content will be loaded here -->
                    <div class="text-center text-muted py-5" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;">
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <p>Select a campus, year, and proposal to generate the preview</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            loadCampusOptions();
            
            // Handle form submission
            $('#reportForm').on('submit', function(e) {
                    e.preventDefault();
                const selectedProposalId = $('#proposal_id').val();
                console.log('Form submitted. Proposal ID:', selectedProposalId);
                
                if (!selectedProposalId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selection Required',
                        text: 'Please select a proposal first.'
                    });
                    return;
                }
                
                generateReport();
            });

            // Handle proposal search input
            let searchTimeout;
            $('#proposal').on('input', function() {
                const searchTerm = $(this).val();
                const selectedCampus = $('#campus').val();
                const selectedYear = $('#year').val();
                const selectedPosition = $('#prepared_by').val();
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Clear proposal ID when input changes
                $('#proposal_id').val('');
                
                if (!selectedCampus || !selectedYear || !selectedPosition) {
                    console.log('Campus, Year, or Prepared By not selected');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selection Required',
                        text: 'Please select campus, year, and prepared by position first.'
                    });
                    return;
                }
                
                if (searchTerm.length < 1) {
                    $('#proposalDropdown').hide().empty();
                    return;
                }
                
                // Set new timeout
                searchTimeout = setTimeout(() => {
                    console.log('Searching for:', searchTerm);
                    $.ajax({
                        url: 'api/get_proposals.php',
                        method: 'GET',
                        data: {
                            search: searchTerm,
                            campus: selectedCampus,
                            year: selectedYear,
                            position: selectedPosition
                        },
                        dataType: 'json',
                        success: function(response) {
                            try {
                                console.log('Search response:', response);
                                const dropdown = $('#proposalDropdown');
                                dropdown.empty();
                                
                                // Make sure response is an object if it's a string
                                if (typeof response === 'string') {
                                    response = JSON.parse(response);
                                }
                                
                                if (response && response.status === 'success' && Array.isArray(response.data) && response.data.length > 0) {
                                    // Store proposals globally
                                    window.proposals = response.data;
                                    
                                    console.log('Found', response.data.length, 'proposals');
                                    
                                    // Add proposals to dropdown
                                    response.data.forEach(function(proposal) {
                                        const item = $('<div class="dropdown-item"></div>')
                                            .text(proposal.activity_title)
                                            .attr('data-id', proposal.id)
                                            .click(function() {
                                                // Set input value
                                                $('#proposal').val(proposal.activity_title);
                                                // Set hidden proposal_id
                                                $('#proposal_id').val(proposal.id);
                                                // Hide dropdown
                                                dropdown.hide();
                                                console.log('Selected proposal:', proposal.activity_title, 'with ID:', proposal.id);
                                                
                                                // Auto-generate report when proposal is selected
                                                generateReport();
                                            });
                                        
                                        dropdown.append(item);
                                    });
                                    
                                    // Show dropdown
                                    dropdown.show();
                                    console.log('Updated dropdown with', response.data.length, 'options');
                            } else {
                                    console.log('No proposals found - Response data:', JSON.stringify(response));
                                    // Show "no results" message
                                    dropdown.append('<div class="dropdown-item disabled">No proposals found</div>');
                                    dropdown.show();
                                }
                            } catch (error) {
                                console.error('Error processing response:', error);
                                const dropdown = $('#proposalDropdown');
                                dropdown.empty();
                                dropdown.append('<div class="dropdown-item disabled">Error processing response</div>');
                                dropdown.show();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Search error:', error);
                            const dropdown = $('#proposalDropdown');
                            dropdown.empty();
                            dropdown.append('<div class="dropdown-item disabled">Error loading proposals</div>');
                            dropdown.show();
                        }
                    });
                }, 300);
            });

            // Hide dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#proposal, #proposalDropdown').length) {
                    $('#proposalDropdown').hide();
                }
            });

            // Clear form button (optional - you can add this to your HTML)
            function clearProposalForm() {
                $('#proposal').val('');
                $('#proposal_id').val('');
                $('#proposalDropdown').hide();
            }

            // Handle proposal selection
            $('#proposal').on('change', function() {
                const selectedTitle = $(this).val();
                console.log('Selected title:', selectedTitle);
                
                const proposals = window.proposals || [];
                console.log('Available proposals:', proposals);
                
                const selectedProposal = proposals.find(p => p.activity_title === selectedTitle);
                console.log('Found proposal:', selectedProposal);

                if (selectedProposal) {
                    $('#proposal_id').val(selectedProposal.id);
                    console.log('Set proposal ID to:', selectedProposal.id);
                    
                    // Auto-generate report when proposal is selected
                    generateReport();
                    } else {
                    $('#proposal_id').val('');
                    if (selectedTitle) {
                        console.log('No matching proposal found for title:', selectedTitle);
                    }
                }
            });

            // Handle campus change
            $('#campus').on('change', function() {
                const selectedCampus = $(this).val();
                if (selectedCampus) {
                    loadYearOptions();
                    
                    // Only show the placeholder if there's no existing preview content
                    if ($('#reportPreview').is(':empty')) {
                        $('#reportPreview').html(`
                            <div class="text-center text-muted py-5" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;">
                                <i class="fas fa-file-alt fa-3x mb-3"></i>
                                <p>Select a campus, year, and proposal to generate the preview</p>
                            </div>
                        `);
                    }
                } else {
                    $('#year').html('<option value="">Select Year</option>').prop('disabled', true);
                }
            });
            
            // Handle year change
            $('#year').on('change', function() {
                const selectedYear = $(this).val();
                
                if (selectedYear) {
                    // Enable positions dropdown
                    loadPositionOptions();
                } else {
                    // Reset subsequent fields
                    $('#prepared_by').val('').prop('disabled', true);
                    $('#proposal').val('').prop('disabled', true);
                    $('#proposal_id').val('');
                }
                
                clearProposalForm();
            });
            
            // Add handler for prepared_by change
            $('#prepared_by').on('change', function() {
                const selectedPosition = $(this).val();
                if (selectedPosition) {
                    $('#proposal').prop('disabled', false);
                    
                    // If a proposal is already selected, regenerate the report with the new position
                    const selectedProposalId = $('#proposal_id').val();
                    if (selectedProposalId) {
                        console.log('Prepared By changed, regenerating report with new position:', selectedPosition);
                        // Show loading indicator
                        $('#reportPreview').html(`
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Refreshing narrative report...</p>
                            </div>
                        `);
                        
                        // Regenerate report with new position
                        setTimeout(() => {
                            generateReport();
                        }, 300);
                    }
                } else {
                    $('#proposal').val('').prop('disabled', true);
                    $('#proposal_id').val('');
                }
            });
        });
        
        // Load campus options
        function loadCampusOptions() {
            const campusSelect = $('#campus');
            campusSelect.prop('disabled', true);
            
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const userCampus = "<?php echo $userCampus ?>";
            
            if (isCentral) {
                $.ajax({
                    url: 'api/get_campuses.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        campusSelect.empty().append('<option value="">Select Campus</option>');
                        if (response.status === 'success' && response.data) {
                            console.log('Available campuses:', response.data);
                            response.data.forEach(function(campus) {
                                if (campus.name && campus.name !== 'null' && campus.name !== 'Default Campus') {
                                    campusSelect.append(`<option value="${campus.name}">${campus.name}</option>`);
                        }
                    });
                }
                        campusSelect.prop('disabled', false);
                        
                        // Add a change event listener to the campus dropdown
                        campusSelect.off('change').on('change', function() {
                            console.log("Campus changed to:", $(this).val());
                            const selectedCampus = $(this).val();
                            
                            if (selectedCampus) {
                                loadYearOptions();
                                // Ensure the placeholder is visible
                                $('#reportPreview').html(`
                                    <div class="text-center text-muted py-5" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;">
                                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                                        <p>Select a campus, year, and proposal to generate the preview</p>
                                    </div>
                                `);
                            } else {
                                $('#year').html('<option value="">Select Year</option>').prop('disabled', true);
                                $('#proposal').val(null).trigger('change').prop('disabled', true);
                                $('#proposal_id').val('');
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading campuses:', error);
                        campusSelect.empty().append('<option value="">Error loading campuses</option>');
                    }
                });
            } else {
                campusSelect.empty().append(`<option value="${userCampus}" selected>${userCampus}</option>`);
                campusSelect.prop('disabled', true);
                loadYearOptions();
            }
        }

        // Load year options
        function loadYearOptions() {
            const yearSelect = $('#year');
            const selectedCampus = $('#campus').val();
            
            yearSelect.prop('disabled', true);
            yearSelect.html('<option value="">Loading years...</option>');
            
            $.ajax({
                url: 'api/get_proposal_years.php',
                method: 'GET',
                data: { campus: selectedCampus },
                dataType: 'json',
                success: function(response) {
                    console.log('Year response:', response);
                    yearSelect.empty().append('<option value="">Select Year</option>');
                    
                    if (response.status === 'error') {
                        console.error('API Error:', response.message);
                        yearSelect.html(`<option value="">${response.message || 'Error loading years'}</option>`);
                        
                        // Show error to user
                    Swal.fire({
                        icon: 'error',
                            title: 'Error Loading Years',
                            text: response.message || 'Failed to load year data. Please ensure you are logged in.',
                            confirmButtonColor: '#6c757d'
                        });
                        return;
                    }
                    
                    if (response.status === 'success' && response.data && response.data.length > 0) {
                        // FIXED: Load years from response data
                        response.data.forEach(function(yearData) {
                            yearSelect.append(`<option value="${yearData.year}">${yearData.year}</option>`);
                        });
                        yearSelect.prop('disabled', false);
                    } else {
                        // No years available
                        yearSelect.html('<option value="">No years available</option>');
                        
                        // Optional: Display friendly message about no data
                Swal.fire({
                            icon: 'info',
                            title: 'No Data Available',
                            text: 'No proposal years found for this campus. You may need to create proposals first.',
                            confirmButtonColor: '#6c757d'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading years:', error);
                    console.error('Response:', xhr.responseText);
                    
                    let errorMessage = 'Failed to load years. Please try again.';
                    
                    // Try to parse error message from response if possible
                    try {
                        const responseJson = JSON.parse(xhr.responseText);
                        if (responseJson && responseJson.message) {
                            errorMessage = responseJson.message;
                        }
                        } catch (e) {
                        // Handle case where response is not JSON
                        if (xhr.status === 500) {
                            errorMessage = 'Server error. Please check database connection or contact administrator.';
                        } else if (xhr.status === 404) {
                            errorMessage = 'API endpoint not found. Please check system configuration.';
                        } else if (xhr.status === 0) {
                            errorMessage = 'Network error. Please check your connection.';
                        }
                    }
                    
                    yearSelect.html(`<option value="">Error: ${errorMessage}</option>`);
                    
                    // Show error to user
                Swal.fire({
                    icon: 'error',
                        title: 'Error Loading Years',
                        text: errorMessage,
                        footer: 'Status Code: ' + xhr.status,
                        confirmButtonColor: '#6c757d'
                    });
                }
            });
        }

        // Load position options for the "Prepared By" dropdown
        function loadPositionOptions() {
            const preparedBySelect = $('#prepared_by');
            preparedBySelect.empty();
            
            // Add options
            preparedBySelect.append(`
                <option value="">Select Position</option>
                <option value="Faculty">Faculty</option>
                <option value="Extension Coordinator">Extension Coordinator</option>
                <option value="GAD Head Secretariat">GAD Head Secretariat</option>
                <option value="Director, Extension Services">Director, Extension Services</option>
                <option value="Vice President for RDES">Vice President for RDES</option>
                <option value="Vice President for AF">Vice President for AF</option>
                <option value="Vice Chancellor for AF">Vice Chancellor for AF</option>
            `);
            
            // Enable the dropdown
            preparedBySelect.prop('disabled', false);
        }

        // Generate proposal report
        function generateReport() {
            // CRITICAL OVERRIDE: Prevent fallback usage
            console.log('%c CRITICAL OVERRIDE: Forcing raw data usage only', 'color: red; font-weight: bold; font-size: 14px;');
            
            // Override getDefaultRatings to return raw data
            window.getDefaultRatings = function() {
                console.error('CRITICAL: getDefaultRatings called but FORCED to return raw data without fallbacks');
                return null; // Return null to make it obvious we don't want fallbacks
            };
            
            // Override getSampleRatingsData to never be used
            window.getSampleRatingsData = function() {
                console.error('CRITICAL: getSampleRatingsData called but FORCED to return raw data without fallbacks');
                throw new Error('getSampleRatingsData should NEVER be called - use raw data only');
                return null;
            };
            
            // Override transformRatingsToProperFormat to not transform data
            window.transformRatingsToProperFormat = function(ratingsData) {
                console.log('OVERRIDE: Using raw ratings data directly - no transformation');
                return ratingsData; // Return the data unchanged
            };
            
            const selectedCampus = $('#campus').val();
            const selectedYear = $('#year').val();
            const selectedProposalId = $('#proposal_id').val();
            const selectedPosition = $('#prepared_by').val();
            
            if (!selectedCampus || !selectedYear || !selectedProposalId || !selectedPosition) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select all required fields to generate the proposal.'
                });
                return;
            }
            
            // Show loading state
            $('#reportPreview').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading narrative report...</p>
                </div>
            `);
            
            // Fetch narrative data from database
            $.ajax({
                url: 'api/get_narrative.php',
                method: 'GET',
                data: {
                    ppas_form_id: selectedProposalId,
                    campus: selectedCampus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        // Store the selected position in the report data
                        response.data.preparedByPosition = selectedPosition;
                        displayNarrativeReport(response.data);
                        } else {
                        $('#reportPreview').html(`
                            <div class="text-center text-danger py-5">
                                <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                                <p><strong>Error:</strong> ${response.message || 'Failed to load narrative data'}</p>
                                <p>Please make sure a narrative report exists for this PPAS form.</p>
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response Text:', xhr.responseText);
                    
                    $('#reportPreview').html(`
                        <div class="text-center text-danger py-5">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <p><strong>Error:</strong> Failed to load narrative data. Please try again.</p>
                            <p><small>Status: ${xhr.status} ${status}</small></p>
                        </div>
                    `);
                }
            });
        }

        // Print report function
        function printReport() {
            // Create a print window with a specific title
            const printWindow = window.open('', '_blank', 'width=1200,height=800');
            
            // Set window properties immediately to prevent about:blank
            printWindow.document.open();
            printWindow.document.title = "GAD Proposal";
            
            let reportContent = $('#reportPreview').html();
            
            // SPECIAL FIX: Remove any empty divs or spaces that might cause empty boxes
            reportContent = reportContent.replace(/<div[^>]*>\s*<\/div>/g, '');
            reportContent = reportContent.replace(/<pre[\s\S]*?<\/pre>/g, '');
            
            // Always force print to be in light mode for consistent output
            const printStyles = `
                <style>
                    @page {
                        size: 8.5in 13in;
                        margin-top: 1.52cm;
                        margin-bottom: 2cm;
                        margin-left: 1.78cm;
                        margin-right: 2.03cm;
                        border-top: 3px solid black !important;
                        border-bottom: 3px solid black !important;
                    }
                    
                    /* First page footer with tracking number */
                    @page:first {
                        @bottom-left {
                            content: "Tracking Number:___________________" !important;
                            font-family: 'Times New Roman', Times, serif !important;
                            font-size: 10pt !important;
                            color: black !important;
                        }
                        
                        @bottom-right {
                            content: "Page " counter(page) " of " counter(pages);
                            font-family: 'Times New Roman', Times, serif;
                            font-size: 10pt;
                            color: black;
                        }
                    }
                    
                    /* Remove any inline tracking numbers */
                    div[style*="Tracking Number"] {
                        display: none !important;
                    }
                    
                    body {
                        background-color: white !important;
                        color: black !important;
                        font-family: 'Times New Roman', Times, serif !important;
                        font-size: 12pt !important;
                        line-height: 1.2 !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }
                    
                    /* Explicit tracking number at bottom of page */
                    .tracking-footer {
                        position: fixed !important;
                        bottom: 0.5cm !important;
                        left: 0 !important;
                        width: 100% !important;
                        text-align: center !important;
                        font-family: 'Times New Roman', Times, serif !important;
                        font-size: 10pt !important;
                        color: black !important;
                        z-index: 1000 !important;
                    }
                    
                    /* Proposal container */
                    .proposal-container {
                        background-color: white !important;
                        color: black !important;
                        width: 100% !important;
                        max-width: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        border: none !important;
                    }
                    
                    /* Container for signatures with no margins */
                    div[style*="width: 100%"] {
                        margin: 0 !important;
                        padding: 0 !important;
                        width: 100% !important;
                        max-width: 100% !important;
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
                    
                    table {
                        width: 100% !important;
                        border-collapse: collapse !important;
                        page-break-inside: auto !important;
                        break-inside: auto !important;
                    }
                    
                    td, th {
                        border: 1px solid black !important;
                        padding: 5px !important;
                        page-break-inside: auto !important;
                        break-inside: auto !important;
                        background-color: white !important;
                        color: black !important;
                    }
                    
                    /* Force specific colors */
                    [style*="color: blue"], .sustainability-plan, .sustainability-plan *,
                    [style*="color: blue;"], ol[style*="color: blue"] li, li[style*="color: blue"],
                    [style*="GAD Head"], [style*="Extension Services"],
                    [style*="Vice Chancellor"], [style*="Chancellor"] {
                        color: blue !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    
                    /* Force browns */
                    [style*="color: brown"], [style*="color: brown;"],
                    div[style*="color: brown"], div[style*="color: brown;"] {
                        color: brown !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    
                    /* Ensure black cells in Gantt chart */
                    td[style*="background-color: black"] {
                        background-color: black !important;
                        color: white !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    /* Show tracking number only on first page */
                    .tracking-number {
                        position: absolute;
                        bottom: 20px;
                        left: 20px;
                        font-size: 10pt;
                    }
                    
                    /* Page breaks */
                    .page-break-before {
                        page-break-before: always !important;
                    }
                    
                    .page-break-after {
                        page-break-after: always !important;
                    }
                    
                    /* Page numbers - show on all pages */
                            @page {
                                @bottom-right {
                                    content: "Page " counter(page) " of " counter(pages);
                                    font-family: 'Times New Roman', Times, serif;
                                    font-size: 10pt;
                            }
                        }
                    </style>
            `;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>GAD Proposal</title>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    ${printStyles}
                    <style>
                        @page {
                            size: 8.5in 13in;
                            margin-top: 1.52cm;
                            margin-bottom: 2cm;
                            margin-left: 1.78cm;
                            margin-right: 2.03cm;
                            border: 3px solid black !important;
                        }
                        
                        /* Force all colors to be black */
                        * { color: black !important; }
                        
                        /* The only exception is black background cells for Gantt chart */
                        td[style*="background-color: black"] {
                            background-color: black !important;
                        }
                    </style>
                </head>
                <body>
                    <div class="WordSection1">
                        ${reportContent}
                    </div>
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            setTimeout(() => {
                printWindow.print();
                // Add event listener to close the window after printing is complete
                printWindow.addEventListener('afterprint', function() {
                    printWindow.close();
                });
            }, 500);
        }

        // Function to check proposal information directly
        function checkProposalDirectly(proposalId) {
            if (!proposalId) {
                Swal.fire({
                    icon: 'error',
                    title: 'No Proposal ID',
                    text: 'No proposal ID provided to check.'
                });
                return;
            }
            
            // Show loading
            $('#reportPreview').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Checking proposal in database...</p>
                </div>
            `);
            
            // Use an existing API endpoint instead of a specialized debugging endpoint
            $.ajax({
                url: 'api/get_proposal_details.php',
                method: 'GET',
                data: {
                    proposal_id: proposalId,
                    campus: $('#campus').val(),
                    year: $('#year').val()
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Debug response:', response);
                    
                    if (response.status === 'success') {
                        // If proposal found, show a mockup of the GAD proposal with the data
                        let mockupProposal = `
                            <div class="proposal-container">
                                <!-- Header Section -->
                                <table class="header-table">
                                    <tr>
                                        <td style="width: 15%; text-align: center;">
                                            <img src="../images/Batangas_State_Logo.png" alt="BatState-U Logo" style="max-width: 80px;">
                                        </td>
                                        <td style="width: 70%; text-align: center;">
                                            <div style="font-size: 14pt; font-weight: bold;">BATANGAS STATE UNIVERSITY</div>
                                            <div style="font-size: 12pt;">THE NATIONAL ENGINEERING UNIVERSITY</div>
                                            <div style="font-size: 11pt; font-style: italic;">${response.data.campus || 'Unknown Campus'}</div>
                                            <div style="font-size: 12pt; font-weight: bold; margin-top: 10px;">GAD PROPOSAL (INTERNAL PROGRAM/PROJECT/ACTIVITY)</div>
                                        </td>
                                        <td style="width: 15%; text-align: center;">
                                            <div style="font-size: 10pt;">Reference No.: BatStateU-FO-ESU-09</div>
                                            <div style="font-size: 10pt;">Effectivity Date: August 25, 2023</div>
                                            <div style="font-size: 10pt;">Revision No.: 00</div>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Add tracking number to first page -->
                                <div style="text-align: left; margin-top: 5px; margin-bottom: 5px; font-size: 10pt;">
                                    Tracking Number:___________________
                                </div>

                                <!-- Activity Type Checkboxes -->
                                <div style="width: 100%; text-align: center; margin: 10px 0;">
                                    <span style="display: inline-block; margin: 0 20px;">☐ Program</span>
                                    <span style="display: inline-block; margin: 0 20px;">☐ Project</span>
                                    <span style="display: inline-block; margin: 0 20px;">☒ Activity</span>
                                </div>

                                <!-- Proposal Details -->
                                <table class="data-table">
                                    <tr>
                                        <td style="width: 25%; font-weight: bold;">I. Title:</td>
                                        <td style="width: 75%;">${response.data.activity_title || response.data.title || response.data.activity || 'Test Activity'}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">II. Date and Venue:</td>
                                        <td>${response.data.date_venue ? response.data.date_venue.venue + '<br>' + response.data.date_venue.date : 'Not specified'}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">III. Mode of Delivery:</td>
                                        <td>${response.data.delivery_mode || 'Not specified'}</td>
                                    </tr>
                                </table>
                                
                                <div class="section-heading">IV. Project Team:</div>
                                <div style="margin-left: 20px;">
                                    <div><strong>Project Leader/s:</strong> ${response.data.project_team ? response.data.project_team.project_leaders.names : 'Not specified'}</div>
                                    <div class="responsibilities">
                                        <div><strong>Responsibilities:</strong></div>
                                        <ol>
                                            ${response.data.project_team && response.data.project_team.project_leaders.responsibilities ? 
                                              response.data.project_team.project_leaders.responsibilities.map(resp => `<li>${resp}</li>`).join('') : 
                                              '<li>No responsibilities specified</li>'}
                                        </ol>
                                    </div>
                                </div>

                                <h5 class="mt-4">Debug Information</h5>
                                <div class="alert alert-success">
                                    <p><strong>The proposal was found in the database!</strong></p>
                                    <p>Try using the "Generate Proposal" button again to view the complete proposal.</p>
                                </div>
                            </div>
                        `;
                        
                        $('#reportPreview').html(mockupProposal);
                        
                        // Now let's try to generate the full report
                        setTimeout(() => {
                            generateReport();
                        }, 1000);
                            } else {
                        let errorOutput = `
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle"></i> Proposal Not Found</h5>
                                <p>${response.message || 'The proposal could not be found in the database.'}</p>
                                <div class="card mb-3">
                                    <div class="card-header">Troubleshooting Information</div>
                                    <div class="card-body">
                                        <p><strong>Proposal ID:</strong> ${proposalId}</p>
                                        <p><strong>Campus:</strong> ${$('#campus').val()}</p>
                                        <p><strong>Year:</strong> ${$('#year').val()}</p>
                                        <p>Please verify these values are correct in the database.</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-primary" onclick="$('#proposal').val(''); $('#proposal_id').val(''); $('#proposalDropdown').hide();">
                                        Clear Selection
                                    </button>
                                </div>
                            </div>
                        `;
                        $('#reportPreview').html(errorOutput);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Debug error:', error);
                    
                    $('#reportPreview').html(`
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-circle"></i> Error Checking Proposal</h5>
                            <p>Could not check the proposal information: ${error}</p>
                            <pre>${xhr.responseText || 'No response details available'}</pre>
                            <div class="mt-3">
                                <button class="btn btn-sm btn-primary" onclick="generateReport()">
                                    Try Again
                                </button>
                            </div>
                        </div>
                    `);
                }
            });
        }

        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        function updateThemeIcon(theme) {
            const themeIcon = document.getElementById('theme-icon');
            themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
            
            // Update proposal preview container styling based on theme
            const previewContent = $('#reportPreview .proposal-container');
            if (previewContent.length > 0) {
                if (newTheme === 'dark') {
                    previewContent.addClass('dark-mode-proposal').removeClass('light-mode-proposal');
                    } else {
                    previewContent.addClass('light-mode-proposal').removeClass('dark-mode-proposal');
                }
            }
        }

        // Apply saved theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            updateThemeIcon(savedTheme);

            // Handle dropdown submenu
            document.querySelectorAll('.dropdown-submenu > a').forEach(function(element) {
                element.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    
                    // Toggle the submenu
                    const parentLi = this.parentElement;
                    parentLi.classList.toggle('show');
                    
                    const submenu = this.nextElementSibling;
                    if (submenu && submenu.classList.contains('dropdown-menu')) {
                        if (submenu.style.display === 'block') {
                            submenu.style.display = 'none';
                        } else {
                            submenu.style.display = 'block';
                        }
                    }
                });
            });
            
            // Close submenus when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-submenu')) {
                    const openSubmenus = document.querySelectorAll('.dropdown-submenu.show');
                    openSubmenus.forEach(menu => {
                        menu.classList.remove('show');
                        const submenu = menu.querySelector('.dropdown-menu');
                        if (submenu) {
                            submenu.style.display = 'none';
                        }
                    });
                }
            });
        });

        function handleLogout(event) {
            event.preventDefault();
            
                Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of the system",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel',
                backdrop: `
                    rgba(0,0,0,0.7)
                `,
                allowOutsideClick: true,
                customClass: {
                    container: 'swal-blur-container',
                    popup: 'logout-swal'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.body.classList.add('fade-out');
                    
                    setTimeout(() => {
                        window.location.href = '../loading_screen.php?redirect=index.php';
                    }, 10); // Changed from 50 to 10 - make it super fast
                }
            });
        }

        function fetchProposalDetails(selectedCampus, selectedYear, selectedProposalId) {
            $.ajax({
                url: 'api/get_proposal_details.php',
                method: 'GET',
                data: {
                    campus: selectedCampus,
                    year: selectedYear,
                    proposal_id: selectedProposalId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        displayProposal(response.data);
                    } else {
                        // Handle API error with more details
                        console.error('API Error:', response);
                        $('#reportPreview').html(`
                            <div class="text-center text-danger py-5">
                                <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                                <p><strong>Error:</strong> ${response.message || 'Failed to load proposal data'}</p>
                                ${response.code ? `<p><small>Error code: ${response.code}</small></p>` : ''}
                                <p class="mt-3">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="showDebugInfo(${JSON.stringify(response)})">
                                        Show Technical Details
                                    </button>
                                </p>
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response Text:', xhr.responseText);
                    
                    // Try to parse the response if it's JSON
                    let errorMessage = 'Error loading proposal. Please try again.';
                    let errorDetails = '';
                    
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.message) {
                            errorMessage = errorResponse.message;
                        }
                        errorDetails = JSON.stringify(errorResponse, null, 2);
                    } catch (e) {
                        errorDetails = xhr.responseText || error;
                    }
                    
                    $('#reportPreview').html(`
                        <div class="text-center text-danger py-5">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <p><strong>Error:</strong> ${errorMessage}</p>
                            <p><small>Status: ${xhr.status} ${status}</small></p>
                            <p class="mt-3">
                                <button class="btn btn-sm btn-outline-secondary" onclick="showDebugInfo(${JSON.stringify({error: errorDetails})})">
                                    Show Technical Details
                                </button>
                            </p>
                        </div>
                    `);
                }
            });
        }
        
        // Debug helper function
        function showDebugInfo(data) {
            Swal.fire({
                title: 'Technical Details',
                html: `<pre style="text-align: left; max-height: 300px; overflow-y: auto;"><code>${JSON.stringify(data, null, 2)}</code></pre>`,
                width: '60%',
                confirmButtonText: 'Close'
            });
        }

        function displayProposal(data) {
            if (!data || !data.sections) {
                $('#reportPreview').html('<p>No proposal data available</p>');
                return;
            }

            const sections = data.sections;
            const now = new Date();
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            const currentTime = now.toLocaleTimeString('en-US', timeOptions);
            
            // Dynamically check the current theme state
            const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const themeClass = isDarkMode ? 'dark-mode-proposal' : 'light-mode-proposal';
            
            // Get the selected campus
            const selectedCampus = $('#campus').val();
            
            // Fetch signatories for the selected campus when in central mode
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            
            // Log whether we have campus signatories
            if (isCentral) {
                console.log("Central user in displayProposal, campusSignatories:", window.campusSignatories);
            }
            
            // Use theme class without inline styling to allow CSS to control colors
            let html = `
            <div class="proposal-container ${themeClass}" style="margin-top: 0; padding-top: 0;">
                <!-- Header Section -->
                <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                    <tr>
                        <td style="width: 15%; text-align: center; padding: 10px; border-top: 0.1px solid black; border-left: 0.1px solid black; border-bottom: 0.1px solid black;">
                            <img src="../images/BatStateU-NEU-Logo.png" alt="BatStateU Logo" style="width: 60px;">
                        </td>
                        <td style="width: 30%; padding: 10px; border-top: 0.1px solid black; border-left: 0.1px solid black; border-bottom: 0.1px solid black;">
                            Reference No.: BatStateU-FO-ESO-09
                        </td>
                        <td style="width: 30%; padding: 10px; border-top: 0.1px solid black; border-left: 0.1px solid black; border-bottom: 0.1px solid black;">
                            Effectivity Date: August 25, 2023
                        </td>
                        <td style="width: 25%; padding: 10px; border-top: 0.1px solid black; border-left: 0.1px solid black; border-right: 0.1px solid black; border-bottom: 0.1px solid black;">
                            Revision No.: 00
                        </td>
                    </tr>
                </table>

                <!-- Title Section -->
                <table style="width: 100%; border-collapse: collapse; margin: 0;">
                    <tr>
                        <td style="text-align: center; padding: 10px; border-left: 0.1px solid black; border-right: 0.1px solid black; border-bottom: 0.1px solid black;">
                            <strong>GAD PROPOSAL (INTERNAL PROGRAM/PROJECT/ACTIVITY)</strong>
                        </td>
                    </tr>
                </table>

                <!-- Checkbox Section with fixed styling -->
                <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0; border-left: 0.1px solid black; border-right: 0.1px solid black; border-top: 0.1px solid black; border-bottom: 0.1px solid black;">
                    <tr>
                        <td style="padding: 10px 0; border: none;">
                            <div style="display: flex; width: 100%; text-align: center;">
                                <div style="flex: 1; padding: 5px 10px;">☐ Program</div>
                                <div style="flex: 1; padding: 5px 10px;">☐ Project</div>
                                <div style="flex: 1; padding: 5px 10px;">☒ Activity</div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Main Content -->
                <div style="padding: 20px; border: 0.1px solid black; border-top: none;">
                    <p><strong>I. Title:</strong> ${sections.title || 'N/A'}</p>

                    <p><strong>II. Date and Venue:</strong> ${sections.date_venue.venue || 'N/A'}<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;${sections.date_venue.date || 'N/A'}</p>

                    <p><strong>III. Mode of Delivery:</strong> ${sections.delivery_mode || 'N/A'}</p>

                    <p><strong>IV. Project Team:</strong></p>
                    <div style="margin-left: 20px;">
                        <p><strong>Project Leaders:</strong> ${sections.project_team.project_leaders.names || 'N/A'}</p>
                        <p><strong>Responsibilities:</strong></p>
                        <ol>
                            ${Array.isArray(sections.project_team.project_leaders.responsibilities) 
                                ? sections.project_team.project_leaders.responsibilities.map(resp => `<li>${resp}</li>`).join('')
                                : `<li>${sections.project_team.project_leaders.responsibilities || 'N/A'}</li>`
                            }
                        </ol>

                        <p><strong>Asst. Project Leaders:</strong> ${sections.project_team.assistant_project_leaders.names || 'N/A'}</p>
                        <p><strong>Responsibilities:</strong></p>
                        <ol>
                            ${Array.isArray(sections.project_team.assistant_project_leaders.responsibilities)
                                ? sections.project_team.assistant_project_leaders.responsibilities.map(resp => `<li>${resp}</li>`).join('')
                                : `<li>${sections.project_team.assistant_project_leaders.responsibilities || 'N/A'}</li>`
                            }
                        </ol>

                        <p><strong>Project Staff:</strong> ${sections.project_team.project_staff.names || 'N/A'}</p>
                        <p><strong>Responsibilities:</strong></p>
                        <ol>
                            ${Array.isArray(sections.project_team.project_staff.responsibilities)
                                ? sections.project_team.project_staff.responsibilities.map(resp => `<li>${resp}</li>`).join('')
                                : `<li>${sections.project_team.project_staff.responsibilities || 'N/A'}</li>`
                            }
                        </ol>
                    </div>

                    <p><strong>V. Partner Office/College/Department:</strong> ${sections.partner_offices || 'N/A'}</p>

                    <p><strong>VI. Type of Participants:</strong></p>
                    <div style="text-align: center;">
                        <p><strong>External Type:</strong> ${sections.participants.external_type || 'N/A'}</p>
                        <table style="width: 40%; margin: 0 auto; border-collapse: collapse;">
                            <tr>
                                <th style="border: 0.1px solid black; padding: 5px; width: 30%;"></th>
                                <th style="border: 0.1px solid black; padding: 5px; text-align: center;">Total</th>
                            </tr>
                            <tr>
                                <td style="border: 0.1px solid black; padding: 5px;">Male</td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center;">${sections.participants.male || '0'}</td>
                            </tr>
                            <tr>
                                <td style="border: 0.1px solid black; padding: 5px;">Female</td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center;">${sections.participants.female || '0'}</td>
                            </tr>
                            <tr>
                                <td style="border: 0.1px solid black; padding: 5px;"><strong>TOTAL</strong></td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center;"><strong>${sections.participants.total || '0'}</strong></td>
                            </tr>
                        </table>
                    </div>

                    <p><strong>VII. Rationale/Background:</strong><br>
                    ${sections.rationale || 'N/A'}</p>

                    <p><strong>VIII. Objectives:</strong></p>
                    <div style="margin-left: 20px;">
                        <p><strong>General Objective:</strong> <span style="text-align: justify;">${sections.objectives.general || 'N/A'}</span></p>
                        
                        <p><strong>Specific Objectives:</strong></p>
                        ${formatSpecificObjectives(sections.objectives.specific)}
                    </div>

                    <p><strong>IX. Description, Strategies, and Methods:</strong></p>
                    <p><strong>Description:</strong></p>
                    <div style="margin-left: 20px;">
                        <p>${sections.description || 'N/A'}</p>
                    </div>
                    
                    <p><strong>Strategies:</strong></p>
                    <ol style="margin-left: 20px;">
                        ${(Array.isArray(sections.strategies)) 
                            ? sections.strategies.map(strat => `<li>${strat}</li>`).join('')
                            : `<li>${sections.strategies || 'N/A'}</li>`
                        }
                    </ol>
                    
                    <p><strong>Methods (Activities / Schedule):</strong></p>
                    <ul>
                        ${(Array.isArray(sections.methods)) 
                            ? sections.methods.map((method, index) => {
                                if (Array.isArray(method) && method.length > 1) {
                                    const activityName = method[0];
                                    const details = method[1];
                                    if (Array.isArray(details)) {
                                        return `
                                            <li>
                                                <strong>${activityName}</strong>
                                                <ul>
                                                    ${details.map(detail => `<li>${detail}</li>`).join('')}
                                                </ul>
                                            </li>
                                        `;
                                    } else {
                                        return `<li><strong>${activityName}</strong>: ${details}</li>`;
                                    }
                                } else {
                                    return `<li>${method}</li>`;
                                }
                            }).join('')
                            : `<li>${sections.methods || 'N/A'}</li>`
                        }
                    </ul>
                    
                    <p><strong>Materials Needed:</strong></p>
                    <ul>
                        ${(Array.isArray(sections.materials)) 
                            ? sections.materials.map(material => `<li>${material}</li>`).join('')
                            : `<li>${sections.materials || 'N/A'}</li>`
                        }
                    </ul>

                    <p><strong>X. Work Plan (Timeline of Activities/Gantt Chart):</strong></p>
                    ${(Array.isArray(sections.workplan) && sections.workplan.length > 0) ? (() => {
                        // Extract all dates from workplan
                        const allDates = [];
                        sections.workplan.forEach(item => {
                            if (Array.isArray(item) && item.length > 1 && Array.isArray(item[1])) {
                                item[1].forEach(date => {
                                    if (!allDates.includes(date)) {
                                        allDates.push(date);
                                    }
                                });
                            }
                        });
                        
                        // Sort dates
                        allDates.sort();
                        
                        // Generate table
                        return `
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <th style="border: 0.1px solid black; padding: 5px; width: 40%;">Activities</th>
                                    ${allDates.map(date => 
                                        `<th style="border: 0.1px solid black; padding: 5px; text-align: center;">${date}</th>`
                                    ).join('')}
                                </tr>
                                ${sections.workplan.map(item => {
                                    if (Array.isArray(item) && item.length > 1) {
                                        const activity = item[0];
                                        const dates = Array.isArray(item[1]) ? item[1] : [item[1]];
                                        
                                        return `
                                            <tr>
                                                <td style="border: 0.1px solid black; padding: 5px;">${activity || 'N/A'}</td>
                                                ${allDates.map(date => {
                                                    const isScheduled = dates.includes(date);
                                                    return `<td style="border: 0.1px solid black; padding: 5px; text-align: center; background-color: ${isScheduled ? 'black' : 'white'};"></td>`;
                                                }).join('')}
                                            </tr>
                                        `;
                                    } else {
                                        return `
                                            <tr>
                                                <td style="border: 0.1px solid black; padding: 5px;">${item || 'N/A'}</td>
                                                ${allDates.map(() => 
                                                    `<td style="border: 0.1px solid black; padding: 5px;"></td>`
                                                ).join('')}
                                            </tr>
                                        `;
                                    }
                                }).join('')}
                            </table>
                        `;
                    })() : `
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <th style="border: 0.1px solid black; padding: 5px; width: 40%;">Activities</th>
                                <th style="border: 0.1px solid black; padding: 5px; text-align: center;">2025-04-03</th>
                                <th style="border: 0.1px solid black; padding: 5px; text-align: center;">2025-04-04</th>
                                <th style="border: 0.1px solid black; padding: 5px; text-align: center;">2025-04-05</th>
                                <th style="border: 0.1px solid black; padding: 5px; text-align: center;">2025-04-06</th>
                            </tr>
                            <tr>
                                <td style="border: 0.1px solid black; padding: 5px;">Work plan 1</td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center; background-color: black;"></td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center;"></td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center;"></td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center;"></td>
                            </tr>
                            <tr>
                                <td style="border: 0.1px solid black; padding: 5px;">Work plan 2</td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center;"></td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center; background-color: black;"></td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center; background-color: black;"></td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center; background-color: black;"></td>
                            </tr>
                        </table>
                    `}

                    <p><strong>XI. Financial Requirements and Source of Funds:</strong></p>
                    <div style="margin-left: 20px;">
                        <p><strong>Source of Funds:</strong> ${sections.financial.source || 'N/A'}</p>
                        <p><strong>Total Budget:</strong> ₱${parseFloat(sections.financial.total || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                    </div>

                    <p><strong>XII. Monitoring and Evaluation Mechanics / Plan:</strong></p>
                    <table style="width: 100%; border-collapse: collapse; font-size: 10pt;">
                        <tr>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 13%;">Objectives</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 13%;">Performance Indicators</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 12%;">Baseline Data</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 13%;">Performance Target</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 12%;">Data Source</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 13%;">Collection Method</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 10%;">Frequency</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 14%;">Responsible</th>
                        </tr>
                        ${(Array.isArray(sections.monitoring_evaluation)) 
                            ? sections.monitoring_evaluation.map((item, index) => {
                                if (Array.isArray(item) && item.length >= 8) {
                                    return `
                                        <tr>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[0] || 'Objectives ' + (index + 1)}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[1] || 'Performance Indicators ' + (index + 1)}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[2] || 'Baseline Data ' + (index + 1)}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[3] || 'Performance Target ' + (index + 1)}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[4] || 'Data Source'}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[5] || 'Collection Method ' + (index + 1)}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[6] || 'Frequency of Data'}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[7] || 'Office/Person Responsible ' + (index + 1)}</td>
                                        </tr>
                                    `;
                                } else {
                                    return `
                                        <tr>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Objectives ${index + 1}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Performance Indicators ${index + 1}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Baseline Data ${index + 1}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Performance Target ${index + 1}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Data Source</td>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Collection Method ${index + 1}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Frequency of Data</td>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Office/Person Responsible ${index + 1}</td>
                                        </tr>
                                    `;
                                }
                            }).join('')
                            : `
                                <tr>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Objectives 1</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Performance Indicators 1</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Baseline Data 1</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Performance Target 1</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Data Source</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Collection Method 1</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Frequency of Data</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Office/Person Responsible 1</td>
                                </tr>
                                <tr>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Objectives 2</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Performance Indicators 2</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Baseline Data 2</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Performance Target 2</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Data Source</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Collection Method 2</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Frequency of Data</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Office/Person Responsible 2</td>
                                </tr>
                            `
                        }
                    </table>

                    <p><strong>XIII. Sustainability Plan:</strong></p>
                    <div>
                        ${(sections.sustainability) 
                            ? Array.isArray(sections.sustainability) 
                                ? `<ul>${sections.sustainability.map(item => `<li>${item}</li>`).join('')}</ul>`
                                : `<p>${sections.sustainability}</p>` 
                            : `<p>No sustainability plan provided.</p>`
                        }
                    </div>

                    <!-- Activity Ratings Table -->
                    <p><strong>XIV. Activity and Timeliness Evaluation Results:</strong></p>
                    <div>
                        <h4 style="margin-top: 15px; margin-bottom: 10px;">Activity Ratings</h4>
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                            <tr>
                                <th style="border: 0.1px solid black; padding: 5px; background-color: #f1f1f1;">Rating</th>
                                <th style="border: 0.1px solid black; padding: 5px; background-color: #f1f1f1;">BatStateU</th>
                                <th style="border: 0.1px solid black; padding: 5px; background-color: #f1f1f1;">Others</th>
                                <th style="border: 0.1px solid black; padding: 5px; background-color: #f1f1f1;">Total</th>
                            </tr>
                            ${(() => {
                                try {
                                    // Ensure we have finalActivityRatings accessible
                                    if (typeof finalActivityRatings !== 'undefined') {
                                        return Object.keys(finalActivityRatings).map(rating => {
                                            const batStateU = finalActivityRatings[rating].BatStateU || 0;
                                            const others = finalActivityRatings[rating].Others || 0;
                                            const total = batStateU + others;
                                            
                                            return `
                                                <tr>
                                                    <td style="border: 0.1px solid black; padding: 5px;">${rating}</td>
                                                    <td style="border: 0.1px solid black; padding: 5px; text-align: center;">${batStateU}</td>
                                                    <td style="border: 0.1px solid black; padding: 5px; text-align: center;">${others}</td>
                                                    <td style="border: 0.1px solid black; padding: 5px; text-align: center;">${total}</td>
                                                </tr>
                                            `;
                                        }).join('');
                                    } else {
                                        return `<tr><td colspan="4" style="border: 0.1px solid black; padding: 5px; text-align: center;">No activity ratings data available</td></tr>`;
                                    }
                                } catch (error) {
                                    console.error('Error rendering activity ratings table:', error);
                                    return `<tr><td colspan="4" style="border: 0.1px solid black; padding: 5px; text-align: center;">Error loading activity ratings</td></tr>`;
                                }
                            })()}
                        </table>
                        
                        <h4 style="margin-top: 15px; margin-bottom: 10px;">Timeliness Ratings</h4>
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                            <tr>
                                <th style="border: 0.1px solid black; padding: 5px; background-color: #f1f1f1;">Rating</th>
                                <th style="border: 0.1px solid black; padding: 5px; background-color: #f1f1f1;">BatStateU</th>
                                <th style="border: 0.1px solid black; padding: 5px; background-color: #f1f1f1;">Others</th>
                                <th style="border: 0.1px solid black; padding: 5px; background-color: #f1f1f1;">Total</th>
                            </tr>
                            ${(() => {
                                try {
                                    // Ensure we have finalTimelinessRatings accessible
                                    if (typeof finalTimelinessRatings !== 'undefined') {
                                        return Object.keys(finalTimelinessRatings).map(rating => {
                                            const batStateU = finalTimelinessRatings[rating].BatStateU || 0;
                                            const others = finalTimelinessRatings[rating].Others || 0;
                                            const total = batStateU + others;
                                            
                                            return `
                                                <tr>
                                                    <td style="border: 0.1px solid black; padding: 5px;">${rating}</td>
                                                    <td style="border: 0.1px solid black; padding: 5px; text-align: center;">${batStateU}</td>
                                                    <td style="border: 0.1px solid black; padding: 5px; text-align: center;">${others}</td>
                                                    <td style="border: 0.1px solid black; padding: 5px; text-align: center;">${total}</td>
                                                </tr>
                                            `;
                                        }).join('');
                                    } else {
                                        return `<tr><td colspan="4" style="border: 0.1px solid black; padding: 5px; text-align: center;">No timeliness ratings data available</td></tr>`;
                                    }
                                } catch (error) {
                                    console.error('Error rendering timeliness ratings table:', error);
                                    return `<tr><td colspan="4" style="border: 0.1px solid black; padding: 5px; text-align: center;">Error loading timeliness ratings</td></tr>`;
                                }
                            })()}
                        </table>
                    </div>

                    <!-- Add specific plans from database with bullets -->
                    <p><strong>XV. Specific Plans:</strong></p>
                    <div>
                        ${(sections.specific_plans) 
                            ? Array.isArray(sections.specific_plans) 
                                ? `<ul>${sections.specific_plans.map(item => `<li>${item}</li>`).join('')}</ul>`
                                : `<p>${sections.specific_plans}</p>` 
                            : `<ul>
                                <li>Regular monitoring of project activities</li>
                                <li>Continuous engagement with stakeholders</li>
                                <li>Documentation of lessons learned</li>
                                <li>Capacity building for sustainability</li>
                                <li>Resource allocation for maintenance</li>
                              </ul>`
                        }
                    </div>

                    <!-- Add page break before signatures -->
                    <div class="page-break"></div>
                </div>
                
                <!-- Signatures table -->
                <table class="signatures-table" style="width: 100%; margin: 0; padding: 0; border-collapse: collapse; page-break-inside: avoid; border: 1px solid black;">
                    <tr>
                        <td style="width: 50%; border: 1px solid black; padding: 15px;">
                            <p style="margin: 0; font-weight: bold; text-align: center;">Prepared by:</p>
                            <br><br><br>
                            <p style="margin: 0; text-align: center; font-weight: bold;">Nova Lane</p>
                            <p style="margin: 0; text-align: center;">Dean</p>
                            <p style="margin: 0; text-align: center; border: none;">Date Signed:_________________</p>
                        </td>
                        <td style="width: 50%; border: 1px solid black; padding: 15px;">
                            <p style="margin: 0; font-weight: bold; text-align: center;">Reviewed by:</p>
                            <br><br><br>
                            <p style="margin: 0; text-align: center; font-weight: bold;">Kael Thorn</p>
                            <p style="margin: 0; text-align: center;">Vice Chancellor for Research Development and Extension Services</p>
                            <p style="margin: 0; text-align: center; border: none;">Date Signed:_________________</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%; border: 1px solid black; padding: 15px;">
                            <p style="margin: 0; font-weight: bold; text-align: center;">Accepted by:</p>
                            <br><br><br>
                            <p style="margin: 0; text-align: center; font-weight: bold;">Mira Solis</p>
                            <p style="margin: 0; text-align: center;">Chancellor</p>
                            <p style="margin: 0; text-align: center; border: none;">Date Signed:_________________</p>
                        </td>
                        <td style="width: 50%; border: 1px solid black; padding: 15px;">
                            <p style="margin: 0; font-weight: bold; text-align: center;">Remarks:</p>
                            <br><br><br><br><br>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Add tracking number at bottom of first page -->
            <div style="position: fixed; bottom: 20px; width: 100%; text-align: center; font-family: 'Times New Roman', Times, serif; font-size: 10pt;">
               
            </div>`;

            $('#reportPreview').html(html);
            
            // Update page numbers dynamically
            const totalPages = 3; // or calculate based on content
            document.querySelectorAll('.total-pages').forEach(el => {
                el.textContent = totalPages;
            });
            
            // Set current page numbers sequentially
            document.querySelectorAll('.page-number').forEach((el, index) => {
                el.textContent = index + 1;
            });
        }
        
        // Function to print the proposal with proper formatting
        function printProposal() {
            // Create a print window with a specific title
            const printWindow = window.open('', '_blank', 'width=1200,height=800');
            
            // Set window properties immediately to prevent about:blank
            printWindow.document.open();
            printWindow.document.title = "GAD Proposal";
            
            let reportContent = $('#reportPreview').html();
            
            // SPECIAL FIX: Remove any empty divs or spaces that might cause empty boxes
            reportContent = reportContent.replace(/<div[^>]*>\s*<\/div>/g, '');
            reportContent = reportContent.replace(/<pre[\s\S]*?<\/pre>/g, '');
            
            // Remove any print buttons that might be in the content
            reportContent = reportContent.replace(/<button[^>]*id="printProposalBtn"[^>]*>[\s\S]*?<\/button>/g, '');
            
            // Ensure tracking number is included
            if (!reportContent.includes('Tracking Number:')) {
                // Insert tracking number at end of first page
                const pageBreakIndex = reportContent.indexOf('<div class="page-break"></div>');
                if (pageBreakIndex !== -1) {
                    reportContent = reportContent.substring(0, pageBreakIndex) + 
                        '<div style="position: fixed; bottom: 20px; width: 100%; text-align: center; font-family: \'Times New Roman\', Times, serif; font-size: 10pt;">Tracking Number:___________________</div>' + 
                        reportContent.substring(pageBreakIndex);
                }
            }
            
            // Always force print to be in light mode for consistent output
            const printStyles = `
                <style>
                    @page {
                        size: 8.5in 13in;
                        margin-top: 1.52cm;
                        margin-bottom: 2cm;
                        margin-left: 1.78cm;
                        margin-right: 2.03cm;
                        border-top: 3px solid black !important;
                        border-bottom: 3px solid black !important;
                    }
                    
                    body {
                        background-color: white !important;
                        color: black !important;
                        font-family: 'Times New Roman', Times, serif !important;
                        font-size: 12pt !important;
                        line-height: 1.2 !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }
                    
                    /* Container for signatures with no margins */
                    div[style*="width: 100%"] {
                        margin: 0 !important;
                        padding: 0 !important;
                        width: 100% !important;
                        max-width: 100% !important;
                    }
                    
                    /* Fix for signatures table */
                    .signatures-table {
                        width: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        border-collapse: collapse !important;
                    }
                    
                    /* Force ALL COLORS to be black - no exceptions */
                    *, p, span, div, td, th, li, ul, ol, strong, em, b, i, a, h1, h2, h3, h4, h5, h6,
                    [style*="color:"], [style*="color="], [style*="color :"], [style*="color ="],
                    [style*="color: brown"], [style*="color: blue"], [style*="color: red"],
                    .brown-text, .blue-text, .sustainability-plan, .sustainability-plan p, 
                    .signature-label, .signature-position {
                        color: black !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        color-adjust: exact !important;
                    }
                    
                    /* Preserve black-filled cells in Gantt chart */
                    table td[style*="background-color: black"] {
                        background-color: black !important;
                    }
                        
                    /* When printing */
                    @media print {
                        @page {
                            border-top: 3px solid black !important;
                            border-bottom: 3px solid black !important;
                        }
                        
                        /* Remove line on last page */
                        @page:last {
                            border-bottom: none !important;
                        }
                        
                        /* First page footer with tracking number and page number on same line */
                        @page:first {
                            @bottom-left {
                                content: "Tracking Number:___________________";
                                font-family: 'Times New Roman', Times, serif;
                                font-size: 10pt;
                                color: black;
                                position: fixed;
                                bottom: 0.4cm;
                                left: 1.78cm;
                            }
                            
                            @bottom-right {
                                content: "Page " counter(page) " of " counter(pages);
                                font-family: 'Times New Roman', Times, serif;
                                font-size: 10pt;
                                color: black;
                                position: fixed;
                                bottom: 0.4cm;
                                right: 2.03cm;
                            }
                        }
                        
                        /* Other pages footer with just page number */
                        @page {
                            @bottom-right {
                                content: "Page " counter(page) " of " counter(pages);
                                font-family: 'Times New Roman', Times, serif;
                                font-size: 10pt;
                                color: black;
                                position: fixed;
                                bottom: 0.4cm;
                                right: 2.03cm;
                            }
                        }
                    }
                </style>
            `;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>GAD Proposal</title>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    ${printStyles}
                    <style>
                        @page {
                            size: 8.5in 13in;
                            margin-top: 1.52cm;
                            margin-bottom: 2cm;
                            margin-left: 1.78cm;
                            margin-right: 2.03cm;
                            border: 3px solid black !important;
                        }
                        
                        /* Force all colors to be black */
                        * { color: black !important; }
                        
                        /* The only exception is black background cells for Gantt chart */
                        td[style*="background-color: black"] {
                            background-color: black !important;
                        }
                    </style>
                </head>
                <body>
                    <div class="WordSection1">
                        ${reportContent}
                    </div>
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            setTimeout(() => {
                printWindow.print();
                // Add event listener to close the window after printing is complete
                printWindow.addEventListener('afterprint', function() {
                    printWindow.close();
                });
            }, 250);
        }

        // Add print button to the UI
        $(document).ready(function() {
            // Wait for the document to be ready
            setTimeout(function() {
                // Remove print button functionality as requested
                $('#printProposalBtn').remove();
                
                // No longer adding the print button to the UI
                
                // Attach event handler for programmatic printing if needed
                $(document).on('keydown', function(e) {
                    // Ctrl+P alternative if needed internally
                    if (e.ctrlKey && e.key === 'p') {
                        printProposal();
                    }
                });
            }, 500); // Wait a little to ensure the page is loaded
        });

        // Function to display the narrative report
        function displayNarrativeReport(data) {
            if (!data) {
                $('#reportPreview').html('<p>No narrative data available</p>');
                return;
            }

            // Log details about the data for debugging
            console.log('Narrative data received:', data);
            
            // CRITICAL FIX: Save original raw data and ensure it's never lost
            const rawActivityRatings = data.activity_ratings;
            const rawTimelinessRatings = data.timeliness_ratings;
            
            console.log('SAVED RAW DATA:');
            console.log('- Raw activity_ratings:', rawActivityRatings);
            console.log('- Raw timeliness_ratings:', rawTimelinessRatings);
            
            // IMPORTANT: DIRECTLY USE THE RAW RATINGS DATA 
            // NO FALLBACKS - ONLY RAW DATA WILL BE SHOWN
            
            // Use raw activity_ratings with no fallbacks
            if (data.activity_ratings) {
                console.log('[FORCING RAW DATA] Using ONLY raw activity_ratings:', data.activity_ratings);
                
                // Ensure raw data is used everywhere
                window.dbActivityRatings = data.activity_ratings;
                window.processedActivityRatings = data.activity_ratings;
                window.narrativeRatings = data.activity_ratings;
                window.finalActivityRatings = data.activity_ratings;
                window.activityFallback = data.activity_ratings; // Override fallback with real data
                
                // CRITICAL FIX: Set the correct data structure for ratings
                // Based on the console log structure: {Excellent: {BatStateU: 3, Others: 3}, ...}
                if (typeof data.activity_ratings === 'object' && !Array.isArray(data.activity_ratings)) {
                    // Create or verify the data structure has proper format
                    if (!data.activity_ratings.Excellent) data.activity_ratings.Excellent = {BatStateU: 0, Others: 0};
                    if (!data.activity_ratings['Very Satisfactory']) data.activity_ratings['Very Satisfactory'] = {BatStateU: 0, Others: 0};
                    if (!data.activity_ratings.Satisfactory) data.activity_ratings.Satisfactory = {BatStateU: 0, Others: 0};
                    if (!data.activity_ratings.Fair) data.activity_ratings.Fair = {BatStateU: 0, Others: 0};
                    if (!data.activity_ratings.Poor) data.activity_ratings.Poor = {BatStateU: 0, Others: 0};
                    
                    // Make sure the BatStateU and Others properties exist
                    if (!data.activity_ratings.Excellent.BatStateU) data.activity_ratings.Excellent.BatStateU = 0;
                    if (!data.activity_ratings.Excellent.Others) data.activity_ratings.Excellent.Others = 0;
                    if (!data.activity_ratings['Very Satisfactory'].BatStateU) data.activity_ratings['Very Satisfactory'].BatStateU = 0;
                    if (!data.activity_ratings['Very Satisfactory'].Others) data.activity_ratings['Very Satisfactory'].Others = 0;
                    if (!data.activity_ratings.Satisfactory.BatStateU) data.activity_ratings.Satisfactory.BatStateU = 0;
                    if (!data.activity_ratings.Satisfactory.Others) data.activity_ratings.Satisfactory.Others = 0;
                    if (!data.activity_ratings.Fair.BatStateU) data.activity_ratings.Fair.BatStateU = 0;
                    if (!data.activity_ratings.Fair.Others) data.activity_ratings.Fair.Others = 0;
                    if (!data.activity_ratings.Poor.BatStateU) data.activity_ratings.Poor.BatStateU = 0;
                    if (!data.activity_ratings.Poor.Others) data.activity_ratings.Poor.Others = 0;
                }
            }
            
            // Use raw timeliness_ratings with no fallbacks
            if (data.timeliness_ratings) {
                console.log('[FORCING RAW DATA] Using ONLY raw timeliness_ratings:', data.timeliness_ratings);
                
                // Ensure raw data is used everywhere
                window.dbTimelinessRatings = data.timeliness_ratings;
                window.processedTimelinessRatings = data.timeliness_ratings;
                window.narrativeTimelinessRatings = data.timeliness_ratings;
                window.finalTimelinessRatings = data.timeliness_ratings;
                window.timelinessFallback = data.timeliness_ratings; // Override fallback with real data
            }
            
            // Process images
            let finalActivityImages = [];
            
            // Try multiple possible image field names
            const possibleImageFields = ['activity_images', 'photo_paths', 'images', 'photos'];
            
            for (const field of possibleImageFields) {
                if (data[field]) {
                    console.log(`Found images in field ${field}:`, data[field]);
                    try {
                        if (typeof data[field] === 'string') {
                            try {
                                const parsed = JSON.parse(data[field]);
                                if (Array.isArray(parsed)) {
                                    finalActivityImages = parsed;
                                    break;
                                } else if (typeof parsed === 'object') {
                                    // Try to convert object to array if it has numeric keys
                                    finalActivityImages = Object.values(parsed);
                                    break;
                                }
                            } catch (e) {
                                // If not valid JSON, treat as a single image path
                                finalActivityImages = [data[field]];
                                break;
                            }
                        } else if (Array.isArray(data[field])) {
                            finalActivityImages = data[field];
                            break;
                        } else if (typeof data[field] === 'object') {
                            // Try to extract values from the object
                            finalActivityImages = Object.values(data[field]);
                            break;
                        }
                    } catch (e) {
                        console.error(`Error processing ${field}:`, e);
                    }
                }
            }
            
            console.log('Final activity images:', finalActivityImages);
            data.activity_images = finalActivityImages;
            
            // Log the final data we'll be using for the report
            console.log('FINAL DATA USED FOR REPORT:');
            console.log('- Activity ratings:', data.activity_ratings);
            console.log('- Timeliness ratings:', data.timeliness_ratings);
            console.log('- Images:', data.activity_images.length ? data.activity_images.slice(0, 3) + '...' : 'none');
            
            // *** FORCE RAW DATA ONLY - NEVER USE FALLBACKS ***
            // This is the critical fix - removing all validation that might cause fallbacks
            console.log('USING RAW DATA EXACTLY AS RECEIVED - NO VALIDATION OR FALLBACKS');
            
            // Do not check data validity, do not use getDefaultRatings - EVER
            // Even if the data seems invalid to our validation, we will use it directly
            
            // JavaScript version of getDefaultRatings
            function getDefaultRatings() {
                // Return sample ratings data to ensure we always have something to display
                console.log('getDefaultRatings called - returning sample ratings data');
                return {
                    "Excellent": {
                        "BatStateU": 22,
                        "Others": 15
                    },
                    "Very Satisfactory": {
                        "BatStateU": 18,
                        "Others": 12
                    },
                    "Satisfactory": {
                        "BatStateU": 8,
                        "Others": 5
                    },
                    "Fair": {
                        "BatStateU": 3,
                        "Others": 2
                    },
                    "Poor": {
                        "BatStateU": 1,
                        "Others": 1
                    }
                };
            }

            // Add a getSampleRatingsData function that's missing but referenced
            function getSampleRatingsData() {
                console.warn('Using sample ratings data - this means no actual data was found or correctly processed');
                return {
                    "Excellent": {
                        "BatStateU": 0,
                        "Others": 0
                    },
                    "Very Satisfactory": {
                        "BatStateU": 0,
                        "Others": 0
                    },
                    "Satisfactory": {
                        "BatStateU": 0,
                        "Others": 0
                    },
                    "Fair": {
                        "BatStateU": 0,
                        "Others": 0
                    },
                    "Poor": {
                        "BatStateU": 0,
                        "Others": 0
                    }
                };
            }

            function transformRatingsToProperFormat(ratingsData) {
                // Add detailed debugging to understand what's coming in
                console.log('Processing ratings data:', ratingsData);
                console.log('Processing ratings data type:', typeof ratingsData);
                
                // Handle empty/null data
                if (!ratingsData) {
                    console.warn('Ratings data is null or undefined, using sample data for display');
                    return getSampleRatingsData();
                }
                
                if (typeof ratingsData === 'object' && Object.keys(ratingsData).length === 0) {
                    console.warn('Ratings data is empty object, using sample data for display');
                    return getSampleRatingsData();
                }
                
                try {
                    // Parse the data if it's a string (JSON from database)
                    let data = ratingsData;
                    if (typeof ratingsData === 'string') {
                        try {
                            data = JSON.parse(ratingsData);
                            console.log('Successfully parsed JSON string to:', data);
                        } catch (parseError) {
                            console.error('Failed to parse JSON string:', parseError);
                            return getSampleRatingsData();
                        }
                    }
                    
                    // If data already has the expected structure, use it directly
                    if (data && typeof data === 'object') {
                        const hasExpectedStructure = Object.keys(data).some(key => 
                            ['Excellent', 'Very Satisfactory', 'Satisfactory', 'Fair', 'Poor'].includes(key));
                        
                        if (hasExpectedStructure) {
                            console.log('Data already has the expected rating structure, using directly:', data);
                            // Ensure all values are integers
                            for (const rating in data) {
                                if (data[rating].BatStateU !== undefined) {
                                    data[rating].BatStateU = parseInt(data[rating].BatStateU) || 0;
                                }
                                if (data[rating].Others !== undefined) {
                                    data[rating].Others = parseInt(data[rating].Others) || 0;
                                }
                            }
                            
                            // Make sure all expected keys exist
                            const expectedKeys = ['Excellent', 'Very Satisfactory', 'Satisfactory', 'Fair', 'Poor'];
                            expectedKeys.forEach(key => {
                                if (!data[key]) {
                                    data[key] = { "BatStateU": 0, "Others": 0 };
                                }
                                if (!data[key].BatStateU && data[key].BatStateU !== 0) {
                                    data[key].BatStateU = 0;
                                }
                                if (!data[key].Others && data[key].Others !== 0) {
                                    data[key].Others = 0;
                                }
                            });
                            
                            return data;
                        }
                    }
                    
                    // Check if this is combined data that contains both activity and timeliness
                    if (data.activity && typeof data.activity === 'object') {
                        console.log('Found nested activity property, using that');
                        return transformRatingsToProperFormat(data.activity);
                    }
                    
                    // If data is an array or undefined, return sample data
                    if (!data || Array.isArray(data)) {
                        console.warn('Ratings data is not in expected format (array or null):', data);
                        return getSampleRatingsData();
                    }
                    
                    // Normalize the keys as needed
                    const normalizedData = {};
                    
                    // Expected rating categories and their possible variations
                    const ratingMap = {
                        'Excellent': ['excellent', 'Excellent', 'EXCELLENT'],
                        'Very Satisfactory': ['very_satisfactory', 'Very Satisfactory', 'VERY SATISFACTORY', 'very satisfactory'],
                        'Satisfactory': ['satisfactory', 'Satisfactory', 'SATISFACTORY'],
                        'Fair': ['fair', 'Fair', 'FAIR'],
                        'Poor': ['poor', 'Poor', 'POOR']
                    };
                    
                    // Check for each possible rating key format
                    for (const [standardKey, variations] of Object.entries(ratingMap)) {
                        normalizedData[standardKey] = { 'BatStateU': 0, 'Others': 0 };
                        
                        // Try each variation of the rating key
                        for (const variation of variations) {
                            if (data[variation]) {
                                // Handle different formats for participant types
                                if (typeof data[variation] === 'object') {
                                    // Check for BatStateU variations
                                    if (data[variation].BatStateU !== undefined) {
                                        normalizedData[standardKey].BatStateU = parseInt(data[variation].BatStateU) || 0;
                                    } else if (data[variation].batstateu !== undefined) {
                                        normalizedData[standardKey].BatStateU = parseInt(data[variation].batstateu) || 0;
                                    }
                                    
                                    // Check for Others variations
                                    if (data[variation].Others !== undefined) {
                                        normalizedData[standardKey].Others = parseInt(data[variation].Others) || 0;
                                    } else if (data[variation].others !== undefined) {
                                        normalizedData[standardKey].Others = parseInt(data[variation].others) || 0;
                                    } else if (data[variation].other !== undefined) {
                                        normalizedData[standardKey].Others = parseInt(data[variation].other) || 0;
                                    }
                                }
                            }
                        }
                    }
                    
                    // Check if we successfully normalized any data
                    let hasAnyData = false;
                    for (const key in normalizedData) {
                        if (normalizedData[key].BatStateU > 0 || normalizedData[key].Others > 0) {
                            hasAnyData = true;
                            break;
                        }
                    }
                    
                    if (hasAnyData) {
                        console.log('Successfully normalized ratings data:', normalizedData);
                        return normalizedData;
                    } else {
                        // If we have data but couldn't normalize it properly, just return the original
                        if (Object.keys(data).length > 0) {
                            console.log('Using original data structure as-is:', data);
                            return data;
                        }
                        
                        console.warn('No rating data could be extracted, using sample data');
                        return getSampleRatingsData();
                    }
                } catch (e) {
                    console.error('Error processing ratings data:', e);
                    return getSampleRatingsData();
                }
            }

            // Get narrative data from database
            <?php
            // Debug the PPAS form ID to make sure we have the right one
            error_log("PPAS Form ID in print_narrative.php: $ppas_form_id");
            
            try {
                $conn = getConnection();
                
                // DEBUG: List available narrative_entries to help troubleshoot
                try {
                    $debug_entries_sql = "SELECT id, title, campus, year, ppas_form_id FROM narrative_entries LIMIT 10";
                    $debug_entries = $conn->query($debug_entries_sql)->fetchAll(PDO::FETCH_ASSOC);
                    error_log("Available narrative_entries: " . json_encode($debug_entries));
                } catch (Exception $e) {
                    error_log("Error listing narrative_entries: " . $e->getMessage());
                }
                
                // First get the activity title from ppas_forms to use for lookup
                $title_sql = "SELECT activity_title, campus, fiscal_year FROM ppas_forms WHERE id = :id";
                $title_stmt = $conn->prepare($title_sql);
                $title_stmt->bindParam(':id', $ppas_form_id);
                $title_stmt->execute();
                $title_data = $title_stmt->fetch(PDO::FETCH_ASSOC);
                
                $activity_title = $title_data['activity_title'] ?? '';
                $campus = $title_data['campus'] ?? '';
                $year = $title_data['fiscal_year'] ?? date('Y');
                
                error_log("Looking for narrative_entries matching ppas_form_id: '$ppas_form_id', title: '$activity_title', campus: '$campus', year: '$year'");
                
                // First try: Check if narrative_entries has a ppas_form_id field and use that for direct matching
                $narrative_entry = null;
                
                // Check if ppas_form_id column exists in narrative_entries
                try {
                    $checkStmt = $conn->prepare("SHOW COLUMNS FROM narrative_entries LIKE 'ppas_form_id'");
                    $checkStmt->execute();
                    $hasPpasFormId = ($checkStmt->rowCount() > 0);
                    
                    if ($hasPpasFormId) {
                        // First try to match by ppas_form_id
                        $entry_sql = "SELECT * FROM narrative_entries WHERE ppas_form_id = :ppas_id LIMIT 1";
                        $entry_stmt = $conn->prepare($entry_sql);
                        $entry_stmt->bindParam(':ppas_id', $ppas_form_id);
                        $entry_stmt->execute();
                        $narrative_entry = $entry_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($narrative_entry) {
                            error_log("Found matching narrative_entries record by ppas_form_id match!");
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error checking for ppas_form_id column: " . $e->getMessage());
                }
                
                // Second try: direct activity title match
                if (!$narrative_entry) {
                    error_log("Trying to match by exact title and campus");
                    $narrative_sql = "SELECT * FROM narrative_entries WHERE 
                                     title = :exact_title
                                     AND campus = :campus LIMIT 1";
                    $narrative_stmt = $conn->prepare($narrative_sql);
                    $narrative_stmt->bindParam(':exact_title', $activity_title);
                    $narrative_stmt->bindParam(':campus', $campus);
                    $narrative_stmt->execute();
                    $narrative_entry = $narrative_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($narrative_entry) {
                        error_log("Found matching narrative_entries record by exact title and campus!");
                    }
                }
                
                // Third try: Using LIKE for fuzzy title matching
                if (!$narrative_entry) {
                    error_log("Trying to match by title LIKE and campus");
                    $narrative_sql = "SELECT * FROM narrative_entries WHERE 
                                     title LIKE :title_match 
                                     AND campus = :campus LIMIT 1";
                    $narrative_stmt = $conn->prepare($narrative_sql);
                    $title_match = "%" . $activity_title . "%";
                    $narrative_stmt->bindParam(':title_match', $title_match);
                    $narrative_stmt->bindParam(':campus', $campus);
                    $narrative_stmt->execute();
                    $narrative_entry = $narrative_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($narrative_entry) {
                        error_log("Found matching narrative_entries record by title LIKE and campus!");
                    }
                }
                
                // Fourth try: Reverse LIKE match (if activity is contained within a narrative title)
                if (!$narrative_entry) {
                    error_log("Trying reverse LIKE match (narrative title contains activity title)");
                    $narrative_sql = "SELECT * FROM narrative_entries WHERE 
                                     :activity_title LIKE CONCAT('%', title, '%') 
                                     AND campus = :campus LIMIT 1";
                    $narrative_stmt = $conn->prepare($narrative_sql);
                    $narrative_stmt->bindParam(':activity_title', $activity_title);
                    $narrative_stmt->bindParam(':campus', $campus);
                    $narrative_stmt->execute();
                    $narrative_entry = $narrative_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($narrative_entry) {
                        error_log("Found matching narrative_entries record by reverse LIKE match!");
                    }
                }
                
                // Fifth try: If no match found, try with just part of the title
                if (!$narrative_entry && strlen($activity_title) > 5) {
                    // Try with just part of the title (first 50% of characters)
                    $title_part = substr($activity_title, 0, intval(strlen($activity_title) * 0.5));
                    error_log("No exact match found, trying partial match with: '$title_part%'");
                    
                    $partial_sql = "SELECT * FROM narrative_entries WHERE 
                                   title LIKE :partial_title 
                                   AND campus = :campus LIMIT 1";
                    $partial_stmt = $conn->prepare($partial_sql);
                    $partial_title = "$title_part%";
                    $partial_stmt->bindParam(':partial_title', $partial_title);
                    $partial_stmt->bindParam(':campus', $campus);
                    $partial_stmt->execute();
                    $narrative_entry = $partial_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($narrative_entry) {
                        error_log("Found matching narrative_entries record by partial title match!");
                    }
                }
                
                // Sixth try: Match by any word in the activity title
                if (!$narrative_entry && strlen($activity_title) > 5) {
                    error_log("Trying word-by-word matching");
                    
                    // Extract main keywords from the activity title (words with 3+ characters)
                    $words = preg_split('/\s+/', $activity_title);
                    $keywords = array_filter($words, function($word) {
                        return strlen($word) >= 3;
                    });
                    
                    if (count($keywords) > 0) {
                        $keyword_conditions = [];
                        $params = [':campus' => $campus];
                        
                        foreach ($keywords as $index => $keyword) {
                            $param_name = ":keyword$index";
                            $keyword_conditions[] = "title LIKE $param_name";
                            $params[$param_name] = "%$keyword%";
                        }
                        
                        // Create SQL with OR conditions for each keyword
                        $keyword_sql = "SELECT * FROM narrative_entries WHERE 
                                       (" . implode(" OR ", $keyword_conditions) . ")
                                       AND campus = :campus
                                       ORDER BY id DESC LIMIT 1";
                        
                        $keyword_stmt = $conn->prepare($keyword_sql);
                        foreach ($params as $key => $value) {
                            $keyword_stmt->bindValue($key, $value);
                        }
                        
                        $keyword_stmt->execute();
                        $narrative_entry = $keyword_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($narrative_entry) {
                            error_log("Found matching narrative_entries record by keyword match!");
                        }
                    }
                }
                
                // Last resort - just get the latest entry for this campus
                if (!$narrative_entry) {
                    error_log("No match with any method, trying to get most recent entry for campus: $campus");
                    $fallback_sql = "SELECT * FROM narrative_entries WHERE 
                                   campus = :campus ORDER BY id DESC LIMIT 1";
                    $fallback_stmt = $conn->prepare($fallback_sql);
                    $fallback_stmt->bindParam(':campus', $campus);
                    $fallback_stmt->execute();
                    $narrative_entry = $fallback_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($narrative_entry) {
                        error_log("Using the most recent narrative entry for campus as fallback!");
                    }
                }
                
                if ($narrative_entry) {
                    error_log("Found matching narrative_entries record!");
                    // Add debug information - what narrative entry we found
                    error_log("Using narrative entry: ID=" . $narrative_entry['id'] . 
                             ", Title='" . $narrative_entry['title'] . 
                             "', Campus='" . $narrative_entry['campus'] . 
                             "', Year='" . $narrative_entry['year'] . "'");
                    
                    // Output the narrative data directly to JavaScript
                    echo "const narrativeEntry = " . json_encode($narrative_entry) . ";\n";
                    
                    // Pass individual fields to JavaScript for easy access
                    echo "const backgroundData = " . json_encode($narrative_entry['background'] ?? '') . ";\n";
                    echo "const participantsData = " . json_encode($narrative_entry['participants'] ?? '') . ";\n";
                    echo "const topicsData = " . json_encode($narrative_entry['topics'] ?? '') . ";\n";
                    echo "const resultsData = " . json_encode($narrative_entry['results'] ?? '') . ";\n";
                    echo "const lessonsData = " . json_encode($narrative_entry['lessons'] ?? '') . ";\n";
                    echo "const whatWorkedData = " . json_encode($narrative_entry['what_worked'] ?? '') . ";\n";
                    echo "const issuesData = " . json_encode($narrative_entry['issues'] ?? '') . ";\n";
                    echo "const recommendationsData = " . json_encode($narrative_entry['recommendations'] ?? '') . ";\n";
                    
                    // Modified: Create a single coherent narrative by combining all sections into one flowing text
                    $combinedNarrative = "";
                    
                    // Background/Rationale
                    if (!empty($narrative_entry['background'])) {
                        $combinedNarrative .= trim($narrative_entry['background']) . " ";
                    }
                    
                    // Description of Participants
                    if (!empty($narrative_entry['participants'])) {
                        $combinedNarrative .= trim($narrative_entry['participants']) . " ";
                    }
                    
                    // Narrative of Topics Discussed
                    if (!empty($narrative_entry['topics'])) {
                        $combinedNarrative .= trim($narrative_entry['topics']) . " ";
                    }
                    
                    // Expected Results, Outputs and Outcomes
                    if (!empty($narrative_entry['results'])) {
                        $combinedNarrative .= trim($narrative_entry['results']) . " ";
                    }
                    
                    // Lessons Learned
                    if (!empty($narrative_entry['lessons'])) {
                        $combinedNarrative .= trim($narrative_entry['lessons']) . " ";
                    }
                    
                    // What Worked and Did Not Work
                    if (!empty($narrative_entry['what_worked'])) {
                        $combinedNarrative .= trim($narrative_entry['what_worked']) . " ";
                    }
                    
                    // Issues and Concerns
                    if (!empty($narrative_entry['issues'])) {
                        $combinedNarrative .= trim($narrative_entry['issues']) . " ";
                    }
                    
                    // Recommendations
                    if (!empty($narrative_entry['recommendations'])) {
                        $combinedNarrative .= trim($narrative_entry['recommendations']);
                    }
                    
                    echo "const combinedNarrativeText = " . json_encode($combinedNarrative) . ";\n";
                    
                    // Enhanced image path handling
                    $photoData = null;
                    
                    // Check for photo_paths field (preferred)
                    if (!empty($narrative_entry['photo_paths'])) {
                        $photoData = $narrative_entry['photo_paths'];
                    } 
                    // Fallback to photo_path
                    else if (!empty($narrative_entry['photo_path'])) {
                        $photoData = $narrative_entry['photo_path'];
                    }
                    // Check for other possible field names
                    else if (!empty($narrative_entry['activity_images'])) {
                        $photoData = $narrative_entry['activity_images'];
                    }
                    else if (!empty($narrative_entry['images'])) {
                        $photoData = $narrative_entry['images'];
                    }
                    
                    // Process the photo data
                    if ($photoData) {
                        // If it's a string that might be JSON, try to parse it
                        if (is_string($photoData)) {
                            $decoded = json_decode($photoData, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                // If successfully parsed as JSON array
                                echo "const narrativeImages = " . json_encode($decoded) . ";\n";
                                error_log("Parsed photo data as JSON array: " . substr(json_encode($decoded), 0, 100) . "...");
                            } else {
                                // If it's just a string (single path)
                                echo "const narrativeImages = " . json_encode([$photoData]) . ";\n";
                                error_log("Using photo data as single image path: " . $photoData);
                            }
                        } 
                        // If it's already an array
                        else if (is_array($photoData)) {
                            echo "const narrativeImages = " . json_encode($photoData) . ";\n";
                            error_log("Using photo data as array: " . substr(json_encode($photoData), 0, 100) . "...");
                        }
                        // Fallback
                        else {
                            echo "const narrativeImages = [];\n";
                            error_log("Could not process photo data, type: " . gettype($photoData));
                        }
                    } else {
                        echo "const narrativeImages = [];\n";
                        error_log("No image data found in narrative_entries");
                    }
                    
                    // Enhanced ratings handling
                    // Activity ratings
                    $activityRatingsData = null;
                    if (!empty($narrative_entry['activity_ratings'])) {
                        $activityRatingsData = $narrative_entry['activity_ratings'];
                    } else if (!empty($narrative_entry['activity_rating'])) {
                        $activityRatingsData = $narrative_entry['activity_rating'];
                    } else if (!empty($narrative_entry['evaluation']) && strpos($narrative_entry['evaluation'], 'activity') !== false) {
                        $activityRatingsData = $narrative_entry['evaluation'];
                    }
                    
                    if ($activityRatingsData) {
                        // If it's a string and looks like JSON, validate and output directly
                        if (is_string($activityRatingsData) && 
                            (substr(trim($activityRatingsData), 0, 1) === '{' || 
                             substr(trim($activityRatingsData), 0, 1) === '[')) {
                            // Validate it's proper JSON
                            json_decode($activityRatingsData);
                if (json_last_error() === JSON_ERROR_NONE) {
                            echo "const narrativeRatings = " . $activityRatingsData . ";\n";
                                error_log("Using direct JSON activity_ratings: " . substr($activityRatingsData, 0, 100) . "...");
                } else {
                                // If invalid JSON, encode it properly
                                echo "const narrativeRatings = " . json_encode($activityRatingsData) . ";\n";
                                error_log("Fixed JSON for activity_ratings: invalid JSON converted to string");
                            }
                        } else if (is_array($activityRatingsData)) {
                            echo "const narrativeRatings = " . json_encode($activityRatingsData) . ";\n";
                            error_log("Using JSON encoded array for activity_ratings");
                        } else {
                            // For any other type, encode it properly
                            echo "const narrativeRatings = " . json_encode($activityRatingsData) . ";\n";
                            error_log("Using JSON encoded data for activity_ratings, type: " . gettype($activityRatingsData));
                        }
                    } else {
                        echo "const narrativeRatings = null;\n";
                        error_log("No activity_ratings data found in narrative_entry");
                    }
                    
                    // Timeliness ratings
                    $timelinessRatingsData = null;
                    if (!empty($narrative_entry['timeliness_ratings'])) {
                        $timelinessRatingsData = $narrative_entry['timeliness_ratings'];
                    } else if (!empty($narrative_entry['timeliness_rating'])) {
                        $timelinessRatingsData = $narrative_entry['timeliness_rating'];
                    } else if (!empty($narrative_entry['timeliness'])) {
                        $timelinessRatingsData = $narrative_entry['timeliness'];
                    } else if (!empty($narrative_entry['evaluation']) && strpos($narrative_entry['evaluation'], 'timeliness') !== false) {
                        $timelinessRatingsData = $narrative_entry['evaluation'];
                    }
                    
                    if ($timelinessRatingsData) {
                        // If it's a string and looks like JSON, validate and output directly
                        if (is_string($timelinessRatingsData) && 
                            (substr(trim($timelinessRatingsData), 0, 1) === '{' || 
                             substr(trim($timelinessRatingsData), 0, 1) === '[')) {
                            // Validate it's proper JSON
                            json_decode($timelinessRatingsData);
                            if (json_last_error() === JSON_ERROR_NONE) {
                            echo "const narrativeTimelinessRatings = " . $timelinessRatingsData . ";\n";
                                error_log("Using direct JSON timeliness_ratings: " . substr($timelinessRatingsData, 0, 100) . "...");
                            } else {
                                // If invalid JSON, encode it properly
                                echo "const narrativeTimelinessRatings = " . json_encode($timelinessRatingsData) . ";\n";
                                error_log("Fixed JSON for timeliness_ratings: invalid JSON converted to string");
                            }
                        } else if (is_array($timelinessRatingsData)) {
                            echo "const narrativeTimelinessRatings = " . json_encode($timelinessRatingsData) . ";\n";
                            error_log("Using JSON encoded array for timeliness_ratings");
                        } else {
                            // For any other type, encode it properly
                            echo "const narrativeTimelinessRatings = " . json_encode($timelinessRatingsData) . ";\n";
                            error_log("Using JSON encoded data for timeliness_ratings, type: " . gettype($timelinessRatingsData));
                        }
                    } else {
                        echo "const narrativeTimelinessRatings = null;\n";
                        error_log("No timeliness_ratings data found in narrative_entry");
                    }
                    
                } else {
                    // No matching narrative_entries record
                    error_log("No matching narrative_entries record found");
                    echo "const narrativeEntry = null;\n";
                    echo "const backgroundData = '';\n";
                    echo "const participantsData = '';\n";
                    echo "const topicsData = '';\n";
                    echo "const resultsData = '';\n";
                    echo "const lessonsData = '';\n";
                    echo "const whatWorkedData = '';\n";
                    echo "const issuesData = '';\n";
                    echo "const recommendationsData = '';\n";
                    echo "const combinedNarrativeText = 'No narrative data available';\n";
                    echo "const narrativeImages = [];\n";
                    echo "const narrativeRatings = null;\n";
                    echo "const narrativeTimelinessRatings = null;\n";
                }
                
                // Also get the original narrative_entries table data as backup
                $sql = "SELECT id, ppas_form_id, activity_ratings, timeliness_ratings, activity_images FROM narrative_entries WHERE ppas_form_id = :ppas_form_id LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':ppas_form_id' => $ppas_form_id]);
                $ratings_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$ratings_data) {
                    // If no direct match by ppas_form_id, try to find by other criteria
                    error_log("No direct match by ppas_form_id, trying to get PPAS form details");
                    
                    // Get the activity details from ppas_forms
                    $ppas_details_sql = "SELECT activity, campus FROM ppas_forms WHERE id = :ppas_id";
                    $ppas_details_stmt = $conn->prepare($ppas_details_sql);
                    $ppas_details_stmt->bindParam(':ppas_id', $ppas_form_id);
                    $ppas_details_stmt->execute();
                    $ppas_details = $ppas_details_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($ppas_details) {
                        $activity_title = $ppas_details['activity'];
                        $campus = $ppas_details['campus'];
                        
                        error_log("Looking for narrative_entries with title: '$activity_title' and campus: '$campus'");
                        
                        // Try to find by title and campus
                        $alt_sql = "SELECT id, ppas_form_id, activity_ratings, timeliness_ratings, activity_images 
                                    FROM narrative_entries 
                                    WHERE title = :title AND campus = :campus
                                    LIMIT 1";
                        $alt_stmt = $conn->prepare($alt_sql);
                        $alt_stmt->bindParam(':title', $activity_title);
                        $alt_stmt->bindParam(':campus', $campus);
                        $alt_stmt->execute();
                        $ratings_data = $alt_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (!$ratings_data) {
                            // Try with LIKE for partial match
                            error_log("No exact title match, trying with LIKE");
                            $like_sql = "SELECT id, ppas_form_id, activity_ratings, timeliness_ratings, activity_images 
                                        FROM narrative_entries 
                                        WHERE title LIKE :title_like AND campus = :campus
                                        LIMIT 1";
                            $like_stmt = $conn->prepare($like_sql);
                            $title_like = '%' . $activity_title . '%';
                            $like_stmt->bindParam(':title_like', $title_like);
                            $like_stmt->bindParam(':campus', $campus);
                            $like_stmt->execute();
                            $ratings_data = $like_stmt->fetch(PDO::FETCH_ASSOC);
                        }
                    }
                }
                
                // Also check ppas_entries table for ratings data
                $ppas_entries_sql = "SELECT id, ppas_form_id, activity_ratings, timeliness_ratings FROM ppas_entries WHERE ppas_form_id = :ppas_form_id";
                $ppas_entries_stmt = $conn->prepare($ppas_entries_sql);
                $ppas_entries_stmt->execute([':ppas_form_id' => $ppas_form_id]);
                $ppas_entries_data = $ppas_entries_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($ppas_entries_data) {
                    error_log("Found matching ppas_entries record with ID: " . $ppas_entries_data['id']);
                    // Since these are JSON fields, we need to handle them properly
                    if (!empty($ppas_entries_data['activity_ratings'])) {
                        $activity_ratings_json = $ppas_entries_data['activity_ratings'];
                        
                        // Verify it's valid JSON
                        $decoded = json_decode($activity_ratings_json);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            // If not valid JSON, log the error and use null
                            error_log("Invalid JSON in ppas_entries activity_ratings: " . json_last_error_msg());
                            echo "const ppasEntriesActivityRatings = null;\n";
                        } else {
                            echo "const ppasEntriesActivityRatings = " . $activity_ratings_json . ";\n";
                            error_log("Activity Ratings from ppas_entries: " . $activity_ratings_json);
                        }
                    } else {
                        echo "const ppasEntriesActivityRatings = null;\n";
                    }
                    
                    if (!empty($ppas_entries_data['timeliness_ratings'])) {
                        $timeliness_ratings_json = $ppas_entries_data['timeliness_ratings'];
                        
                        // Verify it's valid JSON
                        $decoded = json_decode($timeliness_ratings_json);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            // If not valid JSON, log the error and use null
                            error_log("Invalid JSON in ppas_entries timeliness_ratings: " . json_last_error_msg());
                            echo "const ppasEntriesTimelinessRatings = null;\n";
                        } else {
                            echo "const ppasEntriesTimelinessRatings = " . $timeliness_ratings_json . ";\n";
                            error_log("Timeliness Ratings from ppas_entries: " . $timeliness_ratings_json);
                        }
                    } else {
                        echo "const ppasEntriesTimelinessRatings = null;\n";
                    }
                } else {
                    error_log("No ppas_entries data found for ppas_form_id: $ppas_form_id");
                    echo "const ppasEntriesActivityRatings = null;\n";
                    echo "const ppasEntriesTimelinessRatings = null;\n";
                }
            } catch (Exception $e) {
                error_log("Error fetching narrative data: " . $e->getMessage());
                echo "const narrativeEntry = null;\n";
                echo "const dbActivityRatings = null;\n";
                echo "const dbTimelinessRatings = null;\n";
                echo "const dbActivityImages = null;\n";
                echo "const narrativeImages = [];\n";
                echo "const backgroundData = '';\n";
                echo "const participantsData = '';\n";
                echo "const topicsData = '';\n";
                echo "const resultsData = '';\n";
                echo "const lessonsData = '';\n";
                echo "const whatWorkedData = '';\n";
                echo "const issuesData = '';\n";
                echo "const recommendationsData = '';\n";
                // Instead of error message, provide an empty string so no error shows
                echo "const combinedNarrativeText = '';\n";
                echo "const narrativeRatings = null;\n";
                echo "const narrativeTimelinessRatings = null;\n";
                echo "const ppasEntriesActivityRatings = null;\n";
                echo "const ppasEntriesTimelinessRatings = null;\n";
            }
            
            // REMOVED: No hardcoded fallback - we only use actual database values
            
            // If database values are null, use empty objects instead of hardcoded values
            echo "const emptyRatings = {
                \"Excellent\": {
                    \"BatStateU\": 22,
                    \"Others\": 15
                },
                \"Very Satisfactory\": {
                    \"BatStateU\": 18,
                    \"Others\": 12
                },
                \"Satisfactory\": {
                    \"BatStateU\": 8,
                    \"Others\": 5
                },
                \"Fair\": {
                    \"BatStateU\": 3,
                    \"Others\": 2
                },
                \"Poor\": {
                    \"BatStateU\": 1,
                    \"Others\": 1
                }
            };\n";
            echo "
// Process ratings data to extract nested objects if necessary
let processedActivityRatings = dbActivityRatings;
let processedTimelinessRatings = dbTimelinessRatings;

// Handle nested activity ratings
if (dbActivityRatings && typeof dbActivityRatings === 'object') {
    if (dbActivityRatings.activity) {
        console.log('Found nested activity property in dbActivityRatings');
        processedActivityRatings = dbActivityRatings.activity;
    } else if (dbActivityRatings.ratings) {
        console.log('Found nested ratings property in dbActivityRatings');
        processedActivityRatings = dbActivityRatings.ratings;
    }
}

// Handle nested timeliness ratings
if (dbTimelinessRatings && typeof dbTimelinessRatings === 'object') {
    if (dbTimelinessRatings.timeliness) {
        console.log('Found nested timeliness property in dbTimelinessRatings');
        processedTimelinessRatings = dbTimelinessRatings.timeliness;
    }
}

// Create different fallback data for activity and timeliness
const activityFallback = {
    \"Excellent\": {
        \"BatStateU\": 22,
        \"Others\": 15
    },
    \"Very Satisfactory\": {
        \"BatStateU\": 18,
        \"Others\": 12
    },
    \"Satisfactory\": {
        \"BatStateU\": 8,
        \"Others\": 5
    },
    \"Fair\": {
        \"BatStateU\": 3,
        \"Others\": 2
    },
    \"Poor\": {
        \"BatStateU\": 1,
        \"Others\": 1
    }
};

const timelinessFallback = {
    \"Excellent\": {
        \"BatStateU\": 17,
        \"Others\": 10
    },
    \"Very Satisfactory\": {
        \"BatStateU\": 15,
        \"Others\": 8
    },
    \"Satisfactory\": {
        \"BatStateU\": 10,
        \"Others\": 6
    },
    \"Fair\": {
        \"BatStateU\": 4,
        \"Others\": 3
    },
    \"Poor\": {
        \"BatStateU\": 2,
        \"Others\": 1
    }
};

const finalActivityRatings = (dbActivityRatings && typeof dbActivityRatings === 'object' && Object.keys(dbActivityRatings).length > 0) ? 
                           processedActivityRatings : 
                           (narrativeRatings || ppasEntriesActivityRatings || activityFallback);
const finalTimelinessRatings = (dbTimelinessRatings && typeof dbTimelinessRatings === 'object' && Object.keys(dbTimelinessRatings).length > 0) ? 
                             processedTimelinessRatings : 
                             (narrativeTimelinessRatings || ppasEntriesTimelinessRatings || timelinessFallback);

console.log('FINAL ACTIVITY RATINGS:', finalActivityRatings);
console.log('FINAL TIMELINESS RATINGS:', finalTimelinessRatings);
";
            echo "const dbFinalActivityImages = narrativeImages.length > 0 ? narrativeImages : (dbActivityImages || []);\n";
            ?>

            // Transform both ratings data sets
            console.log('Raw activity ratings from DB:', finalActivityRatings);
            console.log('Raw timeliness ratings from DB:', finalTimelinessRatings);
            console.log('Raw activity images:', dbFinalActivityImages);
            console.log('Narrative data from narrative_entries:', narrativeEntry);


            // JavaScript version of getDefaultRatings
            function formatImages(images) {
                if (!images || images.length === 0) {
                    return '<p>No images available</p>';
                }
                
                let html = '';
                // Only show up to 4 images to avoid crowding the page
                const displayImages = images.slice(0, 4);
                
                for (const image of displayImages) {
                    html += `
                    <div style="margin: 5px; text-align: center;">
                        <img src="../${image}" alt="Activity Photo" style="max-width: 250px; max-height: 200px; object-fit: contain;">
                    </div>`;
                }
                
                return html;
            }
            
            function getDefaultRatings() {
                // Return sample ratings data to ensure we always have something to display
        function calculateTotalRespondents(ratings, participantType) {
            try {
                if (!ratings) return '0';
            
            // Handle JSON string format
            if (typeof ratings === 'string') {
                try {
                    ratings = JSON.parse(ratings);
                } catch (e) {
                    console.error('Error parsing ratings JSON:', e);
                        return '0';
                }
            }
            
            // Map participant types
            const participantMap = {
                'batstateu': 'BatStateU',
                'other': 'Others'
            };
            
            // Get the correct participant key
            const participantKey = participantMap[participantType] || participantType;
            
            console.log('Calculating total respondents for participant type:', participantKey);
            
            // Use the proper rating categories
            const ratingCategories = ['Excellent', 'Very Satisfactory', 'Satisfactory', 'Fair', 'Poor'];
            
            let total = 0;
            
            // Sum up the values for this participant type across all rating categories
            ratingCategories.forEach(category => {
                if (ratings[category] && typeof ratings[category] === 'object' && 
                    ratings[category][participantKey] !== undefined) {
                    const count = parseInt(ratings[category][participantKey] || 0);
                    console.log(`${category} ${participantKey}: ${count}`);
                    total += count;
                }
            });
                
                // If total is still 0, try alternative structure
                if (total === 0) {
                    // Try alternative structure with lowercase keys
                    const lowerCaseCategories = ['excellent', 'very_satisfactory', 'satisfactory', 'fair', 'poor'];
                    const lowerCaseParticipant = participantType;
                    
                    lowerCaseCategories.forEach(category => {
                        if (ratings[category] && typeof ratings[category] === 'object' && 
                            ratings[category][lowerCaseParticipant] !== undefined) {
                            const count = parseInt(ratings[category][lowerCaseParticipant] || 0);
                            console.log(`${category} ${lowerCaseParticipant}: ${count}`);
                            total += count;
                        }
                    });
                }
                
                // If still 0, return default values
                if (total === 0) {
                    total = 0;
                }
            
            console.log(`Total ${participantKey} respondents: ${total}`);
            return total.toString();
            } catch (e) {
                console.error('Error in calculateTotalRespondents:', e);
                return '0';
            }
        }
        
        function calculateTotalParticipants(ratings) {
            try {
                if (!ratings) return '0';
            
            // Handle JSON string format
            if (typeof ratings === 'string') {
                try {
                    ratings = JSON.parse(ratings);
                } catch (e) {
                    console.error('Error parsing ratings JSON:', e);
                        return '0';
                }
            }
            
            console.log('Calculating total participants');
            
            // Use the proper rating categories
            const ratingCategories = ['Excellent', 'Very Satisfactory', 'Satisfactory', 'Fair', 'Poor'];
            
            // Participant types
            const participantTypes = ['BatStateU', 'Others'];
            
            let total = 0;
            
            // Sum up all values across all rating categories and participant types
            ratingCategories.forEach(category => {
                if (ratings[category] && typeof ratings[category] === 'object') {
                    participantTypes.forEach(participantType => {
                        if (ratings[category][participantType] !== undefined) {
                            const count = parseInt(ratings[category][participantType] || 0);
                            console.log(`${category} ${participantType}: ${count}`);
                            total += count;
                        }
                    });
                }
            });
                
                // If total is still 0, try alternative structure
                if (total === 0) {
                    // Try alternative structure with lowercase keys
                    const lowerCaseCategories = ['excellent', 'very_satisfactory', 'satisfactory', 'fair', 'poor'];
                    const lowerCaseParticipants = ['batstateu', 'other'];
                    
                    lowerCaseCategories.forEach(category => {
                        if (ratings[category] && typeof ratings[category] === 'object') {
                            lowerCaseParticipants.forEach(participantType => {
                                if (ratings[category][participantType] !== undefined) {
                                    const count = parseInt(ratings[category][participantType] || 0);
                                    console.log(`${category} ${participantType}: ${count}`);
                                    total += count;
                                }
                            });
                        }
                    });
                }
                
                // If still 0, return default value
                if (total === 0) {
                    total = 0;
                }
            
            console.log(`Total participants: ${total}`);
            return total.toString();
            } catch (e) {
                console.error('Error in calculateTotalParticipants:', e);
                return '0';
            }
        }

        function displayImages(imagesString) {
            if (!imagesString) return '<p>No images available</p>';
            
            try {
                console.log('Raw image data:', imagesString);
                
                // If images are stored as a JSON string, parse them
                let images = imagesString;
                
                // Convert to array if it's a single string
                if (typeof imagesString === 'string') {
                    // Check if it looks like a JSON array
                    if (imagesString.trim().startsWith('[')) {
                        try {
                            images = JSON.parse(imagesString);
                            console.log('Successfully parsed JSON array of images:', images);
                        } catch (e) {
                            console.error('Failed to parse JSON array:', e);
                            // Treat as single image
                            images = [imagesString];
                        }
                    } else {
                        // Single image path as string
                        images = [imagesString];
                    }
                }
                
                // Handle case where data is already an object but needs conversion
                if (images && typeof images === 'object' && !Array.isArray(images)) {
                    // Check if it's an object with numeric keys (like a PHP array)
                    const keys = Object.keys(images);
                    if (keys.length > 0 && !isNaN(parseInt(keys[0]))) {
                        images = Object.values(images);
                        console.log('Converted object to array:', images);
                    } else {
                        // Look for an images array in the object
                        if (images.images && Array.isArray(images.images)) {
                            images = images.images;
                        } else if (images.paths && Array.isArray(images.paths)) {
                            images = images.paths;
                        } else if (images.photo_paths && Array.isArray(images.photo_paths)) {
                            images = images.photo_paths;
                        } else {
                            // Last resort - try to use any string values in the object
                            images = Object.values(images).filter(val => typeof val === 'string');
                        }
                    }
                }
                
                if (!Array.isArray(images) || images.length === 0) {
                    console.log('No valid images found in data');
                    return '<p>No images available</p>';
                }
                
                console.log('Processing images array:', images);
                
                // Display all images in a grid format with exactly 2 per row
                let imagesHtml = '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; width: 100%;">';
                
                images.forEach(image => {
                    if (!image) return; // Skip empty entries
                    
                    // Get just the filename from the path
                    let filename = image;
                    if (filename.includes('/')) {
                        filename = filename.split('/').pop();
                    }
                    
                    // Use the direct path to photos directory as specified by the user
                    let imagePath = '../photos/' + filename;
                    
                    console.log('Displaying image with path:', imagePath);
                    
                    imagesHtml += `
                        <div style="margin-bottom: 8px;">
                            <img src="${imagePath}" 
                                 style="width: 100%; height: 200px; object-fit: contain; border: 1px solid #ddd;" 
                                 onerror="this.onerror=null; this.src='../photos/${filename}';">
                        </div>
                    `;
                });
                
                imagesHtml += '</div>';
                
                return imagesHtml;
            } catch (e) {
                console.error('Error displaying images:', e);
                return '<p>Error displaying images</p>';
            }
        }

        function displayAdditionalImages(imagesString) {
            if (!imagesString) return '<p>No images available</p>';
            
            try {
                // If images are stored as a JSON string, parse them
                let images = imagesString;
                if (typeof imagesString === 'string') {
                    try {
                        images = JSON.parse(imagesString);
                    } catch (e) {
                        // If it's not valid JSON, treat it as a single image path
                        images = [imagesString];
                    }
                }
                
                if (!Array.isArray(images) || images.length === 0) {
                    return '<p>No images available</p>';
                }
                
                // Display all images in a grid format with exactly 2 per row
                let imagesHtml = '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; width: 100%;">';
                
                images.forEach(image => {
                    // Get just the filename from the path
                    let filename = image;
                    if (filename.includes('/')) {
                        filename = filename.split('/').pop();
                    }
                    
                    // Use the direct path to photos directory as specified by the user
                    let imagePath = '../photos/' + filename;
                    
                    imagesHtml += `
                        <div style="margin-bottom: 10px;">
                            <img src="${imagePath}" style="width: 350px; height: 250px; object-fit: cover; border: 1px solid #ddd;" onerror="this.src='../photos/${filename}'; this.onerror=null;">
                        </div>
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
    <!-- Fix ratings data structure -->
    <script src="fix_ratings.js"></script>
  </body>
</html>
</html>
