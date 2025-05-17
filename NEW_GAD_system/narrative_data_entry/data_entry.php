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
            --purple-color: #6a1b9a;
            --purple-light: #8e24aa;
            --purple-dark: #4a148c;
            --bg-soft: #f8f5fc; /* Soft light purple background */
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
            --bg-soft: #2a1f33; /* Soft dark purple background */
        }

        /* Custom purple classes */
        .bg-purple {
            background-color: var(--purple-color) !important;
        }
        
        .bg-purple-light {
            background-color: var(--purple-light) !important;
        }
        
        .bg-soft {
            background-color: var(--bg-soft) !important;
        }
        
        .text-purple {
            color: var(--purple-color) !important;
        }
        
        .border-purple {
            border-color: var(--purple-color) !important;
        }
        
        .border-purple-light {
            border-color: var(--purple-light) !important;
        }
        
        /* Table purple styles */
        .table-purple {
            background-color: var(--purple-color);
            color: white;
        }
        
        .table-purple th {
            font-weight: 500;
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        [data-bs-theme="light"] .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: rgba(106, 27, 154, 0.03);
        }
        
        [data-bs-theme="light"] .table-hover > tbody > tr:hover {
            background-color: rgba(106, 27, 154, 0.07);
        }
        
        /* Header box styles */
        .header-box {
            background-color: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            flex: 1;
            min-width: 120px;
        }
        
        .header-box i {
            font-size: 1.25rem;
            display: block;
        }
        
        /* Button styles */
        .btn-purple-outline {
            color: var(--purple-color);
            border-color: var(--purple-color);
            background-color: transparent;
        }
        
        .btn-purple-outline:hover {
            color: white;
            background-color: var(--purple-color);
            border-color: var(--purple-color);
        }
        
        /* Modal enhancements */
        .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .modal .card {
            transition: all 0.3s ease;
        }
        
        /* Narrative details section styling */
        .narrative-section .section-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1);
        }
        
        .content-box {
            border-left: 4px solid var(--purple-color);
        }
        
        /* Photo card enhancements */
        .photo-card {
            transition: all 0.25s ease;
        }
        
        .photo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
        }
        
        .photo-card .photo-img {
            transition: transform 0.4s ease;
        }
        
        .photo-card:hover .photo-img {
            transform: scale(1.08);
        }
        
        .photo-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            padding: 8px 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .photo-card:hover .photo-overlay {
            opacity: 1;
        }
        
        /* Animation for modal */
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
            transform: scale(0.95);
        }
        
        .modal.show .modal-dialog {
            transform: scale(1);
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
            color: var(--text-primary);
        }

        /* Light mode hover color */
        [data-bs-theme="light"] .nav-link:hover {
            color: var(--text-primary);
            background: var(--hover-color);
        }

        /* Dark mode hover color */
        [data-bs-theme="dark"] .nav-link:hover {
            color: var(--text-primary);
            background: var(--hover-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-menu .dropdown-item:hover {
            color: var(--text-primary);
            background-color: var(--hover-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--accent-color) !important;
        }

        .nav-link.active {
            color: var(--accent-color) !important;
            position: relative;
        }

        /* Light theme - active nav links should be purple */
        [data-bs-theme="light"] .nav-link.active {
            color: var(--accent-color) !important;
        }

        /* Dark theme - active nav links should be purple */
        [data-bs-theme="dark"] .nav-link.active {
            color: var(--accent-color) !important;
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

        /* Update hover state for active nav links */
        .nav-link.active:hover {
            color: var(--accent-color) !important;
        }

        /* Dark mode hover state for active nav links */
        [data-bs-theme="dark"] .nav-link.active:hover {
            color: white !important;
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
            color: var(--text-primary);
            background-color: var(--hover-color);
        }

        [data-bs-theme="dark"] .nav-item .dropdown-menu .dropdown-item:hover {
            color: var(--text-primary);
            background-color: var(--hover-color);
        }

        .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--text-primary) !important;
            background: var(--hover-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--text-primary) !important;
            background: var(--hover-color);
        }

        [data-bs-theme="dark"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--text-primary) !important;
            background: var(--hover-color);
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
            overflow: hidden;
            border: none;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            position: relative;
            min-height: 465px;
            border-radius: 15px;
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

        /* ... existing code ... */
        .card-header {
            background: #ffffff;
            color: #333333;
            font-weight: 500;
            border-bottom: 1px solid #e0e0e0;
            padding: 15px 20px;
            border-top-left-radius: inherit !important;
            border-top-right-radius: inherit !important;
            padding-bottom: 0.5rem !important;
        }

        /* Dark theme card header */
        [data-bs-theme="dark"] .card-header {
            background: #2B3035;
            color: white;
            font-weight: 500;
            border-bottom: none;
            padding: 15px 20px;
        }
        /* ... existing code ... */

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
        #deleteBtn.btn-disabled {
            background: rgba(108, 117, 125, 0.1) !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            opacity: 0.6 !important;
            border: 1px solid #6c757d !important;
        }

        /* Dark theme support for disabled delete button */
        [data-bs-theme="dark"] #deleteBtn.btn-disabled {
            background: rgba(108, 117, 125, 0.1) !important;
            color: #adb5bd !important;
            border-color: #495057 !important;
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
        
        /* Card title link styling to match card-title */
        .card-title-link {
            color: var(--accent-color);
            font-weight: 500;
            font-size: 1rem;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        .card-title-link:hover {
            background-color: rgba(106, 27, 154, 0.1);
            color: var(--accent-hover);
        }
        
        [data-bs-theme="dark"] .card-title-link {
            color: var(--purple-light);
        }
        
        [data-bs-theme="dark"] .card-title-link:hover {
            background-color: rgba(156, 39, 176, 0.2);
            color: var(--purple-light);
        }
        
        /* Card header link styling */
        .card-header-link {
            color: #ffffff;
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 6px;
            background-color: rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        .card-header-link:hover {
            background-color: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }
        
        [data-bs-theme="dark"] .card-header-link {
            background-color: rgba(0, 0, 0, 0.2);
        }
        
        [data-bs-theme="dark"] .card-header-link:hover {
            background-color: rgba(0, 0, 0, 0.3);
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
            margin-bottom: 20px;
            position: relative;
            min-height: 465px;
            border-radius: 15px;
        }
        
        .card-header {
            background: #ffffff;
            color: #333333;
            font-weight: 500;
            border-bottom: 1px solid #e0e0e0;
            padding: 15px 20px;
            border-top-left-radius: inherit !important;
            border-top-right-radius: inherit !important;
        }
        
        [data-bs-theme="dark"] .card-header {
            background: #2B3035;
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

        /* Evaluation results section spacing */
        .evaluation-table {
            margin-bottom: 25px;
        }
        
        .evaluation-table:last-child {
            margin-bottom: 0;
        }
        
        /* Disabled file upload styling */
        .custom-file-upload.disabled label {
            background-color: #e9ecef !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            opacity: 0.65;
        }

        /* Readonly title field styling in edit mode */
        #title.form-select[readonly] {
            background-color: #2B3035;
            color: #ffffff;
            border-color: #2B3035;
            opacity: 0.9;
            cursor: not-allowed;
            pointer-events: none;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        /* Dark theme support for readonly title field */
        [data-bs-theme="dark"] #title.form-select[readonly] {
            background-color: #2B3035;
            color: #ffffff;
            border-color: #6c757d;
            pointer-events: none;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        
        /* Disable selection of options with narratives (red color) */
        #title option[data-has-narrative="true"] {
            color: #ff0000;
            background-color: #f8d7da;
            cursor: not-allowed;
            pointer-events: none;
            opacity: 0.7;
            text-decoration: line-through;
            font-style: italic;
        }

        /* Filter styles */
        [data-bs-theme="light"] .campus-filter-select {
            background-color: #ffffff;
            color: #212529;
            border-color: #ced4da;
        }
        
        [data-bs-theme="dark"] .campus-filter-select {
            background-color: #404040;
            color: #ffffff;
            border-color: #6c757d;
        }
        
        /* All Campuses filter button */
        [data-bs-theme="light"] .all-campuses-filter {
            background-color: #ffffff !important;
            color: #212529 !important;
            border: 1px solid #ced4da !important;
            border-radius: 5px;
            padding: 8px 12px;
            transition: all 0.3s ease;
        }
        
        [data-bs-theme="dark"] .all-campuses-filter {
            background-color: #2d2d2d !important;
            color: #ffffff !important;
            border: 1px solid #404040 !important;
        }
        
        .all-campuses-filter:hover {
            background-color: #f8f9fa !important;
        }
        
        /* Ensure list group items with "All Campuses" text have white background in light mode */
        [data-bs-theme="light"] .list-group-item:contains('All Campuses'),
        [data-bs-theme="light"] button:contains('All Campuses'),
        [data-bs-theme="light"] [role="button"]:contains('All Campuses') {
            background-color: #ffffff !important;
            color: #212529 !important;
            border: 1px solid #ced4da !important;
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
        <div class="page-title d-flex align-items-center">
            <i class="fas fa-users-gear"></i>
            <h2>Narrative Report Data Entry</h2>
            
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Narrative Report Entry Form</h5>
                <a href="../gad_narrative/gad_narrative.php" class="card-header-link">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
            <div class="card-body">
                <form id="narrativeForm" method="post" enctype="multipart/form-data">
                    <!-- Add MAX_FILE_SIZE hidden field -->
                    <input type="hidden" name="MAX_FILE_SIZE" value="67108864"> <!-- 64MB in bytes -->
                    <input type="hidden" id="ppas_form_id" name="ppas_form_id" value="">
                    
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3">
                            <label for="campus" class="form-label">Campus</label>
                            <select class="form-select" id="campus" name="campus" required <?php if(!$isCentral) echo 'disabled'; ?>>
                                <option value="" selected disabled>Select Campus</option>
                                <option value="Central">Central</option>
                                <option value="Alangilan">Alangilan</option>
                                <option value="Arasof-Nasugbu">Arasof-Nasugbu</option>
                                <option value="Balayan">Balayan</option>
                                <option value="Lemery">Lemery</option>
                                <option value="Lipa">Lipa</option>
                                <option value="Lobo">Lobo</option>
                                <option value="Mabini">Mabini</option>
                                <option value="Malvar">Malvar</option>
                                <option value="Pablo Borbon">Pablo Borbon</option>
                                <option value="Rosario">Rosario</option>
                                <option value="San Juan">San Juan</option>
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
                            <label for="evaluation" class="form-label">Evaluation Results</label>
                            <div class="table-responsive">
                                <table class="table table-bordered evaluation-table">
                                    <thead>
                                        <tr>
                                            <th scope="col" style="width: 25%">Scale</th>
                                            <th scope="col">BatStateU Participants</th>
                                            <th scope="col">Participants from other Institutions</th>
                                            <th scope="col" style="width: 15%">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">Excellent</th>
                                            <td><input type="number" class="form-control activity-rating" min="0" data-row="excellent" data-col="batstateu"></td>
                                            <td><input type="number" class="form-control activity-rating" min="0" data-row="excellent" data-col="others"></td>
                                            <td><input type="number" class="form-control activity-total" readonly data-row="excellent"></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Very Satisfactory</th>
                                            <td><input type="number" class="form-control activity-rating" min="0" data-row="very" data-col="batstateu"></td>
                                            <td><input type="number" class="form-control activity-rating" min="0" data-row="very" data-col="others"></td>
                                            <td><input type="number" class="form-control activity-total" readonly data-row="very"></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Satisfactory</th>
                                            <td><input type="number" class="form-control activity-rating" min="0" data-row="satisfactory" data-col="batstateu"></td>
                                            <td><input type="number" class="form-control activity-rating" min="0" data-row="satisfactory" data-col="others"></td>
                                            <td><input type="number" class="form-control activity-total" readonly data-row="satisfactory"></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Fair</th>
                                            <td><input type="number" class="form-control activity-rating" min="0" data-row="fair" data-col="batstateu"></td>
                                            <td><input type="number" class="form-control activity-rating" min="0" data-row="fair" data-col="others"></td>
                                            <td><input type="number" class="form-control activity-total" readonly data-row="fair"></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Poor</th>
                                            <td><input type="number" class="form-control activity-rating" min="0" data-row="poor" data-col="batstateu"></td>
                                            <td><input type="number" class="form-control activity-rating" min="0" data-row="poor" data-col="others"></td>
                                            <td><input type="number" class="form-control activity-total" readonly data-row="poor"></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Total</th>
                                            <td><input type="number" class="form-control activity-col-total" readonly data-col="batstateu"></td>
                                            <td><input type="number" class="form-control activity-col-total" readonly data-col="others"></td>
                                            <td><input type="number" class="form-control activity-grand-total" readonly></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-12 mt-4">
                                <label class="form-label">Number of Beneficiaries who rated The Timeliness of the activity as:</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered evaluation-table">
                                        <thead>
                                            <tr>
                                                <th scope="col" style="width: 25%">Scale</th>
                                                <th scope="col">BatStateU Participants</th>
                                                <th scope="col">Participants from other Institutions</th>
                                                <th scope="col" style="width: 15%">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th scope="row">Excellent</th>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="excellent" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="excellent" data-col="others"></td>
                                                <td><input type="number" class="form-control timeliness-total" readonly data-row="excellent"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Very Satisfactory</th>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="very" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="very" data-col="others"></td>
                                                <td><input type="number" class="form-control timeliness-total" readonly data-row="very"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Satisfactory</th>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="satisfactory" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="satisfactory" data-col="others"></td>
                                                <td><input type="number" class="form-control timeliness-total" readonly data-row="satisfactory"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Fair</th>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="fair" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="fair" data-col="others"></td>
                                                <td><input type="number" class="form-control timeliness-total" readonly data-row="fair"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Poor</th>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="poor" data-col="batstateu"></td>
                                                <td><input type="number" class="form-control timeliness-rating" min="0" data-row="poor" data-col="others"></td>
                                                <td><input type="number" class="form-control timeliness-total" readonly data-row="poor"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Total</th>
                                                <td><input type="number" class="form-control timeliness-col-total" readonly data-col="batstateu"></td>
                                                <td><input type="number" class="form-control timeliness-col-total" readonly data-col="others"></td>
                                                <td><input type="number" class="form-control timeliness-grand-total" readonly></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <input type="hidden" id="evaluation" name="evaluation">
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
                                    <div class="custom-file-upload">
                                        <input type="file" class="d-none" id="photoUpload" name="photoUpload[]" accept="image/*" multiple>
                                        <label for="photoUpload" class="btn btn-outline-primary w-100 py-2">
                                            <i class="fas fa-cloud-upload-alt me-2"></i> Upload Images
                                        </label>
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
    </div>
    
    <!-- Narrative Details Modal -->
    <div class="modal fade" id="narrativeDetailsModal" tabindex="-1" aria-labelledby="narrativeDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-purple text-white rounded-0">
                    <h5 class="modal-title" id="narrativeDetailsModalLabel">Narrative Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 bg-soft" id="narrativeDetailsModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer bg-soft rounded-0">
                    <button type="button" class="btn btn-purple-outline" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteNarrativeModal" tabindex="-1" aria-labelledby="deleteNarrativeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-purple text-white rounded-0">
                    <h5 class="modal-title" id="deleteNarrativeModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-soft" id="deleteNarrativeModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer bg-soft rounded-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Narrative List Modal -->
    <div class="modal fade" id="narrativeListModal" tabindex="-1" aria-labelledby="narrativeListModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-purple text-white rounded-0">
                    <h5 class="modal-title" id="narrativeListModalLabel">Narrative Entries</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-soft" id="narrativeListModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer bg-soft rounded-0">
                    <button type="button" class="btn btn-purple-outline" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                </div>
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
            
            // Update the All Campuses elements after theme change
            styleAllCampusesElements();
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

        // Add this to the DOMContentLoaded event
        document.addEventListener('DOMContentLoaded', function() {
            // Clear any old temporary uploads on page load
            clearTemporaryUploads();
            
            // Apply saved theme on page load
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            updateThemeIcon(savedTheme);
            
            // Style all All Campuses elements
            styleAllCampusesElements();
            
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

            // Initialize Narrative CRUD operations with dropdown options first
            loadDropdownOptions();
            initializeNarrativeCRUD();
            
            // Initialize evaluation table calculations
            setupEvaluationTableCalculations();
        });

        // Setup photo upload previews
        function setupPhotoUploads() {
            // Add event listener to the photo upload input
            const photoInput = document.getElementById('photoUpload');
            if (photoInput) {
                photoInput.addEventListener('change', function(e) {
                    // Auto-upload images immediately when selected
                    if (this.files && this.files.length > 0) {
                        uploadImages(this.files, false); // Don't clear previous uploads by default
                    }
                });
            }
        }
        
        // Function to upload images immediately
        function uploadImages(files, clearPrevious = false) {
            // Get the current preview container
            const previewContainer = document.getElementById('photoPreviewContainer');
            
            // If clearPrevious is true, clear the preview container
            if (clearPrevious) {
                previewContainer.innerHTML = '';
            }
            
            // Don't clear existing previews immediately - we'll update them after the upload
            // Instead, just track the temp previews we're adding now
            const tempPreviews = [];
            
            // Debug log
            console.log(`Starting upload of ${files.length} files${clearPrevious ? ' (clearing previous uploads)' : ''}`);
            
            // Validate files before attempting upload
            let validFiles = [];
            let errorMessages = [];
            const maxFileSize = 64 * 1024 * 1024; // 64MB in bytes
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                console.log(`Validating file ${i+1}/${files.length}: ${file.name} (${file.type}, ${(file.size / 1024 / 1024).toFixed(2)} MB)`);
                
                // Check file size
                if (file.size > maxFileSize) {
                    errorMessages.push(`File too large: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB). Maximum size is 64MB.`);
                    continue;
                }
                
                // Check file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                if (!allowedTypes.includes(file.type)) {
                    errorMessages.push(`Invalid file type: ${file.name}. Allowed types are JPEG, PNG, and GIF.`);
                    continue;
                }
                
                // If all checks pass, add to valid files
                validFiles.push(file);
            }
            
            console.log(`Validation complete: ${validFiles.length} valid files, ${errorMessages.length} errors`);
            
            // If we have error messages, show them
            if (errorMessages.length > 0) {
                let errorHtml = 'The following issues were found:<ul class="text-start">';
                errorMessages.forEach(msg => {
                    errorHtml += `<li>${msg}</li>`;
                });
                errorHtml += '</ul>';
                
                Swal.fire({
                    icon: 'error',
                    title: 'File Validation Error',
                    html: errorHtml,
                    position: 'center',
                    showConfirmButton: true
                });
                
                // If we still have valid files, ask if user wants to proceed with them
                if (validFiles.length > 0) {
                    Swal.fire({
                        icon: 'question',
                        title: 'Continue with valid files?',
                        html: `${errorMessages.length} file(s) cannot be uploaded. Do you want to continue with the ${validFiles.length} valid file(s)?`,
                        showCancelButton: true,
                        confirmButtonText: 'Continue',
                        cancelButtonText: 'Cancel',
                        position: 'center'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // User wants to continue with valid files
                            proceedWithUpload(validFiles, clearPrevious);
                        } else {
                            // User cancels the upload
                            console.log('Upload canceled by user');
                        }
                    });
                }
                
                // If no valid files, just return
                if (validFiles.length === 0) {
                    return;
                }
            } else {
                // All files are valid, proceed with upload
                proceedWithUpload(files, clearPrevious);
            }
            
            function proceedWithUpload(filesToUpload, clearPrevious) {
                // Show temporary previews for these new files only
                const tempPreviews = previewNewImages(filesToUpload);
                
                // Get current narrative ID if editing
                const currentNarrativeId = window.currentNarrativeId || 0;
                const campus = document.getElementById('campus').value;
                
                // Log the upload attempt
                console.log(`Uploading ${filesToUpload.length} images for narrative ID: ${currentNarrativeId}, campus: ${campus}`);
                
                // Create FormData object
                const formData = new FormData();
                
                // Add all files to FormData
                for (let i = 0; i < filesToUpload.length; i++) {
                    formData.append('images[]', filesToUpload[i]);
                    console.log(`Adding file to upload: ${filesToUpload[i].name} (${filesToUpload[i].size} bytes)`);
                }
                
                // Add narrative ID and campus
                formData.append('narrative_id', currentNarrativeId);
                formData.append('campus', campus);
                
                // Add clear_previous flag if needed
                if (clearPrevious) {
                    formData.append('clear_previous', 'true');
                }
                
                // Show loading state
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'col-12 text-center loading-indicator';
                loadingDiv.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Uploading images...</p>';
                previewContainer.appendChild(loadingDiv);
                
                // Debug the form data before sending
                for (const pair of formData.entries()) {
                    console.log(`FormData: ${pair[0]}, ${typeof pair[1] === 'object' ? pair[1].name : pair[1]}`);
                }
                
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
                            // Remove temporary previews
                            tempPreviews.forEach(el => el.remove());
                            
                            // Update the preview container with ALL confirmed uploaded images
                            previewUploadedImages(response.images);
                            
                            // Store the narrative ID if provided by server (for new narratives)
                            if (response.narrative_id) {
                                window.currentNarrativeId = response.narrative_id;
                                console.log(`Updated currentNarrativeId to: ${response.narrative_id}`);
                            }
                            
                            // Store the uploaded image paths to prevent duplicates on refresh
                            window.uploadedImagePaths = response.images || [];
                            
                            // Clear the file input
                            document.getElementById('photoUpload').value = '';
                            
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: `Successfully uploaded ${filesToUpload.length} image(s)! Total: ${response.image_count}`,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                            
                            // If there were warnings, show those too
                            if (response.warnings && response.warnings.length > 0) {
                                setTimeout(() => {
                                    let warningMessage = 'Some issues occurred:';
                                    warningMessage += '<ul class="text-start">';
                                    response.warnings.forEach(warning => {
                                        warningMessage += `<li>${warning}</li>`;
                                    });
                                    warningMessage += '</ul>';
                                    
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Partial Success',
                                        html: warningMessage,
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 5000
                                    });
                                }, 1000);
                            }
                        } else {
                            // Remove temporary previews on error
                            tempPreviews.forEach(el => el.remove());
                            
                            // Get any existing images to redisplay them
                            if (response.existing_images && Array.isArray(response.existing_images) && response.existing_images.length > 0) {
                                previewUploadedImages(response.existing_images);
                                console.log('Displaying existing images after failed upload:', response.existing_images);
                            }
                            
                            // Show error message
                            let errorMessage = response.message || 'Failed to upload images';
                            
                            // If there are specific errors for files, display those too
                            if (response.errors && response.errors.length > 0) {
                                errorMessage += '<br><ul class="text-start">';
                                response.errors.forEach(err => {
                                    errorMessage += `<li>${err}</li>`;
                                });
                                errorMessage += '</ul>';
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: errorMessage,
                                position: 'center',
                                showConfirmButton: true,
                                timer: 7000
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Remove loading indicator
                        const indicator = document.querySelector('.loading-indicator');
                        if (indicator) indicator.remove();
                        
                        // Clear all temporary previews on error
                        previewContainer.innerHTML = '';
                        
                        console.error('AJAX Error:', status + ' - ' + error);
                        console.log('Response Text:', xhr.responseText);
                        
                        // Parse response to extract error information and existing images
                        let errorMessage = 'Failed to upload images. Server error occurred.';
                        let existingImages = [];
                        
                        try {
                            // Check if we have a PHP error message (usually non-JSON responses)
                            if (xhr.responseText.includes('Fatal error') || xhr.responseText.includes('<br />')) {
                                // Extract the error message from PHP error output
                                const errorMatch = xhr.responseText.match(/Fatal error: (.*?) in/);
                                if (errorMatch && errorMatch[1]) {
                                    errorMessage = 'PHP Error: ' + errorMatch[1];
                                } else {
                                    errorMessage = 'PHP Error: Check server logs for details';
                                }
                            } else {
                                // Try to parse as JSON
                                const response = JSON.parse(xhr.responseText);
                                if (response) {
                                    if (response.message) {
                                        errorMessage = response.message;
                                        
                                        // If there are specific errors for files, display those too
                                        if (response.errors && response.errors.length > 0) {
                                            errorMessage += '<br><ul class="text-start">';
                                            response.errors.forEach(err => {
                                                errorMessage += `<li>${err}</li>`;
                                            });
                                            errorMessage += '</ul>';
                                        }
                                    }
                                    
                                    // Get existing images to redisplay them
                                    if (response.existing_images && Array.isArray(response.existing_images)) {
                                        existingImages = response.existing_images;
                                    }
                                }
                            }
                        } catch (e) {
                            // If JSON parsing fails, use the default message
                            console.log('Could not parse error response as JSON:', e);
                        }
                        
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Upload Failed',
                            html: errorMessage,
                            position: 'center', // Changed from toast for more visibility
                            showConfirmButton: true,
                            timer: 10000
                        });
                        
                        // Re-display existing images if available
                        if (existingImages.length > 0) {
                            previewUploadedImages(existingImages);
                            console.log('Restored display of existing images:', existingImages);
                        }
                    }
                });
            }
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
        
        // Function to preview new images temporarily before upload completes, without clearing existing ones
        function previewNewImages(files) {
            const previewContainer = document.getElementById('photoPreviewContainer');
            const tempPreviews = [];
            
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
                        previewDiv.className = 'col-4 col-sm-3 col-md-2 mb-2 temp-preview';
                        tempPreviews.push(previewDiv);
                        
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
            
            return tempPreviews;
        }
        
        // Original function for backwards compatibility, now just clears and calls the new function
        function previewImages(files) {
            const previewContainer = document.getElementById('photoPreviewContainer');
            
            // Clear existing temporary previews
            const existingTempPreviews = previewContainer.querySelectorAll('.temp-preview');
            existingTempPreviews.forEach(el => el.remove());
            
            // Use the new function to preview images
            return previewNewImages(files);
        }
        
        // Function to display confirmed uploaded images
        function previewUploadedImages(imagePaths) {
            const previewContainer = document.getElementById('photoPreviewContainer');
            
            // Clear existing previews
            previewContainer.innerHTML = '';
            
            // Track images we've already displayed to avoid duplicates
            const displayedPaths = new Set();
            
            // Log the received image paths for debugging
            console.log("Received image paths:", imagePaths);
            
            // Add each confirmed image
            if (Array.isArray(imagePaths) && imagePaths.length > 0) {
                // IMPORTANT: Limit to 6 most recent images if we have more than 6
                let pathsToShow = imagePaths;
                if (imagePaths.length > 6) {
                    // Take the 6 most recent uploads (assuming they're at the end of the array)
                    pathsToShow = imagePaths.slice(-6);
                    console.log("Too many images - limiting to most recent 6:", pathsToShow);
                }
                
                // Display up to 6 images
                for (let i = 0; i < pathsToShow.length; i++) {
                    const path = pathsToShow[i];
                    
                    // Skip if we've already displayed this image
                    if (displayedPaths.has(path)) {
                        console.log(`Skipping duplicate image: ${path}`);
                        continue;
                    }
                    
                    // Add to tracked paths
                    displayedPaths.add(path);
                    
                    // Create preview container
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'col-4 col-sm-3 col-md-2 mb-2';
                    previewDiv.setAttribute('data-path', path);
                    
                    // Get the display path (ensure proper path handling)
                    let displayPath = path;
                    
                    // Fix path handling - check if path already has proper structure
                    if (!path.includes('/') && !path.includes('\\')) {
                        // Simple filename, add photos/ prefix
                        displayPath = '../photos/' + path;
                    } else if (path.includes('narrative_')) {
                        // If it contains narrative_ prefix and already has photos/ prefix, don't add another photos/
                        if (path.startsWith('photos/')) {
                            displayPath = '../' + path;
                        } else {
                            displayPath = '../photos/' + path.replace('photos/', '');
                        }
                    }
                    
                    console.log("Image path:", path);
                    console.log("Display path:", displayPath);
                    
                    // Create image element
                    const img = document.createElement('img');
                    img.src = displayPath;
                    img.className = 'img-thumbnail';
                    img.style.height = '100px';
                    img.style.width = '100%';
                    img.style.objectFit = 'cover';
                    
                    // Add error handler for images
                    img.onerror = function() {
                        console.error("Failed to load image:", displayPath);
                        // Try alternate paths as fallback
                        const altPath = path.includes('/') ? path.split('/').pop() : path;
                        console.log("Trying alternate path:", "../photos/" + altPath);
                        img.src = '../photos/' + altPath;
                        
                        // If still fails, show placeholder
                        img.onerror = function() {
                            console.error("All fallback paths failed, using placeholder");
                            img.src = 'https://via.placeholder.com/100x100?text=Image+Error';
                            img.style.opacity = '0.5';
                        };
                    };
                    
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
            
            // Log how many images were displayed
            console.log(`Displayed ${displayedPaths.size} images out of ${imagePaths ? imagePaths.length : 0} paths received`);
            
            // Show warning if there are more images than we can display
            if (imagePaths && imagePaths.length > 6) {
                const warningDiv = document.createElement('div');
                warningDiv.className = 'col-12 mt-2 alert alert-warning';
                warningDiv.innerHTML = `<small>Note: Only showing the 6 most recent images. ${imagePaths.length - 6} older images not displayed.</small>`;
                previewContainer.appendChild(warningDiv);
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
                        // Log the image path we're trying to remove for debugging
                        console.log('Attempting to remove image:', imagePath);
                        
                        // Normalize the path for server
                        let pathToSend = imagePath;
                        
                        // If the path contains full URL or domain, extract just the relative path
                        if (pathToSend.includes('http')) {
                            // Extract just the filename
                            pathToSend = pathToSend.split('/').pop();
                        }
                        
                        console.log('Sending path to server:', pathToSend);
                        
                        // Remove the image from the database
                        $.ajax({
                            url: 'remove_image.php',
                            type: 'POST',
                            data: {
                                narrative_id: narrativeId,
                                image_path: pathToSend
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
                                    // Show error message with details
                                    console.error('Failed to remove image:', response.message);
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
                            error: function(xhr, status, error) {
                                // Show error message with details
                                console.error('Server error:', xhr.responseText);
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
            let narrativeIdField = null; // Add global variable for narrative ID field

            // DOM elements
            const form = document.getElementById('narrativeForm');
            let addBtn = document.getElementById('addBtn');
            let editBtn = document.getElementById('editBtn');
            let deleteBtn = document.getElementById('deleteBtn');
            let viewBtn = document.getElementById('viewBtn');
            narrativeIdField = document.getElementById('narrative_id');
            const saveSpinner = document.getElementById('save-spinner');
            
            // Initially set up buttons
            // Make sure delete button is enabled by default
            deleteBtn.classList.remove('btn-disabled');
            deleteBtn.title = 'Delete narrative';
            
            // Initialize data access based on user type
            initializeDataAccess();
            
            // Event listeners
            form.addEventListener('submit', function(e) {
                // Prevent default form submission
                e.preventDefault();
                
                // Check if form is already being submitted
                if (document.getElementById('save-spinner').style.display === 'inline-block') {
                    console.log('Form submission already in progress, preventing duplicate submission');
                    return;
                }
                
                // If not central, ensure the campus is set from the override
                if (!isCentral && document.getElementById('campus_override')) {
                    document.getElementById('campus_override').value = document.getElementById('campus').value;
                }
                
                // Call the form submission handler directly
                handleFormSubmit(e);
            });
            
            // Function to initialize data access based on user type
            function initializeDataAccess() {
                if (!isCentral) {
                    // For non-Central users, fetch their campus from session and set it
                    $.ajax({
                        url: 'narrative_handler.php',
                        type: 'POST',
                        data: { action: 'get_user_campus' },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success && response.campus) {
                                console.log("Non-central user campus set to:", response.campus);
                                
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
                                
                                // Now load narratives with the campus filter
                                loadNarratives(response.campus);
                            } else {
                                console.error("Failed to get user campus");
                                // Load narratives without a filter as fallback
                                loadNarratives();
                            }
                        },
                        error: function(xhr) {
                            console.error("Error fetching user campus:", xhr.responseText);
                            // Load narratives without a filter as fallback
                            loadNarratives();
                        }
                    });
                } else {
                    // For central users, load all narratives
                    loadNarratives();
                }
            }
            
            // Updated function to load narratives with explicit campus parameter
            function loadNarratives(campusOverride) {
                // Get filter parameters for non-central users
                const campusFilter = isCentral ? '' : (campusOverride || document.getElementById('campus_override')?.value || document.getElementById('campus').value);
                
                console.log("Loading narratives with campus filter:", campusFilter || "All campuses");
                
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
                            console.log("Loaded", narrativeData.length, "narratives");
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

            // Initially disable edit and delete buttons
            // editBtn.classList.add('btn-disabled');
            // deleteBtn.classList.add('btn-disabled');

            // Event listeners
            editBtn.addEventListener('click', function() {
                // If currently editing, just cancel the edit
                if (isEditing) {
                    cancelEdit();
                    return;
                }
                
                // Show loading message
                const saveStatus = document.getElementById('save-status');
                
                // Get filter parameters for users
                let campusFilter = isCentral ? '' : (document.getElementById('campus_override')?.value || document.getElementById('campus').value);
                console.log(`${isCentral ? 'Central' : 'Non-central'} user editing narratives for campus:`, campusFilter);
                
                // Load narratives with the user's campus filter
                loadNarrativesAndShowList(campusFilter, 'edit');
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
                
                // If button is disabled, don't proceed
                if (deleteBtn.classList.contains('btn-disabled')) {
                    return;
                }
                
                // Show loading message
                const saveStatus = document.getElementById('save-status');
                
                // Get filter parameters for users
                let campusFilter = isCentral ? '' : (document.getElementById('campus_override')?.value || document.getElementById('campus').value);
                console.log(`${isCentral ? 'Central' : 'Non-central'} user deleting narratives for campus:`, campusFilter);
                
                // Load narratives with the user's campus filter
                loadNarrativesAndShowList(campusFilter, 'delete');
            });
            
            viewBtn.addEventListener('click', function() {
                // Show loading message
                const saveStatus = document.getElementById('save-status');
                
                // Get filter parameters for users
                let campusFilter = isCentral ? '' : (document.getElementById('campus_override')?.value || document.getElementById('campus').value);
                console.log(`${isCentral ? 'Central' : 'Non-central'} user viewing narratives for campus:`, campusFilter);
                
                // Load narratives with the user's campus filter
                loadNarrativesAndShowList(campusFilter, 'view');
            });
            
            // Function to load narratives and show the list with the specified action
            function loadNarrativesAndShowList(campusFilter, action) {
                console.log(`Loading narratives with campus filter: ${campusFilter}, action: ${action}`);
                
                // Store the action for later use
                window.currentListAction = action || 'view';
                
                const saveStatus = document.getElementById('save-status');
                
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
                            console.log("Loaded", narrativeData.length, "narratives for viewing");
                            
                            if (narrativeData.length === 0) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'No Records',
                                    text: 'No narrative entries found for ' + (campusFilter || 'any campus')
                                });
                                return;
                            }
                            
                            // Now show the list with the updated data for viewing
                            showNarrativesActionList(action);
                        } else {
                            
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
            }
            
            // Function to handle form submission (create or update)
            function handleFormSubmit(e) {
                // Prevent default form submission
                e.preventDefault();
                
                // Check if form is already being submitted
                if (document.getElementById('save-spinner').style.display === 'inline-block') {
                    console.log('Form submission already in progress, preventing duplicate submission');
                    return;
                }
                
                const form = document.getElementById('narrativeForm');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                
                // For Central users, validate that campus is selected
                const isCentral = document.getElementById('isCentral') ? 
                    document.getElementById('isCentral').value === '1' : false;
                
                if (isCentral) {
                    const campusSelect = document.getElementById('campus');
                    if (!campusSelect.value) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Campus Required',
                            text: 'Please select a campus before saving',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        campusSelect.focus();
                        return;
                    }
                }
                
                // Make sure evaluation data is updated before submission
                updateEvaluationData();
                
                // Show saving spinner
                const saveSpinner = document.getElementById('save-spinner');
                saveSpinner.style.display = 'inline-block';
                
                // Create FormData object from the form
                const formData = new FormData(form);
                
                // Get current editing state - store locally to avoid scope issues
                let currentlyEditing = window.isEditing || isEditing || false;
                let narrativeId = window.currentNarrativeId || currentNarrativeId || 0;
                
                // Add action based on whether we're editing or adding
                formData.append('action', currentlyEditing ? 'update' : 'create');
                
                // If not central, ensure the campus is set
                if (!isCentral && document.getElementById('campus_override')) {
                    formData.append('campus', document.getElementById('campus_override').value);
                }
                
                // Debug log the form data before submission
                console.log('Submitting form with evaluation data:', document.getElementById('evaluation').value);
                
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
                        document.getElementById('save-spinner').style.display = 'none';
                        
                        if (response.success) {
                            // Update currentNarrativeId
                            window.currentNarrativeId = response.narrative_id || null;
                            if (window.currentNarrativeId) {
                                document.getElementById('narrative_id').value = window.currentNarrativeId;
                            }
                            
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message || (currentlyEditing ? 'Narrative updated successfully' : 'Narrative added successfully'),
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                            
                            // If we were editing, exit edit mode but keep buttons enabled
                            if (currentlyEditing) {
                                // Reset editing state
                                window.isEditing = false;
                                isEditing = false;
                                
                                // Get button references
                                let editBtn = document.getElementById('editBtn');
                                let addBtn = document.getElementById('addBtn');
                                let deleteBtn = document.getElementById('deleteBtn');
                                
                                editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                                editBtn.classList.remove('editing');
                                editBtn.title = 'Edit narrative';
                                
                                // Reset add button
                                addBtn.innerHTML = '<i class="fas fa-plus"></i>';
                                addBtn.title = 'Add new narrative';
                                addBtn.classList.remove('btn-update');
                                
                                // Make sure delete button is re-enabled
                                deleteBtn.classList.remove('btn-disabled');
                                deleteBtn.title = 'Delete narrative';
                                
                                // Clear form fields completely, including images
                                resetForm();
                            } else {
                                // For new entries, just reset the form completely
                                resetForm();
                            }
                            
                            // Ensure that any additional restrictions on the title dropdown are cleared
                            const titleSelect = document.getElementById('title');
                            if (titleSelect) {
                                // Make sure the title field is not readonly
                                titleSelect.removeAttribute('readonly');
                                titleSelect.classList.remove('bg-light');
                                // Reset all the style properties
                                titleSelect.style.opacity = '';
                                titleSelect.style.color = '';
                                titleSelect.style.borderColor = '';
                                titleSelect.style.cursor = '';
                                
                                // Remove any note about editing restrictions
                                const titleFormGroup = titleSelect.closest('.form-group') || titleSelect.closest('.mb-3') || titleSelect.parentElement;
                                if (titleFormGroup) {
                                    const readOnlyNote = titleFormGroup.querySelector('.form-text.text-muted');
                                    if (readOnlyNote) {
                                        readOnlyNote.remove();
                                    }
                                }
                            }
                            
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
                        document.getElementById('save-spinner').style.display = 'none';
                        
                        console.error("AJAX Error: " + status + " - " + error);
                        console.log("Response Text: " + xhr.responseText);
                        
                        // Try to parse the response if it's JSON
                        let errorMessage = 'Server error while processing your request';
                        try {
                            // Check if we have a PHP error message (usually non-JSON responses)
                            if (xhr.responseText.includes('Fatal error') || xhr.responseText.includes('<br />')) {
                                // Extract the error message from PHP error output
                                const errorMatch = xhr.responseText.match(/Fatal error: (.*?) in/);
                                if (errorMatch && errorMatch[1]) {
                                    errorMessage = 'PHP Error: ' + errorMatch[1];
                                } else {
                                    errorMessage = 'PHP Error: Check server logs for details';
                                }
                            } else {
                                // Try to parse as JSON
                                const response = JSON.parse(xhr.responseText);
                                if (response && response.message) {
                                    errorMessage = response.message;
                                }
                            }
                        } catch (e) {
                            // If JSON parsing fails, use the default message
                            console.log('Could not parse error response as JSON');
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: errorMessage,
                            position: 'center', // Changed from toast for better visibility of important errors
                            showConfirmButton: true,
                            timer: 10000
                        });
                    }
                });
            }

            // Function to start edit mode
            function startEdit() {
                if (!currentNarrativeId) return;
                
                isEditing = true;
                window.isEditing = true;
                
                // Change button appearance
                const editBtn = document.getElementById('editBtn');
                editBtn.innerHTML = '<i class="fas fa-times"></i>';
                editBtn.classList.add('editing');
                editBtn.title = 'Cancel editing';
                
                // Update add button to show it's for saving now
                const addBtn = document.getElementById('addBtn');
                addBtn.innerHTML = '<i class="fas fa-save"></i>';
                addBtn.title = 'Save changes';
                addBtn.classList.add('btn-update');
                
                // Disable delete button while in edit mode
                const deleteBtn = document.getElementById('deleteBtn');
                deleteBtn.classList.add('btn-disabled');
                deleteBtn.title = 'Cannot delete while editing';
                
                // Load narrative data into form
                loadNarrativeForEdit(currentNarrativeId);
            }
            
            // Function to cancel edit mode
            function cancelEdit() {
                isEditing = false;
                window.isEditing = false;
                
                // Get fresh references to buttons
                let editBtn = document.getElementById('editBtn');
                let addBtn = document.getElementById('addBtn');
                let deleteBtn = document.getElementById('deleteBtn');
                
                // Revert button appearance
                editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                editBtn.classList.remove('editing');
                editBtn.title = 'Edit narrative';
                
                // Reset add button
                addBtn.innerHTML = '<i class="fas fa-plus"></i>';
                addBtn.title = 'Add new narrative';
                addBtn.classList.remove('btn-update');
                
                // Re-enable delete button
                deleteBtn.classList.remove('btn-disabled');
                deleteBtn.title = 'Delete narrative';
                
                // Clear form and images
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
                            console.log("Loaded narrative data:", narrative);
                            
                            // First populate the campus dropdown
                            const campusSelect = document.getElementById('campus');
                            campusSelect.value = narrative.campus;
                            
                            // Store the narrative ID globally
                            window.currentNarrativeId = narrativeId;
                            document.getElementById('narrative_id').value = narrativeId;
                            
                            // Then load years for this campus
                            loadYearsForCampus(narrative.campus, function() {
                                // Once years are loaded, set the year
                                document.getElementById('year').value = narrative.year;
                                
                                // Then load activities for this campus and year
                                loadActivitiesForCampusAndYear(function() {
                                    // Once activities are loaded, set the title
                                    const titleSelect = document.getElementById('title');
                                    
                                    // Check if the title exists in the dropdown
                                    let titleExists = false;
                                    let targetTitle = narrative.title;
                                    
                                    console.log("Setting title to:", targetTitle);
                                    
                                    // First try to find an exact match
                                    for (let i = 0; i < titleSelect.options.length; i++) {
                                        if (titleSelect.options[i].value === targetTitle) {
                                            titleSelect.selectedIndex = i;
                                            titleExists = true;
                                            console.log("Found exact match for title");
                                            break;
                                        }
                                    }
                                    
                                    // If no exact match, try case-insensitive
                                    if (!titleExists) {
                                        for (let i = 0; i < titleSelect.options.length; i++) {
                                            if (titleSelect.options[i].value.toLowerCase() === targetTitle.toLowerCase()) {
                                                titleSelect.selectedIndex = i;
                                                titleExists = true;
                                                console.log("Found case-insensitive match for title");
                                                break;
                                            }
                                        }
                                    }
                                    
                                    // If still not found, add a new option with the title
                                    if (!titleExists) {
                                        console.log("Title not found in dropdown, adding it:", targetTitle);
                                        const option = document.createElement('option');
                                        option.value = targetTitle;
                                        option.textContent = targetTitle;
                                        option.selected = true;
                                        titleSelect.appendChild(option);
                                    }
                                    
                                    // Make the title field read-only in edit mode
                                    titleSelect.setAttribute('readonly', 'readonly');
                                    titleSelect.classList.add('bg-light');
                                    titleSelect.style.opacity = '0.8';
                                    titleSelect.style.color = '#333'; // Darker color that works in both light and dark mode
                                    titleSelect.style.borderColor = '#6c757d'; // Grey border to indicate read-only
                                    // Add a visual indicator that it's read-only
                                    titleSelect.style.cursor = 'not-allowed';
                                    // Add a note about read-only status
                                    const titleFormGroup = titleSelect.closest('.form-group') || titleSelect.closest('.mb-3') || titleSelect.parentElement;
                                    // Only add the note if the parent element exists
                                    if (titleFormGroup) {
                                        // Only add the note if it doesn't already exist
                                        if (!titleFormGroup.querySelector('.form-text.text-muted')) {
                                            const readOnlyNote = document.createElement('small');
                                            readOnlyNote.className = 'form-text text-muted';
                                            readOnlyNote.textContent = 'Activity cannot be changed in edit mode';
                                            titleFormGroup.appendChild(readOnlyNote);
                                        }
                                    }
                                    
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
                                    // Ensure photoArray is an array by converting from string if needed
                                    let photoArray = [];
                                    if (typeof narrative.photo_paths === 'string' && narrative.photo_paths) {
                                        try {
                                            photoArray = JSON.parse(narrative.photo_paths);
                                        } catch (e) {
                                            console.warn('Failed to parse photo_paths as JSON:', e);
                                            photoArray = [];
                                        }
                                    } else if (Array.isArray(narrative.photo_paths)) {
                                        photoArray = narrative.photo_paths;
                                    }
                                    
                                    // Add the main photo path if it's not already included
                                    if (narrative.photo_path && !photoArray.includes(narrative.photo_path)) {
                                        photoArray.push(narrative.photo_path);
                                    }
                                    
                                    console.log("Photo paths:", photoArray);
                                    
                                    if (photoArray.length > 0) {
                                        previewUploadedImages(photoArray);
                                    }
                                    
                                    // Process evaluation data
                                    try {
                                        if (narrative.evaluation) {
                                            const evalData = JSON.parse(narrative.evaluation);
                                            console.log("Evaluation data:", evalData);
                                            
                                            // Check for new format (activity and timeliness properties)
                                            if (evalData.activity) {
                                                // Handle new format
                                                
                                                // Populate activity ratings
                                                if (evalData.activity["Excellent"]) {
                                                    document.querySelector('.activity-rating[data-row="excellent"][data-col="batstateu"]').value = 
                                                        evalData.activity["Excellent"]["BatStateU"] || 0;
                                                    document.querySelector('.activity-rating[data-row="excellent"][data-col="others"]').value = 
                                                        evalData.activity["Excellent"]["Others"] || 0;
                                                }
                                                
                                                if (evalData.activity["Very Satisfactory"]) {
                                                    document.querySelector('.activity-rating[data-row="very"][data-col="batstateu"]').value = 
                                                        evalData.activity["Very Satisfactory"]["BatStateU"] || 0;
                                                    document.querySelector('.activity-rating[data-row="very"][data-col="others"]').value = 
                                                        evalData.activity["Very Satisfactory"]["Others"] || 0;
                                                }
                                                
                                                if (evalData.activity["Satisfactory"]) {
                                                    document.querySelector('.activity-rating[data-row="satisfactory"][data-col="batstateu"]').value = 
                                                        evalData.activity["Satisfactory"]["BatStateU"] || 0;
                                                    document.querySelector('.activity-rating[data-row="satisfactory"][data-col="others"]').value = 
                                                        evalData.activity["Satisfactory"]["Others"] || 0;
                                                }
                                                
                                                if (evalData.activity["Fair"]) {
                                                    document.querySelector('.activity-rating[data-row="fair"][data-col="batstateu"]').value = 
                                                        evalData.activity["Fair"]["BatStateU"] || 0;
                                                    document.querySelector('.activity-rating[data-row="fair"][data-col="others"]').value = 
                                                        evalData.activity["Fair"]["Others"] || 0;
                                                }
                                                
                                                if (evalData.activity["Poor"]) {
                                                    document.querySelector('.activity-rating[data-row="poor"][data-col="batstateu"]').value = 
                                                        evalData.activity["Poor"]["BatStateU"] || 0;
                                                    document.querySelector('.activity-rating[data-row="poor"][data-col="others"]').value = 
                                                        evalData.activity["Poor"]["Others"] || 0;
                                                }
                                                
                                                // Populate timeliness ratings if available
                                                if (evalData.timeliness) {
                                                    if (evalData.timeliness["Excellent"]) {
                                                        document.querySelector('.timeliness-rating[data-row="excellent"][data-col="batstateu"]').value = 
                                                            evalData.timeliness["Excellent"]["BatStateU"] || 0;
                                                        document.querySelector('.timeliness-rating[data-row="excellent"][data-col="others"]').value = 
                                                            evalData.timeliness["Excellent"]["Others"] || 0;
                                                    }
                                                    
                                                    if (evalData.timeliness["Very Satisfactory"]) {
                                                        document.querySelector('.timeliness-rating[data-row="very"][data-col="batstateu"]').value = 
                                                            evalData.timeliness["Very Satisfactory"]["BatStateU"] || 0;
                                                        document.querySelector('.timeliness-rating[data-row="very"][data-col="others"]').value = 
                                                            evalData.timeliness["Very Satisfactory"]["Others"] || 0;
                                                    }
                                                    
                                                    if (evalData.timeliness["Satisfactory"]) {
                                                        document.querySelector('.timeliness-rating[data-row="satisfactory"][data-col="batstateu"]').value = 
                                                            evalData.timeliness["Satisfactory"]["BatStateU"] || 0;
                                                        document.querySelector('.timeliness-rating[data-row="satisfactory"][data-col="others"]').value = 
                                                            evalData.timeliness["Satisfactory"]["Others"] || 0;
                                                    }
                                                    
                                                    if (evalData.timeliness["Fair"]) {
                                                        document.querySelector('.timeliness-rating[data-row="fair"][data-col="batstateu"]').value = 
                                                            evalData.timeliness["Fair"]["BatStateU"] || 0;
                                                        document.querySelector('.timeliness-rating[data-row="fair"][data-col="others"]').value = 
                                                            evalData.timeliness["Fair"]["Others"] || 0;
                                                    }
                                                    
                                                    if (evalData.timeliness["Poor"]) {
                                                        document.querySelector('.timeliness-rating[data-row="poor"][data-col="batstateu"]').value = 
                                                            evalData.timeliness["Poor"]["BatStateU"] || 0;
                                                        document.querySelector('.timeliness-rating[data-row="poor"][data-col="others"]').value = 
                                                            evalData.timeliness["Poor"]["Others"] || 0;
                                                    }
                                                }
                                            } 
                                            // Handle old format (ratings and timeliness properties)
                                            else if (evalData.ratings) {
                                                // Populate activity ratings
                                                if (evalData.ratings.excellent) {
                                                    document.querySelector('.activity-rating[data-row="excellent"][data-col="batstateu"]').value = 
                                                        evalData.ratings.excellent.batstateu || 0;
                                                    document.querySelector('.activity-rating[data-row="excellent"][data-col="others"]').value = 
                                                        evalData.ratings.excellent.others || 0;
                                                }
                                                
                                                if (evalData.ratings.very_satisfactory) {
                                                    document.querySelector('.activity-rating[data-row="very"][data-col="batstateu"]').value = 
                                                        evalData.ratings.very_satisfactory.batstateu || 0;
                                                    document.querySelector('.activity-rating[data-row="very"][data-col="others"]').value = 
                                                        evalData.ratings.very_satisfactory.others || 0;
                                                }
                                                
                                                if (evalData.ratings.satisfactory) {
                                                    document.querySelector('.activity-rating[data-row="satisfactory"][data-col="batstateu"]').value = 
                                                        evalData.ratings.satisfactory.batstateu || 0;
                                                    document.querySelector('.activity-rating[data-row="satisfactory"][data-col="others"]').value = 
                                                        evalData.ratings.satisfactory.others || 0;
                                                }
                                                
                                                if (evalData.ratings.fair) {
                                                    document.querySelector('.activity-rating[data-row="fair"][data-col="batstateu"]').value = 
                                                        evalData.ratings.fair.batstateu || 0;
                                                    document.querySelector('.activity-rating[data-row="fair"][data-col="others"]').value = 
                                                        evalData.ratings.fair.others || 0;
                                                }
                                                
                                                if (evalData.ratings.poor) {
                                                    document.querySelector('.activity-rating[data-row="poor"][data-col="batstateu"]').value = 
                                                        evalData.ratings.poor.batstateu || 0;
                                                    document.querySelector('.activity-rating[data-row="poor"][data-col="others"]').value = 
                                                        evalData.ratings.poor.others || 0;
                                                }
                                                
                                                // Populate timeliness ratings if available
                                                if (evalData.timeliness) {
                                                    if (evalData.timeliness.excellent) {
                                                        document.querySelector('.timeliness-rating[data-row="excellent"][data-col="batstateu"]').value = 
                                                            evalData.timeliness.excellent.batstateu || 0;
                                                        document.querySelector('.timeliness-rating[data-row="excellent"][data-col="others"]').value = 
                                                            evalData.timeliness.excellent.others || 0;
                                                    }
                                                    
                                                    if (evalData.timeliness.very_satisfactory) {
                                                        document.querySelector('.timeliness-rating[data-row="very"][data-col="batstateu"]').value = 
                                                            evalData.timeliness.very_satisfactory.batstateu || 0;
                                                        document.querySelector('.timeliness-rating[data-row="very"][data-col="others"]').value = 
                                                            evalData.timeliness.very_satisfactory.others || 0;
                                                    }
                                                    
                                                    if (evalData.timeliness.satisfactory) {
                                                        document.querySelector('.timeliness-rating[data-row="satisfactory"][data-col="batstateu"]').value = 
                                                            evalData.timeliness.satisfactory.batstateu || 0;
                                                        document.querySelector('.timeliness-rating[data-row="satisfactory"][data-col="others"]').value = 
                                                            evalData.timeliness.satisfactory.others || 0;
                                                    }
                                                    
                                                    if (evalData.timeliness.fair) {
                                                        document.querySelector('.timeliness-rating[data-row="fair"][data-col="batstateu"]').value = 
                                                            evalData.timeliness.fair.batstateu || 0;
                                                        document.querySelector('.timeliness-rating[data-row="fair"][data-col="others"]').value = 
                                                            evalData.timeliness.fair.others || 0;
                                                    }
                                                    
                                                    if (evalData.timeliness.poor) {
                                                        document.querySelector('.timeliness-rating[data-row="poor"][data-col="batstateu"]').value = 
                                                            evalData.timeliness.poor.batstateu || 0;
                                                        document.querySelector('.timeliness-rating[data-row="poor"][data-col="others"]').value = 
                                                            evalData.timeliness.poor.others || 0;
                                                    }
                                                }
                                            }
                                            // Handle direct object format (Excellent, Very Satisfactory, etc.)
                                            else if (evalData["Excellent"] || evalData["Fair"] || evalData["Poor"] || evalData["Satisfactory"] || evalData["Very Satisfactory"]) {
                                                // This is the simplest format with just the ratings
                                                if (evalData["Excellent"]) {
                                                    document.querySelector('.activity-rating[data-row="excellent"][data-col="batstateu"]').value = 
                                                        evalData["Excellent"]["BatStateU"] || 0;
                                                    document.querySelector('.activity-rating[data-row="excellent"][data-col="others"]').value = 
                                                        evalData["Excellent"]["Others"] || 0;
                                                }
                                                
                                                if (evalData["Very Satisfactory"]) {
                                                    document.querySelector('.activity-rating[data-row="very"][data-col="batstateu"]').value = 
                                                        evalData["Very Satisfactory"]["BatStateU"] || 0;
                                                    document.querySelector('.activity-rating[data-row="very"][data-col="others"]').value = 
                                                        evalData["Very Satisfactory"]["Others"] || 0;
                                                }
                                                
                                                if (evalData["Satisfactory"]) {
                                                    document.querySelector('.activity-rating[data-row="satisfactory"][data-col="batstateu"]').value = 
                                                        evalData["Satisfactory"]["BatStateU"] || 0;
                                                    document.querySelector('.activity-rating[data-row="satisfactory"][data-col="others"]').value = 
                                                        evalData["Satisfactory"]["Others"] || 0;
                                                }
                                                
                                                if (evalData["Fair"]) {
                                                    document.querySelector('.activity-rating[data-row="fair"][data-col="batstateu"]').value = 
                                                        evalData["Fair"]["BatStateU"] || 0;
                                                    document.querySelector('.activity-rating[data-row="fair"][data-col="others"]').value = 
                                                        evalData["Fair"]["Others"] || 0;
                                                }
                                                
                                                if (evalData["Poor"]) {
                                                    document.querySelector('.activity-rating[data-row="poor"][data-col="batstateu"]').value = 
                                                        evalData["Poor"]["BatStateU"] || 0;
                                                    document.querySelector('.activity-rating[data-row="poor"][data-col="others"]').value = 
                                                        evalData["Poor"]["Others"] || 0;
                                                }
                                            }
                                            
                                            // Recalculate totals
                                            calculateTotals();
                                            calculateTimelinessTotal();
                                        }
                                    } catch (e) {
                                        console.error("Error parsing evaluation data:", e);
                                        // If there's an error, just set the raw value to the hidden field
                                        document.getElementById('evaluation').value = narrative.evaluation || '';
                                    }
                                    
                                    // After populating data, enable photo upload if both year and activity are set
                                    updatePhotoUploadState();
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
                
                // Store the current action in a global variable for reference
                window.currentListAction = action;
                
                // Set title based on action
                let title = 'Narrative Entries';
                
                if (action === 'edit') {
                    title = 'Edit Narrative';
                } else if (action === 'delete') {
                    title = 'Delete Narrative';
                } else if (action === 'view') {
                    title = 'View Narrative';
                }
                
                // Update the modal title
                document.getElementById('narrativeListModalLabel').textContent = title;
                
                // Create HTML for data table view
                let html = '';
                
                // Add campus filter for central users
                if (isCentral) {
                    html += `
                    <div class="form-group mb-3">
                        <label for="modalCampusFilter" class="form-label">Filter by Campus</label>
                        <select id="modalCampusFilter" class="form-select campus-filter-select">
                            <option value="">All Campuses</option>
                        </select>
                    </div>
                    `;
                }
                
                html += `
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-purple">
                            <tr>
                                <th>Title</th>
                                <th>Campus</th>
                                <th>Year</th>
                                <th>Date Created</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                narrativeData.forEach(narrative => {
                    const date = new Date(narrative.created_at).toLocaleDateString();
                    html += `
                        <tr class="narrative-row" data-id="${narrative.id}" data-action="${action}" style="cursor: pointer;">
                            <td>${narrative.title || 'Untitled'}</td>
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
                `;
                
                // Update the modal body with the HTML content
                document.getElementById('narrativeListModalBody').innerHTML = html;
                
                // Show the Bootstrap modal
                const listModal = new bootstrap.Modal(document.getElementById('narrativeListModal'));
                listModal.show();
                
                // Add click event listeners to rows after the modal is shown
                document.getElementById('narrativeListModal').addEventListener('shown.bs.modal', function() {
                    // If central user, populate and setup the campus filter
                    if (isCentral && document.getElementById('modalCampusFilter')) {
                        // Populate campus dropdown
                        $.ajax({
                            url: 'narrative_handler.php',
                            type: 'POST',
                            data: { action: 'get_campuses' },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    const campusSelect = document.getElementById('modalCampusFilter');
                                    
                                    // Add new options
                                    response.data.forEach(campus => {
                                        const option = document.createElement('option');
                                        option.value = campus;
                                        option.textContent = campus;
                                        campusSelect.appendChild(option);
                                    });
                                    
                                    // Add change event to filter narratives
                                    campusSelect.addEventListener('change', function() {
                                        const selectedCampus = this.value;
                                        // Use the stored global action variable
                                        const currentAction = window.currentListAction || 'view';
                                        filterNarrativesByCampus(selectedCampus, currentAction);
                                    });
                                }
                            }
                        });
                    }
                    
                    // Attach click event listeners to rows
                    attachRowClickListeners();
                    
                    // Add a direct click handler to the table for event delegation
                    const table = document.querySelector('#narrativeListModal .table');
                    if (table) {
                        table.addEventListener('click', function(e) {
                            // Find the closest row that was clicked
                            const row = e.target.closest('.narrative-row');
                            if (row) {
                                const narrativeId = row.getAttribute('data-id');
                                const actionType = row.getAttribute('data-action');
                                console.log(`Table click handler - Row clicked! ID: ${narrativeId}, Action: ${actionType}`);
                                
                                // Close the list modal
                                const modal = bootstrap.Modal.getInstance(document.getElementById('narrativeListModal'));
                                modal.hide();
                                
                                // Set current narrative ID
                                currentNarrativeId = narrativeId;
                                document.getElementById('narrative_id').value = narrativeId;
                                window.currentNarrativeId = narrativeId;
                                
                                // Handle different actions
                                if (actionType === 'view') {
                                    showNarrativeDetails(narrativeId);
                                } else if (actionType === 'edit') {
                                    // Enter edit mode
                                    isEditing = true;
                                    window.isEditing = true;
                                    
                                    // Get fresh button references
                                    let editBtn = document.getElementById('editBtn');
                                    let addBtn = document.getElementById('addBtn');
                                    let deleteBtn = document.getElementById('deleteBtn');
                                    
                                    editBtn.innerHTML = '<i class="fas fa-times"></i>';
                                    editBtn.classList.add('editing');
                                    editBtn.title = 'Cancel editing';
                                    
                                    // Update add button to show it's for saving now
                                    addBtn.innerHTML = '<i class="fas fa-save"></i>';
                                    addBtn.title = 'Save changes';
                                    addBtn.classList.add('btn-update');
                                    
                                    // Disable delete button while in edit mode
                                    deleteBtn.classList.add('btn-disabled');
                                    deleteBtn.title = 'Cannot delete while editing';
                                    
                                    // Load narrative data into form
                                    loadNarrativeForEdit(narrativeId);
                                } else if (actionType === 'delete') {
                                    // Call the function to show the delete confirmation
                                    deleteNarrative();
                                }
                            }
                        });
                    }
                }, { once: true }); // Use once: true to ensure it only gets added once
            }

            // Function to view narrative details
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
                            console.log("Viewing narrative details:", narrative);
                            
                            // Update modal content
                            document.getElementById('narrativeDetailsModalLabel').textContent = 'Narrative Details';
                            
                            // Build the HTML for the details view
                            let html = `
                                <div class="narrative-details">
                                    <div class="card mb-4 border-0 shadow-sm">
                                        <div class="card-header bg-purple text-white py-3 d-flex justify-content-between rounded-0">
                                            <div class="header-box text-center px-3 py-2 me-2">
                                                <i class="fas fa-map-marker-alt mb-1"></i>
                                                <div class="fw-bold small">Campus</div>
                                                <div>${narrative.campus || 'N/A'}</div>
                                            </div>
                                            <div class="header-box text-center px-3 py-2 me-2">
                                                <i class="fas fa-calendar-alt mb-1"></i>
                                                <div class="fw-bold small">Year</div>
                                                <div>${narrative.year || 'N/A'}</div>
                                            </div>
                                            <div class="header-box text-center px-3 py-2">
                                                <i class="fas fa-tasks mb-1"></i>
                                                <div class="fw-bold small">Activity</div>
                                                <div>${narrative.title || 'N/A'}</div>
                                            </div>
                                        </div>
                                        <div class="card-body p-4 bg-soft">
                                            <div class="mb-4 narrative-section">
                                                <h5 class="border-bottom pb-2 text-purple d-flex align-items-center">
                                                    <span class="section-icon rounded-circle bg-purple-light text-white me-2">
                                                        <i class="fas fa-info-circle"></i>
                                                    </span>
                                                    Background/Rationale
                                                </h5>
                                                <div class="content-box bg-white p-3 rounded-3 mt-2">
                                                    <p class="mb-0">${narrative.background || 'N/A'}</p>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4 narrative-section">
                                                <h5 class="border-bottom pb-2 text-purple d-flex align-items-center">
                                                    <span class="section-icon rounded-circle bg-purple-light text-white me-2">
                                                        <i class="fas fa-users"></i>
                                                    </span>
                                                    Description of Participants
                                                </h5>
                                                <div class="content-box bg-white p-3 rounded-3 mt-2">
                                                    <p class="mb-0">${narrative.participants || 'N/A'}</p>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4 narrative-section">
                                                <h5 class="border-bottom pb-2 text-purple d-flex align-items-center">
                                                    <span class="section-icon rounded-circle bg-purple-light text-white me-2">
                                                        <i class="fas fa-comments"></i>
                                                    </span>
                                                    Topics Discussed
                                                </h5>
                                                <div class="content-box bg-white p-3 rounded-3 mt-2">
                                                    <p class="mb-0">${narrative.topics || 'N/A'}</p>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4 narrative-section">
                                                <h5 class="border-bottom pb-2 text-purple d-flex align-items-center">
                                                    <span class="section-icon rounded-circle bg-purple-light text-white me-2">
                                                        <i class="fas fa-chart-line"></i>
                                                    </span>
                                                    Results
                                                </h5>
                                                <div class="content-box bg-white p-3 rounded-3 mt-2">
                                                    <p class="mb-0">${narrative.results || 'N/A'}</p>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4 narrative-section">
                                                <h5 class="border-bottom pb-2 text-purple d-flex align-items-center">
                                                    <span class="section-icon rounded-circle bg-purple-light text-white me-2">
                                                        <i class="fas fa-lightbulb"></i>
                                                    </span>
                                                    Lessons Learned
                                                </h5>
                                                <div class="content-box bg-white p-3 rounded-3 mt-2">
                                                    <p class="mb-0">${narrative.lessons || 'N/A'}</p>
                                                </div>
                                            </div>
                                `;
                                
                                // Add photos if available
                                // Ensure photoArray is an array by converting from string if needed
                                let photoArray = [];
                                if (typeof narrative.photo_paths === 'string' && narrative.photo_paths) {
                                    try {
                                        photoArray = JSON.parse(narrative.photo_paths);
                                    } catch (e) {
                                        console.warn('Failed to parse photo_paths as JSON:', e);
                                        photoArray = [];
                                    }
                                } else if (Array.isArray(narrative.photo_paths)) {
                                    photoArray = narrative.photo_paths;
                                }
                                
                                // Add the main photo path if it's not already included
                                if (narrative.photo_path && !photoArray.includes(narrative.photo_path)) {
                                    photoArray.push(narrative.photo_path);
                                }
                                
                                if (photoArray.length > 0) {
                                    html += `
                                        <div class="mb-4 narrative-section">
                                            <h5 class="border-bottom pb-2 text-purple d-flex align-items-center">
                                                <span class="section-icon rounded-circle bg-purple-light text-white me-2">
                                                    <i class="fas fa-camera"></i>
                                                </span>
                                                Photo Documentation
                                            </h5>
                                            <div class="row g-3 mt-2">
                                    `;
                                    
                                    photoArray.forEach(photo => {
                                        // Fix image path handling for display
                                        let displayPath = photo;
                                        if (!photo.includes('/') && !photo.includes('\\')) {
                                            displayPath = '../photos/' + photo;
                                        } else if (photo.includes('narrative_')) {
                                            // If it contains narrative_ prefix and already has photos/ prefix, don't add another photos/
                                            if (photo.startsWith('photos/')) {
                                                displayPath = '../' + photo;
                                            } else {
                                                displayPath = '../photos/' + photo.replace('photos/', '');
                                            }
                                        }
                                        
                                        console.log("View mode - Image path:", photo);
                                        console.log("View mode - Display path:", displayPath);
                                        
                                        html += `
                                            <div class="col-md-4 mb-3">
                                                <div class="photo-card h-100 shadow-sm border border-purple-light border-opacity-25 rounded-3 overflow-hidden bg-white">
                                                    <div class="position-relative">
                                                        <img src="${displayPath}" alt="Photo documentation" class="img-fluid w-100 photo-img" 
                                                            style="height: 200px; object-fit: cover;" onerror="this.onerror=null;this.src='../photos/${photo.includes('/') ? photo.split('/').pop() : photo}';
                                                            if(this.src.includes('undefined')) this.src='https://via.placeholder.com/200x150?text=Image+Not+Found';">
                                                        <div class="photo-overlay">
                                                            <button class="btn btn-sm btn-light" onclick="window.open('${displayPath}', '_blank')">
                                                                <i class="fas fa-search-plus"></i> View Full
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                    });
                                    
                                    html += `
                                            </div>
                                            <p class="mt-3 fst-italic text-center text-muted">${narrative.photo_caption || 'No caption provided.'}</p>
                                        </div>
                                    `;
                                }
                                
                                html += `</div></div></div>`;
                                
                                document.getElementById('narrativeDetailsModalBody').innerHTML = html;
                                
                                // Show the modal
                                const detailsModal = new bootstrap.Modal(document.getElementById('narrativeDetailsModal'));
                                detailsModal.show();
                            } else {
                                saveStatus.innerHTML = '<span class="text-danger">Error loading details</span>';
                                setTimeout(() => { saveStatus.innerHTML = ''; }, 3000);
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Failed to load narrative details'
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
                            
                            // Update the delete confirmation modal content
                            document.getElementById('deleteNarrativeModalLabel').textContent = 'Confirm Deletion';
                            
                            const confirmationContent = `
                                <div class="text-start">
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Are you sure you want to delete this narrative? This action cannot be undone.
                                    </div>
                                    <div class="card border-purple-light shadow-sm mb-3 mt-4">
                                        <div class="card-header bg-purple-light text-white py-2">
                                            <h6 class="mb-0">Narrative Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-bordered mb-0">
                                                <tr>
                                                    <th class="bg-soft" style="width: 140px;">Campus</th>
                                                    <td>${narrative.campus || 'N/A'}</td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-soft">Year</th>
                                                    <td>${narrative.year || 'N/A'}</td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-soft">Activity</th>
                                                    <td>${narrative.title || 'N/A'}</td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-soft">Date Created</th>
                                                    <td>${new Date(narrative.created_at).toLocaleString() || 'N/A'}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            `;
                            
                            document.getElementById('deleteNarrativeModalBody').innerHTML = confirmationContent;
                            
                            // Update the delete confirmation button
                            document.getElementById('confirmDeleteBtn').onclick = function() {
                                // Close the modal
                                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteNarrativeModal'));
                                deleteModal.hide();
                                
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
                            };
                            
                            // Show the modal
                            const deleteModal = new bootstrap.Modal(document.getElementById('deleteNarrativeModal'));
                            deleteModal.show();
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
                document.getElementById('narrative_id').value = '0';
                document.getElementById('ppas_form_id').value = '';
                
                // Reset buttons
                editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                editBtn.classList.remove('editing');
                
                addBtn.innerHTML = '<i class="fas fa-plus"></i>';
                addBtn.classList.remove('btn-update');
                
                // Keep delete button enabled
                deleteBtn.classList.remove('btn-disabled');
                
                // Clear photo previews completely
                const previewContainer = document.getElementById('photoPreviewContainer');
                if (previewContainer) {
                    previewContainer.innerHTML = '';
                }
                
                // Clear the file input
                const photoInput = document.getElementById('photoUpload');
                if (photoInput) {
                    photoInput.value = '';
                }
                
                // Clear any stored image data or cached paths
                window.uploadedImagePaths = [];
                
                // Clear session storage for uploaded images
                if (window.sessionStorage) {
                    sessionStorage.removeItem('uploadedImages');
                }
                
                // Reset the title field - remove readonly attribute and the explanatory note
                const titleSelect = document.getElementById('title');
                if (titleSelect) {
                    titleSelect.removeAttribute('readonly');
                    titleSelect.classList.remove('bg-light');
                    
                    // Remove the explanatory note if it exists
                    const titleFormGroup = titleSelect.closest('.form-group');
                    if (titleFormGroup) {
                        const readOnlyNote = titleFormGroup.querySelector('.form-text.text-muted');
                        if (readOnlyNote) {
                            readOnlyNote.remove();
                        }
                    }
                }
                
                // For non-central users, restore their campus
                if (!isCentral && document.getElementById('campus_override')) {
                    const campusValue = document.getElementById('campus_override').value;
                    document.getElementById('campus').value = campusValue;
                    
                    // Reload years based on campus
                    loadYearsForCampus(campusValue);
                } else {
                    // For central users, just reset the dropdowns
                    const campusSelect = document.getElementById('campus');
                    if (campusSelect && campusSelect.options.length > 0) {
                        campusSelect.selectedIndex = 0;
                    }
                    
                    const yearSelect = document.getElementById('year');
                    if (yearSelect && yearSelect.options.length > 0) {
                        yearSelect.selectedIndex = 0;
                    }
                    
                    // Title select already handled above
                    if (titleSelect && titleSelect.options.length > 0) {
                        titleSelect.selectedIndex = 0;
                    }
                }
                
                // Clear any status messages
                const saveStatus = document.getElementById('save-status');
                if (saveStatus) {
                    saveStatus.innerHTML = '';
                }
                
                // Update photo upload state (should disable it since year/activity are reset)
                updatePhotoUploadState();
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
                            response.data.forEach(activity => {
                                const option = document.createElement('option');
                                // Check if activity is an object (new format) or string (old format)
                                if (typeof activity === 'object' && activity !== null) {
                                    option.value = activity.title;
                                    option.textContent = activity.title;
                                    
                                    // Add red color to activities that already have narratives
                                    if (activity.has_narrative) {
                                        option.style.color = 'red';
                                        option.setAttribute('data-has-narrative', 'true');
                                    }
                                } else {
                                    // Handle legacy format (string)
                                    option.value = activity;
                                    option.textContent = activity;
                                }
                                
                                titleSelect.appendChild(option);
                            });
                            
                            // Add change event listener to title dropdown
                            titleSelect.addEventListener('change', loadActivityDetails);
                            
                            // Add event listener to prevent selection of options with narratives
                            titleSelect.addEventListener('change', function(e) {
                                const selectedOption = this.options[this.selectedIndex];
                                if (selectedOption.getAttribute('data-has-narrative') === 'true') {
                                    // Prevent selection and show a message
                                    e.preventDefault();
                                    this.value = ''; // Reset to default option
                                    
                                    // Show a notification
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Selection Not Allowed',
                                        text: 'This activity already has a narrative and cannot be selected.',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                }
                            });
                            
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
                            response.data.forEach(activity => {
                                const option = document.createElement('option');
                                // Check if activity is an object (new format) or string (old format)
                                if (typeof activity === 'object' && activity !== null) {
                                    option.value = activity.title;
                                    option.textContent = activity.title;
                                    
                                    // Add red color to activities that already have narratives
                                    if (activity.has_narrative) {
                                        option.style.color = 'red';
                                        option.setAttribute('data-has-narrative', 'true');
                                    }
                                } else {
                                    // Handle legacy format (string)
                                    option.value = activity;
                                    option.textContent = activity;
                                }
                                
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
                            // Populate PS attribution, gender issue, and ppas_form_id fields
                            document.getElementById('psAttribution').value = response.data.ps_attribution || '';
                            document.getElementById('genderIssue').value = response.data.gender_issue || '';
                            document.getElementById('ppas_form_id').value = response.data.ppas_form_id || '';
                            
                            // If this was a fallback response (empty values), show a gentle notification
                            if (response.status === 'fallback') {
                                console.log("Activity details not found, using empty values");
                                Swal.fire({
                                    icon: 'info',
                                    title: 'No Activity Details',
                                    text: 'No details found for this activity. Please fill in the PS Attribution and Gender Issue fields manually.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 5000
                                });
                            }
                        } else {
                            console.error("Error loading activity details: " + response.message);
                            
                            // Show a helpful error message to the user
                            Swal.fire({
                                icon: 'warning',
                                title: 'Activity Details Not Found',
                                text: 'Could not find details for this activity. You can still continue with the narrative entry, but some fields may need to be filled in manually.',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 5000
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error (activity details): " + status + " - " + error);
                        console.log("Response Text: " + xhr.responseText);
                        
                        // Show a general error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load activity details due to a server error. Please try again later.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 5000
                        });
                    }
                });
        }

        // Add this function for evaluation table calculations
        function setupEvaluationTableCalculations() {
            // Get all rating inputs
            const ratingInputs = document.querySelectorAll('.activity-rating');
            const timelinessInputs = document.querySelectorAll('.timeliness-rating');
            
            // Add event listeners to all rating inputs
            ratingInputs.forEach(input => {
                input.addEventListener('input', calculateTotals);
            });
            
            // Add event listeners to all timeliness inputs
            timelinessInputs.forEach(input => {
                input.addEventListener('input', calculateTimelinessTotal);
            });
            
            // Calculate initial totals
            calculateTotals();
            calculateTimelinessTotal();
        }
        
        function calculateTotals() {
            // Calculate row totals (horizontally)
            const rows = ['excellent', 'very', 'satisfactory', 'fair', 'poor'];
            
            rows.forEach(row => {
                const batstateuInput = document.querySelector(`.activity-rating[data-row="${row}"][data-col="batstateu"]`);
                const othersInput = document.querySelector(`.activity-rating[data-row="${row}"][data-col="others"]`);
                const totalInput = document.querySelector(`.activity-total[data-row="${row}"]`);
                
                const batstateuValue = parseInt(batstateuInput.value) || 0;
                const othersValue = parseInt(othersInput.value) || 0;
                
                totalInput.value = batstateuValue + othersValue;
            });
            
            // Calculate column totals (vertically)
            const cols = ['batstateu', 'others'];
            
            cols.forEach(col => {
                let colTotal = 0;
                
                rows.forEach(row => {
                    const input = document.querySelector(`.activity-rating[data-row="${row}"][data-col="${col}"]`);
                    colTotal += parseInt(input.value) || 0;
                });
                
                document.querySelector(`.activity-col-total[data-col="${col}"]`).value = colTotal;
            });
            
            // Calculate grand total
            let grandTotal = 0;
            document.querySelectorAll('.activity-total').forEach(input => {
                grandTotal += parseInt(input.value) || 0;
            });
            
            document.querySelector('.activity-grand-total').value = grandTotal;
            
            // Update hidden evaluation field with JSON data
            updateEvaluationData();
        }

        // Add function to calculate timeliness totals
        function calculateTimelinessTotal() {
            // Calculate row totals (horizontally)
            const rows = ['excellent', 'very', 'satisfactory', 'fair', 'poor'];
            
            rows.forEach(row => {
                const batstateuInput = document.querySelector(`.timeliness-rating[data-row="${row}"][data-col="batstateu"]`);
                const othersInput = document.querySelector(`.timeliness-rating[data-row="${row}"][data-col="others"]`);
                const totalInput = document.querySelector(`.timeliness-total[data-row="${row}"]`);
                
                const batstateuValue = parseInt(batstateuInput.value) || 0;
                const othersValue = parseInt(othersInput.value) || 0;
                
                totalInput.value = batstateuValue + othersValue;
            });
            
            // Calculate column totals (vertically)
            const cols = ['batstateu', 'others'];
            
            cols.forEach(col => {
                let colTotal = 0;
                
                rows.forEach(row => {
                    const input = document.querySelector(`.timeliness-rating[data-row="${row}"][data-col="${col}"]`);
                    colTotal += parseInt(input.value) || 0;
                });
                
                document.querySelector(`.timeliness-col-total[data-col="${col}"]`).value = colTotal;
            });
            
            // Calculate grand total
            let grandTotal = 0;
            document.querySelectorAll('.timeliness-total').forEach(input => {
                grandTotal += parseInt(input.value) || 0;
            });
            
            document.querySelector('.timeliness-grand-total').value = grandTotal;
            
            // Update hidden evaluation field with JSON data
            updateEvaluationData();
        }
        
        // Update function to format evaluation data for submission
        function updateEvaluationData() {
            // Activity ratings
            const activityData = {
                "Excellent": {
                    "BatStateU": parseInt(document.querySelector('.activity-rating[data-row="excellent"][data-col="batstateu"]').value) || 0,
                    "Others": parseInt(document.querySelector('.activity-rating[data-row="excellent"][data-col="others"]').value) || 0
                },
                "Very Satisfactory": {
                    "BatStateU": parseInt(document.querySelector('.activity-rating[data-row="very"][data-col="batstateu"]').value) || 0,
                    "Others": parseInt(document.querySelector('.activity-rating[data-row="very"][data-col="others"]').value) || 0
                },
                "Satisfactory": {
                    "BatStateU": parseInt(document.querySelector('.activity-rating[data-row="satisfactory"][data-col="batstateu"]').value) || 0,
                    "Others": parseInt(document.querySelector('.activity-rating[data-row="satisfactory"][data-col="others"]').value) || 0
                },
                "Fair": {
                    "BatStateU": parseInt(document.querySelector('.activity-rating[data-row="fair"][data-col="batstateu"]').value) || 0,
                    "Others": parseInt(document.querySelector('.activity-rating[data-row="fair"][data-col="others"]').value) || 0
                },
                "Poor": {
                    "BatStateU": parseInt(document.querySelector('.activity-rating[data-row="poor"][data-col="batstateu"]').value) || 0,
                    "Others": parseInt(document.querySelector('.activity-rating[data-row="poor"][data-col="others"]').value) || 0
                }
            };
            
            // Timeliness ratings
            const timelinessData = {
                "Excellent": {
                    "BatStateU": parseInt(document.querySelector('.timeliness-rating[data-row="excellent"][data-col="batstateu"]').value) || 0,
                    "Others": parseInt(document.querySelector('.timeliness-rating[data-row="excellent"][data-col="others"]').value) || 0
                },
                "Very Satisfactory": {
                    "BatStateU": parseInt(document.querySelector('.timeliness-rating[data-row="very"][data-col="batstateu"]').value) || 0,
                    "Others": parseInt(document.querySelector('.timeliness-rating[data-row="very"][data-col="others"]').value) || 0
                },
                "Satisfactory": {
                    "BatStateU": parseInt(document.querySelector('.timeliness-rating[data-row="satisfactory"][data-col="batstateu"]').value) || 0,
                    "Others": parseInt(document.querySelector('.timeliness-rating[data-row="satisfactory"][data-col="others"]').value) || 0
                },
                "Fair": {
                    "BatStateU": parseInt(document.querySelector('.timeliness-rating[data-row="fair"][data-col="batstateu"]').value) || 0,
                    "Others": parseInt(document.querySelector('.timeliness-rating[data-row="fair"][data-col="others"]').value) || 0
                },
                "Poor": {
                    "BatStateU": parseInt(document.querySelector('.timeliness-rating[data-row="poor"][data-col="batstateu"]').value) || 0,
                    "Others": parseInt(document.querySelector('.timeliness-rating[data-row="poor"][data-col="others"]').value) || 0
                }
            };
            
            // Store activity ratings in a hidden field
            if (!document.getElementById('activity_ratings')) {
                const activityField = document.createElement('input');
                activityField.type = 'hidden';
                activityField.id = 'activity_ratings';
                activityField.name = 'activity_ratings';
                document.getElementById('narrativeForm').appendChild(activityField);
            }
            document.getElementById('activity_ratings').value = JSON.stringify(activityData);
            
            // Store timeliness ratings in a hidden field
            if (!document.getElementById('timeliness_ratings')) {
                const timelinessField = document.createElement('input');
                timelinessField.type = 'hidden';
                timelinessField.id = 'timeliness_ratings';
                timelinessField.name = 'timeliness_ratings';
                document.getElementById('narrativeForm').appendChild(timelinessField);
            }
            document.getElementById('timeliness_ratings').value = JSON.stringify(timelinessData);
            
            // For backward compatibility, still keep the combined evaluation field
            const evalData = {
                activity: activityData,
                timeliness: timelinessData
            };
            document.getElementById('evaluation').value = JSON.stringify(evalData);
        }
        
        // Modify the setupPhotoUploads function to add year and activity constraints
        function setupPhotoUploads() {
            const photoInput = document.getElementById('photoUpload');
            const uploadLabel = document.querySelector('.custom-file-upload label');
            
            if (photoInput && uploadLabel) {
                // Initially disable the upload button
                updatePhotoUploadState();
                
                // Add event listener to the photo upload input
                photoInput.addEventListener('change', function(e) {
                    // First check if year and activity are selected
                    if (!validateYearAndActivity()) {
                        e.preventDefault();
                        resetFileInput(this);
                        return;
                    }
                    
                    // Auto-upload images when selected
                    if (this.files && this.files.length > 0) {
                        // Clear existing images first when new ones are selected
                        if (!window.currentNarrativeId || window.currentNarrativeId === '0') {
                            const previewContainer = document.getElementById('photoPreviewContainer');
                            previewContainer.innerHTML = '';
                        }
                        
                        uploadImages(this.files);
                    }
                });
                
                // Add event listeners to year and activity dropdowns to update upload state
                const yearSelect = document.getElementById('year');
                const titleSelect = document.getElementById('title');
                
                if (yearSelect) {
                    yearSelect.addEventListener('change', updatePhotoUploadState);
                }
                
                if (titleSelect) {
                    titleSelect.addEventListener('change', updatePhotoUploadState);
                }
                
                // Initialize uploadedImagePaths array
                window.uploadedImagePaths = window.uploadedImagePaths || [];
            }
        }
        
        // Function to validate year and activity selection
        function validateYearAndActivity() {
            const yearSelect = document.getElementById('year');
            const titleSelect = document.getElementById('title');
            
            if (!yearSelect.value || yearSelect.value === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Year Required',
                    text: 'Please select a year before uploading images',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return false;
            }
            
            if (!titleSelect.value || titleSelect.value === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Activity Required',
                    text: 'Please select an activity before uploading images',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return false;
            }
            
            return true;
        }
        
        // Function to update photo upload button state based on selections
        function updatePhotoUploadState() {
            const yearSelect = document.getElementById('year');
            const titleSelect = document.getElementById('title');
            const uploadLabel = document.querySelector('.custom-file-upload label');
            const photoInput = document.getElementById('photoUpload');
            const uploadContainer = document.querySelector('.custom-file-upload');
            
            if (yearSelect && titleSelect && uploadLabel && photoInput && uploadContainer) {
                if (!yearSelect.value || !titleSelect.value) {
                    // Disable upload
                    photoInput.disabled = true;
                    uploadContainer.classList.add('disabled');
                    uploadLabel.title = 'Please select year and activity first';
                } else {
                    // Enable upload
                    photoInput.disabled = false;
                    uploadContainer.classList.remove('disabled');
                    uploadLabel.title = 'Upload images';
                }
            }
        }
        
        // Helper function to reset file input
        function resetFileInput(input) {
            input.value = '';
        }
        
        // Update loadNarrativeForEdit function to handle the new evaluation data format
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
                        console.log("Loaded narrative data:", narrative);
                        
                        // First populate the campus dropdown
                        const campusSelect = document.getElementById('campus');
                        campusSelect.value = narrative.campus;
                        
                        // Store the narrative ID globally
                        window.currentNarrativeId = narrativeId;
                        document.getElementById('narrative_id').value = narrativeId;
                        
                        // Then load years for this campus
                        loadYearsForCampus(narrative.campus, function() {
                            // Once years are loaded, set the year
                            document.getElementById('year').value = narrative.year;
                            
                            // Then load activities for this campus and year
                            loadActivitiesForCampusAndYear(function() {
                                // Once activities are loaded, set the title
                                const titleSelect = document.getElementById('title');
                                // Handle case where title might be an object
                                titleSelect.value = narrative.title;
                                // Make the title field read-only in edit mode
                                titleSelect.setAttribute('readonly', 'readonly');
                                titleSelect.classList.add('bg-light');
                                titleSelect.style.opacity = '0.8';
                                titleSelect.style.color = '#333'; // Darker color that works in both light and dark mode
                                titleSelect.style.borderColor = '#6c757d'; // Grey border to indicate read-only
                                // Add a visual indicator that it's read-only
                                titleSelect.style.cursor = 'not-allowed';
                                // Add a note about read-only status
                                const titleFormGroup = titleSelect.closest('.form-group') || titleSelect.closest('.mb-3') || titleSelect.parentElement;
                                // Only add the note if the parent element exists
                                if (titleFormGroup) {
                                    // Only add the note if it doesn't already exist
                                    if (!titleFormGroup.querySelector('.form-text.text-muted')) {
                                        const readOnlyNote = document.createElement('small');
                                        readOnlyNote.className = 'form-text text-muted';
                                        readOnlyNote.textContent = 'Activity cannot be changed in edit mode';
                                        titleFormGroup.appendChild(readOnlyNote);
                                    }
                                }
                                
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
                                // Ensure photoArray is an array by converting from string if needed
                                let photoArray = [];
                                if (typeof narrative.photo_paths === 'string' && narrative.photo_paths) {
                                    try {
                                        photoArray = JSON.parse(narrative.photo_paths);
                                    } catch (e) {
                                        console.warn('Failed to parse photo_paths as JSON:', e);
                                        photoArray = [];
                                    }
                                } else if (Array.isArray(narrative.photo_paths)) {
                                    photoArray = narrative.photo_paths;
                                }
                                
                                // Add the main photo path if it's not already included
                                if (narrative.photo_path && !photoArray.includes(narrative.photo_path)) {
                                    photoArray.push(narrative.photo_path);
                                }
                                
                                console.log("Photo paths:", photoArray);
                                
                                if (photoArray.length > 0) {
                                    previewUploadedImages(photoArray);
                                }
                                
                                // Process evaluation data
                                try {
                                    if (narrative.evaluation) {
                                        const evalData = JSON.parse(narrative.evaluation);
                                        console.log("Evaluation data:", evalData);
                                        
                                        // Check for new format (activity and timeliness properties)
                                        if (evalData.activity) {
                                            // Handle new format
                                            
                                            // Populate activity ratings
                                            if (evalData.activity["Excellent"]) {
                                                document.querySelector('.activity-rating[data-row="excellent"][data-col="batstateu"]').value = 
                                                    evalData.activity["Excellent"]["BatStateU"] || 0;
                                                document.querySelector('.activity-rating[data-row="excellent"][data-col="others"]').value = 
                                                    evalData.activity["Excellent"]["Others"] || 0;
                                            }
                                            
                                            if (evalData.activity["Very Satisfactory"]) {
                                                document.querySelector('.activity-rating[data-row="very"][data-col="batstateu"]').value = 
                                                    evalData.activity["Very Satisfactory"]["BatStateU"] || 0;
                                                document.querySelector('.activity-rating[data-row="very"][data-col="others"]').value = 
                                                    evalData.activity["Very Satisfactory"]["Others"] || 0;
                                            }
                                            
                                            if (evalData.activity["Satisfactory"]) {
                                                document.querySelector('.activity-rating[data-row="satisfactory"][data-col="batstateu"]').value = 
                                                    evalData.activity["Satisfactory"]["BatStateU"] || 0;
                                                document.querySelector('.activity-rating[data-row="satisfactory"][data-col="others"]').value = 
                                                    evalData.activity["Satisfactory"]["Others"] || 0;
                                            }
                                            
                                            if (evalData.activity["Fair"]) {
                                                document.querySelector('.activity-rating[data-row="fair"][data-col="batstateu"]').value = 
                                                    evalData.activity["Fair"]["BatStateU"] || 0;
                                                document.querySelector('.activity-rating[data-row="fair"][data-col="others"]').value = 
                                                    evalData.activity["Fair"]["Others"] || 0;
                                            }
                                            
                                            if (evalData.activity["Poor"]) {
                                                document.querySelector('.activity-rating[data-row="poor"][data-col="batstateu"]').value = 
                                                    evalData.activity["Poor"]["BatStateU"] || 0;
                                                document.querySelector('.activity-rating[data-row="poor"][data-col="others"]').value = 
                                                    evalData.activity["Poor"]["Others"] || 0;
                                            }
                                            
                                            // Populate timeliness ratings if available
                                            if (evalData.timeliness) {
                                                if (evalData.timeliness["Excellent"]) {
                                                    document.querySelector('.timeliness-rating[data-row="excellent"][data-col="batstateu"]').value = 
                                                        evalData.timeliness["Excellent"]["BatStateU"] || 0;
                                                    document.querySelector('.timeliness-rating[data-row="excellent"][data-col="others"]').value = 
                                                        evalData.timeliness["Excellent"]["Others"] || 0;
                                                }
                                                
                                                if (evalData.timeliness["Very Satisfactory"]) {
                                                    document.querySelector('.timeliness-rating[data-row="very"][data-col="batstateu"]').value = 
                                                        evalData.timeliness["Very Satisfactory"]["BatStateU"] || 0;
                                                    document.querySelector('.timeliness-rating[data-row="very"][data-col="others"]').value = 
                                                        evalData.timeliness["Very Satisfactory"]["Others"] || 0;
                                                }
                                                
                                                if (evalData.timeliness["Satisfactory"]) {
                                                    document.querySelector('.timeliness-rating[data-row="satisfactory"][data-col="batstateu"]').value = 
                                                        evalData.timeliness["Satisfactory"]["BatStateU"] || 0;
                                                    document.querySelector('.timeliness-rating[data-row="satisfactory"][data-col="others"]').value = 
                                                        evalData.timeliness["Satisfactory"]["Others"] || 0;
                                                }
                                                
                                                if (evalData.timeliness["Fair"]) {
                                                    document.querySelector('.timeliness-rating[data-row="fair"][data-col="batstateu"]').value = 
                                                        evalData.timeliness["Fair"]["BatStateU"] || 0;
                                                    document.querySelector('.timeliness-rating[data-row="fair"][data-col="others"]').value = 
                                                        evalData.timeliness["Fair"]["Others"] || 0;
                                                }
                                                
                                                if (evalData.timeliness["Poor"]) {
                                                    document.querySelector('.timeliness-rating[data-row="poor"][data-col="batstateu"]').value = 
                                                        evalData.timeliness["Poor"]["BatStateU"] || 0;
                                                    document.querySelector('.timeliness-rating[data-row="poor"][data-col="others"]').value = 
                                                        evalData.timeliness["Poor"]["Others"] || 0;
                                                }
                                            }
                                        } 
                                        // Handle old format (ratings and timeliness properties)
                                        else if (evalData.ratings) {
                                            // Populate activity ratings
                                            if (evalData.ratings.excellent) {
                                                document.querySelector('.activity-rating[data-row="excellent"][data-col="batstateu"]').value = 
                                                    evalData.ratings.excellent.batstateu || 0;
                                                document.querySelector('.activity-rating[data-row="excellent"][data-col="others"]').value = 
                                                    evalData.ratings.excellent.others || 0;
                                            }
                                            
                                            if (evalData.ratings.very_satisfactory) {
                                                document.querySelector('.activity-rating[data-row="very"][data-col="batstateu"]').value = 
                                                    evalData.ratings.very_satisfactory.batstateu || 0;
                                                document.querySelector('.activity-rating[data-row="very"][data-col="others"]').value = 
                                                    evalData.ratings.very_satisfactory.others || 0;
                                            }
                                            
                                            if (evalData.ratings.satisfactory) {
                                                document.querySelector('.activity-rating[data-row="satisfactory"][data-col="batstateu"]').value = 
                                                    evalData.ratings.satisfactory.batstateu || 0;
                                                document.querySelector('.activity-rating[data-row="satisfactory"][data-col="others"]').value = 
                                                    evalData.ratings.satisfactory.others || 0;
                                            }
                                            
                                            if (evalData.ratings.fair) {
                                                document.querySelector('.activity-rating[data-row="fair"][data-col="batstateu"]').value = 
                                                    evalData.ratings.fair.batstateu || 0;
                                                document.querySelector('.activity-rating[data-row="fair"][data-col="others"]').value = 
                                                    evalData.ratings.fair.others || 0;
                                            }
                                            
                                            if (evalData.ratings.poor) {
                                                document.querySelector('.activity-rating[data-row="poor"][data-col="batstateu"]').value = 
                                                    evalData.ratings.poor.batstateu || 0;
                                                document.querySelector('.activity-rating[data-row="poor"][data-col="others"]').value = 
                                                    evalData.ratings.poor.others || 0;
                                            }
                                            
                                            // Populate timeliness ratings if available
                                            if (evalData.timeliness) {
                                                if (evalData.timeliness.excellent) {
                                                    document.querySelector('.timeliness-rating[data-row="excellent"][data-col="batstateu"]').value = 
                                                        evalData.timeliness.excellent.batstateu || 0;
                                                    document.querySelector('.timeliness-rating[data-row="excellent"][data-col="others"]').value = 
                                                        evalData.timeliness.excellent.others || 0;
                                                }
                                                
                                                if (evalData.timeliness.very_satisfactory) {
                                                    document.querySelector('.timeliness-rating[data-row="very"][data-col="batstateu"]').value = 
                                                        evalData.timeliness.very_satisfactory.batstateu || 0;
                                                    document.querySelector('.timeliness-rating[data-row="very"][data-col="others"]').value = 
                                                        evalData.timeliness.very_satisfactory.others || 0;
                                                }
                                                
                                                if (evalData.timeliness.satisfactory) {
                                                    document.querySelector('.timeliness-rating[data-row="satisfactory"][data-col="batstateu"]').value = 
                                                        evalData.timeliness.satisfactory.batstateu || 0;
                                                    document.querySelector('.timeliness-rating[data-row="satisfactory"][data-col="others"]').value = 
                                                        evalData.timeliness.satisfactory.others || 0;
                                                }
                                                
                                                if (evalData.timeliness.fair) {
                                                    document.querySelector('.timeliness-rating[data-row="fair"][data-col="batstateu"]').value = 
                                                        evalData.timeliness.fair.batstateu || 0;
                                                    document.querySelector('.timeliness-rating[data-row="fair"][data-col="others"]').value = 
                                                        evalData.timeliness.fair.others || 0;
                                                }
                                                
                                                if (evalData.timeliness.poor) {
                                                    document.querySelector('.timeliness-rating[data-row="poor"][data-col="batstateu"]').value = 
                                                        evalData.timeliness.poor.batstateu || 0;
                                                    document.querySelector('.timeliness-rating[data-row="poor"][data-col="others"]').value = 
                                                        evalData.timeliness.poor.others || 0;
                                                }
                                            }
                                        }
                                        // Handle direct object format (Excellent, Very Satisfactory, etc.)
                                        else if (evalData["Excellent"] || evalData["Fair"] || evalData["Poor"] || evalData["Satisfactory"] || evalData["Very Satisfactory"]) {
                                            // This is the simplest format with just the ratings
                                            if (evalData["Excellent"]) {
                                                document.querySelector('.activity-rating[data-row="excellent"][data-col="batstateu"]').value = 
                                                    evalData["Excellent"]["BatStateU"] || 0;
                                                document.querySelector('.activity-rating[data-row="excellent"][data-col="others"]').value = 
                                                    evalData["Excellent"]["Others"] || 0;
                                            }
                                            
                                            if (evalData["Very Satisfactory"]) {
                                                document.querySelector('.activity-rating[data-row="very"][data-col="batstateu"]').value = 
                                                    evalData["Very Satisfactory"]["BatStateU"] || 0;
                                                document.querySelector('.activity-rating[data-row="very"][data-col="others"]').value = 
                                                    evalData["Very Satisfactory"]["Others"] || 0;
                                            }
                                            
                                            if (evalData["Satisfactory"]) {
                                                document.querySelector('.activity-rating[data-row="satisfactory"][data-col="batstateu"]').value = 
                                                    evalData["Satisfactory"]["BatStateU"] || 0;
                                                document.querySelector('.activity-rating[data-row="satisfactory"][data-col="others"]').value = 
                                                    evalData["Satisfactory"]["Others"] || 0;
                                            }
                                            
                                            if (evalData["Fair"]) {
                                                document.querySelector('.activity-rating[data-row="fair"][data-col="batstateu"]').value = 
                                                    evalData["Fair"]["BatStateU"] || 0;
                                                document.querySelector('.activity-rating[data-row="fair"][data-col="others"]').value = 
                                                    evalData["Fair"]["Others"] || 0;
                                            }
                                            
                                            if (evalData["Poor"]) {
                                                document.querySelector('.activity-rating[data-row="poor"][data-col="batstateu"]').value = 
                                                    evalData["Poor"]["BatStateU"] || 0;
                                                document.querySelector('.activity-rating[data-row="poor"][data-col="others"]').value = 
                                                    evalData["Poor"]["Others"] || 0;
                                            }
                                        }
                                        
                                        // Recalculate totals
                                        calculateTotals();
                                        calculateTimelinessTotal();
                                    }
                                } catch (e) {
                                    console.error("Error parsing evaluation data:", e);
                                    // If there's an error, just set the raw value to the hidden field
                                    document.getElementById('evaluation').value = narrative.evaluation || '';
                                }
                                
                                // After populating data, enable photo upload if both year and activity are set
                                updatePhotoUploadState();
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
        
        // Update showNarrativeDetails to display the new evaluation table format
        function showNarrativeDetails(narrativeId) {
            // Existing code...
            
            // Inside the success callback, modify the HTML generation for evaluation display
            if (response.success) {
                // Existing HTML generation...
                
                html += `<div class="mt-3"><h6>Evaluation Results:</h6>`;
                
                // Check if evaluation data exists and is in JSON format
                try {
                    if (narrative.evaluation) {
                        const evalData = JSON.parse(narrative.evaluation);
                        
                        // Check for new format (activity property)
                        if (evalData.activity) {
                            // Display activity ratings table
                            html += `
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th scope="col" style="width: 25%">Scale</th>
                                            <th scope="col">BatStateU Participants</th>
                                            <th scope="col">Participants from other Institutions</th>
                                            <th scope="col" style="width: 15%">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">Excellent</th>
                                            <td>${evalData.activity["Excellent"]?.["BatStateU"] || 0}</td>
                                            <td>${evalData.activity["Excellent"]?.["Others"] || 0}</td>
                                            <td>${(evalData.activity["Excellent"]?.["BatStateU"] || 0) + (evalData.activity["Excellent"]?.["Others"] || 0)}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Very Satisfactory</th>
                                            <td>${evalData.activity["Very Satisfactory"]?.["BatStateU"] || 0}</td>
                                            <td>${evalData.activity["Very Satisfactory"]?.["Others"] || 0}</td>
                                            <td>${(evalData.activity["Very Satisfactory"]?.["BatStateU"] || 0) + (evalData.activity["Very Satisfactory"]?.["Others"] || 0)}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Satisfactory</th>
                                            <td>${evalData.activity["Satisfactory"]?.["BatStateU"] || 0}</td>
                                            <td>${evalData.activity["Satisfactory"]?.["Others"] || 0}</td>
                                            <td>${(evalData.activity["Satisfactory"]?.["BatStateU"] || 0) + (evalData.activity["Satisfactory"]?.["Others"] || 0)}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Fair</th>
                                            <td>${evalData.activity["Fair"]?.["BatStateU"] || 0}</td>
                                            <td>${evalData.activity["Fair"]?.["Others"] || 0}</td>
                                            <td>${(evalData.activity["Fair"]?.["BatStateU"] || 0) + (evalData.activity["Fair"]?.["Others"] || 0)}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Poor</th>
                                            <td>${evalData.activity["Poor"]?.["BatStateU"] || 0}</td>
                                            <td>${evalData.activity["Poor"]?.["Others"] || 0}</td>
                                            <td>${(evalData.activity["Poor"]?.["BatStateU"] || 0) + (evalData.activity["Poor"]?.["Others"] || 0)}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            `;
                            
                            // Display timeliness ratings table if available
                            if (evalData.timeliness) {
                                html += `
                                <div class="mt-4">
                                    <label class="form-label">Number of Beneficiaries who rated The Timeliness of the activity as:</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th scope="col" style="width: 25%">Scale</th>
                                                    <th scope="col">BatStateU Participants</th>
                                                    <th scope="col">Participants from other Institutions</th>
                                                    <th scope="col" style="width: 15%">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th scope="row">Excellent</th>
                                                    <td>${evalData.timeliness["Excellent"]?.["BatStateU"] || 0}</td>
                                                    <td>${evalData.timeliness["Excellent"]?.["Others"] || 0}</td>
                                                    <td>${(evalData.timeliness["Excellent"]?.["BatStateU"] || 0) + (evalData.timeliness["Excellent"]?.["Others"] || 0)}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Very Satisfactory</th>
                                                    <td>${evalData.timeliness["Very Satisfactory"]?.["BatStateU"] || 0}</td>
                                                    <td>${evalData.timeliness["Very Satisfactory"]?.["Others"] || 0}</td>
                                                    <td>${(evalData.timeliness["Very Satisfactory"]?.["BatStateU"] || 0) + (evalData.timeliness["Very Satisfactory"]?.["Others"] || 0)}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Satisfactory</th>
                                                    <td>${evalData.timeliness["Satisfactory"]?.["BatStateU"] || 0}</td>
                                                    <td>${evalData.timeliness["Satisfactory"]?.["Others"] || 0}</td>
                                                    <td>${(evalData.timeliness["Satisfactory"]?.["BatStateU"] || 0) + (evalData.timeliness["Satisfactory"]?.["Others"] || 0)}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Fair</th>
                                                    <td>${evalData.timeliness["Fair"]?.["BatStateU"] || 0}</td>
                                                    <td>${evalData.timeliness["Fair"]?.["Others"] || 0}</td>
                                                    <td>${(evalData.timeliness["Fair"]?.["BatStateU"] || 0) + (evalData.timeliness["Fair"]?.["Others"] || 0)}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Poor</th>
                                                    <td>${evalData.timeliness["Poor"]?.["BatStateU"] || 0}</td>
                                                    <td>${evalData.timeliness["Poor"]?.["Others"] || 0}</td>
                                                    <td>${(evalData.timeliness["Poor"]?.["BatStateU"] || 0) + (evalData.timeliness["Poor"]?.["Others"] || 0)}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                `;
                            }
                        }
                        // Handle old format with ratings property
                        else if (evalData.ratings) {
                            html += `
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th scope="col" style="width: 25%">Scale</th>
                                            <th scope="col">BatStateU Participants</th>
                                            <th scope="col">Participants from other Institutions</th>
                                            <th scope="col" style="width: 15%">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">Excellent</th>
                                            <td>${evalData.ratings.excellent?.batstateu || 0}</td>
                                            <td>${evalData.ratings.excellent?.others || 0}</td>
                                            <td>${evalData.ratings.excellent?.total || 0}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Very Satisfactory</th>
                                            <td>${evalData.ratings.very_satisfactory?.batstateu || 0}</td>
                                            <td>${evalData.ratings.very_satisfactory?.others || 0}</td>
                                            <td>${evalData.ratings.very_satisfactory?.total || 0}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Satisfactory</th>
                                            <td>${evalData.ratings.satisfactory?.batstateu || 0}</td>
                                            <td>${evalData.ratings.satisfactory?.others || 0}</td>
                                            <td>${evalData.ratings.satisfactory?.total || 0}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Fair</th>
                                            <td>${evalData.ratings.fair?.batstateu || 0}</td>
                                            <td>${evalData.ratings.fair?.others || 0}</td>
                                            <td>${evalData.ratings.fair?.total || 0}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Poor</th>
                                            <td>${evalData.ratings.poor?.batstateu || 0}</td>
                                            <td>${evalData.ratings.poor?.others || 0}</td>
                                            <td>${evalData.ratings.poor?.total || 0}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Total</th>
                                            <td>${evalData.totals?.batstateu || 0}</td>
                                            <td>${evalData.totals?.others || 0}</td>
                                            <td>${evalData.totals?.grand_total || 0}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            `;
                            
                            // Add timeliness table if available
                            if (evalData.timeliness) {
                                html += `
                                <div class="mt-4">
                                    <label class="form-label">Number of Beneficiaries who rated The Timeliness of the activity as:</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th scope="col" style="width: 25%">Scale</th>
                                                    <th scope="col">BatStateU Participants</th>
                                                    <th scope="col">Participants from other Institutions</th>
                                                    <th scope="col" style="width: 15%">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th scope="row">Excellent</th>
                                                    <td>${evalData.timeliness.excellent?.batstateu || 0}</td>
                                                    <td>${evalData.timeliness.excellent?.others || 0}</td>
                                                    <td>${evalData.timeliness.excellent?.total || 0}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Very Satisfactory</th>
                                                    <td>${evalData.timeliness.very_satisfactory?.batstateu || 0}</td>
                                                    <td>${evalData.timeliness.very_satisfactory?.others || 0}</td>
                                                    <td>${evalData.timeliness.very_satisfactory?.total || 0}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Satisfactory</th>
                                                    <td>${evalData.timeliness.satisfactory?.batstateu || 0}</td>
                                                    <td>${evalData.timeliness.satisfactory?.others || 0}</td>
                                                    <td>${evalData.timeliness.satisfactory?.total || 0}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Fair</th>
                                                    <td>${evalData.timeliness.fair?.batstateu || 0}</td>
                                                    <td>${evalData.timeliness.fair?.others || 0}</td>
                                                    <td>${evalData.timeliness.fair?.total || 0}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Poor</th>
                                                    <td>${evalData.timeliness.poor?.batstateu || 0}</td>
                                                    <td>${evalData.timeliness.poor?.others || 0}</td>
                                                    <td>${evalData.timeliness.poor?.total || 0}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Total</th>
                                                    <td>${evalData.timeliness_totals?.batstateu || 0}</td>
                                                    <td>${evalData.timeliness_totals?.others || 0}</td>
                                                    <td>${evalData.timeliness_totals?.grand_total || 0}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                `;
                            }
                        }
                        // Handle direct object format (Excellent, Very Satisfactory, etc.)
                        else if (evalData["Excellent"] || evalData["Fair"] || evalData["Poor"] || evalData["Satisfactory"] || evalData["Very Satisfactory"]) {
                            html += `
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th scope="col" style="width: 25%">Scale</th>
                                            <th scope="col">BatStateU Participants</th>
                                            <th scope="col">Participants from other Institutions</th>
                                            <th scope="col" style="width: 15%">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">Excellent</th>
                                            <td>${evalData["Excellent"]?.["BatStateU"] || 0}</td>
                                            <td>${evalData["Excellent"]?.["Others"] || 0}</td>
                                            <td>${(evalData["Excellent"]?.["BatStateU"] || 0) + (evalData["Excellent"]?.["Others"] || 0)}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Very Satisfactory</th>
                                            <td>${evalData["Very Satisfactory"]?.["BatStateU"] || 0}</td>
                                            <td>${evalData["Very Satisfactory"]?.["Others"] || 0}</td>
                                            <td>${(evalData["Very Satisfactory"]?.["BatStateU"] || 0) + (evalData["Very Satisfactory"]?.["Others"] || 0)}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Satisfactory</th>
                                            <td>${evalData["Satisfactory"]?.["BatStateU"] || 0}</td>
                                            <td>${evalData["Satisfactory"]?.["Others"] || 0}</td>
                                            <td>${(evalData["Satisfactory"]?.["BatStateU"] || 0) + (evalData["Satisfactory"]?.["Others"] || 0)}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Fair</th>
                                            <td>${evalData["Fair"]?.["BatStateU"] || 0}</td>
                                            <td>${evalData["Fair"]?.["Others"] || 0}</td>
                                            <td>${(evalData["Fair"]?.["BatStateU"] || 0) + (evalData["Fair"]?.["Others"] || 0)}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Poor</th>
                                            <td>${evalData["Poor"]?.["BatStateU"] || 0}</td>
                                            <td>${evalData["Poor"]?.["Others"] || 0}</td>
                                            <td>${(evalData["Poor"]?.["BatStateU"] || 0) + (evalData["Poor"]?.["Others"] || 0)}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            `;
                        } else {
                            html += `<p>No evaluation data available</p>`;
                        }
                    } else {
                        html += `<p>No evaluation data available</p>`;
                    }
                } catch (e) {
                    // If not JSON, display as plain text
                    html += `<p>${narrative.evaluation || 'No evaluation data available'}</p>`;
                }
                
                html += `</div>`;
            }
        }
        
        // Add this to the DOMContentLoaded event
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize evaluation table calculations
            setupEvaluationTableCalculations();
            
            // Other initialization code...
        });

        // Function to clear temporary image uploads
        function clearTemporaryUploads() {
            console.log("Clearing temporary uploads");
            
            // Create FormData object
            const formData = new FormData();
            formData.append('clear_temp', 'true');
            
            // Send AJAX request to clear temporary uploads
            $.ajax({
                url: 'image_upload_handler.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    console.log("Temporary uploads cleared:", response);
                    
                    // Clear the preview container
                    const previewContainer = document.getElementById('photoPreviewContainer');
                    previewContainer.innerHTML = '';
                },
                error: function(xhr, status, error) {
                    console.error("Error clearing temporary uploads:", error);
                }
            });
        }
        
        // Function to reset the form
        function resetForm() {
            form.reset();
            currentNarrativeId = null;
            window.currentNarrativeId = null;
            document.getElementById('narrative_id').value = '0';
            
            // Reset buttons
            editBtn.innerHTML = '<i class="fas fa-edit"></i>';
            editBtn.classList.remove('editing');
            
            addBtn.innerHTML = '<i class="fas fa-plus"></i>';
            addBtn.classList.remove('btn-update');
            
            // Keep delete button enabled
            deleteBtn.classList.remove('btn-disabled');
            
            // Clear photo previews completely
            const previewContainer = document.getElementById('photoPreviewContainer');
            if (previewContainer) {
                previewContainer.innerHTML = '';
            }
            
            // Clear the file input
            const photoInput = document.getElementById('photoUpload');
            if (photoInput) {
                photoInput.value = '';
            }
            
            // Clear any stored image data or cached paths
            window.uploadedImagePaths = [];
            
            // Clear session storage for uploaded images
            if (window.sessionStorage) {
                sessionStorage.removeItem('uploadedImages');
            }
            
            // Reset the title field - remove readonly attribute and the explanatory note
            const titleSelect = document.getElementById('title');
            if (titleSelect) {
                titleSelect.removeAttribute('readonly');
                titleSelect.classList.remove('bg-light');
                // Reset all the style properties that were set in edit mode
                titleSelect.style.opacity = '';
                titleSelect.style.color = '';
                titleSelect.style.borderColor = '';
                titleSelect.style.cursor = '';
                
                // Remove the explanatory note if it exists
                const titleFormGroup = titleSelect.closest('.form-group') || titleSelect.closest('.mb-3') || titleSelect.parentElement;
                if (titleFormGroup) {
                    const readOnlyNote = titleFormGroup.querySelector('.form-text.text-muted');
                    if (readOnlyNote) {
                        readOnlyNote.remove();
                    }
                }
            }
            
            // For non-central users, restore their campus
            if (!isCentral && document.getElementById('campus_override')) {
                const campusValue = document.getElementById('campus_override').value;
                document.getElementById('campus').value = campusValue;
                
                // Reload years based on campus
                loadYearsForCampus(campusValue);
            } else {
                // For central users, just reset the dropdowns
                const campusSelect = document.getElementById('campus');
                if (campusSelect && campusSelect.options.length > 0) {
                    campusSelect.selectedIndex = 0;
                }
                
                const yearSelect = document.getElementById('year');
                if (yearSelect && yearSelect.options.length > 0) {
                    yearSelect.selectedIndex = 0;
                }
                
                // Title select already handled above
                if (titleSelect && titleSelect.options.length > 0) {
                    titleSelect.selectedIndex = 0;
                }
            }
            
            // Clear any status messages
            const saveStatus = document.getElementById('save-status');
            if (saveStatus) {
                saveStatus.innerHTML = '';
            }
            
            // Update photo upload state (should disable it since year/activity are reset)
            updatePhotoUploadState();
        }

        // Document ready function
        $(document).ready(function() {
            // Fix the title form group issue
            fixTitleFormGroupIssue();
            
            // Clear any old temporary uploads when the page loads
            clearTemporaryUploads();
            
            // Setup photo uploads
            setupPhotoUploads();
            
            // Initialize evaluation table calculations
            setupEvaluationTableCalculations();
            
            // Add global event listener to prevent selecting options with narratives
            const titleSelect = document.getElementById('title');
            if (titleSelect) {
                titleSelect.addEventListener('change', preventNarrativeSelection, true);
            }
        });

        // Function to filter narratives by campus
        function filterNarrativesByCampus(campus, action) {
            console.log(`Filtering narratives by campus: ${campus}, action: ${action}`);
            
            // Store the action for later use
            window.currentListAction = action || window.currentListAction || 'view';
            
            if (!campus) {
                // If no campus selected, show all narratives
                loadNarrativesAndShowList('', window.currentListAction);
                return;
            }
            
            // Show loading indicator
            const tableBody = document.querySelector('#narrativeListModal .table tbody');
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...</td></tr>';
            }
            
            // Filter the narratives by campus
            $.ajax({
                url: 'narrative_handler.php',
                type: 'POST',
                data: { 
                    action: 'read',
                    campus: campus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        narrativeData = response.data || [];
                        
                        // Check if we have data
                        if (narrativeData.length === 0) {
                            if (tableBody) {
                                tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No narratives found for this campus</td></tr>';
                            }
                        } else {
                            // Update the table body with filtered data
                            updateNarrativeTableBody(window.currentListAction);
                        }
                    } else {
                        console.error("Error loading narratives:", response.message);
                        narrativeData = [];
                        if (tableBody) {
                            tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading narratives</td></tr>';
                        }
                    }
                },
                error: function(xhr) {
                    console.error("AJAX Error:", xhr.responseText);
                    narrativeData = [];
                    if (tableBody) {
                        tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Server error</td></tr>';
                    }
                }
            });
        }
        
        // Function to update the narrative table body with current data
        function updateNarrativeTableBody(action) {
            console.log("Updating narrative table with action:", action);
            const tableBody = document.querySelector('#narrativeListModal .table tbody');
            if (!tableBody) return;
            
            // Use the provided action or fall back to the stored global action
            const currentAction = action || window.currentListAction || 'view';
            
            let html = '';
            
            // Create rows for each narrative
            narrativeData.forEach(narrative => {
                html += `
                    <tr class="narrative-row" data-id="${narrative.id}" data-action="${currentAction}">
                        <td>${narrative.title || 'Untitled'}</td>
                        <td>${narrative.campus || 'N/A'}</td>
                        <td>${narrative.year || 'N/A'}</td>
                        <td>${narrative.created_at || 'N/A'}</td>
                    </tr>
                `;
            });
            
            // Update the table body
            tableBody.innerHTML = html;
            
            // Re-add click event listeners to rows
            attachRowClickListeners();
        }
        
        // Function to attach click event listeners to narrative rows
        function attachRowClickListeners() {
            console.log("Attaching click listeners to rows");
            const rows = document.querySelectorAll('.narrative-row');
            console.log(`Found ${rows.length} rows to attach listeners to`);
            
            rows.forEach(row => {
                // Remove any existing click listeners to prevent duplicates
                row.replaceWith(row.cloneNode(true));
            });
            
            // Get the fresh nodes after cloning
            document.querySelectorAll('.narrative-row').forEach(row => {
                const narrativeId = row.getAttribute('data-id');
                const actionType = row.getAttribute('data-action');
                console.log(`Attaching listener to row with ID: ${narrativeId}, action: ${actionType}`);
                
                row.addEventListener('click', function(e) {
                    console.log(`Row clicked! ID: ${narrativeId}, Action: ${actionType}`);
                    
                    // Close the list modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('narrativeListModal'));
                    modal.hide();
                    
                    // Set current narrative ID
                    currentNarrativeId = narrativeId;
                    document.getElementById('narrative_id').value = narrativeId;
                    window.currentNarrativeId = narrativeId;
                    
                    // Handle different actions
                    if (actionType === 'view') {
                        showNarrativeDetails(narrativeId);
                    } else if (actionType === 'edit') {
                        // Enter edit mode
                        isEditing = true;
                        window.isEditing = true;
                        
                        // Get fresh button references
                        let editBtn = document.getElementById('editBtn');
                        let addBtn = document.getElementById('addBtn');
                        let deleteBtn = document.getElementById('deleteBtn');
                        
                        editBtn.innerHTML = '<i class="fas fa-times"></i>';
                        editBtn.classList.add('editing');
                        editBtn.title = 'Cancel editing';
                        
                        // Update add button to show it's for saving now
                        addBtn.innerHTML = '<i class="fas fa-save"></i>';
                        addBtn.title = 'Save changes';
                        addBtn.classList.add('btn-update');
                        
                        // Disable delete button while in edit mode
                        deleteBtn.classList.add('btn-disabled');
                        deleteBtn.title = 'Cannot delete while editing';
                        
                        // Load narrative data into form
                        loadNarrativeForEdit(narrativeId);
                    } else if (actionType === 'delete') {
                        // Call the function to show the delete confirmation
                        deleteNarrative();
                    }
                });
                
                // Make the row look clickable with a pointer cursor
                row.style.cursor = 'pointer';
            });
        }

        // Fix for the title form group issue
        function fixTitleFormGroupIssue() {
            // Fix for the Cannot read properties of null error
            const titleSelect = document.getElementById('title');
            if (titleSelect) {
                // Add a parent div with form-group class if it doesn't exist
                if (!titleSelect.closest('.form-group') && !titleSelect.closest('.mb-3')) {
                    const parent = titleSelect.parentElement;
                    if (parent) {
                        parent.classList.add('form-group');
                    }
                }
            }
        }

        // Function to prevent selection of options with narratives
        function preventNarrativeSelection(e) {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.getAttribute('data-has-narrative') === 'true') {
                // Prevent selection and show a message
                e.preventDefault();
                this.value = ''; // Reset to default option
                
                // Show a notification
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Not Allowed',
                    text: 'This activity already has a narrative and cannot be selected.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return false;
            }
        }

        // Function to style All Campuses elements
        function styleAllCampusesElements() {
            // Find all possible All Campuses elements
            const allElements = document.querySelectorAll('div, span, button, a, li, option');
            const theme = document.documentElement.getAttribute('data-bs-theme');
            
            allElements.forEach(el => {
                if (el.textContent.trim() === 'All Campuses') {
                    // Add our custom class
                    el.classList.add('all-campuses-filter');
                    
                    // Add direct styling for light mode
                    if (theme === 'light') {
                        el.style.backgroundColor = '#ffffff';
                        el.style.color = '#212529';
                        if (el.tagName !== 'OPTION') {
                            el.style.border = '1px solid #ced4da';
                        }
                    }
                }
            });
        }
    </script>
    <script src="../js/approval-badge.js"></script>
    
    
   
</body>
</html>
