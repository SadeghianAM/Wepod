<?php
// فایل: quiz_api.php (نسخه نهایی با امتیازدهی سفارشی برای هر سوال)
header('Content-Type: application/json');
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
require_once __DIR__ . '/../db/database.php';

/**
 * نمره نهایی آزمون را بر اساس پاسخ‌های کاربر و امتیازدهی سفارشی هر سوال محاسبه و ذخیره می‌کند.
 * @param PDO $pdo آبجکت اتصال به دیتابیس
 * @param int $attemptId شناسه تلاش (attempt) کاربر در آزمون
 * @return float نمره نهایی محاسبه شده
 */
function calculateAndSaveFinalScore(PDO $pdo, int $attemptId): float
{
    // دریافت اطلاعات سوالات آزمون به همراه امتیازهایشان در یک کوئری
    $stmt_questions = $pdo->prepare("
        SELECT
            q.id,
            q.points_correct,
            q.points_incorrect
        FROM Questions q
        JOIN QuizQuestions qq ON q.id = qq.question_id
        WHERE qq.quiz_id = (SELECT quiz_id FROM QuizAttempts WHERE id = ?)
    ");
    $stmt_questions->execute([$attemptId]);

    // ✅ استفاده از FETCH_UNIQUE تا ستون اول (id) کلید آرایه شود
    $question_scores = $stmt_questions->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    // دریافت پاسخ‌های کاربر
    $stmt_user_answers = $pdo->prepare("SELECT question_id, selected_answer_id FROM UserAnswers WHERE attempt_id = ?");
    $stmt_user_answers->execute([$attemptId]);
    $userAnswers = $stmt_user_answers->fetchAll(PDO::FETCH_KEY_PAIR);

    $finalScore = 0;
    // تکرار روی پاسخ‌های کاربر
    foreach ($userAnswers as $questionId => $selectedAnswerId) {
        if ($selectedAnswerId === null) {
            continue; // سوال بی‌پاسخ امتیازی ندارد
        }

        // بررسی صحت پاسخ
        $stmt_correct = $pdo->prepare("SELECT is_correct FROM Answers WHERE id = ?");
        $stmt_correct->execute([$selectedAnswerId]);
        $is_correct = (bool) $stmt_correct->fetchColumn();

        // ✅ اصلاح نحوه دسترسی به امتیازها از آرایه جدید
        $points_correct = $question_scores[$questionId]['points_correct'] ?? 1.0;
        $points_incorrect = $question_scores[$questionId]['points_incorrect'] ?? 1.0;

        if ($is_correct) {
            $finalScore += (float) $points_correct; // امتیاز پاسخ صحیح
        } else {
            $finalScore -= (float) $points_incorrect; // نمره منفی پاسخ غلط
        }
    }

    // به‌روزرسانی نمره نهایی در جدول تلاش‌ها
    $stmt_update = $pdo->prepare("UPDATE QuizAttempts SET score = ?, end_time = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt_update->execute([$finalScore, $attemptId]);
    return (float) $finalScore;
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

        // دریافت تمام ID سوالات آزمون برای اطمینان از ثبت پاسخ null برای سوالات بی‌پاسخ
        $stmt_quiz_q = $pdo->prepare("SELECT question_id FROM QuizQuestions WHERE quiz_id = ?");
        $stmt_quiz_q->execute([$quizId]);
        $all_question_ids = $stmt_quiz_q->fetchAll(PDO::FETCH_COLUMN);

        foreach ($all_question_ids as $questionId) {
            // اگر کاربر به سوالی پاسخ داده، آن را ثبت کن، در غیر این صورت null ثبت کن
            $selectedAnswerId = $answers[$questionId] ?? null;
            $stmt_answer->execute([$attemptId, $questionId, $selectedAnswerId]);
        }

        // ۳. محاسبه و ذخیره نمره در جدول QuizAttempts
        $finalScore = calculateAndSaveFinalScore($pdo, $attemptId);

        // ۴. به‌روزرسانی امتیاز کل کاربر در جدول users
        $stmt_update_user_score = $pdo->prepare("UPDATE users SET score = score + ? WHERE id = ?");
        $stmt_update_user_score->execute([$finalScore, $userId]);

        $pdo->commit();
        $response = ['success' => true, 'message' => 'امتیاز شما با موفقیت ثبت شد.', 'score' => $finalScore];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500); // ارسال کد خطای سرور
        $response['message'] = 'خطا در سرور: ' . $e->getMessage();
    }
}

echo json_encode($response);
