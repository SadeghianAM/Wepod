<?php

// اتصال به دیتابیس
require_once __DIR__ . '/../db/database.php';
// فایل مربوط به احراز هویت
require_once __DIR__ . '/../auth/require-auth.php';

// تنظیم هدر برای پاسخ JSON
header('Content-Type: application/json');

// دریافت عملیات درخواستی از URL
$action = $_GET['action'] ?? '';

// مسیردهی درخواست به تابع مربوطه
switch ($action) {
    case 'getPrizes':
        requireAuth(null);
        getPrizes($pdo);
        break;

    case 'calculateWinner':
        $claims = requireAuth(null);
        calculateWinner($pdo, $claims);
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

    // ===================================================================
    // ** این بخش تغییر کرده است **
    // اکنون سیستم بررسی می‌کند که مجموع ضرایب تعریف شده توسط ادمین دقیقا 100 باشد
    if ($totalWeight != 100) {
        throw new Exception("پیکربندی جوایز نامعتبر است. مجموع ضریب شانس باید دقیقاً 100 باشد.");
    }
    // ===================================================================

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
            return [
                'text' => $p['name'],
                'color' => $p['color'],
                'type' => $p['type']
            ];
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
            // با توجه به اینکه مجموع وزن 100 است، این بخش به عنوان اطمینان ثانویه عمل می‌کند
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
        echo json_encode(['error' => 'خطا: ' . $e->getMessage()]);
    }
}
