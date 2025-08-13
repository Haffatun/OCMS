<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';
$error = '';

// Handle adding restriction
if (isset($_POST['add_restriction'])) {
    $user_id = $_POST['user_id'] ?? null;
    $reason = trim($_POST['reason'] ?? '');
    $restricted_until = $_POST['restricted_until'] ?? null;

    if (!$user_id || !$reason || !$restricted_until) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO user_restrictions (user_id, reason, restricted_until) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $reason, $restricted_until]);
            $message = "Restriction added successfully.";
        } catch (PDOException $e) {
            $error = "Error adding restriction: " . $e->getMessage();
        }
    }
}

// Handle removing restriction
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $restriction_id = $_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM user_restrictions WHERE restriction_id = ?");
    $stmt->execute([$restriction_id]);
    $message = "Restriction removed successfully.";
}

// Fetch all restrictions with user info
$stmt = $pdo->query("
    SELECT ur.restriction_id, u.user_id, u.name, u.email, ur.reason, ur.restricted_until, ur.created_at
    FROM user_restrictions ur
    JOIN users u ON ur.user_id = u.user_id
    ORDER BY ur.created_at DESC
");
$restrictions = $stmt->fetchAll();

// Fetch all users to add restriction (excluding admins for safety)
$users_stmt = $pdo->query("SELECT user_id, name, email FROM users WHERE role != 'admin' ORDER BY name");
$users = $users_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>User Restrictions - Admin - DevGeeks</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 2rem;
        }

        th,
        td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #3B82F6;
            color: #fff;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .btn {
            background: #3B82F6;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-danger {
            background: #DC2626;
        }

        form {
            background: #fff;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 0 5px #ccc;
            margin-bottom: 2rem;
            max-width: 500px;
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

        .message {
            color: green;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .error {
            color: #DC2626;
            font-weight: 600;
            margin-bottom: 1rem;
        }
    </style>
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
                    <li><a href="post_notices.php">Post Notices</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 120px;">
        <h2>User Restrictions</h2>

        <?php if ($message): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" onsubmit="return confirm('Add this restriction?');">
            <input type="hidden" name="add_restriction" value="1" />

            <label for="user_id">Select User</label>
            <select name="user_id" id="user_id" required>
                <option value="">-- Select User --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['user_id']; ?>">
                        <?php echo htmlspecialchars($user['name'] . " (" . $user['email'] . ")"); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="reason">Reason</label>
            <textarea name="reason" id="reason" rows="3" required></textarea>

            <label for="restricted_until">Restricted Until</label>
            <input type="datetime-local" name="restricted_until" id="restricted_until" required />

            <button type="submit" class="btn">Add Restriction</button>
        </form>

        <h3>Currently Restricted Users</h3>
        <?php if (empty($restrictions)): ?>
            <p>No users are currently restricted.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Reason</th>
                        <th>Restricted Until</th>
                        <th>Added On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($restrictions as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['name']); ?></td>
                            <td><?php echo htmlspecialchars($r['email']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($r['reason'])); ?></td>
                            <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($r['restricted_until']))); ?></td>
                            <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($r['created_at']))); ?></td>
                            <td>
                                <a class="btn btn-danger" href="?remove=<?php echo $r['restriction_id']; ?>"
                                    onclick="return confirm('Remove this restriction?');">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>

</body>

</html>
