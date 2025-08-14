<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>مدیریت برنامه شیفت‌ها</title>
  <style>
    /* --- General Styles (Unchanged) --- */
    :root {
      --primary-color: #00ae70;
      --primary-dark: #089863;
      --primary-light: #e6f7f2;
      --bg-color: #f8fcf9;
      --text-color: #222;
      --secondary-text-color: #555;
      --card-bg: #ffffff;
      --header-text: #ffffff;
      --border-radius: 0.75rem;
      --border-color: #e9e9e9;
      /* --- NEW --- */
      --swap-color: #e8eaf6;
      /* Light Indigo for swaps */
      --swap-text-color: #3f51b5;
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
      width: 100%;
      flex-grow: 1;
      background-color: var(--card-bg);
      padding: 2rem;
    }

    h1 {
      font-size: 1.8rem;
      margin-bottom: 1.5rem;
      color: var(--primary-dark);
      text-align: center;
      font-weight: 700;
    }

    .filters-container {
      background-color: #f1f3f5;
      padding: 1.5rem;
      border-radius: var(--border-radius);
      margin-bottom: 2rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.5rem;
      align-items: flex-end;
    }

    .filter-group {
      display: flex;
      flex-direction: column;
    }

    .filter-group label {
      font-weight: 600;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
    }

    .filter-group input,
    .filter-group select {
      padding: 0.75rem;
      border-radius: 0.5rem;
      border: 1px solid var(--border-color);
      font-size: 1rem;
      width: 100%;
    }

    .table-container {
      width: 100%;
      overflow-x: auto;
      border: 1px solid var(--border-color);
      border-radius: 0.5rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 900px;
    }

    th,
    td {
      padding: 0.9rem 1rem;
      text-align: center;
      border: 1px solid var(--border-color);
      white-space: nowrap;
    }

    thead th {
      background-color: var(--primary-light);
      color: var(--secondary-text-color);
      font-weight: 600;
      position: sticky;
      top: 0;
      z-index: 3;
      line-height: 1.4;
    }

    tbody tr:nth-child(even) {
      background-color: var(--bg-color);
    }

    tbody tr:hover {
      background-color: var(--primary-light);
    }

    tbody td.editable-cell {
      cursor: pointer;
      transition: background-color 0.2s, box-shadow 0.2s;
    }

    tbody td.editable-cell:hover {
      background-color: #ffe0b2;
      box-shadow: inset 0 0 0 2px var(--primary-dark);
    }

    .status {
      padding: 0.5em 0.7em;
      border-radius: 0.3rem;
      font-weight: 500;
      font-size: 0.85rem;
      color: #fff;
      min-width: 80px;
      display: inline-block;
    }

    .status-on-duty {
      background-color: #28a745;
    }

    .status-off {
      background-color: #dc3545;
    }

    .status-unknown {
      background-color: #6c757d;
    }

    .status-special {
      background-color: #ffc107;
      color: #212529;
    }

    /* --- NEW --- */
    .status-swap {
      background-color: var(--swap-color);
      color: var(--swap-text-color);
    }

    #loader {
      text-align: center;
      font-size: 1.2rem;
      padding: 3rem;
    }

    /* --- Modal Styles (Mostly Unchanged) --- */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s, visibility 0.3s;
    }

    .modal-overlay.visible {
      opacity: 1;
      visibility: visible;
    }

    .modal-content {
      background-color: #fff;
      padding: 2rem;
      border-radius: var(--border-radius);
      width: 90%;
      max-width: 500px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
      transform: translateY(-50px);
      transition: transform 0.3s;
    }

    .modal-overlay.visible .modal-content {
      transform: translateY(0);
    }

    .modal-header h2 {
      font-size: 1.5rem;
      color: var(--primary-dark);
      margin: 0;
    }

    .modal-body .info {
      margin: 1.5rem 0;
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 0.5rem;
      line-height: 1.8;
    }

    .modal-body .info span {
      font-weight: bold;
      color: var(--primary-dark);
    }

    .modal-body .form-group {
      margin-bottom: 1rem;
    }

    .modal-body label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .modal-body select,
    .modal-body input {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border-color);
      border-radius: 0.5rem;
      font-size: 1rem;
    }

    .modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 1rem;
      padding-top: 1rem;
      border-top: 1px solid var(--border-color);
      margin-top: 1.5rem;
    }

    .modal-footer button {
      padding: 0.6rem 1.2rem;
      border-radius: 0.5rem;
      font-size: 1rem;
      cursor: pointer;
      border: none;
      transition: background-color 0.2s;
    }

    .modal-footer .btn-save {
      background-color: var(--primary-color);
      color: white;
    }

    .modal-footer .btn-save:hover {
      background-color: var(--primary-dark);
    }

    .modal-footer .btn-cancel {
      background-color: #6c757d;
      color: white;
    }

    .modal-footer .btn-cancel:hover {
      background-color: #5a6268;
    }

    /* --- NEW Style for Swap Date Selection --- */
    #swap-date-list {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      background-color: #f8f9fa;
      padding: 1rem;
      border-radius: 0.5rem;
      max-height: 150px;
      overflow-y: auto;
    }

    .swap-date-option {
      padding: 0.5rem 1rem;
      border: 1px solid #dee2e6;
      border-radius: 0.4rem;
      cursor: pointer;
      background-color: #fff;
    }

    .swap-date-option:hover {
      background-color: var(--primary-light);
    }

    .swap-date-option.selected {
      background-color: var(--primary-dark);
      color: #fff;
      border-color: var(--primary-dark);
      font-weight: bold;
    }

    /* Summary Styles & DatePicker Styles (Unchanged) */
    .summary-separator td {
      background-color: #e9ecef;
      font-weight: bold;
      color: #495057;
      border-top: 2px solid var(--primary-dark);
      padding: 0.8rem;
    }

    .summary-row td:first-child {
      text-align: right;
      padding-right: 1.5rem;
      font-weight: 600;
      color: #333;
    }

    .summary-count {
      font-weight: 700;
      font-size: 1.1rem;
      color: var(--primary-dark);
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
  </style>
</head>

<body>
  <main>
    <h1>مدیریت برنامه شیفت‌ها</h1>
    <div class="filters-container">
      <div class="filter-group">
        <label for="startDate">از تاریخ:</label>
        <input type="text" id="startDate" placeholder="انتخاب تاریخ (جلالی)" autocomplete="off" />
        <input type="hidden" id="startDateAlt" />
      </div>
      <div class="filter-group">
        <label for="endDate">تا تاریخ:</label>
        <input type="text" id="endDate" placeholder="انتخاب تاریخ (جلالی)" autocomplete="off" />
        <input type="hidden" id="endDateAlt" />
      </div>
      <div class="filter-group">
        <label for="expertSelect1">انتخاب کارشناس اول:</label>
        <select id="expertSelect1"></select>
      </div>
      <div class="filter-group">
        <label for="expertSelect2">انتخاب کارشناس دوم:</label>
        <select id="expertSelect2"></select>
      </div>
    </div>

    <div id="loader">در حال بارگذاری اطلاعات...</div>
    <div id="schedule-container" class="table-container"></div>
  </main>

  <div id="edit-shift-modal" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h2>ویرایش وضعیت شیفت</h2>
      </div>
      <div class="modal-body">
        <div class="info">
          <p>کارشناس: <span id="modal-expert-name"></span></p>
          <p>تاریخ: <span id="modal-shift-date"></span></p>
        </div>
        <div class="form-group">
          <label for="shift-status-select">انتخاب وضعیت</label>
          <select id="shift-status-select">
            <option value="on-duty">حضور</option>
            <option value="off">عدم حضور</option>
            <option value="leave">مرخصی</option>
            <option value="swap">جابجایی شیفت</option>
            <option value="custom">سایر موارد (در کادر زیر بنویسید)</option>
          </select>
        </div>
        <div class="form-group" id="custom-status-group" style="display: none">
          <label for="custom-shift-status">وضعیت سفارشی</label>
          <input type="text" id="custom-shift-status" placeholder="مثلا: ماموریت، آموزش، یا 10:00 - 19:00" />
        </div>
        <div id="swap-controls-group" style="display: none;">
          <div class="form-group">
            <label for="swap-expert-select">جابجایی با:</label>
            <select id="swap-expert-select"></select>
          </div>
          <div class="form-group">
            <label for="swap-date-list">در تاریخ (روزهای عدم حضور همکار):</label>
            <div id="swap-date-list">
              <p style="color: #6c757d;">ابتدا یک همکار را انتخاب کنید.</p>
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button id="modal-cancel-btn" class="btn-cancel">انصراف</button>
        <button id="modal-save-btn" class="btn-save">ذخیره تغییرات</button>
      </div>
    </div>
  </div>

  <script>
    let allExperts = [];
    let allAvailableDates = [];
    const modal = document.getElementById("edit-shift-modal");
    let currentEditingInfo = { expertId: null, date: null, expertName: null };

    const shiftColorMap = new Map();
    const colorPalette = ["#E3F2FD", "#E8F5E9", "#FFF3E0", "#F3E5F5", "#FFEBEE", "#E0F7FA"];
    let nextColorIndex = 0;

    // Jalali Helper Functions (Unchanged)
    function jalaliToGregorian(jy, jm, jd) { var sal_a, gy, gm, gd, days; jy += 1595; days = -355668 + 365 * jy + ~~(jy / 33) * 8 + ~~(((jy % 33) + 3) / 4) + jd + (jm < 7 ? (jm - 1) * 31 : (jm - 7) * 30 + 186); gy = 400 * ~~(days / 146097); days %= 146097; if (days > 36524) { gy += 100 * ~~(--days / 36524); days %= 36524; if (days >= 365) days++; } gy += 4 * ~~(days / 1461); days %= 1461; if (days > 365) { gy += ~~((days - 1) / 365); days = (days - 1) % 365; } gd = days + 1; sal_a = [0, 31, (gy % 4 === 0 && gy % 100 !== 0) || gy % 400 === 0 ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]; for (gm = 0; gm < 13 && gd > sal_a[gm]; gm++) gd -= sal_a[gm]; return new Date(gy, gm - 1, gd); }
    function toPersian(date) { const parts = date.toLocaleDateString("fa-IR-u-nu-latn").split("/"); return parts.map((part) => parseInt(part, 10)); }
    function pad2(n) { return String(n).padStart(2, "0"); }
    function formatJalaliDisplay(jy, jm, jd) { return `${jy}/${pad2(jm)}/${pad2(jd)}`; }
    function formatISO(date) { return `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`; }
    function isJalaliLeap(jy) { return ((((((jy - 474) % 2820) + 2820) % 2820) + 474 + 38) * 682) % 2816 < 682; }
    function jalaliMonthLength(jy, jm) { if (jm <= 6) return 31; if (jm <= 11) return 30; return isJalaliLeap(jy) ? 30 : 29; }

    // Vanilla Jalali DatePicker Class (Unchanged)
    class JalaliDatePicker { constructor(inputId, altId) { this.input = document.getElementById(inputId); this.alt = document.getElementById(altId); if (!this.input || !this.alt) return; const gNow = new Date(); const [jy, jm, jd] = toPersian(gNow); this.jy = jy; this.jm = jm; this.jd = jd; this.pop = document.createElement("div"); this.pop.className = "jdp-popover jdp-hidden"; document.body.appendChild(this.pop); this.boundClickOutside = (e) => { if (!this.pop.contains(e.target) && e.target !== this.input) { this.hide(); } }; this.input.addEventListener("focus", () => this.show()); this.input.addEventListener("click", () => this.show()); window.addEventListener("resize", () => this.position()); } show() { this.render(); this.position(); this.pop.classList.remove("jdp-hidden"); setTimeout(() => document.addEventListener("mousedown", this.boundClickOutside), 0); } hide() { this.pop.classList.add("jdp-hidden"); document.removeEventListener("mousedown", this.boundClickOutside); } position() { const rect = this.input.getBoundingClientRect(); this.pop.style.top = window.scrollY + rect.bottom + 6 + "px"; this.pop.style.left = window.scrollX + rect.left + "px"; } nav(delta) { this.jm += delta; if (this.jm < 1) { this.jm = 12; this.jy--; } if (this.jm > 12) { this.jm = 1; this.jy++; } this.render(); } render() { const weekDays = ["ش", "ی", "د", "س", "چ", "پ", "ج"]; const firstG = jalaliToGregorian(this.jy, this.jm, 1); const firstWeekday = (firstG.getDay() + 1) % 7; const daysInMonth = jalaliMonthLength(this.jy, this.jm); let html = `<div class="jdp-header"><button class="jdp-nav-btn" data-nav="-1">&rarr;</button><div>${new Intl.DateTimeFormat("fa-IR", { month: "long" }).format(firstG)} ${new Intl.DateTimeFormat("fa-IR", { year: "numeric" }).format(firstG)}</div><button class="jdp-nav-btn" data-nav="1">&larr;</button></div><div class="jdp-grid">${weekDays.map((w) => `<div class="jdp-weekday">${w}</div>`).join("")}`; for (let i = 0; i < firstWeekday; i++) { html += `<div class="jdp-day other"></div>`; } for (let d = 1; d <= daysInMonth; d++) { html += `<div class="jdp-day" data-day="${d}">${new Intl.NumberFormat("fa-IR").format(d)}</div>`; } html += `</div>`; this.pop.innerHTML = html; this.pop.querySelectorAll("[data-nav]").forEach((btn) => { btn.addEventListener("click", (e) => this.nav(parseInt(e.currentTarget.dataset.nav, 10))); }); this.pop.querySelectorAll("[data-day]").forEach((cell) => { cell.addEventListener("click", (e) => { const d = parseInt(e.currentTarget.dataset.day, 10); const gDate = jalaliToGregorian(this.jy, this.jm, d); this.input.value = formatJalaliDisplay(this.jy, this.jm, d); this.alt.value = formatISO(gDate); this.hide(); if (typeof applyFilters === "function") applyFilters(); }); }); } setInitialFromGregorian(date) { const [jy, jm, jd] = toPersian(date); this.jy = jy; this.jm = jm; this.jd = jd; this.input.value = formatJalaliDisplay(jy, jm, jd); this.alt.value = formatISO(date); } }

    document.addEventListener("DOMContentLoaded", initializePage);

    async function initializePage() {
      const loader = document.getElementById("loader");
      loader.style.display = "block";
      try {
        const response = await fetch("/data/shifts.json?v=" + new Date().getTime());
        if (!response.ok) throw new Error(`فایل shifts.json یافت نشد (کد: ${response.status})`);

        const data = await response.json();
        allExperts = data.experts || [];
        if (allExperts.length === 0) {
          loader.textContent = "هیچ اطلاعاتی برای نمایش وجود ندارد.";
          return;
        }

        populateFilterControls();
        setupEventListeners();

        const today = new Date();
        const nextWeek = new Date();
        nextWeek.setDate(today.getDate() + 7);

        const dpStart = new JalaliDatePicker("startDate", "startDateAlt");
        dpStart.setInitialFromGregorian(today);
        const dpEnd = new JalaliDatePicker("endDate", "endDateAlt");
        dpEnd.setInitialFromGregorian(nextWeek);

        applyFilters();
      } catch (error) {
        loader.textContent = `خطا در بارگذاری اولیه: ${error.message}`;
      }
    }

    function setupEventListeners() {
      document.getElementById("startDate").addEventListener("blur", applyFilters);
      document.getElementById("endDate").addEventListener("blur", applyFilters);
      document.getElementById("expertSelect1").addEventListener("change", applyFilters);
      document.getElementById("expertSelect2").addEventListener("change", applyFilters);
      document.getElementById("modal-cancel-btn").addEventListener("click", closeEditModal);
      document.getElementById("modal-save-btn").addEventListener("click", saveShiftUpdate);

      // --- MODIFIED Event Listener for Status Select ---
      document.getElementById("shift-status-select").addEventListener("change", (e) => {
        const customGroup = document.getElementById("custom-status-group");
        const swapGroup = document.getElementById("swap-controls-group");
        const selectedValue = e.target.value;

        customGroup.style.display = selectedValue === "custom" ? "block" : "none";
        swapGroup.style.display = selectedValue === "swap" ? "block" : "none";

        if (selectedValue === "swap") {
          populateSwapExpertSelect();
        }
      });

      // --- NEW Event Listener for Swap Expert Select ---
      document.getElementById("swap-expert-select").addEventListener("change", (e) => {
        const expertB_id = e.target.value;
        if (expertB_id && expertB_id !== 'none') {
          populateSwapDates(expertB_id);
        } else {
          document.getElementById("swap-date-list").innerHTML = `<p style="color: #6c757d;">ابتدا یک همکار را انتخاب کنید.</p>`;
        }
      });

      // --- NEW Event Listener for Date Selection in Swap ---
      document.getElementById('swap-date-list').addEventListener('click', e => {
        if (e.target.classList.contains('swap-date-option')) {
          document.querySelectorAll('.swap-date-option').forEach(el => el.classList.remove('selected'));
          e.target.classList.add('selected');
        }
      });

      document.getElementById("schedule-container").addEventListener("click", (e) => {
        const cell = e.target.closest(".editable-cell");
        if (cell) {
          const { expertId, date, currentStatus } = cell.dataset;
          openEditModal(expertId, date, currentStatus);
        }
      });
    }

    function openEditModal(expertId, date, currentStatus) {
      const expert = allExperts.find((exp) => exp.id == expertId);
      currentEditingInfo = { expertId, date, expertName: expert.name };

      document.getElementById("modal-expert-name").textContent = expert.name;
      document.getElementById("modal-shift-date").textContent = new Date(date).toLocaleDateString("fa-IR");

      const statusSelect = document.getElementById("shift-status-select");
      const customStatusInput = document.getElementById("custom-shift-status");
      const customGroup = document.getElementById("custom-status-group");
      const swapGroup = document.getElementById("swap-controls-group");

      // Reset all conditional groups
      customGroup.style.display = "none";
      swapGroup.style.display = "none";
      customStatusInput.value = "";
      document.getElementById("swap-date-list").innerHTML = `<p style="color: #6c757d;">ابتدا یک همکار را انتخاب کنید.</p>`;


      const standardStatuses = ["on-duty", "off", "leave"];
      if (standardStatuses.includes(currentStatus)) {
        statusSelect.value = currentStatus;
      } else if (currentStatus && currentStatus.startsWith("جابجایی")) {
        statusSelect.value = "swap";
        // In a real scenario, you might want to pre-fill the swap controls here
        // For now, we just set the type.
      } else if (currentStatus && currentStatus !== "unknown") {
        statusSelect.value = "custom";
        customGroup.style.display = "block";
        customStatusInput.value = currentStatus;
      } else {
        statusSelect.value = "on-duty";
      }
      modal.classList.add("visible");
    }

    function closeEditModal() {
      modal.classList.remove("visible");
    }

    // --- NEW Functions for Swap ---
    function populateSwapExpertSelect() {
      const select = document.getElementById('swap-expert-select');
      const expertA_id = currentEditingInfo.expertId;

      let optionsHtml = '<option value="none">-- انتخاب کنید --</option>';
      allExperts
        .filter(exp => String(exp.id) !== String(expertA_id)) // Exclude current expert
        .sort((a, b) => a.name.localeCompare(b.name, 'fa'))
        .forEach(expert => {
          optionsHtml += `<option value="${expert.id}">${expert.name}</option>`;
        });
      select.innerHTML = optionsHtml;
    }

    function populateSwapDates(expertB_id) {
      const dateListContainer = document.getElementById('swap-date-list');
      const expertB = allExperts.find(exp => String(exp.id) === String(expertB_id));
      if (!expertB) {
        dateListContainer.innerHTML = `<p style="color: #dc3545;">خطا: کارشناس یافت نشد.</p>`;
        return;
      }

      const availableDates = Object.entries(expertB.shifts)
        .filter(([date, status]) => status === 'off')
        .map(([date]) => date)
        .sort();

      if (availableDates.length === 0) {
        dateListContainer.innerHTML = `<p style="color: #6c757d;">این همکار روز خالی برای جابجایی ندارد.</p>`;
        return;
      }

      let datesHtml = '';
      availableDates.forEach(date => {
        const jalaliDate = new Date(date).toLocaleDateString('fa-IR');
        datesHtml += `<div class="swap-date-option" data-date="${date}">${jalaliDate}</div>`;
      });
      dateListContainer.innerHTML = datesHtml;
    }

    // --- MODIFIED saveShiftUpdate function ---
    async function saveShiftUpdate() {
      const token = localStorage.getItem("jwt");
      if (!token) {
        alert("توکن احراز هویت یافت نشد. لطفاً دوباره وارد شوید.");
        return;
      }

      const statusSelectValue = document.getElementById("shift-status-select").value;
      let requestBody, action;

      if (statusSelectValue === 'swap') {
        action = 'swap';
        const expertA_id = currentEditingInfo.expertId;
        const dateX = currentEditingInfo.date;
        const expertB_id = document.getElementById('swap-expert-select').value;
        const selectedDateEl = document.querySelector('.swap-date-option.selected');

        if (expertB_id === 'none' || !selectedDateEl) {
          alert('لطفاً همکار و تاریخ مورد نظر برای جابجایی را انتخاب کنید.');
          return;
        }
        const dateY = selectedDateEl.dataset.date;

        requestBody = { action, expertA_id, dateX, expertB_id, dateY };

      } else {
        action = 'update';
        const { expertId, date } = currentEditingInfo;
        if (!expertId || !date) return;

        let newStatus = statusSelectValue;
        if (newStatus === "custom") {
          newStatus = document.getElementById("custom-shift-status").value.trim();
          if (!newStatus) {
            alert("لطفاً برای وضعیت سفارشی یک مقدار وارد کنید.");
            return;
          }
        }
        requestBody = { action, expertId, date, status: newStatus };
      }

      try {
        const response = await fetch("/php/update-shift.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${token}`,
          },
          body: JSON.stringify(requestBody),
        });

        const result = await response.json();
        if (!response.ok) {
          throw new Error(result.message || "خطا در ذخیره‌سازی تغییرات.");
        }

        alert(result.message);
        closeEditModal();

        // --- Update local data for instant feedback ---
        if (action === 'swap') {
          const { expertA_id, dateX, expertB_id, dateY } = requestBody;
          const expertA = allExperts.find(e => String(e.id) === String(expertA_id));
          const expertB = allExperts.find(e => String(e.id) === String(expertB_id));
          if (expertA && expertB) {
            expertA.shifts[dateX] = `عدم حضور (جابجایی با ${expertB.name})`;
            expertB.shifts[dateY] = `حضور (جابجایی از ${expertA.name})`;
          }
        } else {
          const { expertId, date, status } = requestBody;
          const exp = allExperts.find(e => String(e.id) === String(expertId));
          if (exp) {
            exp.shifts[date] = status;
          }
        }
        applyFilters();

      } catch (error) {
        console.error("Error updating shift:", error);
        alert(`ذخیره ناموفق بود: ${error.message}`);
      }
    }

    // Unchanged functions: getShiftStyle, populateFilterControls
    function getShiftStyle(shiftTime) { if (!shiftTime) return ""; if (!shiftColorMap.has(shiftTime)) { shiftColorMap.set(shiftTime, colorPalette[nextColorIndex]); nextColorIndex = (nextColorIndex + 1) % colorPalette.length; } return `style="background-color: ${shiftColorMap.get(shiftTime)};"`; }
    function populateFilterControls() { const expertSelect1 = document.getElementById("expertSelect1"); const expertSelect2 = document.getElementById("expertSelect2"); let optionsHtml = '<option value="none">-- هیچکدام --</option>'; allExperts.sort((a, b) => a.name.localeCompare(b.name, "fa")).forEach((expert) => { optionsHtml += `<option value="${expert.id}">${expert.name}</option>`; }); expertSelect1.innerHTML = optionsHtml; expertSelect2.innerHTML = optionsHtml; expertSelect1.value = "none"; expertSelect2.value = "none"; const allDatesSet = new Set(); allExperts.forEach((expert) => { Object.keys(expert.shifts).forEach((date) => allDatesSet.add(date)); }); allAvailableDates = Array.from(allDatesSet).sort(); }

    // --- MODIFIED renderTable function ---
    function renderTable(expertsToRender, datesToRender) {
      const container = document.getElementById("schedule-container");
      const loader = document.getElementById("loader");

      if (!expertsToRender || !datesToRender || datesToRender.length === 0) {
        container.innerHTML = "";
        loader.textContent = "هیچ داده‌ای مطابق با فیلترهای شما یافت نشد.";
        loader.style.display = "block";
        return;
      }
      loader.style.display = "none";

      // All calculation logic remains unchanged
      const dailyOnDutyCounts = {}; const dailyOffDutyCounts = {}; const dailyLeaveCounts = {}; const totalOnDutyByDate = {}; const totalOffDutyByDate = {}; const totalLeaveByDate = {};
      const uniqueShiftTimes = [...new Set(allExperts.map((e) => e["shifts-time"]).filter(Boolean))].sort();
      datesToRender.forEach((date) => { dailyOnDutyCounts[date] = {}; dailyOffDutyCounts[date] = {}; dailyLeaveCounts[date] = {}; uniqueShiftTimes.forEach((shift) => { dailyOnDutyCounts[date][shift] = 0; dailyOffDutyCounts[date][shift] = 0; dailyLeaveCounts[date][shift] = 0; }); totalOnDutyByDate[date] = 0; totalOffDutyByDate[date] = 0; totalLeaveByDate[date] = 0; allExperts.forEach((expert) => { const status = expert.shifts[date] || "unknown"; const shiftTime = expert["shifts-time"]; if (shiftTime) { switch (status) { case "off": dailyOffDutyCounts[date][shiftTime]++; break; case "leave": dailyLeaveCounts[date][shiftTime]++; break; default: if (!status.startsWith("عدم حضور")) dailyOnDutyCounts[date][shiftTime]++; break; } } switch (status) { case "off": totalOffDutyByDate[date]++; break; case "leave": totalLeaveByDate[date]++; break; case "unknown": break; default: if (status.startsWith("عدم حضور")) { totalOffDutyByDate[date]++; } else { totalOnDutyByDate[date]++; } break; } }); });

      let tableHtml = `<table><thead><tr><th>نام کارشناس</th><th>ساعت شیفت</th><th>تایم استراحت</th>`;
      datesToRender.forEach((date) => {
        const d = new Date(date);
        const day = d.toLocaleDateString("fa-IR", { day: "numeric" });
        const month = d.toLocaleDateString("fa-IR", { month: "short" });
        const weekday = d.toLocaleDateString("fa-IR", { weekday: "short" });
        tableHtml += `<th>${weekday}<br>${day} ${month}<br><span style="font-size: 0.8rem; color: #6c757d; font-weight: 400;">${date}</span></th>`;
      });
      tableHtml += "</tr></thead><tbody>";

      if (expertsToRender.length > 0) {
        expertsToRender.forEach((expert) => {
          const shiftTime = expert["shifts-time"] || "-";
          const breakTime = expert["break-time"] || "-";
          const shiftStyle = getShiftStyle(shiftTime);
          tableHtml += `<tr><td>${expert.name}</td><td ${shiftStyle}>${shiftTime}</td><td>${breakTime}</td>`;

          datesToRender.forEach((date) => {
            const status = expert.shifts[date] || "unknown";
            let statusClass = "", statusText = status;

            // --- MODIFIED Status rendering logic ---
            if (status.includes("جابجایی")) {
              statusClass = "status-swap";
              statusText = status; // Display full text
            } else if (status === "on-duty") {
              statusClass = "status-on-duty";
              statusText = "حضور";
            } else if (status === "off") {
              statusClass = "status-off";
              statusText = "عدم حضور";
            } else if (status === "leave") {
              statusClass = "status-special";
              statusText = "مرخصی";
            } else if (status === "unknown") {
              statusClass = "status-unknown";
              statusText = "-";
            } else {
              statusClass = "status-special";
            }

            tableHtml += `<td class="editable-cell" data-expert-id="${expert.id}" data-date="${date}" data-current-status="${status}">
                            <span class="status ${statusClass}">${statusText}</span>
                          </td>`;
          });
          tableHtml += "</tr>";
        });
      }

      // Summary section generation (Unchanged)
      const totalColumns = datesToRender.length + 3;
      if (uniqueShiftTimes.length > 0) { tableHtml += `<tr class="summary-separator"><td colspan="${totalColumns}">خلاصه وضعیت به تفکیک شیفت</td></tr>`; uniqueShiftTimes.forEach((shiftTime) => { const summaryRowStyle = getShiftStyle(shiftTime); tableHtml += `<tr class="summary-row"><td ${summaryRowStyle}>حاضرین در شیفت ${shiftTime}</td><td>-</td><td>-</td>`; datesToRender.forEach((date) => { tableHtml += `<td><span class="summary-count">${dailyOnDutyCounts[date][shiftTime] || 0}</span></td>`; }); tableHtml += `</tr>`; tableHtml += `<tr class="summary-row"><td ${summaryRowStyle}>عدم حضور در شیفت ${shiftTime}</td><td>-</td><td>-</td>`; datesToRender.forEach((date) => { tableHtml += `<td><span class="summary-count" style="color: #dc3545;">${dailyOffDutyCounts[date][shiftTime] || 0}</span></td>`; }); tableHtml += `</tr>`; tableHtml += `<tr class="summary-row"><td ${summaryRowStyle}>مرخصی در شیفت ${shiftTime}</td><td>-</td><td>-</td>`; datesToRender.forEach((date) => { tableHtml += `<td><span class="summary-count" style="color: #ffc107; text-shadow: 1px 1px 1px #fff;">${dailyLeaveCounts[date][shiftTime] || 0}</span></td>`; }); tableHtml += `</tr>`; }); }
      tableHtml += `<tr class="summary-separator"><td colspan="${totalColumns}">جمع‌بندی کل روزانه</td></tr>`; tableHtml += `<tr class="summary-row"><td style="background-color: #E8F5E9;">مجموع کل کارشناسان حاضر</td><td>-</td><td>-</td>`; datesToRender.forEach((date) => { tableHtml += `<td><span class="summary-count">${totalOnDutyByDate[date] || 0}</span></td>`; }); tableHtml += `</tr>`; tableHtml += `<tr class="summary-row"><td style="background-color: #FFEBEE;">مجموع کل عدم حضور</td><td>-</td><td>-</td>`; datesToRender.forEach((date) => { tableHtml += `<td><span class="summary-count" style="color: #dc3545;">${totalOffDutyByDate[date] || 0}</span></td>`; }); tableHtml += `</tr>`; tableHtml += `<tr class="summary-row"><td style="background-color: #FFF3E0;">مجموع کل مرخصی</td><td>-</td><td>-</td>`; datesToRender.forEach((date) => { tableHtml += `<td><span class="summary-count" style="color: #ffc107; text-shadow: 1px 1px 1px #fff;">${totalLeaveByDate[date] || 0}</span></td>`; }); tableHtml += `</tr>`;

      tableHtml += "</tbody></table>";
      container.innerHTML = tableHtml;
    }

    function applyFilters() {
      const startDate = document.getElementById("startDateAlt").value;
      const endDate = document.getElementById("endDateAlt").value;
      const expert1 = document.getElementById("expertSelect1").value;
      const expert2 = document.getElementById("expertSelect2").value;

      const selectedExpertIds = new Set();
      if (expert1 !== "none") selectedExpertIds.add(expert1);
      if (expert2 !== "none") selectedExpertIds.add(expert2);

      const filteredDates = allAvailableDates.filter((date) => (!startDate || date >= startDate) && (!endDate || date <= endDate));

      let filteredExperts = allExperts;
      if (selectedExpertIds.size > 0) {
        filteredExperts = allExperts.filter((expert) => selectedExpertIds.has(String(expert.id)));
      }

      filteredExperts.sort((a, b) => (a["shifts-time"] || "").localeCompare(b["shifts-time"] || ""));
      renderTable(filteredExperts, filteredDates);
    }
  </script>
</body>

</html>
