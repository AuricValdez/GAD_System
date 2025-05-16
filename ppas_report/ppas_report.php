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
    <title> PPAS Reports - GAD System</title>
    <link rel="icon" type="image/x-icon" href="../images/Batangas_State_Logo.ico">
    <script src="../js/common.js"></script>
    <!-- Immediate theme loading to prevent flash -->
    <script>
        (function() {
            // Use the stored theme preference instead of forcing light mode
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
    <!-- Global Styles -->
    <!-- <link href="../js/global-styles.css" rel="stylesheet"> -->
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
            --card-bg: #f8f9fa;
            --border-color: #dee2e6;
            --horizontal-bar: rgba(33, 37, 41, 0.125);
            --input-placeholder: rgba(33, 37, 41, 0.75);
            --input-bg: #ffffff;
            --input-text: #212529;
            --card-title: #212529;
            --scrollbar-thumb: rgba(156, 39, 176, 0.4);
            --scrollbar-thumb-hover: rgba(156, 39, 176, 0.7);
            --form-select-bg: #ffffff;
            --form-select-text: #212529;
            --form-select-border: #dee2e6;
            --btn-outline-color: #0d6efd;
            --form-label-color: #333333;
            --disabled-bg: #e9ecef;
            --disabled-text: #6c757d;
        }

        /* Dark Theme Variables */
        [data-bs-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --sidebar-bg: #2d2d2d;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --hover-color: #8a4ebd;
            --card-bg: rgba(33, 37, 41, 0.3);
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
            --table-header-bg: #2d2d2d;
            --table-subheader-bg: #2b3035;
            --form-select-bg: #212529;
            --form-select-text: #ffffff;
            --form-select-border: #495057;
            --btn-outline-color: #0dcaf0;
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
            padding: 10px 15px;
            border-radius: 12px;
            margin-bottom: 3px;
            position: relative;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        /* Nav link styles */
        .approval-link {
            color: var(--text-primary);
            padding: 10px 15px;
            border-radius: 12px;
            margin-bottom: 3px;
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

        /* End of dropdown submenu styles */

        .nav-item .dropdown-menu.show {
            display: block;
        }

        /* Dropdown item styles */
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
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            font-size: 16px;
            cursor: pointer;
            padding: 12px;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
        }

        .theme-switch-button:hover {
            background-color: var(--hover-color);
            transform: translateY(-2px);
        }

        [data-bs-theme="light"] .theme-switch-button {
            color: #6c757d;
            background: #f2f2f2;
            border-width: 1.5px;
        }

        [data-bs-theme="light"] .theme-switch-button:hover {
            color: #212529;
            background-color: rgba(0, 0, 0, 0.05);
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
        }
        
        /* Specific size for the form card */
        .card.mb-4 {
            min-height: auto;
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
            --dark-bg: #2d2d2d;
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

/* Table header styling for dark mode */
[data-bs-theme="dark"] #reportTable thead tr:first-child td,
[data-bs-theme="dark"] #reportTable thead tr:nth-child(2) td {
    background-color: #343a40 !important;
    color: #f8f9fa !important;
    border-color: #495057 !important;
}

/* Special styling for the logo cell in dark mode */
[data-bs-theme="dark"] #reportTable thead tr:first-child td:first-child {
    background-color: #343a40 !important;
    color: #f8f9fa !important;
}

[data-bs-theme="dark"] #reportTable thead tr:first-child h4,
[data-bs-theme="dark"] #reportTable thead tr:first-child div {
    color: #f8f9fa !important;
}
        
/* Exception for the logo cell text color */
[data-bs-theme="dark"] #reportTable thead tr:first-child td:first-child div {
    color: #f8f9fa !important;
}

/* Override for all print styles in dark mode */
[data-bs-theme="dark"] #reportTable,
[data-bs-theme="dark"] #reportTable * {
    background-color: #343a40 !important;
    color: #f8f9fa !important;
    border-color: #495057 !important;
}

/* Even more specific overrides for table rows and cells in dark mode */
[data-bs-theme="dark"] #reportTable tr,
[data-bs-theme="dark"] #reportTable tr td,
[data-bs-theme="dark"] #reportTable tr th,
[data-bs-theme="dark"] #reportTable tbody tr,
[data-bs-theme="dark"] #reportTable tbody td,
[data-bs-theme="dark"] #reportTable tbody th {
    background-color: #343a40 !important;
    color: #f8f9fa !important;
}

/* Dark mode styling for table headers - use more specific selectors to override Bootstrap */
[data-bs-theme="dark"] #reportTable thead tr th,
[data-bs-theme="dark"] .table-bordered > thead > tr > th,
[data-bs-theme="dark"] .table > thead > tr > th,
[data-bs-theme="dark"] .table > :not(caption) > * > th {
    background-color: #2d2d2d !important;
    color: #f8f9fa !important;
    border-color: #495057 !important;
}

/* Column headers styling for dark mode */
[data-bs-theme="dark"] #reportTable thead tr:nth-child(3) th {
    background-color: #2d2d2d !important;
    color: #f8f9fa !important;
    font-weight: 600 !important;
}

/* Print-specific styles */
@media print {
    @page {
        size: A4 landscape !important;
        margin: 0.3cm !important;
    }

    /* Reset body/html styles */
    body, html {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        height: auto !important;
        background: white !important;
        font-family: Times New Roman, sans-serif !important;
    }

    /* Hide non-printable elements */
    .sidebar, .card-header, .btn-group, .page-title, 
    .mobile-nav-toggle, .theme-switch-button, .card,
    .nav-content, .bottom-controls, .datetime-container {
        display: none !important;
    }

    /* Main content adjustments */
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: none !important;
        overflow: visible !important;
    }

    /* Table container styles */
    .table-responsive {
        overflow: visible !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Core table styles */
    #reportTable {
        width: 100% !important;
        max-width: none !important;
        border-collapse: collapse !important;
        font-size: 7pt !important; /* Smaller font size */
        margin: 0 !important;
        padding: 0 !important;
        page-break-inside: auto !important;
        table-layout: fixed !important;
        border: 1px solid black !important;
        box-sizing: border-box !important;
    }

    /* Cell styles */
    #reportTable th, 
    #reportTable td {
        border: 1px solid black !important;
        border-right: 1px solid black !important;
        padding: 2px !important; /* Smaller padding */
        vertical-align: top !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        white-space: normal !important;
        line-height: 1.1 !important; /* Reduced line height */
        font-size: 7pt !important; /* Smaller font size */
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    /* Optimize header rows to take up less space */
    #reportTable thead tr:first-child td,
    #reportTable thead tr:nth-child(2) td,
    #reportTable thead tr:nth-child(3) td,
    #reportTable thead tr:nth-child(4) td {
        padding: 2px !important; /* Smaller padding */
        font-size: 7pt !important;
        line-height: 1 !important;
    }
    
    /* Reduce the size of the logo */
    #reportTable thead tr:first-child img {
        height: 30px !important; /* Smaller logo */
        margin: 2px auto !important;
    }
    
    /* Make the header information more compact */
    #reportTable thead tr:first-child td {
        height: auto !important;
        padding: 2px !important;
    }
    
    /* Tighter spacing for title row */
    #reportTable thead tr:nth-child(2) td {
        padding: 2px !important;
    }
    
    #reportTable thead tr:nth-child(2) div {
        margin: 0 !important;
        padding: 0 !important;
        font-size: 10pt !important;
    }
    
    /* Quarter and campus info rows should be compact */
    #reportTable thead tr:nth-child(3) td,
    #reportTable thead tr:nth-child(4) td {
        padding: 2px !important;
        line-height: 1 !important;
    }
    
    /* Column headers should be more compact */
    #reportTable thead tr:last-child th {
        padding: 2px !important;
        font-size: 6pt !important;
        line-height: 1 !important;
        height: auto !important;
        vertical-align: middle !important;
    }
    
    /* CRITICAL: Prevent header from repeating on each page - only show on first page */
    @page {
        @top-center { content: none !important; }
    }
    
    /* Make the table header appear only on the first page */
    #reportTable thead {
        display: table-header-group !important;
        break-inside: avoid !important;
    }
    
    /* The real fix: Create a special class for column headers (last row of thead) */
    #reportTable .column-headers {
        display: table-header-group !important;
    }
    
    /* Prevent the first 4 rows (logo, title, quarter, campus) from repeating */
    #reportTable .header-no-repeat {
        display: table-row-group !important;
    }
    
    /* Gender issue cell centering */
    #reportTable td[rowspan] {
        text-align: center !important;
        vertical-align: middle !important;
        border-bottom: 1px solid black !important;
    }
    
    /* Smaller spacing for signature section */
    #reportTable tr:nth-last-child(-n+3) td {
        padding: 2px !important;
        font-size: 6pt !important;
        line-height: 1 !important;
    }
}

/* Dark mode additional fixes for bootstrap tables */
[data-bs-theme="dark"] .table {
    --bs-table-bg: #343a40 !important;
    --bs-table-striped-bg: #2c3034 !important;
    --bs-table-striped-color: #fff !important;
    --bs-table-active-bg: #373b3e !important;
    --bs-table-active-color: #fff !important;
    --bs-table-hover-bg: #323539 !important;
    --bs-table-hover-color: #fff !important;
    border-color: #495057 !important;
    color: #fff !important;
}

