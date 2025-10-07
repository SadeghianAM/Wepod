<?php
require_once __DIR__ . '/../../../auth/require-auth.php';
$claims = requireAuth('admin', '/../../auth/login.html');
require_once __DIR__ . '/../../../db/database.php';

$stmt_teams = $pdo->query("
    SELECT
        t.id,
        t.team_name,
        COUNT(tm.user_id) AS member_count,
        GROUP_CONCAT(u.name, '||') AS member_names
    FROM Teams t
    LEFT JOIN TeamMembers tm ON t.id = tm.team_id
    LEFT JOIN Users u ON tm.user_id = u.id
    GROUP BY t.id, t.team_name
    ORDER BY t.id DESC
");
$teams = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);

$stmt_users = $pdo->query("SELECT id, name FROM Users ORDER BY name");
$all_users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

$stmt_unassigned_users = $pdo->query("
    SELECT u.id, u.name
    FROM Users u
    LEFT JOIN TeamMembers tm ON u.id = tm.user_id
    WHERE tm.team_id IS NULL
    ORDER BY u.name
");
$unassigned_users = $stmt_unassigned_users->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>داشبورد مدیریت تیم‌ها</title>
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
            align-items: center;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: clamp(1.5rem, 3vw, 2rem);
            margin-block-end: .5rem;
        }

        .page-subtitle {
            color: var(--secondary-text);
            font-weight: 400;
            font-size: clamp(.95rem, 2.2vw, 1rem);
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
            font-size: .95rem;
            font-weight: 600;
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s, filter 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6em;
            white-space: nowrap;
        }

        .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            filter: brightness(0.92);
        }

        .btn:disabled,
        .btn.loading {
            background-color: var(--border-color);
            color: var(--secondary-text);
            cursor: not-allowed;
            transform: none;
            filter: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
        }

        .btn-secondary {
            background-color: var(--secondary-text);
        }

        .btn-danger {
            background-color: var(--danger-color);
        }

        .btn-info {
            background-color: var(--info-color);
        }

        .btn .btn-text {
            transition: opacity .2s ease;
        }

        .btn.loading .btn-text {
            opacity: 0;
        }

        .btn .spinner {
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(0, 0, 0, 0.2);
            border-top-color: var(--secondary-text);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            opacity: 0;
            transition: opacity .2s ease;
        }

        .btn.btn-primary .spinner {
            border-top-color: #fff;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .btn.loading .spinner {
            opacity: 1;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .search-box input {
            width: 100%;
            font-size: 1rem;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius);
            background: var(--card-bg);
            transition: border-color .2s, box-shadow .2s;
            min-width: 300px;
        }

        .search-box input:focus-visible {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 174, 112, .15);
        }

        .team-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            align-items: start;
        }

        .team-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            border-top: 4px solid var(--primary-color);
            display: flex;
            flex-direction: column;
            transition: all .2s ease;
            overflow: hidden;
        }

        .team-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .team-card-header {
            padding: 1.25rem 1.5rem;
        }

        .team-card-header h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-color);
            margin: 0;
        }

        .team-card-body {
            padding: 0 1.5rem 1.25rem;
            flex-grow: 1;
        }

        .team-card-body h4 {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--secondary-text);
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .team-card-member-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .member-name-chip {
            background-color: var(--bg-color);
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.85rem;
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .no-members-text {
            width: 100%;
            text-align: center;
            padding: 2rem 0;
            color: var(--secondary-text);
        }

        .team-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background-color: var(--bg-color);
            border-top: 1px solid var(--border-color);
            margin-top: 1.25rem;
        }

        .team-meta-info {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--secondary-text);
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .team-meta-info .icon {
            color: var(--primary-color);
        }

        .team-actions {
            display: flex;
            gap: .5rem;
        }

        .btn-icon {
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--secondary-text);
            padding: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s, color 0.2s;
        }

        .btn-icon:hover {
            color: var(--text-color);
            background-color: var(--border-color);
        }

        .btn-icon[data-action="delete"]:hover {
            background-color: var(--danger-light);
            color: var(--danger-color);
        }

        .btn-icon .icon {
            width: 1.25rem;
            height: 1.25rem;
        }

        #no-search-results {
            display: none;
            text-align: center;
            padding: 2rem;
            grid-column: 1 / -1;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background-color: var(--card-bg);
            border-radius: var(--radius);
            border: 2px dashed var(--border-color);
        }

        .empty-state .icon {
            width: 4rem;
            height: 4rem;
            stroke-width: 1.5;
            color: var(--primary-color);
            opacity: 0.6;
            margin-bottom: 1rem;
        }

        .empty-state h2 {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: .5rem;
        }

        .empty-state p {
            margin-bottom: 1.5rem;
            color: var(--secondary-text);
        }

        .unassigned-users-container {
            margin-top: 4rem;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 2rem;
        }

        .unassigned-users-list {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .unassigned-user-chip {
            background: var(--bg-color);
            padding: .5rem 1rem;
            border-radius: 20px;
            font-size: .9rem;
            color: var(--secondary-text);
            border: 1px solid var(--border-color);
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100;
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
            display: flex;
            flex-direction: column;
        }

        .modal-overlay.visible .modal-form {
            transform: scale(1);
        }

        .modal-form-content {
            overflow-y: auto;
            padding-right: 1rem;
            margin-right: -1rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: .5rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--secondary-text);
        }

        .form-group input {
            width: 100%;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 1rem;
        }

        .form-group input:focus-visible {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 174, 112, .15);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            flex-shrink: 0;
        }

        .searchable-list-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-bottom: .75rem;
        }

        .searchable-list-controls input[type="text"] {
            flex-grow: 1;
            padding: .6em 1.1em;
            border-radius: 25px;
        }

        .select-all-label {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 500;
            cursor: pointer;
            font-size: .9rem;
            color: var(--secondary-text);
        }

        .modern-selection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 8px;
        }

        .assignment-grid-container {
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            padding: 10px;
            border-radius: 8px;
        }

        .selectable-item {
            display: block;
        }

        .selectable-item input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .selectable-item label {
            display: flex;
            align-items: center;
            width: 100%;
            min-height: 44px;
            padding: 8px 12px;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--bg-color);
            color: var(--secondary-text);
            font-size: 0.9rem;
            text-align: right;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            user-select: none;
        }

        .selectable-item label:hover {
            border-color: var(--primary-color);
            color: var(--primary-dark);
        }

        .selectable-item input[type="checkbox"]:checked+label {
            background-color: var(--primary-light);
            border-color: var(--primary-dark);
            color: var(--primary-dark);
            font-weight: 600;
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
            <div>
                <h1 class="page-title">مدیریت تیم‌ها</h1>
                <p class="page-subtitle">تیم‌های جدید بسازید و اعضای آن‌ها را مدیریت کنید.</p>
            </div>
            <div style="display: flex; gap: 1rem; align-items:center; flex-wrap: wrap;">
                <div class="search-box">
                    <input type="text" id="team-search-input" placeholder="جستجوی تیم...">
                </div>
                <button id="add-new-team-btn" class="btn btn-primary">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="M12 5v14" />
                    </svg>
                    <span>تیم جدید</span>
                </button>
            </div>
        </div>

        <?php if (empty($teams)): ?>
            <div class="empty-state">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" x2="12" y1="9" y2="13" />
                    <line x1="12" x2="12.01" y1="17" y2="17" />
                </svg>
                <h2>هنوز هیچ تیمی نساخته‌اید!</h2>
                <p>برای شروع، اولین تیم خود را ایجاد کرده و کاربران را به آن اضافه کنید.</p>
                <button id="add-new-team-btn-empty" class="btn btn-primary">ایجاد اولین تیم</button>
            </div>
        <?php else: ?>
            <div id="teams-grid" class="team-card-grid">
                <?php foreach ($teams as $team): ?>
                    <div class="team-card" data-search-term="<?= htmlspecialchars(strtolower($team['team_name'])) ?>">
                        <div class="team-card-header">
                            <h3><?= htmlspecialchars($team['team_name']) ?></h3>
                        </div>
                        <div class="team-card-body">
                            <h4>اعضا</h4>
                            <div class="team-card-member-list">
                                <?php if ($team['member_names']):
                                    $members = explode('||', $team['member_names']);
                                    foreach ($members as $name): ?>
                                        <span class="member-name-chip"><?= htmlspecialchars($name) ?></span>
                                    <?php endforeach;
                                else: ?>
                                    <div class="no-members-text">هیچ عضوی در این تیم نیست.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="team-card-footer">
                            <div class="team-meta-info">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                                <span><?= $team['member_count'] ?> عضو</span>
                            </div>
                            <div class="team-actions">
                                <button class="btn-icon" data-action="edit" title="ویرایش" data-id="<?= $team['id'] ?>">
                                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                    </svg>
                                </button>
                                <button class="btn-icon" data-action="delete" title="حذف" data-id="<?= $team['id'] ?>" data-name="<?= htmlspecialchars($team['team_name']) ?>">
                                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18" />
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div id="no-search-results">
                    <h3>تیمی با این نام یافت نشد.</h3>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($unassigned_users)): ?>
            <div class="unassigned-users-container">
                <h2 class="page-title" style="font-size: 1.5rem; margin: 0;">کاربران بدون تیم</h2>
                <div class="unassigned-users-list">
                    <?php foreach ($unassigned_users as $user): ?>
                        <span class="unassigned-user-chip"><?= htmlspecialchars($user['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <div id="modal-overlay" class="modal-overlay">
        <div id="modal-form" class="modal-form">
            <h2 id="form-title" class="page-title" style="font-size: 1.5rem;">افزودن تیم جدید</h2>
            <form id="team-form" class="modal-form-content">
                <input type="hidden" id="team-id">
                <div class="form-group">
                    <label for="team-name">نام تیم:</label>
                    <input type="text" id="team-name" required>
                </div>
                <h3>اعضای تیم:</h3>
                <div class="searchable-list-controls">
                    <input type="text" id="user-search" placeholder="جستجوی کاربر...">
                    <label class="select-all-label"><input type="checkbox" id="select-all-users"> انتخاب همه</label>
                </div>
                <div class="assignment-grid-container">
                    <div id="users-container" class="modern-selection-grid"></div>
                </div>
            </form>
            <div class="form-actions">
                <button type="button" id="cancel-btn" class="btn btn-secondary">انصراف</button>
                <button type="submit" form="team-form" id="save-btn" class="btn btn-primary">
                    <span class="btn-text">ذخیره</span>
                    <span class="spinner"></span>
                </button>
            </div>
        </div>
    </div>

    <div id="toast-container"></div>
    <div id="footer-placeholder"></div>

    <script src="/js/header.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const allUsers = <?= json_encode($all_users) ?>;
            const modalOverlay = document.getElementById('modal-overlay');
            const form = document.getElementById('team-form');
            const formTitle = document.getElementById('form-title');
            const saveBtn = document.getElementById('save-btn');
            const teamsGrid = document.getElementById('teams-grid');

            const showModal = () => modalOverlay.classList.add('visible');
            const hideModal = () => modalOverlay.classList.remove('visible');

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

            const toggleLoading = (button, isLoading) => {
                button.disabled = isLoading;
                button.classList.toggle('loading', isLoading);
            };

            const setupSearchableList = (searchInputId, selectAllCheckboxId, containerId) => {
                const searchInput = document.getElementById(searchInputId);
                const selectAllCheckbox = document.getElementById(selectAllCheckboxId);
                const container = document.getElementById(containerId);

                searchInput.addEventListener('input', () => {
                    const searchTerm = searchInput.value.toLowerCase();
                    container.querySelectorAll('.filterable-item').forEach(item => {
                        item.style.display = item.textContent.toLowerCase().includes(searchTerm) ? 'block' : 'none';
                    });
                    selectAllCheckbox.checked = false;
                });

                selectAllCheckbox.addEventListener('change', () => {
                    container.querySelectorAll('.filterable-item').forEach(item => {
                        if (item.style.display !== 'none') {
                            item.querySelector('input[type="checkbox"]').checked = selectAllCheckbox.checked;
                        }
                    });
                });
            };

            const renderUsers = (selectedUserIds = []) => {
                const usersContainer = document.getElementById('users-container');
                usersContainer.innerHTML = '';
                allUsers.forEach(user => {
                    const isChecked = selectedUserIds.includes(parseInt(user.id));
                    const itemHTML = `
                        <div class="selectable-item filterable-item">
                            <input type="checkbox" name="members" value="${user.id}" id="user-${user.id}" ${isChecked ? 'checked' : ''}>
                            <label for="user-${user.id}">${user.name}</label>
                        </div>`;
                    usersContainer.insertAdjacentHTML('beforeend', itemHTML);
                });
            };

            const openAddModal = () => {
                form.reset();
                formTitle.textContent = 'افزودن تیم جدید';
                document.getElementById('team-id').value = '';
                renderUsers();
                setupSearchableList('user-search', 'select-all-users', 'users-container');
                showModal();
            };

            const openEditModal = async (id) => {
                try {
                    const response = await fetch(`teams_api.php?action=get_team&id=${id}`);
                    const data = await response.json();
                    if (data.success) {
                        const team = data.team;
                        form.reset();
                        formTitle.textContent = `ویرایش تیم: ${team.team_name}`;
                        document.getElementById('team-id').value = team.id;
                        document.getElementById('team-name').value = team.team_name;
                        const memberIds = data.team.member_details.map(m => parseInt(m.id));
                        renderUsers(memberIds);
                        setupSearchableList('user-search', 'select-all-users', 'users-container');
                        showModal();
                    } else {
                        showToast(data.message || 'خطا در دریافت اطلاعات', 'error');
                    }
                } catch (err) {
                    showToast('خطای شبکه. لطفاً دوباره تلاش کنید.', 'error');
                }
            };

            document.getElementById('add-new-team-btn')?.addEventListener('click', openAddModal);
            document.getElementById('add-new-team-btn-empty')?.addEventListener('click', openAddModal);

            if (modalOverlay) {
                document.getElementById('cancel-btn').addEventListener('click', hideModal);
                modalOverlay.addEventListener('click', e => {
                    if (e.target === modalOverlay) hideModal();
                });
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    toggleLoading(saveBtn, true);
                    const teamId = document.getElementById('team-id').value;
                    const action = teamId ? 'update_team' : 'create_team';
                    const memberIds = Array.from(document.querySelectorAll('input[name="members"]:checked')).map(cb => parseInt(cb.value));
                    const data = {
                        id: teamId || undefined,
                        name: document.getElementById('team-name').value,
                        members: memberIds
                    };
                    try {
                        const response = await fetch(`teams_api.php?action=${action}`, {
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
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            showToast(result.message || 'خطایی رخ داد.', 'error');
                        }
                    } catch (err) {
                        showToast('خطای شبکه. لطفاً دوباره تلاش کنید.', 'error');
                    } finally {
                        toggleLoading(saveBtn, false);
                    }
                });
            }

            if (teamsGrid) {
                teamsGrid.addEventListener('click', async (e) => {
                    const actionButton = e.target.closest('.btn-icon');
                    if (!actionButton) return;
                    e.preventDefault();
                    const action = actionButton.dataset.action;
                    const teamId = actionButton.dataset.id;
                    if (action === 'edit') {
                        openEditModal(teamId);
                    } else if (action === 'delete') {
                        const teamName = actionButton.dataset.name;
                        showConfirmation(`آیا از حذف تیم "${teamName}" مطمئن هستید؟`, async () => {
                            const formData = new FormData();
                            formData.append('id', teamId);
                            try {
                                const response = await fetch(`teams_api.php?action=delete_team`, {
                                    method: 'POST',
                                    body: formData
                                });
                                const result = await response.json();
                                if (result.success) {
                                    showToast('تیم با موفقیت حذف شد.');
                                    const cardToRemove = actionButton.closest('.team-card');
                                    cardToRemove.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
                                    cardToRemove.style.transform = 'scale(0.9)';
                                    cardToRemove.style.opacity = '0';
                                    setTimeout(() => cardToRemove.remove(), 300);
                                } else {
                                    showToast(result.message || 'خطا در حذف تیم', 'error');
                                }
                            } catch (err) {
                                showToast('خطای شبکه. لطفاً دوباره تلاش کنید.', 'error');
                            }
                        });
                    }
                });
                const searchInput = document.getElementById('team-search-input');
                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        const searchTerm = e.target.value.toLowerCase().trim();
                        let visibleCount = 0;
                        teamsGrid.querySelectorAll('.team-card').forEach(card => {
                            const shouldShow = card.dataset.searchTerm.includes(searchTerm);
                            card.style.display = shouldShow ? 'flex' : 'none';
                            if (shouldShow) visibleCount++;
                        });
                        document.getElementById('no-search-results').style.display = (visibleCount === 0 && teams.length > 0) ? 'block' : 'none';
                    });
                }
            }
        });
    </script>
</body>

</html>
