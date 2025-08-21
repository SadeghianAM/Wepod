<?php

require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
// تنظیم هدر برای اطمینان از اینکه پاسخ به صورت JSON است
header('Content-Type: application/json');

// دریافت دیتای JSON که از جاوا اسکریپت ارسال شده
$data = file_get_contents('php://input');

// آدرس فایل JSON شما که قرار است بازنویسی شود
$file_path = 'service-status.json';

// داده‌های جدید را در فایل می‌نویسیم
// file_put_contents فایل را به طور کامل بازنویسی می‌کند
if (file_put_contents($file_path, $data) !== false) {
    // اگر موفق بود، پیام موفقیت‌آمیز برگردان
    echo json_encode(['success' => true, 'message' => 'اطلاعات با موفقیت روی سرور ذخیره شد.']);
} else {
    // اگر خطا داد، پیام خطا برگردان
    http_response_code(500); // ارسال کد خطای سرور
    echo json_encode(['success' => false, 'message' => 'خطا در ذخیره‌سازی فایل روی سرور. لطفا دسترسی‌های فایل (Permissions) را بررسی کنید.']);
}