[data-bs-theme="dark"] .table-bordered {
    border-color: #495057 !important;
}

[data-bs-theme="dark"] .table-bordered > :not(caption) > * {
    border-color: #495057 !important;
}

[data-bs-theme="dark"] .table-bordered > :not(caption) > * > * {
    border-color: #495057 !important;
    background-color: #343a40 !important;
}

/* Force header cells to be dark */
[data-bs-theme="dark"] .table th {
    background-color: #2d2d2d !important;
    color: #f8f9fa !important;
}

/* Different color for header rows */
[data-bs-theme="dark"] #reportTable > thead > tr > td {
    background-color: #2d2d2d !important;
    color: #f8f9fa !important;
}

/* NEW: Additional stronger specificity rules to override Bootstrap styles */
[data-bs-theme="dark"] #reportTable thead tr th,
[data-bs-theme="dark"] #reportTable thead th {
    background-color: #2d2d2d !important;
    color: #f8f9fa !important;
    border-color: #495057 !important;
}

/* NEW: Even more specific rules for the first row headers */
[data-bs-theme="dark"] #reportTable > thead > tr:first-child th,
[data-bs-theme="dark"] #reportTable > thead > tr:first-child td, 
[data-bs-theme="dark"] #reportTable > thead > tr:nth-child(2) th,
[data-bs-theme="dark"] #reportTable > thead > tr:nth-child(2) td,
[data-bs-theme="dark"] #reportTable > thead > tr:nth-child(3) th,
[data-bs-theme="dark"] #reportTable > thead > tr:nth-child(3) td {
    background-color: #2d2d2d !important;
    color: #f8f9fa !important;
    border-color: #495057 !important;
}

/* NEW: Force all cells to have the correct background */
[data-bs-theme="dark"] table.table, 
[data-bs-theme="dark"] table.table thead, 
[data-bs-theme="dark"] table.table tbody, 
[data-bs-theme="dark"] table.table tr, 
[data-bs-theme="dark"] table.table td, 
[data-bs-theme="dark"] table.table th {
    background-color: #343a40 !important;
    color: #f8f9fa !important;
    border-color: #495057 !important;
}

/* NEW: Override specific Bootstrap classes that might be causing the issue */
[data-bs-theme="dark"] .table-light,
[data-bs-theme="dark"] .table-light > td,
[data-bs-theme="dark"] .table-light > th {
    background-color: #343a40 !important;
    color: #f8f9fa !important;
}

/* NEW: Specific override for header cells */
[data-bs-theme="dark"] #reportTable tr th {
    background-color: #2d2d2d !important;
}

/* Dark mode form control styles */
[data-bs-theme="dark"] .form-select:disabled,
[data-bs-theme="dark"] .form-control:disabled {
    background-color: #2b3035 !important;
    color: #6c757d !important;
    border-color: #495057 !important;
}

[data-bs-theme="dark"] .form-select,
[data-bs-theme="dark"] .form-control {
    background-color: #2d2d2d !important;
    color: #e9ecef !important;
    border-color: #495057 !important;
}

[data-bs-theme="dark"] .form-select:focus,
[data-bs-theme="dark"] .form-control:focus {
    background-color: #2d2d2d !important;
    color: #e9ecef !important;
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

[data-bs-theme="dark"] .card {
    background-color: #2d2d2d !important;
    border-color: #495057 !important;
    color: #fff !important;
}

[data-bs-theme="dark"] .card-title {
    color: #fff !important;
}

[data-bs-theme="dark"] .text-muted {
    color: #adb5bd !important;
}

[data-bs-theme="dark"] label {
    color: #e9ecef !important;
}

[data-bs-theme="dark"] p {
    color: #e9ecef !important;
}

[data-bs-theme="dark"] .btn-outline-primary {
    color: #0d6efd !important;
    border-color: #0d6efd !important;
}

[data-bs-theme="dark"] .btn-outline-primary:hover {
    color: #fff !important;
    background-color: #0d6efd !important;
}

[data-bs-theme="dark"] .btn-outline-info {
    color: #0dcaf0 !important;
    border-color: #0dcaf0 !important;
}

[data-bs-theme="dark"] .btn-outline-info:hover {
    color: #000 !important;
    background-color: #0dcaf0 !important;
}

/* Table styles for both light and dark mode */
#reportTable {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid var(--border-color);
    background-color: var(--card-bg);
}

#reportTable td,
#reportTable th {
    border: 1px solid var(--border-color);
    padding: 8px;
    vertical-align: middle;
    background-color: var(--card-bg);
}

/* Header specific styles */
#reportTable thead tr:first-child td {
    height: 100px;
    vertical-align: middle;
    background-color: var(--card-bg);
}

#reportTable thead tr:first-child img {
    height: 80px;
    width: auto;
    display: block;
    margin: 0 auto;
}

#reportTable thead tr:nth-child(2) td {
    padding: 15px;
    background-color: var(--card-bg);
}

#reportTable thead tr:nth-child(2) h4 {
    margin: 0;
    font-size: 16px;
    font-weight: bold;
}

#reportTable thead tr:nth-child(3) td {
    padding: 10px;
    background-color: var(--card-bg);
}

#reportTable thead tr:nth-child(3) u {
    text-decoration: underline;
}

/* Dark mode specific styles */
[data-bs-theme="dark"] #reportTable {
    border-color: #495057 !important;
    background-color: #2d2d2d !important;
}

[data-bs-theme="dark"] #reportTable td,
[data-bs-theme="dark"] #reportTable th {
    border-color: #495057 !important;
    background-color: #2d2d2d !important;
    color: #e9ecef !important;
}

/* Header row styling for dark mode */
[data-bs-theme="dark"] #reportTable thead tr:first-child td {
    background-color: #2d2d2d !important;
    color: #e9ecef !important;
    border-color: #495057 !important;
}

/* Logo cell specific styling */
[data-bs-theme="dark"] #reportTable thead tr:first-child td:first-child {
    background-color: #2d2d2d !important;
    border-color: #495057 !important;
}

/* Title row styling */
[data-bs-theme="dark"] #reportTable thead tr:nth-child(2) td {
    background-color: #2d2d2d !important;
}

[data-bs-theme="dark"] #reportTable thead tr:nth-child(2) div {
    color: #ffffff !important;
}

/* Column headers styling in dark mode */
[data-bs-theme="dark"] #reportTable thead tr:last-child th {
    background-color: #2d2d2d !important;
    color: #ffffff !important;
    font-weight: 600 !important;
}

/* Table body styling in dark mode */
[data-bs-theme="dark"] #reportTable tbody td {
    background-color: #2d2d2d !important;
    color: #e9ecef !important;
}

/* Underline styling for quarter and year */
[data-bs-theme="dark"] #reportTable span[style*="text-decoration: underline"] {
    color: #ffffff !important;
    text-decoration: underline !important;
}

/* Print styles - ensure they don't affect dark mode */
@media print {
    #reportTable,
    #reportTable td,
    #reportTable th,
    #reportTable span {
        background-color: white !important;
        color: black !important;
        border-color: black !important;
    }
}

/* Dark mode table overrides */
[data-bs-theme="dark"] .table {
    --bs-table-bg: #2d2d2d !important;
    --bs-table-striped-bg: #2d2d2d !important;
    --bs-table-active-bg: #2d2d2d !important;
    --bs-table-hover-bg: #343a40 !important;
    color: #e9ecef !important;
}

[data-bs-theme="dark"] #reportTable > thead > tr > td,
[data-bs-theme="dark"] #reportTable > thead > tr > th,
[data-bs-theme="dark"] #reportTable > tbody > tr > td {
    background-color: #2d2d2d !important;
}

/* Override Bootstrap dark theme colors */
[data-bs-theme="dark"] .table > :not(caption) > * > * {
    background-color: #2d2d2d !important;
    color: #e9ecef !important;
}

/* Additional overrides to ensure consistent background */
[data-bs-theme="dark"] #reportTable thead tr td,
[data-bs-theme="dark"] #reportTable thead tr th,
[data-bs-theme="dark"] #reportTable tbody tr td {
    background-color: #2d2d2d !important;
    color: #e9ecef !important;
}

/* Ensure no white backgrounds in dark mode */
[data-bs-theme="dark"] #reportTable * {
    background-color: #2d2d2d !important;
    color: #e9ecef !important;
}

/* Exception for print mode */
@media print {
    [data-bs-theme="dark"] #reportTable * {
        background-color: white !important;
        color: black !important;
    }
}

[data-bs-theme="dark"] .form-control.bg-primary {
    background-color: #0d6efd !important;
    color: #ffffff !important;
    border: none !important;
}

[data-bs-theme="dark"] .form-control.bg-primary:hover {
    background-color: #0b5ed7 !important;
    color: #ffffff !important;
}

[data-bs-theme="dark"] .form-control.bg-primary:active,
[data-bs-theme="dark"] .form-control.bg-primary:focus {
    background-color: #0a58ca !important;
    color: #ffffff !important;
    box-shadow: 0 0 0 0.25rem rgba(49, 132, 253, 0.5) !important;
}

/* Form controls in dark mode */
[data-bs-theme="dark"] .form-select,
[data-bs-theme="dark"] .form-control {
    background-color: #2d2d2d !important;
    color: #e9ecef !important;
    border-color: #495057 !important;
}

[data-bs-theme="dark"] .form-select:disabled,
[data-bs-theme="dark"] .form-control:disabled {
    background-color: #2b3035 !important;
    color: #adb5bd !important;
}

