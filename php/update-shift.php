<?php
// فایل حاوی کلید مخفی شما
require 'secret.php';

// تنظیم هدر برای پاسخ JSON
header('Content-Type: application/json; charset=utf-8');

// =================================================================
// بخش ۱: توابع امنیتی و JWT
// =================================================================
function verify_jwt($token, $secret)
{
    $parts = explode('.', $token);
    if (count($parts) !== 3)
        return false;
    [$header, $payload, $signature] = $parts;
    $sig_check = rtrim(strtr(base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true)), '+/', '-_'), '=');
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

$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    http_response_code(401);
    echo json_encode(['message' => 'توکن احراز هویت ارسال نشده است.']);
    exit;
}

$token = $matches[1];
// JWT_SECRET از فایل secret.php می‌آید
if (!verify_jwt($token, JWT_SECRET)) {
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
// بخش ۲: منطق اصلی برنامه
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

    $fileContents = file_get_contents($filePath);
    $masterData = ($fileContents) ? json_decode($fileContents, true) : ['experts' => []];
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("فرمت فایل شیفت‌ها (JSON) نامعتبر است.");
    }

    $expertIndexMap = array_column($masterData['experts'], null, 'id');

    switch ($action) {
        case 'swap':
            $eA_id = $input['expertA_id'];
            $eB_id = $input['expertB_id'];
            $dateX = $input['dateX'];
            $dateY = $input['dateY'];

            if (!isset($expertIndexMap[$eA_id]) || !isset($expertIndexMap[$eB_id])) {
                throw new Exception("یک یا هر دو کارشناس یافت نشدند.");
            }

            $nameA = $expertIndexMap[$eA_id]['name'];
            $nameB = $expertIndexMap[$eB_id]['name'];

            // پیدا کردن ایندکس در آرایه اصلی برای ویرایش
            $indexA = array_search($eA_id, array_column($masterData['experts'], 'id'));
            $indexB = array_search($eB_id, array_column($masterData['experts'], 'id'));

            $masterData['experts'][$indexA]['shifts'][$dateX] = [
                'status' => 'swap',
                'displayText' => "عدم حضور (جابجایی با {$nameB})",
                'linkedTo' => ['expertId' => (int) $eB_id, 'date' => $dateY]
            ];

            $masterData['experts'][$indexB]['shifts'][$dateY] = [
                'status' => 'swap',
                'displayText' => "حضور (جابجایی از {$nameA})",
                'linkedTo' => ['expertId' => (int) $eA_id, 'date' => $dateX]
            ];
            $message = 'جابجایی شیفت با موفقیت ثبت شد.';
            break;

        case 'revert_and_update':
            $expertId = $input['expertId'];
            $date = $input['date'];
            $newStatus = $input['newStatus'];
            $linkedExpertId = $input['linkedExpertId'];
            $linkedDate = $input['linkedDate'];

            if (!isset($expertIndexMap[$expertId]) || !isset($expertIndexMap[$linkedExpertId])) {
                throw new Exception("یک یا هر دو کارشناس درگیر در جابجایی یافت نشدند.");
            }

            $indexCurrent = array_search($expertId, array_column($masterData['experts'], 'id'));
            $indexLinked = array_search($linkedExpertId, array_column($masterData['experts'], 'id'));

            $masterData['experts'][$indexCurrent]['shifts'][$date] = $newStatus;
            $masterData['experts'][$indexLinked]['shifts'][$linkedDate] = 'off';

            $message = 'جابجایی لغو شد و وضعیت جدید ثبت گردید.';
            break;

        case 'update':
            $expertId = $input['expertId'];
            $date = $input['date'];
            $status = $input['status'];

            if (!isset($expertIndexMap[$expertId])) {
                throw new Exception("کارشناس یافت نشد.");
            }
            $index = array_search($expertId, array_column($masterData['experts'], 'id'));
            $masterData['experts'][$index]['shifts'][$date] = $status;
            $message = 'شیفت با موفقیت به‌روزرسانی شد.';
            break;

        default:
            throw new Exception('عملیات درخواستی نامشخص یا پشتیبانی نشده است.');
    }

    $jsonOutput = json_encode($masterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (file_put_contents($filePath, $jsonOutput, LOCK_EX) === false) {
        throw new Exception("امکان نوشتن در فایل {$filePath} وجود ندارد.");
    }

    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
}
?>
