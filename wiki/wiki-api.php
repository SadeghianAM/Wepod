<?php

$pdo = require_once __DIR__ . '/../db/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // فقط متد GET مجاز است
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405); // Method Not Allowed
        echo json_encode(['success' => false, 'message' => 'متد غیرمجاز.']);
        exit;
    }

    // خواندن تمام آیتم‌ها از دیتابیس
    $stmt = $pdo->query("SELECT * FROM wiki ORDER BY id ASC");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$item) {
        $item['categories'] = json_decode($item['category'], true) ?: [];
    }
    unset($item);

    echo json_encode($items);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'خطای داخلی سرور.']);
}
