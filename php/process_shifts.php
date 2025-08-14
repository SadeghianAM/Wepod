<?php

require __DIR__ . '/../php/auth_check.php';

// Set headers to return JSON and prevent caching
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// --- CORRECT FILE PATH DEFINITION ---
// Use the server's DOCUMENT_ROOT to build a reliable, absolute path from the web root.
// This is the most robust way to define the path.
if (!isset($_SERVER['DOCUMENT_ROOT']) || empty($_SERVER['DOCUMENT_ROOT'])) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'خطای سرور: متغیر DOCUMENT_ROOT تعریف نشده است. لطفاً تنظیمات سرور را بررسی کنید.']);
    exit;
}
$filePath = $_SERVER['DOCUMENT_ROOT'] . '/data/shifts.json';
// --- END FILE PATH DEFINITION ---


// --- DIAGNOSTICS BLOCK ---
// Clear PHP's file status cache to get the most up-to-date information
clearstatcache();

// This block runs first to check file permissions.
if (file_exists($filePath)) {
    if (!is_writable($filePath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => "خطای دسترسی: فایل shifts.json وجود دارد اما قابل نوشتن نیست. مسیر بررسی شده: " . $filePath]);
        exit;
    }
} else {
    // If the file does not exist, check if its directory is writable.
    $dirName = dirname($filePath);
    if (!is_dir($dirName)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => "خطای مسیر: پوشه '{$dirName}' وجود ندارد. لطفاً مطمئن شوید پوشه data در ریشه وب‌سایت شما قرار دارد."]);
        exit;
    }
    if (!is_writable($dirName)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => "خطای دسترسی: پوشه '{$dirName}' قابل نوشتن نیست و نمی‌توان فایل shifts.json را ایجاد کرد."]);
        exit;
    }
}
// --- END DIAGNOSTICS BLOCK ---


// --- Main execution block ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'متد درخواست باید POST باشد.']);
    exit;
}

// Check for the 'clear' action first
if (isset($_POST['action']) && $_POST['action'] === 'clear') {
    try {
        $bytesWritten = clearMasterJsonFile($filePath);
        echo json_encode(['success' => true, 'message' => "موفقیت: {$bytesWritten} بایت در فایل نوشته شد. مسیر فایل: " . $filePath]);
    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'خطا در پاک کردن فایل: ' . $e->getMessage()]);
    }
    exit;
}

// If not clearing, proceed with the update logic
if (empty($_POST['schedule_data'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'هیچ اطلاعاتی برای پردازش ارسال نشده است.']);
    exit;
}

try {
    $rawText = $_POST['schedule_data'];
    $newExpertsData = parseScheduleText($rawText);
    $bytesWritten = updateMasterJsonFile($filePath, $newExpertsData);
    $count = count($newExpertsData);

    echo json_encode(['success' => true, 'message' => "موفقیت: اطلاعات {$count} کارشناس به‌روزرسانی شد ({$bytesWritten} بایت). مسیر فایل: " . $filePath]);

} catch (Exception $e) {
    http_response_code(400); // Bad Request, as it's likely a data format issue
    echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
}

/**
 * Clears the master JSON file and returns the number of bytes written.
 * @param string $filePath The path to the JSON file.
 * @return int The number of bytes written to the file.
 * @throws Exception If file operations fail.
 */
function clearMasterJsonFile(string $filePath): int
{
    $emptyStructure = ['experts' => []];
    $jsonOutput = json_encode($emptyStructure, JSON_PRETTY_PRINT);

    $bytes = file_put_contents($filePath, $jsonOutput, LOCK_EX);
    if ($bytes === false) {
        throw new Exception("امکان نوشتن در فایل {$filePath} وجود ندارد.");
    }
    return $bytes;
}

/**
 * Reads, updates, and writes back to the master JSON file. Returns bytes written.
 * @param string $filePath The path to the JSON file.
 * @param array $newExpertsData An associative array of new expert data keyed by ID.
 * @return int The number of bytes written to the file.
 * @throws Exception If file operations fail.
 */
function updateMasterJsonFile(string $filePath, array $newExpertsData): int
{
    $masterData = ['experts' => []];

    // Read existing data if the file is not empty
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
    if ($bytes === false) {
        throw new Exception("امکان نوشتن در فایل {$filePath} وجود ندارد.");
    }
    return $bytes;
}


/**
 * Parses the raw tab-separated schedule text into a structured array.
 * @param string $text The raw text input.
 * @return array An associative array of experts keyed by their ID.
 * @throws Exception If the input format is invalid.
 */
function parseScheduleText(string $text): array
{
    $lines = explode("\n", trim($text));
    if (count($lines) < 4) {
        throw new Exception("فرمت ورودی صحیح نیست. حداقل به 4 خط اطلاعات نیاز است.");
    }

    // 1. Extract and format dates
    $gregorianDateLine = trim($lines[1]);
    $gregorianDatesRaw = array_filter(explode("\t", $gregorianDateLine));
    $currentYear = date('Y');
    $formattedDates = [];
    foreach ($gregorianDatesRaw as $dateStr) {
        $dateObj = DateTime::createFromFormat('d-M-Y', $dateStr . '-' . $currentYear);
        if (!$dateObj) {
            throw new Exception("فرمت تاریخ '{$dateStr}' نامعتبر است.");
        }
        $formattedDates[] = $dateObj->format('Y-m-d');
    }

    // 2. Process data rows
    $dataRows = array_slice($lines, 3);
    $expertsById = [];

    foreach ($dataRows as $rowStr) {
        if (empty(trim($rowStr)))
            continue;

        $cells = explode("\t", trim($rowStr));
        if (count($cells) < 4)
            continue; // Skip malformed rows

        $id = trim($cells[0]);
        $shiftTime = trim($cells[1]);
        $name = trim($cells[2]);
        $breakTime = trim($cells[3]);
        $scheduleStatuses = array_slice($cells, 4);

        if (empty($id) || empty($name))
            continue;

        $shifts = [];
        foreach ($scheduleStatuses as $index => $status) {
            if (isset($formattedDates[$index])) {
                $dateKey = $formattedDates[$index];
                $mappedStatus = trim($status);
                if ($mappedStatus === 'ON')
                    $mappedStatus = 'on-duty';
                if ($mappedStatus === 'OFF')
                    $mappedStatus = 'off';
                $shifts[$dateKey] = $mappedStatus;
            }
        }

        $expertsById[$id] = [
            'id' => $id,
            'name' => $name,
            'shifts-time' => $shiftTime,
            'break-time' => $breakTime,
            'shifts' => $shifts
        ];
    }
    return $expertsById;
}
?>
