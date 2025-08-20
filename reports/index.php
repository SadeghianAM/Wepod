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
            --bg-color: #f8fcf9;
            --text-color: #222;
            --secondary-text-color: #555;
            --card-bg: #ffffff;
            --header-text: #ffffff;
            --shadow-color-light: rgba(0, 174, 112, 0.07);
            --shadow-color-medium: rgba(0, 174, 112, 0.12);
            --border-radius: 0.75rem;
            --border-color: #e9e9e9;
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
            box-shadow: 0 2px 6px var(--shadow-color-light);
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
            padding: 2rem 1rem;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
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
        }

        #date-nav button {
            font-size: 0.9rem;
            padding: 0.5rem 1.2rem;
            border-radius: 100px;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--secondary-text-color);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        #date-nav button:hover {
            background-color: #f0f0f0;
            border-color: #ccc;
        }

        #date-nav button.active {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            border-color: var(--primary-color);
            font-weight: 600;
        }

        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .metric-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: 0 3px 8px var(--shadow-color-light);
            border: 1px solid var(--border-color);
            border-inline-start: 5px solid var(--primary-color);
            transition: all 0.2s ease;
        }

        .metric-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px var(--shadow-color-medium);
            border-inline-start-color: var(--primary-dark);
        }

        .metric-card h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--secondary-text-color);
            margin-bottom: 0.75rem;
        }

        .metric-card p {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-color);
        }

        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: 0 3px 8px var(--shadow-color-light);
            border: 1px solid var(--border-color);
        }

        .summary-title {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 2rem;
        }

        .no-data {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--secondary-text-color);
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px dashed var(--border-color);
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
        const labels = {
            answered_calls: "پاسخ داده شده",
            total_talk_time: "مجموع مکالمه (ثانیه)",
            avg_talk_time: "میانگین مکالمه (ثانیه)",
            max_talk_time: "بیشترین زمان مکالمه (ثانیه)",
            avg_rating: "میانگین امتیاز",
            ratings_count: "تعداد امتیاز",
            outbound_calls: "تماس خروجی"
        };
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

        function destroyCharts() {
            Object.keys(charts).forEach(key => {
                if (charts[key]) charts[key].destroy();
            });
        }

        function formatDate(date) {
            return date.toISOString().split('T')[0];
        }

        function displayDailyReport(date) {
            destroyCharts();
            const reportContent = document.getElementById('report-content');
            const data = agentData[date];
            if (data) {
                let html = '<div class="report-grid">';
                for (const key in data) {
                    html += `
                        <div class="metric-card">
                            <h3>${labels[key] || key}</h3>
                            <p>${data[key].toLocaleString()}</p>
                        </div>`;
                }
                html += '</div>';
                reportContent.innerHTML = html;
            } else {
                reportContent.innerHTML = '<div class="no-data">گزارشی برای این روز ثبت نشده است.</div>';
            }
            updateActiveButton(date);
        }

        function displaySummaryReport() {
            destroyCharts();
            const reportContent = document.getElementById('report-content');
            const dates = getLastNDays(7);
            const summary = {
                answered_calls: 0,
                total_talk_time: 0,
                ratings_count: 0,
                avg_rating: 0,
                daysWithData: 0
            };
            const chartData = {
                labels: [],
                answered: [],
                ratings: []
            };

            dates.forEach(date => {
                chartData.labels.push(new Date(date).toLocaleDateString('fa-IR', {
                    day: 'numeric',
                    month: 'short'
                }));
                const dayData = agentData[date];
                if (dayData) {
                    summary.daysWithData++;
                    summary.answered_calls += dayData.answered_calls;
                    summary.total_talk_time += dayData.total_talk_time;
                    summary.ratings_count += dayData.ratings_count;
                    summary.avg_rating += dayData.avg_rating;
                    chartData.answered.push(dayData.answered_calls);
                    chartData.ratings.push(dayData.avg_rating);
                } else {
                    chartData.answered.push(0);
                    chartData.ratings.push(0);
                }
            });

            if (summary.daysWithData > 0) {
                summary.avg_rating = (summary.avg_rating / summary.daysWithData).toFixed(2);
            }

            let html = `
                <h2 class="summary-title">خلاصه عملکرد ۷ روز گذشته</h2>
                <div class="report-grid">
                    <div class="metric-card"><h3>مجموع تماس پاسخ داده شده</h3><p>${summary.answered_calls.toLocaleString()}</p></div>
                    <div class="metric-card"><h3>مجموع مکالمات (ثانیه)</h3><p>${summary.total_talk_time.toLocaleString()}</p></div>
                    <div class="metric-card"><h3>میانگین امتیاز</h3><p>${summary.avg_rating}</p></div>
                </div>
                <div class="charts-container" style="margin-top: 2.5rem;">
                    <div class="chart-card"><canvas id="callsChart"></canvas></div>
                    <div class="chart-card"><canvas id="ratingsChart"></canvas></div>
                </div>`;
            reportContent.innerHTML = html;
            drawChart('callsChart', 'bar', chartData.labels.reverse(), [{
                label: 'تماس پاسخ داده شده',
                data: chartData.answered.reverse(),
                backgroundColor: 'rgba(0, 174, 112, 0.7)'
            }]);
            drawChart('ratingsChart', 'line', chartData.labels, [{
                label: 'میانگین امتیاز',
                data: chartData.ratings.reverse(),
                borderColor: 'rgba(0, 174, 112, 1)',
                backgroundColor: 'rgba(0, 174, 112, 0.1)',
                fill: true,
                tension: 0.1
            }]);
            updateActiveButton('summary');
        }

        function drawChart(canvasId, type, labels, datasets) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            charts[canvasId] = new Chart(ctx, {
                type,
                data: {
                    labels,
                    datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    family: 'Vazirmatn'
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
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
            let buttonsHtml = '<button id="btn-summary">خلاصه ۷ روز گذشته</button>';
            const dates = getLastNDays(7);
            dates.forEach(date => {
                const dateFa = new Date(date).toLocaleDateString('fa-IR', {
                    weekday: 'long',
                    day: 'numeric'
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
