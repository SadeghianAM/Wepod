<?php
// فایل: questions.php (نسخه نهایی با تغییرات درخواستی)

// دو خط زیر بر اساس درخواست شما ویرایش شد
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');

require_once 'database.php';

$stmt = $pdo->query("SELECT id, question_text FROM Questions ORDER BY id DESC");
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت سوالات</title>
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

        .item-list-container {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
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

        /* Modal Styles */
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

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-group textarea {
            resize: none;
        }

        .form-actions {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        /* New Answer Option Styles */
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
            transition: border-color .2s, background-color .2s;
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
        <div class="item-list-container">
            <div class="item-list-header">
                <div>
                    <h1 class="page-title" style="margin-bottom: 0;">مدیریت سوالات</h1>
                    <p class="page-subtitle" style="margin-bottom: 0;">سوالات آزمون را از اینجا اضافه، ویرایش یا حذف کنید.</p>
                </div>
                <button id="add-new-question-btn" class="btn btn-primary"><span class="btn-text">افزودن سوال جدید</span></button>
            </div>
            <div id="questions-list" class="item-list">
                <?php foreach ($questions as $question): ?>
                    <div class="list-item" id="question-item-<?= $question['id'] ?>">
                        <p><?= htmlspecialchars($question['question_text']) ?></p>
                        <div>
                            <button class="btn btn-secondary" onclick="editQuestion(<?= $question['id'] ?>)">ویرایش</button>
                            <button class="btn btn-danger" onclick="deleteQuestion(<?= $question['id'] ?>)">حذف</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
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
                    <label for="question-category">دسته‌بندی:</label>
                    <input type="text" id="question-category" required>
                </div>
                <h3>گزینه‌ها:</h3>
                <div id="answers-container"></div>
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
    <div id="footer-placeholder"></div>

    <script src="/js/header.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalOverlay = document.getElementById('modal-overlay');
            const form = document.getElementById('question-form');
            const formTitle = document.getElementById('form-title');
            const saveBtn = document.getElementById('save-btn');

            const showModal = () => modalOverlay.classList.add('visible');
            const hideModal = () => modalOverlay.classList.remove('visible');

            const showToast = (message, type = 'success') => {
                const toastContainer = document.getElementById('toast-container');
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
                        <input type="text" class="answer-text" placeholder="متن گزینه..." value="${answer.answer_text || ''}" required>
                    </label>
                `;
                document.getElementById('answers-container').appendChild(div);
            };

            const createQuestionListItem = (question) => {
                const item = document.createElement('div');
                item.className = 'list-item';
                item.id = `question-item-${question.id}`;
                item.innerHTML = `
                    <p>${question.question_text}</p>
                    <div>
                        <button class="btn btn-secondary" onclick="editQuestion(${question.id})">ویرایش</button>
                        <button class="btn btn-danger" onclick="deleteQuestion(${question.id})">حذف</button>
                    </div>
                `;
                return item;
            };

            window.editQuestion = async (id) => {
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
                    formTitle.textContent = 'ویرایش سوال';
                    let answers = q.answers.length < 4 ? [...q.answers, ...Array(4 - q.answers.length).fill({})] : q.answers;
                    answers.forEach((ans, i) => addAnswerInput(ans, i));
                    showModal();
                } else {
                    showToast(data.message || 'خطا در دریافت اطلاعات', 'error');
                }
            };

            window.deleteQuestion = async (id) => {
                if (confirm('آیا از حذف این سوال مطمئن هستید؟')) {
                    const formData = new FormData();
                    formData.append('action', 'delete_question');
                    formData.append('id', id);
                    const response = await fetch('questions_api.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        const itemToRemove = document.getElementById(`question-item-${id}`);
                        itemToRemove.classList.add('removing');
                        setTimeout(() => itemToRemove.remove(), 300);
                        showToast('سوال با موفقیت حذف شد.');
                    } else {
                        showToast(result.message || 'خطا در حذف سوال', 'error');
                    }
                }
            };

            document.getElementById('add-new-question-btn').addEventListener('click', () => {
                form.reset();
                document.getElementById('answers-container').innerHTML = '';
                document.getElementById('question-id').value = '';
                document.getElementById('action').value = 'create_question';
                formTitle.textContent = 'افزودن سوال جدید';
                for (let i = 0; i < 4; i++) {
                    addAnswerInput({}, i);
                }
                showModal();
            });

            document.getElementById('cancel-btn').addEventListener('click', hideModal);
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) hideModal();
            });

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                toggleLoading(saveBtn, true);

                const answers = Array.from(document.querySelectorAll('.answer-option')).map(option => ({
                    text: option.querySelector('.answer-text').value,
                    is_correct: option.querySelector('.answer-correct-radio').checked ? 1 : 0
                }));
                if (answers.filter(a => a.is_correct).length === 0) {
                    showToast('لطفاً یک پاسخ صحیح را مشخص کنید.', 'error');
                    toggleLoading(saveBtn, false);
                    return;
                }

                const data = {
                    id: document.getElementById('question-id').value,
                    text: document.getElementById('question-text').value,
                    category: document.getElementById('question-category').value,
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
                    if (action === 'create_question') {
                        const newListItem = createQuestionListItem(result.question);
                        document.getElementById('questions-list').prepend(newListItem);
                    } else {
                        const itemToUpdate = document.getElementById(`question-item-${data.id}`);
                        itemToUpdate.querySelector('p').textContent = data.text;
                    }
                    hideModal();
                    showToast('عملیات با موفقیت انجام شد.');
                } else {
                    showToast(result.message || 'خطایی رخ داد.', 'error');
                }
                toggleLoading(saveBtn, false);
            });
        });
    </script>
</body>

</html>
