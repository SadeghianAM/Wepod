<?php
// Set headers to return JSON
header('Content-Type: application/json; charset=utf-8');

// --- Main execution block ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'متد درخواست باید POST باشد.']);
    exit;
}

if (empty($_POST['schedule_data'])) {
    echo json_encode(['success' => false, 'message' => 'هیچ اطلاعاتی برای پردازش ارسال نشده است.']);
    exit;
}

try {
    $rawText = $_POST['schedule_data'];
    $newExpertsData = parseScheduleText($rawText);
    updateMasterJsonFile($newExpertsData);

    $count = count($newExpertsData);
    echo json_encode(['success' => true, 'message' => "عملیات موفقیت‌آمیز بود. اطلاعات {$count} کارشناس به‌روزرسانی شد."]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
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

        // **UPDATED LOGIC HERE**
        $id = trim($cells[0]);
        $shiftTime = trim($cells[1]);
        $name = trim($cells[2]);
        $breakTime = trim($cells[3]); // <-- New field for break time
        $scheduleStatuses = array_slice($cells, 4); // <-- Start slicing from index 4

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
            'break-time' => $breakTime, // <-- Storing the new field
            'shifts' => $shifts
        ];
    }
    return $expertsById;
}

/**
 * Reads the master JSON file, updates it with new data, and writes it back.
 * @param array $newExpertsData An associative array of new expert data keyed by ID.
 * @throws Exception If file operations fail.
 */
function updateMasterJsonFile(array $newExpertsData) {
    $filePath = 'shifts.json';
    $masterData = ['experts' => []];

    // File locking to prevent race conditions
    $fileHandle = fopen($filePath, 'c+');
    if (!$fileHandle) {
        throw new Exception("امکان باز کردن فایل {$filePath} وجود ندارد.");
    }

    // Lock file for exclusive writing
    if (!flock($fileHandle, LOCK_EX)) {
        fclose($fileHandle);
        throw new Exception("امکان قفل کردن فایل {$filePath} وجود ندارد.");
    }

    $fileContents = fread($fileHandle, filesize($filePath) ?: 1);
    if (!empty($fileContents)) {
        $decodedData = json_decode($fileContents, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['experts'])) {
            $masterData = $decodedData;
        }
    }

    // Create an associative array of master experts by ID for efficient updates
    $masterExpertsById = array_column($masterData['experts'], null, 'id');

    // Update or insert new data
    foreach ($newExpertsData as $id => $expert) {
        $masterExpertsById[$id] = $expert;
    }

    // Convert back to a zero-indexed array for proper JSON array format
    $masterData['experts'] = array_values($masterExpertsById);

    // Prepare JSON for writing
    $jsonOutput = json_encode($masterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Write updated content back to the file
    ftruncate($fileHandle, 0); // Clear the file
    rewind($fileHandle);       // Move pointer to the beginning
    fwrite($fileHandle, $jsonOutput);

    // Release lock and close
    flock($fileHandle, LOCK_UN);
    fclose($fileHandle);
}
?>
