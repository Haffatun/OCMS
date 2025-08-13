<?php
session_start();
require_once __DIR__ . '/../db.php';

// Restrict to students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch courses the student is enrolled in
$stmt = $pdo->prepare("
    SELECT c.course_id, c.title
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.user_id = ? AND e.payment_status = 'completed'
");
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);

    if ($course_id && $rating >= 1 && $rating <= 5) {
        $stmt = $pdo->prepare("
            INSERT INTO feedback (course_id, user_id, rating, comment_text, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$course_id, $user_id, $rating, $comment]);
        $message = "Feedback submitted successfully!";
    } else {
        $message = "Please select a course and give a valid rating.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit Feedback - DevGeeks</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f9fafb;
            color: #1e293b;
            margin: 0;
        }

        .container {
            max-width: 700px;
            margin: 3rem auto;
            padding: 1.5rem;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            color: #b91c1c;
            margin-bottom: 2rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        select,
        textarea,
        input[type="number"] {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
        }

        button {
            background: #b91c1c;
            color: #fff;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        button:hover {
            background: #7f1d1d;
        }

        .message {
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: #fde2e2;
            color: #b91c1c;
            border-radius: 6px;
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
        <h1>Submit Feedback</h1>
        <?php if ($message)
            echo "<div class='message'>{$message}</div>"; ?>

        <?php if (empty($courses)): ?>
            <p>You are not enrolled in any courses or payment is not completed.</p>
        <?php else: ?>
            <form method="POST">
                <label for="course_id">Select Course</label>
                <select name="course_id" id="course_id" required>
                    <option value="">-- Choose Course --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?php echo $c['course_id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="rating">Rating (1-5)</label>
                <input type="number" name="rating" id="rating" min="1" max="5" required>

                <label for="comment">Comment</label>
                <textarea name="comment" id="comment" rows="4" placeholder="Your feedback..."></textarea>

                <button type="submit">Submit Feedback</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>
