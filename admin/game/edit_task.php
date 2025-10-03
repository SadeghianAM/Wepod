<?php
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');
require_once __DIR__ . '/../../db/database.php';

$task_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$page_title = $task_id ? "ویرایش تکلیف" : "ایجاد تکلیف جدید";

$stmt_teams = $pdo->query("SELECT id, team_name FROM Teams ORDER BY team_name");
$all_teams = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);
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
            width: min(900px, 100%);
            padding: 2.5rem 2rem;
            margin-inline: auto;
        }

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
            min-height: var(--footer-h);
            font-size: .85rem;
            justify-content: center;
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

        .btn:disabled {
            opacity: .6;
            cursor: not-allowed;
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

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
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

        .wizard-container {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            position: relative;
        }

        .loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
            border-radius: var(--radius);
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid var(--primary-light);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .btn .spinner-sm {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-top-color: #fff;
        }

        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }

        .progress-bar::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--border-color);
            transform: translateY(-50%);
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .5rem;
            z-index: 1;
            background: var(--card-bg);
            padding-inline: 1rem;
            color: var(--secondary-text);
        }

        .step .step-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid var(--border-color);
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            background: #fff;
            transition: all .3s ease;
        }

        .step.active .step-circle {
            border-color: var(--primary-color);
            background-color: var(--primary-color);
            color: #fff;
        }

        .step.active span {
            color: var(--primary-dark);
            font-weight: bold;
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
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

        .step-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            border-top: 1px solid var(--border-color);
            padding-top: 1.5rem;
        }

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
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="wizard-container">
            <div class="loading-overlay" id="loading-overlay" style="display: none;">
                <div class="spinner"></div>
            </div>

            <h2 class="page-title"><?= $page_title ?></h2>

            <div class="progress-bar">
                <div class="step active" id="progress-step-1">
                    <div class="step-circle">1</div>
                    <span>اطلاعات کلی</span>
                </div>
                <div class="step" id="progress-step-2">
                    <div class="step-circle">2</div>
                    <span>تعریف سوالات</span>
                </div>
            </div>

            <div id="step-1" class="form-step active">
                <p>ابتدا اطلاعات کلی تکلیف را وارد کنید.</p>
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
                        <?php foreach ($all_teams as $team): ?>
                            <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['team_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="step-actions">
                    <a href="tasks.php" class="btn btn-secondary">انصراف</a>
                    <button id="next-btn" class="btn btn-primary">مرحله بعد (تعریف سوالات) &larr;</button>
                </div>
            </div>

            <div id="step-2" class="form-step">
                <p>سوالات تکلیف را یک به یک اضافه یا ویرایش کنید. پس از اتمام، دکمه ثبت نهایی را بزنید.</p>
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

                <div class="step-actions">
                    <button id="back-btn" class="btn btn-secondary">&rarr; مرحله قبل</button>
                    <button id="finish-btn" class="btn btn-primary">
                        <span class="btn-text">✅ تایید و ثبت نهایی تکلیف</span>
                        <div class="spinner spinner-sm" style="display: none;"></div>
                    </button>
                </div>
            </div>
        </div>
    </main>
    <div id="toast-container"></div>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const taskId = <?= $task_id ?? 'null' ?>;
            let currentlyEditingIndex = null;

            let taskData = {
                id: taskId,
                title: '',
                description: '',
                team_id: '',
                questions: []
            };

            const step1 = document.getElementById('step-1');
            const step2 = document.getElementById('step-2');
            const nextBtn = document.getElementById('next-btn');
            const backBtn = document.getElementById('back-btn');
            const finishBtn = document.getElementById('finish-btn');
            const addQuestionForm = document.getElementById('add-question-form');
            const questionList = document.getElementById('question-list');
            const questionText = document.getElementById('question-text');
            const questionImageInput = document.getElementById('question-image');
            const imagePreview = document.getElementById('image-preview');
            const loadingOverlay = document.getElementById('loading-overlay');
            const progressStep1 = document.getElementById('progress-step-1');
            const progressStep2 = document.getElementById('progress-step-2');
            const questionFormTitle = document.getElementById('question-form-title');
            const addQuestionBtn = document.getElementById('add-question-btn');
            const cancelEditBtn = document.getElementById('cancel-edit-btn');

            const renderQuestions = () => {
                questionList.innerHTML = '';
                if (taskData.questions.length === 0) {
                    questionList.innerHTML = '<p style="text-align: center; color: var(--secondary-text); margin: 1rem 0;">هنوز سوالی اضافه نشده است.</p>';
                } else {
                    taskData.questions.forEach((q, index) => {
                        const item = document.createElement('div');
                        item.className = 'question-item';
                        if (index === currentlyEditingIndex) {
                            item.classList.add('editing');
                        }

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
                }
            };

            const startEditing = (index) => {
                currentlyEditingIndex = index;
                const q = taskData.questions[index];

                questionText.value = q.text;
                imagePreview.style.display = 'none';
                if (q.existing_image_url) {
                    imagePreview.src = q.existing_image_url;
                    imagePreview.style.display = 'block';
                } else if (q.file) {
                    imagePreview.src = URL.createObjectURL(q.file);
                    imagePreview.style.display = 'block';
                }
                addQuestionForm.reset(); // to clear file input unless we re-select

                questionFormTitle.textContent = `ویرایش سوال ${index + 1}`;
                addQuestionBtn.textContent = '🔄 بروزرسانی سوال';
                cancelEditBtn.style.display = 'inline-flex';

                renderQuestions();
                addQuestionForm.scrollIntoView({
                    behavior: 'smooth'
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

            nextBtn.addEventListener('click', () => {
                taskData.title = document.getElementById('task-title').value.trim();
                taskData.description = document.getElementById('task-description').value.trim();
                taskData.team_id = document.getElementById('team-id').value;

                if (!taskData.title || !taskData.team_id) {
                    showToast('عنوان تکلیف و انتخاب تیم الزامی است.', 'error');
                    return;
                }
                step1.classList.remove('active');
                step2.classList.add('active');
                progressStep1.classList.remove('active');
                progressStep2.classList.add('active');
            });

            backBtn.addEventListener('click', () => {
                step2.classList.remove('active');
                step1.classList.add('active');
                progressStep2.classList.remove('active');
                progressStep1.classList.add('active');
            });

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
                    // Editing existing question
                    const q = taskData.questions[currentlyEditingIndex];
                    q.text = text;
                    if (file) {
                        q.file = file;
                        q.existing_image_url = null; // New file overrides old one
                    }
                    showToast(`سوال ${currentlyEditingIndex+1} بروزرسانی شد.`);
                } else {
                    // Adding new question
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
                    taskData.questions.splice(index, 1);
                    if (index === currentlyEditingIndex) cancelEditing();
                    renderQuestions();
                    showToast('سوال با موفقیت حذف شد.');
                } else if (button.classList.contains('btn-edit')) {
                    startEditing(index);
                }
            });

            finishBtn.addEventListener('click', async () => {
                if (taskData.questions.length === 0) {
                    showToast('تکلیف باید حداقل یک سوال داشته باشد.', 'error');
                    return;
                }

                finishBtn.disabled = true;
                finishBtn.querySelector('.btn-text').style.display = 'none';
                finishBtn.querySelector('.spinner-sm').style.display = 'block';

                const formData = new FormData();
                formData.append('action', taskId ? 'update_task' : 'create_task');
                if (taskId) formData.append('id', taskData.id);
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
                        showToast(result.message);
                        setTimeout(() => window.location.href = 'tasks.php', 1500);
                    } else {
                        showToast(result.message || 'خطایی رخ داد.', 'error');
                    }
                } catch (error) {
                    showToast('خطا در ارتباط با سرور.', 'error');
                } finally {
                    finishBtn.disabled = false;
                    finishBtn.querySelector('.btn-text').style.display = 'inline';
                    finishBtn.querySelector('.spinner-sm').style.display = 'none';
                }
            });

            const loadTaskForEdit = async () => {
                if (!taskId) return;
                loadingOverlay.style.display = 'flex';

                try {
                    const response = await fetch(`tasks_api.php?action=get_task&id=${taskId}`);
                    const data = await response.json();

                    if (data.success) {
                        const task = data.task;
                        document.getElementById('task-title').value = task.title;
                        document.getElementById('task-description').value = task.description;
                        document.getElementById('team-id').value = task.team_id;

                        taskData.title = task.title;
                        taskData.description = task.description;
                        taskData.team_id = task.team_id;

                        task.questions.forEach(q => {
                            taskData.questions.push({
                                id: q.id,
                                text: q.question_text,
                                file: null,
                                existing_image_url: q.image_url || null
                            });
                        });
                        renderQuestions();
                    } else {
                        showToast(data.message || 'خطا در بارگذاری اطلاعات تکلیف.', 'error');
                    }
                } catch (error) {
                    showToast('خطا در ارتباط با سرور.', 'error');
                } finally {
                    loadingOverlay.style.display = 'none';
                }
            };

            loadTaskForEdit();
            renderQuestions();
        });
    </script>
</body>

</html>
