<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// ۱. نیازمندی‌های اولیه و احراز هویت
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin'); // نیازی به ریدایرکت نیست چون این یک API است

// ۲. اتصال به دیتابیس
// فرض می‌شود این فایل PDO را در متغیر $pdo برمی‌گرداند
$pdo = require_once __DIR__ . '/../../db/database.php';

header('Content-Type: application/json; charset=utf-8');

// ۳. مدیریت انواع درخواست‌ها (GET برای خواندن، POST برای نوشتن)
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // خواندن تمام آیتم‌ها از دیتابیس
        $stmt = $pdo->query("SELECT * FROM wiki ORDER BY id ASC");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as &$item) {
            // اطلاعات را از ستون صحیح 'category' بخوان
            // و نتیجه را برای فرانت‌اند در کلید 'categories' ذخیره کن
            $item['categories'] = json_decode($item['category'], true) ?: [];
        }
        unset($item); // این خط برای جلوگیری از باگ‌های احتمالی بعد از حلقه است

        echo json_encode($items);
    } elseif ($method === 'POST') {
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
    } else {
        http_response_code(405); // Method Not Allowed
        echo json_encode(['success' => false, 'message' => 'متد غیرمجاز.']);
    }
} catch (Exception $e) {
    // Get the exception's code.
    $code = $e->getCode();

    // Check if the code is an integer and a valid HTTP status code (4xx or 5xx).
    // Otherwise, default to 500 (Internal Server Error).
    $httpStatusCode = is_int($code) && $code >= 400 && $code < 600 ? $code : 500;

    // Set the valid integer HTTP status code.
    http_response_code($httpStatusCode);

    // Return the actual error message from the exception as JSON.
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
