<?php
require_once __DIR__ . '/../../../auth/require-auth.php';
$claims = requireAuth('admin', '/../../auth/login.html');
require_once __DIR__ . '/../../../db/database.php';

$stmt = $pdo->query("
    SELECT
        sa.id,
        s.title AS scenario_title,
        t.team_name,
        sa.is_active
    FROM ScenarioAssignments sa
    JOIN Scenarios s ON sa.scenario_id = s.id
    JOIN Teams t ON sa.team_id = t.id
    ORDER BY sa.id DESC
");
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت تخصیص سناریو</title>
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
            --danger-color: #dc3545;
            --success-color: #28a745;
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
            padding: 2.5rem 2rem;
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
            font-size: 1.8rem;
            margin-block-end: .5rem;
        }

        .page-subtitle {
            color: var(--secondary-text);
            font-weight: 400;
            font-size: 1rem;
        }

        .btn {
            padding: .8em 1.5em;
            font-weight: 600;
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: .5em;
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

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--card-bg);
            box-shadow: var(--shadow-sm);
            border-radius: var(--radius);
            overflow: hidden;
        }

        th,
        td {
            padding: 1rem 1.25rem;
            text-align: right;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: var(--bg-color);
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--secondary-text);
        }

        td {
            font-size: 0.95rem;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .team-badge {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            padding: .25rem .75rem;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-toggle {
            display: flex;
            align-items: center;
            gap: .75rem;
            cursor: pointer;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--primary-color);
        }

        input:checked+.slider:before {
            transform: translateX(20px);
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
            width: min(500px, 95%);
            transform: scale(.95);
            transition: transform .3s;
        }

        .modal-overlay.visible .modal-form {
            transform: scale(1);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label,
        .form-group select {
            display: block;
            width: 100%;
        }

        .form-group label {
            margin-bottom: .5rem;
            font-weight: 600;
        }

        .form-group select {
            padding: .8em;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .form-actions {
            margin-top: 1.5rem;
            text-align: left;
            display: flex;
            gap: .5rem;
            justify-content: flex-end;
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
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="page-toolbar">
            <div>
                <h1 class="page-title">مدیریت تخصیص‌ها</h1>
                <p class="page-subtitle">سناریوها را به تیم‌ها تخصیص دهید و وضعیت پاسخ‌دهی آن‌ها را مدیریت کنید.</p>
            </div>
            <button id="add-assignment-btn" class="btn btn-primary">تخصیص سناریو جدید</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>سناریو</th>
                    <th>تیم</th>
                    <th>وضعیت پاسخ‌دهی</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $assignment) : ?>
                    <tr>
                        <td><?= htmlspecialchars($assignment['scenario_title']) ?></td>
                        <td><span class="team-badge"><?= htmlspecialchars($assignment['team_name']) ?></span></td>
                        <td>
                            <label class="status-toggle" for="status-<?= $assignment['id'] ?>">
                                <span id="status-text-<?= $assignment['id'] ?>"><?= $assignment['is_active'] ? 'فعال' : 'غیرفعال' ?></span>
                                <div class="switch">
                                    <input type="checkbox" id="status-<?= $assignment['id'] ?>" onchange="toggleAssignmentStatus(<?= $assignment['id'] ?>)" <?= $assignment['is_active'] ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </div>
                            </label>
                        </td>
                        <td>
                            <button class="btn btn-danger" style="font-size: 0.8rem; padding: 0.5em 1em;" onclick="deleteAssignment(<?= $assignment['id'] ?>)">حذف</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($assignments)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem;">هنوز هیچ سناریویی تخصیص داده نشده است.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
    <div id="modal-overlay" class="modal-overlay">
        <div class="modal-form">
            <h2 style="margin-bottom: 1.5rem;">تخصیص سناریو به تیم</h2>
            <div class="form-group"><label for="scenario-id">انتخاب سناریو:</label><select id="scenario-id" required></select></div>
            <div class="form-group"><label for="team-id">انتخاب تیم:</label><select id="team-id" required></select></div>
            <div class="form-actions"><button type="button" id="cancel-btn" class="btn btn-secondary">انصراف</button><button type="button" id="save-btn" class="btn btn-primary">ذخیره تخصیص</button></div>
        </div>
    </div>
    <div id="toast-container"></div>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js"></script>
    <script>
        function showToast(message, type = 'success', duration = 3000) {
            const container = document.getElementById('toast-container');
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

        async function toggleAssignmentStatus(id) {
            const formData = new FormData();
            formData.append('action', 'toggle_assignment_status');
            formData.append('id', id);
            try {
                const response = await fetch('assignments_api.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                showToast(result.message, result.success ? 'success' : 'error');
                if (result.success) {
                    document.getElementById(`status-text-${id}`).textContent = result.is_active ? 'فعال' : 'غیرفعال';
                }
            } catch (error) {
                showToast('خطا در ارتباط با سرور.', 'error');
            }
        }

        async function deleteAssignment(id) {
            if (!confirm('آیا از حذف این تخصیص مطمئن هستید؟ این عمل غیرقابل بازگشت است.')) return;
            const formData = new FormData();
            formData.append('action', 'delete_assignment');
            formData.append('id', id);
            try {
                const response = await fetch('assignments_api.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    showToast(result.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('خطا در ارتباط با سرور.', 'error');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const modalOverlay = document.getElementById('modal-overlay');
            const addBtn = document.getElementById('add-assignment-btn');
            const cancelBtn = document.getElementById('cancel-btn');
            const saveBtn = document.getElementById('save-btn');

            addBtn.addEventListener('click', async () => {
                try {
                    const response = await fetch('assignments_api.php?action=get_active_scenarios_and_teams');
                    const data = await response.json();
                    if (data.success) {
                        const scenarioSelect = document.getElementById('scenario-id');
                        const teamSelect = document.getElementById('team-id');
                        scenarioSelect.innerHTML = '<option value="">یک سناریو را انتخاب کنید...</option>';
                        data.scenarios.forEach(s => {
                            scenarioSelect.innerHTML += `<option value="${s.id}">${s.title}</option>`;
                        });
                        teamSelect.innerHTML = '<option value="">یک تیم را انتخاب کنید...</option>';
                        data.teams.forEach(t => {
                            teamSelect.innerHTML += `<option value="${t.id}">${t.team_name}</option>`;
                        });
                        modalOverlay.classList.add('visible');
                    } else {
                        showToast(data.message, 'error');
                    }
                } catch (e) {
                    showToast('خطا در بارگذاری اطلاعات.', 'error');
                }
            });

            const hideModal = () => modalOverlay.classList.remove('visible');
            cancelBtn.addEventListener('click', hideModal);
            modalOverlay.addEventListener('click', e => {
                if (e.target === modalOverlay) hideModal();
            });

            saveBtn.addEventListener('click', async () => {
                const scenarioId = document.getElementById('scenario-id').value;
                const teamId = document.getElementById('team-id').value;

                if (!scenarioId || !teamId) {
                    showToast('لطفا سناریو و تیم را انتخاب کنید.', 'error');
                    return;
                }
                const formData = new FormData();
                formData.append('action', 'create_assignment');
                formData.append('scenario_id', scenarioId);
                formData.append('team_id', teamId);
                try {
                    const response = await fetch('assignments_api.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        showToast(result.message, 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showToast(result.message, 'error');
                    }
                } catch (e) {
                    showToast('خطا در ارتباط با سرور.', 'error');
                }
            });
        });
    </script>
</body>

</html>
