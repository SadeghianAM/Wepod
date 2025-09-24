<?php
// فایل: results.php (نسخه کاملاً بازطراحی شده)
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');
require_once __DIR__ . '/../../db/database.php';

$quiz_id_filter = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);
$page_title = "نتایج همه آزمون‌ها";
$quiz_title_for_header = '';

// دریافت لیست آزمون‌ها برای فیلتر
$stmt_quizzes = $pdo->query("SELECT id, title FROM Quizzes ORDER BY title");
$all_quizzes = $stmt_quizzes->fetchAll(PDO::FETCH_ASSOC);

// دریافت نتایج با اطلاعات کاربر و آزمون
$sql = "
    SELECT
        qa.id, qa.score, qa.start_time,
        u.name AS user_name,
        q.title AS quiz_title
    FROM QuizAttempts qa
    JOIN Users u ON qa.user_id = u.id
    JOIN Quizzes q ON qa.quiz_id = q.id
";

// اگر ID آزمون مشخص بود، نتایج را فیلتر می‌کنیم و عنوان صفحه را تغییر می‌دهیم
if ($quiz_id_filter) {
    $sql .= " WHERE qa.quiz_id = ?";
    $stmt = $pdo->prepare($sql . " ORDER BY qa.start_time DESC");
    $stmt->execute([$quiz_id_filter]);

    // پیدا کردن نام آزمون برای نمایش در عنوان
    foreach ($all_quizzes as $quiz) {
        if ($quiz['id'] == $quiz_id_filter) {
            $quiz_title_for_header = $quiz['title'];
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
        }

        footer {
            min-height: var(--footer-h);
            font-size: .85rem
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
        }

        /* Toolbar & Filters */
        .page-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .search-box {
            position: relative;
            width: 250px;
        }

        .search-box input,
        .filter-select {
            width: 100%;
            padding: .75rem 1rem;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: .9rem;
            transition: all .2s ease;
            background-color: #fff;
        }

        .search-box input:focus,
        .filter-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
            outline: none;
        }

        .filter-select {
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23555' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: left 0.75rem center;
            padding-left: 2rem;
            cursor: pointer;
        }

        /* Container & Table */
        .results-container {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            padding: 2rem;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
        }

        .results-table th,
        .results-table td {
            padding: 1rem;
            text-align: right;
            border-bottom: 1px solid var(--border-color);
        }

        .results-table th {
            font-weight: 600;
            color: var(--secondary-text);
            font-size: .9rem;
        }

        .results-table tbody tr {
            transition: background-color .2s ease;
        }

        .results-table tbody tr:hover {
            background-color: var(--primary-light);
        }

        .score-badge {
            padding: .25em .6em;
            font-weight: 600;
            border-radius: 6px;
            color: #fff;
            background-color: #6c757d;
            /* Default */
        }

        .score-badge.high-score {
            background-color: var(--primary-dark);
        }

        .score-badge.mid-score {
            background-color: #ffc107;
            color: #1a1a1a;
        }

        .score-badge.low-score {
            background-color: #dc3545;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background-color: var(--card-bg);
            border-radius: var(--radius);
            border: 2px dashed var(--border-color);
        }

        .empty-state h2 {
            margin-bottom: .5rem;
            font-weight: 700;
        }

        .empty-state p {
            margin-bottom: 1.5rem;
            color: var(--secondary-text);
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="page-toolbar">
            <div>
                <h2 class="page-title" style="margin: 0;"><?= $page_title ?></h2>
                <p class="page-subtitle">نتایج شرکت‌کنندگان را بررسی و تحلیل کنید.</p>
            </div>
            <div class="filter-controls">
                <div class="search-box">
                    <input type="text" id="results-search-input" placeholder="جستجو در نتایج...">
                </div>
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
                <p>هنوز هیچ کاربری در این آزمون شرکت نکرده است یا نتیجه‌ای مطابق با فیلتر شما وجود ندارد.</p>
            </div>
        <?php else: ?>
            <div class="results-container">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>کاربر</th>
                            <?php if (!$quiz_id_filter): ?>
                                <th>نام آزمون</th>
                            <?php endif; ?>
                            <th>نمره (از ۱۰۰)</th>
                            <th>تاریخ شرکت در آزمون</th>
                        </tr>
                    </thead>
                    <tbody id="results-tbody">
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?= htmlspecialchars($result['user_name']) ?></td>
                                <?php if (!$quiz_id_filter): ?>
                                    <td><?= htmlspecialchars($result['quiz_title']) ?></td>
                                <?php endif; ?>
                                <td>
                                    <?php
                                    $score = intval($result['score']);
                                    $score_class = 'mid-score';
                                    if ($score >= 80) $score_class = 'high-score';
                                    elseif ($score < 50) $score_class = 'low-score';
                                    ?>
                                    <span class="score-badge <?= $score_class ?>"><?= $score ?></span>
                                </td>
                                <td data-timestamp="<?= htmlspecialchars($result['start_time']) ?>">
                                    در حال بارگذاری...
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    <div id="footer-placeholder"></div>

    <script src="/js/header.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- Client-side Search ---
            const searchInput = document.getElementById('results-search-input');
            const tableBody = document.getElementById('results-tbody');
            if (searchInput && tableBody) {
                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase().trim();
                    const rows = tableBody.querySelectorAll('tr');
                    rows.forEach(row => {
                        const rowText = row.textContent.toLowerCase();
                        row.style.display = rowText.includes(searchTerm) ? '' : 'none';
                    });
                });
            }

            // --- Quiz Filter Dropdown ---
            const quizFilter = document.getElementById('quiz-filter');
            if (quizFilter) {
                quizFilter.addEventListener('change', (e) => {
                    const selectedQuizId = e.target.value;
                    if (selectedQuizId) {
                        window.location.href = `results.php?quiz_id=${selectedQuizId}`;
                    } else {
                        window.location.href = 'results.php';
                    }
                });
            }

            // --- Format Timestamps ---
            document.querySelectorAll('[data-timestamp]').forEach(cell => {
                const timestamp = cell.getAttribute('data-timestamp');
                if (timestamp) {
                    try {
                        const date = new Date(timestamp.replace(' ', 'T') + 'Z'); // Handle SQL format and treat as UTC
                        const options = {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        };
                        cell.textContent = date.toLocaleString('fa-IR', options);
                    } catch (e) {
                        cell.textContent = timestamp; // Fallback to original text on error
                    }
                }
            });
        });
    </script>
</body>

</html>
