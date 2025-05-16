<?php
require_once('../includes/db_connection.php');
header('Content-Type: application/json');

// Get parameters from request
$year = isset($_GET['year']) ? $_GET['year'] : null;
$quarter = isset($_GET['quarter']) ? $_GET['quarter'] : null;

// Prepare response array
$response = [
    'success' => false,
    'titles' => [],
    'message' => ''
];

try {
    if (!$year || !$quarter) {
        throw new Exception('Year and quarter are required');
    }

    // Get all titles for the specified year and quarter
    $sql = "SELECT id, activity, project, program, start_date, end_date, location,
            external_type, external_male, external_female, total_male, total_female, total_beneficiaries 
            FROM ppas_forms 
            WHERE year = :year AND quarter = :quarter
            ORDER BY activity ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':year' => $year,
        ':quarter' => $quarter
    ]);

    $titles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($titles && count($titles) > 0) {
        // For each activity, fetch its personnel
        foreach ($titles as &$title) {
            // Fetch personnel for this activity
            $personnelSql = "SELECT pp.personnel_id, p.name, p.academic_rank, p.gender, pp.role 
                        FROM ppas_personnel pp
                        JOIN personnel p ON pp.personnel_id = p.id
                        WHERE pp.ppas_form_id = :ppas_id
                        ORDER BY pp.role";
            
            $personnelStmt = $conn->prepare($personnelSql);
            $personnelStmt->execute([':ppas_id' => $title['id']]);
            $personnel = $personnelStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Group personnel by role
            $title['personnel'] = [
                'projectLeaders' => [],
                'assistantProjectLeaders' => [],
                'projectStaff' => []
            ];
            
            foreach ($personnel as $person) {
                $roleKey = '';
                switch ($person['role']) {
                    case 'Project Leader':
                        $roleKey = 'projectLeaders';
                        break;
                    case 'Assistant Project Leader':
                        $roleKey = 'assistantProjectLeaders';
                        break;
                    case 'Staff':
                    case 'Other Internal Participants':
                        $roleKey = 'projectStaff';
                        break;
                }
                
                if ($roleKey) {
                    $title['personnel'][$roleKey][] = [
                        'id' => $person['personnel_id'],
                        'name' => $person['name'],
                        'academic_rank' => $person['academic_rank'],
                        'gender' => $person['gender']
                    ];
                }
            }
        }
        
        $response['success'] = true;
        $response['titles'] = $titles;
        $response['message'] = count($titles) . ' activities found';
    } else {
        $response['message'] = 'No activities found for the specified year and quarter';
    }

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response); 
