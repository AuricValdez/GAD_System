<?php
session_start();

// Debug session information
error_log("Session data in narrative.php: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in - redirecting to login");
    header("Location: ../login.php");
    exit();
}

$isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Narrative Form - GAD System</title>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            width: 45px;
            height: 45px;
            padding: 0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-icon i {
            font-size: 1.2rem;
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

/* Add these styles for the beneficiaries table */
.table input.form-control {
    min-width: 80px;
    width: 100%;
}

/* Make the first column (Type of Participants) wider */
#beneficiariesTable td:first-child input {
    min-width: 150px;
}

/* Make the total row text more visible in dark mode */
[data-bs-theme="dark"] .table-secondary input.form-control {
    color: #212529 !important;
    font-weight: bold;
}

/* Ensure table styles for evaluation section */
.evaluation-table {
    width: 100%;
    margin-bottom: 1rem;
}

.evaluation-table th, 
.evaluation-table td {
    border: 1px solid var(--border-color);
    padding: 0.5rem;
}

.evaluation-table th {
    background-color: rgba(0, 0, 0, 0.05);
}

/* Make total row text darker in dark mode */
.table-dark .total-row {
    color: #212529 !important;
    font-weight: bold;
}

/* Styling for evaluation tables */
.evaluation-table {
    width: 100%;
    margin-bottom: 1rem;
    border-collapse: collapse;
}

.evaluation-table th, 
.evaluation-table td {
    padding: 0.5rem;
    border: 1px solid #dee2e6;
    text-align: center;
}

.evaluation-table th {
    background-color: #f8f9fa;
    vertical-align: middle;
}

.evaluation-table td:first-child {
    text-align: left;
    width: 200px;
}

/* Form control styling */
.beneficiaries-table .form-control,
.evaluation-table .form-control {
    min-width: 80px;
    width: 100%;
}

.beneficiaries-table td:first-child {
    min-width: 150px;
}

