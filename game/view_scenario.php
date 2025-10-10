<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
require_once __DIR__ . '/../db/database.php';

$scenario_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$scenario_id) {
    die("شناسه سناریو نامعتبر است.");
}

$user_id = $claims['sub'];

$stmt_scenario = $pdo->prepare("SELECT title, description FROM Scenarios WHERE id = ?");
$stmt_scenario->execute([$scenario_id]);
$scenario = $stmt_scenario->fetch(PDO::FETCH_ASSOC);
if (!$scenario) {
    die("سناریو یافت نشد.");
}

$stmt_challenges = $pdo->prepare("SELECT id, challenge_text, challenge_image FROM Challenges WHERE scenario_id = ? ORDER BY challenge_order ASC");
$stmt_challenges->execute([$scenario_id]);
$challenges = $stmt_challenges->fetchAll(PDO::FETCH_ASSOC);

$answers = [];
if (!empty($challenges)) {
    $challenge_ids = array_column($challenges, 'id');
    $placeholders = implode(',', array_fill(0, count($challenge_ids), '?'));

    $stmt_answers = $pdo->prepare(
        "SELECT challenge_id, status, feedback FROM ChallengeAnswers
         WHERE user_id = ? AND challenge_id IN ($placeholders)
         ORDER BY submitted_at DESC"
    );
    $params = array_merge([$user_id], $challenge_ids);
    $stmt_answers->execute($params);
    $user_answers_raw = $stmt_answers->fetchAll(PDO::FETCH_ASSOC);

    foreach ($user_answers_raw as $answer) {
        if (!isset($answers[$answer['challenge_id']])) {
            $answers[$answer['challenge_id']] = $answer;
        }
    }
}

$challenge_states = [];
$previous_challenge_approved = true;
$approved_count = 0;

foreach ($challenges as $challenge) {
    $challenge_id = $challenge['id'];
    $status = $answers[$challenge_id]['status'] ?? null;
    $state = 'locked';

    if ($previous_challenge_approved) {
        if ($status === 'approved') {
            $state = 'completed';
            $approved_count++;
        } else {
            $state = 'active';
            $previous_challenge_approved = false;
        }
    }
    $challenge_states[$challenge_id] = $state;
}

$total_challenges = count($challenges);
$is_scenario_completed = ($total_challenges > 0 && $approved_count === $total_challenges);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>سناریو: <?= htmlspecialchars($scenario['title']) ?></title>
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

        .attachment-link {
            display: inline-block;
            margin-top: .75rem;
            margin-bottom: 1rem;
            text-decoration: none;
            color: var(--primary-dark);
            font-weight: 600;
            background-color: var(--primary-light);
            padding: .5rem 1rem;
            border-radius: 8px;
            transition: background-color .2s;
        }

        .attachment-link:hover {
            background-color: #d4f3e9;
            color: var(--primary-dark);
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="task-container">
            <div class="task-header">
                <h1><?= htmlspecialchars($scenario['title']) ?></h1>
                <p><?= nl2br(htmlspecialchars($scenario['description'])) ?></p>
            </div>

            <?php if ($is_scenario_completed): ?>
                <div class="final-message">
                    <div class="icon">🎉</div>
                    <h2>شما این سناریو را با موفقیت به پایان رسانده‌اید!</h2>
                    <p>خسته نباشید، منتظر چالش‌های بعدی باشید.</p>
                </div>
            <?php else: ?>
                <?php if ($total_challenges > 0): ?>
                    <div class="progress-bar">
                        <div class="progress-bar-inner" style="width: <?= ($approved_count / $total_challenges) * 100 ?>%;"></div>
                    </div>
                    <p class="progress-label"><?= $approved_count ?> از <?= $total_challenges ?> چالش تایید شده است.</p>
                <?php endif; ?>

                <?php foreach ($challenges as $index => $challenge):
                    $challenge_id = $challenge['id'];
                    $current_state = $challenge_states[$challenge_id];

                    if ($current_state === 'locked') {
                        break;
                    }

                    $answer_data = $answers[$challenge_id] ?? null;
                    $status = $answer_data['status'] ?? null;
                    $feedback = $answer_data['feedback'] ?? null;
                ?>
                    <div class="question-box <?= $current_state ?>">
                        <div class="question-header">
                            <div class="question-icon"><?= $current_state === 'completed' ? '✅' : '📝' ?></div>
                            <h2 class="question-text">چالش <?= $index + 1 ?>: <?= htmlspecialchars($challenge['challenge_text']); ?></h2>
                            <?php if (!empty($challenge['challenge_image'])): ?>
                                <a href="/quiz-img/<?= htmlspecialchars($challenge['challenge_image']) ?>" target="_blank" class="attachment-link">🖼️ مشاهده تصویر پیوست</a>
                            <?php endif; ?>
                        </div>

                        <?php if ($current_state === 'active'): ?>
                            <?php if ($status === 'rejected'): ?>
                                <p class="status-message rejected"><span>⚠️</span><span>پاسخ شما نیاز به بازبینی دارد. لطفاً با توجه به بازخورد، پاسخ خود را اصلاح و مجدداً ارسال کنید.</span></p>
                                <?php if (!empty($feedback)): ?>
                                    <div class="status-message feedback-box">
                                        <span>💬</span>
                                        <div><strong>بازخورد ادمین:</strong>
                                            <p style="margin-top: .5rem; line-height: 1.7;"><?= nl2br(htmlspecialchars($feedback)); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php elseif ($status === 'submitted'): ?>
                                <p class="status-message pending"><span>⏳</span><span>پاسخ شما ارسال شده و منتظر تایید ادمین است. پس از تایید، چالش بعدی برای شما فعال خواهد شد.</span></p>
                            <?php endif; ?>

                            <?php if ($status === null || $status === 'rejected'): ?>
                                <form action="submit_challenge_answer.php" method="post">
                                    <input type="hidden" name="scenario_id" value="<?= $scenario_id; ?>">
                                    <input type="hidden" name="challenge_id" value="<?= $challenge_id; ?>">
                                    <textarea name="answer_text" placeholder="پاسخ خود را اینجا بنویسید..." required></textarea>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">ارسال پاسخ</button>
                                    </div>
                                </form>
                            <?php endif; ?>

                        <?php elseif ($current_state === 'completed'): ?>
                            <p class="status-message approved">
                                <span>✔️</span>
                                <span>پاسخ شما برای این چالش تایید شد.</span>
                            </p>
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
