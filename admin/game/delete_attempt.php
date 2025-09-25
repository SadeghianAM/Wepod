<?php
// فایل: delete_attempt.php (اصلاح شده بر اساس ساختار جدید)
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin');

require_once __DIR__ . '/../../db/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("خطا: متد درخواست نامعتبر است.");
}

$attempt_id = filter_input(INPUT_POST, 'attempt_id', FILTER_VALIDATE_INT);
$quiz_id_return = filter_input(INPUT_POST, 'quiz_id', FILTER_VALIDATE_INT);

if (!$attempt_id) {
    http_response_code(400);
    die("خطا: شناسه نتیجه نامعتبر است.");
}

try {
    // ابتدا رکوردهای مرتبط در UserAnswers را حذف می‌کنیم
    // **تغییر:** ستون به attempt_id تغییر کرد
    $stmt_answers = $pdo->prepare("DELETE FROM UserAnswers WHERE attempt_id = ?");
    $stmt_answers->execute([$attempt_id]);

    // سپس خود رکورد تلاش را حذف می‌کنیم
    $stmt_attempt = $pdo->prepare("DELETE FROM QuizAttempts WHERE id = ?");
    $stmt_attempt->execute([$attempt_id]);

    $redirect_url = 'results.php';
    if ($quiz_id_return) {
        $redirect_url .= '?quiz_id=' . $quiz_id_return;
    }

    header("Location: $redirect_url");
    exit();
} catch (PDOException $e) {
    http_response_code(500);
    die("خطا در پایگاه داده: " . $e->getMessage());
}
