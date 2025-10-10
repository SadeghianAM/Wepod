<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
require_once __DIR__ . '/../db/database.php';

$user_id = $claims['sub'];

$stmt_team = $pdo->prepare("SELECT team_id FROM TeamMembers WHERE user_id = ?");
$stmt_team->execute([$user_id]);
$team_id = $stmt_team->fetchColumn();

$stmt_completed_quizzes = $pdo->prepare("SELECT DISTINCT quiz_id FROM QuizAttempts WHERE user_id = ?");
$stmt_completed_quizzes->execute([$user_id]);
$completed_quiz_ids = $stmt_completed_quizzes->fetchAll(PDO::FETCH_COLUMN);

$sql_quizzes = "
    SELECT DISTINCT q.id, q.title, q.description
    FROM Quizzes q
    LEFT JOIN QuizUserAssignments qua ON q.id = qua.quiz_id
    LEFT JOIN QuizTeamAssignments qta ON q.id = qta.quiz_id
    WHERE qua.user_id = :user_id OR qta.team_id = :team_id OR (
        NOT EXISTS (SELECT 1 FROM QuizUserAssignments WHERE quiz_id = q.id) AND
        NOT EXISTS (SELECT 1 FROM QuizTeamAssignments WHERE quiz_id = q.id)
    )
    ORDER BY q.id DESC
";
$stmt_quizzes = $pdo->prepare($sql_quizzes);
$stmt_quizzes->execute([':user_id' => $user_id, ':team_id' => $team_id ?: null]);
$quizzes = $stmt_quizzes->fetchAll(PDO::FETCH_ASSOC);

$scenarios = [];
$completed_scenario_ids = [];

