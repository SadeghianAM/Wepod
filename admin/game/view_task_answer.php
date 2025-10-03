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
            --status-approved: #28a745;
            --status-rejected: #dc3545;
            --status-submitted: #ffc107;
            --danger-light: #f8d7da;
            --danger-dark: #721c24;
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
            font-weight: 500;
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

        .answer-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
            padding: 2rem;
        }

        .answer-card h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .answer-text {
            white-space: pre-wrap;
            background-color: var(--bg-color);
            padding: 1rem;
            border-radius: 8px;
            line-height: 1.7;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .info-item {
            background-color: var(--card-bg);
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        .info-label {
            color: var(--secondary-text);
            font-size: .9rem;
            margin-bottom: .25rem;
            display: block;
        }

        .info-value {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .status-badge {
            padding: .25em .8em;
            font-size: .8rem;
            font-weight: 700;
            border-radius: 15px;
            color: white;
        }

        .status-approved {
            background-color: var(--status-approved);
        }

        .status-rejected {
            background-color: var(--status-rejected);
        }

        .status-submitted {
            background-color: var(--status-submitted);
            color: #333;
        }

        .review-section {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .review-section h3 {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
        }

        .review-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: .75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
        }

        .btn-approve {
            background-color: var(--status-approved);
            color: white;
        }

        .btn-reject {
            background-color: var(--status-rejected);
            color: white;
        }

        .btn-delete {
            background-color: #343a40;
            color: white;
        }

        .feedback-display {
            background-color: var(--danger-light);
            color: var(--danger-dark);
            border: 1px solid var(--danger-dark);
            padding: 1rem;
            border-radius: 8px;
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
            <p class="page-subtitle">کاربر: <?= htmlspecialchars($answer['user_name']) ?></p>
        </div>

        <?php
        $status_map = [
            'submitted' => ['text' => 'در انتظار بازبینی', 'class' => 'status-submitted'],
            'approved' => ['text' => 'تایید شده', 'class' => 'status-approved'],
            'rejected' => ['text' => 'رد شده', 'class' => 'status-rejected']
        ];
        $status_info = $status_map[$answer['status']] ?? ['text' => $answer['status'], 'class' => ''];
        ?>

        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">وضعیت</span>
                <span class="info-value"><span class="status-badge <?= $status_info['class'] ?>"><?= $status_info['text'] ?></span></span>
            </div>
            <div class="info-item">
                <span class="info-label">تاریخ ارسال</span>
                <span class="info-value" id="submission-date" data-timestamp="<?= htmlspecialchars($answer['submitted_at']) ?>">...</span>
            </div>
        </div>

        <div class="answer-card">
            <h3>سوال تکلیف</h3>
            <p><?= htmlspecialchars($answer['question_text']) ?></p>
        </div>

        <div class="answer-card">
            <h3>پاسخ ارسال شده توسط کاربر</h3>
            <div class="answer-text"><?= nl2br(htmlspecialchars($answer['answer_text'])) ?></div>
        </div>

        <div class="review-section">
            <?php if ($answer['status'] === 'submitted'): ?>
                <h3>عملیات بازبینی</h3>
                <div class="review-actions">
                    <button class="btn btn-approve" onclick="reviewAnswer(<?= $answer_id ?>, 'approved')">✅ تایید پاسخ</button>
                    <button class="btn btn-reject" onclick="reviewAnswer(<?= $answer_id ?>, 'rejected')">❌ رد پاسخ</button>
                </div>
            <?php else: ?>
                <h3>نتیجه بازبینی</h3>
                <?php if ($answer['status'] === 'rejected' && !empty($answer['feedback'])): ?>
                    <p><strong>دلیل رد شدن:</strong></p>
                    <div class="feedback-display"><?= nl2br(htmlspecialchars($answer['feedback'])) ?></div>
                <?php else: ?>
                    <p>این پاسخ قبلاً بازبینی شده است.</p>
                <?php endif; ?>
                <div class="review-actions" style="margin-top: 1.5rem;">
                    <button class="btn btn-delete" onclick="deleteAnswer(<?= $answer_id ?>)">حذف کامل این پاسخ</button>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div id="footer-placeholder"></div>
    <script src="/js/header.js"></script>
    <script>
        // --- Date Formatting ---
        document.addEventListener('DOMContentLoaded', () => {
            const dateCell = document.getElementById('submission-date');
            const timestamp = dateCell.getAttribute('data-timestamp');
            if (timestamp) {
                try {
                    const date = new Date(timestamp.replace(' ', 'T') + 'Z');
                    dateCell.textContent = date.toLocaleString('fa-IR', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch (e) {
                    dateCell.textContent = timestamp;
                }
            }
        });

        // --- Action Handlers (reusing logic from your original file) ---
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
                    if (result.success) window.location.href = 'task_answers.php';
                })
                .catch(error => alert('خطای شبکه رخ داد.'));
        }
    </script>
</body>

</html>