/* Override for the Generate Report button specifically */
[data-bs-theme="dark"] button.form-control.bg-primary {
    background-color: #0d6efd !important;
    color: #ffffff !important;
    border: none !important;
    font-weight: 500 !important;
}

[data-bs-theme="dark"] button.form-control.bg-primary:hover {
    background-color: #0b5ed7 !important;
}

[data-bs-theme="dark"] #reportTable {
    border-color: #495057 !important;
}

[data-bs-theme="dark"] #reportTable td,
[data-bs-theme="dark"] #reportTable th {
    border-color: #495057 !important;
    background-color: #2d2d2d !important;
    color: #e9ecef !important;
}

/* Header rows styling in dark mode */
[data-bs-theme="dark"] #reportTable thead tr td {
    background-color: #2d2d2d !important;
    color: #e9ecef !important;
}

/* Logo cell styling in dark mode */
[data-bs-theme="dark"] #reportTable thead tr:first-child td:first-child {
    background-color: #2d2d2d !important;
}

/* Title row styling in dark mode */
[data-bs-theme="dark"] #reportTable thead tr:nth-child(2) td {
    background-color: #2d2d2d !important;
}

/* Column headers styling in dark mode */
[data-bs-theme="dark"] #reportTable thead tr:last-child th {
    background-color: #1a1a1a !important;
    color: #ffffff !important;
    font-weight: 600 !important;
}

/* Table body styling in dark mode */
[data-bs-theme="dark"] #reportTable tbody td {
    background-color: #212121 !important;
    color: #e9ecef !important;
}

/* Dark mode table overrides */
[data-bs-theme="dark"] .table {
    --bs-table-bg: #212121 !important;
    --bs-table-striped-bg: #212121 !important;
    --bs-table-active-bg: #212121 !important;
    --bs-table-hover-bg: #2a2a2a !important;
    color: #e9ecef !important;
}

[data-bs-theme="dark"] #reportTable > thead > tr > td,
[data-bs-theme="dark"] #reportTable > thead > tr > th,
[data-bs-theme="dark"] #reportTable > tbody > tr > td {
    background-color: #212121 !important;
}

/* Override Bootstrap dark theme colors */
[data-bs-theme="dark"] .table > :not(caption) > * > * {
    background-color: #212121 !important;
    color: #e9ecef !important;
}

/* Special style for the signature section table */
#reportTable tr:nth-last-child(2) td {
    padding: 0 !important;
    border: 1px solid black !important;
}

#reportTable tr:nth-last-child(2) td table {
    width: 100% !important;
    border-collapse: collapse !important;
    table-layout: auto !important;
    margin: 0 !important;
    border: none !important;
}

#reportTable tr:nth-last-child(2) td table td {
    width: auto !important;
    border: 1px solid black !important;
    border-width: 0 1px 0 0 !important;
    border-style: solid !important;
    border-color: black !important;
    text-align: center !important;
    padding: 10px !important;
}

#reportTable tr:nth-last-child(2) td table td:first-child {
    border-left: none !important;
}

#reportTable tr:nth-last-child(2) td table td:last-child {
    border-right: none !important;
}

/* Dark theme form controls */
.form-select {
    background-color: #2b3035 !important;
    color: #e9ecef !important;
    border: 1px solid #495057 !important;
}

.form-select:focus {
    box-shadow: none !important;
    border-color: #0d6efd !important;
}

.btn-primary {
    background-color: #0d6efd !important;
    border: none !important;
}

.btn-primary:hover {
    background-color: #0b5ed7 !important;
}

/* Card styling */
.card {
    backdrop-filter: blur(10px);
}

/* Dark theme text */
.text-secondary {
    color: #adb5bd !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .row.g-3 > * {
        margin-bottom: 1rem;
    }
}

/* Additional styles for form controls */
.form-select {
    background-color: #2b3035 !important;
    color: #e9ecef !important;
    border: 1px solid #495057 !important;
}

.form-select:focus {
    box-shadow: none !important;
    border-color: #0d6efd !important;
}

.btn-primary {
    background-color: #0d6efd !important;
    border: none !important;
}

.btn-primary:hover {
    background-color: #0b5ed7 !important;
}

/* Ensure all form elements have the same height */
.form-select, .btn-primary {
    height: 45px !important;
    line-height: normal !important;
}

/* Align all form elements */
.row.align-items-end {
    margin: 0 !important;
}

.col-md-3 {
    padding: 0 8px !important;
}

/* Make labels more compact */
.form-label.small {
    margin-bottom: 4px !important;
    font-size: 0.875rem !important;
}

        /* Light mode specific styles */
        [data-bs-theme="light"] {
            --card-bg: #f8f9fa;
            --form-select-bg: #ffffff;
            --form-select-text: #212529;
            --form-select-border: #dee2e6;
            --btn-outline-color: #0d6efd;
            --form-label-color: #333333;
            --disabled-bg: #e9ecef;
            --disabled-text: #6c757d;
        }

        [data-bs-theme="light"] .theme-form-select,
        [data-bs-theme="light"] select.form-select {
            background-color: white !important;
            color: #212529 !important;
            border: 1px solid #dee2e6 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 16px 12px !important;
        }

        /* Ensuring select option text is also dark and visible */
        [data-bs-theme="light"] select.form-select option {
            background-color: white !important;
            color: #212529 !important;
            font-weight: normal !important;
        }

        [data-bs-theme="light"] .report-card {
            background-color: #ffffff !important;
            border: 1px solid #dee2e6 !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05) !important;
        }

        /* Dark mode specific styles */
        [data-bs-theme="dark"] {
            --card-bg: rgba(33, 37, 41, 0.3);
            --form-select-bg: #212529;
            --form-select-text: #ffffff;
            --form-select-border: #495057;
            --btn-outline-color: #0dcaf0;
        }

        [data-bs-theme="dark"] .theme-form-select,
        [data-bs-theme="dark"] select.form-select {
            background-color: #212529 !important;
            color: #ffffff !important;
            border: 1px solid #495057 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 16px 12px !important;
        }

        /* Responsive card styles that adapt to theme */
        .report-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid var(--form-select-border);
        }

        /* Responsive form elements that adapt to theme */
        .theme-form-select {
            background-color: var(--form-select-bg) !important;
            color: var(--form-select-text) !important;
            border: 1px solid var(--form-select-border) !important;
            height: 45px !important;
            border-radius: 8px !important;
            -webkit-appearance: none !important;
            appearance: none !important;
        }

        /* Button hover effects */
        .btn-outline-primary:hover, .btn-outline-info:hover {
            background-color: var(--btn-outline-color);
            color: white;
        }

        /* Responsive form elements that adapt to theme */
        .theme-form-select {
            background-color: var(--form-select-bg) !important;
            color: var(--form-select-text) !important;
            border: 1px solid var(--form-select-border) !important;
            height: 45px !important;
            border-radius: 8px !important;
            -webkit-appearance: none !important;
            appearance: none !important;
        }

        /* Override bootstrap selects */
        select.form-select {
            background-color: var(--form-select-bg) !important;
            color: var(--form-select-text) !important;
            border: 1px solid var(--form-select-border) !important;
            -webkit-appearance: none !important;
            appearance: none !important;
        }

        /* Additional styles for form controls */
        .form-select {
            background-color: var(--form-select-bg) !important;
            color: var(--form-select-text) !important;
            border: 1px solid var(--form-select-border) !important;
            -webkit-appearance: none !important;
            appearance: none !important;
        }

        [data-bs-theme="light"] .form-label {
            color: var(--form-label-color) !important;
            font-weight: normal !important;
        }

        /* Improve visibility of disabled elements in light mode */
        [data-bs-theme="light"] .form-select:disabled,
        [data-bs-theme="light"] .form-control:disabled,
        [data-bs-theme="light"] .form-control[readonly],
        [data-bs-theme="light"] select:disabled,
        [data-bs-theme="light"] input:disabled,
        [data-bs-theme="light"] textarea:disabled {
            background-color: var(--disabled-bg) !important;
            color: var(--disabled-text) !important;
            border-color: var(--border-color) !important;
            cursor: not-allowed;
        }

      /* Dropdown submenu styles */
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

