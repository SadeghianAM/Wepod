<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');

$filePath = __DIR__ . '/../data/shifts.json';

if (file_exists($filePath)) {
    header('Content-Type: application/json');
    echo file_get_contents($filePath);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Settings file not found.']);
}
