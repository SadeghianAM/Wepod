<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>مدیریت و گزارش‌گیری شیفت‌ها</title>
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
      --swap-color: #e8eaf6;
      --swap-text-color: #3f51b5;
      --danger-color: #dc3545;
      --danger-light: #fbebec;
      --success-color: #28a745;
      --success-light: #e9f7eb;
      --warning-color: #ffc107;
      --warning-light: #fff8e7;
      --info-color: #17a2b8;
      --info-light: #e8f6f8;
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
      margin-bottom: 1rem;
      color: var(--primary-dark);
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: .75rem;
    }

    .icon {
      width: 1.1em;
      height: 1.1em;
      stroke-width: 2.2;
      vertical-align: -0.15em;
    }

    .view-switcher {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 2rem;
      border-bottom: 2px solid var(--border-color);
    }

    .view-switcher button {
      padding: 0.8rem 1.5rem;
      font-size: 1rem;
      font-weight: 600;
      border: none;
      background-color: transparent;
      cursor: pointer;
      color: var(--secondary-text);
      border-bottom: 3px solid transparent;
      transition: all 0.2s;
    }

    .view-switcher button:hover {
      color: var(--primary-dark);
    }

    .view-switcher button.active {
      color: var(--primary-color);
      border-bottom-color: var(--primary-color);
    }

    .view-section {
      display: none;
    }

    .view-section.active {
      display: block;
    }

    .shift-viewer-container.active {
      display: flex;
    }

    .shift-viewer-container {
      gap: 2rem;
      align-items: flex-start;
    }

    .expert-sidebar {
      flex: 0 0 280px;
      background-color: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-color);
      height: calc(100vh - 250px);
      overflow-y: auto;
    }

    .expert-sidebar h2 {
      padding: 1.25rem 1.5rem;
      font-size: 1.2rem;
      font-weight: 700;
      border-bottom: 1px solid var(--border-color);
      position: sticky;
      top: 0;
      background-color: var(--card-bg);
      z-index: 2;
    }

    .expert-list {
      list-style: none;
      padding: 0.5rem;
    }

    .expert-list li {
      padding: 0.9rem 1.25rem;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      transition: background-color 0.2s, color 0.2s;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .expert-list li:hover {
      background-color: var(--primary-light);
    }

    .expert-list li.active {
      background-color: var(--primary-color);
      color: var(--header-text);
      font-weight: 700;
    }

    .calendar-view-main {
      flex-grow: 1;
    }

    .placeholder {
      background-color: var(--card-bg);
      border-radius: var(--radius);
      padding: 4rem 2rem;
      text-align: center;
      font-size: 1.1rem;
      color: var(--secondary-text);
      border: 2px dashed var(--border-color);
    }

    #calendar-view-main-content {
      background-color: var(--card-bg);
      border-radius: var(--radius);
      padding: 2rem;
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-color);
    }

    #user-shift-info {
      background-color: var(--primary-light);
      padding: 1.5rem;
      border-radius: var(--radius);
      margin-bottom: 2rem;
      display: flex;
      justify-content: space-around;
      flex-wrap: wrap;
      gap: 1rem;
      border: 1px solid var(--primary-color);
    }

    #user-shift-info p {
      font-size: 1rem;
      color: var(--secondary-text);
    }

    #user-shift-info span {
      font-weight: 700;
      color: var(--primary-dark);
      margin-right: 0.25rem;
    }

    #calendar-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    #calendar-controls button {
      background-color: var(--primary-color);
      color: white;
      border: none;
      padding: 0.6rem 1.2rem;
      border-radius: 0.5rem;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.2s, opacity 0.2s;
    }

    #calendar-controls button:hover {
      background-color: var(--primary-dark);
    }

    #calendar-controls button:disabled {
      background-color: #a5d8d1;
      opacity: 0.6;
      cursor: not-allowed;
    }

    #current-month-year {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary-dark);
    }

    #calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 5px;
      padding: 1rem;
      border-radius: var(--radius);
      border: 1px solid var(--border-color);
    }

    .calendar-header {
      text-align: center;
      font-weight: 600;
      padding: 0.8rem 0;
      color: var(--secondary-text);
      border-bottom: 2px solid var(--border-color);
    }

    .calendar-day {
      min-height: 120px;
      border: 1px solid #f0f0f0;
      border-radius: 0.5rem;
      padding: 0.5rem;
      font-size: 0.9rem;
      background-color: #fafafa;
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
      cursor: pointer;
      transition: box-shadow .2s, border-color .2s;
    }

    .calendar-day:not(.other-month):hover {
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
      border-color: var(--primary-color);
    }

    .calendar-day.other-month {
      background-color: #f8f9fa;
      color: #ced4da;
      cursor: default;
    }

    .calendar-day .shift-info {
      padding: 0.4rem 0.5rem;
      border-radius: 0.3rem;
      color: white;
      text-align: center;
      font-weight: 500;
      pointer-events: none;
    }

    .swapped-shift-details {
      font-size: 0.8rem;
      background-color: #f0f0f0;
      border-radius: 0.3rem;
      padding: 0.4rem;
      text-align: center;
      color: #333;
      border: 1px solid #ddd;
      line-height: 1.5;
      margin-top: 0.2rem;
      pointer-events: none;
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

    #report-view {
      background-color: var(--card-bg);
      border-radius: var(--radius);
      padding: 2rem;
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-color);
    }

    #report-output-container,
    #table-output-container {
      width: 100%;
      overflow-x: auto;
    }

    .report-table,
    .table-view-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    .report-table th,
    .report-table td,
    .table-view-table th,
    .table-view-table td {
      padding: 0.9rem 1rem;
      text-align: center;
      border: 1px solid var(--border-color);
      white-space: nowrap;
    }

    .report-table thead th,
    .table-view-table thead th {
      background-color: var(--bg-color);
      color: var(--secondary-text);
      font-weight: 600;
      position: sticky;
      top: 0;
      z-index: 1;
    }

    .report-table tbody tr:nth-child(even),
    .table-view-table tbody tr:nth-child(even) {
      background-color: var(--bg-color);
    }

    .report-table tbody tr:hover,
    .table-view-table tbody tr:hover {
      background-color: var(--primary-light);
    }

    .report-table td:first-child,
    .table-view-table td:first-child {
      font-weight: 600;
      text-align: right;
    }

    .count-cell {
      font-weight: 700;
      font-size: 1.1rem;
    }

    .table-view-table .editable-cell {
      cursor: pointer;
      transition: background-color 0.2s, box-shadow 0.2s;
    }

    .table-view-table .editable-cell:hover {
      background-color: #e3f2fd;
      box-shadow: inset 0 0 0 2px var(--info-color);
    }

    .status {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: .5em;
      color: white;
    }

    .status .icon {
      width: 1.2em;
      height: 1.2em;
    }

    .status-on-duty {
      background-color: #28a745;
    }

    .status-off {
      background-color: #dc3545;
    }

    .status-remote {
      background-color: #d8b100;
    }

    .status-special {
      background-color: #5487df;
      color: #212529;
    }

    .status-leave {
      background-color: #ec7433;
      color: white;
    }

    .status-swap {
      background-color: var(--swap-color);
      color: var(--swap-text-color);
    }

    .status-custom {
      background-color: #e0e0e0;
      color: #424242;
    }

    .status-unknown {
      background-color: var(--border-color);
      color: var(--secondary-text);
    }

    .summary-separator {
      cursor: pointer;
    }

    .summary-separator .arrow {
      display: inline-block;
      transition: transform 0.2s ease-in-out;
      width: 20px;
    }

    .summary-separator.expanded .arrow {
      transform: rotate(90deg);
    }

    .collapsed {
      display: none;
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
      <span>مدیریت شیفت‌ها</span>
    </h1>
    <nav class="view-switcher">
      <button id="calendar-view-btn" class="active">📅 نمای تقویم</button>
      <button id="report-view-btn">📊 نمای گزارش</button>
      <button id="table-view-btn">▦ نمای جدولی</button>
    </nav>
    <div id="calendar-view" class="shift-viewer-container view-section active">
      <aside id="expert-list-container" class="expert-sidebar"></aside>
      <div id="calendar-view-main" class="calendar-view-main">
        <p class="placeholder">لطفاً یک کارشناس را از لیست انتخاب کنید تا تقویم شیفت نمایش داده شود.</p>
      </div>
    </div>
    <div id="report-view" class="view-section">
      <div class="report-filters filters-container">
        <div class="filter-group">
          <label for="reportStartDate">از تاریخ:</label>
          <input type="text" id="reportStartDate" placeholder="انتخاب تاریخ" autocomplete="off" />
          <input type="hidden" id="reportStartDateAlt" />
        </div>
        <div class="filter-group">
          <label for="reportEndDate">تا تاریخ:</label>
          <input type="text" id="reportEndDate" placeholder="انتخاب تاریخ" autocomplete="off" />
          <input type="hidden" id="reportEndDateAlt" />
        </div>
        <div class="filter-group">
          <button id="generate-report-btn" class="btn btn-primary">تولید گزارش</button>
        </div>
      </div>
      <div id="report-output-container">
        <p class="placeholder">برای مشاهده گزارش، لطفاً بازه زمانی مورد نظر را انتخاب و روی دکمه "تولید گزارش" کلیک کنید.</p>
      </div>
    </div>
    <div id="table-view" class="view-section">
      <div class="filters-container">
        <div class="filter-group">
          <label for="tableStartDate">از تاریخ:</label>
          <input type="text" id="tableStartDate" placeholder="انتخاب تاریخ" autocomplete="off" />
          <input type="hidden" id="tableStartDateAlt" />
        </div>
        <div class="filter-group">
          <label for="tableEndDate">تا تاریخ:</label>
          <input type="text" id="tableEndDate" placeholder="انتخاب تاریخ" autocomplete="off" />
          <input type="hidden" id="tableEndDateAlt" />
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
      <div id="table-output-container"></div>
    </div>
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
          <div class="form-group"><label for="swap-expert-select">جابجایی با:</label><select id="swap-expert-select"></select></div>
          <div class="form-group"><label>در تاریخ (روزهای عدم حضور همکار):</label>
            <div id="swap-date-list">
              <p style="color: var(--secondary-text);">ابتدا یک همکار را انتخاب کنید.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer"><button id="modal-cancel-btn" class="btn btn-secondary">انصراف</button><button id="modal-save-btn" class="btn btn-primary">ذخیره تغییرات</button></div>
    </div>
  </div>
  <div id="toast-container"></div>
  <div id="footer-placeholder"></div>
  <script src="/js/header.js"></script>
  <script src="/js/jalali-datepicker.js"></script>
  <script>
    let allExperts = [];
    let allAvailableDates = [];
    let currentSelectedExpert = null;
    let currentCalendarDate = new Date();
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

    function fetchNoCache(url, options = {}) {
      const timestamp = new Date().getTime();
      const separator = url.includes("?") ? "&" : "?";
      const urlWithCacheBust = `${url}${separator}t=${timestamp}`;
      return fetch(urlWithCacheBust, options);
    }

    document.addEventListener("DOMContentLoaded", initializePage);

    async function initializePage() {
      try {
        const response = await fetchNoCache('/php/get-shifts.php');
        if (!response.ok) throw new Error(`خطا در دریافت اطلاعات شیفت (کد: ${response.status})`);
        const data = await response.json();
        allExperts = data.experts || [];
        if (allExperts.length === 0) {
          document.querySelector('main').innerHTML = "<h1>مدیریت شیفت‌ها</h1><p>هیچ اطلاعاتی برای نمایش وجود ندارد.</p>";
          return;
        }
        const allDatesSet = new Set();
        allExperts.forEach(expert => Object.keys(expert.shifts).forEach(date => allDatesSet.add(date)));
        allAvailableDates = Array.from(allDatesSet).sort();

        renderExpertList();
        populateTableFilters();
        setupEventListeners();
        setupDatePickers();
        applyTableFilters();
      } catch (error) {
        document.querySelector('main').innerHTML = `<h1>مدیریت شیفت‌ها</h1><p style="color: #dc3545;">خطا در بارگذاری اطلاعات: ${error.message}</p>`;
      }
    }

    function setupEventListeners() {
      document.getElementById('calendar-view-btn').addEventListener('click', () => switchView('calendar'));
      document.getElementById('report-view-btn').addEventListener('click', () => switchView('report'));
      document.getElementById('table-view-btn').addEventListener('click', () => switchView('table'));
      document.getElementById('expert-list-container').addEventListener('click', handleExpertClick);
      document.getElementById('generate-report-btn').addEventListener('click', generateDailyReport);
      document.getElementById("table-output-container").addEventListener("click", e => {
        const cell = e.target.closest(".editable-cell");
        if (cell) {
          openEditModal(cell.dataset.expertId, cell.dataset.date);
          return;
        }
        const header = e.target.closest(".summary-separator");
        if (header) {
          header.classList.toggle('expanded');
          const targetClass = 'shift-summary-details';
          let nextRow = header.nextElementSibling;
          while (nextRow && nextRow.classList.contains(targetClass)) {
            nextRow.classList.toggle('collapsed');
            nextRow = nextRow.nextElementSibling;
          }
        }
      });
      ["expertSelect1", "expertSelect2"].forEach(id => document.getElementById(id).addEventListener("change", applyTableFilters));
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
    }

    function setupDatePickers() {
      const today = new Date();
      const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
      const nextWeek = new Date();
      nextWeek.setDate(today.getDate() + 7);

      new JalaliDatePicker("reportStartDate", "reportStartDateAlt");
      new JalaliDatePicker("reportEndDate", "reportEndDateAlt");
      new JalaliDatePicker("tableStartDate", "tableStartDateAlt");
      new JalaliDatePicker("tableEndDate", "tableEndDateAlt");

      const [todayJ] = [toPersian(today)];
      const [firstDayJ] = [toPersian(firstDayOfMonth)];
      const [nextWeekJ] = [toPersian(nextWeek)];

      document.getElementById('reportStartDate').value = formatJalaliDisplay(firstDayJ[0], firstDayJ[1], firstDayJ[2]);
      document.getElementById('reportStartDateAlt').value = formatISO(firstDayOfMonth);

      document.getElementById('reportEndDate').value = formatJalaliDisplay(todayJ[0], todayJ[1], todayJ[2]);
      document.getElementById('reportEndDateAlt').value = formatISO(today);

      document.getElementById('tableStartDate').value = formatJalaliDisplay(todayJ[0], todayJ[1], todayJ[2]);
      document.getElementById('tableStartDateAlt').value = formatISO(today);

      document.getElementById('tableEndDate').value = formatJalaliDisplay(nextWeekJ[0], nextWeekJ[1], nextWeekJ[2]);
      document.getElementById('tableEndDateAlt').value = formatISO(nextWeek);

      document.getElementById('tableStartDateAlt').addEventListener('change', applyTableFilters);
      document.getElementById('tableEndDateAlt').addEventListener('change', applyTableFilters);
    }

    function switchView(viewName) {
      document.querySelectorAll('.view-section').forEach(sec => sec.classList.remove('active'));
      document.querySelectorAll('.view-switcher button').forEach(btn => btn.classList.remove('active'));
      if (viewName === 'calendar') {
        document.getElementById('calendar-view').classList.add('active');
        document.getElementById('calendar-view-btn').classList.add('active');
      } else if (viewName === 'report') {
        document.getElementById('report-view').classList.add('active');
        document.getElementById('report-view-btn').classList.add('active');
      } else if (viewName === 'table') {
        document.getElementById('table-view').classList.add('active');
        document.getElementById('table-view-btn').classList.add('active');
      }
    }

    function renderExpertList() {
      const container = document.getElementById('expert-list-container');
      const sortedExperts = [...allExperts].sort((a, b) => a.name.localeCompare(b.name, 'fa'));
      let listHtml = '<h2>کارشناسان</h2><ul class="expert-list">';
      sortedExperts.forEach(expert => {
        listHtml += `<li data-expert-id="${expert.id}">${expert.name}</li>`;
      });
      listHtml += '</ul>';
      container.innerHTML = listHtml;
    }

    function handleExpertClick(event) {
      const target = event.target.closest('li[data-expert-id]');
      if (!target) return;
      document.querySelectorAll('.expert-list li').forEach(li => li.classList.remove('active'));
      target.classList.add('active');
      const expertId = target.dataset.expertId;
      currentSelectedExpert = allExperts.find(e => String(e.id) === expertId);
      if (currentSelectedExpert) {
        currentCalendarDate = new Date();
        renderExpertShiftView(currentSelectedExpert);
      }
    }

    function hasShiftsInMonth(date, userShifts) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const monthPrefix = `${year}-${month}-`;
      return Object.keys(userShifts).some((shiftDate) => shiftDate.startsWith(monthPrefix));
    }

    function getShiftDetails(shiftEntry) {
      if (typeof shiftEntry === "object" && shiftEntry !== null && shiftEntry.status === "swap") {
        return {
          status: "swap",
          displayText: shiftEntry.displayText,
          isSwap: true,
          linkedTo: shiftEntry.linkedTo
        };
      }
      const status = shiftEntry || "unknown";
      let displayText = status;
      switch (status) {
        case "on-duty":
          displayText = "حضور";
          break;
        case "remote":
          displayText = "دورکاری";
          break;
        case "off":
          displayText = "عدم حضور";
          break;
        case "leave":
          displayText = "مرخصی";
          break;
        case "unknown":
          displayText = "-";
          break;
      }
      return {
        status,
        displayText,
        isSwap: false,
        linkedTo: null
      };
    }

    function renderCalendar(date, shiftsData) {
      const container = document.getElementById("calendar-container");
      const weekDays = ["ش", "ی", "د", "س", "چ", "پ", "ج"];
      let html = '<div id="calendar-grid">';
      weekDays.forEach(day => html += `<div class="calendar-header">${day}</div>`);
      const [pYear, pMonth] = toPersian(date);
      const firstDayOfPersianMonth = jalaliToGregorian(pYear, pMonth, 1);
      const daysInMonth = jalaliMonthLength(pYear, pMonth);
      const calendarStartDate = new Date(firstDayOfPersianMonth);
      const offsetToSaturday = (firstDayOfPersianMonth.getDay() + 1) % 7;
      calendarStartDate.setDate(calendarStartDate.getDate() - offsetToSaturday);
      const lastDayOfPersianMonth = jalaliToGregorian(pYear, pMonth, daysInMonth);
      const calendarEndDate = new Date(lastDayOfPersianMonth);
      const offsetToFriday = (5 - lastDayOfPersianMonth.getDay() + 7) % 7;
      calendarEndDate.setDate(calendarEndDate.getDate() + offsetToFriday);
      let loopDate = new Date(calendarStartDate);
      while (loopDate <= calendarEndDate) {
        const [, currentPMonth] = toPersian(loopDate);
        const isOtherMonth = currentPMonth !== pMonth;
        const dateString = formatISO(loopDate);
        const shiftDetails = getShiftDetails(shiftsData[dateString]);
        let statusClass = "",
          statusText = shiftDetails.displayText,
          extraDetailsHtml = "";
        if (shiftDetails.isSwap) {
          statusClass = "status-swap";
          if (shiftDetails.displayText.includes("حضور")) {
            const originalExpert = allExperts.find(exp => String(exp.id) === String(shiftDetails.linkedTo.expertId));
            if (originalExpert) {
              const originalShiftTime = originalExpert["shifts-time"] || "نامشخص";
              const originalBreakTime = originalExpert["break-time"] || "نامشخص";
              extraDetailsHtml = `<div class="swapped-shift-details"><div>⏰ ${originalShiftTime}</div><div>🌮 ${originalBreakTime}</div></div>`;
            }
          }
        } else {
          const classMap = {
            "on-duty": "status-on-duty",
            "remote": "status-remote",
            "off": "status-off",
            "leave": "status-leave",
            "unknown": "status-unknown"
          };
          statusClass = classMap[shiftDetails.status] || "status-special";
        }
        if (shiftDetails.status === "unknown") statusText = "";
        html += `<div class="calendar-day ${isOtherMonth ? "other-month" : ""}" ${!isOtherMonth ? `data-date="${dateString}"` : ''}><div class="day-number">${loopDate.toLocaleDateString("fa-IR",{ day: "numeric" })}</div>${statusText ? `<div class="shift-info ${statusClass}">${statusText}</div>` : ""}${extraDetailsHtml}</div>`;
        loopDate.setDate(loopDate.getDate() + 1);
      }
      html += "</div>";
      container.innerHTML = html;
    }

    function renderExpertShiftView(expertData) {
      const container = document.getElementById("calendar-view-main");
      const monthName = currentCalendarDate.toLocaleDateString('fa-IR', {
        month: 'long'
      });
      const year = new Intl.NumberFormat('fa-IR', {
        useGrouping: false
      }).format(currentCalendarDate.toLocaleDateString('fa-IR-u-nu-latn', {
        year: 'numeric'
      }));
      const prevMonthDate = new Date(currentCalendarDate);
      prevMonthDate.setMonth(prevMonthDate.getMonth() - 1);
      const nextMonthDate = new Date(currentCalendarDate);
      nextMonthDate.setMonth(nextMonthDate.getMonth() + 1);
      const hasPrevShifts = hasShiftsInMonth(prevMonthDate, expertData.shifts);
      const hasNextShifts = hasShiftsInMonth(nextMonthDate, expertData.shifts);
      const breakTime = expertData["break-time"];
      let breakLabel = "ساعت استراحت",
        breakValue = breakTime || "تعیین نشده";
      if (breakTime && breakTime.includes(" - ")) {
        const endTime = breakTime.split(" - ")[1].trim();
        if (endTime) breakLabel = endTime <= "17:00" ? "🌮 تایم ناهار" : "🌮 تایم شام";
      }
      container.innerHTML = `<div id="calendar-view-main-content"><div id="user-shift-info"><p>👤 کارشناس: <span>${expertData.name}</span></p><p>⏰ ساعت شیفت: <span>${expertData["shifts-time"] || "تعیین نشده"}</span></p><p>${breakLabel}: <span>${breakValue}</span></p></div><div id="calendar-controls"><button id="prev-month" ${hasPrevShifts ? "" : "disabled"}>&rarr; ماه قبل</button><span id="current-month-year">${monthName} ${year}</span><button id="next-month" ${hasNextShifts ? "" : "disabled"}>ماه بعد &larr;</button></div><div id="calendar-container"></div></div>`;
      document.getElementById("prev-month").addEventListener("click", () => {
        currentCalendarDate.setMonth(currentCalendarDate.getMonth() - 1);
        renderExpertShiftView(currentSelectedExpert);
      });
      document.getElementById("next-month").addEventListener("click", () => {
        currentCalendarDate.setMonth(currentCalendarDate.getMonth() + 1);
        renderExpertShiftView(currentSelectedExpert);
      });
      document.getElementById("calendar-container").addEventListener("click", (e) => {
        const dayCell = e.target.closest('.calendar-day:not(.other-month)');
        if (dayCell && dayCell.dataset.date) {
          openEditModal(currentSelectedExpert.id, dayCell.dataset.date);
        }
      });
      renderCalendar(currentCalendarDate, expertData.shifts);
    }

    function generateDailyReport() {
      const startDateStr = document.getElementById('reportStartDateAlt').value;
      const endDateStr = document.getElementById('reportEndDateAlt').value;
      if (!startDateStr || !endDateStr) {
        showToast("لطفاً هر دو تاریخ شروع و پایان را انتخاب کنید.", "error");
        return;
      }
      const startDate = new Date(startDateStr);
      const endDate = new Date(endDateStr);
      if (startDate > endDate) {
        showToast("تاریخ شروع نمی‌تواند بعد از تاریخ پایان باشد.", "error");
        return;
      }
      const dailyReportData = {};
      let currentDate = new Date(startDate);
      while (currentDate <= endDate) {
        const dateString = formatISO(currentDate);
        const stats = {
          onDuty: 0,
          remote: 0,
          off: 0,
          leave: 0,
          custom: 0
        };
        allExperts.forEach(expert => {
          const shiftEntry = expert.shifts[dateString];
          const details = getShiftDetails(shiftEntry);
          switch (details.status) {
            case 'on-duty':
              stats.onDuty++;
              break;
            case 'remote':
              stats.remote++;
              break;
            case 'off':
              stats.off++;
              break;
            case 'leave':
              stats.leave++;
              break;
            case 'swap':
              if (details.displayText.includes('حضور')) stats.onDuty++;
              else if (details.displayText.includes('عدم')) stats.off++;
              break;
            default:
              if (details.status !== 'unknown') stats.custom++;
              break;
          }
        });
        dailyReportData[dateString] = stats;
        currentDate.setDate(currentDate.getDate() + 1);
      }
      renderDailyReportTable(dailyReportData);
    }

    function renderDailyReportTable(data) {
      const container = document.getElementById('report-output-container');
      const dates = Object.keys(data).sort();
      if (dates.length === 0) {
        container.innerHTML = '<p class="placeholder">اطلاعاتی برای نمایش در این بازه زمانی یافت نشد.</p>';
        return;
      }
      let tableHtml = `<table class="report-table">
                <thead><tr><th>تاریخ</th><th>✅ حضور</th><th>💻 دورکاری</th><th>❌ عدم حضور</th><th>✈️ مرخصی</th><th>📋 سایر</th></tr></thead>
                <tbody>`;
      dates.forEach(dateString => {
        const item = data[dateString];
        const displayDate = new Date(dateString).toLocaleDateString('fa-IR', {
          weekday: 'long',
          day: 'numeric',
          month: 'long'
        });
        tableHtml += `<tr>
                        <td>${displayDate}</td>
                        <td class="count-cell">${item.onDuty}</td>
                        <td class="count-cell">${item.remote}</td>
                        <td class="count-cell">${item.off}</td>
                        <td class="count-cell">${item.leave}</td>
                        <td class="count-cell">${item.custom}</td>
                    </tr>`;
      });
      tableHtml += `</tbody></table>`;
      container.innerHTML = tableHtml;
    }

    function populateTableFilters() {
      const expertSelect1 = document.getElementById("expertSelect1"),
        expertSelect2 = document.getElementById("expertSelect2");
      let optionsHtml = '<option value="none">-- هیچکدام --</option>';
      allExperts.sort((a, b) => a.name.localeCompare(b.name, "fa")).forEach(expert => optionsHtml += `<option value="${expert.id}">${expert.name}</option>`);
      expertSelect1.innerHTML = optionsHtml;
      expertSelect2.innerHTML = optionsHtml;
      expertSelect1.value = "none";
      expertSelect2.value = "none";
    }

    function applyTableFilters() {
      const startDate = document.getElementById("tableStartDateAlt").value,
        endDate = document.getElementById("tableEndDateAlt").value;
      const expert1 = document.getElementById("expertSelect1").value,
        expert2 = document.getElementById("expertSelect2").value;
      const selectedExpertIds = new Set();
      if (expert1 !== "none") selectedExpertIds.add(expert1);
      if (expert2 !== "none") selectedExpertIds.add(expert2);
      const filteredDates = allAvailableDates.filter(date => (!startDate || date >= startDate) && (!endDate || date <= endDate));
      let filteredExperts = allExperts;
      if (selectedExpertIds.size > 0) filteredExperts = allExperts.filter(expert => selectedExpertIds.has(String(expert.id)));
      filteredExperts.sort((a, b) => (a["shifts-time"] || "").localeCompare(b["shifts-time"] || ""));
      renderTableView(filteredExperts, filteredDates);
    }

    function getShiftStyle(shiftTime) {
      if (!shiftTime) return "";
      if (!shiftColorMap.has(shiftTime)) {
        shiftColorMap.set(shiftTime, colorPalette[nextColorIndex]);
        nextColorIndex = (nextColorIndex + 1) % colorPalette.length;
      }
      return `style="background-color: ${shiftColorMap.get(shiftTime)};"`;
    }

    function renderTableView(expertsToRender, datesToRender) {
      const container = document.getElementById("table-output-container");
      if (!expertsToRender || expertsToRender.length === 0 || !datesToRender || datesToRender.length === 0) {
        container.innerHTML = "<p class='placeholder'>هیچ داده‌ای مطابق با فیلترهای شما یافت نشد.</p>";
        return;
      }
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
      let tableHtml = `<table class="table-view-table"><thead><tr><th>نام کارشناس</th><th>ساعت شیفت</th><th>تایم استراحت</th>`;
      datesToRender.forEach(date => {
        const d = new Date(date);
        tableHtml += `<th>${d.toLocaleDateString("fa-IR", { weekday: "short" })}<br>${d.toLocaleDateString("fa-IR", { day: "numeric", month: "short" })}</th>`;
      });
      tableHtml += "</tr></thead><tbody>";
      expertsToRender.forEach(expert => {
        tableHtml += `<tr><td style="text-align:right;">${expert.name}</td><td ${getShiftStyle(expert["shifts-time"])}>${expert["shifts-time"] || "-"}</td><td>${expert["break-time"] || "-"}</td>`;
        datesToRender.forEach(date => {
          const shiftDetails = getShiftDetails(expert.shifts[date]);
          let statusClass = '',
            icon = '';
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
        tableHtml += `<tr class="summary-separator"><td colspan="${totalColumns}"><span class="arrow">▶</span> خلاصه وضعیت به تفکیک شیفت</td></tr>`;
        uniqueShiftTimes.forEach(shiftTime => {
          tableHtml += `<tr class="summary-row shift-summary-details collapsed"><td ${getShiftStyle(shiftTime)}>حاضرین در شیفت ${shiftTime}</td><td>-</td><td>-</td>${datesToRender.map(date => `<td><span class="summary-count">${(dailyCounts[date][shiftTime] || {}).onDuty || 0}</span></td>`).join('')}</tr>`;
          tableHtml += `<tr class="summary-row shift-summary-details collapsed"><td ${getShiftStyle(shiftTime)}>عدم حضور در شیفت ${shiftTime}</td><td>-</td><td>-</td>${datesToRender.map(date => `<td><span class="summary-count" style="color: var(--danger-color);">${(dailyCounts[date][shiftTime] || {}).offDuty || 0}</span></td>`).join('')}</tr>`;
          tableHtml += `<tr class="summary-row shift-summary-details collapsed"><td ${getShiftStyle(shiftTime)}>مرخصی در شیفت ${shiftTime}</td><td>-</td><td>-</td>${datesToRender.map(date => `<td><span class="summary-count" style="color: var(--warning-color);">${(dailyCounts[date][shiftTime] || {}).leave || 0}</span></td>`).join('')}</tr>`;
        });
      }
      tableHtml += `<tr class="summary-separator"><td colspan="${totalColumns}">جمع‌بندی کل روزانه</td></tr>`;
      tableHtml += `<tr class="summary-row"><td style="background-color: var(--success-light);">مجموع کل کارشناسان حاضر</td><td>-</td><td>-</td>${datesToRender.map(date => `<td><span class="summary-count">${totalCounts.onDuty[date] || 0}</span></td>`).join('')}</tr>`;
      tableHtml += `<tr class="summary-row"><td style="background-color: var(--danger-light);">مجموع کل عدم حضور</td><td>-</td><td>-</td>${datesToRender.map(date => `<td><span class="summary-count" style="color: var(--danger-color);">${totalCounts.offDuty[date] || 0}</span></td>`).join('')}</tr>`;
      tableHtml += `<tr class="summary-row"><td style="background-color: var(--warning-light);">مجموع کل مرخصی</td><td>-</td><td>-</td>${datesToRender.map(date => `<td><span class="summary-count" style="color: #a17400;">${totalCounts.leave[date] || 0}</span></td>`).join('')}</tr>`;
      container.innerHTML = tableHtml + "</tbody></table>";
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

    function closeEditModal() {
      modal.classList.remove("visible");
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
      allExperts.filter(exp => String(exp.id) !== String(currentEditingInfo.expertId)).sort((a, b) => a.name.localeCompare(b.name, 'fa')).forEach(expert => optionsHtml += `<option value="${expert.id}">${expert.name}</option>`);
      select.innerHTML = optionsHtml;
      select.value = selectedExpertId;
    }

    function populateSwapDates(expertB_id, selectedDate = null) {
      const dateListContainer = document.getElementById('swap-date-list');
      const expertB = allExperts.find(exp => String(exp.id) === String(expertB_id));
      if (!expertB) return;
      const availableDates = Object.entries(expertB.shifts).filter(([_, status]) => getShiftDetails(status).status === 'off').map(([date]) => date);
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
          showToast('تغییری در جابجایی ایجاد نشده است.');
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
        const response = await fetchNoCache("/php/get-shifts.php");
        if (!response.ok) {
          console.error("Failed to refresh data from server.");
          return;
        }
        const data = await response.json();
        allExperts = (data && Array.isArray(data.experts)) ? data.experts : [];
        if (document.getElementById('calendar-view').classList.contains('active') && currentSelectedExpert) {
          currentSelectedExpert = allExperts.find(e => String(e.id) === String(currentSelectedExpert.id)) || null;
          if (currentSelectedExpert) {
            renderExpertShiftView(currentSelectedExpert);
          } else {
            document.getElementById("calendar-view-main").innerHTML = '<p class="placeholder">کارشناس انتخاب شده دیگر در دسترس نیست. لطفاً فرد دیگری را انتخاب کنید.</p>';
          }
        } else if (document.getElementById('table-view').classList.contains('active')) {
          const allDatesSet = new Set();
          allExperts.forEach(expert => Object.keys(expert.shifts).forEach(date => allDatesSet.add(date)));
          allAvailableDates = Array.from(allDatesSet).sort();
          applyTableFilters();
        }
      } catch (error) {
        showToast('خطا در به‌روزرسانی تقویم.', 'error');
      }
    }
  </script>
</body>

</html>
