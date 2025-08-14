<?php
require 'secret.php';

header('Content-Type: application/json');
$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
  http_response_code(401);
  echo json_encode(['message' => 'توکن ارسال نشده']);
  exit;
}

$token = $matches[1];
if (!verify_jwt($token, JWT_SECRET)) {
  http_response_code(403);
  echo json_encode(['message' => 'توکن نامعتبر است']);
  exit;
}

$payload = get_payload($token);
echo json_encode(['message' => 'موفق', 'user' => $payload]);


function verify_jwt($token, $secret)
{
  [$header, $payload, $signature] = explode('.', $token);
  $sig_check = base64url_encode(
    hash_hmac('sha256', "$header.$payload", $secret, true)
  );
  if (!hash_equals($sig_check, $signature))
    return false;

  $payload_arr = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
  if ($payload_arr['exp'] < time())
    return false;

  return true;
}

function get_payload($token)
{
  [, $payload,] = explode('.', $token);
  return json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
}

function base64url_encode($data)
{
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
