<?php
// profile/profile-api.php (Updated Version)

// امنیت: اطمینان از اینکه کاربر لاگین کرده است
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html'); 

// تنظیم هدر خروجی به عنوان JSON
header('Content-Type: application/json; charset=utf-8');

// ✅ **تغییر اصلی اینجاست: اتصال به دیتابیس از طریق فایل مرکزی**
$pdo = require_once __DIR__ . '/../db/database.php';

// تابع کمکی برای ارسال پاسخ JSON و خروج
function send_json_response($data)
{
    echo json_encode($data);
    exit;
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && $action === 'get_my_assets') {
    // گرفتن شناسه کاربر از توکن احراز هویت
    $userId = $claims['sub'] ?? null;

    if (!$userId) {
        http_response_code(401);
        send_json_response(['success' => false, 'error' => 'شناسه کاربری یافت نشد.']);
    }

    try {
        // کوئری برای گرفتن اموال تخصیص داده شده به این کاربر
        $sql = "
            SELECT
                name,
                serial_number,
                assigned_at
            FROM
                assets
            WHERE
                assigned_to_user_id = ?
            ORDER BY
                assigned_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        send_json_response($assets);
    } catch (PDOException $e) {
        http_response_code(500);
        send_json_response(['success' => false, 'error' => 'خطای دیتابیس: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    send_json_response(['success' => false, 'error' => 'درخواست نامعتبر است.']);
}
