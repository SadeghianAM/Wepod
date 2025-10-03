<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
require_once __DIR__ . '/../db/database.php';

$task_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$task_id) {
    die("شناسه تکلیف نامعتبر است.");
}
$user_id = $claims['sub'];

// Fetch Task Details
$stmt_task = $pdo->prepare("SELECT title, description FROM Tasks WHERE id = ?");
$stmt_task->execute([$task_id]);
$task = $stmt_task->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    die("تکلیف یافت نشد.");
}

// Fetch All Questions for the Task
$stmt_questions = $pdo->prepare("SELECT id, question_text FROM TaskQuestions WHERE task_id = ? ORDER BY question_order ASC");
$stmt_questions->execute([$task_id]);
$questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);

// Fetch All User Answers for these Questions
$answers = [];
if (!empty($questions)) {
    $question_ids = array_column($questions, 'id');
    $placeholders = implode(',', array_fill(0, count($question_ids), '?'));

    $stmt_answers = $pdo->prepare(
        "SELECT task_question_id, status, feedback FROM TaskAnswers
         WHERE user_id = ? AND task_question_id IN ($placeholders)
         ORDER BY submitted_at DESC"
    );
    $params = array_merge([$user_id], $question_ids);
    $stmt_answers->execute($params);
    $user_answers_raw = $stmt_answers->fetchAll(PDO::FETCH_ASSOC);

    // Keep only the latest answer for each question
    foreach ($user_answers_raw as $answer) {
        if (!isset($answers[$answer['task_question_id']])) {
            $answers[$answer['task_question_id']] = $answer;
        }
    }
}

// Determine the state of each question (completed, active, locked)
$question_states = [];
$previous_question_approved = true;
$approved_count = 0;

foreach ($questions as $question) {
    $question_id = $question['id'];
    $status = $answers[$question_id]['status'] ?? null;
    $state = 'locked'; // Default state

    if ($previous_question_approved) {
        if ($status === 'approved') {
            $state = 'completed';
            $approved_count++;
        } else {
            $state = 'active';
            $previous_question_approved = false;
        }
    }
    $question_states[$question_id] = $state;
}

