<?php
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin');
require_once __DIR__ . '/../../db/database.php';

$answer_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$answer_id) {
    header("Location: task_answers.php");
    exit();
}

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
            flex-shrink: 0;
            min-height: 60px;
            font-size: .85rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .icon {
            width: 1.1em;
            height: 1.1em;
            stroke-width: 2.2;
            vertical-align: -0.15em;
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

        .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            filter: brightness(0.92);
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-secondary {
            background-color: var(--secondary-text);
            color: white;
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

        .task-summary {
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
            gap: 2rem;
            flex-wrap: wrap;
        }

        .summary-status-visual {
            font-size: 4rem;
            line-height: 1;
            flex-shrink: 0;
            width: 72px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .summary-status-visual .icon {
            width: 100%;
            height: 100%;
            stroke-width: 1.5;
        }

        .summary-details .summary-title {
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

        .status-badge {
            font-size: 1rem;
            font-weight: 700;
            padding: .4rem 1rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: .5em;
        }

        .status-badge.correct {
            background-color: var(--success-light);
            color: var(--success-color);
        }

        .status-badge.incorrect {
            background-color: var(--danger-light);
            color: var(--danger-color);
        }

        .status-badge.medium {
            background-color: var(--warning-light);
            color: #a17400;
        }

        .content-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .content-header {
            padding: 1.25rem 1.5rem;
            background-color: var(--bg-color);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .content-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-dark);
        }

        .content-header .icon {
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
            border: 1px solid var(--border-color);
        }

        .review-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .feedback-display {
            background-color: var(--danger-light);
            color: var(--danger-color);
            border: 1.5px solid var(--danger-color);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
        }

        .feedback-display strong {
            display: block;
            margin-bottom: .5rem;
        }

        #toast-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }

        .toast {
            padding: 12px 20px;
            border-radius: var(--radius);
            color: white;
            font-weight: 500;
            box-shadow: var(--shadow-md);
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.3s, transform 0.3s;
            min-width: 280px;
            text-align: center;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast-success {
            background-color: var(--success-color);
        }

        .toast-error {
            background-color: var(--danger-color);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.show {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: var(--radius);
            width: 90%;
            max-width: 500px;
            box-shadow: var(--shadow-md);
            transform: scale(0.95);
            transition: transform 0.3s ease;
        }

        .modal.show .modal-content {
            transform: scale(1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h3 {
            font-size: 1.3rem;
            font-weight: 700;
        }

        .modal-close {
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            color: var(--secondary-text);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: var(--secondary-text);
        }

        #feedback-text {
            width: 100%;
            min-height: 100px;
            font-size: 1rem;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius);
            transition: border-color .2s, box-shadow .2s;
        }

        #feedback-text:focus-visible {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 174, 112, .15);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            margin-top: 1.5rem;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="page-header">
            <a href="task_answers.php?task_id=<?= $answer['task_id'] ?>" class="btn btn-outline-secondary">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12" />
                    <polyline points="12 19 5 12 12 5" />
                </svg>
                <span>بازگشت به لیست پاسخ‌ها</span>
            </a>
            <h1 class="page-title"><?= $page_title ?></h1>
            <p class="page-subtitle">بررسی پاسخ ارسال‌شده توسط: <?= htmlspecialchars($answer['user_name']) ?></p>
        </div>

        <?php
        $status_map = [
            'submitted' => ['text' => 'در انتظار بازبینی', 'class' => 'medium', 'icon' => '<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>'],
            'approved' => ['text' => 'تایید شده', 'class' => 'correct', 'icon' => '<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'],
            'rejected' => ['text' => 'رد شده', 'class' => 'incorrect', 'icon' => '<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>']
        ];
        $status_info = $status_map[$answer['status']] ?? ['text' => $answer['status'], 'class' => '', 'icon' => ''];
        ?>

        <div class="task-summary">
            <div class="summary-main">
                <div class="summary-status-visual" style="color: var(--<?= $status_info['class'] === 'medium' ? 'warning' : ($status_info['class'] === 'correct' ? 'success' : 'danger') ?>-color);"><?= $status_info['icon'] ?></div>
                <div class="summary-details">
                    <h2 class="summary-title">خلاصه وضعیت</h2>
                    <p class="summary-info">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <span>کاربر:</span> <strong><?= htmlspecialchars($answer['user_name']) ?></strong>
                    </p>
                    <p class="summary-info">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                            <line x1="16" x2="16" y1="2" y2="6" />
                            <line x1="8" x2="8" y1="2" y2="6" />
                            <line x1="3" x2="21" y1="10" y2="10" />
                        </svg>
                        <span>تاریخ ارسال:</span> <strong id="submission-date" data-timestamp="<?= htmlspecialchars($answer['submitted_at']) ?>">...</strong>
                    </p>
                    <div style="margin-top: 1rem;">
                        <span class="status-badge <?= $status_info['class'] ?>"><?= $status_info['icon'] ?> <span><?= $status_info['text'] ?></span></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="content-header">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><help-circle cx="12" cy="12" r="10" />
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                    <path d="M12 17h.01" />
                </svg>
                <p class="content-title">سوال تکلیف</p>
            </div>
            <div class="content-body">
                <p><?= htmlspecialchars($answer['question_text']) ?></p>
            </div>
        </div>

        <div class="content-card">
            <div class="content-header">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><message-square />
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                </svg>
                <p class="content-title">پاسخ کاربر</p>
            </div>
            <div class="content-body">
                <div class="answer-text-display"><?= nl2br(htmlspecialchars($answer['answer_text'])) ?></div>
            </div>
        </div>

        <div class="content-card">
            <div class="content-header">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <settings />
                    <path d="M20 7h-9" />
                    <path d="M14 17H5" />
                    <circle cx="17" cy="17" r="3" />
                    <circle cx="8" cy="7" r="3" />
                </svg>
                <p class="content-title">عملیات بازبینی</p>
            </div>
            <div class="content-body">
                <?php if ($answer['status'] === 'submitted'): ?>
                    <p style="margin-bottom: 1.5rem;">پاسخ کاربر را تایید یا رد کنید.</p>
                    <div class="review-actions">
                        <button class="btn btn-success" onclick="reviewAnswer(<?= $answer_id ?>, 'approved')">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                            <span>تایید پاسخ</span>
                        </button>
                        <button class="btn btn-danger" onclick="reviewAnswer(<?= $answer_id ?>, 'rejected')">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                            <span>رد پاسخ</span>
                        </button>
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
                        <button class="btn btn-danger" onclick="deleteAnswer(<?= $answer_id ?>)">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18" />
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                            </svg>
                            <span>حذف کامل این پاسخ</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <div id="feedback-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>دلیل رد کردن پاسخ</h3>
                <span class="modal-close" onclick="closeModal()">&times;</span>
            </div>
            <div class="form-group">
                <label for="feedback-text">لطفاً دلیل رد کردن پاسخ را بنویسید (اختیاری):</label>
                <textarea id="feedback-text"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">انصراف</button>
                <button id="submit-feedback-btn" class="btn btn-danger">ارسال بازخورد</button>
            </div>
        </div>
    </div>

    <div id="toast-container"></div>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dateCell = document.getElementById('submission-date');
            if (dateCell) {
                const timestamp = dateCell.getAttribute('data-timestamp');
                if (timestamp) {
                    try {
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
                        dateCell.textContent = timestamp;
                    }
                }
            }
        });

        const feedbackModal = document.getElementById('feedback-modal');
        const submitFeedbackBtn = document.getElementById('submit-feedback-btn');

        function closeModal() {
            feedbackModal.classList.remove('show');
        }

        function showToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `toast toast-${type} show`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('show');
                toast.addEventListener('transitionend', () => toast.remove());
            }, duration);
        }

        function showConfirmation(message, onConfirm) {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast toast-confirm show';
            toast.innerHTML = `
                <div class="toast-message">${message}</div>
                <div class="toast-buttons">
                    <button class="btn btn-danger" id="confirmAction">بله، حذف کن</button>
                    <button class="btn btn-secondary" id="cancelAction">لغو</button>
                </div>`;
            const removeToast = () => {
                toast.classList.remove('show');
                toast.addEventListener('transitionend', () => toast.remove());
            };
            toast.querySelector('#confirmAction').onclick = () => {
                onConfirm();
                removeToast();
            };
            toast.querySelector('#cancelAction').onclick = removeToast;
            toastContainer.appendChild(toast);
        }

        async function performReview(answerId, status, feedback = null) {
            const formData = new FormData();
            formData.append('action', 'review_answer');
            formData.append('answer_id', answerId);
            formData.append('status', status);
            if (feedback) formData.append('feedback', feedback);

            try {
                const response = await fetch('review_api.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                showToast(result.message, result.success ? 'success' : 'error');
                if (result.success) setTimeout(() => location.reload(), 1500);
            } catch (error) {
                showToast('خطای شبکه رخ داد.', 'error');
            }
        }

        function reviewAnswer(answerId, statusAction) {
            if (statusAction === 'rejected') {
                feedbackModal.classList.add('show');
                submitFeedbackBtn.onclick = () => {
                    const feedback = document.getElementById('feedback-text').value;
                    closeModal();
                    performReview(answerId, 'rejected', feedback);
                };
            } else {
                performReview(answerId, 'approved');
            }
        }

        function deleteAnswer(answerId) {
            showConfirmation('آیا از حذف این پاسخ اطمینان دارید؟ این عملیات غیرقابل بازگشت است.', async () => {
                const formData = new FormData();
                formData.append('action', 'delete_answer');
                formData.append('answer_id', answerId);
                try {
                    const response = await fetch('review_api.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    showToast(result.message, result.success ? 'success' : 'error');
                    if (result.success) {
                        setTimeout(() => window.location.href = 'task_answers.php?task_id=<?= $answer['task_id'] ?>', 1500);
                    }
                } catch (error) {
                    showToast('خطای شبکه رخ داد.', 'error');
                }
            });
        }

        window.onclick = function(event) {
            if (event.target == feedbackModal) {
                closeModal();
            }
        }
    </script>
</body>

</html>
