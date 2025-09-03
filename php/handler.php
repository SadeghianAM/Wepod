<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');

$data_directory = $_SERVER['DOCUMENT_ROOT'] . '/data/';
$seats_file = $data_directory . 'seats.json';
$users_file = $data_directory . 'users.json';

if (!is_dir($data_directory)) {
    mkdir($data_directory, 0775, true);
}

function read_file($filepath)
{
    if (!file_exists($filepath)) return [];
    return json_decode(file_get_contents($filepath), true);
}

function write_seats($data)
{
    global $seats_file;
    file_put_contents($seats_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'getData':
        $seats_data = read_file($seats_file);
        $users_data = read_file($users_file); // users_data اکنون یک آرایه مستقیم از کاربران است

        echo json_encode([
            'seats' => $seats_data['seats'] ?? [],
            'users' => $users_data ?? [] // 🔽 تغییر: دیگر نیازی به ['users'] نیست
        ]);
        break;

    case 'selectSeat':
        // ID کاربر از توکن احراز هویت گرفته می‌شود
        $userId = $claims['sub'] ?? ($claims['id'] ?? null);
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'شناسه کاربری نامعتبر است.']);
            exit;
        }

        $seatId = $_POST['seatId'] ?? null;
        $seats_data = read_file($seats_file);

        // ۱. میز قبلی کاربر را خالی کن
        foreach ($seats_data['seats'] as &$s) {
            if (isset($s['userId']) && $s['userId'] == $userId) {
                $s['status'] = 'available';
                $s['userId'] = null;
            }
        }
        unset($s);

        // ۲. اگر میز جدیدی انتخاب شده بود، آن را ثبت کن
        if ($seatId) {
            $seat_found = false;
            foreach ($seats_data['seats'] as &$seat) {
                if ($seat['id'] === $seatId && $seat['status'] === 'available') {
                    $seat['status'] = 'occupied';
                    $seat['userId'] = $userId;
                    $seat_found = true;
                    break;
                }
            }
            if (!$seat_found) {
                echo json_encode(['success' => false, 'message' => 'این میز دیگر در دسترس نیست.']);
                exit;
            }
        }

        write_seats($seats_data);
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'درخواست نامعتبر است.']);
        break;
}
