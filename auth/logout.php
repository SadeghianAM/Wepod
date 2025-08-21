<?php
// /auth/logout.php
// Clears the JWT cookie and redirects to login page (or returns JSON).

$redirect = $_GET['redirect'] ?? '/login.html';

setcookie('jwt_token', '', [
  'expires' => time() - 3600,
  'path' => '/',
  'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
  'httponly' => true,
  'samesite' => 'Strict'
]);

if (isset($_GET['json'])) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => true]);
} else {
  header('Location: ' . $redirect);
}
