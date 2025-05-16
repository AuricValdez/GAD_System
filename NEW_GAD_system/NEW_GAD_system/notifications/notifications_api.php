<?php
session_start();
require_once "../config.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Current user's campus
$userCampus = $_SESSION['username'];

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_notifications':
            getNotifications();
            break;
        case 'mark_as_read':
            markAsRead();
            break;
        case 'count_unread':
            countUnread();
            break;
        case 'create_notification':
            createNotification();
            break;
        case 'clear_all_read':
            clearAllRead();
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
 * Get notifications for the current user
 */
function getNotifications() {
    global $conn, $userCampus;
    
    try {
        $query = "SELECT n.*, g.gender_issue, g.campus 
                 FROM gbp_notifications n
                 JOIN gpb_entries g ON n.gbp_id = g.id
                 WHERE n.campus = ?
                 ORDER BY n.created_at DESC
                 LIMIT 50";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $userCampus);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'notifications' => $notifications]);
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to fetch notifications.']);
    }
}

/**
 * Mark a notification as read
 */
function markAsRead() {
    global $conn, $userCampus;
    
    $notificationId = $_POST['notification_id'] ?? 0;
    $allNotifications = isset($_POST['all']) && $_POST['all'] == 'true';
    
    try {
        if ($allNotifications) {
            // Mark all approved notifications as read
            $query = "UPDATE gbp_notifications SET is_read = 1 
                     WHERE campus = ? AND status = 'Approved'";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $userCampus);
        } else {
            // Mark specific notification as read
            $query = "UPDATE gbp_notifications SET is_read = 1 
                     WHERE id = ? AND campus = ? AND status = 'Approved'";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("is", $notificationId, $userCampus);
        }
        
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Notification(s) marked as read.']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No notifications found or updated.']);
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read.']);
    }
}

/**
 * Count unread notifications
 */
function countUnread() {
    global $conn, $userCampus;
    
    try {
        $query = "SELECT COUNT(*) as count FROM gbp_notifications 
                 WHERE campus = ? AND is_read = 0";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $userCampus);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'count' => $row['count']]);
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to count unread notifications.']);
    }
}

/**
 * Create a new notification
 * Note: This should typically be called when Central approves/rejects a GBP entry
 */
function createNotification() {
    global $conn;
    
    // Only allow Central to create notifications
    if ($_SESSION['username'] !== 'Central') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Only Central can create notifications.']);
        return;
    }
    
    $gbpId = $_POST['gbp_id'] ?? 0;
    $campus = $_POST['campus'] ?? '';
    $status = $_POST['status'] ?? '';
    $message = $_POST['message'] ?? '';
    
    if (!$gbpId || !$campus || !$status || !$message) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
        return;
    }
    
    try {
        $query = "INSERT INTO gbp_notifications (gbp_id, campus, status, message)
                 VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isss", $gbpId, $campus, $status, $message);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Notification created successfully.']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to create notification.']);
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Clear all read notifications
 */
function clearAllRead() {
    global $conn, $userCampus;
    
    try {
        $query = "DELETE FROM gbp_notifications 
                 WHERE campus = ? AND is_read = 1";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $userCampus);
        $stmt->execute();
        
        $affectedRows = $stmt->affected_rows;
        
        header('Content-Type: application/json');
        if ($affectedRows > 0) {
            echo json_encode(['success' => true, 'message' => 'All read notifications cleared successfully.', 'count' => $affectedRows]);
        } else {
            echo json_encode(['success' => true, 'message' => 'No read notifications to clear.', 'count' => 0]);
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to clear read notifications.']);
    }
}
?> 