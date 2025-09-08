<?php
require_once __DIR__ . '/../auth/require-auth.php';
requireAuth('admin'); // اطمینان از اینکه کاربر ادمین است

// تعیین مسیر فایل users.json
$filePath = __DIR__ . '/../data/users.json';

// خواندن و ارسال محتوای فایل
if (file_exists($filePath)) {
    header('Content-Type: application/json');
    echo file_get_contents($filePath);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Users file not found.']);
}
