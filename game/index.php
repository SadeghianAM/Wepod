<?php
// فایل: quiz_list.php (نسخه نهایی با جلوگیری از شرکت مجدد)

// احراز هویت کاربر
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');

require_once __DIR__ . '/../db/database.php';

// دریافت شناسه کاربر لاگین کرده
$user_id = $claims['sub'];

// پیدا کردن تیمی که کاربر عضو آن است
$stmt_team = $pdo->prepare("SELECT team_id FROM TeamMembers WHERE user_id = ?");
$stmt_team->execute([$user_id]);
$team_id = $stmt_team->fetchColumn(); // اگر کاربر عضو تیمی نباشد، این مقدار false خواهد بود

// ⭐ بخش جدید: پیدا کردن تمام آزمون‌هایی که کاربر قبلاً در آن‌ها شرکت کرده است
$stmt_completed = $pdo->prepare("SELECT DISTINCT quiz_id FROM QuizAttempts WHERE user_id = ?");
$stmt_completed->execute([$user_id]);
$completed_quiz_ids = $stmt_completed->fetchAll(PDO::FETCH_COLUMN);

// کوئری هوشمند برای دریافت آزمون‌های مجاز برای کاربر
$sql = "
    SELECT DISTINCT q.id, q.title, q.description
    FROM Quizzes q
    LEFT JOIN QuizUserAssignments qua ON q.id = qua.quiz_id
    LEFT JOIN QuizTeamAssignments qta ON q.id = qta.quiz_id
    WHERE
        -- شرط ۱: آزمون مستقیماً به کاربر تخصیص داده شده باشد
        qua.user_id = :user_id
        -- شرط ۲: آزمون به تیم کاربر تخصیص داده شده باشد
        OR qta.team_id = :team_id
        -- شرط ۳: آزمون عمومی باشد (به هیچکس تخصیص داده نشده باشد)
        OR (
            NOT EXISTS (SELECT 1 FROM QuizUserAssignments WHERE quiz_id = q.id) AND
            NOT EXISTS (SELECT 1 FROM QuizTeamAssignments WHERE quiz_id = q.id)
        )
    ORDER BY q.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':user_id' => $user_id,
    ':team_id' => $team_id ?: null // اگر کاربر تیمی نداشت، null ارسال می‌شود
]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>لیست آزمون‌ها</title>
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

        header,
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
        }

        header {
            min-height: 70px;
        }

        footer {
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

        .tools-grid {
            list-style: none;
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }

        .tool-card a {
            position: relative;
            /* برای جای‌گیری badge */
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: .75rem;
            padding: 1.75rem;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            will-change: transform;
        }

        .tool-card a:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
            color: var(--primary-dark);
        }

        /* ⭐ استایل‌های جدید برای آزمون‌های تکمیل شده */
        .tool-card.completed a {
            background-color: #f1f3f5;
            /* رنگ پس‌زمینه متفاوت */
            cursor: not-allowed;
            /* تغییر نشانگر موس */
            color: #868e96;
            border-color: var(--border-color);
        }

        .tool-card.completed a:hover {
            transform: none;
            box-shadow: var(--shadow-sm);
        }

        .completed-badge {
            font-size: 0.8rem;
            font-weight: bold;
            color: var(--primary-dark);
            background-color: var(--primary-light);
            padding: 5px 10px;
            border-radius: 12px;
            position: absolute;
            top: 1.25rem;
            left: 1.25rem;
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

        @media (max-width: 768px) {
            main {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>

    <main>
        <h1 class="page-title">انتخاب آزمون</h1>
        <p class="page-subtitle">برای شروع، یکی از آزمون‌های در دسترس خود را انتخاب کنید.</p>

        <ul class="tools-grid">
            <?php if (empty($quizzes)): ?>
                <p>در حال حاضر هیچ آزمونی برای شما تعریف نشده است.</p>
            <?php else: ?>
                <?php foreach ($quizzes as $quiz): ?>
                    <?php
                    // ⭐ بررسی اینکه آیا آزمون فعلی در لیست آزمون‌های تکمیل شده کاربر است یا خیر
                    $is_completed = in_array($quiz['id'], $completed_quiz_ids);
                    ?>
                    <li class="tool-card <?= $is_completed ? 'completed' : '' ?>">
                        <a href="<?= $is_completed ? '#' : 'take_quiz.php?id=' . $quiz['id'] ?>">
                            <?php if ($is_completed): ?>
                                <span class="completed-badge">✔ تکمیل شده</span>
                            <?php endif; ?>
                            <span class="tool-icon">📋</span>
                            <div>
                                <span class="tool-title"><?= htmlspecialchars($quiz['title']) ?></span>
                                <?php if (!empty($quiz['description'])): ?>
                                    <p class="tool-description"><?= htmlspecialchars($quiz['description']) ?></p>
                                <?php endif; ?>
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
