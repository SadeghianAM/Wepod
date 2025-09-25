<?php
// فایل: view_attempt.php (نسخه بازطراحی شده)
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin');
require_once __DIR__ . '/../../db/database.php';

$attempt_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$attempt_id) {
    header("Location: results.php");
    exit();
}

// دریافت اطلاعات اولیه آزمون
$stmt_attempt = $pdo->prepare("
    SELECT qa.start_time, u.name AS user_name, q.title AS quiz_title, q.id AS quiz_id
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

// دریافت تمام جزئیات سوالات، پاسخ‌ها و امتیازات
$stmt_details = $pdo->prepare("
    SELECT
        q.id AS question_id, q.question_text, q.points_correct, q.points_incorrect,
        a.id AS answer_id, a.answer_text, a.is_correct,
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

// سازماندهی داده‌ها و محاسبه امتیازات در PHP
$questions_and_answers = [];
$total_earned_points = 0;
$total_max_points = 0;
$processed_questions = [];

foreach ($raw_results as $row) {
    $qid = $row['question_id'];
    if (!isset($questions_and_answers[$qid])) {
        // محاسبه امتیاز کل آزمون فقط یک بار برای هر سوال
        if (!in_array($qid, $processed_questions)) {
            $total_max_points += $row['points_correct'];
            $processed_questions[] = $qid;
        }

        $questions_and_answers[$qid] = [
            'question_text' => $row['question_text'],
            'selected_answer_id' => $row['selected_answer_id'],
            'points_correct' => $row['points_correct'],
            'points_incorrect' => $row['points_incorrect'],
            'points_earned' => 0, // امتیاز اولیه برای این سوال صفر است
            'answers' => []
        ];
    }

    // افزودن پاسخ فعلی به لیست پاسخ‌های سوال
    $questions_and_answers[$qid]['answers'][] = [
        'answer_id' => $row['answer_id'],
        'answer_text' => $row['answer_text'],
        'is_correct' => (bool)$row['is_correct']
    ];

    // اگر این پاسخ، همان پاسخی است که کاربر انتخاب کرده، امتیاز را محاسبه کن
    if ($row['selected_answer_id'] == $row['answer_id']) {
        $points = $row['is_correct'] ? $row['points_correct'] : $row['points_incorrect'];
        $questions_and_answers[$qid]['points_earned'] = $points;
        $total_earned_points += $points;
    }
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
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --primary-light: #e6f7f2;
            --bg-color: #f7f9fa;
            --card-bg: #fff;
            --text-color: #1a1a1a;
            --secondary-text: #6c757d;
            --border-color: #e9e9e9;
            --header-text: #fff;
            --radius: 12px;
            --footer-h: 60px;
            --shadow-sm: 0 2px 6px rgba(0, 120, 80, .06);
            --shadow-md: 0 6px 20px rgba(0, 120, 80, .10);
            --correct-color: #28a745;
            --correct-light: #d4edda;
            --incorrect-color: #dc3545;
            --incorrect-light: #f8d7da;
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
            background: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        main {
            width: min(1000px, 100%);
            padding: 2.5rem 2rem;
            margin-inline: auto;
        }

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
            min-height: var(--footer-h);
            font-size: .85rem
        }

        /* --- Header --- */
        .page-header {
            margin-bottom: 2rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            padding: .6rem 1.2rem;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            text-decoration: none;
            color: var(--secondary-text);
            font-weight: 500;
            transition: all .2s;
        }

        .back-link:hover {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            border-color: var(--primary-color);
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: .25rem;
        }

        .page-subtitle {
            color: var(--secondary-text);
            font-size: 1rem;
        }

        /* --- Summary Card --- */
        .attempt-summary {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            padding: 2rem;
            margin-bottom: 2.5rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .summary-item .label {
            font-size: .9rem;
            color: var(--secondary-text);
            margin-bottom: .25rem;
            display: block;
        }

        .summary-item .value {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .score-text {
            display: flex;
            justify-content: space-between;
            margin-bottom: .5rem;
            font-size: 0.9rem;
            color: var(--secondary-text);
        }

        .score-text .earned {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--text-color);
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background-color: var(--bg-color);
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 0.5s ease-in-out;
        }

        /* --- Question Cards --- */
        .question-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
            overflow: hidden;
            /* برای اینکه border-top هدر بیرون نزند */
        }

        .question-header {
            padding: 1rem 1.5rem;
            background-color: var(--primary-light);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .question-text {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-dark);
        }

        .question-score {
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
        }

        .question-score.correct {
            background-color: var(--correct-light);
            color: var(--correct-color);
        }

        .question-score.incorrect {
            background-color: var(--incorrect-light);
            color: var(--incorrect-color);
        }

        .answers-list {
            list-style: none;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .answer-item {
            padding: 1rem;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: .75rem;
            transition: all .2s;
        }

        .answer-item .icon {
            font-size: 1.2rem;
            font-weight: bold;
            width: 20px;
            text-align: center;
        }

        .answer-item.is-correct {
            border-color: var(--correct-color);
            background-color: var(--correct-light);
            color: #155724;
        }

        .answer-item.is-selected.is-wrong {
            border-color: var(--incorrect-color);
            background-color: var(--incorrect-light);
            color: #721c24;
        }

        .user-choice-label {
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: auto;
            /* این المان را به انتهای فلکس می‌برد */
            background-color: rgba(0, 0, 0, 0.05);
            padding: 0.1rem 0.5rem;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="page-header">
            <a href="results.php?quiz_id=<?= $attempt_info['quiz_id'] ?>" class="back-link">
                <span>&larr;</span> بازگشت به لیست نتایج
            </a>
            <h1 class="page-title"><?= $page_title ?></h1>
            <p class="page-subtitle">بررسی دقیق پاسخ‌های کاربر: <?= htmlspecialchars($attempt_info['user_name']) ?></p>
        </div>

        <?php
        $percentage = ($total_max_points > 0) ? ($total_earned_points / $total_max_points) * 100 : 0;
        $score_class = 'incorrect';
        if ($percentage >= 75) $score_class = 'correct';
        ?>
        <div class="attempt-summary">
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="label">آزمون</span>
                    <span class="value"><?= htmlspecialchars($attempt_info['quiz_title']) ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">کاربر</span>
                    <span class="value"><?= htmlspecialchars($attempt_info['user_name']) ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">امتیاز نهایی</span>
                    <span class="value"><?= round($total_earned_points, 2) ?> از <?= round($total_max_points, 2) ?></span>
                </div>
            </div>
            <div class="score-progress">
                <div class="score-text">
                    <span class="earned">عملکرد کلی: <?= round($percentage, 1) ?>٪</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-bar-fill <?= $score_class == 'correct' ? 'question-score correct' : 'question-score incorrect' ?>" style="width: <?= $percentage ?>%;"></div>
                </div>
            </div>
        </div>

        <?php $q_num = 0;
        foreach ($questions_and_answers as $qid => $data): $q_num++; ?>
            <div class="question-card">
                <div class="question-header">
                    <p class="question-text">سوال <?= $q_num ?>: <?= htmlspecialchars($data['question_text']) ?></p>
                    <?php
                    $points_earned = round($data['points_earned'], 1);
                    $is_q_correct = $points_earned > 0;
                    if ($data['selected_answer_id'] === null) {
                        $points_text = "بی‌پاسخ";
                        $q_score_class = "";
                    } else {
                        $points_text = ($points_earned >= 0 ? '+' : '') . $points_earned . ' امتیاز';
                        $q_score_class = $is_q_correct ? 'correct' : 'incorrect';
                    }
                    ?>
                    <span class="question-score <?= $q_score_class ?>"><?= $points_text ?></span>
                </div>
                <ul class="answers-list">
                    <?php foreach ($data['answers'] as $answer):
                        $user_selected_this = ($answer['answer_id'] == $data['selected_answer_id']);
                        $is_correct = $answer['is_correct'];

                        $classes = 'answer-item';
                        $icon = '&nbsp;';
                        $icon_color = '';

                        if ($is_correct) {
                            $classes .= ' is-correct';
                            $icon = '✔';
                            $icon_color = 'var(--correct-color)';
                        }

                        if ($user_selected_this) {
                            $classes .= ' is-selected';
                            if (!$is_correct) {
                                $classes .= ' is-wrong';
                                $icon = '✖';
                                $icon_color = 'var(--incorrect-color)';
                            }
                        }
                    ?>
                        <li class="<?= $classes ?>">
                            <span class="icon" style="color: <?= $icon_color ?>"><?= $icon ?></span>
                            <span><?= htmlspecialchars($answer['answer_text']) ?></span>
                            <?php if ($user_selected_this): ?>
                                <span class="user-choice-label">پاسخ شما</span>
                            <?php endif; ?>
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
