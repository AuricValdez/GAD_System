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
    <title>Signatory - GAD System</title>
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
            transition: opacity 0.50s ease-in-out;
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
            min-height: 465px;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* Remove vertical line styling */
        #ppasForm h5 {
            margin-bottom: 15px;
        }

        #ppasForm .form-row:first-child::after {
            display: none;
        }

        /* Remove the header-separator div */
        .header-separator {
            display: none;
        }

        /* Remove the vertical line from the card */
        .card::after {
            display: none;
            /* Hide the vertical line */
            content: none;
            /* Ensure no content is generated */
            background: none;
            /* Remove background color */
        }

        /* Position the form correctly for the separator */
        #ppasForm {
            position: relative;
        }

        #ppasForm.row {
            flex: 1;
        }

        #ppasForm .col-12.text-end {
            margin-top: auto !important;
            padding-top: 20px;
            /* Remove horizontal line */
            border-top: none;
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
            border-bottom: none !important;
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
            position: relative;
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
            -webkit-backdrop-filter: blur(20px) !important;
            backdrop-filter: blur(20px) !important;
            background-color: rgba(0, 0, 0, 0.7) !important;
            transform: translateZ(0);
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


        /* Dark mode input color overrides */
        [data-bs-theme="dark"] .form-control:read-only {
            background-color: #37383A !important;
            border: 1px dotted #555 !important;
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .form-control:not(:read-only) {
            background-color: #2B3035 !important;
            border-color: #495057 !important;
            color: #e9ecef !important;
        }

        /* Light mode input color overrides */
        [data-bs-theme="light"] .form-control:read-only {
            background-color: #f2f2f2 !important;
            border: 1px dotted #c8c8c8 !important;
            color: #555555 !important;
        }

        [data-bs-theme="light"] .form-control:not(:read-only) {
            background-color: #ffffff !important;
            border-color: #ced4da !important;
            color: #212529 !important;
        }

        /* Placeholder text styling for light mode */
        [data-bs-theme="light"] .form-control::placeholder {
            color: #666666 !important;
            opacity: 1 !important;
        }

        /* Modal backdrop blur */
        .modal-backdrop.show {
            -webkit-backdrop-filter: blur(20px) !important;
            backdrop-filter: blur(20px) !important;
            opacity: 1 !important;
            transform: translateZ(0);
        }

        /* Light mode modal backdrop */
        [data-bs-theme="light"] .modal-backdrop.show {
            background-color: rgba(108, 117, 125, 0.75) !important;
        }

        /* Dark mode modal backdrop */
        [data-bs-theme="dark"] .modal-backdrop.show {
            background-color: rgba(0, 0, 0, 0.7) !important;
        }

        .modal-content {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            transform: translateZ(0);
        }

        /* Light mode modal styles */
        [data-bs-theme="light"] .modal-content {
            background-color: #ffffff !important;
            color: #212529 !important;
            border: 1px solid #dee2e6 !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        /* Dark mode modal styles */
        [data-bs-theme="dark"] .modal-content {
            -webkit-backdrop-filter: blur(20px) !important;
            backdrop-filter: blur(20px) !important;
            background-color: rgba(33, 37, 41, 0.85) !important;
        }

        .modal {
            padding-right: 0 !important;
        }

        /* Matching logout blur effect */
        .swal2-container.swal2-backdrop-show {
            -webkit-backdrop-filter: blur(20px) !important;
            backdrop-filter: blur(20px) !important;
            background-color: rgba(0, 0, 0, 0.7) !important;
            transform: translateZ(0);
        }

        .swal2-popup {
            -webkit-backdrop-filter: blur(20px) !important;
            backdrop-filter: blur(20px) !important;
            background-color: rgba(33, 37, 41, 0.85) !important;
            transform: translateZ(0);
        }

        /* Add these styles to add a vertical line between name and position columns */
        .name-column {
            position: relative;
        }

        /* Remove the line by commenting out this rule 
.name-column::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    height: 100%;
    width: 1px;
    background-color: var(--border-color);
}
*/

        /* Remove dark mode line enhancement 
[data-bs-theme="dark"] .name-column::after {
    background-color: rgba(255, 255, 255, 0.2);
}
*/

        /* Enhance the vertical line styling for the name-position separator */
        .form-row {
            position: relative;
        }

        /* Remove continuous vertical line 
.card-body::before {
    content: '';
    position: absolute;
    top: 60px;
    bottom: 60px;
    left: 50%;
    width: 1px;
    background-color: var(--border-color);
    z-index: 1;
}

[data-bs-theme="dark"] .card-body::before {
    background-color: rgba(255, 255, 255, 0.2);
}
*/

        /* Add these CSS rules after the following line:
 * "/* Modern Card Styles */
        
 */

        /* Add horizontal line below Name and Position headings */
        .row.mb-3 {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 20px !important;
            border-bottom: 1px solid var(--border-color);
        }

        /* Add vertical line after Name column */
        .row.mb-3 .col-md-6:first-child {
            position: relative;
        }


        /* Add horizontal line above CRUD operations (buttons) */
        .col-12.text-end {
            position: relative;
            margin-top: 20px !important;
            padding-top: 20px !important;
            border-top: 1px solid var(--border-color) !important;
        }

        /* Dark mode adjustments */
        [data-bs-theme="dark"] .row.mb-3 {
            border-bottom-color: var(--dark-border);
        }

        [data-bs-theme="dark"] .col-12.text-end {
            border-top-color: var(--dark-border) !important;
        }

        #filterBtn {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
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

        #filterBtn:hover,
        #filterBtn:focus {
            background: #0d6efd;
            color: white;
        }

        /* Style for dropdown */
        .dropdown-menu {
            min-width: 200px;
            padding: 8px 0;
            margin: 0;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .dropdown-item {
            padding: 8px 16px;
        }

        .dropdown-item:hover {
            background-color: rgba(13, 110, 253, 0.1);
        }

        [data-bs-theme="dark"] .dropdown-item:hover {
            background-color: rgba(13, 110, 253, 0.2);
        }

        /* Enhance form controls in light mode */
        [data-bs-theme="light"] .modal .form-control,
        [data-bs-theme="light"] .modal .form-select {
            background-color: #f8f9fa !important;
            border: 1px solid #ced4da !important;
            color: #212529 !important;
        }

        [data-bs-theme="light"] .modal .form-label {
            color: #495057 !important;
            font-weight: 500;
        }

        [data-bs-theme="light"] .modal .modal-header {
            background-color: #f8f9fa !important;
            border-bottom: 1px solid #dee2e6 !important;
        }

        [data-bs-theme="light"] .modal .btn-close {
            filter: none !important;
        }

        /* Placeholder text colors */
        .form-control::placeholder {
            color: #666666 !important;
            opacity: 1 !important;
            font-weight: 500 !important;
        }

        /* Darker placeholder text for light mode */
        [data-bs-theme="light"] .form-control::placeholder {
            color: #555555 !important;
        }

        /* Enhance form control styling in light mode for better visibility */
        [data-bs-theme="light"] .form-control {
            background-color: #f5f5f5 !important;
            border: 1px solid #c0c0c0 !important;
        }

        [data-bs-theme="light"] .form-control:read-only {
            background-color: #e8e8e8 !important;
            border: 1px solid #bbbbbb !important;
            color: #444444 !important;
        }

        /* Extra specific selector to ensure placeholder text is visible */
        [data-bs-theme="light"] input.form-control::placeholder {
            color: #444444 !important;
            opacity: 1 !important;
            font-weight: 500 !important;
        }

        /* Dark mode modal styles */
        [data-bs-theme="dark"] .modal-content {
            -webkit-backdrop-filter: blur(20px) !important;
            backdrop-filter: blur(20px) !important;
            background-color: #212529 !important;
            color: #fff !important;
            border: 1px solid #495057 !important;
        }

        /* Dark mode form elements in modals */
        [data-bs-theme="dark"] .modal .form-select,
        [data-bs-theme="dark"] .modal .form-control {
            background-color: #2b3035 !important;
            border-color: #495057 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .modal .form-label {
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .modal .modal-header {
            background-color: #343a40 !important;
            border-bottom: 1px solid #495057 !important;
        }

        [data-bs-theme="dark"] .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%) !important;
        }

        /* Add these dropdown submenu styles to the CSS */
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
                    <a class="nav-link dropdown-toggle active" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users me-2"></i> Staff
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../academic_rank/academic.php">Academic Rank</a></li>
                        <li><a class="dropdown-item" href="../personnel_list/personnel_list.php">Personnel List</a></li>
                        <li><a class="dropdown-item" href="#">Signatory</a></li>
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
                    <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i> Reports
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../gpb_reports/gbp_reports.php">Annual GPB Reports</a></li>
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
            <i class="fa-solid fa-signature"></i>
            <h2>Signatory Management</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">View Signatory

                </h5>
            </div>
            <div class="card-body">
                <form id="ppasForm">
                    <div class="row mb-3">
                        <div class="col-md-6">

                            <h5>Name</h5>
                        </div>
                        <div class="col-md-6">
                            <h5>Position</h5>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="name1" name="name1" placeholder="Enter name" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="rank1" name="rank1" value="GAD Head Secretariat" readonly>
                        </div>
                    </div>

                    <div class="form-row mt-3">
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="name2" name="name2" placeholder="Enter name" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="rank2" name="rank2" value="Vice Chancellor For Research, Development and Extension" readonly>
                        </div>
                    </div>

                    <div class="form-row mt-3">
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="name3" name="name3" placeholder="Enter name" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="rank3" name="rank3" value="Chancellor" readonly>
                        </div>
                    </div>

                    <div class="form-row mt-3">
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="name4" name="name4" placeholder="Enter name" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="rank4" name="rank4" value="Assistant Director For GAD Advocacies" readonly>
                        </div>
                    </div>

                    <div class="form-row mt-3">
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="name5" name="name5" placeholder="Enter name" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="rank5" name="rank5" value="Head of Extension Services" readonly>
                        </div>
                    </div>

                    <div class="form-row mt-3">
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="name6" name="name6" placeholder="Enter name" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="rank6" name="rank6" value="Vice Chancellor for Administration and Finance" readonly>
                        </div>
                    </div>

                    <div class="form-row mt-3">
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="name7" name="name7" placeholder="Enter name" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="rank7" name="rank7" value="Dean" readonly>
                        </div>
                    </div>



                    <div class="col-12 text-end mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Replace dropdown with a button that opens a modal -->
                            <button type="button" class="btn-icon" id="filterBtn">
                                <i class="fas fa-filter"></i>
                            </button>
                            <div class="d-inline-flex gap-3">
                                <button type="submit" class="btn-icon btn-disabled" id="addBtn" disabled>
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button type="button" class="btn-icon" id="editBtn">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Signatory Modal -->
    <div class="modal fade" id="viewSignatoryModal" tabindex="-1" aria-labelledby="viewSignatoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewSignatoryModalLabel">SIGNATORY LIST</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php if (isset($_SESSION['username']) && $_SESSION['username'] === 'Central'): ?>
                                <select class="form-select" id="campusFilter">
                                    <option value="all">All Campuses</option>
                                    <option value="Alangilan">Alangilan</option>
                                    <option value="Arasof">ARASOF-Nasugbu</option>
                                    <option value="Balayan">Balayan</option>
                                    <option value="Lemery">Lemery</option>
                                    <option value="Lipa">Lipa</option>
                                    <option value="Lobo">Lobo</option>
                                    <option value="Mabini">Mabini</option>
                                    <option value="Malvar">Malvar</option>
                                    <option value="Pablo Borbon">Pablo Borbon</option>
                                    <option value="Rosario">Rosario</option>
                                    <option value="San Juan">San Juan</option>
                                </select>
                            <?php else: ?>
                                <div class="form-control bg-light">Campus: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></div>
                                <input type="hidden" id="campusFilter" value="<?php echo htmlspecialchars($_SESSION['username']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="searchSignatory" placeholder="Search by name...">
                        </div>
                    </div>
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>1</th>
                                        <th>Position 1</th>
                                        <th>2</th>
                                        <th>Position 2</th>
                                        <th>3</th>
                                        <th>Position 3</th>
                                        <th>4</th>
                                        <th>Position 4</th>
                                        <th>5</th>
                                        <th>Position 5</th>
                                        <th>6</th>
                                        <th>Position 6</th>
                                        <th>7</th>
                                        <th>Position 7</th>
                                    </tr>
                                </thead>
                                <tbody id="signatoryTableBody">
                                    <!-- Table content will be dynamically populated -->
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div id="signatoryCount" class="text-muted">
                                <!-- Signatory count will be dynamically added here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Signatory Modal -->
    <div class="modal fade" id="editSignatoryModal" tabindex="-1" aria-labelledby="editSignatoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSignatoryModalLabel">EDIT SIGNATORY</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="searchEditSignatory" placeholder="Search by name...">
                    </div>
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Campus</th>
                                        <th>1</th>
                                        <th>Position 1</th>
                                        <th>2</th>
                                        <th>Position 2</th>
                                        <th>3</th>
                                        <th>Position 3</th>
                                        <th>4</th>
                                        <th>Position 4</th>
                                        <th>5</th>
                                        <th>Position 5</th>
                                        <th>6</th>
                                        <th>Position 6</th>
                                        <th>7</th>
                                        <th>Position 7</th>
                                    </tr>
                                </thead>
                                <tbody id="editSignatoryTableBody">
                                    <!-- Table content will be dynamically populated -->
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div id="editSignatoryCount" class="text-muted">
                                <!-- Signatory count will be dynamically added here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Campus Selection Modal -->
    <div class="modal fade" id="campusSelectionModal" tabindex="-1" aria-labelledby="campusSelectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="campusSelectionModalLabel">Select Campus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="campusDropdown" class="form-label">Campus</label>
                        <select class="form-select" id="campusDropdown">
                            <option value="" selected disabled>Choose a campus...</option>
                            <option value="Alangilan">Alangilan</option>
                            <option value="ARASOF-Nasugbu">ARASOF-Nasugbu</option>
                            <option value="Balayan">Balayan</option>
                            <option value="Lemery">Lemery</option>
                            <option value="Lipa">Lipa</option>
                            <option value="Lobo">Lobo</option>
                            <option value="Mabini">Mabini</option>
                            <option value="Malvar">Malvar</option>
                            <option value="Pablo Borbon">Pablo Borbon</option>
                            <option value="Rosario">Rosario</option>
                            <option value="San Juan">San Juan</option>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="button" class="btn btn-primary" id="selectCampusBtn">Select</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add these variables at the top of your script
        let currentPage = 1;
        const rowsPerPage = 6;
        let filteredData = [];
        let isEditMode = false;
        let currentSignatoryId = null;
        let viewModal, editModal, campusModal;

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


        // Initialize modals when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap modals
            viewModal = new bootstrap.Modal(document.getElementById('viewSignatoryModal'));
            editModal = new bootstrap.Modal(document.getElementById('editSignatoryModal'));
            campusModal = new bootstrap.Modal(document.getElementById('campusSelectionModal'));

            // Apply current theme to modals
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            document.querySelectorAll('.modal-content').forEach(modal => {
                modal.style.backgroundColor = currentTheme === 'light' ? '#ffffff' : 'rgba(33, 37, 41, 0.85)';
                modal.style.color = currentTheme === 'light' ? '#212529' : '#ffffff';
            });

            // Make placeholders more visible in light mode
            if (currentTheme === 'light') {
                document.querySelectorAll('.form-control').forEach(input => {
                    input.style.setProperty('--placeholder-color', '#555555', 'important');
                });

                // Add a style element for placeholder color
                const style = document.createElement('style');
                style.textContent = `
                    .form-control::placeholder {
                        color: #555555 !important;
                        opacity: 1 !important;
                        font-weight: 500 !important;
                    }
                `;
                document.head.appendChild(style);
            }

            // Add search event listeners
            document.getElementById('searchSignatory').addEventListener('input', function() {
                const campusFilter = document.getElementById('campusFilter');
                const campusValue = campusFilter.tagName.toLowerCase() === 'select' ?
                    campusFilter.value : campusFilter.value;
                filterSignatories(this.value, campusValue, 'view');
            });

            document.getElementById('searchEditSignatory').addEventListener('input', function() {
                filterSignatories(this.value, 'all', 'edit');
            });

            // Add select campus button event listener
            document.getElementById('selectCampusBtn').addEventListener('click', function() {
                const campusDropdown = document.getElementById('campusDropdown');
                const selectedCampus = campusDropdown.value;

                if (selectedCampus) {
                    filterAndPopulateByCampus(selectedCampus);
                    campusModal.hide();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Campus Selected',
                        text: 'Please select a campus from the dropdown',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });

            // Add filter button click handler
            document.getElementById('filterBtn').addEventListener('click', function() {
                // Load signatory data and show the modal
                loadSignatoryData();

                // Apply current theme to modal
                const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';

                if (currentTheme === 'light') {
                    // Light mode styling
                    document.querySelector('#campusSelectionModal .modal-content').style.backgroundColor = '#ffffff';
                    document.querySelector('#campusSelectionModal .modal-content').style.color = '#212529';
                    document.querySelector('#campusSelectionModal .modal-content').style.border = '1px solid #dee2e6';
                    document.querySelector('#campusSelectionModal .modal-content').style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
                    document.querySelector('#campusSelectionModal .modal-header').style.backgroundColor = '#f8f9fa';
                    document.querySelector('#campusSelectionModal .modal-header').style.borderBottom = '1px solid #dee2e6';
                    document.querySelector('#campusDropdown').style.backgroundColor = '#f8f9fa';
                    document.querySelector('#campusDropdown').style.border = '1px solid #ced4da';
                } else {
                    // Dark mode styling
                    document.querySelector('#campusSelectionModal .modal-content').style.backgroundColor = '#212529';
                    document.querySelector('#campusSelectionModal .modal-content').style.color = '#ffffff';
                    document.querySelector('#campusSelectionModal .modal-content').style.border = '1px solid #495057';
                    document.querySelector('#campusSelectionModal .modal-header').style.backgroundColor = '#343a40';
                    document.querySelector('#campusSelectionModal .modal-header').style.borderBottom = '1px solid #495057';
                    document.querySelector('#campusDropdown').style.backgroundColor = '#2b3035';
                    document.querySelector('#campusDropdown').style.border = '1px solid #495057';
                    document.querySelector('#campusDropdown').style.color = '#e9ecef';
                }

                campusModal.show();
            });

            // Load initial data
            loadSignatoriesAndCampuses();
        });

        // Load signatories data
        function loadSignatoriesAndCampuses() {
            // For Central user, keep form fields empty
            const isCentral = <?php echo $isCentral ? 'true' : 'false'; ?>;
            if (isCentral) {
                document.getElementById('name1').value = '';
                document.getElementById('name2').value = '';
                document.getElementById('name3').value = '';
                document.getElementById('name4').value = '';
                document.getElementById('name5').value = '';
                document.getElementById('name6').value = '';
                document.getElementById('name7').value = '';

                // Disable edit functionality for Central users
                document.getElementById('editBtn').disabled = true;
                document.getElementById('editBtn').classList.add('btn-disabled');
                document.getElementById('addBtn').disabled = true;
                document.getElementById('addBtn').classList.add('btn-disabled');

                // Load signatory data for filtering
                loadSignatoryData();
            } else {
                // For non-Central users:
                // Disable filter button - should only be available for Central users
                document.getElementById('filterBtn').disabled = true;
                document.getElementById('filterBtn').classList.add('btn-disabled');

                // Only load signatory data for non-Central users
                loadSignatories()
                    .then(data => {
                        console.log('Initial data loaded:', data);

                        // If there's data, populate the form
                        if (data && data.length > 0) {
                            populateFormWithDefaults(data[0]);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading initial data:', error);
                    });
            }
        }

        // Load signatory data
        function loadSignatoryData() {
            // Remove loading animation
            fetch('get_signatories.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Store data globally
                        window.fullSignatoryData = data.data;
                    }
                })
                .catch(error => {
                    console.error('Error loading signatory data:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load signatory data',
                        confirmButtonColor: '#d33'
                    });
                });
        }

        // Filter signatories by campus and populate form
        function filterAndPopulateByCampus(campus) {
            if (!window.fullSignatoryData) return;

            // Find signatory data for the selected campus
            const campusData = window.fullSignatoryData.find(item => item.campus === campus);

            if (campusData) {
                // Populate form with campus data
                document.getElementById('name1').value = campusData.name1 || '';
                document.getElementById('name2').value = campusData.name2 || '';
                document.getElementById('name3').value = campusData.name3 || '';
                document.getElementById('name4').value = campusData.name4 || '';
                document.getElementById('name5').value = campusData.name5 || '';
                document.getElementById('name6').value = campusData.name6 || '';
                document.getElementById('name7').value = campusData.name7 || '';
            } else {
                // No data found for campus, clear fields
                clearFormFields();
            }
        }

        // Clear all form fields
        function clearFormFields() {
            document.getElementById('name1').value = '';
            document.getElementById('name2').value = '';
            document.getElementById('name3').value = '';
            document.getElementById('name4').value = '';
            document.getElementById('name5').value = '';
            document.getElementById('name6').value = '';
            document.getElementById('name7').value = '';
        }

        // Function to filter signatories
        function filterSignatories(searchTerm, campus, modalType) {
            if (!window.fullSignatoryData) {
                console.error('No signatory data loaded');
                return;
            }

            const isCentral = <?php echo $isCentral ? 'true' : 'false'; ?>;
            const userCampus = '<?php echo htmlspecialchars($_SESSION['username']); ?>';

            console.log('Filtering with params:', {
                searchTerm,
                campus,
                modalType,
                isCentral,
                dataCount: window.fullSignatoryData.length
            });

            // Debug all data in storage
            window.fullSignatoryData.forEach(s => {
                console.log(`Data record: id=${s.id}, campus=${s.campus}`);
            });

            const filteredData = window.fullSignatoryData.filter(signatory => {
                // Search match in all text fields
                const matchesSearch = searchTerm === '' ||
                    (signatory.name1 && signatory.name1.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (signatory.gad_head_secretariat && signatory.gad_head_secretariat.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (signatory.name2 && signatory.name2.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (signatory.vice_chancellor_rde && signatory.vice_chancellor_rde.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (signatory.name3 && signatory.name3.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (signatory.chancellor && signatory.chancellor.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (signatory.name4 && signatory.name4.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (signatory.asst_director_gad && signatory.asst_director_gad.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (signatory.name5 && signatory.name5.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (signatory.head_extension_services && signatory.head_extension_services.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (signatory.name6 && signatory.name6.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (signatory.vice_chancellor_admin_finance && signatory.vice_chancellor_admin_finance.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (signatory.name7 && signatory.name7.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (signatory.dean && signatory.dean.toLowerCase().includes(searchTerm.toLowerCase()));

                // Campus matching logic
                let matchesCampus = false;

                if (isCentral) {
                    // For Central user
                    if (campus === 'all') {
                        matchesCampus = true;
                    } else {
                        // Debug each record
                        console.log(`Comparing: DB campus="${signatory.campus}" with selected="${campus}"`);

                        // Make the comparison case-insensitive and trim whitespace
                        const dbCampus = (signatory.campus || '').trim().toLowerCase();
                        const selectedCampus = campus.trim().toLowerCase();

                        matchesCampus = dbCampus === selectedCampus;
                        console.log(`  - After normalization: "${dbCampus}" vs "${selectedCampus}" = ${matchesCampus}`);
                    }
                } else {
                    // For campus-specific users
                    matchesCampus = signatory.campus === userCampus;
                }

                return matchesSearch && matchesCampus;
            });

            console.log('Filtered data count:', filteredData.length, filteredData);
            displaySignatories(filteredData, modalType);
        }

        // Function to display signatories in modal
        function displaySignatories(data, modalType = 'view') {
            const tableBodyId = modalType === 'view' ? 'signatoryTableBody' : 'editSignatoryTableBody';
            const countDivId = modalType === 'view' ? 'signatoryCount' : 'editSignatoryCount';

            const tableBody = document.getElementById(tableBodyId);
            const countDiv = document.getElementById(countDivId);

            if (!tableBody || !countDiv) {
                console.error('Required elements not found');
                return;
            }

            tableBody.innerHTML = '';

            if (!data || data.length === 0) {
                countDiv.textContent = 'No signatories found';
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <div class="no-data-message">No signatories found.</div>
                        </td>
                    </tr>`;
                return;
            }

            // Update signatory count text
            countDiv.textContent = `Total Signatories: ${data.length}`;

            // Create table rows
            data.forEach(signatory => {
                const row = document.createElement('tr');
                row.dataset.id = signatory.id;
                row.innerHTML = `
                    <td>${signatory.name1}</td>
                    <td>${signatory.gad_head_secretariat}</td>
                    <td>${signatory.name2}</td>
                    <td>${signatory.vice_chancellor_rde}</td>
                    <td>${signatory.name3}</td>
                    <td>${signatory.chancellor}</td>
                    <td>${signatory.name4}</td>
                    <td>${signatory.asst_director_gad}</td>
                    <td>${signatory.name5}</td>
                    <td>${signatory.head_extension_services}</td>
                    <td>${signatory.name6}</td>
                    <td>${signatory.vice_chancellor_admin_finance}</td>
                    <td>${signatory.name7}</td>
                    <td>${signatory.dean}</td>
                `;

                // Add click event for selecting a row
                row.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const selectedSignatory = data.find(s => s.id == id);

                    if (modalType === 'edit') {
                        // Fill form fields with selected signatory data
                        document.getElementById('name1').value = selectedSignatory.name1;
                        document.getElementById('rank1').value = selectedSignatory.gad_head_secretariat;
                        document.getElementById('name2').value = selectedSignatory.name2;
                        document.getElementById('rank2').value = selectedSignatory.vice_chancellor_rde;
                        document.getElementById('name3').value = selectedSignatory.name3;
                        document.getElementById('rank3').value = selectedSignatory.chancellor;
                        document.getElementById('name4').value = selectedSignatory.name4;
                        document.getElementById('rank4').value = selectedSignatory.asst_director_gad;
                        document.getElementById('name5').value = selectedSignatory.name5;
                        document.getElementById('rank5').value = selectedSignatory.head_extension_services;
                        document.getElementById('name6').value = selectedSignatory.name6;
                        document.getElementById('rank6').value = selectedSignatory.vice_chancellor_admin_finance;
                        document.getElementById('name7').value = selectedSignatory.name7;
                        document.getElementById('rank7').value = selectedSignatory.dean;

                        currentSignatoryId = id;

                        // Close modal and enter edit mode
                        editModal.hide();
                        isEditMode = true;
                        document.getElementById('editBtn').innerHTML = '<i class="fas fa-times"></i>';
                        document.getElementById('addBtn').innerHTML = '<i class="fas fa-save"></i>';
                        document.getElementById('addBtn').disabled = false;
                        document.getElementById('addBtn').classList.remove('btn-disabled');

                        // Make name fields editable
                        toggleNameFieldsReadonly(false);

                        // Update card title
                        updateCardTitle(true);
                    }
                });

                tableBody.appendChild(row);
            });
        }

        // Function to handle form submission
        document.getElementById('ppasForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const name1 = document.getElementById('name1').value;
            const rank1 = document.getElementById('rank1').value;
            const name2 = document.getElementById('name2').value;
            const rank2 = document.getElementById('rank2').value;
            const name3 = document.getElementById('name3').value;
            const rank3 = document.getElementById('rank3').value;
            const name4 = document.getElementById('name4').value;
            const rank4 = document.getElementById('rank4').value;
            const name5 = document.getElementById('name5').value;
            const rank5 = document.getElementById('rank5').value;
            const name6 = document.getElementById('name6').value;
            const rank6 = document.getElementById('rank6').value;
            const name7 = document.getElementById('name7').value;
            const rank7 = document.getElementById('rank7').value;
            // Use the session username as campus
            const campus = '<?php echo htmlspecialchars($_SESSION['username']); ?>';

            if (!name1 || !name2 || !name3 || !name4 || !name5 || !name6 || !name7) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please fill in all name fields',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            const data = isEditMode ? {
                id: currentSignatoryId,
                name1,
                gad_head_secretariat: rank1,
                name2,
                vice_chancellor_rde: rank2,
                name3,
                chancellor: rank3,
                name4,
                asst_director_gad: rank4,
                name5,
                head_extension_services: rank5,
                name6,
                vice_chancellor_admin_finance: rank6,
                name7,
                dean: rank7,
                campus
            } : {
                name1,
                gad_head_secretariat: rank1,
                name2,
                vice_chancellor_rde: rank2,
                name3,
                chancellor: rank3,
                name4,
                asst_director_gad: rank4,
                name5,
                head_extension_services: rank5,
                name6,
                vice_chancellor_admin_finance: rank6,
                name7,
                dean: rank7,
                campus
            };

            // If we're in edit mode but the ID is 0, use add endpoint
            const url = isEditMode && currentSignatoryId !== 0 ? 'update_signatory.php' : 'add_signatory.php';

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Reset form and reload data
                            document.getElementById('ppasForm').reset();
                            isEditMode = false;
                            currentSignatoryId = null;

                            // Reset button states
                            document.getElementById('editBtn').innerHTML = '<i class="fas fa-edit"></i>';
                            document.getElementById('editBtn').classList.remove('editing'); // Remove the editing class to restore yellow color
                            document.getElementById('addBtn').innerHTML = '<i class="fas fa-plus"></i>';
                            document.getElementById('addBtn').disabled = true;
                            document.getElementById('addBtn').classList.add('btn-disabled');

                            // Set all name fields to readonly
                            toggleNameFieldsReadonly(true);

                            // Update card title
                            updateCardTitle(false);

                            // Reload the data to refresh the form
                            loadInitialData();
                        });
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Operation failed',
                        confirmButtonColor: '#d33'
                    });
                });
        });

        // View button click handler
        // document.getElementById('viewBtn').addEventListener('click', function() {
        //    // This is now handled by the filter button
        // });

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

            // Update modal styling when theme changes
            if (newTheme === 'light') {
                document.querySelectorAll('.modal-content').forEach(modal => {
                    modal.style.backgroundColor = '#ffffff';
                    modal.style.color = '#212529';
                    modal.style.border = '1px solid #dee2e6';
                });

                // Update campus dropdown if it exists
                const campusDropdown = document.getElementById('campusDropdown');
                if (campusDropdown) {
                    campusDropdown.style.backgroundColor = '#f8f9fa';
                    campusDropdown.style.border = '1px solid #ced4da';
                    campusDropdown.style.color = '#212529';
                }
            } else {
                document.querySelectorAll('.modal-content').forEach(modal => {
                    modal.style.backgroundColor = '#212529';
                    modal.style.color = '#ffffff';
                    modal.style.border = '1px solid #495057';
                });

                // Update campus dropdown if it exists
                const campusDropdown = document.getElementById('campusDropdown');
                if (campusDropdown) {
                    campusDropdown.style.backgroundColor = '#2b3035';
                    campusDropdown.style.border = '1px solid #495057';
                    campusDropdown.style.color = '#e9ecef';
                }
            }
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
                iconColor: '#f8b26a',
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel',
                background: '#ffffff',
                color: '#212529',
                customClass: {
                    popup: 'border-0 shadow',
                    title: 'text-dark',
                    htmlContainer: 'text-dark',
                    confirmButton: 'btn-secondary',
                    cancelButton: 'btn-danger'
                },
                buttonsStyling: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.body.classList.add('fade-out');

                    setTimeout(() => {
                        window.location.href = '../loading_screen.php?redirect=index.php';
                    }, 10);
                }
            });
        }

        // Add additional styling for SweetAlert to ensure it's always light-themed
        document.addEventListener('DOMContentLoaded', function() {
            // Add a style element to force white theme for SweetAlert
            const style = document.createElement('style');
            style.innerHTML = `
        .swal2-popup {
            background-color: #ffffff !important;
            color: #212529 !important;
        }
        .swal2-title, .swal2-html-container {
            color: #212529 !important;
        }
        .swal2-icon.swal2-warning {
            color: #f8b26a !important;
            border-color: #f8b26a !important;
        }
    `;
            document.head.appendChild(style);
        });

        // Edit button click handler
        document.getElementById('editBtn').addEventListener('click', function() {
            // Check if user is Central - don't allow editing
            const isCentral = <?php echo $isCentral ? 'true' : 'false'; ?>;
            if (isCentral) {
                Swal.fire({
                    icon: 'info',
                    title: 'Information',
                    text: 'Central users cannot edit signatories. Please log in as a specific campus to edit.',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            if (!isEditMode) {
                // Enter edit mode
                fetch('get_signatories.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const signatories = data.data;
                            if (signatories.length === 1) {
                                // If there's only one record, edit it directly
                                const record = signatories[0];
                                currentSignatoryId = record.id;

                                // Fill form fields
                                document.getElementById('name1').value = record.name1 || '';
                                document.getElementById('rank1').value = record.gad_head_secretariat || '';
                                document.getElementById('name2').value = record.name2 || '';
                                document.getElementById('rank2').value = record.vice_chancellor_rde || '';
                                document.getElementById('name3').value = record.name3 || '';
                                document.getElementById('rank3').value = record.chancellor || '';
                                document.getElementById('name4').value = record.name4 || '';
                                document.getElementById('rank4').value = record.asst_director_gad || '';
                                document.getElementById('name5').value = record.name5 || '';
                                document.getElementById('rank5').value = record.head_extension_services || '';
                                document.getElementById('name6').value = record.name6 || '';
                                document.getElementById('rank6').value = record.vice_chancellor_admin_finance || '';
                                document.getElementById('name7').value = record.name7 || '';
                                document.getElementById('rank7').value = record.dean || '';

                                // Enter edit mode
                                isEditMode = true;
                                document.getElementById('editBtn').innerHTML = '<i class="fas fa-times"></i>';
                                document.getElementById('editBtn').classList.add('editing');
                                document.getElementById('addBtn').innerHTML = '<i class="fas fa-save"></i>';
                                document.getElementById('addBtn').disabled = false;
                                document.getElementById('addBtn').classList.remove('btn-disabled');

                                // Make name fields editable
                                toggleNameFieldsReadonly(false);

                                // Update card title
                                updateCardTitle(true);
                            } else {
                                // Multiple records, show selection modal
                                window.allSignatoryData = signatories;
                                setupEditModal();
                                editModal.show();
                            }
                        } else {
                            throw new Error(data.message || 'Failed to load signatories');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading signatories for edit:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'Failed to load signatories',
                            confirmButtonColor: '#d33'
                        });
                    });
            } else {
                // Cancel edit mode
                isEditMode = false;
                currentSignatoryId = null;
                this.innerHTML = '<i class="fas fa-edit"></i>';
                this.classList.remove('editing');
                document.getElementById('addBtn').innerHTML = '<i class="fas fa-plus"></i>';
                document.getElementById('addBtn').disabled = true;
                document.getElementById('addBtn').classList.add('btn-disabled');
                document.getElementById('ppasForm').reset();

                // Set all name fields to readonly
                toggleNameFieldsReadonly(true);

                // Update card title
                updateCardTitle(false);

                // Reload the form data
                loadInitialData();
            }
        });

        // Function to set up the edit modal
        function setupEditModal() {
            const searchBox = document.getElementById('searchEditSignatory');

            // Clear previous data
            const tableBody = document.getElementById('editSignatoryTableBody');
            if (tableBody) {
                tableBody.innerHTML = '';
            }

            // Reset search
            if (searchBox) {
                const newSearchBox = searchBox.cloneNode(true);
                searchBox.parentNode.replaceChild(newSearchBox, searchBox);
                newSearchBox.value = '';

                // Set search handler
                newSearchBox.addEventListener('input', function() {
                    const term = this.value.toLowerCase();
                    // Filter and display in edit modal
                    const filteredData = window.allSignatoryData.filter(record =>
                        (record.name1 || '').toLowerCase().includes(term) ||
                        (record.name2 || '').toLowerCase().includes(term) ||
                        (record.name3 || '').toLowerCase().includes(term) ||
                        (record.name4 || '').toLowerCase().includes(term) ||
                        (record.name5 || '').toLowerCase().includes(term) ||
                        (record.name6 || '').toLowerCase().includes(term) ||
                        (record.name7 || '').toLowerCase().includes(term)
                    );
                    displayEditSignatories(filteredData);
                });
            }

            // Display all records initially
            displayEditSignatories(window.allSignatoryData);
        }

        // Function to display signatories in the edit modal
        function displayEditSignatories(data) {
            const tableBody = document.getElementById('editSignatoryTableBody');
            const countDiv = document.getElementById('editSignatoryCount');

            if (!tableBody || !countDiv) return;

            tableBody.innerHTML = '';

            if (!data || data.length === 0) {
                countDiv.textContent = 'No signatories found';
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <div class="no-data-message">No signatories found.</div>
                        </td>
                    </tr>`;
                return;
            }

            // Update count
            countDiv.textContent = `Total Signatories: ${data.length}`;

            // Create rows
            data.forEach(record => {
                const row = document.createElement('tr');
                row.dataset.id = record.id;
                row.innerHTML = `
                    <td>${record.campus || ''}</td>
                    <td>${record.name1 || ''}</td>
                    <td>${record.gad_head_secretariat || ''}</td>
                    <td>${record.name2 || ''}</td>
                    <td>${record.vice_chancellor_rde || ''}</td>
                    <td>${record.name3 || ''}</td>
                    <td>${record.chancellor || ''}</td>
                    <td>${record.name4 || ''}</td>
                    <td>${record.asst_director_gad || ''}</td>
                    <td>${record.name5 || ''}</td>
                    <td>${record.head_extension_services || ''}</td>
                    <td>${record.name6 || ''}</td>
                    <td>${record.vice_chancellor_admin_finance || ''}</td>
                    <td>${record.name7 || ''}</td>
                    <td>${record.dean || ''}</td>
                `;

                row.addEventListener('click', function() {
                    handleSignatoryRowClick(this.dataset.id);
                });

                tableBody.appendChild(row);
            });
        }

        // Function to toggle readonly attribute on name fields
        function toggleNameFieldsReadonly(readonly) {
            document.getElementById('name1').readOnly = readonly;
            document.getElementById('name2').readOnly = readonly;
            document.getElementById('name3').readOnly = readonly;
            document.getElementById('name4').readOnly = readonly;
            document.getElementById('name5').readOnly = readonly;
            document.getElementById('name6').readOnly = readonly;
            document.getElementById('name7').readOnly = readonly;
        }

        // Function to update card title based on edit mode
        function updateCardTitle(isEditing) {
            const cardTitle = document.querySelector('.card-title');

            // Save the badge if it exists
            const badge = cardTitle.querySelector('.badge');

            if (isEditing) {
                cardTitle.innerHTML = 'Edit Signatory';
            } else {
                cardTitle.innerHTML = 'View Signatory';
            }

            // Re-add the badge if it exists
            if (badge) {
                cardTitle.appendChild(badge);
            }

            // Add the badge for Central users if needed
            <?php if ($isCentral): ?>
                if (!cardTitle.querySelector('.badge')) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-info ms-2';
                    badge.textContent = 'View Only';
                    cardTitle.appendChild(badge);
                }
            <?php endif; ?>
        }

        // Function to load initial signatory data for non-central users
        function loadInitialData() {
            loadSignatories()
                .then(data => {
                    if (data && data.length > 0) {
                        populateFormWithDefaults(data[0]);
                    }
                })
                .catch(error => {
                    console.error('Error loading initial data:', error);
                });
        }

        // Function to load signatories from the server
        function loadSignatories() {
            return fetch('get_signatories.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.fullSignatoryData = data.data;
                        return data.data;
                    } else {
                        throw new Error(data.message || 'Failed to load signatories');
                    }
                });
        }

        // Function to populate form with default values
        function populateFormWithDefaults(data) {
            if (!data) return;

            document.getElementById('name1').value = data.name1 || '';
            document.getElementById('name2').value = data.name2 || '';
            document.getElementById('name3').value = data.name3 || '';
            document.getElementById('name4').value = data.name4 || '';
            document.getElementById('name5').value = data.name5 || '';
            document.getElementById('name6').value = data.name6 || '';
            document.getElementById('name7').value = data.name7 || '';
        }

        // Initialize modals when document is ready
    </script>
</body>

</html>