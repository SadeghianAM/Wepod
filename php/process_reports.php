<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');

header('Content-Type: application/json; charset=utf-8');

/**
 * Converts a Jalali (Shamsi) date to a Gregorian date.
 *
 * @param int $jy Jalali Year
 * @param int $jm Jalali Month
 * @param int $jd Jalali Day
 * @return string Gregorian date in YYYY-MM-DD format.
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

$response = [
    'success' => false,
    'message' => 'درخواست نامعتبر است یا نوع گزارش انتخاب نشده.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['report_type']) && !empty($_POST['excel_data'])) {
    try {
        $reportType = $_POST['report_type'];
        $pastedData = trim($_POST['excel_data']);
        $lines = explode("\n", $pastedData);
        $jsonFile = __DIR__ . '/../data/reports.json';
        $existingData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
        if (!is_array($existingData)) $existingData = [];

        $processedCount = 0;

        switch ($reportType) {
            case 'call_metrics':
                foreach ($lines as $line) {
                    $trimmedLine = trim($line);
                    if (empty($trimmedLine)) continue;
                    $columns = preg_split('/\t+/', $trimmedLine);
                    if (!is_numeric(trim($columns[0]))) continue;
                    if (count($columns) < 9) continue;

                    $agentId = trim($columns[0]);
                    $shamsi_date_parts = explode('/', trim($columns[2]));
                    if (count($shamsi_date_parts) != 3) continue;
                    $date = jalali_to_gregorian($shamsi_date_parts[0], $shamsi_date_parts[1], $shamsi_date_parts[2]);

                    $reportData = [
                        "answered_calls"  => (int)str_replace(',', '', $columns[3]),
                        "total_talk_time" => (int)str_replace(',', '', $columns[4]),
                        "avg_talk_time"   => (int)str_replace(',', '', $columns[5]),
                        "max_talk_time"   => (int)str_replace(',', '', $columns[6]),
                        "avg_rating"      => (float)$columns[7],
                        "ratings_count"   => (int)str_replace(',', '', $columns[8])
                    ];

                    if (!isset($existingData[$agentId][$date])) $existingData[$agentId][$date] = [];
                    $existingData[$agentId][$date] = array_merge($existingData[$agentId][$date], $reportData);
                    $processedCount++;
                }
                break;

            case 'presence_duration':
                foreach ($lines as $line) {
                    if (empty(trim($line))) continue;
                    $columns = explode("\t", trim($line));
                    if (!is_numeric(trim($columns[0]))) continue;
                    if (count($columns) < 4) continue;

                    $agentId = trim($columns[0]);
                    $shamsi_date_parts = explode('/', trim($columns[2]));
                    if (count($shamsi_date_parts) != 3) continue;
                    $date = jalali_to_gregorian($shamsi_date_parts[0], $shamsi_date_parts[1], $shamsi_date_parts[2]);

                    $presenceDuration = (int)str_replace(',', '', $columns[3]);

                    if (!isset($existingData[$agentId])) $existingData[$agentId] = [];
                    if (!isset($existingData[$agentId][$date])) $existingData[$agentId][$date] = [];
                    $existingData[$agentId][$date]['presence_duration'] = $presenceDuration;
                    $processedCount++;
                }
                break;

            case 'off_queue_duration':
                foreach ($lines as $line) {
                    if (empty(trim($line))) continue;
                    $columns = explode("\t", trim($line));
                    if (!is_numeric(trim($columns[0]))) continue;
                    if (count($columns) < 4) continue;

                    $agentId = trim($columns[0]);
                    $shamsi_date_parts = explode('/', trim($columns[2]));
                    if (count($shamsi_date_parts) != 3) continue;
                    $date = jalali_to_gregorian($shamsi_date_parts[0], $shamsi_date_parts[1], $shamsi_date_parts[2]);

                    $offQueueDuration = (int)str_replace(',', '', $columns[3]);

                    if (!isset($existingData[$agentId])) $existingData[$agentId] = [];
                    if (!isset($existingData[$agentId][$date])) $existingData[$agentId][$date] = [];
                    $existingData[$agentId][$date]['off_queue_duration'] = $offQueueDuration;
                    $processedCount++;
                }
                break;

            case 'one_star_ratings':
            case 'calls_over_5_min':
            case 'missed_calls':
            case 'outbound_calls':
                $dailyCounts = [];
                $countingConfigs = [
                    'one_star_ratings' => ['key' => 'one_star_ratings', 'date_col' => 0, 'id_col' => 1],
                    'calls_over_5_min' => ['key' => 'calls_over_5_min', 'date_col' => 0, 'id_col' => 1],
                    'missed_calls'     => ['key' => 'missed_calls',     'date_col' => 2, 'id_col' => 0],
                    'outbound_calls'   => ['key' => 'outbound_calls',   'date_col' => 2, 'id_col' => 0]
                ];
                $config = $countingConfigs[$reportType];
                $metricKey = $config['key'];

                foreach ($lines as $line) {
                    if (empty(trim($line))) continue;
                    $columns = explode("\t", trim($line));
                    if (count($columns) < 3) continue;
                    if (is_numeric(trim($columns[$config['id_col']]))) {
                        $shamsi_date_parts = explode('/', trim($columns[$config['date_col']]));
                        if (count($shamsi_date_parts) != 3) continue;
                        $date = jalali_to_gregorian($shamsi_date_parts[0], $shamsi_date_parts[1], $shamsi_date_parts[2]);

                        $agentId = trim($columns[$config['id_col']]);

                        if (!isset($dailyCounts[$date])) $dailyCounts[$date] = [];
                        if (!isset($dailyCounts[$date][$agentId])) $dailyCounts[$date][$agentId] = 0;
                        $dailyCounts[$date][$agentId]++;
                    }
                }

                foreach ($dailyCounts as $date => $agentStats) {
                    foreach ($agentStats as $agentId => $count) {
                        if (!isset($existingData[$agentId])) $existingData[$agentId] = [];
                        if (!isset($existingData[$agentId][$date])) $existingData[$agentId][$date] = [];
                        $existingData[$agentId][$date][$metricKey] = $count;
                        $processedCount++;
                    }
                }
                break;

            case 'no_call_reason':
                $usersFile = __DIR__ . '/../data/users.json';
                if (!file_exists($usersFile)) throw new Exception("فایل users.json یافت نشد.");
                $users = json_decode(file_get_contents($usersFile), true);
                $nameToIdMap = [];
                foreach ($users as $user) {
                    $nameToIdMap[$user['name']] = $user['id'];
                }
                $dailyCounts = [];
                foreach ($lines as $line) {
                    if (empty(trim($line))) continue;
                    $columns = explode("\t", trim($line));
                    if (count($columns) < 2) continue;
                    if (strpos($columns[0], '/') === false) continue;

                    $shamsi_date_parts = explode('/', trim($columns[0]));
                    if (count($shamsi_date_parts) != 3) continue;
                    $date = jalali_to_gregorian($shamsi_date_parts[0], $shamsi_date_parts[1], $shamsi_date_parts[2]);

                    $agentName = trim($columns[1]);

                    if (isset($nameToIdMap[$agentName])) {
                        $agentId = $nameToIdMap[$agentName];
                        if (!isset($dailyCounts[$date])) $dailyCounts[$date] = [];
                        if (!isset($dailyCounts[$date][$agentId])) $dailyCounts[$date][$agentId] = 0;
                        $dailyCounts[$date][$agentId]++;
                    }
                }

                foreach ($dailyCounts as $date => $agentStats) {
                    foreach ($agentStats as $agentId => $count) {
                        if (!isset($existingData[$agentId])) $existingData[$agentId] = [];
                        if (!isset($existingData[$agentId][$date])) $existingData[$agentId][$date] = [];
                        $existingData[$agentId][$date]['no_call_reason'] = $count;
                        $processedCount++;
                    }
                }
                break;

            case 'tickets_count':
                if (empty($_POST['report_date'])) throw new Exception("برای این نوع گزارش، انتخاب تاریخ الزامی است.");
                $date = $_POST['report_date'];

                $usersFile = __DIR__ . '/../data/users.json';
                if (!file_exists($usersFile)) throw new Exception("فایل users.json یافت نشد.");
                $users = json_decode(file_get_contents($usersFile), true);
                $nameToIdMap = [];
                foreach ($users as $user) {
                    $nameToIdMap[$user['name']] = $user['id'];
                }
                foreach ($lines as $line) {
                    if (empty(trim($line))) continue;
                    $columns = explode("\t", trim($line));
                    if (count($columns) < 2) continue;
                    $agentName = trim($columns[0]);
                    $ticketCount = (int)$columns[1];
                    if (isset($nameToIdMap[$agentName])) {
                        $agentId = $nameToIdMap[$agentName];
                        if (!isset($existingData[$agentId])) $existingData[$agentId] = [];
                        if (!isset($existingData[$agentId][$date])) $existingData[$agentId][$date] = [];
                        $existingData[$agentId][$date]['tickets_count'] = $ticketCount;
                        $processedCount++;
                    }
                }
                break;
        }

        if ($processedCount > 0) {
            file_put_contents($jsonFile, json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $response['success'] = true;
            $response['message'] = "داده‌ها با موفقیت پردازش و ذخیره شدند.";
        } else {
            $response['message'] = "هیچ ردیف معتبری برای پردازش یافت نشد. لطفاً فرمت داده‌ها و نوع گزارش انتخابی را بررسی کنید.";
        }
    } catch (Exception $e) {
        http_response_code(500);
        $response['message'] = "خطای داخلی سرور: " . $e->getMessage();
    }
}

echo json_encode($response);
