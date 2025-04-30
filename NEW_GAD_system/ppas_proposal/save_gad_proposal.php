<?php
// Enable error reporting for debugging
ini_set('display_errors', 0); // Turn off direct output of errors
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Make sure no prior output exists
if (ob_get_length()) ob_clean();

// Buffer all output to ensure clean JSON response
ob_start();

// Create a debug log file
$debug_log = fopen(__DIR__ . '/form_submission_debug.log', 'a');
function debug_log($message) {
    global $debug_log;
    fwrite($debug_log, '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n");
}

debug_log("---- NEW FORM SUBMISSION ----");

// Start session
session_start();

// Set proper content type before any output
header('Content-Type: application/json');

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    global $debug_log;
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Clear any previous output
        if (ob_get_length()) ob_clean();
        
        debug_log("FATAL ERROR: " . print_r($error, true));
        echo json_encode([
            'success' => false,
            'message' => 'A server error occurred: ' . $error['message']
        ]);
        exit();
    }
    
    // If we have any unexpected output buffered, clear it and return a clean JSON response
    $output = ob_get_clean();
    if (!empty($output)) {
        debug_log("Unexpected output detected: " . $output);
        echo json_encode([
            'success' => false,
            'message' => 'Server returned unexpected output. Please try again.'
        ]);
    }
    
    // Close debug log if still open
    if (isset($debug_log) && is_resource($debug_log)) {
        fclose($debug_log);
    }
});

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    debug_log("Error: User not logged in");
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

debug_log("User: " . $_SESSION['username']);

// Include database connection - update to correct path
require_once '../config.php';

