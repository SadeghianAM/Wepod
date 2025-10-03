<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin');
require_once __DIR__ . '/../../db/database.php';

define('UPLOAD_DIR', __DIR__ . '/../../quiz-img/');

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

function handle_upload($file_input, $index)
{
    if (isset($file_input['name'][$index]) && $file_input['error'][$index] === UPLOAD_ERR_OK) {
        $tmp_name = $file_input['tmp_name'][$index];
        $original_name = basename($file_input['name'][$index]);
        $new_filename = uniqid() . '-' . preg_replace('/[^A-Za-z0-9.\-_]/', '', $original_name);
        $destination = UPLOAD_DIR . $new_filename;

        if (move_uploaded_file($tmp_name, $destination)) {
            return $new_filename;
        }
    }
    return null;
}

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
                $stmt_q = $pdo->prepare("SELECT id, question_text, question_image, question_order FROM TaskQuestions WHERE task_id = ? ORDER BY question_order");
                $stmt_q->execute([$id]);
                $task['questions'] = $stmt_q->fetchAll(PDO::FETCH_ASSOC);

                foreach ($task['questions'] as &$question) {
                    $question['image_url'] = !empty($question['question_image']) ? '/quiz-img/' . $question['question_image'] : null;
                }
                $response = ['success' => true, 'task' => $task];
            } else {
                throw new Exception('تکلیف یافت نشد.');
            }
            break;

        case 'create_task':
            $title = $_POST['title'] ?? null;
            $team_id = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
            $description = $_POST['description'] ?? '';
            $questions_text = $_POST['questions_text'] ?? [];

            if (empty($title) || empty($team_id) || empty($questions_text)) {
                throw new Exception('عنوان، تیم و حداقل یک سوال الزامی هستند.');
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO Tasks (title, description, team_id) VALUES (?, ?, ?)");
            $stmt->execute([$title, $description, $team_id]);
            $taskId = $pdo->lastInsertId();

            $stmt_q = $pdo->prepare("INSERT INTO TaskQuestions (task_id, question_text, question_image, question_order) VALUES (?, ?, ?, ?)");
            foreach ($questions_text as $index => $q_text) {
                if (empty(trim($q_text))) continue;
                $image_filename = handle_upload($_FILES['questions_images'], $index);
                $order = $index + 1;
                $stmt_q->execute([$taskId, $q_text, $image_filename, $order]);
            }

            $pdo->commit();
            $response = ['success' => true, 'message' => 'تکلیف با موفقیت ایجاد شد.'];
            break;

        case 'update_task':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $title = $_POST['title'] ?? null;
            $team_id = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
            $description = $_POST['description'] ?? '';
            $questions_text = $_POST['questions_text'] ?? [];
            $questions_ids = $_POST['questions_ids'] ?? [];

            if (!$id || empty($title) || empty($team_id)) {
                throw new Exception('تمام فیلدهای اصلی الزامی هستند.');
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE Tasks SET title = ?, description = ?, team_id = ? WHERE id = ?");
            $stmt->execute([$title, $description, $team_id, $id]);

            $stmt_db_q = $pdo->prepare("SELECT id, question_image FROM TaskQuestions WHERE task_id = ?");
            $stmt_db_q->execute([$id]);
            $db_questions = $stmt_db_q->fetchAll(PDO::FETCH_KEY_PAIR);

            $submitted_ids = [];
            $stmt_update_q = $pdo->prepare("UPDATE TaskQuestions SET question_text = ?, question_image = ?, question_order = ? WHERE id = ?");
            $stmt_insert_q = $pdo->prepare("INSERT INTO TaskQuestions (task_id, question_text, question_image, question_order) VALUES (?, ?, ?, ?)");

            foreach ($questions_text as $index => $q_text) {
                if (empty(trim($q_text))) continue;

                $q_id = !empty($questions_ids[$index]) ? (int)$questions_ids[$index] : null;
                $order = $index + 1;
                $new_image_filename = handle_upload($_FILES['questions_images'], $index);

                if ($q_id && array_key_exists($q_id, $db_questions)) {
                    // UPDATE existing question
                    $submitted_ids[] = $q_id;
                    $current_image = $db_questions[$q_id];
                    $image_to_set = $current_image;

                    if ($new_image_filename) {
                        if ($current_image && file_exists(UPLOAD_DIR . $current_image)) {
                            unlink(UPLOAD_DIR . $current_image);
                        }
                        $image_to_set = $new_image_filename;
                    }
                    $stmt_update_q->execute([$q_text, $image_to_set, $order, $q_id]);
                } else {
                    // INSERT new question
                    $stmt_insert_q->execute([$id, $q_text, $new_image_filename, $order]);
                    $submitted_ids[] = $pdo->lastInsertId();
                }
            }

            $ids_to_delete = array_diff(array_keys($db_questions), $submitted_ids);
            if (!empty($ids_to_delete)) {
                $placeholders = implode(',', array_fill(0, count($ids_to_delete), '?'));
                $stmt_delete_q = $pdo->prepare("DELETE FROM TaskQuestions WHERE id IN ($placeholders)");
                foreach ($ids_to_delete as $delete_id) {
                    $image_to_delete = $db_questions[$delete_id];
                    if ($image_to_delete && file_exists(UPLOAD_DIR . $image_to_delete)) {
                        unlink(UPLOAD_DIR . $image_to_delete);
                    }
                }
                $stmt_delete_q->execute(array_values($ids_to_delete));
            }

            $pdo->commit();
            $response = ['success' => true, 'message' => 'تکلیف با موفقیت ویرایش شد.'];
            break;

        case 'delete_task':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('شناسه نامعتبر است.');

            $pdo->beginTransaction();

            $stmt_old_images = $pdo->prepare("SELECT question_image FROM TaskQuestions WHERE task_id = ? AND question_image IS NOT NULL");
            $stmt_old_images->execute([$id]);
            $old_images = $stmt_old_images->fetchAll(PDO::FETCH_COLUMN);
            foreach ($old_images as $img) {
                if (file_exists(UPLOAD_DIR . $img)) {
                    unlink(UPLOAD_DIR . $img);
                }
            }

            $stmt = $pdo->prepare("DELETE FROM Tasks WHERE id = ?");
            $stmt->execute([$id]);

            $pdo->commit();
            $response = ['success' => true, 'message' => 'تکلیف با موفقیت حذف شد.'];
            break;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
