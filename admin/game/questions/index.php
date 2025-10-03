<?php
require_once __DIR__ . '/../../../auth/require-auth.php';
$claims = requireAuth('admin', '/../../auth/login.html');
require_once __DIR__ . '/../../../db/database.php';

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
            --success-light: #e9f7eb;
            --info-light: #e8f6f8;
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
            padding: 0 2rem;
            box-shadow: var(--shadow-sm);
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

        .btn:hover {
            transform: translateY(-2px);
            filter: brightness(0.92);
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

        .action-button {
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 8px;
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

        .table-container {
            background-color: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .questions-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
        }

        .questions-table th,
        .questions-table td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .questions-table tr:last-child td {
            border-bottom: none;
        }

        .questions-table thead {
            background-color: var(--bg-color);
        }

        .questions-table th {
            font-weight: 600;
            color: var(--secondary-text);
            font-size: 0.85rem;
        }

        .questions-table tbody tr:hover {
            background-color: var(--primary-light);
        }

        .question-text-cell {
            max-width: 450px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 500;
        }

        .category-badge {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            padding: .25rem .75rem;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
            display: inline-block;
        }

        .points-cell.correct {
            color: var(--success-color);
            font-weight: 600;
        }

        .points-cell.incorrect {
            color: var(--danger-color);
            font-weight: 600;
        }

        .actions-cell {
            text-align: left;
            white-space: nowrap;
        }

        .actions-cell .btn {
            margin: 0 0.2rem;
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
            margin-bottom: .5rem;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .empty-state p {
            margin-bottom: 1.5rem;
            color: var(--secondary-text);
        }

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
            font-size: 0.9rem;
            color: var(--secondary-text);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            font-size: 1rem;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius);
            background: var(--card-bg);
            transition: border-color .2s, box-shadow .2s;
        }

        .form-group input:focus-visible,
        .form-group textarea:focus-visible {
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
        }

        .score-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .answer-option {
            position: relative;
            margin-bottom: .75rem;
        }

        .answer-label {
            display: flex;
            align-items: center;
            padding: .75rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius);
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

        .btn.loading .btn-text {
            opacity: 0;
        }

        .btn .spinner {
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            opacity: 0;
            transition: opacity .2s ease;
        }

        .btn.loading .spinner {
            opacity: 1;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
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
                <h1 class="page-title">بانک سوالات</h1>
                <p class="page-subtitle">سوالات آزمون را از اینجا مدیریت، ویرایش یا حذف کنید.</p>
            </div>
            <div style="display: flex; gap: 1rem; align-items:center; flex-wrap: wrap;">
                <div class="search-box">
                    <input type="text" id="question-search-input" placeholder="جستجوی سوال یا دسته‌بندی...">
                </div>
                <button id="add-new-question-btn" class="btn btn-primary">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="M12 5v14" />
                    </svg>
                    <span>سوال جدید</span>
                </button>
            </div>
        </div>

        <?php if (empty($questions)): ?>
            <div class="empty-state">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" x2="12" y1="9" y2="13" />
                    <line x1="12" x2="12.01" y1="17" y2="17" />
                </svg>
                <h2>هنوز هیچ سوالی نساخته‌اید!</h2>
                <p>برای شروع، اولین سوال خود را ایجاد کرده و در آزمون‌ها از آن استفاده کنید.</p>
                <button id="add-new-question-btn-empty" class="btn btn-primary">ایجاد اولین سوال</button>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="questions-table">
                    <thead>
                        <tr>
                            <th>متن سوال</th>
                            <th>دسته‌بندی</th>
                            <th>امتیاز مثبت</th>
                            <th>نمره منفی</th>
                            <th>تعداد گزینه‌ها</th>
                            <th class="actions-cell">عملیات</th>
                        </tr>
                    </thead>
                    <tbody id="questions-tbody">
                        <?php foreach ($questions as $question): ?>
                            <tr data-search-term="<?= htmlspecialchars(strtolower($question['question_text'] . ' ' . $question['category'])) ?>">
                                <td data-label="متن سوال" class="question-text-cell" title="<?= htmlspecialchars($question['question_text']) ?>">
                                    <?= htmlspecialchars($question['question_text']) ?>
                                </td>
                                <td data-label="دسته‌بندی">
                                    <span class="category-badge"><?= htmlspecialchars($question['category'] ?: 'عمومی') ?></span>
                                </td>
                                <td data-label="امتیاز مثبت" class="points-cell correct"><?= htmlspecialchars($question['points_correct']) ?></td>
                                <td data-label="نمره منفی" class="points-cell incorrect"><?= htmlspecialchars($question['points_incorrect']) ?></td>
                                <td data-label="تعداد گزینه‌ها"><?= $question['answer_count'] ?></td>
                                <td data-label="عملیات" class="actions-cell">
                                    <button class="btn btn-info action-button" onclick="editQuestion(<?= $question['id'] ?>)" title="ویرایش">
                                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                    </button>
                                    <button class="btn btn-danger action-button" onclick="deleteQuestion(<?= $question['id'] ?>)" title="حذف">
                                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </button>
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
            <h2 id="form-title" class="page-title" style="font-size: 1.5rem;">افزودن سوال جدید</h2>
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
                        <div class="spinner"></div>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast-container"></div>
    <div id="footer-placeholder"></div>

    <script src="/js/header.js"></script>
    <script>
        function showToast(message, type = 'success', duration = 4000) {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            toastContainer.appendChild(toast);
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

        async function editQuestion(id) {
            document.dispatchEvent(new CustomEvent('openEditModal', {
                detail: {
                    id
                }
            }));
        }

        async function deleteQuestion(id) {
            showConfirmation('آیا از حذف این سوال و تمام گزینه‌های آن مطمئن هستید؟', async () => {
                const formData = new FormData();
                formData.append('action', 'delete_question');
                formData.append('id', id);

                try {
                    const response = await fetch('questions_api.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    showToast(result.message, result.success ? 'success' : 'error');
                    if (result.success) {
                        setTimeout(() => window.location.reload(), 1200);
                    }
                } catch (error) {
                    showToast('خطا در ارتباط با سرور.', 'error');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const modalOverlay = document.getElementById('modal-overlay');
            const form = document.getElementById('question-form');
            const formTitle = document.getElementById('form-title');
            const saveBtn = document.getElementById('save-btn');

            const showModal = () => modalOverlay.classList.add('visible');
            const hideModal = () => modalOverlay.classList.remove('visible');
            const toggleLoading = (button, isLoading) => {
                button.disabled = isLoading;
                if (button.querySelector('.spinner')) {
                    button.classList.toggle('loading', isLoading);
                }
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
                    </label>`;
                document.getElementById('answers-container').appendChild(div);
            };

            const openAddModal = () => {
                form.reset();
                document.getElementById('answers-container').innerHTML = '';
                document.getElementById('question-id').value = '';
                document.getElementById('action').value = 'create_question';
                document.getElementById('points-correct').value = '1';
                document.getElementById('points-incorrect').value = '1';
                formTitle.textContent = 'افزودن سوال جدید';
                for (let i = 0; i < 4; i++) addAnswerInput({}, i);
                showModal();
            };

            document.getElementById('add-new-question-btn')?.addEventListener('click', openAddModal);
            document.getElementById('add-new-question-btn-empty')?.addEventListener('click', openAddModal);

            document.addEventListener('openEditModal', async (e) => {
                const {
                    id
                } = e.detail;
                try {
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
                        document.getElementById('points-correct').value = q.points_correct;
                        document.getElementById('points-incorrect').value = q.points_incorrect;
                        formTitle.textContent = 'ویرایش سوال';
                        let answers = q.answers.length < 4 ? [...q.answers, ...Array(4 - q.answers.length).fill({})] : q.answers;
                        answers.slice(0, 4).forEach((ans, i) => addAnswerInput(ans, i));
                        showModal();
                    } else {
                        showToast(data.message || 'خطا در دریافت اطلاعات', 'error');
                    }
                } catch (error) {
                    showToast('خطا در ارتباط با سرور.', 'error');
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

                const data = {
                    id: document.getElementById('question-id').value,
                    text: document.getElementById('question-text').value,
                    category: document.getElementById('question-category').value,
                    points_correct: document.getElementById('points-correct').value,
                    points_incorrect: document.getElementById('points-incorrect').value,
                    answers: answers
                };
                const action = document.getElementById('action').value;

                try {
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
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        showToast(result.message || 'خطایی رخ داد.', 'error');
                    }
                } catch (error) {
                    showToast('خطا در ارتباط با سرور.', 'error');
                } finally {
                    toggleLoading(saveBtn, false);
                }
            });

            const searchInput = document.getElementById('question-search-input');
            const questionsTbody = document.getElementById('questions-tbody');
            if (searchInput && questionsTbody) {
                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase().trim();
                    const rows = questionsTbody.querySelectorAll('tr');
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
