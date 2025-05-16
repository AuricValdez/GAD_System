<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'You must be logged in to save replies'
    ]);
    exit;
}

// Check if campus in session matches their actual campus
$userCampus = $_SESSION['username']; // Campus is stored in username session variable

// Make sure we have the required parameters
if (!isset($_POST['entry_id']) || !isset($_POST['replies'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required parameters'
    ]);
    exit;
}

$entryId = intval($_POST['entry_id']);
$replies = $_POST['replies'];

// Validate entry ID
if ($entryId <= 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid entry ID'
    ]);
    exit;
}

// Verify the entry exists and belongs to this campus (except for Central)
if ($userCampus !== 'Central') {
    $checkQuery = "SELECT id FROM gpb_entries WHERE id = ? AND campus = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("is", $entryId, $userCampus);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Entry not found or you do not have permission to modify it'
        ]);
        exit;
    }
}

// Update the reply column in the database
$updateQuery = "UPDATE gpb_entries SET reply = ? WHERE id = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("si", $replies, $entryId);

if ($stmt->execute()) {
    // Create a notification for Central if this is a campus user (not Central)
    if ($userCampus !== 'Central') {
        try {
            // First, fetch the gender_issue for this entry
            $issueQuery = "SELECT gender_issue FROM gpb_entries WHERE id = ?";
            $issueStmt = $conn->prepare($issueQuery);
            $issueStmt->bind_param("i", $entryId);
            $issueStmt->execute();
            $issueResult = $issueStmt->get_result();
            
            $genderIssue = "Unknown Issue";
            if ($issueRow = $issueResult->fetch_assoc()) {
                $genderIssue = $issueRow['gender_issue'];
            }
            $issueStmt->close();
            
            // Prepare notification message with the gender issue name
            $message = "New reply from $userCampus on GBP entry '$genderIssue'";
            
            // Insert into central_notifications table
            $notifyQuery = "INSERT INTO central_notifications (gbp_id, campus, message) VALUES (?, ?, ?)";
            $notifyStmt = $conn->prepare($notifyQuery);
            $notifyStmt->bind_param("iss", $entryId, $userCampus, $message);
            $notifyStmt->execute();
            $notifyStmt->close();
            
            // Log notification creation
            error_log("Created notification for Central: $message");
        } catch (Exception $e) {
            error_log("Failed to create notification: " . $e->getMessage());
            // Don't return failure to user since the reply was saved successfully
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Replies saved successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save replies: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close(); 