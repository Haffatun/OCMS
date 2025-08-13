<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';
$error = '';

// Handle adding users (admin or instructor) and restrictions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $role = $_POST['role'];
        $password = $_POST['password'];

        if (!$email || !$name || !$password || !in_array($role, ['admin', 'instructor'])) {
            $error = "Please fill all fields correctly.";
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Email already registered.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                if ($role === 'instructor') {
                    // Check if any instructor exists
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'instructor'");
                    $stmt->execute();
                    $instructor_count = $stmt->fetchColumn();

                    if ($instructor_count == 0) {
                        // First instructor => user_id = 1001
                        $stmt = $pdo->prepare("INSERT INTO users (user_id, email, password_hash, name, role, is_active) VALUES (?, ?, ?, ?, ?, TRUE)");
                        $stmt->execute([1001, $email, $password_hash, $name, $role]);
                    } else {
                        // Normal insert
                        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, name, role, is_active) VALUES (?, ?, ?, ?, TRUE)");
                        $stmt->execute([$email, $password_hash, $name, $role]);
                    }
                } else {
                    // Normal insert for admin
                    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, name, role, is_active) VALUES (?, ?, ?, ?, TRUE)");
                    $stmt->execute([$email, $password_hash, $name, $role]);
                }

                $message = ucfirst($role) . " added successfully.";
            }
        }
    }

    if (isset($_POST['restrict_user'])) {
        $user_id = intval($_POST['user_id']);
        $reason = filter_var($_POST['reason'], FILTER_SANITIZE_STRING);
        $restricted_until = $_POST['restricted_until'];

        if (!$user_id || !$reason) {
            $error = "Please provide all restriction details.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO user_restrictions (user_id, reason, restricted_until) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $reason, $restricted_until]);
            $message = "User restricted successfully.";
        }
    }
}

// Fetch users for restriction dropdown
$users_stmt = $pdo->query("SELECT user_id, name, email, role FROM users ORDER BY role, name");
$all_users = $users_stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Manage Users - Admin - DevGeeks</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        form {
            margin-bottom: 2rem;
            background: #fff;
            padding: 1rem;
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
        }

        button {
            background: #3B82F6;
            color: white;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #2563EB;
        }

        .message {
            color: green;
            margin-bottom: 1rem;
        }

        .error {
            color: #DC2626;
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
                    <li><a href="users_list.php">Users List</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top:120px;">
        <h2>Manage Users</h2>

        <?php if ($message): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <section>
            <h3>Add New User</h3>
            <form method="POST">
                <input type="hidden" name="add_user" value="1" />
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" required />

                <label for="email">Email</label>
                <input type="email" name="email" id="email" required />

                <label for="role">Role</label>
                <select name="role" id="role" required>
                    <option value="admin">Admin</option>
                    <option value="instructor">Instructor</option>
                </select>

                <label for="password">Password</label>
                <input type="password" name="password" id="password" required />

                <button type="submit">Add User</button>
            </form>
        </section>

        <section>
            <h3>Restrict User</h3>
            <form method="POST">
                <input type="hidden" name="restrict_user" value="1" />
                <label for="user_id">Select User</label>
                <select name="user_id" id="user_id" required>
                    <option value="">-- Select User --</option>
                    <?php foreach ($all_users as $user): ?>
                        <option value="<?php echo $user['user_id']; ?>">
                            <?php echo htmlspecialchars("{$user['name']} ({$user['role']}) - {$user['email']}"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="reason">Reason</label>
                <textarea name="reason" id="reason" rows="3" required></textarea>

                <label for="restricted_until">Restricted Until</label>
                <input type="date" name="restricted_until" id="restricted_until" required />

                <button type="submit">Restrict User</button>
            </form>
        </section>
    </main>
</body>

</html>
