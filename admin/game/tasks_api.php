<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin');
require_once __DIR__ . '/../../db/database.php';

$action = $_REQUEST['action'] ?? null;
$response = ['success' => false, 'message' => 'عملیات نامعتبر است.'];

try {
    switch ($action) {
        case 'get_task':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('شناسه نامعتبر است.');

            $stmt = $pdo->prepare("SELECT * FROM Tasks WHERE id = ?");
            $stmt->execute([$id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($task) {
                $stmt_q = $pdo->prepare("SELECT id, question_text, question_order FROM TaskQuestions WHERE task_id = ? ORDER BY question_order");
                $stmt_q->execute([$id]);
                $task['questions'] = $stmt_q->fetchAll(PDO::FETCH_ASSOC);
                $response = ['success' => true, 'task' => $task];
            } else {
                throw new Exception('تکلیف یافت نشد.');
            }
            break;

        case 'create_task':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['title']) || empty($data['team_id']) || empty($data['question1']) || empty($data['question2'])) {
                throw new Exception('تمام فیلدها الزامی هستند.');
            }

            $pdo->beginTransaction();
            // 1. ایجاد تکلیف اصلی
            $stmt = $pdo->prepare("INSERT INTO Tasks (title, description, team_id) VALUES (?, ?, ?)");
            $stmt->execute([$data['title'], $data['description'], $data['team_id']]);
            $taskId = $pdo->lastInsertId();

            // 2. ایجاد سوال اول
            $stmt_q1 = $pdo->prepare("INSERT INTO TaskQuestions (task_id, question_text, question_order) VALUES (?, ?, 1)");
            $stmt_q1->execute([$taskId, $data['question1']]);

            // 3. ایجاد سوال دوم
            $stmt_q2 = $pdo->prepare("INSERT INTO TaskQuestions (task_id, question_text, question_order) VALUES (?, ?, 2)");
            $stmt_q2->execute([$taskId, $data['question2']]);

            $pdo->commit();
            $response = ['success' => true, 'message' => 'تکلیف با موفقیت ایجاد شد.'];
            break;

        case 'update_task':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'];
            if (!$id || empty($data['title']) || empty($data['team_id']) || empty($data['question1']) || empty($data['question2'])) {
                throw new Exception('تمام فیلدها الزامی هستند.');
            }

            $pdo->beginTransaction();
            // 1. ویرایش تکلیف اصلی
            $stmt = $pdo->prepare("UPDATE Tasks SET title = ?, description = ?, team_id = ? WHERE id = ?");
            $stmt->execute([$data['title'], $data['description'], $data['team_id'], $id]);

            // 2. ویرایش سوال اول
            $stmt_q1 = $pdo->prepare("UPDATE TaskQuestions SET question_text = ? WHERE task_id = ? AND question_order = 1");
            $stmt_q1->execute([$data['question1'], $id]);

            // 3. ویرایش سوال دوم
            $stmt_q2 = $pdo->prepare("UPDATE TaskQuestions SET question_text = ? WHERE task_id = ? AND question_order = 2");
            $stmt_q2->execute([$data['question2'], $id]);

            $pdo->commit();
            $response = ['success' => true, 'message' => 'تکلیف با موفقیت ویرایش شد.'];
            break;

        case 'delete_task':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('شناسه نامعتبر است.');

            // به لطف ON DELETE CASCADE در تعریف جدول، با حذف تکلیف، سوالات و پاسخ‌ها هم حذف می‌شوند
            $stmt = $pdo->prepare("DELETE FROM Tasks WHERE id = ?");
            $stmt->execute([$id]);

            $response = ['success' => true, 'message' => 'تکلیف با موفقیت حذف شد.'];
            break;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
