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
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPAs Forms - GAD System</title>
    <link rel="icon" type="image/x-icon" href="../images/Batangas_State_Logo.ico">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css"> <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="../css/custom.css">
    <style>
        /* Custom Source Fund Selector Styles */
        .source-fund-container {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            height: 120px;
            /* Fixed height of 120px as requested */
            overflow-y: hidden;
            padding: 0.375rem 0;
            background-color: #fff;
        }

        .source-fund-option {
            padding: 0.375rem 0.75rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .source-fund-option:hover {
            background-color: #f8f9fa;
        }

        .source-fund-option.selected {
            background-color: var(--accent-color);
            color: white;
            font-weight: 500;
        }

        .source-fund-option.selected::before {
            content: "✓ ";
            color: white;
        }

        /* Dark mode styles */
        [data-bs-theme="dark"] .source-fund-container {
            background-color: #2B3035;
            border-color: #495057;
        }

        [data-bs-theme="dark"] .source-fund-option {
            color: #f8f9fa;
        }

        [data-bs-theme="dark"] .source-fund-option:hover {
            background-color: #495057;
        }

        [data-bs-theme="dark"] .source-fund-option.selected {
            background-color: var(--accent-color);
            color: white;
        }

        [data-bs-theme="dark"] .source-fund-option.selected::before {
            color: white;
        }

        /* Financial Plan Table Styles */
        #financialPlanTable {
            border-collapse: separate;
            border-spacing: 0;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        #financialPlanTable thead {
            background-color: var(--accent-color);
            color: white;
        }

        #financialPlanTable thead th {
            border: none;
            padding: 1rem 0.75rem;
        }

        #financialPlanTable tbody td {
            padding: 0.75rem;
            vertical-align: middle;
            border-color: rgba(0, 0, 0, 0.15);
            background-color: #fff;
        }

        #financialPlanTable tbody tr:nth-child(even) td {
            background-color: rgba(0, 0, 0, 0.02);
        }

        #financialPlanTable tbody tr:hover {
            background-color: rgba(var(--accent-color-rgb), 0.05);
        }

        #financialPlanTable .form-control {
            border: 1px solid rgba(0, 0, 0, 0.2);
        }

        #financialPlanTable .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(var(--accent-color-rgb), 0.25);
        }

        #financialPlanTable .input-group-text {
            background-color: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }

        #financialPlanTable .item-total-cost {
            font-weight: 500;
            color: var(--accent-color);
        }

        #financialPlanTable .btn-remove-item {
            border-radius: 50%;
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            color: var(--accent-color);
            border-color: var(--accent-color);
        }

        #financialPlanTable .btn-remove-item:hover {
            background-color: var(--accent-color);
            color: white;
        }

        #financialPlanTable tfoot {
            border-top: 2px solid rgba(var(--accent-color-rgb), 0.3);
            background-color: rgba(0, 0, 0, 0.03);
        }

        #financialPlanTable tfoot td {
            padding: 1rem 0.75rem;
        }

        #grandTotalCost {
            color: var(--accent-color);
            font-size: 1.1em;
        }

        /* Dark mode table styles */
        [data-bs-theme="dark"] #financialPlanTable {
            border-color: #495057;
        }

        [data-bs-theme="dark"] #financialPlanTable tbody td {
            border-color: rgba(255, 255, 255, 0.08);
            background-color: #2B3035;
        }

        [data-bs-theme="dark"] #financialPlanTable tbody tr:nth-child(even) td {
            background-color: #343a40;
        }

        [data-bs-theme="dark"] #financialPlanTable tbody tr:hover td {
            background-color: rgba(156, 39, 176, 0.1);
        }

        [data-bs-theme="dark"] #financialPlanTable tfoot {
            border-top: 2px solid rgba(255, 255, 255, 0.1);
            background-color: #343a40;
        }

        [data-bs-theme="dark"] #financialPlanTable .form-control {
            background-color: #2B3035;
            border-color: #495057;
            color: #f8f9fa;
        }

        [data-bs-theme="dark"] #financialPlanTable .form-control:focus {
            background-color: #3a4147;
            border-color: var(--accent-color);
            color: #fff;
        }

        /* Autocomplete dropdown styling */
        .position-relative {
            position: relative;
        }

        .autocomplete-dropdown {
            position: absolute;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 0 0 4px 4px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
        }

        .autocomplete-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
            color: var(--accent-color);
        }

        .autocomplete-item:last-child {
            border-bottom: none;
        }

        .autocomplete-item:hover {
            background-color: #f5f5f5;
        }

        .autocomplete-item.selected {
            background-color: #e9f2fb;
        }

        .autocomplete-input:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Dark mode styles for autocomplete */
        [data-bs-theme="dark"] .autocomplete-dropdown,
        .dark-mode .autocomplete-dropdown {
            background-color: #212529;
            border-color: #444;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        [data-bs-theme="dark"] .autocomplete-item,
        .dark-mode .autocomplete-item {
            color: var(--accent-color);
            border-bottom-color: #444;
        }

        [data-bs-theme="dark"] .autocomplete-item:hover,
        .dark-mode .autocomplete-item:hover {
            background-color: #2c3034;
        }

        [data-bs-theme="dark"] .autocomplete-item.selected,
        .dark-mode .autocomplete-item.selected {
            background-color: #343a40;
        }

        /* Force dark mode table header - stronger rule */
        [data-bs-theme="dark"] #ppasEntriesModal .table-light,
        [data-bs-theme="dark"] #ppasEntriesModal thead.table-light,
        [data-bs-theme="dark"] #ppasEntriesModal .table-light th {
            background-color: var(--bg-secondary) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
        }

        /* Ensure sticky header stays visible with correct colors */
        [data-bs-theme="dark"] #ppasEntriesModal .sticky-top {
            background-color: var(--bg-secondary) !important;
            border-bottom: 1px solid var(--border-color) !important;
        }

        /* Match GBP entries modal size */
        #ppasEntriesModal.modal .modal-dialog.modal-xl {
            max-width: 1400px !important;
            width: 100%;
            margin: 1.75rem auto;
        }

        #ppasEntriesModal .modal-content {
            min-height: 670px;
            border-radius: 16px;
            display: flex;
            flex-direction: column;
        }

        #ppasEntriesModal .modal-body {
            display: flex;
            flex-direction: column;
            padding: 20px;
            padding-bottom: 0;
            height: 600px;
            overflow-y: auto;
        }

        #ppasEntriesModal .table-container {
            min-height: 340px;
            overflow: hidden;
        }

        /* Fixed page navigation at bottom of modal */
        #ppasEntriesModal .pagination-container {
            margin-top: 25px;
            padding: 15px 0;
            border-top: 1px solid var(--border-color);
            background-color: var(--bg-secondary);
            border-radius: 0 0 16px 16px;
            width: 100%;
            margin-bottom: 0;
            /* Ensure no bottom margin */
        }

        /* Modal title styling */
        #ppasEntriesModalLabel {
            font-weight: bold;
            text-align: center;
            color: var(--accent-color);
        }
    </style>
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
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
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
            --readonly-bg: #EDF0F2;
            --readonly-border: #dee2e6;
            --readonly-text: #6c757d;
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
            --readonly-border: #444444;
            --readonly-text: #aaaaaa;
            --dark-bg: #212529;
            --dark-input: #2b3035;
            --dark-text: #e9ecef;
            --dark-border: #495057;
            --dark-sidebar: #2d2d2d;
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

        /* Light Mode Readonly Field Styling */
        input[readonly],
        select[readonly],
        textarea[readonly] {
            background-color: var(--readonly-bg) !important;
            border: 1px dashed var(--readonly-border) !important;
            color: var(--readonly-text) !important;
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

        /* Dark mode specific styling for readonly/disabled fields */
        [data-bs-theme="dark"] input:disabled,
        [data-bs-theme="dark"] select:disabled,
        [data-bs-theme="dark"] textarea:disabled,
        [data-bs-theme="dark"] button:disabled,
        [data-bs-theme="dark"] input[readonly],
        [data-bs-theme="dark"] select[readonly],
        [data-bs-theme="dark"] textarea[readonly] {
            background-color: #37383A !important;
            border: 1px dashed #444444 !important;
            color: #aaaaaa !important;
        }

        /* Ensure disabled dropdowns have proper styling */
        select:disabled {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236c757d' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 16px 12px !important;
            appearance: none !important;
        }

        /* Form field styling */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--input-bg);
            color: var(--input-text);
            transition: border-color 0.2s ease;
            padding: 10px 12px;
        }

        /* Focus styling */
        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25);
        }

        /* Textarea styling */
        textarea.form-control {
            height: auto;
            min-height: 100px;
        }

        /* Modern multi-select styling */
        select[multiple] {
            padding: 8px;
            min-height: 120px;
            background-image: none !important;
        }

        select[multiple] option {
            padding: 8px 12px;
            margin-bottom: 4px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        select[multiple] option:checked {
            background-color: var(--accent-color) !important;
            color: white !important;
            font-weight: 500;
        }

        select[multiple] option:hover:not(:checked) {
            background-color: rgba(106, 27, 154, 0.1) !important;
            color: var(--text-primary);
        }

        [data-bs-theme="dark"] select[multiple] option:hover:not(:checked) {
            background-color: rgba(156, 39, 176, 0.2) !important;
        }

        /* Smaller multi-select for fields with few options */
        select[multiple].small-select {
            min-height: auto;
            height: auto;
            overflow-y: hidden;
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
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25) !important;
        }

        .input-with-currency .form-control:focus {
            box-shadow: none !important;
            border-color: transparent !important;
        }

        /* Dark mode specific styling for currency input */
        [data-bs-theme="dark"] .input-with-currency {
            border-color: #404040;
            background-color: #2B3035;
        }

        [data-bs-theme="dark"] .input-with-currency::before {
            color: #ffffff;
            border-color: #404040;
            background-color: #37383A;
        }

        /* Add form sectioning styles */
        .form-section {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .form-section.active {
            display: block;
        }

        /* Project Team Card Styling */
        .team-member-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            position: relative;
            min-height: 400px;
            overflow-y: auto;
        }

        /* Error message styling for dynamic input fields */
        .input-group+.invalid-feedback {
            display: block;
            margin-top: -0.5rem;
            margin-bottom: 1rem;
        }

        /* Ensure proper spacing between input groups when validation messages are present */
        .input-group {
            margin-bottom: 0.5rem;
        }

        .team-member-card .card-header {
            position: sticky;
            top: 0;
            z-index: 1;
            background: var(--card-bg);
            border-top-left-radius: 15px !important;
            border-top-right-radius: 15px !important;
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .team-member-card .card-body {
            padding: 15px;
        }

        /* Dark mode team card */
        [data-bs-theme="dark"] .team-member-card {
            background-color: var(--dark-sidebar) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .team-member-card .card-header {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Navigation Header Styles */
        .form-nav {
            display: flex;
            margin-bottom: 25px;
            overflow: hidden;
            position: relative;
            z-index: 1;
            padding-right: 0;
            width: 100%;
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

        .nav-section-group {
            display: flex;
            align-items: center;
            width: 100%;
            position: relative;
            padding-right: 0;
            /* No need for padding since we use a container */
        }

        .nav-items-container {
            display: flex;
            align-items: center;
            flex-wrap: nowrap;
            flex-grow: 1;
            margin-right: 50px;
            /* Space for the arrow */
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

        .form-nav-item:last-of-type {
            margin-right: 0;
        }

        .form-nav-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--accent-color);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 10;
            position: absolute;
        }

        .form-nav-arrow:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            background-color: var(--accent-hover);
        }

        #nav-prev-group {
            margin-right: 15px;
            position: static;
        }

        #nav-next-group {
            right: 0;
            top: 50%;
            transform: translateY(-50%);
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-form-nav.btn-prev {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .form-group {
            margin-bottom: 1.5rem;
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
            margin-top: 15px;
            margin-bottom: 0
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
            backdrop-filter: blur(8px);
        }

        /* Special styling for the duplicate personnel modal */
        .swal2-popup.swal2-modal.swal2-icon-warning {
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Add animation for the duplicate warning */
        @keyframes warningPulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .swal2-icon.swal2-warning {
            animation: warningPulse 1s infinite;
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
            background-color: var(--accent-color) !important;
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
                    rgba(255, 255, 255, 0) 40%,
                    rgba(255, 255, 255, 0.3) 50%,
                    rgba(255, 255, 255, 0) 60%);
            background-size: 200% 100%;
            animation: approvalShine 2s infinite;
        }

        @keyframes approvalShine {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .approval-link.active i {
            transform: scale(1.2);
            color: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1.2);
            }

            50% {
                transform: scale(1.4);
            }

            100% {
                transform: scale(1.2);
            }
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

        /* Responsive styles for form navigation */
        @media (max-width: 768px) {
            .form-nav {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 10px;
                -webkit-overflow-scrolling: touch;
            }

            .form-nav-item {
                font-size: 0.9rem;
                flex: 0 0 auto;
                padding: 8px 5px;
                margin-right: 15px;
            }

            .form-nav-item .step-number {
                width: 24px;
                height: 24px;
                font-size: 12px;
            }
        }

        @media (max-width: 576px) {
            .form-nav-item {
                font-size: 0.8rem;
                margin-right: 10px;
            }

            .action-buttons-container {
                flex-direction: column;
                gap: 10px;
            }

            .btn-form-nav {
                width: 100%;
            }
        }

        /* Info text below fields (typically appears with readonly fields) */
        .info-text {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: var(--text-secondary);
            font-style: italic;
        }

        /* Form row for horizontally aligned form groups */
        .row {
            margin-bottom: 0.5rem;
        }

        /* Properly space form sections */
        .form-sections-container {
            margin-bottom: 2rem;
        }

        /* Dark Mode Input Field Styling */
        [data-bs-theme="dark"] .form-control:not(:disabled):not([readonly]),
        [data-bs-theme="dark"] .form-select:not(:disabled):not([readonly]) {
            background-color: #2B3035;
            border-color: #404040;
            color: #ffffff;
        }

        /* Focus styling for all interactive elements */
        .form-control:focus,
        .form-select:focus,
        button:focus,
        a:focus,
        .btn:focus,
        .nav-link:focus,
        .btn-form-nav:focus,
        .btn-icon:focus,
        input:focus,
        select:focus,
        textarea:focus,
        .dropdown-item:focus,
        .form-nav-item:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25) !important;
            outline: none !important;
        }

        /* Ensure all focus states use accent-color */
        *:focus {
            outline-color: var(--accent-color) !important;
        }

        /* Special focus styling for currency input */
        .input-with-currency:focus-within {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25) !important;
        }

        .input-with-currency .form-control:focus {
            box-shadow: none !important;
            border-color: transparent !important;
        }

        /* Form validation styles */
        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }

        .form-control.is-invalid,
        .form-select.is-invalid,
        .input-with-currency.is-invalid,
        .modern-options-container.is-invalid {
            border-color: #dc3545 !important;
        }

        .form-control.is-invalid:focus,
        .form-select.is-invalid:focus,
        .input-with-currency.is-invalid:focus-within {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
        }

        /* Checkbox styling to use accent color */
        .form-check-input:checked {
            background-color: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
        }

        .form-check-input:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25) !important;
        }

        /* Navigation validation status */
        .form-nav-item.has-error .step-number {
            background-color: #dc3545 !important;
            color: white !important;
        }

        .form-nav-item.has-error {
            color: #dc3545 !important;
        }

        .form-nav-item.is-complete .step-number {
            background-color: #198754 !important;
            color: white !important;
        }

        .form-nav-item.is-complete {
            color: #198754 !important;
        }

        .section-title.has-error {
            color: #dc3545 !important;
        }

        /* Modern radio buttons styled as checkboxes */
        .modern-options-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Error state for modern-options-container, form-group, and input-group */
        .modern-options-container.is-invalid,
        .form-group.is-invalid,
        .input-group.is-invalid {
            padding: 15px;
            border: 1px solid #dc3545;
            border-radius: 8px;
        }

        .modern-option {
            position: relative;
            padding-left: 40px;
            cursor: pointer;
            display: flex;
            align-items: flex-start;
            margin-bottom: 5px;
            transition: transform 0.15s ease-in-out;
        }

        .modern-option:hover {
            transform: translateX(3px);
        }

        .modern-radio {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .modern-option-label {
            font-weight: 400;
            cursor: pointer;
            padding: 0.3rem 0;
            user-select: none;
            font-size: 1rem;
            position: relative;
            color: var(--text-primary);
            line-height: 1.4;
            transition: color 0.2s ease;
        }

        .modern-option:hover .modern-option-label {
            color: var(--accent-color);
        }

        .custom-radio {
            position: absolute;
            top: 0.3rem;
            left: -40px;
            height: 26px;
            width: 26px;
            background-color: var(--input-bg);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Hover effects for both light and dark themes */
        .modern-option:hover .custom-radio {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(106, 27, 154, 0.1);
            background-color: rgba(106, 27, 154, 0.03);
        }

        /* Checked state */
        .modern-radio:checked~.modern-option-label .custom-radio {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            box-shadow: 0 2px 5px rgba(106, 27, 154, 0.3);
            transform: scale(1.05);
        }

        .modern-radio:checked~.modern-option-label {
            font-weight: 500;
            color: var(--accent-color);
        }

        /* Checkmark indicator */
        .custom-radio:after {
            content: "";
            position: absolute;
            display: none;
            left: 9px;
            top: 5px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            transition: all 0.1s ease-in-out;
        }

        /* Show checkmark when checked */
        .modern-radio:checked~.modern-option-label .custom-radio:after {
            display: block;
        }

        /* Focus state for accessibility */
        .modern-radio:focus~.modern-option-label .custom-radio {
            box-shadow: 0 0 0 3px rgba(106, 27, 154, 0.25);
        }

        /* Disabled state */
        .modern-radio:disabled~.modern-option-label {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .modern-radio:disabled~.modern-option-label .custom-radio {
            background-color: var(--readonly-bg);
            border-color: var(--readonly-border);
        }

        /* Adjust modern radio styling to work with checkboxes too */
        .modern-radio[type="checkbox"]:checked~.modern-option-label .custom-radio:after {
            display: block;
        }

        /* Allow multiple selections for checkboxes */
        .modern-radio[type="checkbox"]:checked~.modern-option-label {
            font-weight: 500;
            color: var(--accent-color);
        }

        /* Add form sectioning styles */
        .add-field-btn {
            color: var(--accent-color);
            background-color: transparent;
            border: 2px dotted var(--accent-color);
            font-size: 0.9rem;
            padding: 6px 15px;
            transition: all 0.2s ease;
        }

        .add-field-btn:hover {
            background-color: rgba(106, 27, 154, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1);
        }

        /* Dark theme styling for add-field-btn */
        [data-bs-theme="dark"] .add-field-btn {
            border-color: var(--accent-color);
            color: var(--accent-color);
        }

        [data-bs-theme="dark"] .add-field-btn:hover {
            background-color: rgba(156, 39, 176, 0.2);
        }

        /* Numbered input fields styling */
        .numbered-input-container {
            position: relative;
        }

        .input-number-indicator {
            position: absolute;
            left: 0;
            top: 0;
            height: 46px;
            width: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--accent-color);
            color: white;
            font-weight: bold;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
            font-size: 0.9rem;
            z-index: 1;
            /* Ensure it's above other elements */
        }

        /* Add styling for remove buttons to match height */
        .input-group .btn-outline-danger.remove-input {
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .numbered-input {
            padding-left: 46px !important;
        }

        /* Fix validation message positioning for numbered inputs */
        .numbered-input-container+.invalid-feedback,
        .input-group .numbered-input-container+.invalid-feedback,
        .input-group-container .invalid-feedback {
            display: block;
            margin-top: 5px;
            position: relative;
            z-index: 0;
        }

        /* Dark theme adjustment */
        [data-bs-theme="dark"] .input-number-indicator {
            background-color: var(--accent-color);
        }

        /* ... existing code ... */
        .ui-autocomplete {
            max-height: 250px;
            overflow-y: auto;
            overflow-x: hidden;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
            border: 1px solid var(--border-color);
            padding: 8px;
            background: var(--input-bg);
            z-index: 9999 !important;
        }

        .ui-autocomplete .ui-menu-item {
            padding: 0;
            margin-bottom: 4px;
        }

        .ui-autocomplete .ui-menu-item:last-child {
            margin-bottom: 0;
        }

        .ui-autocomplete .ui-menu-item .ui-menu-item-wrapper {
            padding: 0;
            border: none !important;
            outline: none !important;
        }

        .ui-autocomplete .ui-menu-item .ui-menu-item-wrapper.ui-state-active {
            border: none !important;
            margin: 0 !important;
            outline: none !important;
        }

        .ui-autocomplete .autocomplete-item {
            display: flex;
            flex-direction: column;
            padding: 10px 14px;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .ui-autocomplete .ui-state-active .autocomplete-item,
        .ui-autocomplete .autocomplete-item:hover {
            background-color: var(--accent-color);
            color: white;
            border-left: 3px solid var(--accent-color);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .ui-autocomplete .personnel-name {
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 0.95rem;
            letter-spacing: 0.01rem;
        }

        .ui-autocomplete .personnel-rank {
            font-size: 0.85rem;
            opacity: 0.85;
            letter-spacing: 0.01rem;
        }

        .ui-helper-hidden-accessible {
            display: none;
        }

        [data-bs-theme="dark"] .ui-autocomplete {
            background-color: var(--dark-input);
            border-color: var(--dark-border);
        }

        [data-bs-theme="dark"] .ui-autocomplete .ui-menu-item {
            color: var(--dark-text);
        }

        [data-bs-theme="dark"] .ui-autocomplete .ui-state-active .autocomplete-item,
        [data-bs-theme="dark"] .ui-autocomplete .autocomplete-item:hover {
            background-color: var(--accent-color);
            color: white;
        }

        [data-bs-theme="dark"] .ui-autocomplete .autocomplete-item {
            border-left: 3px solid rgba(255, 255, 255, 0.05);
        }

        /* Remove focus outlines and borders */
        .ui-front {
            z-index: 9999 !important;
        }

        .ui-menu,
        .ui-widget,
        .ui-widget-content,
        .ui-corner-all {
            border: 1px solid var(--border-color) !important;
            outline: none !important;
        }

        .ui-state-focus,
        .ui-state-active {
            border: none !important;
            outline: none !important;
            margin: 0 !important;
        }

        .ui-autocomplete .autocomplete-item {
            display: flex;
            flex-direction: column;
            padding: 10px 14px;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .ui-autocomplete .ui-state-active .autocomplete-item,
        .ui-autocomplete .autocomplete-item:hover {
            background-color: var(--accent-color);
            color: white;
            border-left: 3px solid var(--accent-color);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .ui-autocomplete .personnel-top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }

        .ui-autocomplete .personnel-bottom-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .ui-autocomplete .personnel-name {
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.01rem;
        }

        .ui-autocomplete .personnel-gender {
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 10px;
            background-color: rgba(0, 0, 0, 0.05);
        }

        .ui-autocomplete .ui-state-active .personnel-gender,
        .ui-autocomplete .autocomplete-item:hover .personnel-gender {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .ui-autocomplete .personnel-rank {
            font-size: 0.85rem;
            opacity: 0.85;
            letter-spacing: 0.01rem;
        }

        /* Work Plan Timeline checkbox styling */
        .checkbox-cell {
            vertical-align: middle;
            text-align: center;
        }

        .checkbox-cell input[type="checkbox"] {
            accent-color: var(--accent-color);
            width: 18px;
            height: 18px;
        }

        [data-bs-theme="dark"] .nav-link.active {
            color: #9c27b0;
        }

        /* Modal styles */
        .modal-backdrop.show {
            opacity: 0.7;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            background-color: var(--card-bg);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 20px 25px;
            background-color: var(--bg-secondary);
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .modal-header .btn-close {
            padding: 10px;
            margin: -10px -10px -10px auto;
            background-color: transparent;
            border: none;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        /* Fixed page navigation at bottom of modal */
        .pagination-container {
            padding: 15px 0;
            border-top: 1px solid var(--border-color);
            margin-top: 10px;
        }

        .pagination .page-item .page-link {
            border: none;
            margin: 0 3px;
            min-width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: var(--text-primary);
            background-color: var(--bg-secondary);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--accent-color);
            color: white;
        }

        .table-container {
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .table {
            margin-bottom: 0;
        }

        #ppasEntriesModal .table td,
        #ppasEntriesModal .table th {
            padding: 16px 20px;
            /* Increase padding from default */
            vertical-align: middle;
            /* Center content vertically */
        }

        #ppasEntriesModal .table tbody tr {
            height: 40px;
            cursor: pointer;
            transition: background-color 0.15s;
        }

        .modal-xl {
            max-width: 1000px;
        }

        .modal-title {
            font-weight: bold;
            text-align: center;
            width: 100%;
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
                    <a class="nav-link dropdown-toggle active" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
            <i class="fas fa-clipboard-list"></i>
            <h2>PPAs Management</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Add PPAs Form</h5>
            </div>
            <div class="card-body">
                <form id="ppasForm">
                    <!-- Section Navigation Header -->
                    <div class="form-nav mb-4">
                        <div class="nav-section-group" id="section-group-1">
                            <div class="nav-items-container">
                                <div class="form-nav-item active" data-section="gender-issue">
                                    <span class="step-number">1</span>
                                    Gender Issue
                                </div>
                                <div class="form-nav-item" data-section="basic-info">
                                    <span class="step-number">2</span>
                                    Basic Info
                                </div>
                                <div class="form-nav-item" data-section="agenda-section">
                                    <span class="step-number">3</span>
                                    Agenda
                                </div>
                                <div class="form-nav-item" data-section="sdgs-section">
                                    <span class="step-number">4</span>
                                    SDGs
                                </div>
                                <div class="form-nav-item" data-section="office-programs">
                                    <span class="step-number">5</span>
                                    Office & Programs
                                </div>
                                <div class="form-nav-item" data-section="project-team">
                                    <span class="step-number">6</span>
                                    Project Team
                                </div>
                            </div>
                            <div class="form-nav-arrow" id="nav-next-group">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                        <div class="nav-section-group" id="section-group-2" style="display: none;">
                            <div class="form-nav-arrow" id="nav-prev-group">
                                <i class="fas fa-chevron-left"></i>
                            </div>
                            <div class="nav-items-container">
                                <div class="form-nav-item" data-section="section-7">
                                    <span class="step-number">7</span>
                                    Agencies & Participants
                                </div>
                                <div class="form-nav-item" data-section="section-8">
                                    <span class="step-number">8</span>
                                    Program Description
                                </div>
                                <div class="form-nav-item" data-section="section-9">
                                    <span class="step-number">9</span>
                                    Work Plan
                                </div>
                                <div class="form-nav-item" data-section="section-10">
                                    <span class="step-number">10</span>
                                    Financial Requirements
                                </div>
                                <div class="form-nav-item" data-section="section-11">
                                    <span class="step-number">11</span>
                                    Monitoring
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Sections -->
                    <div class="form-sections-container">
                        <!-- Section 1: Gender Issue Section -->
                        <div class="form-section active" id="gender-issue">
                            <h6 class="section-title"><i class="fas fa-venus-mars me-2"></i> Gender Issue Section</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="campus" class="form-label">Campus</label>
                                        <input type="text" class="form-control" id="campus" name="campus" value="<?php echo htmlspecialchars($userCampus); ?>" readonly>
                                        <small class="info-text">Your campus is automatically populated</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="year" class="form-label">Year</label>
                                        <select class="form-select" id="year" name="year" required>
                                            <option value="" selected disabled>Select Year</option>
                                            <!-- Years will be populated from gpb_entries table -->
                                        </select>
                                        <small class="info-text">Fetched from gpb entries</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="quarter" class="form-label">Quarter</label>
                                        <select class="form-select" id="quarter" name="quarter" required disabled>
                                            <option value="" selected disabled>Select Quarter</option>
                                            <option value="Q1">Q1</option>
                                            <option value="Q2">Q2</option>
                                            <option value="Q3">Q3</option>
                                            <option value="Q4">Q4</option>
                                        </select>
                                        <small class="info-text">Select a year first</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="genderIssue" class="form-label">Gender Issue</label>
                                        <select class="form-select" id="genderIssue" name="genderIssue" required disabled>
                                            <option value="" selected disabled>Select Gender Issue</option>
                                            <!-- Gender issues will be populated based on year and campus -->
                                        </select>
                                        <small class="info-text">Select year and quarter first</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="program" class="form-label">Program</label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control autocomplete-input" id="program" name="program" data-field="program" required>
                                            <div class="autocomplete-dropdown" id="program-suggestions"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="project" class="form-label">Project</label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control autocomplete-input" id="project" name="project" data-field="project" required>
                                            <div class="autocomplete-dropdown" id="project-suggestions"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="activity" class="form-label">Activity</label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control autocomplete-input" id="activity" name="activity" data-field="activity" required>
                                            <div class="autocomplete-dropdown" id="activity-suggestions"></div>
                                            <div class="invalid-feedback" id="activity-error"></div>
                                        </div>
                                        <small class="text-muted">Activities must be unique. Duplicates are not allowed.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="action-buttons-container">
                                <div></div> <!-- Empty div for spacing -->
                                <button type="button" class="btn-form-nav" data-navigate-to="basic-info" id="gender-issue-next-btn">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Section 2: Basic Info Section -->
                        <div class="form-section" id="basic-info">
                            <h6 class="section-title"><i class="fas fa-info-circle me-2"></i> Basic Info Section</h6>

                            <div class="form-group mb-3">
                                <label for="locationVenue" class="form-label">Location/Venue</label>
                                <input type="text" class="form-control" id="locationVenue" name="locationVenue" required>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Start Date</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <select class="form-select" id="startMonth" name="startMonth" required>
                                                <option value="" selected disabled>Month</option>
                                                <option value="1">January</option>
                                                <option value="2">February</option>
                                                <option value="3">March</option>
                                                <option value="4">April</option>
                                                <option value="5">May</option>
                                                <option value="6">June</option>
                                                <option value="7">July</option>
                                                <option value="8">August</option>
                                                <option value="9">September</option>
                                                <option value="10">October</option>
                                                <option value="11">November</option>
                                                <option value="12">December</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-select" id="startDay" name="startDay" required>
                                                <option value="" selected disabled>Day</option>
                                                <?php for ($i = 1; $i <= 31; $i++): ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-select" id="startYear" name="startYear" required>
                                                <option value="" selected disabled>Year</option>
                                                <?php
                                                $currentYear = date('Y');
                                                for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++):
                                                ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">End Date</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <select class="form-select" id="endMonth" name="endMonth" required>
                                                <option value="" selected disabled>Month</option>
                                                <option value="1">January</option>
                                                <option value="2">February</option>
                                                <option value="3">March</option>
                                                <option value="4">April</option>
                                                <option value="5">May</option>
                                                <option value="6">June</option>
                                                <option value="7">July</option>
                                                <option value="8">August</option>
                                                <option value="9">September</option>
                                                <option value="10">October</option>
                                                <option value="11">November</option>
                                                <option value="12">December</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-select" id="endDay" name="endDay" required>
                                                <option value="" selected disabled>Day</option>
                                                <?php for ($i = 1; $i <= 31; $i++): ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-select" id="endYear" name="endYear" required>
                                                <option value="" selected disabled>Year</option>
                                                <?php
                                                $currentYear = date('Y');
                                                for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++):
                                                ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="startTime" class="form-label">Start Time</label>
                                        <input type="time" class="form-control" id="startTime" name="startTime" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="endTime" class="form-label">End Time</label>
                                        <input type="time" class="form-control" id="endTime" name="endTime" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="lunchBreak" class="form-label">Lunch Break</label>
                                        <div class="form-check" style="padding-top: 0.5rem;">
                                            <input class="form-check-input" type="checkbox" id="lunchBreak" name="lunchBreak" style="width: 1.2em; height: 1.2em;">
                                            <label class="form-check-label" for="lunchBreak">
                                                Include 1 hour break
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="totalDuration" class="form-label">Total Duration (hours)</label>
                                        <input type="text" class="form-control" id="totalDuration" name="totalDuration" readonly>
                                        <small class="info-text">Automatically calculated</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="modeOfDelivery" class="form-label">Mode of Delivery</label>
                                <select class="form-select" id="modeOfDelivery" name="modeOfDelivery" required>
                                    <option value="" selected disabled>Select Mode of Delivery</option>
                                    <option value="Face-to-Face">Face-to-Face</option>
                                    <option value="Online">Online</option>
                                    <option value="Hybrid">Hybrid</option>
                                </select>
                            </div>

                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="gender-issue">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <button type="button" class="btn-form-nav" data-navigate-to="agenda-section">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Section 3: Agenda Section -->
                        <div class="form-section" id="agenda-section">
                            <h6 class="section-title"><i class="fas fa-list-check me-2"></i> Agenda Section</h6>

                            <div class="form-group mb-4">
                                <label class="form-label mb-3">Type of Extension Service Agenda</label>
                                <small class="info-text d-block mb-3">Please select only one agenda type</small>
                                <div class="modern-options-container">
                                    <div class="modern-option">
                                        <input type="radio" id="agenda-bisig" name="agenda_type" class="modern-radio" value="BatStateU Inclusive Social Innovation for Regional Growth (BISIG) Program" required>
                                        <label for="agenda-bisig" class="modern-option-label">
                                            <span class="custom-radio"></span>
                                            BatStateU Inclusive Social Innovation for Regional Growth (BISIG) Program
                                        </label>
                                    </div>

                                    <div class="modern-option">
                                        <input type="radio" id="agenda-leaf" name="agenda_type" class="modern-radio" value="Livelihood and other Entrepreneurship related on Agri-Fisheries (LEAF)">
                                        <label for="agenda-leaf" class="modern-option-label">
                                            <span class="custom-radio"></span>
                                            Livelihood and other Entrepreneurship related on Agri-Fisheries (LEAF)
                                        </label>
                                    </div>

                                    <div class="modern-option">
                                        <input type="radio" id="agenda-environment" name="agenda_type" class="modern-radio" value="Environment and Natural Resources Conservation, Protection and Rehabilitation Program">
                                        <label for="agenda-environment" class="modern-option-label">
                                            <span class="custom-radio"></span>
                                            Environment and Natural Resources Conservation, Protection and Rehabilitation Program
                                        </label>
                                    </div>

                                    <div class="modern-option">
                                        <input type="radio" id="agenda-smart" name="agenda_type" class="modern-radio" value="Smart Analytics and Engineering Innovation">
                                        <label for="agenda-smart" class="modern-option-label">
                                            <span class="custom-radio"></span>
                                            Smart Analytics and Engineering Innovation
                                        </label>
                                    </div>

                                    <div class="modern-option">
                                        <input type="radio" id="agenda-adopt" name="agenda_type" class="modern-radio" value="Adopt-a Municipality/Barangay/School/Social Development Thru BIDANI Implementation">
                                        <label for="agenda-adopt" class="modern-option-label">
                                            <span class="custom-radio"></span>
                                            Adopt-a Municipality/Barangay/School/Social Development Thru BIDANI Implementation
                                        </label>
                                    </div>

                                    <div class="modern-option">
                                        <input type="radio" id="agenda-community" name="agenda_type" class="modern-radio" value="Community Outreach">
                                        <label for="agenda-community" class="modern-option-label">
                                            <span class="custom-radio"></span>
                                            Community Outreach
                                        </label>
                                    </div>

                                    <div class="modern-option">
                                        <input type="radio" id="agenda-tvet" name="agenda_type" class="modern-radio" value="Technical - Vocational Education and Training (TVET) Program">
                                        <label for="agenda-tvet" class="modern-option-label">
                                            <span class="custom-radio"></span>
                                            Technical - Vocational Education and Training (TVET) Program
                                        </label>
                                    </div>

                                    <div class="modern-option">
                                        <input type="radio" id="agenda-technology" name="agenda_type" class="modern-radio" value="Technology Transfer and Adoption/Utilization Program">
                                        <label for="agenda-technology" class="modern-option-label">
                                            <span class="custom-radio"></span>
                                            Technology Transfer and Adoption/Utilization Program
                                        </label>
                                    </div>

                                    <div class="modern-option">
                                        <input type="radio" id="agenda-technical" name="agenda_type" class="modern-radio" value="Technical Assistance and Advisory Services Program">
                                        <label for="agenda-technical" class="modern-option-label">
                                            <span class="custom-radio"></span>
                                            Technical Assistance and Advisory Services Program
                                        </label>
                                    </div>

                                    <div class="modern-option">
                                        <input type="radio" id="agenda-pesodev" name="agenda_type" class="modern-radio" value="Parents' Empowerment through Social Development (PESODEV)">
                                        <label for="agenda-pesodev" class="modern-option-label">
                                            <span class="custom-radio"></span>
                                            Parents' Empowerment through Social Development (PESODEV)
                                        </label>
                                    </div>

                                    <div class="modern-option">
                                        <input type="radio" id="agenda-gad" name="agenda_type" class="modern-radio" value="Gender and Development">
                                        <label for="agenda-gad" class="modern-option-label">
                                            <span class="custom-radio"></span>
                                            Gender and Development
                                        </label>
                                    </div>

                                    <div class="modern-option">
                                        <input type="radio" id="agenda-drrm" name="agenda_type" class="modern-radio" value="Disaster Risk Reduction and Management and Disaster Preparedness and Response/Climate Change Adaptation (DRRM and DPR/CCA)">
                                        <label for="agenda-drrm" class="modern-option-label">
                                            <span class="custom-radio"></span>
                                            Disaster Risk Reduction and Management and Disaster Preparedness and Response/Climate Change Adaptation (DRRM and DPR/CCA)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="basic-info">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <button type="button" class="btn-form-nav" data-navigate-to="sdgs-section">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Section 4: SDGs Section -->
                        <div class="form-section" id="sdgs-section">
                            <h6 class="section-title"><i class="fas fa-globe me-2"></i> SDGs Section</h6>

                            <div class="form-group mb-4">
                                <label class="form-label mb-3">Sustainable Development Goals (SDGs)</label>
                                <small class="info-text d-block mb-3">Please select at least one applicable SDG</small>
                                <div class="modern-options-container">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-1" name="sdgs[]" class="modern-radio" value="SDG 1">
                                                <label for="sdg-1" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 1: No Poverty
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-2" name="sdgs[]" class="modern-radio" value="SDG 2">
                                                <label for="sdg-2" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 2: Zero Hunger
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-3" name="sdgs[]" class="modern-radio" value="SDG 3">
                                                <label for="sdg-3" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 3: Good Health and Well-being
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-4" name="sdgs[]" class="modern-radio" value="SDG 4">
                                                <label for="sdg-4" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 4: Quality Education
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-5" name="sdgs[]" class="modern-radio" value="SDG 5">
                                                <label for="sdg-5" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 5: Gender Equality
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-6" name="sdgs[]" class="modern-radio" value="SDG 6">
                                                <label for="sdg-6" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 6: Clean Water and Sanitation
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-7" name="sdgs[]" class="modern-radio" value="SDG 7">
                                                <label for="sdg-7" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 7: Affordable and Clean Energy
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-8" name="sdgs[]" class="modern-radio" value="SDG 8">
                                                <label for="sdg-8" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 8: Decent Work and Economic Growth
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-9" name="sdgs[]" class="modern-radio" value="SDG 9">
                                                <label for="sdg-9" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 9: Industry, Innovation and Infrastructure
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-10" name="sdgs[]" class="modern-radio" value="SDG 10">
                                                <label for="sdg-10" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 10: Reduced Inequalities
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-11" name="sdgs[]" class="modern-radio" value="SDG 11">
                                                <label for="sdg-11" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 11: Sustainable Cities and Communities
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-12" name="sdgs[]" class="modern-radio" value="SDG 12">
                                                <label for="sdg-12" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 12: Responsible Consumption and Production
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-13" name="sdgs[]" class="modern-radio" value="SDG 13">
                                                <label for="sdg-13" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 13: Climate Action
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-14" name="sdgs[]" class="modern-radio" value="SDG 14">
                                                <label for="sdg-14" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 14: Life Below Water
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-15" name="sdgs[]" class="modern-radio" value="SDG 15">
                                                <label for="sdg-15" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 15: Life on Land
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-16" name="sdgs[]" class="modern-radio" value="SDG 16">
                                                <label for="sdg-16" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 16: Peace, Justice and Strong Institutions
                                                </label>
                                            </div>

                                            <div class="modern-option mb-3">
                                                <input type="checkbox" id="sdg-17" name="sdgs[]" class="modern-radio" value="SDG 17">
                                                <label for="sdg-17" class="modern-option-label">
                                                    <span class="custom-radio"></span>
                                                    SDG 17: Partnerships for the Goals
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="agenda-section">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <button type="button" class="btn-form-nav" data-navigate-to="office-programs">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Section 5: Office and Programs Section (replacing Budget Details) -->
                        <div class="form-section" id="office-programs">
                            <h6 class="section-title"><i class="fas fa-building me-2"></i> Office and Programs Section</h6>

                            <div class="mb-4">
                                <label class="form-label">Office/College/Organization involved</label>
                                <div id="officeInputsContainer">
                                    <div class="input-group mb-2">
                                        <div class="numbered-input-container flex-grow-1">
                                            <div class="input-number-indicator">#1</div>
                                            <input type="text" class="form-control numbered-input" name="offices[]" placeholder="Enter office/college/organization" required>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger remove-input" style="display: none;">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn add-field-btn mt-1" id="addOfficeBtn">
                                    <i class="fas fa-plus me-1"></i> Add Another Office/College/Organization
                                </button>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Program involved</label>
                                <div id="programInputsContainer">
                                    <div class="input-group mb-2">
                                        <div class="numbered-input-container flex-grow-1">
                                            <div class="input-number-indicator">#1</div>
                                            <input type="text" class="form-control numbered-input" name="programs[]" placeholder="Enter program" required>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger remove-input" style="display: none;">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn add-field-btn mt-1" id="addProgramBtn">
                                    <i class="fas fa-plus me-1"></i> Add Another Program
                                </button>
                            </div>

                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="sdgs-section">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <button type="button" class="btn-form-nav" data-navigate-to="project-team">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Section 6: Project Team Section -->
                        <div class="form-section" id="project-team">
                            <h6 class="section-title"><i class="fas fa-users me-2"></i> Project Team Section</h6>

                            <!-- Project Leaders Container -->
                            <div class="mb-4">
                                <h6 class="mb-3">Project Leaders</h6>
                                <div id="projectLeadersContainer">
                                    <!-- Project Leader -->
                                    <div class="team-member-card mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Project Leader #1</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <label for="leader_name_1" class="form-label">Name</label>
                                                    <input type="text" class="form-control personnel-autocomplete" id="leader_name_1" name="leader_name[]" placeholder="Start typing to search personnel..." required>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="leader_gender_1" class="form-label">Gender</label>
                                                    <input type="text" class="form-control" id="leader_gender_1" name="leader_gender[]" readonly>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="leader_rank_1" class="form-label">Academic Rank</label>
                                                    <input type="text" class="form-control" id="leader_rank_1" name="leader_rank[]" readonly>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="leader_salary_1" class="form-label">Monthly Salary</label>
                                                    <div class="input-with-currency">
                                                        <input type="text" class="form-control" id="leader_salary_1" name="leader_salary[]" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="leader_rate_1" class="form-label">Rate per hour</label>
                                                    <div class="input-with-currency">
                                                        <input type="text" class="form-control" id="leader_rate_1" name="leader_rate[]" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Responsibilities/Assigned Tasks</label>
                                                <div class="tasks-container" id="leaderTasksContainer_1">
                                                    <div class="input-group mb-2">
                                                        <div class="numbered-input-container flex-grow-1">
                                                            <div class="input-number-indicator">#1</div>
                                                            <input type="text" class="form-control numbered-input" name="leader_tasks_1[]" placeholder="Enter task or responsibility" required>
                                                        </div>
                                                        <button type="button" class="btn btn-outline-danger remove-input" style="display: none;">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn add-field-btn mt-1 add-task-btn" data-role="leader" data-index="1">
                                                    <i class="fas fa-plus me-1"></i> Add Task/Responsibility
                                                </button>
                                            </div>
                                            <button type="button" class="btn btn-outline-danger remove-team-member" style="display: none;">
                                                <i class="fas fa-trash-alt me-1"></i> Remove Project Leader
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn add-field-btn mt-1" id="addProjectLeaderBtn">
                                    <i class="fas fa-plus me-1"></i> Add Another Project Leader
                                </button>
                            </div>

                            <!-- Assistant Project Leaders Container -->
                            <div class="mb-4">
                                <h6 class="mb-3">Assistant Project Leaders</h6>
                                <div id="assistantLeadersContainer">
                                    <!-- Assistant Project Leader -->
                                    <div class="team-member-card mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Assistant Project Leader #1</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <label for="asst_leader_name_1" class="form-label">Name</label>
                                                    <input type="text" class="form-control personnel-autocomplete" id="asst_leader_name_1" name="asst_leader_name[]" placeholder="Start typing to search personnel..." required>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="asst_leader_gender_1" class="form-label">Gender</label>
                                                    <input type="text" class="form-control" id="asst_leader_gender_1" name="asst_leader_gender[]" readonly>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="asst_leader_rank_1" class="form-label">Academic Rank</label>
                                                    <input type="text" class="form-control" id="asst_leader_rank_1" name="asst_leader_rank[]" readonly>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="asst_leader_salary_1" class="form-label">Monthly Salary</label>
                                                    <div class="input-with-currency">
                                                        <input type="text" class="form-control" id="asst_leader_salary_1" name="asst_leader_salary[]" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="asst_leader_rate_1" class="form-label">Rate per hour</label>
                                                    <div class="input-with-currency">
                                                        <input type="text" class="form-control" id="asst_leader_rate_1" name="asst_leader_rate[]" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Responsibilities/Assigned Tasks</label>
                                                <div class="tasks-container" id="asstLeaderTasksContainer_1">
                                                    <div class="input-group mb-2">
                                                        <div class="numbered-input-container flex-grow-1">
                                                            <div class="input-number-indicator">#1</div>
                                                            <input type="text" class="form-control numbered-input" name="asst_leader_tasks_1[]" placeholder="Enter task or responsibility" required>
                                                        </div>
                                                        <button type="button" class="btn btn-outline-danger remove-input" style="display: none;">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn add-field-btn mt-1 add-task-btn" data-role="asst_leader" data-index="1">
                                                    <i class="fas fa-plus me-1"></i> Add Task/Responsibility
                                                </button>
                                            </div>
                                            <button type="button" class="btn btn-outline-danger remove-team-member" style="display: none;">
                                                <i class="fas fa-trash-alt me-1"></i> Remove Assistant Project Leader
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn add-field-btn mt-1" id="addAssistantLeaderBtn">
                                    <i class="fas fa-plus me-1"></i> Add Another Assistant Project Leader
                                </button>
                            </div>

                            <!-- Project Staff Container -->
                            <div class="mb-4">
                                <h6 class="mb-3">Project Staff/Coordinators</h6>
                                <div id="projectStaffContainer">
                                    <!-- Project Staff/Coordinator -->
                                    <div class="team-member-card mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Project Staff/Coordinator #1</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <label for="staff_name_1" class="form-label">Name</label>
                                                    <input type="text" class="form-control personnel-autocomplete" id="staff_name_1" name="staff_name[]" placeholder="Start typing to search personnel..." required>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="staff_gender_1" class="form-label">Gender</label>
                                                    <input type="text" class="form-control" id="staff_gender_1" name="staff_gender[]" readonly>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="staff_rank_1" class="form-label">Academic Rank</label>
                                                    <input type="text" class="form-control" id="staff_rank_1" name="staff_rank[]" readonly>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="staff_salary_1" class="form-label">Monthly Salary</label>
                                                    <div class="input-with-currency">
                                                        <input type="text" class="form-control" id="staff_salary_1" name="staff_salary[]" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="staff_rate_1" class="form-label">Rate per hour</label>
                                                    <div class="input-with-currency">
                                                        <input type="text" class="form-control" id="staff_rate_1" name="staff_rate[]" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Responsibilities/Assigned Tasks</label>
                                                <div class="tasks-container" id="staffTasksContainer_1">
                                                    <div class="input-group mb-2">
                                                        <div class="numbered-input-container flex-grow-1">
                                                            <div class="input-number-indicator">#1</div>
                                                            <input type="text" class="form-control numbered-input" name="staff_tasks_1[]" placeholder="Enter task or responsibility" required>
                                                        </div>
                                                        <button type="button" class="btn btn-outline-danger remove-input" style="display: none;">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn add-field-btn mt-1 add-task-btn" data-role="staff" data-index="1">
                                                    <i class="fas fa-plus me-1"></i> Add Task/Responsibility
                                                </button>
                                            </div>
                                            <button type="button" class="btn btn-outline-danger remove-team-member" style="display: none;">
                                                <i class="fas fa-trash-alt me-1"></i> Remove Project Staff
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn add-field-btn mt-1" id="addProjectStaffBtn">
                                    <i class="fas fa-plus me-1"></i> Add Another Project Staff/Coordinator
                                </button>
                            </div>

                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="office-programs">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <button type="button" class="btn-form-nav" data-navigate-to="section-7">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Section 7 -->
                        <div class="form-section" id="section-7">
                            <h6 class="section-title"><i class="fas fa-users-rectangle me-2"></i> Agencies & Participants Section</h6>

                            <!-- Participants/Beneficiaries -->
                            <div class="mb-4">

                                <!-- Internal Participants -->
                                <div class="card mb-4" style="min-height: 300px;">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Internal Participants</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label for="internal_type" class="form-label">Type</label>
                                                <input type="text" class="form-control" id="internal_type" name="internal_type" placeholder="e.g. Students, Faculty, Staff" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="internal_male" class="form-label">Male</label>
                                                <input type="number" min="0" class="form-control participant-count" id="internal_male" name="internal_male" readonly tabindex="-1" style="background-color: var(--readonly-bg); pointer-events: none;">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="internal_female" class="form-label">Female</label>
                                                <input type="number" min="0" class="form-control participant-count" id="internal_female" name="internal_female" readonly tabindex="-1" style="background-color: var(--readonly-bg); pointer-events: none;">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="internal_total" class="form-label">Total</label>
                                                <input type="text" class="form-control" id="internal_total" name="internal_total" readonly>
                                                <small class="info-text">Automatically calculated</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- External Participants -->
                                <div class="card mb-4" style="min-height: 300px;">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">External Participants</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label for="external_type" class="form-label">Type</label>
                                                <input type="text" class="form-control" id="external_type" name="external_type" placeholder="e.g. Community members, LGU" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="external_male" class="form-label">Male</label>
                                                <input type="number" min="0" class="form-control participant-count" id="external_male" name="external_male" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="external_female" class="form-label">Female</label>
                                                <input type="number" min="0" class="form-control participant-count" id="external_female" name="external_female" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="external_total" class="form-label">Total</label>
                                                <input type="text" class="form-control" id="external_total" name="external_total" readonly>
                                                <small class="info-text">Automatically calculated</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Grand Totals -->
                                <div class="card" style="min-height: 200px;">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Grand Totals</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="total_male" class="form-label">Total Male</label>
                                                <input type="text" class="form-control" id="total_male" name="total_male" readonly>
                                                <small class="info-text">Automatically calculated</small>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="total_female" class="form-label">Total Female</label>
                                                <input type="text" class="form-control" id="total_female" name="total_female" readonly>
                                                <small class="info-text">Automatically calculated</small>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="grand_total" class="form-label">Total</label>
                                                <input type="text" class="form-control" id="grand_total" name="grand_total" readonly>
                                                <small class="info-text">Automatically calculated</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="project-team">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <button type="button" class="btn-form-nav" data-navigate-to="section-8">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Section 8 -->
                        <div class="form-section" id="section-8">
                            <h6 class="section-title"><i class="fas fa-file-alt me-2"></i> Program Description Section</h6>

                            <!-- Rationale Card -->
                            <div class="card mb-4" style="min-height: 300px;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Rationale</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <textarea class="form-control" id="rationale" name="rationale" rows="6" placeholder="Enter program rationale" required></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Objectives Card -->
                            <div class="card mb-4" style="min-height: 200px;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Objectives</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-4">
                                        <label for="general_objectives" class="form-label">General Objectives</label>
                                        <textarea class="form-control" id="general_objectives" name="general_objectives" rows="4" placeholder="Enter general objectives" required></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Specific Objectives</label>
                                        <div id="specific_objectives_container">
                                            <div class="input-group mb-2">
                                                <div class="numbered-input-container flex-grow-1">
                                                    <div class="input-number-indicator">#1</div>
                                                    <input type="text" class="form-control numbered-input" name="specific_objectives[]" placeholder="Enter specific objective" required>
                                                </div>
                                                <button type="button" class="btn btn-outline-danger remove-input" style="display: none;">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn add-field-btn mt-1" id="add_specific_objectives_btn">
                                            <i class="fas fa-plus me-1"></i> Add Another Specific Objective
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Description and Strategies Card -->
                            <div class="card mb-4" style="min-height: 300px;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Description and Strategies</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-4">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Enter program description" required></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Strategies</label>
                                        <div id="strategies_container">
                                            <div class="input-group mb-2">
                                                <div class="numbered-input-container flex-grow-1">
                                                    <div class="input-number-indicator">#1</div>
                                                    <input type="text" class="form-control numbered-input" name="strategies[]" placeholder="Enter strategy" required>
                                                </div>
                                                <button type="button" class="btn btn-outline-danger remove-input" style="display: none;">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn add-field-btn mt-1" id="add_strategies_btn">
                                            <i class="fas fa-plus me-1"></i> Add Another Strategy
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Expected Output Card -->
                            <div class="card mb-4" style="min-height: 300px;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Expected Output</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <div id="expected_output_container">
                                            <div class="input-group mb-2">
                                                <div class="numbered-input-container flex-grow-1">
                                                    <div class="input-number-indicator">#1</div>
                                                    <input type="text" class="form-control numbered-input" name="expected_output[]" placeholder="Enter expected output" required>
                                                </div>
                                                <button type="button" class="btn btn-outline-danger remove-input" style="display: none;">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn add-field-btn mt-1" id="add_expected_output_btn">
                                            <i class="fas fa-plus me-1"></i> Add Another Expected Output
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Functional Requirements Card -->
                            <div class="card mb-4" style="min-height: 300px;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Functional Requirements</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <textarea class="form-control" id="functional_requirements" name="functional_requirements" rows="6" placeholder="Enter functional requirements" required></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Sustainability Card -->
                            <div class="card mb-4" style="min-height: 300px;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Sustainability</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-4">
                                        <textarea class="form-control" id="sustainability_plan" name="sustainability_plan" rows="4" placeholder="Enter sustainability plan" required></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Specific Plans</label>
                                        <div id="specific_plans_container">
                                            <div class="input-group mb-2">
                                                <div class="numbered-input-container flex-grow-1">
                                                    <div class="input-number-indicator">#1</div>
                                                    <input type="text" class="form-control numbered-input" name="specific_plans[]" placeholder="Enter specific plan" required>
                                                </div>
                                                <button type="button" class="btn btn-outline-danger remove-input" style="display: none;">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn add-field-btn mt-1" id="add_specific_plans_btn">
                                            <i class="fas fa-plus me-1"></i> Add Another Specific Plan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="section-7">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <button type="button" class="btn-form-nav" data-navigate-to="section-9">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Section 9 -->
                        <div class="form-section" id="section-9">
                            <h6 class="section-title"><i class="fas fa-calendar-check me-2"></i> Work Plan Section</h6>

                            <!-- Timeline Card -->
                            <div class="card mb-4" style="min-height: 300px;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Timeline of Activities</h6>
                                </div>
                                <div class="card-body">
                                    <div id="timeline-container">
                                        <!-- Timeline will be generated here based on date range from Basic Info -->
                                        <div id="timeline-message" class="alert alert-info mb-3">
                                            Please select Start Date and End Date in the Basic Info section first to generate the timeline.
                                        </div>

                                        <div id="timeline-table-container" style="display: none;">
                                            <div class="table-responsive">
                                                <table class="table table-bordered" id="timeline-table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 300px;">Activity</th>
                                                            <!-- Date columns will be added here -->
                                                        </tr>
                                                    </thead>
                                                    <tbody id="timeline-activities">
                                                        <!-- Activity rows will be added here -->
                                                        <tr class="activity-row">
                                                            <td>
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control activity-name" name="activity_name[]" placeholder="Enter activity name" required>
                                                                    <button type="button" class="btn btn-outline-danger remove-activity" style="display: none;">
                                                                        <i class="fas fa-minus"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                            <!-- Checkbox cells will be added here -->
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <button type="button" class="btn add-field-btn mt-3" id="add-activity-btn">
                                                <i class="fas fa-plus me-1"></i> Add Another Activity
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="section-8">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <button type="button" class="btn-form-nav" data-navigate-to="section-10">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Section 10 -->
                        <div class="form-section" id="section-10">
                            <h6 class="section-title"><i class="fas fa-money-bill-wave me-2"></i> Financial Requirements Section</h6>

                            <!-- Financial Plan Selection -->
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">Financial Plan</label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="hasFinancialPlan" id="withFinancialPlan" value="1">
                                        <label class="form-check-label" for="withFinancialPlan">
                                            With Financial Plan
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="hasFinancialPlan" id="withoutFinancialPlan" value="0">
                                        <label class="form-check-label" for="withoutFinancialPlan">
                                            Without Financial Plan
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Plan Table (shown only when "With Financial Plan" is selected) -->
                            <div id="financialPlanSection" class="mb-4" style="display:none;">
                                <div class="mb-3">
                                    <h6 class="mb-3">Financial Plan Items</h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover shadow-sm rounded overflow-hidden" id="financialPlanTable">
                                            <thead class="bg-accent text-white">
                                                <tr>
                                                    <th>Item Description</th>
                                                    <th>Quantity</th>
                                                    <th>Unit</th>
                                                    <th>Unit Cost (₱)</th>
                                                    <th>Total Cost (₱)</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="financialPlanTableBody" class="border-top-0">
                                                <!-- Table rows will be added dynamically -->
                                            </tbody>
                                            <tfoot class="fw-bold">
                                                <tr>
                                                    <td colspan="4" class="text-end">Grand Total:</td>
                                                    <td class="fw-bold" id="grandTotalCost">₱0.00</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <button type="button" class="btn add-field-btn mt-3" id="addFinancialItem">
                                        <i class="fas fa-plus me-1"></i> Add Financial Item
                                    </button>

                                    <!-- Empty state message -->
                                    <div id="emptyFinancialPlanMessage" class="alert alert-info d-flex align-items-center mt-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <span>No financial items added yet. Click "Add Financial Item" to begin.</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Cost -->
                            <div class="form-group mb-4">
                                <label for="totalCost" class="form-label fw-bold">Total Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" class="form-control" id="totalCost" name="totalCost" value="0.00" readonly>
                                </div>
                                <small class="text-muted">Automatically calculated from financial plan</small>
                            </div>

                            <!-- Source of Fund -->
                            <div class="form-group mb-4">
                                <label for="sourceOfFund" class="form-label fw-bold">Source of Fund</label>
                                <div class="source-fund-container" id="sourceFundContainer">
                                    <div class="source-fund-option" data-value="GAA">GAA</div>
                                    <div class="source-fund-option" data-value="MDS">MDS</div>
                                    <div class="source-fund-option" data-value="STF">STF</div>
                                </div>
                                <input type="hidden" id="sourceOfFund" name="sourceOfFund[]" value="">
                                <small class="text-muted">Click on options to select/deselect</small>
                            </div>

                            <!-- Financial Note -->
                            <div class="form-group mb-4">
                                <label for="financialNote" class="form-label fw-bold">Financial Note</label>
                                <textarea class="form-control" id="financialNote" name="financialNote" rows="3"></textarea>
                            </div>

                            <!-- Approved Budget -->
                            <div class="form-group mb-4">
                                <label for="approvedBudget" class="form-label fw-bold">Approved Budget</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" class="form-control" id="approvedBudget" name="approvedBudget" placeholder="0.00">
                                </div>
                            </div>

                            <!-- PS Attribution -->
                            <div class="form-group mb-4">
                                <label for="psAttribution" class="form-label fw-bold">PS Attribution</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" class="form-control" id="psAttribution" name="psAttribution" value="0.00" readonly>
                                </div>
                                <small class="text-muted">Automatically calculated from Project Team section (duration × rate per hour)</small>
                                <div id="psAttributionMessage" class="alert alert-warning mt-2 d-none">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Please complete the Project Team section to calculate PS Attribution.
                                </div>
                            </div>

                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="section-9">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                                <button type="button" class="btn-form-nav" data-navigate-to="section-11">
                                    Next <i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Section 11 -->
                        <div class="form-section" id="section-11">
                            <h6 class="section-title"><i class="fas fa-chart-line me-2"></i> 11. Monitoring Section</h6>

                            <div id="monitoring-items-container">
                                <div class="monitoring-item mb-4">
                                    <div class="card">
                                        <div class="card-header bg-light d-flex align-items-center">
                                            <h6 class="mb-0">
                                                Monitoring Item <span class="monitoring-number-indicator">#1</span>
                                            </h6>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-auto remove-monitoring" style="display: none;">
                                                <i class="fas fa-trash-alt"></i> Remove
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="objectives1" class="form-label">Objectives</label>
                                                        <textarea class="form-control rich-text" id="objectives1" name="objectives[]" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="performance_indicators1" class="form-label">Performance Indicators</label>
                                                        <textarea class="form-control rich-text" id="performance_indicators1" name="performance_indicators[]" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="baseline_data1" class="form-label">Baseline Data</label>
                                                        <textarea class="form-control rich-text" id="baseline_data1" name="baseline_data[]" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="performance_target1" class="form-label">Performance Target</label>
                                                        <textarea class="form-control rich-text" id="performance_target1" name="performance_target[]" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="data_source1" class="form-label">Data Source</label>
                                                        <textarea class="form-control rich-text" id="data_source1" name="data_source[]" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="collection_method1" class="form-label">Collection Method</label>
                                                        <textarea class="form-control rich-text" id="collection_method1" name="collection_method[]" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="frequency1" class="form-label">Frequency of Data Collection</label>
                                                        <textarea class="form-control rich-text" id="frequency1" name="frequency[]" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="persons_involved1" class="form-label">Office/Persons Involved</label>
                                                        <textarea class="form-control rich-text" id="persons_involved1" name="persons_involved[]" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mb-4">
                                <button type="button" class="btn add-field-btn mt-3" id="add-monitoring-btn">
                                    <i class="fas fa-plus me-2"></i> Add Monitoring Item
                                </button>
                            </div>

                            <div class="action-buttons-container">
                                <button type="button" class="btn-form-nav btn-prev" data-navigate-to="section-10">
                                    <i class="fas fa-chevron-left me-2"></i> Previous
                                </button>
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4" style="margin-bottom: -30px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="button" class="btn-icon" id="viewBtn">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="d-inline-flex gap-3">
                                    <button type="button" class="btn-icon" id="addBtn">
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
            function updateInternalParticipantsFromProjectTeam() {
                // Helper to count gender in a container
                function countGender(containerSelector, genderFieldName) {
                    let male = 0,
                        female = 0;
                    document.querySelectorAll(containerSelector + ' .team-member-card').forEach(card => {
                        const genderInput = card.querySelector(`input[name="${genderFieldName}[]"]`);
                        if (genderInput) {
                            const val = genderInput.value.trim().toLowerCase();
                            if (val === 'male') male++;
                            else if (val === 'female') female++;
                        }
                    });
                    return {
                        male,
                        female
                    };
                }
                // Count for each role
                const leaders = countGender('#projectLeadersContainer', 'leader_gender');
                const asstLeaders = countGender('#assistantLeadersContainer', 'asst_leader_gender');
                const staff = countGender('#projectStaffContainer', 'staff_gender');
                // Sum up
                const totalMale = leaders.male + asstLeaders.male + staff.male;
                const totalFemale = leaders.female + asstLeaders.female + staff.female;
                // Set values (default to 0)
                document.getElementById('internal_male').value = totalMale;
                document.getElementById('internal_female').value = totalFemale;
                document.getElementById('internal_total').value = totalMale + totalFemale;
                // Trigger participant totals update
                if (typeof calculateParticipantTotals === 'function') calculateParticipantTotals();
            }
            // Attach to project team changes
            function attachProjectTeamGenderListeners() {
                // Listen for changes in all gender fields in project team
                const observer = new MutationObserver(updateInternalParticipantsFromProjectTeam);
                [
                    '#projectLeadersContainer',
                    '#assistantLeadersContainer',
                    '#projectStaffContainer'
                ].forEach(selector => {
                    const container = document.querySelector(selector);
                    if (container) observer.observe(container, {
                        childList: true,
                        subtree: true
                    });
                });
                // Listen for input changes on gender fields
                document.addEventListener('input', function(e) {
                    if (
                        e.target.matches('input[name="leader_gender[]"]') ||
                        e.target.matches('input[name="asst_leader_gender[]"]') ||
                        e.target.matches('input[name="staff_gender[]"]')
                    ) {
                        updateInternalParticipantsFromProjectTeam();
                    }
                });
                // Listen for personnel autocomplete changes (in case gender is filled by JS)
                document.addEventListener('change', function(e) {
                    if (e.target.classList.contains('personnel-autocomplete')) {
                        updateInternalParticipantsFromProjectTeam();
                    }
                });
            }
            document.addEventListener('DOMContentLoaded', function() {
                attachProjectTeamGenderListeners();
                updateInternalParticipantsFromProjectTeam();
            });

            // Form validation flag
            let validationTriggered = false;

            // Function to determine if a field is required based on its ID or name
            function isRequiredField(fieldId) {
                const requiredFields = [
                    'year', 'quarter', 'campus', 'genderIssue',
                    'program', 'project', 'activity',
                    // Basic Info section fields
                    'locationVenue', 'startDate', 'endDate',
                    'startTime', 'endTime', 'modeOfDelivery',
                    // Agenda section
                    'agenda_type',
                    // SDGs section - at least one must be selected
                    'sdgs[]',
                    // Office and Programs section
                    'offices[]', 'programs[]',
                    // Project Team section
                    'leader_name', 'asst_leader_name', 'staff_name',
                    'leader_tasks[]', 'asst_leader_tasks[]', 'staff_tasks[]',
                    // Budget section
                    'budgetAllocation', 'fundingSource', 'approvedBudget'
                ];
                return requiredFields.includes(fieldId);
            }

            // Function to mark a field as invalid
            function markAsInvalid(input, errorMessage = 'This field is required') {
                input.classList.add('is-invalid');

                // For currency input, mark the wrapper as invalid
                const currencyWrapper = input.closest('.input-with-currency');
                if (currencyWrapper) {
                    currencyWrapper.classList.add('is-invalid');

                    // Add feedback message after the currency wrapper if not already present
                    if (!currencyWrapper.nextElementSibling || !currencyWrapper.nextElementSibling.classList.contains('invalid-feedback')) {
                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = errorMessage;
                        currencyWrapper.parentNode.insertBefore(feedback, currencyWrapper.nextSibling);
                    }
                }
                // Special handling for activity input in workplan
                else if (input.classList.contains('activity-name')) {
                    const inputGroup = input.closest('.input-group');
                    if (inputGroup) {
                        // Position the input group to contain the absolute-positioned error if not already
                        if (getComputedStyle(inputGroup).position !== 'relative') {
                            inputGroup.style.position = 'relative';
                        }

                        // Find or create validation container
                        let validationContainer = inputGroup.querySelector('.validation-message-container');
                        if (!validationContainer) {
                            validationContainer = document.createElement('div');
                            validationContainer.className = 'validation-message-container';
                            validationContainer.style.position = 'absolute';
                            validationContainer.style.bottom = '-20px';
                            validationContainer.style.left = '0';
                            validationContainer.style.width = '100%';
                            validationContainer.style.zIndex = '1';
                            inputGroup.appendChild(validationContainer);
                        }

                        // Add feedback if not present
                        if (!validationContainer.querySelector('.invalid-feedback')) {
                            const feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.style.display = 'block';
                            feedback.textContent = errorMessage;
                            validationContainer.appendChild(feedback);
                        }
                    } else {
                        // Fallback to standard behavior if no input group
                        if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('invalid-feedback')) {
                            const feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.textContent = errorMessage;
                            input.parentNode.insertBefore(feedback, input.nextSibling);
                        }
                    }
                } else {
                    // Normal input field handling
                    // Add feedback message if not already present
                    if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('invalid-feedback')) {
                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = errorMessage;
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
                }
                // Special handling for activity input in workplan
                else if (input.classList.contains('activity-name')) {
                    const inputGroup = input.closest('.input-group');
                    if (inputGroup) {
                        // Find validation container and remove any feedback messages
                        const validationContainer = inputGroup.querySelector('.validation-message-container');
                        if (validationContainer) {
                            const feedbackElements = validationContainer.querySelectorAll('.invalid-feedback');
                            feedbackElements.forEach(el => el.remove());
                        }
                    } else {
                        // Fallback to standard behavior
                        if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) {
                            input.nextElementSibling.remove();
                        }
                    }
                } else {
                    // Remove feedback message if present
                    if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) {
                        input.nextElementSibling.remove();
                    }
                }
            }

            // Function to validate a section
            function validateSection(sectionId) {
                const section = document.getElementById(sectionId);
                let isValid = true;

                // Special handling for section-8 with dynamic input fields
                if (sectionId === 'section-8') {
                    return updateSectionCompletionStatus(sectionId);
                }

                // Special handling for section-9 (Work Plan)
                if (sectionId === 'section-9') {
                    return validateWorkPlanSection();
                }

                // Special handling for section-11 (Monitoring Section)
                if (sectionId === 'section-11') {
                    return updateMonitoringSectionStatus();
                }

                // Special handling for section-10 (Finance Section)
                if (sectionId === 'section-10') {
                    // Fix: Check both by ID and also by name to ensure we find the radio buttons
                    // First try by ID
                    let withFinancialPlanRadio = document.getElementById('withFinancialPlan');
                    let withoutFinancialPlanRadio = document.getElementById('withoutFinancialPlan');

                    // If not found, try by name and value
                    if (!withFinancialPlanRadio || !withoutFinancialPlanRadio) {
                        console.log('Radio buttons not found by ID, trying by name...');
                        const radiosByName = document.querySelectorAll('input[name="hasFinancialPlan"]');
                        console.log('Found radio buttons by name:', radiosByName.length);

                        Array.from(radiosByName).forEach(radio => {
                            console.log(`Radio ${radio.id}, value: ${radio.value}, checked: ${radio.checked}`);
                            if (radio.value === '1') withFinancialPlanRadio = radio;
                            if (radio.value === '0') withoutFinancialPlanRadio = radio;
                        });
                    }

                    // Handle possible null values more gracefully
                    const hasFinancialPlanSelection = (withFinancialPlanRadio && withFinancialPlanRadio.checked) ||
                        (withoutFinancialPlanRadio && withoutFinancialPlanRadio.checked);

                    console.log('Finance validation - withPlan element exists:', !!withFinancialPlanRadio);
                    console.log('withoutPlan element exists:', !!withoutFinancialPlanRadio);
                    console.log('withPlan checked:', withFinancialPlanRadio?.checked);
                    console.log('withoutPlan checked:', withoutFinancialPlanRadio?.checked);
                    console.log('hasFinancialPlanSelection:', hasFinancialPlanSelection);
                    console.log('validationTriggered:', validationTriggered);
                    // Check approved budget
                    // Check approved budget
                    const approvedBudget = document.getElementById('approvedBudget');
                    const hasApprovedBudget = approvedBudget && approvedBudget.value.trim() !== '' && approvedBudget.value.trim() !== '0.00';

                    if (!hasApprovedBudget) {
                        if (validationTriggered) {
                            markAsInvalid(approvedBudget, 'Please enter the approved budget');
                        }
                        isValid = false;
                    }
                    if (!hasFinancialPlanSelection) {
                        isValid = false;

                        if (validationTriggered) {
                            const radioContainer = section.querySelector('.form-group:nth-child(2)');
                            if (radioContainer) {
                                radioContainer.classList.add('is-invalid');

                                // Add feedback message if not already present
                                if (!radioContainer.querySelector('.invalid-feedback')) {
                                    const feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    feedback.textContent = 'Please select a financial plan option';
                                    feedback.style.display = 'block'; // Ensure it's visible
                                    radioContainer.appendChild(feedback);
                                }
                            }

                            // Update section title with error
                            const sectionTitle = document.querySelector('#section-10 .section-title');
                            if (sectionTitle) {
                                sectionTitle.classList.add('has-error');
                            }

                            // Update nav item to show error
                            const navItem = document.querySelector('.form-nav-item[data-section="section-10"]');
                            if (navItem) {
                                navItem.classList.add('has-error');
                                navItem.classList.remove('is-complete');
                            }
                        }
                    }

                    // If "With Financial Plan" is selected, check for financial plan items
                    if (withFinancialPlanRadio && withFinancialPlanRadio.checked) {
                        const financialPlanRows = document.querySelectorAll('#financialPlanTableBody tr');
                        const hasFinancialPlanItems = financialPlanRows.length > 0;

                        if (!hasFinancialPlanItems) {
                            if (validationTriggered) {
                                const financialPlanSection = document.getElementById('financialPlanSection');
                                if (financialPlanSection) {
                                    financialPlanSection.classList.add('is-invalid');

                                    // Add feedback message if not already present
                                    if (!financialPlanSection.querySelector('.invalid-feedback')) {
                                        const feedback = document.createElement('div');
                                        feedback.className = 'invalid-feedback';
                                        feedback.textContent = 'Please add at least one financial plan item';
                                        feedback.style.display = 'block'; // Ensure it's visible
                                        financialPlanSection.appendChild(feedback);
                                    }
                                }
                            }
                            isValid = false;
                        } else {
                            // Check if each row has complete data
                            let allRowsComplete = true;

                            financialPlanRows.forEach(row => {
                                // Check each input in the row
                                const description = row.querySelector('.item-description');
                                const quantity = row.querySelector('.item-quantity');
                                const unit = row.querySelector('.item-unit');
                                const unitCost = row.querySelector('.item-unit-cost');

                                // Check if any field is empty
                                const hasDescription = description && description.value.trim() !== '';
                                const hasQuantity = quantity && quantity.value.trim() !== '';
                                const hasUnit = unit && unit.value.trim() !== '';
                                const hasUnitCost = unitCost && unitCost.value.trim() !== '';

                                // If any required field is empty, mark row as incomplete
                                if (!hasDescription || !hasQuantity || !hasUnit || !hasUnitCost) {
                                    allRowsComplete = false;

                                    if (validationTriggered) {
                                        // Add visual feedback for empty fields
                                        if (!hasDescription && description) {
                                            description.classList.add('is-invalid');
                                        }
                                        if (!hasQuantity && quantity) {
                                            quantity.classList.add('is-invalid');
                                        }
                                        if (!hasUnit && unit) {
                                            unit.classList.add('is-invalid');
                                        }
                                        if (!hasUnitCost && unitCost) {
                                            unitCost.classList.add('is-invalid');
                                        }
                                    }
                                } else {
                                    // Clear invalid state if all fields are filled
                                    if (description) description.classList.remove('is-invalid');
                                    if (quantity) quantity.classList.remove('is-invalid');
                                    if (unit) unit.classList.remove('is-invalid');
                                    if (unitCost) unitCost.classList.remove('is-invalid');
                                }
                            });

                            if (!allRowsComplete) {
                                isValid = false;
                                if (validationTriggered) {
                                    const financialPlanSection = document.getElementById('financialPlanSection');
                                    if (financialPlanSection) {
                                        // Add feedback message if not already present
                                        if (!financialPlanSection.querySelector('.invalid-feedback')) {
                                            const feedback = document.createElement('div');
                                            feedback.className = 'invalid-feedback';
                                            feedback.textContent = 'Please complete all financial plan item fields';
                                            feedback.style.display = 'block'; // Ensure it's visible
                                            financialPlanSection.appendChild(feedback);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Check source of fund
                    const sourceOfFund = document.getElementById('sourceOfFund');
                    const hasSourceFund = sourceOfFund && sourceOfFund.value.trim() !== '';

                    if (!hasSourceFund) {
                        if (validationTriggered) {
                            const sourceFundContainer = document.getElementById('sourceFundContainer');
                            if (sourceFundContainer) {
                                sourceFundContainer.classList.add('is-invalid');

                                // Add feedback message if not already present
                                if (!sourceFundContainer.nextElementSibling || !sourceFundContainer.nextElementSibling.classList.contains('invalid-feedback')) {
                                    const feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    feedback.textContent = 'Please select at least one source of fund';
                                    feedback.style.display = 'block'; // Ensure it's visible
                                    sourceFundContainer.parentNode.insertBefore(feedback, sourceFundContainer.nextSibling);
                                }
                            }
                        }
                        isValid = false;
                    }

                    // Check financial note
                    const financialNote = document.getElementById('financialNote');
                    const hasFinancialNote = financialNote && financialNote.value.trim() !== '';

                    if (!hasFinancialNote) {
                        if (validationTriggered) {
                            financialNote.classList.add('is-invalid');

                            // Add feedback message if not already present
                            if (!financialNote.nextElementSibling || !financialNote.nextElementSibling.classList.contains('invalid-feedback')) {
                                const feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                feedback.textContent = 'Please provide financial notes';
                                financialNote.parentNode.insertBefore(feedback, financialNote.nextSibling);
                            }
                        }
                        isValid = false;
                    }

                    // Summarize all validity checks
                    console.log('Financial section validation results:');
                    console.log('- Has radio selected:', hasFinancialPlanSelection);
                    console.log('- Has approved budget:', hasApprovedBudget);
                    console.log('- Has source fund:', hasSourceFund);
                    console.log('- Has financial note:', hasFinancialNote);
                    console.log('- Final validation result:', isValid ? '✅ VALID' : '🔴 INVALID');

                    // Update section status
                    if (validationTriggered) {
                        updateSectionStatus(sectionId, isValid);
                    }

                    return isValid;
                }

                // Special handling for office-programs section with dynamic inputs
                if (sectionId === 'office-programs') {
                    // Check all office inputs
                    const officeInputs = section.querySelectorAll('#officeInputsContainer input[name="offices[]"]');
                    officeInputs.forEach(input => {
                        if (!input.value.trim()) {
                            if (validationTriggered) {
                                input.classList.add('is-invalid');

                                // Add feedback message if not already present
                                const inputGroup = input.closest('.input-group');
                                if (inputGroup && (!inputGroup.nextElementSibling || !inputGroup.nextElementSibling.classList.contains('invalid-feedback'))) {
                                    const feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    feedback.textContent = 'This field is required';
                                    feedback.style.display = 'block'; // Ensure it's visible
                                    inputGroup.parentNode.insertBefore(feedback, inputGroup.nextSibling);
                                }
                            }
                            isValid = false;
                        } else {
                            input.classList.remove('is-invalid');

                            // Remove feedback message if present
                            const inputGroup = input.closest('.input-group');
                            if (inputGroup && inputGroup.nextElementSibling && inputGroup.nextElementSibling.classList.contains('invalid-feedback')) {
                                inputGroup.nextElementSibling.remove();
                            }
                        }
                    });

                    // Check all program inputs
                    const programInputs = section.querySelectorAll('#programInputsContainer input[name="programs[]"]');
                    programInputs.forEach(input => {
                        if (!input.value.trim()) {
                            if (validationTriggered) {
                                input.classList.add('is-invalid');

                                // Add feedback message if not already present
                                const inputGroup = input.closest('.input-group');
                                if (inputGroup && (!inputGroup.nextElementSibling || !inputGroup.nextElementSibling.classList.contains('invalid-feedback'))) {
                                    const feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    feedback.textContent = 'This field is required';
                                    feedback.style.display = 'block'; // Ensure it's visible
                                    inputGroup.parentNode.insertBefore(feedback, inputGroup.nextSibling);
                                }
                            }
                            isValid = false;
                        } else {
                            input.classList.remove('is-invalid');

                            // Remove feedback message if present
                            const inputGroup = input.closest('.input-group');
                            if (inputGroup && inputGroup.nextElementSibling && inputGroup.nextElementSibling.classList.contains('invalid-feedback')) {
                                inputGroup.nextElementSibling.remove();
                            }
                        }
                    });

                    // Check if all fields have values (for completion status)
                    const allFieldsFilled = checkAllFieldsFilled(sectionId);

                    // Update section header and nav item to indicate errors and completion status
                    if (validationTriggered) {
                        updateSectionStatus(sectionId, isValid, allFieldsFilled);
                    } else {
                        // Even without validation triggered, update completion status
                        updateOfficeProgramsCompletionStatus();
                    }

                    return isValid;
                }

                // Special handling for agenda-section with radio buttons
                if (sectionId === 'agenda-section') {
                    // Existing code for agenda-section validation
                    const radioButtons = section.querySelectorAll('input[name="agenda_type"]');
                    const isAnyRadioSelected = Array.from(radioButtons).some(radio => radio.checked);

                    if (!isAnyRadioSelected && validationTriggered) {
                        // Add error styling to the radio buttons container
                        const container = section.querySelector('.modern-options-container');
                        if (container) {
                            container.classList.add('is-invalid');

                            // Add feedback message if not already present
                            if (!container.nextElementSibling || !container.nextElementSibling.classList.contains('invalid-feedback')) {
                                const feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                feedback.textContent = 'Please select an agenda type';
                                container.parentNode.insertBefore(feedback, container.nextSibling);
                            }
                        }
                        isValid = false;
                    } else {
                        // Remove error styling
                        const container = section.querySelector('.modern-options-container');
                        if (container) {
                            container.classList.remove('is-invalid');

                            // Remove feedback message if present
                            if (container.nextElementSibling && container.nextElementSibling.classList.contains('invalid-feedback')) {
                                container.nextElementSibling.remove();
                            }
                        }
                    }

                    // Update section header and nav item to indicate errors only if validation has been triggered
                    if (validationTriggered) {
                        updateSectionStatus(sectionId, isValid);
                    }

                    return isValid;
                }

                // Special handling for sdgs-section with checkboxes
                if (sectionId === 'sdgs-section') {
                    console.log('SDG section validation running');
                    // Verify section.querySelector is not null before using it
                    if (!section) {
                        console.error('SDG section is null!');
                        return false;
                    }

                    // Get all inputs in the section for debugging
                    const allInputs = section.querySelectorAll('input');
                    console.log('All inputs in sdgs-section:', allInputs.length);

                    // Try to find SDG checkboxes by name="sdgs[]"
                    let checkboxes = section.querySelectorAll('input[name="sdgs[]"]');
                    console.log('SDG checkboxes found by name="sdgs[]":', checkboxes.length);

                    // If no checkboxes found, try alternative approaches
                    if (checkboxes.length === 0) {
                        console.log('No SDG checkboxes found by name="sdgs[]", trying alternatives...');

                        // Try by class
                        checkboxes = section.querySelectorAll('input.modern-radio');
                        console.log('SDG checkboxes found by class="modern-radio":', checkboxes.length);

                        // If still no checkboxes, try by id pattern
                        if (checkboxes.length === 0) {
                            checkboxes = section.querySelectorAll('input[id^="sdg-"]');
                            console.log('SDG checkboxes found by id pattern "sdg-":', checkboxes.length);
                        }

                        // If still no checkboxes, try all checkboxes in the section
                        if (checkboxes.length === 0) {
                            checkboxes = section.querySelectorAll('input[type="checkbox"]');
                            console.log('Falling back to all checkboxes in section:', checkboxes.length);
                        }
                    }

                    // List all checkboxes and their checked state
                    Array.from(checkboxes).forEach((cb, i) => {
                        console.log(`SDG checkbox ${i+1} - id: ${cb.id}, value: ${cb.value}, checked: ${cb.checked}`);
                    });

                    const isAnyCheckboxSelected = Array.from(checkboxes).some(checkbox => checkbox.checked);
                    console.log('Any SDG checkbox selected:', isAnyCheckboxSelected);
                    console.log('Validation triggered:', validationTriggered);

                    // IMPORTANT: This section should report as INVALID if no checkboxes are selected!
                    if (!isAnyCheckboxSelected) {
                        console.log('🔴 SDG section should be INVALID - no checkboxes selected!');
                        isValid = false;
                    } else {
                        console.log('✅ SDG section is valid - checkboxes selected');
                    }

                    // If no checkboxes are selected, mark as invalid
                    if (!isAnyCheckboxSelected) {
                        isValid = false;

                        // Add error styling only if validation has been triggered
                        if (validationTriggered) {
                            // Add error styling to the checkbox container
                            const container = section.querySelector('.modern-options-container');
                            if (container) {
                                container.classList.add('is-invalid');

                                // Add feedback message if not already present
                                if (!container.nextElementSibling || !container.nextElementSibling.classList.contains('invalid-feedback')) {
                                    const feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    feedback.textContent = 'Please select at least one SDG';
                                    feedback.style.display = 'block'; // Ensure visible
                                    container.parentNode.insertBefore(feedback, container.nextSibling);
                                }
                            }

                            // Update section title with error
                            const sectionTitle = document.querySelector('#sdgs-section .section-title');
                            if (sectionTitle) {
                                sectionTitle.classList.add('has-error');
                            }

                            // Update nav item to show error
                            const navItem = document.querySelector('.form-nav-item[data-section="sdgs-section"]');
                            if (navItem) {
                                navItem.classList.add('has-error');
                                navItem.classList.remove('is-complete');
                            }
                        }
                    } else {
                        // Remove error styling
                        const container = section.querySelector('.modern-options-container');
                        if (container) {
                            container.classList.remove('is-invalid');

                            // Remove feedback message if present
                            if (container.nextElementSibling && container.nextElementSibling.classList.contains('invalid-feedback')) {
                                container.nextElementSibling.remove();
                            }
                        }

                        // Remove error from section title
                        const sectionTitle = document.querySelector('#sdgs-section .section-title');
                        if (sectionTitle) {
                            sectionTitle.classList.remove('has-error');
                        }

                        // Mark nav item as complete if valid
                        const navItem = document.querySelector('.form-nav-item[data-section="sdgs-section"]');
                        if (navItem) {
                            navItem.classList.remove('has-error');
                            navItem.classList.add('is-complete');
                        }
                    }

                    // Update section header and nav item to indicate completion/error status
                    if (validationTriggered) {
                        updateSectionStatus(sectionId, isValid, isAnyCheckboxSelected);
                    }

                    return isValid;
                }

                // Special handling for project-team section
                if (sectionId === 'project-team') {
                    // Check all Project Leaders
                    const leaderCards = section.querySelectorAll('#projectLeadersContainer .team-member-card');
                    leaderCards.forEach(card => {
                        // Check name field
                        const leaderName = card.querySelector('.personnel-autocomplete');
                        if (leaderName) {
                            if (!leaderName.value.trim()) {
                                if (validationTriggered) {
                                    markAsInvalid(leaderName);
                                }
                                isValid = false;
                            } else {
                                // Value is not empty, but check if it's a valid personnel
                                if (validationTriggered && !leaderName.hasAttribute('data-personnel-id')) {
                                    // Get the campus value
                                    const campus = document.getElementById('campus').value;
                                    markAsInvalid(leaderName, `This personnel does not exist in ${campus}`);
                                    isValid = false;
                                } else {
                                    markAsValid(leaderName);
                                }
                            }
                        }

                        // Check tasks
                        const leaderTasks = card.querySelectorAll('.tasks-container input[type="text"]');
                        leaderTasks.forEach(input => {
                            if (!input.value.trim()) {
                                if (validationTriggered) {
                                    input.classList.add('is-invalid');

                                    // Add feedback message if not already present
                                    const inputGroup = input.closest('.input-group');
                                    if (inputGroup && (!inputGroup.nextElementSibling || !inputGroup.nextElementSibling.classList.contains('invalid-feedback'))) {
                                        const feedback = document.createElement('div');
                                        feedback.className = 'invalid-feedback';
                                        feedback.textContent = 'This field is required';
                                        feedback.style.display = 'block'; // Ensure it's visible
                                        inputGroup.parentNode.insertBefore(feedback, inputGroup.nextSibling);
                                    }
                                }
                                isValid = false;
                            } else {
                                input.classList.remove('is-invalid');

                                // Remove feedback message if present
                                const inputGroup = input.closest('.input-group');
                                if (inputGroup && inputGroup.nextElementSibling && inputGroup.nextElementSibling.classList.contains('invalid-feedback')) {
                                    inputGroup.nextElementSibling.remove();
                                }
                            }
                        });
                    });

                    // Check all Assistant Project Leaders
                    const asstLeaderCards = section.querySelectorAll('#assistantLeadersContainer .team-member-card');
                    asstLeaderCards.forEach(card => {
                        // Check name field
                        const asstLeaderName = card.querySelector('.personnel-autocomplete');
                        if (asstLeaderName) {
                            if (!asstLeaderName.value.trim()) {
                                if (validationTriggered) {
                                    markAsInvalid(asstLeaderName);
                                }
                                isValid = false;
                            } else {
                                // Value is not empty, but check if it's a valid personnel
                                if (validationTriggered && !asstLeaderName.hasAttribute('data-personnel-id')) {
                                    // Get the campus value
                                    const campus = document.getElementById('campus').value;
                                    markAsInvalid(asstLeaderName, `This personnel does not exist in ${campus}`);
                                    isValid = false;
                                } else {
                                    markAsValid(asstLeaderName);
                                }
                            }
                        }

                        // Check tasks
                        const asstLeaderTasks = card.querySelectorAll('.tasks-container input[type="text"]');
                        asstLeaderTasks.forEach(input => {
                            if (!input.value.trim()) {
                                if (validationTriggered) {
                                    input.classList.add('is-invalid');

                                    // Add feedback message if not already present
                                    const inputGroup = input.closest('.input-group');
                                    if (inputGroup && (!inputGroup.nextElementSibling || !inputGroup.nextElementSibling.classList.contains('invalid-feedback'))) {
                                        const feedback = document.createElement('div');
                                        feedback.className = 'invalid-feedback';
                                        feedback.textContent = 'This field is required';
                                        feedback.style.display = 'block'; // Ensure it's visible
                                        inputGroup.parentNode.insertBefore(feedback, inputGroup.nextSibling);
                                    }
                                }
                                isValid = false;
                            } else {
                                input.classList.remove('is-invalid');

                                // Remove feedback message if present
                                const inputGroup = input.closest('.input-group');
                                if (inputGroup && inputGroup.nextElementSibling && inputGroup.nextElementSibling.classList.contains('invalid-feedback')) {
                                    inputGroup.nextElementSibling.remove();
                                }
                            }
                        });
                    });

                    // Check all Project Staff
                    const staffCards = section.querySelectorAll('#projectStaffContainer .team-member-card');
                    staffCards.forEach(card => {
                        // Check name field
                        const staffName = card.querySelector('.personnel-autocomplete');
                        if (staffName) {
                            if (!staffName.value.trim()) {
                                if (validationTriggered) {
                                    markAsInvalid(staffName);
                                }
                                isValid = false;
                            } else {
                                // Value is not empty, but check if it's a valid personnel
                                if (validationTriggered && !staffName.hasAttribute('data-personnel-id')) {
                                    // Get the campus value
                                    const campus = document.getElementById('campus').value;
                                    markAsInvalid(staffName, `This personnel does not exist in ${campus}`);
                                    isValid = false;
                                } else {
                                    markAsValid(staffName);
                                }
                            }
                        }

                        // Check tasks
                        const staffTasks = card.querySelectorAll('.tasks-container input[type="text"]');
                        staffTasks.forEach(input => {
                            if (!input.value.trim()) {
                                if (validationTriggered) {
                                    input.classList.add('is-invalid');

                                    // Add feedback message if not already present
                                    const inputGroup = input.closest('.input-group');
                                    if (inputGroup && (!inputGroup.nextElementSibling || !inputGroup.nextElementSibling.classList.contains('invalid-feedback'))) {
                                        const feedback = document.createElement('div');
                                        feedback.className = 'invalid-feedback';
                                        feedback.textContent = 'This field is required';
                                        feedback.style.display = 'block'; // Ensure it's visible
                                        inputGroup.parentNode.insertBefore(feedback, inputGroup.nextSibling);
                                    }
                                }
                                isValid = false;
                            } else {
                                input.classList.remove('is-invalid');

                                // Remove feedback message if present
                                const inputGroup = input.closest('.input-group');
                                if (inputGroup && inputGroup.nextElementSibling && inputGroup.nextElementSibling.classList.contains('invalid-feedback')) {
                                    inputGroup.nextElementSibling.remove();
                                }
                            }
                        });
                    });

                    // Update section header and nav item to indicate errors
                    if (validationTriggered) {
                        updateSectionStatus(sectionId, isValid);

                        // Explicitly set the error class on the navigation item if validation fails
                        const navItem = document.querySelector(`.form-nav-item[data-section="${sectionId}"]`);
                        if (navItem) {
                            if (!isValid) {
                                navItem.classList.add('has-error');

                                // Also add error class to section title for visual feedback
                                const sectionTitle = section.querySelector('.section-title');
                                if (sectionTitle) {
                                    sectionTitle.classList.add('has-error');
                                }
                            } else {
                                navItem.classList.remove('has-error');

                                // Remove error class from section title
                                const sectionTitle = section.querySelector('.section-title');
                                if (sectionTitle) {
                                    sectionTitle.classList.remove('has-error');
                                }
                            }
                        }
                    }

                    return isValid;
                }

                // Get all required inputs in the section
                const inputs = section.querySelectorAll('input:not([type="button"]):not([readonly]), select:not([disabled]), textarea');

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

                // Update section header and nav item to indicate errors only if validation has been triggered
                if (validationTriggered) {
                    updateSectionStatus(sectionId, isValid);
                }

                return isValid;
            }

            // Function to update section header and nav status
            function updateSectionStatus(sectionId, isValid, isComplete) {
                const navItem = document.querySelector(`.form-nav-item[data-section="${sectionId}"]`);
                const sectionTitle = document.querySelector(`#${sectionId} .section-title`);

                // Skip if navItem doesn't exist
                if (!navItem) {
                    console.warn(`Navigation item for section ${sectionId} not found`);
                    return;
                }

                console.log(`🔄 Updating section ${sectionId} - isValid: ${isValid}, isComplete: ${isComplete}`);

                if (!isValid) {
                    console.log(`🔴 Marking section ${sectionId} as INVALID - adding 'has-error' class`);
                    navItem.classList.add('has-error');
                    navItem.classList.remove('is-complete');
                    if (sectionTitle) {
                        sectionTitle.classList.add('has-error');
                    }
                } else {
                    console.log(`✅ Section ${sectionId} is valid - removing 'has-error' class`);
                    navItem.classList.remove('has-error');
                    if (sectionTitle) sectionTitle.classList.remove('has-error');

                    // Use the provided isComplete parameter if available, otherwise check all fields
                    if (isComplete !== undefined) {
                        if (isComplete) {
                            console.log(`✅ Section ${sectionId} is complete - adding 'is-complete' class`);
                            navItem.classList.add('is-complete');
                        } else {
                            console.log(`🔶 Section ${sectionId} is not complete - removing 'is-complete' class`);
                            navItem.classList.remove('is-complete');
                        }
                    } else {
                        // Check if all fields in this section have values (not just valid but actually filled)
                        const allFieldsFilled = checkAllFieldsFilled(sectionId);
                        if (allFieldsFilled) {
                            console.log(`✅ Section ${sectionId} has all fields filled - adding 'is-complete' class`);
                            navItem.classList.add('is-complete');
                        } else {
                            console.log(`🔶 Section ${sectionId} doesn't have all fields filled - removing 'is-complete' class`);
                            navItem.classList.remove('is-complete');
                        }
                    }
                }
            }

            // Function to check for duplicate activities
            function checkDuplicateActivity(activityValue) {
                const activityInput = document.getElementById('activity');
                const errorDiv = document.getElementById('activity-error');

                // Reset validation state
                activityInput.classList.remove('is-invalid');
                errorDiv.textContent = ''; // Clear error message text

                // Don't check if empty
                if (!activityValue) {
                    activityInput.dataset.isDuplicate = 'false';
                    // Update gender issue section status
                    updateSectionStatus('gender-issue', checkAllFieldsFilled('gender-issue'));
                    return;
                }

                // Check if activity already exists
                fetch(`check_duplicate_activity.php?activity=${encodeURIComponent(activityValue)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.exists) {
                            // Mark as invalid if activity exists
                            activityInput.classList.add('is-invalid');
                            errorDiv.textContent = data.message || 'This activity already exists.';

                            // Store the duplicate status on the input for form validation
                            activityInput.dataset.isDuplicate = 'true';

                            // Mark gender issue section as incomplete
                            const navItem = document.querySelector('.form-nav-item[data-section="gender-issue"]');
                            if (navItem) {
                                navItem.classList.remove('is-complete');
                                if (validationTriggered) {
                                    navItem.classList.add('has-error');

                                    // Add error to section title
                                    const sectionTitle = document.querySelector('#gender-issue .section-title');
                                    if (sectionTitle) {
                                        sectionTitle.classList.add('has-error');
                                    }
                                }
                            }
                        } else {
                            // Clear any previous error
                            activityInput.classList.remove('is-invalid');
                            errorDiv.textContent = ''; // Ensure error message is cleared
                            activityInput.dataset.isDuplicate = 'false';

                            // Update gender issue section status
                            updateSectionStatus('gender-issue', checkAllFieldsFilled('gender-issue'));
                        }
                    })
                    .catch(error => {
                        console.error('Error checking duplicate activity:', error);
                        // Also clear error on exception
                        activityInput.classList.remove('is-invalid');
                        errorDiv.textContent = '';
                        activityInput.dataset.isDuplicate = 'false';

                        // Update gender issue section status
                        updateSectionStatus('gender-issue', checkAllFieldsFilled('gender-issue'));
                    });
            }

            // Function to check if all fields in a section are filled
            function checkAllFieldsFilled(sectionId) {
                const section = document.getElementById(sectionId);

                // Special handling for agenda-section with radio buttons
                if (sectionId === 'agenda-section') {
                    const radioButtons = section.querySelectorAll('input[name="agenda_type"]');
                    const isAnyRadioSelected = Array.from(radioButtons).some(radio => radio.checked);
                    return isAnyRadioSelected;
                }

                // Special handling for sdgs-section with checkboxes
                if (sectionId === 'sdgs-section') {
                    const checkboxes = section.querySelectorAll('input[name="sdgs[]"]');
                    const isAnyCheckboxSelected = Array.from(checkboxes).some(checkbox => checkbox.checked);
                    console.log(`checkAllFieldsFilled for sdgs-section: ${isAnyCheckboxSelected ? 'YES - some SDGs are selected' : 'NO - no SDGs selected'}`);

                    // IMPORTANT: If no SDGs are selected and validation is triggered, ensure it's marked as 'has-error'
                    if (!isAnyCheckboxSelected && validationTriggered) {
                        console.log('⚠️ SDGs section incomplete - calling updateSectionStatus with isValid=false');
                        updateSectionStatus('sdgs-section', false);
                    }

                    return isAnyCheckboxSelected;
                }

                // Special handling for office-programs section with dynamic fields
                if (sectionId === 'office-programs') {
                    const officeInputs = section.querySelectorAll('input[name="offices[]"]');
                    const programInputs = section.querySelectorAll('input[name="programs[]"]');

                    // Check if any office input is empty
                    const allOfficesFilled = Array.from(officeInputs).every(input => input.value.trim() !== '');

                    // Check if any program input is empty
                    const allProgramsFilled = Array.from(programInputs).every(input => input.value.trim() !== '');

                    return allOfficesFilled && allProgramsFilled;
                }

                // Special handling for section-10 (Finance Section)
                if (sectionId === 'section-10') {
                    console.log('⚡ checkAllFieldsFilled for FINANCE section');

                    // Fix: Check both by ID and also by name to ensure we find the radio buttons
                    // First try by ID
                    let withFinancialPlanRadio = document.getElementById('withFinancialPlan');
                    let withoutFinancialPlanRadio = document.getElementById('withoutFinancialPlan');

                    // If not found, try by name and value
                    if (!withFinancialPlanRadio || !withoutFinancialPlanRadio) {
                        console.log('Radio buttons not found by ID, trying by name...');
                        const radiosByName = document.querySelectorAll('input[name="hasFinancialPlan"]');
                        console.log('Found radio buttons by name:', radiosByName.length);

                        Array.from(radiosByName).forEach(radio => {
                            console.log(`Radio ${radio.id}, value: ${radio.value}, checked: ${radio.checked}`);
                            if (radio.value === '1') withFinancialPlanRadio = radio;
                            if (radio.value === '0') withoutFinancialPlanRadio = radio;
                        });
                    }

                    const hasFinancialPlanSelection = (withFinancialPlanRadio && withFinancialPlanRadio.checked) ||
                        (withoutFinancialPlanRadio && withoutFinancialPlanRadio.checked);
                    console.log('Has financial plan selection:', hasFinancialPlanSelection);

                    // Check source of fund
                    const sourceOfFund = document.getElementById('sourceOfFund');
                    const hasSourceFund = sourceOfFund && sourceOfFund.value.trim() !== '';
                    console.log('Has source fund:', hasSourceFund);

                    // Check financial note
                    const financialNote = document.getElementById('financialNote');
                    const hasFinancialNote = financialNote && financialNote.value.trim() !== '';
                    console.log('Has financial note:', hasFinancialNote);

                    // Check approved budget
                    const approvedBudget = document.getElementById('approvedBudget');
                    const hasApprovedBudget = approvedBudget &&
                        approvedBudget.value.trim() !== '' &&
                        approvedBudget.value.trim() !== '0.00';
                    console.log('Has approved budget:', hasApprovedBudget);

                    // For with financial plan, check financial plan items
                    let financialItemsValid = true;
                    if (withFinancialPlanRadio && withFinancialPlanRadio.checked) {
                        const financialPlanRows = document.querySelectorAll('#financialPlanTableBody tr');
                        const hasFinancialPlanItems = financialPlanRows.length > 0;
                        console.log('With plan selected, has items:', hasFinancialPlanItems, 'Count:', financialPlanRows.length);

                        // If no items, it's invalid
                        if (!hasFinancialPlanItems) {
                            financialItemsValid = false;
                        } else {
                            // Check if each row has complete data
                            financialPlanRows.forEach((row, i) => {
                                const description = row.querySelector('.item-description');
                                const quantity = row.querySelector('.item-quantity');
                                const unit = row.querySelector('.item-unit');
                                const unitCost = row.querySelector('.item-unit-cost');

                                const hasDescription = description && description.value.trim() !== '';
                                const hasQuantity = quantity && quantity.value.trim() !== '';
                                const hasUnit = unit && unit.value.trim() !== '';
                                const hasUnitCost = unitCost && unitCost.value.trim() !== '';

                                console.log(`Item ${i+1}: Description: ${hasDescription}, Quantity: ${hasQuantity}, Unit: ${hasUnit}, Cost: ${hasUnitCost}`);

                                if (!hasDescription || !hasQuantity || !hasUnit || !hasUnitCost) {
                                    financialItemsValid = false;
                                }
                            });
                        }
                    }

                    // Final validation result for finance section
                    let financeComplete = hasFinancialPlanSelection && hasSourceFund && hasFinancialNote && hasApprovedBudget;

                    // If "With Financial Plan" is selected, also check items
                    if (withFinancialPlanRadio && withFinancialPlanRadio.checked) {
                        financeComplete = financeComplete && financialItemsValid;
                    }

                    console.log('Finance section complete:', financeComplete);

                    // IMPORTANT: If section is not complete, ensure it's marked as 'has-error' by calling updateSectionStatus directly
                    if (!financeComplete && validationTriggered) {
                        console.log('⚠️ Finance section incomplete - calling updateSectionStatus with isValid=false');
                        updateSectionStatus('section-10', false);
                    }

                    // Return whether section is complete
                    return financeComplete;
                }

                // Special handling for project-team section
                if (sectionId === 'project-team') {
                    // Check all Project Leaders
                    const leaderCards = section.querySelectorAll('#projectLeadersContainer .team-member-card');
                    let allLeadersFilled = true;

                    leaderCards.forEach(card => {
                        const nameField = card.querySelector('.personnel-autocomplete');
                        const taskFields = card.querySelectorAll('.tasks-container input[type="text"]');

                        // Check if name is filled
                        const nameIsFilled = nameField && nameField.value.trim() !== '';

                        // Check if all tasks are filled
                        const tasksAreFilled = Array.from(taskFields).every(input => input.value.trim() !== '');

                        // If any field is empty, mark the section as incomplete
                        if (!nameIsFilled || !tasksAreFilled) {
                            allLeadersFilled = false;
                        }
                    });

                    // Check all Assistant Project Leaders
                    const asstLeaderCards = section.querySelectorAll('#assistantLeadersContainer .team-member-card');
                    let allAsstLeadersFilled = true;

                    asstLeaderCards.forEach(card => {
                        const nameField = card.querySelector('.personnel-autocomplete');
                        const taskFields = card.querySelectorAll('.tasks-container input[type="text"]');

                        // Check if name is filled
                        const nameIsFilled = nameField && nameField.value.trim() !== '';

                        // Check if all tasks are filled
                        const tasksAreFilled = Array.from(taskFields).every(input => input.value.trim() !== '');

                        // If any field is empty, mark the section as incomplete
                        if (!nameIsFilled || !tasksAreFilled) {
                            allAsstLeadersFilled = false;
                        }
                    });

                    // Check all Project Staff
                    const staffCards = section.querySelectorAll('#projectStaffContainer .team-member-card');
                    let allStaffFilled = true;

                    staffCards.forEach(card => {
                        const nameField = card.querySelector('.personnel-autocomplete');
                        const taskFields = card.querySelectorAll('.tasks-container input[type="text"]');

                        // Check if name is filled
                        const nameIsFilled = nameField && nameField.value.trim() !== '';

                        // Check if all tasks are filled
                        const tasksAreFilled = Array.from(taskFields).every(input => input.value.trim() !== '');

                        // If any field is empty, mark the section as incomplete
                        if (!nameIsFilled || !tasksAreFilled) {
                            allStaffFilled = false;
                        }
                    });

                    // All fields need to be filled
                    return allLeadersFilled && allAsstLeadersFilled && allStaffFilled;
                }

                // Get all required inputs in the section
                const inputs = section.querySelectorAll('input:not([type="button"]):not([readonly]), select, textarea');

                for (const input of inputs) {
                    if (input.hasAttribute('required') || (input.id && isRequiredField(input.id))) {
                        if (!input.value.trim()) {
                            return false;
                        }
                    }
                }

                return true;
            }

            // Function to check all sections
            function checkAllSectionsStatus() {
                // Only perform validation if validation has been triggered
                if (!validationTriggered) return;

                const sections = ['gender-issue', 'basic-info', 'agenda-section', 'sdgs-section', 'office-programs', 'project-team', 'section-6', 'section-7', 'section-8', 'section-9', 'section-10', 'section-11'];
                sections.forEach(sectionId => {
                    // Only validate if the section exists in the DOM
                    if (document.getElementById(sectionId)) {
                        const isValid = validateSection(sectionId);
                        // No need to call updateSectionStatus, already called in validateSection
                    }
                });
            }

            // Function to validate all sections
            function validateAllSections() {
                validationTriggered = true;
                console.log('🚀 VALIDATION TRIGGERED - validateAllSections called 🚀');

                // Only include sections that actually exist in the form
                const sections = ['gender-issue', 'basic-info', 'agenda-section', 'sdgs-section', 'office-programs', 'project-team', 'section-6', 'section-7', 'section-8', 'section-9', 'section-10', 'section-11'];
                let isFormValid = true;

                console.log('==== STARTING DETAILED VALIDATION ====');

                // Debug which sections exist
                console.log('Checking sections existence:');
                sections.forEach(sectionId => {
                    const exists = document.getElementById(sectionId) !== null;
                    console.log(`Section ${sectionId} exists: ${exists}`);
                });

                sections.forEach(sectionId => {
                    // Check if section exists in the DOM before validating
                    if (document.getElementById(sectionId)) {
                        console.log(`Validating section: ${sectionId}`);
                        const isValid = validateSection(sectionId);
                        console.log(`Section ${sectionId} valid: ${isValid}`);
                        if (!isValid) {
                            isFormValid = false;
                        }
                    } else {
                        console.warn(`Skipping section ${sectionId} - not found in DOM`);
                    }
                });

                // Navigate to the first section with errors
                if (!isFormValid) {
                    for (const sectionId of sections) {
                        const navItem = document.querySelector(`.form-nav-item[data-section="${sectionId}"].has-error`);
                        if (navItem) {
                            navigateToSection(sectionId);
                            break;
                        }
                    }
                }

                return isFormValid;
            }

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

                // Handle form navigation buttons
                const navButtons = document.querySelectorAll('.btn-form-nav[data-navigate-to]');
                navButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const targetSection = this.getAttribute('data-navigate-to');
                        navigateToSection(targetSection);
                    });
                });

                // Handle section header navigation
                const navItems = document.querySelectorAll('.form-nav-item');
                navItems.forEach(item => {
                    item.addEventListener('click', function() {
                        const targetSection = this.getAttribute('data-section');
                        navigateToSection(targetSection);
                    });
                });

                // Handle section group navigation
                const nextGroupBtn = document.getElementById('nav-next-group');
                const prevGroupBtn = document.getElementById('nav-prev-group');

                if (nextGroupBtn) {
                    nextGroupBtn.addEventListener('click', function() {
                        showSectionGroup(2);
                        // Navigate to the first section in the second group
                        navigateToSection('section-7');
                    });
                }

                if (prevGroupBtn) {
                    prevGroupBtn.addEventListener('click', function() {
                        showSectionGroup(1);
                        // Navigate to the first section in the first group
                        navigateToSection('gender-issue');
                    });
                }

                // Handle Add button click
                const addBtn = document.getElementById('addBtn');
                if (addBtn) {
                    addBtn.addEventListener('click', function() {
                        // Explicitly set validationTriggered to true to ensure all validations run
                        validationTriggered = true;

                        // Trigger validation without showing error modal
                        validateAllSections();

                        // Extra handling for SDG section - ensure error styling is applied
                        const sdgsSection = document.getElementById('sdgs-section');
                        if (sdgsSection) {
                            const checkboxes = sdgsSection.querySelectorAll('input[name="sdgs[]"]');
                            const isAnyCheckboxSelected = Array.from(checkboxes).some(checkbox => checkbox.checked);

                            if (!isAnyCheckboxSelected) {
                                // Manually add error styling to the checkbox container
                                const container = sdgsSection.querySelector('.modern-options-container');
                                if (container) {
                                    container.classList.add('is-invalid');

                                    // Add feedback message if not already present
                                    if (!container.nextElementSibling || !container.nextElementSibling.classList.contains('invalid-feedback')) {
                                        const feedback = document.createElement('div');
                                        feedback.className = 'invalid-feedback';
                                        feedback.textContent = 'Please select at least one SDG';
                                        feedback.style.display = 'block'; // Ensure visible
                                        container.parentNode.insertBefore(feedback, container.nextSibling);
                                    }
                                }
                            }
                        }

                        // Extra handling for Finance section - ensure error styling is applied 
                        const financeSection = document.getElementById('section-10');
                        if (financeSection) {
                            // First check financial plan radio buttons
                            // Handle both possible id formats
                            let withFinancialPlanRadio = document.getElementById('withFinancialPlan');
                            let withoutFinancialPlanRadio = document.getElementById('withoutFinancialPlan');

                            // If not found, try by name
                            if (!withFinancialPlanRadio || !withoutFinancialPlanRadio) {
                                const radiosByName = document.querySelectorAll('input[name="hasFinancialPlan"]');
                                Array.from(radiosByName).forEach(radio => {
                                    if (radio.value === '1') withFinancialPlanRadio = radio;
                                    if (radio.value === '0') withoutFinancialPlanRadio = radio;
                                });
                            }

                            const hasFinancialPlanSelection = (withFinancialPlanRadio && withFinancialPlanRadio.checked) ||
                                (withoutFinancialPlanRadio && withoutFinancialPlanRadio.checked);

                            if (!hasFinancialPlanSelection) {
                                // Add error styling to the radio buttons container
                                const radioContainer = financeSection.querySelector('.form-group:nth-child(2)');
                                if (radioContainer) {
                                    radioContainer.classList.add('is-invalid');

                                    // Add feedback message if not already present
                                    if (!radioContainer.querySelector('.invalid-feedback')) {
                                        const feedback = document.createElement('div');
                                        feedback.className = 'invalid-feedback';
                                        feedback.textContent = 'Please select a financial plan option';
                                        feedback.style.display = 'block'; // Ensure it's visible
                                        radioContainer.appendChild(feedback);
                                    }
                                }
                            }

                            // Check financial plan items if "With Financial Plan" is selected
                            if (withFinancialPlanRadio && withFinancialPlanRadio.checked) {
                                const financialPlanRows = document.querySelectorAll('#financialPlanTableBody tr');
                                const financialPlanSection = document.getElementById('financialPlanSection');

                                if (financialPlanRows.length === 0 && financialPlanSection) {
                                    // No items in the financial plan table
                                    financialPlanSection.classList.add('is-invalid');

                                    // Add feedback message if not already present
                                    if (!financialPlanSection.querySelector('.invalid-feedback:not(#emptyFinancialPlanMessage)')) {
                                        const feedback = document.createElement('div');
                                        feedback.className = 'invalid-feedback';
                                        feedback.textContent = 'Please add at least one financial item';
                                        feedback.style.display = 'block'; // Ensure it's visible
                                        financialPlanSection.appendChild(feedback);
                                    }
                                }
                            }

                            // Check source of fund
                            const sourceOfFund = document.getElementById('sourceOfFund');
                            const sourceFundContainer = document.getElementById('sourceFundContainer');

                            if ((!sourceOfFund || !sourceOfFund.value) && sourceFundContainer) {
                                sourceFundContainer.classList.add('is-invalid');

                                // Add feedback message if not already present
                                if (!sourceFundContainer.nextElementSibling || !sourceFundContainer.nextElementSibling.classList.contains('invalid-feedback')) {
                                    const feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    feedback.textContent = 'Please select at least one source of fund';
                                    feedback.style.display = 'block'; // Ensure it's visible
                                    sourceFundContainer.parentNode.insertBefore(feedback, sourceFundContainer.nextSibling);
                                }
                            }

                            // Check approved budget
                            const approvedBudget = document.getElementById('approvedBudget');
                            if (approvedBudget && (!approvedBudget.value || approvedBudget.value === '0.00')) {
                                approvedBudget.closest('.input-group').classList.add('is-invalid');

                                // Add feedback message if not already present
                                const approvedBudgetGroup = approvedBudget.closest('.form-group');
                                if (approvedBudgetGroup && (!approvedBudgetGroup.querySelector('.invalid-feedback'))) {
                                    const feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    feedback.textContent = 'Please enter an approved budget amount';
                                    feedback.style.display = 'block'; // Ensure it's visible
                                    approvedBudgetGroup.appendChild(feedback);
                                }
                            }

                            // Check financial note
                            const financialNote = document.getElementById('financialNote');
                            if (financialNote && !financialNote.value.trim()) {
                                financialNote.classList.add('is-invalid');

                                // Add feedback message if not already present
                                if (!financialNote.nextElementSibling || !financialNote.nextElementSibling.classList.contains('invalid-feedback')) {
                                    const feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    feedback.textContent = 'Please provide financial notes';
                                    feedback.style.display = 'block'; // Ensure it's visible
                                    financialNote.parentNode.insertBefore(feedback, financialNote.nextSibling);
                                }
                            }
                        }

                        // Check for duplicate activity
                        const activityInput = document.getElementById('activity');
                        if (activityInput && activityInput.dataset.isDuplicate === 'true') {
                            // Show error message
                            Swal.fire({
                                title: 'Duplicate Activity',
                                text: 'The activity you entered already exists in your campus database. Please provide a unique activity.',
                                icon: 'error',
                                confirmButtonColor: '#6a1b9a',
                                backdrop: `
                                    rgba(0,0,0,0.7)
                                    url()
                                    center
                                    no-repeat
                                `,
                                customClass: {
                                    container: 'swal-blur-container'
                                }
                            });

                            // Mark gender-issue section as having error
                            const navItem = document.querySelector('.form-nav-item[data-section="gender-issue"]');
                            if (navItem) {
                                navItem.classList.add('has-error');
                                navItem.classList.remove('is-complete');

                                // Add error to section title
                                const sectionTitle = document.querySelector('#gender-issue .section-title');
                                if (sectionTitle) {
                                    sectionTitle.classList.add('has-error');
                                }
                            }

                            // Navigate to gender-issue section
                            navigateToSection('gender-issue');

                            // Scroll to activity field
                            activityInput.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });

                            return; // Prevent form submission
                        }

                        // Check if all sections are valid manually
                        const sections = ['gender-issue', 'basic-info', 'agenda-section', 'sdgs-section', 'office-programs', 'project-team', 'section-6', 'section-7', 'section-8', 'section-9', 'section-10', 'section-11'];
                        const allValid = sections.every(sectionId => {
                            return !document.querySelector(`.form-nav-item[data-section="${sectionId}"].has-error`);
                        });

                        if (allValid) {
                            // Collect all form data
                            const formData = new FormData(ppasForm);

                            // Set gender_issue_id from the selected genderIssue value
                            const genderIssueSelect = document.getElementById('genderIssue');
                            if (genderIssueSelect && genderIssueSelect.value) {
                                formData.append('gender_issue_id', genderIssueSelect.value);
                            }

                            // Add program, project, and activity values
                            const programInput = document.getElementById('program');
                            if (programInput) {
                                formData.append('program', programInput.value);
                            }

                            const projectInput = document.getElementById('project');
                            if (projectInput) {
                                formData.append('project', projectInput.value);
                            }

                            const activityInput = document.getElementById('activity');
                            if (activityInput) {
                                formData.append('activity', activityInput.value);
                            }

                            // Get values from location field
                            const locationVenue = document.getElementById('locationVenue');
                            if (locationVenue) {
                                formData.append('location', locationVenue.value);
                            }

                            // Make sure agenda is properly collected
                            const selectedAgenda = document.querySelector('input[name="agenda_type"]:checked');
                            if (selectedAgenda) {
                                formData.append('agenda', selectedAgenda.value);
                            } else {
                                formData.append('agenda', '');
                            }

                            // Collect array data for fields with multiple values

                            // SDGs
                            const sdgCheckboxes = document.querySelectorAll('input[name="sdgs[]"]:checked');
                            sdgCheckboxes.forEach(checkbox => {
                                formData.append('sdg[]', checkbox.value);
                            });

                            // Office/College/Organization
                            const officeFields = document.querySelectorAll('input[name="offices[]"]');
                            officeFields.forEach(field => {
                                formData.append('officeCollegeOrg[]', field.value);
                            });

                            // Program
                            const programFields = document.querySelectorAll('input[name="programs[]"]');
                            programFields.forEach(field => {
                                formData.append('programList[]', field.value);
                            });

                            // Project Team
                            // Project Leader
                            const projectLeaderFields = document.querySelectorAll('input[name="leader_name[]"]');
                            projectLeaderFields.forEach(field => {
                                formData.append('projectLeader[]', field.value);
                            });

                            // Project Leader Responsibilities
                            projectLeaderFields.forEach((field, index) => {
                                const taskInputs = document.querySelectorAll(`input[name="leader_tasks_${index+1}[]"]`);
                                const tasks = Array.from(taskInputs).map(input => input.value);
                                formData.append('projectLeaderResponsibilities[]', tasks);
                            });

                            // Assistant Project Leader
                            const assistantProjectLeaderFields = document.querySelectorAll('input[name="asst_leader_name[]"]');
                            assistantProjectLeaderFields.forEach(field => {
                                formData.append('assistantProjectLeader[]', field.value);
                            });

                            // Assistant Project Leader Responsibilities
                            assistantProjectLeaderFields.forEach((field, index) => {
                                const taskInputs = document.querySelectorAll(`input[name="asst_leader_tasks_${index+1}[]"]`);
                                const tasks = Array.from(taskInputs).map(input => input.value);
                                formData.append('assistantProjectLeaderResponsibilities[]', tasks);
                            });

                            // Project Staff/Coordinator
                            const projectStaffFields = document.querySelectorAll('input[name="staff_name[]"]');
                            projectStaffFields.forEach(field => {
                                formData.append('projectStaff[]', field.value);
                            });

                            // Project Staff/Coordinator Responsibilities
                            projectStaffFields.forEach((field, index) => {
                                const taskInputs = document.querySelectorAll(`input[name="staff_tasks_${index+1}[]"]`);
                                const tasks = Array.from(taskInputs).map(input => input.value);
                                formData.append('projectStaffResponsibilities[]', tasks);
                            });

                            // Agency and Participants
                            formData.append('internalType', document.getElementById('internal_type').value);
                            formData.append('internalMale', document.getElementById('internal_male').value);
                            formData.append('internalFemale', document.getElementById('internal_female').value);
                            formData.append('internalTotal', document.getElementById('internal_total').value);

                            formData.append('externalType', document.getElementById('external_type').value);
                            formData.append('externalMale', document.getElementById('external_male').value);
                            formData.append('externalFemale', document.getElementById('external_female').value);
                            formData.append('externalTotal', document.getElementById('external_total').value);

                            formData.append('grandTotalMale', document.getElementById('total_male').value);
                            formData.append('grandTotalFemale', document.getElementById('total_female').value);
                            formData.append('grandTotal', document.getElementById('grand_total').value);

                            // Program Description
                            formData.append('rationale', document.getElementById('rationale').value);
                            formData.append('generalObjectives', document.getElementById('general_objectives').value);

                            // Specific Objectives
                            const specificObjectivesFields = document.querySelectorAll('input[name="specific_objectives[]"]');
                            specificObjectivesFields.forEach(field => {
                                formData.append('specificObjectives[]', field.value);
                            });

                            formData.append('description', document.getElementById('description').value);

                            // Strategy
                            const strategyFields = document.querySelectorAll('input[name="strategies[]"]');
                            strategyFields.forEach(field => {
                                formData.append('strategy[]', field.value);
                            });

                            // Expected Output
                            const expectedOutputFields = document.querySelectorAll('input[name="expected_output[]"]');
                            expectedOutputFields.forEach(field => {
                                formData.append('expectedOutput[]', field.value);
                            });

                            formData.append('functionalRequirements', document.getElementById('functional_requirements').value);
                            formData.append('sustainabilityPlan', document.getElementById('sustainability_plan').value);

                            // Specific Plan
                            const specificPlanFields = document.querySelectorAll('input[name="specific_plans[]"]');
                            specificPlanFields.forEach(field => {
                                formData.append('specificPlan[]', field.value);
                            });

                            // Workplan
                            // Activities
                            const activityFields = document.querySelectorAll('input.activity-name');
                            activityFields.forEach(field => {
                                formData.append('workplanActivity[]', field.value);
                            });

                            // Collect workplan dates per activity
                            const activityRows = document.querySelectorAll('#timeline-activities .activity-row');
                            activityRows.forEach((row, index) => {
                                console.log(`Processing activity row ${index}`);

                                // Get all the checked checkboxes in this row
                                const checkedBoxes = row.querySelectorAll('input[type="checkbox"]:checked');
                                console.log(`Found ${checkedBoxes.length} checked checkboxes in row ${index}`);

                                // If no checkboxes are checked, add an empty value
                                if (checkedBoxes.length === 0) {
                                    formData.append(`workplanDate[${index}]`, '');
                                    return;
                                }

                                // Get all date values from the checked checkboxes
                                const dateValues = [];

                                // NEW APPROACH: Get all checkbox cells and their corresponding dates
                                const allCells = row.querySelectorAll('td');

                                // Debug the structure
                                console.log(`Row has ${allCells.length} cells`);

                                // Skip the first cell which is the activity name
                                for (let i = 1; i < allCells.length; i++) {
                                    const cell = allCells[i];
                                    const checkbox = cell.querySelector('input[type="checkbox"]');

                                    // If the checkbox exists and is checked
                                    if (checkbox && checkbox.checked) {
                                        // Try to get the date from the table header
                                        const headerRow = document.querySelector('#timeline-table thead tr');
                                        if (headerRow && headerRow.children[i]) {
                                            const dateText = headerRow.children[i].textContent.trim();
                                            console.log(`Column ${i} has date: ${dateText}`);
                                            if (dateText && dateText !== '') {
                                                dateValues.push(dateText);
                                            }
                                        }
                                    }
                                }

                                console.log(`Date values for row ${index}:`, dateValues);

                                // Add dates to form data
                                formData.append(`workplanDate[${index}]`, dateValues.join(','));
                            });

                            // Financial Plan
                            const withFinancialPlanRadio = document.getElementById('withFinancialPlan');
                            const withoutFinancialPlanRadio = document.getElementById('withoutFinancialPlan');

                            if (withFinancialPlanRadio && withFinancialPlanRadio.checked) {
                                formData.append('financialPlan', 'withFinancialPlan');

                                // Financial items
                                const itemDescriptions = document.querySelectorAll('.item-description');
                                itemDescriptions.forEach(item => {
                                    formData.append('financialPlanItems[]', item.value);
                                });

                                const quantities = document.querySelectorAll('.item-quantity');
                                quantities.forEach(item => {
                                    formData.append('financialPlanQuantity[]', item.value);
                                });

                                const units = document.querySelectorAll('.item-unit');
                                units.forEach(item => {
                                    formData.append('financialPlanUnit[]', item.value);
                                });

                                const unitCosts = document.querySelectorAll('.item-unit-cost');
                                unitCosts.forEach(item => {
                                    formData.append('financialPlanUnitCost[]', item.value);
                                });

                                formData.append('financialTotalCost', document.getElementById('totalCost').value);
                            } else if (withoutFinancialPlanRadio && withoutFinancialPlanRadio.checked) {
                                formData.append('financialPlan', 'withoutFinancialPlan');
                            }

                            // Source of Fund
                            const sourceFundInput = document.getElementById('sourceOfFund');
                            if (sourceFundInput && sourceFundInput.value) {
                                // Get values, split by comma, filter out empty values, and ensure uniqueness
                                const sourceValues = sourceFundInput.value.split(',')
                                    .filter(value => value.trim() !== '')
                                    .filter((value, index, self) => self.indexOf(value) === index); // Remove duplicates

                                // Add each unique value
                                sourceValues.forEach(value => {
                                    formData.append('sourceOfFund[]', value.trim());
                                });
                            }

                            formData.append('financialNote', document.getElementById('financialNote').value);

                            // Get approved budget and remove commas before sending
                            const approvedBudgetInput = document.getElementById('approvedBudget');
                            if (approvedBudgetInput) {
                                // Remove commas from the value
                                const approvedBudgetValue = approvedBudgetInput.value.replace(/,/g, '');
                                formData.append('approvedBudget', approvedBudgetValue);
                            }

                            formData.append('psAttribution', document.getElementById('psAttribution').value);

                            // Monitoring
                            // Loop through all monitoring items
                            const monitoringItems = document.querySelectorAll('.monitoring-item');
                            monitoringItems.forEach((item, index) => {
                                const objectives = item.querySelector(`#objectives${index+1}`);
                                if (objectives) formData.append('monitoringObjectives[]', objectives.value);

                                const baselineData = item.querySelector(`#baseline_data${index+1}`);
                                if (baselineData) formData.append('monitoringBaselineData[]', baselineData.value);

                                const dataSource = item.querySelector(`#data_source${index+1}`);
                                if (dataSource) formData.append('monitoringDataSource[]', dataSource.value);

                                const frequency = item.querySelector(`#frequency${index+1}`);
                                if (frequency) formData.append('monitoringFrequencyDataCollection[]', frequency.value);

                                const performanceIndicators = item.querySelector(`#performance_indicators${index+1}`);
                                if (performanceIndicators) formData.append('monitoringPerformanceIndicators[]', performanceIndicators.value);

                                const performanceTarget = item.querySelector(`#performance_target${index+1}`);
                                if (performanceTarget) formData.append('monitoringPerformanceTarget[]', performanceTarget.value);

                                const collectionMethod = item.querySelector(`#collection_method${index+1}`);
                                if (collectionMethod) formData.append('monitoringCollectionMethod[]', collectionMethod.value);

                                const personsInvolved = item.querySelector(`#persons_involved${index+1}`);
                                if (personsInvolved) formData.append('monitoringOfficePersonsInvolved[]', personsInvolved.value);
                            });

                            // Send AJAX request to save or update the form
                            const url = editMode ? 'update_ppas_form.php' : 'save_ppas_form.php';

                            // Add the ID for updates
                            if (editMode) {
                                formData.append('id', editingEntryId);
                            }

                            fetch(url, {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: editMode ? 'Form updated successfully' : 'Form submitted successfully',
                                            icon: 'success',
                                            timer: 1500,
                                            timerProgressBar: true,
                                            showConfirmButton: false,
                                            backdrop: `rgba(0,0,0,0.8)`,
                                            allowOutsideClick: false,
                                            customClass: {
                                                container: 'swal-blur-container'
                                            },
                                            willClose: () => {
                                                // Reload the page when the alert closes
                                                window.location.reload();
                                            }
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: data.message || 'An error occurred while ' + (editMode ? 'updating' : 'saving') + ' the form',
                                            icon: 'error',
                                            confirmButtonColor: '#6a1b9a'
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'An unexpected error occurred',
                                        icon: 'error',
                                        confirmButtonColor: '#6a1b9a'
                                    });
                                });

                        }
                    });
                }

                // Initialize form submission
                const ppasForm = document.getElementById('ppasForm');
                ppasForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Check for duplicate activity
                    const activityInput = document.getElementById('activity');
                    if (activityInput && activityInput.dataset.isDuplicate === 'true') {
                        // Highlight the activity field with error
                        activityInput.classList.add('is-invalid');
                        document.getElementById('activity-error').textContent = 'This activity already exists. Please choose a different activity.';

                        // Scroll to the activity field
                        activityInput.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });

                        // Show error message
                        Swal.fire({
                            title: 'Duplicate Activity',
                            text: 'The activity you entered already exists. Please provide a unique activity.',
                            icon: 'error',
                            confirmButtonColor: '#6a1b9a',
                            backdrop: `
                                rgba(0,0,0,0.7)
                                url()
                                center
                                no-repeat
                            `,
                            customClass: {
                                container: 'swal-blur-container'
                            }
                        });

                        return;
                    }

                    // Trigger validation
                    if (!validateAllSections()) {
                        return;
                    }
                });

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
                        checkAllFieldsAndUpdateStatus();
                    });
                });

                // Function to update completion status without showing errors
                function checkAllFieldsAndUpdateStatus() {
                    const sections = ['gender-issue', 'basic-info', 'agenda-section', 'sdgs-section', 'office-programs', 'project-team', 'section-6', 'section-7', 'section-8', 'section-9', 'section-10', 'section-11'];
                    sections.forEach(sectionId => {
                        // Only check for sections that exist in the DOM
                        if (!document.getElementById(sectionId)) return;

                        const navItem = document.querySelector(`.form-nav-item[data-section="${sectionId}"]`);
                        if (!navItem) return;

                        const allFieldsFilled = checkAllFieldsFilled(sectionId);

                        if (allFieldsFilled) {
                            navItem.classList.add('is-complete');
                        } else {
                            navItem.classList.remove('is-complete');
                        }
                    });
                }

                // Load years from gpb_entries for the logged-in user's campus
                loadYears();

                // Add event listener to year dropdown to load gender issues and enable quarter dropdown
                const yearSelect = document.getElementById('year');
                if (yearSelect) {
                    yearSelect.addEventListener('change', function() {
                        // Enable the quarter field when a year is selected
                        const quarterSelect = document.getElementById('quarter');
                        if (quarterSelect) {
                            quarterSelect.disabled = false;
                        }

                        // Reset and disable gender issue field when year changes
                        const genderIssueSelect = document.getElementById('genderIssue');
                        if (genderIssueSelect) {
                            genderIssueSelect.disabled = true;
                            genderIssueSelect.innerHTML = '<option value="" selected disabled>Select Gender Issue</option>';
                        }
                    });
                }

                // Add event listener to quarter dropdown to enable gender issue field
                const quarterSelect = document.getElementById('quarter');
                if (quarterSelect) {
                    quarterSelect.addEventListener('change', function() {
                        // Load gender issues based on year and quarter
                        loadGenderIssues();
                    });
                }

                // Add event listeners to update duration calculation
                const startTimeInput = document.getElementById('startTime');
                const endTimeInput = document.getElementById('endTime');
                const lunchBreakCheckbox = document.getElementById('lunchBreak');
                const startMonthSelect = document.getElementById('startMonth');
                const startDaySelect = document.getElementById('startDay');
                const startYearSelect = document.getElementById('startYear');
                const endMonthSelect = document.getElementById('endMonth');
                const endDaySelect = document.getElementById('endDay');
                const endYearSelect = document.getElementById('endYear');

                // Add event listeners to all time and date fields
                const durationFields = [
                    startTimeInput, endTimeInput, lunchBreakCheckbox,
                    startMonthSelect, startDaySelect, startYearSelect,
                    endMonthSelect, endDaySelect, endYearSelect
                ];

                if (durationFields.every(field => field)) {
                    durationFields.forEach(field => {
                        field.addEventListener('change', calculateTotalDuration);
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

                // Handle the "Other" option in funding source
                const fundingSourceSelect = document.getElementById('fundingSource');
                const otherSourceContainer = document.getElementById('otherSourceContainer');
                const otherFundingSourceInput = document.getElementById('otherFundingSource');

                if (fundingSourceSelect && otherSourceContainer && otherFundingSourceInput) {
                    fundingSourceSelect.addEventListener('change', function() {
                        if (this.value === 'Others') {
                            otherSourceContainer.style.display = 'block';
                            otherFundingSourceInput.setAttribute('required', 'required');
                        } else {
                            otherSourceContainer.style.display = 'none';
                            otherFundingSourceInput.removeAttribute('required');
                            otherFundingSourceInput.value = '';
                        }
                    });
                }

                // Add validation for date and time fields in the basic info section
                const dateFields = [
                    document.getElementById('startMonth'),
                    document.getElementById('startDay'),
                    document.getElementById('startYear'),
                    document.getElementById('endMonth'),
                    document.getElementById('endDay'),
                    document.getElementById('endYear')
                ];

                const timeFields = [
                    document.getElementById('startTime'),
                    document.getElementById('endTime')
                ];

                // Add additional validation for all date fields
                dateFields.forEach(field => {
                    if (field) {
                        field.addEventListener('change', function() {
                            // Check if all date fields are filled
                            if (dateFields.every(f => f && f.value)) {
                                // Create date objects for comparison
                                const startDate = new Date(
                                    document.getElementById('startYear').value,
                                    document.getElementById('startMonth').value - 1,
                                    document.getElementById('startDay').value
                                );

                                const endDate = new Date(
                                    document.getElementById('endYear').value,
                                    document.getElementById('endMonth').value - 1,
                                    document.getElementById('endDay').value
                                );

                                // Check if end date is before start date
                                if (endDate < startDate) {
                                    // Mark all date fields as invalid
                                    markDateFieldsAsInvalid(true);
                                    // Add error message if not already present
                                    const endDateField = document.getElementById('endDay');
                                    if (endDateField && (!endDateField.nextElementSibling || !endDateField.nextElementSibling.classList.contains('invalid-feedback'))) {
                                        const feedback = document.createElement('div');
                                        feedback.className = 'invalid-feedback';
                                        feedback.textContent = 'End date cannot be before start date';
                                        endDateField.parentNode.insertBefore(feedback, endDateField.nextSibling);
                                    }
                                } else {
                                    // Mark all date fields as valid
                                    markDateFieldsAsInvalid(false);
                                }

                                // Recalculate duration
                                calculateTotalDuration();
                            }
                        });
                    }
                });

                // Add additional validation for all time fields
                timeFields.forEach(field => {
                    if (field) {
                        field.addEventListener('change', function() {
                            // Check if all time fields are filled
                            if (timeFields.every(f => f && f.value)) {
                                // Recalculate duration which includes time validation
                                calculateTotalDuration();
                            }
                        });
                    }
                });

                // Add event listeners to update PS Attribution when total duration changes
                const totalDurationField = document.getElementById('totalDuration');
                if (totalDurationField) {
                    totalDurationField.addEventListener('input', function() {
                        console.log('Total duration changed:', this.value);
                        calculatePSAttribution();
                    });
                }

                // Add event listeners to update PS Attribution when personnel details change
                const personnelInputs = document.querySelectorAll('.personnel-autocomplete, .input-with-currency input');
                personnelInputs.forEach(input => {
                    input.addEventListener('input', function() {
                        console.log('Personnel input changed:', this.id, this.value);
                    });
                });

                // Update PS Attribution calculation function to include console logs
                function calculatePSAttribution() {
                    console.log('Calculating PS Attribution...');
                    const psAttributionInput = document.getElementById('psAttribution');
                    if (!psAttributionInput) return;

                    // Get total duration from the Basic Info section
                    const totalDurationField = document.getElementById('totalDuration');
                    let totalDuration = 0;
                    if (totalDurationField && totalDurationField.value && !isNaN(totalDurationField.value)) {
                        totalDuration = parseFloat(totalDurationField.value);
                    } else if (totalDurationField && totalDurationField.value && !isNaN(parseFloat(totalDurationField.value))) {
                        totalDuration = parseFloat(totalDurationField.value);
                    } else {
                        // If the value is not a number (e.g., error message), set to 0
                        totalDuration = 0;
                    }
                    if (isNaN(totalDuration) || totalDuration < 0) totalDuration = 0;
                    console.log('Total Duration used for PS Attribution:', totalDuration);

                    let totalAttribution = 0;
                    let hasTeamData = false;

                    // Sum all leader rates
                    const leaderRateInputs = document.querySelectorAll('input[name="leader_rate[]"]');
                    leaderRateInputs.forEach((input, idx) => {
                        const rate = parseFloat(input.value) || 0;
                        if (rate > 0 && totalDuration > 0) {
                            totalAttribution += rate * totalDuration;
                            hasTeamData = true;
                            console.log(`Leader #${idx+1} attribution:`, rate, totalDuration, rate * totalDuration);
                        }
                    });

                    // Sum all assistant leader rates
                    const asstLeaderRateInputs = document.querySelectorAll('input[name="asst_leader_rate[]"]');
                    asstLeaderRateInputs.forEach((input, idx) => {
                        const rate = parseFloat(input.value) || 0;
                        if (rate > 0 && totalDuration > 0) {
                            totalAttribution += rate * totalDuration;
                            hasTeamData = true;
                            console.log(`Assistant Leader #${idx+1} attribution:`, rate, totalDuration, rate * totalDuration);
                        }
                    });

                    // Sum all staff rates
                    const staffRateInputs = document.querySelectorAll('input[name="staff_rate[]"]');
                    staffRateInputs.forEach((input, idx) => {
                        const rate = parseFloat(input.value) || 0;
                        if (rate > 0 && totalDuration > 0) {
                            totalAttribution += rate * totalDuration;
                            hasTeamData = true;
                            console.log(`Staff #${idx+1} attribution:`, rate, totalDuration, rate * totalDuration);
                        }
                    });

                    psAttributionInput.value = totalAttribution.toFixed(2);
                    console.log('Total PS Attribution:', totalAttribution);

                    const psAttributionMessage = document.getElementById('psAttributionMessage');
                    if (hasTeamData) {
                        psAttributionMessage.classList.add('d-none');
                    } else {
                        psAttributionMessage.classList.remove('d-none');
                    }
                }
            });

            // Function to navigate between form sections
            function navigateToSection(sectionId) {
                // If validation is triggered, validate current section before leaving
                if (validationTriggered) {
                    const currentSection = document.querySelector('.form-section.active');
                    if (currentSection) {
                        const currentSectionId = currentSection.id;
                        validateSection(currentSectionId);
                    }
                }

                // Hide all sections and remove active class
                const allSections = document.querySelectorAll('.form-section');
                allSections.forEach(section => {
                    section.classList.remove('active');
                });

                // Show the target section
                const targetSection = document.getElementById(sectionId);
                if (targetSection) {
                    targetSection.classList.add('active');

                    // Update navigation header
                    const navItems = document.querySelectorAll('.form-nav-item');
                    navItems.forEach(item => {
                        item.classList.remove('active');
                        if (item.getAttribute('data-section') === sectionId) {
                            item.classList.add('active');
                        }
                    });

                    // Check if we need to switch section groups in the navigation
                    const sectionNumber = parseInt(sectionId.replace('section-', '')) || 0;
                    if (sectionId === 'gender-issue' || sectionId === 'basic-info' || sectionId === 'agenda-section' ||
                        sectionId === 'sdgs-section' || sectionId === 'office-programs' || sectionId === 'project-team' ||
                        (sectionNumber >= 4 && sectionNumber <= 6)) {
                        showSectionGroup(1);
                    } else if (sectionNumber >= 7 && sectionNumber <= 11) {
                        showSectionGroup(2);
                    }

                    // If we're navigating to office-programs section, check completion status
                    if (sectionId === 'office-programs') {
                        updateOfficeProgramsCompletionStatus();
                    }

                    // In edit mode, ensure the gender issue section stays validated during navigation
                    if (editMode && editingEntryId) {
                        markGenderIssueAsValid();
                    }

                    // Scroll main-content to top
                    const mainContent = document.querySelector('.main-content');
                    if (mainContent) {
                        mainContent.scrollTop = 0;
                    }
                }
            }

            // Function to show correct section group
            function showSectionGroup(groupNumber) {
                const group1 = document.getElementById('section-group-1');
                const group2 = document.getElementById('section-group-2');

                if (groupNumber === 1) {
                    group1.style.display = 'flex';
                    group2.style.display = 'none';
                } else {
                    group1.style.display = 'none';
                    group2.style.display = 'flex';
                }
            }

            // Duration calculation for the Basic Info section
            function calculateTotalDuration() {
                // Get time values
                const startTime = document.getElementById('startTime').value;
                const endTime = document.getElementById('endTime').value;

                // Get date values
                const startMonth = document.getElementById('startMonth').value;
                const startDay = document.getElementById('startDay').value;
                const startYear = document.getElementById('startYear').value;

                const endMonth = document.getElementById('endMonth').value;
                const endDay = document.getElementById('endDay').value;
                const endYear = document.getElementById('endYear').value;

                const lunchBreak = document.getElementById('lunchBreak').checked;
                const totalDurationField = document.getElementById('totalDuration');

                // If all time and date fields are filled
                if (startTime && endTime && startMonth && startDay && startYear && endMonth && endDay && endYear) {
                    try {
                        // Create date objects for comparison (for date difference)
                        const startDate = new Date(startYear, startMonth - 1, startDay);
                        const endDate = new Date(endYear, endMonth - 1, endDay);

                        // Check if end date is before start date
                        if (endDate < startDate) {
                            totalDurationField.value = "Error: End date before start date";
                            // Add error class to date fields
                            markDateFieldsAsInvalid(true);
                            return;
                        } else {
                            // Remove error class from date fields
                            markDateFieldsAsInvalid(false);
                        }

                        // Calculate days between dates (inclusive)
                        const dayCount = Math.floor((endDate - startDate) / (24 * 60 * 60 * 1000)) + 1;

                        // Get time components in decimal hours (e.g., 9:30 = 9.5)
                        const [startHours, startMinutes] = startTime.split(':').map(Number);
                        const [endHours, endMinutes] = endTime.split(':').map(Number);

                        const startDecimalTime = startHours + (startMinutes / 60);
                        const endDecimalTime = endHours + (endMinutes / 60);

                        // Calculate daily hours (end time - start time)
                        let dailyHours = endDecimalTime - startDecimalTime;

                        // Handle case where end time is earlier than start time
                        if (dailyHours < 0) {
                            // If on the same day, this is an error
                            if (startDate.getTime() === endDate.getTime()) {
                                totalDurationField.value = "Error: End time before start time";
                                // Add error class to time fields
                                markTimeFieldsAsInvalid(true);
                                return;
                            } else {
                                // If multi-day, assume 24-hour clock and add 24 hours
                                dailyHours += 24;
                                // Remove error class from time fields
                                markTimeFieldsAsInvalid(false);
                            }
                        } else {
                            // Remove error class from time fields
                            markTimeFieldsAsInvalid(false);
                        }

                        // Deduct lunch break if applicable
                        if (lunchBreak && dailyHours >= 5) {
                            dailyHours -= 1;
                        }

                        // Check if dailyHours became negative after lunch break deduction
                        if (dailyHours < 0) {
                            totalDurationField.value = "Error: Negative hours after lunch break";
                            return;
                        }

                        // Calculate total hours using the formula: daily hours × number of days
                        let totalHours = dailyHours * dayCount;

                        // Final check for negative results
                        if (totalHours < 0) {
                            totalDurationField.value = "Error: Calculation resulted in negative hours";
                            return;
                        }

                        // Format and display the result
                        totalDurationField.value = totalHours.toFixed(2);
                        // Call PS Attribution calculation after duration is set
                        calculatePSAttribution();
                    } catch (error) {
                        console.error("Error calculating duration:", error);
                        totalDurationField.value = "Error: Invalid calculation";
                    }
                } else {
                    totalDurationField.value = '';
                }
            }

            // Function to mark date fields as invalid
            function markDateFieldsAsInvalid(isInvalid) {
                const dateFields = [
                    document.getElementById('startMonth'),
                    document.getElementById('startDay'),
                    document.getElementById('startYear'),
                    document.getElementById('endMonth'),
                    document.getElementById('endDay'),
                    document.getElementById('endYear')
                ];

                dateFields.forEach(field => {
                    if (isInvalid) {
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                        // Also remove any error messages
                        if (field.nextElementSibling && field.nextElementSibling.classList.contains('invalid-feedback')) {
                            field.nextElementSibling.remove();
                        }
                    }
                });
            }

            // Function to mark time fields as invalid
            function markTimeFieldsAsInvalid(isInvalid) {
                const timeFields = [
                    document.getElementById('startTime'),
                    document.getElementById('endTime')
                ];

                timeFields.forEach(field => {
                    if (isInvalid) {
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                        // Also remove any error messages
                        if (field.nextElementSibling && field.nextElementSibling.classList.contains('invalid-feedback')) {
                            field.nextElementSibling.remove();
                        }
                    }
                });
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
                        }, 10); // Changed from 50 to 10 - make it super fast
                    }
                });
            }

            // Function to load years from gpb_entries
            function loadYears() {
                const yearSelect = document.getElementById('year');
                if (!yearSelect) return;

                // Add a loading option
                yearSelect.innerHTML = '<option value="" selected disabled>Loading years...</option>';

                // AJAX request to get years
                fetch('get_years.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.years && data.years.length > 0) {
                            // Clear loading option
                            yearSelect.innerHTML = '<option value="" selected disabled>Select Year</option>';

                            // Add years from response
                            data.years.forEach(year => {
                                const option = document.createElement('option');
                                option.value = year;
                                option.textContent = year;
                                yearSelect.appendChild(option);
                            });
                        } else {
                            // Handle error or empty results
                            yearSelect.innerHTML = '<option value="" selected disabled>No years available</option>';
                            console.error('Error loading years:', data.message || 'No years returned');
                        }
                    })
                    .catch(error => {
                        yearSelect.innerHTML = '<option value="" selected disabled>Error loading years</option>';
                        console.error('Error:', error);
                    });
            }

            // Function to load gender issues based on selected year and campus
            function loadGenderIssues() {
                const yearSelect = document.getElementById('year');
                const quarterSelect = document.getElementById('quarter');
                const genderIssueSelect = document.getElementById('genderIssue');

                if (!yearSelect || !genderIssueSelect || !quarterSelect) return;

                const selectedYear = yearSelect.value;
                const selectedQuarter = quarterSelect.value;

                // Check if both year and quarter are selected
                if (!selectedYear || !selectedQuarter) {
                    // If either year or quarter is not selected, disable gender issue dropdown
                    genderIssueSelect.innerHTML = '<option value="" selected disabled>Select Gender Issue</option>';
                    genderIssueSelect.disabled = true;
                    return;
                }

                // Both year and quarter are selected, now enable and populate gender issues
                genderIssueSelect.disabled = false;

                // Add a loading option
                genderIssueSelect.innerHTML = '<option value="" selected disabled>Loading gender issues...</option>';

                // AJAX request to get gender issues
                fetch(`get_gender_issues.php?year=${selectedYear}&quarter=${selectedQuarter}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.issues && data.issues.length > 0) {
                            // Clear loading option
                            genderIssueSelect.innerHTML = '<option value="" selected disabled>Select Gender Issue</option>';

                            // Add gender issues from response
                            data.issues.forEach(issue => {
                                const option = document.createElement('option');
                                option.value = issue.id; // Assuming each issue has an ID
                                option.textContent = issue.gender_issue; // Assuming each issue has a gender_issue field

                                // Apply styling based on status
                                if (issue.status && issue.status !== 'Approved') {
                                    // For non-approved issues, apply special styling
                                    option.style.color = 'red';
                                    option.style.fontStyle = 'italic';
                                    option.disabled = true;

                                    // Add status suffix to the text
                                    option.textContent += ` (${issue.status})`;
                                }

                                genderIssueSelect.appendChild(option);
                            });

                            // Enable the select
                            genderIssueSelect.disabled = false;
                        } else {
                            // Handle error or empty results
                            genderIssueSelect.innerHTML = '<option value="" selected disabled>No gender issues available</option>';
                            console.error('Error loading gender issues:', data.message || 'No gender issues returned');
                        }
                    })
                    .catch(error => {
                        genderIssueSelect.innerHTML = '<option value="" selected disabled>Error loading gender issues</option>';
                        console.error('Error:', error);
                    });
            }

            // Add validation for date dropdown fields
            function validateDateDropdowns() {
                // Start date validation
                const startMonth = document.getElementById('startMonth');
                const startDay = document.getElementById('startDay');
                const startYear = document.getElementById('startYear');

                // End date validation
                const endMonth = document.getElementById('endMonth');
                const endDay = document.getElementById('endDay');
                const endYear = document.getElementById('endYear');

                // Add change listeners to validate days based on month selection
                [startMonth, startYear].forEach(field => {
                    field.addEventListener('change', function() {
                        updateDaysInMonth(startMonth, startDay, startYear);
                    });
                });

                [endMonth, endYear].forEach(field => {
                    field.addEventListener('change', function() {
                        updateDaysInMonth(endMonth, endDay, endYear);
                    });
                });
            }

            // Function to update days in month based on month and year selection
            function updateDaysInMonth(monthSelect, daySelect, yearSelect) {
                if (!monthSelect.value || !yearSelect.value) return;

                const month = parseInt(monthSelect.value);
                const year = parseInt(yearSelect.value);
                const daysInMonth = new Date(year, month, 0).getDate();

                // Store the currently selected day
                const selectedDay = daySelect.value;

                // Clear day options
                daySelect.innerHTML = '<option value="" disabled>Day</option>';

                // Add days based on month and year
                for (let i = 1; i <= daysInMonth; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = i;
                    // Restore previously selected day if valid
                    if (selectedDay && parseInt(selectedDay) === i) {
                        option.selected = true;
                    }
                    daySelect.appendChild(option);
                }

                // If no day is selected, select the first day
                if (!daySelect.value) {
                    daySelect.options[1].selected = true;
                }
            }

            // Initialize date validations
            validateDateDropdowns();

            // Function to initialize SDGs section checkbox behavior
            function initSdgsCheckboxes() {
                const sdgsSection = document.getElementById('sdgs-section');
                if (!sdgsSection) return;

                // First, check if any checkboxes are already selected and update status
                const checkboxes = sdgsSection.querySelectorAll('input[name="sdgs[]"]');
                const isAnySelected = Array.from(checkboxes).some(cb => cb.checked);

                // Update section status
                const navItem = document.querySelector('.form-nav-item[data-section="sdgs-section"]');
                if (isAnySelected) {
                    navItem.classList.add('is-complete');
                } else {
                    navItem.classList.remove('is-complete');
                }

                // Add event listeners to checkboxes
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        // Check if any checkbox is selected
                        const isAnySelected = Array.from(checkboxes).some(cb => cb.checked);

                        // Update section status
                        if (isAnySelected) {
                            navItem.classList.add('is-complete');
                        } else {
                            navItem.classList.remove('is-complete');
                        }
                    });
                });
            }

            // Initialize SDGs checkboxes
            initSdgsCheckboxes();

            // Call initSdgsCheckboxes when the DOM is fully loaded
            setTimeout(initSdgsCheckboxes, 500);

            // Function to check all sections for completion
            document.addEventListener('DOMContentLoaded', function() {
                // Make sure SDGs section is not initially marked as complete
                const sdgsNavItem = document.querySelector('.form-nav-item[data-section="sdgs-section"]');
                if (sdgsNavItem) {
                    sdgsNavItem.classList.remove('is-complete');
                }

                // Other code...
            });

            // Function to add real-time validation to Agenda and SDGs sections
            function initRealTimeValidation() {
                // Add event listeners to agenda radio buttons
                const agendaSection = document.getElementById('agenda-section');
                if (agendaSection) {
                    const radioButtons = agendaSection.querySelectorAll('input[name="agenda_type"]');
                    radioButtons.forEach(radio => {
                        radio.addEventListener('change', function() {
                            // If validation was previously triggered, update validation status
                            if (validationTriggered) {
                                validateSection('agenda-section');

                                // Remove error styling from section title and nav item
                                const sectionTitle = document.querySelector('#agenda-section .section-title');
                                if (sectionTitle) {
                                    sectionTitle.classList.remove('has-error');
                                }

                                const navItem = document.querySelector('.form-nav-item[data-section="agenda-section"]');
                                if (navItem) {
                                    navItem.classList.remove('has-error');
                                    navItem.classList.add('is-complete');
                                }

                                // Remove error styling from container
                                const container = agendaSection.querySelector('.modern-options-container');
                                if (container) {
                                    container.classList.remove('is-invalid');

                                    // Remove feedback message if present
                                    if (container.nextElementSibling && container.nextElementSibling.classList.contains('invalid-feedback')) {
                                        container.nextElementSibling.remove();
                                    }
                                }
                            }
                        });
                    });
                }

                // Add event listeners to SDGs checkboxes
                const sdgsSection = document.getElementById('sdgs-section');
                if (sdgsSection) {
                    const checkboxes = sdgsSection.querySelectorAll('input[name="sdgs[]"]');
                    checkboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            // Check if any checkbox is selected
                            const isAnySelected = Array.from(checkboxes).some(cb => cb.checked);

                            // If validation was previously triggered, update validation status
                            if (validationTriggered) {
                                validateSection('sdgs-section');

                                // If any SDG is selected, remove error styling
                                if (isAnySelected) {
                                    // Remove error styling from section title and nav item
                                    const sectionTitle = document.querySelector('#sdgs-section .section-title');
                                    if (sectionTitle) {
                                        sectionTitle.classList.remove('has-error');
                                    }

                                    const navItem = document.querySelector('.form-nav-item[data-section="sdgs-section"]');
                                    if (navItem) {
                                        navItem.classList.remove('has-error');
                                        navItem.classList.add('is-complete');
                                    }

                                    // Remove error styling from container
                                    const container = sdgsSection.querySelector('.modern-options-container');
                                    if (container) {
                                        container.classList.remove('is-invalid');

                                        // Remove feedback message if present
                                        if (container.nextElementSibling && container.nextElementSibling.classList.contains('invalid-feedback')) {
                                            container.nextElementSibling.remove();
                                        }
                                    }
                                }
                            }
                        });
                    });
                }
            }

            // Initialize real-time validation
            initRealTimeValidation();

            // Call initRealTimeValidation again after a delay to ensure all elements are loaded
            setTimeout(initRealTimeValidation, 500);

            // Make sure SDGs section is not initially marked as complete
            const sdgsNavItem = document.querySelector('.form-nav-item[data-section="sdgs-section"]');
            if (sdgsNavItem) {
                sdgsNavItem.classList.remove('is-complete');
            }

            // Function to handle add/remove functionality for Office and Programs section
            function initOfficeAndProgramsSection() {
                // Prevent multiple initializations
                if (window.officeAndProgramsInitialized) return;
                window.officeAndProgramsInitialized = true;

                // Office section
                const addOfficeBtn = document.getElementById('addOfficeBtn');
                const officeInputsContainer = document.getElementById('officeInputsContainer');

                if (addOfficeBtn && officeInputsContainer) {
                    // Add input event listener to the first input field
                    const firstOfficeInput = officeInputsContainer.querySelector('input[name="offices[]"]');
                    if (firstOfficeInput) {
                        firstOfficeInput.addEventListener('input', function() {
                            if (validationTriggered) {
                                validateSection('office-programs');
                            } else {
                                updateOfficeProgramsCompletionStatus();
                            }
                        });
                    }

                    addOfficeBtn.addEventListener('click', function() {
                        // Get current count of office inputs to determine the new number
                        const currentCount = officeInputsContainer.querySelectorAll('.input-group').length;
                        const newNumber = currentCount + 1;

                        // Clone the first input group
                        const firstInput = officeInputsContainer.querySelector('.input-group');
                        const newInput = firstInput.cloneNode(true);

                        // Clear the value in the cloned input
                        newInput.querySelector('input').value = '';

                        // Update the number indicator
                        newInput.querySelector('.input-number-indicator').textContent = `#${newNumber}`;

                        // Show the remove button
                        const removeBtn = newInput.querySelector('.remove-input');
                        removeBtn.style.display = 'block';

                        // Add event listener to remove button
                        removeBtn.addEventListener('click', function() {
                            officeInputsContainer.removeChild(newInput);

                            // Update numbering of remaining inputs
                            updateNumbering(officeInputsContainer);

                            // Update completion status and validation
                            if (validationTriggered) {
                                validateSection('office-programs');
                            } else {
                                updateOfficeProgramsCompletionStatus();
                            }
                        });

                        // Add input event listener to the new input field
                        const newInputField = newInput.querySelector('input[name="offices[]"]');
                        if (newInputField) {
                            newInputField.addEventListener('input', function() {
                                if (validationTriggered) {
                                    validateSection('office-programs');
                                } else {
                                    updateOfficeProgramsCompletionStatus();
                                }
                            });
                        }

                        // Append to the container
                        officeInputsContainer.appendChild(newInput);

                        // Mark the section as incomplete immediately since we've added an empty field
                        const navItem = document.querySelector('.form-nav-item[data-section="office-programs"]');
                        if (navItem) {
                            navItem.classList.remove('is-complete');
                        }

                        // Re-validate if validation was already triggered
                        if (validationTriggered) {
                            validateSection('office-programs');
                        } else {
                            updateOfficeProgramsCompletionStatus();
                        }
                    });
                }

                // Program section
                const addProgramBtn = document.getElementById('addProgramBtn');
                const programInputsContainer = document.getElementById('programInputsContainer');

                if (addProgramBtn && programInputsContainer) {
                    // Add input event listener to the first input field
                    const firstProgramInput = programInputsContainer.querySelector('input[name="programs[]"]');
                    if (firstProgramInput) {
                        firstProgramInput.addEventListener('input', function() {
                            if (validationTriggered) {
                                validateSection('office-programs');
                            } else {
                                updateOfficeProgramsCompletionStatus();
                            }
                        });
                    }

                    addProgramBtn.addEventListener('click', function() {
                        // Get current count of program inputs to determine the new number
                        const currentCount = programInputsContainer.querySelectorAll('.input-group').length;
                        const newNumber = currentCount + 1;

                        // Clone the first input group
                        const firstInput = programInputsContainer.querySelector('.input-group');
                        const newInput = firstInput.cloneNode(true);

                        // Clear the value in the cloned input
                        newInput.querySelector('input').value = '';

                        // Update the number indicator
                        newInput.querySelector('.input-number-indicator').textContent = `#${newNumber}`;

                        // Show the remove button
                        const removeBtn = newInput.querySelector('.remove-input');
                        removeBtn.style.display = 'block';

                        // Add event listener to remove button
                        removeBtn.addEventListener('click', function() {
                            programInputsContainer.removeChild(newInput);

                            // Update numbering of remaining inputs
                            updateNumbering(programInputsContainer);

                            // Update completion status and validation
                            if (validationTriggered) {
                                validateSection('office-programs');
                            } else {
                                updateOfficeProgramsCompletionStatus();
                            }
                        });

                        // Add input event listener to the new input field
                        const newInputField = newInput.querySelector('input[name="programs[]"]');
                        if (newInputField) {
                            newInputField.addEventListener('input', function() {
                                if (validationTriggered) {
                                    validateSection('office-programs');
                                } else {
                                    updateOfficeProgramsCompletionStatus();
                                }
                            });
                        }

                        // Append to the container
                        programInputsContainer.appendChild(newInput);

                        // Mark the section as incomplete immediately since we've added an empty field
                        const navItem = document.querySelector('.form-nav-item[data-section="office-programs"]');
                        if (navItem) {
                            navItem.classList.remove('is-complete');
                        }

                        // Re-validate if validation was already triggered
                        if (validationTriggered) {
                            validateSection('office-programs');
                        } else {
                            updateOfficeProgramsCompletionStatus();
                        }
                    });
                }

                // Helper function to update numbering after removing an input
                function updateNumbering(container) {
                    const inputs = container.querySelectorAll('.input-group');
                    inputs.forEach((input, index) => {
                        const numberIndicator = input.querySelector('.input-number-indicator');
                        if (numberIndicator) {
                            numberIndicator.textContent = `#${index + 1}`;
                        }
                    });
                }
            }

            // Initialize Office and Programs section
            document.addEventListener('DOMContentLoaded', function() {
                initOfficeAndProgramsSection();

                // Check completion status for the Office and Programs section
                setTimeout(updateOfficeProgramsCompletionStatus, 500);

                // Add event delegation for input changes in office and program containers
                const officeInputsContainer = document.getElementById('officeInputsContainer');
                const programInputsContainer = document.getElementById('programInputsContainer');

                if (officeInputsContainer) {
                    officeInputsContainer.addEventListener('input', function(event) {
                        if (event.target && event.target.matches('input[name="offices[]"]')) {
                            updateOfficeProgramsCompletionStatus();
                        }
                    });
                }

                if (programInputsContainer) {
                    programInputsContainer.addEventListener('input', function(event) {
                        if (event.target && event.target.matches('input[name="programs[]"]')) {
                            updateOfficeProgramsCompletionStatus();
                        }
                    });
                }
            });

            // Function to specifically check and update the completion status for the Office and Programs section
            function updateOfficeProgramsCompletionStatus() {
                const sectionId = 'office-programs';
                const section = document.getElementById(sectionId);
                const navItem = document.querySelector(`.form-nav-item[data-section="${sectionId}"]`);

                if (!section || !navItem) return;

                // Check all office inputs
                const officeInputs = section.querySelectorAll('#officeInputsContainer input[name="offices[]"]');

                // Check if there are any empty office inputs
                let hasEmptyOffice = false;
                officeInputs.forEach(input => {
                    if (!input.value.trim()) {
                        hasEmptyOffice = true;
                    }
                });

                // Check all program inputs
                const programInputs = section.querySelectorAll('#programInputsContainer input[name="programs[]"]');

                // Check if there are any empty program inputs
                let hasEmptyProgram = false;
                programInputs.forEach(input => {
                    if (!input.value.trim()) {
                        hasEmptyProgram = true;
                    }
                });

                // Only mark as complete if ALL fields have values
                const allFieldsFilled = !hasEmptyOffice && !hasEmptyProgram;

                console.log(`Checking office-programs completion: emptyOffices=${hasEmptyOffice}, emptyPrograms=${hasEmptyProgram}, allFilled=${allFieldsFilled}`);
                console.log(`Total fields: ${officeInputs.length} offices, ${programInputs.length} programs`);

                // Explicitly remove the 'is-complete' class first to ensure it's gone
                navItem.classList.remove('is-complete');

                // Then add it back only if all fields are filled
                if (allFieldsFilled) {
                    navItem.classList.add('is-complete');

                    // Also remove any error state if present
                    navItem.classList.remove('has-error');
                    const sectionTitle = section.querySelector('.section-title');
                    if (sectionTitle) {
                        sectionTitle.classList.remove('has-error');
                    }
                }

                return allFieldsFilled;
            }

            // Function to initialize Project Team section
            function initProjectTeamSection() {
                // Prevent multiple initializations
                if (window.projectTeamInitialized) return;
                window.projectTeamInitialized = true;

                // Initialize team member addition buttons
                initTeamMemberAddition();

                // Initialize task addition buttons
                initTaskAddition();

                // Initialize personnel autocomplete fields
                initPersonnelAutocomplete();
            }

            // Function to handle adding new team members
            function initTeamMemberAddition() {
                // Project Leaders
                const addProjectLeaderBtn = document.getElementById('addProjectLeaderBtn');
                if (addProjectLeaderBtn) {
                    addProjectLeaderBtn.addEventListener('click', function() {
                        addNewTeamMember('leader', 'projectLeadersContainer', 'Project Leader');
                    });
                }

                // Assistant Project Leaders
                const addAssistantLeaderBtn = document.getElementById('addAssistantLeaderBtn');
                if (addAssistantLeaderBtn) {
                    addAssistantLeaderBtn.addEventListener('click', function() {
                        addNewTeamMember('asst_leader', 'assistantLeadersContainer', 'Assistant Project Leader');
                    });
                }

                // Project Staff
                const addProjectStaffBtn = document.getElementById('addProjectStaffBtn');
                if (addProjectStaffBtn) {
                    addProjectStaffBtn.addEventListener('click', function() {
                        addNewTeamMember('staff', 'projectStaffContainer', 'Project Staff/Coordinator');
                    });
                }

                // Add event delegation for remove team member buttons
                document.addEventListener('click', function(event) {
                    if (event.target.closest('.remove-team-member')) {
                        const removeBtn = event.target.closest('.remove-team-member');
                        const teamMemberCard = removeBtn.closest('.team-member-card');
                        const container = teamMemberCard.parentNode;

                        // Remove the team member card
                        container.removeChild(teamMemberCard);

                        // Update numbering for remaining team members in this container
                        updateTeamMemberNumbering(container);

                        // If validation has been triggered, validate the section
                        if (validationTriggered) {
                            validateSection('project-team');
                        }
                    }
                });

                // Initialize direct event listeners for existing task buttons
                initExistingTaskButtons();
            }

            // Function to add a new team member card
            function addNewTeamMember(role, containerId, title) {
                const container = document.getElementById(containerId);
                if (!container) return;

                // Get current count of team members to determine the new index
                const currentCount = container.querySelectorAll('.team-member-card').length;
                const newIndex = currentCount + 1;

                // Clone the first team member card
                const firstCard = container.querySelector('.team-member-card');
                const newCard = firstCard.cloneNode(true);

                // Update the title/header
                const cardHeader = newCard.querySelector('.card-header h6');
                if (cardHeader) {
                    cardHeader.textContent = `${title} #${newIndex}`;
                }

                // Update all ID attributes and name attributes with the new index
                updateElementAttributes(newCard, role, newIndex);

                // Clear all input values
                newCard.querySelectorAll('input:not([type="button"])').forEach(input => {
                    input.value = '';
                    if (input.hasAttribute('readonly')) {
                        input.setAttribute('readonly', 'readonly');
                    }
                });

                // Reset the tasks container to have only one task input
                const tasksContainer = newCard.querySelector('.tasks-container');
                if (tasksContainer) {
                    const firstTaskInput = tasksContainer.querySelector('.input-group');
                    // Clear existing tasks except for the first one
                    tasksContainer.innerHTML = '';
                    tasksContainer.appendChild(firstTaskInput);

                    // Clear the value of the first task input
                    const taskInput = firstTaskInput.querySelector('input');
                    if (taskInput) {
                        taskInput.value = '';
                    }

                    // Update the tasks container ID
                    tasksContainer.id = `${role}TasksContainer_${newIndex}`;

                    // Update the task input name attribute
                    const taskInputs = tasksContainer.querySelectorAll('input');
                    taskInputs.forEach(input => {
                        input.name = `${role}_tasks_${newIndex}[]`;
                    });
                }

                // Update the "Add Task" button data attributes
                const addTaskBtn = newCard.querySelector('.add-task-btn');
                if (addTaskBtn) {
                    addTaskBtn.setAttribute('data-role', role);
                    addTaskBtn.setAttribute('data-index', newIndex);
                }

                // Show the remove button for all cards except the first one
                const removeBtn = newCard.querySelector('.remove-team-member');
                if (removeBtn) {
                    removeBtn.style.display = 'block';
                }

                // Add the new card to the container
                container.appendChild(newCard);

                // Initialize personnel autocomplete for the new card
                initPersonnelAutocompleteForCard(newCard);

                // Initialize validation handlers for the new task fields
                initExistingTaskFields();

                // If validation has been triggered, validate the section
                if (validationTriggered) {
                    validateSection('project-team');
                }
            }

            // Function to update element attributes with the new index
            function updateElementAttributes(card, role, newIndex) {
                // Update input IDs and names
                card.querySelectorAll('input').forEach(input => {
                    const oldId = input.id;
                    if (oldId) {
                        // Extract the base ID without the index
                        const baseId = oldId.replace(/(_\d+)?$/, '');
                        input.id = `${baseId}_${newIndex}`;
                    }

                    const oldName = input.name;
                    if (oldName) {
                        // Handle array inputs
                        if (oldName.endsWith('[]')) {
                            // No change needed for array inputs
                        } else if (oldName.includes('_tasks_')) {
                            // Update task input names to include the new team member index
                            input.name = `${role}_tasks_${newIndex}[]`;
                        }
                    }
                });

                // Update label "for" attributes
                card.querySelectorAll('label').forEach(label => {
                    const forAttr = label.getAttribute('for');
                    if (forAttr) {
                        const baseFor = forAttr.replace(/(_\d+)?$/, '');
                        label.setAttribute('for', `${baseFor}_${newIndex}`);
                    }
                });
            }

            // Function to update team member numbering in headers
            function updateTeamMemberNumbering(container) {
                const cards = container.querySelectorAll('.team-member-card');
                cards.forEach((card, index) => {
                    const number = index + 1;

                    // Update the header
                    const header = card.querySelector('.card-header h6');
                    if (header) {
                        // Extract the title without the number
                        const titleBase = header.textContent.replace(/ #\d+$/, '');
                        header.textContent = `${titleBase} #${number}`;
                    }

                    // Update data-index attributes for task buttons
                    const addTaskBtn = card.querySelector('.add-task-btn');
                    if (addTaskBtn) {
                        addTaskBtn.setAttribute('data-index', number);
                    }

                    // Update input IDs and names based on role
                    const role = getCardRole(card);
                    if (role) {
                        updateElementAttributes(card, role, number);

                        // Update tasks container ID
                        const tasksContainer = card.querySelector('.tasks-container');
                        if (tasksContainer) {
                            tasksContainer.id = `${role}TasksContainer_${number}`;
                        }
                    }

                    // Show/hide remove button
                    const removeBtn = card.querySelector('.remove-team-member');
                    if (removeBtn) {
                        removeBtn.style.display = index === 0 ? 'none' : 'block';
                    }
                });
            }

            // Helper function to determine the role of a team member card
            function getCardRole(card) {
                const header = card.querySelector('.card-header h6');
                if (!header) return null;

                const title = header.textContent.toLowerCase();
                if (title.includes('project leader') && !title.includes('assistant')) {
                    return 'leader';
                } else if (title.includes('assistant project leader')) {
                    return 'asst_leader';
                } else if (title.includes('project staff') || title.includes('coordinator')) {
                    return 'staff';
                }
                return null;
            }

            // Function to initialize task addition functionality
            function initTaskAddition() {
                // Use event delegation for task addition buttons
                document.addEventListener('click', function(event) {
                    const addTaskBtn = event.target.closest('.add-task-btn');
                    if (addTaskBtn) {
                        const role = addTaskBtn.getAttribute('data-role');
                        const index = addTaskBtn.getAttribute('data-index');

                        // Debug information
                        console.log('Task button clicked:', {
                            role: role,
                            index: index,
                            buttonElement: addTaskBtn,
                            targetContainerId: `${role}TasksContainer_${index}`
                        });

                        const tasksContainer = document.getElementById(`${role}TasksContainer_${index}`);

                        console.log('Found container:', tasksContainer);

                        if (tasksContainer) {
                            addNewTaskInput(tasksContainer, `${role}_tasks_${index}[]`);
                        } else {
                            console.error(`Container ${role}TasksContainer_${index} not found!`);
                        }
                    }
                });

                // Use event delegation for remove input buttons
                document.addEventListener('click', function(event) {
                    const removeBtn = event.target.closest('.remove-input');
                    if (removeBtn) {
                        const inputGroup = removeBtn.closest('.input-group');
                        const container = inputGroup.closest('.tasks-container');

                        // Remove the input group
                        container.removeChild(inputGroup);

                        // Update numbering of remaining tasks
                        updateTaskNumbering(container);

                        // If validation has been triggered, validate the section
                        if (validationTriggered) {
                            validateSection('project-team');
                        }
                    }
                });
            }

            // Helper function for adding new task inputs
            function addNewTaskInput(container, fieldName) {
                // Get current count of task inputs to determine the new number
                const currentCount = container.querySelectorAll('.input-group').length;
                const newNumber = currentCount + 1;

                // Clone the first input group
                const firstInput = container.querySelector('.input-group');
                const newInput = firstInput.cloneNode(true);

                // Clear the value in the cloned input
                const inputField = newInput.querySelector('input');
                inputField.value = '';

                // Update the number indicator
                newInput.querySelector('.input-number-indicator').textContent = `#${newNumber}`;

                // Show the remove button
                const removeBtn = newInput.querySelector('.remove-input');
                removeBtn.style.display = 'block';

                // Add input event listener for real-time validation
                inputField.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid')) {
                        // Remove invalid class when user types
                        this.classList.remove('is-invalid');

                        // Remove feedback message if present
                        const inputGroup = this.closest('.input-group');
                        if (inputGroup && inputGroup.nextElementSibling && inputGroup.nextElementSibling.classList.contains('invalid-feedback')) {
                            inputGroup.nextElementSibling.remove();
                        }
                    }

                    // Re-validate the section if validation was triggered
                    if (validationTriggered) {
                        const sectionId = this.closest('.form-section').id;
                        validateSection(sectionId);
                    }
                });

                // Append to the container
                container.appendChild(newInput);

                // If validation has been triggered, validate the section
                if (validationTriggered) {
                    validateSection('project-team');
                }
            }

            // Helper function to update task numbering
            function updateTaskNumbering(container) {
                const inputs = container.querySelectorAll('.input-group');
                inputs.forEach((input, index) => {
                    const numberIndicator = input.querySelector('.input-number-indicator');
                    if (numberIndicator) {
                        numberIndicator.textContent = `#${index + 1}`;
                    }

                    // Show/hide remove button based on if it's the only task
                    const removeBtn = input.querySelector('.remove-input');
                    if (removeBtn) {
                        removeBtn.style.display = inputs.length === 1 ? 'none' : 'block';
                    }
                });
            }

            // Function to initialize personnel autocomplete for a specific card
            function initPersonnelAutocompleteForCard(card) {
                const personnelInput = card.querySelector('.personnel-autocomplete');
                if (!personnelInput) return;

                // Initialize jQuery UI autocomplete
                $(personnelInput).autocomplete({
                    source: function(request, response) {
                        console.log("Autocomplete search term:", request.term);

                        $.ajax({
                            url: "get_personnel.php",
                            dataType: "json",
                            data: {
                                term: request.term
                            },
                            success: function(data) {
                                console.log("Personnel data received:", data);

                                if (data.error) {
                                    console.error('Error fetching personnel:', data.error);
                                    return;
                                }

                                // Transform data for autocomplete display
                                const transformedData = $.map(data, function(item) {
                                    return {
                                        label: item.name,
                                        value: item.name,
                                        personnel: item // Store the full personnel data
                                    };
                                });

                                response(transformedData);

                                // Auto-fill on exact match when typing (without selecting from dropdown)
                                const inputValue = request.term.toLowerCase();
                                const role = getCardRole(card);
                                const idMatch = personnelInput.id.match(/_(\d+)$/);
                                const index = idMatch ? idMatch[1] : '1';

                                // Check for exact match
                                const exactMatch = data.find(item => item.name.toLowerCase() === inputValue);
                                if (exactMatch && role) {
                                    // Check for duplicate personnel before filling data
                                    if (!isDuplicatePersonnel(exactMatch.id, personnelInput)) {
                                        // Fill details automatically
                                        fillPersonnelData(role, exactMatch, index);

                                        // Store the personnel ID in a data attribute for duplicate checking
                                        personnelInput.setAttribute('data-personnel-id', exactMatch.id);

                                        // Explicitly remove any validation errors immediately
                                        if (personnelInput.classList.contains('is-invalid')) {
                                            markAsValid(personnelInput);

                                            // Re-validate the section if validation was triggered
                                            if (validationTriggered) {
                                                validateSection('project-team');
                                            }
                                        }
                                    } else {
                                        // Alert the user about duplicate
                                        showDuplicateWarning(personnelInput, exactMatch.name);

                                        // Clear fields
                                        clearPersonnelData(role, index);
                                        personnelInput.value = '';
                                    }
                                } else if (role && inputValue.length > 0) {
                                    // Clear fields if no match but there is input (user has modified the input)
                                    clearPersonnelData(role, index);
                                    personnelInput.removeAttribute('data-personnel-id');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("AJAX Error:", status, error);

                                // Debug: Try to see what was actually returned
                                console.log("Raw response:", xhr.responseText);
                                console.log("Status code:", xhr.status);
                                console.log("Status text:", xhr.statusText);
                            }
                        });
                    },
                    minLength: 2,
                    select: function(event, ui) {
                        // When an item is selected from the dropdown
                        if (ui.item) {
                            // Get the card and role info
                            const role = getCardRole(card);
                            if (role) {
                                // Get the index from the input ID
                                const idMatch = this.id.match(/_(\d+)$/);
                                const index = idMatch ? idMatch[1] : '1';

                                // Check for duplicate personnel before filling data
                                if (!isDuplicatePersonnel(ui.item.personnel.id, personnelInput)) {
                                    // Fill the related fields with actual data
                                    fillPersonnelData(role, ui.item.personnel, index);

                                    // Store the personnel ID in a data attribute for duplicate checking
                                    personnelInput.setAttribute('data-personnel-id', ui.item.personnel.id);

                                    // Explicitly remove any validation errors immediately
                                    if (personnelInput.classList.contains('is-invalid')) {
                                        markAsValid(personnelInput);

                                        // Re-validate the section if validation was triggered
                                        if (validationTriggered) {
                                            validateSection('project-team');
                                        }
                                    }
                                } else {
                                    // Prevent selection
                                    event.preventDefault();

                                    // Alert the user about duplicate
                                    showDuplicateWarning(personnelInput, ui.item.personnel.name);

                                    // Clear fields
                                    clearPersonnelData(role, index);
                                    personnelInput.value = '';
                                }
                            }
                        }
                    },
                    change: function(event, ui) {
                        // This fires when the input value changes (whether by selection or typing)
                        // If there's no selection and the input doesn't match any items, clear the details
                        if (!ui.item) {
                            const role = getCardRole(card);
                            if (role) {
                                const idMatch = this.id.match(/_(\d+)$/);
                                const index = idMatch ? idMatch[1] : '1';

                                // If there's text but no match, clear fields
                                if (this.value.trim().length > 0) {
                                    // Let's double-check if there's a match for what's typed
                                    $.ajax({
                                        url: "get_personnel.php",
                                        dataType: "json",
                                        data: {
                                            term: this.value
                                        },
                                        success: function(data) {
                                            const trimmedInput = personnelInput.value.trim().toLowerCase();
                                            const exactMatch = data.find(item =>
                                                item.name.trim().toLowerCase() === trimmedInput);

                                            if (exactMatch) {
                                                // Check for duplicate personnel
                                                if (!isDuplicatePersonnel(exactMatch.id, personnelInput)) {
                                                    // Fill the related fields with actual data
                                                    fillPersonnelData(role, exactMatch, index);

                                                    // Store the personnel ID
                                                    personnelInput.setAttribute('data-personnel-id', exactMatch.id);

                                                    // Explicitly remove any validation errors immediately
                                                    if (personnelInput.classList.contains('is-invalid')) {
                                                        markAsValid(personnelInput);

                                                        // Re-validate the section if validation was triggered
                                                        if (validationTriggered) {
                                                            validateSection('project-team');
                                                        }
                                                    }
                                                } else {
                                                    // Alert the user about duplicate
                                                    showDuplicateWarning(personnelInput, exactMatch.name);

                                                    // Clear fields
                                                    clearPersonnelData(role, index);
                                                    personnelInput.value = '';
                                                    personnelInput.removeAttribute('data-personnel-id');

                                                    // If validation was triggered, show the validation error
                                                    if (validationTriggered) {
                                                        markAsInvalid(personnelInput, "Please select a valid personnel");
                                                        validateSection('project-team');
                                                    }
                                                }
                                            } else {
                                                // No match found, clear the fields
                                                clearPersonnelData(role, index);
                                                personnelInput.removeAttribute('data-personnel-id');

                                                // If validation was triggered, show the validation error
                                                if (validationTriggered) {
                                                    // Get the campus value
                                                    const campus = document.getElementById('campus').value;
                                                    markAsInvalid(personnelInput, `This personnel does not exist in ${campus}`);
                                                    validateSection('project-team');
                                                }
                                            }
                                        }
                                    });
                                } else {
                                    // Empty input, clear fields
                                    clearPersonnelData(role, index);
                                    personnelInput.removeAttribute('data-personnel-id');

                                    // If validation was triggered and field is required, show the validation error
                                    if (validationTriggered) {
                                        markAsInvalid(personnelInput, "This field is required");
                                        validateSection('project-team');
                                    }
                                }
                            }
                        }
                    }
                }).data("ui-autocomplete")._renderItem = function(ul, item) {
                    // Custom rendering for a more modern look
                    return $("<li>")
                        .append("<div class='autocomplete-item'>" +
                            "<div class='personnel-top-row'>" +
                            "<span class='personnel-name'>" + item.label + "</span>" +
                            "<span class='personnel-gender'>" + (item.personnel.gender || '') + "</span>" +
                            "</div>" +
                            "<div class='personnel-bottom-row'>" +
                            "<span class='personnel-rank'>" + (item.personnel.academic_rank || 'No rank') + "</span>" +
                            "</div>" +
                            "</div>")
                        .appendTo(ul);
                };

                // Add input event listener for immediate response
                personnelInput.addEventListener('input', function(e) {
                    // If input is cleared, clear the details
                    if (this.value.trim() === '') {
                        const role = getCardRole(card);
                        if (role) {
                            const idMatch = this.id.match(/_(\d+)$/);
                            const index = idMatch ? idMatch[1] : '1';
                            clearPersonnelData(role, index);
                            this.removeAttribute('data-personnel-id');
                        }
                    }
                });
            }

            // Function to initialize personnel autocomplete for all existing fields
            function initPersonnelAutocomplete() {
                // Add autocomplete CSS
                const style = document.createElement('style');
                style.textContent = `
                .ui-autocomplete {
                    max-height: 250px;
                    overflow-y: auto;
                    overflow-x: hidden;
                    border-radius: 0 0 8px 8px;
                    box-shadow: 0 6px 16px rgba(0,0,0,0.15);
                    border: 1px solid var(--border-color);
                    padding: 8px;
                    background: var(--input-bg);
                    z-index: 9999 !important;
                }
                .ui-autocomplete .ui-menu-item {
                    padding: 0;
                    margin-bottom: 4px;
                }
                .ui-autocomplete .ui-menu-item:last-child {
                    margin-bottom: 0;
                }
                .ui-autocomplete .ui-menu-item .ui-menu-item-wrapper {
                    padding: 0;
                    border: none !important;
                    outline: none !important;
                }
                .ui-autocomplete .ui-state-active .ui-menu-item-wrapper,
                .ui-autocomplete .ui-menu-item .ui-menu-item-wrapper:hover {
                    border: none !important;
                    margin: 0 !important;
                    outline: none !important;
                }
                .ui-autocomplete .autocomplete-item {
                    display: flex;
                    flex-direction: column;
                    padding: 10px 14px;
                    cursor: pointer;
                    border-radius: 6px;
                    transition: all 0.2s ease;
                    border-left: 3px solid transparent;
                }
                .ui-autocomplete .ui-state-active .autocomplete-item,
                .ui-autocomplete .autocomplete-item:hover {
                    background-color: var(--accent-color);
                    color: white;
                    border-left: 3px solid var(--accent-color);
                    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
                }
                .ui-autocomplete .personnel-top-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 4px;
                }
                .ui-autocomplete .personnel-bottom-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .ui-autocomplete .personnel-name {
                    font-weight: 600;
                    font-size: 0.95rem;
                    letter-spacing: 0.01rem;
                }
                .ui-autocomplete .personnel-gender {
                    font-size: 0.8rem;
                    padding: 2px 8px;
                    border-radius: 10px;
                    background-color: rgba(0,0,0,0.05);
                }
                .ui-autocomplete .ui-state-active .personnel-gender,
                .ui-autocomplete .autocomplete-item:hover .personnel-gender {
                    background-color: rgba(255,255,255,0.2);
                }
                .ui-autocomplete .personnel-rank {
                    font-size: 0.85rem;
                    opacity: 0.85;
                    letter-spacing: 0.01rem;
                }
                .ui-helper-hidden-accessible {
                    display: none;
                }
                [data-bs-theme="dark"] .ui-autocomplete {
                    background-color: var(--dark-input);
                    border-color: var(--dark-border);
                }
                [data-bs-theme="dark"] .ui-autocomplete .ui-menu-item {
                    color: var(--dark-text);
                }
                [data-bs-theme="dark"] .ui-autocomplete .ui-state-active .autocomplete-item,
                [data-bs-theme="dark"] .ui-autocomplete .autocomplete-item:hover {
                    background-color: var(--accent-color);
                    color: white;
                }
                [data-bs-theme="dark"] .ui-autocomplete .autocomplete-item {
                    border-left: 3px solid rgba(255,255,255,0.05);
                }
                [data-bs-theme="dark"] .ui-autocomplete .personnel-gender {
                    background-color: rgba(255,255,255,0.08);
                }
                /* Remove focus outlines and borders */
                .ui-front {
                    z-index: 9999 !important;
                }
                .ui-menu, .ui-widget, .ui-widget-content, .ui-corner-all {
                    border: 1px solid var(--border-color) !important;
                    outline: none !important;
                }
                .ui-state-focus, .ui-state-active {
                    border: none !important;
                    outline: none !important;
                    margin: 0 !important;
                }
            `;
                document.head.appendChild(style);

                // Find all existing personnel autocomplete fields
                document.querySelectorAll('.personnel-autocomplete').forEach(input => {
                    const card = input.closest('.team-member-card');
                    if (card) {
                        initPersonnelAutocompleteForCard(card);
                    }
                });
            }

            // Function to fill personnel data from database results
            function fillPersonnelData(personType, personnelData, index) {
                if (!personnelData) return;

                // Fill the fields with real data
                const genderField = document.getElementById(`${personType}_gender_${index}`);
                const rankField = document.getElementById(`${personType}_rank_${index}`);
                const salaryField = document.getElementById(`${personType}_salary_${index}`);
                const rateField = document.getElementById(`${personType}_rate_${index}`);

                if (genderField) genderField.value = personnelData.gender || '';
                if (rankField) rankField.value = personnelData.academic_rank || '';
                if (salaryField) salaryField.value = personnelData.monthly_salary || '';
                if (rateField) rateField.value = personnelData.hourly_rate || '';

                // Re-validate if validation was already triggered
                if (validationTriggered) {
                    validateSection('project-team');
                }

                calculatePSAttribution();
            }

            // Function to clear personnel data fields
            function clearPersonnelData(personType, index) {
                const genderField = document.getElementById(`${personType}_gender_${index}`);
                const rankField = document.getElementById(`${personType}_rank_${index}`);
                const salaryField = document.getElementById(`${personType}_salary_${index}`);
                const rateField = document.getElementById(`${personType}_rate_${index}`);

                if (genderField) genderField.value = '';
                if (rankField) rankField.value = '';
                if (salaryField) salaryField.value = '';
                if (rateField) rateField.value = '';

                // Re-validate if validation was already triggered
                if (validationTriggered) {
                    validateSection('project-team');
                }
            }

            // Initialize Project Team section
            document.addEventListener('DOMContentLoaded', function() {
                initProjectTeamSection();

                // Add validation input handlers to all existing task fields
                initExistingTaskFields();

                // Test connection to get_personnel.php
                testPersonnelEndpoint();
            });

            // Function to add input handlers to existing task fields
            function initExistingTaskFields() {
                // Get all task input fields from all team member cards
                const taskFields = document.querySelectorAll('.team-member-card .tasks-container input[type="text"]');

                // Add input event listeners to each field
                taskFields.forEach(inputField => {
                    inputField.addEventListener('input', function() {
                        if (this.classList.contains('is-invalid')) {
                            // Remove invalid class when user types
                            this.classList.remove('is-invalid');

                            // Remove feedback message if present
                            const inputGroup = this.closest('.input-group');
                            if (inputGroup && inputGroup.nextElementSibling && inputGroup.nextElementSibling.classList.contains('invalid-feedback')) {
                                inputGroup.nextElementSibling.remove();
                            }
                        }

                        // Re-validate the section if validation was triggered
                        if (validationTriggered) {
                            const sectionId = this.closest('.form-section').id;
                            validateSection(sectionId);
                        }
                    });
                });
            }

            // Function to test the connection to the personnel endpoint
            function testPersonnelEndpoint() {
                console.log("Testing connection to get_personnel.php...");

                $.ajax({
                    url: "get_personnel.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        term: "test"
                    },
                    success: function(data) {
                        console.log("Connection test successful!", data);
                    },
                    error: function(xhr, status, error) {
                        console.error("Connection test failed!");
                        console.error("Status:", status);
                        console.error("Error:", error);
                        console.log("Response text:", xhr.responseText);

                        // If there's HTML in the response, show it differently
                        if (xhr.responseText.includes("<")) {
                            console.log("HTML Response (first 200 chars):", xhr.responseText.substring(0, 200));
                        }
                    }
                });
            }

            // Function to initialize the first task buttons that exist when the page loads
            function initExistingTaskButtons() {
                // Make sure the first add task buttons work properly
                document.querySelectorAll('.add-task-btn').forEach(btn => {
                    // Ensure all buttons have the correct role and index attributes
                    if (!btn.hasAttribute('data-role') || !btn.hasAttribute('data-index')) {
                        const card = btn.closest('.team-member-card');
                        if (card) {
                            const role = getCardRole(card);
                            if (role) {
                                btn.setAttribute('data-role', role);

                                // Find the index from the header
                                const header = card.querySelector('.card-header h6');
                                if (header) {
                                    const match = header.textContent.match(/#(\d+)/);
                                    const index = match ? match[1] : '1';
                                    btn.setAttribute('data-index', index);
                                } else {
                                    btn.setAttribute('data-index', '1');
                                }
                            }
                        }
                    }

                    // Ensure the tasks container has the correct ID
                    const role = btn.getAttribute('data-role');
                    const index = btn.getAttribute('data-index');
                    if (role && index) {
                        const card = btn.closest('.team-member-card');
                        if (card) {
                            const tasksContainer = card.querySelector('.tasks-container');
                            if (tasksContainer) {
                                tasksContainer.id = `${role}TasksContainer_${index}`;
                            }
                        }
                    }
                });
            }

            // Function to check if a personnel is already added to any team role
            function isDuplicatePersonnel(personnelId, currentInput) {
                if (!personnelId) return false;

                // Get all personnel autocomplete inputs except the current one
                const inputs = document.querySelectorAll('.personnel-autocomplete');

                // Check if the personnel ID exists in any other input
                for (const input of inputs) {
                    // Skip the current input being checked
                    if (input === currentInput) continue;

                    // Check if this input has the same personnel ID
                    const existingId = input.getAttribute('data-personnel-id');
                    if (existingId && existingId === personnelId.toString()) {
                        return true;
                    }
                }

                return false;
            }

            // Function to show a warning about duplicate personnel
            function showDuplicateWarning(input, personnelName) {
                // Use SweetAlert2 for a nice notification
                Swal.fire({
                    icon: 'warning',
                    title: 'Duplicate Personnel',
                    text: `${personnelName} is already part of the project team. Each personnel can only be added once.`,
                    confirmButtonColor: '#6a1b9a',
                    backdrop: `
                    rgba(0,0,0,0.7)
                    url("")
                    center
                    no-repeat
                `,
                    customClass: {
                        container: 'swal-blur-container'
                    }
                });

                // Highlight the input to indicate error
                input.classList.add('is-invalid');

                // Remove the error class after a delay
                setTimeout(() => {
                    input.classList.remove('is-invalid');
                }, 3000);
            }

            // Function to fetch personnel data by name for edit mode
            function fetchPersonnelDataByName(name, personType, index) {
                if (!name) return;

                console.log(`Fetching data for ${personType} #${index} with name: ${name}`);

                // Make an AJAX request to get personnel data
                $.ajax({
                    url: "get_personnel.php",
                    dataType: "json",
                    data: {
                        term: name
                    },
                    success: function(data) {
                        console.log(`Personnel data received for ${name}:`, data);

                        if (data.length > 0) {
                            // Look for exact match
                            const exactMatch = data.find(item =>
                                item.name.toLowerCase() === name.toLowerCase());

                            if (exactMatch) {
                                console.log(`Found exact match for ${name}:`, exactMatch);
                                // Fill the personnel data
                                fillPersonnelData(personType, exactMatch, index);

                                // Store personnel ID for future reference
                                const inputField = document.querySelector(`#${personType}_name_${index}`);
                                if (inputField) {
                                    inputField.setAttribute('data-personnel-id', exactMatch.id);
                                }
                            } else {
                                console.log(`No exact match found for ${name}`);
                            }
                        } else {
                            console.log(`No personnel data found for ${name}`);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(`Error fetching personnel data for ${name}:`, error);
                    }
                });
            }

            // Function to check if a personnel name exists in the database
            function checkPersonnelExists(personnelName, callback) {
                if (!personnelName.trim()) {
                    callback(false, 'This field is required');
                    return;
                }

                // Get the campus value from the form
                const campus = document.getElementById('campus').value;

                // Send AJAX request to check if personnel exists
                $.ajax({
                    url: "check_personnel.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        personnel_name: personnelName,
                        campus: campus
                    },
                    success: function(response) {
                        if (response.exists) {
                            callback(true);
                        } else {
                            callback(false, `This personnel does not exist in ${campus}`);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error checking personnel:", error);
                        // Default to accepting the name if the server check fails
                        callback(true);
                    }
                });
            }

            // Initialize Program Description section's dynamic fields
            document.addEventListener('DOMContentLoaded', function() {
                initDynamicInputField('add_specific_objectives_btn', 'specific_objectives_container', 'specific_objectives[]', 'Enter specific objective');
                initDynamicInputField('add_strategies_btn', 'strategies_container', 'strategies[]', 'Enter strategy');
                initDynamicInputField('add_expected_output_btn', 'expected_output_container', 'expected_output[]', 'Enter expected output');
                initDynamicInputField('add_specific_plans_btn', 'specific_plans_container', 'specific_plans[]', 'Enter specific plan');

                // Check initial completion status of section 8
                setTimeout(() => {
                    updateSectionCompletionStatus('section-8');
                }, 500);
            });

            // Function to initialize dynamic input fields
            function initDynamicInputField(addBtnId, containerId, inputName, placeholder) {
                const addBtn = document.getElementById(addBtnId);
                const container = document.getElementById(containerId);

                if (!addBtn || !container) return;

                // Add event listener for the first input field
                const firstInput = container.querySelector(`input[name="${inputName}"]`);
                if (firstInput) {
                    firstInput.addEventListener('input', function() {
                        // If validation has been triggered, validate the section when input changes
                        if (validationTriggered) {
                            validateSection('section-8');
                        } else {
                            // Otherwise just update the completion status
                            updateSectionCompletionStatus('section-8');
                        }
                    });
                }

                // Add event listener to all textarea fields in section 8
                document.querySelectorAll('#section-8 textarea').forEach(textarea => {
                    textarea.addEventListener('input', function() {
                        // If validation has been triggered, validate the section when input changes
                        if (validationTriggered) {
                            validateSection('section-8');
                        } else {
                            // Otherwise just update the completion status
                            updateSectionCompletionStatus('section-8');
                        }
                    });
                });

                // Add button click event
                addBtn.addEventListener('click', function() {
                    // Get current count of inputs
                    const currentCount = container.querySelectorAll('.input-group').length;
                    const newNumber = currentCount + 1;

                    // Clone the first input group
                    const firstInputGroup = container.querySelector('.input-group');
                    const newInputGroup = firstInputGroup.cloneNode(true);

                    // Clear the value in the cloned input
                    const newInput = newInputGroup.querySelector('input');
                    newInput.value = '';
                    newInput.placeholder = placeholder;

                    // Update the number indicator
                    newInputGroup.querySelector('.input-number-indicator').textContent = `#${newNumber}`;

                    // Show the remove button
                    const removeBtn = newInputGroup.querySelector('.remove-input');
                    removeBtn.style.display = 'block';

                    // Add event listener to the new input
                    newInput.addEventListener('input', function() {
                        if (validationTriggered) {
                            validateSection('section-8');
                        } else {
                            // Otherwise just update the completion status
                            updateSectionCompletionStatus('section-8');
                        }
                    });

                    // Add event listener to remove button
                    removeBtn.addEventListener('click', function() {
                        container.removeChild(newInputGroup);

                        // Update numbering of remaining inputs
                        updateInputNumbering(container);

                        // Re-validate if validation has been triggered
                        if (validationTriggered) {
                            validateSection('section-8');
                        }

                        // Check completion status and update nav item
                        updateSectionCompletionStatus('section-8');
                    });

                    // Append to the container
                    container.appendChild(newInputGroup);

                    // Always mark the section as incomplete when a new empty field is added
                    // This ensures the completion status is immediately updated
                    const navItem = document.querySelector('.form-nav-item[data-section="section-8"]');
                    if (navItem) {
                        navItem.classList.remove('is-complete');
                    }

                    // If validation has been triggered, validate the section again
                    if (validationTriggered) {
                        validateSection('section-8');
                    }
                });
            }

            // Helper function to update input numbering after removal
            function updateInputNumbering(container) {
                const inputs = container.querySelectorAll('.input-group');
                inputs.forEach((input, index) => {
                    // Update number indicator
                    const numberIndicator = input.querySelector('.input-number-indicator');
                    if (numberIndicator) {
                        numberIndicator.textContent = `#${index + 1}`;
                    }

                    // Show/hide remove button for first input
                    const removeBtn = input.querySelector('.remove-input');
                    if (removeBtn) {
                        removeBtn.style.display = index === 0 && inputs.length === 1 ? 'none' : 'block';
                    }
                });
            }

            // Function to update section completion status specifically for dynamic input field sections
            function updateSectionCompletionStatus(sectionId) {
                const section = document.getElementById(sectionId);
                const navItem = document.querySelector(`.form-nav-item[data-section="${sectionId}"]`);

                if (!section || !navItem) return false;

                // Check all required fields in the section
                const allInputs = section.querySelectorAll('input[required], textarea[required], select[required]');
                let allFieldsFilled = true;

                allInputs.forEach(input => {
                    if (!input.value.trim()) {
                        allFieldsFilled = false;

                        // If validation has been triggered, mark this field as invalid
                        if (validationTriggered) {
                            markAsInvalid(input, 'This field is required');
                        }
                    } else {
                        markAsValid(input);
                    }
                });

                // Update navigation status based on validation result
                if (validationTriggered) {
                    if (allFieldsFilled) {
                        navItem.classList.remove('has-error');
                        navItem.classList.add('is-complete');

                        // Remove error from section title
                        const sectionTitle = section.querySelector('.section-title');
                        if (sectionTitle) {
                            sectionTitle.classList.remove('has-error');
                        }
                    } else {
                        navItem.classList.add('has-error');
                        navItem.classList.remove('is-complete');

                        // Add error to section title
                        const sectionTitle = section.querySelector('.section-title');
                        if (sectionTitle) {
                            sectionTitle.classList.add('has-error');
                        }
                    }
                } else if (allFieldsFilled) {
                    navItem.classList.add('is-complete');
                } else {
                    navItem.classList.remove('is-complete');
                }

                return allFieldsFilled;
            }

            // Function to validate a specific section
            function validateSection(sectionId) {
                const section = document.getElementById(sectionId);
                if (!section) return true; // Skip validation if section doesn't exist

                const navItem = document.querySelector(`.form-nav-item[data-section="${sectionId}"]`);
                if (!navItem) return true; // Skip if nav item doesn't exist

                // Special handling for agenda-section with radio buttons
                if (sectionId === 'agenda-section') {
                    const radioButtons = section.querySelectorAll('input[name="agenda_type"]');
                    const errorContainer = section.querySelector('.modern-options-container');

                    // Check if any radio button is selected
                    const isAnySelected = Array.from(radioButtons).some(radio => radio.checked);
                    if (!isAnySelected) {
                        // Show error
                        if (errorContainer) {
                            errorContainer.classList.add('is-invalid');

                            // Add feedback message if not already present
                            if (!errorContainer.nextElementSibling || !errorContainer.nextElementSibling.classList.contains('invalid-feedback')) {
                                const feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                feedback.textContent = 'Please select an agenda type';
                                errorContainer.after(feedback);
                            }
                        }

                        // Mark section as having error
                        navItem.classList.add('has-error');
                        navItem.classList.remove('is-complete');

                        // Add error to section title
                        const sectionTitle = section.querySelector('.section-title');
                        if (sectionTitle) {
                            sectionTitle.classList.add('has-error');
                        }

                        return false;
                    } else {
                        // Remove error
                        if (errorContainer) {
                            errorContainer.classList.remove('is-invalid');

                            // Remove feedback message if present
                            if (errorContainer.nextElementSibling && errorContainer.nextElementSibling.classList.contains('invalid-feedback')) {
                                errorContainer.nextElementSibling.remove();
                            }
                        }

                        // Mark section as valid
                        navItem.classList.remove('has-error');
                        navItem.classList.add('is-complete');

                        // Remove error from section title
                        const sectionTitle = section.querySelector('.section-title');
                        if (sectionTitle) {
                            sectionTitle.classList.remove('has-error');
                        }

                        return true;
                    }
                }

                // Special handling for gender-issue section to check for duplicate activities
                if (sectionId === 'gender-issue') {
                    const activityInput = document.getElementById('activity');
                    if (activityInput && activityInput.dataset.isDuplicate === 'true') {
                        // Mark section as having error
                        navItem.classList.add('has-error');
                        navItem.classList.remove('is-complete');

                        // Add error to section title
                        const sectionTitle = section.querySelector('.section-title');
                        if (sectionTitle) {
                            sectionTitle.classList.add('has-error');
                        }

                        return false;
                    }
                }

                // Check required fields
                let allFieldsValid = true;

                // Check all required inputs, selects, and textareas in the section
                const requiredFields = section.querySelectorAll('input[required], select[required], textarea[required]');

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        // Mark field as invalid
                        markAsInvalid(field, 'This field is required');
                        allFieldsValid = false;
                    } else {
                        // Mark field as valid
                        markAsValid(field);
                    }
                });

                // Update navigation status
                updateSectionStatus(sectionId, allFieldsValid);

                return allFieldsValid;
            }

            // Function to validate the Work Plan section
            function validateWorkPlanSection() {
                const section = document.getElementById('section-9');
                const navItem = document.querySelector('.form-nav-item[data-section="section-9"]');

                if (!section || !navItem) return false;

                let hasErrors = false;
                let allRequirementsMet = true;

                // Check if timeline table is visible
                const timelineTableContainer = document.getElementById('timeline-table-container');
                if (!timelineTableContainer || timelineTableContainer.style.display === 'none') {
                    // Dates haven't been selected yet, section is not complete
                    navItem.classList.remove('is-complete');
                    return false;
                }

                // Check each activity row
                const activityRows = document.querySelectorAll('#timeline-activities .activity-row');
                activityRows.forEach(row => {
                    const nameInput = row.querySelector('.activity-name');
                    const checkboxes = row.querySelectorAll('.activity-checkbox');

                    if (!nameInput) return;

                    // Track requirements for this row
                    let rowNameFilled = nameInput.value.trim() !== '';
                    let rowHasCheckedBox = false;

                    // Check if activity name is filled
                    if (!rowNameFilled) {
                        if (validationTriggered) {
                            markAsInvalid(nameInput, 'Activity name is required');
                        }
                        hasErrors = true;
                        allRequirementsMet = false;
                    } else {
                        // Only clear validation if validation has been triggered or there's an explicit error
                        if (validationTriggered || nameInput.classList.contains('is-invalid')) {
                            markAsValid(nameInput);
                        }
                    }

                    // Check if at least one checkbox is selected
                    checkboxes.forEach(checkbox => {
                        if (checkbox.checked) {
                            rowHasCheckedBox = true;
                        }
                    });

                    if (!rowHasCheckedBox) {
                        if (validationTriggered) {
                            // Add error message for checkboxes
                            const checkboxErrorId = `checkbox-error-${Math.random().toString(36).substring(2, 9)}`;

                            // Remove any existing checkbox error for this row
                            const existingErrors = row.querySelectorAll('.checkbox-error-msg');
                            existingErrors.forEach(error => error.remove());

                            // Create new error
                            const errorDiv = document.createElement('div');
                            errorDiv.id = checkboxErrorId;
                            errorDiv.className = 'invalid-feedback checkbox-error-msg';
                            errorDiv.textContent = 'Select at least one date for this activity';
                            errorDiv.style.display = 'block';
                            errorDiv.style.position = 'absolute';
                            errorDiv.style.bottom = '-20px';
                            errorDiv.style.left = '0';
                            errorDiv.style.zIndex = '1';

                            // Find the validation container or input group to add the error message
                            const validationContainer = row.querySelector('.validation-message-container');
                            if (validationContainer) {
                                validationContainer.appendChild(errorDiv);
                            } else {
                                const inputGroup = nameInput.closest('.input-group');
                                if (inputGroup) {
                                    // Position the input group to contain the absolute-positioned error
                                    inputGroup.style.position = 'relative';
                                    inputGroup.appendChild(errorDiv);
                                }
                            }

                            // Add invalid class to the row
                            row.classList.add('is-invalid');
                        }
                        hasErrors = true;
                        allRequirementsMet = false;
                    } else {
                        // Only clear validation errors if validation was triggered or there's an explicit error
                        if (validationTriggered || row.classList.contains('is-invalid')) {
                            // Remove error message
                            const existingErrors = row.querySelectorAll('.checkbox-error-msg');
                            existingErrors.forEach(error => error.remove());

                            // Remove invalid class from row only if name is also valid
                            if (rowNameFilled) {
                                row.classList.remove('is-invalid');
                            }
                        }
                    }

                    // Explicitly track if this row meets all requirements
                    // A row is only complete if BOTH name is filled AND at least one checkbox is selected
                    if (!rowNameFilled || !rowHasCheckedBox) {
                        allRequirementsMet = false;
                    }
                });

                // Update nav status based on validation state
                if (validationTriggered) {
                    // Only show errors if validation has been triggered (form submitted)
                    if (hasErrors) {
                        navItem.classList.add('has-error');
                        navItem.classList.remove('is-complete');

                        // Add error to section title
                        const sectionTitle = section.querySelector('.section-title');
                        if (sectionTitle) {
                            sectionTitle.classList.add('has-error');
                        }
                    } else {
                        navItem.classList.remove('has-error');

                        // Remove error from section title
                        const sectionTitle = section.querySelector('.section-title');
                        if (sectionTitle) {
                            sectionTitle.classList.remove('has-error');
                        }

                        if (allRequirementsMet) {
                            navItem.classList.add('is-complete');
                        } else {
                            navItem.classList.remove('is-complete');
                        }
                    }
                } else {
                    // For regular state (before validation triggered)
                    // Just update completion status without showing errors
                    if (allRequirementsMet) {
                        navItem.classList.add('is-complete');
                    } else {
                        navItem.classList.remove('is-complete');
                        // IMPORTANT: Do NOT add has-error class here
                        navItem.classList.remove('has-error');

                        // Also ensure section title doesn't have error class
                        const sectionTitle = section.querySelector('.section-title');
                        if (sectionTitle) {
                            sectionTitle.classList.remove('has-error');
                        }
                    }
                }

                return !hasErrors;
            }

            // Update checkbox cells in a row
            function updateRowCheckboxes(row, dates) {
                if (!row || !dates) return;

                // Remove existing checkbox cells
                const existingCells = row.querySelectorAll('td.checkbox-cell');
                existingCells.forEach(cell => cell.remove());

                // Get the header row to reference date titles
                const headerRow = document.querySelector('#timeline-table thead tr');

                // Add a checkbox cell for each date
                dates.forEach((date, index) => {
                    const cell = document.createElement('td');
                    cell.className = 'checkbox-cell';
                    cell.style.textAlign = 'center';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'activity-checkbox';

                    // Store date string in data attribute
                    if (headerRow && headerRow.children[index + 1]) {
                        const dateLabel = headerRow.children[index + 1].textContent.trim();
                        checkbox.setAttribute('data-date', dateLabel);
                    }

                    checkbox.name = `activity_date_${index}[]`;
                    checkbox.value = date ? date.toISOString() : index;

                    // Add event listener to update validation and collect checked dates
                    checkbox.addEventListener('change', function() {
                        // Collect all checked dates for this row
                        const checkboxes = row.querySelectorAll('input[type="checkbox"]');
                        const checkedDates = [];

                        checkboxes.forEach(cb => {
                            if (cb.checked && cb.hasAttribute('data-date')) {
                                checkedDates.push(cb.getAttribute('data-date'));
                            }
                        });

                        // Log the dates for this checkbox
                        console.log('Checkbox data-date attribute:', checkbox.getAttribute('data-date'));
                        console.log('Checkbox checked status:', checkbox.checked);
                        console.log('All checked dates after change:', checkedDates);

                        // Store the checked dates in a hidden input
                        // First, remove any existing hidden inputs for workplanDate to prevent duplicates
                        const existingInputs = row.querySelectorAll('input[name="workplanDate[]"]');
                        existingInputs.forEach(input => input.remove());

                        // Create a new hidden input
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'workplanDate[]';
                        hiddenInput.value = checkedDates.join(',');
                        row.appendChild(hiddenInput);

                        console.log('Created new hidden workplanDate input:', hiddenInput.value);

                        validateWorkPlanSection();
                    });

                    cell.appendChild(checkbox);
                    row.appendChild(cell);
                });

                // Add a hidden input for the dates - ensure no duplicates
                // First, remove any existing hidden inputs
                const existingInputs = row.querySelectorAll('input[name="workplanDate[]"]');
                existingInputs.forEach(input => input.remove());

                // Create a new hidden input
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'workplanDate[]';
                hiddenInput.value = ''; // Empty by default
                row.appendChild(hiddenInput);
            }

            // Initialize the default activity row
            function initializeDefaultActivityRow() {
                // Find the default activity row
                const defaultActivityRow = document.querySelector('#timeline-activities .activity-row');
                if (!defaultActivityRow) return;

                // Find the name input
                const nameInput = defaultActivityRow.querySelector('.activity-name');
                if (nameInput) {
                    // Add input event listener to the name field
                    nameInput.addEventListener('input', function() {
                        validateWorkPlanSection();
                    });
                }

                // Add change event listeners to all checkboxes
                const checkboxes = defaultActivityRow.querySelectorAll('.activity-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        validateWorkPlanSection();
                    });
                });

                // Initial validation check
                validateWorkPlanSection();
            }
        </script>
        <script>
            // Calculate participant totals
            document.addEventListener('DOMContentLoaded', function() {
                // Function to calculate totals
                function calculateParticipantTotals() {
                    // Get internal participant counts
                    const internalMale = parseInt(document.getElementById('internal_male').value) || 0;
                    const internalFemale = parseInt(document.getElementById('internal_female').value) || 0;
                    const internalTotal = internalMale + internalFemale;

                    // Get external participant counts
                    const externalMale = parseInt(document.getElementById('external_male').value) || 0;
                    const externalFemale = parseInt(document.getElementById('external_female').value) || 0;
                    const externalTotal = externalMale + externalFemale;

                    // Calculate grand totals
                    const totalMale = internalMale + externalMale;
                    const totalFemale = internalFemale + externalFemale;
                    const grandTotal = totalMale + totalFemale;

                    // Update internal total
                    document.getElementById('internal_total').value = internalTotal;

                    // Update external total
                    document.getElementById('external_total').value = externalTotal;

                    // Update grand totals
                    document.getElementById('total_male').value = totalMale;
                    document.getElementById('total_female').value = totalFemale;
                    document.getElementById('grand_total').value = grandTotal;
                }

                // Add event listeners to participant count fields
                const participantCountFields = document.querySelectorAll('.participant-count');
                participantCountFields.forEach(field => {
                    field.addEventListener('input', calculateParticipantTotals);
                });

                // Initialize totals on page load
                calculateParticipantTotals();
            });
        </script>
        <script src="../js/approval-badge.js"></script>

        <script>
            // Work Plan Section Initialization
            document.addEventListener('DOMContentLoaded', function() {
                // Get date inputs from Basic Info section
                const startMonthSelect = document.getElementById('startMonth');
                const startDaySelect = document.getElementById('startDay');
                const startYearSelect = document.getElementById('startYear');
                const endMonthSelect = document.getElementById('endMonth');
                const endDaySelect = document.getElementById('endDay');
                const endYearSelect = document.getElementById('endYear');

                // Add activity button
                const addActivityBtn = document.getElementById('add-activity-btn');
                if (addActivityBtn) {
                    addActivityBtn.addEventListener('click', function() {
                        addNewActivityRow();
                    });
                }

                // Event delegation for removing activities
                document.addEventListener('click', function(e) {
                    if (e.target.closest('.remove-activity')) {
                        const row = e.target.closest('.activity-row');
                        removeActivityRow(row);
                    }
                });

                // Initialize event listeners for the default activity row
                initializeDefaultActivityRow();

                // Function to check if dates are selected and generate the timeline
                function checkAndGenerateTimeline() {
                    const startMonth = startMonthSelect?.value;
                    const startDay = startDaySelect?.value;
                    const startYear = startYearSelect?.value;
                    const endMonth = endMonthSelect?.value;
                    const endDay = endDaySelect?.value;
                    const endYear = endYearSelect?.value;

                    // Check if all date fields are filled
                    if (startMonth && startDay && startYear && endMonth && endDay && endYear) {
                        // Create date objects
                        const startDate = new Date(startYear, startMonth - 1, startDay);
                        const endDate = new Date(endYear, endMonth - 1, endDay);

                        // Generate the timeline
                        generateWorkPlanTimeline(startDate, endDate);
                    }
                }

                // Add event listeners to date fields
                if (startMonthSelect && startDaySelect && startYearSelect &&
                    endMonthSelect && endDaySelect && endYearSelect) {

                    const dateFields = [startMonthSelect, startDaySelect, startYearSelect,
                        endMonthSelect, endDaySelect, endYearSelect
                    ];

                    dateFields.forEach(field => {
                        field.addEventListener('change', checkAndGenerateTimeline);
                    });

                    // Initial check
                    setTimeout(checkAndGenerateTimeline, 500);
                }
            });

            // Generate timeline for work plan
            function generateWorkPlanTimeline(startDate, endDate) {
                // Get containers
                const timelineMessage = document.getElementById('timeline-message');
                const timelineTableContainer = document.getElementById('timeline-table-container');
                const timelineTable = document.getElementById('timeline-table');

                if (!timelineMessage || !timelineTableContainer || !timelineTable) return;

                // Check if dates are valid
                if (startDate > endDate) {
                    timelineMessage.textContent = "Error: End date is before start date. Please correct the dates in Basic Info section.";
                    timelineMessage.style.display = 'block';
                    timelineTableContainer.style.display = 'none';
                    return;
                }

                // Hide message and show table
                timelineMessage.style.display = 'none';
                timelineTableContainer.style.display = 'block';

                // Get the table header row
                const headerRow = timelineTable.querySelector('thead tr');

                // Clear any existing date columns
                while (headerRow.children.length > 1) {
                    headerRow.removeChild(headerRow.lastChild);
                }

                // Generate date columns
                const dates = [];
                const currentDate = new Date(startDate);

                // Get month names for display
                const monthNames = [
                    "Jan", "Feb", "Mar", "Apr", "May", "Jun",
                    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                ];

                // Add date columns
                while (currentDate <= endDate) {
                    const formattedDate = `${monthNames[currentDate.getMonth()]} ${currentDate.getDate()}`;
                    dates.push(new Date(currentDate));

                    const th = document.createElement('th');
                    th.textContent = formattedDate;
                    th.style.width = "80px";
                    th.style.textAlign = "center";
                    headerRow.appendChild(th);

                    // Move to next day
                    currentDate.setDate(currentDate.getDate() + 1);
                }

                // Update existing activity rows with the new dates
                const activityRows = document.querySelectorAll('#timeline-activities .activity-row');
                activityRows.forEach(row => {
                    updateRowCheckboxes(row, dates);

                    // If we're in edit mode and have a hidden input with date data, use it to check boxes
                    const hiddenInput = row.querySelector('input[name="workplanDate[]"]');
                    if (hiddenInput && hiddenInput.value) {
                        const savedDates = hiddenInput.value.split(',');

                        if (savedDates.length > 0) {
                            console.log('Found saved dates to restore:', savedDates);

                            // Check the appropriate checkboxes
                            const checkboxes = row.querySelectorAll('input.activity-checkbox');
                            checkboxes.forEach((checkbox, index) => {
                                if (headerRow.children[index + 1]) {
                                    const headerDate = headerRow.children[index + 1].textContent.trim();

                                    // Try different matching approaches
                                    if (savedDates.includes(headerDate) ||
                                        savedDates.some(date => date.trim() === headerDate) ||
                                        savedDates.some(date => date.trim().toLowerCase() === headerDate.toLowerCase())) {
                                        checkbox.checked = true;
                                        console.log(`Re-checked date ${headerDate} from saved data`);
                                    }
                                }
                            });
                        }
                    }
                });

                // If no activity rows exist, add the first one
                if (activityRows.length === 0) {
                    addNewActivityRow(dates);
                }

                // Update validation
                validateWorkPlanSection();

                // If we're in edit mode (with editingEntryId), check if workplan data needs to be restored
                if (window.editMode && window.editingEntryId) {
                    console.log('In edit mode, checking if workplan data needs restoration');
                    // If we have global temporary storage for workplan data, restore it
                    if (window.tempWorkplanActivity && window.tempWorkplanDate) {
                        try {
                            restoreWorkplanData(window.tempWorkplanActivity, window.tempWorkplanDate);
                        } catch (e) {
                            console.error('Error restoring workplan data:', e);
                        }
                    }
                }
            }

            // Helper function to restore workplan data
            function restoreWorkplanData(activities, dates) {
                console.log('Attempting to restore workplan data', activities, dates);
                if (!Array.isArray(activities) || !Array.isArray(dates)) {
                    console.warn('Invalid workplan data to restore');
                    return;
                }

                // Get all existing rows
                const rows = document.querySelectorAll('#timeline-activities .activity-row');
                const headerRow = document.querySelector('#timeline-table thead tr');

                if (!headerRow) {
                    console.warn('Header row not found, cannot restore workplan');
                    return;
                }

                // If we need more rows, add them
                while (rows.length < activities.length) {
                    addNewActivityRow();
                }

                // Update each row
                activities.forEach((activity, i) => {
                    if (i >= rows.length) return;

                    const row = rows[i];
                    const nameInput = row.querySelector('input.activity-name');
                    if (nameInput) {
                        nameInput.value = activity;
                    }

                    if (dates[i]) {
                        const dateArray = dates[i].split(',');
                        const checkboxes = row.querySelectorAll('input.activity-checkbox');

                        checkboxes.forEach((checkbox, j) => {
                            if (headerRow.children[j + 1]) {
                                const headerDate = headerRow.children[j + 1].textContent.trim();

                                // Check if this date should be checked
                                if (dateArray.includes(headerDate) ||
                                    dateArray.some(date => date.trim() === headerDate)) {
                                    checkbox.checked = true;
                                    console.log(`Restored checked date ${headerDate} for activity ${i+1}`);
                                }
                            }
                        });

                        // Update the hidden input
                        const hiddenInput = row.querySelector('input[name="workplanDate[]"]');
                        if (hiddenInput) {
                            hiddenInput.value = dates[i];
                        }
                    }
                });

                // Force validation of the section to update completion status
                setTimeout(() => {
                    validateWorkPlanSection();

                    // Directly set the completion status for the section since validation has already happened
                    const navItem = document.querySelector('.form-nav-item[data-section="section-9"]');
                    const section = document.getElementById('section-9');

                    if (navItem && section) {
                        // Check each activity row to see if all requirements are met
                        const activityRows = document.querySelectorAll('#timeline-activities .activity-row');
                        let allRequirementsMet = true;

                        activityRows.forEach(row => {
                            const nameInput = row.querySelector('.activity-name');
                            const checkboxes = row.querySelectorAll('.activity-checkbox:checked');

                            if (!nameInput || !nameInput.value.trim() || checkboxes.length === 0) {
                                allRequirementsMet = false;
                            }
                        });

                        // If all requirements are met, mark the section as complete
                        if (allRequirementsMet) {
                            navItem.classList.add('is-complete');
                            navItem.classList.remove('has-error');

                            // Also update the section title
                            const sectionTitle = section.querySelector('.section-title');
                            if (sectionTitle) {
                                sectionTitle.classList.remove('has-error');
                            }

                            console.log('✅ Workplan section requirements met - marking as complete');
                        } else {
                            console.log('❌ Workplan section requirements not met');
                        }
                    }
                }, 300);
            }

            // Add a new activity row
            function addNewActivityRow(dates = null) {
                const timelineActivities = document.getElementById('timeline-activities');
                if (!timelineActivities) return null;

                // If dates is null, get the dates from existing rows
                if (!dates && timelineActivities.children.length > 0) {
                    const firstRow = timelineActivities.querySelector('.activity-row');
                    const checkboxCells = firstRow.querySelectorAll('td.checkbox-cell');

                    // Count the number of date columns
                    if (checkboxCells.length > 0) {
                        // We'll need to create matching checkboxes in the new row
                        dates = Array.from({
                            length: checkboxCells.length
                        }, () => null);
                    }
                }

                // Create a new row
                const newRow = document.createElement('tr');
                newRow.className = 'activity-row';

                // Add activity name cell
                const nameCell = document.createElement('td');
                const inputGroup = document.createElement('div');
                inputGroup.className = 'input-group';
                // Add position relative to contain validation messages
                inputGroup.style.position = 'relative';

                const nameInput = document.createElement('input');
                nameInput.type = 'text';
                nameInput.className = 'form-control activity-name';
                nameInput.name = 'workplanActivity[]'; // Changed to match the form field name
                nameInput.placeholder = 'Enter activity name';
                nameInput.required = true;

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-outline-danger remove-activity';
                removeBtn.innerHTML = '<i class="fas fa-minus"></i>';
                // Ensure button stays at the end of the input group
                removeBtn.style.marginLeft = '5px';
                removeBtn.style.flexShrink = '0';

                // Only show remove button if there's already at least one row
                if (timelineActivities.children.length > 0) {
                    removeBtn.style.display = 'block';
                }

                inputGroup.appendChild(nameInput);
                inputGroup.appendChild(removeBtn);
                nameCell.appendChild(inputGroup);

                // Create a container for validation messages that won't affect layout
                const validationContainer = document.createElement('div');
                validationContainer.className = 'validation-message-container';
                validationContainer.style.position = 'absolute';
                validationContainer.style.bottom = '-20px';
                validationContainer.style.left = '0';
                validationContainer.style.width = '100%';
                inputGroup.appendChild(validationContainer);
                newRow.appendChild(nameCell);

                // Add checkbox cells if dates are available
                if (dates && dates.length > 0) {
                    updateRowCheckboxes(newRow, dates);
                }

                // Add the row to the table
                timelineActivities.appendChild(newRow);

                // Add input event listener to the new activity name field
                nameInput.addEventListener('input', function() {
                    validateWorkPlanSection();
                });

                // Do not trigger validation on newly added rows
                // This ensures empty fields aren't immediately marked as invalid
                // Just update the section status without showing validation errors
                updateSectionStatus('section-9', false);

                // Skip focus part to avoid errors if item-description doesn't exist 
                try {
                    const descriptionField = newRow.querySelector('.item-description');
                    if (descriptionField) {
                        descriptionField.focus();
                    }
                } catch (e) {
                    console.log('No item-description field found, skipping focus');
                }

                // Just calculate section status without showing any validation errors
                // Don't modify the nav item or section header visually at all when adding a new row
                // This will prevent "missing input" validation appearing when adding rows

                // Just remove has-error classes if they're present
                const workplanNavItem = document.querySelector('.form-nav-item[data-section="section-9"]');
                const workplanSectionTitle = document.querySelector('#section-9 .section-title');

                if (workplanNavItem) workplanNavItem.classList.remove('has-error');
                if (workplanSectionTitle) workplanSectionTitle.classList.remove('has-error');

                // Return the newly added row
                return newRow;
            }

            // Remove an activity row
            function removeActivityRow(row) {
                const timelineActivities = document.getElementById('timeline-activities');
                if (!timelineActivities || !row) return;

                // Remove the row
                timelineActivities.removeChild(row);

                // If this was the last row, add a new empty one
                if (timelineActivities.children.length === 0) {
                    const headerRow = document.querySelector('#timeline-table thead tr');
                    if (headerRow) {
                        const columnCount = headerRow.children.length - 1;

                        if (columnCount > 0) {
                            // Create empty dates array of the right length
                            const dummyDates = Array.from({
                                length: columnCount
                            }, () => null);
                            addNewActivityRow(dummyDates);
                        }
                    }
                } else {
                    // Hide the remove button on the first row if it's the only one left
                    if (timelineActivities.children.length === 1) {
                        const firstRowBtn = timelineActivities.querySelector('.activity-row .remove-activity');
                        if (firstRowBtn) {
                            firstRowBtn.style.display = 'none';
                        }
                    }
                }

                // Update validation status
                validateWorkPlanSection();
            }
        </script>

        <!-- Finance Section Script -->
        <script>
            function calculatePSAttribution() {
                console.log('Calculating PS Attribution...');
                const psAttributionInput = document.getElementById('psAttribution');
                if (!psAttributionInput) return;

                // Get total duration from the Basic Info section
                const totalDurationField = document.getElementById('totalDuration');
                let totalDuration = 0;
                if (totalDurationField && totalDurationField.value && !isNaN(totalDurationField.value)) {
                    totalDuration = parseFloat(totalDurationField.value);
                } else if (totalDurationField && totalDurationField.value && !isNaN(parseFloat(totalDurationField.value))) {
                    totalDuration = parseFloat(totalDurationField.value);
                } else {
                    totalDuration = 0;
                }
                if (isNaN(totalDuration) || totalDuration <= 0) {
                    console.log('Duration not valid for PS Attribution:', totalDurationField.value);
                    psAttributionInput.value = "0.00";
                    return;
                }
                console.log('Total Duration used for PS Attribution:', totalDuration);

                let totalAttribution = 0;
                let hasTeamData = false;

                // Sum all leader rates
                const leaderRateInputs = document.querySelectorAll('input[name="leader_rate[]"]');
                leaderRateInputs.forEach((input, idx) => {
                    const rate = parseFloat(input.value) || 0;
                    if (rate > 0) {
                        totalAttribution += rate * totalDuration;
                        hasTeamData = true;
                        console.log(`Leader #${idx+1} attribution:`, rate, totalDuration, rate * totalDuration);
                    }
                });

                // Sum all assistant leader rates
                const asstLeaderRateInputs = document.querySelectorAll('input[name="asst_leader_rate[]"]');
                asstLeaderRateInputs.forEach((input, idx) => {
                    const rate = parseFloat(input.value) || 0;
                    if (rate > 0) {
                        totalAttribution += rate * totalDuration;
                        hasTeamData = true;
                        console.log(`Assistant Leader #${idx+1} attribution:`, rate, totalDuration, rate * totalDuration);
                    }
                });

                // Sum all staff rates
                const staffRateInputs = document.querySelectorAll('input[name="staff_rate[]"]');
                staffRateInputs.forEach((input, idx) => {
                    const rate = parseFloat(input.value) || 0;
                    if (rate > 0) {
                        totalAttribution += rate * totalDuration;
                        hasTeamData = true;
                        console.log(`Staff #${idx+1} attribution:`, rate, totalDuration, rate * totalDuration);
                    }
                });

                // Only update if at least one personnel is filled
                if (!hasTeamData) {
                    psAttributionInput.value = "0.00";
                    return;
                }

                psAttributionInput.value = totalAttribution.toFixed(2);
                console.log('Total PS Attribution:', totalAttribution);

                const psAttributionMessage = document.getElementById('psAttributionMessage');
                if (hasTeamData) {
                    psAttributionMessage.classList.add('d-none');
                } else {
                    psAttributionMessage.classList.remove('d-none');
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Finance Section - Financial Plan Radio Buttons
                const withFinancialPlanRadio = document.getElementById('withFinancialPlan');
                const withoutFinancialPlanRadio = document.getElementById('withoutFinancialPlan');
                const financialPlanSection = document.getElementById('financialPlanSection');
                const totalCostInput = document.getElementById('totalCost');
                const emptyFinancialPlanMessage = document.getElementById('emptyFinancialPlanMessage');
                const financialPlanTableBody = document.getElementById('financialPlanTableBody');
                const grandTotalCost = document.getElementById('grandTotalCost');
                const psAttributionMessage = document.getElementById('psAttributionMessage');

                // Source Fund Custom Multiselect
                const sourceFundContainer = document.getElementById('sourceFundContainer');
                const sourceFundOptions = document.querySelectorAll('.source-fund-option');
                const sourceFundInput = document.getElementById('sourceOfFund');

                // Initialize Source Fund Multiselect
                if (sourceFundContainer && sourceFundOptions.length > 0) {
                    // Add click event to each option
                    sourceFundOptions.forEach(option => {
                        option.addEventListener('click', function() {
                            // Toggle selected class
                            this.classList.toggle('selected');

                            // Update hidden input value with selected options
                            updateSourceFundValue();

                            // Clear validation error when a source fund is selected
                            const sourceFundContainer = document.getElementById('sourceFundContainer');
                            if (sourceFundContainer) {
                                sourceFundContainer.classList.remove('is-invalid');
                                // Remove feedback message if present
                                const nextElement = sourceFundContainer.nextElementSibling;
                                if (nextElement && nextElement.classList.contains('invalid-feedback')) {
                                    nextElement.remove();
                                }
                            }
                        });
                    });

                    // Function to update the hidden input with selected values
                    function updateSourceFundValue() {
                        const selectedOptions = Array.from(document.querySelectorAll('.source-fund-option.selected'))
                            .map(option => option.getAttribute('data-value'));

                        // Update the hidden input value
                        sourceFundInput.value = selectedOptions.join(',');

                        // Trigger validation for section-10
                        validateSection('section-10');
                        checkAllFieldsFilled('section-10');
                        updateSectionStatus('section-10', true);
                    }
                }

                // Add event listener for approved budget field
                const approvedBudgetInput = document.getElementById('approvedBudget');
                if (approvedBudgetInput) {
                    approvedBudgetInput.addEventListener('input', function(e) {
                        // Format with commas for thousands, millions, etc.
                        let value = this.value.replace(/[^\d.]/g, ''); // Remove all non-numeric characters except decimal point

                        // If we have a valid number, format it
                        if (value !== '' && !isNaN(parseFloat(value))) {
                            // Format with commas
                            const parts = value.split('.');
                            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');

                            // Set the value without moving cursor to end
                            const cursorPosition = this.selectionStart;
                            const oldLength = this.value.length;

                            this.value = parts.join('.');

                            // Adjust cursor position
                            const newLength = this.value.length;
                            const cursorOffset = newLength - oldLength;
                            this.setSelectionRange(cursorPosition + cursorOffset, cursorPosition + cursorOffset);
                        }

                        // Clear validation error when input changes
                        if (this.value.trim() !== '' && this.value.trim() !== '0.00') {
                            const inputGroup = this.closest('.input-group');
                            if (inputGroup) {
                                inputGroup.classList.remove('is-invalid');
                            }

                            // Remove feedback message if present
                            const formGroup = this.closest('.form-group');
                            if (formGroup) {
                                const feedback = formGroup.querySelector('.invalid-feedback');
                                if (feedback) {
                                    feedback.remove();
                                }
                            }
                        }
                    });
                }

                // Financial note event handler is already defined elsewhere

                // Add event listeners to financial plan radio buttons
                if (withFinancialPlanRadio && withoutFinancialPlanRadio) {
                    withFinancialPlanRadio.addEventListener('change', function() {
                        if (this.checked) {
                            financialPlanSection.style.display = 'block';
                            updateFinancialPlanVisibility();
                            calculateTotalCost();
                        }

                        // Remove error styling when a radio button is selected
                        const radioContainer = document.querySelector('#section-10 .form-group:nth-child(2)');
                        if (radioContainer) {
                            radioContainer.classList.remove('is-invalid');
                            // Remove feedback message if present
                            const feedback = radioContainer.querySelector('.invalid-feedback');
                            if (feedback) {
                                feedback.remove();
                            }
                        }
                    });

                    withoutFinancialPlanRadio.addEventListener('change', function() {
                        if (this.checked) {
                            financialPlanSection.style.display = 'none';
                            totalCostInput.value = '0.00';
                            grandTotalCost.textContent = '₱0.00';
                        }

                        // Remove error styling when a radio button is selected
                        const radioContainer = document.querySelector('#section-10 .form-group:nth-child(2)');
                        if (radioContainer) {
                            radioContainer.classList.remove('is-invalid');
                            // Remove feedback message if present
                            const feedback = radioContainer.querySelector('.invalid-feedback');
                            if (feedback) {
                                feedback.remove();
                            }
                        }
                    });
                }

                // Add item button functionality
                const addItemButton = document.getElementById('addFinancialItem');
                if (addItemButton) {
                    addItemButton.addEventListener('click', function() {
                        addFinancialPlanItem();
                        updateFinancialPlanVisibility();
                    });
                }

                // Function to update financial plan table visibility
                function updateFinancialPlanVisibility() {
                    const hasItems = financialPlanTableBody.querySelectorAll('tr').length > 0;

                    if (hasItems) {
                        document.getElementById('financialPlanTable').style.display = 'table';
                        emptyFinancialPlanMessage.style.display = 'none';
                    } else {
                        document.getElementById('financialPlanTable').style.display = 'none';
                        emptyFinancialPlanMessage.style.display = 'flex';
                    }
                }

                // Function to add a new financial plan item
                function addFinancialPlanItem() {
                    const newRow = document.createElement('tr');
                    newRow.className = 'financial-item-row';
                    newRow.innerHTML = `
                    <td>
                        <input type="text" class="form-control item-description" name="item_description[]" placeholder="Enter item description" required>
                    </td>
                    <td>
                        <input type="number" class="form-control item-quantity" name="item_quantity[]" min="0" placeholder="Amount" value="" required>
                    </td>
                    <td>
                        <input type="text" class="form-control item-unit" name="item_unit[]" placeholder="Unit" required>
                    </td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control item-unit-cost" name="item_unit_cost[]" min="0" step="0.01" placeholder="0.00" value="" required>
                            </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="item-total-cost fw-medium">₱0.00</span>
                            <input type="hidden" name="item_total_cost[]" value="0.00">
                        </div>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-remove-item btn-outline-danger" title="Remove item">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                `;

                    // Add the row to the table
                    financialPlanTableBody.appendChild(newRow);

                    // Directly hide the empty message since we now have a row
                    const emptyMessage = document.getElementById('emptyFinancialPlanMessage');
                    if (emptyMessage) {
                        console.log('Directly hiding empty message after adding row');
                        emptyMessage.style.display = 'none';
                        emptyMessage.classList.add('d-none');
                    }

                    // Clear validation error for financial plan section when an item is added
                    const financialPlanSection = document.getElementById('financialPlanSection');
                    if (financialPlanSection) {
                        financialPlanSection.classList.remove('is-invalid');

                        // Remove any error messages
                        const feedbacks = financialPlanSection.querySelectorAll('.invalid-feedback:not(#emptyFinancialPlanMessage)');
                        feedbacks.forEach(feedback => feedback.remove());
                    }

                    // Update visibility immediately after adding the row
                    updateFinancialPlanVisibility();

                    // Add event listeners to the new inputs
                    const quantityInput = newRow.querySelector('.item-quantity');
                    const unitCostInput = newRow.querySelector('.item-unit-cost');
                    const removeButton = newRow.querySelector('.btn-remove-item');

                    // Calculate item total when quantity or unit cost changes
                    quantityInput.addEventListener('input', function() {
                        calculateItemTotal(newRow);

                        // Only trigger validation if it's already been triggered by addBtn
                        if (validationTriggered) {
                            validateSection('section-10');
                            checkAllFieldsFilled('section-10');
                            updateSectionStatus('section-10', true);
                        }
                    });

                    unitCostInput.addEventListener('input', function() {
                        calculateItemTotal(newRow);

                        // Only trigger validation if it's already been triggered by addBtn
                        if (validationTriggered) {
                            validateSection('section-10');
                            checkAllFieldsFilled('section-10');
                            updateSectionStatus('section-10', true);
                        }
                    });

                    // Add validation for description and unit fields
                    const descriptionInput = newRow.querySelector('.item-description');
                    const unitInput = newRow.querySelector('.item-unit');

                    descriptionInput.addEventListener('input', function() {
                        // Only trigger validation if it's already been triggered by addBtn
                        if (validationTriggered) {
                            validateSection('section-10');
                            checkAllFieldsFilled('section-10');
                            updateSectionStatus('section-10', true);
                        }
                    });

                    unitInput.addEventListener('input', function() {
                        // Only trigger validation if it's already been triggered by addBtn
                        if (validationTriggered) {
                            validateSection('section-10');
                            checkAllFieldsFilled('section-10');
                            updateSectionStatus('section-10', true);
                        }
                    });

                    // Remove item when remove button is clicked
                    removeButton.addEventListener('click', function() {
                        // Remove the row
                        financialPlanTableBody.removeChild(newRow);
                        calculateTotalCost();

                        // Check if this was the last row
                        if (financialPlanTableBody.querySelectorAll('tr').length === 0) {
                            // It was the last row, explicitly show the empty message
                            const emptyMessage = document.getElementById('emptyFinancialPlanMessage');
                            if (emptyMessage) {
                                emptyMessage.style.display = 'flex';
                                emptyMessage.classList.remove('d-none');
                            }
                        }

                        // Regular updates
                        updateFinancialPlanVisibility();

                        // Only trigger validation if it's already been triggered by addBtn
                        if (validationTriggered) {
                            validateSection('section-10');
                            checkAllFieldsFilled('section-10');
                            updateSectionStatus('section-10', true);
                        }
                    });

                    // Focus on the first input field
                    newRow.querySelector('.item-description').focus();

                    // Only run validation on newly added rows if validation has already been triggered
                    // by the user clicking addBtn
                    if (validationTriggered) {
                        validateSection('section-10');
                        checkAllFieldsFilled('section-10');
                        updateSectionStatus('section-10', true);
                    }
                }

                // Function to calculate total cost for a financial item
                function calculateItemTotal(row) {
                    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                    const unitCost = parseFloat(row.querySelector('.item-unit-cost').value) || 0;
                    const totalCost = quantity * unitCost;

                    // Update the total cost display
                    row.querySelector('.item-total-cost').textContent = '₱' + totalCost.toFixed(2);
                    row.querySelector('input[name="item_total_cost[]"]').value = totalCost.toFixed(2);

                    // Update the grand total
                    calculateTotalCost();
                }

                // Function to calculate the total cost of all items
                function calculateTotalCost() {
                    let grandTotal = 0;

                    // Sum up all item totals
                    document.querySelectorAll('.financial-item-row').forEach(row => {
                        const totalCostText = row.querySelector('input[name="item_total_cost[]"]').value;
                        grandTotal += parseFloat(totalCostText) || 0;
                    });

                    // Update the grand total display
                    grandTotalCost.textContent = '₱' + grandTotal.toFixed(2);
                    totalCostInput.value = grandTotal.toFixed(2);
                }


                // Call calculatePSAttribution initially
                calculatePSAttribution();

                // Add event listener to recalculate PS Attribution when navigating to Finance section
                document.querySelectorAll('.btn-form-nav[data-navigate-to="section-10"]').forEach(button => {
                    button.addEventListener('click', calculatePSAttribution);
                });

                // Initialize empty state for financial plan
                updateFinancialPlanVisibility();

                // Add a MutationObserver to monitor changes to the financial plan table body
                if (financialPlanTableBody) {
                    const observer = new MutationObserver((mutations) => {
                        updateFinancialPlanVisibility();
                    });

                    // Start observing changes to the table body's child elements
                    observer.observe(financialPlanTableBody, {
                        childList: true
                    });
                }

                // Add event listener for financial note
                const financialNote = document.getElementById('financialNote');
                if (financialNote) {
                    financialNote.addEventListener('input', function() {
                        console.log('Financial note updated, triggering validation');

                        // Clear validation error when input changes
                        if (this.value.trim() !== '') {
                            this.classList.remove('is-invalid');

                            // Remove feedback message if present
                            const nextElement = this.nextElementSibling;
                            if (nextElement && nextElement.classList.contains('invalid-feedback')) {
                                nextElement.remove();
                            }
                        }

                        validateSection('section-10');
                        checkAllFieldsFilled('section-10');
                        updateSectionStatus('section-10', true);
                    });
                }

                // Handle multi-select for Source of Fund
                const sourceOfFundSelect = document.getElementById('sourceOfFund');
                if (sourceOfFundSelect) {
                    // Set a nice height for the multi-select
                    const optionCount = sourceOfFundSelect.options.length;
                    const exactHeight = (optionCount * 36) + 10;
                    sourceOfFundSelect.style.height = exactHeight + 'px';

                    // Add change listener to trigger validation
                    sourceOfFundSelect.addEventListener('change', function() {
                        console.log('Source of fund changed, triggering validation');
                        validateSection('section-10');
                        checkAllFieldsFilled('section-10');
                        updateSectionStatus('section-10', true);
                    });

                    // Make multi-select work without holding Ctrl key
                    sourceOfFundSelect.addEventListener('mousedown', function(e) {
                        e.preventDefault();

                        const option = e.target.closest('option');
                        if (!option) return;

                        // Store current scroll position
                        const scrollTop = this.scrollTop;

                        // Toggle the selected state
                        option.selected = !option.selected;

                        // Trigger change event
                        const event = new Event('change', {
                            bubbles: true
                        });
                        this.dispatchEvent(event);

                        // Restore scroll position after selection
                        setTimeout(() => {
                            this.scrollTop = scrollTop;
                        }, 0);

                        // Direct call to validation
                        validateSection('section-10');
                        checkAllFieldsFilled('section-10');
                        updateSectionStatus('section-10', true);

                        // Prevent default mouseup behavior
                        this.focus();
                        return false;
                    });
                }
            });
        </script>
        <script>
            // Initialize Monitoring Section functionality
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize monitoring items functionality
                initMonitoringItems();

                // Run initial validation check on monitoring section
                updateMonitoringSectionStatus();
            });

            // Function to initialize monitoring items
            function initMonitoringItems() {
                const addBtn = document.getElementById('add-monitoring-btn');
                const container = document.getElementById('monitoring-items-container');

                if (!addBtn || !container) return;

                // Add event listeners to all required fields in the first monitoring item
                const firstItem = container.querySelector('.monitoring-item');
                if (firstItem) {
                    const inputs = firstItem.querySelectorAll('textarea, input');
                    inputs.forEach(input => {
                        input.addEventListener('input', function() {
                            if (validationTriggered) {
                                validateSection('section-11');
                            } else {
                                updateMonitoringSectionStatus();
                            }
                        });
                    });
                }

                // Function to populate monitoring section when editing
                window.populateMonitoringSection = function(entry) {
                    console.log('Populating monitoring section with entry data');

                    // Required data sets for monitoring items
                    // Use array spread to ensure consistent handling
                    const monitoringObjectives = Array.isArray(entry.monitoring_objectives) ? [...entry.monitoring_objectives] : [];
                    const monitoringPerformanceIndicators = Array.isArray(entry.monitoring_performance_indicators) ? [...entry.monitoring_performance_indicators] : [];
                    const monitoringBaselineData = Array.isArray(entry.monitoring_baseline_data) ? [...entry.monitoring_baseline_data] : [];
                    const monitoringPerformanceTarget = Array.isArray(entry.monitoring_performance_target) ? [...entry.monitoring_performance_target] : [];
                    const monitoringDataSource = Array.isArray(entry.monitoring_data_source) ? [...entry.monitoring_data_source] : [];
                    const monitoringCollectionMethod = Array.isArray(entry.monitoring_collection_method) ? [...entry.monitoring_collection_method] : [];
                    const monitoringFrequency = Array.isArray(entry.monitoring_frequency_data_collection) ? [...entry.monitoring_frequency_data_collection] : [];
                    const monitoringResponsible = Array.isArray(entry.monitoring_office_persons_involved) ? [...entry.monitoring_office_persons_involved] : [];

                    // Debug the raw data
                    console.log('Raw monitoring_objectives from server:', entry.monitoring_objectives);
                    console.log('Raw monitoring_performance_indicators from server:', entry.monitoring_performance_indicators);
                    console.log('Raw monitoring_baseline_data from server:', entry.monitoring_baseline_data);

                    // Log the processed arrays
                    console.log('Processed data arrays:', {
                        objectives: monitoringObjectives,
                        indicators: monitoringPerformanceIndicators,
                        baseline: monitoringBaselineData,
                        target: monitoringPerformanceTarget,
                        dataSource: monitoringDataSource,
                        collectionMethod: monitoringCollectionMethod,
                        frequency: monitoringFrequency,
                        responsible: monitoringResponsible
                    });

                    // Get the maximum length of all arrays to determine how many items to create
                    const maxLength = Math.max(
                        monitoringObjectives.length || 0,
                        monitoringPerformanceIndicators.length || 0,
                        monitoringBaselineData.length || 0,
                        monitoringPerformanceTarget.length || 0,
                        monitoringDataSource.length || 0,
                        monitoringCollectionMethod.length || 0,
                        monitoringFrequency.length || 0,
                        monitoringResponsible.length || 0
                    );

                    if (maxLength === 0) {
                        console.log('No monitoring items to populate');
                        return;
                    }

                    // Ensure we're working with arrays that have values, not just placeholders
                    const hasRealContent =
                        (monitoringObjectives.some(val => val && val.trim() !== '') ||
                            monitoringPerformanceIndicators.some(val => val && val.trim() !== '') ||
                            monitoringBaselineData.some(val => val && val.trim() !== ''));

                    if (!hasRealContent) {
                        console.log('No real monitoring content found, skipping population');
                        return;
                    }

                    console.log(`Found ${maxLength} monitoring items to populate`);

                    // Clear existing monitoring items except the first one
                    const existingItems = container.querySelectorAll('.monitoring-item');
                    for (let i = 1; i < existingItems.length; i++) {
                        container.removeChild(existingItems[i]);
                    }

                    // Helper to get array value safely
                    const getValueSafely = (arr, index) => {
                        if (Array.isArray(arr) && index < arr.length && arr[index] !== null && arr[index] !== undefined) {
                            return String(arr[index]).trim();
                        }
                        return '';
                    };

                    // Populate the first monitoring item
                    if (existingItems.length > 0) {
                        populateMonitoringItem(existingItems[0], 0,
                            getValueSafely(monitoringObjectives, 0),
                            getValueSafely(monitoringPerformanceIndicators, 0),
                            getValueSafely(monitoringBaselineData, 0),
                            getValueSafely(monitoringPerformanceTarget, 0),
                            getValueSafely(monitoringDataSource, 0),
                            getValueSafely(monitoringCollectionMethod, 0),
                            getValueSafely(monitoringFrequency, 0),
                            getValueSafely(monitoringResponsible, 0)
                        );
                    }

                    // Add and populate additional monitoring items
                    for (let i = 1; i < maxLength; i++) {
                        addMonitoringItem(
                            getValueSafely(monitoringObjectives, i),
                            getValueSafely(monitoringPerformanceIndicators, i),
                            getValueSafely(monitoringBaselineData, i),
                            getValueSafely(monitoringPerformanceTarget, i),
                            getValueSafely(monitoringDataSource, i),
                            getValueSafely(monitoringCollectionMethod, i),
                            getValueSafely(monitoringFrequency, i),
                            getValueSafely(monitoringResponsible, i)
                        );
                    }
                };

                // Function to populate a single monitoring item
                function populateMonitoringItem(item, index, objectives, performanceIndicators, baselineData,
                    performanceTarget, dataSource, collectionMethod, frequency, responsible) {
                    console.log(`Populating monitoring item #${index+1} with:`, {
                        objectives,
                        performanceIndicators,
                        baselineData,
                        performanceTarget,
                        dataSource,
                        collectionMethod,
                        frequency,
                        responsible
                    });

                    // Update the monitoring item number
                    const numberIndicator = item.querySelector('.monitoring-number-indicator');
                    if (numberIndicator) {
                        numberIndicator.textContent = `#${index + 1}`;
                    }

                    // Set values for all inputs in the monitoring item - use correct selectors based on HTML structure
                    const objectivesInput = item.querySelector(`#objectives${index+1}`) || item.querySelector('[name="objectives[]"]');
                    if (objectivesInput) {
                        console.log(`Setting objectives${index+1} to:`, objectives);
                        objectivesInput.value = objectives;
                    } else {
                        console.error(`Could not find objectives input for item #${index+1}`);
                    }

                    const performanceIndicatorsInput = item.querySelector(`#performance_indicators${index+1}`) || item.querySelector('[name="performance_indicators[]"]');
                    if (performanceIndicatorsInput) {
                        console.log(`Setting performance_indicators${index+1} to:`, performanceIndicators);
                        performanceIndicatorsInput.value = performanceIndicators;
                    } else {
                        console.error(`Could not find performance indicators input for item #${index+1}`);
                    }

                    const baselineDataInput = item.querySelector(`#baseline_data${index+1}`) || item.querySelector('[name="baseline_data[]"]');
                    if (baselineDataInput) {
                        console.log(`Setting baseline_data${index+1} to:`, baselineData);
                        baselineDataInput.value = baselineData;
                    } else {
                        console.error(`Could not find baseline data input for item #${index+1}`);
                    }

                    const performanceTargetInput = item.querySelector(`#performance_target${index+1}`) || item.querySelector('[name="performance_target[]"]');
                    if (performanceTargetInput) {
                        console.log(`Setting performance_target${index+1} to:`, performanceTarget);
                        performanceTargetInput.value = performanceTarget;
                    } else {
                        console.error(`Could not find performance target input for item #${index+1}`);
                    }

                    const dataSourceInput = item.querySelector(`#data_source${index+1}`) || item.querySelector('[name="data_source[]"]');
                    if (dataSourceInput) {
                        console.log(`Setting data_source${index+1} to:`, dataSource);
                        dataSourceInput.value = dataSource;
                    } else {
                        console.error(`Could not find data source input for item #${index+1}`);
                    }

                    const collectionMethodInput = item.querySelector(`#collection_method${index+1}`) || item.querySelector('[name="collection_method[]"]');
                    if (collectionMethodInput) {
                        console.log(`Setting collection_method${index+1} to:`, collectionMethod);
                        collectionMethodInput.value = collectionMethod;
                    } else {
                        console.error(`Could not find collection method input for item #${index+1}`);
                    }

                    const frequencyInput = item.querySelector(`#frequency${index+1}`) || item.querySelector('[name="frequency[]"]');
                    if (frequencyInput) {
                        console.log(`Setting frequency${index+1} to:`, frequency);
                        frequencyInput.value = frequency;
                    } else {
                        console.error(`Could not find frequency input for item #${index+1}`);
                    }

                    const responsibleInput = item.querySelector(`#persons_involved${index+1}`) || item.querySelector('[name="persons_involved[]"]');
                    if (responsibleInput) {
                        console.log(`Setting persons_involved${index+1} to:`, responsible);
                        responsibleInput.value = responsible;
                    } else {
                        console.error(`Could not find persons involved input for item #${index+1}`);
                    }

                    // Show remove button for all items except the first one
                    if (index > 0) {
                        const removeButton = item.querySelector('.remove-monitoring');
                        if (removeButton) {
                            removeButton.style.display = 'block';
                        }
                    }
                }

                // Function to add a new monitoring item with optional initial values
                window.addMonitoringItem = function(objectives = '', performanceIndicators = '', baselineData = '',
                    performanceTarget = '', dataSource = '', collectionMethod = '',
                    frequency = '', responsible = '') {
                    // Get current count of monitoring items
                    const currentCount = container.querySelectorAll('.monitoring-item').length;
                    const newNumber = currentCount + 1;

                    // Clone the first monitoring item
                    const firstItem = container.querySelector('.monitoring-item');
                    const newItem = firstItem.cloneNode(true);

                    // Update ID and name attributes to use new index
                    const inputs = newItem.querySelectorAll('textarea, input');
                    inputs.forEach(input => {
                        const oldId = input.id;
                        if (oldId) {
                            const baseName = oldId.replace(/\d+$/, '');
                            input.id = baseName + newNumber;
                        }
                        // Clear all input values first to prevent copying values from the first card
                        input.value = '';
                    });

                    // Update labels to reference new IDs
                    const labels = newItem.querySelectorAll('label');
                    labels.forEach(label => {
                        const forAttr = label.getAttribute('for');
                        if (forAttr) {
                            const baseName = forAttr.replace(/\d+$/, '');
                            label.setAttribute('for', baseName + newNumber);
                        }
                    });

                    // Update the number indicator
                    newItem.querySelector('.monitoring-number-indicator').textContent = `#${newNumber}`;

                    // Set input values using ID selectors to ensure we target the correct inputs
                    const objectivesInput = newItem.querySelector(`#objectives${newNumber}`);
                    if (objectivesInput) objectivesInput.value = objectives;

                    const performanceIndicatorsInput = newItem.querySelector(`#performance_indicators${newNumber}`);
                    if (performanceIndicatorsInput) performanceIndicatorsInput.value = performanceIndicators;

                    const baselineDataInput = newItem.querySelector(`#baseline_data${newNumber}`);
                    if (baselineDataInput) baselineDataInput.value = baselineData;

                    const performanceTargetInput = newItem.querySelector(`#performance_target${newNumber}`);
                    if (performanceTargetInput) performanceTargetInput.value = performanceTarget;

                    const dataSourceInput = newItem.querySelector(`#data_source${newNumber}`);
                    if (dataSourceInput) dataSourceInput.value = dataSource;

                    const collectionMethodInput = newItem.querySelector(`#collection_method${newNumber}`);
                    if (collectionMethodInput) collectionMethodInput.value = collectionMethod;

                    const frequencyInput = newItem.querySelector(`#frequency${newNumber}`);
                    if (frequencyInput) frequencyInput.value = frequency;

                    const responsibleInput = newItem.querySelector(`#persons_involved${newNumber}`);
                    if (responsibleInput) responsibleInput.value = responsible;

                    // Show the remove button
                    const removeBtn = newItem.querySelector('.remove-monitoring');
                    removeBtn.style.display = 'block';

                    // Add event listeners to inputs in the new item
                    inputs.forEach(input => {
                        input.addEventListener('input', function() {
                            if (validationTriggered) {
                                validateSection('section-11');
                            } else {
                                updateMonitoringSectionStatus();
                            }
                        });
                    });

                    // Add event listener to remove button
                    removeBtn.addEventListener('click', function() {
                        container.removeChild(newItem);

                        // Update numbering of remaining items
                        updateMonitoringItemNumbering();

                        // Re-validate if validation has been triggered
                        if (validationTriggered) {
                            validateSection('section-11');
                        }

                        // Update completion status
                        updateMonitoringSectionStatus();
                    });

                    // Append to the container
                    container.appendChild(newItem);

                    // Mark the section validation status
                    const navItem = document.querySelector('.form-nav-item[data-section="section-11"]');
                    if (navItem && !objectives && !performanceIndicators) {
                        // Only mark incomplete if this is an empty item (no initial values)
                        navItem.classList.remove('is-complete');
                    }

                    // If validation has been triggered, validate the section again
                    if (validationTriggered) {
                        validateSection('section-11');
                    }

                    return newItem;
                };

                // Add button click event - add a new empty monitoring item
                addBtn.addEventListener('click', function() {
                    addMonitoringItem();
                });

                // Initial check for completion status
                updateMonitoringSectionStatus();
            }

            // Helper function to update monitoring item numbering after removal
            function updateMonitoringItemNumbering() {
                const container = document.getElementById('monitoring-items-container');
                if (!container) return;

                const items = container.querySelectorAll('.monitoring-item');
                items.forEach((item, index) => {
                    // Update number indicator
                    const numberIndicator = item.querySelector('.monitoring-number-indicator');
                    if (numberIndicator) {
                        numberIndicator.textContent = `#${index + 1}`;
                    }

                    // Update input IDs and labels
                    const inputs = item.querySelectorAll('textarea, input');
                    inputs.forEach(input => {
                        const oldId = input.id;
                        if (oldId) {
                            const baseName = oldId.replace(/\d+$/, '');
                            input.id = baseName + (index + 1);
                        }
                    });

                    const labels = item.querySelectorAll('label');
                    labels.forEach(label => {
                        const forAttr = label.getAttribute('for');
                        if (forAttr) {
                            const baseName = forAttr.replace(/\d+$/, '');
                            label.setAttribute('for', baseName + (index + 1));
                        }
                    });

                    // Show/hide remove button for first input
                    const removeBtn = item.querySelector('.remove-monitoring');
                    if (removeBtn) {
                        removeBtn.style.display = index === 0 && items.length === 1 ? 'none' : 'block';
                    }
                });
            }

            // Function to validate and update monitoring section status
            function updateMonitoringSectionStatus() {
                const section = document.getElementById('section-11');
                const navItem = document.querySelector('.form-nav-item[data-section="section-11"]');

                if (!section || !navItem) return false;

                // Check all required fields in the section
                const allInputs = section.querySelectorAll('textarea[required], input[required]');
                let allFieldsFilled = true;

                allInputs.forEach(input => {
                    if (!input.value.trim()) {
                        allFieldsFilled = false;

                        // If validation has been triggered, mark this field as invalid
                        if (validationTriggered) {
                            markAsInvalid(input, 'This field is required');
                        }
                    } else {
                        markAsValid(input);
                    }
                });

                // Update navigation status based on validation result
                if (validationTriggered) {
                    if (allFieldsFilled) {
                        navItem.classList.remove('has-error');
                        navItem.classList.add('is-complete');

                        // Remove error from section title
                        const sectionTitle = section.querySelector('.section-title');
                        if (sectionTitle) {
                            sectionTitle.classList.remove('has-error');
                        }
                    } else {
                        navItem.classList.add('has-error');
                        navItem.classList.remove('is-complete');

                        // Add error to section title
                        const sectionTitle = section.querySelector('.section-title');
                        if (sectionTitle) {
                            sectionTitle.classList.add('has-error');
                        }
                    }
                } else if (allFieldsFilled) {
                    navItem.classList.add('is-complete');
                } else {
                    navItem.classList.remove('is-complete');
                }

                return allFieldsFilled;
            }

            // Add validation for section-11 to the validateSection function
            const originalValidateSection = validateSection;
            validateSection = function(sectionId) {
                if (sectionId === 'section-11') {
                    return updateMonitoringSectionStatus();
                }
                return originalValidateSection(sectionId);
            };

            // Autocomplete functionality for program, project, and activity fields
            function initializeAutocomplete() {
                const autocompleteFields = document.querySelectorAll('.autocomplete-input');

                autocompleteFields.forEach(input => {
                    const field = input.getAttribute('data-field');
                    const suggestionsContainer = document.getElementById(`${field}-suggestions`);

                    let currentFocus = -1;
                    let typingTimer;
                    const doneTypingInterval = 300; // Wait for 300ms after user stops typing

                    // Add input event listener
                    input.addEventListener('input', function() {
                        clearTimeout(typingTimer);

                        // Hide dropdown if input is empty
                        if (!this.value) {
                            suggestionsContainer.style.display = 'none';
                            return;
                        }

                        // Set a timer to fetch suggestions after user stops typing
                        typingTimer = setTimeout(() => {
                            fetchSuggestions(this.value, field, suggestionsContainer);

                            // For activity field, check for duplicates
                            if (field === 'activity' && this.value.trim() !== '') {
                                checkDuplicateActivityWithEditMode(this.value.trim());

                                // Ensure dropdown is visible in edit mode even with partial typing
                                if (editMode && editingEntryId) {
                                    suggestionsContainer.style.display = 'block';
                                }
                            }
                        }, doneTypingInterval);
                    });

                    // Handle keyboard navigation
                    input.addEventListener('keydown', function(e) {
                        const items = suggestionsContainer.getElementsByClassName('autocomplete-item');
                        if (items.length === 0) return;

                        // Get only non-duplicate items
                        const selectableItems = Array.from(items).filter(item => !item.classList.contains('duplicate-item'));
                        if (selectableItems.length === 0) return;

                        // Down arrow
                        if (e.keyCode === 40) {
                            currentFocus++;
                            setActive(items, currentFocus);
                            e.preventDefault(); // Prevent cursor from moving
                        }
                        // Up arrow
                        else if (e.keyCode === 38) {
                            currentFocus--;
                            setActive(items, currentFocus);
                            e.preventDefault(); // Prevent cursor from moving
                        }
                        // Enter key
                        else if (e.keyCode === 13 && currentFocus > -1) {
                            if (currentFocus < selectableItems.length) {
                                input.value = selectableItems[currentFocus].textContent;
                                suggestionsContainer.style.display = 'none';
                                currentFocus = -1;
                                e.preventDefault(); // Prevent form submission

                                // For activity field, check for duplicates after selection
                                if (field === 'activity') {
                                    checkDuplicateActivityWithEditMode(input.value.trim());
                                }
                            }
                        }
                        // Escape key
                        else if (e.keyCode === 27) {
                            suggestionsContainer.style.display = 'none';
                            currentFocus = -1;
                        }
                    });

                    // Close dropdown when clicking outside
                    document.addEventListener('click', function(e) {
                        if (e.target !== input) {
                            suggestionsContainer.style.display = 'none';
                            currentFocus = -1;
                        }
                    });

                    // Set active item in dropdown
                    function setActive(items, index) {
                        if (!items || items.length === 0) return;

                        // Get only non-duplicate items
                        const selectableItems = Array.from(items).filter(item => !item.classList.contains('duplicate-item'));
                        if (selectableItems.length === 0) return;

                        // Clear all selected items first
                        for (let i = 0; i < items.length; i++) {
                            items[i].classList.remove('selected');
                        }

                        // Adjust index if out of bounds
                        if (index >= selectableItems.length) currentFocus = 0;
                        if (index < 0) currentFocus = selectableItems.length - 1;

                        // Add selected class to current item
                        selectableItems[currentFocus].classList.add('selected');

                        // Scroll to selected item if needed
                        selectableItems[currentFocus].scrollIntoView({
                            block: 'nearest'
                        });
                    }
                });
            }

            // Function to check for duplicate activities
            function checkDuplicateActivity(activityValue) {
                const activityInput = document.getElementById('activity');
                const errorDiv = document.getElementById('activity-error');

                // Reset validation state
                activityInput.classList.remove('is-invalid');
                errorDiv.textContent = ''; // Clear error message text

                // Don't check if empty
                if (!activityValue) {
                    activityInput.dataset.isDuplicate = 'false';
                    // Update gender issue section status
                    updateSectionStatus('gender-issue', checkAllFieldsFilled('gender-issue'));
                    return;
                }

                // Check if activity already exists
                fetch(`check_duplicate_activity.php?activity=${encodeURIComponent(activityValue)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.exists) {
                            // Mark as invalid if activity exists
                            activityInput.classList.add('is-invalid');
                            errorDiv.textContent = data.message || 'This activity already exists.';

                            // Store the duplicate status on the input for form validation
                            activityInput.dataset.isDuplicate = 'true';

                            // Mark gender issue section as incomplete
                            const navItem = document.querySelector('.form-nav-item[data-section="gender-issue"]');
                            if (navItem) {
                                navItem.classList.remove('is-complete');
                                if (validationTriggered) {
                                    navItem.classList.add('has-error');

                                    // Add error to section title
                                    const sectionTitle = document.querySelector('#gender-issue .section-title');
                                    if (sectionTitle) {
                                        sectionTitle.classList.add('has-error');
                                    }
                                }
                            }
                        } else {
                            // Clear any previous error
                            activityInput.classList.remove('is-invalid');
                            errorDiv.textContent = ''; // Ensure error message is cleared
                            activityInput.dataset.isDuplicate = 'false';

                            // Update gender issue section status
                            updateSectionStatus('gender-issue', checkAllFieldsFilled('gender-issue'));
                        }
                    })
                    .catch(error => {
                        console.error('Error checking duplicate activity:', error);
                        // Also clear error on exception
                        activityInput.classList.remove('is-invalid');
                        errorDiv.textContent = '';
                        activityInput.dataset.isDuplicate = 'false';

                        // Update gender issue section status
                        updateSectionStatus('gender-issue', checkAllFieldsFilled('gender-issue'));
                    });
            }

            // Function to fetch suggestions from server
            function fetchSuggestions(query, field, container) {
                // Build URL with edit mode consideration
                let url = `get_autocomplete_suggestions.php?field=${field}&query=${encodeURIComponent(query)}`;

                // If in edit mode and it's the activity field, include the current entry ID
                // Also include current value to help identify it in the dropdown
                const currentInput = document.getElementById(field);
                if (field === 'activity' && editMode && editingEntryId) {
                    url += `&id=${editingEntryId}`;
                    if (currentInput && currentInput.value) {
                        url += `&current_value=${encodeURIComponent(currentInput.value.trim())}`;
                    }
                }

                // Fetch suggestions from server using AJAX
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        // Clear the container
                        container.innerHTML = '';

                        if (data.success && data.suggestions && data.suggestions.length > 0) {
                            // Create and append suggestion items
                            data.suggestions.forEach(suggestion => {
                                const item = document.createElement('div');
                                item.className = 'autocomplete-item';

                                // Handle simple string or object with value and isDuplicate
                                const value = typeof suggestion === 'string' ? suggestion : suggestion.value;
                                const isDuplicate = suggestion.isDuplicate || false;
                                const isCurrent = suggestion.isCurrent || false;

                                // Add "(Current)" label for the activity being edited
                                if (isCurrent && field === 'activity' && editMode) {
                                    const valueSpan = document.createElement('span');
                                    valueSpan.textContent = value;

                                    const currentLabel = document.createElement('span');
                                    currentLabel.textContent = ' (Current)';
                                    currentLabel.style.fontStyle = 'italic';
                                    currentLabel.style.color = '#6a1b9a';
                                    currentLabel.style.fontWeight = 'bold';

                                    item.appendChild(valueSpan);
                                    item.appendChild(currentLabel);
                                } else {
                                    item.textContent = value;
                                }

                                // Apply styling for duplicate activities
                                if (field === 'activity' && isDuplicate) {
                                    item.style.color = 'red';
                                    item.style.fontStyle = 'italic';
                                    item.style.pointerEvents = 'none';
                                    item.classList.add('duplicate-item');
                                } else {
                                    // Add click handler only for non-duplicate items
                                    item.addEventListener('click', function() {
                                        const input = document.getElementById(field);
                                        input.value = value;
                                        container.style.display = 'none';

                                        // For activity field, check for duplicates after selection
                                        if (field === 'activity') {
                                            checkDuplicateActivityWithEditMode(value);
                                        }
                                    });
                                }

                                container.appendChild(item);
                            });

                            // Show the container
                            container.style.display = 'block';
                        } else {
                            // Hide container if no suggestions
                            container.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching suggestions:', error);
                        container.style.display = 'none';
                    });
            }

            // Initialize autocomplete when the page loads
            document.addEventListener('DOMContentLoaded', function() {
                initializeAutocomplete();

                // Add direct input handler for the activity field to clear errors immediately when empty
                const activityInput = document.getElementById('activity');
                const activityErrorDiv = document.getElementById('activity-error');

                if (activityInput && activityErrorDiv) {
                    activityInput.addEventListener('input', function() {
                        // If the input is empty, immediately clear the error message
                        if (!this.value.trim()) {
                            activityInput.classList.remove('is-invalid');
                            activityErrorDiv.textContent = '';
                            activityInput.dataset.isDuplicate = 'false';
                        } else {
                            // Use the edit-mode-aware duplicate check
                            checkDuplicateActivityWithEditMode(this.value.trim());
                        }
                    });
                }

                // Add validation for activity duplication when navigating between sections
                const navigationButtons = document.querySelectorAll('.btn-form-nav');
                navigationButtons.forEach(button => {
                    const originalClickHandler = button.onclick;

                    button.onclick = async function(e) {
                        // If we're navigating away from the gender issue section
                        const currentSection = this.closest('.form-section');
                        if (currentSection && currentSection.id === 'gender-issue') {
                            const activityInput = document.getElementById('activity');

                            if (activityInput && activityInput.value.trim() !== '') {
                                // Prevent default navigation
                                e.preventDefault();
                                e.stopPropagation();

                                // Check for duplicates using the enhanced function
                                try {
                                    const isDuplicate = await checkDuplicateActivityWithEditMode(activityInput.value.trim());

                                    if (isDuplicate) {
                                        // Scroll to activity field
                                        activityInput.scrollIntoView({
                                            behavior: 'smooth',
                                            block: 'center'
                                        });

                                        // Show error message
                                        Swal.fire({
                                            title: 'Duplicate Activity',
                                            text: 'The activity you entered already exists in your campus database. Please provide a unique activity.',
                                            icon: 'error',
                                            confirmButtonColor: '#6a1b9a',
                                            backdrop: `
                                                rgba(0,0,0,0.7)
                                                url()
                                                center
                                                no-repeat
                                            `,
                                            customClass: {
                                                container: 'swal-blur-container'
                                            }
                                        });
                                        return; // Prevent navigation
                                    }
                                } catch (error) {
                                    console.error('Error checking duplicate activity:', error);
                                }
                            }
                        }

                        // If we reached here, continue with the original navigation
                        const targetSection = this.getAttribute('data-navigate-to');
                        if (targetSection) {
                            // Show the target section
                            document.querySelectorAll('.form-section').forEach(section => {
                                section.classList.remove('active');
                            });
                            document.getElementById(targetSection).classList.add('active');

                            // Update the navigation
                            document.querySelectorAll('.form-nav-item').forEach(item => {
                                item.classList.remove('active');
                            });
                            document.querySelector(`.form-nav-item[data-section="${targetSection}"]`).classList.add('active');

                            // Scroll to top of the section
                            document.getElementById(targetSection).scrollIntoView({
                                behavior: 'smooth'
                            });
                        }
                    };
                });
            });
        </script>

        <!-- PPAS Entries Modal -->
        <div class="modal fade" id="ppasEntriesModal" tabindex="-1" aria-labelledby="ppasEntriesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ppasEntriesModalLabel">PPAS Entries</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Filters -->
                        <div class="filters-container">
                            <div class="row g-3">
                                <div class="col-md">
                                    <label for="filterGenderIssue" class="form-label">Gender Issue</label>
                                    <input type="text" class="form-control" id="filterGenderIssue" placeholder="Search gender issue...">
                                </div>

                                <div class="col-md">
                                    <label for="filterActivity" class="form-label">Activity</label>
                                    <input type="text" class="form-control" id="filterActivity" placeholder="Search activity...">
                                </div>

                                <div class="col-md">
                                    <label for="filterYear" class="form-label">Year</label>
                                    <select class="form-select" id="filterYear">
                                        <option value="">All Years</option>
                                        <!-- Will be populated dynamically -->
                                    </select>
                                </div>

                                <div class="col-md">
                                    <label for="filterQuarter" class="form-label">Quarter</label>
                                    <select class="form-select" id="filterQuarter">
                                        <option value="">All Quarters</option>
                                        <option value="Q1">Q1</option>
                                        <option value="Q2">Q2</option>
                                        <option value="Q3">Q3</option>
                                        <option value="Q4">Q4</option>
                                    </select>
                                </div>

                                <div class="col-md">
                                    <label for="filterCampus" class="form-label">Campus</label>
                                    <input type="text" class="form-control" id="displayCampus" value="<?php echo $userCampus; ?>" readonly>
                                    <input type="hidden" id="filterCampus" value="<?php echo $userCampus; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-container mt-3">
                            <table class="table table-hover table-bordered">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Year</th>
                                        <th>Quarter</th>
                                        <th>Gender Issue</th>
                                        <th>Program</th>
                                        <th>Project</th>
                                        <th>Activity</th>
                                    </tr>
                                </thead>
                                <tbody id="ppasEntriesTableBody">
                                    <!-- Data will be populated here dynamically -->
                                </tbody>
                            </table>

                            <!-- No results message -->
                            <div id="noResultsMessage" class="text-center py-4 d-none">
                                <div class="mb-3">
                                    <i class="fas fa-search fa-3x"></i>
                                </div>
                                <h5>No PPAS entries found</h5>
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
                                <ul class="pagination justify-content-center" id="entriesPagination">
                                    <!-- Pagination will be populated dynamically -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of PPAS Entries Modal -->
        <script>
            (function() {
                // Global variables for the modal
                let ppasModal;
                let currentMode = 'view'; // Can be 'view', 'edit', or 'delete'
                let currentPage = 1;
                const rowsPerPage = 5; // Show 5 rows per page as requested
                let allEntries = []; // To store all entries for pagination

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
                function showPpasEntriesModal(mode) {
                    currentMode = mode;
                    currentPage = 1;

                    // Update modal title based on mode
                    const modalTitle = document.getElementById('ppasEntriesModalLabel');
                    switch (mode) {
                        case 'view':
                            modalTitle.textContent = 'View PPAs Entries';
                            break;
                        case 'edit':
                            modalTitle.textContent = 'Edit PPAs Entry';
                            break;
                        case 'delete':
                            modalTitle.textContent = 'Delete PPAs Entry';
                            break;
                    }

                    // Initialize Bootstrap modal if not already done
                    if (!ppasModal) {
                        ppasModal = new bootstrap.Modal(document.getElementById('ppasEntriesModal'));

                        // Load years for filter dropdown when modal is shown
                        document.getElementById('ppasEntriesModal').addEventListener('shown.bs.modal', function() {
                            loadYears();
                        });

                        // Set up event listeners for filter changes
                        document.getElementById('filterGenderIssue').addEventListener('input', debounce(loadPpasEntries, 300));
                        document.getElementById('filterActivity').addEventListener('input', debounce(loadPpasEntries, 300));
                        document.getElementById('filterYear').addEventListener('change', loadPpasEntries);
                        document.getElementById('filterQuarter').addEventListener('change', loadPpasEntries);
                    }

                    // Initial data load
                    loadPpasEntries();

                    // Show the modal
                    ppasModal.show();
                }

                // Function to load available years from ppas_forms table
                function loadYears() {
                    const yearSelect = document.getElementById('filterYear');

                    // Skip if years are already loaded
                    if (yearSelect.options.length > 1) return;

                    // Show loading in the dropdown
                    yearSelect.innerHTML = '<option value="">All Years</option><option value="" disabled>Loading years...</option>';

                    fetch('get_ppas_years.php')
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

                // Function to load PPAS entries based on filters
                function loadPpasEntries() {
                    const tableBody = document.getElementById('ppasEntriesTableBody');
                    const noResultsMessage = document.getElementById('noResultsMessage');
                    const loadingIndicator = document.getElementById('loadingIndicator');
                    const pagination = document.getElementById('entriesPagination');
                    const table = document.querySelector('.table');

                    // Always reset to page 1 when applying filters
                    currentPage = 1;

                    // Get filter values
                    const campus = document.getElementById('filterCampus').value;
                    const year = document.getElementById('filterYear').value;
                    const quarter = document.getElementById('filterQuarter').value;
                    const genderIssue = document.getElementById('filterGenderIssue').value;
                    const activity = document.getElementById('filterActivity').value;

                    // Show loading state
                    tableBody.innerHTML = '';
                    pagination.innerHTML = '';

                    table.classList.add('d-none');
                    noResultsMessage.classList.add('d-none');
                    loadingIndicator.classList.remove('d-none');

                    // Build query string
                    const queryParams = new URLSearchParams();
                    if (campus) queryParams.append('campus', campus);
                    if (year) queryParams.append('year', year);
                    if (quarter) queryParams.append('quarter', quarter);
                    if (genderIssue) queryParams.append('gender_issue', genderIssue);
                    if (activity) queryParams.append('activity', activity)

                    // Fetch data
                    fetch(`get_ppas_entries.php?${queryParams.toString()}`)
                        .then(response => response.json())
                        .then(data => {
                            loadingIndicator.classList.add('d-none');
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
                            loadingIndicator.classList.add('d-none');
                            noResultsMessage.classList.remove('d-none');
                        });
                }

                // Function to display entries for current page
                function displayEntries(page) {
                    const tableBody = document.getElementById('ppasEntriesTableBody');
                    const startIndex = (page - 1) * rowsPerPage;
                    const endIndex = Math.min(startIndex + rowsPerPage, allEntries.length);
                    const entriesSlice = allEntries.slice(startIndex, endIndex);

                    // Clear the table
                    tableBody.innerHTML = '';

                    // Display entries for the current page
                    entriesSlice.forEach(entry => {
                        const row = document.createElement('tr');
                        row.setAttribute('data-id', entry.id);

                        // Add row cells for each column
                        row.innerHTML = `
                <td>${entry.year}</td>
                <td>${entry.quarter}</td>
                <td>${entry.gender_issue || 'N/A'}</td>
                <td>${entry.program}</td>
                <td>${entry.project}</td>
                <td>${entry.activity}</td>
            `;

                        // Add click event listener based on mode
                        row.addEventListener('click', function() {
                            const entryId = this.getAttribute('data-id');

                            // Placeholder for future functionality
                            switch (currentMode) {
                                case 'view':
                                    console.log('View entry ID:', entryId);
                                    break;
                                case 'edit':
                                    fetch('get_ppas_entry.php?id=' + entryId)
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success && data.data) {
                                                enterEditMode(data.data);
                                                const modal = bootstrap.Modal.getInstance(document.getElementById('ppasEntriesModal'));
                                                if (modal) modal.hide();
                                            } else {
                                                Swal.fire({
                                                    title: 'Error',
                                                    text: data.message || 'Could not fetch entry data',
                                                    icon: 'error',
                                                    confirmButtonColor: '#6a1b9a'
                                                });
                                            }
                                        });
                                    break;
                                case 'delete':
                                    // Create details HTML with modern styling
                                    const detailsHtml = `
        <div class="ppas-delete-details">
            <div class="detail-row">
                <span class="detail-label">Year:</span>
                <span class="detail-value">${entry.year}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Quarter:</span>
                <span class="detail-value">${entry.quarter}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Gender Issue:</span>
                <span class="detail-value">${entry.gender_issue || 'N/A'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Program:</span>
                <span class="detail-value">${entry.program}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Project:</span>
                <span class="detail-value">${entry.project}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Activity:</span>
                <span class="detail-value">${entry.activity}</span>
            </div>
        </div>
    `;

                                    // Custom styling for the SweetAlert
                                    const customStyle = `
        <style>
            .ppas-delete-details {
                text-align: left;
                background: rgba(0,0,0,0.03);
                padding: 20px;
                border-radius: 10px;
                margin: 15px 0;
                border-left: 4px solid var(--accent-color);
            }
            .detail-row {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                border-bottom: 1px solid rgba(0,0,0,0.05);
            }
            .detail-row:last-child {
                border-bottom: none;
            }
            .detail-label {
                font-weight: 600;
                color: #555;
            }
            .detail-value {
                color: var(--accent-color);
                font-weight: 500;
            }
            .swal2-confirm {
                background-color: var(--accent-color) !important;
            }
            .swal2-title {
                color: #333 !important;
                font-size: 1.5rem !important;
            }
        </style>
    `;

                                    Swal.fire({
                                        title: 'Delete PPAS Entry?',
                                        html: customStyle + detailsHtml,
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Yes, delete it',
                                        cancelButtonText: 'Cancel',
                                        confirmButtonColor: '#9c27b0',
                                        cancelButtonColor: '#6c757d',
                                        backdrop: `rgba(0,0,0,0.5)`,
                                        customClass: {
                                            container: 'swal-blur-container',
                                            popup: 'delete-swal-popup',
                                            title: 'delete-swal-title',
                                            content: 'delete-swal-content'
                                        }
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // Make the delete request directly without showing loading
                                            fetch(`delete_ppas_entry.php?id=${entryId}`, {
                                                    method: 'DELETE',
                                                })
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.success) {
                                                        // Show success with auto-close and progress bar
                                                        Swal.fire({
                                                            title: 'Deleted!',
                                                            text: 'The PPAS entry has been deleted successfully.',
                                                            icon: 'success',
                                                            showConfirmButton: false, // No OK button
                                                            timer: 1500,
                                                            timerProgressBar: true,
                                                            backdrop: `rgba(0,0,0,0.5)`,
                                                            customClass: {
                                                                container: 'swal-blur-container',
                                                                popup: 'success-swal-popup',
                                                                title: 'success-swal-title'
                                                            },
                                                        }).then(() => {
                                                            // Reload the data after auto-close
                                                            loadPpasEntries();
                                                        });
                                                    } else {
                                                        Swal.fire({
                                                            title: 'Error!',
                                                            text: data.message || 'Failed to delete the entry',
                                                            icon: 'error',
                                                            confirmButtonColor: '#9c27b0',
                                                            customClass: {
                                                                container: 'swal-blur-container'
                                                            }
                                                        });
                                                    }
                                                })
                                                .catch(error => {
                                                    console.error('Error:', error);
                                                    Swal.fire({
                                                        title: 'Error!',
                                                        text: 'An error occurred while deleting the entry',
                                                        icon: 'error',
                                                        confirmButtonColor: '#9c27b0',
                                                        customClass: {
                                                            container: 'swal-blur-container'
                                                        }
                                                    });
                                                });
                                        }
                                    });
                                    break;
                            }
                        });

                        tableBody.appendChild(row);
                    });
                }

                // Function to display pagination
                function displayPagination(totalEntries) {
                    const pagination = document.getElementById('entriesPagination');
                    const totalPages = Math.ceil(totalEntries / rowsPerPage);

                    // Clear pagination
                    pagination.innerHTML = '';

                    // Previous page button
                    const prevLi = document.createElement('li');
                    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                    prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
                    prevLi.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (currentPage > 1) {
                            currentPage--;
                            updatePagination();
                            displayEntries(currentPage);
                        }
                    });
                    pagination.appendChild(prevLi);

                    // Page numbers
                    for (let i = 1; i <= totalPages; i++) {
                        const pageLi = document.createElement('li');
                        pageLi.className = `page-item ${currentPage === i ? 'active' : ''}`;
                        pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                        pageLi.addEventListener('click', function(e) {
                            e.preventDefault();
                            currentPage = i;
                            updatePagination();
                            displayEntries(currentPage);
                        });
                        pagination.appendChild(pageLi);
                    }

                    // Next page button
                    const nextLi = document.createElement('li');
                    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                    nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
                    nextLi.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (currentPage < totalPages) {
                            currentPage++;
                            updatePagination();
                            displayEntries(currentPage);
                        }
                    });
                    pagination.appendChild(nextLi);
                }

                // Function to update pagination active state
                function updatePagination() {
                    const pagination = document.getElementById('entriesPagination');
                    const totalPages = Math.ceil(allEntries.length / rowsPerPage);

                    // Update active state for page items
                    const pageItems = pagination.querySelectorAll('.page-item');
                    pageItems.forEach((item, index) => {
                        if (index === 0) {
                            // Previous button
                            item.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                        } else if (index === pageItems.length - 1) {
                            // Next button
                            item.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                        } else {
                            // Page numbers
                            item.className = `page-item ${currentPage === index ? 'active' : ''}`;
                        }
                    });
                }

                // Add event listeners to buttons to open the modal
                document.addEventListener('DOMContentLoaded', function() {
                    // View button
                    document.getElementById('viewBtn').addEventListener('click', function() {
                        showPpasEntriesModal('view');
                    });

                    // Edit button
                    document.getElementById('editBtn').addEventListener('click', function() {
                        showPpasEntriesModal('edit');
                    });

                    // Delete button
                    document.getElementById('deleteBtn').addEventListener('click', function() {
                        showPpasEntriesModal('delete');
                    });
                });

                // Expose the showPpasEntriesModal function globally for direct calls
                window.showPpasEntriesModal = showPpasEntriesModal;
            })();
        </script>
        <script>
            // === BEGIN EDIT MODE LOGIC ===
            let editMode = false;
            let editingEntryId = null;
            let originalFormState = null;

            function saveFormState() {
                const form = document.getElementById('ppasForm');
                return new FormData(form);
            }

            function restoreFormState(formData) {
                const form = document.getElementById('ppasForm');
                for (let [key, value] of formData.entries()) {
                    const field = form.elements[key];
                    if (field) field.value = value;
                }
            }

            function resetForm() {
                const form = document.getElementById('ppasForm');
                form.reset();

                // Clear all <select> elements (single and multi-select)
                form.querySelectorAll('select').forEach(select => {
                    select.selectedIndex = 0; // Set to first option (usually placeholder)
                    // For multi-selects, clear all selections
                    if (select.multiple) {
                        Array.from(select.options).forEach(option => option.selected = false);
                    }
                });

                // Reset Project Team section
                resetProjectTeamSection();

                // Reset Office and Programs section
                resetOfficeAndProgramsSection();

                // Reset Program Description section fields
                resetProgramDescriptionSection();

                // If you use any custom dropdown plugins (like Select2, Choices.js, etc.), also call their reset methods here
                // Example for Select2:
                // $(form).find('select').val(null).trigger('change');
            }

            // Function to reset the Program Description Section dynamic fields
            function resetProgramDescriptionSection() {
                console.log('Resetting Program Description Section dynamic fields');

                // Reset all dynamic array fields
                resetArrayField('specific_objectives');
                resetArrayField('strategies');
                resetArrayField('expected_output');
                resetArrayField('specific_plans');
            }

            // Function to reset a specific array field in the form
            function resetArrayField(fieldName) {
                const container = document.getElementById(`${fieldName}_container`);
                if (!container) {
                    console.error(`Container for ${fieldName} not found`);
                    return;
                }

                console.log(`Resetting ${fieldName} container`);

                // Keep only the first input group and remove any additional ones
                const inputGroups = container.querySelectorAll('.input-group');
                if (inputGroups.length > 0) {
                    // Keep the first input group
                    const firstInputGroup = inputGroups[0];

                    // Clear the value of the first input
                    const input = firstInputGroup.querySelector(`input[name="${fieldName}[]"]`);
                    if (input) {
                        input.value = '';
                    }

                    // Hide the remove button
                    const removeBtn = firstInputGroup.querySelector('.remove-input');
                    if (removeBtn) {
                        removeBtn.style.display = 'none';
                    }

                    // Remove all other input groups
                    for (let i = 1; i < inputGroups.length; i++) {
                        container.removeChild(inputGroups[i]);
                    }
                }
            }

            // Function to reset the project team section completely
            function resetProjectTeamSection() {
                console.log('Resetting project team section');

                // Reset Project Leaders
                resetTeamMemberSection('projectLeadersContainer', 'Project Leader', 'leader');

                // Reset Assistant Project Leaders
                resetTeamMemberSection('assistantLeadersContainer', 'Assistant Project Leader', 'asst_leader');

                // Reset Project Staff
                resetTeamMemberSection('projectStaffContainer', 'Project Staff/Coordinator', 'staff');
            }

            // Function to reset Office and Programs section
            function resetOfficeAndProgramsSection() {
                console.log('Resetting Office and Programs section');

                // Reset Offices
                resetInputContainer('officeInputsContainer', 'offices[]');

                // Reset Programs
                resetInputContainer('programInputsContainer', 'programs[]');
            }

            // Function to reset a generic input container with numbered inputs
            function resetInputContainer(containerId, inputName) {
                const container = document.getElementById(containerId);
                if (!container) return;

                console.log(`Resetting ${containerId}`);

                // Keep only the first input group and remove any additional ones
                const inputGroups = container.querySelectorAll('.input-group');
                if (inputGroups.length > 0) {
                    // Keep the first input group
                    const firstInputGroup = inputGroups[0];

                    // Clear the value of the first input
                    const input = firstInputGroup.querySelector(`input[name="${inputName}"]`);
                    if (input) {
                        input.value = '';
                    }

                    // Hide the remove button
                    const removeBtn = firstInputGroup.querySelector('.remove-input');
                    if (removeBtn) {
                        removeBtn.style.display = 'none';
                    }

                    // Remove all other input groups
                    for (let i = 1; i < inputGroups.length; i++) {
                        container.removeChild(inputGroups[i]);
                    }
                }
            }

            // Function to reset a specific team member section
            function resetTeamMemberSection(containerId, titleText, role) {
                const container = document.getElementById(containerId);
                if (!container) return;

                console.log(`Resetting ${titleText} section`);

                // Keep only the first card and remove any additional ones
                const cards = container.querySelectorAll('.team-member-card');
                if (cards.length > 0) {
                    // Keep the first card
                    const firstCard = cards[0];

                    // Remove all other cards
                    for (let i = 1; i < cards.length; i++) {
                        container.removeChild(cards[i]);
                    }

                    // Reset the first card's fields
                    const inputs = firstCard.querySelectorAll('input[type="text"]');
                    inputs.forEach(input => {
                        input.value = '';
                        // Remove any personnel ID data attribute
                        if (input.classList.contains('personnel-autocomplete')) {
                            input.removeAttribute('data-personnel-id');
                        }
                    });

                    // Reset tasks - keep only the first task input and remove others
                    const tasksContainer = firstCard.querySelector('.tasks-container');
                    if (tasksContainer) {
                        const taskInputGroups = tasksContainer.querySelectorAll('.input-group');
                        if (taskInputGroups.length > 0) {
                            // Keep only the first task input group
                            const firstTaskGroup = taskInputGroups[0];

                            // Clear its value
                            const taskInput = firstTaskGroup.querySelector('input');
                            if (taskInput) {
                                taskInput.value = '';
                            }

                            // Remove all other task input groups
                            for (let i = 1; i < taskInputGroups.length; i++) {
                                tasksContainer.removeChild(taskInputGroups[i]);
                            }

                            // Hide the remove button in the first task input
                            const removeBtn = firstTaskGroup.querySelector('.remove-input');
                            if (removeBtn) {
                                removeBtn.style.display = 'none';
                            }
                        }
                    }

                    // Hide the remove team member button
                    const removeTeamMemberBtn = firstCard.querySelector('.remove-team-member');
                    if (removeTeamMemberBtn) {
                        removeTeamMemberBtn.style.display = 'none';
                    }
                }
            }

            function populateForm(entry) {
                // Set simple fields
                const set = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.value = val ?? '';
                };
                set('year', entry.year);
                set('quarter', entry.quarter);
                set('program', entry.program);
                set('project', entry.project);
                set('activity', entry.activity);
                set('locationVenue', entry.location);
                set('startTime', entry.start_time);
                set('endTime', entry.end_time);
                set('totalDuration', entry.total_duration);
                set('modeOfDelivery', entry.mode_of_delivery);
                set('rationale', entry.rationale);
                set('general_objectives', entry.general_objectives);
                set('description', entry.description);
                set('functional_requirements', entry.functional_requirements);
                set('sustainability_plan', entry.sustainability_plan);
                set('approvedBudget', entry.approved_budget);
                set('financialNote', entry.financial_note);
                set('psAttribution', entry.ps_attribution);

                // Populate monitoring section
                populateMonitoringSection(entry);

                // Set financial plan radio button
                console.log('Setting financial plan radio button. Value from DB:', entry.financial_plan);

                // The database field is 'financial_plan', not 'has_financial_plan'
                if (entry.financial_plan !== undefined) {
                    // Check the appropriate radio button based on the database value
                    if (entry.financial_plan == 1) {
                        document.getElementById('withFinancialPlan').checked = true;
                        console.log('Setting WITH financial plan');
                    } else {
                        document.getElementById('withoutFinancialPlan').checked = true;
                        console.log('Setting WITHOUT financial plan');
                    }

                    // Trigger the change event to update visibility of financial plan section
                    const changeEvent = new Event('change', {
                        bubbles: true
                    });
                    if (document.getElementById('withFinancialPlan').checked) {
                        document.getElementById('withFinancialPlan').dispatchEvent(changeEvent);

                        // Define local versions of the calculation functions at the appropriate scope level
                        // Define a local version of calculateTotalCost for use in populateForm
                        const calculateTotalCostLocal = () => {
                            let grandTotal = 0;

                            // Sum up all item totals
                            document.querySelectorAll('.financial-item-row').forEach(row => {
                                const totalCostText = row.querySelector('input[name="item_total_cost[]"]').value;
                                grandTotal += parseFloat(totalCostText) || 0;
                            });

                            // Update the grand total display
                            const grandTotalCost = document.getElementById('grandTotalCost');
                            const totalCostInput = document.getElementById('totalCost');
                            if (grandTotalCost) grandTotalCost.textContent = '₱' + grandTotal.toFixed(2);
                            if (totalCostInput) totalCostInput.value = grandTotal.toFixed(2);
                        };

                        // Define a local version of calculateItemTotal for use in populateForm
                        const calculateItemTotalLocal = (row) => {
                            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                            const unitCost = parseFloat(row.querySelector('.item-unit-cost').value) || 0;
                            const totalCost = quantity * unitCost;

                            // Update the total cost display
                            row.querySelector('.item-total-cost').textContent = '₱' + totalCost.toFixed(2);
                            row.querySelector('input[name="item_total_cost[]"]').value = totalCost.toFixed(2);

                            // Update the grand total locally
                            calculateTotalCostLocal();
                        };

                        // Define a local version of updateFinancialPlanVisibility
                        const updateFinancialPlanVisibilityLocal = () => {
                            const financialPlanTableBody = document.getElementById('financialPlanTableBody');
                            const emptyFinancialPlanMessage = document.getElementById('emptyFinancialPlanMessage');
                            const financialPlanTable = document.getElementById('financialPlanTable');

                            if (!financialPlanTableBody || !emptyFinancialPlanMessage || !financialPlanTable) {
                                console.error('Missing required DOM elements for financial plan visibility update');
                                return;
                            }

                            const hasItems = financialPlanTableBody.querySelectorAll('tr').length > 0;

                            if (hasItems) {
                                financialPlanTable.style.display = 'table';
                                emptyFinancialPlanMessage.style.display = 'none';
                            } else {
                                financialPlanTable.style.display = 'none';
                                emptyFinancialPlanMessage.style.display = 'flex';
                            }
                        };

                        // If with financial plan, also populate the financial items table
                        if (entry.financial_plan_items && entry.financial_plan_quantity &&
                            entry.financial_plan_unit && entry.financial_plan_unit_cost) {

                            // Clear existing items first
                            const financialPlanTableBody = document.getElementById('financialPlanTableBody');
                            if (financialPlanTableBody) {
                                financialPlanTableBody.innerHTML = '';
                            }

                            // Make sure these are arrays before trying to iterate
                            let descriptions = Array.isArray(entry.financial_plan_items) ? entry.financial_plan_items : [];
                            let quantities = Array.isArray(entry.financial_plan_quantity) ? entry.financial_plan_quantity : [];
                            let units = Array.isArray(entry.financial_plan_unit) ? entry.financial_plan_unit : [];
                            let unitCosts = Array.isArray(entry.financial_plan_unit_cost) ? entry.financial_plan_unit_cost : [];

                            console.log('Financial plan items:', descriptions);
                            console.log('Financial plan quantities:', quantities);
                            console.log('Financial plan units:', units);
                            console.log('Financial plan unit costs:', unitCosts);

                            // Skip if only contains "none" placeholder
                            if (descriptions.length === 1 && descriptions[0] === "none") {
                                console.log('Skipping financial items as it only contains placeholder value "none"');
                            } else {
                                // Add each item to the table
                                for (let i = 0; i < descriptions.length; i++) {
                                    if (!descriptions[i] || descriptions[i] === "none") continue;

                                    // Add a new financial item row
                                    const newRow = document.createElement('tr');
                                    newRow.className = 'financial-item-row';

                                    // Create row content
                                    newRow.innerHTML = `
                                    <td>
                                        <input type="text" class="form-control item-description" name="item_description[]" placeholder="Enter item description" value="${descriptions[i] || ''}" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control item-quantity" name="item_quantity[]" min="0" placeholder="Amount" value="${quantities[i] || ''}" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control item-unit" name="item_unit[]" placeholder="Unit" value="${units[i] || ''}" required>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control item-unit-cost" name="item_unit_cost[]" min="0" step="0.01" placeholder="0.00" value="${unitCosts[i] || ''}" required>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="item-total-cost fw-medium">₱0.00</span>
                                            <input type="hidden" name="item_total_cost[]" value="0.00">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-remove-item btn-outline-danger" title="Remove item">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                    `;

                                    // Add the row to the table
                                    financialPlanTableBody.appendChild(newRow);

                                    // Add event listeners to inputs
                                    const quantityInput = newRow.querySelector('.item-quantity');
                                    const unitCostInput = newRow.querySelector('.item-unit-cost');
                                    const removeButton = newRow.querySelector('.btn-remove-item');

                                    if (quantityInput) {
                                        quantityInput.addEventListener('input', function() {
                                            calculateItemTotalLocal(newRow);
                                        });
                                    }

                                    if (unitCostInput) {
                                        unitCostInput.addEventListener('input', function() {
                                            calculateItemTotalLocal(newRow);
                                        });
                                    }

                                    if (removeButton) {
                                        removeButton.addEventListener('click', function() {
                                            financialPlanTableBody.removeChild(newRow);
                                            calculateTotalCostLocal();

                                            // Check if there are no more rows
                                            if (financialPlanTableBody.querySelectorAll('tr').length === 0) {
                                                // Show the empty message if this was the last row
                                                const emptyMessage = document.getElementById('emptyFinancialPlanMessage');
                                                const financialPlanTable = document.getElementById('financialPlanTable');

                                                if (emptyMessage) {
                                                    emptyMessage.style.display = 'flex';
                                                }

                                                if (financialPlanTable) {
                                                    financialPlanTable.style.display = 'none';
                                                }
                                            }

                                            // Call this after making our manual adjustments
                                            updateFinancialPlanVisibilityLocal();
                                        });
                                    }

                                    // Calculate this item's total using the local function
                                    calculateItemTotalLocal(newRow);
                                }

                                // Check if we have any rows after adding everything
                                const hasRows = financialPlanTableBody.querySelectorAll('tr').length > 0;
                                console.log('Financial plan has rows:', hasRows, 'Count:', financialPlanTableBody.querySelectorAll('tr').length);

                                // Update visibility based on whether we have rows
                                const emptyMessage = document.getElementById('emptyFinancialPlanMessage');
                                const financialPlanTable = document.getElementById('financialPlanTable');

                                if (hasRows) {
                                    // Hide empty message and show table if we have rows
                                    if (emptyMessage) {
                                        emptyMessage.style.display = 'none';
                                        emptyMessage.classList.add('d-none'); // Add Bootstrap d-none class
                                        console.log('Hiding empty financial plan message - have items');
                                    }
                                    if (financialPlanTable) {
                                        financialPlanTable.style.display = 'table';
                                    }
                                } else {
                                    // Show empty message and hide table if no rows
                                    if (emptyMessage) {
                                        emptyMessage.style.display = 'flex';
                                        emptyMessage.classList.remove('d-none'); // Remove Bootstrap d-none class
                                        console.log('Showing empty financial plan message - no items');
                                    }
                                    if (financialPlanTable) {
                                        financialPlanTable.style.display = 'none';
                                    }
                                }

                                // Make extra sure the badge is hidden by using a small timeout
                                // This helps with potential timing or rendering issues
                                if (hasRows) {
                                    setTimeout(() => {
                                        const emptyMsg = document.getElementById('emptyFinancialPlanMessage');
                                        if (emptyMsg) {
                                            emptyMsg.style.display = 'none';
                                            emptyMsg.classList.add('d-none');
                                            console.log('Applied delayed hiding of empty message');
                                        }
                                    }, 50);
                                }

                                // Calculate the total cost using the local function
                                calculateTotalCostLocal();
                            }
                        }
                    } else if (document.getElementById('withoutFinancialPlan').checked) {
                        document.getElementById('withoutFinancialPlan').dispatchEvent(changeEvent);
                    }
                }

                // Set source of fund values
                if (entry.source_of_fund) {
                    try {
                        // Parse the JSON string if necessary
                        let sourceFunds = entry.source_of_fund;
                        if (typeof sourceFunds === 'string') {
                            sourceFunds = JSON.parse(sourceFunds);
                        }

                        // Get the source fund options
                        const sourceFundOptions = document.querySelectorAll('.source-fund-option');
                        const sourceFundInput = document.getElementById('sourceOfFund');

                        console.log('Source of funds from DB:', sourceFunds);

                        // Mark selected options
                        let selectedFunds = [];
                        sourceFundOptions.forEach(option => {
                            const value = option.getAttribute('data-value');
                            if (sourceFunds.includes(value)) {
                                option.classList.add('selected');
                                selectedFunds.push(value);
                            } else {
                                option.classList.remove('selected');
                            }
                        });

                        // Update the hidden input value
                        if (sourceFundInput) {
                            sourceFundInput.value = selectedFunds.join(',');
                            console.log('Set source fund input value to:', sourceFundInput.value);
                        }
                    } catch (e) {
                        console.error('Error setting source of fund:', e);
                    }
                }

                // Populate dates with proper conversion handling
                if (entry.start_date) {
                    try {
                        console.log('Raw start_date from DB:', entry.start_date);
                        // Support different date formats (SQL date, MM/DD/YYYY, etc.)
                        let dateStr = entry.start_date;
                        let startDate;

                        // Handle MM/DD/YYYY format
                        if (dateStr.includes('/')) {
                            const parts = dateStr.split('/');
                            if (parts.length === 3) {
                                // Convert MM/DD/YYYY to YYYY-MM-DD for proper Date parsing
                                startDate = new Date(parts[2], parts[0] - 1, parts[1]);
                            } else {
                                startDate = new Date(dateStr);
                            }
                        } else {
                            startDate = new Date(dateStr);
                        }

                        if (isNaN(startDate.getTime())) {
                            console.error('Invalid start date:', dateStr);
                        } else {
                            console.log('Parsed start date:', startDate);
                            console.log('Setting date values - Day:', startDate.getDate(), 'Month:', startDate.getMonth() + 1, 'Year:', startDate.getFullYear());

                            // Set values using element IDs directly to ensure they are set
                            const startDayElem = document.getElementById('startDay');
                            const startMonthElem = document.getElementById('startMonth');
                            const startYearElem = document.getElementById('startYear');

                            if (startDayElem) startDayElem.value = startDate.getDate();
                            if (startMonthElem) startMonthElem.value = startDate.getMonth() + 1;
                            if (startYearElem) startYearElem.value = startDate.getFullYear();
                        }
                    } catch (e) {
                        console.error('Error parsing start date:', e);
                    }
                }

                if (entry.end_date) {
                    try {
                        console.log('Raw end_date from DB:', entry.end_date);
                        // Support different date formats (SQL date, MM/DD/YYYY, etc.)
                        let dateStr = entry.end_date;
                        let endDate;

                        // Handle MM/DD/YYYY format
                        if (dateStr.includes('/')) {
                            const parts = dateStr.split('/');
                            if (parts.length === 3) {
                                // Convert MM/DD/YYYY to YYYY-MM-DD for proper Date parsing
                                endDate = new Date(parts[2], parts[0] - 1, parts[1]);
                            } else {
                                endDate = new Date(dateStr);
                            }
                        } else {
                            endDate = new Date(dateStr);
                        }

                        if (isNaN(endDate.getTime())) {
                            console.error('Invalid end date:', dateStr);
                        } else {
                            console.log('Parsed end date:', endDate);
                            console.log('Setting date values - Day:', endDate.getDate(), 'Month:', endDate.getMonth() + 1, 'Year:', endDate.getFullYear());

                            // Set values using element IDs directly to ensure they are set
                            const endDayElem = document.getElementById('endDay');
                            const endMonthElem = document.getElementById('endMonth');
                            const endYearElem = document.getElementById('endYear');

                            if (endDayElem) endDayElem.value = endDate.getDate();
                            if (endMonthElem) endMonthElem.value = endDate.getMonth() + 1;
                            if (endYearElem) endYearElem.value = endDate.getFullYear();
                        }
                    } catch (e) {
                        console.error('Error parsing end date:', e);
                    }
                }

                // Generate the workplan timeline if we have start and end dates
                if (entry.start_date && entry.end_date) {
                    // Store the workplan data in global variables for possible later restoration
                    try {
                        if (entry.workplan_activity) {
                            window.tempWorkplanActivity = typeof entry.workplan_activity === 'string' ?
                                JSON.parse(entry.workplan_activity) :
                                entry.workplan_activity;
                        }

                        if (entry.workplan_date) {
                            window.tempWorkplanDate = typeof entry.workplan_date === 'string' ?
                                JSON.parse(entry.workplan_date) :
                                entry.workplan_date;
                        }
                    } catch (e) {
                        console.error('Error storing workplan data:', e);
                    }

                    // Use a faster timeout
                    setTimeout(() => {
                        const startMonth = document.getElementById('startMonth')?.value;
                        const startDay = document.getElementById('startDay')?.value;
                        const startYear = document.getElementById('startYear')?.value;
                        const endMonth = document.getElementById('endMonth')?.value;
                        const endDay = document.getElementById('endDay')?.value;
                        const endYear = document.getElementById('endYear')?.value;

                        if (startMonth && startDay && startYear && endMonth && endDay && endYear) {
                            const startDate = new Date(startYear, startMonth - 1, startDay);
                            const endDate = new Date(endYear, endMonth - 1, endDay);
                            generateWorkPlanTimeline(startDate, endDate);

                            // After timeline is generated, populate workplan activities
                            if (entry.workplan_activity && entry.workplan_date) {
                                try {
                                    console.log('Populating workplan with activity data:', entry.workplan_activity);
                                    console.log('Populating workplan with date data:', entry.workplan_date);

                                    let activities = [];
                                    let dates = [];

                                    // Handle both string and parsed JSON format
                                    if (typeof entry.workplan_activity === 'string') {
                                        activities = JSON.parse(entry.workplan_activity);
                                    } else if (Array.isArray(entry.workplan_activity)) {
                                        activities = entry.workplan_activity;
                                    }

                                    if (typeof entry.workplan_date === 'string') {
                                        dates = JSON.parse(entry.workplan_date);
                                    } else if (Array.isArray(entry.workplan_date)) {
                                        dates = entry.workplan_date;
                                    }

                                    console.log('Parsed activities:', activities);
                                    console.log('Parsed dates:', dates);

                                    // Clear existing activities first
                                    const timelineActivities = document.getElementById('timeline-activities');
                                    if (timelineActivities) {
                                        timelineActivities.innerHTML = '';
                                    }

                                    // Add activities from saved data
                                    if (Array.isArray(activities)) {
                                        for (let i = 0; i < activities.length; i++) {
                                            const activity = activities[i];
                                            const dateStr = dates[i] || '';
                                            const dateArray = dateStr.split(',');

                                            console.log(`Adding activity ${i+1}:`, activity);
                                            console.log(`Dates for activity ${i+1}:`, dateArray);

                                            // Add new row
                                            const newRow = addNewActivityRow();

                                            // Set the activity name
                                            const nameInput = newRow.querySelector('input[name="workplanActivity[]"]');
                                            if (nameInput) {
                                                nameInput.value = activity || '';
                                                console.log(`Set activity ${i+1} name:`, activity);
                                            }

                                            // Create hidden input for dates - ensure no duplicates
                                            // First, remove any existing hidden inputs
                                            const existingInputs = newRow.querySelectorAll('input[name="workplanDate[]"]');
                                            existingInputs.forEach(input => input.remove());

                                            // Create a new hidden input with the dates
                                            const hiddenInput = document.createElement('input');
                                            hiddenInput.type = 'hidden';
                                            hiddenInput.name = 'workplanDate[]';
                                            hiddenInput.value = dateStr;
                                            newRow.appendChild(hiddenInput);

                                            // Make sure the row has checkboxes
                                            const checkboxCells = newRow.querySelectorAll('td.checkbox-cell');
                                            if (checkboxCells.length === 0) {
                                                // The row doesn't have checkboxes yet, get dates from the timeline table
                                                const headerRow = document.querySelector('#timeline-table thead tr');
                                                if (headerRow) {
                                                    // Count the number of date columns in the header
                                                    const dateColumnCount = headerRow.children.length - 1; // Minus 1 for the activity name column
                                                    if (dateColumnCount > 0) {
                                                        // Get date objects for the entire period covered by the timeline
                                                        const startDate = new Date(startYear, startMonth - 1, startDay);
                                                        const endDate = new Date(endYear, endMonth - 1, endDay);
                                                        let datesList = [];
                                                        let currentDate = new Date(startDate);

                                                        while (currentDate <= endDate) {
                                                            datesList.push(new Date(currentDate));
                                                            currentDate.setDate(currentDate.getDate() + 1);
                                                        }

                                                        // Add checkboxes to the row
                                                        updateRowCheckboxes(newRow, datesList);
                                                        console.log(`Added ${datesList.length} checkbox cells to row ${i+1}`);
                                                    }
                                                }
                                            }

                                            // Now check the appropriate date checkboxes
                                            if (dateArray.length > 0) {
                                                console.log(`Date array for activity ${i+1}:`, dateArray);

                                                // Use an optimized timeout
                                                setTimeout(() => {
                                                    const headerRow = document.querySelector('#timeline-table thead tr');
                                                    const checkboxes = newRow.querySelectorAll('input.activity-checkbox');

                                                    if (headerRow && checkboxes.length > 0) {
                                                        console.log(`Found ${checkboxes.length} checkboxes to match with ${dateArray.length} dates for activity ${i+1}`);

                                                        // Check each checkbox based on its data-date attribute
                                                        checkboxes.forEach((checkbox, index) => {
                                                            if (headerRow.children[index + 1]) {
                                                                const headerDate = headerRow.children[index + 1].textContent.trim();

                                                                // Try different formats and approaches for matching
                                                                const isDateSelected =
                                                                    dateArray.includes(headerDate) ||
                                                                    dateArray.some(date => date.trim() === headerDate) ||
                                                                    dateArray.some(date => date.trim().toLowerCase() === headerDate.toLowerCase());

                                                                if (isDateSelected) {
                                                                    checkbox.checked = true;
                                                                    console.log(`✓ Checked date ${headerDate} for activity ${i+1}`);
                                                                }

                                                                // Also explicitly set the data-date attribute
                                                                checkbox.setAttribute('data-date', headerDate);
                                                            }
                                                        });

                                                        // Force update of the hidden field with the date values
                                                        // First, remove any existing hidden inputs to prevent duplicates
                                                        const existingInputs = newRow.querySelectorAll('input[name="workplanDate[]"]');
                                                        existingInputs.forEach(input => input.remove());

                                                        // Create a new hidden input
                                                        const hiddenDateField = document.createElement('input');
                                                        hiddenDateField.type = 'hidden';
                                                        hiddenDateField.name = 'workplanDate[]';
                                                        hiddenDateField.value = dateArray.join(',');
                                                        newRow.appendChild(hiddenDateField);
                                                    } else {
                                                        console.warn(`Could not find checkboxes or headers for activity ${i+1}`);
                                                    }
                                                }, 100);
                                            }
                                        }

                                        // Final validation to make sure section is properly marked as complete
                                        setTimeout(() => {
                                            validateWorkPlanSection();

                                            // Immediately mark section as complete if data exists
                                            const navItem = document.querySelector('.form-nav-item[data-section="section-9"]');
                                            if (navItem) {
                                                const allRows = document.querySelectorAll('#timeline-activities .activity-row');
                                                let allComplete = true;

                                                // Fast check - just verify rows exist with some data
                                                if (allRows.length > 0) {
                                                    navItem.classList.add('is-complete');
                                                    navItem.classList.remove('has-error');

                                                    // Also update section title
                                                    const sectionTitle = document.querySelector('#section-9 .section-title');
                                                    if (sectionTitle) {
                                                        sectionTitle.classList.remove('has-error');
                                                    }
                                                }
                                            }
                                        }, 300);
                                    }
                                } catch (e) {
                                    console.error('Error populating workplan:', e);
                                }
                            }
                        }
                    }, 200);
                }

                // Helper function to populate array fields in the form (for JSON arrays)
                function populateArrayField(fieldName, values) {
                    if (!values) return;

                    // Handle both array and string formats
                    let valuesArray = values;
                    if (typeof values === 'string') {
                        try {
                            valuesArray = JSON.parse(values);
                        } catch (e) {
                            console.error(`Error parsing ${fieldName}:`, e);
                            return;
                        }
                    }

                    if (!Array.isArray(valuesArray) || valuesArray.length === 0) {
                        console.log(`No values to populate for ${fieldName}`);
                        return;
                    }

                    console.log(`Populating ${fieldName} with values:`, valuesArray);

                    // Get the container element
                    const container = document.getElementById(`${fieldName}_container`);
                    if (!container) {
                        console.error(`Container for ${fieldName} not found`);
                        return;
                    }

                    // Clear existing fields except the first one
                    const existingInputs = container.querySelectorAll('.input-group');
                    for (let i = 1; i < existingInputs.length; i++) {
                        existingInputs[i].remove();
                    }

                    // Set the first value
                    const firstInput = container.querySelector(`input[name="${fieldName}[]"]`);
                    if (firstInput && valuesArray.length > 0) {
                        firstInput.value = valuesArray[0];
                    }

                    // Add additional inputs for remaining values
                    const addButton = document.getElementById(`add_${fieldName}_btn`);

                    for (let i = 1; i < valuesArray.length; i++) {
                        // Clone the first input group
                        const firstGroup = container.querySelector('.input-group');
                        const newGroup = firstGroup.cloneNode(true);

                        // Update the number indicator
                        const numberIndicator = newGroup.querySelector('.input-number-indicator');
                        if (numberIndicator) {
                            numberIndicator.textContent = `#${i + 1}`;
                        }

                        // Set the value
                        const input = newGroup.querySelector('input');
                        if (input) {
                            input.value = valuesArray[i];
                        }

                        // Show the remove button
                        const removeButton = newGroup.querySelector('.remove-input');
                        if (removeButton) {
                            removeButton.style.display = 'block';

                            // Attach event listener
                            removeButton.addEventListener('click', function() {
                                newGroup.remove();
                                updateNumberIndicators(container);
                            });
                        }

                        // Add to the container
                        container.appendChild(newGroup);
                    }

                    // Helper function to update number indicators after removal
                    function updateNumberIndicators(container) {
                        const groups = container.querySelectorAll('.input-group');
                        groups.forEach((group, index) => {
                            const indicator = group.querySelector('.input-number-indicator');
                            if (indicator) {
                                indicator.textContent = `#${index + 1}`;
                            }
                        });
                    }
                }

                // Populate the array fields
                populateArrayField('specific_objectives', entry.specific_objectives);

                // Use entry.strategy since that's what's in the database
                populateArrayField('strategies', entry.strategy);
                populateArrayField('expected_output', entry.expected_output);
                populateArrayField('specific_plans', entry.specific_plan);

                // Agency & Participants section
                set('internal_type', entry.internal_type);
                set('internal_male', entry.internal_male);
                set('internal_female', entry.internal_female);
                set('internal_total', entry.internal_total);
                set('external_type', entry.external_type);
                set('external_male', entry.external_male);
                set('external_female', entry.external_female);
                set('external_total', entry.external_total);
                set('total_male', entry.grand_total_male);
                set('total_female', entry.grand_total_female);
                set('grand_total', entry.grand_total);


                // Trigger calculation to ensure totals are displayed correctly
                setTimeout(() => {
                    if (typeof updateInternalParticipantsFromProjectTeam === 'function') {
                        updateInternalParticipantsFromProjectTeam();
                    }
                    calculateParticipantTotals();
                }, 200);

        
                // ... (repeat for all simple fields as needed)
                // Set year and quarter first, then handle gender issue
                if (entry.year && entry.quarter) {
                    // Load gender issues based on year and quarter before trying to set gender_issue_id
                    const yearSelect = document.getElementById('year');
                    const quarterSelect = document.getElementById('quarter');

                    if (yearSelect && quarterSelect) {
                        // Set year and quarter values first
                        yearSelect.value = entry.year;
                        quarterSelect.value = entry.quarter;
                        quarterSelect.disabled = false;

                        // Load gender issues
                        const genderIssueSelect = document.getElementById('genderIssue');
                        if (genderIssueSelect) {
                            // Immediately mark the gender issue section as valid
                            markGenderIssueAsValid();

                            // First load gender issues asynchronously
                            fetch(`get_gender_issues.php?year=${entry.year}&quarter=${entry.quarter}`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success && data.issues && data.issues.length > 0) {
                                        // Clear existing options
                                        genderIssueSelect.innerHTML = '<option value="" selected disabled>Select Gender Issue</option>';

                                        // Add gender issues from response
                                        data.issues.forEach(issue => {
                                            const option = document.createElement('option');
                                            option.value = issue.id;
                                            option.textContent = issue.gender_issue;

                                            // Apply styling based on status
                                            if (issue.status && issue.status !== 'Approved') {
                                                option.style.color = 'red';
                                                option.style.fontStyle = 'italic';
                                                option.disabled = true;
                                                option.textContent += ` (${issue.status})`;
                                            }

                                            genderIssueSelect.appendChild(option);
                                        });

                                        // Enable the select
                                        genderIssueSelect.disabled = false;

                                        // Now set the selected gender issue
                                        if (entry.gender_issue_id) {
                                            // Force check if the option exists
                                            let optionExists = false;
                                            for (let i = 0; i < genderIssueSelect.options.length; i++) {
                                                if (genderIssueSelect.options[i].value == entry.gender_issue_id) {
                                                    optionExists = true;
                                                    break;
                                                }
                                            }

                                            if (optionExists) {
                                                genderIssueSelect.value = entry.gender_issue_id;
                                                // Trigger the change event to clear any validation errors
                                                const changeEvent = new Event('change', {
                                                    bubbles: true
                                                });
                                                genderIssueSelect.dispatchEvent(changeEvent);
                                                console.log('Set gender issue ID to:', entry.gender_issue_id);

                                                // Mark gender issue as valid again after setting value
                                                markGenderIssueAsValid();
                                            } else {
                                                console.error('Gender issue ID not found in options:', entry.gender_issue_id);
                                                // Create and add missing option if not found
                                                const option = document.createElement('option');
                                                option.value = entry.gender_issue_id;
                                                option.textContent = 'Gender Issue #' + entry.gender_issue_id;
                                                genderIssueSelect.appendChild(option);
                                                genderIssueSelect.value = entry.gender_issue_id;

                                                // Trigger the change event to clear any validation errors
                                                const changeEvent = new Event('change', {
                                                    bubbles: true
                                                });
                                                genderIssueSelect.dispatchEvent(changeEvent);

                                                // Mark gender issue as valid again after setting value
                                                markGenderIssueAsValid();
                                            }
                                        }

                                        // Update the gender-issue section status
                                        updateSectionStatus('gender-issue', true, true);
                                    }
                                });
                        }
                    }
                }
                // Set dates
                if (entry.start_date) {
                    const [m, d, y] = entry.start_date.split('/');
                    set('startMonth', m);
                    set('startDay', d);
                    set('startYear', y);
                }
                if (entry.end_date) {
                    const [m, d, y] = entry.end_date.split('/');
                    set('endMonth', m);
                    set('endDay', d);
                    set('endYear', y);
                }
                // Set lunch break
                if (document.getElementById('lunchBreak')) {
                    document.getElementById('lunchBreak').checked = entry.lunch_break == 1;
                }
                // Set agenda type (radio buttons)
                if (entry.agenda) {
                    const agendaRadios = document.querySelectorAll('input[name="agenda_type"]');
                    agendaRadios.forEach(radio => {
                        if (radio.value === entry.agenda) {
                            radio.checked = true;
                            console.log(`Selected agenda: ${entry.agenda}`);

                            // Update section status
                            updateSectionStatus('agenda-section', true);
                        }
                    });
                }

                // Set SDGs checkboxes
                if (entry.sdg) {
                    let sdgs = entry.sdg;
                    // Handle both array and string formats
                    if (typeof entry.sdg === 'string') {
                        try {
                            sdgs = JSON.parse(entry.sdg);
                        } catch (e) {
                            console.error('Error parsing SDGs:', e);
                        }
                    }

                    console.log('Setting SDGs:', sdgs);

                    // Check the corresponding checkboxes - note the name is "sdgs[]" not "sdg[]"
                    document.querySelectorAll('input[name="sdgs[]"]').forEach(cb => {
                        // Check if this checkbox value is in the sdgs array
                        const isChecked = Array.isArray(sdgs) && sdgs.includes(cb.value);
                        cb.checked = isChecked;
                        if (isChecked) {
                            console.log(`Checked SDG: ${cb.value}`);
                        }
                    });

                    // Update SDGs section status
                    updateSectionStatus('sdgs-section', true);
                }
                // Populate office/college/organization
                if (entry.office_college_organization) {
                    let offices = entry.office_college_organization;
                    // Handle both array and string formats
                    if (typeof entry.office_college_organization === 'string') {
                        try {
                            offices = JSON.parse(entry.office_college_organization);
                        } catch (e) {
                            console.error('Error parsing offices:', e);
                        }
                    }

                    console.log('Setting offices:', offices);

                    // Get references to containers
                    const officeInputsContainer = document.getElementById('officeInputsContainer');
                    if (officeInputsContainer && Array.isArray(offices)) {
                        // Clear container except for the first input
                        const firstInput = officeInputsContainer.querySelector('.input-group');
                        if (!firstInput) return;

                        // Clear all inputs except first one
                        while (officeInputsContainer.children.length > 1) {
                            officeInputsContainer.removeChild(officeInputsContainer.lastChild);
                        }

                        // Set the first input value
                        const firstOfficeInput = firstInput.querySelector('input[name="offices[]"]');
                        if (firstOfficeInput && offices.length > 0) {
                            firstOfficeInput.value = offices[0];
                        }

                        // Add additional inputs for remaining values
                        for (let i = 1; i < offices.length; i++) {
                            // Clone the first input group
                            const newInput = firstInput.cloneNode(true);

                            // Set value
                            newInput.querySelector('input').value = offices[i];

                            // Update the number indicator
                            newInput.querySelector('.input-number-indicator').textContent = `#${i + 1}`;

                            // Show the remove button
                            const removeBtn = newInput.querySelector('.remove-input');
                            removeBtn.style.display = 'block';

                            // Add event listener to remove button
                            removeBtn.addEventListener('click', function() {
                                officeInputsContainer.removeChild(newInput);

                                // Update numbering of remaining inputs
                                const inputs = officeInputsContainer.querySelectorAll('.input-group');
                                inputs.forEach((input, index) => {
                                    const numberIndicator = input.querySelector('.input-number-indicator');
                                    if (numberIndicator) {
                                        numberIndicator.textContent = `#${index + 1}`;
                                    }
                                });

                                // Update validation
                                updateOfficeProgramsCompletionStatus();
                            });

                            // Add input event listener
                            const newInputField = newInput.querySelector('input[name="offices[]"]');
                            if (newInputField) {
                                newInputField.addEventListener('input', function() {
                                    updateOfficeProgramsCompletionStatus();
                                });
                            }

                            // Append to container
                            officeInputsContainer.appendChild(newInput);
                        }
                    }
                }

                // Populate programs
                if (entry.program_list) {
                    let programs = entry.program_list;
                    // Handle both array and string formats
                    if (typeof entry.program_list === 'string') {
                        try {
                            programs = JSON.parse(entry.program_list);
                        } catch (e) {
                            console.error('Error parsing programs:', e);
                        }
                    }

                    console.log('Setting programs:', programs);

                    // Get references to containers
                    const programInputsContainer = document.getElementById('programInputsContainer');
                    if (programInputsContainer && Array.isArray(programs)) {
                        // Clear container except for the first input
                        const firstInput = programInputsContainer.querySelector('.input-group');
                        if (!firstInput) return;

                        // Clear all inputs except first one
                        while (programInputsContainer.children.length > 1) {
                            programInputsContainer.removeChild(programInputsContainer.lastChild);
                        }

                        // Set the first input value
                        const firstProgramInput = firstInput.querySelector('input[name="programs[]"]');
                        if (firstProgramInput && programs.length > 0) {
                            firstProgramInput.value = programs[0];
                        }

                        // Add additional inputs for remaining values
                        for (let i = 1; i < programs.length; i++) {
                            // Clone the first input group
                            const newInput = firstInput.cloneNode(true);

                            // Set value
                            newInput.querySelector('input').value = programs[i];

                            // Update the number indicator
                            newInput.querySelector('.input-number-indicator').textContent = `#${i + 1}`;

                            // Show the remove button
                            const removeBtn = newInput.querySelector('.remove-input');
                            removeBtn.style.display = 'block';

                            // Add event listener to remove button
                            removeBtn.addEventListener('click', function() {
                                programInputsContainer.removeChild(newInput);

                                // Update numbering of remaining inputs
                                const inputs = programInputsContainer.querySelectorAll('.input-group');
                                inputs.forEach((input, index) => {
                                    const numberIndicator = input.querySelector('.input-number-indicator');
                                    if (numberIndicator) {
                                        numberIndicator.textContent = `#${index + 1}`;
                                    }
                                });

                                // Update validation
                                updateOfficeProgramsCompletionStatus();
                            });

                            // Add input event listener
                            const newInputField = newInput.querySelector('input[name="programs[]"]');
                            if (newInputField) {
                                newInputField.addEventListener('input', function() {
                                    updateOfficeProgramsCompletionStatus();
                                });
                            }

                            // Append to container
                            programInputsContainer.appendChild(newInput);
                        }
                    }
                }

                // Update Office and Programs section status
                updateOfficeProgramsCompletionStatus();

                // Handle project team data
                // Project Leaders
                if (entry.project_leader) {
                    // Parse project leader data
                    let projectLeaders = entry.project_leader;
                    let projectLeaderResponsibilities = entry.project_leader_responsibilities;

                    // Handle string format (convert to JSON)
                    if (typeof projectLeaders === 'string') {
                        try {
                            projectLeaders = JSON.parse(projectLeaders);
                        } catch (e) {
                            console.error('Error parsing project leaders:', e);
                        }
                    }

                    if (typeof projectLeaderResponsibilities === 'string') {
                        try {
                            projectLeaderResponsibilities = JSON.parse(projectLeaderResponsibilities);
                        } catch (e) {
                            console.error('Error parsing project leader responsibilities:', e);
                        }
                    }

                    // Get container reference
                    const projectLeadersContainer = document.getElementById('projectLeadersContainer');

                    // Clear existing content, keeping the first template
                    const firstLeaderCard = projectLeadersContainer.querySelector('.team-member-card');
                    if (firstLeaderCard) {
                        // Clear all cards except the first one
                        while (projectLeadersContainer.children.length > 1) {
                            projectLeadersContainer.removeChild(projectLeadersContainer.lastChild);
                        }

                        // Populate the first card with the first leader
                        if (Array.isArray(projectLeaders) && projectLeaders.length > 0) {
                            // Set name and trigger autocomplete to populate other fields
                            const nameInput = firstLeaderCard.querySelector('input[name="leader_name[]"]');
                            if (nameInput) {
                                nameInput.value = projectLeaders[0];

                                // Create a change event to trigger the autocomplete handlers
                                const event = new Event('change', {
                                    bubbles: true
                                });
                                nameInput.dispatchEvent(event);

                                // Additionally, explicitly fetch personnel data for this name
                                fetchPersonnelDataByName(projectLeaders[0], 'leader', 1);
                            }

                            // Process responsibilities for the first leader
                            if (Array.isArray(projectLeaderResponsibilities) && projectLeaderResponsibilities.length > 0) {
                                const taskContainer = firstLeaderCard.querySelector('#leaderTasksContainer_1');
                                if (taskContainer) {
                                    // Get the first task input
                                    const firstTaskInput = taskContainer.querySelector('.input-group');

                                    if (firstTaskInput) {
                                        // Check the data format and handle both string and array formats
                                        let responsibilities = [];
                                        const respItem = projectLeaderResponsibilities[0];

                                        console.log('First project leader responsibility item:', respItem, 'type:', typeof respItem);

                                        if (typeof respItem === 'string') {
                                            // If it's a string, split by commas
                                            responsibilities = respItem.split(',');
                                        } else if (Array.isArray(respItem)) {
                                            // If it's an array, use it directly
                                            responsibilities = respItem;
                                        } else {
                                            // If it's anything else, convert to string and split
                                            responsibilities = String(respItem).split(',');
                                        }

                                        console.log('Processed first project leader responsibilities:', responsibilities);

                                        // Set first responsibility
                                        const firstTaskField = firstTaskInput.querySelector('input[name="leader_tasks_1[]"]');
                                        if (firstTaskField && responsibilities.length > 0) {
                                            firstTaskField.value = responsibilities[0].trim();
                                        }

                                        // Add additional responsibilities
                                        for (let i = 1; i < responsibilities.length; i++) {
                                            // Clone the first input
                                            const newTask = firstTaskInput.cloneNode(true);

                                            // Set the value
                                            const taskInput = newTask.querySelector('input[name="leader_tasks_1[]"]');
                                            if (taskInput) {
                                                taskInput.value = responsibilities[i].trim();
                                            }

                                            // Update the number indicator
                                            const numberIndicator = newTask.querySelector('.input-number-indicator');
                                            if (numberIndicator) {
                                                numberIndicator.textContent = `#${i + 1}`;
                                            }

                                            // Show the remove button
                                            const removeBtn = newTask.querySelector('.remove-input');
                                            if (removeBtn) {
                                                removeBtn.style.display = 'block';

                                                // Add event listener for the remove button
                                                removeBtn.addEventListener('click', function() {
                                                    taskContainer.removeChild(newTask);

                                                    // Update numbering
                                                    const inputs = taskContainer.querySelectorAll('.input-group');
                                                    inputs.forEach((input, index) => {
                                                        const numberIndicator = input.querySelector('.input-number-indicator');
                                                        if (numberIndicator) {
                                                            numberIndicator.textContent = `#${index + 1}`;
                                                        }
                                                    });
                                                });
                                            }

                                            // Append to container
                                            taskContainer.appendChild(newTask);
                                        }
                                    }
                                }
                            }
                        }

                        // Add additional project leaders
                        for (let i = 1; i < projectLeaders.length; i++) {
                            // Clone the first card
                            const newCard = firstLeaderCard.cloneNode(true);

                            // Update card title
                            const cardTitle = newCard.querySelector('.card-header h6');
                            if (cardTitle) {
                                cardTitle.textContent = `Project Leader #${i + 1}`;
                            }

                            // Update input IDs and names for the new card
                            newCard.querySelectorAll('input, select, textarea').forEach(input => {
                                if (input.id && input.id.includes('_1')) {
                                    input.id = input.id.replace('_1', `_${i + 1}`);
                                }
                                if (input.name && input.name.includes('_1[')) {
                                    input.name = input.name.replace('_1[', `_${i + 1}[`);
                                }
                            });

                            // Update task container ID
                            const taskContainer = newCard.querySelector('.tasks-container');
                            if (taskContainer && taskContainer.id) {
                                taskContainer.id = `leaderTasksContainer_${i + 1}`;
                            }

                            // Update task button data-index
                            const taskButton = newCard.querySelector('.add-task-btn');
                            if (taskButton) {
                                taskButton.dataset.index = i + 1;
                            }

                            // Set name value
                            const nameInput = newCard.querySelector('input[name="leader_name[]"]');
                            if (nameInput) {
                                nameInput.value = projectLeaders[i];

                                // Create a change event to trigger the autocomplete handlers
                                const event = new Event('change', {
                                    bubbles: true
                                });
                                nameInput.dispatchEvent(event);

                                // Additionally, explicitly fetch personnel data for this name
                                fetchPersonnelDataByName(projectLeaders[i], 'leader', i + 1);
                            }

                            // Process responsibilities for this leader
                            if (Array.isArray(projectLeaderResponsibilities) && projectLeaderResponsibilities.length > i) {
                                const taskContainer = newCard.querySelector(`.tasks-container`);
                                if (taskContainer) {
                                    // Get the first task input
                                    const firstTaskInput = taskContainer.querySelector('.input-group');

                                    if (firstTaskInput) {
                                        // Clear existing tasks
                                        while (taskContainer.children.length > 0) {
                                            taskContainer.removeChild(taskContainer.lastChild);
                                        }

                                        // Check the data format and handle both string and array formats
                                        let responsibilities = [];
                                        const respItem = projectLeaderResponsibilities[i];

                                        console.log('Additional project leader responsibility item:', respItem, 'type:', typeof respItem);

                                        if (typeof respItem === 'string') {
                                            // If it's a string, split by commas
                                            responsibilities = respItem.split(',');
                                        } else if (Array.isArray(respItem)) {
                                            // If it's an array, use it directly
                                            responsibilities = respItem;
                                        } else {
                                            // If it's anything else, convert to string and split
                                            responsibilities = String(respItem).split(',');
                                        }

                                        console.log('Processed additional project leader responsibilities:', responsibilities);

                                        // Add tasks for each responsibility
                                        for (let j = 0; j < responsibilities.length; j++) {
                                            // Clone the template
                                            const newTask = firstTaskInput.cloneNode(true);

                                            // Update the name attribute
                                            const input = newTask.querySelector('input');
                                            if (input) {
                                                input.name = `leader_tasks_${i + 1}[]`;
                                                input.value = responsibilities[j].trim();
                                            }

                                            // Update the number indicator
                                            const numberIndicator = newTask.querySelector('.input-number-indicator');
                                            if (numberIndicator) {
                                                numberIndicator.textContent = `#${j + 1}`;
                                            }

                                            // Show the remove button for tasks after the first one
                                            const removeBtn = newTask.querySelector('.remove-input');
                                            if (removeBtn) {
                                                removeBtn.style.display = j > 0 ? 'block' : 'none';

                                                // Add event listener for the remove button
                                                removeBtn.addEventListener('click', function() {
                                                    taskContainer.removeChild(newTask);

                                                    // Update numbering
                                                    const inputs = taskContainer.querySelectorAll('.input-group');
                                                    inputs.forEach((input, index) => {
                                                        const numberIndicator = input.querySelector('.input-number-indicator');
                                                        if (numberIndicator) {
                                                            numberIndicator.textContent = `#${index + 1}`;
                                                        }
                                                    });
                                                });
                                            }

                                            // Append to container
                                            taskContainer.appendChild(newTask);
                                        }
                                    }
                                }
                            }

                            // Show the remove button
                            const removeButton = newCard.querySelector('.remove-team-member');
                            if (removeButton) {
                                removeButton.style.display = 'block';

                                // Add event listener for the remove button
                                removeButton.addEventListener('click', function() {
                                    projectLeadersContainer.removeChild(newCard);

                                    // Update numbering of remaining cards
                                    const cards = projectLeadersContainer.querySelectorAll('.team-member-card');
                                    cards.forEach((card, index) => {
                                        const title = card.querySelector('.card-header h6');
                                        if (title) {
                                            title.textContent = `Project Leader #${index + 1}`;
                                        }
                                    });
                                });
                            }

                            // Append to container
                            projectLeadersContainer.appendChild(newCard);
                        }
                    }
                }

                // Assistant Project Leaders
                if (entry.assistant_project_leader) {
                    // Parse assistant project leader data
                    let assistantLeaders = entry.assistant_project_leader;
                    let assistantLeaderResponsibilities = entry.assistant_project_leader_responsibilities;

                    console.log('Assistant project leader data:', entry.assistant_project_leader);
                    console.log('Assistant project leader responsibilities data:', entry.assistant_project_leader_responsibilities);
                    console.log('COMPLETE ENTRY OBJECT KEYS:', Object.keys(entry));
                    console.log('FULL ENTRY OBJECT FOR DEBUGGING:', JSON.stringify(entry));

                    // More detailed check for missing or incorrect field name
                    for (const key of Object.keys(entry)) {
                        if (key.toLowerCase().includes('assistant') || key.toLowerCase().includes('responsib')) {
                            console.log('Potential match for assistant or responsibilities:', key, JSON.stringify(entry[key]));
                        }
                    }

                    // Additional debugging to check data format before parsing
                    console.log('assistantLeaderResponsibilities before parsing:', typeof assistantLeaderResponsibilities, assistantLeaderResponsibilities);

                    // Handle string format (convert to JSON)
                    if (typeof assistantLeaders === 'string') {
                        try {
                            assistantLeaders = JSON.parse(assistantLeaders);
                        } catch (e) {
                            console.error('Error parsing assistant project leaders:', e);
                        }
                    }

                    if (typeof assistantLeaderResponsibilities === 'string') {
                        try {
                            assistantLeaderResponsibilities = JSON.parse(assistantLeaderResponsibilities);
                        } catch (e) {
                            console.error('Error parsing assistant project leader responsibilities:', e);
                        }
                    }

                    console.log('After parsing: Assistant leaders =', assistantLeaders);
                    console.log('After parsing: Assistant leader responsibilities =', assistantLeaderResponsibilities);

                    // Get container reference
                    const assistantLeadersContainer = document.getElementById('assistantLeadersContainer');

                    // Clear existing content, keeping the first template
                    const firstAssistantCard = assistantLeadersContainer.querySelector('.team-member-card');
                    if (firstAssistantCard) {
                        // Clear all cards except the first one
                        while (assistantLeadersContainer.children.length > 1) {
                            assistantLeadersContainer.removeChild(assistantLeadersContainer.lastChild);
                        }

                        // Populate the first card with the first assistant leader
                        if (Array.isArray(assistantLeaders) && assistantLeaders.length > 0) {
                            // Set name and trigger autocomplete to populate other fields
                            const nameInput = firstAssistantCard.querySelector('input[name="asst_leader_name[]"]');
                            if (nameInput) {
                                nameInput.value = assistantLeaders[0];

                                // Create a change event to trigger the autocomplete handlers
                                const event = new Event('change', {
                                    bubbles: true
                                });
                                nameInput.dispatchEvent(event);

                                // Additionally, explicitly fetch personnel data for this name
                                fetchPersonnelDataByName(assistantLeaders[0], 'asst_leader', 1);
                            }

                            // Process responsibilities for the first assistant leader
                            console.log('Processing first assistant leader responsibilities');
                            console.log('assistantLeaderResponsibilities =', assistantLeaderResponsibilities);
                            console.log('Is Array?', Array.isArray(assistantLeaderResponsibilities));
                            if (assistantLeaderResponsibilities) {
                                console.log('Length:', assistantLeaderResponsibilities.length);
                            }

                            // Debug all container IDs to find potential matches
                            firstAssistantCard.querySelectorAll('*[id]').forEach(el => {
                                console.log('Found element with ID:', el.id);
                            });

                            if (Array.isArray(assistantLeaderResponsibilities) && assistantLeaderResponsibilities.length > 0) {
                                console.log('Found valid assistant leader responsibilities array with length > 0');
                                // Try both id and class selector approaches
                                let taskContainer = firstAssistantCard.querySelector('#asstLeaderTasksContainer_1');
                                if (!taskContainer) {
                                    // Try alternative approach by finding the tasks container directly
                                    taskContainer = firstAssistantCard.querySelector('.tasks-container');
                                    console.log('Using alternative task container selector, found?', !!taskContainer);
                                }
                                console.log('Task container found?', !!taskContainer);
                                if (taskContainer) {
                                    // Get the first task input
                                    const firstTaskInput = taskContainer.querySelector('.input-group');
                                    console.log('First task input found?', !!firstTaskInput);

                                    if (firstTaskInput) {
                                        // Check the data format and handle both string and array formats
                                        let responsibilities = [];
                                        const respItem = assistantLeaderResponsibilities[0];

                                        console.log('First assistant leader responsibility item:', respItem, 'type:', typeof respItem);

                                        if (typeof respItem === 'string') {
                                            // If it's a string, split by commas
                                            responsibilities = respItem.split(',');
                                        } else if (Array.isArray(respItem)) {
                                            // If it's an array, use it directly
                                            responsibilities = respItem;
                                        } else {
                                            // If it's anything else, convert to string and split
                                            responsibilities = String(respItem).split(',');
                                        }

                                        // Special case: if we have a single string in the array and it already contains commas
                                        // This matches the format seen in the logs: ["Assistant 1,Assistant 2"]
                                        if (responsibilities.length === 1 && responsibilities[0].includes(',')) {
                                            console.log('Found comma in single responsibility, further splitting');
                                            responsibilities = responsibilities[0].split(',').map(item => item.trim());
                                        }

                                        console.log('Processed first assistant leader responsibilities:', responsibilities);

                                        // Set first responsibility
                                        // Try to find the input by name attribute or any input in the container as fallback
                                        let firstTaskField = firstTaskInput.querySelector('input[name="asst_leader_tasks_1[]"]');
                                        if (!firstTaskField) {
                                            firstTaskField = firstTaskInput.querySelector('input');
                                            console.log('Falling back to any input in task container, found?', !!firstTaskField);
                                            if (firstTaskField) {
                                                console.log('Input name:', firstTaskField.name);
                                                // Ensure the name attribute is set correctly
                                                firstTaskField.name = 'asst_leader_tasks_1[]';
                                            }
                                        }
                                        console.log('First task field found?', !!firstTaskField);
                                        if (firstTaskField && responsibilities.length > 0) {
                                            console.log('Setting first responsibility value to:', responsibilities[0].trim());
                                            firstTaskField.value = responsibilities[0].trim();
                                        }

                                        // Add additional responsibilities
                                        for (let i = 1; i < responsibilities.length; i++) {
                                            // Clone the first input
                                            const newTask = firstTaskInput.cloneNode(true);

                                            // Set the value - try specific selector then fall back to any input
                                            let taskInput = newTask.querySelector('input[name="asst_leader_tasks_1[]"]');
                                            if (!taskInput) {
                                                taskInput = newTask.querySelector('input');
                                                console.log(`Falling back to any input for additional responsibility ${i}, found?`, !!taskInput);
                                                if (taskInput) {
                                                    console.log(`Original input name:`, taskInput.name);
                                                    // Ensure the name attribute is set correctly
                                                    taskInput.name = 'asst_leader_tasks_1[]';
                                                }
                                            }
                                            console.log(`Additional responsibility ${i} input found?`, !!taskInput);
                                            if (taskInput) {
                                                console.log(`Setting additional responsibility ${i} value to:`, responsibilities[i].trim());
                                                taskInput.value = responsibilities[i].trim();
                                            }

                                            // Update the number indicator
                                            const numberIndicator = newTask.querySelector('.input-number-indicator');
                                            if (numberIndicator) {
                                                numberIndicator.textContent = `#${i + 1}`;
                                            }

                                            // Show the remove button
                                            const removeBtn = newTask.querySelector('.remove-input');
                                            if (removeBtn) {
                                                removeBtn.style.display = 'block';

                                                // Add event listener for the remove button
                                                removeBtn.addEventListener('click', function() {
                                                    taskContainer.removeChild(newTask);

                                                    // Update numbering
                                                    const inputs = taskContainer.querySelectorAll('.input-group');
                                                    inputs.forEach((input, index) => {
                                                        const numberIndicator = input.querySelector('.input-number-indicator');
                                                        if (numberIndicator) {
                                                            numberIndicator.textContent = `#${index + 1}`;
                                                        }
                                                    });
                                                });
                                            }

                                            // Append to container
                                            taskContainer.appendChild(newTask);
                                            console.log(`Added additional responsibility ${i} to task container`);
                                        }
                                    }
                                }
                            }
                        }

                        // Add additional assistant project leaders
                        for (let i = 1; i < assistantLeaders.length; i++) {
                            // Clone the first card
                            const newCard = firstAssistantCard.cloneNode(true);

                            // Update card title
                            const cardTitle = newCard.querySelector('.card-header h6');
                            if (cardTitle) {
                                cardTitle.textContent = `Assistant Project Leader #${i + 1}`;
                            }

                            // Update input IDs and names for the new card
                            newCard.querySelectorAll('input, select, textarea').forEach(input => {
                                if (input.id && input.id.includes('_1')) {
                                    input.id = input.id.replace('_1', `_${i + 1}`);
                                }
                                if (input.name && input.name.includes('_1[')) {
                                    input.name = input.name.replace('_1[', `_${i + 1}[`);
                                }
                            });

                            // Update task container ID
                            const taskContainer = newCard.querySelector('.tasks-container');
                            if (taskContainer && taskContainer.id) {
                                taskContainer.id = `asstLeaderTasksContainer_${i + 1}`;
                            }

                            // Update task button data-index
                            const taskButton = newCard.querySelector('.add-task-btn');
                            if (taskButton) {
                                taskButton.dataset.index = i + 1;
                            }

                            // Set name value
                            const nameInput = newCard.querySelector('input[name="asst_leader_name[]"]');
                            if (nameInput) {
                                nameInput.value = assistantLeaders[i];

                                // Create a change event to trigger the autocomplete handlers
                                const event = new Event('change', {
                                    bubbles: true
                                });
                                nameInput.dispatchEvent(event);

                                // Additionally, explicitly fetch personnel data for this name
                                fetchPersonnelDataByName(assistantLeaders[i], 'asst_leader', i + 1);
                            }

                            // Process responsibilities for this assistant leader
                            if (Array.isArray(assistantLeaderResponsibilities) && assistantLeaderResponsibilities.length > i) {
                                const taskContainer = newCard.querySelector(`.tasks-container`);
                                if (taskContainer) {
                                    // Get the first task input
                                    const firstTaskInput = taskContainer.querySelector('.input-group');

                                    if (firstTaskInput) {
                                        // Clear existing tasks
                                        while (taskContainer.children.length > 0) {
                                            taskContainer.removeChild(taskContainer.lastChild);
                                        }

                                        // Check the data format and handle both string and array formats
                                        let responsibilities = [];
                                        const respItem = assistantLeaderResponsibilities[i];

                                        console.log('Assistant leader responsibility item:', respItem, 'type:', typeof respItem);

                                        if (typeof respItem === 'string') {
                                            // If it's a string, split by commas
                                            responsibilities = respItem.split(',');
                                        } else if (Array.isArray(respItem)) {
                                            // If it's an array, use it directly
                                            responsibilities = respItem;
                                        } else {
                                            // If it's anything else, convert to string and split
                                            responsibilities = String(respItem).split(',');
                                        }

                                        console.log('Processed additional assistant leader responsibilities:', responsibilities);

                                        // Add tasks for each responsibility
                                        for (let j = 0; j < responsibilities.length; j++) {
                                            // Clone the template
                                            const newTask = firstTaskInput.cloneNode(true);

                                            // Update the name attribute
                                            const input = newTask.querySelector('input');
                                            console.log(`Additional assistant leader ${i+1}, task ${j+1} input found?`, !!input);
                                            if (input) {
                                                input.name = `asst_leader_tasks_${i + 1}[]`;
                                                console.log(`Setting additional assistant leader ${i+1}, task ${j+1} value to:`, responsibilities[j].trim());
                                                input.value = responsibilities[j].trim();
                                            }

                                            // Update the number indicator
                                            const numberIndicator = newTask.querySelector('.input-number-indicator');
                                            if (numberIndicator) {
                                                numberIndicator.textContent = `#${j + 1}`;
                                            }

                                            // Show the remove button for tasks after the first one
                                            const removeBtn = newTask.querySelector('.remove-input');
                                            if (removeBtn) {
                                                removeBtn.style.display = j > 0 ? 'block' : 'none';

                                                // Add event listener for the remove button
                                                removeBtn.addEventListener('click', function() {
                                                    taskContainer.removeChild(newTask);

                                                    // Update numbering
                                                    const inputs = taskContainer.querySelectorAll('.input-group');
                                                    inputs.forEach((input, index) => {
                                                        const numberIndicator = input.querySelector('.input-number-indicator');
                                                        if (numberIndicator) {
                                                            numberIndicator.textContent = `#${index + 1}`;
                                                        }
                                                    });
                                                });
                                            }

                                            // Append to container
                                            taskContainer.appendChild(newTask);
                                        }
                                    }
                                }
                            }

                            // Show the remove button
                            const removeButton = newCard.querySelector('.remove-team-member');
                            if (removeButton) {
                                removeButton.style.display = 'block';

                                // Add event listener for the remove button
                                removeButton.addEventListener('click', function() {
                                    assistantLeadersContainer.removeChild(newCard);

                                    // Update numbering of remaining cards
                                    const cards = assistantLeadersContainer.querySelectorAll('.team-member-card');
                                    cards.forEach((card, index) => {
                                        const title = card.querySelector('.card-header h6');
                                        if (title) {
                                            title.textContent = `Assistant Project Leader #${index + 1}`;
                                        }
                                    });
                                });
                            }

                            // Append to container
                            assistantLeadersContainer.appendChild(newCard);
                        }
                    }
                }

                // Project Staff
                if (entry.project_staff_coordinator) {
                    // Parse project staff data
                    let projectStaff = entry.project_staff_coordinator;
                    let projectStaffResponsibilities = entry.project_staff_coordinator_responsibilities;

                    // Handle string format (convert to JSON)
                    if (typeof projectStaff === 'string') {
                        try {
                            projectStaff = JSON.parse(projectStaff);
                        } catch (e) {
                            console.error('Error parsing project staff:', e);
                        }
                    }

                    if (typeof projectStaffResponsibilities === 'string') {
                        try {
                            projectStaffResponsibilities = JSON.parse(projectStaffResponsibilities);
                        } catch (e) {
                            console.error('Error parsing project staff responsibilities:', e);
                        }
                    }

                    // Get container reference
                    const projectStaffContainer = document.getElementById('projectStaffContainer');

                    // Clear existing content, keeping the first template
                    const firstStaffCard = projectStaffContainer.querySelector('.team-member-card');
                    if (firstStaffCard) {
                        // Clear all cards except the first one
                        while (projectStaffContainer.children.length > 1) {
                            projectStaffContainer.removeChild(projectStaffContainer.lastChild);
                        }

                        // Populate the first card with the first staff member
                        if (Array.isArray(projectStaff) && projectStaff.length > 0) {
                            // Set name and trigger autocomplete to populate other fields
                            const nameInput = firstStaffCard.querySelector('input[name="staff_name[]"]');
                            if (nameInput) {
                                nameInput.value = projectStaff[0];

                                // Create a change event to trigger the autocomplete handlers
                                const event = new Event('change', {
                                    bubbles: true
                                });
                                nameInput.dispatchEvent(event);

                                // Additionally, explicitly fetch personnel data for this name
                                fetchPersonnelDataByName(projectStaff[0], 'staff', 1);
                            }

                            // Process responsibilities for the first staff member
                            if (Array.isArray(projectStaffResponsibilities) && projectStaffResponsibilities.length > 0) {
                                const taskContainer = firstStaffCard.querySelector('#staffTasksContainer_1');
                                if (taskContainer) {
                                    // Get the first task input
                                    const firstTaskInput = taskContainer.querySelector('.input-group');

                                    if (firstTaskInput) {
                                        // Check the data format and handle both string and array formats
                                        let responsibilities = [];
                                        const respItem = projectStaffResponsibilities[0];

                                        console.log('First project staff responsibility item:', respItem, 'type:', typeof respItem);

                                        if (typeof respItem === 'string') {
                                            // If it's a string, split by commas
                                            responsibilities = respItem.split(',');
                                        } else if (Array.isArray(respItem)) {
                                            // If it's an array, use it directly
                                            responsibilities = respItem;
                                        } else {
                                            // If it's anything else, convert to string and split
                                            responsibilities = String(respItem).split(',');
                                        }

                                        console.log('Processed first project staff responsibilities:', responsibilities);

                                        // Set first responsibility
                                        const firstTaskField = firstTaskInput.querySelector('input[name="staff_tasks_1[]"]');
                                        if (firstTaskField && responsibilities.length > 0) {
                                            firstTaskField.value = responsibilities[0].trim();
                                        }

                                        // Add additional responsibilities
                                        for (let i = 1; i < responsibilities.length; i++) {
                                            // Clone the first input
                                            const newTask = firstTaskInput.cloneNode(true);

                                            // Set the value
                                            const taskInput = newTask.querySelector('input[name="staff_tasks_1[]"]');
                                            if (taskInput) {
                                                taskInput.value = responsibilities[i].trim();
                                            }

                                            // Update the number indicator
                                            const numberIndicator = newTask.querySelector('.input-number-indicator');
                                            if (numberIndicator) {
                                                numberIndicator.textContent = `#${i + 1}`;
                                            }

                                            // Show the remove button
                                            const removeBtn = newTask.querySelector('.remove-input');
                                            if (removeBtn) {
                                                removeBtn.style.display = 'block';

                                                // Add event listener for the remove button
                                                removeBtn.addEventListener('click', function() {
                                                    taskContainer.removeChild(newTask);

                                                    // Update numbering
                                                    const inputs = taskContainer.querySelectorAll('.input-group');
                                                    inputs.forEach((input, index) => {
                                                        const numberIndicator = input.querySelector('.input-number-indicator');
                                                        if (numberIndicator) {
                                                            numberIndicator.textContent = `#${index + 1}`;
                                                        }
                                                    });
                                                });
                                            }

                                            // Append to container
                                            taskContainer.appendChild(newTask);
                                        }
                                    }
                                }
                            }
                        }

                        // Add additional staff members
                        for (let i = 1; i < projectStaff.length; i++) {
                            // Clone the first card
                            const newCard = firstStaffCard.cloneNode(true);

                            // Update card title
                            const cardTitle = newCard.querySelector('.card-header h6');
                            if (cardTitle) {
                                cardTitle.textContent = `Project Staff/Coordinator #${i + 1}`;
                            }

                            // Update input IDs and names for the new card
                            newCard.querySelectorAll('input, select, textarea').forEach(input => {
                                if (input.id && input.id.includes('_1')) {
                                    input.id = input.id.replace('_1', `_${i + 1}`);
                                }
                                if (input.name && input.name.includes('_1[')) {
                                    input.name = input.name.replace('_1[', `_${i + 1}[`);
                                }
                            });

                            // Update task container ID
                            const taskContainer = newCard.querySelector('.tasks-container');
                            if (taskContainer && taskContainer.id) {
                                taskContainer.id = `staffTasksContainer_${i + 1}`;
                            }

                            // Update task button data-index
                            const taskButton = newCard.querySelector('.add-task-btn');
                            if (taskButton) {
                                taskButton.dataset.index = i + 1;
                            }

                            // Set name value
                            const nameInput = newCard.querySelector('input[name="staff_name[]"]');
                            if (nameInput) {
                                nameInput.value = projectStaff[i];

                                // Create a change event to trigger the autocomplete handlers
                                const event = new Event('change', {
                                    bubbles: true
                                });
                                nameInput.dispatchEvent(event);

                                // Additionally, explicitly fetch personnel data for this name
                                fetchPersonnelDataByName(projectStaff[i], 'staff', i + 1);
                            }

                            // Process responsibilities for this staff member
                            if (Array.isArray(projectStaffResponsibilities) && projectStaffResponsibilities.length > i) {
                                const taskContainer = newCard.querySelector(`.tasks-container`);
                                if (taskContainer) {
                                    // Get the first task input
                                    const firstTaskInput = taskContainer.querySelector('.input-group');

                                    if (firstTaskInput) {
                                        // Clear existing tasks
                                        while (taskContainer.children.length > 0) {
                                            taskContainer.removeChild(taskContainer.lastChild);
                                        }

                                        // Check the data format and handle both string and array formats
                                        let responsibilities = [];
                                        const respItem = projectStaffResponsibilities[i];

                                        console.log('Additional project staff responsibility item:', respItem, 'type:', typeof respItem);

                                        if (typeof respItem === 'string') {
                                            // If it's a string, split by commas
                                            responsibilities = respItem.split(',');
                                        } else if (Array.isArray(respItem)) {
                                            // If it's an array, use it directly
                                            responsibilities = respItem;
                                        } else {
                                            // If it's anything else, convert to string and split
                                            responsibilities = String(respItem).split(',');
                                        }

                                        console.log('Processed additional project staff responsibilities:', responsibilities);

                                        // Add tasks for each responsibility
                                        for (let j = 0; j < responsibilities.length; j++) {
                                            // Clone the template
                                            const newTask = firstTaskInput.cloneNode(true);

                                            // Update the name attribute
                                            const input = newTask.querySelector('input');
                                            if (input) {
                                                input.name = `staff_tasks_${i + 1}[]`;
                                                input.value = responsibilities[j].trim();
                                            }

                                            // Update the number indicator
                                            const numberIndicator = newTask.querySelector('.input-number-indicator');
                                            if (numberIndicator) {
                                                numberIndicator.textContent = `#${j + 1}`;
                                            }

                                            // Show the remove button for tasks after the first one
                                            const removeBtn = newTask.querySelector('.remove-input');
                                            if (removeBtn) {
                                                removeBtn.style.display = j > 0 ? 'block' : 'none';

                                                // Add event listener for the remove button
                                                removeBtn.addEventListener('click', function() {
                                                    taskContainer.removeChild(newTask);

                                                    // Update numbering
                                                    const inputs = taskContainer.querySelectorAll('.input-group');
                                                    inputs.forEach((input, index) => {
                                                        const numberIndicator = input.querySelector('.input-number-indicator');
                                                        if (numberIndicator) {
                                                            numberIndicator.textContent = `#${index + 1}`;
                                                        }
                                                    });
                                                });
                                            }

                                            // Append to container
                                            taskContainer.appendChild(newTask);
                                        }
                                    }
                                }
                            }

                            // Show the remove button
                            const removeButton = newCard.querySelector('.remove-team-member');
                            if (removeButton) {
                                removeButton.style.display = 'block';

                                // Add event listener for the remove button
                                removeButton.addEventListener('click', function() {
                                    projectStaffContainer.removeChild(newCard);

                                    // Update numbering of remaining cards
                                    const cards = projectStaffContainer.querySelectorAll('.team-member-card');
                                    cards.forEach((card, index) => {
                                        const title = card.querySelector('.card-header h6');
                                        if (title) {
                                            title.textContent = `Project Staff/Coordinator #${index + 1}`;
                                        }
                                    });
                                });
                            }

                            // Append to container
                            projectStaffContainer.appendChild(newCard);
                        }
                    }
                }

                // Update project team section status after processing all personnel
                updateSectionStatus('project-team', true);

                updateInternalParticipantsFromProjectTeam();
            }

            // Helper function to mark gender issue as valid
            function markGenderIssueAsValid() {
                const genderIssueSelect = document.getElementById('genderIssue');
                if (!genderIssueSelect) return;

                // Remove is-invalid class
                genderIssueSelect.classList.remove('is-invalid');

                // Remove any validation messages
                const feedback = genderIssueSelect.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.remove();
                }

                // Mark the section as valid in the navigation
                const navItem = document.querySelector('.form-nav-item[data-section="gender-issue"]');
                if (navItem) {
                    navItem.classList.remove('has-error');
                    navItem.classList.add('is-complete');
                }

                // Mark the section title as valid
                const sectionTitle = document.querySelector('#gender-issue .section-title');
                if (sectionTitle) {
                    sectionTitle.classList.remove('has-error');
                }
            }

            // Enhanced version of checkDuplicateActivity that handles edit mode correctly
            function checkDuplicateActivityWithEditMode(activityValue) {
                const activityInput = document.getElementById('activity');
                const errorDiv = document.getElementById('activity-error');

                // Reset validation state
                activityInput.classList.remove('is-invalid');
                errorDiv.textContent = '';

                // Don't check if empty
                if (!activityValue) {
                    activityInput.dataset.isDuplicate = 'false';
                    updateSectionStatus('gender-issue', checkAllFieldsFilled('gender-issue'));
                    return;
                }

                // Build URL with edit mode consideration
                let url = `check_duplicate_activity.php?activity=${encodeURIComponent(activityValue)}`;
                if (editMode && editingEntryId) {
                    url += `&id=${editingEntryId}`;
                }

                // Check if activity exists
                return fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.exists) {
                            // Mark as invalid if activity exists
                            activityInput.classList.add('is-invalid');
                            errorDiv.textContent = data.message || 'This activity already exists.';
                            activityInput.dataset.isDuplicate = 'true';

                            // Mark gender issue section as incomplete
                            const navItem = document.querySelector('.form-nav-item[data-section="gender-issue"]');
                            if (navItem) {
                                navItem.classList.remove('is-complete');
                                if (validationTriggered) {
                                    navItem.classList.add('has-error');

                                    // Add error to section title
                                    const sectionTitle = document.querySelector('#gender-issue .section-title');
                                    if (sectionTitle) {
                                        sectionTitle.classList.add('has-error');
                                    }
                                }
                            }
                            return true; // Is duplicate
                        } else {
                            // Clear any previous error
                            activityInput.classList.remove('is-invalid');
                            errorDiv.textContent = '';
                            activityInput.dataset.isDuplicate = 'false';

                            // Update gender issue section status
                            updateSectionStatus('gender-issue', checkAllFieldsFilled('gender-issue'));
                            return false; // Not duplicate
                        }
                    })
                    .catch(error => {
                        console.error('Error checking duplicate activity:', error);
                        activityInput.classList.remove('is-invalid');
                        errorDiv.textContent = '';
                        activityInput.dataset.isDuplicate = 'false';
                        updateSectionStatus('gender-issue', checkAllFieldsFilled('gender-issue'));
                        return false; // Assume not duplicate on error
                    });
            }

            function enterEditMode(entry) {
                editMode = true;
                editingEntryId = entry.id;
                originalFormState = saveFormState();

                // Debug the entry object
                console.log('ENTRY OBJECT:', entry);
                console.log('Project Leader:', entry.project_leader);
                console.log('Project Leader Responsibilities:', entry.project_leader_responsibilities);
                console.log('Assistant Project Leader:', entry.assistant_project_leader);
                console.log('Assistant Project Leader Responsibilities:', entry.assistant_project_leader_responsibilities);
                console.log('Project Staff:', entry.project_staff_coordinator);
                console.log('Project Staff Responsibilities:', entry.project_staff_coordinator_responsibilities);

                populateForm(entry);

                // Run validation immediately, but we'll have a special case for gender issue
                if (entry.gender_issue_id) {
                    // Mark the gender issue field as valid regardless of fetch completion
                    markGenderIssueAsValid();
                }

                // Run validation now
                validateAllSections();

                // Change title
                document.querySelector('.card-title').textContent = 'Edit PPAs Form';
                // Disable deleteBtn
                document.getElementById('deleteBtn').classList.add('disabled');
                document.getElementById('deleteBtn').disabled = true;
                // Change editBtn to X (cancel) with red palette
                const editBtn = document.getElementById('editBtn');
                editBtn.innerHTML = '<i class="fas fa-times"></i>';
                editBtn.classList.add('editing');
                editBtn.title = 'Cancel Edit';
                // Change addBtn to updateBtn (icon, color)
                const addBtn = document.getElementById('addBtn');
                addBtn.innerHTML = '<i class="fas fa-save"></i>';
                addBtn.classList.add('btn-update');
                addBtn.title = 'Update Entry';
            }

            function exitEditMode() {
                editMode = false;
                editingEntryId = null;

                // Clear validation state first
                validationTriggered = false;

                // Remove all validation error styling
                document.querySelectorAll('.is-invalid').forEach(element => {
                    element.classList.remove('is-invalid');
                });

                // Remove all error messages
                document.querySelectorAll('.invalid-feedback').forEach(element => {
                    element.remove();
                });

                // Remove error styling from section titles
                document.querySelectorAll('.section-title.has-error').forEach(element => {
                    element.classList.remove('has-error');
                });

                // Remove error styling from navigation items
                document.querySelectorAll('.form-nav-item.has-error').forEach(element => {
                    element.classList.remove('has-error');
                });

                // Remove completion styling from navigation items
                document.querySelectorAll('.form-nav-item.is-complete').forEach(element => {
                    element.classList.remove('is-complete');
                });

                // Reset all section status indicators
                const sections = ['gender-issue', 'basic-info', 'agenda-section', 'sdgs-section', 'office-programs', 'project-team', 'section-6', 'section-7', 'section-8', 'section-9', 'section-10', 'section-11'];
                sections.forEach(sectionId => {
                    const navItem = document.querySelector(`.form-nav-item[data-section="${sectionId}"]`);
                    if (navItem) {
                        navItem.classList.remove('is-complete');
                        navItem.classList.remove('has-error');
                    }

                    // Clear section title error indicators
                    const sectionTitle = document.querySelector(`#${sectionId} .section-title`);
                    if (sectionTitle) {
                        sectionTitle.classList.remove('has-error');
                    }
                });

                // Clear workplan timeline table before form reset
                clearWorkPlanTimeline();

                // Reset Monitoring Section
                // Remove all dynamically added monitoring item cards (keep only the first one)
                const monitoringItemsContainer = document.getElementById('monitoring-items-container');
                if (monitoringItemsContainer) {
                    const monitoringItems = monitoringItemsContainer.querySelectorAll('.monitoring-item');

                    // Keep only the first monitoring item card and remove the rest
                    for (let i = 1; i < monitoringItems.length; i++) {
                        monitoringItemsContainer.removeChild(monitoringItems[i]);
                    }

                    // Clear inputs in the first monitoring item card
                    if (monitoringItems.length > 0) {
                        const firstItem = monitoringItems[0];
                        firstItem.querySelectorAll('textarea, input').forEach(input => {
                            input.value = '';
                        });
                    }

                    console.log('Cleared monitoring items section');
                }

                // Reset Financial Requirements Section
                // 1. Clear financial plan items table
                const financialPlanTableBody = document.getElementById('financialPlanTableBody');
                if (financialPlanTableBody) {
                    financialPlanTableBody.innerHTML = '';
                }

                // Hide the financial plan section and show the empty message
                const financialPlanSection = document.getElementById('financialPlanSection');
                const emptyFinancialPlanMessage = document.getElementById('emptyFinancialPlanMessage');
                const financialPlanTable = document.getElementById('financialPlanTable');

                if (financialPlanSection) financialPlanSection.style.display = 'none';
                if (emptyFinancialPlanMessage) emptyFinancialPlanMessage.style.display = 'flex';
                if (financialPlanTable) financialPlanTable.style.display = 'none';

                // 2. Deselect all source of fund options
                const sourceFundOptions = document.querySelectorAll('.source-fund-option');
                const sourceOfFundInput = document.getElementById('sourceOfFund');

                // Remove selected class from all options
                sourceFundOptions.forEach(option => {
                    option.classList.remove('selected');
                });

                // Clear the hidden input value
                if (sourceOfFundInput) {
                    sourceOfFundInput.value = '';
                }

                // 3. Uncheck both financial plan radio buttons
                const withFinancialPlanRadio = document.getElementById('withFinancialPlan');
                const withoutFinancialPlanRadio = document.getElementById('withoutFinancialPlan');

                if (withFinancialPlanRadio) withFinancialPlanRadio.checked = false;
                if (withoutFinancialPlanRadio) withoutFinancialPlanRadio.checked = false;

                // 4. Reset total cost value
                const totalCostInput = document.getElementById('totalCost');
                const grandTotalCost = document.getElementById('grandTotalCost');

                if (totalCostInput) totalCostInput.value = '0.00';
                if (grandTotalCost) grandTotalCost.textContent = '₱0.00';

                resetForm(); // <-- This will clear all fields!
                // Change title
                document.querySelector('.card-title').textContent = 'Add PPAs Form';
                // Enable deleteBtn
                document.getElementById('deleteBtn').classList.remove('disabled');
                document.getElementById('deleteBtn').disabled = false;
                // Change editBtn back to edit icon
                const editBtn = document.getElementById('editBtn');
                editBtn.innerHTML = '<i class="fas fa-edit"></i>';
                editBtn.classList.remove('editing');
                editBtn.title = 'Edit Entry';
                // Change addBtn back to add icon
                const addBtn = document.getElementById('addBtn');
                addBtn.innerHTML = '<i class="fas fa-plus"></i>';
                addBtn.classList.remove('btn-update');
                addBtn.title = 'Add Entry';
            }

            // Function to clear the workplan timeline table
            function clearWorkPlanTimeline() {
                console.log("Clearing workplan timeline table...");

                // Clear the activity rows
                const timelineActivities = document.getElementById('timeline-activities');
                if (timelineActivities) {
                    timelineActivities.innerHTML = '';
                }

                // Clear the table header (except first column)
                const headerRow = document.querySelector('#timeline-table thead tr');
                if (headerRow) {
                    // Keep only the first cell (Activity column)
                    while (headerRow.children.length > 1) {
                        headerRow.removeChild(headerRow.lastChild);
                    }
                }

                // Hide table and show message
                const timelineMessage = document.getElementById('timeline-message');
                const timelineTableContainer = document.getElementById('timeline-table-container');

                if (timelineMessage) {
                    timelineMessage.textContent = "Please select start and end dates in the Basic Info section to generate timeline.";
                    timelineMessage.style.display = 'block';
                }

                if (timelineTableContainer) {
                    timelineTableContainer.style.display = 'none';
                }

                // Reset the stored workplan data
                window.tempWorkplanActivity = null;
                window.tempWorkplanDate = null;

                console.log("Workplan timeline table cleared");
            }

            document.getElementById('editBtn').addEventListener('click', function() {
                if (editMode) {
                    exitEditMode();
                    // Navigate back to gender issue section and scroll to top
                    navigateToSection('gender-issue');
                } else {
                    showPpasEntriesModal('edit');
                }
            });

            const origDisplayEntries = displayEntries;
            displayEntries = function(page) {
                origDisplayEntries(page);
                const tableBody = document.getElementById('ppasEntriesTableBody');
                Array.from(tableBody.querySelectorAll('tr')).forEach(row => {
                    row.addEventListener('click', function() {
                        if (currentMode === 'edit') {
                            const entryId = this.getAttribute('data-id');
                            fetch('get_ppas_entry.php?id=' + entryId)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success && data.data) {
                                        enterEditMode(data.data);
                                        const modal = bootstrap.Modal.getInstance(document.getElementById('ppasEntriesModal'));
                                        if (modal) modal.hide();
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: data.message || 'Could not fetch entry data',
                                            icon: 'error',
                                            confirmButtonColor: '#6a1b9a'
                                        });
                                    }
                                });
                        }
                    });
                });
            };
            // === END EDIT MODE LOGIC ===
        </script>
</body>

</html>