<?php
require_once __DIR__ . '/../../../auth/require-auth.php';
$claims = requireAuth('admin', '/../../auth/login.html');
require_once __DIR__ . '/../../../db/database.php';

$stmt_scenarios = $pdo->query("
    SELECT
        s.id,
        s.title,
        s.description,
        (SELECT COUNT(*) FROM Challenges WHERE scenario_id = s.id) AS challenge_count
    FROM Scenarios s
    ORDER BY s.id DESC
");
$scenarios = $stmt_scenarios->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت سناریوها</title>
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
            text-decoration: none;
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

        .task-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .task-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            transition: all .2s ease;
        }

        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .task-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .task-card-header h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-color);
        }

        .task-card-meta {
            display: flex;
            flex-direction: column;
            gap: .75rem;
            flex-grow: 1;
            color: var(--secondary-text);
            font-size: .9rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .meta-item .icon {
            color: var(--primary-color);
        }

        .actions-menu {
            position: relative;
        }

        .actions-menu-btn {
            background: none;
            border: none;
            padding: .5rem;
            cursor: pointer;
            border-radius: 50%;
            display: flex;
            color: var(--secondary-text);
        }

        .actions-menu-btn:hover {
            background-color: var(--bg-color);
        }

        .actions-menu-btn .icon {
            width: 1.25rem;
            height: 1.25rem;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            left: 0;
            top: calc(100% + 5px);
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: var(--shadow-md);
            list-style: none;
            padding: .5rem 0;
            width: 140px;
            z-index: 10;
            border: 1px solid var(--border-color);
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .6rem 1rem;
            font-size: .9rem;
            text-decoration: none;
            color: var(--text-color);
        }

        .dropdown-menu a:hover {
            background-color: var(--bg-color);
        }

        .dropdown-menu .delete-action {
            color: var(--danger-color);
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
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
            width: min(800px, 95%);
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
            min-height: 350px;
        }

        .form-step {
            display: none;
            animation: fadeIn 0.5s;
        }

        .form-step.active-step {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            font-size: 1rem;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius);
            background: var(--card-bg);
            transition: border-color .2s, box-shadow .2s;
        }

        .form-group input:focus-visible,
        .form-group textarea:focus-visible,
        .form-group select:focus-visible {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 174, 112, .15);
        }

        #step-indicator {
            font-size: .9rem;
            color: var(--secondary-text);
            font-weight: 500;
        }

        .form-actions {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            flex-shrink: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navigation-buttons {
            display: flex;
            gap: 10px;
        }

        #challenge-list {
            margin-top: 1.5rem;
        }

        .challenge-item {
            display: flex;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            margin-bottom: .5rem;
            background-color: var(--bg-color);
            transition: background-color 0.3s;
        }

        .challenge-item.editing {
            background-color: var(--primary-light);
            border-color: var(--primary-color);
        }

        .challenge-item-content {
            flex-grow: 1;
        }

        .challenge-item-actions {
            display: flex;
            gap: .5rem;
        }

        .btn-icon {
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--secondary-text);
            padding: 0;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s, color 0.2s;
        }

        .btn-icon:hover {
            background-color: var(--border-color);
            color: var(--text-color);
        }

        .btn-icon .icon {
            width: 1.1rem;
            height: 1.1rem;
            stroke-width: 2.5;
        }

        .challenge-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        #add-challenge-form {
            margin-top: 2rem;
            padding: 1.5rem;
            border: 2px dashed var(--border-color);
            border-radius: var(--radius);
            background-color: var(--card-bg);
        }

        #add-challenge-form-actions {
            display: flex;
            gap: 0.5rem;
        }

        #image-preview {
            max-width: 100px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }

        #toast-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1001;
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
                <h1 class="page-title">مدیریت سناریوها</h1>
                <p class="page-subtitle">سناریوها و چالش‌های آنها را از اینجا بسازید و مدیریت کنید.</p>
            </div>
            <button id="add-new-scenario-btn" class="btn btn-primary">سناریوی جدید</button>
        </div>
        <div id="scenarios-grid" class="task-card-grid">
            <?php foreach ($scenarios as $scenario) : ?>
                <div class="task-card">
                    <div class="task-card-header">
                        <h3><?= htmlspecialchars($scenario['title']) ?></h3>
                        <div class="actions-menu">
                            <button class="actions-menu-btn">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="1" />
                                    <circle cx="12" cy="5" r="1" />
                                    <circle cx="12" cy="19" r="1" />
                                </svg>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href="#" onclick="editScenario(event, <?= $scenario['id'] ?>)"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg><span>ویرایش</span></a></li>
                                <li><a href="#" onclick="deleteScenario(event, <?= $scenario['id'] ?>)" class="delete-action"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg><span>حذف</span></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="task-card-meta">
                        <span class="meta-item">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                                <polyline points="14 2 14 8 20 8" />
                                <line x1="16" y1="13" x2="8" y2="13" />
                                <line x1="16" y1="17" x2="8" y2="17" />
                                <line x1="10" y1="9" x2="8" y2="9" />
                            </svg>
                            <span><?= $scenario['challenge_count'] ?> چالش</span>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <div id="modal-overlay" class="modal-overlay">
        <div id="modal-form" class="modal-form">
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1rem;">
                <h2 id="form-title" class="page-title" style="font-size: 1.5rem;">افزودن سناریوی جدید</h2>
                <span id="step-indicator">مرحله ۱ از ۲</span>
            </div>
            <div class="modal-form-content">
                <div id="step-1" class="form-step active-step">
                    <p class="page-subtitle" style="margin-bottom: 1.5rem;">ابتدا اطلاعات کلی سناریو را وارد کنید.</p>
                    <div class="form-group">
                        <label for="scenario-title">عنوان سناریو:</label>
                        <input type="text" id="scenario-title" required>
                    </div>
                    <div class="form-group">
                        <label for="scenario-description">توضیحات:</label>
                        <textarea id="scenario-description" rows="3"></textarea>
                    </div>
                </div>
                <div id="step-2" class="form-step">
                    <p class="page-subtitle" style="margin-bottom: 1rem;">چالش‌های سناریو را یک به یک اضافه یا ویرایش کنید.</p>
                    <div id="challenge-list"></div>
                    <form id="add-challenge-form">
                        <h4 id="challenge-form-title">افزودن چالش جدید</h4>
                        <div class="form-group">
                            <label for="challenge-text">متن چالش:</label>
                            <textarea id="challenge-text" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="challenge-image">تصویر چالش (اختیاری):</label>
                            <input type="file" id="challenge-image" accept="image/*">
                            <img id="image-preview" src="#" alt="پیش‌نمایش تصویر" />
                        </div>
                        <div id="add-challenge-form-actions">
                            <button type="submit" id="add-challenge-btn" class="btn btn-primary">افزودن این چالش</button>
                            <button type="button" id="cancel-edit-btn" class="btn btn-secondary" style="display: none;">انصراف از ویرایش</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" id="cancel-btn" class="btn btn-secondary">انصراف</button>
                <div class="navigation-buttons">
                    <button type="button" id="prev-btn" class="btn btn-secondary">قبلی</button>
                    <button type="button" id="next-btn" class="btn btn-primary">بعدی</button>
                    <button type="button" id="save-btn" class="btn btn-primary">
                        <span class="btn-text">ذخیره</span>
                        <span class="spinner"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div id="toast-container"></div>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js"></script>
    <script>
        function showToast(message, type = 'success', duration = 4000) {
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

        function showConfirmation(message, onConfirm) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast toast-confirm';
            toast.innerHTML = `<div class="toast-message">${message}</div><div class="toast-buttons"><button class="btn btn-danger" id="confirmAction">بله، حذف کن</button><button class="btn btn-secondary" id="cancelAction">لغو</button></div>`;
            const removeToast = () => {
                toast.classList.remove('show');
                toast.addEventListener('transitionend', () => toast.remove());
            };
            toast.querySelector('#confirmAction').onclick = () => {
                onConfirm();
                removeToast();
            };
            toast.querySelector('#cancelAction').onclick = removeToast;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
        }
        async function deleteScenario(event, id) {
            event.preventDefault();
            showConfirmation('آیا از حذف این سناریو و تمام چالش‌های آن مطمئن هستید؟', async () => {
                const formData = new FormData();
                formData.append('action', 'delete_scenario');
                formData.append('id', id);
                try {
                    const response = await fetch('scenarios_api.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    showToast(result.message, result.success ? 'success' : 'error');
                    if (result.success) setTimeout(() => window.location.reload(), 1200);
                } catch (error) {
                    showToast('خطا در ارتباط با سرور.', 'error');
                }
            });
        }

        function editScenario(event, id) {
            event.preventDefault();
            document.dispatchEvent(new CustomEvent('openModalForEdit', {
                detail: {
                    id
                }
            }));
        }
        document.addEventListener('DOMContentLoaded', () => {
            const ICONS = {
                edit: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" /></svg>`,
                delete: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" /><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /></svg>`
            };
            const modalOverlay = document.getElementById('modal-overlay');
            const modalForm = document.getElementById('modal-form');
            const formTitle = document.getElementById('form-title');
            const saveBtn = document.getElementById('save-btn');
            const nextBtn = document.getElementById('next-btn');
            const prevBtn = document.getElementById('prev-btn');
            const cancelBtn = document.getElementById('cancel-btn');
            const stepIndicator = document.getElementById('step-indicator');
            const steps = document.querySelectorAll('.form-step');
            const addChallengeForm = document.getElementById('add-challenge-form');
            const challengeList = document.getElementById('challenge-list');
            const challengeText = document.getElementById('challenge-text');
            const challengeImageInput = document.getElementById('challenge-image');
            const imagePreview = document.getElementById('image-preview');
            const challengeFormTitle = document.getElementById('challenge-form-title');
            const addChallengeBtn = document.getElementById('add-challenge-btn');
            const cancelEditBtn = document.getElementById('cancel-edit-btn');
            let currentStep = 1;
            const totalSteps = steps.length;
            let currentlyEditingIndex = null;
            let scenarioData = {
                id: null,
                title: '',
                description: '',
                challenges: []
            };
            const showModal = () => modalOverlay.classList.add('visible');
            const hideModal = () => modalOverlay.classList.remove('visible');
            const toggleLoading = (button, isLoading) => {
                button.disabled = isLoading;
                button.classList.toggle('loading', isLoading);
            };
            const renderChallenges = () => {
                challengeList.innerHTML = '';
                if (scenarioData.challenges.length === 0) {
                    challengeList.innerHTML = '<p style="text-align: center; color: var(--secondary-text); margin: 1rem 0;">هنوز چالشی اضافه نشده است.</p>';
                    return;
                }
                scenarioData.challenges.forEach((c, index) => {
                    const item = document.createElement('div');
                    item.className = 'challenge-item';
                    if (index === currentlyEditingIndex) item.classList.add('editing');
                    let imageHtml = c.existing_image_url ? `<img src="${c.existing_image_url}" alt="تصویر چالش">` : (c.file ? `<img src="${URL.createObjectURL(c.file)}" alt="پیش‌نمایش">` : '');
                    item.innerHTML = `${imageHtml}<div class="challenge-item-content"><span><strong>چالش ${index + 1}:</strong> ${c.text.substring(0, 80)}${c.text.length > 80 ? '...' : ''}</span></div><div class="challenge-item-actions"><button class="btn-icon btn-edit" data-index="${index}" title="ویرایش چالش">${ICONS.edit}</button><button class="btn-icon btn-delete" data-index="${index}" title="حذف چالش">${ICONS.delete}</button></div>`;
                    challengeList.appendChild(item);
                });
            };
            const cancelEditing = () => {
                currentlyEditingIndex = null;
                addChallengeForm.reset();
                imagePreview.style.display = 'none';
                challengeFormTitle.textContent = 'افزودن چالش جدید';
                addChallengeBtn.textContent = 'افزودن این چالش';
                cancelEditBtn.style.display = 'none';
                renderChallenges();
            };
            const startEditing = (index) => {
                currentlyEditingIndex = index;
                const c = scenarioData.challenges[index];
                addChallengeForm.reset();
                challengeText.value = c.text;
                imagePreview.style.display = 'none';
                if (c.existing_image_url) {
                    imagePreview.src = c.existing_image_url;
                    imagePreview.style.display = 'block';
                } else if (c.file) {
                    imagePreview.src = URL.createObjectURL(c.file);
                    imagePreview.style.display = 'block';
                }
                challengeFormTitle.textContent = `ویرایش چالش ${index + 1}`;
                addChallengeBtn.textContent = 'بروزرسانی چالش';
                cancelEditBtn.style.display = 'inline-flex';
                renderChallenges();
                addChallengeForm.scrollIntoView({
                    behavior: 'smooth'
                });
            };
            const updateFormSteps = () => {
                steps.forEach(step => step.classList.toggle('active-step', parseInt(step.id.split('-')[1]) === currentStep));
                stepIndicator.textContent = `مرحله ${currentStep} از ${totalSteps}`;
                prevBtn.style.display = currentStep > 1 ? 'inline-flex' : 'none';
                nextBtn.style.display = currentStep < totalSteps ? 'inline-flex' : 'none';
                saveBtn.style.display = currentStep === totalSteps ? 'inline-flex' : 'none';
            };
            const validateStep = (stepNumber) => {
                if (stepNumber === 1 && !document.getElementById('scenario-title').value.trim()) {
                    showToast('عنوان سناریو الزامی است.', 'error');
                    return false;
                }
                if (stepNumber === 2 && scenarioData.challenges.length === 0) {
                    showToast('سناریو باید حداقل یک چالش داشته باشد.', 'error');
                    return false;
                }
                return true;
            };
            nextBtn.addEventListener('click', () => {
                if (validateStep(currentStep) && currentStep < totalSteps) {
                    scenarioData.title = document.getElementById('scenario-title').value.trim();
                    scenarioData.description = document.getElementById('scenario-description').value.trim();
                    currentStep++;
                    updateFormSteps();
                }
            });
            prevBtn.addEventListener('click', () => {
                if (currentStep > 1) {
                    currentStep--;
                    updateFormSteps();
                }
            });
            cancelEditBtn.addEventListener('click', cancelEditing);
            addChallengeForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const text = challengeText.value.trim();
                const file = challengeImageInput.files[0];
                if (!text) {
                    showToast('متن چالش نمی‌تواند خالی باشد.', 'error');
                    return;
                }
                if (currentlyEditingIndex !== null) {
                    const c = scenarioData.challenges[currentlyEditingIndex];
                    c.text = text;
                    if (file) {
                        c.file = file;
                        c.existing_image_url = null;
                    }
                    showToast(`چالش ${currentlyEditingIndex + 1} بروزرسانی شد.`);
                } else {
                    scenarioData.challenges.push({
                        id: null,
                        text,
                        file,
                        existing_image_url: null
                    });
                }
                cancelEditing();
            });
            challengeImageInput.addEventListener('change', (e) => {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        imagePreview.src = event.target.result;
                        imagePreview.style.display = 'block';
                    }
                    reader.readAsDataURL(e.target.files[0]);
                } else {
                    imagePreview.style.display = 'none';
                }
            });
            challengeList.addEventListener('click', (e) => {
                const button = e.target.closest('.btn-icon');
                if (!button) return;
                const index = parseInt(button.dataset.index, 10);
                if (button.classList.contains('btn-delete')) {
                    showConfirmation(`آیا از حذف چالش ${index + 1} مطمئن هستید؟`, () => {
                        scenarioData.challenges.splice(index, 1);
                        if (index === currentlyEditingIndex) cancelEditing();
                        else renderChallenges();
                        showToast('چالش با موفقیت از لیست حذف شد.');
                    });
                } else if (button.classList.contains('btn-edit')) {
                    startEditing(index);
                }
            });
            const resetFormAndState = () => {
                document.getElementById('scenario-title').value = '';
                document.getElementById('scenario-description').value = '';
                addChallengeForm.reset();
                scenarioData = {
                    id: null,
                    title: '',
                    description: '',
                    challenges: []
                };
                currentlyEditingIndex = null;
                cancelEditing();
            };
            const openAddModal = () => {
                resetFormAndState();
                formTitle.textContent = 'افزودن سناریوی جدید';
                currentStep = 1;
                updateFormSteps();
                showModal();
            };
            document.getElementById('add-new-scenario-btn')?.addEventListener('click', openAddModal);
            document.addEventListener('openModalForEdit', async (e) => {
                resetFormAndState();
                const scenarioId = e.detail.id;
                modalForm.style.opacity = '0.5';
                try {
                    const response = await fetch(`scenarios_api.php?action=get_scenario&id=${scenarioId}`);
                    const data = await response.json();
                    if (data.success) {
                        const scenario = data.scenario;
                        formTitle.textContent = 'ویرایش سناریو';
                        document.getElementById('scenario-title').value = scenario.title;
                        document.getElementById('scenario-description').value = scenario.description;
                        scenarioData.id = scenario.id;
                        scenarioData.challenges = scenario.challenges.map(c => ({
                            id: c.id,
                            text: c.challenge_text,
                            file: null,
                            existing_image_url: c.image_url || null
                        }));
                        renderChallenges();
                        currentStep = 1;
                        updateFormSteps();
                        showModal();
                    } else {
                        showToast(data.message || 'خطا در بارگذاری اطلاعات.', 'error');
                    }
                } catch (error) {
                    showToast('خطا در ارتباط با سرور.', 'error');
                } finally {
                    modalForm.style.opacity = '1';
                }
            });
            cancelBtn.addEventListener('click', hideModal);
            modalOverlay.addEventListener('click', e => {
                if (e.target === modalOverlay) hideModal();
            });
            saveBtn.addEventListener('click', async () => {
                if (!validateStep(2)) return;
                toggleLoading(saveBtn, true);
                const formData = new FormData();
                formData.append('action', scenarioData.id ? 'update_scenario' : 'create_scenario');
                if (scenarioData.id) formData.append('id', scenarioData.id);
                formData.append('title', scenarioData.title);
                formData.append('description', scenarioData.description);
                scenarioData.challenges.forEach((c, index) => {
                    formData.append(`challenges_text[${index}]`, c.text);
                    formData.append(`challenges_ids[${index}]`, c.id || '');
                    if (c.file) formData.append(`challenges_images[${index}]`, c.file);
                });
                try {
                    const response = await fetch('scenarios_api.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        hideModal();
                        showToast(result.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showToast(result.message || 'خطایی رخ داد.', 'error');
                    }
                } catch (error) {
                    showToast('خطا در ارتباط با سرور.', 'error');
                } finally {
                    toggleLoading(saveBtn, false);
                }
            });
            document.querySelectorAll('.actions-menu-btn').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        if (menu !== button.nextElementSibling) menu.classList.remove('show');
                    });
                    button.nextElementSibling.classList.toggle('show');
                });
            });
            document.addEventListener('click', () => document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show')));
        });
    </script>
</body>

</html>
