<?php
session_start();

// Debug session information
error_log("Session data in ppas.php: " . print_r($_SESSION, true));

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
            min-height: auto; /* Remove fixed min-height */
        }

        .card-body {
            padding: 1rem; /* Reduce padding */
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

/* Base styles for both screen and print */
.proposal-container {
    width: 100%;
    margin: 0 !important;
    padding: 0 !important;
    border: 1px solid black !important;
    border-collapse: collapse !important;
}

/* Table styles for both screen and print */
.proposal-container table {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border-collapse: collapse !important;
    border: none !important;
}

.proposal-container td,
.proposal-container th {
    border: 1px solid black !important;
    padding: 8px !important;
    margin: 0 !important;
}

/* Remove spacing between sections */
.header-section,
.main-section {
    margin: 0 !important;
    padding: 0 !important;
}

.header-section table,
.main-section table {
    margin: 0 !important;
    border: none !important;
}

/* Remove any margins between rows */
.proposal-container tr {
    margin: 0 !important;
    padding: 0 !important;
}

/* Print-specific styles */
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

    .proposal-container {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 1px solid black !important;
        background: white !important;
    }

    .proposal-container table {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border-collapse: collapse !important;
        page-break-inside: avoid !important;
    }

    .proposal-container td,
    .proposal-container th {
        border: 1px solid black !important;
        padding: 8px !important;
        margin: 0 !important;
        font-size: 12pt !important;
        line-height: 1.3 !important;
    }

    /* Force background colors */
    .proposal-container th,
    .proposal-container td[style*="background-color: #f8f9fa"] {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Hide non-printable elements */
    .sidebar, .mobile-nav-toggle, .sidebar-backdrop, 
    .btn-group, .card, .page-title {
        display: none !important;
    }

    /* Reset main content */
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }

    /* Force colors and remove decorations */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
        box-shadow: none !important;
        text-shadow: none !important;
    }
}

/* Dark mode adjustments */
[data-bs-theme="dark"] .proposal-container {
    background-color: var(--proposal-bg);
    border-color: var(--proposal-border);
}

[data-bs-theme="dark"] .proposal-container td,
[data-bs-theme="dark"] .proposal-container th {
    border-color: var(--proposal-border);
    background-color: var(--proposal-bg);
}

[data-bs-theme="dark"] .proposal-container th {
    background-color: var(--proposal-header-bg) !important;
    color: var(--proposal-text);
}

/* PDF-like container styles */
.proposal-container {
    max-width: 1200px;
    margin: 30px auto;
    background: white;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    padding: 40px;
    font-family: 'Times New Roman', Times, serif;
}

/* Table layout styles */
.proposal-container table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}

.proposal-container td,
.proposal-container th {
    padding: 12px 15px;
    line-height: 1.5;
}

/* Header section styles */
.header-section {
    margin-bottom: 30px;
}

.header-section table {
    margin-bottom: 15px !important;
}

.header-section table:first-child {
    border: 1px solid #000 !important;
}

.header-section table:first-child td {
    vertical-align: middle;
}

.header-section table:nth-child(2),
.header-section table:nth-child(3) {
    border: 1px solid #000 !important;
}

/* Main section styles */
.main-section table {
    border: 1px solid #000 !important;
    margin-bottom: 15px !important;
}

.main-section table th {
    background-color: #f8f9fa;
    font-weight: bold;
    text-align: left;
}

/* Specific table styles */
.project-team-table td:first-child {
    width: 25%;
    font-weight: bold;
    background-color: #f8f9fa;
}

/* Dark mode styles */
[data-bs-theme="dark"] .proposal-container {
    background-color: var(--proposal-bg);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
}

[data-bs-theme="dark"] .proposal-container table {
    border-color: var(--proposal-border) !important;
}

[data-bs-theme="dark"] .proposal-container th,
[data-bs-theme="dark"] .project-team-table td:first-child {
    background-color: var(--proposal-header-bg) !important;
    color: var(--proposal-text);
}

[data-bs-theme="dark"] .proposal-container td {
    color: var(--proposal-text);
    background-color: var(--proposal-bg) !important;
}

