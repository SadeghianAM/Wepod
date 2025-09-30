<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ۱. نیازمندی‌های اولیه و احراز هویت ادمین در همان ابتدا الزامی است
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin');

// ۲. اتصال به دیتابیس
$pdo = require_once __DIR__ . '/../../db/database.php';

header('Content-Type: application/json; charset=utf-8');

// ۳. این فایل فقط متد POST را برای انجام عملیات مدیریت می‌کند
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'متد غیرمجاز.']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('دیتای ورودی نامعتبر است.', 400);
    }

    $action = $input['action'] ?? null;
    $data = $input['data'] ?? null;

    // لیست دسته‌بندی‌های مجاز برای اعتبارسنجی
    $availableCategories = ["عمومی", "احراز هویت", "اعتبار سنجی", "تنظیمات امنیت حساب", "تغییر شماره تلفن همراه", "عدم دریافت پیامک", "کارت فیزیکی", "کارت و حساب دیجیتال", "مسدودی و رفع مسدودی حساب", "انتقال وجه", "خدمات قبض", "شارژ و بسته اینترنت", "تسهیلات برآیند", "تسهیلات برآیند چک یار", "تسهیلات پشتوانه", "تسهیلات پیش درآمد", "تسهیلات پیمان", "تسهیلات تکلیفی", "تسهیلات سازمانی", "بیمه پاسارگاد", "چک", "خدمات چکاد", "صندوق های سرمایه گذاری", "طرح سرمایه گذاری رویش", "دعوت از دوستان", "هدیه دیجیتال", "وی کلاب"];

    switch ($action) {
        case 'create':
            // اعتبارسنجی و پاک‌سازی داده‌ها
            $id = filter_var($data['id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
            $title = htmlspecialchars(trim($data['title']), ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars(trim($data['description']), ENT_QUOTES, 'UTF-8');
            $categories = array_intersect($data['categories'], $availableCategories);

            if (!$id || empty($title) || empty($categories)) {
                throw new Exception('داده‌های ورودی ناقص یا نامعتبر است.', 400);
            }

            $sql = "INSERT INTO wiki (id, title, description, category) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id, $title, $description, json_encode(array_values($categories), JSON_UNESCAPED_UNICODE)]);

            echo json_encode(['success' => true, 'message' => 'پیام جدید با موفقیت ذخیره شد.', 'item' => $data]);
            break;

        case 'update':
            $id = filter_var($data['id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
            $title = htmlspecialchars(trim($data['title']), ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars(trim($data['description']), ENT_QUOTES, 'UTF-8');
            $categories = array_intersect($data['categories'], $availableCategories);

            if (!$id || empty($title) || empty($categories)) {
                throw new Exception('داده‌های ورودی ناقص یا نامعتبر است.', 400);
            }

            $sql = "UPDATE wiki SET title = ?, description = ?, category = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $title,
                $description,
                json_encode(array_values($categories), JSON_UNESCAPED_UNICODE),
                $id
            ]);

            echo json_encode(['success' => true, 'message' => 'پیام با موفقیت به‌روزرسانی شد.', 'item' => $data]);
            break;

        case 'delete':
            $id = filter_var($data['id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
            if (!$id) {
                throw new Exception('شناسه نامعتبر است.', 400);
            }

            $sql = "DELETE FROM wiki WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'پیام با موفقیت حذف شد.']);
            break;

        default:
            throw new Exception('عملیات نامشخص است.', 400);
    }
} catch (Exception $e) {
    // دریافت کد استثنا
    $code = $e->getCode();

    // بررسی اینکه آیا کد یک عدد صحیح و یک کد وضعیت HTTP معتبر (4xx یا 5xx) است یا خیر
    // در غیر این صورت، پیش‌فرض روی 500 (خطای داخلی سرور) تنظیم می‌شود
    $httpStatusCode = is_int($code) && $code >= 400 && $code < 600 ? $code : 500;

    // تنظیم کد وضعیت HTTP معتبر
    http_response_code($httpStatusCode);

    // بازگرداندن پیام خطای واقعی از استثنا به صورت JSON
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
