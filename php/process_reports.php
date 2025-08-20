<?php
require_once __DIR__ . '/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => false,
    'message' => 'درخواست نامعتبر است.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excel_data'])) {
    try {
        $jsonFile = __DIR__ . '/../data/reports.json';
        $pastedData = trim($_POST['excel_data']);
        $lines = explode("\n", $pastedData);
        $existingData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

        if (json_last_error() !== JSON_ERROR_NONE && !is_array($existingData)) {
            $existingData = [];
        }

        $processedCount = 0;
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

            $existingData[$agentId][$date] = $reportData;
            $processedCount++;
        }

        if ($processedCount > 0) {
            file_put_contents($jsonFile, json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $response['success'] = true;
            $response['message'] = "$processedCount ردیف با موفقیت پردازش و ذخیره شد.";
        } else {
            $response['message'] = "هیچ ردیف معتبری برای پردازش یافت نشد. لطفاً فرمت داده‌ها را بررسی کنید.";
        }
    } catch (Exception $e) {
        http_response_code(500);
        $response['message'] = "خطای داخلی سرور: " . $e->getMessage();
    }
}

echo json_encode($response);
