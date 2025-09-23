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
            width: min(1200px, 100%);
            padding: clamp(1rem, 3vw, 2.5rem) clamp(1rem, 3vw, 2rem);
            margin-inline: auto;
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: clamp(1.3rem, 3vw, 1.8rem);
            margin-block-end: .5rem;
        }

        .page-subtitle {
            color: var(--secondary-text);
            font-weight: 400;
            font-size: clamp(.95rem, 2.2vw, 1rem);
            margin-block-end: 2.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
            max-width: 600px;
            margin-inline: auto;
        }

        .form-card {
            background: var(--card-bg);
            padding: 1.75rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .form-card h2 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        input[type="text"] {
            width: 100%;
            font-size: 1rem;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius);
            background: var(--card-bg);
            transition: border-color .2s, box-shadow .2s;
            margin-bottom: 1rem;
        }

        input[type="text"]:focus-visible {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 174, 112, .15);
        }

        .btn {
            padding: .8em 1.5em;
            font-size: .9rem;
            font-weight: 600;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn:hover {
            background-color: var(--primary-dark);
        }

        .table-container {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: right;
            border-bottom: 1px solid var(--border-color);
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

        .data-table tr:hover {
            background-color: var(--primary-light);
        }

        .status {
            padding: 5px 12px;
            border-radius: 12px;
            color: white;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
            display: inline-block;
        }

        .status-in-stock {
            background-color: #28a745;
        }

        .status-assigned {
            background-color: #17a2b8;
        }

        .action-button {
            padding: 6px 12px;
            font-size: 0.85rem;
            margin: 0 4px;
            font-weight: 500;
        }

        .btn-assign {
            background-color: #007bff;
        }

        .btn-assign:hover {
            background-color: #0056b3;
        }

        .btn-return {
            background-color: #ffc107;
            color: #333;
        }

        .btn-return:hover {
            background-color: #e0a800;
        }

        .btn-delete {
            background-color: #dc3545;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

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
        }

        .modal-content {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: var(--radius);
            width: 90%;
            max-width: 400px;
            box-shadow: var(--shadow-md);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h3 {
            font-size: 1.2rem;
            font-weight: 700;
        }

        .modal-close {
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            color: var(--secondary-text);
        }

        #expertSelect {
            width: 100%;
            padding: .8em 1.2em;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <h1 class="page-title">سامانه مدیریت اموال</h1>
        <p class="page-subtitle">اموال و تجهیزات مربوط به مرکز پشتیبانی را ثبت و مدیریت کنید.</p>
        <div class="form-grid">
            <div class="form-card">
                <h2>افزودن اموال جدید</h2>
                <form id="addAssetForm">
                    <input type="text" id="assetName" placeholder="نام کالا (مثلا: موس Logitech)" required>
                    <input type="text" id="assetSerial" placeholder="شماره سریال" required>
                    <button type="submit" class="btn">افزودن کالا</button>
                </form>
            </div>
        </div>
        <h2 class="page-title" style="font-size: 1.5rem; margin-bottom: 1rem;">لیست اموال</h2>
        <div class="table-container">
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
                <tbody></tbody>
            </table>
        </div>
    </main>
    <div id="assignModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>تخصیص به کارشناس</h3><span class="modal-close" id="closeModal">&times;</span>
            </div>
            <select id="expertSelect"></select>
            <button id="confirmAssignBtn" class="btn">تایید تخصیص</button>
        </div>
    </div>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const API_URL = 'api.php';
            const assetTableBody = document.querySelector('#assetTable tbody');
            const addAssetForm = document.getElementById('addAssetForm');
            const assignModal = document.getElementById('assignModal');
            const closeModal = document.getElementById('closeModal');
            const expertSelect = document.getElementById('expertSelect');
            const confirmAssignBtn = document.getElementById('confirmAssignBtn');
            let currentAssetId = null;

            function renderAssets(assets) {
                assetTableBody.innerHTML = '';
                if (!assets || assets.length === 0) {
                    const row = assetTableBody.insertRow();
                    const cell = row.insertCell();
                    cell.colSpan = 6;
                    cell.textContent = 'هیچ کالایی ثبت نشده است.';
                    cell.style.textAlign = 'center';
                    cell.style.padding = '2rem';
                    return;
                }

                assets.forEach(asset => {
                    const row = assetTableBody.insertRow();
                    const createCell = (text) => row.insertCell().textContent = text;

                    createCell(asset.id);
                    createCell(asset.name);
                    createCell(asset.serial_number);

                    const statusCell = row.insertCell();
                    const statusBadge = document.createElement('span');
                    statusBadge.className = 'status';
                    if (asset.status === 'In Stock') {
                        statusBadge.classList.add('status-in-stock');
                        statusBadge.textContent = 'موجود در انبار';
                    } else {
                        statusBadge.classList.add('status-assigned');
                        statusBadge.textContent = 'تحویل داده شده';
                    }
                    statusCell.appendChild(statusBadge);

                    createCell(asset.assigned_to_name || '---');

                    const actionsCell = row.insertCell();
                    if (asset.status === 'In Stock') {
                        const assignButton = document.createElement('button');
                        assignButton.textContent = 'تخصیص';
                        assignButton.className = 'btn action-button btn-assign';
                        assignButton.dataset.id = asset.id;
                        actionsCell.appendChild(assignButton);
                    } else {
                        const returnButton = document.createElement('button');
                        returnButton.textContent = 'بازگرداندن';
                        returnButton.className = 'btn action-button btn-return';
                        returnButton.dataset.id = asset.id;
                        actionsCell.appendChild(returnButton);
                    }

                    if (asset.status === 'In Stock') {
                        const deleteButton = document.createElement('button');
                        deleteButton.textContent = 'حذف';
                        deleteButton.className = 'btn action-button btn-delete';
                        deleteButton.dataset.id = asset.id;
                        actionsCell.appendChild(deleteButton);
                    }
                });
            }

            addAssetForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const name = document.getElementById('assetName').value.trim();
                const serial = document.getElementById('assetSerial').value.trim();
                if (!name || !serial) {
                    alert('نام کالا و شماره سریال نمی‌توانند خالی باشند.');
                    return;
                }
                const result = await apiPost('add_asset', {
                    name,
                    serial
                });
                if (result) {
                    addAssetForm.reset();
                    fetchAssets();
                }
            });
            assetTableBody.addEventListener('click', async (e) => {
                const target = e.target;
                if (!target.classList.contains('btn')) return;
                const assetId = target.dataset.id;
                if (target.classList.contains('btn-assign')) {
                    currentAssetId = assetId;
                    await openAssignModal();
                } else if (target.classList.contains('btn-return')) {
                    if (confirm('آیا از بازگرداندن این کالا به انبار مطمئن هستید؟')) {
                        const result = await apiPost('return_asset', {
                            asset_id: assetId
                        });
                        if (result) fetchAssets();
                    }
                } else if (target.classList.contains('btn-delete')) {
                    if (confirm(`آیا از حذف کامل کالای با کد ${assetId} اطمینان دارید؟ این عمل غیرقابل بازگشت است.`)) {
                        const result = await apiPost('delete_asset', {
                            asset_id: assetId
                        });
                        if (result) {
                            alert('کالا با موفقیت حذف شد.');
                            fetchAssets();
                        }
                    }
                }
            });
            async function openAssignModal() {
                try {
                    const response = await fetch(`${API_URL}?action=get_experts`);
                    if (response.status === 401) {
                        alert('دسترسی غیرمجاز. لطفا ابتدا وارد شوید.');
                        return;
                    }
                    if (!response.ok) {
                        throw new Error('پاسخی از سرور دریافت نشد.');
                    }
                    const experts = await response.json();
                    expertSelect.innerHTML = '<option value="">یک کارشناس را انتخاب کنید...</option>';
                    experts.forEach(expert => {
                        expertSelect.innerHTML += `<option value="${expert.username}">${expert.name}</option>`;
                    });
                    assignModal.style.display = 'flex';
                } catch (error) {
                    alert(`خطا در دریافت لیست کارشناسان: ${error.message}`);
                }
            }
            closeModal.onclick = () => assignModal.style.display = 'none';
            window.onclick = (e) => {
                if (e.target == assignModal) assignModal.style.display = 'none';
            };
            confirmAssignBtn.addEventListener('click', async () => {
                const selectedOption = expertSelect.options[expertSelect.selectedIndex];
                const username = selectedOption.value;
                const userName = selectedOption.text;
                if (!username) {
                    alert('لطفا یک کارشناس را انتخاب کنید.');
                    return;
                }
                const payload = {
                    asset_id: currentAssetId,
                    username: username,
                    user_name: userName
                };
                const result = await apiPost('assign_asset', payload);
                if (result) {
                    assignModal.style.display = 'none';
                    fetchAssets();
                }
            });
            async function fetchAssets() {
                try {
                    const response = await fetch(`${API_URL}?action=get_assets`);
                    if (!response.ok) throw new Error('Network response was not ok');
                    const assets = await response.json();
                    renderAssets(assets);
                } catch (error) {
                    assetTableBody.innerHTML = `<tr><td colspan="6" style="text-align:center; color: red;">خطا در بارگذاری اطلاعات: ${error.message}</td></tr>`;
                }
            }
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
                    alert(`خطا: ${error.message}`);
                    return null;
                }
            }
            fetchAssets();
        });
    </script>
</body>

</html>
