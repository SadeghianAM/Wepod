<?php
// /auth/get-user-info.php
// Returns basic user info (from users.json) for the logged-in user.

require_once __DIR__ . '/require-auth.php';
header('Content-Type: application/json; charset=utf-8');

$claims = requireAuth(null, '/login.html');
$username = $claims['username'] ?? null;
if (!$username) {
  http_response_code(401);
  echo json_encode(['message' => 'Unauthorized']);
  exit();
}

function loadUsers(): array
{
  $candidates = [
    __DIR__ . '/data/users.json',
    dirname(__DIR__) . '/data/users.json',
    __DIR__ . '/../data/users.json'
  ];
  foreach ($candidates as $p) {
    if (file_exists($p)) {
      $json = json_decode(file_get_contents($p), true);
      return is_array($json) ? $json : [];
    }
  }
  return [];
}

$users = loadUsers();
$info = null;
foreach ($users as $u) {
  if (isset($u['username']) && strcasecmp($u['username'], $username) === 0) {
    $info = $u;
    unset($info['password']); // hide hash
    break;
  }
}

if (!$info) {
  http_response_code(404);
  echo json_encode(['message' => 'کاربر یافت نشد.']);
  exit();
}

echo json_encode(['ok' => true, 'user' => $info]);
