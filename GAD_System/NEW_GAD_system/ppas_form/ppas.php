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
    <title>Main PPAS Forms - GAD System</title>
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
    <!-- Select2 for multi-select dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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

/* Dark mode non-interactible fields */
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

/* Interactible fields in dark mode */
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

/* Styling for personnel and participants sections */
.personnel-container, .participants-container {
    background: var(--bg-secondary);
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
}

.personnel-container.compact {
    padding: 10px;
    margin-bottom: 10px;
}

.personnel-container .form-row, 
.participants-container .form-row {
    margin-bottom: 10px;
}

.personnel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    font-weight: 600;
    color: var(--accent-color);
}

.btn-add-personnel {
    display: inline-flex;
    align-items: center;
    background: rgba(106, 27, 154, 0.1);
    color: var(--accent-color);
    border: 1px dashed var(--accent-color);
    border-radius: 8px;
    padding: 5px 12px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-add-personnel:hover {
    background: var(--accent-color);
    color: white;
    border-color: var(--accent-color);
}

.info-text {
    display: block;
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-top: 5px;
}

/* Time input container */
.time-input-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.time-input-container .form-control {
    width: auto;
    flex: 1;
}

/* Multi-select dropdown for SDGs */
.select2-container--bootstrap-5 .select2-selection {
    border-radius: 8px;
    border: 1px solid var(--border-color);
    background-color: var(--input-bg);
    color: var(--text-primary);
    transition: border-color 0.2s ease;
    min-height: 38px;
    padding: 2px 0;
    height: calc(1.5em + 0.75rem + 2px);
}

.select2-container--bootstrap-5.select2-container--focus .select2-selection {
    border-color: var(--accent-color);
    box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25);
}

