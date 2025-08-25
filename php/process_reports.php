<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');

header('Content-Type: application/json; charset=utf-8');

/**
 * Converts a Jalali (Shamsi) date to a Gregorian date.
 */
function jalali_to_gregorian($jy, $jm, $jd)
{
    $g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
    $jy -= 979;
    $jm -= 1;
    $jd -= 1;
    $j_day_no = 365 * $jy + intval($jy / 33) * 8 + intval((($jy % 33) + 3) / 4);
    for ($i = 0; $i < $jm; ++$i) $j_day_no += $j_days_in_month[$i];
    $j_day_no += $jd;
    $g_day_no = $j_day_no + 79;
    $gy = 1600 + 400 * intval($g_day_no / 146097);
    $g_day_no = $g_day_no % 146097;
    $leap = true;
    if ($g_day_no >= 36525) {
        $g_day_no--;
        $gy += 100 * intval($g_day_no / 36524);
        $g_day_no = $g_day_no % 36524;
        if ($g_day_no >= 365) $g_day_no++;
        else $leap = false;
    }
    $gy += 4 * intval($g_day_no / 1461);
    $g_day_no %= 1461;
    if ($g_day_no >= 366) {
        $leap = false;
        $g_day_no--;
        $gy += intval($g_day_no / 365);
        $g_day_no = $g_day_no % 365;
    }
    for ($i = 0; $g_day_no >= $g_days_in_month[$i] + ($i == 1 && $leap); $i++) $g_day_no -= $g_days_in_month[$i] + ($i == 1 && $leap);
    $gm = $i + 1;
    $gd = $g_day_no + 1;
    return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
}

/**
 * Converts a time string like HH:MM:SS to total seconds.
 */
function time_to_seconds($time_str)
{
    $parts = explode(':', $time_str);
    if (count($parts) === 3) {
        return (int)$parts[0] * 3600 + (int)$parts[1] * 60 + (int)$parts[2];
    }
    return 0;
}

$response = [
    'success' => false,
    'message' => 'درخواست نامعتبر است.'
];
$jsonFile = __DIR__ . '/../data/reports.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? 'process_report';
        $existingData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
        if (!is_array($existingData)) $existingData = [];

        // --- Handle Delete Action ---
        if ($action === 'delete_report') {
            $agentId = $_POST['agent_id'] ?? null;
            $date = $_POST['date'] ?? null;

            if (!$agentId || !$date) throw new Exception("کد کارشناس و تاریخ برای حذف الزامی است.");
            if (!isset($existingData[$agentId][$date])) throw new Exception("هیچ رکوردی برای این کارشناس در تاریخ مشخص شده یافت نشد.");

            if (file_exists($jsonFile)) copy($jsonFile, $jsonFile . '.bak.' . time());

            unset($existingData[$agentId][$date]);

            if (empty($existingData[$agentId])) {
                unset($existingData[$agentId]);
            }

            file_put_contents($jsonFile, json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $response = ['success' => true, 'message' => "رکورد با موفقیت حذف شد."];
        }
        // --- Process New Unified Report ---
        elseif ($action === 'process_report' && !empty($_POST['excel_data'])) {
            $pastedData = trim($_POST['excel_data']);
            $lines = explode("\n", $pastedData);
            $processedCount = 0;
            $MIN_COLUMNS = 21;

            foreach ($lines as $line) {
                $trimmedLine = trim($line);
                if (empty($trimmedLine)) continue;

                $columns = preg_split('/\t+/', $trimmedLine);
                if (count($columns) < $MIN_COLUMNS) continue;

                $agentId = trim($columns[0]);
                if (!is_numeric($agentId)) continue;

                $shamsi_date_parts = explode('/', trim($columns[2]));
                if (count($shamsi_date_parts) != 3) continue;
                $date = jalali_to_gregorian($shamsi_date_parts[0], $shamsi_date_parts[1], $shamsi_date_parts[2]);

                $reportData = [
                    "incoming_calls" => (int)trim($columns[3]),
                    "total_talk_time_in" => time_to_seconds(trim($columns[4])),
                    "avg_talk_time_in" => time_to_seconds(trim($columns[5])),
                    "max_talk_time_in" => time_to_seconds(trim($columns[6])),
                    "ratings_count" => (int)trim($columns[7]),
                    "avg_rating" => (float)trim($columns[8]),
                    "presence_duration" => time_to_seconds(trim($columns[9])),
                    "break_duration" => time_to_seconds(trim($columns[10])),
                    "missed_calls" => (int)trim($columns[11]),
                    "outbound_calls" => (int)trim($columns[12]),
                    "avg_talk_time_out" => time_to_seconds(trim($columns[13])),
                    "tickets_count" => (int)trim($columns[14]),
                    "chat_count" => (int)trim($columns[15]),
                    "famas_count" => (int)trim($columns[16]),
                    "jira_count" => (int)trim($columns[17]),
                    "one_star_ratings" => (int)trim($columns[18]),
                    "calls_over_5_min" => (int)trim($columns[19]),
                    "no_call_reason" => (int)trim($columns[20]),
                ];

                if (!isset($existingData[$agentId])) $existingData[$agentId] = [];
                // Overwrite existing data for that day with the new unified record
                $existingData[$agentId][$date] = $reportData;
                $processedCount++;
            }

            if ($processedCount > 0) {
                if (file_exists($jsonFile)) {
                    copy($jsonFile, $jsonFile . '.bak.' . time());
                }
                file_put_contents($jsonFile, json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $response['success'] = true;
                $response['message'] = "{$processedCount} ردیف داده با موفقیت پردازش و ذخیره شد.";
            } else {
                $response['message'] = "هیچ ردیف معتبری برای پردازش یافت نشد. لطفاً فرمت داده‌ها را بررسی کنید.";
            }
        }
    } catch (Exception $e) {
        http_response_code(500);
        $response['message'] = "خطای داخلی سرور: " . $e->getMessage();
    }
}

echo json_encode($response);
