<?php
// فایل: teams.php (بازطراحی شده برای UI/UX بهتر)
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');
require_once 'database.php';

// خواندن تیم‌ها برای نمایش در لیست
$stmt_teams = $pdo->query("SELECT id, team_name FROM Teams ORDER BY id DESC");
$teams = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);

// خواندن کاربرانی که عضو هیچ تیمی نیستند برای استفاده در فرم
$stmt_users = $pdo->query("
    SELECT u.id, u.name
    FROM Users u
    LEFT JOIN TeamMembers tm ON u.id = tm.user_id
    WHERE tm.user_id IS NULL
    ORDER BY u.name
");
$unassigned_users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت تیم‌ها</title>
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
        }

        .btn {
            position: relative;
            display: inline-block;
            padding: .75rem 1.25rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: .95rem;
            font-weight: 600;
            text-align: center;
            margin: 5px 0;
            transition: all .2s ease;
        }

        .btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .btn .btn-text {
            transition: opacity .2s ease;
        }

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

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
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


        .item-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .list-item {
            background-color: #f8f9fa;
            border: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: .75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: opacity .3s, transform .3s;
        }

        .list-item.removing {
            opacity: 0;
            transform: translateX(50px);
        }

        .list-item p {
            margin: 0;
            font-weight: 500;
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

        .form-group input {
            width: 100%;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
        }

        .members-grid {
            display: grid;
            max-height: 250px;
            overflow-y: auto;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
            border: 1px solid var(--border-color);
            padding: 10px;
            border-radius: 5px;
        }

        .members-grid .current-member {
            background-color: var(--primary-light);
            padding: 5px;
            border-radius: 3px;
        }

        .form-actions {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        #toast-container {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 200;
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
    <header>
        <h1><a href="index.php">پنل مدیریت آزمون</a></h1>
        <?php if (isset($claims) && isset($claims['name'])): ?>
            <span id="user-info">خوش آمدید، <?= htmlspecialchars($claims['name']) ?></span>
        <?php endif; ?>
    </header>
    <main>
        <div class="item-list-container">
            <div class="item-list-header">
                <div>
                    <h1 class="page-title" style="margin-bottom: 0;">مدیریت تیم‌ها</h1>
                    <p class="page-subtitle" style="margin-bottom: 0;">تیم‌ها و اعضای آن‌ها را مدیریت کنید.</p>
                </div>
                <button id="add-new-team-btn" class="btn btn-primary"><span class="btn-text">افزودن تیم جدید</span></button>
            </div>
            <div id="teams-list" class="item-list">
                <?php foreach ($teams as $team): ?>
                    <div class="list-item" id="team-item-<?= $team['id'] ?>">
                        <p><?= htmlspecialchars($team['team_name']) ?></p>
                        <div>
                            <button class="btn btn-secondary" onclick="editTeam(<?= $team['id'] ?>)">ویرایش</button>
                            <button class="btn btn-danger" onclick="deleteTeam(<?= $team['id'] ?>)">حذف</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> - سامانه آزمون</p>
    </footer>

    <div id="modal-overlay" class="modal-overlay">
        <div id="modal-form" class="modal-form">
            <h2 id="form-title" class="page-title">افزودن تیم جدید</h2>
            <form id="team-form">
                <input type="hidden" id="team-id">
                <input type="hidden" id="action">
                <div class="form-group">
                    <label for="team-name">نام تیم:</label>
                    <input type="text" id="team-name" required>
                </div>
                <h3>اعضای تیم:</h3>
                <div id="members-container" class="members-grid"></div>
                <div class="form-actions">
                    <button type="submit" id="save-btn" class="btn btn-primary">
                        <span class="btn-text">ذخیره</span>
                        <span class="spinner"></span>
                    </button>
                    <button type="button" id="cancel-btn" class="btn">انصراف</button>
                </div>
            </form>
        </div>
    </div>
    <div id="toast-container"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalOverlay = document.getElementById('modal-overlay');
            const form = document.getElementById('team-form');
            const formTitle = document.getElementById('form-title');
            const saveBtn = document.getElementById('save-btn');
            const membersContainer = document.getElementById('members-container');

            const unassignedUsers = <?= json_encode($unassigned_users) ?>;

            const showModal = () => modalOverlay.classList.add('visible');
            const hideModal = () => modalOverlay.classList.remove('visible');

            const showToast = (message, type = 'success') => {
                /* ... code from previous answer ... */
            };
            const toggleLoading = (button, isLoading) => {
                /* ... code from previous answer ... */
            };

            const createTeamListItem = (team) => {
                const item = document.createElement('div');
                item.className = 'list-item';
                item.id = `team-item-${team.id}`;
                item.innerHTML = `
                    <p>${team.team_name}</p>
                    <div>
                        <button class="btn btn-secondary" onclick="editTeam(${team.id})">ویرایش</button>
                        <button class="btn btn-danger" onclick="deleteTeam(${team.id})">حذف</button>
                    </div>
                `;
                return item;
            };

            const renderMembers = (currentMembers = []) => {
                membersContainer.innerHTML = '';
                currentMembers.forEach(user => {
                    const label = document.createElement('label');
                    label.className = 'current-member';
                    label.innerHTML = `<input type="checkbox" name="members" value="${user.id}" checked> ${user.name} (عضو فعلی)`;
                    membersContainer.appendChild(label);
                });
                unassignedUsers.forEach(user => {
                    const label = document.createElement('label');
                    label.innerHTML = `<input type="checkbox" name="members" value="${user.id}"> ${user.name}`;
                    membersContainer.appendChild(label);
                });
            };

            window.editTeam = async (id) => {
                const response = await fetch(`teams_api.php?action=get_team&id=${id}`);
                const data = await response.json();
                if (data.success) {
                    const team = data.team;
                    form.reset();
                    formTitle.textContent = 'ویرایش تیم';
                    document.getElementById('team-id').value = team.id;
                    document.getElementById('action').value = 'update_team';
                    document.getElementById('team-name').value = team.team_name;
                    renderMembers(team.member_details);
                    showModal();
                } else {
                    showToast(data.message || 'خطا در دریافت اطلاعات', 'error');
                }
            };

            window.deleteTeam = async (id) => {
                if (confirm('آیا از حذف این تیم مطمئن هستید؟')) {
                    const formData = new FormData();
                    formData.append('action', 'delete_team');
                    formData.append('id', id);
                    const response = await fetch('teams_api.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        const itemToRemove = document.getElementById(`team-item-${id}`);
                        itemToRemove.classList.add('removing');
                        setTimeout(() => itemToRemove.remove(), 300);
                        showToast('تیم با موفقیت حذف شد.');
                    } else {
                        showToast(result.message || 'خطا در حذف', 'error');
                    }
                }
            };

            document.getElementById('add-new-team-btn').addEventListener('click', () => {
                form.reset();
                formTitle.textContent = 'افزودن تیم جدید';
                document.getElementById('team-id').value = '';
                document.getElementById('action').value = 'create_team';
                renderMembers();
                showModal();
            });

            document.getElementById('cancel-btn').addEventListener('click', hideModal);
            modalOverlay.addEventListener('click', e => {
                if (e.target === modalOverlay) hideModal();
            });

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                toggleLoading(saveBtn, true);

                const memberIds = Array.from(document.querySelectorAll('input[name="members"]:checked')).map(cb => parseInt(cb.value));
                const data = {
                    id: document.getElementById('team-id').value,
                    name: document.getElementById('team-name').value,
                    members: memberIds
                };
                const action = document.getElementById('action').value;
                const response = await fetch(`teams_api.php?action=${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    if (action === 'create_team') {
                        const newListItem = createTeamListItem(result.team);
                        document.getElementById('teams-list').prepend(newListItem);
                    } else {
                        const itemToUpdate = document.getElementById(`team-item-${data.id}`);
                        itemToUpdate.querySelector('p').textContent = data.name;
                    }
                    hideModal();
                    showToast('عملیات با موفقیت انجام شد.');
                } else {
                    showToast(result.message || 'خطایی رخ داد.', 'error');
                }
                toggleLoading(saveBtn, false);
            });

            // Helper functions to copy
            const showToast_func = (message, type = 'success') => {
                const toastContainer = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.textContent = message;
                toastContainer.appendChild(toast);
                setTimeout(() => toast.remove(), 4000);
            };
            const toggleLoading_func = (button, isLoading) => {
                button.disabled = isLoading;
                button.classList.toggle('loading', isLoading);
            };
        });
    </script>
</body>

</html>
