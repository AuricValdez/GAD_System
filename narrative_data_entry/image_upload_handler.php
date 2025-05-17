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
    error_log("[Image Upload Error] " . $message);
}

// Debug received request
logError("Upload request received");
logError("POST data: " . print_r($_POST, true));
if (isset($_FILES['images'])) {
    logError("Files received: " . count($_FILES['images']['name']));
    foreach($_FILES['images']['name'] as $index => $name) {
        logError("File $index: $name, size: " . $_FILES['images']['size'][$index] . ", type: " . $_FILES['images']['type'][$index]);
    }
} else {
    logError("No files received in the request");
}

// Check if this is a request to clear the temporary uploads
if (isset($_POST['clear_temp']) && $_POST['clear_temp'] === 'true') {
    // Clear the temporary uploads from session
    if (isset($_SESSION['temp_narrative_uploads'])) {
        unset($_SESSION['temp_narrative_uploads']);
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Temporary uploads cleared',
        'images' => [],
        'image_count' => 0
    ]);
    exit;
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if files were uploaded
if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
    echo json_encode(['success' => false, 'message' => 'No images uploaded']);
    exit;
}

// Get narrative ID from the request
$narrativeId = isset($_POST['narrative_id']) ? intval($_POST['narrative_id']) : 0;
$campus = isset($_POST['campus']) ? $_POST['campus'] : '';
$clearPrevious = isset($_POST['clear_previous']) && $_POST['clear_previous'] === 'true';

// If clear_previous flag is set, clear existing temporary uploads
if ($clearPrevious && isset($_SESSION['temp_narrative_uploads'])) {
    unset($_SESSION['temp_narrative_uploads']);
    logError("Cleared previous temporary uploads");
}

// Create upload directory if it doesn't exist
$uploadDir = '../photos/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit;
    }
}

// Process uploaded files
$uploadedImages = [];
$errors = [];
$timestamp = time(); // Use timestamp to make filenames unique

// Create a temporary session key to track this upload batch
$uploadBatchKey = 'upload_batch_' . $timestamp;
$_SESSION[$uploadBatchKey] = [];

// Process each uploaded file
foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
    // Skip empty entries
    if (empty($tmp_name) || empty($_FILES['images']['name'][$key])) {
        logError("Empty file entry at index $key - skipping");
        continue;
    }

    // Check if the file was uploaded successfully
    if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
        $errorMessage = "Error uploading file: " . $_FILES['images']['name'][$key] . " - ";
        
        // Get more detailed error message
        switch ($_FILES['images']['error'][$key]) {
            case UPLOAD_ERR_INI_SIZE:
                $errorMessage .= "File exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $errorMessage .= "File exceeds the MAX_FILE_SIZE directive in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMessage .= "File was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMessage .= "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMessage .= "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errorMessage .= "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $errorMessage .= "File upload stopped by extension";
                break;
            default:
                $errorMessage .= "Unknown upload error";
        }
        
        logError($errorMessage);
        $errors[] = $errorMessage;
        continue;
    }
    
    // Generate a unique filename - use original extension if possible
    $originalName = $_FILES['images']['name'][$key];
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    if (empty($extension)) {
        $extension = 'jpeg'; // Default to jpeg if no extension
    }
    
    $filename = 'narrative_' . $timestamp . '_' . $key . '.' . $extension;
    $targetPath = $uploadDir . $filename;
    
    logError("Processing file $key: $originalName -> $filename");
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    if (!in_array($_FILES['images']['type'][$key], $allowedTypes)) {
        $errorMessage = "Invalid file type: " . $_FILES['images']['name'][$key] . " (" . $_FILES['images']['type'][$key] . ")";
        logError($errorMessage);
        $errors[] = $errorMessage;
        continue;
    }
    
    // Check file size (limit to 50MB - increased from 5MB)
    if ($_FILES['images']['size'][$key] > 50 * 1024 * 1024) {
        $errorMessage = "File too large: " . $_FILES['images']['name'][$key] . " (" . round($_FILES['images']['size'][$key] / 1024 / 1024, 2) . " MB)";
        logError($errorMessage);
        $errors[] = $errorMessage;
        continue;
    }
    
    // Move the uploaded file to the target path
    if (move_uploaded_file($tmp_name, $targetPath)) {
        $uploadedImages[] = $filename;
        
        // Store in session for this batch
        $_SESSION[$uploadBatchKey][] = $filename;
        logError("Successfully uploaded: " . $_FILES['images']['name'][$key] . " as " . $filename);
    } else {
        $errorMessage = "Failed to move uploaded file: " . $_FILES['images']['name'][$key] . " to " . $targetPath;
        logError($errorMessage);
        $errors[] = $errorMessage;
    }
}

