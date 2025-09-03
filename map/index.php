<?php
// File Location: /map/index.php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>نقشه میز کار - وی هاب</title>
    <style>
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --bg-color: #f7f9fa;
            --card-bg: #fff;
            --text-color: #1a1a1a;
            --secondary-text: #555;
            --border-color: #e9e9e9;
            --radius: 12px;
            --shadow-sm: 0 2px 6px rgba(0, 120, 80, .06);
            --color-red: #F44336;
            --color-grey: #9E9E9E;
        }

        @font-face {
            font-family: "Vazirmatn";
            src: url("/assets/fonts/Vazirmatn[wght].ttf");
            font-weight: 100 900;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Vazirmatn", system-ui;
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
            padding: clamp(1rem, 3vw, 2rem);
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
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .map-container {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        #mapCanvas {
            width: 100%;
            height: auto;
            background-color: #fafafa;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
        }

        .map-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
            justify-content: center;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .9rem;
            font-weight: 500;
        }

        .legend-color {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .legend-available {
            background-color: var(--primary-color);
        }

        .legend-occupied {
            background-color: var(--color-red);
        }

        .legend-unavailable {
            background-color: var(--color-grey);
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <h1 class="page-title">انتخاب میز کار</h1>
        <p class="page-subtitle">میز کار مورد نظر خود را از روی نقشه انتخاب کنید.</p>
        <div class="map-container">
            <canvas id="mapCanvas" width="700" height="900"></canvas>
            <div class="map-legend">
                <div class="legend-item"><span class="legend-color legend-available"></span><span>در دسترس</span></div>
                <div class="legend-item"><span class="legend-color legend-occupied"></span><span>اشغال شده</span></div>
                <div class="legend-item"><span class="legend-color legend-unavailable"></span><span>خارج از دسترس</span></div>
            </div>
        </div>
    </main>
    <div id="footer-placeholder"></div>

    <script src="/js/header.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('mapCanvas');
            const ctx = canvas.getContext('2d');
            let seats = [];
            let users = [];
            let claims = <?php echo json_encode($claims); ?>;

            const COLOR_AVAILABLE = '#00ae70';
            const COLOR_OCCUPIED = '#F44336';
            const COLOR_UNAVAILABLE = '#9E9E9E';

            async function fetchDataAndDrawMap() {
                try {
                    const response = await fetch('/php/handler.php?action=getData');
                    const data = await response.json();
                    seats = data.seats || [];
                    users = data.users || [];
                    drawMap();
                } catch (error) {
                    console.error('Failed to fetch map data:', error);
                }
            }

            function drawMap() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                seats.forEach(seat => {
                    let labelText = seat.id;
                    let labelColor = '#FFFFFF';

                    if (seat.status === 'available') {
                        ctx.fillStyle = COLOR_AVAILABLE;
                    } else if (seat.status === 'occupied') {
                        ctx.fillStyle = COLOR_OCCUPIED;
                        const user = users.find(u => u.id === seat.userId);

                        // 🔽 تغییر اصلی اینجاست 🔽
                        if (user && user.name) {
                            const nameParts = user.name.split(' '); // نام را با فاصله جدا کن
                            labelText = nameParts.pop() || user.name; // قسمت آخر (فامیلی) را بگیر
                        }
                        // 🔼 پایان تغییرات 🔼

                    } else {
                        ctx.fillStyle = COLOR_UNAVAILABLE;
                    }

                    const pos = seat.position;
                    ctx.fillRect(pos.x, pos.y, pos.width, pos.height);

                    ctx.fillStyle = labelColor;
                    ctx.font = '12px Vazirmatn'; // بازگشت به فونت سایز ۱۲
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(labelText, pos.x + pos.width / 2, pos.y + pos.height / 2);
                });
            }

            canvas.addEventListener('click', async (e) => {
                const rect = canvas.getBoundingClientRect();
                const scaleX = canvas.width / rect.width;
                const scaleY = canvas.height / rect.height;
                const mouseX = (e.clientX - rect.left) * scaleX;
                const mouseY = (e.clientY - rect.top) * scaleY;

                for (const seat of seats) {
                    const pos = seat.position;
                    if (mouseX >= pos.x && mouseX <= pos.x + pos.width &&
                        mouseY >= pos.y && mouseY <= pos.y + pos.height) {

                        const userId = claims.sub || claims.id;
                        if (seat.status === 'available') {
                            if (confirm(`آیا می‌خواهید روی میز ${seat.id} بنشینید؟`)) {
                                await selectSeat(seat.id);
                            }
                        } else if (seat.userId === userId) {
                            if (confirm(`میز ${seat.id} متعلق به شماست. آیا می‌خواهید آن را رها کنید؟`)) {
                                await selectSeat(null);
                            }
                        } else {
                            const user = users.find(u => u.id === seat.userId);
                            const message = user ? `این میز توسط "${user.name}" اشغال شده است.` : 'این میز در دسترس نیست.';
                            alert(message);
                        }
                        break;
                    }
                }
            });

            async function selectSeat(seatId) {
                const formData = new FormData();
                formData.append('action', 'selectSeat');
                if (seatId) {
                    formData.append('seatId', seatId);
                }
                const response = await fetch('/php/handler.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    await fetchDataAndDrawMap();
                } else {
                    alert(result.message || 'خطایی رخ داد.');
                }
            }

            fetchDataAndDrawMap();
            setInterval(fetchDataAndDrawMap, 10000);
        });
    </script>
</body>

</html>
