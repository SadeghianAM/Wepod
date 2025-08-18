<?php
// logout.php
header('Content-Type: application/json');

// کوکی را با تنظیم زمان انقضا در گذشته، پاک می‌کنیم
setcookie('jwt_token', '', time() - 3600, '/');

echo json_encode(['message' => 'خروج با موفقیت انجام شد.']);
