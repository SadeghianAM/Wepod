<?php

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
    case 'getAppSettings':
        getAppSettings($pdo);
        break;
    case 'updateWheelStatus':
        updateWheelStatus($pdo);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'عملیات نامعتبر یا یافت نشد']);
}

// ===================================================================
// ** بخش جدید: توابع مدیریت تنظیمات **
// ===================================================================

// تابع برای دریافت یک تنظیم خاص
function getSetting($pdo, $key, $default = null)
{
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = :key");
    $stmt->execute([':key' => $key]);
    $result = $stmt->fetchColumn();
    return $result !== false ? $result : $default;
}

// تابع برای ثبت یا به‌روزرسانی یک تنظیم
function setSetting($pdo, $key, $value)
{
    $sql = "INSERT OR REPLACE INTO settings (setting_key, setting_value) VALUES (:key, :value)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':key' => $key, ':value' => $value]);
}

// تابع برای دریافت تمام تنظیمات (برای پنل ادمین)
function getAppSettings($pdo)
{
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    echo json_encode($settings);
}

// تابع برای به‌روزرسانی وضعیت گردونه شانس
function updateWheelStatus($pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $isEnabled = filter_var($data['enabled'], FILTER_VALIDATE_BOOLEAN);

    setSetting($pdo, 'is_wheel_enabled', $isEnabled ? '1' : '0');

    if ($isEnabled) {
        // [این خط اصلاح شده است]
        // دستور NOW() با CURRENT_TIMESTAMP برای سازگاری با SQLite جایگزین شد
        $stmt = $pdo->query("SELECT CURRENT_TIMESTAMP");
        $now = $stmt->fetchColumn();
        setSetting($pdo, 'wheel_last_enabled_at', $now);
    }

    echo json_encode(['success' => true]);
}

// ===================================================================
// ** توابع مدیریت جوایز و سوابق (با تغییرات) **
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
