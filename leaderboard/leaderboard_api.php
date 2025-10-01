<?php
// فایل: leaderboard_api.php (سازگار با فایل require-auth.php شما)
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.
header('Content-Type: application/json');

// فایل احراز هویت شما فراخوانی می‌شود
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/../auth/login.html');

// فایل اتصال به دیتابیس
require_once __DIR__ . '/../db/database.php';

$response = ['success' => false, 'data' => null];

try {
    // متغیرها را برای اطلاعات کاربر فعلی با مقدار اولیه null تعریف می‌کنیم
    $current_user_id = null;
    $user_team_id = null;

    // --- بخش کلیدی برای جلوگیری از خطا ---
    // ابتدا بررسی می‌کنیم که آیا کاربر لاگین کرده و شناسه او در توکن موجود است یا خیر
    if (isset($claims['user_id']) && !empty($claims['user_id'])) {
        $current_user_id = (int)$claims['user_id'];

        // اگر کاربر شناسایی شد، تیم او را از دیتابیس پیدا می‌کنیم
        $stmt_user_team = $pdo->prepare("SELECT team_id FROM TeamMembers WHERE user_id = ?");
        $stmt_user_team->execute([$current_user_id]);
        $user_team_id = $stmt_user_team->fetchColumn();
    }
    // --- پایان بخش کلیدی ---

    // کوئری اصلی برای رتبه‌بندی تیم‌ها (سازگار با SQLite)
    $main_query = "
        SELECT
            t.id AS team_id,
            t.team_name,
            SUM(u.score) AS total_score,
            group_concat(u.id || ':' || u.name || ':' || u.score, '||') AS members_data
        FROM Teams t
        LEFT JOIN TeamMembers tm ON t.id = tm.team_id
        LEFT JOIN Users u ON tm.user_id = u.id
        WHERE u.id IS NOT NULL
        GROUP BY t.id, t.team_name
        ORDER BY total_score DESC, t.team_name ASC
    ";

    $stmt = $pdo->query($main_query);
    $teams_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $rankings = [];
    $rank = 1;
    foreach ($teams_raw as $team) {
        $members = [];
        if (!empty($team['members_data'])) {
            $members_raw = explode('||', $team['members_data']);
            foreach ($members_raw as $member_str) {
                if (substr_count($member_str, ':') >= 2) {
                    list($id, $name, $score) = explode(':', $member_str, 3);
                    $members[] = [
                        'id' => (int)$id,
                        'name' => $name,
                        'score' => (int)$score
                    ];
                }
            }
            usort($members, fn($a, $b) => $b['score'] <=> $a['score']);
        }

        $rankings[] = [
            'rank' => $rank++,
            'team_id' => (int)$team['team_id'],
            'team_name' => $team['team_name'],
            'total_score' => (int)$team['total_score'],
            'members' => $members
        ];
    }

    $response = [
        'success' => true,
        'data' => [
            'rankings' => $rankings,
            'currentUser' => [
                'id' => $current_user_id,
                'teamId' => $user_team_id ? (int)$user_team_id : null
            ]
        ]
    ];
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
