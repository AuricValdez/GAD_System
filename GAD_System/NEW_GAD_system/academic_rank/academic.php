<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Rank - GAD System</title>
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

        .analytics-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            height: 100%;
        }

        .analytics-card:hover {
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
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

        /* Table container */
        .table-container {
            min-height: 219px;
            margin-bottom: 0.5rem;
        }

        .table {
            font-size: 0.9rem;
        }

        .table > tbody > tr {
            height: 40px;
        }

        .table > tbody > tr > td {
            padding-top: 0.4rem;
            padding-bottom: 0.4rem;
        }

        /* Pagination */
        .pagination {
            margin: 0;
            justify-content: flex-end;
        }

        .pagination-container {
            margin-top: 0.5rem;
            margin-bottom: 0;
            padding: 0;
        }

        .page-link {
            color: var(--accent-color);
            background-color: transparent;
            border-color: var(--accent-color);
        }

        .page-link:hover {
            color: #fff;
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .page-item.active .page-link {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: #fff;
        }

        .page-item.disabled .page-link {
            color: #6c757d;
            background-color: transparent;
            border-color: #dee2e6;
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

        /* Card body reduced padding */
        .card-body {
            padding: 1rem;
            padding-bottom: 0.5rem;
        }
        
        /* Table container styles */
        .table-container {
            overflow-x: auto;
            margin-bottom: 1rem;
        }

        /* Specific height for academic ranks table */
        #academicRanksTable {
            min-height: 200px; /* Height for approximately 5 rows */
        }

        /* Modal and SweetAlert backdrop blur */
        .modal-backdrop {
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            background-color: rgba(33, 33, 33, 0.85) !important;
        }
        
        /* Make sure modal appears above backdrop with proper blur */
        .modal {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.98) !important;
            border: none !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
        }

        /* Dark mode adjustments */
        [data-bs-theme="dark"] .modal-content {
            background: rgba(33, 37, 41, 0.98) !important;
        }

        [data-bs-theme="dark"] .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.85) !important;
        }

        /* SweetAlert container */
        .swal2-container {
            backdrop-filter: blur(5px) !important;
        }
        
        .swal2-popup {
            border-radius: 8px !important;
        }
        
        /* Pagination container */
        .pagination-container {
            margin-top: 0.75rem;
            margin-bottom: 1rem;
            padding-top: 0;
            border-top: none;
        }
        
        /* Table wrapper */
        .table-wrapper {
            margin-bottom: 0;
        }
        
        /* Dark mode specific styles */
        [data-bs-theme="dark"] .modal-content {
            background-color: var(--dark-sidebar);
            border-color: var(--dark-border);
        }

        [data-bs-theme="dark"] .modal-header {
            border-color: var(--dark-border);
        }

        [data-bs-theme="dark"] #academicRank:readonly {
            background-color: rgba(108, 117, 125, 0.2) !important;
            border: 1px dashed #495057 !important;
            color: #adb5bd !important;
            cursor: not-allowed !important;
        }

        [data-bs-theme="dark"] .diagonal-pattern {
            background-color: rgba(108, 117, 125, 0.2) !important;
            border: 1px dashed #495057 !important;
            color: #adb5bd !important;
            cursor: not-allowed !important;
        }
    </style>
    <style>
        /* Modal styles */
        .edit-modal-title {
            color: #6a1b9a !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            margin: 0 auto !important;
            font-size: 1.1rem !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1) !important;
        }

        /* Dark mode specific styles */
        [data-bs-theme="dark"] .edit-modal-title {
            color: var(--accent-color) !important;
            text-shadow: 1px 1px 2px rgba(156, 39, 176, 0.1) !important;
        }

        /* Ensure modal header is properly centered */
        .modal-header {
            justify-content: center !important;
            position: relative !important;
            border-bottom: 1px solid var(--dark-border) !important;
            padding: 1rem 1.5rem !important;
        }

        .modal-header .btn-close {
            position: absolute !important;
            right: 1rem !important;
            padding: 0.5rem !important;
        }

        .modal-body {
            padding: 1.5rem !important;
        }

        /* Table styles */
        .table-container {
            min-height: 300px !important;
            max-height: 300px !important;
            overflow-y: auto !important;
        }

        .table tr {
            cursor: pointer !important;
            transition: background-color 0.2s ease !important;
        }

        .table tr:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        }

        /* Dark mode specific styles */
        [data-bs-theme="dark"] .modal-content {
            background-color: var(--dark-sidebar) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .modal-header {
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .edit-modal-title {
            color: #ff4d4d !important;
            text-shadow: 1px 1px 2px rgba(255, 77, 77, 0.1) !important;
        }

        [data-bs-theme="dark"] #academicRank:disabled {
            background-color: rgba(108, 117, 125, 0.2) !important;
            border: 1px dashed #495057 !important;
            color: #adb5bd !important;
            cursor: not-allowed !important;
        }

        /* Button states */
        #editBtn.editing {
            background: rgba(220, 53, 69, 0.1) !important;
            color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        #editBtn.editing:hover {
            background: #dc3545 !important;
            color: white !important;
        }

        #addBtn.updating {
            background: rgba(25, 135, 84, 0.1) !important;
            color: #198754 !important;
        }

        #addBtn.updating:hover {
            background: #198754 !important;
            color: white !important;
        }

        #deleteBtn.disabled {
            background: rgba(108, 117, 125, 0.1) !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        
    </style>
    <style>
        /* Add these styles to increase blur for modals */
        .modal-backdrop {
            backdrop-filter: blur(3px);
            background-color: rgba(0, 0, 0, 0.3);
        }

        .modal-backdrop.show {
            opacity: 1;
        }

        .fade-out {
            opacity: 0;
        }

        /* Modal title styles for both light and dark mode */
