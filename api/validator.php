<?php

// تنظیم هدر خروجی به عنوان JSON
header('Content-Type: application/json; charset=utf-8');

// ۱. دریافت نوع درخواست از URL (مثال: api.php?type=bins)
$requestType = $_GET['type'] ?? '';

// ۲. تعیین جدول و ستون‌ها بر اساس نوع درخواست
$table = '';
$columns = '';

switch ($requestType) {
    case 'bins':
        $table = 'bank_bins';
        $columns = 'bin, name, logo';
        break;
    case 'codes':
        $table = 'bank_codes';
        $columns = 'code, name, logo';
        break;
    default:
        // اگر نوع درخواست نامعتبر بود، خطای Bad Request برگردان
        http_response_code(400);
        echo json_encode(['error' => 'نوع درخواست نامعتبر است.']);
        exit; // پایان اجرای اسکریپت
}

// ۳. اتصال به دیتابیس و اجرای کوئری
try {
    $pdo = require_once __DIR__ . '/../db/database.php';

    // کوئری داینامیک بر اساس اطلاعات بالا
    $stmt = $pdo->query("SELECT {$columns} FROM {$table}");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ۴. تبدیل نتیجه به JSON و چاپ خروجی
    echo json_encode($data, JSON_UNESCAPED_UNICODE);

    // بستن اتصال
    $pdo = null;
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'خطا در ارتباط با دیتابیس: ' . $e->getMessage()]);
}
