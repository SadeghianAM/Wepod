<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>مدیریت برنامه شیفت‌ها</title>
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
      --success-color: #28a745;
      --info-color: #17a2b8;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --success-light: #e9f7eb;
      --info-light: #e8f6f8;
      --warning-light: #fff8e7;
      --danger-light: #fbebec;
      --swap-color: #e8eaf6;
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
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    main {
      width: 100%;
      max-width: 1800px;
      margin: 0 auto;
      padding: 2.5rem 2rem;
      flex-grow: 1;
    }

    footer {
      background: var(--primary-color);
      color: var(--header-text);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      min-height: 60px;
      font-size: .85rem;
    }

    h1 {
      font-size: clamp(1.5rem, 3vw, 2rem);
      margin-bottom: 2rem;
      color: var(--primary-dark);
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: .75rem;
    }

    .filters-container {
      background-color: var(--card-bg);
      padding: 1.5rem;
      border-radius: var(--radius);
      margin-bottom: 2rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.5rem;
      align-items: flex-end;
      border: 1px solid var(--border-color);
      box-shadow: var(--shadow-sm);
    }

    .filter-group {
      display: flex;
      flex-direction: column;
    }

    .filter-group label {
      font-weight: 600;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
      color: var(--secondary-text);
    }

    .filter-group input,
    .filter-group select {
      padding: 0.8em 1.2em;
      border-radius: var(--radius);
      border: 1.5px solid var(--border-color);
      font-size: 1rem;
      width: 100%;
      transition: border-color .2s, box-shadow .2s;
    }

    .filter-group input:focus-visible,
    .filter-group select:focus-visible {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(0, 174, 112, .15);
    }

    .table-container {
      width: 100%;
      overflow-x: auto;
      border: 1px solid var(--border-color);
      border-radius: var(--radius);
      background-color: var(--card-bg);
      box-shadow: var(--shadow-sm);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 1200px;
    }

    th,
    td {
      padding: 0.9rem 1rem;
      text-align: center;
      border: 1px solid var(--border-color);
      white-space: nowrap;
    }

    thead th {
      background-color: var(--bg-color);
      color: var(--secondary-text);
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
      background-color: #e3f2fd;
      box-shadow: inset 0 0 0 2px var(--info-color);
    }

    .icon {
      width: 1.1em;
      height: 1.1em;
      stroke-width: 2.2;
      vertical-align: -0.15em;
    }

    .status {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: .5em;
    }

    .status .icon {
      width: 1.2em;
      height: 1.2em;
    }

    .status-on-duty {
      background-color: var(--success-light);
      color: var(--success-color);
    }

    .status-off {
      background-color: var(--danger-light);
      color: var(--danger-color);
    }

    .status-unknown {
      background-color: var(--border-color);
      color: var(--secondary-text);
    }

    .status-leave {
      background-color: var(--warning-light);
      color: #a17400;
    }

    .status-remote {
      background-color: var(--info-light);
      color: var(--info-color);
    }

    .status-swap {
      background-color: var(--swap-color);
      color: var(--swap-text-color);
    }

    .status-custom {
      background-color: #e0e0e0;
      color: #424242;
    }

    #loader {
      text-align: center;
      font-size: 1.2rem;
      padding: 3rem;
    }

    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
      opacity: 0;
      transition: opacity 0.3s;
    }

    .modal-overlay.visible {
      display: flex;
      opacity: 1;
    }

    .modal-content {
      background-color: #fff;
      padding: 2rem;
      border-radius: var(--radius);
      width: 90%;
      max-width: 500px;
      box-shadow: var(--shadow-md);
      transform: scale(0.95);
      transition: transform 0.3s;
    }

    .modal-overlay.visible .modal-content {
      transform: scale(1);
    }

    .modal-header {
      display: flex;
      align-items: center;
      gap: .75rem;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid var(--border-color);
    }

    .modal-header h2 {
      font-size: 1.5rem;
      color: var(--primary-dark);
      margin: 0;
    }

    .modal-body .info {
      margin-bottom: 1.5rem;
      background: var(--bg-color);
      padding: 1rem;
      border-radius: .5rem;
      line-height: 1.8;
      border: 1px solid var(--border-color);
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
      font-size: .9rem;
    }

    .modal-body select,
    .modal-body input {
      width: 100%;
      padding: 0.8em 1.2em;
      border: 1.5px solid var(--border-color);
      border-radius: var(--radius);
      font-size: 1rem;
    }

    .modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 1rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--border-color);
      margin-top: 1.5rem;
    }

    .btn {
      padding: .8em 1.5em;
      font-size: .95rem;
      font-weight: 600;
      border: 1.5px solid transparent;
      border-radius: var(--radius);
      cursor: pointer;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.6em;
    }

    .btn:hover {
      transform: translateY(-2px);
      filter: brightness(0.92);
    }

    .btn-primary {
      background-color: var(--primary-color);
      color: white;
    }

    .btn-secondary {
      background-color: var(--secondary-text);
      color: white;
    }

    #swap-date-list {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      background-color: var(--bg-color);
      padding: 1rem;
      border-radius: 0.5rem;
      max-height: 150px;
      overflow-y: auto;
    }

    .swap-date-option {
      padding: 0.5rem 1rem;
      border: 1px solid var(--border-color);
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
      border-radius: var(--radius);
      box-shadow: var(--shadow-md);
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
      color: var(--secondary-text);
      padding: 0.3rem 0;
    }

    .jdp-day {
      text-align: center;
      padding: 0.4rem 0;
      border-radius: 0.4rem;
      cursor: pointer;
      background: var(--bg-color);
      border: 1px solid var(--border-color);
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
  </style>
</head>

<body>
  <div id="header-placeholder"></div>
  <main>
    <h1>
      <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
        <line x1="16" x2="16" y1="2" y2="6" />
        <line x1="8" x2="8" y1="2" y2="6" />
        <line x1="3" x2="21" y1="10" y2="10" />
      </svg>
      <span>مدیریت برنامه شیفت‌ها</span>
    </h1>
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
            <option value="remote">دورکار</option>
            <option value="off">عدم حضور</option>
            <option value="leave">مرخصی</option>
            <option value="swap">جابجایی شیفت</option>
            <option value="custom">سایر موارد</option>
          </select>
        </div>
        <div class="form-group" id="custom-status-group" style="display: none;">
          <label for="custom-shift-status">وضعیت سفارشی</label>
          <input type="text" id="custom-shift-status" placeholder="مثلا: ماموریت، آموزش، یا 10:00 - 19:00" />
        </div>
        <div id="swap-controls-group" style="display: none;">
          <div class="form-group">
            <label for="swap-expert-select">جابجایی با:</label>
            <select id="swap-expert-select"></select>
          </div>
          <div class="form-group">
            <label>در تاریخ (روزهای عدم حضور همکار):</label>
            <div id="swap-date-list">
              <p style="color: var(--secondary-text);">ابتدا یک همکار را انتخاب کنید.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button id="modal-cancel-btn" class="btn btn-secondary">انصراف</button>
        <button id="modal-save-btn" class="btn btn-primary">ذخیره تغییرات</button>
      </div>
    </div>
  </div>
  <div id="toast-container"></div>
  <div id="footer-placeholder"></div>
  <script src="/js/header.js"></script>
  <script>
    let allExperts = [];
    let allAvailableDates = [];
    const modal = document.getElementById("edit-shift-modal");
    let currentEditingInfo = {
      expertId: null,
      date: null,
      isSwap: false,
      linkedTo: null
    };
    const shiftColorMap = new Map();
    const colorPalette = ["#E3F2FD", "#E8F5E9", "#FFF3E0", "#F3E5F5", "#FFEBEE", "#E0F7FA"];
    let nextColorIndex = 0;

    const ICONS = {
      'on-duty': `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>`,
      'off': `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
      'remote': `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`,
      'leave': `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
      'swap': `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 3 4 4-4 4"/><path d="M20 7H4"/><path d="m8 21-4-4 4-4"/><path d="M4 17h16"/></svg>`,
      'custom': `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>`,
      'unknown': `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>`
    };

    function jalaliToGregorian(jy, jm, jd) {
      jy += 1595;
      let days = -355668 + 365 * jy + ~~(jy / 33) * 8 + ~~(((jy % 33) + 3) / 4) + jd + (jm < 7 ? (jm - 1) * 31 : (jm - 7) * 30 + 186);
      let gy = 400 * ~~(days / 146097);
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
      let gd = days + 1;
      const sal_a = [0, 31, (gy % 4 === 0 && gy % 100 !== 0) || gy % 400 === 0 ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
      let gm = 0;
      for (; gm < 13 && gd > sal_a[gm]; gm++) gd -= sal_a[gm];
      return new Date(gy, gm - 1, gd);
    }

    function toPersian(date) {
      return date.toLocaleDateString("fa-IR-u-nu-latn").split("/").map(p => parseInt(p, 10));
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
      return jm <= 6 ? 31 : jm <= 11 ? 30 : isJalaliLeap(jy) ? 30 : 29;
    }

    class JalaliDatePicker {
      constructor(inputId, altId) {
        this.input = document.getElementById(inputId);
        this.alt = document.getElementById(altId);
        if (!this.input || !this.alt) return;
        const [jy, jm, jd] = toPersian(new Date());
        this.jy = jy;
        this.jm = jm;
        this.jd = jd;
        this.pop = document.createElement("div");
        this.pop.className = "jdp-popover jdp-hidden";
        document.body.appendChild(this.pop);
        this.boundClickOutside = e => {
          if (!this.pop.contains(e.target) && e.target !== this.input) this.hide();
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
        const firstG = jalaliToGregorian(this.jy, this.jm, 1);
        const firstWeekday = (firstG.getDay() + 1) % 7;
        const daysInMonth = jalaliMonthLength(this.jy, this.jm);
        let html = `<div class="jdp-header"><button class="jdp-nav-btn" data-nav="-1">&rarr;</button><div>${new Intl.DateTimeFormat("fa-IR", { month: "long" }).format(firstG)} ${new Intl.DateTimeFormat("fa-IR-u-nu-latn", { year: "numeric" }).format(firstG)}</div><button class="jdp-nav-btn" data-nav="1">&larr;</button></div><div class="jdp-grid">${["ش", "ی", "د", "س", "چ", "پ", "ج"].map(w => `<div class="jdp-weekday">${w}</div>`).join("")}`;
        for (let i = 0; i < firstWeekday; i++) html += `<div class="jdp-day other"></div>`;
        for (let d = 1; d <= daysInMonth; d++) html += `<div class="jdp-day" data-day="${d}">${new Intl.NumberFormat("fa-IR").format(d)}</div>`;
        this.pop.innerHTML = html + `</div>`;
        this.pop.querySelectorAll("[data-nav]").forEach(btn => btn.addEventListener("click", e => this.nav(parseInt(e.currentTarget.dataset.nav, 10))));
        this.pop.querySelectorAll("[data-day]").forEach(cell => cell.addEventListener("click", e => {
          const d = parseInt(e.currentTarget.dataset.day, 10);
          this.input.value = formatJalaliDisplay(this.jy, this.jm, d);
          this.alt.value = formatISO(jalaliToGregorian(this.jy, this.jm, d));
          this.hide();
          if (typeof applyFilters === "function") applyFilters();
        }));
      }
      setInitialFromGregorian(date) {
        const [jy, jm, jd] = toPersian(date);
        this.jy = jy;
        this.jm = jm;
        this.jd = jd;
        this.input.value = formatJalaliDisplay(jy, jm, jd);
        this.alt.value = formatISO(date);
      }
    }

    function showToast(message, type = 'success', duration = 4000) {
      const container = document.getElementById('toast-container');
      const toast = document.createElement('div');
      toast.className = `toast toast-${type}`;
      toast.textContent = message;
      container.appendChild(toast);
      setTimeout(() => toast.classList.add('show'), 10);
      setTimeout(() => {
        toast.classList.remove('show');
        toast.addEventListener('transitionend', () => toast.remove());
      }, duration);
    }

    document.addEventListener("DOMContentLoaded", initializePage);

    async function initializePage() {
      document.getElementById("loader").style.display = "block";
      try {
        const response = await fetch("/php/get-shifts.php?v=" + new Date().getTime());
        if (!response.ok) throw new Error(`فایل shifts.json یافت نشد (کد: ${response.status})`);
        const data = await response.json();
        allExperts = data.experts || [];
        if (allExperts.length === 0) {
          document.getElementById("loader").textContent = "هیچ اطلاعاتی برای نمایش وجود ندارد.";
          return;
        }
        populateFilterControls();
        setupEventListeners();
        const today = new Date();
        const nextWeek = new Date();
        nextWeek.setDate(today.getDate() + 7);
        new JalaliDatePicker("startDate", "startDateAlt").setInitialFromGregorian(today);
        new JalaliDatePicker("endDate", "endDateAlt").setInitialFromGregorian(nextWeek);
        applyFilters();
      } catch (error) {
        document.getElementById("loader").textContent = `خطا در بارگذاری اولیه: ${error.message}`;
        console.error(error);
      }
    }

    function setupEventListeners() {
      ["startDateAlt", "endDateAlt", "expertSelect1", "expertSelect2"].forEach(id => document.getElementById(id).addEventListener("change", applyFilters));
      document.getElementById("modal-cancel-btn").addEventListener("click", closeEditModal);
      document.getElementById("modal-save-btn").addEventListener("click", saveShiftUpdate);
      document.getElementById("shift-status-select").addEventListener("change", e => {
        const customGroup = document.getElementById("custom-status-group");
        const swapGroup = document.getElementById("swap-controls-group");
        customGroup.style.display = e.target.value === "custom" ? "block" : "none";
        swapGroup.style.display = e.target.value === "swap" ? "block" : "none";
        if (e.target.value === "swap" && !currentEditingInfo.isSwap) {
          populateSwapExpertSelect();
          document.getElementById("swap-date-list").innerHTML = `<p style="color: var(--secondary-text);">ابتدا یک همکار را انتخاب کنید.</p>`;
        }
      });
      document.getElementById("swap-expert-select").addEventListener("change", e => {
        if (e.target.value && e.target.value !== 'none') populateSwapDates(e.target.value);
        else document.getElementById("swap-date-list").innerHTML = `<p style="color: var(--secondary-text);">ابتدا یک همکار را انتخاب کنید.</p>`;
      });
      document.getElementById('swap-date-list').addEventListener('click', e => {
        if (e.target.classList.contains('swap-date-option')) {
          document.querySelectorAll('.swap-date-option').forEach(el => el.classList.remove('selected'));
          e.target.classList.add('selected');
        }
      });
      document.getElementById("schedule-container").addEventListener("click", e => {
        const cell = e.target.closest(".editable-cell");
        if (cell) openEditModal(cell.dataset.expertId, cell.dataset.date);
      });
    }

    function getShiftDetails(shiftEntry) {
      if (typeof shiftEntry === 'object' && shiftEntry !== null && shiftEntry.status === 'swap') {
        return {
          status: 'swap',
          displayText: shiftEntry.displayText,
          isSwap: true,
          linkedTo: shiftEntry.linkedTo
        };
      }
      const status = shiftEntry || 'unknown';
      let displayText = status;
      switch (status) {
        case 'on-duty':
          displayText = 'حضور';
          break;
        case 'remote':
          displayText = 'دورکار';
          break;
        case 'off':
          displayText = 'عدم حضور';
          break;
        case 'leave':
          displayText = 'مرخصی';
          break;
        case 'unknown':
          displayText = '-';
          break;
      }
      return {
        status,
        displayText,
        isSwap: false,
        linkedTo: null
      };
    }

    function openEditModal(expertId, date) {
      const expert = allExperts.find(exp => exp.id == expertId);
      const shiftDetails = getShiftDetails(expert.shifts[date]);
      currentEditingInfo = {
        expertId,
        date,
        isSwap: shiftDetails.isSwap,
        linkedTo: shiftDetails.linkedTo
      };
      document.getElementById("modal-expert-name").textContent = expert.name;
      document.getElementById("modal-shift-date").textContent = new Date(date).toLocaleDateString("fa-IR");
      const statusSelect = document.getElementById("shift-status-select");
      document.getElementById("custom-status-group").style.display = "none";
      document.getElementById("swap-controls-group").style.display = "none";
      document.getElementById("custom-shift-status").value = "";
      if (shiftDetails.isSwap) {
        statusSelect.value = "swap";
        document.getElementById("swap-controls-group").style.display = "block";
        populateSwapExpertSelect(shiftDetails.linkedTo.expertId);
        populateSwapDates(shiftDetails.linkedTo.expertId, shiftDetails.linkedTo.date);
      } else if (['on-duty', 'off', 'leave', 'remote'].includes(shiftDetails.status)) {
        statusSelect.value = shiftDetails.status;
      } else if (shiftDetails.status !== 'unknown') {
        statusSelect.value = 'custom';
        document.getElementById("custom-status-group").style.display = 'block';
        document.getElementById('custom-shift-status').value = shiftDetails.status;
      } else {
        statusSelect.value = 'on-duty';
      }
      modal.classList.add("visible");
    }

    function populateSwapExpertSelect(selectedExpertId = 'none') {
      const select = document.getElementById('swap-expert-select');
      let optionsHtml = '<option value="none">-- انتخاب کنید --</option>';
      allExperts.filter(exp => String(exp.id) !== String(currentEditingInfo.expertId))
        .sort((a, b) => a.name.localeCompare(b.name, 'fa'))
        .forEach(expert => optionsHtml += `<option value="${expert.id}">${expert.name}</option>`);
      select.innerHTML = optionsHtml;
      select.value = selectedExpertId;
    }

    function populateSwapDates(expertB_id, selectedDate = null) {
      const dateListContainer = document.getElementById('swap-date-list');
      const expertB = allExperts.find(exp => String(exp.id) === String(expertB_id));
      if (!expertB) return;
      const availableDates = Object.entries(expertB.shifts)
        .filter(([_, status]) => getShiftDetails(status).status === 'off')
        .map(([date]) => date);
      if (selectedDate && !availableDates.includes(selectedDate)) availableDates.push(selectedDate);
      availableDates.sort();
      if (availableDates.length === 0) {
        dateListContainer.innerHTML = `<p style="color: var(--secondary-text);">این همکار روز خالی برای جابجایی ندارد.</p>`;
        return;
      }
      let datesHtml = '';
      availableDates.forEach(date => {
        const jalaliDate = new Date(date).toLocaleDateString('fa-IR');
        datesHtml += `<div class="swap-date-option ${date === selectedDate ? 'selected' : ''}" data-date="${date}">${jalaliDate}</div>`;
      });
      dateListContainer.innerHTML = datesHtml;
    }
    async function saveShiftUpdate() {
      const statusSelectValue = document.getElementById("shift-status-select").value;
      let requestBody = {};
      const wantsSwap = (statusSelectValue === 'swap');
      if (currentEditingInfo.isSwap && wantsSwap) {
        const newExpertB_id = document.getElementById('swap-expert-select').value;
        const selectedDateEl = document.querySelector('.swap-date-option.selected');
        if (newExpertB_id === 'none' || !selectedDateEl) {
          showToast('لطفاً همکار و تاریخ جدید برای جابجایی را انتخاب کنید.', 'error');
          return;
        }
        const newDateY = selectedDateEl.dataset.date;
        if (String(currentEditingInfo.linkedTo.expertId) === newExpertB_id && currentEditingInfo.linkedTo.date === newDateY) {
          showToast('تغییری در جابجایی ایجاد نشده است.', 'info');
          closeEditModal();
          return;
        }
        requestBody = {
          action: 'modify_swap',
          expertA_id: currentEditingInfo.expertId,
          dateX: currentEditingInfo.date,
          newExpertB_id,
          newDateY,
          oldLinkedExpertId: currentEditingInfo.linkedTo.expertId,
          oldLinkedDate: currentEditingInfo.linkedTo.date
        };
      } else if (currentEditingInfo.isSwap && !wantsSwap) {
        let newStatus = statusSelectValue;
        if (newStatus === 'custom') newStatus = document.getElementById('custom-shift-status').value.trim();
        if (!newStatus) {
          showToast("لطفاً وضعیت جدید را مشخص کنید.", 'error');
          return;
        }
        requestBody = {
          action: 'revert_and_update',
          expertId: currentEditingInfo.expertId,
          date: currentEditingInfo.date,
          newStatus,
          linkedExpertId: currentEditingInfo.linkedTo.expertId,
          linkedDate: currentEditingInfo.linkedTo.date
        };
      } else if (wantsSwap) {
        const expertB_id = document.getElementById('swap-expert-select').value;
        const selectedDateEl = document.querySelector('.swap-date-option.selected');
        if (expertB_id === 'none' || !selectedDateEl) {
          showToast('لطفاً همکار و تاریخ مورد نظر برای جابجایی را انتخاب کنید.', 'error');
          return;
        }
        const dateY = selectedDateEl.dataset.date;
        requestBody = {
          action: 'swap',
          expertA_id: currentEditingInfo.expertId,
          dateX: currentEditingInfo.date,
          expertB_id,
          dateY
        };
      } else {
        let newStatus = statusSelectValue;
        if (newStatus === 'custom') newStatus = document.getElementById('custom-shift-status').value.trim();
        if (!newStatus) {
          showToast("لطفاً وضعیت سفارشی را وارد کنید.", 'error');
          return;
        }
        requestBody = {
          action: 'update',
          expertId: currentEditingInfo.expertId,
          date: currentEditingInfo.date,
          status: newStatus
        };
      }
      try {
        const response = await fetch("/php/update-shift.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "fetch"
          },
          credentials: "same-origin",
          body: JSON.stringify(requestBody)
        });
        if (response.status === 401 || response.status === 403) {
          window.location.href = "/auth/login.html";
          return;
        }
        const result = await response.json();
        if (!response.ok || result.success === false) throw new Error(result.message || "خطا در ذخیره‌سازی.");
        showToast(result.message, 'success');
        closeEditModal();
        await refreshDataAndRender();
      } catch (error) {
        showToast(`ذخیره ناموفق بود: ${error.message}`, 'error');
      }
    }
    async function refreshDataAndRender() {
      try {
        const response = await fetch("/php/get-shifts.php?v=" + new Date().getTime());
        if (!response.ok) {
          console.error("Failed to refresh data from server.");
          return;
        }
        const data = await response.json();
        allExperts = (data && Array.isArray(data.experts)) ? data.experts : [];
        const allDatesSet = new Set();
        allExperts.forEach(expert => Object.keys(expert.shifts).forEach(date => allDatesSet.add(date)));
        allAvailableDates = Array.from(allDatesSet).sort();
        applyFilters();
      } catch (error) {
        showToast('خطا در به‌روزرسانی جدول.', 'error');
      }
    }

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
      const dailyCounts = {};
      const totalCounts = {
        onDuty: {},
        offDuty: {},
        leave: {}
      };
      datesToRender.forEach(date => {
        dailyCounts[date] = {};
        totalCounts.onDuty[date] = 0;
        totalCounts.offDuty[date] = 0;
        totalCounts.leave[date] = 0;
        allExperts.forEach(expert => {
          const shiftDetails = getShiftDetails(expert.shifts[date]);
          const shiftTime = expert["shifts-time"];
          const status = shiftDetails.status;
          if (shiftTime) {
            if (!dailyCounts[date][shiftTime]) dailyCounts[date][shiftTime] = {
              onDuty: 0,
              offDuty: 0,
              leave: 0
            };
            if (status === 'off' || (status === 'swap' && shiftDetails.displayText.startsWith('عدم'))) dailyCounts[date][shiftTime].offDuty++;
            else if (status === 'leave') dailyCounts[date][shiftTime].leave++;
            else if (status !== 'unknown') dailyCounts[date][shiftTime].onDuty++;
          }
          if (status === 'off' || (status === 'swap' && shiftDetails.displayText.startsWith('عدم'))) totalCounts.offDuty[date]++;
          else if (status === 'leave') totalCounts.leave[date]++;
          else if (status !== 'unknown') totalCounts.onDuty[date]++;
        });
      });

      let tableHtml = `<table><thead><tr><th>نام کارشناس</th><th>ساعت شیفت</th><th>تایم استراحت</th>`;
      datesToRender.forEach(date => {
        const d = new Date(date);
        tableHtml += `<th>${d.toLocaleDateString("fa-IR", { weekday: "short" })}<br>${d.toLocaleDateString("fa-IR", { day: "numeric", month: "short" })}<br><span style="font-size: 0.8rem; color: #6c757d; font-weight: 400;">${date}</span></th>`;
      });
      tableHtml += "</tr></thead><tbody>";
      expertsToRender.forEach(expert => {
        tableHtml += `<tr><td>${expert.name}</td><td ${getShiftStyle(expert["shifts-time"])}>${expert["shifts-time"] || "-"}</td><td>${expert["break-time"] || "-"}</td>`;
        datesToRender.forEach(date => {
          const shiftDetails = getShiftDetails(expert.shifts[date]);
          let statusClass = '';
          let icon = '';
          if (shiftDetails.isSwap) {
            statusClass = 'status-swap';
            icon = ICONS['swap'];
          } else {
            const classMap = {
              'on-duty': 'status-on-duty',
              'remote': 'status-remote',
              'off': 'status-off',
              'leave': 'status-leave',
              'unknown': 'status-unknown'
            };
            statusClass = classMap[shiftDetails.status] || 'status-custom';
            icon = ICONS[shiftDetails.status] || ICONS['custom'];
          }
          tableHtml += `<td class="editable-cell" data-expert-id="${expert.id}" data-date="${date}"><span class="status ${statusClass}">${icon} ${shiftDetails.displayText}</span></td>`;
        });
        tableHtml += "</tr>";
      });

      const totalColumns = datesToRender.length + 3;
      const uniqueShiftTimes = [...new Set(allExperts.map(e => e["shifts-time"]).filter(Boolean))].sort();
      if (uniqueShiftTimes.length > 0) {
        tableHtml += `<tr class="summary-separator"><td colspan="${totalColumns}">خلاصه وضعیت به تفکیک شیفت</td></tr>`;
        uniqueShiftTimes.forEach(shiftTime => {
          tableHtml += `<tr class="summary-row"><td ${getShiftStyle(shiftTime)}>حاضرین در شیفت ${shiftTime}</td><td>-</td><td>-</td>${datesToRender.map(date => `<td><span class="summary-count">${(dailyCounts[date][shiftTime] || {}).onDuty || 0}</span></td>`).join('')}</tr>`;
          tableHtml += `<tr class="summary-row"><td ${getShiftStyle(shiftTime)}>عدم حضور در شیفت ${shiftTime}</td><td>-</td><td>-</td>${datesToRender.map(date => `<td><span class="summary-count" style="color: var(--danger-color);">${(dailyCounts[date][shiftTime] || {}).offDuty || 0}</span></td>`).join('')}</tr>`;
          tableHtml += `<tr class="summary-row"><td ${getShiftStyle(shiftTime)}>مرخصی در شیفت ${shiftTime}</td><td>-</td><td>-</td>${datesToRender.map(date => `<td><span class="summary-count" style="color: var(--warning-color);">${(dailyCounts[date][shiftTime] || {}).leave || 0}</span></td>`).join('')}</tr>`;
        });
      }
      tableHtml += `<tr class="summary-separator"><td colspan="${totalColumns}">جمع‌بندی کل روزانه</td></tr>`;
      tableHtml += `<tr class="summary-row"><td style="background-color: var(--success-light);">مجموع کل کارشناسان حاضر</td><td>-</td><td>-</td>${datesToRender.map(date => `<td><span class="summary-count">${totalCounts.onDuty[date] || 0}</span></td>`).join('')}</tr>`;
      tableHtml += `<tr class="summary-row"><td style="background-color: var(--danger-light);">مجموع کل عدم حضور</td><td>-</td><td>-</td>${datesToRender.map(date => `<td><span class="summary-count" style="color: var(--danger-color);">${totalCounts.offDuty[date] || 0}</span></td>`).join('')}</tr>`;
      tableHtml += `<tr class="summary-row"><td style="background-color: var(--warning-light);">مجموع کل مرخصی</td><td>-</td><td>-</td>${datesToRender.map(date => `<td><span class="summary-count" style="color: var(--warning-color);">${totalCounts.leave[date] || 0}</span></td>`).join('')}</tr>`;

      container.innerHTML = tableHtml + "</tbody></table>";
    }

    function applyFilters() {
      const startDate = document.getElementById("startDateAlt").value,
        endDate = document.getElementById("endDateAlt").value;
      const expert1 = document.getElementById("expertSelect1").value,
        expert2 = document.getElementById("expertSelect2").value;
      const selectedExpertIds = new Set();
      if (expert1 !== "none") selectedExpertIds.add(expert1);
      if (expert2 !== "none") selectedExpertIds.add(expert2);
      const filteredDates = allAvailableDates.filter(date => (!startDate || date >= startDate) && (!endDate || date <= endDate));
      let filteredExperts = allExperts;
      if (selectedExpertIds.size > 0) filteredExperts = allExperts.filter(expert => selectedExpertIds.has(String(expert.id)));
      filteredExperts.sort((a, b) => (a["shifts-time"] || "").localeCompare(b["shifts-time"] || ""));
      renderTable(filteredExperts, filteredDates);
    }

    function closeEditModal() {
      modal.classList.remove("visible");
    }

    function getShiftStyle(shiftTime) {
      if (!shiftTime) return "";
      if (!shiftColorMap.has(shiftTime)) {
        shiftColorMap.set(shiftTime, colorPalette[nextColorIndex]);
        nextColorIndex = (nextColorIndex + 1) % colorPalette.length;
      }
      return `style="background-color: ${shiftColorMap.get(shiftTime)};"`;
    }

    function populateFilterControls() {
      const expertSelect1 = document.getElementById("expertSelect1"),
        expertSelect2 = document.getElementById("expertSelect2");
      let optionsHtml = '<option value="none">-- هیچکدام --</option>';
      allExperts.sort((a, b) => a.name.localeCompare(b.name, "fa")).forEach(expert => optionsHtml += `<option value="${expert.id}">${expert.name}</option>`);
      expertSelect1.innerHTML = optionsHtml;
      expertSelect2.innerHTML = optionsHtml;
      expertSelect1.value = "none";
      expertSelect2.value = "none";
      const allDatesSet = new Set();
      allExperts.forEach(expert => Object.keys(expert.shifts).forEach(date => allDatesSet.add(date)));
      allAvailableDates = Array.from(allDatesSet).sort();
    }
  </script>
</body>

</html>
