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
    <title>GPB Reports - GAD System</title>
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
            --dark-bg: #212529;
            --dark-input: #2b3035;
            --dark-text: #e9ecef;
            --dark-border: #495057;
            --dark-sidebar: #2d2d2d;
            --table-header-bg: #343a40;
            --table-subheader-bg: #2b3035;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
            opacity: 1;
            transition: opacity 0.05s ease-in-out;
            /* Changed from 0.05s to 0.01s - make it super fast */
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
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.05), 0 5px 15px rgba(0, 0, 0, 0.05);
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
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
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
                box-shadow: 5px 0 25px rgba(0, 0, 0, 0.1);
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
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
                background: rgba(0, 0, 0, 0.5);
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
            min-height: auto;
            /* Remove fixed min-height */
        }

        .card-body {
            padding: 1rem;
            /* Reduce padding */
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

        /* Print Styles */
        @media print {
            @page {
                size: landscape;
                margin: 0;
            }

            /* Remove browser headers/footers */
            @page :first {
                margin-top: 0;
                margin-bottom: 0;
            }

            @page :left {
                margin-left: 0;
            }

            @page :right {
                margin-right: 0;
            }

            /* Hide all default headers/footers */
            html {
                margin: 0 !important;
                padding: 0 !important;
            }

            body {
                margin: 1cm !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                overflow: visible !important;
            }

            /* Hide scrollbars when printing */
            body::-webkit-scrollbar,
            .main-content::-webkit-scrollbar,
            #reportPreview::-webkit-scrollbar,
            .table-responsive::-webkit-scrollbar {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
            }

            /* Hide browser-generated content */
            @top-left, @top-center, @top-right, @bottom-left, @bottom-center, @bottom-right {
                content: none !important;
            }

            /* Table styles */
            .table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 10pt !important;
                overflow: visible !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            /* Headers on first page only */
            .table thead {
                display: none !important;
            }

            .table tbody:first-of-type thead {
                display: table-header-group !important;
            }

            /* Style first page headers */
            .table tbody:first-of-type tr:first-child th {
                background-color: #e9ecef !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Hide headers on subsequent pages */
            .table tbody:not(:first-of-type) thead {
                display: none !important;
            }

            /* Hide the date/time and report title */
            .print-header {
                display: none !important;
            }

            /* Basic table cell styles */
            .table th,
            .table td {
                border: 1px solid black !important;
                padding: 4px !important;
                vertical-align: middle !important;
            }

            /* Category headers */
            .category-header {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                font-weight: bold !important;
                text-align: center !important;
            }

            /* Hide unnecessary elements */
            .sidebar,
            .btn-group,
            .page-title,
            .mobile-nav-toggle,
            .theme-switch-button,
            .datetime-container,
            .card-title {
                display: none !important;
            }

            /* Content area */
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                overflow: visible !important;
            }

            #reportPreview {
                overflow: visible !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }

            .card-body {
                padding: 0 !important;
                overflow: visible !important;
            }

            /* Prevent row breaks */
            tr {
                page-break-inside: avoid !important;
            }

            /* Report header */
            .report-header {
                page-break-after: avoid !important;
            }

            /* Hide about:blank text */
            title {
                display: none !important;
            }

            /* Make tables seamless in print */
            @media print {

                /* Additional print styling to create a truly seamless appearance */
                .table-responsive {
                    margin-bottom: -1px !important;
                    padding-bottom: 0 !important;
                }

                /* Force the signature table to attach directly to the main table */
                .report-footer {
                    margin-top: -1px !important;
                    border-top: none !important;
                }

                /* Remove any spacing between divs */
                div+div {
                    margin-top: -1px !important;
                }

                /* Force the signatory table to be directly adjacent to the main table */
                .table+div {
                    margin-top: -1px !important;
                }

                .table+div table {
                    border-top: none !important;
                }

                /* Force the total row to connect to the signature table */
                tr.total-row+tr,
                tr.total-row {
                    border-bottom: none !important;
                }

                /* Remove default margin and padding between elements */
                * {
                    margin-bottom: 0;
                }
            }
        }

        /* Adjust form spacing */
        #reportForm .form-group {
            margin-bottom: 0.5rem;
        }

        /* Adjust table spacing */
        .table td,
        .table th {
            padding: 0.5rem;
        }

        /* Make preview area more compact */
        #reportPreview {
            margin-top: 0.5rem;
        }

        /* Adjust button group spacing */
        .btn-group {
            gap: 0.5rem;
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
        .dropdown-submenu.show>.dropdown-menu {
            display: block;
        }

        .dropdown-submenu>a:after {
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
            transition: border-color 0.2s ease;
        }

        /* Light mode - hover/active arrow color */
        [data-bs-theme="light"] .dropdown-submenu.show>a:after,
        [data-bs-theme="light"] .dropdown-submenu>a:hover:after {
            border-left-color: var(--accent-color);
        }

        /* Dark mode - make sure arrow changes to purple when clicked */
        [data-bs-theme="dark"] .dropdown-submenu.show>a:after,
        [data-bs-theme="dark"] .dropdown-submenu>a[aria-expanded="true"]:after,
        [data-bs-theme="dark"] .nav-item.dropdown-submenu.show>a:after {
            border-left-color: #9c27b0 !important;
            border-top-color: transparent !important;
            border-bottom-color: transparent !important;
            border-right-color: transparent !important;
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

            .dropdown-submenu>a:after {
                transform: rotate(90deg);
                margin-top: 8px;
            }

            /* Ensure mobile arrow also changes color when expanded */
            [data-bs-theme="dark"] .dropdown-submenu.show>a:after {
                border-left-color: #9c27b0 !important;
            }
        }


        /* Dark mode report preview styles */
        [data-bs-theme="dark"] #reportPreview {
            color: var(--dark-text);
        }

        [data-bs-theme="dark"] #reportPreview .report-header {
            color: var(--dark-text);
        }

        [data-bs-theme="dark"] #reportPreview .table {
            color: var(--dark-text);
            border-color: var(--dark-border);
        }

        [data-bs-theme="dark"] #reportPreview .table th {
            background-color: var(--table-header-bg) !important;
            color: var(--dark-text);
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] #reportPreview .table td {
            background-color: var(--dark-sidebar);
            color: var(--dark-text);
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] #reportPreview .category-header {
            background-color: var(--table-subheader-bg) !important;
            color: var(--dark-text);
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] #reportPreview .report-footer {
            color: var(--dark-text);
        }

        [data-bs-theme="dark"] #reportPreview hr {
            border-color: var(--dark-border);
        }

        [data-bs-theme="dark"] #reportPreview .text-end,
        [data-bs-theme="dark"] #reportPreview .text-center {
            color: var(--dark-text);
        }

        /* Style for total row in dark mode */
        [data-bs-theme="dark"] #reportPreview .table tr:last-child td {
            background-color: var(--table-header-bg);
            color: var(--dark-text);
            font-weight: bold;
        }

        .form-select {
            font-weight: normal;
            background-color: var(--input-bg, #ffffff);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 12px;
            width: 100%;
            transition: border-color 0.2s ease;
        }

        .form-select:focus {
            border-color: var(--accent-color);
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25);
        }

        [data-bs-theme="dark"] .form-select {
            background-color: var(--input-bg);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        [data-bs-theme="dark"] .form-select:focus {
            border-color: var(--accent-color);
        }

        /* Special styling for the approval link - only visible to Central users */
        .approval-link {
            background-color: var(--accent-color);
            color: white !important;
            border-radius: 12px;
            margin-top: 10px;
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
                        <li><a class="dropdown-item" href="#">Annual GPB Reports</a></li>
                        <li><a class="dropdown-item" href="../ppas_reports/ppas_report.php">Quarterly PPAs Reports</a></li>
                        <li><a class="dropdown-item" href="../ps_atrib_reports/ps.php">PSA Reports</a></li>
                        <li><a class="dropdown-item" href="../ppas_proposal_reports/print_proposal.php">GAD Proposal Reports</a></li>
                        <li><a class="dropdown-item" href="../narrative_reports/print_narrative.php">Narrative Reports</a></li>
                    </ul>
                </div>
                <?php
                $currentPage = basename($_SERVER['PHP_SELF']);
                if ($isCentral):
                ?>
                    <a href="../approval/approval.php" class="nav-link approval-link">
                        <i class="fas fa-check-circle me-2"></i> Approval
                        <span id="approvalBadge" class="notification-badge" style="display: none;">0</span>
                    </a>
                <?php endif; ?>

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
            <h2>GPB Reports</h2>
        </div>

        <!-- Report Generation Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="reportForm">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="campus">Campus</label>
                                <select class="form-control" id="campus" required>
                                    <option value="">Select Campus</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="year">Year</label>
                                <select class="form-control" id="year" required>
                                    <option value="">Select Year</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-print"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="small text-muted mt-2">
                    <i class="fas fa-info-circle"></i> Note: Only entries with approved status can be included in reports. Entries with pending or rejected status will be excluded.
                </div>
            </div>
        </div>



        <!-- Report Preview -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Report Preview</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary" onclick="printReport()">
                            <i class="fas fa-print"></i> Print
                        </button>

                    </div>
                </div>
                <div id="reportPreview" class="table-responsive">
                    <!-- Report content will be loaded here -->
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <p>Select a campus and year to generate the report</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

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


        $(document).ready(function() {
            loadCampusOptions();

            // Handle form submission
            $('#reportForm').on('submit', function(e) {
                e.preventDefault();
                loadReport();
            });

            // Handle dropdown submenu with click-based behavior for all screen sizes
            $('.dropdown-submenu > a').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Close other open submenus
                $('.dropdown-submenu.show').not($(this).parent()).removeClass('show');

                // Toggle current submenu
                $(this).parent().toggleClass('show');
                $(this).next('.dropdown-menu').toggle();
            });

            // Close submenus when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.dropdown-submenu').length) {
                    $('.dropdown-submenu.show').removeClass('show');
                }
            });
        });


        function loadCampusOptions() {
            const campusSelect = $('#campus');
            campusSelect.prop('disabled', true);

            // Remove any existing change handlers to prevent duplicates
            campusSelect.off('change');

            // Check if user is Central or regular user
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const userCampus = "<?php echo $userCampus ?>";

            if (isCentral) {
                // Default list of all BatStateU campuses to ensure all are shown
                const allCampuses = [
                    "Alangilan",
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

                // Central users can see all campuses via API
                $.ajax({
                    url: 'api/get_campuses.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Campus response:', response);
                        if (response.status === 'success' && response.data) {
                            // Empty the select and add the placeholder
                            campusSelect.empty().append('<option value="">Select Campus</option>');

                            // Track campuses from API response
                            const apiCampuses = [];

                            // Add campuses from API response first
                            response.data.forEach(function(campus) {
                                if (campus.name && campus.name !== 'null' && campus.name !== 'Default Campus') {
                                    campusSelect.append(`<option value="${campus.name}">${campus.name}</option>`);
                                    apiCampuses.push(campus.name);
                                }
                            });

                            // Add any missing campuses from our default list
                            allCampuses.forEach(function(campus) {
                                if (!apiCampuses.includes(campus)) {
                                    campusSelect.append(`<option value="${campus}">${campus}</option>`);
                                }
                            });
                        } else {
                            // API failed, use our default list
                            console.error('Failed to load campuses from API, using default list');
                            campusSelect.empty().append('<option value="">Select Campus</option>');

                            // Add all campuses from our default list
                            allCampuses.forEach(function(campus) {
                                campusSelect.append(`<option value="${campus}">${campus}</option>`);
                            });
                        }

                        // Add change event handler
                        campusSelect.on('change', function() {
                            const selectedCampus = $(this).val();
                            const yearSelect = $('#year');

                            // Clear and disable year dropdown
                            yearSelect.empty().append('<option value="">Select Year</option>');
                            yearSelect.prop('disabled', true);

                            if (selectedCampus) {
                                loadYearOptions();
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading campuses:');
                        console.error('Status:', status);
                        console.error('Error:', error);

                        // On error, use our default list
                        console.log('Using fallback campus list');
                        campusSelect.empty().append('<option value="">Select Campus</option>');

                        // Add all campuses from our default list
                        allCampuses.forEach(function(campus) {
                            campusSelect.append(`<option value="${campus}">${campus}</option>`);
                        });

                        // Add change event handler
                        campusSelect.on('change', function() {
                            const selectedCampus = $(this).val();
                            const yearSelect = $('#year');

                            // Clear and disable year dropdown
                            yearSelect.empty().append('<option value="">Select Year</option>');
                            yearSelect.prop('disabled', true);

                            if (selectedCampus) {
                                loadYearOptions();
                            }
                        });
                    },
                    complete: function() {
                        campusSelect.prop('disabled', false);
                    }
                });
            } else {
                // Non-central users can only see their own campus
                campusSelect.empty();
                campusSelect.append(`<option value="${userCampus}" selected>${userCampus}</option>`);
                campusSelect.prop('disabled', true); // Disable selection for non-central users

                // Automatically load years for the user's campus
                loadYearOptions();
            }
        }

        function loadYearOptions() {
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const selectedCampus = isCentral ? $('#campus').val() : "<?php echo $userCampus ?>";
            const yearSelect = $('#year');

            // Show loading state
            yearSelect.prop('disabled', true);
            yearSelect.empty().append('<option value="">Loading years...</option>');

            // Debug log
            console.log('Loading years for campus:', selectedCampus);

            $.ajax({
                url: 'api/get_years.php',
                method: 'GET',
                data: {
                    campus_id: selectedCampus
                }, // Fixed parameter name to match API expectation
                dataType: 'json',
                success: function(response) {
                    console.log('Years response:', response);
                    yearSelect.empty().append('<option value="">Select Year</option>');

                    if (response.status === 'success' && response.data && response.data.length > 0) {
                        // Sort years in descending order
                        const years = response.data.sort((a, b) => {
                            const yearA = parseInt(a.year);
                            const yearB = parseInt(b.year);
                            return yearB - yearA;
                        });

                        years.forEach(function(yearData) {
                            yearSelect.append(`<option value="${yearData.year}">${yearData.year}</option>`);
                        });

                        yearSelect.prop('disabled', false);
                    } else {
                        yearSelect.empty().append('<option value="">No years available</option>');
                        yearSelect.prop('disabled', true);

                        // Inform the user
                        Swal.fire({
                            icon: 'info',
                            title: 'No Data Available',
                            text: `No report data is available for ${selectedCampus} campus. Please select a different campus or contact the administrator to add data.`
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading years:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });

                    yearSelect.empty().append('<option value="">Error loading years</option>');
                    yearSelect.prop('disabled', true);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load available years. Please try again or contact support.'
                    });
                }
            });
        }

        function loadBudgetSummary() {
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const selectedCampus = isCentral ? $('#campus').val() : "<?php echo $userCampus ?>";
            const selectedYear = $('#year').val();

            if (!selectedCampus || !selectedYear) {
                $('#budgetSummaryCard').hide();
                return;
            }

            $.ajax({
                url: 'api/get_budget_summary.php',
                method: 'GET',
                data: {
                    campus: selectedCampus,
                    year: selectedYear
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        displayBudgetSummary(response.data, selectedYear);
                        $('#budgetSummaryCard').show();
                    } else {
                        $('#budgetSummaryCard').hide();
                        console.error('No budget summary data available:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    $('#budgetSummaryCard').hide();
                    console.error('Error loading budget summary:', error);
                }
            });
        }

        function displayBudgetSummary(data, year) {
            const totalGAABudget = parseFloat(data.total_gaa) || 0;
            const totalGADFund = parseFloat(data.total_gad_fund) || 0;
            const totalBudget = totalGAABudget + totalGADFund;

            const html = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-primary h-100">
                            <div class="card-body text-center">
                                <h6 class="text-primary mb-2">Total GAA Budget for ${year}</h6>
                                <h3 class="mb-0">₱${totalGAABudget.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success h-100">
                            <div class="card-body text-center">
                                <h6 class="text-success mb-2">Total GAD Fund for ${year}</h6>
                                <h3 class="mb-0">₱${totalGADFund.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-dark h-100">
                            <div class="card-body text-center">
                                <h6 class="text-dark mb-2">Total GAD Budget for ${year}</h6>
                                <h3 class="mb-0">₱${totalBudget.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#budgetSummary').html(html);
        }

        async function loadReport() {
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            var selectedCampus = isCentral ? $('#campus').val() : "<?php echo $userCampus ?>";
            var selectedYear = $('#year').val();

            if (!selectedCampus || !selectedYear) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select' + (isCentral ? ' both a campus and' : '') + ' a year to generate the report.'
                });
                return;
            }

            console.log('Loading report for campus:', selectedCampus, 'year:', selectedYear);

            // Show loading message in report preview
            $('#reportPreview').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading signatories...</p>
                </div>
            `);

            // First load signatories from database to ensure we have them
            try {
                console.log('Loading signatories before report generation');
                signatories = await loadSignatories();
                console.log('Loaded signatories:', signatories);

                // Add a small delay to ensure signatories are fully processed
                await new Promise(resolve => setTimeout(resolve, 300));
            } catch (error) {
                console.error('Error loading signatories:', error);
                // Continue with default signatories if there's an error
                signatories = [{
                        name: 'RICHELLE M. SULIT',
                        position: 'GAD Head Secretariat',
                        side: 'left'
                    },
                    {
                        name: 'ATTY. ALVIN R. DE SILVA',
                        position: 'Chancellor',
                        side: 'middle'
                    },
                    {
                        name: 'JOCELYN A. JAUGAN',
                        position: 'Assistant Director, GAD',
                        side: 'right'
                    }
                ];
            }

            // Then proceed with the rest of the report generation
            // First, load budget summary to get target table values
            $.ajax({
                url: 'api/get_budget_summary.php',
                method: 'GET',
                data: {
                    campus: selectedCampus,
                    year: selectedYear
                },
                dataType: 'json',
                success: function(budgetResponse) {
                    // Save budget data for the report display
                    let budgetData = null;

                    if (budgetResponse.status === 'success' && budgetResponse.data) {
                        budgetData = budgetResponse.data;
                        displayBudgetSummary(budgetData, selectedYear);
                        $('#budgetSummaryCard').show();
                    } else {
                        $('#budgetSummaryCard').hide();
                        console.error('No budget summary data available:', budgetResponse.message);
                    }

                    // Now load the report data and pass the budget data to it
                    loadReportData(selectedCampus, selectedYear, budgetData);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading budget summary:', error);
                    // Proceed with report loading anyway, without budget data
                    loadReportData(selectedCampus, selectedYear, null);
                }
            });
        }

        function loadReportData(selectedCampus, selectedYear, budgetData) {
            // Use our async function that's already working to get signatories
            // We've already loaded them in loadReport() so we'll use the global variable
            console.log('Using previously loaded signatories for report:', signatories);

            // Show loading indicator in preview area
            $('#reportPreview').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading report data...</p>
                </div>
            `);

            // Now fetch the report data
            $.ajax({
                url: 'api/get_gpb_report.php',
                method: 'GET',
                data: {
                    campus: selectedCampus,
                    year: selectedYear
                },
                success: function(response) {
                    console.log('Report response:', response);

                    if (response.status === 'success' && response.data) {
                        // Check if any entries have pending or rejected status
                        let hasPendingOrRejected = false;
                        if (response.data.length > 0) {
                            hasPendingOrRejected = response.data.some(item =>
                                item.status !== undefined &&
                                item.status !== 'approved' &&
                                item.status !== 'Approved'
                            );
                        }

                        // Check if there's no data with approved status
                        if (response.data.length === 0) {
                            // Show alert that no approved data exists
                            Swal.fire({
                                icon: 'warning',
                                title: 'No Approved Entries',
                                text: 'There are no approved entries for this campus and year. Entries are still pending or have been rejected by the admin.',
                                confirmButtonColor: '#3085d6'
                            });

                            // Show no data message in preview
                            $('#reportPreview').html(`
                                <div class="text-center text-warning py-5">
                                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                                    <p>No approved entries found for report generation.</p>
                                    <p class="small">Please wait for admin approval before generating reports.</p>
                                </div>
                            `);
                            return;
                        }

                        if (hasPendingOrRejected) {
                            // Show warning about pending/rejected entries
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid Status Detected',
                                text: 'Entries must have Approved status to be included in reports. Entries with Pending or Rejected status cannot be used for report generation.',
                                confirmButtonColor: '#d33'
                            });

                            // Show no data message in preview
                            $('#reportPreview').html(`
                                <div class="text-center text-warning py-5">
                                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                                    <p>Cannot generate report with pending or rejected entries.</p>
                                    <p class="small">Please ensure all entries have approved status before generating the report.</p>
                                </div>
                            `);
                            return;
                        }

                        // Process and display the report data, passing budget data and signatories
                        console.log('Calling displayReport with data and signatories');
                        displayReport(response.data, budgetData, signatories);
                    } else {
                        console.error('Failed to load report data:', response.message);

                        // Show no data message in preview
                        $('#reportPreview').html(`
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-file-alt fa-3x mb-3"></i>
                                <p>No report data available for the selected campus and year.</p>
                                <p class="small text-danger">${response.message || ''}</p>
                            </div>
                        `);

                        Swal.fire({
                            icon: 'warning',
                            title: 'No Data',
                            text: response.message || 'No report data available for the selected criteria'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Report error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);

                    // Show error message in preview
                    $('#reportPreview').html(`
                        <div class="text-center text-danger py-5">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                            <p>Error loading report data. Please try again.</p>
                            <p class="small">${error}</p>
                        </div>
                    `);

                    let errorMessage = 'Failed to load the report. Please try again.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Failed to parse error response:', e);
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                }
            });
        }

        function updateDateTime() {
            const now = new Date();
            const dateOptions = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const timeOptions = {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            };

            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        // Define categories for GAD activities
        const allCategories = [
            'Client-Focused',
            'Organization-Focused',
            'Attributable PAPs'
        ];

        function displayReport(data, budgetData, signatories) {
            const preview = $('#reportPreview');

            // Debug the budgetData to see what's coming in
            console.log('Budget data:', budgetData);
            console.log('Signatories data:', signatories);

            // Get values directly from target table
            let totalGAA = 0;
            let totalGADFund = 0;
            let totalGADBudget = 0;

            if (budgetData) {
                // Use exact values from target table with proper parsing
                totalGAA = budgetData.total_gaa ? parseFloat(budgetData.total_gaa) : 0;
                totalGADFund = budgetData.total_gad_fund ? parseFloat(budgetData.total_gad_fund) : 0;
            }

            // Calculate total GAD budget from report data
            if (data && data.length > 0) {
                data.forEach(item => {
                    totalGADBudget += parseFloat(item.gad_budget) || 0;
                });
            }

            // Get the prepared by, approved by, and asst director signatories
            let preparedByName = 'RICHELLE M. SULIT';
            let preparedByPosition = 'GAD Head Secretariat';
            let approvedByName = 'ATTY. ALVIN R. DE SILVA';
            let approvedByPosition = 'Chancellor';
            let asstDirectorName = 'JOCELYN A. JAUGAN';
            let asstDirectorPosition = 'Assistant Director, GAD';

            // Extract signatories if available
            if (signatories && signatories.length > 0) {
                // Find signatories by position or index
                signatories.forEach(signatory => {
                    if (signatory.side === 'left' || signatories.indexOf(signatory) === 0) {
                        preparedByName = signatory.name;
                        preparedByPosition = signatory.position;
                    } else if (signatory.side === 'middle' || signatories.indexOf(signatory) === 1) {
                        approvedByName = signatory.name;
                        approvedByPosition = signatory.position;
                    } else if (signatory.side === 'right' || signatories.indexOf(signatory) === 2) {
                        asstDirectorName = signatory.name;
                        asstDirectorPosition = signatory.position;
                    }
                });
            }

            console.log('Using signatories:', {
                preparedBy: {
                    name: preparedByName,
                    position: preparedByPosition
                },
                approvedBy: {
                    name: approvedByName,
                    position: approvedByPosition
                },
                asstDirector: {
                    name: asstDirectorName,
                    position: asstDirectorPosition
                }
            });

            // Create report HTML with consistent font sizes
            let html = `
                <div class="report-header text-center mb-2" style="font-size: 10px;">
                    <img src="../images/BatStateU-NEU-Logo.png" alt="BatStateU Logo" style="width: 60px; height: 60px; margin-bottom: 5px;">
                    <div class="header-text" style="line-height: 1.2;">
                        <p class="mb-0" style="font-size: 10px;">Republic of the Philippines</p>
                        <p class="mb-0" style="font-size: 10px; font-weight: bold;">BATANGAS STATE UNIVERSITY</p>
                        <p class="mb-0" style="font-size: 10px;">The National Engineering University</p>
                        <p class="mb-0" style="font-size: 10px;">Batangas City</p>
                    </div>
                    <div style="margin-top: 10px;">
                        <hr style="height: 2px; background-color: #000000; border: none; margin: 5px 0;">
                        <h4 style="font-size: 10px; font-weight: bold; margin-bottom: 2px;">ANNUAL GENDER AND DEVELOPMENT (GAD) PLAN AND BUDGET</h4>
                        <h5 style="font-size: 10px; margin-bottom: 2px;">FY ${data[0]?.year || ''}</h5>
                    </div>
                </div>
                
                <!-- TARGET TABLE VALUES - Simple format with smaller font -->
                <div class="mb-1">
                    <div class="row">
                        <div class="col-12" style="line-height: 1.1;">
                            <p class="mb-0" style="font-size: 10px;"><strong>Campus:</strong> ${data[0]?.campus || ''}</p>
                            <p class="mb-0" style="font-size: 10px;"><strong>Total GAA:</strong> ₱${totalGAA.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                            <p class="mb-0" style="font-size: 10px;"><strong>Total GAD Fund (5%):</strong> ₱${totalGADFund.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="reportTable" style="font-size: 10px;">
                        <thead>
                            <tr style="background-color: #f8f9fa;">
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; width: 10%; padding: 2px;">Gender Issue and/or GAD Mandate</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; width: 10%; padding: 2px;">Cause of the Gender Issue</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; width: 10%; padding: 2px;">GAD Result Statement/GAD Objective</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; width: 10%; padding: 2px;">Relevant Agency MFO/PAP</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; width: 18%; padding: 2px; border-right: 1px solid #000;">GAD Activity</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; width: 12%; padding: 2px; border-left: 1px solid #000;">Output Performance Indicators and Target</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; width: 10%; padding: 2px;">GAD Budget</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; width: 10%; padding: 2px;">Source of Budget</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; width: 10%; padding: 2px;">Responsible Unit/Office</th>
                            </tr>
                            <tr style="background-color: #f8f9fa;">
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; padding: 2px;">(1)</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; padding: 2px;">(2)</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; padding: 2px;">(3)</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; padding: 2px;">(4)</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; padding: 2px; border-right: 1px solid #000;">(5)</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; padding: 2px; border-left: 1px solid #000;">(6)</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; padding: 2px;">(7)</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; padding: 2px;">(8)</th>
                                <th style="text-align: center; background-color: #e9ecef; font-size: 10px; padding: 2px;">(9)</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            if (!data || data.length === 0) {
                html += '<tr><td colspan="9" class="text-center">No data available</td></tr>';
            } else {
                // Process each category
                allCategories.forEach(category => {
                    // Add category header
                    html += `
                        <tr>
                            <td colspan="9" class="category-header" style="font-size: 10px; font-weight: bold; padding: 2px;">${category}</td>
                        </tr>
                    `;

                    // Filter data for current category
                    const categoryData = data.filter(item => item.category === category);

                    if (categoryData.length === 0) {
                        // Add empty rows for categories with no data (minimal height)
                        html += `
                            <tr>
                                <td style="font-size: 10px; padding: 2px;">&nbsp;</td>
                                <td style="font-size: 10px; padding: 2px;">&nbsp;</td>
                                <td style="font-size: 10px; padding: 2px;">&nbsp;</td>
                                <td style="font-size: 10px; padding: 2px;">&nbsp;</td>
                                <td style="font-size: 10px; width: 18%; padding: 2px; border-right: 1px solid #000;">&nbsp;</td>
                                <td style="font-size: 10px; width: 12%; padding: 2px; border-left: 1px solid #000;">&nbsp;</td>
                                <td style="font-size: 10px; padding: 2px;">&nbsp;</td>
                                <td style="font-size: 10px; padding: 2px;">&nbsp;</td>
                                <td style="font-size: 10px; padding: 2px;">&nbsp;</td>
                            </tr>
                        `;
                    } else {
                        // Add data rows
                        categoryData.forEach(item => {
                            // Format activities
                            let formattedActivities = '';

                            // Parse the nested JSON structures
                            const genericActivities = item.generic_activity ?
                                JSON.parse(item.generic_activity.replace(/[\[\]"]/g, (match) => {
                                    return match === '[' ? '[' : match === ']' ? ']' : '"';
                                })) : [];

                            const specificActivities = item.specific_activities ?
                                JSON.parse(item.specific_activities.replace(/[\[\]"]/g, (match) => {
                                    return match === '[' ? '[' : match === ']' ? ']' : '"';
                                })) : [];

                            // Format activities with proper layout - each generic activity gets its own specific activities
                            genericActivities.forEach((gen, idx) => {
                                // Add generic activity
                                formattedActivities += `${gen.trim()}<br>`;

                                // Get specific activities for this generic activity
                                const specificsForThisGeneric = specificActivities[idx] || [];
                                specificsForThisGeneric.forEach(spec => {
                                    formattedActivities += `• ${spec.trim()}<br>`;
                                });

                                // Add spacing between groups, but not after the last one
                                if (idx < genericActivities.length - 1) {
                                    formattedActivities += '<br>';
                                }
                            });

                            // Format output indicators - use gender issue name instead of generic activities
                            let formattedOutputs = '';

                            // Get the gender issue name
                            const genderIssue = item.gender_issue ? item.gender_issue.trim() : "No issue specified";

                            // Count total number of activities (sum of all specific activities)
                            const totalActivities = specificActivities.reduce((sum, group) => sum + group.length, 0);

                            // Format exactly as requested with correct spacing and using gender issue
                            formattedOutputs = "   No. of activities in \"" + genderIssue + "\" conducted - at least " + totalActivities + " " + (totalActivities > 1 ? 'activities' : 'activity') + ".<br><br>";
                            formattedOutputs += "   No. of participants in \"" + genderIssue + "\" - at least " + (item.male_participants || '0') + " male and " + (item.female_participants || '0') + " female participants.";

                            html += `
                                <tr>
                                    <td style="font-size: 10px; padding: 2px;">${item.gender_issue || ''}</td>
                                    <td style="font-size: 10px; padding: 2px;">${item.cause_of_issue || ''}</td>
                                    <td style="font-size: 10px; padding: 2px;">${item.gad_objective || ''}</td>
                                    <td style="font-size: 10px; padding: 2px;">${item.relevant_agency || ''}</td>
                                    <td style="font-size: 10px; width: 18%; padding: 2px; border-right: 1px solid #000;">${formattedActivities}</td>
                                    <td style="font-size: 10px; width: 12%; padding: 2px; border-left: 1px solid #000;">${formattedOutputs}</td>
                                    <td style="font-size: 10px; padding: 2px;">₱${parseFloat(item.gad_budget || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td style="font-size: 10px; padding: 2px;">${item.source_of_budget || ''}</td>
                                    <td style="font-size: 10px; padding: 2px;">${item.responsible_unit || ''}</td>
                                </tr>
                            `;
                        });
                    }
                });

                // Add total row at the end of the table for GAD Budget with smaller font
                html += `
                    <tr class="total-row" style="background-color: #f9f9fa; font-weight: bold !important; margin-bottom: 0 !important;">
                        <td colspan="6" class="text-end" style="font-size: 10px; padding: 2px;"><strong>Total GAD Budget</strong></td>
                        <td style="font-size: 10px; padding: 2px;"><strong>₱${totalGADBudget.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                `;
            }

            // Add the signature footer at the end of the report with no spacing
            html += `</tbody></table></div>
            <div style="page-break-inside: avoid !important; break-inside: avoid !important; margin-top: -1px !important;">
                <table class="table table-bordered report-footer" style="border-collapse: collapse; width: 100%; page-break-inside: avoid !important; break-inside: avoid !important; margin-top: 0 !important; border-top: 1px solid black !important;">
                    <tr style="page-break-inside: avoid !important; break-inside: avoid !important;">
                        <td class="text-center" style="width: 33.3%; border: 1px solid black; padding: 8px; vertical-align: top;">
                            <p class="mb-0" style="font-size: 10px;">Prepared by:</p>
                            <br><br><br>
                            <p class="mb-0" style="font-size: 10px; font-weight: bold; text-decoration: underline;">${preparedByName}</p>
                            <p class="mb-0" style="font-size: 10px;">${preparedByPosition}</p>
                        </td>
                        <td class="text-center" style="width: 33.3%; border: 1px solid black; padding: 8px; vertical-align: top;">
                            <p class="mb-0" style="font-size: 10px;">Approved by:</p>
                            <br><br><br>
                            <p class="mb-0" style="font-size: 10px; font-weight: bold; text-decoration: underline;">${approvedByName}</p>
                            <p class="mb-0" style="font-size: 10px;">${approvedByPosition}</p>
                        </td>
                        <td class="text-center" style="width: 33.3%; border: 1px solid black; padding: 8px; vertical-align: top;">
                            <p class="mb-0" style="font-size: 10px;">Noted by:</p>
                            <br><br><br>
                            <p class="mb-0" style="font-size: 10px; font-weight: bold; text-decoration: underline;">${asstDirectorName}</p>
                            <p class="mb-0" style="font-size: 10px;">${asstDirectorPosition}</p>
                        </td>
                    </tr>
                </table>
            </div>`;

            // Make sure to set the HTML content of the preview element
            preview.html(html);
            console.log('Report preview generated!');
        }

        function printReport() {
            // Create a print window with a specific title
            const printWindow = window.open('', '_blank', 'width=1200,height=800');

            // Set window properties immediately to prevent about:blank
            printWindow.document.open();
            printWindow.document.title = "GPB Report";

            // Capture the original report content with signatories
            const reportContent = $('#reportPreview').html();

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>GPB Report</title>
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @page {
                            size: landscape;
                            margin: 1cm;
                        }
                        
                        body {
                            font-family: Times New Roman, sans-serif !important;
                            font-size: 12px !important;
                        }
                        
                        /* Replace hr with div for more reliable printing */
                        .report-header hr, 
                        .report-header div[style*="height: 1px"] {
                            height: 1px !important;
                            background-color: black !important;
                            width: 100% !important;
                            margin: 15px auto !important;
                            display: block !important;
                            border: none !important;
                            color: black !important;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                            color-adjust: exact !important;
                        }
                        
                        /* Keep signatures with the report - prevent page break */
                        .report-footer, 
                        .table ~ div, 
                        .table + div {
                            page-break-before: avoid !important;
                            page-break-inside: avoid !important;
                            break-inside: avoid !important;
                        }
                        
                        /* Ensure the total row stays with signatures */
                        .total-row, tr.total-row {
                            page-break-after: avoid !important;
                        }
                        
                        /* Force the signatures to stay together */
                        .report-footer {
                            margin-top: 0 !important;
                            margin-bottom: 0 !important;
                            page-break-before: avoid !important;
                        }
                        
                        /* Additional styles to remove spacing */
                        .table {
                            margin-bottom: 0 !important;
                        }
                        
                        /* Report footer specific styles */
                        .report-footer table {
                            page-break-inside: avoid !important;
                            break-inside: avoid !important;
                            margin-top: 0 !important;
                        }
                        
                        /* Total row should have no bottom margin */
                        tr.total-row {
                            margin-bottom: 0 !important;
                        }
                        
                        /* Table container should have no bottom margin */
                        .table-responsive {
                            margin-bottom: 0 !important;
                        }
                        
                        /* Force correct display of underline */
                        [style*="text-decoration: underline"] {
                            text-decoration: underline !important;
                            border-bottom: none !important;
                        }
                        
                        @media print {
                            * {
                                font-family: Times New Roman, sans-serif !important;
                                font-size: 12px !important;
                            }
                            
                            /* Column widths for print */
                            .table th:nth-child(5), .table td:nth-child(5) {
                                width: 18% !important;
                                border-right: 1px solid black !important;
                            }
                            .table th:nth-child(6), .table td:nth-child(6) {
                                width: 12% !important;
                                border-left: 1px solid black !important;
                            }
                            
                            /* Force total rows to display in print */
                            tr.total-row {
                                background-color: #f9f9f9 !important;
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                                color-adjust: exact !important;
                                page-break-inside: avoid !important;
                                font-weight: bold !important;
                                display: table-row !important;
                                visibility: visible !important;
                            }
                            
                            tr.total-row td {
                                background-color: #f9f9f9 !important;
                                font-weight: bold !important;
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                                color-adjust: exact !important;
                                font-size: 12px !important;
                                border: 1px solid black !important;
                                display: table-cell !important;
                                visibility: visible !important;
                            }
                            
                            /* Add page break control */
                            thead {
                                display: table-header-group;
                            }
                            
                            tfoot {
                                display: table-footer-group;
                            }
                            
                            tr {
                                page-break-inside: avoid;
                            }
                            
                            /* Solid lines for all borders */
                            td, th {
                                border: 1px solid black !important;
                            }
                            
                            .table {
                                border: 1px solid black !important;
                            }
                        }
                    </style>
                </head>
                <body class="p-4">
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
                // Clone the content to avoid modifying the original
                const content = $('#reportPreview').clone();

                // Create Word document HTML
                const html = `
                    <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
                    <head>
                        <meta charset='utf-8'>
                        <title>GPB Report</title>
                        <style>
                            @page {
                                size: folio landscape;
                                mso-page-orientation: landscape;
                            }
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
                            /* Ensure underlines appear in Word */
                            .text-decoration-underline,
                            [style*="text-decoration: underline"] {
                                text-decoration: underline !important;
                                border-bottom: 1px solid black !important;
                            }
                            /* Signature areas */
                            .signature-name {
                                text-decoration: underline !important;
                                font-weight: bold;
                            }
                        </style>
                    </head>
                    <body>
                        ${content.html()}
                    </body>
                    </html>
                `;

                // Create blob and download
                const blob = new Blob([html], {
                    type: 'application/msword'
                });
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

        // Define a global signatories variable
        let signatories = [];

        // Instead of using multiple fetch calls, load all signatories at once and sync
        async function loadSignatories() {
            try {
                // Get the currently selected campus
                const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
                const selectedCampus = isCentral ? $('#campus').val() : "<?php echo $userCampus ?>";

                // Get signatories from database
                const response = await fetch('get_all_signatories.php');
                const result = await response.json();

                if (result.status === 'success' && result.data && result.data.length > 0) {
                    // Find the signatory that matches the current campus
                    const dbSignatories = result.data.find(sig => sig.campus.toLowerCase() === selectedCampus.toLowerCase()) || result.data[0];
                    console.log('Selected campus:', selectedCampus);
                    console.log('Loaded signatories from database:', dbSignatories);

                    // Clear existing signatories and use database values
                    signatories = [];

                    // Map database fields to report positions
                    if (dbSignatories.name1) {
                        signatories.push({
                            name: dbSignatories.name1,
                            position: 'GAD Head Secretariat',
                            side: 'left'
                        });
                    }

                    if (dbSignatories.name3) {
                        signatories.push({
                            name: dbSignatories.name3,
                            position: 'Chancellor',
                            side: 'middle'
                        });
                    }

                    if (dbSignatories.name4) {
                        signatories.push({
                            name: dbSignatories.name4,
                            position: 'Assistant Director, GAD',
                            side: 'right'
                        });
                    }

                    // Add defaults only if needed
                    if (!signatories.some(s => s.side === 'left')) {
                        signatories.push({
                            name: 'RICHELLE M. SULIT',
                            position: 'GAD Head Secretariat',
                            side: 'left'
                        });
                    }

                    if (!signatories.some(s => s.side === 'middle')) {
                        signatories.push({
                            name: 'ATTY. ALVIN R. DE SILVA',
                            position: 'Chancellor',
                            side: 'middle'
                        });
                    }

                    if (!signatories.some(s => s.side === 'right')) {
                        signatories.push({
                            name: 'JOCELYN A. JAUGAN',
                            position: 'Assistant Director, GAD',
                            side: 'right'
                        });
                    }
                } else {
                    console.error('Failed to load signatories from database');
                    // Use default signatories as fallback
                    signatories = [{
                            name: 'RICHELLE M. SULIT',
                            position: 'GAD Head Secretariat',
                            side: 'left'
                        },
                        {
                            name: 'ATTY. ALVIN R. DE SILVA',
                            position: 'Chancellor',
                            side: 'middle'
                        },
                        {
                            name: 'JOCELYN A. JAUGAN',
                            position: 'Assistant Director, GAD',
                            side: 'right'
                        }
                    ];
                }
            } catch (error) {
                console.error('Error fetching signatories:', error);
                // Use defaults on error
                signatories = [{
                        name: 'RICHELLE M. SULIT',
                        position: 'GAD Head Secretariat',
                        side: 'left'
                    },
                    {
                        name: 'ATTY. ALVIN R. DE SILVA',
                        position: 'Chancellor',
                        side: 'middle'
                    },
                    {
                        name: 'JOCELYN A. JAUGAN',
                        position: 'Assistant Director, GAD',
                        side: 'right'
                    }
                ];
            }

            // Return the signatories array for use in rendering
            return signatories;
        }

        // Modify the report generation to wait for signatories
        async function generateReport() {
            // Wait for signatories to load before rendering
            signatories = await loadSignatories();

            // Now render the report with the loaded signatories
            // ... rest of the report generation code ...
        }
    </script>
</body>

</html>