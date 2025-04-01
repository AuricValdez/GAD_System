<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../tcpdf/tcpdf.php');

// Extend TCPDF with custom header and footer
class MYPDF extends TCPDF {
    public function Header() {
        // Add logo
        $this->Image('images/Batangas_State_Logo.png', 95, 10, 20);
        
        // Header text
        $this->SetFont('helvetica', '', 12);
        $this->Cell(0, 10, 'BATANGAS STATE UNIVERSITY', 0, 1, 'C');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, 'The National Engineering University', 0, 1, 'C');
        $this->Cell(0, 5, 'GENDER AND DEVELOPMENT (GAD) PROPOSAL', 0, 1, 'C');
        $this->Ln(10);
    }

    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->current_page, 0, 0, 'C');
    }
}

// Create new PDF document
$pdf = new MYPDF('P', 'mm', 'A4');

// Set document information
$pdf->SetCreator('BatState-U GAD System');
$pdf->SetAuthor('BatState-U');
$pdf->SetTitle('GAD Proposal');

// Set margins
$pdf->SetMargins(15, 15, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Add content
$title = 'Gender Sensitivity Training Workshop';
$date = date('F d, Y');
$proponent = 'College of Engineering';
$venue = 'Main Campus';
$participants = 'Faculty and Staff';
$budget = '50,000.00';

// Content starts after header
$pdf->SetY(60);

// Title Section
$pdf->Cell(50, 10, 'Title of Activity:', 0);
$pdf->Cell(0, 10, $title, 0, 1);

// Proponent Section
$pdf->Cell(50, 10, 'Proponent/Office:', 0);
$pdf->Cell(0, 10, $proponent, 0, 1);

// Date and Venue Section
$pdf->Cell(50, 10, 'Date and Venue:', 0);
$pdf->Cell(0, 10, $date . ' - ' . $venue, 0, 1);

// Participants Section
$pdf->Cell(50, 10, 'Participants:', 0);
$pdf->Cell(0, 10, $participants, 0, 1);

// Budget Section
$pdf->Cell(50, 10, 'Total Budget:', 0);
$pdf->Cell(0, 10, 'PHP ' . $budget, 0, 1);

$pdf->Ln(10);

// Project Description
$pdf->Cell(0, 10, 'Project Description', 0, 1);
$pdf->MultiCell(0, 10, 'A comprehensive workshop aimed at promoting gender sensitivity and awareness in the academic environment.', 0, 'J');

// Objectives
$pdf->Ln(5);
$pdf->Cell(0, 10, 'Objectives', 0, 1);
$pdf->MultiCell(0, 10, "1. To raise awareness about gender issues in the workplace\n2. To promote inclusive practices\n3. To develop gender-responsive policies", 0, 'L');

// Expected Output
$pdf->Ln(5);
$pdf->Cell(0, 10, 'Expected Output', 0, 1);
$pdf->MultiCell(0, 10, 'Increased awareness and implementation of gender-sensitive practices in the university.', 0, 'J');

// Signatures
$pdf->Ln(20);
$pdf->Cell(85, 10, 'Prepared by:', 0, 0, 'C');
$pdf->Cell(85, 10, 'Approved by:', 0, 1, 'C');

$pdf->Ln(15);
$pdf->Cell(85, 0, '__________________', 0, 0, 'C');
$pdf->Cell(85, 0, '__________________', 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(85, 10, 'Project Leader', 0, 0, 'C');
$pdf->Cell(85, 10, 'GAD Coordinator', 0, 1, 'C');

// Output the PDF
$pdf->Output('gad_proposal.pdf', 'I');
?> 