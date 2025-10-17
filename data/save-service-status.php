<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');

header('Content-Type: application/json; charset=utf-8');

// --- لایه امنیتی ۱: رد کردن درخواست‌های آشکارا مخرب ---

/**
 * Checks if the input string contains common and dangerous XSS patterns.
 * @param string $input The raw input string (the entire JSON body).
 * @return bool
 */
function containsMaliciousPatterns($input)
{
    $patterns = [
        '/<script/i',           // Script tags
        '/onerror\s*=/i',       // onerror event
        '/onload\s*=/i',        // onload event
        '/onmouseover\s*=/i',   // onmouseover event
        '/javascript\s*:/i',    // javascript: protocol
        '/<iframe/i',           // Iframe tags
        '/<svg/i',               // SVG tags
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true; // Malicious pattern found
        }
    }
    return false; // Input appears safe
}

// 1. Get the raw data
$json_data = file_get_contents('php://input');

// --- خط کد جدید و اصلاحی ---
// Decode any HTML entities (&lt;, &gt;, etc.) back to their original characters (<, >)
// This ensures the security check works even if the browser encodes the input.
$decoded_json_data = html_entity_decode($json_data);
// --- پایان خط کد اصلاحی ---

// 2. Perform the Layer 1 security check on the decoded data
if (containsMaliciousPatterns($decoded_json_data)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'ورودی حاوی محتوای غیرمجاز یا خطرناک است و درخواست رد شد.']);
    exit; // Stop script execution
}


// --- لایه امنیتی ۲: پاک‌سازی دقیق ورودی‌ها ---

// 3. Decode the JSON data for processing
// We use the original $json_data here to correctly handle legitimate encoded characters if needed.
$items = json_decode($json_data, true);

// Check if JSON is valid
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'The submitted data is not in a valid JSON format.']);
    exit;
}

// 4. Sanitize the data
$sanitized_items = [];
if (is_array($items)) {
    foreach ($items as $item) {
        // Sanitize name: only plain text is allowed, all HTML tags are stripped.
        $sanitized_name = isset($item['name']) ? strip_tags((string)$item['name']) : '';

        // Sanitize status: must be one of the allowed values.
        $allowed_statuses = ['فعال', 'غیرفعال', 'اختلال در عملکرد'];
        $sanitized_status = isset($item['status']) && in_array($item['status'], $allowed_statuses) ? $item['status'] : 'نامشخص';

        // Sanitize description: only safe, harmless tags are allowed.
        $allowed_tags = '<b><strong><p><div><br>';
        $sanitized_description = isset($item['description']) ? strip_tags((string)$item['description'], $allowed_tags) : '';

        // Only add items that have a name.
        if (!empty(trim($sanitized_name))) {
            $sanitized_items[] = [
                'name' => $sanitized_name,
                'status' => $sanitized_status,
                'description' => $sanitized_description,
            ];
        }
    }
}

// 5. Convert the sanitized array to JSON and save it
$file_path = __DIR__ . '/service-status.json';
$final_json_data = json_encode($sanitized_items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

if (file_put_contents($file_path, $final_json_data) !== false) {
    echo json_encode(['success' => true, 'message' => 'Information was successfully saved on the server.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error saving the file on the server.']);
}
