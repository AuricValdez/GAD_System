<?php
session_start();
require_once "../config.php";

// Debug session info
error_log(print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'Central') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'fetch_gbp_entries':
            fetchGbpEntries();
            break;
        case 'fetch_gbp_details':
            fetchGbpDetails();
            break;
        case 'approve_gbp':
            approveGbp();
            break;
        case 'reject_gbp':
            rejectGbp();
            break;
        case 'delete_feedback':
            deleteFeedback();
            break;
        case 'count_pending':
            countPendingEntries();
            break;
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            break;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

/**
 * Fetch GBP entries with filters
 */
function fetchGbpEntries() {
    global $conn;
    
    $genderIssue = $_POST['gender_issue'] ?? '';
    $campus = $_POST['campus'] ?? 'All';
    $status = $_POST['status'] ?? 'All';
    
    try {
        // Start building query
        $query = "SELECT id, campus, category, 
                  gender_issue, year, status
                  FROM gpb_entries
                  WHERE 1=1";
        
        $params = [];
        
        // Add filters
        if (!empty($genderIssue)) {
            $query .= " AND gender_issue LIKE ?";
            $params[] = "%$genderIssue%";
        }
        
        if ($campus !== 'All') {
            $query .= " AND campus = ?";
            $params[] = $campus;
        }
        
        if ($status !== 'All') {
            $query .= " AND status = ?";
            $params[] = $status;
        }
        
        // Order by ID desc (newest first)
        $query .= " ORDER BY id DESC";
        
        $stmt = $conn->prepare($query);
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $entries = [];
        while ($row = $result->fetch_assoc()) {
            $entries[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'entries' => $entries]);
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to fetch entries. Please try again later.']);
    }
}

/**
 * Fetch detailed information for a specific GBP entry
 */
function fetchGbpDetails() {
    global $conn;
    
    $id = $_POST['id'] ?? 0;
    
    if (!$id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid entry ID.']);
        return;
    }
    
    try {
        // Get entry details
        $query = "SELECT * FROM gpb_entries WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Entry not found.']);
            return;
        }
        
        $entry = $result->fetch_assoc();
        
        // Parse JSON fields
        $entry['generic_activity'] = json_decode($entry['generic_activity'], true);
        $entry['specific_activities'] = json_decode($entry['specific_activities'], true);
        $entry['feedback'] = json_decode($entry['feedback'], true) ?? [];
        $entry['reply'] = json_decode($entry['reply'], true) ?? [];
        
        // Calculate total participants if not already set
        if (!isset($entry['total_participants'])) {
            $entry['total_participants'] = $entry['male_participants'] + $entry['female_participants'];
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'entry' => $entry]);
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to fetch entry details. Please try again later.']);
    }
}

/**
 * Approve a GBP entry
 */
function approveGbp() {
    global $conn;
    
    $id = $_POST['id'] ?? 0;
    
    if (!$id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid entry ID.']);
        return;
    }
    
    try {
        $conn->begin_transaction();
        
        // Get campus information for notification
        $campusQuery = "SELECT campus, gender_issue FROM gpb_entries WHERE id = ?";
        $campusStmt = $conn->prepare($campusQuery);
        $campusStmt->bind_param("i", $id);
        $campusStmt->execute();
        $campusResult = $campusStmt->get_result();
        $campusRow = $campusResult->fetch_assoc();
        $campus = $campusRow['campus'];
        $genderIssue = $campusRow['gender_issue'];
        
        // Update entry status
        $query = "UPDATE gpb_entries SET status = 'Approved', created_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        
        if ($result) {
            // Delete any existing rejection notifications for this GBP entry
            $deleteQuery = "DELETE FROM gbp_notifications WHERE gbp_id = ? AND status = 'Rejected'";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $id);
            $deleteStmt->execute();
            
            // Create notification for the campus
            $notificationMessage = "Your GBP entry addressing \"$genderIssue\" has been approved by Central Office.";
            $notificationStatus = "Approved";
            
            $notifQuery = "INSERT INTO gbp_notifications (gbp_id, campus, status, message) VALUES (?, ?, ?, ?)";
            $notifStmt = $conn->prepare($notifQuery);
            $notifStmt->bind_param("isss", $id, $campus, $notificationStatus, $notificationMessage);
            $notifStmt->execute();
            
            $conn->commit();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'GBP entry approved successfully.']);
        } else {
            $conn->rollback();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to approve entry.']);
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Database error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to approve entry. Please try again later.']);
    }
}

/**
 * Reject a GBP entry and add feedback
 */
