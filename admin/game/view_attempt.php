<?php
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin');
require_once __DIR__ . '/../../db/database.php';

$attempt_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$attempt_id) {
    header("Location: results.php");
    exit();
}

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

$stats = [
    'correct' => 0,
    'incorrect' => 0,
    'unanswered' => 0,
    'total_questions' => 0
];
$temp_questions = [];
foreach ($raw_results as $row) {
    if (!isset($temp_questions[$row['question_id']])) {
        $stats['total_questions']++;
        $temp_questions[$row['question_id']] = [
            'selected_id' => $row['selected_answer_id'],
            'correct_id' => null
        ];
    }
    if ($row['is_correct']) {
        $temp_questions[$row['question_id']]['correct_id'] = $row['answer_id'];
    }
}

foreach ($temp_questions as $q) {
    if ($q['selected_id'] === null) {
        $stats['unanswered']++;
    } elseif ($q['selected_id'] == $q['correct_id']) {
        $stats['correct']++;
    } else {
        $stats['incorrect']++;
    }
}

$questions_and_answers = [];
$total_earned_points = 0;
$total_max_points = 0;
$processed_questions = [];

foreach ($raw_results as $row) {
    $qid = $row['question_id'];
    if (!isset($questions_and_answers[$qid])) {
        if (!in_array($qid, $processed_questions)) {
            $total_max_points += $row['points_correct'];
            $processed_questions[] = $qid;
        }

        $questions_and_answers[$qid] = [
            'question_text' => $row['question_text'],
            'selected_answer_id' => $row['selected_answer_id'],
            'points_correct' => $row['points_correct'],
            'points_incorrect' => $row['points_incorrect'],
            'points_earned' => 0,
            'answers' => []
        ];
    }

    $questions_and_answers[$qid]['answers'][] = [
        'answer_id' => $row['answer_id'],
        'answer_text' => $row['answer_text'],
        'is_correct' => (bool)$row['is_correct']
    ];

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
            --shadow-sm: 0 2px 6px rgba(0, 120, 80, .06);
            --shadow-md: 0 6px 20px rgba(0, 120, 80, .10);
            --correct-color: #28a745;
            --correct-light: #d4edda;
            --incorrect-color: #dc3545;
            --incorrect-light: #f8d7da;
            --medium-color: #ffc107;
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

        .attempt-summary {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            padding: 2rem;
            margin-bottom: 2.5rem;
            border-top: 5px solid var(--primary-color);
        }

        .summary-main {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .summary-score-visual {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .summary-percentage {
            position: absolute;
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text-color);
        }

        .progress-ring-circle,
        .progress-ring-circle-bg {
            transition: stroke-dashoffset 0.8s ease-out;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }

        .progress-ring-circle-bg {
            stroke: var(--border-color);
        }

        .progress-ring-circle.correct {
            stroke: var(--correct-color);
        }

        .progress-ring-circle.incorrect {
            stroke: var(--incorrect-color);
        }

        .progress-ring-circle.medium {
            stroke: var(--medium-color);
        }

        .summary-details .summary-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: .5rem;
        }

        .summary-details .summary-user,
        .summary-details .summary-final-score {
            font-size: 1rem;
            color: var(--secondary-text);
        }

        .summary-details strong {
            color: var(--text-color);
            font-weight: 600;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            border-top: 1px solid var(--border-color);
            padding-top: 1.5rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            background-color: var(--bg-color);
        }

        .stat-item .stat-value {
            display: block;
            font-size: 1.75rem;
            font-weight: 700;
        }

        .stat-item .stat-label {
            font-size: .85rem;
            color: var(--secondary-text);
        }

        .stat-item.correct .stat-value {
            color: var(--correct-color);
        }

        .stat-item.incorrect .stat-value {
            color: var(--incorrect-color);
        }

        .stat-item.unanswered .stat-value {
            color: #6c757d;
        }

        .stat-item.total .stat-value {
            color: var(--primary-dark);
        }

        .question-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .question-header {
            padding: 1rem 1.5rem;
            background-color: var(--primary-light);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .question-card.unanswered .question-header {
            background-color: #f8f9fa;
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

        .question-score.unanswered-score {
            background-color: #e9ecef;
            color: #6c757d;
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
            font-size: 1.4rem;
            line-height: 1;
        }

        .answer-item:not(.is-correct):not(.is-selected) {
            opacity: 0.7;
        }

        .answer-item.is-correct {
            border-color: var(--correct-color);
            background-color: var(--correct-light);
            color: #155724;
            font-weight: 600;
        }

        .answer-item.is-selected.is-wrong {
            border-color: var(--incorrect-color);
            background-color: var(--incorrect-light);
            color: #721c24;
            opacity: 0.8;
        }

        .user-choice-label {
            font-size: 0.8rem;
            font-weight: 700;
            margin-right: auto;
            background-color: var(--primary-dark);
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
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
        $percentage = ($total_max_points > 0) ? round(($total_earned_points / $total_max_points) * 100) : 0;
        $progress_color_class = 'incorrect';
        if ($percentage >= 75) {
            $progress_color_class = 'correct';
        } elseif ($percentage >= 40) {
            $progress_color_class = 'medium';
        }
        ?>
        <div class="attempt-summary">
            <div class="summary-main">
                <div class="summary-score-visual">
                    <svg class="progress-ring" width="120" height="120">
                        <circle class="progress-ring-circle-bg" stroke-width="10" fill="transparent" r="52" cx="60" cy="60" />
                        <circle class="progress-ring-circle <?= $progress_color_class ?>" stroke-width="10" fill="transparent" r="52" cx="60" cy="60"
                            style="stroke-dasharray: <?= 2 * M_PI * 52 ?>; stroke-dashoffset: <?= (2 * M_PI * 52) * (1 - $percentage / 100) ?>;" />
                    </svg>
                    <span class="summary-percentage"><?= $percentage ?>%</span>
                </div>
                <div class="summary-details">
                    <h2 class="summary-title">خلاصه عملکرد</h2>
                    <p class="summary-user">کاربر: <strong><?= htmlspecialchars($attempt_info['user_name']) ?></strong></p>
                    <p class="summary-final-score">
                        امتیاز نهایی: <strong><?= round($total_earned_points, 2) ?></strong> از <?= round($total_max_points, 2) ?>
                    </p>
                </div>
            </div>
            <div class="summary-stats">
                <div class="stat-item correct">
                    <span class="stat-value"><?= $stats['correct'] ?></span>
                    <span class="stat-label">صحیح</span>
                </div>
                <div class="stat-item incorrect">
                    <span class="stat-value"><?= $stats['incorrect'] ?></span>
                    <span class="stat-label">غلط</span>
                </div>
                <div class="stat-item unanswered">
                    <span class="stat-value"><?= $stats['unanswered'] ?></span>
                    <span class="stat-label">بی‌پاسخ</span>
                </div>
                <div class="stat-item total">
                    <span class="stat-value"><?= $stats['total_questions'] ?></span>
                    <span class="stat-label">کل سوالات</span>
                </div>
            </div>
        </div>

        <?php $q_num = 0;
        foreach ($questions_and_answers as $qid => $data): $q_num++;
            $card_classes = "question-card";
            if ($data['selected_answer_id'] === null) {
                $card_classes .= " unanswered";
            }
        ?>
            <div class="<?= $card_classes ?>">
                <div class="question-header">
                    <p class="question-text">سوال <?= $q_num ?>: <?= htmlspecialchars($data['question_text']) ?></p>
                    <?php
                    $points_earned = round($data['points_earned'], 1);
                    $is_q_correct = $points_earned > 0;
                    if ($data['selected_answer_id'] === null) {
                        $points_text = "بی‌پاسخ";
                        $q_score_class = "unanswered-score";
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
                        $icon = '';
                        $user_choice_text = '';

                        if ($is_correct) {
                            $classes .= ' is-correct';
                            $icon = '✅';
                        }

                        if ($user_selected_this) {
                            $classes .= ' is-selected';
                            $user_choice_text = '<span class="user-choice-label">پاسخ شما</span>';
                            if (!$is_correct) {
                                $classes .= ' is-wrong';
                                $icon = '❌';
                            }
                        }
                    ?>
                        <li class="<?= $classes ?>">
                            <span class="icon"><?= $icon ?></span>
                            <span><?= htmlspecialchars($answer['answer_text']) ?></span>
                            <?= $user_choice_text ?>
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
