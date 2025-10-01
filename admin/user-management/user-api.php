<?php
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.require_once __DIR__ . '/../../auth/require-auth.php';
requireAuth('admin');

header('Content-Type: application/json');

// مسیر فایل دیتابیس
$db_path = __DIR__ . '/../../db/database.db';
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
    // ✨ تغییر: ستون spin_chances به کوئری SELECT اضافه شد
    $stmt = $pdo->query("SELECT id, name, username, start_work, is_admin, score, role, spin_chances FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
} elseif ($method === 'POST') {
    // مدیریت عملیات افزودن، ویرایش و حذف
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'create':
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            // ✨ تغییر: ستون و مقدار spin_chances به کوئری INSERT اضافه شد
            $sql = "INSERT INTO users (id, name, username, password_hash, start_work, is_admin, score, role, spin_chances) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            // ✨ تغییر: مقدار spin_chances به پارامترهای execute اضافه شد
            $stmt->execute([$data['id'], $data['name'], $data['username'], $hash, $data['start_work'], $data['is_admin'], $data['score'], $data['role'], $data['spin_chances']]);
            echo json_encode(['success' => true, 'id' => $data['id']]);
            break;

        case 'update':
            // ✨ تغییر: ستون spin_chances به کوئری UPDATE اضافه شد
            $sql = "UPDATE users SET id = ?, name = ?, username = ?, start_work = ?, is_admin = ?, score = ?, role = ?, spin_chances = ? WHERE id = ?";
            // ✨ تغییر: مقدار spin_chances به پارامترها اضافه شد
            $params = [$data['new_id'], $data['name'], $data['username'], $data['start_work'], $data['is_admin'], $data['score'], $data['role'], $data['spin_chances'], $data['id']];

            // اگر رمز عبور جدیدی وارد شده بود، آن را هم آپدیت کن
            if (!empty($data['password'])) {
                $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                // ✨ تغییر: ستون spin_chances به کوئری UPDATE (با پسورد) اضافه شد
                $sql = "UPDATE users SET id = ?, name = ?, username = ?, password_hash = ?, start_work = ?, is_admin = ?, score = ?, role = ?, spin_chances = ? WHERE id = ?";
                // ✨ تغییر: مقدار spin_chances به پارامترهای (با پسورد) اضافه شد
                $params = [$data['new_id'], $data['name'], $data['username'], $hash, $data['start_work'], $data['is_admin'], $data['score'], $data['role'], $data['spin_chances'], $data['id']];
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
