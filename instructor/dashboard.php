<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch instructor name
$stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_name = htmlspecialchars($user['name'] ?? 'Instructor');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Instructor Dashboard - DevGeeks</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        /* Reset & base */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            color: #334155;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Header */
        header {
            background-color: #0f172a;
            padding: 1rem 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgb(0 0 0 / 0.15);
        }

        header .logo {
            font-weight: 700;
            font-size: 1.8rem;
            letter-spacing: 1.5px;
            user-select: none;
        }

        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 1.2rem;
        }

        nav ul li a {
            font-weight: 600;
            color: #cbd5e1;
            padding: 0.4rem 0.7rem;
            border-radius: 4px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        nav ul li a:hover {
            background-color: #3b82f6;
            color: white;
        }

        /* Main container */
        main.container {
            max-width: 960px;
            margin: 3rem auto;
            background: white;
            padding: 2.5rem 3rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgb(0 0 0 / 0.1);
        }

        main h2 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.25rem;
            color: #1e293b;
        }

        main h3 {
            font-weight: 500;
            font-size: 1.125rem;
            margin-top: 0;
            margin-bottom: 2rem;
            color: #64748b;
        }

        /* Dashboard grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
        }

        .dashboard-card {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 1.8rem 1.5rem;
            box-shadow: 0 4px 12px rgb(0 0 0 / 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.05rem;
            color: #334155;
            cursor: pointer;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
            user-select: none;
            border: 1px solid transparent;
        }

        .dashboard-card:hover,
        .dashboard-card:focus {
            box-shadow: 0 8px 24px rgb(59 130 246 / 0.3);
            transform: translateY(-4px);
            border-color: #3b82f6;
            outline: none;
        }

        footer {
            background: #1e293b;
            color: #cbd5e1;
            padding: 3rem 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }

        footer a {
            color: #94a3b8;
        }

        footer a:hover,
        footer a:focus {
            color: #60a5fa;
            outline: none;
        }

        @media (max-width: 600px) {
            .hero h1 {
                font-size: 2.4rem;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="logo">DevGeeks Instructor</div>
        <nav>
            <ul>
                <li><a href="../index.php" tabindex="1">Home</a></li>
                <li><a href="../logout.php" tabindex="2">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="container" role="main" aria-label="Instructor Dashboard">
        <h2>Welcome, <?= $user_name ?>!</h2>
        <h3>Instructor Dashboard</h3>

        <div class="dashboard-grid">
            <a href="mycourses.php" class="dashboard-card" tabindex="3" aria-label="My Courses">My Courses</a>
            <a href="module_manage.php" class="dashboard-card" tabindex="5"
                aria-label="Manage Modules & Materials">Manage Modules & Materials</a>
            <a href="add_quiz.php" class="dashboard-card" tabindex="6" aria-label="Add Quiz">Add Quiz</a>
            <a href="feedback_view.php" class="dashboard-card" tabindex="7" aria-label="View Feedback">View Feedback</a>
            <a href="notices.php" class="dashboard-card" tabindex="8" aria-label="Post Notices">Post Notices</a>
            <a href="notification_view.php" class="dashboard-card" tabindex="9" aria-label="View Notifications">View
                Notifications</a>
        </div>
    </main>
    <footer
        style="background:#1E293B; color:#F1F5F9; padding:60px 24px; font-family:'Poppins', sans-serif; font-size:16px; line-height:1.6;">
        <div
            style="max-width:1200px; margin:0 auto; display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:40px; text-align:left;">

            <div>
                <h3 style="font-size:22px; font-weight:700; margin-bottom:20px; color:#60A5FA;">About DevGeeks</h3>
                <p style="color:#CBD5E1; font-size:15px; margin-bottom:14px;">
                    DevGeeks is an innovative online education platform empowering learners worldwide with expert tech
                    courses and hands-on skills for today’s digital challenges.
                </p>
            </div>

            <div>
                <h3 style="font-size:22px; font-weight:700; margin-bottom:20px; color:#60A5FA;">Quick Links</h3>
                <ul style="list-style:none; padding:0; margin:0; color:#CBD5E1; font-size:15px;">
                    <li><a href="index.php"
                            style="color:#CBD5E1; text-decoration:none; transition:color 0.3s ease;">Home</a></li>
                    <li><a href="courses.php"
                            style="color:#CBD5E1; text-decoration:none; transition:color 0.3s ease;">Courses</a></li>
                    <li><a href="about.php"
                            style="color:#CBD5E1; text-decoration:none; transition:color 0.3s ease;">About Us</a></li>
                    <li><a href="contact.php"
                            style="color:#CBD5E1; text-decoration:none; transition:color 0.3s ease;">Contact</a></li>
                    <li><a href="faq.php"
                            style="color:#CBD5E1; text-decoration:none; transition:color 0.3s ease;">FAQ</a></li>
                </ul>
            </div>

            <div>
                <h3 style="font-size:22px; font-weight:700; margin-bottom:20px; color:#60A5FA;">Contact Us</h3>
                <p style="color:#CBD5E1; font-size:15px; margin-bottom:8px;">
                    <strong>Address:</strong> 123 Zinda Bazar, Sylhet 3100, Bangladesh
                </p>
                <p style="color:#CBD5E1; font-size:15px; margin-bottom:8px;">
                    <strong>Phone:</strong> +880 1712-345678
                </p>
                <p style="color:#CBD5E1; font-size:15px; margin-bottom:8px;">
                    <strong>Email:</strong> <a href="mailto:support@devgeeks.com"
                        style="color:#60A5FA; text-decoration:none;">support@devgeeks.com</a>
                </p>
            </div>
        </div>
        <div>
            <h3 style="color:#60A5FA;">Find Us</h3>
            <div class="social-icons">
                <a href="https://facebook.com" target="_blank" aria-label="Facebook"><i
                        class="fab fa-facebook-f"></i></a>
                <a href="https://twitter.com" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="https://linkedin.com" target="_blank" aria-label="LinkedIn"><i
                        class="fab fa-linkedin-in"></i></a>
                <a href="https://instagram.com" target="_blank" aria-label="Instagram"><i
                        class="fab fa-instagram"></i></a>
                <a href="https://youtube.com" target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        <div style="margin-top:50px; text-align:center; font-size:14px; color:#94A3B8; letter-spacing:0.05em;">
            &copy; 2025 DevGeeks. All rights reserved.
        </div>
    </footer>
</body>

</html>
