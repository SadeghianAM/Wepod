<?php
// profile/profile-api.php (Updated Version)

// امنیت: اطمینان از اینکه کاربر لاگین کرده است
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');

// تنظیم هدر خروجی به عنوان JSON
header('Content-Type: application/json; charset=utf-t');

// اتصال به دیتابیس از طریق فایل مرکزی
$pdo = require_once __DIR__ . '/../db/database.php';

// تابع کمکی برای ارسال پاسخ JSON و خروج
function send_json_response($data)
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE); // اطمینان از نمایش صحیح کاراکترهای فارسی
    exit;
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$userId = $claims['sub'] ?? null;

if (!$userId) {
    http_response_code(401);
    send_json_response(['success' => false, 'error' => 'شناسه کاربری یافت نشد.']);
}

// ==================================================================
// بخش‌های جدید برای مدیریت نظرسنجی
// ==================================================================

// اکشن برای دریافت نظرسنجی فعال
if ($method === 'GET' && $action === 'get_active_poll') {
    try {
        // ۱. پیدا کردن نظرسنجی فعال
        $stmt = $pdo->query("SELECT * FROM polls WHERE is_active = 1 LIMIT 1");
        $active_poll = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$active_poll) {
            send_json_response(['success' => true, 'poll' => null]); // هیچ نظرسنجی فعالی وجود ندارد
        }

        $poll_id = $active_poll['id'];

        // ۲. بررسی اینکه آیا کاربر قبلاً رای داده است؟
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_votes WHERE user_id = ? AND poll_id = ?");
        $stmt->execute([$userId, $poll_id]);
        $user_has_voted = $stmt->fetchColumn() > 0;

        // ۳. گرفتن گزینه‌ها به همراه تعداد رای‌های ثبت‌شده برای هر کدام
        $stmt = $pdo->prepare("
            SELECT po.*, (SELECT COUNT(*) FROM user_votes uv WHERE uv.option_id = po.id) as vote_count
            FROM poll_options po
            WHERE po.poll_id = ?
        ");
        $stmt->execute([$poll_id]);
        $poll_options = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ۴. ارسال تمام اطلاعات به فرانت‌اند
        send_json_response([
            'success' => true,
            'poll' => $active_poll,
            'options' => $poll_options,
            'user_has_voted' => $user_has_voted
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        send_json_response(['success' => false, 'error' => 'خطای دیتابیس: ' . $e->getMessage()]);
    }
}
// اکشن برای ثبت رای کاربر
else if ($method === 'POST' && $action === 'submit_vote') {
    $data = json_decode(file_get_contents('php://input'), true);
    $option_id = $data['option_id'] ?? null;
    $poll_id = $data['poll_id'] ?? null;

    if (!$option_id || !$poll_id) {
        http_response_code(400);
        send_json_response(['success' => false, 'error' => 'اطلاعات ارسالی ناقص است.']);
    }

    try {
        $pdo->beginTransaction();

        // ۱. بررسی مجدد اینکه کاربر قبلا رای نداده باشد
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_votes WHERE user_id = ? AND poll_id = ?");
        $stmt->execute([$userId, $poll_id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("شما قبلاً در این نظرسنجی شرکت کرده‌اید.");
        }

        // ۲. بررسی ظرفیت گزینه
        $stmt = $pdo->prepare("SELECT capacity, (SELECT COUNT(*) FROM user_votes WHERE option_id = ?) as vote_count FROM poll_options WHERE id = ?");
        $stmt->execute([$option_id, $option_id]);
        $option_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$option_data || $option_data['vote_count'] >= $option_data['capacity']) {
            throw new Exception("ظرفیت این گزینه تکمیل شده است.");
        }

        // ۳. ثبت رای
        $stmt = $pdo->prepare("INSERT INTO user_votes (user_id, poll_id, option_id) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $poll_id, $option_id]);

        $pdo->commit();
        send_json_response(['success' => true, 'message' => 'رای شما با موفقیت ثبت شد.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(409); // Conflict
        send_json_response(['success' => false, 'error' => $e->getMessage()]);
    }
}

// ==================================================================
// بخش‌های قبلی (مانند get_my_assets)
// ==================================================================

else if ($method === 'GET' && $action === 'get_my_assets') {
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
}
// در صورتی که هیچ اکشنی مطابقت نداشت
else {
    http_response_code(400);
    send_json_response(['success' => false, 'error' => 'درخواست نامعتبر است.']);
}
