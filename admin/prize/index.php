<?php
// فایل: admin/prize/index.php (بازنویسی نهایی)
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت گردونه شانس</title>
    <style>
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --primary-light: #e6f7f2;
            --bg-color: #f7f9fa;
            --text-color: #1a1a1a;
            --secondary-text-color: #555;
            --card-bg: #ffffff;
            --header-text: #fff;
            --footer-h: 60px;

            --border-color: #e9e9e9;
            --shadow-light: rgba(0, 120, 80, 0.06);
            --danger-color: #dc2626;
            --danger-bg: #fef2f2;
            --success-color: #16a34a;
            --border-radius: 0.75rem;
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
            font-family: "Vazirmatn", sans-serif !important;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            direction: rtl;
        }

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

        main {
            padding: 2rem;
            max-width: 1100px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary-dark);
            /* [تغییر] هماهنگی رنگ عنوان */
        }

        .page-title p {
            font-size: 1rem;
            color: var(--secondary-text-color);
            margin-top: 0.25rem;
        }

        .content-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 15px var(--shadow-light);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1.3rem;
            font-weight: 700;
        }

        .card-body {
            padding: 0;
        }

        /* --- Buttons --- */
        .btn {
            padding: 0.6rem 1.2rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.2s;
            cursor: pointer;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn.loading {
            pointer-events: none;
            color: transparent;
        }

        .btn.loading::after {
            content: '';
            display: block;
            position: absolute;
            width: 1.2em;
            height: 1.2em;
            border: 2px solid white;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .btn-icon {
            background: none;
            border: none;
            padding: 0.4rem;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            line-height: 1;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .btn-icon:hover {
            background-color: #f1f1f1;
        }

        .modal-header .btn-icon {
            font-size: 1rem;
            color: var(--secondary-text-color);
        }

        /* --- Table --- */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th,
        td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            text-align: right;
            vertical-align: middle;
        }

        thead th {
            background-color: #f9fafb;
            font-weight: 700;
            color: var(--secondary-text-color);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background-color: var(--primary-light);
        }

        .color-preview {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-left: 10px;
            vertical-align: middle;
            border: 1px solid #ddd;
        }

        .actions-cell {
            display: flex;
            gap: 0.25rem;
        }

        /* --- Modal --- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }

        .modal-overlay.visible {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            transform: scale(0.9);
            transition: transform 0.3s;
        }

        .modal-overlay.visible .modal-content {
            transform: scale(1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        #modal-title {
            font-size: 1.4rem;
            font-weight: 700;
        }

        #modal-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 174, 112, 0.15);
            outline: none;
        }

        /* [جدید] استایل پالت رنگی */
        .color-palette {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-top: 0.75rem;
        }

        .color-swatch {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px var(--border-color);
            transition: transform 0.2s;
        }

        .color-swatch:hover {
            transform: scale(1.1);
        }

        .color-swatch.selected {
            box-shadow: 0 0 0 2px var(--primary-dark);
        }

        .modal-footer {
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
            /* [بهبود] چیدمان دکمه‌ها */
            flex-direction: row-reverse;
            /* [بهبود] دکمه اصلی در راست */
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background-color: #e2e8f0;
        }

        /* --- Toast Notifications --- */
        #toast-container {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 1001;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .toast {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-left: 5px solid;
            animation: slideIn 0.3s ease-out, fadeOut 0.3s ease-in 3.7s forwards;
        }

        .toast.success {
            border-color: var(--success-color);
        }

        .toast.error {
            border-color: var(--danger-color);
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
                transform: translateX(-20px);
            }
        }

        .back-link {
            display: block;
            margin-top: 2rem;
            text-align: center;
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>

    <main>
        <div class="page-header">
            <div class="page-title">
                <h1>مدیریت گردونه شانس</h1>
                <p>جوایز و سوابق برندگان را در این بخش مدیریت کنید.</p>
            </div>
            <button id="add-new-prize-btn" class="btn btn-primary">➕ افزودن جایزه جدید</button>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2>🏆 لیست جوایز</h2>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>جایزه</th>
                            <th>نوع</th>
                            <th>ضریب</th>
                            <th>تعداد برد</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody id="prize-list-body"></tbody>
                </table>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2>📊 سوابق برندگان (۵۰ رکورد آخر)</h2>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>نام کاربر</th>
                            <th>جایزه برنده شده</th>
                            <th>تاریخ</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody id="winner-history-body"></tbody>
                </table>
            </div>
        </div>
        <a href="/admin/index.php" class="back-link">بازگشت به پنل مدیریت</a>
    </main>

    <div id="prize-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">افزودن جایزه جدید</h2>
                <button class="btn-icon" id="close-modal-btn" title="بستن">✖️</button>
            </div>
            <form id="modal-form" onsubmit="return false;">
                <div class="form-group full-width">
                    <label for="prize-name">نام جایزه</label>
                    <input type="text" id="prize-name" placeholder="مثال: ۱۰٪ تخفیف" required>
                </div>
                <div class="form-group">
                    <label for="prize-type">نوع</label>
                    <select id="prize-type">
                        <option value="positive" selected>مثبت</option>
                        <option value="negative">منفی (پوچ)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="prize-weight">ضریب شانس (وزن)</label>
                    <input type="number" id="prize-weight" min="0" value="10" required>
                </div>
                <div class="form-group full-width">
                    <label for="prize-color">رنگ</label>
                    <input type="color" id="prize-color" value="#00AE70" style="padding: 0.25rem; height: 40px; width: 100%;">
                    <div class="color-palette" id="color-palette"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="submit-btn" class="btn btn-primary">افزودن</button>
                    <button type="button" id="cancel-btn" class="btn btn-secondary">لغو</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast-container"></div>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const prizeListBody = document.getElementById('prize-list-body');
            const winnerHistoryBody = document.getElementById('winner-history-body');
            const modal = document.getElementById('prize-modal');
            const modalForm = document.getElementById('modal-form');
            const modalTitle = document.getElementById('modal-title');
            const submitBtn = document.getElementById('submit-btn');
            const prizeNameInput = document.getElementById('prize-name');
            const prizeColorInput = document.getElementById('prize-color');
            const prizeTypeInput = document.getElementById('prize-type');
            const prizeWeightInput = document.getElementById('prize-weight');
            const colorPaletteContainer = document.getElementById('color-palette');

            const API_URL = 'prize-api.php';
            let prizesData = [];
            let currentEditId = null;

            // [جدید] لیست رنگ‌های پیشنهادی برای پالت
            const colorPalette = [
                '#00AE70', '#10B981', '#34D399', '#22C55E', '#84CC16', '#F59E0B',
                '#F97316', '#EF4444', '#EC4899', '#D946EF', '#8B5CF6', '#6366F1',
                '#3B82F6', '#0EA5E9', '#06B6D4', '#14B8A6', '#64748B', '#334155'
            ];

            const escapeHTML = (str) => {
                const p = document.createElement('p');
                p.textContent = str;
                return p.innerHTML;
            }

            const showToast = (message, type = 'success') => {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.innerHTML = `<span>${message}</span>`;
                container.appendChild(toast);
                setTimeout(() => toast.remove(), 4000);
            };

            // [جدید] تابع برای ساخت و مدیریت پالت رنگی
            const setupColorPalette = () => {
                colorPaletteContainer.innerHTML = '';
                colorPalette.forEach(color => {
                    const swatch = document.createElement('span');
                    swatch.className = 'color-swatch';
                    swatch.style.backgroundColor = color;
                    swatch.dataset.color = color;
                    colorPaletteContainer.appendChild(swatch);
                });

                colorPaletteContainer.addEventListener('click', (e) => {
                    if (e.target.classList.contains('color-swatch')) {
                        const newColor = e.target.dataset.color;
                        prizeColorInput.value = newColor;
                        updateSelectedSwatch(newColor);
                    }
                });
            };

            // [جدید] تابع برای هایلایت کردن رنگ انتخاب شده در پالت
            const updateSelectedSwatch = (selectedColor) => {
                document.querySelectorAll('.color-swatch').forEach(sw => {
                    sw.classList.toggle('selected', sw.dataset.color.toLowerCase() === selectedColor.toLowerCase());
                });
            }

            prizeColorInput.addEventListener('input', (e) => updateSelectedSwatch(e.target.value));

            const openModal = (mode = 'add', prize = {}) => {
                modalForm.reset();
                currentEditId = null;
                const defaultColor = '#00AE70';

                if (mode === 'edit') {
                    modalTitle.textContent = "📝 ویرایش جایزه";
                    submitBtn.innerHTML = "💾 ذخیره تغییرات";
                    currentEditId = prize.id;
                    prizeNameInput.value = prize.name;
                    prizeColorInput.value = prize.color;
                    prizeTypeInput.value = prize.type;
                    prizeWeightInput.value = prize.weight;
                } else {
                    modalTitle.textContent = "✨ افزودن جایزه جدید";
                    submitBtn.innerHTML = "➕ افزودن";
                    prizeColorInput.value = defaultColor;
                }
                updateSelectedSwatch(prizeColorInput.value); // هایلایت رنگ فعلی
                modal.classList.add('visible');
            };

            const closeModal = () => modal.classList.remove('visible');

            const renderPrizeList = () => {
                prizeListBody.innerHTML = '';
                if (!prizesData || prizesData.length === 0) {
                    prizeListBody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 2rem;">هیچ جایزه‌ای ثبت نشده است.</td></tr>';
                    return;
                }
                prizesData.forEach(prize => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><span class="color-preview" style="background-color:${prize.color};"></span>${escapeHTML(prize.name)}</td>
                        <td>${prize.type === 'positive' ? 'مثبت' : 'منفی'}</td>
                        <td>${prize.weight}</td>
                        <td>${prize.win_count || 0}</td>
                        <td class="actions-cell">
                            <button class="btn-icon edit-btn" data-id="${prize.id}" title="ویرایش">✏️</button>
                            <button class="btn-icon delete-prize-btn" data-id="${prize.id}" title="حذف">🗑️</button>
                        </td>
                    `;
                    prizeListBody.appendChild(row);
                });
            };

            const renderWinnerHistory = (history) => {
                winnerHistoryBody.innerHTML = '';
                if (!history || history.length === 0) {
                    winnerHistoryBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem;">هیچ سابقه‌ای برای نمایش وجود ندارد.</td></tr>';
                    return;
                }
                history.forEach(record => {
                    const row = document.createElement('tr');
                    const date = new Date(record.won_at);
                    const formattedDate = new Intl.DateTimeFormat('fa-IR', {
                        dateStyle: 'medium',
                        timeStyle: 'short'
                    }).format(date);
                    row.innerHTML = `
                        <td>${escapeHTML(record.user_name)}</td>
                        <td>${escapeHTML(record.prize_name)}</td>
                        <td>${formattedDate}</td>
                        <td class="actions-cell">
                            <button class="btn-icon delete-history-btn" data-id="${record.id}" title="حذف سابقه">🗑️</button>
                        </td>
                    `;
                    winnerHistoryBody.appendChild(row);
                });
            };

            const apiRequest = async (action, method = 'GET', body = null) => {
                const options = {
                    method,
                    headers: method !== 'GET' ? {
                        'Content-Type': 'application/json'
                    } : {}
                };
                if (body) options.body = JSON.stringify(body);
                try {
                    const response = await fetch(`${API_URL}?action=${action}`, options);
                    if (!response.ok) throw new Error(`Server responded with ${response.status}`);
                    return await response.json();
                } catch (error) {
                    console.error(`Error during action '${action}':`, error);
                    showToast('خطا در ارتباط با سرور', 'error');
                    return null;
                }
            };

            const loadData = async () => {
                const [prizeResult, historyResult] = await Promise.all([
                    apiRequest('getPrizeListForAdmin'),
                    apiRequest('getWinnerHistory')
                ]);
                if (prizeResult) {
                    prizesData = prizeResult;
                    renderPrizeList();
                }
                if (historyResult) {
                    renderWinnerHistory(historyResult);
                }
            };

            const handleFormSubmit = async (event) => {
                event.preventDefault();
                const prizeData = {
                    id: currentEditId,
                    name: prizeNameInput.value.trim(),
                    color: prizeColorInput.value,
                    type: prizeTypeInput.value,
                    weight: parseInt(prizeWeightInput.value, 10)
                };
                if (!prizeData.name || isNaN(prizeData.weight) || prizeData.weight < 0) {
                    showToast('لطفاً نام و ضریب شانس معتبر وارد کنید.', 'error');
                    return;
                }

                submitBtn.classList.add('loading');
                const action = currentEditId ? 'updatePrize' : 'addPrize';
                const result = await apiRequest(action, 'POST', prizeData);
                submitBtn.classList.remove('loading');

                if (result && result.success) {
                    showToast(`جایزه با موفقیت ${currentEditId ? 'ویرایش شد' : 'افزوده شد'}.`);
                    closeModal();
                    await loadData();
                } else {
                    showToast(result?.message || 'خطا در ثبت اطلاعات.', 'error');
                }
            };

            const deletePrize = async (id) => {
                if (!confirm('آیا از حذف این جایزه اطمینان دارید؟')) return;
                const result = await apiRequest('deletePrize', 'POST', {
                    id
                });
                if (result && result.success) {
                    showToast('جایزه با موفقیت حذف شد.');
                    await loadData();
                } else {
                    showToast(result?.message || 'خطا در حذف جایزه.', 'error');
                }
            };

            const deleteWinnerRecord = async (id) => {
                if (!confirm('آیا از حذف این سابقه اطمینان دارید؟')) return;
                const result = await apiRequest('deleteWinnerRecord', 'POST', {
                    id
                });
                if (result && result.success) {
                    showToast('سابقه با موفقیت حذف شد.');
                    await loadData();
                } else {
                    showToast(result?.message || 'خطا در حذف سابقه.', 'error');
                }
            };

            // --- Event Listeners ---
            document.getElementById('add-new-prize-btn').addEventListener('click', () => openModal('add'));
            document.getElementById('close-modal-btn').addEventListener('click', closeModal);
            document.getElementById('cancel-btn').addEventListener('click', closeModal);
            modalForm.addEventListener('submit', handleFormSubmit);

            prizeListBody.addEventListener('click', (event) => {
                const target = event.target.closest('.btn-icon');
                if (!target) return;
                const id = parseInt(target.dataset.id, 10);
                if (target.classList.contains('edit-btn')) {
                    const prizeToEdit = prizesData.find(p => p.id === id);
                    if (prizeToEdit) openModal('edit', prizeToEdit);
                } else if (target.classList.contains('delete-prize-btn')) {
                    deletePrize(id);
                }
            });

            winnerHistoryBody.addEventListener('click', (event) => {
                const deleteBtn = event.target.closest('.delete-history-btn');
                if (deleteBtn) deleteWinnerRecord(parseInt(deleteBtn.dataset.id, 10));
            });

            // --- Initial Load ---
            setupColorPalette();
            loadData();
        });
    </script>
</body>

</html>
