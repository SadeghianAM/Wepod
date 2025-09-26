<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>گردونه شانس وی هاب (نسخه بهبودیافته)</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <style>
        /* ====================
            Root Variables from Vihub Design System
           ==================== */
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
            --danger-color: #d93025;
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

        /* ====================
            Base and Layout Styles
           ==================== */
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-size: 16px;
        }

        header {
            background: var(--card-bg);
            color: var(--primary-dark);
            box-shadow: 0 2px 8px var(--shadow-color-light);
            padding: 1rem 2rem;
            font-weight: 700;
            font-size: 1.25rem;
            z-index: 10;
        }

        main {
            flex-grow: 1;
            padding: 2rem;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 450px;
            gap: 2rem;
            align-items: start;
        }

        footer {
            background: var(--primary-color);
            color: var(--header-text);
            text-align: center;
            padding: 1.25rem;
            font-size: 0.9rem;
        }

        /* ====================
            Component Styles
           ==================== */
        .tool-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px var(--shadow-color-light);
            overflow: hidden;
        }

        .tool-card h2 {
            font-size: 1.1rem;
            font-weight: 700;
            padding: 0.9rem 1.5rem;
            background-color: var(--bg-color);
            color: var(--primary-dark);
            border-bottom: 1px solid var(--border-color);
        }

        .tool-card .card-content {
            padding: 1.5rem;
        }

        .column-info h2 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--primary-dark);
        }

        .column-info p,
        .column-info li {
            color: var(--secondary-text-color);
            line-height: 1.8;
            margin-bottom: 0.75rem;
        }

        .column-info ul {
            list-style: none;
            padding-right: 1.5rem;
        }

        .column-info li::before {
            content: "•";
            color: var(--primary-color);
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
            margin-right: 0.5rem;
        }

        /* ====================
            Improved Wheel Styles
           ==================== */
        .wheel-container {
            position: relative;
            width: 400px;
            height: 400px;
            margin: 0 auto 1.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .pin {
            width: 0;
            height: 0;
            border-left: 20px solid transparent;
            border-right: 20px solid transparent;
            border-top: 40px solid var(--danger-color);
            position: absolute;
            top: -20px;
            z-index: 10;
            filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.2));
            transition: transform 0.3s ease;
        }

        .wheel {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            position: relative;
            overflow: hidden;
            border: 8px solid #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1), inset 0 0 15px rgba(0, 0, 0, 0.1);
            transition: transform 8s cubic-bezier(0.2, 0.8, 0.2, 1);
            background: conic-gradient(#fff 0deg 360deg);
            /* Conic gradient is set by JS */
        }

        .wheel-text-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            transform-origin: center;
            /* Rotation is set by JS */
        }

        /* === CHANGED: Text styling for prizes === */
        .wheel-text {
            position: absolute;
            top: 48%;
            left: 40%;
            rotate: 30deg;
            text-align: center;
            max-width: 110px;
            color: white;
            font-weight: 700;
            /* Bolder font */
            font-size: 16px;
            /* Larger font */
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
            user-select: none;
            /* Move text outward and center vertically */
            transform: translate(90px, -50%);
        }

        /* === END CHANGE === */

        /* ====================
            Button & Feedback Styles
           ==================== */
        .spin-button {
            width: 100%;
            padding: 0.8rem 1rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--header-text);
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 0.6rem;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 10px var(--shadow-color-medium);
        }

        .spin-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px var(--shadow-color-medium);
        }

        .spin-button:active:not(:disabled) {
            transform: translateY(0);
        }

        .spin-button:disabled {
            background: #aaa;
            cursor: not-allowed;
            box-shadow: none;
        }

        .spin-error {
            color: var(--danger-color);
            font-size: 0.9rem;
            text-align: center;
            margin-top: 0.75rem;
            height: 1.2em;
            /* Reserve space to prevent layout shift */
        }

        /* ====================
            Redesigned Result Popup
           ==================== */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
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
            border-radius: var(--border-radius);
            padding: 1.5rem 2rem;
            line-height: 1.7;
            border: 1px solid transparent;
            border-inline-start-width: 5px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            min-width: 350px;
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28);
        }

        .popup-overlay.visible .popup-content {
            transform: scale(1);
        }

        .popup-content.positive-result {
            background-color: var(--primary-light);
            border-color: var(--primary-color);
        }

        .popup-content.negative-result {
            background-color: var(--danger-bg);
            border-color: var(--danger-color);
        }

        .popup-content h3 {
            font-weight: 900;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .popup-content p {
            font-size: 1rem;
            color: var(--secondary-text-color);
        }

        .popup-content strong {
            font-size: 1.2rem;
            font-weight: 700;
            display: block;
            margin: 1rem 0;
        }

        .popup-content.positive-result h3,
        .popup-content.positive-result strong {
            color: var(--primary-dark);
        }

        .popup-content.negative-result h3,
        .popup-content.negative-result strong {
            color: var(--danger-color);
        }

        #close-popup {
            margin-top: 1rem;
            padding: 0.5rem 1.5rem;
            background-color: #777;
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #close-popup:hover {
            background-color: #555;
        }

        /* ====================
            Responsive Design
           ==================== */
        @media (max-width: 900px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .column-wheel {
                order: 1;
            }

            .column-info {
                order: 2;
            }

            main {
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            main {
                padding: 1rem;
            }

            header {
                padding: 1rem;
                font-size: 1.1rem;
            }

            .tool-card .card-content {
                padding: 1rem;
            }

            .popup-content {
                min-width: 90%;
            }

            .wheel-container {
                width: 300px;
                height: 300px;
            }

            /* === CHANGED: Responsive text styling === */
            .wheel-text {
                font-size: 14px;
                font-weight: 600;
                transform: translate(65px, -50%);
            }

            /* === END CHANGE === */
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="main-content">
            <div class="column column-info">
                <h2>راهنمای گردونه شانس</h2>
                <p>شانس خود را برای برنده شدن جوایز هیجان‌انگیز امتحان کنید! هر روز یک بار فرصت چرخاندن گردونه را دارید.</p>
                <ul>
                    <li>جوایز شامل موارد مثبت (امتیاز، تخفیف) و منفی (پوچ) است.</li>
                    <li>شانس برنده شدن هر جایزه توسط مدیر سیستم تعیین می‌شود.</li>
                    <li>پس از هر بار چرخش، نتیجه به شما نمایش داده می‌شود.</li>
                    <li>با کلیک روی دکمه "بچرخان" سرنوشت خود را مشخص کنید!</li>
                </ul>
            </div>
            <div class="column column-wheel">
                <div class="tool-card">
                    <h2>🎡 گردونه شانس روزانه</h2>
                    <div class="card-content">
                        <div class="wheel-container">
                            <div class="pin"></div>
                            <div id="wheel" class="wheel"></div>
                        </div>
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
        <div class="popup-content">
            <h3 id="popup-title"></h3>
            <p id="popup-text"></p>
            <strong id="prize-name"></strong>
            <button id="close-popup">باشه</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const spinButton = document.getElementById('spin-button');
            const resultPopup = document.getElementById('result-popup');
            const popupContent = resultPopup.querySelector('.popup-content');
            const prizeNameElement = document.getElementById('prize-name');
            const closePopupButton = document.getElementById('close-popup');
            const popupTitle = document.getElementById('popup-title');
            const popupText = document.getElementById('popup-text');
            const wheel = document.getElementById('wheel');
            const spinError = document.getElementById('spin-error');

            let isSpinning = false;
            let prizes = [];
            let winnerPrize = null;

            /**
             * Creates the wheel using a modern conic-gradient for segments
             * and separate, readable text elements.
             * @param {Array} prizesData - Array of prize objects from the API.
             */
            function createWheel(prizesData) {
                prizes = prizesData;
                wheel.innerHTML = '';
                const numPrizes = prizes.length;
                if (numPrizes < 2) return;

                const segmentAngle = 360 / numPrizes;

                // 1. Create the background with conic-gradient
                const gradientStops = prizes.map((prize, index) => {
                    const startAngle = segmentAngle * index;
                    const endAngle = segmentAngle * (index + 1);
                    return `${prize.color} ${startAngle}deg ${endAngle}deg`;
                }).join(', ');

                wheel.style.background = `conic-gradient(${gradientStops})`;

                // 2. Add text labels in a readable way
                prizes.forEach((prize, index) => {
                    const textContainer = document.createElement('div');
                    textContainer.className = 'wheel-text-container';

                    // Rotate the container to the middle of the segment
                    const rotation = (segmentAngle * index) + (segmentAngle / 2);
                    textContainer.style.transform = `rotate(${rotation}deg)`;

                    const text = document.createElement('div');
                    text.className = 'wheel-text';
                    text.textContent = prize.text;

                    textContainer.appendChild(text);
                    wheel.appendChild(textContainer);
                });
            }


            /**
             * Initializes the wheel by fetching prizes from the server.
             */
            async function setupWheel() {
                try {
                    const response = await fetch('/admin/prize/prize-api.php?action=getPrizes');
                    if (!response.ok) throw new Error('Network response was not ok.');
                    const prizesData = await response.json();

                    if (!prizesData || prizesData.length < 2) {
                        spinButton.innerText = 'تعداد جوایز کافی نیست';
                        return;
                    }

                    createWheel(prizesData);

                    // Event listener for when the CSS transition (spin) ends
                    wheel.addEventListener('transitionend', () => {
                        // A short delay before showing the popup for better UX
                        setTimeout(() => showResult(winnerPrize), 300);
                    });

                    spinButton.innerText = 'بچرخان!';
                    spinButton.disabled = false;

                } catch (error) {
                    spinButton.innerText = 'خطا در بارگذاری';
                    spinError.textContent = 'امکان بارگذاری جوایز وجود ندارد.';
                    console.error("Failed to load prizes:", error);
                }
            }

            /**
             * Handles the spin button click event.
             */
            spinButton.addEventListener('click', async () => {
                if (isSpinning) return;
                isSpinning = true;
                spinButton.disabled = true;
                spinButton.innerText = 'در حال چرخش...';
                spinError.textContent = ''; // Clear previous errors
                winnerPrize = null;

                // Optional: Add a starting tick sound effect here
                // new Audio('/sounds/tick.mp3').play();

                try {
                    const response = await fetch('/admin/prize/prize-api.php?action=calculateWinner');
                    if (!response.ok) throw new Error('Server could not calculate a winner.');
                    const result = await response.json();

                    if (result.winner && typeof result.stopAngle !== 'undefined') {
                        winnerPrize = result.winner;

                        const fullSpins = 5; // Number of full rotations

                        // The pin is at the top (270 degrees). We want the winning angle to stop there.
                        const targetRotation = (fullSpins * 360) + 270 - result.stopAngle;

                        // Apply the rotation
                        wheel.style.transform = `rotate(${targetRotation}deg)`;

                    } else {
                        throw new Error(result.error || 'پاسخ نامعتبر از سرور');
                    }
                } catch (error) {
                    console.error('Spin error:', error);
                    spinError.textContent = 'خطایی رخ داد. لطفا دوباره تلاش کنید.';
                    isSpinning = false;
                    spinButton.disabled = false;
                    spinButton.innerText = 'بچرخان!';
                }
            });

            /**
             * Displays the result in a popup.
             * @param {Object} winner - The winning prize object.
             */
            function showResult(winner) {
                if (!winner) return;

                // Optional: Play a win or lose sound effect
                // if (winner.type === 'positive') new Audio('/sounds/win.mp3').play();
                // else new Audio('/sounds/lose.mp3').play();

                popupContent.classList.remove('positive-result', 'negative-result');

                if (winner.type === 'positive') {
                    popupContent.classList.add('positive-result');
                    popupTitle.innerText = '🎉 تبریک!';
                    popupText.innerText = 'شما برنده جایزه زیر شدید:';
                } else {
                    popupContent.classList.add('negative-result');
                    popupTitle.innerText = '😕 بیشتر تلاش کن!';
                    popupText.innerText = 'نتیجه شانس شما این بود:';
                }

                prizeNameElement.innerText = winner.name;
                resultPopup.classList.add('visible');
            }

            // Close button for the result popup
            closePopupButton.addEventListener('click', () => {
                resultPopup.classList.remove('visible');

                // Keep the button disabled according to the "one spin per day" rule
                spinButton.innerText = 'فردا دوباره تلاش کنید';
                spinButton.disabled = true;

                // Reset the wheel to its initial position without animation for the next page load
                wheel.style.transition = 'none';
                wheel.style.transform = 'rotate(0deg)';
                // Re-enable transition for the next spin
                setTimeout(() => wheel.style.transition = 'transform 8s cubic-bezier(0.2, 0.8, 0.2, 1)', 50);
            });

            // Initialize the wheel on page load
            await setupWheel();
        });
    </script>
</body>

</html>
