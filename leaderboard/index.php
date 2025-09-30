<?php
// فایل: leaderboard.php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/../auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>جدول امتیازات</title>
    <style>
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --primary-light: #e6f7f2;
            --highlight-bg: #fff8e1;
            --highlight-border: #ffecb3;
            --bg-color: #f5f7fa;
            --card-bg: #ffffff;
            --text-color: #263238;
            --header-text: #fff;
            --footer-h: 60px;
            --secondary-text: #546e7a;
            --border-color: #eceff1;
            --rank1-color: #ffc107;
            --rank2-color: #silver;
            --rank3-color: #d8a677;
            --radius: 16px;
            /* گردی بیشتر برای ظاهر مدرن */
            --shadow-md: 0 8px 25px rgba(0, 0, 0, 0.07);
            --transition-speed: 0.3s;
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
            padding-bottom: 100px;
            /* ایجاد فضا برای نوار شناور */
        }

        main {
            width: min(900px, 100%);
            padding: 2.5rem 1rem;
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
            flex-shrink: 0;
            min-height: var(--footer-h);
            font-size: .85rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: 2.8rem;
            margin-bottom: .5rem;
        }

        .page-subtitle {
            color: var(--secondary-text);
            font-weight: 400;
            font-size: 1.2rem;
        }

        /* Leaderboard Cards Layout */
        .leaderboard {
            display: grid;
            gap: 1.5rem;
        }

        .leaderboard-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            transition: transform var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
            position: relative;
            overflow: hidden;
            /* برای نوار رنگی کنار */
        }

        .leaderboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 8px;
            background-color: transparent;
        }

        .leaderboard-card.rank-1::before {
            background-color: var(--rank1-color);
        }

        .leaderboard-card.rank-2::before {
            background-color: var(--rank2-color);
        }

        .leaderboard-card.rank-3::before {
            background-color: var(--rank3-color);
        }


        .leaderboard-card.highlighted {
            border-color: var(--primary-color);
            box-shadow: 0 12px 30px rgba(0, 191, 165, 0.2);
        }

        .card-header {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            cursor: pointer;
            gap: 1.5rem;
        }

        .rank {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--secondary-text);
            min-width: 50px;
            text-align: center;
        }

        .rank-1 .rank {
            color: var(--rank1-color);
        }

        .rank-2 .rank {
            color: var(--rank2-color);
        }

        .rank-3 .rank {
            color: var(--rank3-color);
        }


        .team-info {
            flex-grow: 1;
        }

        .team-name {
            font-size: 1.3rem;
            font-weight: 700;
        }

        .total-score {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-dark);
            text-align: left;
            direction: ltr;
            /* برای نمایش صحیح اعداد */
        }

        .total-score small {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--secondary-text);
            margin-left: 5px;
        }

        .toggle-icon {
            font-size: 1.5rem;
            transition: transform var(--transition-speed) ease;
        }

        .leaderboard-card.open .toggle-icon {
            transform: rotate(90deg);
        }

        /* Member Details Section */
        .member-details {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out, padding 0.4s ease-out;
            background-color: #fafafa;
        }

        .leaderboard-card.open .member-details {
            max-height: 500px;
            /* ارتفاع کافی برای نمایش اعضا */
            transition: max-height 0.5s ease-in;
        }

        .member-details-content {
            padding: 0 1.5rem 1.5rem;
            border-top: 1px solid var(--border-color);
            margin: 0 1.5rem;
        }

        .member-details-title {
            font-size: 1rem;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            color: var(--secondary-text);
        }

        .member-list {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 0.75rem;
        }

        .member-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            background: var(--card-bg);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 0.95rem;
        }

        .member-item.highlighted {
            background-color: var(--highlight-bg);
            border-color: var(--highlight-border);
            font-weight: 700;
        }

        .member-score {
            font-weight: 600;
            direction: ltr;
        }

        /* My Team Sticky Bar */
        .my-team-sticky-bar {
            position: fixed;
            bottom: -100px;
            /* Start hidden */
            left: 0;
            right: 0;
            background: var(--card-bg);
            padding: 1rem;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.1);
            border-top: 3px solid var(--primary-color);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 100;
            transition: bottom var(--transition-speed) ease-in-out;
            visibility: hidden;
        }

        .my-team-sticky-bar.visible {
            bottom: 0;
            visibility: visible;
        }

        .sticky-stat {
            text-align: center;
        }

        .sticky-stat .label {
            font-size: 0.8rem;
            color: var(--secondary-text);
            margin-bottom: 0.25rem;
        }

        .sticky-stat .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-dark);
        }


        .loading-spinner {
            text-align: center;
            padding: 4rem;
            font-size: 1.2rem;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="page-header">
            <h1 class="page-title">🏆 جدول امتیازات</h1>
            <p class="page-subtitle">عملکرد تیم‌ها را مقایسه کنید و جایگاه خود را بیابید.</p>
        </div>

        <div class="leaderboard" id="leaderboard-container">
            <div class="loading-spinner">درحال بارگذاری اطلاعات...</div>
        </div>
    </main>

    <div id="my-team-sticky-bar-placeholder"></div>

    <div id="footer-placeholder"></div>
    <script src="/js/header.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const leaderboardContainer = document.getElementById('leaderboard-container');
            const myTeamStickyBarPlaceholder = document.getElementById('my-team-sticky-bar-placeholder');

            function renderMyTeamStickyBar(myTeam) {
                if (!myTeam) return;

                const barHTML = `
                    <div class="my-team-sticky-bar" id="my-team-bar">
                        <div class="sticky-stat">
                            <div class="label">رتبه شما</div>
                            <div class="value">#${myTeam.rank}</div>
                        </div>
                        <div class="sticky-stat">
                            <div class="label">تیم شما</div>
                            <div class="value">${myTeam.team_name}</div>
                        </div>
                        <div class="sticky-stat">
                            <div class="label">امتیاز کل</div>
                            <div class="value">${myTeam.total_score.toLocaleString()}</div>
                        </div>
                    </div>
                 `;
                myTeamStickyBarPlaceholder.innerHTML = barHTML;
                // Add class to make it visible with animation
                setTimeout(() => {
                    document.getElementById('my-team-bar').classList.add('visible');
                }, 100);
            }

            function renderLeaderboard(rankings, currentUser) {
                if (rankings.length === 0) {
                    leaderboardContainer.innerHTML = `<div class="loading-spinner">هنوز تیمی برای نمایش وجود ندارد.</div>`;
                    return;
                }

                let cardsHTML = rankings.map(team => {
                    const isMyTeam = currentUser && team.team_id === currentUser.teamId;
                    const isMyUser = (memberId) => currentUser && memberId === currentUser.id;
                    const rankIcon = team.rank === 1 ? '🥇' : team.rank === 2 ? '🥈' : team.rank === 3 ? '🥉' : `#${team.rank}`;

                    const membersHTML = team.members.map(member => `
                        <li class="member-item ${isMyUser(member.id) ? 'highlighted' : ''}">
                            <span>${member.name}</span>
                            <span class="member-score">${member.score.toLocaleString()}</span>
                        </li>
                    `).join('');

                    return `
                        <div class="leaderboard-card ${isMyTeam ? 'highlighted' : ''} rank-${team.rank}">
                            <div class="card-header">
                                <div class="rank">${rankIcon}</div>
                                <div class="team-info">
                                    <div class="team-name">${team.team_name}</div>
                                </div>
                                <div class="total-score">
                                    ${team.total_score.toLocaleString()} <small>امتیاز</small>
                                </div>
                                <div class="toggle-icon">▶️</div>
                            </div>
                            <div class="member-details">
                                <div class="member-details-content">
                                     <h4 class="member-details-title">اعضای تیم</h4>
                                     <ul class="member-list">${membersHTML}</ul>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

                leaderboardContainer.innerHTML = cardsHTML;
            }

            function setupEventListeners() {
                leaderboardContainer.addEventListener('click', (e) => {
                    const cardHeader = e.target.closest('.card-header');
                    if (!cardHeader) return;

                    const card = cardHeader.parentElement;
                    card.classList.toggle('open');
                });
            }

            // --- Main Execution ---
            try {
                const response = await fetch('leaderboard_api.php');
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const result = await response.json();

                if (!result.success) throw new Error(result.message || 'خطا در دریافت اطلاعات');

                const {
                    rankings,
                    currentUser
                } = result.data;

                renderLeaderboard(rankings, currentUser);

                if (currentUser && currentUser.teamId) {
                    const myTeamData = rankings.find(t => t.team_id === currentUser.teamId);
                    renderMyTeamStickyBar(myTeamData);
                }

                setupEventListeners();

            } catch (error) {
                console.error("Leaderboard Error:", error);
                leaderboardContainer.innerHTML = `<div class="loading-spinner" style="color: red;">خطا: ${error.message}</div>`;
            }
        });
    </script>
</body>

</html>
