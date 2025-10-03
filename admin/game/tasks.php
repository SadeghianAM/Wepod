<?php
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');
require_once __DIR__ . '/../../db/database.php';

$stmt_tasks = $pdo->query("
    SELECT t.id, t.title, t.description, tm.team_name
    FROM Tasks t JOIN Teams tm ON t.team_id = tm.id
    ORDER BY t.id DESC
");
$tasks = $stmt_tasks->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>داشبورد مدیریت تکالیف</title>
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
            background: var(--bg-color);
            color: var(--text-color);
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
            justify-content: space-between;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 10;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
            min-height: var(--footer-h);
            font-size: .85rem
        }

        .page-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
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

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: .75rem 1.25rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: .95rem;
            font-weight: 600;
            transition: all .2s ease;
            position: relative;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .search-box input {
            width: 100%;
            padding: .75rem 1rem;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: .9rem;
            transition: all .2s ease;
        }

        .search-box input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
            outline: none;
        }

        .table-container {
            background-color: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .tasks-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
        }

        .tasks-table th,
        .tasks-table td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
        }

        .tasks-table thead {
            background-color: var(--bg-color);
        }

        .tasks-table th {
            font-weight: 600;
            color: var(--secondary-text);
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .tasks-table tbody tr {
            border-bottom: 1px solid var(--border-color);
        }

        .tasks-table tbody tr:last-child {
            border-bottom: none;
        }

        .tasks-table tbody tr:hover {
            background-color: var(--primary-light);
        }

        .task-description-cell {
            max-width: 400px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 500;
        }

        .team-badge {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            padding: .25rem .6rem;
            border-radius: 12px;
            font-size: .8rem;
            font-weight: 600;
            display: inline-block;
        }

        .actions-cell {
            text-align: left;
        }

        .actions-cell .btn-action {
            background: none;
            border: 1px solid transparent;
            cursor: pointer;
            padding: 0.5rem;
            margin: 0 0.2rem;
            border-radius: 6px;
            font-size: 1rem;
            line-height: 1;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #333;
        }

        .actions-cell .btn-action:hover {
            border-color: #ccc;
            background-color: var(--bg-color);
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

        #toast-container {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1200;
        }

        .toast {
            padding: 12px 20px;
            background-color: var(--primary-dark);
            color: white;
            border-radius: 8px;
            box-shadow: var(--shadow-md);
            margin-bottom: 10px;
            opacity: 0;
            transform: translateY(20px);
            animation: fade-in-out 4s forwards;
        }

        .toast.error {
            background-color: #c82333;
        }

        @keyframes fade-in-out {
            5% {
                opacity: 1;
                transform: translateY(0);
            }

            90% {
                opacity: 1;
                transform: translateY(0);
            }

            100% {
                opacity: 0;
                transform: translateY(20px);
            }
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="page-toolbar">
            <div>
                <h2 class="page-title" style="margin: 0;">مدیریت تکالیف</h2>
                <p class="page-subtitle">تکالیف را از اینجا مدیریت کنید.</p>
            </div>
            <div style="display: flex; gap: 1rem; align-items:center;">
                <div class="search-box">
                    <input type="text" id="task-search-input" placeholder="جستجوی تکلیف یا تیم...">
                </div>
                <a href="edit_task.php" class="btn btn-primary">➕ <span>تکلیف جدید</span></a>
            </div>
        </div>
        <?php if (empty($tasks)): ?>
            <div class="empty-state">
                <h2>هنوز هیچ تکلیفی نساخته‌اید! 🙁</h2>
                <p>برای شروع، اولین تکلیف خود را برای تیم‌ها ایجاد کنید.</p>
                <a href="edit_task.php" class="btn btn-primary">ایجاد اولین تکلیف</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="tasks-table">
                    <thead>
                        <tr>
                            <th>عنوان تکلیف</th>
                            <th>توضیحات</th>
                            <th>تیم</th>
                            <th class="actions-cell">عملیات</th>
                        </tr>
                    </thead>
                    <tbody id="tasks-tbody">
                        <?php foreach ($tasks as $task): ?>
                            <tr data-search-term="<?= htmlspecialchars(strtolower($task['title'] . ' ' . $task['team_name'])) ?>">
                                <td data-label="عنوان تکلیف" style="font-weight: 600;"><?= htmlspecialchars($task['title']) ?></td>
                                <td data-label="توضیحات" class="task-description-cell" title="<?= htmlspecialchars($task['description']) ?>"><?= htmlspecialchars($task['description']) ?></td>
                                <td data-label="تیم"><span class="team-badge"><?= htmlspecialchars($task['team_name']) ?></span></td>
                                <td data-label="عملیات" class="actions-cell">
                                    <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn-action" title="ویرایش">✏️</a>
                                    <button class="btn-action" onclick="deleteTask(<?= $task['id'] ?>)" title="حذف">🗑️</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    <div id="toast-container"></div>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js"></script>
    <script>
        async function deleteTask(id) {
            if (confirm('آیا از حذف این تکلیف مطمئن هستید؟ تمام پاسخ‌های کاربران نیز حذف خواهد شد.')) {
                const formData = new FormData();
                formData.append('action', 'delete_task');
                formData.append('id', id);

                const response = await fetch('tasks_api.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                showToast(result.message, result.success ? 'success' : 'error');
                if (result.success) {
                    setTimeout(() => window.location.reload(), 1000);
                }
            }
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('task-search-input');
            const tasksTbody = document.getElementById('tasks-tbody');
            if (searchInput && tasksTbody) {
                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = tasksTbody.querySelectorAll('tr');
                    rows.forEach(row => {
                        row.style.display = row.dataset.searchTerm.includes(searchTerm) ? '' : 'none';
                    });
                });
            }
        });
    </script>
</body>

</html>
