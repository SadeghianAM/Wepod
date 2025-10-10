<?php
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت کارشناسان</title>
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

        html,
        body {
            height: 100%;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-size: 16px;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
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
            height: 60px;
            font-size: 0.85rem;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: clamp(1.5rem, 3vw, 2rem);
            display: flex;
            align-items: center;
            gap: .75rem;
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
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.6em;
        }

        .btn:hover {
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

        .btn-icon {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--secondary-text);
            padding: .5rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
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

        .content-body {
            background-color: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .toolbar {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        #searchInput {
            width: 100%;
            padding: 0.8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: all .2s ease;
        }

        #searchInput:focus-visible {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 174, 112, .15);
        }

        .table-container {
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th,
        .users-table td {
            padding: 1.25rem 1.5rem;
            text-align: right;
            white-space: nowrap;
        }

        .users-table thead {
            background-color: var(--bg-color);
        }

        .users-table th {
            font-weight: 600;
            color: var(--secondary-text);
            font-size: 0.9rem;
        }

        .users-table tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
        }

        .users-table tbody tr:last-child {
            border-bottom: none;
        }

        .users-table tbody tr:hover {
            background-color: var(--primary-light);
        }

        .user-name {
            font-weight: 600;
            color: var(--text-color);
        }

        .status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .5em;
        }

        .status-success {
            background-color: var(--success-light);
            color: var(--success-color);
        }

        .status-secondary {
            background-color: var(--border-color);
            color: var(--secondary-text);
        }

        .skeleton-loader td .skeleton-item {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-animation 1.5s infinite linear;
            border-radius: 4px;
            height: 1.2rem;
        }

        @keyframes skeleton-animation {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .empty-state td {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state-icon {
            font-size: 4rem;
            color: var(--border-color);
            margin-bottom: 1rem;
        }

        .empty-state p {
            font-size: 1.1rem;
            color: var(--secondary-text);
        }

        .drawer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            visibility: hidden;
            opacity: 0;
            transition: visibility 0.3s, opacity 0.3s;
            z-index: 1000;
        }

        .drawer.open {
            visibility: visible;
            opacity: 1;
        }

        .drawer-content {
            position: absolute;
            top: 0;
            left: -400px;
            width: 100%;
            max-width: 400px;
            height: 100%;
            background-color: var(--card-bg);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            transition: left 0.3s ease-out;
            display: flex;
            flex-direction: column;
        }

        .drawer.open .drawer-content {
            left: 0;
        }

        .drawer-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .drawer-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .drawer-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex-grow: 1;
        }

        .drawer-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--secondary-text);
            font-size: .9rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus-visible {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 174, 112, .15);
        }

        .toggle-switch {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .switch-label {
            position: relative;
            cursor: pointer;
            width: 50px;
            height: 26px;
            background: var(--border-color);
            border-radius: 34px;
            transition: background-color 0.2s;
        }

        .switch-label:before {
            content: '';
            position: absolute;
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            border-radius: 50%;
            transition: transform 0.2s;
        }

        input:checked+.switch-label {
            background-color: var(--success-color);
        }

        input:checked+.switch-label:before {
            transform: translateX(24px);
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

        #printable-content {
            display: none;
        }

        @media print {
            body {
                background-color: #fff;
                color: #000;
            }

            #header-placeholder,
            #footer-placeholder,
            main,
            .drawer,
            #toast-container {
                display: none !important;
            }

            #printable-content {
                display: block;
                margin: 1.5cm;
            }

            #printable-content h1 {
                text-align: center;
                margin-bottom: 0.5rem;
                font-size: 1.5rem;
            }

            #print-timestamp {
                text-align: center;
                font-size: 0.9rem;
                color: #555;
                margin-bottom: 2rem;
            }

            #printable-content .users-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 9pt;
            }

            #printable-content .users-table th,
            #printable-content .users-table td {
                border: 1px solid #333;
                padding: 8px;
                text-align: right;
            }

            #printable-content .users-table th {
                background-color: #f2f2f2;
                font-weight: bold;
            }

            @page {
                size: A4 landscape;
                margin: 0;
            }
        }

        @media (max-width: 768px) {
            .users-table thead {
                display: none;
            }

            .users-table tr {
                display: block;
                border: 1px solid var(--border-color);
                border-radius: var(--radius);
                margin-bottom: 1rem;
                padding: 1rem;
            }

            .users-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0;
                border-bottom: 1px dashed #eee;
            }

            .users-table td:last-child {
                border-bottom: none;
            }

            .users-table td::before {
                content: attr(data-label);
                font-weight: 600;
                margin-left: 1rem;
            }
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="content-header">
            <h1 class="page-title">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                <span>مدیریت کاربران</span>
            </h1>
            <div class="header-actions" style="display: flex; gap: 1rem;">
                <button id="print-btn" class="btn btn-secondary">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9" />
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <rect x="6" y="14" width="12" height="8" />
                    </svg>
                    <span>چاپ لیست</span>
                </button>
                <button id="add-new-user-btn" class="btn btn-primary">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="M12 5v14" />
                    </svg>
                    <span>افزودن کاربر جدید</span>
                </button>
            </div>
        </div>
        <div class="content-body">
            <div class="toolbar">
                <input type="text" id="searchInput" placeholder="جستجو بر اساس نام، نام کاربری یا شناسه...">
            </div>
            <div class="table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>نام کامل</th>
                            <th>نام کاربری</th>
                            <th>شناسه</th>
                            <th>تاریخ شروع</th>
                            <th>نقش</th>
                            <th>امتیاز</th>
                            <th>ادمین</th>
                            <th>تعداد شانس</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody id="user-list"></tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="printable-content">
        <h1>گزارش لیست کاربران</h1>
        <p id="print-timestamp"></p>
        <table class="users-table" id="printTable">
            <thead>
                <tr>
                    <th>نام کامل</th>
                    <th>نام کاربری</th>
                    <th>شناسه</th>
                    <th>تاریخ شروع</th>
                    <th>نقش</th>
                    <th>امتیاز</th>
                    <th>ادمین</th>
                    <th>تعداد شانس</th>
                </tr>
            </thead>
            <tbody id="printTableBody"></tbody>
        </table>
    </div>

    <div id="user-drawer" class="drawer">
        <div class="drawer-content">
            <div class="drawer-header">
                <h2 id="drawer-title" class="drawer-title"></h2>
                <button id="close-drawer-btn" class="btn-icon">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>
            <form id="user-form" class="drawer-body" autocomplete="off">
                <div class="form-group">
                    <label for="userIdInput">داخلی:</label>
                    <input type="number" id="userIdInput" name="id" required />
                </div>
                <div class="form-group">
                    <label for="name">نام کامل:</label>
                    <input type="text" id="name" name="name" required />
                </div>
                <div class="form-group">
                    <label for="username">نام کاربری:</label>
                    <input type="text" id="username" name="username" required />
                </div>
                <div class="form-group">
                    <label for="role">نقش:</label>
                    <input type="text" id="role" name="role" placeholder="مثال: کارشناس فروش" />
                </div>
                <div class="form-group">
                    <label for="password">رمز عبور:</label>
                    <input type="password" id="password" placeholder="(برای کاربر جدید یا تغییر رمز)" autocomplete="new-password" />
                </div>
                <div class="form-group">
                    <label for="start_work">تاریخ شروع به کار:</label>
                    <input type="text" id="start_work" name="start_work" placeholder="مثال: 1403/01/15" />
                </div>
                <div class="form-group">
                    <label for="score">امتیاز:</label>
                    <input type="number" id="score" name="score" value="0" />
                </div>
                <div class="form-group">
                    <label for="spin_chances">تعداد شانس گردونه:</label>
                    <input type="number" id="spin_chances" name="spin_chances" value="0" />
                </div>
                <div class="form-group">
                    <label>کاربر ادمین است؟</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="is_admin" name="is_admin" />
                        <label for="is_admin" class="switch-label"></label>
                        <span>فعال</span>
                    </div>
                </div>
            </form>
            <div class="drawer-footer">
                <button type="submit" form="user-form" class="btn btn-primary">ذخیره تغییرات</button>
            </div>
        </div>
    </div>

    <div id="toast-container"></div>
    <div id="footer-placeholder"></div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let currentUserId = null;
            let allFetchedUsers = [];
            let currentlyDisplayedUsers = [];
            const userListBody = document.getElementById("user-list");
            const searchInput = document.getElementById("searchInput");
            const drawer = document.getElementById("user-drawer");
            const form = document.getElementById('user-form');
            const drawerTitle = document.getElementById('drawer-title');
            const apiEndpoint = '/admin/user-management/user-api.php';
            const printBtn = document.getElementById('print-btn');

            const ICONS = {
                add: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>`,
                edit: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>`,
                delete: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>`
            };

            const openDrawer = (title) => {
                drawerTitle.innerHTML = title;
                drawer.classList.add('open');
            };
            const closeDrawer = () => drawer.classList.remove('open');

            function showToast(message, type = 'success', duration = 4000) {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.textContent = message;
                container.appendChild(toast);
                setTimeout(() => toast.classList.add('show'), 10);
                setTimeout(() => {
                    toast.classList.remove('show');
                    toast.addEventListener('transitionend', () => toast.remove());
                }, duration);
            }

            function showConfirmation(message, onConfirm) {
                const toastContainer = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = 'toast toast-confirm';
                toast.innerHTML = `<div class="toast-message">${message}</div>
                    <div class="toast-buttons">
                        <button class="btn btn-danger" id="confirmAction">بله، حذف کن</button>
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
                setTimeout(() => toast.classList.add('show'), 10);
            }

            async function apiCall(method, body) {
                try {
                    const options = {
                        method,
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    };
                    if (body) options.body = JSON.stringify(body);
                    const response = await fetch(apiEndpoint, options);
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || `HTTP error! status: ${response.status}`);
                    }
                    return data;
                } catch (error) {
                    showToast('خطا: ' + error.message, 'error');
                    return null;
                }
            }

            const showSkeletonLoader = () => {
                let skeletonHTML = '';
                for (let i = 0; i < 5; i++) {
                    skeletonHTML += `
                        <tr class="skeleton-loader">
                            <td><div class="skeleton-item" style="width: 120px;"></div></td>
                            <td><div class="skeleton-item" style="width: 100px;"></div></td>
                            <td><div class="skeleton-item" style="width: 50px;"></div></td>
                            <td><div class="skeleton-item" style="width: 90px;"></div></td>
                            <td><div class="skeleton-item" style="width: 80px;"></div></td>
                            <td><div class="skeleton-item" style="width: 50px;"></div></td>
                            <td><div class="skeleton-item" style="width: 60px;"></div></td>
                            <td><div class="skeleton-item" style="width: 70px;"></div></td>
                            <td><div class="skeleton-item" style="width: 70px;"></div></td>
                        </tr>`;
                }
                userListBody.innerHTML = skeletonHTML;
            };

            async function loadUsersAndRender() {
                showSkeletonLoader();
                const users = await apiCall('GET');
                if (users) {
                    allFetchedUsers = users;
                    renderUsers();
                } else {
                    userListBody.innerHTML = `<tr class="empty-state"><td colspan="9">خطا در بارگذاری اطلاعات.</td></tr>`;
                }
            }

            function renderUsers() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const filteredUsers = searchTerm ? allFetchedUsers.filter(user =>
                    user.name.toLowerCase().includes(searchTerm) ||
                    user.username.toLowerCase().includes(searchTerm) ||
                    String(user.id).includes(searchTerm)
                ) : allFetchedUsers;

                currentlyDisplayedUsers = filteredUsers;
                userListBody.innerHTML = "";
                if (filteredUsers.length === 0) {
                    userListBody.innerHTML = `<tr class="empty-state"><td colspan="9"><p>${searchTerm ? 'کاربری با این مشخصات یافت نشد.' : 'هنوز کاربری اضافه نشده است.'}</p></td></tr>`;
                    return;
                }
                filteredUsers.forEach(user => {
                    const row = document.createElement("tr");
                    row.dataset.user = JSON.stringify(user);
                    const isAdminBadge = user.is_admin ?
                        '<span class="status status-success">ادمین</span>' :
                        '<span class="status status-secondary">کاربر</span>';

                    row.innerHTML = `
                        <td data-label="نام کامل" class="user-name">${user.name}</td>
                        <td data-label="نام کاربری">${user.username}</td>
                        <td data-label="شناسه">${user.id}</td>
                        <td data-label="تاریخ شروع">${user.start_work || '-'}</td>
                        <td data-label="نقش">${user.role || '-'}</td>
                        <td data-label="امتیاز">${user.score ?? 0}</td>
                        <td data-label="ادمین">${isAdminBadge}</td>
                        <td data-label="تعداد شانس">${user.spin_chances ?? 0}</td>
                        <td data-label="عملیات">
                            <div class="actions-cell">
                                <button class="btn-icon edit-btn" title="ویرایش">${ICONS.edit}</button>
                                <button class="btn-icon delete-btn" title="حذف">${ICONS.delete}</button>
                            </div>
                        </td>`;
                    row.querySelector('.edit-btn').addEventListener('click', handleEdit);
                    row.querySelector('.delete-btn').addEventListener('click', handleDelete);
                    userListBody.appendChild(row);
                });
            }

            function generatePrintContent() {
                const printTableBody = document.getElementById('printTableBody');
                const printTimestamp = document.getElementById('print-timestamp');
                printTableBody.innerHTML = '';

                const now = new Date();
                const formattedDate = now.toLocaleDateString('fa-IR');
                const formattedTime = now.toLocaleTimeString('fa-IR', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                printTimestamp.textContent = `تاریخ گزارش: ${formattedDate} - ساعت: ${formattedTime}`;

                if (!currentlyDisplayedUsers || currentlyDisplayedUsers.length === 0) {
                    printTableBody.innerHTML = `<tr><td colspan="8" style="text-align:center;">داده‌ای برای چاپ وجود ندارد.</td></tr>`;
                    return;
                }

                currentlyDisplayedUsers.forEach(user => {
                    const row = printTableBody.insertRow();
                    row.insertCell().textContent = user.name;
                    row.insertCell().textContent = user.username;
                    row.insertCell().textContent = user.id;
                    row.insertCell().textContent = user.start_work || '-';
                    row.insertCell().textContent = user.role || '-';
                    row.insertCell().textContent = user.score ?? 0;
                    row.insertCell().textContent = user.is_admin ? 'ادمین' : 'کاربر';
                    row.insertCell().textContent = user.spin_chances ?? 0;
                });
            }

            printBtn.addEventListener('click', () => {
                generatePrintContent();
                window.print();
            });

            document.getElementById("add-new-user-btn").addEventListener("click", () => {
                currentUserId = null;
                form.reset();
                form.elements['score'].value = 0;
                form.elements['spin_chances'].value = 1;
                document.getElementById("password").required = true;
                openDrawer(`${ICONS.add} <span>افزودن کاربر جدید</span>`);
            });

            function handleEdit(e) {
                const user = JSON.parse(e.target.closest('tr').dataset.user);
                currentUserId = user.id;
                form.elements['id'].value = user.id;
                form.elements['name'].value = user.name;
                form.elements['username'].value = user.username;
                form.elements['password'].value = "";
                form.elements['password'].required = false;
                form.elements['start_work'].value = user.start_work || "";
                form.elements['score'].value = user.score ?? 0;
                form.elements['role'].value = user.role || "";
                form.elements['spin_chances'].value = user.spin_chances ?? 0;
                form.elements['is_admin'].checked = user.is_admin == 1;
                openDrawer(`${ICONS.edit} <span>ویرایش کاربر</span>`);
            }

            function handleDelete(e) {
                const user = JSON.parse(e.target.closest('tr').dataset.user);
                showConfirmation(`آیا از حذف کاربر "${user.name}" مطمئن هستید؟`, async () => {
                    const result = await apiCall('POST', {
                        action: 'delete',
                        id: user.id
                    });
                    if (result && result.success) loadUsersAndRender();
                });
            }

            form.addEventListener("submit", async (e) => {
                e.preventDefault();
                const payload = {
                    action: currentUserId !== null ? 'update' : 'create',
                    id: currentUserId,
                    new_id: parseInt(form.elements['id'].value),
                    name: form.elements['name'].value.trim(),
                    username: form.elements['username'].value.trim(),
                    password: form.elements['password'].value,
                    start_work: form.elements['start_work'].value.trim(),
                    is_admin: form.elements['is_admin'].checked ? 1 : 0,
                    score: parseInt(form.elements['score'].value) || 0,
                    role: form.elements['role'].value.trim(),
                    spin_chances: parseInt(form.elements['spin_chances'].value) || 0,
                };
                const result = await apiCall('POST', payload);
                if (result && result.success) {
                    closeDrawer();
                    loadUsersAndRender();
                    showToast('عملیات با موفقیت انجام شد.', 'success');
                }
            });

            searchInput.addEventListener('input', renderUsers);
            document.getElementById('close-drawer-btn').addEventListener('click', closeDrawer);
            drawer.addEventListener('click', (e) => {
                if (e.target === drawer) closeDrawer();
            });

            loadUsersAndRender();
        });
    </script>
    <script src="/js/header.js"></script>
</body>

</html>
