<?php
// File Location: /admin/map-editor.php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ویرایشگر نقشه - وی هاب</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <style>
        /* کدهای CSS مشترک با پنل مدیریت شما */
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
            --color-blue: #0d6efd;
            --color-red: #dc3545;
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
            width: min(1400px, 100%);
            padding: 2rem;
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

        /* استایل‌های اختصاصی ویرایشگر */
        .editor-container {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
        }

        .toolbar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .btn {
            padding: .6rem 1.2rem;
            border: none;
            border-radius: 8px;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color .2s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: var(--color-blue);
            color: white;
        }

        #canvas-wrapper {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }

        #status-message {
            margin-top: 1rem;
            font-weight: 500;
            color: var(--primary-dark);
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <h1 class="page-title">ویرایشگر نقشه میزهای کاری</h1>
        <p class="page-subtitle">میزها را اضافه، جابجا یا حذف کنید و در نهایت تغییرات را ذخیره نمایید.</p>

        <div class="editor-container">
            <div class="toolbar">
                <button id="addSeatBtn" class="btn btn-primary">افزودن میز جدید</button>
                <button id="saveMapBtn" class="btn btn-secondary">ذخیره تغییرات</button>
            </div>
            <div id="canvas-wrapper">
                <canvas id="mapCanvas" width="1200" height="700"></canvas>
            </div>
            <p id="status-message"></p>
        </div>
    </main>
    <div id="footer-placeholder"></div>

    <script src="/js/header.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = new fabric.Canvas('mapCanvas');
            const statusMessage = document.getElementById('status-message');

            // --- بارگذاری نقشه اولیه ---
            async function loadMap() {
                try {
                    const response = await fetch('/php/map-handler.php?action=load');
                    const data = await response.json();
                    canvas.clear();
                    if (data.seats) {
                        data.seats.forEach(seat => createSeatObject(seat));
                    }
                    canvas.renderAll();
                    statusMessage.textContent = 'نقشه با موفقیت بارگذاری شد.';
                } catch (error) {
                    console.error('Error loading map:', error);
                    statusMessage.textContent = 'خطا در بارگذاری نقشه.';
                }
            }

            // --- ایجاد یک آبجکت میز روی Canvas ---
            function createSeatObject(seatData) {
                const rect = new fabric.Rect({
                    width: seatData.position.width,
                    height: seatData.position.height,
                    fill: 'rgba(0, 174, 112, 0.7)',
                    stroke: 'rgba(8, 152, 99, 1)',
                    strokeWidth: 2,
                    originX: 'center',
                    originY: 'center',
                });

                const text = new fabric.IText(seatData.id, {
                    fontFamily: 'Vazirmatn',
                    fontSize: 14,
                    fill: '#fff',
                    originX: 'center',
                    originY: 'center',
                });

                const group = new fabric.Group([rect, text], {
                    left: seatData.position.x,
                    top: seatData.position.y,
                    // اطلاعات سفارشی را به آبجکت اضافه می‌کنیم
                    seatId: seatData.id
                });
                canvas.add(group);
            }

            // --- رویدادهای دکمه‌ها ---
            document.getElementById('addSeatBtn').addEventListener('click', () => {
                const seatId = prompt("لطفاً یک شناسه منحصر به فرد برای میز جدید وارد کنید (مثلاً D-10):", "New-Seat");
                if (seatId && seatId.trim() !== "") {
                    createSeatObject({
                        id: seatId.trim(),
                        position: {
                            x: 50,
                            y: 50,
                            width: 60,
                            height: 60
                        }
                    });
                    canvas.renderAll();
                    statusMessage.textContent = "میز جدید اضافه شد. آن را در جای مناسب قرار دهید.";
                }
            });

            document.getElementById('saveMapBtn').addEventListener('click', async () => {
                const seatsData = {
                    seats: []
                };
                canvas.getObjects().forEach(obj => {
                    if (obj.seatId) {
                        seatsData.seats.push({
                            id: obj.seatId,
                            status: 'available', // همیشه در ویرایشگر available است
                            position: {
                                x: Math.round(obj.left),
                                y: Math.round(obj.top),
                                width: Math.round(obj.getScaledWidth()),
                                height: Math.round(obj.getScaledHeight())
                            },
                            userId: null
                        });
                    }
                });

                try {
                    statusMessage.textContent = "در حال ذخیره سازی...";
                    const response = await fetch('/php/map-handler.php?action=save', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(seatsData)
                    });
                    const result = await response.json();
                    statusMessage.textContent = result.message;
                } catch (error) {
                    console.error('Error saving map:', error);
                    statusMessage.textContent = 'خطا در ذخیره سازی نقشه.';
                }
            });

            // --- قابلیت‌های تعاملی ---
            // ویرایش شناسه با دابل کلیک
            canvas.on('mouse:dblclick', (options) => {
                if (options.target && options.target.seatId) {
                    const newId = prompt("شناسه جدید را وارد کنید:", options.target.seatId);
                    if (newId && newId.trim() !== "") {
                        const group = options.target;
                        group.seatId = newId.trim();
                        const text = group.getObjects('i-text')[0];
                        text.set('text', newId.trim());
                        canvas.renderAll();
                    }
                }
            });

            // حذف میز با دکمه Delete
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Delete' || e.key === 'Backspace') {
                    const activeObject = canvas.getActiveObject();
                    if (activeObject) {
                        if (confirm(`آیا از حذف میز "${activeObject.seatId}" مطمئن هستید؟`)) {
                            canvas.remove(activeObject);
                            canvas.renderAll();
                        }
                    }
                }
            });

            // اولین بار نقشه را بارگذاری کن
            loadMap();
        });
    </script>
</body>

</html>
