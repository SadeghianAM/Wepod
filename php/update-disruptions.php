<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
header('Content-Type: application/json; charset=utf-8');

$dataFile = __DIR__ . '/../data/disruptions.json';

// --- Security Layer 1: Reject Blatantly Malicious Requests ---

function containsMaliciousPatterns($input)
{
    if (!is_string($input)) return false;
    $patterns = [
        '/<script/i',
        '/onerror\s*=/i',
        '/onload\s*=/i',
        '/javascript\s*:/i',
        '/<iframe/i',
        '/<svg/i',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    return false;
}

// Check every value in the POST request for malicious patterns
foreach ($_POST as $key => $value) {
    if (containsMaliciousPatterns($value)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Input contains forbidden content and was rejected.']);
        exit;
    }
}

// --- Security Layer 2: Sanitize and Validate All Inputs ---

function get_records($file)
{
    if (!file_exists($file)) file_put_contents($file, '[]');
    return json_decode(file_get_contents($file), true);
}

function save_records($file, $data)
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($file, $json);
}

// Whitelists for validation
$allowedDays = ["شنبه", "یکشنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه", "پنجشنبه", "جمعه"];
$allowedStatuses = ["باز", "درحال رسیدگی", "برطرف شده"];
$allowedTeams = ["اعلام از سمت فنی", "اعلام از سمت مرکز تماس"];
$allowedSubjects = [
    "اختلال در اپلیکیشن",
    "اختلال در ارتباط با واحد بازاریابی",
    "اختلال در ارتقاء/تغییر سطح",
    "اختلال در اعتبار سنجی",
    "اختلال در انتقال وجه",
    "اختلال در بانکداری ویدئویی",
    "اختلال در بیمه پاسارگاد",
    "اختلال در پنل CRM",
    "اختلال در تمامی تسهیلات",
    "اختلال در تسهیلات برآیند",
    "اختلال در تسهیلات پشتوانه",
    "اختلال در تسهیلات پیش درآمد",
    "اختلال در تسهیلات سازمانی",
    "اختلال در تسهیلات کاپ کارت",
    "اختلال در تسویه کارت خوان ها",
    "اختلال در تسویه معوقات",
    "اختلال در تغییر شماره تلفن همراه",
    "اختلال در تنظیمات امنیت حساب",
    "اختلال در چک",
    "اختلال چکاد",
    "اختلال در خدمات قبض",
    "اختلال در دریافت پیامک",
    "اختلال در دعوت از دوستان",
    "اختلال در سرویس درگاه پاد",
    "اختلال در سرویس مالی پاد",
    "کندی و قطعی پنل پاد",
    "اختلال در سرویس ثبت احوال",
    "اختلال در سرویس سمات",
    "اختلال در سرویس سیاح",
    "اختلال در سرویس شاهکار",
    "اختلال در سرویس شرکت ملی پست ایران",
    "اختلال در صندوق های سرمایه گذاری",
    "اختلال در طرح سرمایه گذاری جوانه",
    "اختلال در طرح سرمایه گذاری رویش",
    "اختلال در طرح کاوه",
    "اختلال در کارت فیزیکی",
    "اختلال در کارت و حساب دیجیتال",
    "اختلال در کارت و اعتبار هدیه دیجیتال",
    "اختلال در مسدودی و رفع مسدودی حساب",
    "اختلال در وی کلاب",
    "اختلال دستگاه پوز",
    "اختلال رمز دو عاملی",
    "اختلال کد شهاب",
    "مشکلات شعب",
    "سایر اختلالات",
    "اختلال در خرید شارژ و اینترنت",
    "اختلال ورود به برنامه",
    "اختلال در تسهیلات پیمان",
    "افزایش موجودی",
    "اختلال افتتاح حساب جاری"
];

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode(get_records($dataFile));
} elseif ($method === 'POST') {
    $records = get_records($dataFile);
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        $id_to_delete = $_POST['id'] ?? null;
        if ($id_to_delete) {
            $records = array_values(array_filter($records, fn($rec) => $rec['id'] !== $id_to_delete));
            save_records($dataFile, $records);
            echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully.']);
        }
    } else { // 'save' action (Create/Update)
        // Sanitize all inputs before using them
        $sanitizedData = [];
        $sanitizedData['id'] = isset($_POST['id']) ? htmlspecialchars($_POST['id'], ENT_QUOTES, 'UTF-8') : null;
        $sanitizedData['dayOfWeek'] = in_array($_POST['dayOfWeek'], $allowedDays) ? $_POST['dayOfWeek'] : null;
        $sanitizedData['subject'] = in_array($_POST['subject'], $allowedSubjects) ? $_POST['subject'] : null;
        $sanitizedData['status'] = in_array($_POST['status'], $allowedStatuses) ? $_POST['status'] : 'باز';
        $sanitizedData['reportingTeam'] = in_array($_POST['reportingTeam'], $allowedTeams) ? $_POST['reportingTeam'] : null;
        $sanitizedData['description'] = isset($_POST['description']) ? htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8') : '';
        $sanitizedData['totalDuration'] = isset($_POST['totalDuration']) ? htmlspecialchars($_POST['totalDuration'], ENT_QUOTES, 'UTF-8') : '';

        // Validate date and time formats
        $sanitizedData['startDate'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['startDate']) ? $_POST['startDate'] : null;
        $sanitizedData['startTime'] = preg_match('/^\d{2}:\d{2}$/', $_POST['startTime']) ? $_POST['startTime'] : null;
        $sanitizedData['endDate'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['endDate']) ? $_POST['endDate'] : null;
        $sanitizedData['endTime'] = preg_match('/^\d{2}:\d{2}$/', $_POST['endTime']) ? $_POST['endTime'] : null;

        if (empty($sanitizedData['subject']) || empty($sanitizedData['dayOfWeek'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Required fields are missing or invalid.']);
            exit;
        }

        if ($sanitizedData['id']) { // Update
            $found = false;
            foreach ($records as $key => $record) {
                if ($record['id'] === $sanitizedData['id']) {
                    $records[$key] = $sanitizedData;
                    $found = true;
                    break;
                }
            }
            if ($found) {
                save_records($dataFile, $records);
                echo json_encode(['status' => 'success', 'message' => 'Record updated successfully.']);
            }
        } else { // Create
            $sanitizedData['id'] = 'rec_' . uniqid();
            $records[] = $sanitizedData;
            save_records($dataFile, $records);
            echo json_encode(['status' => 'success', 'message' => 'New record created successfully.', 'data' => $sanitizedData]);
        }
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
}
