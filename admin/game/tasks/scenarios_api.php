<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../auth/require-auth.php';
$claims = requireAuth('admin');
require_once __DIR__ . '/../../../db/database.php';

define('UPLOAD_DIR', __DIR__ . '/../../../quiz-img/');

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
    if (!$pdo->inTransaction()) $pdo->beginTransaction();

    switch ($action) {
        case 'get_scenario':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('شناسه سناریو نامعتبر است.');
            $stmt = $pdo->prepare("SELECT * FROM Scenarios WHERE id = ?");
            $stmt->execute([$id]);
            $scenario = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($scenario) {
                $stmt_q = $pdo->prepare("SELECT id, challenge_text, challenge_image, challenge_order FROM Challenges WHERE scenario_id = ? ORDER BY challenge_order");
                $stmt_q->execute([$id]);
                $challenges = $stmt_q->fetchAll(PDO::FETCH_ASSOC);
                foreach ($challenges as &$challenge) {
                    $challenge['image_url'] = !empty($challenge['challenge_image']) ? '/quiz-img/' . $challenge['challenge_image'] : null;
                }
                $scenario['challenges'] = $challenges;
                $response = ['success' => true, 'scenario' => $scenario];
            } else {
                throw new Exception('سناریو یافت نشد.');
            }
            break;

        case 'create_scenario':
            $title = $_POST['title'] ?? null;
            $description = $_POST['description'] ?? '';
            $challenges_text = $_POST['challenges_text'] ?? [];
            if (empty($title) || empty($challenges_text)) throw new Exception('عنوان سناریو و حداقل یک چالش الزامی است.');
            $stmt = $pdo->prepare("INSERT INTO Scenarios (title, description) VALUES (?, ?)");
            $stmt->execute([$title, $description]);
            $scenarioId = $pdo->lastInsertId();
            $stmt_c = $pdo->prepare("INSERT INTO Challenges (scenario_id, challenge_text, challenge_image, challenge_order) VALUES (?, ?, ?, ?)");
            foreach ($challenges_text as $index => $c_text) {
                if (empty(trim($c_text))) continue;
                $image_filename = handle_upload($_FILES['challenges_images'] ?? [], $index);
                $stmt_c->execute([$scenarioId, $c_text, $image_filename, $index + 1]);
            }
            $response = ['success' => true, 'message' => 'سناریو با موفقیت ایجاد شد.'];
            break;

        case 'update_scenario':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $title = $_POST['title'] ?? null;
            $description = $_POST['description'] ?? '';
            $challenges_text = $_POST['challenges_text'] ?? [];
            $challenges_ids = $_POST['challenges_ids'] ?? [];
            if (!$id || empty($title)) throw new Exception('شناسه و عنوان سناریو الزامی است.');
            $stmt = $pdo->prepare("UPDATE Scenarios SET title = ?, description = ? WHERE id = ?");
            $stmt->execute([$title, $description, $id]);
            $stmt_db_c = $pdo->prepare("SELECT id, challenge_image FROM Challenges WHERE scenario_id = ?");
            $stmt_db_c->execute([$id]);
            $db_challenges = $stmt_db_c->fetchAll(PDO::FETCH_KEY_PAIR);
            $submitted_ids = [];
            $stmt_update_c = $pdo->prepare("UPDATE Challenges SET challenge_text = ?, challenge_image = ?, challenge_order = ? WHERE id = ?");
            $stmt_insert_c = $pdo->prepare("INSERT INTO Challenges (scenario_id, challenge_text, challenge_image, challenge_order) VALUES (?, ?, ?, ?)");
            foreach ($challenges_text as $index => $c_text) {
                if (empty(trim($c_text))) continue;
                $c_id = !empty($challenges_ids[$index]) ? (int)$challenges_ids[$index] : null;
                $new_image = handle_upload($_FILES['challenges_images'] ?? [], $index);
                if ($c_id && array_key_exists($c_id, $db_challenges)) {
                    $submitted_ids[] = $c_id;
                    $image_to_set = $new_image ?? $db_challenges[$c_id];
                    if ($new_image && !empty($db_challenges[$c_id]) && file_exists(UPLOAD_DIR . $db_challenges[$c_id])) {
                        unlink(UPLOAD_DIR . $db_challenges[$c_id]);
                    }
                    $stmt_update_c->execute([$c_text, $image_to_set, $index + 1, $c_id]);
                } else {
                    $stmt_insert_c->execute([$id, $c_text, $new_image, $index + 1]);
                    $submitted_ids[] = $pdo->lastInsertId();
                }
            }
            $ids_to_delete = array_diff(array_keys($db_challenges), $submitted_ids);
            if (!empty($ids_to_delete)) {
                foreach ($ids_to_delete as $delete_id) {
                    if (!empty($db_challenges[$delete_id]) && file_exists(UPLOAD_DIR . $db_challenges[$delete_id])) {
                        unlink(UPLOAD_DIR . $db_challenges[$delete_id]);
                    }
                }
                $placeholders = implode(',', array_fill(0, count($ids_to_delete), '?'));
                $stmt_delete_c = $pdo->prepare("DELETE FROM Challenges WHERE id IN ($placeholders)");
                $stmt_delete_c->execute(array_values($ids_to_delete));
            }
            $response = ['success' => true, 'message' => 'سناریو با موفقیت ویرایش شد.'];
            break;

        case 'delete_scenario':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('شناسه نامعتبر است.');
            $stmt_images = $pdo->prepare("SELECT challenge_image FROM Challenges WHERE scenario_id = ? AND challenge_image IS NOT NULL");
            $stmt_images->execute([$id]);
            foreach ($stmt_images->fetchAll(PDO::FETCH_COLUMN) as $img) {
                if (file_exists(UPLOAD_DIR . $img)) unlink(UPLOAD_DIR . $img);
            }
            $stmt = $pdo->prepare("DELETE FROM Scenarios WHERE id = ?");
            $stmt->execute([$id]);
            $response = ['success' => true, 'message' => 'سناریو با موفقیت حذف شد.'];
            break;
    }
    if ($pdo->inTransaction()) $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