// After processing all files, check if we have uploads or errors
$hasUploads = !empty($uploadedImages);
$hasErrors = !empty($errors);

// Log upload results
logError("Upload results: " . count($uploadedImages) . " successful, " . count($errors) . " failed");

// Get existing images if we have a narrative ID
$existingImages = [];
if ($narrativeId > 0) {
    try {
        $stmt = $conn->prepare("SELECT photo_path, photo_paths FROM narrative_entries WHERE id = ?");
        $stmt->execute([$narrativeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            // Get existing main photo path
            $existingPath = $row['photo_path'];
            
            // Get existing photo paths array
            $existingPaths = $row['photo_paths'] ? json_decode($row['photo_paths'], true) : [];
            if (!is_array($existingPaths)) {
                $existingPaths = [];
            }
            
            // Add the existing main path if not already in the array
            if ($existingPath && !in_array($existingPath, $existingPaths)) {
                $existingPaths[] = $existingPath;
            }
            
            $existingImages = $existingPaths;
        }
    } catch (Exception $e) {
        logError("Error fetching existing images: " . $e->getMessage());
    }
}

// If no images were uploaded at all, return an error with existing images
if (!$hasUploads) {
    $response = [
        'success' => false, 
        'message' => 'No images were uploaded successfully', 
        'errors' => $errors,
        'existing_images' => $existingImages // Return existing images so frontend can still display them
    ];
    logError("No successful uploads. Response: " . json_encode($response));
    echo json_encode($response);
    exit;
}

// Prepare new image paths with photos/ prefix for consistency
$pathsWithPrefix = array_map(function($path) {
    return 'photos/' . $path;
}, $uploadedImages);

// If we have a narrative ID, update the database entry with the new images
if ($narrativeId > 0) {
    try {
        // FIXED: Merge with existing images instead of replacing them
        $allImagePaths = array_merge($existingImages, $pathsWithPrefix);
        
        // Remove any duplicates to ensure each image appears only once
        $allImagePaths = array_unique($allImagePaths);
        
        // Update the database with the new paths
        $photoPathsJson = json_encode($allImagePaths);
        $mainPhotoPath = $allImagePaths[0] ?? ''; // Use the first image as the main photo
        
        $stmt = $conn->prepare("UPDATE narrative_entries SET photo_path = ?, photo_paths = ? WHERE id = ?");
        $stmt->execute([$mainPhotoPath, $photoPathsJson, $narrativeId]);
        
        // Return success with the complete list of paths
        $response = [
            'success' => true, 
            'message' => $hasErrors ? 'Some images were uploaded successfully' : 'All images uploaded successfully', 
            'images' => $allImagePaths,
            'image_count' => count($allImagePaths),
            'narrative_id' => $narrativeId
        ];
        
        // Add warnings for files that failed
        if ($hasErrors) {
            $response['warnings'] = $errors;
        }
        
        logError("Response for existing narrative: " . json_encode($response));
        echo json_encode($response);
        exit;
    } catch (Exception $e) {
        logError("Database error: " . $e->getMessage());
        $response = [
            'success' => false, 
            'message' => 'Database error: ' . $e->getMessage(),
            'narrative_id' => $narrativeId,
            'existing_images' => $existingImages  // Return existing images in case of error
        ];
        logError("Error response: " . json_encode($response));
        echo json_encode($response);
        exit;
    }
} else {
    // For new narratives (no ID yet), just store the paths in the session
    // We'll use a unique session key to track uploads for this form
    if (!isset($_SESSION['temp_narrative_uploads'])) {
        $_SESSION['temp_narrative_uploads'] = [];
    }
    
    // FIXED: Don't clear previous uploads, MERGE with existing uploads
    // Get existing uploads
    $existingUploads = $_SESSION['temp_narrative_uploads'];
    
    // Merge with new uploads, avoiding duplicates
    $allUploads = $existingUploads;
    foreach ($pathsWithPrefix as $newPath) {
        if (!in_array($newPath, $allUploads)) {
            $allUploads[] = $newPath;
        }
    }
    
    // Store the merged list back in session
    $_SESSION['temp_narrative_uploads'] = $allUploads;
    
    // Return success with ALL images, not just the new ones
    $response = [
        'success' => true, 
        'message' => $hasErrors ? 'Some images were uploaded successfully' : 'All images uploaded successfully',
        'images' => $allUploads,
        'image_count' => count($allUploads),
        'narrative_id' => 0
    ];
    
    // Add warnings for files that failed
    if ($hasErrors) {
        $response['warnings'] = $errors;
    }
    
    logError("Response for new narrative: " . json_encode($response));
    echo json_encode($response);
    exit;
}
?> 