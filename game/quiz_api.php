<?php
// فایل: quiz_api.php
header('Content-Type: application/json');
require_once __DIR__ . '/../auth/require-auth.php'; // مسیر فایل auth خود را تنظیم کنید
$claims = requireAuth(null, '/auth/login.html'); // فرض می‌کنیم نقش کاربر 'user' است
require_once __DIR__ . '/../admin/game/database.php';

// کپی تابع محاسبه نمره از پاسخ قبلی
function calculateAndSaveFinalScore(PDO $pdo, int $attemptId): float
{
    $stmt = $pdo->prepare("SELECT q.quiz_id FROM QuizAttempts q WHERE q.id = ?");
    $stmt->execute([$attemptId]);
    $quizId = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT question_id FROM QuizQuestions WHERE quiz_id = ?");
    $stmt->execute([$quizId]);
    $quizQuestionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare("SELECT question_id, selected_answer_id FROM UserAnswers WHERE attempt_id = ?");
    $stmt->execute([$attemptId]);
    $userAnswers = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $finalScore = 0;
    foreach ($quizQuestionIds as $questionId) {
        if (array_key_exists($questionId, $userAnswers)) {
            $selectedAnswerId = $userAnswers[$questionId];
            $stmt_correct = $pdo->prepare("SELECT is_correct FROM Answers WHERE id = ?");
            $stmt_correct->execute([$selectedAnswerId]);
            if ((bool) $stmt_correct->fetchColumn()) {
                $finalScore += 1;
            } else {
                $finalScore -= 2;
            }
        }
    }
    $stmt_update = $pdo->prepare("UPDATE QuizAttempts SET score = ?, end_time = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt_update->execute([$finalScore, $attemptId]);
    return $finalScore;
}

$action = $_REQUEST['action'] ?? null;
$response = ['success' => false, 'message' => 'عملیات نامعتبر است.'];

if ($action === 'submit_attempt') {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $claims['sub']; // شناسه کاربر از توکن
    $quizId = $data['quizId'];
    $answers = $data['answers']; // آرایه‌ای به شکل {questionId: answerId}

    try {
        $pdo->beginTransaction();

        // ۱. ثبت تلاش جدید
        $stmt_attempt = $pdo->prepare("INSERT INTO QuizAttempts (user_id, quiz_id, start_time) VALUES (?, ?, CURRENT_TIMESTAMP)");
        $stmt_attempt->execute([$userId, $quizId]);
        $attemptId = $pdo->lastInsertId();

        // ۲. ثبت پاسخ‌های کاربر
        $stmt_answer = $pdo->prepare("INSERT INTO UserAnswers (attempt_id, question_id, selected_answer_id) VALUES (?, ?, ?)");
        foreach ($answers as $questionId => $answerId) {
            $stmt_answer->execute([$attemptId, $questionId, $answerId]);
        }

        // ۳. محاسبه و ذخیره نمره نهایی
        $finalScore = calculateAndSaveFinalScore($pdo, $attemptId);

        $pdo->commit();
        $response = ['success' => true, 'score' => $finalScore];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
