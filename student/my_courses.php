<?php
session_start();
require_once "../db.php";

// Redirect if not logged in or not student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch enrolled courses for this student
$sql = "SELECT e.enrollment_id, c.course_id, c.title, c.description, c.price, e.payment_status, e.enrolled_at
        FROM enrollments e
        INNER JOIN courses c ON e.course_id = c.course_id
        WHERE e.user_id = ?
        ORDER BY e.enrolled_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - OCMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f5f5f5;
        }

        header {
            background-color: maroon;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        .container {
            width: 85%;
            margin: 30px auto;
        }

        h2 {
            color: maroon;
            border-bottom: 2px solid maroon;
            padding-bottom: 5px;
        }

        .course-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .course-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .course-card:hover {
            transform: translateY(-5px);
        }

        .course-card h3 {
            margin-top: 0;
            color: maroon;
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;
            margin-top: 10px;
            background: maroon;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn:hover {
            background: #8b0000;
        }
    </style>
</head>

<body>

    <header>
        OCMS Student Dashboard
    </header>

    <div class="container">
        <h2>My Courses</h2>
        <div class="course-list">
            <div class="course-card">
                <h3>Introduction to Programming</h3>
                <p>Learn the basics of programming in C language.</p>
                <a href="view_module.php" class="btn">View Modules</a>
            </div>
            <div class="course-card">
                <h3>Database Management Systems</h3>
                <p>Understand relational databases and SQL.</p>
                <a href="view_module.php" class="btn">View Modules</a>
            </div>
            <div class="course-card">
                <h3>Web Development</h3>
                <p>HTML, CSS, and JavaScript for beginners.</p>
                <a href="view_module.php" class="btn">View Modules</a>
            </div>
        </div>
    </div>

</body>

</html>
