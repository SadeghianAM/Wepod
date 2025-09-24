<?php
// فایل: results.php
require_once 'database.php';

// دریافت تمام نتایج با اطلاعات کاربر و آزمون
$sql = "
    SELECT
        qa.id, qa.score, qa.start_time,
        u.name AS user_name,
        q.title AS quiz_title
    FROM QuizAttempts qa
    JOIN Users u ON qa.user_id = u.id
    JOIN Quizzes q ON qa.quiz_id = q.id
";

// اگر از صفحه آزمون‌ها به اینجا آمده باشیم، نتایج را فیلتر می‌کنیم
if (isset($_GET['quiz_id'])) {
    $sql .= " WHERE qa.quiz_id = ?";
    $stmt = $pdo->prepare($sql . " ORDER BY qa.start_time DESC");
    $stmt->execute([filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT)]);
} else {
    $stmt = $pdo->query($sql . " ORDER BY qa.start_time DESC");
}
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مشاهده نتایج آزمون‌ها</title>
    <style>
        body {
            font-family: -apple-system, sans-serif;
            direction: rtl;
            background-color: #f4f7f9;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #1a2533;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: right;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f8f8f8;
        }

        tr:hover {
            background-color: #e9f5ff;
        }
    </style>
</head>

<body>
    <main class="container">
        <h1>نتایج آزمون‌ها</h1>
        <table>
            <thead>
                <tr>
                    <th>کاربر</th>
                    <th>نام آزمون</th>
                    <th>نمره</th>
                    <th>تاریخ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($results)): ?>
                    <tr>
                        <td colspan="4">هیچ نتیجه‌ای برای نمایش وجود ندارد.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?= htmlspecialchars($result['user_name']) ?></td>
                            <td><?= htmlspecialchars($result['quiz_title']) ?></td>
                            <td><?= htmlspecialchars($result['score']) ?></td>
                            <td><?= htmlspecialchars($result['start_time']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>

</html>
