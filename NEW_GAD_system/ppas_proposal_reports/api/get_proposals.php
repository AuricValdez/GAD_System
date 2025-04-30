<?php
session_start();
require_once '../../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

// Get parameters
$campus = isset($_GET['campus']) ? $_GET['campus'] : null;
$year = isset($_GET['year']) ? $_GET['year'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

if (!$campus || !$year) {
    echo json_encode(['status' => 'error', 'message' => 'Campus and year parameters are required']);
    exit;
}

try {
    $db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Base query
    $query = "SELECT gp.proposal_id as id, pf.activity as activity_title 
              FROM gad_proposals gp
              JOIN ppas_forms pf ON gp.ppas_form_id = pf.id
              WHERE pf.campus = :campus AND pf.year = :year";
    
    // Add search condition if search parameter is provided
    if ($search) {
        $query .= " AND pf.activity LIKE :search";
    }
    
    $query .= " ORDER BY pf.activity ASC";
    
    $stmt = $db->prepare($query);
    
    // Base parameters
    $params = [
        'campus' => $campus,
        'year' => $year
    ];
    
    // Add search parameter if provided
    if ($search) {
        $params['search'] = "%$search%";
    }
    
    $stmt->execute($params);
    
    $proposals = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $proposals[] = [
            'id' => $row['id'],
            'activity_title' => $row['activity_title']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $proposals
    ]);

} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch proposals'
    ]);
} 