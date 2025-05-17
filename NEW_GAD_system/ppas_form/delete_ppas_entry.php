<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID is required']);
    exit();
}

$id = intval($_GET['id']);

try {
    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM ppas_forms WHERE id = ?");
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();

    if ($result) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'PPAS entry deleted successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to delete PPAS entry']);
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>