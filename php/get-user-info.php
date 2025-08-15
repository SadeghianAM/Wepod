<?php
require_once 'secret.php';
require_once 'auth_check.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_COOKIE['jwt_token'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['message' => 'کاربر وارد نشده است.']);
    exit;
}

$token = $_COOKIE['jwt_token'];

if (!verify_jwt($token, JWT_SECRET)) {
    http_response_code(403); // Forbidden
    setcookie('jwt_token', '', time() - 3600, '/'); // پاک کردن کوکی نامعتبر
    echo json_encode(['message' => 'توکن نامعتبر است.']);
    exit;
}

$payload = get_payload($token);
$userId = $payload['id'] ?? null;

if (!$userId) {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'اطلاعات کاربر در توکن یافت نشد.']);
    exit;
}

$users_file_path = __DIR__ . '/../data/users.json';
if (!file_exists($users_file_path)) {
    http_response_code(500);
    echo json_encode(['message' => 'فایل اطلاعات کاربران یافت نشد.']);
    exit;
}

$json_data = file_get_contents($users_file_path);
$users = json_decode($json_data, true);

foreach ($users as $user) {
    if ($user['id'] === $userId) {
        echo json_encode([
            'id' => $user['id'],
            'name' => $user['name']
        ]);
        exit;
    }
}

http_response_code(404);
echo json_encode(['message' => 'کاربر یافت نشد.']);

?>
