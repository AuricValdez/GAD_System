<?php
session_start();

$isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';

// Debug session information
error_log("Session data in ppas.php: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in - redirecting to login");
    header("Location: ../index.php");
    exit();
}

$isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';
$userCampus = isset($_SESSION['campus']) ? $_SESSION['campus'] : '';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSA Reports - GAD System</title>
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
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
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
        }

        /* Update hover effect for arrow */
        .dropdown-submenu.show>a:after {
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

            .dropdown-submenu>a:after {
                transform: rotate(90deg);
                margin-top: 8px;
            }
        }

        /* End of dropdown submenu styles */

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

        /* Add print-specific styles */
        @media print {

            /* Hide everything by default */
            body * {
                visibility: hidden;
            }

            /* Show only the print section */
            .print-section,
            .print-section * {
                visibility: visible !important;
                overflow: visible !important;
            }

            /* Position the print section */
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Table styles for print */
            .table {
                width: 100% !important;
                margin-bottom: 1rem;
                border-collapse: collapse !important;
                page-break-inside: auto !important;
            }

            .table td,
            .table th {
                background-color: #fff !important;
                border: 1px solid #000 !important;
                padding: 0.5rem !important;
                page-break-inside: avoid !important;
            }

            .table thead th {
                border-bottom: 2px solid #000 !important;
            }

            /* Hide non-printable elements */
            .no-print,
            .action-buttons,
            .btn,
            select,
            input {
                display: none !important;
            }

            /* Ensure text is black for better printing */
            * {
                color: #000 !important;
                text-shadow: none !important;
            }
        }

        /* Styling for readonly inputs */
        input[readonly] {
            background-color: #e9ecef !important;
            cursor: not-allowed;
        }

        [data-bs-theme="dark"] input[readonly] {
            background-color: #343a40 !important;
            cursor: not-allowed;
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


        // Immediately disable all buttons as soon as the page loads
        window.onload = function() {
            for (let quarter = 1; quarter <= 4; quarter++) {
                const printBtn = document.getElementById(`printBtn${quarter}`);
                const exportBtn = document.getElementById(`exportBtn${quarter}`);
                if (printBtn) printBtn.disabled = true;
                if (exportBtn) exportBtn.disabled = true;
            }

            // Initialize campus fields based on user
            const isCentral = <?php echo $isCentral ? 'true' : 'false'; ?>;
            const userCampus = "<?php echo $userCampus; ?>";

            // Set campus fields
            for (let quarter = 1; quarter <= 4; quarter++) {
                const campusField = document.getElementById(`ppasCampus${quarter}`);
                if (campusField) {
                    campusField.value = userCampus;
                    // For Central users, campus will be populated when a PPA is selected
                    if (isCentral) {
                        campusField.value = '';
                    }
                }
            }
        };
    </script>

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
                        <li><a class="dropdown-item" href="../gpb_reports/gbp_reports.php">Annual GPB Reports</a></li>
                        <li><a class="dropdown-item" href="../ppas_reports/ppas_report.php">Quarterly PPAs Reports</a></li>
                        <li><a class="dropdown-item" href="#">PSA Reports</a></li>
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
            <h2>PS Attributions</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">PS Attributions</h5>
            </div>
            <div class="card-body">
                <div class="nav nav-tabs mb-3" id="quarterTabs" role="tablist">
                    <button class="nav-link active" id="q1-tab" data-bs-toggle="tab" data-bs-target="#q1" type="button" role="tab">Q1</button>
                    <button class="nav-link" id="q2-tab" data-bs-toggle="tab" data-bs-target="#q2" type="button" role="tab">Q2</button>
                    <button class="nav-link" id="q3-tab" data-bs-toggle="tab" data-bs-target="#q3" type="button" role="tab">Q3</button>
                    <button class="nav-link" id="q4-tab" data-bs-toggle="tab" data-bs-target="#q4" type="button" role="tab">Q4</button>
                </div>

                <div class="tab-content" id="quarterTabsContent">
                    <!-- Quarter 1 Content -->
                    <div class="tab-pane fade show active" id="q1" role="tabpanel">
                        <div class="row mb-3">
                            <?php if ($isCentral): ?>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Campus</label>
                                        <select class="form-control" id="ppasCampus1">
                                            <option value="">All Campuses</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="ppasTitle1">Title (PPAs)</label>
                                        <select class="form-control" id="ppasTitle1"></select>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Campus</label>
                                        <input type="text" class="form-control" id="ppasCampus1" readonly>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="ppasTitle1">Title (PPAs)</label>
                                        <select class="form-control" id="ppasTitle1"></select>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="text" class="form-control" id="ppasDate1" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Total PS</label>
                                    <input type="text" class="form-control" id="totalPS1" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="action-buttons mb-3">
                            <button type="button" class="btn btn-info" id="printBtn1" disabled style="pointer-events: none; opacity: 0.65;">
                                <i class="fas fa-print"></i> Print PS Attribution
                            </button>
                            <button type="button" class="btn btn-success" id="exportBtn1" disabled style="pointer-events: none; opacity: 0.65;">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Academic Rank</th>
                                        <th>No. of Personnel</th>
                                        <th>Monthly Salary</th>
                                        <th>Rate per Hour</th>
                                        <th>No. of Hours</th>
                                        <th>PS</th>
                                    </tr>
                                </thead>
                                <tbody id="psTable1"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Quarter 2 Content -->
                    <div class="tab-pane fade" id="q2" role="tabpanel">
                        <div class="row mb-3">
                            <?php if ($isCentral): ?>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Campus</label>
                                        <select class="form-control" id="ppasCampus2">
                                            <option value="">All Campuses</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="ppasTitle2">Title (PPAs)</label>
                                        <select class="form-control" id="ppasTitle2"></select>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Campus</label>
                                        <input type="text" class="form-control" id="ppasCampus2" readonly>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="ppasTitle2">Title (PPAs)</label>
                                        <select class="form-control" id="ppasTitle2"></select>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="text" class="form-control" id="ppasDate2" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Total PS</label>
                                    <input type="text" class="form-control" id="totalPS2" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="action-buttons mb-3">
                            <button type="button" class="btn btn-info" id="printBtn2" disabled style="pointer-events: none; opacity: 0.65;">
                                <i class="fas fa-print"></i> Print PS Attribution
                            </button>
                            <button type="button" class="btn btn-success" id="exportBtn2" disabled style="pointer-events: none; opacity: 0.65;">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Academic Rank</th>
                                        <th>No. of Personnel</th>
                                        <th>Monthly Salary</th>
                                        <th>Rate per Hour</th>
                                        <th>No. of Hours</th>
                                        <th>PS</th>
                                    </tr>
                                </thead>
                                <tbody id="psTable2"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Quarter 3 Content -->
                    <div class="tab-pane fade" id="q3" role="tabpanel">
                        <div class="row mb-3">
                            <?php if ($isCentral): ?>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Campus</label>
                                        <select class="form-control" id="ppasCampus3">
                                            <option value="">All Campuses</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="ppasTitle3">Title (PPAs)</label>
                                        <select class="form-control" id="ppasTitle3"></select>
                                    </div>
                                </div>
                            <?php else: ?>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Campus</label>
                                        <input type="text" class="form-control" id="ppasCampus3" readonly>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="ppasTitle3">Title (PPAs)</label>
                                        <select class="form-control" id="ppasTitle3"></select>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="text" class="form-control" id="ppasDate3" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Total PS</label>
                                    <input type="text" class="form-control" id="totalPS3" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="action-buttons mb-3">
                            <button type="button" class="btn btn-info" id="printBtn3" disabled style="pointer-events: none; opacity: 0.65;">
                                <i class="fas fa-print"></i> Print PS Attribution
                            </button>
                            <button type="button" class="btn btn-success" id="exportBtn3" disabled style="pointer-events: none; opacity: 0.65;">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Academic Rank</th>
                                        <th>No. of Personnel</th>
                                        <th>Monthly Salary</th>
                                        <th>Rate per Hour</th>
                                        <th>No. of Hours</th>
                                        <th>PS</th>
                                    </tr>
                                </thead>
                                <tbody id="psTable3"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Quarter 4 Content -->
                    <div class="tab-pane fade" id="q4" role="tabpanel">
                        <div class="row mb-3">
                            <?php if ($isCentral): ?>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Campus</label>
                                        <select class="form-control" id="ppasCampus4">
                                            <option value="">All Campuses</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="ppasTitle4">Title (PPAs)</label>
                                        <select class="form-control" id="ppasTitle4"></select>
                                    </div>
                                </div>
                            <?php else: ?>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Campus</label>
                                        <input type="text" class="form-control" id="ppasCampus4" readonly>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="ppasTitle4">Title (PPAs)</label>
                                        <select class="form-control" id="ppasTitle4"></select>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="text" class="form-control" id="ppasDate4" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Total PS</label>
                                    <input type="text" class="form-control" id="totalPS4" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="action-buttons mb-3">
                            <button type="button" class="btn btn-info" id="printBtn4" disabled style="pointer-events: none; opacity: 0.65;">
                                <i class="fas fa-print"></i> Print PS Attribution
                            </button>
                            <button type="button" class="btn btn-success" id="exportBtn4" disabled style="pointer-events: none; opacity: 0.65;">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Academic Rank</th>
                                        <th>No. of Personnel</th>
                                        <th>Monthly Salary</th>
                                        <th>Rate per Hour</th>
                                        <th>No. of Hours</th>
                                        <th>PS</th>
                                    </tr>
                                </thead>
                                <tbody id="psTable4"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
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
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            updateThemeIcon(savedTheme);

            // Handle dropdown submenu
            document.querySelectorAll('.dropdown-submenu > a').forEach(function(element) {
                element.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();

                    // Toggle the submenu
                    const parentLi = this.parentElement;
                    parentLi.classList.toggle('show');

                    const submenu = this.nextElementSibling;
                    if (submenu && submenu.classList.contains('dropdown-menu')) {
                        if (submenu.style.display === 'block') {
                            submenu.style.display = 'none';
                        } else {
                            submenu.style.display = 'block';
                        }
                    }
                });
            });

            // Close submenus when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-submenu')) {
                    const openSubmenus = document.querySelectorAll('.dropdown-submenu.show');
                    openSubmenus.forEach(menu => {
                        menu.classList.remove('show');
                        const submenu = menu.querySelector('.dropdown-menu');
                        if (submenu) {
                            submenu.style.display = 'none';
                        }
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

        // Initialize selectedPPAs object to store selected PPAs for each quarter
        let selectedPPAs = {
            1: null,
            2: null,
            3: null,
            4: null
        };

        // PS Attribution specific JavaScript
        document.addEventListener('DOMContentLoaded', async function() {
            // Initialize table messages
            [1, 2, 3, 4].forEach(quarter => {
                const table = document.querySelector(`#psTable${quarter}`);
                if (table) {
                    table.innerHTML = '<tr><td colspan="6" class="text-center">No PS attribution found for this quarter</td></tr>';
                }

                // Update button styles through JavaScript instead of inline styles
                const printBtn = document.getElementById(`printBtn${quarter}`);
                const exportBtn = document.getElementById(`exportBtn${quarter}`);
                if (printBtn) {
                    printBtn.disabled = true;
                    printBtn.style.pointerEvents = 'none';
                    printBtn.style.opacity = '0.65';
                }
                if (exportBtn) {
                    exportBtn.disabled = true;
                    exportBtn.style.pointerEvents = 'none';
                    exportBtn.style.opacity = '0.65';
                }
            });

            // For Central users, load available campuses
            const isCentral = <?php echo $isCentral ? 'true' : 'false'; ?>;
            if (isCentral) {
                try {
                    console.log('Loading campuses for Central user...');
                    fetch('get_campuses.php')
                        .then(response => {
                            if (!response.ok) {
                                console.error('Campus fetch failed:', response.status, response.statusText);
                                return {
                                    success: false,
                                    error: `Server returned ${response.status} ${response.statusText}`,
                                    campuses: ['Alangilan', 'Lemery', 'Lipa', 'Balayan', 'Lobo', 'Mabini', 'Malvar', 'Nasugbu', 'Pablo Borbon', 'Rosario', 'San Juan']
                                };
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Campus data received:', data);

                            // Even on error, we should have at least a default list of campuses
                            const campusList = (data.success && Array.isArray(data.campuses)) ?
                                data.campuses : ['Alangilan', 'Lemery', 'Lipa', 'Balayan', 'Lobo', 'Mabini', 'Malvar', 'Nasugbu', 'Pablo Borbon', 'Rosario', 'San Juan'];

                            if (!data.success) {
                                console.warn('Using default campus list:', campusList);
                            }

                            // Populate campus dropdowns for all quarters
                            [1, 2, 3, 4].forEach(quarter => {
                                const campusSelect = document.getElementById(`ppasCampus${quarter}`);
                                if (campusSelect) {
                                    // Keep the All Campuses option
                                    const allOption = campusSelect.querySelector('option[value=""]');

                                    // Clear other options
                                    campusSelect.innerHTML = '';

                                    // Add back the All Campuses option
                                    campusSelect.appendChild(allOption);

                                    // Add campus options
                                    campusList.forEach(campus => {
                                        const option = document.createElement('option');
                                        option.value = campus;
                                        option.textContent = campus;
                                        campusSelect.appendChild(option);
                                    });

                                    // Add change event listener
                                    campusSelect.addEventListener('change', function() {
                                        // When campus changes, reload the PPAs for this quarter
                                        initializeQuarter(quarter);
                                    });
                                }
                            });
                        })
                        .catch(error => {
                            console.error('Error loading campuses:', error);
                            // Use default campus list on error
                            const defaultCampuses = ['Main Campus', 'Alangilan', 'Lemery', 'Lipa', 'Balayan', 'Lobo', 'Mabini', 'Malvar', 'Nasugbu', 'Pablo Borbon', 'Rosario', 'San Juan'];

                            // Populate campus dropdowns with defaults
                            [1, 2, 3, 4].forEach(quarter => {
                                const campusSelect = document.getElementById(`ppasCampus${quarter}`);
                                if (campusSelect) {
                                    // Keep the All Campuses option
                                    const allOption = campusSelect.querySelector('option[value=""]');

                                    // Clear other options
                                    campusSelect.innerHTML = '';

                                    // Add back the All Campuses option
                                    campusSelect.appendChild(allOption);

                                    // Add default campus options
                                    defaultCampuses.forEach(campus => {
                                        const option = document.createElement('option');
                                        option.value = campus;
                                        option.textContent = campus;
                                        campusSelect.appendChild(option);
                                    });

                                    // Add change event listener
                                    campusSelect.addEventListener('change', function() {
                                        // When campus changes, reload the PPAs for this quarter
                                        initializeQuarter(quarter);
                                    });
                                }
                            });
                        });
                } catch (error) {
                    console.error('Fatal error loading campuses:', error);
                }
            }

            // Initialize all quarters on page load
            await Promise.all([1, 2, 3, 4].map(quarter => initializeQuarter(quarter)));

            // For Central users, disable PPA dropdowns until campus is selected
            if (<?php echo $isCentral ? 'true' : 'false'; ?>) {
                [1, 2, 3, 4].forEach(quarter => {
                    const ppasSelect = document.getElementById(`ppasTitle${quarter}`);
                    if (ppasSelect) {
                        // Clear and disable PPA dropdown initially
                        ppasSelect.innerHTML = '<option value="">Please select a campus first</option>';
                        ppasSelect.disabled = true;

                        // Add change handler to campus dropdown
                        const campusSelect = document.getElementById(`ppasCampus${quarter}`);
                        if (campusSelect) {
                            campusSelect.addEventListener('change', function() {
                                if (this.value) {
                                    // Enable PPA dropdown when campus is selected
                                    ppasSelect.disabled = false;
                                } else {
                                    // Disable PPA dropdown when no campus is selected
                                    ppasSelect.innerHTML = '<option value="">Please select a campus first</option>';
                                    ppasSelect.disabled = true;

                                    // Clear other fields
                                    document.getElementById(`ppasDate${quarter}`).value = '';
                                    document.getElementById(`totalPS${quarter}`).value = '';

                                    // Clear table
                                    const table = document.querySelector(`#psTable${quarter}`);
                                    if (table) {
                                        table.innerHTML = '<tr><td colspan="6" class="text-center">No PS attribution found for this quarter</td></tr>';
                                    }

                                    // Disable buttons
                                    const printBtn = document.getElementById(`printBtn${quarter}`);
                                    const exportBtn = document.getElementById(`exportBtn${quarter}`);
                                    if (printBtn) {
                                        printBtn.disabled = true;
                                        printBtn.style.pointerEvents = 'none';
                                        printBtn.style.opacity = '0.65';
                                    }
                                    if (exportBtn) {
                                        exportBtn.disabled = true;
                                        exportBtn.style.pointerEvents = 'none';
                                        exportBtn.style.opacity = '0.65';
                                    }
                                }

                                // Reload PPAs for the selected campus
                                initializeQuarter(quarter);
                            });
                        }
                    }
                });
            }

            // Add tab change listeners
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', async function(event) {
                    const quarter = parseInt(event.target.id.replace('q', '').replace('-tab', ''));
                    console.log(`Tab changed to quarter ${quarter}`);
                    // Refresh data for the selected quarter
                    await initializeQuarter(quarter);
                    // Disable buttons when switching tabs
                    document.getElementById(`printBtn${quarter}`).disabled = true;
                    document.getElementById(`exportBtn${quarter}`).disabled = true;
                });
            });

            // Add change listeners for PPA selects
            [1, 2, 3, 4].forEach(quarter => {
                const select = document.getElementById(`ppasTitle${quarter}`);
                if (select) {
                    select.addEventListener('change', function() {
                        const ppaId = this.value;
                        if (ppaId) {
                            updatePSAttribution(quarter, ppaId);
                        } else {
                            clearPSAttribution(quarter);
                        }
                    });
                }
            });

            // Add event listeners for buttons
            [1, 2, 3, 4].forEach(quarter => {
                document.getElementById(`printBtn${quarter}`).addEventListener('click', function() {
                    printPSAttribution(quarter);
                });
            });

            // Add export button listeners
            [1, 2, 3, 4].forEach(quarter => {
                document.getElementById(`exportBtn${quarter}`).addEventListener('click', function() {
                    exportToExcel(quarter);
                });
            });
        });

        // Function to initialize quarter data
        async function initializeQuarter(quarter) {
            console.log(`Initializing quarter ${quarter}`);
            const select = document.getElementById(`ppasTitle${quarter}`);
            if (!select) {
                console.error(`Select element for quarter ${quarter} not found`);
                return;
            }

            // User campus and Central status
            const isCentral = <?php echo $isCentral ? 'true' : 'false'; ?>;
            const userCampus = "<?php echo $userCampus; ?>";

            // For Central users, get the selected campus value
            let selectedCampus = userCampus;
            if (isCentral) {
                const campusSelect = document.getElementById(`ppasCampus${quarter}`);
                if (campusSelect) {
                    selectedCampus = campusSelect.value;
                    console.log(`Selected campus for quarter ${quarter}: "${selectedCampus}"`);

                    // For Central users, don't load PPAs if no campus is selected
                    if (!selectedCampus) {
                        console.log(`No campus selected for quarter ${quarter}, skipping PPA loading`);
                        select.innerHTML = '<option value="">Please select a campus first</option>';
                        select.disabled = true;

                        // Clear table
                        const table = document.querySelector(`#psTable${quarter}`);
                        if (table) {
                            table.innerHTML = '<tr><td colspan="6" class="text-center">No PS attribution found for this quarter</td></tr>';
                        }

                        // Disable buttons
                        const printBtn = document.getElementById(`printBtn${quarter}`);
                        const exportBtn = document.getElementById(`exportBtn${quarter}`);
                        if (printBtn) printBtn.disabled = true;
                        if (exportBtn) exportBtn.disabled = true;

                        return;
                    } else {
                        // Enable the select if a campus is chosen
                        select.disabled = false;
                    }
                }
            }

            // Show loading state
            select.disabled = true;
            const loadingOption = document.createElement('option');
            loadingOption.text = 'Loading PPAs...';
            select.innerHTML = '';
            select.add(loadingOption);

            try {
                // Fetch PPAs for the specific quarter, filtered by campus if selected
                console.log(`Fetching PPAs for quarter ${quarter}...`);
                let apiUrl = `get_ppas.php?quarter=${quarter}`;

                // Add campus filter if a campus is selected or for non-Central users
                if ((!isCentral && userCampus) || (isCentral && selectedCampus && selectedCampus !== '')) {
                    apiUrl += `&campus=${encodeURIComponent(selectedCampus)}`;
                    console.log(`Filtering by campus: ${selectedCampus} for quarter ${quarter}`);
                } else if (isCentral) {
                    // This case shouldn't happen now due to the early return above
                    console.log(`Central user without campus selection - shouldn't reach this point`);
                    select.innerHTML = '<option value="">Please select a campus first</option>';
                    select.disabled = true;
                    return;
                } else {
                    console.log(`No campus filter applied for quarter ${quarter}`);
                }

                console.log(`API URL: ${apiUrl}`);
                const response = await fetch(apiUrl);

                // Log the status and headers for debugging
                console.log(`Response status for quarter ${quarter}:`, response.status, response.statusText);

                let rawText = '';
                try {
                    // Get response as text first for better error handling
                    rawText = await response.text();
                    console.log(`Raw response for quarter ${quarter}:`, rawText.substring(0, 100) + (rawText.length > 100 ? '...' : ''));

                    // Parse as JSON
                    const result = JSON.parse(rawText);

                    // Clear loading state
                    select.innerHTML = '';

                    // Check if the response indicates success
                    if (!result.success) {
                        throw new Error(result.error || 'API returned failure status');
                    }

                    // Add default option
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.text = 'Select PPA';
                    select.add(defaultOption);

                    // Add PPAs to dropdown
                    if (Array.isArray(result.data) && result.data.length > 0) {
                        console.log(`Received ${result.data.length} PPAs for quarter ${quarter}`);
                        result.data.forEach(ppa => {
                            const option = document.createElement('option');
                            option.value = ppa.id;
                            option.text = ppa.title || `PPA #${ppa.id}`;
                            // Add campus as data attribute for debugging
                            if (ppa.campus) {
                                option.setAttribute('data-campus', ppa.campus);
                            }
                            select.add(option);
                        });
                        console.log(`Added ${result.data.length} PPAs for quarter ${quarter}`);

                        // Enable buttons if PPAs were found
                        const printBtn = document.getElementById(`printBtn${quarter}`);
                        if (printBtn) printBtn.disabled = false;
                    } else {
                        const noDataOption = document.createElement('option');
                        noDataOption.value = '';
                        noDataOption.text = 'No PPAs found for this quarter';
                        select.add(noDataOption);
                        console.log(`No PPAs found for quarter ${quarter}`);

                        // Disable buttons if no PPAs
                        const printBtn = document.getElementById(`printBtn${quarter}`);
                        if (printBtn) printBtn.disabled = true;
                    }
                } catch (parseError) {
                    console.error(`Error parsing response for quarter ${quarter}:`, parseError);
                    console.error(`Raw response was:`, rawText);

                    select.innerHTML = '';
                    const errorOption = document.createElement('option');
                    errorOption.value = '';
                    errorOption.text = `Error: Invalid server response`;
                    select.add(errorOption);

                    // Show error alert with more details
                    Swal.fire({
                        icon: 'error',
                        title: 'Response Error',
                        text: `Could not parse server response for quarter ${quarter}. Server may be misconfigured.`,
                        footer: 'Check browser console for more details',
                        confirmButtonText: 'OK'
                    });
                }
            } catch (error) {
                console.error(`Error fetching PPAs for quarter ${quarter}:`, error);

                select.innerHTML = '';
                const errorOption = document.createElement('option');
                errorOption.value = '';
                errorOption.text = `Error: ${error.message}`;
                select.add(errorOption);

                // Show error alert
                Swal.fire({
                    icon: 'error',
                    title: 'Data Loading Error',
                    text: `Unable to load PPAs for quarter ${quarter}. Please check your database configuration.`,
                    footer: 'View browser console for more details.',
                    confirmButtonText: 'OK'
                });
            } finally {
                // Re-enable the select element unless this is a Central user with no campus selected
                if (!(isCentral && !selectedCampus)) {
                    select.disabled = false;
                }
            }
        }

        function updatePSAttribution(quarter, ppaId) {
            console.log(`Updating PS Attribution for quarter ${quarter} with PPA ID ${ppaId}`);

            // Show loading state
            const table = document.querySelector(`#psTable${quarter}`);
            if (table) {
                table.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';
            }

            // Disable buttons during loading
            const printBtn = document.getElementById(`printBtn${quarter}`);
            const exportBtn = document.getElementById(`exportBtn${quarter}`);
            if (printBtn) {
                printBtn.disabled = true;
                printBtn.style.pointerEvents = 'none';
                printBtn.style.opacity = '0.65';
            }
            if (exportBtn) {
                exportBtn.disabled = true;
                exportBtn.style.pointerEvents = 'none';
                exportBtn.style.opacity = '0.65';
            }

            // Get quarter title
            const quarterTitles = {
                1: "Q1",
                2: "Q2",
                3: "Q3",
                4: "Q4"
            };

            // User campus and Central status
            const isCentral = <?php echo $isCentral ? 'true' : 'false'; ?>;
            const userCampus = "<?php echo $userCampus; ?>";

            // Fetch PPA details and academic ranks
            Promise.all([
                    fetch(`get_ppa_details.php?id=${ppaId}`)
                    .then(response => response.text())
                    .then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error("JSON parse error in get_ppa_details.php:", e);
                            console.error("Raw response:", text);
                            throw new Error("Invalid JSON response from server. Check server logs for details.");
                        }
                    }),
                    fetch(`get_academic_ranks_simple.php?ppaId=${ppaId}`)
                    .then(response => response.text())
                    .then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error("JSON parse error in get_academic_ranks_simple.php:", e);
                            console.error("Raw response:", text);
                            throw new Error("Invalid JSON response from server. Check server logs for details.");
                        }
                    })
                ])
                .then(([ppaDetails, academicRanksResponse]) => {
                    console.log('Received PPA details:', ppaDetails);
                    console.log('Received academic ranks:', academicRanksResponse);

                    // Check if there was an error in the responses
                    if (ppaDetails.error || !ppaDetails.success === false) {
                        throw new Error(ppaDetails.error || "Unknown error getting PPA details");
                    }

                    if (!academicRanksResponse.success) {
                        throw new Error(academicRanksResponse.error || "Unknown error getting academic ranks");
                    }

                    // Store PPA details for later use
                    selectedPPAs[quarter] = {
                        ...ppaDetails,
                        id: ppaId,
                        quarterTitle: quarterTitles[quarter],
                        quarter: quarter
                    };

                    // Format date
                    const date = document.getElementById(`ppasDate${quarter}`);
                    if (date && ppaDetails.date) {
                        date.value = ppaDetails.date;
                    }

                    // Set campus field for non-Central users only
                    // For Central users, we preserve their selected campus
                    if (!isCentral) {
                        const campus = document.getElementById(`ppasCampus${quarter}`);
                        if (campus) {
                            campus.value = userCampus;
                        }
                    }

                    // Update table with academic ranks data
                    if (academicRanksResponse.success && academicRanksResponse.academicRanks) {
                        if (academicRanksResponse.academicRanks.length === 0) {
                            console.log('No academic ranks found');
                            table.innerHTML = '<tr><td colspan="6" class="text-center">No PS attribution found for this quarter</td></tr>';
                            if (printBtn) {
                                printBtn.disabled = true;
                                printBtn.style.pointerEvents = 'none';
                                printBtn.style.opacity = '0.65';
                            }
                            if (exportBtn) {
                                exportBtn.disabled = true;
                                exportBtn.style.pointerEvents = 'none';
                                exportBtn.style.opacity = '0.65';
                            }
                        } else {
                            console.log('Updating table with academic ranks');
                            updatePSTable(quarter, academicRanksResponse.academicRanks);

                            // Enable buttons and remove inline styles
                            if (printBtn) {
                                printBtn.disabled = false;
                                printBtn.style.pointerEvents = '';
                                printBtn.style.opacity = '';
                                console.log('Print button enabled');
                            }
                            if (exportBtn) {
                                exportBtn.disabled = false;
                                exportBtn.style.pointerEvents = '';
                                exportBtn.style.opacity = '';
                                console.log('Export button enabled');
                            }
                        }
                    } else {
                        throw new Error('Failed to get academic ranks data');
                    }
                })
                .catch(error => {
                    console.error('Error updating PS Attribution:', error);
                    if (table) {
                        table.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Error: ${error.message}</td></tr>`;
                    }

                    // Show error alert
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Loading Data',
                        text: `Failed to load PS attribution data: ${error.message}`,
                        footer: 'Check browser console for more details',
                        confirmButtonText: 'OK'
                    });

                    // Keep buttons disabled on error
                    if (printBtn) {
                        printBtn.disabled = true;
                        printBtn.style.pointerEvents = 'none';
                        printBtn.style.opacity = '0.65';
                    }
                    if (exportBtn) {
                        exportBtn.disabled = true;
                        exportBtn.style.pointerEvents = 'none';
                        exportBtn.style.opacity = '0.65';
                    }
                });
        }

        function clearPSAttribution(quarter) {
            // Clear stored PPA details
            selectedPPAs[quarter] = null;

            // Clear date field
            const date = document.getElementById(`ppasDate${quarter}`);
            if (date) {
                date.value = '';
            }

            // Clear campus field
            const campus = document.getElementById(`ppasCampus${quarter}`);
            if (campus) {
                campus.value = '';
            }

            // Clear table
            const table = document.querySelector(`#psTable${quarter}`);
            if (table) {
                table.innerHTML = '<tr><td colspan="6" class="text-center">No PS attribution found for this quarter</td></tr>';
            }

            // Disable buttons and add inline styles
            const printBtn = document.getElementById(`printBtn${quarter}`);
            const exportBtn = document.getElementById(`exportBtn${quarter}`);
            if (printBtn) {
                printBtn.disabled = true;
                printBtn.style.pointerEvents = 'none';
                printBtn.style.opacity = '0.65';
            }
            if (exportBtn) {
                exportBtn.disabled = true;
                exportBtn.style.pointerEvents = 'none';
                exportBtn.style.opacity = '0.65';
            }
        }

        function updatePSTable(quarter, academicRanks) {
            console.log(`Updating PS table for quarter ${quarter}`);
            const table = document.querySelector(`#psTable${quarter}`);
            if (table) {
                table.innerHTML = '';
                let totalPS = 0;
                let totalParticipants = 0;

                if (!Array.isArray(academicRanks) || academicRanks.length === 0) {
                    console.log('No academic ranks to display');
                    table.innerHTML = '<tr><td colspan="6" class="text-center">No PS attribution found for this quarter</td></tr>';
                    if (printBtn) {
                        printBtn.disabled = true;
                        printBtn.style.pointerEvents = 'none';
                        printBtn.style.opacity = '0.65';
                    }
                    if (exportBtn) {
                        exportBtn.disabled = true;
                        exportBtn.style.pointerEvents = 'none';
                        exportBtn.style.opacity = '0.65';
                    }
                    return;
                }

                const ppaDetails = selectedPPAs[quarter];
                const totalDuration = ppaDetails && ppaDetails.total_duration ?
                    parseFloat(ppaDetails.total_duration) :
                    8;

                // Define the custom order for academic ranks
                const rankOrder = {
                    'Instructor I': 1,
                    'Instructor II': 2,
                    'Instructor III': 3,
                    'College Lecturer': 4,
                    'Senior Lecturer': 5,
                    'Master Lecturer': 6,
                    'Assistant Professor I': 7,
                    'Assistant Professor II': 8,
                    'Assistant Professor III': 9,
                    'Assistant Professor IV': 10,
                    'Associate Professor I': 11,
                    'Associate Professor II': 12,
                    'Associate Professor III': 13,
                    'Associate Professor IV': 14,
                    'Associate Professor V': 15,
                    'Professor I': 16,
                    'Professor II': 17,
                    'Professor III': 18,
                    'Professor IV': 19,
                    'Professor V': 20,
                    'Professor VI': 21,
                    'Administrative Aide I': 22,
                    'Administrative Aide II': 23,
                    'Administrative Aide III': 24,
                    'Administrative Aide IV': 25,
                    'Administrative Aide V': 26,
                    'Administrative Aide VI': 27,
                    'Administrative Assistant I': 28,
                    'Administrative Assistant II': 29,
                    'Administrative Assistant III': 30
                };

                // Sort academic ranks according to the predefined order
                academicRanks.sort((a, b) => {
                    // Get the order values for the ranks
                    const orderA = rankOrder[a.rank_name] || 999; // Default high value for unknown ranks
                    const orderB = rankOrder[b.rank_name] || 999;

                    // Sort by the predefined order
                    return orderA - orderB;
                });

                academicRanks.forEach(rank => {
                    if (!rank.monthly_salary) {
                        console.warn(`Missing monthly salary for rank: ${rank.rank_name}`);
                        return;
                    }

                    const ratePerHour = rank.monthly_salary / 176;
                    const ps = ratePerHour * totalDuration * rank.personnel_count;
                    totalPS += ps;
                    totalParticipants += rank.personnel_count;

                    const row = `
                        <tr>
                            <td>${rank.rank_name}</td>
                            <td class="text-center">${rank.personnel_count === 0 ? '-' : rank.personnel_count}</td>
                            <td class="text-end">${rank.monthly_salary === 0 ? '-' : '₱' + rank.monthly_salary.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-end">${ratePerHour === 0 ? '-' : '₱' + ratePerHour.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-center">${rank.personnel_count === 0 ? '-' : totalDuration}</td>
                            <td class="text-end">${ps === 0 ? '-' : '₱' + ps.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        </tr>
                    `;
                    table.innerHTML += row;
                });

                const totalRow = `
                    <tr class="table-active">
                        <td colspan="2" class="text-end"><strong>Total Number of Participants:</strong></td>
                        <td class="text-end"><strong>${totalParticipants === 0 ? '-' : totalParticipants}</strong></td>
                        <td colspan="2" class="text-end"><strong>Total PS:</strong></td>
                        <td class="text-end"><strong>${totalPS === 0 ? '-' : '₱' + totalPS.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                    </tr>
                `;
                table.innerHTML += totalRow;

                document.getElementById(`totalPS${quarter}`).value = totalPS === 0 ? '-' : `₱${totalPS.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

                // Enable export button
                document.getElementById(`exportBtn${quarter}`).disabled = false;

                // Enable buttons after table is updated
                const printBtn = document.getElementById(`printBtn${quarter}`);
                const exportBtn = document.getElementById(`exportBtn${quarter}`);

                console.log('Print button in updatePSTable:', printBtn);
                console.log('Export button in updatePSTable:', exportBtn);

                if (printBtn) {
                    printBtn.disabled = false;
                    console.log('Print button enabled in updatePSTable');
                }
                if (exportBtn) {
                    exportBtn.disabled = false;
                    console.log('Export button enabled in updatePSTable');
                }
            }
        }

        function printPSAttribution(quarter) {
            const ppaDetails = selectedPPAs[quarter];
            if (!ppaDetails) {
                Swal.fire({
                    icon: 'info',
                    title: 'Error',
                    text: 'No PPA selected. Please select a PPA first.',
                });
                return;
            }

            // Create a new window for printing
            const printWindow = window.open('', '_blank');

            // Get the table content
            const table = document.querySelector(`#psTable${quarter}`).closest('.table-responsive');

            // Create the print content with proper styling
            const printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>PS Attribution Report</title>
                    <style>
                        @media print {
                            body {
                                padding: 20px;
                                font-family: Arial, sans-serif;
                            }
                            .print-header {
                                margin-bottom: 30px;
                            }
                            .header-row {
                                display: flex;
                                align-items: flex-start;
                                margin-bottom: 10px;
                                font-size: 16px;
                            }
                            .header-label {
                                width: 120px;
                                text-align: left;
                                font-weight: normal;
                            }
                            .header-content {
                                flex: 1;
                                text-align: left;
                                font-weight: normal;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 20px;
                            }
                            th, td {
                                border: 1px solid #000;
                                padding: 8px;
                                text-align: left;
                            }
                            th {
                                background-color: #f2f2f2;
                            }
                            .text-end {
                                text-align: right;
                            }
                            .text-center {
                                text-align: center;
                            }
                            .table-active {
                                background-color: #f8f9fa;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <div class="header-row">
                            <div class="header-label">Activity Title:</div>
                            <div class="header-content">${ppaDetails.title}</div>
                        </div>
                        <div class="header-row">
                            <div class="header-label">Campus:</div>
                            <div class="header-content">${document.getElementById(`ppasCampus${quarter}`).value || 'N/A'}</div>
                        </div>
                        <div class="header-row">
                            <div class="header-label">Date:</div>
                            <div class="header-content">${document.getElementById(`ppasDate${quarter}`).value}</div>
                        </div>
                    </div>
                    ${table.outerHTML}
                </body>
                </html>
            `;

            // Write the content to the new window
            printWindow.document.write(printContent);
            printWindow.document.close();

            // Add print trigger after a short delay to ensure content is loaded
            setTimeout(() => {
                printWindow.print();
                // Close the window after printing
                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            }, 250);
        }

        // Function to export table to Excel
        function exportToExcel(quarter) {
            const ppaDetails = selectedPPAs[quarter];
            if (!ppaDetails) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No PPA selected. Please select a PPA first.',
                });
                return;
            }

            try {
                // Create workbook and worksheet
                const wb = XLSX.utils.book_new();

                // Get table data
                const table = document.querySelector(`#psTable${quarter}`).closest('table');

                // Create header rows for PPA details
                const headerData = [
                    ['PS Attribution Report'],
                    [''],
                    ['Activity Title:', ppaDetails.title],
                    ['Campus:', document.getElementById(`ppasCampus${quarter}`).value || 'N/A'],
                    ['Date:', document.getElementById(`ppasDate${quarter}`).value],
                    ['']
                ];

                // Get table data including headers
                const tableHeaders = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent);
                const tableRows = Array.from(table.querySelectorAll('tbody tr')).map(row =>
                    Array.from(row.querySelectorAll('td')).map(cell => {
                        // Remove currency symbol and commas from numbers
                        let text = cell.textContent.trim();
                        if (text.startsWith('₱')) {
                            text = text.replace('₱', '').replace(/,/g, '');
                        }
                        return text;
                    })
                );

                // Combine all data
                const wsData = [
                    ...headerData,
                    tableHeaders,
                    ...tableRows
                ];

                // Create worksheet
                const ws = XLSX.utils.aoa_to_sheet(wsData);

                // Set column widths
                const colWidths = [30, 15, 15, 15, 15, 15];
                ws['!cols'] = colWidths.map(width => ({
                    width
                }));

                // Add worksheet to workbook
                XLSX.utils.book_append_sheet(wb, ws, 'PS Attribution');

                // Generate Excel file name
                const fileName = `PS_Attribution_${ppaDetails.quarterTitle}_${ppaDetails.title.replace(/[^a-zA-Z0-9]/g, '_')}.xlsx`;

                // Write to array buffer directly
                const arrayBuffer = XLSX.write(wb, {
                    bookType: 'xlsx',
                    type: 'array'
                });

                // Convert to Uint8Array
                const uint8Array = new Uint8Array(arrayBuffer);

                // Use FileSaver.js-like saveAs function (implemented inline)
                const saveAs = (function() {
                    // Convert Uint8Array to Blob with proper MIME type
                    const data = new Blob([uint8Array], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });

                    // If IE/Edge
                    if (navigator.msSaveBlob) {
                        navigator.msSaveBlob(data, fileName);
                        return;
                    }

                    // For other browsers - create data link using download attribute
                    const downloadLink = document.createElement('a');
                    downloadLink.href = window.URL.createObjectURL(data);
                    downloadLink.download = fileName;
                    downloadLink.rel = 'noopener'; // Security best practice
                    downloadLink.style.display = 'none';

                    // Append link and trigger click
                    document.body.appendChild(downloadLink);
                    downloadLink.click();

                    // Clean up
                    setTimeout(function() {
                        document.body.removeChild(downloadLink);
                        window.URL.revokeObjectURL(downloadLink.href);
                    }, 200);
                })();

                console.log("Excel export completed successfully");

            } catch (error) {
                console.error("Error during Excel export:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Export Failed',
                    text: 'There was an error generating the Excel file. Please try again.'
                });
            }
        }
    </script>
</body>

</html>