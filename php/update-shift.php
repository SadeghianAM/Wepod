<?php
// ✅ احراز هویت ادمین با میان‌افزار جدید
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');

header('Content-Type: application/json; charset=utf-8');

// مسیر فایل داده‌ها
$filePath = $_SERVER['DOCUMENT_ROOT'] . '/data/shifts.json';
if (!file_exists(dirname($filePath))) {
    mkdir(dirname($filePath), 0775, true);
}

try {
    // دریافت ورودی JSON
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('درخواست نامعتبر است. فرمت JSON اشتباه است.');
    }

    $action = $input['action'] ?? null;

    // خواندن فایل اصلی
    $fileContents = @file_get_contents($filePath);
    $masterData = $fileContents ? json_decode($fileContents, true) : ['experts' => []];
    if (!is_array($masterData)) $masterData = ['experts' => []];
    if (!isset($masterData['experts']) || !is_array($masterData['experts'])) $masterData['experts'] = [];

    // نقشه‌ی سریع برای دسترسی با id
    $expertIndexMap = array_column($masterData['experts'], null, 'id');

    switch ($action) {
        case 'modify_swap':
            $eA_id = $input['expertA_id'];
            $dateX = $input['dateX'];
            $new_eB_id = $input['newExpertB_id'];
            $new_dateY = $input['newDateY'];
            $old_eB_id = $input['oldLinkedExpertId'];
            $old_dateY = $input['oldLinkedDate'];

            if (!isset($expertIndexMap[$eA_id]) || !isset($expertIndexMap[$new_eB_id]) || !isset($expertIndexMap[$old_eB_id])) {
                throw new Exception("یک یا چند نفر از کارشناسان درگیر در عملیات یافت نشدند.");
            }

            $indexA     = array_search($eA_id,     array_column($masterData['experts'], 'id'));
            $index_newB = array_search($new_eB_id, array_column($masterData['experts'], 'id'));
            $index_oldB = array_search($old_eB_id, array_column($masterData['experts'], 'id'));

            // بازگردانی همکار قبلی
            $masterData['experts'][$index_oldB]['shifts'][$old_dateY] = 'off';

            // ایجاد جابجایی جدید
            $nameA     = $expertIndexMap[$eA_id]['name'];
            $name_newB = $expertIndexMap[$new_eB_id]['name'];

            // کارشناس A
            $masterData['experts'][$indexA]['shifts'][$dateX] = [
                'status'     => 'swap',
                'displayText' => "عدم حضور (جابجایی با {$name_newB})",
                'linkedTo'   => ['expertId' => (int)$new_eB_id, 'date' => $new_dateY]
            ];

            // کارشناس جدید B
            $masterData['experts'][$index_newB]['shifts'][$new_dateY] = [
                'status'     => 'swap',
                'displayText' => "حضور (جابجایی از {$nameA})",
                'linkedTo'   => ['expertId' => (int)$eA_id, 'date' => $dateX]
            ];

            $message = 'جابجایی شیفت با موفقیت تغییر یافت.';
            break;

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

            $indexA = array_search($eA_id, array_column($masterData['experts'], 'id'));
            $indexB = array_search($eB_id, array_column($masterData['experts'], 'id'));

            $masterData['experts'][$indexA]['shifts'][$dateX] = [
                'status'     => 'swap',
                'displayText' => "عدم حضور (جابجایی با {$nameB})",
                'linkedTo'   => ['expertId' => (int)$eB_id, 'date' => $dateY]
            ];

            $masterData['experts'][$indexB]['shifts'][$dateY] = [
                'status'     => 'swap',
                'displayText' => "حضور (جابجایی از {$nameA})",
                'linkedTo'   => ['expertId' => (int)$eA_id, 'date' => $dateX]
            ];

            $message = 'جابجایی شیفت با موفقیت ثبت شد.';
            break;

        case 'revert_and_update':
            $expertId       = $input['expertId'];
            $date           = $input['date'];
            $newStatus      = $input['newStatus'];
            $linkedExpertId = $input['linkedExpertId'];
            $linkedDate     = $input['linkedDate'];

            if (!isset($expertIndexMap[$expertId]) || !isset($expertIndexMap[$linkedExpertId])) {
                throw new Exception("یک یا هر دو کارشناس درگیر در جابجایی یافت نشدند.");
            }

            $indexCurrent = array_search($expertId,       array_column($masterData['experts'], 'id'));
            $indexLinked  = array_search($linkedExpertId, array_column($masterData['experts'], 'id'));

            $masterData['experts'][$indexCurrent]['shifts'][$date]       = $newStatus;
            $masterData['experts'][$indexLinked]['shifts'][$linkedDate]  = 'off';

            $message = 'جابجایی لغو شد و وضعیت جدید ثبت گردید.';
            break;

        case 'update':
            $expertId = $input['expertId'];
            $date     = $input['date'];
            $status   = $input['status'];

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

    // ذخیره فایل
    $jsonOutput = json_encode($masterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (file_put_contents($filePath, $jsonOutput, LOCK_EX) === false) {
        throw new Exception("امکان نوشتن در فایل {$filePath} وجود ندارد.");
    }

    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
}
