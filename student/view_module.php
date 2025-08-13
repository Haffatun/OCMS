<?php
session_start();
require_once "../db.php";

// Restrict access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_name = $_SESSION['name'];
$course_id = $_GET['course_id'] ?? null;

if (!$course_id) {
    die("Course ID not provided.");
}

// Fetch course title
$stmt = $pdo->prepare("SELECT title FROM courses WHERE course_id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    die("Course not found.");
}

// Fetch modules and materials
$stmt = $pdo->prepare("
    SELECT m.module_id, m.title AS module_title, m.description AS module_desc, c.content_id, c.title AS content_title, c.content_type, c.file_path 
    FROM modules m
    LEFT JOIN contents c ON m.module_id = c.module_id
    WHERE m.course_id = ?
    ORDER BY m.module_id, c.content_id
");
$stmt->execute([$course_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by module
$modules = [];
foreach ($rows as $row) {
    $modules[$row['module_id']]['title'] = $row['module_title'];
    $modules[$row['module_id']]['description'] = $row['module_desc'];
    $modules[$row['module_id']]['contents'][] = [
        'title' => $row['content_title'],
        'type' => $row['content_type'],
        'file' => $row['file_path']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Modules - OCMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f9f9f9;
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

        .module {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .module h3 {
            margin-top: 0;
            color: maroon;
        }

        .material {
            margin-left: 20px;
        }

        .material a {
            display: block;
            text-decoration: none;
            color: maroon;
            margin-bottom: 5px;
        }

        .material a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <header>
        Course: Database Management Systems
    </header>

    <div class="container">
        <h2>Modules & Materials</h2>

        <div class="module">
            <h3>Module 1: Introduction to Databases</h3>
            <div class="material">
                <a href="#">📄 Lecture Notes</a>
                <a href="#">🎥 Video Lecture</a>
                <a href="#">📂 Additional Resources</a>
            </div>
        </div>

        <div class="module">
            <h3>Module 2: SQL Basics</h3>
            <div class="material">
                <a href="#">📄 Lecture Notes</a>
                <a href="#">🎥 Video Lecture</a>
                <a href="#">📂 Additional Resources</a>
            </div>
        </div>

        <div class="module">
            <h3>Module 3: Advanced SQL Queries</h3>
            <div class="material">
                <a href="#">📄 Lecture Notes</a>
                <a href="#">🎥 Video Lecture</a>
                <a href="#">📂 Additional Resources</a>
            </div>
        </div>

    </div>

</body>

</html>
