<?php
// فایل: quizzes.php (نسخه نهایی با قابلیت تخصیص)
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');
require_once 'database.php';

// خواندن تمام آزمون‌ها
$stmt_quizzes = $pdo->query("SELECT id, title FROM Quizzes ORDER BY id DESC");
$quizzes = $stmt_quizzes->fetchAll(PDO::FETCH_ASSOC);

// خواندن و گروه‌بندی سوالات بر اساس دسته‌بندی
$stmt_questions = $pdo->query("SELECT id, question_text, category FROM Questions ORDER BY category, id");
$questions_by_category = [];
foreach ($stmt_questions->fetchAll(PDO::FETCH_ASSOC) as $question) {
    $category = $question['category'] ?: 'بدون دسته‌بندی';
    $questions_by_category[$category][] = $question;
}

// خواندن تمام تیم‌ها
$stmt_teams = $pdo->query("SELECT id, team_name FROM Teams ORDER BY team_name");
$teams = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);

// خواندن تمام کاربران
$stmt_users = $pdo->query("SELECT id, name FROM Users ORDER BY name");
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت آزمون‌ها</title>
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

        .btn-success {
            background-color: #28a745;
            color: white;
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

        .modal-form form {
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
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
        }

        .questions-grid {
            display: grid;
            max-height: 250px;
            overflow-y: auto;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
            border: 1px solid var(--border-color);
            padding: 10px;
            border-radius: 5px;
        }

        .category-group {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .category-group legend {
            font-weight: 600;
            padding: 0 .5rem;
            color: var(--primary-dark);
        }

        .form-actions {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            flex-shrink: 0;
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
            <span id="user-info">خوش آمدید, <?= htmlspecialchars($claims['name']) ?></span>
        <?php endif; ?>
    </header>
    <main>
        <div class="item-list-container">
            <div class="item-list-header">
                <div>
                    <h1 class="page-title" style="margin-bottom: 0;">مدیریت آزمون‌ها</h1>
                    <p class="page-subtitle" style="margin-bottom: 0;">آزمون‌ها را تعریف کرده و به کاربران تخصیص دهید.</p>
                </div>
                <button id="add-new-quiz-btn" class="btn btn-primary"><span class="btn-text">افزودن آزمون جدید</span></button>
            </div>
            <div id="quizzes-list" class="item-list">
                <?php foreach ($quizzes as $quiz): ?>
                    <div class="list-item" id="quiz-item-<?= $quiz['id'] ?>">
                        <p><?= htmlspecialchars($quiz['title']) ?></p>
                        <div>
                            <button class="btn btn-secondary" onclick="editQuiz(<?= $quiz['id'] ?>)">ویرایش</button>
                            <a href="results.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-success">مشاهده نتایج</a>
                            <button class="btn btn-danger" onclick="deleteQuiz(<?= $quiz['id'] ?>)">حذف</button>
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
            <h2 id="form-title" class="page-title">افزودن آزمون جدید</h2>
            <form id="quiz-form">
                <div class="form-group">
                    <label for="quiz-title">عنوان آزمون:</label>
                    <input type="text" id="quiz-title" required>
                </div>
                <div class="form-group">
                    <label for="quiz-description">توضیحات:</label>
                    <textarea id="quiz-description" rows="2"></textarea>
                </div>
                <h3>سوالات آزمون:</h3>
                <p class="page-subtitle" style="margin-bottom: 1rem; font-size: .9rem;">دقیقاً ۱۰ سوال از حداقل ۴ دسته‌بندی مختلف انتخاب کنید.</p>
                <div id="questions-container" class="questions-grid">
                    <?php foreach ($questions_by_category as $category => $questions_in_cat): ?>
                        <fieldset class="category-group">
                            <legend><?= htmlspecialchars($category) ?></legend>
                            <?php foreach ($questions_in_cat as $question): ?>
                                <label><input type="checkbox" name="questions" value="<?= $question['id'] ?>" data-category="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($question['question_text']) ?></label>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endforeach; ?>
                </div>
                <hr style="margin: 1.5rem 0;">
                <h3>تخصیص آزمون (اختیاری)</h3>
                <p class="page-subtitle" style="margin-bottom: 1rem; font-size: .9rem;">اگر گزینه‌ای انتخاب نشود، آزمون برای همه در دسترس خواهد بود.</p>
                <div class="form-group">
                    <label>تخصیص به تیم‌ها:</label>
                    <div id="teams-container" class="questions-grid">
                        <?php foreach ($teams as $team): ?>
                            <label><input type="checkbox" name="teams" value="<?= $team['id'] ?>"><?= htmlspecialchars($team['team_name']) ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label>تخصیص به کاربران خاص:</label>
                    <div id="users-container" class="questions-grid">
                        <?php foreach ($users as $user): ?>
                            <label><input type="checkbox" name="users" value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <input type="hidden" id="quiz-id">
                <input type="hidden" id="action">
            </form>
            <div class="form-actions">
                <button type="submit" form="quiz-form" id="save-btn" class="btn btn-primary"><span class="btn-text">ذخیره</span><span class="spinner"></span></button>
                <button type="button" id="cancel-btn" class="btn">انصراف</button>
            </div>
        </div>
    </div>
    <div id="toast-container"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalOverlay = document.getElementById('modal-overlay');
            const form = document.getElementById('quiz-form');
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

            const createQuizListItem = (quiz) => {
                const item = document.createElement('div');
                item.className = 'list-item';
                item.id = `quiz-item-${quiz.id}`;
                item.innerHTML = `
                    <p>${quiz.title}</p>
                    <div>
                        <button class="btn btn-secondary" onclick="editQuiz(${quiz.id})">ویرایش</button>
                        <a href="results.php?quiz_id=${quiz.id}" class="btn btn-success">مشاهده نتایج</a>
                        <button class="btn btn-danger" onclick="deleteQuiz(${quiz.id})">حذف</button>
                    </div>`;
                return item;
            };

            window.editQuiz = async (id) => {
                const response = await fetch(`quizzes_api.php?action=get_quiz&id=${id}`);
                const data = await response.json();
                if (data.success) {
                    const quiz = data.quiz;
                    form.reset();
                    formTitle.textContent = 'ویرایش آزمون';
                    document.getElementById('quiz-id').value = quiz.id;
                    document.getElementById('action').value = 'update_quiz';
                    document.getElementById('quiz-title').value = quiz.title;
                    document.getElementById('quiz-description').value = quiz.description;

                    document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                    quiz.questions.forEach(qId => {
                        const cb = document.querySelector(`input[name="questions"][value="${qId}"]`);
                        if (cb) cb.checked = true;
                    });
                    quiz.assigned_teams.forEach(tId => {
                        const cb = document.querySelector(`input[name="teams"][value="${tId}"]`);
                        if (cb) cb.checked = true;
                    });
                    quiz.assigned_users.forEach(uId => {
                        const cb = document.querySelector(`input[name="users"][value="${uId}"]`);
                        if (cb) cb.checked = true;
                    });

                    showModal();
                } else {
                    showToast(data.message, 'error');
                }
            };

            window.deleteQuiz = async (id) => {
                if (confirm('آیا از حذف این آزمون مطمئن هستید؟ تمام تخصیص‌های آن نیز حذف خواهد شد.')) {
                    const formData = new FormData();
                    formData.append('action', 'delete_quiz');
                    formData.append('id', id);
                    const response = await fetch('quizzes_api.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        const itemToRemove = document.getElementById(`quiz-item-${id}`);
                        itemToRemove.classList.add('removing');
                        setTimeout(() => itemToRemove.remove(), 300);
                        showToast('آزمون با موفقیت حذف شد.');
                    } else {
                        showToast(result.message, 'error');
                    }
                }
            };

            document.getElementById('add-new-quiz-btn').addEventListener('click', () => {
                form.reset();
                formTitle.textContent = 'افزودن آزمون جدید';
                document.getElementById('quiz-id').value = '';
                document.getElementById('action').value = 'create_quiz';
                document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                showModal();
            });

            document.getElementById('cancel-btn').addEventListener('click', hideModal);
            modalOverlay.addEventListener('click', e => {
                if (e.target === modalOverlay) hideModal();
            });

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                toggleLoading(saveBtn, true);

                const questionCheckboxes = document.querySelectorAll('input[name="questions"]:checked');
                if (questionCheckboxes.length !== 10) {
                    showToast('تعداد سوالات انتخابی باید دقیقاً ۱۰ مورد باشد.', 'error');
                    toggleLoading(saveBtn, false);
                    return;
                }
                const categories = new Set(Array.from(questionCheckboxes).map(cb => cb.dataset.category));
                if (categories.size < 4) {
                    showToast('سوالات باید حداقل از ۴ دسته‌بندی مختلف انتخاب شوند.', 'error');
                    toggleLoading(saveBtn, false);
                    return;
                }

                const data = {
                    id: document.getElementById('quiz-id').value,
                    title: document.getElementById('quiz-title').value,
                    description: document.getElementById('quiz-description').value,
                    questions: Array.from(questionCheckboxes).map(cb => parseInt(cb.value)),
                    assigned_teams: Array.from(document.querySelectorAll('input[name="teams"]:checked')).map(cb => parseInt(cb.value)),
                    assigned_users: Array.from(document.querySelectorAll('input[name="users"]:checked')).map(cb => parseInt(cb.value))
                };

                const action = document.getElementById('action').value;
                const response = await fetch(`quizzes_api.php?action=${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    if (action === 'create_quiz') {
                        const newListItem = createQuizListItem(result.quiz);
                        document.getElementById('quizzes-list').prepend(newListItem);
                    } else {
                        const itemToUpdate = document.getElementById(`quiz-item-${data.id}`);
                        itemToUpdate.querySelector('p').textContent = data.title;
                    }
                    hideModal();
                    showToast('عملیات با موفقیت انجام شد.');
                } else {
                    showToast(result.message, 'error');
                }
                toggleLoading(saveBtn, false);
            });
        });
    </script>
</body>

</html>
