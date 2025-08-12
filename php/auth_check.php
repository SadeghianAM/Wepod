<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'secret.php';

/**
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
 * @param string $data
 * @return string
 */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}


// لیست یوزرنیم‌های مجاز به عنوان ادمین
$adminUsernames = ["abolfazl", "f.alavimoghaddam","m.pourmosa", "h.mohammadalizadeh","m.samyari", "ehsan.jafari", "aida.akbari", "a.jamshidvand", "a.sadeghianmajd"];

$is_authorized = false;

if (isset($_COOKIE['jwt_token'])) {
    $token = $_COOKIE['jwt_token'];

    if (verify_jwt($token, JWT_SECRET)) {
        $payload = get_payload($token);
        $username = $payload['data']['username'] ?? ($payload['username'] ?? '');

        if (!empty($username) && in_array($username, $adminUsernames)) {
            $is_authorized = true;
        }
    }
}

if (!$is_authorized) {
    if (isset($_COOKIE['jwt_token'])) {
        setcookie('jwt_token', '', time() - 3600, '/');
    }

    header('Location: /admin/login.html');
    exit();
}
?>
