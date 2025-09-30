<?php
// فایل: admin/polls/index.php
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت نظرسنجی</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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

        main {
            padding: 2rem;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            flex-grow: 1;
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
            padding: 1.5rem;
        }

        .card-body.no-padding {
            padding: 0;
        }

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

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
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

        .actions-cell {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
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
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 600px;
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

        .modal-header h2 {
            font-size: 1.4rem;
            font-weight: 700;
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

        .modal-footer {
            margin-top: 2rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background-color: #e2e8f0;
        }

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

        .status-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 99px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-badge.active {
            background-color: var(--success-color);
            color: white;
        }

        .status-badge.inactive {
            background-color: #f1f5f9;
            color: #64748b;
        }

        #options-list {
            list-style: none;
            padding: 0;
            margin-top: 1.5rem;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            border-radius: .5rem;
        }

        #options-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        #options-list li:last-child {
            border-bottom: none;
        }

        #add-option-form {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: .75rem;
            align-items: flex-end;
            border-top: 1px solid var(--border-color);
            padding-top: 1.5rem;
            margin-top: 1.5rem;
        }

        .modal-content.modal-lg {
            max-width: 800px;
        }

        #results-table-container {
            max-height: 400px;
            overflow-y: auto;
        }

        /* Skeleton Loader Styles */
        .skeleton-loader td {
            padding: 1rem 1.5rem;
        }

        .skeleton-line {
            width: 100%;
            height: 1.2rem;
            background-color: #f0f0f0;
            border-radius: 0.25rem;
            margin-bottom: 0.75rem;
            animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .skeleton-line:last-child {
            width: 70%;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Responsive Table Styles */
        @media screen and (max-width: 768px) {
            .table-responsive {
                overflow-x: hidden;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            tr {
                border: 1px solid var(--border-color);
                border-radius: .5rem;
                margin-bottom: 1rem;
                background: white;
            }

            td {
                border: none;
                border-bottom: 1px solid #eee;
                position: relative;
                padding-left: 50%;
                text-align: left;
                white-space: normal;
            }

            td:before {
                position: absolute;
                top: 50%;
                right: 1.5rem;
                transform: translateY(-50%);
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                text-align: right;
                font-weight: 700;
                color: var(--secondary-text-color);
            }

            td:nth-of-type(1):before {
                content: "سوال نظرسنجی:";
            }

            td:nth-of-type(2):before {
                content: "تعداد گزینه‌ها:";
            }

            td:nth-of-type(3):before {
                content: "تعداد آرا:";
            }

            td:nth-of-type(4):before {
                content: "وضعیت:";
            }

            td:nth-of-type(5):before {
                content: "عملیات:";
            }

            td.actions-cell {
                padding-left: 1rem;
                justify-content: flex-start;
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
    </style>
</head>

<body>
    <div id="header-placeholder"></div>

    <main>
        <div class="page-header">
            <div class="page-title">
                <h1>🗳️ مدیریت نظرسنجی</h1>
                <p>نظرسنجی‌ها، گزینه‌ها و وضعیت آن‌ها را مدیریت کنید.</p>
            </div>
            <button id="add-new-poll-btn" class="btn btn-primary">➕ افزودن نظرسنجی جدید</button>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2>لیست نظرسنجی‌ها</h2>
            </div>
            <div class="card-body no-padding">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>سوال نظرسنجی</th>
                                <th>تعداد گزینه‌ها</th>
                                <th>تعداد آرا</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody id="polls-list-body">
                            <tr class="skeleton-loader">
                                <td colspan="5">
                                    <div class="skeleton-line"></div>
                                </td>
                            </tr>
                            <tr class="skeleton-loader">
                                <td colspan="5">
                                    <div class="skeleton-line"></div>
                                </td>
                            </tr>
                            <tr class="skeleton-loader">
                                <td colspan="5">
                                    <div class="skeleton-line"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <a href="/admin/index.php" class="back-link">بازگشت به پنل مدیریت</a>
    </main>

    <div id="poll-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="poll-modal-title">افزودن نظرسنجی</h2>
                <button class="btn-icon close-modal-btn" title="بستن">✖️</button>
            </div>
            <form id="poll-modal-form" onsubmit="return false;">
                <div class="form-group">
                    <label for="poll-question">متن سوال نظرسنجی</label>
                    <input type="text" id="poll-question" placeholder="مثال: بهترین ساعت کاری کدام است؟" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-modal-btn">لغو</button>
                    <button type="submit" id="poll-submit-btn" class="btn btn-primary">افزودن</button>
                </div>
            </form>
        </div>
    </div>

    <div id="options-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="options-modal-title">مدیریت گزینه‌ها</h2>
                <button class="btn-icon close-modal-btn" title="بستن">✖️</button>
            </div>
            <p id="options-modal-question" style="font-weight: 500; color: var(--secondary-text-color); margin-bottom: 1rem;"></p>
            <ul id="options-list"></ul>
            <form id="add-option-form" onsubmit="return false;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="option-text">متن گزینه جدید</label>
                    <input type="text" id="option-text" placeholder="مثال: ۹ تا ۱۷" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="option-capacity">ظرفیت</label>
                    <input type="number" id="option-capacity" min="1" value="10" required>
                </div>
                <button type="submit" id="add-option-btn" class="btn btn-primary">افزودن</button>
            </form>
        </div>
    </div>

    <div id="results-modal" class="modal-overlay">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2 id="results-modal-title">نتایج نظرسنجی</h2>
                <button class="btn-icon close-modal-btn" title="بستن">✖️</button>
            </div>
            <p id="results-modal-question" style="font-weight: 500; color: var(--secondary-text-color); margin-bottom: 1.5rem;"></p>

            <div style="height: 300px; margin-bottom: 2rem; position: relative;">
                <canvas id="results-chart"></canvas>
            </div>

            <div id="results-table-container"></div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal-btn">بستن</button>
            </div>
        </div>
    </div>

    <div id="confirm-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <h2 id="confirm-modal-title">تایید عملیات</h2>
                <button class="btn-icon close-modal-btn" title="بستن">✖️</button>
            </div>
            <p id="confirm-modal-message">آیا از انجام این عملیات اطمینان دارید؟</p>
            <div class="modal-footer">
                <button type="button" id="confirm-cancel-btn" class="btn btn-secondary">لغو</button>
                <button type="button" id="confirm-ok-btn" class="btn btn-danger">تایید و انجام</button>
            </div>
        </div>
    </div>


    <div id="toast-container"></div>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const API_URL = 'polls-api.php';
            let pollsData = [];
            let currentPollId = null;
            let resultsChart = null;

            // --- DOM Elements ---
            const pollsListBody = document.getElementById('polls-list-body');
            // Modals
            const pollModal = document.getElementById('poll-modal');
            const optionsModal = document.getElementById('options-modal');
            const resultsModal = document.getElementById('results-modal');
            const confirmModal = document.getElementById('confirm-modal');
            // Poll Modal
            const pollModalForm = document.getElementById('poll-modal-form');
            const pollModalTitle = document.getElementById('poll-modal-title');
            const pollSubmitBtn = document.getElementById('poll-submit-btn');
            const pollQuestionInput = document.getElementById('poll-question');
            // Options Modal
            const optionsModalQuestion = document.getElementById('options-modal-question');
            const optionsList = document.getElementById('options-list');
            const addOptionForm = document.getElementById('add-option-form');
            const optionTextInput = document.getElementById('option-text');
            const optionCapacityInput = document.getElementById('option-capacity');
            // Results Modal
            const resultsModalQuestion = document.getElementById('results-modal-question');
            const resultsTableContainer = document.getElementById('results-table-container');
            const resultsChartCanvas = document.getElementById('results-chart');
            // Confirm Modal
            const confirmModalTitle = document.getElementById('confirm-modal-title');
            const confirmModalMessage = document.getElementById('confirm-modal-message');
            const confirmOkBtn = document.getElementById('confirm-ok-btn');
            const confirmCancelBtn = document.getElementById('confirm-cancel-btn');


            // --- Helper Functions ---
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
            const apiRequest = async (action, method = 'GET', body = null) => {
                const upperMethod = method.toUpperCase();
                const options = {
                    method: upperMethod,
                    headers: {}
                };
                let url = `${API_URL}?action=${action}`;

                if (upperMethod === 'GET' && body) {
                    url += '&' + new URLSearchParams(body).toString();
                } else if (body && (upperMethod === 'POST' || upperMethod === 'PUT')) {
                    options.headers['Content-Type'] = 'application/json';
                    options.body = JSON.stringify(body);
                }

                try {
                    const response = await fetch(url, options);
                    const result = await response.json();
                    if (!response.ok || (result && result.success === false)) {
                        throw new Error(result.message || `Server responded with ${response.status}`);
                    }
                    return result;
                } catch (error) {
                    console.error(`Error during action '${action}':`, error);
                    showToast(error.message || 'خطا در ارتباط با سرور', 'error');
                    return null;
                }
            };

            // --- Modal Handling ---
            const openModal = (modal) => modal.classList.add('visible');
            const closeModal = () => {
                document.querySelectorAll('.modal-overlay.visible').forEach(m => m.classList.remove('visible'));
                currentPollId = null;
            };
            const showConfirmationModal = (title, message, onConfirm) => {
                confirmModalTitle.textContent = title;
                confirmModalMessage.innerHTML = message; // Use innerHTML to allow simple formatting like <br>
                openModal(confirmModal);

                // Clone and replace the button to remove old event listeners
                const newOkBtn = confirmOkBtn.cloneNode(true);
                confirmOkBtn.parentNode.replaceChild(newOkBtn, confirmOkBtn);

                newOkBtn.addEventListener('click', () => {
                    closeModal();
                    onConfirm();
                }, {
                    once: true
                });
            };

            const openPollModal = (mode = 'add', poll = {}) => {
                pollModalForm.reset();
                currentPollId = null;
                if (mode === 'edit') {
                    pollModalTitle.textContent = "📝 ویرایش نظرسنجی";
                    pollSubmitBtn.innerHTML = "💾 ذخیره تغییرات";
                    currentPollId = poll.id;
                    pollQuestionInput.value = poll.question;
                } else {
                    pollModalTitle.textContent = "✨ افزودن نظرسنجی جدید";
                    pollSubmitBtn.innerHTML = "➕ افزودن";
                }
                openModal(pollModal);
            };
            const openOptionsModal = async (poll) => {
                currentPollId = poll.id;
                optionsModalQuestion.textContent = poll.question;
                openModal(optionsModal);
                await loadOptionsForPoll(poll.id);
            };
            const openResultsModal = async (poll) => {
                openModal(resultsModal);
                resultsModalQuestion.textContent = poll.question;
                resultsTableContainer.innerHTML = '<p>در حال بارگذاری نتایج...</p>';
                if (resultsChart) resultsChart.destroy();

                const results = await apiRequest('getPollResults', 'GET', {
                    poll_id: poll.id
                });
                if (results) {
                    renderResultsTable(results);
                    renderResultsChart(results);
                }
            };

            // --- Data Rendering ---
            const renderPollsList = () => {
                pollsListBody.innerHTML = '';
                if (pollsData.length === 0) {
                    pollsListBody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 2rem;">هیچ نظرسنجی ثبت نشده است.</td></tr>';
                    return;
                }
                pollsData.forEach(poll => {
                    const row = document.createElement('tr');
                    const statusBadge = poll.is_active ?
                        '<span class="status-badge active">فعال</span>' :
                        '<span class="status-badge inactive">غیرفعال</span>';

                    row.innerHTML = `
                        <td data-label="سوال">${escapeHTML(poll.question)}</td>
                        <td data-label="گزینه‌ها">${poll.options_count ?? 'N/A'}</td>
                        <td data-label="آرا">${poll.votes_count ?? 'N/A'}</td>
                        <td data-label="وضعیت">${statusBadge}</td>
                        <td class="actions-cell">
                            <button class="btn-icon view-results-btn" data-id="${poll.id}" title="نمایش نتایج">📊</button>
                            <button class="btn-icon manage-options-btn" data-id="${poll.id}" title="مدیریت گزینه‌ها">⚙️</button>
                            <button class="btn-icon edit-poll-btn" data-id="${poll.id}" title="ویرایش سوال">✏️</button>
                            ${!poll.is_active ? `<button class="btn-icon set-active-btn" data-id="${poll.id}" title="فعال‌سازی">✅</button>` : ''}
                            <button class="btn-icon delete-poll-btn" data-id="${poll.id}" title="حذف نظرسنجی">🗑️</button>
                        </td>`;
                    pollsListBody.appendChild(row);
                });
            };
            const renderOptionsList = (options) => {
                optionsList.innerHTML = '';
                if (options.length === 0) {
                    optionsList.innerHTML = '<li style="justify-content:center; color: var(--secondary-text-color);">هنوز گزینه‌ای اضافه نشده است.</li>';
                }
                options.forEach(opt => {
                    const li = document.createElement('li');
                    li.innerHTML = `
                        <span>${escapeHTML(opt.option_text)} (ظرفیت: ${opt.capacity})</span>
                        <button class="btn-icon delete-option-btn" data-id="${opt.id}" title="حذف گزینه">🗑️</button>`;
                    optionsList.appendChild(li);
                });
            };
            const renderResultsTable = (results) => {
                if (results.length === 0) {
                    resultsTableContainer.innerHTML = '<p style="text-align: center; padding: 1rem;">هنوز رایی برای این نظرسنجی ثبت نشده است.</p>';
                    return;
                }
                let tableHTML = `<table><thead><tr><th>نام کاربر</th><th>گزینه انتخابی</th><th>زمان ثبت</th></tr></thead><tbody>`;
                results.forEach(record => {
                    const votedAt = new Date(record.voted_at + 'Z').toLocaleString('fa-IR', {
                        timeZone: 'Asia/Tehran'
                    });
                    tableHTML += `<tr><td>${escapeHTML(record.user_name)}</td><td>${escapeHTML(record.option_text)}</td><td>${votedAt}</td></tr>`;
                });
                tableHTML += '</tbody></table>';
                resultsTableContainer.innerHTML = tableHTML;
            };
            const renderResultsChart = (results) => {
                const voteCounts = results.reduce((acc, vote) => {
                    acc[vote.option_text] = (acc[vote.option_text] || 0) + 1;
                    return acc;
                }, {});

                const labels = Object.keys(voteCounts);
                const data = Object.values(voteCounts);

                if (labels.length === 0) return;

                resultsChart = new Chart(resultsChartCanvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'تعداد آرا',
                            data: data,
                            backgroundColor: 'rgba(0, 174, 112, 0.6)',
                            borderColor: 'rgba(0, 174, 112, 1)',
                            borderRadius: 4,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            };

            // --- Main Logic Functions ---
            const loadPageData = async () => {
                document.querySelectorAll('.skeleton-loader').forEach(el => el.style.display = 'table-row');
                pollsListBody.querySelectorAll('tr:not(.skeleton-loader)').forEach(el => el.remove());

                const result = await apiRequest('getPolls');

                document.querySelectorAll('.skeleton-loader').forEach(el => el.style.display = 'none');

                if (result) {
                    pollsData = result;
                    renderPollsList();
                }
            };
            const loadOptionsForPoll = async (pollId) => {
                optionsList.innerHTML = '<li>در حال بارگذاری...</li>';
                const options = await apiRequest('getOptions', 'GET', {
                    poll_id: pollId
                });
                if (options) renderOptionsList(options);
            };

            // --- Event Handlers ---
            const handlePollFormSubmit = async (e) => {
                e.preventDefault();
                const pollData = {
                    id: currentPollId,
                    question: pollQuestionInput.value.trim()
                };
                if (!pollData.question) {
                    showToast('متن سوال نمی‌تواند خالی باشد.', 'error');
                    return;
                }
                pollSubmitBtn.classList.add('loading');
                const action = currentPollId ? 'updatePoll' : 'addPoll';
                const result = await apiRequest(action, 'POST', pollData);
                pollSubmitBtn.classList.remove('loading');
                if (result) {
                    showToast(`نظرسنجی با موفقیت ${currentPollId ? 'ویرایش شد' : 'افزوده شد'}.`);
                    closeModal();
                    await loadPageData();
                }
            };
            const handleAddOptionSubmit = async (e) => {
                e.preventDefault();
                const optionData = {
                    poll_id: currentPollId,
                    text: optionTextInput.value.trim(),
                    capacity: parseInt(optionCapacityInput.value, 10)
                };
                if (!optionData.text || isNaN(optionData.capacity) || optionData.capacity < 1) {
                    showToast('متن و ظرفیت گزینه را به درستی وارد کنید.', 'error');
                    return;
                }
                const addBtn = document.getElementById('add-option-btn');
                addBtn.classList.add('loading');
                const result = await apiRequest('addOption', 'POST', optionData);
                addBtn.classList.remove('loading');
                if (result) {
                    showToast('گزینه با موفقیت افزوده شد.');
                    addOptionForm.reset();
                    optionCapacityInput.value = 10;
                    await loadOptionsForPoll(currentPollId);
                }
            };

            // --- Event Listeners ---
            document.getElementById('add-new-poll-btn').addEventListener('click', () => openPollModal('add'));
            document.querySelectorAll('.close-modal-btn').forEach(btn => btn.addEventListener('click', closeModal));
            confirmCancelBtn.addEventListener('click', closeModal);

            pollModalForm.addEventListener('submit', handlePollFormSubmit);
            addOptionForm.addEventListener('submit', handleAddOptionSubmit);

            pollsListBody.addEventListener('click', async (e) => {
                const target = e.target.closest('.btn-icon');
                if (!target) return;

                const id = parseInt(target.dataset.id, 10);
                const poll = pollsData.find(p => p.id === id);
                if (!poll) return;

                if (target.classList.contains('view-results-btn')) {
                    await openResultsModal(poll);
                } else if (target.classList.contains('edit-poll-btn')) {
                    openPollModal('edit', poll);
                } else if (target.classList.contains('manage-options-btn')) {
                    await openOptionsModal(poll);
                } else if (target.classList.contains('delete-poll-btn')) {
                    showConfirmationModal(
                        'حذف نظرسنجی',
                        `آیا از حذف نظرسنجی "<b style="color:var(--danger-color);">${escapeHTML(poll.question)}</b>" و تمام رای‌های آن مطمئن هستید؟`,
                        async () => {
                            if (await apiRequest('deletePoll', 'POST', {
                                    id
                                })) {
                                showToast('نظرسنجی با موفقیت حذف شد.');
                                await loadPageData();
                            }
                        }
                    );
                } else if (target.classList.contains('set-active-btn')) {
                    showConfirmationModal(
                        'فعال‌سازی نظرسنجی',
                        'آیا می‌خواهید این نظرسنجی را فعال کنید؟<br>(نظرسنجی فعال قبلی غیرفعال خواهد شد)',
                        async () => {
                            if (await apiRequest('setActivePoll', 'POST', {
                                    id
                                })) {
                                showToast('نظرسنجی با موفقیت فعال شد.');
                                await loadPageData();
                            }
                        }
                    );
                }
            });

            optionsList.addEventListener('click', async (e) => {
                const target = e.target.closest('.delete-option-btn');
                if (!target) return;
                const id = parseInt(target.dataset.id, 10);

                showConfirmationModal('حذف گزینه', 'آیا از حذف این گزینه مطمئن هستید؟', async () => {
                    if (await apiRequest('deleteOption', 'POST', {
                            id
                        })) {
                        showToast('گزینه حذف شد.');
                        await loadOptionsForPoll(currentPollId);
                    }
                });
            });

            // --- Initial Load ---
            loadPageData();
        });
    </script>
</body>

</html>