$total_questions = count($questions);
$is_task_completed = ($total_questions > 0 && $approved_count === $total_questions);

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>تکلیف: <?= htmlspecialchars($task['title']) ?></title>
    <style>
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --primary-light: #e6f7f2;
            --bg-color: #f7f9fa;
            --card-bg: #fff;
            --text-color: #1a1a1a;
            --secondary-text: #555;
            --border-color: #e9e9e9;
            --disabled-color: #ccc;
            --radius: 16px;
            --footer-h: 60px;
            --header-text: #fff;
            --shadow-md: 0 8px 25px rgba(0, 120, 80, .12);
            --status-pending-bg: #fff8e1;
            --status-pending-text: #8d6e00;
            --status-approved-bg: #e8f5e9;
            --status-approved-text: #1b5e20;
            --status-rejected-bg: #ffebee;
            --status-rejected-text: #c62828;
            --feedback-bg: #f3e5f5;
            --feedback-text: #6a1b9a;
        }

        @font-face {
            font-family: "Vazirmatn";
            src: url("/assets/fonts/Vazirmatn[wght].ttf") format("truetype");
            font-weight: 100 900;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Vazirmatn", system-ui, sans-serif;
            /* Font applied to all elements */
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        main {
            flex: 1;
            width: min(900px, 95%);
            padding: 2.5rem 1rem;
            margin-inline: auto;
        }

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
            min-height: var(--footer-h);
            font-size: .85rem;
            justify-content: center;
        }

        .task-container {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
        }

        .task-header h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: .5rem;
        }

        .task-header p {
            font-size: 1rem;
            color: var(--secondary-text);
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .progress-bar {
            background-color: #e9ecef;
            border-radius: .5rem;
            overflow: hidden;
            margin-bottom: 2.5rem;
        }

        .progress-bar-inner {
            height: 10px;
            background-color: var(--primary-color);
            width: 0;
            transition: width .4s ease-in-out;
        }

        .progress-label {
            text-align: center;
            font-size: .9rem;
            font-weight: 600;
            color: var(--secondary-text);
            margin-top: .5rem;
        }

        .question-box {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            transition: opacity .3s;
        }

        .question-box:first-child {
            margin-top: 0;
            padding-top: 0;
            border-top: none;
        }

        .question-box.locked {
            opacity: 0.5;
        }

        .question-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .question-icon {
            font-size: 1.5rem;
        }

        .question-text {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            flex: 1;
        }

        .question-box.locked .question-text {
            color: var(--secondary-text);
        }

        .status-message {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: 1rem;
            border-radius: 10px;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .status-message.pending {
            background-color: var(--status-pending-bg);
            color: var(--status-pending-text);
        }

        .status-message.approved {
            background-color: var(--status-approved-bg);
            color: var(--status-approved-text);
        }

        .status-message.rejected {
            background-color: var(--status-rejected-bg);
            color: var(--status-rejected-text);
        }

        .feedback-box {
            background-color: var(--feedback-bg);
            color: var(--feedback-text);
        }

        textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            min-height: 150px;
            transition: border-color .2s;
            resize: vertical;
        }

        textarea:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .form-actions {
            margin-top: 1.5rem;
            text-align: left;
        }

        .btn {
            display: inline-flex;
            padding: .75rem 1.5rem;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            transition: all .25s ease;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .final-message {
            text-align: center;
            padding: 3rem 0;
        }

        .final-message .icon {
            font-size: 4rem;
        }

        .final-message h2 {
            font-size: 2rem;
            color: var(--primary-dark);
            margin-top: 1rem;
        }

        .final-message p {
            font-size: 1.1rem;
            color: var(--secondary-text);
            margin-top: .5rem;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="task-container">
            <div class="task-header">
                <h1><?= htmlspecialchars($task['title']) ?></h1>
                <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>
            </div>

            <?php if ($is_task_completed): ?>
                <div class="final-message">
                    <div class="icon">🎉</div>
                    <h2>شما این تکلیف را با موفقیت به پایان رسانده‌اید!</h2>
                    <p>خسته نباشید، منتظر تکالیف بعدی باشید.</p>
                </div>
            <?php else: ?>
                <?php if ($total_questions > 0): ?>
                    <div class="progress-bar">
                        <div class="progress-bar-inner" style="width: <?= ($approved_count / $total_questions) * 100 ?>%;"></div>
                    </div>
                    <p class="progress-label">
                        <?= $approved_count ?> از <?= $total_questions ?> سوال تایید شده است.
                    </p>
                <?php endif; ?>

                <?php foreach ($questions as $index => $question):
                    $question_id = $question['id'];
                    $current_state = $question_states[$question_id];
                    $answer_data = $answers[$question_id] ?? null;
                    $status = $answer_data['status'] ?? null;
                    $feedback = $answer_data['feedback'] ?? null;
                ?>
                    <div class="question-box <?= $current_state ?>">
                        <div class="question-header">
                            <div class="question-icon">
                                <?php if ($current_state === 'completed') echo '✅'; ?>
                                <?php if ($current_state === 'active') echo '📝'; ?>
                                <?php if ($current_state === 'locked') echo '🔒'; ?>
                            </div>
                            <h2 class="question-text">سوال <?= $index + 1 ?>: <?= htmlspecialchars($question['question_text']); ?></h2>
                        </div>

                        <?php if ($current_state === 'active'): ?>
                            <?php if ($status === 'rejected'): ?>
                                <p class="status-message rejected">
                                    <span>⚠️</span>
                                    <span>پاسخ شما نیاز به بازبینی دارد. لطفاً با توجه به بازخورد، پاسخ خود را اصلاح و مجدداً ارسال کنید.</span>
                                </p>
                                <?php if (!empty($feedback)): ?>
                                    <div class="status-message feedback-box">
                                        <span>💬</span>
                                        <div>
                                            <strong>بازخورد ادمین:</strong>
                                            <p style="margin-top: .5rem; line-height: 1.7;"><?= nl2br(htmlspecialchars($feedback)); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php elseif ($status === 'submitted'): ?>
                                <p class="status-message pending">
                                    <span>⏳</span>
                                    <span>پاسخ شما ارسال شده و منتظر تایید ادمین است. پس از تایید، سوال بعدی برای شما فعال خواهد شد.</span>
                                </p>
                            <?php endif; ?>

                            <?php if ($status === null || $status === 'rejected'): ?>
                                <form action="submit_task_answer.php" method="post">
                                    <input type="hidden" name="task_id" value="<?= $task_id; ?>">
                                    <input type="hidden" name="task_question_id" value="<?= $question_id; ?>">
                                    <textarea name="answer_text" placeholder="پاسخ خود را اینجا بنویسید..." required></textarea>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">ارسال پاسخ</button>
                                    </div>
                                </form>
                            <?php endif; ?>

                        <?php elseif ($current_state === 'completed'): ?>
                            <p class="status-message approved">
                                <span>✔️</span>
                                <span>پاسخ شما برای این سوال تایید شد.</span>
                            </p>
                        <?php endif; ?>

                        <?php // For locked questions, nothing is shown in the body
                        ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js?v=1.0"></script>
</body>

</html>
