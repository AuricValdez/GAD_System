<?php
session_start();

// Debug session information
error_log("Session data in personnel_list.php: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in - redirecting to login");
    header("Location: ../login.php");
    exit();
}

$isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnel List - GAD System</title>
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
            background: #e1bee7;
            color: var(--accent-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-menu .dropdown-item:hover {
            background: #e1bee7;
            color: var(--accent-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            background: #e1bee7;
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

        .analytics-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1.25rem;
            height: 170px;
            position: relative;
            transition: all 0.2s ease;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .analytics-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        /* Add horizontal bars for each card type */
        .analytics-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            /* Increased from 4px to 8px */
            opacity: 0.8;
        }

        /* Match horizontal bar colors with icon colors */
        .analytics-card[data-type="total"]::before {
            background-color: #a855f7;
        }

        .analytics-card[data-type="teaching"]::before {
            background-color: #10b981;
        }

        .analytics-card[data-type="non-teaching"]::before {
            background-color: #3b82f6;
        }

        .analytics-card[data-type="male"]::before {
            background-color: #ec4899;
        }

        .analytics-card[data-type="female"]::before {
            background-color: #ec4899;
        }

        .analytics-card[data-type="other"]::before {
            background-color: #ec4899;
        }

        .analytics-card-content {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            height: 100%;
        }

        .analytics-icon {
            width: 60px;
            /* Increased from 64px */
            height: 60px;
            /* Increased from 64px */
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            /* Increased from 1.75rem */
        }

        /* Update icon background colors to be slightly more opaque */
        .analytics-icon.total {
            background: rgba(168, 85, 247, 0.15);
            color: #a855f7;
        }

        .analytics-icon.teaching {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }

        .analytics-icon.non-teaching {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
        }

        .analytics-icon.male {
            background: rgba(236, 72, 153, 0.15);
            color: #ec4899;
        }

        .analytics-icon.female {
            background: rgba(236, 72, 153, 0.15);
            color: #ec4899;
        }

        .analytics-icon.other {
            background: rgba(236, 72, 153, 0.15);
            color: #ec4899;
        }

        /* Dark mode enhancements */
        [data-bs-theme="dark"] .analytics-card {
            background: rgba(255, 255, 255, 0.03);
            border-color: var(--dark-border);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        [data-bs-theme="dark"] .analytics-card:hover {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
        }

        [data-bs-theme="dark"] .analytics-card::before {
            opacity: 0.9;
        }

        .analytics-info {
            flex: 1;
        }

        .analytics-label {
            display: block;
            color: var(--text-secondary);
            font-size: 1 rem;
            /* Increased from 0.95rem */
            margin-bottom: 0.5rem;
            line-height: 1.2;
            font-weight: 500;
        }

        .analytics-value {
            color: var(--text-primary);
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0;
            line-height: 1.2;
        }

        /* Slide Navigation */
        .analytics-nav {
            position: absolute;
            bottom: 0.75rem;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 2;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .analytics-nav.prev {
            left: 1rem;
        }

        .analytics-nav.next {
            right: 1rem;
        }

        /* Slides */
        .analytics-slides {
            position: relative;
            height: 100%;
        }

        .analytics-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .analytics-slide.active {
            opacity: 1;
            visibility: visible;
        }

        /* Dots Navigation */
        .analytics-dots {
            position: absolute;
            bottom: 0.5rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.375rem;
        }

        .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--border-color);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .dot:hover {
            background: var(--text-secondary);
        }

        .dot.active {
            background: var(--accent-color);
            transform: scale(1.2);
        }

        /* Card Types */
        .analytics-icon.total {
            background: rgba(168, 85, 247, 0.1);
            color: #a855f7;
        }

        .analytics-icon.teaching {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .analytics-icon.non-teaching {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .analytics-icon.male {
            background: rgba(236, 72, 153, 0.15);
            color: #ec4899;
        }

        .analytics-icon.female {
            background: rgba(236, 72, 153, 0.15);
            color: #ec4899;
        }

        .analytics-icon.other {
            background: rgba(236, 72, 153, 0.15);
            color: #ec4899;
        }

        /* Dark Mode */
        [data-bs-theme="dark"] .analytics-card {
            background: rgba(255, 255, 255, 0.03);
            border-color: var(--dark-border);
        }

        [data-bs-theme="dark"] .analytics-nav {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--dark-border);
            color: rgba(255, 255, 255, 0.7);
        }

        [data-bs-theme="dark"] .analytics-nav:hover {
            background: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
        }

        [data-bs-theme="dark"] .analytics-label {
            color: rgba(255, 255, 255, 0.7);
        }

        [data-bs-theme="dark"] .analytics-value {
            color: rgba(255, 255, 255, 0.95);
        }

        [data-bs-theme="dark"] .dot {
            background: rgba(255, 255, 255, 0.2);
        }

        [data-bs-theme="dark"] .dot:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        [data-bs-theme="dark"] .dot.active {
            background: var(--accent-color);
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

            .analytics-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
                margin-top: 20px;
            }

            .analytics-card {
                margin-bottom: 0;
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

        #personnelForm {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        #personnelForm .row {
            flex: 1;
        }

        #personnelForm .col-12.text-end {
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

        /* Dark mode form fields */
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] #hourlyRate,
        [data-bs-theme="dark"] #searchInput,
        [data-bs-theme="dark"] #salaryGradeFilter {
            background-color: var(--dark-input) !important;
            color: var(--dark-text) !important;
            border-color: var(--dark-border) !important;
        }

        /* Fix for disabled inputs in dark mode */
        [data-bs-theme="dark"] input:disabled,
        [data-bs-theme="dark"] input[readonly],
        [data-bs-theme="dark"] #hourlyRate {
            background-color: rgba(108, 117, 125, 0.2) !important;
            color: #adb5bd !important;
            opacity: 0.8;
            cursor: not-allowed;
            border: 1px dashed #495057 !important;
        }

        [data-bs-theme="dark"] input:disabled::placeholder,
        [data-bs-theme="dark"] input[readonly]::placeholder {
            color: #6c757d !important;
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

        /* Light mode disabled/readonly inputs */
        [data-bs-theme="light"] input:disabled,
        [data-bs-theme="light"] input[readonly],
        [data-bs-theme="light"] #salaryGrade,
        [data-bs-theme="light"] #monthlySalary,
        [data-bs-theme="light"] #hourlyRate {
            background-color: #e9ecef !important;
            color: #6c757d !important;
            opacity: 0.8;
            cursor: not-allowed;
            border: 1px solid #dee2e6 !important;
        }

        /* Dark mode disabled select */
        [data-bs-theme="dark"] select:disabled {
            background-color: rgba(108, 117, 125, 0.2) !important;
            color: #adb5bd !important;
            opacity: 0.8;
            cursor: not-allowed;
            border: 1px dashed #495057 !important;
        }

        /* Light mode disabled select */
        [data-bs-theme="light"] select:disabled {
            background-color: #e9ecef !important;
            color: #6c757d !important;
            opacity: 0.8;
            cursor: not-allowed;
            border: 1px solid #dee2e6 !important;
        }

        /* Input group styling for disabled/readonly fields */
        [data-bs-theme="light"] .input-group>.form-control[readonly],
        [data-bs-theme="light"] .input-group>.form-control:disabled {
            background-color: #e9ecef !important;
        }

        [data-bs-theme="light"] .input-group-text {
            background-color: #e9ecef;
            border-color: #dee2e6;
        }

        [data-bs-theme="dark"] .input-group-text {
            background-color: rgba(108, 117, 125, 0.2);
            border-color: #495057;
            color: #adb5bd;
        }

        /* Update your gender-related styles */
        .gender-container {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .gender-options {
            display: flex;
            gap: 2rem;
            align-items: center;
            margin-left: 1.5rem;
        }

        .form-check {
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
        }

        .form-check-input {
            margin-right: 0.5rem;
        }

        .other-gender-wrapper {
            width: 100%;
            max-width: 200px;
            margin-top: 5px;
            margin-left: 25px !important;
            display: block !important;
        }

        #otherGender {
            display: block !important;
            /* Always visible */
            height: calc(1.5em + 0.75rem + 2px);
            /* Standard Bootstrap form-control height */
            padding: 0.375rem 0.75rem;
            /* Standard Bootstrap padding */
            transition: all 0.3s ease;
        }

        #otherGender:disabled {
            background-color: #f5f5f5;
            color: #999;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Responsive styles */
        @media (max-width: 576px) {
            .gender-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .other-gender-wrapper {
                position: static;
                width: 100%;
            }
        }

        .swal-blur-container {
            backdrop-filter: blur(5px);
        }

        /* Ensure the logo stays behind the blur */
        .logo-container {
            z-index: 1;
        }

        .swal2-container {
            z-index: 9999;
        }

        /* Add this with your other button styles */
        /* View button */
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

        /* Modal styles */
        .modal {
            z-index: 1055 !important;
        }

        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.7) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            z-index: 1050 !important;
        }

        .modal-backdrop.show {
            opacity: 1 !important;
        }

        .modal-content {
            z-index: 1056 !important;
        }

        .main-content.modal-open {
            filter: blur(5px);
            transition: filter 0.3s ease;
        }

        .modal-dialog {
            max-height: 90vh;
            /* Increase modal height to 90% of viewport height */
            margin: 5vh auto;
            /* Center vertically with 5vh margin top and bottom */
            z-index: 1056 !important;
        }

        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            margin-top: -20px;
            max-height: 90vh;
            /* Match dialog height */
            z-index: 1056 !important;
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
            max-height: calc(90vh - 120px);
            /* Account for header height */
            overflow-y: auto;
        }

        .modal-title {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1.5rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Table container height */
        .table-container {
            height: calc(90vh - 280px);
            /* Adjust for modal header, filters, and padding */
            display: flex;
            flex-direction: column;
        }

        .table-responsive {
            flex: 1;
            overflow-y: auto;
        }

        .table {
            color: var(--text-primary);
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 2px solid var(--border-color);
            font-weight: 600;
            position: sticky;
            top: 0;
            background: var(--card-bg);
            z-index: 1;
        }

        .table tbody td {
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            padding: 0.75rem;
        }

        /* Pagination styles */
        .pagination {
            display: flex;
            gap: 0.25rem;
            justify-content: center;
            margin-top: 1rem;
        }

        .page-link {
            border: none;
            padding: 0.5rem 1rem;
            color: var(--text-primary);
            background: var(--card-bg);
            border-radius: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .page-link:hover {
            background: var(--accent-color);
            color: white;
        }

        .page-link.active {
            background: var(--accent-color);
            color: white;
        }

        .page-link.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Dark mode specific styles */
        [data-bs-theme="dark"] .modal-content {
            background-color: var(--dark-sidebar);
        }

        [data-bs-theme="dark"] .table {
            color: var(--dark-text);
        }

        [data-bs-theme="dark"] .page-link {
            background: var(--dark-input);
            color: var(--dark-text);
            border: 1px solid var(--dark-border);
        }

        [data-bs-theme="dark"] .page-link:hover {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }

        [data-bs-theme="dark"] .page-link.active {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }

        [data-bs-theme="dark"] .page-link.disabled {
            background: var(--dark-input);
            color: var(--dark-border);
            border-color: var(--dark-border);
            opacity: 0.5;
        }

        /* Add styles for the "No personnel found" message */
        .no-data-message {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-secondary);
            font-size: 1.1rem;
            padding: 2rem;
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

        .cursor-pointer {
            cursor: pointer;
        }

        .cursor-pointer:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }

        /* SweetAlert Delete Confirmation Styles */
        .swal-delete-backdrop {
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
        }

        /* Add this to target the SweetAlert container directly */
        .swal2-container.swal2-backdrop-show {
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            background-color: rgba(0, 0, 0, 0.7) !important;
        }

        .swal-delete-popup {
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }

        .analytics-section {
            margin-bottom: 2rem;
        }

        .analytics-header {
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
        }

        .analytics-title {
            color: var(--text-primary);
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        .analytics-card {
            background: var(--card-bg);
            margin-bottom: 0.25rem;
        }

        .status-breakdown {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .status-breakdown small {
            margin-bottom: 0.125rem;
        }

        [data-bs-theme="dark"] .analytics-card {
            background: var(--dark-sidebar);
            border-color: var(--dark-border);
        }

        [data-bs-theme="dark"] .analytics-label {
            color: rgba(255, 255, 255, 0.6);
        }

        [data-bs-theme="dark"] .analytics-value {
            color: rgba(255, 255, 255, 0.9);
        }

        [data-bs-theme="dark"] .status-breakdown {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Make sure analytics section doesn't exceed sidebar height */
        .main-content {
            max-height: 100vh;
            overflow-y: auto;
            padding-bottom: 2rem;
        }

        /* Analytics Navigation Hover States */
        .analytics-card[data-type="teaching"] .analytics-nav:hover {
            background-color: #10b981;
            border-color: #10b981;
            color: white;
        }

        .analytics-card[data-type="non-teaching"] .analytics-nav:hover {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }

        .analytics-card[data-type="male"] .analytics-nav:hover,
        .analytics-card[data-type="female"] .analytics-nav:hover,
        .analytics-card[data-type="other"] .analytics-nav:hover {
            background-color: #ec4899;
            border-color: #ec4899;
            color: white;
        }

        /* Dark mode hover states */
        [data-bs-theme="dark"] .analytics-card[data-type="teaching"] .analytics-nav:hover {
            background-color: #10b981;
            border-color: #10b981;
            color: white;
        }

        [data-bs-theme="dark"] .analytics-card[data-type="non-teaching"] .analytics-nav:hover {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }

        [data-bs-theme="dark"] .analytics-card[data-type="male"] .analytics-nav:hover,
        [data-bs-theme="dark"] .analytics-card[data-type="female"] .analytics-nav:hover,
        [data-bs-theme="dark"] .analytics-card[data-type="other"] .analytics-nav:hover {
            background-color: #ec4899;
            border-color: #ec4899;
            color: white;
        }

        /* Analytics Pagination Bullet Colors */
        .analytics-card[data-type="teaching"] .analytics-dots .dot {
            background-color: rgba(16, 185, 129, 0.3);
            /* Light green */
        }

        .analytics-card[data-type="teaching"] .analytics-dots .dot.active {
            background-color: #10b981;
            /* Solid green */
            transform: scale(1.5);
        }

        .analytics-card[data-type="non-teaching"] .analytics-dots .dot {
            background-color: rgba(59, 130, 246, 0.3);
            /* Light blue */
        }

        .analytics-card[data-type="non-teaching"] .analytics-dots .dot.active {
            background-color: #3b82f6;
            /* Solid blue */
            transform: scale(1.5);
        }

        .analytics-card[data-type="male"] .analytics-dots .dot,
        .analytics-card[data-type="female"] .analytics-dots .dot,
        .analytics-card[data-type="other"] .analytics-dots .dot {
            background-color: rgba(236, 72, 153, 0.3);
            /* Light pink */
        }

        .analytics-card[data-type="male"] .analytics-dots .dot.active,
        .analytics-card[data-type="female"] .analytics-dots .dot.active,
        .analytics-card[data-type="other"] .analytics-dots .dot.active {
            background-color: #ec4899;
            /* Solid pink */
            transform: scale(1.5);
        }

        /* Dark mode bullet colors */
        [data-bs-theme="dark"] .analytics-card[data-type="teaching"] .analytics-dots .dot {
            background-color: rgba(16, 185, 129, 0.3);
        }

        [data-bs-theme="dark"] .analytics-card[data-type="teaching"] .analytics-dots .dot.active {
            background-color: #10b981;
        }

        [data-bs-theme="dark"] .analytics-card[data-type="non-teaching"] .analytics-dots .dot {
            background-color: rgba(59, 130, 246, 0.3);
        }

        [data-bs-theme="dark"] .analytics-card[data-type="non-teaching"] .analytics-dots .dot.active {
            background-color: #3b82f6;
        }

        [data-bs-theme="dark"] .analytics-card[data-type="male"] .analytics-dots .dot,
        [data-bs-theme="dark"] .analytics-card[data-type="female"] .analytics-dots .dot,
        [data-bs-theme="dark"] .analytics-card[data-type="other"] .analytics-dots .dot {
            background-color: rgba(236, 72, 153, 0.3);
        }

        [data-bs-theme="dark"] .analytics-card[data-type="male"] .analytics-dots .dot.active,
        [data-bs-theme="dark"] .analytics-card[data-type="female"] .analytics-dots .dot.active,
        [data-bs-theme="dark"] .analytics-card[data-type="other"] .analytics-dots .dot.active {
            background-color: #ec4899;
        }

        /* Analytics Card Title Colors */
        .analytics-card[data-type="total"] .analytics-label {
            color: #a855f7;
        }

        .analytics-card[data-type="teaching"] .analytics-label {
            color: #10b981;
        }

        .analytics-card[data-type="non-teaching"] .analytics-label {
            color: #3b82f6;
        }

        .analytics-card[data-type="male"] .analytics-label,
        .analytics-card[data-type="female"] .analytics-label,
        .analytics-card[data-type="other"] .analytics-label {
            color: #ec4899;
        }

        /* Dark mode title colors */
        [data-bs-theme="dark"] .analytics-card[data-type="total"] .analytics-label {
            color: #a855f7;
        }

        [data-bs-theme="dark"] .analytics-card[data-type="teaching"] .analytics-label {
            color: #10b981;
        }

        [data-bs-theme="dark"] .analytics-card[data-type="non-teaching"] .analytics-label {
            color: #3b82f6;
        }

        [data-bs-theme="dark"] .analytics-card[data-type="male"] .analytics-label,
        [data-bs-theme="dark"] .analytics-card[data-type="female"] .analytics-label,
        [data-bs-theme="dark"] .analytics-card[data-type="other"] .analytics-label {
            color: #ec4899;
        }

        /* Add these styles for consistent input focus with accent color */
        .form-control:focus,
        .form-select:focus,
        .form-check-input:focus,
        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        input[type="time"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        textarea:focus,
        select:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25) !important;
            outline: none !important;
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

        .nav-link {
            padding: 10px 15px;
            margin-bottom: 3px;
        }

        .nav-item .dropdown-menu .dropdown-item {
            padding: 6px 48px;
        }

        .approval-link {
            margin-top: 5px;
            margin-bottom: 10px;
        }

        .bottom-controls {
            margin-top: 15px;
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
                    <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                if ($isCentral):
                ?>
                    <a href="../approval/approval.php" class="nav-link approval-link">
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
            <h2>Personnel Management</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Add Personnel Forms</h5>
            </div>
            <div class="card-body">
                <form id="personnelForm">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6 pe-md-4">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="category" class="form-label">Category</label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Select Category</option>
                                            <option value="Teaching">Teaching</option>
                                            <option value="Non-teaching">Non-teaching</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status" required disabled>
                                            <option value="">Select Status</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label d-block">Gender</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center flex-wrap ms-4">
                                            <div class="form-check me-5 mb-0">
                                                <input class="form-check-input" type="radio" name="gender" id="male" value="male" required>
                                                <label class="form-check-label" for="male">Male</label>
                                            </div>
                                            <div class="form-check me-5 mb-0">
                                                <input class="form-check-input" type="radio" name="gender" id="female" value="female">
                                                <label class="form-check-label" for="female">Female</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6 ps-md-4">
                            <div class="form-group mb-3">
                                <label for="academicRank" class="form-label">Academic Rank</label>
                                <select class="form-select" id="academicRank" name="academicRank" required>
                                    <option value="">Select Academic Rank</option>
                                    <!-- Options will be populated from database -->
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="salaryGrade" class="form-label">Salary Grade</label>
                                <input type="text" class="form-control" id="salaryGrade" name="salaryGrade" readonly>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="monthlySalary" class="form-label">Monthly Salary</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="text" class="form-control" id="monthlySalary" name="monthlySalary" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="hourlyRate" class="form-label">Hourly Rate</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="text" class="form-control" id="hourlyRate" name="hourlyRate" readonly>
                                        </div>
                                    </div>
                                </div>
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

        <!-- Analytics Section -->
        <div class="analytics-section mt-4">
            <div class="row g-4">
                <!-- Total Personnel Card -->
                <div class="col-md-6 col-xl-3">
                    <div class="analytics-card" data-type="total">
                        <div class="analytics-card-content">
                            <div class="analytics-icon total">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="analytics-info">
                                <span class="analytics-label">Total Personnel</span>
                                <h3 class="analytics-value" id="totalPersonnel">0</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Teaching Personnel Card with Navigation -->
                <div class="col-md-6 col-xl-3">
                    <div class="analytics-card" data-type="teaching">
                        <div class="analytics-nav prev" onclick="prevTeachingStat()">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                        <div class="analytics-slides" id="teachingSlides">
                            <!-- Total Teaching -->
                            <div class="analytics-slide active" data-index="0">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon teaching">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Teaching Personnel</span>
                                        <h3 class="analytics-value" id="teachingPersonnel">0</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Permanent Teaching -->
                            <div class="analytics-slide" data-index="1">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon teaching">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Permanent Teaching</span>
                                        <h3 class="analytics-value" id="teachingPermanent">0</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Temporary Teaching -->
                            <div class="analytics-slide" data-index="2">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon teaching">
                                        <i class="fas fa-user-clock"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Temporary Teaching</span>
                                        <h3 class="analytics-value" id="teachingTemporary">0</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Guest Lecturer -->
                            <div class="analytics-slide" data-index="3">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon teaching">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Guest Lecturer</span>
                                        <h3 class="analytics-value" id="teachingGuest">0</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="analytics-nav next" onclick="nextTeachingStat()">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                        <div class="analytics-dots">
                            <span class="dot active" onclick="showTeachingSlide(0)"></span>
                            <span class="dot" onclick="showTeachingSlide(1)"></span>
                            <span class="dot" onclick="showTeachingSlide(2)"></span>
                            <span class="dot" onclick="showTeachingSlide(3)"></span>
                        </div>
                    </div>
                </div>

                <!-- Non-Teaching Personnel Card with Navigation -->
                <div class="col-md-6 col-xl-3">
                    <div class="analytics-card" data-type="non-teaching">
                        <div class="analytics-nav prev" onclick="prevNonTeachingStat()">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                        <div class="analytics-slides" id="nonTeachingSlides">
                            <!-- Total Non-Teaching -->
                            <div class="analytics-slide active" data-index="0">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon non-teaching">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Non-Teaching Personnel</span>
                                        <h3 class="analytics-value" id="nonTeachingPersonnel">0</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Permanent Non-Teaching -->
                            <div class="analytics-slide" data-index="1">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon non-teaching">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Permanent Non-Teaching</span>
                                        <h3 class="analytics-value" id="nonTeachingPermanent">0</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Job Order -->
                            <div class="analytics-slide" data-index="2">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon non-teaching">
                                        <i class="fas fa-file-contract"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Job Order</span>
                                        <h3 class="analytics-value" id="nonTeachingJobOrder">0</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Part-timer -->
                            <div class="analytics-slide" data-index="3">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon non-teaching">
                                        <i class="fas fa-user-clock"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Part-timer</span>
                                        <h3 class="analytics-value" id="nonTeachingPartTimer">0</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Casual -->
                            <div class="analytics-slide" data-index="4">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon non-teaching">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Casual</span>
                                        <h3 class="analytics-value" id="nonTeachingCasual">0</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="analytics-nav next" onclick="nextNonTeachingStat()">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                        <div class="analytics-dots">
                            <span class="dot active" onclick="showNonTeachingSlide(0)"></span>
                            <span class="dot" onclick="showNonTeachingSlide(1)"></span>
                            <span class="dot" onclick="showNonTeachingSlide(2)"></span>
                            <span class="dot" onclick="showNonTeachingSlide(3)"></span>
                            <span class="dot" onclick="showNonTeachingSlide(4)"></span>
                        </div>
                    </div>
                </div>

                <!-- Gender Distribution Card with Navigation -->
                <div class="col-md-6 col-xl-3">
                    <div class="analytics-card" data-type="male">
                        <div class="analytics-nav prev" onclick="prevGenderStat()">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                        <div class="analytics-slides" id="genderSlides">
                            <!-- Male Personnel -->
                            <div class="analytics-slide active" data-index="0">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon male">
                                        <i class="fas fa-male"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Male Personnel</span>
                                        <h3 class="analytics-value" id="malePersonnel">0</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Female Personnel -->
                            <div class="analytics-slide" data-index="1">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon female">
                                        <i class="fas fa-female"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Female Personnel</span>
                                        <h3 class="analytics-value" id="femalePersonnel">0</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Other Gender -->
                            <div class="analytics-slide" data-index="2">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon other">
                                        <i class="fas fa-transgender-alt"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Other Gender</span>
                                        <h3 class="analytics-value" id="otherPersonnel">0</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="analytics-nav next" onclick="nextGenderStat()">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                        <div class="analytics-dots">
                            <span class="dot active" onclick="showGenderSlide(0)"></span>
                            <span class="dot" onclick="showGenderSlide(1)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personnel View Modal -->
        <div class="modal fade" id="viewPersonnelModal" tabindex="-1" aria-labelledby="viewPersonnelModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-center w-100" id="viewPersonnelModalLabel">Personnel List</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-6 mb-2">
                                <input type="text" class="form-control" id="nameFilter" placeholder="Search by name...">
                            </div>
                            <div class="col-md-6 mb-2">
                                <select class="form-select" id="academicRankFilter">
                                    <option value="">All Academic Ranks</option>
                                </select>
                            </div>
                            <div class="col-md-<?php echo ($_SESSION['username'] === 'Central' ? '3' : '4'); ?> mb-2">
                                <select class="form-select" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    <option value="Teaching">Teaching</option>
                                    <option value="Non-teaching">Non-teaching</option>
                                </select>
                            </div>
                            <div class="col-md-<?php echo ($_SESSION['username'] === 'Central' ? '3' : '4'); ?> mb-2">
                                <select class="form-select" id="statusFilter" disabled>
                                    <option value="">Select Status</option>
                                </select>
                            </div>
                            <div class="col-md-<?php echo ($_SESSION['username'] === 'Central' ? '3' : '4'); ?> mb-2">
                                <select class="form-select" id="genderFilter">
                                    <option value="">All Genders</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <?php if ($_SESSION['username'] === 'Central'): ?>
                                <div class="col-md-3 mb-2">
                                    <select class="form-select" id="campusFilter">
                                        <option value="">All Campuses</option>
                                        <option value="Central">Central</option>
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
                            <?php endif; ?>
                        </div>

                        <!-- Table Container -->
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Gender</th>
                                            <th>Academic Rank</th>
                                            <?php if ($_SESSION['username'] === 'Central'): ?>
                                                <th>Campus</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody id="personnelTableBody">
                                        <!-- Table content will be dynamically populated -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div id="personnelCount" class="text-muted">
                                    <!-- Personnel count will be dynamically added here -->
                                </div>
                                <div id="pagination" class="d-flex justify-content-center">
                                    <!-- Pagination will be dynamically added here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personnel Edit Modal -->
        <div class="modal fade" id="editPersonnelModal" tabindex="-1" aria-labelledby="editPersonnelModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-center w-100" id="editPersonnelModalLabel">Edit Personnel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-6 mb-2">
                                <input type="text" class="form-control" id="editNameFilter" placeholder="Search by name...">
                            </div>
                            <div class="col-md-6 mb-2">
                                <select class="form-select" id="editAcademicRankFilter">
                                    <option value="">All Academic Ranks</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <select class="form-select" id="editCategoryFilter">
                                    <option value="">All Categories</option>
                                    <option value="Teaching">Teaching</option>
                                    <option value="Non-teaching">Non-teaching</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <select class="form-select" id="editStatusFilter" disabled>
                                    <option value="">All Status</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <select class="form-select" id="editGenderFilter">
                                    <option value="">All Genders</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <?php if ($_SESSION['username'] === 'Central'): ?>
                                <!-- Campus filter removed from edit modal for Central users -->
                            <?php endif; ?>
                        </div>

                        <!-- Table Container -->
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Gender</th>
                                            <th>Academic Rank</th>
                                            <?php if ($_SESSION['username'] === 'Central'): ?>
                                                <th>Campus</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody id="editPersonnelTableBody">
                                        <!-- Table content will be dynamically populated -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div id="editPersonnelCount" class="text-muted">
                                    <!-- Personnel count will be dynamically added here -->
                                </div>
                                <div id="editPagination" class="d-flex justify-content-center">
                                    <!-- Pagination will be dynamically added here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personnel Delete Modal -->
        <div class="modal fade" id="deletePersonnelModal" tabindex="-1" aria-labelledby="deletePersonnelModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-center w-100" id="deletePersonnelModalLabel">Delete Personnel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-6 mb-2">
                                <input type="text" class="form-control" id="deleteNameFilter" placeholder="Search by name...">
                            </div>
                            <div class="col-md-6 mb-2">
                                <select class="form-select" id="deleteAcademicRankFilter">
                                    <option value="">All Academic Ranks</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <select class="form-select" id="deleteCategoryFilter">
                                    <option value="">All Categories</option>
                                    <option value="Teaching">Teaching</option>
                                    <option value="Non-teaching">Non-teaching</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <select class="form-select" id="deleteStatusFilter" disabled>
                                    <option value="">All Status</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <select class="form-select" id="deleteGenderFilter">
                                    <option value="">All Genders</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <?php if ($_SESSION['username'] === 'Central'): ?>
                                <!-- Campus filter removed from delete modal for Central users -->
                            <?php endif; ?>
                        </div>

                        <!-- Table Container -->
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Gender</th>
                                            <th>Academic Rank</th>
                                            <?php if ($_SESSION['username'] === 'Central'): ?>
                                                <th>Campus</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody id="deletePersonnelTableBody">
                                        <!-- Table content will be dynamically populated -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div id="deletePersonnelCount" class="text-muted">
                                    <!-- Personnel count will be dynamically added here -->
                                </div>
                                <div id="deletePagination" class="d-flex justify-content-center">
                                    <!-- Pagination will be dynamically added here -->
                                </div>
                            </div>
                        </div>
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

        // Add this variable at the top with other variables
        let isLoadingData = false;

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

        // Update the filterPersonnel function
        function filterPersonnel() {
            console.log('filterPersonnel called');
            console.log('window.fullPersonnelData:', window.fullPersonnelData);

            if (!window.fullPersonnelData && !isLoadingData) {
                console.log('No personnel data available - loading data');
                isLoadingData = true;
                loadPersonnelData();
                return;
            } else if (isLoadingData) {
                console.log('Data is currently loading, skipping filter');
                return;
            }

            if (!Array.isArray(window.fullPersonnelData)) {
                console.error('fullPersonnelData is not an array:', typeof window.fullPersonnelData);
                return;
            }

            if (window.fullPersonnelData.length === 0) {
                console.log('fullPersonnelData is an empty array');
                filteredData = [];
                displayPersonnel();
                updatePagination();
                return;
            }

            const nameFilter = document.getElementById('nameFilter')?.value.toLowerCase() || '';
            const academicRankFilter = document.getElementById('academicRankFilter')?.value || '';
            const categoryFilter = document.getElementById('categoryFilter')?.value || '';
            const statusFilter = document.getElementById('statusFilter')?.value || '';
            const genderFilter = document.getElementById('genderFilter')?.value || '';
            const campusFilter = document.getElementById('campusFilter')?.value || '';

            // Debug log all current filter values
            console.log('Applied filters:', {
                name: nameFilter,
                rank: academicRankFilter,
                category: categoryFilter,
                status: statusFilter,
                gender: genderFilter,
                campus: campusFilter
            });

            try {
                filteredData = window.fullPersonnelData.filter(person => {
                    if (!person) {
                        console.error('Found null/undefined person in data');
                        return false;
                    }

                    console.log('Filtering person:', person);

                    const matchesName = person.name?.toLowerCase().includes(nameFilter) ?? false;
                    const matchesRank = !academicRankFilter || person.academic_rank === academicRankFilter;
                    const matchesCategory = !categoryFilter || person.category === categoryFilter;
                    const matchesStatus = !statusFilter || person.status === statusFilter;
                    const matchesGender = !genderFilter ||
                        (genderFilter === 'other' ?
                            (person.gender !== 'male' && person.gender !== 'female') :
                            person.gender?.toLowerCase() === genderFilter.toLowerCase());
                    const matchesCampus = !campusFilter || person.campus === campusFilter;

                    const matches = matchesName && matchesRank && matchesCategory &&
                        matchesStatus && matchesGender && matchesCampus;

                    if (!matches) {
                        console.log('Person did not match filters:', {
                            person,
                            matchesName,
                            matchesRank,
                            matchesCategory,
                            matchesStatus,
                            matchesGender,
                            matchesCampus
                        });
                    }

                    return matches;
                });

                console.log('Filtered data:', filteredData);

                // Update display
                displayPersonnel();
                updatePagination();
            } catch (error) {
                console.error('Error during filtering:', error);
                filteredData = [];
                displayPersonnel();
                updatePagination();
            }
        }

        // Make sure to initialize campus filter for Central user
        function initializeFilters(data) {
            // Initialize Academic Rank filter
            const uniqueRanks = [...new Set(data.map(person => person.academic_rank).filter(Boolean))];
            const rankFilter = document.getElementById('academicRankFilter');
            if (rankFilter) {
                rankFilter.innerHTML = '<option value="">All Academic Ranks</option>';
                uniqueRanks.sort().forEach(rank => {
                    rankFilter.innerHTML += `<option value="${rank}">${rank}</option>`;
                });
            }

            // Initialize Category filter
            const categoryFilter = document.getElementById('categoryFilter');
            if (categoryFilter) {
                categoryFilter.innerHTML = `
            <option value="">All Categories</option>
            <option value="Teaching">Teaching</option>
            <option value="Non-teaching">Non-teaching</option>
        `;
            }

            // Initialize Gender filter
            const genderFilter = document.getElementById('genderFilter');
            if (genderFilter) {
                genderFilter.innerHTML = `
            <option value="">All Genders</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
        `;
            }

            // Initialize Status filter (disabled by default, enabled when category is selected)
            const statusFilter = document.getElementById('statusFilter');
            if (statusFilter) {
                statusFilter.innerHTML = '<option value="">Select Status</option>';
                statusFilter.disabled = true;
            }

            // Initialize Campus filter for Central user
            const currentUser = '<?php echo $_SESSION["username"]; ?>';
            if (currentUser === 'Central') {
                const campusFilter = document.getElementById('campusFilter');
                if (campusFilter) {
                    campusFilter.innerHTML = `
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
            `;
                    // Add event listener for campus filter
                    campusFilter.addEventListener('change', filterPersonnel);
                }
            }

            // Add event listeners for all other filters
            const filters = ['nameFilter', 'academicRankFilter', 'categoryFilter', 'statusFilter', 'genderFilter'];
            filters.forEach(filterId => {
                const filter = document.getElementById(filterId);
                if (filter) {
                    if (filterId === 'nameFilter') {
                        filter.addEventListener('input', filterPersonnel);
                    } else {
                        filter.addEventListener('change', filterPersonnel);
                    }
                }
            });
        }

        // Update the updateStatusFilter function
        function updateStatusFilter() {
            const categoryFilter = document.getElementById('categoryFilter');
            const statusFilter = document.getElementById('statusFilter');
            const selectedCategory = categoryFilter.value;

            statusFilter.disabled = !selectedCategory;
            statusFilter.innerHTML = '<option value="">Select Status</option>';

            if (selectedCategory) {
                const statusOptions = {
                    'Teaching': ['Guest Lecturer', 'Permanent', 'Temporary'],
                    'Non-teaching': ['Casual', 'Job Order', 'Part-timer', 'Permanent']
                };

                statusOptions[selectedCategory].forEach(status => {
                    const option = document.createElement('option');
                    option.value = status;
                    option.textContent = status;
                    statusFilter.appendChild(option);
                });
            }

            filterPersonnel(); // Call filterPersonnel after updating status options
        }

        // Make sure these event listeners are properly set up
        document.addEventListener('DOMContentLoaded', function() {
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
            // ... other initialization code ...

            // Filter handlers
            document.getElementById('nameFilter').addEventListener('input', filterPersonnel);
            document.getElementById('academicRankFilter').addEventListener('change', filterPersonnel);
            document.getElementById('categoryFilter').addEventListener('change', updateStatusFilter);
            document.getElementById('statusFilter').addEventListener('change', filterPersonnel);
            document.getElementById('genderFilter').addEventListener('change', filterPersonnel);

            // Central user specific setup
            const currentUser = '<?php echo $_SESSION["username"]; ?>';
            console.log('Current user:', currentUser);

            if (currentUser === 'Central') {
                console.log('Central user detected');

                // REMOVE THE CODE THAT DISABLES FORMS FOR CENTRAL
                // Central users should be able to add/edit/delete their own personnel
                
                // Add campus filter when the view modal is shown
                document.getElementById('viewPersonnelModal').addEventListener('show.bs.modal', function() {
                    console.log('Modal is being shown');

                    // Add campus filter if it doesn't exist
                    if (!document.getElementById('campusFilter')) {
                        const filterRow = document.querySelector('#viewPersonnelModal .row.mb-3');
                        if (filterRow) {
                            const campusFilterHTML = `
                        <div class="col-md-<?php echo ($_SESSION['username'] === 'Central' ? '3' : '4'); ?> mb-2">
                            <select id="campusFilter" class="form-select" onchange="filterPersonnel()">
                                <option value="">All Campuses</option>
                                <option value="Central">Central</option>
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
                    `;
                            filterRow.insertAdjacentHTML('beforeend', campusFilterHTML);
                            console.log('Added campus filter');
                        }
                    }
                });
            }
        });

        // Update the displayPersonnel function
        function displayPersonnel() {
            const currentUser = '<?php echo $_SESSION["username"]; ?>';
            console.log('Current user:', currentUser);

            const tableBody = document.getElementById('personnelTableBody');
            const personnelCountDiv = document.getElementById('personnelCount');

            if (!tableBody || !personnelCountDiv) {
                console.error('Required elements not found');
                return;
            }

            tableBody.innerHTML = '';

            if (!filteredData || filteredData.length === 0) {
                personnelCountDiv.textContent = 'No personnel found';
                const colspanValue = currentUser === 'Central' ? '6' : '5';

                tableBody.innerHTML = `
            <tr>
                <td colspan="${colspanValue}" class="text-center py-5">
                    <div class="no-data-message">No personnel found.</div>
                </td>
            </tr>`;

                document.getElementById('pagination').innerHTML = '';
                return;
            }

            // Update personnel count text
            personnelCountDiv.textContent = `Total Personnel: ${filteredData.length}`;

            // Calculate pagination
            const start = (currentPage - 1) * rowsPerPage;
            const end = Math.min(start + rowsPerPage, filteredData.length);
            const paginatedData = filteredData.slice(start, end);

            // Create table rows
            paginatedData.forEach(person => {
                const row = document.createElement('tr');
                row.innerHTML = `
            <td>${person.name}</td>
            <td>${person.category}</td>
            <td>${person.status}</td>
            <td>${person.gender}</td>
            <td>${person.academic_rank || ''}</td>
            ${currentUser === 'Central' ? `<td>${person.campus}</td>` : ''}
        `;
                tableBody.appendChild(row);
            });

            // Update pagination
            const totalPages = Math.ceil(filteredData.length / rowsPerPage);
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';

            if (totalPages > 1) {
                const paginationContainer = document.createElement('div');
                paginationContainer.className = 'pagination-container';

                // Previous button
                const prevButton = document.createElement('button');
                prevButton.className = 'btn btn-sm btn-outline-secondary me-2';
                prevButton.innerHTML = '<i class="fas fa-chevron-left"></i>';
                prevButton.disabled = currentPage === 1;
                prevButton.onclick = () => {
                    if (currentPage > 1) {
                        currentPage--;
                        displayPersonnel();
                    }
                };
                paginationContainer.appendChild(prevButton);

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    const pageButton = document.createElement('button');
                    pageButton.className = `btn btn-sm ${currentPage === i ? 'btn-primary' : 'btn-outline-secondary'} me-2`;
                    pageButton.textContent = i;
                    pageButton.onclick = () => {
                        currentPage = i;
                        displayPersonnel();
                    };
                    paginationContainer.appendChild(pageButton);
                }

                // Next button
                const nextButton = document.createElement('button');
                nextButton.className = 'btn btn-sm btn-outline-secondary';
                nextButton.innerHTML = '<i class="fas fa-chevron-right"></i>';
                nextButton.disabled = currentPage === totalPages;
                nextButton.onclick = () => {
                    if (currentPage < totalPages) {
                        currentPage++;
                        displayPersonnel();
                    }
                };
                paginationContainer.appendChild(nextButton);

                pagination.appendChild(paginationContainer);
            }
        }

        // Update the pagination function
        function updatePagination() {
            const totalPages = Math.ceil(filteredData.length / rowsPerPage);
            const pagination = document.getElementById('pagination');
            if (!pagination) {
                console.error('Pagination element not found'); // Debug log
                return;
            }

            console.log('Updating pagination, total pages:', totalPages); // Debug log
            pagination.innerHTML = '';

            if (totalPages <= 1) {
                console.log('No pagination needed'); // Debug log
                return;
            }

            const paginationContainer = document.createElement('div');
            paginationContainer.className = 'pagination';

            // Previous button
            const prevButton = document.createElement('button');
            prevButton.className = `page-link ${currentPage === 1 ? 'disabled' : ''}`;
            prevButton.innerHTML = '<i class="fas fa-chevron-left"></i>';
            prevButton.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    displayPersonnel();
                }
            };
            paginationContainer.appendChild(prevButton);

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                const pageButton = document.createElement('button');
                pageButton.className = `page-link ${currentPage === i ? 'active' : ''}`;
                pageButton.textContent = i;
                pageButton.onclick = () => {
                    currentPage = i;
                    displayPersonnel();
                };
                paginationContainer.appendChild(pageButton);
            }

            // Next button
            const nextButton = document.createElement('button');
            nextButton.className = `page-link ${currentPage === totalPages ? 'disabled' : ''}`;
            nextButton.innerHTML = '<i class="fas fa-chevron-right"></i>';
            nextButton.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    displayPersonnel();
                }
            };
            paginationContainer.appendChild(nextButton);

            pagination.appendChild(paginationContainer);
        }

        // Update your viewBtn click handler
        document.getElementById('viewBtn').addEventListener('click', function() {
            const viewModal = new bootstrap.Modal(document.getElementById('viewPersonnelModal'));

            if (!window.fullPersonnelData) {
                // Show loading state in the modal
                const tableBody = document.getElementById('personnelTableBody');
                if (tableBody) {
                    tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading personnel data...</p>
                    </td>
                </tr>
            `;
                }

                // Show the modal first
                viewModal.show();

                // Then load the data
                const originalLoadPersonnelData = loadPersonnelData;
                loadPersonnelData = function() {
                    // Restore original function
                    loadPersonnelData = originalLoadPersonnelData;

                    console.log('Loading personnel data for view modal...');
                    fetch('get_personnel.php')
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.text().then(text => {
                                try {
                                    return JSON.parse(text);
                                } catch (e) {
                                    throw new Error('Invalid JSON response: ' + e.message);
                                }
                            });
                        })
                        .then(response => {
                            isLoadingData = false;

                            if (!response) {
                                throw new Error('Empty response received');
                            }

                            if (response.status === 'success' && Array.isArray(response.data)) {
                                const currentUser = '<?php echo $_SESSION["username"]; ?>';
                                window.fullPersonnelData = currentUser === 'Central' ?
                                    response.data :
                                    response.data.filter(person => person.campus === currentUser);

                                // Initialize filters and display data
                                initializeFilters(window.fullPersonnelData);
                                filterPersonnel();
                                updateAnalytics(response);
                            } else {
                                throw new Error('Invalid data format received');
                            }
                        })
                        .catch(error => {
                            console.error('Error loading personnel data:', error);
                            isLoadingData = false;
                            if (tableBody) {
                                tableBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center text-danger">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <p class="mt-2">Error loading data: ${error.message}</p>
                                </td>
                            </tr>
                        `;
                            }
                        });
                };

                loadPersonnelData();
            } else {
                viewModal.show();
                filterPersonnel();
            }
        });

        // Add edit button click handler
        document.getElementById('editBtn').addEventListener('click', function() {
            // Initialize edit modal variables
            window.editCurrentPage = 1;
            window.editRowsPerPage = 6; // Define rows per page for edit modal

            // Load data for edit modal
            loadEditPersonnelData();

            // Initialize and show edit modal
            const editModal = new bootstrap.Modal(document.getElementById('editPersonnelModal'));
            editModal.show();
        });

        // Make sure this is called when loading personnel data
        function loadPersonnelData() {
            console.log('Loading personnel data...');
            console.log('Current session username:', '<?php echo isset($_SESSION["username"]) ? $_SESSION["username"] : "Not set"; ?>');

            if (!document.getElementById('personnelTableBody')) {
                console.error('Personnel table body element not found');
                isLoadingData = false;
                return;
            }

            // Reset the data before loading
            window.fullPersonnelData = null;
            filteredData = [];

            fetch('get_personnel.php')
                .then(response => {
                    console.log('Raw response status:', response.status);
                    console.log('Raw response headers:', [...response.headers.entries()]);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text().then(text => {
                        console.log('Raw response text:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            throw new Error('Invalid JSON response: ' + e.message);
                        }
                    });
                })
                .then(response => {
                    console.log('Parsed response:', response);
                    isLoadingData = false; // Reset loading state

                    if (!response) {
                        throw new Error('Empty response received');
                    }

                    if (response.status === 'success' && Array.isArray(response.data)) {
                        const currentUser = '<?php echo $_SESSION["username"]; ?>';
                        console.log('Current user:', currentUser);
                        console.log('Response data:', response.data);

                        // Filter data based on user's campus - even for Central users
                        // Central users should only see Central personnel
                        window.fullPersonnelData = response.data.filter(person => person.campus === currentUser);

                        console.log('Filtered personnel data:', window.fullPersonnelData);

                        if (!window.fullPersonnelData || window.fullPersonnelData.length === 0) {
                            console.log('No personnel data after filtering');
                            // Show empty state in the table
                            displayPersonnel();
                            return;
                        }

                        // Initialize filters only after we have data
                        initializeFilters(window.fullPersonnelData);

                        // Update analytics and display
                        updateAnalytics(response);
                        currentPage = 1;

                        // Only filter if we have data
                        if (window.fullPersonnelData && window.fullPersonnelData.length > 0) {
                            filterPersonnel();
                        }
                    } else {
                        console.error('Invalid response format:', response);
                        throw new Error('Invalid data format received');
                    }
                })
                .catch(error => {
                    console.error('Error loading personnel data:', error);
                    // Clear any existing data
                    window.fullPersonnelData = null;
                    filteredData = [];
                    // Show error message
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load personnel data: ' + error.message,
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                    // Display empty state
                    displayPersonnel();
                });
        }

        function initializeFilters(data) {
            // Initialize Academic Rank filter
            const uniqueRanks = [...new Set(data.map(person => person.academic_rank).filter(Boolean))];
            const rankFilter = document.getElementById('academicRankFilter');
            if (rankFilter) {
                rankFilter.innerHTML = '<option value="">All Academic Ranks</option>';
                uniqueRanks.sort().forEach(rank => {
                    rankFilter.innerHTML += `<option value="${rank}">${rank}</option>`;
                });
            }

            // Initialize Category filter
            const categoryFilter = document.getElementById('categoryFilter');
            if (categoryFilter) {
                categoryFilter.innerHTML = `
            <option value="">All Categories</option>
            <option value="Teaching">Teaching</option>
            <option value="Non-teaching">Non-teaching</option>
        `;
            }

            // Initialize Gender filter
            const genderFilter = document.getElementById('genderFilter');
            if (genderFilter) {
                genderFilter.innerHTML = `
            <option value="">All Genders</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
        `;
            }

            // Initialize Status filter (disabled by default, enabled when category is selected)
            const statusFilter = document.getElementById('statusFilter');
            if (statusFilter) {
                statusFilter.innerHTML = '<option value="">Select Status</option>';
                statusFilter.disabled = true;
            }

            // Add event listeners
            if (categoryFilter) {
                categoryFilter.addEventListener('change', updateStatusFilter);
            }

            // Add event listeners for all filters
            const filters = ['nameFilter', 'academicRankFilter', 'categoryFilter', 'statusFilter', 'genderFilter'];
            filters.forEach(filterId => {
                const filter = document.getElementById(filterId);
                if (filter) {
                    if (filterId === 'nameFilter') {
                        filter.addEventListener('input', filterPersonnel);
                    } else {
                        filter.addEventListener('change', filterPersonnel);
                    }
                }
            });
        }

        // Add near your other event listeners
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
            const currentUser = '<?php echo $_SESSION["username"]; ?>';
            console.log('DOM loaded, current user:', currentUser);

            // Make sure the table header includes campus column for Central user
            if (currentUser === 'Central') {
                const headerRow = document.querySelector('#viewPersonnelModal table thead tr');
                if (headerRow && !headerRow.querySelector('th:last-child')?.textContent.includes('Campus')) {
                    headerRow.innerHTML += '<th>Campus</th>';
                }
            }

        });

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

        // Update time every second
        updateDateTime();
        setInterval(updateDateTime, 1000);

        document.addEventListener('DOMContentLoaded', function() {
            // Load academic ranks from database
            loadAcademicRanks();

            const categorySelect = document.getElementById('category');
            const statusSelect = document.getElementById('status');
            // Remove reference to otherGenderRadio which no longer exists
            const academicRankSelect = document.getElementById('academicRank');

            // Status options
            const statusOptions = {
                'Teaching': ['Guest Lecturer', 'Permanent', 'Temporary'],
                'Non-teaching': ['Casual', 'Job Order', 'Part-timer', 'Permanent']
            };

            // Category change handler
            categorySelect.addEventListener('change', function() {
                statusSelect.disabled = !this.value;
                statusSelect.innerHTML = '<option value="">Select Status</option>';

                if (this.value) {
                    statusOptions[this.value].forEach(status => {
                        const option = document.createElement('option');
                        option.value = status;
                        option.textContent = status;
                        statusSelect.appendChild(option);
                    });
                }
            });

            // Remove other gender handler which is no longer needed

            // Academic rank change handler
            academicRankSelect.addEventListener('change', function() {
                if (this.value) {
                    fetchAcademicRankDetails(this.value);
                } else {
                    clearAcademicRankDetails();
                }
            });
        });

        function loadAcademicRanks() {
            fetch('get_academic_ranks.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Parsed response:', data);
                    const academicRankSelect = document.getElementById('academicRank');
                    academicRankSelect.innerHTML = '<option value="">Select Academic Rank</option>';

                    if (data.data && Array.isArray(data.data)) {
                        data.data.forEach(rank => {
                            const option = document.createElement('option');
                            option.value = rank.id;
                            option.textContent = rank.academic_rank;
                            academicRankSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading academic ranks:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load academic ranks. Check console for details.',
                    });
                });
        }

        function fetchAcademicRankDetails(rankId) {
            // Use relative path instead of absolute URL
            fetch(`get_academic_rank_details.php?id=${rankId}`)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.log('Server response:', text);
                            throw new Error('Server returned: ' + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success' && data.data) {
                        document.getElementById('salaryGrade').value = data.data.salary_grade;
                        document.getElementById('monthlySalary').value = formatCurrency(data.data.monthly_salary);
                        document.getElementById('hourlyRate').value = formatCurrency(data.data.hourly_rate);
                    } else {
                        clearAcademicRankDetails();
                        console.warn('No details found for this rank');
                    }
                })
                .catch(error => {
                    console.error('Error loading rank details:', error);
                    clearAcademicRankDetails();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load rank details. Please try again later.',
                    });
                });
        }

        function clearAcademicRankDetails() {
            document.getElementById('salaryGrade').value = '';
            document.getElementById('monthlySalary').value = '';
            document.getElementById('hourlyRate').value = '';
        }

        function formatCurrency(value) {
            return new Intl.NumberFormat('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        }

        // Update your gender handling JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const genderRadios = document.querySelectorAll('input[name="gender"]');
            // We don't need to reference otherGenderInput anymore since it was removed

            // No need to disable a non-existent input

            // No need to add change event listeners for other gender functionality
        });

        // Add this to your existing JavaScript
        document.getElementById('personnelForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form data
            const academicRankSelect = document.getElementById('academicRank');
            const academicRankName = academicRankSelect.options[academicRankSelect.selectedIndex].text;

            const formData = {
                name: document.getElementById('name').value,
                category: document.getElementById('category').value,
                status: document.getElementById('status').value,
                gender: document.querySelector('input[name="gender"]:checked').value,
                academicRank: academicRankName,
                campus: '<?php echo $_SESSION["username"]; ?>' // This will set "Central" as campus when Central is logged in
            };

            // Send data to server
            fetch('add_personnel.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#6c757d',
                            timer: 1500,
                            showConfirmButton: false,
                            backdrop: `
                            rgba(0,0,0,0.7)
                        `,
                            allowOutsideClick: true,
                            customClass: {
                                container: 'swal-blur-container',
                                popup: 'logout-swal'
                            }
                        }).then(() => {
                            // Reset form
                            document.getElementById('personnelForm').reset();
                            clearAcademicRankDetails();

                            // Reset status field to disabled state
                            const statusSelect = document.getElementById('status');
                            const categorySelect = document.getElementById('category');
                            statusSelect.disabled = !categorySelect.value;
                            statusSelect.innerHTML = '<option value="">Select Status</option>';
                            loadPersonnelData();
                            // Reset the data and trigger a refresh
                            window.fullPersonnelData = null;
                            isLoadingData = false;

                            // Refresh data in all relevant tables
                            if (document.getElementById('viewPersonnelModal').classList.contains('show')) {
                                loadPersonnelData(); // Refresh view modal if it's open
                            }

                            // Also refresh edit modal data if it's open
                            if (document.getElementById('editPersonnelModal').classList.contains('show')) {
                                loadEditPersonnelData();
                            }

                            // Also refresh delete modal data if it's open
                            if (document.getElementById('deletePersonnelModal').classList.contains('show')) {
                                loadDeletePersonnelData();
                            }
                        });
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Failed to add personnel',
                        icon: 'error',
                        confirmButtonColor: '#d33',
                        backdrop: `
            rgba(0,0,0,0.7)
        `,
                        allowOutsideClick: true,
                        customClass: {
                            container: 'swal-blur-container',
                            popup: 'logout-swal'
                        }
                    }).then(() => {
                        // Reset form
                        document.getElementById('personnelForm').reset();
                        clearAcademicRankDetails();

                        // Reset status field to disabled state
                        const statusSelect = document.getElementById('status');
                        const categorySelect = document.getElementById('category');
                        statusSelect.disabled = !categorySelect.value;
                        statusSelect.innerHTML = '<option value="">Select Status</option>';
                    });
                });
        });
        // Add event listener for campus filter when modal is shown
        document.getElementById('viewPersonnelModal').addEventListener('show.bs.modal', function() {
            const currentUser = '<?php echo $_SESSION["username"]; ?>';
            if (currentUser === 'Central') {
                const campusFilter = document.getElementById('campusFilter');
                if (campusFilter && !campusFilter.hasEventListener) {
                    campusFilter.addEventListener('change', filterPersonnel);
                    campusFilter.hasEventListener = true;
                }
            }
        });

        // Function to load personnel data for the edit modal
        function loadEditPersonnelData() {
            console.log('Loading personnel data for edit modal...'); // Debug log

            // Get the current campus from PHP session
            const currentUser = '<?php echo $_SESSION["username"]; ?>';
            console.log('Current user:', currentUser); // Debug log

            fetch('get_personnel.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Parsed response for edit modal:', data);
                    if (data.status === 'success') {
                        // For ALL users, filter by campus - Central users should only see Central personnel
                        window.editPersonnelData = data.data.filter(person => person.campus === currentUser);

                        // Check if each record has an ID
                        window.editPersonnelData.forEach((person, index) => {
                            if (!person.id) {
                                console.error(`Person at index ${index} is missing ID:`, person);
                            }
                        });

                        console.log('Processed personnel data for edit:', window.editPersonnelData);

                        // Populate academic rank filter
                        const uniqueRanks = [...new Set(window.editPersonnelData.map(item => item.academic_rank))];
                        const rankFilter = document.getElementById('editAcademicRankFilter');
                        rankFilter.innerHTML = '<option value="">All Academic Ranks</option>';
                        uniqueRanks.forEach(rank => {
                            if (rank) { // Only add non-null ranks
                                rankFilter.innerHTML += `<option value="${rank}">${rank}</option>`;
                            }
                        });

                        filterEditPersonnel();
                    } else {
                        throw new Error(data.message || 'Failed to load personnel data for edit');
                    }
                })
                .catch(error => {
                    console.error('Error loading personnel for edit:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to load personnel data for edit',
                        confirmButtonColor: '#d33',
                        backdrop: `rgba(0,0,0,0.7)`,
                        customClass: {
                            container: 'swal-blur-container',
                            popup: 'logout-swal'
                        }
                    });
                });
        }

        // Function to filter personnel in the edit modal
        function filterEditPersonnel() {
            if (!window.editPersonnelData || !Array.isArray(window.editPersonnelData)) {
                console.log('No personnel data available for edit or invalid data');
                return;
            }

            console.log('Starting filtering for edit with data:', window.editPersonnelData); // Debug log

            const nameFilter = document.getElementById('editNameFilter').value.toLowerCase();
            const academicRankFilter = document.getElementById('editAcademicRankFilter').value;
            const categoryFilter = document.getElementById('editCategoryFilter').value;
            const statusFilter = document.getElementById('editStatusFilter').value;
            const genderFilter = document.getElementById('editGenderFilter').value;
            
            // For Central users, we'll only show Central data without filtering by campus
            // since the campus filter has been removed from the edit modal
            const campusFilter = '<?php echo $_SESSION["username"]; ?>';

            // Debug log all current filter values
            console.log('Applied filters for edit:', {
                name: nameFilter,
                rank: academicRankFilter,
                category: categoryFilter,
                status: statusFilter,
                gender: genderFilter,
                campus: campusFilter
            });

            editFilteredData = window.editPersonnelData.filter(person => {
                const matchesName = person.name.toLowerCase().includes(nameFilter);
                const matchesRank = !academicRankFilter || person.academic_rank === academicRankFilter;
                const matchesCategory = !categoryFilter || person.category === categoryFilter;
                const matchesStatus = !statusFilter || person.status === statusFilter;
                const matchesGender = !genderFilter ||
                    (genderFilter === 'other' ?
                        (person.gender !== 'male' && person.gender !== 'female') :
                        person.gender.toLowerCase() === genderFilter.toLowerCase());
                // For Central, only show Central personnel
                const matchesCampus = person.campus === campusFilter;

                return matchesName && matchesRank && matchesCategory &&
                    matchesStatus && matchesGender && matchesCampus;
            });

            console.log('Filtered results for edit:', editFilteredData); // Debug log

            editCurrentPage = 1; // Reset to first page when filtering
            displayEditPersonnel();
        }

        // Function to display personnel in the edit modal
        function displayEditPersonnel() {
            const currentUser = '<?php echo $_SESSION["username"]; ?>';
            console.log('Current user for edit:', currentUser); // Debug log

            const tableBody = document.getElementById('editPersonnelTableBody');
            const personnelCountDiv = document.getElementById('editPersonnelCount');

            if (!tableBody || !personnelCountDiv) {
                console.error('Edit table body or count element not found');
                return;
            }
            tableBody.innerHTML = '';

            console.log('Filtered data length for edit:', editFilteredData?.length); // Debug log

            if (!editFilteredData || editFilteredData.length === 0) {
                personnelCountDiv.textContent = 'No personnel found';
                const row = document.createElement('tr');
                // Adjust colspan based on whether user is Central (adds campus column)
                const colspanValue = currentUser === 'Central' ? '6' : '5';
                console.log('Using colspan for edit:', colspanValue); // Debug log

                row.innerHTML = `
                <td colspan="${colspanValue}" class="text-center py-5">
                <div class="no-data-message">No personnel found.</div>
                </td>
        `;
                tableBody.appendChild(row);
                document.getElementById('editPagination').innerHTML = '';
                return;
            }

            // Update personnel count text
            personnelCountDiv.textContent = `Total Personnel: ${editFilteredData.length}`;

            // Use the defined rows per page
            const start = (editCurrentPage - 1) * editRowsPerPage;
            const end = start + editRowsPerPage;
            const paginatedData = editFilteredData.slice(start, end);
            console.log('Paginated data for edit:', paginatedData); // Debug log

            paginatedData.forEach(person => {
                // Make sure person has an id
                if (!person.id) {
                    console.error('Person missing ID:', person);
                }

                const row = document.createElement('tr');
                row.style.cursor = 'pointer'; // Add pointer cursor to indicate clickable
                const campusColumn = currentUser === 'Central' ? `<td>${person.campus}</td>` : '';
                row.innerHTML = `
            <td>${person.name}</td>
            <td>${person.category}</td>
            <td>${person.status}</td>
            <td>${person.gender}</td>
            <td>${person.academic_rank}</td>
            ${campusColumn}
        `;

                // Add click event to the row
                row.addEventListener('click', function() {
                    // Store the original data for potential cancel operation
                    window.originalFormData = {
                        name: document.getElementById('name').value,
                        category: document.getElementById('category').value,
                        status: document.getElementById('status').value,
                        gender: document.querySelector('input[name="gender"]:checked')?.value || '',
                        academicRank: document.getElementById('academicRank').value
                    };

                    console.log('Selected person for edit:', person);
                    console.log('Person ID:', person.id);

                    // Fill the form with the selected person's data
                    const success = fillFormWithPersonData(person);

                    // Only proceed if fillFormWithPersonData was successful
                    if (success) {
                        // Close the edit modal
                        const editModal = bootstrap.Modal.getInstance(document.getElementById('editPersonnelModal'));
                        editModal.hide();

                        // Enter edit mode
                        enterEditMode();
                    }
                });

                tableBody.appendChild(row);
            });

            // Update pagination to match the style of the view personnel modal
            updateEditPagination();
        }

        // Function to fill the form with person data
        function fillFormWithPersonData(person) {
            // Check if person has an ID
            if (!person.id) {
                console.error('Person object is missing ID:', person);
                Swal.fire({
                    title: 'Error!',
                    text: 'Cannot edit this record: Missing ID',
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    backdrop: `rgba(0,0,0,0.7)`,
                    customClass: {
                        container: 'swal-blur-container',
                        popup: 'logout-swal'
                    }
                });
                return false;
            }

            // Store the person ID for update operation
            window.editingPersonId = person.id;
            console.log('Setting editingPersonId:', window.editingPersonId);

            // Set name
            document.getElementById('name').value = person.name;

            // Set category
            const categorySelect = document.getElementById('category');
            categorySelect.value = person.category;

            // Enable and set status
            const statusSelect = document.getElementById('status');
            statusSelect.disabled = false;

            // Update status options based on category
            updateStatusOptions(person.category);

            // Set status after options are updated
            statusSelect.value = person.status;

            // Set gender
            const genderValue = person.gender.toLowerCase();

            console.log('Setting gender for:', person.gender, 'Lowercase value:', genderValue);

            if (genderValue === 'male' || genderValue === 'female') {
                document.getElementById(genderValue).checked = true;
            } else {
                // If gender is not male or female, default to male
                document.getElementById('male').checked = true;
            }

            // Update the state of the other gender input field
            updateOtherGenderVisibility();

            // Set academic rank
            const academicRankSelect = document.getElementById('academicRank');

            // Find the option with matching text
            for (let i = 0; i < academicRankSelect.options.length; i++) {
                if (academicRankSelect.options[i].text === person.academic_rank) {
                    academicRankSelect.selectedIndex = i;
                    break;
                }
            }

            // Trigger change event to update salary grade, monthly salary, and hourly rate
            const event = new Event('change');
            academicRankSelect.dispatchEvent(event);

            // Log for debugging
            console.log('Filled form with person data:', person);
            console.log('Gender value:', genderValue);

            return true;
        }

        // Function to enter edit mode
        function enterEditMode() {
            // Change card title to Edit Personnel Forms
            const cardTitle = document.querySelector('.card-header .card-title');
            if (cardTitle) {
                cardTitle.textContent = 'Edit Personnel Forms';
            }
            
            // Change Add button to Update button
            const addBtn = document.getElementById('addBtn');
            addBtn.innerHTML = '<i class="fas fa-save"></i>';
            addBtn.setAttribute('type', 'button'); // Change from submit to button
            addBtn.id = 'updateBtn';

            // Keep the same color palette as addBtn
            document.getElementById('updateBtn').style.background = 'rgba(25, 135, 84, 0.1)';
            document.getElementById('updateBtn').style.color = '#198754';

            // Add hover effect
            document.getElementById('updateBtn').addEventListener('mouseenter', function() {
                this.style.background = '#198754';
                this.style.color = 'white';
            });

            document.getElementById('updateBtn').addEventListener('mouseleave', function() {
                this.style.background = 'rgba(25, 135, 84, 0.1)';
                this.style.color = '#198754';
            });

            // Change Edit button to Cancel button
            const editBtn = document.getElementById('editBtn');
            editBtn.innerHTML = '<i class="fas fa-times"></i>';
            editBtn.classList.add('editing');

            // Disable and gray out Delete button
            const deleteBtn = document.getElementById('deleteBtn');
            deleteBtn.classList.add('btn-disabled');

            // Make name input field non-interactible but keep alignment
            const nameInput = document.getElementById('name');
            nameInput.readOnly = true;
            // Use a different approach to style the readonly field without changing alignment
            nameInput.style.backgroundColor = 'rgba(0, 0, 0, 0.05)';
            nameInput.style.cursor = 'not-allowed';

            // Add event listeners for update and cancel buttons
            document.getElementById('updateBtn').addEventListener('click', handleUpdate);
            editBtn.addEventListener('click', exitEditMode);
        }

        // Function to exit edit mode
        function exitEditMode() {
            // Change card title back to Add Personnel Forms
            const cardTitle = document.querySelector('.card-header .card-title');
            if (cardTitle) {
                cardTitle.textContent = 'Add Personnel Forms';
            }
            
            // Restore original form data if available
            if (window.originalFormData) {
                document.getElementById('name').value = window.originalFormData.name;
                document.getElementById('category').value = window.originalFormData.category;

                // Update status options based on category
                updateStatusOptions(window.originalFormData.category);

                document.getElementById('status').value = window.originalFormData.status;

                // Restore gender
                if (window.originalFormData.gender === 'male' || window.originalFormData.gender === 'female') {
                    document.getElementById(window.originalFormData.gender).checked = true;
                } else {
                    // If gender is not male or female, default to male
                    document.getElementById('male').checked = true;
                }

                // Restore academic rank
                document.getElementById('academicRank').value = window.originalFormData.academicRank;

                // Trigger change event to update salary grade, monthly salary, and hourly rate
                const event = new Event('change');
                document.getElementById('academicRank').dispatchEvent(event);
            } else {
                // If no original data, just reset the form
                document.getElementById('personnelForm').reset();
                clearAcademicRankDetails();
            }

            // Change Update button back to Add button
            const updateBtn = document.getElementById('updateBtn');
            updateBtn.innerHTML = '<i class="fas fa-plus"></i>';
            updateBtn.setAttribute('type', 'submit');
            updateBtn.id = 'addBtn';

            // Remove hover event listeners
            updateBtn.removeEventListener('mouseenter', function() {
                this.style.background = '#198754';
                this.style.color = 'white';
            });

            updateBtn.removeEventListener('mouseleave', function() {
                this.style.background = 'rgba(25, 135, 84, 0.1)';
                this.style.color = '#198754';
            });

            // Change Cancel button back to Edit button
            const editBtn = document.getElementById('editBtn');
            editBtn.innerHTML = '<i class="fas fa-edit"></i>';
            editBtn.classList.remove('editing');

            // Enable Delete button
            const deleteBtn = document.getElementById('deleteBtn');
            deleteBtn.classList.remove('btn-disabled');

            // Make name input field interactible again
            const nameInput = document.getElementById('name');
            nameInput.readOnly = false;
            nameInput.style.backgroundColor = '';
            nameInput.style.cursor = '';
            nameInput.classList.remove('form-control-plaintext');

            // Remove event listeners
            document.getElementById('addBtn').removeEventListener('click', handleUpdate);
            editBtn.removeEventListener('click', exitEditMode);

            // Clear editing person ID
            window.editingPersonId = null;
            window.originalFormData = null;
        }

        // Function to handle update
        function handleUpdate() {
            // Validate all required fields
            const name = document.getElementById('name').value.trim();
            const category = document.getElementById('category').value;
            const status = document.getElementById('status').value;
            const selectedGender = document.querySelector('input[name="gender"]:checked');
            // Remove reference to otherGenderInput
            const academicRankSelect = document.getElementById('academicRank');
            const academicRank = academicRankSelect.value;

            // Check if all required fields are filled
            let missingFields = [];

            if (!name) missingFields.push('Name');
            if (!category) missingFields.push('Category');
            if (!status) missingFields.push('Status');
            if (!selectedGender) missingFields.push('Gender');
            // Remove check for other gender field
            if (!academicRank) missingFields.push('Academic Rank');

            // If any required fields are missing, show error and return
            if (missingFields.length > 0) {
                Swal.fire({
                    title: 'Required Fields Missing!',
                    html: `Please fill in the following required fields:<br>${missingFields.join('<br>')}`,
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    backdrop: `rgba(0,0,0,0.7)`,
                    customClass: {
                        container: 'swal-blur-container',
                        popup: 'logout-swal'
                    }
                });
                return;
            }

            // Get form data
            const academicRankName = academicRankSelect.options[academicRankSelect.selectedIndex].text;

            const formData = {
                id: window.editingPersonId,
                name: name,
                category: category,
                status: status,
                gender: selectedGender.value,
                academicRank: academicRankName,
                campus: '<?php echo $_SESSION["username"]; ?>'
            };

            // Debug log
            console.log('Updating personnel with data:', formData);
            console.log('Person ID:', window.editingPersonId);

            // Validate that we have an ID
            if (!window.editingPersonId) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Missing personnel ID. Please try selecting the record again.',
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    backdrop: `rgba(0,0,0,0.7)`,
                    customClass: {
                        container: 'swal-blur-container',
                        popup: 'logout-swal'
                    }
                });
                return;
            }

            // Send data to server
            fetch('update_personnel.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    // Check if response is ok before trying to parse JSON
                    if (!response.ok) {
                        throw new Error(`Server responded with status: ${response.status}`);
                    }
                    return response.text().then(text => {
                        // Try to parse as JSON, but handle case where it's not valid JSON
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Invalid JSON response:', text);
                            throw new Error('Server returned invalid JSON: ' + text.substring(0, 100) + '...');
                        }
                    });
                })
                .then(data => {
                    console.log('Update response:', data);
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#6c757d',
                            timer: 1500,
                            showConfirmButton: false,
                            backdrop: `rgba(0,0,0,0.7)`,
                            allowOutsideClick: true,
                            customClass: {
                                container: 'swal-blur-container',
                                popup: 'logout-swal'
                            }
                        }).then(() => {
                            // Exit edit mode
                            exitEditMode();

                            // Reset cached data to force refresh
                            window.fullPersonnelData = null;

                            // Reload personnel data for analytics
                            loadPersonnelData();

                            // Refresh view modal if it's open
                            if (document.getElementById('viewPersonnelModal').classList.contains('show')) {
                                // Fetch fresh data for view modal
                                fetch('get_personnel.php')
                                    .then(response => response.json())
                                    .then(response => {
                                        if (response.status === 'success' && Array.isArray(response.data)) {
                                            const currentUser = '<?php echo $_SESSION["username"]; ?>';
                                            window.fullPersonnelData = currentUser === 'Central' ?
                                                response.data :
                                                response.data.filter(person => person.campus === currentUser);

                                            // Initialize filters and display data
                                            initializeFilters(window.fullPersonnelData);
                                            filterPersonnel();
                                        }
                                    })
                                    .catch(error => console.error('Error refreshing view modal:', error));
                            }

                            // Refresh edit modal if it's open
                            if (document.getElementById('editPersonnelModal').classList.contains('show')) {
                                loadEditPersonnelData();
                            }

                            // Refresh delete modal if it's open
                            if (document.getElementById('deletePersonnelModal').classList.contains('show')) {
                                loadDeletePersonnelData();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to update personnel',
                            icon: 'error',
                            confirmButtonColor: '#d33',
                            backdrop: `rgba(0,0,0,0.7)`,
                            customClass: {
                                container: 'swal-blur-container',
                                popup: 'logout-swal'
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error updating personnel:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to update personnel: ' + error.message,
                        icon: 'error',
                        confirmButtonColor: '#d33',
                        backdrop: `rgba(0,0,0,0.7)`,
                        customClass: {
                            container: 'swal-blur-container',
                            popup: 'logout-swal'
                        }
                    });
                });
        }

        // Function to update status options based on category
        function updateStatusOptions(category) {
            const statusSelect = document.getElementById('status');
            statusSelect.innerHTML = '<option value="">Select Status</option>';

            if (category) {
                const statusOptions = {
                    'Teaching': ['Guest Lecturer', 'Permanent', 'Temporary'],
                    'Non-teaching': ['Casual', 'Job Order', 'Part-timer', 'Permanent']
                };

                statusOptions[category].forEach(status => {
                    const option = document.createElement('option');
                    option.value = status;
                    option.textContent = status;
                    statusSelect.appendChild(option);
                });

                statusSelect.disabled = false;
            } else {
                statusSelect.disabled = true;
            }
        }

        // New function to update pagination for edit modal
        function updateEditPagination() {
            const totalPages = Math.ceil(editFilteredData.length / editRowsPerPage);
            const pagination = document.getElementById('editPagination');
            if (!pagination) {
                console.error('Edit pagination element not found'); // Debug log
                return;
            }

            console.log('Updating edit pagination, total pages:', totalPages); // Debug log
            pagination.innerHTML = '';

            if (totalPages <= 1) {
                console.log('No edit pagination needed'); // Debug log
                return;
            }

            const paginationContainer = document.createElement('div');
            paginationContainer.className = 'pagination';

            // Previous button
            const prevButton = document.createElement('button');
            prevButton.className = `page-link ${editCurrentPage === 1 ? 'disabled' : ''}`;
            prevButton.innerHTML = '<i class="fas fa-chevron-left"></i>';
            prevButton.onclick = () => {
                if (editCurrentPage > 1) {
                    editCurrentPage--;
                    displayEditPersonnel();
                }
            };
            paginationContainer.appendChild(prevButton);

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                const pageButton = document.createElement('button');
                pageButton.className = `page-link ${editCurrentPage === i ? 'active' : ''}`;
                pageButton.textContent = i;
                pageButton.onclick = () => {
                    editCurrentPage = i;
                    displayEditPersonnel();
                };
                paginationContainer.appendChild(pageButton);
            }

            // Next button
            const nextButton = document.createElement('button');
            nextButton.className = `page-link ${editCurrentPage === totalPages ? 'disabled' : ''}`;
            nextButton.innerHTML = '<i class="fas fa-chevron-right"></i>';
            nextButton.onclick = () => {
                if (editCurrentPage < totalPages) {
                    editCurrentPage++;
                    displayEditPersonnel();
                }
            };
            paginationContainer.appendChild(nextButton);

            pagination.appendChild(paginationContainer);
        }

        // Add event listeners for edit modal filters
        document.getElementById('editNameFilter').addEventListener('input', filterEditPersonnel);
        document.getElementById('editAcademicRankFilter').addEventListener('change', filterEditPersonnel);
        document.getElementById('editCategoryFilter').addEventListener('change', updateEditStatusFilter);
        document.getElementById('editStatusFilter').addEventListener('change', filterEditPersonnel);
        document.getElementById('editGenderFilter').addEventListener('change', filterEditPersonnel);
        // Campus filter removed for edit modal

        // Function to update status filter in edit modal based on category
        function updateEditStatusFilter() {
            const categoryFilter = document.getElementById('editCategoryFilter');
            const statusFilter = document.getElementById('editStatusFilter');
            const selectedCategory = categoryFilter.value;

            statusFilter.disabled = !selectedCategory;
            statusFilter.innerHTML = '<option value="">All Status</option>';

            if (selectedCategory) {
                const statusOptions = {
                    'Teaching': ['Guest Lecturer', 'Permanent', 'Temporary'],
                    'Non-teaching': ['Casual', 'Job Order', 'Part-timer', 'Permanent']
                };

                statusOptions[selectedCategory].forEach(status => {
                    statusFilter.innerHTML += `<option value="${status}">${status}</option>`;
                });
            }

            filterEditPersonnel(); // Call filterEditPersonnel after updating status options
        }

        // Add event listener for edit modal show event
        document.getElementById('editPersonnelModal').addEventListener('show.bs.modal', function() {
            // Initialize variables for edit modal
            window.editCurrentPage = 1;
            window.editRowsPerPage = 6; // Define rows per page for edit modal
            window.editFilteredData = [];
        });

        // Add event listener for category change to update status options
        document.getElementById('category').addEventListener('change', function() {
            updateStatusOptions(this.value);
        });

        // Function to clear academic rank details
        function clearAcademicRankDetails() {
            document.getElementById('salaryGrade').value = '';
            document.getElementById('monthlySalary').value = '';
            document.getElementById('hourlyRate').value = '';
        }

        // Add event listeners for gender radio buttons
        document.querySelectorAll('input[name="gender"]').forEach(radio => {
            radio.addEventListener('change', function() {
                updateOtherGenderVisibility();
            });
        });

        // Hide other gender input field initially
        // document.getElementById('otherGender').style.display = 'none';

        // ... existing code ...

        // Function to handle the visibility of the other gender input field
        function updateOtherGenderVisibility() {
            // This function can now be empty or simply log that it was called
            console.log('updateOtherGenderVisibility called - no action needed as other gender was removed');
            // No need to reference elements that don't exist anymore
        }

        // Delete button click handler
        document.getElementById('deleteBtn').addEventListener('click', function() {
            loadDeletePersonnelData();
            const deleteModal = new bootstrap.Modal(document.getElementById('deletePersonnelModal'));
            deleteModal.show();
        });

        // Function to load personnel data for delete modal
        function loadDeletePersonnelData() {
            console.log('Loading delete personnel data...');
            console.log('Current user:', '<?php echo $_SESSION["username"]; ?>');

            // Fetch personnel data
            fetch('get_personnel.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Check for success using 'status' key instead of 'success'
                    if (data.status !== 'success') {
                        throw new Error(data.message || 'Failed to load personnel data');
                    }

                    // Filter data to only show personnel from current user's campus
                    const currentUser = '<?php echo $_SESSION["username"]; ?>';
                    let filteredData = data.data.filter(person => person.campus === currentUser);

                    // Check if each record has an ID
                    filteredData.forEach(person => {
                        if (!person.id) {
                            console.error('Person record missing ID:', person);
                        }
                    });

                    console.log('Filtered data length:', filteredData.length);

                    // Store the data for pagination
                    window.deletePersonnelData = filteredData;

                    // Populate filters
                    populateDeleteFilters(filteredData);

                    // Display the personnel data
                    displayDeletePersonnel(filteredData);
                })
                .catch(error => {
                    console.error('Error loading personnel data:', error);

                    // Display error message in the table instead of SweetAlert
                    const tableBody = document.getElementById('deletePersonnelTableBody');
                    tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">
                        <i class="fas fa-exclamation-circle"></i> Failed to load personnel data: ${error.message}
                    </td></tr>`;

                    // Clear pagination
                    document.getElementById('deletePagination').innerHTML = '';
                });
        }

        // Function to populate delete modal filters
        function populateDeleteFilters(data) {
            // Populate academic rank filter
            const uniqueRanks = [...new Set(data.map(person =>
                person.academicRank || person.academic_rank || ''
            ))].filter(Boolean);

            const deleteAcademicRankFilter = document.getElementById('deleteAcademicRankFilter');
            deleteAcademicRankFilter.innerHTML = '<option value="">All Academic Ranks</option>';
            uniqueRanks.forEach(rank => {
                const option = document.createElement('option');
                option.value = rank;
                option.textContent = rank;
                deleteAcademicRankFilter.appendChild(option);
            });

            // Set up status filter based on category
            const deleteCategoryFilter = document.getElementById('deleteCategoryFilter');
            const deleteStatusFilter = document.getElementById('deleteStatusFilter');

            // Status options
            const statusOptions = {
                'Teaching': ['Guest Lecturer', 'Permanent', 'Temporary'],
                'Non-teaching': ['Casual', 'Job Order', 'Part-timer', 'Permanent']
            };

            // Category change handler
            deleteCategoryFilter.addEventListener('change', function() {
                deleteStatusFilter.disabled = !this.value;
                deleteStatusFilter.innerHTML = '<option value="">All Status</option>';

                if (this.value) {
                    statusOptions[this.value].forEach(status => {
                        const option = document.createElement('option');
                        option.value = status;
                        option.textContent = status;
                        deleteStatusFilter.appendChild(option);
                    });
                }

                // Trigger filter update
                filterDeletePersonnel();
            });

            // Add event listeners for all filters
            document.getElementById('deleteNameFilter').addEventListener('input', filterDeletePersonnel);
            document.getElementById('deleteAcademicRankFilter').addEventListener('change', filterDeletePersonnel);
            document.getElementById('deleteGenderFilter').addEventListener('change', filterDeletePersonnel);
            document.getElementById('deleteStatusFilter').addEventListener('change', filterDeletePersonnel);

            // Campus filter removed for delete modal
            // const deleteCampusFilter = document.getElementById('deleteCampusFilter');
            // if (deleteCampusFilter) {
            //     deleteCampusFilter.addEventListener('change', filterDeletePersonnel);
            // }
        }

        // Variables for delete pagination
        let deleteCurrentPage = 1;
        const deleteRowsPerPage = 6;

        // Function to display personnel in the delete modal
        function displayDeletePersonnel(data) {
            console.log('Displaying delete personnel data...');
            console.log('Current user:', '<?php echo $_SESSION["username"]; ?>');
            console.log('Filtered data length:', data?.length);

            const tableBody = document.getElementById('deletePersonnelTableBody');
            const personnelCountDiv = document.getElementById('deletePersonnelCount');

            if (!tableBody || !personnelCountDiv) {
                console.error('Delete table body or count element not found');
                return;
            }

            // Clear existing table rows
            tableBody.innerHTML = '';

            if (!data || data.length === 0) {
                personnelCountDiv.textContent = 'No personnel found';
                const row = document.createElement('tr');
                const colSpan = '<?php echo $_SESSION["username"]; ?>' === 'Central' ? 6 : 5;
                row.innerHTML = `<td colspan="${colSpan}" class="text-center">No personnel found</td>`;
                tableBody.appendChild(row);

                // Clear pagination
                document.getElementById('deletePagination').innerHTML = '';
                return;
            }

            // Calculate pagination
            const totalPages = Math.ceil(data.length / deleteRowsPerPage);
            const startIndex = (deleteCurrentPage - 1) * deleteRowsPerPage;
            const endIndex = Math.min(startIndex + deleteRowsPerPage, data.length);

            // Update count text to show total personnel
            personnelCountDiv.textContent = `Total Personnel: ${data.length}`;

            // Display rows for current page
            for (let i = startIndex; i < endIndex; i++) {
                const person = data[i];
                const row = document.createElement('tr');
                row.className = 'cursor-pointer'; // Add pointer cursor

                // Handle different property names that might be in the data
                const name = person.name || '';
                const category = person.category || '';
                const status = person.status || '';
                const gender = person.gender || '';
                const academicRank = person.academicRank || person.academic_rank || '';
                const campus = person.campus || '';

                // Add campus column for Central user
                const campusColumn = '<?php echo isset($_SESSION["username"]) ? $_SESSION["username"] : ""; ?>' === 'Central' ?
                    `<td>${campus}</td>` :
                    '';

                row.innerHTML = `
                    <td>${name}</td>
                    <td>${category}</td>
                    <td>${status}</td>
                    <td>${gender}</td>
                    <td>${academicRank}</td>
                    ${campusColumn}
                `;

                // Add click event to the row
                row.addEventListener('click', function() {
                    confirmDeletePersonnel(person);
                });

                tableBody.appendChild(row);
            }

            // Update pagination
            updateDeletePagination(data.length);
        }

        // Function to update delete pagination
        function updateDeletePagination(totalItems) {
            const totalPages = Math.ceil(totalItems / deleteRowsPerPage);
            const pagination = document.getElementById('deletePagination');

            console.log('Updating delete pagination, total pages:', totalPages);
            pagination.innerHTML = '';

            if (totalPages <= 1) {
                console.log('No pagination needed for delete modal');
                return;
            }

            const paginationContainer = document.createElement('div');
            paginationContainer.className = 'pagination';

            // Previous button
            const prevButton = document.createElement('button');
            prevButton.className = `page-link ${deleteCurrentPage === 1 ? 'disabled' : ''}`;
            prevButton.innerHTML = '<i class="fas fa-chevron-left"></i>';
            prevButton.onclick = () => {
                if (deleteCurrentPage > 1) {
                    deleteCurrentPage--;
                    displayDeletePersonnel(window.deletePersonnelData);
                }
            };
            paginationContainer.appendChild(prevButton);

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                const pageButton = document.createElement('button');
                pageButton.className = `page-link ${deleteCurrentPage === i ? 'active' : ''}`;
                pageButton.textContent = i;
                pageButton.onclick = () => {
                    deleteCurrentPage = i;
                    displayDeletePersonnel(window.deletePersonnelData);
                };
                paginationContainer.appendChild(pageButton);
            }

            // Next button
            const nextButton = document.createElement('button');
            nextButton.className = `page-link ${deleteCurrentPage === totalPages ? 'disabled' : ''}`;
            nextButton.innerHTML = '<i class="fas fa-chevron-right"></i>';
            nextButton.onclick = () => {
                if (deleteCurrentPage < totalPages) {
                    deleteCurrentPage++;
                    displayDeletePersonnel(window.deletePersonnelData);
                }
            };
            paginationContainer.appendChild(nextButton);

            pagination.appendChild(paginationContainer);
        }

        // Function to filter personnel in the delete modal
        function filterDeletePersonnel() {
            const nameFilter = document.getElementById('deleteNameFilter').value.toLowerCase();
            const rankFilter = document.getElementById('deleteAcademicRankFilter').value;
            const categoryFilter = document.getElementById('deleteCategoryFilter').value;
            const statusFilter = document.getElementById('deleteStatusFilter').value;
            const genderFilter = document.getElementById('deleteGenderFilter').value;

            // For Central users, we'll only show Central data without filtering by campus
            // since the campus filter has been removed from the delete modal
            const campusFilter = '<?php echo $_SESSION["username"]; ?>';

            console.log('Applied delete filters:', {
                name: nameFilter,
                rank: rankFilter,
                category: categoryFilter,
                status: statusFilter,
                gender: genderFilter,
                campus: campusFilter
            });

            let filteredData = window.deletePersonnelData;

            // Apply name filter
            if (nameFilter) {
                filteredData = filteredData.filter(person =>
                    (person.name || '').toLowerCase().includes(nameFilter));
            }

            // Apply rank filter
            if (rankFilter) {
                filteredData = filteredData.filter(person =>
                    (person.academicRank || person.academic_rank || '') === rankFilter);
            }

            // Apply category filter
            if (categoryFilter) {
                filteredData = filteredData.filter(person =>
                    (person.category || '') === categoryFilter);
            }

            // Apply status filter
            if (statusFilter) {
                filteredData = filteredData.filter(person =>
                    (person.status || '') === statusFilter);
            }

            // Apply gender filter
            if (genderFilter) {
                filteredData = filteredData.filter(person => {
                    if (genderFilter === 'other') {
                        return (person.gender || '').toLowerCase() !== 'male' &&
                            (person.gender || '').toLowerCase() !== 'female';
                    }
                    return (person.gender || '').toLowerCase() === genderFilter.toLowerCase();
                });
            }

            // Filter by campus - only show Central's data for Central user
            filteredData = filteredData.filter(person =>
                (person.campus || '') === campusFilter);

            // Reset to first page when filtering
            deleteCurrentPage = 1;

            // Display filtered data
            displayDeletePersonnel(filteredData);
        }

        // Function to confirm and handle personnel deletion
        function confirmDeletePersonnel(person) {
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deletePersonnelModal'));
            deleteModal.hide();

            // Get the person's name, handling potential property name differences
            const personName = person.name || 'this personnel';
            const personId = person.id || person.personnel_id;

            // Check if we have a valid ID
            if (!personId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Cannot delete: Missing personnel ID'
                });
                deleteModal.show();
                return;
            }

            Swal.fire({
                    title: 'Delete Personnel',
                    html: `Are you sure you want to delete <strong>${personName}</strong>?<br>This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash-alt"></i> Delete',
                    cancelButtonText: 'Cancel',
                    backdrop: `rgba(0,0,0,0.7)`,
                    allowOutsideClick: false
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        // Show loading indicator
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait while we delete the personnel',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Send delete request
                        fetch('delete_personnel.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    id: personId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: data.message || 'Personnel deleted successfully',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        // Reset cached data to force refresh
                                        window.fullPersonnelData = null;

                                        // Reload personnel data for analytics
                                        loadPersonnelData();

                                        // Refresh view modal if it's open
                                        if (document.getElementById('viewPersonnelModal').classList.contains('show')) {
                                            // Fetch fresh data for view modal
                                            fetch('get_personnel.php')
                                                .then(response => response.json())
                                                .then(response => {
                                                    if (response.status === 'success' && Array.isArray(response.data)) {
                                                        const currentUser = '<?php echo $_SESSION["username"]; ?>';
                                                        window.fullPersonnelData = currentUser === 'Central' ?
                                                            response.data :
                                                            response.data.filter(person => person.campus === currentUser);

                                                        // Initialize filters and display data
                                                        initializeFilters(window.fullPersonnelData);
                                                        filterPersonnel();
                                                    }
                                                })
                                                .catch(error => console.error('Error refreshing view modal:', error));
                                        }

                                        // Refresh edit modal if it's open
                                        if (document.getElementById('editPersonnelModal').classList.contains('show')) {
                                            loadEditPersonnelData();
                                        }

                                        // Refresh delete modal if it's open
                                        if (document.getElementById('deletePersonnelModal').classList.contains('show')) {
                                            loadDeletePersonnelData();
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message || 'Failed to delete personnel'
                                    });
                                    // Show the delete modal again
                                    deleteModal.show();
                                }
                            })
                            .catch(error => {
                                console.error('Error deleting personnel:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to delete personnel: ' + error.message
                                });
                                // Show the delete modal again
                                deleteModal.show();
                            });
                    } else {
                        // User cancelled, show the delete modal again
                        deleteModal.show();
                    }
                });
        }

        function updateAnalytics(response) {
            // Initialize counters
            let totalCount = 0;
            let teachingCount = 0;
            let nonTeachingCount = 0;
            let maleCount = 0;
            let femaleCount = 0;
            let otherCount = 0;

            // Teaching status counters
            let teachingPermanent = 0;
            let teachingTemporary = 0;
            let teachingGuest = 0;

            // Non-teaching status counters
            let nonTeachingPermanent = 0;
            let nonTeachingJobOrder = 0;
            let nonTeachingPartTimer = 0;
            let nonTeachingCasual = 0;

            // Current user
            const currentUser = '<?php echo $_SESSION["username"]; ?>';

            // Count all categories for current user only
            if (!response.data) {
                console.error('No data property in response:', response);
                return;
            }

            response.data.forEach(person => {
                // Only count personnel from current user's campus
                if (person.campus === currentUser) {
                    totalCount++;

                    // Count by gender
                    if (person.gender.toLowerCase() === 'male') {
                        maleCount++;
                    } else if (person.gender.toLowerCase() === 'female') {
                        femaleCount++;
                    } else {
                        otherCount++;
                    }

                    // Count by category and status
                    if (person.category === 'Teaching') {
                        teachingCount++;
                        switch (person.status) {
                            case 'Permanent':
                                teachingPermanent++;
                                break;
                            case 'Temporary':
                                teachingTemporary++;
                                break;
                            case 'Guest Lecturer':
                                teachingGuest++;
                                break;
                        }
                    } else if (person.category === 'Non-teaching') {
                        nonTeachingCount++;
                        switch (person.status) {
                            case 'Permanent':
                                nonTeachingPermanent++;
                                break;
                            case 'Job Order':
                                nonTeachingJobOrder++;
                                break;
                            case 'Part-timer':
                                nonTeachingPartTimer++;
                                break;
                            case 'Casual':
                                nonTeachingCasual++;
                                break;
                        }
                    }
                }
            });

            // Helper function to safely update element text content
            function updateElementText(id, value) {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = value;
                } else {
                    console.warn(`Element with id '${id}' not found`);
                }
            }

            // Update all counters
            updateElementText('totalPersonnel', totalCount);
            updateElementText('teachingPersonnel', teachingCount);
            updateElementText('nonTeachingPersonnel', nonTeachingCount);
            updateElementText('malePersonnel', maleCount);
            updateElementText('femalePersonnel', femaleCount);
            updateElementText('otherPersonnel', otherCount);

            // Update teaching status breakdown
            updateElementText('teachingPermanent', teachingPermanent);
            updateElementText('teachingTemporary', teachingTemporary);
            updateElementText('teachingGuest', teachingGuest);

            // Update non-teaching status breakdown
            updateElementText('nonTeachingPermanent', nonTeachingPermanent);
            updateElementText('nonTeachingJobOrder', nonTeachingJobOrder);
            updateElementText('nonTeachingPartTimer', nonTeachingPartTimer);
            updateElementText('nonTeachingCasual', nonTeachingCasual);
        }

        // Call updateAnalytics whenever personnel data is loaded or updated
        function loadPersonnelData() {
            fetch('get_personnel.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    filteredData = data;
                    updateAnalytics(data); // Add this line to update analytics
                    filterPersonnel();
                })
                .catch(error => {
                    console.error('Error loading personnel data:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load personnel data',
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                });
        }

        // Analytics Carousel Functions
        let teachingCurrentSlide = 0;
        let nonTeachingCurrentSlide = 0;
        let genderCurrentSlide = 0;

        function showTeachingSlide(index) {
            const slides = document.querySelectorAll('#teachingSlides .analytics-slide');
            const dots = document.querySelectorAll('#teachingSlides + .analytics-nav + .analytics-dots .dot');

            slides[teachingCurrentSlide].classList.remove('active');
            dots[teachingCurrentSlide].classList.remove('active');

            teachingCurrentSlide = index;

            slides[teachingCurrentSlide].classList.add('active');
            dots[teachingCurrentSlide].classList.add('active');
        }

        function prevTeachingStat() {
            const slides = document.querySelectorAll('#teachingSlides .analytics-slide');
            let index = teachingCurrentSlide - 1;
            if (index < 0) index = slides.length - 1;
            showTeachingSlide(index);
        }

        function nextTeachingStat() {
            const slides = document.querySelectorAll('#teachingSlides .analytics-slide');
            let index = teachingCurrentSlide + 1;
            if (index >= slides.length) index = 0;
            showTeachingSlide(index);
        }

        function showNonTeachingSlide(index) {
            const slides = document.querySelectorAll('#nonTeachingSlides .analytics-slide');
            const dots = document.querySelectorAll('#nonTeachingSlides + .analytics-nav + .analytics-dots .dot');

            slides[nonTeachingCurrentSlide].classList.remove('active');
            dots[nonTeachingCurrentSlide].classList.remove('active');

            nonTeachingCurrentSlide = index;

            slides[nonTeachingCurrentSlide].classList.add('active');
            dots[nonTeachingCurrentSlide].classList.add('active');
        }

        function prevNonTeachingStat() {
            const slides = document.querySelectorAll('#nonTeachingSlides .analytics-slide');
            let index = nonTeachingCurrentSlide - 1;
            if (index < 0) index = slides.length - 1;
            showNonTeachingSlide(index);
        }

        function nextNonTeachingStat() {
            const slides = document.querySelectorAll('#nonTeachingSlides .analytics-slide');
            let index = nonTeachingCurrentSlide + 1;
            if (index >= slides.length) index = 0;
            showNonTeachingSlide(index);
        }

        function showGenderSlide(index) {
            const slides = document.querySelectorAll('#genderSlides .analytics-slide');
            const dots = document.querySelectorAll('#genderSlides + .analytics-nav + .analytics-dots .dot');

            slides[genderCurrentSlide].classList.remove('active');
            dots[genderCurrentSlide].classList.remove('active');

            genderCurrentSlide = index;

            slides[genderCurrentSlide].classList.add('active');
            dots[genderCurrentSlide].classList.add('active');
        }

        function prevGenderStat() {
            const slides = document.querySelectorAll('#genderSlides .analytics-slide');
            let index = genderCurrentSlide - 1;
            if (index < 0) index = slides.length - 1;
            showGenderSlide(index);
        }

        function nextGenderStat() {
            const slides = document.querySelectorAll('#genderSlides .analytics-slide');
            let index = genderCurrentSlide + 1;
            if (index >= slides.length) index = 0;
            showGenderSlide(index);
        }

        // Add touch swipe support for mobile
        function handleTouchStart(evt) {
            const firstTouch = evt.touches[0];
            window.touchStartX = firstTouch.clientX;
            window.touchStartY = firstTouch.clientY;
        }

        function handleTouchMove(evt, slideType) {
            if (!window.touchStartX || !window.touchStartY) return;

            const xDiff = window.touchStartX - evt.touches[0].clientX;
            const yDiff = window.touchStartY - evt.touches[0].clientY;

            if (Math.abs(xDiff) > Math.abs(yDiff)) {
                if (xDiff > 0) {
                    // Swiped left
                    if (slideType === 'teaching') nextTeachingStat();
                    else if (slideType === 'nonTeaching') nextNonTeachingStat();
                    else if (slideType === 'gender') nextGenderStat();
                } else {
                    // Swiped right
                    if (slideType === 'teaching') prevTeachingStat();
                    else if (slideType === 'nonTeaching') prevNonTeachingStat();
                    else if (slideType === 'gender') prevGenderStat();
                }
            }

            window.touchStartX = null;
            window.touchStartY = null;
        }

        // Initialize touch events
        document.addEventListener('DOMContentLoaded', function() {
            const teachingSlides = document.getElementById('teachingSlides');
            const nonTeachingSlides = document.getElementById('nonTeachingSlides');
            const genderSlides = document.getElementById('genderSlides');

            teachingSlides.addEventListener('touchstart', handleTouchStart, false);
            teachingSlides.addEventListener('touchmove', (e) => handleTouchMove(e, 'teaching'), false);

            nonTeachingSlides.addEventListener('touchstart', handleTouchStart, false);
            nonTeachingSlides.addEventListener('touchmove', (e) => handleTouchMove(e, 'nonTeaching'), false);

            genderSlides.addEventListener('touchstart', handleTouchStart, false);
            genderSlides.addEventListener('touchmove', (e) => handleTouchMove(e, 'gender'), false);
        });

        // Call loadPersonnelData when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadPersonnelData();
        });
    </script>
</body>

</html>