.modal-header .modal-title {
    color: #6a1b9a !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    margin: 0 auto !important;
    font-size: 1.1rem !important;
}

/* Dark mode specific styles */
[data-bs-theme="dark"] .modal-header .modal-title {
    color: var(--accent-color) !important;
}

/* This ensures both edit and delete modal titles are styled */
[data-bs-theme="dark"] #editModalLabel,
[data-bs-theme="dark"] #deleteModalLabel {
    color: var(--accent-color) !important;
}

/* SweetAlert Styles */
/* Default style for delete confirmation (red) */
.swal2-popup[class*="delete"] .swal2-confirm {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
}

.swal2-popup[class*="delete"] .swal2-confirm:hover {
    background-color: #bb2d3b !important;
    border-color: #b02a37 !important;
}

/* Style for logout confirmation (gray) */
.swal2-popup:not([class*="delete"]) .swal2-confirm {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
}

.swal2-popup:not([class*="delete"]) .swal2-confirm:hover {
    background-color: #5c636a !important;
    border-color: #565e64 !important;
}
    </style>
    <style>
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

        /* Style for checkbox and radio when focused/checked */
        .form-check-input:checked {
            background-color: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
        }

        /* Style for buttons with accent color */
        .btn-primary {
            background-color: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background-color: var(--accent-hover) !important;
            border-color: var(--accent-hover) !important;
        }

        .btn-outline-primary {
            color: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus,
        .btn-outline-primary:active {
            background-color: var(--accent-color) !important;
            color: white !important;
        }

        /* Style for dropdown items */
        .dropdown-item:active,
        .dropdown-item.active {
            background-color: var(--accent-color) !important;
            color: white !important;
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
                        <li><a class="dropdown-item" href="#">Academic Rank</a></li>
                        <li><a class="dropdown-item" href="../personnel_list/personnel_list.php">Personnel List</a></li>
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
            <i class="fas fa-graduation-cap"></i>
            <h2>Academic Rank Management</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Academic Forms</h5>
            </div>
            <div class="card-body">
                <form id="academicForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="academicRank" class="form-label">Academic Rank</label>
                            <input type="text" class="form-control" id="academicRank" name="academicRank" required>
                            <div class="invalid-feedback">Please enter an academic rank</div>
                        </div>
                        <div class="form-group">
                            <label for="salaryGrade" class="form-label">Salary Grade</label>
                            <input type="number" class="form-control" id="salaryGrade" name="salaryGrade" min="1" 
                                oninput="this.value = this.value.replace(/^0+/, '').replace(/[^0-9]/g, '')" required>
                            <div class="invalid-feedback">Please enter a valid salary grade (greater than 0)</div>
                        </div>
                        <div class="form-group">
                            <label for="monthlySalary" class="form-label">Monthly Salary</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="monthlySalary" name="monthlySalary" min="1" step="0.01"
                                    oninput="this.value = this.value.replace(/^0+/, '').replace(/[^0-9.]/g, '')" required>
                            </div>
                            <div class="invalid-feedback">Please enter a valid monthly salary (greater than 0)</div>
                        </div>
                        <div class="form-group">
                            <label for="hourlyRate" class="form-label">Hourly Rate</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="text" class="form-control diagonal-pattern" id="hourlyRate" name="hourlyRate" readonly 
                                    style="background-color: #e9ecef; cursor: not-allowed;">
                            </div>
                            <div class="form-text">
                                <i class="fas fa-calculator"></i> Monthly Salary ÷ 176
                            </div>
                        </div>
                    </div>

                    <div class="col-12 text-end mt-4">
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
                </form>
            </div>
        </div>

        <!-- Academic Ranks Table Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Academic Ranks List</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search academic rank...">
                    </div>
                    <div class="col-md-6">
                        <select id="salaryGradeFilter" class="form-select">
                            <option value="">All Salary Grades</option>
                        </select>
                    </div>
                </div>
                <div class="table-wrapper">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="academicRanksTable">
                            <thead>
                                <tr>
                                    <th>Academic Rank</th>
                                    <th>Salary Grade</th>
                                    <th>Monthly Salary</th>
                                    <th>Hourly Rate</th>
                                </tr>
                            </thead>
                            <tbody id="academicRanksTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="pagination-container">
                    <nav>
                        <ul class="pagination justify-content-center" id="pagination">
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center w-100 edit-modal-title" id="editModalLabel">Select Academic Rank to Edit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editSearchInput" class="form-label">Search Academic Rank</label>
                                <input type="text" class="form-control" id="editSearchInput" placeholder="Search...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editSalaryGradeFilter" class="form-label">Filter by Salary Grade</label>
                                <select class="form-select" id="editSalaryGradeFilter">
                                    <option value="">All</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Academic Rank</th>
                                    <th>Salary Grade</th>
                                    <th>Monthly Salary</th>
                                    <th>Hourly Rate</th>
                                </tr>
                            </thead>
                            <tbody id="editTableBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        <nav aria-label="Edit table navigation">
                            <ul class="pagination" id="editPagination">
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center w-100 edit-modal-title" id="deleteModalLabel">Select Academic Rank to Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="deleteFilterRank" class="form-label">Search Academic Rank</label>
                                <input type="text" class="form-control" id="deleteFilterRank" placeholder="Search...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="deleteFilterGrade" class="form-label">Filter by Salary Grade</label>
                                <select class="form-select" id="deleteFilterGrade">
                                    <option value="">All</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Academic Rank</th>
                                    <th>Salary Grade</th>
                                    <th>Monthly Salary</th>
                                    <th>Hourly Rate</th>
                                </tr>
                            </thead>
                            <tbody id="deleteTableBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        <nav aria-label="Delete table navigation">
                            <ul class="pagination" id="deletePagination">
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center w-100" id="deleteConfirmModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this academic rank?</p>
                    <div class="delete-details">
                        <p><strong>Academic Rank:</strong> <span id="deleteRankName"></span></p>
                        <p><strong>Salary Grade:</strong> <span id="deleteSalaryGrade"></span></p>
                        <p><strong>Monthly Salary:</strong> <span id="deleteMonthlySalary"></span></p>
                        <p><strong>Hourly Rate:</strong> <span id="deleteHourlyRate"></span></p>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables for edit modal pagination
        let editModalData = [];
        let editModalCurrentPage = 1;
        const editModalRowsPerPage = 5;

        // Make loadEditModalData available globally
        window.loadEditModalData = async function(page = 1) {
            try {
                if (page === 1 || editModalData.length === 0) {
                    console.log('Fetching academic ranks...');
                    const response = await fetch('get_academic_ranks.php');
                    console.log('Response status:', response.status);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    console.log('Received data:', data);
                    
                    if (!Array.isArray(data)) {
                        throw new Error('Invalid data format received');
                    }
                    editModalData = data;

                    // Populate salary grade filter
                    const uniqueGrades = [...new Set(data.map(item => item.salary_grade))].sort((a, b) => a - b);
                    const filterSelect = document.querySelector('#editSalaryGradeFilter');
                    filterSelect.innerHTML = '<option value="">All</option>';
                    uniqueGrades.forEach(grade => {
                        filterSelect.innerHTML += `<option value="${grade}">SG ${grade}</option>`;
                    });
                }

                // Calculate pagination
                const totalPages = Math.ceil(editModalData.length / editModalRowsPerPage);
                editModalCurrentPage = Math.min(Math.max(1, page), totalPages);
                const startIndex = (editModalCurrentPage - 1) * editModalRowsPerPage;
                const endIndex = startIndex + editModalRowsPerPage;
                const pageData = editModalData.slice(startIndex, endIndex);
                
                // Populate edit modal table
                const tbody = document.querySelector('#editTableBody');
                tbody.innerHTML = '';
                
                if (editModalData.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No academic ranks found</td></tr>';
                    return;
                }
                
                pageData.forEach(rank => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', rank.id);
                    row.innerHTML = `
                        <td>${rank.academic_rank || ''}</td>
                        <td>${rank.salary_grade || ''}</td>
                        <td>${rank.monthly_salary ? '₱' + parseFloat(rank.monthly_salary).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : ''}</td>
                        <td>${rank.hourly_rate ? '₱' + parseFloat(rank.hourly_rate).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : ''}</td>
                    `;
                    tbody.appendChild(row);
                });

                // Update pagination controls
                const pagination = document.querySelector('#editPagination');
                if (totalPages > 1) {
                    let paginationHtml = '';
                    
                    // Previous button
                    paginationHtml += `
                        <li class="page-item ${editModalCurrentPage === 1 ? 'disabled' : ''}">
                            <button class="page-link" onclick="loadEditModalData(${editModalCurrentPage - 1})" ${editModalCurrentPage === 1 ? 'disabled' : ''}>Previous</button>
                        </li>
                    `;
                    
                    // Page numbers
                    for (let i = 1; i <= totalPages; i++) {
                        paginationHtml += `
                            <li class="page-item ${editModalCurrentPage === i ? 'active' : ''}">
                                <button class="page-link" onclick="loadEditModalData(${i})">${i}</button>
                            </li>
                        `;
                    }
                    
                    // Next button
                    paginationHtml += `
                        <li class="page-item ${editModalCurrentPage === totalPages ? 'disabled' : ''}">
                            <button class="page-link" onclick="loadEditModalData(${editModalCurrentPage + 1})" ${editModalCurrentPage === totalPages ? 'disabled' : ''}>Next</button>
                        </li>
                    `;
                    
                    pagination.innerHTML = paginationHtml;
                    pagination.style.display = '';
                } else {
                    pagination.style.display = 'none';
                }
                
                console.log('Data loaded successfully');
            } catch (error) {
                console.error('Error loading data:', error);
                const tbody = document.querySelector('#editTableBody');
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load academic ranks. Please try again.',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        };

        $(document).ready(function() {
            let isEditing = false;
            let editData = [];
            let isIntentionalClose = false; // Add flag to track intentional modal close
            const ROWS_PER_PAGE = 5;
            let currentPage = 1;
            window.academicRanks = [];
            window.filteredRanks = [];
            
            const form = document.getElementById('academicForm');
            const editBtn = document.getElementById('editBtn');
            const deleteBtn = document.getElementById('deleteBtn');
            const addBtn = document.getElementById('addBtn');
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            
            // Edit button click handler
            editBtn.addEventListener('click', function() {
                if (isEditing) {
                    window.cancelEdit();
                } else {
                    loadEditModalData(); // Load data before showing modal
                    editModal.show();
                }
            });

            // Search and filter for edit modal
            document.querySelector('#editSearchInput').addEventListener('input', function() {
                filterEditModalTable();
            });

            document.querySelector('#editSalaryGradeFilter').addEventListener('change', function() {
                filterEditModalTable();
            });

            function filterEditModalTable() {
                const searchTerm = document.querySelector('#editSearchInput').value.toLowerCase();
                const gradeFilter = document.querySelector('#editSalaryGradeFilter').value;
                const tbody = document.querySelector('#editTableBody');
                const pagination = document.querySelector('#editPagination');

                // If search and filter are empty, reload the original data
                if (!searchTerm && !gradeFilter) {
                    loadEditModalData(editModalCurrentPage);
                    return;
                }

                // Filter the data
                const filteredData = editModalData.filter(rank => {
                    const matchesSearch = rank.academic_rank.toLowerCase().includes(searchTerm);
                    const matchesGrade = !gradeFilter || rank.salary_grade.toString() === gradeFilter;
                    return matchesSearch && matchesGrade;
                });

                // Calculate pagination for filtered data
                editModalCurrentPage = 1; // Reset to first page when filtering
                const startIndex = 0;
                const endIndex = startIndex + editModalRowsPerPage;
                const pageData = filteredData.slice(startIndex, endIndex);

                // Update table content
                tbody.innerHTML = '';
                if (filteredData.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No academic ranks found</td></tr>';
                    if (pagination) {
                        pagination.style.display = 'none';
                    }
                } else {
                    pageData.forEach(rank => {
                        const row = document.createElement('tr');
                        row.setAttribute('data-id', rank.id);
                        row.innerHTML = `
                            <td>${rank.academic_rank || ''}</td>
                            <td>${rank.salary_grade || ''}</td>
                            <td>${rank.monthly_salary ? '₱' + parseFloat(rank.monthly_salary).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : ''}</td>
                            <td>${rank.hourly_rate ? '₱' + parseFloat(rank.hourly_rate).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : ''}</td>
                        `;
                        tbody.appendChild(row);
                    });

                    // Update pagination only if we have more than one page
                    if (pagination) {
                        const totalPages = Math.ceil(filteredData.length / editModalRowsPerPage);
                        if (totalPages > 1) {
                            let paginationHtml = '';
                            
                            // Previous button
                            paginationHtml += `
                                <li class="page-item ${editModalCurrentPage === 1 ? 'disabled' : ''}">
                                    <button class="page-link" onclick="loadEditModalData(${editModalCurrentPage - 1})" ${editModalCurrentPage === 1 ? 'disabled' : ''}>Previous</button>
                                </li>
                            `;
                            
                            // Page numbers
                            for (let i = 1; i <= totalPages; i++) {
                                paginationHtml += `
                                    <li class="page-item ${editModalCurrentPage === i ? 'active' : ''}">
                                        <button class="page-link" onclick="loadEditModalData(${i})">${i}</button>
                                    </li>
                                `;
                            }
                            
                            // Next button
                            paginationHtml += `
                                <li class="page-item ${editModalCurrentPage === totalPages ? 'disabled' : ''}">
                                    <button class="page-link" onclick="loadEditModalData(${editModalCurrentPage + 1})" ${editModalCurrentPage === totalPages ? 'disabled' : ''}>Next</button>
                                </li>
                            `;
                            
                            pagination.innerHTML = paginationHtml;
                            pagination.style.display = '';
                        } else {
                            pagination.style.display = 'none';
                        }
                    }
                }
            }

            // Handle modal close
            $('#editModal').on('hidden.bs.modal', function () {
                if (isEditing && !isIntentionalClose) {
                    window.cancelEdit();
                }
                isIntentionalClose = false; // Reset the flag
                // Clear search and filter
                document.querySelector('#editSearchInput').value = '';
                document.querySelector('#editSalaryGradeFilter').value = '';
                // Reset selected rows
                const selectedRows = document.querySelectorAll('tr.selected');
                selectedRows.forEach(row => row.classList.remove('selected'));
            });

            // Add click event listener to modal table rows
            $(document).on('click', '#editModal .table tbody tr', function() {
                const rows = document.querySelectorAll('#editModal .table tbody tr');
                rows.forEach(row => row.classList.remove('selected'));
                this.classList.add('selected');
                
                const cells = this.getElementsByTagName('td');
                const rankId = this.getAttribute('data-id'); // Get the ID from the row
                editData = [
                    rankId, // Store the ID
                    cells[0].textContent.trim(),
                    cells[1].textContent.trim(),
                    cells[2].textContent.trim()
                ];
                
                // Get monthly salary and remove currency symbol and commas
                const monthlySalary = parseFloat(cells[2].textContent.replace(/[₱,]/g, ''));
                const hourlyRate = (monthlySalary / 176).toFixed(2);
                
                // Add hidden input for ID if it doesn't exist
                let idInput = form.querySelector('input[name="editId"]');
                if (!idInput) {
                    idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'editId';
                    form.appendChild(idInput);
                }
                idInput.value = rankId;
                
                form.elements['academicRank'].value = cells[0].textContent.trim();
                form.elements['salaryGrade'].value = cells[1].textContent.trim();
                form.elements['monthlySalary'].value = monthlySalary;
                form.elements['hourlyRate'].value = hourlyRate;
                
                // Make academic rank field readonly in edit mode
                form.elements['academicRank'].readOnly = true;
                form.elements['academicRank'].classList.add('diagonal-pattern');
                form.elements['academicRank'].style.backgroundColor = '#e9ecef';
                form.elements['academicRank'].style.cursor = 'not-allowed';
                
                // Enter edit mode
                editBtn.classList.remove('btn-primary');
                editBtn.classList.add('editing');
                editBtn.innerHTML = '<i class="fas fa-times"></i>';
                deleteBtn.classList.add('disabled');
                addBtn.classList.add('btn-update');
                addBtn.innerHTML = '<i class="fas fa-save"></i>';
                isEditing = true;
                
                // Set flag before closing modal
                isIntentionalClose = true;
                // Close the modal
                editModal.hide();
            });

            // Make cancelEdit function available globally
            window.cancelEdit = function() {
                form.reset();
                // Remove the edit ID when canceling
                const idInput = form.querySelector('input[name="editId"]');
                if (idInput) {
                    idInput.remove();
                }
                
                // Reset academic rank field to editable
                form.elements['academicRank'].readOnly = false;
                form.elements['academicRank'].classList.remove('diagonal-pattern');
                form.elements['academicRank'].style.backgroundColor = '';
                form.elements['academicRank'].style.cursor = '';
                
                editBtn.classList.remove('editing');
                editBtn.classList.add('btn-primary');
                editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                deleteBtn.classList.remove('disabled');
                addBtn.classList.remove('btn-update');
                addBtn.innerHTML = '<i class="fas fa-plus"></i>';
                isEditing = false;
                editData = [];

                const selectedRows = document.querySelectorAll('tr.selected');
                selectedRows.forEach(row => row.classList.remove('selected'));
            }

            // Form submission handler
            $('#academicForm').on('submit', async function(e) {
                e.preventDefault();
                
                const rankName = $('#academicRank').val().trim();
                const formData = new FormData(this);
                
                try {
                    // Check for duplicates only when adding new record
                    if (!formData.get('editId')) {
                        const exists = await checkRankExists(rankName);
                        if (exists) {
                            await Swal.fire({
                                icon: 'error',
                                title: 'Duplicate Academic Rank',
                                text: 'This academic rank already exists in the database.',
                                confirmButtonColor: '#dc3545'
                            });
                            return;
                        }
                    }
                    
                    // Determine which URL to use based on whether we're editing or adding
                    const url = formData.get('editId') ? 'save_academic_rank.php' : 'add_academic_rank.php';
                    
                    // If no duplicate or editing, proceed with form submission
                    $.ajax({
                        url: url,  // Use the determined URL
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            // Add this line to debug
                            console.log('Response:', response);
                            
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    loadAcademicRanks();
                                    cancelEdit(); // Exit edit mode after successful save
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Failed to save academic rank'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to save academic rank. Please try again.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                } catch (error) {
                    console.error('Error checking rank:', error);
                }
            });

            // Update button styles for edit mode
            const editBtnStyles = `
                #editBtn.editing {
                    background: rgba(220, 53, 69, 0.1) !important;
                    color: #dc3545 !important;
                    border-color: #dc3545 !important;
                }
                #editBtn.editing:hover {
                    background: #dc3545 !important;
                    color: white !important;
                }
                
                /* Override accent color for edit button */
                #editBtn.btn-primary {
                    background: rgba(255, 193, 7, 0.1) !important;
                    color: #ffc107 !important;
                    border-color: transparent !important;
                }
                
                #editBtn.btn-primary:hover {
                    background: #ffc107 !important;
                    color: white !important;
                    border-color: #ffc107 !important;
                }
            `;
            
            // Add styles to head
            const styleSheet = document.createElement("style");
            styleSheet.innerText = editBtnStyles;
            document.head.appendChild(styleSheet);
            
            // Rest of your existing code...
            
            // Pagination variables
            const itemsPerPage = 4;
            
            // Search and filter elements
            const searchInput = document.getElementById('searchInput');
            const salaryGradeFilter = document.getElementById('salaryGradeFilter');

            // Event listeners for search and filter
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const gradeFilter = salaryGradeFilter.value;
                
                window.filteredRanks = window.academicRanks.filter(rank => {
                    const matchesSearch = rank.academic_rank.toLowerCase().includes(searchTerm);
                    const matchesGrade = !gradeFilter || rank.salary_grade.toString() === gradeFilter;
                    return matchesSearch && matchesGrade;
                });
                
                currentPage = 1;
                updateTable();
            });

            salaryGradeFilter.addEventListener('change', function() {
                const searchTerm = searchInput.value.toLowerCase();
                const gradeFilter = this.value;
                
                window.filteredRanks = window.academicRanks.filter(rank => {
                    const matchesSearch = rank.academic_rank.toLowerCase().includes(searchTerm);
                    const matchesGrade = !gradeFilter || rank.salary_grade.toString() === gradeFilter;
                    return matchesSearch && matchesGrade;
                });
                
                currentPage = 1;
                updateTable();
            });

            // Filter and update table
            window.filterTable = function() {
                const searchTerm = searchInput.value.toLowerCase();
                const gradeFilter = salaryGradeFilter.value;
                
                window.filteredRanks = window.academicRanks.filter(rank => {
                    const matchesSearch = rank.academic_rank.toLowerCase().includes(searchTerm);
                    const matchesGrade = !gradeFilter || rank.salary_grade.toString() === gradeFilter;
                    return matchesSearch && matchesGrade;
                });
                
                currentPage = 1;
                updateTable();
            }

            window.updatePagination = function() {
                const totalPages = Math.ceil(window.filteredRanks.length / itemsPerPage);
                currentPage = Math.min(currentPage, totalPages);
                const pagination = document.getElementById('pagination');
                let paginationHtml = '';
                
                // Previous button
                paginationHtml += `
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="event.preventDefault(); changePage(${currentPage - 1})">Previous</a>
                    </li>
                `;
                
                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    paginationHtml += `
                        <li class="page-item ${currentPage === i ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="event.preventDefault(); changePage(${i})">${i}</a>
                        </li>
                    `;
                }
                
                // Next button
                paginationHtml += `
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="event.preventDefault(); changePage(${currentPage + 1})">Next</a>
                    </li>
                `;
                
                pagination.innerHTML = paginationHtml;
            };

            window.changePage = function(page) {
                if (page >= 1 && page <= Math.ceil(window.filteredRanks.length / itemsPerPage)) {
                    currentPage = page;
                    updateTable();
                }
            };

            function updateTable() {
                const tableBody = document.getElementById('academicRanksTableBody');
                const start = (currentPage - 1) * itemsPerPage;
                const end = start + itemsPerPage;
                const pageData = window.filteredRanks.slice(start, end);
                
                tableBody.innerHTML = '';
                
                if (window.filteredRanks.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No academic ranks found</td></tr>';
                    document.getElementById('pagination').innerHTML = ''; // Clear pagination if no results
                    return;
                }
                
                pageData.forEach(rank => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${rank.academic_rank}</td>
                        <td>${rank.salary_grade}</td>
                        <td>₱${parseFloat(rank.monthly_salary).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td>₱${parseFloat(rank.hourly_rate).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    `;
                    tableBody.appendChild(row);
                });
                
                window.updatePagination();
            }

            // Load and display academic ranks in table
            window.loadAcademicRanks = async function() {
                try {
                    const response = await fetch('get_academic_ranks.php', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Cache-Control': 'no-cache'
                        }
                    });
                    
                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.error || 'Failed to fetch academic ranks');
                    }
                    
                    const data = await response.json();
                    if (!Array.isArray(data)) {
                        throw new Error('Invalid response format');
                    }
                    window.academicRanks = data;
                    window.filteredRanks = [...window.academicRanks];
                    
                    // Update filter options
                    const uniqueGrades = [...new Set(window.academicRanks.map(rank => rank.salary_grade))].sort((a, b) => a - b);
                    window.populateSalaryGradeFilters(uniqueGrades);
                    
                    updateTable();
                } catch (error) {
                    console.error('Error loading academic ranks:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to load academic ranks',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            }

            // Calculate hourly rate when monthly salary changes
            document.getElementById('monthlySalary').addEventListener('input', function() {
                const monthlySalary = parseFloat(this.value) || 0;
                const hourlyRate = (monthlySalary / 176).toFixed(2);
                document.getElementById('hourlyRate').value = '₱' + hourlyRate.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            });

            function formatCurrency(amount) {
                return new Intl.NumberFormat('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(amount);
            }

            // Initial load
            loadAcademicRanks();
        });

        // Global variables for delete modal pagination
        let deleteModalData = [];
        let deleteModalCurrentPage = 1;
        const deleteModalRowsPerPage = 5;

        // Filter function for delete modal
        function filterDeleteModalData() {
            const rankFilter = document.getElementById('deleteFilterRank').value.toLowerCase();
            const gradeFilter = document.getElementById('deleteFilterGrade').value;
            
            return deleteModalData.filter(rank => {
                const matchRank = !rankFilter || (rank.academic_rank && rank.academic_rank.toLowerCase().includes(rankFilter));
                const matchGrade = !gradeFilter || (rank.salary_grade && rank.salary_grade.toString() === gradeFilter);
                return matchRank && matchGrade;
            });
        }

        // Load delete modal data with filters
        window.loadDeleteModalData = async function(page = 1, preserveFilters = true) {
            try {
                if (page === 1 || deleteModalData.length === 0) {
                    const response = await fetch('get_academic_ranks.php');
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    if (!Array.isArray(data)) {
                        throw new Error('Invalid data format received');
                    }
                    deleteModalData = data;

                    // Only populate salary grade filter if we're not preserving filters
                    if (!preserveFilters) {
                        // Populate salary grade filter
                        const uniqueGrades = [...new Set(data.map(item => item.salary_grade))].sort((a, b) => a - b);
                        const filterSelect = document.querySelector('#deleteFilterGrade');
                        filterSelect.innerHTML = '<option value="">All</option>';
                        uniqueGrades.forEach(grade => {
                            filterSelect.innerHTML += `<option value="${grade}">SG ${grade}</option>`;
                        });
                    }
                }

                const filteredData = filterDeleteModalData();
                
                // Calculate pagination
                const totalPages = Math.ceil(filteredData.length / deleteModalRowsPerPage);
                deleteModalCurrentPage = Math.min(Math.max(1, page), totalPages || 1);
                const startIndex = (deleteModalCurrentPage - 1) * deleteModalRowsPerPage;
                const endIndex = startIndex + deleteModalRowsPerPage;
                const pageData = filteredData.slice(startIndex, endIndex);
                
                // Populate delete modal table
                const tbody = document.querySelector('#deleteTableBody');
                tbody.innerHTML = '';
                
                if (filteredData.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No academic ranks found</td></tr>';
                    document.querySelector('#deletePagination').style.display = 'none';
                    return;
                }
                
                pageData.forEach(rank => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', rank.id);
                    row.innerHTML = `
                        <td>${rank.academic_rank || ''}</td>
                        <td>${rank.salary_grade ? 'SG ' + rank.salary_grade : ''}</td>
                        <td>${rank.monthly_salary ? '₱' + parseFloat(rank.monthly_salary).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : ''}</td>
                        <td>${rank.hourly_rate ? '₱' + parseFloat(rank.hourly_rate).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : ''}</td>
                    `;
                    tbody.appendChild(row);
                });

                // Update pagination
                const pagination = document.querySelector('#deletePagination');
                if (totalPages > 1) {
                    let paginationHtml = '';
                    
                    // Previous button
                    paginationHtml += `
                        <li class="page-item ${deleteModalCurrentPage === 1 ? 'disabled' : ''}">
                            <button class="page-link" onclick="loadDeleteModalData(${deleteModalCurrentPage - 1}, true)" ${deleteModalCurrentPage === 1 ? 'disabled' : ''}>Previous</button>
                        </li>
                    `;
                    
                    // Page numbers
                    for (let i = 1; i <= totalPages; i++) {
                        paginationHtml += `
                            <li class="page-item ${deleteModalCurrentPage === i ? 'active' : ''}">
                                <button class="page-link" onclick="loadDeleteModalData(${i}, true)">${i}</button>
                            </li>
                        `;
                    }
                    
                    // Next button
                    paginationHtml += `
                        <li class="page-item ${deleteModalCurrentPage === totalPages ? 'disabled' : ''}">
                            <button class="page-link" onclick="loadDeleteModalData(${deleteModalCurrentPage + 1}, true)" ${deleteModalCurrentPage === totalPages ? 'disabled' : ''}>Next</button>
                        </li>
                    `;
                    
                    pagination.innerHTML = paginationHtml;
                    pagination.style.display = '';
                } else {
                    pagination.style.display = 'none';
                }
            } catch (error) {
                console.error('Error loading delete modal data:', error);
                const tbody = document.querySelector('#deleteTableBody');
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
            }
        };

        // Handle delete modal close
        $('#deleteModal').on('hidden.bs.modal', function () {
            // Clear search and filter
            document.querySelector('#deleteFilterRank').value = '';
            document.querySelector('#deleteFilterGrade').value = '';
            // Reset selected rows
            const selectedRows = document.querySelectorAll('#deleteTableBody tr.selected');
            selectedRows.forEach(row => row.classList.remove('selected'));
        });

        // Delete button click handler
        deleteBtn.addEventListener('click', function() {
            loadDeleteModalData(1, false); // Initial load, don't preserve filters
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });

        // Add click event listener for delete rows with SweetAlert confirmation
        $(document).on('click', '#deleteModal .table tbody tr', async function() {
            const cells = this.getElementsByTagName('td');
            const rankId = this.getAttribute('data-id');
            const rankName = cells[0].textContent.trim();
            const salaryGrade = cells[1].textContent.trim();
            const monthlySalary = cells[2].textContent.trim();
            const hourlyRate = cells[3].textContent.trim();
            
            // Hide delete modal
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            deleteModal.hide();

            // Show SweetAlert confirmation
            Swal.fire({
                title: 'Confirm Delete',
                html: `
                    <div class="delete-details text-left">
                        <p><strong>Academic Rank:</strong> ${rankName}</p>
                        <p><strong>Salary Grade:</strong> ${salaryGrade}</p>
                        <p><strong>Monthly Salary:</strong> ${monthlySalary}</p>
                        <p><strong>Hourly Rate:</strong> ${hourlyRate}</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6a1b9a',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash-alt"></i> Delete',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary',
                    popup: 'delete-swal'
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch('delete_academic_rank.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ id: rankId })
                        });

                        if (!response.ok) {
                            throw new Error('Failed to delete academic rank');
                        }

                        const result = await response.json();
                        
                        if (result.success) {
                            await Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Academic rank has been deleted successfully',
                                timer: 1500,
                                showConfirmButton: false
                            });

                            // Refresh the main table data and UI
                            await loadAcademicRanks();
                            filterTable();
                            updatePagination();
                            
                            // Refresh the delete modal data
                            await loadDeleteModalData(1);
                        } else {
                            throw new Error(result.message || 'Failed to delete academic rank');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'Failed to delete academic rank. Please try again.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                }
            });
        });

        // Update the delete success handler
        $(document).on('click', '#deleteModal .table tbody tr', async function() {
            // ... existing click handler code ...
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Academic rank has been deleted successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    // Reset filters and reload all tables
                    document.getElementById('deleteFilterRank').value = '';
                    document.getElementById('deleteFilterGrade').value = '';
                    loadAcademicRanks(); // Refresh main table
                    loadDeleteModalData(1); // Refresh delete modal table
                });
            }
        });

        // Initialize filters on page load
        document.addEventListener('DOMContentLoaded', function() {
            window.populateSalaryGradeFilters = function(uniqueGrades) {
                const salaryGradeFilter = document.getElementById('salaryGradeFilter');
                salaryGradeFilter.innerHTML = '<option value="">All Salary Grades</option>';
                uniqueGrades.forEach(grade => {
                    salaryGradeFilter.innerHTML += `<option value="${grade}">Grade ${grade}</option>`;
                });
            }

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

        // Add event listeners for delete modal filters
        document.querySelector('#deleteFilterRank').addEventListener('input', function() {
            loadDeleteModalData(1, true); // Reset to first page when filtering, preserve filters
        });

        document.querySelector('#deleteFilterGrade').addEventListener('change', function() {
            loadDeleteModalData(1, true); // Reset to first page when filtering, preserve filters
        });

        // Function to check if rank exists
        function checkRankExists(rankName) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: 'check_duplicate_rank.php',
                    method: 'POST',
                    data: { rank_name: rankName },
                    success: function(response) {
                        resolve(response.exists);
                    },
                    error: function(xhr, status, error) {
                        reject(error);
                    }
                });
            });
        }
    </script>

    <script>
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
            // Add fade-out class to body
            document.body.classList.add('fade-out');
            
            setTimeout(() => {
                window.location.href = '../loading_screen.php?redirect=index.php';
            }, 50);
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
        });

        // Initialize all dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
        });

        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        // Update time every second
        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>

    <script>
        // Mobile Navigation Toggle
        const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
        const sidebar = document.querySelector('.sidebar');
        const backdrop = document.querySelector('.sidebar-backdrop');
        const body = document.body;

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
            body.classList.toggle('sidebar-open');
        }

        mobileNavToggle.addEventListener('click', toggleSidebar);
        backdrop.addEventListener('click', toggleSidebar);

        // Close sidebar when clicking a link on mobile
        const mobileNavLinks = document.querySelectorAll('.sidebar .nav-link');
        mobileNavLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    toggleSidebar();
                }
            });
        });
    </script>
</body>
</html>