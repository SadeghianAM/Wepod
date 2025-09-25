<?php
// فایل: results.php (نسخه بازطراحی شده)
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');
require_once __DIR__ . '/../../db/database.php';

$quiz_id_filter = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);
$page_title = "نتایج همه آزمون‌ها";

// دریافت لیست آزمون‌ها برای فیلتر
$stmt_quizzes = $pdo->query("SELECT id, title FROM Quizzes ORDER BY title");
$all_quizzes = $stmt_quizzes->fetchAll(PDO::FETCH_ASSOC);

// ====================================================================
// بهینه‌سازی کوئری SQL:
// به جای استفاده از ساب‌کوئری‌های تو در تو (که برای هر سطر اجرا می‌شوند)،
// از JOIN و GROUP BY برای محاسبه امتیازات به صورت بهینه‌تر استفاده می‌کنیم.
// این کار عملکرد را به شدت بهبود می‌بخشد.
// ====================================================================
$sql = "
SELECT
    qa.id,
    qa.start_time,
    u.name AS user_name,
    q.id AS quiz_id,
    q.title AS quiz_title,
    -- محاسبه امتیاز کسب شده با یک subquery بهینه‌تر
    COALESCE(attempt_scores.earned_points, 0) AS earned_points,
    -- محاسبه حداکثر امتیاز آزمون
    COALESCE(quiz_max_points.max_points, 0) AS max_points
FROM
    QuizAttempts qa
JOIN
    Users u ON qa.user_id = u.id
JOIN
    Quizzes q ON qa.quiz_id = q.id
-- بخش محاسبه امتیاز کسب شده برای هر تلاش (attempt)
LEFT JOIN (
    SELECT
        ua.attempt_id,
        SUM(CASE WHEN a.is_correct = 1 THEN qu.points_correct ELSE qu.points_incorrect END) as earned_points
    FROM
        UserAnswers ua
    JOIN
        Answers a ON ua.selected_answer_id = a.id
    JOIN
        Questions qu ON ua.question_id = qu.id
    GROUP BY
        ua.attempt_id
) AS attempt_scores ON qa.id = attempt_scores.attempt_id
-- بخش محاسبه سقف امتیاز برای هر آزمون
LEFT JOIN (
    SELECT
        qq.quiz_id,
        SUM(qu.points_correct) as max_points
    FROM
        QuizQuestions qq
    JOIN
        Questions qu ON qq.question_id = qu.id
    GROUP BY
        qq.quiz_id
) AS quiz_max_points ON q.id = quiz_max_points.quiz_id
";

