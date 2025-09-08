<?php
require_once __DIR__ . '/../auth/require-auth.php';
requireAuth('admin');
header('Content-Type: application/json; charset=utf-8');

// Layer 1 Security (No changes)
function containsMaliciousPatterns($input)
{
    $patterns = ['/<script/i', '/onerror\s*=/i', '/onload\s*=/i', '/onmouseover\s*=/i', '/javascript\s*:/i', '/<iframe/i', '/<svg/i'];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) return true;
    }
    return false;
}
$json_data = file_get_contents('php://input');
$decoded_json_data = html_entity_decode($json_data);
if (containsMaliciousPatterns($decoded_json_data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ورودی حاوی محتوای غیرمجاز است و درخواست رد شد.']);
    exit;
}

// Layer 2 Security (UPDATED)
$users = json_decode($json_data, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'فرمت داده ارسالی معتبر نیست.']);
    exit;
}

$sanitized_users = [];
if (is_array($users)) {
    foreach ($users as $user) {
        $sanitized_id = isset($user['id']) ? filter_var($user['id'], FILTER_VALIDATE_INT) : null;
        if ($sanitized_id === null || $sanitized_id === false) continue;

        $sanitized_name = isset($user['name']) ? trim(strip_tags((string)$user['name'])) : '';
        $sanitized_username = isset($user['username']) ? trim(strip_tags((string)$user['username'])) : '';

        // --- NEW: Server-Side Validation ---
        // Validate name: Must be Persian characters, space, or half-space (ZWNJ)
        if (!preg_match('/^[\x{0600}-\x{06FF}\s\x{200C}]+$/u', $sanitized_name)) {
            continue; // Skip user with invalid name format
        }
        // Validate username: Must be English alphanumeric and . _ -
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $sanitized_username)) {
            continue; // Skip user with invalid username format
        }
        // --- End of Validation ---

        $cleanUser = [
            'id'       => $sanitized_id,
            'name'     => $sanitized_name,
            'username' => $sanitized_username,
        ];

        if (isset($user['password']) && !empty($user['password'])) {
            $cleanUser['password_hash'] = password_hash($user['password'], PASSWORD_DEFAULT);
        } else {
            $cleanUser['password_hash'] = isset($user['password_hash']) ? $user['password_hash'] : '';
        }

        if (!empty($cleanUser['name']) && !empty($cleanUser['username'])) {
            $sanitized_users[] = $cleanUser;
        }
    }
}

$file_path = __DIR__ . '/../data/users.json';
$final_json_data = json_encode($sanitized_users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

if (file_put_contents($file_path, $final_json_data) !== false) {
    echo json_encode(['success' => true, 'message' => 'اطلاعات با موفقیت در سرور ذخیره شد.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'خطا در ذخیره فایل روی سرور.']);
}
