<?php
session_start();

// Debug session information
error_log("Session data in ppas.php: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in - redirecting to login");
    header("Location: ../index.php");
    exit();
}

// PDF Generation Code
if (isset($_POST['generate_pdf'])) {
    require_once('../tcpdf/tcpdf.php');

    // Create new PDF document
    class MYPDF extends TCPDF {
        public function Header() {
            // Add BatState-U logo
            $image_file = '../images/Batangas_State_Logo.png';
            $this->Image($image_file, 15, 10, 25, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            
            // Set font
            $this->SetFont('helvetica', 'B', 12);
            
            // Title
            $this->Cell(0, 5, 'BATANGAS STATE UNIVERSITY', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Ln(6);
            $this->SetFont('helvetica', '', 11);
            $this->Cell(0, 5, 'The National Engineering University', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Ln(6);
            $this->SetFont('helvetica', 'B', 12);
            $this->Cell(0, 5, 'GENDER AND DEVELOPMENT (GAD) PROPOSAL', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            
            // Line break
            $this->Ln(15);
        }

        public function Footer() {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C');
        }
    }

    // Create new PDF document
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('BatState-U GAD System');
    $pdf->SetAuthor('Batangas State University');
    $pdf->SetTitle('GAD Proposal');

    // Set margins
    $pdf->SetMargins(15, 45, 15);
    $pdf->SetHeaderMargin(20);
    $pdf->SetFooterMargin(15);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 15);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 11);

    // Get form data
    $title = $_POST['title'] ?? '';
    $proponent = $_POST['proponent'] ?? '';
    $date_venue = $_POST['date_venue'] ?? '';
    $participants = $_POST['participants'] ?? '';
    $budget = $_POST['budget'] ?? '';
    $description = $_POST['description'] ?? '';
    $objectives = $_POST['objectives'] ?? '';
    $output = $_POST['output'] ?? '';

    // Content
    $html = '
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        td {
            padding: 5px;
            border: 1px solid #000;
        }
        .label {
            font-weight: bold;
            width: 30%;
            background-color: #f0f0f0;
        }
        h3 {
            color: #000;
            font-size: 12pt;
            margin-top: 15px;
            margin-bottom: 10px;
        }
    </style>
    
    <table>
        <tr>
            <td class="label">Title of Activity</td>
            <td>'.htmlspecialchars($title).'</td>
        </tr>
        <tr>
            <td class="label">Proponent/Office</td>
            <td>'.htmlspecialchars($proponent).'</td>
        </tr>
        <tr>
            <td class="label">Date and Venue</td>
            <td>'.htmlspecialchars($date_venue).'</td>
        </tr>
        <tr>
            <td class="label">Participants</td>
            <td>'.htmlspecialchars($participants).'</td>
        </tr>
        <tr>
            <td class="label">Total Budget</td>
            <td>PHP '.number_format((float)$budget, 2).'</td>
        </tr>
    </table>

    <h3>Project Description</h3>
    <p>'.nl2br(htmlspecialchars($description)).'</p>

    <h3>Objectives</h3>
    <p>'.nl2br(htmlspecialchars($objectives)).'</p>

    <h3>Expected Output</h3>
    <p>'.nl2br(htmlspecialchars($output)).'</p>
    ';

    // Print content
    $pdf->writeHTML($html, true, false, true, false, '');

    // Close and output PDF document
    $pdf->Output('gad_proposal.pdf', 'D');
    exit();
}

$isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light"><Error>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPAS Forms - GAD System</title>
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
    <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --accent-color: #6a1b9a;
            --accent-hover: #4a148c;
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
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
            opacity: 1;
            transition: opacity 0.05s ease-in-out; /* Changed from 0.05s to 0.01s - make it super fast */
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
            box-shadow: 5px 0 15px rgba(0,0,0,0.05), 0 5px 15px rgba(0,0,0,0.05);
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
    scrollbar-width: none;  /* Firefox */
    -ms-overflow-style: none;  /* IE and Edge */
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
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
                box-shadow: 5px 0 25px rgba(0,0,0,0.1);
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
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
                background: rgba(0,0,0,0.5);
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
            width: 38px;
            height: 38px;
            padding: 0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s ease;
            margin-left: 5px;
        }

        .btn-icon i {
            font-size: 1rem;
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

.swal-wide {
    max-width: 850px !important;
}
.text-start {
    text-align: left !important;
}
pre {
    background: #f4f4f4;
    border: 1px solid #ddd;
    border-radius: 3px;
    color: #333;
    padding: 10px;
    overflow-x: auto;
    max-height: 300px;
}

/* Add this to your existing styles */
tr.selected {
    background-color: #e0f7fa !important;
    font-weight: bold;
    border: 2px solid #0097a7 !important;
}

/* Improve hover effect on clickable rows */
tr[data-id]:hover {
    background-color: #f5f5f5;
    cursor: pointer;
}

#deleteModalBody tr, #viewModalBody tr, #editModalBody tr {
    transition: all 0.2s ease-in-out;
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
.dropdown-submenu.show > .dropdown-menu {
    display: block;
}

.dropdown-submenu > a:after {
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
.dropdown-submenu.show > a:after {
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
    
    .dropdown-submenu > a:after {
        transform: rotate(90deg);
        margin-top: 8px;
    }
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
                <a class="nav-link" href="../dashboard/dashboard.php">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users me-2"></i> Staff
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../academic_rank/academic.php">Academic Rank</a></li>
                        <li><a class="dropdown-item" href="../personnel_list/personnel_list.php">Personnel List</a></li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="#" id="formsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-alt me-2"></i> Forms
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../target/target.php">Target Form</a></li>
                        <li><a class="dropdown-item" href="../gbp_forms/gbp.php">GPB Form</a></li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" id="ppasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                PPAs Form
                            </a>
                            <ul class="dropdown-menu dropdown-submenu" aria-labelledby="ppasDropdown">
                                <li><a class="dropdown-item" href="../ppas_form/ppas.php">Main PPAs Form</a></li>
                                <li><a class="dropdown-item" href="#">GAD Proposal Form</a></li>
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
                        <li><a class="dropdown-item" href="../ppas_report/ppas_report.php">Quarterly PPAs Reports</a></li>
                        <li><a class="dropdown-item" href="../ps_atrib/ps.php">PSA Reports</a></li>
                        <li><a class="dropdown-item" href="../ppas_proposal/print_proposal.php">GAD Proposal Reports</a></li>
                        <li><a class="dropdown-item" href="../narrative/print_narrative.php">Narrative Reports</a></li>
                    </ul>
                </div>
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
            <i class="fas fa-file-alt"></i>
            <h2>GAD Proposal Form</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Gender and Development (GAD) Activity Proposal</h5>
            </div>
            <div class="card-body">
                <!-- Add right after the form begins, before any other content -->

                <form id="gadProposalForm">
                    <!-- Page 1: Basic Information -->
                    <div class="form-page" id="page1">
                        <div class="row g-3">
                            

                            <div class="col-md-6">
                                <label for="year" class="form-label">Year</label>
                                <select class="form-select" id="year" name="year" required>
                                    <option value="">Select Year</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="quarter" class="form-label">Quarter</label>
                                <select class="form-select" id="quarter" name="quarter" required disabled>
                                    <option value="">Select Quarter</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label for="titleSelector" class="form-label">I. Available Activities</label>
                                <select class="form-select" id="titleSelector" name="titleSelector" required disabled>
                                    <option value="">Select Activity</option>
                                </select>
                                <input type="hidden" id="activityTitle" name="activityTitle">
                            </div>

                            <div class="col-md-6">
                                <label for="project" class="form-label">Project</label>
                                <input type="text" class="form-control" id="project" name="project" readonly>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="program" class="form-label">Program</label>
                                <input type="text" class="form-control" id="program" name="program" readonly>
                            </div>

                            <div class="col-md-6">
                                <label for="startDate" class="form-label">II. Start Date</label>
                                <input type="date" class="form-control" id="startDate" name="startDate" readonly disabled>
                            </div>

                            <div class="col-md-6">
                                <label for="endDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="endDate" name="endDate" readonly disabled>
                            </div>

                            <div class="col-md-12">
                                <label for="venue" class="form-label">Venue</label>
                                <input type="text" class="form-control" id="venue" name="venue" readonly disabled>
                            </div>

                            <div class="col-md-12">
                                <label for="deliveryMode" class="form-label">III. Mode of delivery</label>
                                <select class="form-select" id="deliveryMode" name="deliveryMode" required>
                                    <option value="">Select Mode</option>
                                    <option value="online">Online</option>
                                    <option value="face-to-face">Face-to-face</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Page 2: Project Team -->
                    <div class="form-page" id="page2" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">IV. Project Team</h5>
                            </div>

                            <div class="col-md-12">
                                <label for="projectLeaders" class="form-label">Project Leaders</label>
                                <input type="text" class="form-control" id="projectLeaders" name="projectLeaders" placeholder="Populated from PPAS data" readonly>
                                <div id="projectLeadersList" class="mt-2"></div>
                                <small class="text-muted personnel-note" style="display: none;">Project Leaders are populated from PPAS data and cannot be modified</small>
                            </div>

                            <div class="col-md-12">
                                <label for="leaderResponsibilities" class="form-label">Responsibilities</label>
                                <textarea class="form-control" id="leaderResponsibilities" name="leaderResponsibilities" rows="2"></textarea>
                            </div>

                            <div class="col-md-12">
                                <label for="assistantProjectLeaders" class="form-label">Assistant Project Leaders</label>
                                <input type="text" class="form-control" id="assistantProjectLeaders" name="assistantProjectLeaders" placeholder="Populated from PPAS data" readonly>
                                <div id="assistantProjectLeadersList" class="mt-2"></div>
                                <small class="text-muted personnel-note" style="display: none;">Assistant Project Leaders are populated from PPAS data and cannot be modified</small>
                            </div>

                            <div class="col-md-12">
                                <label for="assistantResponsibilities" class="form-label">Responsibilities</label>
                                <textarea class="form-control" id="assistantResponsibilities" name="assistantResponsibilities" rows="2"></textarea>
                            </div>

                            <div class="col-md-12">
                                <label for="projectStaff" class="form-label">Project Staff</label>
                                <input type="text" class="form-control" id="projectStaff" name="projectStaff" placeholder="Populated from PPAS data" readonly>
                                <div id="projectStaffList" class="mt-2"></div>
                                <small class="text-muted personnel-note" style="display: none;">Project Staff are populated from PPAS data and cannot be modified</small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Staff Responsibilities</label>
                                <div class="staff-responsibilities-container">
                                    <textarea class="form-control" id="staffResponsibilities" name="staffResponsibilities" rows="6" placeholder="Enter responsibilities (press Enter for new numbered item)"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Page 3: Partners and Participants -->
                    <div class="form-page" id="page3" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">V. Partner Office/College/Department</h5>
                            </div>

                            <div class="col-md-12">
                                <label for="partnerOffices" class="form-label">V. Partner Office/College/Department</label>
                                <input type="text" class="form-control" id="partnerOffices" name="partnerOffices" required>
                            </div>

                            <div class="col-12 mt-4">
                                <h5 class="mb-3">VI. Type of Participants</h5>
                                <p>Children from the families in Cuenca</p>
                            </div>

                            <div class="col-md-4">
                                <label for="maleBeneficiaries" class="form-label">Male</label>
                                <input type="number" class="form-control" id="maleBeneficiaries" name="maleBeneficiaries" value="50" min="0" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="femaleBeneficiaries" class="form-label">Female</label>
                                <input type="number" class="form-control" id="femaleBeneficiaries" name="femaleBeneficiaries" value="50" min="0" readonly>
</div>
                            <div class="col-md-4">
                                <label for="totalBeneficiaries" class="form-label">Total</label>
                                <input type="number" class="form-control" id="totalBeneficiaries" name="totalBeneficiaries" value="100" min="0" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Page 4: Rationale and Objectives -->
                    <div class="form-page" id="page4" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">VII. Rationale/Background</h5>
                            </div>

                            <div class="col-md-12">
                                <textarea class="form-control" id="rationale" name="rationale" rows="5" required></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <h5 class="mb-3">VIII. Objectives</h5>
                            </div>

                            <div class="col-md-12">
                                <label for="specificObjectives" class="form-label">Specific Objectives</label>
                                <textarea class="form-control" id="specificObjectives" name="specificObjectives" rows="5" required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Page 5: Activities and Methods -->
                    <div class="form-page" id="page5" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">IX. Description, Strategies, and Methods</h5>
                            </div>

                            <div class="col-md-12">
                                <label for="strategies" class="form-label">Strategies</label>
                                <textarea class="form-control" id="strategies" name="strategies" rows="3"></textarea>
                            </div>

                            <div class="col-md-12 mt-3">
                                <label class="form-label">Methods (Activities / Schedule)</label>
                                <div id="activitiesContainer">
                                    <!-- Activities will be added here dynamically -->
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addActivity()">
                                    <i class="fas fa-plus"></i> Add Activity
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Page 6: Work Plan -->
                    <div class="form-page" id="page6" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">X. Work Plan (Timeline of Activities/Gantt Chart)</h5>
                            </div>

                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="workPlanTable">
                                        <thead>
                                            <tr>
                                                <th>Activities</th>
                                                <!-- Date columns will be generated dynamically -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><input type="text" class="form-control form-control-sm" name="workplan_activity_1" placeholder="Add activity"></td>
                                                <!-- Checkboxes will be generated dynamically -->
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger remove-workplan-row">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addWorkPlanRowBtn">
                                    <i class="fas fa-plus"></i> Add Row
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Page 7: Budget -->
                    <div class="form-page" id="page7" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">XI. Financial Requirements and Source of Funds</h5>
                            </div>

                            <div class="col-md-6">
                                <label for="budgetSource" class="form-label">Source of Budget</label>
                                <select class="form-select" id="budgetSource" name="budgetSource" required>
                                    <option value="">Select Source</option>
                                    <option value="GAA">GAA</option>
                                    <option value="Income">Income</option>
                                    <option value="External">External</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="totalBudget" class="form-label">Total Budget</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="totalBudget" name="totalBudget" step="0.01" min="0" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="budgetBreakdown" class="form-label">Budget Breakdown</label>
                                <textarea class="form-control" id="budgetBreakdown" name="budgetBreakdown" rows="4" required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Page 8: Monitoring and Evaluation -->
                    <div class="form-page" id="page8" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">XII. Monitoring and Evaluation Mechanics / Plan</h5>
                            </div>

                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Objectives</th>
                                                <th>Performance Indicators</th>
                                                <th>Baseline Data</th>
                                                <th>Performance Target</th>
                                                <th>Data Source</th>
                                                <th>Collection Method</th>
                                                <th>Frequency of Data Collection</th>
                                                <th>Office/Persons Responsible</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><textarea class="form-control" name="monitoring_objectives_1" rows="2"></textarea></td>
                                                <td><textarea class="form-control" name="monitoring_indicators_1" rows="2"></textarea></td>
                                                <td><textarea class="form-control" name="monitoring_baseline_1" rows="2"></textarea></td>
                                                <td><textarea class="form-control" name="monitoring_target_1" rows="2"></textarea></td>
                                                <td><textarea class="form-control" name="monitoring_source_1" rows="2"></textarea></td>
                                                <td><textarea class="form-control" name="monitoring_method_1" rows="2"></textarea></td>
                                                <td><textarea class="form-control" name="monitoring_frequency_1" rows="2"></textarea></td>
                                                <td><textarea class="form-control" name="monitoring_responsible_1" rows="2"></textarea></td>
                                                <td class="text-center" style="vertical-align: middle;">
                                                    <button type="button" class="btn btn-sm btn-danger remove-row">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addMonitoringRowBtn">
                                    <i class="fas fa-plus"></i> Add Row
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Page 9: Sustainability Plan -->
                    <div class="form-page" id="page9" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">XIII. Sustainability Plan</h5>
                            </div>

                            <div class="col-md-12">
                                <textarea class="form-control" id="sustainabilityPlan" name="sustainabilityPlan" rows="6" required></textarea>
                            </div>
                        </div>

                        <!-- Add Print Button -->
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary" onclick="printProposal()" id="printBtn" style="display: none;">
                                <i class="fas fa-print"></i> Print Proposal
                            </button>
                            <button type="button" class="btn btn-info ms-2" onclick="printHtmlVersion()" id="printHtmlBtn" style="display: none;">
                                <i class="fas fa-file-alt"></i> HTML Print
                            </button>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" id="prevBtn" onclick="navigateForm(-1)" style="display: none;">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                        <div class="d-flex gap-3 ms-auto">
                            <!-- CRUD Action Buttons (Always visible) -->
                            <div class="d-inline-flex gap-2 me-3">
                                <button type="button" class="btn-icon" id="addBtn" title="Save Proposal">
                                    <i class="fas fa-save"></i>
                                </button>
                                <button type="button" class="btn-icon" id="editBtn" title="Edit Proposals">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn-icon" id="deleteBtn" title="Delete Proposal">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <button type="button" class="btn-icon" id="viewAllBtn" title="View All Proposals">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                            <!-- No Submit button anymore, using + button instead -->
                            <button type="button" class="btn btn-primary" id="nextBtn" onclick="navigateForm(1)">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                            <button type="button" class="btn btn-info ms-2" id="printBtn" onclick="window.location.href='print_proposal.php?id=' + document.getElementById('currentProposalId').value;" style="display: none;">
                                <i class="fas fa-print"></i> Generate PDF
                            </button>
                        </div>
                    </div>

                    <!-- Hidden input for proposal ID -->
                    <input type="hidden" id="currentProposalId" name="currentProposalId" value="">
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center w-100" id="editModalLabel">Select Proposal to Edit</h5>
                    <button type="button" class="btn-close d-none" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" id="searchEditProposal" class="form-control" placeholder="Search title...">
                        </div>
                        <div class="col-md-4">
                            <select id="yearEditFilter" class="form-select">
                                <option value="">All Years</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select id="quarterEditFilter" class="form-select">
                                <option value="">All Quarters</option>
                                <option value="Q1">Q1</option>
                                <option value="Q2">Q2</option>
                                <option value="Q3">Q3</option>
                                <option value="Q4">Q4</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Year</th>
                                    <th>Quarter</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                </tr>
                            </thead>
                            <tbody id="editModalBody">
                                <!-- Proposals will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        <nav>
                            <ul class="pagination" id="editModalPagination">
                                <!-- Pagination will be generated here -->
                            </ul>
                        </nav>
                    </div>
                    <input type="hidden" id="selectedEditId" name="selectedEditId" value="">
                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="button" id="confirmEditBtn" class="btn btn-primary" onclick="editProposalById()" disabled>
                            Edit Selected Proposal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center w-100" id="deleteModalLabel">Select Proposal to Delete</h5>
                    <button type="button" class="btn-close d-none" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" id="searchDeleteProposal" class="form-control" placeholder="Search title...">
                        </div>
                        <div class="col-md-4">
                            <select id="yearDeleteFilter" class="form-select">
                                <option value="">All Years</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select id="quarterDeleteFilter" class="form-select">
                                <option value="">All Quarters</option>
                                <option value="Q1">Q1</option>
                                <option value="Q2">Q2</option>
                                <option value="Q3">Q3</option>
                                <option value="Q4">Q4</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered table-hover" id="deleteModalTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Year</th>
                                    <th>Quarter</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                </tr>
                            </thead>
                            <tbody id="deleteModalBody">
                                <!-- Proposals will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        <nav>
                            <ul class="pagination" id="deleteModalPagination">
                                <!-- Pagination will be generated here -->
                            </ul>
                        </nav>
                    </div>
                    <input type="hidden" id="selectedDeleteId" name="selectedDeleteId" value="">
                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="button" id="confirmDeleteBtn" class="btn btn-danger" onclick="confirmDeleteProposalById()" disabled>
                            Delete Selected Proposal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View All Proposals Modal -->
    <div class="modal fade" id="viewAllModal" tabindex="-1" aria-labelledby="viewAllModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center w-100" id="viewAllModalLabel">All GAD Proposals</h5>
                    <button type="button" class="btn-close d-none" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" id="searchViewProposal" class="form-control" placeholder="Search title...">
                        </div>
                        <div class="col-md-3">
                            <select id="yearViewFilter" class="form-select">
                                <option value="">All Years</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="quarterViewFilter" class="form-select">
                                <option value="">All Quarters</option>
                                <option value="Q1">Q1</option>
                                <option value="Q2">Q2</option>
                                <option value="Q3">Q3</option>
                                <option value="Q4">Q4</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-sm btn-outline-primary" id="refreshViewBtn">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Year</th>
                                    <th>Quarter</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="viewAllModalBody">
                                <!-- Proposals will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        <nav>
                            <ul class="pagination" id="viewAllModalPagination">
                                <!-- Pagination will be generated here -->
                            </ul>
                        </nav>
                    </div>
                    <input type="hidden" id="selectedViewId" name="selectedViewId" value="">
                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Proposal Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center w-100" id="viewModalLabel">Select Proposal to View</h5>
                    <button type="button" class="btn-close d-none" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" id="searchViewItemProposal" class="form-control" placeholder="Search title...">
                        </div>
                        <div class="col-md-4">
                            <select id="yearViewItemFilter" class="form-select">
                                <option value="">All Years</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select id="quarterViewItemFilter" class="form-select">
                                <option value="">All Quarters</option>
                                <option value="Q1">Q1</option>
                                <option value="Q2">Q2</option>
                                <option value="Q3">Q3</option>
                                <option value="Q4">Q4</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Year</th>
                                    <th>Quarter</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                </tr>
                            </thead>
                            <tbody id="viewModalBody">
                                <!-- Proposals will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        <nav>
                            <ul class="pagination" id="viewModalPagination">
                                <!-- Pagination will be generated here -->
                            </ul>
                        </nav>
                    </div>
                    <input type="hidden" id="selectedViewModalId" name="selectedViewModalId" value="">
                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="button" id="confirmViewBtn" class="btn btn-primary" onclick="viewProposalById()" disabled>
                            View Selected Proposal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CRUD Management Interface Card (Hidden - to be removed) -->
    <div class="card mt-4" id="proposalsManagement" style="display: none;">
        <!-- We'll remove this later, keeping it for now to avoid breaking code -->
    </div>

    <script>
        let currentPage = 1;
        const totalPages = 9;
        // Create an object to track the personnel selections
        let selectedPersonnel = {
            projectLeaders: [],
            assistantProjectLeaders: [],
            projectStaff: []
        };

        // Global variables for proposals management
        let proposals = [];
        let filteredProposals = [];
        let proposalsCurrentPage = 1;
        const proposalsPerPage = 10;
        let editingProposalId = null;

        // Function to safely close a modal
        function safelyCloseModal(modal) {
            if (!modal) return;
            
            try {
                // First remove any aria-hidden attributes that might cause issues
                document.querySelectorAll('[aria-hidden="true"]').forEach(el => {
                    el.removeAttribute('aria-hidden');
                });
                
                // Use Bootstrap's API to close the modal safely
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                } else {
                    // Fallback if bootstrap modal instance not found
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                    
                    // Remove backdrop
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                    
                    // Clean up body classes and styles
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
                
                // Additional cleanup after a short delay
                setTimeout(() => {
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, 300);
            } catch (error) {
                console.error('Error closing modal:', error);
                // Extra fallback - try to remove all modals and backdrops
                try {
                    document.querySelectorAll('.modal').forEach(m => {
                        m.classList.remove('show');
                        m.style.display = 'none';
                    });
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                } catch (e) {
                    console.error('Failed fallback modal cleanup:', e);
                }
            }
        }

        // Initialize modal handling for close buttons
        document.addEventListener('DOMContentLoaded', function() {
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

            // Add event listeners to all modal close buttons
            document.querySelectorAll('.modal .btn-close').forEach(closeBtn => {
                closeBtn.addEventListener('click', function(e) {
                    try {
                        // Get the parent modal
                        const modal = this.closest('.modal');
                        if (!modal) return;
                        
                        // Use Bootstrap's API to close the modal safely
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        } else {
                            // Fallback if bootstrap modal instance not found
                            modal.classList.remove('show');
                            modal.style.display = 'none';
                            const backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) backdrop.remove();
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';
                        }
                    } catch (error) {
                        console.error('Error closing modal:', error);
                        // Extra fallback - try to remove all modals and backdrops
                        try {
                            document.querySelectorAll('.modal').forEach(m => {
                                m.classList.remove('show');
                                m.style.display = 'none';
                            });
                            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';
                        } catch (e) {
                            console.error('Failed fallback modal cleanup:', e);
                        }
                    }
                });
            });
        });

        // Add button styles for CRUD operations
        const buttonStyles = `
            .btn-icon {
                width: 38px;
                height: 38px;
                padding: 0;
                border-radius: 12px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: none;
                transition: all 0.2s ease;
                margin-left: 5px;
            }

            .btn-icon i {
                font-size: 1rem;
            }

            /* Refresh button */
            #refreshBtn {
                background: rgba(0, 123, 255, 0.1);
                color: #0d6efd;
            }

            #refreshBtn:hover {
                background: #0d6efd;
                color: white;
            }

            /* New proposal button */
            #newProposalBtn {
                background: rgba(25, 135, 84, 0.1);
                color: #198754;
            }

            #newProposalBtn:hover {
                background: #198754;
                color: white;
            }

            /* Edit button */
            .edit-btn {
                background: rgba(255, 193, 7, 0.1);
                color: #ffc107;
            }

            .edit-btn:hover {
                background: #ffc107;
                color: white;
            }

            /* Delete button */
            .delete-btn {
                background: rgba(220, 53, 69, 0.1);
                color: #dc3545;
            }

            .delete-btn:hover {
                background: #dc3545;
                color: white;
            }

            /* View button */
            .view-btn {
                background: rgba(13, 110, 253, 0.1);
                color: #0d6efd;
            }

            .view-btn:hover {
                background: #0d6efd;
                color: white;
            }

            /* Print button */
            .print-btn {
                background: rgba(108, 117, 125, 0.1);
                color: #6c757d;
            }

            .print-btn:hover {
                background: #6c757d;
                color: white;
            }
        `;

        // Add styles to the document
        const styleElement = document.createElement('style');
        styleElement.innerHTML = buttonStyles;
        document.head.appendChild(styleElement);

        // Show/hide print button based on proposal ID
        function updatePrintButtonVisibility() {
            const proposalId = document.getElementById('currentProposalId').value;
            const printBtn = document.getElementById('printBtn');
            if (printBtn) {
                printBtn.style.display = proposalId ? 'inline-block' : 'none';
            }
        }

        // Update this function to show print button after successful submission
        function handleFormSubmission(response) {
            if (response.success) {
                document.getElementById('currentProposalId').value = response.proposalId;
                
                // Remove submitBtn reference since we're using the save button
                
                // Show print buttons
                const printBtn = document.getElementById('printBtn');
                if (printBtn) {
                    printBtn.style.display = 'inline-block';
                }
                
                const printHtmlBtn = document.getElementById('printHtmlBtn');
                if (printHtmlBtn) {
                    printHtmlBtn.style.display = 'inline-block';
                }
                
                // Show success message
                Swal.fire({
                    title: 'Success',
                    text: 'Proposal saved successfully!',
                    icon: 'success',
                    confirmButtonColor: '#6a1b9a'
                }).then(() => {
                    // Reload proposals list if the management view is visible
                    if (document.getElementById('proposalsManagement').style.display !== 'none') {
                        loadProposals();
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'Failed to save proposal.',
                    icon: 'error',
                    confirmButtonColor: '#6a1b9a'
                });
            }
        }

        // Call this when the page loads to set initial print button state
        document.addEventListener('DOMContentLoaded', function() {
            updatePrintButtonVisibility();
            
            // Add event listener to the first monitoring row's remove button
            const firstMonitoringRow = document.querySelector('#page8 table tbody tr');
            if (firstMonitoringRow) {
                const removeButton = firstMonitoringRow.querySelector('.remove-row');
                if (removeButton) {
                    removeButton.addEventListener('click', function() {
                        firstMonitoringRow.remove();
                        renumberMonitoringRows();
                    });
                }
            }

            // Initialize CRUD button handlers
            initCrudButtons();
            
            // Add event listeners to all modal close buttons for safe closing
            document.querySelectorAll('.modal .btn-close, .modal .btn-secondary[data-bs-dismiss="modal"]').forEach(closeBtn => {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent default button behavior
                    const modal = this.closest('.modal');
                    if (modal) {
                        // Close modal directly using Bootstrap's API
                        try {
                            // First remove any aria-hidden attributes that might cause issues
                            document.querySelectorAll('[aria-hidden="true"]').forEach(el => {
                                el.removeAttribute('aria-hidden');
                            });
                            
                            // Use Bootstrap's API to close the modal safely
                            const bsModal = bootstrap.Modal.getInstance(modal);
                            if (bsModal) {
                                bsModal.hide();
                            } else {
                                // Fallback if bootstrap modal instance not found
                                modal.classList.remove('show');
                                modal.style.display = 'none';
                                
                                // Remove backdrop
                                const backdrop = document.querySelector('.modal-backdrop');
                                if (backdrop) backdrop.remove();
                                
                                // Clean up body classes and styles
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';
                            }
                            
                            // Additional cleanup after a short delay
                            setTimeout(() => {
                                document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';
                            }, 300);
                        } catch (error) {
                            console.error('Error closing modal:', error);
                            // Extra fallback - try to remove all modals and backdrops
                            try {
                                document.querySelectorAll('.modal').forEach(m => {
                                    m.classList.remove('show');
                                    m.style.display = 'none';
                                });
                                document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';
                            } catch (e) {
                                console.error('Failed fallback modal cleanup:', e);
                            }
                        }
                    }
                });
            });
            
            // Fix for Bootstrap modal issues with aria-hidden
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'aria-hidden' && mutation.target.getAttribute('aria-hidden') === 'true') {
                        // If a focused element is a descendant of an element with aria-hidden="true", remove the aria-hidden
                        if (mutation.target.contains(document.activeElement)) {
                            mutation.target.removeAttribute('aria-hidden');
                            console.log('Removed problematic aria-hidden attribute from element containing focus');
                        }
                    }
                });
            });
            
            // Start observing the document for aria-hidden changes
            observer.observe(document.body, { 
                attributes: true, 
                attributeFilter: ['aria-hidden'], 
                subtree: true 
            });
        });
        
        // Initialize CRUD buttons
        function initCrudButtons() {
            // Save button (previously Add)
            document.getElementById('addBtn').addEventListener('click', function() {
                // Check if we're on the last page
                if (currentPage !== totalPages) {
                    Swal.fire({
                        title: 'Not Ready to Submit',
                        text: 'Please complete all pages before submitting the proposal.',
                        icon: 'warning',
                        confirmButtonColor: '#6a1b9a'
                    });
                    return;
                }
                
                // Validate the form
                if (!validateCurrentPage()) {
                    Swal.fire({
                        title: 'Required Fields',
                        text: 'Please fill in all required fields before submitting.',
                        icon: 'warning',
                        confirmButtonColor: '#6a1b9a'
                    });
                    return;
                }
                
                // Submit the form
                submitProposal();
            });
            
            // Edit button
            document.getElementById('editBtn').addEventListener('click', function() {
                loadProposalsForEditModal();
                const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.show();
            });
            
            // Delete button
            document.getElementById('deleteBtn').addEventListener('click', function() {
                loadProposalsForDeleteModal();
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });
            
            // View All button
            document.getElementById('viewAllBtn').addEventListener('click', function() {
                loadProposalsForViewModal();
                const viewAllModal = new bootstrap.Modal(document.getElementById('viewAllModal'));
                viewAllModal.show();
            });
            
            // Setup search and filters for edit modal
            document.getElementById('searchEditProposal').addEventListener('input', function() {
                filterModalProposals('edit');
            });
            
            document.getElementById('yearEditFilter').addEventListener('change', function() {
                filterModalProposals('edit');
            });
            
            document.getElementById('quarterEditFilter').addEventListener('change', function() {
                filterModalProposals('edit');
            });
            
            // Setup search and filters for delete modal
            document.getElementById('searchDeleteProposal').addEventListener('input', function() {
                filterModalProposals('delete');
            });
            
            document.getElementById('yearDeleteFilter').addEventListener('change', function() {
                filterModalProposals('delete');
            });
            
            document.getElementById('quarterDeleteFilter').addEventListener('change', function() {
                filterModalProposals('delete');
            });
            
            // Setup search and filters for view all modal
            document.getElementById('searchViewProposal').addEventListener('input', function() {
                filterModalProposals('view');
            });
            
            document.getElementById('yearViewFilter').addEventListener('change', function() {
                filterModalProposals('view');
            });
            
            document.getElementById('quarterViewFilter').addEventListener('change', function() {
                filterModalProposals('view');
            });
            
            // Refresh button for view all modal
            document.getElementById('refreshViewBtn').addEventListener('click', function() {
                loadProposalsForViewModal();
            });
        }
        
        // Function to submit the proposal
        function submitProposal() {
            try {
                const form = document.getElementById('gadProposalForm');
                const formData = new FormData(form);
                
                // Add PPAS ID if available
                const ppasId = document.getElementById('ppasId')?.value;
                if (ppasId) {
                    formData.append('ppasId', ppasId);
                }
                
                // Show loading indicator
                Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait while we save your GAD proposal',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Collect activities data
                const activitiesContainer = document.getElementById('activitiesContainer');
                const activityItems = activitiesContainer.querySelectorAll('.activity-item');
                
                activityItems.forEach((item, index) => {
                    const titleInput = item.querySelector('input[type="text"]');
                    const detailsTextarea = item.querySelector('textarea');
                    
                    if (titleInput && titleInput.value.trim() !== '') {
                        formData.append(`activity_title_${index + 1}`, titleInput.value);
                        if (detailsTextarea) {
                            formData.append(`activity_details_${index + 1}`, detailsTextarea.value);
                        }
                    }
                });
                
                // Collect monitoring data
                const monitoringTable = document.querySelector('#page8 table tbody');
                if (monitoringTable) {
                    const monitoringRows = monitoringTable.querySelectorAll('tr');
                    monitoringRows.forEach((row, index) => {
                        const textareas = row.querySelectorAll('textarea');
                        if (textareas.length === 8) {
                            formData.append(`monitoring_objectives_${index + 1}`, textareas[0].value);
                            formData.append(`monitoring_indicators_${index + 1}`, textareas[1].value);
                            formData.append(`monitoring_baseline_${index + 1}`, textareas[2].value);
                            formData.append(`monitoring_target_${index + 1}`, textareas[3].value);
                            formData.append(`monitoring_source_${index + 1}`, textareas[4].value);
                            formData.append(`monitoring_method_${index + 1}`, textareas[5].value);
                            formData.append(`monitoring_frequency_${index + 1}`, textareas[6].value);
                            formData.append(`monitoring_responsible_${index + 1}`, textareas[7].value);
                        }
                    });
                }
                
                // Collect work plan data
                const workplanTable = document.querySelector('#workPlanTable tbody');
                if (workplanTable) {
                    const workplanRows = workplanTable.querySelectorAll('tr');
                    workplanRows.forEach((row, index) => {
                        const activityInput = row.querySelector('td:first-child input');
                        if (activityInput && activityInput.value.trim() !== '') {
                            formData.append(`workplan_activity_${index + 1}`, activityInput.value);
                            
                            // Get timeline data from hidden input or from checkboxes
                            let timelineData = '';
                            const hiddenInput = row.querySelector(`input[name="workplan_timeline_${index + 1}"]`);
                            if (hiddenInput) {
                                timelineData = hiddenInput.value;
                            } else {
                                const checkboxes = row.querySelectorAll('.workplan-checkbox');
                                timelineData = Array.from(checkboxes)
                                    .map((checkbox, i) => checkbox.checked ? i + 1 : null)
                                    .filter(val => val !== null)
                                    .join(',');
                            }
                            
                            formData.append(`workplan_timeline_${index + 1}`, timelineData);
                        }
                    });
                }
                
                // Send AJAX request
                fetch('save_gad_proposal.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    handleFormSubmission(result);
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'An error occurred while saving the proposal. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#6a1b9a'
                    });
                });
            } catch (e) {
                console.error('Exception:', e);
                Swal.fire({
                    title: 'Error',
                    text: 'An error occurred: ' + e.message,
                    icon: 'error',
                    confirmButtonColor: '#6a1b9a'
                });
            }
        }

        // Reset the form for a new proposal
        function resetForm() {
            document.getElementById('gadProposalForm').reset();
            document.getElementById('currentProposalId').value = '';
            
            // Clear personnel lists
            clearPersonnelLists();
            
            // Reset activities
            resetActivities();
            
            // Show the first page
            currentPage = 1;
            showPage(currentPage);
            
            // Reset navigation buttons
            document.getElementById('prevBtn').style.display = 'none';
            document.getElementById('nextBtn').style.display = 'block';
            // Remove submitBtn reference since we're using the save button
            
            // Update print button visibility
            updatePrintButtonVisibility();
        }
        
        // Global variables for modals
        let allProposals = [];
        let editModalProposals = [];
        let deleteModalProposals = [];
        let viewModalProposals = [];
        const rowsPerModalPage = 10;
        let editModalPage = 1;
        let deleteModalPage = 1;
        let viewModalPage = 1;
        
        // Load proposals for edit modal
        function loadProposalsForEditModal() {
            fetch('get_gad_proposals.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        editModalProposals = data.proposals;
                        
                        // Populate year filter
                        const years = [...new Set(editModalProposals.map(p => p.year))].sort((a, b) => b - a);
                        const yearFilter = document.getElementById('yearEditFilter');
                        yearFilter.innerHTML = '<option value="">All Years</option>';
                        years.forEach(year => {
                            yearFilter.innerHTML += `<option value="${year}">${year}</option>`;
                        });
                        
                        // Reset page
                        editModalPage = 1;
                        
                        // Update table
                        updateEditModalTable();
                    } else {
                        showErrorAlert(data.message || 'Failed to load proposals');
                    }
                })
                .catch(error => {
                    console.error('Error loading proposals:', error);
                    showErrorAlert('Failed to load proposals. Please try again.');
                });
        }
        
        // Load proposals for delete modal
        function loadProposalsForDeleteModal() {
            fetch('get_gad_proposals.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        deleteModalProposals = data.proposals;
                        
                        // Populate year filter
                        const years = [...new Set(deleteModalProposals.map(p => p.year))].sort((a, b) => b - a);
                        const yearFilter = document.getElementById('yearDeleteFilter');
                        yearFilter.innerHTML = '<option value="">All Years</option>';
                        years.forEach(year => {
                            yearFilter.innerHTML += `<option value="${year}">${year}</option>`;
                        });
                        
                        // Reset page
                        deleteModalPage = 1;
                        
                        // Update table
                        updateDeleteModalTable();
                    } else {
                        showErrorAlert(data.message || 'Failed to load proposals');
                    }
                })
                .catch(error => {
                    console.error('Error loading proposals:', error);
                    showErrorAlert('Failed to load proposals. Please try again.');
                });
        }
        
        // Load proposals for view all modal
        function loadProposalsForViewModal() {
            fetch('get_gad_proposals.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        viewModalProposals = data.proposals;
                        
                        // Populate year filter
                        const years = [...new Set(viewModalProposals.map(p => p.year))].sort((a, b) => b - a);
                        const yearFilter = document.getElementById('yearViewFilter');
                        yearFilter.innerHTML = '<option value="">All Years</option>';
                        years.forEach(year => {
                            yearFilter.innerHTML += `<option value="${year}">${year}</option>`;
                        });
                        
                        // Reset page
                        viewModalPage = 1;
                        
                        // Update tables
                        updateViewAllModalTable();
                        updateViewModalTable();
                    } else {
                        showErrorAlert(data.message || 'Failed to load proposals');
                    }
                })
                .catch(error => {
                    console.error('Error loading proposals:', error);
                    showErrorAlert('Failed to load proposals. Please try again.');
                });
        }
        
        // Filter proposals in modals
        function filterModalProposals(modalType) {
            try {
                let searchInput, yearFilter, quarterFilter;
                
                if (modalType === 'edit') {
                    searchInput = document.getElementById('searchEditProposal')?.value?.toLowerCase() || '';
                    yearFilter = document.getElementById('yearEditFilter')?.value || '';
                    quarterFilter = document.getElementById('quarterEditFilter')?.value || '';
                    
                    editModalProposals = Array.isArray(editModalProposals) ? editModalProposals : [];
                    
                    const filtered = editModalProposals.filter(proposal => {
                        if (!proposal) return false;
                        
                        const title = proposal.activity_title || proposal.title || '';
                        const year = proposal.year ? proposal.year.toString() : '';
                        const quarter = proposal.quarter || '';
                        
                        const matchTitle = title.toLowerCase().includes(searchInput);
                        const matchYear = !yearFilter || year === yearFilter;
                        const matchQuarter = !quarterFilter || quarter === quarterFilter;
                        
                        return matchTitle && matchYear && matchQuarter;
                    });
                    
                    editModalProposals = filtered;
                    editModalPage = 1;
                    updateEditModalTable();
                } 
                else if (modalType === 'delete') {
                    searchInput = document.getElementById('searchDeleteProposal')?.value?.toLowerCase() || '';
                    yearFilter = document.getElementById('yearDeleteFilter')?.value || '';
                    quarterFilter = document.getElementById('quarterDeleteFilter')?.value || '';
                    
                    deleteModalProposals = Array.isArray(deleteModalProposals) ? deleteModalProposals : [];
                    
                    const filtered = deleteModalProposals.filter(proposal => {
                        if (!proposal) return false;
                        
                        const title = proposal.activity_title || proposal.title || '';
                        const year = proposal.year ? proposal.year.toString() : '';
                        const quarter = proposal.quarter || '';
                        
                        const matchTitle = title.toLowerCase().includes(searchInput);
                        const matchYear = !yearFilter || year === yearFilter;
                        const matchQuarter = !quarterFilter || quarter === quarterFilter;
                        
                        return matchTitle && matchYear && matchQuarter;
                    });
                    
                    deleteModalProposals = filtered;
                    deleteModalPage = 1;
                    updateDeleteModalTable();
                }
                else if (modalType === 'view') {
                    searchInput = document.getElementById('searchViewProposal')?.value?.toLowerCase() || '';
                    yearFilter = document.getElementById('yearViewFilter')?.value || '';
                    quarterFilter = document.getElementById('quarterViewFilter')?.value || '';
                    
                    viewModalProposals = Array.isArray(viewModalProposals) ? viewModalProposals : [];
                    
                    const filtered = viewModalProposals.filter(proposal => {
                        if (!proposal) return false;
                        
                        const title = proposal.activity_title || proposal.title || '';
                        const year = proposal.year ? proposal.year.toString() : '';
                        const quarter = proposal.quarter || '';
                        
                        const matchTitle = title.toLowerCase().includes(searchInput);
                        const matchYear = !yearFilter || year === yearFilter;
                        const matchQuarter = !quarterFilter || quarter === quarterFilter;
                        
                        return matchTitle && matchYear && matchQuarter;
                    });
                    
                    viewModalProposals = filtered;
                    viewModalPage = 1;
                    updateViewModalTable();
                }
            } catch (error) {
                console.error(`Error filtering modal proposals for ${modalType}:`, error);
            }
        }
        
        // Update edit modal table
        function updateEditModalTable() {
            try {
                const tableBody = document.getElementById('editModalBody');
                if (!tableBody) {
                    console.warn('Edit modal table body not found');
                    return;
                }
                
                // Calculate pagination
                editModalProposals = Array.isArray(editModalProposals) ? editModalProposals : [];
                const totalProposals = editModalProposals.length;
                const totalPages = Math.ceil(totalProposals / rowsPerModalPage);
                const startIndex = (editModalPage - 1) * rowsPerModalPage;
                const endIndex = Math.min(startIndex + rowsPerModalPage, totalProposals);
                
                // Get page data
                const pageData = editModalProposals.slice(startIndex, endIndex);
                
                // Clear table
                tableBody.innerHTML = '';
                
                // Show message if no data
                if (pageData.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center">No proposals found</td>
                        </tr>
                    `;
                    return;
                }
                
                // Add rows for each proposal
                pageData.forEach(proposal => {
                    if (!proposal) return;
                    
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', proposal.id);
                    row.style.cursor = 'pointer';
                    
                    // Format dates safely
                    let startDate = "Not available";
                    let endDate = "Not available";
                    
                    try {
                        if (proposal.start_date) {
                            startDate = new Date(proposal.start_date).toLocaleDateString();
                        }
                        if (proposal.end_date) {
                            endDate = new Date(proposal.end_date).toLocaleDateString();
                        }
                    } catch (e) {
                        console.error('Error formatting dates:', e);
                    }
                    
                    // Use either activity_title or title
                    const title = proposal.activity_title || proposal.title || 'Untitled';
                    const year = proposal.year || '';
                    const quarter = proposal.quarter || '';
                    
                    row.innerHTML = `
                        <td>${proposal.id}</td>
                        <td>${title}</td>
                        <td>${year}</td>
                        <td>${quarter}</td>
                        <td>${startDate}</td>
                        <td>${endDate}</td>
                    `;
                    
                    // Add click event to select the proposal
                    row.addEventListener('click', function() {
                        try {
                            // Remove selected class from all rows
                            document.querySelectorAll('#editModalBody tr').forEach(tr => {
                                tr.classList.remove('selected');
                            });
                            
                            // Add selected class to this row
                            this.classList.add('selected');
                            
                            // Set the selected ID if the input exists
                            const selectedIdInput = document.getElementById('selectedEditId');
                            if (selectedIdInput) {
                                selectedIdInput.value = proposal.id;
                            } else {
                                console.warn('Could not find selectedEditId input element');
                            }
                            
                            // If edit confirmation button exists, enable it
                            const confirmEditBtn = document.getElementById('confirmEditBtn');
                            if (confirmEditBtn) {
                                confirmEditBtn.disabled = false;
                            }
                        } catch (error) {
                            console.error('Error in row click handler:', error);
                        }
                    });
                    
                    tableBody.appendChild(row);
                });
                
                // Update pagination
                updateModalPagination('edit', totalPages);
            } catch (error) {
                console.error('Error updating edit modal table:', error);
            }
        }
        
        // Update delete modal table
        function updateDeleteModalTable() {
            try {
                console.log("Updating delete modal table...");
                const tableBody = document.getElementById('deleteModalBody');
                if (!tableBody) {
                    console.warn('Delete modal table body not found');
                    return;
                }
                
                // Calculate pagination
                deleteModalProposals = Array.isArray(deleteModalProposals) ? deleteModalProposals : [];
                console.log(`Found ${deleteModalProposals.length} proposals for delete modal`);
                
                const totalProposals = deleteModalProposals.length;
                const totalPages = Math.ceil(totalProposals / rowsPerModalPage);
                const startIndex = (deleteModalPage - 1) * rowsPerModalPage;
                const endIndex = Math.min(startIndex + rowsPerModalPage, totalProposals);
                
                // Get page data
                const pageData = deleteModalProposals.slice(startIndex, endIndex);
                
                // Clear table
                tableBody.innerHTML = '';
                
                // Show message if no data
                if (pageData.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center">No proposals found</td>
                        </tr>
                    `;
                    return;
                }
                
                // Add rows for each proposal
                pageData.forEach(proposal => {
                    if (!proposal) return;
                    
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', proposal.id);
                    
                    // Format dates safely
                    let startDate = "Not available";
                    let endDate = "Not available";
                    
                    try {
                        if (proposal.start_date) {
                            startDate = new Date(proposal.start_date).toLocaleDateString();
                        }
                        if (proposal.end_date) {
                            endDate = new Date(proposal.end_date).toLocaleDateString();
                        }
                    } catch (e) {
                        console.error('Error formatting dates:', e);
                    }
                    
                    // Use either activity_title or title
                    const title = proposal.activity_title || proposal.title || 'Untitled';
                    const year = proposal.year || '';
                    const quarter = proposal.quarter || '';
                    
                    row.innerHTML = `
                        <td>${proposal.id}</td>
                        <td>${title}</td>
                        <td>${year}</td>
                        <td>${quarter}</td>
                        <td>${startDate}</td>
                        <td>${endDate}</td>
                    `;
                    
                    // Add click event to select the proposal
                    row.addEventListener('click', function(e) {
                        try {
                            console.log(`Row clicked for proposal ID: ${proposal.id}`);
                            
                            // Remove selected class from all rows
                            document.querySelectorAll('#deleteModalTable tr, #deleteModalBody tr').forEach(tr => {
                                tr.classList.remove('selected');
                            });
                            
                            // Add selected class to this row
                            this.classList.add('selected');
                            
                            // Set the selected ID
                            const selectedIdInput = document.getElementById('selectedDeleteId');
                            if (selectedIdInput) {
                                selectedIdInput.value = proposal.id;
                                console.log(`Set selectedDeleteId input value to: ${proposal.id}`);
                            } else {
                                console.warn('Could not find selectedDeleteId input element');
                            }
                            
                            // Enable the delete confirmation button
                            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
                            if (confirmDeleteBtn) {
                                confirmDeleteBtn.disabled = false;
                                console.log('Enabled delete confirmation button');
                            }
                            
                            // Highlight the selection with a flash effect
                            this.style.transition = 'background-color 0.3s';
                            const originalColor = this.style.backgroundColor;
                            this.style.backgroundColor = '#c8e6c9';
                            setTimeout(() => {
                                this.style.backgroundColor = originalColor;
                            }, 300);
                            
                            // Prevent event bubbling
                            e.stopPropagation();
                        } catch (error) {
                            console.error('Error in row click handler:', error);
                        }
                    });
                    
                    tableBody.appendChild(row);
                });
                
                // Update pagination
                updateModalPagination('delete', totalPages);
                console.log("Delete modal table updated successfully");
            } catch (error) {
                console.error('Error updating delete modal table:', error);
            }
        }
        
        // Update view all modal table
        function updateViewAllModalTable() {
            try {
                const tableBody = document.getElementById('viewAllModalBody');
                if (!tableBody) {
                    console.warn('View all modal table body not found');
                    return;
                }
                
                // Calculate pagination
                viewModalProposals = Array.isArray(viewModalProposals) ? viewModalProposals : [];
                const totalProposals = viewModalProposals.length;
                const totalPages = Math.ceil(totalProposals / rowsPerModalPage);
                const startIndex = (viewModalPage - 1) * rowsPerModalPage;
                const endIndex = Math.min(startIndex + rowsPerModalPage, totalProposals);
                
                // Get page data
                const pageData = viewModalProposals.slice(startIndex, endIndex);
                
                // Clear table
                tableBody.innerHTML = '';
                
                // Show message if no data
                if (pageData.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center">No proposals found</td>
                        </tr>
                    `;
                    return;
                }
                
                // Add rows for each proposal
                pageData.forEach(proposal => {
                    if (!proposal) return;
                    
                    // Format dates safely
                    let startDate = "Not available";
                    let endDate = "Not available";
                    
                    try {
                        if (proposal.start_date) {
                            startDate = new Date(proposal.start_date).toLocaleDateString();
                        }
                        if (proposal.end_date) {
                            endDate = new Date(proposal.end_date).toLocaleDateString();
                        }
                    } catch (e) {
                        console.error('Error formatting dates:', e);
                    }
                    
                    // Use either activity_title or title
                    const title = proposal.activity_title || proposal.title || 'Untitled';
                    const year = proposal.year || '';
                    const quarter = proposal.quarter || '';
                    
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${proposal.id}</td>
                        <td>${title}</td>
                        <td>${year}</td>
                        <td>${quarter}</td>
                        <td>${startDate}</td>
                        <td>${endDate}</td>
                        <td>
                            <button class="btn-icon view-btn" onclick="viewProposal(${proposal.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-icon edit-btn" onclick="editProposal(${proposal.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon delete-btn" onclick="confirmDeleteProposal(${proposal.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    
                    tableBody.appendChild(row);
                });
                
                // Update pagination
                updateModalPagination('viewAll', totalPages);
            } catch (error) {
                console.error('Error updating view all modal table:', error);
            }
        }
        
        // Update view modal table
        function updateViewModalTable() {
            try {
                const tableBody = document.getElementById('viewModalBody');
                if (!tableBody) {
                    console.warn('View modal table body not found');
                    return;
                }
                
                // Calculate pagination
                viewModalProposals = Array.isArray(viewModalProposals) ? viewModalProposals : [];
                const totalProposals = viewModalProposals.length;
                const totalPages = Math.ceil(totalProposals / rowsPerModalPage);
                const startIndex = (viewModalPage - 1) * rowsPerModalPage;
                const endIndex = Math.min(startIndex + rowsPerModalPage, totalProposals);
                
                // Get page data
                const pageData = viewModalProposals.slice(startIndex, endIndex);
                
                // Clear table
                tableBody.innerHTML = '';
                
                // Show message if no data
                if (pageData.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center">No proposals found</td>
                        </tr>
                    `;
                    return;
                }
                
                // Add rows for each proposal
                pageData.forEach(proposal => {
                    if (!proposal) return;
                    
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', proposal.id);
                    row.style.cursor = 'pointer';
                    
                    // Format dates safely
                    let startDate = "Not available";
                    let endDate = "Not available";
                    
                    try {
                        if (proposal.start_date) {
                            startDate = new Date(proposal.start_date).toLocaleDateString();
                        }
                        if (proposal.end_date) {
                            endDate = new Date(proposal.end_date).toLocaleDateString();
                        }
                    } catch (e) {
                        console.error('Error formatting dates:', e);
                    }
                    
                    // Use either activity_title or title
                    const title = proposal.activity_title || proposal.title || 'Untitled';
                    const year = proposal.year || '';
                    const quarter = proposal.quarter || '';
                    
                    row.innerHTML = `
                        <td>${proposal.id}</td>
                        <td>${title}</td>
                        <td>${year}</td>
                        <td>${quarter}</td>
                        <td>${startDate}</td>
                        <td>${endDate}</td>
                    `;
                    
                    // Add click event to select the proposal
                    row.addEventListener('click', function() {
                        try {
                            // Remove selected class from all rows
                            document.querySelectorAll('#viewModalBody tr').forEach(tr => {
                                tr.classList.remove('selected');
                            });
                            
                            // Add selected class to this row
                            this.classList.add('selected');
                            
                            // Set the selected ID if the input exists
                            const selectedIdInput = document.getElementById('selectedViewModalId');
                            if (selectedIdInput) {
                                selectedIdInput.value = proposal.id;
                            } else {
                                console.warn('Could not find selectedViewModalId input element');
                            }
                            
                            // If view confirmation button exists, enable it
                            const confirmViewBtn = document.getElementById('confirmViewBtn');
                            if (confirmViewBtn) {
                                confirmViewBtn.disabled = false;
                            }
                        } catch (error) {
                            console.error('Error in view row click handler:', error);
                        }
                    });
                    
                    tableBody.appendChild(row);
                });
                
                // Update pagination
                updateModalPagination('view', totalPages);
            } catch (error) {
                console.error('Error updating view modal table:', error);
            }
        }
        
        // Update modal pagination
        function updateModalPagination(modalType, totalPages) {
            try {
                let pagination, currentPage;
                
                if (modalType === 'edit') {
                    pagination = document.getElementById('editModalPagination');
                    currentPage = editModalPage;
                } else if (modalType === 'delete') {
                    pagination = document.getElementById('deleteModalPagination');
                    currentPage = deleteModalPage;
                } else if (modalType === 'view') {
                    pagination = document.getElementById('viewModalPagination');
                    currentPage = viewModalPage;
                } else if (modalType === 'viewAll') {
                    pagination = document.getElementById('viewAllModalPagination');
                    currentPage = viewModalPage;
                } else {
                    console.warn('Invalid modal type for pagination:', modalType);
                    return;
                }
                
                if (!pagination) {
                    console.warn(`Pagination element not found for ${modalType} modal`);
                    return;
                }
                
                // Clear pagination
                pagination.innerHTML = '';
                
                // Don't show pagination if only one page
                if (totalPages <= 1) return;
                
                // Add previous button
                const prevLi = document.createElement('li');
                prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                prevLi.innerHTML = `
                    <a class="page-link" href="#" aria-label="Previous" onclick="event.preventDefault(); changeModalPage('${modalType}', ${currentPage - 1})">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                `;
                pagination.appendChild(prevLi);
                
                // Add page numbers
                for (let i = 1; i <= totalPages; i++) {
                    const li = document.createElement('li');
                    li.className = `page-item ${currentPage === i ? 'active' : ''}`;
                    li.innerHTML = `
                        <a class="page-link" href="#" onclick="event.preventDefault(); changeModalPage('${modalType}', ${i})">${i}</a>
                    `;
                    pagination.appendChild(li);
                }
                
                // Add next button
                const nextLi = document.createElement('li');
                nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                nextLi.innerHTML = `
                    <a class="page-link" href="#" aria-label="Next" onclick="event.preventDefault(); changeModalPage('${modalType}', ${currentPage + 1})">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                `;
                pagination.appendChild(nextLi);
            } catch (error) {
                console.error(`Error updating ${modalType} modal pagination:`, error);
            }
        }

        // Function to change modal pagination page
        function changeModalPage(modalType, page) {
            try {
                let totalPages;
                
                if (modalType === 'edit') {
                    totalPages = Math.ceil(editModalProposals.length / rowsPerModalPage);
                    if (page >= 1 && page <= totalPages) {
                        editModalPage = page;
                        updateEditModalTable();
                    }
                } else if (modalType === 'delete') {
                    totalPages = Math.ceil(deleteModalProposals.length / rowsPerModalPage);
                    if (page >= 1 && page <= totalPages) {
                        deleteModalPage = page;
                        updateDeleteModalTable();
                    }
                } else if (modalType === 'view') {
                    totalPages = Math.ceil(viewModalProposals.length / rowsPerModalPage);
                    if (page >= 1 && page <= totalPages) {
                        viewModalPage = page;
                        updateViewModalTable();
                    }
                } else if (modalType === 'viewAll') {
                    totalPages = Math.ceil(viewModalProposals.length / rowsPerModalPage);
                    if (page >= 1 && page <= totalPages) {
                        viewModalPage = page;
                        updateViewAllModalTable();
                    }
                }
            } catch (error) {
                console.error(`Error changing ${modalType} modal page:`, error);
            }
        }
        
        // View a proposal
        function viewProposal(id) {
            window.location.href = `print_proposal.php?id=${id}`;
        }
        
        // Edit a proposal
        function editProposal(id) {
            console.log(`Starting to edit proposal with ID: ${id}`);
            
            // Set the editing proposal ID
            editingProposalId = id;
            
            // Hide the proposals management card
            document.getElementById('proposalsManagement').style.display = 'none';
            
            // Fetch proposal details
            console.log(`Fetching proposal data from get_gad_proposal.php?id=${id}`);
            fetch(`get_gad_proposal.php?id=${id}`)
                .then(response => {
                    console.log("Received response from server");
                    return response.json();
                })
                .then(data => {
                    console.log("Parsed response data:", data);
                    if (data.success) {
                        console.log("Proposal data retrieved successfully");
                        
                        // Reset form
                        document.getElementById('gadProposalForm').reset();
                        
                        // Set ID in the hidden field
                        document.getElementById('currentProposalId').value = id;
                        console.log(`Set currentProposalId to ${id}`);
                        
                        // Populate form fields
                        populateFormWithProposal(data);
                        
                        // Show the first page
                        currentPage = 1;
                        showPage(currentPage);
                        
                        // Reset navigation buttons
                        document.getElementById('prevBtn').style.display = 'none';
                        document.getElementById('nextBtn').style.display = 'block';
                        
                        // Update print button visibility
                        updatePrintButtonVisibility();
                    } else {
                        console.error("Failed to retrieve proposal data:", data.message);
                        showErrorAlert(data.message || 'Failed to load proposal details');
                    }
                })
                .catch(error => {
                    console.error('Error loading proposal details:', error);
                    showErrorAlert('Failed to load proposal details. Please try again.');
                });
        }

        // Function to populate form with proposal data
        function populateFormWithProposal(data) {
            console.log("Starting to populate form with proposal data");
            
            if (!data) {
                console.error('No data provided');
                return;
            }
            
            // Extract proposal from data object - the server response might have the proposal nested
            const proposal = data.proposal || data;
            
            console.log("Proposal data to populate form:", proposal);
            
            if (!proposal) {
                console.error('No proposal data found in the response');
                return;
            }
            
            // Helper function to safely set form field values
            function setFieldValue(id, value) {
                const element = document.getElementById(id);
                if (element) {
                    element.value = value || '';
                    console.log(`Set ${id} to ${value}`);
                } else {
                    console.warn(`Element with id '${id}' not found in the DOM`);
                }
            }
            
            // Helper function to find and check a radio button by value
            function setRadioValue(name, value) {
                const radioButtons = document.getElementsByName(name);
                if (radioButtons && radioButtons.length > 0) {
                    let found = false;
                    for (let i = 0; i < radioButtons.length; i++) {
                        if (radioButtons[i].value === value) {
                            radioButtons[i].checked = true;
                            found = true;
                            console.log(`Set ${name} radio to ${value}`);
                            break;
                        }
                    }
                    if (!found) {
                        console.warn(`No radio button with name='${name}' and value='${value}' found`);
                    }
                } else {
                    console.warn(`No radio buttons with name='${name}' found`);
                }
            }
            
            try {
                // Set basic fields
                setFieldValue('activityTitle', proposal.activity_title);
                
                // Set year and quarter with selectOption function
                selectOption('year', proposal.year);
                setTimeout(() => {
                    selectOption('quarter', proposal.quarter);
                }, 100); // Small delay to ensure year change has applied
                
                setFieldValue('startDate', formatDateForInput(proposal.start_date));
                setFieldValue('endDate', formatDateForInput(proposal.end_date));
                setFieldValue('venue', proposal.venue);
                
                // Set delivery mode
                setRadioValue('deliveryMode', proposal.delivery_mode);
                
                // Set beneficiaries
                setFieldValue('maleBeneficiaries', proposal.male_beneficiaries);
                setFieldValue('femaleBeneficiaries', proposal.female_beneficiaries);
                setFieldValue('totalBeneficiaries', proposal.total_beneficiaries);
                
                // Set other fields as needed
                setFieldValue('partnerOffices', proposal.partner_offices);
                setFieldValue('rationale', proposal.rationale);
                setFieldValue('specificObjectives', proposal.specific_objectives);
                setFieldValue('strategies', proposal.strategies);
                setFieldValue('budgetBreakdown', proposal.budget_breakdown);
                setFieldValue('totalBudget', proposal.total_budget);
                setFieldValue('budgetSource', proposal.budget_source);
                setFieldValue('sustainabilityPlan', proposal.sustainability_plan);
                
                // If activities data exists, populate activities
                if (proposal.activities && Array.isArray(proposal.activities)) {
                    resetActivities(); // Clear existing activities
                    proposal.activities.forEach(activity => {
                        try {
                            addActivity(activity);
                        } catch (e) {
                            console.error('Error adding activity:', e);
                        }
                    });
                } else {
                    console.log("No activities data available or not an array");
                }
                
                // If personnel data exists, update personnel lists
                if (proposal.personnel && Array.isArray(proposal.personnel)) {
                    try {
                        updatePersonnelFromProposal(proposal.personnel);
                    } catch (e) {
                        console.error('Error updating personnel:', e);
                    }
                } else {
                    console.log("No personnel data available or not an array");
                }
                
                console.log("Form population completed successfully");
            } catch (error) {
                console.error("Error populating form:", error);
            }
        }
        
        // Helper function to select an option in a select element
        function selectOption(selectId, value) {
            const select = document.getElementById(selectId);
            if (!select) {
                console.warn(`Select element with id='${selectId}' not found`);
                return;
            }
            
            // If value is undefined or null, do nothing
            if (value === undefined || value === null) {
                console.warn(`Value for ${selectId} is undefined or null`);
                return;
            }
            
            // Convert value to string for comparison
            const valueStr = String(value);
            
            // Check if the select has options
            if (select.options.length === 0) {
                console.warn(`Select element with id='${selectId}' has no options`);
                return;
            }
            
            // Try to find and select the option
            let found = false;
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value === valueStr) {
                    select.selectedIndex = i;
                    found = true;
                    console.log(`Selected option ${valueStr} in ${selectId}`);
                    
                    // Trigger change event
                    const event = new Event('change', { bubbles: true });
                    select.dispatchEvent(event);
                    break;
                }
            }
            
            if (!found) {
                console.warn(`Option with value='${valueStr}' not found in select with id='${selectId}'`);
                // If year or quarter, try to dynamically add option
                if (selectId === 'year' || selectId === 'quarter') {
                    const option = document.createElement('option');
                    option.value = valueStr;
                    option.text = valueStr;
                    select.appendChild(option);
                    select.value = valueStr;
                    
                    // Trigger change event
                    const event = new Event('change', { bubbles: true });
                    select.dispatchEvent(event);
                    
                    console.log(`Added and selected option ${valueStr} in ${selectId}`);
                }
            }
        }
        
        // Function to confirm delete proposal
        function confirmDeleteProposal(id) {
            // Find the proposal in our local array
            const proposal = proposals.find(p => p.id == id);
            
            if (!proposal) {
                showErrorAlert('Proposal not found');
                return;
            }
            
            // Format dates safely
            let startDateDisplay = "Not available";
            let endDateDisplay = "Not available";
            
            try {
                if (proposal.start_date) {
                    startDateDisplay = new Date(proposal.start_date).toLocaleDateString();
                }
                if (proposal.end_date) {
                    endDateDisplay = new Date(proposal.end_date).toLocaleDateString();
                }
            } catch (e) {
                console.error('Error formatting dates:', e);
            }
            
            // Get the title from either activity_title or title property
            const title = proposal.activity_title || 'Untitled';
            const year = proposal.year || 'N/A';
            const quarter = proposal.quarter || 'N/A';
            
            Swal.fire({
                title: 'Delete Proposal',
                html: `
                    <p>Are you sure you want to delete this proposal?</p>
                    <div class="text-start mt-3">
                        <p><strong>Title:</strong> ${title}</p>
                        <p><strong>Year/Quarter:</strong> ${year} - ${quarter}</p>
                        <p><strong>Date:</strong> ${startDateDisplay} to ${endDateDisplay}</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteProposal(id);
                }
            });
        }
        
        // Function to delete a proposal
        function deleteProposal(id) {
            fetch('delete_proposal.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Proposal has been deleted.',
                        icon: 'success',
                        confirmButtonColor: '#6a1b9a',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // If we're deleting the currently loaded proposal, reset the form
                        const currentId = document.getElementById('currentProposalId').value;
                        if (currentId == id) {
                            resetForm();
                        }
                        
                        // Reload proposals in all modals
                        loadProposalsForEditModal();
                        loadProposalsForDeleteModal();
                        loadProposalsForViewModal();
                    });
                } else {
                    showErrorAlert(data.message || 'Failed to delete proposal');
                }
            })
            .catch(error => {
                console.error('Error deleting proposal:', error);
                showErrorAlert('Failed to delete proposal. Please try again.');
            });
        }
        
        // Function to show error alert
        function showErrorAlert(message) {
            Swal.fire({
                title: 'Error',
                text: message,
                icon: 'error',
                confirmButtonColor: '#6a1b9a'
            });
        }
        
        // Function to format date for input elements
        function formatDateForInput(dateString) {
            if (!dateString) return '';
            
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        }

        // Function to set up proposals management
        function setupProposalsManagement() {
            // Get management elements
            const refreshBtn = document.getElementById('refreshBtn');
            const newProposalBtn = document.getElementById('newProposalBtn');
            const searchProposal = document.getElementById('searchProposal');
            const yearFilter = document.getElementById('yearFilter');
            const quarterFilter = document.getElementById('quarterFilter');

            // Add event listeners
            if (refreshBtn) refreshBtn.addEventListener('click', loadProposals);
            if (newProposalBtn) newProposalBtn.addEventListener('click', showNewProposalForm);
            
            if (searchProposal) {
                searchProposal.addEventListener('input', function() {
                    filterProposals();
                });
            }
            
            if (yearFilter) {
                yearFilter.addEventListener('change', function() {
                    filterProposals();
                });
            }
            
            if (quarterFilter) {
                quarterFilter.addEventListener('change', function() {
                    filterProposals();
                });
            }

            // Initial load of proposals
            loadProposals();
        }

        // Function to load proposals
        function loadProposals() {
            console.log("Loading proposals...");
            
            // Show loading indicator
            const tableBody = document.getElementById('proposalsTableBody');
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading proposals...</p>
                        </td>
                    </tr>
                `;
            }
            
            fetch('get_gad_proposals.php')
                .then(response => {
                    // Check if the response is ok (status in the range 200-299)
                    if (!response.ok) {
                        throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
                    }
                    console.log("Proposals response received");
                    return response.json();
                })
                .then(data => {
                    console.log("Proposals data parsed:", data);
                    if (data.success) {
                        proposals = Array.isArray(data.proposals) ? data.proposals : [];
                        console.log(`Loaded ${proposals.length} proposals successfully`);
                        
                        // Populate year filter with unique years
                        const years = [...new Set(proposals.map(p => p.year))].sort((a, b) => b - a);
                        const yearFilter = document.getElementById('yearFilter');
                        if (yearFilter) {
                            yearFilter.innerHTML = '<option value="">All Years</option>';
                            years.forEach(year => {
                                yearFilter.innerHTML += `<option value="${year}">${year}</option>`;
                            });
                        }
                        
                        // Set filtered proposals
                        filteredProposals = [...proposals];
                        
                        // Reset to page 1
                        proposalsCurrentPage = 1;
                        
                        // Update the table
                        updateProposalsTable();
                        
                        // Also update modal tables if needed
                        refreshAllModalTables();
                    } else {
                        console.error("Failed to load proposals:", data.message);
                        showErrorAlert(data.message || 'Failed to load proposals');
                        
                        // Clear the table with error message
                        if (tableBody) {
                            tableBody.innerHTML = `
                                <tr>
                                    <td colspan="7" class="text-center text-danger">
                                        <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                                        <p>Failed to load proposals. Please try again.</p>
                                        <button class="btn btn-outline-primary btn-sm mt-2" onclick="loadProposals()">
                                            <i class="fas fa-sync-alt"></i> Retry
                                        </button>
                                    </td>
                                </tr>
                            `;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading proposals:', error);
                    showErrorAlert('Failed to load proposals. Please try again.');
                    
                    // Clear the table with error message
                    if (tableBody) {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center text-danger">
                                    <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                                    <p>Error: ${error.message || 'Failed to load proposals'}</p>
                                    <button class="btn btn-outline-primary btn-sm mt-2" onclick="loadProposals()">
                                        <i class="fas fa-sync-alt"></i> Retry
                                    </button>
                                </td>
                            </tr>
                        `;
                    }
                });
        }

        // Function to refresh all modal tables
        function refreshAllModalTables() {
            console.log("Refreshing all modal tables");
            
            // Call each load function with a slight delay to prevent race conditions
            setTimeout(() => {
                if (typeof loadProposalsForEditModal === 'function') {
                    try {
                        loadProposalsForEditModal();
                    } catch (e) {
                        console.error("Error loading edit modal proposals:", e);
                    }
                }
            }, 100);
            
            setTimeout(() => {
                if (typeof loadProposalsForDeleteModal === 'function') {
                    try {
                        loadProposalsForDeleteModal();
                    } catch (e) {
                        console.error("Error loading delete modal proposals:", e);
                    }
                }
            }, 200);
            
            setTimeout(() => {
                if (typeof loadProposalsForViewModal === 'function') {
                    try {
                        loadProposalsForViewModal();
                    } catch (e) {
                        console.error("Error loading view modal proposals:", e);
                    }
                }
            }, 300);
        }

        // Enhanced function for loading proposals for delete modal
        function loadProposalsForDeleteModal() {
            console.log("Loading proposals for delete modal...");
            
            // Show loading indicator
            const tableBody = document.getElementById('deleteModalBody');
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading proposals...</p>
                        </td>
                    </tr>
                `;
            }
            
            fetch('get_gad_proposals.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("Delete modal proposals data:", data);
                    if (data.success) {
                        deleteModalProposals = Array.isArray(data.proposals) ? data.proposals : [];
                        console.log(`Loaded ${deleteModalProposals.length} proposals for delete modal`);
                        
                        // Populate year filter if it exists
                        const yearFilter = document.getElementById('yearDeleteFilter');
                        if (yearFilter) {
                            const years = [...new Set(deleteModalProposals.map(p => p.year))].sort((a, b) => b - a);
                            yearFilter.innerHTML = '<option value="">All Years</option>';
                            years.forEach(year => {
                                yearFilter.innerHTML += `<option value="${year}">${year}</option>`;
                            });
                        }
                        
                        // Reset to page 1
                        deleteModalPage = 1;
                        
                        // Update the table
                        updateDeleteModalTable();
                    } else {
                        console.error("Failed to load proposals for delete modal:", data.message);
                        if (tableBody) {
                            tableBody.innerHTML = `
                                <tr>
                                    <td colspan="6" class="text-center text-danger">
                                        <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                                        <p>Failed to load proposals. Please try again.</p>
                                        <button class="btn btn-outline-primary btn-sm mt-2" onclick="loadProposalsForDeleteModal()">
                                            <i class="fas fa-sync-alt"></i> Retry
                                        </button>
                                    </td>
                                </tr>
                            `;
                        }
                        
                        // Disable delete button
                        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
                        if (confirmDeleteBtn) {
                            confirmDeleteBtn.disabled = true;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading proposals for delete modal:', error);
                    if (tableBody) {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center text-danger">
                                    <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                                    <p>Error: ${error.message || 'Failed to load proposals'}</p>
                                    <button class="btn btn-outline-primary btn-sm mt-2" onclick="loadProposalsForDeleteModal()">
                                        <i class="fas fa-sync-alt"></i> Retry
                                    </button>
                                </td>
                            </tr>
                        `;
                    }
                    
                    // Disable delete button
                    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
                    if (confirmDeleteBtn) {
                        confirmDeleteBtn.disabled = true;
                    }
                });
        }

        // Function to filter proposals
        function filterProposals() {
            const searchTerm = document.getElementById('searchProposal').value.toLowerCase();
            const yearValue = document.getElementById('yearFilter').value;
            const quarterValue = document.getElementById('quarterFilter').value;
            
            filteredProposals = proposals.filter(proposal => {
                const matchSearch = proposal.activity_title.toLowerCase().includes(searchTerm);
                const matchYear = !yearValue || proposal.year.toString() === yearValue;
                const matchQuarter = !quarterValue || proposal.quarter === quarterValue;
                
                return matchSearch && matchYear && matchQuarter;
            });
            
            // Reset to page 1 on filter
            proposalsCurrentPage = 1;
            
            // Update the table
            updateProposalsTable();
        }

        // Function to update the proposals table
        function updateProposalsTable() {
            const tableBody = document.getElementById('proposalsTableBody');
            if (!tableBody) return;
            
            // Calculate pagination
            const totalProposals = filteredProposals.length;
            const totalPages = Math.ceil(totalProposals / proposalsPerPage);
            const startIndex = (proposalsCurrentPage - 1) * proposalsPerPage;
            const endIndex = Math.min(startIndex + proposalsPerPage, totalProposals);
            
            // Get page data
            const pageData = filteredProposals.slice(startIndex, endIndex);
            
            // Clear table
            tableBody.innerHTML = '';
            
            // Show message if no data
            if (pageData.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center">No proposals found</td>
                    </tr>
                `;
            } else {
                // Add rows for each proposal
                pageData.forEach(proposal => {
                    const row = document.createElement('tr');
                    
                    // Format dates
                    const startDate = new Date(proposal.start_date).toLocaleDateString();
                    const endDate = new Date(proposal.end_date).toLocaleDateString();
                    
                    row.innerHTML = `
                        <td>${proposal.id}</td>
                        <td>${proposal.activity_title}</td>
                        <td>${proposal.year}</td>
                        <td>${proposal.quarter}</td>
                        <td>${startDate}</td>
                        <td>${endDate}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center">
                                <button class="btn-icon view-btn mx-1" title="View Proposal" onclick="viewProposal(${proposal.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon edit-btn mx-1" title="Edit Proposal" onclick="editProposal(${proposal.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon delete-btn mx-1" title="Delete Proposal" onclick="confirmDeleteProposal(${proposal.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn-icon print-btn mx-1" title="Print Proposal" onclick="printProposal(${proposal.id})">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    
                    tableBody.appendChild(row);
                });
            }
            
            // Update pagination
            updateProposalsPagination(totalPages);
        }

        // Function to update pagination controls
        function updateProposalsPagination(totalPages) {
            const pagination = document.getElementById('proposalsPagination');
            if (!pagination) return;
            
            // Clear pagination
            pagination.innerHTML = '';
            
            // Don't show pagination if only one page
            if (totalPages <= 1) return;
            
            // Add previous button
            const prevLi = document.createElement('li');
            prevLi.className = `page-item ${proposalsCurrentPage === 1 ? 'disabled' : ''}`;
            prevLi.innerHTML = `
                <a class="page-link" href="#" aria-label="Previous" onclick="event.preventDefault(); changePage(${proposalsCurrentPage - 1})">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            `;
            pagination.appendChild(prevLi);
            
            // Add page numbers
            for (let i = 1; i <= totalPages; i++) {
                const li = document.createElement('li');
                li.className = `page-item ${proposalsCurrentPage === i ? 'active' : ''}`;
                li.innerHTML = `
                    <a class="page-link" href="#" onclick="event.preventDefault(); changePage(${i})">${i}</a>
                `;
                pagination.appendChild(li);
            }
            
            // Add next button
            const nextLi = document.createElement('li');
            nextLi.className = `page-item ${proposalsCurrentPage === totalPages ? 'disabled' : ''}`;
            nextLi.innerHTML = `
                <a class="page-link" href="#" aria-label="Next" onclick="event.preventDefault(); changePage(${proposalsCurrentPage + 1})">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            `;
            pagination.appendChild(nextLi);
        }

        // Function to change pagination page
        function changePage(page) {
            const totalPages = Math.ceil(filteredProposals.length / proposalsPerPage);
            
            if (page >= 1 && page <= totalPages) {
                proposalsCurrentPage = page;
                updateProposalsTable();
            }
        }

        // Function to show new proposal form
        function showNewProposalForm() {
            // Hide the proposals management card
            document.getElementById('proposalsManagement').style.display = 'none';
            
            // Reset form
            document.getElementById('gadProposalForm').reset();
            document.getElementById('currentProposalId').value = '';
            
            // Show the first page
            currentPage = 1;
            showPage(currentPage);
            
            // Reset navigation buttons
            document.getElementById('prevBtn').style.display = 'none';
            document.getElementById('nextBtn').style.display = 'block';
            // Remove submitBtn reference since we're using the save button
            
            // Clear personnel lists
            clearPersonnelLists();
            
            // Reset activities
            resetActivities();
            
            // Update button visibilities
            updatePrintButtonVisibility();
        }

        // Function to view a proposal
        function viewProposal(id) {
            window.location.href = `print_proposal.php?id=${id}`;
        }

        // Function to edit a proposal
        function editProposal(id) {
            console.log(`Starting to edit proposal with ID: ${id}`);
            
            // Set the editing proposal ID
            editingProposalId = id;
            
            // Hide the proposals management card
            document.getElementById('proposalsManagement').style.display = 'none';
            
            // Fetch proposal details
            console.log(`Fetching proposal data from get_gad_proposal.php?id=${id}`);
            fetch(`get_gad_proposal.php?id=${id}`)
                .then(response => {
                    console.log("Received response from server");
                    return response.json();
                })
                .then(data => {
                    console.log("Parsed response data:", data);
                    if (data.success) {
                        console.log("Proposal data retrieved successfully");
                        
                        // Reset form
                        document.getElementById('gadProposalForm').reset();
                        
                        // Set ID in the hidden field
                        document.getElementById('currentProposalId').value = id;
                        console.log(`Set currentProposalId to ${id}`);
                        
                        // Populate form fields
                        populateFormWithProposal(data);
                        
                        // Show the first page
                        currentPage = 1;
                        showPage(currentPage);
                        
                        // Reset navigation buttons
                        document.getElementById('prevBtn').style.display = 'none';
                        document.getElementById('nextBtn').style.display = 'block';
                        
                        // Update print button visibility
                        updatePrintButtonVisibility();
                    } else {
                        console.error("Failed to retrieve proposal data:", data.message);
                        showErrorAlert(data.message || 'Failed to load proposal details');
                    }
                })
                .catch(error => {
                    console.error('Error loading proposal details:', error);
                    showErrorAlert('Failed to load proposal details. Please try again.');
                });
        }

        // Function to populate form with proposal data
        function populateFormWithProposal(data) {
            console.log("Starting to populate form with proposal data");
            
            if (!data) {
                console.error('No data provided');
                return;
            }
            
            // Extract proposal from data object - the server response might have the proposal nested
            const proposal = data.proposal || data;
            
            console.log("Proposal data to populate form:", proposal);
            
            if (!proposal) {
                console.error('No proposal data found in the response');
                return;
            }
            
            // Helper function to safely set form field values
            function setFieldValue(id, value) {
                const element = document.getElementById(id);
                if (element) {
                    element.value = value || '';
                    console.log(`Set ${id} to ${value}`);
                } else {
                    console.warn(`Element with id '${id}' not found in the DOM`);
                }
            }
            
            // Helper function to find and check a radio button by value
            function setRadioValue(name, value) {
                const radioButtons = document.getElementsByName(name);
                if (radioButtons && radioButtons.length > 0) {
                    let found = false;
                    for (let i = 0; i < radioButtons.length; i++) {
                        if (radioButtons[i].value === value) {
                            radioButtons[i].checked = true;
                            found = true;
                            console.log(`Set ${name} radio to ${value}`);
                            break;
                        }
                    }
                    if (!found) {
                        console.warn(`No radio button with name='${name}' and value='${value}' found`);
                    }
                } else {
                    console.warn(`No radio buttons with name='${name}' found`);
                }
            }
            
            try {
                // Set basic fields
                setFieldValue('activityTitle', proposal.activity_title);
                
                // Set year and quarter with selectOption function
                selectOption('year', proposal.year);
                setTimeout(() => {
                    selectOption('quarter', proposal.quarter);
                }, 100); // Small delay to ensure year change has applied
                
                setFieldValue('startDate', formatDateForInput(proposal.start_date));
                setFieldValue('endDate', formatDateForInput(proposal.end_date));
                setFieldValue('venue', proposal.venue);
                
                // Set delivery mode
                setRadioValue('deliveryMode', proposal.delivery_mode);
                
                // Set beneficiaries
                setFieldValue('maleBeneficiaries', proposal.male_beneficiaries);
                setFieldValue('femaleBeneficiaries', proposal.female_beneficiaries);
                setFieldValue('totalBeneficiaries', proposal.total_beneficiaries);
                
                // Set other fields as needed
                setFieldValue('partnerOffices', proposal.partner_offices);
                setFieldValue('rationale', proposal.rationale);
                setFieldValue('specificObjectives', proposal.specific_objectives);
                setFieldValue('strategies', proposal.strategies);
                setFieldValue('budgetBreakdown', proposal.budget_breakdown);
                setFieldValue('totalBudget', proposal.total_budget);
                setFieldValue('budgetSource', proposal.budget_source);
                setFieldValue('sustainabilityPlan', proposal.sustainability_plan);
                
                // If activities data exists, populate activities
                if (proposal.activities && Array.isArray(proposal.activities)) {
                    resetActivities(); // Clear existing activities
                    proposal.activities.forEach(activity => {
                        try {
                            addActivity(activity);
                        } catch (e) {
                            console.error('Error adding activity:', e);
                        }
                    });
                } else {
                    console.log("No activities data available or not an array");
                }
                
                // If personnel data exists, update personnel lists
                if (proposal.personnel && Array.isArray(proposal.personnel)) {
                    try {
                        updatePersonnelFromProposal(proposal.personnel);
                    } catch (e) {
                        console.error('Error updating personnel:', e);
                    }
                } else {
                    console.log("No personnel data available or not an array");
                }
                
                console.log("Form population completed successfully");
            } catch (error) {
                console.error("Error populating form:", error);
            }
        }
        
        // Helper function to select an option in a select element
        function selectOption(selectId, value) {
            const select = document.getElementById(selectId);
            if (!select) {
                console.warn(`Select element with id='${selectId}' not found`);
                return;
            }
            
            // If value is undefined or null, do nothing
            if (value === undefined || value === null) {
                console.warn(`Value for ${selectId} is undefined or null`);
                return;
            }
            
            // Convert value to string for comparison
            const valueStr = String(value);
            
            // Check if the select has options
            if (select.options.length === 0) {
                console.warn(`Select element with id='${selectId}' has no options`);
                return;
            }
            
            // Try to find and select the option
            let found = false;
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value === valueStr) {
                    select.selectedIndex = i;
                    found = true;
                    console.log(`Selected option ${valueStr} in ${selectId}`);
                    
                    // Trigger change event
                    const event = new Event('change', { bubbles: true });
                    select.dispatchEvent(event);
                    break;
                }
            }
            
            if (!found) {
                console.warn(`Option with value='${valueStr}' not found in select with id='${selectId}'`);
                // If year or quarter, try to dynamically add option
                if (selectId === 'year' || selectId === 'quarter') {
                    const option = document.createElement('option');
                    option.value = valueStr;
                    option.text = valueStr;
                    select.appendChild(option);
                    select.value = valueStr;
                    
                    // Trigger change event
                    const event = new Event('change', { bubbles: true });
                    select.dispatchEvent(event);
                    
                    console.log(`Added and selected option ${valueStr} in ${selectId}`);
                }
            }
        }
        
        // Function to confirm delete proposal
        function confirmDeleteProposal(id) {
            // Find the proposal in our local array
            const proposal = proposals.find(p => p.id == id);
            
            if (!proposal) {
                showErrorAlert('Proposal not found');
                return;
            }
            
            // Format dates safely
            let startDateDisplay = "Not available";
            let endDateDisplay = "Not available";
            
            try {
                if (proposal.start_date) {
                    startDateDisplay = new Date(proposal.start_date).toLocaleDateString();
                }
                if (proposal.end_date) {
                    endDateDisplay = new Date(proposal.end_date).toLocaleDateString();
                }
            } catch (e) {
                console.error('Error formatting dates:', e);
            }
            
            // Get the title from either activity_title or title property
            const title = proposal.activity_title || 'Untitled';
            const year = proposal.year || 'N/A';
            const quarter = proposal.quarter || 'N/A';
            
            Swal.fire({
                title: 'Delete Proposal',
                html: `
                    <p>Are you sure you want to delete this proposal?</p>
                    <div class="text-start mt-3">
                        <p><strong>Title:</strong> ${title}</p>
                        <p><strong>Year/Quarter:</strong> ${year} - ${quarter}</p>
                        <p><strong>Date:</strong> ${startDateDisplay} to ${endDateDisplay}</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteProposal(id);
                }
            });
        }
        
        // Function to delete a proposal
        function deleteProposal(id) {
            fetch('delete_proposal.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Proposal has been deleted.',
                        icon: 'success',
                        confirmButtonColor: '#6a1b9a',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // If we're deleting the currently loaded proposal, reset the form
                        const currentId = document.getElementById('currentProposalId').value;
                        if (currentId == id) {
                            resetForm();
                        }
                        
                        // Reload proposals in all modals
                        loadProposalsForEditModal();
                        loadProposalsForDeleteModal();
                        loadProposalsForViewModal();
                    });
                } else {
                    showErrorAlert(data.message || 'Failed to delete proposal');
                }
            })
            .catch(error => {
                console.error('Error deleting proposal:', error);
                showErrorAlert('Failed to delete proposal. Please try again.');
            });
        }
        
        // Function to show error alert
        function showErrorAlert(message) {
            Swal.fire({
                title: 'Error',
                text: message,
                icon: 'error',
                confirmButtonColor: '#6a1b9a'
            });
        }
        
        // Function to format date for input elements
        function formatDateForInput(dateString) {
            if (!dateString) return '';
            
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        }

        // Function to clear personnel lists
        function clearPersonnelLists() {
            // Reset selected personnel
            selectedPersonnel = {
                projectLeaders: [],
                assistantProjectLeaders: [],
                projectStaff: []
            };
            
            // Clear list elements
            document.getElementById('projectLeadersList').innerHTML = '';
            document.getElementById('assistantProjectLeadersList').innerHTML = '';
            document.getElementById('projectStaffList').innerHTML = '';
        }

        // Function to reset activities
        function resetActivities() {
            // Clear activities container
            const activitiesContainer = document.getElementById('activitiesContainer');
            if (activitiesContainer) {
                activitiesContainer.innerHTML = '';
            }
        }

        // Show the CRUD management view
        function showProposalsManagement() {
            // Hide form pages
            document.querySelectorAll('.form-page').forEach(page => {
                page.style.display = 'none';
            });
            
            // Show proposals management card
            document.getElementById('proposalsManagement').style.display = 'block';
            
            // Load proposals
            loadProposals();
        }

        // Add a link to the sidebar to access the CRUD management
      

        // Call this function when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Add management link to the sidebar
            addManagementLink();
            
            // Check if URL has a management parameter
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('manage') === 'true') {
                showProposalsManagement();
            }
        });

        function showPage(pageNumber) {
            document.querySelectorAll('.form-page').forEach(page => {
                page.style.display = 'none';
            });
            document.getElementById(`page${pageNumber}`).style.display = 'block';
        }

        function validateCurrentPage() {
            const currentPageElement = document.getElementById(`page${currentPage}`);
            const requiredFields = currentPageElement.querySelectorAll('[required]:not([type="hidden"])');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value && field.offsetParent !== null) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            return isValid;
        }

        function navigateForm(direction) {
            if (direction === 1 && !validateCurrentPage()) {
                Swal.fire({
                    title: 'Required Fields',
                    text: 'Please fill in all required fields before proceeding.',
                    icon: 'warning',
                    confirmButtonColor: '#6a1b9a'
                });
                return;
            }

            const newPage = currentPage + direction;
            if (newPage >= 1 && newPage <= totalPages) {
                currentPage = newPage;
                showPage(currentPage);
                
                // Show/hide navigation buttons
                document.getElementById('prevBtn').style.display = currentPage === 1 ? 'none' : 'block';
                document.getElementById('nextBtn').style.display = currentPage === totalPages ? 'none' : 'block';
                // Remove submitBtn toggle since we're using the addBtn now
            }
        }

        // Calculate total beneficiaries
        function updateTotalBeneficiaries() {
            const male = parseInt(document.getElementById('maleBeneficiaries').value) || 0;
            const female = parseInt(document.getElementById('femaleBeneficiaries').value) || 0;
            document.getElementById('totalBeneficiaries').value = male + female;
        }

        // Form submission handler
        function handleFormSubmit(e) {
            e.preventDefault();
            
            try {
                const form = document.getElementById('gadProposalForm');
                const formData = new FormData(form);
                
                // Add PPAS ID if available
                const ppasId = document.getElementById('ppasId')?.value;
                if (ppasId) {
                    formData.append('ppasId', ppasId);
                }
                
                // Show loading indicator
                Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait while we save your GAD proposal',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Collect activities data
                const activitiesContainer = document.getElementById('activitiesContainer');
                const activityItems = activitiesContainer.querySelectorAll('.activity-item');
                
                activityItems.forEach((item, index) => {
                    const titleInput = item.querySelector('input[type="text"]');
                    const detailsTextarea = item.querySelector('textarea');
                    
                    if (titleInput && titleInput.value.trim() !== '') {
                        formData.append(`activity_title_${index + 1}`, titleInput.value);
                        if (detailsTextarea) {
                            formData.append(`activity_details_${index + 1}`, detailsTextarea.value);
                        }
                    }
                });
                
                // Collect monitoring data
                const monitoringTable = document.querySelector('#page8 table tbody');
                if (monitoringTable) {
                    const monitoringRows = monitoringTable.querySelectorAll('tr');
                    monitoringRows.forEach((row, index) => {
                        const textareas = row.querySelectorAll('textarea');
                        if (textareas.length === 8) {
                            formData.append(`monitoring_objectives_${index + 1}`, textareas[0].value);
                            formData.append(`monitoring_indicators_${index + 1}`, textareas[1].value);
                            formData.append(`monitoring_baseline_${index + 1}`, textareas[2].value);
                            formData.append(`monitoring_target_${index + 1}`, textareas[3].value);
                            formData.append(`monitoring_source_${index + 1}`, textareas[4].value);
                            formData.append(`monitoring_method_${index + 1}`, textareas[5].value);
                            formData.append(`monitoring_frequency_${index + 1}`, textareas[6].value);
                            formData.append(`monitoring_responsible_${index + 1}`, textareas[7].value);
                        }
                    });
                }
                
                // Collect workplan data
                const workplanTable = document.querySelector('#workPlanTable tbody');
                if (workplanTable) {
                    const workplanRows = workplanTable.querySelectorAll('tr');
                    workplanRows.forEach((row, index) => {
                        const activityInput = row.querySelector('td:first-child input');
                        if (activityInput && activityInput.value.trim() !== '') {
                            formData.append(`workplan_activity_${index + 1}`, activityInput.value);
                            
                            // Collect timeline data (which checkboxes are checked)
                            const checkboxes = row.querySelectorAll('input[type="checkbox"]');
                            const timelineData = Array.from(checkboxes)
                                .map((checkbox, i) => checkbox.checked ? i + 1 : null)
                                .filter(val => val !== null)
                                .join(',');
                            
                            formData.append(`workplan_timeline_${index + 1}`, timelineData);
                        }
                    });
                }
                
                // Handle hidden personnel fields
                for (const role in selectedPersonnel) {
                    const personnelIds = selectedPersonnel[role].map(p => p.id).join(',');
                    formData.append(`${role}Hidden`, personnelIds);
                    
                    // Also include the display names for clarity in the form data
                    const personnelNames = selectedPersonnel[role].map(p => p.name).join(', ');
                    formData.append(role, personnelNames);
                }
                
                // Ensure project leaders data is collected
                const projectLeadersIds = document.getElementById('projectLeadersHidden')?.value;
                const assistantProjectLeadersIds = document.getElementById('assistantProjectLeadersHidden')?.value;
                const projectStaffIds = document.getElementById('projectStaffHidden')?.value;
                
                if (projectLeadersIds) formData.append('projectLeadersHidden', projectLeadersIds);
                if (assistantProjectLeadersIds) formData.append('assistantProjectLeadersHidden', assistantProjectLeadersIds);
                if (projectStaffIds) formData.append('projectStaffHidden', projectStaffIds);
                
                // Add debug info
                console.log("Form data being submitted:");
                for (const pair of formData.entries()) {
                    console.log(pair[0], pair[1]);
                }
                
                fetch('save_gad_proposal.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        if (response.status === 500) {
                            console.error('Server error 500. Database or server configuration issue.');
                            throw new Error('Database server error. Please contact administrator or check database configuration.');
                        }
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            console.error('Server returned non-JSON response:', text);
                            throw new Error('Server returned invalid response format');
                        });
                    }
                    
                    return response.json();
                })
                .then(result => {
                    handleFormSubmission(result);
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'An unknown error occurred',
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                });
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'An unknown error occurred',
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
            }
        }

        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        // Update date and time every second
        setInterval(updateDateTime, 1000);
        updateDateTime(); // Initial call

        function updateThemeIcon(theme) {
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
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
                    }, 10);
                }
            });
        }

        // Activities Management
        function addActivity() {
            const container = document.getElementById('activitiesContainer');
            const activityNum = container.children.length + 1;
            
            const activityDiv = document.createElement('div');
            activityDiv.className = 'activity-item mb-3';
            activityDiv.innerHTML = `
                <div class="input-group mb-2">
                    <span class="input-group-text">Activity ${activityNum}</span>
                    <input type="text" class="form-control" placeholder="Enter activity title" name="activity_title_${activityNum}">
                    <button type="button" class="btn btn-outline-danger" onclick="removeActivity(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <textarea class="form-control" rows="3" placeholder="Enter activity details" name="activity_details_${activityNum}"></textarea>
            `;
            container.appendChild(activityDiv);
        }

        function removeActivity(button) {
            const activityItem = button.closest('.activity-item');
            if (activityItem) {
                activityItem.remove();
                renumberActivities();
            }
        }

        function renumberActivities() {
            const activities = document.querySelectorAll('.activity-item');
            activities.forEach((activity, index) => {
                const span = activity.querySelector('.input-group-text');
                if (span) {
                    span.textContent = `Activity ${index + 1}`;
                }
            });
        }

        // Function to generate dates for work plan
        function generateWorkPlanDates() {
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            
            if (!startDateInput || !endDateInput || !startDateInput.value || !endDateInput.value) {
                return;
            }
            
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            
            // Clear existing table header
            const thead = document.querySelector('#workPlanTable thead tr');
            if (!thead) return;
            
            // Keep only the first column (Activities)
            while (thead.children.length > 1) {
                thead.removeChild(thead.lastChild);
            }
            
            // Generate date columns (up to 10 days maximum to keep it reasonable)
            const maxDays = 10;
            const dayCount = Math.min(Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1, maxDays);
            
            const dates = [];
            for (let i = 0; i < dayCount; i++) {
                const date = new Date(startDate);
                date.setDate(date.getDate() + i);
                dates.push(date);
                
                const th = document.createElement('th');
                th.textContent = formatDate(date);
                thead.appendChild(th);
            }
            
            // Add Action header
            const actionTh = document.createElement('th');
            actionTh.textContent = 'Action';
            thead.appendChild(actionTh);
            
            // Update all existing rows
            const tbody = document.querySelector('#workPlanTable tbody');
            if (tbody) {
                Array.from(tbody.rows).forEach(row => {
                    // Remove all cells except the first one
                    while (row.cells.length > 1) {
                        row.deleteCell(1);
                    }
                    
                    // Add new checkbox cells
                    for (let i = 0; i < dayCount; i++) {
                        const cell = row.insertCell();
                        cell.innerHTML = '<input type="checkbox" class="form-check-input">';
                    }
                    
                    // Add the delete button cell if it doesn't exist
                    const lastCell = row.insertCell();
                    lastCell.className = 'text-center';
                    lastCell.innerHTML = `
                        <button type="button" class="btn btn-sm btn-danger remove-workplan-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    
                    // Add event listener for remove button
                    const removeButton = lastCell.querySelector('.remove-workplan-row');
                    if (removeButton) {
                        removeButton.addEventListener('click', function() {
                            row.remove();
                            renumberWorkplanRows();
                        });
                    }
                });
            }
        }

        // Format date as "DD (Month)"
        function formatDate(date) {
            const day = date.getDate();
            const month = date.toLocaleString('default', { month: 'short' });
            return `${day} (${month})`;
        }

        // Add work plan row
        function addWorkPlanRow() {
            const tbody = document.querySelector('#workPlanTable tbody');
            if (!tbody) return;
            
            const columnCount = document.querySelector('#workPlanTable thead tr').children.length;
            const rowCount = tbody.querySelectorAll('tr').length;
            const newIndex = rowCount + 1;
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" class="form-control form-control-sm" name="workplan_activity_${newIndex}" placeholder="Add activity"></td>
                ${Array(columnCount - 2).fill('<td><input type="checkbox" class="form-check-input workplan-checkbox"></td>').join('')}
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-workplan-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            // Add event listener for the checkboxes to update the timeline data
            const checkboxes = row.querySelectorAll('.workplan-checkbox');
            checkboxes.forEach((checkbox, i) => {
                checkbox.addEventListener('change', function() {
                    updateWorkplanTimelineData(row, newIndex);
                });
            });
            
            // Add event listener for remove button
            const removeButton = row.querySelector('.remove-workplan-row');
            if (removeButton) {
                removeButton.addEventListener('click', function() {
                    row.remove();
                    renumberWorkplanRows();
                });
            }
            
            tbody.appendChild(row);
        }

        // Function to update the workplan timeline data based on checked checkboxes
        function updateWorkplanTimelineData(row, rowIndex) {
            const checkboxes = row.querySelectorAll('.workplan-checkbox');
            const timelineData = Array.from(checkboxes)
                .map((checkbox, i) => checkbox.checked ? i + 1 : null)
                .filter(val => val !== null)
                .join(',');
            
            // Create or update a hidden input for this timeline data
            let hiddenInput = row.querySelector(`input[name="workplan_timeline_${rowIndex}"]`);
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `workplan_timeline_${rowIndex}`;
                row.appendChild(hiddenInput);
            }
            hiddenInput.value = timelineData;
        }

        // Function to renumber workplan rows after deletion
        function renumberWorkplanRows() {
            const tbody = document.querySelector('#workPlanTable tbody');
            if (tbody) {
                const rows = tbody.querySelectorAll('tr');
                rows.forEach((row, index) => {
                    const newIndex = index + 1;
                    const activityInput = row.querySelector('td:first-child input');
                    if (activityInput) {
                        activityInput.setAttribute('name', `workplan_activity_${newIndex}`);
                    }
                    
                    // Update hidden timeline input if it exists
                    const hiddenInput = row.querySelector('input[type="hidden"]');
                    if (hiddenInput) {
                        hiddenInput.setAttribute('name', `workplan_timeline_${newIndex}`);
                    }
                });
            }
        }

        // Update work plan when dates change
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const addWorkPlanRowBtn = document.getElementById('addWorkPlanRowBtn');
            
            if (startDateInput && endDateInput) {
                startDateInput.addEventListener('change', generateWorkPlanDates);
                endDateInput.addEventListener('change', generateWorkPlanDates);
            }
            
            if (addWorkPlanRowBtn) {
                addWorkPlanRowBtn.addEventListener('click', addWorkPlanRow);
            }
        });

        // Add row for Monitoring and Evaluation table
        function addMonitoringRow() {
            const tbody = document.querySelector('#page8 table tbody');
            if (tbody) {
                const rowCount = tbody.querySelectorAll('tr').length;
                const newIndex = rowCount + 1;
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><textarea class="form-control" name="monitoring_objectives_${newIndex}" rows="2"></textarea></td>
                    <td><textarea class="form-control" name="monitoring_indicators_${newIndex}" rows="2"></textarea></td>
                    <td><textarea class="form-control" name="monitoring_baseline_${newIndex}" rows="2"></textarea></td>
                    <td><textarea class="form-control" name="monitoring_target_${newIndex}" rows="2"></textarea></td>
                    <td><textarea class="form-control" name="monitoring_source_${newIndex}" rows="2"></textarea></td>
                    <td><textarea class="form-control" name="monitoring_method_${newIndex}" rows="2"></textarea></td>
                    <td><textarea class="form-control" name="monitoring_frequency_${newIndex}" rows="2"></textarea></td>
                    <td><textarea class="form-control" name="monitoring_responsible_${newIndex}" rows="2"></textarea></td>
                    <td class="text-center" style="vertical-align: middle;">
                        <button type="button" class="btn btn-sm btn-danger remove-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                
                // Add event listener for remove button
                const removeButton = row.querySelector('.remove-row');
                if (removeButton) {
                    removeButton.addEventListener('click', function() {
                        row.remove();
                        // Renumber remaining rows
                        renumberMonitoringRows();
                    });
                }
                
                tbody.appendChild(row);
            }
        }

        // Function to renumber monitoring rows after deletion
        function renumberMonitoringRows() {
            const tbody = document.querySelector('#page8 table tbody');
            if (tbody) {
                const rows = tbody.querySelectorAll('tr');
                rows.forEach((row, index) => {
                    const newIndex = index + 1;
                    const textareas = row.querySelectorAll('textarea');
                    textareas[0].setAttribute('name', `monitoring_objectives_${newIndex}`);
                    textareas[1].setAttribute('name', `monitoring_indicators_${newIndex}`);
                    textareas[2].setAttribute('name', `monitoring_baseline_${newIndex}`);
                    textareas[3].setAttribute('name', `monitoring_target_${newIndex}`);
                    textareas[4].setAttribute('name', `monitoring_source_${newIndex}`);
                    textareas[5].setAttribute('name', `monitoring_method_${newIndex}`);
                    textareas[6].setAttribute('name', `monitoring_frequency_${newIndex}`);
                    textareas[7].setAttribute('name', `monitoring_responsible_${newIndex}`);
                });
            }
        }

        // Personnel function to add a person to the list
        function addPersonnelToList(role, person) {
            // Check if person is already in the list
            if (selectedPersonnel[role].some(p => p.id === person.id)) {
                return;
            }

            // Add to selected personnel array
            selectedPersonnel[role].push(person);

            // Create a badge for the selected person
            const badge = document.createElement('span');
            badge.className = 'badge bg-secondary me-2 mb-2 personnel-badge';
            badge.dataset.id = person.id;
            
            // Always create read-only badges without remove button
            badge.innerHTML = `${person.name}`;
            badge.title = "Personnel cannot be removed";

            // Add badge to the list container
            const listContainer = document.getElementById(`${role}List`);
            if (listContainer) {
                listContainer.appendChild(badge);
            }

            // Update the hidden field with the selected personnel
            updateHiddenField(role);
        }

        // Function to update the hidden field with selected personnel
        function updateHiddenField(role) {
            // Create a hidden input if it doesn't exist
            let hiddenField = document.getElementById(`${role}Hidden`);
            if (!hiddenField) {
                hiddenField = document.createElement('input');
                hiddenField.type = 'hidden';
                hiddenField.id = `${role}Hidden`;
                hiddenField.name = `${role}Hidden`;
                const inputField = document.getElementById(role);
                if (inputField && inputField.parentNode) {
                    inputField.parentNode.appendChild(hiddenField);
                }
            }

            // Update the hidden field value with the selected personnel IDs
            const personnelIds = selectedPersonnel[role].map(p => p.id).join(',');
            hiddenField.value = personnelIds;

            // Update the original input with the personnel names for display purposes
            const personnelNames = selectedPersonnel[role].map(p => p.name).join(', ');
            const inputField = document.getElementById(role);
            if (inputField) {
                inputField.value = personnelNames;
            }
        }

        // Load all personnel on page load
        function loadAllPersonnel() {
            // If jQuery is available
            if (typeof $ !== 'undefined') {
                $.ajax({
                    url: '../personnel_list/get_all_personnel.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log('Loaded personnel:', data);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading personnel:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);
                    }
                });
            } else {
                console.warn('jQuery not available for personnel loading');
                // Fallback to fetch API if needed
            }
        }

        // Function to load available years
        function loadAvailableYears() {
            fetch('get_available_data.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        const yearSelect = document.getElementById('year');
                        if (yearSelect) {
                            yearSelect.innerHTML = '<option value="">Select Year</option>';
                            
                            if (data.success) {
                                if (data.years && data.years.length > 0) {
                                    data.years.forEach(year => {
                                        const option = document.createElement('option');
                                        option.value = year;
                                        option.textContent = year;
                                        yearSelect.appendChild(option);
                                    });
                                }
                                
                                if (data.message) {
                                    Swal.fire({
                                        title: 'Information',
                                        text: data.message,
                                        icon: 'info',
                                        confirmButtonColor: '#6a1b9a'
                                    });
                                }
                            } else {
                                throw new Error(data.message || 'Failed to load years');
                            }
                        }
                    } catch (e) {
                        console.error('Server response:', text);
                        throw new Error('Invalid response from server');
                    }
                })
                .catch(error => {
                    console.error('Error loading years:', error);
                    console.error('Error details:', error.stack);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to load available years. Please try refreshing the page.',
                        icon: 'error',
                        confirmButtonColor: '#6a1b9a'
                    });
                });
        }

        // Load quarters for selected year
        function loadQuarters(year) {
            const quarterSelect = document.getElementById('quarter');
            if (!quarterSelect) return;
            
            quarterSelect.disabled = true;
            quarterSelect.innerHTML = '<option value="">Select Quarter</option>';

            if (year) {
                fetch(`get_available_data.php?year=${year}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            if (data.quarters && data.quarters.length > 0) {
                                data.quarters.forEach(quarter => {
                                    const option = document.createElement('option');
                                    option.value = quarter;
                                    option.textContent = quarter;
                                    quarterSelect.appendChild(option);
                                });
                                quarterSelect.disabled = false;
                            } else {
                                Swal.fire({
                                    title: 'Information',
                                    text: 'No quarters available for the selected year',
                                    icon: 'info',
                                    confirmButtonColor: '#6a1b9a'
                                });
                            }
                        } else {
                            throw new Error(data.message || 'Failed to load quarters');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading quarters:', error);
                        Swal.fire({
                            title: 'Error',
                            text: error.message || 'Failed to load quarters',
                            icon: 'error',
                            confirmButtonColor: '#6a1b9a'
                        });
                    });
            }
        }

        // Project team fetching function
        function fetchProjectTeam() {
            const year = document.getElementById('year')?.value;
            const quarter = document.getElementById('quarter')?.value;

            if (year && quarter) {
                // Clear existing personnel
                selectedPersonnel = {
                    projectLeaders: [],
                    assistantProjectLeaders: [],
                    projectStaff: []
                };
                
                const projectLeadersList = document.getElementById('projectLeadersList');
                const assistantProjectLeadersList = document.getElementById('assistantProjectLeadersList');
                const projectStaffList = document.getElementById('projectStaffList');
                
                if (projectLeadersList) projectLeadersList.innerHTML = '';
                if (assistantProjectLeadersList) assistantProjectLeadersList.innerHTML = '';
                if (projectStaffList) projectStaffList.innerHTML = '';
                
                const projectLeaders = document.getElementById('projectLeaders');
                const assistantProjectLeaders = document.getElementById('assistantProjectLeaders');
                const projectStaff = document.getElementById('projectStaff');
                
                if (projectLeaders) projectLeaders.value = '';
                if (assistantProjectLeaders) assistantProjectLeaders.value = '';
                if (projectStaff) projectStaff.value = '';
            }
        }

        // Print function
        function printProposal() {
            const proposalId = document.getElementById('currentProposalId')?.value;
            
            if (!proposalId) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please save the proposal first before printing',
                    icon: 'warning',
                    confirmButtonColor: '#6a1b9a'
                });
                return;
            }
            
            // Ask which version to print
            Swal.fire({
                title: 'Choose Print Format',
                text: 'Select the format you want to use for printing',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'PDF Format',
                cancelButtonText: 'HTML Format',
                showCloseButton: true,
                confirmButtonColor: '#6a1b9a',
                cancelButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    // PDF option selected
                    window.open(`print_proposal.php?id=${proposalId}`, '_blank');
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // HTML option selected
                    window.open(`print_html.php?id=${proposalId}`, '_blank');
                }
            });
        }

        // Function to fetch PPAS data
        function fetchPPASData(year, quarter) {
            fetch(`get_ppas_data.php?year=${year}&quarter=${quarter}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        // Store PPAS ID in hidden field
                        const ppasIdInput = document.getElementById('ppasId') || document.createElement('input');
                        ppasIdInput.type = 'hidden';
                        ppasIdInput.id = 'ppasId';
                        ppasIdInput.name = 'ppasId';
                        ppasIdInput.value = data.data.ppas.id;
                        
                        // Add to form if it doesn't exist
                        const form = document.getElementById('gadProposalForm');
                        if (!document.getElementById('ppasId') && form) {
                            form.appendChild(ppasIdInput);
                        }
                        
                        // Populate title from PPAS data
                        document.getElementById('activityTitle').value = data.data.ppas.activity || '';
                        
                        // Populate project and program
                        document.getElementById('project').value = data.data.ppas.project || '';
                        document.getElementById('program').value = data.data.ppas.program || '';
                        
                        // Populate start and end dates if available 
                        if (data.data.ppas.start_date) {
                            document.getElementById('startDate').value = data.data.ppas.start_date;
                        }
                        
                        if (data.data.ppas.end_date) {
                            document.getElementById('endDate').value = data.data.ppas.end_date;
                        } else if (data.data.ppas.start_date) {
                            // If no end date is available, use start date as fallback
                            document.getElementById('endDate').value = data.data.ppas.start_date;
                        }
                        
                        // Generate work plan dates based on the new date values
                        generateWorkPlanDates();
                        
                        // Populate venue/location if available
                        if (data.data.ppas.location) {
                            document.getElementById('venue').value = data.data.ppas.location;
                        }

                        // Clear existing personnel
                        selectedPersonnel = {
                            projectLeaders: [],
                            assistantProjectLeaders: [],
                            projectStaff: []
                        };

                        // Clear personnel lists
                        document.getElementById('projectLeadersList').innerHTML = '';
                        document.getElementById('assistantProjectLeadersList').innerHTML = '';
                        document.getElementById('projectStaffList').innerHTML = '';
                        
                        // Add note about personnel being read-only
                        const personnelNotes = document.querySelectorAll('.personnel-note');
                        personnelNotes.forEach(note => {
                            note.style.display = 'block';
                        });

                        // Populate personnel data
                        if (data.data.personnel && Array.isArray(data.data.personnel)) {
                            data.data.personnel.forEach(person => {
                                // Map the role from database to our frontend role names
                                let role;
                                switch (person.role) {
                                    case 'project_leader':
                                    case 'projectLeader':
                                        role = 'projectLeaders';
                                        break;
                                    case 'assistant_project_leader':
                                    case 'assistantProjectLeader':
                                    case 'asst_project_leader':
                                        role = 'assistantProjectLeaders';
                                        break;
                                    case 'project_staff':
                                    case 'projectStaff':
                                        role = 'projectStaff';
                                        break;
                                }

                                if (role) {
                                    addPersonnelToList(role, {
                                        id: person.personnel_id,
                                        name: person.personnel_name,
                                        gender: '', // Not provided in this data structure
                                        academic_rank: '' // Not provided in this data structure
                                    });
                                }
                            });
                        }
                        
                        // Show success message
                        Swal.fire({
                            title: 'Data Loaded',
                            text: 'PPAS data has been loaded successfully',
                            icon: 'success',
                            confirmButtonColor: '#6a1b9a',
                            timer: 2000,
                            timerProgressBar: true
                        });
                    } else if (data.error) {
                        console.error('Server error:', data.message);
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to fetch PPAS data: ' + data.message,
                            icon: 'error',
                            confirmButtonColor: '#6a1b9a'
                        });
                    } else {
                        Swal.fire({
                            title: 'No Data Found',
                            text: data.message || 'No PPAS data found for the selected year and quarter',
                            icon: 'info',
                            confirmButtonColor: '#6a1b9a'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching PPAS data:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to fetch PPAS data. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#6a1b9a'
                    });
                });
        }

        // Function to fetch titles for a specific year and quarter
        function fetchTitlesForYearQuarter(year, quarter) {
            // Reset and disable fields
            document.getElementById('startDate').disabled = true;
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').disabled = true;
            document.getElementById('endDate').value = '';
            document.getElementById('venue').disabled = true;
            document.getElementById('venue').value = '';
            
            // Reset participant fields
            document.getElementById('maleBeneficiaries').value = '0';
            document.getElementById('femaleBeneficiaries').value = '0';
            document.getElementById('totalBeneficiaries').value = '0';
            
            // Clear personnel lists
            document.getElementById('projectLeadersList').innerHTML = '';
            document.getElementById('assistantProjectLeadersList').innerHTML = '';
            document.getElementById('projectStaffList').innerHTML = '';
            
            fetch(`get_titles.php?year=${year}&quarter=${quarter}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Raw titles data:', data);
                    
                    const titleSelector = document.getElementById('titleSelector');
                    titleSelector.innerHTML = '<option value="">Select Activity</option>';
                    
                    if (data.success && data.titles && data.titles.length > 0) {
                        // Show what fields are available in the first item
                        if (data.titles[0]) {
                            console.log('Sample title data keys:', Object.keys(data.titles[0]));
                            console.log('Sample title data:', data.titles[0]);
                        }
                        
                        data.titles.forEach(title => {
                            const option = document.createElement('option');
                            option.value = title.id;
                            option.textContent = title.activity;
                            
                            // DIRECT assignment of all data attributes
                            option.dataset.project = title.project || '';
                            option.dataset.program = title.program || '';
                            option.dataset.startDate = title.start_date || '';
                            option.dataset.endDate = title.end_date || '';
                            option.dataset.location = title.location || '';
                            
                            // Store participant data
                            option.dataset.externalType = title.external_type || '';
                            option.dataset.externalMale = title.external_male || '0';
                            option.dataset.externalFemale = title.external_female || '0';
                            option.dataset.totalMale = title.total_male || '0';
                            option.dataset.totalFemale = title.total_female || '0';
                            option.dataset.totalBeneficiaries = title.total_beneficiaries || '0';
                            
                            // Store personnel data as JSON in data attribute
                            if (title.personnel) {
                                option.dataset.personnel = JSON.stringify(title.personnel);
                            }
                            
                            console.log(`Option ${title.id} data:`, {
                                project: option.dataset.project,
                                program: option.dataset.program,
                                startDate: option.dataset.startDate,
                                endDate: option.dataset.endDate,
                                location: option.dataset.location,
                                externalType: option.dataset.externalType,
                                totalMale: option.dataset.totalMale,
                                totalFemale: option.dataset.totalFemale,
                                personnel: title.personnel
                            });
                            
                            titleSelector.appendChild(option);
                        });
                        
                        titleSelector.disabled = false;
                    } else {
                        console.log('No titles found or error in data');
                        titleSelector.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching titles:', error);
                    const titleSelector = document.getElementById('titleSelector');
                    titleSelector.innerHTML = '<option value="">Error loading activities</option>';
                    titleSelector.disabled = true;
                });
        }

        // Add event listener for title selection
        document.getElementById('titleSelector').addEventListener('change', function() {
            const ppasId = this.value;
            
            if (ppasId) {
                // Get selected option
                const selectedOption = this.options[this.selectedIndex];
                
                // Set the activity title in the hidden field
                document.getElementById('activityTitle').value = selectedOption.textContent;
                
                // Enable fields
                document.getElementById('startDate').disabled = false;
                document.getElementById('endDate').disabled = false;
                document.getElementById('venue').disabled = false;
                
                // Populate fields using data attributes
                document.getElementById('project').value = selectedOption.dataset.project || '';
                document.getElementById('program').value = selectedOption.dataset.program || '';
                document.getElementById('startDate').value = formatDateForInput(selectedOption.dataset.startDate);
                document.getElementById('endDate').value = formatDateForInput(selectedOption.dataset.endDate);
                document.getElementById('venue').value = selectedOption.dataset.location || '';
                
                // Populate participant data
                document.getElementById('maleBeneficiaries').value = selectedOption.dataset.externalMale || '0';
                document.getElementById('femaleBeneficiaries').value = selectedOption.dataset.externalFemale || '0';
                document.getElementById('totalBeneficiaries').value = 
                    (parseInt(selectedOption.dataset.externalMale || '0') + 
                     parseInt(selectedOption.dataset.externalFemale || '0')).toString();
                
                // Update participant type text if available
                const participantTypeElement = document.querySelector('h5.mb-3 + p');
                if (participantTypeElement && selectedOption.dataset.externalType) {
                    participantTypeElement.textContent = selectedOption.dataset.externalType || 'Not specified';
                }
                
                console.log('Fields populated from option:', {
                    project: document.getElementById('project').value,
                    program: document.getElementById('program').value,
                    startDate: document.getElementById('startDate').value,
                    endDate: document.getElementById('endDate').value,
                    venue: document.getElementById('venue').value,
                    externalType: selectedOption.dataset.externalType,
                    maleBeneficiaries: document.getElementById('maleBeneficiaries').value,
                    femaleBeneficiaries: document.getElementById('femaleBeneficiaries').value,
                    totalBeneficiaries: document.getElementById('totalBeneficiaries').value
                });
                
                // Generate work plan dates
                generateWorkPlanDates();
                
                // Populate personnel data if available
                if (selectedOption.dataset.personnel) {
                    try {
                        const personnel = JSON.parse(selectedOption.dataset.personnel);
                        console.log('Personnel data from option:', personnel);
                        
                        // Clear existing personnel
                        selectedPersonnel = {
                            projectLeaders: [],
                            assistantProjectLeaders: [],
                            projectStaff: []
                        };
                        
                        // Clear personnel lists
                        document.getElementById('projectLeadersList').innerHTML = '';
                        document.getElementById('assistantProjectLeadersList').innerHTML = '';
                        document.getElementById('projectStaffList').innerHTML = '';
                        
                        // Populate project leaders
                        if (personnel.projectLeaders && personnel.projectLeaders.length > 0) {
                            personnel.projectLeaders.forEach(person => {
                                addPersonnelToList('projectLeaders', person);
                            });
                        }
                        
                        // Populate assistant project leaders
                        if (personnel.assistantProjectLeaders && personnel.assistantProjectLeaders.length > 0) {
                            personnel.assistantProjectLeaders.forEach(person => {
                                addPersonnelToList('assistantProjectLeaders', person);
                            });
                        }
                        
                        // Populate project staff
                        if (personnel.projectStaff && personnel.projectStaff.length > 0) {
                            personnel.projectStaff.forEach(person => {
                                addPersonnelToList('projectStaff', person);
                            });
                        }
                    } catch (error) {
                        console.error('Error parsing personnel data:', error);
                    }
                }
                
                // Fetch data from server
                fetch(`get_ppas_data.php?id=${ppasId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Server response:', data);
                        if (data.success && data.data && data.data.ppas) {
                            console.log('PPAS data:', data.data.ppas);
                            
                            // Store PPAS ID in hidden field
                            const ppasIdInput = document.getElementById('ppasId') || document.createElement('input');
                            ppasIdInput.type = 'hidden';
                            ppasIdInput.id = 'ppasId';
                            ppasIdInput.name = 'ppasId';
                            ppasIdInput.value = data.data.ppas.id;
                            
                            // Add to form if it doesn't exist
                            const form = document.getElementById('gadProposalForm');
                            if (!document.getElementById('ppasId') && form) {
                                form.appendChild(ppasIdInput);
                            }
                            
                            // Update fields with server data
                            document.getElementById('activityTitle').value = data.data.ppas.activity || '';
                            document.getElementById('project').value = data.data.ppas.project || '';
                            document.getElementById('program').value = data.data.ppas.program || '';
                            
                            // Use the formatter for dates
                            document.getElementById('startDate').value = formatDateForInput(data.data.ppas.start_date);
                            document.getElementById('endDate').value = formatDateForInput(data.data.ppas.end_date);
                            document.getElementById('venue').value = data.data.ppas.location || '';
                            
                            // Update participants data from server
                            document.getElementById('maleBeneficiaries').value = data.data.ppas.external_male || '0';
                            document.getElementById('femaleBeneficiaries').value = data.data.ppas.external_female || '0';
                            document.getElementById('totalBeneficiaries').value = 
                                (parseInt(data.data.ppas.external_male || '0') + 
                                 parseInt(data.data.ppas.external_female || '0')).toString();
                            
                            // Update participant type text if available
                            const participantTypeElement = document.querySelector('h5.mb-3 + p');
                            if (participantTypeElement && data.data.ppas.external_type) {
                                participantTypeElement.textContent = data.data.ppas.external_type || 'Not specified';
                            }
                            
                            console.log('After server update:', {
                                startDate: document.getElementById('startDate').value,
                                endDate: document.getElementById('endDate').value,
                                venue: document.getElementById('venue').value,
                                externalType: data.data.ppas.external_type,
                                externalMale: data.data.ppas.external_male,
                                externalFemale: data.data.ppas.external_female
                            });
                            
                            // Generate work plan dates based on the updated date values
                            generateWorkPlanDates();
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching PPAS data:', error);
                    });
            } else {
                // Disable and clear fields if no activity is selected
                document.getElementById('startDate').disabled = true;
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').disabled = true;
                document.getElementById('endDate').value = '';
                document.getElementById('venue').disabled = true;
                document.getElementById('venue').value = '';
                
                // Reset participant fields
                document.getElementById('maleBeneficiaries').value = '0';
                document.getElementById('femaleBeneficiaries').value = '0';
                document.getElementById('totalBeneficiaries').value = '0';
                
                // Reset participant type text
                const participantTypeElement = document.querySelector('h5.mb-3 + p');
                if (participantTypeElement) {
                    participantTypeElement.textContent = 'Children from the families in Cuenca';
                }
                
                // Clear personnel lists
                document.getElementById('projectLeadersList').innerHTML = '';
                document.getElementById('assistantProjectLeadersList').innerHTML = '';
                document.getElementById('projectStaffList').innerHTML = '';
            }
        });

        // Function to populate year dropdown
        function populateYearDropdown() {
            const yearSelect = document.getElementById('year');
            if (!yearSelect) return;

            const currentYear = new Date().getFullYear();
            const startYear = currentYear - 2; // 2 years back
            const endYear = currentYear + 2;   // 2 years forward

            yearSelect.innerHTML = '<option value="">Select Year</option>';

            for (let year = startYear; year <= endYear; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                if (year === currentYear) {
                    option.selected = true;
                }
                yearSelect.appendChild(option);
            }

            // After populating years, enable quarter selection if a year is selected
            enableQuarterDropdown();
        }

        // Function to enable quarter dropdown based on year selection
        function enableQuarterDropdown() {
            const yearSelect = document.getElementById('year');
            const quarterSelect = document.getElementById('quarter');
            const titleSelector = document.getElementById('titleSelector');
            
            if (!quarterSelect || !titleSelector) return;

            if (yearSelect.value) {
                quarterSelect.disabled = false;
                quarterSelect.innerHTML = `
                    <option value="">Select Quarter</option>
                    <option value="1st Quarter">1st Quarter</option>
                    <option value="2nd Quarter">2nd Quarter</option>
                    <option value="3rd Quarter">3rd Quarter</option>
                    <option value="4th Quarter">4th Quarter</option>
                `;
            } else {
                quarterSelect.disabled = true;
                quarterSelect.innerHTML = '<option value="">Select Quarter</option>';
                titleSelector.disabled = true;
                titleSelector.innerHTML = '<option value="">Select Activity</option>';
            }
        }

        // Document ready function
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Initialize page
                showPage(1);
                
                // Set up initial delete buttons for workplan
                const initialRemoveButtons = document.querySelectorAll('#workPlanTable .remove-workplan-row');
                initialRemoveButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const row = this.closest('tr');
                        if (row) {
                            row.remove();
                            renumberWorkplanRows();
                        }
                    });
                });
                
                // Initialize year dropdown
                populateYearDropdown();
                
                // Add event listener for year change
                document.getElementById('year').addEventListener('change', function() {
                    const year = this.value;
                    const quarter = document.getElementById('quarter').value;
                    
                    if (year && quarter) {
                        fetchTitlesForYearQuarter(year, quarter);
                    } else {
                        // Reset and disable fields
                        const titleSelector = document.getElementById('titleSelector');
                        titleSelector.innerHTML = '<option value="">Select Activity</option>';
                        titleSelector.disabled = true;
                        
                        document.getElementById('project').value = '';
                        document.getElementById('program').value = '';
                        
                        document.getElementById('startDate').disabled = true;
                        document.getElementById('startDate').value = '';
                        document.getElementById('endDate').disabled = true;
                        document.getElementById('endDate').value = '';
                        document.getElementById('venue').disabled = true;
                        document.getElementById('venue').value = '';
                    }
                });

                document.getElementById('quarter').addEventListener('change', function() {
                    const year = document.getElementById('year').value;
                    const quarter = this.value;
                    
                    if (year && quarter) {
                        fetchTitlesForYearQuarter(year, quarter);
                    } else {
                        // Reset and disable fields
                        const titleSelector = document.getElementById('titleSelector');
                        titleSelector.innerHTML = '<option value="">Select Activity</option>';
                        titleSelector.disabled = true;
                        
                        document.getElementById('project').value = '';
                        document.getElementById('program').value = '';
                        
                        document.getElementById('startDate').disabled = true;
                        document.getElementById('startDate').value = '';
                        document.getElementById('endDate').disabled = true;
                        document.getElementById('endDate').value = '';
                        document.getElementById('venue').disabled = true;
                        document.getElementById('venue').value = '';
                    }
                });

                // Initialize theme
                const savedTheme = localStorage.getItem('theme') || 'light';
                document.documentElement.setAttribute('data-bs-theme', savedTheme);
                updateThemeIcon(savedTheme);
                
                // Add event listeners for form fields
                const maleBeneficiaries = document.getElementById('maleBeneficiaries');
                const femaleBeneficiaries = document.getElementById('femaleBeneficiaries');
                if (maleBeneficiaries) maleBeneficiaries.addEventListener('input', updateTotalBeneficiaries);
                if (femaleBeneficiaries) femaleBeneficiaries.addEventListener('input', updateTotalBeneficiaries);
                
                // Form submission
                const gadProposalForm = document.getElementById('gadProposalForm');
                if (gadProposalForm) {
                    // Not adding form submit listener since we're using the save button now
                    
                    // Reset form
                    gadProposalForm.reset();
                }
                
                // Initialize CRUD buttons
                initCrudButtons();
                
                // Year selection
                const yearSelect = document.getElementById('year');
                if (yearSelect) {
                    yearSelect.addEventListener('change', function() {
                        const year = this.value;
                        const quarterSelect = document.getElementById('quarter');
                        const titleSelector = document.getElementById('titleSelector');
                        
                        if (quarterSelect) {
                            quarterSelect.value = '';
                            loadQuarters(year);
                        }
                        
                        // Reset the title selector
                        if (titleSelector) {
                            titleSelector.innerHTML = '<option value="">Select Activity</option>';
                            titleSelector.disabled = true;
                        }
                        
                        // Clear project team fields
                        const projectLeaders = document.getElementById('projectLeaders');
                        const assistantProjectLeaders = document.getElementById('assistantProjectLeaders');
                        const projectStaff = document.getElementById('projectStaff');
                        
                        if (projectLeaders) projectLeaders.value = '';
                        if (assistantProjectLeaders) assistantProjectLeaders.value = '';
                        if (projectStaff) projectStaff.value = '';
                    });
                }
                
                // Quarter selection
                const quarterSelect = document.getElementById('quarter');
                if (quarterSelect) {
                    quarterSelect.addEventListener('change', fetchProjectTeam);
                }
                
                // Generate Work Plan dates if start and end dates are already set
                const startDate = document.getElementById('startDate');
                const endDate = document.getElementById('endDate');
                if (startDate && endDate && startDate.value && endDate.value) {
                    generateWorkPlanDates();
                }
                
                // Add event listeners for dates
                if (startDate) startDate.addEventListener('change', generateWorkPlanDates);
                if (endDate) endDate.addEventListener('change', generateWorkPlanDates);
                
                // Work plan and monitoring row buttons
                const addWorkPlanRowBtn = document.getElementById('addWorkPlanRowBtn');
                const addMonitoringRowBtn = document.getElementById('addMonitoringRowBtn');
                if (addWorkPlanRowBtn) addWorkPlanRowBtn.addEventListener('click', addWorkPlanRow);
                if (addMonitoringRowBtn) addMonitoringRowBtn.addEventListener('click', addMonitoringRow);
                
                // Initialize autocomplete for personnel fields if jQuery and jQuery UI are available
                if (typeof $ !== 'undefined' && $.fn.autocomplete) {
                    // We no longer need autocomplete for these fields as they're read-only
                    // The personnel comes from PPAS data and cannot be edited
                    
                    // Add CSS for personnel badges
                    $("<style>")
                        .prop("type", "text/css")
                        .html(`
                            .personnel-badge {
                                font-size: 0.9rem;
                                padding: 0.5rem 0.75rem;
                            }
                            .personnel-badge .remove-personnel {
                                cursor: pointer;
                            }
                        `)
                        .appendTo("head");
                }
                
                // Load available years
                loadAvailableYears();
                
                // Clear textareas
                document.querySelectorAll('textarea').forEach(textarea => {
                    textarea.value = '';
                });
                
                console.log("GAD Proposal form initialization complete");
            } catch (error) {
                console.error("Error initializing GAD Proposal form:", error);
                alert("There was an error initializing the form. Please check the console for details or refresh the page.");
            }
        });

        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileToggle = document.querySelector('.mobile-nav-toggle');
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.querySelector('.sidebar-backdrop');
            
            if (mobileToggle && sidebar && backdrop) {
                mobileToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    backdrop.classList.toggle('show');
                    document.body.classList.toggle('sidebar-open');
                });
                
                backdrop.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                });
            }
        });

        function loadProposal(id) {
            showSpinner();
            fetch(`get_gad_proposal.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fillForm(data.proposal);
                        if (data.activities) {
                            clearExistingActivities(); // Clear any existing activities
                            data.activities.forEach(activity => {
                                addActivity(activity);
                            });
                        }
                        loadPersonnel(id);
                        document.getElementById('currentProposalId').value = id;
                        
                        // Show the print and navigation buttons
                        document.getElementById('printBtn').style.display = 'inline-block';
                        document.getElementById('printHtmlBtn').style.display = 'inline-block';
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to load proposal: ' + data.message,
                            icon: 'error',
                            confirmButtonColor: '#6a1b9a'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading proposal:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to load proposal. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#6a1b9a'
                    });
                });
        }

        // Function to print HTML version
        function printHtmlVersion() {
            const proposalId = document.getElementById('currentProposalId')?.value;
            
            if (!proposalId) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please save the proposal first before printing',
                    icon: 'warning',
                    confirmButtonColor: '#6a1b9a'
                });
                return;
            }
            
            // Open HTML print view in new window
            window.open(`print_html.php?id=${proposalId}`, '_blank');
        }

        // Add print button click handler with error checking
        document.getElementById('printBtn').addEventListener('click', function(e) {
            e.preventDefault();
            const proposalId = document.getElementById('currentProposalId').value;
            if (!proposalId) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please save the proposal before printing.',
                    icon: 'error',
                    confirmButtonColor: '#6a1b9a'
                });
                return;
            }
            window.location.href = 'print_proposal.php?id=' + proposalId;
        });

        function showPage(pageNumber) {
            document.querySelectorAll('.form-page').forEach(page => {
                page.style.display = 'none';
            });
            document.getElementById(`page${pageNumber}`).style.display = 'block';
        }

        function inspectFormFields() {
            // Get selected option
            const titleSelector = document.getElementById('titleSelector');
            const selectedOption = titleSelector.options[titleSelector.selectedIndex];
            
            // Get current values
            const currentValues = {
                'Selected Activity': titleSelector.value,
                'Activity Text': selectedOption ? selectedOption.textContent : 'None selected',
                'Activity Title': document.getElementById('activityTitle').value,
                'Project': document.getElementById('project').value,
                'Program': document.getElementById('program').value,
                'Start Date': document.getElementById('startDate').value,
                'End Date': document.getElementById('endDate').value,
                'Venue': document.getElementById('venue').value
            };
            
            // Get data attributes if option selected
            let dataAttributes = {};
            if (selectedOption && selectedOption.value) {
                dataAttributes = {
                    'Option dataset.project': selectedOption.dataset.project,
                    'Option dataset.program': selectedOption.dataset.program,
                    'Option dataset.startDate': selectedOption.dataset.startDate,
                    'Option dataset.endDate': selectedOption.dataset.endDate,
                    'Option dataset.location': selectedOption.dataset.location
                };
            }
            
            // Create output
            let output = '<h4>Current Field Values:</h4>';
            output += '<pre>' + JSON.stringify(currentValues, null, 2) + '</pre>';
            
            if (Object.keys(dataAttributes).length > 0) {
                output += '<h4>Selected Option Data Attributes:</h4>';
                output += '<pre>' + JSON.stringify(dataAttributes, null, 2) + '</pre>';
            }
            
            // Show in alert
            Swal.fire({
                title: 'Form Debugging',
                html: output,
                width: 800,
                customClass: {
                    popup: 'swal-wide',
                    content: 'text-start'
                }
            });
        }

        // Add this helper function right after the inspectFormFields function
        function formatDateForInput(dateString) {
            if (!dateString) return '';
            
            try {
                // Try different date formats
                let date;
                
                // Try parsing as ISO format first
                if (dateString.includes('T')) {
                    date = new Date(dateString);
                } 
                // Try MM/DD/YYYY format
                else if (dateString.includes('/')) {
                    const parts = dateString.split('/');
                    // MM/DD/YYYY format
                    if (parts.length === 3) {
                        date = new Date(parts[2], parts[0] - 1, parts[1]);
                    }
                }
                // Try YYYY-MM-DD format
                else if (dateString.includes('-')) {
                    date = new Date(dateString);
                }
                // Default to trying direct parsing
                else {
                    date = new Date(dateString);
                }
                
                // Check if date is valid
                if (isNaN(date.getTime())) {
                    console.error('Invalid date:', dateString);
                    return dateString; // Return original if parsing failed
                }
                
                // Format as YYYY-MM-DD for input fields
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                
                return `${year}-${month}-${day}`;
            } catch (e) {
                console.error('Error formatting date:', e);
                return dateString; // Return original if error
            }
        }

        // Function to fetch titles for a specific year and quarter
        function fetchTitlesForYearQuarter(year, quarter) {
            // Reset and disable fields
            document.getElementById('startDate').disabled = true;
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').disabled = true;
            document.getElementById('endDate').value = '';
            document.getElementById('venue').disabled = true;
            document.getElementById('venue').value = '';
            
            // Reset participant fields
            document.getElementById('maleBeneficiaries').value = '0';
            document.getElementById('femaleBeneficiaries').value = '0';
            document.getElementById('totalBeneficiaries').value = '0';
            
            // Clear personnel lists
            document.getElementById('projectLeadersList').innerHTML = '';
            document.getElementById('assistantProjectLeadersList').innerHTML = '';
            document.getElementById('projectStaffList').innerHTML = '';
            
            fetch(`get_titles.php?year=${year}&quarter=${quarter}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Raw titles data:', data);
                    
                    const titleSelector = document.getElementById('titleSelector');
                    titleSelector.innerHTML = '<option value="">Select Activity</option>';
                    
                    if (data.success && data.titles && data.titles.length > 0) {
                        // Show what fields are available in the first item
                        if (data.titles[0]) {
                            console.log('Sample title data keys:', Object.keys(data.titles[0]));
                            console.log('Sample title data:', data.titles[0]);
                        }
                        
                        data.titles.forEach(title => {
                            const option = document.createElement('option');
                            option.value = title.id;
                            option.textContent = title.activity;
                            
                            // DIRECT assignment of all data attributes
                            option.dataset.project = title.project || '';
                            option.dataset.program = title.program || '';
                            option.dataset.startDate = title.start_date || '';
                            option.dataset.endDate = title.end_date || '';
                            option.dataset.location = title.location || '';
                            
                            // Store participant data
                            option.dataset.externalType = title.external_type || '';
                            option.dataset.externalMale = title.external_male || '0';
                            option.dataset.externalFemale = title.external_female || '0';
                            option.dataset.totalMale = title.total_male || '0';
                            option.dataset.totalFemale = title.total_female || '0';
                            option.dataset.totalBeneficiaries = title.total_beneficiaries || '0';
                            
                            // Store personnel data as JSON in data attribute
                            if (title.personnel) {
                                option.dataset.personnel = JSON.stringify(title.personnel);
                            }
                            
                            console.log(`Option ${title.id} data:`, {
                                project: option.dataset.project,
                                program: option.dataset.program,
                                startDate: option.dataset.startDate,
                                endDate: option.dataset.endDate,
                                location: option.dataset.location,
                                externalType: option.dataset.externalType,
                                totalMale: option.dataset.totalMale,
                                totalFemale: option.dataset.totalFemale,
                                personnel: title.personnel
                            });
                            
                            titleSelector.appendChild(option);
                        });
                        
                        titleSelector.disabled = false;
                    } else {
                        console.log('No titles found or error in data');
                        titleSelector.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching titles:', error);
                    const titleSelector = document.getElementById('titleSelector');
                    titleSelector.innerHTML = '<option value="">Error loading activities</option>';
                    titleSelector.disabled = true;
                });
        }

        // Add event listener for quarter selection
        document.getElementById('quarter').addEventListener('change', function() {
            const year = document.getElementById('year').value;
            const quarter = this.value;
            
            if (year && quarter) {
                fetchTitlesForYearQuarter(year, quarter);
            }
        });

        // Function to delete the current proposal
        function deleteProposal() {
            const proposalId = document.getElementById('currentProposalId').value;
            
            if (!proposalId) {
                Swal.fire({
                    title: 'Error',
                    text: 'No proposal selected for deletion.',
                    icon: 'error',
                    confirmButtonColor: '#6a1b9a'
                });
                return;
            }
            
            // Ask for confirmation before deleting
            Swal.fire({
                title: 'Delete Proposal',
                text: 'Are you sure you want to delete this proposal? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading indicator
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the proposal',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Send delete request to server
                    fetch('delete_gad_proposal.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: proposalId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'Your proposal has been deleted successfully.',
                                icon: 'success',
                                confirmButtonColor: '#6a1b9a'
                            }).then(() => {
                                // Redirect to the proposal list page
                                window.location.href = 'proposal_list.php';
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'Failed to delete the proposal.',
                                icon: 'error',
                                confirmButtonColor: '#6a1b9a'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while deleting the proposal.',
                            icon: 'error',
                            confirmButtonColor: '#6a1b9a'
                        });
                    });
                }
            });
        }

        // Function to update the current proposal
        function updateProposal() {
            const proposalId = document.getElementById('currentProposalId').value;
            
            if (!proposalId) {
                Swal.fire({
                    title: 'Error',
                    text: 'No proposal selected for update.',
                    icon: 'error',
                    confirmButtonColor: '#6a1b9a'
                });
                return;
            }
            
            // Show validation message
            if (!validateCurrentPage()) {
                Swal.fire({
                    title: 'Required Fields',
                    text: 'Please fill in all required fields before updating.',
                    icon: 'warning',
                    confirmButtonColor: '#6a1b9a'
                });
                return;
            }
            
            // Prepare form data for submission
            const form = document.getElementById('gadProposalForm');
            const formData = new FormData(form);
            
            // Show loading indicator
            Swal.fire({
                title: 'Updating...',
                text: 'Please wait while we update your GAD proposal',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Collect additional data if needed (similar to handleFormSubmit function)
            // Collect activities data
            const activitiesContainer = document.getElementById('activitiesContainer');
            const activityItems = activitiesContainer.querySelectorAll('.activity-item');
            
            activityItems.forEach((item, index) => {
                const titleInput = item.querySelector('input[type="text"]');
                const detailsTextarea = item.querySelector('textarea');
                
                if (titleInput && titleInput.value.trim() !== '') {
                    formData.append(`activity_title_${index + 1}`, titleInput.value);
                    if (detailsTextarea) {
                        formData.append(`activity_details_${index + 1}`, detailsTextarea.value);
                    }
                }
            });
            
            // Collect monitoring data
            const monitoringTable = document.querySelector('#page8 table tbody');
            if (monitoringTable) {
                const monitoringRows = monitoringTable.querySelectorAll('tr');
                monitoringRows.forEach((row, index) => {
                    const textareas = row.querySelectorAll('textarea');
                    if (textareas.length === 8) {
                        formData.append(`monitoring_objectives_${index + 1}`, textareas[0].value);
                        formData.append(`monitoring_indicators_${index + 1}`, textareas[1].value);
                        formData.append(`monitoring_baseline_${index + 1}`, textareas[2].value);
                        formData.append(`monitoring_target_${index + 1}`, textareas[3].value);
                        formData.append(`monitoring_activity_${index + 1}`, textareas[4].value);
                        formData.append(`monitoring_timeframe_${index + 1}`, textareas[5].value);
                        formData.append(`monitoring_responsible_${index + 1}`, textareas[6].value);
                        formData.append(`monitoring_budget_${index + 1}`, textareas[7].value);
                    }
                });
            }
            
            // Send update request to server (using the same endpoint as saving)
            fetch('save_gad_proposal.php', {
                method: 'POST',
                body: formData,
                // Add headers to prevent caching issues
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
            .then(response => {
                // Check for HTTP errors first
                if (!response.ok) {
                    if (response.status === 500) {
                        console.error('Server error 500. Database or server configuration issue.');
                        throw new Error('Database server error. Please contact administrator.');
                    }
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Check content type
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.error('Non-JSON response received');
                    return response.text().then(text => {
                        console.error('Error response text:', text);
                        throw new Error('Received invalid response from server');
                    });
                }
                
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Updated!',
                        text: 'Your proposal has been updated successfully.',
                        icon: 'success',
                        confirmButtonColor: '#6a1b9a'
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Failed to update the proposal.',
                        icon: 'error',
                        confirmButtonColor: '#6a1b9a'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'An error occurred while updating the proposal: ' + (error.message || 'Unknown error'),
                    icon: 'error',
                    confirmButtonColor: '#6a1b9a'
                });
            });
        }

        // Function to create a new proposal
        function createNewProposal() {
            // Check if there are unsaved changes
            if (document.getElementById('currentProposalId').value) {
                Swal.fire({
                    title: 'Create New Proposal',
                    text: 'Any unsaved changes to the current proposal will be lost. Do you want to continue?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, create new',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect to a new proposal page
                        window.location.href = 'gad_proposal.php';
                    }
                });
            } else {
                // Reset the form for a new proposal
                document.getElementById('gadProposalForm').reset();
                document.getElementById('currentProposalId').value = '';
                
                // Show first page
                currentPage = 1;
                showPage(currentPage);
                
                // Update button visibility
                document.getElementById('prevBtn').style.display = 'none';
                document.getElementById('nextBtn').style.display = 'block';
                // Remove submitBtn reference since we're using the save button
                updatePrintButtonVisibility();
                
                // Clear any validation errors
                document.querySelectorAll('.is-invalid').forEach(field => {
                    field.classList.remove('is-invalid');
                });
                
                // Reset activity items
                const activitiesContainer = document.getElementById('activitiesContainer');
                if (activitiesContainer) {
                    // Keep only the first activity item if it exists
                    const activityItems = activitiesContainer.querySelectorAll('.activity-item');
                    if (activityItems.length > 0) {
                        // Clear the first item's fields
                        const firstItem = activityItems[0];
                        const titleInput = firstItem.querySelector('input[type="text"]');
                        const detailsTextarea = firstItem.querySelector('textarea');
                        
                        if (titleInput) titleInput.value = '';
                        if (detailsTextarea) detailsTextarea.value = '';
                        
                        // Remove all other items
                        for (let i = 1; i < activityItems.length; i++) {
                            activityItems[i].remove();
                        }
                    }
                }
                
                // Reset monitoring rows
                const monitoringTable = document.querySelector('#page8 table tbody');
                if (monitoringTable) {
                    // Keep only the first row if it exists
                    const monitoringRows = monitoringTable.querySelectorAll('tr');
                    if (monitoringRows.length > 0) {
                        // Clear the first row's fields
                        const firstRow = monitoringRows[0];
                        const textareas = firstRow.querySelectorAll('textarea');
                        
                        textareas.forEach(textarea => {
                            textarea.value = '';
                        });
                        
                        // Remove all other rows
                        for (let i = 1; i < monitoringRows.length; i++) {
                            monitoringRows[i].remove();
                        }
                    }
                }
            }
        }

        // Initialize event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize form
            initForm();
            
            // Initialize the CRUD buttons
            initCrudButtons();
            
            // Initialize multi-select dropdowns
            initializeMultiSelects();
            
            // Setup event listeners
            const gadProposalForm = document.getElementById('gadProposalForm');
            // Remove form submit listener since we're using the save button now
            
            document.getElementById('maleBeneficiaries').addEventListener('input', updateTotalBeneficiaries);
            document.getElementById('femaleBeneficiaries').addEventListener('input', updateTotalBeneficiaries);
            
            // Initialize first page
            showPage(1);
            
            // Initialize activity management
            initActivityButtons();
            
            // Initialize monitoring rows
            addMonitoringRow();
            
            // Initialize modal event listeners
            initializeModals();
        });

        // Initialize the form with default values and settings
        function initForm() {
            // Reset the form
            const form = document.getElementById('gadProposalForm');
            if (form) form.reset();
            
            // Set initial page
            currentPage = 1;
            showPage(currentPage);
            
            // Update navigation buttons
            document.getElementById('prevBtn').style.display = 'none';
            document.getElementById('nextBtn').style.display = 'block';
            
            // Clear any existing validation errors
            document.querySelectorAll('.is-invalid').forEach(field => {
                field.classList.remove('is-invalid');
            });
            
            // Set default values for any fields that need them
            const today = new Date();
            const year = today.getFullYear();
            
            // Set current fiscal year if available
            const yearSelect = document.getElementById('year');
            if (yearSelect && yearSelect.options.length > 0) {
                // Try to select current year if it exists
                for (let i = 0; i < yearSelect.options.length; i++) {
                    if (parseInt(yearSelect.options[i].value) === year) {
                        yearSelect.selectedIndex = i;
                        break;
                    }
                }
            }
            
            // Initialize date fields with current date format
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                if (!input.value) {
                    const formattedDate = today.toISOString().split('T')[0];
                    input.value = formattedDate;
                }
            });
            
            // Reset any counters or totals
            updateTotalBeneficiaries();
            
            console.log('Form initialized successfully');
        }
        
        // Initialize multi-select dropdowns for personnel selection
        function initializeMultiSelects() {
            // Initialize personnel dropdowns
            const projectLeadersInput = document.getElementById('projectLeaders');
            const assistantProjectLeadersInput = document.getElementById('assistantProjectLeaders');
            const projectStaffInput = document.getElementById('projectStaff');
            
            // Personnel lists containers
            const projectLeadersList = document.getElementById('projectLeadersList');
            const assistantProjectLeadersList = document.getElementById('assistantProjectLeadersList');
            const projectStaffList = document.getElementById('projectStaffList');
            
            // Make readonly if personnel comes from PPAS data
            if (projectLeadersInput) projectLeadersInput.readOnly = true;
            if (assistantProjectLeadersInput) assistantProjectLeadersInput.readOnly = true;
            if (projectStaffInput) projectStaffInput.readOnly = true;
            
            // Show personnel notes if they exist
            const personnelNotes = document.querySelectorAll('.personnel-note');
            personnelNotes.forEach(note => {
                note.style.display = 'block';
            });
            
            console.log('Multi-select dropdowns initialized');
        }
        
        // Initialize activity buttons
        function initActivityButtons() {
            try {
                // Add activity button
                const addActivityBtn = document.getElementById('addActivityBtn');
                if (addActivityBtn) {
                    addActivityBtn.addEventListener('click', function() {
                        // Create a new empty activity
                        addActivity();
                    });
                }
                
                // Set up initial activity UI
                const activitiesContainer = document.getElementById('activitiesContainer');
                if (activitiesContainer && activitiesContainer.children.length === 0) {
                    // Add a first empty activity if container is empty
                    // Pass an empty object to ensure no undefined access errors
                    addActivity({title: "", details: ""});
                }
                
                console.log('Activity buttons initialized');
            } catch (error) {
                console.error('Error initializing activity buttons:', error);
            }
        }
        
        // Initialize modal event listeners
        function initializeModals() {
            // Edit modal pagination
            document.getElementById('editPrevPage')?.addEventListener('click', function() {
                if (editModalPage > 1) {
                    editModalPage--;
                    updateEditModalTable();
                }
            });
            
            document.getElementById('editNextPage')?.addEventListener('click', function() {
                const totalPages = Math.ceil(editModalProposals.length / rowsPerModalPage);
                if (editModalPage < totalPages) {
                    editModalPage++;
                    updateEditModalTable();
                }
            });
            
            // Delete modal pagination
            document.getElementById('deletePrevPage')?.addEventListener('click', function() {
                if (deleteModalPage > 1) {
                    deleteModalPage--;
                    updateDeleteModalTable();
                }
            });
            
            document.getElementById('deleteNextPage')?.addEventListener('click', function() {
                const totalPages = Math.ceil(deleteModalProposals.length / rowsPerModalPage);
                if (deleteModalPage < totalPages) {
                    deleteModalPage++;
                    updateDeleteModalTable();
                }
            });
            
            // View modal pagination
            document.getElementById('viewPrevPage')?.addEventListener('click', function() {
                if (viewModalPage > 1) {
                    viewModalPage--;
                    updateViewModalTable();
                }
            });
            
            document.getElementById('viewNextPage')?.addEventListener('click', function() {
                const totalPages = Math.ceil(viewModalProposals.length / rowsPerModalPage);
                if (viewModalPage < totalPages) {
                    viewModalPage++;
                    updateViewModalTable();
                }
            });
            
            console.log('Modal event listeners initialized');
        }

        // Function to update personnel lists from proposal data
        function updatePersonnelFromProposal(personnelData) {
            // Reset personnel lists first
            clearPersonnelLists();
            
            // Group personnel by role
            const projectLeaders = [];
            const assistantProjectLeaders = [];
            const projectStaff = [];
            
            personnelData.forEach(person => {
                if (!person || !person.role) return;
                
                // Convert snake_case to camelCase for role matching if needed
                const role = person.role.toLowerCase().replace(/_/g, '');
                
                if (role === 'projectleader' || role === 'project_leader') {
                    projectLeaders.push(person);
                } else if (role === 'assistantprojectleader' || role === 'assistant_project_leader') {
                    assistantProjectLeaders.push(person);
                } else if (role === 'projectstaff' || role === 'project_staff') {
                    projectStaff.push(person);
                }
            });
            
            // Update UI for each group
            const projectLeadersList = document.getElementById('projectLeadersList');
            const assistantProjectLeadersList = document.getElementById('assistantProjectLeadersList');
            const projectStaffList = document.getElementById('projectStaffList');
            
            // Helper function to add personnel to list
            function addPersonnelToList(list, personnel) {
                if (!list) return;
                
                personnel.forEach(person => {
                    const personName = person.personnel_name || person.name || 'Unknown';
                    const personId = person.personnel_id || person.id || 0;
                    
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center';
                    li.innerHTML = `
                        ${personName}
                        <button type="button" class="btn btn-sm btn-danger remove-personnel" 
                                 data-id="${personId}" data-name="${personName}">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    list.appendChild(li);
                    
                    // Store in the selectedPersonnel object for form submission
                    if (list === projectLeadersList && selectedPersonnel) {
                        selectedPersonnel.projectLeaders.push({
                            id: personId,
                            name: personName
                        });
                    } else if (list === assistantProjectLeadersList && selectedPersonnel) {
                        selectedPersonnel.assistantProjectLeaders.push({
                            id: personId,
                            name: personName
                        });
                    } else if (list === projectStaffList && selectedPersonnel) {
                        selectedPersonnel.projectStaff.push({
                            id: personId,
                            name: personName
                        });
                    }
                });
            }
            
            // Add personnel to their respective lists
            if (projectLeadersList) addPersonnelToList(projectLeadersList, projectLeaders);
            if (assistantProjectLeadersList) addPersonnelToList(assistantProjectLeadersList, assistantProjectLeaders);
            if (projectStaffList) addPersonnelToList(projectStaffList, projectStaff);
        }

        // Function to add activity from loaded data
        function addActivity(activity) {
            // Skip if no activity container exists
            const activitiesContainer = document.getElementById('activitiesContainer');
            if (!activitiesContainer) {
                console.warn('Activities container not found');
                return;
            }
            
            try {
                // Check if activity is defined
                if (activity === undefined) {
                    // Create an empty activity object when no parameter is provided
                    activity = { title: '', details: '' };
                }
                
                // Get activity data with fallbacks
                const title = activity && activity.title ? activity.title : '';
                const details = activity && activity.details ? activity.details : '';
                
                // Create a new activity row
                const activityDiv = document.createElement('div');
                activityDiv.className = 'activity-item mb-3 p-3 border rounded';
                
                // Generate a unique ID for this activity
                const activityId = 'activity_' + new Date().getTime() + '_' + Math.floor(Math.random() * 1000);
                activityDiv.dataset.id = activityId;
                
                // Create the activity content
                activityDiv.innerHTML = `
                    <div class="form-group mb-2">
                        <label for="${activityId}_title">Activity Title</label>
                        <input type="text" class="form-control activity-title" id="${activityId}_title" value="${title}" required>
                    </div>
                    <div class="form-group mb-2">
                        <label for="${activityId}_details">Activity Details</label>
                        <textarea class="form-control activity-details" id="${activityId}_details" rows="2">${details}</textarea>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm remove-activity">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                `;
                
                // Add event listener for remove button
                const removeButton = activityDiv.querySelector('.remove-activity');
                if (removeButton) {
                    removeButton.addEventListener('click', function() {
                        activityDiv.remove();
                    });
                }
                
                // Append to container
                activitiesContainer.appendChild(activityDiv);
            } catch (e) {
                console.error('Error adding activity:', e);
            }
        }

        // Function to safely load personnel for a proposal
        function loadPersonnel(proposalId) {
            try {
                // Clear existing personnel
                clearPersonnelLists();
                
                // Fetch personnel data for the proposal
                fetch(`get_proposal_personnel.php?id=${proposalId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.personnel) {
                            // Update the personnel lists
                            updatePersonnelFromProposal(data.personnel);
                        } else {
                            console.warn('No personnel data returned or request unsuccessful');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading personnel:', error);
                    });
            } catch (error) {
                console.error('Error in loadPersonnel function:', error);
            }
        }

        // Add new functions to handle proposal editing and deletion by selected ID
        function editProposalById() {
            const selectedId = document.getElementById('selectedEditId').value;
            if (!selectedId) {
                showErrorAlert('Please select a proposal to edit');
                return;
            }
            editProposal(selectedId);
        }

        function confirmDeleteProposalById() {
            console.log("confirmDeleteProposalById function called");
            
            try {
                // Try multiple ways to find the selected proposal ID
                let proposalId = null;
                
                // Method 1: Check for a row with the 'selected' class
                const selectedRow = document.querySelector('#deleteModalBody tr.selected');
                
                if (selectedRow) {
                    proposalId = selectedRow.getAttribute('data-id');
                    console.log(`Found selected row with data-id: ${proposalId}`);
                } 
                // Method 2: Check the hidden input field
                else {
                    const selectedIdInput = document.getElementById('selectedDeleteId');
                    if (selectedIdInput && selectedIdInput.value) {
                        proposalId = selectedIdInput.value;
                        console.log(`Found ID in hidden input: ${proposalId}`);
                    }
                }
                
                // If we still don't have an ID, show error
                if (!proposalId) {
                    console.error("No proposal selected for deletion");
                    Swal.fire({
                        title: 'Selection Required',
                        text: 'Please select a proposal to delete first',
                        icon: 'warning'
                    });
                    return;
                }
                
                // Find the proposal in the deleteModalProposals array
                const proposal = deleteModalProposals.find(p => p.id == proposalId);
                console.log("Found proposal object:", proposal);
                
                if (!proposal) {
                    console.error(`Proposal with ID ${proposalId} not found in deleteModalProposals array`);
                    Swal.fire({
                        title: 'Error',
                        text: 'Proposal data not found. The list may be outdated. Please try refreshing the page.',
                        icon: 'error'
                    });
                    return;
                }
                
                // Format dates safely
                let startDateDisplay = "Not available";
                let endDateDisplay = "Not available";
                
                try {
                    if (proposal.start_date) {
                        startDateDisplay = new Date(proposal.start_date).toLocaleDateString();
                    }
                    if (proposal.end_date) {
                        endDateDisplay = new Date(proposal.end_date).toLocaleDateString();
                    }
                } catch (e) {
                    console.error('Error formatting dates:', e);
                }
                
                // Get the title from either activity_title or title property
                const title = proposal.activity_title || proposal.title || 'Untitled';
                const year = proposal.year || 'N/A';
                const quarter = proposal.quarter || 'N/A';
                
                // Show confirmation dialog
                Swal.fire({
                    title: 'Delete Proposal',
                    html: `
                        <p>Are you sure you want to delete this proposal?</p>
                        <div class="text-start mt-3">
                            <p><strong>Title:</strong> ${title}</p>
                            <p><strong>Year/Quarter:</strong> ${year} - ${quarter}</p>
                            <p><strong>Date:</strong> ${startDateDisplay} to ${endDateDisplay}</p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Call deleteProposal with the ID
                        deleteProposal(proposalId);
                    }
                });
            } catch (error) {
                console.error("Error in confirmDeleteProposalById:", error);
                Swal.fire({
                    title: 'Error',
                    text: 'An unexpected error occurred. Please try again.',
                    icon: 'error'
                });
            }
        }

        // Function to delete a proposal
        function deleteProposal(id) {
            console.log(`Deleting proposal with ID: ${id}`);
            
            if (!id) {
                console.error("No proposal ID provided to deleteProposal function");
                Swal.fire({
                    title: 'Error',
                    text: 'Proposal ID is required',
                    icon: 'error'
                });
                return;
            }
            
            // Show loading with no backdrop to prevent stacking issues
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while we delete the proposal',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                backdrop: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Send request to delete
            fetch('delete_gad_proposal.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${id}`
            })
            .then(response => {
                console.log("Delete response received");
                return response.json();
            })
            .then(data => {
                console.log("Delete response data:", data);
                
                // Always close any open modal first before showing success/error message
                safelyCloseModal('deleteModal');
                
                // Small delay to ensure modal is properly closed
                setTimeout(() => {
                    if (data.success) {
                        // Close the success alert quickly to avoid multiple layers
                        Swal.fire({
                            title: 'Deleted!',
                            text: data.message || 'Proposal has been deleted.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false,
                            backdrop: false
                        }).then(() => {
                            // If we're deleting the currently loaded proposal, reset the form
                            const currentId = document.getElementById('currentProposalId')?.value;
                            if (currentId == id) {
                                document.getElementById('gadProposalForm').reset();
                            }
                            
                            // Reload data after a small delay to prevent UI freezing
                            setTimeout(() => {
                                // Reload proposals
                                loadProposals();
                            }, 300);
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || 'Failed to delete proposal.',
                            icon: 'error',
                            backdrop: false
                        });
                    }
                }, 300);
            })
            .catch(error => {
                console.error('Error deleting proposal:', error);
                
                // Close modal first
                safelyCloseModal('deleteModal');
                
                // Show error after a small delay
                setTimeout(() => {
                    Swal.fire({
                        title: 'Error',
                        text: 'An error occurred while deleting the proposal. Please try again.',
                        icon: 'error',
                        backdrop: false
                    });
                }, 300);
            });
        }

        // Function to handle edit by ID
        function editProposalById() {
            try {
                const selectedId = document.getElementById('selectedEditId').value;
                if (!selectedId) {
                    showErrorAlert('Please select a proposal to edit');
                    return;
                }
                
                console.log("Selected ID for editing:", selectedId);
                
                // Close the modal properly
                const editModal = document.getElementById('editModal');
                if (editModal) {
                    // Close modal directly using Bootstrap's API
                    try {
                        // First remove any aria-hidden attributes that might cause issues
                        document.querySelectorAll('[aria-hidden="true"]').forEach(el => {
                            el.removeAttribute('aria-hidden');
                        });
                        
                        // Use Bootstrap's API to close the modal safely
                        const bsModal = bootstrap.Modal.getInstance(editModal);
                        if (bsModal) {
                            bsModal.hide();
                        } else {
                            // Fallback if bootstrap modal instance not found
                            editModal.classList.remove('show');
                            editModal.style.display = 'none';
                            
                            // Remove backdrop
                            const backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) backdrop.remove();
                            
                            // Clean up body classes and styles
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';
                        }
                        
                        // Additional cleanup after a short delay
                        setTimeout(() => {
                            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';
                        }, 300);
                    } catch (error) {
                        console.error('Error closing modal:', error);
                        // Extra fallback - try to remove all modals and backdrops
                        try {
                            document.querySelectorAll('.modal').forEach(m => {
                                m.classList.remove('show');
                                m.style.display = 'none';
                            });
                            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';
                        } catch (e) {
                            console.error('Failed fallback modal cleanup:', e);
                        }
                    }
                }
                
                // Wait a moment before proceeding to edit
                setTimeout(() => {
                    editProposal(selectedId);
                }, 300);
            } catch (error) {
                console.error("Error in editProposalById:", error);
                showErrorAlert("An error occurred while trying to edit the proposal. Please try again.");
            }
        }
        
        // Function to handle view by ID
        function viewProposalById() {
            try {
                const selectedId = document.getElementById('selectedViewModalId').value;
                if (!selectedId) {
                    showErrorAlert('Please select a proposal to view');
                    return;
                }
                
                console.log("Selected ID for viewing:", selectedId);
                
                // Close the modal properly
                const viewModal = document.getElementById('viewModal');
                if (viewModal) {
                    // Close modal directly using Bootstrap's API
                    try {
                        // First remove any aria-hidden attributes that might cause issues
                        document.querySelectorAll('[aria-hidden="true"]').forEach(el => {
                            el.removeAttribute('aria-hidden');
                        });
                        
                        // Use Bootstrap's API to close the modal safely
                        const bsModal = bootstrap.Modal.getInstance(viewModal);
                        if (bsModal) {
                            bsModal.hide();
                        } else {
                            // Fallback if bootstrap modal instance not found
                            viewModal.classList.remove('show');
                            viewModal.style.display = 'none';
                            
                            // Remove backdrop
                            const backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) backdrop.remove();
                            
                            // Clean up body classes and styles
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';
                        }
                        
                        // Additional cleanup after a short delay
                        setTimeout(() => {
                            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';
                        }, 300);
                    } catch (error) {
                        console.error('Error closing modal:', error);
                        // Extra fallback - try to remove all modals and backdrops
                        try {
                            document.querySelectorAll('.modal').forEach(m => {
                                m.classList.remove('show');
                                m.style.display = 'none';
                            });
                            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';
                        } catch (e) {
                            console.error('Failed fallback modal cleanup:', e);
                        }
                    }
                }
                
                // Wait a moment before proceeding to view
                setTimeout(() => {
                    viewProposal(selectedId);
                }, 300);
            } catch (error) {
                console.error("Error in viewProposalById:", error);
                showErrorAlert("An error occurred while trying to view the proposal. Please try again.");
            }
        }
        
        // Add the fix for Bootstrap modal issues
        document.addEventListener('DOMContentLoaded', function() {
            // Fix for Bootstrap modal issues with aria-hidden
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'aria-hidden' && mutation.target.getAttribute('aria-hidden') === 'true') {
                        // If a focused element is a descendant of an element with aria-hidden="true", remove the aria-hidden
                        if (mutation.target.contains(document.activeElement)) {
                            mutation.target.removeAttribute('aria-hidden');
                            console.log('Removed problematic aria-hidden attribute from element containing focus');
                        }
                    }
                });
            });
            
            // Start observing the document for aria-hidden changes
            observer.observe(document.body, { 
                attributes: true, 
                attributeFilter: ['aria-hidden'], 
                subtree: true 
            });
            
            // Make all modal close buttons clear aria-hidden attributes before closing
            document.querySelectorAll('.modal .btn-close, .modal .btn-secondary[data-bs-dismiss="modal"]').forEach(closeBtn => {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Clear all aria-hidden attributes
                    document.querySelectorAll('[aria-hidden="true"]').forEach(el => {
                        el.removeAttribute('aria-hidden');
                    });
                    
                    // Then try to close the modal safely
                    const modal = this.closest('.modal');
                    if (modal) {
                        safelyCloseModal(modal);
                    }
                });
            });
        });

        // Add the missing safelyCloseModal function before it's used
        function safelyCloseModal(modal) {
            if (!modal) return;
            
            try {
                // First remove any aria-hidden attributes that might cause issues
                document.querySelectorAll('[aria-hidden="true"]').forEach(el => {
                    el.removeAttribute('aria-hidden');
                });
                
                // Use Bootstrap's API to close the modal safely
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                } else {
                    // Fallback if bootstrap modal instance not found
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                    
                    // Remove backdrop
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                    
                    // Clean up body classes and styles
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
                
                // Additional cleanup after a short delay
                setTimeout(() => {
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, 100);
                
                console.log('Modal cleanup completed');
            } catch (error) {
                console.error(`Error while safely closing modal ${modal}:`, error);
            }
        }

        // Add this at the beginning of your script section
        // Function to safely close Bootstrap modals
        function safelyCloseModal(modalId) {
            console.log(`Attempting to safely close modal: ${modalId}`);
            try {
                // First, find the modal element
                const modalElement = document.getElementById(modalId);
                if (!modalElement) {
                    console.warn(`Modal element with ID ${modalId} not found`);
                    return;
                }
                
                // Remove aria-hidden attributes that might cause issues
                document.querySelectorAll('[aria-hidden="true"]').forEach(el => {
                    console.log('Removing aria-hidden attribute from element', el);
                    el.removeAttribute('aria-hidden');
                });
                
                // Try to use Bootstrap's API to close the modal
                try {
                    const bsModal = bootstrap.Modal.getInstance(modalElement);
                    if (bsModal) {
                        console.log('Closing modal using Bootstrap API');
                        bsModal.hide();
                        return; // If successful, we're done
                    } else {
                        console.warn('No Bootstrap modal instance found, using fallback methods');
                    }
                } catch (bootstrapError) {
                    console.error('Error using Bootstrap API to close modal:', bootstrapError);
                }
                
                // Fallback method if Bootstrap API failed
                console.log('Using fallback modal closing methods');
                
                // Remove show class and hide modal
                modalElement.classList.remove('show');
                modalElement.style.display = 'none';
                
                // Force remove backdrop
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => {
                    console.log('Removing backdrop element', backdrop);
                    backdrop.remove();
                });
                
                // Clean up body
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
                
                // Final cleanup after a short delay
                setTimeout(() => {
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, 100);
                
                console.log('Modal cleanup completed');
            } catch (error) {
                console.error(`Error while safely closing modal ${modalId}:`, error);
            }
        }

        // Add this code after the safelyCloseModal function but before other script code

        // Global modal fix - run on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing modal fixes');
            
            // Fix for modal backdrop issues
            setupModalBackdropFix();
            
            // Add global listeners to close buttons
            setupModalCloseListeners();
        });

        // Setup MutationObserver to fix modal backdrop issues
        function setupModalBackdropFix() {
            try {
                console.log('Setting up modal backdrop fix observer');
                
                // Create a mutation observer to watch for aria-hidden attributes
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        // Look for attribute changes
                        if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
                            const target = mutation.target;
                            
                            // If aria-hidden is set to true and it's not a modal
                            if (target.getAttribute('aria-hidden') === 'true' && 
                                !target.classList.contains('modal') && 
                                !target.closest('.modal')) {
                                
                                console.log('Fixing aria-hidden attribute on element:', target);
                                target.removeAttribute('aria-hidden');
                            }
                        }
                        
                        // If nodes are added, check for .modal-backdrop that might be orphaned
                        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                            // Check if a modal backdrop was added but no modal is visible
                            const hasBackdrop = document.querySelector('.modal-backdrop');
                            const hasVisibleModal = document.querySelector('.modal.show');
                            
                            if (hasBackdrop && !hasVisibleModal) {
                                console.log('Removing orphaned modal backdrop');
                                hasBackdrop.remove();
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';
                            }
                        }
                    });
                });
                
                // Start observing the entire document
                observer.observe(document.documentElement, {
                    attributes: true,
                    childList: true,
                    subtree: true,
                    attributeFilter: ['aria-hidden']
                });
                
                console.log('Modal backdrop fix observer initialized successfully');
            } catch (error) {
                console.error('Error setting up modal backdrop fix:', error);
            }
        }

        // Set up listeners for all modal close buttons
        function setupModalCloseListeners() {
            try {
                console.log('Setting up modal close button listeners');
                
                // Add click handlers to all modal close buttons
                document.querySelectorAll('[data-bs-dismiss="modal"], .modal .btn-close, .modal .close').forEach(button => {
                    button.addEventListener('click', function(e) {
                        const modal = this.closest('.modal');
                        if (modal) {
                            const modalId = modal.id;
                            console.log(`Close button clicked for modal: ${modalId}`);
                            
                            // Use our safe close function
                            setTimeout(() => {
                                safelyCloseModal(modalId);
                            }, 100);
                        }
                    });
                });
                
                console.log('Modal close button listeners initialized successfully');
            } catch (error) {
                console.error('Error setting up modal close button listeners:', error);
            }
        }

        // Enhanced version of the deleteProposal function to avoid screen freezing
        function deleteProposal(id) {
            console.log(`Starting deletion process for proposal ID: ${id}`);
            
            if (!id) {
                console.error("No proposal ID provided to deleteProposal function");
                Swal.fire({
                    title: 'Error',
                    text: 'Proposal ID is required',
                    icon: 'error'
                });
                return;
            }
            
            // Before showing loading, close any modal that might be open
            const openModals = document.querySelectorAll('.modal.show');
            for (const modal of openModals) {
                if (modal.id) {
                    console.log(`Pre-emptively closing modal: ${modal.id}`);
                    safelyCloseModal(modal.id);
                }
            }
            
            // Short delay before showing loading indicator
            setTimeout(() => {
                // Show loading with no backdrop to prevent stacking issues
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    backdrop: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Send request to delete after a short delay
                setTimeout(() => {
                    console.log(`Sending delete request for proposal ID: ${id}`);
                    fetch('delete_gad_proposal.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id=${id}`
                    })
                    .then(response => {
                        console.log("Delete response received");
                        return response.json();
                    })
                    .then(data => {
                        console.log("Delete response data:", data);
                        
                        // Close loading indicator
                        Swal.close();
                        
                        // Process the response after a short delay
                        setTimeout(() => {
                            if (data.success) {
                                // Brief success message
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'Proposal has been deleted',
                                    icon: 'success',
                                    timer: 1000,
                                    showConfirmButton: false,
                                    backdrop: false
                                });
                                
                                // Update UI after delay to prevent freezing
                                setTimeout(() => {
                                    try {
                                        console.log("Updating UI after deletion");
                                        
                                        // If we're deleting the currently loaded proposal, reset the form
                                        const currentId = document.getElementById('currentProposalId')?.value;
                                        if (currentId == id) {
                                            document.getElementById('gadProposalForm').reset();
                                        }
                                        
                                        // Refresh the proposals list
                                        loadProposals();
                                        
                                        console.log("UI update completed successfully");
                                    } catch (error) {
                                        console.error("Error updating UI after deletion:", error);
                                    }
                                }, 500);
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: data.message || 'Failed to delete proposal',
                                    icon: 'error',
                                    backdrop: false
                                });
                            }
                        }, 300);
                    })
                    .catch(error => {
                        console.error('Error in delete request:', error);
                        Swal.close();
                        
                        setTimeout(() => {
                            Swal.fire({
                                title: 'Error',
                                text: 'An error occurred while deleting. Please try again.',
                                icon: 'error',
                                backdrop: false
                            });
                        }, 300);
                    });
                }, 300);
            }, 300);
        }
    </script>
</body>
</html>