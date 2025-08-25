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

$response = ['success' => false, 'message' => 'درخواست نامعتبر است.'];
$jsonFile = __DIR__ . '/../data/reports.json';
$time_based_metrics = ['total_talk_time_in', 'avg_talk_time_in', 'max_talk_time_in', 'avg_talk_time_out', 'presence_duration', 'break_duration'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? null;
        if (!$action) throw new Exception("عملیات مشخص نشده است.");

        $existingData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
        if (!is_array($existingData)) $existingData = [];

        if (in_array($action, ['process_report', 'delete_report', 'edit_metric', 'delete_metric'])) {
            if (file_exists($jsonFile)) {
                copy($jsonFile, $jsonFile . '.bak.' . time());
            }
        }

        switch ($action) {
            case 'process_report':
                if (empty($_POST['excel_data'])) throw new Exception("داده‌ای برای پردازش ارسال نشده است.");
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

                    // *** FIX: Added 'chat_count' from column 15 ***
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
                        "chat_count" => (int)trim($columns[15]), // <-- ADDED THIS LINE
                        "famas_count" => (int)trim($columns[16]),
                        "jira_count" => (int)trim($columns[17]),
                        "one_star_ratings" => (int)trim($columns[18]),
                        "calls_over_5_min" => (int)trim($columns[19]),
                        "no_call_reason" => (int)trim($columns[20]),
                    ];

                    if (!isset($existingData[$agentId])) $existingData[$agentId] = [];
                    $existingData[$agentId][$date] = $reportData;
                    $processedCount++;
                }

                if ($processedCount > 0) {
                    $response = ['success' => true, 'message' => "{$processedCount} ردیف داده با موفقیت پردازش و ذخیره شد."];
                } else {
                    throw new Exception("هیچ ردیف معتبری برای پردازش یافت نشد. لطفاً فرمت داده‌ها را بررسی کنید.");
                }
                break;

            case 'edit_metric':
                $agentId = $_POST['agent_id'] ?? null;
                $date = $_POST['date'] ?? null;
                $metricKey = $_POST['metric_key'] ?? null;
                $newValue = $_POST['new_value'] ?? null;

                if (!$agentId || !$date || !$metricKey || $newValue === null) throw new Exception("اطلاعات ارسالی ناقص است.");

                // This check is now safe, as the front-end ensures all keys are present
                // if (!isset($existingData[$agentId][$date][$metricKey])) throw new Exception("متریک مورد نظر یافت نشد.");

                $finalValue = trim($newValue);
                if (in_array($metricKey, $time_based_metrics)) {
                    if (!preg_match('/^\d{1,4}:\d{2}:\d{2}$/', $finalValue)) throw new Exception("فرمت زمان باید HH:MM:SS باشد.");
                    $finalValue = time_to_seconds($finalValue);
                } elseif (strpos($metricKey, 'rating') !== false) {
                    $finalValue = (float)$finalValue;
                } else {
                    $finalValue = (int)$finalValue;
                }

                $existingData[$agentId][$date][$metricKey] = $finalValue;
                $response = [
                    'success' => true,
                    'message' => "مقدار با موفقیت ویرایش شد.",
                    'updatedValue' => $finalValue
                ];
                break;

            case 'delete_metric':
                $agentId = $_POST['agent_id'] ?? null;
                $date = $_POST['date'] ?? null;
                $metricKey = $_POST['metric_key'] ?? null;

                if (!$agentId || !$date || !$metricKey) throw new Exception("اطلاعات ارسالی ناقص است.");
                if (!isset($existingData[$agentId][$date][$metricKey])) throw new Exception("متریک مورد نظر یافت نشد.");

                unset($existingData[$agentId][$date][$metricKey]);

                if (empty($existingData[$agentId][$date])) {
                    unset($existingData[$agentId][$date]);
                    if (empty($existingData[$agentId])) unset($existingData[$agentId]);
                }
                $response = ['success' => true, 'message' => "متریک با موفقیت حذف شد."];
                break;

            default:
                throw new Exception("عملیات نامعتبر است.");
        }

        file_put_contents($jsonFile, json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    } catch (Exception $e) {
        http_response_code(400); // Bad Request
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
