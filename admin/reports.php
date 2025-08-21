<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');

// Load existing report data to populate the management section
$jsonFile = __DIR__ . '/../data/reports.json';
$existingData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
if (!is_array($existingData)) $existingData = [];
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
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
            --info-light: #cff4fc;
            --bg-color: #f8fcf9;
            --text-color: #222;
            --secondary-text-color: #555;
            --card-bg: #ffffff;
            --header-text: #ffffff;
            --shadow-color-light: rgba(0, 174, 112, 0.07);
            --shadow-color-medium: rgba(0, 174, 112, 0.12);
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

        a {
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }

        header,
        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px var(--shadow-color-light);
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
            color: var(--header-text);
            margin-bottom: 0;
        }

        footer {
            height: 60px;
            font-size: 0.85rem;
            margin-top: auto;
        }


        main {
            padding: 1.5rem;
            max-width: 900px;
            width: 100%;
            margin: 2rem auto;
            flex-grow: 1;
        }

        .form-container,
        .management-container {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 4px 15px var(--shadow-color-light);
            border-top: 4px solid var(--primary-color);
        }

        .management-container {
            margin-top: 2rem;
            border-top-color: #4a90e2;
        }

        h1,
        h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
            text-align: center;
            font-weight: 700;
        }

        .management-container h2 {
            color: #4a90e2;
        }

        .description {
            color: #555;
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .flex-group {
            display: flex;
            gap: 1rem;
            align-items: flex-end;
        }

        .flex-group .form-group {
            flex-grow: 1;
            margin-bottom: 0;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }

        select,
        textarea,
        input[type="date"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            box-sizing: border-box;
            background-color: #fcfcfc;
            transition: border-color 0.2s;
        }

        textarea {
            min-height: 250px;
            direction: ltr;
            text-align: left;
        }

        select:focus,
        textarea:focus,
        input[type="date"]:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        button {
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            display: block;
            width: 100%;
            transition: background-color 0.2s, transform 0.2s;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        button.btn-secondary {
            background-color: #6c757d;
        }

        button.btn-secondary:hover:not(:disabled) {
            background-color: #5a6268;
        }

        button.btn-danger {
            background-color: var(--danger-color);
        }

        button.btn-danger:hover:not(:disabled) {
            background-color: var(--danger-dark);
        }

        button[type="submit"] {
            background-color: var(--primary-color);
            box-shadow: 0 4px 10px var(--shadow-color-medium);
        }

        button[type="submit"]:hover:not(:disabled) {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .response-message {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            font-weight: 500;
            display: none;
        }

        .response-message.success {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            border: 1px solid var(--primary-color);
        }

        .response-message.error {
            background-color: #fff0f3;
            color: #c82333;
            border: 1px solid #ff0040;
        }

        .instructions-box {
            background-color: #f0f7ff;
            border: 1px solid #cce2ff;
            border-right: 4px solid #4a90e2;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: -0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.88rem;
            color: #333;
            line-height: 1.7;
            display: none;
        }

        .instructions-box strong {
            font-weight: 700;
            color: var(--primary-dark);
        }

        .instructions-box em {
            color: var(--danger-color);
            font-style: normal;
            font-weight: 500;
            display: block;
            margin-top: 8px;
        }

        /* --- Modal Styles --- */
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
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 25px;
            border: 1px solid #888;
            width: 80%;
            max-width: 1000px;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .modal-header h2 {
            margin-bottom: 0;
        }

        .close-button {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-button:hover,
        .close-button:focus {
            color: black;
        }

        #preview-summary {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
        }

        #preview-summary.valid {
            background-color: var(--primary-light);
        }

        #preview-summary.invalid {
            background-color: var(--danger-light);
        }

        .preview-table-container {
            max-height: 400px;
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
            padding: 8px 12px;
            text-align: left;
            direction: ltr;
        }

        th {
            background-color: #f2f2f2;
            position: sticky;
            top: 0;
        }

        tr.valid-row {
            background-color: #e6f7f2;
        }

        tr.invalid-row {
            background-color: #f8d7da;
        }

        tr.invalid-row td:last-child {
            color: var(--danger-dark);
            font-weight: 500;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        #view-data-pre {
            background-color: #2d2d2d;
            color: #f1f1f1;
            padding: 1rem;
            border-radius: 0.5rem;
            max-height: 300px;
            overflow: auto;
            direction: ltr;
            text-align: left;
            white-space: pre-wrap;
            word-wrap: break-word;
            margin-top: 1rem;
            display: none;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="form-container">
            <h1>سامانه به‌روزرسانی گزارش‌ها</h1>
            <p class="description">
                ابتدا نوع گزارش را انتخاب کرده، سپس اطلاعات را در کادر زیر وارد و دکمه ذخیره را بزنید.
            </p>
            <form id="reportForm">
                <div class="form-group">
                    <label for="report_type">نوع گزارش:</label>
                    <select id="report_type" name="report_type" required>
                        <option value="" disabled selected>لطفا یک مورد را انتخاب کنید</option>
                        <option value="call_metrics">گزارش معیارهای تماس</option>
                        <option value="presence_duration">گزارش مدت حضور</option>
                        <option value="off_queue_duration">گزارش مدت خروج از صف</option>
                        <option value="one_star_ratings">گزارش امتیاز ۱ داده شده</option>
                        <option value="calls_over_5_min">گزارش مکالمات بالای ۵ دقیقه</option>
                        <option value="missed_calls">گزارش تماس بی‌پاسخ</option>
                        <option value="outbound_calls">گزارش تماس خروجی</option>
                        <option value="no_call_reason">گزارش عدم ثبت دلیل تماس</option>
                        <option value="tickets_count">گزارش تعداد تیکت</option>
                    </select>
                </div>

                <div id="report-instructions" class="instructions-box"></div>

                <div class="form-group" id="date-picker-group" style="display: none;">
                    <label for="report_date">تاریخ گزارش:</label>
                    <input type="date" id="report_date" name="report_date">
                </div>

                <div class="form-group">
                    <label for="excel_data">محتوای گزارش:</label>
                    <textarea id="excel_data" name="excel_data" required placeholder="داده‌های کپی شده از اکسل را اینجا جای‌گذاری کنید..."></textarea>
                </div>
                <div class="flex-group">
                    <button type="button" id="previewBtn">پیش‌نمایش و اعتبارسنجی</button>
                    <button type="submit" id="submitBtn" disabled>ذخیره تغییرات</button>
                </div>
            </form>
            <div id="response" class="response-message"></div>
        </div>

        <div class="management-container">
            <h2>مدیریت داده‌های موجود</h2>
            <div class="flex-group">
                <div class="form-group">
                    <label for="agent_select">انتخاب کارشناس:</label>
                    <select id="agent_select">
                        <option value="">ابتدا یک کارشناس انتخاب کنید</option>
                        <?php
                        $agentIds = array_keys($existingData);
                        sort($agentIds, SORT_NUMERIC);
                        foreach ($agentIds as $agentId) {
                            echo "<option value='{$agentId}'>{$agentId}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_select">انتخاب تاریخ:</label>
                    <select id="date_select" disabled>
                        <option value="">ابتدا کارشناس را انتخاب کنید</option>
                    </select>
                </div>
            </div>
            <div class="flex-group" style="margin-top: 1rem;">
                <button type="button" id="viewDataBtn" class="btn-secondary" disabled>نمایش داده</button>
                <button type="button" id="deleteDataBtn" class="btn-danger" disabled>حذف رکورد روزانه</button>
            </div>
            <pre id="view-data-pre"></pre>
        </div>
    </main>

    <div id="previewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>پیش‌نمایش داده‌ها</h2>
                <span class="close-button">&times;</span>
            </div>
            <div id="preview-summary"></div>
            <div class="preview-table-container">
                <table id="preview-table">
                    <thead></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>


    <div id="footer-placeholder"></div>

    <script src="/js/header.js"></script>
    <script>
        const existingData = <?php echo json_encode($existingData); ?>;

        // Form Elements
        const reportTypeSelect = document.getElementById("report_type");
        const datePickerGroup = document.getElementById("date-picker-group");
        const datePickerInput = document.getElementById("report_date");
        const instructionsBox = document.getElementById("report-instructions");
        const excelDataTextarea = document.getElementById("excel_data");
        const reportForm = document.getElementById("reportForm");
        const responseDiv = document.getElementById("response");
        const submitButton = document.getElementById("submitBtn");

        // Preview Elements
        const previewButton = document.getElementById("previewBtn");
        const previewModal = document.getElementById("previewModal");
        const closeModal = document.querySelector(".close-button");
        const previewSummaryDiv = document.getElementById("preview-summary");
        const previewTableBody = document.querySelector("#preview-table tbody");
        const previewTableHeader = document.querySelector("#preview-table thead");

        // Management Elements
        const agentSelect = document.getElementById('agent_select');
        const dateSelect = document.getElementById('date_select');
        const viewDataBtn = document.getElementById('viewDataBtn');
        const deleteDataBtn = document.getElementById('deleteDataBtn');
        const viewDataPre = document.getElementById('view-data-pre');

        const instructions = {
            call_metrics: `<strong>ستون‌ها:</strong> کد اپراتور - نام اپراتور - تاریخ - پاسخ داده شده - مجموع مکالمه - میانگین مکالمه - بیشترین زمان مکالمه - میانگین امتیاز - تعداد امتیاز`,
            presence_duration: `<strong>ستون‌ها:</strong> کد اپراتور - نام اپراتور - تاریخ - مدت حضور`,
            off_queue_duration: `<strong>ستون‌ها:</strong> کد اپراتور - نام اپراتور - تاریخ - مدت خروج از صف`,
            one_star_ratings: `<strong>ستون‌ها:</strong> تاریخ تماس - کد اپراتور - نام اپراتور`,
            calls_over_5_min: `<strong>ستون‌ها:</strong> تاریخ تماس - کد اپراتور - نام اپراتور`,
            missed_calls: `<strong>ستون‌ها:</strong> کد اپراتور - نام اپراتور - تاریخ`,
            outbound_calls: `<strong>ستون‌ها:</strong> کد اپراتور - نام اپراتور - تاریخ`,
            no_call_reason: `<strong>ستون‌ها:</strong> تاریخ تماس - کاربر`,
            tickets_count: `<strong>ستون‌ها:</strong> اقدام کننده عملیات - تعداد تیکت`
        };

        const validators = {
            _isNumeric: (val) => val && /^\d+$/.test(val.trim()),
            _isShamsiDate: (val) => val && /^\d{4}\/\d{2}\/\d{2}$/.test(val.trim()),

            call_metrics: {
                minCols: 9,
                validate: (cols) => validators._isNumeric(cols[0]) && validators._isShamsiDate(cols[2])
            },
            presence_duration: {
                minCols: 4,
                validate: (cols) => validators._isNumeric(cols[0]) && validators._isShamsiDate(cols[2])
            },
            off_queue_duration: {
                minCols: 4,
                validate: (cols) => validators._isNumeric(cols[0]) && validators._isShamsiDate(cols[2])
            },
            one_star_ratings: {
                minCols: 3,
                config: {
                    id_col: 1,
                    date_col: 0
                },
                validate: (cols, config) => validators._isNumeric(cols[config.id_col]) && validators._isShamsiDate(cols[config.date_col])
            },
            calls_over_5_min: {
                minCols: 3,
                config: {
                    id_col: 1,
                    date_col: 0
                },
                validate: (cols, config) => validators._isNumeric(cols[config.id_col]) && validators._isShamsiDate(cols[config.date_col])
            },
            missed_calls: {
                minCols: 3,
                config: {
                    id_col: 0,
                    date_col: 2
                },
                validate: (cols, config) => validators._isNumeric(cols[config.id_col]) && validators._isShamsiDate(cols[config.date_col])
            },
            outbound_calls: {
                minCols: 3,
                config: {
                    id_col: 0,
                    date_col: 2
                },
                validate: (cols, config) => validators._isNumeric(cols[config.id_col]) && validators._isShamsiDate(cols[config.date_col])
            },
            no_call_reason: {
                minCols: 2,
                validate: (cols) => validators._isShamsiDate(cols[0])
            },
            tickets_count: {
                minCols: 2,
                validate: (cols) => cols[0].trim().length > 0 && validators._isNumeric(cols[1])
            }
        };

        function updateFormUI() {
            const selectedValue = reportTypeSelect.value;
            submitButton.disabled = true; // Always disable submit button on change, force preview

            if (selectedValue === 'tickets_count') {
                datePickerGroup.style.display = 'block';
                datePickerInput.required = true;
            } else {
                datePickerGroup.style.display = 'none';
                datePickerInput.required = false;
            }

            if (instructions[selectedValue]) {
                instructionsBox.innerHTML = instructions[selectedValue];
                instructionsBox.style.display = 'block';
            } else {
                instructionsBox.style.display = 'none';
                instructionsBox.innerHTML = '';
            }
        }

        reportTypeSelect.addEventListener("change", updateFormUI);

        previewButton.addEventListener("click", () => {
            const reportType = reportTypeSelect.value;
            const data = excelDataTextarea.value;
            if (!reportType || !data.trim()) {
                alert("لطفا نوع گزارش و محتوای آن را وارد کنید.");
                return;
            }
            if (reportType === 'tickets_count' && !datePickerInput.value) {
                alert("لطفا برای گزارش تعداد تیکت، تاریخ را مشخص کنید.");
                return;
            }

            const validator = validators[reportType];
            const lines = data.trim().split("\n");
            let validCount = 0;
            let invalidCount = 0;

            let tableHTML = "";
            let headerHTML = "<tr>";
            const headerTitles = instructions[reportType].replace(/<strong>.*?<\/strong>/g, '').replace(/<em>.*?<\/em>/g, '').split(' - ').map(s => s.trim());
            headerTitles.forEach(title => headerHTML += `<th>${title}</th>`);
            headerHTML += `<th>وضعیت</th></tr>`;
            previewTableHeader.innerHTML = headerHTML;

            lines.forEach(line => {
                if (!line.trim()) return;
                const columns = line.split(/\t+/);
                let isValid = false;
                let reason = "تعداد ستون‌ها نامعتبر است.";

                if (columns.length >= validator.minCols) {
                    if (validator.validate(columns, validator.config)) {
                        isValid = true;
                        reason = "معتبر";
                    } else {
                        reason = "فرمت داده‌های ستون کلیدی (کد یا تاریخ) اشتباه است.";
                    }
                }

                tableHTML += `<tr class="${isValid ? 'valid-row' : 'invalid-row'}">`;
                columns.forEach(col => tableHTML += `<td>${col}</td>`);
                tableHTML += `<td>${reason}</td></tr>`;

                if (isValid) validCount++;
                else invalidCount++;
            });

            previewTableBody.innerHTML = tableHTML;
            previewSummaryDiv.innerHTML = `تعداد ردیف‌های معتبر: ${validCount} <br> تعداد ردیف‌های نامعتبر: ${invalidCount}`;

            if (invalidCount === 0 && validCount > 0) {
                previewSummaryDiv.className = "valid";
                submitButton.disabled = false;
            } else {
                previewSummaryDiv.className = "invalid";
                submitButton.disabled = true;
            }

            previewModal.style.display = "block";
        });

        closeModal.onclick = () => previewModal.style.display = "none";
        window.onclick = (event) => {
            if (event.target == previewModal) {
                previewModal.style.display = "none";
            }
        };

        reportForm.addEventListener("submit", async function(e) {
            e.preventDefault();
            const formData = new FormData(reportForm);

            responseDiv.style.display = "none";
            submitButton.disabled = true;
            submitButton.textContent = "در حال ذخیره...";

            try {
                const response = await fetch("/php/process_reports.php", {
                    method: "POST",
                    body: formData,
                });
                if (!response.ok) throw new Error(`خطای سرور: ${response.statusText}`);

                const result = await response.json();
                responseDiv.textContent = result.message;
                responseDiv.className = result.success ? "response-message success" : "response-message error";
                responseDiv.style.display = "block";

                if (result.success) {
                    setTimeout(() => window.location.reload(), 2000); // Reload to get fresh data
                }

            } catch (error) {
                responseDiv.textContent = `یک خطای غیرمنتظره رخ داد: ${error.message}`;
                responseDiv.className = "response-message error";
                responseDiv.style.display = "block";
            } finally {
                submitButton.textContent = "ذخیره تغییرات";
                // Keep it disabled after submit to force re-validation
            }
        });

        // --- Management Logic ---
        agentSelect.addEventListener('change', () => {
            const agentId = agentSelect.value;
            dateSelect.innerHTML = '<option value="">...بارگذاری تاریخ‌ها</option>';
            dateSelect.disabled = true;
            viewDataBtn.disabled = true;
            deleteDataBtn.disabled = true;
            viewDataPre.style.display = 'none';

            if (agentId && existingData[agentId]) {
                const dates = Object.keys(existingData[agentId]).sort().reverse();
                let options = '<option value="">یک تاریخ انتخاب کنید</option>';
                dates.forEach(date => {
                    const dateFa = new Date(date).toLocaleDateString('fa-IR', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    options += `<option value="${date}">${dateFa} (${date})</option>`;
                });
                dateSelect.innerHTML = options;
                dateSelect.disabled = false;
            } else {
                dateSelect.innerHTML = '<option value="">ابتدا کارشناس را انتخاب کنید</option>';
            }
        });

        dateSelect.addEventListener('change', () => {
            const canProceed = agentSelect.value && dateSelect.value;
            viewDataBtn.disabled = !canProceed;
            deleteDataBtn.disabled = !canProceed;
            viewDataPre.style.display = 'none';
        });

        viewDataBtn.addEventListener('click', () => {
            const agentId = agentSelect.value;
            const date = dateSelect.value;
            if (agentId && date && existingData[agentId][date]) {
                const dataToShow = existingData[agentId][date];
                viewDataPre.textContent = JSON.stringify(dataToShow, null, 2);
                viewDataPre.style.display = 'block';
            }
        });

        deleteDataBtn.addEventListener('click', async () => {
            const agentId = agentSelect.value;
            const date = dateSelect.value;
            if (!agentId || !date) return;

            const dateFa = new Date(date).toLocaleDateString('fa-IR');
            if (!confirm(`آیا از حذف تمام داده‌های کارشناس ${agentId} در تاریخ ${dateFa} مطمئن هستید؟ این عمل غیرقابل بازگشت است.`)) {
                return;
            }

            deleteDataBtn.disabled = true;
            deleteDataBtn.textContent = 'در حال حذف...';

            try {
                const formData = new FormData();
                formData.append('action', 'delete_report');
                formData.append('agent_id', agentId);
                formData.append('date', date);

                const response = await fetch('/php/process_reports.php', {
                    method: 'POST',
                    body: formData
                });
                if (!response.ok) throw new Error(`خطای سرور: ${response.statusText}`);

                const result = await response.json();
                alert(result.message);

                if (result.success) {
                    window.location.reload();
                }

            } catch (error) {
                alert(`خطا در حذف داده‌ها: ${error.message}`);
            } finally {
                deleteDataBtn.textContent = 'حذف رکورد روزانه';
                // Button will be re-enabled on selection change
            }
        });
    </script>
</body>

</html>
