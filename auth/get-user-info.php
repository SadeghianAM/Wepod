<?php
// /auth/get-user-info.php (نسخه بهبودیافته با Error Handling)

header('Content-Type: application/json; charset=utf-8');

// فایل‌های ضروری را فراخوانی می‌کنیم
try {
  require_once __DIR__ . '/require-auth.php';
  // مسیر اتصال به دیتابیس را با دقت بررسی کنید
  require_once dirname(__DIR__) . '/db/database.php';
} catch (Throwable $e) {
  // اگر فایل‌ها پیدا نشوند، خطای سرور برمی‌گردانیم
  http_response_code(500);
  error_log("Failed to include required files: " . $e->getMessage());
  echo json_encode(['message' => 'خطای داخلی سرور: فایل‌های ضروری یافت نشدند.']);
  exit();
}

// احراز هویت کاربر از طریق توکن موجود در کوکی
$claims = requireAuth(null, '/login.html');
$username = $claims['username'] ?? null;

if (!$username) {
  http_response_code(401);
  echo json_encode(['message' => 'Unauthorized: Missing username in token.']);
  exit();
}

// --- جستجوی کاربر در دیتابیس با try-catch ---
try {
  // متغیر $pdo باید از فایل database.php در دسترس باشد
  if (!isset($pdo)) {
    throw new Exception("Database connection object (\$pdo) not found.");
  }

  // کوئری اصلاح شده برای تطابق با ستون‌های دیتابیس شما (name و id)
  $stmt = $pdo->prepare(
    "SELECT
        id,
        username,
        name AS full_name,
        is_admin,
        CASE WHEN is_admin = 1 THEN 'admin' ELSE 'user' END AS role
     FROM users
     WHERE username = :username COLLATE NOCASE"
  );

  $stmt->execute([':username' => $username]);
  $info = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  // اگر در اتصال یا اجرای کوئری خطایی رخ دهد
  http_response_code(500);
  // خطا را در لاگ سرور ثبت می‌کنیم تا بتوانیم آن را بررسی کنیم
  error_log("Database error in get-user-info.php: " . $e->getMessage());
  // یک پیام عمومی به کاربر نمایش می‌دهیم
  echo json_encode(['message' => 'خطای داخلی سرور در ارتباط با دیتابیس.']);
  exit();
}

if (!$info) {
  http_response_code(404);
  echo json_encode(['message' => 'کاربر در دیتابیس یافت نشد.']);
  exit();
}

// اگر همه چیز موفقیت‌آمیز بود، اطلاعات کاربر را برمی‌گردانیم
echo json_encode(['ok' => true, 'user' => $info]);
