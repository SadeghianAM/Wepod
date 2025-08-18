<?php
// jwt-functions.php

require_once 'secret.php';

/**
 * @param string $token The JWT token
 * @param string $secret The secret key
 * @return bool
 */
function verify_jwt($token, $secret)
{
    $parts = explode('.', $token);
    if (count($parts) !== 3)
        return false;
    [$header, $payload, $signature] = $parts;

    $sig_check = base64url_encode(hash_hmac('sha256', "$header.$payload", $secret, true));
    if (!hash_equals($sig_check, $signature))
        return false;

    $payload_arr = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
    if (json_last_error() !== JSON_ERROR_NONE)
        return false;

    if (!isset($payload_arr['exp']) || $payload_arr['exp'] < time())
        return false;

    return true;
}

/**
 * @param string $token The JWT token
 * @return array|null
 */
function get_payload($token)
{
    $parts = explode('.', $token);
    if (count($parts) !== 3)
        return null;
    [, $payload,] = $parts;
    return json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
}

/**
 * @param string $data
 * @return string
 */
function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
