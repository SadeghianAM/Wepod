<?php
require_once __DIR__ . '/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

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

        if (json_last_error() !== JSON_ERROR_NONE && !is_array($existingData)) {
            $existingData = [];
        }

        $processedCount = 0;

        switch ($reportType) {
            case 'call_metrics':
                foreach ($lines as $line) {
                    if (empty(trim($line))) continue;
                    $columns = explode("\t", trim($line));
                    if (count($columns) < 17) continue;
                    $agentId = trim($columns[0]);
                    $date = str_replace('/', '-', trim($columns[9]));
                    $reportData = [
                        "answered_calls"  => (int)str_replace(',', '', $columns[11]),
                        "total_talk_time" => (int)str_replace(',', '', $columns[12]),
                        "avg_talk_time"   => (int)str_replace(',', '', $columns[13]),
                        "max_talk_time"   => (int)str_replace(',', '', $columns[14]),
                        "avg_rating"      => (float)$columns[15],
                        "ratings_count"   => (int)$columns[16]
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
                    if (count($columns) < 10) continue;
                    $agentId = trim($columns[0]);
                    $date = str_replace('/', '-', trim($columns[7]));
                    $presenceDuration = (int)str_replace(',', '', $columns[8]);
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
                    if (count($columns) < 9) continue;
                    $agentId = trim($columns[0]);
                    $date = str_replace('/', '-', trim($columns[7]));
                    $offQueueDuration = (int)str_replace(',', '', $columns[8]);
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
                    $date = str_replace('/', '-', trim($columns[$config['date_col']]));
                    $agentId = trim($columns[$config['id_col']]);

                    if (!isset($dailyCounts[$date])) $dailyCounts[$date] = [];
                    if (!isset($dailyCounts[$date][$agentId])) $dailyCounts[$date][$agentId] = 0;

                    $dailyCounts[$date][$agentId]++;
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
                if (!file_exists($usersFile)) {
                    throw new Exception("فایل users.json یافت نشد.");
                }
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

                    $date = str_replace('/', '-', trim($columns[0]));
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
                if (empty($_POST['report_date'])) {
                    throw new Exception("برای این نوع گزارش، انتخاب تاریخ الزامی است.");
                }
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