// Check if database connection is working
if ($conn->connect_error) {
    debug_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

debug_log("Database connection successful");

// Function to sanitize input data
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Get JSON data from request
$json_data = file_get_contents('php://input');
debug_log("Raw input: " . $json_data);

$data = json_decode($json_data, true);

// Check if data is valid
if (!$data) {
    debug_log("Error: Invalid JSON format - " . json_last_error_msg());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data format: ' . json_last_error_msg()]);
    exit();
}

debug_log("JSON decoded successfully. Keys present: " . implode(", ", array_keys($data)));

// Required fields based on actual table structure
$required_fields = [
    'mode_of_delivery', 'partner_office', 'rationale', 'general_objectives',
    'description', 'budget_breakdown', 'sustainability_plan'
];

// Check required fields
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        debug_log("Missing required field: $field");
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

debug_log("All required fields present");

try {
    // Begin transaction
    $conn->begin_transaction();
    debug_log("Transaction started");
    
    // Prepare arrays for JSON fields
    $project_leader_responsibilities = isset($data['project_leader_responsibilities']) ? $data['project_leader_responsibilities'] : [];
    $assistant_leader_responsibilities = isset($data['assistant_leader_responsibilities']) ? $data['assistant_leader_responsibilities'] : [];
    $staff_responsibilities = isset($data['staff_responsibilities']) ? $data['staff_responsibilities'] : [];
    $specific_objectives = isset($data['specific_objectives']) ? $data['specific_objectives'] : [];
    $strategies = isset($data['strategies']) ? $data['strategies'] : [];
    $materials = isset($data['materials']) ? $data['materials'] : [];
    $specific_plans = isset($data['specific_plans']) ? $data['specific_plans'] : [];
    
    // Process methods data
    $methods = [];
    if (isset($data['methods']) && is_array($data['methods'])) {
        foreach ($data['methods'] as $method) {
            // Check if method is in the new array format [name, details]
            if (is_array($method) && isset($method[0])) {
                $methodName = $method[0];
                $methodDetails = $method[1] ?? [];
                $methods[] = [
                    sanitize($methodName),
                    array_map('sanitize', $methodDetails)
                ];
            } 
            // Fallback for old format { name, details }
            else if (isset($method['name'])) {
                $methodDetails = isset($method['details']) ? $method['details'] : [];
                $methods[] = [
                    sanitize($method['name']),
                    array_map('sanitize', $methodDetails)
                ];
            }
        }
    }
    debug_log("Methods processed: " . count($methods));
    
    // Process workplan data
    $workplan = [];
    if (isset($data['workplan']) && is_array($data['workplan'])) {
        foreach ($data['workplan'] as $item) {
            // Check if workplan is in the new array format [activity, dates]
            if (is_array($item) && isset($item[0])) {
                $workplan[] = [
                    $item[0],
                    $item[1]
                ];
            }
            // Fallback for old format { activity, dates/days }
            else if (isset($item['activity'])) {
                $days = isset($item['days']) ? $item['days'] : [];
                $checkedDays = [];
                
                // If dates are already provided, use them directly
                if (isset($item['dates']) && is_array($item['dates'])) {
                    $checkedDays = $item['dates'];
                }
                // Otherwise process the days array
                else {
                    // Validate start and end date
                    if (!isset($data['start_date']) || empty($data['start_date'])) {
                        debug_log("Error: Missing start_date for workplan");
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => "Missing start_date for workplan"]);
                        exit();
                    }
                    
                    if (!isset($data['end_date']) || empty($data['end_date'])) {
                        debug_log("Error: Missing end_date for workplan");
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => "Missing end_date for workplan"]);
                        exit();
                    }
                    
                    // Get start and end date to determine date range
                    try {
                        $start_date = new DateTime($data['start_date']);
                        $end_date = new DateTime($data['end_date']);
                        $interval = $start_date->diff($end_date);
                        $dayCount = $interval->days + 1; // Include both start and end dates
                        
                        debug_log("Date range: {$data['start_date']} to {$data['end_date']} ($dayCount days)");
                        
                        // Process each day in the range
                        for ($i = 0; $i < $dayCount; $i++) {
                            $currentDate = clone $start_date;
                            $currentDate->modify("+$i days");
                            
                            // Check if this day is checked in the form
                            if (isset($days[$i]) && $days[$i] == '1') {
                                $checkedDays[] = $currentDate->format('Y-m-d');
                            }
                        }
                    } catch (Exception $e) {
                        debug_log("Error processing dates: " . $e->getMessage());
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => "Invalid date format: " . $e->getMessage()]);
                        exit();
                    }
                }
                
                $workplan[] = [
                    sanitize($item['activity']),
                    $checkedDays
                ];
            }
        }
    }
    debug_log("Workplan processed: " . count($workplan));
    
    // Process monitoring data
    $monitoring_items = [];
    if (isset($data['monitoring']) && is_array($data['monitoring'])) {
        foreach ($data['monitoring'] as $item) {
            // Check if monitoring data is in the new array format
            if (is_array($item) && !isset($item['objectives'])) {
                // New format: [Objectives, Performance Indicators, Baseline Data, Performance Target, Data Source, Collection Method, Frequency, Office/Person]
                $monitoring_items[] = [
                    sanitize($item[0] ?? ''),
                    sanitize($item[1] ?? ''),
                    sanitize($item[2] ?? ''),
                    sanitize($item[3] ?? ''),
                    sanitize($item[4] ?? ''),
                    sanitize($item[5] ?? ''),
                    sanitize($item[6] ?? ''),
                    sanitize($item[7] ?? '')
                ];
            }
            // Fallback for old format with associative keys
            else if (isset($item['objectives']) && isset($item['performance_indicators'])) {
                $monitoring_items[] = [
                    sanitize($item['objectives']),
                    sanitize($item['performance_indicators']),
                    sanitize($item['baseline_data'] ?? ''),
                    sanitize($item['performance_target'] ?? ''),
                    sanitize($item['data_source'] ?? ''),
                    sanitize($item['collection_method'] ?? ''),
                    sanitize($item['frequency'] ?? ''),
                    sanitize($item['responsible'] ?? '')
                ];
            }
        }
    }
    debug_log("Monitoring items processed: " . count($monitoring_items));
    
    // Get the ppas_form_id from the ppas_forms table using activity_title
    $ppas_form_id = null;
    if (isset($data['activity_title']) && !empty($data['activity_title'])) {
        $activity_title = $data['activity_title'];
        debug_log("Looking up ppas_form_id for activity: " . $activity_title);
        
        // Query to get the ppas_form_id - updated field name to match your database structure
        $query = "SELECT id FROM ppas_forms WHERE activity = ?";
        $stmt_query = $conn->prepare($query);
        $stmt_query->bind_param("s", $activity_title);
        $stmt_query->execute();
        $result = $stmt_query->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $ppas_form_id = $row['id'];
            debug_log("Found ppas_form_id: " . $ppas_form_id);
        } else {
            // If not found, log the error but continue with a default value or null
            debug_log("Activity title not found in ppas_forms table: $activity_title");
            // Try to use the activity ID directly if it's numeric
            if (is_numeric($activity_title)) {
                $ppas_form_id = intval($activity_title);
                debug_log("Using numeric value as ppas_form_id: " . $ppas_form_id);
            } else {
                debug_log("No ppas_form_id available, will attempt to insert with NULL");
            }
        }
        
        $stmt_query->close();
    } else {
        debug_log("Warning: No activity_title provided");
    }
    
    // Log the data we're about to insert for debugging
    debug_log("Attempting to insert with ppas_form_id: " . ($ppas_form_id ? $ppas_form_id : 'NULL'));
    
    // Insert proposal into database
    $sql = "INSERT INTO gad_proposals (
                ppas_form_id, 
                campus,
                mode_of_delivery, 
                partner_office, 
                rationale, 
                general_objectives, 
                description, 
                budget_breakdown, 
                sustainability_plan, 
                project_leader_responsibilities, 
                assistant_leader_responsibilities, 
                staff_responsibilities, 
                specific_objectives, 
                strategies, 
                methods, 
                materials, 
                workplan, 
                monitoring_items, 
                specific_plans
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        debug_log("Prepare statement failed: " . $conn->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Prepare statement failed: ' . $conn->error]);
        exit();
    }
    
    // Get current user's campus from session
    $campus = $_SESSION['username'];
    debug_log("User campus: " . $campus);
    
    // Convert arrays to JSON
    $project_leader_responsibilities_json = json_encode($project_leader_responsibilities);
    $assistant_leader_responsibilities_json = json_encode($assistant_leader_responsibilities);
    $staff_responsibilities_json = json_encode($staff_responsibilities);
    $specific_objectives_json = json_encode($specific_objectives);
    $strategies_json = json_encode($strategies);
    $methods_json = json_encode($methods);
    $materials_json = json_encode($materials);
    $workplan_json = json_encode($workplan);
    $monitoring_items_json = json_encode($monitoring_items);
    $specific_plans_json = json_encode($specific_plans);
    
    // Debug JSON encoding
    if (json_last_error() !== JSON_ERROR_NONE) {
        debug_log("JSON encoding error: " . json_last_error_msg());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'JSON encoding error: ' . json_last_error_msg()]);
        exit();
    }
    
    debug_log("All JSON fields encoded successfully");
    
    $bind_result = $stmt->bind_param(
        "issssssssssssssssss",
        $ppas_form_id, // Using the fetched ID from ppas_forms table
        $campus,
        $data['mode_of_delivery'],
        $data['partner_office'],
        $data['rationale'],
        $data['general_objectives'],
        $data['description'],
        $data['budget_breakdown'],
        $data['sustainability_plan'],
        $project_leader_responsibilities_json,
        $assistant_leader_responsibilities_json,
        $staff_responsibilities_json,
        $specific_objectives_json,
        $strategies_json,
        $methods_json,
        $materials_json,
        $workplan_json,
        $monitoring_items_json,
        $specific_plans_json
    );
    
    if (!$bind_result) {
        debug_log("Bind param failed: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Bind param failed: ' . $stmt->error]);
        exit();
    }
    
    debug_log("Parameters bound successfully");
    
    $execute_result = $stmt->execute();
    if (!$execute_result) {
        debug_log("Execute failed: " . $stmt->error);
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
        exit();
    }
    
    debug_log("Statement executed successfully");
    
    // Check if insertion was successful
    if ($stmt->affected_rows > 0) {
        // Commit transaction
        $conn->commit();
        debug_log("Transaction committed successfully");
        
        echo json_encode(['success' => true, 'message' => 'GAD Proposal saved successfully']);
    } else {
        // Rollback on failure
        $conn->rollback();
        debug_log("No rows affected. Rollback performed. Error: " . $stmt->error);
        
        echo json_encode(['success' => false, 'message' => 'Failed to save GAD Proposal: ' . $stmt->error]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    // Rollback on exception
    if (isset($conn)) {
        $conn->rollback();
    }
    
    // Clear any buffered output
    if (ob_get_length()) ob_clean();
    
    debug_log("Exception: " . $e->getMessage());
    debug_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage(),
        'error_details' => $e->getTraceAsString()
    ]);
}

// Close connection
if (isset($conn)) {
    $conn->close();
}

debug_log("---- END OF FORM SUBMISSION ----\n");

// End output buffering if still active and not ended by the shutdown function
if (ob_get_level() > 0) {
    ob_end_flush();
}

// Close debug log
if (isset($debug_log) && is_resource($debug_log)) {
    fclose($debug_log);
}
?> 