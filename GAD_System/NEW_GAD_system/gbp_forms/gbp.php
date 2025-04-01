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
$userCampus = $_SESSION['username']; // Get the user's campus from the session

// Store campus in session for consistency (used by the modal)
$_SESSION['campus'] = $userCampus;

// Include database configuration
require_once '../config.php';

// Fetch years from target table for the logged-in campus
$years = [];
$yearQuery = "SELECT DISTINCT year FROM target WHERE campus = ? ORDER BY year DESC";
$stmt = $conn->prepare($yearQuery);
$stmt->bind_param("s", $userCampus);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $years[] = $row['year'];
}

$stmt->close();
// The closing of the connection is handled in config.php
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

        /* Remove hover-based display */
        /* .dropdown-submenu:hover > .dropdown-menu {
            display: block;
        } */

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

        /* Add form sectioning styles */
        .form-section {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .form-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-nav {
            display: flex;
            margin-bottom: 25px;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .form-nav::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--border-color);
            z-index: -1;
        }

        .form-nav-item {
            padding: 10px 5px;
            margin-right: 25px;
            color: var(--text-secondary);
            cursor: pointer;
            position: relative;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
            white-space: nowrap;
        }

        .form-nav-item::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--accent-color);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .form-nav-item.active {
            color: var(--accent-color);
        }

        .form-nav-item.active::after {
            transform: scaleX(1);
        }

        .form-nav-item .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background-color: var(--bg-secondary);
            color: var(--text-secondary);
            margin-right: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-nav-item.active .step-number {
            background-color: var(--accent-color);
            color: white;
        }

        [data-bs-theme="dark"] .form-control:disabled,
        [data-bs-theme="dark"] .form-control[readonly] {
            background-color: #37383A;
            border: 1px dashed var(--dark-border);
            color: var(--dark-text);
            opacity: 0.8;
        }

        /* Light mode non-interactible fields */
        .form-control:disabled,
        .form-control[readonly] {
            background-color: #e9ecef;
            border: 1px dashed #ced4da;
            color: #6c757d;
            opacity: 0.8;
        }
        
        [data-bs-theme="dark"] .form-control, 
        [data-bs-theme="dark"] .form-select {
            background-color: #2B3035;
            border-color: var(--dark-border);
            color: var(--dark-text);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            color: var(--accent-color);
        }

        .action-buttons-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .btn-form-nav {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 20px;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
            background: var(--accent-color);
            color: white;
        }

        .btn-form-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-form-nav.btn-prev {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-floating > label {
            padding-left: 12px;
        }

        /* Fix for adding activities */
        .program-container {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }

        .program-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .program-title {
            font-weight: 600;
            color: var(--accent-color);
        }

        .activities-container {
            padding-left: 20px;
            border-left: 2px dashed var(--accent-color);
            margin-bottom: 15px;
        }

        .activity-item {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
            position: relative;
        }

        .btn-add-item {
            display: inline-flex;
            align-items: center;
            background: rgba(106, 27, 154, 0.1);
            color: var(--accent-color);
            border: 1px dashed var(--accent-color);
            border-radius: 8px;
            padding: 8px 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .btn-add-item:hover {
            background: rgba(106, 27, 154, 0.2);
        }

        .btn-add-item i {
            margin-right: 8px;
        }

        .btn-delete-item {
            padding: 3px 8px;
            border-radius: 6px;
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-delete-item:hover {
            background: #dc3545;
            color: white;
        }

        .info-text {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 5px;
        }

        .participants-container {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .program-activities-row {
            margin-top: 30px;
        }

        /* Form validation styles */
        .form-nav-item.has-error {
            color: #dc3545;
        }

        .form-nav-item.has-error .step-number {
            background-color: #dc3545;
            color: white;
        }

        .section-title.has-error {
            color: #dc3545;
        }

        .section-title.is-complete {
            color: #198754;
        }

        .form-nav-item.is-complete {
            color: #198754;
        }

        .form-nav-item.is-complete .step-number {
            background-color: #198754;
            color: white;
        }

        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .input-with-currency.is-invalid {
            border-color: #dc3545;
            border: 1px solid #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
            border-radius: 8px;
        }

        .input-with-currency.is-invalid::before {
            border-color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
        }

        .input-with-currency.is-invalid + .invalid-feedback {
            display: block;
        }

        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }

        .is-invalid ~ .invalid-feedback {
            display: block;
        }
        
        /* Form field labels */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--input-bg);
            color: var(--text-primary);
            transition: border-color 0.2s ease;
            padding: 10px 12px;
        }
        
        /* Focus styling for form controls */
        .form-control:focus, 
        .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25);
        }
        
        textarea.form-control {
            height: auto;
            min-height: 100px;
        }
        
        /* Input with currency symbol */
        .input-with-currency {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            background-color: var(--card-bg);
        }
        
        .input-with-currency .form-control {
            padding-left: 40px;
            border-radius: 0;
            border: none;
            background-color: transparent;
        }
        
        .input-with-currency::before {
            content: "₱";
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: var(--text-primary);
            z-index: 10;
            border-right: 1px solid var(--border-color);
            background-color: var(--bg-secondary);
        }
        
        /* Focus styling for currency input */
        .input-with-currency:focus-within {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25);
        }
        
        .input-with-currency .form-control:focus {
            box-shadow: none;
            border-color: transparent;
        }
        
        /* Dark mode specific styling for currency input */
        [data-bs-theme="dark"] .input-with-currency {
            border-color: var(--dark-border);
            background-color: #2B3035;
        }
        
        [data-bs-theme="dark"] .input-with-currency::before {
            color: var(--dark-text);
            border-color: var(--dark-border);
            background-color: #37383A;
        }

        .swal2-container {
            backdrop-filter: blur(5px);
        }
        
        .swal2-backdrop-show {
            background-color: rgba(0, 0, 0, 0.7) !important;
        }
        
        /* Form field labels */
        .swal2-backdrop-show {
            background-color: rgba(0, 0, 0, 0.7) !important;
        }
        
        /* Autocomplete styles */
        .autocomplete-container {
            position: relative;
        }
        
        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 5px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 100;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: none;
        }
        
        .autocomplete-results.show {
            display: block;
        }
        
        .autocomplete-item {
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            border-bottom: 1px solid var(--border-color);
        }
        
        .autocomplete-item:last-child {
            border-bottom: none;
        }
        
        .autocomplete-item:hover {
            background-color: rgba(106, 27, 154, 0.1);
        }
        
        .autocomplete-item.used {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
        }
        
        .autocomplete-item.used:hover {
            background-color: rgba(220, 53, 69, 0.2);
        }
        
        .used-indicator {
            font-size: 0.85em;
            color: #dc3545;
            font-style: italic;
            margin-left: 5px;
        }
        
        /* Dark mode styles */
        [data-bs-theme="dark"] .autocomplete-results {
            background-color: #2d2d2d;
            border-color: #444;
        }
        
        [data-bs-theme="dark"] .autocomplete-item:hover {
            background-color: #3a3a3a;
        }
        
        /* Modal styles */
        .modal-backdrop.show {
            opacity: 0.85;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
        }
        
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            background: var(--card-bg);
            border: none;
        }
        
        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 20px 24px;
            display: flex;
            justify-content: center;
            position: relative;
        }
        
        .modal-header .btn-close {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        /* Fixed page navigation at bottom of modal */
        .pagination-container {
            margin-top: 15px;
            padding: 10px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: center;
            width: 100%;
            min-height: 60px;
            background-color: var(--bg-secondary);
            border-radius: 0 0 8px 8px;
        }
        
        nav[aria-label="Page navigation"] {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        
        .modal-body {
            padding: 24px;
            height: 600px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        
        /* Styles for disabled buttons */
        button.disabled, 
        button[disabled],
        .btn-icon.disabled,
        .btn-form-nav[disabled] {
            background: rgba(108, 117, 125, 0.1) !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            opacity: 0.65 !important;
            border-color: transparent !important;
        }
        
        .modal-title {
            font-weight: 700;
            color: var(--accent-color);
            text-align: center;
            width: 100%;
        }
        
        .modal-xl {
            max-width: 1400px !important;
        }
        
        /* Table container with fixed height and scrolling */
        .table-container {
            flex: 1;
            overflow-y: auto;
            min-height: 300px;
            max-height: 350px;
            margin-bottom: 10px;
            position: relative;
            border: 1px solid var(--border-color);
            border-radius: 8px;
        }
        
        /* Filter container */
        .filters-container {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        /* Table styling */
        .table {
            border-radius: 8px;
            overflow: hidden;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid var(--border-color);
        }
        
        .table th {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            font-weight: 600;
            border-bottom-width: 1px;
            padding: 12px 16px;
            border-color: var(--border-color);
        }
        
        .table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            border-color: var(--border-color);
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(106, 27, 154, 0.05);
        }
        
        /* Pagination styling */
        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
        
        .pagination .page-item .page-link {
            color: var(--accent-color);
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }
        
        .pagination .page-item.active .page-link {
            color: white;
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .pagination .page-item.disabled .page-link {
            color: var(--text-secondary);
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }
        
        .pagination .page-link:hover {
            background-color: var(--bg-secondary);
            border-color: var(--border-color);
        }
        
        /* Fix table border in dark mode */
        [data-bs-theme="dark"] .table {
            border-color: var(--border-color);
        }
        
        [data-bs-theme="dark"] .table th,
        [data-bs-theme="dark"] .table td {
            border-color: var(--border-color);
        }
        
        [data-bs-theme="dark"] .table-bordered {
            border-color: var(--border-color);
        }
        /* No results and loading messages */
        #noResultsMessage {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            background-color: rgba(var(--card-bg-rgb), 0.9);
            border-radius: 8px;
            padding: 20px;
            z-index: 5;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #noResultsMessage i {
            color: var(--accent-color);
            margin-bottom: 10px;
        }

        #noResultsMessage h5 {
            font-weight: 600;
            margin-bottom: 5px;
        }

        #noResultsMessage p {
            margin-bottom: 0;
        }

        /* Change visibility approach for no results */
        .d-none {
            display: none !important;
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
                <a href="../dashboard/dashboard.php"  class="nav-link">
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
                        <li><a class="dropdown-item" href="../gpb_reports/gbp_reports.php">GPB Reports</a></li>
                        <li><a class="dropdown-item" href="../ppas_reports/ppas_repors.php">PPAs Reports</a></li>
                        <li><a class="dropdown-item" href="../ps_atrib/psa.php">PSA Reports</a></li>
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
            <i class="fas fa-clipboard-list"></i>
            <h2>GBP Management</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Add GBP Form</h5>
            </div>
            <div class="card-body">
                <form id="ppasForm">
                    <!-- Form Navigation -->
                    <div class="form-nav">
                        <div class="form-nav-item active" data-section="basic-info">
                            <span class="step-number">1</span>
                            <span class="step-text">Basic Info</span>
                        </div>
                        <div class="form-nav-item" data-section="gender-issue">
                            <span class="step-number">2</span>
                            <span class="step-text">Gender Issue</span>
                        </div>
                        <div class="form-nav-item" data-section="activity">
                            <span class="step-number">3</span>
                            <span class="step-text">Activity</span>
                        </div>
                        <div class="form-nav-item" data-section="performance-budget">
                            <span class="step-number">4</span>
                            <span class="step-text">Performance & Budget</span>
                        </div>
                    </div>

                    <!-- Form Sections -->
                    <div class="form-sections-container">
                        <!-- Section 1: Basic Info -->
                        <div class="form-section active" id="basic-info">
                            <h6 class="section-title"><i class="fas fa-info-circle me-2"></i> Basic Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="year" class="form-label">Year</label>
                                        <select class="form-select" id="year" name="year" required>
                                            <option value="" selected disabled>Select Year</option>
                                            <?php foreach ($years as $year): ?>
                                                <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="campus" class="form-label">Campus</label>
                                        <input type="text" class="form-control" id="campus" name="campus" value="<?php echo htmlspecialchars($userCampus); ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="totalGAA" class="form-label">Total GAA</label>
                                        <input type="text" class="form-control" id="totalGAA" name="totalGAA" readonly>
                                        <small class="info-text">Fetched automatically from target table</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="totalGADFund" class="form-label">Total GAD Fund</label>
                                        <input type="text" class="form-control" id="totalGADFund" name="totalGADFund" readonly>
                                        <small class="info-text">Fetched automatically from target table</small>
                                    </div>
                                </div>
                            </div>
                            <div class="action-buttons-container">
                                <div></div> <!-- Empty div for spacing -->
                                <button type="button" class="btn-form-nav" data-navigate-to="gender-issue">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Section 2: Gender Issue -->
                        <div class="form-section" id="gender-issue">
                            <h6 class="section-title"><i class="fas fa-venus-mars me-2"></i> Gender Issue Information</h6>
                            <div class="form-group">
                                <label for="genderIssue" class="form-label">Gender Issue/GAD Mandate</label>
                                <div class="autocomplete-container">
                                    <input type="text" class="form-control" id="genderIssue" name="genderIssue" required autocomplete="off">
                                    <div class="autocomplete-results" id="genderIssueResults"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="genderCategory" class="form-label">Category of Gender Issue</label>
                                <select class="form-select" id="genderCategory" name="genderCategory" required>
                                    <option value="" selected disabled>Select Category</option>
                                    <option value="Client-Focused">Client-Focused</option>
                                    <option value="Organization-Focused">Organization-Focused</option>
                                    <option value="Attributable PAPs">Attributable PAPs</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="causeGenderIssue" class="form-label">Cause of Gender Issue</label>
                                <textarea class="form-control" id="causeGenderIssue" name="causeGenderIssue" style="height: 100px" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="gadResult" class="form-label">GAD Result/GAD Objective</label>
                                <textarea class="form-control" id="gadResult" name="gadResult" style="height: 100px" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="relevantAgency" class="form-label">Relevant Agency MFO/MPO</label>
                                <select class="form-select" id="relevantAgency" name="relevantAgency" required>
                                    <option value="" selected disabled>Select Relevant Agency</option>
                                    <option value="Higher Education Services">MFO1 - Higher Education Services</option>
                                    <option value="Advanced Education Services">MFO2 - Advanced Education Services</option>
                                    <option value="Research Services">MFO3 - Research Services</option>
                                    <option value="Technical Advisory Extension Services">MFO4 - Technical Advisory Extension Services</option>
                                </select>
                            </div>
                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="basic-info">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <button type="button" class="btn-form-nav" data-navigate-to="activity">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Section 3: Activity -->
                        <div class="form-section" id="activity">
                            <h6 class="section-title"><i class="fas fa-tasks me-2"></i> GAD Activity</h6>
                            <div id="programsContainer">
                                <!-- Initial Program -->
                                <div class="program-container" data-program-id="1">
                                    <div class="program-header">
                                        <span class="program-title">Program 1</span>
                                        <button type="button" class="btn-delete-item" data-delete-type="program"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="form-group">
                                        <label for="program_1" class="form-label">Program Name</label>
                                        <input type="text" class="form-control" id="program_1" name="program_1" required>
                                    </div>
                                    <div class="activities-container" data-program-id="1">
                                        <div class="activity-item" data-activity-id="1">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="activity-title">Activity 1</span>
                                                <button type="button" class="btn-delete-item" data-delete-type="activity"><i class="fas fa-times"></i></button>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label for="activity_1_1" class="form-label">Activity Name</label>
                                                <input type="text" class="form-control" id="activity_1_1" name="activity_1_1" required>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-add-item mt-3" data-add-type="activity" data-program-id="1">
                                            <i class="fas fa-plus"></i> Add Activity
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-item mt-3" data-add-type="program">
                                <i class="fas fa-plus"></i> Add Program
                            </button>
                            
                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="gender-issue">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <button type="button" class="btn-form-nav" data-navigate-to="performance-budget">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Section 4: Performance & Budget -->
                        <div class="form-section" id="performance-budget">
                            <h6 class="section-title"><i class="fas fa-chart-line me-2"></i> Performance Indicators & Budget</h6>
                            
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <h6 class="mb-3">Output Performance Indicators and Target</h6>
                                    <div class="form-group">
                                        <label for="activityCount" class="form-label">Number of Activities</label>
                                        <input type="text" class="form-control" id="activityCount" name="activityCount" readonly>
                                        <small class="info-text">Auto-calculated from number of activities added</small>
                                    </div>
                                    
                                    <h6 class="mb-3">Number of Participants</h6>
                                    <div class="participants-container">
                                        <div class="form-group mb-0">
                                            <label for="maleParticipants" class="form-label">Male Participants</label>
                                            <input type="number" class="form-control" id="maleParticipants" name="maleParticipants" min="0" required>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="femaleParticipants" class="form-label">Female Participants</label>
                                            <input type="number" class="form-control" id="femaleParticipants" name="femaleParticipants" min="0" required>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="totalParticipants" class="form-label">Total Participants</label>
                                            <input type="text" class="form-control" id="totalParticipants" name="totalParticipants" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="gadBudget" class="form-label">GAD Budget</label>
                                        <div class="input-with-currency">
                                            <input type="text" class="form-control" id="gadBudget" name="gadBudget" inputmode="decimal" pattern="[0-9]*[.]?[0-9]*" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="sourceBudget" class="form-label">Source of Budget/Fund</label>
                                        <select class="form-select" id="sourceBudget" name="sourceBudget" required>
                                            <option value="" selected disabled>Select Source</option>
                                            <option value="GAA">GAA</option>
                                            <option value="ODA">ODA</option>
                                            <option value="Corporate Budget">Corporate Budget</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="responsibleUnit" class="form-label">Responsible Unit/Office</label>
                                        <select class="form-select" id="responsibleUnit" name="responsibleUnit" required>
                                            <option value="" selected disabled>Select Unit</option>
                                            <option value="Extension Services - GAD Office of Student Affairs and Services">Extension Services</option>
                                            <option value="OVCAA">OVCAA</option>
                                            <option value="OVCPD">OVCPD</option>
                                            <option value="OSA">OSA</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="activity">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <div></div> <!-- Empty div for spacing -->
                            </div>
                        </div>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn-icon" id="viewBtn">
                                <i class="fas fa-eye"></i>
                            </button>
                            <div class="d-inline-flex gap-3">
                                <button type="submit" class="btn-icon" id="addBtn">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button type="button" class="btn-icon" id="editBtn">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn-icon" id="deleteBtn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <script>
// Add this near the start of the script, with other variable declarations
let originalEntryData = null; // Used to store the original entry data when in edit mode

 // Reset Form Sections
 function resetFormSections() {
            // Reset validation status
            const sections = ['basic-info', 'gender-issue', 'activity', 'performance-budget'];
            sections.forEach(sectionId => {
                const navItem = document.querySelector(`.form-nav-item[data-section="${sectionId}"]`);
                navItem.classList.remove('has-error', 'is-complete');
                
                const sectionTitle = document.querySelector(`#${sectionId} .section-title`);
                if (sectionTitle) {
                    sectionTitle.classList.remove('has-error');
                }
            });
            
            // Reset gender issue error - only call if function exists
            if (typeof clearGenderIssueError === 'function') {
            clearGenderIssueError();
            } else {
                // Fallback handling if function doesn't exist
                const genderIssueInput = document.getElementById('genderIssue');
                if (genderIssueInput) {
                    genderIssueInput.classList.remove('is-invalid');
                    
                    // Remove any error messages
                    const errorMessages = document.querySelectorAll('.gender-issue-error');
                    errorMessages.forEach(msg => msg.remove());
                }
            }
            
            isGenderIssueUsed = false;
            
            // Clear all programs and activities
            const programsContainer = document.getElementById('programsContainer');
            programsContainer.innerHTML = '';
            
            // Add a default program and activity - check if function exists first
            if (typeof addProgram === 'function') {
            addProgram();
            } else {
                // Fallback implementation if addProgram doesn't exist
                const programsContainer = document.getElementById('programsContainer');
                const newProgram = document.createElement('div');
                newProgram.className = 'program-container';
                newProgram.setAttribute('data-program-id', '1');
                
                newProgram.innerHTML = `
                    <div class="program-header">
                        <span class="program-title">Program 1</span>
                        <button type="button" class="btn-delete-item" data-delete-type="program"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="form-group">
                        <label for="program_1" class="form-label">Program Name</label>
                        <input type="text" class="form-control" id="program_1" name="program_1" required>
                    </div>
                    <div class="activities-container" data-program-id="1">
                        <div class="activity-item" data-activity-id="1">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="activity-title">Activity 1</span>
                                <button type="button" class="btn-delete-item" data-delete-type="activity"><i class="fas fa-times"></i></button>
                            </div>
                            <div class="form-group mb-0">
                                <label for="activity_1_1" class="form-label">Activity Name</label>
                                <input type="text" class="form-control" id="activity_1_1" name="activity_1_1" required>
                            </div>
                        </div>
                        <button type="button" class="btn-add-item mt-3" data-add-type="activity" data-program-id="1">
                            <i class="fas fa-plus"></i> Add Activity
                        </button>
                    </div>
                `;
                
                programsContainer.appendChild(newProgram);
                
                // Add event listeners to the new program's buttons
                const addActivityBtn = newProgram.querySelector('.btn-add-item[data-add-type="activity"]');
                if (addActivityBtn) {
                    addActivityBtn.addEventListener('click', function() {
                        const programId = this.getAttribute('data-program-id');
                        if (typeof addActivity === 'function') {
                            addActivity.call(this, programId);
                        } else {
                            // Fallback if addActivity isn't accessible
                            addActivityFallback(this, programId);
                        }
                    });
                }
                
                // Add event listeners to all delete buttons
                const deleteButtons = newProgram.querySelectorAll('.btn-delete-item');
                deleteButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const deleteType = this.getAttribute('data-delete-type');
                        
                        if (deleteType === 'program') {
                            const programContainer = this.closest('.program-container');
                            const programCount = document.querySelectorAll('.program-container').length;
                            
                            if (programCount <= 1) {
                                // Show warning when trying to delete the last program
                                Swal.fire({
                                    title: 'Cannot Delete',
                                    text: 'At least one program is required',
                                    icon: 'warning',
                                    confirmButtonColor: '#6a1b9a',
                                    backdrop: `rgba(0,0,0,0.7)`,
                                    allowOutsideClick: true
                                });
                                return;
                            }
                            
                            programContainer.remove();
                        } else if (deleteType === 'activity') {
                            const activityItem = this.closest('.activity-item');
                            const programContainer = this.closest('.program-container');
                            const activityCount = programContainer.querySelectorAll('.activity-item').length;
                            
                            if (activityCount <= 1) {
                                // Show warning when trying to delete the last activity
                                Swal.fire({
                                    title: 'Cannot Delete',
                                    text: 'At least one activity is required per program',
                                    icon: 'warning',
                                    confirmButtonColor: '#6a1b9a',
                                    backdrop: `rgba(0,0,0,0.7)`,
                                    allowOutsideClick: true
                                });
                                return;
                            }
                            
                            activityItem.remove();
                        }
            
            // Update activity count
                        if (typeof updateActivityCount === 'function') {
            updateActivityCount();
                        } else {
                            const activityCountField = document.getElementById('activityCount');
                            if (activityCountField) {
                                const activityItems = document.querySelectorAll('.activity-item');
                                activityCountField.value = activityItems.length || 0;
                            }
                        }
                    });
                });
                
                // Define fallback addActivity function
                function addActivityFallback(button, programId) {
                    const activitiesContainer = button.closest('.activities-container');
                    const activityItems = activitiesContainer.querySelectorAll('.activity-item');
                    const activityId = activityItems.length + 1;
                    
                    const newActivity = document.createElement('div');
                    newActivity.className = 'activity-item';
                    newActivity.setAttribute('data-activity-id', activityId);
                    
                    newActivity.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="activity-title">Activity ${activityId}</span>
                            <button type="button" class="btn-delete-item" data-delete-type="activity"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="form-group mb-0">
                            <label for="activity_${programId}_${activityId}" class="form-label">Activity Name</label>
                            <input type="text" class="form-control" id="activity_${programId}_${activityId}" name="activity_${programId}_${activityId}" required>
                        </div>
                    `;
                    
                    activitiesContainer.insertBefore(newActivity, button);
                    
                    // Add event listener to the delete button
                    const deleteBtn = newActivity.querySelector('.btn-delete-item');
                    deleteBtn.addEventListener('click', function() {
                        const activityItem = this.closest('.activity-item');
                        const programContainer = this.closest('.program-container');
                        const activityCount = programContainer.querySelectorAll('.activity-item').length;
                        
                        if (activityCount <= 1) {
                            // Show warning when trying to delete the last activity
                            Swal.fire({
                                title: 'Cannot Delete',
                                text: 'At least one activity is required per program',
                                icon: 'warning',
                                confirmButtonColor: '#6a1b9a',
                                backdrop: `rgba(0,0,0,0.7)`,
                                allowOutsideClick: true
                            });
                            return;
                        }
                        
                        activityItem.remove();
                        
                        // Update activity count
                        const activityCountField = document.getElementById('activityCount');
                        if (activityCountField) {
                            const allActivityItems = document.querySelectorAll('.activity-item');
                            activityCountField.value = allActivityItems.length || 0;
                        }
                    });
                }
            }
            
            // Update activity count - check if function exists first
            if (typeof updateActivityCount === 'function') {
                updateActivityCount();
            } else {
                // Simple fallback for activity count if function doesn't exist
                const activityCountField = document.getElementById('activityCount');
                if (activityCountField) {
                    const activityItems = document.querySelectorAll('.activity-item');
                    activityCountField.value = activityItems.length || 0;
                }
            }
        }

// exit Edit Mode
function cancelEdit() {
            // Reset originalEntryData
            originalEntryData = null;
            
            // Reset form
            document.getElementById('ppasForm').reset();
            
            // Change card title back
            document.querySelector('.card-header .card-title').textContent = 'Add GBP Form';
            
            // Reset add button
            const addBtn = document.getElementById('addBtn');
            addBtn.innerHTML = '<i class="fas fa-plus"></i>';
            addBtn.removeAttribute('data-action');
            addBtn.removeAttribute('data-entry-id');
            
            // Restore edit button
            const editBtn = document.getElementById('editBtn');
            editBtn.innerHTML = '<i class="fas fa-edit"></i>';
            editBtn.classList.remove('editing');
            editBtn.removeAttribute('data-action');
            
            // Restore original click handler for edit button
            if (editBtn.hasAttribute('data-original-handler')) {
                // Re-attach the event listener for opening the edit modal
                editBtn.onclick = function() {
                    showGbpEntriesModal('edit');
                    return false;
                };
                editBtn.removeAttribute('data-original-handler');
            }
            
            // Re-enable delete button
            const deleteBtn = document.getElementById('deleteBtn');
            deleteBtn.disabled = false;
            deleteBtn.classList.remove('disabled');
            
            // Reset form sections
            resetFormSections();
            
            // Navigate to first section - check if function exists first
            if (typeof navigateToSection === 'function') {
            navigateToSection('basic-info');
            } else {
                // Fallback implementation if navigateToSection doesn't exist
                // Update nav items
                const navItems = document.querySelectorAll('.form-nav-item');
                navItems.forEach(item => {
                    if (item.getAttribute('data-section') === 'basic-info') {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });
                
                // Update form sections
                const formSections = document.querySelectorAll('.form-section');
                formSections.forEach(section => {
                    if (section.id === 'basic-info') {
                        section.classList.add('active');
                    } else {
                        section.classList.remove('active');
                    }
                });
                
                // Scroll to the top of the page
                const mainContent = document.querySelector('.main-content');
                if (mainContent) {
                    mainContent.scrollTop = 0;
                }
            }
        }

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

// Modern multi-section form handling
document.addEventListener('DOMContentLoaded', function() {
    // Flag to track if validation has been triggered
    let validationTriggered = false;
    
    // Form navigation
    const navItems = document.querySelectorAll('.form-nav-item');
    const formSections = document.querySelectorAll('.form-section');
    const navButtons = document.querySelectorAll('[data-navigate-to]');
    
    // Navigation event handlers
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            const targetSection = this.getAttribute('data-section');
            navigateToSection(targetSection);
        });
    });
    
    navButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetSection = this.getAttribute('data-navigate-to');
            navigateToSection(targetSection);
        });
    });
    
    function navigateToSection(sectionId) {
        // Update nav items
        navItems.forEach(item => {
            if (item.getAttribute('data-section') === sectionId) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
        
        // Update form sections
        formSections.forEach(section => {
            if (section.id === sectionId) {
                section.classList.add('active');
            } else {
                section.classList.remove('active');
            }
        });
        
        // Scroll to the top of the page
        document.querySelector('.main-content').scrollTop = 0;
    }
    
    // Function to validate all fields in a section
    function validateSection(sectionId) {
        const section = document.getElementById(sectionId);
        let isValid = true;
        
        // Get all required inputs in the section
        const inputs = section.querySelectorAll('input:not([type="button"]):not([readonly]), select, textarea');
        
        inputs.forEach(input => {
            if (input.hasAttribute('required') || (input.id && isRequiredField(input.id))) {
                if (!input.value.trim()) {
                    // Only mark as invalid if validation has been triggered
                    if (validationTriggered) {
                        markAsInvalid(input);
                    }
                    isValid = false;
                } else {
                    markAsValid(input);
                }
            }
        });
        
        // Special validation for programs and activities
        if (sectionId === 'activity') {
            const programs = section.querySelectorAll('.program-container');
            programs.forEach(program => {
                const programNameInput = program.querySelector('input[id^="program_"]');
                if (!programNameInput.value.trim()) {
                    // Only mark as invalid if validation has been triggered
                    if (validationTriggered) {
                        markAsInvalid(programNameInput);
                    }
                    isValid = false;
                } else {
                    markAsValid(programNameInput);
                }
                
                const activities = program.querySelectorAll('.activity-item');
                activities.forEach(activity => {
                    const activityNameInput = activity.querySelector('input[id^="activity_"]');
                    if (!activityNameInput.value.trim()) {
                        // Only mark as invalid if validation has been triggered
                        if (validationTriggered) {
                            markAsInvalid(activityNameInput);
                        }
                        isValid = false;
                    } else {
                        markAsValid(activityNameInput);
                    }
                });
            });
        }
        
        // Update section header and nav item to indicate errors only if validation has been triggered
        if (validationTriggered) {
            updateSectionStatus(sectionId, isValid);
        } else {
            // Just check for completion without showing errors
            const navItem = document.querySelector(`.form-nav-item[data-section="${sectionId}"]`);
            const allFieldsFilled = checkAllFieldsFilled(sectionId);
            
            if (allFieldsFilled) {
                navItem.classList.add('is-complete');
            } else {
                navItem.classList.remove('is-complete');
            }
        }
        
        return isValid;
    }
    
    // Function to determine if a field is required based on its ID
    function isRequiredField(fieldId) {
        const requiredFields = [
            'year', 'campus', 
            'genderIssue', 'genderCategory', 'causeGenderIssue', 'gadResult', 'relevantAgency',
            'maleParticipants', 'femaleParticipants', 
            'gadBudget', 'sourceBudget', 'responsibleUnit'
        ];
        
        return requiredFields.includes(fieldId);
    }
    
    // Function to mark a field as invalid
    function markAsInvalid(input) {
        input.classList.add('is-invalid');
        
        // For currency input, mark the wrapper as invalid
        const currencyWrapper = input.closest('.input-with-currency');
        if (currencyWrapper) {
            currencyWrapper.classList.add('is-invalid');
            
            // Add feedback message after the currency wrapper if not already present
            if (!currencyWrapper.nextElementSibling || !currencyWrapper.nextElementSibling.classList.contains('invalid-feedback')) {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'This field is required';
                currencyWrapper.parentNode.insertBefore(feedback, currencyWrapper.nextSibling);
            }
        } else {
            // Normal input field handling
            // Add feedback message if not already present
            if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('invalid-feedback')) {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'This field is required';
                input.parentNode.insertBefore(feedback, input.nextSibling);
            }
        }
    }
    
    // Function to mark a field as valid
    function markAsValid(input) {
        input.classList.remove('is-invalid');
        
        // For currency input, remove invalid class from wrapper
        const currencyWrapper = input.closest('.input-with-currency');
        if (currencyWrapper) {
            currencyWrapper.classList.remove('is-invalid');
            
            // Remove feedback message if present
            if (currencyWrapper.nextElementSibling && currencyWrapper.nextElementSibling.classList.contains('invalid-feedback')) {
                currencyWrapper.nextElementSibling.remove();
            }
        } else {
            // Remove feedback message if present
            if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) {
                input.nextElementSibling.remove();
            }
        }
    }
    
    // Function to update section header and nav status
    function updateSectionStatus(sectionId, isValid) {
        const navItem = document.querySelector(`.form-nav-item[data-section="${sectionId}"]`);
        const sectionTitle = document.querySelector(`#${sectionId} .section-title`);
        
        if (!isValid) {
            navItem.classList.add('has-error');
            navItem.classList.remove('is-complete');
            if (sectionTitle) {
                sectionTitle.classList.add('has-error');
            }
        } else {
            navItem.classList.remove('has-error');
            if (sectionTitle) sectionTitle.classList.remove('has-error');
            
            // Check if all fields in this section have values (not just valid but actually filled)
            const allFieldsFilled = checkAllFieldsFilled(sectionId);
            if (allFieldsFilled) {
                navItem.classList.add('is-complete');
            } else {
                navItem.classList.remove('is-complete');
            }
        }
    }
    
    // Function to check if all fields in a section are filled
    function checkAllFieldsFilled(sectionId) {
        const section = document.getElementById(sectionId);
        
        // Get all required inputs in the section
        const inputs = section.querySelectorAll('input:not([type="button"]):not([readonly]), select, textarea');
        
        for (const input of inputs) {
            if (input.hasAttribute('required') || (input.id && isRequiredField(input.id))) {
                if (!input.value.trim()) {
                    return false;
                }
            }
        }
        
        // Special check for programs and activities
        if (sectionId === 'activity') {
            const programs = section.querySelectorAll('.program-container');
            for (const program of programs) {
                const programNameInput = program.querySelector('input[id^="program_"]');
                if (!programNameInput.value.trim()) {
                    return false;
                }
                
                const activities = program.querySelectorAll('.activity-item');
                for (const activity of activities) {
                    const activityNameInput = activity.querySelector('input[id^="activity_"]');
                    if (!activityNameInput.value.trim()) {
                        return false;
                    }
                }
            }
        }
        
        return true;
    }
    
    // Function to check all sections
    function checkAllSectionsStatus() {
        // Only perform validation if validation has been triggered
        if (!validationTriggered) return;
        
        const sections = ['basic-info', 'gender-issue', 'activity', 'performance-budget'];
        sections.forEach(sectionId => {
            const isValid = validateSection(sectionId);
            updateSectionStatus(sectionId, isValid);
        });
    }
    
    // Function to highlight invalid fields when returning to a section
    function highlightInvalidFields(section) {
        const inputs = section.querySelectorAll('.is-invalid');
        if (inputs.length > 0) {
            // Fields are already marked, no need to do anything
        }
    }
    
    // Validate all sections and update their status
    function validateAllSections() {
        const sections = ['basic-info', 'gender-issue', 'activity', 'performance-budget'];
        sections.forEach(validateSection);
    }
    
    // Add input event listeners to clear validation when user types
    document.querySelectorAll('input, select, textarea').forEach(input => {
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                markAsValid(this);
                
                // Check if all fields in the section are now valid
                const sectionId = this.closest('.form-section').id;
                if (validateSection(sectionId)) {
                    updateSectionStatus(sectionId, true);
                }
            }
            
            // Check completion status for all sections
            checkAllSectionsStatus();
        });
    });
    
    // Handle participants calculation
    const maleInput = document.getElementById('maleParticipants');
    const femaleInput = document.getElementById('femaleParticipants');
    const totalInput = document.getElementById('totalParticipants');
    
    function calculateTotal() {
        const male = parseInt(maleInput.value) || 0;
        const female = parseInt(femaleInput.value) || 0;
        totalInput.value = male + female;
    }
    
    maleInput.addEventListener('input', calculateTotal);
    femaleInput.addEventListener('input', calculateTotal);
    
    // Handle dynamic Programs and Activities
    let programCounter = 1;
    let activityCounters = { 1: 1 }; // Initialize for first program
    
    // Add Program button
    const addProgramBtn = document.querySelector('[data-add-type="program"]');
    addProgramBtn.addEventListener('click', addProgram);

    // Function to add a program
    function addProgram() {
        programCounter++;
        activityCounters[programCounter] = 0;
        
        const programsContainer = document.getElementById('programsContainer');
        const newProgram = document.createElement('div');
        newProgram.className = 'program-container';
        newProgram.setAttribute('data-program-id', programCounter);
        
        newProgram.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="program-title">Program ${programCounter}</span>
                <button type="button" class="btn-delete-item" data-delete-type="program"><i class="fas fa-times"></i></button>
            </div>
            <div class="form-group">
                <label for="program_${programCounter}" class="form-label">Program Name</label>
                <input type="text" class="form-control" id="program_${programCounter}" name="program_${programCounter}" required>
            </div>
            <div class="activities-container" data-program-id="${programCounter}"></div>
        `;
        
        programsContainer.appendChild(newProgram);
        
        // Add event listener to delete button
        const deleteBtn = newProgram.querySelector('.btn-delete-item');
        deleteBtn.addEventListener('click', handleDelete);
        
        // Add validation listener to the program name input
        const programInput = newProgram.querySelector('input');
        addInputValidationListener(programInput);
        
        // Add first activity and make sure its input listener works
        const firstActivity = addActivity(programCounter);
        
        // Re-run the listener attachment to be doubly sure
        if (firstActivity) {
            const input = firstActivity.querySelector('input[type="text"]');
            if (input) {
                // Remove any existing listeners
                const newInput = input.cloneNode(true);
                input.parentNode.replaceChild(newInput, input);
                
                // Add fresh listener with timeout
                newInput.addEventListener('input', function() {
                    setTimeout(updateActivityCount, 50);
                });
                
                // Add validation listener
                addInputValidationListener(newInput);
            }
        }
        
        // Check section completion status
        if (typeof checkAllSectionsStatus === 'function') {
            checkAllSectionsStatus();
        } else {
            checkCompletionStatus();
        }
    }
    
    // Additional enhancement to ensure all activity inputs have proper listeners
    function refreshAllActivityListeners() {
        document.querySelectorAll('.activity-item input[type="text"]').forEach(input => {
            // Remove existing listeners by cloning
            const newInput = input.cloneNode(true);
            input.parentNode.replaceChild(newInput, input);
            
            // Add fresh listener with timeout
            newInput.addEventListener('input', function() {
                setTimeout(updateActivityCount, 50);
            });
            
            // Add validation listener
            addInputValidationListener(newInput);
        });
        
        // Update count immediately
        updateActivityCount();
    }
    
    // Call this at document ready
    document.addEventListener('DOMContentLoaded', function() {
        refreshAllActivityListeners();
        
        // Add a global event handler for when "Add Program" is clicked
        const addProgramBtn = document.getElementById('addProgramBtn');
        if (addProgramBtn) {
            addProgramBtn.addEventListener('click', function() {
                // Set a timeout to refresh listeners after DOM is updated
                setTimeout(refreshAllActivityListeners, 100);
            });
        }
    });
    
    // Function to attach event listeners to dynamically created elements
    function attachEventListeners(container) {
        // Add activity button
        const addActivityBtns = container.querySelectorAll('[data-add-type="activity"]');
        addActivityBtns.forEach(btn => {
            btn.addEventListener('click', addActivity);
        });
        
        // Delete buttons
        const deleteButtons = container.querySelectorAll('.btn-delete-item');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', handleDelete);
        });
    }
    
    // Helper function to add validation listeners to inputs
    function addInputValidationListener(input) {
        input.addEventListener('input', function() {
            // Only handle validation errors if they've been triggered
            if (validationTriggered && this.classList.contains('is-invalid')) {
                markAsValid(this);
                
                // Check if all fields in the section are now valid
                const sectionId = this.closest('.form-section').id;
                if (validateSection(sectionId)) {
                    updateSectionStatus(sectionId, true);
                }
            }
            
            // Always check completion status (green indicators)
            // without showing validation errors
            checkCompletionStatus();
        });
    }
    
    // Initial event listeners for the first program
    attachEventListeners(document.getElementById('programsContainer'));
    
    // Update activity count function
    function updateActivityCount() {
        const activityItems = document.querySelectorAll('.activity-item');
        let filledActivitiesCount = 0;
        
        activityItems.forEach(item => {
            const activityInput = item.querySelector('input[type="text"]');
            if (activityInput && activityInput.value.trim() !== '') {
                filledActivitiesCount++;
            }
        });
        
        const activityCountField = document.getElementById('activityCount');
        activityCountField.value = filledActivitiesCount;
        
        // Check completion status
        checkCompletionStatus();
    }
    
    // Initialize activity count
    updateActivityCount();
    
    // Add activity input listeners for counting activities with values
    function addActivityInputListener(activityItem) {
        const input = activityItem.querySelector('input[type="text"]');
        if (input) {
            input.addEventListener('input', function() {
                // Use setTimeout to ensure value is updated after typing
                setTimeout(updateActivityCount, 50);
            });
        }
    }
    
    // Initialize input listeners for all existing activity items
    document.querySelectorAll('.activity-item').forEach(item => {
        addActivityInputListener(item);
    });
    
    // Modify addActivity to add input listener and accept a programId parameter
    function addActivity(programIdOrEvent) {
        // Determine if called from an event or directly
        let programId;
        if (typeof programIdOrEvent === 'string' || typeof programIdOrEvent === 'number') {
            // Called directly with programId
            programId = programIdOrEvent;
        } else {
            // Called from event
            programId = this.getAttribute('data-program-id');
        }
        
        activityCounters[programId]++;
        
        const activitiesContainer = document.querySelector(`.activities-container[data-program-id="${programId}"]`);
        const newActivity = document.createElement('div');
        newActivity.className = 'activity-item';
        newActivity.setAttribute('data-activity-id', activityCounters[programId]);
        
        newActivity.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="activity-title">Activity ${activityCounters[programId]}</span>
                <button type="button" class="btn-delete-item" data-delete-type="activity"><i class="fas fa-times"></i></button>
            </div>
            <div class="form-group mb-0">
                <label for="activity_${programId}_${activityCounters[programId]}" class="form-label">Activity Name</label>
                <input type="text" class="form-control" id="activity_${programId}_${activityCounters[programId]}" name="activity_${programId}_${activityCounters[programId]}" required>
            </div>
        `;
        
        // If called from event, insert before the button
        if (typeof programIdOrEvent !== 'string' && typeof programIdOrEvent !== 'number') {
            activitiesContainer.insertBefore(newActivity, this);
        } else {
            // If called directly, append to the container
            const addBtn = activitiesContainer.querySelector('.btn-add-item');
            if (addBtn) {
                activitiesContainer.insertBefore(newActivity, addBtn);
            } else {
                activitiesContainer.appendChild(newActivity);
                
                // Add the "Add Activity" button if it doesn't exist
                const addActivityBtn = document.createElement('button');
                addActivityBtn.type = 'button';
                addActivityBtn.className = 'btn-add-item mt-3';
                addActivityBtn.setAttribute('data-add-type', 'activity');
                addActivityBtn.setAttribute('data-program-id', programId);
                addActivityBtn.innerHTML = '<i class="fas fa-plus"></i> Add Activity';
                addActivityBtn.addEventListener('click', addActivity);
                activitiesContainer.appendChild(addActivityBtn);
            }
        }
        
        // Add event listener to delete button
        const deleteBtn = newActivity.querySelector('.btn-delete-item');
        deleteBtn.addEventListener('click', handleDelete);
        
        // Add input listener to the new activity
        addActivityInputListener(newActivity);
        
        // Add validation listener to the input field
        const input = newActivity.querySelector('input');
        addInputValidationListener(input);
        
        // Update activity count after adding
        updateActivityCount();
        
        // Check section completion status
        if (typeof checkAllSectionsStatus === 'function') {
            checkAllSectionsStatus();
        } else {
            checkCompletionStatus();
        }
        
        return newActivity;
    }

    // Delete function
    function handleDelete(e) {
        const deleteType = this.getAttribute('data-delete-type');
        
        if (deleteType === 'program') {
            const programContainer = this.closest('.program-container');
            const programId = programContainer.getAttribute('data-program-id');
            
            // Don't allow deleting the last program
            const programCount = document.querySelectorAll('.program-container').length;
            if (programCount <= 1) {
                Swal.fire({
                    title: 'Cannot Delete',
                    text: 'At least one program is required',
                    icon: 'warning',
                    confirmButtonColor: '#6a1b9a',
                    backdrop: `rgba(0,0,0,0.7)`,
                    allowOutsideClick: true
                });
                return;
            }
            
            programContainer.remove();
            delete activityCounters[programId];
        } else if (deleteType === 'activity') {
            const activityItem = this.closest('.activity-item');
            const programContainer = this.closest('.program-container');
            const programId = programContainer.getAttribute('data-program-id');
            
            // Don't allow deleting the last activity
            const activityCount = programContainer.querySelectorAll('.activity-item').length;
            if (activityCount <= 1) {
                Swal.fire({
                    title: 'Cannot Delete',
                    text: 'At least one activity is required per program',
                    icon: 'warning',
                    confirmButtonColor: '#6a1b9a',
                    backdrop: `rgba(0,0,0,0.7)`,
                    allowOutsideClick: true
                });
                return;
            }
            
            activityItem.remove();
            // We don't decrement the counter to avoid ID conflicts
        }
        
        updateActivityCount();
        
        // Check completion status 
        checkCompletionStatus();
    }
    
    // Fetch GAA and GAD Fund values when year is selected
    const yearSelect = document.getElementById('year');
    const campusSelect = document.getElementById('campus');
    const totalGAAInput = document.getElementById('totalGAA');
    const totalGADFundInput = document.getElementById('totalGADFund');
    
    function updateTargetData() {
        const year = yearSelect.value;
        const campus = campusSelect.value;
        
        fetchTargetData(year, campus);
    }
    
    function fetchTargetData(year, campus = null) {
        // If campus is not provided, get it from the campus input
        if (!campus) {
            campus = campusSelect.value;
        }
        
        if (year && campus) {
            // Fetch data from the target table
            fetch(`get_target_data.php?year=${year}&campus=${campus}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Format the numbers with PHP currency format
                        totalGAAInput.value = 'PHP ' + parseFloat(data.total_gaa).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        totalGADFundInput.value = 'PHP ' + parseFloat(data.total_gad_fund).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    } else {
                        totalGAAInput.value = '';
                        totalGADFundInput.value = '';
                        console.error('Error fetching target data:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    totalGAAInput.value = '';
                    totalGADFundInput.value = '';
                });
        }
    }
    
    yearSelect.addEventListener('change', updateTargetData);
    
    // Handle GAD Budget validation - only positive numbers
    const gadBudgetInput = document.getElementById('gadBudget');
    
    gadBudgetInput.addEventListener('keypress', function(e) {
        // Block any character that isn't a number or decimal point
        const charCode = (e.which) ? e.which : e.keyCode;
        if (charCode !== 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
            e.preventDefault();
            return false;
        }
        
        // Only allow one decimal point
        if (charCode === 46 && this.value.indexOf('.') !== -1) {
            e.preventDefault();
            return false;
        }
    });
    
    gadBudgetInput.addEventListener('paste', function(e) {
        // Get pasted data
        let pastedData = (e.clipboardData || window.clipboardData).getData('text');
        
        // Check if pasted data only contains numbers and at most one decimal point
        if (!/^[0-9]*\.?[0-9]*$/.test(pastedData)) {
            e.preventDefault();
            return false;
        }
    });
    
    gadBudgetInput.addEventListener('input', function() {
        // Remove any negative signs
        if (this.value.includes('-')) {
            this.value = this.value.replace(/-/g, '');
        }
        
        // Format with commas for thousands
        if (this.value !== '') {
            // First remove any existing commas
            let value = this.value.replace(/,/g, '');
            
            // Split the number at the decimal point
            let parts = value.split('.');
            
            // Format the whole number part with commas
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            
            // Join back with the decimal part if it exists
            this.value = parts.join('.');
        }
    });
    
    // Initialize validation on page load
    window.addEventListener('load', function() {
        // Add input listeners for validation to all fields
        document.querySelectorAll('input:not([readonly]), select, textarea').forEach(input => {
            addInputValidationListener(input);
        });

        // Check completion status on page load
        checkCompletionStatus();
    });
    
    // Handle Add Button click (form submission)
    const addBtn = document.getElementById('addBtn');
    addBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Set validation as triggered so errors will show
        validationTriggered = true;
        
        // Validate all sections before submission
        const sections = ['basic-info', 'gender-issue', 'activity', 'performance-budget'];
        let isFormValid = true;
        
        // Special check for gender issue duplication before general validation
        if (isGenderIssueUsed) {
            // If we're in edit mode, only show error if the gender issue is different from the original one
            const isEditMode = this.getAttribute('data-action') === 'update';
            const originalGenderIssue = isEditMode ? originalEntryData?.gender_issue : null;
            const currentGenderIssue = document.getElementById('genderIssue').value;
            
            if (!isEditMode || (isEditMode && originalGenderIssue !== currentGenderIssue)) {
                showGenderIssueError();
                const navItem = document.querySelector(`.form-nav-item[data-section="gender-issue"]`);
                const sectionTitle = document.querySelector(`#gender-issue .section-title`);
                
                navItem.classList.add('has-error');
                navItem.classList.remove('is-complete');
                if (sectionTitle) {
                    sectionTitle.classList.add('has-error');
                }
                
                isFormValid = false;
            }
        }
        
        // Continue with normal validation
        sections.forEach(sectionId => {
            const isValid = validateSection(sectionId);
            if (!isValid) {
                isFormValid = false;
            }
        });
        
        if (!isFormValid) {
            // Navigate to the first section with errors
            for (const sectionId of sections) {
                const navItem = document.querySelector(`.form-nav-item[data-section="${sectionId}"]`);
                if (navItem.classList.contains('has-error')) {
                    navigateToSection(sectionId);
                    break;
                }
            }
            
            return;
        }
        
        // If form is valid, prepare data for submission
        const formData = new FormData();
        
        // Check if we're updating or adding
        const isUpdate = this.getAttribute('data-action') === 'update';
        
        // If updating, add the entry ID
        if (isUpdate) {
            formData.append('entry_id', this.getAttribute('data-entry-id'));
        }
        
        // Basic info
        formData.append('year', document.getElementById('year').value);
        formData.append('campus', document.getElementById('campus').value);
        
        // Gender Issue
        formData.append('gender_issue', document.getElementById('genderIssue').value);
        formData.append('cause_of_issue', document.getElementById('causeGenderIssue').value);
        formData.append('gad_objective', document.getElementById('gadResult').value);
        formData.append('relevant_agency', document.getElementById('relevantAgency').value);
        
        // Activities
        const programContainers = document.querySelectorAll('.program-container');
        let programsData = [];
        
        programContainers.forEach(program => {
            const programId = program.getAttribute('data-program-id');
            const programName = document.getElementById(`program_${programId}`).value;
            
            // Add program to FormData for update endpoint
            if (isUpdate) {
                formData.append(`program_${programId}`, programName);
            }
            
            const activities = program.querySelectorAll('.activity-item');
            let activitiesData = [];
            
            activities.forEach(activity => {
                const activityId = activity.getAttribute('data-activity-id');
                const activityName = document.getElementById(`activity_${programId}_${activityId}`).value;
                activitiesData.push(activityName);
                
                // Add activity to FormData for update endpoint
                if (isUpdate) {
                    formData.append(`activity_${programId}_${activityId}`, activityName);
                }
            });
            
            programsData.push({
                programName: programName,
                activities: activitiesData
            });
        });
        
        // Create a simple array of just program names for generic_activity
        let programNames = programsData.map(program => program.programName);
        
        formData.append('category', document.getElementById('genderCategory').value); // Use gender category value
        formData.append('generic_activity', JSON.stringify(programNames)); // Store just program names
        
        // Extract and append specific activities for the required field
        let specificActivities = [];
        programsData.forEach(program => {
            specificActivities.push(program.activities);
        });
        formData.append('specific_activities', JSON.stringify(specificActivities));
        
        // Count total activities across all programs
        let totalActivities = 0;
        programsData.forEach(program => {
            totalActivities += program.activities.length;
        });
        formData.append('total_activities', totalActivities.toString());
        
        // Performance and Budget
        formData.append('male_participants', document.getElementById('maleParticipants').value);
        formData.append('female_participants', document.getElementById('femaleParticipants').value);
        formData.append('total_participants', document.getElementById('totalParticipants').value);
        
        // Remove commas from budget before saving
        const budget = document.getElementById('gadBudget').value.replace(/,/g, '');
        formData.append('gad_budget', budget);
        
        formData.append('source_of_budget', document.getElementById('sourceBudget').value);
        formData.append('responsible_unit', document.getElementById('responsibleUnit').value);
        
        // Determine endpoint based on action
        const endpoint = isUpdate ? 'update_gbp.php' : 'save_gbp.php';
        
        // Send to server
        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: isUpdate ? 'GBP entry updated successfully' : 'GBP entry saved successfully',
                    icon: 'success',
                    confirmButtonColor: '#6a1b9a',
                    backdrop: `rgba(0,0,0,0.7)`,
                    allowOutsideClick: true,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    if (isUpdate) {
                        // Reset to add mode after successful update
                        cancelEdit();
                        
                        // Force UI refresh to ensure we're fully out of edit mode
                        currentAction = 'add';
                        
                        // Add null checks for DOM elements before trying to access them
                        const formTitleEl = document.getElementById('formTitle');
                        if (formTitleEl) {
                            formTitleEl.textContent = 'Add New GBP Entry';
                        }
                        
                        const submitBtnEl = document.getElementById('submitBtn');
                        if (submitBtnEl) {
                            submitBtnEl.textContent = 'Save Entry';
                        }
                        
                        // Clear entry ID to ensure we're in add mode
                        const entryIdEl = document.getElementById('entry_id');
                        if (entryIdEl) {
                            entryIdEl.value = '';
                        }
                        
                        // Reload entries to show the updated data
                        if (typeof loadGbpEntries === 'function') {
                            loadGbpEntries();
                        } else {
                            // Fallback if function not available
                            window.location.reload();
                        }
                    } else {
                        // Reset form for add mode
                        window.location.reload();
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to ' + (isUpdate ? 'update' : 'save') + ' GBP entry',
                    icon: 'error',
                    confirmButtonColor: '#6a1b9a',
                    backdrop: `rgba(0,0,0,0.7)`,
                    allowOutsideClick: false
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An unexpected error occurred',
                icon: 'error',
                confirmButtonColor: '#6a1b9a',
                backdrop: `rgba(0,0,0,0.7)`,
                allowOutsideClick: false
            });
        });
    });
    
    // Handle Edit Button click
    const editBtn = document.getElementById('editBtn');
    editBtn.addEventListener('click', function() {
        showGbpEntriesModal('edit');
    });
    
    // Handle Delete Button click
    const deleteBtn = document.getElementById('deleteBtn');
    deleteBtn.addEventListener('click', function() {
        showGbpEntriesModal('delete');
    });
    
    // Handle View Button click
    const viewBtn = document.getElementById('viewBtn');
    viewBtn.addEventListener('click', function() {
        showGbpEntriesModal('view');
    });

    // Add input event listeners to clear validation and check completion status
    document.querySelectorAll('input, select, textarea').forEach(input => {
        input.addEventListener('input', function() {
            // Clear validation errors if any
            if (validationTriggered && this.classList.contains('is-invalid')) {
                markAsValid(this);
                
                // Check if all fields in the section are now valid
                const sectionId = this.closest('.form-section').id;
                if (validateSection(sectionId)) {
                    updateSectionStatus(sectionId, true);
                }
            }
            
            // Always check completion status (green indicators)
            // without showing validation errors
            checkCompletionStatus();
        });
    });
    
    // Function that only checks if sections are complete without showing validation errors
    function checkCompletionStatus() {
        const sections = ['basic-info', 'gender-issue', 'activity', 'performance-budget'];
        sections.forEach(sectionId => {
            const navItem = document.querySelector(`.form-nav-item[data-section="${sectionId}"]`);
            const allFieldsFilled = checkAllFieldsFilled(sectionId);
            
            if (allFieldsFilled) {
                navItem.classList.add('is-complete');
            } else {
                navItem.classList.remove('is-complete');
            }
        });
    }
    
    // Gender Issue Autocomplete and Duplication Validation
    const genderIssueInput = document.getElementById('genderIssue');
    const genderIssueResults = document.getElementById('genderIssueResults');
    let debounceTimer;
    let usedGenderIssues = []; // Store gender issues already used by this campus
    
    // Track if the current gender issue is already used
    let isGenderIssueUsed = false;
    
    // Make the autocomplete container relative for proper positioning
    genderIssueInput.closest('.autocomplete-container').style.position = 'relative';
    
    // Function to search and display gender issues
    function searchGenderIssues(query) {
        if (query.length < 2) {
            genderIssueResults.innerHTML = '';
            genderIssueResults.classList.remove('show');
            return;
        }
        
        // Show a loading indicator
        genderIssueResults.innerHTML = '<div class="autocomplete-item">Searching...</div>';
        genderIssueResults.classList.add('show');
        
        // Check if we're in edit mode
        const addBtn = document.getElementById('addBtn');
        const isEditMode = addBtn.getAttribute('data-action') === 'update';
        const currentId = isEditMode ? addBtn.getAttribute('data-entry-id') : null;
        const originalIssue = isEditMode && originalEntryData ? originalEntryData.gender_issue : null;
        
        // Build the URL with the current ID if in edit mode
        let searchUrl = `search_gender_issues.php?term=${encodeURIComponent(query)}`;
        if (currentId) {
            searchUrl += `&current_id=${encodeURIComponent(currentId)}`;
        }
        
        // Search for similar gender issues
        fetch(searchUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log("Search results:", data); // Debug log
                
                if (data.success && data.issues && data.issues.length > 0) {
                    // Update the list of used gender issues
                    usedGenderIssues = data.used_issues || [];
                    
                    // Show results
                    genderIssueResults.innerHTML = '';
                    
                    // Use a Set to track unique values we've already added
                    const addedValues = new Set();
                    
                    data.issues.forEach(issue => {
                        // Skip if we've already added this value (shouldn't happen with our updated backend)
                        if (addedValues.has(issue.value)) {
                            return;
                        }
                        
                        addedValues.add(issue.value);
                        
                        const item = document.createElement('div');
                        item.className = 'autocomplete-item';
                        item.textContent = issue.value;
                        
                        // Check if this is the original issue we're editing
                        const isOriginalIssue = isEditMode && originalIssue === issue.value;
                        
                        // If this issue is already used by this campus but NOT the original issue, mark it
                        let isUsed = usedGenderIssues.includes(issue.value) && !isOriginalIssue;
                        
                        if (isUsed) {
                            item.classList.add('used');
                            item.innerHTML = `${issue.value} <span class="used-indicator">(already used)</span>`;
                        }
                        
                        item.addEventListener('click', function() {
                            genderIssueInput.value = issue.value;
                            genderIssueResults.innerHTML = '';
                            genderIssueResults.classList.remove('show');
                            
                            // Check if we're in edit mode and this is the original issue
                            const addBtn = document.getElementById('addBtn');
                            const isEditMode = addBtn.getAttribute('data-action') === 'update';
                            const originalIssue = isEditMode && originalEntryData ? originalEntryData.gender_issue : null;
                            const isSameAsOriginal = isEditMode && issue.value === originalIssue;
                            
                            // Only mark as used if it's NOT the original issue we're editing
                            if (isUsed && !isSameAsOriginal) {
                                showGenderIssueError();
                                isGenderIssueUsed = true;
                                
                                // Always mark the section as invalid immediately when duplicate is selected
                                const navItem = document.querySelector(`.form-nav-item[data-section="gender-issue"]`);
                                const sectionTitle = document.querySelector(`#gender-issue .section-title`);
                                
                                navItem.classList.add('has-error');
                                navItem.classList.remove('is-complete');
                                if (sectionTitle) {
                                    sectionTitle.classList.add('has-error');
                                }
                            } else {
                                // Clear any existing error if this is valid
                                isGenderIssueUsed = false;
                                clearGenderIssueError();
                            }
                            
                            // Trigger validation check
                            checkCompletionStatus();
                        });
                        
                        genderIssueResults.appendChild(item);
                    });
                } else {
                    genderIssueResults.innerHTML = '<div class="autocomplete-item">No matching gender issues found</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching gender issues:', error);
                genderIssueResults.innerHTML = '<div class="autocomplete-item">Error searching. Please try again.</div>';
            });
    }
    
    // Function to show error for duplicate gender issue
    function showGenderIssueError() {
        // Mark the field as invalid
        genderIssueInput.classList.add('is-invalid');
        
        // Get the autocomplete container
        const container = genderIssueInput.closest('.autocomplete-container');
        
        // Remove any existing error message first
        const existingError = container.nextElementSibling;
        if (existingError && existingError.classList.contains('invalid-feedback')) {
            existingError.remove();
        }
        
        // Create and append a new error message
        const errorMessage = document.createElement('div');
        errorMessage.className = 'invalid-feedback gender-issue-error';
        errorMessage.textContent = 'This gender issue has already been used by the campus';
        errorMessage.style.display = 'block';
        container.parentNode.insertBefore(errorMessage, container.nextSibling);
    }
    
    // Function to clear gender issue error
    function clearGenderIssueError() {
        // Clear the input validation
        genderIssueInput.classList.remove('is-invalid');
        
        // Get the autocomplete container
        const container = genderIssueInput.closest('.autocomplete-container');
        
        // Remove the error message if it exists
        const errorMessages = document.querySelectorAll('.gender-issue-error');
        errorMessages.forEach(msg => msg.remove());
        
        // Clear error indicators on the section header and navigation
        const navItem = document.querySelector(`.form-nav-item[data-section="gender-issue"]`);
        const sectionTitle = document.querySelector(`#gender-issue .section-title`);
        
        navItem.classList.remove('has-error');
        if (sectionTitle) {
            sectionTitle.classList.remove('has-error');
        }
    }

    genderIssueInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Clear any existing error
        clearGenderIssueError();
        
        // Reset the isGenderIssueUsed flag when input changes
        isGenderIssueUsed = false;
        
        // Check if section should now show as complete
        checkCompletionStatus();
        
        // Clear previous results
        genderIssueResults.innerHTML = '';
        
        // Debounce to avoid too many requests
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            searchGenderIssues(query);
        }, 300); // 300ms debounce
    });
    
    // Close autocomplete results when clicking outside
    document.addEventListener('click', function(e) {
        if (!genderIssueInput.contains(e.target) && !genderIssueResults.contains(e.target)) {
            genderIssueResults.classList.remove('show');
        }
    });
    
    // When input loses focus, check if the value is a duplicate
    genderIssueInput.addEventListener('blur', function() {
        const query = this.value.trim();
        
        if (query && !isGenderIssueUsed) {
            // Check if we're in edit mode
            const addBtn = document.getElementById('addBtn');
            const isEditMode = addBtn.getAttribute('data-action') === 'update';
            const currentId = isEditMode ? addBtn.getAttribute('data-entry-id') : null;
            
            // Check if this is the original gender issue when in edit mode
            const originalIssue = isEditMode && originalEntryData ? originalEntryData.gender_issue : null;
            if (isEditMode && query === originalIssue) {
                // If we're editing and this is the original value, it's not a duplicate
                isGenderIssueUsed = false;
                clearGenderIssueError();
                return;
            }
            
            // Build the query string
            let apiUrl = `check_gender_issue.php?issue=${encodeURIComponent(query)}`;
            if (currentId) {
                apiUrl += `&current_id=${encodeURIComponent(currentId)}`;
            }
            
            // Check if this gender issue is already used by this campus
            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("Duplicate check result:", data); // Debug log
                    
                    if (data.success && data.isUsed) {
                        showGenderIssueError();
                        isGenderIssueUsed = true;
                        
                        // Always mark the section as invalid immediately when duplicate is found
                        const navItem = document.querySelector(`.form-nav-item[data-section="gender-issue"]`);
                        const sectionTitle = document.querySelector(`#gender-issue .section-title`);
                        
                        navItem.classList.add('has-error');
                        navItem.classList.remove('is-complete');
                        if (sectionTitle) {
                            sectionTitle.classList.add('has-error');
                        }
                        
                        // Trigger validation check
                        checkCompletionStatus();
                    } else {
                        isGenderIssueUsed = false;
                        clearGenderIssueError();
                    }
                })
                .catch(error => {
                    console.error('Error checking gender issue:', error);
                });
        }
    });
    
    // Override validateSection to check for gender issue duplication
    const originalValidateSection = validateSection;
    validateSection = function(sectionId) {
        let isValid = originalValidateSection(sectionId);
        
        // If validating gender-issue section and the gender issue is used, mark as invalid
        if (sectionId === 'gender-issue' && isGenderIssueUsed) {
            if (validationTriggered) {
                showGenderIssueError();
            }
            isValid = false;
            
            // Always mark the gender-issue section as having an error
            const navItem = document.querySelector(`.form-nav-item[data-section="gender-issue"]`);
            const sectionTitle = document.querySelector(`#gender-issue .section-title`);
            
            navItem.classList.add('has-error');
            navItem.classList.remove('is-complete');
            if (sectionTitle) {
                sectionTitle.classList.add('has-error');
            }
        }
        
        return isValid;
    };

    // Debug console logs for autocomplete
    console.log("Autocomplete initialized with corrected table name (gpb_entries instead of gbp_entries)");
    console.log("genderIssueInput element:", genderIssueInput);
    console.log("genderIssueResults element:", genderIssueResults);
    
    // Test the API endpoints directly
    fetch('search_gender_issues.php?term=test')
        .then(response => response.json())
        .then(data => {
            console.log("API test result for search_gender_issues.php (with corrected table name):", data);
        })
        .catch(error => {
            console.error("API test error for search_gender_issues.php:", error);
        });
        
    fetch('check_gender_issue.php?issue=test')
        .then(response => response.json())
        .then(data => {
            console.log("API test result for check_gender_issue.php (with corrected table name):", data);
        })
        .catch(error => {
            console.error("API test error for check_gender_issue.php:", error);
        });
    
    // Make the autocomplete container relative for proper positioning
});
    </script>
    
    <!-- Modal functionality script -->
    <script>
    // All modal code in one self-contained scope
    (function() {
        // Global variables for the modal
        let gbpModal;
        let currentMode = 'view'; // Can be 'view', 'edit', or 'delete'
        let currentPage = 1;
        const rowsPerPage = 5;
        let allEntries = []; // To store all entries for pagination
        let originalEntryData = null; // Store original entry data when editing
        
        // Debounce function to prevent too many requests
        function debounce(func, delay) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), delay);
            };
        }
        
        // Function to show the modal
        function showGbpEntriesModal(mode) {
            currentMode = mode;
            currentPage = 1;
            
            // Update modal title based on mode
            const modalTitle = document.getElementById('gbpEntriesModalLabel');
            switch(mode) {
                case 'view':
                    modalTitle.textContent = 'View GBP Entries';
                    break;
                case 'edit':
                    modalTitle.textContent = 'Edit GBP Entry';
                    break;
                case 'delete':
                    modalTitle.textContent = 'Delete GBP Entry';
                    break;
            }
            
            // Initialize Bootstrap modal if not already done
            if (!gbpModal) {
                gbpModal = new bootstrap.Modal(document.getElementById('gbpEntriesModal'));
                
                // Load years for filter dropdown when modal is shown
                document.getElementById('gbpEntriesModal').addEventListener('shown.bs.modal', function() {
                    loadYears();
                });
                
                // Set up event listeners for filter changes
                document.getElementById('filterGenderIssue').addEventListener('input', debounce(loadGbpEntries, 300));
                document.getElementById('filterCategory').addEventListener('change', loadGbpEntries);
                document.getElementById('filterYear').addEventListener('change', loadGbpEntries);
                
                if (document.getElementById('filterCampus').tagName === 'SELECT') {
                    document.getElementById('filterCampus').addEventListener('change', function() {
                        loadYears(); // Reload years when campus changes
                        loadGbpEntries();
                    });
                }
            }
                
            // Initial data load - pass the mode to ensure it's used correctly
                loadGbpEntries();
            
            // Show the modal
            gbpModal.show();
        }
        
        // Function to load available years from target table
        function loadYears() {
            const yearSelect = document.getElementById('filterYear');
            const campus = document.getElementById('filterCampus').value;
            
            // Skip if years are already loaded
            if (yearSelect.options.length > 1) return;
            
            // Show loading in the dropdown
            yearSelect.innerHTML = '<option value="">All Years</option><option value="" disabled>Loading years...</option>';
            
            fetch(`get_years.php?campus=${encodeURIComponent(campus)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Start with the default option
                        yearSelect.innerHTML = '<option value="">All Years</option>';
                        
                        // Add years from the response
                        data.years.forEach(year => {
                            const option = document.createElement('option');
                            option.value = year;
                            option.textContent = year;
                            yearSelect.appendChild(option);
                        });
                    } else {
                        console.error('Error loading years:', data.message);
                        yearSelect.innerHTML = '<option value="">All Years</option>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    yearSelect.innerHTML = '<option value="">All Years</option>';
                });
        }
        
        // Function to load GBP entries based on filters
        function loadGbpEntries() {
            const tableBody = document.getElementById('gbpEntriesTableBody');
            const noResultsMessage = document.getElementById('noResultsMessage');
            const pagination = document.getElementById('entriesPagination');
            const table = document.querySelector('.table');
            
            // Always reset to page 1 when applying filters
            currentPage = 1;
            
            // Get filter values
            const campus = document.getElementById('filterCampus').value;
            const year = document.getElementById('filterYear').value;
            const category = document.getElementById('filterCategory').value;
            const genderIssue = document.getElementById('filterGenderIssue').value;
            
            // Show loading state
            tableBody.innerHTML = '';
            pagination.innerHTML = '';
            // Hide the table immediately during loading to prevent flickering
            table.classList.add('d-none');
            noResultsMessage.classList.add('d-none');
            
            // Build query string
            const queryParams = new URLSearchParams();
            if (campus) queryParams.append('campus', campus);
            if (year) queryParams.append('year', year);
            if (category) queryParams.append('category', category);
            if (genderIssue) queryParams.append('gender_issue', genderIssue);
            
            // Fetch data
            fetch(`get_gbp_entries.php?${queryParams.toString()}`)
                .then(response => response.json())
                .then(data => {
                    allEntries = []; // Reset entries array
                    
                    if (data.success && data.entries && data.entries.length > 0) {
                        // Store all entries for pagination
                        allEntries = data.entries;
                        
                        // Display pagination and first page of entries
                        table.classList.remove('d-none');
                        displayPagination(data.entries.length);
                        displayEntries(currentPage);
                    } else {
                        // Show no results message and keep table hidden
                        noResultsMessage.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    noResultsMessage.classList.remove('d-none');
                });
        }
        
        // Function to display entries for current page
        function displayEntries(page) {
            const tableBody = document.getElementById('gbpEntriesTableBody');
            const startIndex = (page - 1) * rowsPerPage;
            const endIndex = Math.min(startIndex + rowsPerPage, allEntries.length);
            const entriesSlice = allEntries.slice(startIndex, endIndex);
            
            // Clear the table
            tableBody.innerHTML = '';
            
            console.log('Current mode for displaying entries:', currentMode);
            
            // Display entries for the current page
            entriesSlice.forEach(entry => {
                const row = document.createElement('tr');
                row.setAttribute('data-id', entry.id);
                
                // Add campus column if user is Central
                if (document.querySelector('th').textContent === 'Campus') {
                    const campusCell = document.createElement('td');
                    campusCell.textContent = entry.campus;
                    row.appendChild(campusCell);
                }
                
                // Add other columns
                const yearCell = document.createElement('td');
                yearCell.textContent = entry.year;
                row.appendChild(yearCell);
                
                const genderIssueCell = document.createElement('td');
                genderIssueCell.textContent = entry.gender_issue;
                row.appendChild(genderIssueCell);
                
                const categoryCell = document.createElement('td');
                categoryCell.textContent = entry.category;
                row.appendChild(categoryCell);
                
                const causeCell = document.createElement('td');
                causeCell.textContent = entry.cause_of_issue;
                row.appendChild(causeCell);
                
                const objectiveCell = document.createElement('td');
                objectiveCell.textContent = entry.gad_objective;
                row.appendChild(objectiveCell);
                
                const budgetCell = document.createElement('td');
                budgetCell.textContent = formatCurrency(entry.gad_budget);
                row.appendChild(budgetCell);
                
                // Add click handler based on mode - using the global currentMode variable
                row.style.cursor = 'pointer';
                
                // Add the appropriate click handler based on current mode
                switch(currentMode) {
                    case 'view':
                        // In view mode - rows don't do anything
                        break;
                    case 'edit':
                        row.addEventListener('click', function() {
                            editEntry(entry.id);
                        });
                        break;
                    case 'delete':
                        row.addEventListener('click', function() {
                            deleteEntry(entry.id);
                        });
                        break;
                    default:
                        console.log('Unknown mode:', currentMode);
                        break;
                }
                
                tableBody.appendChild(row);
            });
        }
        
        // Function to display pagination
        function displayPagination(totalItems) {
            const pagination = document.getElementById('entriesPagination');
            const totalPages = Math.ceil(totalItems / rowsPerPage);
            
            // Clear existing pagination
            pagination.innerHTML = '';
            
            // Create previous button
            const prevLi = document.createElement('li');
            prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
            
            const prevLink = document.createElement('a');
            prevLink.className = 'page-link';
            prevLink.href = '#';
            prevLink.setAttribute('aria-label', 'Previous');
            prevLink.innerHTML = '<span aria-hidden="true">&laquo;</span>';
            prevLi.appendChild(prevLink);
            
            prevLink.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentPage > 1) {
                    currentPage--;
                    displayEntries(currentPage);
                    updatePaginationUI(totalPages);
                }
                return false;
            });
            
            pagination.appendChild(prevLi);
            
            // Create page number buttons
            for (let i = 1; i <= totalPages; i++) {
                const pageLi = document.createElement('li');
                pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
                
                const pageLink = document.createElement('a');
                pageLink.className = 'page-link';
                pageLink.href = '#';
                pageLink.textContent = i;
                pageLi.appendChild(pageLink);
                
                pageLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentPage = i;
                    displayEntries(currentPage);
                    updatePaginationUI(totalPages);
                    return false;
                });
                
                pagination.appendChild(pageLi);
            }
            
            // Create next button
            const nextLi = document.createElement('li');
            nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
            
            const nextLink = document.createElement('a');
            nextLink.className = 'page-link';
            nextLink.href = '#';
            nextLink.setAttribute('aria-label', 'Next');
            nextLink.innerHTML = '<span aria-hidden="true">&raquo;</span>';
            nextLi.appendChild(nextLink);
            
            nextLink.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentPage < totalPages) {
                    currentPage++;
                    displayEntries(currentPage);
                    updatePaginationUI(totalPages);
                }
                return false;
            });
            
            pagination.appendChild(nextLi);
        }
        
        // Function to update pagination UI after page changes
        function updatePaginationUI(totalPages) {
            const pagination = document.getElementById('entriesPagination');
            const pageItems = pagination.querySelectorAll('li');
            
            // Update previous button
            pageItems[0].className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
            
            // Update page number buttons
            for (let i = 1; i <= totalPages; i++) {
                pageItems[i].className = `page-item ${i === currentPage ? 'active' : ''}`;
            }
            
            // Update next button
            pageItems[totalPages + 1].className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        }
        
        // Helper function to format currency
        function formatCurrency(amount) {
            return 'PHP ' + parseFloat(amount).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        // Entry action functions
        function editEntry(id) {
            resetFormSections();
            console.log('Edit entry with ID:', id);
            
            // Fetch the entry data
            fetch(`get_gbp_entry.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide the modal
                        const gbpEntriesModal = bootstrap.Modal.getInstance(document.getElementById('gbpEntriesModal'));
                        gbpEntriesModal.hide();
                        
                        // Populate the form with data
                        populateForm(data.entry);
                        
                        // Change card title
                        document.querySelector('.card-header .card-title').textContent = 'Edit GBP Form';
                        
                        // Change add button to update button
                        const addBtn = document.getElementById('addBtn');
                        addBtn.innerHTML = '<i class="fas fa-save"></i>';
                        addBtn.setAttribute('data-action', 'update');
                        addBtn.setAttribute('data-entry-id', id);
                        
                        // Transform edit button to cancel button
                        const editBtn = document.getElementById('editBtn');
                        editBtn.innerHTML = '<i class="fas fa-times"></i>';
                        editBtn.classList.add('editing');
                        editBtn.setAttribute('data-original-action', 'edit');
                        editBtn.setAttribute('data-action', 'cancel');
                        
                        // Store the original click handler and replace it
                        const originalEditHandler = editBtn.onclick;
                        editBtn.setAttribute('data-original-handler', 'stored');
                        editBtn.onclick = function() {
                            cancelEdit();
                            return false;
                        };
                        
                        // Disable delete button
                        const deleteBtn = document.getElementById('deleteBtn');
                        deleteBtn.disabled = true;
                        deleteBtn.classList.add('disabled');
                        
                        // Scroll to top
                        document.querySelector('.main-content').scrollTop = 0;
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || 'Failed to load entry data',
                            icon: 'error',
                            confirmButtonColor: '#6A1B9A'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to load entry data',
                        icon: 'error',
                        confirmButtonColor: '#6A1B9A'
                    });
                });
        }
        
        function populateForm(entry) {
            // Store the original entry data for reference
            originalEntryData = entry;
            
            // Define updateActivityCount function locally
            function updateActivityCount() {
                const activityItems = document.querySelectorAll('.activity-item');
                let filledActivitiesCount = 0;
                
                activityItems.forEach(item => {
                    const activityInput = item.querySelector('input[type="text"]');
                    if (activityInput && activityInput.value.trim() !== '') {
                        filledActivitiesCount++;
                    }
                });
                
                const activityCountField = document.getElementById('activityCount');
                if (activityCountField) {
                    activityCountField.value = filledActivitiesCount;
                }
            }
            
            // Define local function to add input listeners to activities
            function addActivityInputListener(activityItem) {
                const input = activityItem.querySelector('input[type="text"]');
                if (input) {
                    input.addEventListener('input', function() {
                        // Use setTimeout to ensure value is updated after typing
                        setTimeout(updateActivityCount, 50);
                    });
                }
            }
            
            // Initialize local counters
            let localProgramCounter = 0;
            let localActivityCounters = {};
            
            // Local implementation of addProgram to avoid scope issues
            const addProgramLocal = function() {
                // Use local counter
                localProgramCounter++;
                localActivityCounters[localProgramCounter] = 0; // Start at 0 so first activity will be 1
                
                const programsContainer = document.getElementById('programsContainer');
                const newProgram = document.createElement('div');
                newProgram.className = 'program-container';
                newProgram.setAttribute('data-program-id', localProgramCounter);
                
                newProgram.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="program-title">Program ${localProgramCounter}</span>
                        <button type="button" class="btn-delete-item" data-delete-type="program"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="form-group">
                        <label for="program_${localProgramCounter}" class="form-label">Program Name</label>
                        <input type="text" class="form-control" id="program_${localProgramCounter}" name="program_${localProgramCounter}" required>
                    </div>
                    <div class="activities-container" data-program-id="${localProgramCounter}"></div>
                `;
                
                programsContainer.appendChild(newProgram);
                
                // Add event listeners
                const addActivityBtn = newProgram.querySelector('.btn-add-item[data-add-type="activity"]');
                if (addActivityBtn) {
                    addActivityBtn.addEventListener('click', function() {
                        const programId = this.getAttribute('data-program-id');
                        addActivityLocal(programId);
                    });
                }
                
                const deleteButtons = newProgram.querySelectorAll('.btn-delete-item');
                deleteButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const deleteType = this.getAttribute('data-delete-type');
                        if (deleteType === 'program') {
                            const programContainer = this.closest('.program-container');
                            const programCount = document.querySelectorAll('.program-container').length;
                            
                            // Don't allow deleting the last program
                            if (programCount <= 1) {
                                Swal.fire({
                                    title: 'Cannot Delete',
                                    text: 'At least one program is required',
                                    icon: 'warning',
                                    confirmButtonColor: '#6a1b9a',
                                    backdrop: `rgba(0,0,0,0.7)`,
                                    allowOutsideClick: true
                                });
                                return;
                            }
                            
                                programContainer.remove();
                            delete localActivityCounters[programId];
                            
                            // Update activity count after deletion
                            const activityCountField = document.getElementById('activityCount');
                            if (activityCountField) {
                                const activityItems = document.querySelectorAll('.activity-item');
                                activityCountField.value = activityItems.length || 0;
                            }
                        } else if (deleteType === 'activity') {
                            const activityItem = this.closest('.activity-item');
                            const programContainer = this.closest('.program-container');
                            const activityCount = programContainer.querySelectorAll('.activity-item').length;
                            
                            // Don't allow deleting the last activity
                            if (activityCount <= 1) {
                                Swal.fire({
                                    title: 'Cannot Delete',
                                    text: 'At least one activity is required per program',
                                    icon: 'warning',
                                    confirmButtonColor: '#6a1b9a',
                                    backdrop: `rgba(0,0,0,0.7)`,
                                    allowOutsideClick: true
                                });
                                return;
                            }
                            
                                activityItem.remove();
                            
                            // Update activity count after deletion
                            const activityCountField = document.getElementById('activityCount');
                            if (activityCountField) {
                                const activityItems = document.querySelectorAll('.activity-item');
                                activityCountField.value = activityItems.length || 0;
                            }
                        }
                    });
                });
                
                return newProgram;
            };
            
            // Local implementation of addActivity to avoid scope issues
            const addActivityLocal = function(programId) {
                localActivityCounters[programId]++;
                
                const activitiesContainer = document.querySelector(`.activities-container[data-program-id="${programId}"]`);
                const newActivity = document.createElement('div');
                newActivity.className = 'activity-item';
                newActivity.setAttribute('data-activity-id', localActivityCounters[programId]);
                
                newActivity.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="activity-title">Activity ${localActivityCounters[programId]}</span>
                        <button type="button" class="btn-delete-item" data-delete-type="activity"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="form-group mb-0">
                        <label for="activity_${programId}_${localActivityCounters[programId]}" class="form-label">Activity Name</label>
                        <input type="text" class="form-control" id="activity_${programId}_${localActivityCounters[programId]}" name="activity_${programId}_${localActivityCounters[programId]}" required>
                    </div>
                `;
                
                // If there's an Add Activity button, insert before it
                const addBtn = activitiesContainer.querySelector('.btn-add-item');
                if (addBtn) {
                    activitiesContainer.insertBefore(newActivity, addBtn);
                } else {
                    // Otherwise just append
                    activitiesContainer.appendChild(newActivity);
                    
                    // Add the "Add Activity" button if it doesn't exist
                    const addActivityBtn = document.createElement('button');
                    addActivityBtn.type = 'button';
                    addActivityBtn.className = 'btn-add-item mt-3';
                    addActivityBtn.setAttribute('data-add-type', 'activity');
                    addActivityBtn.setAttribute('data-program-id', programId);
                    addActivityBtn.innerHTML = '<i class="fas fa-plus"></i> Add Activity';
                    addActivityBtn.addEventListener('click', function() {
                        addActivityLocal(programId);
                    });
                    activitiesContainer.appendChild(addActivityBtn);
                }
                
                // Add event listener to delete button
                const deleteBtn = newActivity.querySelector('.btn-delete-item');
                deleteBtn.addEventListener('click', function() {
                    const activityItem = this.closest('.activity-item');
                    const programContainer = this.closest('.program-container');
                    const activityCount = programContainer.querySelectorAll('.activity-item').length;
                    
                    if (activityCount <= 1) {
                        Swal.fire({
                            title: 'Cannot Delete',
                            text: 'At least one activity is required per program',
                            icon: 'warning',
                            confirmButtonColor: '#6a1b9a',
                            backdrop: `rgba(0,0,0,0.7)`,
                            allowOutsideClick: true
                        });
                        return;
                    }
                    
                    activityItem.remove();
                    updateActivityCount(); // Update count after deletion
                });
                
                // Get the input field
                const inputField = newActivity.querySelector('input[type="text"]');
                
                // Add direct event listener to this specific input
                if (inputField) {
                    inputField.addEventListener('input', function() {
                        // Immediately update the activity count
                        updateActivityCount();
                    });
                }
                
                // Also add through the standard method
                addActivityInputListener(newActivity);
                
                // Update activity count immediately
                setTimeout(updateActivityCount, 10);
                
                return newActivity;
            };
            
            // Basic Info
            document.getElementById('year').value = entry.year;
            
            // Get target data for the year directly instead of calling fetchTargetData
            const yearValue = entry.year;
            const campusValue = document.getElementById('campus').value;
            
            if (yearValue && campusValue) {
                // Fetch data from the target table
                fetch(`get_target_data.php?year=${yearValue}&campus=${campusValue}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Format the numbers with PHP currency format
                            document.getElementById('totalGAA').value = 'PHP ' + parseFloat(data.total_gaa).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            document.getElementById('totalGADFund').value = 'PHP ' + parseFloat(data.total_gad_fund).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        } else {
                            document.getElementById('totalGAA').value = '';
                            document.getElementById('totalGADFund').value = '';
                            console.error('Error fetching target data:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('totalGAA').value = '';
                        document.getElementById('totalGADFund').value = '';
                    });
            }
            
            // Gender Issue
            document.getElementById('genderIssue').value = entry.gender_issue;
            document.getElementById('genderCategory').value = entry.category;
            document.getElementById('causeGenderIssue').value = entry.cause_of_issue;
            document.getElementById('gadResult').value = entry.gad_objective;
            document.getElementById('relevantAgency').value = entry.relevant_agency;
            
            // Clear existing programs and activities
            document.getElementById('programsContainer').innerHTML = '';
            
            // Add programs and activities
            if (entry.programs && entry.programs.length > 0) {
                entry.programs.forEach((program, programIndex) => {
                    // Add program
                    const newProgram = addProgramLocal();
                    
                    // Get the program ID
                    const programId = newProgram.getAttribute('data-program-id');
                    
                    // Set program name
                    document.getElementById(`program_${programId}`).value = program.name;
                    
                    // Remove default activity
                    const activityContainer = newProgram.querySelector('.activities-container');
                    activityContainer.innerHTML = '';
                    
                    // Add activities
                    if (program.activities && program.activities.length > 0) {
                        program.activities.forEach((activity, activityIndex) => {
                            // Add new activity
                            const newActivity = addActivityLocal(programId);
                            
                            // Get the activity ID
                            const activityId = newActivity.getAttribute('data-activity-id');
                            
                            // Set activity name
                            document.getElementById(`activity_${programId}_${activityId}`).value = activity.name;
                        });
                        
                        // Add the final "Add Activity" button if it's not there
                        if (!activityContainer.querySelector('.btn-add-item')) {
                            const addActivityBtn = document.createElement('button');
                            addActivityBtn.type = 'button';
                            addActivityBtn.className = 'btn-add-item mt-3';
                            addActivityBtn.setAttribute('data-add-type', 'activity');
                            addActivityBtn.setAttribute('data-program-id', programId);
                            addActivityBtn.innerHTML = '<i class="fas fa-plus"></i> Add Activity';
                            activityContainer.appendChild(addActivityBtn);
                        }
                    } else {
                        // Add a default empty activity
                        addActivityLocal(programId);
                    }
                });
            } else {
                // Add a default empty program
                addProgramLocal();
            }
            
            // Update activity count - simplified version
            const activityCount = document.querySelectorAll('.activity-item').length;
            document.getElementById('activityCount').value = activityCount;
            
            // Performance & Budget
            document.getElementById('maleParticipants').value = entry.male_participants;
            document.getElementById('femaleParticipants').value = entry.female_participants;
            updateTotalParticipants();
            
            document.getElementById('gadBudget').value = entry.gad_budget;
            document.getElementById('sourceBudget').value = entry.source_of_budget;
            document.getElementById('responsibleUnit').value = entry.responsible_unit;
            
            // Make sure all the Add Activity buttons have event listeners
            const allAddActivityButtons = document.querySelectorAll('.btn-add-item[data-add-type="activity"]');
            allAddActivityButtons.forEach(button => {
                // Remove any existing event listeners to prevent duplicates
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);
                
                // Add fresh event listener
                newButton.addEventListener('click', function() {
                    const programId = this.getAttribute('data-program-id');
                    addActivityLocal(programId);
                });
            });
            
            // Make sure all delete buttons have event listeners
            const allDeleteButtons = document.querySelectorAll('.btn-delete-item');
            allDeleteButtons.forEach(button => {
                // Remove any existing event listeners to prevent duplicates
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);
                
                // Add fresh event listener
                newButton.addEventListener('click', function() {
                    const deleteType = this.getAttribute('data-delete-type');
                    if (deleteType === 'program') {
                        const programContainer = this.closest('.program-container');
                        const programCount = document.querySelectorAll('.program-container').length;
                        
                        if (programCount <= 1) {
                            Swal.fire({
                                title: 'Cannot Delete',
                                text: 'At least one program is required',
                                icon: 'warning',
                                confirmButtonColor: '#6a1b9a',
                                backdrop: `rgba(0,0,0,0.7)`,
                                allowOutsideClick: true
                            });
                            return;
                        }
                        
                        programContainer.remove();
                    } else if (deleteType === 'activity') {
                        const activityItem = this.closest('.activity-item');
                        const programContainer = this.closest('.program-container');
                        const activityCount = programContainer.querySelectorAll('.activity-item').length;
                        
                        if (activityCount <= 1) {
                            Swal.fire({
                                title: 'Cannot Delete',
                                text: 'At least one activity is required per program',
                                icon: 'warning',
                                confirmButtonColor: '#6a1b9a',
                                backdrop: `rgba(0,0,0,0.7)`,
                                allowOutsideClick: true
                            });
                            return;
                        }
                        
                        activityItem.remove();
                    }
                    
                    // Update activity count
                    const activityCountField = document.getElementById('activityCount');
                    if (activityCountField) {
                        const activityItems = document.querySelectorAll('.activity-item');
                        activityCountField.value = activityItems.length || 0;
                    }
                });
            });
            
            // Make sure all the Add Activity buttons have event listeners
/* Removed duplicate code that was causing errors */
            // Add input listener to all activity inputs
            document.querySelectorAll('.activity-item').forEach(item => {
                addActivityInputListener(item);
            });
            
            // Update activity count based on actual filled inputs
            setTimeout(updateActivityCount, 100);
        }
        
        function deleteEntry(id) {
            console.log('Delete entry with ID:', id);
            
            // First fetch the entry details
            fetch(`get_gbp_entry.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to retrieve entry details',
                            icon: 'error',
                            confirmButtonColor: '#6a1b9a'
                        });
                        return;
                    }
                    
                    const entry = data.entry;
                    
                    // Show confirmation modal with entry details
                    Swal.fire({
                        title: 'Confirm Deletion',
                        html: `
                            <div class="text-start">
                                <p>Are you sure you want to delete this entry? This action cannot be undone.</p>
                                <div class="mb-3 mt-4">
                                    <h6 class="mb-2">Entry Details:</h6>
                                    <table class="table table-bordered" style="background-color: #f8f9fa; color: #212529;">
                                        <tr>
                                            <th style="width: 140px; background-color: #e9ecef; color: #212529;">Year</th>
                                            <td style="background-color: #fff; color: #212529;">${entry.year}</td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #e9ecef; color: #212529;">Gender Issue</th>
                                            <td style="background-color: #fff; color: #212529;">${entry.gender_issue}</td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #e9ecef; color: #212529;">Category</th>
                                            <td style="background-color: #fff; color: #212529;">${entry.category}</td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #e9ecef; color: #212529;">Budget</th>
                                            <td style="background-color: #fff; color: #212529;">${formatCurrency(entry.gad_budget)}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6a1b9a',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        backdrop: `rgba(0,0,0,0.7)`,
                        allowOutsideClick: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Proceed with deletion by sending request to server
                            fetch(`delete_gbp.php?id=${id}`, {
                                method: 'POST'
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Show success message with auto-close timer
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: 'The entry has been deleted successfully.',
                                        icon: 'success',
                                        confirmButtonColor: '#6a1b9a',
                                        timer: 1500,
                                        timerProgressBar: true,
                                        showConfirmButton: false
                                    });
                                    
                                    // Reload entries
                                    loadGbpEntries();
                                } else {
                                    // Show error message
                                    Swal.fire({
                                        title: 'Error',
                                        text: data.message || 'Failed to delete entry',
                                        icon: 'error',
                                        confirmButtonColor: '#6a1b9a'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: 'Error',
                                    text: 'An error occurred while deleting the entry',
                                    icon: 'error',
                                    confirmButtonColor: '#6a1b9a'
                                });
                            });
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching entry details:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to retrieve entry details',
                        icon: 'error',
                        confirmButtonColor: '#6a1b9a'
                    });
                });
        }
        
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Set up button click handlers
            document.getElementById('viewBtn').addEventListener('click', function() {
                showGbpEntriesModal('view');
            });
            
            document.getElementById('editBtn').addEventListener('click', function() {
                showGbpEntriesModal('edit');
            });
            
            document.getElementById('deleteBtn').addEventListener('click', function() {
                showGbpEntriesModal('delete');
            });
        });
        
        // Expose the showGbpEntriesModal function globally for direct calls
        window.showGbpEntriesModal = showGbpEntriesModal;
        
        // Expose the resetFilters function globally for the reset button
        window.resetFilters = function() {
            // Reset all filter inputs
            document.getElementById('filterGenderIssue').value = '';
            document.getElementById('filterCategory').value = '';
            document.getElementById('filterYear').value = '';
            
            // Don't reset campus for campus-specific users
            const campusSelect = document.getElementById('filterCampus');
            if (campusSelect.tagName === 'SELECT') {
                campusSelect.value = '';
            }
            
            // Reload entries with reset filters
            loadGbpEntries();
        };

        // Calculate total participants
        const maleParticipantsInput = document.getElementById('maleParticipants');
        const femaleParticipantsInput = document.getElementById('femaleParticipants');
        const totalParticipantsInput = document.getElementById('totalParticipants');
        
        function updateTotalParticipants() {
            const maleParticipants = parseInt(maleParticipantsInput.value) || 0;
            const femaleParticipants = parseInt(femaleParticipantsInput.value) || 0;
            const totalParticipants = maleParticipants + femaleParticipants;
            
            totalParticipantsInput.value = totalParticipants;
        }
        
        maleParticipantsInput.addEventListener('input', updateTotalParticipants);
        femaleParticipantsInput.addEventListener('input', updateTotalParticipants);
    })();
    </script>
    
    <!-- GBP Entries Modal -->
    <div class="modal fade" id="gbpEntriesModal" tabindex="-1" aria-labelledby="gbpEntriesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="gbpEntriesModalLabel">GBP Entries</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Filters -->
                    <div class="filters-container">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="filterGenderIssue" class="form-label">Gender Issue</label>
                                <input type="text" class="form-control" id="filterGenderIssue" placeholder="Search gender issue...">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="filterCategory" class="form-label">Category</label>
                                <select class="form-select" id="filterCategory">
                                    <option value="">All Categories</option>
                                    <option value="Client-Focused">Client-Focused</option>
                                    <option value="Organization-Focused">Organization-Focused</option>
                                    <option value="Attributable PAPs">Attributable PAPs</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="filterYear" class="form-label">Year</label>
                                <select class="form-select" id="filterYear">
                                    <option value="">All Years</option>
                                    <!-- Will be populated dynamically -->
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="filterCampus" class="form-label">Campus</label>
                                <?php if ($_SESSION['campus'] == 'Central'): ?>
                                <select class="form-select" id="filterCampus">
                                    <option value="">All Campuses</option>
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
                                <?php else: ?>
                                <input type="text" class="form-control" value="<?php echo $_SESSION['campus']; ?>" readonly disabled>
                                <input type="hidden" id="filterCampus" value="<?php echo $_SESSION['campus']; ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Table -->
                    <div class="table-container">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <?php if ($_SESSION['campus'] == 'Central'): ?>
                                    <th>Campus</th>
                                    <?php endif; ?>
                                    <th>Year</th>
                                    <th>Gender Issue</th>
                                    <th>Category</th>
                                    <th>Cause of Issue</th>
                                    <th>GAD Objective</th>
                                    <th>Budget</th>
                                </tr>
                            </thead>
                            <tbody id="gbpEntriesTableBody">
                                <!-- Data will be populated here dynamically -->
                            </tbody>
                        </table>
                        
                        <!-- No results message -->
                        <div id="noResultsMessage" class="text-center py-4 d-none">
                            <div class="mb-3">
                                <i class="fas fa-search fa-3x"></i>
                            </div>
                            <h5>No gender issues found</h5>
                            <p class="small">Try adjusting your filters to find more results</p>
                        </div>
                        
                        <!-- Loading indicator -->
                        <div id="loadingIndicator" class="text-center py-4 d-none">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading data...</p>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination-container">
                        <nav aria-label="Page navigation">
                            <ul class="pagination" id="entriesPagination">
                                <!-- Pagination will be populated dynamically -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    // Check if user is from Central campus and disable form elements
    document.addEventListener('DOMContentLoaded', function() {
        const isCentralUser = <?php echo $isCentral ? 'true' : 'false'; ?>;
        
        if (isCentralUser) {
            // Disable all form inputs
            document.querySelectorAll('#gbpForm input, #gbpForm select, #gbpForm textarea').forEach(input => {
                input.disabled = true;
            });
            
            // Disable navigation items
            document.querySelectorAll('.form-nav-item').forEach(navItem => {
                navItem.classList.add('disabled');
                navItem.style.pointerEvents = 'none';
                navItem.style.opacity = '0.5';
            });
            
            // Disable navigation buttons
            document.querySelectorAll('.btn-form-nav').forEach(btn => {
                btn.disabled = true;
                btn.classList.add('disabled');
            });
            
            // Style section titles to appear disabled
            document.querySelectorAll('.section-title').forEach(title => {
                title.style.opacity = '0.5';
            });
            
            // Disable add button
            const addBtn = document.getElementById('addBtn');
            if (addBtn) {
                addBtn.disabled = true;
                addBtn.classList.add('disabled');
            }
            
            // Disable edit button
            const editBtn = document.getElementById('editBtn');
            if (editBtn) {
                editBtn.disabled = true;
                editBtn.classList.add('disabled');
            }
            
            // Disable delete button
            const deleteBtn = document.getElementById('deleteBtn');
            if (deleteBtn) {
                deleteBtn.disabled = true;
                deleteBtn.classList.add('disabled');
            }
            
            // Disable all add buttons within the form
            document.querySelectorAll('.btn-add-item').forEach(btn => {
                btn.disabled = true;
                btn.classList.add('disabled');
            });
            
            // Disable all delete buttons within form
            document.querySelectorAll('.btn-delete-item').forEach(btn => {
                btn.disabled = true;
                btn.classList.add('disabled');
            });
            
            // Display a notice to Central users
            const formContainer = document.querySelector('.form-container');
            if (formContainer) {
                const notice = document.createElement('div');
                notice.className = 'alert alert-info mb-3';
                notice.innerHTML = '<i class="fas fa-info-circle me-2"></i> As a Central user, you can only view GBP entries. Use the View button to see all entries.';
                formContainer.insertBefore(notice, formContainer.firstChild);
            }
        }
    });
</script>
</html>
