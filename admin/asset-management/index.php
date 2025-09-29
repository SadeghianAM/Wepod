<?php
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت اموال - وی هاب</title>
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

            /* New Colors for better UX feedback */
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --success-light: #e9f7eb;
            --info-light: #e8f6f8;
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
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Vazirmatn", system-ui;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--bg-color);
            color: var(--text-color);
        }

        header,
        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 2rem;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
        }

        header {
            min-height: 70px;
        }

        footer {
            min-height: 60px;
            font-size: .85rem;
        }

        header h1 {
            font-weight: 700;
            font-size: 1.2rem;
        }

        main {
            flex: 1 1 auto;
            width: min(1400px, 100%);
            padding: clamp(1rem, 3vw, 2.5rem) clamp(1rem, 3vw, 2rem);
            margin-inline: auto;
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: clamp(1.5rem, 3vw, 2rem);
            margin-block-end: .5rem;
        }

        .page-subtitle {
            color: var(--secondary-text);
            font-weight: 400;
            font-size: clamp(.95rem, 2.2vw, 1rem);
            margin-block-end: 2.5rem;
        }

        /* New Layout: Grid for form and table */
        .content-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem;
        }

        @media (min-width: 992px) {
            .content-layout {
                grid-template-columns: 380px 1fr;
                align-items: flex-start;
            }
        }

        .form-card {
            background: var(--card-bg);
            padding: 1.75rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            position: sticky;
            top: 2rem;
        }

        .form-card h2 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text-color);
        }

        /* Form group for better spacing and labels */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: var(--secondary-text);
        }

        input[type="text"],
        select {
            width: 100%;
            font-size: 1rem;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius);
            background: var(--card-bg);
            transition: border-color .2s, box-shadow .2s;
        }

        input[type="text"]:focus-visible,
        select:focus-visible {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 174, 112, .15);
        }

        .btn {
            padding: .8em 1.5em;
            font-size: .95rem;
            font-weight: 600;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5em;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn.btn-full-width {
            width: 100%;
        }

        .table-wrapper {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            overflow-x: auto;
        }

        .section-header {
            padding: 1rem 1.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section-header h2 {
            font-size: 1.25rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 1.25rem;
            text-align: right;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .data-table th {
            font-weight: 600;
            font-size: .9rem;
            color: var(--secondary-text);
            background-color: var(--bg-color);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background-color: var(--primary-light);
        }

        .status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            display: inline-block;
            white-space: nowrap;
        }

        .status-in-stock {
            background-color: var(--success-light);
            color: var(--success-color);
        }

        .status-assigned {
            background-color: var(--info-light);
            color: var(--info-color);
        }

        .actions-cell {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-start;
        }

        .action-button {
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 8px;
        }

        .btn-assign {
            background-color: #007bff;
        }

        .btn-assign:hover {
            background-color: #0056b3;
        }

        .btn-return {
            background-color: var(--warning-color);
            color: #333;
        }

        .btn-return:hover {
            background-color: #e0a800;
        }

        .btn-delete {
            background-color: var(--danger-color);
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.show {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: var(--radius);
            width: 90%;
            max-width: 450px;
            box-shadow: var(--shadow-md);
            transform: scale(0.95);
            transition: transform 0.3s ease;
        }

        .modal.show .modal-content {
            transform: scale(1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h3 {
            font-size: 1.3rem;
            font-weight: 700;
        }

        .modal-close {
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            color: var(--secondary-text);
            transition: color 0.2s;
        }

        .modal-close:hover {
            color: var(--text-color);
        }

        /* Toast Notification System */
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

        .toast-info {
            background-color: var(--info-color);
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
        <h1 class="page-title">سامانه مدیریت اموال</h1>
        <p class="page-subtitle">اموال و تجهیزات مربوط به مرکز پشتیبانی را ثبت و مدیریت کنید.</p>

        <div class="content-layout">
            <div class="form-container">
                <div class="form-card">
                    <h2>✨ افزودن اموال جدید</h2>
                    <form id="addAssetForm">
                        <div class="form-group">
                            <label for="assetName">نام کالا</label>
                            <input type="text" id="assetName" placeholder="مثلا: موس Logitech" required>
                        </div>
                        <div class="form-group">
                            <label for="assetSerial">شماره سریال</label>
                            <input type="text" id="assetSerial" placeholder="شماره منحصر به فرد کالا" required>
                        </div>
                        <button type="submit" class="btn btn-full-width">افزودن کالا</button>
                    </form>
                </div>
            </div>

            <div class="table-container-wrapper">
                <div class="table-wrapper">
                    <div class="section-header">
                        <h2>لیست اموال</h2>
                    </div>
                    <table class="data-table" id="assetTable">
                        <thead>
                            <tr>
                                <th>کد</th>
                                <th>نام کالا</th>
                                <th>شماره سریال</th>
                                <th>وضعیت</th>
                                <th>تحویل‌گیرنده</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div id="assignModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>تخصیص به کارشناس</h3>
                <span class="modal-close" id="closeModal">&times;</span>
            </div>
            <div class="form-group">
                <label for="expertSelect">انتخاب کارشناس</label>
                <select id="expertSelect">
                </select>
            </div>
            <button id="confirmAssignBtn" class="btn btn-full-width">تایید تخصیص</button>
        </div>
    </div>

    <div id="toast-container"></div>
    <div id="footer-placeholder"></div>

    <script src="/js/header.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const API_URL = '/admin/asset-management/asset-api.php';
            const assetTableBody = document.querySelector('#assetTable tbody');
            const addAssetForm = document.getElementById('addAssetForm');
            const assignModal = document.getElementById('assignModal');
            const closeModal = document.getElementById('closeModal');
            const expertSelect = document.getElementById('expertSelect');
            const confirmAssignBtn = document.getElementById('confirmAssignBtn');
            const toastContainer = document.getElementById('toast-container');
            let currentAssetId = null;

            // --- Toast Notification System ---
            function showToast(message, type = 'info', duration = 4000) {
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.textContent = message;
                toastContainer.appendChild(toast);

                setTimeout(() => toast.classList.add('show'), 10); // Trigger transition

                setTimeout(() => {
                    toast.classList.remove('show');
                    toast.addEventListener('transitionend', () => toast.remove());
                }, duration);
            }

            function showConfirmation(message, onConfirm) {
                const toast = document.createElement('div');
                toast.className = 'toast toast-confirm';

                toast.innerHTML = `
                    <div class="toast-message">${message}</div>
                    <div class="toast-buttons">
                        <button class="btn btn-delete" id="confirmAction">بله، مطمئنم</button>
                        <button class="btn" style="background-color: var(--secondary-text);" id="cancelAction">لغو</button>
                    </div>
                `;

                toast.querySelector('#confirmAction').onclick = () => {
                    onConfirm();
                    removeToast();
                };

                toast.querySelector('#cancelAction').onclick = () => removeToast();

                const removeToast = () => {
                    toast.classList.remove('show');
                    toast.addEventListener('transitionend', () => toast.remove());
                };

                toastContainer.appendChild(toast);
                setTimeout(() => toast.classList.add('show'), 10);
            }

            // --- Render Functions ---
            function showLoadingState() {
                assetTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 2rem;">
                            در حال بارگذاری اطلاعات...
                        </td>
                    </tr>`;
            }

            function renderAssets(assets) {
                assetTableBody.innerHTML = '';
                if (!assets || assets.length === 0) {
                    assetTableBody.innerHTML = `
                            <tr>
                                <td colspan="6" style="text-align:center; padding: 2rem;">
                                    هیچ کالایی ثبت نشده است.
                                </td>
                            </tr>`;
                    return;
                }

                assets.forEach(asset => {
                    const row = assetTableBody.insertRow();
                    row.insertCell().textContent = asset.id;
                    row.insertCell().textContent = asset.name;
                    row.insertCell().textContent = asset.serial_number;

                    const statusCell = row.insertCell();
                    const statusBadge = document.createElement('span');
                    statusBadge.className = 'status';
                    if (asset.status === 'In Stock') {
                        statusBadge.classList.add('status-in-stock');
                        statusBadge.textContent = '📦 موجود در انبار';
                    } else {
                        statusBadge.classList.add('status-assigned');
                        statusBadge.textContent = '🧑‍💼 تحویل داده شده';
                    }
                    statusCell.appendChild(statusBadge);

                    row.insertCell().textContent = asset.assigned_to_name || '---';

                    const actionsCell = row.insertCell();
                    actionsCell.className = 'actions-cell';
                    if (asset.status === 'In Stock') {
                        const assignButton = document.createElement('button');
                        assignButton.textContent = 'تخصیص';
                        assignButton.className = 'btn action-button btn-assign';
                        assignButton.dataset.id = asset.id;
                        actionsCell.appendChild(assignButton);

                        const deleteButton = document.createElement('button');
                        deleteButton.innerHTML = 'حذف 🗑️';
                        deleteButton.className = 'btn action-button btn-delete';
                        deleteButton.dataset.id = asset.id;
                        actionsCell.appendChild(deleteButton);
                    } else {
                        const returnButton = document.createElement('button');
                        returnButton.textContent = 'بازگرداندن';
                        returnButton.className = 'btn action-button btn-return';
                        returnButton.dataset.id = asset.id;
                        actionsCell.appendChild(returnButton);
                    }
                });
            }

            // --- API Calls ---
            async function apiPost(action, data) {
                try {
                    const response = await fetch(`${API_URL}?action=${action}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });
                    const responseData = await response.json();
                    if (!response.ok) {
                        throw new Error(responseData.error || 'خطایی در سرور رخ داد.');
                    }
                    return responseData;
                } catch (error) {
                    showToast(error.message, 'error');
                    return null;
                }
            }

            async function fetchAssets() {
                showLoadingState();
                try {
                    const response = await fetch(`${API_URL}?action=get_assets`);
                    if (!response.ok) throw new Error('پاسخی از سرور دریافت نشد.');
                    const assets = await response.json();
                    renderAssets(assets);
                } catch (error) {
                    assetTableBody.innerHTML = `<tr><td colspan="6" style="text-align:center; color: red;">خطا در بارگذاری اطلاعات: ${error.message}</td></tr>`;
                }
            }

            // --- Event Listeners ---
            addAssetForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const name = document.getElementById('assetName').value.trim();
                const serial = document.getElementById('assetSerial').value.trim();
                if (!name || !serial) {
                    showToast('نام کالا و شماره سریال نمی‌توانند خالی باشند.', 'error');
                    return;
                }
                const result = await apiPost('add_asset', {
                    name,
                    serial
                });
                if (result) {
                    showToast('کالا با موفقیت افزوده شد.', 'success');
                    addAssetForm.reset();
                    fetchAssets();
                }
            });

            assetTableBody.addEventListener('click', async (e) => {
                const target = e.target.closest('.btn');
                if (!target) return;

                const assetId = target.dataset.id;

                if (target.classList.contains('btn-assign')) {
                    currentAssetId = assetId;
                    await openAssignModal();
                } else if (target.classList.contains('btn-return')) {
                    showConfirmation('آیا از بازگرداندن این کالا به انبار مطمئن هستید؟', async () => {
                        const result = await apiPost('return_asset', {
                            asset_id: assetId
                        });
                        if (result) {
                            showToast('کالا با موفقیت به انبار بازگردانده شد.', 'info');
                            fetchAssets();
                        }
                    });
                } else if (target.classList.contains('btn-delete')) {
                    showConfirmation(`آیا از حذف کامل کالای با کد ${assetId} اطمینان دارید؟ این عمل غیرقابل بازگشت است.`, async () => {
                        const result = await apiPost('delete_asset', {
                            asset_id: assetId
                        });
                        if (result) {
                            showToast('کالا با موفقیت حذف شد.', 'success');
                            fetchAssets();
                        }
                    });
                }
            });

            // --- Modal Logic ---
            async function openAssignModal() {
                try {
                    const response = await fetch(`${API_URL}?action=get_experts`);
                    if (!response.ok) throw new Error('خطا در دریافت لیست کارشناسان.');

                    const experts = await response.json();
                    expertSelect.innerHTML = '<option value="">یک کارشناس را انتخاب کنید...</option>';

                    // ✅ *** CHANGE 1: Use expert.id for the option value ***
                    experts.forEach(expert => {
                        expertSelect.innerHTML += `<option value="${expert.id}">${expert.name}</option>`;
                    });

                    assignModal.classList.add('show');
                } catch (error) {
                    showToast(error.message, 'error');
                }
            }

            const closeTheModal = () => assignModal.classList.remove('show');
            closeModal.onclick = closeTheModal;
            window.onclick = (e) => {
                if (e.target == assignModal) closeTheModal();
            };

            confirmAssignBtn.addEventListener('click', async () => {
                const selectedOption = expertSelect.options[expertSelect.selectedIndex];

                // ✅ *** CHANGE 2.1: Get the user ID from the value ***
                const userId = selectedOption.value;

                if (!userId) {
                    showToast('لطفا یک کارشناس را انتخاب کنید.', 'error');
                    return;
                }

                // ✅ *** CHANGE 2.2: Create the correct payload for the new API ***
                const payload = {
                    asset_id: currentAssetId,
                    user_id: userId
                };

                const result = await apiPost('assign_asset', payload);
                if (result) {
                    showToast(`کالا به ${selectedOption.text} تخصیص داده شد.`, 'success');
                    closeTheModal();
                    fetchAssets();
                }
            });

            // Initial Load
            fetchAssets();
        });
    </script>
</body>

</html>
