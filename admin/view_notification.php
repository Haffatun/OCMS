<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch admin info
$stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$user_name = $user ? $user['name'] : 'Admin';

// Pagination setup
$limit = 10; // notices per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total notices count for pagination
$countStmt = $pdo->query("SELECT COUNT(*) FROM notices WHERE user_id = {$_SESSION['user_id']} OR is_global = TRUE");
$total_notices = $countStmt->fetchColumn();
$total_pages = ceil($total_notices / $limit);

// Fetch notices posted by admin or global notices with course info
$query = "
    SELECT n.notice_id, n.title, n.content, n.created_at, n.is_global, c.title AS course_title
    FROM notices n
    LEFT JOIN courses c ON n.course_id = c.course_id
    WHERE n.user_id = :user_id OR n.is_global = TRUE
    ORDER BY n.created_at DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$notices = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>View Notices - Admin - DevGeeks</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        body {
            background: #f8fafc;
            color: #1e293b;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding-top: 80px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-weight: 700;
            margin-bottom: 1rem;
            color: #3b82f6;
        }

        .notice {
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem 0;
        }

        .notice:last-child {
            border-bottom: none;
        }

        .notice-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }

        .notice-meta {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .notice-content {
            font-size: 1rem;
            color: #475569;
        }

        .pagination {
            margin-top: 2rem;
            text-align: center;
        }

        .pagination a {
            padding: 8px 14px;
            margin: 0 4px;
            background: #3b82f6;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .pagination a:hover {
            background: #2563eb;
        }

        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 12px 24px;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav a {
            color: #3b82f6;
            font-weight: 600;
            text-decoration: none;
            margin-left: 20px;
        }
    </style>
</head>

<body>

    <nav>
        <div>DevGeeks Admin - Welcome, <?php echo htmlspecialchars($user_name); ?></div>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_users.php">Manage Users</a>
            <a href="manage_courses.php">Manage Courses</a>
            <a href="post_notices.php">Post Notices</a>
            <a href="user_restrictions.php">User Restrictions</a>
            <a href="../logout.php" style="color:#dc2626;">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Your Notices & Announcements</h1>

        <?php if (empty($notices)): ?>
            <p>No notices found.</p>
        <?php else: ?>
            <?php foreach ($notices as $notice): ?>
                <div class="notice">
                    <div class="notice-title">
                        <?php echo htmlspecialchars($notice['title']); ?>
                        <?php if ($notice['is_global']): ?>
                            <span style="font-size:0.85rem; color:#2563eb;">(Global)</span>
                        <?php elseif ($notice['course_title']): ?>
                            <span style="font-size:0.85rem; color:#2563eb;">(Course:
                                <?php echo htmlspecialchars($notice['course_title']); ?>)</span>
                        <?php endif; ?>
                    </div>
                    <div class="notice-meta">
                        Posted on <?php echo date('M d, Y', strtotime($notice['created_at'])); ?>
                    </div>
                    <div class="notice-content">
                        <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>">&laquo; Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" style="<?php echo $i === $page ? 'background:#2563eb;' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>
