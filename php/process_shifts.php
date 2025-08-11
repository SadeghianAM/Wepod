<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

$filePath = __DIR__ . '/shifts.json';

// --- Main execution block ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'متد درخواست باید POST باشد.']);
    exit;
}

// Check for the 'clear' action first
if (isset($_POST['action']) && $_POST['action'] === 'clear') {
    try {
        clearMasterJsonFile($filePath);
        echo json_encode(['success' => true, 'message' => 'تمام اطلاعات با موفقیت پاک شد.']);
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
    updateMasterJsonFile($filePath, $newExpertsData);

    $count = count($newExpertsData);
    echo json_encode(['success' => true, 'message' => "عملیات موفقیت‌آمیز بود. اطلاعات {$count} کارشناس به‌روزرسانی شد."]);

} catch (Exception $e) {
    http_response_code(400); // Bad Request, as it's likely a data format issue
    echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
}

/**
 * Clears the master JSON file by writing an empty expert list to it.
 * @param string $filePath The path to the JSON file.
 * @throws Exception If file operations fail.
 */
function clearMasterJsonFile(string $filePath) {
    $emptyStructure = ['experts' => []];
    $jsonOutput = json_encode($emptyStructure, JSON_PRETTY_PRINT);

    // file_put_contents with LOCK_EX is a concise and safe way to write
    if (file_put_contents($filePath, $jsonOutput, LOCK_EX) === false) {
        throw new Exception("امکان نوشتن در فایل {$filePath} وجود ندارد.");
    }
}

/**
 * Parses the raw tab-separated schedule text into a structured array.
 * @param string $text The raw text input.
 * @return array An associative array of experts keyed by their ID.
 * @throws Exception If the input format is invalid.
 */
function parseScheduleText(string $text): array {
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
        if (empty(trim($rowStr))) continue;

        $cells = explode("\t", trim($rowStr));
        if (count($cells) < 4) continue; // Skip malformed rows

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

/**
 * Reads the master JSON file, updates it with new data, and writes it back.
 * @param string $filePath The path to the JSON file.
 * @param array $newExpertsData An associative array of new expert data keyed by ID.
 * @throws Exception If file operations fail.
 */
function updateMasterJsonFile(string $filePath, array $newExpertsData) {
    $masterData = ['experts' => []];

    // File locking to prevent race conditions
    $fileHandle = fopen($filePath, 'c+');
    if (!$fileHandle) {
        throw new Exception("امکان باز کردن فایل {$filePath} وجود ندارد.");
    }

    if (!flock($fileHandle, LOCK_EX)) {
        fclose($fileHandle);
        throw new Exception("امکان قفل کردن فایل {$filePath} وجود ندارد.");
    }

    $fileSize = filesize($filePath);
    if ($fileSize > 0) {
        $fileContents = fread($fileHandle, $fileSize);
        if (!empty($fileContents)) {
            $decodedData = json_decode($fileContents, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['experts'])) {
                $masterData = $decodedData;
            }
        }
    }

    $masterExpertsById = array_column($masterData['experts'], null, 'id');

    foreach ($newExpertsData as $id => $expert) {
        $masterExpertsById[$id] = $expert;
    }

    $masterData['experts'] = array_values($masterExpertsById);
    $jsonOutput = json_encode($masterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    ftruncate($fileHandle, 0);
    rewind($fileHandle);
    fwrite($fileHandle, $jsonOutput);

    flock($fileHandle, LOCK_UN);
    fclose($fileHandle);
}
?>
