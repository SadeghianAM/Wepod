<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../../auth/require-auth.php';
requireAuth('admin');

header('Content-Type: application/json');

$db_path = __DIR__ . '/../../db/database.db';
try {
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'خطا در اتصال به دیتابیس: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, name, username, start_work, is_admin, score, role, spin_chances FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'create':
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (id, name, username, password_hash, start_work, is_admin, score, role, spin_chances) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['id'], $data['name'], $data['username'], $hash, $data['start_work'], $data['is_admin'], $data['score'], $data['role'], $data['spin_chances']]);
            echo json_encode(['success' => true, 'id' => $data['id']]);
            break;

        case 'update':
            $sql = "UPDATE users SET id = ?, name = ?, username = ?, start_work = ?, is_admin = ?, score = ?, role = ?, spin_chances = ? WHERE id = ?";
            $params = [$data['new_id'], $data['name'], $data['username'], $data['start_work'], $data['is_admin'], $data['score'], $data['role'], $data['spin_chances'], $data['id']];

            if (!empty($data['password'])) {
                $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE users SET id = ?, name = ?, username = ?, password_hash = ?, start_work = ?, is_admin = ?, score = ?, role = ?, spin_chances = ? WHERE id = ?";
                $params = [$data['new_id'], $data['name'], $data['username'], $hash, $data['start_work'], $data['is_admin'], $data['score'], $data['role'], $data['spin_chances'], $data['id']];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true]);
            break;

        case 'delete':
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'عملیات نامعتبر است.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'متد درخواست غیرمجاز است.']);
}
