<?php
date_default_timezone_set('Asia/Tehran');
require_once __DIR__ . '/../../db/database.php';
require_once __DIR__ . '/../../auth/require-auth.php';

header('Content-Type: application/json');

// This action is public for the wheel logic, others are admin-only.
if ($_GET['action'] !== 'getWheelSettingsForUser') {
    requireAuth('admin');
}

$action = $_GET['action'] ?? '';

switch ($action) {
    // Admin Actions
    case 'getPrizeListForAdmin':
        getPrizeListForAdmin($pdo);
        break;
    case 'addPrize':
        addPrize($pdo);
        break;
    case 'updatePrize':
        updatePrize($pdo);
        break;
    case 'deletePrize':
        deletePrize($pdo);
        break;
    case 'getWinnerHistory':
        getWinnerHistory($pdo);
        break;
    case 'deleteWinnerRecord':
        deleteWinnerRecord($pdo);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'عملیات نامعتبر یا یافت نشد']);
}

// ===================================================================
// ** توابع مدیریت جوایز و سوابق **
// ===================================================================

function getPrizeListForAdmin($pdo)
{
    // کوئری دیگر نیازی به شمارش تعداد برد ندارد
    $stmt = $pdo->query("SELECT id, name, color, type, weight FROM prizes ORDER BY id DESC");
    $prizes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($prizes);
}

function addPrize($pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $newWeight = $data['weight'] ?? 0;

    $stmt = $pdo->query("SELECT SUM(weight) as total_weight FROM prizes");
    $currentTotalWeight = $stmt->fetch(PDO::FETCH_ASSOC)['total_weight'] ?? 0;

    if (($currentTotalWeight + $newWeight) > 100) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'مجموع ضریب شانس جوایز نمی‌تواند بیشتر از 100 باشد.']);
        return;
    }

    $sql = "INSERT INTO prizes (name, color, type, weight) VALUES (:name, :color, :type, :weight)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => htmlspecialchars($data['name']),
        ':color' => $data['color'],
        ':type' => $data['type'],
        ':weight' => $newWeight
    ]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
}

function updatePrize($pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['id'], $data['name'], $data['color'], $data['type'], $data['weight'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'اطلاعات ارسالی ناقص است.']);
        return;
    }

    $prizeId = $data['id'];
    $newWeight = $data['weight'];

    $stmt = $pdo->prepare("SELECT SUM(weight) as total_weight FROM prizes WHERE id != :id");
    $stmt->execute([':id' => $prizeId]);
    $otherPrizesWeight = $stmt->fetch(PDO::FETCH_ASSOC)['total_weight'] ?? 0;

    if (($otherPrizesWeight + $newWeight) > 100) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'مجموع ضریب شانس جوایز نمی‌تواند بیشتر از 100 باشد.']);
        return;
    }

    $sql = "UPDATE prizes SET name = :name, color = :color, type = :type, weight = :weight WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $prizeId,
        ':name' => htmlspecialchars($data['name']),
        ':color' => $data['color'],
        ':type' => $data['type'],
        ':weight' => $newWeight
    ]);
    echo json_encode(['success' => true]);
}

function deletePrize($pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "DELETE FROM prizes WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $data['id']]);
    echo json_encode(['success' => true]);
}

function getWinnerHistory($pdo)
{
    $sql = "SELECT pw.id, u.name AS user_name, p.name AS prize_name, pw.won_at
            FROM prize_winners pw
            JOIN users u ON pw.user_id = u.id
            JOIN prizes p ON pw.prize_id = p.id
            ORDER BY pw.won_at DESC
            LIMIT 50";
    $stmt = $pdo->query($sql);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($history as &$record) {
        if (!empty($record['won_at'])) {
            $record['won_at'] = date('c', strtotime($record['won_at']));
        }
    }
    echo json_encode($history);
}

function deleteWinnerRecord($pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'آیدی رکورد مشخص نشده است.']);
        return;
    }
    $sql = "DELETE FROM prize_winners WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $data['id']]);
    echo json_encode(['success' => true]);
}
