<?php
require __DIR__ . '/../php/auth_check.php';

header('Content-Type: application/json');

// دریافت داده‌های ارسالی به صورت JSON
$input = json_decode(file_get_contents('php://input'), true);
$password = $input['password'] ?? null;

if (empty($password)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'رمز عبور ارسال نشده است.']);
    exit;
}

try {
    // ساخت هش با استفاده از الگوریتم امن و پیش‌فرض PHP
    $hash = password_hash($password, PASSWORD_BCRYPT);
    echo json_encode(['hash' => $hash]);
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'خطای داخلی سرور هنگام ساخت هش.']);
}
