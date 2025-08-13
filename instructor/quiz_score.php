<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit;
}

$instructor_id = $_SESSION['user_id'];

if (!isset($_GET['quiz_id'])) {
    die("Quiz ID is required.");
}

$quiz_id = $_GET['quiz_id'];

// Get quiz details
$stmt = $pdo->prepare("
    SELECT q.title, m.title AS module_title 
    FROM quizzes q
    JOIN modules m ON q.module_id = m.module_id
    WHERE q.quiz_id = ? AND m.instructor_id = ?
");
$stmt->execute([$quiz_id, $instructor_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die("Quiz not found or you don't have permission to view it.");
}

// Get quiz submissions
$stmt = $pdo->prepare("
    SELECT qs.submission_id, u.name AS username, qs.score, qs.submitted_at
    FROM quiz_submissions qs
    JOIN users u ON qs.user_id = u.user_id
    WHERE qs.quiz_id = ?
    ORDER BY qs.submitted_at DESC
");
$stmt->execute([$quiz_id]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Quiz Scores</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h1 {
            margin-bottom: 5px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
    </style>
</head>

<body>
    <h1>Scores for <?= htmlspecialchars($quiz['title']) ?></h1>
    <p>Module: <?= htmlspecialchars($quiz['module_title']) ?></p>

    <?php if (count($submissions) === 0): ?>
        <p>No submissions yet.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Student Name</th>
                <th>Score</th>
                <th>Submitted At</th>
            </tr>
            <?php foreach ($submissions as $sub): ?>
                <tr>
                    <td><?= htmlspecialchars($sub['username']) ?></td>
                    <td><?= htmlspecialchars($sub['score']) ?></td>
                    <td><?= htmlspecialchars($sub['submitted_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>

</html>
