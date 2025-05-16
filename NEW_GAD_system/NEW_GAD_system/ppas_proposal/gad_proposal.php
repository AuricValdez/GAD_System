 <?php
    // Start session
    session_start();

    // Debug session info
    error_log("SESSION in gad_proposal.php: " . print_r($_SESSION, true));

    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        header("Location: ../login.php");
        exit();
    }

    // Set campus from username if not already set
    if (!isset($_SESSION['campus'])) {
        $_SESSION['campus'] = $_SESSION['username'];
    }

    // Ensure campus matches username for this form since it's campus-specific
    $_SESSION['campus'] = $_SESSION['username'];
    $campus = $_SESSION['campus'];

    // Check if the user is Central - for read-only mode
    $isCentral = ($campus === 'Central');

    error_log("Using campus: $campus, isCentral: " . ($isCentral ? 'true' : 'false'));
    ?>
 <!DOCTYPE html>
 <html lang="en" data-bs-theme="light">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>GAD Forms - GAD System</title>
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
             --readonly-bg: #e9ecef;
             --readonly-border: #ced4da;
             --readonly-text: #6c757d;
         }

         /* Custom focus styles for all form elements */
         .form-control:focus,
         .form-select:focus,
         .form-check-input:focus,
         .btn:focus,
         .btn-sm:focus,
         .input-group-text:focus,
         input[type="date"]:focus,
         input[type="time"]:focus,
         input[type="text"]:focus,
         input[type="number"]:focus,
         textarea:focus,
         select:focus {
             border-color: var(--accent-color) !important;
             box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25) !important;
         }

         /* Style for checked checkboxes */
         .form-check-input:checked {
             background-color: var(--accent-color) !important;
             border-color: var(--accent-color) !important;
         }

         /* Dark theme focus styles */
         [data-bs-theme="dark"] .form-control:focus,
         [data-bs-theme="dark"] .form-select:focus,
         [data-bs-theme="dark"] .form-check-input:focus,
         [data-bs-theme="dark"] .btn:focus,
         [data-bs-theme="dark"] .btn-sm:focus,
         [data-bs-theme="dark"] .input-group-text:focus,
         [data-bs-theme="dark"] input[type="date"]:focus,
         [data-bs-theme="dark"] input[type="time"]:focus,
         [data-bs-theme="dark"] input[type="text"]:focus,
         [data-bs-theme="dark"] input[type="number"]:focus,
         [data-bs-theme="dark"] textarea:focus,
         [data-bs-theme="dark"] select:focus {
             border-color: var(--accent-color) !important;
             box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25) !important;
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
             --input-text: #ffffff;
             --card-title: #ffffff;
             --scrollbar-thumb: #6a1b9a;
             --scrollbar-thumb-hover: #9c27b0;
             --accent-color: #9c27b0;
             --accent-hover: #7b1fa2;
             --readonly-bg: #37383A;
             --readonly-border: #495057;
             --readonly-text: #adb5bd;
             --dark-bg: #212529;
             --dark-input: #2b3035;
             --dark-text: #e9ecef;
             --dark-border: #495057;
             --dark-sidebar: #2d2d2d;
         }

         /* Dark theme checked checkboxes */
         [data-bs-theme="dark"] .form-check-input:checked {
             background-color: var(--accent-color) !important;
             border-color: var(--accent-color) !important;
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

         /* GAD Proposal Form specific styles */
         .section {
             background-color: var(--card-bg);
             border-radius: 10px;
             padding: 20px;
             box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
             margin-bottom: 25px;
             border: 1px solid var(--border-color);
         }

         .section h5 {
             color: var(--accent-color);
             border-bottom: 1px solid var(--border-color);
             padding-bottom: 10px;
             margin-bottom: 20px;
         }

         /* Form control heights and styling */
         .form-control,
         .form-select {
             height: 45px;
             border-radius: 8px;
             border: 1px solid var(--border-color);
             color: var(--text-primary);
             background-color: var(--input-bg);
         }

         textarea.form-control {
             height: auto;
             min-height: 100px;
         }

         /* Dark mode specific input styling for interactible fields */
         [data-bs-theme="dark"] .form-control:not([readonly]):not(:disabled):not(.bg-secondary-subtle),
         [data-bs-theme="dark"] .form-select:not([readonly]):not(:disabled):not(.bg-secondary-subtle) {
             background-color: #2B3035 !important;
             color: var(--text-primary);
             border-color: var(--border-color);
         }

         /* Readonly and non-interactible input styling */
         .form-control[readonly],
         .form-select[readonly],
         .form-control:disabled,
         .form-select:disabled,
         .bg-secondary-subtle {
             background-color: var(--readonly-bg) !important;
             color: var(--readonly-text);
             border: 1px dotted var(--readonly-border) !important;
             cursor: not-allowed;
         }

         /* Dark mode specific readonly field styling */
         [data-bs-theme="dark"] .form-control[readonly],
         [data-bs-theme="dark"] .form-select[readonly],
         [data-bs-theme="dark"] .form-control:disabled,
         [data-bs-theme="dark"] .form-select:disabled,
         [data-bs-theme="dark"] .bg-secondary-subtle {
             background-color: #37383A !important;
             border: 1px dotted #6c757d !important;
             color: #adb5bd;
         }

         /* Action buttons within form elements */
         .btn-sm {
             height: 38px;
             min-width: 38px;
             border-radius: 8px;
             display: inline-flex;
             align-items: center;
             justify-content: center;
         }

         /* Style for add/plus buttons */
         .add-responsibility,
         .add-item,
         #add_method,
         #add_monitoring_row,
         .btn-primary,
         .btn-success {
             background-color: rgba(106, 27, 154, 0.1);
             color: var(--accent-color);
             border: 2px dotted var(--accent-color);
             transition: all 0.2s ease;
         }

         .add-responsibility:hover,
         .add-item:hover,
         #add_method:hover,
         #add_monitoring_row:hover,
         .btn-primary:hover,
         .btn-success:hover {
             background-color: var(--accent-color);
             color: white;
             border: 2px solid var(--accent-color);
         }

         /* Fix for active/focus states to prevent green background */
         .add-responsibility:active,
         .add-item:active,
         #add_method:active,
         #add_monitoring_row:active,
         .btn-primary:active,
         .btn-success:active,
         .add-responsibility:focus,
         .add-item:focus,
         #add_method:focus,
         #add_monitoring_row:focus,
         .btn-primary:focus,
         .btn-success:focus {
             background-color: var(--accent-color) !important;
             color: white !important;
             border: 2px solid var(--accent-color) !important;
             box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25) !important;
         }

         [data-bs-theme="dark"] .add-responsibility,
         [data-bs-theme="dark"] .add-item,
         [data-bs-theme="dark"] #add_method,
         [data-bs-theme="dark"] #add_monitoring_row,
         [data-bs-theme="dark"] .btn-primary,
         [data-bs-theme="dark"] .btn-success {
             background-color: rgba(156, 39, 176, 0.1);
             color: var(--accent-color);
             border: 2px dotted var(--accent-color);
         }

         [data-bs-theme="dark"] .add-responsibility:hover,
         [data-bs-theme="dark"] .add-item:hover,
         [data-bs-theme="dark"] #add_method:hover,
         [data-bs-theme="dark"] #add_monitoring_row:hover,
         [data-bs-theme="dark"] .btn-primary:hover,
         [data-bs-theme="dark"] .btn-success:hover,
         [data-bs-theme="dark"] .add-responsibility:active,
         [data-bs-theme="dark"] .add-item:active,
         [data-bs-theme="dark"] #add_method:active,
         [data-bs-theme="dark"] #add_monitoring_row:active,
         [data-bs-theme="dark"] .btn-primary:active,
         [data-bs-theme="dark"] .btn-success:active,
         [data-bs-theme="dark"] .add-responsibility:focus,
         [data-bs-theme="dark"] .add-item:focus,
         [data-bs-theme="dark"] #add_method:focus,
         [data-bs-theme="dark"] #add_monitoring_row:focus,
         [data-bs-theme="dark"] .btn-primary:focus,
         [data-bs-theme="dark"] .btn-success:focus {
             background-color: var(--accent-color) !important;
             color: white !important;
             border: 2px solid var(--accent-color) !important;
         }

         /* Method item (Activity) card styling */
         .method-item {
             border-radius: 8px;
             background-color: var(--card-bg);
             border: 1px solid var(--border-color);
             margin-bottom: 10px;
             transition: all 0.3s ease;
             padding: 12px 15px;
             min-height: auto;
             max-height: fit-content;
         }

         .method-item .row {
             margin-bottom: 0;
         }

         .method-item h6 {
             margin-bottom: 20px;
             /* Increased from 10px to 20px */
             font-size: 1.1rem;
             font-weight: 600;
         }

         .method-item .form-label {
             margin-bottom: 6px;
             font-size: 0.9rem;
             font-weight: 500;
         }

         .method-item .mb-1 {
             margin-bottom: 15px !important;
         }

         .activity-details-container {
             margin-bottom: 15px;
         }

         .detail-row {
             margin-bottom: 10px;
         }

         .add-detail,
         .remove-detail {
             height: 45px !important;
             width: 45px !important;
             padding: 0 !important;
             display: inline-flex !important;
             align-items: center !important;
             justify-content: center !important;
         }

         /* Style for remove detail button */
         .remove-detail {
             background-color: rgba(220, 53, 69, 0.1);
             color: #dc3545;
             border: 2px dotted #dc3545;
             transition: all 0.2s ease;
         }

         .remove-detail:hover,
         .remove-detail:focus,
         .remove-detail:active {
             background-color: #dc3545 !important;
             color: white !important;
             border: 2px solid #dc3545 !important;
         }

         /* Reset form controls to match global height */
         .method-item .form-control,
         .method-item .form-select {
             height: 45px !important;
             padding-top: 0.375rem;
             padding-bottom: 0.375rem;
             font-size: 1rem;
         }

         .method-item .btn-sm {
             height: 45px !important;
             min-width: 45px;
         }

         .method-item .add-detail,
         .method-item .remove-detail {
             height: 45px !important;
             width: 45px !important;
         }

         .method-item .form-control::placeholder {
             font-size: 0.9rem;
         }

         /* Style for remove buttons to match add buttons */
         .remove-method,
         .remove-detail,
         .remove-item,
         .remove-monitoring-row,
         .remove-workplan-row {
             background-color: rgba(220, 53, 69, 0.1);
             color: #dc3545;
             border: 2px dotted #dc3545;
             transition: all 0.2s ease;
         }

         .remove-method:hover,
         .remove-detail:hover,
         .remove-item:hover,
         .remove-monitoring-row:hover,
         .remove-workplan-row:hover {
             background-color: #dc3545;
             color: white;
             border: 2px solid #dc3545;
         }

         .remove-method:focus,
         .remove-detail:focus,
         .remove-item:focus,
         .remove-monitoring-row:focus,
         .remove-workplan-row:focus,
         .remove-method:active,
         .remove-detail:active,
         .remove-item:active,
         .remove-monitoring-row:active,
         .remove-workplan-row:active {
             background-color: #dc3545 !important;
             color: white !important;
             border: 2px solid #dc3545 !important;
             box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
         }

         /* Monitoring table styling */
         .table {
             color: var(--text-primary);
             border-color: var(--border-color);
         }

         .table th {
             background-color: var(--accent-color);
             color: white;
             font-weight: 500;
             white-space: nowrap;
             padding: 12px 15px;
             border-color: var(--border-color);
         }

         .table td {
             padding: 8px 10px;
             border-color: var(--border-color);
             vertical-align: middle;
         }

         .table td .form-control {
             margin: 0;
             height: 38px;
         }

         /* Gantt chart table */
         #gantt_chart th:not(:first-child),
         #gantt_chart td:not(:first-child) {
             text-align: center;
             width: 40px;
             min-width: 40px;
         }

         #gantt_chart th:first-child,
         #gantt_chart td:first-child {
             min-width: 200px;
         }

         /* Form feedback and validation */
         .is-invalid {
             border-color: #dc3545 !important;
             padding-right: calc(1.5em + 0.75rem) !important;
             background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e") !important;
             background-repeat: no-repeat !important;
             background-position: right calc(0.375em + 0.1875rem) center !important;
             background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem) !important;
         }

         .invalid-feedback {
             display: block;
             color: #dc3545;
             margin-top: 0.25rem;
             font-size: 0.875rem;
         }

         .add-responsibility,
         .add-item {
             height: 45px !important;
             width: 45px !important;
             padding: 0 !important;
             display: inline-flex !important;
             align-items: center !important;
             justify-content: center !important;
         }

         /* Ensure consistent width for input fields in dynamic rows */
         .d-flex input[type="text"],
         .d-flex textarea {
             flex: 1;
             width: calc(100% - 55px);
             /* Account for the width of the button (45px) plus margin (10px) */
             margin-right: 10px;
         }

         /* Make sure the flex container properly expands */
         [id$="_container"] .d-flex {
             width: 100%;
             display: flex;
             align-items: center;
         }

         /* Activity detail styling */
         .activity-details-container {
             margin-bottom: 15px;
         }

         .detail-row {
             margin-bottom: 10px;
         }

         .add-detail,
         .remove-detail {
             height: 45px !important;
             width: 45px !important;
             padding: 0 !important;
             display: inline-flex !important;
             align-items: center !important;
             justify-content: center !important;
         }

         /* Style for remove detail button */
         .remove-detail {
             background-color: rgba(220, 53, 69, 0.1);
             color: #dc3545;
             border: 2px dotted #dc3545;
             transition: all 0.2s ease;
         }

         .remove-detail:hover,
         .remove-detail:focus,
         .remove-detail:active {
             background-color: #dc3545 !important;
             color: white !important;
             border: 2px solid #dc3545 !important;
         }

         .method-item .form-control,
         .method-item .form-select {
             height: 40px;
             padding-top: 0.4rem;
             padding-bottom: 0.4rem;
         }

         .method-item .btn-sm {
             height: 35px;
             min-width: 35px;
         }

         .method-item .add-detail,
         .method-item .remove-detail {
             height: 40px !important;
             width: 40px !important;
         }

         /* Section validation styles */
         .section.complete {
             border-color: #28a745 !important;
             border-width: 2px !important;
         }

         .section.complete h5 {
             color: #28a745 !important;
         }

         .section.complete h5 i {
             color: #28a745 !important;
         }

         /* Add a subtle background for completed sections */
         .section.complete {
             background-color: rgba(40, 167, 69, 0.05);
         }

         /* Add transition for smooth color change */
         .section {
             transition: border-color 0.3s ease, background-color 0.3s ease;
         }

         .section h5,
         .section h5 i {
             transition: color 0.3s ease;
         }

         /* Rich input textarea styling */
         .rich-input {
             font-size: 1rem;
             min-height: 60px;
             line-height: 1.5;
             border-radius: 0.25rem;
             transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
             resize: none;
         }

         .rich-input:focus {
             border-color: var(--primary-color);
             box-shadow: 0 0 0 0.2rem rgba(106, 27, 154, 0.25);
         }

         .card-body.p-3 {
             padding: 0.75rem !important;
         }

         .monitoring-item .form-label {
             font-weight: 500;
             font-size: 0.9rem;
         }

         /* Styles for incomplete sections when validation fails */
         .section.incomplete {
             border: 2px solid #dc3545;
             border-radius: 6px;
             box-shadow: 0 0 8px rgba(220, 53, 69, 0.25);
             background-color: rgba(220, 53, 69, 0.05);
             padding: 15px;
             transition: all 0.3s ease;
         }

         .section.incomplete h5 {
             color: #dc3545;
         }

         .section.incomplete h5 i {
             color: #dc3545;
         }

         /* Style for invalid fields */
         .field-invalid {
             border-color: #dc3545 !important;
             padding-right: calc(1.5em + 0.75rem) !important;
             background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e") !important;
             background-repeat: no-repeat !important;
             background-position: right calc(0.375em + 0.1875rem) center !important;
             background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem) !important;
         }

         /* Add animation for highlighting */
         @keyframes highlight-shake {

             0%,
             100% {
                 transform: translateX(0);
             }

             25% {
                 transform: translateX(-5px);
             }

             75% {
                 transform: translateX(5px);
             }
         }

         .highlight-invalid {
             animation: highlight-shake 0.5s ease;
         }

         /* Central user disabled elements styling */
         .central-disabled {
             opacity: 0.65;
             cursor: not-allowed;
             pointer-events: none;
         }

         /* Additional styling for disabled fields */
         input:disabled,
         select:disabled,
         textarea:disabled,
         button:disabled {
             background-color: var(--readonly-bg) !important;
             border-color: var(--readonly-border) !important;
             color: var(--readonly-text) !important;
         }

         /* Special styling for buttons when disabled */
         button.central-disabled,
         .btn.central-disabled {
             opacity: 0.5;
             filter: grayscale(50%);
         }

         /* Ensure the read-only banner stands out */
         .alert-info {
             background-color: rgba(106, 27, 154, 0.1);
             border-color: rgba(106, 27, 154, 0.2);
             color: var(--accent-color);
         }

         /* Force colors on select options across browsers */
         option[disabled] {
             color: inherit !important;
         }

         .swal-blur-container {
             backdrop-filter: blur(5px);
             -webkit-backdrop-filter: blur(5px);
         }

         .swal2-popup {
             border-radius: 15px;
             box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
         }

         .swal2-container {
             z-index: 99999 !important;
             /* Extremely high z-index */
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
                                 <li><a class="dropdown-item" href="#">GAD Proposal Form</a></li>
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
             <h2>GAD Proposal Management</h2>
         </div>

         <div class="card">
             <div class="card-header">
                 <h5 class="card-title">Add GAD Proposal Form</h5>
             </div>
             <div class="card-body">
                 <form id="ppasForm">
                     <!-- Section 1: Basic Information -->
                     <div class="section mb-4">
                         <h5 class="fw-bold mb-3"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                         <div class="row g-3">
                             <div class="col-md-4">
                                 <label for="year" class="form-label">Year</label>
                                 <select class="form-select" id="year" name="year">
                                     <option value="" selected disabled>Select Year</option>
                                     <!-- Years will be populated from database -->
                                 </select>
                                 <small class="text-muted fst-italic">Fetched from PPAs entries.</small>
                             </div>
                             <div class="col-md-4">
                                 <label for="quarter" class="form-label">Quarter</label>
                                 <select class="form-select" id="quarter" name="quarter" disabled>
                                     <option value="" selected disabled>Select Quarter</option>
                                     <option value="Q1">Q1</option>
                                     <option value="Q2">Q2</option>
                                     <option value="Q3">Q3</option>
                                     <option value="Q4">Q4</option>
                                 </select>
                                 <small class="text-muted fst-italic">Select a quarter to enable activity selection</small>
                             </div>
                             <div class="col-md-4">
                                 <label for="activity_title" class="form-label">Activity Title</label>
                                 <select class="form-select" id="activity_title" name="activity_title" disabled>
                                     <option value="" selected disabled>Select Activity Title</option>
                                     <!-- Activity titles will be populated dynamically -->
                                 </select>
                                 <small class="text-muted fst-italic">Choose an activity to load its details</small>
                             </div>
                             <div class="col-md-4">
                                 <label for="project" class="form-label">Project</label>
                                 <input type="text" class="form-control bg-secondary-subtle" id="project" name="project" readonly>
                             </div>
                             <div class="col-md-4">
                                 <label for="program" class="form-label">Program</label>
                                 <input type="text" class="form-control bg-secondary-subtle" id="program" name="program" readonly>
                             </div>
                             <div class="col-md-4">
                                 <label for="venue" class="form-label">Venue</label>
                                 <input type="text" class="form-control bg-secondary-subtle" id="venue" name="venue" readonly>
                             </div>
                             <div class="col-md-12">
                                 <label class="form-label">Duration</label>
                                 <div class="row g-2">
                                     <div class="col-md-3">
                                         <div class="input-group">
                                             <span class="input-group-text">Start Date</span>
                                             <input type="date" class="form-control bg-secondary-subtle" id="start_date" name="start_date" readonly>
                                         </div>
                                     </div>
                                     <div class="col-md-3">
                                         <div class="input-group">
                                             <span class="input-group-text">End Date</span>
                                             <input type="date" class="form-control bg-secondary-subtle" id="end_date" name="end_date" readonly>
                                         </div>
                                     </div>
                                     <div class="col-md-3">
                                         <div class="input-group">
                                             <span class="input-group-text">Start Time</span>
                                             <input type="time" class="form-control bg-secondary-subtle" id="start_time" name="start_time" readonly>
                                         </div>
                                     </div>
                                     <div class="col-md-3">
                                         <div class="input-group">
                                             <span class="input-group-text">End Time</span>
                                             <input type="time" class="form-control bg-secondary-subtle" id="end_time" name="end_time" readonly>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                             <div class="col-md-12">
                                 <label for="mode_of_delivery" class="form-label">Mode of Delivery</label>
                                 <select class="form-select" id="mode_of_delivery" name="mode_of_delivery">
                                     <option value="" selected disabled>Select Mode of Delivery</option>
                                     <option value="Online">Online</option>
                                     <option value="Face-to-Face">Face-to-Face</option>
                                     <option value="Hybrid">Hybrid</option>
                                 </select>
                             </div>
                         </div>
                     </div>

                     <!-- Section 2: Project Personnel -->
                     <div class="section mb-4">
                         <h5 class="fw-bold mb-3"><i class="fas fa-users me-2"></i>Project Personnel</h5>
                         <div class="row g-3">
                             <!-- Project Leaders Section -->
                             <div class="col-md-12">
                                 <label for="project_leaders" class="form-label">Project Leaders</label>
                                 <input type="text" class="form-control bg-secondary-subtle" id="project_leaders" name="project_leaders" readonly>
                             </div>
                             <div class="col-md-12 mb-2">
                                 <label class="form-label">Project Leader Responsibilities</label>
                                 <div id="project_leader_responsibilities_container">
                                     <div class="d-flex mb-2">
                                         <input type="text" class="form-control" name="project_leader_responsibilities[]" placeholder="Enter responsibility">
                                         <button type="button" class="btn btn-sm btn-success add-responsibility ms-2" data-target="project_leader_responsibilities_container">
                                             <i class="fas fa-plus"></i>
                                         </button>
                                     </div>
                                 </div>
                             </div>

                             <!-- Assistant Project Leaders Section -->
                             <div class="col-md-12">
                                 <label for="assistant_project_leaders" class="form-label">Assistant Project Leaders</label>
                                 <input type="text" class="form-control bg-secondary-subtle" id="assistant_project_leaders" name="assistant_project_leaders" readonly>
                             </div>
                             <div class="col-md-12 mb-2">
                                 <label class="form-label">Assistant Project Leader Responsibilities</label>
                                 <div id="assistant_project_leader_responsibilities_container">
                                     <div class="d-flex mb-2">
                                         <input type="text" class="form-control" name="assistant_project_leader_responsibilities[]" placeholder="Enter responsibility">
                                         <button type="button" class="btn btn-sm btn-success add-responsibility ms-2" data-target="assistant_project_leader_responsibilities_container">
                                             <i class="fas fa-plus"></i>
                                         </button>
                                     </div>
                                 </div>
                             </div>

                             <!-- Project Staff Section -->
                             <div class="col-md-12">
                                 <label for="project_staff" class="form-label">Project Staff</label>
                                 <input type="text" class="form-control bg-secondary-subtle" id="project_staff" name="project_staff" readonly>
                             </div>
                             <div class="col-md-12 mb-2">
                                 <label class="form-label">Project Staff Responsibilities</label>
                                 <div id="project_staff_responsibilities_container">
                                     <div class="d-flex mb-2">
                                         <input type="text" class="form-control" name="project_staff_responsibilities[]" placeholder="Enter responsibility">
                                         <button type="button" class="btn btn-sm btn-success add-responsibility ms-2" data-target="project_staff_responsibilities_container">
                                             <i class="fas fa-plus"></i>
                                         </button>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <!-- Section 3: Participants -->
                     <div class="section mb-4">
                         <h5 class="fw-bold mb-3"><i class="fas fa-user-friends me-2"></i>Participants</h5>
                         <div class="row g-3">
                             <div class="col-md-12">
                                 <label for="partner_office" class="form-label">Partner Office/College/Department</label>
                                 <input type="text" class="form-control" id="partner_office" name="partner_office" placeholder="Enter partner office/college/department">
                             </div>
                             <div class="col-md-12">
                                 <label for="type_of_participants" class="form-label">Type of Participants</label>
                                 <input type="text" class="form-control bg-secondary-subtle" id="type_of_participants" name="type_of_participants" readonly>
                             </div>
                             <div class="col-md-4">
                                 <label for="male_participants" class="form-label">Male Participants</label>
                                 <input type="number" class="form-control bg-secondary-subtle" id="male_participants" name="male_participants" readonly>
                             </div>
                             <div class="col-md-4">
                                 <label for="female_participants" class="form-label">Female Participants</label>
                                 <input type="number" class="form-control bg-secondary-subtle" id="female_participants" name="female_participants" readonly>
                             </div>
                             <div class="col-md-4">
                                 <label for="total_participants" class="form-label">Total Participants</label>
                                 <input type="number" class="form-control bg-secondary-subtle" id="total_participants" name="total_participants" readonly>
                             </div>
                         </div>
                     </div>

                     <!-- Section 4: Rationale -->
                     <div class="section mb-4">
                         <h5 class="fw-bold mb-3"><i class="fas fa-align-left me-2"></i>Rationale</h5>
                         <div class="row g-3">
                             <div class="col-md-12">
                                 <label for="rationale" class="form-label">Rationale/Background</label>
                                 <textarea class="form-control" id="rationale" name="rationale" rows="4" placeholder="Enter rationale/background"></textarea>
                             </div>
                         </div>
                     </div>

                     <!-- Section 5: Objectives -->
                     <div class="section mb-4">
                         <h5 class="fw-bold mb-3"><i class="fas fa-bullseye me-2"></i>Objectives</h5>
                         <div class="row g-3">
                             <div class="col-md-12">
                                 <label for="objectives" class="form-label">General Objectives</label>
                                 <textarea class="form-control" id="objectives" name="objectives" rows="3" placeholder="Enter general objectives"></textarea>
                             </div>
                             <div class="col-md-12 mb-2">
                                 <label class="form-label">Specific Objectives</label>
                                 <div id="specific_objectives_container">
                                     <div class="d-flex mb-2">
                                         <input type="text" class="form-control" name="specific_objectives[]" placeholder="Enter specific objective">
                                         <button type="button" class="btn btn-sm btn-success add-item ms-2" data-target="specific_objectives_container">
                                             <i class="fas fa-plus"></i>
                                         </button>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <!-- Section 6: Description and Strategies -->
                     <div class="section mb-4">
                         <h5 class="fw-bold mb-3"><i class="fas fa-file-alt me-2"></i>Description and Strategies</h5>
                         <div class="row g-3">
                             <div class="col-md-12">
                                 <label for="description" class="form-label">Description</label>
                                 <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter description"></textarea>
                             </div>
                             <div class="col-md-12 mb-2">
                                 <label class="form-label">Strategies</label>
                                 <div id="strategies_container">
                                     <div class="d-flex mb-2">
                                         <input type="text" class="form-control" name="strategies[]" placeholder="Enter strategy">
                                         <button type="button" class="btn btn-sm btn-success add-item ms-2" data-target="strategies_container">
                                             <i class="fas fa-plus"></i>
                                         </button>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <!-- Section 7: Methods (Activities / Schedule) -->
                     <div class="section mb-4">
                         <h5 class="fw-bold mb-3"><i class="fas fa-tasks me-2"></i>Methods (Activities / Schedule)</h5>
                         <div id="methods_container">
                             <div class="method-item mb-3">
                                 <div class="d-flex justify-content-between align-items-center">
                                     <h6 class="m-0">Activity 1</h6>
                                     <button type="button" class="btn btn-sm btn-danger remove-method d-none">
                                         <i class="fas fa-trash"></i>
                                     </button>
                                 </div>
                                 <div class="mb-3 mt-3"> <!-- Added mt-3 class to increase spacing -->
                                     <label class="form-label">Activity Name</label>
                                     <input type="text" class="form-control" name="methods[0][name]" placeholder="Enter activity name">
                                 </div>
                                 <div>
                                     <label class="form-label">Activity Details</label>
                                     <div class="activity-details-container" data-index="0">
                                         <div class="detail-row d-flex">
                                             <input type="text" class="form-control" name="methods[0][details][]" placeholder="Enter activity detail">
                                             <button type="button" class="btn btn-sm btn-success add-detail ms-2" data-index="0">
                                                 <i class="fas fa-plus"></i>
                                             </button>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                         <button type="button" class="btn btn-sm btn-primary" id="add_method">
                             <i class="fas fa-plus me-1"></i> Add Activity
                         </button>
                     </div>

                     <!-- Section 8: Materials Needed -->
                     <div class="section mb-4">
                         <h5 class="fw-bold mb-3"><i class="fas fa-box-open me-2"></i>Materials Needed</h5>
                         <div id="materials_container">
                             <div class="d-flex mb-2">
                                 <input type="text" class="form-control" name="materials[]" placeholder="Enter material needed">
                                 <button type="button" class="btn btn-sm btn-success add-item ms-2" data-target="materials_container">
                                     <i class="fas fa-plus"></i>
                                 </button>
                             </div>
                         </div>
                     </div>

                     <!-- Section 9: Work Plan (Timeline of Activities/Gantt Chart) -->
                     <div class="section mb-4">
                         <h5 class="fw-bold mb-3"><i class="fas fa-calendar-alt me-2"></i>Work Plan (Timeline of Activities/Gantt Chart)</h5>
                         <div class="row mb-3">
                             <div class="col-md-12">
                                 <button type="button" class="btn btn-sm btn-primary" id="add_workplan_row">
                                     <i class="fas fa-plus me-1"></i> Add Activity to Workplan
                                 </button>
                             </div>
                         </div>
                         <div class="table-responsive">
                             <table class="table table-bordered" id="workplan_table">
                                 <thead>
                                     <tr>
                                         <th style="width: 250px;">Activity Name</th>
                                         <!-- Days will be added dynamically based on start and end date -->
                                     </tr>
                                 </thead>
                                 <tbody>
                                     <!-- Work plan rows will be added here -->
                                 </tbody>
                             </table>
                         </div>
                         <div class="text-muted mt-2">
                             <small><i class="fas fa-info-circle me-1"></i> Select the days on which each activity will take place.</small>
                         </div>
                     </div>

                     <!-- Section 10: Financial Requirements and Source of Funds -->
                     <div class="section mb-4">
                         <h5 class="fw-bold mb-3"><i class="fas fa-money-bill-wave me-2"></i>Financial Requirements and Source of Funds</h5>
                         <div class="row g-3">
                             <div class="col-md-6">
                                 <label for="source_of_fund" class="form-label">Source of Fund</label>
                                 <input type="text" class="form-control bg-secondary-subtle" id="source_of_fund" name="source_of_fund" readonly>
                             </div>
                             <div class="col-md-6">
                                 <label for="total_budget" class="form-label">Total Budget</label>
                                 <div class="input-group">
                                     <span class="input-group-text">₱</span>
                                     <input type="number" class="form-control bg-secondary-subtle" id="total_budget" name="total_budget" readonly>
                                 </div>
                             </div>
                             <div class="col-md-12">
                                 <label for="budget_breakdown" class="form-label">Budget Breakdown</label>
                                 <textarea class="form-control" id="budget_breakdown" name="budget_breakdown" rows="4" placeholder="Enter budget breakdown details"></textarea>
                             </div>
                         </div>
                     </div>

                     <!-- Section 11: Monitoring and Evaluation Mechanics / Plan -->
                     <div class="section mb-4">
                         <h5 class="fw-bold mb-3"><i class="fas fa-chart-line me-2"></i>Monitoring and Evaluation Mechanics / Plan</h5>

                         <div id="monitoring_container">
                             <!-- First monitoring item card -->
                             <div class="card mb-3 monitoring-item" data-index="0">
                                 <div class="card-body p-3">
                                     <div class="d-flex justify-content-between align-items-center mb-2">
                                         <h6 class="card-title mb-0">Monitoring Item 1</h6>
                                         <button type="button" class="btn btn-sm btn-danger remove-monitoring-row d-none" style="height: 45px; width: 45px; background-color: rgba(220, 53, 69, 0.1); color: #dc3545; border: 2px dotted #dc3545;">
                                             <i class="fas fa-trash"></i>
                                         </button>
                                     </div>

                                     <div class="row g-2">
                                         <div class="col-md-6 mb-2">
                                             <label class="form-label mb-1">Objectives</label>
                                             <textarea class="form-control rich-input" name="monitoring[0][objectives]" placeholder="Enter objectives" rows="2"></textarea>
                                         </div>
                                         <div class="col-md-6 mb-2">
                                             <label class="form-label mb-1">Performance Indicators</label>
                                             <textarea class="form-control rich-input" name="monitoring[0][performance_indicators]" placeholder="Enter performance indicators" rows="2"></textarea>
                                         </div>
                                         <div class="col-md-6 mb-2">
                                             <label class="form-label mb-1">Baseline Data</label>
                                             <textarea class="form-control rich-input" name="monitoring[0][baseline_data]" placeholder="Enter baseline data" rows="2"></textarea>
                                         </div>
                                         <div class="col-md-6 mb-2">
                                             <label class="form-label mb-1">Performance Target</label>
                                             <textarea class="form-control rich-input" name="monitoring[0][performance_target]" placeholder="Enter performance target" rows="2"></textarea>
                                         </div>
                                         <div class="col-md-6 mb-2">
                                             <label class="form-label mb-1">Data Source</label>
                                             <textarea class="form-control rich-input" name="monitoring[0][data_source]" placeholder="Enter data source" rows="2"></textarea>
                                         </div>
                                         <div class="col-md-6 mb-2">
                                             <label class="form-label mb-1">Collection Method</label>
                                             <textarea class="form-control rich-input" name="monitoring[0][collection_method]" placeholder="Enter collection method" rows="2"></textarea>
                                         </div>
                                         <div class="col-md-6 mb-2">
                                             <label class="form-label mb-1">Frequency of Data Collection</label>
                                             <textarea class="form-control rich-input" name="monitoring[0][frequency]" placeholder="Enter frequency" rows="2"></textarea>
                                         </div>
                                         <div class="col-md-6 mb-2">
                                             <label class="form-label mb-1">Office/Persons Responsible</label>
                                             <textarea class="form-control rich-input" name="monitoring[0][responsible]" placeholder="Enter responsible persons" rows="2"></textarea>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <div class="mt-3">
                             <button type="button" class="btn btn-sm btn-primary" id="add_monitoring_row">
                                 <i class="fas fa-plus me-1"></i> Add Monitoring Item
                             </button>
                         </div>
                     </div>

                     <!-- Section 12: Sustainability Plan -->
                     <div class="section mb-4">
                         <h5 class="fw-bold mb-3"><i class="fas fa-seedling me-2"></i>Sustainability Plan</h5>
                         <div class="row g-3">
                             <div class="col-md-12">
                                 <label for="sustainability_plan" class="form-label">Sustainability Plan</label>
                                 <textarea class="form-control" id="sustainability_plan" name="sustainability_plan" rows="3" placeholder="Enter sustainability plan"></textarea>
                             </div>
                             <div class="col-md-12 mb-2">
                                 <label class="form-label">Specific Plans</label>
                                 <div id="specific_plans_container">
                                     <div class="d-flex mb-2">
                                         <input type="text" class="form-control" name="specific_plans[]" placeholder="Enter specific plan">
                                         <button type="button" class="btn btn-sm btn-success add-item ms-2" data-target="specific_plans_container">
                                             <i class="fas fa-plus"></i>
                                         </button>
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

             // Define the submitForm function early to ensure it's available
             function submitForm(formData) {
                 console.log("submitForm called with formData:", formData);

                 // Check if all sections are complete before submission
                 const allSectionsComplete = document.querySelectorAll('.section:not(.complete)').length === 0;

                 if (!allSectionsComplete) {
                     console.log("Not all sections are complete, stopping form submission");
                     Swal.fire({
                         title: 'Incomplete Form',
                         text: 'Please complete all sections before submitting.',
                         icon: 'warning',
                         confirmButtonColor: '#6a1b9a'
                     });
                     return;
                 }

                 // Convert FormData to object with proper structure
                 const data = {};

                 // First, handle standard form fields from formData
                 formData.forEach((value, key) => {
                     // Handle array fields (with [] in name)
                     if (key.includes('[]')) {
                         const cleanKey = key.replace('[]', '');
                         if (!data[cleanKey]) {
                             data[cleanKey] = [];
                         }
                         data[cleanKey].push(value);
                     } else if (key.includes('[') && key.includes(']')) {
                         // Handle nested objects like methods[0][name]
                         const matches = key.match(/(.+)\[(\d+)\]\[(.+)\]/);
                         if (matches) {
                             const [, object, index, property] = matches;
                             if (!data[object]) data[object] = [];
                             if (!data[object][index]) data[object][index] = {};
                             data[object][index][property] = value;
                         }
                     } else {
                         data[key] = value;
                     }
                 });

                 try {
                     // Now use the proper helper functions to ensure structured data
                     // Use try-catch to fallback to formData if any function fails

                     // Project personnel data - ensuring proper structure
                     data.project_leader_responsibilities = getResponsibilities('project-leader');
                     data.assistant_leader_responsibilities = getResponsibilities('assistant-leader');
                     data.staff_responsibilities = getResponsibilities('staff');

                     // Methods with details
                     if (typeof getMethodsWithDetails === 'function') {
                         data.methods = getMethodsWithDetails();
                     } else {
                         console.warn("getMethodsWithDetails function not found, using form data");
                     }

                     // Special attention for workplan data
                     console.log("About to collect workplan data...");
                     data.workplan = getWorkplanData();
                     console.log("Workplan data collected:", data.workplan);

                     // Monitoring data
                     if (typeof getMonitoringData === 'function') {
                         data.monitoring = getMonitoringData();
                     } else {
                         console.warn("getMonitoringData function not found, using form data");
                     }
                 } catch (error) {
                     console.error("Error in data collection:", error);
                     // Continue with what we have already collected
                 }

                 // Fix field name mismatches - map 'objectives' to 'general_objectives'
                 if (data.objectives) {
                     data.general_objectives = data.objectives;
                     console.log("Mapped 'objectives' field to 'general_objectives' for server compatibility");
                 }

                 console.log("Prepared data object:", data);
                 console.log("JSON data to be sent:", JSON.stringify(data));

                 // Check for critical required fields
                 if (!data.start_date || !data.end_date) {
                     console.error("Missing critical date fields:", {
                         start_date: data.start_date,
                         end_date: data.end_date
                     });
                 }

                 if (!data.activity_title) {
                     console.error("Missing activity_title field");
                 }

                 // Send form data to server
                 console.log("Sending data to save_gad_proposal_robust.php");
                 fetch('save_gad_proposal_robust.php', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json'
                         },
                         body: JSON.stringify(data)
                     })
                     .then(response => {
                         console.log("Response received:", response.status, response.statusText);

                         // Check for non-200 responses
                         if (!response.ok) {
                             // Try to get more details about the error
                             return response.text().then(text => {
                                 console.error("Error response body:", text);
                                 try {
                                     // Try to parse JSON, but handle if it's not valid JSON
                                     return JSON.parse(text);
                                 } catch (e) {
                                     return {
                                         success: false,
                                         message: `Server returned ${response.status}: ${text || response.statusText}`
                                     };
                                 }
                             });
                         }

                         return response.json();
                     })
                     .then(data => {
                         console.log("Parsed response data:", data);

                         if (data.success) {
                             Swal.fire({
                                 title: 'Success!',
                                 text: 'GAD Proposal has been saved successfully',
                                 icon: 'success',
                                 timer: 1500,
                                 timerProgressBar: true,
                                 showConfirmButton: false,
                                 backdrop: `rgba(0,0,0,0.7)`,
                                 allowOutsideClick: false,
                                 customClass: {
                                     container: 'swal-blur-container'
                                 }
                             }).then(() => {
                                 // Redirect to the list view after successful save
                                 window.location.reload();
                             });
                         } else {
                             console.error("Server reported error:", data.message);
                             Swal.fire({
                                 title: 'Error!',
                                 text: data.message || 'Something went wrong',
                                 icon: 'error',
                                 confirmButtonColor: '#6a1b9a'
                             });
                         }
                     })
                     .catch(error => {
                         console.error('Fetch error:', error);

                         // Create a more detailed error message
                         let errorMessage = 'Failed to save GAD Proposal: ' + error.message;

                         // Add debugging information for JSON parse errors
                         if (error instanceof SyntaxError && error.message.includes('JSON')) {
                             errorMessage += '\n\nThis appears to be a server configuration issue. Please check:';
                             errorMessage += '\n1. PHP error reporting settings';
                             errorMessage += '\n2. Memory limits in php.ini';
                             errorMessage += '\n3. Server error logs';

                             // Log additional guidance for developers
                             console.error('DEVELOPER NOTE: The server is returning HTML instead of JSON. ' +
                                 'Check for PHP errors, memory limits, or execution timeouts.');
                         }

                         Swal.fire({
                             title: 'Error!',
                             text: errorMessage,
                             icon: 'error',
                             confirmButtonColor: '#6a1b9a'
                         });
                     });
             }

             // The rest of your JavaScript code will follow...

             // Apply saved theme on page load
             document.addEventListener('DOMContentLoaded', function() {
                 // Check if we need to open edit modal after page refresh
                 if (localStorage.getItem('openGadEditModal') === 'true') {
                     // Clear the flag
                     localStorage.removeItem('openGadEditModal');

                     // Open the edit modal after a short delay to ensure DOM is ready
                     setTimeout(() => {
                         openGadProposalModal('edit');
                     }, 500);
                 }

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

                 // Initialize form functionality

                 // 1. Populate years dropdown (current year and 5 years forward)
                 const yearSelect = document.getElementById('year');
                 const currentYear = new Date().getFullYear();
                 for (let i = 0; i <= 5; i++) {
                     const year = currentYear + i;
                     const option = document.createElement('option');
                     option.value = year;
                     option.textContent = year;
                     yearSelect.appendChild(option);
                 }

                 // 2. Calculate total participants
                 function calculateTotal() {
                     const maleCount = parseInt(document.getElementById('male_participants').value) || 0;
                     const femaleCount = parseInt(document.getElementById('female_participants').value) || 0;
                     document.getElementById('total_participants').value = maleCount + femaleCount;
                 }

                 // 3. Add event listeners for dynamic form elements
                 document.addEventListener('click', function(e) {
                     // Handle add responsibilities and other items with add buttons
                     if (e.target.closest('.add-responsibility') || e.target.closest('.add-item')) {
                         const button = e.target.closest('.add-responsibility') || e.target.closest('.add-item');
                         const targetContainer = document.getElementById(button.dataset.target);
                         const parentDiv = button.closest('div.d-flex');
                         const newRow = parentDiv.cloneNode(true);
                         const input = newRow.querySelector('input');
                         input.value = '';

                         // Remove add button from new row and add delete button
                         const addButton = newRow.querySelector('.add-responsibility, .add-item');
                         if (addButton) {
                             addButton.remove();
                         }

                         // Make sure input field maintains proper width
                         input.className = input.className + ' flex-grow-1';

                         // Add remove button if it doesn't exist
                         if (!newRow.querySelector('.remove-item')) {
                             const removeBtn = document.createElement('button');
                             removeBtn.className = 'btn btn-sm btn-danger remove-item ms-2';
                             removeBtn.style.height = '45px';
                             removeBtn.style.width = '45px';
                             removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
                             newRow.appendChild(removeBtn);
                         }

                         // Ensure consistent styling for the entire row
                         newRow.style.display = 'flex';
                         newRow.style.width = '100%';

                         targetContainer.appendChild(newRow);
                     }

                     // Handle remove responsibilities and other items
                     if (e.target.closest('.remove-item')) {
                         const button = e.target.closest('.remove-item');
                         const rowToRemove = button.closest('div.d-flex');
                         rowToRemove.remove();
                     }

                     // Handle add method/activity button
                     if (e.target.closest('#add_method')) {
                         const methodsContainer = document.getElementById('methods_container');
                         const methodItems = methodsContainer.querySelectorAll('.method-item');
                         const newIndex = methodItems.length;

                         const methodTemplate = methodItems[0].cloneNode(true);
                         const title = methodTemplate.querySelector('h6');
                         title.textContent = `Activity ${newIndex + 1}`;

                         // Update labels to be standard size
                         const labels = methodTemplate.querySelectorAll('.form-label');
                         labels.forEach(label => {
                             label.className = 'form-label';
                         });

                         // Update input names and data attributes
                         const inputs = methodTemplate.querySelectorAll('input:not([name*="details"])');
                         inputs.forEach(input => {
                             if (input.name) {
                                 input.name = input.name.replace(/methods\[\d+\]/, `methods[${newIndex}]`);
                                 input.value = '';
                             }
                         });

                         // Reset activity details container
                         const detailsContainer = methodTemplate.querySelector('.activity-details-container');
                         detailsContainer.setAttribute('data-index', newIndex);
                         const detailRows = detailsContainer.querySelectorAll('.detail-row');

                         // Keep only the first detail row and update its attributes
                         if (detailRows.length > 1) {
                             for (let i = 1; i < detailRows.length; i++) {
                                 detailRows[i].remove();
                             }
                         }

                         // Update the first detail row
                         const firstDetailRow = detailRows[0];
                         const detailInput = firstDetailRow.querySelector('input');
                         detailInput.name = `methods[${newIndex}][details][]`;
                         detailInput.value = '';

                         // Update the add detail button data index
                         const addDetailBtn = firstDetailRow.querySelector('.add-detail');
                         addDetailBtn.setAttribute('data-index', newIndex);

                         // Show remove button for all except first item
                         const removeBtn = methodTemplate.querySelector('.remove-method');
                         if (newIndex > 0) {
                             removeBtn.classList.remove('d-none');
                         }

                         // Ensure consistent spacing
                         methodTemplate.className = 'method-item mb-3';

                         // Set consistent spacing for sections
                         const sections = methodTemplate.querySelectorAll('.mb-1, .mb-2');
                         sections.forEach(section => {
                             section.className = section.className.replace(/mb-[1-2]/g, 'mb-3');
                         });

                         methodsContainer.appendChild(methodTemplate);
                     }

                     // Handle add activity detail button
                     if (e.target.closest('.add-detail')) {
                         const button = e.target.closest('.add-detail');
                         const activityIndex = button.getAttribute('data-index');
                         const detailsContainer = button.closest('.activity-details-container');
                         const detailRow = button.closest('.detail-row');

                         // Clone the detail row
                         const newDetailRow = detailRow.cloneNode(true);
                         const detailInput = newDetailRow.querySelector('input');
                         detailInput.value = '';

                         // Remove add button from new row and add delete button
                         const addButton = newDetailRow.querySelector('.add-detail');
                         if (addButton) {
                             addButton.remove();
                         }

                         // Add remove button if it doesn't exist
                         if (!newDetailRow.querySelector('.remove-detail')) {
                             const removeBtn = document.createElement('button');
                             removeBtn.className = 'btn btn-sm btn-danger remove-detail ms-2';
                             removeBtn.style.height = '45px';
                             removeBtn.style.width = '45px';
                             removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
                             newDetailRow.appendChild(removeBtn);
                         }

                         detailsContainer.appendChild(newDetailRow);
                     }

                     // Handle remove activity detail button
                     if (e.target.closest('.remove-detail')) {
                         const button = e.target.closest('.remove-detail');
                         const detailRow = button.closest('.detail-row');
                         detailRow.remove();
                     }

                     // Handle remove method/activity button
                     if (e.target.closest('.remove-method')) {
                         const button = e.target.closest('.remove-method');
                         const methodItem = button.closest('.method-item');
                         methodItem.remove();

                         // Renumber the remaining activities
                         const methodItems = document.querySelectorAll('.method-item');
                         methodItems.forEach((item, index) => {
                             const title = item.querySelector('h6');
                             title.textContent = `Activity ${index + 1}`;

                             // Update input names
                             const inputs = item.querySelectorAll('input, textarea');
                             inputs.forEach(input => {
                                 if (input.name) {
                                     input.name = input.name.replace(/methods\[\d+\]/, `methods[${index}]`);
                                 }
                             });
                         });
                     }

                     // Handle add monitoring row button
                     if (e.target.closest('#add_monitoring_row')) {
                         const container = document.getElementById('monitoring_container');
                         const items = container.querySelectorAll('.monitoring-item');
                         const newIndex = items.length;

                         // Clone the first item
                         const template = items[0].cloneNode(true);
                         template.dataset.index = newIndex;

                         // Update the title
                         const title = template.querySelector('.card-title');
                         title.textContent = `Monitoring Item ${newIndex + 1}`;

                         // Update input names and clear values
                         const inputs = template.querySelectorAll('textarea');
                         inputs.forEach(input => {
                             input.name = input.name.replace(/monitoring\[\d+\]/, `monitoring[${newIndex}]`);
                             input.value = '';
                         });

                         // Show the remove button for additional items
                         const removeBtn = template.querySelector('.remove-monitoring-row');
                         removeBtn.classList.remove('d-none');

                         // Add the new item to the container
                         container.appendChild(template);

                         // Trigger validation
                         validateMonitoringSection();
                     }

                     // Handle remove monitoring row button
                     if (e.target.closest('.remove-monitoring-row')) {
                         const button = e.target.closest('.remove-monitoring-row');
                         const card = button.closest('.monitoring-item');
                         card.remove();

                         // Renumber the remaining items
                         const container = document.getElementById('monitoring_container');
                         const items = container.querySelectorAll('.monitoring-item');
                         items.forEach((item, index) => {
                             item.dataset.index = index;

                             // Update title
                             const title = item.querySelector('.card-title');
                             title.textContent = `Monitoring Item ${index + 1}`;

                             // Update input names
                             const inputs = item.querySelectorAll('textarea');
                             inputs.forEach(input => {
                                 input.name = input.name.replace(/monitoring\[\d+\]/, `monitoring[${index}]`);
                             });
                         });

                         // Trigger validation
                         validateMonitoringSection();
                     }
                 });

                 // 7. Handle activity title selection to populate related fields
                 document.getElementById('activity_title').addEventListener('change', function() {
                     const activityId = this.value;
                     if (!activityId) return;

                     // Fetch activity details from server
                     fetch(`get_activity_details.php?id=${activityId}`)
                         .then(response => response.json())
                         .then(data => {
                             if (data.success) {
                                 // Populate form fields with the fetched data
                                 document.getElementById('project').value = data.project || '';
                                 document.getElementById('program').value = data.program || '';
                                 document.getElementById('venue').value = data.venue || '';

                                 // Populate date and time fields separately
                                 if (data.start_date_only) document.getElementById('start_date').value = data.start_date_only || '';
                                 if (data.end_date_only) document.getElementById('end_date').value = data.end_date_only || '';
                                 if (data.start_time_only) document.getElementById('start_time').value = data.start_time_only || '';
                                 if (data.end_time_only) document.getElementById('end_time').value = data.end_time_only || '';

                                 document.getElementById('project_leaders').value = data.project_leaders || '';
                                 document.getElementById('assistant_project_leaders').value = data.assistant_project_leaders || '';
                                 document.getElementById('project_staff').value = data.project_staff || '';

                                 // Populate participant fields
                                 document.getElementById('type_of_participants').value = data.external_type || '';
                                 document.getElementById('male_participants').value = data.total_male || '';
                                 document.getElementById('female_participants').value = data.total_female || '';
                                 document.getElementById('total_participants').value = data.total_beneficiaries || '';

                                 // Populate financial fields
                                 document.getElementById('source_of_fund').value = data.source_of_budget || '';
                                 document.getElementById('total_budget').value = data.approved_budget || '';

                                 // Update workplan table if dates are available
                                 if (data.start_date_only && data.end_date_only) {
                                     updateWorkPlanTable();
                                 }
                             } else {
                                 console.error('Error fetching activity details:', data.message || 'Unknown error');
                             }
                         })
                         .catch(error => {
                             console.error('Error fetching activity details:', error);
                         });
                 });

                 // 8. Apply custom styling for dark mode
                 function applyDarkModeStyles() {
                     const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';

                     if (isDarkMode) {
                         // Apply dark mode styles to non-interactible fields
                         document.querySelectorAll('input[readonly], textarea[readonly], input:disabled, textarea:disabled, .bg-secondary-subtle').forEach(input => {
                             input.style.backgroundColor = '#37383A';
                             input.style.border = '1px dotted #6c757d';
                             input.style.color = '#adb5bd';
                         });

                         // Apply dark mode styles to interactible fields
                         document.querySelectorAll('input:not([readonly]):not(:disabled):not(.bg-secondary-subtle), textarea:not([readonly]):not(:disabled):not(.bg-secondary-subtle), select:not([readonly]):not(:disabled):not(.bg-secondary-subtle)').forEach(input => {
                             input.style.backgroundColor = '#2B3035';
                             input.style.color = '#fff';
                             input.style.borderColor = '#495057';
                         });
                     } else {
                         // Reset all styles in light mode
                         document.querySelectorAll('input, textarea, select').forEach(input => {
                             input.style.backgroundColor = '';
                             input.style.border = '';
                             input.style.color = '';
                             input.style.borderColor = '';
                         });

                         // Apply light mode styles to non-interactible fields
                         document.querySelectorAll('input[readonly], textarea[readonly], input:disabled, textarea:disabled, .bg-secondary-subtle').forEach(input => {
                             input.style.backgroundColor = '#e9ecef';
                             input.style.border = '1px dotted #ced4da';
                             input.style.color = '#6c757d';
                         });
                     }
                 }

                 // Initial application of dark mode styles
                 applyDarkModeStyles();

                 // Update styles when theme changes
                 document.addEventListener('theme-changed', applyDarkModeStyles);

                 // Trigger the event when theme is toggled
                 const originalToggleTheme = toggleTheme;
                 toggleTheme = function() {
                     originalToggleTheme();

                     // Dispatch theme changed event
                     const themeChangedEvent = new Event('theme-changed');
                     document.dispatchEvent(themeChangedEvent);
                 };

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

             // 9. Initialize Work Plan Table when dates are selected
             document.getElementById('start_date').addEventListener('change', updateWorkPlanTable);
             document.getElementById('end_date').addEventListener('change', updateWorkPlanTable);

             function updateWorkPlanTable() {
                 const startDateEl = document.getElementById('start_date');
                 const endDateEl = document.getElementById('end_date');

                 if (!startDateEl.value || !endDateEl.value) {
                     return; // Invalid dates, do nothing
                 }

                 const startDate = new Date(startDateEl.value);
                 const endDate = new Date(endDateEl.value);

                 if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                     return; // Invalid dates, do nothing
                 }

                 // Clear existing headers
                 const thead = document.querySelector('#workplan_table thead tr');
                 // Keep only the first cell (Activity Name)
                 while (thead.cells.length > 1) {
                     thead.deleteCell(1);
                 }

                 // Add day columns
                 const days = [];
                 const currentDate = new Date(startDate);
                 while (currentDate <= endDate) {
                     days.push(new Date(currentDate));
                     currentDate.setDate(currentDate.getDate() + 1);
                 }

                 // Month names for formatting
                 const monthNames = [
                     "January", "February", "March", "April", "May", "June",
                     "July", "August", "September", "October", "November", "December"
                 ];

                 days.forEach(day => {
                     const th = document.createElement('th');
                     const month = monthNames[day.getMonth()];
                     // Create combined month-day text with full month name in white
                     th.textContent = `${month} ${day.getDate()}`;
                     th.title = `${month} ${day.getDate()}, ${day.getFullYear()}`; // Full date as tooltip
                     th.style.width = '80px'; // Increase width to accommodate full month name
                     th.style.minWidth = '80px';
                     th.style.textAlign = 'center';
                     th.style.fontSize = '0.8rem'; // Smaller font to fit
                     th.style.color = '#ffffff'; // Set text color to white
                     thead.appendChild(th);
                 });

                 // Update existing rows if any
                 const tbody = document.querySelector('#workplan_table tbody');
                 const rows = tbody.querySelectorAll('tr');
                 rows.forEach(row => {
                     // Keep only the first cell (Activity Name)
                     while (row.cells.length > 1) {
                         row.deleteCell(1);
                     }

                     // Add checkbox cells for each day
                     days.forEach(() => {
                         const td = document.createElement('td');
                         td.style.textAlign = 'center';
                         const checkbox = document.createElement('input');
                         checkbox.type = 'checkbox';
                         checkbox.className = 'form-check-input';
                         // Use a consistent naming pattern for the checkboxes
                         const activityInput = row.querySelector('input[type="text"]');
                         const activityIndex = activityInput ? activityInput.dataset.index : '0';
                         const dayIndex = row.cells.length - 1; // Zero-based index of the current day
                         checkbox.name = `workplan[${activityIndex}][days][${dayIndex}]`;
                         checkbox.value = '1';
                         td.appendChild(checkbox);
                         row.appendChild(td);
                     });
                 });

                 // If there are no rows yet, add an initial row
                 if (rows.length === 0) {
                     addWorkplanRow(true); // The first row should not have a delete button
                 }
             }

             // Update the add workplan row function for consistent button styling
             function addWorkplanRow(isFirstRow = false) {
                 const tbody = document.querySelector('#workplan_table tbody');
                 const rows = tbody.querySelectorAll('tr');
                 const rowIndex = rows.length;

                 const tr = document.createElement('tr');

                 // Activity Name cell
                 const tdName = document.createElement('td');
                 const inputGroup = document.createElement('div');
                 inputGroup.className = 'd-flex';

                 const input = document.createElement('input');
                 input.type = 'text';
                 input.className = 'form-control';
                 input.name = `workplan[${rowIndex}][activity]`;
                 input.placeholder = 'Enter activity name';
                 input.dataset.index = rowIndex;

                 // Set a consistent width for the input field
                 input.style.maxWidth = '600px';
                 input.style.width = '600px';

                 inputGroup.appendChild(input);

                 // Only add the delete button if it's not the first row
                 if (!isFirstRow) {
                     const removeBtn = document.createElement('button');
                     removeBtn.type = 'button';
                     removeBtn.className = 'btn btn-sm btn-danger remove-workplan-row ms-2';
                     removeBtn.style.height = '45px';
                     removeBtn.style.width = '45px';
                     removeBtn.style.backgroundColor = 'rgba(220, 53, 69, 0.1)';
                     removeBtn.style.color = '#dc3545';
                     removeBtn.style.border = '2px dotted #dc3545';
                     removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
                     inputGroup.appendChild(removeBtn);
                 } else {
                     // Add a spacer div to maintain consistent width
                     const spacer = document.createElement('div');
                     spacer.style.width = '45px';
                     spacer.style.marginLeft = '0.5rem'; // ms-2 equivalent
                     inputGroup.appendChild(spacer);
                 }

                 tdName.appendChild(inputGroup);
                 tr.appendChild(tdName);

                 // Add checkbox cells for each day
                 const thead = document.querySelector('#workplan_table thead tr');
                 for (let i = 1; i < thead.cells.length; i++) {
                     const td = document.createElement('td');
                     td.style.textAlign = 'center';
                     const checkbox = document.createElement('input');
                     checkbox.type = 'checkbox';
                     checkbox.className = 'form-check-input';
                     checkbox.name = `workplan[${rowIndex}][days][${i-1}]`;
                     checkbox.value = '1';
                     td.appendChild(checkbox);
                     tr.appendChild(td);
                 }

                 tbody.appendChild(tr);

                 // Validate the table after adding a row
                 validateWorkPlanSection();

                 return tr;
             }

             // Update the remove workplan row function to maintain at least one row
             document.addEventListener('click', function(e) {
                 if (e.target.closest('.remove-workplan-row')) {
                     const button = e.target.closest('.remove-workplan-row');
                     const row = button.closest('tr');
                     const tbody = row.closest('tbody');

                     // Check if this is the last row - don't remove if it's the last one
                     if (tbody.querySelectorAll('tr').length > 1) {
                         row.remove();
                     } else {
                         // Clear the inputs in the last row instead of removing it
                         const inputs = row.querySelectorAll('input');
                         inputs.forEach(input => {
                             if (input.type === 'text') {
                                 input.value = '';
                             } else if (input.type === 'checkbox') {
                                 input.checked = false;
                             }
                         });
                     }

                     // Reindex remaining rows
                     const rows = tbody.querySelectorAll('tr');
                     rows.forEach((row, index) => {
                         const input = row.querySelector('input[type="text"]');
                         if (input) {
                             input.name = `workplan[${index}][activity]`;
                             input.dataset.index = index;

                             // Update checkbox names
                             const checkboxes = row.querySelectorAll('input[type="checkbox"]');
                             checkboxes.forEach((checkbox, dayIndex) => {
                                 checkbox.name = `workplan[${index}][days][${dayIndex}]`;
                             });
                         }
                     });

                     // Validate after removing
                     validateWorkPlanSection();
                 }
             });

             // Fetch years from database based on campus
             function fetchYearsFromDatabase() {
                 // Get the logged-in username for strict validation
                 const loggedInUser = "<?php echo $_SESSION['username']; ?>";

                 console.log("%c[CRITICAL] Starting year fetch for logged-in user:", "color:red;font-weight:bold", loggedInUser);

                 // Add cache-busting parameter and random request ID for tracking
                 const requestId = Math.random().toString(36).substring(2, 15);
                 fetch(`get_years.php?nocache=${Date.now()}&request=${requestId}`)
                     .then(response => {
                         console.log(`[${requestId}] Response status:`, response.status);
                         if (!response.ok) {
                             throw new Error(`Server returned ${response.status}: ${response.statusText}`);
                         }
                         return response.json();
                     })
                     .then(data => {
                         console.log(`%c[${requestId}] Complete years data from server:`, "color:blue;font-weight:bold", data);
                         console.log(`%c[${requestId}] Response campus: ${data.campus}, Username: ${data.username || 'not provided'}, Record count: ${data.record_count || 'unknown'}, Years count: ${data.years?.length || 0}`, "color:green");

                         // STRICT VALIDATION: Only accept response if campus matches logged-in user
                         if (data.campus !== loggedInUser) {
                             console.error(`%cSECURITY ALERT! Campus mismatch!`, "color:red;font-size:16px;font-weight:bold");
                             console.error(`Response campus (${data.campus}) doesn't match logged-in user (${loggedInUser})`);
                             throw new Error(`Security validation failed: Campus mismatch`);
                         }

                         if (data.success) {
                             const yearSelect = document.getElementById('year');
                             const campus = data.campus; // Get campus from the response

                             console.log(`[${requestId}] Processing successful response for ${campus} with ${data.years.length} years`);

                             // Clear existing options except the first one
                             while (yearSelect.options.length > 1) {
                                 yearSelect.remove(1);
                             }

                             // Check if there are any years
                             if (!data.years || data.years.length === 0) {
                                 // Add a clear "No data" option
                                 const noDataOption = document.createElement('option');
                                 noDataOption.value = "";
                                 noDataOption.textContent = `No data available for ${campus}`;
                                 noDataOption.disabled = true;
                                 yearSelect.appendChild(noDataOption);

                                 console.log(`[${requestId}] No years found for ${campus}, showing no data message`);

                             } else {
                                 // Add years from database
                                 data.years.forEach((year, index) => {
                                     console.log(`[${requestId}] Adding year[${index}]: ${year} for ${campus}`);
                                     const option = document.createElement('option');
                                     option.value = year;
                                     option.textContent = year;
                                     yearSelect.appendChild(option);
                                 });

                                 console.log(`%c[${requestId}] Successfully loaded ${data.years.length} years for ${campus}`, "color:green;font-weight:bold");
                             }
                         } else {
                             console.error('Error fetching years:', data.message);
                             // Show error without fallback
                             const yearSelect = document.getElementById('year');

                             // Clear existing options except the first one
                             while (yearSelect.options.length > 1) {
                                 yearSelect.remove(1);
                             }

                             // Add an error option
                             const errorOption = document.createElement('option');
                             errorOption.value = "";
                             errorOption.textContent = "Error loading years";
                             errorOption.disabled = true;
                             yearSelect.appendChild(errorOption);
                         }
                     })
                     .catch(error => {
                         console.error(`[${requestId}] Error:`, error);

                         // Show error without fallback
                         const yearSelect = document.getElementById('year');

                         // Clear existing options except the first one
                         while (yearSelect.options.length > 1) {
                             yearSelect.remove(1);
                         }

                         // Add an error option
                         const errorOption = document.createElement('option');
                         errorOption.value = "";
                         errorOption.textContent = "Error connecting to server";
                         errorOption.disabled = true;
                         yearSelect.appendChild(errorOption);
                     });
             }

             // Function to fetch activities based on year and quarter
             function fetchActivities(year, quarter) {
                 if (!year || !quarter) return;

                 // Get username directly from session for the campus
                 const campus = "<?php echo $_SESSION['username']; ?>";

                 console.log("Fetching activities:", {
                     year,
                     quarter,
                     campus
                 });

                 // Get the current activity ID being edited (if in edit mode)
                 const currentEditingId = isEditMode ? currentProposalId : null;
                 console.log("Current editing proposal ID:", currentEditingId);

                 // Get the current activity ID from the form data if available
                 let currentActivityId = null;
                 if (isEditMode && originalFormData && originalFormData.ppas_form_id) {
                     currentActivityId = originalFormData.ppas_form_id;
                     console.log("Current activity ID being edited:", currentActivityId);
                 }

                 // Explicitly include campus in the query params
                 fetch(`get_activities.php?year=${year}&quarter=${quarter}&campus=${encodeURIComponent(campus)}`)
                     .then(response => {
                         if (!response.ok) {
                             throw new Error(`Server returned ${response.status}: ${response.statusText}`);
                         }
                         return response.json();
                     })
                     .then(data => {
                         console.log("Activities data:", data);

                         if (data.success) {
                             const activitySelect = document.getElementById('activity_title');

                             // Clear existing options except the first one
                             while (activitySelect.options.length > 1) {
                                 activitySelect.remove(1);
                             }

                             // Check if there are any activities
                             if (!data.activities || data.activities.length === 0) {
                                 // Add a "No activities found" option
                                 const noActivitiesOption = document.createElement('option');
                                 noActivitiesOption.value = "";
                                 noActivitiesOption.textContent = `No activities found for ${campus} (${year} ${quarter})`;
                                 noActivitiesOption.disabled = true;
                                 activitySelect.appendChild(noActivitiesOption);

                                 // Show a message to the user
                                 console.log(`No activities found for ${campus} (${year} ${quarter})`);
                             } else {
                                 // Add activities from database
                                 data.activities.forEach(activity => {
                                     const option = document.createElement('option');
                                     option.value = activity.id;

                                     // Default styling
                                     option.textContent = activity.title;

                                     // Check for special cases and apply styling
                                     if (activity.is_duplicate === true) {
                                         // Apply both class and inline styles for maximum compatibility
                                         option.classList.add('duplicate-option');
                                         option.setAttribute('style', 'color: #FF0000 !important; font-style: italic !important; font-weight: bold !important;');
                                         option.disabled = true;
                                         option.title = "Duplicate activity";
                                         option.textContent = `${activity.title} (Duplicate)`;

                                         // Debug output to console
                                         console.log(`%cMarking duplicate: ${activity.title} (ID: ${activity.id})`, "color:#FF0000;font-weight:bold");

                                     } else if (activity.has_proposal === true) {
                                         // Apply both class and inline styles for maximum compatibility
                                         option.classList.add('proposal-option');
                                         option.setAttribute('style', 'color: #FF0000 !important; font-style: italic !important;');

                                         // OVERRIDE: If this activity is the one being edited, don't disable it
                                         if (isEditMode && currentActivityId && activity.id == currentActivityId) {
                                             console.log(`Allowing selection of activity ${activity.id} (${activity.title}) because it's being edited`);
                                             option.disabled = false;
                                             option.title = "Currently editing this activity's proposal";
                                             option.textContent = `${activity.title} (Current)`;
                                             option.setAttribute('style', 'color: #008000 !important; font-weight: bold !important;');
                                         } else {
                                             option.disabled = true;
                                             option.title = "Already has a GAD proposal";
                                             option.textContent = `${activity.title} (Has proposal)`;
                                         }
                                     }

                                     activitySelect.appendChild(option);
                                 });

                                 // Display information about duplicates
                                 if (data.duplicates_found) {
                                     console.log(`%cShowing all activities including ${data.count - data.unique_count} duplicates highlighted in red`, "color:orange;font-weight:bold");

                                     // Add a note about the displayed duplicates
                                     const duplicateInfoOption = document.createElement('option');
                                     duplicateInfoOption.value = "";
                                     duplicateInfoOption.textContent = `Note: ${data.count - data.unique_count} duplicate activity/activities shown in red`;
                                     duplicateInfoOption.disabled = true;
                                     duplicateInfoOption.style.color = "red";
                                     duplicateInfoOption.style.fontStyle = "italic";
                                     activitySelect.appendChild(duplicateInfoOption);
                                 } else {
                                     console.log(`Loaded ${data.count} activities for ${campus} (${year} ${quarter})`);
                                 }
                             }
                         } else {
                             console.error('Error fetching activities:', data.message);
                             // Add a "No activities found" option with error message
                             const activitySelect = document.getElementById('activity_title');
                             while (activitySelect.options.length > 1) {
                                 activitySelect.remove(1);
                             }

                             const errorOption = document.createElement('option');
                             errorOption.value = "";
                             errorOption.textContent = "Error loading activities";
                             errorOption.disabled = true;
                             activitySelect.appendChild(errorOption);
                         }
                     })
                     .catch(error => {
                         console.error('Error fetching activities:', error);

                         // Add an error option to the dropdown
                         const activitySelect = document.getElementById('activity_title');
                         while (activitySelect.options.length > 1) {
                             activitySelect.remove(1);
                         }

                         const errorOption = document.createElement('option');
                         errorOption.value = "";
                         errorOption.textContent = "Error loading activities";
                         errorOption.disabled = true;
                         activitySelect.appendChild(errorOption);
                     });
             }

             // Function to validate all form sections and update visual feedback
             function validateAllSections() {
                 // First, attach listeners to any new inputs that might have been added
                 attachListenersToNewInputs();

                 // Then perform the validation
                 validateBasicInfoSection();
                 validateProjectPersonnelSection();
                 validateParticipantsSection();
                 validateRationaleSection();
                 validateObjectivesSection();
                 validateDescriptionStrategySection();
                 validateMethodsSection();
                 validateMaterialsSection();
                 validateWorkPlanSection();
                 validateFinancialSection();
                 validateMonitoringSection();
                 validateSustainabilitySection();
             }

             // Helper function to attach listeners to any new inputs
             function attachListenersToNewInputs() {
                 const allInputs = document.querySelectorAll('.section input, .section select, .section textarea');
                 allInputs.forEach(input => {
                     // Check if the input already has our event listeners
                     const hasListener = input.getAttribute('data-has-validation-listener');
                     if (!hasListener) {
                         input.addEventListener('change', validateAllSections);
                         input.addEventListener('input', validateAllSections);
                         input.setAttribute('data-has-validation-listener', 'true');
                     }
                 });
             }

             // Validate Project Personnel Section - update to check all dynamic rows
             function validateProjectPersonnelSection() {
                 const section = document.querySelector('.section:nth-of-type(2)');
                 const requiredFields = [
                     'project_leaders',
                     'assistant_project_leaders',
                     'project_staff'
                 ];

                 // Check if all required fields have values
                 const hasRequiredFields = checkRequiredFields(requiredFields);

                 // Check each responsibility input in each container
                 const plResponsibilities = document.querySelectorAll('[name="project_leader_responsibilities[]"]');
                 const aplResponsibilities = document.querySelectorAll('[name="assistant_project_leader_responsibilities[]"]');
                 const psResponsibilities = document.querySelectorAll('[name="project_staff_responsibilities[]"]');

                 // Consider valid only if all inputs have values
                 const hasProjectLeaderResponsibilities = plResponsibilities.length > 0 &&
                     Array.from(plResponsibilities).every(input => input.value.trim() !== '');

                 const hasAssistantLeaderResponsibilities = aplResponsibilities.length > 0 &&
                     Array.from(aplResponsibilities).every(input => input.value.trim() !== '');

                 const hasStaffResponsibilities = psResponsibilities.length > 0 &&
                     Array.from(psResponsibilities).every(input => input.value.trim() !== '');

                 const isComplete = hasRequiredFields &&
                     hasProjectLeaderResponsibilities &&
                     hasAssistantLeaderResponsibilities &&
                     hasStaffResponsibilities;

                 updateSectionStatus(section, isComplete);
             }

             // Validate Basic Information Section
             function validateBasicInfoSection() {
                 const section = document.querySelector('.section:nth-of-type(1)');
                 const requiredFields = [
                     'year',
                     'quarter',
                     'activity_title',
                     'project',
                     'program',
                     'venue',
                     'start_date',
                     'end_date',
                     'start_time',
                     'end_time',
                     'mode_of_delivery'
                 ];

                 const isComplete = checkRequiredFields(requiredFields);
                 updateSectionStatus(section, isComplete);
             }

             // Validate Participants Section
             function validateParticipantsSection() {
                 const section = document.querySelector('.section:nth-of-type(3)');
                 const requiredFields = [
                     'partner_office',
                     'type_of_participants',
                     'male_participants',
                     'female_participants',
                     'total_participants'
                 ];

                 const isComplete = checkRequiredFields(requiredFields);
                 updateSectionStatus(section, isComplete);
             }

             // Validate Rationale Section
             function validateRationaleSection() {
                 const section = document.querySelector('.section:nth-of-type(4)');
                 const rationale = document.getElementById('rationale');

                 const isComplete = rationale && rationale.value.trim() !== '';
                 updateSectionStatus(section, isComplete);
             }

             // Validate Objectives Section
             function validateObjectivesSection() {
                 const section = document.querySelector('.section:nth-of-type(5)');
                 const objectives = document.getElementById('objectives');

                 // Check general objectives
                 const hasGeneralObjectives = objectives && objectives.value.trim() !== '';

                 // Check specific objectives - all must be filled
                 const specificObjectives = document.querySelectorAll('[name="specific_objectives[]"]');
                 const hasSpecificObjectives = specificObjectives.length > 0 &&
                     Array.from(specificObjectives).every(input => input.value.trim() !== '');

                 const isComplete = hasGeneralObjectives && hasSpecificObjectives;
                 updateSectionStatus(section, isComplete);
             }

             // Validate Description and Strategies Section
             function validateDescriptionStrategySection() {
                 const section = document.querySelector('.section:nth-of-type(6)');
                 const description = document.getElementById('description');

                 // Check description
                 const hasDescription = description && description.value.trim() !== '';

                 // Check strategies - all must be filled
                 const strategies = document.querySelectorAll('[name="strategies[]"]');
                 const hasStrategies = strategies.length > 0 &&
                     Array.from(strategies).every(input => input.value.trim() !== '');

                 const isComplete = hasDescription && hasStrategies;
                 updateSectionStatus(section, isComplete);
             }

             // Validate Methods Section
             function validateMethodsSection() {
                 const section = document.querySelector('.section:nth-of-type(7)');
                 const methodItems = document.querySelectorAll('.method-item');

                 if (methodItems.length === 0) {
                     updateSectionStatus(section, false);
                     return;
                 }

                 // Check if each method is complete
                 let allMethodsComplete = true;

                 methodItems.forEach(item => {
                     const nameInput = item.querySelector('input[name*="[name]"]');
                     const detailInputs = item.querySelectorAll('input[name*="[details]"]');

                     const hasName = nameInput && nameInput.value.trim() !== '';
                     const allDetailsComplete = detailInputs.length > 0 &&
                         Array.from(detailInputs).every(input => input.value.trim() !== '');

                     if (!hasName || !allDetailsComplete) {
                         allMethodsComplete = false;
                     }
                 });

                 updateSectionStatus(section, allMethodsComplete);
             }

             // Validate Materials Section
             function validateMaterialsSection() {
                 const section = document.querySelector('.section:nth-of-type(8)');
                 const materials = document.querySelectorAll('[name="materials[]"]');

                 const isComplete = materials.length > 0 &&
                     Array.from(materials).every(input => input.value.trim() !== '');

                 updateSectionStatus(section, isComplete);
             }

             // Validate Work Plan Section
             function validateWorkPlanSection() {
                 const section = document.querySelector('.section:nth-of-type(9)');
                 const workplanRows = document.querySelectorAll('#workplan_table tbody tr');

                 if (workplanRows.length === 0) {
                     updateSectionStatus(section, false);
                     return;
                 }

                 // Check if all workplan rows are complete
                 let allRowsComplete = true;

                 workplanRows.forEach(row => {
                     const activityInput = row.querySelector('input[name*="workplan"][name*="activity"]');
                     const checkboxes = row.querySelectorAll('input[type="checkbox"]');

                     const hasActivity = activityInput && activityInput.value.trim() !== '';
                     const hasCheckedDay = Array.from(checkboxes).some(checkbox => checkbox.checked);

                     if (!hasActivity || !hasCheckedDay) {
                         allRowsComplete = false;
                     }
                 });

                 updateSectionStatus(section, allRowsComplete);
             }

             // Validate Financial Section
             function validateFinancialSection() {
                 const section = document.querySelector('.section:nth-of-type(10)');
                 const requiredFields = ['source_of_fund', 'total_budget'];
                 const budgetBreakdown = document.getElementById('budget_breakdown');

                 const hasBudgetBreakdown = budgetBreakdown && budgetBreakdown.value.trim() !== '';
                 const hasRequiredFields = checkRequiredFields(requiredFields);

                 const isComplete = hasRequiredFields && hasBudgetBreakdown;
                 updateSectionStatus(section, isComplete);
             }

             // Validate Monitoring Section
             function validateMonitoringSection() {
                 const section = document.querySelector('.section:nth-of-type(11)'); // Get the actual section element
                 const monitoringItems = document.querySelectorAll('.monitoring-item');
                 const allItemsComplete = Array.from(monitoringItems).every(item => {
                     const inputs = item.querySelectorAll('textarea.rich-input');
                     return Array.from(inputs).every(input => input.value.trim() !== '');
                 });

                 updateSectionStatus(section, allItemsComplete);
             }

             // Validate Sustainability Section
             function validateSustainabilitySection() {
                 const section = document.querySelector('.section:nth-of-type(12)');
                 const sustainabilityPlan = document.getElementById('sustainability_plan');

                 // Check sustainability plan
                 const hasSustainabilityPlan = sustainabilityPlan && sustainabilityPlan.value.trim() !== '';

                 // Check specific plans - all must be filled
                 const specificPlans = document.querySelectorAll('[name="specific_plans[]"]');
                 const hasSpecificPlans = specificPlans.length > 0 &&
                     Array.from(specificPlans).every(input => input.value.trim() !== '');

                 const isComplete = hasSustainabilityPlan && hasSpecificPlans;
                 updateSectionStatus(section, isComplete);
             }

             // Helper function to check if all required fields in an array have values
             function checkRequiredFields(fieldIds) {
                 return fieldIds.every(id => {
                     const element = document.getElementById(id);
                     return element && element.value.trim() !== '';
                 });
             }

             // Update the visual status of a section
             function updateSectionStatus(section, isComplete) {
                 // Check if section is a string (section title) or DOM element
                 if (typeof section === 'string') {
                     // If it's a string, find the section by its heading text
                     const sectionTitle = section;
                     const allSections = document.querySelectorAll('.section');
                     let foundSection = null;

                     // Search for the section with matching heading text
                     allSections.forEach(sec => {
                         const heading = sec.querySelector('h5');
                         if (heading && heading.textContent.includes(sectionTitle)) {
                             foundSection = sec;
                         }
                     });

                     // Use the found section if available, otherwise log an error
                     if (foundSection) {
                         section = foundSection;
                     } else {
                         console.error(`Section with title "${sectionTitle}" not found`);
                         return; // Exit if section not found
                     }
                 }

                 // Now section should be a DOM element
                 if (section && section.classList) {
                     if (isComplete) {
                         section.classList.add('complete');
                     } else {
                         section.classList.remove('complete');
                     }
                 } else {
                     console.error('Invalid section element provided to updateSectionStatus', section);
                 }
             }

             // Add event listeners to all input fields to validate on change
             function addValidationListeners() {
                 // Get all input elements within form sections
                 const inputElements = document.querySelectorAll('.section input, .section select, .section textarea');

                 // Add change listener to each input
                 inputElements.forEach(input => {
                     input.addEventListener('change', validateAllSections);
                     input.addEventListener('input', validateAllSections);
                 });

                 // Add listeners for dynamic elements
                 document.addEventListener('click', function(e) {
                     // When adding or removing elements
                     if (e.target.closest('.add-responsibility') ||
                         e.target.closest('.add-item') ||
                         e.target.closest('.remove-item') ||
                         e.target.closest('#add_method') ||
                         e.target.closest('.remove-method') ||
                         e.target.closest('.add-detail') ||
                         e.target.closest('.remove-detail') ||
                         e.target.closest('#add_monitoring_row') ||
                         e.target.closest('.remove-monitoring-row') ||
                         e.target.closest('#add_workplan_row') ||
                         e.target.closest('.remove-workplan-row')) {

                         // Wait a bit for DOM to update
                         setTimeout(validateAllSections, 100);
                     }
                 });

                 // Listen for any new elements added to the DOM (use MutationObserver)
                 const formSections = document.querySelectorAll('.section');
                 formSections.forEach(section => {
                     const observer = new MutationObserver(mutations => {
                         mutations.forEach(mutation => {
                             if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                                 // Attach event listeners to any new input elements
                                 mutation.addedNodes.forEach(node => {
                                     if (node.nodeType === Node.ELEMENT_NODE) {
                                         const newInputs = node.querySelectorAll('input, select, textarea');
                                         newInputs.forEach(input => {
                                             input.addEventListener('change', validateAllSections);
                                             input.addEventListener('input', validateAllSections);
                                         });

                                         // Also re-validate immediately
                                         validateAllSections();
                                     }
                                 });
                             }
                         });
                     });

                     // Start observing the section for changes
                     observer.observe(section, {
                         childList: true,
                         subtree: true
                     });
                 });

                 // Initial validation
                 validateAllSections();
             }

             // Call this when document is ready
             document.addEventListener('DOMContentLoaded', function() {
                 // Add validation listeners after a short delay to ensure all elements are loaded
                 setTimeout(addValidationListeners, 500);
             });

             // Add back the event listener for the add workplan row button
             document.addEventListener('DOMContentLoaded', function() {
                 // Add workplan row button event
                 const addWorkplanButton = document.getElementById('add_workplan_row');
                 if (addWorkplanButton) {
                     addWorkplanButton.addEventListener('click', function() {
                         addWorkplanRow();
                     });
                 }
             });

             // Form submission handling with validation visualization
             document.getElementById('ppasForm').addEventListener('submit', function(e) {
                 e.preventDefault();

                 console.log("Form submission triggered");

                 // Clear previous incomplete indicators
                 clearIncompleteIndicators();

                 // Validate and mark incomplete fields
                 const isValid = validateFormWithVisualFeedback();
                 console.log("Form validation result:", isValid);

                 if (!isValid) {
                     console.log("Form validation failed, stopping submission");
                     // Scroll to the first incomplete section
                     const firstIncompleteSection = document.querySelector('.section.incomplete');
                     if (firstIncompleteSection) {
                         firstIncompleteSection.scrollIntoView({
                             behavior: 'smooth',
                             block: 'start'
                         });

                         // Highlight the section with animation
                         firstIncompleteSection.classList.add('highlight-invalid');
                         setTimeout(() => {
                             firstIncompleteSection.classList.remove('highlight-invalid');
                         }, 600);
                     }

                     return; // Stop form submission
                 }

                 // If all validations pass, collect and submit form data
                 const formData = new FormData(this);

                 // Debug FormData contents
                 console.log("FormData entries:");
                 for (let [key, value] of formData.entries()) {
                     console.log(`${key}:`, value);
                 }

                 submitForm(formData);
             });

             // Clear all incomplete indicators
             function clearIncompleteIndicators() {
                 // Remove incomplete section styling
                 document.querySelectorAll('.section.incomplete').forEach(section => {
                     section.classList.remove('incomplete');
                 });

                 // Remove field-invalid styling
                 document.querySelectorAll('.field-invalid').forEach(field => {
                     field.classList.remove('field-invalid');
                 });
             }

             // Validate form with visual feedback for incomplete fields
             function validateFormWithVisualFeedback() {
                 let isValid = true;

                 // Validate all sections
                 const basicInfoValid = validateBasicInfoSectionWithFeedback();
                 const personnelValid = validateProjectPersonnelSectionWithFeedback();
                 const participantsValid = validateParticipantsSectionWithFeedback();
                 const rationaleValid = validateRationaleSectionWithFeedback();
                 const objectivesValid = validateObjectivesSectionWithFeedback();
                 const descriptionValid = validateDescriptionSectionWithFeedback();
                 const methodsValid = validateMethodsSectionWithFeedback();
                 const materialsValid = validateMaterialsSectionWithFeedback();
                 const workplanValid = validateWorkPlanSectionWithFeedback();
                 const financialValid = validateFinancialSectionWithFeedback();
                 const monitoringValid = validateMonitoringSectionWithFeedback();
                 const sustainabilityValid = validateSustainabilitySectionWithFeedback();

                 console.log("Section validation results:", {
                     basicInfo: basicInfoValid,
                     personnel: personnelValid,
                     participants: participantsValid,
                     rationale: rationaleValid,
                     objectives: objectivesValid,
                     description: descriptionValid,
                     methods: methodsValid,
                     materials: materialsValid,
                     workplan: workplanValid,
                     financial: financialValid,
                     monitoring: monitoringValid,
                     sustainability: sustainabilityValid
                 });

                 // Ensure critical fields are present and valid
                 const criticalFields = {
                     'activity_title': document.getElementById('activity_title')?.value,
                     'mode_of_delivery': document.getElementById('mode_of_delivery')?.value,
                     'partner_office': document.getElementById('partner_office')?.value,
                     'rationale': document.getElementById('rationale')?.value,
                     'general_objectives': document.getElementById('general_objectives')?.value,
                     'description': document.getElementById('description')?.value,
                     'budget_breakdown': document.getElementById('budget_breakdown')?.value,
                     'sustainability_plan': document.getElementById('sustainability_plan')?.value,
                     'start_date': document.getElementById('start_date')?.value,
                     'end_date': document.getElementById('end_date')?.value
                 };

                 console.log("Critical fields check:", criticalFields);

                 // Check for any missing critical fields
                 for (const [fieldName, fieldValue] of Object.entries(criticalFields)) {
                     if (!fieldValue) {
                         console.error(`Missing critical field: ${fieldName}`);
                         const element = document.getElementById(fieldName);
                         if (element) {
                             element.classList.add('field-invalid');
                             isValid = false;
                         }
                     }
                 }

                 // Check if any section is incomplete
                 const incompleteSections = document.querySelectorAll('.section.incomplete');
                 if (incompleteSections.length > 0) {
                     isValid = false;
                 }

                 return isValid;
             }

             // Validation functions with visual feedback
             function validateBasicInfoSectionWithFeedback() {
                 const section = document.querySelector('.section:nth-of-type(1)');
                 const requiredFields = ['year', 'quarter', 'activity_title', 'mode_of_delivery'];

                 let isComplete = true;

                 // Check required fields
                 requiredFields.forEach(fieldId => {
                     const field = document.getElementById(fieldId);
                     if (!field || !field.value.trim()) {
                         isComplete = false;
                         field.classList.add('field-invalid');
                     }
                 });

                 // Update section status
                 if (!isComplete) {
                     section.classList.add('incomplete');
                 }

                 return isComplete;
             }

             function validateProjectPersonnelSectionWithFeedback() {
                 const section = document.querySelector('.section:nth-of-type(2)');
                 let isComplete = true;

                 // Check project leader responsibilities
                 const projectLeaderResponsibilities = document.querySelectorAll('[name="project_leader_responsibilities[]"]');
                 if (projectLeaderResponsibilities.length === 0 || !Array.from(projectLeaderResponsibilities).some(input => input.value.trim() !== '')) {
                     isComplete = false;
                     projectLeaderResponsibilities.forEach(input => {
                         input.classList.add('field-invalid');
                     });
                 }

                 // Check assistant project leader responsibilities
                 const assistantLeaderResponsibilities = document.querySelectorAll('[name="assistant_project_leader_responsibilities[]"]');
                 if (assistantLeaderResponsibilities.length === 0 || !Array.from(assistantLeaderResponsibilities).some(input => input.value.trim() !== '')) {
                     isComplete = false;
                     assistantLeaderResponsibilities.forEach(input => {
                         input.classList.add('field-invalid');
                     });
                 }

                 // Check project staff responsibilities
                 const staffResponsibilities = document.querySelectorAll('[name="project_staff_responsibilities[]"]');
                 if (staffResponsibilities.length === 0 || !Array.from(staffResponsibilities).some(input => input.value.trim() !== '')) {
                     isComplete = false;
                     staffResponsibilities.forEach(input => {
                         input.classList.add('field-invalid');
                     });
                 }

                 // Update section status
                 if (!isComplete) {
                     section.classList.add('incomplete');
                 }

                 return isComplete;
             }

             // The rest of the validation functions - implementing just a few for brevity
             function validateParticipantsSectionWithFeedback() {
                 const section = document.querySelector('.section:nth-of-type(3)');
                 const partnerOffice = document.getElementById('partner_office');

                 let isComplete = true;

                 if (!partnerOffice || !partnerOffice.value.trim()) {
                     isComplete = false;
                     partnerOffice.classList.add('field-invalid');
                 }

                 // Update section status
                 if (!isComplete) {
                     section.classList.add('incomplete');
                 }

                 return isComplete;
             }

             function validateRationaleSectionWithFeedback() {
                 const section = document.querySelector('.section:nth-of-type(4)');
                 const rationale = document.getElementById('rationale');

                 let isComplete = rationale && rationale.value.trim() !== '';

                 if (!isComplete) {
                     rationale.classList.add('field-invalid');
                     section.classList.add('incomplete');
                 }

                 return isComplete;
             }


             // Add the missing validation functions after validateRationaleSectionWithFeedback()

             function validateObjectivesSectionWithFeedback() {
                 const section = document.querySelector('.section:nth-of-type(5)');
                 const objectives = document.getElementById('objectives');
                 const specificObjectives = document.querySelectorAll('[name="specific_objectives[]"]');

                 let isComplete = true;

                 // Check general objectives
                 if (!objectives || !objectives.value.trim()) {
                     isComplete = false;
                     objectives.classList.add('field-invalid');
                 }

                 // Check specific objectives
                 if (specificObjectives.length === 0 || !Array.from(specificObjectives).some(input => input.value.trim() !== '')) {
                     isComplete = false;
                     specificObjectives.forEach(input => {
                         input.classList.add('field-invalid');
                     });
                 }

                 // Update section status
                 if (!isComplete) {
                     section.classList.add('incomplete');
                 }

                 return isComplete;
             }

             function validateDescriptionSectionWithFeedback() {
                 const section = document.querySelector('.section:nth-of-type(6)');
                 const description = document.getElementById('description');
                 const strategies = document.querySelectorAll('[name="strategies[]"]');

                 let isComplete = true;

                 // Check description
                 if (!description || !description.value.trim()) {
                     isComplete = false;
                     description.classList.add('field-invalid');
                 }

                 // Check strategies
                 if (strategies.length === 0 || !Array.from(strategies).some(input => input.value.trim() !== '')) {
                     isComplete = false;
                     strategies.forEach(input => {
                         input.classList.add('field-invalid');
                     });
                 }

                 // Update section status
                 if (!isComplete) {
                     section.classList.add('incomplete');
                 }

                 return isComplete;
             }

             function validateMethodsSectionWithFeedback() {
                 const section = document.querySelector('.section:nth-of-type(7)');
                 const methodItems = document.querySelectorAll('.method-item');

                 let isComplete = true;

                 if (methodItems.length === 0) {
                     isComplete = false;
                 } else {
                     methodItems.forEach(item => {
                         const nameInput = item.querySelector('input[name*="[name]"]');
                         const detailInputs = item.querySelectorAll('input[name*="[details]"]');

                         // Check if name is filled
                         if (!nameInput || !nameInput.value.trim()) {
                             isComplete = false;
                             nameInput.classList.add('field-invalid');
                         }

                         // Check if at least one detail is filled
                         const hasFilledDetail = Array.from(detailInputs).some(input => input.value.trim() !== '');
                         if (!hasFilledDetail) {
                             isComplete = false;
                             detailInputs.forEach(input => {
                                 input.classList.add('field-invalid');
                             });
                         }
                     });
                 }

                 // Update section status
                 if (!isComplete) {
                     section.classList.add('incomplete');
                 }

                 return isComplete;
             }

             function validateMaterialsSectionWithFeedback() {
                 const section = document.querySelector('.section:nth-of-type(8)');
                 const materials = document.querySelectorAll('[name="materials[]"]');

                 let isComplete = materials.length > 0 &&
                     Array.from(materials).some(input => input.value.trim() !== '');

                 if (!isComplete) {
                     materials.forEach(input => {
                         input.classList.add('field-invalid');
                     });
                     section.classList.add('incomplete');
                 }

                 return isComplete;
             }

             function validateWorkPlanSectionWithFeedback() {
                 const section = document.querySelector('.section:nth-of-type(9)');
                 const workplanRows = document.querySelectorAll('#workplan_table tbody tr');

                 let isComplete = true;

                 if (workplanRows.length === 0) {
                     isComplete = false;
                 } else {
                     workplanRows.forEach(row => {
                         const activityInput = row.querySelector('input[name*="workplan"][name*="activity"]');
                         const checkboxes = row.querySelectorAll('input[type="checkbox"]');

                         if (!activityInput || !activityInput.value.trim()) {
                             isComplete = false;
                             activityInput.classList.add('field-invalid');
                         }

                         // Check if at least one day is checked
                         const hasCheckedDay = Array.from(checkboxes).some(checkbox => checkbox.checked);
                         if (!hasCheckedDay) {
                             isComplete = false;
                             checkboxes.forEach(checkbox => {
                                 checkbox.parentElement.classList.add('field-invalid');
                             });
                         }
                     });
                 }

                 // Update section status
                 if (!isComplete) {
                     section.classList.add('incomplete');
                 }

                 return isComplete;
             }

             function validateFinancialSectionWithFeedback() {
                 const section = document.querySelector('.section:nth-of-type(10)');
                 const budgetBreakdown = document.getElementById('budget_breakdown');

                 let isComplete = budgetBreakdown && budgetBreakdown.value.trim() !== '';

                 if (!isComplete) {
                     budgetBreakdown.classList.add('field-invalid');
                     section.classList.add('incomplete');
                 }

                 return isComplete;
             }

             function validateMonitoringSectionWithFeedback() {
                 const section = document.querySelector('.section:nth-of-type(11)');
                 const monitoringItems = document.querySelectorAll('.monitoring-item');

                 let isComplete = true;

                 monitoringItems.forEach(item => {
                     const inputs = item.querySelectorAll('textarea.rich-input');
                     const allFilled = Array.from(inputs).every(input => input.value.trim() !== '');

                     if (!allFilled) {
                         isComplete = false;
                         inputs.forEach(input => {
                             if (!input.value.trim()) {
                                 input.classList.add('field-invalid');
                             }
                         });
                     }
                 });

                 // Update section status
                 if (!isComplete) {
                     section.classList.add('incomplete');
                 }

                 return isComplete;
             }

             function validateSustainabilitySectionWithFeedback() {
                 const section = document.querySelector('.section:nth-of-type(12)');
                 const sustainabilityPlan = document.getElementById('sustainability_plan');
                 const specificPlans = document.querySelectorAll('[name="specific_plans[]"]');

                 let isComplete = true;

                 // Check sustainability plan
                 if (!sustainabilityPlan || !sustainabilityPlan.value.trim()) {
                     isComplete = false;
                     sustainabilityPlan.classList.add('field-invalid');
                 }

                 // Check specific plans
                 if (specificPlans.length === 0 || !Array.from(specificPlans).some(input => input.value.trim() !== '')) {
                     isComplete = false;
                     specificPlans.forEach(input => {
                         input.classList.add('field-invalid');
                     });
                 }

                 // Update section status
                 if (!isComplete) {
                     section.classList.add('incomplete');
                 }

                 return isComplete;
             }

             // Clear previous incomplete indicators
             function clearIncompleteIndicators() {
                 // Remove incomplete section styling
                 document.querySelectorAll('.section.incomplete').forEach(section => {
                     section.classList.remove('incomplete');
                 });

                 // Remove field-invalid styling
                 document.querySelectorAll('.field-invalid').forEach(field => {
                     field.classList.remove('field-invalid');
                 });
             }

             // Add a flag to track if validation has been triggered by the add button
             let validationTriggered = false;

             // Validate form with visual feedback for incomplete fields
             function validateFormWithVisualFeedback() {
                 let isValid = true;

                 // Set validation triggered flag to true
                 validationTriggered = true;

                 // Validate all sections
                 const sections = [
                     validateBasicInfoSectionWithFeedback(),
                     validateProjectPersonnelSectionWithFeedback(),
                     validateParticipantsSectionWithFeedback(),
                     validateRationaleSectionWithFeedback(),
                     validateObjectivesSectionWithFeedback(),
                     validateDescriptionSectionWithFeedback(),
                     validateMethodsSectionWithFeedback(),
                     validateMaterialsSectionWithFeedback(),
                     validateWorkPlanSectionWithFeedback(),
                     validateFinancialSectionWithFeedback(),
                     validateMonitoringSectionWithFeedback(),
                     validateSustainabilitySectionWithFeedback()
                 ];

                 // Check if any section is incomplete
                 if (sections.includes(false)) {
                     isValid = false;
                 }

                 return isValid;
             }

             // Add the event listeners once the DOM is loaded
             document.addEventListener('DOMContentLoaded', function() {
                 // Add click event to the add button for validation
                 const addBtn = document.getElementById('addBtn');
                 if (addBtn) {
                     addBtn.addEventListener('click', function(e) {
                         // Prevent default form submission (our submit event handler will handle this)
                         e.preventDefault();

                         // Trigger the form's submit event
                         document.getElementById('ppasForm').dispatchEvent(new Event('submit'));
                     });
                 }

                 // Add real-time validation clearing for all input fields
                 const form = document.getElementById('ppasForm');

                 // For text inputs, textareas, and selects
                 form.addEventListener('input', function(e) {
                     // Only perform real-time validation if validation has been triggered by the add button
                     if (!validationTriggered) return;

                     const target = e.target;
                     if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.tagName === 'SELECT') {
                         // Remove error styling from the field itself
                         if (target.value.trim() !== '') {
                             target.classList.remove('field-invalid');

                             // Revalidate the section this field belongs to
                             const section = target.closest('.section');
                             if (section) {
                                 validateSectionByIndex(Array.from(document.querySelectorAll('.section')).indexOf(section) + 1);
                             }
                         }
                     }
                 });

                 // For checkboxes
                 form.addEventListener('change', function(e) {
                     // Only perform real-time validation if validation has been triggered by the add button
                     if (!validationTriggered) return;

                     const target = e.target;
                     if (target.type === 'checkbox') {
                         if (target.checked) {
                             // Remove error styling from the parent
                             target.parentElement.classList.remove('field-invalid');

                             // Revalidate the section this field belongs to
                             const section = target.closest('.section');
                             if (section) {
                                 validateSectionByIndex(Array.from(document.querySelectorAll('.section')).indexOf(section) + 1);
                             }
                         }
                     }
                 });

                 // Listen for add/remove events that might affect validation
                 document.addEventListener('click', function(e) {
                     // Only perform real-time validation if validation has been triggered by the add button
                     if (!validationTriggered) return;

                     if (e.target.closest('.add-responsibility') ||
                         e.target.closest('.add-item') ||
                         e.target.closest('.remove-item') ||
                         e.target.closest('#add_method') ||
                         e.target.closest('.remove-method') ||
                         e.target.closest('.add-detail') ||
                         e.target.closest('.remove-detail') ||
                         e.target.closest('#add_monitoring_row') ||
                         e.target.closest('.remove-monitoring-row') ||
                         e.target.closest('#add_workplan_row') ||
                         e.target.closest('.remove-workplan-row')) {

                         // Wait a bit for DOM to update
                         setTimeout(function() {
                             // Revalidate the affected section
                             const section = e.target.closest('.section');
                             if (section) {
                                 validateSectionByIndex(Array.from(document.querySelectorAll('.section')).indexOf(section) + 1);
                             }
                         }, 100);
                     }
                 });
             });

             // Helper function to validate a specific section by its index
             function validateSectionByIndex(index) {
                 switch (index) {
                     case 1:
                         validateBasicInfoSectionWithFeedback();
                         break;
                     case 2:
                         validateProjectPersonnelSectionWithFeedback();
                         break;
                     case 3:
                         validateParticipantsSectionWithFeedback();
                         break;
                     case 4:
                         validateRationaleSectionWithFeedback();
                         break;
                     case 5:
                         validateObjectivesSectionWithFeedback();
                         break;
                     case 6:
                         validateDescriptionSectionWithFeedback();
                         break;
                     case 7:
                         validateMethodsSectionWithFeedback();
                         break;
                     case 8:
                         validateMaterialsSectionWithFeedback();
                         break;
                     case 9:
                         validateWorkPlanSectionWithFeedback();
                         break;
                     case 10:
                         validateFinancialSectionWithFeedback();
                         break;
                     case 11:
                         validateMonitoringSectionWithFeedback();
                         break;
                     case 12:
                         validateSustainabilitySectionWithFeedback();
                         break;
                 }
             }

             // Update the visual status of a section
             function updateSectionStatus(section, isComplete) {
                 // Check if section is a string (section title) or DOM element
                 if (typeof section === 'string') {
                     // If it's a string, find the section by its heading text
                     const sectionTitle = section;
                     const allSections = document.querySelectorAll('.section');
                     let foundSection = null;

                     // Search for the section with matching heading text
                     allSections.forEach(sec => {
                         const heading = sec.querySelector('h5');
                         if (heading && heading.textContent.includes(sectionTitle)) {
                             foundSection = sec;
                         }
                     });

                     // Use the found section if available, otherwise log an error
                     if (foundSection) {
                         section = foundSection;
                     } else {
                         console.error(`Section with title "${sectionTitle}" not found`);
                         return; // Exit if section not found
                     }
                 }

                 // Now section should be a DOM element
                 if (section && section.classList) {
                     if (isComplete) {
                         section.classList.add('complete');
                         section.classList.remove('incomplete');
                     } else {
                         section.classList.remove('complete');
                         // Only add incomplete class if validation has been triggered
                         if (validationTriggered) {
                             section.classList.add('incomplete');
                         }
                     }
                 } else {
                     console.error('Invalid section element provided to updateSectionStatus', section);
                 }
             }

             // Add special handling for problematic elements in the DOMContentLoaded event
             document.addEventListener('DOMContentLoaded', function() {
                 // ... existing code ...

                 // Special handling for problematic fields that don't clear validation properly
                 const problematicFields = [
                     // Textareas
                     document.getElementById('rationale'),
                     document.getElementById('objectives'),
                     document.getElementById('description'),
                     document.getElementById('sustainability_plan'),
                     // Dropdowns
                     document.getElementById('year'),
                     document.getElementById('quarter'),
                     document.getElementById('activity_title'),
                     document.getElementById('mode_of_delivery')
                 ];

                 // Add specific event listeners to these problematic fields
                 problematicFields.forEach(field => {
                     if (!field) return; // Skip if field doesn't exist

                     // For dropdowns (select elements)
                     if (field.tagName === 'SELECT') {
                         field.addEventListener('change', function() {
                             if (validationTriggered && this.value) {
                                 console.log('Dropdown value changed:', this.id, this.value);
                                 this.classList.remove('field-invalid');

                                 // Re-validate the section
                                 const section = this.closest('.section');
                                 if (section) {
                                     validateSectionByIndex(Array.from(document.querySelectorAll('.section')).indexOf(section) + 1);
                                 }
                             }
                         });
                     }
                     // For textareas
                     else if (field.tagName === 'TEXTAREA') {
                         // Add both input and change events for better coverage
                         ['input', 'change', 'blur'].forEach(eventType => {
                             field.addEventListener(eventType, function() {
                                 if (validationTriggered && this.value.trim()) {
                                     console.log('Textarea value changed:', this.id, this.value);
                                     this.classList.remove('field-invalid');

                                     // Re-validate the section
                                     const section = this.closest('.section');
                                     if (section) {
                                         validateSectionByIndex(Array.from(document.querySelectorAll('.section')).indexOf(section) + 1);
                                     }
                                 }
                             });
                         });
                     }
                 });

                 // Override the validation functions for these specific sections to ensure they work properly
                 window.originalValidateRationaleSectionWithFeedback = validateRationaleSectionWithFeedback;
                 window.validateRationaleSectionWithFeedback = function() {
                     const result = window.originalValidateRationaleSectionWithFeedback();
                     const rationale = document.getElementById('rationale');
                     if (rationale && rationale.value.trim()) {
                         rationale.classList.remove('field-invalid');
                         const section = rationale.closest('.section');
                         if (section) {
                             section.classList.remove('incomplete');
                             section.classList.add('complete');
                         }
                         return true;
                     }
                     return result;
                 };

                 window.originalValidateObjectivesSectionWithFeedback = validateObjectivesSectionWithFeedback;
                 window.validateObjectivesSectionWithFeedback = function() {
                     const result = window.originalValidateObjectivesSectionWithFeedback();
                     const objectives = document.getElementById('objectives');
                     if (objectives && objectives.value.trim()) {
                         objectives.classList.remove('field-invalid');
                         // We still need to check specific objectives before making the section complete
                         const specificObjectives = document.querySelectorAll('[name="specific_objectives[]"]');
                         const hasSpecificObjectives = specificObjectives.length > 0 &&
                             Array.from(specificObjectives).some(input => input.value.trim() !== '');

                         if (hasSpecificObjectives) {
                             const section = objectives.closest('.section');
                             if (section) {
                                 section.classList.remove('incomplete');
                                 section.classList.add('complete');
                             }
                             return true;
                         }
                     }
                     return result;
                 };

                 window.originalValidateDescriptionSectionWithFeedback = validateDescriptionSectionWithFeedback;
                 window.validateDescriptionSectionWithFeedback = function() {
                     const result = window.originalValidateDescriptionSectionWithFeedback();
                     const description = document.getElementById('description');
                     if (description && description.value.trim()) {
                         description.classList.remove('field-invalid');
                         // We still need to check strategies before making the section complete
                         const strategies = document.querySelectorAll('[name="strategies[]"]');
                         const hasStrategies = strategies.length > 0 &&
                             Array.from(strategies).some(input => input.value.trim() !== '');

                         if (hasStrategies) {
                             const section = description.closest('.section');
                             if (section) {
                                 section.classList.remove('incomplete');
                                 section.classList.add('complete');
                             }
                             return true;
                         }
                     }
                     return result;
                 };
             });

             // Replace the special handling code with a more aggressive approach
             document.addEventListener('DOMContentLoaded', function() {
                 // ... existing code ...

                 // Use a more direct approach to fix the problematic fields
                 setTimeout(function() {
                     // Fix dropdown lists in Basic Information section
                     const dropdowns = ['year', 'quarter', 'activity_title', 'mode_of_delivery'];
                     dropdowns.forEach(id => {
                         const dropdown = document.getElementById(id);
                         if (dropdown) {
                             // Use the select element's native onchange event
                             dropdown.onchange = function() {
                                 if (this.value) {
                                     // Force remove all validation styling directly
                                     this.classList.remove('field-invalid');
                                     this.style.removeProperty('background-image');
                                     this.style.borderColor = '';
                                     this.style.paddingRight = '';

                                     // Remove error messages
                                     const errorMsg = this.parentNode.querySelector('.invalid-feedback');
                                     if (errorMsg) errorMsg.remove();

                                     // Force revalidate the Basic Info section
                                     validateBasicInfoSectionWithFeedback();
                                 }
                             };

                             // Also add immediate check if it already has a value
                             if (dropdown.value) {
                                 dropdown.classList.remove('field-invalid');
                                 dropdown.style.removeProperty('background-image');
                                 dropdown.style.borderColor = '';
                                 dropdown.style.paddingRight = '';
                             }
                         }
                     });

                     // Fix textareas in various sections
                     const textareas = ['rationale', 'objectives', 'description'];
                     textareas.forEach(id => {
                         const textarea = document.getElementById(id);
                         if (textarea) {
                             // Use direct event binding for input event
                             textarea.oninput = function() {
                                 if (this.value.trim()) {
                                     // Force remove all validation styling directly
                                     this.classList.remove('field-invalid');
                                     this.style.removeProperty('background-image');
                                     this.style.borderColor = '';
                                     this.style.paddingRight = '';

                                     // Remove error messages
                                     const errorMsg = this.parentNode.querySelector('.invalid-feedback');
                                     if (errorMsg) errorMsg.remove();

                                     // Force revalidate the section
                                     const section = this.closest('.section');
                                     if (section) {
                                         const sectionIndex = Array.from(document.querySelectorAll('.section')).indexOf(section) + 1;
                                         validateSectionByIndex(sectionIndex);
                                     }
                                 }
                             };

                             // Also handle keyup event with more aggressive cleanup
                             textarea.onkeyup = function() {
                                 if (this.value.trim()) {
                                     // More aggressive styling reset
                                     this.classList.remove('field-invalid', 'is-invalid');
                                     this.classList.add('is-valid'); // Temporarily add valid class
                                     this.style.removeProperty('background-image');
                                     this.style.borderColor = '';
                                     this.style.paddingRight = '';

                                     // Remove any error styling that might have been added inline
                                     setTimeout(() => {
                                         this.classList.remove('is-valid'); // Remove temporary valid class
                                         this.style.removeProperty('background-image');
                                         this.style.borderColor = '';
                                         this.style.paddingRight = '';
                                     }, 50);
                                 }
                             };

                             // Also check immediately if it already has content
                             if (textarea.value.trim()) {
                                 textarea.classList.remove('field-invalid');
                                 textarea.style.removeProperty('background-image');
                                 textarea.style.borderColor = '';
                                 textarea.style.paddingRight = '';
                             }
                         }
                     });

                     // Add a more aggressive global input handler
                     document.querySelectorAll('input, textarea, select').forEach(field => {
                         field.addEventListener('input', function() {
                             if ((this.tagName === 'SELECT' && this.value) ||
                                 ((this.tagName === 'INPUT' || this.tagName === 'TEXTAREA') && this.value.trim())) {

                                 // Complete reset of styling
                                 this.classList.remove('field-invalid', 'is-invalid');
                                 this.style.removeProperty('background-image');
                                 this.style.borderColor = '';
                                 this.style.paddingRight = '';

                                 // Remove error messages
                                 const errorMsg = this.parentNode.querySelector('.invalid-feedback');
                                 if (errorMsg) errorMsg.remove();
                             }
                         });
                     });

                     // Add a click handler on the body to force remove styles
                     document.body.addEventListener('click', function() {
                         if (validationTriggered) {
                             document.querySelectorAll('input, textarea, select').forEach(field => {
                                 if ((field.tagName === 'SELECT' && field.value) ||
                                     ((field.tagName === 'INPUT' || field.tagName === 'TEXTAREA') && field.value.trim())) {

                                     // Force reset styling
                                     field.classList.remove('field-invalid', 'is-invalid');
                                     field.style.removeProperty('background-image');
                                     field.style.borderColor = '';
                                     field.style.paddingRight = '';
                                 }
                             });
                         }
                     });
                 }, 1000); // Wait for a second to ensure all other scripts have loaded

                 // Create a MutationObserver to watch for dynamically added error messages
                 const bodyObserver = new MutationObserver(mutations => {
                     if (validationTriggered) {
                         // Check if any inputs have values but still show error styling
                         document.querySelectorAll('input.field-invalid, textarea.field-invalid, select.field-invalid').forEach(field => {
                             if ((field.tagName === 'SELECT' && field.value) ||
                                 ((field.tagName === 'INPUT' || field.tagName === 'TEXTAREA') && field.value.trim())) {
                                 // Input has a value but still shows error - remove error styling
                                 field.classList.remove('field-invalid');

                                 // Remove error messages
                                 const errorMsg = field.parentNode.querySelector('.invalid-feedback');
                                 if (errorMsg) errorMsg.remove();

                                 // Revalidate the section
                                 const section = field.closest('.section');
                                 if (section) {
                                     const sectionIndex = Array.from(document.querySelectorAll('.section')).indexOf(section) + 1;
                                     validateSectionByIndex(sectionIndex);
                                 }
                             }
                         });
                     }
                 });

                 // Start observing the body for changes
                 bodyObserver.observe(document.body, {
                     childList: true,
                     subtree: true,
                     attributes: true,
                     attributeFilter: ['class']
                 });

                 // Override validation functions with direct access approaches
                 validateBasicInfoSectionWithFeedback = function() {
                     const section = document.querySelector('.section:nth-of-type(1)');
                     const requiredFields = ['year', 'quarter', 'activity_title', 'mode_of_delivery'];

                     let isComplete = true;

                     // Check required fields
                     requiredFields.forEach(fieldId => {
                         const field = document.getElementById(fieldId);
                         if (!field || !field.value.trim()) {
                             isComplete = false;
                             // Only add invalid class if validation has been triggered
                             if (validationTriggered) {
                                 field.classList.add('field-invalid');
                             }
                         } else {
                             // Always remove invalid class if field has a value
                             field.classList.remove('field-invalid');
                         }
                     });

                     // Update section status
                     if (isComplete) {
                         section.classList.add('complete');
                         section.classList.remove('incomplete');
                     } else if (validationTriggered) {
                         section.classList.remove('complete');
                         section.classList.add('incomplete');
                     }

                     return isComplete;
                 };

                 validateRationaleSectionWithFeedback = function() {
                     const section = document.querySelector('.section:nth-of-type(4)');
                     const rationale = document.getElementById('rationale');

                     let isComplete = rationale && rationale.value.trim() !== '';

                     if (!isComplete && validationTriggered) {
                         rationale.classList.add('field-invalid');
                         section.classList.add('incomplete');
                         section.classList.remove('complete');
                     } else if (isComplete) {
                         rationale.classList.remove('field-invalid');
                         section.classList.remove('incomplete');
                         section.classList.add('complete');
                     }

                     return isComplete;
                 };

                 validateObjectivesSectionWithFeedback = function() {
                     const section = document.querySelector('.section:nth-of-type(5)');
                     const objectives = document.getElementById('objectives');
                     const specificObjectives = document.querySelectorAll('[name="specific_objectives[]"]');

                     let hasObjectives = objectives && objectives.value.trim() !== '';
                     let hasSpecificObjectives = specificObjectives.length > 0 &&
                         Array.from(specificObjectives).some(input => input.value.trim() !== '');

                     let isComplete = hasObjectives && hasSpecificObjectives;

                     // Update field styling
                     if (!hasObjectives && validationTriggered) {
                         objectives.classList.add('field-invalid');
                     } else {
                         objectives.classList.remove('field-invalid');
                     }

                     if (!hasSpecificObjectives && validationTriggered) {
                         specificObjectives.forEach(input => input.classList.add('field-invalid'));
                     } else {
                         specificObjectives.forEach(input => input.classList.remove('field-invalid'));
                     }

                     // Update section status
                     if (isComplete) {
                         section.classList.add('complete');
                         section.classList.remove('incomplete');
                     } else if (validationTriggered) {
                         section.classList.remove('complete');
                         section.classList.add('incomplete');
                     }

                     return isComplete;
                 };

                 validateDescriptionSectionWithFeedback = function() {
                     const section = document.querySelector('.section:nth-of-type(6)');
                     const description = document.getElementById('description');
                     const strategies = document.querySelectorAll('[name="strategies[]"]');

                     let hasDescription = description && description.value.trim() !== '';
                     let hasStrategies = strategies.length > 0 &&
                         Array.from(strategies).some(input => input.value.trim() !== '');

                     let isComplete = hasDescription && hasStrategies;

                     // Update field styling
                     if (!hasDescription && validationTriggered) {
                         description.classList.add('field-invalid');
                     } else {
                         description.classList.remove('field-invalid');
                     }

                     if (!hasStrategies && validationTriggered) {
                         strategies.forEach(input => input.classList.add('field-invalid'));
                     } else {
                         strategies.forEach(input => input.classList.remove('field-invalid'));
                     }

                     // Update section status
                     if (isComplete) {
                         section.classList.add('complete');
                         section.classList.remove('incomplete');
                     } else if (validationTriggered) {
                         section.classList.remove('complete');
                         section.classList.add('incomplete');
                     }

                     return isComplete;
                 };
             });

             // Add workplan checkbox handling to the DOM ready event
             document.addEventListener('DOMContentLoaded', function() {
                 // ... existing code remains ...

                 // Fix workplan checkboxes validation
                 setTimeout(function() {
                     // Add event handlers for workplan checkboxes
                     document.addEventListener('change', function(e) {
                         if (e.target.type === 'checkbox' && e.target.name && e.target.name.includes('workplan')) {
                             const row = e.target.closest('tr');
                             if (row) {
                                 const checkboxes = row.querySelectorAll('input[type="checkbox"]');
                                 const hasChecked = Array.from(checkboxes).some(cb => cb.checked);

                                 if (hasChecked) {
                                     // Clear error styling from all checkboxes in this row
                                     checkboxes.forEach(checkbox => {
                                         const cell = checkbox.closest('td');
                                         if (cell) {
                                             cell.classList.remove('field-invalid');
                                             cell.style.removeProperty('background-color');
                                             cell.style.borderColor = '';
                                         }
                                     });

                                     // Re-validate the workplan section
                                     validateWorkPlanSectionWithFeedback();
                                 }
                             }
                         }
                     });

                     // Add CSS for invalid cells
                     const style = document.createElement('style');
                     style.textContent = `
            td.field-invalid {
                background-color: rgba(220, 53, 69, 0.1) !important;
                border: 1px solid #dc3545 !important;
            }
        `;
                     document.head.appendChild(style);

                     // Override the workplan validation function
                     validateWorkPlanSectionWithFeedback = function() {
                         const section = document.querySelector('.section:nth-of-type(9)');
                         const workplanRows = document.querySelectorAll('#workplan_table tbody tr');

                         let isComplete = true;

                         if (workplanRows.length === 0) {
                             isComplete = false;
                         } else {
                             workplanRows.forEach(row => {
                                 const activityInput = row.querySelector('input[name*="workplan"][name*="activity"]');
                                 const checkboxes = row.querySelectorAll('input[type="checkbox"]');

                                 const hasActivity = activityInput && activityInput.value.trim() !== '';
                                 const hasCheckedDay = Array.from(checkboxes).some(checkbox => checkbox.checked);

                                 // Clear validation if fields are filled
                                 if (hasActivity) {
                                     activityInput.classList.remove('field-invalid');
                                     activityInput.style.removeProperty('background-image');
                                     activityInput.style.borderColor = '';
                                     activityInput.style.paddingRight = '';
                                 }

                                 if (hasCheckedDay) {
                                     checkboxes.forEach(checkbox => {
                                         const cell = checkbox.closest('td');
                                         if (cell) {
                                             cell.classList.remove('field-invalid');
                                             cell.style.removeProperty('background-color');
                                             cell.style.borderColor = '';
                                         }
                                     });
                                 }

                                 // Add validation if fields are empty and validation is triggered
                                 if (!hasActivity && validationTriggered) {
                                     activityInput.classList.add('field-invalid');
                                     isComplete = false;
                                 }

                                 if (!hasCheckedDay && validationTriggered) {
                                     checkboxes.forEach(checkbox => {
                                         const cell = checkbox.closest('td');
                                         if (cell) {
                                             cell.classList.add('field-invalid');
                                         }
                                     });
                                     isComplete = false;
                                 }
                             });
                         }

                         // Update section status
                         if (isComplete) {
                             section.classList.add('complete');
                             section.classList.remove('incomplete');
                         } else if (validationTriggered) {
                             section.classList.remove('complete');
                             section.classList.add('incomplete');
                         }

                         return isComplete;
                     };
                 }, 1000);
             });

             document.addEventListener('DOMContentLoaded', function() {
                 // Verify that all required fields exist in the form
                 verifyRequiredFieldsExist();

                 // Rest of your DOMContentLoaded code...
             });

             // Function to verify all required field elements exist in the DOM
             function verifyRequiredFieldsExist() {
                 // List of fields required by the server
                 const requiredFieldIds = [
                     'activity_title',
                     'mode_of_delivery',
                     'partner_office',
                     'rationale',
                     'objectives', // This maps to general_objectives on the server
                     'description',
                     'budget_breakdown',
                     'sustainability_plan',
                     'start_date',
                     'end_date'
                 ];

                 // Check each field
                 let missingFields = [];
                 requiredFieldIds.forEach(fieldId => {
                     const element = document.getElementById(fieldId);
                     if (!element) {
                         console.error(`Required field element missing: ${fieldId}`);
                         missingFields.push(fieldId);
                     } else {
                         console.log(`Found required field: ${fieldId} (type: ${element.tagName}, name: ${element.name || 'unnamed'})`);
                     }
                 });

                 if (missingFields.length > 0) {
                     console.warn('Missing required field elements:', missingFields);
                 } else {
                     console.log('All required field elements found in the DOM');
                 }

                 // Check for possible naming mismatches between IDs and actual field names
                 const formElements = document.querySelectorAll('#ppasForm input, #ppasForm textarea, #ppasForm select');
                 console.log('Form elements found:', formElements.length);

                 // Log element IDs and names for debugging
                 formElements.forEach(el => {
                     if (el.id && el.name && el.id !== el.name) {
                         console.log(`Field name mismatch - id: ${el.id}, name: ${el.name}`);
                     }
                 });

                 // Special note about the field mapping
                 console.log("NOTE: 'objectives' field will be mapped to 'general_objectives' when sent to the server");
             }

             // Collect workplan data
             function getWorkplanData() {
                 console.log("Getting workplan data using direct form elements");

                 // Create an object to store activities by index
                 const workplanByIndex = {};

                 // First, collect all workplan inputs directly using their names
                 const allInputs = document.querySelectorAll('input[name^="workplan"]');
                 console.log(`Found ${allInputs.length} workplan-related inputs`);

                 allInputs.forEach(input => {
                     // Extract the index and property from the name
                     // Format: workplan[0][activity] or workplan[0][days][0]
                     const nameMatch = input.name.match(/workplan\[(\d+)\]\[(\w+)(?:\]\[(\d+)\])?/);
                     if (!nameMatch) return;

                     const [, index, property, dayIndex] = nameMatch;

                     // Create the activity object if it doesn't exist
                     if (!workplanByIndex[index]) {
                         workplanByIndex[index] = {
                             activity: '',
                             days: []
                         };
                     }

                     if (property === 'activity') {
                         // Activity name
                         if (input.value.trim()) {
                             // Remove any "activity:" prefix
                             workplanByIndex[index].activity = removePrefix(input.value.trim(), ["activity:", "activity :"]);
                         }
                     } else if (property === 'days' && dayIndex !== undefined) {
                         // Day checkbox
                         if (input.type === 'checkbox') {
                             // Make sure days array is big enough
                             while (workplanByIndex[index].days.length <= parseInt(dayIndex)) {
                                 workplanByIndex[index].days.push('0');
                             }
                             // Set the day value based on checkbox
                             workplanByIndex[index].days[parseInt(dayIndex)] = input.checked ? '1' : '0';
                         }
                     }
                 });

                 // Convert the object to an array, filtering out empty activities
                 // and changing format to [activity, dates] as requested
                 const workplan = Object.values(workplanByIndex)
                     .filter(item => item.activity !== '')
                     .map(item => {
                         // Get start and end date to determine date range
                         const startDate = document.querySelector('#start_date').value;
                         const endDate = document.querySelector('#end_date').value;
                         const checkedDays = [];

                         if (startDate && endDate) {
                             try {
                                 const start = new Date(startDate);
                                 const end = new Date(endDate);
                                 const dayCount = Math.floor((end - start) / (24 * 60 * 60 * 1000)) + 1;

                                 for (let i = 0; i < dayCount; i++) {
                                     const currentDate = new Date(start);
                                     currentDate.setDate(start.getDate() + i);

                                     if (item.days[i] === '1') {
                                         checkedDays.push(currentDate.toISOString().split('T')[0]);
                                     }
                                 }
                             } catch (e) {
                                 console.error("Error processing dates:", e);
                             }
                         }

                         // Return array format [activity, dates] as requested
                         return [
                             item.activity,
                             checkedDays
                         ];
                     });

                 console.log(`Final workplan data has ${workplan.length} activities`);
                 console.log("Workplan data:", workplan);

                 return workplan;
             }

             // Collect responsibilities from a specific personnel type container
             function getResponsibilities(type) {
                 // Map the type to the correct container ID
                 const containerMap = {
                     'project-leader': 'project_leader_responsibilities_container',
                     'assistant-leader': 'assistant_project_leader_responsibilities_container',
                     'staff': 'project_staff_responsibilities_container'
                 };

                 const containerId = containerMap[type];
                 if (!containerId) {
                     console.error(`No container mapping for ${type}`);
                     return [];
                 }

                 const container = document.getElementById(containerId);
                 if (!container) {
                     console.error(`Container not found for ${containerId}`);
                     return [];
                 }

                 const items = container.querySelectorAll('input[name*="responsibilities"]');

                 const responsibilities = [];
                 items.forEach(input => {
                     if (input && input.value.trim() !== '') {
                         responsibilities.push(input.value.trim());
                     }
                 });

                 console.log(`${type} responsibilities:`, responsibilities);
                 return responsibilities;
             }

             // Collect methods with their details
             function getMethodsWithDetails() {
                 const methodItems = document.querySelectorAll('.method-item');
                 const methods = [];

                 methodItems.forEach((item, index) => {
                     const nameInput = item.querySelector('input[name*="[name]"]');
                     if (!nameInput || nameInput.value.trim() === '') return;

                     // Remove any "name:" prefix
                     const methodName = removePrefix(nameInput.value.trim(), ["name:", "name :"]);
                     const detailsContainer = item.querySelector('.activity-details-container');
                     const detailInputs = detailsContainer ? detailsContainer.querySelectorAll('input[name*="[details]"]') : [];

                     const details = [];
                     detailInputs.forEach(input => {
                         if (input && input.value.trim() !== '') {
                             // Remove any "details:" prefix
                             details.push(removePrefix(input.value.trim(), ["details:", "details :", "detail:", "detail :"]));
                         }
                     });

                     // Changed to array format instead of object with name/details keys
                     methods.push([
                         methodName,
                         details
                     ]);
                 });

                 console.log("Methods with details:", methods);
                 return methods;
             }

             // Collect monitoring data
             function getMonitoringData() {
                 const monitoringItems = document.querySelectorAll('.monitoring-item');
                 const monitoring = [];

                 monitoringItems.forEach((item, index) => {
                     // Get values without adding titles
                     let objectives = item.querySelector('textarea[name*="[objectives]"]')?.value?.trim() || '';
                     let performance_indicators = item.querySelector('textarea[name*="[performance_indicators]"]')?.value?.trim() || '';
                     let baseline_data = item.querySelector('textarea[name*="[baseline_data]"]')?.value?.trim() || '';
                     let performance_target = item.querySelector('textarea[name*="[performance_target]"]')?.value?.trim() || '';
                     let data_source = item.querySelector('textarea[name*="[data_source]"]')?.value?.trim() || '';
                     let collection_method = item.querySelector('textarea[name*="[collection_method]"]')?.value?.trim() || '';
                     let frequency = item.querySelector('textarea[name*="[frequency]"]')?.value?.trim() || '';
                     let responsible = item.querySelector('textarea[name*="[responsible]"]')?.value?.trim() || '';

                     // Remove any title prefixes like "objectives:", "frequency:", etc.
                     objectives = removePrefix(objectives, ["objectives:", "objectives :"]);
                     performance_indicators = removePrefix(performance_indicators, ["performance indicators:", "performance_indicators:", "performance indicators :"]);
                     baseline_data = removePrefix(baseline_data, ["baseline data:", "baseline_data:", "baseline data :"]);
                     performance_target = removePrefix(performance_target, ["performance target:", "performance_target:", "performance target :"]);
                     data_source = removePrefix(data_source, ["data source:", "data_source:", "data source :"]);
                     collection_method = removePrefix(collection_method, ["collection method:", "collection_method:", "collection method :"]);
                     frequency = removePrefix(frequency, ["frequency:", "frequency :"]);
                     responsible = removePrefix(responsible, ["responsible:", "responsible office:", "office/person involved:", "office/person:"]);

                     // Only add if at least objectives and performance indicators are filled
                     if (objectives && performance_indicators) {
                         // Changed to array format in the requested order
                         monitoring.push([
                             objectives,
                             performance_indicators,
                             baseline_data,
                             performance_target,
                             data_source,
                             collection_method,
                             frequency,
                             responsible
                         ]);
                     }
                 });

                 console.log("Monitoring data:", monitoring);
                 return monitoring;
             }

             // Helper function to remove prefixes from strings
             function removePrefix(text, prefixes) {
                 if (!text) return text;

                 // Make case insensitive comparison
                 text = text.trim();
                 const lowerText = text.toLowerCase();

                 for (const prefix of prefixes) {
                     const lowerPrefix = prefix.toLowerCase();
                     if (lowerText.startsWith(lowerPrefix)) {
                         return text.substring(prefix.length).trim();
                     }
                 }

                 return text;
             }

             // Call this function when page loads
             document.addEventListener('DOMContentLoaded', function() {
                 // Log current state
                 console.log("DOM Content Loaded - GAD Proposal Form");
                 console.log("Current campus: <?php echo $_SESSION['username']; ?>");

                 // Check if user is Central - apply read-only mode
                 const isCentral = <?php echo $isCentral ? 'true' : 'false'; ?>;
                 if (isCentral) {
                     console.log("Central user detected - applying read-only mode");
                     applyCentralReadOnlyMode();
                 }

                 // Fetch years from database
                 fetchYearsFromDatabase();

                 // Add event listeners for dependent dropdowns
                 document.getElementById('year').addEventListener('change', function() {
                     const yearValue = this.value;
                     console.log("Year changed to:", yearValue);

                     const quarterSelect = document.getElementById('quarter');
                     const activitySelect = document.getElementById('activity_title');

                     // Enable quarter selection
                     quarterSelect.disabled = false;

                     // Reset quarter selection to default
                     quarterSelect.value = '';

                     // Reset and disable activity selection
                     activitySelect.disabled = true;
                     activitySelect.value = '';

                     // Reset form fields related to activity
                     document.getElementById('project').value = '';
                     document.getElementById('program').value = '';
                     document.getElementById('venue').value = '';
                     document.getElementById('start_date').value = '';
                     document.getElementById('end_date').value = '';
                     document.getElementById('start_time').value = '';
                     document.getElementById('end_time').value = '';
                     document.getElementById('project_leaders').value = '';
                     document.getElementById('assistant_project_leaders').value = '';
                     document.getElementById('project_staff').value = '';

                     // Reset workplan table
                     updateWorkPlanTable();
                 });

                 document.getElementById('quarter').addEventListener('change', function() {
                     const yearSelect = document.getElementById('year');
                     const quarterSelect = document.getElementById('quarter');
                     const yearValue = yearSelect.value;
                     const quarterValue = quarterSelect.value;

                     console.log("Quarter changed to:", quarterValue, "for year:", yearValue);

                     const activitySelect = document.getElementById('activity_title');

                     // Enable activity selection
                     activitySelect.disabled = false;

                     // Reset activity selection
                     activitySelect.value = '';

                     // Reset form fields related to activity
                     document.getElementById('project').value = '';
                     document.getElementById('program').value = '';
                     document.getElementById('venue').value = '';
                     document.getElementById('start_date').value = '';
                     document.getElementById('end_date').value = '';
                     document.getElementById('start_time').value = '';
                     document.getElementById('end_time').value = '';

                     // Fetch activities based on year and quarter
                     fetchActivities(yearValue, quarterValue);
                 });
             });

             // Function to apply read-only mode for Central users
             function applyCentralReadOnlyMode() {
                 // Disable all form input elements
                 const formInputs = document.querySelectorAll('input, select, textarea');
                 formInputs.forEach(input => {
                     // Skip the viewBtn (since we need to keep that enabled)
                     if (input.closest('.viewBtn') || input.id === 'viewBtn') {
                         return;
                     }

                     // Disable the input
                     input.disabled = true;
                     input.classList.add('central-disabled');
                 });

                 // Specifically disable addBtn, editBtn, deleteBtn
                 const buttonsToDisable = document.querySelectorAll('.addBtn, .editBtn, .deleteBtn, button[id="addBtn"], button[id="editBtn"], button[id="deleteBtn"]');
                 buttonsToDisable.forEach(button => {
                     button.disabled = true;
                     button.classList.add('central-disabled');
                 });

                 // Disable all "add" and "remove" buttons in the form
                 const actionButtons = document.querySelectorAll('.add-leader, .remove-leader, .add-assistant, .remove-assistant, .add-staff, .remove-staff, .add-method, .remove-method, .add-detail, .remove-detail, .add-item, .remove-item, .add-workplan-row, .remove-workplan-row, .add-monitoring-row, .remove-monitoring-row, .add-responsibility, .remove-responsibility, #add_workplan_row, #add_monitoring_row, #add_method, button[data-add-type="activity"]');
                 actionButtons.forEach(button => {
                     button.disabled = true;
                     button.classList.add('central-disabled');
                 });

                 // Add a notification at the top of the form
                 const formContainer = document.querySelector('.card-body');
                 if (formContainer) {
                     const notification = document.createElement('div');
                     notification.className = 'alert alert-info mb-4 d-flex justify-content-between align-items-center';
                     notification.style.backgroundColor = 'rgba(106, 27, 154, 0.1)';
                     notification.style.borderColor = 'rgba(106, 27, 154, 0.2)';
                     notification.style.color = 'var(--accent-color)';

                     // Create the message container
                     const messageContainer = document.createElement('div');
                     messageContainer.innerHTML = '<i class="fas fa-info-circle me-2"></i> <strong>Read-Only Mode:</strong> As a Central user, you can view but not modify the data.';

                     // Create the view button
                     const viewButton = document.createElement('button');
                     viewButton.className = 'btn btn-sm';
                     viewButton.style.backgroundColor = 'rgba(106, 27, 154, 0.1)';
                     viewButton.style.borderColor = 'rgba(106, 27, 154, 0.2)';
                     viewButton.style.color = 'var(--accent-color)';
                     viewButton.style.transition = 'all 0.2s ease';
                     viewButton.innerHTML = '<i class="fas fa-arrow-down me-1"></i> View';

                     // Add hover effect
                     viewButton.addEventListener('mouseover', function() {
                         this.style.backgroundColor = 'var(--accent-color)';
                         this.style.color = 'white';
                     });

                     viewButton.addEventListener('mouseout', function() {
                         this.style.backgroundColor = 'rgba(106, 27, 154, 0.1)';
                         this.style.color = 'var(--accent-color)';
                     });

                     // Add click handler to scroll to bottom of main card
                     viewButton.addEventListener('click', function() {
                         const mainCard = document.querySelector('.card');
                         if (mainCard) {
                             mainCard.scrollIntoView({
                                 behavior: 'smooth',
                                 block: 'end'
                             });
                         }
                     });

                     // Append elements to notification
                     notification.appendChild(messageContainer);
                     notification.appendChild(viewButton);

                     formContainer.insertBefore(notification, formContainer.firstChild);
                 }
             }

             // Function to fetch activities based on year and quarter
         </script>

         <!-- Modal for viewing GAD proposals -->
         <div class="modal-overlay" id="gadProposalModalOverlay" style="display: none;" onclick="closeGadProposalModal()">
             <div class="gad-proposal-modal" onclick="event.stopPropagation()">
                 <div class="gad-proposal-modal-header">
                     <h3 id="gadProposalModalTitle">GAD Proposals</h3>
                     <button class="close-modal-btn" onclick="closeGadProposalModal()">
                         <i class="fas fa-times"></i>
                     </button>
                 </div>
                 <div class="gad-proposal-modal-body">
                     <!-- Filters -->
                     <div class="filter-container">
                         <div class="filter-row">
                             <div class="filter-item">
                                 <label for="filter-activity">Activity Name:</label>
                                 <input type="text" id="filter-activity" class="form-control" placeholder="Search activities..." onkeyup="loadGadProposalData()">
                             </div>
                             <div class="filter-item">
                                 <label for="filter-mode">Mode of Delivery:</label>
                                 <select id="filter-mode" class="form-control" onchange="loadGadProposalData()">
                                     <option value="">All Modes</option>
                                     <option value="Online">Online</option>
                                     <option value="Face-to-Face">Face-to-Face</option>
                                     <option value="Hybrid">Hybrid</option>
                                 </select>
                             </div>
                             <div class="filter-item">
                                 <label for="filter-campus">Campus:</label>
                                 <?php if (isset($_SESSION['username']) && $_SESSION['username'] === 'Central'): ?>
                                     <select id="filter-campus" class="form-control" onchange="loadGadProposalData();" style="pointer-events: auto; opacity: 1; background-color: var(--input-bg, #ffffff);">
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
                                     <input type="text" id="filter-campus" class="form-control" value="<?php echo $_SESSION['username']; ?>" readonly>
                                 <?php endif; ?>
                             </div>
                         </div>
                     </div>

                     <!-- Data Table -->
                     <div class="gad-proposal-table-wrapper">
                         <table class="gad-proposal-table">
                             <thead>
                                 <tr>
                                     <th>Activity Name</th>
                                     <th>Mode of Delivery</th>
                                     <th>Partner Office</th>
                                     <?php if (isset($_SESSION['username']) && $_SESSION['username'] === 'Central'): ?>
                                         <th>Campus</th>
                                     <?php endif; ?>
                                 </tr>
                             </thead>
                             <tbody id="gad-proposal-tbody">
                                 <!-- Data will be populated dynamically -->
                             </tbody>
                         </table>
                     </div>
                 </div>

                 <!-- Modal Footer with Pagination -->
                 <div class="gad-proposal-modal-footer">
                     <div class="pagination-container">
                         <nav aria-label="Page navigation">
                             <ul class="pagination" id="proposalsPagination">
                                 <!-- Pagination will be populated dynamically -->
                             </ul>
                         </nav>
                     </div>
                 </div>
             </div>
         </div>

         <style>
             /* Modal Styles */
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

             .gad-proposal-modal {
                 width: 90%;
                 max-width: 1200px;
                 height: 85vh;
                 max-height: 800px;
                 background-color: var(--card-bg, #ffffff);
                 border-radius: 15px;
                 box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                 display: flex;
                 flex-direction: column;
                 overflow: hidden;
                 border-top: 5px solid var(--accent-color, #6a1b9a);
                 transform: translateY(20px);
                 opacity: 0;
                 transition: transform 0.3s ease, opacity 0.3s ease;
             }

             .modal-overlay.active .gad-proposal-modal {
                 transform: translateY(0);
                 opacity: 1;
             }

             .gad-proposal-modal-header {
                 display: flex;
                 justify-content: space-between;
                 align-items: center;
                 padding: 1rem 1.5rem;
                 border-bottom: 1px solid var(--border-color, #dee2e6);
                 flex-shrink: 0;
             }

             .gad-proposal-modal-header h3 {
                 margin: 0;
                 color: var(--accent-color, #6a1b9a);
                 font-size: 1.25rem;
                 text-align: center;
                 flex-grow: 1;
             }

             .close-modal-btn {
                 background: transparent;
                 border: none;
                 color: var(--text-primary, #212529);
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
                 color: var(--accent-color, #6a1b9a);
             }

             .gad-proposal-modal-body {
                 padding: 1rem 1.5rem;
                 overflow-y: auto;
                 flex: 1;
                 min-height: 200px;
                 /* Hide scrollbar but maintain scroll functionality */
                 scrollbar-width: none;
                 /* Firefox */
                 -ms-overflow-style: none;
                 /* IE and Edge */
             }

             /* Hide scrollbar for Chrome, Safari and Opera */
             .gad-proposal-modal-body::-webkit-scrollbar {
                 display: none;
             }

             .gad-proposal-table-wrapper {
                 overflow-x: auto;
                 border-radius: 10px;
                 border: 1px solid var(--border-color, #dee2e6);
                 margin-bottom: 0;
                 height: calc(100% - 100px);
                 /* Reduced from 120px */
                 min-height: 200px;
                 /* Hide scrollbar but maintain scroll functionality */
                 scrollbar-width: none;
                 /* Firefox */
                 -ms-overflow-style: none;
                 /* IE and Edge */
             }

             /* Hide scrollbar for Chrome, Safari and Opera */
             .gad-proposal-table-wrapper::-webkit-scrollbar {
                 display: none;
             }

             .gad-proposal-modal-footer {
                 padding: 0.5rem;
                 border-top: 1px solid var(--border-color, #dee2e6);
                 background-color: var(--card-bg, #ffffff);
                 border-radius: 0 0 15px 15px;
                 flex-shrink: 0;
             }

             .filter-container {
                 margin-bottom: 0.75rem;
                 background-color: var(--bg-secondary, #e9ecef);
                 padding: 0.75rem;
                 border-radius: 10px;
             }

             .filter-row {
                 display: flex;
                 flex-wrap: wrap;
                 gap: 0.75rem;
             }

             .filter-item {
                 flex: 1;
                 min-width: 200px;
             }

             .filter-item label {
                 display: block;
                 margin-bottom: 0.25rem;
                 color: var(--text-primary, #212529);
                 font-weight: 500;
                 font-size: 0.9rem;
             }

             .gad-proposal-table {
                 width: 100%;
                 border-collapse: collapse;
             }

             .gad-proposal-table th,
             .gad-proposal-table td {
                 padding: 1rem;
                 text-align: left;
                 border-bottom: 1px solid var(--border-color, #dee2e6);
             }

             .gad-proposal-table th {
                 background-color: var(--accent-color, #6a1b9a);
                 color: white;
                 font-weight: 500;
                 position: sticky;
                 top: 0;
                 z-index: 10;
             }

             .gad-proposal-table tr:last-child td {
                 border-bottom: none;
             }

             .gad-proposal-table tr:hover {
                 background-color: var(--hover-color, rgba(106, 27, 154, 0.1));
             }

             /* Styles for different modal modes */
             .gad-proposal-table-wrapper.view-mode tr {
                 cursor: default;
             }

             /* Remove hover effect for view mode */
             .gad-proposal-table-wrapper.view-mode tr:hover {
                 background-color: transparent !important;
             }

             .gad-proposal-table-wrapper.edit-mode tr {
                 cursor: pointer;
             }

             .gad-proposal-table-wrapper.delete-mode tr {
                 cursor: pointer;
             }

             .gad-proposal-table-wrapper.delete-mode tr:hover {
                 background-color: rgba(220, 53, 69, 0.1) !important;
             }

             /* Pagination Styles */
             .pagination-container {
                 display: flex;
                 justify-content: center;
                 margin-top: 0.5rem;
                 margin-bottom: 0.25rem;
             }

             .pagination {
                 display: flex;
                 list-style: none;
                 padding: 0;
                 margin: 0;
                 gap: 5px;
             }

             .page-item {
                 margin: 0 2px;
             }

             .page-link {
                 display: flex;
                 align-items: center;
                 justify-content: center;
                 width: 36px;
                 height: 36px;
                 border-radius: 50%;
                 background-color: var(--bg-secondary, #e9ecef);
                 color: var(--text-primary, #212529);
                 text-decoration: none;
                 transition: all 0.2s;
                 border: 1px solid var(--border-color, #dee2e6);
                 cursor: pointer;
             }

             .page-link:hover {
                 background-color: var(--accent-color, #6a1b9a);
                 color: white;
             }

             .page-item.active .page-link {
                 background-color: var(--accent-color, #6a1b9a);
                 color: white;
                 border-color: var(--accent-color, #6a1b9a);
             }

             .page-item.disabled .page-link {
                 opacity: 0.5;
                 cursor: not-allowed;
             }

             .pagination-info {
                 text-align: center;
                 margin-top: 0.5rem;
                 font-size: 0.9rem;
                 color: var(--text-secondary, #6c757d);
             }

             /* Sweet Alert container */
             .swal-blur-container {
                 backdrop-filter: blur(10px);
                 -webkit-backdrop-filter: blur(10px);
             }

             /* Additional styling for Sweet Alert popups */
             .swal2-popup {
                 border-radius: 15px;
                 box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
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

         <script>
             // Global variables for pagination
             let currentPage = 1;
             let totalPages = 1;
             let proposalData = [];
             const rowsPerPage = 5;
             let currentModalMode = 'view'; // can be 'view', 'edit', or 'delete'
             let selectedProposalId = null;
             let isCentralUser = <?php echo isset($_SESSION['username']) && $_SESSION['username'] === 'Central' ? 'true' : 'false'; ?>;

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

             // Function to ensure the campus filter is interactive for Central users
             function enableCampusFilter() {
                 if (isCentralUser) {
                     const campusFilter = document.getElementById('filter-campus');
                     const activityFilter = document.getElementById('filter-activity');
                     const modeFilter = document.getElementById('filter-mode');

                     if (campusFilter) {
                         // Force-enable the campus filter for Central users
                         campusFilter.disabled = false;
                         campusFilter.readOnly = false;
                         campusFilter.style.pointerEvents = 'auto';
                         campusFilter.style.opacity = '1';
                         campusFilter.style.backgroundColor = 'var(--input-bg, #ffffff)';
                         campusFilter.style.color = 'var(--text-primary, #212529)';
                         campusFilter.classList.remove('central-disabled');
                         campusFilter.classList.remove('readonly');
                         campusFilter.classList.remove('bg-secondary-subtle');
                     }

                     if (activityFilter) {
                         // Enable the activity filter for Central users
                         activityFilter.disabled = false;
                         activityFilter.readOnly = false;
                         activityFilter.style.pointerEvents = 'auto';
                         activityFilter.style.opacity = '1';
                         activityFilter.style.backgroundColor = 'var(--input-bg, #ffffff)';
                         activityFilter.style.color = 'var(--text-primary, #212529)';
                         activityFilter.classList.remove('central-disabled');
                         activityFilter.classList.remove('readonly');
                         activityFilter.classList.remove('bg-secondary-subtle');
                     }

                     if (modeFilter) {
                         // Enable the mode filter for Central users
                         modeFilter.disabled = false;
                         modeFilter.readOnly = false;
                         modeFilter.style.pointerEvents = 'auto';
                         modeFilter.style.opacity = '1';
                         modeFilter.style.backgroundColor = 'var(--input-bg, #ffffff)';
                         modeFilter.style.color = 'var(--text-primary, #212529)';
                         modeFilter.classList.remove('central-disabled');
                         modeFilter.classList.remove('readonly');
                         modeFilter.classList.remove('bg-secondary-subtle');
                     }
                 }
             }

             // Function to open the GAD proposal modal
             function openGadProposalModal(mode = 'view') {
                 // Set the modal mode
                 currentModalMode = mode;

                 // Update the modal title based on mode
                 const modalTitle = document.getElementById('gadProposalModalTitle');
                 switch (mode) {
                     case 'edit':
                         modalTitle.textContent = 'Edit GAD Proposals';
                         break;
                     case 'delete':
                         modalTitle.textContent = 'Delete GAD Proposals';
                         break;
                     default:
                         modalTitle.textContent = 'View GAD Proposals';
                 }

                 const modalOverlay = document.getElementById('gadProposalModalOverlay');
                 modalOverlay.style.display = 'flex';

                 // Update table wrapper class based on mode
                 const tableWrapper = document.querySelector('.gad-proposal-table-wrapper');
                 tableWrapper.classList.remove('view-mode', 'edit-mode', 'delete-mode');
                 tableWrapper.classList.add(`${mode}-mode`);

                 // Trigger the CSS transition by adding the active class after a small delay
                 setTimeout(() => {
                     modalOverlay.classList.add('active');

                     // Ensure campus filter is interactive for Central users
                     enableCampusFilter();
                 }, 10);

                 document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal

                 // Load GAD proposal data
                 loadGadProposalData();
             }

             // Function to close the GAD proposal modal
             function closeGadProposalModal() {
                 const modalOverlay = document.getElementById('gadProposalModalOverlay');
                 modalOverlay.classList.remove('active');
                 // Wait for the transition to complete before hiding the element
                 setTimeout(() => {
                     modalOverlay.style.display = 'none';
                     document.body.style.overflow = 'auto'; // Restore scrolling
                 }, 300); // Match this to the transition duration in CSS
             }

             // Add a debounce function to prevent excessive filtering
             function debounce(func, wait) {
                 let timeout;
                 return function(...args) {
                     const context = this;
                     clearTimeout(timeout);
                     timeout = setTimeout(() => func.apply(context, args), wait);
                 };
             }

             // Function to load GAD proposal data with debouncing
             const debouncedLoadData = debounce(function() {
                 const activityFilter = document.getElementById('filter-activity').value;
                 const modeFilter = document.getElementById('filter-mode').value;
                 let campusFilter = document.getElementById('filter-campus').value;

                 // Don't clear the table yet - keep showing previous data until new data arrives

                 // Fetch data from server
                 fetch('get_gad_proposals.php', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                         },
                         body: JSON.stringify({
                             activity: activityFilter,
                             mode: modeFilter,
                             campus: campusFilter
                         })
                     })
                     .then(response => response.json())
                     .then(data => {
                         if (data.success) {
                             proposalData = data.data;
                             totalPages = Math.ceil(proposalData.length / rowsPerPage);

                             // Reset to first page when filters change
                             currentPage = 1;

                             // Update display
                             updatePaginationDisplay();
                             renderCurrentPage();
                         } else {
                             console.error('API returned error:', data.error || data.message);
                             document.getElementById('gad-proposal-tbody').innerHTML =
                                 '<tr><td colspan="' + (isCentralUser ? '4' : '3') + '" class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> No records found</td></tr>';
                         }
                     })
                     .catch(error => {
                         console.error('Error fetching data:', error);
                         document.getElementById('gad-proposal-tbody').innerHTML =
                             '<tr><td colspan="' + (isCentralUser ? '4' : '3') + '" class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> Connection error. Please try again.</td></tr>';
                     });
             }, 300); // Wait 300ms after typing stops before filtering

             // Replace original function with debounced version
             function loadGadProposalData() {
                 debouncedLoadData();
             }

             // Function to render the current page of data
             function renderCurrentPage() {
                 const tbody = document.getElementById('gad-proposal-tbody');
                 tbody.innerHTML = '';

                 const startIndex = (currentPage - 1) * rowsPerPage;
                 const endIndex = Math.min(startIndex + rowsPerPage, proposalData.length);

                 if (proposalData.length === 0) {
                     tbody.innerHTML = '<tr><td colspan="' + (isCentralUser ? '4' : '3') + '" class="text-center">No data found</td></tr>';
                     return;
                 }

                 for (let i = startIndex; i < endIndex; i++) {
                     const item = proposalData[i];
                     const row = document.createElement('tr');

                     // Add row click handler based on mode
                     if (currentModalMode === 'edit') {
                         row.onclick = function() {
                             // Fetch and load data for editing
                             loadProposalForEditing(item.id);
                             // Close the modal
                             closeGadProposalModal();
                         };
                         row.style.cursor = 'pointer';
                     } else if (currentModalMode === 'delete') {
                         row.onclick = function() {
                             confirmDeleteProposal(item);
                         };
                         row.style.cursor = 'pointer';
                     } else if (currentModalMode === 'view') {
                         // Do nothing for view mode - remove the click handler
                         row.onclick = null;
                         row.style.cursor = 'default';
                     }

                     let rowHTML = `
                    <td>${item.activity_name}</td>
                    <td>${item.mode_of_delivery}</td>
                    <td>${item.partner_office}</td>`;

                     // Add campus column for Central users
                     if (isCentralUser) {
                         rowHTML += `<td>${item.campus}</td>`;
                     }

                     row.innerHTML = rowHTML;
                     tbody.appendChild(row);
                 }
             }

             // Function to update pagination display
             function updatePaginationDisplay() {
                 const pagination = document.getElementById('proposalsPagination');
                 pagination.innerHTML = '';

                 // Create box-style pagination that matches the image
                 // Add pagination container with proper styling - reduce margin top
                 pagination.className = 'd-flex justify-content-center mt-2';

                 // Previous button with <<
                 const prevLink = document.createElement('a');
                 prevLink.className = 'btn btn-outline-secondary rounded-0 border-end-0';
                 prevLink.innerHTML = '&laquo;';
                 prevLink.href = 'javascript:void(0)';
                 prevLink.style.borderRadius = '0';
                 prevLink.style.borderTopLeftRadius = '0.25rem';
                 prevLink.style.borderBottomLeftRadius = '0.25rem';
                 if (currentPage === 1) {
                     prevLink.classList.add('disabled');
                     prevLink.style.pointerEvents = 'none';
                 }
                 prevLink.addEventListener('click', () => {
                     if (currentPage > 1) {
                         currentPage--;
                         updatePaginationDisplay();
                         renderCurrentPage();
                     }
                 });
                 pagination.appendChild(prevLink);

                 // Calculate which page numbers to show (max 5)
                 const maxVisiblePages = 5;
                 let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
                 let endPage = startPage + maxVisiblePages - 1;

                 // Adjust if endPage exceeds totalPages
                 if (endPage > totalPages) {
                     endPage = totalPages;
                     startPage = Math.max(1, endPage - maxVisiblePages + 1);
                 }

                 // Page numbers
                 for (let i = startPage; i <= endPage; i++) {
                     const pageLink = document.createElement('a');

                     // Style for page numbers
                     if (i === currentPage) {
                         // Current page style
                         pageLink.className = 'btn rounded-0 border-start-0 border-end-0';
                         pageLink.style.backgroundColor = 'var(--accent-color)';
                         pageLink.style.color = 'white';
                         pageLink.style.borderColor = 'var(--accent-color)';
                     } else {
                         // Other page numbers style
                         pageLink.className = 'btn btn-outline-secondary rounded-0 border-start-0 border-end-0';
                     }

                     pageLink.textContent = i;
                     pageLink.href = 'javascript:void(0)';

                     // Add click event to change page
                     pageLink.addEventListener('click', () => {
                         if (i !== currentPage) {
                             currentPage = i;
                             updatePaginationDisplay();
                             renderCurrentPage();
                         }
                     });

                     pagination.appendChild(pageLink);
                 }

                 // Next button with >>
                 const nextLink = document.createElement('a');
                 nextLink.className = 'btn btn-outline-secondary rounded-0 border-start-0';
                 nextLink.innerHTML = '&raquo;';
                 nextLink.href = 'javascript:void(0)';
                 nextLink.style.borderRadius = '0';
                 nextLink.style.borderTopRightRadius = '0.25rem';
                 nextLink.style.borderBottomRightRadius = '0.25rem';
                 if (currentPage === totalPages) {
                     nextLink.classList.add('disabled');
                     nextLink.style.pointerEvents = 'none';
                 }
                 nextLink.addEventListener('click', () => {
                     if (currentPage < totalPages) {
                         currentPage++;
                         updatePaginationDisplay();
                         renderCurrentPage();
                     }
                 });
                 pagination.appendChild(nextLink);
             }

             // Function to confirm delete
             function confirmDeleteProposal(item) {
                 let detailsHTML = `
                <div class="text-left">
                    <p>Are you sure you want to delete this GAD Proposal?</p>
                    <ul class="list-unstyled">
                        <li><strong>Activity:</strong> ${item.activity_name}</li>
                        <li><strong>Mode of Delivery:</strong> ${item.mode_of_delivery}</li>
                        <li><strong>Partner Office:</strong> ${item.partner_office}</li>`;

                 // Add campus info for Central users
                 if (isCentralUser) {
                     detailsHTML += `<li><strong>Campus:</strong> ${item.campus}</li>`;
                 }

                 detailsHTML += `
                        </ul>
                        <p class="text-danger">This action cannot be undone!</p>
                    </div>
                `;

                 Swal.fire({
                     title: 'Confirm Deletion',
                     html: detailsHTML,
                     icon: 'warning',
                     showCancelButton: true,
                     confirmButtonColor: '#dc3545',
                     cancelButtonColor: '#6c757d',
                     confirmButtonText: 'Delete',
                     cancelButtonText: 'Cancel',
                     backdrop: `rgba(0,0,0,0.7)`,
                     allowOutsideClick: false,
                     customClass: {
                         container: 'swal-blur-container'
                     },
                     // Ensure SweetAlert appears above the modal
                     zIndex: 10000
                 }).then((result) => {
                     if (result.isConfirmed) {
                         deleteGadProposal(item.id);
                     }
                 });
             }

             // Function to delete GAD proposal
             function deleteGadProposal(id) {
                 fetch('delete_gad_proposal.php', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                         },
                         body: JSON.stringify({
                             id: id
                         })
                     })
                     .then(response => {
                         // Check if response is ok
                         if (!response.ok) {
                             return response.text().then(text => {
                                 // Try to parse the response as JSON
                                 try {
                                     return JSON.parse(text);
                                 } catch (e) {
                                     // If not valid JSON, create an error object
                                     console.error('Server returned non-JSON response:', text);
                                     return {
                                         success: false,
                                         message: 'Server returned an invalid response. Please try again.'
                                     };
                                 }
                             });
                         }
                         return response.json();
                     })
                     .then(data => {
                         if (data.success) {
                             // Show success message
                             Swal.fire({
                                 title: 'Deleted!',
                                 text: 'The GAD Proposal has been deleted successfully.',
                                 icon: 'success',
                                 confirmButtonColor: '#6a1b9a',
                                 timer: 1500,
                                 timerProgressBar: true,
                                 backdrop: `rgba(0,0,0,0.7)`,
                                 showConfirmButton: false,
                                 customClass: {
                                     container: 'swal-blur-container'
                                 }
                             }).then(() => {
                                 // Reload the data instead of reloading the entire page
                                 loadGadProposalData();
                             });
                         } else {
                             Swal.fire({
                                 title: 'Error',
                                 text: data.message || 'Failed to delete GAD Proposal',
                                 icon: 'error',
                                 confirmButtonColor: '#6a1b9a'
                             });
                         }
                     })
                     .catch(error => {
                         console.error('Error:', error);
                         Swal.fire({
                             title: 'Error',
                             text: 'Failed to delete GAD Proposal. Please try again.',
                             icon: 'error',
                             confirmButtonColor: '#6a1b9a'
                         });
                     });
             }

             // Add event listeners to buttons
             document.addEventListener('DOMContentLoaded', function() {
                 const viewBtn = document.getElementById('viewBtn');
                 if (viewBtn) {
                     viewBtn.addEventListener('click', function() {
                         openGadProposalModal('view');
                     });
                 }

                 const editBtn = document.getElementById('editBtn');
                 if (editBtn) {
                     editBtn.addEventListener('click', function() {
                         openGadProposalModal('edit');
                     });
                 }

                 const deleteBtn = document.getElementById('deleteBtn');
                 if (deleteBtn) {
                     deleteBtn.addEventListener('click', function() {
                         openGadProposalModal('delete');
                     });
                 }

                 // Make sure the campus filter is enabled for Central users on page load
                 enableCampusFilter();

                 // Set an interval to check and ensure campus filter remains enabled (in case other scripts disable it)
                 if (isCentralUser) {
                     setInterval(enableCampusFilter, 1000);
                 }
             });

             // Edit mode functionality
             let isEditMode = false;
             let currentProposalId = null;
             let originalFormData = null;

             // Override the row click handler in renderCurrentPage for edit mode
             const originalRenderCurrentPage = renderCurrentPage;
             renderCurrentPage = function() {
                 const tbody = document.getElementById('gad-proposal-tbody');
                 tbody.innerHTML = '';

                 const startIndex = (currentPage - 1) * rowsPerPage;
                 const endIndex = Math.min(startIndex + rowsPerPage, proposalData.length);

                 if (proposalData.length === 0) {
                     tbody.innerHTML = '<tr><td colspan="' + (isCentralUser ? '4' : '3') + '" class="text-center">No data found</td></tr>';
                     return;
                 }

                 for (let i = startIndex; i < endIndex; i++) {
                     const item = proposalData[i];
                     const row = document.createElement('tr');

                     // Add row click handler based on mode
                     if (currentModalMode === 'edit') {
                         row.onclick = function() {
                             // Fetch and load data for editing
                             loadProposalForEditing(item.id);
                             // Close the modal
                             closeGadProposalModal();
                         };
                         row.style.cursor = 'pointer';
                     } else if (currentModalMode === 'delete') {
                         row.onclick = function() {
                             confirmDeleteProposal(item);
                         };
                         row.style.cursor = 'pointer';
                     } else if (currentModalMode === 'view') {
                         // Do nothing for view mode
                         row.onclick = null;
                         row.style.cursor = 'default';
                     }

                     let rowHTML = `
                    <td>${item.activity_name}</td>
                    <td>${item.mode_of_delivery}</td>
                    <td>${item.partner_office}</td>`;

                     // Add campus column for Central users
                     if (isCentralUser) {
                         rowHTML += `<td>${item.campus}</td>`;
                     }

                     row.innerHTML = rowHTML;
                     tbody.appendChild(row);
                 }
             };

             // Function to load proposal data for editing
             function loadProposalForEditing(proposalId) {

                 console.log("Loading proposal for editing:", proposalId);
                 isEditMode = true;
                 currentProposalId = proposalId;

                 // Smooth scroll to the top of main-content
                 const mainContent = document.querySelector('.main-content');
                 if (mainContent) {
                     mainContent.scrollTo({
                         top: 0,
                         behavior: 'smooth'
                     });
                 } else {
                     // Fallback to window scroll
                     window.scrollTo({
                         top: 0,
                         behavior: 'smooth'
                     });
                 }

                 // Fetch the proposal data
                 console.log("Fetching proposal data from server...");
                 fetch(`get_gad_proposal.php?id=${proposalId}`, {
                         method: 'GET',
                         headers: {
                             'Content-Type': 'application/json',
                         }
                     })
                     .then(response => response.json())
                     .then(data => {
                         if (data.success) {
                             console.log("EDIT MODE - Proposal data received:", data.data);
                             console.log("EDIT MODE - PPAS Form ID:", data.data.ppas_form_id);

                             // Change the form title
                             document.querySelector('.card-title').textContent = 'Edit Proposal Form';

                             // Store original form data for reset
                             originalFormData = {
                                 ...data.data
                             };

                             // Clear previous validation indicators before filling form
                             clearIncompleteIndicators();

                             // Fill non-dropdown fields first
                             if (data.data.mode_of_delivery) {
                                 document.getElementById('mode_of_delivery').value = data.data.mode_of_delivery;
                             }

                             if (data.data.partner_office) {
                                 document.getElementById('partner_office').value = data.data.partner_office;
                             }

                             if (data.data.rationale) {
                                 document.getElementById('rationale').value = data.data.rationale;
                             }

                             if (data.data.general_objectives) {
                                 document.getElementById('objectives').value = data.data.general_objectives;
                             }

                             if (data.data.description) {
                                 document.getElementById('description').value = data.data.description;
                             }

                             if (data.data.budget_breakdown) {
                                 document.getElementById('budget_breakdown').value = data.data.budget_breakdown;
                             }

                             if (data.data.sustainability_plan) {
                                 document.getElementById('sustainability_plan').value = data.data.sustainability_plan;
                             }

                             // Handle array fields
                             fillArrayField('specific_objectives', data.data.specific_objectives);
                             fillArrayField('project_leader_responsibilities', data.data.project_leader_responsibilities);
                             fillArrayField('assistant_project_leader_responsibilities', data.data.assistant_leader_responsibilities);
                             fillArrayField('project_staff_responsibilities', data.data.staff_responsibilities);
                             fillArrayField('strategies', data.data.strategies);
                             fillArrayField('materials', data.data.materials);
                             fillArrayField('specific_plans', data.data.specific_plans);

                             // Fill Methods section (Activities / Schedule)
                             if (data.data.methods && Array.isArray(data.data.methods)) {
                                 console.log("Filling methods section with:", data.data.methods);
                                 fillMethodsSection(data.data.methods);
                             }

                             // Fill Work Plan section (Timeline/Gantt Chart) - first make sure the table is initialized
                             // Handle both directly available dates or extract them from workplan data
                             let startDate = data.data.start_date;
                             let endDate = data.data.end_date;

                             // Try to extract dates from workplan data if they're not directly available
                             if ((!startDate || !endDate) && data.data.workplan && Array.isArray(data.data.workplan) && data.data.workplan.length > 0) {
                                 console.log("WORKPLAN DEBUG: No direct start_date/end_date found, attempting to extract from workplan data");

                                 // Extract all dates from all workplan items
                                 const allDates = [];
                                 data.data.workplan.forEach(item => {
                                     let dates = [];
                                     if (Array.isArray(item) && item.length > 1 && Array.isArray(item[1])) {
                                         dates = item[1];
                                     } else if (item.days && Array.isArray(item.days)) {
                                         dates = item.days;
                                     }

                                     // Add valid dates to our collection
                                     dates.forEach(date => {
                                         if (typeof date === 'string' && date.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                             allDates.push(date);
                                         }
                                     });
                                 });

                                 if (allDates.length > 0) {
                                     console.log("WORKPLAN DEBUG: Extracted dates from workplan:", allDates);
                                     // Sort dates to find min and max
                                     allDates.sort();
                                     startDate = allDates[0];
                                     endDate = allDates[allDates.length - 1];
                                     console.log("WORKPLAN DEBUG: Using extracted date range:", startDate, "to", endDate);
                                 } else {
                                     console.warn("WORKPLAN DEBUG: Could not extract dates from workplan data");
                                 }
                             }

                             if (startDate && endDate) {
                                 console.log("WORKPLAN DEBUG: Initializing Work Plan table with dates:", startDate, endDate);

                                 // Set start date
                                 const startDateEl = document.getElementById('start_date');
                                 if (startDateEl) {
                                     startDateEl.value = startDate;
                                     console.log("WORKPLAN DEBUG: Set start_date to:", startDate, "Value is now:", startDateEl.value);
                                 } else {
                                     console.error("WORKPLAN DEBUG: Could not find start_date element");
                                 }

                                 // Set end date
                                 const endDateEl = document.getElementById('end_date');
                                 if (endDateEl) {
                                     endDateEl.value = endDate;
                                     console.log("WORKPLAN DEBUG: Set end_date to:", endDate, "Value is now:", endDateEl.value);
                                 } else {
                                     console.error("WORKPLAN DEBUG: Could not find end_date element");
                                 }

                                 // Update the workplan table with the new date range
                                 console.log("WORKPLAN DEBUG: Calling updateWorkPlanTable() to rebuild table with date range");
                                 updateWorkPlanTable();

                                 // Now fill the workplan data
                                 if (data.data.workplan && Array.isArray(data.data.workplan)) {
                                     console.log("WORKPLAN DEBUG: Workplan data found with", data.data.workplan.length, "items:", data.data.workplan);
                                     setTimeout(() => {
                                         console.log("WORKPLAN DEBUG: Calling fillWorkPlanSection after delay");
                                         fillWorkPlanSection(data.data.workplan);
                                     }, 500); // Wait for the table to be fully updated
                                 } else {
                                     console.warn("WORKPLAN DEBUG: No workplan data found or invalid format");
                                 }
                             } else {
                                 console.warn("WORKPLAN DEBUG: Could not determine start_date and end_date for workplan");
                             }

                             // Fill Monitoring section
                             if (data.data.monitoring_items && Array.isArray(data.data.monitoring_items)) {
                                 console.log("Filling monitoring section with:", data.data.monitoring_items);
                                 fillMonitoringSection(data.data.monitoring_items);
                             }

                             // Now set the dropdowns directly from the proposal data
                             if (data.data.ppas_form_id) {
                                 // Get the year dropdown element
                                 const yearElement = document.getElementById('year');
                                 console.log("Year dropdown options:", Array.from(yearElement.options).map(opt => ({
                                     value: opt.value,
                                     text: opt.text
                                 })));

                                 // Use the year directly from the response if available
                                 if (data.data.year) {
                                     console.log("Using year directly from proposal data:", data.data.year);
                                     yearElement.value = data.data.year;
                                     console.log("Set year to:", data.data.year, "Value is now:", yearElement.value);

                                     // Enable quarter dropdown
                                     const quarterElement = document.getElementById('quarter');
                                     quarterElement.disabled = false;

                                     // If quarter is available, set it
                                     if (data.data.quarter) {
                                         console.log("Using quarter directly from proposal data:", data.data.quarter);

                                         // Wait for quarter options to load if needed
                                         setTimeout(() => {
                                             console.log("Quarter dropdown options:", Array.from(quarterElement.options).map(opt => ({
                                                 value: opt.value,
                                                 text: opt.text
                                             })));
                                             quarterElement.value = data.data.quarter;
                                             console.log("Set quarter to:", data.data.quarter, "Value is now:", quarterElement.value);

                                             // Trigger change event to load activities
                                             quarterElement.dispatchEvent(new Event('change'));

                                             // Wait for activities to load
                                             setTimeout(() => {
                                                 // Set activity value
                                                 const activityElement = document.getElementById('activity_title');
                                                 console.log("Available activities:", Array.from(activityElement.options).map(opt => ({
                                                     value: opt.value,
                                                     text: opt.text
                                                 })));
                                                 activityElement.value = data.data.ppas_form_id;
                                                 console.log("Set activity to:", data.data.ppas_form_id, "Value is now:", activityElement.value);

                                                 // Trigger change event
                                                 activityElement.dispatchEvent(new Event('change'));

                                                 // Transform buttons
                                                 transformButtonsForEditMode();

                                                 // Validate all sections after form is filled
                                                 setTimeout(() => {
                                                     validateAllSectionsWithVisualFeedback();
                                                 }, 500);
                                             }, 1000); // Wait 1 second for activities to load
                                         }, 300); // Wait 300ms for quarter options
                                     } else {
                                         console.log("No quarter in proposal data");

                                         // Transform buttons
                                         transformButtonsForEditMode();

                                         // Validate all sections
                                         setTimeout(() => {
                                             validateAllSectionsWithVisualFeedback();
                                         }, 500);
                                     }
                                 } else {
                                     console.log("No year in proposal data");

                                     // As a last resort, use 2025 (based on dropdown options)
                                     console.log("Using hardcoded year 2025 as last resort");
                                     yearElement.value = "2025";
                                     console.log("Set year to: 2025, Value is now:", yearElement.value);

                                     const quarterElement = document.getElementById('quarter');
                                     quarterElement.disabled = false;

                                     // Default to first quarter option
                                     setTimeout(() => {
                                         console.log("Using first available quarter as last resort");
                                         const options = Array.from(quarterElement.options).filter(opt => opt.value);
                                         if (options.length > 0) {
                                             quarterElement.value = options[0].value;
                                             console.log("Set quarter to:", options[0].value, "Value is now:", quarterElement.value);
                                             quarterElement.dispatchEvent(new Event('change'));

                                             setTimeout(() => {
                                                 const activityElement = document.getElementById('activity_title');
                                                 activityElement.value = data.data.ppas_form_id;
                                                 console.log("Set activity to:", data.data.ppas_form_id, "Value is now:", activityElement.value);
                                                 activityElement.dispatchEvent(new Event('change'));

                                                 // Transform buttons
                                                 transformButtonsForEditMode();

                                                 // Validate all sections
                                                 setTimeout(() => {
                                                     validateAllSectionsWithVisualFeedback();
                                                 }, 500);
                                             }, 1000);
                                         } else {
                                             // Transform buttons
                                             transformButtonsForEditMode();

                                             // Validate all sections
                                             setTimeout(() => {
                                                 validateAllSectionsWithVisualFeedback();
                                             }, 500);
                                         }
                                     }, 300);
                                 }
                             } else {
                                 // If no ppas_form_id, we don't need to fetch additional data
                                 // Transform buttons
                                 transformButtonsForEditMode();

                                 // Validate all sections
                                 setTimeout(() => {
                                     validateAllSectionsWithVisualFeedback();
                                 }, 500);
                             }
                         } else {
                             console.error("Error fetching proposal data:", data.message);

                             // Show error
                             Swal.fire({
                                 title: 'Error!',
                                 text: data.message || 'Failed to load proposal data',
                                 icon: 'error',
                                 confirmButtonColor: '#6a1b9a'
                             });
                         }
                     })
                     .catch(error => {
                         console.error('Error fetching proposal data:', error);

                         Swal.fire({
                             title: 'Error!',
                             text: 'Failed to load proposal data',
                             icon: 'error',
                             confirmButtonColor: '#6a1b9a'
                         });
                     });
             }


             // Function to transform buttons for edit mode
             function transformButtonsForEditMode() {
                 const addBtn = document.getElementById('addBtn');
                 const editBtn = document.getElementById('editBtn');
                 const deleteBtn = document.getElementById('deleteBtn');

                 // Disable year, quarter, and activity title dropdowns during edit mode
                 const yearDropdown = document.getElementById('year');
                 const quarterDropdown = document.getElementById('quarter');
                 const activityTitleDropdown = document.getElementById('activity_title');

                 if (yearDropdown) yearDropdown.disabled = true;
                 if (quarterDropdown) quarterDropdown.disabled = true;
                 if (activityTitleDropdown) activityTitleDropdown.disabled = true;

                 if (addBtn) {
                     // Transform add button to update button (just an icon)
                     addBtn.innerHTML = '<i class="fas fa-save"></i>';
                     addBtn.title = 'Update Proposal';
                     addBtn.classList.add('btn-update');
                 }

                 if (editBtn) {
                     // Transform edit button to X button (red color palette)
                     editBtn.innerHTML = '<i class="fas fa-times"></i>';
                     editBtn.title = 'Cancel Edit';
                     editBtn.className = 'btn-icon text-danger';
                     editBtn.style.backgroundColor = 'rgba(220, 53, 69, 0.1)';

                     // Remove existing event listeners and add new one
                     const newEditBtn = editBtn.cloneNode(true);
                     editBtn.parentNode.replaceChild(newEditBtn, editBtn);

                     newEditBtn.addEventListener('click', exitEditMode);
                 }

                 if (deleteBtn) {
                     // Disable delete button
                     deleteBtn.classList.add('disabled');
                     deleteBtn.style.pointerEvents = 'none';
                     deleteBtn.style.opacity = '0.5';
                 }

                 // Change the form title
                 document.querySelector('.card-title').textContent = 'Edit Proposal Form';
             }

             // Function to exit edit mode and revert changes
             function exitEditMode() {
                 if (!isEditMode) return;
                 const yearDropdown = document.getElementById('year');
                 if (yearDropdown) yearDropdown.disabled = false;
                 // Reset form
                 resetForm();

                 // Clear all validation indicators
                 clearIncompleteIndicators();
                 document.querySelectorAll('.section.complete').forEach(section => {
                     section.classList.remove('complete');
                 });
                 document.querySelectorAll('.field-invalid').forEach(field => {
                     field.classList.remove('field-invalid');
                 });

                 // Clear workplan table
                 clearWorkplanTable();

                 // Restore buttons to original state
                 restoreButtonsFromEditMode();

                 // Change form title back
                 document.querySelector('.card-title').textContent = 'Add GAD Proposal Form';

                 // Reset mode variables
                 isEditMode = false;
                 currentProposalId = null;
                 originalFormData = null;

                 // Set flag in localStorage to open edit modal after refresh
                 localStorage.setItem('openGadEditModal', 'true');

                 // Refresh the page
                 window.location.reload();
             }

             // Function to reset form
             function resetForm() {
                 const form = document.getElementById('ppasForm');
                 if (form) {
                     form.reset();
                 }

                 // Reset select elements to first option
                 const selects = form.querySelectorAll('select');
                 selects.forEach(select => {
                     select.selectedIndex = 0;

                     // Disable dependent selects
                     if (select.id === 'quarter' || select.id === 'activity_title') {
                         select.disabled = true;
                     }
                 });

                 // Clear array fields
                 const arrayContainers = form.querySelectorAll('[id$="_container"]');
                 arrayContainers.forEach(container => {
                     // Keep first input, remove others
                     while (container.children.length > 1) {
                         container.removeChild(container.lastChild);
                     }

                     // Clear first input value
                     const firstInput = container.querySelector('input');
                     if (firstInput) {
                         firstInput.value = '';
                     }
                 });

                 // Special handling for method items and details
                 resetMethodsSection();
             }

             // Function to reset the methods section
             function resetMethodsSection() {
                 // Get the methods container
                 const methodsContainer = document.getElementById('methods_container');
                 if (!methodsContainer) return;

                 // Get all method items
                 const methodItems = methodsContainer.querySelectorAll('.method-item');

                 // Keep only the first method item
                 if (methodItems.length > 1) {
                     for (let i = 1; i < methodItems.length; i++) {
                         methodsContainer.removeChild(methodItems[i]);
                     }
                 }

                 // Reset the first method item if it exists
                 if (methodItems.length > 0) {
                     const firstMethodItem = methodItems[0];

                     // Clear the method name input
                     const nameInput = firstMethodItem.querySelector('input[name*="[name]"]');
                     if (nameInput) {
                         nameInput.value = '';
                     }

                     // Get the activity details container
                     const detailsContainer = firstMethodItem.querySelector('.activity-details-container');
                     if (detailsContainer) {
                         // Get all detail rows
                         const detailRows = detailsContainer.querySelectorAll('.d-flex');

                         // Keep only the first detail row
                         if (detailRows.length > 1) {
                             for (let i = 1; i < detailRows.length; i++) {
                                 detailsContainer.removeChild(detailRows[i]);
                             }
                         }

                         // Clear the input in the first row
                         if (detailRows.length > 0) {
                             const input = detailRows[0].querySelector('input');
                             if (input) {
                                 input.value = '';
                             }
                         }
                     }
                 }

                 console.log("Methods section reset");
             }

             // Function to restore buttons from edit mode
             function restoreButtonsFromEditMode() {
                 const addBtn = document.getElementById('addBtn');
                 const editBtn = document.getElementById('editBtn');
                 const deleteBtn = document.getElementById('deleteBtn');

                 if (addBtn) {
                     // Restore add button
                     addBtn.innerHTML = '<i class="fas fa-plus"></i>';
                     addBtn.title = 'Add Proposal';
                     addBtn.classList.remove('btn-update');
                 }

                 if (editBtn) {
                     // Restore edit button
                     editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                     editBtn.title = 'Edit Proposals';
                     editBtn.className = 'btn-icon';
                     editBtn.style.backgroundColor = '';

                     // Remove event listeners and restore original
                     const newEditBtn = editBtn.cloneNode(true);
                     editBtn.parentNode.replaceChild(newEditBtn, editBtn);

                     // Re-add the original event listener
                     newEditBtn.addEventListener('click', function() {
                         openGadProposalModal('edit');
                     });
                 }

                 if (deleteBtn) {
                     // Re-enable delete button
                     deleteBtn.classList.remove('disabled');
                     deleteBtn.style.pointerEvents = 'auto';
                     deleteBtn.style.opacity = '1';
                 }

                 // Restore form title
                 document.querySelector('.card-title').textContent = 'Add GAD Proposal Form';
             }

             // Modify form submission to handle updates
             // Remove this duplicate event listener that's causing double submissions
             /*
             const originalFormSubmitListener = document.getElementById('ppasForm').onsubmit;
             document.getElementById('ppasForm').addEventListener('submit', function(e) {
                 e.preventDefault();
                 
                 // Create FormData object
                 const formData = new FormData(this);
                 
                 // If in edit mode, add the proposal ID
                 if (isEditMode && currentProposalId) {
                     formData.append('proposal_id', currentProposalId);
                     formData.append('is_update', 'true');
                 }
                 
                 // Call submitForm function
                 submitForm(formData);
             });
             */

             // Instead, modify the existing form submit handler to handle edit mode
             const originalSubmitForm = submitForm;
             submitForm = function(formData) {
                 // If in edit mode, add the proposal ID
                 if (isEditMode && currentProposalId) {
                     formData.append('proposal_id', currentProposalId);
                     formData.append('is_update', 'true');

                     // Use the update endpoint instead
                     updateGadProposal();
                     return; // Don't call the original submitForm
                 }

                 // Call the original submitForm for new entries
                 originalSubmitForm(formData);
             };

             // Function to update GAD proposal
             function updateGadProposal() {
                 // Validate form with visual feedback
                 // Clear previous incomplete indicators
                 clearIncompleteIndicators();

                 // Validate and mark incomplete fields
                 const isValid = validateFormWithVisualFeedback();
                 console.log("Form validation result:", isValid);

                 if (!isValid) {
                     console.log("Form validation failed, stopping submission");
                     // Scroll to the first incomplete section
                     const firstIncompleteSection = document.querySelector('.section.incomplete');
                     if (firstIncompleteSection) {
                         firstIncompleteSection.scrollIntoView({
                             behavior: 'smooth',
                             block: 'start'
                         });

                         // Highlight the section with animation
                         firstIncompleteSection.classList.add('highlight-invalid');
                         setTimeout(() => {
                             firstIncompleteSection.classList.remove('highlight-invalid');
                         }, 600);
                     }

                     return; // Stop form submission
                 }

                 // Create FormData object
                 const form = document.getElementById('ppasForm');
                 const formData = new FormData(form);

                 // Ensure proposal_id is included
                 if (!formData.get('proposal_id') && currentProposalId) {
                     formData.append('proposal_id', currentProposalId);
                 }

                 // Special handling for responsibility arrays
                 // Get responsibilities for assistant leader
                 const assistantLeaderResp = getResponsibilities('assistant-leader');
                 console.log("Assistant Leader Responsibilities:", assistantLeaderResp);
                 if (assistantLeaderResp && assistantLeaderResp.length > 0) {
                     formData.append('assistant_leader_responsibilities', JSON.stringify(assistantLeaderResp));
                 }

                 // Get responsibilities for staff
                 const staffResp = getResponsibilities('staff');
                 console.log("Staff Responsibilities:", staffResp);
                 if (staffResp && staffResp.length > 0) {
                     formData.append('staff_responsibilities', JSON.stringify(staffResp));
                 }

                 // Get responsibilities for project leader
                 const leaderResp = getResponsibilities('project-leader');
                 console.log("Project Leader Responsibilities:", leaderResp);
                 if (leaderResp && leaderResp.length > 0) {
                     formData.append('project_leader_responsibilities', JSON.stringify(leaderResp));
                 }

                 // Special handling for workplan data
                 const workplanData = getWorkplanData();
                 console.log("Workplan Data:", workplanData);
                 formData.append('workplan', JSON.stringify(workplanData));

                 // Special handling for methods data
                 const methodsData = getMethodsWithDetails();
                 console.log("Methods Data:", methodsData);
                 formData.append('methods', JSON.stringify(methodsData));

                 // Send update request
                 fetch('update_gad_proposal.php', {
                         method: 'POST',
                         body: formData
                     })
                     .then(response => {
                         // Always get text first to handle non-JSON responses
                         return response.text().then(text => {
                             console.log('Response received:', text.substring(0, 100) + (text.length > 100 ? '...' : ''));

                             // Try to parse as JSON
                             try {
                                 const json = JSON.parse(text);
                                 return json;
                             } catch (e) {
                                 // If not valid JSON, log error and create error object
                                 console.error('Error parsing JSON:', e);
                                 console.error('Server returned non-JSON response:', text);

                                 return {
                                     success: false,
                                     message: 'Server returned an invalid response: ' +
                                         (text.length > 100 ? text.substring(0, 100) + '...' : text),
                                     originalResponse: text
                                 };
                             }
                         });
                     })
                     .then(data => {
                         // Close loading state
                         Swal.close();

                         if (data.success) {
                             // Clear all validation indicators
                             clearAllValidationIndicators();

                             // Show success message
                             Swal.fire({
                                 title: 'Success!',
                                 text: 'GAD Proposal has been updated successfully',
                                 icon: 'success',
                                 timer: 1500,
                                 timerProgressBar: true,
                                 showConfirmButton: false,
                                 backdrop: `rgba(0,0,0,0.7)`,
                                 allowOutsideClick: false,
                                 customClass: {
                                     container: 'swal-blur-container'
                                 }
                             }).then(() => {
                                 // Reset form
                                 resetForm();

                                 // Restore buttons to original state
                                 restoreButtonsFromEditMode();

                                 // Reset mode variables
                                 isEditMode = false;
                                 currentProposalId = null;
                                 originalFormData = null;

                                 window.location.reload();
                             });
                         } else {
                             // Show error message
                             Swal.fire({
                                 title: 'Error!',
                                 text: data.message || 'Failed to update GAD Proposal',
                                 icon: 'error',
                                 confirmButtonColor: '#6a1b9a'
                             });
                         }
                     })
                     .catch(error => {
                         // Close loading state
                         Swal.close();

                         // Show error message
                         console.error('Error:', error);
                         Swal.fire({
                             title: 'Error!',
                             text: 'Failed to update GAD Proposal',
                             icon: 'error',
                             confirmButtonColor: '#6a1b9a'
                         });
                     });
             }

             // Modify the transformButtonsForEditMode function to add update functionality
             const originalTransformButtonsForEditMode = transformButtonsForEditMode;
             transformButtonsForEditMode = function() {
                 originalTransformButtonsForEditMode();

                 // Get the add button
                 const addBtn = document.getElementById('addBtn');

                 // Remove existing event listeners and add new one for update
                 if (addBtn) {
                     const newAddBtn = addBtn.cloneNode(true);
                     addBtn.parentNode.replaceChild(newAddBtn, addBtn);

                     // Add click handler for update
                     newAddBtn.addEventListener('click', function(e) {
                         e.preventDefault();
                         updateGadProposal();
                     });
                 }
             };

             // Function to validate all sections and apply visual feedback
             function validateAllSectionsWithVisualFeedback() {
                 console.log("Validating all sections with visual feedback");

                 // Clear previous indicators first
                 clearIncompleteIndicators();
                 document.querySelectorAll('.section.complete').forEach(section => {
                     section.classList.remove('complete');
                 });

                 // Get all sections
                 const sections = document.querySelectorAll('.section');

                 // Validate each section
                 const basicInfoValid = validateBasicInfoSectionWithFeedback();
                 const personnelValid = validateProjectPersonnelSectionWithFeedback();
                 const participantsValid = validateParticipantsSectionWithFeedback();
                 const rationaleValid = validateRationaleSectionWithFeedback();
                 const objectivesValid = validateObjectivesSectionWithFeedback();
                 const descriptionValid = validateDescriptionSectionWithFeedback();
                 const methodsValid = validateMethodsSectionWithFeedback();
                 const materialsValid = validateMaterialsSectionWithFeedback();
                 const workplanValid = validateWorkPlanSectionWithFeedback();
                 const financialValid = validateFinancialSectionWithFeedback();
                 const monitoringValid = validateMonitoringSectionWithFeedback();
                 const sustainabilityValid = validateSustainabilitySectionWithFeedback();

                 // Map of validation results to section indices
                 const validationResults = [
                     basicInfoValid,
                     personnelValid,
                     participantsValid,
                     rationaleValid,
                     objectivesValid,
                     descriptionValid,
                     methodsValid,
                     materialsValid,
                     workplanValid,
                     financialValid,
                     monitoringValid,
                     sustainabilityValid
                 ];

                 // Apply complete class to valid sections
                 sections.forEach((section, index) => {
                     if (index < validationResults.length && validationResults[index]) {
                         section.classList.add('complete');
                     }
                 });

                 console.log("Validation complete. Results:", validationResults);
             }

             // Function to fill array fields
             function fillArrayField(fieldName, values) {
                 if (!values || !Array.isArray(values) || values.length === 0) return;

                 const container = document.getElementById(`${fieldName}_container`);
                 if (!container) return;

                 // Clear existing inputs except the first one
                 while (container.children.length > 1) {
                     container.removeChild(container.lastChild);
                 }

                 // Set the first value
                 const firstInput = container.querySelector(`input[name="${fieldName}[]"]`);
                 if (firstInput) {
                     firstInput.value = values[0];
                 }

                 // Add additional inputs for remaining values
                 for (let i = 1; i < values.length; i++) {
                     const newRow = document.createElement('div');
                     newRow.className = 'd-flex mb-2';
                     newRow.innerHTML = `
                    <input type="text" class="form-control" name="${fieldName}[]" value="${values[i]}" placeholder="Enter item">
                    <button type="button" class="btn btn-sm btn-danger remove-item ms-2">
                        <i class="fas fa-minus"></i>
                    </button>
                `;
                     container.appendChild(newRow);

                     // Add event listener to the remove button
                     const removeBtn = newRow.querySelector('.remove-item');
                     if (removeBtn) {
                         removeBtn.addEventListener('click', function() {
                             container.removeChild(newRow);
                         });
                     }
                 }
             }

             // Function to fill Methods section
             function fillMethodsSection(methods) {
                 console.log("METHODS DEBUG: Starting fillMethodsSection with:", methods);
                 if (!methods || !Array.isArray(methods) || methods.length === 0) {
                     console.log("METHODS DEBUG: No methods data available or invalid format");
                     return;
                 }

                 const methodsContainer = document.getElementById('methods_container');
                 if (!methodsContainer) {
                     console.error("METHODS DEBUG: Methods container not found");
                     return;
                 }

                 // Get the template item (first method item)
                 const templateItem = methodsContainer.querySelector('.method-item');
                 if (!templateItem) {
                     console.error("METHODS DEBUG: Method item template not found");
                     return;
                 }

                 // Clear existing method items except the template
                 const existingItems = methodsContainer.querySelectorAll('.method-item');
                 console.log(`METHODS DEBUG: Found ${existingItems.length} existing method items`);
                 for (let i = 1; i < existingItems.length; i++) {
                     methodsContainer.removeChild(existingItems[i]);
                 }

                 // Fill the first method item with data
                 if (methods.length > 0 && methods[0]) {
                     console.log("METHODS DEBUG: Filling first method item with:", methods[0]);
                     const firstItem = templateItem;
                     fillMethodItem(firstItem, methods[0], 0);
                 }

                 // Add additional method items for remaining data
                 for (let i = 1; i < methods.length; i++) {
                     if (methods[i]) {
                         console.log(`METHODS DEBUG: Filling method item ${i+1} with:`, methods[i]);
                         // Clone the template and fill it
                         const newItem = templateItem.cloneNode(true);
                         fillMethodItem(newItem, methods[i], i);

                         // Show remove button for additional items
                         const removeBtn = newItem.querySelector('.remove-method');
                         if (removeBtn) {
                             removeBtn.classList.remove('d-none');
                         }

                         methodsContainer.appendChild(newItem);
                     }
                 }

                 console.log(`METHODS DEBUG: Methods section filled with ${methods.length} items`);
             }

             // Helper function to fill a single method item
             function fillMethodItem(methodItem, methodData, index) {
                 console.log(`METHODS DEBUG: Filling method item ${index+1} with data:`, JSON.stringify(methodData));

                 // Handle both array format and object format
                 let activity = '';
                 let details = [];

                 if (Array.isArray(methodData)) {
                     // Handle array format - could be [activity, details] or [activity, objective, details]
                     activity = methodData[0] || '';

                     // Check if there's a details array at index 1 or 2
                     if (Array.isArray(methodData[1])) {
                         details = methodData[1];
                     } else if (Array.isArray(methodData[2])) {
                         details = methodData[2];
                     }

                     console.log(`METHODS DEBUG: Parsed array format - activity: ${activity}, details:`, details);
                 } else {
                     // Handle object format with multiple possible field names
                     activity = methodData.activity || methodData.name || '';
                     details = Array.isArray(methodData.details) ? methodData.details : [];
                     console.log(`METHODS DEBUG: Parsed object format - activity: ${activity}, details:`, details);
                 }

                 // Update title
                 const title = methodItem.querySelector('h6');
                 if (title) {
                     title.textContent = `Activity ${index + 1}`;
                 }

                 // Set the activity name - method items use [name] not [activity]
                 const activityInput = methodItem.querySelector('input[name^="methods"][name$="[name]"]');
                 if (activityInput) {
                     console.log(`METHODS DEBUG: Setting activity name to: ${activity} in field ${activityInput.name}`);
                     activityInput.value = activity;
                     activityInput.name = `methods[${index}][name]`;
                 } else {
                     console.warn(`METHODS DEBUG: Could not find activity name input for method item ${index+1}`);
                 }

                 // Handle details if available
                 if (details && details.length > 0) {
                     console.log(`METHODS DEBUG: Processing ${details.length} details for method item ${index+1}`);
                     const detailsContainer = methodItem.querySelector('.activity-details-container');
                     if (detailsContainer) {
                         // Set data-index attribute
                         detailsContainer.setAttribute('data-index', index);

                         // Get all detail rows
                         const detailRows = detailsContainer.querySelectorAll('.detail-row');
                         console.log(`METHODS DEBUG: Found ${detailRows.length} existing detail rows`);

                         // Fill first detail row
                         if (detailRows.length > 0 && details.length > 0) {
                             const firstDetailRow = detailRows[0];
                             const detailInput = firstDetailRow.querySelector('input');
                             if (detailInput) {
                                 console.log(`METHODS DEBUG: Setting first detail to: ${details[0]}`);
                                 detailInput.value = details[0];
                                 detailInput.name = `methods[${index}][details][]`;
                             } else {
                                 console.warn(`METHODS DEBUG: Could not find detail input in first row`);
                             }

                             // Add data-index attribute to the add button
                             const addButton = firstDetailRow.querySelector('.add-detail');
                             if (addButton) {
                                 addButton.setAttribute('data-index', index);
                             }
                         }

                         // Remove existing additional detail rows
                         for (let i = 1; i < detailRows.length; i++) {
                             detailsContainer.removeChild(detailRows[i]);
                         }

                         // Add additional detail rows
                         for (let i = 1; i < details.length; i++) {
                             console.log(`METHODS DEBUG: Adding detail row ${i+1} with: ${details[i]}`);
                             // Clone the first detail row
                             const templateRow = detailRows[0];
                             const newDetailRow = templateRow.cloneNode(true);

                             // Update the input value
                             const detailInput = newDetailRow.querySelector('input');
                             if (detailInput) {
                                 detailInput.value = details[i];
                                 detailInput.name = `methods[${index}][details][]`;
                             }

                             // Replace add button with remove button if needed
                             const addButton = newDetailRow.querySelector('.add-detail');
                             if (addButton) {
                                 addButton.remove();
                             }

                             // Add remove button if it doesn't exist
                             if (!newDetailRow.querySelector('.remove-detail')) {
                                 const removeBtn = document.createElement('button');
                                 removeBtn.className = 'btn btn-sm btn-danger remove-detail ms-2';
                                 removeBtn.style.height = '45px';
                                 removeBtn.style.width = '45px';
                                 removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
                                 newDetailRow.appendChild(removeBtn);
                             }

                             detailsContainer.appendChild(newDetailRow);
                         }
                     } else {
                         console.error(`METHODS DEBUG: Could not find details container for method item ${index+1}`);
                     }
                 } else {
                     // Even if there are no details in the data, we should set up the initial empty details container
                     console.log(`METHODS DEBUG: No details array found, setting up empty details container`);
                     const detailsContainer = methodItem.querySelector('.activity-details-container');
                     if (detailsContainer) {
                         detailsContainer.setAttribute('data-index', index);

                         const detailRows = detailsContainer.querySelectorAll('.detail-row');
                         if (detailRows.length > 0) {
                             // Just update the name and index for the first row
                             const firstDetailRow = detailRows[0];
                             const detailInput = firstDetailRow.querySelector('input');
                             if (detailInput) {
                                 detailInput.name = `methods[${index}][details][]`;
                                 detailInput.value = ''; // Clear it to be safe
                             }

                             // Update the add button index
                             const addButton = firstDetailRow.querySelector('.add-detail');
                             if (addButton) {
                                 addButton.setAttribute('data-index', index);
                             }
                         }
                     }
                 }

                 console.log(`METHODS DEBUG: Finished filling method item ${index+1}`);
             }

             // Function to fill Work Plan section
             function fillWorkPlanSection(workplanData) {
                 console.log("WORKPLAN DEBUG: Starting fillWorkPlanSection with:", workplanData);

                 if (!workplanData || workplanData.length === 0) {
                     console.log("WORKPLAN DEBUG: No workplan data to fill");
                     return;
                 }

                 const startDateEl = document.getElementById('start_date');
                 const endDateEl = document.getElementById('end_date');

                 // Check if date elements exist and if they're empty
                 if (!startDateEl || !endDateEl) {
                     console.log("WORKPLAN DEBUG: Missing start_date or end_date elements");
                     return;
                 }

                 // If date values are empty, try to restore them from workplan data
                 if (!startDateEl.value || !endDateEl.value) {
                     console.log("WORKPLAN DEBUG: No direct start_date/end_date found, attempting to extract from workplan data");

                     // Extract dates from workplan data
                     let dates = [];

                     try {
                         workplanData.forEach(activityRow => {
                             if (activityRow[1] && Array.isArray(activityRow[1])) {
                                 activityRow[1].forEach(date => {
                                     if (date && date.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                         dates.push(date);
                                     }
                                 });
                             }
                         });

                         console.log("WORKPLAN DEBUG: Extracted dates from workplan:", dates);

                         if (dates.length > 0) {
                             // Sort dates to find first and last
                             dates.sort();
                             const firstDate = dates[0];
                             const lastDate = dates[dates.length - 1];

                             console.log("WORKPLAN DEBUG: Using extracted date range:", firstDate, "to", lastDate);

                             // Set the date values
                             if (firstDate) {
                                 console.log("WORKPLAN DEBUG: Initializing Work Plan table with dates:", firstDate, lastDate);

                                 startDateEl.value = firstDate;
                                 console.log("WORKPLAN DEBUG: Set start_date to:", firstDate, "Value is now:", startDateEl.value);

                                 endDateEl.value = lastDate;
                                 console.log("WORKPLAN DEBUG: Set end_date to:", lastDate, "Value is now:", endDateEl.value);

                                 // Trigger change events to ensure any listeners are notified
                                 startDateEl.dispatchEvent(new Event('change'));
                                 endDateEl.dispatchEvent(new Event('change'));
                             }
                         }
                     } catch (e) {
                         console.error("WORKPLAN DEBUG: Error extracting dates from workplan data:", e);
                     }
                 }

                 // Check again if we have date values
                 if (!startDateEl.value || !endDateEl.value) {
                     console.log("WORKPLAN DEBUG: Missing start or end date values. Start date:", startDateEl.value, "End date:", endDateEl.value);
                     return;
                 }

                 console.log("WORKPLAN DEBUG: Proceeding with dates:", startDateEl.value, "to", endDateEl.value);

                 // Generate date range
                 const startDate = new Date(startDateEl.value);
                 const endDate = new Date(endDateEl.value);

                 // Create an array of all dates within the range
                 const dateRange = [];
                 const currentDate = new Date(startDate);
                 while (currentDate <= endDate) {
                     dateRange.push(new Date(currentDate).toISOString().split('T')[0]);
                     currentDate.setDate(currentDate.getDate() + 1);
                 }
                 console.log("WORKPLAN DEBUG: Generated date range:", dateRange);

                 // Clear existing rows
                 const workplanTable = document.getElementById('workplan_table');
                 const tbody = workplanTable.querySelector('tbody');
                 tbody.innerHTML = '';

                 // Process each activity in the workplan data
                 workplanData.forEach((activityData, index) => {
                     console.log(`WORKPLAN DEBUG: Processing activity ${index+1}:`, activityData);

                     // Skip if the activity data is not valid
                     if (!activityData || (!Array.isArray(activityData) && typeof activityData !== 'object')) {
                         console.warn(`WORKPLAN DEBUG: Invalid activity data at index ${index}:`, activityData);
                         return;
                     }

                     // Extract activity name and checked dates
                     let activityName = '';
                     let checkedDates = [];

                     if (Array.isArray(activityData)) {
                         // Handle [activity, dates] format
                         activityName = activityData[0] || '';
                         checkedDates = Array.isArray(activityData[1]) ? activityData[1] : [];
                     } else {
                         // Handle {activity: "name", days: ["2023-01-01", ...]} format
                         activityName = activityData.activity || '';
                         checkedDates = Array.isArray(activityData.days) ? activityData.days : [];
                     }

                     // Skip if there's no activity name
                     if (!activityName) {
                         console.warn(`WORKPLAN DEBUG: Empty activity name at index ${index}, skipping`);
                         return;
                     }

                     console.log(`WORKPLAN DEBUG: Activity ${index+1} - Name: "${activityName}", Dates:`, checkedDates);

                     // Create a new row for this activity
                     const tr = addWorkplanRow(index === 0); // First row doesn't have delete button

                     // Set the activity name
                     const nameInput = tr.querySelector('input[name*="workplan"][name*="activity"]');
                     if (nameInput) {
                         nameInput.value = activityName;
                         nameInput.dataset.index = index;
                         nameInput.name = `workplan[${index}][activity]`;
                     }

                     // Get the column headers to match dates
                     const thead = document.querySelector('#workplan_table thead tr');
                     const headerCells = thead.querySelectorAll('th');

                     // Check the appropriate checkboxes
                     const checkboxes = tr.querySelectorAll('input[type="checkbox"]');
                     checkboxes.forEach((checkbox, dayIndex) => {
                         // We need to match the date from the header with our stored dates
                         // Skip the first cell which is the activity name header
                         if (dayIndex >= headerCells.length - 1) return;

                         const headerCell = headerCells[dayIndex + 1]; // +1 to skip the first header (Activity Name)
                         const headerTitle = headerCell.title; // This contains the full date, e.g., "April 3, 2025"

                         // Extract the ISO date from the header title
                         let dateMatch = null;
                         if (headerTitle) {
                             const match = headerTitle.match(/([a-zA-Z]+)\s+(\d+),\s+(\d{4})/);
                             if (match) {
                                 const monthName = match[1];
                                 const day = match[2];
                                 const year = match[3];

                                 // Convert month name to month number (0-11)
                                 const monthNames = [
                                     "January", "February", "March", "April", "May", "June",
                                     "July", "August", "September", "October", "November", "December"
                                 ];
                                 const monthIndex = monthNames.findIndex(m => m === monthName);

                                 if (monthIndex !== -1) {
                                     // Fix timezone issues by using explicit date formatting instead of toISOString()
                                     // Format YYYY-MM-DD manually to avoid timezone issues
                                     const yyyy = parseInt(year);
                                     // Month index is 0-11, so add 1 to get 1-12, then pad with leading zero if needed
                                     const mm = String(monthIndex + 1).padStart(2, '0');
                                     // pad day with leading zero if needed
                                     const dd = String(parseInt(day)).padStart(2, '0');

                                     dateMatch = `${yyyy}-${mm}-${dd}`;
                                     console.log(`WORKPLAN DEBUG: Matched header "${headerTitle}" to ISO date: ${dateMatch}`);
                                 }
                             }
                         }

                         // If we couldn't extract from title, use the date from our range
                         if (!dateMatch && dayIndex < dateRange.length) {
                             dateMatch = dateRange[dayIndex];
                         }

                         // Check if this date is in the checked dates array
                         if (dateMatch) {
                             const isChecked = checkedDates.includes(dateMatch);
                             console.log(`WORKPLAN DEBUG: Checkbox for date ${dateMatch} - Should be checked: ${isChecked}`);

                             // Set the checked property
                             checkbox.checked = isChecked;

                             // Also set the checked attribute if checked (for visibility in DOM)
                             if (isChecked) {
                                 checkbox.setAttribute('checked', 'checked');
                                 console.log(`WORKPLAN DEBUG: Checked checkbox for activity ${index+1}, date ${dateMatch}`);
                             }

                             // Force a DOM update by triggering a change event
                             checkbox.dispatchEvent(new Event('change'));

                             // Set the name attribute
                             checkbox.name = `workplan[${index}][days][${dayIndex}]`;
                         }
                     });
                 });

                 // Ensure we validate the workplan section after filling
                 validateWorkPlanSection();
                 console.log("WORKPLAN DEBUG: Finished filling workplan section");

                 // Add a delay before checking workplan checkboxes again
                 setTimeout(() => checkWorkplanCheckboxes(workplanData), 1000);
             }

             // Function to fill Monitoring section
             function fillMonitoringSection(monitoringData) {
                 if (!monitoringData || !Array.isArray(monitoringData) || monitoringData.length === 0) return;

                 const monitoringContainer = document.getElementById('monitoring_container');
                 if (!monitoringContainer) return;

                 // Get the template item (first monitoring item)
                 const templateItem = monitoringContainer.querySelector('.monitoring-item');
                 if (!templateItem) return;

                 // Clear existing monitoring items except the template
                 const existingItems = monitoringContainer.querySelectorAll('.monitoring-item');
                 for (let i = 1; i < existingItems.length; i++) {
                     monitoringContainer.removeChild(existingItems[i]);
                 }

                 // Fill the first monitoring item with data
                 if (monitoringData.length > 0 && monitoringData[0]) {
                     const firstItem = templateItem;
                     fillMonitoringItem(firstItem, monitoringData[0], 0);
                 }

                 // Add additional monitoring items for remaining data
                 for (let i = 1; i < monitoringData.length; i++) {
                     if (monitoringData[i]) {
                         // Clone the template and fill it
                         const newItem = templateItem.cloneNode(true);
                         fillMonitoringItem(newItem, monitoringData[i], i);

                         // Show remove button for additional items
                         const removeBtn = newItem.querySelector('.remove-monitoring-row');
                         if (removeBtn) {
                             removeBtn.classList.remove('d-none');
                         }

                         monitoringContainer.appendChild(newItem);
                     }
                 }
             }

             // Helper function to fill a single monitoring item
             function fillMonitoringItem(monitoringItem, monitoringData, index) {
                 console.log(`Filling monitoring item ${index+1} with data:`, monitoringData);

                 // Update title
                 const title = monitoringItem.querySelector('.card-title');
                 if (title) {
                     title.textContent = `Monitoring Item ${index + 1}`;
                 }

                 // Set data-index attribute
                 monitoringItem.dataset.index = index;

                 // Create an array of field definitions for easier processing
                 const fields = [{
                         name: 'objectives',
                         defaultValue: ''
                     },
                     {
                         name: 'performance_indicators',
                         defaultValue: ''
                     },
                     {
                         name: 'baseline_data',
                         defaultValue: ''
                     },
                     {
                         name: 'performance_target',
                         defaultValue: ''
                     },
                     {
                         name: 'data_source',
                         defaultValue: ''
                     },
                     {
                         name: 'collection_method',
                         defaultValue: ''
                     },
                     {
                         name: 'frequency',
                         defaultValue: ''
                     },
                     {
                         name: 'responsible',
                         defaultValue: ''
                     }
                 ];

                 // Check if monitoringData is an array (old format) or object (new format)
                 if (Array.isArray(monitoringData)) {
                     // Handle array format [objectives, performance_indicators, baseline_data, performance_target, data_source, collection_method, frequency, responsible]
                     fields.forEach((field, fieldIndex) => {
                         const element = monitoringItem.querySelector(`textarea[name^="monitoring"][name$="[${field.name}]"]`);
                         if (element) {
                             // Use the data if available, otherwise use default empty value
                             element.value = monitoringData[fieldIndex] !== undefined ? monitoringData[fieldIndex] : field.defaultValue;
                             element.name = `monitoring[${index}][${field.name}]`;

                             console.log(`Set monitoring[${index}][${field.name}] to: ${element.value}`);
                         } else {
                             console.warn(`Could not find field ${field.name} for monitoring item ${index+1}`);
                         }
                     });
                 } else {
                     // Handle object format with field names as keys
                     fields.forEach(field => {
                         const element = monitoringItem.querySelector(`textarea[name^="monitoring"][name$="[${field.name}]"]`);
                         if (element) {
                             // Use the data if available, otherwise use default empty value
                             element.value = monitoringData[field.name] || field.defaultValue;
                             element.name = `monitoring[${index}][${field.name}]`;

                             console.log(`Set monitoring[${index}][${field.name}] to: ${element.value}`);
                         } else {
                             console.warn(`Could not find field ${field.name} for monitoring item ${index+1}`);
                         }
                     });
                 }

                 console.log(`Finished filling monitoring item ${index+1}`);
             }

             // Helper function to ensure workplan checkboxes are checked
             function checkWorkplanCheckboxes(workplanData) {
                 console.log("WORKPLAN DEBUG: Running additional checkbox check");

                 try {
                     const thead = document.querySelector('#workplan_table thead tr');
                     const headerCells = thead.querySelectorAll('th');
                     const tbody = document.querySelector('#workplan_table tbody');
                     const rows = tbody.querySelectorAll('tr');

                     // For each activity row
                     workplanData.forEach((activityData, activityIndex) => {
                         if (activityIndex >= rows.length) return;

                         const tr = rows[activityIndex];

                         // Extract checked dates
                         let checkedDates = [];
                         if (Array.isArray(activityData) && activityData.length > 1 && Array.isArray(activityData[1])) {
                             checkedDates = activityData[1];
                         } else if (activityData.days && Array.isArray(activityData.days)) {
                             checkedDates = activityData.days;
                         }

                         if (checkedDates.length === 0) return;

                         console.log(`WORKPLAN DEBUG [Additional]: Processing row ${activityIndex+1} with dates:`, checkedDates);

                         // For each checkbox in the row
                         const checkboxes = tr.querySelectorAll('input[type="checkbox"]');

                         checkboxes.forEach((checkbox, columnIndex) => {
                             if (columnIndex >= headerCells.length - 1) return;

                             const headerCell = headerCells[columnIndex + 1]; // +1 to skip first column
                             const headerTitle = headerCell.title; // e.g., "April 3, 2025"

                             if (!headerTitle) return;

                             // Extract date from header title
                             const match = headerTitle.match(/([a-zA-Z]+)\s+(\d+),\s+(\d{4})/);
                             if (!match) return;

                             const monthName = match[1];
                             const day = match[2];
                             const year = match[3];

                             // Convert month name to number
                             const monthNames = [
                                 "January", "February", "March", "April", "May", "June",
                                 "July", "August", "September", "October", "November", "December"
                             ];
                             const monthIndex = monthNames.findIndex(m => m === monthName);

                             if (monthIndex === -1) return;

                             // Format YYYY-MM-DD manually
                             const yyyy = parseInt(year);
                             const mm = String(monthIndex + 1).padStart(2, '0');
                             const dd = String(parseInt(day)).padStart(2, '0');

                             const dateISO = `${yyyy}-${mm}-${dd}`;

                             // Check if this date should be checked
                             const shouldBeChecked = checkedDates.includes(dateISO);

                             if (shouldBeChecked) {
                                 console.log(`WORKPLAN DEBUG [Additional]: Setting checkbox checked for row ${activityIndex+1}, date ${dateISO}`);
                                 // Use jQuery-style checkbox checking for maximum compatibility
                                 checkbox.checked = true;
                                 checkbox.setAttribute('checked', 'checked');
                             }
                         });
                     });

                     console.log("WORKPLAN DEBUG: Additional checkbox checking completed");
                 } catch (e) {
                     console.error("WORKPLAN DEBUG: Error in additional checkbox checking:", e);
                 }
             }

             // Function to clear the workplan table
             function clearWorkplanTable() {
                 console.log("Clearing workplan table...");

                 // Clear start and end date inputs
                 const startDateEl = document.getElementById('start_date');
                 const endDateEl = document.getElementById('end_date');

                 if (startDateEl) startDateEl.value = '';
                 if (endDateEl) endDateEl.value = '';

                 // Clear table headers (except first column)
                 const thead = document.querySelector('#workplan_table thead tr');
                 if (thead) {
                     // Keep only the first cell (Activity Name)
                     while (thead.cells.length > 1) {
                         thead.deleteCell(1);
                     }
                 }

                 // Clear table rows
                 const tbody = document.querySelector('#workplan_table tbody');
                 if (tbody) {
                     tbody.innerHTML = '';

                     // Add a single empty row
                     addWorkplanRow(true);
                 }

                 console.log("Workplan table cleared");
             }

             // Function to clear all validation indicators
             function clearAllValidationIndicators() {
                 console.log("Clearing all validation indicators");

                 // Clear section complete/incomplete classes
                 document.querySelectorAll('.section').forEach(section => {
                     section.classList.remove('complete', 'incomplete', 'highlight-invalid');
                 });

                 // Clear field validation classes
                 document.querySelectorAll('.field-invalid').forEach(field => {
                     field.classList.remove('field-invalid');
                     if (field.style) {
                         field.style.removeProperty('background-image');
                         field.style.removeProperty('background-color');
                         field.style.borderColor = '';
                         field.style.paddingRight = '';
                     }
                 });

                 // Reset any special styling on form elements
                 document.querySelectorAll('input, textarea, select').forEach(element => {
                     element.classList.remove('is-invalid', 'is-valid');
                     if (element.style) {
                         element.style.borderColor = '';
                         element.style.backgroundColor = '';
                     }
                 });

                 console.log("All validation indicators cleared");
             }
         </script>
 </body>

 </html>