<?php
// فایل نهایی: quiz_attempts.php (پنل ادمین)
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html'); // فقط ادمین دسترسی دارد
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت نتایج آزمون‌ها</title>
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

        .delete-btn:hover {
            color: var(--danger-color);
        }

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

        .table-container {
            overflow-x: auto;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
        }

        .results-table th,
        .results-table td {
            padding: 1rem 1.5rem;
            text-align: right;
            white-space: nowrap;
        }

        .results-table thead {
            background-color: #f8f9fa;
        }

        .results-table th {
            font-weight: 600;
            color: var(--secondary-text-color);
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .results-table tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
        }

        .results-table tbody tr:last-child {
            border-bottom: none;
        }

        .results-table tbody tr:hover {
            background-color: #f1f3f5;
        }

        .user-name {
            font-weight: 500;
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
            color: var(--secondary-text-color);
        }

        /* === استایل‌های مودال (Modal) === */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            visibility: hidden;
            opacity: 0;
            transition: visibility 0.3s, opacity 0.3s;
        }

        .modal-overlay.open {
            visibility: visible;
            opacity: 1;
        }

        .modal-content {
            background-color: var(--card-bg);
            padding: 1.5rem 2rem;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 700px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            transform: scale(0.95);
            transition: transform 0.3s;
        }

        .modal-overlay.open .modal-content {
            transform: scale(1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .modal-body {
            overflow-y: auto;
            padding: 0.5rem;
        }

        /* استایل لیست سوالات و پاسخ‌ها */
        .qa-list {
            list-style: none;
            padding: 0;
        }

        .qa-item {
            padding: 1rem;
            border-bottom: 1px dashed var(--border-color);
        }

        .qa-item:last-child {
            border-bottom: none;
        }

        .qa-question {
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .qa-answer {
            padding-right: 1.5rem;
            position: relative;
            font-size: 0.95rem;
        }

        .qa-answer::before {
            position: absolute;
            right: 0;
            top: 2px;
            font-weight: bold;
        }

        .qa-answer.correct {
            color: var(--success-color);
        }

        .qa-answer.correct::before {
            content: '✅';
        }

        .qa-answer.incorrect {
            color: var(--danger-color);
        }

        .qa-answer.incorrect::before {
            content: '❌';
        }

        .correct-answer-note {
            font-size: 0.85rem;
            color: var(--secondary-text-color);
            margin-top: 0.5rem;
            padding-right: 1.5rem;
        }

        @media (max-width: 768px) {
            .results-table thead {
                display: none;
            }

            .results-table tr {
                display: block;
                border: 1px solid var(--border-color);
                border-radius: var(--border-radius);
                margin-bottom: 1rem;
                padding: 1rem;
            }

            .results-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0;
                border-bottom: 1px dashed #eee;
            }

            .results-table td:last-child {
                border-bottom: none;
            }

            .results-table td::before {
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
            <h1 class="page-title">مدیریت نتایج آزمون‌ها</h1>
        </div>
        <div class="content-body">
            <div class="toolbar">
                <input type="text" id="searchInput" placeholder="جستجو بر اساس نام کاربر، نام کاربری یا عنوان آزمون...">
            </div>
            <div class="table-container">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>نام کامل کاربر</th>
                            <th>نام کاربری</th>
                            <th>عنوان آزمون</th>
                            <th>امتیاز</th>
                            <th>تاریخ تکمیل</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody id="attempts-list">
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <div id="footer-placeholder"></div>

    <div id="details-modal-overlay" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title" class="modal-title">جزئیات پاسخ‌ها</h2>
                <button id="close-modal-btn" class="icon-btn">&times;</button>
            </div>
            <div id="modal-body" class="modal-body">
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let allAttempts = [];
            const attemptListBody = document.getElementById("attempts-list");
            const searchInput = document.getElementById("searchInput");
            const apiEndpoint = 'attempts_api.php';
            const modalOverlay = document.getElementById('details-modal-overlay');
            const modalTitle = document.getElementById('modal-title');
            const modalBody = document.getElementById('modal-body');

            // === ابزار ارتباط با API ===
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

            // === توابع رندر و نمایش ===
            const showSkeletonLoader = () => {
                let skeletonHTML = '';
                for (let i = 0; i < 8; i++) {
                    skeletonHTML += `
                <tr class="skeleton-loader">
                    <td><div class="skeleton-item" style="width: 150px;"></div></td>
                    <td><div class="skeleton-item" style="width: 120px;"></div></td>
                    <td><div class="skeleton-item" style="width: 200px;"></div></td>
                    <td><div class="skeleton-item" style="width: 50px;"></div></td>
                    <td><div class="skeleton-item" style="width: 140px;"></div></td>
                    <td><div class="skeleton-item" style="width: 70px;"></div></td>
                </tr>`;
                }
                attemptListBody.innerHTML = skeletonHTML;
            };

            const renderAttempts = () => {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const filteredAttempts = searchTerm ? allAttempts.filter(attempt =>
                    attempt.user_fullname.toLowerCase().includes(searchTerm) ||
                    attempt.username.toLowerCase().includes(searchTerm) ||
                    attempt.quiz_title.toLowerCase().includes(searchTerm)
                ) : allAttempts;

                attemptListBody.innerHTML = "";
                if (filteredAttempts.length === 0) {
                    attemptListBody.innerHTML = `<tr class="empty-state"><td colspan="6"><div class="empty-state-icon">🤷</div><p>${searchTerm ? 'نتیجه‌ای با این مشخصات یافت نشد.' : 'هنوز نتیجه‌ای ثبت نشده است.'}</p></td></tr>`;
                    return;
                }

                filteredAttempts.forEach(attempt => {
                    const row = document.createElement("tr");
                    row.dataset.attemptId = attempt.attempt_id;
                    const formattedDate = attempt.end_time ? new Date(attempt.end_time).toLocaleString('fa-IR', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : 'N/A';

                    row.innerHTML = `
                        <td data-label="نام کامل" class="user-name">${escapeHTML(attempt.user_fullname)}</td>
                        <td data-label="نام کاربری">${escapeHTML(attempt.username)}</td>
                        <td data-label="عنوان آزمون">${escapeHTML(attempt.quiz_title)}</td>
                        <td data-label="امتیاز">${attempt.score}</td>
                        <td data-label="تاریخ تکمیل">${formattedDate}</td>
                        <td data-label="عملیات">
                            <button class="icon-btn details-btn">📋</button>
                            <button class="icon-btn delete-btn">🗑️</button>
                        </td>`;

                    row.querySelector('.details-btn').addEventListener('click', handleDetails);
                    row.querySelector('.delete-btn').addEventListener('click', handleDelete);
                    attemptListBody.appendChild(row);
                });
            };

            function escapeHTML(str) {
                const p = document.createElement("p");
                p.textContent = str;
                return p.innerHTML;
            }

            // === توابع مدیریت مودال ===
            const openModal = () => modalOverlay.classList.add('open');
            const closeModal = () => modalOverlay.classList.remove('open');

            // === مدیریت رویدادها ===
            async function handleDetails(e) {
                const row = e.target.closest('tr');
                const attemptId = row.dataset.attemptId;

                openModal();
                modalBody.innerHTML = '<p>در حال بارگذاری جزئیات...</p>';
                modalTitle.textContent = 'جزئیات پاسخ‌ها';

                const result = await apiCall('POST', {
                    action: 'get_details',
                    id: attemptId
                });

                if (result && result.success) {
                    modalTitle.textContent = `پاسخ‌های "${result.details.user_fullname}" در آزمون "${result.details.quiz_title}"`;
                    let contentHTML = '<ul class="qa-list">';
                    if (result.answers.length === 0) {
                        contentHTML += '<p>هیچ پاسخی برای این آزمون ثبت نشده است.</p>';
                    } else {
                        result.answers.forEach(item => {
                            const answerClass = item.is_correct ? 'correct' : 'incorrect';
                            contentHTML += `
                                <li class="qa-item">
                                    <p class="qa-question">${escapeHTML(item.question_text)}</p>
                                    <p class="qa-answer ${answerClass}">
                                        پاسخ شما: ${escapeHTML(item.user_answer_text)}
                                    </p>`;
                            if (!item.is_correct) {
                                contentHTML += `<p class="correct-answer-note">پاسخ صحیح: ${escapeHTML(item.correct_answer_text)}</p>`;
                            }
                            contentHTML += `</li>`;
                        });
                    }
                    contentHTML += '</ul>';
                    modalBody.innerHTML = contentHTML;
                } else {
                    modalBody.innerHTML = `<p>خطا در دریافت اطلاعات: ${result ? result.message : 'ارتباط برقرار نشد.'}</p>`;
                }
            }

            async function handleDelete(e) {
                const row = e.target.closest('tr');
                const attemptId = row.dataset.attemptId;
                const confirmationMessage = 'آیا مطمئن هستید؟ با حذف این تاریخچه، کاربر می‌تواند مجدداً در این آزمون شرکت کند و امتیاز کسب شده از مجموع امتیازات او کسر خواهد شد.';

                if (confirm(confirmationMessage)) {
                    const result = await apiCall('POST', {
                        action: 'delete',
                        id: attemptId
                    });
                    if (result && result.success) {
                        row.style.transition = 'opacity 0.5s ease';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            loadAttemptsAndRender();
                        }, 500);
                    }
                }
            }

            async function loadAttemptsAndRender() {
                showSkeletonLoader();
                const attemptsData = await apiCall('GET');
                if (attemptsData) {
                    allAttempts = attemptsData;
                    renderAttempts();
                } else {
                    attemptListBody.innerHTML = `<tr class="empty-state"><td colspan="6"><div class="empty-state-icon">📂</div><p>خطا در بارگذاری اطلاعات.</p></td></tr>`;
                }
            }

            // === اتصال رویدادها و بارگذاری اولیه ===
            document.getElementById('close-modal-btn').addEventListener('click', closeModal);
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) closeModal();
            });

            searchInput.addEventListener('input', renderAttempts);
            loadAttemptsAndRender();
        });
    </script>
    <script src="/js/header.js"></script>
</body>

</html>
