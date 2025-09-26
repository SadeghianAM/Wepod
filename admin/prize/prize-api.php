<?php
// فایل: admin/prize/prize-api.php

// شروع یا ادامه یک سشن
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// اتصال به دیتابیس
require_once __DIR__ . '/../../db/database.php';
// فایل مربوط به احراز هویت
require_once __DIR__ . '/../../auth/require-auth.php';

// تنظیم هدر برای پاسخ JSON
header('Content-Type: application/json');

// دریافت عملیات درخواستی از URL
$action = $_GET['action'] ?? '';

// مسیردهی درخواست به تابع مربوطه
switch ($action) {
    case 'getPrizes':
        // هر کاربر لاگین کرده‌ای می‌تواند لیست جوایز را برای نمایش ببیند
        requireAuth();
        getPrizes($pdo);
        break;

    case 'calculateWinner':
        // خروجی تابع احراز هویت را در متغیر claims$ ذخیره می‌کنیم
        $claims = requireAuth();
        // متغیر claims$ را به عنوان ورودی به تابع ارسال می‌کنیم
        calculateWinner($pdo, $claims);
        break;

    case 'getPrizeListForAdmin':
        // فقط ادمین می‌تواند لیست کامل جوایز را برای مدیریت ببیند
        requireAuth('admin');
        getPrizeListForAdmin($pdo);
        break;

    case 'addPrize':
        // فقط ادمین می‌تواند جایزه اضافه کند
        requireAuth('admin');
        addPrize($pdo);
        break;

    case 'deletePrize':
        // فقط ادمین می‌تواند جایزه حذف کند
        requireAuth('admin');
        deletePrize($pdo);
        break;

    case 'getWinnerHistory':
        // فقط ادمین می‌تواند سوابق را مشاهده کند
        requireAuth('admin');
        getWinnerHistory($pdo);
        break;

    default:
        // ارسال خطا در صورت نامعتبر بودن عملیات
        echo json_encode(['error' => 'عملیات نامعتبر']);
}

// تابع برای دریافت لیست جوایز برای نمایش در گردونه
function getPrizes($pdo)
{
    $stmt = $pdo->query("SELECT name as text, color, type FROM prizes WHERE weight > 0");
    $prizes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($prizes);
}

// تابع برای دریافت لیست کامل جوایز برای پنل ادمین
function getPrizeListForAdmin($pdo)
{
    $stmt = $pdo->query("SELECT * FROM prizes ORDER BY id DESC");
    $prizes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($prizes);
}

// تابع برای افزودن جایزه جدید
function addPrize($pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "INSERT INTO prizes (name, color, type, weight) VALUES (:name, :color, :type, :weight)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => htmlspecialchars($data['name']),
        ':color' => $data['color'],
        ':type' => $data['type'],
        ':weight' => $data['weight']
    ]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
}

// تابع برای حذف جایزه
function deletePrize($pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "DELETE FROM prizes WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $data['id']]);
    echo json_encode(['success' => true]);
}

// تابع برای محاسبه برنده و ذخیره سابقه (هماهنگ شده با require-auth.php)
function calculateWinner($pdo, $claims)
{
    try {
        // *** تغییر اینجاست: 'user_id' به 'sub' تبدیل شد ***
        if (!isset($claims['sub'])) {
            throw new Exception("شناسه کاربر (sub) در توکن JWT یافت نشد.");
        }
        // *** و اینجا ***
        $userId = $claims['sub'];

        $stmt = $pdo->query("SELECT * FROM prizes WHERE weight > 0");
        $prizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($prizes)) {
            throw new Exception("هیچ جایزه‌ای برای انتخاب وجود ندارد.");
        }

        $totalWeight = array_sum(array_column($prizes, 'weight'));
        $randomNumber = mt_rand(1, $totalWeight);
        $currentWeight = 0;
        $winner = null;

        foreach ($prizes as $prize) {
            $currentWeight += $prize['weight'];
            if ($randomNumber <= $currentWeight) {
                $winner = $prize;
                break;
            }
        }

        if ($winner) {
            $sql = "INSERT INTO prize_winners (user_id, prize_id) VALUES (:user_id, :prize_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':prize_id' => $winner['id']
            ]);
        } else {
            throw new Exception("محاسبه برنده با شکست مواجه شد.");
        }

        // محاسبه زاویه توقف برای ارسال به فرانت‌اند
        $numSegments = count($prizes);
        $segmentAngle = 360 / $numSegments;
        $winnerIndex = -1;
        foreach ($prizes as $index => $prize) {
            if ($prize['id'] === $winner['id']) {
                $winnerIndex = $index;
                break;
            }
        }
        if ($winnerIndex === -1) {
            throw new Exception("جایزه برنده شده در لیست جوایز یافت نشد.");
        }
        $stopAt = ($winnerIndex * $segmentAngle) + mt_rand(1, floor($segmentAngle) - 1);

        echo json_encode(['winner' => $winner, 'stopAngle' => $stopAt]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'خطا: ' . $e->getMessage()]);
    }
}


// تابع برای دریافت سوابق برندگان
function getWinnerHistory($pdo)
{
    $sql = "SELECT
                u.name AS user_name,
                p.name AS prize_name,
                pw.won_at
            FROM
                prize_winners pw
            JOIN
                users u ON pw.user_id = u.id
            JOIN
                prizes p ON pw.prize_id = p.id
            ORDER BY
                pw.won_at DESC
            LIMIT 50";

    $stmt = $pdo->query($sql);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($history);
}
