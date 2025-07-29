<?php
require 'secret.php';

header('Content-Type: application/json');

// کاربران تستی
$users = [
  ['username' => 'admin', 'password' => '1234', 'role' => 'ADMIN'],
  ['username' => 'editor', 'password' => '5678', 'role' => 'EDITOR']
];

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

foreach ($users as $user) {
  if ($user['username'] === $username && $user['password'] === $password) {
    $payload = [
      'username' => $username,
      'role' => $user['role'],
      'exp' => time() + (60 * 60) // 1 ساعت
    ];
    $token = create_jwt($payload, JWT_SECRET);
    echo json_encode(['token' => $token]);
    exit;
  }
}

http_response_code(401);
echo json_encode(['message' => 'نام کاربری یا رمز عبور اشتباه است.']);

// ===== JWT ساخت =====
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
