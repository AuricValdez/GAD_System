<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Entry ID is required']);
    exit();
}

// Include database configuration
require_once '../config.php';

try {
    $entryId = intval($_GET['id']);
    
    // Get the entry data
    $sql = "SELECT * FROM gpb_entries WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $entryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Entry not found']);
        exit();
    }
    
    $entry = $result->fetch_assoc();
    $stmt->close();
    
    // Parse the JSON data for programs and activities
    $programs = [];
    
    // Parse generic_activity (program names)
    $programNames = json_decode($entry['generic_activity'], true);
    
    // Parse specific_activities (activities for each program)
    $specificActivities = json_decode($entry['specific_activities'], true);
    
    // Create the programs array with activities
    if (is_array($programNames) && is_array($specificActivities)) {
        for ($i = 0; $i < count($programNames); $i++) {
            $programData = [
                'id' => $i + 1,
                'name' => $programNames[$i],
                'activities' => []
            ];
            
            if (isset($specificActivities[$i]) && is_array($specificActivities[$i])) {
                foreach ($specificActivities[$i] as $j => $activityName) {
                    if (!empty($activityName)) {
                        $programData['activities'][] = [
                            'id' => $j + 1,
                            'name' => $activityName
                        ];
                    }
                }
            }
            
            $programs[] = $programData;
        }
    }
    
    // Add programs to the entry data
    $entry['programs'] = $programs;
    
    echo json_encode([
        'success' => true,
        'entry' => $entry
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
} 