/* Special styling for the approval link - only visible to Central users */
/* Approval link styles */
.approval-link {
            background-color: var(--accent-color);
            color: white !important;
            border-radius: 12px;
            margin-top: 5px;
            margin-bottom: 10px;
            font-weight: 600;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .approval-link::before {
            content: '';
            position: absolute;
            right: -20px;
            top: 0;
            width: 40px;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            transform: skewX(-25deg);
            opacity: 0.7;
            transition: all 0.5s ease;
        }

        .approval-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            background-color: var(--accent-hover) !important;
            color: white !important;
        }

        .approval-link:hover::before {
            right: 100%;
        }

        /* Ensure the icon in approval link stands out */
        .approval-link i {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .approval-link:hover i {
            transform: scale(1.2);
        }

        /* Dark theme adjustments for approval link */
        [data-bs-theme="dark"] .approval-link {
            background-color: var(--accent-color);
        }

        [data-bs-theme="dark"] .approval-link:hover {
            background-color: var(--accent-hover) !important;
        }

        /* Revamped active state - distinctive but elegant */
        .approval-link.active {
            background-color: transparent !important;
            color: white !important;
            border: 2px solid white;
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: visible;
        }

        .approval-link.active::before {
            display: none;
        }

        .approval-link.active i {
            color: white;
        }

        /* Dark theme revamped active state */
        [data-bs-theme="dark"] .approval-link.active {
            background-color: transparent !important;
            color: white !important;
            border: 2px solid #e0b6ff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
        }

        [data-bs-theme="dark"] .approval-link.active i {
            color: #e0b6ff;
        }

        /* Fixed active state using accent color */
        .approval-link.active {
            background-color: transparent !important;
            color: var(--accent-color) !important;
            border: 2px solid var(--accent-color);
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .approval-link.active i {
            color: var(--accent-color);
        }

        /* Dark theme with accent color */
        [data-bs-theme="dark"] .approval-link.active {
            background-color: transparent !important;
            color: white !important;
            border: 2px solid var(--accent-color);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
        }

        [data-bs-theme="dark"] .approval-link.active i {
            color: var(--accent-color);
        }

/* Notification Badge */
.notification-badge {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: bold;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

/* Dark mode support */
[data-bs-theme="dark"] .notification-badge {
    background-color: #ff5c6c;
}

/* Active state styling */
.nav-link.active .notification-badge {
    background-color: white;
    color: var(--accent-color);
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
                        <li><a class="dropdown-item" href="../signatory/sign.php">Signatory</a></li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-alt me-2"></i> GPB
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../target_forms/target.php">Target</a></li>
                        <li><a class="dropdown-item" href="../gbp_forms/gbp.php">Data Entry</a></li>
                        <li><a class="dropdown-item" href="../gpb_reports/gbp_reports.php">Generate Form</a></li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-invoice me-2"></i> PPAs
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../ppas_form/ppas.php">Data Entry</a></li>
                        <li><a class="dropdown-item" href="../gad_proposal/gad_proposal.php">GAD Proposal</a></li>
                        <li><a class="dropdown-item" href="../gad_narrative/gad_narrative.php">GAD Narrative</a></li>
                        <li><a class="dropdown-item" href="../extension_proposal/extension_proposal.php">Extension Proposal</a></li>
                        <li><a class="dropdown-item" href="../extension_narrative/extension_narrative.php">Extension Narrative</a></li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link  active dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i> Reports
                    </a>
                    <ul class="dropdown-menu">                       
                        <li><a class="dropdown-item" href="../ppas_report/ppas_report.php">Quarterly Report</a></li>
                        <li><a class="dropdown-item" href="../ps_atrib_reports/ps.php">PS Attribution</a></li>
                        <li><a class="dropdown-item" href="../annual_reports/annual_report.php">Annual Report</a></li>
                    </ul>
                </div> 
      
                <?php 
$currentPage = basename($_SERVER['PHP_SELF']);
if($isCentral): 
?>
<a href="../approval/approval.php" class="nav-link approval-link">
    <i class="fas fa-check-circle me-2"></i> Approval
    <span id="approvalBadge" class="notification-badge" style="display: none;">0</span>
</a>
<?php endif; ?>
            </nav>
        </div>
        <div class="bottom-controls">
            <a href="../index.php" class="logout-button" onclick="handleLogout(event)">
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
            <h2>PPAS Quarterly Reports</h2>
        </div>

        <!-- Report Generation Form -->
        <div class="card report-card mb-2">
            <div class="card-body p-2">
                <form id="reportForm">
                    <div class="row align-items-end g-2">
                        <div class="col-md-3">
                            <label for="campus" class="form-label small mb-1"><i class="fas fa-university me-1"></i> Campus</label>
                            <select class="form-select theme-form-select" id="campus" style="height: 45px;" required <?php echo !$isCentral ? 'disabled' : ''; ?>>
                                <?php if (!$isCentral): ?>
                                <option value="<?php echo $userCampus; ?>" selected><?php echo $userCampus; ?></option>
                                <?php else: ?>
                                <option value="">Select Campus</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="year" class="form-label small mb-1"><i class="fas fa-calendar-alt me-1"></i> Year</label>
                            <select class="form-select theme-form-select" id="year" style="height: 45px;" required disabled>
                                <option value="">Select Year</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="quarter" class="form-label small mb-1"><i class="fas fa-calendar-check me-1"></i> Quarter</label>
                            <select class="form-select theme-form-select" id="quarter" style="height: 45px;" required disabled>
                                <option value="">Select Quarter</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="preparedBy" class="form-label small mb-1"><i class="fas fa-user-edit me-1"></i> Prepared By Position</label>
                            <select class="form-select theme-form-select" id="preparedBy" style="height: 45px;" disabled>
                                <option value="">Select Position</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="mb-4"></div>

        <!-- Report Preview Card -->
        <div class="card report-card">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Report Preview</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary btn-sm me-2" onclick="printReport()" style="border-radius: 6px;">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                      
                    </div>
                </div>
                <div id="reportPreview" class="table-responsive">
                    <!-- Report content will be loaded here -->
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <p>Select a campus and year to generate the report</p>
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
                generateReport();
            });

            // Check for stored theme preference and apply it
            const storedTheme = localStorage.getItem('theme') || 'light';
            setTheme(storedTheme);
        });

        // Enhanced theme toggle function
        function toggleTheme() {
            const currentTheme = $('html').attr('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
            localStorage.setItem('theme', newTheme);
        }

        function setTheme(theme) {
            $('html').attr('data-bs-theme', theme);
            
            // Update icon based on theme
            if (theme === 'dark') {
                $('#theme-icon').removeClass('fa-moon').addClass('fa-sun');
            } else {
                $('#theme-icon').removeClass('fa-sun').addClass('fa-moon');
            }
            
            // Also update any other theme-specific elements
            updateThemeSpecificElements(theme);
        }
        
        function updateThemeSpecificElements(theme) {
            // Optional: Any additional theme-specific updates can go here
        }

        function loadCampusOptions() {
            const campusSelect = $('#campus');
            campusSelect.prop('disabled', true);
            
            // Check if user is Central or regular user
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const userCampus = "<?php echo $userCampus ?>";
            
            if (isCentral) {
                // Default list of all BatStateU campuses
                const allCampuses = [
                    "Alangilan", 
                    "Central", 
                    "Pablo Borbon", 
                    "ARASOF-Nasugbu", 
                    "Balayan", 
                    "Lemery", 
                    "Lipa", 
                    "Lobo", 
                    "Mabini", 
                    "Malvar", 
                    "Rosario", 
                    "San Juan"
                ];
                
                // Central users can see all campuses
                campusSelect.empty().append('<option value="">Select Campus</option>');
                // Add "All Campuses" option for central users
                campusSelect.append(`<option value="All">All Campuses</option>`);
                allCampuses.forEach(function(campus) {
                    campusSelect.append(`<option value="${campus}">${campus}</option>`);
                });
                
                // Add change event handler
                campusSelect.on('change', function() {
                    const selectedCampus = $(this).val();
                    const yearSelect = $('#year');
                    const quarterSelect = $('#quarter');
                    const preparedBySelect = $('#preparedBy');
                    
                    // Clear downstream selections
                    yearSelect.empty().append('<option value="">Select Year</option>');
                    quarterSelect.empty().append('<option value="">Select Quarter</option>');
                    
                    // Reset prepared by dropdown
                    preparedBySelect.empty();
                    preparedBySelect.prop('disabled', true);
                    
                    // Configure prepared by options based on campus
                    if (selectedCampus === "Central" || selectedCampus === "All") {
                        // Central or All Campuses positions
                        preparedBySelect.append(`
                            <option value="">Select Position</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Extension Coordinator">Extension Coordinator</option>
                            <option value="Director, Extension Services">Director, Extension Services</option>
                            <option value="Vice President for RDES">Vice President for RDES</option>
                            <option value="Vice President for AF">Vice President for AF</option>
                        `);
                    } else if (selectedCampus) {
                        // Campus positions
                        preparedBySelect.append(`
                            <option value="">Select Position</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Extension Coordinator">Extension Coordinator</option>
                            <option value="GAD Head Secretariat">GAD Head Secretariat</option>
                    
                            <option value="Vice Chancellor for AF">Vice Chancellor for AF</option>
                        `);
                    }
                    
                    // Enable/disable year based on campus selection
                    if (selectedCampus) {
                        yearSelect.prop('disabled', false);
                    } else {
                        yearSelect.prop('disabled', true);
                        quarterSelect.prop('disabled', true);
                    }
                    
                    if (selectedCampus) {
                        loadAvailablePeriods();
                    }
                });
                
                campusSelect.prop('disabled', false);
            } else {
                // Non-central users can only see their own campus
                campusSelect.empty();
                campusSelect.append(`<option value="${userCampus}" selected>${userCampus}</option>`);
                campusSelect.prop('disabled', true);
                
                // For non-central users, enable the year dropdown since campus is pre-selected
                $('#year').prop('disabled', false);
                
                // Setup prepared by dropdown for non-central users
                const preparedBySelect = $('#preparedBy');
                preparedBySelect.empty();
                preparedBySelect.append(`
                    <option value="">Select Position</option>
                    <option value="Faculty">Faculty</option>
                    <option value="Extension Coordinator">Extension Coordinator</option>
                    <option value="GAD Head Secretariat">GAD Head Secretariat</option>
                    <option value="Vice Chancellor for RDES">Vice Chancellor for RDES</option>
                    <option value="Vice Chancellor for AF">Vice Chancellor for AF</option>
                `);
                preparedBySelect.prop('disabled', true);
                
                // Automatically load years for the user's campus
                loadAvailablePeriods();
            }
        }

        function loadAvailablePeriods() {
            const yearSelect = $('#year');
            const quarterSelect = $('#quarter');
            const preparedBySelect = $('#preparedBy');
            
            // Show loading state
            yearSelect.prop('disabled', true);
            yearSelect.empty().append('<option value="">Loading years...</option>');
            
            // Keep quarter and prepared by disabled while loading years
            quarterSelect.prop('disabled', true);
            quarterSelect.empty().append('<option value="">Select Quarter</option>');
            preparedBySelect.prop('disabled', true);
            
            // Get the campus value
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const campus = isCentral ? $('#campus').val() : "<?php echo $userCampus ?>";
            const isAllCampuses = campus === 'All';
            
            // Log for debugging
            console.log("Requesting periods for campus:", campus, "isAllCampuses:", isAllCampuses);
            
            $.ajax({
                url: 'get_available_periods.php',
                method: 'GET',
                data: { 
                    campus: campus,
                    all_campuses: isAllCampuses ? 1 : 0 
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Periods response:', response); // Debug log
                    
                    if (response.success && response.data) {
                        // Enable year dropdown
                        yearSelect.prop('disabled', false);
                        yearSelect.empty().append('<option value="">Select Year</option>');
                        
                        // Get years and sort them in descending order
                        const years = Object.keys(response.data).sort((a, b) => b - a);
                        
                        // Add years to dropdown
                        years.forEach(year => {
                            yearSelect.append(`<option value="${year}">${year}</option>`);
                        });
                        
                        // Remove any existing change handlers to prevent duplicates
                        yearSelect.off('change');
                        
                        // Handle year change
                        yearSelect.on('change', function() {
                            const selectedYear = $(this).val();
                            quarterSelect.empty().append('<option value="">Select Quarter</option>');
                            
                            // Enable/disable quarter based on year selection
                            if (selectedYear) {
                                quarterSelect.prop('disabled', false);
                                
                                if (response.data[selectedYear]) {
                                    // Sort quarters
                                    const quarters = response.data[selectedYear].sort();
                                    quarters.forEach(quarter => {
                                        const quarterLabel = {
                                            'Q1': '1st Quarter',
                                            'Q2': '2nd Quarter',
                                            'Q3': '3rd Quarter',
                                            'Q4': '4th Quarter'
                                        }[quarter] || quarter;
                                        quarterSelect.append(`<option value="${quarter}">${quarterLabel}</option>`);
                                    });
                                }
                            } else {
                                quarterSelect.prop('disabled', true);
                                preparedBySelect.prop('disabled', true);
                            }
                            
                            // Add change handler for quarter to enable prepared by
                            quarterSelect.off('change').on('change', function() {
                                const selectedQuarter = $(this).val();
                                if (selectedQuarter) {
                                    preparedBySelect.prop('disabled', false);
                                } else {
                                    preparedBySelect.prop('disabled', true);
                                }
                            });
                            
                            // Add auto-generate functionality when prepared by changes
                            preparedBySelect.off('change').on('change', function() {
                                const selectedPosition = $(this).val();
                                if (selectedPosition) {
                                    // Check if all required fields are filled
                                    const campusValue = $('#campus').val();
                                    const yearValue = $('#year').val();
                                    const quarterValue = $('#quarter').val();
                                    
                                    if (campusValue && yearValue && quarterValue) {
                                        console.log('Auto-generating report after prepared by position changed to:', selectedPosition);
                                        // Automatically generate the report
                                        generateReport();
                                    }
                                }
                            });
                        });
                    } else {
                        console.error('Invalid response format:', response);
                        yearSelect.empty().append('<option value="">No years available</option>');
                        quarterSelect.empty().append('<option value="">No quarters available</option>');
                        
                        // Ensure quarter and prepared by remain disabled
                        quarterSelect.prop('disabled', true);
                        preparedBySelect.prop('disabled', true);
                        
                        // Show error message
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Data Available',
                            text: response.message || 'No periods available for this campus'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading periods:', error);
                    yearSelect.empty().append('<option value="">Error loading years</option>');
                    quarterSelect.empty().append('<option value="">Error loading quarters</option>');
                    
                    // Ensure quarter and prepared by remain disabled
                    quarterSelect.prop('disabled', true);
                    preparedBySelect.prop('disabled', true);
                    
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load available periods. Please try again.'
                    });
                },
                complete: function() {
                    // If no year is selected, disable quarter and prepared by dropdowns
                    if (!yearSelect.val()) {
                        quarterSelect.prop('disabled', true);
                        preparedBySelect.prop('disabled', true);
                    }
                }
            });
        }

        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        function generateReport() {
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const selectedCampus = isCentral ? $('#campus').val() : "<?php echo $userCampus ?>";
            const selectedYear = $('#year').val();
            const selectedQuarter = $('#quarter').val();
            const selectedPosition = $('#preparedBy').val();
            const isAllCampuses = selectedCampus === 'All';

            if (!selectedCampus || !selectedYear || !selectedQuarter || !selectedPosition) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select all required fields to generate the report.'
                });
                return;
            }

            console.log('Generating report for campus:', selectedCampus, 'year:', selectedYear, 'quarter:', selectedQuarter, 'position:', selectedPosition);

            // Show loading state
            Swal.fire({
                title: 'Generating Report',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // First load signatories before generating the report
            // The loadSignatories function will now automatically use the selected campus
            loadSignatories().then(signatoriesData => {
                console.log('Loaded signatories for campus:', selectedCampus, signatoriesData);
                
                // Fetch report data
                $.ajax({
                    url: 'get_ppas_report.php',
                    method: 'GET',
                    data: {
                        campus: selectedCampus,
                        year: selectedYear,
                        quarter: selectedQuarter,
                        position: selectedPosition,
                        all_campuses: isAllCampuses ? 1 : 0
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        
                        if (response.success) {
                            displayReport(response.data, selectedYear, selectedQuarter, signatoriesData, selectedCampus, selectedPosition, isAllCampuses);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to load report data'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Report error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load the report. Please try again.'
                        });
                    }
                });
            }).catch(error => {
                console.error('Error loading signatories:', error);
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load signatories. Please try again.'
                });
            });
        }

        function displayReport(data, year, quarter, signatories, selectedCampus, selectedPosition, isAllCampuses) {
            const quarterText = {
                'Q1': '1st Quarter',
                'Q2': '2nd Quarter',
                'Q3': '3rd Quarter',
                'Q4': '4th Quarter'
            }[quarter] || quarter;
            
            const preview = $('#reportPreview');
            
            // Get the signatory names and positions
            let preparedByName = 'RICHELLE M. SULIT';
            let preparedByPosition = selectedPosition || 'GAD Head Secretariat'; // Use the selected position
            let checkedByName = 'DR. FRANCIS G. BALAZON';
            let checkedByPosition = 'Vice Chancellor for RDES';
            let verifiedByName = 'ATTY. ALVIN R. DE SILVA';
            let verifiedByPosition = 'Chancellor';
            let asstDirectorName = 'ASSOC. PROF. MARIA THERESA A. HERNANDEZ';
            let asstDirectorPosition = 'Assistant Director for GAD Advocacies';
            
            // Extract signatories if available
            if (signatories && signatories.length > 0) {
                console.log('Using signatories data:', signatories);
                
                // Find the right signatory based on selected position
                const signatory = signatories[0] || {}; // Use first signatory record by default
                
                // Map name based on the selected position
                if (selectedPosition) {
                    switch(selectedPosition) {
                        case 'Faculty':
                            preparedByName = signatory.name7 || signatory.dean || 'Faculty Name';
                            break;
                        case 'Extension Coordinator':
                            preparedByName = signatory.name5 || signatory.head_extension_services || 'Extension Coordinator';
                            break;
                        case 'GAD Head Secretariat':
                            preparedByName = signatory.name1 || signatory.gad_head_secretariat || 'GAD Head Secretariat';
                            break;
                        case 'Vice Chancellor for RDES':
                            preparedByName = signatory.name2 || signatory.vice_chancellor_rde || 'Vice Chancellor for RDES';
                            break;
                        case 'Vice Chancellor for AF':
                            preparedByName = signatory.name6 || signatory.vice_chancellor_admin_finance || 'Vice Chancellor for AF';
                            break;
                        case 'Director, Extension Services':
                            preparedByName = signatory.name5 || signatory.head_extension_services || 'Director, Extension Services';
                            break;
                        case 'Vice President for RDES':
                            preparedByName = signatory.name2 || signatory.vice_chancellor_rde || 'Vice President for RDES';
                            break;
                        case 'Vice President for AF':
                            preparedByName = signatory.name6 || signatory.vice_chancellor_admin_finance || 'Vice President for AF';
                            break;
                        default:
                            preparedByName = signatory.name1 || 'RICHELLE M. SULIT';
                    }
                }
                
                // Always use these signatories for the other positions
                if (signatory.name2) {
                    checkedByName = signatory.name2;
                }
                
                if (signatory.name3) {
                    verifiedByName = signatory.name3;
                }
                
                if (signatory.name4) {
                    asstDirectorName = signatory.name4;
                }
            }
            
            console.log('Final signatory values:');
            console.log('Prepared by:', preparedByName, preparedByPosition);
            console.log('Checked by:', checkedByName, checkedByPosition);
            console.log('Verified by:', verifiedByName, verifiedByPosition);
            console.log('Asst Director:', asstDirectorName, asstDirectorPosition);
            
            // Apply print styles
            if (!$('#reportPrintStyles').length) {
                $('head').append(`
                    <style id="reportPrintStyles">
                        @media print {
                            @page {
                                size: A4 landscape !important;
                                margin: 0.2cm !important;
                            }
                            
                            #reportTable {
                                width: 100% !important;
                                max-width: 100% !important;
                                border-collapse: collapse !important;
                                table-layout: fixed !important;
                                page-break-inside: auto !important;
                                border: 1px solid #000 !important;
                                font-size: 7pt !important;
                            }
                            
                            #reportTable th, #reportTable td {
                                border: 1px solid #000 !important;
                                border-right: 1px solid #000 !important;
                                border-left: 1px solid #000 !important;
                                box-sizing: border-box !important;
                                padding: 2px !important;
                                font-size: 7pt !important;
                            }

                            #reportTable td *, #reportTable th *, #reportTable span, #reportTable strong {
                                font-size: 7pt !important;
                            }
                            
                            #reportTable thead {
                                display: table-header-group !important;
                            }
                            
                            #reportTable thead tr img {
                                height: 30px !important;
                            }
                            
                            #reportTable tr {
                                page-break-inside: avoid !important;
                                page-break-after: auto !important;
                                border-right: 1px solid #000 !important;
                            }
                            
                            #reportTable tr td:last-child, 
                            #reportTable tr th:last-child {
                                border-right: 1px solid #000 !important;
                            }
                        }
                    </style>
                `);
            }
            
            // Generate the table header with compact styling
            let html = `
                <table class="table table-bordered" id="reportTable" style="border-collapse: collapse; width: 100%; border: 1px solid #000; table-layout: fixed; font-size: 7pt;">
                    <colgroup>
                        <col style="width: 10%;">  <!-- Gender Issue -->
                        ${isAllCampuses ? '<col style="width: 5%;">' : ''}  <!-- Campus (only for All Campuses) -->
                        <col style="width: 12%;">  <!-- Title -->
                        <col style="width: 7%;">   <!-- Date/Duration -->
                        <col style="width: 10%;">  <!-- Participants -->
                        <col style="width: 6%;">   <!-- Type -->
                        <col style="width: 5%;">   <!-- Location -->
                        <col style="width: 15%;">  <!-- Personnel -->
                        <col style="width: 6%;">   <!-- Budget -->
                        <col style="width: 6%;">   <!-- Cost -->
                        <col style="width: 6%;">   <!-- PS Attribution -->
                        <col style="width: 4%;">   <!-- Source -->
                    </colgroup>
                    <thead>
                        <!-- First 4 rows are the document headers that shouldn't repeat -->
                        <tr class="no-repeat-header">
                            <td colspan="${isAllCampuses ? '4' : '3'}" style="width: 20%; border: 1px solid #000;">
                                <img src="../images/BatStateU-NEU-Logo.png" alt="BatStateU Logo" style="height: 40px; display: block; margin: 5px auto;">
                            </td>
                            <td colspan="3" style="width: 30%; padding: 5px; border: 1px solid #000; font-size: 7pt;">Reference No.: BatStateU-FO-ESO-03</td>
                            <td colspan="4" style="width: 30%; padding: 5px; border: 1px solid #000; font-size: 7pt;">Effectivity Date: January 03, 2024</td>
                            <td colspan="1" style="width: 10%; padding: 5px; border: 1px solid #000; font-size: 7pt;">Revision No.: 02</td>
                        </tr>
                        <tr class="no-repeat-header">
                            <td colspan="${isAllCampuses ? '12' : '11'}" style="text-align: center; padding: 8px; border: 1px solid #000;">
                                <div style="font-weight: bold; font-size: 12pt;">Quarterly Report of GAD Programs, Projects and Activities (PPAs)</div>
                            </td>
                        </tr>
                        <tr class="no-repeat-header">
                            <td colspan="${isAllCampuses ? '12' : '11'}" style="text-align: center; padding: 5px; border: 1px solid #000; font-size: 7pt;">
                                ${quarterText.split(' ')[0]} Quarter, FY ${year}
                            </td>
                        </tr>
                        <tr class="no-repeat-header">
                            <td colspan="${isAllCampuses ? '12' : '11'}" style="padding: 5px; border: 1px solid #000; font-size: 7pt;"> &nbsp; Campus: ${isAllCampuses ? 'All Campuses' : selectedCampus}</td>
                        </tr>
                        <!-- This is the column headers row that can repeat on each page -->
                        <tr style="background-color: #f2f2f2; text-align: center; font-size: 7pt;">
                            <th style="vertical-align: middle; border: 1px solid #000; padding: 2px;">Gender\nIssue/s*</th>
                            ${isAllCampuses ? '<th style="vertical-align: middle; border: 1px solid #000; padding: 2px;">Campus</th>' : ''}
                            <th style="vertical-align: middle; border: 1px solid #000; padding: 2px;">Title of\nImplemented\nPPAs</th>
                            <th style="vertical-align: middle; border: 1px solid #000; padding: 2px;">Date/\nDuration</th>
                            <th style="vertical-align: middle; border: 1px solid #000; padding: 2px;">No. of\nParticipants</th>
                            <th style="vertical-align: middle; border: 1px solid #000; padding: 2px;">Type of\nBeneficiaries</th>
                            <th style="vertical-align: middle; border: 1px solid #000; padding: 2px;">Location</th>
                            <th style="vertical-align: middle; border: 1px solid #000; padding: 2px;">Personnel</th>
                            <th style="vertical-align: middle; border: 1px solid #000; padding: 2px;">Approved\nBudget</th>
                            <th style="vertical-align: middle; border: 1px solid #000; padding: 2px;">Actual\nCost</th>
                            <th style="vertical-align: middle; border: 1px solid #000; padding: 2px;">PS\nAttribution</th>
                            <th style="vertical-align: middle; border: 1px solid #000; padding: 2px;">Source</th>
                        </tr>
                    </thead>
                    <tbody>`;
            
            // Variables to hold budget totals
            let totalOtherSources = 0;
            let totalMdsGad = 0;
            let overallTotal = 0;
            
            // Check if there's data
            if (!data || data.length === 0) {
                html += `<tr><td colspan="${isAllCampuses ? '12' : '11'}" style="text-align: center; padding: 10px; border: 1px solid #000;">No data available for this period</td></tr>`;
            } else {
                // Group data by gender issue first
                const groupedByIssue = {};
                
                // First pass: group rows by gender issue
                data.forEach(report => {
                    const issue = report.gender_issue || 'Undefined Issue';
                    if (!groupedByIssue[issue]) {
                        groupedByIssue[issue] = [];
                    }
                    groupedByIssue[issue].push(report);
                });
                
                // Second pass: generate rows for each gender issue group
                Object.keys(groupedByIssue).forEach(issue => {
                    const reports = groupedByIssue[issue];
                    const rowspan = reports.length;
                    
                    // Process each report in the group
                    reports.forEach((report, index) => {
                        // Format the title column with Program, Project, Activity vertically
                        const titleColumn = `
                            <span style="font-size: 7pt;"><strong>Program:</strong> ${report.program || 'N/A'}</span><br>
                            <span style="font-size: 7pt;"><strong>Project:</strong> ${report.project || 'N/A'}</span><br>
                            <span style="font-size: 7pt;"><strong>Activity:</strong> ${report.activity || 'N/A'}</span>
                        `;
                        
                        // Format the date and duration column
                        const dateDurationColumn = `
                            <span style="font-size: 7pt;"><strong>Date:</strong> ${report.start_date === report.end_date 
                                ? report.start_date 
                                : `${report.start_date} to ${report.end_date}`}</span><br>
                            <span style="font-size: 7pt;"><strong>Duration:</strong> ${report.duration} hrs.</span>
                        `;
                        
                        // Format participants
                        const students = report.participants.students;
                        const faculty = report.participants.faculty;
                        const external = report.participants.external;
                        
                        const internalMale = parseInt(students.male) + parseInt(faculty.male);
                        const internalFemale = parseInt(students.female) + parseInt(faculty.female);
                        const externalMale = parseInt(external.male);
                        const externalFemale = parseInt(external.female);
                        const totalMale = internalMale + externalMale;
                        const totalFemale = internalFemale + externalFemale;
                        const totalParticipants = totalMale + totalFemale;
                        
                        const participantsBreakdown = `
                            <span style="font-size: 7pt;"><strong>Internal:</strong></span><br>
                            <span style="font-size: 7pt;">Male-${internalMale} Female-${internalFemale}</span><br>
                            <span style="font-size: 7pt;"><strong>External:</strong></span><br>
                            <span style="font-size: 7pt;">Male-${externalMale} Female-${externalFemale}</span><br>
                            <span style="font-size: 7pt;"><strong>Total:</strong> ${totalParticipants}</span>
                        `;
                        
                        // Type of beneficiaries
                        const beneficiaryTypes = [];
                        if (parseInt(students.male) + parseInt(students.female) > 0) beneficiaryTypes.push("Students");
                        if (parseInt(faculty.male) + parseInt(faculty.female) > 0) beneficiaryTypes.push("Faculty");
                        if (external.type && (parseInt(external.male) + parseInt(external.female) > 0)) beneficiaryTypes.push(external.type);
                        
                        const beneficiaryTypesText = beneficiaryTypes.length > 0 ? beneficiaryTypes.join("<br>") : "N/A";
                        
                        // Format personnel data using the academic rank and name format
                        let personnelHtml = '';
                        report.personnel.forEach(p => {
                            const parts = p.split(':');
                            if (parts.length > 1) {
                                const role = parts[0];
                                const nameWithRank = parts[1].trim().split(',');
                                
                                // Default if no rank information is available
                                let name = nameWithRank[0];
                                let rank = '';
                                
                                // If rank information is available
                                if (nameWithRank.length > 1) {
                                    rank = nameWithRank[1].trim();
                                }
                                
                                personnelHtml += `
                                    <span style="font-size: 7pt;"><strong>${role}:</strong></span>
                                    <span style="display: block; margin-left: 3px; margin-bottom: 2px; font-size: 7pt;">
                                        ${rank ? rank + ' ' : ''}${name}
                                    </span>
                                `;
                            } else {
                                personnelHtml += `<span style="display: block; margin-bottom: 2px; font-size: 7pt;">${p}</span>`;
                            }
                        });
                        
                        // Add budget to appropriate total based on source
                        const budget = parseFloat(report.budget) || 0;
                        if (report.source_of_budget === 'GAA' || report.source_of_budget === 'MDS-GAD') {
                            totalMdsGad += budget;
                        } else {
                            // This part is no longer used for totalOtherSources calculation
                        }
                        
                        // Get PS attribution amount
                        const psAttribution = parseFloat(report.ps_attribution) || 0;
                        totalOtherSources += psAttribution;
                        
                        // Add both budget and PS attribution to the overall total
                        overallTotal = totalMdsGad + totalOtherSources;
                        
                        // Start a new row
                        html += `<tr${index > 0 ? ' style="border-top: none;"' : ''}>`;
                        
                        // Only add the gender issue cell on the first row of each group
                        if (index === 0) {
                            html += `<td style="border: 1px solid #000; padding: 1px; text-align: center; vertical-align: middle; font-size: 7pt;" rowspan="${rowspan}">${issue}</td>`;
                        }
                        
                        // Add all other cells with modified border-top for rows after the first in a group
                        const borderStyle = index > 0 ? 'border-top: 1px solid #ddd;' : '';
                        
                        // Add campus column if showing all campuses
                        if (isAllCampuses) {
                            html += `<td style="border: 1px solid #000; ${borderStyle} padding: 1px; font-size: 7pt; font-weight: bold;">${report.campus || 'N/A'}</td>`;
                        }
                        
                        html += `
                            <td style="border: 1px solid #000; ${borderStyle} padding: 1px; font-size: 7pt;">${titleColumn}</td>
                            <td style="border: 1px solid #000; ${borderStyle} padding: 1px; font-size: 7pt;">${dateDurationColumn}</td>
                            <td style="border: 1px solid #000; ${borderStyle} padding: 1px; font-size: 7pt;">${participantsBreakdown}</td>
                            <td style="border: 1px solid #000; ${borderStyle} padding: 1px; font-size: 7pt;">${beneficiaryTypesText}</td>
                            <td style="border: 1px solid #000; ${borderStyle} padding: 1px; font-size: 7pt;">${report.location}</td>
                            <td style="border: 1px solid #000; ${borderStyle} padding: 1px; font-size: 7pt;">${personnelHtml}</td>
                            <td style="border: 1px solid #000; ${borderStyle} padding: 1px; text-align: right; font-size: 7pt;">₱${parseFloat(report.budget).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td style="border: 1px solid #000; ${borderStyle} padding: 1px; text-align: right; font-size: 7pt;">₱${parseFloat(report.actual_cost).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td style="border: 1px solid #000; ${borderStyle} padding: 1px; text-align: right; font-size: 7pt;">₱${parseFloat(report.ps_attribution || 0).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td style="border: 1px solid #000; ${borderStyle} padding: 1px; font-size: 7pt;">${report.source_of_budget || 'N/A'}</td>
                        `;
                        
                        // Close the row
                        html += `</tr>`;
                    });
                });
            }
            
            // Add subtotal and total rows
            html += `
                <tr>
                    <td colspan="${isAllCampuses ? '11' : '10'}" style="text-align: right; padding: 1px; border: 1px solid #000; font-size: 7pt;"><strong style="font-size: 7pt;">Subtotal (Other Sources)</strong></td>
                    <td colspan="1" style="text-align: right; padding: 1px; border: 1px solid #000; font-size: 7pt;">₱ ${totalOtherSources.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                </tr>
                <tr>
                    <td colspan="${isAllCampuses ? '11' : '10'}" style="text-align: right; padding: 1px; border: 1px solid #000; font-size: 7pt;"><strong style="font-size: 7pt;">Subtotal (MDS-GAD)</strong></td>
                    <td colspan="1" style="text-align: right; padding: 1px; border: 1px solid #000; font-size: 7pt;">₱ ${totalMdsGad.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                </tr>
                <tr>
                    <td colspan="${isAllCampuses ? '11' : '10'}" style="text-align: right; padding: 1px; border: 1px solid #000; font-size: 7pt;"><strong style="font-size: 7pt;">TOTAL</strong></td>
                    <td colspan="1" style="text-align: right; padding: 1px; font-weight: bold; color: #007bff; border: 1px solid #000; font-size: 7pt;">₱ ${overallTotal.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                </tr>
            `;
            
            // Add signature rows with nested table for equal distribution
            html += `
                <tr>
                    <td colspan="${isAllCampuses ? '12' : '11'}" style="padding: 0; border: none;">
                        <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                            <tr>
                                <td style="width: 25%; padding: 1px; text-align: center; border: 1px solid #000; font-size: 7pt;">
                                    <span style="font-size: 7pt;">Prepared by:</span><br>
                                    <strong style="font-size: 7pt;">${preparedByName}</strong><br>
                                    <span style="font-size: 7pt;">${preparedByPosition}</span><br>
                                    <span style="font-size: 7pt;">Date Signed: _______________</span>
                                </td>
                                <td style="width: 25%; padding: 1px; text-align: center; border: 1px solid #000; font-size: 7pt;">
                                    <span style="font-size: 7pt;">Checked by:</span><br>
                                    <strong style="font-size: 7pt;">${checkedByName}</strong><br>
                                    <span style="font-size: 7pt;">${checkedByPosition}</span><br>
                                    <span style="font-size: 7pt;">Date Signed: _______________</span>
                                </td>
                                <td style="width: 25%; padding: 1px; text-align: center; border: 1px solid #000; font-size: 7pt;">
                                   <br>
                                    <strong style="font-size: 7pt;">${verifiedByName}</strong><br>
                                    <span style="font-size: 7pt;">${verifiedByPosition}</span><br>
                                    <span style="font-size: 7pt;">Date Signed: _______________</span>
                                </td>
                                <td style="width: 25%; padding: 1px; text-align: center; border: 1px solid #000; font-size: 7pt;">
                                    <span style="font-size: 7pt;">Verified by:</span><br>
                                    <strong style="font-size: 7pt;">${asstDirectorName}</strong><br>
                                    <span style="font-size: 7pt;">${asstDirectorPosition}</span><br>
                                    <span style="font-size: 7pt;">Date Signed: _______________</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="${isAllCampuses ? '12' : '11'}" style="padding: 1px; font-style: italic; border: 1px solid #000; font-size: 7pt;">
                        Required Attachments: Signed and approved: (1) PPA Proposal or Request Letter
                        (2) Narrative or Evaluation Report of the PPAs implemented
                    </td>
                </tr>
                <tr>
                    <td colspan="${isAllCampuses ? '12' : '11'}" style="padding: 1px; font-style: italic; border: 1px solid #000; font-size: 7pt;">
                        *based on the gender issue in the campus annual GPB
                    </td>
                </tr>
            `;
            
            // Close the table
            html += `</tbody></table>`;
            
            // Set the content
            preview.html(html);
        }

        function printReport() {
            const table = document.getElementById('reportTable');
            if (!table) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Data',
                    text: 'Please generate a report first'
                });
                return;
            }

            // Create a print window with a specific title
            const printWindow = window.open('', '_blank', 'width=1200,height=800');
            
            // Set window properties immediately to prevent about:blank
            printWindow.document.open();
            printWindow.document.title = "PPAS Report";
            
            const reportContent = $('#reportPreview').html();
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>PPAS Report</title>
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @page {
                            size: A4 landscape !important;
                            margin: 0.3cm !important;
                        }

                        /* Reset body/html styles */
                        body, html {
                            margin: 0 !important;
                            padding: 0 !important;
                            width: 100% !important;
                            height: auto !important;
                            background: white !important;
                            font-family: Times New Roman, sans-serif !important;
                        }

                        /* Hide non-printable elements */
                        .sidebar, .card-header, .btn-group, .page-title, 
                        .mobile-nav-toggle, .theme-switch-button, .card,
                        .nav-content, .bottom-controls, .datetime-container {
                            display: none !important;
                        }

                        /* Table container styles */
                        .table-responsive {
                            overflow: visible !important;
                            width: 100% !important;
                            margin: 0 !important;
                            padding: 0 !important;
                        }

                        /* Core table styles */
                        #reportTable {
                            width: 100% !important;
                            max-width: none !important;
                            border-collapse: collapse !important;
                            font-size: 7pt !important;
                            margin: 0 !important;
                            padding: 0 !important;
                            page-break-inside: auto !important;
                            table-layout: fixed !important;
                            border: 1px solid black !important;
                            box-sizing: border-box !important;
                        }

                        /* Cell styles */
                        #reportTable th, 
                        #reportTable td {
                            border: 1px solid black !important;
                            border-right: 1px solid black !important;
                            padding: 4px !important;
                            vertical-align: top !important;
                            word-wrap: break-word !important;
                            font-size: 7pt !important;
                        }
                        
                        /* Ensure all text within cells has consistent font size */
                        #reportTable td *, #reportTable th *, 
                        #reportTable span, #reportTable strong,
                        #reportTable p, #reportTable div {
                            font-size: 7pt !important;
                        }
                            overflow-wrap: break-word !important;
                            white-space: normal !important;
                            line-height: 1.2 !important;
                            font-size: 8pt !important;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }

                        /* THIS IS THE CRITICAL PART: Only show header rows with column headers, hide document headers on subsequent pages */
                        @media print {
                            /* Make thead display as header group but only show the last row (column headers) */
                            #reportTable thead {
                                display: table-header-group !important;
                            }
                            
                            /* Hide the first 4 rows of the thead (document headers) when they would repeat */
                            #reportTable thead tr.no-repeat-header {
                                display: none !important;
                            }
                            
                            /* But show them on the first page */
                            #reportTable thead tr.no-repeat-header:first-of-type {
                                display: table-row !important;
                            }
                        }
                        
                        /* Force document headers visible on the first page */
                        #reportTable thead tr.no-repeat-header {
                            display: table-row !important;
                        }
                        
                        /* Make sure column headers are always shown, especially when break to next page */
                        #reportTable thead tr:last-child {
                            display: table-row !important;
                        }
                    </style>
                </head>
                <body class="p-0">
                    ${reportContent}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        }

        function exportToWord() {
            const table = document.getElementById('reportTable');
            if (!table) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Data',
                    text: 'Please generate a report first'
                });
                return;
            }

            const selectedYear = $('#year').val();
            const selectedQuarter = $('#quarter').val();
            
            // Create a new blob with HTML content
            const reportContent = document.getElementById('reportPreview').innerHTML;
            
            // HTML for Word export with simple header control
            const html = `
                <html xmlns:o="urn:schemas-microsoft-com:office:office" 
                      xmlns:w="urn:schemas-microsoft-com:office:word" 
                      xmlns="http://www.w3.org/TR/REC-html40">
                <head>
                    <meta charset="utf-8">
                    <title>PPAS Report</title>
                    <style>
                        table {
                            border-collapse: collapse;
                            width: 100%;
                            margin-bottom: 10px;
                            table-layout: fixed;
                        }
                        table, th, td {
                            border: 1px solid black;
                            padding: 4px;
                        }
                        th {
                            background-color: #f2f2f2;
                            text-align: center;
                            font-weight: bold;
                        }
                        td {
                            vertical-align: middle;
                        }
                        
                        /* Column widths */
                        #reportTable th:nth-child(1), #reportTable td:nth-child(1) { width: 15%; }  /* Gender Issue */
                        #reportTable th:nth-child(2), #reportTable td:nth-child(2) { width: 13%; }  /* Title */
                        #reportTable th:nth-child(3), #reportTable td:nth-child(3) { width: 10%; }  /* Date/Duration */
                        #reportTable th:nth-child(4), #reportTable td:nth-child(4) { width: 10%; }  /* Participants */
                        #reportTable th:nth-child(5), #reportTable td:nth-child(5) { width: 7%; }   /* Type */
                        #reportTable th:nth-child(6), #reportTable td:nth-child(6) { width: 6%; }   /* Location */
                        #reportTable th:nth-child(7), #reportTable td:nth-child(7) { width: 25%; }  /* Personnel */
                        #reportTable th:nth-child(8), #reportTable td:nth-child(8) { width: 6%; }   /* Budget */
                        #reportTable th:nth-child(9), #reportTable td:nth-child(9) { width: 6%; }   /* Cost */
                        #reportTable th:nth-child(10), #reportTable td:nth-child(10) { width: 6%; } /* PS Attribution */
                        #reportTable th:nth-child(11), #reportTable td:nth-child(11) { width: 3%; } /* Source */
                        
                        /* Personnel column specific styling */
                        #reportTable td:nth-child(7) {
                            line-height: 1.4 !important;
                            padding: 8px !important;
                            white-space: pre-line !important;
                        }
                        
                        #reportTable td:nth-child(7) span {
                            display: block !important;
                            margin-bottom: 8px !important;
                        }
                        
                        #reportTable td:nth-child(7) span:last-child {
                            margin-bottom: 0 !important;
                        }
                        
                        /* PS Attribution column styling */
                        #reportTable td:nth-child(10) {
                            text-align: right !important;
                            padding: 8px !important;
                            font-family: monospace !important;
                        }
                        
                        /* Logo styling */
                        img {
                            height: 60px;
                            display: block;
                            margin: 0 auto;
                        }
                        
                        /* Simple fix for Word documents to handle headers correctly */
                        /* This prevents the document header from repeating on each page in Word */
                        @page Section1 {
                            mso-header: h1;
                        }
                        
                        div.Section1 { page:Section1; }
                        
                        /* Word-specific settings to control headers */
                        #reportTable tr.no-repeat-header {
                            display: table-row;
                        }
                        
                        /* Only print the last row of the header on page breaks */
                        mso-element:header {
                            display: none;
                        }
                    </style>
                </head>
                <body>
                    <div class="Section1">
                        ${reportContent}
                    </div>
                </body>
                </html>
            `;
            
            const blob = new Blob([html], { type: 'application/msword' });
            const url = URL.createObjectURL(blob);
            
            // Create download link
            const link = document.createElement('a');
            link.href = url;
            link.download = `PPAS_Report_${selectedYear}_${selectedQuarter}.doc`;
            
            // Trigger download
            document.body.appendChild(link);
            link.click();
            
            // Cleanup
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
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

        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Function to load signatories from the database
        async function loadSignatories() {
            try {
                // For central users, get the selected campus
                const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
                let selectedCampus = isCentral ? $('#campus').val() : '';
                
                // If "All Campuses" is selected, use Central's signatories
                if (selectedCampus === 'All') {
                    selectedCampus = 'Central';
                }
                
                console.log('Loading signatories - isCentral:', isCentral, 'selectedCampus:', selectedCampus);
                
                // Get signatories from database
                let url = 'get_all_signatories.php';
                if (isCentral && selectedCampus) {
                    url += `?campus=${encodeURIComponent(selectedCampus)}`;
                }
                
                console.log('Fetching signatories from:', url);
                const response = await fetch(url);
                const result = await response.json();
                
                if (result.status === 'success' && result.data && result.data.length > 0) {
                    console.log('Loaded signatories from database:', result.data);
                    return result.data;
                } else {
                    console.error('No signatories found in database');
                    return [];
                }
            } catch (error) {
                console.error('Error fetching signatories:', error);
                return [];
            }
        }
    </script>

    <!-- Add necessary libraries -->
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        // Apply theme styling after page load to ensure everything is properly styled
        $(document).ready(function() {
            // Use the stored theme preference
            const savedTheme = localStorage.getItem('theme') || 'light';
            
            // Apply specific styles to form elements
            $('.form-select').addClass('theme-form-select');
            
            // If any form selects still have incorrect styling, force refresh them based on current theme
            setTimeout(function() {
                if (savedTheme === 'light') {
                    $('.form-select').css({
                        'background-color': '#ffffff', 
                        'color': '#212529', 
                        'font-weight': 'normal',
                        'border': '1px solid #dee2e6'
                    });
                    
                    // Ensure form labels are visible in light mode
                    $('.form-label').css({
                        'color': '#333333',
                        'font-weight': 'normal'
                    });
                } else {
                    // Dark mode styles
                    $('.form-select').css({
                        'background-color': '#212529', 
                        'color': '#ffffff', 
                        'font-weight': 'normal',
                        'border': '1px solid #495057'
                    });
                    
                    // Dark mode label styles
                    $('.form-label').css({
                        'color': '#b3b3b3',
                        'font-weight': '400'
                    });
                }
            }, 100);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            updateThemeIcon(savedTheme);

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
        });
    </script>
    <script>
        function updateNotificationBadge(endpoint, action, badgeId) {
    const badge = document.getElementById(badgeId);
    if (!badge) return;
    
    const formData = new FormData();
    formData.append('action', action);
    
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    })
    .catch(error => console.error('Error fetching count:', error));
}

// Initial load and periodic updates
document.addEventListener('DOMContentLoaded', function() {
    // For approval badge
    updateNotificationBadge('../approval/gbp_api.php', 'count_pending', 'approvalBadge');
    
    // Set interval for updates (only if not on the page with that badge active)
    const isApprovalPage = document.querySelector('.approval-link.active');
    if (!isApprovalPage) {
        setInterval(() => {
            updateNotificationBadge('../approval/gbp_api.php', 'count_pending', 'approvalBadge');
        }, 30000); // Update every 30 seconds
    }
});

    </script>
</body>
</html>
