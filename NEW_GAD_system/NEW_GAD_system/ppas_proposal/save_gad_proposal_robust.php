<?php
// Increase memory limit if needed
ini_set('memory_limit', '256M');

// Increase execution time limit
ini_set('max_execution_time', 120);

// Disable all error display
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Make sure no prior output exists
if (ob_get_length()) ob_clean();

// Start output buffering
ob_start();

// Set JSON content type header first, before any possible output
header('Content-Type: application/json');

// Create a debug log file
$debug_log = fopen(__DIR__ . '/robust_form_submission.log', 'a');
function debug_log($message) {
    global $debug_log;
    fwrite($debug_log, '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n");
}

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    global $debug_log;
    
    // Get last error
    $error = error_get_last();
    
    // Handle fatal errors
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Clear any previous output
        if (ob_get_length()) ob_clean();
        
        debug_log("FATAL ERROR: " . print_r($error, true));
        
        // Send a clean JSON response
        echo json_encode([
            'success' => false,
            'message' => 'A server error occurred: ' . $error['message']
        ]);
    } else {
        // If there's unexpected output in the buffer, log it and return clean JSON
        $output = ob_get_contents();
        if ($output && strpos($output, '{') !== 0) { // Not starting with {, so not JSON
            debug_log("Unexpected output in buffer: " . substr($output, 0, 1000));
            
            // Clean buffer and send proper JSON
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Server returned unexpected output. Please try again.'
            ]);
        }
    }
    
    // End output buffering
    ob_end_flush();
    
    // Close debug log
    if (isset($debug_log) && is_resource($debug_log)) {
        fclose($debug_log);
    }
});

debug_log("---- NEW FORM SUBMISSION (ROBUST) ----");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    debug_log("Error: User not logged in");
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

debug_log("User: " . $_SESSION['username']);

// Include database connection
try {
    require_once '../config.php';
} catch (Exception $e) {
    debug_log("Failed to include config file: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database configuration error']);
    exit();
}

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    debug_log("Database connection failed: " . ($conn->connect_error ?? 'Connection variable not set'));
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

debug_log("Database connection successful");

// Function to sanitize input data
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    
    // Convert data to string if it's not already
    $data = (string)$data;
    
    // Remove any null bytes which can be problematic
    $data = str_replace("\0", '', $data);
    
    // Trim whitespace but preserve hashtags and other special characters
    $data = trim($data);
    
    // Rather than using strip_tags and htmlspecialchars which might affect hashtags,
    // we'll just escape specific dangerous characters for database safety
    $data = str_replace(
        ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
        ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
        $data
    );
    
    return $data;
}

// Get JSON data from request
$json_data = file_get_contents('php://input');
debug_log("Received raw input of length: " . strlen($json_data));

// Check if input is empty
if (empty($json_data)) {
    debug_log("Error: Empty input data");
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit();
}

