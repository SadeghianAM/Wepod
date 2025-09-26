<?php
// فایل: api.php
require_once __DIR__ . '/../../auth/require-auth.php';
requireAuth('admin');
// تنظیم هدر برای پاسخ JSON
header('Content-Type: application/json');

// اتصال به دیتابیس با استفاده از فایل شما
// متغیر $pdo از این فایل در دسترس خواهد بود
require_once __DIR__ . '/../../db/database.php';

// دریافت عملیات درخواستی از URL
$action = $_GET['action'] ?? '';

// مسیردهی درخواست به تابع مربوطه
switch ($action) {
    case 'getPrizes':
        getPrizes($pdo);
        break;
    case 'getPrizeListForAdmin':
        getPrizeListForAdmin($pdo);
        break;
    case 'addPrize':
        addPrize($pdo);
        break;
    case 'deletePrize':
        deletePrize($pdo);
        break;
    case 'calculateWinner':
        calculateWinner($pdo);
        break;
    default:
        // ارسال خطا در صورت نامعتبر بودن عملیات
        echo json_encode(['error' => 'عملیات نامعتبر']);
}

// تابع برای دریافت لیست جوایز برای نمایش در گردونه
function getPrizes($pdo)
{
    $stmt = $pdo->query("SELECT name as text, color as fillStyle, type FROM prizes WHERE weight > 0");
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
        ':name' => htmlspecialchars($data['name']), // امن‌سازی ورودی
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

// تابع برای محاسبه برنده بر اساس وزن (ضریب شانس)
function calculateWinner($pdo)
{
    $stmt = $pdo->query("SELECT * FROM prizes WHERE weight > 0");
    $prizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($prizes)) {
        echo json_encode(['error' => 'هیچ جایزه‌ای برای انتخاب وجود ندارد']);
        return;
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

    // محاسبه زاویه توقف برای ارسال به فرانت‌اند
    $numSegments = count($prizes);
    $segmentAngle = 360 / $numSegments;
    // یافتن ایندکس جایزه برنده شده در آرایه
    $winnerIndex = array_search($winner['id'], array_column($prizes, 'id'));
    // یک زاویه تصادفی در محدوده سگمنت برنده
    $stopAt = ($winnerIndex * $segmentAngle) + mt_rand(1, floor($segmentAngle) - 1);

    echo json_encode([
        'winner' => $winner,
        'stopAngle' => $stopAt
    ]);
}
