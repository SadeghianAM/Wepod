<?php

// اتصال به دیتابیس
require_once __DIR__ . '/../db/database.php';
// فایل مربوط به احراز هویت
require_once __DIR__ . '/../auth/require-auth.php';

// تنظیم هدر برای پاسخ JSON
header('Content-Type: application/json');

// دریافت عملیات درخواستی از URL
$action = $_GET['action'] ?? '';

/**
 * تابع کمکی برای دریافت یک تنظیم خاص از دیتابیس
 */
function getSetting($pdo, $key, $default = null)
{
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = :key");
    $stmt->execute([':key' => $key]);
    $result = $stmt->fetchColumn();
    return $result !== false ? $result : $default;
}

/**
 * تابع: بررسی می‌کند آیا کاربر شانس چرخش دارد یا خیر
 */
function checkUserChance($pdo, $userId)
{
    $isWheelEnabled = getSetting($pdo, 'is_wheel_enabled', '0');
    $lastEnabledAt = getSetting($pdo, 'wheel_last_enabled_at');

    if ($isWheelEnabled !== '1') {
        return ['canSpin' => false, 'reason' => 'گردونه شانس در حال حاضر غیرفعال است.'];
    }

    if (!$lastEnabledAt) {
        return ['canSpin' => false, 'reason' => 'پیکربندی گردونه ناقص است.'];
    }

    $stmt = $pdo->prepare("SELECT won_at FROM prize_winners WHERE user_id = :user_id ORDER BY won_at DESC LIMIT 1");
    $stmt->execute([':user_id' => $userId]);
    $lastPlayTimestamp = $stmt->fetchColumn();

    if ($lastPlayTimestamp && strtotime($lastPlayTimestamp) >= strtotime($lastEnabledAt)) {
        return ['canSpin' => false, 'reason' => 'شما شانس خود را برای این دوره استفاده کرده‌اید.'];
    }

    return ['canSpin' => true, 'reason' => 'بچرخان!'];
}

// مسیردهی درخواست به تابع مربوطه
switch ($action) {
    case 'getPrizes':
        requireAuth(null);
        getPrizes($pdo);
        break;

    case 'getWheelStatus':
        $claims = requireAuth(null);
        if (!isset($claims['sub'])) {
            http_response_code(401);
            echo json_encode(['error' => 'کاربر شناسایی نشد.']);
            exit;
        }
        $userId = $claims['sub'];
        $status = checkUserChance($pdo, $userId);
        echo json_encode($status);
        break;

    case 'calculateWinner':
        $claims = requireAuth(null);
        calculateWinner($pdo, $claims);
        break;

    case 'getLastWinnerInfo':
        $claims = requireAuth(null);
        getLastWinnerInfo($pdo, $claims);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'عملیات نامعتبر یا یافت نشد']);
}

/**
 * تابع کمکی برای دریافت لیست جوایز تعریف شده در دیتابیس
 */
function _getWheelPrizes($pdo)
{
    $stmt = $pdo->query("SELECT id, name, color, type, weight FROM prizes WHERE weight > 0 ORDER BY id ASC");
    $prizes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalWeight = array_sum(array_column($prizes, 'weight'));

    if ($totalWeight != 100) {
        throw new Exception("پیکربندی جوایز نامعتبر است. مجموع ضریب شانس باید دقیقاً 100 باشد.");
    }

    return $prizes;
}

/**
 * تابع برای دریافت لیست جوایز برای نمایش در گردونه
 */
function getPrizes($pdo)
{
    try {
        $prizes = _getWheelPrizes($pdo);
        $frontendPrizes = array_map(function ($p) {
            return ['text' => $p['name'], 'color' => $p['color'], 'type' => $p['type']];
        }, $prizes);
        echo json_encode($frontendPrizes);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'خطا: ' . $e->getMessage()]);
    }
}

/**
 * تابع برای محاسبه برنده و ذخیره سابقه
 */
function calculateWinner($pdo, $claims)
{
    try {
        if (!isset($claims['sub'])) {
            throw new Exception("شناسه کاربر (sub) در توکن JWT یافت نشد.");
        }
        $userId = $claims['sub'];

        $chanceCheck = checkUserChance($pdo, $userId);
        if (!$chanceCheck['canSpin']) {
            http_response_code(403);
            echo json_encode(['error' => $chanceCheck['reason']]);
            return;
        }

        $wheelPrizes = _getWheelPrizes($pdo);
        if (empty($wheelPrizes)) {
            throw new Exception("هیچ جایزه‌ای برای انتخاب وجود ندارد.");
        }

        $randomNumber = mt_rand(1, 100);
        $currentWeight = 0;
        $winner = null;
        foreach ($wheelPrizes as $prize) {
            $currentWeight += $prize['weight'];
            if ($randomNumber <= $currentWeight) {
                $winner = $prize;
                break;
            }
        }
        if (!$winner) {
            $winner = end($wheelPrizes);
        }

        if ($winner && $winner['id'] > 0) {
            $sql = "INSERT INTO prize_winners (user_id, prize_id) VALUES (:user_id, :prize_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user_id' => $userId, ':prize_id' => $winner['id']]);
        }

        $numSegments = count($wheelPrizes);
        $segmentAngle = 360 / $numSegments;
        $winnerIndex = -1;
        foreach ($wheelPrizes as $index => $prize) {
            if ($prize['id'] === $winner['id']) {
                $winnerIndex = $index;
                break;
            }
        }

        if ($winnerIndex === -1) {
            throw new Exception("جایزه برنده شده در لیست جوایز یافت نشد.");
        }

        $randomAngleInSegment = mt_rand(1, max(1, floor($segmentAngle) - 1));
        $finalAngle = ($winnerIndex * $segmentAngle) + $randomAngleInSegment;
        $stopAt = 360 - $finalAngle;

        echo json_encode(['winner' => $winner, 'stopAngle' => $stopAt]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'خطای سرور: ' . $e->getMessage()]);
    }
}

/**
 * تابع: اطلاعات آخرین جایزه برنده شده کاربر را برمی‌گرداند
 */
function getLastWinnerInfo($pdo, $claims)
{
    if (!isset($claims['sub'])) {
        http_response_code(401);
        echo json_encode(['error' => 'کاربر شناسایی نشد.']);
        return;
    }
    $userId = $claims['sub'];

    $sql = "SELECT p.name AS prize_name, pw.won_at
            FROM prize_winners pw
            JOIN prizes p ON pw.prize_id = p.id
            WHERE pw.user_id = :user_id
            ORDER BY pw.won_at DESC
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $lastWinner = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lastWinner) {
        echo json_encode($lastWinner);
    } else {
        echo json_encode(null);
    }
}
