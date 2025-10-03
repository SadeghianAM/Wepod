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

$stats = ['correct' => 0, 'incorrect' => 0, 'unanswered' => 0, 'total_questions' => 0];
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
    }
}
$total_earned_points = array_sum(array_column($questions_and_answers, 'points_earned'));
$page_title = "جزئیات آزمون: " . htmlspecialchars($attempt_info['quiz_title']);

function toPersianNumber($number)
{
    $persian_digits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($english_digits, $persian_digits, $number);
}
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
            --warning-light: #fff8e7;
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
            background: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        main {
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
            min-height: 60px;
            font-size: .85rem;
            flex-shrink: 0;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .btn {
            padding: .8em 1.5em;
            font-size: .95rem;
            font-weight: 600;
            border: 1.5px solid transparent;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6em;
            text-decoration: none;
        }

        .btn-outline-secondary {
            background-color: transparent;
            border-color: var(--secondary-text);
            color: var(--secondary-text);
        }

        .btn-outline-secondary:hover {
            background-color: var(--secondary-text);
            border-color: var(--secondary-text);
            color: #fff;
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: clamp(1.5rem, 3vw, 2rem);
            margin-top: 1.5rem;
            margin-bottom: .5rem;
        }

        .page-subtitle {
            color: var(--secondary-text);
            font-size: clamp(.95rem, 2.2vw, 1rem);
        }

        .attempt-summary {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            padding: 2rem;
            margin-bottom: 2.5rem;
            border: 1px solid var(--border-color);
        }

        .summary-main {
            display: flex;
            align-items: center;
            gap: 2.5rem;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
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
            stroke: var(--success-color);
        }

        .progress-ring-circle.incorrect {
            stroke: var(--danger-color);
        }

        .progress-ring-circle.medium {
            stroke: var(--warning-color);
        }

        .summary-details {
            flex-grow: 1;
        }

        .summary-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .summary-info {
            font-size: 1rem;
            color: var(--secondary-text);
            display: flex;
            align-items: center;
            gap: 0.6em;
            margin-bottom: 0.5rem;
        }

        .summary-info strong {
            color: var(--text-color);
            font-weight: 600;
        }

        .summary-info .icon {
            color: var(--primary-color);
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            border-radius: var(--radius);
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .5rem;
        }

        .stat-item .icon {
            width: 1.8rem;
            height: 1.8rem;
            stroke-width: 2;
        }

        .stat-item .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-item .stat-label {
            font-size: .85rem;
            color: var(--secondary-text);
        }

        .stat-item.correct .icon,
        .stat-item.correct .stat-value {
            color: var(--success-color);
        }

        .stat-item.incorrect .icon,
        .stat-item.incorrect .stat-value {
            color: var(--danger-color);
        }

        .stat-item.unanswered .icon,
        .stat-item.unanswered .stat-value {
            color: var(--secondary-text);
        }

        .stat-item.total .icon,
        .stat-item.total .stat-value {
            color: var(--primary-dark);
        }

        .icon {
            width: 1.1em;
            height: 1.1em;
            stroke-width: 2.2;
            vertical-align: -0.15em;
        }

        .question-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .question-header {
            padding: 1.25rem 1.5rem;
            background-color: var(--primary-light);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .question-card.status-correct .question-header {
            background-color: var(--success-light);
        }

        .question-card.status-correct .question-text {
            color: var(--success-color);
        }

        .question-card.status-incorrect .question-header {
            background-color: var(--danger-light);
        }

        .question-card.status-incorrect .question-text {
            color: var(--danger-color);
        }

        .question-card.status-unanswered .question-header {
            background-color: var(--bg-color);
        }

        .question-card.status-unanswered .question-text {
            color: var(--secondary-text);
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
            white-space: nowrap;
        }

        .question-score.correct {
            background-color: var(--success-color);
            color: white;
        }

        .question-score.incorrect {
            background-color: var(--danger-color);
            color: white;
        }

        .question-score.unanswered-score {
            background-color: var(--secondary-text);
            color: white;
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
        }

        .answer-item .icon {
            width: 1.4rem;
            height: 1.4rem;
            flex-shrink: 0;
        }

        .answer-item.is-correct {
            border-color: var(--success-color);
            background-color: var(--success-light);
            color: var(--success-color);
            font-weight: 600;
        }

        .answer-item.is-selected.is-wrong {
            border-color: var(--danger-color);
            background-color: var(--danger-light);
            color: var(--danger-color);
        }

        .user-choice-label {
            font-size: 0.8rem;
            font-weight: 700;
            margin-right: auto;
            background-color: var(--primary-dark);
            color: white;
            padding: 0.2rem 0.7rem;
            border-radius: 12px;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="page-header">
            <a href="results.php?quiz_id=<?= $attempt_info['quiz_id'] ?>" class="btn btn-outline-secondary">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12" />
                    <polyline points="12 19 5 12 12 5" />
                </svg>
                <span>بازگشت به لیست نتایج</span>
            </a>
            <h1 class="page-title"><?= $page_title ?></h1>
            <p class="page-subtitle">بررسی دقیق پاسخ‌های کاربر: <?= htmlspecialchars($attempt_info['user_name']) ?></p>
        </div>

        <?php
        $percentage = ($total_max_points > 0) ? round(($total_earned_points / $total_max_points) * 100) : 0;
        $progress_color_class = 'incorrect';
        if ($percentage >= 75) $progress_color_class = 'correct';
        elseif ($percentage >= 40) $progress_color_class = 'medium';
        ?>
        <div class="attempt-summary">
            <div class="summary-main">
                <div class="summary-score-visual">
                    <svg class="progress-ring" width="120" height="120">
                        <circle class="progress-ring-circle-bg" stroke-width="10" fill="transparent" r="52" cx="60" cy="60" />
                        <circle class="progress-ring-circle <?= $progress_color_class ?>" stroke-width="10" fill="transparent" r="52" cx="60" cy="60"
                            style="stroke-dasharray: <?= 2 * M_PI * 52 ?>; stroke-dashoffset: <?= (2 * M_PI * 52) * (1 - $percentage / 100) ?>;" />
                    </svg>
                    <span class="summary-percentage"><?= toPersianNumber($percentage) ?>٪</span>
                </div>
                <div class="summary-details">
                    <h2 class="summary-title">خلاصه عملکرد</h2>
                    <p class="summary-info">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <span>کاربر:</span> <strong><?= htmlspecialchars($attempt_info['user_name']) ?></strong>
                    </p>
                    <p class="summary-info">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                        </svg>
                        <span>امتیاز نهایی:</span> <strong><?= toPersianNumber(round($total_earned_points, 2)) ?></strong> از <?= toPersianNumber(round($total_max_points, 2)) ?>
                    </p>
                </div>
            </div>
            <div class="summary-stats">
                <div class="stat-item correct">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    <span class="stat-value"><?= toPersianNumber($stats['correct']) ?></span>
                    <span class="stat-label">صحیح</span>
                </div>
                <div class="stat-item incorrect">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="15" y1="9" x2="9" y2="15" />
                        <line x1="9" y1="9" x2="15" y2="15" />
                    </svg>
                    <span class="stat-value"><?= toPersianNumber($stats['incorrect']) ?></span>
                    <span class="stat-label">غلط</span>
                </div>
                <div class="stat-item unanswered">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="8" y1="12" x2="16" y2="12" />
                    </svg>
                    <span class="stat-value"><?= toPersianNumber($stats['unanswered']) ?></span>
                    <span class="stat-label">بی‌پاسخ</span>
                </div>
                <div class="stat-item total">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <hash />
                        <line x1="4" y1="9" x2="20" y2="9" />
                        <line x1="4" y1="15" x2="20" y2="15" />
                        <line x1="10" y1="3" x2="8" y2="21" />
                        <line x1="16" y1="3" x2="14" y2="21" />
                    </svg>
                    <span class="stat-value"><?= toPersianNumber($stats['total_questions']) ?></span>
                    <span class="stat-label">کل سوالات</span>
                </div>
            </div>
        </div>

        <?php $q_num = 0;
        foreach ($questions_and_answers as $qid => $data): $q_num++;
            $points_earned = round($data['points_earned'], 2);
            $is_q_correct = false;
            foreach ($data['answers'] as $ans) {
                if ($ans['is_correct'] && $ans['answer_id'] == $data['selected_answer_id']) {
                    $is_q_correct = true;
                    break;
                }
            }

            $card_status_class = "status-unanswered";
            if ($data['selected_answer_id'] !== null) {
                $card_status_class = $is_q_correct ? "status-correct" : "status-incorrect";
            }
        ?>
            <div class="question-card <?= $card_status_class ?>">
                <div class="question-header">
                    <p class="question-text">سوال <?= toPersianNumber($q_num) ?>: <?= htmlspecialchars($data['question_text']) ?></p>
                    <?php
                    if ($data['selected_answer_id'] === null) {
                        $points_text = "بی‌پاسخ";
                        $q_score_class = "unanswered-score";
                    } else {
                        $points_text = ($points_earned >= 0 ? '+' : '') . toPersianNumber($points_earned) . ' امتیاز';
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
                            $icon = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
                        }

                        if ($user_selected_this) {
                            $classes .= ' is-selected';
                            $user_choice_text = '<span class="user-choice-label">پاسخ شما</span>';
                            if (!$is_correct) {
                                $classes .= ' is-wrong';
                                $icon = '<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
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
