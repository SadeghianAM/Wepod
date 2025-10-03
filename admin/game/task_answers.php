<?php
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');
require_once __DIR__ . '/../../db/database.php';

$task_id_filter = filter_input(INPUT_GET, 'task_id', FILTER_VALIDATE_INT);
$page_title = "پاسخ همه تکالیف";

$stmt_tasks = $pdo->query("SELECT id, title FROM Tasks ORDER BY title");
$all_tasks = $stmt_tasks->fetchAll(PDO::FETCH_ASSOC);

$sql = "
SELECT
    ta.id,
    ta.status,
    ta.submitted_at,
    u.name AS user_name,
    t.id AS task_id,
    t.title AS task_title
FROM
    TaskAnswers ta
JOIN
    Users u ON ta.user_id = u.id
JOIN
    TaskQuestions tq ON ta.task_question_id = tq.id
JOIN
    Tasks t ON tq.task_id = t.id
";

if ($task_id_filter) {
    $sql .= " WHERE t.id = :task_id";
    $stmt = $pdo->prepare($sql . " ORDER BY ta.submitted_at DESC");
    $stmt->execute([':task_id' => $task_id_filter]);

    foreach ($all_tasks as $task) {
        if ($task['id'] == $task_id_filter) {
            $page_title = "پاسخ‌های تکلیف: " . htmlspecialchars($task['title']);
            break;
        }
    }
} else {
    $stmt = $pdo->query($sql . " ORDER BY ta.submitted_at DESC");
}
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            --success-light: #e9f7eb;
            --warning-light: #fff8e7;
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
            min-height: 60px;
            font-size: .85rem;
            flex-shrink: 0;
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
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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

        .status-badge {
            padding: .4em .9em;
            font-size: .85rem;
            font-weight: 600;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: .5em;
            margin-bottom: 1rem;
        }

        .status-badge .icon {
            width: 1.2em;
            height: 1.2em;
        }

        .status-approved {
            background-color: var(--success-light);
            color: var(--success-color);
        }

        .status-rejected {
            background-color: var(--danger-light);
            color: var(--danger-color);
        }

        .status-submitted {
            background-color: var(--warning-light);
            color: #a17400;
        }

        .card-footer {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
            margin-top: 1.5rem;
        }

        .icon {
            width: 1.1em;
            height: 1.1em;
            stroke-width: 2.2;
            vertical-align: -0.15em;
        }

        .btn {
            padding: .8em 1.5em;
            font-size: .9rem;
            font-weight: 600;
            border: 1.5px solid transparent;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6em;
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
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="page-toolbar">
            <div class="page-header">
                <h1><?= $page_title ?></h1>
                <p>پاسخ‌های ارسالی کاربران به تکالیف را مدیریت کنید.</p>
            </div>
            <div class="filter-controls">
                <input type="text" id="results-search-input" class="search-input" placeholder="جستجوی کاربر یا تکلیف...">
                <select id="task-filter" class="filter-select">
                    <option value="">همه تکالیف</option>
                    <?php foreach ($all_tasks as $task): ?>
                        <option value="<?= $task['id'] ?>" <?= ($task_id_filter == $task['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($task['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (empty($answers)): ?>
            <div class="empty-state">
                <h2>هیچ پاسخی یافت نشد!</h2>
                <p>هنوز پاسخی ثبت نشده یا نتیجه‌ای با این فیلتر وجود ندارد.</p>
            </div>
        <?php else: ?>
            <div class="results-list" id="results-list">
                <?php foreach ($answers as $answer): ?>
                    <?php
                    $status_map = [
                        'submitted' => ['text' => 'در انتظار بازبینی', 'class' => 'status-submitted', 'icon' => '<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>'],
                        'approved' => ['text' => 'تایید شده', 'class' => 'status-approved', 'icon' => '<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'],
                        'rejected' => ['text' => 'رد شده', 'class' => 'status-rejected', 'icon' => '<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>']
                    ];
                    $status_info = $status_map[$answer['status']] ?? ['text' => $answer['status'], 'class' => '', 'icon' => ''];
                    ?>
                    <div class="result-card" data-search-term="<?= htmlspecialchars(strtolower($answer['user_name'] . ' ' . $answer['task_title'])) ?>">
                        <div class="card-header">
                            <div class="header-item">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                                <span>کاربر:</span>
                                <strong><?= htmlspecialchars($answer['user_name']) ?></strong>
                            </div>
                            <div class="header-item">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="16" y1="13" x2="8" y2="13" />
                                    <line x1="16" y1="17" x2="8" y2="17" />
                                </svg>
                                <span>تکلیف:</span>
                                <strong><?= htmlspecialchars($answer['task_title']) ?></strong>
                            </div>
                        </div>
                        <div class="card-body">
                            <div>
                                <span class="status-badge <?= $status_info['class'] ?>"><?= $status_info['icon'] ?><span><?= $status_info['text'] ?></span></span>
                            </div>
                            <div class="card-meta" data-timestamp="<?= htmlspecialchars($answer['submitted_at']) ?>">
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
                            <a href="view_task_answer.php?id=<?= $answer['id'] ?>" class="btn btn-outline-primary">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <span>مشاهده و بازبینی</span>
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

            const taskFilter = document.getElementById('task-filter');
            if (taskFilter) {
                taskFilter.addEventListener('change', (e) => {
                    const selectedTaskId = e.target.value;
                    window.location.href = selectedTaskId ? `task_answers.php?task_id=${selectedTaskId}` : 'task_answers.php';
                });
            }

            document.querySelectorAll('[data-timestamp]').forEach(cell => {
                const timestamp = cell.getAttribute('data-timestamp');
                const dateText = cell.querySelector('.date-text');
                if (timestamp && dateText) {
                    try {
                        const date = new Date(timestamp.replace(' ', 'T') + 'Z');
                        dateText.textContent = 'ارسال: ' + date.toLocaleString('fa-IR', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    } catch (e) {
                        dateText.textContent = `تاریخ: ${timestamp}`;
                    }
                }
            });
        });
    </script>
</body>

</html>
