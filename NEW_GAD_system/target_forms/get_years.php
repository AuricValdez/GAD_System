<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['campus'])) {
    echo json_encode(['error' => 'Campus parameter is required']);
    exit;
}

$campus = $_GET['campus'];

// Prepare and execute query to get years for the specified campus
$stmt = $conn->prepare("SELECT DISTINCT year FROM target_form WHERE campus = ? ORDER BY year");
$stmt->bind_param("s", $campus);
$stmt->execute();
$result = $stmt->get_result();

$years = [];
while ($row = $result->fetch_assoc()) {
    $years[] = $row['year'];
}

echo json_encode($years);

$stmt->close();
$conn->close();
