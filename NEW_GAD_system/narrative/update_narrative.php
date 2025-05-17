<?php
session_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug logging
function debug_log($message) {
    error_log("[update_narrative] " . $message);
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
    $debugOutput[] = "Current user: " . $_SESSION['username'];
    $debugOutput[] = "narrative_id: " . (isset($_POST['narrative_id']) ? $_POST['narrative_id'] : 'Not provided');
    $debugOutput[] = "ppas_form_id: " . (isset($_POST['ppas_form_id']) ? $_POST['ppas_form_id'] : 'Not provided');
    $debugOutput[] = "implementing_office: " . (isset($_POST['implementing_office']) ? $_POST['implementing_office'] : 'Not provided');
    
    // Get narrative ID
    $narrativeId = isset($_POST['narrative_id']) ? intval($_POST['narrative_id']) : 0;
    if ($narrativeId <= 0) {
        throw new Exception('Invalid narrative ID');
    }
    
    // Connect to database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // First get current narrative data to check what needs updating
    $sql = "SELECT * FROM narrative WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $narrativeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$row = $result->fetch_assoc()) {
        throw new Exception('Narrative not found');
    }
    
    // Extract data from the request
    $ppasFormId = isset($_POST['ppas_form_id']) ? intval($_POST['ppas_form_id']) : 0;
    $implementingOffice = isset($_POST['implementing_office']) ? $_POST['implementing_office'] : '[]';
    $partnerAgency = isset($_POST['partner_agency']) ? $_POST['partner_agency'] : '';
    
    // Process extension service agenda checkboxes (matching the save_narrative.php approach)
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

    $typeBeneficiaries = isset($_POST['type_beneficiaries']) ? $_POST['type_beneficiaries'] : '';
    $beneficiaryDistribution = isset($_POST['beneficiary_distribution']) ? $_POST['beneficiary_distribution'] : '{}';
    $teamTasks = isset($_POST['team_tasks']) ? $_POST['team_tasks'] : '{}';
    $activityNarrative = isset($_POST['activity_narrative']) ? $_POST['activity_narrative'] : '';
    $activityRatings = isset($_POST['activity_ratings']) ? $_POST['activity_ratings'] : '{}';
    $timelinessRatings = isset($_POST['timeliness_ratings']) ? $_POST['timeliness_ratings'] : '{}';
    
    // Process team tasks
    $decodedTasks = json_decode($teamTasks, true);
    $leaderTasks = [];
    $assistantTasks = [];
    $staffTasks = [];
    
    if (is_array($decodedTasks)) {
        if (isset($decodedTasks['projectLeader']) && is_array($decodedTasks['projectLeader'])) {
            foreach ($decodedTasks['projectLeader'] as $task) {
                if (isset($task['task']) && !empty($task['task'])) {
                    $leaderTasks[] = $task['task'];
                }
            }
        }
        
        if (isset($decodedTasks['assistantLeader']) && is_array($decodedTasks['assistantLeader'])) {
            foreach ($decodedTasks['assistantLeader'] as $task) {
                if (isset($task['task']) && !empty($task['task'])) {
                    $assistantTasks[] = $task['task'];
                }
            }
        }
        
        if (isset($decodedTasks['projectStaff']) && is_array($decodedTasks['projectStaff'])) {
            foreach ($decodedTasks['projectStaff'] as $task) {
                if (isset($task['task']) && !empty($task['task'])) {
                    $staffTasks[] = $task['task'];
                }
            }
        }
    }
    
    $leaderTasks = json_encode($leaderTasks);
    $assistantTasks = json_encode($assistantTasks);
    $staffTasks = json_encode($staffTasks);
    
    // Handle image uploads and existing images
    $currentImages = json_decode($row['activity_images'] ?? '[]', true);
    $keepImages = isset($_POST['existing_images']) ? json_decode($_POST['existing_images'], true) : [];
    
    // Debug image handling
    $debugOutput[] = "Current images: " . json_encode($currentImages);
    $debugOutput[] = "Images to keep: " . json_encode($keepImages);
    
    // If existing_images wasn't provided but we're in update mode, preserve all existing images
    if (empty($keepImages) && !empty($currentImages)) {
        $keepImages = $currentImages;
        $debugOutput[] = "No existing_images parameter - preserving all current images: " . json_encode($keepImages);
    }
    
    // Process existing images - determine which ones to keep
    $imagesToKeep = array_intersect($currentImages, $keepImages);
    if (empty($imagesToKeep) && !empty($currentImages)) {
        // If the intersection is empty but we have current images, keep all current images
        $imagesToKeep = $currentImages;
        $debugOutput[] = "Preserving all current images because none were explicitly kept";
    }
    $debugOutput[] = "Images intersection to keep: " . json_encode($imagesToKeep);
    
    // Process new image uploads
    $uploadDir = '../narrative_images/';
    $newImages = [];
    
    // Handle base64 image data
    if (isset($_POST['activity_images'])) {
        $imageData = $_POST['activity_images'];
        if (is_string($imageData)) {
            $imageData = json_decode($imageData, true);
        }
        
        if (is_array($imageData) && !empty($imageData)) {
            $debugOutput[] = "Processing " . count($imageData) . " new image data";
            
            foreach ($imageData as $index => $base64_image) {
                if (strpos($base64_image, 'data:image') === 0) {
                    $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64_image));
                    $fileName = 'narrative_' . $ppasFormId . '_' . time() . '_' . $index . '.jpg';
                    $targetPath = $uploadDir . $fileName;
                    
                    if (file_put_contents($targetPath, $image_data)) {
                        $newImages[] = $fileName;
                        $debugOutput[] = "Saved new image: " . $fileName;
                    } else {
                        $debugOutput[] = "Failed to save image: " . $fileName;
                    }
                }
            }
        }
    }
    
    // Handle traditional file uploads
    foreach ($_FILES as $key => $file) {
        if (strpos($key, 'image_') === 0 && !empty($file['tmp_name'])) {
            $fileName = 'narrative_' . $ppasFormId . '_' . time() . '_' . rand(1000, 9999) . '.jpg';
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $newImages[] = $fileName;
                $debugOutput[] = "Uploaded file: " . $fileName;
            }
        }
    }
    
    // Combine kept and new images
    $finalImages = array_merge($imagesToKeep, $newImages);
    $imageJson = json_encode($finalImages);
    $debugOutput[] = "Final image array: " . $imageJson;
    
    // Delete removed images
    $imagesToDelete = array_diff($currentImages, $keepImages);
    foreach ($imagesToDelete as $image) {
        $imagePath = $uploadDir . $image;
        if (file_exists($imagePath)) {
            unlink($imagePath);
            $debugOutput[] = "Deleted image: " . $image;
        }
    }
    
    // Update the narrative record
    $sql = "UPDATE narrative SET 
            ppas_form_id = ?, 
            implementing_office = ?, 
            partner_agency = ?, 
            extension_service_agenda = ?, 
            type_beneficiaries = ?, 
            beneficiary_distribution = ?, 
            leader_tasks = ?,
            assistant_tasks = ?,
            staff_tasks = ?,
            activity_narrative = ?, 
            activity_ratings = ?, 
            timeliness_ratings = ?, 
            activity_images = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "issssssssssssi",
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
        $narrativeId
    );
    
    $success = $stmt->execute();
    
    if (!$success) {
        $conn->rollback();
        throw new Exception('Failed to update narrative: ' . $stmt->error);
    }
    
    // Commit the transaction
    $conn->commit();
    
    // Write debug log
    error_log(implode("\n", $debugOutput));
    
    // Clean up
    ob_end_clean();
    
    // Return success response
    echo json_encode(['success' => true, 'message' => 'Narrative updated successfully']);
    
} catch (Exception $e) {
    // If transaction was started, roll it back
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    
    debug_log("Error: " . $e->getMessage());
    
    // Clean up
    ob_end_clean();
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit();
?> 