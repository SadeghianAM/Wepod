<?php

require __DIR__ . '/../php/auth_check.php';

// تنظیم هدر برای اطمینان از اینکه پاسخ به صورت JSON است
header('Content-Type: application/json');

// دریافت دیتای JSON که از جاوا اسکریپت ارسال شده
$data = file_get_contents('php://input');

// ❗️ آدرس فایل JSON شما که قرار است بازنویسی شود
$file_path = 'news-alerts.json';

// داده‌های جدید را در فایل می‌نویسیم
if (file_put_contents($file_path, $data) !== false) {
    // اگر موفق بود، پیام موفقیت‌آمیز برگردان
    echo json_encode(['success' => true, 'message' => 'اطلاعیه‌ها با موفقیت روی سرور ذخیره شد.']);
} else {
    // اگر خطا داد، پیام خطا برگردان
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'خطا در ذخیره‌سازی فایل روی سرور.']);
}
?>
