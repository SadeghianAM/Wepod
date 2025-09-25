<?php
// فایل: questions.php (نسخه بازطراحی شده با زبان طراحی جدید و امتیازدهی سفارشی)
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');
require_once __DIR__ . '/../../db/database.php';
// کوئری بهینه‌سازی شده برای دریافت اطلاعات سوالات به همراه دسته‌بندی و تعداد گزینه‌ها
$stmt = $pdo->query("
    SELECT
        q.id,
        q.question_text,
        q.category,
        q.points_correct,
        q.points_incorrect,
        COUNT(a.id) AS answer_count
    FROM Questions q
    LEFT JOIN Answers a ON q.id = a.question_id
    GROUP BY q.id, q.question_text, q.category, q.points_correct, q.points_incorrect
    ORDER BY q.id DESC
");
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>داشبورد مدیریت سوالات</title>
    <style>
        /* All CSS styles from your original file go here... */
        /* ... for brevity, the CSS is omitted, but it should be the same as your file */
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
            direction: rtl;
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

        header,
        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 10;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
        }

        header {
            min-height: var(--header-h)
        }

        footer {
            min-height: var(--footer-h);
            font-size: .85rem
        }

        header h1 {
            font-weight: 700;
            font-size: clamp(1rem, 2.2vw, 1.2rem);
            white-space: nowrap;
            max-width: 60vw;
            text-overflow: ellipsis;
            overflow: hidden;
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
            text-align: center;
            margin: 0;
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

        /* Dashboard Styles */
        .page-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .search-box {
            position: relative;
            width: 300px;
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

        .question-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .question-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            transition: all .2s ease;
        }

        .question-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .question-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .question-card-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-color);
        }

        .question-card-meta {
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

        .category-badge {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            padding: .25rem .6rem;
            border-radius: 12px;
            font-size: .8rem;
            font-weight: 600;
        }

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
        .form-group textarea {
            width: 100%;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        /* ⭐ New style for score fields container */
        .score-fields {
            display: flex;
            gap: 1rem;
        }

        .score-fields .form-group {
            flex: 1;
        }

        /* Answer Option Styles */
        .answer-option {
            position: relative;
            margin-bottom: .75rem;
        }

        .answer-label {
            display: flex;
            align-items: center;
            padding: .75rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all .2s;
        }

        .answer-label:hover {
            border-color: #ccc;
        }

        .answer-correct-radio {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .radio-custom {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #ccc;
            display: grid;
            place-items: center;
            margin-left: .75rem;
            transition: border-color .2s;
        }

        .radio-custom::before {
            content: '';
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: var(--primary-color);
            transform: scale(0);
            transition: transform .2s ease-in-out;
        }

        .answer-text {
            flex-grow: 1;
            border: none;
            background: none;
            font-size: 1rem;
            padding: 0;
            outline: none;
        }

        .answer-correct-radio:checked+.answer-label {
            border-color: var(--primary-color);
            background-color: var(--primary-light);
        }

        .answer-correct-radio:checked+.answer-label .radio-custom {
            border-color: var(--primary-color);
        }

        .answer-correct-radio:checked+.answer-label .radio-custom::before {
            transform: scale(1);
        }

        /* Toast Notification Styles */
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
    <div id="header-placeholder"></div>
    <main>
        <div class="page-toolbar">
            <div>
                <h2 class="page-title" style="margin: 0;">بانک سوالات</h2>
                <p class="page-subtitle">سوالات آزمون را از اینجا مدیریت، ویرایش یا حذف کنید.</p>
            </div>
            <div style="display: flex; gap: 1rem; align-items:center;">
                <div class="search-box">
                    <input type="text" id="question-search-input" placeholder="جستجوی سوال یا دسته‌بندی...">
                </div>
                <button id="add-new-question-btn" class="btn btn-primary">➕ <span>سوال جدید</span></button>
            </div>
        </div>

        <?php if (empty($questions)): ?>
            <div class="empty-state">
                <h2>هنوز هیچ سوالی نساخته‌اید! 🙁</h2>
                <p>برای شروع، اولین سوال خود را ایجاد کرده و در آزمون‌ها از آن استفاده کنید.</p>
                <button id="add-new-question-btn-empty" class="btn btn-primary">ایجاد اولین سوال</button>
            </div>
        <?php else: ?>
            <div id="questions-grid" class="question-card-grid">
                <?php foreach ($questions as $question): ?>
                    <div class="question-card" data-search-term="<?= htmlspecialchars(strtolower($question['question_text'] . ' ' . $question['category'])) ?>">
                        <div class="question-card-header">
                            <h3><?= htmlspecialchars($question['question_text']) ?></h3>
                            <div class="actions-menu">
                                <button class="actions-menu-btn">...</button>
                                <ul class="dropdown-menu">
                                    <li><a href="#" onclick="editQuestion(<?= $question['id'] ?>)">ویرایش</a></li>
                                    <li><a href="#" onclick="deleteQuestion(<?= $question['id'] ?>)" class="delete-action">حذف</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="question-card-meta">
                            <span class="meta-item">
                                📚 <span class="category-badge"><?= htmlspecialchars($question['category'] ?: 'عمومی') ?></span>
                            </span>
                            <span class="meta-item">
                                📝 <span><?= $question['answer_count'] ?> گزینه</span>
                            </span>
                            <span class="meta-item">
                                ✅ <span style="color: #28a745;">امتیاز مثبت: <?= htmlspecialchars($question['points_correct']) ?></span>
                            </span>
                            <span class="meta-item">
                                ❌ <span style="color: #dc3545;">نمره منفی: <?= htmlspecialchars($question['points_incorrect']) ?></span>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <div id="modal-overlay" class="modal-overlay">
        <div id="modal-form" class="modal-form">
            <h2 id="form-title" class="page-title">افزودن سوال جدید</h2>
            <form id="question-form">
                <input type="hidden" id="question-id">
                <input type="hidden" id="action">
                <div class="form-group">
                    <label for="question-text">متن سوال:</label>
                    <textarea id="question-text" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="question-category">دسته‌بندی (اختیاری):</label>
                    <input type="text" id="question-category" placeholder="مثال: عمومی، فنی، شخصیت‌شناسی">
                </div>
                <div class="score-fields">
                    <div class="form-group">
                        <label for="points-correct">امتیاز پاسخ صحیح:</label>
                        <input type="number" id="points-correct" step="0.25" value="1" required>
                    </div>
                    <div class="form-group">
                        <label for="points-incorrect">نمره منفی (مقدار کسری):</label>
                        <input type="number" id="points-incorrect" step="0.25" value="1" required>
                    </div>
                </div>

                <h3>گزینه‌ها (حداقل ۲ گزینه الزامی است):</h3>
                <div id="answers-container"></div>
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
        // Global functions for card buttons
        async function editQuestion(id) {
            document.dispatchEvent(new CustomEvent('openEditModal', {
                detail: {
                    id
                }
            }));
        }

        async function deleteQuestion(id) {
            if (confirm('آیا از حذف این سوال مطمئن هستید؟ تمام پاسخ‌های ثبت شده به این سوال نیز حذف خواهند شد.')) {
                const formData = new FormData();
                formData.append('action', 'delete_question');
                formData.append('id', id);
                const response = await fetch('questions_api.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                const toastContainer = document.getElementById('toast-container');
                const showToast = (message, type = 'success') => {
                    const toast = document.createElement('div');
                    toast.className = `toast ${type}`;
                    toast.textContent = message;
                    toastContainer.appendChild(toast);
                    setTimeout(() => toast.remove(), 4000);
                };

                if (result.success) {
                    showToast('سوال با موفقیت حذف شد.');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(result.message || 'خطا در حذف سوال', 'error');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const modalOverlay = document.getElementById('modal-overlay');
            const form = document.getElementById('question-form');
            const formTitle = document.getElementById('form-title');
            const saveBtn = document.getElementById('save-btn');
            const toastContainer = document.getElementById('toast-container');

            const showModal = () => modalOverlay.classList.add('visible');
            const hideModal = () => modalOverlay.classList.remove('visible');

            const showToast = (message, type = 'success') => {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.textContent = message;
                toastContainer.appendChild(toast);
                setTimeout(() => toast.remove(), 4000);
            };

            const toggleLoading = (button, isLoading) => {
                button.disabled = isLoading;
                button.classList.toggle('loading', isLoading);
            };

            const addAnswerInput = (answer = {}, index) => {
                const uniqueId = `ans-radio-${Date.now()}-${index}`;
                const div = document.createElement('div');
                div.className = 'answer-option';
                div.innerHTML = `
                    <input type="radio" name="correct_answer_radio" class="answer-correct-radio" id="${uniqueId}" ${answer.is_correct == 1 ? 'checked' : ''}>
                    <label for="${uniqueId}" class="answer-label">
                        <span class="radio-custom"></span>
                        <input type="text" class="answer-text" placeholder="متن گزینه ${index + 1}..." value="${answer.answer_text || ''}" required>
                    </label>
                `;
                document.getElementById('answers-container').appendChild(div);
            };

            const openAddModal = () => {
                form.reset();
                document.getElementById('answers-container').innerHTML = '';
                document.getElementById('question-id').value = '';
                document.getElementById('action').value = 'create_question';
                document.getElementById('points-correct').value = '1'; // ⭐ Reset to default
                document.getElementById('points-incorrect').value = '1'; // ⭐ Reset to default
                formTitle.textContent = 'افزودن سوال جدید';
                for (let i = 0; i < 4; i++) {
                    addAnswerInput({}, i);
                }
                showModal();
            };

            document.getElementById('add-new-question-btn')?.addEventListener('click', openAddModal);
            document.getElementById('add-new-question-btn-empty')?.addEventListener('click', openAddModal);

            document.addEventListener('openEditModal', async (e) => {
                const {
                    id
                } = e.detail;
                const response = await fetch(`questions_api.php?action=get_question&id=${id}`);
                const data = await response.json();
                if (data.success) {
                    const q = data.question;
                    form.reset();
                    document.getElementById('answers-container').innerHTML = '';
                    document.getElementById('question-id').value = q.id;
                    document.getElementById('action').value = 'update_question';
                    document.getElementById('question-text').value = q.question_text;
                    document.getElementById('question-category').value = q.category;
                    // ⭐ Populate score fields
                    document.getElementById('points-correct').value = q.points_correct;
                    document.getElementById('points-incorrect').value = q.points_incorrect;

                    formTitle.textContent = 'ویرایش سوال';
                    let answers = q.answers.length < 4 ? [...q.answers, ...Array(4 - q.answers.length).fill({})] : q.answers;
                    answers.slice(0, 4).forEach((ans, i) => addAnswerInput(ans, i));
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

                const answers = Array.from(document.querySelectorAll('.answer-option'))
                    .map(option => ({
                        text: option.querySelector('.answer-text').value.trim(),
                        is_correct: option.querySelector('.answer-correct-radio').checked ? 1 : 0
                    }))
                    .filter(a => a.text !== '');

                if (answers.length < 2) {
                    showToast('حداقل باید دو گزینه با متن معتبر وارد کنید.', 'error');
                    toggleLoading(saveBtn, false);
                    return;
                }
                if (answers.filter(a => a.is_correct).length === 0) {
                    showToast('لطفاً یک پاسخ صحیح را مشخص کنید.', 'error');
                    toggleLoading(saveBtn, false);
                    return;
                }

                // ⭐ Get score data from the form
                const data = {
                    id: document.getElementById('question-id').value,
                    text: document.getElementById('question-text').value,
                    category: document.getElementById('question-category').value,
                    points_correct: document.getElementById('points-correct').value,
                    points_incorrect: document.getElementById('points-incorrect').value,
                    answers: answers
                };
                const action = document.getElementById('action').value;
                const response = await fetch(`questions_api.php?action=${action}`, {
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

            // --- Dashboard Search ---
            const searchInput = document.getElementById('question-search-input');
            const questionsGrid = document.getElementById('questions-grid');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    const cards = questionsGrid.querySelectorAll('.question-card');
                    cards.forEach(card => {
                        if (card.dataset.searchTerm.includes(searchTerm)) {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }

            // --- Dashboard Kebab Menu ---
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
