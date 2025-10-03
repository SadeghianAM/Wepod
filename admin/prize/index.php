<?php
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
            --card-bg: #fff;
            --text-color: #1a1a1a;
            --secondary-text: #555;
            --header-text: #fff;
            --border-color: #e9e9e9;
            --radius: 12px;
            --shadow-sm: 0 2px 6px rgba(0, 120, 80, .06);
            --shadow-md: 0 6px 20px rgba(0, 120, 80, .10);
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
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
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            padding: 2.5rem 2rem;
            max-width: 1500px;
            width: 100%;
            margin: 0 auto;
            flex-grow: 1;
        }

        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            min-height: 60px;
            font-size: .85rem;
        }

        .page-header {
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-header .actions-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .page-title h1 {
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 800;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .page-title p {
            font-size: clamp(.95rem, 2.2vw, 1rem);
            color: var(--secondary-text);
            margin-top: 0.5rem;
        }

        .content-card {
            background-color: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
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
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-body.no-padding {
            padding: 0;
        }

        .icon {
            width: 1.1em;
            height: 1.1em;
            stroke-width: 2.2;
            vertical-align: -0.15em;
        }

        .btn {
            position: relative;
            padding: .8em 1.5em;
            font-size: .95rem;
            font-weight: 600;
            border: 1.5px solid transparent;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6em;
            white-space: nowrap;
        }

        .btn:hover:not(.loading) {
            transform: translateY(-2px);
            filter: brightness(0.92);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background-color: var(--secondary-text);
            color: white;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn.loading {
            pointer-events: none;
            color: transparent !important;
        }

        .btn.loading::after {
            content: '';
            display: block;
            position: absolute;
            width: 1.2em;
            height: 1.2em;
            border: 2px solid currentColor;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
        }

        .btn-primary.loading::after {
            border-color: rgba(255, 255, 255, 0.7);
            border-top-color: transparent;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .btn-icon {
            background: transparent;
            border: none;
            padding: .5rem;
            border-radius: 50%;
            cursor: pointer;
            color: var(--secondary-text);
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s, color 0.2s;
        }

        .btn-icon:hover {
            background-color: var(--border-color);
            color: var(--text-color);
        }

        .btn-icon .icon {
            width: 1.25rem;
            height: 1.25rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th,
        td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            text-align: right;
            vertical-align: middle;
        }

        thead th {
            background-color: var(--bg-color);
            font-weight: 600;
            color: var(--secondary-text);
            font-size: .9rem;
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
            border: 1px solid var(--border-color);
        }

        .actions-cell {
            display: flex;
            gap: 0.25rem;
        }

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
            border-radius: var(--radius);
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
            display: flex;
            align-items: center;
            gap: .6rem;
            color: var(--primary-dark);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 1.5rem;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--secondary-text);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 174, 112, 0.15);
            outline: none;
        }

        #modal-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

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
            margin-top: 1rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            grid-column: 1 / -1;
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

        #toast-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }

        .toast {
            padding: 12px 20px;
            border-radius: var(--radius);
            color: white;
            font-weight: 500;
            box-shadow: var(--shadow-md);
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.3s, transform 0.3s;
            min-width: 280px;
            text-align: center;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast-success {
            background-color: var(--success-color);
        }

        .toast-error {
            background-color: var(--danger-color);
        }

        .toast-confirm {
            background-color: var(--card-bg);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .toast-confirm .toast-message {
            margin-bottom: 1rem;
        }

        .toast-confirm .toast-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .toast-confirm .btn {
            font-size: 0.85rem;
            padding: 0.5em 1em;
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
            <div class="actions-wrapper">
                <button id="add-chance-to-all-btn" class="btn btn-secondary">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 7h-9" />
                        <path d="M14 17H5" />
                        <circle cx="17" cy="17" r="3" />
                        <circle cx="8" cy="7" r="3" />
                    </svg>
                    <span>افزودن شانس به همه</span>
                </button>
                <button id="add-new-prize-btn" class="btn btn-primary">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="M12 5v14" />
                    </svg>
                    <span>افزودن جایزه جدید</span>
                </button>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2>
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 8V4H8" />
                        <rect width="16" height="12" x="4" y="8" rx="2" />
                        <path d="M2 14h2" />
                        <path d="M20 14h2" />
                        <path d="M15 13v2" />
                        <path d="M9 13v2" />
                    </svg>
                    <span>لیست جوایز</span>
                </h2>
                <span id="total-weight-display" style="font-weight: 600; color: var(--secondary-text);"></span>
            </div>
            <div class="card-body no-padding">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>جایزه</th>
                                <th>نوع</th>
                                <th>ضریب</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody id="prize-list-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2>
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>
                    <span>سوابق برندگان (۵۰ رکورد آخر)</span>
                </h2>
            </div>
            <div class="card-body no-padding">
                <div class="table-responsive">
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
        </div>
        <a href="/admin/index.php" class="back-link">بازگشت به پنل مدیریت</a>
    </main>

    <div id="prize-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title"></h2>
                <button class="btn-icon" id="close-modal-btn" title="بستن">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
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
                    <small id="weight-info" style="margin-top: 8px; color: var(--secondary-text); min-height: 1.2em;"></small>
                </div>
                <div class="form-group full-width">
                    <label for="prize-color">رنگ</label>
                    <input type="color" id="prize-color" value="#00AE70" style="padding: 0.25rem; height: 40px; width: 100%;">
                    <div class="color-palette" id="color-palette"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="cancel-btn" class="btn btn-secondary">لغو</button>
                    <button type="submit" id="submit-btn" class="btn btn-primary"></button>
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
            const totalWeightDisplay = document.getElementById('total-weight-display');
            const weightInfo = document.getElementById('weight-info');

            const API_URL = 'prize-api.php';
            let prizesData = [];
            let currentEditId = null;

            const colorPalette = ['#00AE70', '#10B981', '#34D399', '#22C55E', '#84CC16', '#F59E0B', '#F97316', '#EF4444', '#EC4899', '#D946EF', '#8B5CF6', '#6366F1', '#3B82F6', '#0EA5E9', '#06B6D4', '#14B8A6', '#64748B', '#334155'];
            const ICONS = {
                add: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>`,
                save: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>`,
                edit: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>`,
                delete: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>`
            };

            const escapeHTML = (str) => {
                const p = document.createElement('p');
                p.textContent = str;
                return p.innerHTML;
            }

            function showToast(message, type = 'success', duration = 4000) {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.innerHTML = `<span>${message}</span>`;
                container.appendChild(toast);
                setTimeout(() => toast.classList.add('show'), 10);
                setTimeout(() => {
                    toast.classList.remove('show');
                    toast.addEventListener('transitionend', () => toast.remove());
                }, duration);
            }

            function showConfirmation(message, title, onConfirm) {
                const toastContainer = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = 'toast toast-confirm show';
                toast.innerHTML = `
                    <div class="toast-message">${message}</div>
                    <div class="toast-buttons">
                        <button class="btn btn-danger" id="confirmAction">${title}</button>
                        <button class="btn btn-secondary" id="cancelAction">لغو</button>
                    </div>`;
                const removeToast = () => {
                    toast.classList.remove('show');
                    toast.addEventListener('transitionend', () => toast.remove());
                };
                toast.querySelector('#confirmAction').onclick = () => {
                    onConfirm();
                    removeToast();
                };
                toast.querySelector('#cancelAction').onclick = removeToast;
                toastContainer.appendChild(toast);
            }

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
                    const data = await response.json();
                    if (!response.ok || data.success === false) {
                        throw new Error(data.message || `Server error: ${response.status}`);
                    }
                    return data;
                } catch (error) {
                    console.error(`API Error on ${action}:`, error);
                    showToast(error.message || 'خطا در ارتباط با سرور.', 'error');
                    return null;
                }
            };

            const setupColorPalette = () => {
                colorPaletteContainer.innerHTML = '';
                colorPalette.forEach(color => {
                    const swatch = document.createElement('span');
                    swatch.className = 'color-swatch';
                    swatch.style.backgroundColor = color;
                    swatch.dataset.color = color;
                    colorPaletteContainer.appendChild(swatch);
                });
            };

            const updateSelectedSwatch = (selectedColor) => {
                document.querySelectorAll('.color-swatch').forEach(sw => {
                    sw.classList.toggle('selected', sw.dataset.color.toLowerCase() === selectedColor.toLowerCase());
                });
            };

            const updateTotalWeightDisplay = () => {
                const total = prizesData.reduce((sum, p) => sum + p.weight, 0);
                totalWeightDisplay.innerHTML = `مجموع ضرایب: <strong style="color: ${total > 100 ? 'var(--danger-color)' : 'var(--text-color)'};">${total} / 100</strong>`;
            };

            const updateDynamicWeightInfo = () => {
                const currentWeightValue = parseInt(prizeWeightInput.value, 10) || 0;
                let otherPrizesWeight = prizesData.reduce((sum, p) => sum + p.weight, 0);
                if (currentEditId) {
                    const currentPrize = prizesData.find(p => p.id === currentEditId);
                    if (currentPrize) {
                        otherPrizesWeight -= currentPrize.weight;
                    }
                }
                const newTotal = otherPrizesWeight + currentWeightValue;
                weightInfo.innerHTML = `مجموع نهایی: ${otherPrizesWeight} + ${currentWeightValue} = <strong style="color: ${newTotal > 100 ? 'var(--danger-color)' : 'var(--text-color)'};">${newTotal} / 100</strong>`;
            };

            const openModal = (mode = 'add', prize = {}) => {
                modalForm.reset();
                currentEditId = null;
                weightInfo.innerHTML = '';
                const defaultColor = '#00AE70';
                if (mode === 'edit') {
                    modalTitle.innerHTML = `${ICONS.edit} <span>ویرایش جایزه</span>`;
                    submitBtn.innerHTML = `${ICONS.save} <span>ذخیره تغییرات</span>`;
                    currentEditId = prize.id;
                    prizeNameInput.value = prize.name;
                    prizeColorInput.value = prize.color;
                    prizeTypeInput.value = prize.type;
                    prizeWeightInput.value = prize.weight;
                } else {
                    modalTitle.innerHTML = `${ICONS.add} <span>افزودن جایزه جدید</span>`;
                    submitBtn.innerHTML = `${ICONS.add} <span>افزودن</span>`;
                    prizeColorInput.value = defaultColor;
                    prizeWeightInput.value = 10;
                }
                updateSelectedSwatch(prizeColorInput.value);
                updateDynamicWeightInfo();
                modal.classList.add('visible');
            };

            const closeModal = () => modal.classList.remove('visible');

            const renderPrizeList = () => {
                prizeListBody.innerHTML = '';
                if (!prizesData || prizesData.length === 0) {
                    prizeListBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem;">هیچ جایزه‌ای ثبت نشده است.</td></tr>';
                } else {
                    prizesData.forEach(prize => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><span class="color-preview" style="background-color:${prize.color};"></span>${escapeHTML(prize.name)}</td>
                            <td>${prize.type === 'positive' ? 'مثبت' : 'منفی'}</td>
                            <td>${prize.weight}</td>
                            <td class="actions-cell">
                                <button class="btn-icon edit-btn" data-id="${prize.id}" title="ویرایش">${ICONS.edit}</button>
                                <button class="btn-icon delete-prize-btn" data-id="${prize.id}" title="حذف">${ICONS.delete}</button>
                            </td>`;
                        prizeListBody.appendChild(row);
                    });
                }
                updateTotalWeightDisplay();
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
                        timeStyle: 'short',
                        timeZone: 'Asia/Tehran'
                    }).format(date);
                    row.innerHTML = `
                        <td>${escapeHTML(record.user_name)}</td>
                        <td>${escapeHTML(record.prize_name)}</td>
                        <td>${formattedDate}</td>
                        <td class="actions-cell">
                            <button class="btn-icon delete-history-btn" data-id="${record.id}" title="حذف سابقه">${ICONS.delete}</button>
                        </td>`;
                    winnerHistoryBody.appendChild(row);
                });
            };

            const loadPageData = async () => {
                const [prizesResult, historyResult] = await Promise.all([
                    apiRequest('getPrizeListForAdmin'),
                    apiRequest('getWinnerHistory')
                ]);
                if (prizesResult) {
                    prizesData = prizesResult;
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
                    await loadPageData();
                }
            };

            document.getElementById('add-new-prize-btn').addEventListener('click', () => openModal('add'));
            document.getElementById('close-modal-btn').addEventListener('click', closeModal);
            document.getElementById('cancel-btn').addEventListener('click', closeModal);
            modalForm.addEventListener('submit', handleFormSubmit);
            prizeWeightInput.addEventListener('input', updateDynamicWeightInfo);
            colorPaletteContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('color-swatch')) {
                    const newColor = e.target.dataset.color;
                    prizeColorInput.value = newColor;
                    updateSelectedSwatch(newColor);
                }
            });
            prizeColorInput.addEventListener('input', (e) => updateSelectedSwatch(e.target.value));

            prizeListBody.addEventListener('click', (event) => {
                const target = event.target.closest('.btn-icon');
                if (!target) return;
                const id = parseInt(target.dataset.id, 10);
                if (target.classList.contains('edit-btn')) {
                    const prizeToEdit = prizesData.find(p => p.id === id);
                    if (prizeToEdit) openModal('edit', prizeToEdit);
                } else if (target.classList.contains('delete-prize-btn')) {
                    showConfirmation('آیا از حذف این جایزه اطمینان دارید؟', 'تایید و حذف', async () => {
                        const result = await apiRequest('deletePrize', 'POST', {
                            id
                        });
                        if (result && result.success) {
                            showToast('جایزه با موفقیت حذف شد.');
                            await loadPageData();
                        }
                    });
                }
            });

            winnerHistoryBody.addEventListener('click', (event) => {
                const deleteBtn = event.target.closest('.delete-history-btn');
                if (deleteBtn) {
                    showConfirmation('آیا از حذف این سابقه اطمینان دارید؟', 'تایید و حذف', async () => {
                        const id = parseInt(deleteBtn.dataset.id, 10);
                        const result = await apiRequest('deleteWinnerRecord', 'POST', {
                            id
                        });
                        if (result && result.success) {
                            showToast('سابقه با موفقیت حذف شد.');
                            await loadPageData();
                        }
                    });
                }
            });

            document.getElementById('add-chance-to-all-btn').addEventListener('click', () => {
                showConfirmation('آیا مطمئن هستید که می‌خواهید به تمام کاربران یک شانس گردونه اضافه کنید؟ این عمل قابل بازگشت نیست.', 'تایید و افزودن', async () => {
                    const btn = document.getElementById('add-chance-to-all-btn');
                    btn.classList.add('loading');
                    const result = await apiRequest('addSpinChanceToAllUsers', 'POST');
                    btn.classList.remove('loading');
                    if (result && result.success) {
                        showToast(result.message, 'success');
                    }
                });
            });

            setupColorPalette();
            loadPageData();
        });
    </script>
</body>

</html>
