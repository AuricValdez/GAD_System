<?php
session_start();

// Debug session information
error_log("Session data in ppas.php: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in - redirecting to login");
    header("Location: ../login.php");
    exit();
}

$isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';

// Include database configuration
require_once '../config.php';

// Get the logged-in user's campus (username)
$userCampus = $_SESSION['username'];

// Fetch distinct years from ppas_forms filtered by campus
$years = array();
$sql = "SELECT DISTINCT year FROM ppas_forms WHERE campus = ? ORDER BY year DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userCampus);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $years[] = $row["year"];
    }
}

// Close the statement
$stmt->close();

// Convert years array to JSON for JavaScript use
$yearsJson = json_encode($years);

/**
 * Fetch narratives from the database with pagination and filtering
 */
function fetchNarratives($activityFilter = '', $campusFilter = '')
{
    global $conn;

    try {
        // Base query to join narrative table with ppas_forms table
        $sql = "SELECT n.id, n.partner_agency, n.campus, p.activity 
                FROM narrative n 
                INNER JOIN ppas_forms p ON n.ppas_form_id = p.id 
                WHERE 1=1";

        $params = array();

        // Add activity filter if provided
        if (!empty($activityFilter)) {
            $sql .= " AND p.activity LIKE ?";
            $params[] = "%$activityFilter%";
        }

        // Add campus filter if provided
        if (!empty($campusFilter)) {
            $sql .= " AND n.campus = ?";
            $params[] = $campusFilter;
        }

        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);

        if (!empty($params)) {
            // Dynamically bind parameters
            $types = str_repeat('s', count($params)); // Assume all are strings for simplicity
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $narratives = array();
        while ($row = $result->fetch_assoc()) {
            $narratives[] = $row;
        }

        $stmt->close();
        return $narratives;
    } catch (Exception $e) {
        error_log("Error fetching narratives: " . $e->getMessage());
        return array();
    }
}

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create an API endpoint to fetch narrative data via AJAX
if (isset($_GET['action']) && $_GET['action'] === 'get_narratives') {
    header('Content-Type: application/json');

    $activityFilter = isset($_GET['activity_filter']) ? $_GET['activity_filter'] : '';
    $campusFilter = isset($_GET['campus_filter']) ? $_GET['campus_filter'] : '';

    $narratives = fetchNarratives($activityFilter, $campusFilter);

    echo json_encode($narratives);
    exit();
}

/**
 * Delete a narrative and its associated images
 */
