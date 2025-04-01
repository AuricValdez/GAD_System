<?php
session_start();
require_once('../includes/db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if proposal ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Proposal ID is required']);
    exit();
}

$proposalId = intval($_POST['id']);

try {
    // Start transaction
    $conn->beginTransaction();

    // Delete related monitoring records first
    $stmt = $conn->prepare("DELETE FROM gad_proposal_monitoring WHERE proposal_id = :id");
    $stmt->execute([':id' => $proposalId]);

    // Delete the proposal
    $stmt = $conn->prepare("DELETE FROM gad_proposals WHERE id = :id");
    $stmt->execute([':id' => $proposalId]);

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Proposal deleted successfully']);
} catch (PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    
    // Log the error
    error_log("Error deleting proposal: " . $e->getMessage());
    
    echo json_encode(['success' => false, 'message' => 'Failed to delete proposal: ' . $e->getMessage()]);
}
?> 