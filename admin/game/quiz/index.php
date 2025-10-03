<?php
require_once __DIR__ . '/../../../auth/require-auth.php';
$claims = requireAuth('admin', '/../../auth/login.html');
require_once __DIR__ . '/../../../db/database.php';

$stmt_quizzes = $pdo->query("
    SELECT
        q.id,
        q.title,
        COUNT(DISTINCT qq.question_id) AS question_count,
        (COUNT(DISTINCT qta.team_id) + COUNT(DISTINCT qua.user_id)) AS assignment_count
    FROM Quizzes q
    LEFT JOIN QuizQuestions qq ON q.id = qq.quiz_id
    LEFT JOIN QuizTeamAssignments qta ON q.id = qta.quiz_id
    LEFT JOIN QuizUserAssignments qua ON q.id = qua.quiz_id
    GROUP BY q.id, q.title
    ORDER BY q.id DESC
");
$quizzes = $stmt_quizzes->fetchAll(PDO::FETCH_ASSOC);

$stmt_questions = $pdo->query("SELECT id, question_text, category FROM Questions ORDER BY category, id");
$questions_by_category = [];
foreach ($stmt_questions->fetchAll(PDO::FETCH_ASSOC) as $question) {
    $category = $question['category'] ?: 'بدون دسته‌بندی';
    $questions_by_category[$category][] = $question;
}
$stmt_teams = $pdo->query("SELECT id, team_name FROM Teams ORDER BY team_name");
$teams = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);
$stmt_users = $pdo->query("SELECT id, name FROM Users ORDER BY name");
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>داشبورد مدیریت آزمون‌ها</title>
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

        a {
            color: inherit;
            text-decoration: none;
            transition: all .2s ease;
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
        }

        .btn:hover {
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

        .quiz-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .quiz-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            transition: all .2s ease;
        }

        .quiz-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .quiz-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .quiz-card-header h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-color);
        }

        .quiz-card-meta {
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
            gap: .6rem;
        }

        .meta-item .icon {
            color: var(--primary-color);
        }

        .quiz-card-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        }

        .dropdown-menu a:hover {
            background-color: var(--bg-color);
        }

        .dropdown-menu .delete-action {
            color: var(--danger-color);
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
                <h1 class="page-title">لیست آزمون‌ها</h1>
                <p class="page-subtitle">آزمون‌های خود را مدیریت، ویرایش یا حذف کنید.</p>
            </div>
            <div style="display: flex; gap: 1rem; align-items:center; flex-wrap: wrap;">
                <div class="search-box">
                    <input type="text" id="quiz-search-input" placeholder="جستجوی آزمون...">
                </div>
                <button id="add-new-quiz-btn" class="btn btn-primary">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="M12 5v14" />
                    </svg>
                    <span>آزمون جدید</span>
                </button>
            </div>
        </div>

        <?php if (empty($quizzes)): ?>
            <div class="empty-state">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" x2="12" y1="9" y2="13" />
                    <line x1="12" x2="12.01" y1="17" y2="17" />
                </svg>
                <h2>هنوز هیچ آزمونی نساخته‌اید!</h2>
                <p>برای شروع، اولین آزمون خود را ایجاد کرده و به کاربران خود تخصیص دهید.</p>
                <button id="add-new-quiz-btn-empty" class="btn btn-primary">ایجاد اولین آزمون</button>
            </div>
        <?php else: ?>
            <div id="quizzes-grid" class="quiz-card-grid">
                <?php foreach ($quizzes as $quiz): ?>
                    <div class="quiz-card" data-title="<?= htmlspecialchars(strtolower($quiz['title'])) ?>">
                        <div class="quiz-card-header">
                            <h3><?= htmlspecialchars($quiz['title']) ?></h3>
                            <div class="actions-menu">
                                <button class="actions-menu-btn">
                                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="1" />
                                        <circle cx="12" cy="5" r="1" />
                                        <circle cx="12" cy="19" r="1" />
                                    </svg>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="#" onclick="editQuiz(event, <?= $quiz['id'] ?>)">
                                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                            </svg>
                                            <span>ویرایش</span></a>
                                    </li>
                                    <li><a href="#" onclick="deleteQuiz(event, <?= $quiz['id'] ?>)" class="delete-action">
                                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18" />
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                            </svg>
                                            <span>حذف</span></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="quiz-card-meta">
                            <span class="meta-item">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="16" y1="13" x2="8" y2="13" />
                                    <line x1="16" y1="17" x2="8" y2="17" />
                                    <line x1="10" y1="9" x2="8" y2="9" />
                                </svg>
                                <span><?= $quiz['question_count'] ?> سوال</span>
                            </span>
                            <span class="meta-item">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                                <?php if ($quiz['assignment_count'] > 0): ?>
                                    <span>تخصیص به <?= $quiz['assignment_count'] ?> گروه/فرد</span>
                                <?php else: ?>
                                    <span>عمومی (برای همه)</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="quiz-card-actions">
                            <a href="results.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-primary" style="width: 100%;">مشاهده نتایج</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <div id="modal-overlay" class="modal-overlay">
        <div id="modal-form" class="modal-form">
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1rem;">
                <h2 id="form-title" class="page-title" style="font-size: 1.5rem;">افزودن آزمون جدید</h2>
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
                <button type="button" id="cancel-btn" class="btn btn-secondary">انصراف</button>
                <div class="navigation-buttons">
                    <button type="button" id="prev-btn" class="btn btn-secondary">قبلی</button>
                    <button type="button" id="next-btn" class="btn btn-primary">بعدی</button>
                    <button type="submit" form="quiz-form" id="save-btn" class="btn btn-primary"><span class="btn-text">ذخیره</span><span class="spinner"></span></button>
                </div>
            </div>
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

        async function editQuiz(event, id) {
            event.preventDefault();
            try {
                const response = await fetch(`quizzes_api.php?action=get_quiz&id=${id}`);
                const data = await response.json();
                if (data.success) {
                    const quiz = data.quiz;
                    document.getElementById('form-title').textContent = 'ویرایش آزمون';
                    document.getElementById('quiz-id').value = quiz.id;
                    document.getElementById('action').value = 'update_quiz';
                    document.getElementById('quiz-title').value = quiz.title;
                    document.getElementById('quiz-description').value = quiz.description;

                    document.querySelectorAll('#quiz-form input[type="checkbox"]').forEach(cb => cb.checked = false);
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

                    document.dispatchEvent(new CustomEvent('openModal', {
                        detail: {
                            startStep: 1
                        }
                    }));
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                showToast('خطا در ارتباط با سرور.', 'error');
            }
        }

        function deleteQuiz(event, id) {
            event.preventDefault();
            showConfirmation('آیا از حذف این آزمون مطمئن هستید؟', async () => {
                const formData = new FormData();
                formData.append('action', 'delete_quiz');
                formData.append('id', id);
                try {
                    const response = await fetch('quizzes_api.php', {
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
            const form = document.getElementById('quiz-form');
            const formTitle = document.getElementById('form-title');
            const saveBtn = document.getElementById('save-btn');
            const nextBtn = document.getElementById('next-btn');
            const prevBtn = document.getElementById('prev-btn');
            const cancelBtn = document.getElementById('cancel-btn');
            const stepIndicator = document.getElementById('step-indicator');
            const steps = document.querySelectorAll('.form-step');
            let currentStep = 1;
            const totalSteps = steps.length;

            const showModal = () => modalOverlay.classList.add('visible');
            const hideModal = () => modalOverlay.classList.remove('visible');
            const toggleLoading = (button, isLoading) => {
                button.disabled = isLoading;
                button.classList.toggle('loading', isLoading);
            };

            const searchInput = document.getElementById('quiz-search-input');
            const quizzesGrid = document.getElementById('quizzes-grid');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase().trim();
                    const cards = quizzesGrid.querySelectorAll('.quiz-card');
                    cards.forEach(card => {
                        card.style.display = card.dataset.title.includes(searchTerm) ? 'flex' : 'none';
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

            const updateFormSteps = () => {
                steps.forEach(step => step.classList.toggle('active-step', parseInt(step.dataset.step) === currentStep));
                stepIndicator.textContent = `مرحله ${currentStep} از ${totalSteps}`;
                prevBtn.style.display = currentStep > 1 ? 'inline-flex' : 'none';
                nextBtn.style.display = currentStep < totalSteps ? 'inline-flex' : 'none';
                saveBtn.style.display = currentStep === totalSteps ? 'inline-flex' : 'none';
            };

            const validateStep = (stepNumber) => {
                if (stepNumber === 1) {
                    if (!document.getElementById('quiz-title').value.trim()) {
                        showToast('لطفاً عنوان آزمون را وارد کنید.', 'error');
                        return false;
                    }
                }
                if (stepNumber === 2) {
                    if (form.querySelectorAll('input[name="questions"]:checked').length < 1) {
                        showToast('حداقل یک سوال باید برای آزمون انتخاب شود.', 'error');
                        return false;
                    }
                }
                return true;
            };

            nextBtn.addEventListener('click', () => {
                if (validateStep(currentStep) && currentStep < totalSteps) {
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

            const setupSearchableList = (searchInputId, selectAllCheckboxId, containerId) => {
                const searchInput = document.getElementById(searchInputId);
                const selectAllCheckbox = document.getElementById(selectAllCheckboxId);
                const container = document.getElementById(containerId);
                const items = container.querySelectorAll('.filterable-item');
                searchInput.addEventListener('input', () => {
                    const searchTerm = searchInput.value.toLowerCase().trim();
                    items.forEach(item => item.style.display = item.textContent.toLowerCase().includes(searchTerm) ? 'block' : 'none');
                    selectAllCheckbox.checked = false;
                });
                selectAllCheckbox.addEventListener('change', () => {
                    items.forEach(item => {
                        if (item.style.display !== 'none') item.querySelector('input[type="checkbox"]').checked = selectAllCheckbox.checked;
                    });
                });
            };
            setupSearchableList('team-search', 'select-all-teams', 'teams-container');
            setupSearchableList('user-search', 'select-all-users', 'users-container');

            const openAddModal = () => {
                form.reset();
                formTitle.textContent = 'افزودن آزمون جدید';
                document.getElementById('quiz-id').value = '';
                document.getElementById('action').value = 'create_quiz';
                currentStep = 1;
                updateFormSteps();
                showModal();
            };

            document.getElementById('add-new-quiz-btn')?.addEventListener('click', openAddModal);
            document.getElementById('add-new-quiz-btn-empty')?.addEventListener('click', openAddModal);
            document.addEventListener('openModal', (e) => {
                currentStep = e.detail.startStep || 1;
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
                try {
                    const response = await fetch(`quizzes_api.php?action=${action}`, {
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
                        showToast(result.message, 'error');
                    }
                } catch (error) {
                    showToast('خطا در ارتباط با سرور.', 'error');
                } finally {
                    toggleLoading(saveBtn, false);
                }
            });
        });
    </script>
</body>

</html>
