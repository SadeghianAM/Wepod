<?php
// فایل: quizzes.php (نسخه نهایی - ظاهر مدرن برای سوالات)
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

        .btn-secondary:hover {
            background-color: #5a6268;
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
            min-height: 350px;
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

        #questions-container {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            padding: 10px;
            border-radius: 8px;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navigation-buttons {
            display: flex;
            gap: 10px;
        }

        .searchable-list-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-bottom: .75rem;
        }

        .searchable-list-controls input[type="text"] {
            flex-grow: 1;
            padding: .5em .8em;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: .9rem;
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

        /* --- NEW: Generic Modern Selection Styles --- */
        .modern-selection-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
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

        /* --- End of new styles --- */

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

        #step-indicator {
            font-size: .9rem;
            color: var(--secondary-text);
            font-weight: 500;
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
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1rem;">
                <h2 id="form-title" class="page-title">افزودن آزمون جدید</h2>
                <span id="step-indicator">مرحله ۱ از ۴</span>
            </div>
            <form id="quiz-form">
                <div class="form-step active-step" data-step="1">
                    <div class="form-group">
                        <label for="quiz-title">عنوان آزمون:</label>
                        <input type="text" id="quiz-title" required>
                    </div>
                    <div class="form-group">
                        <label for="quiz-description">توضیحات:</label>
                        <textarea id="quiz-description" rows="4"></textarea>
                    </div>
                </div>

                <div class="form-step" data-step="2">
                    <h3>سوالات آزمون:</h3>
                    <p class="page-subtitle" style="margin-bottom: 1rem; font-size: .9rem;">سوالات مورد نظر خود را برای آزمون انتخاب کنید (حداقل یک سوال).</p>
                    <div id="questions-container">
                        <?php foreach ($questions_by_category as $category => $questions_in_cat): ?>
                            <fieldset class="category-group">
                                <legend><?= htmlspecialchars($category) ?></legend>
                                <div class="modern-selection-grid">
                                    <?php foreach ($questions_in_cat as $question): ?>
                                        <div class="selectable-item">
                                            <input type="checkbox" name="questions" value="<?= $question['id'] ?>" id="question-<?= $question['id'] ?>" data-category="<?= htmlspecialchars($category) ?>">
                                            <label for="question-<?= $question['id'] ?>"><?= htmlspecialchars($question['question_text']) ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-step" data-step="3">
                    <h3>تخصیص به تیم‌ها (اختیاری)</h3>
                    <p class="page-subtitle" style="margin-bottom: 1rem; font-size: .9rem;">می‌توانید این مرحله را نادیده بگیرید.</p>
                    <div class="searchable-list-controls">
                        <input type="text" id="team-search" placeholder="جستجوی تیم...">
                        <label class="select-all-label"><input type="checkbox" id="select-all-teams"> انتخاب همه</label>
                    </div>
                    <div class="assignment-grid-container">
                        <div id="teams-container" class="modern-selection-grid">
                            <?php foreach ($teams as $team): ?>
                                <div class="selectable-item filterable-item">
                                    <input type="checkbox" name="teams" value="<?= $team['id'] ?>" id="team-<?= $team['id'] ?>">
                                    <label for="team-<?= $team['id'] ?>"><?= htmlspecialchars($team['team_name']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="form-step" data-step="4">
                    <h3>تخصیص به کاربران خاص (اختیاری)</h3>
                    <p class="page-subtitle" style="margin-bottom: 1rem; font-size: .9rem;">اگر در مرحله قبل تیمی انتخاب شده، این افراد علاوه بر آن تیم‌ها به آزمون دسترسی خواهند داشت.</p>
                    <div class="searchable-list-controls">
                        <input type="text" id="user-search" placeholder="جستجوی کاربر...">
                        <label class="select-all-label"><input type="checkbox" id="select-all-users"> انتخاب همه</label>
                    </div>
                    <div class="assignment-grid-container">
                        <div id="users-container" class="modern-selection-grid">
                            <?php foreach ($users as $user): ?>
                                <div class="selectable-item filterable-item">
                                    <input type="checkbox" name="users" value="<?= $user['id'] ?>" id="user-<?= $user['id'] ?>">
                                    <label for="user-<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="quiz-id">
                <input type="hidden" id="action">
            </form>
            <div class="form-actions">
                <button type="button" id="cancel-btn" class="btn">انصراف</button>
                <div class="navigation-buttons">
                    <button type="button" id="prev-btn" class="btn btn-secondary">قبلی</button>
                    <button type="button" id="next-btn" class="btn btn-primary">بعدی</button>
                    <button type="submit" form="quiz-form" id="save-btn" class="btn btn-primary"><span class="btn-text">ذخیره</span><span class="spinner"></span></button>
                </div>
            </div>
        </div>
    </div>
    <div id="toast-container"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- Elements ---
            const modalOverlay = document.getElementById('modal-overlay');
            const form = document.getElementById('quiz-form');
            const formTitle = document.getElementById('form-title');
            const saveBtn = document.getElementById('save-btn');
            const nextBtn = document.getElementById('next-btn');
            const prevBtn = document.getElementById('prev-btn');
            const cancelBtn = document.getElementById('cancel-btn');
            const stepIndicator = document.getElementById('step-indicator');
            const steps = document.querySelectorAll('.form-step');

            // --- State ---
            let currentStep = 1;
            const totalSteps = steps.length;

            // --- Functions ---
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
                item.innerHTML = `<p>${quiz.title}</p><div><button class="btn btn-secondary" onclick="editQuiz(${quiz.id})">ویرایش</button><a href="results.php?quiz_id=${quiz.id}" class="btn btn-success">مشاهده نتایج</a><button class="btn btn-danger" onclick="deleteQuiz(${quiz.id})">حذف</button></div>`;
                return item;
            };

            const updateFormSteps = () => {
                steps.forEach(step => {
                    step.classList.toggle('active-step', parseInt(step.dataset.step) === currentStep);
                });
                stepIndicator.textContent = `مرحله ${currentStep} از ${totalSteps}`;
                prevBtn.style.display = currentStep > 1 ? 'inline-block' : 'none';
                nextBtn.style.display = currentStep < totalSteps ? 'inline-block' : 'none';
                saveBtn.style.display = currentStep === totalSteps ? 'inline-block' : 'none';
            };

            const validateStep = (stepNumber) => {
                if (stepNumber === 1) {
                    const title = document.getElementById('quiz-title').value.trim();
                    if (!title) {
                        showToast('لطفاً عنوان آزمون را وارد کنید.', 'error');
                        return false;
                    }
                }
                if (stepNumber === 2) {
                    const questionCheckboxes = form.querySelectorAll('input[name="questions"]:checked');
                    if (questionCheckboxes.length < 1) {
                        showToast('حداقل یک سوال باید برای آزمون انتخاب شود.', 'error');
                        return false;
                    }
                }
                return true;
            };

            // --- Search & Select All Logic ---
            const setupSearchableList = (searchInputId, selectAllCheckboxId, containerId) => {
                const searchInput = document.getElementById(searchInputId);
                const selectAllCheckbox = document.getElementById(selectAllCheckboxId);
                const container = document.getElementById(containerId);
                const items = container.querySelectorAll('.filterable-item');

                searchInput.addEventListener('input', () => {
                    const searchTerm = searchInput.value.toLowerCase();
                    items.forEach(item => {
                        const text = item.textContent.toLowerCase();
                        item.style.display = text.includes(searchTerm) ? 'block' : 'none';
                    });
                    selectAllCheckbox.checked = false;
                });

                selectAllCheckbox.addEventListener('change', () => {
                    const isChecked = selectAllCheckbox.checked;
                    items.forEach(item => {
                        if (item.style.display !== 'none') {
                            const checkbox = item.querySelector('input[type="checkbox"]');
                            if (checkbox) checkbox.checked = isChecked;
                        }
                    });
                });
            };
            setupSearchableList('team-search', 'select-all-teams', 'teams-container');
            setupSearchableList('user-search', 'select-all-users', 'users-container');

            // --- Event Handlers ---
            nextBtn.addEventListener('click', () => {
                if (validateStep(currentStep)) {
                    if (currentStep < totalSteps) {
                        currentStep++;
                        updateFormSteps();
                    }
                }
            });

            prevBtn.addEventListener('click', () => {
                if (currentStep > 1) {
                    currentStep--;
                    updateFormSteps();
                }
            });

            window.editQuiz = async (id) => {
                showToast('قابلیت ویرایش برای نمایش ساده‌تر غیرفعال شده است. لطفاً یک آزمون جدید بسازید.', 'error');
            };

            window.deleteQuiz = async (id) => {
                if (confirm('آیا از حذف این آزمون مطمئن هستید؟')) {
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
                currentStep = 1;
                updateFormSteps();
                showModal();
            });

            cancelBtn.addEventListener('click', hideModal);
            modalOverlay.addEventListener('click', e => {
                if (e.target === modalOverlay) hideModal();
            });

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                toggleLoading(saveBtn, true);

                const data = {
                    id: document.getElementById('quiz-id').value,
                    title: document.getElementById('quiz-title').value,
                    description: document.getElementById('quiz-description').value,
                    questions: Array.from(form.querySelectorAll('input[name="questions"]:checked')).map(cb => parseInt(cb.value)),
                    assigned_teams: Array.from(form.querySelectorAll('input[name="teams"]:checked')).map(cb => parseInt(cb.value)),
                    assigned_users: Array.from(form.querySelectorAll('input[name="users"]:checked')).map(cb => parseInt(cb.value))
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
                        document.getElementById('quizzes-list').prepend(createQuizListItem(result.quiz));
                    } else {
                        document.getElementById(`quiz-item-${data.id}`).querySelector('p').textContent = data.title;
                    }
                    hideModal();
                    showToast('عملیات با موفقیت انجام شد.');
                } else {
                    showToast(result.message, 'error');
                }
                toggleLoading(saveBtn, false);
            });

            // Initial setup on page load
            updateFormSteps();
        });
    </script>
</body>

</html>
