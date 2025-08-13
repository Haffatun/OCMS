<?php
session_start();
require_once 'db.php';

// Fetch active courses
$stmt = $pdo->query("SELECT c.course_id, c.title, c.description, u.name AS instructor_name 
                     FROM courses c 
                     JOIN users u ON c.instructor_id = u.user_id 
                     WHERE c.status = 'active'");
$courses = $stmt->fetchAll();

$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['role'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DevGeeks - Learn Tech Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        /* Reset & base */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f9fafb;
            color: #1e293b;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: #2563eb;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #1e40af;
        }

        header {
            background: #2563eb;
            padding: 1rem 2rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-weight: 700;
            font-size: 1.8rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            user-select: none;
        }

        .logo svg {
            stroke: white;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            margin: 0;
            padding: 0;
        }

        nav ul li a {
            color: white;
            font-weight: 600;
            padding: 0.5rem 0;
            border-bottom: 2px solid transparent;
        }

        nav ul li a:hover,
        nav ul li a:focus {
            border-bottom: 2px solid #dbeafe;
            outline: none;
        }

        main {
            max-width: 1100px;
            margin: 3rem auto 5rem;
            padding: 0 1.5rem;
        }

        .hero {
            text-align: center;
            margin-bottom: 4rem;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            background: linear-gradient(90deg, #2563eb, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.25rem;
            max-width: 600px;
            margin: 0 auto;
            color: #475569;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
        }

        .course-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgb(38 38 38 / 6%);
            padding: 1.75rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 24px rgb(38 38 38 / 12%);
        }

        .course-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #1e293b;
        }

        .course-desc {
            flex-grow: 1;
            color: #64748b;
            margin-bottom: 1.25rem;
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .instructor {
            font-size: 0.9rem;
            color: #475569;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .enroll-btn {
            background: #2563eb;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .enroll-btn:hover,
        .enroll-btn:focus {
            background: #1e40af;
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
        <div class="container">
            <a href="index.php" class="logo" aria-label="DevGeeks Home">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <polyline points="2 17 12 22 22 17"></polyline>
                    <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
                DevGeeks
            </a>
            <nav aria-label="Primary Navigation">
                <ul>
                    <li><a href="index.php" tabindex="0">Home</a></li>
                    <?php if ($is_logged_in): ?>
                        <li><a href="dashboard.php" tabindex="0">Dashboard</a></li>
                        <li><a href="logout.php" tabindex="0">Logout</a></li>
                    <?php else: ?>
                        <li><a href="signup.php" tabindex="0">Sign Up</a></li>
                        <li><a href="login.php" tabindex="0">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero" role="banner">
            <h1>Empower Your Future with DevGeeks</h1>
            <p>Join thousands of learners in Sylhet and beyond. Explore our expertly curated courses and start your
                journey today!</p>
        </section>

        <section aria-label="Courses Available" class="courses-grid">
            <?php if (empty($courses)): ?>
                <p style="grid-column: 1 / -1; text-align:center; color:#64748b; font-weight:600;">No courses available yet.
                    Check back soon!</p>
            <?php else: ?>
                <?php foreach ($courses as $course): ?>
                    <article class="course-card" tabindex="0" aria-labelledby="course-title-<?php echo $course['course_id'] ?>">
                        <h2 id="course-title-<?php echo $course['course_id'] ?>" class="course-title">
                            <?php echo htmlspecialchars($course['title']); ?>
                        </h2>
                        <p class="course-desc">
                            <?php echo htmlspecialchars(substr($course['description'], 0, 140)) . (strlen($course['description']) > 140 ? '...' : ''); ?>
                        </p>
                        <p class="instructor">Instructor: <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                        <a href="student/enroll.php?course_id=<?php echo $course['course_id']; ?>" class="enroll-btn">Enroll
                            Now</a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
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
