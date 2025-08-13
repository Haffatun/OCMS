<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = TRUE");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php"); // Temporary until dashboard.php is implemented
            }
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - DevGeeks</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        /* Reset */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f5f7ff;
            color: #374151;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            background: #1e40af;
            padding: 18px 24px;
            color: #f1f5f9;
        }

        header .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 1.6rem;
            letter-spacing: 0.08em;
        }

        header h1 .logo {
            font-weight: 800;
            color: #60a5fa;
        }

        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 20px;
        }

        nav a {
            color: #f1f5f9;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        nav a:hover,
        nav a[aria-current="page"] {
            color: #93c5fd;
        }

        main {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            background: #f5f7ff;
        }

        .login-container {
            background: #fff;
            padding: 40px 36px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 420px;
            width: 100%;
        }

        .login-container h2 {
            margin-top: 0;
            margin-bottom: 28px;
            font-weight: 700;
            font-size: 1.9rem;
            color: #2563eb;
            text-align: center;
            letter-spacing: 0.03em;
        }

        form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #374151;
        }

        form input {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 1rem;
            border-radius: 8px;
            border: 1.8px solid #d1d5db;
            transition: border-color 0.3s ease;
            font-family: inherit;
            color: #111827;
        }

        form input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 8px rgba(37, 99, 235, 0.3);
        }

        button.btn {
            background-color: #2563eb;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 14px 0;
            border: none;
            border-radius: 10px;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.3s ease;
            letter-spacing: 0.05em;
        }

        button.btn:hover {
            background-color: #1e40af;
        }

        p.footer-text {
            margin-top: 24px;
            text-align: center;
            font-size: 0.95rem;
            color: #6b7280;
        }

        p.footer-text a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
            transition: text-decoration 0.3s ease;
        }

        p.footer-text a:hover {
            text-decoration: underline;
        }

        .error-message {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 14px 20px;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 24px;
            text-align: center;
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
            <h1><span class="logo">DG</span> <span class="brand">DevGeeks</span></h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                    <li><a href="login.php" aria-current="page">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="login-container">
            <h2>Login to Your Account</h2>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required
                    autocomplete="email" />

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Your password" required
                    autocomplete="current-password" />

                <button type="submit" class="btn">Login</button>
            </form>

            <p class="footer-text">
                Don't have an account? <a href="signup.php">Sign Up here</a>
            </p>
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
</body>

</html>
