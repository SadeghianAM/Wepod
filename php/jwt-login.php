<?php
session_start();
require 'secret.php';

header('Content-Type: application/json');

define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 300);

if (isset($_SESSION['last_attempt_time']) && (time() - $_SESSION['last_attempt_time'] > LOCKOUT_TIME)) {
    $_SESSION['login_attempts'] = 0;
}

if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
    http_response_code(429);
    echo json_encode(['message' => 'تعداد تلاش‌های شما بیش از حد مجاز است. لطفاً ۵ دقیقه دیگر دوباره امتحان کنید.']);
    exit;
}

$users_file_path = __DIR__ . '/../data/users.json';

if (!file_exists($users_file_path)) {
    http_response_code(500);
    echo json_encode(['message' => 'خطای سرور: فایل اطلاعات کاربران یافت نشد.']);
    exit;
}

$json_data = file_get_contents($users_file_path);
$users = json_decode($json_data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['message' => 'خطای سرور: فایل اطلاعات کاربران معتبر نیست.']);
    exit;
}


$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

foreach ($users as $user) {
    if ($user['username'] === $username) {
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['login_attempts'] = 0;
            unset($_SESSION['last_attempt_time']);

            $payload = [
                'id'       => $user['id'],
                'username' => $username,
                'exp'      => time() + (60 * 60 * 12)
            ];
            $token = create_jwt($payload, JWT_SECRET);
            echo json_encode(['token' => $token]);
            exit;
        }
        break;
    }
}

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
$_SESSION['login_attempts']++;
$_SESSION['last_attempt_time'] = time();

http_response_code(401);
echo json_encode(['message' => 'نام کاربری یا رمز عبور اشتباه است.']);


function create_jwt($payload, $secret) {
    $header = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $body = base64url_encode(json_encode($payload));
    $signature = hash_hmac('sha256', "$header.$body", $secret, true);
    $sig_encoded = base64url_encode($signature);
    return "$header.$body.$sig_encoded";
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