// اعمال فیلتر در صورت وجود
if ($quiz_id_filter) {
    $sql .= " WHERE qa.quiz_id = :quiz_id";
    $stmt = $pdo->prepare($sql . " ORDER BY qa.start_time DESC");
    $stmt->execute([':quiz_id' => $quiz_id_filter]);

    // پیدا کردن عنوان آزمون برای نمایش در بالای صفحه
    foreach ($all_quizzes as $quiz) {
        if ($quiz['id'] == $quiz_id_filter) {
            $page_title = "نتایج آزمون: " . htmlspecialchars($quiz['title']);
            break;
        }
    }
} else {
    $stmt = $pdo->query($sql . " ORDER BY qa.start_time DESC");
}
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $page_title ?></title>
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
            --footer-h: 60px;
            --border-color: #e9e9e9;
            --radius: 12px;
            --shadow-sm: 0 2px 6px rgba(0, 120, 80, .06);
            --shadow-md: 0 6px 20px rgba(0, 120, 80, .10);
            --score-high: #00ae70;
            --score-mid: #ffc107;
            --score-low: #dc3545;
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
            background: var(--bg-color);
            color: var(--text-color);
        }

        a {
            color: inherit;
            text-decoration: none;
            transition: all .2s ease;
        }

        main {
            flex: 1;
            width: min(1200px, 100%);
            padding: 2.5rem 2rem;
            margin-inline: auto;
        }

        /* استایل‌های فوتر (بدون تغییر) */
        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 10;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
            min-height: var(--footer-h);
            font-size: .85rem
        }

        /* --- Toolbar & Filters --- */
        .page-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            /* تغییر برای چینش بهتر */
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .page-header h1 {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: .25rem;
        }

        .page-header p {
            color: var(--secondary-text);
            font-size: 1rem;
        }

        .filter-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input,
        .filter-select {
            width: 250px;
            padding: .75rem 1rem;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: .9rem;
            transition: all .2s ease;
            background-color: var(--card-bg);
        }

        .search-input:focus,
        .filter-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
            outline: none;
        }

        /* --- Results List & Cards (طراحی جدید) --- */
        .results-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .result-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .card-header {
            display: flex;
            flex-direction: column;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .card-header .user-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-color);
        }

        .card-header .quiz-title {
            font-size: 0.9rem;
            color: var(--secondary-text);
        }

        .card-body {
            flex: 1;
        }

        .card-score {
            margin-bottom: 1rem;
        }

        .score-text {
            display: flex;
            justify-content: space-between;
            margin-bottom: .5rem;
            font-size: 0.9rem;
            color: var(--secondary-text);
        }

        .score-text .earned {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--text-color);
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background-color: var(--bg-color);
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 0.5s ease-in-out;
        }

        .progress-bar-fill.high-score {
            background-color: var(--score-high);
        }

        .progress-bar-fill.mid-score {
            background-color: var(--score-mid);
        }

        .progress-bar-fill.low-score {
            background-color: var(--score-low);
        }


        .card-meta {
            font-size: 0.85rem;
            color: var(--secondary-text);
        }

        .card-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .action-btn {
            padding: .5rem 1rem;
            font-size: .85rem;
            font-weight: 600;
            border-radius: 8px;
            border: 1.5px solid transparent;
            cursor: pointer;
            transition: all .2s;
        }

        .action-btn.view {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            border-color: var(--primary-color);
        }

        .action-btn.view:hover {
            background-color: var(--primary-dark);
            color: #fff;
        }

        .action-btn.delete {
            background-color: #fff1f2;
            color: var(--score-low);
            border-color: #ffdde0;
        }

        .action-btn.delete:hover {
            background-color: var(--score-low);
            color: #fff;
        }

        form.delete-form {
            display: inline;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background-color: var(--card-bg);
            border-radius: var(--radius);
            border: 2px dashed var(--border-color);
        }

        .empty-state h2 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--secondary-text);
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="page-toolbar">
            <div class="page-header">
                <h1><?= $page_title ?></h1>
                <p>نتایج شرکت‌کنندگان را بررسی و تحلیل کنید.</p>
            </div>
            <div class="filter-controls">
                <input type="text" id="results-search-input" class="search-input" placeholder="جستجوی کاربر یا آزمون...">
                <select id="quiz-filter" class="filter-select">
                    <option value="">همه آزمون‌ها</option>
                    <?php foreach ($all_quizzes as $quiz): ?>
                        <option value="<?= $quiz['id'] ?>" <?= ($quiz_id_filter == $quiz['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($quiz['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (empty($results)): ?>
            <div class="empty-state">
                <h2>هیچ نتیجه‌ای یافت نشد!</h2>
                <p>هنوز هیچ کاربری در این آزمون شرکت نکرده است یا نتیجه‌ای با این فیلتر وجود ندارد.</p>
            </div>
        <?php else: ?>
            <div class="results-list" id="results-list">
                <?php foreach ($results as $result): ?>
                    <?php
                    $earned_points = round($result['earned_points'] ?? 0, 2);
                    $max_points = round($result['max_points'] ?? 0, 2);
                    $percentage = ($max_points > 0) ? ($earned_points / $max_points) * 100 : 0;

                    $score_class = 'low-score';
                    if ($percentage >= 75) {
                        $score_class = 'high-score';
                    } elseif ($percentage >= 40) {
                        $score_class = 'mid-score';
                    }
                    ?>
                    <div class="result-card">
                        <div class="card-header">
                            <span class="user-name"><?= htmlspecialchars($result['user_name']) ?></span>
                            <?php if (!$quiz_id_filter): ?>
                                <span class="quiz-title"><?= htmlspecialchars($result['quiz_title']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <div class="card-score">
                                <div class="score-text">
                                    <span class="earned"><?= $earned_points ?> / <?= $max_points ?></span>
                                    <span>امتیاز</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-bar-fill <?= $score_class ?>" style="width: <?= $percentage ?>%;"></div>
                                </div>
                            </div>
                            <div class="card-meta" data-timestamp="<?= htmlspecialchars($result['start_time']) ?>">
                                در حال بارگذاری تاریخ...
                            </div>
                        </div>

                        <div class="card-footer">
                            <a href="view_attempt.php?id=<?= $result['id'] ?>" class="action-btn view">مشاهده جزئیات</a>
                            <form action="delete_attempt.php" method="POST" class="delete-form">
                                <input type="hidden" name="attempt_id" value="<?= $result['id'] ?>">
                                <input type="hidden" name="quiz_id" value="<?= $quiz_id_filter ?: '' ?>">
                                <button type="submit" class="action-btn delete" onclick="return confirm('آیا از حذف این نتیجه مطمئن هستید؟');">حذف</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <div id="footer-placeholder"></div>

    <script src="/js/header.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- Live Search Functionality ---
            const searchInput = document.getElementById('results-search-input');
            const resultsList = document.getElementById('results-list');
            if (searchInput && resultsList) {
                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase().trim();
                    resultsList.querySelectorAll('.result-card').forEach(card => {
                        card.style.display = card.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
                    });
                });
            }

            // --- Quiz Filter Functionality ---
            const quizFilter = document.getElementById('quiz-filter');
            if (quizFilter) {
                quizFilter.addEventListener('change', (e) => {
                    const selectedQuizId = e.target.value;
                    window.location.href = selectedQuizId ? `results.php?quiz_id=${selectedQuizId}` : 'results.php';
                });
            }

            // --- Date Formatting ---
            document.querySelectorAll('[data-timestamp]').forEach(cell => {
                const timestamp = cell.getAttribute('data-timestamp');
                if (timestamp) {
                    try {
                        // افزودن 'Z' برای مشخص کردن اینکه زمان UTC است تا از خطاهای منطقه زمانی جلوگیری شود
                        const date = new Date(timestamp.replace(' ', 'T') + 'Z');
                        cell.textContent = 'تاریخ: ' + date.toLocaleString('fa-IR', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    } catch (e) {
                        cell.textContent = `تاریخ: ${timestamp}`; // Fallback
                    }
                }
            });
        });
    </script>
</body>

</html>
