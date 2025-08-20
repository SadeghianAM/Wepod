<?php
require_once __DIR__ . '/../php/jwt-functions.php';

if (!isset($_COOKIE['jwt_token'])) {
    header('Location: /login.html');
    exit;
}
$token = $_COOKIE['jwt_token'];
$agentId = null;
$agentData = [];

if (verify_jwt($token, JWT_SECRET)) {
    $payload = get_payload($token);
    $agentId = $payload['id'] ?? null;

    if ($agentId) {
        $jsonFile = __DIR__ . '/../data/reports.json';
        if (file_exists($jsonFile)) {
            $allData = json_decode(file_get_contents($jsonFile), true);
            if (isset($allData[$agentId])) {
                $agentData = $allData[$agentId];
            }
        }
    } else {
        setcookie('jwt_token', '', time() - 3600, '/');
        header('Location: /login.html');
        exit;
    }
} else {
    setcookie('jwt_token', '', time() - 3600, '/');
    header('Location: /login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>داشبورد عملکرد کارشناس</title>
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

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            direction: rtl;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header,
        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px var(--shadow-color);
            position: relative;
            z-index: 10;
            flex-shrink: 0;
        }

        header {
            height: 70px;
        }

        header h1 {
            font-size: 1.2rem;
            font-weight: 700;
        }

        footer {
            height: 60px;
            font-size: 0.85rem;
            margin-top: auto;
        }

        main {
            flex-grow: 1;
            padding: 2rem 1.5rem;
            max-width: 1300px;
            width: 100%;
            margin: 0 auto;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
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
            color: var(--text-color);
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
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="dashboard-header">
            <h1 id="welcome-title">داشبورد عملکرد</h1>
            <p id="agent-name-display">در حال بارگذاری اطلاعات کاربری...</p>
        </div>
        <nav id="date-nav"></nav>
        <div id="report-content"></div>
    </main>
    <div id="footer-placeholder"></div>

    <script src="/js/header.js"></script>
    <script>
        const agentData = <?php echo json_encode($agentData); ?>;
        const metricsConfig = {
            performance: {
                title: "عملکرد کلی تماس‌ها",
                keys: ["answered_calls", "total_talk_time", "avg_talk_time", "max_talk_time", "missed_calls", "outbound_calls", "calls_over_5_min"]
            },
            quality: {
                title: "کیفیت و بازخورد",
                keys: ["avg_rating", "ratings_count", "one_star_ratings"]
            },
            productivity: {
                title: "بهره‌وری و مدیریت زمان",
                keys: ["presence_duration", "off_queue_duration"]
            },
            tasks: {
                title: "سایر وظایف",
                keys: ["tickets_count", "fams_count", "jira_count", "no_call_reason"]
            }
        };
        const labels = {
            answered_calls: {
                title: "پاسخ داده شده",
                emoji: "📞"
            },
            total_talk_time: {
                title: "مجموع مکالمه",
                emoji: "⏱️"
            },
            avg_talk_time: {
                title: "میانگین مکالمه",
                emoji: "⏳"
            },
            max_talk_time: {
                title: "بیشترین زمان مکالمه",
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
            off_queue_duration: {
                title: "مدت خروج از صف",
                emoji: "🚶‍♂️"
            },
            one_star_ratings: {
                title: "امتیاز ۱ داده شده",
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
            no_call_reason: {
                title: "عدم ثبت دلیل تماس",
                emoji: "❓"
            },
            tickets_count: {
                title: "تعداد تیکت",
                emoji: "🎟️"
            },
            fams_count: {
                title: "تعداد FAMS",
                emoji: "📄"
            },
            jira_count: {
                title: "تعداد جیرا",
                emoji: "✅"
            }
        };
        const timeBasedMetrics = ['total_talk_time', 'avg_talk_time', 'max_talk_time', 'presence_duration', 'off_queue_duration'];
        const charts = {};

        document.addEventListener('DOMContentLoaded', () => {
            fetch('/php/get-user-info.php').then(res => res.json()).then(data => {
                if (data.name) {
                    document.getElementById('agent-name-display').textContent = `گزارش‌های روزانه شما، ${data.name}`;
                }
            }).catch(err => {
                document.getElementById('agent-name-display').textContent = 'اطلاعات کاربری یافت نشد.';
            });
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
                    const hasValue = dataForDay && dataForDay[key] !== undefined && dataForDay[key] !== null;
                    let displayValue, p_class = "";
                    if (hasValue) {
                        const value = dataForDay[key];
                        displayValue = timeBasedMetrics.includes(key) ? formatSeconds(value) : value.toLocaleString();
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
                answered_calls: 0,
                total_talk_time: 0,
                ratings_count: 0,
                total_rating_sum: 0,
                tickets_count: 0,
                fams_count: 0,
                jira_count: 0
            };
            const chartData = {
                labels: [],
                answered: [],
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
                    summary.answered_calls += dayData.answered_calls || 0;
                    summary.total_talk_time += dayData.total_talk_time || 0;
                    summary.ratings_count += dayData.ratings_count || 0;
                    summary.total_rating_sum += (dayData.avg_rating || 0) * (dayData.ratings_count || 0);
                    summary.tickets_count += dayData.tickets_count || 0;
                    summary.fams_count += dayData.fams_count || 0;
                    summary.jira_count += dayData.jira_count || 0;
                    chartData.answered.push(dayData.answered_calls || 0);
                    chartData.ratings.push(dayData.avg_rating || 0);
                    chartData.callsOver5Min.push(dayData.calls_over_5_min || 0);
                    chartData.avgTalkTime.push(dayData.avg_talk_time || 0);
                } else {
                    chartData.answered.push(0);
                    chartData.ratings.push(0);
                    chartData.callsOver5Min.push(0);
                    chartData.avgTalkTime.push(0);
                }
            });

            const finalAvgRating = summary.ratings_count > 0 ? (summary.total_rating_sum / summary.ratings_count).toFixed(2) : 0;

            let html = `
                <div class="metric-group">
                    <h2 class="metric-group-title">خلاصه عملکرد ۷ روز گذشته</h2>
                    <div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="metric-card"><div class="emoji-container">📞</div><div class="content"><h3>مجموع تماس پاسخ داده شده</h3><p>${summary.answered_calls.toLocaleString()}</p></div></div>
                        <div class="metric-card"><div class="emoji-container">⏱️</div><div class="content"><h3>مجموع مکالمات</h3><p>${formatSeconds(summary.total_talk_time)}</p></div></div>
                        <div class="metric-card"><div class="emoji-container">⭐</div><div class="content"><h3>میانگین امتیاز کل</h3><p>${finalAvgRating}</p></div></div>
                        <div class="metric-card"><div class="emoji-container">🎟️</div><div class="content"><h3>مجموع تیکت</h3><p>${summary.tickets_count.toLocaleString()}</p></div></div>
                        <div class="metric-card"><div class="emoji-container">📄</div><div class="content"><h3>مجموع FAMS</h3><p>${summary.fams_count.toLocaleString()}</p></div></div>
                        <div class="metric-card"><div class="emoji-container">✅</div><div class="content"><h3>مجموع جیرا</h3><p>${summary.jira_count.toLocaleString()}</p></div></div>
                    </div>
                </div>
                <div class="charts-container" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="chart-card"><canvas id="callsChart"></canvas></div>
                    <div class="chart-card"><canvas id="ratingsChart"></canvas></div>
                    <div class="chart-card"><canvas id="callsOver5MinChart"></canvas></div>
                    <div class="chart-card"><canvas id="avgTalkTimeChart"></canvas></div>
                </div>`;
            reportContent.innerHTML = html;

            drawChart('callsChart', 'bar', chartData.labels, [{
                label: 'تماس پاسخ داده شده',
                data: chartData.answered,
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
                label: 'میانگین مکالمه (ثانیه)',
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
                type: type,
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
            const dates = [];
            for (let i = 0; i < n; i++) {
                const d = new Date();
                d.setDate(d.getDate() - i);
                dates.push(formatDate(d));
            }
            return dates;
        }

        function createNavButtons() {
            const nav = document.getElementById('date-nav');
            let buttonsHtml = '<button id="btn-summary">خلاصه ۷ روز</button>';
            const dates = getLastNDays(7);
            dates.forEach(date => {
                const dateFa = new Date(date).toLocaleDateString('fa-IR', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long'
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
            if (activeBtn) {
                activeBtn.classList.add('active');
            }
        }
    </script>
</body>

</html>
