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
            color: var(--header-text);
            margin-bottom: 0;
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
            color: var(--text-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .step-title span {
            background-color: var(--primary-color);
            color: white;
            border-radius: 999px;
            width: 28px;
            height: 28px;
            display: inline-grid;
            place-items: center;
            font-weight: 700;
        }

        .management-card .step-title span {
            background-color: var(--info-dark);
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }

        select,
        textarea,
        input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            background-color: #fcfdff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        select:focus,
        textarea:focus,
        input[type="text"]:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 174, 112, 0.15);
            outline: none;
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
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
        }

        .btn-primary:hover:not(:disabled) {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: var(--info-color);
        }

        .btn-secondary:hover:not(:disabled) {
            background-color: var(--info-dark);
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--danger-color);
        }

        .btn-danger:hover:not(:disabled) {
            background-color: var(--danger-dark);
            transform: translateY(-2px);
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
            display: none;
            margin-top: 1rem;
        }

        #data-sections-list {
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 1rem;
            background-color: #fcfdff;
            min-height: 100px;
        }

        #data-sections-list .placeholder-text {
            color: var(--secondary-text-color);
            text-align: center;
            padding-top: 1.5rem;
        }

        #data-sections-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        #data-sections-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background-color: var(--bg-color);
            border-radius: 0.4rem;
            border-right: 4px solid var(--info-color);
        }

        #data-sections-list li span {
            font-weight: 600;
            color: var(--text-color);
        }

        .btn-delete-section {
            padding: 0.3rem 0.8rem;
            font-size: 0.85rem;
            font-weight: 500;
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
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background-color: var(--card-bg);
            margin: 5% auto;
            padding: 0;
            border: none;
            width: 90%;
            max-width: 1000px;
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
            line-height: 1;
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
            max-height: 50vh;
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

        .jdp-popover {
            position: absolute;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            padding: 0.75rem;
            width: 280px;
            z-index: 9999;
        }

        .jdp-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .jdp-nav-btn {
            background: var(--primary-color);
            color: #fff;
            border: none;
            padding: 0.25rem 0.6rem;
            border-radius: 0.4rem;
            cursor: pointer;
        }

        .jdp-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }

        .jdp-weekday {
            text-align: center;
            font-size: 0.85rem;
            color: var(--secondary-text-color);
            padding: 0.3rem 0;
        }

        .jdp-day {
            text-align: center;
            padding: 0.4rem 0;
            border-radius: 0.4rem;
            cursor: pointer;
            background: #fafafa;
            border: 1px solid #f0f0f0;
        }

        .jdp-day:hover {
            background: var(--primary-light);
        }

        .jdp-day.other {
            color: #bbb;
            background: #f8f9fa;
        }

        .jdp-hidden {
            display: none;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @media (max-width: 1024px) {
            .page-layout-grid {
                grid-template-columns: 1fr;
            }

            .management-card {
                margin-top: 2.5rem;
            }
        }

        @media (max-width: 600px) {
            main {
                padding: 1.5rem 1rem;
            }

            .form-card,
            .management-card {
                padding: 1.5rem;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <h1 class="page-title">مدیریت و به‌روزرسانی گزارش‌ها</h1>
        <p class="page-subtitle">داده‌های گزارش‌های روزانه را وارد کرده یا داده‌های موجود را مدیریت کنید.</p>

        <div class="page-layout-grid">
            <div class="form-card">
                <div class="card-header">
                    <h2 class="card-title">بارگذاری گزارش جدید</h2>
                </div>

                <form id="reportForm">
                    <div class="step">
                        <h3 class="step-title"><span>۱</span> نوع گزارش را انتخاب کنید</h3>
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
                        <div id="report-instructions" class="instructions-box"></div>
                    </div>

                    <div class="step">
                        <h3 class="step-title"><span>۲</span> داده‌ها را جای‌گذاری کنید</h3>
                        <div id="date-picker-group" style="display: none; margin-bottom: 1rem;">
                            <label for="report_date_display">تاریخ گزارش:</label>
                            <input type="text" id="report_date_display" placeholder="انتخاب تاریخ (جلالی)" autocomplete="off" />
                            <input type="hidden" id="report_date" name="report_date" />
                        </div>
                        <textarea id="excel_data" name="excel_data" required placeholder="داده‌های کپی شده از اکسل را اینجا جای‌گذاری کنید..."></textarea>
                    </div>

                    <div class="step">
                        <h3 class="step-title"><span>۳</span> پیش‌نمایش و ذخیره</h3>
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
                    <h3 class="step-title"><span>۱</span> رکورد مورد نظر را انتخاب کنید</h3>
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
                    <h3 class="step-title"><span>۲</span> داده مورد نظر را حذف کنید</h3>
                    <div id="data-sections-list">
                        <p class="placeholder-text">ابتدا کارشناس و تاریخ را انتخاب کنید تا لیست داده‌ها نمایش داده شود.</p>
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
        /* ===== Jalali helpers and DatePicker Class (self-contained) ===== */
        function jalaliToGregorian(jy, jm, jd) {
            var sal_a, gy, gm, gd, days;
            jy += 1595;
            days = -355668 + 365 * jy + ~~(jy / 33) * 8 + ~~(((jy % 33) + 3) / 4) + jd + (jm < 7 ? (jm - 1) * 31 : (jm - 7) * 30 + 186);
            gy = 400 * ~~(days / 146097);
            days %= 146097;
            if (days > 36524) {
                gy += 100 * ~~(--days / 36524);
                days %= 36524;
                if (days >= 365) days++;
            }
            gy += 4 * ~~(days / 1461);
            days %= 1461;
            if (days > 365) {
                gy += ~~((days - 1) / 365);
                days = (days - 1) % 365;
            }
            gd = days + 1;
            sal_a = [0, 31, (gy % 4 === 0 && gy % 100 !== 0) || gy % 400 === 0 ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            for (gm = 0; gm < 13 && gd > sal_a[gm]; gm++) gd -= sal_a[gm];
            return new Date(gy, gm - 1, gd);
        }

        function toPersian(date) {
            const parts = date.toLocaleDateString("fa-IR-u-nu-latn").split("/");
            return parts.map((part) => parseInt(part, 10));
        }

        function pad2(n) {
            return String(n).padStart(2, "0");
        }

        function formatJalaliDisplay(jy, jm, jd) {
            return `${jy}/${pad2(jm)}/${pad2(jd)}`;
        }

        function formatISO(date) {
            return `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`;
        }

        function isJalaliLeap(jy) {
            return ((((((jy - 474) % 2820) + 2820) % 2820) + 474 + 38) * 682) % 2816 < 682;
        }

        function jalaliMonthLength(jy, jm) {
            if (jm <= 6) return 31;
            if (jm <= 11) return 30;
            return isJalaliLeap(jy) ? 30 : 29;
        }
        class JalaliDatePicker {
            constructor(inputId, altId) {
                this.input = document.getElementById(inputId);
                this.alt = document.getElementById(altId);
                if (!this.input || !this.alt) return;
                const gNow = new Date();
                const [jy, jm, jd] = toPersian(gNow);
                this.jy = jy;
                this.jm = jm;
                this.jd = jd;
                this.pop = document.createElement("div");
                this.pop.className = "jdp-popover jdp-hidden";
                document.body.appendChild(this.pop);
                this.boundClickOutside = (e) => {
                    if (!this.pop.contains(e.target) && e.target !== this.input) {
                        this.hide();
                    }
                };
                this.input.addEventListener("focus", () => this.show());
                this.input.addEventListener("click", () => this.show());
                window.addEventListener("resize", () => this.position());
            }
            show() {
                this.render();
                this.position();
                this.pop.classList.remove("jdp-hidden");
                setTimeout(() => document.addEventListener("mousedown", this.boundClickOutside), 0);
            }
            hide() {
                this.pop.classList.add("jdp-hidden");
                document.removeEventListener("mousedown", this.boundClickOutside);
            }
            position() {
                const rect = this.input.getBoundingClientRect();
                this.pop.style.top = window.scrollY + rect.bottom + 6 + "px";
                this.pop.style.left = window.scrollX + rect.left + "px";
            }
            nav(delta) {
                this.jm += delta;
                if (this.jm < 1) {
                    this.jm = 12;
                    this.jy--;
                }
                if (this.jm > 12) {
                    this.jm = 1;
                    this.jy++;
                }
                this.render();
            }
            render() {
                const weekDays = ["ش", "ی", "د", "س", "چ", "پ", "ج"];
                const firstG = jalaliToGregorian(this.jy, this.jm, 1);
                const firstWeekday = (firstG.getDay() + 1) % 7;
                const daysInMonth = jalaliMonthLength(this.jy, this.jm);
                let html = `<div class="jdp-header"><button class="jdp-nav-btn" data-nav="-1">&rarr;</button><div>${new Intl.DateTimeFormat("fa-IR", { month: "long" }).format(firstG)} ${new Intl.DateTimeFormat("fa-IR", { year: "numeric" }).format(firstG)}</div><button class="jdp-nav-btn" data-nav="1">&larr;</button></div><div class="jdp-grid">${weekDays.map((w) => `<div class="jdp-weekday">${w}</div>`).join("")}`;
                for (let i = 0; i < firstWeekday; i++) {
                    html += `<div class="jdp-day other"></div>`;
                }
                for (let d = 1; d <= daysInMonth; d++) {
                    html += `<div class="jdp-day" data-day="${d}">${new Intl.NumberFormat("fa-IR").format(d)}</div>`;
                }
                html += `</div>`;
                this.pop.innerHTML = html;
                this.pop.querySelectorAll("[data-nav]").forEach((btn) => {
                    btn.addEventListener("click", (e) => this.nav(parseInt(e.currentTarget.dataset.nav, 10)));
                });
                this.pop.querySelectorAll("[data-day]").forEach((cell) => {
                    cell.addEventListener("click", (e) => {
                        const d = parseInt(e.currentTarget.dataset.day, 10);
                        const gDate = jalaliToGregorian(this.jy, this.jm, d);
                        this.input.value = formatJalaliDisplay(this.jy, this.jm, d);
                        this.alt.value = formatISO(gDate);
                        this.hide();
                    });
                });
            }
        }

        /* ===== Main Application Logic ===== */
        const existingData = <?php echo json_encode($existingData); ?>;
        const reportTypeSelect = document.getElementById("report_type"),
            datePickerGroup = document.getElementById("date-picker-group"),
            datePickerInput = document.getElementById("report_date"),
            instructionsBox = document.getElementById("report-instructions"),
            excelDataTextarea = document.getElementById("excel_data"),
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
            dataSectionsList = document.getElementById("data-sections-list");

        const reportTypeNames = {};
        document.querySelectorAll("#report_type option").forEach(option => {
            if (option.value) {
                reportTypeNames[option.value] = option.textContent;
            }
        });

        const instructions = {
            call_metrics: "<strong>ستون‌ها:</strong> کد اپراتور - نام اپراتور - تاریخ - پاسخ داده شده - مجموع مکالمه - میانگین مکالمه - بیشترین زمان مکالمه - میانگین امتیاز - تعداد امتیاز",
            presence_duration: "<strong>ستون‌ها:</strong> کد اپراتور - نام اپراتور - تاریخ - مدت حضور",
            off_queue_duration: "<strong>ستون‌ها:</strong> کد اپراتور - نام اپراتور - تاریخ - مدت خروج از صف",
            one_star_ratings: "<strong>ستون‌ها:</strong> تاریخ تماس - کد اپراتور - نام اپراتور",
            calls_over_5_min: "<strong>ستون‌ها:</strong> تاریخ تماس - کد اپراتور - نام اپراتور",
            missed_calls: "<strong>ستون‌ها:</strong> کد اپراتور - نام اپراتور - تاریخ",
            outbound_calls: "<strong>ستون‌ها:</strong> کد اپراتور - نام اپراتور - تاریخ",
            no_call_reason: "<strong>ستون‌ها:</strong> تاریخ تماس - کاربر",
            tickets_count: "<strong>ستون‌ها:</strong> اقدام کننده عملیات - تعداد تیکت"
        };
        const validators = {
            _isNumeric: e => e && /^\d+$/.test(e.trim()),
            _isShamsiDate: e => e && /^\d{4}\/\d{2}\/\d{2}$/.test(e.trim()),
            call_metrics: {
                minCols: 9,
                validate: e => validators._isNumeric(e[0]) && validators._isShamsiDate(e[2])
            },
            presence_duration: {
                minCols: 4,
                validate: e => validators._isNumeric(e[0]) && validators._isShamsiDate(e[2])
            },
            off_queue_duration: {
                minCols: 4,
                validate: e => validators._isNumeric(e[0]) && validators._isShamsiDate(e[2])
            },
            one_star_ratings: {
                minCols: 3,
                config: {
                    id_col: 1,
                    date_col: 0
                },
                validate: (e, t) => validators._isNumeric(e[t.id_col]) && validators._isShamsiDate(e[t.date_col])
            },
            calls_over_5_min: {
                minCols: 3,
                config: {
                    id_col: 1,
                    date_col: 0
                },
                validate: (e, t) => validators._isNumeric(e[t.id_col]) && validators._isShamsiDate(e[t.date_col])
            },
            missed_calls: {
                minCols: 3,
                config: {
                    id_col: 0,
                    date_col: 2
                },
                validate: (e, t) => validators._isNumeric(e[t.id_col]) && validators._isShamsiDate(e[t.date_col])
            },
            outbound_calls: {
                minCols: 3,
                config: {
                    id_col: 0,
                    date_col: 2
                },
                validate: (e, t) => validators._isNumeric(e[t.id_col]) && validators._isShamsiDate(e[t.date_col])
            },
            no_call_reason: {
                minCols: 2,
                validate: e => validators._isShamsiDate(e[0])
            },
            tickets_count: {
                minCols: 2,
                validate: e => e[0].trim().length > 0 && validators._isNumeric(e[1])
            }
        };

        function updateFormUI() {
            const e = reportTypeSelect.value;
            submitButton.disabled = !0, "tickets_count" === e ? (datePickerGroup.style.display = "block", datePickerInput.required = !0) : (datePickerGroup.style.display = "none", datePickerInput.required = !1), instructions[e] ? (instructionsBox.innerHTML = `ℹ️ ${instructions[e]}`, instructionsBox.style.display = "block") : (instructionsBox.style.display = "none")
        }
        reportTypeSelect.addEventListener("change", updateFormUI);

        previewButton.addEventListener("click", () => {
            const e = reportTypeSelect.value,
                t = excelDataTextarea.value;
            if (!e || !t.trim()) return void alert("لطفا نوع گزارش و محتوای آن را وارد کنید.");
            if ("tickets_count" === e && !datePickerInput.value) return void alert("لطفا برای گزارش تعداد تیکت، تاریخ را مشخص کنید.");
            const a = validators[e],
                d = t.trim().split("\n");
            let o = 0,
                n = 0;
            let s = "",
                i = "<tr>";
            const r = instructions[e].replace(/<strong>.*?<\/strong>/g, "").split(" - ").map(e => e.trim());
            r.forEach(e => i += `<th>${e}</th>`), i += "<th>وضعیت</th></tr>", previewTableHeader.innerHTML = i, d.forEach(e => {
                if (!e.trim()) return;
                const t = e.split(/\t+/);
                let d = !1,
                    i = "تعداد ستون‌ها نامعتبر است.";
                t.length >= a.minCols && (a.validate(t, a.config) ? (d = !0, i = "✅ معتبر") : (i = "❌ فرمت داده اشتباه است.")), s += `<tr class="${d?"valid-row":"invalid-row"}">`, t.forEach(e => s += `<td>${e}</td>`), s += `<td>${i}</td></tr>`, d ? o++ : n++
            }), previewTableBody.innerHTML = s, previewSummaryDiv.innerHTML = `تعداد ردیف‌های معتبر: ${o} <br> تعداد ردیف‌های نامعتبر: ${n}`, 0 === n && o > 0 ? (previewSummaryDiv.className = "valid", submitButton.disabled = !1) : (previewSummaryDiv.className = "invalid", submitButton.disabled = !0), previewModal.style.display = "block"
        });

        closeModal.onclick = () => previewModal.style.display = "none";
        window.onclick = e => {
            e.target == previewModal && (previewModal.style.display = "none")
        };

        reportForm.addEventListener("submit", async function(e) {
            e.preventDefault();
            const t = new FormData(reportForm);
            responseDiv.style.display = "none", submitButton.disabled = !0, submitButton.innerHTML = "در حال ذخیره...";
            try {
                const e = await fetch("/php/process_reports.php", {
                    method: "POST",
                    body: t
                });
                if (!e.ok) throw new Error(`خطای سرور: ${e.statusText}`);
                const a = await e.json();
                responseDiv.textContent = a.message, responseDiv.className = a.success ? "alert success" : "alert error", responseDiv.style.display = "block", a.success && setTimeout(() => window.location.reload(), 2e3)
            } catch (e) {
                responseDiv.textContent = `یک خطای غیرمنتظره رخ داد: ${e.message}`, responseDiv.className = "alert error", responseDiv.style.display = "block"
            } finally {
                submitButton.innerHTML = "💾 ذخیره تغییرات"
            }
        });

        agentSelect.addEventListener("change", () => {
            const agentId = agentSelect.value;
            dateSelect.innerHTML = '<option value="">...بارگذاری تاریخ‌ها</option>';
            dateSelect.disabled = true;
            dataSectionsList.innerHTML = '<p class="placeholder-text">ابتدا کارشناس و تاریخ را انتخاب کنید...</p>';

            if (agentId && existingData[agentId]) {
                dateSelect.innerHTML = '<option value="">یک تاریخ انتخاب کنید</option>' + Object.keys(existingData[agentId]).sort().reverse().map(date => `<option value="${date}">${new Date(date).toLocaleDateString("fa-IR",{year:"numeric",month:"long",day:"numeric"})} (${date})</option>`).join("");
                dateSelect.disabled = false;
            } else {
                dateSelect.innerHTML = '<option value="">ابتدا کارشناس را انتخاب کنید</option>';
            }
        });

        dateSelect.addEventListener("change", () => {
            const agentId = agentSelect.value;
            const date = dateSelect.value;

            if (agentId && date && existingData[agentId] && existingData[agentId][date]) {
                const sections = Object.keys(existingData[agentId][date]);
                if (sections.length > 0) {
                    let html = '<ul>';
                    sections.forEach(sectionKey => {
                        const sectionName = reportTypeNames[sectionKey] || sectionKey;
                        html += `
                            <li>
                                <span>${sectionName}</span>
                                <button class="btn-danger btn-delete-section"
                                        data-action="delete-section"
                                        data-agent-id="${agentId}"
                                        data-date="${date}"
                                        data-section-key="${sectionKey}">
                                    🗑️ حذف
                                </button>
                            </li>
                        `;
                    });
                    html += '</ul>';
                    dataSectionsList.innerHTML = html;
                } else {
                    dataSectionsList.innerHTML = '<p class="placeholder-text">هیچ داده‌ای برای این تاریخ و کارشناس یافت نشد.</p>';
                }
            } else {
                dataSectionsList.innerHTML = '<p class="placeholder-text">لطفا یک تاریخ معتبر انتخاب کنید...</p>';
            }
        });

        dataSectionsList.addEventListener('click', async (event) => {
            const targetButton = event.target.closest('[data-action="delete-section"]');
            if (!targetButton) return;

            const {
                agentId,
                date,
                sectionKey
            } = targetButton.dataset;
            const agentName = agentSelect.options[agentSelect.selectedIndex].text;
            const sectionName = reportTypeNames[sectionKey] || sectionKey;
            const dateFa = new Date(date).toLocaleDateString("fa-IR");

            if (confirm(`آیا از حذف داده بخش «${sectionName}» برای کارشناس «${agentName}» در تاریخ ${dateFa} مطمئن هستید؟`)) {
                targetButton.disabled = true;
                targetButton.innerHTML = "در حال حذف...";
                try {
                    const formData = new FormData();
                    formData.append('action', 'delete_section');
                    formData.append('agent_id', agentId);
                    formData.append('date', date);
                    formData.append('section_key', sectionKey);

                    const response = await fetch("/php/process_reports.php", {
                        method: "POST",
                        body: formData
                    });
                    if (!response.ok) throw new Error(`خطای سرور: ${response.statusText}`);

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
                    targetButton.innerHTML = "🗑️ حذف";
                }
            }
        });

        new JalaliDatePicker("report_date_display", "report_date");
    </script>
</body>

</html>
