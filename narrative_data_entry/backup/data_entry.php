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

        .nav-item .dropdown-menu.show {
            display: block;
        }

        .nav-item .dropdown-menu .dropdown-item {
            padding: 6px 48px;
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
            margin-top: 15px;
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
            --dark-input: #2d2d2d;
            --dark-text: #e9ecef;
            --dark-border: #495057;
            --dark-sidebar: #2d2d2d;
        }

        /* Readonly field styling */
        .form-control:read-only, .form-select:disabled {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }

        [data-bs-theme="dark"] .form-control:read-only, 
        [data-bs-theme="dark"] .form-select:disabled {
            background-color: #343a40;
            color: #adb5bd;
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

        .btn-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
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

/* Active state for the approval link */
.approval-link.active {
    background-color: white !important;
    color: var(--accent-color) !important;
    font-weight: 700;
    box-shadow: 0 4px 10px rgba(106, 27, 154, 0.3);
    border-left: 4px solid var(--accent-color);
}

.approval-link.active i {
    transform: scale(1.15);
    color: var(--accent-color);
}

/* Dark theme active state */
[data-bs-theme="dark"] .approval-link.active {
    background-color: var(--dark-bg) !important;
    color: white !important;
    border-left: 4px solid #9c27b0;
    box-shadow: 0 4px 10px rgba(156, 39, 176, 0.5);
}

[data-bs-theme="dark"] .approval-link.active i {
    color: #9c27b0;
}

/* IMPROVED Active state for the approval link */
.approval-link.active {
    background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-hover) 100%) !important;
    color: white !important;
    font-weight: 700;
    box-shadow: 0 5px 15px rgba(106, 27, 154, 0.4);
    transform: translateY(-2px);
    border: none;
    position: relative;
    overflow: hidden;
}

.approval-link.active::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, 
        rgba(255,255,255,0) 40%, 
        rgba(255,255,255,0.3) 50%, 
        rgba(255,255,255,0) 60%);
    background-size: 200% 100%;
    animation: approvalShine 2s infinite;
}

@keyframes approvalShine {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.approval-link.active i {
    transform: scale(1.2);
    color: white;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1.2); }
    50% { transform: scale(1.4); }
    100% { transform: scale(1.2); }
}

/* Add a special indicator to show we're on the approval page */
.approval-link.active::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 50%;
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
    transform: translateY(-50%);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
    animation: pulse 2s infinite;
    z-index: 1;
}

