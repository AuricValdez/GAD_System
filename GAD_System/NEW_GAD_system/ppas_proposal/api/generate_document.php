<?php
require_once '../../vendor/autoload.php';
require_once '../../config/database.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

header('Content-Type: application/json');

try {
    // Get POST parameters
    $campus = $_POST['campus'] ?? '';
    $year = $_POST['year'] ?? '';
    $proposalId = $_POST['proposal_id'] ?? '';
    $type = $_POST['type'] ?? 'word';

    // Validate inputs
    if (empty($campus) || empty($year) || empty($proposalId)) {
        throw new Exception('Missing required parameters');
    }

    // Get proposal data from database
    // ... Your database query code here ...

    // Generate document using PHPWord
    $phpWord = generateDocument($proposalData);

    // Create temp directory if it doesn't exist
    $tempDir = '../temp';
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    // Generate unique filename
    $filename = 'gad_proposal_' . time() . '_' . uniqid();
    $filePath = $tempDir . '/' . $filename;

    if ($type === 'print') {
        // Save as PDF
        $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');
        $pdfWriter->save($filePath . '.pdf');
        $fileUrl = 'temp/' . $filename . '.pdf';
    } else {
        // Save as DOCX
        $docxWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $docxWriter->save($filePath . '.docx');
        $fileUrl = 'temp/' . $filename . '.docx';
    }

    echo json_encode([
        'status' => 'success',
        'file_url' => $fileUrl
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 