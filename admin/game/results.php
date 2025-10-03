<?php
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');
require_once __DIR__ . '/../../db/database.php';

$quiz_id_filter = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);
$page_title = "نتایج همه آزمون‌ها";

$stmt_quizzes = $pdo->query("SELECT id, title FROM Quizzes ORDER BY title");
$all_quizzes = $stmt_quizzes->fetchAll(PDO::FETCH_ASSOC);

$sql = "
SELECT
    qa.id,
    qa.start_time,
    u.name AS user_name,
    q.id AS quiz_id,
    q.title AS quiz_title,
    COALESCE(attempt_scores.earned_points, 0) AS earned_points,
    COALESCE(quiz_max_points.max_points, 0) AS max_points
FROM
    QuizAttempts qa
JOIN
    Users u ON qa.user_id = u.id
JOIN
    Quizzes q ON qa.quiz_id = q.id
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

if ($quiz_id_filter) {
    $sql .= " WHERE qa.quiz_id = :quiz_id";
    $stmt = $pdo->prepare($sql . " ORDER BY qa.start_time DESC");
    $stmt->execute([':quiz_id' => $quiz_id_filter]);
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

function toPersianNumber($number)
{
    $persian_digits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($english_digits, $persian_digits, $number);
}
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
            --border-color: #e9e9e9;
            --radius: 12px;
            --shadow-sm: 0 2px 6px rgba(0, 120, 80, .06);
            --shadow-md: 0 6px 20px rgba(0, 120, 80, .10);
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --danger-light: #fbebec;
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
            max-width: 1500px;
            width: 100%;
            padding: clamp(1.5rem, 3vw, 2.5rem) clamp(1rem, 3vw, 2rem);
            margin-inline: auto;
        }

        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            min-height: 60px;
            font-size: .85rem;
        }

        .page-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .page-header h1 {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: clamp(1.5rem, 3vw, 2rem);
            margin-bottom: .5rem;
        }

        .page-header p {
            color: var(--secondary-text);
            font-size: clamp(.95rem, 2.2vw, 1rem);
        }

        .filter-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input,
        .filter-select {
            width: 280px;
            font-size: 1rem;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius);
            background: var(--card-bg);
            transition: border-color .2s, box-shadow .2s;
        }

        .search-input:focus-visible,
        .filter-select:focus-visible {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 174, 112, .15);
        }

        .results-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .result-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            transition: transform .2s ease, box-shadow .2s ease;
            overflow: hidden;
        }

        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .card-header {
            display: flex;
            flex-direction: column;
            gap: .75rem;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            background-color: var(--bg-color);
        }

        .header-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: var(--secondary-text);
            font-size: .9rem;
        }

        .header-item .icon {
            color: var(--primary-color);
            stroke-width: 2.2;
        }

        .header-item strong {
            font-weight: 700;
            color: var(--text-color);
            font-size: 1.05rem;
        }

        .card-body {
            padding: 1.5rem;
            flex: 1;
        }

        .card-score {
            margin-bottom: 1.5rem;
        }

        .score-text {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: .75rem;
            font-size: 0.95rem;
            color: var(--secondary-text);
        }

        .score-text .earned {
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--text-color);
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background-color: var(--border-color);
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 0.5s ease-in-out;
        }

        .progress-bar-fill.high-score {
            background-color: var(--success-color);
        }

        .progress-bar-fill.mid-score {
            background-color: var(--warning-color);
        }

        .progress-bar-fill.low-score {
            background-color: var(--danger-color);
        }

        .card-meta {
            font-size: 0.9rem;
            color: var(--secondary-text);
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .card-meta .icon {
            color: var(--secondary-text);
            stroke-width: 2;
        }

        .card-footer {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .icon {
            width: 1.1em;
            height: 1.1em;
            stroke-width: 2.2;
            vertical-align: -0.15em;
        }

        .btn {
            position: relative;
            padding: .8em 1.5em;
            font-size: .9rem;
            font-weight: 600;
            border: 1.5px solid transparent;
            border-radius: var(--radius);
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s, transform 0.2s, filter 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6em;
            white-space: nowrap;
            text-decoration: none;
        }

        .btn-outline-primary {
            background-color: transparent;
            border-color: var(--primary-color);
            color: var(--primary-dark);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            color: #fff;
        }

        .btn-outline-danger {
            background-color: transparent;
            border-color: var(--danger-color);
            color: var(--danger-color);
        }

        .btn-outline-danger:hover {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            color: #fff;
        }

        form.delete-form {
            display: inline;
        }

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

        #toast-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }

        .toast {
            padding: 12px 20px;
            border-radius: var(--radius);
            color: white;
            font-weight: 500;
            box-shadow: var(--shadow-md);
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.3s, transform 0.3s;
            min-width: 280px;
            text-align: center;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast-success {
            background-color: var(--success-color);
        }

        .toast-error {
            background-color: var(--danger-color);
        }

        .toast-info {
            background-color: var(--info-color);
        }

        .toast-confirm {
            background-color: var(--card-bg);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .toast-confirm .toast-message {
            margin-bottom: 1rem;
        }

        .toast-confirm .toast-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .toast-confirm .btn {
            font-size: 0.85rem;
            padding: 0.5em 1em;
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
                    if ($percentage >= 75) $score_class = 'high-score';
                    elseif ($percentage >= 40) $score_class = 'mid-score';
                    ?>
                    <div class="result-card" data-search-term="<?= htmlspecialchars(strtolower($result['user_name'] . ' ' . $result['quiz_title'])) ?>">
                        <div class="card-header">
                            <div class="header-item">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                                <span>کاربر:</span>
                                <strong><?= htmlspecialchars($result['user_name']) ?></strong>
                            </div>
                            <?php if (!$quiz_id_filter): ?>
                                <div class="header-item">
                                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="16" y1="13" x2="8" y2="13" />
                                        <line x1="16" y1="17" x2="8" y2="17" />
                                        <line x1="10" y1="9" x2="8" y2="9" />
                                    </svg>
                                    <span>آزمون:</span>
                                    <strong><?= htmlspecialchars($result['quiz_title']) ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <div class="card-score">
                                <div class="score-text">
                                    <span class="earned"><?= toPersianNumber($earned_points) ?> از <?= toPersianNumber($max_points) ?></span>
                                    <span>امتیاز</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-bar-fill <?= $score_class ?>" style="width: <?= $percentage ?>%;"></div>
                                </div>
                            </div>
                            <div class="card-meta" data-timestamp="<?= htmlspecialchars($result['start_time']) ?>">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                                    <line x1="16" x2="16" y1="2" y2="6" />
                                    <line x1="8" x2="8" y1="2" y2="6" />
                                    <line x1="3" x2="21" y1="10" y2="10" />
                                </svg>
                                <span class="date-text">در حال بارگذاری تاریخ...</span>
                            </div>
                        </div>

                        <div class="card-footer">
                            <form action="delete_attempt.php" method="POST" class="delete-form">
                                <input type="hidden" name="attempt_id" value="<?= $result['id'] ?>">
                                <input type="hidden" name="quiz_id" value="<?= $quiz_id_filter ?: '' ?>">
                                <button type="button" class="btn btn-outline-danger btn-delete">
                                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18" />
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                    </svg>
                                    <span>حذف</span>
                                </button>
                            </form>
                            <a href="view_attempt.php?id=<?= $result['id'] ?>" class="btn btn-outline-primary">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <span>مشاهده جزئیات</span>
                            </a>
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
            function showToast(message, type = 'success', duration = 4000) {
                const container = document.getElementById('toast-container');
                if (!container) return;
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.textContent = message;
                container.appendChild(toast);
                setTimeout(() => toast.classList.add('show'), 10);
                setTimeout(() => {
                    toast.classList.remove('show');
                    toast.addEventListener('transitionend', () => toast.remove());
                }, duration);
            }

            function showConfirmation(message, onConfirm) {
                const toastContainer = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = 'toast toast-confirm';
                toast.innerHTML = `
                    <div class="toast-message">${message}</div>
                    <div class="toast-buttons">
                        <button class="btn btn-danger" id="confirmAction">بله، حذف کن</button>
                        <button class="btn btn-secondary" id="cancelAction">لغو</button>
                    </div>`;
                const removeToast = () => {
                    toast.classList.remove('show');
                    toast.addEventListener('transitionend', () => toast.remove());
                };
                toast.querySelector('#confirmAction').onclick = () => {
                    onConfirm();
                    removeToast();
                };
                toast.querySelector('#cancelAction').onclick = removeToast;
                toastContainer.appendChild(toast);
                setTimeout(() => toast.classList.add('show'), 10);
            }

            const searchInput = document.getElementById('results-search-input');
            const resultsList = document.getElementById('results-list');
            if (searchInput && resultsList) {
                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase().trim();
                    resultsList.querySelectorAll('.result-card').forEach(card => {
                        card.style.display = card.dataset.searchTerm.toLowerCase().includes(searchTerm) ? '' : 'flex';
                    });
                });
            }

            const quizFilter = document.getElementById('quiz-filter');
            if (quizFilter) {
                quizFilter.addEventListener('change', (e) => {
                    const selectedQuizId = e.target.value;
                    window.location.href = selectedQuizId ? `results.php?quiz_id=${selectedQuizId}` : 'results.php';
                });
            }

            document.querySelectorAll('[data-timestamp]').forEach(cell => {
                const timestamp = cell.getAttribute('data-timestamp');
                const dateText = cell.querySelector('.date-text');
                if (timestamp && dateText) {
                    try {
                        const date = new Date(timestamp.replace(' ', 'T') + 'Z');
                        dateText.textContent = date.toLocaleString('fa-IR', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    } catch (e) {
                        dateText.textContent = timestamp;
                    }
                }
            });

            resultsList?.addEventListener('click', (e) => {
                const deleteButton = e.target.closest('.btn-delete');
                if (deleteButton) {
                    e.preventDefault();
                    showConfirmation('آیا از حذف این نتیجه مطمئن هستید؟ این عمل غیرقابل بازگشت است.', () => {
                        deleteButton.closest('form.delete-form').submit();
                    });
                }
            });
        });
    </script>
</body>

</html>
