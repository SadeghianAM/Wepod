<?php
// /auth/logout.php
// Clears the JWT cookie and adds the token JTI to a denylist.

require_once __DIR__ . '/jwt-functions.php';

// مسیر فایل لیست سیاه
$denylist_path = dirname(__DIR__) . '/data/token_denylist.json';

// خواندن توکن از کوکی قبل از حذف
$token = $_COOKIE['jwt_token'] ?? null;

if ($token) {
  $claims = decode_jwt($token); // فقط payload را بدون تأیید امضا می‌خوانیم
  if (isset($claims['jti']) && isset($claims['exp'])) {

    // خواندن لیست سیاه فعلی
    $denylist = [];
    if (file_exists($denylist_path)) {
      $denylist = json_decode(file_get_contents($denylist_path), true) ?: [];
    }

    // پاکسازی شناسه‌های منقضی شده برای جلوگیری از حجیم شدن فایل
    $now = time();
    foreach ($denylist as $jti => $exp) {
      if ($now >= $exp) {
        unset($denylist[$jti]);
      }
    }

    // افزودن شناسه جدید به لیست سیاه
    $denylist[$claims['jti']] = $claims['exp'];

    // ذخیره لیست سیاه (با قفل‌گذاری برای جلوگیری از تداخل)
    // نکته: اطمینان حاصل کنید که وب سرور اجازه نوشتن در پوشه /data را دارد.
    file_put_contents($denylist_path, json_encode($denylist, JSON_PRETTY_PRINT), LOCK_EX);
  }
}

// حذف کوکی از مرورگر با تنظیم تاریخ انقضا در گذشته
setcookie('jwt_token', '', [
  'expires' => time() - 3600,
  'path' => '/',
  'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
  'httponly' => true,
  'samesite' => 'Strict'
]);

// پاسخ به درخواست
if (isset($_GET['json'])) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => true]);
} else {
  $redirect = $_GET['redirect'] ?? '/';
  header('Location: ' . $redirect);
}
