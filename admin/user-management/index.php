<?php
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت کاربران</title>
    <style>
        /* === متغیرهای اصلی طراحی === */
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --primary-light: #e6f2ff;
            --bg-color: #f4f7f9;
            --text-color: #212529;
            --secondary-text-color: #6c757d;
            --header-text: #ffffff;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --danger-color: #dc3545;
            --success-color: #28a745;
            --border-radius: 8px;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        @font-face {
            font-family: "Vazirmatn";
            src: url("/assets/fonts/Vazirmatn[wght].ttf") format("truetype");
            font-weight: 100 900;
            font-display: swap;
        }

        /* === استایل‌های پایه === */
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
            direction: rtl;
            font-size: 16px;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
        }

        main {
            padding: 2rem 1.5rem;
            max-width: 1280px;
            width: 100%;
            margin: 0 auto;
            flex-grow: 1;
            /* ✨ تغییر: این باعث می‌شود main تمام فضای خالی را پر کند و فوتر را به پایین هل دهد */
        }

        header,
        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 174, 112, 0.07);
            position: relative;
            z-index: 10;
            flex-shrink: 0;
        }

        header {
            height: 70px;
        }

        footer {
            height: 60px;
            font-size: 0.85rem;
        }

        /* === هدر و دکمه‌ها === */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: .5rem;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border: 1px solid transparent;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
        }

        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.25rem;
            color: var(--secondary-text-color);
            padding: 0.25rem;
            transition: color 0.2s;
        }

        .icon-btn:hover {
            color: var(--primary-dark);
        }

        /* === کانتینر اصلی و جستجو === */
        .content-body {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .toolbar {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        #searchInput {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
        }

        /* === جدول کاربران === */
        .table-container {
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th,
        .users-table td {
            padding: 1rem 1.5rem;
            text-align: right;
            white-space: nowrap;
        }

        .users-table thead {
            background-color: #f8f9fa;
        }

        .users-table th {
            font-weight: 600;
            color: var(--secondary-text-color);
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .users-table tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
        }

        .users-table tbody tr:last-child {
            border-bottom: none;
        }

        .users-table tbody tr:hover {
            background-color: #f1f3f5;
        }

        .user-name {
            font-weight: 500;
        }

        /* === نشان (Badge) === */
        .badge {
            padding: 0.25em 0.6em;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 20px;
        }

        .badge-success {
            background-color: var(--success-color);
            color: white;
        }

        .badge-secondary {
            background-color: #e9ecef;
            color: var(--secondary-text-color);
        }

        /* === انیمیشن اسکلتی (Skeleton Loader) === */
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

        /* === حالت خالی (Empty State) === */
        .empty-state td {
            /* ✨ تغییر: استایل به td منتقل شد تا داخل جدول اعمال شود */
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
            color: var(--secondary-text-color);
        }

        /* === پنل کشویی (Drawer) === */
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
            font-weight: 600;
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

        /* === فرم در پنل کشویی === */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
        }

        /* کلید تاگل (Toggle Switch) */
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
            background: #ccc;
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

        /* === واکنش‌گرایی (Responsiveness) === */
        @media (max-width: 768px) {
            .users-table thead {
                display: none;
            }

            .users-table tr {
                display: block;
                border: 1px solid var(--border-color);
                border-radius: var(--border-radius);
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

            .actions-cell {
                grid-area: actions;
                justify-content: flex-end;
            }
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="content-header">
            <h1 class="page-title">مدیریت کاربران</h1>
            <button id="add-new-user-btn" class="btn btn-primary"><span>➕</span> افزودن کاربر جدید</button>
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
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody id="user-list">
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="user-drawer" class="drawer">
        <div class="drawer-content">
            <div class="drawer-header">
                <h2 id="drawer-title" class="drawer-title">افزودن کاربر جدید</h2>
                <button id="close-drawer-btn" class="icon-btn">&times;</button>
            </div>
            <form id="user-form" class="drawer-body" autocomplete="off">
                <div class="form-group">
                    <label for="userIdInput">شناسه (ID):</label>
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
                    <label for="password">رمز عبور:</label>
                    <input type="password" id="password" placeholder="(برای کاربر جدید یا تغییر رمز)" />
                </div>
                <div class="form-group">
                    <label for="start_work">تاریخ شروع به کار:</label>
                    <input type="text" id="start_work" name="start_work" placeholder="مثال: 1403/01/15" />
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

    <div id="footer-placeholder"></div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let currentUserId = null;
            const userListBody = document.getElementById("user-list");
            const searchInput = document.getElementById("searchInput");
            const drawer = document.getElementById("user-drawer");
            const form = document.getElementById('user-form');
            const apiEndpoint = '/admin/user-management/user-api.php';

            // === توابع مدیریت پنل کشویی (Drawer) ===
            const openDrawer = (title) => {
                document.getElementById('drawer-title').textContent = title;
                drawer.classList.add('open');
            };
            const closeDrawer = () => {
                drawer.classList.remove('open');
            };

            // === توابع API و رندر کردن ===
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
                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                    }
                    return await response.json();
                } catch (error) {
                    alert('خطا: ' + error.message);
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
                    <td><div class="skeleton-item" style="width: 60px;"></div></td>
                    <td><div class="skeleton-item" style="width: 70px;"></div></td>
                </tr>`;
                }
                userListBody.innerHTML = skeletonHTML;
            };

            async function loadUsersAndRender() {
                showSkeletonLoader();
                const users = await apiCall('GET');
                if (users) {
                    renderUsers(users);
                } else {
                    userListBody.innerHTML = `<tr class="empty-state"><td colspan="6"><div class="empty-state-icon">📂</div><p>خطا در بارگذاری اطلاعات.</p></td></tr>`;
                }
            }

            function renderUsers(allUsers) {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const filteredUsers = searchTerm ? allUsers.filter(user =>
                    user.name.toLowerCase().includes(searchTerm) ||
                    user.username.toLowerCase().includes(searchTerm) ||
                    String(user.id).includes(searchTerm)
                ) : allUsers;

                userListBody.innerHTML = "";
                if (filteredUsers.length === 0) {
                    userListBody.innerHTML = `<tr class="empty-state"><td colspan="6"><div class="empty-state-icon">🤷</div><p>${searchTerm ? 'کاربری با این مشخصات یافت نشد.' : 'هنوز کاربری اضافه نشده است.'}</p></td></tr>`;
                    return;
                }
                filteredUsers.forEach(user => {
                    const row = document.createElement("tr");
                    row.dataset.user = JSON.stringify(user);
                    const isAdminBadge = user.is_admin ?
                        '<span class="badge badge-success">ادمین</span>' :
                        '<span class="badge badge-secondary">کاربر</span>';

                    row.innerHTML = `
                <td data-label="نام کامل" class="user-name">${user.name}</td>
                <td data-label="نام کاربری">${user.username}</td>
                <td data-label="شناسه">${user.id}</td>
                <td data-label="تاریخ شروع">${user.start_work || '-'}</td>
                <td data-label="وضعیت">${isAdminBadge}</td>
                <td data-label="عملیات" class="actions-cell">
                    <button class="icon-btn edit-btn">✏️</button>
                    <button class="icon-btn delete-btn">🗑️</button>
                </td>`;
                    row.querySelector('.edit-btn').addEventListener('click', handleEdit);
                    row.querySelector('.delete-btn').addEventListener('click', handleDelete);
                    userListBody.appendChild(row);
                });
            }

            // === مدیریت رویدادها ===
            document.getElementById("add-new-user-btn").addEventListener("click", () => {
                currentUserId = null;
                form.reset();
                document.getElementById("password").required = true;
                openDrawer("افزودن کاربر جدید");
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
                form.elements['is_admin'].checked = user.is_admin == 1;
                openDrawer("ویرایش کاربر");
            }

            async function handleDelete(e) {
                const user = JSON.parse(e.target.closest('tr').dataset.user);
                if (confirm(`آیا از حذف کاربر "${user.name}" مطمئن هستید؟`)) {
                    const result = await apiCall('POST', {
                        action: 'delete',
                        id: user.id
                    });
                    if (result && result.success) loadUsersAndRender();
                }
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
                };
                const result = await apiCall('POST', payload);
                if (result && result.success) {
                    closeDrawer();
                    loadUsersAndRender();
                }
            });

            searchInput.addEventListener('input', loadUsersAndRender);
            document.getElementById('close-drawer-btn').addEventListener('click', closeDrawer);
            drawer.addEventListener('click', (e) => {
                if (e.target === drawer) closeDrawer();
            });

            // بارگذاری اولیه
            loadUsersAndRender();
        });
    </script>
    <script src="/js/header.js"></script>
</body>

</html>
