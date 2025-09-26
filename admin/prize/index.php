<?php
// فایل: admin/prize/index.php

// این بخش مسئول احراز هویت و کنترل دسترسی به صفحه است
// اطمینان حاصل می‌کند که فقط کاربر با نقش 'admin' می‌تواند این صفحه را ببیند
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت گردونه شانس</title>
    <style>
        /* ====================
            Root Variables & Base Styles
           ==================== */
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --primary-light: #e6f7f2;
            --danger-color: #d93025;
            --danger-bg: #fce8e6;
            --border-color: #e0e0e0;
            --card-bg: #ffffff;
            --text-color: #222;
            --secondary-text-color: #555;
            --bg-color: #f8fcf9;
            --border-radius: 0.75rem;
            --shadow-light: rgba(0, 174, 112, 0.07);
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
        }

        body {
            font-family: "Vazirmatn", sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        /* ====================
            Layout
           ==================== */
        main {
            max-width: 960px;
            margin: 2rem auto;
            padding: 1rem;
        }

        header h1 {
            text-align: center;
            color: var(--primary-dark);
            margin-bottom: 2rem;
            font-weight: 800;
        }

        .tool-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            margin-bottom: 2.5rem;
            box-shadow: 0 4px 15px var(--shadow-light);
            overflow: hidden;
        }

        .tool-card h2 {
            font-size: 1.2rem;
            font-weight: 700;
            padding: 1rem 1.5rem;
            background-color: var(--primary-light);
            color: var(--primary-dark);
            border-bottom: 1px solid var(--border-color);
        }

        .card-content {
            padding: 1.5rem;
        }

        /* ====================
            Form Styles
           ==================== */
        #add-prize-form {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: flex-end;
            margin-bottom: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: var(--secondary-text-color);
        }

        .form-group input,
        .form-group select {
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--shadow-light);
        }

        input[type="color"] {
            padding: 0.25rem;
            height: 48px;
            cursor: pointer;
        }

        /* ====================
            Table Styles
           ==================== */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            text-align: right;
            vertical-align: middle;
        }

        thead th {
            background-color: #f9fafb;
            font-weight: 600;
            color: var(--secondary-text-color);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background-color: var(--bg-color);
        }

        .color-preview {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-left: 10px;
            vertical-align: middle;
            border: 1px solid #eee;
        }

        /* ====================
            Button Styles
           ==================== */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-danger {
            background-color: var(--danger-bg);
            color: var(--danger-color);
            padding: 0.5rem 1rem;
        }

        .btn-danger:hover {
            background-color: var(--danger-color);
            color: white;
        }

        @media (max-width: 768px) {
            #add-prize-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <main>
        <header>
            <h1>پنل مدیریت گردونه شانس</h1>
        </header>

        <div class="tool-card">
            <h2>مدیریت جوایز</h2>
            <div class="card-content">
                <form id="add-prize-form">
                    <div class="form-group">
                        <label for="prize-name">نام جایزه</label>
                        <input type="text" id="prize-name" placeholder="مثال: ۱۰٪ تخفیف" required>
                    </div>
                    <div class="form-group">
                        <label for="prize-color">رنگ</label>
                        <input type="color" id="prize-color" value="#00AE70">
                    </div>
                    <div class="form-group">
                        <label for="prize-type">نوع</label>
                        <select id="prize-type">
                            <option value="positive" selected>مثبت</option>
                            <option value="negative">منفی</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="prize-weight">ضریب شانس (وزن)</label>
                        <input type="number" id="prize-weight" min="0" value="10" required>
                    </div>
                    <button type="submit" class="btn btn-primary">افزودن</button>
                </form>

                <table>
                    <thead>
                        <tr>
                            <th>جایزه</th>
                            <th>نوع</th>
                            <th>ضریب</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody id="prize-list-body">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tool-card">
            <h2>📊 سوابق برندگان (۵۰ رکورد آخر)</h2>
            <div class="card-content">
                <table>
                    <thead>
                        <tr>
                            <th>نام کاربر</th>
                            <th>جایزه برنده شده</th>
                            <th>تاریخ و ساعت</th>
                        </tr>
                    </thead>
                    <tbody id="winnerHistoryBody">
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            //============== عناصر مربوط به مدیریت جوایز ==============//
            const prizeForm = document.getElementById('add-prize-form');
            const prizeNameInput = document.getElementById('prize-name');
            const prizeColorInput = document.getElementById('prize-color');
            const prizeTypeInput = document.getElementById('prize-type');
            const prizeWeightInput = document.getElementById('prize-weight');
            const prizeListBody = document.getElementById('prize-list-body');

            //============== عنصر مربوط به سوابق برندگان ==============//
            const winnerHistoryBody = document.getElementById('winnerHistoryBody');

            const API_URL = 'prize-api.php';

            /**
             * تابع برای بارگذاری لیست جوایز از سرور
             */
            async function loadPrizeList() {
                try {
                    const response = await fetch(`${API_URL}?action=getPrizeListForAdmin`);
                    const prizes = await response.json();

                    prizeListBody.innerHTML = ''; // پاک کردن جدول قبل از بازسازی

                    if (prizes.length === 0) {
                        prizeListBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">هیچ جایزه‌ای ثبت نشده است.</td></tr>';
                        return;
                    }

                    prizes.forEach(prize => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                    <td>
                        <span class="color-preview" style="background-color:${prize.color};"></span>
                        ${escapeHTML(prize.name)}
                    </td>
                    <td>${prize.type === 'positive' ? 'مثبت' : 'منفی'}</td>
                    <td>${prize.weight}</td>
                    <td>
                        <button class="btn btn-danger" onclick="deletePrize(${prize.id})">حذف</button>
                    </td>
                `;
                        prizeListBody.appendChild(row);
                    });

                } catch (error) {
                    console.error('خطا در بارگذاری لیست جوایز:', error);
                    prizeListBody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:red;">خطا در دریافت اطلاعات.</td></tr>';
                }
            }

            /**
             * تابع برای افزودن یک جایزه جدید
             */
            async function addPrize(event) {
                event.preventDefault(); // جلوگیری از رفرش صفحه

                const prizeData = {
                    name: prizeNameInput.value.trim(),
                    color: prizeColorInput.value,
                    type: prizeTypeInput.value,
                    weight: parseInt(prizeWeightInput.value)
                };

                if (!prizeData.name || prizeData.weight < 0) {
                    alert('لطفاً نام جایزه و ضریب شانس معتبر وارد کنید.');
                    return;
                }

                try {
                    const response = await fetch(`${API_URL}?action=addPrize`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(prizeData)
                    });
                    const result = await response.json();

                    if (result.success) {
                        prizeForm.reset(); // پاک کردن فرم
                        prizeColorInput.value = '#00AE70'; // ریست کردن رنگ
                        await loadPrizeList(); // بارگذاری مجدد لیست
                    } else {
                        alert('خطایی در افزودن جایزه رخ داد.');
                    }
                } catch (error) {
                    console.error('خطا در ارسال اطلاعات:', error);
                }
            }

            /**
             * تابع برای حذف یک جایزه
             * این تابع باید در دسترس گلوبال باشد تا onclick بتواند آن را فراخوانی کند
             */
            window.deletePrize = async function(id) {
                if (!confirm('آیا از حذف این جایزه اطمینان دارید؟')) {
                    return;
                }

                try {
                    const response = await fetch(`${API_URL}?action=deletePrize`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: id
                        })
                    });
                    const result = await response.json();

                    if (result.success) {
                        await loadPrizeList(); // بارگذاری مجدد لیست
                    } else {
                        alert('خطایی در حذف جایزه رخ داد.');
                    }
                } catch (error) {
                    console.error('خطا در حذف جایزه:', error);
                }
            }

            /**
             * تابع برای بارگذاری سوابق برندگان
             */
            async function loadWinnerHistory() {
                try {
                    const response = await fetch(`${API_URL}?action=getWinnerHistory`);
                    const history = await response.json();

                    winnerHistoryBody.innerHTML = ''; // پاک کردن جدول

                    if (!history || history.length === 0) {
                        winnerHistoryBody.innerHTML = '<tr><td colspan="3" style="text-align:center;">هیچ سابقه‌ای برای نمایش وجود ندارد.</td></tr>';
                        return;
                    }

                    history.forEach(record => {
                        const row = document.createElement('tr');

                        const userCell = document.createElement('td');
                        userCell.textContent = record.user_name;

                        const prizeCell = document.createElement('td');
                        prizeCell.textContent = record.prize_name;

                        const dateCell = document.createElement('td');
                        const date = new Date(record.won_at);
                        dateCell.textContent = new Intl.DateTimeFormat('fa-IR', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        }).format(date);

                        row.appendChild(userCell);
                        row.appendChild(prizeCell);
                        row.appendChild(dateCell);

                        winnerHistoryBody.appendChild(row);
                    });

                } catch (error) {
                    console.error('خطا در بارگذاری سوابق:', error);
                    winnerHistoryBody.innerHTML = '<tr><td colspan="3" style="text-align:center; color: red;">خطا در دریافت اطلاعات از سرور.</td></tr>';
                }
            }

            /**
             * تابع کمکی برای جلوگیری از حملات XSS هنگام نمایش نام جایزه
             */
            function escapeHTML(str) {
                const p = document.createElement('p');
                p.appendChild(document.createTextNode(str));
                return p.innerHTML;
            }


            // ============ راه‌اندازی اولیه ============ //
            prizeForm.addEventListener('submit', addPrize);
            loadPrizeList();
            loadWinnerHistory();
        });
    </script>

</body>

</html>