// Try to decode the JSON data
try {
    // Log the first 1000 characters of the input data for debugging
    debug_log("JSON input preview: " . substr($json_data, 0, 1000));
    
    // Try to decode with options to handle potential issues
    $data = json_decode($json_data, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
    
    // Check if data is valid
    if (!$data) {
        $json_error = json_last_error_msg();
        debug_log("Error: Invalid JSON format - " . $json_error);
        debug_log("JSON decode error code: " . json_last_error());
        
        // Try again with different flags
        $data = json_decode($json_data, true, 512, JSON_INVALID_UTF8_IGNORE);
        
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Invalid data format: ' . $json_error]);
            exit();
        } else {
            debug_log("JSON decoded on second attempt with different flags");
        }
    }
    
    debug_log("JSON decoded successfully. Keys present: " . implode(", ", array_keys($data)));
    
    // Recursively sanitize all data
    $data = sanitize($data);
    debug_log("All data sanitized");
    
} catch (Exception $e) {
    debug_log("Exception during JSON decode: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error processing input data: ' . $e->getMessage()]);
    exit();
}

// Required fields
$required_fields = [
    'mode_of_delivery', 'partner_office', 'rationale', 'general_objectives',
    'description', 'budget_breakdown', 'sustainability_plan'
];

// Check required fields
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        debug_log("Missing required field: $field");
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

// Continue with processing the data and saving to database
try {
    // Begin transaction
    $conn->begin_transaction();
    debug_log("Transaction started");
    
    // Process and prepare all the data fields exactly as in the original file
    // ...
    
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
        debug_log("Processing methods array with " . count($data['methods']) . " items");
        foreach ($data['methods'] as $method) {
            // Check if method is in the new array format [name, details]
            if (is_array($method) && isset($method[0])) {
                $methodName = $method[0];
                $methodDetails = isset($method[1]) && is_array($method[1]) ? $method[1] : [];
                $methods[] = [
                    $methodName,
                    array_map('sanitize', $methodDetails)
                ];
            } 
            // Fallback for old format { name, details }
            else if (isset($method['name'])) {
                $methodDetails = isset($method['details']) && is_array($method['details']) ? $method['details'] : [];
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
        debug_log("Processing workplan array with " . count($data['workplan']) . " items");
        foreach ($data['workplan'] as $item) {
            // Check if workplan is in the new array format [activity, dates]
            if (is_array($item) && isset($item[0])) {
                $workplan[] = [
                    $item[0],
                    isset($item[1]) && is_array($item[1]) ? $item[1] : []
                ];
            }
            // Fallback for old format { activity, dates/days }
            else if (isset($item['activity'])) {
                $days = isset($item['days']) && is_array($item['days']) ? $item['days'] : [];
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
                        echo json_encode(['success' => false, 'message' => "Missing start_date for workplan"]);
                        exit();
                    }
                    
                    if (!isset($data['end_date']) || empty($data['end_date'])) {
                        debug_log("Error: Missing end_date for workplan");
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
        debug_log("Processing monitoring array with " . count($data['monitoring']) . " items");
        foreach ($data['monitoring'] as $item) {
            // Check if monitoring data is in the new array format
            if (is_array($item) && !isset($item['objectives'])) {
                // New format: [Objectives, Performance Indicators, Baseline Data, Performance Target, Data Source, Collection Method, Frequency, Office/Person]
                $monitoring_items[] = [
                    $item[0] ?? '',
                    $item[1] ?? '',
                    $item[2] ?? '',
                    $item[3] ?? '',
                    $item[4] ?? '',
                    $item[5] ?? '',
                    $item[6] ?? '',
                    $item[7] ?? ''
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
        
        // If the activity_title is numeric, use it directly
        if (is_numeric($activity_title)) {
            $ppas_form_id = intval($activity_title);
            debug_log("Using numeric activity_title as ppas_form_id: " . $ppas_form_id);
        } else {
            // Try to look up by name
            $query = "SELECT id FROM ppas_forms WHERE activity = ?";
            $stmt_query = $conn->prepare($query);
            if (!$stmt_query) {
                debug_log("Prepare statement failed for ppas_forms lookup: " . $conn->error);
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
                exit();
            }
            
            $stmt_query->bind_param("s", $activity_title);
            $stmt_query->execute();
            $result = $stmt_query->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $ppas_form_id = $row['id'];
                debug_log("Found ppas_form_id: " . $ppas_form_id);
            } else {
                debug_log("Activity title not found in ppas_forms table: $activity_title");
            }
            
            $stmt_query->close();
        }
    } else {
        debug_log("No activity_title provided");
    }
    
    // Convert arrays to JSON with error checking
    try {
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
        
        // Check for JSON encoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON encoding error: " . json_last_error_msg());
        }
    } catch (Exception $e) {
        debug_log("JSON encoding error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'JSON encoding error: ' . $e->getMessage()]);
        exit();
    }
    
    // Get current user's campus from session
    $campus = $_SESSION['username'];
    debug_log("User campus: " . $campus);
    
    // Insert the data into the database
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
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit();
    }
    
    // Bind parameters with explicit variable references to avoid PHP reference issues
    $bind_result = $stmt->bind_param(
        "issssssssssssssssss",
        $ppas_form_id,
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
        echo json_encode(['success' => false, 'message' => 'Database binding error: ' . $stmt->error]);
        exit();
    }
    
    $execute_result = $stmt->execute();
    if (!$execute_result) {
        debug_log("Execute failed: " . $stmt->error);
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Database execution error: ' . $stmt->error]);
        exit();
    }
    
    // Check if insertion was successful
    if ($stmt->affected_rows > 0) {
        // Commit transaction
        $conn->commit();
        debug_log("Transaction committed successfully");
        
        // Return success JSON
        echo json_encode(['success' => true, 'message' => 'GAD Proposal saved successfully']);
    } else {
        // Rollback on failure
        $conn->rollback();
        debug_log("No rows affected. Rollback performed.");
        
        echo json_encode(['success' => false, 'message' => 'No data was saved. Please try again.']);
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
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
    
    debug_log("---- END OF FORM SUBMISSION (ROBUST) ----\n");
    
    // Make sure we flush the output buffer
    if (ob_get_level() > 0 && ob_get_length() > 0) {
        ob_end_flush();
    }
}
?> 