<?php
require_once '../config.php';
session_start();

$response = ['success' => false, 'message' => ''];

try {
    // Get the logged-in user from session
    $userCampus = isset($_SESSION['username']) ? $_SESSION['username'] : '';
    $isCentralUser = ($userCampus === 'Central');
    
    error_log("Update - Session username: " . $userCampus);
    error_log("Update - Is Central user: " . ($isCentralUser ? "Yes" : "No"));
    
    // Get JSON data from request body
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    error_log("Update request received: " . print_r($data, true));
    error_log("Raw JSON data for update: " . $jsonData);

    // Validate required fields
    if (empty($data['campus'])) {
        $response['message'] = 'Campus is required';
        error_log("Update Error: Campus is empty or null");
    } elseif (empty($data['year'])) {
        $response['message'] = 'Year is required';
        error_log("Update Error: Year is empty or null");
    } elseif (!isset($data['total_gaa']) || $data['total_gaa'] === '') {
        $response['message'] = 'Total GAA is required';
        error_log("Update Error: Total GAA is empty or null");
    } else {
        // Convert values to appropriate types
        $campus = trim($data['campus']);
        
        $year = intval($data['year']);
        
        // Remove leading zeros and commas from total_gaa
        $total_gaa = preg_replace('/^0+/', '', str_replace(',', '', $data['total_gaa']));
        if ($total_gaa === '') $total_gaa = '0';
        $total_gaa = floatval($total_gaa);
        
        $total_gad_fund = floatval(str_replace(',', '', $data['total_gad_fund']));

        error_log("Updating target - Campus: $campus, Year: $year, GAA: $total_gaa, GAD: $total_gad_fund");

        // Begin transaction
        $pdo->beginTransaction();

        try {
            // First check if the target exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM target WHERE campus = ? AND year = ?");
            $checkStmt->execute([$campus, $year]);
            $exists = $checkStmt->fetchColumn() > 0;

            if ($exists) {
                // Update existing target
                $stmt = $pdo->prepare("UPDATE target SET total_gaa = ?, total_gad_fund = ? WHERE campus = ? AND year = ?");
                $result = $stmt->execute([$total_gaa, $total_gad_fund, $campus, $year]);
            } else {
                // Insert new target
                $stmt = $pdo->prepare("INSERT INTO target (campus, year, total_gaa, total_gad_fund) VALUES (?, ?, ?, ?)");
                $result = $stmt->execute([$campus, $year, $total_gaa, $total_gad_fund]);
            }

            if ($result) {
                $pdo->commit();
                $response['success'] = true;
                $response['message'] = $exists 
                    ? "Target for {$campus} campus and year {$year} updated successfully"
                    : "Target for {$campus} campus and year {$year} created successfully";
                error_log($response['message']);
            } else {
                $pdo->rollBack();
                error_log("Failed to save target");
                $response['message'] = "Failed to save target for {$campus} campus and year {$year}";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Update Error: " . $e->getMessage());
            throw $e;
        }
    }
} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $response['message'] = 'Database error occurred: ' . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);