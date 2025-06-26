<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header('Location: index.html');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch username and email from the database
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user['username'];
$email = $user['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Welcome <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        #bgVideo {
            position: fixed;
            top: 0;
            left: 0;
            min-width: 100vw;
            min-height: 100vh;
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            z-index: -1;
            background: #000;
        }
        .container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: transparent;
        }
        .dashboard-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 600px;
            min-width: 0;
            margin: 40px 10px;
            padding: 40px 24px;
            background: transparent;
            border-radius: 15px;
            box-shadow: none;
        }
        .welcome-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            background: transparent;
            color: white;
            padding: 40px 10px 30px 10px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-sizing: border-box;
        }
        .welcome-header h1 {
            margin: 0;
            font-size: 3.5em;
            font-weight: 700;
            text-align: center;
            word-break: break-word;
            color: white;
            text-shadow: 0 2px 12px rgba(0,0,0,0.8);
            letter-spacing: 1px;
        }
        .username {
            font-size: 2em;
            font-weight: bold;
            margin-top: 20px;
            word-break: break-all;
            text-align: center;
            color: white;
            text-shadow: 0 2px 8px rgba(0,0,0,0.7);
        }
        .user-email {
            font-size: 1.3em;
            color: #fff;
            margin-top: 10px;
            font-weight: 400;
            letter-spacing: 0.5px;
            word-break: break-all;
            text-align: center;
            text-shadow: 0 2px 8px rgba(0,0,0,0.7);
        }
        .logout-btn {
            background: transparent;
            color: #fff;
            border: 2px solid #fff;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            font-weight: 500;
            text-shadow: 0 2px 8px rgba(0,0,0,0.7);
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
            border-color: #fff;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInGlow {
            from {
                opacity: 0;
                transform: translateY(20px);
                text-shadow: 0 0 25px rgba(255, 255, 255, 0.9),
                             0 0 50px rgba(255, 255, 255, 0.7),
                             0 0 80px rgba(255, 255, 255, 0.5),
                             0 2px 8px rgba(0,0,0,0.7);
            }
            to {
                opacity: 1;
                transform: translateY(0);
                text-shadow: 0 2px 8px rgba(0,0,0,0.7);
            }
        }

        /* Apply animations */
        .welcome-header h1,
        .username,
        .user-email,
        .logout-btn {
            opacity: 0;
            animation-duration: 1.0s;
            animation-fill-mode: forwards;
            animation-timing-function: ease-in-out;
        }

        .welcome-header h1 {
            animation-name: fadeIn;
            animation-delay: 0.2s;
        }

        .username {
            animation-name: fadeIn;
            animation-delay: 0.4s;
        }

        .user-email {
            animation-name: fadeIn;
            animation-delay: 0.6s;
        }

        .logout-btn {
            animation-name: fadeInGlow;
            animation-delay: 0.8s;
        }

        .line {
            width: 100%;
            height: 1px;
            background: rgba(255, 255, 255, 0.5);
            margin: 30px 0;
            opacity: 0;
            animation: fadeIn 1s ease-in-out 0.1s forwards;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 24px 6px;
                margin: 20px 2px;
                max-width: 98vw;
            }
            .welcome-header {
                padding: 30px 4px 20px 4px;
            }
            .welcome-header h1 {
                font-size: 2em;
            }
            .username {
                font-size: 1.3em;
            }
            .user-email {
                font-size: 1em;
            }
            .logout-btn {
                width: 100%;
                font-size: 1em;
                padding: 12px 0;
            }
        }

        /* Black fade-in overlay */
        .fade-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            z-index: 9999;
            opacity: 1;
            transition: opacity 1s ease-in-out;
        }

        .fade-overlay.fade-out {
            opacity: 0;
        }
    </style>
</head>
<body>
    <!-- Black fade-in overlay -->
    <div class="fade-overlay" id="fadeOverlay"></div>
    
    <video autoplay muted loop id="bgVideo">
        <source src="assets/darius.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="container">
        <div class="dashboard-container" id="dashboardContent">
            <div class="line"></div>
            <div class="welcome-header">
                <h1>Welcome Back!</h1>
                <div class="username"><?php echo htmlspecialchars($username); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($email); ?></div>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
            <div class="line"></div>
        </div>
    </div>

    <script>
        // Fade-in effect when dashboard loads
        window.addEventListener('load', function() {
            const overlay = document.getElementById('fadeOverlay');
            
            // After a brief delay, fade out the black overlay
            setTimeout(() => {
                overlay.classList.add('fade-out');
                
                // Remove overlay after fade completes
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 1000);
            }, 500);
        });
    </script>
</body>
</html> 