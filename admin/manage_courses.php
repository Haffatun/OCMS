<?php
session_start();
require_once '../db.php';

// Check if admin logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = $success = '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create new course
    if (isset($_POST['course_title'])) {
        $title = trim($_POST['course_title']);
        $description = trim($_POST['description']);
        $instructor_id = intval($_POST['instructor_id']);
        $price = floatval($_POST['price']);
        $status = in_array($_POST['status'], ['draft', 'active', 'archived']) ? $_POST['status'] : 'draft';

        if (empty($title) || empty($description) || !$instructor_id) {
            $error = "Please fill all course fields.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO courses (title, description, instructor_id, price, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $instructor_id, $price, $status]);
                $success = "Course created successfully.";
            } catch (PDOException $e) {
                $error = "Error creating course: " . $e->getMessage();
            }
        }
    }

    // Add new module to a course
    if (isset($_POST['module_course_id'])) {
        $course_id = intval($_POST['module_course_id']);
        $module_title = trim($_POST['module_title']);
        $module_desc = trim($_POST['module_description']);
        $order_index = intval($_POST['order_index']);
        $module_status = in_array($_POST['module_status'], ['draft', 'published']) ? $_POST['module_status'] : 'draft';

        if (!$course_id || empty($module_title) || empty($module_desc)) {
            $error = "Please fill all module fields.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO modules (course_id, title, description, order_index, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$course_id, $module_title, $module_desc, $order_index, $module_status]);
                $success = "Module added successfully.";
            } catch (PDOException $e) {
                $error = "Error adding module: " . $e->getMessage();
            }
        }
    }
}

// Fetch instructors for dropdown
$stmt = $pdo->prepare("SELECT user_id, name FROM users WHERE role = 'instructor' AND is_active = TRUE ORDER BY name");
$stmt->execute();
$instructors = $stmt->fetchAll();

// Fetch courses with instructors
$stmt = $pdo->query("SELECT c.course_id, c.title, c.description, c.price, c.status, u.name AS instructor_name 
                     FROM courses c
                     JOIN users u ON c.instructor_id = u.user_id
                     ORDER BY c.title");
$courses = $stmt->fetchAll();

// Fetch modules grouped by course_id
$stmt = $pdo->query("SELECT module_id, course_id, title, order_index, status FROM modules ORDER BY course_id, order_index ASC");
$modules_all = $stmt->fetchAll();

$modules_by_course = [];
foreach ($modules_all as $mod) {
    $modules_by_course[$mod['course_id']][] = $mod;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Courses & Modules - Admin - DevGeeks</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        table.modules-table {
            margin-top: 8px;
            border-collapse: collapse;
            width: 100%;
            font-size: 0.9rem;
        }

        table.modules-table th,
        table.modules-table td {
            border: 1px solid #ddd;
            padding: 6px 10px;
        }

        table.modules-table th {
            background-color: #f3f4f6;
            color: #333;
        }

        form input[type=text],
        form textarea,
        form select,
        form input[type=number] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
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
                    <li><a href="users_list.php">Users List</a></li>
                    <li><a href="courses_manage.php" class="active">Manage Courses</a></li>
                    <li><a href="notices.php">Notices</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 120px;">
        <h2>Create New Course</h2>
        <?php if ($error): ?>
            <p style="color: #DC2626;"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif ($success): ?>
            <p style="color: #16A34A;"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="POST" action="courses_manage.php" style="max-width:600px;">
            <label for="course_title">Course Title</label>
            <input type="text" id="course_title" name="course_title" required />

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" required></textarea>

            <label for="price">Price (Taka)</label>
            <input type="number" id="price" name="price" min="0" step="0.01" value="0.00" required />

            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="draft">Draft</option>
                <option value="active" selected>Active</option>
                <option value="archived">Archived</option>
            </select>

            <label for="instructor_id">Instructor</label>
            <select id="instructor_id" name="instructor_id" required>
                <option value="">-- Select Instructor --</option>
                <?php foreach ($instructors as $inst): ?>
                    <option value="<?php echo $inst['user_id']; ?>"><?php echo htmlspecialchars($inst['name']); ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn">Create Course</button>
        </form>

        <h2>Existing Courses & Modules</h2>
        <?php if (empty($courses)): ?>
            <p>No courses found.</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.95rem;">
                <thead>
                    <tr style="background:#3B82F6; color:#fff;">
                        <th style="padding:10px; border:1px solid #ddd;">Title</th>
                        <th style="padding:10px; border:1px solid #ddd;">Description</th>
                        <th style="padding:10px; border:1px solid #ddd;">Price (Tk)</th>
                        <th style="padding:10px; border:1px solid #ddd;">Status</th>
                        <th style="padding:10px; border:1px solid #ddd;">Instructor</th>
                        <th style="padding:10px; border:1px solid #ddd;">Modules</th>
                        <th style="padding:10px; border:1px solid #ddd;">Add Module</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td style="padding:8px; border:1px solid #ddd;"><?php echo htmlspecialchars($course['title']); ?>
                            </td>
                            <td style="padding:8px; border:1px solid #ddd;">
                                <?php echo htmlspecialchars(substr($course['description'], 0, 100)) . (strlen($course['description']) > 100 ? '...' : ''); ?>
                            </td>
                            <td style="padding:8px; border:1px solid #ddd;"><?php echo number_format($course['price'], 2); ?>
                            </td>
                            <td style="padding:8px; border:1px solid #ddd;"><?php echo ucfirst($course['status']); ?></td>
                            <td style="padding:8px; border:1px solid #ddd;">
                                <?php echo htmlspecialchars($course['instructor_name']); ?></td>
                            <td style="padding:8px; border:1px solid #ddd; max-width: 200px;">
                                <?php if (!empty($modules_by_course[$course['course_id']])): ?>
                                    <table class="modules-table">
                                        <thead>
                                            <tr>
                                                <th>Order</th>
                                                <th>Title</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($modules_by_course[$course['course_id']] as $module): ?>
                                                <tr>
                                                    <td><?php echo $module['order_index']; ?></td>
                                                    <td><?php echo htmlspecialchars($module['title']); ?></td>
                                                    <td><?php echo ucfirst($module['status']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <em>No modules yet</em>
                                <?php endif; ?>
                            </td>
                            <td style="padding:8px; border:1px solid #ddd; max-width: 280px;">
                                <form method="POST" action="courses_manage.php">
                                    <input type="hidden" name="module_course_id" value="<?php echo $course['course_id']; ?>">
                                    <input type="text" name="module_title" placeholder="Module Title" required>
                                    <textarea name="module_description" placeholder="Module Description" rows="2"
                                        required></textarea>
                                    <label for="order_index_<?php echo $course['course_id']; ?>">Order</label>
                                    <input type="number" id="order_index_<?php echo $course['course_id']; ?>" name="order_index"
                                        min="1" value="1" required>
                                    <label for="module_status_<?php echo $course['course_id']; ?>">Status</label>
                                    <select id="module_status_<?php echo $course['course_id']; ?>" name="module_status"
                                        required>
                                        <option value="draft" selected>Draft</option>
                                        <option value="published">Published</option>
                                    </select>
                                    <button type="submit" class="btn" style="margin-top: 6px;">Add Module</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>

</html>
