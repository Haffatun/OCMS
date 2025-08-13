<?php
session_start();
require_once '../db.php';

// Check login and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$error = '';
$success = '';

// Get course_id from GET or POST (depends on your flow)
$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    die("Course ID is required.");
}

// Check if course exists and is active
$stmt = $pdo->prepare("SELECT title, price, status FROM courses WHERE course_id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    die("Course not found.");
}
if ($course['status'] !== 'active') {
    die("Course is not currently available for enrollment.");
}

// Check if already enrolled
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user_id, $course_id]);
if ($stmt->fetch()) {
    $error = "You have already enrolled in this course.";
}

// Handle enrollment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    try {
        // Insert enrollment with pending payment_status
        $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id, payment_status, amount_paid) VALUES (?, ?, 'pending', 0.00)");
        $stmt->execute([$user_id, $course_id]);

        $success = "Enrollment successful! Please proceed to payment to complete your enrollment.";
    } catch (PDOException $e) {
        $error = "Failed to enroll: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Enroll in Course - <?= htmlspecialchars($course['title']) ?></title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        .container {
            max-width: 600px;
            margin: 3rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgb(0 0 0 / 0.1);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        h2 {
            margin-bottom: 1rem;
            color: #334155;
        }

        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 6px;
        }

        .error {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .success {
            background-color: #d1fae5;
            color: #047857;
        }

        button {
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #1e40af;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Enroll in: <?= htmlspecialchars($course['title']) ?></h2>
        <p><strong>Price:</strong> <?= $course['price'] > 0 ? "৳ " . number_format($course['price'], 2) : "Free" ?></p>

        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
            <p><a href="my_courses.php">Go to My Courses</a></p>
        <?php elseif (!$error): ?>
            <form method="POST" action="">
                <button type="submit">Enroll Now</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>
