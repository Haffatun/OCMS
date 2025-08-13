<?php
session_start();
require_once '../db.php';

// Redirect to login if not instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch instructor name
$stmtUser = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();
if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}
$user_name = htmlspecialchars($user['name']);

// Fetch courses created by this instructor
$stmt = $pdo->prepare("SELECT course_id, title, description, status FROM courses WHERE instructor_id = ?");
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Courses - DevGeeks Instructor</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        .courses-list {
            margin-top: 20px;
        }

        .course-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 5px rgb(0 0 0 / 0.05);
        }

        .course-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #2563eb;
        }

        .course-description {
            font-size: 1rem;
            color: #475569;
            margin-bottom: 12px;
        }

        .course-status {
            font-weight: 600;
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .course-actions a {
            margin-right: 12px;
            text-decoration: none;
            background-color: #3b82f6;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .course-actions a:hover {
            background-color: #2563eb;
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <h1>
                <span class="logo">DG</span>
                <span class="brand">DevGeeks Instructor</span>
            </h1>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="mycourses.php">My Courses</a></li>
                    <li><a href="notices.php">Notices</a></li>
                    <li><a href="notification_view.php">Notifications</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 100px;">
        <h2>Welcome, <?php echo $user_name; ?>!</h2>
        <h3>My Courses</h3>

        <div class="courses-list">
            <?php if (empty($courses)): ?>
                <p>You haven't created any courses yet.</p>
            <?php else: ?>
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <div class="course-title"><?php echo htmlspecialchars($course['title']); ?></div>
                        <div class="course-description"><?php echo nl2br(htmlspecialchars($course['description'])); ?></div>
                        <div class="course-status">Status: <?php echo ucfirst($course['status']); ?></div>
                        <div class="course-actions">
                            <a href="module_manage.php?course_id=<?php echo $course['course_id']; ?>">Manage Modules</a>
                            <a href="quiz_manage.php?course_id=<?php echo $course['course_id']; ?>">Manage Quizzes</a>
                            <a href="notices.php?course_id=<?php echo $course['course_id']; ?>">Post Notices</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>
