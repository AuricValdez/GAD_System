<?php
require_once '../config.php';
session_start();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data from request body
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    try {
        // Get the logged-in user from session
        $userCampus = isset($_SESSION['username']) ? $_SESSION['username'] : '';
        $isCentralUser = ($userCampus === 'Central');
        
        error_log("Session username: " . $userCampus);
        error_log("Is Central user: " . ($isCentralUser ? "Yes" : "No"));
        
        // Log incoming data
        error_log("Received data: " . print_r($data, true));
        error_log("Raw JSON data: " . $jsonData);

        // Validate required fields
        if (empty($data['campus'])) {
            $response['message'] = 'Campus is required';
            error_log("Error: Campus is empty or null");
        } elseif (empty($data['year'])) {
            $response['message'] = 'Year is required';
            error_log("Error: Year is empty or null");
        } elseif (empty($data['total_gaa'])) {
            $response['message'] = 'Total GAA is required';
            error_log("Error: Total GAA is empty or null");
        } else {
            // Convert values to appropriate types
            $campus = trim($data['campus']);
            
            $year = intval($data['year']);
            $total_gaa = str_replace(',', '', $data['total_gaa']);
            $total_gad_fund = str_replace(',', '', $data['total_gad_fund']);

            // Convert to float after removing commas
            $total_gaa = floatval($total_gaa);
            $total_gad_fund = floatval($total_gad_fund);

            // Log processed values
            error_log("Processing values - Campus: '$campus', Year: $year, GAA: $total_gaa, GAD: $total_gad_fund");
            error_log("Campus value type: " . gettype($campus) . ", Length: " . strlen($campus));

            // First check if a target already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM target WHERE campus = ? AND year = ?");
            $checkStmt->execute([$campus, $year]);
            $exists = $checkStmt->fetchColumn() > 0;

            if ($exists) {
                $response['message'] = "Target for {$campus} campus and year {$year} already exists";
            } else {
                // Begin transaction
                $pdo->beginTransaction();
                
                try {
                    // Insert the new target
                    $stmt = $pdo->prepare("INSERT INTO target (campus, year, total_gaa, total_gad_fund) VALUES (?, ?, ?, ?)");
                    $result = $stmt->execute([$campus, $year, $total_gaa, $total_gad_fund]);
                    
                    if ($result) {
                        $pdo->commit();
                        $response['success'] = true;
                        $response['message'] = 'Target added successfully';
                        error_log("Target added successfully - Campus: $campus, Year: $year");
                    } else {
                        $pdo->rollBack();
                        $error = $stmt->errorInfo();
                        error_log("Insert Error: " . print_r($error, true));
                        $response['message'] = 'Failed to add target: ' . $error[2];
                    }
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    error_log("Transaction Error: " . $e->getMessage());
                    throw $e;
                }
            }
        }
    } catch(PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        $response['message'] = 'Database error occurred: ' . $e->getMessage();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
