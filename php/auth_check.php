<?php
/**
 * این اسکریپت وظیفه بررسی احراز هویت کاربر برای دسترسی به صفحات محافظت‌شده را دارد.
 * این فایل باید در ابتدای تمام صفحاتی که نیاز به دسترسی ادمین دارند، فراخوانی (require) شود.
 */

// شروع session برای استفاده‌های احتمالی بعدی
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ۱. فراخوانی فایل حاوی کلید مخفی
// مسیر این فایل را نسبت به مکان auth_check.php تنظیم کنید.
// برای مثال اگر secret.php یک پوشه بالاتر و در پوشه config قرار دارد: require __DIR__ . '/../config/secret.php';
require 'secret.php';

//================================================
// ۲. توابع کمکی برای کار با JWT
//================================================

/**
 * توکن JWT را با کلید مخفی تأیید می‌کند.
 * @param string $token توکن JWT
 * @param string $secret کلید مخفی
 * @return bool
 */
function verify_jwt($token, $secret) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    [$header, $payload, $signature] = $parts;

    $sig_check = base64url_encode(hash_hmac('sha256', "$header.$payload", $secret, true));
    if (!hash_equals($sig_check, $signature)) return false;

    $payload_arr = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
    if (json_last_error() !== JSON_ERROR_NONE) return false;

    if (!isset($payload_arr['exp']) || $payload_arr['exp'] < time()) return false;

    return true;
}

/**
 * محتوای (payload) یک توکن JWT را استخراج می‌کند.
 * @param string $token توکن JWT
 * @return array|null
 */
function get_payload($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [, $payload, ] = $parts;
    return json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
}

/**
 * داده را به فرمت Base64-URL انکود می‌کند.
 * @param string $data
 * @return string
 */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

//================================================
// ۳. منطق اصلی بررسی دسترسی
//================================================

// لیست یوزرنیم‌های مجاز به عنوان ادمین
$adminUsernames = ["abolfazl", "f.alavimoghaddam","m.pourmosa", "h.mohammadalizadeh","m.samyari", "ehsan.jafari", "aida.akbari", "a.jamshidvand", "a.sadeghianmajd"];

$is_authorized = false;

// توکن را از کوکی مرورگر کاربر می‌خوانیم
if (isset($_COOKIE['jwt_token'])) {
    $token = $_COOKIE['jwt_token'];

    // اگر توکن معتبر بود
    if (verify_jwt($token, JWT_SECRET)) {
        $payload = get_payload($token);
        // یوزرنیم را از داخل توکن استخراج می‌کنیم (مسیر username را مطابق ساختار توکن خود تنظیم کنید)
        $username = $payload['data']['username'] ?? ($payload['username'] ?? '');

        // بررسی می‌کنیم که آیا یوزرنیم استخراج شده در لیست ادمین‌ها وجود دارد یا خیر
        if (!empty($username) && in_array($username, $adminUsernames)) {
            $is_authorized = true;
        }
    }
}

// اگر پس از تمام بررسی‌ها، کاربر هنوز مجاز شناخته نشده بود
if (!$is_authorized) {
    // برای امنیت بیشتر، هر کوکی نامعتبری را پاک می‌کنیم
    if (isset($_COOKIE['jwt_token'])) {
        setcookie('jwt_token', '', time() - 3600, '/');
    }

    // کاربر را به صفحه ورود هدایت می‌کنیم
    header('Location: /admin/login.html'); // <-- مسیر صفحه لاگین خود را در اینجا وارد کنید
    exit(); // اجرای تمام کدهای بعدی را متوقف می‌کنیم
}

// اگر کاربر مجاز بود، این اسکریپت بدون هیچ کاری به پایان می‌رسد و به PHP اجازه می‌دهد
// تا بقیه محتوای صفحه اصلی (مثلاً admin-shifts.php) را پردازش و نمایش دهد.
?>
