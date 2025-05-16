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

// Check if this is a POST request (for security)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Include database configuration
require_once '../config.php';

try {
    $entryId = intval($_GET['id']);
    
    // Delete the entry from the database
    $sql = "DELETE FROM gpb_entries WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $entryId);
    $result = $stmt->execute();
    
    if ($result && $stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Entry deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete entry or entry not found'
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
} 