function deleteNarrativeWithImages($id)
{
    global $conn;

    try {
        // First get the narrative to find the associated images
        $sql = "SELECT activity_images FROM narrative WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Delete associated images if they exist
            if (!empty($row['activity_images'])) {
                $images = json_decode($row['activity_images'], true);

                if (is_array($images)) {
                    foreach ($images as $image) {
                        $imagePath = '../narrative_images/' . $image;
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                            error_log("Deleted image: " . $imagePath);
                        }
                    }
                }
            }

            // Now delete the narrative record
            $sql = "DELETE FROM narrative WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();

            if ($success) {
                return ['success' => true, 'message' => 'Narrative and associated images deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete narrative: ' . $stmt->error];
            }
        } else {
            return ['success' => false, 'message' => 'Narrative not found'];
        }
    } catch (Exception $e) {
        error_log("Error deleting narrative: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Create an API endpoint to handle narrative deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete_narrative' && isset($_GET['id'])) {
    header('Content-Type: application/json');

    $id = intval($_GET['id']);
    $result = deleteNarrativeWithImages($id);

    echo json_encode($result);
    exit();
}

/**
 * Get narrative details for editing
 */
function getNarrativeDetails($id)
{
    global $conn;

    try {
        // Query to fetch narrative and related form data
        $sql = "SELECT n.*, p.activity, p.campus as form_campus, p.year, p.quarter, p.project, p.program 
                FROM narrative n 
                INNER JOIN ppas_forms p ON n.ppas_form_id = p.id 
                WHERE n.id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return ['success' => true, 'data' => $row];
        } else {
            return ['success' => false, 'message' => 'Narrative not found'];
        }
    } catch (Exception $e) {
        error_log("Error fetching narrative details: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// API endpoint to get narrative details for editing
if (isset($_GET['action']) && $_GET['action'] === 'get_narrative_details' && isset($_GET['id'])) {
    header('Content-Type: application/json');

    $id = intval($_GET['id']);
    $result = getNarrativeDetails($id);

    echo json_encode($result);
    exit();
}

/**
 * Update an existing narrative and its associated images
 */
function updateNarrative($narrativeId, $data, $files, $existingImages)
{
    global $conn;

    try {
        // Start transaction
        $conn->begin_transaction();

        // First get current narrative data to check what needs updating
        $sql = "SELECT * FROM narrative WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $narrativeId);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$row = $result->fetch_assoc()) {
            return ['success' => false, 'message' => 'Narrative not found'];
        }

        // Extract data from the request
        $ppasFormId = isset($data['ppas_form_id']) ? intval($data['ppas_form_id']) : 0;
        $implementingOffice = isset($data['implementing_office']) ? $data['implementing_office'] : '[]';
        $partnerAgency = isset($data['partner_agency']) ? $data['partner_agency'] : '';
        $extensionServiceAgenda = isset($data['extension_service_agenda']) ? $data['extension_service_agenda'] : '[]';
        $typeBeneficiaries = isset($data['type_beneficiaries']) ? $data['type_beneficiaries'] : '';
        $beneficiaryDistribution = isset($data['beneficiary_distribution']) ? $data['beneficiary_distribution'] : '{}';
        $teamTasks = isset($data['team_tasks']) ? $data['team_tasks'] : '{}';
        $activityNarrative = isset($data['activity_narrative']) ? $data['activity_narrative'] : '';
        $activityRatings = isset($data['activity_ratings']) ? $data['activity_ratings'] : '{}';
        $timelinessRatings = isset($data['timeliness_ratings']) ? $data['timeliness_ratings'] : '{}';

        // Process team tasks
        $decodedTasks = json_decode($teamTasks, true);
        $leaderTasks = [];
        $assistantTasks = [];
        $staffTasks = [];

        if (is_array($decodedTasks)) {
            if (isset($decodedTasks['projectLeader']) && is_array($decodedTasks['projectLeader'])) {
                foreach ($decodedTasks['projectLeader'] as $task) {
                    if (isset($task['task']) && !empty($task['task'])) {
                        $leaderTasks[] = $task['task'];
                    }
                }
            }

            if (isset($decodedTasks['assistantLeader']) && is_array($decodedTasks['assistantLeader'])) {
                foreach ($decodedTasks['assistantLeader'] as $task) {
                    if (isset($task['task']) && !empty($task['task'])) {
                        $assistantTasks[] = $task['task'];
                    }
                }
            }

            if (isset($decodedTasks['projectStaff']) && is_array($decodedTasks['projectStaff'])) {
                foreach ($decodedTasks['projectStaff'] as $task) {
                    if (isset($task['task']) && !empty($task['task'])) {
                        $staffTasks[] = $task['task'];
                    }
                }
            }
        }

        $leaderTasks = json_encode($leaderTasks);
        $assistantTasks = json_encode($assistantTasks);
        $staffTasks = json_encode($staffTasks);

        // Handle image uploads and existing images
        $currentImages = json_decode($row['activity_images'] ?? '[]', true);
        $keepImages = !empty($existingImages) ? json_decode($existingImages, true) : [];

        // Process existing images - determine which ones to keep
        $imagesToKeep = array_intersect($currentImages, $keepImages);

        // Process new image uploads
        $uploadDir = '../narrative_images/';
        $newImages = [];

        foreach ($files as $key => $file) {
            if (strpos($key, 'image_') === 0 && !empty($file['tmp_name'])) {
                $fileName = 'narrative_' . $ppasFormId . '_' . time() . '_' . rand(1000, 9999) . '.jpg';
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $newImages[] = $fileName;
                }
            }
        }

        // Combine kept and new images
        $finalImages = array_merge($imagesToKeep, $newImages);
        $imageJson = json_encode($finalImages);

        // Delete removed images
        $imagesToDelete = array_diff($currentImages, $keepImages);
        foreach ($imagesToDelete as $image) {
            $imagePath = $uploadDir . $image;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Update the narrative record
        $sql = "UPDATE narrative SET 
                ppas_form_id = ?, 
                implementing_office = ?, 
                partner_agency = ?, 
                extension_service_agenda = ?, 
                type_beneficiaries = ?, 
                beneficiary_distribution = ?, 
                leader_tasks = ?,
                assistant_tasks = ?,
                staff_tasks = ?,
                activity_narrative = ?, 
                activity_ratings = ?, 
                timeliness_ratings = ?, 
                activity_images = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "issssssssssssi",
            $ppasFormId,
            $implementingOffice,
            $partnerAgency,
            $extensionServiceAgenda,
            $typeBeneficiaries,
            $beneficiaryDistribution,
            $leaderTasks,
            $assistantTasks,
            $staffTasks,
            $activityNarrative,
            $activityRatings,
            $timelinessRatings,
            $imageJson,
            $narrativeId
        );

        $success = $stmt->execute();

        if (!$success) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Failed to update narrative: ' . $stmt->error];
        }

        // Commit the transaction
        $conn->commit();

        return ['success' => true, 'message' => 'Narrative updated successfully'];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating narrative: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function activityHasNarrative($activityId)
{
    global $conn;

    $sql = "SELECT COUNT(*) as count FROM narrative WHERE activity_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $activityId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['count'] > 0;
}

// Handle update narrative request
if (basename($_SERVER['PHP_SELF']) === 'update_narrative.php') {
    session_start();

    // Debug logging
    function debug_log($message)
    {
        error_log("[update_narrative] " . $message);
    }

    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        debug_log("User not logged in");
        echo json_encode(['success' => false, 'message' => 'Not authorized']);
        exit();
    }

    // Include database configuration
    require_once '../config.php';

    // Start output buffering to prevent any unwanted output
    ob_start();

    try {
        // Connect to database
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception('Database connection failed: ' . $conn->connect_error);
        }

        // Get narrative ID
        $narrativeId = isset($_POST['narrative_id']) ? intval($_POST['narrative_id']) : 0;
        if ($narrativeId <= 0) {
            throw new Exception('Invalid narrative ID');
        }

        // Process existing images
        $existingImages = isset($_POST['existing_images']) ? $_POST['existing_images'] : '[]';

        // Update the narrative
        $result = updateNarrative($narrativeId, $_POST, $_FILES, $existingImages);

        // Clean up
        ob_end_clean();

        // Return response
        echo json_encode($result);
    } catch (Exception $e) {
        debug_log("Error: " . $e->getMessage());

        // Clean up
        ob_end_clean();

        // Return error response
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    exit();
}

// End of PHP script for narrative AJAX handlers
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Narrative Forms - GAD System</title>
    <link rel="icon" type="image/x-icon" href="../images/Batangas_State_Logo.ico">
    <script src="../js/common.js"></script>
    <!-- Immediate theme loading to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = savedTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        })();
    </script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 for enhanced dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --accent-color: #6a1b9a;
            --accent-hover: #4a148c;
            --accent-color-rgb: 106, 27, 154;
            --readonly-bg: #e9ecef;
            --readonly-border: #dee2e6;
            --readonly-text: #6c757d;
        }

        /* Light Theme Variables */
        [data-bs-theme="light"] {
            --bg-primary: #f0f0f0;
            --bg-secondary: #e9ecef;
            --sidebar-bg: #ffffff;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --hover-color: rgba(106, 27, 154, 0.1);
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --horizontal-bar: rgba(33, 37, 41, 0.125);
            --input-placeholder: rgba(33, 37, 41, 0.75);
            --input-bg: #ffffff;
            --input-text: #212529;
            --card-title: #212529;
            --scrollbar-thumb: rgba(156, 39, 176, 0.4);
            --scrollbar-thumb-hover: rgba(156, 39, 176, 0.7);
        }

        /* Dark Theme Variables */
        [data-bs-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --sidebar-bg: #2d2d2d;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --hover-color: #8a4ebd;
            --card-bg: #2d2d2d;
            --border-color: #404040;
            --horizontal-bar: rgba(255, 255, 255, 0.1);
            --input-placeholder: rgba(255, 255, 255, 0.7);
            --input-bg: #404040;
            --input-text: #ffffff;
            --card-title: #ffffff;
            --scrollbar-thumb: #6a1b9a;
            --scrollbar-thumb-hover: #9c27b0;
            --accent-color: #9c27b0;
            --accent-hover: #7b1fa2;
            --accent-color-rgb: 156, 39, 176;
            --readonly-bg: #37383A;
            --readonly-border: #495057;
            --readonly-text: #adb5bd;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
            opacity: 1;
            transition: opacity 0.05s ease-in-out;
            /* Changed from 0.05s to 0.01s - make it super fast */
        }

        body.fade-out {
            opacity: 0;
        }



        .sidebar {
            width: var(--sidebar-width);
            height: calc(100vh - 40px);
            position: fixed;
            left: 20px;
            top: 20px;
            padding: 20px;
            background: var(--sidebar-bg);
            color: var(--text-primary);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.05), 0 5px 15px rgba(0, 0, 0, 0.05);
            z-index: 1;
        }

        .main-content {
            margin-left: calc(var(--sidebar-width) + 20px);
            padding: 15px;
            height: calc(100vh - 30px);
            max-height: calc(100vh - 30px);
            background: var(--bg-primary);
            border-radius: 20px;
            position: relative;
            overflow-y: auto;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .main-content::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        body::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for Firefox */
        html {
            scrollbar-width: none;
        }

        .nav-link {
            color: var(--text-primary);
            padding: 12px 15px;
            border-radius: 12px;
            margin-bottom: 5px;
            position: relative;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 12px;
        }

        .nav-link:hover {
            background: var(--hover-color);
            color: white;
        }

        /* Restore light mode hover color */
        [data-bs-theme="light"] .nav-link:hover {
            color: var(--accent-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-menu .dropdown-item:hover {
            color: var(--accent-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--accent-color) !important;
        }

        .nav-link.active {
            color: var(--accent-color);
            position: relative;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: var(--accent-color);
            border-radius: 0 2px 2px 0;
        }

        /* Add hover state for active nav links in dark mode */
        [data-bs-theme="dark"] .nav-link.active:hover {
            color: white;
        }

        .nav-item {
            position: relative;
        }

        .nav-item .dropdown-menu {
            position: static !important;
            background: var(--sidebar-bg);
            border: 1px solid var(--border-color);
            padding: 8px 0;
            margin: 5px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            transform: none !important;
            display: none;
            overflow: visible;
            max-height: none;
        }

        .nav-item .dropdown-menu.show {
            display: block;
        }

        .nav-item .dropdown-menu .dropdown-item {
            padding: 8px 48px;
            color: var(--text-primary);
            position: relative;
            opacity: 0.85;
            background: transparent;
        }

        .nav-item .dropdown-menu .dropdown-item::before {
            content: '•';
            position: absolute;
            left: 35px;
            color: var(--accent-color);
        }

        .nav-item .dropdown-menu .dropdown-item:hover {
            background: var(--hover-color);
            color: white;
            opacity: 1;
        }

        [data-bs-theme="light"] .nav-item .dropdown-menu .dropdown-item:hover {
            color: var(--accent-color);
        }

        .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: white !important;
            background: var(--hover-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--accent-color) !important;
        }

        .logo-container {
            padding: 20px 0;
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-title {
            font-size: 24px;
            font-weight: bold;
            color: var(--text-primary);
            margin-bottom: 15px;
        }

        .logo-image {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            margin-bottom: -25px;
        }

        .logo-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .datetime-container {
            text-align: center;
            padding: 15px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--horizontal-bar);
        }

        .datetime-container .date {
            font-size: 1.1rem;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .datetime-container .time {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--accent-color);
        }

        .nav-content {
            flex-grow: 1;
            overflow-y: auto;
            max-height: calc(100vh - 470px);
            margin-bottom: 20px;
            padding-right: 5px;
            scrollbar-width: thin;
            scrollbar-color: rgba(106, 27, 154, 0.4) transparent;
            overflow-x: hidden;
        }

        .nav-content::-webkit-scrollbar {
            width: 5px;
        }

        .nav-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .nav-content::-webkit-scrollbar-thumb {
            background-color: rgba(106, 27, 154, 0.4);
            border-radius: 1px;
        }

        .nav-content::-webkit-scrollbar-thumb:hover {
            background-color: rgba(106, 27, 154, 0.7);
        }

        .nav-link:focus,
        .dropdown-toggle:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        .dropdown-menu {
            outline: none !important;
            border: none !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .dropdown-item:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        /* Bottom controls container */
        .bottom-controls {
            position: absolute;
            bottom: 20px;
            width: calc(var(--sidebar-width) - 40px);
            display: flex;
            gap: 5px;
            align-items: center;
        }

        /* Logout button styles */
        .logout-button {
            flex: 1;
            background: var(--bg-primary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Theme switch button */
        .theme-switch-button {
            width: 46.5px;
            height: 50px;
            padding: 12px 0;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

        /* Light theme specific styles for bottom controls */
        [data-bs-theme="light"] .logout-button,
        [data-bs-theme="light"] .theme-switch-button {
            background: #f2f2f2;
            border-width: 1.5px;
        }

        /* Hover effects */
        .logout-button:hover,
        .theme-switch-button:hover {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--accent-color);
        }

        .theme-switch {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }

        .theme-switch-button:hover {
            transform: translateY(-2px);
            box-shadow:
                0 8px 12px rgba(0, 0, 0, 0.15),
                0 3px 6px rgba(0, 0, 0, 0.1),
                inset 0 1px 2px rgba(255, 255, 255, 0.2);
        }

        .theme-switch-button:active {
            transform: translateY(0);
            box-shadow:
                0 4px 6px rgba(0, 0, 0, 0.1),
                0 2px 4px rgba(0, 0, 0, 0.06),
                inset 0 1px 2px rgba(255, 255, 255, 0.2);
        }

        /* Theme switch button icon size */
        .theme-switch-button i {
            font-size: 1rem;
        }

        .theme-switch-button:hover i {
            transform: scale(1.1);
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 1.5rem;
        }

        .page-title i {
            color: var(--accent-color);
            font-size: 2.2rem;
        }

        .page-title h2 {
            margin: 0;
            font-weight: 600;
        }

        .show>.nav-link {
            background: transparent !important;
            color: var(--accent-color) !important;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 991px) {
            :root {
                --sidebar-width: 240px;
            }

            body {
                padding: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
                left: 0;
                top: 0;
                height: 100vh;
                position: fixed;
                padding-top: 70px;
                border-radius: 0;
                box-shadow: 5px 0 25px rgba(0, 0, 0, 0.1);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 70px 15px 15px 15px;
                border-radius: 0;
                box-shadow: none;
            }

            .mobile-nav-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
                background: var(--card-bg);
                border: none;
                border-radius: 8px;
                color: var(--text-primary);
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                cursor: pointer;
            }

            .mobile-nav-toggle:hover {
                background: var(--hover-color);
                color: var(--accent-color);
            }

            body.sidebar-open {
                overflow: hidden;
            }

            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            .sidebar-backdrop.show {
                display: block;
            }

            .theme-switch {
                position: fixed;
                bottom: 30px;
                right: 30px;
            }

        }

        @media (max-width: 576px) {
            :root {
                --sidebar-width: 100%;
            }

            .sidebar {
                left: 0;
                top: 0;
                width: 100%;
                height: 100vh;
                padding-top: 60px;
            }

            .mobile-nav-toggle {
                width: 40px;
                height: 40px;
                top: 10px;
                left: 10px;
            }

            .theme-switch {
                top: 10px;
                right: 10px;
            }

            .theme-switch-button {
                padding: 8px 15px;
            }

            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                margin-top: 10px;
            }

            .page-title h2 {
                font-size: 1.5rem;
            }
        }

        /* Modern Card Styles */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            position: relative;
            min-height: 465px;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        #ppasForm {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        #ppasForm.row {
            flex: 1;
        }

        #ppasForm .col-12.text-end {
            margin-top: auto !important;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        /* Dark Theme Colors */
        [data-bs-theme="dark"] {
            --dark-bg: #212529;
            --dark-input: #2b3035;
            --dark-text: #e9ecef;
            --dark-border: #495057;
            --dark-sidebar: #2d2d2d;
        }

        /* Dark mode card */
        [data-bs-theme="dark"] .card {
            background-color: var(--dark-sidebar) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .card-header {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
            overflow: hidden;
        }

        /* Fix for card header corners */
        .card-header {
            border-top-left-radius: inherit !important;
            border-top-right-radius: inherit !important;
            padding-bottom: 0.5rem !important;
        }

        .card-title {
            margin-bottom: 0;
        }

        /* Form Controls */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1 1 200px;
        }


        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 25px;
            margin-bottom: 20px;
        }

        .btn-icon {
            width: 45px;
            height: 45px;
            padding: 0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-icon i {
            font-size: 1.2rem;
        }

        /* Add button */
        #addBtn {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        #addBtn:hover {
            background: #198754;
            color: white;
        }

        /* Edit button */
        #editBtn {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        #editBtn:hover {
            background: #ffc107;
            color: white;
        }

        /* Edit button in cancel mode */
        #editBtn.editing {
            background: rgba(220, 53, 69, 0.1) !important;
            color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        #editBtn.editing:hover {
            background: #dc3545 !important;
            color: white !important;
        }

        /* Delete button */
        #deleteBtn {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        #deleteBtn:hover {
            background: #dc3545;
            color: white;
        }

        /* Delete button disabled state */
        #deleteBtn.disabled {
            background: rgba(108, 117, 125, 0.1) !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        /* Update button state */
        #addBtn.btn-update {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        #addBtn.btn-update:hover {
            background: #198754;
            color: white;
        }

        #viewBtn {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        #viewBtn:hover {
            background: #0d6efd;
            color: white;
        }

        /* Optional: Add disabled state for view button */
        #viewBtn.disabled {
            background: rgba(108, 117, 125, 0.1) !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        /* Add these styles for disabled buttons */
        .btn-disabled {
            border-color: #6c757d !important;
            background: rgba(108, 117, 125, 0.1) !important;
            color: #6c757d !important;
            opacity: 0.65 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        /* Dark mode styles */
        [data-bs-theme="dark"] .btn-disabled {
            background-color: #495057 !important;
            border-color: #495057 !important;
            color: #adb5bd !important;
        }

        .swal-blur-container {
            backdrop-filter: blur(5px);
        }

        /* Dropdown submenu styles */
        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -8px;
            margin-left: 1px;
            border-radius: 0 6px 6px 6px;
            display: none;
        }

        /* Add click-based display */
        .dropdown-submenu.show>.dropdown-menu {
            display: block;
        }

        .dropdown-submenu>a:after {
            display: block;
            content: " ";
            float: right;
            width: 0;
            height: 0;
            border-color: transparent;
            border-style: solid;
            border-width: 5px 0 5px 5px;
            border-left-color: var(--text-primary);
            margin-top: 5px;
            margin-right: -10px;
        }

        /* Update hover effect for arrow */
        .dropdown-submenu.show>a:after {
            border-left-color: var(--accent-color);
        }

        /* Mobile styles for dropdown submenu */
        @media (max-width: 991px) {
            .dropdown-submenu .dropdown-menu {
                position: static !important;
                left: 0;
                margin-left: 20px;
                margin-top: 0;
                border-radius: 0;
                border-left: 2px solid var(--accent-color);
            }

            .dropdown-submenu>a:after {
                transform: rotate(90deg);
                margin-top: 8px;
            }
        }

        /* Form section styles */
        .form-section {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            background-color: var(--bg-surface);
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        /* Dark mode specific styles for form sections */
        [data-bs-theme="dark"] .form-section {
            border-color: var(--border-color);
            background-color: var(--card-bg);
        }

        /* Section validation styles */
        .form-section.complete {
            border-color: #28a745 !important;
            border-width: 2px !important;
            background-color: rgba(40, 167, 69, 0.05);
        }

        /* Dark mode styles for completed sections */
        [data-bs-theme="dark"] .form-section.complete {
            background-color: rgba(40, 167, 69, 0.08);
        }

        .form-section.complete .section-header .section-title,
        .form-section.complete .section-header i {
            color: #28a745 !important;
        }

        /* For incomplete sections when validation fails */
        .form-section.incomplete {
            border: 2px solid #dc3545;
            box-shadow: 0 0 8px rgba(220, 53, 69, 0.25);
            background-color: rgba(220, 53, 69, 0.05);
        }

        .form-section.incomplete .section-header .section-title,
        .form-section.incomplete .section-header i {
            color: #dc3545 !important;
        }

        /* Style for invalid fields */
        .field-invalid {
            border-color: #dc3545 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right calc(0.375em + 0.1875rem) center !important;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem) !important;
        }

        /* Add transition for smooth color change */
        .form-section .section-header h5,
        .form-section .section-header i {
            transition: color 0.3s ease;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .section-title {
            margin: 0;
            color: var(--accent-color);
            font-weight: 700;
        }

        .text-accent {
            color: var(--accent-color) !important;
            font-size: 1.2rem;
        }

        /* Non-interactive fields styling */
        .non-interactive {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            cursor: not-allowed;
            opacity: 1;
            color: #6c757d;
            /* Add grayed out text color */
        }

        /* Ensure non-interactive fields stay grayed out when focused */
        .non-interactive:focus,
        .non-interactive:active,
        input.non-interactive:focus,
        input.non-interactive:active,
        .form-control.non-interactive:focus,
        .form-control.non-interactive:active,
        .form-select.non-interactive:focus,
        .form-select.non-interactive:active {
            background-color: #e9ecef !important;
            border-color: #ced4da !important;
            outline: none !important;
            box-shadow: none !important;
            cursor: not-allowed !important;
            opacity: 1 !important;
            color: #6c757d !important;
            /* Add grayed out text color for focus state */
        }

        /* Special styling for select elements that are non-interactive */
        select.non-interactive,
        .form-select.non-interactive {
            color: #6c757d;
        }

        /* Option styling in disabled selects */
        select.non-interactive option,
        .form-select.non-interactive option {
            color: #6c757d;
        }

        /* Dark mode non-interactive fields */
        [data-bs-theme="dark"] .non-interactive {
            background-color: #37383A;
            border: 1px dotted var(--dark-border);
            color: #adb5bd;
        }

        /* Dark mode non-interactive fields when focused */
        [data-bs-theme="dark"] .non-interactive:focus,
        [data-bs-theme="dark"] .non-interactive:active,
        [data-bs-theme="dark"] input.non-interactive:focus,
        [data-bs-theme="dark"] input.non-interactive:active,
        [data-bs-theme="dark"] .form-control.non-interactive:focus,
        [data-bs-theme="dark"] .form-control.non-interactive:active,
        [data-bs-theme="dark"] .form-select.non-interactive:focus,
        [data-bs-theme="dark"] .form-select.non-interactive:active {
            background-color: #37383A !important;
            border-color: var(--dark-border) !important;
            color: #adb5bd !important;
            outline: none !important;
            box-shadow: none !important;
        }

        /* Dark mode interactible fields */
        [data-bs-theme="dark"] input:not(.non-interactive):not(.form-check-input),
        [data-bs-theme="dark"] select:not(.non-interactive),
        [data-bs-theme="dark"] textarea:not(.non-interactive) {
            background-color: #2B3035;
            color: var(--dark-text);
            border-color: var(--dark-border);
        }

        /* Select multiple styling */
        select[multiple] {
            height: auto;
            min-height: 100px;
        }

        /* Table styling */
        .table {
            background-color: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            border-collapse: separate;
            border-spacing: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 0;
        }

        .table th,
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
        }

        .table thead th {
            font-weight: 600;
            border-bottom-width: 1px;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            white-space: nowrap;
        }

        .table tbody th {
            font-weight: 600;
            background-color: var(--bg-secondary);
            opacity: 1;
            color: var(--text-primary);
        }

        .table-bordered {
            border: 1px solid var(--border-color);
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid var(--border-color);
        }

        .table input[type="number"] {
            min-width: 80px;
            border-radius: 6px;
            padding: 8px 10px;
            transition: all 0.2s ease;
            text-align: center;
            font-weight: 500;
        }

        .table input[readonly] {
            background-color: var(--bg-secondary);
            font-weight: 600;
        }

        .table input:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(var(--accent-color-rgb), 0.25) !important;
        }

        /* Table hover effects */
        .table tbody tr:hover {
            background-color: rgba(var(--accent-color-rgb), 0.04);
        }

        /* Total row styling */
        .table tr:last-child th,
        .table tr:last-child td {
            border-top: 2px solid var(--accent-color);
            background-color: rgba(var(--accent-color-rgb), 0.05);
        }

        /* Dark mode table styles */
        [data-bs-theme="dark"] .table {
            color: var(--dark-text);
        }

        [data-bs-theme="dark"] .table thead th {
            background-color: #2B3035;
            border-bottom-color: #495057;
            color: var(--dark-text);
        }

        [data-bs-theme="dark"] .table tbody th {
            background-color: rgba(43, 48, 53, 0.8);
        }

        [data-bs-theme="dark"] .table-bordered {
            border-color: #495057;
        }

        [data-bs-theme="dark"] .table-bordered th,
        [data-bs-theme="dark"] .table-bordered td {
            border-color: #495057;
        }

        [data-bs-theme="dark"] .table input[readonly] {
            background-color: #37383A;
            color: #adb5bd;
        }

        [data-bs-theme="dark"] .table tbody tr:hover {
            background-color: rgba(var(--accent-color-rgb), 0.1);
        }

        [data-bs-theme="dark"] .table tr:last-child th,
        [data-bs-theme="dark"] .table tr:last-child td {
            background-color: rgba(var(--accent-color-rgb), 0.1);
        }

        /* Specific table title styling */
        .table-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        /* Evaluation results section spacing */
        .evaluation-table {
            margin-bottom: 25px;
        }

        .evaluation-table:last-child {
            margin-bottom: 0;
        }

        /* Image preview container */
        #imagePreviewContainer {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .image-preview {
            width: calc(33.333% - 10px);
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            aspect-ratio: 16/9;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #dc3545;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            transition: all 0.2s ease;
        }

        .remove-image:hover {
            background-color: #bd2130;
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .image-preview {
                width: calc(50% - 10px);
            }
        }

        @media (max-width: 576px) {
            .image-preview {
                width: 100%;
            }
        }

        /* Table input focus */
        .table input:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(var(--accent-color-rgb), 0.25) !important;
        }

        /* Override Bootstrap's default focus box-shadow */
        .form-control:focus-visible,
        .form-select:focus-visible {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(var(--accent-color-rgb), 0.25) !important;
        }

        /* Override Bootstrap's default button focus styles */
        .btn:focus,
        .btn-icon:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(var(--accent-color-rgb), 0.25) !important;
        }

        /* Form check input focus state */
        .form-check-input:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(var(--accent-color-rgb), 0.25) !important;
        }

        /* Style for checked checkboxes */
        .form-check-input:checked {
            background-color: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
        }

        /* Dark theme focus styles */
        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus,
        [data-bs-theme="dark"] .form-check-input:focus,
        [data-bs-theme="dark"] .btn:focus,
        [data-bs-theme="dark"] .btn-sm:focus,
        [data-bs-theme="dark"] .input-group-text:focus,
        [data-bs-theme="dark"] input[type="date"]:focus,
        [data-bs-theme="dark"] input[type="time"]:focus,
        [data-bs-theme="dark"] input[type="text"]:focus,
        [data-bs-theme="dark"] input[type="number"]:focus,
        [data-bs-theme="dark"] textarea:focus,
        [data-bs-theme="dark"] select:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(var(--accent-color-rgb), 0.25) !important;
        }

        /* Input field styling enhancements */
        .form-control,
        .form-select {
            transition: all 0.2s ease-in-out;
        }

        /* Custom checkbox styling */
        .form-check-input {
            width: 1.2em;
            height: 1.2em;
            margin-top: 0.25em;
            vertical-align: top;
            border: 1px solid var(--border-color);
            appearance: none;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            transition: all 0.3s ease-in-out;
            position: relative;
        }

        .form-check-input:checked {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .form-check-input:checked[type="checkbox"] {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
        }

        .form-check-input:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(var(--accent-color-rgb), 0.25) !important;
        }

        .form-check-label {
            cursor: pointer;
            padding-left: 0.25rem;
            color: var(--text-primary);
            font-weight: 400;
            user-select: none;
        }

        /* Enhanced checkbox containers */
        .checkbox-container,
        .sdgs-container {
            display: grid;
            gap: 12px;
            margin-bottom: 15px;
        }

        .checkbox-container {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }

        .sdgs-container {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }

        .checkbox-item {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.2s ease;
            min-height: 60px;
            display: flex;
            width: 100%;
            align-items: center;
            /* Center items vertically */
        }

        .checkbox-item .form-check {
            margin: 0;
            display: flex;
            align-items: center;
            /* Center checkbox and label vertically */
            flex-grow: 1;
            width: 100%;
        }

        .checkbox-item .form-check-input {
            margin-top: 0;
            /* Remove top margin to help with centering */
            flex-shrink: 0;
            position: relative;
            top: 0;
            /* Align with text */
        }

        .checkbox-item .form-check-label {
            padding-left: 0.5rem;
            line-height: 1.4;
            flex-grow: 1;
            word-break: break-word;
            display: flex;
            align-items: center;
            /* Center text vertically */
            min-height: 24px;
            /* Ensure minimum height for alignment */
        }

        .checkbox-item:hover {
            background-color: rgba(var(--accent-color-rgb), 0.08);
        }

        /* Checkbox item checked state */
        .checkbox-item.checked {
            background-color: rgba(var(--accent-color-rgb), 0.15);
        }

        /* Dark mode checkbox styling */
        [data-bs-theme="dark"] .form-check-input {
            background-color: #2B3035;
            border-color: #495057;
        }

        [data-bs-theme="dark"] .form-check-input:checked {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        [data-bs-theme="dark"] .checkbox-item {
            background-color: #2B3035;
        }

        [data-bs-theme="dark"] .checkbox-item:hover {
            background-color: rgba(var(--accent-color-rgb), 0.15);
        }

        [data-bs-theme="dark"] .checkbox-item.checked {
            background-color: rgba(var(--accent-color-rgb), 0.25);
        }

        /* SDGs checkbox grid for better organization */
        .sdgs-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }

        /* Single column checkbox container */
        .checkbox-container.single-column {
            grid-template-columns: 1fr;
        }

        /* Select2 custom styling */
        .select2-container--bootstrap-5 .select2-selection {
            min-height: calc(1.5em + 0.75rem + 2px) !important;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            border-radius: 0.375rem;
            width: 100%;
        }

        /* Make the dropdown feel more like a dropdown */
        .select2-container--bootstrap-5 .select2-selection--multiple {
            overflow: hidden;
            height: auto;
        }

        /* Fix the search box to not look like a separate input */
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-search {
            margin: 0;
            width: 100%;
            min-width: 150px;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-search .select2-search__field {
            margin: 0;
            border: 0 !important;
            padding: 0.25rem;
            width: 100% !important;
            box-shadow: none !important;
            background: transparent;
        }

        /* Fix dropdown positioning and appearance */
        .select2-container--bootstrap-5 .select2-dropdown {
            border-color: var(--accent-color);
            border-radius: 0.375rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 2px;
        }

        /* Focus styles */
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(var(--accent-color-rgb), 0.25) !important;
        }

        .select2-container--bootstrap-5 .select2-selection__choice__display {
            font-size: 0.875rem;
        }

        .select2-container--bootstrap-5 .select2-dropdown .select2-results__option {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }

        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
            font-size: 0.875rem;
            padding: 0.375rem 0.5rem;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            padding-left: 0;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            margin: 0.25rem 0.25rem 0.25rem 0;
            padding: 0.25rem 0.5rem;
            color: #fff;
            background-color: var(--accent-color);
            border: 1px solid var(--accent-color);
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }

        /* Dark mode select2 */
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: rgba(var(--accent-color-rgb), 0.2);
            border-color: rgba(var(--accent-color-rgb), 0.3);
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-dropdown {
            background-color: var(--dark-input);
            color: var(--dark-text);
            border-color: var(--dark-border);
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection {
            background-color: var(--dark-input);
            color: var(--dark-text);
            border-color: var(--dark-border);
        }

        /* Placeholder styling */
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-search__field::placeholder,
        .select2-container--bootstrap-5 .select2-selection__placeholder {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .select2-container--bootstrap-5 .select2-selection__choice__display {
            font-size: 0.875rem;
            color: #fff;
        }

        .select2-container--bootstrap-5 .select2-dropdown .select2-results__option {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
            background-color: var(--accent-color) !important;
            color: white;
        }

        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
            font-size: 0.875rem;
            padding: 0.375rem 0.5rem;
        }

        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(var(--accent-color-rgb), 0.25) !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            padding-left: 0;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            margin: 0.25rem 0.25rem 0.25rem 0;
            padding: 0.25rem 0.5rem;
            color: #fff;
            background-color: var(--accent-color);
            border: 1px solid var(--accent-color);
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255, 255, 255, 0.8);
            margin-right: 0.25rem;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Select2 dropdown options styling */
        .select2-container--bootstrap-5 .select2-results__option {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }

        /* Hover and selection styling */
        .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected],
        .select2-container--bootstrap-5 .select2-results__option--selected,
        .select2-container--bootstrap-5 .select2-results__option[aria-selected=true] {
            background-color: var(--accent-color) !important;
            color: white !important;
        }

        /* Fix dropdown list font size */
        .select2-container--bootstrap-5 .select2-dropdown {
            font-size: 0.875rem;
        }

        /* Dark mode special handling */
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option {
            color: var(--dark-text);
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected],
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--selected,
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option[aria-selected=true] {
            color: white !important;
        }

        /* Ensure ALL Select2 dropdown elements have consistent font size */
        .select2-container--bootstrap-5 .select2-dropdown,
        .select2-container--bootstrap-5 .select2-dropdown *,
        .select2-container--bootstrap-5 .select2-results__option,
        .select2-container--bootstrap-5 .select2-results__group,
        .select2-container--bootstrap-5 .select2-results__message,
        .select2-container--bootstrap-5 .select2-selection__choice,
        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field,
        .select2-container--bootstrap-5 .select2-selection__rendered {
            font-size: 0.875rem !important;
        }

        /* Fix any padding issues */
        .select2-container--bootstrap-5 .select2-dropdown {
            padding: 0;
        }

        /* Handle dropdown options specifically */
        .select2-container--bootstrap-5 .select2-results__option {
            padding: 0.375rem 0.75rem;
        }

        /* Make it even more specific for overriding bootstrap styles */
        #implementingOffice~.select2-container .select2-dropdown,
        #implementingOffice~.select2-container .select2-dropdown * {
            font-size: 0.875rem !important;
        }

        /* Also ensure input field has consistent size */
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-search .select2-search__field {
            font-size: 0.875rem !important;
        }

        /* Make the clear button more visible and functional */
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__clear {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            margin: 0;
            padding: 0 5px;
            background: transparent;
            border: none;
            color: #6c757d;
            font-size: 1.25rem;
            font-weight: bold;
            line-height: 1;
            z-index: 10;
            cursor: pointer;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__clear:hover {
            color: var(--accent-color);
        }

        /* Standardize font size for all input fields while maintaining height */
        .form-control,
        .form-control-lg,
        .form-select,
        .form-select-lg,
        textarea.form-control,
        textarea.form-control-lg,
        .select2-container--bootstrap-5 .select2-selection,
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            font-size: 1rem !important;
        }

        /* Keep the original height and padding */
        .form-control-lg,
        .form-select-lg {
            height: auto;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        /* Ensure labels have consistent size too */
        .form-label {
            font-size: 1rem;
            font-weight: 500;
        }

        /* Light mode specific checkbox item styling */
        [data-bs-theme="light"] .checkbox-item {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        [data-bs-theme="light"] .checkbox-item:hover {
            background-color: rgba(var(--accent-color-rgb), 0.05);
            border-color: rgba(var(--accent-color-rgb), 0.2);
        }

        /* Keep original checked state styling */
        [data-bs-theme="light"] .checkbox-item.checked {
            background-color: rgba(var(--accent-color-rgb), 0.15);
            border-color: rgba(var(--accent-color-rgb), 0.3);
        }

        /* Override font size for form-control-lg to keep standard size */
        .input-group-lg .form-control,
        .input-group-lg .input-group-text,
        .input-group-lg .form-control-lg {
            font-size: 1rem !important;
        }

        /* Ensure input groups maintain proper height */
        .input-group-lg {
            height: calc(1.5em + 1rem + 2px);
        }

        .input-group-lg .input-group-text {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
            line-height: 1.5;
        }

        /* Form control heights and styling to match gad_proposal.php */
        .form-control,
        .form-select {
            height: 45px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            background-color: var(--input-bg, #ffffff);
        }

        /* Keep large inputs large, but with consistent styling */
        .form-control-lg,
        .form-select-lg,
        .input-group-lg .form-control {
            height: 45px !important;
            border-radius: 8px !important;
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }

        /* Fix input group height to match inputs */
        .input-group-lg {
            height: 45px !important;
        }

        .input-group-lg .input-group-text {
            border-radius: 8px 0 0 8px !important;
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }

        .input-group-lg .form-control {
            border-radius: 0 8px 8px 0 !important;
        }

        /* Textarea height exception */
        textarea.form-control {
            height: auto;
            min-height: 100px;
        }

        /* Hide scrollbar for Firefox */
        html {
            scrollbar-width: none;
        }

        /* Styling for disabled SDG checkboxes */
        .sdgs-container .checkbox-item {
            opacity: 0.9;
        }

        .sdgs-container .form-check-input:disabled {
            opacity: 0.8;
            pointer-events: none;
        }

        .sdgs-container .form-check-input:disabled:checked {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            opacity: 1;
        }

        .sdgs-container .checkbox-item.checked {
            opacity: 1;
        }

        /* Custom file input styling */
        .image-upload-container .form-control[type="file"] {
            padding: 0.375rem 0.75rem;
            line-height: 1.5;
            display: flex;
            align-items: center;
        }

        .image-upload-container .form-control[type="file"]::file-selector-button {
            margin-right: 1rem;
            padding: 0.375rem 0.75rem;
            border: 0;
            border-right: 1px solid var(--border-color);
            border-radius: 0;
            color: var(--text-primary);
            background-color: #e9ecef;
            pointer-events: none;
            display: inline-flex;
            align-items: center;
            height: calc(100% + 0.75rem);
            margin: -0.375rem 1rem -0.375rem -0.75rem;
        }

        [data-bs-theme="dark"] .image-upload-container .form-control[type="file"]::file-selector-button {
            background-color: #2B3035;
            color: var(--dark-text);
            border-right-color: var(--dark-border);
        }

        /* Image preview styling */
        #imagePreviewContainer {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .image-preview {
            position: relative;
            width: calc(33.333% - 10px);
            aspect-ratio: 4/3;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: rgba(255, 255, 255, 0.8);
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #dc3545;
        }

        .remove-image:hover {
            background-color: #dc3545;
            color: white;
        }

        /* Custom styles for SweetAlert2 */
        .swal2-backdrop-show {
            backdrop-filter: blur(5px);
            background-color: rgba(0, 0, 0, 0.7) !important;
        }

        .swal2-popup {
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }

        /* Disabled select field styling for dark/light mode */
        .filter-item select:disabled,
        .filter-item select.readonly,
        .filter-item select.bg-secondary-subtle {
            cursor: not-allowed;
        }

        /* Light mode disabled field styling */
        :root:not([data-bs-theme="dark"]) .filter-item select:disabled,
        :root:not([data-bs-theme="dark"]) .filter-item select.readonly,
        :root:not([data-bs-theme="dark"]) .filter-item select.bg-secondary-subtle {
            background-color: #e9ecef !important;
            color: #6c757d !important;
            border-color: #dee2e6 !important;
        }

        /* Dark mode disabled field styling */
        :root[data-bs-theme="dark"] .filter-item select:disabled,
        :root[data-bs-theme="dark"] .filter-item select.readonly,
        :root[data-bs-theme="dark"] .filter-item select.bg-secondary-subtle {
            background-color: #37383A !important;
            color: #adb5bd !important;
            border-color: #495057 !important;
        }

        .narrative-table-wrapper {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid var(--border-color, #dee2e6);
            margin-bottom: 0;
            height: calc(100% - 85px);
            /* Reduced subtraction to show 5th row */
            min-height: 400px;
            /* Set reasonable min-height */
            /* Hide scrollbar but maintain scroll functionality */
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        /* Additional styling for disabled fields */
        input:disabled,
        select:disabled,
        textarea:disabled,
        button:disabled {
            background-color: var(--readonly-bg) !important;
            border-color: var(--readonly-border) !important;
            color: var(--readonly-text) !important;
        }

        /* Special styling for buttons when disabled */
        button.central-disabled,
        .btn.central-disabled,
        button.non-interactive,
        .btn.non-interactive {
            opacity: 0.5;
            filter: grayscale(50%);
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Table Styles */
        .narrative-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Fixed layout to control column widths */
        }

        /* For campus users (non-central), adjust column widths */
        .narrative-table.campus-user th:nth-child(1),
        .narrative-table.campus-user td:nth-child(1) {
            width: 50%;
            /* Activity column takes half the width */
        }

        .narrative-table.campus-user th:nth-child(2),
        .narrative-table.campus-user td:nth-child(2) {
            width: 50%;
            /* Partner Agency column takes half the width */
        }

        /* For central users, adjust column widths */
        .narrative-table.central-user th:nth-child(1),
        .narrative-table.central-user td:nth-child(1) {
            width: 40%;
            /* Activity column takes 40% width */
        }

        .narrative-table.central-user th:nth-child(2),
        .narrative-table.central-user td:nth-child(2) {
            width: 35%;
            /* Partner Agency column takes 35% width */
        }

        .narrative-table.central-user th:nth-child(3),
        .narrative-table.central-user td:nth-child(3) {
            width: 25%;
            /* Campus column takes 25% width */
        }

        .narrative-table th,
        .narrative-table td {
            padding: 0.85rem 1rem;
            /* Reduced padding */
            text-align: left;
            border-bottom: 1px solid var(--border-color, #dee2e6);
        }

        /* Specific styles for disabled inputs in light mode */
        [data-bs-theme="light"] .form-control:disabled,
        [data-bs-theme="light"] .form-control[readonly] {
            background-color: #e9ecef !important;
            color: #6c757d !important;
            border-color: #dee2e6 !important;
            opacity: 1;
        }

        /* Fix for modal backdrop flickering */
        .swal2-backdrop-show,
        .swal2-backdrop-hide {
            -webkit-transition: background-color .1s !important;
            transition: background-color .1s !important;
        }

        .swal2-container.swal-blur-container {
            -webkit-backdrop-filter: blur(5px);
            backdrop-filter: blur(5px);
            -webkit-transition: backdrop-filter .1s !important;
            transition: backdrop-filter .1s !important;
        }

        .swal2-container.swal-blur-container.swal2-backdrop-hide {
            -webkit-backdrop-filter: blur(0px) !important;
            backdrop-filter: blur(0px) !important;
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* Dark mode support */
        [data-bs-theme="dark"] .notification-badge {
            background-color: #ff5c6c;
        }

        /* Active state styling */
        .nav-link.active .notification-badge {
            background-color: white;
            color: var(--accent-color);
        }
    </style>
</head>

<body>

    <!-- Mobile Navigation Toggle -->
    <button class="mobile-nav-toggle d-lg-none">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Backdrop -->
    <div class="sidebar-backdrop"></div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo-title">GAD SYSTEM</div>
            <div class="logo-image">
                <img src="../images/Batangas_State_Logo.png" alt="Batangas State Logo">
            </div>
        </div>
        <div class="datetime-container">
            <div class="date" id="current-date"></div>
            <div class="time" id="current-time"></div>
        </div>
        <div class="nav-content">
            <nav class="nav flex-column">
                <a href="../dashboard/dashboard.php" class="nav-link">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users me-2"></i> Staff
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../academic_rank/academic.php">Academic Rank</a></li>
                        <li><a class="dropdown-item" href="../personnel_list/personnel_list.php">Personnel List</a></li>
                        <li><a class="dropdown-item" href="../signatory/sign.php">Signatory</a></li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="#" id="formsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-alt me-2"></i> Forms
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../target_forms/target.php">Target Form</a></li>
                        <li><a class="dropdown-item" href="../gbp_forms/gbp.php">GBP Form</a></li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" id="ppasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                PPAs Form
                            </a>
                            <ul class="dropdown-menu dropdown-submenu" aria-labelledby="ppasDropdown">
                                <li><a class="dropdown-item" href="../ppas_form/ppas.php">Main PPAs Form</a></li>
                                <li><a class="dropdown-item" href="../ppas_proposal/gad_proposal.php">GAD Proposal Form</a></li>
                                <li><a class="dropdown-item" href="../narrative/narrative.php">Narrative Form</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i> Reports
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../gpb_reports/gbp_reports.php">Annual GPB Reports</a></li>
                        <li><a class="dropdown-item" href="../ppas_reports/ppas_report.php">Quarterly PPAs Reports</a></li>
                        <li><a class="dropdown-item" href="../ps_atrib_reports/ps.php">PSA Reports</a></li>
                        <li><a class="dropdown-item" href="../ppas_proposal_reports/print_proposal.php">GAD Proposal Reports</a></li>
                        <li><a class="dropdown-item" href="../narrative_reports/print_narrative.php">Narrative Reports</a></li>
                    </ul>
                </div>
                <?php
                $currentPage = basename($_SERVER['PHP_SELF']);
                if ($isCentral):
                ?>
                    <a href="../approval/approval.php" class="nav-link approval-link">
                        <i class="fas fa-check-circle me-2"></i> Approval
                        <span id="approvalBadge" class="notification-badge" style="display: none;">0</span>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
        <!-- Add inside the sidebar div, after the nav-content div (around line 1061) -->
        <div class="bottom-controls">
            <a href="#" class="logout-button" onclick="handleLogout(event)">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
            <button class="theme-switch-button" onclick="toggleTheme()">
                <i class="fas fa-sun" id="theme-icon"></i>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-title">
            <i class="fas fa-clipboard-list"></i>
            <h2>Narrative Management</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Add Narrative Form</h5>
            </div>
            <div class="card-body">
                <form id="ppasForm" novalidate>
                    <!-- Hidden field for narrative ID -->
                    <input type="hidden" id="hiddenNarrativeId" name="narrative_id" value="">

                    <!-- Basic Info Section -->
                    <div class="form-section mb-4">
                        <div class="section-header mb-3">
                            <i class="fas fa-info-circle text-accent"></i>
                            <h5 class="section-title">Basic Information</h5>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="year" class="form-label">Year</label>
                                <select class="form-select form-control-lg" id="year" required>
                                    <option value="" selected disabled>Select Year</option>
                                    <!-- Options will be populated dynamically -->
                                </select>
                                <small class="text-muted fst-italic">Fetched from PPAs entries.</small>
                            </div>
                            <div class="col-md-4">
                                <label for="quarter" class="form-label">Quarter</label>
                                <select class="form-select form-control-lg non-interactive" id="quarter" required disabled>
                                    <option value="" selected disabled>Select Quarter</option>
                                    <option value="Q1">Q1</option>
                                    <option value="Q2">Q2</option>
                                    <option value="Q3">Q3</option>
                                    <option value="Q4">Q4</option>
                                </select>
                                <small class="text-muted fst-italic">Year must be selected first.</small>
                            </div>
                            <div class="col-md-4">
                                <label for="title" class="form-label">Activity Title</label>
                                <select class="form-select form-control-lg non-interactive" id="title" required disabled>
                                    <option value="" selected disabled>Select Activity Title</option>
                                    <!-- Options will be populated dynamically -->
                                </select>
                                <small class="text-muted fst-italic">Choose an activity to load details.</small>
                            </div>
                            <div class="col-12">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control form-control-lg non-interactive" id="location" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Duration</label>
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text">Start Date</span>
                                            <input type="text" class="form-control form-control-lg non-interactive" id="startDate" readonly placeholder="dd/mm/yy">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text">End Date</span>
                                            <input type="text" class="form-control form-control-lg non-interactive" id="endDate" readonly placeholder="dd/mm/yy">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text">Start Time</span>
                                            <input type="text" class="form-control form-control-lg non-interactive" id="startTime" readonly placeholder="--:-- --">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text">End Time</span>
                                            <input type="text" class="form-control form-control-lg non-interactive" id="endTime" readonly placeholder="--:-- --">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Implementing Office Section -->
                    <div class="form-section mb-4">
                        <div class="section-header mb-3">
                            <i class="fas fa-building text-accent"></i>
                            <h5 class="section-title">Implementing Office & Service Type</h5>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="implementingOffice" class="form-label">Implementing Office/College/Organization</label>
                                <select class="form-select form-select-lg select2-multi" id="implementingOffice" multiple required>
                                    <option value="College of Informatics and Computing Sciences">College of Informatics and Computing Sciences</option>
                                    <option value="College of Nursing and Allied Health Sciences">College of Nursing and Allied Health Sciences</option>
                                    <option value="College of Arts and Sciences">College of Arts and Sciences</option>
                                    <option value="College of Engineering">College of Engineering</option>
                                    <option value="College of Accountancy, Business, Economics and International Hospitality Management">College of Accountancy, Business, Economics and International Hospitality Management</option>
                                    <option value="College of Teacher Education">College of Teacher Education</option>
                                    <option value="Bachelor of Science in Computer Science">Bachelor of Science in Computer Science</option>
                                    <option value="Bachelor of Science in Information Technology">Bachelor of Science in Information Technology</option>
                                    <option value="Bachelor of Science in Information Systems">Bachelor of Science in Information Systems</option>
                                    <option value="Bachelor of Science in Nursing">Bachelor of Science in Nursing</option>
                                    <option value="Bachelor of Science in Medical Technology">Bachelor of Science in Medical Technology</option>
                                    <option value="Bachelor of Science in Mechanical Engineering">Bachelor of Science in Mechanical Engineering</option>
                                    <option value="Bachelor of Science in Civil Engineering">Bachelor of Science in Civil Engineering</option>
                                    <option value="Bachelor of Science in Electrical Engineering">Bachelor of Science in Electrical Engineering</option>
                                    <option value="Bachelor of Science in Industrial Engineering">Bachelor of Science in Industrial Engineering</option>
                                    <option value="Bachelor of Science in Electronics Engineering">Bachelor of Science in Electronics Engineering</option>
                                    <option value="Bachelor of Science in Accountancy">Bachelor of Science in Accountancy</option>
                                    <option value="Bachelor of Science in Business Administration">Bachelor of Science in Business Administration</option>
                                    <option value="Bachelor of Science in Tourism Management">Bachelor of Science in Tourism Management</option>
                                    <option value=">Bachelor of Secondary Education">Bachelor of Secondary Education</option>
                                    <option value="Bachelor of Elementary Education">Bachelor of Elementary Education</option>
                                    <option value="Gender and Development Office">Gender and Development Office</option>
                                    <option value="Extension Services Office">Extension Services Office</option>
                                    <option value="Research and Development Office">Research and Development Office</option>
                                </select>
                                <small class="text-muted fst-italic">Search and select multiple organizations.</small>
                            </div>
                            <div class="col-12">
                                <label for="partnerAgency" class="form-label">Partner Agency</label>
                                <input type="text" class="form-control form-control-lg" id="partnerAgency">
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label">Type of Extension Service Agenda</label>
                                <div class="checkbox-container single-column">
                                    <div class="checkbox-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agenda1">
                                            <label class="form-check-label" for="agenda1">BatStateU Inclusive Social Innovation for Regional Growth (BISIG) Program</label>
                                        </div>
                                    </div>
                                    <div class="checkbox-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agenda2">
                                            <label class="form-check-label" for="agenda2">Livelihood and other Entrepreneurship related on Agri-Fisheries (LEAF)</label>
                                        </div>
                                    </div>
                                    <div class="checkbox-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agenda3">
                                            <label class="form-check-label" for="agenda3">Environment and Natural Resources Conservation, Protection and Rehabilitation Program</label>
                                        </div>
                                    </div>
                                    <div class="checkbox-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agenda4">
                                            <label class="form-check-label" for="agenda4">Smart Analytics and Engineering Innovation</label>
                                        </div>
                                    </div>
                                    <div class="checkbox-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agenda5">
                                            <label class="form-check-label" for="agenda5">Adopt-a Municipality/Barangay/School/Social Development Thru BIDANI Implementation</label>
                                        </div>
                                    </div>
                                    <div class="checkbox-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agenda6">
                                            <label class="form-check-label" for="agenda6">Community Outreach</label>
                                        </div>
                                    </div>
                                    <div class="checkbox-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agenda7">
                                            <label class="form-check-label" for="agenda7">Technical - Vocational Education and Training (TVET) Program</label>
                                        </div>
                                    </div>
                                    <div class="checkbox-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agenda8">
                                            <label class="form-check-label" for="agenda8">Technology Transfer and Adoption/Utilization Program</label>
                                        </div>
                                    </div>
                                    <div class="checkbox-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agenda9">
                                            <label class="form-check-label" for="agenda9">Technical Assistance and Advisory Services Program</label>
                                        </div>
                                    </div>
                                    <div class="checkbox-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agenda10">
                                            <label class="form-check-label" for="agenda10">Parents' Empowerment through Social Development (PESODEV)</label>
                                        </div>
                                    </div>
                                    <div class="checkbox-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agenda11">
                                            <label class="form-check-label" for="agenda11">Gender and Development</label>
                                        </div>
                                    </div>
                                    <div class="checkbox-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agenda12">
                                            <label class="form-check-label" for="agenda12">Disaster Risk Reduction and Management and Disaster Preparedness and Response/Climate Change Adaptation (DRRM and DPR/CCA)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SDGs Section -->
                    <div class="form-section mb-4">
                        <div class="section-header mb-3">
                            <i class="fas fa-globe-americas text-accent"></i>
                            <h5 class="section-title">Sustainable Development Goals (SDGs)</h5>
                            <small class="text-muted fst-italic">(Auto-populated from selected activity)</small>
                        </div>
                        <div class="sdgs-container">
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg1" disabled>
                                    <label class="form-check-label" for="sdg1">1. No Poverty</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg2" disabled>
                                    <label class="form-check-label" for="sdg2">2. Zero Hunger</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg3" disabled>
                                    <label class="form-check-label" for="sdg3">3. Good Health and Well-being</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg4" disabled>
                                    <label class="form-check-label" for="sdg4">4. Quality Education</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg5" disabled>
                                    <label class="form-check-label" for="sdg5">5. Gender Equality</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg6" disabled>
                                    <label class="form-check-label" for="sdg6">6. Clean Water and Sanitation</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg7" disabled>
                                    <label class="form-check-label" for="sdg7">7. Affordable and Clean Energy</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg8" disabled>
                                    <label class="form-check-label" for="sdg8">8. Decent Work and Economic Growth</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg9" disabled>
                                    <label class="form-check-label" for="sdg9">9. Industry, Innovation and Infrastructure</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg10" disabled>
                                    <label class="form-check-label" for="sdg10">10. Reduced Inequalities</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg11" disabled>
                                    <label class="form-check-label" for="sdg11">11. Sustainable Cities and Communities</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg12" disabled>
                                    <label class="form-check-label" for="sdg12">12. Responsible Consumption and Production</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg13" disabled>
                                    <label class="form-check-label" for="sdg13">13. Climate Action</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg14" disabled>
                                    <label class="form-check-label" for="sdg14">14. Life Below Water</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg15" disabled>
                                    <label class="form-check-label" for="sdg15">15. Life on Land</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg16" disabled>
                                    <label class="form-check-label" for="sdg16">16. Peace, Justice and Strong Institutions</label>
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sdg17" disabled>
                                    <label class="form-check-label" for="sdg17">17. Partnerships for the Goals</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Beneficiaries Section -->
                    <div class="form-section mb-4">
                        <div class="section-header mb-3">
                            <i class="fas fa-users text-accent"></i>
                            <h5 class="section-title">Beneficiaries</h5>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="typeBeneficiaries" class="form-label">Type of Beneficiaries</label>
                                <input type="text" class="form-control form-control-lg" id="typeBeneficiaries" required>
                                <small class="text-muted fst-italic">Specify the target beneficiary category.</small>
                            </div>
                            <div class="col-12">
                                <p class="table-title">Beneficiary Distribution by Gender</p>
                                <div class="table-responsive evaluation-table">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col" style="width: 25%">Gender</th>
                                                <th scope="col">BatStateU Participants</th>
                                                <th scope="col">Participants from other Institutions</th>
                                                <th scope="col" style="width: 15%">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th scope="row">Male</th>
                                                <td><input type="number" class="form-control" id="maleBatStateU" min="0"></td>
                                                <td><input type="number" class="form-control" id="maleOthers" min="0"></td>
                                                <td><input type="number" class="form-control" id="maleTotal" readonly></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Female</th>
                                                <td><input type="number" class="form-control" id="femaleBatStateU" min="0"></td>
                                                <td><input type="number" class="form-control" id="femaleOthers" min="0"></td>
                                                <td><input type="number" class="form-control" id="femaleTotal" readonly></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Total</th>
                                                <td><input type="number" class="form-control" id="totalBatStateU" readonly></td>
                                                <td><input type="number" class="form-control" id="totalOthers" readonly></td>
                                                <td><input type="number" class="form-control" id="grandTotal" readonly></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Project Team Section -->
                    <div class="form-section mb-4">
                        <div class="section-header mb-3">
                            <i class="fas fa-user-tie text-accent"></i>
                            <h5 class="section-title">Project Team</h5>
                            <small class="text-muted fst-italic">(Personnel are loaded dynamically based on selected activity)</small>
                        </div>

                        <!-- Project Leaders Container -->
                        <div id="projectLeadersContainer">
                            <!-- Dynamic Project Leader fields will be added here -->
                        </div>

                        <!-- Assistant Project Leaders Container -->
                        <div id="assistantLeadersContainer">
                            <!-- Dynamic Assistant Project Leader fields will be added here -->
                        </div>

                        <!-- Project Staff Container -->
                        <div id="projectStaffContainer">
                            <!-- Dynamic Project Staff fields will be added here -->
                        </div>
                    </div>

                    <!-- Objectives Section -->
                    <div class="form-section mb-4">
                        <div class="section-header mb-3">
                            <i class="fas fa-bullseye text-accent"></i>
                            <h5 class="section-title">Objectives & Narrative</h5>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">General Objective</label>
                                <textarea class="form-control form-control-lg non-interactive" id="generalObjectives" rows="3" readonly></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Specific Objectives</label>
                                <textarea class="form-control form-control-lg non-interactive" id="specificObjectives" rows="6" style="min-height: 180px;" readonly></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Narrative of the Activity</label>
                                <textarea class="form-control form-control-lg" id="activityNarrative" rows="6" required></textarea>
                                <small class="text-muted fst-italic">Provide detailed description of the activity implementation.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Evaluation Results Section -->
                    <div class="form-section mb-4">
                        <div class="section-header mb-3">
                            <i class="fas fa-chart-bar text-accent"></i>
                            <h5 class="section-title">Evaluation Results</h5>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Number of Beneficiaries who rated the activity as:</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col" style="width: 25%">Scale</th>
                                                <th scope="col">BatStateU Participants</th>
                                                <th scope="col">Participants from other Institutions</th>
                                                <th scope="col" style="width: 15%">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th scope="row">Excellent</th>
                                                <td><input type="number" class="form-control activity-rating" min="0" data-row="excellent" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control activity-rating" min="0" data-row="excellent" data-col="others"></td>
                                                <td><input type="number" class="form-control activity-total" readonly data-row="excellent"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Very Satisfactory</th>
                                                <td><input type="number" class="form-control activity-rating" min="0" data-row="very" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control activity-rating" min="0" data-row="very" data-col="others"></td>
                                                <td><input type="number" class="form-control activity-total" readonly data-row="very"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Satisfactory</th>
                                                <td><input type="number" class="form-control activity-rating" min="0" data-row="satisfactory" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control activity-rating" min="0" data-row="satisfactory" data-col="others"></td>
                                                <td><input type="number" class="form-control activity-total" readonly data-row="satisfactory"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Fair</th>
                                                <td><input type="number" class="form-control activity-rating" min="0" data-row="fair" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control activity-rating" min="0" data-row="fair" data-col="others"></td>
                                                <td><input type="number" class="form-control activity-total" readonly data-row="fair"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Poor</th>
                                                <td><input type="number" class="form-control activity-rating" min="0" data-row="poor" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control activity-rating" min="0" data-row="poor" data-col="others"></td>
                                                <td><input type="number" class="form-control activity-total" readonly data-row="poor"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Total</th>
                                                <td><input type="number" class="form-control activity-col-total" readonly data-col="batstateu"></td>
                                                <td><input type="number" class="form-control activity-col-total" readonly data-col="others"></td>
                                                <td><input type="number" class="form-control activity-grand-total" readonly></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <label class="form-label">Number of Beneficiaries who rated The Timeliness of the activity as:</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col" style="width: 25%">Scale</th>
                                                <th scope="col">BatStateU Participants</th>
                                                <th scope="col">Participants from other Institutions</th>
                                                <th scope="col" style="width: 15%">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th scope="row">Excellent</th>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="excellent" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="excellent" data-col="others"></td>
                                                <td><input type="number" class="form-control timeliness-total" readonly data-row="excellent"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Very Satisfactory</th>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="very" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="very" data-col="others"></td>
                                                <td><input type="number" class="form-control timeliness-total" readonly data-row="very"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Satisfactory</th>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="satisfactory" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="satisfactory" data-col="others"></td>
                                                <td><input type="number" class="form-control timeliness-total" readonly data-row="satisfactory"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Fair</th>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="fair" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="fair" data-col="others"></td>
                                                <td><input type="number" class="form-control timeliness-total" readonly data-row="fair"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Poor</th>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="poor" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="poor" data-col="others"></td>
                                                <td><input type="number" class="form-control timeliness-total" readonly data-row="poor"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Total</th>
                                                <td><input type="number" class="form-control timeliness-col-total" readonly data-col="batstateu"></td>
                                                <td><input type="number" class="form-control timeliness-col-total" readonly data-col="others"></td>
                                                <td><input type="number" class="form-control timeliness-grand-total" readonly></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Image Section -->
                    <div class="form-section mb-4">
                        <div class="section-header mb-3">
                            <i class="fas fa-images text-accent"></i>
                            <h5 class="section-title">Activity Images</h5>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="image-upload-container">
                                    <label for="imageUpload" class="form-label">Upload Activity Images (Up to 6)</label>
                                    <input type="file" class="form-control" id="imageUpload" accept="image/*" multiple>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row g-3" id="imagePreviewContainer">
                                    <!-- Image previews will be displayed here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn-icon" id="viewBtn">
                                <i class="fas fa-eye"></i>
                            </button>
                            <div class="d-inline-flex gap-3">
                                <button type="submit" class="btn-icon" id="addBtn">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button type="button" class="btn-icon" id="editBtn">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn-icon" id="deleteBtn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Check if we need to open edit modal after page refresh
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('openEditModal') === 'true') {
                // Clear the flag
                localStorage.removeItem('openEditModal');

                // Open the edit modal after a short delay to ensure DOM is ready
                setTimeout(() => {
                    openNarrativeModal('edit');
                }, 500);
            }
        });

        let currentEditingNarrativeId = 0;
        // Get reference to the year select element
        const yearSelect = document.getElementById('year');
        const quarterSelect = document.getElementById('quarter');
        const titleSelect = document.getElementById('title');

        // Populate the year dropdown with data from database
        const years = <?php echo $yearsJson; ?>;

        function updateDateTime() {
            const now = new Date();
            const dateOptions = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const timeOptions = {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            };

            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        function updateThemeIcon(theme) {
            const themeIcon = document.getElementById('theme-icon');
            themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }

        // Calculate beneficiaries table totals
        function calculateBeneficiaryTotals() {
            // Male totals
            const maleBatStateU = parseInt(document.getElementById('maleBatStateU').value) || 0;
            const maleOthers = parseInt(document.getElementById('maleOthers').value) || 0;
            document.getElementById('maleTotal').value = maleBatStateU + maleOthers;

            // Female totals
            const femaleBatStateU = parseInt(document.getElementById('femaleBatStateU').value) || 0;
            const femaleOthers = parseInt(document.getElementById('femaleOthers').value) || 0;
            document.getElementById('femaleTotal').value = femaleBatStateU + femaleOthers;

            // Column totals
            document.getElementById('totalBatStateU').value = maleBatStateU + femaleBatStateU;
            document.getElementById('totalOthers').value = maleOthers + femaleOthers;

            // Grand total
            document.getElementById('grandTotal').value = maleBatStateU + maleOthers + femaleBatStateU + femaleOthers;
        }

        // Calculate activity rating totals
        function calculateActivityRatingTotals() {
            const rows = ['excellent', 'very', 'satisfactory', 'fair', 'poor'];
            const cols = ['batstateu', 'others'];

            // Calculate row totals
            rows.forEach(row => {
                let rowTotal = 0;
                cols.forEach(col => {
                    const value = parseInt(document.querySelector(`.activity-rating[data-row="${row}"][data-col="${col}"]`).value) || 0;
                    rowTotal += value;
                });
                document.querySelector(`.activity-total[data-row="${row}"]`).value = rowTotal;
            });

            // Calculate column totals
            cols.forEach(col => {
                let colTotal = 0;
                rows.forEach(row => {
                    const value = parseInt(document.querySelector(`.activity-rating[data-row="${row}"][data-col="${col}"]`).value) || 0;
                    colTotal += value;
                });
                document.querySelector(`.activity-col-total[data-col="${col}"]`).value = colTotal;
            });

            // Calculate grand total
            let grandTotal = 0;
            document.querySelectorAll('.activity-rating').forEach(input => {
                grandTotal += parseInt(input.value) || 0;
            });
            document.querySelector('.activity-grand-total').value = grandTotal;
        }

        // Calculate timeliness rating totals
        function calculateTimelinessRatingTotals() {
            const rows = ['excellent', 'very', 'satisfactory', 'fair', 'poor'];
            const cols = ['batstateu', 'others'];

            // Calculate row totals
            rows.forEach(row => {
                let rowTotal = 0;
                cols.forEach(col => {
                    const value = parseInt(document.querySelector(`.timeliness-rating[data-row="${row}"][data-col="${col}"]`).value) || 0;
                    rowTotal += value;
                });
                document.querySelector(`.timeliness-total[data-row="${row}"]`).value = rowTotal;
            });

            // Calculate column totals
            cols.forEach(col => {
                let colTotal = 0;
                rows.forEach(row => {
                    const value = parseInt(document.querySelector(`.timeliness-rating[data-row="${row}"][data-col="${col}"]`).value) || 0;
                    colTotal += value;
                });
                document.querySelector(`.timeliness-col-total[data-col="${col}"]`).value = colTotal;
            });

            // Calculate grand total
            let grandTotal = 0;
            document.querySelectorAll('.timeliness-rating').forEach(input => {
                grandTotal += parseInt(input.value) || 0;
            });
            document.querySelector('.timeliness-grand-total').value = grandTotal;
        }


        // Handle image uploads
        function handleImageUpload() {
            const imageUpload = document.getElementById('imageUpload');
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');

            // Object to store uploaded files
            const uploadedFiles = {};
            let fileCounter = 0;

            imageUpload.addEventListener('change', function() {
                // Get existing preview count
                const existingPreviews = imagePreviewContainer.querySelectorAll('.image-preview').length;

                // Check if adding these files would exceed the maximum
                if (existingPreviews + this.files.length > 6) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Too many files',
                        text: `You can upload a maximum of 6 images. You already have ${existingPreviews} image(s).`,
                        customClass: {
                            container: 'swal2-backdrop-show'
                        }
                    });
                    this.value = '';
                    return;
                }

                // Create previews for each new file
                Array.from(this.files).forEach((file) => {
                    const uniqueId = 'img_' + Date.now() + '_' + fileCounter++;
                    uploadedFiles[uniqueId] = file;

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'image-preview';
                        previewDiv.dataset.fileId = uniqueId;

                        const img = document.createElement('img');
                        img.src = e.target.result;

                        const removeBtn = document.createElement('button');
                        removeBtn.className = 'remove-image';
                        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                        removeBtn.dataset.fileId = uniqueId;
                        removeBtn.addEventListener('click', function(e) {
                            e.preventDefault();

                            // Remove the preview
                            const fileId = this.dataset.fileId;
                            const previewToRemove = document.querySelector(`.image-preview[data-file-id="${fileId}"]`);

                            if (previewToRemove) {
                                previewToRemove.remove();
                                delete uploadedFiles[fileId];

                                // Trigger validation
                                validateImageSection();
                            }
                        });

                        previewDiv.appendChild(img);
                        previewDiv.appendChild(removeBtn);
                        imagePreviewContainer.appendChild(previewDiv);

                        // Trigger validation
                        validateImageSection();
                    };
                    reader.readAsDataURL(file);
                });

                // Clear the file input to allow selecting the same files again
                this.value = '';
            });
        }

        // Function to fetch activities based on year and quarter
        function fetchActivities(year, quarter, narrativeId = 0) {
            // Validate inputs
            if (!year || !quarter) {
                console.error('Missing required parameters:', {
                    year,
                    quarter
                });
                const titleSelect = document.getElementById('title');
                if (titleSelect) {
                    titleSelect.disabled = true;
                }
                return; // Exit the function early
            }

            // Create form data for the request
            const formData = new FormData();
            formData.append('year', year);
            formData.append('quarter', quarter);
            formData.append('narrative_id', narrativeId);

            console.log('Fetching activities with:', {
                year,
                quarter
            });

            // Make AJAX request
            fetch('get_activities.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Check if response is OK
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    // Clone the response for debugging if needed
                    const responseClone = response.clone();

                    // Try to parse as JSON, handle errors if it fails
                    return response.json().catch(error => {
                        // If JSON parsing fails, get the text and log it
                        return responseClone.text().then(text => {
                            console.error('Invalid JSON response:', text);
                            throw new Error('Invalid JSON response from server');
                        });
                    });
                })
                .then(data => {
                    console.log('Response from get_activities.php:', data);

                    // Clear dropdown
                    titleSelect.innerHTML = '<option value="" selected disabled>Select Activity Title</option>';

                    // Add styles for missing GAD items to the page
                    if (!document.getElementById('missing-gad-styles')) {
                        const styleEl = document.createElement('style');
                        styleEl.id = 'missing-gad-styles';
                        styleEl.textContent = `
        .missing-gad-option {
            color: red;
            font-style: italic;
        }
        .has-narrative-option {
            color: red;
            font-style: italic;
        }
        .current-narrative-option {
            color: green;
            font-weight: bold;
        }
    `;
                        document.head.appendChild(styleEl);
                    }

                    // Check if activities were found
                    if (data.activities && data.activities.length > 0) {
                        // Add each activity to dropdown
                        // Add each activity to dropdown
                        data.activities.forEach(activity => {
                            const option = document.createElement('option');
                            option.value = activity.id;

                            // Check if activity is the current one being edited (has_narrative = 2)
                            if (activity.has_narrative == 2) {
                                option.textContent = `${activity.activity} (Current)`;
                                option.className = 'current-narrative-option';
                                option.style.color = 'green';
                                option.style.fontWeight = 'bold';
                                // Don't disable it - allow it to be selected
                            }
                            // Check if activity has an existing narrative
                            else if (activity.has_narrative == 1) {
                                option.textContent = `${activity.activity} (Has Narrative)`;
                                option.className = 'has-narrative-option';
                                option.disabled = true; // Make the option non-selectable
                                option.style.color = 'red';
                                option.style.fontStyle = 'italic';
                            }
                            // Check if activity is missing a GAD proposal
                            else if (activity.missing_gad == 1) {
                                option.textContent = `${activity.activity} (No GAD proposal)`;
                                option.className = 'missing-gad-option';
                                option.setAttribute('data-missing-gad', '1');
                                option.disabled = true; // Make the option non-selectable
                            } else {
                                option.textContent = activity.activity;
                            }

                            titleSelect.appendChild(option);
                        });
                        console.log(`Added ${data.activities.length} activities to dropdown`);
                    } else {
                        // No activities found - show debug info
                        const option = document.createElement('option');
                        option.value = "";
                        option.textContent = `No activities found for ${data.campus || userCampus} (${year} ${quarter})`;
                        option.disabled = true;
                        titleSelect.appendChild(option);

                        // Add debugging info if available
                        if (data.debug) {
                            console.warn('Debug info for no activities:', data);

                            // Show available quarters in the console
                            if (data.available_quarters) {
                                console.log('Available quarters for this year and campus:', data.available_quarters);
                            }

                            // Show alternative data if available
                            if (data.alt_data && data.alt_data.length > 0) {
                                console.log('Activities found for this year (without quarter filter):', data.alt_data);

                                // Add them to the dropdown as a fallback with quarter info
                                data.alt_data.forEach(activity => {
                                    const option = document.createElement('option');
                                    option.value = activity.id;
                                    option.textContent = `${activity.activity} (Q${activity.quarter})`;
                                    titleSelect.appendChild(option);
                                });
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching activities:', error);
                    titleSelect.innerHTML = '<option value="" selected disabled>Error loading activities</option>';
                });
        }

        // Initialize form functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Apply saved theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            updateThemeIcon(savedTheme);

            // Initialize select2 for enhanced dropdowns
            $('.select2-multi').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
                closeOnSelect: false,
                selectionCssClass: 'select2--large',
                dropdownCssClass: 'select2--large',
                dropdownParent: $('#implementingOffice').parent(),
                containerCssClass: 'select2-selection--custom'
            });

            // Manually handle the clear button functionality
            $(document).on('click', '.select2-selection__clear', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var select = $(this).closest('.select2-container').siblings('select');
                select.val(null).trigger('change');
            });

            // Get reference to the year select element
            const yearSelect = document.getElementById('year');
            const quarterSelect = document.getElementById('quarter');
            const titleSelect = document.getElementById('title');

            // Populate the year dropdown with data from database
            const years = <?php echo $yearsJson; ?>;

            // Clear existing options except the first one (placeholder)
            while (yearSelect.options.length > 1) {
                yearSelect.options.remove(1);
            }

            // Add options from the fetched years
            years.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            });

            // Field dependencies for Basic Information section
            yearSelect.addEventListener('change', function() {
                if (this.value) {
                    quarterSelect.disabled = false;
                    quarterSelect.classList.remove('non-interactive');

                    // Don't clear quarter dropdown value in edit mode
                    if (currentEditingNarrativeId <= 0) {
                        quarterSelect.value = '';
                    }

                    // Clear any previously loaded activity details
                    clearActivityDetails();

                    // Disable the activity title dropdown when year changes
                    titleSelect.disabled = true;
                    titleSelect.classList.add('non-interactive');
                    titleSelect.value = '';

                    // If we're in edit mode, fetch activities with the current narrative ID
                    if (currentEditingNarrativeId > 0) {
                        // If quarter has a value, fetch activities with the narrative ID
                        if (quarterSelect.value) {
                            fetchActivities(this.value, quarterSelect.value, currentEditingNarrativeId);
                        }
                    }
                } else {
                    quarterSelect.disabled = true;
                    quarterSelect.classList.add('non-interactive');
                    quarterSelect.value = '';
                    titleSelect.disabled = true;
                    titleSelect.classList.add('non-interactive');
                    titleSelect.value = '';
                }
            });

            // Enable Activity Title field when Quarter has a value
            quarterSelect.addEventListener('change', function() {
                if (this.value) {
                    titleSelect.disabled = false;
                    titleSelect.classList.remove('non-interactive');

                    // Clear any previously loaded activity details
                    clearActivityDetails();

                    // If we're in edit mode, fetch activities with the current narrative ID
                    if (currentEditingNarrativeId > 0) {
                        fetchActivities(yearSelect.value, this.value, currentEditingNarrativeId);
                    } else {
                        // Fetch activities based on selected year, quarter and user campus
                        fetchActivities(yearSelect.value, this.value);
                    }
                } else {
                    titleSelect.disabled = true;
                    titleSelect.classList.add('non-interactive');
                    titleSelect.value = '';
                }
            });

            // Function to clear all activity details
            function clearActivityDetails() {
                // Clear location and date fields
                document.getElementById('location').value = '';
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';
                document.getElementById('startTime').value = '';
                document.getElementById('endTime').value = '';

                // Clear SDG checkboxes
                for (let i = 1; i <= 17; i++) {
                    const sdgCheckbox = document.getElementById(`sdg${i}`);
                    if (sdgCheckbox) {
                        sdgCheckbox.checked = false;
                        // Also remove the 'checked' class from the checkbox container
                        const checkboxItem = sdgCheckbox.closest('.checkbox-item');
                        if (checkboxItem) {
                            checkboxItem.classList.remove('checked');
                        }
                    }
                }

                // Clear personnel containers
                clearPersonnelContainers();

                // Clear objectives and narrative fields
                document.getElementById('generalObjectives').value = '';
                document.getElementById('specificObjectives').value = '';
                document.getElementById('activityNarrative').value = '';

                // Reset all numerical input fields to empty or 0
                const numericalInputs = document.querySelectorAll('input[type="number"]');
                numericalInputs.forEach(input => {
                    input.value = '';
                });

                // Clear background color of all checkbox items
                document.querySelectorAll('.checkbox-item').forEach(item => {
                    item.classList.remove('checked');
                });
            }

            // Handle activity selection to populate related fields
            titleSelect.addEventListener('change', function() {
                // Clear all personnel containers first
                clearPersonnelContainers();

                if (this.value) {
                    // Check if the selected option has a missing GAD proposal
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.hasAttribute('data-missing-gad')) {
                        // Show a warning that this activity cannot be used
                        Swal.fire({
                            icon: 'warning',
                            title: 'Cannot Load Activity',
                            text: 'This activity does not have a GAD proposal. Please create a GAD proposal for this activity first.',
                            confirmButtonText: 'OK'
                        });

                        // Reset the selection
                        this.value = '';
                        return;
                    }

                    // Fetch activity details when an activity is selected
                    fetchActivityDetails(this.value, yearSelect.value, quarterSelect.value);
                }
            });

            // Function to clear all personnel containers
            function clearPersonnelContainers() {
                const containers = [
                    document.getElementById('projectLeadersContainer'),
                    document.getElementById('assistantLeadersContainer'),
                    document.getElementById('projectStaffContainer')
                ];

                containers.forEach(container => {
                    if (container) {
                        container.innerHTML = '';
                    }
                });
            }



            // Function to fetch activity details
            function fetchActivityDetails(activityId, year, quarter) {
                // Clear all personnel containers first
                clearPersonnelContainers();

                // Reset all SDG checkboxes
                for (let i = 1; i <= 17; i++) {
                    const sdgCheckbox = document.getElementById(`sdg${i}`);
                    if (sdgCheckbox) {
                        sdgCheckbox.checked = false;
                    }
                }

                // Create form data for the request
                const formData = new FormData();
                formData.append('activity_id', activityId);
                formData.append('year', year);
                formData.append('quarter', quarter);

                // Make AJAX request
                fetch('get_activity_details.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        // Check if response is OK
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }

                        // Clone the response for debugging if needed
                        const responseClone = response.clone();

                        // Try to parse as JSON, handle errors if it fails
                        return response.json().catch(error => {
                            // If JSON parsing fails, get the text and log it
                            return responseClone.text().then(text => {
                                console.error('Invalid JSON response:', text);
                                throw new Error('Invalid JSON response from server');
                            });
                        });
                    })
                    .then(data => {
                        console.log('Activity details fetched:', data);

                        if (data.success && data.details) {
                            const details = data.details;

                            // ---------- Basic Information ----------
                            // Populate basic form fields with null checks
                            const locationElem = document.getElementById('location');
                            if (locationElem) locationElem.value = details.location || '';

                            const startDateElem = document.getElementById('startDate');
                            if (startDateElem) startDateElem.value = details.start_date || '';

                            const endDateElem = document.getElementById('endDate');
                            if (endDateElem) endDateElem.value = details.end_date || '';

                            const startTimeElem = document.getElementById('startTime');
                            if (startTimeElem) startTimeElem.value = details.start_time || '';

                            const endTimeElem = document.getElementById('endTime');
                            if (endTimeElem) endTimeElem.value = details.end_time || '';

                            // Set placeholders for date/time fields
                            if (startDateElem) startDateElem.placeholder = 'dd/mm/yy';
                            if (endDateElem) endDateElem.placeholder = 'dd/mm/yy';
                            if (startTimeElem) startTimeElem.placeholder = '--:-- --';
                            if (endTimeElem) endTimeElem.placeholder = '--:-- --';

                            // ---------- SDGs ----------
                            // Populate SDGs if available
                            if (details.sdgs && Array.isArray(details.sdgs)) {
                                details.sdgs.forEach(sdg => {
                                    const sdgNumber = parseInt(sdg.match(/\d+/)?.[0]);
                                    if (sdgNumber && sdgNumber >= 1 && sdgNumber <= 17) {
                                        const sdgElem = document.getElementById(`sdg${sdgNumber}`);
                                        if (sdgElem) sdgElem.checked = true;
                                    }
                                });

                                // Add visual feedback for checked items
                                document.querySelectorAll('.sdgs-container .checkbox-item').forEach(item => {
                                    const checkbox = item.querySelector('input[type="checkbox"]');
                                    if (checkbox && checkbox.checked) {
                                        item.classList.add('checked');
                                    } else {
                                        item.classList.remove('checked');
                                    }
                                });
                            }

                            // ---------- Personnel ----------
                            // No need to clear containers here again - already done at the beginning
                            const projectLeadersContainer = document.getElementById('projectLeadersContainer');
                            const assistantLeadersContainer = document.getElementById('assistantLeadersContainer');
                            const projectStaffContainer = document.getElementById('projectStaffContainer');

                            // Create function to generate personnel row
                            function createPersonnelRow(name, container, roleTitle) {
                                if (!container) return;

                                const rowDiv = document.createElement('div');

                                // Add appropriate class based on role
                                if (roleTitle === 'Project Leader') {
                                    rowDiv.className = 'row g-3 mb-3 project-leader-row';
                                } else if (roleTitle === 'Assistant Project Leader') {
                                    rowDiv.className = 'row g-3 mb-3 assistant-leader-row';
                                } else if (roleTitle === 'Project Staff') {
                                    rowDiv.className = 'row g-3 mb-3 project-staff-row';
                                } else {
                                    rowDiv.className = 'row g-3 mb-3';
                                }

                                // Create name input
                                const nameCol = document.createElement('div');
                                nameCol.className = 'col-md-6';

                                const nameLabel = document.createElement('label');
                                nameLabel.className = 'form-label';
                                nameLabel.textContent = roleTitle;

                                const nameInput = document.createElement('input');
                                nameInput.type = 'text';
                                nameInput.className = 'form-control form-control-lg non-interactive';
                                nameInput.value = name;
                                nameInput.readOnly = true;

                                nameCol.appendChild(nameLabel);
                                nameCol.appendChild(nameInput);

                                // Create task input
                                const taskCol = document.createElement('div');
                                taskCol.className = 'col-md-6';

                                const taskLabel = document.createElement('label');
                                taskLabel.className = 'form-label';
                                taskLabel.textContent = 'Assigned Task';

                                const taskInput = document.createElement('input');
                                taskInput.type = 'text';
                                taskInput.className = 'form-control form-control-lg';
                                taskInput.name = `${roleTitle.replace(/\s+/g, '_').toLowerCase()}_task[]`;

                                // Add event listener to task input to run validation when it changes
                                taskInput.addEventListener('input', function() {
                                    // Delay validation slightly to ensure the input value is updated
                                    setTimeout(validateTeamSection, 100);
                                });

                                taskCol.appendChild(taskLabel);
                                taskCol.appendChild(taskInput);

                                // Add columns to row
                                rowDiv.appendChild(nameCol);
                                rowDiv.appendChild(taskCol);

                                // Add row to container
                                container.appendChild(rowDiv);
                            }

                            // Populate project leaders with individual rows
                            if (details.personnel && details.personnel.project_leaders && details.personnel.project_leaders.length > 0) {
                                details.personnel.project_leaders.forEach(leader => {
                                    createPersonnelRow(leader, projectLeadersContainer, 'Project Leader');
                                });
                            }

                            // Populate assistant project leaders with individual rows
                            if (details.personnel && details.personnel.assistant_leaders && details.personnel.assistant_leaders.length > 0) {
                                details.personnel.assistant_leaders.forEach(assistant => {
                                    createPersonnelRow(assistant, assistantLeadersContainer, 'Assistant Project Leader');
                                });
                            }

                            // Populate project staff with individual rows
                            if (details.personnel && details.personnel.staff && details.personnel.staff.length > 0) {
                                details.personnel.staff.forEach(staff => {
                                    createPersonnelRow(staff, projectStaffContainer, 'Project Staff');
                                });
                            }

                            // After all personnel are loaded, validate the team section
                            console.log("All personnel loaded, validating team section");
                            setTimeout(validateTeamSection, 300);

                            // ---------- GAD Proposal Data ----------
                            if (details.gad_proposal) {
                                const gad = details.gad_proposal;

                                // Populate mode of delivery
                                if (gad.mode_of_delivery) {
                                    const modeRadios = document.querySelectorAll('input[name="deliveryMode"]');
                                    if (modeRadios && modeRadios.length > 0) {
                                        modeRadios.forEach(radio => {
                                            if (radio.value === gad.mode_of_delivery) {
                                                radio.checked = true;
                                            }
                                        });
                                    }
                                }

                                // Populate rationale and other fields, checking if elements exist first
                                if (gad.rationale) {
                                    const rationaleElem = document.getElementById('rationale');
                                    if (rationaleElem) {
                                        rationaleElem.value = gad.rationale;
                                    }
                                }

                                // Populate general objectives
                                if (gad.general_objectives) {
                                    const generalObjectivesElem = document.getElementById('generalObjectives');
                                    if (generalObjectivesElem) {
                                        generalObjectivesElem.value = gad.general_objectives;
                                    }
                                }

                                // Populate specific objectives (bullet list)
                                if (gad.specific_objectives && Array.isArray(gad.specific_objectives) && gad.specific_objectives.length > 0) {
                                    const specificObjectivesElem = document.getElementById('specificObjectives');
                                    if (specificObjectivesElem) {
                                        specificObjectivesElem.value = gad.specific_objectives.map(obj => `• ${obj}`).join('\n');
                                    }
                                }
                            }

                            // After loading data, trigger validation to update section status
                            setTimeout(() => {
                                validateBasicInfoSection();
                                validateSDGsSection();
                                validateTeamSection();
                                validateObjectivesSection();
                            }, 300);
                        } else {
                            console.error('Error fetching activity details:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching activity details:', error);
                    });
            }

            // Initialize beneficiary calculations
            const beneficiaryInputs = ['maleBatStateU', 'maleOthers', 'femaleBatStateU', 'femaleOthers'];
            beneficiaryInputs.forEach(id => {
                document.getElementById(id).addEventListener('input', calculateBeneficiaryTotals);
            });

            // Initialize activity rating calculations
            document.querySelectorAll('.activity-rating').forEach(input => {
                input.addEventListener('input', calculateActivityRatingTotals);
            });

            // Initialize timeliness rating calculations
            document.querySelectorAll('.timeliness-rating').forEach(input => {
                input.addEventListener('input', calculateTimelinessRatingTotals);
            });

            // Initialize image uploads
            handleImageUpload();

            // Initialize checkbox visual feedback
            document.querySelectorAll('.checkbox-item .form-check-input').forEach(checkbox => {
                // Initial state
                if (checkbox.checked) {
                    checkbox.closest('.checkbox-item').classList.add('checked');
                }

                // Add event listener for changes
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        this.closest('.checkbox-item').classList.add('checked');
                    } else {
                        this.closest('.checkbox-item').classList.remove('checked');
                    }
                });
            });

            // Make entire checkbox card clickable, except for SDG items
            document.querySelectorAll('.checkbox-item').forEach(item => {
                // Skip SDG checkboxes (they're non-interactive)
                if (item.closest('.sdgs-container')) {
                    return;
                }

                item.style.cursor = 'pointer';

                item.addEventListener('click', function(e) {
                    // Prevent clicking the checkbox or label from triggering twice
                    if (e.target.type !== 'checkbox' && !e.target.classList.contains('form-check-label')) {
                        // Find the checkbox inside this card
                        const checkbox = this.querySelector('input[type="checkbox"]');
                        // Toggle the checkbox
                        checkbox.checked = !checkbox.checked;

                        // Trigger change event to update visual state
                        const changeEvent = new Event('change', {
                            bubbles: true
                        });
                        checkbox.dispatchEvent(changeEvent);
                    }
                });
            });

            // Add styling to indicate SDG checkboxes are non-interactive
            document.querySelectorAll('.sdgs-container .checkbox-item').forEach(item => {
                item.style.opacity = '0.9';
                item.style.cursor = 'default';
            });

            // Ensure consistent checkbox item heights in each row
            function equalizeCheckboxHeights() {
                // Get all rows in the checkbox containers
                const checkboxContainers = document.querySelectorAll('.checkbox-container, .sdgs-container');

                checkboxContainers.forEach(container => {
                    // Calculate positions to determine items in the same row
                    const items = Array.from(container.querySelectorAll('.checkbox-item'));
                    const itemPositions = items.map(item => {
                        const rect = item.getBoundingClientRect();
                        return {
                            item: item,
                            top: rect.top,
                            bottom: rect.bottom,
                            height: rect.height
                        };
                    });

                    // Group items by row (based on top position)
                    const rows = {};
                    itemPositions.forEach(pos => {
                        const roundedTop = Math.round(pos.top);
                        if (!rows[roundedTop]) rows[roundedTop] = [];
                        rows[roundedTop].push(pos);
                    });

                    // For each row, find the tallest item and set all items to that height
                    Object.values(rows).forEach(row => {
                        if (row.length <= 1) return; // Skip rows with only one item

                        const maxHeight = Math.max(...row.map(pos => pos.height));
                        row.forEach(pos => {
                            pos.item.style.minHeight = `${maxHeight}px`;
                        });
                    });
                });
            }

            // Run on page load
            equalizeCheckboxHeights();

            // Run on window resize
            window.addEventListener('resize', equalizeCheckboxHeights);

            // Handle dropdown submenu click behavior
            const dropdownSubmenus = document.querySelectorAll('.dropdown-submenu > a');
            dropdownSubmenus.forEach(submenu => {
                submenu.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Close other open submenus
                    const otherSubmenus = document.querySelectorAll('.dropdown-submenu.show');
                    otherSubmenus.forEach(menu => {
                        if (menu !== this.parentElement) {
                            menu.classList.remove('show');
                        }
                    });

                    // Toggle current submenu
                    this.parentElement.classList.toggle('show');
                });
            });

            // Close submenus when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-submenu')) {
                    const openSubmenus = document.querySelectorAll('.dropdown-submenu.show');
                    openSubmenus.forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });
            // Form submission
            document.getElementById('ppasForm').addEventListener('submit', function(e) {
                e.preventDefault();

                // Validate all sections before submitting
                const validations = [
                    validateBasicInfoSection(),
                    validateImplementingOfficeSection(),
                    validateSDGsSection(),
                    validateBeneficiariesSection(),
                    validateTeamSection(),
                    validateObjectivesSection(),
                    validateEvaluationSection(),
                    validateImageSection()
                ];

                const invalidSections = validations.filter(isValid => !isValid).length;

                if (invalidSections > 0) {
                    // Mark all non-complete sections as incomplete with red styling
                    document.querySelectorAll('.form-section:not(.complete)').forEach(section => {
                        section.classList.add('incomplete');
                    });

                    // Highlight required fields in the incomplete sections
                    highlightMissingFields();

                    // Scroll to first incomplete section
                    const firstIncomplete = document.querySelector('.form-section.incomplete');
                    if (firstIncomplete) {
                        firstIncomplete.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                } else {
                    // All sections are complete, prepare data for submission
                    const formData = new FormData();

                    // Get the selected activity ID (ppas_form_id) with null check
                    const activityEl = document.getElementById('title');
                    if (!activityEl) {
                        console.error('Activity title element not found');
                        Swal.fire({
                            title: 'Error!',
                            text: 'Activity selection is missing or invalid.',
                            icon: 'error'
                        });
                        return;
                    }
                    const activityId = activityEl.value;
                    formData.append('ppas_form_id', activityId);

                    // Get Implementing Office/College/Organization with null check
                    const implementingOfficeEl = $('#implementingOffice');
                    if (implementingOfficeEl.length === 0) {
                        console.error('Implementing office element not found');
                        Swal.fire({
                            title: 'Error!',
                            text: 'Implementing office selection is missing or invalid.',
                            icon: 'error'
                        });
                        return;
                    }
                    const implementingOffice = implementingOfficeEl.val();
                    formData.append('implementing_office', JSON.stringify(implementingOffice));

                    // Get Partner Agency with null check
                    const partnerAgencyEl = document.getElementById('partnerAgency');
                    const partnerAgency = partnerAgencyEl ? partnerAgencyEl.value : '';
                    formData.append('partner_agency', partnerAgency);

                    // Collect extension service agenda checkboxes
                    const extensionServiceAgenda = [];
                    for (let i = 1; i <= 12; i++) {
                        const checkbox = document.getElementById(`agenda${i}`);
                        if (checkbox && checkbox.checked) {
                            extensionServiceAgenda.push(i - 1); // Send the position (0-11)
                        }
                    }

                    console.log("Extension Service Agenda before submission:", JSON.stringify(extensionServiceAgenda));
                    formData.append('extension_service_agenda', JSON.stringify(extensionServiceAgenda));

                    // Get Type of Beneficiaries with null check
                    const typeBeneficiariesEl = document.getElementById('typeBeneficiaries');
                    const typeBeneficiaries = typeBeneficiariesEl ? typeBeneficiariesEl.value : '';
                    formData.append('type_beneficiaries', typeBeneficiaries);

                    // Get Beneficiary Distribution by Gender with null checks
                    const beneficiaryDistribution = {};

                    // Add fields in the specific order required
                    const maleBatStateUEl = document.getElementById('maleBatStateU');
                    if (maleBatStateUEl) beneficiaryDistribution.maleBatStateU = maleBatStateUEl.value || 0;

                    const femaleBatStateUEl = document.getElementById('femaleBatStateU');
                    if (femaleBatStateUEl) beneficiaryDistribution.femaleBatStateU = femaleBatStateUEl.value || 0;

                    const maleOthersEl = document.getElementById('maleOthers');
                    if (maleOthersEl) beneficiaryDistribution.maleOthers = maleOthersEl.value || 0;

                    const femaleOthersEl = document.getElementById('femaleOthers');
                    if (femaleOthersEl) beneficiaryDistribution.femaleOthers = femaleOthersEl.value || 0;

                    formData.append('beneficiary_distribution', JSON.stringify(beneficiaryDistribution));

                    // Get Assigned tasks for every role
                    const teamTasks = {
                        projectLeader: [],
                        assistantLeader: [],
                        projectStaff: []
                    };

                    console.log("Searching for team task rows...");

                    // Debug team containers
                    const projectLeadersContainer = document.querySelector('#projectLeadersContainer');
                    const assistantLeadersContainer = document.querySelector('#assistantLeadersContainer');
                    const projectStaffContainer = document.querySelector('#projectStaffContainer');

                    console.log("Project Leaders Container:", projectLeadersContainer);
                    console.log("Assistant Leaders Container:", assistantLeadersContainer);
                    console.log("Project Staff Container:", projectStaffContainer);

                    // Try finding rows by more general selectors
                    const allRows = document.querySelectorAll('#projectLeadersContainer .row, #assistantLeadersContainer .row, #projectStaffContainer .row');
                    console.log("All personnel rows found:", allRows.length);

                    // Process all rows in project leaders container
                    if (projectLeadersContainer) {
                        const rows = projectLeadersContainer.querySelectorAll('.row');
                        console.log("Project leader rows found:", rows.length);

                        rows.forEach((row, index) => {
                            // Find the name input (readonly) and task input
                            const nameInput = row.querySelector('input[readonly]');
                            const taskInput = row.querySelector('input:not([readonly])');

                            const name = nameInput ? nameInput.value : `Leader ${index+1}`;
                            const task = taskInput ? taskInput.value : '';

                            console.log(`Project Leader ${index+1}:`, name, "Task:", task);

                            if (task) {
                                teamTasks.projectLeader.push({
                                    name,
                                    task
                                });
                            }
                        });
                    }

                    // Process all rows in assistant leaders container
                    if (assistantLeadersContainer) {
                        const rows = assistantLeadersContainer.querySelectorAll('.row');
                        console.log("Assistant leader rows found:", rows.length);

                        rows.forEach((row, index) => {
                            // Find the name input (readonly) and task input
                            const nameInput = row.querySelector('input[readonly]');
                            const taskInput = row.querySelector('input:not([readonly])');

                            const name = nameInput ? nameInput.value : `Assistant ${index+1}`;
                            const task = taskInput ? taskInput.value : '';

                            console.log(`Assistant Leader ${index+1}:`, name, "Task:", task);

                            if (task) {
                                teamTasks.assistantLeader.push({
                                    name,
                                    task
                                });
                            }
                        });
                    }

                    // Process all rows in project staff container
                    if (projectStaffContainer) {
                        const rows = projectStaffContainer.querySelectorAll('.row');
                        console.log("Project staff rows found:", rows.length);

                        rows.forEach((row, index) => {
                            // Find the name input (readonly) and task input
                            const nameInput = row.querySelector('input[readonly]');
                            const taskInput = row.querySelector('input:not([readonly])');

                            const name = nameInput ? nameInput.value : `Staff ${index+1}`;
                            const task = taskInput ? taskInput.value : '';

                            console.log(`Project Staff ${index+1}:`, name, "Task:", task);

                            if (task) {
                                teamTasks.projectStaff.push({
                                    name,
                                    task
                                });
                            }
                        });
                    }

                    console.log("Final team tasks:", teamTasks);
                    formData.append('team_tasks', JSON.stringify(teamTasks));

                    // Safely get Narrative of the Activity
                    const narrativeEl = document.getElementById('activityNarrative');
                    const narrativeText = narrativeEl ? narrativeEl.value : '';
                    formData.append('activity_narrative', narrativeText);

                    // Get Activity Ratings as a matrix - order is important
                    const activityRatings = {
                        // Order matters: Excellent, Very Satisfactory, Satisfactory, Fair, Poor
                        "Excellent": {
                            BatStateU: 0,
                            Others: 0
                        },
                        "Very Satisfactory": {
                            BatStateU: 0,
                            Others: 0
                        },
                        "Satisfactory": {
                            BatStateU: 0,
                            Others: 0
                        },
                        "Fair": {
                            BatStateU: 0,
                            Others: 0
                        },
                        "Poor": {
                            BatStateU: 0,
                            Others: 0
                        }
                    };

                    // Get all activity rating inputs
                    const activityRatingInputs = document.querySelectorAll('input.activity-rating');
                    console.log("Found activity rating inputs:", activityRatingInputs.length);

                    // Process each input based on its data-row and data-col attributes
                    activityRatingInputs.forEach(input => {
                        const row = input.getAttribute('data-row');
                        const col = input.getAttribute('data-col');
                        const value = input.value || 0;

                        console.log(`Activity Rating: row=${row}, col=${col}, value=${value}`);

                        // Map the attributes to our structure
                        let ratingLevel;
                        if (row === 'excellent') ratingLevel = 'Excellent';
                        else if (row === 'very') ratingLevel = 'Very Satisfactory'; // 'very' is the actual attribute used
                        else if (row === 'satisfactory') ratingLevel = 'Satisfactory';
                        else if (row === 'fair') ratingLevel = 'Fair';
                        else if (row === 'poor') ratingLevel = 'Poor';

                        let participantType;
                        if (col === 'batstateu') participantType = 'BatStateU';
                        else if (col === 'others') participantType = 'Others';

                        // Store the value if we have valid mappings
                        if (ratingLevel && participantType && activityRatings[ratingLevel]) {
                            activityRatings[ratingLevel][participantType] = parseInt(value, 10) || 0;
                        }
                    });

                    formData.append('activity_ratings', JSON.stringify(activityRatings));
                    console.log("Activity Ratings:", activityRatings);

                    // Get Timeliness Ratings as a matrix - order is important
                    const timelinessRatings = {
                        // Order matters: Excellent, Very Satisfactory, Satisfactory, Fair, Poor
                        "Excellent": {
                            BatStateU: 0,
                            Others: 0
                        },
                        "Very Satisfactory": {
                            BatStateU: 0,
                            Others: 0
                        },
                        "Satisfactory": {
                            BatStateU: 0,
                            Others: 0
                        },
                        "Fair": {
                            BatStateU: 0,
                            Others: 0
                        },
                        "Poor": {
                            BatStateU: 0,
                            Others: 0
                        }
                    };

                    // Get all timeliness rating inputs
                    const timelinessRatingInputs = document.querySelectorAll('input.timeliness-rating');
                    console.log("Found timeliness rating inputs:", timelinessRatingInputs.length);

                    // Process each input based on its data-row and data-col attributes
                    timelinessRatingInputs.forEach(input => {
                        const row = input.getAttribute('data-row');
                        const col = input.getAttribute('data-col');
                        const value = input.value || 0;

                        console.log(`Timeliness Rating: row=${row}, col=${col}, value=${value}`);

                        // Map the attributes to our structure
                        let ratingLevel;
                        if (row === 'excellent') ratingLevel = 'Excellent';
                        else if (row === 'very') ratingLevel = 'Very Satisfactory'; // 'very' is the actual attribute used
                        else if (row === 'satisfactory') ratingLevel = 'Satisfactory';
                        else if (row === 'fair') ratingLevel = 'Fair';
                        else if (row === 'poor') ratingLevel = 'Poor';

                        let participantType;
                        if (col === 'batstateu') participantType = 'BatStateU';
                        else if (col === 'others') participantType = 'Others';

                        // Store the value if we have valid mappings
                        if (ratingLevel && participantType && timelinessRatings[ratingLevel]) {
                            timelinessRatings[ratingLevel][participantType] = parseInt(value, 10) || 0;
                        }
                    });

                    formData.append('timeliness_ratings', JSON.stringify(timelinessRatings));
                    console.log("Timeliness Ratings:", timelinessRatings);

                    // Get Activity Images with null check
                    const imageElements = document.querySelectorAll('.image-preview img');
                    console.log('Found image elements:', imageElements.length);

                    if (!imageElements || imageElements.length === 0) {
                        console.log('No images found, continuing without images');
                        formData.append('activity_images', JSON.stringify([]));
                        submitFormData(formData);
                        return;
                    }

                    // Process images
                    const images = [];
                    imageElements.forEach(img => {
                        console.log('Processing image:', img.src ? img.src.substring(0, 30) + '...' : 'no src');
                        if (img.src && img.src.startsWith('data:image')) {
                            images.push(img.src);
                        }
                    });

                    console.log('Processing ' + images.length + ' images');
                    formData.append('activity_images', JSON.stringify(images));

                    // Collect existing images to preserve them
                    const existingImages = [];
                    document.querySelectorAll('.image-preview').forEach(preview => {
                        // Get image name from data attribute
                        const imageName = preview.dataset.originalName;
                        if (imageName) {
                            existingImages.push(imageName);
                            console.log('Found existing image to preserve:', imageName);
                        }
                    });

                    // Add existing images to form data
                    formData.append('existing_images', JSON.stringify(existingImages));
                    console.log('Added ' + existingImages.length + ' existing images to preserve');

                    // Check if in edit mode and add narrative_id
                    if (document.getElementById('ppasForm').dataset.mode === 'edit') {
                        console.log('Form dataset:', document.getElementById('ppasForm').dataset); // Log all data attributes

                        // Try different ways to get narrative ID
                        const narrativeId = document.getElementById('ppasForm').dataset.narrativeId ||
                            document.getElementById('ppasForm').getAttribute('data-narrative-id') ||
                            document.getElementById('hiddenNarrativeId')?.value;

                        if (narrativeId) {
                            formData.append('narrative_id', narrativeId);
                            console.log('Adding narrative_id to form data:', narrativeId);
                        } else {
                            console.error('ERROR: Could not find narrative ID in edit mode!');
                            // Add an alert to show the error
                            alert('Error: Could not find narrative ID in edit mode!');
                        }
                    }

                    submitFormData(formData);
                }
            });

            // Function to submit the form data
            function submitFormData(formData) {
                // Check if we're updating an existing narrative
                const isEditMode = document.getElementById('ppasForm').dataset.mode === 'edit';
                const url = isEditMode ? 'update_narrative.php' : 'save_narrative.php';

                // Show loading state on button
                const addBtn = document.getElementById('addBtn');
                const originalButtonHtml = addBtn.innerHTML;

                // Send the data to the server
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);

                            // Restore button state
                            addBtn.innerHTML = originalButtonHtml;

                            if (result.success) {
                                // Log debug information to console
                                console.log('Debug Information:');
                                console.log(result.debug);

                                Swal.fire({
                                    title: 'Success!',
                                    text: isEditMode ? 'Narrative updated successfully' : 'Narrative saved successfully',
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 1500,
                                    timerProgressBar: true,
                                    backdrop: 'rgba(0,0,0,0.8)'
                                }).then(() => {
                                    window.location.reload();
                                    // If in edit mode, cancel editing to reset the form and UI
                                    if (isEditMode) {
                                        cancelEditing();
                                    } else {
                                        // Instead of reloading the page, just reset the form
                                        document.getElementById('ppasForm').reset();
                                        $('#implementingOffice').val(null).trigger('change');

                                        // Clear all validation indicators
                                        document.querySelectorAll('.section-status').forEach(status => {
                                            status.className = 'section-status';
                                            status.textContent = '';
                                        });

                                        // Clear personnel containers
                                        if (typeof clearPersonnelContainers === 'function') {
                                            clearPersonnelContainers();
                                        }

                                        // Reset SDG checkboxes
                                        document.querySelectorAll('.sdgs-container input[type="checkbox"]').forEach(checkbox => {
                                            checkbox.checked = false;
                                            const item = checkbox.closest('.checkbox-item');
                                            if (item) item.classList.remove('checked');
                                        });
                                    }
                                });
                            } else {
                                // Log debug information to console
                                console.log('Error Debug Information:');
                                console.log(result.debug);

                                Swal.fire({
                                    title: 'Error!',
                                    text: result.message,
                                    icon: 'error',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        } catch (e) {
                            console.error('Invalid JSON response:', response);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An unexpected error occurred.',
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });

                            // Restore button state
                            addBtn.innerHTML = originalButtonHtml;
                            addBtn.disabled = false;
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to connect to the server.',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });

                        // Restore button state
                        addBtn.innerHTML = originalButtonHtml;
                        addBtn.disabled = false;
                    }
                });
            }

            // Function to highlight missing fields in incomplete sections
            function highlightMissingFields() {
                // Basic Info section
                const basicInfoSection = document.querySelector('.form-section:nth-of-type(1)');
                if (basicInfoSection && !basicInfoSection.classList.contains('complete')) {
                    const requiredFields = ['title', 'location', 'startDate', 'endDate', 'startTime', 'endTime'];
                    requiredFields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        // Only mark editable fields as invalid
                        if (field && !field.value && !field.readOnly && !field.classList.contains('non-interactive')) {
                            field.classList.add('field-invalid');
                        }
                    });
                }

                // Implementing Office section
                const implementingSection = document.querySelector('.form-section:nth-of-type(2)');
                if (implementingSection && !implementingSection.classList.contains('complete')) {
                    // Check implementing office select
                    const implementingOffice = document.getElementById('implementingOffice');
                    if (implementingOffice && (!implementingOffice.value || implementingOffice.selectedOptions.length === 0)) {
                        const select2Container = $(implementingOffice).next('.select2-container');
                        if (select2Container.length) {
                            select2Container.find('.select2-selection').addClass('field-invalid');
                        }
                    }

                    // Check partner agency
                    const partnerAgency = document.getElementById('partnerAgency');
                    if (partnerAgency && !partnerAgency.value.trim()) {
                        partnerAgency.classList.add('field-invalid');
                    }

                    // Check agenda checkboxes
                    const hasAgenda = Array.from(document.querySelectorAll('.form-check-input[id^="agenda"]')).some(cb => cb.checked);
                    if (!hasAgenda) {
                        // Mark only the container as invalid, not individual checkboxes
                        const checkboxContainer = document.querySelector('.checkbox-container.single-column');
                        if (checkboxContainer) {
                            checkboxContainer.classList.add('field-invalid');
                        }
                    }
                }

                // SDGs section (usually auto-populated, no need to check)

                // Beneficiaries section
                const beneficiariesSection = document.querySelector('.form-section:nth-of-type(4)');
                if (beneficiariesSection && !beneficiariesSection.classList.contains('complete')) {
                    const typeBeneficiaries = document.getElementById('typeBeneficiaries');
                    if (typeBeneficiaries && !typeBeneficiaries.value.trim()) {
                        typeBeneficiaries.classList.add('field-invalid');
                    }

                    // Check if any beneficiary count is entered
                    const beneficiaryFields = ['maleBatStateU', 'femaleBatStateU', 'maleOthers', 'femaleOthers'];
                    const hasBeneficiaries = beneficiaryFields.some(id => {
                        const field = document.getElementById(id);
                        return field && field.value;
                    });

                    if (!hasBeneficiaries) {
                        // Mark the table container as invalid instead of individual fields
                        const beneficiariesTable = document.querySelector('.table.table-bordered.beneficiaries-table');
                        if (beneficiariesTable) {
                            beneficiariesTable.classList.add('field-invalid');
                        }
                    }
                }

                // Team section
                const teamSection = document.querySelector('.form-section:nth-of-type(5)');
                if (teamSection && !teamSection.classList.contains('complete')) {
                    // Highlight empty task inputs only
                    document.querySelectorAll('input[name$="_task[]"]').forEach(input => {
                        if (input && !input.value.trim() && !input.readOnly && !input.classList.contains('non-interactive')) {
                            input.classList.add('field-invalid');
                        }
                    });
                }

                // Objectives & Narrative section
                const objectivesSection = document.querySelector('.form-section:nth-of-type(6)');
                if (objectivesSection && !objectivesSection.classList.contains('complete')) {
                    const narrative = document.getElementById('activityNarrative');
                    if (narrative && !narrative.value.trim() && !narrative.readOnly && !narrative.classList.contains('non-interactive')) {
                        narrative.classList.add('field-invalid');
                    }
                }

                // Evaluation section
                const evaluationSection = document.querySelector('.form-section:nth-of-type(7)');
                if (evaluationSection && !evaluationSection.classList.contains('complete')) {
                    // Check activity rating table
                    const hasActivityRating = Array.from(document.querySelectorAll('.activity-rating')).some(input => input && input.value);
                    if (!hasActivityRating) {
                        // Mark the table container, not individual fields
                        const activityTable = evaluationSection.querySelector('.table.table-bordered');
                        if (activityTable) {
                            activityTable.classList.add('field-invalid');
                        }
                    }

                    // Check timeliness rating table
                    const hasTimelinessRating = Array.from(document.querySelectorAll('.timeliness-rating')).some(input => input && input.value);
                    if (!hasTimelinessRating) {
                        // Find the second table in the evaluation section
                        const timelinessTable = evaluationSection.querySelectorAll('.table.table-bordered')[1];
                        if (timelinessTable) {
                            timelinessTable.classList.add('field-invalid');
                        }
                    }
                }

                // Image section
                const imageSection = document.querySelector('.form-section:nth-of-type(8)');
                if (imageSection && !imageSection.classList.contains('complete')) {
                    const imageUpload = document.getElementById('imageUpload');
                    if (imageUpload) {
                        imageUpload.classList.add('field-invalid');
                    }
                }
            }

            // Add event listeners to clear field-invalid class when field is changed
            document.querySelectorAll('input, textarea, select').forEach(field => {
                field.addEventListener('input', function() {
                    this.classList.remove('field-invalid');

                    // If this is a checkbox, clear validation on the container
                    if (this.type === 'checkbox' && this.id.startsWith('agenda')) {
                        const container = document.querySelector('.checkbox-container.single-column');
                        if (container) container.classList.remove('field-invalid');
                    }

                    // If this is a beneficiary input, clear validation on all related fields
                    if (['maleBatStateU', 'femaleBatStateU', 'maleOthers', 'femaleOthers'].includes(this.id)) {
                        const table = document.querySelector('.table.table-bordered.beneficiaries-table');
                        if (table) table.classList.remove('field-invalid');
                    }

                    // If this is a task input field, clear validation
                    if (this.name && this.name.endsWith('_task[]')) {
                        this.classList.remove('field-invalid');
                    }

                    // If this is an activity or timeliness rating input, clear validation on the tables
                    if (this.classList.contains('activity-rating') || this.classList.contains('timeliness-rating')) {
                        const evaluationSection = document.querySelector('.form-section:nth-of-type(7)');
                        if (evaluationSection) {
                            const tables = evaluationSection.querySelectorAll('.table.table-bordered');
                            tables.forEach(table => {
                                if (table) table.classList.remove('field-invalid');
                            });
                        }
                    }
                });

                field.addEventListener('change', function() {
                    this.classList.remove('field-invalid');

                    // If this is a Select2 dropdown (implementing office)
                    if (this.id === 'implementingOffice') {
                        $(this).next('.select2-container').find('.select2-selection').removeClass('field-invalid');
                    }

                    // If this is a checkbox, clear validation on the container
                    if (this.type === 'checkbox' && this.id.startsWith('agenda')) {
                        const container = document.querySelector('.checkbox-container.single-column');
                        if (container) container.classList.remove('field-invalid');
                    }

                    // If this is an activity or timeliness rating input, clear validation on the tables
                    if (this.classList.contains('activity-rating') || this.classList.contains('timeliness-rating')) {
                        const evaluationSection = document.querySelector('.form-section:nth-of-type(7)');
                        if (evaluationSection) {
                            const tables = evaluationSection.querySelectorAll('.table.table-bordered');
                            tables.forEach(table => {
                                if (table) table.classList.remove('field-invalid');
                            });
                        }
                    }
                });
            });

            // Initialize for empty form
            calculateBeneficiaryTotals();
            calculateActivityRatingTotals();
            calculateTimelinessRatingTotals();

            // Initialize form section validation
            initializeFormValidation();

            // Add input change listeners to validate sections in real-time
            addValidationListeners();

            // Add specific event delegation for task fields
            const ppasForm = document.getElementById('ppasForm');
            if (ppasForm) {
                // Use event delegation for task fields
                ppasForm.addEventListener('input', function(e) {
                    // Check if the target is a task input field
                    if (e.target && e.target.name && e.target.name.endsWith('_task[]')) {
                        // Clear validation styling when user types in the field
                        e.target.classList.remove('field-invalid');
                    }
                }, true); // Using capture phase to ensure this runs before other handlers

                // Also handle clicking the add new row buttons to validate immediately after adding rows
                ['addProjectLeaderRow', 'addAssistantLeaderRow', 'addProjectStaffRow'].forEach(btnId => {
                    const btn = document.getElementById(btnId);
                    if (btn) {
                        btn.addEventListener('click', function() {
                            // Wait a moment for DOM to update, then validate the section
                            setTimeout(validateTeamSection, 100);
                        });
                    }
                });
            }
        });

        // Function to update a section's status (complete/incomplete)
        function updateSectionStatus(section, isComplete) {
            if (section && section.classList) {
                if (isComplete) {
                    section.classList.add('complete');
                    section.classList.remove('incomplete');
                } else {
                    section.classList.remove('complete');
                }
            }
        }

        // Initialize validation for all form sections
        function initializeFormValidation() {
            // Validate each section on page load
            validateBasicInfoSection();
            validateImplementingOfficeSection();
            validateSDGsSection();
            validateBeneficiariesSection();
            validateTeamSection();
            validateObjectivesSection();
            validateEvaluationSection();
            validateImageSection();
        }

        // Add change listeners to form fields for real-time validation
        function addValidationListeners() {
            // Basic Info section
            document.querySelectorAll('#year, #quarter, #title, #location, #startDate, #endDate, #startTime, #endTime').forEach(field => {
                field.addEventListener('change', validateBasicInfoSection);
            });

            // Implementing Office section
            const implementingOfficeSelect = document.getElementById('implementingOffice');
            if (implementingOfficeSelect) {
                // For Select2, we need to listen to the select2:select and select2:unselect events
                $(implementingOfficeSelect).on('select2:select select2:unselect select2:clear', function() {
                    setTimeout(validateImplementingOfficeSection, 50); // Small delay to ensure Select2 has updated

                    // Explicitly remove the field-invalid class from the Select2 container
                    $(this).next('.select2-container').find('.select2-selection').removeClass('field-invalid');
                });

                // Also listen to regular change event as fallback
                implementingOfficeSelect.addEventListener('change', function() {
                    validateImplementingOfficeSection();
                    $(this).next('.select2-container').find('.select2-selection').removeClass('field-invalid');
                });

                // Add a click handler on the Select2 container to clear validation
                $(implementingOfficeSelect).next('.select2-container').on('click', function() {
                    $(this).find('.select2-selection').removeClass('field-invalid');
                });
            }

            // Also handle the clear button click
            $(document).on('click', '.select2-selection__clear', function() {
                setTimeout(validateImplementingOfficeSection, 100);
            });

            document.getElementById('partnerAgency').addEventListener('input', validateImplementingOfficeSection);

            // For checkboxes, add the event listener with a different approach
            const agendaCheckboxes = document.querySelectorAll('.form-check-input[id^="agenda"]');
            agendaCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    validateImplementingOfficeSection();
                });
            });

            // SDGs are read-only, so no listeners needed

            // Beneficiaries section
            document.getElementById('typeBeneficiaries').addEventListener('input', validateBeneficiariesSection);
            document.querySelectorAll('input[id$="BatStateU"], input[id$="Others"]').forEach(input => {
                input.addEventListener('input', validateBeneficiariesSection);
            });

            // Team section is dynamically loaded, validation handled after loading

            // Objectives & Narrative section
            document.getElementById('activityNarrative').addEventListener('input', validateObjectivesSection);

            // Evaluation section
            document.querySelectorAll('.activity-rating, .timeliness-rating').forEach(input => {
                input.addEventListener('input', validateEvaluationSection);
            });

            // Image Upload section
            document.getElementById('imageUpload').addEventListener('change', validateImageSection);
        }

        // Validate Basic Information section
        function validateBasicInfoSection() {
            const section = document.querySelector('.form-section:nth-of-type(1)');

            // Check if all required fields have values
            const titleValue = document.getElementById('title').value;
            const locationValue = document.getElementById('location').value;
            const startDateValue = document.getElementById('startDate').value;
            const endDateValue = document.getElementById('endDate').value;

            const isComplete = titleValue && locationValue && startDateValue && endDateValue;
            updateSectionStatus(section, isComplete);

            return isComplete;
        }

        // Validate Implementing Office section
        function validateImplementingOfficeSection() {
            const section = document.querySelector('.form-section:nth-of-type(2)');

            // Check if at least one office is selected
            const offices = document.getElementById('implementingOffice');
            const hasOffices = offices && offices.selectedOptions && offices.selectedOptions.length > 0;

            // If using Select2, we need a different approach
            const isSelect2 = $(offices).hasClass('select2-hidden-accessible');
            const hasOfficesSelect2 = isSelect2 ? $(offices).val() && $(offices).val().length > 0 : hasOffices;

            // Check if at least one agenda checkbox is checked
            const hasAgenda = Array.from(document.querySelectorAll('.form-check-input[id^="agenda"]')).some(cb => cb.checked);

            // Check if partner agency field has a value
            const partnerAgency = document.getElementById('partnerAgency').value.trim();
            const hasPartnerAgency = partnerAgency !== '';

            // For debugging
            console.log('Validation - Has Offices:', hasOfficesSelect2, 'Has Agenda:', hasAgenda, 'Has Partner Agency:', hasPartnerAgency);

            // All three criteria must be met: offices, agenda, and partner agency
            const isComplete = (hasOffices || hasOfficesSelect2) && hasAgenda && hasPartnerAgency;
            updateSectionStatus(section, isComplete);

            return isComplete;
        }

        // Validate SDGs section
        function validateSDGsSection() {
            const section = document.querySelector('.form-section:nth-of-type(3)');

            // Check if at least one SDG is checked
            const hasSDGs = Array.from(document.querySelectorAll('.form-check-input[id^="sdg"]')).some(cb => cb.checked);

            updateSectionStatus(section, hasSDGs);

            return hasSDGs;
        }

        // Validate Beneficiaries section
        function validateBeneficiariesSection() {
            const section = document.querySelector('.form-section:nth-of-type(4)');

            // Check if beneficiary type is specified
            const typeBeneficiaries = document.getElementById('typeBeneficiaries').value;

            // Check if at least one beneficiary count is entered
            const hasBeneficiaries =
                document.getElementById('maleBatStateU').value ||
                document.getElementById('femaleBatStateU').value ||
                document.getElementById('maleOthers').value ||
                document.getElementById('femaleOthers').value;

            const isComplete = typeBeneficiaries && hasBeneficiaries;
            updateSectionStatus(section, isComplete);

            return isComplete;
        }

        // Validate Team section
        function validateTeamSection() {
            const section = document.querySelector('.form-section:nth-of-type(5)');

            console.log('Validating Project Team section');

            // Check if there's at least one team member in each role
            const projectLeadersContent = document.querySelector('#projectLeadersContainer');
            const assistantLeadersContent = document.querySelector('#assistantLeadersContainer');
            const staffContent = document.querySelector('#projectStaffContainer');

            // Check for actual HTML content, not just the existence of containers
            const hasProjectLeader = projectLeadersContent && projectLeadersContent.innerHTML.trim() !== '';
            const hasAssistantLeader = assistantLeadersContent && assistantLeadersContent.innerHTML.trim() !== '';
            const hasStaff = staffContent && staffContent.innerHTML.trim() !== '';

            // Also check if an activity is selected before allowing this section to be complete
            const activitySelected = document.getElementById('title').value !== '';

            console.log('Has activity selected:', activitySelected);
            console.log('Has Project Leader:', hasProjectLeader);
            console.log('Has Assistant Leader:', hasAssistantLeader);
            console.log('Has Staff:', hasStaff);

            // Only check tasks if we have team members
            let allTasksFilled = true;

            if (hasProjectLeader || hasAssistantLeader || hasStaff) {
                // Check project leader tasks
                if (hasProjectLeader) {
                    const projectLeaderTasks = projectLeadersContent.querySelectorAll('input[name="project_leader_task[]"]');
                    if (projectLeaderTasks.length > 0) {
                        projectLeaderTasks.forEach(task => {
                            if (!task.value.trim()) {
                                allTasksFilled = false;
                                console.log('Empty Project Leader task found');
                            }
                        });
                    }
                }

                // Check assistant leader tasks
                if (hasAssistantLeader) {
                    const assistantLeaderTasks = assistantLeadersContent.querySelectorAll('input[name="assistant_project_leader_task[]"]');
                    if (assistantLeaderTasks.length > 0) {
                        assistantLeaderTasks.forEach(task => {
                            if (!task.value.trim()) {
                                allTasksFilled = false;
                                console.log('Empty Assistant Leader task found');
                            }
                        });
                    }
                }

                // Check staff tasks
                if (hasStaff) {
                    const staffTasks = staffContent.querySelectorAll('input[name="project_staff_task[]"]');
                    if (staffTasks.length > 0) {
                        staffTasks.forEach(task => {
                            if (!task.value.trim()) {
                                allTasksFilled = false;
                                console.log('Empty Staff task found');
                            }
                        });
                    }
                }
            } else {
                // If we have no team members, section is not complete
                allTasksFilled = false;
            }

            console.log('All tasks filled:', allTasksFilled);

            // Section is complete only if all conditions are met
            const isComplete = activitySelected && hasProjectLeader && hasAssistantLeader && hasStaff && allTasksFilled;
            console.log('Team Section Complete:', isComplete);

            updateSectionStatus(section, isComplete);

            return isComplete;
        }

        // Validate Objectives & Narrative section
        function validateObjectivesSection() {
            const section = document.querySelector('.form-section:nth-of-type(6)');

            // Check if narrative is entered
            const narrative = document.getElementById('activityNarrative').value;

            updateSectionStatus(section, !!narrative);

            return !!narrative;
        }

        // Validate Evaluation section
        function validateEvaluationSection() {
            const section = document.querySelector('.form-section:nth-of-type(7)');

            // Check if at least one rating is entered in the activity rating table
            const hasActivityRating = Array.from(document.querySelectorAll('.activity-rating')).some(input => input.value);

            // Check if at least one rating is entered in the timeliness rating table
            const hasTimelinessRating = Array.from(document.querySelectorAll('.timeliness-rating')).some(input => input.value);

            // Section is complete only if both tables have at least one value
            const isComplete = hasActivityRating && hasTimelinessRating;

            updateSectionStatus(section, isComplete);

            return isComplete;
        }

        // Validate Image Upload section
        function validateImageSection() {
            const section = document.querySelector('.form-section:nth-of-type(8)');

            // Check if at least one image is uploaded
            const hasImages = document.querySelectorAll('.image-preview').length > 0;

            console.log('Validating images section, has images:', hasImages);

            updateSectionStatus(section, hasImages);

            return hasImages;
        }

        function handleLogout(event) {
            event.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of the system",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel',
                backdrop: `
            rgba(0,0,0,0.7)
        `,
                allowOutsideClick: true,
                customClass: {
                    container: 'swal-blur-container',
                    popup: 'logout-swal'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.body.classList.add('fade-out');

                    setTimeout(() => {
                        window.location.href = '../loading_screen.php?redirect=index.php';
                    }, 10); // Changed from 50 to 10 - make it super fast
                }
            });
        }

        // Add event listener to the addBtn
        document.getElementById('addBtn').addEventListener('click', function() {
            // The form submission is handled by the form's submit event handler
            // Clicking this button will trigger the form submit event
        });

        // Add specific event listeners for task fields to remove validation styling
        document.addEventListener('DOMContentLoaded', function() {
            // Function to add validation triggers to add/remove task buttons
            function addTaskButtonListeners() {
                // Add task buttons
                document.querySelectorAll('.add-task-btn').forEach(btn => {
                    // Clone to remove existing listeners
                    const newBtn = btn.cloneNode(true);
                    if (btn.parentNode) {
                        btn.parentNode.replaceChild(newBtn, btn);
                    }

                    // Add click event with validation
                    newBtn.addEventListener('click', function() {
                        console.log('Add task button clicked, will validate after');
                        setTimeout(validateTeamSection, 300);
                    });
                });

                // Remove task buttons (existing and future ones)
                document.addEventListener('click', function(e) {
                    if (e.target && e.target.classList.contains('remove-task-btn')) {
                        console.log('Remove task button clicked, will validate after');
                        setTimeout(validateTeamSection, 300);
                    }
                });
            }

            // Call when DOM is ready
            addTaskButtonListeners();

            // Add input event listeners to all task fields
            function setupTaskFieldListeners() {
                document.querySelectorAll('input[name$="_task[]"]').forEach(taskField => {
                    // Remove existing listeners to prevent duplicates
                    const newTaskField = taskField.cloneNode(true);
                    if (taskField.parentNode) {
                        taskField.parentNode.replaceChild(newTaskField, taskField);
                    }

                    // Add new listeners
                    newTaskField.addEventListener('input', function() {
                        this.classList.remove('field-invalid');
                        console.log('Task field validation cleared');
                        setTimeout(validateTeamSection, 100);
                    });

                    newTaskField.addEventListener('change', function() {
                        this.classList.remove('field-invalid');
                        console.log('Task field validation cleared (change)');
                        setTimeout(validateTeamSection, 100);
                    });
                });
            }

            // Initial setup
            setupTaskFieldListeners();

            // Also setup listeners when new task fields are added
            ['addProjectLeaderRow', 'addAssistantLeaderRow', 'addProjectStaffRow'].forEach(btnId => {
                const btn = document.getElementById(btnId);
                if (btn) {
                    btn.addEventListener('click', function() {
                        // Wait a moment for the DOM to update
                        setTimeout(setupTaskFieldListeners, 100);
                    });
                }
            });

            // Create a MutationObserver to watch for dynamically added task fields
            const teamSectionObserver = new MutationObserver(function(mutations) {
                let hasNewTaskFields = false;

                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        const containerIds = ['projectLeadersContainer', 'assistantLeadersContainer', 'projectStaffContainer', 'leaderTasksContainer', 'assistantTasksContainer', 'staffTasksContainer'];

                        if (containerIds.includes(mutation.target.id) ||
                            (mutation.target.parentNode && containerIds.includes(mutation.target.parentNode.id))) {
                            hasNewTaskFields = true;
                        }
                    }
                });

                if (hasNewTaskFields) {
                    console.log('Detected new task fields, setting up listeners');
                    setTimeout(setupTaskFieldListeners, 100);
                    setTimeout(validateTeamSection, 200);
                }
            });

            // Start observing the team containers
            const containers = [
                document.getElementById('projectLeadersContainer'),
                document.getElementById('assistantLeadersContainer'),
                document.getElementById('projectStaffContainer'),
                document.getElementById('leaderTasksContainer'),
                document.getElementById('assistantTasksContainer'),
                document.getElementById('staffTasksContainer')
            ];

            containers.forEach(container => {
                if (container) {
                    teamSectionObserver.observe(container, {
                        childList: true,
                        subtree: true
                    });
                }
            });

            // Force validation when the page loads
            setTimeout(function() {
                validateTeamSection();
            }, 1000);

            // Add year change event handler to populate quarters
            const yearSelect = document.getElementById('year');
            if (yearSelect) {
                yearSelect.addEventListener('change', function() {
                    // If we're in edit mode, fetch activities with the current narrative ID
                    if (currentEditingNarrativeId > 0) {
                        // If quarter has a value, fetch activities with the narrative ID
                        if (quarterSelect.value) {
                            fetchActivities(this.value, quarterSelect.value, currentEditingNarrativeId);
                        }
                    }

                    const year = this.value;

                    if (year) {
                        console.log(`Year changed to ${year}, loading quarters...`);
                        // Enable quarter select
                        const quarterSelect = document.getElementById('quarter');
                        if (quarterSelect) {
                            quarterSelect.disabled = false;
                            quarterSelect.classList.remove('non-interactive');
                            // Clear existing options
                            quarterSelect.innerHTML = '<option value="" selected disabled>Select Quarter</option>';

                            // Add quarters Q1-Q4
                            ['Q1', 'Q2', 'Q3', 'Q4'].forEach(quarter => {
                                const option = document.createElement('option');
                                option.value = quarter;
                                option.textContent = quarter;
                                quarterSelect.appendChild(option);
                            });

                            // Add event listener to quarter select if not already added
                            if (!quarterSelect.hasAttribute('data-has-listener')) {
                                quarterSelect.addEventListener('change', function() {
                                    const quarter = this.value;
                                    const currentYear = yearSelect.value; // Get current year value

                                    // If we're in edit mode, fetch activities with the current narrative ID
                                    if (currentEditingNarrativeId > 0) {
                                        // If quarter has a value, fetch activities with the narrative ID
                                        if (quarterSelect.value) {
                                            fetchActivities(this.value, quarterSelect.value, currentEditingNarrativeId);
                                        }
                                    }

                                    if (quarter && currentYear) {
                                        console.log(`Quarter changed to ${quarter}, year is ${currentYear}, fetching activities...`);
                                        // Fetch activities for the selected year and quarter
                                        fetchActivities(currentYear, quarter);

                                        // Enable title select
                                        const titleSelect = document.getElementById('title');
                                        if (titleSelect) {
                                            titleSelect.disabled = false;
                                            titleSelect.classList.remove('non-interactive');
                                        }
                                    }
                                });
                                quarterSelect.setAttribute('data-has-listener', 'true'); // Mark as having listener
                            }
                        }
                    }
                });
            }
        });
    </script>

    <!-- Modal for narratives -->
    <div class="modal-overlay" id="narrativeModalOverlay" style="display: none;" onclick="closeNarrativeModal()">
        <div class="narrative-modal" onclick="event.stopPropagation()">
            <div class="narrative-modal-header">
                <h3 id="narrativeModalTitle">Narratives</h3>
                <button class="close-modal-btn" onclick="closeNarrativeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="narrative-modal-body">
                <!-- Filters -->
                <div class="filter-container">
                    <div class="filter-row">
                        <div class="filter-item">
                            <label for="filter-activity">Activity Name:</label>
                            <input type="text" id="filter-activity" class="form-control" placeholder="Search activities..." onkeyup="loadNarrativeData()">
                        </div>
                        <div class="filter-item">
                            <label for="filter-campus">Campus:</label>
                            <select id="filter-campus" class="form-control" onchange="loadNarrativeData()">
                                <option value="">All Campuses</option>
                                <option value="Lipa">Lipa</option>
                                <option value="Pablo Borbon">Pablo Borbon</option>
                                <option value="Alangilan">Alangilan</option>
                                <option value="Nasugbu">Nasugbu</option>
                                <option value="Malvar">Malvar</option>
                                <option value="Rosario">Rosario</option>
                                <option value="Balayan">Balayan</option>
                                <option value="Lemery">Lemery</option>
                                <option value="San Juan">San Juan</option>
                                <option value="Lobo">Lobo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="narrative-table-wrapper view-mode">
                    <table class="narrative-table">
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Partner Agency</th>
                                <?php if (isset($_SESSION['username']) && $_SESSION['username'] === 'Central'): ?>
                                    <th>Campus</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="narrative-tbody">
                            <!-- Narrative data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="narrative-modal-footer">
                <div class="pagination-container">
                    <nav aria-label="Page navigation">
                        <ul class="pagination" id="narrativesPagination">
                            <!-- Pagination will be populated dynamically -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .narrative-modal {
            width: 90%;
            max-width: 1200px;
            height: 80vh;
            /* Decreased from 90vh */
            max-height: 750px;
            /* Decreased from 900px */
            background-color: var(--card-bg, #ffffff);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-top: 5px solid var(--accent-color, #6a1b9a);
            transform: translateY(20px);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .modal-overlay.active .narrative-modal {
            transform: translateY(0);
            opacity: 1;
        }

        .narrative-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color, #dee2e6);
            flex-shrink: 0;
        }

        .narrative-modal-header h3 {
            margin: 0;
            color: var(--accent-color, #6a1b9a);
            font-size: 1.25rem;
            text-align: center;
            flex-grow: 1;
        }

        .close-modal-btn {
            background: transparent;
            border: none;
            color: var(--text-primary, #212529);
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.2s;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-modal-btn:hover {
            background-color: rgba(0, 0, 0, 0.1);
            color: var(--accent-color, #6a1b9a);
        }

        .narrative-modal-body {
            padding: 0.75rem 1.5rem 0;
            /* Reduced top padding and removed bottom padding */
            overflow-y: auto;
            flex: 1;
            min-height: 300px;
            /* Hide scrollbar but maintain scroll functionality */
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
            padding-bottom: 0;
            display: flex;
            flex-direction: column;
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .narrative-modal-body::-webkit-scrollbar {
            display: none;
        }

        .narrative-table-wrapper {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid var(--border-color, #dee2e6);
            margin-bottom: 0;
            height: calc(100% - 120px);
            /* Further reduced subtraction */
            min-height: 300px;
            /* Hide scrollbar but maintain scroll functionality */
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .narrative-table-wrapper::-webkit-scrollbar {
            display: none;
        }

        .narrative-modal-footer {
            padding: 0.75rem 0 0.5rem;
            border-top: 1px solid var(--border-color, #dee2e6);
            background-color: var(--card-bg, #ffffff);
            border-radius: 0 0 15px 15px;
            flex-shrink: 0;
            margin-top: 0.75rem;
            /* Reduced from 1rem */
            width: 100%;
        }

        .filter-container {
            margin-bottom: 0.4rem;
            /* Further reduced margin */
            background-color: var(--bg-secondary, #e9ecef);
            padding: 0.4rem 0.75rem;
            /* Reduced padding */
            border-radius: 10px;
            flex-shrink: 0;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .filter-item {
            flex: 1;
            min-width: 200px;
        }

        .filter-item label {
            display: block;
            margin-bottom: 0.25rem;
            color: var(--text-primary, #212529);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .narrative-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Fixed layout to control column widths */
        }

        /* For campus users (non-central), adjust column widths */
        .narrative-table.campus-user th:nth-child(1),
        .narrative-table.campus-user td:nth-child(1) {
            width: 50%;
            /* Activity column takes half the width */
        }

        .narrative-table.campus-user th:nth-child(2),
        .narrative-table.campus-user td:nth-child(2) {
            width: 50%;
            /* Partner Agency column takes half the width */
        }

        /* For central users, adjust column widths */
        .narrative-table.central-user th:nth-child(1),
        .narrative-table.central-user td:nth-child(1) {
            width: 40%;
            /* Activity column takes 40% width */
        }

        .narrative-table.central-user th:nth-child(2),
        .narrative-table.central-user td:nth-child(2) {
            width: 35%;
            /* Partner Agency column takes 35% width */
        }

        .narrative-table.central-user th:nth-child(3),
        .narrative-table.central-user td:nth-child(3) {
            width: 25%;
            /* Campus column takes 25% width */
        }

        .narrative-table th,
        .narrative-table td {
            padding: 0.85rem 1rem;
            /* Reduced padding */
            text-align: left;
            border-bottom: 1px solid var(--border-color, #dee2e6);
        }

        .narrative-table th {
            background-color: var(--accent-color, #6a1b9a);
            color: white;
            font-weight: 500;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .narrative-table tr:last-child td {
            border-bottom: none;
        }

        .narrative-table tr:hover {
            background-color: var(--hover-color, rgba(106, 27, 154, 0.1));
        }

        /* Styles for different modal modes */
        .narrative-table-wrapper.view-mode tr {
            cursor: default;
        }

        /* Remove hover effect for view mode */
        .narrative-table-wrapper.view-mode tr:hover {
            background-color: transparent !important;
        }

        .narrative-table-wrapper.edit-mode tr {
            cursor: pointer;
        }

        .narrative-table-wrapper.delete-mode tr {
            cursor: pointer;
        }

        /* Pagination styles */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            border: 1px solid var(--border-color, #dee2e6);
            border-radius: 4px;
            overflow: hidden;
        }

        .page-item {
            margin: 0;
            border-right: 1px solid var(--border-color, #dee2e6);
        }

        .page-item:last-child {
            border-right: none;
        }

        .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 0;
            background-color: var(--bg-secondary, #e9ecef);
            color: var(--text-primary, #212529);
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .page-link:hover {
            background-color: var(--accent-color, #6a1b9a);
            color: white;
        }

        .page-item.active .page-link {
            background-color: var(--accent-color, #6a1b9a);
            color: white;
        }

        .page-item.disabled .page-link {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-info {
            text-align: center;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-secondary, #6c757d);
        }

        /* Special styling for the approval link - only visible to Central users */
        .approval-link {
            background-color: var(--accent-color);
            color: white !important;
            border-radius: 12px;
            margin-top: 10px;
            font-weight: 600;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .approval-link::before {
            content: '';
            position: absolute;
            right: -20px;
            top: 0;
            width: 40px;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            transform: skewX(-25deg);
            opacity: 0.7;
            transition: all 0.5s ease;
        }

        .approval-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            background-color: var(--accent-hover) !important;
            color: white !important;
        }

        .approval-link:hover::before {
            right: 100%;
        }

        /* Ensure the icon in approval link stands out */
        .approval-link i {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .approval-link:hover i {
            transform: scale(1.2);
        }

        /* Dark theme adjustments for approval link */
        [data-bs-theme="dark"] .approval-link {
            background-color: var(--accent-color);
        }

        [data-bs-theme="dark"] .approval-link:hover {
            background-color: var(--accent-hover) !important;
        }

        /* Revamped active state - distinctive but elegant */
        .approval-link.active {
            background-color: transparent !important;
            color: white !important;
            border: 2px solid white;
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: visible;
        }

        .approval-link.active::before {
            display: none;
        }

        .approval-link.active i {
            color: white;
        }

        /* Dark theme revamped active state */
        [data-bs-theme="dark"] .approval-link.active {
            background-color: transparent !important;
            color: white !important;
            border: 2px solid #e0b6ff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
        }

        [data-bs-theme="dark"] .approval-link.active i {
            color: #e0b6ff;
        }

        /* Fixed active state using accent color */
        .approval-link.active {
            background-color: transparent !important;
            color: var(--accent-color) !important;
            border: 2px solid var(--accent-color);
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .approval-link.active i {
            color: var(--accent-color);
        }

        /* Dark theme with accent color */
        [data-bs-theme="dark"] .approval-link.active {
            background-color: transparent !important;
            color: white !important;
            border: 2px solid var(--accent-color);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
        }

        [data-bs-theme="dark"] .approval-link.active i {
            color: var(--accent-color);
        }
    </style>

    <script>
        // Global variables for pagination
        let currentPage = 1;
        let totalPages = 1;
        let narrativeData = [];
        const rowsPerPage = 5;
        let currentModalMode = 'view'; // can be 'view', 'edit', or 'delete'
        let selectedNarrativeId = null;
        let isCentralUser = <?php echo isset($_SESSION['username']) && $_SESSION['username'] === 'Central' ? 'true' : 'false'; ?>;
        let userCampus = "<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>";

        function updateNotificationBadge(endpoint, action, badgeId) {
            const badge = document.getElementById(badgeId);
            if (!badge) return;

            const formData = new FormData();
            formData.append('action', action);

            fetch(endpoint, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.style.display = 'flex';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                })
                .catch(error => console.error('Error fetching count:', error));
        }

        // Initial load and periodic updates
        document.addEventListener('DOMContentLoaded', function() {
            // For approval badge
            updateNotificationBadge('../approval/gbp_api.php', 'count_pending', 'approvalBadge');

            // Set interval for updates (only if not on the page with that badge active)
            const isApprovalPage = document.querySelector('.approval-link.active');
            if (!isApprovalPage) {
                setInterval(() => {
                    updateNotificationBadge('../approval/gbp_api.php', 'count_pending', 'approvalBadge');
                }, 30000); // Update every 30 seconds
            }
        });

        // Function to ensure the campus filter is interactive for Central users
        function enableCampusFilter() {
            const campusFilter = document.getElementById('filter-campus');
            const activityFilter = document.getElementById('filter-activity');

            if (campusFilter) {
                if (isCentralUser) {
                    // Enable the campus filter for Central users
                    campusFilter.disabled = false;
                    campusFilter.readOnly = false;
                    campusFilter.style.pointerEvents = 'auto';
                    campusFilter.style.opacity = '1';
                    campusFilter.style.removeProperty('background-color');
                    campusFilter.style.removeProperty('color');
                    campusFilter.classList.remove('central-disabled');
                    campusFilter.classList.remove('readonly');
                    campusFilter.classList.remove('bg-secondary-subtle');
                } else {
                    // Disable and set to user's campus for non-Central users
                    campusFilter.value = userCampus;
                    campusFilter.disabled = true;
                    campusFilter.readOnly = true;
                    campusFilter.style.pointerEvents = 'none';
                    campusFilter.style.opacity = '0.7';
                    // Remove inline styles and rely on the CSS classes for proper theming
                    campusFilter.style.removeProperty('background-color');
                    campusFilter.style.removeProperty('color');
                    campusFilter.classList.add('readonly');
                    campusFilter.classList.add('bg-secondary-subtle');
                }
            }
        }

        // Function to open the narrative modal
        function openNarrativeModal(mode = 'view') {
            // Set the modal mode
            currentModalMode = mode;

            // Update the modal title based on mode
            const modalTitle = document.getElementById('narrativeModalTitle');
            switch (mode) {
                case 'edit':
                    modalTitle.textContent = 'Edit Narratives';
                    break;
                case 'delete':
                    modalTitle.textContent = 'Delete Narratives';
                    break;
                default:
                    modalTitle.textContent = 'View Narratives';
            }

            const modalOverlay = document.getElementById('narrativeModalOverlay');
            modalOverlay.style.display = 'flex';

            // Update table wrapper class based on mode
            const tableWrapper = document.querySelector('.narrative-table-wrapper');
            tableWrapper.classList.remove('view-mode', 'edit-mode', 'delete-mode');
            tableWrapper.classList.add(`${mode}-mode`);

            // Update table class based on user type
            const table = document.querySelector('.narrative-table');
            table.classList.remove('campus-user', 'central-user');
            table.classList.add(isCentralUser ? 'central-user' : 'campus-user');

            // Trigger the CSS transition by adding the active class after a small delay
            setTimeout(() => {
                modalOverlay.classList.add('active');

                // Ensure campus filter is interactive for Central users
                enableCampusFilter();
            }, 10);

            document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal

            // Load narrative data
            loadNarrativeData();
        }

        // Function to close the narrative modal
        function closeNarrativeModal() {
            const modalOverlay = document.getElementById('narrativeModalOverlay');
            modalOverlay.classList.remove('active');
            // Wait for the transition to complete before hiding the element
            setTimeout(() => {
                modalOverlay.style.display = 'none';
                document.body.style.overflow = 'auto'; // Restore scrolling
            }, 300); // Match this to the transition duration in CSS
        }

        // Add a debounce function to prevent excessive filtering
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        // Function to load narrative data with debouncing
        const debouncedLoadData = debounce(function() {
            const activityFilter = document.getElementById('filter-activity').value;
            const campusFilter = document.getElementById('filter-campus').value;

            // Show loading indicator
            const tbody = document.getElementById('narrative-tbody');
            const colspanValue = isCentralUser ? 3 : 2;

            // Fetch real data from server via AJAX
            fetch(`narrative.php?action=get_narratives&activity_filter=${encodeURIComponent(activityFilter)}&campus_filter=${encodeURIComponent(campusFilter)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Update global narrative data
                    narrativeData = data;

                    // Calculate total pages
                    totalPages = Math.ceil(narrativeData.length / rowsPerPage);

                    // Reset to first page when filters change
                    currentPage = 1;

                    // Update display
                    renderCurrentPage();
                })
                .catch(error => {
                    console.error('Error fetching narrative data:', error);
                    tbody.innerHTML = `<tr><td colspan="${colspanValue}" class="text-center text-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Error loading data. Please try again.
                </td></tr>`;
                });
        }, 300); // Wait 300ms after typing stops before filtering

        // Replace original function with debounced version
        function loadNarrativeData() {
            debouncedLoadData();
        }

        // Function to render the current page of data
        function renderCurrentPage() {
            const tbody = document.getElementById('narrative-tbody');
            tbody.innerHTML = '';

            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = Math.min(startIndex + rowsPerPage, narrativeData.length);

            // Set the colspan for empty data message based on whether campus column is visible
            const colspanValue = isCentralUser ? 3 : 2;

            if (narrativeData.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${colspanValue}" class="text-center">No data found</td></tr>`;
                return;
            }

            for (let i = startIndex; i < endIndex; i++) {
                const item = narrativeData[i];
                const row = document.createElement('tr');

                // Add row click handler based on mode
                if (currentModalMode === 'edit') {
                    row.onclick = function() {
                        // Fetch and load data for editing
                        loadNarrativeForEditing(item.id);
                        // Close the modal
                        closeNarrativeModal();
                    };
                    row.style.cursor = 'pointer';
                } else if (currentModalMode === 'delete') {
                    row.onclick = function() {
                        // Show delete confirmation dialog
                        confirmDeleteNarrative(item);
                    };
                    row.style.cursor = 'pointer';
                }

                let rowHTML = `
                <td>${item.activity}</td>
                <td>${item.partner_agency}</td>`;

                // Add the campus column only for Central users
                if (isCentralUser) {
                    rowHTML += `<td>${item.campus}</td>`;
                }

                row.innerHTML = rowHTML;
                tbody.appendChild(row);
            }

            // Update pagination
            updatePagination();
        }

        // Function to update pagination
        function updatePagination() {
            // Get the pagination container
            const paginationContainer = document.querySelector('.pagination-container');
            paginationContainer.innerHTML = '';

            // Create pagination element
            const pagination = document.createElement('nav');
            pagination.setAttribute('aria-label', 'Page navigation');

            const ul = document.createElement('ul');
            ul.className = 'pagination';

            // Previous button
            const prevLi = document.createElement('li');
            prevLi.className = 'page-item' + (currentPage <= 1 ? ' disabled' : '');

            const prevLink = document.createElement('a');
            prevLink.className = 'page-link';
            prevLink.innerHTML = '&laquo;'; // Updated to match ppas_proposal/gad_proposal.php
            prevLink.href = '#';
            prevLink.setAttribute('aria-label', 'Previous');

            if (currentPage > 1) {
                prevLink.onclick = function(e) {
                    e.preventDefault();
                    prevPage();
                };
            }

            prevLi.appendChild(prevLink);
            ul.appendChild(prevLi);

            // Determine which page numbers to show
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);

            // Adjust startPage if needed to always show 5 boxes (if possible)
            if (endPage - startPage < 4 && startPage > 1) {
                startPage = Math.max(1, endPage - 4);
            }

            // Page numbers - show 5 boxes at a time
            for (let i = startPage; i <= endPage; i++) {
                const li = document.createElement('li');
                li.className = 'page-item' + (i === currentPage ? ' active' : '');

                const link = document.createElement('a');
                link.className = 'page-link';
                link.href = '#';
                link.textContent = i;

                if (i !== currentPage) {
                    link.onclick = function(e) {
                        e.preventDefault();
                        currentPage = i;
                        renderCurrentPage();
                    };
                }

                li.appendChild(link);
                ul.appendChild(li);
            }

            // Next button
            const nextLi = document.createElement('li');
            nextLi.className = 'page-item' + (currentPage >= totalPages ? ' disabled' : '');

            const nextLink = document.createElement('a');
            nextLink.className = 'page-link';
            nextLink.innerHTML = '&raquo;'; // Updated to match ppas_proposal/gad_proposal.php
            nextLink.href = '#';
            nextLink.setAttribute('aria-label', 'Next');

            if (currentPage < totalPages) {
                nextLink.onclick = function(e) {
                    e.preventDefault();
                    nextPage();
                };
            }

            nextLi.appendChild(nextLink);
            ul.appendChild(nextLi);

            pagination.appendChild(ul);
            paginationContainer.appendChild(pagination);
        }

        // Navigation functions
        function prevPage() {
            if (currentPage > 1) {
                currentPage--;
                renderCurrentPage();
            }
        }

        function nextPage() {
            if (currentPage < totalPages) {
                currentPage++;
                renderCurrentPage();
            }
        }

        // Function to handle editing a narrative
        function loadNarrativeForEditing(id) {
            console.log(`Loading narrative ID ${id} for editing`);

            currentEditingNarrativeId = id;

            // Close the narrative modal
            closeNarrativeModal();

            // Clear personnel containers first to avoid stale data
            if (typeof clearPersonnelContainers === 'function') {
                console.log("Clearing personnel containers before edit");
                clearPersonnelContainers();
            } else {
                console.error("clearPersonnelContainers function not found before edit");
                // Fallback manual clear if function not available
                const containers = [
                    'projectLeadersContainer',
                    'assistantLeadersContainer',
                    'projectStaffContainer'
                ];
                containers.forEach(containerId => {
                    const container = document.getElementById(containerId);
                    if (container) {
                        console.log(`Manually clearing ${containerId}`);
                        container.innerHTML = '';
                    } else {
                        console.warn(`Container ${containerId} not found for clearing`);
                    }
                });
            }

            // Set form to edit mode
            setFormEditMode(true);

            // Fetch narrative details from server
            fetch(`narrative.php?action=get_narrative_details&id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Store the narrative ID for form submission
                        window.editingNarrativeId = id;

                        // Store the narrative data for later task application
                        window.narrativeData = data.data;

                        // Populate form with narrative data
                        populateFormWithData(data.data, id);

                        // Wait for the activity details to be fetched and personnel populated
                        setTimeout(() => {
                            // Apply tasks if the function exists
                            if (typeof window.applyNarrativeTasks === 'function') {
                                console.log("Applying narrative tasks after personnel creation");
                                window.applyNarrativeTasks(data.data);
                            } else {
                                console.error("applyNarrativeTasks function not found");
                            }
                        }, 2000); // Give enough time for personnel to be created

                        // Scroll to form
                        document.querySelector('.card').scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to load narrative data.',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading narrative data:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while loading the narrative data.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                });
        }

        // Function to set form to edit mode
        function setFormEditMode(isEditing) {
            // Change form title
            const cardTitle = document.querySelector('.card-title');
            if (cardTitle) {
                cardTitle.textContent = isEditing ? 'Edit Narrative Form' : 'Add Narrative Form';
            }

            // Get buttons
            const addBtn = document.getElementById('addBtn');
            const editBtn = document.getElementById('editBtn');
            const deleteBtn = document.getElementById('deleteBtn');

            // Get dropdown elements
            const yearSelect = document.getElementById('year');
            const quarterSelect = document.getElementById('quarter');
            const activitySelect = document.getElementById('title'); // Changed from 'activity' to 'title'

            if (isEditing) {
                // Update add button to become update button
                if (addBtn) {
                    addBtn.innerHTML = '<i class="fas fa-save"></i>';
                    addBtn.title = 'Update Narrative';
                    addBtn.classList.add('btn-update');
                }

                // Update edit button to become a cancel button
                if (editBtn) {
                    editBtn.innerHTML = '<i class="fas fa-times"></i>';
                    editBtn.title = 'Cancel Editing';
                    editBtn.classList.add('editing');

                    // Change event listener to cancel editing
                    const newEditBtn = editBtn.cloneNode(true);
                    editBtn.parentNode.replaceChild(newEditBtn, editBtn);
                    newEditBtn.addEventListener('click', cancelEditing);
                }

                // Disable delete button
                if (deleteBtn) {
                    deleteBtn.disabled = true;
                    deleteBtn.classList.add('disabled');
                    deleteBtn.style.opacity = '0.5';
                    deleteBtn.style.cursor = 'not-allowed';
                    deleteBtn.style.pointerEvents = 'none';
                }

                // Update form action to update instead of add
                const form = document.getElementById('ppasForm');
                if (form) {
                    form.dataset.mode = 'edit';
                }

                // Disable year, quarter, and activity dropdown fields in edit mode
                if (yearSelect) {
                    yearSelect.disabled = true;
                    yearSelect.classList.add('non-interactive');
                }

                if (quarterSelect) {
                    quarterSelect.disabled = true;
                    quarterSelect.classList.add('non-interactive');
                }

                if (activitySelect) {
                    activitySelect.disabled = true;
                    activitySelect.classList.add('non-interactive');
                }

                // Set up task field validation in edit mode
                setTimeout(() => {
                    // Attach event listeners to task fields
                    document.querySelectorAll('input[name$="_task[]"]').forEach(field => {
                        // Remove any existing listeners
                        const newField = field.cloneNode(true);
                        if (field.parentNode) {
                            field.parentNode.replaceChild(newField, field);
                        }

                        // Add input and change listeners for validation
                        newField.addEventListener('input', function() {
                            setTimeout(validateTeamSection, 100);
                        });

                        newField.addEventListener('change', function() {
                            setTimeout(validateTeamSection, 100);
                        });
                    });

                    // Add event listeners to task add/remove buttons
                    document.querySelectorAll('.add-task-btn').forEach(btn => {
                        // Clone to remove any existing listeners
                        const newBtn = btn.cloneNode(true);
                        if (btn.parentNode) {
                            btn.parentNode.replaceChild(newBtn, btn);
                        }

                        // Add click event that triggers validation
                        newBtn.addEventListener('click', function() {
                            setTimeout(validateTeamSection, 300);
                        });
                    });

                    // Run validation for team section
                    validateTeamSection();
                }, 1500); // Give enough time for data to load
            } else {
                // Reset buttons to original state
                if (addBtn) {
                    addBtn.innerHTML = '<i class="fas fa-plus"></i>';
                    addBtn.title = 'Add Narrative';
                    addBtn.classList.remove('btn-update');
                }

                if (editBtn) {
                    editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                    editBtn.title = 'Edit Narratives';
                    editBtn.classList.remove('editing');

                    // Restore original event listener
                    const newEditBtn = editBtn.cloneNode(true);
                    editBtn.parentNode.replaceChild(newEditBtn, editBtn);
                    newEditBtn.addEventListener('click', function() {
                        openNarrativeModal('edit');
                    });
                }

                if (deleteBtn) {
                    deleteBtn.disabled = false;
                    deleteBtn.classList.remove('disabled');
                    deleteBtn.style.opacity = '';
                    deleteBtn.style.cursor = '';
                    deleteBtn.style.pointerEvents = '';
                }

                // Reset form action
                const form = document.getElementById('ppasForm');
                if (form) {
                    form.dataset.mode = 'add';
                }

                // Re-enable year, quarter, and activity dropdown fields when exiting edit mode
                if (yearSelect) {
                    yearSelect.disabled = false;
                    yearSelect.classList.remove('non-interactive');
                }

                if (quarterSelect && yearSelect && yearSelect.value) {
                    quarterSelect.disabled = false;
                    quarterSelect.classList.remove('non-interactive');
                } else if (quarterSelect) {
                    quarterSelect.disabled = true;
                    quarterSelect.classList.add('non-interactive');
                }

                if (activitySelect && quarterSelect && quarterSelect.value) {
                    activitySelect.disabled = false;
                    activitySelect.classList.remove('non-interactive');
                } else if (activitySelect) {
                    activitySelect.disabled = true;
                    activitySelect.classList.add('non-interactive');
                }

                // Clear editing ID
                window.editingNarrativeId = null;
            }
        }

        // Function to cancel editing
        function cancelEditing() {
            console.log("Cancelling edit mode...");

            // Reset form fields
            document.getElementById('ppasForm').reset();

            // Clear all validation indicators - be comprehensive
            document.querySelectorAll('.section-status').forEach(status => {
                status.className = 'section-status';
                status.textContent = '';
            });

            // Remove any field validation indicators
            document.querySelectorAll('.field-valid, .field-invalid').forEach(field => {
                field.classList.remove('field-valid', 'field-invalid');
            });

            // Remove validation classes from sections
            document.querySelectorAll('.form-section').forEach(section => {
                section.classList.remove('complete', 'incomplete', 'valid', 'invalid');
                // Find and reset status badges
                const badge = section.querySelector('.form-section-header .badge');
                if (badge) {
                    badge.className = 'badge rounded-pill';
                    badge.textContent = '';
                }
            });

            // Reset section headers to default state
            document.querySelectorAll('.form-section-header').forEach(header => {
                header.classList.remove('complete-header', 'incomplete-header');
            });

            // Clear personnel containers
            if (typeof clearPersonnelContainers === 'function') {
                clearPersonnelContainers();
            } else {
                const containers = [
                    'projectLeadersContainer',
                    'assistantLeadersContainer',
                    'projectStaffContainer'
                ];
                containers.forEach(containerId => {
                    const container = document.getElementById(containerId);
                    if (container) container.innerHTML = '';
                });
            }

            // Clear images from the activity images section
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            if (imagePreviewContainer) {
                imagePreviewContainer.innerHTML = '';
            }

            // Reset counters and totals
            const totalFemalesElement = document.getElementById('totalFemales');
            const totalMalesElement = document.getElementById('totalMales');
            const totalBeneficiariesElement = document.getElementById('totalBeneficiaries');

            if (totalFemalesElement) totalFemalesElement.textContent = '0';
            if (totalMalesElement) totalMalesElement.textContent = '0';
            if (totalBeneficiariesElement) totalBeneficiariesElement.textContent = '0';

            // Reset activity ratings
            document.querySelectorAll('.activity-rating-input').forEach(input => {
                input.value = '0';
            });

            // Reset timeliness ratings
            document.querySelectorAll('.timeliness-rating-input').forEach(input => {
                input.value = '0';
            });

            // Reset implementing offices
            document.querySelectorAll('.checkbox-item').forEach(item => {
                const checkbox = item.querySelector('input[type="checkbox"]');
                if (checkbox) checkbox.checked = false;
                item.classList.remove('checked');
            });

            // Reset Select2 elements
            if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                $('select.select2').each(function() {
                    $(this).val(null).trigger('change');
                });
            }

            // Reset any hidden fields or other state
            window.editingNarrativeId = null;
            window.narrativePpasFormId = null;

            // Get year and quarter for re-fetching activities
            const yearSelect = document.getElementById('year');
            const quarterSelect = document.getElementById('quarter');
            const titleSelect = document.getElementById('title');

            // Reset UI to non-edit mode
            setFormEditMode(false);

            // Store current year and quarter values before resetting
            const currentYear = yearSelect ? yearSelect.value : null;
            const currentQuarter = quarterSelect ? quarterSelect.value : null;

            // Make sure year change handler is working
            if (yearSelect) {
                // Re-register the year change event to ensure it works after canceling
                const newYearSelect = yearSelect.cloneNode(true);
                yearSelect.parentNode.replaceChild(newYearSelect, yearSelect);

                // Add event listener to the new year select
                newYearSelect.addEventListener('change', function() {
                    // If we're in edit mode, fetch activities with the current narrative ID
                    if (currentEditingNarrativeId > 0) {
                        // If quarter has a value, fetch activities with the narrative ID
                        if (quarterSelect.value) {
                            fetchActivities(this.value, quarterSelect.value, currentEditingNarrativeId);
                        }
                    }

                    const year = this.value;

                    if (year) {
                        console.log(`Year changed to ${year}, loading quarters...`);
                        // Enable quarter select
                        const quarterSelect = document.getElementById('quarter');
                        if (quarterSelect) {
                            quarterSelect.disabled = false;
                            quarterSelect.classList.remove('non-interactive');
                            // Clear existing options
                            quarterSelect.innerHTML = '<option value="" selected disabled>Select Quarter</option>';

                            // Add quarters Q1-Q4
                            ['Q1', 'Q2', 'Q3', 'Q4'].forEach(quarter => {
                                const option = document.createElement('option');
                                option.value = quarter;
                                option.textContent = quarter;
                                quarterSelect.appendChild(option);
                            });

                            // Add event listener to quarter select if not already added
                            if (!quarterSelect.hasAttribute('data-has-listener')) {
                                quarterSelect.addEventListener('change', function() {
                                    const quarter = this.value;
                                    const currentYear = newYearSelect.value; // Get current year value

                                    // If we're in edit mode, fetch activities with the current narrative ID
                                    if (currentEditingNarrativeId > 0) {
                                        // If quarter has a value, fetch activities with the narrative ID
                                        if (quarterSelect.value) {
                                            fetchActivities(this.value, quarterSelect.value, currentEditingNarrativeId);
                                        }
                                    }

                                    if (quarter && currentYear) {
                                        console.log(`Quarter changed to ${quarter}, year is ${currentYear}, fetching activities...`);
                                        // Fetch activities for the selected year and quarter
                                        fetchActivities(currentYear, quarter);

                                        // Enable title select
                                        const titleSelect = document.getElementById('title');
                                        if (titleSelect) {
                                            titleSelect.disabled = false;
                                            titleSelect.classList.remove('non-interactive');
                                        }
                                    }
                                });
                                quarterSelect.setAttribute('data-has-listener', 'true'); // Mark as having listener
                            }
                        }
                    }
                });

                // If we had a year and quarter value, restore them and re-fetch activities
                if (currentYear && currentQuarter) {
                    console.log(`Restoring values: year=${currentYear}, quarter=${currentQuarter}`);
                    newYearSelect.value = currentYear;

                    // Restore quarter options
                    if (quarterSelect) {
                        quarterSelect.disabled = false;
                        quarterSelect.classList.remove('non-interactive');
                        quarterSelect.innerHTML = '<option value="" selected disabled>Select Quarter</option>';

                        // Add quarters Q1-Q4
                        ['Q1', 'Q2', 'Q3', 'Q4'].forEach(quarter => {
                            const option = document.createElement('option');
                            option.value = quarter;
                            option.textContent = quarter;
                            quarterSelect.appendChild(option);
                        });

                        quarterSelect.value = currentQuarter;

                        // Re-fetch activities with current year and quarter
                        setTimeout(() => {
                            console.log("Re-fetching activities after cancel");
                            fetchActivities(currentYear, currentQuarter);

                            // Enable title select
                            if (titleSelect) {
                                titleSelect.disabled = false;
                                titleSelect.classList.remove('non-interactive');

                                // Ensure missing GAD options are properly styled after refetching
                                setTimeout(() => {
                                    console.log("Ensuring proper styling for missing GAD options");
                                    if (titleSelect && titleSelect.options.length > 0) {
                                        Array.from(titleSelect.options).forEach(option => {
                                            // Check for data attribute or class name to handle all cases
                                            if (option.getAttribute('data-missing-gad') === '1' ||
                                                option.textContent.includes('(No GAD proposal)') ||
                                                option.className.includes('missing-gad-option')) {

                                                option.className = 'missing-gad-option';
                                                option.setAttribute('data-missing-gad', '1');
                                                option.disabled = true;

                                                // Also ensure the text has the "(No GAD proposal)" suffix if not already present
                                                if (!option.textContent.includes('(No GAD proposal)')) {
                                                    option.textContent = `${option.textContent} (No GAD proposal)`;
                                                }
                                            }
                                        });
                                    }
                                }, 500); // Wait for fetchActivities to complete
                            }
                        }, 100);
                    }
                }
            }

            // Reopen the edit narratives modal
            setTimeout(() => {
                // Set flag in localStorage to open edit modal after refresh
                localStorage.setItem('openEditModal', 'true');

                // Refresh the page
                window.location.reload();
            }, 300);
        }

        // Function to populate form with narrative data
        function populateFormWithData(data, narrativeId = 0) {
            console.log('Populating form with data:', data, 'narrativeId:', narrativeId);

            // Set narrative ID in hidden field for edit mode
            if (narrativeId) {
                const hiddenNarrativeField = document.getElementById('hiddenNarrativeId');
                if (hiddenNarrativeField) {
                    hiddenNarrativeField.value = narrativeId;
                    console.log('Setting hidden narrative ID field to:', narrativeId);
                }
            }

            // Store the ppas_form_id for direct access
            window.narrativePpasFormId = data.ppas_form_id;

            // Enable form elements first
            const yearSelect = document.getElementById('year');
            const quarterSelect = document.getElementById('quarter');
            const titleSelect = document.getElementById('title');

            if (yearSelect) {
                yearSelect.disabled = true;
                yearSelect.classList.remove('non-interactive');
                yearSelect.value = data.year;

                console.log(`Set year to: ${data.year}`);

                // Make sure quarters are populated before setting quarter value
                if (quarterSelect) {
                    // Clear existing options first
                    quarterSelect.innerHTML = '<option value="" selected disabled>Select Quarter</option>';

                    // Add quarters Q1-Q4
                    ['Q1', 'Q2', 'Q3', 'Q4'].forEach(quarter => {
                        const option = document.createElement('option');
                        option.value = quarter;
                        option.textContent = quarter;
                        quarterSelect.appendChild(option);
                    });

                    // Now enable and set the quarter value
                    quarterSelect.disabled = true;
                    quarterSelect.classList.remove('non-interactive');
                    quarterSelect.value = data.quarter;
                    console.log(`Set quarter to: ${data.quarter}`);
                }
            }

            if (titleSelect) {
                titleSelect.disabled = true;
                titleSelect.classList.remove('non-interactive');
            }

            // Instead of triggering events, directly call the fetchActivities function
            // Create a direct approach to fetch activities and set the value

            // IMPORTANT: Create a special callback function to be called when activities are loaded
            window.activityLoadedCallback = function(success) {
                console.log(`Activities loaded callback (success: ${success})`);

                // Now try to set the activity value
                const titleSelect = document.getElementById('title');
                if (!titleSelect) {
                    console.error('Title select element not found');
                    return;
                }

                console.log(`Title select has ${titleSelect.options.length} options`);
                Array.from(titleSelect.options).forEach((opt, index) => {
                    console.log(`Option ${index}: value=${opt.value}, text=${opt.textContent}, disabled=${opt.disabled}`);
                });

                // Find and select the correct option
                let found = false;
                Array.from(titleSelect.options).forEach(option => {
                    if (option.value == data.ppas_form_id) {
                        console.log(`Found matching option by value: ${option.textContent}`);

                        // Make sure it's not disabled
                        option.disabled = false;

                        // Set the value
                        titleSelect.value = option.value;
                        found = true;

                        // IMPORTANT: Instead of triggering change event, directly call fetchActivityDetails
                        console.log(`Setting title select value to ${option.value} and directly calling fetchActivityDetails`);

                        // Call the original fetchActivityDetails function from the upper scope
                        // This ensures we call the original function, not our wrapper
                        console.log("Calling fetchActivityDetails with:", {
                            activityId: option.value,
                            year: yearSelect.value,
                            quarter: quarterSelect.value
                        });

                        // We need to get the original function from the page context
                        const originalFetchActivityDetails =
                            document.querySelector('script:not([src])').textContent.includes('function fetchActivityDetails') ?
                            fetchActivityDetails :
                            null;

                        if (originalFetchActivityDetails) {
                            // Call the original function
                            originalFetchActivityDetails(option.value, yearSelect.value, quarterSelect.value);
                        } else {
                            // Fallback to global fetch of activity details
                            console.log("Original fetchActivityDetails not found, performing direct fetch");

                            // Debug: Check if personnel containers exist
                            console.log("Checking if personnel containers exist in DOM:");
                            console.log("projectLeadersContainer:", document.getElementById('projectLeadersContainer') ? "exists" : "missing");
                            console.log("assistantLeadersContainer:", document.getElementById('assistantLeadersContainer') ? "exists" : "missing");
                            console.log("projectStaffContainer:", document.getElementById('projectStaffContainer') ? "exists" : "missing");

                            // Clear all personnel containers first
                            if (typeof clearPersonnelContainers === 'function') {
                                clearPersonnelContainers();
                                console.log("Cleared personnel containers");
                            } else {
                                console.error("clearPersonnelContainers function not found");
                            }

                            // Create form data for direct fetch
                            const formData = new FormData();
                            formData.append('activity_id', option.value);
                            formData.append('year', yearSelect.value);
                            formData.append('quarter', quarterSelect.value);

                            // Make AJAX request directly
                            fetch('get_activity_details.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(details => {
                                    console.log('Activity details fetched directly:', details);

                                    // Check if createPersonnelRow function exists
                                    console.log("createPersonnelRow function exists:", typeof createPersonnelRow === 'function');

                                    // Manually populate fields if fetch successful
                                    if (details.success && details.details) {
                                        populateActivityDetails(details.details);

                                        // After all data is loaded, run form validation to update section status
                                        setTimeout(() => {
                                            console.log("Running form validation to update section status");
                                            initializeFormValidation();
                                        }, 500);
                                    }
                                })
                                .catch(error => {
                                    console.error('Error fetching activity details directly:', error);
                                });
                        }
                    }
                });

                if (!found) {
                    console.error(`Could not find option with value ${data.ppas_form_id} in the title select`);
                }
            };

            // Helper function to populate activity details manually
            function populateActivityDetails(details) {
                // Define a local createPersonnelRow function to ensure it's available
                function createPersonnelRow(name, container, roleTitle) {
                    if (!container) {
                        console.error(`Container for ${roleTitle} is null`);
                        return;
                    }

                    console.log(`Creating personnel row for ${roleTitle}: ${name}`);

                    const rowDiv = document.createElement('div');

                    // Add appropriate class based on role
                    if (roleTitle === 'Project Leader') {
                        rowDiv.className = 'row g-3 mb-3 project-leader-row';
                    } else if (roleTitle === 'Assistant Project Leader') {
                        rowDiv.className = 'row g-3 mb-3 assistant-leader-row';
                    } else if (roleTitle === 'Project Staff') {
                        rowDiv.className = 'row g-3 mb-3 project-staff-row';
                    } else {
                        rowDiv.className = 'row g-3 mb-3';
                    }

                    // Create name input
                    const nameCol = document.createElement('div');
                    nameCol.className = 'col-md-6';

                    const nameLabel = document.createElement('label');
                    nameLabel.className = 'form-label';
                    nameLabel.textContent = roleTitle;

                    const nameInput = document.createElement('input');
                    nameInput.type = 'text';
                    nameInput.className = 'form-control form-control-lg non-interactive';
                    nameInput.value = name;
                    nameInput.readOnly = true;

                    nameCol.appendChild(nameLabel);
                    nameCol.appendChild(nameInput);

                    // Create task input
                    const taskCol = document.createElement('div');
                    taskCol.className = 'col-md-6';

                    const taskLabel = document.createElement('label');
                    taskLabel.className = 'form-label';
                    taskLabel.textContent = 'Assigned Task';

                    const taskInput = document.createElement('input');
                    taskInput.type = 'text';
                    taskInput.className = 'form-control form-control-lg';
                    taskInput.name = `${roleTitle.replace(/\s+/g, '_').toLowerCase()}_task[]`;

                    taskCol.appendChild(taskLabel);
                    taskCol.appendChild(taskInput);

                    // Add columns to row
                    rowDiv.appendChild(nameCol);
                    rowDiv.appendChild(taskCol);

                    // Add row to container
                    container.appendChild(rowDiv);

                    console.log(`Personnel row for ${roleTitle} added successfully`);
                }

                // Populate form fields with fetched data
                const locationElem = document.getElementById('location');
                if (locationElem) locationElem.value = details.location || '';

                const startDateElem = document.getElementById('startDate');
                if (startDateElem) startDateElem.value = details.start_date || '';

                const endDateElem = document.getElementById('endDate');
                if (endDateElem) endDateElem.value = details.end_date || '';

                const startTimeElem = document.getElementById('startTime');
                if (startTimeElem) startTimeElem.value = details.start_time || '';

                const endTimeElem = document.getElementById('endTime');
                if (endTimeElem) endTimeElem.value = details.end_time || '';

                // Populate SDGs if available
                if (details.sdgs && Array.isArray(details.sdgs)) {
                    details.sdgs.forEach(sdg => {
                        const sdgNumber = parseInt(sdg.match(/\d+/)?.[0]);
                        if (sdgNumber && sdgNumber >= 1 && sdgNumber <= 17) {
                            const sdgElem = document.getElementById(`sdg${sdgNumber}`);
                            if (sdgElem) sdgElem.checked = true;
                        }
                    });
                }

                // Populate personnel if available
                if (details.personnel) {
                    console.log("Populating personnel data:", details.personnel);

                    // Populate project leaders
                    if (details.personnel.project_leaders && details.personnel.project_leaders.length > 0) {
                        const container = document.getElementById('projectLeadersContainer');
                        console.log("Project leaders container found:", container ? true : false);
                        if (container) {
                            details.personnel.project_leaders.forEach((leader, index) => {
                                console.log("Creating row for project leader:", leader);
                                // Call createPersonnelRow or equivalent function if available
                                if (typeof createPersonnelRow === 'function') {
                                    createPersonnelRow(leader, container, 'Project Leader');
                                } else {
                                    console.error("createPersonnelRow function not found");
                                }
                            });
                        } else {
                            console.error("projectLeadersContainer not found");
                        }
                    }

                    // Populate assistant project leaders
                    if (details.personnel.assistant_leaders && details.personnel.assistant_leaders.length > 0) {
                        const container = document.getElementById('assistantLeadersContainer');
                        console.log("Assistant leaders container found:", container ? true : false);
                        if (container) {
                            details.personnel.assistant_leaders.forEach((assistant, index) => {
                                console.log("Creating row for assistant leader:", assistant);
                                // Call createPersonnelRow or equivalent function if available
                                if (typeof createPersonnelRow === 'function') {
                                    createPersonnelRow(assistant, container, 'Assistant Project Leader');
                                } else {
                                    console.error("createPersonnelRow function not found");
                                }
                            });
                        } else {
                            console.error("assistantLeadersContainer not found");
                        }
                    }

                    // Populate project staff
                    if (details.personnel.staff && details.personnel.staff.length > 0) {
                        const container = document.getElementById('projectStaffContainer');
                        console.log("Project staff container found:", container ? true : false);
                        if (container) {
                            details.personnel.staff.forEach((staff, index) => {
                                console.log("Creating row for project staff:", staff);
                                // Call createPersonnelRow or equivalent function if available
                                if (typeof createPersonnelRow === 'function') {
                                    createPersonnelRow(staff, container, 'Project Staff');
                                } else {
                                    console.error("createPersonnelRow function not found");
                                }
                            });
                        } else {
                            console.error("projectStaffContainer not found");
                        }
                    }

                    // After creating all personnel, apply the narrative data tasks if available
                    // This will be called after activity details are loaded
                    window.applyNarrativeTasks = function(narrativeData) {
                        console.log("Applying narrative tasks to personnel:", narrativeData);

                        try {
                            // Apply leader tasks
                            if (narrativeData.leader_tasks) {
                                const leaderTasks = JSON.parse(narrativeData.leader_tasks);
                                const leaderInputs = document.querySelectorAll('.project-leader-row input[name="project_leader_task[]"]');

                                leaderTasks.forEach((task, index) => {
                                    if (leaderInputs[index]) {
                                        leaderInputs[index].value = task;
                                        // Add input event listener to trigger validation on change
                                        leaderInputs[index].addEventListener('input', function() {
                                            setTimeout(validateTeamSection, 100);
                                        });
                                    }
                                });
                            }

                            // Apply assistant leader tasks
                            if (narrativeData.assistant_tasks) {
                                const assistantTasks = JSON.parse(narrativeData.assistant_tasks);
                                const assistantInputs = document.querySelectorAll('.assistant-leader-row input[name="assistant_project_leader_task[]"]');

                                assistantTasks.forEach((task, index) => {
                                    if (assistantInputs[index]) {
                                        assistantInputs[index].value = task;
                                        // Add input event listener to trigger validation on change
                                        assistantInputs[index].addEventListener('input', function() {
                                            setTimeout(validateTeamSection, 100);
                                        });
                                    }
                                });
                            }

                            // Apply staff tasks
                            if (narrativeData.staff_tasks) {
                                const staffTasks = JSON.parse(narrativeData.staff_tasks);
                                const staffInputs = document.querySelectorAll('.project-staff-row input[name="project_staff_task[]"]');

                                staffTasks.forEach((task, index) => {
                                    if (staffInputs[index]) {
                                        staffInputs[index].value = task;
                                        // Add input event listener to trigger validation on change
                                        staffInputs[index].addEventListener('input', function() {
                                            setTimeout(validateTeamSection, 100);
                                        });
                                    }
                                });
                            }

                            // Run validation after applying all tasks
                            setTimeout(validateTeamSection, 300);
                        } catch (e) {
                            console.error("Error applying narrative tasks:", e);
                        }
                    };
                }

                // Populate GAD Proposal data if available
                if (details.gad_proposal) {
                    const gad = details.gad_proposal;

                    // Populate general and specific objectives
                    if (gad.general_objectives) {
                        const generalObj = document.getElementById('generalObjectives');
                        if (generalObj) generalObj.value = gad.general_objectives;
                    }

                    if (gad.specific_objectives) {
                        const specificObj = document.getElementById('specificObjectives');
                        if (specificObj) {
                            // Check if it's already an array
                            if (Array.isArray(gad.specific_objectives)) {
                                // Format each item with a bullet point and join with newlines
                                specificObj.value = gad.specific_objectives.map(obj => `• ${obj}`).join('\n');
                            } else if (typeof gad.specific_objectives === 'string') {
                                // If it's a string, split by commas and format each item
                                const objectives = gad.specific_objectives.split(',');
                                specificObj.value = objectives.map(obj => `• ${obj.trim()}`).join('\n');
                            } else {
                                // Fallback
                                specificObj.value = gad.specific_objectives;
                            }
                        }
                    }
                }

                // Calculate totals after populating data
                setTimeout(() => {
                    // Calculate beneficiary totals
                    if (typeof calculateBeneficiaryTotals === 'function') {
                        calculateBeneficiaryTotals();
                        console.log("Beneficiary totals calculated");
                    }

                    // Calculate activity rating totals
                    if (typeof calculateActivityRatingTotals === 'function') {
                        calculateActivityRatingTotals();
                        console.log("Activity rating totals calculated");
                    }

                    // Calculate timeliness rating totals
                    if (typeof calculateTimelinessRatingTotals === 'function') {
                        calculateTimelinessRatingTotals();
                        console.log("Timeliness rating totals calculated");
                    }
                }, 100);
            }

            // IMPORTANT: Add a wrapper around the fetchActivities function to intercept results
            const originalFetchActivities = window.fetchActivities;
            window.fetchActivities = function(year, quarter, narrativeId = 0) {
                console.log(`Custom fetchActivities called with year=${year}, quarter=${quarter}, narrativeId=${narrativeId}`);

                // Create form data for the request
                const formData = new FormData();
                formData.append('year', year);
                formData.append('quarter', quarter);
                formData.append('narrative_id', narrativeId);

                // Make AJAX request
                fetch('get_activities.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Activities data received:', data);

                        // Clear dropdown
                        titleSelect.innerHTML = '<option value="" selected disabled>Select Activity Title</option>';

                        // Check if activities were found
                        if (data.activities && data.activities.length > 0) {
                            console.log(`Found ${data.activities.length} activities`);

                            // Add each activity to dropdown
                            data.activities.forEach(activity => {
                                const option = document.createElement('option');
                                option.value = activity.id;

                                // Check if activity is the current one being edited (has_narrative = 2)
                                if (activity.has_narrative == 2) {
                                    option.textContent = `${activity.activity} (Current)`;
                                    option.className = 'current-narrative-option';
                                    option.style.color = 'green';
                                    option.style.fontWeight = 'bold';
                                    // Don't disable it - allow it to be selected
                                }
                                // Check if activity has an existing narrative
                                else if (activity.has_narrative == 1) {
                                    option.textContent = `${activity.activity} (Has Narrative)`;
                                    option.className = 'has-narrative-option';
                                    option.disabled = true; // Make the option non-selectable
                                    option.style.color = 'red';
                                    option.style.fontStyle = 'italic';
                                }
                                // Check if activity is missing a GAD proposal
                                else if (activity.missing_gad == 1) {
                                    option.textContent = `${activity.activity} (No GAD proposal)`;
                                    option.className = 'missing-gad-option';
                                    option.setAttribute('data-missing-gad', '1');
                                    option.disabled = true; // Make the option non-selectable
                                } else {
                                    option.textContent = activity.activity;
                                }

                                titleSelect.appendChild(option);
                            });

                            // Call our special callback
                            if (typeof window.activityLoadedCallback === 'function') {
                                window.activityLoadedCallback(true);
                            }
                        } else {
                            console.warn('No activities found in the response');

                            // Add "No activities found" message to dropdown
                            const option = document.createElement('option');
                            option.value = "";
                            option.textContent = `No activities found for ${data.campus || userCampus} (${year} ${quarter})`;
                            option.disabled = true;
                            titleSelect.appendChild(option);

                            // Log available quarters if any
                            if (data.available_quarters) {
                                console.log('Available quarters:', data.available_quarters);
                            }

                            // Call our special callback
                            if (typeof window.activityLoadedCallback === 'function') {
                                window.activityLoadedCallback(false);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching activities:', error);
                        if (typeof window.activityLoadedCallback === 'function') {
                            window.activityLoadedCallback(false);
                        }
                    });
            };

            // Call fetchActivities directly with the right values
            console.log('Directly calling fetchActivities');
            fetchActivities(data.year, data.quarter, narrativeId);

            // Set implementing office (multiselect)
            const implementingOffice = JSON.parse(data.implementing_office || '[]');
            if (implementingOffice.length > 0) {
                $('#implementingOffice').val(implementingOffice).trigger('change');
            }

            // Set partner agency
            if (data.partner_agency) {
                document.getElementById('partnerAgency').value = data.partner_agency;
            }

            // Set extension service agenda checkboxes
            if (data.extension_service_agenda) {
                const agenda = JSON.parse(data.extension_service_agenda);
                for (let i = 0; i < agenda.length; i++) {
                    if (agenda[i] === 1) {
                        const checkbox = document.getElementById('agenda' + (i + 1));
                        if (checkbox) checkbox.checked = true;
                    }
                }
            }

            // Set type of beneficiaries
            if (data.type_beneficiaries) {
                document.getElementById('typeBeneficiaries').value = data.type_beneficiaries;
            }

            // Set beneficiary distribution
            if (data.beneficiary_distribution) {
                const distribution = JSON.parse(data.beneficiary_distribution);
                // Loop through the distribution object and set form fields
                for (const key in distribution) {
                    const input = document.getElementById(key);
                    if (input) input.value = distribution[key];
                }

                // Calculate beneficiary totals after setting values
                setTimeout(() => {
                    if (typeof calculateBeneficiaryTotals === 'function') {
                        calculateBeneficiaryTotals();
                        console.log("Beneficiary totals calculated in populateFormWithData");
                    }
                }, 100);
            }

            // Set team tasks
            const setTeamTasks = (tasksJson, containerSelector) => {
                if (tasksJson) {
                    try {
                        const tasks = JSON.parse(tasksJson);
                        const container = document.querySelector(containerSelector);

                        // Add null check for container
                        if (!container) {
                            console.warn(`Container not found: ${containerSelector}`);
                            return;
                        }

                        // Clear existing task rows except the template
                        const existingRows = container.querySelectorAll('.task-row:not(.task-template)');
                        existingRows.forEach(row => row.remove());

                        // Add a new row for each task
                        tasks.forEach(task => {
                            // Find the add button and trigger it to add a new row
                            const addButton = container.querySelector('.add-task-btn');
                            if (addButton) {
                                // Simulate click to add a new row
                                addButton.click();

                                // Set the task text in the newly added row
                                const newRow = container.querySelector('.task-row:last-child');
                                if (newRow) {
                                    const taskInput = newRow.querySelector('.task-input');
                                    if (taskInput) {
                                        taskInput.value = task;

                                        // Add event listeners to trigger validation
                                        taskInput.addEventListener('input', function() {
                                            setTimeout(validateTeamSection, 100);
                                        });

                                        taskInput.addEventListener('change', function() {
                                            setTimeout(validateTeamSection, 100);
                                        });
                                    }
                                }
                            }
                        });

                        // Run validation after all tasks are set
                        setTimeout(validateTeamSection, 300);
                    } catch (e) {
                        console.error('Error parsing tasks:', e);
                    }
                }
            };

            setTeamTasks(data.leader_tasks, '#leaderTasksContainer');
            setTeamTasks(data.assistant_tasks, '#assistantTasksContainer');
            setTeamTasks(data.staff_tasks, '#staffTasksContainer');

            // Set activity narrative
            if (data.activity_narrative) {
                document.getElementById('activityNarrative').value = data.activity_narrative;
            }

            // Set activity and timeliness ratings
            const setRatings = (ratingsJson, ratingType) => {
                if (ratingsJson) {
                    try {
                        console.log(`Setting ${ratingType} ratings:`, ratingsJson);
                        const ratings = JSON.parse(ratingsJson);

                        // Determine which class to target based on rating type
                        const inputClass = ratingType === 'activityRating' ? 'activity-rating' : 'timeliness-rating';

                        // Map the rating level keys to data-row attribute values
                        const ratingLevelMap = {
                            'Excellent': 'excellent',
                            'Very Satisfactory': 'very',
                            'Satisfactory': 'satisfactory',
                            'Fair': 'fair',
                            'Poor': 'poor'
                        };

                        // Map the participant type keys to data-col attribute values
                        const participantTypeMap = {
                            'BatStateU': 'batstateu',
                            'Others': 'others'
                        };

                        // For each rating category in the JSON
                        for (const ratingLevel in ratings) {
                            const dataRow = ratingLevelMap[ratingLevel];
                            if (!dataRow) {
                                console.warn(`Unknown rating level: ${ratingLevel}`);
                                continue;
                            }

                            // For each participant type in the category
                            const categoryData = ratings[ratingLevel];
                            for (const participantType in categoryData) {
                                const dataCol = participantTypeMap[participantType];
                                if (!dataCol) {
                                    console.warn(`Unknown participant type: ${participantType}`);
                                    continue;
                                }

                                const value = categoryData[participantType];

                                // Find the corresponding input field using data attributes
                                const inputField = document.querySelector(`.${inputClass}[data-row="${dataRow}"][data-col="${dataCol}"]`);

                                if (inputField) {
                                    // Set the value
                                    inputField.value = value;
                                    console.log(`Set ${ratingType} value for ${dataRow}/${dataCol} to ${value}`);
                                } else {
                                    console.warn(`Input field for ${ratingType} ${dataRow}/${dataCol} not found`);
                                }
                            }
                        }

                        // Calculate totals after setting ratings
                        if (ratingType === 'activityRating') {
                            setTimeout(() => {
                                if (typeof calculateActivityRatingTotals === 'function') {
                                    calculateActivityRatingTotals();
                                    console.log("Activity rating totals calculated in populateFormWithData");
                                }
                            }, 100);
                        } else if (ratingType === 'timelinessRating') {
                            setTimeout(() => {
                                if (typeof calculateTimelinessRatingTotals === 'function') {
                                    calculateTimelinessRatingTotals();
                                    console.log("Timeliness rating totals calculated in populateFormWithData");
                                }
                            }, 100);
                        }
                    } catch (e) {
                        console.error(`Error parsing ${ratingType} ratings:`, e);
                    }
                }
            };

            setRatings(data.activity_ratings, 'activityRating');
            setRatings(data.timeliness_ratings, 'timelinessRating');

            // Display existing images if any
            if (data.activity_images) {
                try {
                    const images = JSON.parse(data.activity_images);
                    const imagesContainer = document.getElementById('imagePreviewContainer'); // Changed from 'imagesContainer' to 'imagePreviewContainer'

                    // Add null check for images container
                    if (!imagesContainer) {
                        console.warn('Images container not found: #imagePreviewContainer');
                        return;
                    }

                    // Clear existing images except the template
                    const existingImages = imagesContainer.querySelectorAll('.image-preview:not(.image-template)');
                    existingImages.forEach(img => img.remove());

                    // Log images to check what we're dealing with
                    console.log('Images to display:', images);

                    if (Array.isArray(images) && images.length > 0) {
                        // Add existing images
                        images.forEach(imageName => {
                            if (imageName && typeof imageName === 'string') {
                                // Create the proper image URL
                                const imageUrl = `../narrative_images/${imageName}`;
                                console.log('Adding image:', imageUrl);

                                // Use the same image preview function as when adding new images
                                addExistingImagePreview(imageUrl, imageName);
                            }
                        });

                        // After adding images, run validation for the image section
                        setTimeout(validateImageSection, 300);
                    } else {
                        console.warn('No valid images found in data:', images);
                    }
                } catch (e) {
                    console.error('Error parsing images:', e);
                }
            } else {
                console.warn('No activity_images data found in the narrative');
            }

            // After all data is loaded, run form validation to update section status
            setTimeout(() => {
                console.log("Running form validation for all sections after data is loaded");
                initializeFormValidation();

                // Also manually validate each section individually to ensure everything is updated
                validateBasicInfoSection();
                validateImplementingOfficeSection();
                validateSDGsSection();
                validateBeneficiariesSection();
                validateTeamSection();
                validateObjectivesSection();
                validateEvaluationSection();
                validateImageSection();

                // Remove any incomplete classes since we're in edit mode with loaded data
                document.querySelectorAll('.form-section.incomplete').forEach(section => {
                    section.classList.remove('incomplete');
                });
            }, 1000);
        }

        // Function to add existing image preview
        function addExistingImagePreview(imageUrl, imageName) {
            const imagesContainer = document.getElementById('imagePreviewContainer'); // Changed from 'imagesContainer' to 'imagePreviewContainer'

            // Add null check for images container
            if (!imagesContainer) {
                console.warn('Images container not found: #imagePreviewContainer');
                return;
            }

            // Check if we've reached the maximum number of images (6)
            const currentImages = imagesContainer.querySelectorAll('.image-preview').length;
            if (currentImages >= 6) {
                console.warn('Maximum number of images (6) already reached');
                return;
            }

            // Create a new preview div directly since we don't have a template
            const previewDiv = document.createElement('div');
            previewDiv.className = 'image-preview';
            previewDiv.dataset.originalName = imageName;

            // Create the image element
            const img = document.createElement('img');

            // Check if the image exists
            const testImg = new Image();
            testImg.onload = function() {
                // Image exists, proceed
                img.src = imageUrl;
                console.log('Image loaded successfully:', imageUrl);

                // Run validation after image is loaded
                validateImageSection();
            };

            testImg.onerror = function() {
                // Image doesn't exist or there's an error loading it
                console.error('Error loading image:', imageUrl);
                img.src = '../assets/img/image-placeholder.jpg'; // Fallback image

                // Run validation even if image fails to load
                validateImageSection();
            };

            // Start loading the image
            testImg.src = imageUrl;

            // Create the remove button
            const removeBtn = document.createElement('button');
            removeBtn.className = 'remove-image';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.addEventListener('click', function() {
                previewDiv.remove();
                // Run validation after removing the image
                validateImageSection();
            });

            // Add elements to the preview div
            previewDiv.appendChild(img);
            previewDiv.appendChild(removeBtn);

            // Add to container
            imagesContainer.appendChild(previewDiv);

            // Log successful addition
            console.log('Added image preview for:', imageName);

            return previewDiv;
        }

        // Function to confirm narrative deletion
        function confirmDeleteNarrative(item) {
            // Close the narrative modal first so the SweetAlert is visible
            closeNarrativeModal();

            // Wait for the modal to close before showing the SweetAlert
            setTimeout(() => {
                Swal.fire({
                    title: 'Confirm Deletion',
                    html: `
                    <div class="mb-3">
                        <p class="mb-0 text-danger">Are you sure you want to delete this narrative?</p>
                        <p class="text-muted small">This action cannot be undone.</p>
                    </div>
                    <div class="narrative-delete-details p-3 border rounded mb-3 text-start" style="background-color: rgba(0,0,0,0.05);">
                        <p class="mb-1"><strong>Activity:</strong> ${item.activity}</p>
                        <p class="mb-1"><strong>Partner Agency:</strong> ${item.partner_agency}</p>
                        <p class="mb-0"><strong>Campus:</strong> ${item.campus}</p>
                    </div>
                `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                    backdrop: `rgba(0,0,0,0.7)`,
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteNarrative(item.id);
                    } else {
                        // If the user cancels, reopen the delete modal
                        openNarrativeModal('delete');
                    }
                });
            }, 300); // Match this to the transition duration in CSS for closeNarrativeModal
        }

        // Function to delete a narrative
        function deleteNarrative(id) {
            // Send delete request to server
            fetch(`narrative.php?action=delete_narrative&id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // After successful deletion, reload the data
                        loadNarrativeData();

                        // Show success message with timer and progress bar
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'The narrative has been deleted successfully.',
                            icon: 'success',
                            timer: 1500,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            backdrop: `rgba(0,0,0,0.8)`
                        }).then(() => {
                            // Reopen the delete narratives modal after the success alert is closed
                            openNarrativeModal('delete');
                        });
                    } else {
                        // Show error message
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to delete the narrative.',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error deleting narrative:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while deleting the narrative.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                });
        }

        // Function to view narrative details
        function viewNarrativeDetails(item) {
            // Logic to view full narrative details
            console.log(`Viewing narrative ID ${item.id}`);

            Swal.fire({
                title: item.activity,
                html: `
                <div class="narrative-details">
                    <p><strong>Partner Agency:</strong> ${item.partner_agency}</p>
                    <p><strong>Campus:</strong> ${item.campus}</p>
                </div>
            `,
                icon: 'info',
                confirmButtonColor: '#6a1b9a',
                confirmButtonText: 'Close'
            });
        }

        // Function to disable UI elements for Central users
        function disableUIForCentralUsers() {
            if (isCentralUser) {
                // Add a notification at the top of the form
                const formContainer = document.querySelector('.card-body');
                if (formContainer) {
                    const notification = document.createElement('div');
                    notification.className = 'alert alert-info mb-4 d-flex justify-content-between align-items-center';
                    notification.style.backgroundColor = 'rgba(106, 27, 154, 0.1)';
                    notification.style.borderColor = 'rgba(106, 27, 154, 0.2)';
                    notification.style.color = 'var(--accent-color)';

                    // Create the message container
                    const messageContainer = document.createElement('div');
                    messageContainer.innerHTML = '<i class="fas fa-info-circle me-2"></i> <strong>Read-Only Mode:</strong> As a Central user, you can view but not modify the data.';

                    // Create the view button
                    const viewButton = document.createElement('button');
                    viewButton.className = 'btn btn-sm';
                    viewButton.style.backgroundColor = 'rgba(106, 27, 154, 0.1)';
                    viewButton.style.borderColor = 'rgba(106, 27, 154, 0.2)';
                    viewButton.style.color = 'var(--accent-color)';
                    viewButton.style.transition = 'all 0.2s ease';
                    viewButton.innerHTML = '<i class="fas fa-arrow-down me-1"></i> View';

                    // Add hover effect
                    viewButton.addEventListener('mouseover', function() {
                        this.style.backgroundColor = 'var(--accent-color)';
                        this.style.color = 'white';
                    });

                    viewButton.addEventListener('mouseout', function() {
                        this.style.backgroundColor = 'rgba(106, 27, 154, 0.1)';
                        this.style.color = 'var(--accent-color)';
                    });

                    // Add click handler to scroll to bottom of main card
                    viewButton.addEventListener('click', function() {
                        const mainCard = document.querySelector('.card');
                        if (mainCard) {
                            mainCard.scrollIntoView({
                                behavior: 'smooth',
                                block: 'end'
                            });
                        }
                    });

                    // Append elements to notification
                    notification.appendChild(messageContainer);
                    notification.appendChild(viewButton);

                    formContainer.insertBefore(notification, formContainer.firstChild);
                }

                // Disable all form input fields except for modal filters
                document.querySelectorAll('input, textarea, select').forEach(input => {
                    // Don't disable filter controls or narrative modal filters
                    if (!input.classList.contains('filter-control') &&
                        input.id !== 'filter-activity' &&
                        input.id !== 'filter-campus') {
                        input.disabled = true;
                        input.readOnly = true;
                        input.classList.add('non-interactive');
                        input.style.cursor = 'not-allowed';
                    }
                });

                // Specifically ensure Extension Service Agenda checkboxes are disabled
                for (let i = 1; i <= 12; i++) {
                    const agendaCheckbox = document.getElementById('agenda' + i);
                    if (agendaCheckbox) {
                        agendaCheckbox.disabled = true;
                        agendaCheckbox.classList.add('non-interactive');
                        // Add pointer-events:none to the parent element to prevent clicks
                        agendaCheckbox.closest('.checkbox-item').style.pointerEvents = 'none';
                        agendaCheckbox.closest('.checkbox-item').style.opacity = '0.5';
                    }
                }

                // Special handling for Select2 elements (Implementing Office dropdown)
                if ($('#implementingOffice').length) {
                    $('#implementingOffice').prop('disabled', true).trigger('change');

                    // Apply consistent styling to match other non-interactive elements
                    $('#implementingOffice').next('.select2-container').css({
                        'opacity': '0.5',
                        'pointer-events': 'none',
                        'cursor': 'not-allowed'
                    });

                    // Style the Select2 selection area to match non-interactive elements
                    $('#implementingOffice').next('.select2-container').find('.select2-selection').css({
                        'background-color': 'var(--readonly-bg)',
                        'border-color': 'var(--readonly-border)',
                        'color': 'var(--readonly-text)'
                    });
                }

                // Disable add, edit and delete buttons
                const addBtn = document.getElementById('addBtn');
                const editBtn = document.getElementById('editBtn');
                const deleteBtn = document.getElementById('deleteBtn');

                if (addBtn) {
                    addBtn.disabled = true;
                    addBtn.classList.add('central-disabled');
                    addBtn.style.opacity = '0.5';
                    addBtn.style.cursor = 'not-allowed';
                    addBtn.style.pointerEvents = 'none';
                }

                if (editBtn) {
                    editBtn.disabled = true;
                    editBtn.classList.add('central-disabled');
                    editBtn.style.opacity = '0.5';
                    editBtn.style.cursor = 'not-allowed';
                    editBtn.style.pointerEvents = 'none';
                }

                if (deleteBtn) {
                    deleteBtn.disabled = true;
                    deleteBtn.classList.add('central-disabled');
                    deleteBtn.style.opacity = '0.5';
                    deleteBtn.style.cursor = 'not-allowed';
                    deleteBtn.style.pointerEvents = 'none';
                }
            }
        }

        // Event listeners for view, edit, and delete buttons
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial table classes based on user type
            const table = document.querySelector('.narrative-table');
            if (table) {
                table.classList.add(isCentralUser ? 'central-user' : 'campus-user');
            }

            // Add click event listeners to the view, edit, and delete buttons
            const viewBtn = document.getElementById('viewBtn');
            const editBtn = document.getElementById('editBtn');
            const deleteBtn = document.getElementById('deleteBtn');

            if (viewBtn) {
                viewBtn.addEventListener('click', function() {
                    openNarrativeModal('view');
                });
            }

            if (editBtn) {
                editBtn.addEventListener('click', function() {
                    openNarrativeModal('edit');
                });
            }

            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    openNarrativeModal('delete');
                });
            }

            // Call function to disable UI for Central users
            disableUIForCentralUsers();
        });

        // Add missing-gad-option CSS to document head
        document.addEventListener('DOMContentLoaded', function() {
            // Add styles for missing GAD items to the page
            if (!document.getElementById('missing-gad-styles')) {
                const styleEl = document.createElement('style');
                styleEl.id = 'missing-gad-styles';
                styleEl.textContent = `
        .missing-gad-option {
            color: red;
            font-style: italic;
        }
        .has-narrative-option {
            color: red;
            font-style: italic;
        }
        .current-narrative-option {
            color: green;
            font-weight: bold;
        }
    `;
                document.head.appendChild(styleEl);
            }
        });
    </script>
</body>

</html>