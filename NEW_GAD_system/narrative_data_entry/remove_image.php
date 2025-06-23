<?php
// Start session to access session variables
session_start();

// Include database connection (only if constants not defined yet)
if (!defined('DB_HOST')) {
    require_once '../includes/db_connection.php';
}

// Set up error handling for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log errors
function logError($message) {
    error_log("[Image Remove Error] " . $message);
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get parameters from the request
$narrativeId = isset($_POST['narrative_id']) ? intval($_POST['narrative_id']) : 0;
$imagePath = isset($_POST['image_path']) ? $_POST['image_path'] : '';

// Debug logging
error_log("Removing image: " . $imagePath . " from narrative ID: " . $narrativeId);

// Validate inputs
if ($narrativeId <= 0 || empty($imagePath)) {
    echo json_encode(['success' => false, 'message' => 'Invalid narrative ID or image path']);
    exit;
}

try {
    // First get the existing photo paths
    $stmt = $conn->prepare("SELECT photo_path, photo_paths FROM narrative_entries WHERE id = ?");
    $stmt->execute([$narrativeId]);
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Narrative not found']);
        exit;
    }
    
    $existingPath = $row['photo_path'] ?? '';
    $existingPaths = $row['photo_paths'] ? json_decode($row['photo_paths'], true) : [];
    
    // If not an array, initialize as empty array
    if (!is_array($existingPaths)) {
        $existingPaths = [];
    }
    
    // Normalize the path to remove
    $pathToRemove = $imagePath;
    
    // Handle potential path prefixes
    if (strpos($pathToRemove, 'photos/') === false && strpos($pathToRemove, '../photos/') === false) {
        // Might be just a filename - try to find it with potential prefixes
        foreach ($existingPaths as $index => $path) {
            if (strpos($path, $pathToRemove) !== false) {
                $pathToRemove = $path;
                break;
            }
        }
    }
    
    // Log the path we're trying to remove
    error_log("Normalized path to remove: " . $pathToRemove);
    
    // Remove the image from the array
    $updatedPaths = array_filter($existingPaths, function($path) use ($pathToRemove) {
        return $path !== $pathToRemove;
    });
    
    // If we're removing the main photo_path, update it to use the first remaining image
    if ($existingPath === $pathToRemove) {
        $mainPhotoPath = !empty($updatedPaths) ? reset($updatedPaths) : '';
    } else {
        $mainPhotoPath = $existingPath;
    }
    
    // Convert back to JSON
    $photoPathsJson = json_encode(array_values($updatedPaths));
    
    // Update the database
    $updateStmt = $conn->prepare("UPDATE narrative_entries SET photo_path = ?, photo_paths = ? WHERE id = ?");
    $updateStmt->execute([$mainPhotoPath, $photoPathsJson, $narrativeId]);
    
    // Try to delete the actual file if it exists
    $filePath = '../photos/' . basename($pathToRemove);
    if (file_exists($filePath)) {
        unlink($filePath);
        error_log("Deleted file: " . $filePath);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Image removed successfully',
        'remaining_images' => $updatedPaths,
        'main_image' => $mainPhotoPath
    ]);
    
} catch (Exception $e) {
    logError("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error removing image: ' . $e->getMessage()
    ]);
}
?> 