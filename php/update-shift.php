<?php
// فایل حاوی کلید مخفی شما
require 'secret.php';
require __DIR__ . '/../php/auth_check.php';

// تنظیم هدر برای پاسخ JSON
header('Content-Type: application/json; charset=utf-8');

// =================================================================
// بخش ۱: توابع کمکی
// =================================================================

// توابع JWT شما (بدون تغییر)
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

function get_payload($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [, $payload, ] = $parts;
    return json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// تابع جدید برای ویرایش یک شیفت (برای مودال تعاملی)
function updateSingleShift(string $filePath, string $expertId, string $date, string $status): int {
    $fileContents = file_get_contents($filePath);
    if ($fileContents === false) throw new Exception("فایل شیفت‌ها خوانده نشد.");

    $masterData = json_decode($fileContents, true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("فرمت فایل شیفت‌ها (JSON) نامعتبر است.");

    $expertFound = false;
    foreach ($masterData['experts'] as &$expert) {
        // از == برای مقایسه استفاده می‌شود که اگر id عددی یا رشته‌ای باشد، مشکلی پیش نیاید
        if ($expert['id'] == $expertId) {
            $expert['shifts'][$date] = $status;
            $expertFound = true;
            break;
        }
    }

    if (!$expertFound) throw new Exception("کارشناس با شناسه {$expertId} یافت نشد.");

    $jsonOutput = json_encode($masterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $bytes = file_put_contents($filePath, $jsonOutput, LOCK_EX);
    if ($bytes === false) throw new Exception("امکان نوشتن در فایل {$filePath} وجود ندارد.");

    return $bytes;
}

// توابع به‌روزرسانی دسته‌ای شما (بدون تغییر)
function updateMasterJsonFile(string $filePath, array $newExpertsData): int {
    $masterData = ['experts' => []];
    $fileContents = file_get_contents($filePath);
    if ($fileContents !== false && !empty($fileContents)) {
        $decodedData = json_decode($fileContents, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['experts'])) {
            $masterData = $decodedData;
        }
    }
    $masterExpertsById = array_column($masterData['experts'], null, 'id');
    foreach ($newExpertsData as $id => $expert) {
        $masterExpertsById[$id] = $expert;
    }
    $masterData['experts'] = array_values($masterExpertsById);
    $jsonOutput = json_encode($masterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $bytes = file_put_contents($filePath, $jsonOutput, LOCK_EX);
    if ($bytes === false) throw new Exception("امکان نوشتن در فایل {$filePath} وجود ندارد.");
    return $bytes;
}

function parseScheduleText(string $text): array {
    $lines = explode("\n", trim($text));
    if (count($lines) < 2) throw new Exception("فرمت ورودی صحیح نیست.");

    $gregorianDateLine = trim($lines[1]);
    $gregorianDatesRaw = array_filter(explode("\t", $gregorianDateLine));
    $currentYear = date('Y');
    $formattedDates = [];
    foreach ($gregorianDatesRaw as $dateStr) {
        // این بخش ممکن است نیاز به تنظیم بر اساس فرمت دقیق کپی و پیست داشته باشد
        // با فرض فرمتی مانند "11-Aug"
        $dateObj = DateTime::createFromFormat('d-M', trim($dateStr));
        if ($dateObj) {
            $formattedDates[] = $dateObj->format($currentYear . '-m-d');
        }
    }

    $dataRows = array_slice($lines, 3);
    $expertsById = [];
    foreach ($dataRows as $rowStr) {
        if (empty(trim($rowStr))) continue;
        $cells = explode("\t", trim($rowStr));
        if (count($cells) < 4) continue;

        $id = trim($cells[0]);
        $shiftTime = trim($cells[1]);
        $name = trim($cells[2]);
        $breakTime = trim($cells[3]);
        $scheduleStatuses = array_slice($cells, 4);

        if (empty($id) || empty($name)) continue;

        $shifts = [];
        foreach ($scheduleStatuses as $index => $status) {
            if (isset($formattedDates[$index])) {
                $dateKey = $formattedDates[$index];
                $mappedStatus = trim($status);
                if ($mappedStatus === 'ON') $mappedStatus = 'on-duty';
                if ($mappedStatus === 'OFF') $mappedStatus = 'off';
                if (!empty($mappedStatus)) {
                    $shifts[$dateKey] = $mappedStatus;
                }
            }
        }
        $expertsById[$id] = ['id' => $id, 'name' => $name, 'shifts-time' => $shiftTime, 'break-time' => $breakTime, 'shifts' => $shifts];
    }
    return $expertsById;
}


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
// JWT_SECRET از فایل secret.php می‌آید
if (!verify_jwt($token, JWT_SECRET)) {
    http_response_code(403);
    echo json_encode(['message' => 'توکن نامعتبر یا منقضی شده است.']);
    exit;
}

// اختیاری اما بسیار مهم: بررسی نقش ادمین
$payload = get_payload($token);
$adminUsernames = ["abolfazl", "m.pourmosa", "m.samyari", "ehsan.jafari", "aida.akbari", "a.jamshidvand", "a.sadeghianmajd"];
// مسیر نام کاربری را مطابق با ساختار توکن خود تنظیم کنید
$username = $payload['data']['username'] ?? ($payload['username'] ?? '');
if (empty($username) || !in_array($username, $adminUsernames)) {
    http_response_code(403);
    echo json_encode(['message' => 'شما اجازه دسترسی به این عملیات را ندارید.']);
    exit;
}


// =================================================================
// بخش ۳: مسیریابی درخواست و اجرای عملیات
// =================================================================

// تعریف مسیر فایل اصلی
$filePath = $_SERVER['DOCUMENT_ROOT'] . '/data/shifts.json';
if (!file_exists(dirname($filePath))) {
    // ایجاد پوشه در صورت عدم وجود
    mkdir(dirname($filePath), 0775, true);
}


try {
    // حالت اول: به‌روزرسانی دسته‌ای از طریق متن کپی‌شده
    if (isset($_POST['schedule_data']) && !empty($_POST['schedule_data'])) {
        $rawText = $_POST['schedule_data'];
        $newExpertsData = parseScheduleText($rawText);
        $bytesWritten = updateMasterJsonFile($filePath, $newExpertsData);
        $count = count($newExpertsData);
        echo json_encode(['success' => true, 'message' => "موفقیت: اطلاعات {$count} کارشناس از طریق متن به‌روزرسانی شد."]);

    // حالت دوم: ویرایش تکی از طریق مودال تعاملی
    } else {
        $input = json_decode(file_get_contents('php://input'), true);

        // --- بلوک اصلاح شده ---
        // اگر JSON معتبر بود و تمام کلیدهای لازم وجود داشتند، ادامه بده
        if (json_last_error() === JSON_ERROR_NONE && isset($input['expertId'], $input['date'], $input['status'])) {
            updateSingleShift($filePath, $input['expertId'], $input['date'], $input['status']);
            echo json_encode(['success' => true, 'message' => 'شیفت با موفقیت به‌روزرسانی شد.']);
        } else {
            // در غیر این صورت، خطا بده
            throw new Exception('درخواست نامعتبر است. داده‌های ارسالی ناقص یا با فرمت اشتباه است.');
        }
        // --- پایان بلوک اصلاح شده ---
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
}
?>
