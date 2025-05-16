<!DOCTYPE html>
<html lang="en" data-theme="dark" data-bs-theme="dark">
<head>
    <link rel="icon" type="image/x-icon" href="/images/Batangas_State_Logo.ico">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading - GAD System</title>
    <!-- Force dark theme in localStorage before anything else runs -->
    <script>
        if (!localStorage.getItem('theme')) {
            localStorage.setItem('theme', 'dark');
        }
    </script>
    <style>
        /* Theme Variables */
        [data-theme="light"] {
            --bg-primary: #ffffff;
        }

        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
        }

        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--bg-primary);
            opacity: 0;
            transition: opacity 0.1s ease-in-out;
        }

        .loading-container {
            text-align: center;
        }

        .loading-logo {
            max-width: 175px;
            height: auto;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        .fade-out {
            opacity: 0;
        }
    </style>
    <script>
        // Set theme immediately to prevent flash
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();

        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.opacity = '1';
            
            const urlParams = new URLSearchParams(window.location.search);
            const redirectTo = urlParams.get('to') || urlParams.get('redirect') || 'dashboard/dashboard.php';
            
            setTimeout(() => {
                document.body.style.opacity = '1';
                document.body.offsetHeight;
                document.body.style.opacity = '0';
                
                setTimeout(() => {
                    window.location.href = redirectTo;
                }, 100);
            }, 1250);
        });
    </script>
</head>
<body>
    <div class="loading-container">
        <img src="images/loading_screen_logo.png" alt="Loading" class="loading-logo">
    </div>
</body>
</html>
