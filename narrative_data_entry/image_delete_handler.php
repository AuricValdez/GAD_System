<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Extract data
$narrativeId = isset($data['narrative_id']) ? intval($data['narrative_id']) : 0;
$imagePath = isset($data['image_path']) ? $data['image_path'] : '';

// Ensure we have an image path
if (empty($imagePath)) {
    echo json_encode(['success' => false, 'message' => 'No image path provided']);
    exit();
}

try {
    // Extract just the filename from the path
    $filename = basename($imagePath);
    
    // For existing narratives, update database
    if ($narrativeId > 0) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        
        // Get current photo paths array
        $query = "SELECT photo_path FROM narrative_entries WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $narrativeId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $existingPhotoPath = $row['photo_path'];
            
            // Parse existing photo paths
            $photoPathsArray = [];
            if (!empty($existingPhotoPath)) {
                if (substr($existingPhotoPath, 0, 1) === '[') {
                    // JSON format (new)
                    $photoPathsArray = json_decode($existingPhotoPath, true) ?: [];
                } else {
                    // Single path (old)
                    $photoPathsArray = [$existingPhotoPath];
                }
            }
            
            // Remove the requested image
            $photoPathsArray = array_filter($photoPathsArray, function($path) use ($filename) {
                return basename($path) !== $filename;
            });
            
            // Re-index array after filtering
            $photoPathsArray = array_values($photoPathsArray);
            
            // Update the database
            $photoPath = json_encode($photoPathsArray);
            
            $query = "UPDATE narrative_entries SET photo_path = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $photoPath, $narrativeId);
            
            if (!$stmt->execute()) {
                throw new Exception("Error updating record: " . $stmt->error);
            }
            
            // Try to delete the file from the server if it exists
            $filePath = 'photos/' . $filename;
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    error_log("Warning: Could not delete file $filePath");
                    // This is not a fatal error, just log it
                }
            }
            
            // Return the updated image paths
            echo json_encode([
                'success' => true, 
                'message' => 'Image deleted successfully',
                'images' => $photoPathsArray
            ]);
        } else {
            throw new Exception("Narrative not found");
        }
    } else {
        // For new narratives, update session storage
        if (isset($_SESSION['temp_photos']) && is_array($_SESSION['temp_photos'])) {
            // Filter out the deleted image
            $_SESSION['temp_photos'] = array_filter($_SESSION['temp_photos'], function($path) use ($filename) {
                return basename($path) !== $filename;
            });
            
            // Re-index array after filtering
            $_SESSION['temp_photos'] = array_values($_SESSION['temp_photos']);
            
            // Try to delete the file from the server if it exists
            $filePath = 'photos/' . $filename;
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    error_log("Warning: Could not delete file $filePath");
                    // This is not a fatal error, just log it
                }
            }
            
            // Return the updated image paths
            echo json_encode([
                'success' => true, 
                'message' => 'Image deleted successfully',
                'images' => $_SESSION['temp_photos']
            ]);
        } else {
            throw new Exception("No temporary photos found in session");
        }
    }
} catch (Exception $e) {
    error_log("Error in image_delete_handler.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 