<?php
// فایل: index.php (نسخه کامل و مستقل)
require_once __DIR__ . '/../../auth/require-auth.php'; // مسیر فایل auth را تنظیم کنید
$claims = requireAuth('admin', '/../auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>پنل مدیریت آزمون</title>
    <style>
        /* تمام کدهای CSS در اینجا قرار می‌گیرد */
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
            --header-h: 70px;
            --footer-h: 60px;
        }

        @font-face {
            font-family: "Vazirmatn";
            src: url("/assets/fonts/Vazirmatn[wght].ttf") format("truetype");
            /* مسیر فونت را در صورت نیاز تغییر دهید */
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

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            direction: rtl;
            background: var(--bg-color);
            color: var(--text-color);
        }

        a {
            color: inherit;
            text-decoration: none;
            transition: all .2s ease;
        }

        :focus-visible {
            outline: 3px solid rgba(0, 174, 112, .35);
            outline-offset: 2px;
        }

        header,
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
        }

        header {
            min-height: var(--header-h);
        }

        footer {
            min-height: var(--footer-h);
            font-size: .85rem;
            justify-content: center;
        }

        header h1 {
            font-weight: 700;
            font-size: 1.2rem;
        }

        #user-info {
            white-space: nowrap;
            opacity: .9;
            font-weight: 500;
            font-size: 1rem;
            padding: .5rem .8rem;
            border-radius: 8px;
        }

        #user-info:hover {
            background-color: rgba(255, 255, 255, .15);
        }

        main {
            flex: 1;
            width: min(1200px, 100%);
            padding: 2.5rem 2rem;
            margin-inline: auto;
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: .5rem;
        }

        .page-subtitle {
            color: var(--secondary-text);
            font-weight: 400;
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .tools-grid {
            list-style: none;
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }

        .tool-card a {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: .75rem;
            padding: 1.75rem;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            will-change: transform;
        }

        .tool-card a:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
            color: var(--primary-dark);
        }

        .tool-icon {
            font-size: 2rem;
            line-height: 1;
        }

        .tool-title {
            font-size: 1.1rem;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            main {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>

<body>
    <header>
        <h1><a href="/admin/quiz/index.php">پنل مدیریت آزمون</a></h1>
        <?php if (isset($claims) && isset($claims['name'])): ?>
            <span id="user-info">خوش آمدید، <?= htmlspecialchars($claims['name']) ?></span>
        <?php endif; ?>
    </header>

    <main>
        <h1 class="page-title">پنل مدیریت آزمون</h1>
        <p class="page-subtitle">بخش مورد نظر خود را برای مدیریت انتخاب کنید.</p>
        <ul class="tools-grid" id="tools-list">
            <li class="tool-card">
                <a href="questions.php">
                    <span class="tool-icon">📝</span>
                    <span class="tool-title">مدیریت سوالات</span>
                </a>
            </li>
            <li class="tool-card">
                <a href="teams.php">
                    <span class="tool-icon">👥</span>
                    <span class="tool-title">مدیریت تیم‌ها</span>
                </a>
            </li>
            <li class="tool-card">
                <a href="quizzes.php">
                    <span class="tool-icon">📋</span>
                    <span class="tool-title">مدیریت آزمون‌ها</span>
                </a>
            </li>
            <li class="tool-card">
                <a href="results.php">
                    <span class="tool-icon">📊</span>
                    <span class="tool-title">مشاهده نتایج</span>
                </a>
            </li>
        </ul>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> - سامانه آزمون</p>
    </footer>
</body>

</html>