function rejectGbp() {
    global $conn;
    
    $id = $_POST['id'] ?? 0;
    $feedbackItems = json_decode($_POST['feedback'] ?? '[]', true);
    
    if (!$id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid entry ID.']);
        return;
    }
    
    if (empty($feedbackItems)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Feedback is required for rejection.']);
        return;
    }
    
    try {
        $conn->begin_transaction();
        
        // Get entry information for notification
        $entryQuery = "SELECT campus, gender_issue, feedback, reply FROM gpb_entries WHERE id = ?";
        $entryStmt = $conn->prepare($entryQuery);
        $entryStmt->bind_param("i", $id);
        $entryStmt->execute();
        $entryResult = $entryStmt->get_result();
        $entryRow = $entryResult->fetch_assoc();
        $campus = $entryRow['campus'];
        $genderIssue = $entryRow['gender_issue'];
        
        // Merge new feedback with existing feedback
        $existingFeedback = json_decode($entryRow['feedback'] ?? '[]', true) ?? [];
        $allFeedback = array_merge($existingFeedback, $feedbackItems);
        $feedbackJson = json_encode($allFeedback);
        
        // Get existing replies and add empty strings for new feedback items
        $existingReplies = json_decode($entryRow['reply'] ?? '[]', true) ?? [];
        $emptyReplies = array_fill(0, count($feedbackItems), ""); // Add empty strings for new feedback
        $allReplies = array_merge($existingReplies, $emptyReplies);
        $replyJson = json_encode($allReplies);
        
        // Update entry status, feedback, and reply
        $query = "UPDATE gpb_entries SET status = 'Rejected', feedback = ?, reply = ?, created_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $feedbackJson, $replyJson, $id);
        $result = $stmt->execute();
        
        if ($result) {
            // Create notification for the campus
            $notificationMessage = "Your GPB entry addressing \"$genderIssue\" has been rejected by Central Office. Please review the feedback and make necessary revisions.";
            $notificationStatus = "Rejected";
            
            $notifQuery = "INSERT INTO gbp_notifications (gbp_id, campus, status, message) VALUES (?, ?, ?, ?)";
            $notifStmt = $conn->prepare($notifQuery);
            $notifStmt->bind_param("isss", $id, $campus, $notificationStatus, $notificationMessage);
            $notifStmt->execute();
            
            $conn->commit();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'GBP entry rejected with feedback.']);
        } else {
            $conn->rollback();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to reject entry.']);
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Database error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to reject entry. Please try again later.']);
    }
}

/**
 * Delete a feedback item
 */
function deleteFeedback() {
    global $conn;
    
    $gbpId = $_POST['gbp_id'] ?? 0;
    $feedbackIndex = isset($_POST['feedback_index']) ? intval($_POST['feedback_index']) : -1;
    
    if (!$gbpId || $feedbackIndex < 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
        return;
    }
    
    try {
        // Get current feedback and reply
        $query = "SELECT feedback, reply FROM gpb_entries WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $gbpId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Entry not found.']);
            return;
        }
        
        $row = $result->fetch_assoc();
        $feedbackItems = json_decode($row['feedback'] ?? '[]', true) ?? [];
        
        if (!isset($feedbackItems[$feedbackIndex])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Feedback item not found.']);
            return;
        }
        
        // Get reply items if they exist
        $replyItems = [];
        if (!empty($row['reply'])) {
            $replyItems = json_decode($row['reply'], true) ?? [];
        }
        
        // Remove the feedback item
        array_splice($feedbackItems, $feedbackIndex, 1);
        $feedbackJson = json_encode($feedbackItems);
        
        // Remove the corresponding reply item if it exists
        if (!empty($replyItems) && count($replyItems) > $feedbackIndex) {
            array_splice($replyItems, $feedbackIndex, 1);
        }
        $replyJson = json_encode($replyItems);
        
        // Update the entry with the new feedback and reply arrays
        $query = "UPDATE gpb_entries SET feedback = ?, reply = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $feedbackJson, $replyJson, $gbpId);
        $result = $stmt->execute();
        
        if ($result) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Feedback and reply deleted successfully.']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to delete feedback and reply.']);
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to delete feedback. Please try again later.']);
    }
}

/**
 * Count pending entries in the gpb_entries table
 */
function countPendingEntries() {
    global $conn;
    
    try {
        $query = "SELECT COUNT(*) as count FROM gpb_entries WHERE status = 'Pending'";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'count' => $row['count']]);
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to count pending entries.']);
    }
} 
?> 