<?php
session_start();
require_once '../db.php';

// Redirect to login if not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

$user_name = htmlspecialchars($user['name']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard - DevGeeks</title>
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            background-color: #f9f5f5;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            color: #3b1a1a;
        }

        header {
            background: #800000;
            color: white;
            padding: 15px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 1.6rem;
        }

        header nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 20px;
        }

        header nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        header nav ul li a:hover {
            color: #b34747;
        }

        main.container {
            max-width: 1100px;
            margin: 60px auto 80px;
            background: #fff0f0;
            padding: 40px 48px 60px;
            border-radius: 14px;
            box-shadow: 0 12px 30px rgb(128 0 0 / 0.25);
        }

        .welcome-section {
            background: #800000;
            color: white;
            padding: 24px 32px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgb(128 0 0 / 0.4);
            text-align: center;
            margin-bottom: 40px;
        }

        .welcome-section h2 {
            margin: 0;
            font-weight: 700;
            font-size: 2.6rem;
            letter-spacing: 0.03em;
        }

        .welcome-section p {
            font-size: 1.2rem;
            margin-top: 8px;
            font-weight: 500;
            opacity: 0.85;
        }

        h3.dashboard-title {
            font-weight: 700;
            color: #800000;
            margin-bottom: 28px;
            border-bottom: 3px solid #b34747;
            padding-bottom: 6px;
            letter-spacing: 0.02em;
        }

        .dashboard-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
        }

        .dashboard-links a {
            background: #b34747;
            color: white;
            padding: 18px 24px;
            font-weight: 600;
            text-align: center;
            border-radius: 10px;
            text-decoration: none;
            box-shadow: 0 6px 14px rgb(179 71 71 / 0.5);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .dashboard-links a:hover {
            background-color: #800000;
            box-shadow: 0 10px 24px rgb(128 0 0 / 0.7);
        }

        .dashboard-links a i {
            font-size: 1.3rem;
        }

        footer {
            background: #800000;
            color: #cbd5e1;
            padding: 3rem 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }

        footer a {
            color: #94a3b8;
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
        <h1><span class="logo">DG</span> DevGeeks Admin</h1>
        <nav>
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <h2>Welcome, <?php echo $user_name; ?>!</h2>
        <h3>Admin Dashboard</h3>

        <div class="dashboard-links">
            <a href="manage_users.php"><i class="fas fa-users-cog"></i> Manage Users</a>
            <a href="manage_courses.php"><i class="fas fa-book-open"></i> Manage Courses</a>
            <a href="view_payments.php"><i class="fas fa-credit-card"></i> View Payments</a>
            <a href="post_notices.php"><i class="fas fa-bullhorn"></i> Post Notices</a>
            <a href="user_restrictions.php"><i class="fas fa-user-lock"></i> Manage User Restrictions</a>
            <a href="view_notification.php"><i class="fas fa-bell"></i> View Notifications</a>
        </div>
    </main>

    <!-- Keep your existing footer as is, just place it here -->
    <footer
        style="background:#1E293B; color#800000; padding:60px 24px; font-family:'Poppins', sans-serif; font-size:16px; line-height:1.6;">
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
