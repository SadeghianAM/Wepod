<?php
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

// فایل: admin/polls/polls-api.php
header('Content-Type: application/json; charset=utf-8');

// احراز هویت ادمین
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin');

// اتصال به پایگاه داده
require_once __DIR__ . '/../../db/database.php';

function send_json_response($data)
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        switch ($action) {
            case 'getPolls':
                // --- START: کد اصلاح شده ---
                $sql = "
                    SELECT
                        p.*,
                        COUNT(DISTINCT po.id) AS options_count,
                        COUNT(DISTINCT uv.id) AS votes_count
                    FROM
                        polls p
                    LEFT JOIN
                        poll_options po ON p.id = po.poll_id
                    LEFT JOIN
                        user_votes uv ON p.id = uv.poll_id
                    GROUP BY
                        p.id
                    ORDER BY
                        p.id DESC
                ";
                $stmt = $pdo->query($sql);
                send_json_response($stmt->fetchAll(PDO::FETCH_ASSOC));
                // --- END: کد اصلاح شده ---
                break;

            case 'getOptions':
                $poll_id = filter_input(INPUT_GET, 'poll_id', FILTER_VALIDATE_INT);
                if (!$poll_id) throw new Exception('شناسه نظرسنجی نامعتبر است.');

                $stmt = $pdo->prepare("SELECT * FROM poll_options WHERE poll_id = ? ORDER BY id");
                $stmt->execute([$poll_id]);
                send_json_response($stmt->fetchAll(PDO::FETCH_ASSOC));
                break;

            case 'getPollResults':
                $poll_id = filter_input(INPUT_GET, 'poll_id', FILTER_VALIDATE_INT);
                if (!$poll_id) throw new Exception('شناسه نظرسنجی نامعتبر است.');

                $sql = "
                    SELECT
                        u.name AS user_name,
                        po.option_text,
                        uv.voted_at
                    FROM
                        user_votes uv
                    JOIN
                        users u ON uv.user_id = u.id
                    JOIN
                        poll_options po ON uv.option_id = po.id
                    WHERE
                        uv.poll_id = ?
                    ORDER BY
                        uv.voted_at DESC
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$poll_id]);
                send_json_response($stmt->fetchAll(PDO::FETCH_ASSOC));
                break;
        }
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        switch ($action) {
            case 'addPoll':
                $question = $data['question'] ?? '';
                if (empty($question)) throw new Exception('متن سوال نمی‌تواند خالی باشد.');
                $stmt = $pdo->prepare("INSERT INTO polls (question) VALUES (?)");
                $stmt->execute([$question]);
                send_json_response(['success' => true, 'id' => $pdo->lastInsertId()]);
                break;

            case 'updatePoll':
                $id = $data['id'] ?? 0;
                $question = $data['question'] ?? '';
                if (!$id || empty($question)) throw new Exception('اطلاعات ارسالی ناقص است.');
                $stmt = $pdo->prepare("UPDATE polls SET question = ? WHERE id = ?");
                $stmt->execute([$question, $id]);
                send_json_response(['success' => true]);
                break;

            case 'deletePoll':
                $id = $data['id'] ?? 0;
                if (!$id) throw new Exception('شناسه نظرسنجی نامعتبر است.');
                // با فرض اینکه در دیتابیس ON DELETE CASCADE تنظیم شده است
                $stmt = $pdo->prepare("DELETE FROM polls WHERE id = ?");
                $stmt->execute([$id]);
                send_json_response(['success' => true]);
                break;

            case 'setActivePoll':
                $id = $data['id'] ?? 0;
                if (!$id) throw new Exception('شناسه نظرسنجی نامعتبر است.');
                $pdo->beginTransaction();
                $pdo->exec("UPDATE polls SET is_active = 0");
                $stmt = $pdo->prepare("UPDATE polls SET is_active = 1 WHERE id = ?");
                $stmt->execute([$id]);
                $pdo->commit();
                send_json_response(['success' => true]);
                break;

            case 'addOption':
                $poll_id = $data['poll_id'] ?? 0;
                $text = $data['text'] ?? '';
                $capacity = $data['capacity'] ?? 0;
                if (!$poll_id || empty($text) || $capacity <= 0) throw new Exception('اطلاعات گزینه ناقص یا نامعتبر است.');
                $stmt = $pdo->prepare("INSERT INTO poll_options (poll_id, option_text, capacity) VALUES (?, ?, ?)");
                $stmt->execute([$poll_id, $text, $capacity]);
                send_json_response(['success' => true, 'id' => $pdo->lastInsertId()]);
                break;

            case 'deleteOption':
                $id = $data['id'] ?? 0;
                if (!$id) throw new Exception('شناسه گزینه نامعتبر است.');
                $stmt = $pdo->prepare("DELETE FROM poll_options WHERE id = ?");
                $stmt->execute([$id]);
                send_json_response(['success' => true]);
                break;
        }
    }

    throw new Exception('درخواست نامعتبر است.');
} catch (Exception $e) {
    http_response_code(400);
    send_json_response(['success' => false, 'message' => $e->getMessage()]);
}
