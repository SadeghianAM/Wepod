<?php
// فایل: /admin/tasks/view_task_answer.php
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin');
require_once __DIR__ . '/../../db/database.php';

$answer_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$answer_id) {
    header("Location: task_answers.php");
    exit();
}

// کوئری برای دریافت جزئیات کامل پاسخ
$stmt = $pdo->prepare("
    SELECT
        ta.id, ta.answer_text, ta.status, ta.submitted_at, ta.feedback,
        u.name AS user_name,
        tq.question_text,
        t.id AS task_id,
        t.title AS task_title
    FROM TaskAnswers ta
    JOIN Users u ON ta.user_id = u.id
    JOIN TaskQuestions tq ON ta.task_question_id = tq.id
    JOIN Tasks t ON tq.task_id = t.id
    WHERE ta.id = ?
");
$stmt->execute([$answer_id]);
$answer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$answer) {
    die("پاسخی با این شناسه یافت نشد.");
}

$page_title = "بازبینی تکلیف: " . htmlspecialchars($answer['task_title']);

$status_map = [
    'submitted' => ['text' => 'در انتظار بازبینی', 'class' => 'medium', 'icon' => '⏳'],
    'approved' => ['text' => 'تایید شده', 'class' => 'correct', 'icon' => '✅'],
    'rejected' => ['text' => 'رد شده', 'class' => 'incorrect', 'icon' => '❌']
];
$status_info = $status_map[$answer['status']] ?? ['text' => $answer['status'], 'class' => '', 'icon' => ''];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <style>
        /* CSS styles are copied from view_attempt.php for consistency */
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
            --footer-h: 60px;
            --incorrect-light: #f8d7da;
            --medium-color: #ffc107;
            --medium-light: #fff3cd;
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
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 10;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
            min-height: var(--footer-h);
            font-size: .85rem;
            justify-content: center;
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
        .task-summary {
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
            flex-wrap: wrap;
        }

        .summary-status-visual {
            font-size: 4rem;
            flex-shrink: 0;
        }

        .summary-details .summary-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: .5rem;
        }

        .summary-details .summary-user,
        .summary-details .summary-date {
            font-size: 1rem;
            color: var(--secondary-text);
        }

        .summary-details strong {
            color: var(--text-color);
            font-weight: 600;
        }

        .status-badge {
            font-size: 1rem;
            font-weight: 700;
            padding: .4rem 1rem;
            border-radius: 20px;
        }

        .status-badge.correct {
            background-color: var(--correct-light);
            color: var(--correct-color);
        }

        .status-badge.incorrect {
            background-color: var(--incorrect-light);
            color: var(--incorrect-color);
        }

        .status-badge.medium {
            background-color: var(--medium-light);
            color: var(--medium-color);
        }


        /* --- Content Cards --- */
        .content-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .content-header {
            padding: 1rem 1.5rem;
            background-color: var(--primary-light);
            border-bottom: 1px solid var(--border-color);
        }

        .content-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-dark);
        }

        .content-body {
            padding: 1.5rem;
            line-height: 1.8;
        }

        .answer-text-display {
            white-space: pre-wrap;
            background-color: var(--bg-color);
            padding: 1rem;
            border-radius: 8px;
        }

        /* --- Action Section --- */
        .review-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: .75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all .2s;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .btn-approve {
            background-color: var(--correct-color);
            color: white;
        }

        .btn-reject {
            background-color: var(--incorrect-color);
            color: white;
        }

        .btn-delete {
            background-color: #343a40;
            color: white;
        }

        .feedback-display {
            background-color: var(--incorrect-light);
            color: var(--incorrect-color);
            border: 1.5px solid var(--incorrect-color);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
        }

        .feedback-display strong {
            display: block;
            margin-bottom: .5rem;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="page-header">
            <a href="task_answers.php?task_id=<?= $answer['task_id'] ?>" class="back-link">
                <span>&larr;</span> بازگشت به لیست پاسخ‌ها
            </a>
            <h1 class="page-title"><?= $page_title ?></h1>
            <p class="page-subtitle">بررسی پاسخ ارسال‌شده توسط: <?= htmlspecialchars($answer['user_name']) ?></p>
        </div>

        <div class="task-summary">
            <div class="summary-main">
                <div class="summary-status-visual"><?= $status_info['icon'] ?></div>
                <div class="summary-details">
                    <h2 class="summary-title">خلاصه وضعیت</h2>
                    <p class="summary-user">کاربر: <strong><?= htmlspecialchars($answer['user_name']) ?></strong></p>
                    <p class="summary-date">
                        تاریخ ارسال:
                        <strong id="submission-date" data-timestamp="<?= htmlspecialchars($answer['submitted_at']) ?>">...</strong>
                    </p>
                    <div style="margin-top: 1rem;">
                        <span class="status-badge <?= $status_info['class'] ?>"><?= $status_info['text'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="content-header">
                <p class="content-title">سوال تکلیف</p>
            </div>
            <div class="content-body">
                <p><?= htmlspecialchars($answer['question_text']) ?></p>
            </div>
        </div>

        <div class="content-card">
            <div class="content-header">
                <p class="content-title">پاسخ کاربر</p>
            </div>
            <div class="content-body">
                <div class="answer-text-display"><?= nl2br(htmlspecialchars($answer['answer_text'])) ?></div>
            </div>
        </div>

        <div class="content-card">
            <div class="content-header">
                <p class="content-title">عملیات بازبینی</p>
            </div>
            <div class="content-body">
                <?php if ($answer['status'] === 'submitted'): ?>
                    <p style="margin-bottom: 1.5rem;">پاسخ کاربر را تایید یا رد کنید.</p>
                    <div class="review-actions">
                        <button class="btn btn-approve" onclick="reviewAnswer(<?= $answer_id ?>, 'approved')">✅ تایید پاسخ</button>
                        <button class="btn btn-reject" onclick="reviewAnswer(<?= $answer_id ?>, 'rejected')">❌ رد پاسخ</button>
                    </div>
                <?php else: ?>
                    <p>این پاسخ قبلاً بازبینی شده است.</p>

                    <?php if ($answer['status'] === 'rejected' && !empty($answer['feedback'])): ?>
                        <div class="feedback-display">
                            <strong>دلیل رد شدن:</strong>
                            <?= nl2br(htmlspecialchars($answer['feedback'])) ?>
                        </div>
                    <?php endif; ?>

                    <div class="review-actions" style="margin-top: 1.5rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                        <button class="btn btn-delete" onclick="deleteAnswer(<?= $answer_id ?>)">🗑️ حذف کامل این پاسخ</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <div id="footer-placeholder"></div>
    <script src="/js/header.js"></script>
    <script>
        // --- Date Formatting ---
        document.addEventListener('DOMContentLoaded', () => {
            const dateCell = document.getElementById('submission-date');
            if (dateCell) {
                const timestamp = dateCell.getAttribute('data-timestamp');
                if (timestamp) {
                    try {
                        // Adjust for UTC by adding 'Z' if not present
                        const dateStr = timestamp.includes('Z') ? timestamp : timestamp.replace(' ', 'T') + 'Z';
                        const date = new Date(dateStr);
                        dateCell.textContent = date.toLocaleString('fa-IR', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    } catch (e) {
                        console.error("Error parsing date:", e);
                        dateCell.textContent = timestamp; // Fallback
                    }
                }
            }
        });

        // --- Action Handlers ---
        function reviewAnswer(answerId, statusAction) {
            let feedback = null;
            if (statusAction === 'rejected') {
                feedback = prompt('لطفاً دلیل رد کردن پاسخ را بنویسید (اختیاری):');
                if (feedback === null) return; // User cancelled prompt
            }

            const formData = new FormData();
            formData.append('action', 'review_answer');
            formData.append('answer_id', answerId);
            formData.append('status', statusAction);
            if (feedback) formData.append('feedback', feedback);

            fetch('review_api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(result => {
                    alert(result.message);
                    if (result.success) location.reload();
                })
                .catch(error => alert('خطای شبکه رخ داد.'));
        }

        function deleteAnswer(answerId) {
            if (!confirm('آیا از حذف این پاسخ اطمینان دارید؟ این عملیات غیرقابل بازگشت است.')) return;

            const formData = new FormData();
            formData.append('action', 'delete_answer');
            formData.append('answer_id', answerId);

            fetch('review_api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(result => {
                    alert(result.message);
                    if (result.success) window.location.href = 'task_answers.php?task_id=<?= $answer['task_id'] ?>';
                })
                .catch(error => alert('خطای شبکه رخ داد.'));
        }
    </script>
</body>

</html>
