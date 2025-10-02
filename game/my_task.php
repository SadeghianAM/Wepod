<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
require_once __DIR__ . '/../db/database.php';

$task_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$task_id) {
    die("شناسه تکلیف نامعتبر است.");
}
$user_id = $claims['sub'];

$stmt_task = $pdo->prepare("SELECT title, description FROM Tasks WHERE id = ?");
$stmt_task->execute([$task_id]);
$task = $stmt_task->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    die("تکلیف یافت نشد.");
}

$stmt_questions = $pdo->prepare("SELECT id, question_text FROM TaskQuestions WHERE task_id = ? ORDER BY question_order ASC");
$stmt_questions->execute([$task_id]);
$questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);

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

    foreach ($user_answers_raw as $answer) {
        if (!isset($answers[$answer['task_question_id']])) {
            $answers[$answer['task_question_id']] = $answer;
        }
    }
}

$is_task_completed = false;
if (!empty($questions)) {
    $last_question_id = end($questions)['id'];
    if (isset($answers[$last_question_id]) && $answers[$last_question_id]['status'] === 'approved') {
        $is_task_completed = true;
    }
}
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
            --radius: 16px;
            --footer-h: 60px;
            --header-text: #fff;
            --shadow-md: 0 8px 25px rgba(0, 120, 80, .12);
            --status-pending-bg: #fff3cd;
            --status-pending-text: #856404;
            --status-approved-bg: #d4edda;
            --status-approved-text: #155724;
            --status-rejected-bg: #f8d7da;
            --status-rejected-text: #721c24;
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
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: linear-gradient(to top, #f3fdf9 0%, #f7f9fa 100%);
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

        .question-box {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }

        .question-box:first-child {
            margin-top: 0;
            padding-top: 0;
            border-top: none;
        }

        .question-text {
            font-size: 1.3rem;
            font-weight: 700;
            line-height: 1.7;
            color: #333;
            margin-bottom: 1.5rem;
        }

        .status-message {
            padding: 1rem;
            border-radius: 10px;
            font-weight: 500;
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

        textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            min-height: 150px;
            transition: border-color .2s;
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
                </div>
            <?php else: ?>
                <?php
                $previous_question_approved = true;
                foreach ($questions as $index => $question):
                    if (!$previous_question_approved) {
                        break;
                    }

                    $question_id = $question['id'];
                    $answer_data = $answers[$question_id] ?? null;
                    $status = $answer_data['status'] ?? null;
                    $feedback = $answer_data['feedback'] ?? null;
                ?>
                    <div class="question-box">
                        <h2 class="question-text">سوال <?= $index + 1 ?>: <?= htmlspecialchars($question['question_text']); ?></h2>

                        <?php if ($status === null || $status === 'rejected'): ?>
                            <?php if ($status === 'rejected'): ?>
                                <p class="status-message rejected">پاسخ قبلی شما رد شد. لطفاً دوباره تلاش کنید.</p>
                                <?php if (!empty($feedback)): ?>
                                    <div class="status-message pending" style="margin-top: 1rem;">
                                        <strong>بازخورد ادمین:</strong>
                                        <p style="margin-top: .5rem;"><?= htmlspecialchars($feedback); ?></p>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <form action="submit_task_answer.php" method="post">
                                <input type="hidden" name="task_id" value="<?= $task_id; ?>">
                                <input type="hidden" name="task_question_id" value="<?= $question_id; ?>">
                                <textarea name="answer_text" placeholder="پاسخ خود را اینجا بنویسید..." required></textarea>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">ارسال پاسخ</button>
                                </div>
                            </form>
                            <?php $previous_question_approved = false; ?>

                        <?php elseif ($status === 'submitted'): ?>
                            <p class="status-message pending">پاسخ شما ارسال شده و منتظر تایید ادمین است.</p>
                            <?php $previous_question_approved = false; ?>

                        <?php elseif ($status === 'approved'): ?>
                            <p class="status-message approved">پاسخ شما برای این سوال تایید شد.</p>
                            <?php $previous_question_approved = true; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js?v=1.0"></script>
</body>

</html>
