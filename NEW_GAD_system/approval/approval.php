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

$isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval - GAD System</title>
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
            --input-bg: #2B3035;
            /* Updated input background color */
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
            min-height: 660px;
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
            height: 24px;
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

        /* Modern card and filter styles */
        .filter-container {
            background: var(--bg-secondary);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        /* Increase filter label font size */
        .gender-issue-filter label,
        .campus-filter label,
        .status-filter label {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: var(--accent-color);
        }

        /* Style the filter icons */
        .gender-issue-filter label i,
        .campus-filter label i,
        .status-filter label i {
            color: var(--accent-color);
        }

        #genderIssueFilter,
        #campusFilter,
        #statusFilter {
            border-radius: 10px;
            font-size: 1rem;
            border: 1px solid var(--border-color);
            background-color: var(--input-bg);
            color: var(--text-primary);
            height: 45px;
            padding: 0.5rem 1rem;
        }

        /* Focus states */
        .form-control:focus,
        .form-select:focus,
        .btn:focus,
        textarea:focus,
        input:focus,
        select:focus {
            box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25) !important;
            border-color: var(--accent-color) !important;
        }

        /* Table styles */
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 2px solid var(--accent-color) !important;
        }

        .table th,
        .table td {
            border-color: var(--border-color) !important;
        }

        /* Add container to maintain border radius with the border */
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background-color: var(--accent-color);
            color: white;
        }

        .table thead th {
            font-weight: 500;
            border: none !important;
            padding: 15px;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: var(--hover-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
        }

        /* Badge styles */
        .badge {
            font-size: 0.85rem;
            padding: 8px 12px;
            font-weight: 500;
            border-radius: 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .badge:before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .badge.bg-warning {
            background: linear-gradient(45deg, #ff9800, #ffab40) !important;
            color: #fff;
            border: none;
        }

        .badge.bg-warning:before {
            background-color: #fff;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
        }

        .badge.bg-success {
            background: linear-gradient(45deg, #4caf50, #8bc34a) !important;
            color: #fff;
            border: none;
        }

        .badge.bg-success:before {
            background-color: #fff;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
        }

        .badge.bg-danger {
            background: linear-gradient(45deg, #f44336, #ff5722) !important;
            color: #fff;
            border: none;
        }

        .badge.bg-danger:before {
            background-color: #fff;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
        }

        /* Add hover effect */
        .badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Button styles */
        .btn-sm {
            border-radius: 8px;
            padding: 6px 12px;
        }

        /* Modal styles */
        .modal-content {
            border-radius: 15px;
            border: none;
            background-color: var(--card-bg);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 20px 25px;
        }

        .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 20px 25px;
        }

        .modal-title {
            font-weight: 600;
            color: var(--accent-color);
        }

        /* Detail card styles */
        .detail-card {
            padding: 15px;
            border-radius: 12px;
            background-color: var(--bg-secondary);
            height: 100%;
        }

        .detail-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .detail-value {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0;
        }

        /* Programs section */
        .section-title {
            font-weight: 600;
            color: var(--accent-color);
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--accent-color);
            display: inline-block;
        }

        .program-card {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            overflow: hidden;
        }

        .program-header {
            background-color: var(--accent-color);
            color: white;
            padding: 12px 15px;
        }

        .program-title {
            margin: 0;
            font-weight: 500;
        }

        .program-body {
            padding: 15px;
        }

        .specific-activities h6 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 10px;
        }

        .specific-activities ul {
            padding-left: 20px;
            margin-bottom: 0;
        }

        .specific-activities li {
            margin-bottom: 5px;
        }

        /* Feedback styles */
        .feedback-history {
            max-height: 250px;
            overflow-y: auto;
            padding: 10px;
            background-color: var(--bg-secondary);
            border-radius: 12px;
        }

        .feedback-item {
            display: flex;
            padding: 10px;
            background-color: white;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: relative;
            /* For positioning the delete button */
        }

        [data-bs-theme="dark"] .feedback-item {
            background-color: #3a3a3a;
        }

        .feedback-index {
            flex: 0 0 30px;
            height: 30px;
            border-radius: 50%;
            background-color: var(--accent-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 15px;
        }

        .feedback-content {
            flex: 1;
            padding-right: 40px;
            /* Add space for the delete button */
        }

        /* New feedback items */
        .feedback-item-new {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background-color: var(--accent-color);
            color: white;
            border-radius: 25px;
            margin-bottom: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .feedback-item-new .feedback-content {
            flex: 1;
        }

        .feedback-item-new .remove-feedback {
            color: white;
            background: transparent;
            border: none;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            margin-left: 10px;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .feedback-item-new .remove-feedback:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .add-feedback-section textarea {
            border-radius: 15px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }

        /* Button styles */
        .btn {
            border-radius: 25px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-primary,
        .btn-success,
        .btn-danger {
            border: none;
        }

        .btn-primary {
            background-color: var(--accent-color);
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
        }

        /* Dark mode adjustments */
        [data-bs-theme="dark"] .feedback-history {
            background-color: var(--dark-input);
        }

        [data-bs-theme="dark"] .detail-card {
            background-color: var(--dark-input);
        }

        [data-bs-theme="dark"] .program-card {
            background-color: var(--dark-input);
        }

        [data-bs-theme="dark"] .filter-container {
            background-color: var(--dark-sidebar);
        }

        [data-bs-theme="dark"] .add-feedback-section textarea {
            background-color: var(--dark-input);
            border-color: var(--dark-border);
        }

        /* Modal scrollbar */
        .modal-body::-webkit-scrollbar {
            width: 5px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background-color: var(--scrollbar-thumb);
            border-radius: 3px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background-color: var(--scrollbar-thumb-hover);
        }

        /* Dark theme input fields */
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] textarea {
            background-color: #2B3035;
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        /* Ensure dark mode filters use the correct background */
        [data-bs-theme="dark"] #genderIssueFilter,
        [data-bs-theme="dark"] #campusFilter,
        [data-bs-theme="dark"] #statusFilter {
            background-color: #2B3035;
        }

        /* Enhanced modal backdrop */
        .modal-backdrop.show {
            opacity: 0.5;
            /* Reduced from 0.8 */
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 1040 !important;
            /* Ensure backdrop is behind modal */
        }

        /* Additional rule to ensure blur works */
        body.modal-open::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 1039;
            background-color: rgba(0, 0, 0, 0.4);
            /* Reduced from 0.7 */
            pointer-events: none;
            display: block;
        }

        /* Make sure modal has a high z-index */
        .modal {
            z-index: 1050 !important;
            /* Higher than backdrop */
        }

        /* Ensure modal content stands out against darkened background */
        .modal-content {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1051 !important;
            /* Higher than modal itself */
        }

        /* Make sure the table has a very visible border and is scrollable */
        .table-responsive {
            border: 2px solid var(--accent-color) !important;
            border-radius: 12px !important;
            overflow: hidden !important;
            padding: 0 !important;
            margin-bottom: 20px !important;
            height: 400px !important;
            /* Set fixed height */
            max-height: 600px !important;
            overflow-y: auto !important;
            /* Make table scrollable */
        }

        /* Fixed header */
        .table thead {
            position: sticky !important;
            top: 0 !important;
            z-index: 10 !important;
            background-color: var(--accent-color) !important;
        }

        .table {
            margin-bottom: 0 !important;
            border-radius: 0 !important;
            border: none !important;
            /* Remove border from table itself */
            border-collapse: collapse !important;
        }

        /* Box style pagination */
        .pagination {
            margin-top: 10px;
            gap: 5px;
            margin-bottom: 0;
        }

        .pagination-container {
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .pagination .page-item .page-link {
            border-radius: 5px;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            font-weight: 500;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            background-color: var(--card-bg);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
        }

        .pagination .page-item .page-link:hover:not(.active) {
            background-color: var(--hover-color);
            color: var(--accent-color);
        }

        /* Disabled state for pagination */
        .pagination .page-item.disabled .page-link {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--text-secondary);
            border-color: var(--border-color);
        }

        /* For dark mode */
        [data-bs-theme="dark"] .pagination .page-item .page-link {
            background-color: var(--dark-input);
            border-color: var(--dark-border);
        }

        [data-bs-theme="dark"] .pagination .page-item.active .page-link {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
        }

        [data-bs-theme="dark"] .pagination .page-item .page-link:hover:not(.active):not(.disabled) {
            background-color: var(--hover-color);
        }

        [data-bs-theme="dark"] .pagination .page-item.disabled .page-link {
            background-color: rgba(0, 0, 0, 0.2);
            color: rgba(255, 255, 255, 0.3);
            border-color: var(--dark-border);
        }

        .no-data-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .no-data-container h5 {
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        /* Add custom styling for the reset filters button */
        .reset-filters-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .reset-filters-btn:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            color: white;
            /* Ensure text stays white on hover */
        }

        /* Add styling for the feedback delete button */
        .feedback-delete-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #dc3545;
            background: transparent;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
            /* Make always visible */
            transition: all 0.2s ease;
        }

        /* Remove the hover rule since the button is now always visible */
        .feedback-item:hover .feedback-delete-btn {
            opacity: 1;
        }

        .feedback-delete-btn:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }

        [data-bs-theme="dark"] .feedback-delete-btn {
            color: #ff6b6b;
        }

        /* Make the feedback content not overlap with the delete button */
        .feedback-content {
            flex: 1;
            padding-right: 40px;
            /* Add space for the delete button */
        }

        .swal-blur-backdrop {
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        .swal2-container {
            z-index: 9999 !important;
            /* Ensure SweetAlert is above other modals */
        }

        .swal2-backdrop-show {
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
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

        /* Keep badge red even when active */
        .approval-link.active .notification-badge {
            background-color: #dc3545;
            color: white;
        }

        [data-bs-theme="dark"] .approval-link.active .notification-badge {
            background-color: #ff5c6c;
            color: white;
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
                    <a class="nav-link dropdown-toggle active" href="#" id="formsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-alt me-2"></i> Forms
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../target_forms/target.php">Target Form</a></li>
                        <li><a class="dropdown-item" href="../gbp_forms/gbp.php">GBP Form</a></li>
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
                    <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i> Reports
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../gpb_reports/gbp_reports.php">Annual GBP Reports</a></li>
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
                    <a href="#" class="nav-link approval-link <?php echo ($currentPage == 'approval.php') ? 'active' : ''; ?>">
                        <i class="fas fa-check-circle me-2"></i> Approval
                        <span id="approvalBadge" class="notification-badge" style="display: none;">0</span>
                    </a>
                <?php endif; ?>
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
            <i class="fas fa-clipboard-check"></i>
            <h2>Approval Management</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title"></h5>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row align-items-center mb-4">
                    <!-- Gender Issue Filter -->
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="gender-issue-filter">
                            <label for="genderIssueFilter" class="fw-bold mb-2"><i class="fas fa-venus-mars me-2"></i>Filter by Gender Issue:</label>
                            <input type="text" id="genderIssueFilter" class="form-control form-control-lg" placeholder="Enter gender issue...">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="campus-filter">
                            <label for="campusFilter" class="fw-bold mb-2"><i class="fas fa-university me-2"></i>Filter by Campus:</label>
                            <select id="campusFilter" class="form-select form-select-lg">
                                <option value="All">All Campuses</option>
                                <option value="Lipa">Lipa</option>
                                <option value="Pablo Borbon">Pablo Borbon</option>
                                <option value="Alangilan">Alangilan</option>
                                <option value="Nasugbu">Nasugbu</option>
                                <option value="Malvar">Malvar</option>
                                <option value="Rosario">Rosario</option>
                                <option value="Balayan">Balayan</option>
                                <option value="Lemery">Lemery</option>
                                <option value="San Juan">San Juan</option>
                                <option value="Lobo">Lobo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="status-filter">
                            <label for="statusFilter" class="fw-bold mb-2"><i class="fas fa-filter me-2"></i>Filter by Status:</label>
                            <select id="statusFilter" class="form-select form-select-lg">
                                <option value="All">All Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Add pagination container after the table -->
                <div class="table-responsive mb-4" style="border: 2px solid var(--accent-color); border-radius: 12px; overflow: hidden; height: 600px; overflow-y: auto;">
                    <table class="table table-hover" id="gbpEntriesTable">
                        <thead>
                            <tr>
                                <th>Campus</th>
                                <th>Category</th>
                                <th>Gender Issue</th>
                                <th>Year</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="gbpEntriesBody">
                            <!-- Data will be loaded here dynamically -->
                        </tbody>
                        <tbody id="noDataMessage" style="display: none;">
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="no-data-container">
                                        <i class="fas fa-database mb-3" style="font-size: 3rem; color: var(--accent-color); opacity: 0.5;"></i>
                                        <h5>No Data Found</h5>
                                        <p class="text-muted">No matching records found with current filters.</p>
                                        <button class="btn mt-2 reset-filters-btn" onclick="resetFilters()">Reset Filters</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination controls -->
                <div class="pagination-container d-flex justify-content-center">
                    <nav aria-label="GBP entries pagination">
                        <ul class="pagination" id="entriesPagination">
                            <!-- Pagination will be generated here -->
                        </ul>
                    </nav>
                </div>

                <!-- Modal for Entry Details -->
                <div class="modal fade" id="entryDetailModal" tabindex="-1" aria-labelledby="entryDetailModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="entryDetailModalLabel">GBP Entry Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="entry-details">
                                    <!-- Basic Info Section -->
                                    <div class="section mb-4">
                                        <h5 class="section-title"><i class="fas fa-info-circle me-2"></i>Basic Info</h5>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-card">
                                                    <h6 class="detail-title"><i class="fas fa-university me-2"></i>Campus</h6>
                                                    <p id="detailCampus" class="detail-value">-</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-card">
                                                    <h6 class="detail-title"><i class="fas fa-calendar-alt me-2"></i>Year</h6>
                                                    <p id="detailYear" class="detail-value">-</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-card">
                                                    <h6 class="detail-title"><i class="fas fa-tags me-2"></i>Category</h6>
                                                    <p id="detailCategory" class="detail-value">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Gender Issue Section -->
                                    <div class="section mb-4">
                                        <h5 class="section-title"><i class="fas fa-venus-mars me-2"></i>Gender Issue</h5>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-card">
                                                    <h6 class="detail-title"><i class="fas fa-venus-mars me-2"></i>Gender Issue</h6>
                                                    <p id="detailGenderIssue" class="detail-value">-</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-card">
                                                    <h6 class="detail-title"><i class="fas fa-question-circle me-2"></i>Cause of Issue</h6>
                                                    <p id="detailCauseOfIssue" class="detail-value">-</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-card">
                                                    <h6 class="detail-title"><i class="fas fa-bullseye me-2"></i>GAD Objective</h6>
                                                    <p id="detailGadObjective" class="detail-value">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Agency & Budget Section -->
                                    <div class="section mb-4">
                                        <h5 class="section-title"><i class="fas fa-building me-2"></i>Agency & Budget</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="detail-card">
                                                    <h6 class="detail-title"><i class="fas fa-building me-2"></i>Relevant Agency</h6>
                                                    <p id="detailRelevantAgency" class="detail-value">-</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="detail-card">
                                                    <h6 class="detail-title"><i class="fas fa-users-cog me-2"></i>Responsible Unit</h6>
                                                    <p id="detailResponsibleUnit" class="detail-value">-</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="detail-card">
                                                    <h6 class="detail-title"><i class="fas fa-money-bill-wave me-2"></i>GAD Budget</h6>
                                                    <p id="detailGadBudget" class="detail-value">-</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="detail-card">
                                                    <h6 class="detail-title"><i class="fas fa-coins me-2"></i>Source of Budget</h6>
                                                    <p id="detailSourceOfBudget" class="detail-value">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Beneficiaries Section -->
                                    <div class="section mb-4">
                                        <h5 class="section-title"><i class="fas fa-users me-2"></i>Beneficiaries</h5>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-card">
                                                    <h6 class="detail-title"><i class="fas fa-male me-2"></i>Male Participants</h6>
                                                    <p id="detailMaleParticipants" class="detail-value">-</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-card">
                                                    <h6 class="detail-title"><i class="fas fa-female me-2"></i>Female Participants</h6>
                                                    <p id="detailFemaleParticipants" class="detail-value">-</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="detail-card">
                                                    <h6 class="detail-title"><i class="fas fa-users me-2"></i>Total Participants</h6>
                                                    <p id="detailTotalParticipants" class="detail-value">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Programs Section -->
                                    <div class="section mb-4">
                                        <h5 class="section-title"><i class="fas fa-project-diagram me-2"></i>Programs & Activities</h5>
                                        <div id="programsContainer" class="programs-container">
                                            <!-- Programs will be dynamically added here -->
                                        </div>
                                    </div>

                                    <!-- Feedback Section -->
                                    <div class="section mb-4">
                                        <h5 class="section-title"><i class="fas fa-comments me-2"></i>Feedback History</h5>
                                        <div id="feedbackHistory" class="feedback-history">
                                            <!-- Feedback history will be displayed here -->
                                        </div>
                                    </div>

                                    <!-- Add New Feedback -->
                                    <div class="section mb-4">
                                        <h5 class="section-title"><i class="fas fa-comment-medical me-2"></i>Add Feedback</h5>
                                        <div class="feedback-form">
                                            <div class="mb-3">
                                                <textarea id="newFeedback" class="form-control" rows="3" placeholder="Enter your feedback here..."></textarea>
                                            </div>
                                            <div id="feedbackItems" class="feedback-items mb-3">
                                                <!-- New feedback items will be added here -->
                                            </div>
                                            <button id="addFeedbackItem" class="btn btn-outline-primary me-2"><i class="fas fa-plus-circle me-2"></i>Add Feedback</button>
                                            <button id="clearFeedback" class="btn btn-outline-secondary"><i class="fas fa-eraser me-2"></i>Clear All</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="d-flex justify-content-end w-100">
                                    <div>
                                        <button id="rejectBtn" type="button" class="btn btn-danger me-2"><i class="fas fa-times-circle me-2"></i>Reject</button>
                                        <button id="approveBtn" type="button" class="btn btn-success"><i class="fas fa-check-circle me-2"></i>Approve</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Script to handle the approval system functionality -->
                <script>
                    // Pagination settings
                    const rowsPerPage = 5;
                    let currentPage = 1;
                    let filteredEntries = [];

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

                    // Function to load GBP entries from the server
                    function loadGbpEntriesFromServer() {
                        const genderIssueFilter = document.getElementById('genderIssueFilter').value.toLowerCase().trim();
                        const campusFilter = document.getElementById('campusFilter').value;
                        const statusFilter = document.getElementById('statusFilter').value;

                        // Prepare the data for the AJAX request
                        const formData = new FormData();
                        formData.append('action', 'fetch_gbp_entries');
                        formData.append('gender_issue', genderIssueFilter);
                        formData.append('campus', campusFilter);
                        formData.append('status', statusFilter);

                        // Make AJAX request
                        fetch('gbp_api.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    filteredEntries = data.entries;
                                    loadEntriesWithPagination();
                                } else {
                                    // Handle error
                                    document.getElementById('gbpEntriesBody').innerHTML = `<tr><td colspan="6" class="text-center py-5"><div class="text-danger"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p>${data.message || 'Failed to load data'}</p></div></td></tr>`;
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching data:', error);
                                document.getElementById('gbpEntriesBody').innerHTML = `<tr><td colspan="6" class="text-center py-5"><div class="text-danger"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p>Error: ${error.message}</p></div></td></tr>`;
                            });
                    }

                    // Load entries with pagination
                    function loadEntriesWithPagination() {
                        const startIndex = (currentPage - 1) * rowsPerPage;
                        const endIndex = startIndex + rowsPerPage;
                        const paginatedEntries = filteredEntries.slice(startIndex, endIndex);

                        loadEntries(paginatedEntries);
                        updatePagination();
                    }

                    // Load entries to the table
                    function loadEntries(entries) {
                        const tableBody = document.getElementById('gbpEntriesBody');
                        const noDataMessage = document.getElementById('noDataMessage');

                        tableBody.innerHTML = '';

                        // Check if there are entries to display
                        if (entries.length === 0) {
                            tableBody.style.display = 'none';
                            noDataMessage.style.display = 'table-row-group';
                        } else {
                            tableBody.style.display = 'table-row-group';
                            noDataMessage.style.display = 'none';

                            entries.forEach(entry => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                <td>${entry.campus}</td>
                                <td>${entry.category}</td>
                                <td>${entry.gender_issue}</td>
                                <td>${entry.year}</td>
                                <td>
                                    <span class="badge bg-${entry.status === 'Approved' ? 'success' : entry.status === 'Rejected' ? 'danger' : 'warning'} rounded-pill">
                                        ${entry.status}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary view-details" data-id="${entry.id}">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            `;
                                tableBody.appendChild(row);
                            });
                        }

                        // Add event listeners to view buttons
                        document.querySelectorAll('.view-details').forEach(button => {
                            button.addEventListener('click', function() {
                                const entryId = this.getAttribute('data-id');
                                viewEntryDetails(entryId);
                            });
                        });
                    }

                    // Update pagination controls
                    function updatePagination() {
                        const totalPages = Math.ceil(filteredEntries.length / rowsPerPage);
                        const paginationElement = document.getElementById('entriesPagination');
                        paginationElement.innerHTML = '';

                        // Previous button
                        const prevLi = document.createElement('li');
                        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                        prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><i class="fas fa-chevron-left"></i></a>`;
                        prevLi.addEventListener('click', function(e) {
                            e.preventDefault();
                            if (currentPage > 1) {
                                currentPage--;
                                loadEntriesWithPagination();
                            }
                        });
                        paginationElement.appendChild(prevLi);

                        // Page numbers
                        for (let i = 1; i <= totalPages; i++) {
                            const li = document.createElement('li');
                            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
                            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                            li.addEventListener('click', function(e) {
                                e.preventDefault();
                                currentPage = i;
                                loadEntriesWithPagination();
                            });
                            paginationElement.appendChild(li);
                        }

                        // Next button
                        const nextLi = document.createElement('li');
                        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                        nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><i class="fas fa-chevron-right"></i></a>`;
                        nextLi.addEventListener('click', function(e) {
                            e.preventDefault();
                            if (currentPage < totalPages) {
                                currentPage++;
                                loadEntriesWithPagination();
                            }
                        });
                        paginationElement.appendChild(nextLi);
                    }

                    // View entry details
                    function viewEntryDetails(entryId) {
                        // Show loading indicator in modal
                        document.getElementById('detailCampus').textContent = '-';
                        document.getElementById('detailYear').textContent = '-';
                        document.getElementById('detailCategory').textContent = '-';
                        document.getElementById('detailGenderIssue').textContent = '-';
                        document.getElementById('detailCauseOfIssue').textContent = '-';
                        document.getElementById('detailGadObjective').textContent = '-';
                        document.getElementById('detailRelevantAgency').textContent = '-';
                        document.getElementById('detailGadBudget').textContent = '-';
                        document.getElementById('detailSourceOfBudget').textContent = '-';
                        document.getElementById('detailResponsibleUnit').textContent = '-';
                        document.getElementById('detailMaleParticipants').textContent = '-';
                        document.getElementById('detailFemaleParticipants').textContent = '-';
                        document.getElementById('detailTotalParticipants').textContent = '-';

                        // Show loading indicator for programs
                        document.getElementById('programsContainer').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading details...</p></div>';

                        // Show loading indicator for feedback
                        document.getElementById('feedbackHistory').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading feedback...</p></div>';

                        // Show the modal while loading
                        const modal = new bootstrap.Modal(document.getElementById('entryDetailModal'));
                        modal.show();

                        // Prepare the data for the AJAX request
                        const formData = new FormData();
                        formData.append('action', 'fetch_gbp_details');
                        formData.append('id', entryId);

                        // Make AJAX request
                        fetch('gbp_api.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    const entry = data.entry;

                                    // Set basic details
                                    document.getElementById('detailCampus').textContent = entry.campus;
                                    document.getElementById('detailYear').textContent = entry.year;
                                    document.getElementById('detailCategory').textContent = entry.category;
                                    document.getElementById('detailGenderIssue').textContent = entry.gender_issue;
                                    document.getElementById('detailCauseOfIssue').textContent = entry.cause_of_issue;
                                    document.getElementById('detailGadObjective').textContent = entry.gad_objective;
                                    document.getElementById('detailRelevantAgency').textContent = entry.relevant_agency;
                                    document.getElementById('detailGadBudget').textContent = `₱${parseFloat(entry.gad_budget).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                    document.getElementById('detailSourceOfBudget').textContent = entry.source_of_budget;
                                    document.getElementById('detailResponsibleUnit').textContent = entry.responsible_unit;
                                    document.getElementById('detailMaleParticipants').textContent = entry.male_participants;
                                    document.getElementById('detailFemaleParticipants').textContent = entry.female_participants;
                                    document.getElementById('detailTotalParticipants').textContent = entry.total_participants;

                                    // Load programs
                                    const programsContainer = document.getElementById('programsContainer');
                                    programsContainer.innerHTML = '';

                                    if (entry.generic_activity.length > 0) {
                                        entry.generic_activity.forEach((program, index) => {
                                            const programCard = document.createElement('div');
                                            programCard.className = 'program-card mb-3';

                                            let specificActivitiesHTML = '';
                                            if (entry.specific_activities[index] && entry.specific_activities[index].length > 0) {
                                                specificActivitiesHTML = `
                                    <div class="specific-activities">
                                        <h6>Specific Activities</h6>
                                        <ul>
                                            ${entry.specific_activities[index].map(activity => `<li>${activity}</li>`).join('')}
                                        </ul>
                                    </div>
                                `;
                                            }

                                            programCard.innerHTML = `
                                <div class="program-header">
                                    <h6 class="program-title"><i class="fas fa-cogs me-2"></i>Program ${index + 1}: ${program}</h6>
                                </div>
                                <div class="program-body">
                                    ${specificActivitiesHTML}
                                </div>
                            `;
                                            programsContainer.appendChild(programCard);
                                        });
                                    } else {
                                        programsContainer.innerHTML = '<p class="text-muted">No programs available.</p>';
                                    }

                                    // Load feedback history
                                    const feedbackHistory = document.getElementById('feedbackHistory');
                                    feedbackHistory.innerHTML = '';

                                    if (entry.feedback && entry.feedback.length > 0) {
                                        entry.feedback.forEach((feedback, index) => {
                                            const feedbackItem = document.createElement('div');
                                            feedbackItem.className = 'feedback-item';
                                            feedbackItem.dataset.index = index;
                                            feedbackItem.innerHTML = `
                                    <div class="feedback-index">${index + 1}</div>
                                    <div class="feedback-content">${feedback}</div>
                                            <button class="feedback-delete-btn" title="Delete feedback" data-index="${index}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                `;
                                            feedbackHistory.appendChild(feedbackItem);

                                            // Add click event for delete button
                                            feedbackItem.querySelector('.feedback-delete-btn').addEventListener('click', function() {
                                                deleteFeedbackItem(entryId, index);
                                            });
                                        });
                                    } else {
                                        feedbackHistory.innerHTML = '<p class="text-muted">No feedback provided yet.</p>';
                                    }

                                    // Clear new feedback items
                                    document.getElementById('feedbackItems').innerHTML = '';
                                    document.getElementById('newFeedback').value = '';

                                    // Set current entry ID for approve/reject actions
                                    document.getElementById('approveBtn').setAttribute('data-id', entry.id);
                                    document.getElementById('rejectBtn').setAttribute('data-id', entry.id);

                                    // Disable approve/reject buttons based on current status
                                    if (entry.status === 'Approved') {
                                        document.getElementById('approveBtn').disabled = true;
                                        document.getElementById('approveBtn').classList.add('btn-disabled');
                                        document.getElementById('rejectBtn').disabled = false;
                                        document.getElementById('rejectBtn').classList.remove('btn-disabled');
                                    } else if (entry.status === 'Rejected') {
                                        document.getElementById('approveBtn').disabled = false;
                                        document.getElementById('approveBtn').classList.remove('btn-disabled');
                                        document.getElementById('rejectBtn').disabled = true;
                                        document.getElementById('rejectBtn').classList.add('btn-disabled');
                                    } else {
                                        document.getElementById('approveBtn').disabled = false;
                                        document.getElementById('approveBtn').classList.remove('btn-disabled');
                                        document.getElementById('rejectBtn').disabled = false;
                                        document.getElementById('rejectBtn').classList.remove('btn-disabled');
                                    }
                                } else {
                                    // Handle error
                                    document.getElementById('programsContainer').innerHTML = `<div class="text-danger text-center py-4"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p>${data.message || 'Failed to load details'}</p></div>`;
                                    document.getElementById('feedbackHistory').innerHTML = `<div class="text-danger text-center py-4"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p>${data.message || 'Failed to load feedback'}</p></div>`;
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching details:', error);
                                document.getElementById('programsContainer').innerHTML = `<div class="text-danger text-center py-4"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p>Error: ${error.message}</p></div>`;
                                document.getElementById('feedbackHistory').innerHTML = `<div class="text-danger text-center py-4"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p>Error: ${error.message}</p></div>`;
                            });
                    }

                    // Add feedback item
                    document.getElementById('addFeedbackItem').addEventListener('click', function() {
                        const feedbackText = document.getElementById('newFeedback').value.trim();
                        if (!feedbackText) return;

                        const feedbackItems = document.getElementById('feedbackItems');
                        const feedbackItem = document.createElement('div');
                        feedbackItem.className = 'feedback-item-new';
                        feedbackItem.innerHTML = `
                            <div class="feedback-content">${feedbackText}</div>
                            <button class="btn btn-sm btn-outline-danger remove-feedback">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        feedbackItems.appendChild(feedbackItem);

                        // Add remove event listener
                        feedbackItem.querySelector('.remove-feedback').addEventListener('click', function() {
                            feedbackItem.remove();
                        });

                        // Clear the input
                        document.getElementById('newFeedback').value = '';
                    });

                    // Clear all feedback
                    document.getElementById('clearFeedback').addEventListener('click', function() {
                        document.getElementById('feedbackItems').innerHTML = '';
                        document.getElementById('newFeedback').value = '';
                    });

                    // Add new function to delete feedback items
                    function deleteFeedbackItem(entryId, index) {
                        Swal.fire({
                            title: 'Delete Feedback',
                            text: 'Are you sure you want to delete this feedback item?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, delete it!',
                            backdrop: `rgba(0, 0, 0, 0.7)`,
                            customClass: {
                                popup: 'swal-blur-backdrop'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Show loading indicator
                                document.getElementById('feedbackHistory').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Deleting feedback...</p></div>';

                                // Prepare the data for the AJAX request
                                const formData = new FormData();
                                formData.append('action', 'delete_feedback');
                                formData.append('gbp_id', entryId);
                                formData.append('feedback_index', index);

                                // Make AJAX request
                                fetch('gbp_api.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error('Network response was not ok');
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        if (data.success) {
                                            // Show success message with timer instead of OK button
                                            Swal.fire({
                                                title: 'Deleted!',
                                                text: 'The feedback item has been deleted.',
                                                icon: 'success',
                                                showConfirmButton: false,
                                                timer: 1500,
                                                timerProgressBar: true,
                                                backdrop: `rgba(0, 0, 0, 0.7)`,
                                                customClass: {
                                                    popup: 'swal-blur-backdrop'
                                                }
                                            });

                                            // Only refresh the feedback history section, not the entire modal
                                            refreshFeedbackHistory(entryId);
                                        } else {
                                            Swal.fire({
                                                title: 'Error!',
                                                text: data.message || 'Failed to delete feedback.',
                                                icon: 'error',
                                                confirmButtonColor: '#6a1b9a'
                                            });

                                            // Refresh only the feedback history
                                            refreshFeedbackHistory(entryId);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error deleting feedback:', error);
                                        Swal.fire({
                                            title: 'Error!',
                                            text: error.message,
                                            icon: 'error',
                                            confirmButtonColor: '#6a1b9a'
                                        });

                                        // Refresh only the feedback history
                                        refreshFeedbackHistory(entryId);
                                    });
                            }
                        });
                    }

                    // Add new function to refresh only the feedback history section
                    function refreshFeedbackHistory(entryId) {
                        // Show loading indicator in feedback history section
                        document.getElementById('feedbackHistory').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Refreshing feedback...</p></div>';

                        // Prepare the data for the AJAX request
                        const formData = new FormData();
                        formData.append('action', 'fetch_gbp_details');
                        formData.append('id', entryId);

                        // Make AJAX request
                        fetch('gbp_api.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    const entry = data.entry;

                                    // Update only the feedback history section
                                    const feedbackHistory = document.getElementById('feedbackHistory');
                                    feedbackHistory.innerHTML = '';

                                    if (entry.feedback && entry.feedback.length > 0) {
                                        entry.feedback.forEach((feedback, index) => {
                                            const feedbackItem = document.createElement('div');
                                            feedbackItem.className = 'feedback-item';
                                            feedbackItem.dataset.index = index;
                                            feedbackItem.innerHTML = `
                                            <div class="feedback-index">${index + 1}</div>
                                            <div class="feedback-content">${feedback}</div>
                                            <button class="feedback-delete-btn" title="Delete feedback" data-index="${index}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        `;
                                            feedbackHistory.appendChild(feedbackItem);

                                            // Add click event for delete button
                                            feedbackItem.querySelector('.feedback-delete-btn').addEventListener('click', function() {
                                                deleteFeedbackItem(entryId, index);
                                            });
                                        });
                                    } else {
                                        feedbackHistory.innerHTML = '<p class="text-muted">No feedback provided yet.</p>';
                                    }
                                } else {
                                    // Handle error
                                    document.getElementById('feedbackHistory').innerHTML = `<div class="text-danger text-center py-4"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p>${data.message || 'Failed to load feedback'}</p></div>`;
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching feedback:', error);
                                document.getElementById('feedbackHistory').innerHTML = `<div class="text-danger text-center py-4"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p>Error: ${error.message}</p></div>`;
                            });
                    }

                    // Approve entry
                    document.getElementById('approveBtn').addEventListener('click', function() {
                        const entryId = this.getAttribute('data-id');

                        Swal.fire({
                            title: 'Confirm Approval',
                            text: 'Are you sure you want to approve this GBP entry?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, approve it!',
                            backdrop: `rgba(0, 0, 0, 0.7)`,
                            customClass: {
                                popup: 'swal-blur-backdrop'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Prepare the data for the AJAX request
                                const formData = new FormData();
                                formData.append('action', 'approve_gbp');
                                formData.append('id', entryId);

                                // Make AJAX request
                                fetch('gbp_api.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error('Network response was not ok');
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        if (data.success) {
                                            Swal.fire({
                                                title: 'Approved!',
                                                text: 'The GBP entry has been approved.',
                                                icon: 'success',
                                                showConfirmButton: false,
                                                timer: 1500,
                                                timerProgressBar: true,
                                                backdrop: `rgba(0, 0, 0, 0.7)`,
                                                customClass: {
                                                    popup: 'swal-blur-backdrop'
                                                }
                                            });

                                            // Close the modal and refresh the table
                                            bootstrap.Modal.getInstance(document.getElementById('entryDetailModal')).hide();
                                            loadGbpEntriesFromServer();
                                            // Update the notification badge
                                            updateNotificationBadge('gbp_api.php', 'count_pending', 'approvalBadge');
                                        } else {
                                            Swal.fire({
                                                title: 'Error!',
                                                text: data.message || 'Failed to approve entry.',
                                                icon: 'error',
                                                confirmButtonColor: '#6a1b9a'
                                            });
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error approving entry:', error);
                                        Swal.fire({
                                            title: 'Error!',
                                            text: error.message,
                                            icon: 'error',
                                            confirmButtonColor: '#6a1b9a'
                                        });
                                    });
                            }
                        });
                    });

                    // Reject entry
                    document.getElementById('rejectBtn').addEventListener('click', function() {
                        const entryId = this.getAttribute('data-id');

                        // Get all feedback items
                        const feedbackItems = Array.from(document.querySelectorAll('.feedback-item-new .feedback-content')).map(item => item.textContent);

                        if (feedbackItems.length === 0) {
                            Swal.fire({
                                title: 'Feedback Required',
                                text: 'Please add at least one feedback item before rejecting the entry.',
                                icon: 'warning',
                                confirmButtonColor: '#6a1b9a'
                            });
                            return;
                        }

                        Swal.fire({
                            title: 'Confirm Rejection',
                            text: 'Are you sure you want to reject this GBP entry?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, reject it!',
                            backdrop: `rgba(0, 0, 0, 0.7)`,
                            customClass: {
                                popup: 'swal-blur-backdrop'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Prepare the data for the AJAX request
                                const formData = new FormData();
                                formData.append('action', 'reject_gbp');
                                formData.append('id', entryId);
                                formData.append('feedback', JSON.stringify(feedbackItems));

                                // Make AJAX request
                                fetch('gbp_api.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error('Network response was not ok');
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        if (data.success) {
                                            Swal.fire({
                                                title: 'Rejected!',
                                                text: 'The GBP entry has been rejected with feedback.',
                                                icon: 'success',
                                                showConfirmButton: false,
                                                timer: 1500,
                                                timerProgressBar: true,
                                                backdrop: `rgba(0, 0, 0, 0.7)`,
                                                customClass: {
                                                    popup: 'swal-blur-backdrop'
                                                }
                                            });

                                            // Close the modal and refresh the table
                                            bootstrap.Modal.getInstance(document.getElementById('entryDetailModal')).hide();
                                            loadGbpEntriesFromServer();
                                            // Update the notification badge
                                            updateNotificationBadge('gbp_api.php', 'count_pending', 'approvalBadge');
                                        } else {
                                            Swal.fire({
                                                title: 'Error!',
                                                text: data.message || 'Failed to reject entry.',
                                                icon: 'error',
                                                confirmButtonColor: '#6a1b9a'
                                            });
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error rejecting entry:', error);
                                        Swal.fire({
                                            title: 'Error!',
                                            text: error.message,
                                            icon: 'error',
                                            confirmButtonColor: '#6a1b9a'
                                        });
                                    });
                            }
                        });
                    });

                    // Function to reset filters
                    function resetFilters() {
                        document.getElementById('genderIssueFilter').value = '';
                        document.getElementById('campusFilter').value = 'All';
                        document.getElementById('statusFilter').value = 'All';
                        loadGbpEntriesFromServer();
                    }

                    // Add filter change event listeners
                    document.getElementById('genderIssueFilter').addEventListener('input', function() {
                        currentPage = 1; // Reset to first page when filter changes
                        loadGbpEntriesFromServer();
                    });
                    document.getElementById('campusFilter').addEventListener('change', function() {
                        currentPage = 1; // Reset to first page when filter changes
                        loadGbpEntriesFromServer();
                    });
                    document.getElementById('statusFilter').addEventListener('change', function() {
                        currentPage = 1; // Reset to first page when filter changes
                        loadGbpEntriesFromServer();
                    });

                    // Initial load
                    document.addEventListener('DOMContentLoaded', function() {
                        loadGbpEntriesFromServer();
                    });
                </script>

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
    </script>
</body>

</html>