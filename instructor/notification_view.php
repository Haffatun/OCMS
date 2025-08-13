<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit;
}

$instructor_id = $_SESSION['user_id'];

// Fetch notices:
// - Global notices (is_global = 1)
// - Notices related to courses owned by this instructor

$sql = "
SELECT n.notice_id, n.title, n.content, n.created_at, c.title AS course_title
FROM notices n
LEFT JOIN courses c ON n.course_id = c.course_id
WHERE n.is_global = 1 OR c.instructor_id = :instructor_id
ORDER BY n.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':instructor_id' => $instructor_id]);
$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Notifications - Instructor</title>
    <link rel="stylesheet" href="../style.css" />
</head>

<body>
    <header>
        <div class="container">
            <h1>DevGeeks Instructor Panel</h1>
            <nav>
                <a href="dashboard.php">Dashboard</a> |
                <a href="notification_view.php">Notifications</a> |
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 2rem;">
        <h2>Notifications</h2>

        <?php if (count($notices) === 0): ?>
            <p>No notifications at this time.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($notices as $notice): ?>
                    <li style="margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
                        <strong><?= htmlspecialchars($notice['title']) ?></strong><br />
                        <small>
                            <?= htmlspecialchars($notice['course_title'] ?? 'Global Notice') ?> |
                            <?= htmlspecialchars(date('M d, Y H:i', strtotime($notice['created_at']))) ?>
                        </small>
                        <p><?= nl2br(htmlspecialchars($notice['content'])) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>

</html>
