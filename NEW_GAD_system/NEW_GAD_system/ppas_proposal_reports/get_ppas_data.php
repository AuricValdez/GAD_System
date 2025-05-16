<?php
require_once('../includes/db_connection.php');
header('Content-Type: application/json');

// Get parameters from request
$year = isset($_GET['year']) ? $_GET['year'] : null;
$quarter = isset($_GET['quarter']) ? $_GET['quarter'] : null;
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Prepare response array
$response = [
    'success' => false,
    'data' => null,
    'message' => ''
];

try {
    if ($id) {
        // Get PPAS form data by ID
        $sql = "SELECT pf.id, pf.year, pf.quarter, pf.activity, pf.project, pf.program, pf.location, pf.start_date, pf.end_date,
                pf.external_type, pf.external_male, pf.external_female, pf.total_male, pf.total_female, pf.total_beneficiaries
                FROM ppas_forms pf 
                WHERE pf.id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
    } else if ($year && $quarter) {
        // Get PPAS form data by year and quarter
        $sql = "SELECT pf.id, pf.year, pf.quarter, pf.activity, pf.project, pf.program, pf.location, pf.start_date, pf.end_date,
                pf.external_type, pf.external_male, pf.external_female, pf.total_male, pf.total_female, pf.total_beneficiaries
                FROM ppas_forms pf 
                WHERE pf.year = :year AND pf.quarter = :quarter";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':year' => $year,
            ':quarter' => $quarter
        ]);
    } else {
        throw new Exception('Either ID or Year and Quarter are required');
    }

    $ppasData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ppasData) {
        // Get personnel data with their roles
        $personnelSql = "SELECT pp.id, pp.personnel_id, pp.personnel_name, pp.role 
                        FROM ppas_personnel pp
                        WHERE pp.ppas_id = :ppas_id";
        
        $stmt = $conn->prepare($personnelSql);
        $stmt->execute([':ppas_id' => $ppasData['id']]);
        $personnelData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['data'] = [
            'ppas' => $ppasData,
            'personnel' => $personnelData
        ];
    } else {
        if ($id) {
            $response['message'] = 'No data found for the specified ID';
        } else {
            $response['message'] = 'No data found for the specified year and quarter';
        }
    }

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response); 