<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
require_once __DIR__ . '/../db/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$user_id = $claims['sub'];
$scenario_id = filter_input(INPUT_POST, 'scenario_id', FILTER_VALIDATE_INT);
$challenge_id = filter_input(INPUT_POST, 'challenge_id', FILTER_VALIDATE_INT);
$answer_text = trim($_POST['answer_text'] ?? '');

if (!$scenario_id || !$challenge_id || empty($answer_text)) {
    die("اطلاعات ارسالی ناقص است.");
}

try {
    // برای جلوگیری از ثبت پاسخ تکراری، ابتدا پاسخ‌های قبلی با وضعیت rejected یا submitted را حذف می‌کنیم
    $stmt_delete = $pdo->prepare(
        "DELETE FROM ChallengeAnswers WHERE user_id = ? AND challenge_id = ? AND (status = 'submitted' OR status = 'rejected')"
    );
    $stmt_delete->execute([$user_id, $challenge_id]);

    // ثبت پاسخ جدید
    $stmt = $pdo->prepare(
        "INSERT INTO ChallengeAnswers (user_id, challenge_id, answer_text, status) VALUES (?, ?, ?, 'submitted')"
    );
    $stmt->execute([$user_id, $challenge_id, $answer_text]);

    header("Location: view_scenario.php?id=" . $scenario_id);
    exit();
} catch (PDOException $e) {
    die("خطا در ثبت پاسخ: " . $e->getMessage());
}
