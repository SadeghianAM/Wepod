<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>پنل مدیریت وی هاب</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700;900&display=swap" rel="stylesheet">
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
            --danger-color: #d93025;
            --danger-bg: #fce8e6;
            --border-radius: 0.75rem;
            --border-color: #e9e9e9;
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
            max-width: 900px;
            width: 100%;
            margin: 0 auto;
        }

        footer {
            background: var(--primary-color);
            color: var(--header-text);
            text-align: center;
            padding: 1.25rem;
            font-size: 0.9rem;
        }

        .tool-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px var(--shadow-color-light);
            overflow: hidden;
        }

        .tool-card>h2 {
            font-size: 1.2rem;
            font-weight: 700;
            padding: 1rem 1.5rem;
            background-color: var(--bg-color);
            color: var(--primary-dark);
            border-bottom: 1px solid var(--border-color);
        }

        .card-content {
            padding: 1.5rem;
        }

        .card-content h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .admin-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: flex-end;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            color: var(--secondary-text-color);
        }

        .form-input,
        .form-select {
            width: 100%;
            font-size: 1rem;
            padding: 0.6em 0.8em;
            border-radius: 0.6em;
            border: 1.5px solid #ddd;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            background-color: #fff;
        }

        input[type="color"] {
            height: 45px;
            padding: 0.2rem;
        }

        .form-input:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--shadow-color-light);
        }

        .btn {
            padding: 0.65rem 1rem;
            font-size: 1rem;
            font-weight: 700;
            border: none;
            border-radius: 0.6rem;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            white-space: nowrap;
        }

        .btn-primary {
            color: var(--header-text);
            background-color: var(--primary-color);
            box-shadow: 0 4px 10px var(--shadow-color-medium);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-danger {
            color: var(--header-text);
            background-color: var(--danger-color);
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
        }

        .admin-table th,
        .admin-table td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .admin-table thead th {
            background-color: var(--bg-color);
            font-weight: 700;
            color: var(--secondary-text-color);
            font-size: 0.9rem;
        }

        .admin-table tbody tr:last-child td {
            border-bottom: none;
        }

        .color-preview {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            vertical-align: middle;
            margin-left: 0.5rem;
        }

        @media (max-width: 768px) {
            .admin-form {
                grid-template-columns: 1fr 1fr;
            }

            .admin-form .form-group:first-child {
                grid-column: 1 / -1;
                /* Name takes full width */
            }
        }

        @media (max-width: 480px) {
            main {
                padding: 1rem;
            }

            .admin-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <header>وی هاب - پنل مدیریت</header>
    <main>
        <div class="tool-card">
            <h2>مدیریت جوایز گردونه</h2>
            <div class="card-content">
                <h3>افزودن جایزه جدید</h3>
                <form id="add-prize-form" class="admin-form">
                    <div class="form-group">
                        <label for="name">نام جایزه</label>
                        <input class="form-input" type="text" id="name" required>
                    </div>
                    <div class="form-group">
                        <label for="color">رنگ</label>
                        <input class="form-input" type="color" id="color" value="#00ae70">
                    </div>
                    <div class="form-group">
                        <label for="type">نوع</label>
                        <select class="form-select" id="type" required>
                            <option value="positive">مثبت</option>
                            <option value="negative">منفی</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="weight">ضریب شانس</label>
                        <input class="form-input" type="number" id="weight" required min="0">
                    </div>
                    <button type="submit" class="btn btn-primary">افزودن</button>
                </form>

                <h3>لیست جوایز</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>نام جایزه</th>
                            <th>نوع</th>
                            <th>ضریب شانس</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody id="prizes-table-body">
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <footer>
        تمامی حقوق برای وی هاب محفوظ است. © 2025
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('add-prize-form');
            const tableBody = document.getElementById('prizes-table-body');

            // Function to fetch and display prizes
            async function loadPrizes() {
                try {
                    const response = await fetch('prize-api.php?action=getPrizeListForAdmin');
                    const prizes = await response.json();

                    tableBody.innerHTML = ''; // Clear existing rows
                    if (prizes.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding: 2rem; color: #888;">هنوز جایزه‌ای ثبت نشده است.</td></tr>`;
                        return;
                    }

                    prizes.forEach(prize => {
                        const row = `
                            <tr data-id="${prize.id}">
                                <td>
                                    <span class="color-preview" style="background-color:${prize.color};"></span>
                                    ${prize.name}
                                </td>
                                <td>${prize.type === 'positive' ? 'مثبت' : 'منفی'}</td>
                                <td>${prize.weight}</td>
                                <td>
                                    <button class="btn btn-danger" onclick="deletePrize(${prize.id})">حذف</button>
                                </td>
                            </tr>
                        `;
                        tableBody.insertAdjacentHTML('beforeend', row);
                    });
                } catch (error) {
                    tableBody.innerHTML = `<tr><td colspan="4" style="text-align:center; color: var(--danger-color);">خطا در بارگذاری اطلاعات.</td></tr>`;
                }
            }

            // Handle form submission to add a new prize
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const prize = {
                    name: document.getElementById('name').value.trim(),
                    color: document.getElementById('color').value,
                    type: document.getElementById('type').value,
                    weight: parseInt(document.getElementById('weight').value)
                };

                if (!prize.name || isNaN(prize.weight)) {
                    alert('لطفاً تمام فیلدها را به درستی پر کنید.');
                    return;
                }

                await fetch('prize-api.php?action=addPrize', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(prize)
                });

                form.reset();
                document.getElementById('color').value = '#00ae70'; // Reset color picker
                loadPrizes();
            });

            // Make deletePrize function available globally for the onclick attribute
            window.deletePrize = async function(id) {
                if (!confirm('آیا از حذف این جایزه مطمئن هستید؟')) return;

                await fetch('prize-api.php?action=deletePrize', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id
                    })
                });

                loadPrizes();
            };

            // Initial load of prizes
            loadPrizes();
        });
    </script>
</body>

</html>
