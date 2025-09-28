<?php
// /auth/login.php
// Validates user from SQLite DB, issues JWT, and determines role from DB.

require_once __DIR__ . '/jwt-functions.php';
require_once dirname(__DIR__) . '/db/database.php';

header('Content-Type: application/json; charset=utf-8');

// ---- Configuration ----
// const ADMIN_USERNAMES = [...] // <--- این آرایه ثابت حذف شد
const COOKIE_NAME = 'jwt_token';
const TOKEN_TTL_SECONDS = 60 * 60 * 8; // 8 hours
const REDIRECT_ADMIN = '/admin/index.php';
const REDIRECT_USER  = '/profile';
const DEBUG_LOGIN    = true;

// Session-based rate limit (بدون تغییر)
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
if (!isset($_SESSION['login_attempts'])) {
  $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['last_attempt'])) {
  $_SESSION['last_attempt'] = time();
}

function tooManyAttempts(): bool
{
  $window = 15 * 60;
  if (time() - $_SESSION['last_attempt'] > $window) {
    $_SESSION['login_attempts'] = 0;
  }
  return $_SESSION['login_attempts'] >= 8;
}

function bumpAttempts()
{
  $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
  $_SESSION['last_attempt'] = time();
}

// ---- Helpers ----
function readBodyAny(): array
{
  $input = file_get_contents('php://input');
  $data = json_decode($input, true);
  if (is_array($data) && !empty($data)) return $data;
  if (!empty($_POST)) {
    return [
      'username' => $_POST['username'] ?? '',
      'password' => $_POST['password'] ?? ''
    ];
  }
  return [];
}

function findUser(PDO $pdo, string $username): ?array
{
  // ستون is_admin نیز همراه سایر اطلاعات کاربر خوانده می‌شود
  $stmt = $pdo->prepare("SELECT id, username, password_hash, is_admin FROM users WHERE username = :username COLLATE NOCASE");
  $stmt->execute([':username' => $username]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  return $user ?: null;
}

function normalize_bcrypt_prefix($hash)
{
  if (!is_string($hash)) return $hash;
  if (strpos($hash, '$2b$') === 0 || strpos($hash, '$2a$') === 0) {
    return '$2y$' . substr($hash, 4);
  }
  return $hash;
}

// ---- Main ----
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['message' => 'Method Not Allowed']);
  exit();
}

if (tooManyAttempts()) {
  http_response_code(429);
  echo json_encode(['message' => 'تعداد تلاش‌ها زیاد است. بعداً دوباره امتحان کنید.']);
  exit();
}

$body = readBodyAny();
$username = isset($body['username']) ? trim($body['username']) : '';
$password = isset($body['password']) ? $body['password'] : '';

if ($username === '' || $password === '') {
  bumpAttempts();
  http_response_code(400);
  echo json_encode(['message' => 'نام کاربری و کلمه عبور الزامی است.']);
  exit();
}

$user = findUser($pdo, $username);
if (DEBUG_LOGIN) error_log("LOGIN: user " . $username . " found in DB? " . ($user ? 'yes' : 'no'));

if (!$user || !isset($user['password_hash'])) {
  bumpAttempts();
  http_response_code(401);
  echo json_encode(['message' => 'نام کاربری یا کلمه عبور نادرست است.']);
  exit();
}

$hashed = normalize_bcrypt_prefix($user['password_hash']);
$ok = password_verify($password, $hashed);
if (DEBUG_LOGIN) error_log("LOGIN: password_verify=" . ($ok ? 'true' : 'false'));

if (!$ok) {
  bumpAttempts();
  http_response_code(401);
  echo json_encode(['message' => 'نام کاربری یا کلمه عبور نادرست است.']);
  exit();
}

// ---- Success ----

// *** تغییر کلیدی در اینجا اعمال شده است ***
// نقش کاربر بر اساس مقدار ستون is_admin در دیتابیس تعیین می‌شود
$role = (isset($user['is_admin']) && $user['is_admin'] == 1) ? 'admin' : 'user';
if (DEBUG_LOGIN) error_log("LOGIN: User " . $username . " assigned role: " . $role);

$now = time();
$claims = [
  'jti'      => bin2hex(random_bytes(16)),
  'sub'      => $user['id'] ?? $user['username'],
  'username' => $user['username'],
  'role'     => $role, // نقش تعیین‌شده از دیتابیس در توکن قرار می‌گیرد
  'iat'      => $now,
  'exp'      => $now + TOKEN_TTL_SECONDS
];

$secret = getJwtSecret();
$jwt = create_jwt($claims, $secret, 'HS256');

$secureFlag = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
  setcookie(COOKIE_NAME, $jwt, [
    'expires'  => $claims['exp'],
    'path'     => '/',
    'secure'   => $secureFlag,
    'httponly' => true,
    'samesite' => 'Strict'
  ]);
} else {
  setcookie(COOKIE_NAME, $jwt, $claims['exp'], '/', '', $secureFlag, true);
}

$_SESSION['login_attempts'] = 0;
$_SESSION['last_attempt'] = $now;

// مسیر ریدایرکت بر اساس نقش تعیین‌شده از دیتابیس مشخص می‌شود
$redirect = ($role === 'admin') ? REDIRECT_ADMIN : REDIRECT_USER;
echo json_encode(['ok' => true, 'redirect' => $redirect]);
