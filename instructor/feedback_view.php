<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit;
}

$instructor_id = $_SESSION['user_id'];

// Fetch feedback for courses taught by this instructor
$stmt = $pdo->prepare("
    SELECT u.name AS student_name, 
           c.title AS course_title, 
           f.rating, 
           f.comment_text, 
           f.created_at
    FROM feedback f
    JOIN users u ON f.user_id = u.user_id
    JOIN courses c ON f.course_id = c.course_id
    WHERE c.instructor_id = ?
    ORDER BY f.created_at DESC
");
$stmt->execute([$instructor_id]);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <title>View Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
        }

        th {
            background-color: #f4f4f4;
        }

        .rating {
            color: #f39c12;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>Student Feedback</h1>

    <?php if ($feedbacks): ?>
        <table>
            <tr>
                <th>Student</th>
                <th>Course</th>
                <th>Rating</th>
                <th>Feedback</th>
                <th>Date</th>
            </tr>
            <?php foreach ($feedbacks as $fb): ?>
                <tr>
                    <td><?= htmlspecialchars($fb['student_name']) ?></td>
                    <td><?= htmlspecialchars($fb['course_title']) ?></td>
                    <td class="rating"><?= htmlspecialchars($fb['rating']) ?>/5</td>
                    <td><?= htmlspecialchars($fb['comment_text']) ?></td>
                    <td><?= htmlspecialchars($fb['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No feedback found for your courses.</p>
    <?php endif; ?>
</body>

</html>
