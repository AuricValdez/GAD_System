<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Create database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    if (!empty($username) && !empty($password)) {
        $sql = "SELECT * FROM credentials WHERE BINARY username = ? AND BINARY password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $row['id'];
            header("Location: loading_screen.php?to=dashboard/dashboard.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid credentials. Please try again.";
        }
    }
    
    // Close the connection
    $conn->close();
    
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GAD System</title>
    <style>
        /* Prevent theme flash */
        html[data-theme="dark"] {
            background-color: #1a1a1a !important;
        }
    </style>
    <script>
        // Immediate theme loading
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = savedTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        })();
    </script>
    <link rel="icon" type="image/x-icon" href="/images/Batangas_State_Logo.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Root Variables */
        :root {
            --bg-primary: #f8f9fa;
            --bg-secondary: #ffffff;
            --text-primary: #333333;
            --text-secondary: #6c757d;
            --accent-color: #6a1b9a;
            --accent-hover: #4a148c;
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --scrollbar-thumb: #cccccc;
            --scrollbar-thumb-hover: #aaaaaa;
        }
        
        /* Light Theme Variables */
        [data-theme="light"] {
            --bg-primary: #f0f0f0;
            --bg-secondary: #e5e5e5;
            --text-primary: #444444;
            --text-secondary: #666666;
            --card-bg: white;
            --border-color: #cccccc;
            --input-bg: #ffffff;
            --input-text: #444444;
            --input-placeholder: #666666;
            --overlay-color: rgba(255, 255, 255, 0.3);
        }

        /* Dark Theme Variables */
        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --card-bg: #2d2d2d;
            --border-color: #404040;
            --input-bg: #333333;
            --input-border: #444444;
            --input-text: #ffffff;
            --input-placeholder: #888888;
            --overlay-color: rgba(0, 0, 0, 0.7);
            --scrollbar-thumb: #404040;
            --scrollbar-thumb-hover: #555555;
            --accent-color: #9c27b0;
            --accent-hover: #7b1fa2;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-color: transparent;
            color: var(--text-primary);
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(var(--overlay-color), var(--overlay-color)), 
                        url('images/campus1.jpg');
            background-size: cover;
            background-position: center;
            filter: blur(8px);
            z-index: -1;
            pointer-events: none;
        }

        .login-container {
            width: 100%;
            max-width: 900px;
            display: flex;
            background: var(--card-bg);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
        }

        .login-form {
            flex: 1;
            padding: 50px;
            position: relative;
        }

        .login-image {
            flex: 1;
            background: linear-gradient(135deg, rgba(106, 27, 154, 0.9) 0%, rgba(74, 20, 140, 0.9) 100%), 
                        url('images/campus2.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
            animation: slideInRight 1s ease-out;
        }

        /* Animation Keyframes */
        @keyframes slideInRight {
            0% {
                transform: translateX(100%);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .logo-image {
            width: 250px;
            height: 250px;
            position: relative;
            z-index: 2;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.4));
        }

        .logo-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .text-center.mb-4 {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .welcome-text {
            font-size: 1.2rem;
            margin-bottom: 5px;
            text-align: center;
            width: 100%;
        }

        .gad-title {
            color: var(--accent-color);
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
            text-align: center;
            width: 100%;
        }

        .gad-subtitle {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 30px;
            text-align: center;
            width: 120%;
            padding: 0 10%;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        /* Add focus styles for input fields */
        .form-control:focus,
        .form-select:focus,
        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(106, 27, 154, 0.25) !important;
            outline: none !important;
        }

        [data-theme="dark"] .form-control:focus,
        [data-theme="dark"] .form-select:focus,
        [data-theme="dark"] input:focus,
        [data-theme="dark"] select:focus,
        [data-theme="dark"] textarea:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(156, 39, 176, 0.25) !important;
        }

        .password-toggle {
            position: absolute;
            right: 0;
            top: 0;
            height: 50px;
            width: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            cursor: pointer;
            z-index: 10;
        }

        .password-toggle:hover {
            color: var(--accent-color);
        }

        .form-control {
            height: 50px;
            padding: 10px 20px;
            padding-right: 50px;
            border-radius: 10px !important;
            border: 1px solid var(--border-color);
            background-color: var(--input-bg);
            color: var(--input-text);
            width: 100%;
        }

        .form-control:disabled {
            background-color: var(--input-bg);
            cursor: not-allowed;
        }

        .campus-dropdown {
            position: absolute;
            right: 0;
            top: 0;
            height: 50px;
            width: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            cursor: pointer;
        }

        .campus-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            margin-top: 5px;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .campus-list.show {
            display: block;
        }

        .campus-item {
            padding: 10px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .campus-item:hover {
            background: var(--bg-secondary);
            color: var(--accent-color);
        }

        .btn-login {
            height: 50px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-hover) 100%);
            border: none;
            font-weight: 600;
            width: 100%;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, color 0.3s ease;
            color: white !important;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, transparent 0%, rgba(255, 255, 255, 0.2) 50%, transparent 100%);
            transition: left 0.5s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            color: white !important;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .theme-switch {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .theme-switch-button {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            cursor: pointer;
            padding: 12px;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .theme-switch-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .theme-switch-button i {
            font-size: 1.2rem;
        }

        .form-control::placeholder {
            color: var(--input-placeholder);
            opacity: 0.7;
        }

        /* Hide scrollbar for entire site */
        body::-webkit-scrollbar {
            width: 0;
            display: none;
        }
        
        body {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;     /* Firefox */
            overflow: hidden;
        }

        @media (max-width: 768px) {
            .login-container {
                max-width: 400px;
            }
            
            .login-image {
                display: none;
            }

            .login-form {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Theme Switch -->
    <div class="theme-switch">
        <button class="theme-switch-button" onclick="toggleTheme()">
            <i class="fas fa-sun" id="theme-icon"></i>
        </button>
    </div>
    
    <div class="login-container">
        <div class="login-form">
            <div class="text-center mb-4">
                <p class="welcome-text">Welcome to</p>
                <h2 class="gad-title">GAD SYSTEM</h2>
                <p class="gad-subtitle">Gender and Development Information System</p>
            </div>
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="alert alert-primary" role="alert" style="background-color: #e1bee7; color: #4a148c; border-color: #6a1b9a;">
                    <?php 
                    echo $_SESSION['login_error'];
                    unset($_SESSION['login_error']);
                    ?>
                </div>
            <?php endif; ?>
            <form action="index.php" method="POST">
                <div class="input-group">
                    <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                    <button type="button" class="campus-dropdown" onclick="toggleCampusList()">
                        <i class="fas fa-university"></i>
                    </button>
                    <div class="campus-list" id="campusList">
                        <div class="campus-item" onclick="selectCampus('Lipa')">Lipa</div>
                        <div class="campus-item" onclick="selectCampus('Pablo Borbon')">Pablo Borbon</div>
                        <div class="campus-item" onclick="selectCampus('Alangilan')">Alangilan</div>
                        <div class="campus-item" onclick="selectCampus('Nasugbu')">Nasugbu</div>
                        <div class="campus-item" onclick="selectCampus('Malvar')">Malvar</div>
                        <div class="campus-item" onclick="selectCampus('Rosario')">Rosario</div>
                        <div class="campus-item" onclick="selectCampus('Balayan')">Balayan</div>
                        <div class="campus-item" onclick="selectCampus('Lemery')">Lemery</div>
                        <div class="campus-item" onclick="selectCampus('San Juan')">San Juan</div>
                        <div class="campus-item" onclick="selectCampus('Lobo')">Lobo</div>
                        <div class="campus-item" onclick="selectCampus('Central')">Central</div>
                    </div>
                </div>
                <div class="input-group">
                    <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="password-toggle-icon"></i>
                    </button>
                </div>
                <button type="submit" class="btn btn-login">Sign In</button>
            </form>
        </div>
        <div class="login-image">
            <div class="logo-image">
                <img src="images/Batangas_State_Logo.png" alt="Batangas State Logo">
            </div>
        </div>
    </div>

    <script>
        // Theme switching functionality
        function toggleTheme() {
            const html = document.documentElement;
            const themeIcon = document.getElementById('theme-icon');
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update icon
            themeIcon.className = newTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }

        // Campus selection functionality
        function toggleCampusList() {
            const campusList = document.getElementById('campusList');
            campusList.style.display = campusList.style.display === 'none' ? 'block' : 'none';
        }

        function selectCampus(campus) {
            const usernameInput = document.getElementById('username');
            if (campus === '') {
                usernameInput.value = '';
                usernameInput.disabled = true;
            } else {
                usernameInput.value = campus;
                usernameInput.disabled = false;
                usernameInput.focus();
                // Move cursor to end of input
                const len = usernameInput.value.length;
                usernameInput.setSelectionRange(len, len);
            }
            toggleCampusList();
        }

        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('password-toggle-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const campusList = document.getElementById('campusList');
            const campusDropdown = document.querySelector('.campus-dropdown');
            if (!campusDropdown.contains(event.target) && !campusList.contains(event.target)) {
                campusList.style.display = 'none';
            }
        });
    </script>
</body>
</html>
