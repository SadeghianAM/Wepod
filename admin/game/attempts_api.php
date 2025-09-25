<?php
// فایل نهایی: attempts_api.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
require_once __DIR__ . '/../../db/database.php';

$method = $_SERVER['REQUEST_METHOD'];

// اگر درخواست GET بود، لیست تمام نتایج را برمی‌گردانیم
if ($method === 'GET') {
    try {
        $stmt = $pdo->query("
            SELECT
                qa.id as attempt_id,
                u.username,
                u.name as user_fullname,
                q.title as quiz_title,
                qa.score,
                qa.end_time
            FROM QuizAttempts qa
            JOIN users u ON qa.user_id = u.id
            JOIN Quizzes q ON qa.quiz_id = q.id
            ORDER BY qa.end_time DESC
        ");
        $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($attempts);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'خطای سرور: ' . $e->getMessage()]);
    }
    exit;
}

// اگر درخواست POST بود، عملیات مختلف را انجام می‌دهیم
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $response = ['success' => false, 'message' => 'عملیات نامعتبر است.'];
    $action = $input['action'] ?? null;

    switch ($action) {
        case 'delete':
            $attempt_id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$attempt_id) {
                $response['message'] = 'شناسه نامعتبر است.';
                break;
            }

            try {
                $pdo->beginTransaction();
                $stmt_get_attempt = $pdo->prepare("SELECT user_id, score FROM QuizAttempts WHERE id = ?");
                $stmt_get_attempt->execute([$attempt_id]);
                $attempt = $stmt_get_attempt->fetch(PDO::FETCH_ASSOC);

                if (!$attempt) {
                    throw new Exception('تاریخچه آزمون یافت نشد.');
                }

                $user_id = $attempt['user_id'];
                $score_to_subtract = $attempt['score'];

                if ($score_to_subtract > 0) {
                    $stmt_update_user = $pdo->prepare("UPDATE users SET score = score - ? WHERE id = ?");
                    $stmt_update_user->execute([$score_to_subtract, $user_id]);
                }

                $stmt_delete = $pdo->prepare("DELETE FROM QuizAttempts WHERE id = ?");
                $stmt_delete->execute([$attempt_id]);

                if ($stmt_delete->rowCount() > 0) {
                    $pdo->commit();
                    $response = ['success' => true, 'message' => 'تاریخچه آزمون با موفقیت حذف شد.'];
                } else {
                    throw new Exception('خطا در حذف تاریخچه.');
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $response['message'] = $e->getMessage();
            }
            break;

        case 'get_details':
            $attempt_id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$attempt_id) {
                $response['message'] = 'شناسه نامعتبر است.';
                break;
            }

            try {
                $stmt_main = $pdo->prepare("
                    SELECT u.name as user_fullname, q.title as quiz_title, qa.score
                    FROM QuizAttempts qa
                    JOIN users u ON qa.user_id = u.id
                    JOIN Quizzes q ON qa.quiz_id = q.id
                    WHERE qa.id = ?
                ");
                $stmt_main->execute([$attempt_id]);
                $details = $stmt_main->fetch(PDO::FETCH_ASSOC);

                if (!$details) {
                    throw new Exception("نتیجه آزمون یافت نشد.");
                }

                // *** تغییر اصلی در این کوئری است ***
                $stmt_answers = $pdo->prepare("
                    SELECT
                        q.question_text,
                        uo.answer_text AS user_answer_text,
                        co.answer_text AS correct_answer_text,
                        uo.is_correct
                    FROM UserAnswers ua
                    JOIN Questions q ON ua.question_id = q.id
                    JOIN Answers uo ON ua.chosen_option_id = uo.id
                    JOIN Answers co ON q.id = co.question_id AND co.is_correct = 1
                    WHERE ua.attempt_id = ?
                    ORDER BY q.id
                ");
                $stmt_answers->execute([$attempt_id]);
                $answers = $stmt_answers->fetchAll(PDO::FETCH_ASSOC);

                $response = [
                    'success' => true,
                    'details' => $details,
                    'answers' => $answers
                ];
            } catch (Exception $e) {
                $response['message'] = $e->getMessage();
            }
            break;
    }

    echo json_encode($response);
    exit;
}

// در صورت استفاده از متد نامعتبر دیگر
http_response_code(405); // Method Not Allowed
echo json_encode(['success' => false, 'message' => 'متد درخواست پشتیبانی نمی‌شود.']);
