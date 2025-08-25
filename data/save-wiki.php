<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');

header('Content-Type: application/json; charset=utf-8');

// --- Security Layer 1: Rejecting Blatantly Malicious Requests ---

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

// --- FINAL FIX APPLIED HERE ---
// Decode any HTML entities (&lt;, &gt;, etc.) back to their original characters (<, >)
// This ensures the security check works even if the browser encodes the input.
$decoded_json_data = html_entity_decode($json_data);
// --- END OF FIX ---


// 2. Perform the Layer 1 security check on the decoded data
if (containsMaliciousPatterns($decoded_json_data)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Input contains forbidden or dangerous content and the request was rejected.']);
    exit; // Stop script execution
}

// --- Security Layer 2: Sanitizing Inputs Thoroughly ---

// List of allowed categories for validation
$availableCategories = ["عمومی", "احراز هویت", "اعتبار سنجی", "تنظیمات امنیت حساب", "تغییر شماره تلفن همراه", "عدم دریافت پیامک", "کارت فیزیکی", "کارت و حساب دیجیتال", "مسدودی و رفع مسدودی حساب", "انتقال وجه", "خدمات قبض", "شارژ و بسته اینترنت", "تسهیلات برآیند", "تسهیلات برآیند چک یار", "تسهیلات پشتوانه", "تسهیلات پیش درآمد", "تسهیلات پیمان", "تسهیلات تکلیفی", "تسهیلات سازمانی", "بیمه پاسارگاد", "چک", "خدمات چکاد", "صندوق های سرمایه گذاری", "طرح سرمایه گذاری رویش", "دعوت از دوستان", "هدیه دیجیتال", "وی کلاب"];

// 3. Decode the JSON data for processing
$items = json_decode($decoded_json_data, true);

// Check if JSON is valid
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'The submitted data is not in a valid JSON format.']);
    exit;
}

// 4. Sanitize and validate each item
$sanitized_items = [];
if (is_array($items)) {
    foreach ($items as $item) {
        // ID: Must be a positive integer.
        $sanitized_id = isset($item['id']) ? abs((int)$item['id']) : 0;

        // Title: Convert HTML special characters to their safe equivalents.
        $sanitized_title = isset($item['title']) ? htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') : '';

        // Description: Same as title, secure all HTML characters.
        $sanitized_description = isset($item['description']) ? htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') : '';

        // Categories: Only categories from our allowed list are accepted.
        $sanitized_categories = [];
        if (isset($item['categories']) && is_array($item['categories'])) {
            // Find the intersection between submitted categories and our whitelist.
            $sanitized_categories = array_intersect($item['categories'], $availableCategories);
        }

        // Only save items with a valid ID and title.
        if ($sanitized_id > 0 && !empty(trim($sanitized_title))) {
            $sanitized_items[] = [
                'id' => $sanitized_id,
                'title' => $sanitized_title,
                'categories' => array_values($sanitized_categories), // Reset array keys
                'description' => $sanitized_description,
            ];
        }
    }
}

// 5. Save the sanitized data to the file
$file_path = __DIR__ . '/wiki.json'; // Use absolute path
$final_json_data = json_encode($sanitized_items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

if (file_put_contents($file_path, $final_json_data) !== false) {
    echo json_encode(['success' => true, 'message' => 'Messages were successfully saved on the server.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error saving the file on the server.']);
}
