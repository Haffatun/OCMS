<?php
session_start();
require_once __DIR__ . '/../db.php'; // Use your db.php for $pdo

// Restrict to students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch student's quiz submissions
$stmt = $pdo->prepare("
    SELECT qs.submission_id, q.title AS quiz_title, qs.score, q.max_points, qs.submitted_at
    FROM quiz_submissions qs
    JOIN quizzes q ON qs.quiz_id = q.quiz_id
    WHERE qs.user_id = ?
    ORDER BY qs.submitted_at DESC
");
$stmt->execute([$user_id]);
$submissions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Quiz Scores - DevGeeks</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { font-family: 'Poppins', sans-serif; background:#f9fafb; color:#1e293b; margin:0; }
    .container { max-width: 1000px; margin: 3rem auto; padding: 0 1.5rem; }
    h1 { text-align:center; font-size:2.5rem; margin-bottom:2rem; color:#b91c1c; }
    table { width: 100%; border-collapse: collapse; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,0.1); border-radius:8px; overflow:hidden; }
    th, td { padding: 12px 16px; text-align:left; }
    th { background:#b91c1c; color:#fff; }
    tr:nth-child(even) { background:#f3f4f6; }
    tr:hover { background:#fde2e2; }
    .btn-back { display:inline-block; margin-bottom:1rem; background:#b91c1c; color:#fff; padding:0.5rem 1rem; border-radius:6px; text-decoration:none; font-weight:600; }
    .btn-back:hover { background:#7f1d1d; }
</style>
</head>
<body>

<div class="container">
    <a href="my_courses.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to My Courses</a>
    <h1>My Quiz Scores</h1>

    <?php if (empty($submissions)): ?>
        <p style="text-align:center; color:#64748b; font-weight:600;">You haven't taken any quizzes yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Quiz Title</th>
                    <th>Score</th>
                    <th>Max Points</th>
                    <th>Date Taken</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $sub): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sub['quiz_title']); ?></td>
                        <td><?php echo htmlspecialchars($sub['score']); ?></td>
                        <td><?php echo htmlspecialchars($sub['max_points']); ?></td>
                        <td><?php echo date("d M Y, H:i", strtotime($sub['submitted_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
