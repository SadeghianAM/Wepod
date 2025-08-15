<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'jwt-functions.php';

// لیست یوزرنیم‌های مجاز به عنوان ادمین
$adminUsernames = ["abolfazl", "f.alavimoghaddam", "m.pourmosa", "h.mohammadalizadeh", "m.samyari", "ehsan.jafari", "aida.akbari", "a.jamshidvand", "a.sadeghianmajd"];

$is_authorized = false;

if (isset($_COOKIE['jwt_token'])) {
    $token = $_COOKIE['jwt_token'];

    if (verify_jwt($token, JWT_SECRET)) {
        $payload = get_payload($token);
        $username = $payload['username'] ?? '';

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
