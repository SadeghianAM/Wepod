<?php
// فایل: view_attempt.php (کاملاً اصلاح شده بر اساس ساختار جدید)
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin');
require_once __DIR__ . '/../../db/database.php';

$attempt_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$attempt_id) {
    die("شناسه نتیجه نامعتبر است.");
}

// 1. دریافت اطلاعات اصلی تلاش (این کوئری تغییری نکرده است)
$stmt_attempt = $pdo->prepare("
    SELECT qa.score, qa.start_time, u.name AS user_name, q.title AS quiz_title, q.id AS quiz_id
    FROM QuizAttempts qa
    JOIN Users u ON qa.user_id = u.id
    JOIN Quizzes q ON qa.quiz_id = q.id
    WHERE qa.id = ?
");
$stmt_attempt->execute([$attempt_id]);
$attempt_info = $stmt_attempt->fetch(PDO::FETCH_ASSOC);

if (!$attempt_info) {
    die("نتیجه‌ای با این شناسه یافت نشد.");
}

// 2. **کوئری اصلی اصلاح شده** برای دریافت سوالات، گزینه‌ها و پاسخ کاربر
$stmt_details = $pdo->prepare("
    SELECT
        q.id AS question_id,
        q.question_text,
        a.id AS answer_id,
        a.answer_text,
        a.is_correct,
        ua.selected_answer_id
    FROM QuizQuestions qq
    JOIN Questions q ON qq.question_id = q.id
    JOIN Answers a ON q.id = a.question_id
    LEFT JOIN UserAnswers ua ON q.id = ua.question_id AND ua.attempt_id = :attempt_id
    WHERE qq.quiz_id = :quiz_id
    ORDER BY q.id, a.id
");
$stmt_details->execute([':attempt_id' => $attempt_id, ':quiz_id' => $attempt_info['quiz_id']]);
$raw_results = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

// 3. سازماندهی داده‌ها (منطق یکسان است، فقط نام متغیرها تغییر کرده)
$questions_and_answers = [];
foreach ($raw_results as $row) {
    $qid = $row['question_id'];
    if (!isset($questions_and_answers[$qid])) {
        $questions_and_answers[$qid] = [
            'question_text' => $row['question_text'],
            'selected_answer_id' => $row['selected_answer_id'], // نام جدید
            'answers' => [] // نام جدید
        ];
    }
    $questions_and_answers[$qid]['answers'][] = [ // نام جدید
        'answer_id' => $row['answer_id'], // نام جدید
        'answer_text' => $row['answer_text'], // نام جدید
        'is_correct' => $row['is_correct']
    ];
}
$page_title = "جزئیات آزمون: " . htmlspecialchars($attempt_info['quiz_title']);

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <style>
        /* ... کپی کردن تمام استایل‌های فایل results.php در اینجا ... */
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

        main {
            flex: 1;
            width: min(1000px, 100%);
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
            margin-bottom: 2rem;
        }

        .attempt-summary {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .summary-item {
            text-align: center;
        }

        .summary-item span {
            display: block;
        }

        .summary-item .label {
            font-size: .9rem;
            color: var(--secondary-text);
            margin-bottom: .25rem;
        }

        .summary-item .value {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .question-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }

        .question-text {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .answers-list {
            list-style: none;
        }

        .answer-item {
            padding: 1rem;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: .75rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .answer-item.correct-answer {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .answer-item.wrong-answer {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 2rem;
            padding: .5rem 1rem;
            background-color: #f1f1f1;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
            transition: background-color .2s;
        }

        .back-link:hover {
            background-color: #e0e0e0;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <a href="results.php<?= $attempt_info['quiz_id'] ? '?quiz_id=' . $attempt_info['quiz_id'] : '' ?>" class="back-link">&larr; بازگشت به لیست نتایج</a>

        <h1 class="page-title"><?= $page_title ?></h1>
        <p class="page-subtitle">بررسی پاسخ‌های کاربر: <?= htmlspecialchars($attempt_info['user_name']) ?></p>

        <div class="attempt-summary">
            <div class="summary-item">
                <span class="label">آزمون</span>
                <span class="value"><?= htmlspecialchars($attempt_info['quiz_title']) ?></span>
            </div>
            <div class="summary-item">
                <span class="label">کاربر</span>
                <span class="value"><?= htmlspecialchars($attempt_info['user_name']) ?></span>
            </div>
            <div class="summary-item">
                <span class="label">نمره نهایی</span>
                <span class="value"><?= htmlspecialchars($attempt_info['score']) ?> / ۱۰۰</span>
            </div>
        </div>

        <?php foreach ($questions_and_answers as $qid => $data): ?>
            <div class="question-card">
                <p class="question-text"><?= htmlspecialchars($data['question_text']) ?></p>
                <ul class="answers-list"> <?php foreach ($data['answers'] as $answer): // نام متغیر برای خوانایی بهتر تغییر کرد
                                                $class = '';
                                                // **تغییر:** نام ستون‌ها آپدیت شد
                                                $user_selected_this = ($answer['answer_id'] == $data['selected_answer_id']);

                                                if ($answer['is_correct']) {
                                                    $class = 'correct-answer';
                                                } elseif ($user_selected_this && !$answer['is_correct']) {
                                                    $class = 'wrong-answer';
                                                }
                                            ?>
                        <li class="answer-item <?= $class ?>"> <?= htmlspecialchars($answer['answer_text']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </main>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js"></script>
</body>

</html>
