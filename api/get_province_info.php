<?php
// فایل: /api/get_province_info.php

// نتیجه‌ی فایل database.php (که همان آبجکت $pdo است) را در متغیر $pdo ذخیره می‌کنیم
$pdo = require_once __DIR__ . '/../db/database.php';

// تنظیم هدر خروجی به عنوان JSON
header('Content-Type: application/json; charset=utf-8');

$national_code_prefix = isset($_GET['code']) ? trim($_GET['code']) : '';

if (!preg_match('/^\d{3}$/', $national_code_prefix)) {
    echo json_encode(null);
    exit;
}

try {
    // حالا متغیر $pdo به درستی مقداردهی شده و قابل استفاده است
    $stmt = $pdo->prepare("SELECT province, city FROM NationalCode WHERE id = :prefix");

    $stmt->bindValue(':prefix', $national_code_prefix, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($result ?: null);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'خطا در ارتباط با دیتابیس: ' . $e->getMessage()]);
}