/* Add these styles to the existing style section */
.photo-preview-container {
    position: relative;
    height: 120px;
    overflow: hidden;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.photo-preview {
    max-height: 100%;
    object-fit: cover;
    width: 100%;
}

.card {
    transition: all 0.2s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Fix text color for dark mode in all tables */
[data-bs-theme="dark"] table input.form-control,
[data-bs-theme="dark"] .evaluation-table input.form-control,
[data-bs-theme="dark"] #beneficiariesTable input.form-control {
    color: #dee2e6 !important; /* Slightly darker light color for better readability */
}

/* Make the table headers and labels more visible in dark mode */
[data-bs-theme="dark"] .evaluation-table th,
[data-bs-theme="dark"] .evaluation-table td:first-child {
    color: #dee2e6 !important;
    background-color: #343a40 !important;
}

/* Total row styling for dark mode */
[data-bs-theme="dark"] .table-secondary input.form-control,
[data-bs-theme="dark"] tr.table-secondary input.form-control {
    color: #212529 !important; /* Dark color on lighter background */
    background-color: #adb5bd !important;
    font-weight: bold;
}

/* Fix evaluation table background in dark mode */
[data-bs-theme="dark"] .evaluation-table {
    background-color: #343a40;
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
                                <li><a class="dropdown-item" href="../ppas_proposal/gad_proposal.php">GAD Proposal Form</a></li>
                                <li><a class="dropdown-item" href="#">Narrative Form</a></li>
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
            <h2>Narrative Form</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Activity Narrative Report</h5>
            </div>
            <div class="card-body">
                <form id="narrativeForm">
                    <!-- Hidden field for narrative ID -->
                    <input type="hidden" id="narrative_id" name="narrative_id">
                    
                    <!-- Auto-fetched fields from PPAS -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> The Title, Location, and Duration will be auto-fetched from the selected PPAS form.
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="ppas_select" class="form-label">Select PPAS Form</label>
                            <select class="form-select" id="ppas_select" required>
                                <option value="" selected disabled>Select a PPAS form</option>
                                <!-- Options will be populated from database -->
                            </select>
                        </div>
                    </div>

                    <!-- Activity Details Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">Activity Details</h5>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" readonly>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" readonly>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="duration" class="form-label">Duration</label>
                            <input type="text" class="form-control" id="duration" name="duration" readonly>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="implementing_office" class="form-label">Implementing Office</label>
                            <input type="text" class="form-control" id="implementing_office" name="implementing_office" required>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="partner_agency" class="form-label">Partner Agency</label>
                            <input type="text" class="form-control" id="partner_agency" name="partner_agency">
                        </div>
                    </div>

                    <!-- Extension Service Agenda Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">Type of Extension Service Agenda</h5>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Select</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Education and Training</td>
                                            <td><input type="checkbox" class="form-check-input" name="service_agenda[]" value="Education and Training"></td>
                                        </tr>
                                        <tr>
                                            <td>Technical Assistance</td>
                                            <td><input type="checkbox" class="form-check-input" name="service_agenda[]" value="Technical Assistance"></td>
                                        </tr>
                                        <tr>
                                            <td>Advisory Services</td>
                                            <td><input type="checkbox" class="form-check-input" name="service_agenda[]" value="Advisory Services"></td>
                                        </tr>
                                        <tr>
                                            <td>Information Services</td>
                                            <td><input type="checkbox" class="form-check-input" name="service_agenda[]" value="Information Services"></td>
                                        </tr>
                                        <tr>
                                            <td>Community Outreach</td>
                                            <td><input type="checkbox" class="form-check-input" name="service_agenda[]" value="Community Outreach"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Sustainable Development Goals Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">Sustainable Development Goals</h5>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>SDG</th>
                                            <th>Select</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1. No Poverty</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="No Poverty"></td>
                                        </tr>
                                        <tr>
                                            <td>2. Zero Hunger</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Zero Hunger"></td>
                                        </tr>
                                        <tr>
                                            <td>3. Good Health and Well-being</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Good Health and Well-being"></td>
                                        </tr>
                                        <tr>
                                            <td>4. Quality Education</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Quality Education"></td>
                                        </tr>
                                        <tr>
                                            <td>5. Gender Equality</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Gender Equality"></td>
                                        </tr>
                                        <tr>
                                            <td>6. Clean Water and Sanitation</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Clean Water and Sanitation"></td>
                                        </tr>
                                        <tr>
                                            <td>7. Affordable and Clean Energy</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Affordable and Clean Energy"></td>
                                        </tr>
                                        <tr>
                                            <td>8. Decent Work and Economic Growth</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Decent Work and Economic Growth"></td>
                                        </tr>
                                        <tr>
                                            <td>9. Industry, Innovation and Infrastructure</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Industry, Innovation and Infrastructure"></td>
                                        </tr>
                                        <tr>
                                            <td>10. Reduced Inequality</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Reduced Inequality"></td>
                                        </tr>
                                        <tr>
                                            <td>11. Sustainable Cities and Communities</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Sustainable Cities and Communities"></td>
                                        </tr>
                                        <tr>
                                            <td>12. Responsible Consumption and Production</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Responsible Consumption and Production"></td>
                                        </tr>
                                        <tr>
                                            <td>13. Climate Action</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Climate Action"></td>
                                        </tr>
                                        <tr>
                                            <td>14. Life Below Water</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Life Below Water"></td>
                                        </tr>
                                        <tr>
                                            <td>15. Life on Land</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Life on Land"></td>
                                        </tr>
                                        <tr>
                                            <td>16. Peace, Justice and Strong Institutions</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Peace, Justice and Strong Institutions"></td>
                                        </tr>
                                        <tr>
                                            <td>17. Partnerships for the Goals</td>
                                            <td><input type="checkbox" class="form-check-input" name="sdg[]" value="Partnerships for the Goals"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Beneficiaries Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">Number of Beneficiaries</h5>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> The beneficiary numbers are auto-fetched from the selected PPAS form.
                            </div>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">Type of Participants</th>
                                            <th colspan="2">Internal</th>
                                            <th colspan="2">External</th>
                                            <th colspan="2">Total</th>
                                        </tr>
                                        <tr>
                                            <th>Male</th>
                                            <th>Female</th>
                                            <th>Male</th>
                                            <th>Female</th>
                                            <th>Male</th>
                                            <th>Female</th>
                                        </tr>
                                    </thead>
                                    <tbody id="beneficiariesTable">
                                        <!-- Will be populated automatically from PPAS form -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Project Team Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">Project Team</h5>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> Project leader, assistant project leader, and project staff will be auto-fetched from the selected PPAS form.
                            </div>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Role</th>
                                            <th>Name</th>
                                        </tr>
                                    </thead>
                                    <tbody id="projectTeamTable">
                                        <!-- Will be populated from PPAS form -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Task Assignment Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">Task Assignment</h5>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Task</th>
                                        </tr>
                                    </thead>
                                    <tbody id="taskAssignmentTable">
                                        <tr>
                                            <td>
                                                <input type="text" class="form-control" name="task_name[]" placeholder="Name">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="task_description[]" placeholder="Task description">
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2">
                                                <button type="button" class="btn btn-sm btn-primary" id="addTaskRow">
                                                    <i class="fas fa-plus"></i> Add Row
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Objectives Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">Objectives</h5>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="general_objective" class="form-label">General Objective</label>
                            <textarea class="form-control" id="general_objective" name="general_objective" rows="3" required></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="specific_objective" class="form-label">Specific Objectives</label>
                            <textarea class="form-control" id="specific_objective" name="specific_objective" rows="5" required></textarea>
                        </div>
                    </div>

                    <!-- Activity Details Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">Activity Narrative</h5>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="activity_title" class="form-label">Activity Title</label>
                            <input type="text" class="form-control" id="activity_title" name="activity_title" required>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="activity_narrative" class="form-label">Narrative of the Activity</label>
                            <textarea class="form-control" id="activity_narrative" name="activity_narrative" rows="8" required></textarea>
                        </div>
                    </div>

                    <!-- Evaluation Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">Evaluation</h5>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="evaluation_result" class="form-label">Evaluation Results <em>(if activity is training, technical advice, or seminar)</em></label>
                            
                            <div class="table-responsive mb-3">
                                <p>1. Number of beneficiaries/participants who rated the activity as:</p>
                                <table class="evaluation-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">Scale</th>
                                            <th>BatStateU Participants</th>
                                            <th>Participants from other Institutions</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1.1. Excellent</td>
                                            <td><input type="number" class="form-control" name="excellent_batstateu" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="excellent_other" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="excellent_total" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>1.2. Very Satisfactory</td>
                                            <td><input type="number" class="form-control" name="very_satisfactory_batstateu" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="very_satisfactory_other" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="very_satisfactory_total" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>1.3. Satisfactory</td>
                                            <td><input type="number" class="form-control" name="satisfactory_batstateu" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="satisfactory_other" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="satisfactory_total" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>1.4. Fair</td>
                                            <td><input type="number" class="form-control" name="fair_batstateu" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="fair_other" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="fair_total" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>1.5. Poor</td>
                                            <td><input type="number" class="form-control" name="poor_batstateu" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="poor_other" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="poor_total" readonly></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="table-responsive">
                                <p>2. Number of beneficiaries/participants who rated the timeliness of the activity as:</p>
                                <table class="evaluation-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">Scale</th>
                                            <th>BatStateU Participants</th>
                                            <th>Participants from other Institutions</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>2.1. Excellent</td>
                                            <td><input type="number" class="form-control" name="timeliness_excellent_batstateu" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="timeliness_excellent_other" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="timeliness_excellent_total" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>2.2. Very Satisfactory</td>
                                            <td><input type="number" class="form-control" name="timeliness_very_satisfactory_batstateu" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="timeliness_very_satisfactory_other" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="timeliness_very_satisfactory_total" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>2.3. Satisfactory</td>
                                            <td><input type="number" class="form-control" name="timeliness_satisfactory_batstateu" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="timeliness_satisfactory_other" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="timeliness_satisfactory_total" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>2.4. Fair</td>
                                            <td><input type="number" class="form-control" name="timeliness_fair_batstateu" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="timeliness_fair_other" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="timeliness_fair_total" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>2.5. Poor</td>
                                            <td><input type="number" class="form-control" name="timeliness_poor_batstateu" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="timeliness_poor_other" min="0" value="0"></td>
                                            <td><input type="number" class="form-control" name="timeliness_poor_total" readonly></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <textarea class="form-control mt-3" id="evaluation_result" name="evaluation_result" rows="5" placeholder="Additional evaluation comments or results" required></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="survey_result" class="form-label">Survey Results</label>
                            <textarea class="form-control" id="survey_result" name="survey_result" rows="5" required></textarea>
                        </div>
                    </div>

                    <!-- Photos Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">Photos</h5>
                            <p class="text-muted mb-3">Upload at least 6 photos of the activity</p>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <div class="photo-upload-container mb-3">
                                <div class="row" id="photoPreviewsContainer">
                                    <!-- Photo previews will appear here -->
                            </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="photo_upload" class="btn btn-outline-primary">
                                        <i class="fas fa-upload"></i> Upload Photos
                                    </label>
                                    <span id="photoCount" class="text-muted">0/6 photos uploaded</span>
                                </div>
                                <input type="file" class="d-none" id="photo_upload" name="photos[]" accept="image/*" multiple>
                                <div class="invalid-feedback">Please upload at least 6 photos.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 text-end mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn-icon" id="viewBtn" onclick="window.location.href='print_narrative.php'">
                                <i class="fas fa-print"></i>
                            </button>
                            <div class="d-inline-flex gap-3">
                                <button type="submit" class="btn-icon" id="addBtn">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button type="button" class="btn-icon" id="editBtn">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn-icon disabled" id="deleteBtn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        function updateThemeIcon(theme) {
            const themeIcon = document.getElementById('theme-icon');
            themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }

        // Apply saved theme on page load
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


            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            updateThemeIcon(savedTheme);
            
            // Load PPAS forms from the database
            loadPPASForms();
            
            // Add event listeners for the form
            setupFormEventListeners();
            
            // Setup dynamic tables
            setupDynamicTables();
        });
        
        function loadPPASForms() {
            // Fetch PPAS forms from the database
            $.ajax({
                url: 'get_ppas_forms.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const select = $('#ppas_select');
                    select.empty();
                    select.append('<option value="" selected disabled>Select a PPAS form</option>');
                    
                    if (response.status === 'success') {
                        response.data.forEach(function(form) {
                            select.append(`<option value="${form.id}">${form.title}</option>`);
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading PPAS forms:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load PPAS forms. Please try again.'
                    });
                }
            });
        }
        
        function setupFormEventListeners() {
            // PPAS form selection event
            $('#ppas_select').on('change', function() {
                const ppasId = $(this).val();
                if (ppasId) {
                    fetchPPASDetails(ppasId);
                }
            });
            
            // Form submission
            $('#narrativeForm').on('submit', function(e) {
                e.preventDefault();
                saveNarrativeForm();
            });
            
            // Edit button click
            $('#editBtn').on('click', function() {
                if ($(this).find('i').hasClass('fa-edit')) {
                    // Switch to edit mode
                    $(this).find('i').removeClass('fa-edit').addClass('fa-times');
                    $(this).addClass('editing');
                    enableFormEditing();
                } else {
                    // Cancel edit mode
                    $(this).find('i').removeClass('fa-times').addClass('fa-edit');
                    $(this).removeClass('editing');
                    disableFormEditing();
                }
            });
            
            // Delete button click
            $('#deleteBtn').on('click', function() {
                deleteNarrativeForm();
            });
            
            // View button click
            $('#viewBtn').on('click', function() {
                viewNarrativeReport();
            });
            
            // Photo upload preview
            $('#photos').on('change', function() {
                displayPhotoPreview(this);
            });
        }
        
        function setupDynamicTables() {
            // Add row to task assignment table
            $('#addTaskRow').on('click', function() {
                const newRow = `
                    <tr>
                        <td>
                            <input type="text" class="form-control" name="task_name[]" placeholder="Name">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="task_description[]" placeholder="Task description">
                        </td>
                    </tr>
                `;
                $('#taskAssignmentTable').append(newRow);
            });
        }
        
        function fetchPPASDetails(ppasId) {
            $.ajax({
                url: 'get_ppas_details.php',
                type: 'GET',
                data: { id: ppasId },
                dataType: 'json',
                success: function(response) {
                    console.log("PPAS Details Response:", response); // Add debug log
                    
                    if (response.status === 'success') {
                        // Populate auto-fetch fields
                        $('#title').val(response.data.title);
                        $('#location').val(response.data.location);
                        $('#duration').val(response.data.duration);
                        
                        // Populate project team table
                        const projectTeam = response.data.project_team;
                        $('#projectTeamTable').empty();
                        
                        if (projectTeam && projectTeam.length > 0) {
                            projectTeam.forEach(function(member) {
                                $('#projectTeamTable').append(`
                                    <tr>
                                        <td>${member.role}</td>
                                        <td>${member.name}</td>
                                    </tr>
                                `);
                            });
                        }
                        
                        // Populate beneficiaries table with PPAS data
                        populateBeneficiariesTable(response.data);
                        
                        // Check if narrative already exists for this PPAS
                        checkExistingNarrative(ppasId);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching PPAS details:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch PPAS details. Please try again.'
                    });
                }
            });
        }
        
        // Add new function to populate beneficiaries table
        function populateBeneficiariesTable(data) {
            console.log("Populating beneficiaries with:", data);
            
            // Clear existing rows
            $('#beneficiariesTable').empty();
            
            // Handle the nested beneficiaries structure from the API
            const beneficiaries = data.beneficiaries || {};
            
            // Add students row
            $('#beneficiariesTable').append(`
                <tr>
                    <td>
                        <input type="text" class="form-control" name="participant_type[]" value="Students" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control internal-male" name="internal_male[]" min="0" value="${beneficiaries.students?.male || 0}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control internal-female" name="internal_female[]" min="0" value="${beneficiaries.students?.female || 0}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control external-male" name="external_male[]" min="0" value="0" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control external-female" name="external_female[]" min="0" value="0" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control total-male" value="${beneficiaries.students?.male || 0}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control total-female" value="${beneficiaries.students?.female || 0}" readonly>
                    </td>
                </tr>
            `);
            
            // Add faculty row
            $('#beneficiariesTable').append(`
                <tr>
                    <td>
                        <input type="text" class="form-control" name="participant_type[]" value="Faculty" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control internal-male" name="internal_male[]" min="0" value="${beneficiaries.faculty?.male || 0}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control internal-female" name="internal_female[]" min="0" value="${beneficiaries.faculty?.female || 0}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control external-male" name="external_male[]" min="0" value="0" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control external-female" name="external_female[]" min="0" value="0" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control total-male" value="${beneficiaries.faculty?.male || 0}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control total-female" value="${beneficiaries.faculty?.female || 0}" readonly>
                    </td>
                </tr>
            `);
            
            // Add external participants row if they exist
            if (beneficiaries.external && (beneficiaries.external.male > 0 || beneficiaries.external.female > 0)) {
                $('#beneficiariesTable').append(`
                    <tr>
                        <td>
                            <input type="text" class="form-control" name="participant_type[]" value="${beneficiaries.external.type || 'External'}" readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control internal-male" name="internal_male[]" min="0" value="0" readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control internal-female" name="internal_female[]" min="0" value="0" readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control external-male" name="external_male[]" min="0" value="${beneficiaries.external.male || 0}" readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control external-female" name="external_female[]" min="0" value="${beneficiaries.external.female || 0}" readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control total-male" value="${beneficiaries.external.male || 0}" readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control total-female" value="${beneficiaries.external.female || 0}" readonly>
                        </td>
                    </tr>
                `);
            }
            
            // Add total row
            $('#beneficiariesTable').append(`
                <tr class="table-secondary">
                    <td>
                        <input type="text" class="form-control" value="Total" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control" value="${beneficiaries.internal?.male || 0}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control" value="${beneficiaries.internal?.female || 0}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control" value="${beneficiaries.external?.male || 0}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control" value="${beneficiaries.external?.female || 0}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control" value="${beneficiaries.total?.male || 0}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control" value="${beneficiaries.total?.female || 0}" readonly>
                    </td>
                </tr>
            `);
        }
        
        function checkExistingNarrative(ppasId) {
            $.ajax({
                url: 'check_narrative.php',
                type: 'GET',
                data: { ppas_id: ppasId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.exists) {
                        // Narrative exists, load it
                        $('#narrative_id').val(response.data.id);
                        $('#implementing_office').val(response.data.implementing_office);
                        $('#partner_agency').val(response.data.partner_agency);
                        
                        // Handle checkboxes for service agenda
                        if (response.data.service_agenda) {
                            const serviceAgenda = JSON.parse(response.data.service_agenda);
                            $('input[name="service_agenda[]"]').prop('checked', false);
                            serviceAgenda.forEach(function(item) {
                                $(`input[name="service_agenda[]"][value="${item}"]`).prop('checked', true);
                            });
                        }
                        
                        // Handle checkboxes for SDG
                        if (response.data.sdg) {
                            const sdg = JSON.parse(response.data.sdg);
                            $('input[name="sdg[]"]').prop('checked', false);
                            sdg.forEach(function(item) {
                                $(`input[name="sdg[]"][value="${item}"]`).prop('checked', true);
                            });
                        }
                        
                        // Handle tasks
                        if (response.data.tasks) {
                            const tasks = JSON.parse(response.data.tasks);
                            $('#taskAssignmentTable').empty();
                            
                            tasks.forEach(function(task) {
                                $('#taskAssignmentTable').append(`
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control" name="task_name[]" value="${task.name}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="task_description[]" value="${task.task}">
                                        </td>
                                    </tr>
                                `);
                            });
                        }
                        
                        // Load objectives
                        $('#general_objective').val(response.data.general_objective);
                        $('#specific_objective').val(response.data.specific_objective);
                        
                        // Load activity details
                        $('#activity_title').val(response.data.activity_title);
                        $('#activity_narrative').val(response.data.activity_narrative);
                        
                        // Load evaluation details
                        $('#evaluation_result').val(response.data.evaluation_result);
                        $('#survey_result').val(response.data.survey_result);
                        
                        // Display photo previews if available
                        if (response.data.photos) {
                            const photos = JSON.parse(response.data.photos);
                            const previewContainer = $('#photo-preview');
                            previewContainer.empty();
                            
                            photos.forEach(function(photo) {
                                previewContainer.append(`
                                    <div class="col-md-2 mb-3">
                                        <div class="card">
                                            <img src="${photo}" class="card-img-top" alt="Photo preview">
                                        </div>
                                    </div>
                                `);
                            });
                        }
                        
                        // Update buttons state
                        $('#addBtn').html('<i class="fas fa-save"></i>').addClass('btn-update');
                        $('#deleteBtn').removeClass('disabled');
                        $('#viewBtn').removeClass('disabled');
                        
                        // Notify user that an existing narrative was loaded
                        Swal.fire({
                            icon: 'info',
                            title: 'Existing Narrative',
                            text: 'An existing narrative for this PPAS form has been loaded.'
                        });
                    } else {
                        // No existing narrative, reset form (except the auto-fetched fields)
                        $('#narrative_id').val('');
                        $('#implementing_office').val('');
                        $('#partner_agency').val('');
                        $('input[name="service_agenda[]"]').prop('checked', false);
                        $('input[name="sdg[]"]').prop('checked', false);
                        
                        // Reset task table (but don't reset beneficiaries - they're from PPAS data)
                        $('#taskAssignmentTable').html(`
                            <tr>
                                <td>
                                    <input type="text" class="form-control" name="task_name[]" placeholder="Name">
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="task_description[]" placeholder="Task description">
                                </td>
                            </tr>
                        `);
                        
                        // Reset text areas
                        $('#general_objective').val('');
                        $('#specific_objective').val('');
                        $('#activity_title').val('');
                        $('#activity_narrative').val('');
                        $('#evaluation_result').val('');
                        $('#survey_result').val('');
                        
                        // Clear photo preview
                        $('#photo-preview').empty();
                        
                        // Update buttons state
                        $('#addBtn').html('<i class="fas fa-plus"></i>').removeClass('btn-update');
                        $('#deleteBtn').addClass('disabled');
                        $('#viewBtn').addClass('disabled');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error checking existing narrative:', error);
                }
            });
        }
        
        function saveNarrativeForm() {
            // Validate that a PPAS form is selected
            const ppasId = $('#ppas_select').val();
            if (!ppasId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select a PPAS form first'
                });
                return;
            }
            
            // Get form data
            const formData = new FormData(document.getElementById('narrativeForm'));
            
            // Add the PPAS ID
            formData.append('ppas_id', ppasId);
            
            // Show loading indicator
            Swal.fire({
                title: 'Saving...',
                text: 'Please wait while we save your narrative form',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: 'save_narrative.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                    
                    if (response.status === 'success') {
                        // Update the narrative ID field
                        $('#narrative_id').val(response.id);
                        
                        // Enable view and delete buttons
                        $('#deleteBtn').removeClass('disabled');
                        $('#viewBtn').removeClass('disabled');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.error('Error saving narrative form:', error);
                    let errorMessage = 'Failed to save narrative form. Please try again.';
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // If we can't parse the response, use the default error message
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                }
            });
        }
        
        function deleteNarrativeForm() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const narrativeId = $('#narrative_id').val(); // You'll need to add a hidden field for narrative_id
                    
                    $.ajax({
                        url: 'delete_narrative.php',
                        type: 'POST',
                        data: { id: narrativeId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error deleting narrative form:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to delete narrative form. Please try again.'
                            });
                        }
                    });
                }
            });
        }
        
        function viewNarrativeReport() {
            const narrativeId = $('#narrative_id').val(); // You'll need to add a hidden field for narrative_id
            
            if (!narrativeId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please save the narrative form first or select an existing one to view.'
                });
                return;
            }
            
            // Redirect to the narrative report view page
            window.open('view_narrative.php?id=' + narrativeId, '_blank');
        }
        
        function enableFormEditing() {
            // Enable all form fields except auto-fetched ones
            $('#narrativeForm input, #narrativeForm textarea, #narrativeForm select').not('#title, #location, #duration').prop('disabled', false);
            
            // Change the add button to update
            $('#addBtn').html('<i class="fas fa-save"></i>').addClass('btn-update');
        }
        
        function disableFormEditing() {
            // Disable all form fields
            $('#narrativeForm input, #narrativeForm textarea, #narrativeForm select').prop('disabled', true);
            
            // Change the add button back to add
            $('#addBtn').html('<i class="fas fa-plus"></i>').removeClass('btn-update');
        }
        
        function displayPhotoPreview(input) {
            const previewContainer = $('#photo-preview');
            previewContainer.empty();
            
            if (input.files && input.files.length > 0) {
                for (let i = 0; i < input.files.length; i++) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewContainer.append(`
                            <div class="col-md-2 mb-3">
                                <div class="card">
                                    <img src="${e.target.result}" class="card-img-top" alt="Photo preview">
                                </div>
                            </div>
                        `);
                    }
                    
                    reader.readAsDataURL(input.files[i]);
                }
            }
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
                backdrop: `rgba(0,0,0,0.7)`,
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
    </script>
    <script>
    // Add this JavaScript to handle photo uploads and validation
    document.addEventListener('DOMContentLoaded', function() {
        const photoUpload = document.getElementById('photo_upload');
        const photoPreviewsContainer = document.getElementById('photoPreviewsContainer');
        const photoCountDisplay = document.getElementById('photoCount');
        const uploadedPhotos = [];
        const minRequiredPhotos = 6;
        
        // Validation function for form submission
        function validatePhotoUploads() {
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function(event) {
                if (uploadedPhotos.length < minRequiredPhotos) {
                    event.preventDefault();
                    photoUpload.nextElementSibling.style.display = 'block';
                    photoCountDisplay.classList.add('text-danger');
                    photoCountDisplay.classList.remove('text-muted', 'text-success');
                    window.scrollTo({
                        top: photoUpload.parentElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        }
        
        // Handle file selection
        photoUpload.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            files.forEach(file => {
                if (file.type.match('image.*')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const photoId = 'photo_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                        uploadedPhotos.push({
                            id: photoId,
                            file: file,
                            dataUrl: e.target.result
                        });
                        
                        // Create preview element
                        const col = document.createElement('div');
                        col.className = 'col-md-2 col-sm-4 col-6 mb-3';
                        col.dataset.photoId = photoId;
                        
                        const card = document.createElement('div');
                        card.className = 'card h-100';
                        
                        const imgContainer = document.createElement('div');
                        imgContainer.className = 'photo-preview-container';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'card-img-top photo-preview';
                        img.alt = 'Photo preview';
                        
                        const cardBody = document.createElement('div');
                        cardBody.className = 'card-body p-2 d-flex justify-content-center';
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'btn btn-sm btn-outline-danger';
                        removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
                        removeBtn.addEventListener('click', function() {
                            const index = uploadedPhotos.findIndex(p => p.id === photoId);
                            if (index !== -1) {
                                uploadedPhotos.splice(index, 1);
                                col.remove();
                                updatePhotoCount();
                            }
                        });
                        
                        imgContainer.appendChild(img);
                        card.appendChild(imgContainer);
                        cardBody.appendChild(removeBtn);
                        card.appendChild(cardBody);
                        col.appendChild(card);
                        photoPreviewsContainer.appendChild(col);
                        
                        updatePhotoCount();
                    };
                    
                    reader.readAsDataURL(file);
                }
            });
            
            // Clear the input to allow selecting the same files again
            e.target.value = '';
        });
        
        function updatePhotoCount() {
            photoCountDisplay.textContent = `${uploadedPhotos.length}/${minRequiredPhotos} photos uploaded`;
            
            if (uploadedPhotos.length >= minRequiredPhotos) {
                photoCountDisplay.classList.add('text-success');
                photoCountDisplay.classList.remove('text-danger', 'text-muted');
                photoUpload.nextElementSibling.style.display = 'none';
            } else {
                photoCountDisplay.classList.remove('text-success');
                photoCountDisplay.classList.add('text-muted');
            }
            
            // Add file data to a hidden input for submission
            let formData = new FormData();
            uploadedPhotos.forEach(photo => {
                formData.append('photos[]', photo.file);
            });
            
            // Store the FileList in a hidden input for submission
            const existingHiddenInput = document.getElementById('photos_data');
            if (!existingHiddenInput) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = 'photos_data';
                hiddenInput.name = 'photos_data';
                hiddenInput.value = JSON.stringify(uploadedPhotos.map(p => p.id));
                document.querySelector('form').appendChild(hiddenInput);
            } else {
                existingHiddenInput.value = JSON.stringify(uploadedPhotos.map(p => p.id));
            }
        }
        
        // Initialize validation
        validatePhotoUploads();
    });
    </script>
    <script>
    // Function to calculate evaluation table totals
    document.addEventListener('DOMContentLoaded', function() {
        // Activity rating totals
        const ratingFields = [
            {batstateu: 'excellent_batstateu', other: 'excellent_other', total: 'excellent_total'},
            {batstateu: 'very_satisfactory_batstateu', other: 'very_satisfactory_other', total: 'very_satisfactory_total'},
            {batstateu: 'satisfactory_batstateu', other: 'satisfactory_other', total: 'satisfactory_total'},
            {batstateu: 'fair_batstateu', other: 'fair_other', total: 'fair_total'},
            {batstateu: 'poor_batstateu', other: 'poor_other', total: 'poor_total'}
        ];
        
        // Timeliness rating totals
        const timelinessFields = [
            {batstateu: 'timeliness_excellent_batstateu', other: 'timeliness_excellent_other', total: 'timeliness_excellent_total'},
            {batstateu: 'timeliness_very_satisfactory_batstateu', other: 'timeliness_very_satisfactory_other', total: 'timeliness_very_satisfactory_total'},
            {batstateu: 'timeliness_satisfactory_batstateu', other: 'timeliness_satisfactory_other', total: 'timeliness_satisfactory_total'},
            {batstateu: 'timeliness_fair_batstateu', other: 'timeliness_fair_other', total: 'timeliness_fair_total'},
            {batstateu: 'timeliness_poor_batstateu', other: 'timeliness_poor_other', total: 'timeliness_poor_total'}
        ];
        
        // Function to set up automatic calculation for a set of fields
        function setupTotalCalculation(fieldSet) {
            fieldSet.forEach(fields => {
                const batstateuInput = document.querySelector(`input[name="${fields.batstateu}"]`);
                const otherInput = document.querySelector(`input[name="${fields.other}"]`);
                const totalInput = document.querySelector(`input[name="${fields.total}"]`);
                
                if (batstateuInput && otherInput && totalInput) {
                    // Calculate total on page load
                    calculateTotal(batstateuInput, otherInput, totalInput);
                    
                    // Add event listeners for input changes
                    batstateuInput.addEventListener('input', () => calculateTotal(batstateuInput, otherInput, totalInput));
                    otherInput.addEventListener('input', () => calculateTotal(batstateuInput, otherInput, totalInput));
                }
            });
        }
        
        // Function to calculate total from two inputs
        function calculateTotal(input1, input2, totalInput) {
            const value1 = parseInt(input1.value) || 0;
            const value2 = parseInt(input2.value) || 0;
            totalInput.value = value1 + value2;
        }
        
        // Set up calculations for both tables
        setupTotalCalculation(ratingFields);
        setupTotalCalculation(timelinessFields);
    });
    </script>
</body>
</html>