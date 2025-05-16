<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit();
}

$userCampus = $_SESSION['username'];

// Get filter parameters
$year = isset($_GET['year']) ? $_GET['year'] : '';
$quarter = isset($_GET['quarter']) ? $_GET['quarter'] : '';
$genderIssue = isset($_GET['gender_issue']) ? $_GET['gender_issue'] : '';
$campus = isset($_GET['campus']) ? $_GET['campus'] : $userCampus;

// Include database configuration
require_once '../config.php';

try {
    // Build the WHERE clause based on filters
    $whereConditions = [];
    $params = [];
    $types = '';

    // Always filter by campus (user's campus)
    $whereConditions[] = "p.campus = ?";
    $params[] = $campus;
    $types .= 's';

    // Add other filters if they're provided
    if (!empty($year)) {
        $whereConditions[] = "p.year = ?";
        $params[] = $year;
        $types .= 's';
    }

    if (!empty($quarter)) {
        $whereConditions[] = "p.quarter = ?";
        $params[] = $quarter;
        $types .= 's';
    }

    if (!empty($genderIssue)) {
        $whereConditions[] = "g.gender_issue LIKE ?";
        $params[] = '%' . $genderIssue . '%';
        $types .= 's';
    }

    if (!empty($_GET['activity'])) {
        $whereConditions[] = "p.activity LIKE ?";
        $params[] = '%' . $_GET['activity'] . '%';
        $types .= 's';
    }

    // Build the full WHERE clause
    $whereClause = implode(' AND ', $whereConditions);

    // SQL query to fetch PPAS entries with gender issue text from gpb_entries
    $query = "SELECT p.id, p.year, p.quarter, p.gender_issue_id, g.gender_issue, 
                     p.program, p.project, p.activity
              FROM ppas_forms p
              LEFT JOIN gpb_entries g ON p.gender_issue_id = g.id
              WHERE $whereClause
              ORDER BY p.year DESC, p.quarter DESC";

    $stmt = $conn->prepare($query);
    
    // Bind parameters if there are any
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = [
            'id' => $row['id'],
            'year' => $row['year'],
            'quarter' => $row['quarter'],
            'gender_issue_id' => $row['gender_issue_id'],
            'gender_issue' => $row['gender_issue'],
            'program' => $row['program'],
            'project' => $row['project'],
            'activity' => $row['activity']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'entries' => $entries
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    // The connection closure is handled in config.php
}
?>