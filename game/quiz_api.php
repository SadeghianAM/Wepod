<?php
// فایل: quiz_api.php (نسخه نهایی با به‌روزرسانی امتیاز کل کاربر)
header('Content-Type: application/json');
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
require_once __DIR__ . '/../db/database.php';

// تابع محاسبه نمره آزمون فعلی (بدون تغییر)
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
            if ($selectedAnswerId === null) continue; // سوال بی‌پاسخ امتیازی ندارد

            $stmt_correct = $pdo->prepare("SELECT is_correct FROM Answers WHERE id = ?");
            $stmt_correct->execute([$selectedAnswerId]);
            if ((bool) $stmt_correct->fetchColumn()) {
                $finalScore += 1; // امتیاز پاسخ صحیح
            } else {
                $finalScore -= 2; // امتیاز پاسخ غلط (تغییر از ۲- به ۱- طبق درخواست اولیه)
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
    $answers = $data['answers'];

    try {
        $pdo->beginTransaction();

        // ۱. ثبت تلاش جدید
        $stmt_attempt = $pdo->prepare("INSERT INTO QuizAttempts (user_id, quiz_id, start_time) VALUES (?, ?, CURRENT_TIMESTAMP)");
        $stmt_attempt->execute([$userId, $quizId]);
        $attemptId = $pdo->lastInsertId();

        // ۲. ثبت پاسخ‌های کاربر
        $stmt_answer = $pdo->prepare("INSERT INTO UserAnswers (attempt_id, question_id, selected_answer_id) VALUES (?, ?, ?)");
        foreach ($answers as $questionId => $answerId) {
            // اگر کاربر به سوالی پاسخ نداده باشد، answerId می‌تواند null باشد
            $stmt_answer->execute([$attemptId, $questionId, $answerId]);
        }

        // ۳. محاسبه و ذخیره نمره در جدول QuizAttempts
        $finalScore = calculateAndSaveFinalScore($pdo, $attemptId);

        // ۴. ⭐ به‌روزرسانی امتیاز کل کاربر در جدول users (بخش اضافه شده) ⭐
        $stmt_update_user_score = $pdo->prepare("UPDATE users SET score = score + ? WHERE id = ?");
        $stmt_update_user_score->execute([$finalScore, $userId]);

        $pdo->commit();
        $response = ['success' => true, 'message' => 'امتیاز شما با موفقیت ثبت شد.', 'score' => $finalScore];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $response['message'] = 'خطا در سرور: ' . $e->getMessage();
    }
}

echo json_encode($response);