if ($team_id) {
    $stmt_scenarios = $pdo->prepare("
        SELECT s.id, s.title, s.description, sa.is_active
        FROM ScenarioAssignments sa
        JOIN Scenarios s ON sa.scenario_id = s.id
        WHERE sa.team_id = ?
        ORDER BY s.id DESC
    ");
    $stmt_scenarios->execute([$team_id]);
    $scenarios = $stmt_scenarios->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($scenarios)) {
        $scenario_ids = array_column($scenarios, 'id');
        $placeholders = implode(',', array_fill(0, count($scenario_ids), '?'));

        $stmt_completed = $pdo->prepare("
            SELECT s.id
            FROM Scenarios s
            WHERE s.id IN ($placeholders)
            AND (SELECT COUNT(*) FROM Challenges c WHERE c.scenario_id = s.id) =
                (SELECT COUNT(*) FROM ChallengeAnswers ca JOIN Challenges c ON ca.challenge_id = c.id WHERE c.scenario_id = s.id AND ca.user_id = ? AND ca.status = 'approved')
            AND (SELECT COUNT(*) FROM Challenges c WHERE c.scenario_id = s.id) > 0
        ");
        $params = array_merge($scenario_ids, [$user_id]);
        $stmt_completed->execute($params);
        $completed_scenario_ids = $stmt_completed->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>لیست آزمون‌ها و سناریوها</title>
    <style>
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --primary-light: #e6f7f2;
            --bg-color: #f7f9fa;
            --card-bg: #fff;
            --text-color: #1a1a1a;
            --secondary-text: #555;
            --header-text: #fff;
            --border-color: #e9e9e9;
            --radius: 12px;
            --shadow-sm: 0 2px 6px rgba(0, 120, 80, .06);
            --shadow-md: 0 6px 20px rgba(0, 120, 80, .10);
        }

        @font-face {
            font-family: "Vazirmatn";
            src: url("/assets/fonts/Vazirmatn[wght].ttf") format("truetype");
            font-weight: 100 900;
            font-display: swap;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Vazirmatn", system-ui, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            direction: rtl;
            background: var(--bg-color);
            color: var(--text-color);
        }

        a {
            color: inherit;
            text-decoration: none;
            transition: all .2s ease;
        }

        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 10;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
            min-height: 60px;
            font-size: .85rem;
            justify-content: center;
        }

        header h1 {
            font-weight: 700;
            font-size: 1.2rem;
        }

        main {
            flex: 1;
            width: min(1200px, 100%);
            padding: 2.5rem 2rem;
            margin-inline: auto;
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: .5rem;
        }

        .page-subtitle {
            color: var(--secondary-text);
            font-weight: 400;
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .section-divider {
            border: 0;
            height: 1px;
            background-color: var(--border-color);
            margin: 3rem 0;
        }

        .tools-grid {
            list-style: none;
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }

        .tool-card a {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: .75rem;
            padding: 1.75rem;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            height: 100%;
        }

        .tool-card a:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
            color: var(--primary-dark);
        }

        .tool-card.completed a {
            background-color: #f1f3f5;
            cursor: default;
            color: #868e96;
            border-color: var(--border-color);
        }

        .tool-card.completed a:hover {
            transform: none;
            box-shadow: var(--shadow-sm);
        }

        .badge {
            font-size: 0.8rem;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 12px;
            position: absolute;
            top: 1.25rem;
            left: 1.25rem;
        }

        .completed-badge {
            color: var(--primary-dark);
            background-color: var(--primary-light);
        }

        .tool-icon {
            font-size: 2rem;
            line-height: 1;
        }

        .tool-title {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .tool-description {
            color: var(--secondary-text);
            font-size: .9rem;
            line-height: 1.6;
            margin-top: .5rem;
        }

        .tool-card.disabled a {
            background-color: #f8f9fa;
            color: #adb5bd;
            cursor: not-allowed;
        }

        .tool-card.disabled a:hover {
            transform: none;
            box-shadow: var(--shadow-sm);
            border-color: var(--border-color);
            color: #adb5bd;
        }

        .disabled-badge {
            color: #6c757d;
            background-color: #e9ecef;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <h1 class="page-title">آزمون‌های شما</h1>
        <p class="page-subtitle">برای شروع، یکی از آزمون‌های در دسترس خود را انتخاب کنید.</p>
        <ul class="tools-grid">
            <?php if (empty($quizzes)): ?>
                <p>در حال حاضر هیچ آزمونی برای شما تعریف نشده است.</p>
            <?php else: ?>
                <?php foreach ($quizzes as $quiz):
                    $is_quiz_completed = in_array($quiz['id'], $completed_quiz_ids); ?>
                    <li class="tool-card <?= $is_quiz_completed ? 'completed' : '' ?>">
                        <a href="<?= $is_quiz_completed ? '#' : 'take_quiz.php?id=' . $quiz['id'] ?>">
                            <?php if ($is_quiz_completed): ?><span class="badge completed-badge">✔ تکمیل شده</span><?php endif; ?>
                            <span class="tool-icon">📋</span>
                            <div>
                                <span class="tool-title"><?= htmlspecialchars($quiz['title']) ?></span>
                                <?php if (!empty($quiz['description'])): ?><p class="tool-description"><?= htmlspecialchars($quiz['description']) ?></p><?php endif; ?>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <hr class="section-divider">

        <h1 class="page-title">سناریوهای شما</h1>
        <p class="page-subtitle">سناریوهای چالشی که برای تیم شما تعریف شده است.</p>
        <ul class="tools-grid">
            <?php if (empty($scenarios)): ?>
                <p>در حال حاضر هیچ سناریویی برای تیم شما تعریف نشده است.</p>
            <?php else: ?>
                <?php
                foreach ($scenarios as $scenario):
                    $is_scenario_completed = in_array($scenario['id'], $completed_scenario_ids);
                    $is_active = (bool)$scenario['is_active'];

                    $card_class = '';
                    $link_href = '#';

                    if ($is_scenario_completed) {
                        $card_class = 'completed';
                    } elseif (!$is_active) {
                        $card_class = 'disabled';
                    } else {
                        $link_href = 'view_scenario.php?id=' . $scenario['id'];
                    }
                ?>
                    <li class="tool-card <?= $card_class ?>">
                        <a href="<?= $link_href ?>">
                            <?php if ($is_scenario_completed): ?>
                                <span class="badge completed-badge">✔ تکمیل شده</span>
                            <?php elseif (!$is_active): ?>
                                <span class="badge disabled-badge">غیرفعال</span>
                            <?php endif; ?>
                            <span class="tool-icon">🎯</span>
                            <div>
                                <span class="tool-title"><?= htmlspecialchars($scenario['title']) ?></span>
                                <?php if (!empty($scenario['description'])): ?><p class="tool-description"><?= htmlspecialchars($scenario['description']) ?></p><?php endif; ?>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </main>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js?v=1.0"></script>
</body>

</html>
