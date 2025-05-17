<?php
// This is a bridge file to maintain compatibility with existing frontend code
// Simply forward the request to get_gad_proposal.php

// Get the ID parameter
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    // Redirect to get_gad_proposal.php with the same ID
    require_once 'get_gad_proposal.php';
} else {
    // Return error if no ID provided
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No proposal ID provided'
    ]);
}
?> 