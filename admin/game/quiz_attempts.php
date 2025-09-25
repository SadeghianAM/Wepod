<?php
// فایل جدید: quiz_attempts.php (پنل ادمین)
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html'); // فقط ادمین دسترسی دارد
require_once __DIR__ . '/../../db/database.php';

// کوئری برای دریافت تمام تاریخچه‌ها با اطلاعات کاربر و آزمون
$stmt = $pdo->query("
    SELECT
        qa.id as attempt_id,
        u.username,
        u.name as user_fullname,
        q.title as quiz_title,
        qa.score,
        qa.end_time
    FROM QuizAttempts qa
    JOIN users u ON qa.user_id = u.id
    JOIN Quizzes q ON qa.quiz_id = q.id
    ORDER BY qa.end_time DESC
");
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>مدیریت نتایج آزمون‌ها</title>
    <style>
        body {
            font-family: Vazirmatn, sans-serif;
            background-color: #f7f9fa;
            padding: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .06);
        }

        th,
        td {
            padding: 12px 15px;
            border: 1px solid #e9e9e9;
            text-align: right;
        }

        th {
            background-color: #00ae70;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <h1>نتایج آزمون‌ها</h1>
    <table>
        <thead>
            <tr>
                <th>نام کامل کاربر</th>
                <th>نام کاربری</th>
                <th>عنوان آزمون</th>
                <th>امتیاز کسب شده</th>
                <th>تاریخ تکمیل</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($attempts)): ?>
                <tr>
                    <td colspan="6">هیچ نتیجه‌ای برای نمایش وجود ندارد.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($attempts as $attempt): ?>
                    <tr id="attempt-row-<?= $attempt['attempt_id'] ?>">
                        <td><?= htmlspecialchars($attempt['user_fullname']) ?></td>
                        <td><?= htmlspecialchars($attempt['username']) ?></td>
                        <td><?= htmlspecialchars($attempt['quiz_title']) ?></td>
                        <td><?= $attempt['score'] ?></td>
                        <td><?= $attempt['end_time'] ? date('Y-m-d H:i', strtotime($attempt['end_time'])) : 'N/A' ?></td>
                        <td>
                            <button class="btn-danger" onclick="deleteAttempt(<?= $attempt['attempt_id'] ?>)">
                                حذف
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        async function deleteAttempt(attemptId) {
            if (!confirm('آیا مطمئن هستید؟ با حذف این تاریخچه، کاربر می‌تواند مجدداً در این آزمون شرکت کند و امتیاز کسب شده از مجموع امتیازات او کسر خواهد شد.')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete_attempt');
            formData.append('id', attemptId);

            try {
                const response = await fetch('attempts_api.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    // حذف ردیف از جدول بدون نیاز به رفرش صفحه
                    const row = document.getElementById(`attempt-row-${attemptId}`);
                    if (row) {
                        row.remove();
                    }
                } else {
                    alert('خطا: ' + result.message);
                }
            } catch (error) {
                alert('یک خطای ارتباطی رخ داد.');
            }
        }
    </script>
</body>

</html>
