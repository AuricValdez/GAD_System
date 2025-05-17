<?php
session_start();
require_once '../config.php';
require '../../vendor/autoload.php'; // Make sure you have PhpSpreadsheet installed

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    die('User not logged in');
}

// Get parameters
$campus = isset($_GET['campus']) ? $_GET['campus'] : null;
$year = isset($_GET['year']) ? $_GET['year'] : null;

if (!$campus || !$year) {
    die('Missing required parameters');
}

try {
    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set up the header
    $sheet->setCellValue('A1', 'ANNUAL GENDER AND DEVELOPMENT (GAD) PLAN AND BUDGET');
    $sheet->setCellValue('A2', 'FY ' . $year);
    $sheet->setCellValue('A3', $campus);

    // Set up column headers
    $headers = [
        'A5' => 'Gender Issue/GAD Mandate',
        'B5' => 'Cause of Gender Issue',
        'C5' => 'GAD Result Statement/Objective',
        'D5' => 'Relevant Organization MFO/PAP',
        'E5' => 'GAD Activity',
        'F5' => 'Performance Indicators/Targets',
        'G5' => 'GAD Budget',
        'H5' => 'Source of Budget',
        'I5' => 'Responsible Unit/Office'
    ];

    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }

    // Fetch data from gpb_entries table using PDO
    $query = "SELECT 
        gender_issue,
        cause_of_issue as cause_of_gender_issue,
        gad_objective as gad_statement,
        relevant_agency as relevant_organization,
        generic_activity as gad_activity,
        male_participants as target_male,
        female_participants as target_female,
        gad_budget,
        source_of_budget,
        responsible_unit
    FROM gpb_entries
    WHERE campus = :campus AND year = :year
    ORDER BY id";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':campus', $campus, PDO::PARAM_STR);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->execute();
    
    $row = 6;
    $total_budget = 0;

    while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sheet->setCellValue('A' . $row, $data['gender_issue']);
        $sheet->setCellValue('B' . $row, $data['cause_of_gender_issue']);
        $sheet->setCellValue('C' . $row, $data['gad_statement']);
        $sheet->setCellValue('D' . $row, $data['relevant_organization']);
        $sheet->setCellValue('E' . $row, $data['gad_activity']);
        
        // Format performance indicators with targets
        $targets = "Performance Indicators:\n" . 
                  "\n\nTarget:\nMale: " . $data['target_male'] . 
                  "\nFemale: " . $data['target_female'];
        $sheet->setCellValue('F' . $row, $targets);
        
        $sheet->setCellValue('G' . $row, $data['gad_budget']);
        $sheet->setCellValue('H' . $row, $data['source_of_budget']);
        $sheet->setCellValue('I' . $row, $data['responsible_unit']);
        
        $total_budget += (float)$data['gad_budget'];
        $row++;
    }

    // Add total row
    $totalRow = $row;
    $sheet->setCellValue('F' . $totalRow, 'Total GAD Budget:');
    $sheet->setCellValue('G' . $totalRow, $total_budget);

    // Auto-size columns
    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Style the header
    $sheet->mergeCells('A1:I1');
    $sheet->mergeCells('A2:I2');
    $sheet->mergeCells('A3:I3');
    
    $sheet->getStyle('A1:I1')->getAlignment()->setHorizontal('center');
    $sheet->getStyle('A2:I2')->getAlignment()->setHorizontal('center');
    $sheet->getStyle('A3:I3')->getAlignment()->setHorizontal('center');
    
    $sheet->getStyle('A5:I5')->getFont()->setBold(true);
    $sheet->getStyle('A5:I5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
    $sheet->getStyle('A5:I5')->getFill()->getStartColor()->setRGB('F8F9FA');

    // Set the content type
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="GPB_Report_' . $campus . '_' . $year . '.xlsx"');
    header('Cache-Control: max-age=0');

    // Create Excel file
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    
} catch (Exception $e) {
    error_log("Error in export_gpb_excel.php: " . $e->getMessage());
    die('An error occurred while generating the Excel file');
}

// Close PDO connection
$pdo = null; 