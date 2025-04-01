<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Set content type to JSON
header('Content-Type: application/json');

// Include database connection
include_once '../includes/db_connection.php';

// Log access for debugging
file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - User: " . $_SESSION['username'] . " accessed get_ppas_details.php with id: " . ($_GET['id'] ?? 'none') . "\n", FILE_APPEND);

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'PPAS ID is required'
    ]);
    exit();
}

$ppasId = $_GET['id'];

try {
    // Validate that ppasId is a number
    if (!is_numeric($ppasId)) {
        throw new Exception("Invalid PPAS ID format");
    }
    
    // Get PPAS form details
    $query = "SELECT 
                p.*,
                CONCAT(p.project, ' - ', p.program, ' - ', p.activity) AS title,
                CONCAT(DATE_FORMAT(p.start_date, '%M %d, %Y'), 
                    CASE WHEN p.start_date != p.end_date THEN CONCAT(' to ', DATE_FORMAT(p.end_date, '%M %d, %Y')) ELSE '' END,
                    ', ', 
                    TIME_FORMAT(p.start_time, '%h:%i %p'), ' - ', TIME_FORMAT(p.end_time, '%h:%i %p')
                ) AS duration,
                p.location
              FROM ppas_forms p
              WHERE p.id = :id";
              
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $ppasId, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        throw new Exception("PPAS form not found");
    }
    
    $ppasData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Add formatted title
    $ppasData['formatted_title'] = htmlspecialchars($ppasData['activity']) . 
                                  ' (' . htmlspecialchars($ppasData['project']) . 
                                  ' - ' . htmlspecialchars($ppasData['program']) . ')';
    
    // Get personnel/project team
    $personnelQuery = "SELECT 
                        pp.role,
                        pe.name,
                        pe.academic_rank,
                        pe.campus
                      FROM 
                        ppas_personnel pp
                      JOIN 
                        personnel pe ON pp.personnel_id = pe.id
                      WHERE 
                        pp.ppas_form_id = :ppas_id
                      ORDER BY 
                        FIELD(pp.role, 'Project Leader', 'Assistant Project Leader', 'Staff', 'Other Internal Participants')";
                        
    $personnelStmt = $conn->prepare($personnelQuery);
    $personnelStmt->bindParam(':ppas_id', $ppasId, PDO::PARAM_INT);
    $personnelStmt->execute();
    
    $projectTeam = $personnelStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add project team to response data
    $ppasData['project_team'] = $projectTeam;
    
    // Format beneficiaries data for easy use in the frontend
    $ppasData['beneficiaries'] = [
        'students' => [
            'male' => intval($ppasData['students_male']),
            'female' => intval($ppasData['students_female']),
            'total' => intval($ppasData['students_male']) + intval($ppasData['students_female'])
        ],
        'faculty' => [
            'male' => intval($ppasData['faculty_male']),
            'female' => intval($ppasData['faculty_female']),
            'total' => intval($ppasData['faculty_male']) + intval($ppasData['faculty_female'])
        ],
        'internal' => [
            'male' => intval($ppasData['total_internal_male']),
            'female' => intval($ppasData['total_internal_female']),
            'total' => intval($ppasData['total_internal_male']) + intval($ppasData['total_internal_female'])
        ],
        'external' => [
            'type' => $ppasData['external_type'],
            'male' => intval($ppasData['external_male']),
            'female' => intval($ppasData['external_female']),
            'total' => intval($ppasData['external_male']) + intval($ppasData['external_female'])
        ],
        'total' => [
            'male' => intval($ppasData['total_male']),
            'female' => intval($ppasData['total_female']),
            'all' => intval($ppasData['total_beneficiaries'])
        ]
    ];
    
    // Add SDGs if available
    if (!empty($ppasData['sdgs'])) {
        try {
            $ppasData['sdgs'] = json_decode($ppasData['sdgs'], true);
        } catch (Exception $e) {
            $ppasData['sdgs'] = [];
            file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - Error parsing SDGs JSON: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    } else {
        $ppasData['sdgs'] = [];
    }
    
    file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - Successfully retrieved PPAS details for ID: $ppasId\n", FILE_APPEND);
    
    echo json_encode([
        'status' => 'success',
        'data' => $ppasData
    ]);
    
} catch (PDOException $e) {
    // Log PDO error
    file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - PDO Error in get_ppas_details.php: " . $e->getMessage() . "\n", FILE_APPEND);
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Log general error
    file_put_contents('debug_logs.txt', date('Y-m-d H:i:s') . " - Error in get_ppas_details.php: " . $e->getMessage() . "\n", FILE_APPEND);
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Close connection
$conn = null;
?> 