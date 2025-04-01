<?php
session_start();

// Debug session information
error_log("Session data in print_proposal.php: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in - redirecting to login");
    header("Location: ../login.php");
    exit();
}

// Check if user is Central or a specific campus user
$isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';

// For non-Central users, their username is their campus
$userCampus = $isCentral ? '' : $_SESSION['username'];

// Store campus in session for consistency
$_SESSION['campus'] = $userCampus;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print GAD Narrative Report - GAD System</title>
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
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
            --proposal-bg: #ffffff;
            --proposal-text: #212529;
            --proposal-border: #000000;
            --proposal-header-bg: #f8f9fa;
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
            --dark-bg: #212529;
            --dark-input: #2b3035;
            --dark-text: #e9ecef;
            --dark-border: #495057;
            --dark-sidebar: #2d2d2d;
            --table-header-bg: #343a40;
            --table-subheader-bg: #2b3035;
            --proposal-bg: #2d2d2d;
            --proposal-text: #ffffff;
            --proposal-border: #404040;
            --proposal-header-bg: #343a40;
        }

        /* Select2 Theme Customization */
        .select2-container--bootstrap-5 .select2-selection {
            background-color: var(--input-bg) !important;
            border-color: var(--border-color) !important;
            color: var(--input-text) !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single {
            height: 38px !important;
            padding: 0.375rem 0.75rem !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: var(--input-text) !important;
            line-height: 1.5 !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            background-color: var(--input-bg) !important;
            border-color: var(--border-color) !important;
        }

        .select2-container--bootstrap-5 .select2-search__field {
            background-color: var(--input-bg) !important;
            color: var(--input-text) !important;
            border-color: var(--border-color) !important;
        }

        .select2-container--bootstrap-5 .select2-results__option {
            color: var(--input-text) !important;
            background-color: var(--input-bg) !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: var(--accent-color) !important;
            color: white !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected=true] {
            background-color: var(--accent-hover) !important;
        }

        .select2-container--bootstrap-5 .select2-results__option[aria-selected=true] {
            background-color: var(--hover-color) !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: var(--input-placeholder) !important;
        }

        /* Dark mode specific Select2 styles */
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
            color: var(--dark-text) !important;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-dropdown {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-search__field {
            background-color: var(--dark-input) !important;
            color: var(--dark-text) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option {
            color: var(--dark-text) !important;
            background-color: var(--dark-input) !important;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: var(--input-placeholder) !important;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
            opacity: 1;
            transition: opacity 0.05s ease-in-out;
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
            scrollbar-width: none;
            -ms-overflow-style: none;
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
        }
        
        /* Proposal container styles */
        .proposal-container {
            max-width: 1200px;
            margin: 30px auto;
            background: var(--proposal-bg);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            font-family: 'Times New Roman', Times, serif;
            border: 1px solid var(--proposal-border);
        }

        /* Table layout styles */
        .proposal-container table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            border: 1px solid var(--proposal-border);
        }

        .proposal-container td,
        .proposal-container th {
            padding: 12px 15px;
            line-height: 1.5;
            border: 1px solid var(--proposal-border);
            color: var(--proposal-text);
        }

        /* Header section styles */
        .header-section {
            margin-bottom: 30px;
        }

        .header-section table {
            margin-bottom: 15px !important;
            border: 1px solid var(--proposal-border) !important;
        }

        .header-section table:first-child td {
            vertical-align: middle;
        }

        /* Main section styles */
        .main-section table {
            border: 1px solid var(--proposal-border) !important;
            margin-bottom: 15px !important;
        }

        .main-section table th {
            background-color: var(--proposal-header-bg);
            font-weight: bold;
            text-align: left;
        }

        /* Checkbox styles */
        .checkbox-container {
            text-align: center;
            padding: 10px 0;
        }

        .checkbox-option {
            display: inline-block;
            margin: 0 20px;
            font-size: 16px;
        }

        /* Title styles */
        .title-row td {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            padding: 15px !important;
        }

        /* Signature section styles */
        .signatures-table td {
            text-align: center;
            padding: 20px 15px !important;
        }

        .signature-name {
            font-weight: bold;
            margin-top: 50px;
        }

        .signature-position {
            font-style: italic;
        }

        /* Print styles */
        @media print {
            @page {
                size: A4 landscape;
                margin: 1.5cm;
            }

            body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }

            .sidebar, .mobile-nav-toggle, .sidebar-backdrop, 
            .btn-group, .card, .page-title {
                display: none !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
            }

            .proposal-container {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                border: 1px solid black !important;
                box-shadow: none !important;
            }

            .proposal-container table {
                page-break-inside: avoid;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }

        /* Proposal dropdown styles */
        #proposalDropdown {
            position: absolute;
            width: 100%;
            z-index: 1000;
            border: 1px solid var(--border-color);
            border-radius: 0.25rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            max-height: 200px;
            overflow-y: auto;
            background-color: var(--card-bg);
        }

        #proposalDropdown .dropdown-item {
            padding: 8px 12px;
            cursor: pointer;
            color: var(--text-primary);
            white-space: normal;
            word-break: break-word;
        }

        #proposalDropdown .dropdown-item:hover {
            background-color: var(--accent-color);
            color: white;
        }

        /* Dark mode adjustments */
        [data-bs-theme="dark"] #proposalDropdown {
            background-color: var(--dark-input);
            border-color: var(--dark-border);
        }

        [data-bs-theme="dark"] #proposalDropdown .dropdown-item {
            color: var(--dark-text);
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
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="formsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-alt me-2"></i> Forms
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../target_forms/target.php">Target Form</a></li>
                        <li><a class="dropdown-item" href="../gbp_forms/gbp.php">GPB Form</a></li>
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
                    <a class="nav-link dropdown-toggle active" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i> Reports
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../gpb_reports/gbp_reports.php">Annual GPB Reports</a></li>
                        <li><a class="dropdown-item" href="../ppas_report/ppas_report.php">Quarterly PPAs Reports</a></li>
                        <li><a class="dropdown-item" href="../ps_atrib/ps.php">PSA Reports</a></li>
                        <li><a class="dropdown-item" href="../ppas_proposal/print_proposal.php">GAD Proposal Reports</a></li>
                        <li><a class="dropdown-item" href="#">Narrative Reports</a></li>
                    </ul>
                </div>
            </nav>
        </div>
        <!-- Bottom controls -->
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
            <h2>Print GAD Narrative Report</h2>
        </div>

        <!-- Report Generation Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="reportForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="campus">Campus</label>
                                <select class="form-control" id="campus" required>
                                    <option value="">Select Campus</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="year">Year</label>
                                <select class="form-control" id="year" required>
                                    <option value="">Select Year</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="proposal">Proposal</label>
                                <div class="position-relative">
                                    <input type="text" 
                                          class="form-control" 
                                          id="proposal" 
                                          placeholder="Search for a proposal..." 
                                          autocomplete="off"
                                          required>
                                    <div id="proposalDropdown" class="dropdown-menu w-100" style="display:none; max-height: 200px; overflow-y: auto;"></div>
                                    <input type="hidden" id="proposal_id">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-primary" id="generate-report">
                                <i class="fas fa-print"></i> Generate Narrative Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Preview -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Narrative Report Preview</h5>
                <div class="float-end">
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" id="print-report">
                        <i class="fas fa-file-pdf me-1"></i> Generate PDF
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="export-word">
                        <i class="fas fa-file-word me-1"></i> Export to Word
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="reportPreview" class="table-responsive">
                    <!-- Proposal content will be loaded here -->
                    <p class="form-text text-center mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Select a campus, year, and proposal to generate the narrative report
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize theme on page load
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
    </script>
    <script>
        $(document).ready(function() {
            // Initialize Dropdowns
            loadCampusOptions();

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Add click event handlers for print and export buttons
            $('#print-report, #generate-report').on('click', function() {
                printReport();
            });

            $('#export-word').on('click', function() {
                exportToWord();
            });

            // Handle form submission
            $('#reportForm').on('submit', function(e) {
                e.preventDefault();
                const selectedProposalId = $('#proposal_id').val();
                console.log('Form submitted. Proposal ID:', selectedProposalId);
                
                if (!selectedProposalId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selection Required',
                        text: 'Please select a proposal first.'
                    });
                    return;
                }
                
                generateReport();
            });

            // Handle proposal search input
            let searchTimeout;
            $('#proposal').on('input', function() {
                const searchTerm = $(this).val();
                const selectedCampus = $('#campus').val();
                const selectedYear = $('#year').val();
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Clear proposal ID when input changes
                $('#proposal_id').val('');
                
                if (!selectedCampus || !selectedYear) {
                    console.log('Campus or year not selected');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selection Required',
                        text: 'Please select both campus and year first.'
                    });
                    return;
                }
                
                if (searchTerm.length < 1) {
                    $('#proposalDropdown').hide().empty();
                    return;
                }
                
                // Set new timeout
                searchTimeout = setTimeout(() => {
                    console.log('Searching for:', searchTerm);
                    $.ajax({
                        url: 'api/get_narratives.php',
                        method: 'GET',
                        data: {
                            search: searchTerm,
                            campus: selectedCampus,
                            year: selectedYear
                        },
                        dataType: 'json',
                        success: function(response) {
                            try {
                                console.log('Search response:', response);
                                const dropdown = $('#proposalDropdown');
                                dropdown.empty();
                                
                                // Make sure response is an object if it's a string
                                if (typeof response === 'string') {
                                    response = JSON.parse(response);
                                }
                                
                                if (response && response.status === 'success' && Array.isArray(response.data) && response.data.length > 0) {
                                    // Store narratives globally
                                    window.narratives = response.data;
                                    
                                    console.log('Found', response.data.length, 'narratives');
                                    
                                    // Add narratives to dropdown
                                    response.data.forEach(function(narrative) {
                                        // Prepare display text with both titles
                                        let reference = '';
                                        if (narrative.ppas_activity_title) {
                                            reference = ' (PPAS: ' + narrative.ppas_activity_title + ')';
                                        } else if (narrative.activity_reference) {
                                            reference = ' (PPAS ID: ' + narrative.activity_reference + ')';
                                        }
                                        
                                        const displayText = narrative.activity_title + reference;
                                            
                                        const item = $('<div class="dropdown-item"></div>')
                                            .text(displayText)
                                            .attr('data-id', narrative.id)
                                            .click(function() {
                                                // Set input value
                                                $('#proposal').val(displayText);
                                                // Set hidden proposal_id
                                                $('#proposal_id').val(narrative.id);
                                                // Hide dropdown
                                                dropdown.hide();
                                                console.log('Selected narrative:', displayText, 'with ID:', narrative.id);
                                            });
                                        
                                        dropdown.append(item);
                                    });
                                    
                                    // Show dropdown
                                    dropdown.show();
                                    console.log('Updated dropdown with', response.data.length, 'options');
                                } else {
                                    console.log('No narratives found - Response data:', JSON.stringify(response));
                                    // Show "no results" message
                                    dropdown.append('<div class="dropdown-item disabled">No narratives found</div>');
                                    dropdown.show();
                                }
                            } catch (error) {
                                console.error('Error processing response:', error);
                                const dropdown = $('#proposalDropdown');
                                dropdown.empty();
                                dropdown.append('<div class="dropdown-item disabled">Error processing response</div>');
                                dropdown.show();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Search error:', error);
                            const dropdown = $('#proposalDropdown');
                            dropdown.empty();
                            dropdown.append('<div class="dropdown-item disabled">Error loading narratives</div>');
                            dropdown.show();
                        }
                    });
                }, 300);
            });

            // Hide dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#proposal, #proposalDropdown').length) {
                    $('#proposalDropdown').hide();
                }
            });

            // Handle campus change
            $('#campus').on('change', function() {
                const selectedCampus = $(this).val();
                if (selectedCampus) {
                    loadYearOptions();
                } else {
                    $('#year').html('<option value="">Select Year</option>').prop('disabled', true);
                    $('#proposal').val(null).trigger('change').prop('disabled', true);
                    $('#proposal_id').val('');
                }
            });

            // Handle year change
            $('#year').on('change', function() {
                const selectedYear = $(this).val();
                if (selectedYear) {
                    $('#proposal').prop('disabled', false).val(null).trigger('change');
                } else {
                    $('#proposal').val(null).trigger('change').prop('disabled', true);
                    $('#proposal_id').val('');
                }
            });

            // Handle dropdown submenu on hover for desktop
            if (window.matchMedia('(min-width: 992px)').matches) {
                $('.dropdown-submenu').hover(
                    function() {
                        $(this).children('.dropdown-menu').stop(true, true).fadeIn(200);
                    },
                    function() {
                        $(this).children('.dropdown-menu').stop(true, true).fadeOut(200);
                    }
                );
            }
        });

        function loadCampusOptions() {
            const campusSelect = $('#campus');
            campusSelect.prop('disabled', true);
            
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const userCampus = "<?php echo $userCampus ?>";
            
            if (isCentral) {
                $.ajax({
                    url: 'api/get_campuses.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        campusSelect.empty().append('<option value="">Select Campus</option>');
                        if (response.status === 'success' && response.data) {
                            response.data.forEach(function(campus) {
                                if (campus.name && campus.name !== 'null' && campus.name !== 'Default Campus') {
                                    campusSelect.append(`<option value="${campus.name}">${campus.name}</option>`);
                                }
                            });
                        }
                        campusSelect.prop('disabled', false);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading campuses:', error);
                        campusSelect.empty().append('<option value="">Error loading campuses</option>');
                    }
                });
            } else {
                campusSelect.empty().append(`<option value="${userCampus}" selected>${userCampus}</option>`);
                campusSelect.prop('disabled', true);
                loadYearOptions();
            }
        }

        function loadYearOptions() {
            const yearSelect = $('#year');
            const selectedCampus = $('#campus').val();
            
            yearSelect.prop('disabled', true);
            yearSelect.html('<option value="">Loading years...</option>');
            
            $.ajax({
                url: 'api/get_narrative_years.php',
                method: 'GET',
                data: { campus: selectedCampus },
                dataType: 'json',
                success: function(response) {
                    console.log('Year response:', response);
                    yearSelect.empty().append('<option value="">Select Year</option>');
                    
                    if (response.status === 'error') {
                        console.error('API Error:', response.message);
                        yearSelect.html(`<option value="">${response.message || 'Error loading years'}</option>`);
                        
                        // Show error to user
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Loading Years',
                            text: response.message || 'Failed to load year data. Please ensure you are logged in.',
                            confirmButtonColor: '#6c757d'
                        });
                        return;
                    }
                    
                    if (response.status === 'success' && response.data && response.data.length > 0) {
                        response.data.sort((a, b) => b.year - a.year).forEach(function(yearData) {
                            yearSelect.append(`<option value="${yearData.year}">${yearData.year}</option>`);
                        });
                        yearSelect.prop('disabled', false);
                    } else {
                        yearSelect.html('<option value="">No years available</option>');
                        
                        // Optional: Display friendly message about no data
                        Swal.fire({
                            icon: 'info',
                            title: 'No Data Available',
                            text: 'No narrative reports found for this campus. You may need to create narrative reports first.',
                            confirmButtonColor: '#6c757d'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading years:', error);
                    console.error('Response:', xhr.responseText);
                    
                    let errorMessage = 'Failed to load years. Please try again.';
                    
                    // Try to parse error message from response if possible
                    try {
                        const responseJson = JSON.parse(xhr.responseText);
                        if (responseJson && responseJson.message) {
                            errorMessage = responseJson.message;
                        }
                    } catch (e) {
                        // Handle case where response is not JSON
                        if (xhr.status === 500) {
                            errorMessage = 'Server error. Please check database connection or contact administrator.';
                        } else if (xhr.status === 404) {
                            errorMessage = 'API endpoint not found. Please check system configuration.';
                        } else if (xhr.status === 0) {
                            errorMessage = 'Network error. Please check your connection.';
                        }
                    }
                    
                    yearSelect.html(`<option value="">Error: ${errorMessage}</option>`);
                    
                    // Show error to user
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Loading Years',
                        text: errorMessage,
                        footer: 'Status Code: ' + xhr.status,
                        confirmButtonColor: '#6c757d'
                    });
                }
            });
        }

        function generateReport() {
            const selectedCampus = $('#campus').val();
            const selectedYear = $('#year').val();
            const selectedNarrative = $('#proposal_id').val();

            if (!selectedCampus || !selectedYear || !selectedNarrative) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select all required fields to generate the narrative report.'
                });
                return;
            }

            // Show loading state
            $('#reportPreview').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading narrative report...</p>
                </div>
            `);

            $.ajax({
                url: 'api/get_narrative_details.php',
                method: 'GET',
                data: {
                    campus: selectedCampus,
                    year: selectedYear,
                    narrative_id: selectedNarrative
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        displayNarrative(response.data);
                    } else {
                        $('#reportPreview').html(`
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                                <p>${response.message || 'Failed to load narrative report data'}</p>
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading narrative report:', error);
                    $('#reportPreview').html(`
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <p>Error loading narrative report. Please try again.</p>
                        </div>
                    `);
                }
            });
        }

        function displayNarrative(narrative) {
            console.log("Displaying narrative data:", narrative);
            let html = '<div class="proposal-container">';
            
            // Header Section with Reference, Effectivity, and Revision
            html += `<table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                <tr>
                    <td style="width: 33.33%; border: 0.5pt solid black; padding: 5px;">Reference No.: BatStateU-FO-ESO-09</td>
                    <td style="width: 33.33%; border: 0.5pt solid black; padding: 5px;">Effectivity Date: August 25, 2023</td>
                    <td style="width: 33.33%; border: 0.5pt solid black; padding: 5px;">Revision No.: 00</td>
                </tr>
            </table>`;

            // Title and Checkbox Section
            html += `<div style="text-align: center; margin: 15px 0; border-bottom: 0.5pt solid black; padding-bottom: 10px;">
                <div style="font-weight: bold; margin-bottom: 10px;">GAD NARRATIVE REPORT (INTERNAL PROGRAM/PROJECT/ACTIVITY)</div>
                <div style="margin-top: 10px;">
                    ☐ Program   ☐ Project  ☒ Activity
                </div>
            </div>`;

            // Main Content Table
            html += '<table style="width: 100%; border-collapse: collapse;">';

            // Title Section
            html += `<tr>
                <td style="border: 0.5pt solid black; padding: 5px;">I. Title:</td>
                <td style="border: 0.5pt solid black; padding: 5px;">"${narrative.activity_title || ''}"${
                    narrative.ppas_activity_title ? 
                        '<br><br><strong>Related PPAS Activity:</strong> ' + narrative.ppas_activity_title : 
                    (narrative.activity_reference ? 
                        '<br><br><strong>Related PPAS Activity ID:</strong> ' + narrative.activity_reference : 
                        '')
                }</td>
            </tr>`;

            // Date and Venue Section
            html += `<tr>
                <td style="border: 0.5pt solid black; padding: 5px;">II. Date and Venue:</td>
                <td style="border: 0.5pt solid black; padding: 5px;">${narrative.venue || ''}</td>
            </tr>`;

            // Mode of Delivery
            html += `<tr>
                <td style="border: 0.5pt solid black; padding: 5px;">III. Mode of delivery (online/face-to-face):</td>
                <td style="border: 0.5pt solid black; padding: 5px;">${narrative.mode_of_delivery || ''}</td>
            </tr>`;

            // Project Team Section - process JSON fields if they exist
            const projectLeaderResp = Array.isArray(narrative.project_leader_responsibilities) ? 
                narrative.project_leader_responsibilities : 
                (typeof narrative.project_leader_responsibilities === 'string' ? 
                    JSON.parse(narrative.project_leader_responsibilities || '[]') : []);
            
            const asstProjectLeaderResp = Array.isArray(narrative.asst_project_leader_responsibilities) ? 
                narrative.asst_project_leader_responsibilities : 
                (typeof narrative.asst_project_leader_responsibilities === 'string' ? 
                    JSON.parse(narrative.asst_project_leader_responsibilities || '[]') : []);
            
            const projectStaff = Array.isArray(narrative.project_staff) ? 
                narrative.project_staff : 
                (typeof narrative.project_staff === 'string' ? 
                    JSON.parse(narrative.project_staff || '[]') : []);
            
            const projectStaffResp = Array.isArray(narrative.project_staff_responsibilities) ? 
                narrative.project_staff_responsibilities : 
                (typeof narrative.project_staff_responsibilities === 'string' ? 
                    JSON.parse(narrative.project_staff_responsibilities || '[]') : []);

            html += `<tr>
                <td style="border: 0.5pt solid black; padding: 5px; vertical-align: top;">IV. Project Team:</td>
                <td style="border: 0.5pt solid black; padding: 5px;">
                    <strong>Project Leaders:</strong> ${narrative.project_leader || ''}<br>
                    <strong>Responsibilities:</strong><br>
                    ${projectLeaderResp.map(resp => `${resp}<br>`).join('')}
                    
                    <br><strong>Asst. Project Leaders:</strong> ${narrative.asst_project_leader || ''}<br>
                    <strong>Responsibilities:</strong><br>
                    ${asstProjectLeaderResp.map(resp => `${resp}<br>`).join('')}
                    
                    <br><strong>Project Staff:</strong><br>
                    ${projectStaff.map(staff => `${staff}<br>`).join('')}
                    <br><strong>Responsibilities:</strong><br>
                    ${projectStaffResp.map(resp => `${resp}<br>`).join('')}
                </td>
            </tr>`;

            // Partner Office Section
            html += `<tr>
                <td style="border: 0.5pt solid black; padding: 5px;">V. Partner Office/College/Department:</td>
                <td style="border: 0.5pt solid black; padding: 5px;">${narrative.implementing_office || ''}</td>
            </tr>`;

            // Participants Section with Table
            html += `<tr>
                <td style="border: 0.5pt solid black; padding: 5px; vertical-align: top;">VI. Type of Participants:</td>
                <td style="border: 0.5pt solid black; padding: 5px;">
                    ${narrative.participant_type || ''}
                    <table style="width: 50%; border-collapse: collapse; margin-top: 10px;">
                        <tr>
                            <td style="border: 0.5pt solid black; padding: 5px;">Male</td>
                            <td style="border: 0.5pt solid black; padding: 5px; text-align: center;">${narrative.male_count || 0}</td>
                        </tr>
                        <tr>
                            <td style="border: 0.5pt solid black; padding: 5px;">Female</td>
                            <td style="border: 0.5pt solid black; padding: 5px; text-align: center;">${narrative.female_count || 0}</td>
                        </tr>
                        <tr>
                            <td style="border: 0.5pt solid black; padding: 5px;">Total</td>
                            <td style="border: 0.5pt solid black; padding: 5px; text-align: center;">${(parseInt(narrative.male_count) || 0) + (parseInt(narrative.female_count) || 0)}</td>
                        </tr>
                    </table>
                </td>
            </tr>`;

            // Rationale Section
            html += `<tr>
                <td style="border: 0.5pt solid black; padding: 5px;">VII. Rationale/Background:</td>
                <td style="border: 0.5pt solid black; padding: 5px;">${narrative.rationale || ''}</td>
            </tr>`;

            // Objectives Section - process specific_objectives JSON field
            const specificObjectives = Array.isArray(narrative.specific_objectives) ? 
                narrative.specific_objectives : 
                (typeof narrative.specific_objectives === 'string' ? 
                    JSON.parse(narrative.specific_objectives || '[]') : []);

            html += `<tr>
                <td style="border: 0.5pt solid black; padding: 5px; vertical-align: top;">VIII. Objectives:</td>
                <td style="border: 0.5pt solid black; padding: 5px;">
                    ${narrative.general_objective || ''}<br><br>
                    <strong>Specific Objectives:</strong><br>
                    The specific objectives of this project include:<br>
                    <ul style="margin: 5px 0 5px 20px; padding: 0;">`;
            specificObjectives.forEach(objective => {
                html += `<li>${objective}</li>`;
            });
            html += `</ul></td>
            </tr>`;

            // Description and Strategies Section - process strategies and activities JSON fields
            const strategies = Array.isArray(narrative.strategies) ? 
                narrative.strategies : 
                (typeof narrative.strategies === 'string' ? 
                    JSON.parse(narrative.strategies || '[]') : []);
            
            const activities = Array.isArray(narrative.activities) ? 
                narrative.activities : 
                (typeof narrative.activities === 'string' ? 
                    JSON.parse(narrative.activities || '[]') : []);

            html += `<tr>
                <td style="border: 0.5pt solid black; padding: 5px; vertical-align: top;">IX. Description, Strategies, and Methods (Activities / Schedule):</td>
                <td style="border: 0.5pt solid black; padding: 5px;">
                    ${narrative.description || ''}<br><br>
                    <strong>Strategies:</strong><br>
                    <ul style="margin: 5px 0 5px 20px; padding: 0;">`;
            strategies.forEach(strategy => {
                html += `<li>${strategy}</li>`;
            });
            html += `</ul><br>
                    <strong>Methods (Activities / Schedule):</strong><br>`;
            activities.forEach(activity => {
                if (typeof activity === 'object') {
                    html += `<strong>${activity.title || ''}</strong><br>${activity.description || ''}<br><br>`;
                } else {
                    html += `${activity}<br><br>`;
                }
            });
            html += `</td></tr>`;

            // Work Plan Section - process workplan JSON field
            const workplan = Array.isArray(narrative.workplan) ? 
                narrative.workplan : 
                (typeof narrative.workplan === 'string' ? 
                    JSON.parse(narrative.workplan || '[]') : []);

            html += `<tr>
                <td style="border: 0.5pt solid black; padding: 5px; vertical-align: top;">X. Work Plan (Timeline of Activities/Gantt Chart):</td>
                <td style="border: 0.5pt solid black; padding: 5px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="border: 0.5pt solid black; padding: 5px;">Activities</td>
                            <td style="border: 0.5pt solid black; padding: 5px; text-align: center;">1</td>
                            <td style="border: 0.5pt solid black; padding: 5px; text-align: center;">2</td>
                            <td style="border: 0.5pt solid black; padding: 5px; text-align: center;">3</td>
                            <td style="border: 0.5pt solid black; padding: 5px; text-align: center;">4</td>
                        </tr>`;
            workplan.forEach(item => {
                if (typeof item === 'object') {
                    html += `<tr>
                        <td style="border: 0.5pt solid black; padding: 5px;">${item.activity || ''}</td>`;
                    const timeline = Array.isArray(item.timeline) ? item.timeline : [];
                    for (let i = 0; i < 4; i++) {
                        html += `<td style="border: 0.5pt solid black; padding: 5px; text-align: center;">${timeline[i] ? '✓' : ''}</td>`;
                    }
                    html += `</tr>`;
                }
            });
            html += `</table></td></tr>`;

            // Financial Requirements Section
            html += `<tr>
                <td style="border: 0.5pt solid black; padding: 5px;">XI. Financial Requirements and Source of Funds:</td>
                <td style="border: 0.5pt solid black; padding: 5px;">${narrative.financial_requirements || ''}</td>
            </tr>`;

            // Monitoring and Evaluation Section - process monitoring_plan JSON field
            const monitoringPlan = Array.isArray(narrative.monitoring_plan) ? 
                narrative.monitoring_plan : 
                (typeof narrative.monitoring_plan === 'string' ? 
                    JSON.parse(narrative.monitoring_plan || '[]') : []);

            html += `<tr>
                <td style="border: 0.5pt solid black; padding: 5px;">XII. Monitoring and Evaluation Mechanics / Plan:</td>
                <td style="border: 0.5pt solid black; padding: 5px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="border: 0.5pt solid black; padding: 5px;">Objectives</td>
                            <td style="border: 0.5pt solid black; padding: 5px;">Performance Indicators</td>
                            <td style="border: 0.5pt solid black; padding: 5px;">Baseline Data</td>
                            <td style="border: 0.5pt solid black; padding: 5px;">Performance Target</td>
                            <td style="border: 0.5pt solid black; padding: 5px;">Data Source</td>
                            <td style="border: 0.5pt solid black; padding: 5px;">Collection Method</td>
                            <td style="border: 0.5pt solid black; padding: 5px;">Frequency of Data Collection</td>
                            <td style="border: 0.5pt solid black; padding: 5px;">Office/Persons Responsible</td>
                        </tr>`;
            monitoringPlan.forEach(item => {
                if (typeof item === 'object') {
                    html += `<tr>`;
                    // Safely access properties
                    const fields = ['objective', 'indicator', 'baseline', 'target', 'source', 'method', 'frequency', 'responsible'];
                    fields.forEach(field => {
                        html += `<td style="border: 0.5pt solid black; padding: 5px;">${item[field] || ''}</td>`;
                    });
                    html += `</tr>`;
                }
            });
            html += `</table></td></tr>`;

            // Sustainability Plan Section
            html += `<tr>
                <td style="border: 0.5pt solid black; padding: 5px;">XIII. Sustainability Plan:</td>
                <td style="border: 0.5pt solid black; padding: 5px;">${narrative.sustainability_plan || ''}</td>
            </tr>`;

            // Signature Section
            html += `</table><br>`;
            html += `<table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 25%; padding: 15px; text-align: center; vertical-align: bottom;">
                        <p>Prepared by:</p><br><br>
                        <div style="border-bottom: 0.5pt solid black; margin: 0 auto; width: 80%;"></div>
                        <p style="margin: 5px 0;"><strong>${narrative.prepared_by_name || ''}</strong></p>
                        <p style="margin: 0;">${narrative.prepared_by_position || ''}</p>
                    </td>
                    <td style="width: 25%; padding: 15px; text-align: center; vertical-align: bottom;">
                        <p>Reviewed by:</p><br><br>
                        <div style="border-bottom: 0.5pt solid black; margin: 0 auto; width: 80%;"></div>
                        <p style="margin: 5px 0;"><strong>${narrative.reviewed_by_name || ''}</strong></p>
                        <p style="margin: 0;">${narrative.reviewed_by_position || ''}</p>
                    </td>
                    <td style="width: 25%; padding: 15px; text-align: center; vertical-align: bottom;">
                        <p>Recommending Approval:</p><br><br>
                        <div style="border-bottom: 0.5pt solid black; margin: 0 auto; width: 80%;"></div>
                        <p style="margin: 5px 0;"><strong>${narrative.recommending_approval_name || ''}</strong></p>
                        <p style="margin: 0;">${narrative.recommending_approval_position || ''}</p>
                    </td>
                    <td style="width: 25%; padding: 15px; text-align: center; vertical-align: bottom;">
                        <p>Approved by:</p><br><br>
                        <div style="border-bottom: 0.5pt solid black; margin: 0 auto; width: 80%;"></div>
                        <p style="margin: 5px 0;"><strong>${narrative.approved_by_name || ''}</strong></p>
                        <p style="margin: 0;">${narrative.approved_by_position || ''}</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding: 15px 0 0 0;">
                        <p>Date Signed: _________________</p>
                        <p style="margin-top: 15px;">Cc: GAD Central</p>
                    </td>
                </tr>
            </table>`;

            html += `</div>`;
            
            // Update the preview content
            $('#reportPreview').html(html);
        }

        function printReport() {
            const selectedCampus = $('#campus').val();
            const selectedYear = $('#year').val();
            const selectedNarrative = $('#proposal_id').val();

            if (!selectedCampus || !selectedYear || !selectedNarrative) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select all required fields to generate the narrative report.'
                });
                return;
            }

            // Show loading indicator
            Swal.fire({
                title: 'Generating PDF',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // AJAX call to generate PDF
            $.ajax({
                url: 'api/generate_narrative_pdf.php',
                type: 'POST',
                data: {
                    campus: selectedCampus,
                    year: selectedYear,
                    narrative_id: selectedNarrative
                },
                xhrFields: {
                    responseType: 'blob' // Important: Set the response type to blob
                },
                success: function(response) {
                    // Close the loading dialog
                    Swal.close();
                    
                    // Create a blob URL from the PDF response
                    const blob = new Blob([response], { type: 'application/pdf' });
                    const url = URL.createObjectURL(blob);
                    
                    // Open the PDF in a new window
                    const pdfWindow = window.open(url, '_blank');
                    
                    if (!pdfWindow) {
                        // If popup was blocked, offer direct download
                        Swal.fire({
                            icon: 'warning',
                            title: 'Popup Blocked',
                            text: 'Your browser blocked the PDF preview. Click below to download the PDF.',
                            confirmButtonText: 'Download PDF',
                            showCancelButton: true,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Create a temporary link and trigger download
                                const link = document.createElement('a');
                                link.href = url;
                                link.download = `Narrative_Report_${selectedCampus}_${selectedYear}.pdf`;
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                            }
                            // Clean up the blob URL
                            URL.revokeObjectURL(url);
                        });
                    } else {
                        // If the window was opened successfully, revoke the URL when it closes
                        pdfWindow.onload = function() {
                            // After a short delay to ensure PDF is loaded
                            setTimeout(() => {
                                pdfWindow.print(); // Automatically open print dialog
                            }, 1000);
                        };
                        
                        // Set up a timer to revoke the URL after some time
                        setTimeout(() => {
                            URL.revokeObjectURL(url);
                        }, 60000); // Clean up after 1 minute
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error generating PDF:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'PDF Generation Failed',
                        text: 'Failed to generate the Narrative Report PDF. Please try again.'
                    });
                }
            });
        }

        function exportToWord() {
            const selectedCampus = $('#campus').val();
            const selectedYear = $('#year').val();
            const selectedNarrative = $('#proposal_id').val();

            if (!selectedCampus || !selectedYear || !selectedNarrative) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select all required fields to generate the narrative report.'
                });
                return;
            }

            // Show loading state
            Swal.fire({
                title: 'Exporting to Word',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const content = $('#reportPreview').clone();
                
                // Create Word document HTML with mso tags for better Word compatibility
                const html = `
                    <html xmlns:o='urn:schemas-microsoft-com:office:office' 
                          xmlns:w='urn:schemas-microsoft-com:office:word' 
                          xmlns='http://www.w3.org/TR/REC-html40'>
                    <head>
                        <meta charset='utf-8'>
                        <title>GAD Narrative Report</title>
                        <style>
                            /* Word-specific styles */
                            @page Section1 {
                                size: A4 landscape;
                                margin: 1.0in;
                                mso-page-orientation: landscape;
                            }
                            div.Section1 { page:Section1; }
                            
                            /* Document styles */
                            body {
                                font-family: 'Times New Roman', Times, serif;
                                font-size: 12pt;
                                line-height: 1.15;
                                margin: 0;
                                padding: 0;
                            }
                            
                            .proposal-container {
                                width: 100%;
                                border-collapse: collapse;
                            }
                            
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                border: 0.5pt solid black;
                            }
                            
                            td, th {
                                border: 0.5pt solid black;
                                padding: 5pt;
                                vertical-align: top;
                            }
                            
                            /* Preserve whitespace */
                            p {
                                margin: 0;
                                padding: 0;
                            }

                            /* Ensure proper page setup */
                            @page Section1 {
                                size: A4 landscape;
                                margin: 1.0in;
                                mso-page-orientation: landscape;
                            }
                            div.Section1 { page:Section1; }
                        </style>
                    </head>
                    <body>
                        <div class="Section1">
                            ${content.html()}
                        </div>
                    </body>
                    </html>
                `;

                // Create blob and download
                const blob = new Blob([html], { type: 'application/msword' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `Narrative_Report_${selectedCampus}_${selectedYear}.doc`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);

                Swal.close();
            } catch (error) {
                console.error('Word export error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Export Failed',
                    text: 'Failed to export to Word. Please try again.'
                });
            }
        }

        // Update date and time in the sidebar
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('current-date').innerText = now.toLocaleDateString('en-US', options);
            
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            document.getElementById('current-time').innerText = now.toLocaleTimeString('en-US', timeOptions);
        }
        
        // Update date and time every second
        updateDateTime();
        setInterval(updateDateTime, 1000);
        
        // Handle logout with confirmation
        function handleLogout(event) {
            event.preventDefault();
            
            Swal.fire({
                title: 'Logout Confirmation',
                text: 'Are you sure you want to logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../logout.php';
                }
            });
        }
        
        // Toggle theme function
        function toggleTheme() {
            const html = document.documentElement;
            const themeIcon = document.getElementById('theme-icon');
            
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.body.classList.add('fade-out');
            
            setTimeout(() => {
                html.setAttribute('data-bs-theme', newTheme);
                themeIcon.className = newTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
                localStorage.setItem('theme', newTheme);
                document.body.classList.remove('fade-out');
            }, 50);
        }
        
        // Mobile navigation toggle
        $(document).ready(function() {
            $('.mobile-nav-toggle').on('click', function() {
                $('.sidebar').toggleClass('show');
                $('.sidebar-backdrop').toggleClass('show');
                $('body').toggleClass('sidebar-open');
            });
            
            $('.sidebar-backdrop').on('click', function() {
                $('.sidebar').removeClass('show');
                $('.sidebar-backdrop').removeClass('show');
                $('body').removeClass('sidebar-open');
            });
        });
    </script>
</body>
</html> 