/* Print styles */
@media print {
    @page {
        size: A4;
        margin: 1cm;
    }

    body {
        background: white !important;
    }

    .proposal-container {
        margin: 0;
        padding: 0;
        box-shadow: none;
        max-width: none;
    }

    .proposal-container table {
        page-break-inside: avoid;
    }

    .header-section table,
    .main-section table {
        border: 1px solid #000 !important;
    }

    .proposal-container th {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
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

/* Logo styles */
.logo-cell {
    width: 15%;
    text-align: center;
    padding: 15px !important;
}

.logo-cell img {
    max-width: 80px;
    height: auto;
}

/* Reference number styles */
.reference-cell {
    width: 45%;
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

/* Remove duplicate borders */
.main-section table + table {
    border-top: none !important;
}

/* Print styles */
@media print {
    @page {
        size: A4 landscape;
        margin: 1.5cm;
    }

    /* Hide non-printable elements */
    .sidebar, .mobile-nav-toggle, .sidebar-backdrop, 
    .btn-group, .card, .page-title {
        display: none !important;
    }

    /* Container reset */
    .proposal-container {
        margin: 0 !important;
        padding: 0 !important;
        border: 1px solid black !important;
        width: 100% !important;
    }

    /* Remove all margins and spacing between tables */
    .proposal-container table {
        margin: 0 !important;
        padding: 0 !important;
        border-collapse: collapse !important;
        width: 100% !important;
    }

    /* Remove individual table borders, only keep cell borders */
    .proposal-container table {
        border: none !important;
    }

    .proposal-container td,
    .proposal-container th {
        border: 1px solid black !important;
        padding: 8px !important;
        font-size: 12pt !important;
        line-height: 1.3 !important;
    }

    /* Remove spacing between sections */
    .header-section,
    .main-section {
        margin: 0 !important;
        padding: 0 !important;
    }

    .header-section table,
    .main-section table {
        margin: 0 !important;
        border: none !important;
    }

    /* Remove any margins between rows */
    .proposal-container tr {
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Header backgrounds */
    .proposal-container th,
    .proposal-container td[style*="background-color: #f8f9fa"] {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Ensure proper text alignment */
    .proposal-container td {
        vertical-align: middle !important;
    }

    /* Logo sizing */
    .logo-cell img {
        max-width: 60px !important;
        height: auto !important;
    }

    /* Checkbox container */
    .checkbox-container {
        padding: 8px 0 !important;
        margin: 0 !important;
    }

    /* Signature section */
    .signatures-table td {
        padding: 15px 8px !important;
    }

    /* Reset all margins and paddings */
    .proposal-container * {
        margin: 0 !important;
        box-shadow: none !important;
    }

    /* Force background colors */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    /* Reset body and main content */
    body, .main-content {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }

    /* Ensure proper page breaks */
    .proposal-container {
        page-break-inside: avoid !important;
    }
}

/* Screen display styles */
.proposal-container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0;
    border: 1px solid black;
}

.proposal-container table {
    margin: 0;
    padding: 0;
    border-collapse: collapse;
    width: 100%;
}

.proposal-container td,
.proposal-container th {
    border: 1px solid black;
    padding: 8px;
}

.header-section,
.main-section {
    margin: 0;
    padding: 0;
}

.header-section table,
.main-section table {
    margin: 0;
    border: none;
}

/* Remove any spacing between tables */
.proposal-container table + table {
    margin-top: 0 !important;
}

/* Remove border-bottom from tables except the last one */
.main-section > table {
    border-bottom: none !important;
}

.main-section > table:last-child {
    border-bottom: none !important;
}

/* Dark mode adjustments */
[data-bs-theme="dark"] .proposal-container {
    border-color: var(--proposal-border);
}

[data-bs-theme="dark"] .proposal-container td,
[data-bs-theme="dark"] .proposal-container th {
    border-color: var(--proposal-border);
}

/* Remove borders between sections */
.main-section table {
    border: none !important;
    border-collapse: collapse !important;
}

.main-section table td,
.main-section table th {
    border: none !important;
    border-collapse: collapse !important;
    padding: 8px !important;
}

/* Keep only the outer container border */
.proposal-container {
    border: 1px solid black !important;
}

/* Header section styles - keep borders */
.header-section table {
    border: 1px solid black !important;
    margin-bottom: 15px !important;
}

/* Print-specific styles */
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

    .proposal-container {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 1px solid black !important;
        background: white !important;
    }

    /* Remove borders in print for main section */
    .main-section table {
        border: none !important;
        border-collapse: collapse !important;
    }

    .main-section table td,
    .main-section table th {
        border: none !important;
        padding: 8px !important;
    }

    /* Keep header section borders in print */
    .header-section table {
        border: 1px solid black !important;
        margin-bottom: 15px !important;
    }

    /* Force background colors */
    .main-section th,
    .main-section td[style*="background-color: #f8f9fa"] {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Hide non-printable elements */
    .sidebar, .mobile-nav-toggle, .sidebar-backdrop, 
    .btn-group, .card, .page-title {
        display: none !important;
    }

    /* Reset main content */
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }

    /* Force colors and remove decorations */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
        box-shadow: none !important;
        text-shadow: none !important;
    }
}

/* Dark mode adjustments */
[data-bs-theme="dark"] .proposal-container {
    background-color: var(--proposal-bg);
    border-color: var(--proposal-border);
}

[data-bs-theme="dark"] .header-section table {
    border-color: var(--proposal-border) !important;
}

[data-bs-theme="dark"] .main-section th {
    background-color: var(--proposal-header-bg) !important;
    color: var(--proposal-text);
}

/* Remove all other styles that might add borders */
.table-bordered,
.table-bordered > thead > tr > th,
.table-bordered > tbody > tr > td {
    border: none !important;
}

/* Ensure no margins between sections */
.main-section > table {
    margin: 0 !important;
    padding: 0 !important;
}

/* Remove any spacing between tables */
.main-section table + table {
    margin-top: 0 !important;
}

/* Signature section - keep borders */
.signatures-table {
    border: 1px solid black !important;
    margin-top: 15px !important;
}

.signatures-table td {
    border: 1px solid black !important;
    padding: 15px 8px !important;
}

/* Print reset for signature section */
@media print {
    .signatures-table {
        border: 1px solid black !important;
        margin-top: 15px !important;
    }

    .signatures-table td {
        border: 1px solid black !important;
        padding: 15px 8px !important;
    }
}

/* Add these styles to your existing styles section */
input[list]::-webkit-calendar-picker-indicator {
    display: none !important;
}

input[list] {
    padding-right: 30px;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px;
}

/* Dark mode adjustments */
[data-bs-theme="dark"] input[list] {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>');
}

/* Add these styles to your existing styles section */
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

    /* Existing styles */
    // ... existing code ...

    /* Print styles */
    @media print {
        body {
            margin: 0;
            padding: 0;
        }
        
        .proposal-container {
            width: 100%;
            border: 0.5px solid black;
            page-break-inside: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }
        
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        td {
            page-break-inside: avoid;
            padding: 5px;
        }
        
        .header-table, .title-table, .checkbox-table {
            border: 0.5px solid black !important;
        }
        
        .logo-cell, .reference-cell, .form-title {
            border: 0.5px solid black !important;
        }
        
        /* Other existing print styles */
        // ... existing code ...
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
                        <li><a class="dropdown-item" href="#">GAD Proposal Reports</a></li>
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
            <h2>Print GAD Proposal</h2>
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
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-print"></i> Generate Proposal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Preview -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Proposal Preview</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary" onclick="printReport()">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button class="btn btn-outline-info" onclick="exportToWord()">
                            <i class="fas fa-file-word"></i> Word
                        </button>
                    </div>
                </div>
                <div id="reportPreview" class="table-responsive">
                    <!-- Proposal content will be loaded here -->
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <p>Select a campus, year, and proposal to generate the preview</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            loadCampusOptions();
            
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
                        url: 'api/get_proposals.php',
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
                                    // Store proposals globally
                                    window.proposals = response.data;
                                    
                                    console.log('Found', response.data.length, 'proposals');
                                    
                                    // Add proposals to dropdown
                                    response.data.forEach(function(proposal) {
                                        const item = $('<div class="dropdown-item"></div>')
                                            .text(proposal.activity_title)
                                            .attr('data-id', proposal.id)
                                            .click(function() {
                                                // Set input value
                                                $('#proposal').val(proposal.activity_title);
                                                // Set hidden proposal_id
                                                $('#proposal_id').val(proposal.id);
                                                // Hide dropdown
                                                dropdown.hide();
                                                console.log('Selected proposal:', proposal.activity_title, 'with ID:', proposal.id);
                                            });
                                        
                                        dropdown.append(item);
                                    });
                                    
                                    // Show dropdown
                                    dropdown.show();
                                    console.log('Updated dropdown with', response.data.length, 'options');
                                } else {
                                    console.log('No proposals found - Response data:', JSON.stringify(response));
                                    // Show "no results" message
                                    dropdown.append('<div class="dropdown-item disabled">No proposals found</div>');
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
                            dropdown.append('<div class="dropdown-item disabled">Error loading proposals</div>');
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

            // Clear form button (optional - you can add this to your HTML)
            function clearProposalForm() {
                $('#proposal').val('');
                $('#proposal_id').val('');
                $('#proposalDropdown').hide();
            }

            // Handle proposal selection
            $('#proposal').on('change', function() {
                const selectedTitle = $(this).val();
                console.log('Selected title:', selectedTitle);
                
                const proposals = window.proposals || [];
                console.log('Available proposals:', proposals);
                
                const selectedProposal = proposals.find(p => p.activity_title === selectedTitle);
                console.log('Found proposal:', selectedProposal);

                if (selectedProposal) {
                    $('#proposal_id').val(selectedProposal.id);
                    console.log('Set proposal ID to:', selectedProposal.id);
                } else {
                    $('#proposal_id').val('');
                    if (selectedTitle) {
                        console.log('No matching proposal found for title:', selectedTitle);
                    }
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
                url: 'api/get_proposal_years.php',
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
                            text: 'No proposal years found for this campus. You may need to create proposals first.',
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
            const selectedProposalId = $('#proposal_id').val();

            if (!selectedCampus || !selectedYear || !selectedProposalId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select all required fields to generate the proposal.'
                });
                return;
            }

            // Show loading state
            $('#reportPreview').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading proposal...</p>
                </div>
            `);

            $.ajax({
                url: 'api/get_proposal_details.php',
                method: 'GET',
                data: {
                    campus: selectedCampus,
                    year: selectedYear,
                    proposal_id: selectedProposalId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        displayProposal(response.data);
                    } else {
                        $('#reportPreview').html(`
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                                <p>${response.message || 'Failed to load proposal data'}</p>
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading proposal:', error);
                    $('#reportPreview').html(`
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <p>Error loading proposal. Please try again.</p>
                        </div>
                    `);
                }
            });
        }

        function displayProposal(data) {
            if (!data || !data.sections) {
                $('#reportPreview').html('<p>No proposal data available</p>');
                return;
            }

            const sections = data.sections;
            
            let html = `
            <div class="proposal-container">
                <!-- Single outer table with continuous border -->
                <table style="border-collapse: collapse; width: 100%;">
                    <!-- Header Section -->
                    <tr>
                        <td style="padding: 0;">
                            <table class="header-table" style="border: 0.5px solid black !important; width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td class="logo-cell" style="border: 0.5px solid black !important; width: 15%; padding: 5px;">
                                        <img src="../images/BatStateU-NEU-Logo.png" alt="BatStateU Logo">
                            </td>
                                    <td class="reference-cell" style="border: 0.5px solid black !important; width: 30%; padding: 5px;">
                                        <p><strong>Reference No.:</strong> BatStateU-FO-ESO-09</p>
                            </td>
                                    <td style="border: 0.5px solid black !important; width: 30%; padding: 5px;">
                                        <p><strong>Effectivity Date:</strong> August 25, 2023</p>
                            </td>
                                    <td style="border: 0.5px solid black !important; width: 25%; padding: 5px;">
                                        <p><strong>Revision No.:</strong> 00</p>
                            </td>
                        </tr>
                    </table>
                        </td>
                    </tr>
                        <tr>
                        <td style="padding: 0;">
                            <table class="title-table" style="border: 0.5px solid black !important; width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td class="form-title" style="border: 0.5px solid black !important; text-align: center; font-weight: bold; padding: 5px;">
                                GAD PROPOSAL (INTERNAL PROGRAM/PROJECT/ACTIVITY)
                            </td>
                        </tr>
                    </table>
                        </td>
                    </tr>
                        <tr>
                        <td style="padding: 0;">
                            <table class="checkbox-table" style="border: 0.5px solid black !important; width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="border: 0.5px solid black !important; padding: 5px;">
                                <div class="checkbox-container">
                                    <span class="checkbox-option">☐ Program</span>
                                    <span class="checkbox-option">☐ Project</span>
                                    <span class="checkbox-option">☒ Activity</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                        </td>
                    </tr>

                    <!-- Basic Information -->
                    <tr>
                        <td style="padding: 0;">
                            <table class="content-table">
                                <tr>
                                    <td class="label-cell">Title:</td>
                                    <td class="value-cell">${sections.title || 'N/A'}</td>
                        </tr>
                        <tr>
                                    <td class="label-cell">Date and Venue:</td>
                                    <td class="value-cell">${sections.date_venue.date || 'N/A'} at ${sections.date_venue.venue || 'N/A'}</td>
                        </tr>
                        <tr>
                                    <td class="label-cell">Mode of Delivery:</td>
                                    <td class="value-cell">${sections.delivery_mode || 'N/A'}</td>
                        </tr>
                    </table>
                        </td>
                    </tr>

                    <!-- Project Team Section -->
                    <tr>
                        <td style="padding: 0;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td colspan="2" class="section-header" style="text-align: center; font-weight: bold; padding: 5px;">
                                        Project Team
                                    </td>
                        </tr>
                        <tr>
                                    <td style="width: 25%; padding: 5px; vertical-align: top; border: 0.5px solid black;">
                                        <strong>Project Leader(s):</strong>
                                    </td>
                                    <td style="width: 75%; padding: 5px; border: 0.5px solid black;">
                                        <p style="margin: 0;">${sections.project_team.project_leaders.names || 'N/A'}</p>
                                        <p style="margin-top: 10px; margin-bottom: 5px;"><strong>Responsibilities:</strong></p>
                                        <p style="margin: 0; margin-left: 25px;">${sections.project_team.project_leaders.responsibilities || 'N/A'}</p>
                            </td>
                        </tr>
                        <tr>
                                    <td style="width: 25%; padding: 5px; vertical-align: top; border: 0.5px solid black;">
                                        <strong>Assistant Project Leader(s):</strong>
                                    </td>
                                    <td style="width: 75%; padding: 5px; border: 0.5px solid black;">
                                        <p style="margin: 0;">${sections.project_team.assistant_project_leaders.names || 'N/A'}</p>
                                        <p style="margin-top: 10px; margin-bottom: 5px;"><strong>Responsibilities:</strong></p>
                                        <p style="margin: 0; margin-left: 25px;">${sections.project_team.assistant_project_leaders.responsibilities || 'N/A'}</p>
                            </td>
                        </tr>
                        <tr>
                                    <td style="width: 25%; padding: 5px; vertical-align: top; border: 0.5px solid black;">
                                        <strong>Project Staff:</strong>
                                    </td>
                                    <td style="width: 75%; padding: 5px; border: 0.5px solid black;">
                                        <p style="margin: 0;">${sections.project_team.project_staff.names || 'N/A'}</p>
                                        <p style="margin-top: 10px; margin-bottom: 5px;"><strong>Responsibilities:</strong></p>
                                        <p style="margin: 0; margin-left: 25px;">${sections.project_team.project_staff.responsibilities || 'N/A'}</p>
                            </td>
                        </tr>
                    </table>
                        </td>
                    </tr>

                    <!-- Partner Offices -->
                    <tr class="section-separator">
                        <td style="padding: 0;">
                            <table class="content-table">
                        <tr>
                                    <th colspan="2" class="section-header">Partner Offices</th>
                        </tr>
                        <tr>
                                    <td colspan="2" class="value-cell-full">${sections.partner_offices || 'N/A'}</td>
                        </tr>
                    </table>
                        </td>
                    </tr>

                    <!-- Type of Participants -->
                    <tr class="section-separator">
                        <td style="padding: 0;">
                            <table class="content-table">
                        <tr>
                                    <th colspan="2" class="section-header">Type of Participants</th>
                        </tr>
                        <tr>
                                    <td colspan="2" class="value-cell-full">
                                        <p>Male: ${sections.participants.male || '0'}</p>
                                        <p>Female: ${sections.participants.female || '0'}</p>
                                        <p>Total: ${sections.participants.total || '0'}</p>
                            </td>
                        </tr>
                    </table>
                        </td>
                    </tr>

                    <!-- Rationale -->
                    <tr class="section-separator">
                        <td style="padding: 0;">
                            <table class="content-table">
                        <tr>
                                    <th colspan="2" class="section-header">Rationale/Background</th>
                        </tr>
                        <tr>
                                    <td colspan="2" class="value-cell-full">${sections.rationale || 'N/A'}</td>
                        </tr>
                    </table>
                        </td>
                    </tr>

                    <!-- Objectives -->
                    <tr class="section-separator">
                        <td style="padding: 0;">
                            <table class="content-table">
                        <tr>
                                    <th colspan="2" class="section-header">Objectives</th>
                        </tr>
                        <tr>
                                    <td colspan="2" class="value-cell-full">
                                        <p><strong>General:</strong> ${sections.objectives.general || 'N/A'}</p>
                                        <p><strong>Specific:</strong></p>
                                <ul>
                                    ${sections.objectives.specific.map(obj => `<li>${obj}</li>`).join('') || '<li>N/A</li>'}
                                </ul>
                            </td>
                        </tr>
                    </table>
                        </td>
                    </tr>

                    <!-- Strategies -->
                    <tr class="section-separator">
                        <td style="padding: 0;">
                            <table class="content-table">
                        <tr>
                                    <th colspan="2" class="section-header">Strategies and Methods</th>
                        </tr>
                        <tr>
                                    <td colspan="2" class="value-cell-full">${sections.strategies || 'N/A'}</td>
                        </tr>
                    </table>
                        </td>
                    </tr>

                    <!-- Work Plan -->
                    <tr class="section-separator">
                        <td style="padding: 0;">
                            <table class="content-table">
                        <tr>
                                    <th colspan="2" class="section-header">Work Plan</th>
                        </tr>
                        <tr>
                                    <td colspan="2" class="value-cell-full">
                                        <table class="inner-table">
                                    <thead>
                                        <tr>
                                            <th>Activity</th>
                                            <th>Timeline</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${sections.workplan.map(item => `
                                            <tr>
                                                <td>${item.activity || 'N/A'}</td>
                                                <td>${item.timeline || 'N/A'}</td>
                                            </tr>
                                        `).join('') || '<tr><td colspan="2">No work plan available</td></tr>'}
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </table>
                        </td>
                    </tr>

                    <!-- Financial Requirements -->
                    <tr class="section-separator">
                        <td style="padding: 0;">
                            <table class="content-table">
                        <tr>
                                    <th colspan="2" class="section-header">Financial Requirements</th>
                        </tr>
                        <tr>
                                    <td colspan="2" class="value-cell-full">
                                        <p><strong>Source of Budget:</strong> ${sections.financial.source || 'N/A'}</p>
                                        <p><strong>Total Budget:</strong> ₱${sections.financial.total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) || '0.00'}</p>
                                        <p><strong>Budget Breakdown:</strong></p>
                                        <table class="inner-table">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${Object.entries(sections.financial.breakdown || {}).map(([item, amount]) => `
                                                    <tr>
                                                        <td>${item}</td>
                                                        <td>₱${parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                                    </tr>
                                                `).join('') || '<tr><td colspan="2">No breakdown available</td></tr>'}
                                            </tbody>
                                        </table>
                            </td>
                        </tr>
                    </table>
                        </td>
                    </tr>

                    <!-- Sustainability Plan -->
                    <tr class="section-separator">
                        <td style="padding: 0;">
                            <table class="content-table">
                        <tr>
                                    <th colspan="2" class="section-header">Sustainability Plan</th>
                        </tr>
                        <tr>
                                    <td colspan="2" class="value-cell-full">${sections.sustainability || 'N/A'}</td>
                        </tr>
                    </table>
                        </td>
                    </tr>

                    <!-- Signatures Section -->
                    <tr class="section-separator">
                        <td style="padding: 0;">
                            <table class="signatures-table">
                        <tr>
                                    <th colspan="4" class="section-header">Approval</th>
                        </tr>
                        <tr>
                                    <td class="signature-cell">
                                        <p>Prepared by:</p>
                                        <div class="signature-line"></div>
                                        <p class="signature-name">${sections.signatures.prepared_by.name || ''}</p>
                                        <p class="signature-position">${sections.signatures.prepared_by.position || ''}</p>
                            </td>
                                    <td class="signature-cell">
                                        <p>Reviewed by:</p>
                                        <div class="signature-line"></div>
                                        <p class="signature-name">${sections.signatures.reviewed_by.name || ''}</p>
                                        <p class="signature-position">${sections.signatures.reviewed_by.position || ''}</p>
                            </td>
                                    <td class="signature-cell">
                                        <p>Recommending Approval:</p>
                                        <div class="signature-line"></div>
                                        <p class="signature-name">${sections.signatures.recommending_approval.name || ''}</p>
                                        <p class="signature-position">${sections.signatures.recommending_approval.position || ''}</p>
                            </td>
                                    <td class="signature-cell">
                                        <p>Approved by:</p>
                                        <div class="signature-line"></div>
                                        <p class="signature-name">${sections.signatures.approved_by.name || ''}</p>
                                        <p class="signature-position">${sections.signatures.approved_by.position || ''}</p>
                            </td>
                        </tr>
                    </table>
                        </td>
                    </tr>
                </table>
            </div>`;

            $('#reportPreview').html(html);
        }

        function printReport() {
            // Create a print window with a specific title
            const printWindow = window.open('', '_blank', 'width=1200,height=800');
            
            // Set window properties immediately to prevent about:blank
            printWindow.document.open();
            printWindow.document.title = "GAD Proposal";
            
            const reportContent = $('#reportPreview').html();
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>GAD Proposal</title>
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <style>
                        @page {
                            size: A4;
                            margin: 1cm;
                        }
                        
                        body {
                            font-family: 'Times New Roman', Times, serif;
                            font-size: 12pt;
                            line-height: 1.2;
                            color: black;
                            background: white;
                            margin: 0;
                            padding: 0;
                        }
                        
                        /* Container styles */
                        .proposal-container {
                            width: 100%;
                            margin: 0;
                            padding: 0;
                            border: 0.5px solid black;
                            background: white;
                            box-sizing: border-box;
                        }
                        
                        /* Table styles */
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin: 0;
                            padding: 0;
                        }

                        /* Cell styles */
                        td, th {
                            border: 0.5px solid black;
                            padding: 4px 6px;
                            vertical-align: top;
                            margin: 0;
                        }
                        
                        /* Remove double borders between cells */
                        tr td, tr th {
                            border-right: none;
                            border-left: none;
                        }
                        
                        tr td:first-child, tr th:first-child {
                            border-left: 0.5px solid black;
                        }
                        
                        tr td:last-child, tr th:last-child {
                            border-right: 0.5px solid black;
                        }
                        
                        /* Inner tables */
                        .inner-table {
                            border: 0.5px solid black;
                            margin: 4px 0;
                        }
                        
                        .inner-table th,
                        .inner-table td {
                            border: 0.5px solid black;
                            padding: 4px 6px;
                        }
                        
                        /* Header section */
                        .header-section table,
                        .title-table,
                        .checkbox-table {
                            border: none;
                            margin: 0;
                            padding: 0;
                        }
                        
                        .logo-cell {
                            width: 15%;
                            text-align: center;
                            vertical-align: middle;
                            border: 0.5px solid black !important;
                            padding: 4px !important;
                        }
                        
                        .logo-cell img {
                            width: 60px;
                            height: auto;
                        }
                        
                        /* Section headers */
                        .section-header {
                            background-color: #f8f9fa !important;
                            font-weight: bold;
                            text-align: center;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                            padding: 4px 6px;
                            border-top: 0.5px solid black;
                            border-bottom: 0.5px solid black;
                        }
                        
                        /* Remove spacing between sections */
                        .section-separator {
                            border-top: none;
                        }
                        
                        /* Content sections */
                        .content-table {
                            margin: 0;
                            padding: 0;
                            width: 100%;
                        }
                        
                        .value-cell, .value-cell-full {
                            padding: 4px 6px;
                        }
                        
                        .value-cell p, .value-cell-full p {
                            margin: 2px 0;
                        }
                        
                        /* Signatures section */
                        .signatures-table {
                            border: 0.5px solid black;
                            margin: 0;
                        }
                        
                        .signature-cell {
                            border: 0.5px solid black !important;
                            width: 25%;
                            text-align: center;
                            padding: 8px 6px;
                            vertical-align: top;
                        }
                        
                        .signature-line {
                            border-bottom: 0.5px solid black;
                            width: 80%;
                            margin: 20px auto 4px;
                        }
                        
                        .signature-name {
                            margin: 2px 0;
                            font-weight: bold;
                        }
                        
                        .signature-position {
                            margin: 2px 0;
                            font-style: italic;
                        }
                        
                        /* Lists */
                        ul {
                            margin: 2px 0 2px 20px;
                            padding: 0;
                        }
                        
                        li {
                            margin-bottom: 2px;
                        }
                        
                        /* Print-specific styles */
                        @media print {
                            body {
                                margin: 0;
                                padding: 0;
                            }
                            
                            .proposal-container {
                                margin: 0;
                                padding: 0;
                            }
                            
                            td, th {
                                padding: 4px 6px;
                            }
                            
                            .section-header {
                                padding: 4px 6px;
                            }
                            
                            .signature-cell {
                                padding: 8px 6px;
                            }
                            
                            /* Force background colors */
                            * {
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                                color-adjust: exact !important;
                            }
                        }
                    </style>
                </head>
                <body>
                    ${reportContent}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            setTimeout(() => {
                printWindow.print();
                // Don't close the window automatically to allow user to change printer settings
            }, 250);
        }

        function exportToWord() {
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const selectedCampus = isCentral ? $('#campus').val() : "<?php echo $userCampus ?>";
            const selectedYear = $('#year').val();

            if (!selectedCampus || !selectedYear) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select' + (isCentral ? ' both a campus and' : '') + ' a year to generate the report.'
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
                
                // Create Word document HTML
                const html = `
                    <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
                    <head>
                        <meta charset='utf-8'>
                        <title>GPB Report</title>
                        <style>
                            body {
                                font-family: Times New Roman, sans-serif;
                                font-size: 12pt;
                            }
                            table {
                                border-collapse: collapse;
                                width: 100%;
                                border: 1px solid black;
                            }
                            th, td {
                                border: 1px solid black;
                                padding: 8px;
                                text-align: left;
                                font-size: 12pt;
                            }
                            th {
                                background-color: #e9ecef;
                                font-weight: bold;
                                font-size: 12pt;
                            }
                            .category-header {
                                background-color: #f8f9fa;
                                font-weight: bold;
                                text-align: center;
                                font-size: 12pt;
                            }
                            @page {
                                size: landscape;
                                mso-page-orientation: landscape;
                                margin: 1cm;
                            }
                            .report-header {
                                text-align: center;
                                margin-bottom: 20px;
                                font-family: Times New Roman, sans-serif;
                            }
                            /* Thinner line instead of HR */
                            .report-header hr, 
                            .report-header div[style*="height: 1px"] {
                                height: 1px;
                                background-color: black;
                                width: 100%;
                                margin: 15px auto;
                                display: block;
                                border: none;
                                mso-border-top-alt: solid black 1.0pt;
                                mso-border-between: solid black 1.0pt;
                            }
                            /* Total rows styling */
                            tr.total-row td {
                                font-weight: bold;
                                background-color: #f9f9f9;
                            }
                            .report-footer {
                                margin-top: 20px;
                                font-family: Times New Roman, sans-serif;
                                font-size: 12pt;
                            }
                            /* Column widths for Word export */
                            table th:nth-child(5), table td:nth-child(5) {
                                width: 18% !important;
                                border-right: none !important;
                            }
                            table th:nth-child(6), table td:nth-child(6) {
                                width: 12% !important;
                                border-left: none !important;
                            }
                            /* Style for columns with custom borders */
                            td[style*="border-right: none"] {
                                border-right: none !important;
                            }
                            td[style*="border-left: none"] {
                                border-left: none !important;
                            }
                        </style>
                    </head>
                    <body>
                        ${content.html()}
                    </body>
                    </html>
                `;

                // Create blob and download
                const blob = new Blob([html], { type: 'application/msword' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `GPB_Report_${selectedCampus}_${selectedYear}.doc`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

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
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            updateThemeIcon(savedTheme);
            
        });

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
                    }, 10); // Changed from 50 to 10 - make it super fast
                }
            });
        }

        function updateDateTime() {
            const now = new Date();
            
            // Update date
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            
            // Update time - remove seconds and use numeric hour format
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>
</body>
</html>