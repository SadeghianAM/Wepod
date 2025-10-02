<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
require_once __DIR__ . '/../db/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$user_id = $claims['sub'];
$task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
$task_question_id = filter_input(INPUT_POST, 'task_question_id', FILTER_VALIDATE_INT);
$answer_text = trim($_POST['answer_text'] ?? '');

if (!$task_id || !$task_question_id || empty($answer_text)) {
    die("اطلاعات ارسالی ناقص است.");
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO TaskAnswers (user_id, task_question_id, answer_text, status) VALUES (?, ?, ?, 'submitted')"
    );
    $stmt->execute([$user_id, $task_question_id, $answer_text]);

    header("Location: my_task.php?id=" . $task_id);
    exit();
} catch (PDOException $e) {
    die("خطا در ثبت پاسخ: " . $e->getMessage());
}
