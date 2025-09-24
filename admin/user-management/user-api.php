<?php
// امنیت: اطمینان از اینکه درخواست‌ها از سمت ادمین لاگین شده است
require_once __DIR__ . '/../../auth/require-auth.php';
requireAuth('admin');

header('Content-Type: application/json');

// مسیر فایل دیتابیس
$db_path = __DIR__ . '/../../database.db';

try {
    // اتصال به دیتابیس SQLite
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'خطا در اتصال به دیتابیس: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // خواندن تمام کاربران
    $stmt = $pdo->query("SELECT id, name, username, start_work, is_admin FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
} elseif ($method === 'POST') {
    // مدیریت عملیات افزودن، ویرایش و حذف
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'create':
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (id, name, username, password_hash, start_work, is_admin) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['id'], $data['name'], $data['username'], $hash, $data['start_work'], $data['is_admin']]);
            echo json_encode(['success' => true, 'id' => $data['id']]);
            break;

        case 'update':
            $sql = "UPDATE users SET id = ?, name = ?, username = ?, start_work = ?, is_admin = ? WHERE id = ?";
            $params = [$data['new_id'], $data['name'], $data['username'], $data['start_work'], $data['is_admin'], $data['id']];

            // اگر رمز عبور جدیدی وارد شده بود، آن را هم آپدیت کن
            if (!empty($data['password'])) {
                $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE users SET id = ?, name = ?, username = ?, password_hash = ?, start_work = ?, is_admin = ? WHERE id = ?";
                $params = [$data['new_id'], $data['name'], $data['username'], $hash, $data['start_work'], $data['is_admin'], $data['id']];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true]);
            break;

        case 'delete':
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'عملیات نامعتبر است.']);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'متد درخواست غیرمجاز است.']);
}
