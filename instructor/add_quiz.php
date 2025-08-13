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
$quizCreated = false;
$created_quiz_id = null;

// Handle quiz creation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_quiz'])) {
    $module_id = $_POST['module_id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $max_points = (int) ($_POST['max_points'] ?? 100);

    if (!$module_id || !$title) {
        $error = "Please select module and enter quiz title.";
    } else {
        // Check module belongs to instructor
        $stmt = $pdo->prepare("SELECT m.module_id FROM modules m JOIN courses c ON m.course_id = c.course_id WHERE m.module_id = ? AND c.instructor_id = ?");
        $stmt->execute([$module_id, $instructor_id]);
        if (!$stmt->fetch()) {
            $error = "Invalid module selected.";
        } else {
            // Insert quiz
            $stmt = $pdo->prepare("INSERT INTO quizzes (module_id, title, description, max_points) VALUES (?, ?, ?, ?)");
            try {
                $stmt->execute([$module_id, $title, $description, $max_points]);
                $created_quiz_id = $pdo->lastInsertId();
                $quizCreated = true;
                $success = "Quiz created successfully. Now add questions.";
            } catch (PDOException $e) {
                $error = "Failed to create quiz: " . $e->getMessage();
            }
        }
    }
}

// Handle adding a question (same page POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $quiz_id = $_POST['quiz_id'] ?? null;
    $question_text = trim($_POST['question_text'] ?? '');
    $option_a = trim($_POST['option_a'] ?? '');
    $option_b = trim($_POST['option_b'] ?? '');
    $option_c = trim($_POST['option_c'] ?? '');
    $option_d = trim($_POST['option_d'] ?? '');
    $correct_option = $_POST['correct_option'] ?? '';

    // Validate quiz ownership
    $stmt = $pdo->prepare("
        SELECT q.quiz_id, c.instructor_id 
        FROM quizzes q
        JOIN modules m ON q.module_id = m.module_id
        JOIN courses c ON m.course_id = c.course_id
        WHERE q.quiz_id = ?
    ");
    $stmt->execute([$quiz_id]);
    $quiz_owner = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$quiz_owner || $quiz_owner['instructor_id'] != $instructor_id) {
        $error = "Unauthorized to add question to this quiz.";
    } elseif (!$question_text || !$option_a || !$option_b || !$option_c || !$option_d || !in_array($correct_option, ['A', 'B', 'C', 'D'])) {
        $error = "Please fill in all question fields correctly.";
    } else {
        // Insert question
        $stmt = $pdo->prepare("INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option]);
            $success = "Question added successfully.";
            $quizCreated = true;
            $created_quiz_id = $quiz_id;
        } catch (PDOException $e) {
            $error = "Failed to add question: " . $e->getMessage();
        }
    }
}

// Fetch modules of this instructor for dropdown
$stmt = $pdo->prepare("
    SELECT m.module_id, m.title as module_title, c.title as course_title 
    FROM modules m 
    JOIN courses c ON m.course_id = c.course_id
    WHERE c.instructor_id = ?
    ORDER BY c.title, m.order_index
");
$stmt->execute([$instructor_id]);
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If quiz created, fetch its questions to show below question form
$questions = [];
if ($quizCreated && $created_quiz_id) {
    $stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY question_id");
    $stmt->execute([$created_quiz_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Quiz Creation - Instructor</title>
    <link rel="stylesheet" href="../style.css" />
    
    <style>
        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            font-family: 'Poppins', sans-serif;
        }

        h1,
        h2,
        h3 {
            margin-bottom: 1rem;
        }

        form label {
            font-weight: 600;
        }

        input[type=text],
        textarea,
        select {
            width: 100%;
            padding: 0.5rem;
            margin: 0.3rem 0 1rem 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }

        button.btn {
            background-color: #2563eb;
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: background-color 0.3s ease;
        }

        button.btn:hover {
            background-color: #1e40af;
        }

        .message {
            margin: 1rem 0;
            padding: 0.8rem;
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

        .question-item {
            background: #f9fafb;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 6px;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <h2>Create a New Quiz</h2>

        <form method="POST" action="quiz_manage.php">
            <label for="module_id">Select Module</label>
            <select id="module_id" name="module_id" required>
                <option value="">-- Select Module --</option>
                <?php foreach ($modules as $m): ?>
                    <option value="<?= $m['module_id'] ?>" <?= (isset($_POST['module_id']) && $_POST['module_id'] == $m['module_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['course_title'] . " > " . $m['module_title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="title">Quiz Title</label>
            <input type="text" id="title" name="title" placeholder="Enter quiz title" required
                value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">

            <label for="description">Quiz Description (optional)</label>
            <textarea id="description" name="description"
                rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

            <label for="max_points">Max Points</label>
            <input type="number" id="max_points" name="max_points" min="1"
                value="<?= htmlspecialchars($_POST['max_points'] ?? 100) ?>" required>

            <button type="submit" name="create_quiz" class="btn">Create Quiz</button>
        </form>

        <?php if ($quizCreated && $created_quiz_id): ?>
            <hr style="margin:3rem 0;">
            <h2>Add Questions for Quiz: <?= htmlspecialchars($_POST['title'] ?? '') ?></h2>

            <form method="POST" action="quiz_manage.php">
                <input type="hidden" name="quiz_id" value="<?= $created_quiz_id ?>">

                <label for="question_text">Question Text</label>
                <textarea id="question_text" name="question_text" rows="3" required></textarea>

                <label for="option_a">Option A</label>
                <input type="text" id="option_a" name="option_a" required>

                <label for="option_b">Option B</label>
                <input type="text" id="option_b" name="option_b" required>

                <label for="option_c">Option C</label>
                <input type="text" id="option_c" name="option_c" required>

                <label for="option_d">Option D</label>
                <input type="text" id="option_d" name="option_d" required>

                <label for="correct_option">Correct Option</label>
                <select id="correct_option" name="correct_option" required>
                    <option value="">-- Select Correct Option --</option>
                    <option value="A">Option A</option>
                    <option value="B">Option B</option>
                    <option value="C">Option C</option>
                    <option value="D">Option D</option>
                </select>

                <button type="submit" name="add_question" class="btn">Add Question</button>
            </form>

            <section style="margin-top: 3rem;">
                <h3>Existing Questions</h3>
                <?php if (count($questions) === 0): ?>
                    <p>No questions added yet.</p>
                <?php else: ?>
                    <?php foreach ($questions as $q): ?>
                        <div class="question-item">
                            <strong>Q<?= $q['question_id'] ?>:</strong> <?= htmlspecialchars($q['question_text']) ?><br />
                            A: <?= htmlspecialchars($q['option_a']) ?><br />
                            B: <?= htmlspecialchars($q['option_b']) ?><br />
                            C: <?= htmlspecialchars($q['option_c']) ?><br />
                            D: <?= htmlspecialchars($q['option_d']) ?><br />
                            <em>Correct: Option <?= htmlspecialchars($q['correct_option']) ?></em>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <form action="quiz_list.php" method="GET" style="margin-top: 2rem;">
                <button class="btn" type="submit">Finish Quiz</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>
