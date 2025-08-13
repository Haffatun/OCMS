<?php
session_start();
require_once __DIR__ . '/../db.php';

// Restrict to students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch notices for courses student is enrolled in + global notices
$stmt = $pdo->prepare("
    SELECT n.notice_id, n.title, n.content, n.created_at, c.title AS course_title
    FROM notices n
    LEFT JOIN courses c ON n.course_id = c.course_id
    LEFT JOIN enrollments e ON n.course_id = e.course_id AND e.user_id = ?
    WHERE n.is_global = 1 OR e.user_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$user_id, $user_id]);
$notices = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notices - DevGeeks</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f9fafb;
            color: #1e293b;
            margin: 0;
        }

        .container {
            max-width: 900px;
            margin: 3rem auto;
            padding: 1.5rem;
        }

        h1 {
            text-align: center;
            color: #b91c1c;
            margin-bottom: 2rem;
        }

        .notice {
            background: #fff;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
        }

        .notice h2 {
            margin: 0 0 0.5rem;
            color: #b91c1c;
            font-size: 1.2rem;
        }

        .notice p {
            margin: 0.25rem 0;
            color: #475569;
        }

        .notice .course {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 600;
        }

        a.back {
            display: inline-block;
            margin-bottom: 1rem;
            color: #b91c1c;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="my_courses.php" class="back">&larr; Back to My Courses</a>
        <h1>Notices</h1>

        <?php if (empty($notices)): ?>
            <p style="text-align:center; color:#64748b; font-weight:600;">No notices available at the moment.</p>
        <?php else: ?>
            <?php foreach ($notices as $n): ?>
                <div class="notice">
                    <h2><?php echo htmlspecialchars($n['title']); ?></h2>
                    <?php if ($n['course_title']): ?>
                        <p class="course">Course: <?php echo htmlspecialchars($n['course_title']); ?></p>
                    <?php else: ?>
                        <p class="course">Global Notice</p>
                    <?php endif; ?>
                    <p><?php echo nl2br(htmlspecialchars($n['content'])); ?></p>
                    <p style="font-size:0.8rem; color:#94a3b8;">Posted on:
                        <?php echo date("d M Y, H:i", strtotime($n['created_at'])); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

</html>
