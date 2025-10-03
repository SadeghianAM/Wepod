<?php
// فایل: /admin/tasks/task_answers.php
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');
require_once __DIR__ . '/../../db/database.php';

$task_id_filter = filter_input(INPUT_GET, 'task_id', FILTER_VALIDATE_INT);
$page_title = "پاسخ همه تکالیف";

// دریافت لیست تکالیف برای منوی فیلتر
$stmt_tasks = $pdo->query("SELECT id, title FROM Tasks ORDER BY title");
$all_tasks = $stmt_tasks->fetchAll(PDO::FETCH_ASSOC);

// کوئری اصلی برای دریافت همه پاسخ‌ها
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

// اعمال فیلتر در صورت انتخاب یک تکلیف خاص
if ($task_id_filter) {
    $sql .= " WHERE t.id = :task_id";
    $stmt = $pdo->prepare($sql . " ORDER BY ta.submitted_at DESC");
    $stmt->execute([':task_id' => $task_id_filter]);

    // پیدا کردن عنوان تکلیف برای نمایش در بالای صفحه
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
        /* CSS styles are copied from results.php for consistency */
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
            --status-approved: #28a745;
            --status-rejected: #dc3545;
            --status-submitted: #ffc107;
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

        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: var(--footer-h);
            font-size: .85rem;
        }

        .page-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
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
            background-color: var(--card-bg);
        }

        .search-input:focus,
        .filter-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
            outline: none;
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
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
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
            gap: .25rem;
        }

        .card-header .user-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-color);
        }

        .card-header .task-title {
            font-size: 0.9rem;
            color: var(--secondary-text);
        }

        .card-body {
            flex: 1;
            padding-top: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-meta {
            font-size: 0.85rem;
            color: var(--secondary-text);
        }

        .status-badge {
            padding: .25em .8em;
            font-size: .8rem;
            font-weight: 700;
            border-radius: 15px;
            color: white;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .status-approved {
            background-color: var(--status-approved);
        }

        .status-rejected {
            background-color: var(--status-rejected);
        }

        .status-submitted {
            background-color: var(--status-submitted);
            color: #333;
        }

        .card-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
            margin-top: 1rem;
        }

        .action-btn {
            padding: .5rem 1rem;
            font-size: .85rem;
            font-weight: 600;
            border-radius: 8px;
            border: 1.5px solid transparent;
            cursor: pointer;
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

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background-color: var(--card-bg);
            border-radius: var(--radius);
            border: 2px dashed var(--border-color);
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
                        'submitted' => ['text' => 'در انتظار بازبینی', 'class' => 'status-submitted'],
                        'approved' => ['text' => 'تایید شده', 'class' => 'status-approved'],
                        'rejected' => ['text' => 'رد شده', 'class' => 'status-rejected']
                    ];
                    $status_info = $status_map[$answer['status']] ?? ['text' => $answer['status'], 'class' => ''];
                    ?>
                    <div class="result-card">
                        <div class="card-header">
                            <span class="user-name"><?= htmlspecialchars($answer['user_name']) ?></span>
                            <span class="task-title"><?= htmlspecialchars($answer['task_title']) ?></span>
                        </div>

                        <div class="card-body">
                            <div>
                                <span class="status-badge <?= $status_info['class'] ?>"><?= $status_info['text'] ?></span>
                            </div>
                            <div class="card-meta" data-timestamp="<?= htmlspecialchars($answer['submitted_at']) ?>">
                                در حال بارگذاری تاریخ...
                            </div>
                        </div>

                        <div class="card-footer">
                            <a href="view_task_answer.php?id=<?= $answer['id'] ?>" class="action-btn view">مشاهده و بازبینی</a>
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
            // --- Live Search ---
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

            // --- Task Filter ---
            const taskFilter = document.getElementById('task-filter');
            if (taskFilter) {
                taskFilter.addEventListener('change', (e) => {
                    const selectedTaskId = e.target.value;
                    window.location.href = selectedTaskId ? `task_answers.php?task_id=${selectedTaskId}` : 'task_answers.php';
                });
            }

            // --- Date Formatting ---
            document.querySelectorAll('[data-timestamp]').forEach(cell => {
                const timestamp = cell.getAttribute('data-timestamp');
                if (timestamp) {
                    try {
                        const date = new Date(timestamp.replace(' ', 'T') + 'Z'); // Treat as UTC
                        cell.textContent = 'ارسال: ' + date.toLocaleString('fa-IR', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    } catch (e) {
                        cell.textContent = `تاریخ: ${timestamp}`;
                    }
                }
            });
        });
    </script>
</body>

</html>
