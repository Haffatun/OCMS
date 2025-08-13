<?php
session_start();
require_once __DIR__ . '/../db.php'; // your db.php should define $pdo

// Restrict to students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if quiz_id is provided
if (!isset($_GET['quiz_id'])) {
    header("Location: my_courses.php");
    exit;
}

$quiz_id = intval($_GET['quiz_id']);

// Fetch quiz and questions
$stmt = $pdo->prepare("SELECT title, description, max_points FROM quizzes WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    echo "Quiz not found.";
    exit;
}

$questionsStmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ?");
$questionsStmt->execute([$quiz_id]);
$questions = $questionsStmt->fetchAll();

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    foreach ($questions as $q) {
        $qid = $q['question_id'];
        $answer = $_POST['question_'.$qid] ?? '';
        if ($answer === $q['correct_option']) {
            $score++;
        }
    }

    // Save submission
    $insert = $pdo->prepare("INSERT INTO quiz_submissions (quiz_id, user_id, score) VALUES (?, ?, ?)");
    $insert->execute([$quiz_id, $user_id, $score]);

    $submitted = true;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Take Quiz - DevGeeks</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>

<body>

    <header>
        <div class="container">
            <a href="../index.php" class="logo">DevGeeks</a>
            <nav>
                <ul>
                    <li><a href="my_courses.php">My Courses</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <h1>Module Quizzes</h1>
            <?php if (!empty($msg)): ?>
                <p style="color:green; font-weight:600;"><?php echo $msg; ?></p>
            <?php endif; ?>
        </section>

        <section class="courses-grid">
            <?php if (empty($quizzes)): ?>
                <p style="grid-column:1 / -1; text-align:center; color:#64748b; font-weight:600;">No quizzes available for
                    this module.</p>
            <?php else: ?>
                <?php foreach ($quizzes as $quiz): ?>
                    <article class="course-card">
                        <h2 class="course-title"><?php echo htmlspecialchars($quiz['title']); ?></h2>
                        <p class="course-desc"><?php echo htmlspecialchars($quiz['description']); ?></p>
                        <form method="POST">
                            <input type="hidden" name="quiz_id" value="<?php echo $quiz['quiz_id']; ?>">
                            <?php
                            $stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id=?");
                            $stmt->execute([$quiz['quiz_id']]);
                            $questions = $stmt->fetchAll();
                            ?>
                            <?php foreach ($questions as $q): ?>
                                <div style="margin-bottom:15px;">
                                    <p><strong><?php echo htmlspecialchars($q['question_text']); ?></strong></p>
                                    <?php foreach (['A', 'B', 'C', 'D'] as $opt):
                                        if ($q['option_' . $opt] != ''): ?>
                                            <label>
                                                <input type="radio" name="question_<?php echo $q['question_id']; ?>"
                                                    value="<?php echo $opt; ?>">
                                                <?php echo htmlspecialchars($q['option_' . $opt]); ?>
                                            </label><br>
                                        <?php endif; endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                            <button type="submit" name="submit_quiz" class="enroll-btn">Submit Quiz</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <?php include '../footer.php'; ?>

</body>

</html>
