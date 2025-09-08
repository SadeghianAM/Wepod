<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت کاربران</title>
    <style>
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --primary-light: #e6f7f2;
            --bg-color: #f7f9fa;
            --text-color: #1a1a1a;
            --secondary-text-color: #555;
            --card-bg: #ffffff;
            --header-text: #ffffff;
            --border-color: #e9e9e9;
            --danger-color: #ef4444;
            --danger-light: #fef2f2;
            --border-radius: 0.75rem;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-sm: 0 2px 6px rgba(0, 120, 80, .06);
            --footer-h: 60px;
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        main {
            padding: 2rem;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            flex-grow: 1;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: #333;
        }

        .btn {
            padding: 0.65rem 1.25rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 15px -5px var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .content-body {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
        }

        .toolbar {
            margin-bottom: 1.5rem;
        }

        .search-wrapper {
            position: relative;
        }

        #searchInput {
            width: 100%;
            padding: 0.8rem 2.5rem 0.8rem 1rem;
            font-size: 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
        }

        .search-wrapper::after {
            content: '🔍';
            position: absolute;
            top: 50%;
            right: 0.8rem;
            transform: translateY(-50%);
            opacity: 0.5;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th,
        .users-table td {
            padding: 1rem;
            text-align: right;
            border-bottom: 1px solid var(--border-color);
        }

        .users-table th {
            font-weight: 600;
            color: var(--secondary-text-color);
            background-color: #f9fafb;
        }

        .users-table tr:last-child td {
            border-bottom: none;
        }

        .users-table tr:hover {
            background-color: var(--bg-color);
        }

        .actions-cell {
            display: flex;
            gap: 0.75rem;
        }

        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: var(--secondary-text-color);
            position: relative;
            padding: 0.25rem;
        }

        .icon-btn:hover {
            color: var(--text-color);
        }

        .icon-btn::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s;
        }

        .icon-btn:hover::after {
            opacity: 1;
            visibility: visible;
        }

        .empty-row td {
            text-align: center;
            padding: 3rem;
            color: var(--secondary-text-color);
        }

        .empty-row:hover {
            background: none;
        }

        .empty-row .icon {
            font-size: 3rem;
            display: block;
            margin-bottom: 1rem;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            padding-top: 5vh;
        }

        .modal-content {
            background-color: var(--card-bg);
            margin: 0 auto;
            padding: 2rem;
            border: none;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .close-button {
            color: #aaa;
            position: absolute;
            left: 1rem;
            top: 1rem;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
            transition: color 0.2s;
        }

        .close-button:hover {
            color: #333;
        }

        .modal-content label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .modal-content input[type="text"],
        .modal-content input[type="password"],
        .modal-content input[type="number"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1.25rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
        }

        .modal-content .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        #save-item-btn .spinner {
            display: none;
            width: 1em;
            height: 1em;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        #save-item-btn.loading .spinner {
            display: inline-block;
        }

        #save-item-btn.loading .btn-text {
            display: none;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* --- CHANGED: New Style for the Back Link --- */
        .back-link {
            display: inline-block;
            /* Changed from block */
            margin-top: 2.5rem;
            /* Increased margin */
            text-align: center;
            text-decoration: none;
            padding: 0.6rem 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            color: var(--secondary-text-color);
            background-color: var(--card-bg);
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        .back-link:hover {
            background-color: var(--bg-color);
            color: var(--primary-dark);
            border-color: #ccc;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        #notification-container {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            color: white;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.3s forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .toast.success {
            background-color: var(--primary-color);
        }

        .toast.error {
            background-color: var(--danger-color);
        }

        .toast.confirm {
            background-color: #333;
            display: flex;
            flex-direction: column;
        }

        .toast.confirm .confirm-buttons {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }

        .toast.confirm button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        /* این کد جدید را جایگزین کنید */
        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 10;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
            min-height: var(--footer-h);
            font-size: .85rem;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="content-header">
            <h1 class="page-title">مدیریت کاربران</h1>
            <button id="add-new-item-btn" class="btn btn-primary"><span>➕</span><span class="btn-text">افزودن کاربر</span></button>
        </div>
        <div class="content-body">
            <div class="toolbar">
                <div class="search-wrapper">
                    <input type="text" id="searchInput" placeholder="جستجو بر اساس نام، نام کاربری یا شناسه...">
                </div>
            </div>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>نام کامل</th>
                        <th>نام کاربری</th>
                        <th>شناسه</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody id="item-list"></tbody>
            </table>
        </div>
        <div style="text-align: center;"> <a href="/admin/index.php" class="back-link">بازگشت به پنل مدیریت</a>
        </div>
    </main>
    <div id="itemModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2 id="modalTitle"></h2>
            <form id="itemForm" autocomplete="off">
                <label for="userId">شناسه (ID):</label><input type="number" id="userIdInput" name="id" required />
                <label for="name">نام کامل:</label><input type="text" id="name" name="name" required />
                <label for="username">نام کاربری:</label><input type="text" id="username" name="username" required />
                <label for="password">رمز عبور:</label><input type="password" id="password" placeholder="(برای کاربر جدید یا تغییر رمز)" />
                <div class="button-group">
                    <button type="button" class="btn" id="cancel-edit-btn">لغو</button>
                    <button type="submit" class="btn btn-primary" id="save-item-btn"><span class="spinner"></span><span class="btn-text">ذخیره</span></button>
                </div>
            </form>
        </div>
    </div>
    <div id="notification-container"></div>
    <div id="footer-placeholder"></div>
    <script>
        // All JavaScript code remains the same as the previous version.
        document.addEventListener("DOMContentLoaded", () => {
            let usersData = [];
            let currentUserId = null;
            const itemListBody = document.getElementById("item-list");
            const modal = document.getElementById("itemModal");
            const searchInput = document.getElementById("searchInput");
            const saveBtn = document.getElementById('save-item-btn');
            const notificationContainer = document.getElementById('notification-container');

            function showNotification(message, type = 'success', duration = 4000) {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.textContent = message;
                notificationContainer.appendChild(toast);
                setTimeout(() => toast.remove(), duration);
            }

            function showConfirmation(message) {
                return new Promise((resolve) => {
                    const toast = document.createElement('div');
                    toast.className = 'toast confirm';
                    toast.innerHTML = `<p>${message}</p><div class="confirm-buttons"><button class="yes">بله</button><button class="no">خیر</button></div>`;
                    notificationContainer.appendChild(toast);
                    toast.querySelector('.yes').onclick = () => {
                        resolve(true);
                        toast.remove();
                    };
                    toast.querySelector('.no').onclick = () => {
                        resolve(false);
                        toast.remove();
                    };
                });
            }
            async function loadUsers() {
                try {
                    const res = await fetch('/php/get-users.php');
                    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                    usersData = await res.json();
                    renderItems();
                } catch (err) {
                    showNotification('خطا در بارگذاری کاربران', 'error');
                }
            }
            async function saveDataToServer() {
                try {
                    const response = await fetch("/php/save-users.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(usersData)
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) throw new Error(result.message);
                    return true;
                } catch (error) {
                    showNotification(error.message || 'خطا در ذخیره اطلاعات', 'error');
                    return false;
                }
            }

            function renderItems() {
                itemListBody.innerHTML = "";
                const searchTerm = searchInput.value.toLowerCase().trim();
                const filteredUsers = usersData.filter(user =>
                    user.name.toLowerCase().includes(searchTerm) ||
                    user.username.toLowerCase().includes(searchTerm) ||
                    String(user.id).includes(searchTerm)
                );
                if (filteredUsers.length === 0) {
                    itemListBody.innerHTML = `<tr class="empty-row"><td colspan="4"><span class="icon">📂</span>${searchTerm ? 'کاربری یافت نشد.' : 'هنوز کاربری اضافه نشده است.'}</td></tr>`;
                    return;
                }
                filteredUsers.forEach(user => {
                    const row = document.createElement("tr");
                    row.dataset.userId = user.id;
                    row.innerHTML = `<td>${user.name}</td><td>${user.username}</td><td>${user.id}</td><td class="actions-cell"><button class="icon-btn edit-btn" data-tooltip="ویرایش">✏️</button><button class="icon-btn delete-btn" data-tooltip="حذف">🗑️</button></td>`;
                    row.querySelector('.edit-btn').addEventListener('click', handleEdit);
                    row.querySelector('.delete-btn').addEventListener('click', handleDelete);
                    itemListBody.appendChild(row);
                });
            }
            document.getElementById("add-new-item-btn").addEventListener("click", () => {
                currentUserId = null;
                document.getElementById('itemForm').reset();
                document.getElementById('modalTitle').textContent = "افزودن کاربر جدید";
                const nextId = usersData.length > 0 ? Math.max(...usersData.map(u => u.id)) + 1 : 1;
                document.getElementById("userIdInput").value = nextId;
                document.getElementById("password").required = true;
                modal.style.display = "block";
            });

            function handleEdit(e) {
                const row = e.target.closest('tr');
                const userId = parseInt(row.dataset.userId);
                const user = usersData.find(u => u.id === userId);
                if (user) {
                    currentUserId = userId;
                    document.getElementById('modalTitle').textContent = "ویرایش کاربر";
                    document.getElementById("userIdInput").value = user.id;
                    document.getElementById("name").value = user.name;
                    document.getElementById("username").value = user.username;
                    document.getElementById("password").value = "";
                    document.getElementById("password").required = false;
                    modal.style.display = "block";
                }
            }
            async function handleDelete(e) {
                const row = e.target.closest('tr');
                const userId = parseInt(row.dataset.userId);
                const user = usersData.find(u => u.id === userId);
                if (user) {
                    const confirmed = await showConfirmation(`آیا از حذف کاربر "${user.name}" مطمئن هستید؟`);
                    if (confirmed) {
                        usersData = usersData.filter(u => u.id !== userId);
                        if (await saveDataToServer()) {
                            showNotification('کاربر با موفقیت حذف شد.');
                            renderItems();
                        }
                    }
                }
            }
            document.getElementById("itemForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                const idInput = document.getElementById("userIdInput");
                const newId = parseInt(idInput.value);
                const name = document.getElementById("name").value.trim();
                const username = document.getElementById("username").value.trim();
                const password = document.getElementById("password").value;
                const nameRegex = /^[\u0600-\u06FF\s\u200C]+$/u;
                if (!nameRegex.test(name)) {
                    showNotification('نام کامل فقط میتواند شامل حروف فارسی، فاصله و نیم‌فاصله باشد.', 'error');
                    return;
                }
                const usernameRegex = /^[a-zA-Z0-9._-]+$/;
                if (!usernameRegex.test(username)) {
                    showNotification('نام کاربری فقط میتواند شامل حروف انگلیسی، عدد و کاراکترهای . _ - باشد.', 'error');
                    return;
                }
                if (isNaN(newId)) {
                    showNotification('شناسه باید یک عدد باشد.', 'error');
                    return;
                }
                const isDuplicate = usersData.some(user => user.id === newId && user.id !== currentUserId);
                if (isDuplicate) {
                    showNotification(`شناسه ${newId} تکراری است و توسط کاربر دیگری استفاده شده.`, 'error');
                    return;
                }
                saveBtn.classList.add('loading');
                saveBtn.disabled = true;
                if (currentUserId !== null) {
                    const user = usersData.find(u => u.id === currentUserId);
                    user.id = newId;
                    user.name = name;
                    user.username = username;
                    if (password) user.password = password;
                } else {
                    usersData.push({
                        id: newId,
                        name,
                        username,
                        password
                    });
                }
                if (await saveDataToServer()) {
                    showNotification(currentUserId !== null ? 'کاربر ویرایش شد.' : 'کاربر جدید اضافه شد.');
                    modal.style.display = "none";
                    renderItems();
                }
                saveBtn.classList.remove('loading');
                saveBtn.disabled = false;
            });
            searchInput.addEventListener('input', renderItems);
            document.querySelector(".close-button").onclick = () => modal.style.display = "none";
            document.getElementById("cancel-edit-btn").onclick = () => modal.style.display = "none";
            window.onclick = (e) => {
                if (e.target == modal) modal.style.display = "none";
            };
            loadUsers();
        });
    </script>
    <script src="/js/header.js"></script>
</body>

</html>
