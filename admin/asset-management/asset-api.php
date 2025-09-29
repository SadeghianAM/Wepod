<?php
// امنیت: اطمینان از اینکه تمام درخواست‌ها از سمت ادمین لاگین شده است
require_once __DIR__ . '/../../auth/require-auth.php';
requireAuth('admin');

// تنظیم هدر خروجی به عنوان JSON
header('Content-Type: application/json; charset=utf-8');

// مسیر فایل دیتابیس (مشترک با سیستم کاربران)
$db_path = __DIR__ . '/../../db/database.db';
$pdo = null;

try {
    // اتصال به دیتابیس SQLite
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // فعال کردن پشتیبانی از کلید خارجی در SQLite
    $pdo->exec('PRAGMA foreign_keys = ON;');

    // ایجاد جدول اموال (assets) اگر وجود نداشته باشد
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS assets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            serial_number TEXT NOT NULL UNIQUE,
            status TEXT NOT NULL DEFAULT 'In Stock',
            assigned_to_user_id INTEGER,
            assigned_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (assigned_to_user_id) REFERENCES users(id)
        )
    ");
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'خطا در اتصال به دیتابیس: ' . $e->getMessage()]);
    exit;
}

// تابع کمکی برای ارسال پاسخ JSON و خروج
function send_json_response($data)
{
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    switch ($action) {
        case 'get_assets':
            // ✅ *** CHANGE: Added assigned_at to the SELECT statement ***
            $sql = "
                SELECT
                    a.id,
                    a.name,
                    a.serial_number,
                    a.status,
                    u.name AS assigned_to_name,
                    strftime('%Y-%m-%d', a.assigned_at) AS assigned_at_formatted
                FROM
                    assets a
                LEFT JOIN
                    users u ON a.assigned_to_user_id = u.id
                ORDER BY
                    a.id DESC
            ";
            $stmt = $pdo->query($sql);
            $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_json_response($assets);
            break;

        case 'get_experts':
            $stmt = $pdo->query("SELECT id, username, name FROM users ORDER BY name ASC");
            $experts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_json_response($experts);
            break;

        default:
            http_response_code(400);
            send_json_response(['success' => false, 'error' => 'عملیات GET نامعتبر است.']);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        send_json_response(['success' => false, 'error' => 'داده‌های JSON ارسال شده نامعتبر است.']);
    }

    switch ($action) {
        case 'add_asset':
            $name = $data['name'] ?? null;
            $serial = $data['serial'] ?? null;
            if (!$name || !$serial) {
                http_response_code(400);
                send_json_response(['success' => false, 'error' => 'نام کالا و شماره سریال الزامی است.']);
            }
            try {
                $sql = "INSERT INTO assets (name, serial_number) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $serial]);
                send_json_response(['success' => true]);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    http_response_code(409);
                    send_json_response(['success' => false, 'error' => 'کالایی با این شماره سریال قبلا ثبت شده است.']);
                } else {
                    http_response_code(500);
                    send_json_response(['success' => false, 'error' => 'خطای دیتابیس: ' . $e->getMessage()]);
                }
            }
            break;

        case 'assign_asset':
            $asset_id = $data['asset_id'] ?? null;
            $user_id = $data['user_id'] ?? null;
            $assigned_at = $data['assigned_at'] ?? null;

            if (!$asset_id || !$user_id) {
                http_response_code(400);
                send_json_response(['success' => false, 'error' => 'شناسه کالا و شناسه کارشناس الزامی است.']);
            }

            if (empty($assigned_at)) {
                $sql = "UPDATE assets SET status = 'Assigned', assigned_to_user_id = ?, assigned_at = CURRENT_TIMESTAMP WHERE id = ?";
                $params = [$user_id, $asset_id];
            } else {
                $sql = "UPDATE assets SET status = 'Assigned', assigned_to_user_id = ?, assigned_at = ? WHERE id = ?";
                $params = [$user_id, $assigned_at, $asset_id];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            send_json_response(['success' => true]);
            break;

        case 'edit_asset':
            $asset_id = $data['asset_id'] ?? null;
            $name = $data['name'] ?? null;
            $serial = $data['serial'] ?? null;

            if (!$asset_id || !$name || !$serial) {
                http_response_code(400);
                send_json_response(['success' => false, 'error' => 'شناسه، نام و شماره سریال کالا الزامی است.']);
            }

            try {
                $sql = "UPDATE assets SET name = ?, serial_number = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $serial, $asset_id]);
                send_json_response(['success' => true]);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    http_response_code(409);
                    send_json_response(['success' => false, 'error' => 'کالایی با این شماره سریال قبلا ثبت شده است.']);
                } else {
                    http_response_code(500);
                    send_json_response(['success' => false, 'error' => 'خطای دیتابیس: ' . $e->getMessage()]);
                }
            }
            break;

        case 'return_asset':
            $asset_id = $data['asset_id'] ?? null;
            if (!$asset_id) {
                http_response_code(400);
                send_json_response(['success' => false, 'error' => 'شناسه کالا مشخص نشده است.']);
            }
            $sql = "UPDATE assets SET status = 'In Stock', assigned_to_user_id = NULL, assigned_at = NULL WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$asset_id]);
            send_json_response(['success' => true]);
            break;

        case 'delete_asset':
            $asset_id = $data['asset_id'] ?? null;
            if (!$asset_id) {
                http_response_code(400);
                send_json_response(['success' => false, 'error' => 'شناسه کالا مشخص نشده است.']);
            }
            $sql = "DELETE FROM assets WHERE id = ? AND status = 'In Stock'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$asset_id]);
            if ($stmt->rowCount() > 0) {
                send_json_response(['success' => true, 'message' => 'کالا با موفقیت حذف شد.']);
            } else {
                http_response_code(400);
                send_json_response(['success' => false, 'error' => 'کالا یافت نشد یا در انبار موجود نبود و قابل حذف نیست.']);
            }
            break;

        default:
            http_response_code(400);
            send_json_response(['success' => false, 'error' => 'عملیات POST نامعتبر است.']);
    }
} else {
    http_response_code(405);
    send_json_response(['success' => false, 'error' => 'متد درخواست غیرمجاز است.']);
}
