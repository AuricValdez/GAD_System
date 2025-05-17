<?php
session_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug logging
function debug_log($message) {
    error_log("[save_narrative] " . $message);
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    debug_log("User not logged in");
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Include database configuration
require_once '../config.php';

// Start output buffering to prevent any unwanted output
ob_start();

try {
    // Debug received data
    $debugOutput = array();
    $debugOutput[] = "=== Received Data ===";
    $debugOutput[] = "Current user: " . $_SESSION['user'];
    $debugOutput[] = "ppas_form_id: " . (isset($_POST['ppas_form_id']) ? $_POST['ppas_form_id'] : 'Not provided');
    $debugOutput[] = "implementing_office: " . (isset($_POST['implementing_office']) ? $_POST['implementing_office'] : 'Not provided');
    $debugOutput[] = "partner_agency: " . ($_POST['partner_agency'] ?? 'not set');
    $debugOutput[] = "extension_service_agenda: " . ($_POST['extension_service_agenda'] ?? 'not set');
    $debugOutput[] = "type_beneficiaries: " . ($_POST['type_beneficiaries'] ?? 'not set');
    $debugOutput[] = "beneficiary_distribution: " . ($_POST['beneficiary_distribution'] ?? 'not set');
    $debugOutput[] = "team_tasks: " . ($_POST['team_tasks'] ?? 'not set');
    $debugOutput[] = "activity_narrative: " . ($_POST['activity_narrative'] ?? 'not set');
    $debugOutput[] = "activity_ratings: " . ($_POST['activity_ratings'] ?? 'not set');
    $debugOutput[] = "timeliness_ratings: " . ($_POST['timeliness_ratings'] ?? 'not set');
    $debugOutput[] = "activity_images: " . ($_POST['activity_images'] ?? 'not set');

    // Get the logged-in user and campus from session
    $logged_in_user = $_SESSION['user'];
    $campus = $_SESSION['campus'];
    $debugOutput[] = "logged_in_user: " . $logged_in_user;
    $debugOutput[] = "campus: " . $campus;
    
    // Process and validate input data
    $ppasFormId = intval($_POST['ppas_form_id']);
    $implementingOffice = $_POST['implementing_office'] ?? '[]';
    $partnerAgency = $_POST['partner_agency'] ?? '';
    
    // Debug processed data
    $debugOutput[] = "\n=== Processed Data ===";
    $debugOutput[] = "ppasFormId: " . $ppasFormId;
    $debugOutput[] = "implementingOffice: " . $implementingOffice;
    $debugOutput[] = "partnerAgency: " . $partnerAgency;
    
    // Process extension service agenda checkboxes
    $extensionServiceAgenda = array_fill(0, 12, 0); // Initialize array with 12 zeros
    if (isset($_POST['extension_service_agenda'])) {
        // Get the positions array
        $positions = $_POST['extension_service_agenda'];
        
        // If it's a string (JSON), decode it
        if (is_string($positions)) {
            $positions = json_decode($positions, true);
        }
        
        // If we have valid positions, set them to 1
        if (is_array($positions)) {
            foreach ($positions as $pos) {
                if (is_numeric($pos) && $pos >= 0 && $pos < 12) {
                    $extensionServiceAgenda[$pos] = 1;
                }
            }
        }
        
        // Debug output
        $debugOutput[] = "Raw positions: " . print_r($positions, true);
        $debugOutput[] = "Processed array: " . print_r($extensionServiceAgenda, true);
    }
    
    // Convert to JSON for storage
    $extensionServiceAgenda = json_encode($extensionServiceAgenda);
    $debugOutput[] = "Final extensionServiceAgenda: " . $extensionServiceAgenda;
    
    $typeBeneficiaries = $_POST['type_beneficiaries'] ?? '';
    $beneficiaryDistribution = $_POST['beneficiary_distribution'] ?? '{}';
    
    // Process team tasks
    $leaderTasks = [];
    $assistantTasks = [];
    $staffTasks = [];
    
    if (isset($_POST['team_tasks'])) {
        $teamTasks = $_POST['team_tasks'];
        
        // If it's a string (JSON), decode it
        if (is_string($teamTasks)) {
            $teamTasks = json_decode($teamTasks, true);
        }
        
        // Extract tasks for each role
        if (is_array($teamTasks)) {
            // Process project leader tasks
            if (isset($teamTasks['projectLeader']) && is_array($teamTasks['projectLeader'])) {
                foreach ($teamTasks['projectLeader'] as $task) {
                    if (isset($task['task']) && !empty($task['task'])) {
                        $leaderTasks[] = $task['task'];
                    }
                }
            }
            
            // Process assistant leader tasks
            if (isset($teamTasks['assistantLeader']) && is_array($teamTasks['assistantLeader'])) {
                foreach ($teamTasks['assistantLeader'] as $task) {
                    if (isset($task['task']) && !empty($task['task'])) {
                        $assistantTasks[] = $task['task'];
                    }
                }
            }
            
            // Process project staff tasks
            if (isset($teamTasks['projectStaff']) && is_array($teamTasks['projectStaff'])) {
                foreach ($teamTasks['projectStaff'] as $task) {
                    if (isset($task['task']) && !empty($task['task'])) {
                        $staffTasks[] = $task['task'];
                    }
                }
            }
        }
        
        // Debug output
        $debugOutput[] = "Raw team tasks: " . print_r($teamTasks, true);
        $debugOutput[] = "Leader tasks: " . print_r($leaderTasks, true);
        $debugOutput[] = "Assistant tasks: " . print_r($assistantTasks, true);
        $debugOutput[] = "Staff tasks: " . print_r($staffTasks, true);
    }
    
    // Convert to JSON for storage
    $leaderTasks = json_encode($leaderTasks);
    $assistantTasks = json_encode($assistantTasks);
    $staffTasks = json_encode($staffTasks);
    
    $activityNarrative = $_POST['activity_narrative'] ?? '';
    $activityRatings = $_POST['activity_ratings'] ?? '{}';
    $timelinessRatings = $_POST['timeliness_ratings'] ?? '{}';
    
    // Debug image handling
    $imageArray = [];
    
    // Directory to save uploaded images
    $uploadDir = '../narrative_images/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Handle image URLs and files
    if (isset($_POST['activity_images'])) {
        // Decode JSON array if it's a string
        $imageData = $_POST['activity_images'];
        if (is_string($imageData)) {
            $imageData = json_decode($imageData, true);
        }
        
        if (is_array($imageData) && !empty($imageData)) {
            $debugOutput[] = "Processing " . count($imageData) . " images";
            
            foreach ($imageData as $index => $base64_image) {
                if (strpos($base64_image, 'data:image') === 0) {
                    $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64_image));
                    $fileName = 'narrative_' . $ppasFormId . '_' . time() . '_' . $index . '.jpg';
                    $targetPath = $uploadDir . $fileName;
                    
                    $debugOutput[] = "Saving image to: " . $targetPath;
                    
                    if (file_put_contents($targetPath, $image_data)) {
                        $imageArray[] = $fileName;
                        $debugOutput[] = "Successfully saved image: " . $fileName;
                    } else {
                        $debugOutput[] = "Failed to save image: " . $fileName . " - " . error_get_last()['message'];
                    }
                } else {
                    $debugOutput[] = "Invalid image data format at index " . $index;
                }
            }
        } else {
            $debugOutput[] = "No valid image data received";
        }
    } else {
        $debugOutput[] = "No activity_images parameter received";
    }
    
    $imageJson = json_encode($imageArray);
    $debugOutput[] = "Final image array: " . $imageJson;
    
    // Debug final data before saving
    $debugOutput[] = "\n=== Final Data to be Saved ===";
    $debugOutput[] = "ppas_form_id: " . $ppasFormId;
    $debugOutput[] = "implementing_office: " . $implementingOffice;
    $debugOutput[] = "partner_agency: " . $partnerAgency;
    $debugOutput[] = "extension_service_agenda: " . $extensionServiceAgenda;
    $debugOutput[] = "type_beneficiaries: " . $typeBeneficiaries;
    $debugOutput[] = "beneficiary_distribution: " . $beneficiaryDistribution;
    $debugOutput[] = "leader_tasks: " . $leaderTasks;
    $debugOutput[] = "assistant_tasks: " . $assistantTasks;
    $debugOutput[] = "staff_tasks: " . $staffTasks;
    $debugOutput[] = "activity_narrative: " . $activityNarrative;
    $debugOutput[] = "activity_ratings: " . $activityRatings;
    $debugOutput[] = "timeliness_ratings: " . $timelinessRatings;
    $debugOutput[] = "activity_images: " . $imageJson;
    $debugOutput[] = "campus: " . $campus;
    
    // Connect to database
    try {
        // Use the constants from config.php instead of variables
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            debug_log("Database connection failed: " . $conn->connect_error);
            throw new Exception('Database connection failed: ' . $conn->connect_error);
        }
    } catch (Exception $e) {
        debug_log("Database connection error: " . $e->getMessage());
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
    
    // Prepare SQL statement
    $sql = "INSERT INTO narrative (
        ppas_form_id, 
        implementing_office, 
        partner_agency, 
        extension_service_agenda, 
        type_beneficiaries, 
        beneficiary_distribution, 
        leader_tasks,
        assistant_tasks,
        staff_tasks,
        activity_narrative, 
        activity_ratings, 
        timeliness_ratings, 
        activity_images,
        campus
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "isssssssssssss",
        $ppasFormId,
        $implementingOffice,
        $partnerAgency,
        $extensionServiceAgenda,
        $typeBeneficiaries,
        $beneficiaryDistribution,
        $leaderTasks,
        $assistantTasks,
        $staffTasks,
        $activityNarrative,
        $activityRatings,
        $timelinessRatings,
        $imageJson,
        $campus
    );
    
    $result = $stmt->execute();
    
    if (!$result) {
        debug_log("Execute failed: " . $stmt->error);
        throw new Exception('Failed to save narrative data');
    }
    
    // Get insert ID for confirmation
    $insertId = $conn->insert_id;
    debug_log("Narrative saved successfully. ID: " . $insertId);
    
    // Prepare response with debug info for console
    $response = [
        'success' => true,
        'message' => 'Narrative saved successfully',
        'debug' => implode("\n", $debugOutput)
    ];
    
    // Clean up
    $stmt->close();
    $conn->close();
    
    // Clear any output buffer
    ob_end_clean();
    
    // Return the prepared response
    echo json_encode($response);
    
} catch (Exception $e) {
    debug_log("Error: " . $e->getMessage());
    
    // Clear any output buffer
    ob_end_clean();
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 