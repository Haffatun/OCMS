<?php
session_start();
require_once '../db.php';

// Check admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';

// Fetch all users
$stmt = $pdo->query("SELECT user_id, name, email, role, is_active, created_at FROM users ORDER BY role, name");
$users = $stmt->fetchAll();

// Fetch enrollments with user and course info
$stmt2 = $pdo->query("SELECT e.enrollment_id, e.user_id, c.title AS course_title, e.enrolled_at, e.payment_status 
                      FROM enrollments e
                      JOIN courses c ON e.course_id = c.course_id
                      ORDER BY e.user_id, e.enrolled_at");
$enrollments_raw = $stmt2->fetchAll();

// Organize enrollments by user_id for easy display
$enrollments = [];
foreach ($enrollments_raw as $enroll) {
    $enrollments[$enroll['user_id']][] = $enroll;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Users List - Admin - DevGeeks</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            margin-bottom: 40px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
        }

        th {
            background-color: #2563EB;
            color: #fff;
        }

        .enrollment-table {
            margin-top: 8px;
            margin-bottom: 16px;
        }

        .enrollment-table th {
            background-color: #3B82F6;
            font-size: 0.85rem;
        }

        .enrollment-table td {
            font-size: 0.85rem;
        }

        h2 {
            margin-bottom: 12px;
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <h1><span class="logo">DG</span> <span class="brand">DevGeeks Admin</span></h1>
            <nav>
                <ul>
                    <li><a href="manage_user.php">Manage Users</a></li>
                    <li><a href="users_list.php" class="active">Users List</a></li>
                    <li><a href="courses_manage.php">Manage Courses</a></li>
                    <li><a href="notices.php">Notices</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 120px;">
        <h2>All Registered Users</h2>

        <?php if (empty($users)): ?>
            <p>No users found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Active</th>
                        <th>Registered At</th>
                        <th>Enrollments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo ucfirst($u['role']); ?></td>
                            <td><?php echo $u['is_active'] ? 'Yes' : 'No'; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($u['created_at'])); ?></td>
                            <td>
                                <?php if (!empty($enrollments[$u['user_id']])): ?>
                                    <table class="enrollment-table">
                                        <thead>
                                            <tr>
                                                <th>Course Title</th>
                                                <th>Enrolled At</th>
                                                <th>Payment Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($enrollments[$u['user_id']] as $enr): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($enr['course_title']); ?></td>
                                                    <td><?php echo date('Y-m-d', strtotime($enr['enrolled_at'])); ?></td>
                                                    <td><?php echo ucfirst($enr['payment_status']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <em>No enrollments</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>

</html>
