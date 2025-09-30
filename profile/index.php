<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/../auth/require-auth.php';
require_once __DIR__ . '/../db/database.php';

$claims = requireAuth(null, '/auth/login.html');
$agentId = $claims['sub'] ?? ($claims['id'] ?? ($claims['username'] ?? null));

$agentData = [];
$agentName = 'کاربر';
$agentUsername = '-';
$agentRole = 'بدون سمت';
$agentExtension = '-';
$agentScore = 0;
$agentStartDate = '-';

if ($agentId) {
    try {
        $stmt = $pdo->prepare("SELECT name, username, role, score, start_work FROM users WHERE id = :id");
        $stmt->execute([':id' => $agentId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $agentExtension = $agentId;

        if ($user) {
            $agentName = $user['name'] ?? $agentName;
            $agentUsername = $user['username'] ?? $agentUsername;
            $agentRole = $user['role'] ?? $agentRole;
            $agentScore = $user['score'] ?? $agentScore;
            // تاریخ مستقیماً از دیتابیس خوانده می‌شود
            $agentStartDate = $user['start_work'] ?? '-';
        }
    } catch (PDOException $e) {
        error_log("Database error fetching user details: " . $e->getMessage());
    }

    $jsonFile = __DIR__ . '/../data/reports.json';
    if (file_exists($jsonFile)) {
        $allData = json_decode(file_get_contents($jsonFile), true);
        if (is_array($allData) && isset($allData[$agentId])) {
            $agentData = $allData[$agentId];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>پروفایل کاربری</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --primary-light: #e6f7f2;
            --bg-color: #f7f9fc;
            --text-color: #333;
            --secondary-text-color: #6c757d;
            --card-bg: #ffffff;
            --header-text: #ffffff;
            --shadow-color: rgba(0, 0, 0, 0.05);
            --footer-h: 60px;
            --border-radius: 0.8rem;
            --border-color: #e9ecef;
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
            font-family: "Vazirmatn", sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 2rem;
            z-index: 10;
            flex-shrink: 0;
            min-height: var(--footer-h);
            font-size: .85rem;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            direction: rtl;
            /* --- کد اصلاح شده برای چسباندن فوتر به پایین --- */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .profile-container {
            display: flex;
            flex-grow: 1;
            max-width: 1600px;
            width: 100%;
            margin: 2rem auto;
            gap: 2rem;
            padding: 0 1.5rem;
        }

        .profile-sidebar {
            flex: 0 0 260px;
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px var(--shadow-color);
            padding: 1.5rem;
            height: fit-content;
        }

        .profile-user-info {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .profile-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1rem;
            font-weight: 600;
        }

        .profile-user-info h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.25rem;
        }

        .profile-user-role {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }

        .profile-user-extension {
            font-size: 0.85rem;
            color: var(--secondary-text-color);
            background-color: var(--bg-color);
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 0.75rem;
        }

        .profile-user-score {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--primary-light);
            color: var(--primary-dark);
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .profile-menu ul {
            list-style: none;
        }

        .profile-menu li a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.9rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: var(--secondary-text-color);
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        .profile-menu li a:hover {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }

        .profile-menu li a.active {
            background-color: var(--primary-color);
            color: var(--header-text);
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 174, 112, 0.3);
        }

        .profile-menu .menu-emoji {
            font-size: 1.4rem;
            width: 25px;
            text-align: center;
            line-height: 1;
        }

        .profile-content {
            flex-grow: 1;
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .overview-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px var(--shadow-color);
            padding: 2rem;
        }

        .overview-card h2 {
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.6rem;
            transition: all 0.2s ease-in-out;
        }

        .info-item:hover {
            border-color: var(--primary-color);
            background-color: #fafdfc;
        }

        .info-label {
            font-size: 0.95rem;
            color: var(--secondary-text-color);
            font-weight: 500;
        }

        .info-value {
            font-size: 1.05rem;
            color: var(--text-color);
            font-weight: 600;
        }

        .info-value.score {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
            font-size: 1rem;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary-dark);
        }

        .dashboard-header p {
            font-size: 1.1rem;
            color: var(--secondary-text-color);
        }

        #date-nav {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
            background-color: var(--card-bg);
            padding: 0.5rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px var(--shadow-color);
            border: 1px solid var(--border-color);
            width: fit-content;
            margin-inline: auto;
        }

        #date-nav button {
            font-size: 0.9rem;
            padding: 0.6rem 1.4rem;
            border-radius: 50px;
            background-color: transparent;
            border: none;
            color: var(--secondary-text-color);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        #date-nav button:hover {
            color: var(--primary-dark);
        }

        #date-nav button.active {
            background-color: var(--primary-color);
            color: var(--header-text);
            box-shadow: 0 2px 8px rgba(0, 174, 112, 0.3);
            font-weight: 600;
        }

        .metric-group {
            margin-bottom: 2.5rem;
        }

        .metric-group-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-right: 1rem;
            border-right: 4px solid var(--primary-color);
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .metric-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: 0 4px 20px var(--shadow-color);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 1.25rem;
            transition: all 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .metric-card .emoji-container {
            font-size: 2.2rem;
            background-color: var(--primary-light);
            height: 60px;
            width: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            line-height: 1;
            padding-top: 10px;
        }

        .metric-card .content h3 {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--secondary-text-color);
            margin-bottom: 0.5rem;
        }

        .metric-card .content p {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-color);
            line-height: 1.2;
        }

        .metric-card .content p.no-value-text {
            font-size: 1rem;
            font-weight: 400;
            font-style: italic;
        }

        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-top: 2.5rem;
        }

        .chart-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: 0 4px 20px var(--shadow-color);
            border: 1px solid var(--border-color);
        }

        /* ADD THIS CSS to your <style> block in profile/index.php */
        .assets-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        .assets-table th,
        .assets-table td {
            padding: 1rem;
            text-align: right;
            border-bottom: 1px solid var(--border-color);
        }

        .assets-table th {
            background-color: var(--bg-color);
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--secondary-text-color);
        }

        .assets-table tbody tr:hover {
            background-color: var(--primary-light);
        }

        .assets-table td .date-chip {
            display: inline-block;
            background-color: #e9ecef;
            color: #495057;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <div class="profile-container">
        <aside class="profile-sidebar">
            <div class="profile-user-info">
                <div class="profile-avatar"><?php echo htmlspecialchars(mb_substr($agentName, 0, 1)); ?></div>
                <h2><?php echo htmlspecialchars($agentName); ?></h2>
                <p class="profile-user-role"><?php echo htmlspecialchars($agentRole); ?></p>
                <p class="profile-user-extension">داخلی: <?php echo htmlspecialchars($agentExtension); ?></p>
                <div class="profile-user-score">
                    <span>⭐</span>
                    <span>امتیاز: <?php echo htmlspecialchars($agentScore); ?></span>
                </div>
            </div>
            <nav class="profile-menu">
                <ul>
                    <li>
                        <a href="#dashboard" class="profile-tab-link active">
                            <span class="menu-emoji">👤</span>
                            <span>داشبورد</span>
                        </a>
                    </li>
                    <li>
                        <a href="#performance-report" class="profile-tab-link">
                            <span class="menu-emoji">📊</span>
                            <span>گزارش عملکرد</span>
                        </a>
                    </li>
                    <li>
                        <a href="#my-assets" class="profile-tab-link">
                            <span class="menu-emoji">💻</span>
                            <span>اموال من</span>
                        </a>
                    </li>
                    <li>
                        <a href="/wheel/index.php">
                            <span class="menu-emoji">🎡</span>
                            <span>گردونه شانس</span>
                        </a>
                    </li>
                    <li>
                        <a href="/auth/logout.php">
                            <span class="menu-emoji">🚪</span>
                            <span>خروج از حساب</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="profile-content">
            <section id="dashboard" class="content-section active">
                <div class="overview-card">
                    <h2>داشبورد</h2>
                    <div class="overview-grid">
                        <div class="info-item">
                            <span class="info-label">نام کامل</span>
                            <span class="info-value"><?php echo htmlspecialchars($agentName); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">نام کاربری</span>
                            <span class="info-value"><?php echo htmlspecialchars($agentUsername); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">سمت</span>
                            <span class="info-value"><?php echo htmlspecialchars($agentRole); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">شماره داخلی</span>
                            <span class="info-value"><?php echo htmlspecialchars($agentExtension); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">امتیاز فعلی</span>
                            <span class="info-value score">⭐ <?php echo htmlspecialchars($agentScore); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">تاریخ شروع همکاری</span>
                            <span class="info-value"><?php echo htmlspecialchars($agentStartDate); ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <section id="performance-report" class="content-section">
                <div class="dashboard-header">
                    <h1>گزارش عملکرد</h1>
                    <p>گزارش‌های روزانه شما، <?php echo htmlspecialchars($agentName); ?></p>
                </div>
                <nav id="date-nav"></nav>
                <div id="report-content"></div>
            </section>
            <section id="my-assets" class="content-section">
                <div class="overview-card">
                    <h2>اموال در اختیار شما</h2>
                    <div id="user-assets-container">
                    </div>
                </div>
            </section>

        </main>
    </div>
    </main>
    </div>

    <div id="footer-placeholder"></div>
    <script src="/js/header.js?v=1.0"></script>
    <script>
        // Tab switching logic (No changes needed)
        document.addEventListener('DOMContentLoaded', () => {
            const tabLinks = document.querySelectorAll('.profile-tab-link');
            const contentSections = document.querySelectorAll('.content-section');
            tabLinks.forEach(link => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    if (link.classList.contains('active')) return;
                    const targetId = link.getAttribute('href').substring(1);
                    const targetSection = document.getElementById(targetId);
                    tabLinks.forEach(l => l.classList.remove('active'));
                    contentSections.forEach(s => s.classList.remove('active'));
                    link.classList.add('active');
                    if (targetSection) targetSection.classList.add('active');
                });
            });
            const hash = window.location.hash.substring(1);
            if (hash) {
                const targetLink = document.querySelector(`.profile-tab-link[href="#${hash}"]`);
                if (targetLink) targetLink.click();
            }
        });

        // Dashboard logic (No changes needed)
        const agentData = <?php echo json_encode($agentData, JSON_UNESCAPED_UNICODE); ?>;
        const metricsConfig = {
            performance: {
                title: "عملکرد کلی تماس‌ها",
                keys: ["incoming_calls", "total_talk_time_in", "avg_talk_time_in", "max_talk_time_in", "outbound_calls", "avg_talk_time_out", "missed_calls", "calls_over_5_min"]
            },
            quality: {
                title: "کیفیت و بازخورد",
                keys: ["avg_rating", "ratings_count", "one_star_ratings"]
            },
            productivity: {
                title: "بهره‌وری و مدیریت زمان",
                keys: ["presence_duration", "break_duration", "no_call_reason"]
            },
            tasks: {
                title: "سایر وظایف",
                keys: ["tickets_count", "famas_count", "jira_count"]
            }
        };
        const labels = {
            incoming_calls: {
                title: "تماس ورودی",
                emoji: "📞"
            },
            total_talk_time_in: {
                title: "مجموع مکالمه (ورودی)",
                emoji: "⏱️"
            },
            avg_talk_time_in: {
                title: "میانگین مکالمه (ورودی)",
                emoji: "⏳"
            },
            max_talk_time_in: {
                title: "بیشترین مکالمه (ورودی)",
                emoji: "⌛️"
            },
            avg_rating: {
                title: "میانگین امتیاز",
                emoji: "⭐"
            },
            ratings_count: {
                title: "تعداد امتیاز",
                emoji: "👥"
            },
            presence_duration: {
                title: "مدت حضور",
                emoji: "👤"
            },
            break_duration: {
                title: "مدت استراحت",
                emoji: "🚶‍♂️"
            },
            one_star_ratings: {
                title: "امتیاز ۱",
                emoji: "🌟"
            },
            calls_over_5_min: {
                title: "مکالمات بالای ۵ دقیقه",
                emoji: "⏰"
            },
            missed_calls: {
                title: "تماس بی پاسخ",
                emoji: "📵"
            },
            outbound_calls: {
                title: "تماس خروجی",
                emoji: "📲"
            },
            avg_talk_time_out: {
                title: "میانگین مکالمه (خروجی)",
                emoji: "📊"
            },
            no_call_reason: {
                title: "عدم ثبت دلیل تماس",
                emoji: "❓"
            },
            tickets_count: {
                title: "تعداد تیکت",
                emoji: "🎟️"
            },
            famas_count: {
                title: "تعداد فمس",
                emoji: "📄"
            },
            jira_count: {
                title: "تعداد جیرا",
                emoji: "✅"
            }
        };
        const timeBasedMetrics = ['total_talk_time_in', 'avg_talk_time_in', 'max_talk_time_in', 'avg_talk_time_out', 'presence_duration', 'break_duration'];
        const charts = {};

        document.addEventListener('DOMContentLoaded', () => {
            createNavButtons();
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            displayDailyReport(formatDate(yesterday));
        });

        function formatSeconds(seconds) {
            if (isNaN(seconds) || seconds < 0) return "00:00:00";
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = Math.floor(seconds % 60);
            return [h, m, s].map(v => v.toString().padStart(2, '0')).join(':');
        }

        function destroyCharts() {
            Object.values(charts).forEach(chart => chart?.destroy());
        }

        function formatDate(date) {
            return date.toISOString().split('T')[0];
        }

        function displayDailyReport(date) {
            destroyCharts();
            const reportContent = document.getElementById('report-content');
            const dataForDay = agentData[date];
            let html = '';
            for (const group in metricsConfig) {
                const {
                    title,
                    keys
                } = metricsConfig[group];
                html += `<div class="metric-group"><h2 class="metric-group-title">${title}</h2><div class="kpi-grid">`;
                keys.forEach(key => {
                    if (!labels[key]) return;
                    const hasValue = dataForDay && dataForDay[key] !== undefined && dataForDay[key] !== null;
                    let displayValue, p_class = "";
                    if (hasValue) {
                        const value = dataForDay[key];
                        displayValue = timeBasedMetrics.includes(key) ? formatSeconds(value) : Number(value).toLocaleString();
                    } else {
                        displayValue = "(داده‌ای ثبت نشده)";
                        p_class = "no-value-text";
                    }
                    html += `<div class="metric-card"><div class="emoji-container">${labels[key].emoji}</div><div class="content"><h3>${labels[key].title}</h3><p class="${p_class}">${displayValue}</p></div></div>`;
                });
                html += `</div></div>`;
            }
            reportContent.innerHTML = html;
            updateActiveButton(date);
        }

        function displaySummaryReport() {
            destroyCharts();
            const reportContent = document.getElementById('report-content');
            const dates = getLastNDays(7).reverse();
            const summary = {
                incoming_calls: 0,
                total_talk_time_in: 0,
                ratings_count: 0,
                total_rating_sum: 0,
                tickets_count: 0,
                famas_count: 0,
                jira_count: 0
            };
            const chartData = {
                labels: [],
                incoming: [],
                ratings: [],
                callsOver5Min: [],
                avgTalkTime: []
            };
            dates.forEach(date => {
                chartData.labels.push(new Date(date).toLocaleDateString('fa-IR', {
                    day: 'numeric',
                    month: 'short'
                }));
                const dayData = agentData[date];
                if (dayData) {
                    summary.incoming_calls += dayData.incoming_calls || 0;
                    summary.total_talk_time_in += dayData.total_talk_time_in || 0;
                    summary.ratings_count += dayData.ratings_count || 0;
                    summary.total_rating_sum += (dayData.avg_rating || 0) * (dayData.ratings_count || 0);
                    summary.tickets_count += dayData.tickets_count || 0;
                    summary.famas_count += dayData.famas_count || 0;
                    summary.jira_count += dayData.jira_count || 0;
                    chartData.incoming.push(dayData.incoming_calls || 0);
                    chartData.ratings.push(dayData.avg_rating || 0);
                    chartData.callsOver5Min.push(dayData.calls_over_5_min || 0);
                    chartData.avgTalkTime.push(dayData.avg_talk_time_in || 0);
                } else {
                    chartData.incoming.push(0);
                    chartData.ratings.push(0);
                    chartData.callsOver5Min.push(0);
                    chartData.avgTalkTime.push(0);
                }
            });
            const finalAvgRating = summary.ratings_count > 0 ? (summary.total_rating_sum / summary.ratings_count).toFixed(2) : 0;
            let html = `<div class="metric-group"><h2 class="metric-group-title">خلاصه عملکرد ۷ روز گذشته</h2><div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));"><div class="metric-card"><div class="emoji-container">📞</div><div class="content"><h3>مجموع تماس ورودی</h3><p>${Number(summary.incoming_calls).toLocaleString()}</p></div></div><div class="metric-card"><div class="emoji-container">⏱️</div><div class="content"><h3>مجموع مکالمات ورودی</h3><p>${formatSeconds(summary.total_talk_time_in)}</p></div></div><div class="metric-card"><div class="emoji-container">⭐</div><div class="content"><h3>میانگین امتیاز کل</h3><p>${finalAvgRating}</p></div></div><div class="metric-card"><div class="emoji-container">🎟️</div><div class="content"><h3>مجموع تیکت</h3><p>${Number(summary.tickets_count).toLocaleString()}</p></div></div><div class="metric-card"><div class="emoji-container">📄</div><div class="content"><h3>مجموع فمس</h3><p>${Number(summary.famas_count).toLocaleString()}</p></div></div><div class="metric-card"><div class="emoji-container">✅</div><div class="content"><h3>مجموع جیرا</h3><p>${Number(summary.jira_count).toLocaleString()}</p></div></div></div></div><div class="charts-container"><div class="chart-card"><canvas id="callsChart"></canvas></div><div class="chart-card"><canvas id="ratingsChart"></canvas></div><div class="chart-card"><canvas id="callsOver5MinChart"></canvas></div><div class="chart-card"><canvas id="avgTalkTimeChart"></canvas></div></div>`;
            reportContent.innerHTML = html;
            drawChart('callsChart', 'bar', chartData.labels, [{
                label: 'تماس ورودی',
                data: chartData.incoming,
                backgroundColor: 'rgba(0, 174, 112, 0.7)',
                borderRadius: 5
            }]);
            drawChart('ratingsChart', 'line', chartData.labels, [{
                label: 'میانگین امتیاز روزانه',
                data: chartData.ratings,
                borderColor: 'rgba(255, 159, 64, 1)',
                backgroundColor: 'rgba(255, 159, 64, 0.1)',
                fill: true,
                tension: 0.4
            }]);
            drawChart('callsOver5MinChart', 'bar', chartData.labels, [{
                label: 'مکالمات بالای ۵ دقیقه',
                data: chartData.callsOver5Min,
                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                borderRadius: 5
            }]);
            drawChart('avgTalkTimeChart', 'line', chartData.labels, [{
                label: 'میانگین مکالمه ورودی (ثانیه)',
                data: chartData.avgTalkTime,
                borderColor: 'rgba(13, 110, 253, 1)',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }]);
            updateActiveButton('summary');
        }

        function drawChart(canvasId, type, labels, datasets) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            Chart.defaults.font.family = 'Vazirmatn';
            charts[canvasId] = new Chart(ctx, {
                type,
                data: {
                    labels,
                    datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    size: 14
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        function getLastNDays(n) {
            return Array.from({
                length: n
            }, (_, i) => {
                const d = new Date();
                d.setDate(d.getDate() - i);
                return formatDate(d);
            });
        }

        function createNavButtons() {
            const nav = document.getElementById('date-nav');
            const dates = getLastNDays(7);
            let buttonsHtml = '<button id="btn-summary">خلاصه ۷ روز</button>';
            dates.forEach(date => {
                const dateFa = new Date(date).toLocaleDateString('fa-IR', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'short'
                });
                buttonsHtml += `<button id="btn-${date}" data-date="${date}">${dateFa}</button>`;
            });
            nav.innerHTML = buttonsHtml;
            document.getElementById('btn-summary').addEventListener('click', displaySummaryReport);
            dates.forEach(date => {
                document.getElementById(`btn-${date}`).addEventListener('click', () => displayDailyReport(date));
            });
        }

        function updateActiveButton(id) {
            document.querySelectorAll('#date-nav button').forEach(btn => btn.classList.remove('active'));
            const activeBtn = id === 'summary' ? document.getElementById('btn-summary') : document.getElementById(`btn-${id}`);
            if (activeBtn) activeBtn.classList.add('active');
        }
        // ADD THIS SCRIPT LOGIC inside the main <script> tag at the bottom of profile/index.php

        document.addEventListener('DOMContentLoaded', () => {
            // ... (your existing tab switching code is here, leave it as is)

            // --- New code for loading user assets ---
            let assetsLoaded = false; // A flag to prevent multiple API calls

            // Find the new tab link
            const myAssetsLink = document.querySelector('a[href="#my-assets"]');

            myAssetsLink.addEventListener('click', () => {
                // Load assets only once when the tab is first clicked
                if (!assetsLoaded) {
                    loadUserAssets();
                    assetsLoaded = true;
                }
            });

            async function loadUserAssets() {
                const container = document.getElementById('user-assets-container');
                container.innerHTML = '<p>در حال بارگذاری اطلاعات اموال...</p>';

                try {
                    const response = await fetch('/profile/profile-api.php?action=get_my_assets');
                    if (!response.ok) {
                        throw new Error('خطا در ارتباط با سرور.');
                    }
                    const assets = await response.json();

                    if (assets.length === 0) {
                        container.innerHTML = '<p>در حال حاضر هیچ کالایی به شما تخصیص داده نشده است.</p>';
                        return;
                    }

                    // Create the table structure
                    let tableHTML = `
                <table class="assets-table">
                    <thead>
                        <tr>
                            <th>نام کالا</th>
                            <th>شماره سریال</th>
                            <th>تاریخ تحویل</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

                    assets.forEach(asset => {
                        // Format the date to be more readable
                        const assignedDate = new Date(asset.assigned_at).toLocaleDateString('fa-IR', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });

                        tableHTML += `
                    <tr>
                        <td>${asset.name}</td>
                        <td>${asset.serial_number}</td>
                        <td><span class="date-chip">${assignedDate}</span></td>
                    </tr>
                `;
                    });

                    tableHTML += '</tbody></table>';
                    container.innerHTML = tableHTML;

                } catch (error) {
                    container.innerHTML = `<p style="color: #dc3545;">خطا در بارگذاری اطلاعات: ${error.message}</p>`;
                }
            }

            // Check if the page was loaded with #my-assets hash
            if (window.location.hash === '#my-assets') {
                // The existing tab switching logic will show the tab,
                // we just need to ensure the data is loaded.
                if (!assetsLoaded) {
                    loadUserAssets();
                    assetsLoaded = true;
                }
            }
        });
    </script>
</body>

</html>
