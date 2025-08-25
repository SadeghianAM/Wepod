<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');

header('Content-Type: application/json; charset=utf-8');

// 1. دریافت دیتای خام
$json_data = file_get_contents('php://input');
$items = json_decode($json_data, true);

// 2. بررسی اینکه آیا JSON معتبر است یا خیر
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'داده‌های ارسالی فرمت JSON معتبری ندارند.']);
    exit;
}

// 3. پاک‌سازی داده‌ها (مهم‌ترین بخش)
$sanitized_items = [];
if (is_array($items)) {
    foreach ($items as $item) {
        // نام سرویس: فقط متن ساده مجاز است. تمام تگ‌های HTML حذف می‌شوند.
        $sanitized_name = isset($item['name']) ? strip_tags((string)$item['name']) : '';

        // وضعیت: باید یکی از مقادیر مجاز باشد.
        $allowed_statuses = ['فعال', 'غیرفعال', 'اختلال در عملکرد'];
        $sanitized_status = isset($item['status']) && in_array($item['status'], $allowed_statuses) ? $item['status'] : 'نامشخص';

        // توضیحات: فقط تگ‌های امن و بی‌خطر مجاز هستند.
        $allowed_tags = '<b><strong><i><em><u><ul><ol><li>';
        $sanitized_description = isset($item['description']) ? strip_tags((string)$item['description'], $allowed_tags) : '';

        // فقط آیتم‌هایی که نام دارند را اضافه می‌کنیم
        if (!empty(trim($sanitized_name))) {
            $sanitized_items[] = [
                'name' => $sanitized_name,
                'status' => $sanitized_status,
                'description' => $sanitized_description,
            ];
        }
    }
}


// 4. تبدیل آرایه پاک‌سازی شده به JSON و ذخیره آن
$file_path = __DIR__ . '/service-status.json'; // استفاده از مسیر مطلق برای امنیت بیشتر
$final_json_data = json_encode($sanitized_items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

if (file_put_contents($file_path, $final_json_data) !== false) {
    echo json_encode(['success' => true, 'message' => 'اطلاعات با موفقیت روی سرور ذخیره شد.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'خطا در ذخیره‌سازی فایل روی سرور.']);
}
