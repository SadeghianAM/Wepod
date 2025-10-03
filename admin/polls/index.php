<?php
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
            --success-light: #e9f7eb;
            --danger-light: #fbebec;
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

        .icon {
            width: 1.1em;
            height: 1.1em;
            stroke-width: 2.2;
            vertical-align: -0.15em;
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
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-body.no-padding {
            padding: 0;
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

        .btn:hover {
            transform: translateY(-2px);
            filter: brightness(0.92);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-secondary {
            background-color: var(--secondary-text);
            color: white;
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
            border-radius: var(--radius);
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
            display: flex;
            align-items: center;
            gap: .6rem;
            color: var(--primary-dark);
        }

        .modal-header .icon {
            color: var(--primary-color);
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

        .modal-footer {
            margin-top: 2rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .status-badge {
            padding: .4em .9em;
            border-radius: 20px;
            font-size: .85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .5em;
        }

        .status-badge .icon {
            width: 1.2em;
            height: 1.2em;
        }

        .status-badge.active {
            background-color: var(--success-light);
            color: var(--success-color);
        }

        .status-badge.inactive {
            background-color: var(--border-color);
            color: var(--secondary-text);
        }

        #options-list {
            list-style: none;
            padding: 0;
            margin-top: 1.5rem;
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
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

        .skeleton-loader td {
            padding: 1rem 1.5rem;
        }

        .skeleton-line {
            width: 100%;
            height: 1.2rem;
            background-color: var(--border-color);
            border-radius: 0.25rem;
            margin-bottom: 0.75rem;
            animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .skeleton-line:last-child {
            width: 70%;
        }

        @keyframes pulse {
            50% {
                opacity: 0.6;
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

        @media screen and (max-width: 768px) {
            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
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
                color: var(--secondary-text);
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
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="page-header">
            <div class="page-title">
                <h1>
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 9/9-2-2-2 2-2-2Z" />
                        <path d="m12 15-3-3a3 3 0 0 1 3-3 3 3 0 0 1 3 3Z" />
                    </svg>
                    <span>مدیریت نظرسنجی</span>
                </h1>
                <p>نظرسنجی‌ها، گزینه‌ها و وضعیت آن‌ها را مدیریت کنید.</p>
            </div>
            <button id="add-new-poll-btn" class="btn btn-primary">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                <span>افزودن نظرسنجی جدید</span>
            </button>
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
                <button class="btn-icon close-modal-btn" title="بستن">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>
            <form id="poll-modal-form" onsubmit="return false;">
                <div class="form-group">
                    <label for="poll-question">متن سوال نظرسنجی</label>
                    <input type="text" id="poll-question" placeholder="مثال: بهترین ساعت کاری کدام است؟" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-modal-btn">لغو</button>
                    <button type="submit" id="poll-submit-btn" class="btn btn-primary"></button>
                </div>
            </form>
        </div>
    </div>

    <div id="options-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="options-modal-title">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 0 2l-.15.08a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1 0-2l.15-.08a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <span>مدیریت گزینه‌ها</span>
                </h2>
                <button class="btn-icon close-modal-btn" title="بستن">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>
            <p id="options-modal-question" style="font-weight: 500; color: var(--secondary-text); margin-bottom: 1rem;"></p>
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
                <button type="submit" id="add-option-btn" class="btn btn-primary"></button>
            </form>
        </div>
    </div>

    <div id="results-modal" class="modal-overlay">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2 id="results-modal-title">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" x2="12" y1="20" y2="10" />
                        <line x1="18" x2="18" y1="20" y2="4" />
                        <line x1="6" x2="6" y1="20" y2="16" />
                    </svg>
                    <span>نتایج نظرسنجی</span>
                </h2>
                <button class="btn-icon close-modal-btn" title="بستن">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>
            <p id="results-modal-question" style="font-weight: 500; color: var(--secondary-text); margin-bottom: 1.5rem;"></p>
            <div style="height: 300px; margin-bottom: 2rem; position: relative;">
                <canvas id="results-chart"></canvas>
            </div>
            <div id="results-table-container"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal-btn">بستن</button>
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
            const ICONS = {
                add: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>`,
                save: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>`,
                results: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/></svg>`,
                options: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 0 2l-.15.08a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1 0-2l.15-.08a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>`,
                edit: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>`,
                setActive: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>`,
                delete: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>`,
                statusActive: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3H7c-2.2 0-4 1.8-4 4v10c0 2.2 1.8 4 4 4h10c2.2 0 4-1.8 4-4V7c0-2.2-1.8-4-4-4z"/><path d="m9 12 2 2 4-4"/></svg>`,
                statusInactive: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3H7c-2.2 0-4 1.8-4 4v10c0 2.2 1.8 4 4 4h10c2.2 0 4-1.8 4-4V7c0-2.2-1.8-4-4-4z"/><line x1="9" x2="15" y1="15" y2="9"/></svg>`
            };

            const pollsListBody = document.getElementById('polls-list-body');
            const pollModal = document.getElementById('poll-modal');
            const optionsModal = document.getElementById('options-modal');
            const resultsModal = document.getElementById('results-modal');
            const pollModalForm = document.getElementById('poll-modal-form');
            const pollModalTitle = document.getElementById('poll-modal-title');
            const pollSubmitBtn = document.getElementById('poll-submit-btn');
            const pollQuestionInput = document.getElementById('poll-question');
            const optionsModalQuestion = document.getElementById('options-modal-question');
            const optionsList = document.getElementById('options-list');
            const addOptionForm = document.getElementById('add-option-form');
            const optionTextInput = document.getElementById('option-text');
            const optionCapacityInput = document.getElementById('option-capacity');
            const addOptionBtn = document.getElementById('add-option-btn');
            const resultsModalQuestion = document.getElementById('results-modal-question');
            const resultsTableContainer = document.getElementById('results-table-container');
            const resultsChartCanvas = document.getElementById('results-chart');

            const escapeHTML = (str) => {
                const p = document.createElement('p');
                p.textContent = str;
                return p.innerHTML;
            }
            const showToast = (message, type = 'success', duration = 4000) => {
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
            };

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
            const openModal = (modal) => modal.classList.add('visible');
            const closeModal = () => {
                document.querySelectorAll('.modal-overlay.visible').forEach(m => m.classList.remove('visible'));
                currentPollId = null;
            };
            const openPollModal = (mode = 'add', poll = {}) => {
                pollModalForm.reset();
                currentPollId = null;
                if (mode === 'edit') {
                    pollModalTitle.innerHTML = `${ICONS.edit} <span>ویرایش نظرسنجی</span>`;
                    pollSubmitBtn.innerHTML = `${ICONS.save} <span>ذخیره تغییرات</span>`;
                    currentPollId = poll.id;
                    pollQuestionInput.value = poll.question;
                } else {
                    pollModalTitle.innerHTML = `${ICONS.add} <span>افزودن نظرسنجی جدید</span>`;
                    pollSubmitBtn.innerHTML = `${ICONS.add} <span>افزودن</span>`;
                }
                openModal(pollModal);
            };
            const openOptionsModal = async (poll) => {
                currentPollId = poll.id;
                addOptionBtn.innerHTML = `${ICONS.add} <span>افزودن</span>`;
                optionsModalQuestion.textContent = poll.question;
                openModal(optionsModal);
                await loadOptionsForPoll(poll.id);
            };
            const openResultsModal = async (poll) => {
                openModal(resultsModal);
                resultsModalQuestion.textContent = poll.question;
                resultsTableContainer.innerHTML = '<p style="text-align: center; padding: 1rem;">در حال بارگذاری نتایج...</p>';
                if (resultsChart) resultsChart.destroy();
                const results = await apiRequest('getPollResults', 'GET', {
                    poll_id: poll.id
                });
                if (results) {
                    renderResultsTable(results);
                    renderResultsChart(results);
                }
            };
            const renderPollsList = () => {
                pollsListBody.innerHTML = '';
                if (pollsData.length === 0) {
                    pollsListBody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 2rem;">هیچ نظرسنجی ثبت نشده است.</td></tr>';
                    return;
                }
                pollsData.forEach(poll => {
                    const row = document.createElement('tr');
                    const statusBadge = poll.is_active ?
                        `<span class="status-badge active">${ICONS.statusActive} <span>فعال</span></span>` :
                        `<span class="status-badge inactive">${ICONS.statusInactive} <span>غیرفعال</span></span>`;
                    row.innerHTML = `
                        <td data-label="سوال">${escapeHTML(poll.question)}</td>
                        <td data-label="گزینه‌ها">${poll.options_count ?? 'N/A'}</td>
                        <td data-label="آرا">${poll.votes_count ?? 'N/A'}</td>
                        <td data-label="وضعیت">${statusBadge}</td>
                        <td class="actions-cell">
                            <button class="btn-icon view-results-btn" data-id="${poll.id}" title="نمایش نتایج">${ICONS.results}</button>
                            <button class="btn-icon manage-options-btn" data-id="${poll.id}" title="مدیریت گزینه‌ها">${ICONS.options}</button>
                            <button class="btn-icon edit-poll-btn" data-id="${poll.id}" title="ویرایش سوال">${ICONS.edit}</button>
                            ${!poll.is_active ? `<button class="btn-icon set-active-btn" data-id="${poll.id}" title="فعال‌سازی">${ICONS.setActive}</button>` : ''}
                            <button class="btn-icon delete-poll-btn" data-id="${poll.id}" title="حذف نظرسنجی">${ICONS.delete}</button>
                        </td>`;
                    pollsListBody.appendChild(row);
                });
            };
            const renderOptionsList = (options) => {
                optionsList.innerHTML = '';
                if (options.length === 0) {
                    optionsList.innerHTML = '<li style="justify-content:center; color: var(--secondary-text);">هنوز گزینه‌ای اضافه نشده است.</li>';
                }
                options.forEach(opt => {
                    const li = document.createElement('li');
                    li.innerHTML = `
                        <span>${escapeHTML(opt.option_text)} (ظرفیت: ${opt.capacity})</span>
                        <button class="btn-icon delete-option-btn" data-id="${opt.id}" title="حذف گزینه">${ICONS.delete}</button>`;
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
                    const votedAt = new Date(record.voted_at + 'Z').toLocaleString('fa-IR');
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
                addOptionBtn.classList.add('loading');
                const result = await apiRequest('addOption', 'POST', optionData);
                addOptionBtn.classList.remove('loading');
                if (result) {
                    showToast('گزینه با موفقیت افزوده شد.');
                    addOptionForm.reset();
                    optionCapacityInput.value = 10;
                    await loadOptionsForPoll(currentPollId);
                }
            };

            document.getElementById('add-new-poll-btn').addEventListener('click', () => openPollModal('add'));
            document.querySelectorAll('.close-modal-btn').forEach(btn => btn.addEventListener('click', closeModal));
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
                    showConfirmation(`آیا از حذف نظرسنجی "<b style="color:var(--danger-color);">${escapeHTML(poll.question)}</b>" و تمام رای‌های آن مطمئن هستید؟`, "تایید و حذف", async () => {
                        if (await apiRequest('deletePoll', 'POST', {
                                id
                            })) {
                            showToast('نظرسنجی با موفقیت حذف شد.');
                            await loadPageData();
                        }
                    });
                } else if (target.classList.contains('set-active-btn')) {
                    showConfirmation('آیا می‌خواهید این نظرسنجی را فعال کنید؟ (نظرسنجی فعال قبلی غیرفعال خواهد شد)', "تایید و فعال‌سازی", async () => {
                        if (await apiRequest('setActivePoll', 'POST', {
                                id
                            })) {
                            showToast('نظرسنجی با موفقیت فعال شد.');
                            await loadPageData();
                        }
                    });
                }
            });
            optionsList.addEventListener('click', async (e) => {
                const target = e.target.closest('.delete-option-btn');
                if (!target) return;
                const id = parseInt(target.dataset.id, 10);
                showConfirmation('آیا از حذف این گزینه مطمئن هستید؟', 'تایید و حذف', async () => {
                    if (await apiRequest('deleteOption', 'POST', {
                            id
                        })) {
                        showToast('گزینه حذف شد.');
                        await loadOptionsForPoll(currentPollId);
                    }
                });
            });
            loadPageData();
        });
    </script>
</body>

</html>
