<?php
// فایل: quiz_api.php (نسخه نهایی با ارسال جزئیات کامل نتیجه)
header('Content-Type: application/json');
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
require_once __DIR__ . '/../db/database.php';

/**
 * نمره و جزئیات آزمون را محاسبه کرده و در دیتابیس ذخیره می‌کند.
 * @param PDO $pdo آبجکت اتصال به دیتابیس
 * @param int $attemptId شناسه تلاش (attempt) کاربر در آزمون
 * @return array حاوی نمره نهایی و تعداد پاسخ‌های صحیح، غلط و بی‌جواب
 */
function calculateAndSaveFinalScore(PDO $pdo, int $attemptId): array
{
    $stmt_questions = $pdo->prepare("
        SELECT q.id, q.points_correct, q.points_incorrect
        FROM Questions q
        JOIN QuizQuestions qq ON q.id = qq.question_id
        WHERE qq.quiz_id = (SELECT quiz_id FROM QuizAttempts WHERE id = ?)
    ");
    $stmt_questions->execute([$attemptId]);
    $question_scores = $stmt_questions->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    $stmt_user_answers = $pdo->prepare("SELECT question_id, selected_answer_id FROM UserAnswers WHERE attempt_id = ?");
    $stmt_user_answers->execute([$attemptId]);
    $userAnswers = $stmt_user_answers->fetchAll(PDO::FETCH_KEY_PAIR);

    $finalScore = 0.0;
    // ⭐ متغیرهای جدید برای شمارش
    $correctCount = 0;
    $incorrectCount = 0;
    $unansweredCount = 0;

    // تکرار روی تمام سوالات آزمون، نه فقط پاسخ‌های کاربر
    foreach ($question_scores as $questionId => $scores) {
        $selectedAnswerId = $userAnswers[$questionId] ?? null;

        if ($selectedAnswerId === null) {
            $unansweredCount++; // شمارش سوالات بی‌جواب
            continue;
        }

        $stmt_correct = $pdo->prepare("SELECT is_correct FROM Answers WHERE id = ?");
        $stmt_correct->execute([$selectedAnswerId]);
        $is_correct = (bool) $stmt_correct->fetchColumn();

        $points_correct = (float)($scores['points_correct'] ?? 1.0);
        $points_incorrect = (float)($scores['points_incorrect'] ?? 1.0);

        if ($is_correct) {
            $finalScore += $points_correct;
            $correctCount++; // شمارش پاسخ‌های صحیح
        } else {
            $finalScore += $points_incorrect; // از + به جای - استفاده می‌کنیم
            $incorrectCount++; // شمارش پاسخ‌های غلط
        }
    }

    $stmt_update = $pdo->prepare("UPDATE QuizAttempts SET score = ?, end_time = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt_update->execute([$finalScore, $attemptId]);

    // ⭐ برگرداندن آرایه‌ای از نتایج
    return [
        'score' => (float) $finalScore,
        'correctCount' => $correctCount,
        'incorrectCount' => $incorrectCount,
        'unansweredCount' => $unansweredCount
    ];
}

$action = $_REQUEST['action'] ?? null;
$response = ['success' => false, 'message' => 'عملیات نامعتبر است.'];

if ($action === 'submit_attempt') {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $claims['sub'];
    $quizId = $data['quizId'];
    $answers = $data['answers'];

    try {
        $pdo->beginTransaction();

        $stmt_attempt = $pdo->prepare("INSERT INTO QuizAttempts (user_id, quiz_id, start_time) VALUES (?, ?, CURRENT_TIMESTAMP)");
        $stmt_attempt->execute([$userId, $quizId]);
        $attemptId = $pdo->lastInsertId();

        $stmt_answer = $pdo->prepare("INSERT INTO UserAnswers (attempt_id, question_id, selected_answer_id) VALUES (?, ?, ?)");
        $stmt_quiz_q = $pdo->prepare("SELECT question_id FROM QuizQuestions WHERE quiz_id = ?");
        $stmt_quiz_q->execute([$quizId]);
        $all_question_ids = $stmt_quiz_q->fetchAll(PDO::FETCH_COLUMN);

        foreach ($all_question_ids as $questionId) {
            $selectedAnswerId = $answers[$questionId] ?? null;
            $stmt_answer->execute([$attemptId, $questionId, $selectedAnswerId]);
        }

        // ⭐ دریافت نتایج کامل
        $results = calculateAndSaveFinalScore($pdo, $attemptId);

        $stmt_update_user_score = $pdo->prepare("UPDATE users SET score = score + ? WHERE id = ?");
        $stmt_update_user_score->execute([$results['score'], $userId]);

        $pdo->commit();
        // ⭐ ارسال نتایج کامل در پاسخ
        $response = [
            'success' => true,
            'message' => 'امتیاز شما با موفقیت ثبت شد.',
            'results' => $results
        ];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        $response['message'] = 'خطا در سرور: ' . $e->getMessage();
    }
}

echo json_encode($response);
