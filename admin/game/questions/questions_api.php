<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: application/json');
require_once __DIR__ . '/../../../db/database.php';
$action = $_REQUEST['action'] ?? null;
$response = ['success' => false, 'message' => 'عملیات نامعتبر است.'];

try {
    switch ($action) {
        /**
         * دریافت اطلاعات یک سوال خاص به همراه گزینه‌هایش
         */
        case 'get_question':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('شناسه سوال نامعتبر است.');

            // ⭐ اضافه کردن فیلدهای امتیاز به کوئری
            $stmt_q = $pdo->prepare("SELECT id, question_text, category, points_correct, points_incorrect FROM Questions WHERE id = ?");
            $stmt_q->execute([$id]);
            $question = $stmt_q->fetch(PDO::FETCH_ASSOC);

            if ($question) {
                $stmt_a = $pdo->prepare("SELECT id, answer_text, is_correct FROM Answers WHERE question_id = ?");
                $stmt_a->execute([$id]);
                $question['answers'] = $stmt_a->fetchAll(PDO::FETCH_ASSOC);
                $response = ['success' => true, 'question' => $question];
            } else {
                throw new Exception('سوال یافت نشد.');
            }
            break;

        /**
             * ایجاد یک سوال جدید
             */
        case 'create_question':
            $data = json_decode(file_get_contents('php://input'), true);
            // ⭐ بررسی فیلدهای امتیاز
            if (empty($data['text']) || empty($data['answers']) || !isset($data['points_correct']) || !isset($data['points_incorrect'])) {
                throw new Exception('داده‌های ارسالی ناقص است.');
            }

            $pdo->beginTransaction();

            // ⭐ اضافه کردن فیلدهای امتیاز به کوئری
            $stmt = $pdo->prepare("INSERT INTO Questions (question_text, category, points_correct, points_incorrect) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['text'], $data['category'], $data['points_correct'], $data['points_incorrect']]);
            $id = $pdo->lastInsertId();

            $stmt_answer = $pdo->prepare("INSERT INTO Answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
            foreach ($data['answers'] as $answer) {
                $stmt_answer->execute([$id, $answer['text'], $answer['is_correct']]);
            }

            $pdo->commit();

            // اطلاعات سوال جدید را برای به‌روزرسانی UI در فرانت‌اند برمی‌گردانیم
            $new_question_data = [
                'id' => $id,
                'question_text' => $data['text'],
                'category' => $data['category'],
                'points_correct' => $data['points_correct'],
                'points_incorrect' => $data['points_incorrect']
            ];
            $response = ['success' => true, 'message' => 'سوال با موفقیت ایجاد شد.', 'question' => $new_question_data];
            break;

        /**
             * ویرایش یک سوال موجود
             */
        case 'update_question':
            $data = json_decode(file_get_contents('php://input'), true);
            // ⭐ بررسی فیلدهای امتیاز
            if (empty($data['id']) || empty($data['text']) || empty($data['answers']) || !isset($data['points_correct']) || !isset($data['points_incorrect'])) {
                throw new Exception('داده‌های ارسالی ناقص است.');
            }

            $id = $data['id'];
            $pdo->beginTransaction();

            // ⭐ اضافه کردن فیلدهای امتیاز به کوئری
            $stmt = $pdo->prepare("UPDATE Questions SET question_text = ?, category = ?, points_correct = ?, points_incorrect = ? WHERE id = ?");
            $stmt->execute([$data['text'], $data['category'], $data['points_correct'], $data['points_incorrect'], $id]);

            // حذف گزینه‌های قبلی
            $stmt_delete = $pdo->prepare("DELETE FROM Answers WHERE question_id = ?");
            $stmt_delete->execute([$id]);

            // افزودن گزینه‌های جدید
            $stmt_answer = $pdo->prepare("INSERT INTO Answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
            foreach ($data['answers'] as $answer) {
                $stmt_answer->execute([$id, $answer['text'], $answer['is_correct']]);
            }

            $pdo->commit();
            $response = ['success' => true, 'message' => 'سوال با موفقیت ویرایش شد.'];
            break;

        /**
             * حذف یک سوال
             */
        case 'delete_question':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('شناسه سوال نامعتبر است.');

            $stmt = $pdo->prepare("DELETE FROM Questions WHERE id = ?");
            $stmt->execute([$id]); // با فرض فعال بودن ON DELETE CASCADE، گزینه‌ها هم حذف می‌شوند

            if ($stmt->rowCount() > 0) {
                $response = ['success' => true, 'message' => 'سوال با موفقیت حذف شد.'];
            } else {
                throw new Exception('سوال یافت نشد یا در حذف مشکلی رخ داد.');
            }
            break;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
