<?php
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');
require_once __DIR__ . '/../../db/database.php';

$stmt_pending = $pdo->query("
    SELECT ta.id, u.name AS user_name, tq.question_text, ta.answer_text, t.title as task_title
    FROM TaskAnswers ta
    JOIN Users u ON ta.user_id = u.id
    JOIN TaskQuestions tq ON ta.task_question_id = tq.id
    JOIN Tasks t ON tq.task_id = t.id
    WHERE ta.status = 'submitted'
    ORDER BY ta.submitted_at ASC
");
$pending_answers = $stmt_pending->fetchAll(PDO::FETCH_ASSOC);

$stmt_recent = $pdo->query("
    SELECT
        ta.id,
        u.name AS user_name,
        t.title as task_title,
        ta.status
    FROM TaskAnswers ta
    JOIN Users u ON ta.user_id = u.id
    JOIN TaskQuestions tq ON ta.task_question_id = tq.id
    JOIN Tasks t ON tq.task_id = t.id
    WHERE ta.status IN ('approved', 'rejected')
    ORDER BY ta.id DESC
    LIMIT 10
");
$recent_reviews = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>بازبینی پاسخ تکالیف</title>
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
            --footer-h: 60px;
            --border-color: #e9e9e9;
            --radius: 12px;
            --shadow-sm: 0 2px 6px rgba(0, 120, 80, .06);
            --shadow-md: 0 6px 20px rgba(0, 120, 80, .10);
            --danger-color: #dc3545;
            --danger-dark: #c82333;
            --dark-color: #343a40;
            --dark-hover: #23272b;
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
            width: min(1200px, 100%);
            padding: 2.5rem 2rem;
            margin-inline: auto;
        }

        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 2rem;
            z-index: 10;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
            min-height: var(--footer-h);
            font-size: .85rem;
        }

        .page-toolbar {
            margin-bottom: 2rem;
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: 1.8rem;
        }

        .page-subtitle {
            color: var(--secondary-text);
            font-size: 1rem;
        }

        .table-container {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 3rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem 1.25rem;
            text-align: right;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: var(--bg-color);
            font-weight: 600;
            color: var(--secondary-text);
            font-size: .9rem;
        }

        td {
            font-size: .95rem;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .answer-text {
            max-height: 100px;
            overflow-y: auto;
            display: block;
            white-space: pre-wrap;
        }

        .actions-cell {
            display: flex;
            gap: .5rem;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: .85rem;
            font-weight: 600;
            transition: all .2s ease;
        }

        .btn-approve {
            background-color: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background-color: #218838;
        }

        .btn-reject {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-reject:hover {
            background-color: var(--danger-dark);
        }

        .btn-delete {
            background-color: var(--dark-color);
            color: white;
        }

        .btn-delete:hover {
            background-color: var(--dark-hover);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background-color: var(--card-bg);
            border-radius: var(--radius);
            border: 2px dashed var(--border-color);
        }

        #toast-container {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 200;
        }

        .toast {
            padding: 12px 20px;
            background-color: var(--primary-dark);
            color: white;
            border-radius: 8px;
            margin-bottom: 10px;
            opacity: 0;
            transform: translateY(20px);
            animation: fade-in-out 4s forwards;
        }

        .toast.error {
            background-color: var(--danger-dark);
        }

        .status-badge {
            padding: .2em .6em;
            font-size: .8rem;
            font-weight: 700;
            border-radius: 6px;
            color: white;
        }

        .status-approved {
            background-color: #28a745;
        }

        .status-rejected {
            background-color: var(--danger-color);
        }

        @keyframes fade-in-out {
            5% {
                opacity: 1;
                transform: translateY(0);
            }

            90% {
                opacity: 1;
                transform: translateY(0);
            }

            100% {
                opacity: 0;
                transform: translateY(20px);
            }
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="page-toolbar">
            <h2 class="page-title" style="margin: 0;">پاسخ‌های در انتظار بازبینی</h2>
            <p class="page-subtitle">پاسخ‌های ارسال شده توسط کاربران را تایید یا رد کنید.</p>
        </div>
        <?php if (empty($pending_answers)): ?>
            <div class="empty-state">
                <h2>هیچ پاسخی برای بازبینی وجود ندارد! 🎉</h2>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>کاربر</th>
                            <th>نام تکلیف</th>
                            <th>متن سوال</th>
                            <th>پاسخ ارسالی</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_answers as $answer): ?>
                            <tr data-answer-id="<?= $answer['id'] ?>">
                                <td><?= htmlspecialchars($answer['user_name']); ?></td>
                                <td><?= htmlspecialchars($answer['task_title']); ?></td>
                                <td><?= htmlspecialchars($answer['question_text']); ?></td>
                                <td>
                                    <div class="answer-text"><?= nl2br(htmlspecialchars($answer['answer_text'])); ?></div>
                                </td>
                                <td class="actions-cell">
                                    <button class="btn btn-approve" onclick="reviewAnswer(<?= $answer['id'] ?>, 'approved')">تایید</button>
                                    <button class="btn btn-reject" onclick="reviewAnswer(<?= $answer['id'] ?>, 'rejected')">رد</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="page-toolbar" style="margin-top: 4rem;">
            <h2 class="page-title" style="margin: 0;">بازبینی‌های اخیر</h2>
            <p class="page-subtitle">آخرین پاسخ‌هایی که وضعیت آن‌ها مشخص شده است.</p>
        </div>
        <?php if (empty($recent_reviews)): ?>
            <div class="empty-state">
                <p>هنوز پاسخی بازبینی نشده است.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>کاربر</th>
                            <th>نام تکلیف</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_reviews as $review): ?>
                            <tr data-answer-id="<?= $review['id'] ?>">
                                <td><?= htmlspecialchars($review['user_name']); ?></td>
                                <td><?= htmlspecialchars($review['task_title']); ?></td>
                                <td>
                                    <?php
                                    $status_text = $review['status'] === 'approved' ? 'تایید شده' : 'رد شده';
                                    $status_class = $review['status'] === 'approved' ? 'status-approved' : 'status-rejected';
                                    echo "<span class='status-badge $status_class'>$status_text</span>";
                                    ?>
                                </td>
                                <td class="actions-cell">
                                    <button class="btn btn-delete" onclick="deleteAnswer(<?= $review['id'] ?>)">حذف</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <div id="toast-container"></div>
    <div id="footer-placeholder"></div>

    <script src="/js/header.js"></script>
    <script>
        const showToast = (message, type = 'success') => {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        };

        function reviewAnswer(answerId, statusAction) {
            let feedback = null;
            if (statusAction === 'rejected') {
                feedback = prompt('لطفاً دلیل رد کردن پاسخ را بنویسید (اختیاری):');
                if (feedback === null) {
                    return;
                }
            }

            const formData = new FormData();
            formData.append('action', 'review_answer');
            formData.append('answer_id', answerId);
            formData.append('status', statusAction);
            if (feedback !== null) {
                formData.append('feedback', feedback);
            }

            fetch('review_api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(result => {
                    showToast(result.message, result.success ? 'success' : 'error');
                    if (result.success) {
                        setTimeout(() => location.reload(), 1000);
                    }
                })
                .catch(error => {
                    showToast('خطای شبکه رخ داد. لطفاً اتصال اینترنت خود را بررسی کنید.', 'error');
                });
        }

        function deleteAnswer(answerId) {
            if (!confirm('آیا از حذف این پاسخ اطمینان دارید؟ این عملیات غیرقابل بازگشت است.')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete_answer');
            formData.append('answer_id', answerId);

            fetch('review_api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(result => {
                    showToast(result.message, result.success ? 'success' : 'error');
                    if (result.success) {
                        const row = document.querySelector(`tr[data-answer-id="${answerId}"]`);
                        if (row) {
                            row.style.transition = 'opacity 0.5s';
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 500);
                        }
                    }
                })
                .catch(error => {
                    showToast('خطای شبکه رخ داد. لطفاً اتصال اینترنت خود را بررسی کنید.', 'error');
                });
        }
    </script>
</body>

</html>
