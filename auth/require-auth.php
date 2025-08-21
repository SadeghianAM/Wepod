<?php
// /auth/require-auth.php
// Middleware for protecting pages with JWT cookie.
// Include this file, then call: $claims = requireAuth(/* 'admin' or null */, '/auth/login.html');

require_once __DIR__ . '/jwt-functions.php';

if (!function_exists('requireAuth')) {
  function requireAuth($requiredRole = null, $redirect = '/login.html')
  {
    // Read token from HttpOnly cookie
    $token = isset($_COOKIE['jwt_token']) ? $_COOKIE['jwt_token'] : null;
    if (!$token) {
      header('Location: ' . $redirect);
      exit();
    }

    $secret = getJwtSecret();
    $verify = verify_jwt($token, $secret);
    if (!isset($verify['valid']) || !$verify['valid']) {
      // Invalid or expired token: clear cookie and redirect

      // Compatible clear for PHP < 7.3
      setcookie('jwt_token', '', time() - 3600, '/', '', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'), true);

      // Modern clear with attributes (PHP 7.3+)
      if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
        setcookie('jwt_token', '', [
          'expires'  => time() - 3600,
          'path'     => '/',
          'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
          'httponly' => true,
          'samesite' => 'Strict'
        ]);
      }

      header('Location: ' . $redirect);
      exit();
    }

    $claims = isset($verify['claims']) && is_array($verify['claims']) ? $verify['claims'] : array();

    // Optional role check
    if ($requiredRole !== null) {
      if (!isset($claims['role']) || $claims['role'] !== $requiredRole) {
        header('Location: ' . $redirect);
        exit();
      }
    }

    return $claims;
  }
}
