<?php
// /auth/jwt-functions.php
// Minimal HS256 JWT helpers with a single source of truth for create/verify/decode.
// Secret resolution prefers ENV["JWT_SECRET"], otherwise falls back to secret.php (if present) or a dev default.

function getJwtSecret(): string
{
  $env = getenv('JWT_SECRET');
  if ($env && strlen($env) >= 32) {
    return $env;
  }
  // Try secret.php if available (should define JWT_SECRET constant)
  $secretPath1 = __DIR__ . '/secret.php';
  $secretPath2 = dirname(__DIR__) . '/secret.php';
  if (file_exists($secretPath1)) {
    require_once $secretPath1;
  } elseif (file_exists($secretPath2)) {
    require_once $secretPath2;
  }
  if (defined('JWT_SECRET') && strlen(JWT_SECRET) >= 32) {
    return JWT_SECRET;
  }
  // Dev fallback (DO NOT USE IN PRODUCTION)
  return 'replace-this-with-a-strong-production-secret-key-32bytes-min';
}

function base64url_encode(string $data): string
{
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string
{
  $remainder = strlen($data) % 4;
  if ($remainder) {
    $padlen = 4 - $remainder;
    $data .= str_repeat('=', $padlen);
  }
  return base64_decode(strtr($data, '-_', '+/'));
}

function create_jwt(array $claims, string $secret, string $alg = 'HS256'): string
{
  $header = ['typ' => 'JWT', 'alg' => $alg];
  $h = base64url_encode(json_encode($header, JSON_UNESCAPED_SLASHES));
  $p = base64url_encode(json_encode($claims, JSON_UNESCAPED_SLASHES));
  $data = $h . '.' . $p;
  switch ($alg) {
    case 'HS256':
      $sig = hash_hmac('sha256', $data, $secret, true);
      break;
    case 'HS384':
      $sig = hash_hmac('sha384', $data, $secret, true);
      break;
    case 'HS512':
      $sig = hash_hmac('sha512', $data, $secret, true);
      break;
    default:
      throw new Exception('Unsupported JWT alg: ' . $alg);
  }
  return $data . '.' . base64url_encode($sig);
}

function decode_jwt(string $jwt): array
{
  $parts = explode('.', $jwt);
  if (count($parts) !== 3) {
    return [];
  }
  [$h, $p, $s] = $parts;
  $payload = json_decode(base64url_decode($p), true);
  return is_array($payload) ? $payload : [];
}

function verify_jwt(string $jwt, string $secret, string $alg = 'HS256'): array
{
  $parts = explode('.', $jwt);
  if (count($parts) !== 3) {
    return ['valid' => false, 'reason' => 'format', 'claims' => null];
  }
  [$h, $p, $s] = $parts;
  $data = $h . '.' . $p;

  // Recreate signature
  switch ($alg) {
    case 'HS256':
      $expected = base64url_encode(hash_hmac('sha256', $data, $secret, true));
      break;
    case 'HS384':
      $expected = base64url_encode(hash_hmac('sha384', $data, $secret, true));
      break;
    case 'HS512':
      $expected = base64url_encode(hash_hmac('sha512', $data, $secret, true));
      break;
    default:
      return ['valid' => false, 'reason' => 'alg', 'claims' => null];
  }

  if (!hash_equals($expected, $s)) {
    return ['valid' => false, 'reason' => 'signature', 'claims' => null];
  }

  $claims = json_decode(base64url_decode($p), true);
  if (!is_array($claims)) {
    return ['valid' => false, 'reason' => 'payload', 'claims' => null];
  }

  $now = time();
  if (isset($claims['nbf']) && $now < (int)$claims['nbf']) {
    return ['valid' => false, 'reason' => 'nbf', 'claims' => $claims];
  }
  if (isset($claims['iat']) && $now + 300 < (int)$claims['iat']) {
    // iat is unreasonably in future (>5min skew)
    return ['valid' => false, 'reason' => 'iat', 'claims' => $claims];
  }
  if (isset($claims['exp']) && $now >= (int)$claims['exp']) {
    return ['valid' => false, 'reason' => 'expired', 'claims' => $claims];
  }

  return ['valid' => true, 'reason' => 'ok', 'claims' => $claims];
}
