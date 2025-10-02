<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin');
require_once __DIR__ . '/../../db/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'متد درخواست نامعتبر است.']);
    exit();
}

$action = $_POST['action'] ?? null;
$response = ['success' => false, 'message' => 'عملیات نامعتبر است.'];

if ($action === 'review_answer') {
    try {
        $answer_id = filter_input(INPUT_POST, 'answer_id', FILTER_VALIDATE_INT);
        $status = trim($_POST['status'] ?? '');
        $feedback = trim($_POST['feedback'] ?? '');

        if (!$answer_id) {
            throw new Exception('شناسه پاسخ نامعتبر است.');
        }

        if ($status !== 'approved' && $status !== 'rejected') {
            throw new Exception('وضعیت ارسالی نامعتبر است.');
        }

        if ($status === 'approved') {
            $feedback = null;
        }

        $stmt = $pdo->prepare("UPDATE TaskAnswers SET status = ?, feedback = ? WHERE id = ?");
        $stmt->execute([$status, $feedback, $answer_id]);

        if ($stmt->rowCount() > 0) {
            $message = $status === 'approved' ? 'پاسخ با موفقیت تایید شد.' : 'پاسخ با موفقیت رد شد.';
            $response = ['success' => true, 'message' => $message];
        } else {
            throw new Exception('پاسخی برای به‌روزرسانی یافت نشد.');
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
} elseif ($action === 'delete_answer') {
    try {
        $answer_id = filter_input(INPUT_POST, 'answer_id', FILTER_VALIDATE_INT);

        if (!$answer_id) {
            throw new Exception('شناسه پاسخ برای حذف نامعتبر است.');
        }

        $stmt = $pdo->prepare("DELETE FROM TaskAnswers WHERE id = ?");
        $stmt->execute([$answer_id]);

        if ($stmt->rowCount() > 0) {
            $response = ['success' => true, 'message' => 'پاسخ با موفقیت حذف شد.'];
        } else {
            throw new Exception('پاسخی برای حذف یافت نشد.');
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
