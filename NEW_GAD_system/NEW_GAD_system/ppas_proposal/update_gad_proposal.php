<?php
// Force no HTML errors - MUST be at the very top
ini_set('display_errors', 0);
ini_set('html_errors', 0);
error_reporting(E_ALL);

// Set content type to JSON - MUST be at the very top too
header('Content-Type: application/json');

// Start output buffering to capture all output
ob_start();

// Register shutdown function to catch fatal errors and handle output
register_shutdown_function(function() {
    $error = error_get_last();
    
    // Get the current output buffer content
    $output = ob_get_clean();
    
    // Check if we have a fatal error
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log("FATAL PHP ERROR: " . print_r($error, true));
        echo json_encode([
            'success' => false,
            'message' => 'A server error occurred',
            'error' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
        return;
    }
    
    // Check if the output is valid JSON
    // Trim whitespace first
    $trimmed_output = trim($output);
    
    // If empty, return generic error
    if (empty($trimmed_output)) {
        echo json_encode([
            'success' => false,
            'message' => 'Empty response from server'
        ]);
        return;
    }
    
    // Check if it starts with { or [ (likely JSON)
    if (($trimmed_output[0] === '{' || $trimmed_output[0] === '[') && 
        json_decode($trimmed_output) !== null) {
        // It's valid JSON, just echo it as is
        echo $trimmed_output;
    } else {
        // Not valid JSON, log it and return error
        $debug_output = substr($trimmed_output, 0, 1000); // Limit to 1000 chars for safety
        error_log("UPDATE GAD PROPOSAL: Unexpected output detected: " . $debug_output);
        
        echo json_encode([
            'success' => false,
            'message' => 'Server returned unexpected output',
            'debug' => $debug_output
        ]);
    }
});

// Now start session
session_start();
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_errors.log');

// Log the request
error_log("UPDATE GAD PROPOSAL: Request started");
error_log("SESSION: " . print_r($_SESSION, true));
error_log("POST data: " . print_r($_POST, true));

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("UPDATE GAD PROPOSAL: User not logged in");
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("UPDATE GAD PROPOSAL: Invalid request method");
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check if ID is provided
if (!isset($_POST['proposal_id']) || empty($_POST['proposal_id'])) {
    error_log("UPDATE GAD PROPOSAL: Missing proposal ID");
    echo json_encode(['success' => false, 'message' => 'Proposal ID is required']);
    exit();
}

try {
    // Include database configuration
    require_once '../config.php';
    
    $proposalId = $_POST['proposal_id'];
    $userCampus = $_SESSION['username'];
    $isCentral = ($userCampus === 'Central');
    
    error_log("UPDATE GAD PROPOSAL: Updating proposal ID: $proposalId by user: $userCampus");
    
    // First, verify the table structure to get the correct ID field name
    $tableInfo = $conn->query("SHOW COLUMNS FROM gad_proposals");
    if (!$tableInfo) {
        throw new Exception("Error fetching table columns: " . $conn->error);
    }
    
    $columns = [];
    $idField = 'id'; // Default ID field name
    
    while ($column = $tableInfo->fetch_assoc()) {
        $columns[] = $column['Field'];
        // Look for the primary key or a field containing 'id' in its name
        if ($column['Key'] === 'PRI' || strtolower($column['Field']) === 'proposal_id' || 
            strtolower($column['Field']) === 'id') {
            $idField = $column['Field'];
        }
    }
    
    error_log("UPDATE GAD PROPOSAL: Using ID field: $idField");
    
    // First check if the user has permission to update this proposal
    $checkSql = "SELECT * FROM gad_proposals WHERE $idField = ?";
    
    $stmt = $conn->prepare($checkSql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param('i', $proposalId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("UPDATE GAD PROPOSAL: Proposal not found with ID: $proposalId");
        echo json_encode(['success' => false, 'message' => 'Proposal not found']);
        $stmt->close();
        exit();
    }
    
    $proposal = $result->fetch_assoc();
    $proposalCampus = $proposal['campus'];
    
    // Check if user has permission (their campus matches the proposal's campus)
    if (!$isCentral && $userCampus !== $proposalCampus) {
        error_log("UPDATE GAD PROPOSAL: Permission denied for user $userCampus to update proposal from campus $proposalCampus");
        echo json_encode(['success' => false, 'message' => 'You do not have permission to update this proposal']);
        $stmt->close();
        exit();
    }
    
    // Close the check statement
    $stmt->close();
    
    // Get the POST data
    $jsonInput = file_get_contents('php://input');
    $data = json_decode($jsonInput, true);

    // Fallback to regular POST if JSON decoding fails
    if ($data === null) {
        $data = $_POST;
    }

    // Log received data for debugging
    error_log("Update GAD Proposal - Received data: " . print_r($data, true));

    // Special debugging for responsibility fields
    error_log("DEBUG RESPONSIBILITIES - Raw assistant_leader_responsibilities: " . (isset($_POST['assistant_leader_responsibilities']) ? $_POST['assistant_leader_responsibilities'] : 'NOT SET IN POST'));
    error_log("DEBUG RESPONSIBILITIES - Raw staff_responsibilities: " . (isset($_POST['staff_responsibilities']) ? $_POST['staff_responsibilities'] : 'NOT SET IN POST'));
    error_log("DEBUG RESPONSIBILITIES - Raw project_leader_responsibilities: " . (isset($_POST['project_leader_responsibilities']) ? $_POST['project_leader_responsibilities'] : 'NOT SET IN POST'));

    // Process responsibility data directly from POST if it exists
    if (isset($_POST['assistant_leader_responsibilities'])) {
        try {
            $data['assistant_leader_responsibilities'] = json_decode($_POST['assistant_leader_responsibilities'], true);
            error_log("Decoded assistant_leader_responsibilities: " . print_r($data['assistant_leader_responsibilities'], true));
        } catch (Exception $e) {
            error_log("Error decoding assistant_leader_responsibilities: " . $e->getMessage());
        }
    }
    
    if (isset($_POST['staff_responsibilities'])) {
        try {
            $data['staff_responsibilities'] = json_decode($_POST['staff_responsibilities'], true);
            error_log("Decoded staff_responsibilities: " . print_r($data['staff_responsibilities'], true));
        } catch (Exception $e) {
            error_log("Error decoding staff_responsibilities: " . $e->getMessage());
        }
    }
    
    if (isset($_POST['project_leader_responsibilities'])) {
        try {
            $data['project_leader_responsibilities'] = json_decode($_POST['project_leader_responsibilities'], true);
            error_log("Decoded project_leader_responsibilities: " . print_r($data['project_leader_responsibilities'], true));
        } catch (Exception $e) {
            error_log("Error decoding project_leader_responsibilities: " . $e->getMessage());
        }
    }

    // Check if we have the responsibility fields in the decoded data
    if (isset($data['assistant_leader_responsibilities'])) {
        error_log("DEBUG RESPONSIBILITIES - assistant_leader_responsibilities in decoded data: " . print_r($data['assistant_leader_responsibilities'], true));
    } else {
        error_log("DEBUG RESPONSIBILITIES - assistant_leader_responsibilities NOT in decoded data");
    }
    
    if (isset($data['staff_responsibilities'])) {
        error_log("DEBUG RESPONSIBILITIES - staff_responsibilities in decoded data: " . print_r($data['staff_responsibilities'], true));
    } else {
        error_log("DEBUG RESPONSIBILITIES - staff_responsibilities NOT in decoded data");
    }
    
    if (isset($data['project_leader_responsibilities'])) {
        error_log("DEBUG RESPONSIBILITIES - project_leader_responsibilities in decoded data: " . print_r($data['project_leader_responsibilities'], true));
    } else {
        error_log("DEBUG RESPONSIBILITIES - project_leader_responsibilities NOT in decoded data");
    }

    // Special processing for activity_title to get ppas_form_id
    if (isset($data['activity_title']) && !empty($data['activity_title'])) {
        $activity_title = $data['activity_title'];
        error_log("Looking up ppas_form_id for activity: " . $activity_title);
        
        // If the activity_title is numeric, use it directly as ppas_form_id
        if (is_numeric($activity_title)) {
            $data['ppas_form_id'] = intval($activity_title);
            error_log("Using numeric activity_title as ppas_form_id: " . $data['ppas_form_id']);
        } else {
            // Try to look up by name
            $query = "SELECT id FROM ppas_forms WHERE activity = ?";
            $stmt_query = $conn->prepare($query);
            if ($stmt_query) {
                $stmt_query->bind_param("s", $activity_title);
                $stmt_query->execute();
                $result = $stmt_query->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    $data['ppas_form_id'] = $row['id'];
                    error_log("Found ppas_form_id: " . $data['ppas_form_id']);
                } else {
                    error_log("Activity title not found in ppas_forms table: $activity_title");
                }
                
                $stmt_query->close();
            }
        }
    }

    // Define field mappings (POST field name => DB field name)
    // These must exactly match the fields used in save_gad_proposal_robust.php
    $fieldMappings = [
        'ppas_form_id' => 'ppas_form_id',
        'mode_of_delivery' => 'mode_of_delivery',
        'partner_office' => 'partner_office',
        'rationale' => 'rationale',
        'general_objectives' => 'general_objectives',
        'objectives' => 'general_objectives', // Map objectives to general_objectives
        'description' => 'description',
        'budget_breakdown' => 'budget_breakdown',
        'sustainability_plan' => 'sustainability_plan',
        'project_leader_responsibilities' => 'project_leader_responsibilities',
        'assistant_leader_responsibilities' => 'assistant_leader_responsibilities',
        'staff_responsibilities' => 'staff_responsibilities',
        'specific_objectives' => 'specific_objectives',
        'strategies' => 'strategies',
        'methods' => 'methods',
        'materials' => 'materials',
        'workplan' => 'workplan',
        'monitoring_items' => 'monitoring_items',
        'specific_plans' => 'specific_plans'
    ];

    // Process monitoring data exactly like in save_gad_proposal_robust.php
    if (isset($data['monitoring']) && is_array($data['monitoring'])) {
        error_log("Processing monitoring array with " . count($data['monitoring']) . " items");
        $monitoring_items = [];
        
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
                    $item['objectives'],
                    $item['performance_indicators'],
                    $item['baseline_data'] ?? '',
                    $item['performance_target'] ?? '',
                    $item['data_source'] ?? '',
                    $item['collection_method'] ?? '',
                    $item['frequency'] ?? '',
                    $item['responsible'] ?? ''
                ];
            }
        }
        
        // Store the processed data back
        $data['monitoring_items'] = $monitoring_items;
    }
    
    // Convert arrays to JSON with error checking
    $jsonFields = [
        'project_leader_responsibilities',
        'assistant_leader_responsibilities',
        'staff_responsibilities',
        'specific_objectives',
        'strategies',
        'methods',
        'materials',
        'workplan',
        'monitoring_items',
        'specific_plans'
    ];
    
    // JSON encode all array fields
    foreach ($jsonFields as $field) {
        if (isset($data[$field]) && (is_array($data[$field]) || is_object($data[$field]))) {
            try {
                $data[$field . '_json'] = json_encode($data[$field]);
                
                // Check for JSON encoding errors
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("JSON encoding error for $field: " . json_last_error_msg());
                }
            } catch (Exception $e) {
                error_log("JSON encoding error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'JSON encoding error: ' . $e->getMessage()]);
                exit();
            }
        }
    }
    
    // Build SQL UPDATE statement using only the fields from our mapping
    $sql = "UPDATE gad_proposals SET ";
    $params = [];
    $types = '';
    
    // Get the proposal ID
    $proposalId = isset($data['proposal_id']) ? intval($data['proposal_id']) : 0;
    if ($proposalId <= 0) {
        error_log("UPDATE GAD PROPOSAL ERROR: Invalid proposal ID: " . $proposalId);
        echo json_encode(['success' => false, 'message' => 'Invalid proposal ID']);
        exit();
    }
    
    // Add only the fields that exist in our mapping and in the data
    foreach ($fieldMappings as $postField => $dbField) {
        // Skip fields that don't exist in the data
        if (!isset($data[$postField]) && !isset($data[$postField . '_json'])) {
            continue;
        }
        
        // Check if this is a JSON field
        if (in_array($postField, $jsonFields) && isset($data[$postField . '_json'])) {
            $sql .= "$dbField = ?, ";
            $params[] = $data[$postField . '_json'];
            $types .= 's';
        } 
        // Regular field
        else if (isset($data[$postField])) {
            $sql .= "$dbField = ?, ";
            $params[] = $data[$postField];
            $types .= 's';
        }
    }
    
    // Remove trailing comma and space
    $sql = rtrim($sql, ', ');
    
    // Add WHERE clause
    $sql .= " WHERE $idField = ?";
    $params[] = $proposalId;
    $types .= 'i'; // Assuming ID is integer
    
    // Prepare statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("UPDATE GAD PROPOSAL ERROR: Prepare statement failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit();
    }
    
    // Create a reference array for dynamic binding
    $bind_params = array($types);
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    // Bind parameters using call_user_func_array
    call_user_func_array(array($stmt, 'bind_param'), $bind_params);
    
    // Execute the statement
    $result = $stmt->execute();
    if (!$result) {
        error_log("UPDATE GAD PROPOSAL ERROR: Execute failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Database execution error: ' . $stmt->error]);
        exit();
    }
    
    // Check affected rows
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'GAD Proposal updated successfully']);
    } else {
        // No rows affected could mean the data didn't change or the record doesn't exist
        error_log("UPDATE GAD PROPOSAL: No rows affected. ID: " . $proposalId);
        echo json_encode(['success' => true, 'message' => 'No changes detected or record not found']);
    }
    
    // Close statement
    $stmt->close();
    
} catch (Exception $e) {
    error_log("UPDATE GAD PROPOSAL ERROR: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    // End output buffering if still active
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}
?>

