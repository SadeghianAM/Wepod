<?php
// /unified/login.php
// Unified login endpoint: validates user, issues JWT as HttpOnly cookie, returns redirect.
// Accepts JSON or form-encoded POST.
// POST JSON/Form: username, password

require_once __DIR__ . '/jwt-functions.php';

header('Content-Type: application/json; charset=utf-8');

// ---- Configuration ----
const ADMIN_USERNAMES = ["abolfazl", "f.alavimoghaddam", "m.pourmosa", "h.mohammadalizadeh", "m.samyari", "ehsan.jafari", "aida.akbari", "a.jamshidvand", "a.sadeghianmajd"];
const COOKIE_NAME = 'jwt_token';
const TOKEN_TTL_SECONDS = 60 * 60 * 8; // 8 hours
const REDIRECT_ADMIN = '/admin/index.php';
const REDIRECT_USER  = '/index.html';
const DEBUG_LOGIN    = true; // set true temporarily to log details to error_log()

// Optional: naive, session-based rate limit (per browser session)
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
  $window = 15 * 60; // 15 minutes
  if (time() - $_SESSION['last_attempt'] > $window) {
    $_SESSION['login_attempts'] = 0;
  }
  return $_SESSION['login_attempts'] >= 8; // 8 tries in 15 mins
}

function bumpAttempts()
{
  $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
  $_SESSION['last_attempt'] = time();
}

// ---- Helpers ----
function readJsonBody(): array
{
  $input = file_get_contents('php://input');
  $data = json_decode($input, true);
  return is_array($data) ? $data : [];
}

function readBodyAny(): array
{
  $data = readJsonBody();
  if (!empty($data)) return $data;
  // fallback to form fields
  if (!empty($_POST)) {
    return [
      'username' => isset($_POST['username']) ? $_POST['username'] : '',
      'password' => isset($_POST['password']) ? $_POST['password'] : ''
    ];
  }
  return [];
}

function loadUsers(): array
{
  // 1) explicit env path
  $envPath = getenv('USERS_JSON_PATH');
  if ($envPath && file_exists($envPath)) {
    $json = json_decode(file_get_contents($envPath), true);
    if (is_array($json)) return $json;
  }
  // 2) typical relative paths
  $candidates = [
    __DIR__ . '/data/users.json',
    dirname(__DIR__) . '/data/users.json',
    __DIR__ . '/../data/users.json',
    $_SERVER['DOCUMENT_ROOT'] . '/data/users.json'
  ];
  foreach ($candidates as $p) {
    if ($p && file_exists($p)) {
      $json = json_decode(file_get_contents($p), true);
      if (is_array($json)) return $json;
    }
  }
  return [];
}

function findUser(string $username, array $users): ?array
{
  foreach ($users as $u) {
    if (isset($u['username']) && strcasecmp($u['username'], $username) === 0) {
      return $u;
    }
    // fallback: some datasets use 'user' or 'email' as login
    if (isset($u['user']) && strcasecmp($u['user'], $username) === 0) {
      return $u;
    }
    if (isset($u['email']) && strcasecmp($u['email'], $username) === 0) {
      return $u;
    }
  }
  return null;
}

function normalize_bcrypt_prefix($hash)
{
  if (!is_string($hash)) return $hash;
  if (strpos($hash, '$2b$') === 0 || strpos($hash, '$2a$') === 0) {
    return '$2y$' . substr($hash, 4);
  }
  return $hash;
}

function getUserHash(array $user)
{
  foreach (['password', 'hashed_password', 'password_hash', 'hash'] as $key) {
    if (isset($user[$key]) && is_string($user[$key]) && trim($user[$key]) !== '') {
      return trim($user[$key]);
    }
  }
  return null;
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

$users = loadUsers();
if (DEBUG_LOGIN) error_log("LOGIN: loaded users count=" . count($users));

$user = findUser($username, $users);
if (DEBUG_LOGIN) error_log("LOGIN: user " . $username . " found? " . ($user ? 'yes' : 'no'));

if (!$user) {
  bumpAttempts();
  http_response_code(401);
  echo json_encode(['message' => 'نام کاربری یا کلمه عبور نادرست است.']);
  exit();
}

$hashed = getUserHash($user);
if (DEBUG_LOGIN) error_log("LOGIN: hash key detected? " . ($hashed ? 'yes' : 'no'));

if (!$hashed) {
  bumpAttempts();
  http_response_code(401);
  echo json_encode(['message' => 'نام کاربری یا کلمه عبور نادرست است.']);
  exit();
}

// normalize and verify
$hashed = normalize_bcrypt_prefix($hashed);

// Some datasets accidentally include quotes or spaces
$hashed = trim($hashed, " \t\n\r\0\x0B\"'");

$ok = password_verify($password, $hashed);
if (DEBUG_LOGIN) error_log("LOGIN: password_verify=" . ($ok ? 'true' : 'false'));
if (!$ok) {
  bumpAttempts();
  http_response_code(401);
  echo json_encode(['message' => 'نام کاربری یا کلمه عبور نادرست است.']);
  exit();
}

// Determine role
$unameForRole = isset($user['username']) ? $user['username'] : $username;
$role = in_array($unameForRole, ADMIN_USERNAMES, true) ? 'admin' : 'user';

// Build JWT
$now = time();
$claims = [
  'jti'      => bin2hex(random_bytes(16)), // <-- تغییر کلیدی در اینجا اعمال شده است
  'sub'      => isset($user['id']) ? $user['id'] : $unameForRole,
  'username' => $unameForRole,
  'role'     => $role,
  'iat'      => $now,
  'exp'      => $now + TOKEN_TTL_SECONDS
];

$secret = getJwtSecret();
$jwt = create_jwt($claims, $secret, 'HS256');

// Set HttpOnly cookie
$secureFlag = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
// Back-compat for PHP < 7.3
setcookie(COOKIE_NAME, $jwt, $claims['exp'], '/', '', $secureFlag, true);
// Modern attributes (PHP 7.3+)
if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
  setcookie(COOKIE_NAME, $jwt, [
    'expires'  => $claims['exp'],
    'path'     => '/',
    'secure'   => $secureFlag,
    'httponly' => true,
    'samesite' => 'Strict'
  ]);
}

// Reset attempts on success
$_SESSION['login_attempts'] = 0;
$_SESSION['last_attempt'] = $now;

$redirect = ($role === 'admin') ? REDIRECT_ADMIN : REDIRECT_USER;
echo json_encode(['ok' => true, 'redirect' => $redirect]);
