<?php
// فایل: teams_api.php (نسخه نهایی و اصلاح شده)
header('Content-Type: application/json');
require_once 'database.php';

$action = $_REQUEST['action'] ?? null;
$response = ['success' => false, 'message' => 'عملیات نامعتبر است.'];

try {
    switch ($action) {
        case 'get_team':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('شناسه تیم نامعتبر است.');

            $stmt_team = $pdo->prepare("SELECT * FROM Teams WHERE id = ?");
            $stmt_team->execute([$id]);
            $team = $stmt_team->fetch(PDO::FETCH_ASSOC);

            if ($team) {
                $stmt_members = $pdo->prepare("SELECT u.id, u.name FROM Users u JOIN TeamMembers tm ON u.id = tm.user_id WHERE tm.team_id = ?");
                $stmt_members->execute([$id]);
                $team['member_details'] = $stmt_members->fetchAll(PDO::FETCH_ASSOC);
                $response = ['success' => true, 'team' => $team];
            } else {
                throw new Exception('تیم یافت نشد.');
            }
            break;

        case 'create_team':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['name'])) throw new Exception('نام تیم نمی‌تواند خالی باشد.');

            $pdo->beginTransaction();

            if (!empty($data['members'])) {
                $placeholders = implode(',', array_fill(0, count($data['members']), '?'));
                $stmt_check = $pdo->prepare("SELECT user_id FROM TeamMembers WHERE user_id IN ($placeholders)");
                $stmt_check->execute($data['members']);
                if ($existing_member = $stmt_check->fetchColumn()) {
                    throw new Exception("کاربر با شناسه {$existing_member} در حال حاضر عضو تیم دیگری است.");
                }
            }

            $stmt = $pdo->prepare("INSERT INTO Teams (team_name) VALUES (?)");
            $stmt->execute([$data['name']]);
            $id = $pdo->lastInsertId();

            if (!empty($data['members'])) {
                $stmt_member = $pdo->prepare("INSERT INTO TeamMembers (team_id, user_id) VALUES (?, ?)");
                foreach ($data['members'] as $user_id) {
                    $stmt_member->execute([$id, $user_id]);
                }
            }
            $pdo->commit();

            $new_team_data = ['id' => $id, 'team_name' => $data['name']];
            $response = ['success' => true, 'message' => 'تیم با موفقیت ایجاد شد.', 'team' => $new_team_data];
            break;

        case 'update_team':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['id']) || empty($data['name'])) throw new Exception('داده‌های ارسالی ناقص است.');

            $id = $data['id'];
            $pdo->beginTransaction();

            // *** بلوک کد اصلاح شده و ضروری در اینجا اضافه شده است ***
            // بررسی می‌کند که اعضای انتخابی، عضو تیم دیگری نباشند
            if (!empty($data['members'])) {
                $placeholders = implode(',', array_fill(0, count($data['members']), '?'));
                // اعضایی را پیدا کن که در لیست انتخابی هستند اما در تیمی غیر از تیم فعلی حضور دارند
                $sql_check = "SELECT user_id FROM TeamMembers WHERE user_id IN ($placeholders) AND team_id != ?";

                $params = $data['members'];
                $params[] = $id; // شناسه تیم فعلی را به پارامترها اضافه کن

                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->execute($params);

                if ($existing_member = $stmt_check->fetchColumn()) {
                    throw new Exception("کاربر با شناسه {$existing_member} در حال حاضر عضو تیم دیگری است و نمی‌تواند اضافه شود.");
                }
            }
            // *** پایان بلوک کد اصلاح شده ***

            $stmt = $pdo->prepare("UPDATE Teams SET team_name = ? WHERE id = ?");
            $stmt->execute([$data['name'], $id]);

            $stmt_delete = $pdo->prepare("DELETE FROM TeamMembers WHERE team_id = ?");
            $stmt_delete->execute([$id]);

            if (!empty($data['members'])) {
                $stmt_member = $pdo->prepare("INSERT INTO TeamMembers (team_id, user_id) VALUES (?, ?)");
                foreach ($data['members'] as $user_id) {
                    $stmt_member->execute([$id, $user_id]);
                }
            }
            $pdo->commit();
            $response = ['success' => true, 'message' => 'تیم با موفقیت ویرایش شد.'];
            break;

        case 'delete_team':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('شناسه تیم نامعتبر است.');

            $stmt = $pdo->prepare("DELETE FROM Teams WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                $response = ['success' => true, 'message' => 'تیم با موفقیت حذف شد.'];
            } else {
                throw new Exception('تیم یافت نشد.');
            }
            break;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
