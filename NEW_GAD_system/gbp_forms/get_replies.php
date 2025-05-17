<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'You must be logged in to access replies'
    ]);
    exit;
}

// Check if campus in session matches their actual campus
$userCampus = $_SESSION['username']; // Campus is stored in username session variable

// Make sure we have the required parameter
if (!isset($_GET['entry_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing entry ID parameter'
    ]);
    exit;
}

$entryId = intval($_GET['entry_id']);

// Validate entry ID
if ($entryId <= 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid entry ID'
    ]);
    exit;
}

// Verify the entry exists and belongs to this campus (except for Central)
$queryCondition = $userCampus === 'Central' ? "id = ?" : "id = ? AND campus = ?";
$query = "SELECT id, reply FROM gpb_entries WHERE $queryCondition";
$stmt = $conn->prepare($query);

if ($userCampus === 'Central') {
    $stmt->bind_param("i", $entryId);
} else {
    $stmt->bind_param("is", $entryId, $userCampus);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Entry not found or you do not have permission to access it'
    ]);
    exit;
}

$entry = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'replies' => $entry['reply']
]);

$stmt->close();
$conn->close(); 