<?php
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');
require_once __DIR__ . '/../../db/database.php';

// کوئری برای دریافت تکالیف و نام تیم مربوطه
$stmt_tasks = $pdo->query("
    SELECT
        t.id,
        t.title,
        t.description,
        tm.team_name
    FROM Tasks t
    JOIN Teams tm ON t.team_id = tm.id
    ORDER BY t.id DESC
");
$tasks = $stmt_tasks->fetchAll(PDO::FETCH_ASSOC);

// کوئری برای دریافت لیست تمام تیم‌ها برای استفاده در فرم
$stmt_teams = $pdo->query("SELECT id, team_name FROM Teams ORDER BY team_name");
$all_teams = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>داشبورد مدیریت تکالیف</title>
    <style>
        /* General Styles (from questions.php) */
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

        /* ⭐ Table Styles (from questions.php) ⭐ */
        .table-container {
            background-color: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            /* Important for border-radius */
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
        }

        .actions-cell .btn-action:hover {
            border-color: #ccc;
            background-color: var(--bg-color);
        }

        /* Responsive Table Styles */
        @media (max-width: 768px) {
            .tasks-table thead {
                display: none;
            }

            .tasks-table,
            .tasks-table tbody,
            .tasks-table tr,
            .tasks-table td {
                display: block;
                width: 100%;
            }

            .tasks-table tr {
                margin-bottom: 1rem;
                border: 1px solid var(--border-color);
                border-radius: var(--radius);
            }

            .tasks-table td {
                text-align: left;
                padding-left: 50%;
                position: relative;
                border-bottom: 1px solid var(--border-color);
            }

            .tasks-table tr td:last-child {
                border-bottom: none;
            }

            .tasks-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 1rem;
                width: 40%;
                padding-right: 1rem;
                text-align: right;
                font-weight: bold;
                color: var(--text-color);
            }

            .task-description-cell {
                white-space: normal;
                max-width: 100%;
            }
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
            margin-bottom: .5rem;
            font-weight: 700;
        }

        .empty-state p {
            margin-bottom: 1.5rem;
            color: var(--secondary-text);
        }

        /* Modal & Form Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1100;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity .3s, visibility .3s;
        }

        .modal-overlay.visible {
            opacity: 1;
            visibility: visible;
        }

        .modal-form {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            width: min(600px, 95%);
            transform: scale(0.95);
            transition: transform .3s;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-overlay.visible .modal-form {
            transform: scale(1);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: .5rem;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            background-color: #fff;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        /* Loading spinner for buttons */
        .btn.loading .btn-text {
            opacity: 0;
        }

        .btn .spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            opacity: 0;
            transition: opacity .2s ease;
            transform: translate(-50%, -50%);
        }

        .btn.loading .spinner {
            opacity: 1;
        }

        @keyframes spin {
            to {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        /* Toast Notification Styles */
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
                <p class="page-subtitle">تکالیف دومرحله‌ای با بازبینی دستی را از اینجا مدیریت کنید.</p>
            </div>
            <div style="display: flex; gap: 1rem; align-items:center;">
                <div class="search-box">
                    <input type="text" id="task-search-input" placeholder="جستجوی تکلیف یا تیم...">
                </div>
                <button id="add-new-task-btn" class="btn btn-primary">➕ <span>تکلیف جدید</span></button>
            </div>
        </div>

        <?php if (empty($tasks)): ?>
            <div class="empty-state">
                <h2>هنوز هیچ تکلیفی نساخته‌اید! 🙁</h2>
                <p>برای شروع، اولین تکلیف خود را برای تیم‌ها ایجاد کنید.</p>
                <button id="add-new-task-btn-empty" class="btn btn-primary">ایجاد اولین تکلیف</button>
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
                                <td data-label="عنوان تکلیف" style="font-weight: 600;">
                                    <?= htmlspecialchars($task['title']) ?>
                                </td>
                                <td data-label="توضیحات" class="task-description-cell" title="<?= htmlspecialchars($task['description']) ?>">
                                    <?= htmlspecialchars($task['description']) ?>
                                </td>
                                <td data-label="تیم">
                                    <span class="team-badge"><?= htmlspecialchars($task['team_name']) ?></span>
                                </td>
                                <td data-label="عملیات" class="actions-cell">
                                    <button class="btn-action" onclick="editTask(<?= $task['id'] ?>)" title="ویرایش">✏️</button>
                                    <button class="btn-action" onclick="deleteTask(<?= $task['id'] ?>)" title="حذف">🗑️</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <div id="modal-overlay" class="modal-overlay">
        <div id="modal-form" class="modal-form">
            <h2 id="form-title" class="page-title">افزودن تکلیف جدید</h2>
            <form id="task-form">
                <input type="hidden" id="task-id">
                <input type="hidden" id="action">

                <div class="form-group">
                    <label for="task-title">عنوان تکلیف:</label>
                    <input type="text" id="task-title" required>
                </div>

                <div class="form-group">
                    <label for="task-description">توضیحات:</label>
                    <textarea id="task-description" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="team-id">تخصیص به تیم:</label>
                    <select id="team-id" required>
                        <option value="" disabled selected>یک تیم را انتخاب کنید...</option>
                        <?php foreach ($all_teams as $team): ?>
                            <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['team_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <hr style="margin: 1.5rem 0; border-color: var(--border-color); border-style: solid;">

                <h3>سوالات تکلیف (دو مرحله‌ای)</h3>
                <div class="form-group">
                    <label for="question1-text">متن سوال اول:</label>
                    <textarea id="question1-text" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="question2-text">متن سوال دوم:</label>
                    <textarea id="question2-text" rows="3" required></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" id="cancel-btn" class="btn btn-secondary">انصراف</button>
                    <button type="submit" id="save-btn" class="btn btn-primary">
                        <span class="btn-text">ذخیره</span>
                        <span class="spinner"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast-container"></div>
    <div id="footer-placeholder"></div>

    <script src="/js/header.js"></script>
    <script>
        // Global functions for action buttons
        function editTask(id) {
            document.dispatchEvent(new CustomEvent('openEditModal', {
                detail: {
                    id
                }
            }));
        }

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

        // Reusable showToast function
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const modalOverlay = document.getElementById('modal-overlay');
            const form = document.getElementById('task-form');
            const formTitle = document.getElementById('form-title');
            const saveBtn = document.getElementById('save-btn');

            const showModal = () => modalOverlay.classList.add('visible');
            const hideModal = () => modalOverlay.classList.remove('visible');
            const toggleLoading = (button, isLoading) => {
                button.disabled = isLoading;
                button.classList.toggle('loading', isLoading);
            };

            const openAddModal = () => {
                form.reset();
                formTitle.textContent = 'افزودن تکلیف جدید';
                document.getElementById('task-id').value = '';
                document.getElementById('action').value = 'create_task';
                showModal();
            };

            document.getElementById('add-new-task-btn')?.addEventListener('click', openAddModal);
            document.getElementById('add-new-task-btn-empty')?.addEventListener('click', openAddModal);

            document.addEventListener('openEditModal', async (e) => {
                const {
                    id
                } = e.detail;
                const response = await fetch(`tasks_api.php?action=get_task&id=${id}`);
                const data = await response.json();

                if (data.success) {
                    const task = data.task;
                    form.reset();
                    formTitle.textContent = 'ویرایش تکلیف';
                    document.getElementById('task-id').value = task.id;
                    document.getElementById('action').value = 'update_task';
                    document.getElementById('task-title').value = task.title;
                    document.getElementById('task-description').value = task.description;
                    document.getElementById('team-id').value = task.team_id;
                    document.getElementById('question1-text').value = task.questions[0]?.question_text || '';
                    document.getElementById('question2-text').value = task.questions[1]?.question_text || '';
                    showModal();
                } else {
                    showToast(data.message || 'خطا در دریافت اطلاعات', 'error');
                }
            });

            document.getElementById('cancel-btn').addEventListener('click', hideModal);
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) hideModal();
            });

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                toggleLoading(saveBtn, true);

                const data = {
                    id: document.getElementById('task-id').value,
                    title: document.getElementById('task-title').value,
                    description: document.getElementById('task-description').value,
                    team_id: document.getElementById('team-id').value,
                    question1: document.getElementById('question1-text').value,
                    question2: document.getElementById('question2-text').value,
                };
                const action = document.getElementById('action').value;

                const response = await fetch(`tasks_api.php?action=${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    hideModal();
                    showToast('عملیات با موفقیت انجام شد.');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(result.message || 'خطایی رخ داد.', 'error');
                }
                toggleLoading(saveBtn, false);
            });

            // --- Dashboard Search (for Table) ---
            const searchInput = document.getElementById('task-search-input');
            const tasksTbody = document.getElementById('tasks-tbody');
            if (searchInput && tasksTbody) {
                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = tasksTbody.querySelectorAll('tr');
                    rows.forEach(row => {
                        const display = row.dataset.searchTerm.includes(searchTerm) ? '' : 'none';
                        row.style.display = display;
                    });
                });
            }
        });
    </script>
</body>

</html>
