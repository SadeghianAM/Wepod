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
    <title>مدیریت گزارش‌ها</title>
    <style>
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --danger-color: #ef4444;
            --danger-dark: #dc2626;
            --success-color: #22c55e;
            --success-dark: #16a34a;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-color: #1e293b;
            --secondary-text-color: #64748b;
            --border-color: #e2e8f0;
            --shadow-color: rgba(100, 116, 139, 0.12);
            --header-text: #ffffff;
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        header {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            height: 60px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 174, 112, 0.07);
            flex-shrink: 0;
            height: 60px;
            font-size: 0.85rem;
            margin-top: auto;
        }

        main {
            padding: 2.5rem;
            max-width: 1600px;
            width: 100%;
            margin: 0 auto;
            flex-grow: 1;
        }

        .page-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .page-subtitle {
            font-size: 1.1rem;
            font-weight: 400;
            color: var(--secondary-text-color);
            margin-bottom: 3rem;
            text-align: center;
        }

        .page-layout-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2.5rem;
            align-items: start;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px var(--shadow-color), 0 2px 4px -2px var(--shadow-color);
        }

        .card-header {
            text-align: right;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color);
        }

        .step {
            margin-bottom: 2rem;
        }

        .step-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        textarea,
        select,
        input[type="text"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            background-color: #f8fafc;
            color: var(--text-color);
            transition: all 0.2s;
        }

        textarea:focus,
        select:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        textarea {
            min-height: 200px;
            direction: ltr;
            text-align: left;
        }

        .button-group {
            display: flex;
            gap: 1rem;
        }

        button {
            padding: 0.75rem 1.5rem;
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
            color: white;
            border: none;
        }

        .btn-primary:hover:not(:disabled) {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-secondary:hover:not(:disabled) {
            background-color: var(--primary-color);
            color: white;
        }

        #data-record-display ul {
            list-style: none;
            margin-top: 1rem;
        }

        .placeholder-text {
            color: var(--secondary-text-color);
            text-align: center;
            padding: 3rem 0;
            font-size: 1rem;
        }

        .metric-item {
            display: flex;
            align-items: center;
            padding: 1rem 0.5rem;
            border-bottom: 1px solid var(--border-color);
            gap: 1rem;
        }

        .metric-item:last-child {
            border-bottom: none;
        }

        .metric-item .name {
            font-weight: 500;
            flex-basis: 200px;
            flex-shrink: 0;
            color: var(--secondary-text-color);
        }

        .metric-item .value {
            font-family: monospace;
            direction: ltr;
            text-align: left;
            flex-grow: 1;
            font-weight: 600;
        }

        .metric-item .actions {
            flex-shrink: 0;
            display: flex;
            gap: 0.75rem;
        }

        .btn-icon {
            background: none;
            border: none;
            padding: 0.25rem;
            font-size: 1.2rem;
            color: var(--secondary-text-color);
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-icon:hover {
            transform: scale(1.2);
        }

        .btn-save-metric,
        .btn-cancel-edit {
            display: none;
        }

        .metric-item.edit-mode .btn-save-metric,
        .metric-item.edit-mode .btn-cancel-edit {
            display: inline-flex;
        }

        .metric-item.edit-mode .btn-edit-metric,
        .metric-item.edit-mode .btn-delete-metric {
            display: none;
        }

        .metric-item.edit-mode .value span {
            display: none;
        }

        .metric-item .value input {
            display: none;
            padding: 0.5rem;
            font-size: 0.9rem;
        }

        .metric-item.edit-mode .value input {
            display: block;
        }

        .toast {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 2000;
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            color: white;
            font-weight: 600;
            min-width: 300px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            animation: toast-in 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .toast.success {
            background-color: var(--success-dark);
        }

        .toast.error {
            background-color: var(--danger-dark);
        }

        @keyframes toast-in {
            from {
                transform: translateY(120%);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
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
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.3rem;
        }

        .close-button {
            color: #aaa;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
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
            background-color: #dcfce7;
            color: #166534;
        }

        #preview-summary.invalid {
            background-color: #fee2e2;
            color: #991b1b;
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
            background-color: #f1f5f9;
            position: sticky;
            top: 0;
            font-weight: 600;
        }

        tr.valid-row {
            background-color: #f0fdf4;
        }

        tr.invalid-row {
            background-color: #fef2f2;
        }

        tr.invalid-row td:last-child {
            color: var(--danger-dark);
            font-weight: 500;
        }

        @media (max-width: 1024px) {
            .page-layout-grid {
                grid-template-columns: 1fr;
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
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">بارگذاری گزارش جدید</h2>
                </div>
                <form id="reportForm">
                    <div class="step">
                        <h3 class="step-title">۱. جای‌گذاری داده‌ها از اکسل</h3>
                        <textarea id="excel_data" name="excel_data" required placeholder="داده‌های کپی شده (۲۱ ستون) را اینجا جای‌گذاری کنید..."></textarea>
                    </div>
                    <div class="step">
                        <h3 class="step-title">۲. پیش‌نمایش و ذخیره</h3>
                        <div class="button-group">
                            <button type="button" id="previewBtn" class="btn-secondary">🔍 پیش‌نمایش</button>
                            <button type="submit" id="submitBtn" class="btn-primary" disabled>💾 ذخیره گزارش</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">مدیریت داده‌های موجود</h2>
                </div>
                <div class="step">
                    <h3 class="step-title">۱. رکورد مورد نظر را انتخاب کنید</h3>
                    <div class="button-group" style="gap: 1.5rem;">
                        <select id="agent_select" style="flex: 1;">
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
                        <select id="date_select" disabled style="flex: 1;">
                            <option value="">ابتدا کارشناس را انتخاب کنید</option>
                        </select>
                    </div>
                </div>
                <div class="step">
                    <h3 class="step-title">۲. مقادیر را ویرایش یا حذف کنید</h3>
                    <div id="data-record-display">
                        <p class="placeholder-text">ابتدا کارشناس و تاریخ را انتخاب کنید تا لیست مقادیر نمایش داده شود.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div id="previewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>پیش‌نمایش داده‌ها</h2><span class="close-button">&times;</span>
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
        // *** FIX: Added 'chat_count' to the labels ***
        const METRIC_LABELS = {
            incoming_calls: "تماس ورودی",
            total_talk_time_in: "مجموع مکالمه ورودی",
            avg_talk_time_in: "میانگین مکالمه ورودی",
            max_talk_time_in: "بیشترین مکالمه ورودی",
            avg_rating: "میانگین امتیاز",
            ratings_count: "تعداد امتیاز",
            presence_duration: "مدت حضور",
            break_duration: "مدت استراحت",
            one_star_ratings: "امتیاز ۱",
            calls_over_5_min: "مکالمات بالای ۵ دقیقه",
            missed_calls: "تماس بی پاسخ",
            outbound_calls: "تماس خروجی",
            avg_talk_time_out: "میانگین مکالمه خروجی",
            tickets_count: "تعداد تیکت",
            chat_count: "تعداد چت",
            famas_count: "تعداد فمس",
            jira_count: "تعداد جیرا",
            no_call_reason: "عدم ثبت دلیل تماس"
        };
        const TIME_BASED_METRICS = ['total_talk_time_in', 'avg_talk_time_in', 'max_talk_time_in', 'avg_talk_time_out', 'presence_duration', 'break_duration'];
        const UNIFIED_HEADERS = ['کد اپراتور', 'نام', 'تاریخ', 'تماس ورودی', 'مجموع مکالمه (ورودی)', 'میانگین مکالمه (ورودی)', 'بیشترین مکالمه (ورودی)', 'تعداد امتیاز', 'میانگین امتیاز', 'مدت حضور', 'استراحت', 'بی پاسخ', 'تماس خروجی', 'میانگین مکالمه (خروجی)', 'تیکت', 'چت', 'فمس', 'جیرا', 'امتیاز (۱)', 'مکالمات بالای ۵ دقیقه', 'عدم ثبت دلیل تماس'];

        const agentSelect = document.getElementById("agent_select");
        const dateSelect = document.getElementById("date_select");
        const dataRecordDisplay = document.getElementById("data-record-display");
        const reportForm = document.getElementById("reportForm");
        const previewButton = document.getElementById("previewBtn");
        const previewModal = document.getElementById("previewModal");

        previewButton.addEventListener("click", () => {
            const pastedData = document.getElementById("excel_data").value.trim();
            if (!pastedData) return showToast('لطفا محتوای گزارش را وارد کنید.', 'error');
            const lines = pastedData.split("\n");
            let validRows = 0,
                invalidRows = 0;
            let tableHeaderHTML = "<tr>" + UNIFIED_HEADERS.map(h => `<th>${h}</th>`).join('') + "<th>وضعیت</th></tr>";
            document.querySelector("#preview-table thead").innerHTML = tableHeaderHTML;
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
                tableBodyHTML += `<tr class="${isValid ? "valid-row" : "invalid-row"}">` + columns.map(c => `<td>${c}</td>`).join('') + `<td>${statusMsg}</td></tr>`;
                isValid ? validRows++ : invalidRows++;
            });
            const previewTableBody = document.querySelector("#preview-table tbody");
            const previewSummaryDiv = document.getElementById("preview-summary");
            const submitButton = document.getElementById("submitBtn");
            previewTableBody.innerHTML = tableBodyHTML;
            previewSummaryDiv.innerHTML = `ردیف‌های معتبر: ${validRows} <br> ردیف‌های نامعتبر: ${invalidRows}`;
            if (invalidRows === 0 && validRows > 0) {
                previewSummaryDiv.className = "valid";
                submitButton.disabled = false;
            } else {
                previewSummaryDiv.className = "invalid";
                submitButton.disabled = true;
            }
            previewModal.style.display = "block";
        });
        document.querySelector(".close-button").onclick = () => previewModal.style.display = "none";
        window.onclick = e => {
            if (e.target == previewModal) previewModal.style.display = "none";
        };
        reportForm.addEventListener("submit", async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'process_report');
            const submitButton = document.getElementById("submitBtn");
            try {
                const result = await sendRequest(formData, submitButton);
                showToast(result.message, 'success');
                if (result.success) setTimeout(() => window.location.reload(), 2000);
            } catch (error) {
                showToast(error.message, 'error');
            }
        });

        agentSelect.addEventListener("change", () => {
            const agentId = agentSelect.value;
            dateSelect.innerHTML = '<option value="">...بارگذاری</option>';
            dateSelect.disabled = true;
            dataRecordDisplay.innerHTML = '<p class="placeholder-text">برای مشاهده داده‌ها، تاریخ را انتخاب کنید.</p>';
            if (agentId && existingData[agentId]) {
                const dates = Object.keys(existingData[agentId]).sort().reverse();
                let optionsHTML = '<option value="">یک تاریخ انتخاب کنید</option>';
                dates.forEach(date => {
                    const jalaliDate = new Date(date).toLocaleDateString("fa-IR");
                    optionsHTML += `<option value="${date}">${jalaliDate}</option>`;
                });
                dateSelect.innerHTML = optionsHTML;
                dateSelect.disabled = false;
            } else {
                dateSelect.innerHTML = '<option value="">ابتدا کارشناس را انتخاب کنید</option>';
            }
        });
        dateSelect.addEventListener("change", () => {
            const agentId = agentSelect.value;
            const date = dateSelect.value;
            if (agentId && date) {
                renderReportDetails(agentId, date);
            } else {
                dataRecordDisplay.innerHTML = '<p class="placeholder-text">ابتدا کارشناس و تاریخ را انتخاب کنید.</p>';
            }
        });

        // *** FIX: This function now renders all possible metrics, not just existing ones ***
        const renderReportDetails = (agentId, date) => {
            const record = existingData[agentId]?.[date] || {}; // Use empty object as fallback
            let html = '<ul>';

            // Iterate over the master list of labels to ensure all are shown
            for (const key of Object.keys(METRIC_LABELS)) {
                const label = METRIC_LABELS[key];
                const rawValue = record[key] ?? (TIME_BASED_METRICS.includes(key) ? 0 : 0); // Default to 0 if not present
                const displayValue = TIME_BASED_METRICS.includes(key) ? formatSeconds(rawValue) : rawValue;

                html += `
                    <li class="metric-item" data-metric-key="${key}">
                        <span class="name">${label}</span>
                        <div class="value">
                            <span>${displayValue}</span>
                            <input type="text" value="${displayValue}" />
                        </div>
                        <div class="actions">
                            <button class="btn-icon btn-edit-metric" title="ویرایش">✏️</button>
                            <button class="btn-icon btn-delete-metric" title="حذف">🗑️</button>
                            <button class="btn-icon btn-save-metric" title="ذخیره">✔️</button>
                            <button class="btn-icon btn-cancel-edit" title="لغو">❌</button>
                        </div>
                    </li>`;
            }
            html += '</ul>';
            dataRecordDisplay.innerHTML = html;
        };

        dataRecordDisplay.addEventListener('click', async (e) => {
            const agentId = agentSelect.value;
            const date = dateSelect.value;
            if (!agentId || !date) return;
            const button = e.target.closest('button');
            if (!button) return;
            const item = button.closest('.metric-item');
            const metricKey = item.dataset.metricKey;

            if (button.classList.contains('btn-edit-metric')) {
                item.classList.add('edit-mode');
                item.querySelector('input').focus();
            }
            if (button.classList.contains('btn-cancel-edit')) {
                item.classList.remove('edit-mode');
            }
            if (button.classList.contains('btn-delete-metric')) {
                if (confirm(`آیا از حذف متریک «${METRIC_LABELS[metricKey] || metricKey}» مطمئن هستید؟`)) {
                    const formData = new FormData();
                    formData.append('action', 'delete_metric');
                    formData.append('agent_id', agentId);
                    formData.append('date', date);
                    formData.append('metric_key', metricKey);
                    handleAdminAction(formData, button);
                }
            }
            if (button.classList.contains('btn-save-metric')) {
                const newValue = item.querySelector('input').value;
                const formData = new FormData();
                formData.append('action', 'edit_metric');
                formData.append('agent_id', agentId);
                formData.append('date', date);
                formData.append('metric_key', metricKey);
                formData.append('new_value', newValue);
                handleAdminAction(formData, button);
            }
        });

        async function handleAdminAction(formData, button) {
            const item = button.closest('.metric-item');
            const action = formData.get('action');
            try {
                const result = await sendRequest(formData, button);
                showToast(result.message, 'success');
                const agentId = formData.get('agent_id');
                const date = formData.get('date');
                const metricKey = formData.get('metric_key');
                if (!existingData[agentId]) existingData[agentId] = {};
                if (!existingData[agentId][date]) existingData[agentId][date] = {};
                if (action === 'delete_metric') {
                    delete existingData[agentId][date][metricKey];
                    item.style.transition = 'opacity 0.3s';
                    item.style.opacity = '0';
                    setTimeout(() => item.remove(), 300);
                } else if (action === 'edit_metric') {
                    const originalValue = result.updatedValue;
                    const displayValue = TIME_BASED_METRICS.includes(metricKey) ? formatSeconds(originalValue) : originalValue;
                    existingData[agentId][date][metricKey] = originalValue;
                    item.querySelector('.value span').textContent = displayValue;
                    item.querySelector('input').value = displayValue;
                    item.classList.remove('edit-mode');
                }
            } catch (error) {
                showToast(error.message, 'error');
            }
        }

        async function sendRequest(formData, button) {
            const originalContent = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '...';
            try {
                const response = await fetch("/php/process_reports.php", {
                    method: "POST",
                    body: formData
                });
                if (!response.ok) throw new Error(`خطای سرور: ${response.status}`);
                const result = await response.json();
                if (!result.success) throw new Error(result.message);
                return result;
            } finally {
                button.disabled = false;
                button.innerHTML = originalContent;
            }
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.transition = 'opacity 0.5s';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }

        const formatSeconds = (secs) => {
            if (isNaN(secs) || secs === null) return '00:00:00';
            const h = Math.floor(secs / 3600).toString().padStart(2, '0');
            const m = Math.floor((secs % 3600) / 60).toString().padStart(2, '0');
            const s = Math.floor(secs % 60).toString().padStart(2, '0');
            return `${h}:${m}:${s}`;
        };
    </script>
</body>

</html>
