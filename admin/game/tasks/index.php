<?php
// فایل: tasks.php (نسخه کاملاً نهایی و یکپارچه)
require_once __DIR__ . '/../../../auth/require-auth.php';
$claims = requireAuth('admin', '/../../auth/login.html');
require_once __DIR__ . '/../../../db/database.php';

// کوئری برای دریافت لیست تکالیف همراه با نام تیم و تعداد سوالات
$stmt_tasks = $pdo->query("
    SELECT
        t.id,
        t.title,
        t.description,
        tm.team_name,
        (SELECT COUNT(*) FROM TaskQuestions WHERE task_id = t.id) AS question_count
    FROM Tasks t
    JOIN Teams tm ON t.team_id = tm.id
    ORDER BY t.id DESC
");
$tasks = $stmt_tasks->fetchAll(PDO::FETCH_ASSOC);

// خواندن لیست تیم‌ها برای استفاده در مودال
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
            --footer-h: 60px;
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
            flex-shrink: 0;
            min-height: var(--footer-h);
            font-size: .85rem;
        }

        /* --- General Components --- */
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
            position: relative;
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
            text-decoration: none;
        }

        .btn:disabled {
            opacity: .6;
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

        .btn-secondary:hover {
            background-color: #5a6268;
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

        /* --- Card Grid Styles --- */
        .task-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
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
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-color);
        }

        .task-card-meta {
            display: flex;
            flex-direction: column;
            gap: .75rem;
            margin-bottom: 1.5rem;
            flex-grow: 1;
            color: var(--secondary-text);
            font-size: .9rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: .5rem;
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

        .task-card-actions {
            display: flex;
            justify-content: flex-end;
        }

        /* --- Kebab Menu --- */
        .actions-menu {
            position: relative;
        }

        .actions-menu-btn {
            background: none;
            border: none;
            padding: .25rem .5rem;
            cursor: pointer;
            border-radius: 8px;
            font-size: 1.2rem;
            line-height: 1;
            font-weight: bold;
        }

        .actions-menu-btn:hover {
            background-color: var(--bg-color);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            left: 0;
            top: 100%;
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: var(--shadow-md);
            list-style: none;
            padding: .5rem 0;
            width: 120px;
            z-index: 10;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-menu a {
            display: block;
            padding: .5rem 1rem;
            font-size: .9rem;
            color: inherit;
            text-decoration: none;
        }

        .dropdown-menu a:hover {
            background-color: var(--bg-color);
        }

        .dropdown-menu .delete-action {
            color: #dc3545;
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

        /* --- Modal Styles --- */
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
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
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

        /* --- Question Editor Styles (Inside Modal) --- */
        #question-list {
            margin-top: 1.5rem;
        }

        .question-item {
            display: flex;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: .5rem;
            background-color: #fdfdfd;
            transition: background-color 0.3s;
        }

        .question-item.editing {
            background-color: var(--primary-light);
            border-color: var(--primary-color);
        }

        .question-item-content {
            flex-grow: 1;
        }

        .question-item-actions {
            display: flex;
            gap: .75rem;
        }

        .btn-icon {
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0.25rem;
            line-height: 1;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .btn-icon:hover {
            background-color: #0000001a;
        }

        .question-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        #add-question-form {
            margin-top: 2rem;
            padding: 1.5rem;
            border: 2px dashed var(--primary-light);
            border-radius: 8px;
            background-color: var(--primary-light);
        }

        #add-question-form-actions {
            display: flex;
            gap: 0.5rem;
        }

        #image-preview {
            max-width: 100px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }

        /* --- Toast Notification --- */
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
                <p class="page-subtitle">تکالیف را از اینجا مدیریت، ویرایش یا حذف کنید.</p>
            </div>
            <div style="display: flex; gap: 1rem; align-items:center;">
                <div class="search-box">
                    <input type="text" id="task-search-input" placeholder="جستجوی تکلیف یا تیم...">
                </div>
                <button id="add-new-task-btn" class="btn btn-primary">➕ <span>تکلیف جدید</span></button>
            </div>
        </div>

        <?php if (empty($tasks)) : ?>
            <div class="empty-state">
                <h2>هنوز هیچ تکلیفی نساخته‌اید! 🙁</h2>
                <p>برای شروع، اولین تکلیف خود را برای تیم‌ها ایجاد کنید.</p>
                <button id="add-new-task-btn-empty" class="btn btn-primary">ایجاد اولین تکلیف</button>
            </div>
        <?php else : ?>
            <div id="tasks-grid" class="task-card-grid">
                <?php foreach ($tasks as $task) : ?>
                    <div class="task-card" data-search-term="<?= htmlspecialchars(strtolower($task['title'] . ' ' . $task['team_name'])) ?>">
                        <div class="task-card-header">
                            <h3><?= htmlspecialchars($task['title']) ?></h3>
                            <div class="actions-menu">
                                <button class="actions-menu-btn">...</button>
                                <ul class="dropdown-menu">
                                    <li><a href="#" onclick="editTask(<?= $task['id'] ?>)">✏️ ویرایش</a></li>
                                    <li><a href="#" onclick="deleteTask(<?= $task['id'] ?>)" class="delete-action">🗑️ حذف</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="task-card-meta">
                            <span class="meta-item">
                                🏢 <span class="team-badge"><?= htmlspecialchars($task['team_name']) ?></span>
                            </span>
                            <span class="meta-item">
                                📝 <span><?= $task['question_count'] ?> سوال</span>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <div id="modal-overlay" class="modal-overlay">
        <div id="modal-form" class="modal-form">
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1rem;">
                <h2 id="form-title" class="page-title">افزودن تکلیف جدید</h2>
                <span id="step-indicator">مرحله ۱ از ۲</span>
            </div>
            <div class="modal-form-content">
                <div id="step-1" class="form-step active-step">
                    <p class="page-subtitle" style="margin-bottom: 1.5rem;">ابتدا اطلاعات کلی تکلیف را وارد کنید.</p>
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
                            <option value="">یک تیم را انتخاب کنید...</option>
                            <?php foreach ($all_teams as $team) : ?>
                                <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['team_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="step-2" class="form-step">
                    <p class="page-subtitle" style="margin-bottom: 1rem;">سوالات تکلیف را یک به یک اضافه یا ویرایش کنید.</p>
                    <div id="question-list"></div>

                    <form id="add-question-form">
                        <h4 id="question-form-title">افزودن سوال جدید</h4>
                        <div class="form-group">
                            <label for="question-text">متن سوال:</label>
                            <textarea id="question-text" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="question-image">تصویر سوال (اختیاری):</label>
                            <input type="file" id="question-image" accept="image/*">
                            <img id="image-preview" src="#" alt="پیش‌نمایش تصویر" />
                        </div>
                        <div id="add-question-form-actions">
                            <button type="submit" id="add-question-btn" class="btn btn-primary">➕ افزودن این سوال</button>
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
                        <span class="btn-text">✅ ذخیره</span>
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
        // --- Global Functions ---
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }

        async function deleteTask(id) {
            if (confirm('آیا از حذف این تکلیف مطمئن هستید؟ تمام سوالات و پاسخ‌های مرتبط نیز حذف خواهد شد.')) {
                const formData = new FormData();
                formData.append('action', 'delete_task');
                formData.append('id', id);

                try {
                    const response = await fetch('tasks_api.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    showToast(result.message, result.success ? 'success' : 'error');
                    if (result.success) {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } catch (error) {
                    showToast('خطا در ارتباط با سرور.', 'error');
                }
            }
        }

        async function editTask(id) {
            document.dispatchEvent(new CustomEvent('openModalForEdit', {
                detail: {
                    id
                }
            }));
        }

        document.addEventListener('DOMContentLoaded', () => {
            // --- Elements ---
            const modalOverlay = document.getElementById('modal-overlay');
            const modalForm = document.getElementById('modal-form');
            const formTitle = document.getElementById('form-title');
            const saveBtn = document.getElementById('save-btn');
            const nextBtn = document.getElementById('next-btn');
            const prevBtn = document.getElementById('prev-btn');
            const cancelBtn = document.getElementById('cancel-btn');
            const stepIndicator = document.getElementById('step-indicator');
            const steps = document.querySelectorAll('.form-step');

            // --- Question Form Elements ---
            const addQuestionForm = document.getElementById('add-question-form');
            const questionList = document.getElementById('question-list');
            const questionText = document.getElementById('question-text');
            const questionImageInput = document.getElementById('question-image');
            const imagePreview = document.getElementById('image-preview');
            const questionFormTitle = document.getElementById('question-form-title');
            const addQuestionBtn = document.getElementById('add-question-btn');
            const cancelEditBtn = document.getElementById('cancel-edit-btn');

            // --- State ---
            let currentStep = 1;
            const totalSteps = steps.length;
            let currentlyEditingIndex = null;
            let taskData = {
                id: null,
                title: '',
                description: '',
                team_id: '',
                questions: []
            };

            // --- Helper Functions ---
            const showModal = () => modalOverlay.classList.add('visible');
            const hideModal = () => modalOverlay.classList.remove('visible');
            const toggleLoading = (button, isLoading) => {
                button.disabled = isLoading;
                button.classList.toggle('loading', isLoading);
            };

            // --- Multi-step Modal Logic ---
            const updateFormSteps = () => {
                steps.forEach(step => step.classList.toggle('active-step', parseInt(step.id.split('-')[1]) === currentStep));
                stepIndicator.textContent = `مرحله ${currentStep} از ${totalSteps}`;
                prevBtn.style.display = currentStep > 1 ? 'inline-flex' : 'none';
                nextBtn.style.display = currentStep < totalSteps ? 'inline-flex' : 'none';
                saveBtn.style.display = currentStep === totalSteps ? 'inline-flex' : 'none';
            };

            const validateStep = (stepNumber) => {
                if (stepNumber === 1) {
                    if (!document.getElementById('task-title').value.trim() || !document.getElementById('team-id').value) {
                        showToast('عنوان تکلیف و انتخاب تیم الزامی است.', 'error');
                        return false;
                    }
                }
                if (stepNumber === 2 && taskData.questions.length === 0) {
                    showToast('تکلیف باید حداقل یک سوال داشته باشد.', 'error');
                    return false;
                }
                return true;
            };

            nextBtn.addEventListener('click', () => {
                if (validateStep(currentStep) && currentStep < totalSteps) {
                    taskData.title = document.getElementById('task-title').value.trim();
                    taskData.description = document.getElementById('task-description').value.trim();
                    taskData.team_id = document.getElementById('team-id').value;
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

            // --- Question Management Logic ---
            const renderQuestions = () => {
                questionList.innerHTML = '';
                if (taskData.questions.length === 0) {
                    questionList.innerHTML = '<p style="text-align: center; color: var(--secondary-text); margin: 1rem 0;">هنوز سوالی اضافه نشده است.</p>';
                    return;
                }
                taskData.questions.forEach((q, index) => {
                    const item = document.createElement('div');
                    item.className = 'question-item';
                    if (index === currentlyEditingIndex) item.classList.add('editing');

                    let imageHtml = '';
                    if (q.existing_image_url) {
                        imageHtml = `<img src="${q.existing_image_url}" alt="تصویر سوال">`;
                    } else if (q.file) {
                        imageHtml = `<img src="${URL.createObjectURL(q.file)}" alt="پیش‌نمایش">`;
                    }

                    item.innerHTML = `
                        ${imageHtml}
                        <div class="question-item-content">
                            <span><strong>سوال ${index + 1}:</strong> ${q.text.substring(0, 80)}${q.text.length > 80 ? '...' : ''}</span>
                        </div>
                        <div class="question-item-actions">
                            <button class="btn-icon btn-edit" data-index="${index}" title="ویرایش سوال">✏️</button>
                            <button class="btn-icon btn-delete" data-index="${index}" title="حذف سوال">🗑️</button>
                        </div>
                    `;
                    questionList.appendChild(item);
                });
            };

            const cancelEditing = () => {
                currentlyEditingIndex = null;
                addQuestionForm.reset();
                imagePreview.style.display = 'none';
                questionFormTitle.textContent = 'افزودن سوال جدید';
                addQuestionBtn.innerHTML = '➕ افزودن این سوال';
                cancelEditBtn.style.display = 'none';
                renderQuestions();
            };

            const startEditing = (index) => {
                currentlyEditingIndex = index;
                const q = taskData.questions[index];

                addQuestionForm.reset(); // FIX: Reset form first to clear file input

                questionText.value = q.text;
                imagePreview.style.display = 'none';
                if (q.existing_image_url) {
                    imagePreview.src = q.existing_image_url;
                    imagePreview.style.display = 'block';
                } else if (q.file) {
                    imagePreview.src = URL.createObjectURL(q.file);
                    imagePreview.style.display = 'block';
                }

                questionFormTitle.textContent = `ویرایش سوال ${index + 1}`;
                addQuestionBtn.textContent = '🔄 بروزرسانی سوال';
                cancelEditBtn.style.display = 'inline-flex';

                renderQuestions();
                addQuestionForm.scrollIntoView({
                    behavior: 'smooth'
                });
            };

            cancelEditBtn.addEventListener('click', cancelEditing);

            addQuestionForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const text = questionText.value.trim();
                const file = questionImageInput.files[0];

                if (!text) {
                    showToast('متن سوال نمی‌تواند خالی باشد.', 'error');
                    return;
                }

                if (currentlyEditingIndex !== null) {
                    const q = taskData.questions[currentlyEditingIndex];
                    q.text = text;
                    if (file) {
                        q.file = file;
                        q.existing_image_url = null;
                    }
                    showToast(`سوال ${currentlyEditingIndex + 1} بروزرسانی شد.`);
                } else {
                    taskData.questions.push({
                        id: null,
                        text,
                        file,
                        existing_image_url: null
                    });
                }
                cancelEditing();
            });

            questionImageInput.addEventListener('change', (e) => {
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

            questionList.addEventListener('click', (e) => {
                const button = e.target.closest('.btn-icon');
                if (!button) return;
                const index = parseInt(button.dataset.index, 10);
                if (button.classList.contains('btn-delete')) {
                    if (confirm(`آیا از حذف سوال ${index + 1} مطمئن هستید؟`)) {
                        taskData.questions.splice(index, 1);
                        if (index === currentlyEditingIndex) cancelEditing();
                        renderQuestions();
                        showToast('سوال با موفقیت حذف شد.');
                    }
                } else if (button.classList.contains('btn-edit')) {
                    startEditing(index);
                }
            });

            // --- Modal Opening / Submission ---
            const resetFormAndState = () => {
                document.getElementById('task-title').value = '';
                document.getElementById('task-description').value = '';
                document.getElementById('team-id').value = '';
                addQuestionForm.reset();
                taskData = {
                    id: null,
                    title: '',
                    description: '',
                    team_id: '',
                    questions: []
                };
                currentlyEditingIndex = null;
                cancelEditing();
            };

            const openAddModal = () => {
                resetFormAndState();
                formTitle.textContent = 'افزودن تکلیف جدید';
                currentStep = 1;
                updateFormSteps();
                showModal();
            };

            document.getElementById('add-new-task-btn')?.addEventListener('click', openAddModal);
            document.getElementById('add-new-task-btn-empty')?.addEventListener('click', openAddModal);

            document.addEventListener('openModalForEdit', async (e) => {
                resetFormAndState();
                const taskId = e.detail.id;
                modalForm.style.opacity = '0.5'; // Indicate loading

                try {
                    const response = await fetch(`tasks_api.php?action=get_task&id=${taskId}`);
                    const data = await response.json();
                    if (data.success) {
                        const task = data.task;
                        formTitle.textContent = 'ویرایش تکلیف';
                        document.getElementById('task-title').value = task.title;
                        document.getElementById('task-description').value = task.description;
                        document.getElementById('team-id').value = task.team_id;

                        taskData.id = task.id;
                        taskData.questions = task.questions.map(q => ({
                            id: q.id,
                            text: q.question_text,
                            file: null,
                            existing_image_url: q.image_url || null
                        }));

                        renderQuestions();
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
                formData.append('action', taskData.id ? 'update_task' : 'create_task');
                if (taskData.id) formData.append('id', taskData.id);
                formData.append('title', taskData.title);
                formData.append('description', taskData.description);
                formData.append('team_id', taskData.team_id);

                taskData.questions.forEach((q, index) => {
                    formData.append(`questions_text[${index}]`, q.text);
                    formData.append(`questions_ids[${index}]`, q.id || '');
                    if (q.file) {
                        formData.append(`questions_images[${index}]`, q.file);
                    }
                });

                try {
                    const response = await fetch('tasks_api.php', {
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

            // --- Dashboard Search & Kebab Menu ---
            const searchInput = document.getElementById('task-search-input');
            const tasksGrid = document.getElementById('tasks-grid');
            if (searchInput && tasksGrid) {
                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    const cards = tasksGrid.querySelectorAll('.task-card');
                    cards.forEach(card => {
                        card.style.display = card.dataset.searchTerm.includes(searchTerm) ? 'flex' : 'none';
                    });
                });
            }

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
