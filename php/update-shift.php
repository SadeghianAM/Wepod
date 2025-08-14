<?php
// فایل حاوی کلید مخفی شما
require 'secret.php';

// تنظیم هدر برای پاسخ JSON
header('Content-Type: application/json; charset=utf-8');

// =================================================================
// بخش ۱: توابع کمکی
// =================================================================

// JWT Helper Functions (Unchanged)
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
function get_payload($token)
{
    $parts = explode('.', $token);
    if (count($parts) !== 3)
        return null;
    [, $payload,] = $parts;
    return json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
}
function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// --- تابع ویرایش یک شیفت (برای مودال تعاملی) ---
function updateSingleShift(string $filePath, string $expertId, string $date, string $status): void
{
    $fileContents = file_get_contents($filePath);
    if ($fileContents === false)
        throw new Exception("فایل شیفت‌ها خوانده نشد.");

    $masterData = json_decode($fileContents, true);
    if (json_last_error() !== JSON_ERROR_NONE)
        throw new Exception("فرمت فایل شیفت‌ها (JSON) نامعتبر است.");

    $expertFound = false;
    foreach ($masterData['experts'] as &$expert) {
        if ($expert['id'] == $expertId) {
            $expert['shifts'][$date] = $status;
            $expertFound = true;
            break;
        }
    }

    if (!$expertFound)
        throw new Exception("کارشناس با شناسه {$expertId} یافت نشد.");

    $jsonOutput = json_encode($masterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (file_put_contents($filePath, $jsonOutput, LOCK_EX) === false) {
        throw new Exception("امکان نوشتن در فایل {$filePath} وجود ندارد.");
    }
}

// --- NEW FUNCTION for Shift Swapping ---
function performShiftSwap(string $filePath, string $expertA_id, string $dateX, string $expertB_id, string $dateY): void
{
    $fileContents = file_get_contents($filePath);
    if ($fileContents === false)
        throw new Exception("فایل شیفت‌ها خوانده نشد.");

    $masterData = json_decode($fileContents, true);
    if (json_last_error() !== JSON_ERROR_NONE)
        throw new Exception("فرمت فایل شیفت‌ها (JSON) نامعتبر است.");

    $expertA_index = -1;
    $expertB_index = -1;
    $expertA_name = '';
    $expertB_name = '';

    foreach ($masterData['experts'] as $index => $expert) {
        if ($expert['id'] == $expertA_id) {
            $expertA_index = $index;
            $expertA_name = $expert['name'];
        }
        if ($expert['id'] == $expertB_id) {
            $expertB_index = $index;
            $expertB_name = $expert['name'];
        }
    }

    if ($expertA_index === -1)
        throw new Exception("کارشناس اول با شناسه {$expertA_id} یافت نشد.");
    if ($expertB_index === -1)
        throw new Exception("کارشناس دوم با شناسه {$expertB_id} یافت نشد.");

    // Update statuses as per the user's request
    $masterData['experts'][$expertA_index]['shifts'][$dateX] = "عدم حضور (جابجایی با {$expertB_name})";
    $masterData['experts'][$expertB_index]['shifts'][$dateY] = "حضور (جابجایی از {$expertA_name})";

    $jsonOutput = json_encode($masterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (file_put_contents($filePath, $jsonOutput, LOCK_EX) === false) {
        throw new Exception("امکان نوشتن در فایل {$filePath} وجود ندارد.");
    }
}

// Batch update functions (Unchanged)
// ... (Your updateMasterJsonFile and parseScheduleText functions would go here if needed, but they are not used in this specific workflow)

// =================================================================
// بخش ۲: گیت امنیتی (بررسی توکن قبل از هر کاری)
// =================================================================

$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    http_response_code(401);
    echo json_encode(['message' => 'توکن احراز هویت ارسال نشده است.']);
    exit;
}

$token = $matches[1];
if (!verify_jwt($token, JWT_SECRET)) { // JWT_SECRET from secret.php
    http_response_code(403);
    echo json_encode(['message' => 'توکن نامعتبر یا منقضی شده است.']);
    exit;
}

$payload = get_payload($token);
$adminUsernames = ["abolfazl", "m.pourmosa", "m.samyari", "ehsan.jafari", "aida.akbari", "a.jamshidvand", "a.sadeghianmajd"];
$username = $payload['data']['username'] ?? ($payload['username'] ?? '');
if (empty($username) || !in_array($username, $adminUsernames)) {
    http_response_code(403);
    echo json_encode(['message' => 'شما اجازه دسترسی به این عملیات را ندارید.']);
    exit;
}

// =================================================================
// بخش ۳: مسیریابی درخواست و اجرای عملیات
// =================================================================

$filePath = $_SERVER['DOCUMENT_ROOT'] . '/data/shifts.json';
if (!file_exists(dirname($filePath))) {
    mkdir(dirname($filePath), 0775, true);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('درخواست نامعتبر است. فرمت JSON اشتباه است.');
    }

    $action = $input['action'] ?? null;

    if ($action === 'swap') {
        // --- Handle Shift Swap ---
        if (!isset($input['expertA_id'], $input['dateX'], $input['expertB_id'], $input['dateY'])) {
            throw new Exception('اطلاعات مورد نیاز برای جابجایی ناقص است.');
        }
        performShiftSwap($filePath, $input['expertA_id'], $input['dateX'], $input['expertB_id'], $input['dateY']);
        echo json_encode(['success' => true, 'message' => 'جابجایی شیفت با موفقیت ثبت شد.']);

    } elseif ($action === 'update') {
        // --- Handle Single Shift Update ---
        if (!isset($input['expertId'], $input['date'], $input['status'])) {
            throw new Exception('اطلاعات مورد نیاز برای ویرایش شیفت ناقص است.');
        }
        updateSingleShift($filePath, $input['expertId'], $input['date'], $input['status']);
        echo json_encode(['success' => true, 'message' => 'شیفت با موفقیت به‌روزرسانی شد.']);

    } else {
        // Handle other actions like batch update if they exist, otherwise throw error.
        // For example, your original batch update from text:
        // if (isset($_POST['schedule_data']) && !empty($_POST['schedule_data'])) { ... }
        throw new Exception('عملیات درخواستی نامشخص یا پشتیبانی نشده است.');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
}
?>
