<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');

// Load existing report data
$jsonFile = __DIR__ . '/../data/reports.json';
$existingData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
if (!is_array($existingData)) $existingData = [];

// Load users.json to map agent IDs to names
$usersFile = __DIR__ . '/../data/users.json';
$usersData = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
if (!is_array($usersData)) $usersData = [];

$agentNameMap = [];
foreach ($usersData as $user) {
    if (isset($user['id']) && isset($user['name'])) {
        $agentNameMap[$user['id']] = $user['name'];
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت و به‌روزرسانی گزارش‌ها</title>
    <style>
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --primary-light: #e6f7f2;
            --danger-color: #dc3545;
            --danger-dark: #c82333;
            --danger-light: #f8d7da;
            --info-color: #007bff;
            --info-dark: #0056b3;
            --info-light: #e7f5ff;
            --bg-color: #f7f9fa;
            --text-color: #1a1a1a;
            --secondary-text-color: #555;
            --card-bg: #ffffff;
            --header-text: #ffffff;
            --shadow-light: rgba(0, 120, 80, 0.06);
            --shadow-medium: rgba(0, 120, 80, 0.12);
            --border-radius: 0.75rem;
            --border-color: #e9e9e9;
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

        header h1 {
            font-size: 1.2rem;
            font-weight: 700;
        }

        footer {
            height: 60px;
            font-size: 0.85rem;
            margin-top: auto;
        }

        main {
            padding: 2.5rem 2rem;
            max-width: 1600px;
            width: 100%;
            margin: 0 auto;
            flex-grow: 1;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .page-subtitle {
            font-size: 1rem;
            font-weight: 400;
            color: var(--secondary-text-color);
            margin-bottom: 2.5rem;
            text-align: center;
        }

        .page-layout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2.5rem;
            align-items: start;
        }

        .form-card,
        .management-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 4px 20px var(--shadow-light);
            border: 1px solid var(--border-color);
        }

        .card-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .management-card .card-title {
            color: var(--info-dark);
        }

        .step {
            margin-bottom: 2rem;
        }

        .step-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            background-color: #fcfdff;
            transition: border-color 0.2s, box-shadow 0.2s;
            min-height: 250px;
            direction: ltr;
            text-align: left;
        }

        textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 174, 112, 0.15);
            outline: none;
        }

        .button-group {
            display: flex;
            gap: 1rem;
        }

        .button-group button {
            width: 100%;
        }

        button {
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-primary {
            background-color: var(--primary-color);
        }

        .btn-primary:hover:not(:disabled) {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: var(--info-color);
        }

        .btn-secondary:hover:not(:disabled) {
            background-color: var(--info-dark);
        }

        .btn-danger {
            background-color: var(--danger-color);
        }

        .btn-danger:hover:not(:disabled) {
            background-color: var(--danger-dark);
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            font-weight: 500;
            display: none;
            margin-top: 1.5rem;
        }

        .alert.success {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            border: 1px solid var(--primary-color);
        }

        .alert.error {
            background-color: var(--danger-light);
            color: var(--danger-dark);
            border: 1px solid var(--danger-color);
        }

        .instructions-box {
            background-color: var(--info-light);
            border-right: 4px solid var(--info-color);
            border-radius: 0.5rem;
            padding: 1rem;
            font-size: 0.9rem;
            color: #333;
            line-height: 1.7;
            margin-bottom: 1rem;
        }

        #data-record-display {
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 1rem;
            background-color: #fcfdff;
            min-height: 100px;
        }

        #data-record-display .placeholder-text {
            color: var(--secondary-text-color);
            text-align: center;
            padding-top: 1.5rem;
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
        }

        .modal-content {
            background-color: var(--card-bg);
            margin: 5% auto;
            border: none;
            width: 90%;
            max-width: 1200px;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
            background-color: var(--bg-color);
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.3rem;
            color: var(--primary-dark);
        }

        .close-button {
            color: #aaa;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close-button:hover {
            color: black;
        }

        .modal-body {
            padding: 1.5rem;
        }

        #preview-summary {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-align: center;
        }

        #preview-summary.valid {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }

        #preview-summary.invalid {
            background-color: var(--danger-light);
            color: var(--danger-dark);
        }

        .preview-table-container {
            max-height: 60vh;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th,
        td {
            border: 1px solid var(--border-color);
            padding: 10px 14px;
            text-align: left;
            direction: ltr;
        }

        th {
            background-color: #f2f2f2;
            position: sticky;
            top: 0;
            font-weight: 600;
        }

        tr.valid-row {
            background-color: #f0fff9;
        }

        tr.invalid-row {
            background-color: #fff5f6;
        }

        tr.invalid-row td:last-child {
            color: var(--danger-dark);
            font-weight: 500;
        }

        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
        }

        @media (max-width: 1024px) {
            .page-layout-grid {
                grid-template-columns: 1fr;
            }

            .management-card {
                margin-top: 2.5rem;
            }
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <h1 class="page-title">مدیریت و به‌روزرسانی گزارش‌ها</h1>
        <p class="page-subtitle">داده‌های گزارش‌های روزانه را به صورت یکپارچه وارد کرده یا داده‌های موجود را مدیریت کنید.</p>

        <div class="page-layout-grid">
            <div class="form-card">
                <div class="card-header">
                    <h2 class="card-title">بارگذاری گزارش جدید</h2>
                </div>

                <form id="reportForm">
                    <div class="step">
                        <h3 class="step-title">۱. راهنمای ستون‌ها</h3>
                        <div class="instructions-box">
                            ℹ️ لطفاً داده‌ها را دقیقاً با ۲۱ ستون مطابق فرمت زیر از اکسل کپی کنید:<br>
                            کد اپراتور - نام - تاریخ - تماس ورودی - مجموع مکالمه (ورودی) - میانگین مکالمه (ورودی) - بیشترین مکالمه (ورودی) - تعداد امتیاز - میانگین امتیاز - مدت حضور - استراحت - بی پاسخ - تماس خروجی - میانگین مکالمه (خروجی) - تیکت - چت - فمس - جیرا - امتیاز (۱) - مکالمات بالای ۵ دقیقه - عدم ثبت دلیل تماس
                        </div>
                    </div>

                    <div class="step">
                        <h3 class="step-title">۲. جای‌گذاری داده‌ها</h3>
                        <textarea id="excel_data" name="excel_data" required placeholder="داده‌های کپی شده از اکسل را اینجا جای‌گذاری کنید..."></textarea>
                    </div>

                    <div class="step">
                        <h3 class="step-title">۳. پیش‌نمایش و ذخیره</h3>
                        <div class="button-group">
                            <button type="button" id="previewBtn" class="btn-secondary">🔍 پیش‌نمایش و اعتبارسنجی</button>
                            <button type="submit" id="submitBtn" class="btn-primary" disabled>💾 ذخیره تغییرات</button>
                        </div>
                    </div>
                </form>
                <div id="response" class="alert"></div>
            </div>

            <div class="management-card">
                <div class="card-header">
                    <h2 class="card-title">مدیریت داده‌های موجود</h2>
                </div>

                <div class="step">
                    <h3 class="step-title">۱. رکورد مورد نظر را انتخاب کنید</h3>
                    <div class="button-group">
                        <select id="agent_select">
                            <option value="">ابتدا یک کارشناس انتخاب کنید</option>
                            <?php
                            $agentIds = array_keys($existingData);
                            sort($agentIds, SORT_NUMERIC);
                            foreach ($agentIds as $agentId) {
                                $agentName = isset($agentNameMap[$agentId]) ? htmlspecialchars($agentNameMap[$agentId]) : "کارشناس {$agentId}";
                                echo "<option value='{$agentId}'>{$agentName}</option>";
                            }
                            ?>
                        </select>
                        <select id="date_select" disabled>
                            <option value="">ابتدا کارشناس را انتخاب کنید</option>
                        </select>
                    </div>
                </div>

                <div class="step">
                    <h3 class="step-title">۲. رکورد را حذف کنید</h3>
                    <div id="data-record-display">
                        <p class="placeholder-text">ابتدا کارشناس و تاریخ را انتخاب کنید تا رکورد نمایش داده شود.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="previewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>پیش‌نمایش داده‌ها</h2>
                <span class="close-button">&times;</span>
            </div>
            <div class="modal-body">
                <div id="preview-summary"></div>
                <div class="preview-table-container">
                    <table id="preview-table">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="footer-placeholder"></div>

    <script src="/js/header.js"></script>
    <script>
        const existingData = <?php echo json_encode($existingData); ?>;
        const agentNameMap = <?php echo json_encode($agentNameMap); ?>;

        const excelDataTextarea = document.getElementById("excel_data"),
            reportForm = document.getElementById("reportForm"),
            responseDiv = document.getElementById("response"),
            submitButton = document.getElementById("submitBtn"),
            previewButton = document.getElementById("previewBtn"),
            previewModal = document.getElementById("previewModal"),
            closeModal = document.querySelector(".close-button"),
            previewSummaryDiv = document.getElementById("preview-summary"),
            previewTableBody = document.querySelector("#preview-table tbody"),
            previewTableHeader = document.querySelector("#preview-table thead"),
            agentSelect = document.getElementById("agent_select"),
            dateSelect = document.getElementById("date_select"),
            dataRecordDisplay = document.getElementById("data-record-display");

        const UNIFIED_HEADERS = [
            'کد اپراتور', 'نام', 'تاریخ', 'تماس ورودی', 'مجموع مکالمه (ورودی)', 'میانگین مکالمه (ورودی)', 'بیشترین مکالمه (ورودی)',
            'تعداد امتیاز', 'میانگین امتیاز', 'مدت حضور', 'استراحت', 'بی پاسخ', 'تماس خروجی', 'میانگین مکالمه (خروجی)', 'تیکت',
            'چت', 'فمس', 'جیرا', 'امتیاز (۱)', 'مکالمات بالای ۵ دقیقه', 'عدم ثبت دلیل تماس'
        ];

        previewButton.addEventListener("click", () => {
            const pastedData = excelDataTextarea.value.trim();
            if (!pastedData) return alert("لطفا محتوای گزارش را وارد کنید.");

            const lines = pastedData.split("\n");
            let validRows = 0,
                invalidRows = 0;
            let tableHeaderHTML = "<tr>";
            UNIFIED_HEADERS.forEach(h => tableHeaderHTML += `<th>${h}</th>`);
            tableHeaderHTML += "<th>وضعیت</th></tr>";
            previewTableHeader.innerHTML = tableHeaderHTML;

            let tableBodyHTML = "";
            lines.forEach(line => {
                if (!line.trim()) return;
                const columns = line.split(/\t+/);
                let isValid = false;
                let statusMsg = "تعداد ستون‌ها نامعتبر است.";

                if (columns.length >= 21) {
                    const agentId = columns[0].trim();
                    const date = columns[2].trim();
                    if (/^\d+$/.test(agentId) && /^\d{4}\/\d{2}\/\d{2}$/.test(date)) {
                        isValid = true;
                        statusMsg = "✅ معتبر";
                    } else {
                        statusMsg = "❌ کد اپراتور یا فرمت تاریخ اشتباه است.";
                    }
                }

                tableBodyHTML += `<tr class="${isValid ? "valid-row" : "invalid-row"}">`;
                columns.forEach(col => tableBodyHTML += `<td>${col}</td>`);
                tableBodyHTML += `<td>${statusMsg}</td></tr>`;

                isValid ? validRows++ : invalidRows++;
            });

            previewTableBody.innerHTML = tableBodyHTML;
            previewSummaryDiv.innerHTML = `تعداد ردیف‌های معتبر: ${validRows} <br> تعداد ردیف‌های نامعتبر: ${invalidRows}`;

            if (invalidRows === 0 && validRows > 0) {
                previewSummaryDiv.className = "valid";
                submitButton.disabled = false;
            } else {
                previewSummaryDiv.className = "invalid";
                submitButton.disabled = true;
            }
            previewModal.style.display = "block";
        });

        closeModal.onclick = () => previewModal.style.display = "none";
        window.onclick = e => {
            if (e.target == previewModal) previewModal.style.display = "none";
        };

        reportForm.addEventListener("submit", async function(e) {
            e.preventDefault();
            const formData = new FormData(reportForm);
            responseDiv.style.display = "none";
            submitButton.disabled = true;
            submitButton.innerHTML = "در حال ذخیره...";
            try {
                const response = await fetch("/php/process_reports.php", {
                    method: "POST",
                    body: formData
                });
                if (!response.ok) throw new Error(`خطای سرور: ${response.statusText}`);
                const result = await response.json();
                responseDiv.textContent = result.message;
                responseDiv.className = result.success ? "alert success" : "alert error";
                responseDiv.style.display = "block";
                if (result.success) {
                    setTimeout(() => window.location.reload(), 2000);
                }
            } catch (error) {
                responseDiv.textContent = `یک خطای غیرمنتظره رخ داد: ${error.message}`;
                responseDiv.className = "alert error";
                responseDiv.style.display = "block";
            } finally {
                submitButton.innerHTML = "💾 ذخیره تغییرات";
            }
        });

        agentSelect.addEventListener("change", () => {
            const agentId = agentSelect.value;
            dateSelect.innerHTML = '<option value="">...بارگذاری تاریخ‌ها</option>';
            dateSelect.disabled = true;
            dataRecordDisplay.innerHTML = '<p class="placeholder-text">ابتدا کارشناس و تاریخ را انتخاب کنید...</p>';

            if (agentId && existingData[agentId]) {
                const dates = Object.keys(existingData[agentId]).sort().reverse();
                let optionsHTML = '<option value="">یک تاریخ انتخاب کنید</option>';
                dates.forEach(date => {
                    const jalaliDate = new Date(date).toLocaleDateString("fa-IR", {
                        year: "numeric",
                        month: "long",
                        day: "numeric"
                    });
                    optionsHTML += `<option value="${date}">${jalaliDate} (${date})</option>`;
                });
                dateSelect.innerHTML = optionsHTML;
                dateSelect.disabled = false;
            } else {
                dateSelect.innerHTML = '<option value="">ابتدا کارشناس را انتخاب کنید</option>';
            }
        });

        const formatSeconds = (secs) => {
            if (isNaN(secs) || secs === null) return '0:00:00';
            const h = Math.floor(secs / 3600).toString().padStart(2, '0');
            const m = Math.floor((secs % 3600) / 60).toString().padStart(2, '0');
            const s = Math.floor(secs % 60).toString().padStart(2, '0');
            return `${h}:${m}:${s}`;
        };

        dateSelect.addEventListener("change", () => {
            const agentId = agentSelect.value;
            const date = dateSelect.value;

            if (agentId && date && existingData[agentId] && existingData[agentId][date]) {
                const record = existingData[agentId][date];
                const agentName = agentNameMap[agentId] || `کارشناس ${agentId}`;
                let html = `
                    <p><strong>نمایش رکورد برای ${agentName} در تاریخ ${new Date(date).toLocaleDateString("fa-IR")}</strong></p>
                    <ul>
                        <li>تماس ورودی: ${record.incoming_calls || 0}</li>
                        <li>مجموع مکالمه: ${formatSeconds(record.total_talk_time_in)}</li>
                        <li>مدت حضور: ${formatSeconds(record.presence_duration)}</li>
                        <li>مدت استراحت: ${formatSeconds(record.break_duration)}</li>
                        <li>تعداد تیکت: ${record.tickets_count || 0}</li>
                        <li>بی پاسخ: ${record.missed_calls || 0}</li>
                    </ul>
                    <button class="btn-danger" id="delete-record-btn" data-agent-id="${agentId}" data-date="${date}">
                        🗑️ حذف کامل این رکورد
                    </button>
                `;
                dataRecordDisplay.innerHTML = html;
            } else {
                dataRecordDisplay.innerHTML = '<p class="placeholder-text">لطفا یک تاریخ معتبر انتخاب کنید.</p>';
            }
        });

        dataRecordDisplay.addEventListener('click', async (event) => {
            const targetButton = event.target.closest('#delete-record-btn');
            if (!targetButton) return;

            const {
                agentId,
                date
            } = targetButton.dataset;
            const agentName = agentSelect.options[agentSelect.selectedIndex].text;
            const dateFa = new Date(date).toLocaleDateString("fa-IR");

            if (confirm(`آیا از حذف کامل رکورد برای «${agentName}» در تاریخ ${dateFa} مطمئن هستید؟`)) {
                targetButton.disabled = true;
                targetButton.innerHTML = "در حال حذف...";
                try {
                    const formData = new FormData();
                    formData.append('action', 'delete_report');
                    formData.append('agent_id', agentId);
                    formData.append('date', date);

                    const response = await fetch("/php/process_reports.php", {
                        method: "POST",
                        body: formData
                    });
                    if (!response.ok) throw new Error(`Server error: ${response.statusText}`);

                    const result = await response.json();
                    if (result.success) {
                        alert(result.message);
                        window.location.reload();
                    } else {
                        throw new Error(result.message || "خطا در پردازش درخواست.");
                    }
                } catch (error) {
                    alert(`خطا در حذف داده: ${error.message}`);
                    targetButton.disabled = false;
                    targetButton.innerHTML = "🗑️ حذف کامل این رکورد";
                }
            }
        });
    </script>
</body>

</html>