[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection {
    background-color: #2B3035;
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-dropdown {
    background-color: #2B3035;
    border-color: var(--dark-border);
}

[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option {
    color: var(--dark-text);
}

[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection__choice {
    background-color: var(--accent-color);
    color: white;
    border-color: var(--accent-color);
}

[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection__rendered {
    color: var(--dark-text);
}

[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted {
    background-color: var(--accent-color);
    color: white;
}

/* Form field labels and controls from gbp.php */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--text-primary);
}

/* Existing form control styles */
.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid var(--border-color);
    background-color: var(--input-bg);
    color: var(--text-primary);
    transition: border-color 0.2s ease;
    padding: 10px 12px;
    height: auto;
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

.form-control.is-invalid,
.form-select.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
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

.dark-selection-choice {
    background-color: var(--accent-color) !important;
    color: white !important;
    border-color: var(--accent-color) !important;
}

/* Replace all existing SDG selection style blocks with these unified styles */
.select2-container--bootstrap-5 .select2-selection--multiple {
    min-height: 42px !important;
    padding: 5px 8px !important;
    height: auto !important;
    overflow: auto !important;
    max-height: 150px !important;
}

.select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
    display: flex !important;
    flex-wrap: wrap !important;
    width: 100% !important;
}

.sdg-selection .select2-selection__choice {
    background-color: var(--accent-color) !important;
    border-color: var(--accent-color) !important;
    color: white !important;
    padding: 5px 10px !important;
    margin-right: 5px !important;
    margin-bottom: 5px !important;
    border-radius: 4px !important;
    display: flex !important;
    align-items: center !important;
}

.sdg-selection .select2-selection__choice__remove {
    color: white !important;
    font-weight: bold !important;
    margin-right: 5px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border-radius: 50% !important;
    width: 18px !important;
    height: 18px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
}

.sdg-selection .select2-selection__choice__remove:hover {
    color: white !important;
    background-color: rgba(0, 0, 0, 0.4) !important;
}

[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-dropdown {
    background-color: #2b3035 !important;
    color: white !important;
}

[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-dropdown .select2-results__option {
    color: white !important;
}

[data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-dropdown .select2-results__option--highlighted {
    background-color: var(--accent-color) !important;
}

/* SDG selector styling */
.select2-container--bootstrap-5 .select2-selection--multiple {
    min-height: 42px !important;
    padding: 6px 8px !important;
}

.sdg-selection .select2-selection__choice {
    background-color: var(--accent-color) !important;
    }

.sdg-selection .select2-selection__choice__remove:hover {
    color: white !important;
    background-color: rgba(0, 0, 0, 0.4) !important;
}

/* Add missing closing brace for the style tag */

        /* Add styling for disabled form elements in dark mode */
        [data-bs-theme="dark"] .form-select:disabled,
        [data-bs-theme="dark"] .form-control:disabled {
            background-color: #37383A !important;
            color: rgba(255, 255, 255, 0.5) !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
        }

        /* Regular styling for disabled form elements */
        .form-select:disabled,
        .form-control:disabled {
            cursor: not-allowed;
        }

        .form-nav-item.completed {
            color: #198754 !important;
            /* Make the completed state more visible */
            font-weight: bold;
        }

        .form-nav-item.completed .step-number {
            background-color: #198754 !important;
            /* Add a subtle shadow */
            box-shadow: 0 0 8px rgba(25, 135, 84, 0.5);
        }

        .form-nav-item.active.completed {
            color: #198754 !important;
        }

        /* Add CSS for completed section header */
        .section-title.completed {
            color: #198754 !important;
            /* Remove the border-bottom-color property to keep the original underline */
        }

        /* Validation styles for form tabs */
        .form-nav-item.invalid {
            color: #dc3545 !important;
        }
        
        /* Style for invalid form fields */
        .form-control.is-invalid {
            border-color: #dc3545 !important;
        }
        
        /* Ensure currency inputs show proper invalid styling */
        .input-with-currency .form-control.is-invalid {
            border-color: #dc3545 !important;
        }
        
        .input-with-currency.is-invalid {
            border-color: #dc3545 !important;
        }

        .form-nav-item.invalid .step-number {
            background-color: #dc3545 !important;
        }

        /* Validation styles for form fields */
        .form-control.is-invalid, 
        .form-select.is-invalid {
            border-color: #dc3545 !important;
            padding-right: calc(1.5em + 0.75rem) !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right calc(0.375em + 0.1875rem) center !important;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem) !important;
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

        /* This is debug styling to make completed section titles more obvious */
        .section-title.completed {
            color: #198754 !important;
            border-bottom: 2px solid #198754 !important;
        }
        
        /* Add a border transition */
        .section-title {
            transition: all 0.3s ease;
        }

        /* Invalid form styles */
        .form-nav-item.invalid {
            color: #dc3545 !important; /* Red color for invalid */
        }
        
        .form-nav-item.invalid .step-number {
            background-color: #dc3545 !important;
        }
        
        /* Ensure completed state takes precedence over invalid when both classes exist */
        .form-nav-item.completed.invalid {
            color: #198754 !important; /* Green takes priority */
        }
        
        .form-nav-item.completed.invalid .step-number {
            background-color: #198754 !important; /* Green takes priority */
}

        /* Autocomplete styles */
        .autocomplete-items {
            position: absolute;
            border: 1px solid var(--bs-border-color);
            border-bottom: none;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 300px;
            overflow-y: auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            display: none;
        }
        
        .autocomplete-items div {
            padding: 10px;
            cursor: pointer;
            background-color: var(--bs-body-bg);
            color: var(--accent-color);
            border-bottom: 1px solid var(--bs-border-color);
        }
        
        .autocomplete-items div:hover {
            background-color: var(--bs-tertiary-bg);
        }
        
        .autocomplete-active {
            background-color: var(--accent-color) !important;
            color: #fff !important;
        }
        
        .form-group {
            position: relative;
        }

        /* Make sure invalid feedback is always visible */
        .invalid-feedback.d-block {
            display: block !important;
            margin-top: 0.25rem;
            font-size: 80%;
            color: #dc3545;
        }
        
        /* Make the is-invalid class more noticeable */
        .form-control.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
        }
        
        /* Validation loading state */
        .form-control.is-validating {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24'%3E%3Cpath fill='%23007bff' d='M12,4V2A10,10 0 0,0 2,12H4A8,8 0 0,1 12,4Z'%3E%3CanimateTransform attributeName='transform' type='rotate' from='0 12 12' to='360 12 12' dur='1s' repeatCount='indefinite'/%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            padding-right: calc(1.5em + 0.75rem) !important;
        }
        
        /* Override Bootstrap's default invalid feedback style */
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 80%;
            color: #dc3545;
        }
        
        /* Make invalid feedback visible when the field is invalid */
        .form-control.is-invalid + .invalid-feedback {
            display: block !important;
}
        
        /* Styling for the sequential form field dependencies */
        .disabled-field {
            background-color: #f8f9fa !important;
            cursor: not-allowed !important;
            color: #6c757d !important;
            border-color: #dee2e6 !important;
            opacity: 0.7 !important;
        }
        
        [data-bs-theme="dark"] .disabled-field {
            background-color: #343a40 !important;
            color: #adb5bd !important;
            border-color: #495057 !important;
        }
        
        .field-disabled label {
            color: #6c757d !important;
        }
        
        [data-bs-theme="dark"] .field-disabled label {
            color: #adb5bd !important;
        }
        
        .field-disabled .form-control::placeholder {
            color: #adb5bd !important;
            font-style: italic;
        }
        
        /* Add a subtle info icon to indicate dependency */
        .field-disabled::before {
            content: ""; /* Remove Font Awesome icon */
            display: none; /* Hide the pseudo-element completely */
        }
        
        /* Show visual indication of disabled state through other means */
        .field-disabled {
            position: relative;
            opacity: 0.9;
        }
        
        /* Add order indicator numbers to show sequential relationship */
        .form-group {
            position: relative;
        }
        
        /* Add a sequence hint before the label */
        label[for="year"]::before {
            content: "1. ";
            font-weight: bold;
        }
        
        label[for="quarter"]::before {
            content: "2. ";
            font-weight: bold;
        }
        
        label[for="gender_issue"]::before {
            content: "3. ";
            font-weight: bold;
        }
        
        label[for="program"]::before {
            content: "4. ";
            font-weight: bold;
        }
        
        label[for="project"]::before {
            content: "5. ";
            font-weight: bold;
        }
        
        label[for="activity"]::before {
            content: "6. ";
            font-weight: bold;
        }
        
        /* Add a hint below disabled fields */
        .field-disabled::after {
            content: attr(data-hint);
            display: block;
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 5px;
            font-style: italic;
        }
        
        /* Improve visual indication of the sequence */
        .form-control.disabled-field {
            transition: all 0.3s ease;
            box-shadow: none !important;
        }

        /* PPAS Data Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .ppas-data-modal {
            width: 90%;
            max-width: 1200px;
            height: 85vh; /* Use viewport height instead of fixed pixels */
            max-height: 800px;
            background-color: var(--card-bg);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-top: 5px solid var(--accent-color);
            transform: translateY(20px);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        
        .modal-overlay.active .ppas-data-modal {
            transform: translateY(0);
            opacity: 1;
        }
        
        .ppas-data-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            flex-shrink: 0; /* Prevent header from shrinking */
        }
        
        .ppas-data-modal-header h3 {
            margin: 0;
            color: var(--accent-color);
            font-size: 1.25rem;
            text-align: center;
            flex-grow: 1; /* Allow title to take up space */
        }
        
        .close-modal-btn {
            background: transparent;
            border: none;
            color: var(--text-primary);
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.2s;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .close-modal-btn:hover {
            background-color: rgba(0, 0, 0, 0.1);
            color: var(--accent-color);
        }

        /* Styles for different modal modes */
        .ppas-data-table-wrapper.edit-mode tr {
            cursor: pointer;
        }

        .ppas-data-table-wrapper.edit-mode tr:hover {
            background-color: rgba(106, 27, 154, 0.1) !important;
        }

        .ppas-data-table-wrapper.delete-mode tr {
            cursor: pointer;
        }

        .ppas-data-table-wrapper.delete-mode tr:hover {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        /* Style for cancel button in edit mode */
        .btn-cancel-edit {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
        }

        .btn-cancel-edit:hover {
            background-color: #bd2130 !important;
            border-color: #bd2130 !important;
        }
        
        /* Style for danger button (cancel/delete) */
        .btn-danger {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
        }

        .btn-danger:hover {
            background-color: #bd2130 !important;
            border-color: #bd2130 !important;
        }
        
        .ppas-data-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1; /* Take up remaining space */
            min-height: 200px; /* Ensure minimum height */
        }
        
        .ppas-data-modal-footer {
            padding: 1rem;
            border-top: 1px solid var(--border-color);
            background-color: var(--card-bg);
            border-radius: 0 0 15px 15px;
            flex-shrink: 0; /* Prevent footer from shrinking */
        }
        
        .filter-container {
            margin-bottom: 1.5rem;
            background-color: var(--bg-secondary);
            padding: 1rem;
            border-radius: 10px;
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .filter-item {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-item label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .ppas-data-table-wrapper {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            margin-bottom: 0; /* Remove bottom margin since we have a footer now */
            height: calc(100% - 120px); /* Adjust height to account for filters */
            min-height: 150px;
        }
        
        .ppas-data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .ppas-data-table th, 
        .ppas-data-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .ppas-data-table th {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        
        .ppas-data-table tr:last-child td {
            border-bottom: none;
        }
        
        .ppas-data-table tr:hover td {
            background-color: var(--bg-secondary);
        }
        
        .pagination-container {
            display: flex;
            justify-content: center;
            margin: 0; /* Remove margin since it's in a dedicated footer now */
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
        
        .page-numbers-container {
            display: flex;
            gap: 0.25rem;
        }
        
        .page-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            transition: all 0.2s;
        }
        
        .page-number:hover {
            background-color: var(--accent-color);
            color: white;
        }
        
        .page-number.active {
            background-color: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }

        .swal-delete-container {
            z-index: 100000 !important; /* Higher than modal-overlay z-index */
        }

        .swal2-backdrop-show {
            z-index: 99999 !important; /* Higher than modal-overlay z-index */
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
                            <a class="dropdown-item dropdown-toggle" href="" id="ppasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                 PPAs Form
                            </a>
                            <ul class="dropdown-menu dropdown-submenu" aria-labelledby="ppasDropdown">
                                <li><a class="dropdown-item" href="">Main PPAs Form</a></li>
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
                        <li><a class="dropdown-item" href="../ppas_report/ppas_report.php">Quarterly PPAs Reports</a></li>
                        <li><a class="dropdown-item" href="../ps_atrib/ps.php">PSA Reports</a></li>
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
            <h2>PPAS Management</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Add Main PPAS Form</h5>
            </div>
            <div class="card-body">
                <form id="ppasForm">
                    <!-- Form Navigation -->
                    <div class="form-nav">
                        <div class="form-nav-item active" data-section="basic-info">
                            <span class="step-number">1</span>
                            <span class="step-text">Basic Info</span>
                        </div>
                        <div class="form-nav-item" data-section="location-date">
                            <span class="step-number">2</span>
                            <span class="step-text">Location & Date</span>
                        </div>
                        <div class="form-nav-item" data-section="personnel">
                            <span class="step-number">3</span>
                            <span class="step-text">Personnel</span>
                        </div>
                        <div class="form-nav-item" data-section="beneficiaries">
                            <span class="step-number">4</span>
                            <span class="step-text">Beneficiaries</span>
                        </div>
                        <div class="form-nav-item" data-section="budget-sdgs">
                            <span class="step-number">5</span>
                            <span class="step-text">Budget & SDGs</span>
                        </div>
                    </div>

                    <!-- Form Sections -->
                    <div class="form-sections-container">
                        <!-- Section 1: Basic Info -->
                        <div class="form-section active" id="basic-info">
                            <h6 class="section-title"><i class="fas fa-info-circle me-2"></i> Basic Information</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="campus" class="form-label">Campus</label>
                                        <input type="text" class="form-control" id="campus" name="campus" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="year" class="form-label">Year</label>
                                        <select class="form-select" id="year" name="year" required>
                                            <option value="" selected disabled>Select Year</option>
                                            <!-- Years will be populated dynamically -->
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="quarter" class="form-label">Quarter</label>
                                        <select class="form-select" id="quarter" name="quarter" required>
                                            <option value="" selected disabled>Select Quarter</option>
                                            <option value="Q1">Q1</option>
                                            <option value="Q2">Q2</option>
                                            <option value="Q3">Q3</option>
                                            <option value="Q4">Q4</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="gender_issue" class="form-label">Gender Issue</label>
                                        <select class="form-select" id="gender_issue" name="gender_issue" required>
                                            <option value="" selected disabled>Select Gender Issue</option>
                                            <!-- Gender issues will be populated dynamically -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="program" class="form-label">Program</label>
                                        <input type="text" class="form-control autocomplete" id="program" name="program" data-field="program" required>
                                        <div class="autocomplete-items" id="program-autocomplete"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="project" class="form-label">Project</label>
                                        <input type="text" class="form-control autocomplete" id="project" name="project" data-field="project" required>
                                        <div class="autocomplete-items" id="project-autocomplete"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="activity" class="form-label">Activity</label>
                                        <input type="text" class="form-control" id="activity" name="activity" required data-validate-duplicate="true">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="action-buttons-container">
                                <div></div> <!-- Empty div for spacing -->
                                <button type="button" class="btn-form-nav" data-navigate-to="location-date">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Section 2: Location & Date -->
                        <div class="form-section" id="location-date">
                            <h6 class="section-title"><i class="fas fa-map-marker-alt me-2"></i> Location & Date</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="location" class="form-label">Location</label>
                                        <input type="text" class="form-control" id="location" name="location" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <div class="row g-2">
                                            <div class="col-3">
                                                <select class="form-select start-month" id="start_month" name="start_month" required>
                                                    <option value="" selected disabled>Month</option>
                                                    <option value="01">January</option>
                                                    <option value="02">February</option>
                                                    <option value="03">March</option>
                                                    <option value="04">April</option>
                                                    <option value="05">May</option>
                                                    <option value="06">June</option>
                                                    <option value="07">July</option>
                                                    <option value="08">August</option>
                                                    <option value="09">September</option>
                                                    <option value="10">October</option>
                                                    <option value="11">November</option>
                                                    <option value="12">December</option>
                                                </select>
                                            </div>
                                            <div class="col-2">
                                                <select class="form-select start-day" id="start_day" name="start_day" required>
                                                    <option value="" selected disabled>Day</option>
                                                    <!-- Days will be populated dynamically -->
                                                </select>
                                            </div>
                                            <div class="col-2">
                                                <select class="form-select start-year" id="start_year" name="start_year" required>
                                                    <option value="" selected disabled>Year</option>
                                                    <!-- Years will be populated dynamically -->
                                                </select>
                                            </div>
                                            <div class="col-5">
                                                <input type="date" class="form-control" id="start_date" name="start_date" style="display: none;">
                                                <input type="text" class="form-control" id="start_date_display" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <div class="row g-2">
                                            <div class="col-3">
                                                <select class="form-select end-month" id="end_month" name="end_month" required>
                                                    <option value="" selected disabled>Month</option>
                                                    <option value="01">January</option>
                                                    <option value="02">February</option>
                                                    <option value="03">March</option>
                                                    <option value="04">April</option>
                                                    <option value="05">May</option>
                                                    <option value="06">June</option>
                                                    <option value="07">July</option>
                                                    <option value="08">August</option>
                                                    <option value="09">September</option>
                                                    <option value="10">October</option>
                                                    <option value="11">November</option>
                                                    <option value="12">December</option>
                                                </select>
                                            </div>
                                            <div class="col-2">
                                                <select class="form-select end-day" id="end_day" name="end_day" required>
                                                    <option value="" selected disabled>Day</option>
                                                    <!-- Days will be populated dynamically -->
                                                </select>
                                            </div>
                                            <div class="col-2">
                                                <select class="form-select end-year" id="end_year" name="end_year" required>
                                                    <option value="" selected disabled>Year</option>
                                                    <!-- Years will be populated dynamically -->
                                                </select>
                                            </div>
                                            <div class="col-5">
                                                <input type="date" class="form-control" id="end_date" name="end_date" style="display: none;">
                                                <input type="text" class="form-control" id="end_date_display" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="start_time" class="form-label">Start Time</label>
                                        <input type="time" class="form-control" id="start_time" name="start_time" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="end_time" class="form-label">End Time</label>
                                        <input type="time" class="form-control" id="end_time" name="end_time" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="total_duration" class="form-label">Total Duration Hours</label>
                                        <input type="text" class="form-control" id="total_duration" name="total_duration" readonly>
                                        <small class="info-text">Auto-calculated</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">Lunch Break</label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="lunch_break" id="with_lunch" value="with" checked>
                                                <label class="form-check-label" for="with_lunch">With</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="lunch_break" id="no_lunch" value="without">
                                                <label class="form-check-label" for="no_lunch">Without</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="basic-info">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <button type="button" class="btn-form-nav" data-navigate-to="personnel">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Section 3: Personnel -->
                        <div class="form-section" id="personnel">
                            <h6 class="section-title"><i class="fas fa-users me-2"></i> Personnel Involved</h6>
                            
                            <!-- Project Leader -->
                            <div class="personnel-container mb-4">
                                <div class="personnel-header">
                                    <span>Project Leader</span>
                                </div>
                                <div id="project-leader-container">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="project_leader_name" class="form-label">Name</label>
                                                <select class="form-select" id="project_leader_name" name="project_leader_name" required>
                                                    <option value="" selected disabled>Select Personnel</option>
                                                    <!-- Personnel will be populated dynamically -->
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="project_leader_gender" class="form-label">Gender</label>
                                                <input type="text" class="form-control" id="project_leader_gender" name="project_leader_gender" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="project_leader_rank" class="form-label">Academic Rank</label>
                                                <input type="text" class="form-control" id="project_leader_rank" name="project_leader_rank" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="project_leader_salary" class="form-label">Monthly Salary</label>
                                                <input type="text" class="form-control" id="project_leader_salary" name="project_leader_salary" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="project_leader_rate" class="form-label">Rate per hour</label>
                                                <input type="text" class="form-control" id="project_leader_rate" name="project_leader_rate" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-add-personnel mt-3" data-type="project_leader" id="add-project-leader">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                            
                            <!-- Assistant Project Leader -->
                            <div class="personnel-container mb-4">
                                <div class="personnel-header">
                                    <span>Assistant Project Leader</span>
                                </div>
                                <div id="asst-project-leader-container">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="asst_leader_name" class="form-label">Name</label>
                                                <select class="form-select" id="asst_leader_name" name="asst_leader_name" required>
                                                    <option value="" selected disabled>Select Personnel</option>
                                                    <!-- Personnel will be populated dynamically -->
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="asst_leader_gender" class="form-label">Gender</label>
                                                <input type="text" class="form-control" id="asst_leader_gender" name="asst_leader_gender" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="asst_leader_rank" class="form-label">Academic Rank</label>
                                                <input type="text" class="form-control" id="asst_leader_rank" name="asst_leader_rank" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="asst_leader_salary" class="form-label">Monthly Salary</label>
                                                <input type="text" class="form-control" id="asst_leader_salary" name="asst_leader_salary" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="asst_leader_rate" class="form-label">Rate per hour</label>
                                                <input type="text" class="form-control" id="asst_leader_rate" name="asst_leader_rate" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-add-personnel mt-3" data-type="asst_leader" id="add-asst-leader">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                            
                            <!-- Project Staff/Coordinator -->
                            <div class="personnel-container mb-4">
                                <div class="personnel-header">
                                    <span>Project Staff/Coordinator</span>
                                </div>
                                <div id="staff-coordinator-container">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="staff_name" class="form-label">Name</label>
                                                <select class="form-select" id="staff_name" name="staff_name" required>
                                                    <option value="" selected disabled>Select Personnel</option>
                                                    <!-- Personnel will be populated dynamically -->
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="staff_gender" class="form-label">Gender</label>
                                                <input type="text" class="form-control" id="staff_gender" name="staff_gender" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="staff_rank" class="form-label">Academic Rank</label>
                                                <input type="text" class="form-control" id="staff_rank" name="staff_rank" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="staff_salary" class="form-label">Monthly Salary</label>
                                                <input type="text" class="form-control" id="staff_salary" name="staff_salary" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="staff_rate" class="form-label">Rate per hour</label>
                                                <input type="text" class="form-control" id="staff_rate" name="staff_rate" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-add-personnel mt-3" data-type="staff" id="add-staff">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                            
                            <!-- Other Internal Participants -->
                            <div class="personnel-container mb-4">
                                <div class="personnel-header">
                                    <span>Other Internal Participants</span>
                                </div>
                                <div id="other-participants-container">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="other_name" class="form-label">Name</label>
                                                <select class="form-select" id="other_name" name="other_name" required>
                                                    <option value="" selected disabled>Select Personnel</option>
                                                    <!-- Personnel will be populated dynamically -->
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="other_gender" class="form-label">Gender</label>
                                                <input type="text" class="form-control" id="other_gender" name="other_gender" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="other_rank" class="form-label">Academic Rank</label>
                                                <input type="text" class="form-control" id="other_rank" name="other_rank" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="other_salary" class="form-label">Monthly Salary</label>
                                                <input type="text" class="form-control" id="other_salary" name="other_salary" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="other_rate" class="form-label">Rate per hour</label>
                                                <input type="text" class="form-control" id="other_rate" name="other_rate" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-add-personnel mt-3" data-type="other" id="add-other-participant">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                            
                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="location-date">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <button type="button" class="btn-form-nav" data-navigate-to="beneficiaries">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Section 4: Beneficiaries -->
                        <div class="form-section" id="beneficiaries">
                            <h6 class="section-title"><i class="fas fa-users me-2"></i> Beneficiaries</h6>
                            
                            <!-- Internal Beneficiaries -->
                            <div class="mb-3">
                                <h6 class="mb-2">Internal</h6>
                                
                                <div class="personnel-container p-3 mb-0">
                                    <div class="row">
                                        <!-- Students Section -->
                                        <div class="col-md-6">
                                            <div class="personnel-header mb-2">
                                                <span>Students</span>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label for="students_male" class="form-label">Male</label>
                                                        <input type="number" class="form-control" id="students_male" name="students_male" min="0" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label for="students_female" class="form-label">Female</label>
                                                        <input type="number" class="form-control" id="students_female" name="students_female" min="0" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Faculty Section -->
                                        <div class="col-md-6">
                                            <div class="personnel-header mb-2">
                                                <span>Faculty</span>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label for="faculty_male" class="form-label">Male</label>
                                                        <input type="number" class="form-control" id="faculty_male" name="faculty_male" readonly>
                                                        <small class="info-text">Auto-calculated</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label for="faculty_female" class="form-label">Female</label>
                                                        <input type="number" class="form-control" id="faculty_female" name="faculty_female" readonly>
                                                        <small class="info-text">Auto-calculated</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Total Internal -->
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="personnel-header mb-2">
                                                <span>Total Internal</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label for="total_internal_male" class="form-label">Total Male</label>
                                                <input type="number" class="form-control" id="total_internal_male" name="total_internal_male" readonly>
                                                <small class="info-text">Auto-calculated</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label for="total_internal_female" class="form-label">Total Female</label>
                                                <input type="number" class="form-control" id="total_internal_female" name="total_internal_female" readonly>
                                                <small class="info-text">Auto-calculated</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- External Beneficiaries -->
                            <div class="mb-3">
                                <h6 class="mb-2">External</h6>
                                <div class="personnel-container">
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="external_type" class="form-label">Type</label>
                                                <input type="text" class="form-control" id="external_type" name="external_type" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="external_male" class="form-label">Male</label>
                                                <input type="number" class="form-control" id="external_male" name="external_male" min="0" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="external_female" class="form-label">Female</label>
                                                <input type="number" class="form-control" id="external_female" name="external_female" min="0" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Total Beneficiaries -->
                            <div class="mb-3">
                                <h6 class="mb-2">Total</h6>
                                <div class="personnel-container">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="total_male" class="form-label">Total Male</label>
                                                <input type="number" class="form-control" id="total_male" name="total_male" readonly>
                                                <small class="info-text">Auto-calculated</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="total_female" class="form-label">Total Female</label>
                                                <input type="number" class="form-control" id="total_female" name="total_female" readonly>
                                                <small class="info-text">Auto-calculated</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="total_beneficiaries" class="form-label">Total Beneficiaries</label>
                                                <input type="number" class="form-control" id="total_beneficiaries" name="total_beneficiaries" readonly>
                                                <small class="info-text">Auto-calculated</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="personnel">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <button type="button" class="btn-form-nav" data-navigate-to="budget-sdgs">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Section 5: Budget & SDGs -->
                        <div class="form-section" id="budget-sdgs">
                            <h6 class="section-title"><i class="fas fa-dollar-sign me-2"></i> Budget & SDGs</h6>
                            
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="approved_budget" class="form-label">Approved Budget</label>
                                        <div class="input-with-currency">
                                            <input type="number" class="form-control" id="approved_budget" name="approved_budget" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="source_budget" class="form-label">Source of Budget/Fund</label>
                                        <select class="form-select" id="source_budget" name="source_budget" required>
                                            <option value="" selected disabled>Select Source</option>
                                            <option value="MDS-GAD">MDS-GAD</option>
                                            <option value="STF">STF</option>
                                            <option value="RTF">RTF</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ps_attribution" class="form-label">PS Attribution</label>
                                        <div class="input-with-currency">
                                            <input type="text" class="form-control" id="ps_attribution" name="ps_attribution" readonly>
                                        </div>
                                        <small class="info-text">Auto-calculated from hourly rates</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="sdgs" class="form-label">Sustainable Development Goals (SDGs) <small class="text-muted">(Optional)</small></label>
                                        <select class="form-select" id="sdgs" name="sdgs[]" multiple>
                                            <option value="SDG 1 - No Poverty">SDG 1 - No Poverty</option>
                                            <option value="SDG 2 - Zero Hunger">SDG 2 - Zero Hunger</option>
                                            <option value="SDG 3 - Good Health and Well-being">SDG 3 - Good Health and Well-being</option>
                                            <option value="SDG 4 - Quality Education">SDG 4 - Quality Education</option>
                                            <option value="SDG 5 - Gender Equality">SDG 5 - Gender Equality</option>
                                            <option value="SDG 6 - Clean Water and Sanitation">SDG 6 - Clean Water and Sanitation</option>
                                            <option value="SDG 7 - Affordable and Clean Energy">SDG 7 - Affordable and Clean Energy</option>
                                            <option value="SDG 8 - Decent Work and Economic Growth">SDG 8 - Decent Work and Economic Growth</option>
                                            <option value="SDG 9 - Industry, Innovation, and Infrastructure">SDG 9 - Industry, Innovation, and Infrastructure</option>
                                            <option value="SDG 10 - Reduced Inequalities">SDG 10 - Reduced Inequalities</option>
                                            <option value="SDG 11 - Sustainable Cities and Communities">SDG 11 - Sustainable Cities and Communities</option>
                                            <option value="SDG 12 - Responsible Consumption and Production">SDG 12 - Responsible Consumption and Production</option>
                                            <option value="SDG 13 - Climate Action">SDG 13 - Climate Action</option>
                                            <option value="SDG 14 - Life Below Water">SDG 14 - Life Below Water</option>
                                            <option value="SDG 15 - Life on Land">SDG 15 - Life on Land</option>
                                            <option value="SDG 16 - Peace, Justice, and Strong Institutions">SDG 16 - Peace, Justice, and Strong Institutions</option>
                                            <option value="SDG 17 - Partnerships for the Goals">SDG 17 - Partnerships for the Goals</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="beneficiaries">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Action Buttons -->
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

        // Calculate PS Attribution
        function calculatePSAttribution() {
            const totalDuration = parseFloat(document.getElementById('total_duration').value) || 0;
            
            // Get all personnel rate fields, including dynamically added ones
            const rateFields = document.querySelectorAll('input[id$="_rate"]');
            
            let totalAttribution = 0;
            
            rateFields.forEach(field => {
                const rate = parseFloat(field.value) || 0;
                if (rate > 0) {
                    totalAttribution += rate * totalDuration;
                }
            });
            
            document.getElementById('ps_attribution').value = totalAttribution.toFixed(2);
        }

        // Update faculty count based on selected personnel
        function updateFacultyCount() {
            let maleCount = 0;
            let femaleCount = 0;
            
            // Get all gender fields, including dynamically added ones
            const genderFields = document.querySelectorAll('input[id$="_gender"]');
            
            genderFields.forEach(field => {
                const genderValue = field.value.toLowerCase().trim();
                if (genderValue === 'male') {
                    maleCount++;
                } else if (genderValue === 'female') {
                    femaleCount++;
                }
            });
            
            document.getElementById('faculty_male').value = maleCount;
            document.getElementById('faculty_female').value = femaleCount;
            
            // Update total internal counts
            updateTotalInternalCount();
        }
        
        // Update total internal beneficiaries
        function updateTotalInternalCount() {
            const studentsMale = parseInt(document.getElementById('students_male').value) || 0;
            const studentsFemale = parseInt(document.getElementById('students_female').value) || 0;
            const facultyMale = parseInt(document.getElementById('faculty_male').value) || 0;
            const facultyFemale = parseInt(document.getElementById('faculty_female').value) || 0;
            
            document.getElementById('total_internal_male').value = studentsMale + facultyMale;
            document.getElementById('total_internal_female').value = studentsFemale + facultyFemale;
            
            // After updating internal totals, update overall totals
            updateTotalBeneficiaries();
        }
        
        // Update total beneficiaries (internal + external)
        function updateTotalBeneficiaries() {
            const totalInternalMale = parseInt(document.getElementById('total_internal_male').value) || 0;
            const totalInternalFemale = parseInt(document.getElementById('total_internal_female').value) || 0;
            const externalMale = parseInt(document.getElementById('external_male').value) || 0;
            const externalFemale = parseInt(document.getElementById('external_female').value) || 0;
            
            const totalMale = totalInternalMale + externalMale;
            const totalFemale = totalInternalFemale + externalFemale;
            const totalBeneficiaries = totalMale + totalFemale;
            
            document.getElementById('total_male').value = totalMale;
            document.getElementById('total_female').value = totalFemale;
            document.getElementById('total_beneficiaries').value = totalBeneficiaries;
        }

        // Define calculateTotalDuration in the global scope
        function calculateTotalDuration() {
            console.log('calculateTotalDuration called');
            
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            
            console.log('Values:', { startDate, endDate, startTime, endTime });
            
            const lunchBreak = document.querySelector('input[name="lunch_break"]:checked').value;
            
            // Check if all required values are available
            if (!startDate || !endDate || !startTime || !endTime) {
                console.log('Missing required values for duration calculation');
                return;
            }
            
            // Calculate number of days
            const start = new Date(startDate);
            const end = new Date(endDate);
            const timeDiff = Math.abs(end.getTime() - start.getTime());
            const dayCount = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // Add 1 to include both start and end days
            
            console.log('Day count:', dayCount);
            
            // Calculate hours in a day
            let [startHour, startMinute] = startTime.split(':').map(Number);
            let [endHour, endMinute] = endTime.split(':').map(Number);
            
            // Convert to hours with decimal
            const startTimeDecimal = startHour + (startMinute / 60);
            const endTimeDecimal = endHour + (endMinute / 60);
            
            // Calculate hours per day
            let hoursPerDay = endTimeDecimal - startTimeDecimal;
            
            // Adjust for lunch break (subtract 1 hour if "with lunch")
            if (lunchBreak === 'with') {
                hoursPerDay -= 1;
            }
            
            // Make sure we don't have negative hours
            hoursPerDay = Math.max(0, hoursPerDay);
            
            console.log('Hours per day:', hoursPerDay);
            
            // Calculate total duration (days × hours per day)
            const totalDuration = dayCount * hoursPerDay;
            
            console.log('Total duration:', totalDuration);
            
            // Update the total duration field
            document.getElementById('total_duration').value = totalDuration.toFixed(2);
            
            // Update PS Attribution as well
            calculatePSAttribution();
        }

        // Modern multi-section form handling
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
            
            // Function to navigate between sections
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
            
            // Load years from gpb_entries table based on logged in campus
            function loadYears() {
                const userCampus = document.getElementById('campus').value;
                fetch('get_gpb_years.php?campus=' + encodeURIComponent(userCampus))
                    .then(response => response.json())
                    .then(data => {
                        const yearSelect = document.getElementById('year');
                        yearSelect.innerHTML = '<option value="" selected disabled>Select Year</option>';
                        
                        data.forEach(year => {
                            const option = document.createElement('option');
                            option.value = year;
                            option.textContent = year;
                            yearSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading years:', error));
            }
            
            // Load gender issues based on campus and selected year
            function loadGenderIssues() {
                const userCampus = document.getElementById('campus').value;
                const selectedYear = document.getElementById('year').value;
                
                if (selectedYear) {
                    fetch(`get_gender_issues.php?campus=${encodeURIComponent(userCampus)}&year=${encodeURIComponent(selectedYear)}`)
                        .then(response => response.json())
                        .then(data => {
                            const genderIssueSelect = document.getElementById('gender_issue');
                            genderIssueSelect.innerHTML = '<option value="" selected disabled>Select Gender Issue</option>';
                            
                            data.forEach(issue => {
                                const option = document.createElement('option');
                                option.value = issue.id;
                                option.textContent = issue.gender_issue;
                                genderIssueSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error loading gender issues:', error));
                }
            }
            
            // Attach event listeners for date and time inputs to calculate duration
            function attachDurationListeners() {
                const dateTimeInputs = ['start_date', 'end_date', 'start_time', 'end_time'];
                dateTimeInputs.forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        element.addEventListener('change', calculateTotalDuration);
                        element.addEventListener('input', calculateTotalDuration);
                    }
                });
                
                // Add event listeners for lunch break radio buttons
                document.querySelectorAll('input[name="lunch_break"]').forEach(radio => {
                    radio.addEventListener('change', calculateTotalDuration);
                });
            }
            
            // Initialize date/time event listeners
            attachDurationListeners();
            // Also add a timeout to ensure listeners are attached after any dynamic elements are loaded
            setTimeout(attachDurationListeners, 500);
            
            // Load personnel list
            function loadPersonnelList() {
                console.log('Loading personnel list...');
                const userCampus = document.getElementById('campus').value;
                
                // Add debugging for the AJAX call
                console.log('Fetching personnel data for campus: ' + userCampus);
                
                // Fetch personnel data
                fetch('get_personnel.php?campus=' + encodeURIComponent(userCampus))
                    .then(response => {
                        console.log('Response status:', response.status);
                        
                        // Debug the raw response
                        return response.text().then(text => {
                            console.log('Raw personnel response:', text);
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('Parse error:', e);
                                return [];
                            }
                        });
                    })
                    .then(data => {
                        // Debug the parsed data
                        console.log('Parsed personnel data:', data);
                        
                        const personnelSelects = [
                            'project_leader_name',
                            'asst_leader_name',
                            'staff_name',
                            'other_name'
                        ];
                        
                        // Store the personnel data globally for use
                        window.personnelData = Array.isArray(data) ? data : [];
                        console.log('Stored personnel data:', window.personnelData);
                        
                        // Create datalist elements for each personnel field
                        personnelSelects.forEach(selectId => {
                            console.log(`Processing select: ${selectId}`);
                            
                            // Get the select element
                            const select = document.getElementById(selectId);
                            if (!select) {
                                console.error(`Element with ID ${selectId} not found`);
                                return;
                            }
                            
                            const selectParent = select.parentNode;
                            
                            // Create a message element for "No personnel found"
                            const messageId = `${selectId}_message`;
                            let messageElement = document.getElementById(messageId);
                            
                            if (!messageElement) {
                                messageElement = document.createElement('div');
                                messageElement.id = messageId;
                                messageElement.className = 'text-muted small mt-1';
                                // Only show when there is no personnel data
                                messageElement.style.display = window.personnelData.length === 0 ? 'block' : 'none';
                                messageElement.textContent = 'No personnel found';
                            }
                            
                            // Create a datalist element
                            const datalistId = `${selectId}_list`;
                            let datalist = document.getElementById(datalistId);
                            if (!datalist) {
                                datalist = document.createElement('datalist');
                                datalist.id = datalistId;
                                document.body.appendChild(datalist);
                                console.log(`Created new datalist with id: ${datalistId}`);
                            } else {
                                datalist.innerHTML = ''; // Clear existing options
                                console.log(`Cleared existing datalist with id: ${datalistId}`);
                            }
                            
                            // Add options to datalist from personnel data
                            window.personnelData.forEach((person, index) => {
                                if (index < 3) {
                                    console.log(`Adding person to datalist: ${JSON.stringify(person)}`);
                                }
                                const option = document.createElement('option');
                                option.value = person.name || 'Unknown';
                                option.dataset.id = person.id;
                                option.dataset.gender = person.gender;
                                option.dataset.rank = person.academic_rank;
                                option.dataset.salary = person.monthly_salary;
                                option.dataset.rate = person.hourly_rate;
                                datalist.appendChild(option);
                            });
                            console.log(`Added ${window.personnelData.length} personnel to datalist: ${datalistId}`);
                            
                            // Create input element to replace select
                            const input = document.createElement('input');
                            input.type = 'text';
                            input.id = selectId;
                            input.name = selectId;
                            input.className = 'form-control';
                            input.placeholder = 'Type to search personnel';
                            input.setAttribute('list', datalistId);
                            input.required = select.required;
                            
                            // Create a container for the input and message
                            const container = document.createElement('div');
                            container.appendChild(input);
                            container.appendChild(messageElement);
                            
                            // Replace select with the new container
                            selectParent.replaceChild(container, select);
                            console.log(`Replaced select with input and message for: ${selectId}`);
                            
                            // Add input event listener for autocomplete
                            input.addEventListener('input', function() {
                                const inputValue = this.value.trim().toLowerCase();
                                console.log(`Input value changed to: ${inputValue}`);
                                
                                // Get the base ID to reference related fields
                                const baseId = selectId.replace('_name', '');
                                const genderField = document.getElementById(`${baseId}_gender`);
                                const rankField = document.getElementById(`${baseId}_rank`);
                                const salaryField = document.getElementById(`${baseId}_salary`);
                                const rateField = document.getElementById(`${baseId}_rate`);
                                
                                if (inputValue) {
                                    // Check for matches
                                    const matchingPersonnel = window.personnelData.filter(p => 
                                        p.name.toLowerCase().includes(inputValue)
                                    );
                                    
                                    if (matchingPersonnel.length === 0) {
                                        messageElement.style.display = 'block';
                                        messageElement.textContent = 'No matching personnel found';
                                        
                                        // Clear related fields when no match is found
                                        genderField.value = '';
                                        rankField.value = '';
                                        salaryField.value = '';
                                        rateField.value = '';
                                        delete this.dataset.personnelId;
                                    } else {
                                        // Hide message if we have matches
                                        messageElement.style.display = 'none';
                                    }
                                    
                                    // Find exact match
                                    const exactPerson = window.personnelData.find(p => 
                                        p.name.toLowerCase() === inputValue.toLowerCase()
                                    );
                                    
                                    if (exactPerson) {
                                        // Update related fields
                                        genderField.value = exactPerson.gender || '';
                                        rankField.value = exactPerson.academic_rank || '';
                                        salaryField.value = exactPerson.monthly_salary || '';
                                        rateField.value = exactPerson.hourly_rate || '';
                                        
                                        // Store ID
                                        this.dataset.personnelId = exactPerson.id;
                                        
                                        // Update faculty count and PS Attribution
                                        updateFacultyCount();
                                        calculatePSAttribution();
                                    } else {
                                        // No exact match, clear the related fields
                                        genderField.value = '';
                                        rankField.value = '';
                                        salaryField.value = '';
                                        rateField.value = '';
                                        delete this.dataset.personnelId;
                                        
                                        // Update faculty count and PS Attribution
                                        updateFacultyCount();
                                        calculatePSAttribution();
                                    }
                                } else {
                                    // Empty input, clear all fields
                                    genderField.value = '';
                                    rankField.value = '';
                                    salaryField.value = '';
                                    rateField.value = '';
                                    delete this.dataset.personnelId;
                                    
                                    // Show message if no personnel data
                                    messageElement.style.display = window.personnelData.length === 0 ? 'block' : 'none';
                                    if (window.personnelData.length === 0) {
                                        messageElement.textContent = 'No personnel found';
                                    }
                                    
                                    // Update faculty count and PS Attribution
                                    updateFacultyCount();
                                    calculatePSAttribution();
                                }
                            });
                        });
                    })
                    .catch(error => {
                        console.error('Error loading personnel:', error);
                        alert('Failed to load personnel data. Please check the console for more details.');
                    });
            }
            
            // Personnel selection change handler (kept for compatibility)
            function handlePersonnelSelection(selectId, genderId, rankId, salaryId, rateId) {
                // This function is kept for backward compatibility
            }
            
            // Initialize Select2 for multiple select
            if ($.fn.select2) {
                $('#sdgs').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Select SDGs',
                    closeOnSelect: false,
                    width: '100%',
                    selectionCssClass: 'sdg-selection',
                    dropdownCssClass: 'select2-dropdown-custom',
                    templateSelection: function(data) {
                        if (document.documentElement.getAttribute('data-bs-theme') === 'dark') {
                            return $('<span class="select2-selection__choice__text" style="color: white;">' + data.text + '</span>');
                        } else {
                            return $('<span class="select2-selection__choice__text">' + data.text + '</span>');
                        }
                    },
                    templateResult: function(data) {
                        if (!data.id) return data.text;
                        return $('<span>' + data.text + '</span>');
                    }
                });
                
                // Fix dark mode text rendering
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (document.documentElement.getAttribute('data-bs-theme') === 'dark') {
                            $('.select2-selection__choice').addClass('dark-selection-choice');
                            $('.select2-selection__rendered').css('color', 'white');
                            $('.select2-selection__choice__remove').css('color', 'white');
                        } else {
                            $('.select2-selection__choice').removeClass('dark-selection-choice');
                            $('.select2-selection__rendered').css('color', 'var(--text-primary)');
                            $('.select2-selection__choice__remove').css('color', '');
                        }
                    });
                });
                
                observer.observe(document.documentElement, { 
                    attributes: true, 
                    attributeFilter: ['data-bs-theme'] 
                });
                
                // Apply initial dark mode styling if needed
                if (document.documentElement.getAttribute('data-bs-theme') === 'dark') {
                    $('.select2-selection__choice').addClass('dark-selection-choice');
                    $('.select2-selection__rendered').css('color', 'white');
                    $('.select2-selection__choice__remove').css('color', 'white');
                }
            }
            
            // Set up event listeners
            document.getElementById('year').addEventListener('change', loadGenderIssues);
            
            // Make sure these time-related event listeners are set up
            document.getElementById('start_time').addEventListener('change', calculateTotalDuration);
            document.getElementById('end_time').addEventListener('change', calculateTotalDuration);
            document.getElementById('with_lunch').addEventListener('change', calculateTotalDuration);
            document.getElementById('no_lunch').addEventListener('change', calculateTotalDuration);
            
            // Set up input listeners for student counts
            document.getElementById('students_male').addEventListener('input', updateTotalInternalCount);
            document.getElementById('students_female').addEventListener('input', updateTotalInternalCount);
            
            // Set up input listeners for external counts
            document.getElementById('external_male').addEventListener('input', updateTotalBeneficiaries);
            document.getElementById('external_female').addEventListener('input', updateTotalBeneficiaries);
            
            // Initialize personnel selection handlers
            handlePersonnelSelection('project_leader_name', 'project_leader_gender', 'project_leader_rank', 'project_leader_salary', 'project_leader_rate');
            handlePersonnelSelection('asst_leader_name', 'asst_leader_gender', 'asst_leader_rank', 'asst_leader_salary', 'asst_leader_rate');
            handlePersonnelSelection('staff_name', 'staff_gender', 'staff_rank', 'staff_salary', 'staff_rate');
            handlePersonnelSelection('other_name', 'other_gender', 'other_rank', 'other_salary', 'other_rate');
            
            // Load initial data
            loadYears();
            loadPersonnelList();
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

        // Observer for theme changes to update Select2 styling
        const themeObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'data-bs-theme') {
                    const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                    // Force refresh of select2 to update styling
                    const currentSelections = $('#sdgs').val();
                    $('#sdgs').select2('destroy').select2({
                        theme: 'bootstrap-5',
                        placeholder: 'Select SDGs',
                        closeOnSelect: false,
                        width: '100%',
                        selectionCssClass: 'sdg-selection',
                        dropdownCssClass: 'select2-dropdown-custom',
                        templateSelection: function(data) {
                            if (isDarkMode) {
                                return $('<span class="select2-selection__choice__text" style="color: white;">' + data.text + '</span>');
                            } else {
                                return $('<span class="select2-selection__choice__text">' + data.text + '</span>');
                            }
                        },
                        templateResult: function(data) {
                            if (!data.id) return data.text;
                            return $('<span>' + data.text + '</span>');
                        }
                    });
                    $('#sdgs').val(currentSelections).trigger('change');
                }
            });
        });
        
        themeObserver.observe(document.documentElement, { attributes: true });

        // Add event listener to reload personnel when campus changes
        document.addEventListener('DOMContentLoaded', function() {
            // Load personnel initially
            loadPersonnelList();
            
            // Add event listener to campus field
            const campusInput = document.getElementById('campus');
            if (campusInput) {
                campusInput.addEventListener('change', function() {
                    console.log('Campus changed to:', this.value);
                    loadPersonnelList();
                });
            }
            
            // Initialize year dropdown with options
            const yearSelect = document.getElementById('year');
            const currentYear = new Date().getFullYear();
            for (let year = currentYear; year >= currentYear - 5; year--) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            }
            
            console.log('DOMContentLoaded event handlers initialized');

            // Initialize date picker dropdowns
            initializeDatePickers();
        });

        // Function to initialize the date picker dropdowns
        function initializeDatePickers() {
            console.log('Initializing date pickers');
            
            // Populate years for both start and end date pickers (current year + 5 years in future, 5 years in past)
            const yearSelects = [document.getElementById('start_year'), document.getElementById('end_year')];
            const currentYear = new Date().getFullYear();
            
            yearSelects.forEach(select => {
                if (!select) {
                    console.error('Year select not found');
                    return;
                }
                
                // Clear existing options except the placeholder
                while (select.options.length > 1) {
                    select.remove(1);
                }
                
                // Add year options
                for (let year = currentYear - 5; year <= currentYear + 5; year++) {
                    const option = document.createElement('option');
                    option.value = year;
                    option.textContent = year;
                    select.appendChild(option);
                }
                
                console.log(`Added ${currentYear - 5} to ${currentYear + 5} to year dropdown`);
            });
            
            // Initial population of days (default to 31)
            const daySelects = document.querySelectorAll('.start-day, .end-day');
            daySelects.forEach(select => {
                if (!select) {
                    console.error('Day select not found');
                    return;
                }
                populateDays(select, 31);
                console.log('Populated days dropdown with 31 days');
            });
            
            // Set up event listeners for month changes to update days
            const monthSelects = document.querySelectorAll('.start-month, .end-month');
            monthSelects.forEach(select => {
                select.addEventListener('change', updateDaysInMonth);
            });
            
            // Set up event listeners for day/month/year changes to update the date
            const dateInputs = document.querySelectorAll('.start-month, .start-day, .start-year, .end-month, .end-day, .end-year');
            dateInputs.forEach(input => {
                input.addEventListener('change', updateDateValue);
            });
            
            // Check if we should set default to today's date
            const today = new Date();
            
            // Set default month to current month
            const startMonth = document.getElementById('start_month');
            const endMonth = document.getElementById('end_month');
            
            if (startMonth && endMonth) {
                startMonth.value = String(today.getMonth() + 1).padStart(2, '0');
                endMonth.value = String(today.getMonth() + 1).padStart(2, '0');
                
                // Update days based on selected month
                updateDaysInMonth.call(startMonth);
                updateDaysInMonth.call(endMonth);
            }
            
            // Set default day to today
            const startDay = document.getElementById('start_day');
            const endDay = document.getElementById('end_day');
            
            if (startDay && endDay) {
                startDay.value = String(today.getDate()).padStart(2, '0');
                endDay.value = String(today.getDate()).padStart(2, '0');
            }
            
            // Set default year to current year
            const startYear = document.getElementById('start_year');
            const endYear = document.getElementById('end_year');
            
            if (startYear && endYear) {
                startYear.value = today.getFullYear().toString();
                endYear.value = today.getFullYear().toString();
            }
            
            // Update the date values
            if (startDay && endDay) {
                updateDateValue.call(startDay);
                updateDateValue.call(endDay);
                
                // Also set the hidden date fields directly with today's date in YYYY-MM-DD format
                const todayString = today.getFullYear() + '-' + 
                    String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(today.getDate()).padStart(2, '0');
                
                const startDateField = document.getElementById('start_date');
                const endDateField = document.getElementById('end_date');
                
                if (startDateField) {
                    startDateField.value = todayString;
                }
                
                if (endDateField) {
                    endDateField.value = todayString;
                }
            }
            
            console.log('Date picker initialization complete');
        }

        // Function to update days in month based on selected month and year
        function updateDaysInMonth() {
            const prefix = this.id.startsWith('start') ? 'start' : 'end';
            const monthSelect = document.getElementById(`${prefix}_month`);
            const yearSelect = document.getElementById(`${prefix}_year`);
            const daySelect = document.getElementById(`${prefix}_day`);
            
            if (!monthSelect || !yearSelect || !daySelect) return;
            
            const month = parseInt(monthSelect.value);
            const year = parseInt(yearSelect.value);
            
            // Default to 31 if month or year is not selected
            if (isNaN(month) || isNaN(year)) {
                populateDays(daySelect, 31);
                return;
            }
            
            // Determine days in month
            let daysInMonth;
            if (month === 2) { // February
                // Check for leap year
                daysInMonth = ((year % 4 === 0 && year % 100 !== 0) || year % 400 === 0) ? 29 : 28;
            } else if ([4, 6, 9, 11].includes(month)) { // April, June, September, November
                daysInMonth = 30;
            } else {
                daysInMonth = 31;
            }
            
            populateDays(daySelect, daysInMonth);
            
            // Now update the date value
            updateDateValue.call(daySelect);
        }
        
        // Function to populate days in a select element
        function populateDays(daySelect, daysInMonth) {
            const currentValue = daySelect.value;
            
            // Clear existing options except the placeholder
            while (daySelect.options.length > 1) {
                daySelect.remove(1);
            }
            
            // Add days
            for (let day = 1; day <= daysInMonth; day++) {
                const option = document.createElement('option');
                option.value = String(day).padStart(2, '0');
                option.textContent = day;
                daySelect.appendChild(option);
            }
            
            // Restore selected value if it's still valid
            if (currentValue && parseInt(currentValue) <= daysInMonth) {
                daySelect.value = currentValue;
            }
        }
        
        // Function to update date value when day, month, or year changes
        function updateDateValue() {
            console.log('Updating date value');
            
            const prefix = this.id.includes('start') ? 'start' : 'end';
            const monthSelect = document.getElementById(`${prefix}_month`);
            const daySelect = document.getElementById(`${prefix}_day`);
            const yearSelect = document.getElementById(`${prefix}_year`);
            const dateInput = document.getElementById(`${prefix}_date`);
            const dateDisplay = document.getElementById(`${prefix}_date_display`);
            
            if (!monthSelect || !daySelect || !yearSelect || !dateInput || !dateDisplay) {
                console.error('One or more date elements not found');
                return;
            }
            
            // Only update if all values are selected
            if (monthSelect.value && daySelect.value && yearSelect.value) {
                // Format: YYYY-MM-DD
                const dateValue = `${yearSelect.value}-${monthSelect.value}-${daySelect.value}`;
                dateInput.value = dateValue;
                
                // Display format: Month DD, YYYY (e.g., January 01, 2023)
                const month = monthSelect.options[monthSelect.selectedIndex].text;
                dateDisplay.value = `${month} ${parseInt(daySelect.value)}, ${yearSelect.value}`;
                console.log(`Updated date to: ${dateDisplay.value}`);
                
                // Trigger change event on the date input to update calculations
                const event = new Event('change');
                dateInput.dispatchEvent(event);
            } else {
                dateInput.value = '';
                dateDisplay.value = '';
            }
        }

        // Immediately initialize date pickers when the page loads
        document.addEventListener('DOMContentLoaded', initializeDatePickers);
        
        // Also try to initialize them right away if the DOM is already loaded
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            console.log('DOM already loaded, initializing date pickers immediately');
            setTimeout(initializeDatePickers, 0);
        }

        // Function to handle adding new personnel entries
        function addPersonnel(type, personnelData = null) {
            console.log(`Adding new ${type} personnel`, personnelData);
            
            // Find the add button
            const addButton = document.querySelector(`button[data-type="${type}"]`);
            if (!addButton) {
                console.error(`Add button for ${type} not found`);
                return;
            }
            
            // Create unique IDs for the new fields
            const timestamp = new Date().getTime();
            const newId = `${type}_${timestamp}`;
            
            // Create a container that includes the separator and the new entry
            const container = document.createElement('div');
            container.className = 'additional-personnel mb-4';
            
            // Create a horizontal line for separation that matches the section title underline
            const separator = document.createElement('hr');
            separator.className = 'mt-4 mb-4';
            separator.style.borderTop = '2px solid var(--accent-color)';
            separator.style.opacity = '0.8';
            container.appendChild(separator);
            
            // Create the personnel fields container
            const newPersonnel = document.createElement('div');
            newPersonnel.className = 'personnel-fields';
            
            // Add HTML for new personnel fields - matching the original structure
            newPersonnel.innerHTML = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="${newId}_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="${newId}_name" name="${type}_name[]" placeholder="Type to search personnel" required>
                            <div id="${newId}_name_message" class="text-muted small mt-1" style="display: none;">No personnel found</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="${newId}_gender" class="form-label">Gender</label>
                            <input type="text" class="form-control" id="${newId}_gender" name="${type}_gender[]" readonly>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="${newId}_rank" class="form-label">Academic Rank</label>
                            <input type="text" class="form-control" id="${newId}_rank" name="${type}_rank[]" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="${newId}_salary" class="form-label">Monthly Salary</label>
                            <input type="text" class="form-control" id="${newId}_salary" name="${type}_salary[]" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="${newId}_rate" class="form-label">Rate per hour</label>
                            <input type="text" class="form-control" id="${newId}_rate" name="${type}_rate[]" readonly>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePersonnel(this)">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                </div>
            `;
            
            // Add the personnel fields to the container
            container.appendChild(newPersonnel);
            
            // Insert the container before the add button
            addButton.parentNode.insertBefore(container, addButton);
            
            // Get references to the added fields
            const nameInput = document.getElementById(`${newId}_name`);
            const genderField = document.getElementById(`${newId}_gender`);
            const rankField = document.getElementById(`${newId}_rank`);
            const salaryField = document.getElementById(`${newId}_salary`);
            const rateField = document.getElementById(`${newId}_rate`);
            const messageElement = document.getElementById(`${newId}_name_message`);
            
            // If we have personnel data passed in, populate the fields directly
            if (personnelData) {
                console.log(`Populating additional ${type} personnel with data:`, personnelData);
                
                nameInput.value = personnelData.name || '';
                genderField.value = personnelData.gender || '';
                rankField.value = personnelData.academic_rank || '';
                salaryField.value = personnelData.monthly_salary || '';
                rateField.value = personnelData.rate_per_hour || '';
                
                // Get the numeric personnel ID and set it directly
                let numericPersonnelId = 0;
                if (personnelData.personnel_id) {
                    numericPersonnelId = parseInt(personnelData.personnel_id, 10);
                    if (isNaN(numericPersonnelId) || numericPersonnelId <= 0) {
                        console.error(`Invalid personnel ID: ${personnelData.personnel_id}`);
                    } else {
                        // Set the personnel ID using the setAttribute method for maximum compatibility
                        nameInput.setAttribute('data-personnel-id', numericPersonnelId);
                        console.log(`Set data-personnel-id=${numericPersonnelId} on element ${nameInput.id}`);
                        
                        // Verify it was set correctly
                        const actualValue = nameInput.getAttribute('data-personnel-id');
                        console.log(`Verification: ${nameInput.id} has data-personnel-id=${actualValue}`);
                    }
                } else {
                    console.error(`No personnel ID provided for ${personnelData.name}`);
                }
                
                console.log(`Populated additional ${type} personnel fields:`, {
                    name: nameInput.value,
                    gender: genderField.value,
                    rank: rankField.value,
                    salary: salaryField.value,
                    rate: rateField.value,
                    personnelId: nameInput.getAttribute('data-personnel-id')
                });
                
                return; // Skip the rest of the setup
            }
            
            // Create datalist for autocomplete
            const datalistId = `${newId}_name_list`;
            const datalist = document.createElement('datalist');
            datalist.id = datalistId;
            
            // Add personnel options to datalist
            if (window.personnelData && window.personnelData.length > 0) {
                window.personnelData.forEach(person => {
                    const option = document.createElement('option');
                    option.value = person.name;
                    datalist.appendChild(option);
                });
                
                document.body.appendChild(datalist);
                nameInput.setAttribute('list', datalistId);
            } else {
                messageElement.style.display = 'block';
                messageElement.textContent = 'No personnel found';
            }
            
            // Add event listener for name input changes
            nameInput.addEventListener('input', function() {
                const inputValue = this.value.trim().toLowerCase();
                
                if (inputValue) {
                    // Check for matches
                    const matchingPersonnel = window.personnelData.filter(p => 
                        p.name.toLowerCase().includes(inputValue)
                    );
                    
                    if (matchingPersonnel.length === 0) {
                        messageElement.style.display = 'block';
                        messageElement.textContent = 'No matching personnel found';
                        
                        // Clear related fields
                        genderField.value = '';
                        rankField.value = '';
                        salaryField.value = '';
                        rateField.value = '';
                        delete this.dataset.personnelId;
                    } else {
                        messageElement.style.display = 'none';
                    }
                    
                    // Find exact match
                    const exactPerson = window.personnelData.find(p => 
                        p.name.toLowerCase() === inputValue.toLowerCase()
                    );
                    
                    if (exactPerson) {
                        // Update related fields
                        genderField.value = exactPerson.gender || '';
                        rankField.value = exactPerson.academic_rank || '';
                        salaryField.value = exactPerson.monthly_salary || '';
                        rateField.value = exactPerson.hourly_rate || '';
                        
                        // Store ID
                        this.dataset.personnelId = exactPerson.id;
                        
                        // Update calculations
                        updateFacultyCount();
                        calculatePSAttribution();
                    } else {
                        // No exact match, clear the fields
                        genderField.value = '';
                        rankField.value = '';
                        salaryField.value = '';
                        rateField.value = '';
                        delete this.dataset.personnelId;
                        
                        // Update calculations
                        updateFacultyCount();
                        calculatePSAttribution();
                    }
                } else {
                    // Empty input, clear all fields
                    genderField.value = '';
                    rankField.value = '';
                    salaryField.value = '';
                    rateField.value = '';
                    delete this.dataset.personnelId;
                    
                    messageElement.style.display = window.personnelData.length === 0 ? 'block' : 'none';
                    if (window.personnelData.length === 0) {
                        messageElement.textContent = 'No personnel found';
                    }
                    
                    // Update calculations
                    updateFacultyCount();
                    calculatePSAttribution();
                }
            });
            
            // Focus the new input, except for when we're just populating existing data
            if (!personnelData) {
                nameInput.focus();
            }
        }

        // Function to remove personnel entry
        function removePersonnel(button) {
            const entry = button.closest('.additional-personnel');
            if (entry) {
                entry.remove();
                updateFacultyCount();
                calculatePSAttribution();
            }
        }

        // Set up the add button event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize add buttons for different personnel types
            const addTypes = ['project_leader', 'asst_leader', 'staff', 'other'];
            
            addTypes.forEach(type => {
                // Add class to the original sections for identification
                const originalSection = document.querySelector(`#${type}_name`);
                if (originalSection) {
                    const sectionContainer = originalSection.closest('.row').parentNode;
                    sectionContainer.classList.add(`${type}-section`, 'personnel-entry');
                }
                
                // Add class to add button containers
                const addButton = document.querySelector(`button[data-type="${type}"]`);
                if (addButton) {
                    addButton.parentNode.classList.add(`${type}-add-button`);
                    addButton.onclick = function() { addPersonnel(type); };
                }
            });
        });

        // Add small helper text after the gender issue dropdown
        const genderIssueContainer = document.querySelector('#gender_issue').closest('.form-group');
        const genderIssueHelperText = document.createElement('small');
        genderIssueHelperText.className = 'text-muted mt-1 d-block';
        genderIssueContainer.appendChild(genderIssueHelperText);

        // Disable gender issue dropdown by default
        document.getElementById('gender_issue').disabled = true;

        // Add event listener to the year dropdown
        document.getElementById('year').addEventListener('change', function() {
            // We'll use the global updateGenderIssueState function once it's defined
            // The actual logic is now handled in the sequential validation code
            if (typeof updateGenderIssueState === 'function') {
                updateGenderIssueState();
            } else {
                // Fallback behavior if the function isn't defined yet
                const genderIssueDropdown = document.getElementById('gender_issue');
                const selectedYear = this.value;
                
                if (selectedYear) {
                    // Only enable if quarter is also selected (to maintain consistency with sequential validation)
                    const quarterField = document.getElementById('quarter');
                    if (quarterField && quarterField.value) {
                        genderIssueDropdown.disabled = false;
                        genderIssueHelperText.textContent = ''; // Clear the text completely
                        genderIssueHelperText.style.display = 'none'; // And hide the element
                    }
                } else {
                    // No year selected, disable gender issue dropdown
                    genderIssueDropdown.disabled = true;
                    genderIssueHelperText.style.display = 'block'; // Make it visible
                }
            }
        });

        // Directly add event listeners for date fields - this will work regardless of when the DOM is loaded
        window.addEventListener('load', function() {
            console.log('Window loaded - adding direct event listeners for dates');
            
            // Explicitly add for date fields
            const dateFields = ['start_date', 'end_date'];
            dateFields.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    // Don't try to remove listeners with direct reference since the function may not be defined yet
                    
                    // Add new listeners with anonymous function
                    element.addEventListener('change', function(e) {
                        console.log(`Date changed: ${id} = ${this.value}`);
                        // Call the function by name when it's executed
                        if (typeof calculateTotalDuration === 'function') {
                            calculateTotalDuration();
                        } else {
                            console.error('calculateTotalDuration function not found');
                        }
                    });
                    
                    console.log(`Added direct event listener to ${id}`);
                } else {
                    console.error(`Element ${id} not found`);
                }
            });
        });

        // Function to validate section completeness
        function validateSectionCompleteness() {
            console.log('Validating section completeness...');
            
            // Validate Basic Info section
            const basicInfoSection = document.getElementById('basic-info');
            const basicInfoInputs = basicInfoSection.querySelectorAll('input[required], select[required], textarea[required]');
            const basicInfoComplete = Array.from(basicInfoInputs).every(input => {
                console.log(`Basic Info field ${input.id || input.name}: value=${input.value}`);
                return input.value && input.value.trim() !== '';
            });
            
            const basicInfoNavItem = document.querySelector('.form-nav-item[data-section="basic-info"]');
            console.log('Basic Info complete:', basicInfoComplete);
            
            if (basicInfoComplete) {
                basicInfoNavItem.classList.add('completed');
                basicInfoNavItem.classList.remove('invalid'); // Remove invalid class if section is complete
                console.log('Added completed class to Basic Info nav item');
            } else {
                basicInfoNavItem.classList.remove('completed');
            }
            
            // Validate Location & Date section
            const locationDateSection = document.getElementById('location-date');
            const locationDateInputs = locationDateSection.querySelectorAll('input[required], select[required], textarea[required]');
            const locationDateComplete = Array.from(locationDateInputs).every(input => {
                if (input.style.display === 'none') return true; // Skip hidden inputs
                console.log(`Location & Date field ${input.id || input.name}: value=${input.value}, hidden=${input.style.display === 'none'}`);
                return input.value && input.value.trim() !== '';
            });
            
            const locationDateNavItem = document.querySelector('.form-nav-item[data-section="location-date"]');
            console.log('Location & Date complete:', locationDateComplete);
            
            if (locationDateComplete) {
                locationDateNavItem.classList.add('completed');
                locationDateNavItem.classList.remove('invalid'); // Remove invalid class if section is complete
                console.log('Added completed class to Location & Date nav item');
            } else {
                locationDateNavItem.classList.remove('completed');
            }
            
            // Validate Personnel section
            const personnelSection = document.getElementById('personnel');
            const personnelInputs = personnelSection.querySelectorAll('input[required], select[required], textarea[required]');
            const personnelComplete = Array.from(personnelInputs).every(input => {
                console.log(`Personnel field ${input.id || input.name}: value=${input.value}`);
                
                // For personnel name fields, check if they have a valid personnelId
                if (input.id && input.id.endsWith('_name')) {
                    const hasValue = input.value && input.value.trim() !== '';
                    const hasValidPersonnel = input.dataset.personnelId !== undefined;
                    
                    console.log(`  - Personnel name field: value=${hasValue}, valid personnel=${hasValidPersonnel}`);
                    return hasValue && hasValidPersonnel;
                }
                
                return input.value && input.value.trim() !== '';
            });
            
            const personnelNavItem = document.querySelector('.form-nav-item[data-section="personnel"]');
            console.log('Personnel complete:', personnelComplete);
            
            if (personnelComplete) {
                personnelNavItem.classList.add('completed');
                personnelNavItem.classList.remove('invalid'); // Remove invalid class if section is complete
                console.log('Added completed class to Personnel nav item');
            } else {
                personnelNavItem.classList.remove('completed');
            }
            
            // Validate Beneficiaries section
            const beneficiariesSection = document.getElementById('beneficiaries');
            const beneficiariesInputs = beneficiariesSection.querySelectorAll('input[required], select[required], textarea[required]');
            const beneficiariesComplete = Array.from(beneficiariesInputs).every(input => {
                console.log(`Beneficiaries field ${input.id || input.name}: value=${input.value}`);
                return input.value && input.value.trim() !== '';
            });
            
            const beneficiariesNavItem = document.querySelector('.form-nav-item[data-section="beneficiaries"]');
            console.log('Beneficiaries complete:', beneficiariesComplete);
            
            if (beneficiariesComplete) {
                beneficiariesNavItem.classList.add('completed');
                beneficiariesNavItem.classList.remove('invalid'); // Remove invalid class if section is complete
                console.log('Added completed class to Beneficiaries nav item');
            } else {
                beneficiariesNavItem.classList.remove('completed');
            }
            
            // Validate Budget & SDGs section
            const budgetSdgsSection = document.getElementById('budget-sdgs');
            const budgetSdgsInputs = budgetSdgsSection.querySelectorAll('input[required], select[required], textarea[required]');
            const budgetSdgsComplete = Array.from(budgetSdgsInputs).every(input => {
                // Skip the sdgs field since it's optional
                if (input.id === 'sdgs') {
                    console.log('Skipping SDGs field as it is optional');
                    return true;
                }
                console.log(`Budget & SDGs field ${input.id || input.name}: value=${input.value}`);
                return input.value && input.value.trim() !== '';
            });
            
            const budgetSdgsNavItem = document.querySelector('.form-nav-item[data-section="budget-sdgs"]');
            console.log('Budget & SDGs complete:', budgetSdgsComplete);
            
            if (budgetSdgsComplete) {
                budgetSdgsNavItem.classList.add('completed');
                budgetSdgsNavItem.classList.remove('invalid'); // Remove invalid class if section is complete
                console.log('Added completed class to Budget & SDGs nav item');
            } else {
                budgetSdgsNavItem.classList.remove('completed');
            }
        }
        
        // Set up event listeners for validation
        document.addEventListener('DOMContentLoaded', function() {
            // Add validation when inputs change
            const allInputs = document.querySelectorAll('input, select, textarea');
            allInputs.forEach(input => {
                input.addEventListener('change', validateSectionCompleteness);
                input.addEventListener('input', validateSectionCompleteness);
                input.addEventListener('blur', validateSectionCompleteness);
            });
            
            // Special handling for personnel fields
            const personnelSection = document.getElementById('personnel');
            
            // Watch for changes in the personnel section specifically
            function revalidatePersonnel() {
                console.log('Personnel section changed, revalidating...');
                validateSectionCompleteness();
            }
            
            // Monitor personnel inputs more aggressively
            personnelSection.addEventListener('change', revalidatePersonnel);
            personnelSection.addEventListener('input', revalidatePersonnel);
            personnelSection.addEventListener('click', function() {
                setTimeout(revalidatePersonnel, 100);
            });
            
            // Set up a MutationObserver to watch for DOM changes in the personnel section
            const observer = new MutationObserver(function(mutations) {
                // Check if mutations are just class changes on elements we're updating in validateSectionCompleteness
                const shouldSkip = mutations.every(mutation => {
                    // Skip if it's just a class attribute change on section-title element
                    if (mutation.type === 'attributes' && 
                        mutation.attributeName === 'class' && 
                        (mutation.target.classList.contains('section-title') || 
                         mutation.target.classList.contains('form-nav-item'))) {
                        return true;
                    }
                    return false;
                });
                
                if (!shouldSkip) {
                    console.log('DOM mutation detected in personnel section');
                    setTimeout(revalidatePersonnel, 100);
                }
            });
            
            observer.observe(personnelSection, {
                childList: true, 
                subtree: true,
                attributes: true,
                attributeFilter: ['value', 'class']
            });
            
            // Validate when switching sections
            const navItems = document.querySelectorAll('.form-nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', validateSectionCompleteness);
            });
            
            // Add validation to the addPersonnel and removePersonnel functions
            const originalAddPersonnel = window.addPersonnel;
            window.addPersonnel = function(type, personnelData = null) {
                console.log('Wrapper called with:', type, personnelData);
                originalAddPersonnel(type, personnelData);
                setTimeout(revalidatePersonnel, 200);
            };
            
            const originalRemovePersonnel = window.removePersonnel;
            window.removePersonnel = function(button) {
                originalRemovePersonnel(button);
                setTimeout(revalidatePersonnel, 200);
            };
            
            // Initial validation
            validateSectionCompleteness();
        });

        // Function to validate all required fields before submission
        function validateFormBeforeSubmission() {
            console.log('Validating form before submission');
            let isValid = true;
            
            // First, clear all previous validation states
            clearValidationState();
            
            // Force duplicate activity check to ensure it's up to date
            const activityField = document.getElementById('activity');
            const campusField = document.getElementById('campus');
            
            if (activityField && activityField.value.trim()) {
                // Perform an immediate check for duplicates
                if (campusField && campusField.value.trim()) {
                    
                    // Mark the activity as possibly having a duplicate
                    // This will be updated when the async check completes
                    console.log('Checking for duplicate activity before form submission');
                    
                    // Try to get the existing duplicate status
                    if (activityField.dataset.duplicate === 'true') {
                        console.log('Activity is already marked as duplicate');
                        isValid = markActivityAsDuplicate(activityField);
                    } else {
                        // We still need to perform form validation for other fields
                        // The actual duplicate check is async, so we'll rely on the
                        // click handler for the submit button to catch duplicates
                        console.log('Activity is not marked as duplicate, relying on button click handler');
                    }
                }
            }
            
            // Validate each section and mark invalid fields
            const sections = [
                { id: 'basic-info', navSelector: '.form-nav-item[data-section="basic-info"]' },
                { id: 'location-date', navSelector: '.form-nav-item[data-section="location-date"]' },
                { id: 'personnel', navSelector: '.form-nav-item[data-section="personnel"]' },
                { id: 'beneficiaries', navSelector: '.form-nav-item[data-section="beneficiaries"]' },
                { id: 'budget-sdgs', navSelector: '.form-nav-item[data-section="budget-sdgs"]' }
            ];
            
            // For activity fields, if we're in update mode, check if the activity name changed
            if (activityField && activityField.value.trim() && activityField.dataset.duplicate === 'true') {
                // Check if we're in update mode and the activity name hasn't changed
                const addBtn = document.getElementById('addBtn');
                const isUpdate = addBtn && addBtn.getAttribute('data-action') === 'update';
                
                if (isUpdate && window.originalActivityName && activityField.value.trim() === window.originalActivityName) {
                    console.log('Activity name unchanged in update mode, skipping duplicate validation');
                    // Clear duplicate status since we're updating the same record
                    activityField.dataset.duplicate = 'false';
                    activityField.classList.remove('is-invalid');
                    
                    // Clear error message if it exists
                    const feedback = activityField.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.style.display = 'none';
                    }
                }
            }
            
            sections.forEach(section => {
                const sectionElement = document.getElementById(section.id);
                const sectionNavItem = document.querySelector(section.navSelector);
                
                // Get all required fields in this section
                const requiredFields = sectionElement.querySelectorAll('input[required], select[required], textarea[required]');
                
                // Check if any required fields are empty
                let sectionValid = true;
                requiredFields.forEach(field => {
                    // Skip hidden fields
                    if (field.style.display === 'none') return;
                    
                    // Skip multi-select SDGs field since it's optional
                    if (field.id === 'sdgs') return;
                    
                    // Check if field is empty
                    if (!field.value || field.value.trim() === '') {
                        sectionValid = false;
                        isValid = false;
                        
                        // Mark field as invalid
                        field.classList.add('is-invalid');
                        
                        // Special handling for input-with-currency
                        const currencyContainer = field.closest('.input-with-currency');
                        
                        // Add invalid feedback message if it doesn't exist
                        if (currencyContainer) {
                            // Mark the currency container as invalid too
                            currencyContainer.classList.add('is-invalid');
                            
                            // For currency inputs, place the feedback after the .input-with-currency div
                            let feedbackElement = currencyContainer.nextElementSibling;
                            if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
                                feedbackElement = document.createElement('div');
                                feedbackElement.className = 'invalid-feedback d-block'; // d-block to ensure it shows
                                feedbackElement.textContent = 'This field is required.';
                                currencyContainer.parentNode.insertBefore(feedbackElement, currencyContainer.nextSibling);
                            }
                        } else {
                            // Normal fields
                            let feedbackElement = field.nextElementSibling;
                            if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
                                feedbackElement = document.createElement('div');
                                feedbackElement.className = 'invalid-feedback';
                                feedbackElement.textContent = 'This field is required.';
                                field.parentNode.insertBefore(feedbackElement, field.nextSibling);
                            }
                        }
                    } 
                    // Special validation for personnel fields - check if they match with database
                    else if (section.id === 'personnel' && field.id && field.id.endsWith('_name') && !field.dataset.personnelId) {
                        sectionValid = false;
                        isValid = false;
                        
                        // Mark field as invalid
                        field.classList.add('is-invalid');
                        
                        // Add invalid feedback message if it doesn't exist
                        let feedbackElement = field.nextElementSibling;
                        if (feedbackElement && feedbackElement.classList.contains('text-muted')) {
                            // Skip the message element that shows "No personnel found"
                            feedbackElement = feedbackElement.nextElementSibling;
                        }
                        
                        if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
                            feedbackElement = document.createElement('div');
                            feedbackElement.className = 'invalid-feedback';
                            feedbackElement.textContent = 'Not a valid personnel.';
                            field.parentNode.insertBefore(feedbackElement, field.nextSibling ? 
                                                           field.nextSibling.nextElementSibling : null);
                        }
                    }
                    // Special validation for duplicate activities
                    else if (field.id === 'activity' && field.dataset.duplicate === 'true') {
                        sectionValid = false;
                        isValid = false;
                        
                        // Mark field as invalid
                        field.classList.add('is-invalid');
                        
                        // Add invalid feedback message if it doesn't exist
                        let feedbackElement = field.nextElementSibling;
                        if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
                            feedbackElement = document.createElement('div');
                            feedbackElement.className = 'invalid-feedback';
                            field.parentNode.insertBefore(feedbackElement, field.nextSibling);
                        }
                        
                        feedbackElement.style.display = 'block';
                        feedbackElement.textContent = 'This activity already exists. Please enter a unique activity name.';
                    }
                });
                
                // Mark section navigation as invalid if needed
                if (!sectionValid) {
                    sectionNavItem.classList.add('invalid');
                    sectionNavItem.classList.remove('completed');
                }
            });
            
            if (!isValid) {
                // Navigate to the first section with errors
                for (const section of sections) {
                    const sectionNavItem = document.querySelector(section.navSelector);
                    if (sectionNavItem.classList.contains('invalid')) {
                        // Trigger click on this nav item to navigate to the section
                        sectionNavItem.click();
                        break;
                    }
                }
            }
            
            return isValid;
        }
        
        // Helper function to mark activity as duplicate
        function markActivityAsDuplicate(activityField) {
            if (!activityField) return false;
            
            // Check if we're in update mode with unchanged activity name
            const addBtn = document.getElementById('addBtn');
            const isUpdateMode = addBtn && addBtn.getAttribute('data-action') === 'update';
            const isActivityUnchanged = isUpdateMode && 
                                     window.originalActivityName && 
                                     activityField.value.trim() === window.originalActivityName;
            
            // Skip marking as duplicate if we're updating with the same name
            if (isUpdateMode && isActivityUnchanged) {
                console.log('Update mode with unchanged activity name - skipping duplicate marking');
                // Clear duplicate status
                activityField.dataset.duplicate = 'false';
                activityField.classList.remove('is-invalid');
                
                // Clear error message if it exists
                let feedbackElement = activityField.nextElementSibling;
                if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
                    feedbackElement.style.display = 'none';
                }
                
                return true; // Valid for update mode with unchanged name
            }
            
            console.log('Marking activity as duplicate');
            activityField.classList.add('is-invalid');
            
            // Add invalid feedback message if it doesn't exist
            let feedbackElement = activityField.nextElementSibling;
            if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
                feedbackElement = document.createElement('div');
                feedbackElement.className = 'invalid-feedback';
                activityField.parentNode.insertBefore(feedbackElement, activityField.nextSibling);
            }
            
            feedbackElement.style.display = 'block';
            feedbackElement.textContent = 'This activity already exists. Please enter a unique activity name.';
            
            // Mark the Basic Info tab as invalid
            const basicInfoNavItem = document.querySelector('.form-nav-item[data-section="basic-info"]');
            if (basicInfoNavItem) {
                basicInfoNavItem.classList.add('invalid');
                basicInfoNavItem.classList.add('has-error');
                basicInfoNavItem.classList.remove('completed');
            }
            
            return false; // Not valid
        }
        
        // Function to clear all validation states
        function clearValidationState() {
            // Remove invalid class from all inputs
            document.querySelectorAll('.is-invalid').forEach(element => {
                element.classList.remove('is-invalid');
            });
            
            // Remove invalid feedback messages
            document.querySelectorAll('.invalid-feedback').forEach(element => {
                element.remove();
            });
            
            // Remove invalid class from section nav items
            document.querySelectorAll('.form-nav-item.invalid').forEach(element => {
                element.classList.remove('invalid');
            });
            
            // Don't clear completion status during validation
        }
        
        // Set up event listener for input fields to clear their validation state when edited
        document.addEventListener('DOMContentLoaded', function() {
            const allInputs = document.querySelectorAll('input, select, textarea');
            allInputs.forEach(input => {
                // Set up common validation handler for all input events
                const validateInputField = function() {
                    // Remove invalid status
                    this.classList.remove('is-invalid');
                    
                    // Special handling for input with currency
                    const currencyContainer = this.closest('.input-with-currency');
                    if (currencyContainer) {
                        currencyContainer.classList.remove('is-invalid');
                        // Find feedback message after the currency container
                        const feedbackElement = currencyContainer.nextElementSibling;
                        if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
                            feedbackElement.remove();
                        }
                    } else {
                        // Normal fields
                        const feedback = this.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.remove();
                        }
                        
                        // Also check the sibling after the message element for personnel fields
                        if (this.id && this.id.endsWith('_name') && feedback && 
                            feedback.classList.contains('text-muted') && feedback.nextElementSibling && 
                            feedback.nextElementSibling.classList.contains('invalid-feedback')) {
                            feedback.nextElementSibling.remove();
                        }
                    }
                    
                    // Special validation for personnel fields
                    if (this.closest('#personnel') && this.id && this.id.endsWith('_name')) {
                        // Check if the field has a valid personnelId
                        if (this.value && !this.dataset.personnelId) {
                            // Add validation message
                            let messageElement = this.nextElementSibling;
                            let feedbackElement;
                            
                            if (messageElement && messageElement.classList.contains('text-muted')) {
                                // Skip the message element
                                feedbackElement = messageElement.nextElementSibling;
                            } else {
                                feedbackElement = messageElement;
                            }
                            
                            if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
                                feedbackElement = document.createElement('div');
                                feedbackElement.className = 'invalid-feedback';
                                feedbackElement.textContent = 'Not a valid personnel.';
                                
                                if (messageElement && messageElement.classList.contains('text-muted')) {
                                    this.parentNode.insertBefore(feedbackElement, messageElement.nextSibling);
                                } else {
                                    this.parentNode.insertBefore(feedbackElement, this.nextSibling);
                                }
                            }
                            
                            // Mark as invalid
                            this.classList.add('is-invalid');
                            feedbackElement.style.display = 'block';
                        }
                    }
                    
                    // Re-validate the section for completion
                    setTimeout(validateSectionCompleteness, 0);
                };
                
                // Add the validation handler for multiple events
                input.addEventListener('input', validateInputField);
                input.addEventListener('change', validateInputField);
                input.addEventListener('blur', validateInputField);
            });
            
            // Set up event listener for add button
            const addBtn = document.getElementById('addBtn');
            if (addBtn) {
                addBtn.addEventListener('click', function(e) {
                    // Prevent default form submission
                    e.preventDefault();
                    
                    // Get the activity field for duplicate check
                    const activityField = document.getElementById('activity');
                    
                    // For update mode, check if activity name is unchanged from original
                    const isUpdateMode = this.getAttribute('data-action') === 'update';
                    const isActivityUnchanged = isUpdateMode && 
                                   window.originalActivityName && 
                                   activityField.value.trim() === window.originalActivityName;
                    
                    // If we're updating with an unchanged activity name, force duplicate check to pass
                    if (isUpdateMode && isActivityUnchanged && activityField) {
                        console.log('Update mode with unchanged activity name - clearing duplicate status');
                        activityField.dataset.duplicate = 'false';
                        activityField.classList.remove('is-invalid');
                        
                        // Clear feedback message if it exists
                        const feedback = activityField.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.style.display = 'none';
                        }
                    }
                    
                    // Validate form
                    const isValid = validateFormBeforeSubmission();
                    
                    // Check if activity is still marked as duplicate after validation
                    // Skip this check if we're in update mode with unchanged activity name
                    if (activityField && 
                        activityField.dataset.duplicate === 'true' && 
                        !(isUpdateMode && isActivityUnchanged)) {
                        
                        console.log('Activity is marked as duplicate, preventing form submission');
                        
                        // Mark field as invalid
                        activityField.classList.add('is-invalid');
                        
                        // Add invalid feedback message if it doesn't exist
                        let feedbackElement = activityField.nextElementSibling;
                        if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
                            feedbackElement = document.createElement('div');
                            feedbackElement.className = 'invalid-feedback';
                            activityField.parentNode.insertBefore(feedbackElement, activityField.nextSibling);
                        }
                        
                        feedbackElement.style.display = 'block';
                        feedbackElement.textContent = 'This activity already exists. Please enter a unique activity name.';
                        
                        // Mark the Basic Info tab as invalid
                        const basicInfoNavItem = document.querySelector('.form-nav-item[data-section="basic-info"]');
                        if (basicInfoNavItem) {
                            basicInfoNavItem.classList.add('invalid');
                            basicInfoNavItem.classList.add('has-error');
                            basicInfoNavItem.classList.remove('completed');
                            
                            // Navigate to basic info section
                            basicInfoNavItem.click();
                            // Scroll to the activity field and focus it
                            setTimeout(() => {
                                activityField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                activityField.focus();
                            }, 100);
                        }
                        
                        return;
                    }
                    
                    if (isValid) {
                        console.log('Form is valid, saving to database');
                        
                        // Gather all form data
                        const formData = new FormData(document.getElementById('ppasForm'));
                        
                        // Check if this is an update and include the entry ID
                        if (isUpdateMode) {
                            const entryId = this.getAttribute('data-entry-id');
                            if (entryId) {
                                console.log('Update mode detected, adding entry_id:', entryId);
                                formData.append('entry_id', entryId);
                            }
                        }
                        
                        // Get SDGs as array
                        const sdgsSelect = document.getElementById('sdgs');
                        if (sdgsSelect) {
                            const selectedSdgs = [...sdgsSelect.selectedOptions].map(option => option.value);
                            formData.set('sdgs', JSON.stringify(selectedSdgs));
                        }
                        
                        // Add personnel data - gather all personnel with personnelId
                        const personnelData = [];
                        // Get ALL name fields including dynamically added ones
                        const personnelFields = document.querySelectorAll('input[id*="_name"]');

                        console.log(`Found ${personnelFields.length} total personnel name fields`);

                        personnelFields.forEach((field, index) => {
                            // Skip empty fields
                            if (!field.value || field.value.trim() === '') {
                                console.log(`Skipping empty personnel field: ${field.id}`);
                                return;
                            }
                            
                            // Check if the field has a personnel-id attribute
                            let personnelId = 0;
                            if (field.hasAttribute('data-personnel-id')) {
                                personnelId = parseInt(field.getAttribute('data-personnel-id'), 10);
                            } else if (field.dataset && field.dataset.personnelId) {
                                personnelId = parseInt(field.dataset.personnelId, 10);
                            }
                            
                            console.log(`Checking personnel #${index+1}: id=${field.id}, name="${field.value}", personnel-id=${personnelId}`);
                            
                            // Skip invalid personnel IDs
                            if (isNaN(personnelId) || personnelId <= 0) {
                                console.error(`Invalid personnel ID (${personnelId}) for ${field.value}, skipping`);
                                return;
                            }
                            
                            // Determine role based on field id
                            let role = '';
                            if (field.id.includes('project_leader')) {
                                role = 'Project Leader';
                            } else if (field.id.includes('asst_leader')) {
                                role = 'Assistant Project Leader';
                            } else if (field.id.includes('staff')) {
                                role = 'Staff';
                            } else if (field.id.includes('other')) {
                                role = 'Other Internal Participants';
                            } else {
                                console.log(`Skipping field with unknown role: ${field.id}`);
                                return;
                            }
                            
                            // Add to personnel data
                            personnelData.push({
                                personnel_id: personnelId,
                                role: role,
                                name: field.value
                            });
                            
                            console.log(`Added personnel to submission: name="${field.value}", role="${role}", id=${personnelId}`);
                        });

                        console.log(`Total personnel to be submitted: ${personnelData.length}`);
                        console.log('Personnel data JSON:', JSON.stringify(personnelData));

                        formData.append('personnel', JSON.stringify(personnelData));
                        
                        // Send to server
                        fetch('save_ppas.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            // Check if response is ok
                            if (!response.ok) {
                                throw new Error(`Server responded with status: ${response.status}`);
                            }
                            
                            // Check content-type to ensure it's JSON
                            const contentType = response.headers.get('content-type');
                            if (!contentType || !contentType.includes('application/json')) {
                                // If not JSON, get text and throw error with the content
                                return response.text().then(text => {
                                    console.error('Server returned non-JSON response:', text);
                                    throw new Error('Server returned non-JSON response');
                                });
                            }
                            
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Show success message
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'PPAS form has been saved successfully.',
                                    icon: 'success',
                                    timer: 1500,
                                    timerProgressBar: true,
                                    showConfirmButton: false, // Remove the OK button
                                    allowOutsideClick: false,
                                    backdrop: `rgba(0,0,0,0.7)`,
                                    customClass: {
                                        container: 'swal-blur-container'
                                    }
                                }).then(() => {
                                    // Refresh the page instead of resetting the form
                                    window.location.reload();
                                });
                            } else {
                                // Check if it's a duplicate activity error
                                if (data.message && data.message.includes('activity with the same name already exists')) {
                                    // Don't show modal - instead highlight field and show error
                                    console.log("Duplicate activity detected, showing inline validation");
                                    
                                    // Find and mark the activity field as invalid
                                    if (activityField) {
                                        activityField.classList.add('is-invalid');
                                        activityField.dataset.duplicate = 'true';
                                        
                                        // Set error message
                                        const feedback = activityField.nextElementSibling;
                                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                                            feedback.textContent = 'This activity already exists. Please enter a unique activity name.';
                                            feedback.style.display = 'block';
                                        }
                                        
                                        // Navigate to basic info section and focus the field
                                        const basicInfoNavItem = document.querySelector('.form-nav-item[data-section="basic-info"]');
                                        if (basicInfoNavItem) {
                                            basicInfoNavItem.classList.add('invalid');
                                            basicInfoNavItem.classList.add('has-error');
                                            basicInfoNavItem.classList.remove('completed');
                                            
                                            // Navigate to basic info section
                                            basicInfoNavItem.click();
                                            // Scroll to the activity field and focus it
                                            setTimeout(() => {
                                                activityField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                                activityField.focus();
                                            }, 100);
                                        }
                                    }
                                } else {
                                    // Show error modal for other types of errors
                                    Swal.fire({
                                        title: 'Error!',
                                        text: data.message || 'Failed to save PPAS form.',
                                        icon: 'error'
                                    });
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error saving form:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An unexpected error occurred. Please check the server logs for details.',
                                icon: 'error'
                            });
                        });
                    } else {
                        console.log('Form has validation errors, submission prevented');
                    }
                });
            }
        });

        // Call validateSectionCompleteness on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initial validation to mark completed sections
            setTimeout(validateSectionCompleteness, 500);
        });

        // Ensure validation happens immediately on DOM ready and after a short delay
        window.addEventListener('DOMContentLoaded', function() {
            console.log('DOM content loaded - running initial validation');
            validateSectionCompleteness(); // Immediate check
            
            // Also run after a short delay to ensure all elements are fully initialized
            setTimeout(validateSectionCompleteness, 500);
            setTimeout(validateSectionCompleteness, 1000);
        });
        
        // Also run validation as soon as the page is fully loaded
        window.addEventListener('load', function() {
            console.log('Window fully loaded - running validation');
            validateSectionCompleteness();
            
            // Add a check after a short delay
            setTimeout(validateSectionCompleteness, 100);
        });

        // Add autocomplete functionality for program and project fields
        document.addEventListener('DOMContentLoaded', function() {
            // Setup autocomplete for all fields with 'autocomplete' class
            const autocompleteInputs = document.querySelectorAll('.autocomplete');
            
            autocompleteInputs.forEach(input => {
                const field = input.getAttribute('data-field');
                const autocompleteContainer = document.getElementById(`${field}-autocomplete`);
                let typingTimer;
                const doneTypingInterval = 300; // Wait 300ms after user stops typing
                
                // Add event listener for input
                input.addEventListener('input', function() {
                    const value = this.value.trim();
                    
                    // Clear the previous timer
                    clearTimeout(typingTimer);
                    
                    // Hide autocomplete list if input is empty
                    if (value.length < 2) {
                        autocompleteContainer.style.display = 'none';
                        autocompleteContainer.innerHTML = '';
                        return;
                    }
                    
                    // Set a new timer
                    typingTimer = setTimeout(() => {
                        fetchAutocompleteData(field, value, autocompleteContainer);
                    }, doneTypingInterval);
                });
                
                // Handle click outside to close autocomplete
                document.addEventListener('click', function(e) {
                    if (e.target !== input && e.target !== autocompleteContainer) {
                        autocompleteContainer.style.display = 'none';
                    }
                });
                
                // Add keyboard navigation
                input.addEventListener('keydown', function(e) {
                    const items = autocompleteContainer.querySelectorAll('div');
                    if (!items.length) return;
                    
                    let activeItem = autocompleteContainer.querySelector('.autocomplete-active');
                    const index = Array.from(items).indexOf(activeItem);
                    
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        removeActive(items);
                        if (activeItem && index < items.length - 1) {
                            items[index + 1].classList.add('autocomplete-active');
                        } else {
                            items[0].classList.add('autocomplete-active');
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        removeActive(items);
                        if (activeItem && index > 0) {
                            items[index - 1].classList.add('autocomplete-active');
                        } else {
                            items[items.length - 1].classList.add('autocomplete-active');
                        }
                    } else if (e.key === 'Enter' && activeItem) {
                        e.preventDefault();
                        input.value = activeItem.textContent;
                        autocompleteContainer.style.display = 'none';
                    } else if (e.key === 'Escape') {
                        autocompleteContainer.style.display = 'none';
                    }
                });
            });
            
            function removeActive(items) {
                items.forEach(item => {
                    item.classList.remove('autocomplete-active');
                });
            }
            
            function fetchAutocompleteData(field, term, container) {
                // Fetch data from the server
                fetch(`get_autocomplete_data.php?field=${field}&term=${encodeURIComponent(term)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        container.innerHTML = '';
                        
                        if (!data.success || !data.data.length) {
                            container.style.display = 'none';
                            return;
                        }
                        
                        // Create and append items
                        data.data.forEach(item => {
                            const div = document.createElement('div');
                            div.textContent = item;
                            
                            // Add click event
                            div.addEventListener('click', function() {
                                const input = document.getElementById(field);
                                input.value = this.textContent;
                                container.style.display = 'none';
                            });
                            
                            container.appendChild(div);
                        });
                        
                        container.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error fetching autocomplete data:', error);
                        container.style.display = 'none';
                    });
            }
        });

        // Add functionality to check for duplicate activities
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Setting up activity duplicate validation');
            
            // Get required fields for validation
            const activityField = document.getElementById('activity');
            const campusField = document.getElementById('campus');
            const yearField = document.getElementById('year');
            const quarterField = document.getElementById('quarter');
            
            if (!activityField) {
                console.error('Activity field not found for duplicate validation');
                return;
            }
            
            // Keep track of validation timeout
            let activityValidationTimeout;
            
            // Function to check for duplicate activities in real-time
            function setupActivityValidation() {
                console.log('Activity validation setup started');
                
                // Remove existing event listeners to prevent duplicates
                activityField.removeEventListener('input', onActivityInput);
                activityField.removeEventListener('blur', onActivityBlur);
                
                if (yearField) {
                    yearField.removeEventListener('change', onYearQuarterChange);
                }
                
                if (quarterField) {
                    quarterField.removeEventListener('change', onYearQuarterChange);
                }
                
                if (campusField) {
                    campusField.removeEventListener('change', onYearQuarterChange);
                }
                
                // Remove form navigation listeners
                document.querySelectorAll('.form-nav-item').forEach(item => {
                    item.removeEventListener('click', onNavItemClick);
                });
                
                // Define the event handler functions
                function onActivityInput() {
                    console.log('Activity input detected: ' + this.value);
                    
                    // Clear previous timer and validation state
                    clearTimeout(activityValidationTimeout);
                    
                    // Don't clear validation state during typing to prevent flickering
                    // Only clear validation when user has made changes to existing value
                    if (activityField.dataset.lastValue !== activityField.value) {
                        activityField.classList.remove('is-invalid');
                        activityField.dataset.duplicate = 'false';
                        
                        const feedback = activityField.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = '';
                            feedback.style.display = 'none';
                        }
                        
                        // Clear error marking from tab
                        const basicInfoNavItem = document.querySelector('.form-nav-item[data-section="basic-info"]');
                        if (basicInfoNavItem) {
                            basicInfoNavItem.classList.remove('invalid');
                            basicInfoNavItem.classList.remove('has-error');
                        }
                        
                        // No longer need to clear the section-title class
                    }
                    
                    // Store current value for comparison
                    activityField.dataset.lastValue = activityField.value;
                    
                    // Check if all required fields have values - now we only need activity and campus
                    const canValidate = activityField.value.trim() && campusField.value.trim();
                    
                    if (canValidate) {
                        // If all required fields have values, validate immediately
                        console.log('Required fields have values, validating immediately');
                        checkDuplicateActivity();
                    } else {
                        // Otherwise set timer for validation
                        activityValidationTimeout = setTimeout(() => {
                            // Check again if required fields have values before validating
                            if (activityField.value.trim() && campusField.value.trim()) {
                                checkDuplicateActivity();
                            }
                        }, 500);
                    }
                }
                
                function onActivityBlur() {
                    console.log('Activity blur detected: ' + this.value);
                    clearTimeout(activityValidationTimeout);
                    
                    // Check if all required fields have values before validating
                    if (activityField.value.trim() && campusField.value.trim()) {
                        console.log('Required fields have values on blur, validating immediately');
                        checkDuplicateActivity();
                    }
                }
                
                function onYearQuarterChange() {
                    console.log('Year/Quarter/Campus changed');
                    // We no longer need to validate on year/quarter change
                    // since they're not used in the duplicate check
                }
                
                // Add navigation validation
                function onNavItemClick() {
                    console.log('Navigation item clicked, validating if needed');
                    // Validate if all required fields have values and we're leaving the basic info section
                    if (this.getAttribute('data-section') !== 'basic-info' && 
                        activityField.value.trim() && campusField.value.trim()) {
                        console.log('Validating on tab change');
                        checkDuplicateActivity();
                    }
                }
                
                // Add input event listener with debounce
                activityField.addEventListener('input', onActivityInput);
                
                // Add blur event for immediate validation when focus leaves the field
                activityField.addEventListener('blur', onActivityBlur);
                
                // Also validate when year, quarter or campus changes
                if (yearField) {
                    yearField.addEventListener('change', onYearQuarterChange);
                }
                
                if (quarterField) {
                    quarterField.addEventListener('change', onYearQuarterChange);
                }
                
                if (campusField) {
                    campusField.addEventListener('change', onYearQuarterChange);
                }
                
                // Add validation on form navigation
                document.querySelectorAll('.form-nav-item').forEach(item => {
                    item.addEventListener('click', onNavItemClick);
                });
                
                // Validate immediately if all fields have values
                if (activityField.value.trim() && campusField.value.trim() && 
                    yearField.value.trim() && quarterField.value.trim()) {
                    console.log('All fields have values on page load, validating immediately');
                    // Small delay to ensure all DOM elements are ready
                    setTimeout(checkDuplicateActivity, 100);
                }
                
                console.log('Activity validation setup completed');
            }
            
            // Function to check if activity is a duplicate
            function checkDuplicateActivity(retryCount = 0) {
                const activity = activityField.value.trim();
                const campus = campusField.value.trim();
                
                // We still send year and quarter parameters for backward compatibility
                // But they're not used in the duplicate check anymore
                const year = yearField ? yearField.value.trim() : '';
                const quarter = quarterField ? quarterField.value.trim() : '';
                
                // Get the current entry ID if we're in edit mode
                const addBtn = document.getElementById('addBtn');
                let currentId = 0;
                let isUpdate = false;
                
                if (addBtn && addBtn.getAttribute('data-action') === 'update') {
                    currentId = addBtn.getAttribute('data-entry-id') || 0;
                    isUpdate = true;
                }
                
                console.log(`Checking duplicate: "${activity}", Campus: "${campus}", CurrentID: ${currentId}`);
                
                // Skip validation if any required field is empty
                if (!activity || !campus) {
                    console.log('Skipping validation - missing required fields');
                    return;
                }
                
                // Skip validation if we're in update mode and the activity name hasn't changed
                if (isUpdate && window.originalActivityName && activity === window.originalActivityName) {
                    console.log('Skipping duplicate check - activity name unchanged in update mode');
                    // Clear any existing duplicate status
                    activityField.dataset.duplicate = 'false';
                    activityField.classList.remove('is-invalid');
                    
                    // Clear error message if it exists
                    const feedback = activityField.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.style.display = 'none';
                    }
                    return;
                }
                
                // Show loading state
                activityField.classList.add('is-validating');
                
                // Generate a unique request ID to prevent caching
                const uniqueId = Date.now() + Math.random().toString(36).substring(2);
                
                // Call API to check for duplicates
                console.log('Calling duplicate check API');
                fetch(`check_duplicate_activity.php?activity=${encodeURIComponent(activity)}&campus=${encodeURIComponent(campus)}&year=${encodeURIComponent(year)}&quarter=${encodeURIComponent(quarter)}&currentId=${currentId}&_nocache=${uniqueId}`, {
                    method: 'GET',
                    cache: 'no-cache', // Add cache control to prevent cached responses
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                })
                    .then(response => {
                        console.log('API Response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Remove loading state
                        activityField.classList.remove('is-validating');
                        console.log('Duplicate check result:', data);
                        
                        if (data.success) {
                            // Mark as duplicate if exists
                            if (data.exists) {
                                console.log('Duplicate activity found!');
                                activityField.classList.add('is-invalid');
                                activityField.dataset.duplicate = 'true';
                                
                                // Update feedback message
                                const feedback = activityField.nextElementSibling;
                                if (feedback && feedback.classList.contains('invalid-feedback')) {
                                    feedback.textContent = 'This activity already exists. Please enter a unique activity name.';
                                    feedback.style.display = 'block';
                                } else {
                                    // Create feedback element if it doesn't exist
                                    const newFeedback = document.createElement('div');
                                    newFeedback.className = 'invalid-feedback';
                                    newFeedback.textContent = 'This activity already exists. Please enter a unique activity name.';
                                    newFeedback.style.display = 'block';
                                    activityField.parentNode.insertBefore(newFeedback, activityField.nextSibling);
                                }
                                
                                // Mark the Basic Info tab as invalid
                                const basicInfoNavItem = document.querySelector('.form-nav-item[data-section="basic-info"]');
                                if (basicInfoNavItem) {
                                    basicInfoNavItem.classList.add('invalid');
                                    basicInfoNavItem.classList.add('has-error');
                                    basicInfoNavItem.classList.remove('completed');
                                }
                                
                                // No longer mark the section title as invalid
                            } else {
                                console.log('Activity is unique');
                                activityField.classList.remove('is-invalid');
                                activityField.dataset.duplicate = 'false';
                                
                                // Remove error marking from tab if this was the only error
                                // We need to revalidate the whole section
                                setTimeout(validateSectionCompleteness, 100);
                            }
                        } else {
                            console.error('Duplicate check failed:', data.message);
                            // Retry if necessary
                            if (retryCount < 2) {
                                console.log(`Retrying duplicate check (attempt ${retryCount + 1})`);
                                setTimeout(() => checkDuplicateActivity(retryCount + 1), 500);
                            }
                        }
                    })
                    .catch(error => {
                        // Remove loading state on error
                        activityField.classList.remove('is-validating');
                        console.error('Error checking for duplicate activity:', error);
                        
                        // Retry if necessary
                        if (retryCount < 2) {
                            console.log(`Retrying duplicate check after error (attempt ${retryCount + 1})`);
                            setTimeout(() => checkDuplicateActivity(retryCount + 1), 1000);
                        }
                    });
            }
            
            // Run setup once DOM is ready
            setupActivityValidation();
            
            // Also run setup after a delay to ensure all elements are properly initialized
            setTimeout(setupActivityValidation, 500);
        });

        // Add functionality to implement sequential form field dependencies
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Setting up sequential form field dependencies');
            
            // Get all the relevant fields
            const yearField = document.getElementById('year');
            const quarterField = document.getElementById('quarter');
            const genderIssueField = document.getElementById('gender_issue');
            const programField = document.getElementById('program');
            const projectField = document.getElementById('project');
            const activityField = document.getElementById('activity');
            
            if (!yearField || !quarterField || !genderIssueField || !programField || !projectField || !activityField) {
                console.error('Could not find all required fields for sequential validation:',
                    { 
                        year: !!yearField,
                        quarter: !!quarterField,
                        genderIssue: !!genderIssueField, 
                        program: !!programField, 
                        project: !!projectField, 
                        activity: !!activityField 
                    }
                );
                return; // Exit if fields can't be found
            }
            
            console.log('Sequential field dependencies: Found all required fields');
            
            // Function to disable a field and add a placeholder message
            function disableField(field, message) {
                if (!field) return;
                
                field.disabled = true;
                field.placeholder = message;
                field.classList.add('disabled-field');
                
                // Add a special class to the parent form-group for styling
                const formGroup = field.closest('.form-group');
                if (formGroup) {
                    formGroup.classList.add('field-disabled');
                    formGroup.dataset.hint = message;
                }
            }
            
            // Function to enable a field
            function enableField(field, defaultPlaceholder) {
                if (!field) return;
                
                field.disabled = false;
                field.placeholder = defaultPlaceholder || '';
                field.classList.remove('disabled-field');
                
                // Remove disabled class from parent
                const formGroup = field.closest('.form-group');
                if (formGroup) {
                    formGroup.classList.remove('field-disabled');
                    delete formGroup.dataset.hint;
                }
            }
            
            // Function to check if both year and quarter have values
            function canEnableGenderIssue() {
                return yearField.value.trim() && quarterField.value.trim();
            }
            
            // Function to check if gender issue can be enabled and update the UI accordingly
            function updateGenderIssueState() {
                if (canEnableGenderIssue()) {
                    console.log('Year and quarter have values, enabling gender issue field');
                    enableField(genderIssueField, 'Enter gender issue');
                } else {
                    console.log('Year or quarter missing, disabling gender issue field');
                    disableField(genderIssueField, 'First select year and quarter');
                    // Cascade disabling down the chain
                    disableField(programField, 'First enter a gender issue');
                    disableField(projectField, 'First enter a program');
                    disableField(activityField, 'First enter a project');
                }
            }
            
            // Initial setup - disable fields that should not be interactive yet
            updateGenderIssueState();
            disableField(programField, 'First enter a gender issue');
            disableField(projectField, 'First enter a program');
            disableField(activityField, 'First enter a project');
            
            // Add event listeners to enable/disable fields based on values
            
            // Enable gender issue field when both year and quarter have values
            if (yearField) {
                yearField.addEventListener('change', function() {
                    // Skip if form is in edit mode
                    if (document.querySelector('.card-header .card-title').textContent.includes('Edit')) {
                        console.log('Skipping year field dependency check in edit mode');
                        return;
                    }
                    updateGenderIssueState();
                });
            }
            
            if (quarterField) {
                quarterField.addEventListener('change', function() {
                    // Skip if form is in edit mode
                    if (document.querySelector('.card-header .card-title').textContent.includes('Edit')) {
                        console.log('Skipping quarter field dependency check in edit mode');
                        return;
                    }
                    updateGenderIssueState();
                });
            }
            
            // Enable program field when gender issue has a value
            if (genderIssueField) {
                genderIssueField.addEventListener('input', function() {
                    // Skip if we're in edit mode
                    if (this.classList.contains('edit-mode-enabled')) {
                        console.log('Skipping field dependency check in edit mode for gender_issue');
                        return;
                    }
                    
                    if (this.value.trim()) {
                        enableField(programField, 'Enter program name');
                    } else {
                        disableField(programField, 'First enter a gender issue');
                        disableField(projectField, 'First enter a program');
                        disableField(activityField, 'First enter a project');
                    }
                });
                
                // Check if gender issue already has a value on page load
                if (genderIssueField.value.trim() && canEnableGenderIssue()) {
                    enableField(programField, 'Enter program name');
                }
            }
            
            // Enable project field when program has a value
            if (programField) {
                programField.addEventListener('input', function() {
                    // Skip if we're in edit mode
                    if (this.classList.contains('edit-mode-enabled')) {
                        console.log('Skipping field dependency check in edit mode for program');
                        return;
                    }
                    
                    if (this.value.trim()) {
                        enableField(projectField, 'Enter project name');
                    } else {
                        disableField(projectField, 'First enter a program');
                        disableField(activityField, 'First enter a project');
                    }
                });
                
                // Check if program already has a value on page load
                if (programField.value.trim()) {
                    enableField(projectField, 'Enter project name');
                }
            }
            
            // Enable activity field when project has a value
            if (projectField) {
                projectField.addEventListener('input', function() {
                    // Skip if we're in edit mode
                    if (this.classList.contains('edit-mode-enabled')) {
                        console.log('Skipping field dependency check in edit mode for project');
                        return;
                    }
                    
                    if (this.value.trim()) {
                        enableField(activityField, 'Enter activity name');
                    } else {
                        disableField(activityField, 'First enter a project');
                    }
                });
                
                // Check if project already has a value on page load
                if (projectField.value.trim()) {
                    enableField(activityField, 'Enter activity name');
                }
            }
            
            console.log('Sequential field dependencies setup completed');
        });

        document.addEventListener('DOMContentLoaded', function() {
            // ... existing code ...
            
            // Get the add button
            const addBtn = document.getElementById('addBtn');
            if (addBtn) {
                // Save the original click handler
                const originalClickHandler = addBtn.onclick;
                
                // Replace with our custom handler
                addBtn.addEventListener('click', function(e) {
                    // Check for duplicate activity before proceeding
                    const activityField = document.getElementById('activity');
                    
                    // For update mode, check if activity name is unchanged
                    const isUpdateMode = this.getAttribute('data-action') === 'update';
                    const isActivityUnchanged = isUpdateMode && 
                                               window.originalActivityName && 
                                               activityField.value.trim() === window.originalActivityName;
                    
                    // Only block if it's marked as duplicate AND (not in update mode OR activity changed)
                    if (activityField && 
                        activityField.dataset.duplicate === 'true' && 
                        (!isUpdateMode || !isActivityUnchanged)) {
                        
                        // Prevent default form submission
                        e.preventDefault();
                        
                        console.log('Duplicate activity detected when adding form');
                        
                        // Mark field as invalid
                        activityField.classList.add('is-invalid');
                        
                        // Add invalid feedback message if it doesn't exist
                        let feedbackElement = activityField.nextElementSibling;
                        if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
                            feedbackElement = document.createElement('div');
                            feedbackElement.className = 'invalid-feedback';
                            activityField.parentNode.insertBefore(feedbackElement, activityField.nextSibling);
                        }
                        
                        feedbackElement.style.display = 'block';
                        feedbackElement.textContent = 'This activity already exists. Please enter a unique activity name.';
                        
                        // Mark the Basic Info tab as invalid
                        const basicInfoNavItem = document.querySelector('.form-nav-item[data-section="basic-info"]');
                        if (basicInfoNavItem) {
                            basicInfoNavItem.classList.add('invalid');
                            basicInfoNavItem.classList.add('has-error');
                            basicInfoNavItem.classList.remove('completed');
                        }
                        
                        return false;
                    }
                    
                    // If no duplicate, let the original handler run
                    return true;
                });
            }
        });

        function validateTabContent(tabId) {
            let isValid = true;
            
            if (tabId === 'basic-info-tab-pane') {
                // Validate Basic Info fields
                const campus = document.getElementById('campus');
                const activity = document.getElementById('activity');
                
                if (!campus || !campus.value.trim()) {
                    isValid = false;
                    markFieldAsInvalid(campus, 'Please select a campus');
                } else {
                    markFieldAsValid(campus);
                }
                
                if (!activity || !activity.value.trim()) {
                    isValid = false;
                    markFieldAsInvalid(activity, 'Please enter an activity name');
                } else {
                    // Check if it's already been marked as a duplicate
                    if (activity.dataset.duplicate === 'true') {
                        isValid = false;
                        markFieldAsInvalid(activity, 'This activity already exists. Please enter a unique name.');
                    } else {
                        markFieldAsValid(activity);
                    }
                }
                
                // Year and quarter are handled by the sequential validation logic
                // but are not required for duplicate validation anymore
                
                updateTabState('basic-info-tab', isValid);
            }
        }

        // Function to open the PPAS data modal
        function openPpasModal(mode = 'view') {
            // Set the modal mode
            currentModalMode = mode;
            
            // Update the modal title based on mode
            const modalTitle = document.getElementById('ppasDataModalTitle');
            switch (mode) {
                case 'edit':
                    modalTitle.textContent = 'Edit PPAS Activities';
                    
                    // When opening in edit mode, reset the form and set today's date
                    setTimeout(() => {
                        const ppasForm = document.getElementById('ppasForm');
                        if (ppasForm) {
                            ppasForm.reset();
                            setTodayDates();
                            console.log('Form reset and dates set to today in edit mode');
                        }
                    }, 100); // Small delay to ensure the modal is open first
                    break;
                case 'delete':
                    modalTitle.textContent = 'Delete PPAS Activities';
                    break;
                default:
                    modalTitle.textContent = 'View PPAS Activities';
            }
            
            const modalOverlay = document.getElementById('ppasDataModalOverlay');
            modalOverlay.style.display = 'flex';
            
            // Update table wrapper class based on mode
            const tableWrapper = document.querySelector('.ppas-data-table-wrapper');
            tableWrapper.classList.remove('view-mode', 'edit-mode', 'delete-mode');
            tableWrapper.classList.add(`${mode}-mode`);
            
            // Trigger the CSS transition by adding the active class after a small delay
            setTimeout(() => {
                modalOverlay.classList.add('active');
            }, 10);
            document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
            
            // Load years first, then load data
            loadYears();
            loadPpasData();
        }
        
        // Function to close the PPAS data modal
        function closePpasModal() {
            document.getElementById('ppasDataModalOverlay').style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore scrolling
        }

        // Add event listener to view button
        document.addEventListener('DOMContentLoaded', function() {
            const viewBtn = document.getElementById('viewBtn');
            if (viewBtn) {
                viewBtn.addEventListener('click', function() {
                    openPpasModal('view');
                });
            }
            
            // Also add event listeners to edit and delete buttons to open the modal
            const editBtn = document.getElementById('editBtn');
            if (editBtn) {
                editBtn.addEventListener('click', function() {
                    openPpasModal('edit');
                });
            }
            
            const deleteBtn = document.getElementById('deleteBtn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    openPpasModal('delete');
                });
            }
        });
    </script>
    
    <!-- PPAS Data Modal -->
    <div class="modal-overlay" id="ppasDataModalOverlay" style="display: none;" onclick="closePpasModal()">
        <div class="ppas-data-modal" onclick="event.stopPropagation()">
            <div class="ppas-data-modal-header">
                <h3 id="ppasDataModalTitle">PPAS Activities Data</h3>
                <button class="close-modal-btn" onclick="closePpasModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="ppas-data-modal-body">
                <!-- Filters -->
                <div class="filter-container">
                    <div class="filter-row">
                        <div class="filter-item">
                            <label for="filter-activity">Activity:</label>
                            <input type="text" id="filter-activity" class="form-control" placeholder="Search activities..." onkeyup="loadPpasData()">
                        </div>
                        <div class="filter-item">
                            <label for="filter-year">Year:</label>
                            <select id="filter-year" class="form-control" onchange="loadPpasData()">
                                <option value="">All Years</option>
                                <!-- Years will be populated dynamically -->
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="filter-quarter">Quarter:</label>
                            <select id="filter-quarter" class="form-control" onchange="loadPpasData()">
                                <option value="">All Quarters</option>
                                <option value="Q1">Q1</option>
                                <option value="Q2">Q2</option>
                                <option value="Q3">Q3</option>
                                <option value="Q4">Q4</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="filter-campus">Campus:</label>
                            <?php if ($_SESSION['username'] === 'Central'): ?>
                            <select id="filter-campus" class="form-control" onchange="loadYears(); loadPpasData();">
                                <option value="All Campuses">All Campuses</option>
                                <?php
                                // Include database configuration
                                require_once '../config.php';
                                
                                // Query to get all distinct campuses
                                $campusQuery = "SELECT DISTINCT campus FROM ppas_forms ORDER BY campus";
                                $campusResult = $conn->query($campusQuery);
                                
                                if ($campusResult && $campusResult->num_rows > 0) {
                                    while ($campusRow = $campusResult->fetch_assoc()) {
                                        if (!empty($campusRow['campus'])) {
                                            echo "<option value='" . htmlspecialchars($campusRow['campus']) . "'>" . htmlspecialchars($campusRow['campus']) . "</option>";
                                        }
                                    }
                                }
                                ?>
                            </select>
                            <?php else: ?>
                            <input type="text" id="filter-campus" class="form-control" value="<?php echo $_SESSION['username']; ?>" readonly>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Data Table -->
                <div class="ppas-data-table-wrapper">
                    <table class="ppas-data-table">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Quarter</th>
                                <th>Gender Issue</th>
                                <th>Project</th>
                                <th>Program</th>
                                <th>Activity</th>
                                <th>Approved Budget</th>
                            </tr>
                        </thead>
                        <tbody id="ppas-data-tbody">
                            <!-- Data will be populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Modal Footer with Pagination -->
            <div class="ppas-data-modal-footer">
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
    
    <script>
        // Global variables for pagination
        let currentPage = 1;
        let totalPages = 1;
        let ppasData = [];
        const rowsPerPage = 5; // Changed from 8 to 5
        let currentModalMode = 'view'; // can be 'view', 'edit', or 'delete'
        let selectedPpasId = null; // To store the ID of the selected PPAS entry
        let originalFormState = {}; // To store the original form state for cancellation
        
        // Function to open the PPAS data modal
        function openPpasModal(mode = 'view') {
            // Set the modal mode
            currentModalMode = mode;
            
            // Update the modal title based on mode
            const modalTitle = document.getElementById('ppasDataModalTitle');
            switch (mode) {
                case 'edit':
                    modalTitle.textContent = 'Edit PPAS Activities';
                    
                    // When opening in edit mode, reset the form and set today's date
                    setTimeout(() => {
                        const ppasForm = document.getElementById('ppasForm');
                        if (ppasForm) {
                            ppasForm.reset();
                            setTodayDates();
                            console.log('Form reset and dates set to today in edit mode');
                        }
                    }, 100); // Small delay to ensure the modal is open first
                    break;
                case 'delete':
                    modalTitle.textContent = 'Delete PPAS Activities';
                    break;
                default:
                    modalTitle.textContent = 'View PPAS Activities';
            }
            
            const modalOverlay = document.getElementById('ppasDataModalOverlay');
            modalOverlay.style.display = 'flex';
            
            // Update table wrapper class based on mode
            const tableWrapper = document.querySelector('.ppas-data-table-wrapper');
            tableWrapper.classList.remove('view-mode', 'edit-mode', 'delete-mode');
            tableWrapper.classList.add(`${mode}-mode`);
            
            // Trigger the CSS transition by adding the active class after a small delay
            setTimeout(() => {
                modalOverlay.classList.add('active');
            }, 10);
            document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
            
            // Load years first, then load data
            loadYears();
            loadPpasData();
        }
        
        // Function to load years based on campus filter
        function loadYears() {
            const campusFilter = document.getElementById('filter-campus').value;
            const yearSelect = document.getElementById('filter-year');
            const selectedYear = yearSelect.value; // Store the currently selected year
            
            // Clear existing options except "All Years"
            while (yearSelect.options.length > 1) {
                yearSelect.remove(1);
            }
            
            // Add loading option
            const loadingOption = document.createElement('option');
            loadingOption.disabled = true;
            loadingOption.text = 'Loading years...';
            yearSelect.add(loadingOption);
            
            // Fetch years from server
            fetch(`get_ppas_years.php?campus=${encodeURIComponent(campusFilter)}`)
                .then(response => response.json())
                .then(data => {
                    // Remove loading option
                    yearSelect.remove(yearSelect.options.length - 1);
                    
                    if (data.success) {
                        // Add years to dropdown
                        data.years.forEach(year => {
                            const option = document.createElement('option');
                            option.value = year;
                            option.text = year;
                            yearSelect.add(option);
                        });
                        
                        // Try to restore the previously selected year if it exists in the new options
                        if (selectedYear) {
                            for (let i = 0; i < yearSelect.options.length; i++) {
                                if (yearSelect.options[i].value === selectedYear) {
                                    yearSelect.selectedIndex = i;
                                    break;
                                }
                            }
                        }
                    } else {
                        console.error('Error loading years:', data.message);
                        // Add a placeholder option
                        const errorOption = document.createElement('option');
                        errorOption.disabled = true;
                        errorOption.text = 'Error loading years';
                        yearSelect.add(errorOption);
                    }
                })
                .catch(error => {
                    console.error('Error fetching years:', error);
                    // Remove loading option
                    yearSelect.remove(yearSelect.options.length - 1);
                    // Add a placeholder option
                    const errorOption = document.createElement('option');
                    errorOption.disabled = true;
                    errorOption.text = 'Error loading years';
                    yearSelect.add(errorOption);
                });
        }
        
        function closePpasModal() {
            const modalOverlay = document.getElementById('ppasDataModalOverlay');
            modalOverlay.classList.remove('active');
            // Wait for the transition to complete before hiding the element
            setTimeout(() => {
                modalOverlay.style.display = 'none';
                document.body.style.overflow = 'auto'; // Restore scrolling
            }, 300); // Match this to the transition duration in CSS
        }
        
        // Override loadPpasData function to remove loading state
        function loadPpasData() {
            const activityFilter = document.getElementById('filter-activity').value;
            const yearFilter = document.getElementById('filter-year').value;
            const quarterFilter = document.getElementById('filter-quarter').value;
            let campusFilter = document.getElementById('filter-campus').value;
            
            // For Central user with "All Campuses", set to empty string for API
            if (campusFilter === 'All Campuses') {
                campusFilter = '';
            }
            
            // Fetch data from server without showing loading state
            fetch('get_ppas_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    activity: activityFilter,
                    year: yearFilter,
                    quarter: quarterFilter,
                    campus: campusFilter
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    ppasData = data.data;
                    totalPages = Math.ceil(ppasData.length / rowsPerPage);
                    
                    // Reset to first page when filters change
                    currentPage = 1;
                    
                    // Update display
                    updatePaginationDisplay();
                    renderCurrentPage();
                } else {
                    document.getElementById('ppas-data-tbody').innerHTML = 
                        '<tr><td colspan="7" class="text-center">Error loading data: ' + data.message + '</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error fetching PPAS data:', error);
                document.getElementById('ppas-data-tbody').innerHTML = 
                    '<tr><td colspan="7" class="text-center">Error loading data. Please try again.</td></tr>';
            });
        }
        
        // Function to change the current page
        function changePage(direction) {
            const newPage = currentPage + direction;
            
            if (newPage >= 1 && newPage <= totalPages) {
                currentPage = newPage;
                updatePaginationDisplay();
                renderCurrentPage();
            }
        }
        
        // Function to update pagination buttons and display
        function updatePaginationDisplay() {
            const pagination = document.getElementById('entriesPagination');
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
                    renderCurrentPage();
                    updatePaginationDisplay();
                }
                return false;
            });
            
            pagination.appendChild(prevLi);
            
            // Create page number buttons
            // Show max 5 pages with current page centered when possible
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            
            // Adjust start page if end page is maxed out
            if (endPage === totalPages && totalPages > 5) {
                startPage = Math.max(1, endPage - 4);
            }
            
            for (let i = startPage; i <= endPage; i++) {
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
                    renderCurrentPage();
                    updatePaginationDisplay();
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
                    renderCurrentPage();
                    updatePaginationDisplay();
                }
                return false;
            });
            
            pagination.appendChild(nextLi);
        }
        
        // Function to render the current page of data with row click behavior
        function renderCurrentPage() {
            const tbody = document.getElementById('ppas-data-tbody');
            tbody.innerHTML = '';
            
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = Math.min(startIndex + rowsPerPage, ppasData.length);
            
            if (ppasData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">No data found</td></tr>';
                return;
            }
            
            for (let i = startIndex; i < endIndex; i++) {
                const item = ppasData[i];
                const row = document.createElement('tr');
                
                // Add row click handler based on mode
                if (currentModalMode === 'edit') {
                    row.onclick = function() {
                        loadPpasEntryForEdit(item.id);
                    };
                } else if (currentModalMode === 'delete') {
                    row.onclick = function() {
                        confirmDeletePpas(item);
                    };
                }
                
                row.innerHTML = `
                    <td>${item.year}</td>
                    <td>${item.quarter}</td>
                    <td>${item.gender_issue}</td>
                    <td>${item.project}</td>
                    <td>${item.program}</td>
                    <td>${item.activity}</td>
                    <td>₱${parseFloat(item.approved_budget).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                `;
                
                tbody.appendChild(row);
            }
        }

        // Function to load PPAS entry for editing
        function loadPpasEntryForEdit(id) {
            // Close the modal
            closePpasModal();
            
            // Store the selected PPAS ID
            selectedPpasId = id;
            
            // Add a script tag to load our debug helper
            const debugScript = document.createElement('script');
            debugScript.src = 'populatePpasForm.js?v=' + Date.now(); // Cache buster
            document.body.appendChild(debugScript);
            
            // Store gender_issue_id globally so it's accessible to the debug script
            window.currentGenderIssueId = null;
            
            // Fetch the PPAS entry data
            fetch(`get_ppas_entry.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    console.log('PPAS entry data received:', data);
                    if (data.success) {
                        // Store the gender_issue_id globally so our debug script can access it
                        if (data.entry && data.entry.gender_issue_id) {
                            window.currentGenderIssueId = data.entry.gender_issue_id;
                            console.log('Stored gender_issue_id globally:', window.currentGenderIssueId);
                        }
                        
                        // Store original form state for cancellation
                        storeOriginalFormState();
                        
                        // Populate the form with data
                        populatePpasForm(data.entry);
                        
                        // Change card title
                        document.querySelector('.card-header .card-title').textContent = 'Edit PPAS Form';
                        
                        // Change add button to update button
                        const addBtn = document.getElementById('addBtn');
                        if (addBtn) {
                            addBtn.innerHTML = '<i class="fas fa-save"></i>';
                            addBtn.setAttribute('data-action', 'update');
                            addBtn.setAttribute('data-entry-id', id);
                        }
                        
                        // Transform edit button to cancel button
                        const editBtn = document.getElementById('editBtn');
                        if (editBtn) {
                            editBtn.innerHTML = '<i class="fas fa-times"></i>';
                            // Use the delete button's exact color palette
                            editBtn.style.background = 'rgba(220, 53, 69, 0.1)';
                            editBtn.style.color = '#dc3545';
                            editBtn.style.borderColor = '#dc3545';
                            
                            // Set hover styles via class
                            editBtn.classList.add('editing');
                            
                            editBtn.setAttribute('data-original-action', 'edit');
                            editBtn.setAttribute('data-action', 'cancel');
                            
                            // Store the original click handler and replace it
                            editBtn.onclick = function() {
                                cancelEdit();
                                return false;
                            };
                        }
                        
                        // Disable delete button
                        const deleteBtn = document.getElementById('deleteBtn');
                        if (deleteBtn) {
                            deleteBtn.disabled = true;
                            deleteBtn.classList.add('disabled');
                        }
                        
                        // Scroll to top
                        document.querySelector('.main-content').scrollTop = 0;
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || 'Failed to load PPAS data',
                            icon: 'error',
                            confirmButtonColor: '#6A1B9A'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to load PPAS data',
                        icon: 'error',
                        confirmButtonColor: '#6A1B9A'
                    });
                });
        }

        // Store the original form state for cancellation
        function storeOriginalFormState() {
            originalFormState = {
                title: document.querySelector('.card-header .card-title').textContent,
                addBtnHTML: document.getElementById('addBtn').innerHTML,
                editBtnHTML: document.getElementById('editBtn').innerHTML,
                editBtnClass: document.getElementById('editBtn').className,
                deleteBtnDisabled: document.getElementById('deleteBtn').disabled
            };
        }

        // Populate form with PPAS entry data
        function populatePpasForm(entry) {
            console.log('Populating form with entry data:', entry);
            console.log('Gender issue ID value:', entry.gender_issue_id);
            
            // Store original activity name for duplicate checking during updates
            if (entry.activity) {
                window.originalActivityName = entry.activity.trim();
                console.log('Stored original activity name:', window.originalActivityName);
            }
            
            // Populate basic info fields
            const setValueIfElementExists = (id, value) => {
                const element = document.getElementById(id);
                if (element) {
                    console.log(`Setting ${id} to value:`, value);
                    element.value = value;
                } else {
                    console.log(`Element with ID ${id} not found`);
                }
            };
            
            // Basic info fields
            setValueIfElementExists('year', entry.year || '');
            setValueIfElementExists('quarter', entry.quarter || '');
            
            // Before we try to set the gender issue value, make sure all dependent fields are properly set
            // Since gender issue field depends on year and quarter
            if (typeof enableFormFields === 'function') {
                console.log('Enabling all form fields to ensure gender issue field is available');
                enableFormFields();
            }
            
            // We need to delay setting the gender issue to ensure the dropdown has been populated
            setTimeout(() => {
                // Special handling for gender_issue dropdown
                const genderIssueElement = document.getElementById('gender_issue');
                if (genderIssueElement) {
                    // Make sure the element is enabled
                    genderIssueElement.disabled = false;
                    
                    // Find the form group and remove any disabled classes
                    const formGroup = genderIssueElement.closest('.form-group');
                    if (formGroup) {
                        formGroup.classList.remove('field-disabled');
                        delete formGroup.dataset.hint;
                    }
                    
                    // Try to set the gender issue ID
                    if (entry.gender_issue_id) {
                        console.log('Setting gender issue dropdown to:', entry.gender_issue_id);
                        genderIssueElement.value = entry.gender_issue_id;
                        
                        // Log whether the value was actually set
                        console.log('Gender issue dropdown value after setting:', genderIssueElement.value);
                        
                        // If the value is still empty, the option may not exist in the dropdown
                        if (!genderIssueElement.value) {
                            console.warn('Gender issue option not found in dropdown. Available options:');
                            Array.from(genderIssueElement.options).forEach(option => {
                                console.log(`Option: value=${option.value}, text=${option.text}`);
                            });
                        }
                        
                        // Force a change event regardless
                        const event = new Event('change');
                        genderIssueElement.dispatchEvent(event);
                    } else {
                        console.warn('No gender_issue_id found in the entry data');
                    }
                } else {
                    console.error('Gender issue element not found');
                }
            }, 500); // Delay to ensure dropdown is ready
            
            setValueIfElementExists('project', entry.project || '');
            setValueIfElementExists('program', entry.program || '');
            setValueIfElementExists('activity', entry.activity || '');
            
            // Location & Date fields
            setValueIfElementExists('location', entry.location || '');
            if(entry.start_date) setValueIfElementExists('start_date', entry.start_date);
            if(entry.end_date) setValueIfElementExists('end_date', entry.end_date);
            setValueIfElementExists('start_time', entry.start_time || '');
            setValueIfElementExists('end_time', entry.end_time || '');
            setValueIfElementExists('total_duration', entry.total_duration_hours || '');
            
            // Lunch break radio buttons
            console.log('Setting lunch break value:', entry.lunch_break);
            if (entry.lunch_break === 'with' || entry.lunch_break === '1' || entry.lunch_break === 1) {
                const withLunchRadio = document.getElementById('with_lunch');
                if (withLunchRadio) {
                    withLunchRadio.checked = true;
                    console.log('Set lunch break to WITH');
                }
            } else if (entry.lunch_break === 'without' || entry.lunch_break === '0' || entry.lunch_break === 0) {
                const noLunchRadio = document.getElementById('no_lunch');
                if (noLunchRadio) {
                    noLunchRadio.checked = true;
                    console.log('Set lunch break to WITHOUT');
                }
            }
            
            // Beneficiaries fields
            setValueIfElementExists('students_male', entry.students_male || '0');
            setValueIfElementExists('students_female', entry.students_female || '0');
            setValueIfElementExists('faculty_male', entry.faculty_male || '0');
            setValueIfElementExists('faculty_female', entry.faculty_female || '0');
            setValueIfElementExists('total_internal_male', entry.total_internal_male || '0');
            setValueIfElementExists('total_internal_female', entry.total_internal_female || '0');
            setValueIfElementExists('external_type', entry.external_type || '');
            setValueIfElementExists('external_male', entry.external_male || '0');
            setValueIfElementExists('external_female', entry.external_female || '0');
            setValueIfElementExists('total_male', entry.total_male || '0');
            setValueIfElementExists('total_female', entry.total_female || '0');
            setValueIfElementExists('total_beneficiaries', entry.total_beneficiaries || '0');
            
            // Budget & SDGs fields
            setValueIfElementExists('approved_budget', entry.approved_budget || '0');
            setValueIfElementExists('source_budget', entry.source_of_budget || '');
            console.log('Source of budget from DB:', entry.source_of_budget);
            console.log('Source budget dropdown set to:', document.getElementById('source_budget')?.value);
            
            // Populate PS attribution if it exists
            setValueIfElementExists('ps_attribution', entry.ps_attribution || '');
            
            // Populate SDGs if it exists and has a Select2 initialization
            if (document.getElementById('sdgs') && $.fn.select2) {
                try {
                    console.log('SDGs from database:', entry.sdgs);
                    
                    // Handle different formats that might be returned
                    let sdgsArray = [];
                    
                    if (entry.sdgs) {
                        // Check if it's already an array
                        if (Array.isArray(entry.sdgs)) {
                            sdgsArray = entry.sdgs;
                        } 
                        // Check if it's a JSON string
                        else if (typeof entry.sdgs === 'string' && entry.sdgs.startsWith('[')) {
                            try {
                                sdgsArray = JSON.parse(entry.sdgs);
                            } catch (parseErr) {
                                console.error('Error parsing JSON SDGs:', parseErr);
                                // Fallback to comma split
                                sdgsArray = entry.sdgs.split(',');
                            }
                        } 
                        // Otherwise assume comma-separated string
                        else if (typeof entry.sdgs === 'string') {
                            sdgsArray = entry.sdgs.split(',');
                        }
                    }
                    
                    console.log('Parsed SDGs array:', sdgsArray);
                    
                    // Clean up the array (remove empty values, trim)
                    sdgsArray = sdgsArray.filter(sdg => sdg.trim() !== '');
                    sdgsArray = sdgsArray.map(sdg => sdg.trim());
                    
                    console.log('Final SDGs array to set:', sdgsArray);
                    
                    // Set the values and force Select2 to update
                    $('#sdgs').val(sdgsArray).trigger('change');
                    
                    // Verify the values were set
                    setTimeout(() => {
                        const setValues = $('#sdgs').val();
                        console.log('SDGs values after setting:', setValues);
                    }, 500);
                } catch (e) {
                    console.error('Error setting SDGs:', e);
                }
            }
            
            // Fetch and populate personnel data
            if (entry.id) {
                fetchPersonnel(entry.id);
            }
            
            // Enable all fields that might have been disabled due to sequential validation
            if (typeof enableFormFields === 'function') {
                enableFormFields();
            }
            
            // Add a final check to ensure all fields remain editable
            setTimeout(() => {
                console.log('Final check to ensure all fields remain editable');
                if (typeof enableFormFields === 'function') {
                    enableFormFields();
                }
                
                // Directly ensure critical fields are enabled
                const criticalFields = ['gender_issue', 'program', 'project', 'activity'];
                criticalFields.forEach(id => {
                    const field = document.getElementById(id);
                    if (field) {
                        field.disabled = false;
                        console.log(`Final check for ${id}: disabled=${field.disabled}`);
                    }
                });
            }, 1000); // Delay to ensure any event handlers have finished
        }

        // Function to fetch personnel associated with a PPAS form
        function fetchPersonnel(ppasFormId) {
            console.log(`Fetching personnel for PPAS form ID: ${ppasFormId}`);
            
            fetch(`get_ppas_personnel.php?id=${ppasFormId}`)
                .then(response => {
                    console.log('Personnel response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Personnel data received:', data);
                    
                    if (data.success) {
                        populatePersonnel(data.personnel);
                    } else {
                        console.error('Error fetching personnel:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Function to populate personnel fields
        function populatePersonnel(personnelData) {
            console.log('Populating personnel with data:', personnelData);
            
            // Clear existing personnel selections
            clearPersonnelSelections();
            
            if (!personnelData || personnelData.length === 0) {
                console.log('No personnel data to populate');
                return;
            }
            
            // Map database role names to code role keys
            const roleMapping = {
                'Project Leader': 'project_leader',
                'Assistant Project Leader': 'asst_leader',
                'Staff': 'staff',
                'Other Internal Participants': 'other'
            };
            
            // Group personnel by role, using the mapping
            const personnelByRole = {};
            personnelData.forEach(p => {
                const roleKey = roleMapping[p.role] || p.role;
                console.log(`Mapping role: ${p.role} to ${roleKey}`);
                
                if (!personnelByRole[roleKey]) {
                    personnelByRole[roleKey] = [];
                }
                personnelByRole[roleKey].push(p);
            });
            
            console.log('Personnel grouped by role (after mapping):', personnelByRole);
            
            // Populate project leaders
            if (personnelByRole['project_leader']) {
                const leaders = personnelByRole['project_leader'];
                console.log('Populating project leaders:', leaders);
                
                if (leaders.length > 0) {
                    // Set first project leader
                    setPersonnelSelection('project_leader', leaders[0]);
                    
                    // Add additional project leaders if any
                    for (let i = 1; i < leaders.length; i++) {
                        console.log(`Adding additional project leader ${i}:`, leaders[i]);
                        // Directly call the original function to avoid wrapper
                        if (typeof window.originalAddPersonnel === 'function') {
                            window.originalAddPersonnel('project_leader', leaders[i]);
                        } else if (typeof addPersonnel === 'function') {
                            addPersonnel('project_leader', leaders[i]);
                        } else {
                            console.error('addPersonnel function not found');
                        }
                    }
                }
            }
            
            // Populate assistant project leaders
            if (personnelByRole['asst_leader']) {
                const asstLeaders = personnelByRole['asst_leader'];
                console.log('Populating assistant leaders:', asstLeaders);
                
                if (asstLeaders.length > 0) {
                    // Set first asst leader
                    setPersonnelSelection('asst_leader', asstLeaders[0]);
                    
                    // Add additional asst leaders if any
                    for (let i = 1; i < asstLeaders.length; i++) {
                        console.log(`Adding additional assistant leader ${i}:`, asstLeaders[i]);
                        if (typeof addPersonnel === 'function') {
                            addPersonnel('asst_leader', asstLeaders[i]);
                        }
                    }
                }
            }
            
            // Populate staff/coordinators
            if (personnelByRole['staff']) {
                const staffMembers = personnelByRole['staff'];
                console.log('Populating staff:', staffMembers);
                
                if (staffMembers.length > 0) {
                    // Set first staff member
                    setPersonnelSelection('staff', staffMembers[0]);
                    
                    // Add additional staff members if any
                    for (let i = 1; i < staffMembers.length; i++) {
                        console.log(`Adding additional staff ${i}:`, staffMembers[i]);
                        if (typeof addPersonnel === 'function') {
                            addPersonnel('staff', staffMembers[i]);
                        }
                    }
                }
            }
            
            // Populate other participants
            if (personnelByRole['other']) {
                const others = personnelByRole['other'];
                console.log('Populating other participants:', others);
                
                if (others.length > 0) {
                    // Set first other participant
                    setPersonnelSelection('other', others[0]);
                    
                    // Add additional other participants if any
                    for (let i = 1; i < others.length; i++) {
                        console.log(`Adding additional participant ${i}:`, others[i]);
                        if (typeof addPersonnel === 'function') {
                            addPersonnel('other', others[i]);
                        }
                    }
                }
            }
        }

        // Function to set a personnel selection in the form
        function setPersonnelSelection(role, personnel) {
            console.log(`Setting ${role} personnel:`, personnel);
            
            let nameField, genderField, rankField, salaryField, rateField;
            
            switch(role) {
                case 'leader':
                case 'project_leader': // Add project_leader as valid role
                    nameField = document.getElementById('project_leader_name');
                    genderField = document.getElementById('project_leader_gender');
                    rankField = document.getElementById('project_leader_rank');
                    salaryField = document.getElementById('project_leader_salary');
                    rateField = document.getElementById('project_leader_rate');
                    break;
                case 'asst_leader':
                    nameField = document.getElementById('asst_leader_name');
                    genderField = document.getElementById('asst_leader_gender');
                    rankField = document.getElementById('asst_leader_rank');
                    salaryField = document.getElementById('asst_leader_salary');
                    rateField = document.getElementById('asst_leader_rate');
                    break;
                case 'staff':
                    nameField = document.getElementById('staff_name');
                    genderField = document.getElementById('staff_gender');
                    rankField = document.getElementById('staff_rank');
                    salaryField = document.getElementById('staff_salary');
                    rateField = document.getElementById('staff_rate');
                    break;
                case 'other':
                    nameField = document.getElementById('other_name');
                    genderField = document.getElementById('other_gender');
                    rankField = document.getElementById('other_rank');
                    salaryField = document.getElementById('other_salary');
                    rateField = document.getElementById('other_rate');
                    break;
            }
            
            if (nameField) {
                console.log(`Found ${role} name field:`, nameField.id);
                
                // Directly set the fields instead of relying on change event
                nameField.value = personnel.name || '';
                
                if (genderField) genderField.value = personnel.gender || '';
                if (rankField) rankField.value = personnel.academic_rank || '';
                if (salaryField) salaryField.value = personnel.monthly_salary || '';
                if (rateField) rateField.value = personnel.rate_per_hour || '';
                
                // Store additional data
                if (nameField.dataset) {
                    nameField.dataset.personnelId = personnel.personnel_id;
                }
                
                // Also try to trigger change event as a backup
                try {
                    if (typeof jQuery !== 'undefined') {
                        $(nameField).trigger('change');
                    } else {
                        const event = new Event('change');
                        nameField.dispatchEvent(event);
                    }
                } catch (e) {
                    console.error('Error triggering change event:', e);
                }
                
                console.log(`${role} fields set:`, {
                    name: nameField.value,
                    gender: genderField?.value,
                    rank: rankField?.value,
                    salary: salaryField?.value,
                    rate: rateField?.value
                });
            } else {
                console.error(`Name field for ${role} not found`);
            }
        }

        // Function to clear all personnel selections
        function clearPersonnelSelections() {
            const roles = ['project_leader', 'asst_leader', 'staff', 'other'];
            
            roles.forEach(role => {
                const nameField = document.getElementById(`${role}_name`);
                const genderField = document.getElementById(`${role}_gender`);
                const rankField = document.getElementById(`${role}_rank`);
                const salaryField = document.getElementById(`${role}_salary`);
                const rateField = document.getElementById(`${role}_rate`);
                
                if (nameField) nameField.value = '';
                if (genderField) genderField.value = '';
                if (rankField) rankField.value = '';
                if (salaryField) salaryField.value = '';
                if (rateField) rateField.value = '';
                
                // Clear additional containers if they exist (for multiple personnel)
                const container = document.getElementById(`${role}-container`);
                if (container && container.querySelectorAll) {
                    const additionalFields = container.querySelectorAll('.additional-personnel');
                    additionalFields.forEach(field => field.remove());
                }
            });
        }

        // Enable all form fields that might be disabled by sequential validation
        function enableFormFields() {
            const fields = ['gender_issue', 'program', 'project', 'activity'];
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.disabled = false;
                    
                    // Remove disabled class from parent
                    const formGroup = field.closest('.form-group');
                    if (formGroup) {
                        formGroup.classList.remove('field-disabled');
                        delete formGroup.dataset.hint;
                    }
                }
            });
            
            // This is critical - temporarily remove the event listeners that would
            // re-disable fields when in edit mode
            if (document.querySelector('.card-header .card-title').textContent.includes('Edit')) {
                console.log('Edit mode detected - ensuring fields remain interactive');
                
                // Force enable all fields
                const yearField = document.getElementById('year');
                const quarterField = document.getElementById('quarter');
                const genderIssueField = document.getElementById('gender_issue');
                const programField = document.getElementById('program');
                const projectField = document.getElementById('project');
                const activityField = document.getElementById('activity');
                
                // For each field, log its state before and after enabling
                [genderIssueField, programField, projectField, activityField].forEach(field => {
                    if (field) {
                        console.log(`${field.id} before: disabled=${field.disabled}`);
                        field.disabled = false;
                        console.log(`${field.id} after: disabled=${field.disabled}`);
                        
                        // Create a special class for edit mode fields
                        field.classList.add('edit-mode-enabled');
                    }
                });
            }
        }

        // Cancel edit and restore original form state
        function cancelEdit() {
            // Restore original form state
            document.querySelector('.card-header .card-title').textContent = originalFormState.title;
            
            const addBtn = document.getElementById('addBtn');
            if (addBtn) {
                addBtn.innerHTML = originalFormState.addBtnHTML;
                addBtn.removeAttribute('data-action');
                addBtn.removeAttribute('data-entry-id');
            }
            
            const editBtn = document.getElementById('editBtn');
            if (editBtn) {
                editBtn.innerHTML = originalFormState.editBtnHTML;
                editBtn.className = originalFormState.editBtnClass;
                
                // Reset inline styles
                editBtn.style.background = '';
                editBtn.style.color = '';
                editBtn.style.borderColor = '';
                
                // Remove editing class
                editBtn.classList.remove('editing');
                editBtn.classList.remove('btn-danger');
                
                editBtn.removeAttribute('data-original-action');
                editBtn.removeAttribute('data-action');
                
                // Restore original click handler
                editBtn.onclick = function() {
                    openPpasModal('edit');
                    return false;
                };
            }
            
            const deleteBtn = document.getElementById('deleteBtn');
            if (deleteBtn) {
                deleteBtn.disabled = originalFormState.deleteBtnDisabled;
                deleteBtn.classList.remove('disabled');
            }
            
            // Clear stored original activity name
            if (window.originalActivityName) {
                delete window.originalActivityName;
                console.log('Cleared stored original activity name');
            }
            
            // Reset form fields
            document.getElementById('ppasForm').reset();
            
            // Set today's date for date fields
            setTodayDates();
            
            // Reset selected PPAS ID
            selectedPpasId = null;
            
            // Reopen edit modal
            openPpasModal('edit');
        }
        
        // Function to set today's date for date fields
        function setTodayDates() {
            // Format today's date as YYYY-MM-DD
            const today = new Date();
            const todayString = today.getFullYear() + '-' + 
                String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                String(today.getDate()).padStart(2, '0');
            
            // Get the date fields
            const startDateField = document.getElementById('start_date');
            const endDateField = document.getElementById('end_date');
            
            // Set dates with visual verification
            if (startDateField) {
                startDateField.value = todayString;
                console.log('Set start_date to:', todayString);
            }
            
            if (endDateField) {
                endDateField.value = todayString;
                console.log('Set end_date to:', todayString);
            }
            
            // Also update the display fields if they exist
            const startDateDisplay = document.getElementById('start_date_display');
            const endDateDisplay = document.getElementById('end_date_display');
            
            if (startDateDisplay) {
                const formattedDate = today.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                startDateDisplay.value = formattedDate;
            }
            
            if (endDateDisplay) {
                const formattedDate = today.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                endDateDisplay.value = formattedDate;
            }
            
            // Also set the dropdown values if they exist
            const startDay = document.getElementById('start_day');
            const startMonth = document.getElementById('start_month');
            const startYear = document.getElementById('start_year');
            const endDay = document.getElementById('end_day');
            const endMonth = document.getElementById('end_month');
            const endYear = document.getElementById('end_year');
            
            if (startDay) startDay.value = String(today.getDate()).padStart(2, '0');
            if (startMonth) startMonth.value = String(today.getMonth() + 1).padStart(2, '0');
            if (startYear) startYear.value = today.getFullYear().toString();
            if (endDay) endDay.value = String(today.getDate()).padStart(2, '0');
            if (endMonth) endMonth.value = String(today.getMonth() + 1).padStart(2, '0');
            if (endYear) endYear.value = today.getFullYear().toString();
        }

        // Confirm delete for a PPAS entry
        function confirmDeletePpas(item) {
            Swal.fire({
                title: 'Confirm Deletion',
                html: `
                    <div class="text-left">
                        <p>Are you sure you want to delete this PPAS activity?</p>
                        <ul class="list-unstyled">
                            <li><strong>Activity:</strong> ${item.activity}</li>
                            <li><strong>Year:</strong> ${item.year}</li>
                            <li><strong>Quarter:</strong> ${item.quarter}</li>
                            <li><strong>Budget:</strong> ₱${parseFloat(item.approved_budget).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</li>
                        </ul>
                        <p class="text-danger">This action cannot be undone!</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                backdrop: `rgba(0,0,0,0.7)`,
                allowOutsideClick: false,
                customClass: {
                    container: 'swal-delete-container'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    deletePpasEntry(item.id);
                }
            });
        }

        // Delete PPAS entry
        function deletePpasEntry(id) {
            fetch('delete_ppas_entry.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The PPAS activity has been deleted successfully.',
                        icon: 'success',
                        confirmButtonColor: '#6A1B9A',
                        timer: 1500,
                        backdrop: `rgba(0,0,0,0.7)`,
                        showConfirmButton: false
                    });
                    
                    // Reload PPAS data after deletion
                    loadPpasData();
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Failed to delete PPAS activity',
                        icon: 'error',
                        confirmButtonColor: '#6A1B9A'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to delete PPAS activity',
                    icon: 'error',
                    confirmButtonColor: '#6A1B9A'
                });
            });
        }
    </script>
</body>
</html>
