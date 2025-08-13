<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit;
}

$instructor_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch instructor's courses for dropdown
$stmt = $pdo->prepare("SELECT course_id, title FROM courses WHERE instructor_id = ? ORDER BY title");
$stmt->execute([$instructor_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $course_id = $_POST['course_id'] ?? null; // null or course_id string
    $is_global = isset($_POST['is_global']) ? 1 : 0;

    // Validation
    if ($title === '' || $content === '') {
        $error = "Please fill in both title and content.";
    } elseif ($is_global && $course_id) {
        $error = "Global notice cannot be assigned to a specific course.";
    } elseif (!$is_global && !$course_id) {
        $error = "Please select a course or choose global notice.";
    } else {
        // Insert notice
        $sql = "INSERT INTO notices (course_id, user_id, title, content, is_global) VALUES (:course_id, :user_id, :title, :content, :is_global)";
        $stmt = $pdo->prepare($sql);
        $course_id_param = $is_global ? null : $course_id;

        try {
            $stmt->execute([
                ':course_id' => $course_id_param,
                ':user_id' => $instructor_id,
                ':title' => $title,
                ':content' => $content,
                ':is_global' => $is_global,
            ]);
            $success = "Notice posted successfully.";
        } catch (PDOException $e) {
            $error = "Failed to post notice: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Post Notice - Instructor</title>
    <link rel="stylesheet" href="../style.css" />
</head>

<body>
    <header>
        <div class="container">
            <h1>DevGeeks Instructor Panel</h1>
            <nav>
                <a href="dashboard.php">Dashboard</a> |
                <a href="notices.php">Post Notice</a> |
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 2rem;">
        <h2>Post a New Notice</h2>

        <?php if ($error): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p style="color: green;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST" action="notices.php">
            <div>
                <label>
                    <input type="checkbox" id="is_global" name="is_global" value="1"
                        onclick="toggleCourseSelect(this)" />
                    Global Notice (Visible to all users)
                </label>
            </div>

            <div id="course-select-div">
                <label for="course_id">Select Course</label>
                <select name="course_id" id="course_id" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= htmlspecialchars($course['course_id']) ?>">
                            <?= htmlspecialchars($course['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="title">Title</label><br />
                <input type="text" name="title" id="title" required style="width: 100%; max-width: 600px;" />
            </div>

            <div>
                <label for="content">Content</label><br />
                <textarea name="content" id="content" rows="6" required
                    style="width: 100%; max-width: 600px;"></textarea>
            </div>

            <button type="submit" class="btn">Post Notice</button>
        </form>
    </main>

    <script>
        function toggleCourseSelect(checkbox) {
            const courseSelectDiv = document.getElementById('course-select-div');
            if (checkbox.checked) {
                courseSelectDiv.style.display = 'none';
                document.getElementById('course_id').required = false;
            } else {
                courseSelectDiv.style.display = 'block';
                document.getElementById('course_id').required = true;
            }
        }
    </script>
</body>

</html>
