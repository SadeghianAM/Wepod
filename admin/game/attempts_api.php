<?php
// فایل جدید: attempts_api.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html'); // فقط ادمین دسترسی دارد
require_once __DIR__ . '/../../db/database.php';

$response = ['success' => false, 'message' => 'عملیات نامعتبر است.'];
$action = $_POST['action'] ?? null;

if ($action === 'delete_attempt') {
    $attempt_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$attempt_id) {
        $response['message'] = 'شناسه نامعتبر است.';
        echo json_encode($response);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // ۱. ابتدا اطلاعات تلاش مورد نظر را برای کسر امتیاز پیدا می‌کنیم
        $stmt_get_attempt = $pdo->prepare("SELECT user_id, score FROM QuizAttempts WHERE id = ?");
        $stmt_get_attempt->execute([$attempt_id]);
        $attempt = $stmt_get_attempt->fetch(PDO::FETCH_ASSOC);

        if (!$attempt) {
            throw new Exception('تاریخچه آزمون یافت نشد.');
        }

        $user_id = $attempt['user_id'];
        $score_to_subtract = $attempt['score'];

        // ۲. امتیاز این آزمون را از امتیاز کل کاربر کم می‌کنیم
        $stmt_update_user = $pdo->prepare("UPDATE users SET score = score - ? WHERE id = ?");
        $stmt_update_user->execute([$score_to_subtract, $user_id]);

        // ۳. خود رکورد تلاش را حذف می‌کنیم
        // (با فرض اینکه ON DELETE CASCADE روی دیتابیس فعال است تا پاسخ‌های UserAnswers هم حذف شوند)
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
}

echo json_encode($response);
