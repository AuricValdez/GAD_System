<?php
require_once '../config.php';
session_start();

$response = ['success' => false, 'message' => ''];

try {
    // Get the logged-in user from session
    $userCampus = isset($_SESSION['username']) ? $_SESSION['username'] : '';
    $isCentralUser = ($userCampus === 'Central');
    
    error_log("Delete - Session username: " . $userCampus);
    error_log("Delete - Is Central user: " . ($isCentralUser ? "Yes" : "No"));
    
    // Get JSON data from request body
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    
    error_log("Delete request received: " . print_r($data, true));

    if (empty($data['campus']) || empty($data['year'])) {
        $response['message'] = 'Campus and year are required';
    } else {
        $campus = $data['campus'];
        
        $year = intval($data['year']);

        error_log("Attempting to delete target - Campus: $campus, Year: $year");

        // First verify the target exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM target WHERE campus = ? AND year = ?");
        $checkStmt->execute([$campus, $year]);
        $exists = $checkStmt->fetchColumn();

        error_log("Target exists check result: " . ($exists ? 'Yes' : 'No'));

        if ($exists) {
            // Delete the target
            $stmt = $pdo->prepare("DELETE FROM target WHERE campus = ? AND year = ?");
            if ($stmt->execute([$campus, $year])) {
                // Verify deletion
                $verifyStmt = $pdo->prepare("SELECT COUNT(*) FROM target WHERE campus = ? AND year = ?");
                $verifyStmt->execute([$campus, $year]);
                $stillExists = $verifyStmt->fetchColumn();

                if ($stillExists == 0) {
                    $response['success'] = true;
                    $response['message'] = "Target for {$campus} campus and year {$year} deleted successfully";
                    error_log("Successfully deleted target");
                } else {
                    $response['message'] = "Failed to delete target - record still exists";
                    error_log("Failed to delete target - record still exists");
                }
            } else {
                $error = $stmt->errorInfo();
                error_log("Delete Error: " . print_r($error, true));
                $response['message'] = 'Failed to delete target';
            }
        } else {
            $response['message'] = "Target for {$campus} campus and year {$year} not found";
            error_log("Target not found for deletion");
        }
    }
} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $response['message'] = 'Database error occurred';
}

header('Content-Type: application/json');
echo json_encode($response);