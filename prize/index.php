<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>گردونه شانس وی هاب (نسخه نهایی و هوشمند)</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <style>
        /* ====================
           Root Variables & Base Styles
           ==================== */
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --primary-light: #e6f7f2;
            --accent-color: #ffc107;
            --bg-color: #f4f8f7;
            --text-color: #2c3e50;
            --secondary-text-color: #555;
            --footer-h: 60px;
            --card-bg: #ffffff;
            --header-text: #ffffff;
            --shadow-color-light: rgba(0, 174, 112, 0.07);
            --shadow-color-medium: rgba(0, 174, 112, 0.12);
            --danger-color: #e74c3c;
            --danger-bg: #fce8e6;
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
            background-image: linear-gradient(180deg, var(--bg-color) 0%, #eef5f3 100%);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-size: 16px;
        }


        main {
            flex-grow: 1;
            padding: 2.5rem 2rem;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
        }

        .main-content {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 2.5rem;
            align-items: center;
        }

        /* ====================
           Info Column with Emojis
           ==================== */
        .column-info h2 {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: var(--primary-dark);
        }

        .info-card-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .info-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: var(--card-bg);
            padding: 1rem;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px var(--shadow-color-light);
        }

        .info-card-emoji {
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            background-color: var(--primary-light);
            border-radius: 50%;
            font-size: 1.5rem;
        }

        .info-card-text h3 {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .info-card-text p {
            font-size: 0.875rem;
            color: var(--secondary-text-color);
            line-height: 1.6;
        }


        /* ====================
           Wheel Styles
           ==================== */
        .tool-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px var(--shadow-color-light);
            overflow: hidden;
            padding: 1.5rem;
        }

        .wheel-wrapper {
            position: relative;
            width: 450px;
            height: 450px;
            margin: 0 auto;
        }

        .pin {
            width: 0;
            height: 0;
            border-left: 20px solid transparent;
            border-right: 20px solid transparent;
            border-top: 40px solid var(--danger-color);
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.2));
            transition: transform 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28);
        }

        @keyframes pin-jiggle {

            0%,
            100% {
                transform: translateX(-50%) rotate(0deg);
            }

            25% {
                transform: translateX(-50%) rotate(-5deg);
            }

            75% {
                transform: translateX(-50%) rotate(5deg);
            }
        }

        .wheel {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            position: relative;
            overflow: hidden;
            border: 10px solid #fff;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1), inset 0 0 20px rgba(0, 0, 0, 0.15);
            transition: transform 8s cubic-bezier(0.2, 0.8, 0.2, 1);
            background: conic-gradient(#fff 0deg 360deg);
        }

        .wheel-center {
            position: absolute;
            width: 80px;
            height: 80px;
            background: #fff;
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 5;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2), inset 0 0 5px rgba(0, 0, 0, 0.1);
            display: grid;
            place-items: center;
        }

        .wheel-center::before {
            content: '';
            width: 30px;
            height: 30px;
            background: var(--accent-color);
            border-radius: 50%;
            border: 5px solid #fff;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        }

        .wheel-text-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            transform-origin: center;
        }

        /* === FINALIZED: Dynamic Text Styling === */
        .wheel-text {
            position: absolute;
            top: 10%;
            /* متن را به لبه بیرونی نزدیک‌تر می‌کند */
            left: 50%;
            text-align: center;
            max-width: 85%;
            /* تضمین می‌کند متن از لبه‌ها بیرون نزند */
            color: white;
            font-weight: 700;
            font-size: 15px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
            user-select: none;
            /* transform داینامیک توسط جاوا اسکریپت تنظیم می‌شود */
        }

        /* === END FINALIZED === */

        /* ====================
           Button & Feedback
           ==================== */
        .spin-controls {
            padding: 1.5rem 1rem 0;
        }

        .spin-button {
            width: 100%;
            padding: 1rem;
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(45deg, var(--primary-dark), var(--primary-color), var(--primary-dark));
            background-size: 200% 100%;
            border: none;
            border-radius: 0.6rem;
            cursor: pointer;
            transition: all 0.4s ease-in-out;
            box-shadow: 0 4px 15px rgba(0, 174, 112, 0.3);
            letter-spacing: 0.5px;
        }

        .spin-button:hover:not(:disabled) {
            background-position: 100% 0;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 174, 112, 0.4);
        }

        .spin-button:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(0, 174, 112, 0.3);
        }

        .spin-button:disabled {
            background: #aaa;
            cursor: not-allowed;
            box-shadow: none;
            color: #e0e0e0;
        }

        .spin-error {
            color: var(--danger-color);
            font-size: 0.9rem;
            text-align: center;
            margin-top: 1rem;
            height: 1.2em;
        }

        /* ====================
           Result Popup
           ==================== */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }

        .popup-overlay.visible {
            opacity: 1;
            visibility: visible;
        }

        .popup-content {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            text-align: center;
            min-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transform: scale(0.9) translateY(20px);
            transition: transform 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28);
            position: relative;
            overflow: hidden;
        }

        .popup-overlay.visible .popup-content {
            transform: scale(1) translateY(0);
        }

        #popup-icon {
            font-size: 4rem;
            line-height: 1;
            margin-bottom: 1rem;
        }

        .popup-content h3 {
            font-weight: 800;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .popup-content p {
            font-size: 1rem;
            color: var(--secondary-text-color);
            margin-bottom: 1.5rem;
        }

        #prize-name {
            font-size: 1.5rem;
            font-weight: 700;
            display: block;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            margin: 0 auto 1.5rem;
            background: #f0f0f0;
            max-width: 90%;
        }

        .popup-content.positive-result {
            border-top: 5px solid var(--primary-color);
        }

        .popup-content.positive-result #popup-icon,
        .popup-content.positive-result h3,
        .popup-content.positive-result #prize-name {
            color: var(--primary-dark);
        }

        .popup-content.positive-result #prize-name {
            background-color: var(--primary-light);
        }

        .popup-content.negative-result {
            border-top: 5px solid var(--danger-color);
        }

        .popup-content.negative-result #popup-icon,
        .popup-content.negative-result h3,
        .popup-content.negative-result #prize-name {
            color: var(--danger-color);
        }

        .popup-content.negative-result #prize-name {
            background-color: var(--danger-bg);
        }

        #close-popup {
            margin-top: 1rem;
            padding: 0.75rem 2rem;
            background-color: #555;
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.2s;
        }

        #close-popup:hover {
            background-color: var(--text-color);
        }

        #confetti-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 10;
        }

        /* ====================
           Footer Styles
           ==================== */
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

        footer {
            min-height: var(--footer-h);
            font-size: .85rem;
            justify-content: center;
        }

        /* ====================
           Responsive Design
           ==================== */
        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 2rem;
                align-items: center;
            }

            .column-wheel {
                order: 1;
            }

            .column-info {
                order: 2;
                text-align: center;
            }

            .info-card {
                text-align: right;
            }
        }

        @media (max-width: 480px) {
            main {
                padding: 1.5rem 1rem;
            }

            header {
                padding: 1rem;
                font-size: 1.1rem;
            }

            .tool-card {
                padding: 1rem;
            }

            .wheel-wrapper {
                width: 300px;
                height: 300px;
            }

            .wheel-text {
                font-size: 12px;
            }

            .wheel-center {
                width: 50px;
                height: 50px;
            }

            .wheel-center::before {
                width: 20px;
                height: 20px;
            }

            .popup-content {
                min-width: 90%;
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="main-content">
            <div class="column column-info">
                <h2>گردونه شانس وی‌هاب</h2>
                <div class="info-card-container">
                    <div class="info-card">
                        <div class="info-card-emoji">🗓️</div>
                        <div class="info-card-text">
                            <h3>یک شانس در روز</h3>
                            <p>هر روز یک فرصت رایگان برای چرخاندن گردونه و برنده شدن دارید.</p>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-card-emoji">⚡️</div>
                        <div class="info-card-text">
                            <h3>نتیجه آنی</h3>
                            <p>بلافاصله پس از توقف گردونه، جایزه خود را مشاهده کنید.</p>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-card-emoji">🏆</div>
                        <div class="info-card-text">
                            <h3>جوایز متنوع</h3>
                            <p>از امتیاز و اعتبار گرفته تا کدهای تخفیف هیجان‌انگیز.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column column-wheel">
                <div class="tool-card">
                    <div class="wheel-wrapper">
                        <div class="pin"></div>
                        <div class="wheel" id="wheel"></div>
                        <div class="wheel-center"></div>
                    </div>
                    <div class="spin-controls">
                        <button class="spin-button" id="spin-button" disabled>در حال بارگذاری...</button>
                        <div class="spin-error" id="spin-error"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="footer-placeholder"></div>
    <script src="/js/header.js"></script>

    <div class="popup-overlay" id="result-popup">
        <canvas id="confetti-canvas"></canvas>
        <div class="popup-content">
            <div id="popup-icon"></div>
            <h3 id="popup-title"></h3>
            <p id="popup-text"></p>
            <div id="prize-name"></div>
            <button id="close-popup">باشه</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const spinButton = document.getElementById('spin-button');
            const resultPopup = document.getElementById('result-popup');
            const popupContent = resultPopup.querySelector('.popup-content');
            const prizeNameElement = document.getElementById('prize-name');
            const closePopupButton = document.getElementById('close-popup');
            const popupTitle = document.getElementById('popup-title');
            const popupText = document.getElementById('popup-text');
            const popupIcon = document.getElementById('popup-icon');
            const wheel = document.getElementById('wheel');
            const pin = document.querySelector('.pin');
            const spinError = document.getElementById('spin-error');
            const confettiCanvas = document.getElementById('confetti-canvas');
            const confettiInstance = confetti.create(confettiCanvas, {
                resize: true,
                useWorker: true
            });

            let isSpinning = false;
            let prizes = [];
            let winnerPrize = null;
            let currentRotation = 0;

            function createWheel(prizesData) {
                prizes = prizesData;
                wheel.innerHTML = '';
                const numPrizes = prizes.length;
                if (numPrizes < 2) return;

                const segmentAngle = 360 / numPrizes;

                const gradientStops = prizes.map((prize, index) => {
                    const startAngle = segmentAngle * index;
                    const endAngle = segmentAngle * (index + 1);
                    return `${prize.color} ${startAngle}deg ${endAngle}deg`;
                }).join(', ');

                wheel.style.background = `conic-gradient(${gradientStops})`;

                prizes.forEach((prize, index) => {
                    const textContainer = document.createElement('div');
                    textContainer.className = 'wheel-text-container';

                    const containerRotation = (segmentAngle * index) + (segmentAngle / 2);
                    textContainer.style.transform = `rotate(${containerRotation}deg)`;

                    const text = document.createElement('div');
                    text.className = 'wheel-text';
                    text.textContent = prize.text;

                    if (containerRotation > 90 && containerRotation < 270) {
                        text.style.transform = 'translateX(-50%) rotate(180deg)';
                    } else {
                        text.style.transform = 'translateX(-50%)';
                    }

                    textContainer.appendChild(text);
                    wheel.appendChild(textContainer);
                });
            }


            async function setupWheel() {
                try {
                    // ===================================================================
                    // ** تغییر اول: اضافه شدن پارامتر ضد کش **
                    const response = await fetch('/prize/wheel-api.php?action=getPrizes&_=' + new Date().getTime());
                    // ===================================================================
                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.error || 'Network response was not ok.');
                    }
                    const prizesData = await response.json();

                    if (!prizesData || prizesData.length === 0) {
                        spinButton.innerText = 'جایزه‌ای تعریف نشده';
                        return;
                    }

                    createWheel(prizesData);

                    wheel.addEventListener('transitionend', () => {
                        pin.style.animation = 'pin-jiggle 0.5s';
                        setTimeout(() => {
                            showResult(winnerPrize);
                            pin.style.animation = 'none';
                        }, 500);
                    });

                    spinButton.innerText = 'بچرخان!';
                    spinButton.disabled = false;

                } catch (error) {
                    spinButton.innerText = 'خطا در بارگذاری';
                    spinError.textContent = error.message || 'امکان بارگذاری جوایز وجود ندارد.';
                    console.error("Failed to load prizes:", error);
                }
            }

            spinButton.addEventListener('click', async () => {
                if (isSpinning) return;
                isSpinning = true;
                spinButton.disabled = true;
                spinButton.innerText = 'در حال چرخش...';
                spinError.textContent = '';
                winnerPrize = null;

                try {
                    // ===================================================================
                    // ** تغییر دوم: اضافه شدن پارامتر ضد کش **
                    const response = await fetch('/prize/wheel-api.php?action=calculateWinner&_=' + new Date().getTime());
                    // ===================================================================
                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.error || 'Server could not calculate a winner.');
                    }
                    const result = await response.json();

                    if (result.winner && typeof result.stopAngle !== 'undefined') {
                        winnerPrize = result.winner;

                        const fullSpins = 5;
                        const randomOffset = Math.random() * 2 - 1;
                        const newRotation = (fullSpins * 360) + result.stopAngle + randomOffset;

                        const targetRotation = currentRotation - (currentRotation % 360) + newRotation;

                        currentRotation = targetRotation;

                        wheel.style.transform = `rotate(${targetRotation}deg)`;

                    } else {
                        throw new Error(result.error || 'پاسخ نامعتبر از سرور');
                    }
                } catch (error) {
                    console.error('Spin error:', error);
                    spinError.textContent = error.message || 'خطایی رخ داد. لطفا دوباره تلاش کنید.';
                    isSpinning = false;
                    spinButton.disabled = false;
                    spinButton.innerText = 'بچرخان!';
                }
            });

            function showResult(winner) {
                if (!winner) return;

                popupContent.classList.remove('positive-result', 'negative-result');

                if (winner.type === 'positive') {
                    popupContent.classList.add('positive-result');
                    popupIcon.innerText = '🎉';
                    popupTitle.innerText = 'تبریک!';
                    popupText.innerText = 'شما برنده جایزه زیر شدید:';
                    confettiInstance({
                        particleCount: 100,
                        spread: 70,
                        origin: {
                            y: 0.6
                        }
                    });
                } else {
                    popupContent.classList.add('negative-result');
                    popupIcon.innerText = '😕';
                    popupTitle.innerText = 'شانس با شما یار نبود!';
                    popupText.innerText = 'نتیجه شانس شما این بود:';
                }

                prizeNameElement.innerText = winner.name;
                resultPopup.classList.add('visible');
            }

            closePopupButton.addEventListener('click', () => {
                resultPopup.classList.remove('visible');

                spinButton.innerText = 'فردا دوباره تلاش کنید';
                spinButton.disabled = true;
            });

            await setupWheel();
        });
    </script>
</body>

</html>