/* Dark theme improved active state */
[data-bs-theme="dark"] .approval-link.active {
    background: linear-gradient(135deg, #9c27b0 0%, #7b1fa2 100%) !important;
    color: white !important;
    box-shadow: 0 5px 15px rgba(156, 39, 176, 0.6);
}

[data-bs-theme="dark"] .approval-link.active i {
    color: white;
}

/* Notification Badge for Approval */
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

/* Ensure the badge is visible in dark mode */
[data-bs-theme="dark"] .notification-badge {
    background-color: #ff5c6c;
}

/* Add this to the notification badge styles */
.approval-link.active .notification-badge {
    background-color: white;
    color: var(--accent-color);
}

[data-bs-theme="dark"] .approval-link.active .notification-badge {
    background-color: white;
    color: var(--accent-color);
}

        /* Form field styling improvements */
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25);
        }
        
        /* Improved card styling */
        .card {
            overflow: hidden;
            border: none;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--accent-color) 0%, #8e24aa 100%);
            color: white;
            font-weight: 500;
            border-bottom: none;
            padding: 15px 20px;
        }
        
        .card-body {
            padding: 25px;
        }
        
        /* Form section spacing */
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        textarea.form-control {
            min-height: 100px;
        }
        
        /* Dark theme form adjustments */
        [data-bs-theme="dark"] .form-control, 
        [data-bs-theme="dark"] .form-select {
            background-color: var(--dark-input);
            border-color: var(--dark-border);
            color: var(--dark-text);
        }
        
        [data-bs-theme="dark"] .card {
            background-color: var(--dark-sidebar);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
        }
        
        [data-bs-theme="dark"] .card-header {
            background: linear-gradient(135deg, #7b1fa2 0%, #6a1b9a 100%);
        }
        
        /* Improved button styling at the bottom */
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
        
        .btn-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn-icon i {
            font-size: 1.2rem;
        }
        
        /* Status message styling */
        #save-status {
            transition: all 0.3s ease;
            font-weight: 500;
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
                    <a class="nav-link active dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                    <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i> Reports
                    </a>
                    <ul class="dropdown-menu">                       
                        <li><a class="dropdown-item" href="../ppas_report/ppas_report.php">Quarterly Report</a></li>
                        <li><a class="dropdown-item" href="../ps_atrib/ps.php">PS Attribution</a></li>
                        <li><a class="dropdown-item" href="../annual_report/annual_report.php">Annual Report</a></li>
                    </ul>
                </div>
                <?php 
$currentPage = basename($_SERVER['PHP_SELF']);
if($isCentral): 
?>
<a href="../approval/approval.php" class="nav-link approval-link <?php echo ($currentPage == 'approval.php') ? 'active' : ''; ?>">
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
            <i class="fas fa-users-gear"></i>
            <h2>Narrative Report Data Entry</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Narrative Report Entry Form</h5>
            </div>
            <div class="card-body">
                <form id="narrativeForm" method="post" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3">
                            <label for="campus" class="form-label">Campus</label>
                            <select class="form-select" id="campus" name="campus" required <?php if(!$isCentral) echo 'disabled'; ?>>
                                <option value="" selected disabled>Select Campus</option>
                                <!-- Will be populated dynamically -->
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="year" class="form-label">Year</label>
                            <select class="form-select" id="year" name="year" required>
                                <option value="" selected disabled>Select Year</option>
                                <!-- Will be populated with static years -->
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="title" class="form-label">Activity</label>
                            <select class="form-select" id="title" name="title" required>
                                <option value="" selected disabled>Select Activity</option>
                                <!-- Will be populated from ppas_forms table -->
                            </select>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="background" class="form-label">Background/Rationale</label>
                            <textarea class="form-control" id="background" name="background" rows="3"></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="participants" class="form-label">Description of Participants</label>
                            <textarea class="form-control" id="participants" name="participants" rows="3"></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="topics" class="form-label">Narrative of Topics Discussed</label>
                            <textarea class="form-control" id="topics" name="topics" rows="3"></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="results" class="form-label">Expected Results, Actual Outputs and Outcomes</label>
                            <textarea class="form-control" id="results" name="results" rows="3"></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="lessons" class="form-label">Lesson Learned</label>
                            <textarea class="form-control" id="lessons" name="lessons" rows="3"></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="whatWorked" class="form-label">What Worked and Did Not Work</label>
                            <textarea class="form-control" id="whatWorked" name="whatWorked" rows="3"></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="issues" class="form-label">Issues and Concerns Raised and How Addressed</label>
                            <textarea class="form-control" id="issues" name="issues" rows="3"></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="recommendations" class="form-label">Recommendations</label>
                            <textarea class="form-control" id="recommendations" name="recommendations" rows="3"></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="psAttribution" class="form-label">PS Attribution</label>
                            <input type="text" class="form-control" id="psAttribution" name="psAttribution" readonly>
                            <small class="text-muted">Auto-populated from personnel involved</small>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="evaluation" class="form-label">Evaluation Result</label>
                            <textarea class="form-control" id="evaluation" name="evaluation" rows="3"></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Photo Documentation with Caption</label>
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <label for="photoCaption" class="visually-hidden">Photo Caption</label>
                                    <textarea class="form-control" id="photoCaption" name="photoCaption" rows="2" placeholder="Enter photo captions"></textarea>
                                </div>
                                
                                <div class="col-md-12">
                                    <p class="small text-muted mb-2">Upload Activity Images (Up to 6)</p>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="photoUpload" name="photoUpload[]" accept="image/*" multiple>
                                        <label class="input-group-text" for="photoUpload">Choose Files</label>
                                    </div>
                                    <div id="photoPreviewContainer" class="row g-2 mt-2"></div>
                                    <div id="upload-status"></div>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="genderIssue" class="form-label">Gender Issue</label>
                                    <input type="text" class="form-control" id="genderIssue" name="genderIssue" readonly>
                                    <small class="text-muted">Auto-populated from GPB</small>
                                </div>
                            </div>
                            
                            <!-- Add hidden input for narrative ID when editing -->
                            <input type="hidden" id="narrative_id" name="narrative_id" value="0">
                            
                            <div class="col-12 text-end mt-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn-icon" id="viewBtn">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <div id="save-status"></div>
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
                                        <div id="save-spinner" class="spinner-border text-primary" role="status" style="display: none;">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
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

        // Function to handle SQL import - moved outside to make it globally accessible
        function importSQL() {
            Swal.fire({
                title: 'Import Database Table',
                text: 'Would you like to create the narrative_entries table in the database?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, create it',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Make AJAX call to a PHP script that will import the SQL
                    $.ajax({
                        url: 'import_table.php',
                        type: 'POST',
                        data: { table: 'narrative_entries' },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: 'Database table created successfully!'
                                }).then(() => {
                                    location.reload(); // Reload the page
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Failed to create table'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error (import): " + status + " - " + error);
                            console.log("Response Text: " + xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Server error while creating table'
                            });
                        }
                    });
                }
            });
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

            // Initialize photo upload previews
            setupPhotoUploads();

            // Initialize Narrative CRUD operations
            initializeNarrativeCRUD();
        });

        // Setup photo upload previews
        function setupPhotoUploads() {
            // Add event listener to the photo upload input
            const photoInput = document.getElementById('photoUpload');
            if (photoInput) {
                photoInput.addEventListener('change', function(e) {
                    // Auto-upload images immediately when selected
                    if (this.files && this.files.length > 0) {
                        uploadImages(this.files);
                    }
                });
            }
        }
        
        // Function to upload images immediately
        function uploadImages(files) {
            // First show temporary previews
            previewImages(files);
            
            // Get current narrative ID if editing
            const currentNarrativeId = window.currentNarrativeId || 0;
            const campus = document.getElementById('campus').value;
            
            // Log the upload attempt
            console.log(`Uploading images for narrative ID: ${currentNarrativeId}, campus: ${campus}`);
            
            // Create FormData object
            const formData = new FormData();
            
            // Add all files to FormData
            for (let i = 0; i < files.length; i++) {
                formData.append('images[]', files[i]);
                console.log(`Adding file to upload: ${files[i].name} (${files[i].size} bytes)`);
            }
            
            // Add narrative ID and campus
            formData.append('narrative_id', currentNarrativeId);
            formData.append('campus', campus);
            
            // Show loading state
            const previewContainer = document.getElementById('photoPreviewContainer');
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'col-12 text-center loading-indicator';
            loadingDiv.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Uploading images...</p>';
            previewContainer.appendChild(loadingDiv);
            
            // Send AJAX request
            $.ajax({
                url: 'image_upload_handler.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    // Remove loading indicator
                    const indicator = document.querySelector('.loading-indicator');
                    if (indicator) indicator.remove();
                    
                    console.log('Image upload response:', response);
                    
                    if (response.success) {
                        // Update the preview container with the confirmed uploaded images
                        previewUploadedImages(response.images);
                        
                        // Store the narrative ID if provided by server (for new narratives)
                        if (response.narrative_id) {
                            window.currentNarrativeId = response.narrative_id;
                            console.log(`Updated currentNarrativeId to: ${response.narrative_id}`);
                        }
                        
                        // Clear the file input
                        document.getElementById('photoUpload').value = '';
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Images uploaded successfully!',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        
                        // Verify the photo paths were stored correctly
                        if (currentNarrativeId > 0) {
                            // Make a verification request to check the photo_path
                            verifyPhotoPathStorage(currentNarrativeId);
                        }
                    } else {
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to upload images',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 5000
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Remove loading indicator
                    const indicator = document.querySelector('.loading-indicator');
                    if (indicator) indicator.remove();
                    
                    console.error('Upload error:', error);
                    console.log('Response text:', xhr.responseText);
                    
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Failed to upload images. Server error occurred.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000
                    });
                }
            });
        }
        
        // Function to verify the photo_path was stored correctly
        function verifyPhotoPathStorage(narrativeId) {
            $.ajax({
                url: 'narrative_handler.php',
                type: 'POST',
                data: {
                    action: 'get_single',
                    id: narrativeId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const narrative = response.data;
                        console.log('Verification - Retrieved narrative data:', narrative);
                        
                        // Check photo_path property
                        if (narrative.photo_path) {
                            console.log('Verification - photo_path:', narrative.photo_path);
                        } else {
                            console.warn('Verification - photo_path is empty or undefined');
                        }
                        
                        // Check photo_paths array property
                        if (narrative.photo_paths && Array.isArray(narrative.photo_paths)) {
                            console.log('Verification - photo_paths array:', narrative.photo_paths);
                        } else {
                            console.warn('Verification - photo_paths array is empty or not an array');
                        }
                    } else {
                        console.error('Verification failed:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Verification error:', error);
                }
            });
        }
        
        // Function to preview images temporarily before upload completes
        function previewImages(files) {
            const previewContainer = document.getElementById('photoPreviewContainer');
            
            // Clear existing temporary previews
            const tempPreviews = previewContainer.querySelectorAll('.temp-preview');
            tempPreviews.forEach(el => el.remove());
            
            // Check if there are files selected
            if (files && files.length > 0) {
                // Limit to 6 files
                const maxFiles = Math.min(files.length, 6);
                
                // Process each file
                for (let i = 0; i < maxFiles; i++) {
                    const file = files[i];
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        // Create preview container
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'col-md-2 mb-2 temp-preview';
                        
                        // Create image element
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'img-thumbnail';
                        img.style.height = '100px';
                        img.style.width = '100%';
                        img.style.objectFit = 'cover';
                        img.style.opacity = '0.7'; // Lower opacity for temp preview
                        
                        // Add a badge to show this is being processed
                        const processingLabel = document.createElement('small');
                        processingLabel.className = 'badge bg-warning position-absolute top-0 end-0 m-1';
                        processingLabel.textContent = 'Uploading...';
                        
                        // Make the div relative for positioning the badge
                        previewDiv.style.position = 'relative';
                        
                        // Append elements
                        previewDiv.appendChild(img);
                        previewDiv.appendChild(processingLabel);
                        previewContainer.appendChild(previewDiv);
                    }
                    
                    reader.readAsDataURL(file);
                }
            }
        }
        
        // Function to display confirmed uploaded images
        function previewUploadedImages(imagePaths) {
            const previewContainer = document.getElementById('photoPreviewContainer');
            
            // Clear existing temporary previews
            const tempPreviews = previewContainer.querySelectorAll('.temp-preview');
            tempPreviews.forEach(el => el.remove());
            
            // Track images we've already displayed to avoid duplicates
            const displayedPaths = new Set();
            
            // Add each confirmed image
            if (Array.isArray(imagePaths) && imagePaths.length > 0) {
                // Clear existing permanent previews too when replacing with new set
                previewContainer.innerHTML = '';
                
                // Display up to 6 images
                for (let i = 0; i < Math.min(imagePaths.length, 6); i++) {
                    const path = imagePaths[i];
                    
                    // Skip if we've already displayed this image
                    if (displayedPaths.has(path)) {
                        console.log(`Skipping duplicate image: ${path}`);
                        continue;
                    }
                    
                    // Add to tracked paths
                    displayedPaths.add(path);
                    
                    // Create preview container
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'col-md-2 mb-2';
                    previewDiv.setAttribute('data-path', path);
                    
                    // Get the display path (add photos/ if it's just a filename)
                    let displayPath = path;
                    if (!path.includes('/') && !path.includes('\\')) {
                        displayPath = 'photos/' + path;
                    }
                    
                    // Create image element
                    const img = document.createElement('img');
                    img.src = displayPath;
                    img.className = 'img-thumbnail';
                    img.style.height = '100px';
                    img.style.width = '100%';
                    img.style.objectFit = 'cover';
                    
                    // Create remove button
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 m-1';
                    removeBtn.innerHTML = '&times;';
                    removeBtn.style.padding = '0.1rem 0.4rem';
                    
                    // Attach click event to remove button
                    removeBtn.addEventListener('click', function() {
                        removeImage(path, previewDiv);
                    });
                    
                    // Make the div relative for positioning the button
                    previewDiv.style.position = 'relative';
                    
                    // Append elements
                    previewDiv.appendChild(img);
                    previewDiv.appendChild(removeBtn);
                    previewContainer.appendChild(previewDiv);
                }
            }
        }
        
        // Function to remove an image
        function removeImage(imagePath, previewElement) {
            // Get current narrative ID
            const narrativeId = window.currentNarrativeId || 0;
            
            if (narrativeId > 0) {
                // Show confirmation dialog
                Swal.fire({
                    title: 'Remove Image?',
                    text: 'Are you sure you want to remove this image?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Remove the image from the database
                        $.ajax({
                            url: 'remove_image.php',
                            type: 'POST',
                            data: {
                                narrative_id: narrativeId,
                                image_path: imagePath
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    // Remove the preview element
                                    previewElement.remove();
                                    
                                    // Show success message
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: 'Image removed successfully!',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                } else {
                                    // Show error message
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message || 'Failed to remove image',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 5000
                                    });
                                }
                            },
                            error: function() {
                                // Show error message
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Server Error',
                                    text: 'Failed to remove image. Server error occurred.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 5000
                                });
                            }
                        });
                    }
                });
            } else {
                // For new narratives (not yet saved), just remove the preview
                previewElement.remove();
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

        // NARRATIVE CRUD OPERATIONS
        function initializeNarrativeCRUD() {
            // Global variables for CRUD operations
            let narrativeData = [];
            let currentNarrativeId = null;
            let isEditing = false;
            const isCentral = <?php echo $isCentral ? 'true' : 'false'; ?>;

            // DOM elements
            const form = document.getElementById('narrativeForm');
            const addBtn = document.getElementById('addBtn');
            const editBtn = document.getElementById('editBtn');
            const deleteBtn = document.getElementById('deleteBtn');
            const viewBtn = document.getElementById('viewBtn');
            const narrativeIdField = document.getElementById('narrative_id');
            const saveSpinner = document.getElementById('save-spinner');
            
            // Set campus for non-Central users
            if (!isCentral) {
                // For non-Central users, fetch their campus from session and set it
                $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: { action: 'get_user_campus' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.campus) {
                            // Set campus dropdown to user's campus
                            $('#campus').val(response.campus);
                            
                            // Add hidden input to ensure campus is passed when form is submitted
                            const campusInput = document.createElement('input');
                            campusInput.type = 'hidden';
                            campusInput.name = 'campus_override';
                            campusInput.id = 'campus_override';
                            campusInput.value = response.campus;
                            form.appendChild(campusInput);
                            
                            // This will trigger loading years for this campus
                            loadYearsForCampus(response.campus);
                        }
                    }
                });
            }

            // Initially disable edit and delete buttons
            // editBtn.classList.add('btn-disabled');
            // deleteBtn.classList.add('btn-disabled');
            
            // Load narratives when page loads
            loadNarratives();
            
            // Load dropdown options
            loadDropdownOptions();

            // Event listeners
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // If not central, ensure the campus is set from the override
                if (!isCentral && document.getElementById('campus_override')) {
                    document.getElementById('campus_override').value = document.getElementById('campus').value;
                }
                
                handleFormSubmit(e);
            });
            
            editBtn.addEventListener('click', function() {
                // If currently editing, just cancel the edit
                if (isEditing) {
                    cancelEdit();
                    return;
                }
                
                // Show loading message
                const saveStatus = document.getElementById('save-status');
                saveStatus.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Loading narratives...';
                
                // Get filter parameters
                const campusFilter = isCentral ? '' : (document.getElementById('campus_override')?.value || document.getElementById('campus').value);
                
                // Fetch the latest data before showing the modal
                $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: { 
                        action: 'read',
                        campus: campusFilter
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Clear loading message
                        saveStatus.innerHTML = '';
                        
                        if (response.success) {
                            narrativeData = response.data || [];
                            
                            // Now show the list with the updated data for editing
                            showNarrativesActionList('edit');
                        } else {
                            saveStatus.innerHTML = '<span class="text-danger">Error loading narratives</span>';
                            setTimeout(() => { saveStatus.innerHTML = ''; }, 3000);
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to load narratives'
                            });
                        }
                    },
                    error: function(xhr) {
                        saveStatus.innerHTML = '<span class="text-danger">Server error</span>';
                        setTimeout(() => { saveStatus.innerHTML = ''; }, 3000);
                        
                        console.error("AJAX Error:", xhr.responseText);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Failed to connect to the server. Please try again later.'
                        });
                    }
                });
            });
            
            deleteBtn.addEventListener('click', function() {
                // If in edit mode, don't allow deletion
                if (isEditing) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Edit Mode Active',
                        text: 'Please finish or cancel editing before deleting entries',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }
                
                // Show loading message
                const saveStatus = document.getElementById('save-status');
                saveStatus.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Loading narratives...';
                
                // Get filter parameters
                const campusFilter = isCentral ? '' : (document.getElementById('campus_override')?.value || document.getElementById('campus').value);
                
                // Fetch the latest data before showing the modal
                $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: { 
                        action: 'read',
                        campus: campusFilter
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Clear loading message
                        saveStatus.innerHTML = '';
                        
                        if (response.success) {
                            narrativeData = response.data || [];
                            
                            // Now show the list with the updated data for deletion
                            showNarrativesActionList('delete');
                        } else {
                            saveStatus.innerHTML = '<span class="text-danger">Error loading narratives</span>';
                            setTimeout(() => { saveStatus.innerHTML = ''; }, 3000);
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to load narratives'
                            });
                        }
                    },
                    error: function(xhr) {
                        saveStatus.innerHTML = '<span class="text-danger">Server error</span>';
                        setTimeout(() => { saveStatus.innerHTML = ''; }, 3000);
                        
                        console.error("AJAX Error:", xhr.responseText);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Failed to connect to the server. Please try again later.'
                        });
                    }
                });
            });
            
            viewBtn.addEventListener('click', function() {
                // Show loading message
                const saveStatus = document.getElementById('save-status');
                saveStatus.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Loading narratives...';
                
                // Get filter parameters
                const campusFilter = isCentral ? '' : (document.getElementById('campus_override')?.value || document.getElementById('campus').value);
                
                // Fetch the latest data before showing the modal
                $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: { 
                        action: 'read',
                        campus: campusFilter
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Clear loading message
                        saveStatus.innerHTML = '';
                        
                        if (response.success) {
                            narrativeData = response.data || [];
                            
                            // Now show the list with the updated data for viewing
                            showNarrativesActionList('view');
                        } else {
                            saveStatus.innerHTML = '<span class="text-danger">Error loading narratives</span>';
                            setTimeout(() => { saveStatus.innerHTML = ''; }, 3000);
                            
                            // Check if table doesn't exist
                            if (response.message && response.message.includes("doesn't exist")) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Database Setup Required',
                                    text: 'The database table for narratives needs to be created.',
                                    footer: '<a href="javascript:void(0)" onclick="importSQL(); return false;">Click here to create the table</a>'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Failed to load narratives'
                                });
                            }
                        }
                    },
                    error: function(xhr) {
                        saveStatus.innerHTML = '<span class="text-danger">Server error</span>';
                        setTimeout(() => { saveStatus.innerHTML = ''; }, 3000);
                        
                        console.error("AJAX Error:", xhr.responseText);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Failed to connect to the server. Please try again later.'
                        });
                    }
                });
            });
            
            // Function to handle form submission (create or update)
            function handleFormSubmit(e) {
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                
                // Show saving spinner
                saveSpinner.style.display = 'inline-block';
                
                // Create FormData object from the form
                const formData = new FormData(form);
                
                // Add action based on whether we're editing or adding
                formData.append('action', isEditing ? 'update' : 'create');
                
                // If not central, ensure the campus is set
                if (!isCentral && document.getElementById('campus_override')) {
                    formData.append('campus', document.getElementById('campus_override').value);
                }
                
                // Send AJAX request
                $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        // Hide spinner
                        saveSpinner.style.display = 'none';
                        
                        if (response.success) {
                            // Update currentNarrativeId
                            currentNarrativeId = response.narrative_id || null;
                            if (currentNarrativeId) {
                                narrativeIdField.value = currentNarrativeId;
                                window.currentNarrativeId = currentNarrativeId;
                            }
                            
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message || (isEditing ? 'Narrative updated successfully' : 'Narrative added successfully'),
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                            
                            // If we were editing, exit edit mode but keep buttons enabled
                            if (isEditing) {
                                // Reset editing state
                                isEditing = false;
                                editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                                editBtn.classList.remove('editing');
                                editBtn.title = 'Edit narrative';
                                
                                // Reset add button
                                addBtn.innerHTML = '<i class="fas fa-plus"></i>';
                                addBtn.title = 'Add new narrative';
                                addBtn.classList.remove('btn-update');
                                
                                // Clear form fields but keep the form ready for new data
                                form.reset();
                                
                                // For non-central users, restore their campus
                                if (!isCentral && document.getElementById('campus_override')) {
                                    const campusValue = document.getElementById('campus_override').value;
                                    document.getElementById('campus').value = campusValue;
                                    
                                    // Reload years based on campus
                                    loadYearsForCampus(campusValue);
                                }
                                
                                // Clear photo previews
                                document.getElementById('photoPreviewContainer').innerHTML = '';
                            } else {
                                // For new entries, just reset the form
                                form.reset();
                                
                                // For non-central users, restore their campus
                                if (!isCentral && document.getElementById('campus_override')) {
                                    const campusValue = document.getElementById('campus_override').value;
                                    document.getElementById('campus').value = campusValue;
                                    
                                    // Reload years based on campus
                                    loadYearsForCampus(campusValue);
                                }
                                
                                // Clear photo previews
                                document.getElementById('photoPreviewContainer').innerHTML = '';
                            }
                            
                            // Clear current narrative ID to prepare for new entry
                            currentNarrativeId = null;
                            window.currentNarrativeId = null;
                            narrativeIdField.value = '0';
                            
                            // Reload narratives list
                            loadNarratives();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to save narrative',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 5000
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Hide spinner
                        saveSpinner.style.display = 'none';
                        
                        console.error("AJAX Error: " + status + " - " + error);
                        console.log("Response Text: " + xhr.responseText);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Server error while processing your request',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 5000
                        });
                    }
                });
            }

            // Function to start edit mode
            function startEdit() {
                if (!currentNarrativeId) return;
                
                isEditing = true;
                
                // Change button appearance
                editBtn.innerHTML = '<i class="fas fa-times"></i>';
                editBtn.classList.add('editing');
                editBtn.title = 'Cancel editing';
                
                // Update add button to show it's for saving now
                addBtn.innerHTML = '<i class="fas fa-save"></i>';
                addBtn.title = 'Save changes';
                addBtn.classList.add('btn-update');
                
                // Load narrative data into form
                loadNarrativeForEdit(currentNarrativeId);
            }
            
            // Function to cancel edit mode
            function cancelEdit() {
                isEditing = false;
                
                // Revert button appearance
                editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                editBtn.classList.remove('editing');
                editBtn.title = 'Edit narrative';
                
                // Reset add button
                addBtn.innerHTML = '<i class="fas fa-plus"></i>';
                addBtn.title = 'Add new narrative';
                addBtn.classList.remove('btn-update');
                
                // Clear form
                resetForm();
            }
            
            // Function to load narrative for editing
            function loadNarrativeForEdit(narrativeId) {
                // Show loading indicator
                const saveStatus = document.getElementById('save-status');
                saveStatus.innerHTML = '<div class="d-flex align-items-center"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"><span class="visually-hidden">Loading...</span></div><span>Loading narrative data...</span></div>';
                
                // Add loading overlay to form
                const formContainer = document.querySelector('.card-body');
                const overlay = document.createElement('div');
                overlay.className = 'position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center';
                overlay.style.backgroundColor = 'rgba(0,0,0,0.1)';
                overlay.style.zIndex = '10';
                overlay.style.borderRadius = 'inherit';
                overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
                overlay.id = 'form-loading-overlay';
                formContainer.style.position = 'relative';
                formContainer.appendChild(overlay);
                
                $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: { 
                        action: 'get_single',
                        id: narrativeId
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Remove loading overlay
                        const overlay = document.getElementById('form-loading-overlay');
                        if (overlay) {
                            overlay.remove();
                        }
                        
                        // Clear status message
                        saveStatus.innerHTML = '';
                        
                        if (response.success) {
                            const narrative = response.data;
                            
                            // First populate the campus dropdown
                            const campusSelect = document.getElementById('campus');
                            campusSelect.value = narrative.campus;
                            
                            // Store the narrative ID globally
                            window.currentNarrativeId = narrativeId;
                            narrativeIdField.value = narrativeId;
                            
                            // Then load years for this campus
                            loadYearsForCampus(narrative.campus, function() {
                                // Once years are loaded, set the year
                                document.getElementById('year').value = narrative.year;
                                
                                // Then load activities for this campus and year
                                loadActivitiesForCampusAndYear(function() {
                                    // Once activities are loaded, set the title
                                    document.getElementById('title').value = narrative.title;
                                    
                                    // Populate the rest of the form fields
                                    document.getElementById('background').value = narrative.background || '';
                                    document.getElementById('participants').value = narrative.participants || '';
                                    document.getElementById('topics').value = narrative.topics || '';
                                    document.getElementById('results').value = narrative.results || '';
                                    document.getElementById('lessons').value = narrative.lessons || '';
                                    document.getElementById('whatWorked').value = narrative.what_worked || '';
                                    document.getElementById('issues').value = narrative.issues || '';
                                    document.getElementById('recommendations').value = narrative.recommendations || '';
                                    document.getElementById('psAttribution').value = narrative.ps_attribution || '';
                                    document.getElementById('evaluation').value = narrative.evaluation || '';
                                    document.getElementById('photoCaption').value = narrative.photo_caption || '';
                                    document.getElementById('genderIssue').value = narrative.gender_issue || '';
                                    
                                    // Scroll to top of form
                                    document.querySelector('.card').scrollIntoView({ behavior: 'smooth', block: 'start' });
                                    
                                    // Clear existing photo previews
                                    const previewContainer = document.getElementById('photoPreviewContainer');
                                    previewContainer.innerHTML = '';
                                    
                                    // Show existing photo previews if available
                                    const photoArray = narrative.photo_paths || [];
                                    if (narrative.photo_path && !photoArray.includes(narrative.photo_path)) {
                                        photoArray.push(narrative.photo_path);
                                    }
                                    
                                    if (photoArray.length > 0) {
                                        previewUploadedImages(photoArray);
                                    }
                                });
                            });
                        } else {
                            saveStatus.innerHTML = '<span class="badge bg-danger">Error: Failed to load data</span>';
                            setTimeout(() => { saveStatus.innerHTML = ''; }, 3000);
                            
                            console.error("Error loading narrative:", response.message);
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to load narrative data',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function(xhr) {
                        // Remove loading overlay
                        const overlay = document.getElementById('form-loading-overlay');
                        if (overlay) {
                            overlay.remove();
                        }
                        
                        // Show error message
                        saveStatus.innerHTML = '<span class="badge bg-danger">Server error</span>';
                        setTimeout(() => { saveStatus.innerHTML = ''; }, 3000);
                        
                        console.error("AJAX Error:", xhr.responseText);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Server error while loading narrative data',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
            }

            // Function to load all narratives
            function loadNarratives() {
                // Get filter parameters for non-central users
                const campusFilter = isCentral ? '' : (document.getElementById('campus_override')?.value || document.getElementById('campus').value);
                
                $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: { 
                        action: 'read',
                        campus: campusFilter
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            narrativeData = response.data || [];
                        } else {
                            console.error("Error loading narratives:", response.message);
                            narrativeData = [];
                            
                            // Check if table doesn't exist
                            if (response.message && response.message.includes("doesn't exist")) {
                            Swal.fire({
                                    icon: 'warning',
                                    title: 'Database Setup Required',
                                    text: 'The database table for narratives needs to be created.',
                                    footer: '<a href="javascript:void(0)" onclick="importSQL(); return false;">Click here to create the table</a>'
                                });
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error("AJAX Error:", xhr.responseText);
                        narrativeData = [];
                    }
                });
            }

            // Function to show narratives list for any action (view, edit, delete)
            function showNarrativesActionList(action) {
                if (narrativeData.length === 0) {
                        Swal.fire({
                        icon: 'info',
                        title: 'No Records',
                        text: 'No narrative entries found'
                    });
                    return;
                }
                
                // Set title based on action
                let title = 'View Narrative Entries';
                let iconClass = 'fa-eye text-primary';
                
                if (action === 'edit') {
                    title = 'Edit Narrative Entry';
                    iconClass = 'fa-edit text-warning';
                } else if (action === 'delete') {
                    title = 'Delete Narrative Entry';
                    iconClass = 'fa-trash-alt text-danger';
                }
                
                // Create HTML for data table view with search and filter functionality
                let html = `
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <div class="d-flex align-items-center">
                            <i class="fas ${iconClass} me-2" style="font-size: 1.25rem;"></i>
                            <h5 class="modal-title">${title}</h5>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="narrativeSearchInput" placeholder="Search by title or campus..." aria-label="Search">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="narrativeYearFilter">
                                        <option value="">All Years</option>
                                        ${getUniqueYears()}
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-secondary w-100" onclick="resetNarrativeFilters()">
                                        <i class="fas fa-undo-alt me-1"></i> Reset Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover table-striped border">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Title</th>
                                        <th>Campus</th>
                                        <th>Year</th>
                                        <th>Date Created</th>
                                    </tr>
                                </thead>
                                <tbody id="narrativeTableBody">
                `;
                
                narrativeData.forEach(narrative => {
                    const date = new Date(narrative.created_at).toLocaleDateString();
                    html += `
                        <tr class="narrative-row" data-id="${narrative.id}" data-action="${action}" data-title="${narrative.title || ''}" data-campus="${narrative.campus || ''}" data-year="${narrative.year || ''}" style="cursor: pointer;">
                            <td><strong>${narrative.title || 'Untitled'}</strong></td>
                            <td>${narrative.campus || ''}</td>
                            <td>${narrative.year || ''}</td>
                            <td>${date}</td>
                        </tr>
                    `;
                });
                
                html += `
                                </tbody>
                            </table>
                        </div>
                        <div id="noNarrativesMessage" class="text-center py-4 d-none">
                            <i class="fas fa-search fa-2x mb-2 text-muted"></i>
                            <p>No matching narratives found</p>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <div class="small text-muted me-auto">Click on a row to ${action} the narrative</div>
                        <button type="button" class="btn btn-secondary" onclick="Swal.close()">Close</button>
                    </div>
                </div>
                `;
                
                Swal.fire({
                    title: null,
                    html: html,
                    width: '80%',
                    showConfirmButton: false,
                    showCloseButton: false,
                    customClass: {
                        popup: 'swal-wide-modal'
                    },
                    didOpen: () => {
                        // Add search functionality
                        document.getElementById('narrativeSearchInput').addEventListener('input', filterNarrativeTable);
                        document.getElementById('narrativeYearFilter').addEventListener('change', filterNarrativeTable);
                        
                        // Add click event listeners to rows
                        document.querySelectorAll('.narrative-row').forEach(row => {
                            row.addEventListener('click', function() {
                                const narrativeId = this.getAttribute('data-id');
                                const actionType = this.getAttribute('data-action');
                                
                                Swal.close(); // Close the list modal
                                
                                // Set current narrative ID
                                currentNarrativeId = narrativeId;
                                narrativeIdField.value = narrativeId;
                                window.currentNarrativeId = narrativeId;
                                
                                // Handle different actions
                                if (actionType === 'view') {
                                    showNarrativeDetails(narrativeId);
                                } else if (actionType === 'edit') {
                                    // Enter edit mode
                                    isEditing = true;
                                    editBtn.innerHTML = '<i class="fas fa-times"></i>';
                                    editBtn.classList.add('editing');
                                    editBtn.title = 'Cancel editing';
                                    
                                    // Update add button to show it's for saving now
                                    addBtn.innerHTML = '<i class="fas fa-save"></i>';
                                    addBtn.title = 'Save changes';
                                    addBtn.classList.add('btn-update');
                                    
                                    // Load narrative data into form
                                    loadNarrativeForEdit(narrativeId);
                                } else if (actionType === 'delete') {
                                    // Call the function to show the delete confirmation
                                    deleteNarrative();
                                }
                            });
                        });
                    }
                });
                
                // Define the filter function
                window.filterNarrativeTable = function() {
                    const searchInput = document.getElementById('narrativeSearchInput').value.toLowerCase();
                    const yearFilter = document.getElementById('narrativeYearFilter').value;
                    const rows = document.querySelectorAll('#narrativeTableBody tr');
                    let visibleCount = 0;
                    
                    rows.forEach(row => {
                        const title = row.getAttribute('data-title').toLowerCase();
                        const campus = row.getAttribute('data-campus').toLowerCase();
                        const year = row.getAttribute('data-year');
                        
                        const matchesSearch = title.includes(searchInput) || campus.includes(searchInput);
                        const matchesYear = yearFilter === '' || year === yearFilter;
                        
                        if (matchesSearch && matchesYear) {
                            row.classList.remove('d-none');
                            visibleCount++;
                        } else {
                            row.classList.add('d-none');
                        }
                    });
                    
                    // Show/hide no results message
                    const noResultsMsg = document.getElementById('noNarrativesMessage');
                    if (visibleCount === 0) {
                        noResultsMsg.classList.remove('d-none');
                    } else {
                        noResultsMsg.classList.add('d-none');
                    }
                };
                
                // Define reset function
                window.resetNarrativeFilters = function() {
                    document.getElementById('narrativeSearchInput').value = '';
                    document.getElementById('narrativeYearFilter').value = '';
                    filterNarrativeTable();
                };
            }

            // Helper function to get unique years for filter dropdown
            function getUniqueYears() {
                const years = [];
                narrativeData.forEach(narrative => {
                    if (narrative.year && !years.includes(narrative.year)) {
                        years.push(narrative.year);
                    }
                });
                
                return years.sort().map(year => `<option value="${year}">${year}</option>`).join('');
            }

            // Function to show narrative details
            function showNarrativeDetails(narrativeId) {
                // Find the narrative in our data
                const saveStatus = document.getElementById('save-status');
                saveStatus.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Loading details...';
                
                $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: { 
                        action: 'get_single',
                        id: narrativeId
                    },
                    dataType: 'json',
                    success: function(response) {
                        saveStatus.innerHTML = '';
                        
                        if (response.success) {
                            const narrative = response.data;
                            
                            // Build the HTML for the details view
                            let html = `
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-alt me-2"></i>
                                        <h5 class="modal-title">Narrative Details</h5>
                                    </div>
                                </div>
                                <div class="modal-body">
                                    <div class="narrative-details">
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Basic Information</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-2">
                                                    <div class="col-md-4">
                                                        <div class="fw-bold text-muted">Campus:</div>
                                                        <div>${narrative.campus || 'N/A'}</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="fw-bold text-muted">Year:</div>
                                                        <div>${narrative.year || 'N/A'}</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="fw-bold text-muted">Activity:</div>
                                                        <div>${narrative.title || 'N/A'}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Narrative Content</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <h6 class="fw-bold text-primary">Background/Rationale:</h6>
                                                    <p>${narrative.background || 'N/A'}</p>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <h6 class="fw-bold text-primary">Description of Participants:</h6>
                                                    <p>${narrative.participants || 'N/A'}</p>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <h6 class="fw-bold text-primary">Topics Discussed:</h6>
                                                    <p>${narrative.topics || 'N/A'}</p>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <h6 class="fw-bold text-primary">Results:</h6>
                                                    <p>${narrative.results || 'N/A'}</p>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <h6 class="fw-bold text-primary">Lessons Learned:</h6>
                                                    <p>${narrative.lessons || 'N/A'}</p>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <h6 class="fw-bold text-primary">What Worked and Did Not Work:</h6>
                                                    <p>${narrative.what_worked || 'N/A'}</p>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <h6 class="fw-bold text-primary">Issues and Concerns:</h6>
                                                    <p>${narrative.issues || 'N/A'}</p>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <h6 class="fw-bold text-primary">Recommendations:</h6>
                                                    <p>${narrative.recommendations || 'N/A'}</p>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <h6 class="fw-bold text-primary">Evaluation Results:</h6>
                                                    <p>${narrative.evaluation || 'N/A'}</p>
                                                </div>
                                            </div>
                                        </div>
                `;
                
                // Add photos if available
                const photoArray = narrative.photo_paths || [];
                if (narrative.photo_path && !photoArray.includes(narrative.photo_path)) {
                    photoArray.push(narrative.photo_path);
                }
                
                if (photoArray.length > 0) {
                    html += `
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Photo Documentation</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                    `;
                    
                    photoArray.forEach(photo => {
                        // Ensure the photo path is complete
                        let displayPath = photo;
                        if (!photo.includes('/') && !photo.includes('\\')) {
                            displayPath = 'photos/' + photo;
                        }
                        
                        html += `
                            <div class="col-md-4 mb-3">
                                <a href="${displayPath}" target="_blank" class="d-block">
                                    <img src="${displayPath}" alt="Photo documentation" class="img-fluid img-thumbnail" style="height: 200px; width: 100%; object-fit: cover;">
                                </a>
                            </div>
                        `;
                    });
                    
                    html += `
                                </div>
                                <div class="mt-3">
                                    <h6 class="fw-bold text-primary">Caption:</h6>
                                    <p>${narrative.photo_caption || ''}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                html += `
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Additional Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="fw-bold text-muted">PS Attribution:</div>
                                            <div>${narrative.ps_attribution || 'N/A'}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="fw-bold text-muted">Gender Issue:</div>
                                            <div>${narrative.gender_issue || 'N/A'}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <div class="small text-muted me-auto">Created: ${new Date(narrative.created_at).toLocaleString()}</div>
                        <button type="button" class="btn btn-primary" onclick="Swal.close()">Close</button>
                    </div>
                </div>
                `;
                
                // Show the details in a modal
                Swal.fire({
                    title: null,
                    html: html,
                    width: '80%',
                    showConfirmButton: false,
                    showCloseButton: false,
                    customClass: {
                        popup: 'swal-wide-modal'
                    }
                });
            }

            // Function to delete narrative
            function deleteNarrative() {
                if (!currentNarrativeId) return;
                
                // First fetch the narrative details to show in the confirmation
                $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: { 
                        action: 'get_single',
                        id: currentNarrativeId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const narrative = response.data;
                            
                            // Create a more detailed confirmation modal
                            Swal.fire({
                                title: null,
                                html: `
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <h5 class="modal-title">Confirm Deletion</h5>
                                        </div>
                                    </div>
                                    <div class="modal-body">
                                        <div class="text-center mb-4">
                                            <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                                            <h5>Are you sure you want to delete this narrative?</h5>
                                            <p class="text-muted">This action cannot be undone.</p>
                                        </div>
                                        
                                        <div class="card border-danger">
                                            <div class="card-header bg-danger bg-opacity-10">
                                                <h6 class="mb-0 text-danger">Narrative Details</h6>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th class="bg-light" style="width: 30%;">Campus</th>
                                                        <td>${narrative.campus || 'N/A'}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">Year</th>
                                                        <td>${narrative.year || 'N/A'}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">Activity</th>
                                                        <td>${narrative.title || 'N/A'}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">Date Created</th>
                                                        <td>${new Date(narrative.created_at).toLocaleString() || 'N/A'}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary" onclick="Swal.clickCancel()">Cancel</button>
                                        <button type="button" class="btn btn-danger" onclick="Swal.clickConfirm()">
                                            <i class="fas fa-trash-alt me-1"></i> Delete Permanently
                                        </button>
                                    </div>
                                </div>
                                `,
                                showCancelButton: false,
                                showConfirmButton: false,
                                width: '50%',
                                customClass: {
                                    popup: 'swal-wide-modal'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Show loading state
                                    const saveStatus = document.getElementById('save-status');
                                    saveStatus.innerHTML = '<div class="spinner-border spinner-border-sm text-danger" role="status"><span class="visually-hidden">Deleting...</span></div> Deleting...';
                                    
                                    // Send deletion request
                                    $.ajax({
                                        url: 'narrative_handler.php',
                                        type: 'POST',
                                        data: {
                                            action: 'delete',
                                            id: currentNarrativeId
                                        },
                                        dataType: 'json',
                                        success: function(response) {
                                            // Clear loading indicator
                                            saveStatus.innerHTML = '';
                                            
                                            if (response.success) {
                                                // Reset form and state
                                                resetForm();
                                                
                                                // Show success message
                                                Swal.fire({
                                                    icon: 'success',
                                                    title: 'Deleted!',
                                                    text: 'The narrative has been deleted successfully.',
                                                    toast: true,
                                                    position: 'top-end',
                                                    showConfirmButton: false,
                                                    timer: 3000
                                                });
                                                
                                                // Reload narratives list
                                                loadNarratives();
                        } else {
                                                saveStatus.innerHTML = '<span class="text-danger">Delete failed</span>';
                                                setTimeout(() => { saveStatus.innerHTML = ''; }, 3000);
                                                
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                                    text: response.message || 'Failed to delete narrative',
                                                    toast: true,
                                                    position: 'top-end',
                                                    showConfirmButton: false,
                                                    timer: 5000
                            });
                        }
                    },
                                        error: function(xhr) {
                                            saveStatus.innerHTML = '<span class="text-danger">Server error</span>';
                                            setTimeout(() => { saveStatus.innerHTML = ''; }, 3000);
                                            
                                            console.error("AJAX Error:", xhr.responseText);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                                                text: 'Failed to connect to the server. Please try again later.',
                                                toast: true,
                                                position: 'top-end',
                                                showConfirmButton: false,
                                                timer: 5000
                        });
                    }
                });
                                }
                            });
                        } else {
                            // Show error message if we couldn't fetch narrative details
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Could not fetch narrative details',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error("AJAX Error:", xhr.responseText);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Failed to connect to the server. Please try again later.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
            }

            // Function to reset form and state
            function resetForm() {
                form.reset();
                currentNarrativeId = null;
                window.currentNarrativeId = null;
                narrativeIdField.value = '0';
                
                // Reset buttons
                editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                editBtn.classList.remove('editing');
                
                addBtn.innerHTML = '<i class="fas fa-plus"></i>';
                addBtn.classList.remove('btn-update');
                
                // Keep delete button enabled
                deleteBtn.classList.remove('btn-disabled');
                
                // Clear photo previews
                document.getElementById('photoPreviewContainer').innerHTML = '';
                
                // For non-central users, restore their campus
                if (!isCentral && document.getElementById('campus_override')) {
                    const campusValue = document.getElementById('campus_override').value;
                    document.getElementById('campus').value = campusValue;
                    
                    // Reload years based on campus
                    loadYearsForCampus(campusValue);
                } else {
                    // For central users, just reset the dropdowns
                    const campusSelect = document.getElementById('campus');
                    if (campusSelect.options.length > 0) {
                        campusSelect.selectedIndex = 0;
                    }
                    
                    const yearSelect = document.getElementById('year');
                    if (yearSelect.options.length > 0) {
                        yearSelect.selectedIndex = 0;
                    }
                    
                    const titleSelect = document.getElementById('title');
                    if (titleSelect.options.length > 0) {
                        titleSelect.selectedIndex = 0;
                    }
                }
                
                // Clear any status messages
                const saveStatus = document.getElementById('save-status');
                if (saveStatus) {
                    saveStatus.innerHTML = '';
                }
            }
            
            // Helper function to load activities with a callback
            function loadActivitiesForCampusAndYear(callback) {
                const campus = document.getElementById('campus').value;
                const year = document.getElementById('year').value;
                
                // Only proceed if both campus and year are selected
                if (!campus || !year) {
                    if (typeof callback === 'function') callback();
                    return;
                }
                
                // Load activities filtered by campus and year
                $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: { 
                        action: 'get_titles_from_ppas',
                        campus: campus,
                        year: year
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const titleSelect = document.getElementById('title');
                            
                            // Remove existing event listener first to avoid duplicates
                            titleSelect.removeEventListener('change', loadActivityDetails);
                            
                            // Clear existing options except the first one
                            while (titleSelect.options.length > 1) {
                                titleSelect.remove(1);
                            }
                            
                            // Add new options
                            response.data.forEach(title => {
                                const option = document.createElement('option');
                                option.value = title;
                                option.textContent = title;
                                titleSelect.appendChild(option);
                            });
                            
                            // Add change event listener to title dropdown
                            titleSelect.addEventListener('change', loadActivityDetails);
                            
                            if (typeof callback === 'function') callback();
                        } else {
                            console.error("Error loading activities: " + response.message);
                            if (typeof callback === 'function') callback();
                        }
                    },
                    error: function(xhr) {
                        console.error("AJAX Error:", xhr.responseText);
                        if (typeof callback === 'function') callback();
                    }
                });
            }

            // Load dropdown options
            function loadDropdownOptions() {
                // Load campuses
                $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: { action: 'get_campuses' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const campusSelect = document.getElementById('campus');
                            
                            // Clear existing options except the first one
                            while (campusSelect.options.length > 1) {
                                campusSelect.remove(1);
                            }
                            
                            // Add new options
                            response.data.forEach(campus => {
                                const option = document.createElement('option');
                                option.value = campus;
                                option.textContent = campus;
                                campusSelect.appendChild(option);
                            });
                            
                            // If only one campus is available, select it
                            if (response.data.length === 1) {
                                campusSelect.value = response.data[0];
                                
                                // Load years for the selected campus
                                loadYearsForCampus(campusSelect.value);
                            } else {
                                // If no campus is selected yet, load all years
                                loadYearsForCampus('');
                            }
                        } else {
                            console.error("Error loading campuses: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error (campuses): " + status + " - " + error);
                        console.log("Response Text: " + xhr.responseText);
                    }
                });
                
                // Add event listeners to campus and year dropdowns
                document.getElementById('campus').addEventListener('change', function() {
                    loadYearsForCampus(this.value);
                });
                
                document.getElementById('year').addEventListener('change', function() {
                    loadActivitiesForCampusAndYear();
                });
            }
            
            // Function to load years based on selected campus
            function loadYearsForCampus(campus, callback) {
                $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: { 
                        action: 'get_years_from_ppas',
                        campus: campus
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const yearSelect = document.getElementById('year');
                            
                            // Clear existing options except the first one
                            while (yearSelect.options.length > 1) {
                                yearSelect.remove(1);
                            }
                            
                            // Add year options
                            response.data.forEach(year => {
                                const option = document.createElement('option');
                                option.value = year;
                                option.textContent = year;
                                yearSelect.appendChild(option);
                            });
                            
                            // If there are years available, select the first one and load activities
                            if (yearSelect.options.length > 1) {
                                yearSelect.selectedIndex = 1;
                                
                                // If we're not in edit mode (no callback), load activities
                                if (!callback) {
                                    loadActivitiesForCampusAndYear();
                                }
                            } else {
                                // If no years are available, reset the activities dropdown
                                const titleSelect = document.getElementById('title');
                                while (titleSelect.options.length > 1) {
                                    titleSelect.remove(1);
                                }
                                titleSelect.selectedIndex = 0;
                            }
                            
                            // Execute callback if provided
                            if (typeof callback === 'function') {
                                callback();
                            }
                        } else {
                            console.error("Error loading years: " + response.message);
                            
                            // Execute callback if provided, even on error
                            if (typeof callback === 'function') {
                                callback();
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error (years): " + status + " - " + error);
                        console.log("Response Text: " + xhr.responseText);
                        
                        // Execute callback if provided, even on error
                        if (typeof callback === 'function') {
                            callback();
                        }
                    }
                });
            }
            
            // Function to load activities based on selected campus and year
            function loadActivitiesForCampusAndYear() {
                const campus = document.getElementById('campus').value;
                const year = document.getElementById('year').value;
                
                // Only proceed if both campus and year are selected
                if (!campus || !year) {
                    return;
                }
                
                // Load activities filtered by campus and year
                $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: { 
                        action: 'get_titles_from_ppas',
                        campus: campus,
                        year: year
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const titleSelect = document.getElementById('title');
                            
                            // Remove existing event listener first to avoid duplicates
                            titleSelect.removeEventListener('change', loadActivityDetails);
                            
                            // Clear existing options except the first one
                            while (titleSelect.options.length > 1) {
                                titleSelect.remove(1);
                            }
                            
                            // Add new options
                            response.data.forEach(title => {
                                const option = document.createElement('option');
                                option.value = title;
                                option.textContent = title;
                                titleSelect.appendChild(option);
                            });
                            
                            // Display message if no activities found
                            if (response.data.length === 0) {
                                console.log("No activities found for the selected campus and year");
                                // Reset title dropdown
                                titleSelect.selectedIndex = 0;
                            }
                            
                            // Add change event listener to title dropdown
                            titleSelect.addEventListener('change', loadActivityDetails);
                        } else {
                            console.error("Error loading activities: " + response.message);
                            // Show error in a small notification
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning',
                                text: 'Could not load activities for the selected campus and year',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error (activities): " + status + " - " + error);
                        console.log("Response Text: " + xhr.responseText);
                    }
                });
            }
            
            // Function to load PS attribution and gender issue based on selected activity
            function loadActivityDetails() {
                const activity = document.getElementById('title').value;
                
                if (!activity) {
                    return;
                }
                
                $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: { 
                        action: 'get_activity_details',
                        activity: activity
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Populate PS attribution and gender issue fields
                            document.getElementById('psAttribution').value = response.data.ps_attribution || '';
                            document.getElementById('genderIssue').value = response.data.gender_issue || '';
                        } else {
                            console.error("Error loading activity details: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error (activity details): " + status + " - " + error);
                        console.log("Response Text: " + xhr.responseText);
                    }
                });
            }\n        }\n    </script>
    <script src="../js/approval-badge.js"></script>
</body>
</html>

