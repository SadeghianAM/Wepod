<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');

header('Content-Type: application/json; charset=utf-8');

// --- لایه امنیتی ۱: رد کردن درخواست‌های آشکارا مخرب ---

function containsMaliciousPatterns($input)
{
    $patterns = [
        '/<script/i',
        '/onerror\s*=/i',
        '/onload\s*=/i',
        '/onmouseover\s*=/i',
        '/javascript\s*:/i',
        '/<iframe/i',
        '/<svg/i',
        '/<\?php/i',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    return false;
}

// ۱. دریافت دیتای خام
$json_data = file_get_contents('php://input');

// --- خط کد جدید و اصلاحی ---
// داده‌های کدگذاری شده را به حالت اولیه برمی‌گردانیم تا الگوها قابل شناسایی باشند
$decoded_json_data = html_entity_decode($json_data);
// --- پایان خط کد اصلاحی ---


// ۲. اجرای بررسی امنیتی لایه اول روی داده‌های رمزگشایی شده
if (containsMaliciousPatterns($decoded_json_data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ورودی حاوی محتوای غیرمجاز یا خطرناک است و درخواست رد شد.']);
    exit;
}

// --- لایه امنیتی ۲: پاک‌سازی دقیق ورودی‌ها (بدون تغییر) ---

$items = json_decode($json_data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'داده ارسالی معتبر نیست.']);
    exit;
}

$sanitized_items = [];
if (is_array($items)) {
    foreach ($items as $item) {
        $sanitized_title = isset($item['title']) ? htmlspecialchars(trim($item['title']), ENT_QUOTES, 'UTF-8') : '';
        $sanitized_description = isset($item['description']) ? strip_tags($item['description'], '<b><strong>') : '';
        $allowed_colors = ['green', 'yellow', 'red'];
        $sanitized_color = isset($item['color']) && in_array($item['color'], $allowed_colors) ? $item['color'] : 'green';

        $validate_date = function ($date_str) {
            if (empty($date_str)) return '';
            $d = DateTime::createFromFormat('Y-m-d', $date_str);
            return $d && $d->format('Y-m-d') === $date_str ? $date_str : '';
        };
        $validate_time = function ($time_str) {
            if (empty($time_str)) return '';
            return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time_str) ? $time_str : '';
        };

        $sanitized_startDate = $validate_date($item['startDate'] ?? '');
        $sanitized_startTime = $validate_time($item['startTime'] ?? '');
        $sanitized_endDate = $validate_date($item['endDate'] ?? '');
        $sanitized_endTime = $validate_time($item['endTime'] ?? '');

        if (!empty($sanitized_title)) {
            $sanitized_items[] = [
                'title' => $sanitized_title,
                'description' => $sanitized_description,
                'color' => $sanitized_color,
                'startDate' => $sanitized_startDate,
                'startTime' => $sanitized_startTime,
                'endDate' => $sanitized_endDate,
                'endTime' => $sanitized_endTime,
            ];
        }
    }
}

$file_path = __DIR__ . '/news-alerts.json';
$final_json_data = json_encode($sanitized_items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

if (file_put_contents($file_path, $final_json_data) !== false) {
    echo json_encode(['success' => true, 'message' => 'اطلاعیه‌ها با موفقیت روی سرور ذخیره شد.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'خطا در ذخیره‌سازی فایل روی سرور.']);
}
