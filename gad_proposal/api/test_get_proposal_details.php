<?php
// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start a session before including the API file
session_start();
// Set up a mock session username to bypass authentication
$_SESSION['username'] = 'test_user';

// Set up test parameters
$campus = 'Lipa';
$year = '2025';
$proposal_id = 28; // Use the ID we know exists from our earlier testing

echo "Testing get_proposal_details.php with parameters:\n";
echo "- Campus: {$campus}\n";
echo "- Year: {$year}\n";
echo "- Proposal ID: {$proposal_id}\n\n";

// Set the GET parameters
$_GET['campus'] = $campus;
$_GET['year'] = $year;
$_GET['proposal_id'] = $proposal_id;

// Capture the output using output buffering
ob_start();
// Include the API file to execute it
require_once('get_proposal_details.php');
// Get the output
$response = ob_get_clean();

// Check if the response is valid JSON
$data = json_decode($response, true);
if ($data === null) {
    echo "❌ Error: Invalid JSON response\n";
    echo "Raw response: " . substr($response, 0, 1000) . (strlen($response) > 1000 ? "..." : "") . "\n";
    exit;
}

// Check the status
if (isset($data['status']) && $data['status'] !== 'success') {
    echo "❌ Error: Request failed with status '{$data['status']}'\n";
    echo "Message: " . ($data['message'] ?? 'No error message provided') . "\n";
    exit;
}

echo "✅ Request successful!\n\n";
echo "Data structure:\n";

// Display the JSON response in a formatted way
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); 