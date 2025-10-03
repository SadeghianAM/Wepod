<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin');

$pdo = require_once __DIR__ . '/../../db/database.php';

header('Content-Type: application/json; charset=utf-8');

// ۱. مدیریت درخواست GET برای خواندن داده‌ها
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT id, title, description, category FROM wiki ORDER BY id ASC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // تبدیل رشته JSON دسته‌بندی به آرایه
        $items = array_map(function ($item) {
            $categories = json_decode($item['category']);
            // اطمینان از اینکه خروجی همیشه یک آرایه است
            $item['categories'] = is_array($categories) ? $categories : [];
            unset($item['category']); // حذف کلید قدیمی
            return $item;
        }, $results);

        echo json_encode($items);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'خطا در خواندن اطلاعات از دیتابیس.']);
        exit;
    }
}

// ۲. مدیریت درخواست POST برای ایجاد، ویرایش و حذف
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('دیتای ورودی نامعتبر است.', 400);
        }

        $action = $input['action'] ?? null;
        $data = $input['data'] ?? null;

        $availableCategories = ["عمومی", "اختلالات", "احراز هویت", "اعتبار سنجی", "تنظیمات امنیت حساب", "تغییر شماره تلفن همراه", "عدم دریافت پیامک", "کارت فیزیکی", "کارت و حساب دیجیتال", "مسدودی و رفع مسدودی حساب", "انتقال وجه", "خدمات قبض", "شارژ و بسته اینترنت", "تسهیلات برآیند", "تسهیلات برآیند چک یار", "تسهیلات پشتوانه", "تسهیلات پیمان", "تسهیلات تکلیفی", "تسهیلات سازمانی", "بیمه پاسارگاد", "چک", "خدمات چکاد", "صندوق های سرمایه گذاری", "طرح سرمایه گذاری رویش", "دعوت از دوستان", "هدیه دیجیتال", "وی کلاب"];

        switch ($action) {
            case 'create':
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
        $code = $e->getCode();
        $httpStatusCode = is_int($code) && $code >= 400 && $code < 600 ? $code : 500;
        http_response_code($httpStatusCode);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// اگر متد نه GET بود و نه POST، خطا برگردان
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'متد غیرمجاز.']);
