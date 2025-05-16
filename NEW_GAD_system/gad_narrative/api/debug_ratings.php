<?php
session_start();
header('Content-Type: application/json');

// Include the database connection
require_once('../../includes/db_connection.php');

// Function to get database connection if include fails
if (!function_exists('getConnection')) {
    function getConnection() {
        try {
            $conn = new PDO(
                "mysql:host=localhost;dbname=gad_db;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            return $conn;
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
}

try {
    // Get parameters from request
    $ppasFormId = isset($_GET['ppas_form_id']) ? $_GET['ppas_form_id'] : null;
    
    // Validate required parameters
    if (!$ppasFormId) {
        echo json_encode([
            'status' => 'error',
            'message' => 'PPAS Form ID is required',
            'code' => 'MISSING_PARAM'
        ]);
        exit();
    }

    // Get database connection
    $conn = isset($pdo) ? $pdo : getConnection();
    
    // Query to get raw ratings data
    $stmt = $conn->prepare("
        SELECT ne.id, ne.title, ne.activity_ratings, ne.timeliness_ratings
        FROM narrative_entries ne
        JOIN ppas_forms pf ON ne.ppas_form_id = pf.id OR ne.title LIKE CONCAT('%', pf.activity, '%')
        WHERE pf.id = :ppas_id
        LIMIT 1
    ");
    
    $stmt->execute([':ppas_id' => $ppasFormId]);
    $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$narrativeData) {
        // Try a direct lookup by ID
        $stmt = $conn->prepare("SELECT id, title, activity_ratings, timeliness_ratings FROM narrative_entries WHERE ppas_form_id = :ppas_id LIMIT 1");
        $stmt->execute([':ppas_id' => $ppasFormId]);
        $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$narrativeData) {
        // Check PPAS data to get title for searching
        $stmt = $conn->prepare("SELECT activity FROM ppas_forms WHERE id = :ppas_id LIMIT 1");
        $stmt->execute([':ppas_id' => $ppasFormId]);
        $ppasData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ppasData && !empty($ppasData['activity'])) {
            // Try to find by activity title
            $stmt = $conn->prepare("SELECT id, title, activity_ratings, timeliness_ratings FROM narrative_entries WHERE title LIKE :title LIMIT 1");
            $stmt->execute([':title' => '%' . $ppasData['activity'] . '%']);
            $narrativeData = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    // Prepare the response
    $response = [
        'status' => 'success',
        'ppas_id' => $ppasFormId,
        'found' => $narrativeData ? true : false,
        'ratings_data' => null
    ];
    
    if ($narrativeData) {
        $response['narrative_id'] = $narrativeData['id'];
        $response['narrative_title'] = $narrativeData['title'];
        
        // Process activity_ratings
        $activityRatings = $narrativeData['activity_ratings'];
        if (!empty($activityRatings)) {
            if (is_string($activityRatings)) {
                $response['activity_ratings_raw'] = $activityRatings;
                $activityRatingsParsed = json_decode($activityRatings, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $response['activity_ratings'] = $activityRatingsParsed;
                } else {
                    $response['activity_ratings_error'] = 'Failed to parse JSON: ' . json_last_error_msg();
                }
            } else {
                $response['activity_ratings'] = $activityRatings;
            }
        }
        
        // Process timeliness_ratings
        $timelinessRatings = $narrativeData['timeliness_ratings'];
        if (!empty($timelinessRatings)) {
            if (is_string($timelinessRatings)) {
                $response['timeliness_ratings_raw'] = $timelinessRatings;
                $timelinessRatingsParsed = json_decode($timelinessRatings, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $response['timeliness_ratings'] = $timelinessRatingsParsed;
                } else {
                    $response['timeliness_ratings_error'] = 'Failed to parse JSON: ' . json_last_error_msg();
                }
            } else {
                $response['timeliness_ratings'] = $timelinessRatings;
            }
        }
    }
    
    // Return the data
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Log the error and return an error response
    error_log("Error in debug_ratings.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error getting ratings data: ' . $e->getMessage(),
        'code' => 'SERVER_ERROR'
    ]);
} 