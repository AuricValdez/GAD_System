<?php
session_start();
header('Content-Type: application/json');

// Debug session information
error_log("Session data in save_signatory.php: " . print_r($_SESSION, true));

// Check if user is logged in and not Central
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

if ($_SESSION['username'] === 'Central') {
    echo json_encode(['status' => 'error', 'message' => 'Central user cannot modify signatories']);
    exit();
}

// Get the campus from the session
$campus = $_SESSION['campus'] ?? $_SESSION['username'];

// Get JSON data from request
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data format']);
    exit();
}

// Extract signatory data
$name1 = isset($data['name1']) ? trim($data['name1']) : '';
$name2 = isset($data['name2']) ? trim($data['name2']) : '';
$name3 = isset($data['name3']) ? trim($data['name3']) : '';
$name4 = isset($data['name4']) ? trim($data['name4']) : '';
$name5 = isset($data['name5']) ? trim($data['name5']) : '';
$id = isset($data['id']) ? intval($data['id']) : null;

// Set predefined rank values
$gad_head_secretariat = 'GAD Head Secretariat';
$vice_chancellor_rde = 'Vice Chancellor For Research, Development and Extension';
$chancellor = 'Chancellor';
$asst_director_gad = 'Assistant Director For GAD Advocacies';
$head_extension_services = 'Head of Extension Services';

// Connect to database
require_once '../includes/dbh.inc.php';

try {
    // Check if a record for this campus already exists
    $stmt = $conn->prepare("SELECT id FROM signatories WHERE campus = ?");
    $stmt->bind_param("s", $campus);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Record exists, update it
        $row = $result->fetch_assoc();
        $id = $row['id'];
        
        $stmt = $conn->prepare("UPDATE signatories SET name1 = ?, gad_head_secretariat = ?, name2 = ?, vice_chancellor_rde = ?, name3 = ?, chancellor = ?, name4 = ?, asst_director_gad = ?, name5 = ?, head_extension_services = ? WHERE id = ?");
        $stmt->bind_param("ssssssssssi", $name1, $gad_head_secretariat, $name2, $vice_chancellor_rde, $name3, $chancellor, $name4, $asst_director_gad, $name5, $head_extension_services, $id);
    } else {
        // No record, insert new one
        $stmt = $conn->prepare("INSERT INTO signatories (campus, name1, gad_head_secretariat, name2, vice_chancellor_rde, name3, chancellor, name4, asst_director_gad, name5, head_extension_services) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssss", $campus, $name1, $gad_head_secretariat, $name2, $vice_chancellor_rde, $name3, $chancellor, $name4, $asst_director_gad, $name5, $head_extension_services);
    }
    
    if ($stmt->execute()) {
        // If it was an insert, get the new ID
        if (!$id) {
            $id = $conn->insert_id;
        }
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Signatory data saved successfully',
            'id' => $id
        ]);
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }
} catch (Exception $e) {
    error_log("Error in save_signatory.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Close connection
$conn->close();
?> 