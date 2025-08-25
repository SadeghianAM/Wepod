<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');

header('Content-Type: application/json; charset=utf-8');

// لیست دسته‌بندی‌های مجاز برای اعتبارسنجی (باید با لیست جاوا اسکریپت یکسان باشد)
$availableCategories = ["عمومی", "احراز هویت", "اعتبار سنجی", "تنظیمات امنیت حساب", "تغییر شماره تلفن همراه", "عدم دریافت پیامک", "کارت فیزیکی", "کارت و حساب دیجیتال", "مسدودی و رفع مسدودی حساب", "انتقال وجه", "خدمات قبض", "شارژ و بسته اینترنت", "تسهیلات برآیند", "تسهیلات برآیند چک یار", "تسهیلات پشتوانه", "تسهیلات پیش درآمد", "تسهیلات پیمان", "تسهیلات تکلیفی", "تسهیلات سازمانی", "بیمه پاسارگاد", "چک", "خدمات چکاد", "صندوق های سرمایه گذاری", "طرح سرمایه گذاری رویش", "دعوت از دوستان", "هدیه دیجیتال", "وی کلاب"];

// 1. دریافت دیتای خام و تبدیل به آرایه PHP
$json_data = file_get_contents('php://input');
$items = json_decode($json_data, true);

// 2. بررسی اعتبار JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'داده ارسالی معتبر نیست.']);
    exit;
}

// 3. پاک‌سازی و اعتبارسنجی تک تک آیتم‌ها
$sanitized_items = [];
if (is_array($items)) {
    foreach ($items as $item) {
        // ID: باید یک عدد صحیح مثبت باشد
        $sanitized_id = isset($item['id']) ? abs((int)$item['id']) : 0;

        // Title: تبدیل کاراکترهای HTML به معادل امن آن‌ها (جلوگیری از تزریق HTML)
        $sanitized_title = isset($item['title']) ? htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') : '';

        // Description: همانند عنوان، تمام کاراکترهای HTML را امن می‌کنیم.
        $sanitized_description = isset($item['description']) ? htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') : '';

        // Categories: فقط دسته‌بندی‌هایی که در لیست مجاز ما وجود دارند، پذیرفته می‌شوند.
        $sanitized_categories = [];
        if (isset($item['categories']) && is_array($item['categories'])) {
            // پیدا کردن اشتراک بین دسته‌بندی‌های ارسالی و لیست مجاز ما
            $sanitized_categories = array_intersect($item['categories'], $availableCategories);
        }

        // فقط آیتم‌هایی که ID و عنوان معتبر دارند را ذخیره می‌کنیم.
        if ($sanitized_id > 0 && !empty(trim($sanitized_title))) {
            $sanitized_items[] = [
                'id' => $sanitized_id,
                'title' => $sanitized_title,
                'categories' => array_values($sanitized_categories), // Reset array keys
                'description' => $sanitized_description,
            ];
        }
    }
}

// 4. ذخیره داده‌های پاک‌شده در فایل
$file_path = __DIR__ . '/wiki.json'; // استفاده از مسیر مطلق
$final_json_data = json_encode($sanitized_items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

if (file_put_contents($file_path, $final_json_data) !== false) {
    echo json_encode(['success' => true, 'message' => 'پیام‌ها با موفقیت روی سرور ذخیره شد.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'خطا در ذخیره‌سازی فایل روی سرور.']);
}
