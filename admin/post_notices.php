<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';
$error = '';

// Handle posting notice
if (isset($_POST['post_notice'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $course_id = $_POST['course_id'] ?? null;
    $is_global = isset($_POST['is_global']) ? 1 : 0;

    if (!$title || !$content) {
        $error = "Please fill all required fields.";
    } else {
        if ($is_global) {
            $course_id = null; // global notice has no course_id
        }
        $stmt = $pdo->prepare("INSERT INTO notices (course_id, user_id, title, content, is_global) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$course_id, $_SESSION['user_id'], $title, $content, $is_global]);
        $message = "Notice posted successfully.";
    }
}

// Fetch courses for dropdown
$courses_stmt = $pdo->query("SELECT course_id, title FROM courses ORDER BY title");
$courses = $courses_stmt->fetchAll();

// Fetch recent notices (limit 10)
$notices_stmt = $pdo->query("SELECT n.notice_id, n.title, n.content, n.is_global, n.created_at, c.title AS course_title, u.name AS posted_by
    FROM notices n
    LEFT JOIN courses c ON n.course_id = c.course_id
    JOIN users u ON n.user_id = u.user_id
    ORDER BY n.created_at DESC LIMIT 10");
$notices = $notices_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Post Notices - Admin - DevGeeks</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        form {
            background: #fff;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 5px #ccc;
        }

        label {
            display: block;
            margin-bottom: 0.3rem;
            font-weight: 600;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-family: inherit;
            font-size: 1rem;
        }

        button {
            background: #3B82F6;
            color: white;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
        }

        button:hover {
            background: #2563EB;
        }

        .notice {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 5px solid #3B82F6;
        }

        .notice-title {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
        }

        .notice-meta {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 0.5rem;
        }

        .message {
            color: green;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .error {
            color: #DC2626;
            margin-bottom: 1rem;
            font-weight: 600;
        }
    </style>
    <script>
        function toggleCourseDropdown() {
            const isGlobalCheckbox = document.getElementById('is_global');
            const courseSelect = document.getElementById('course_id');
            courseSelect.disabled = isGlobalCheckbox.checked;
        }
        window.onload = toggleCourseDropdown;
    </script>
</head>

<body>
    <header>
        <div class="container">
            <h1><span class="logo">DG</span> <span class="brand">DevGeeks Admin</span></h1>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage_users.php">Manage Users</a></li>
                    <li><a href="users_list.php">Users List</a></li>
                    <li><a href="manage_courses.php">Manage Courses</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 120px;">
        <h2>Post New Notice</h2>

        <?php if ($message): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" onsubmit="return confirm('Are you sure you want to post this notice?');">
            <input type="hidden" name="post_notice" value="1" />

            <label for="title">Title</label>
            <input type="text" name="title" id="title" required maxlength="255" />

            <label for="content">Content</label>
            <textarea name="content" id="content" rows="5" required></textarea>

            <label>
                <input type="checkbox" name="is_global" id="is_global" value="1" onchange="toggleCourseDropdown()" />
                Global Notice
            </label>

            <label for="course_id">Course (if not global)</label>
            <select name="course_id" id="course_id">
                <option value="">-- Select Course --</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['course_id']; ?>">
                        <?php echo htmlspecialchars($course['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Post Notice</button>
        </form>

        <section>
            <h3>Recent Notices</h3>
            <?php if (empty($notices)): ?>
                <p>No notices posted yet.</p>
            <?php else: ?>
                <?php foreach ($notices as $notice): ?>
                    <article class="notice">
                        <div class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></div>
                        <div class="notice-meta">
                            Posted by <?php echo htmlspecialchars($notice['posted_by']); ?>
                            on <?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($notice['created_at']))); ?>
                            <?php if (!$notice['is_global'] && $notice['course_title']): ?>
                                | Course: <?php echo htmlspecialchars($notice['course_title']); ?>
                            <?php else: ?>
                                | Global Notice
                            <?php endif; ?>
                        </div>
                        <div class="notice-content"><?php echo nl2br(htmlspecialchars($notice['content'])); ?></div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>
