<?php
session_start();
require_once __DIR__ . '/../db.php';

// Restrict to students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch student name
$stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_name = $user ? htmlspecialchars($user['name']) : "Student";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard - DevGeeks</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: #f9fafb;
            color: #1e293b;
        }

        header {
            background: #b91c1c;
            padding: 1rem 2rem;
            color: #fff;
            position: sticky;
            top: 0;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: auto;
        }

        header a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            margin-left: 1rem;
        }

        main {
            max-width: 1100px;
            margin: 3rem auto 5rem;
            padding: 0 1.5rem;
        }

        .hero {
            text-align: center;
            margin-bottom: 3rem;
        }

        .hero h1 {
            font-size: 2.8rem;
            font-weight: 700;
            color: #b91c1c;
            margin-bottom: 0.5rem;
        }

        .hero p {
            font-size: 1.1rem;
            color: #475569;
        }

        .dashboard-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .dashboard-links a {
            background: #b91c1c;
            color: #fff;
            padding: 1rem;
            font-weight: 600;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .dashboard-links a:hover {
            background: #7f1d1d;
            transform: translateY(-4px);
        }
    </style>
</head>

<body>

    <header>
        <div class="container">
            <div class="logo">DevGeeks</div>
            <div>
                <a href="../index.php">Home</a>
                <a href="../logout.php">Logout</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <h1>Welcome, <?php echo $user_name; ?>!</h1>
            <p>Access your courses, view scores, submit feedback, and stay updated with latest notices.</p>
        </section>

        <section class="dashboard-links">
            <a href="my_courses.php">My Courses</a>
            <a href="feedback.php">Submit Feedback</a>
            <a href="notices.php">View Notices</a>
            <a href="quiz_take.php">Take Quiz</a>
            <a href="view_score.php">View Scores</a>
        </section>
    </main>

</body>

</html>
