<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>مدیریت اختلالات مرکز تماس</title>

  <style>
    /* =======================
       Design Tokens
       ======================= */
    :root {
      --primary-color: #00ae70;
      --primary-dark: #089863;
      --primary-light: #e6f7f2;
      --bg-color: #f8fcf9;
      --text-color: #222;
      --secondary-text-color: #555;
      --muted-text: #7a7a7a;
      --card-bg: #ffffff;
      --header-text: #ffffff;
      --shadow-color-light: rgba(0, 174, 112, 0.07);
      --shadow-color-medium: rgba(0, 174, 112, 0.12);
      --danger-color: #d93025;
      --orange-color: #f9ab00;
      --border-radius: .75rem;
      --border-color: #e9e9e9;
      --focus-ring: 0 0 0 3px rgba(0, 174, 112, 0.15);
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
      font-family: "Vazirmatn", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
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
      line-height: 1.6;
    }

    /* --- HEADER & FOOTER --- */
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
    }

    header {
      height: 70px;
    }

    footer {
      height: 60px;
      font-size: .85rem;
      margin-top: auto;
    }

    header h1 {
      font-size: 1.2rem;
      font-weight: 700;
    }

    #today-date,
    #user-info {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      font-size: 1rem;
      opacity: .85;
      font-weight: 500;
      white-space: nowrap;
    }

    #today-date {
      inset-inline-start: 1.5rem;
    }

    #user-info {
      inset-inline-end: 1.5rem;
      cursor: pointer;
      padding: .5rem .8rem;
      border-radius: .5rem;
      transition: background-color .2s;
    }

    #user-info:hover {
      background-color: rgba(255, 255, 255, .15);
    }

    /* =======================
       Page Layout
       ======================= */
    main {
      flex-grow: 1;
      padding: 2rem;
      max-width: 1800px;
      width: 100%;
      margin: 0 auto;
    }

    .page-intro {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1rem;
      align-items: center;
      margin-bottom: 1rem;
    }

    .page-intro h2 {
      font-size: 1.6rem;
      color: var(--primary-dark);
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: .5rem;
    }

    .page-intro p {
      color: var(--muted-text);
      font-size: .95rem;
    }

    /* =======================
       Form Card
       ======================= */
    .form-container {
      background-color: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: calc(var(--border-radius) + .25rem);
      box-shadow: 0 8px 28px var(--shadow-color-light);
      margin-bottom: 2rem;
      overflow: clip;
    }

    .form-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      padding: 1rem 1.25rem;
      background: linear-gradient(180deg, #ffffff 0%, #f9fffc 100%);
      border-bottom: 1px solid var(--border-color);
    }

    .form-header .title {
      display: flex;
      align-items: center;
      gap: .5rem;
      font-weight: 800;
      color: var(--primary-dark);
    }

    .form-header .title .emoji-icon {
      font-size: 1.15rem;
    }

    .form-header .subtitle {
      color: var(--secondary-text-color);
      font-size: .9rem;
      margin-top: .25rem;
    }

    .form-content {
      padding: 1.25rem 1.25rem .75rem;
    }

    .form-section {
      border: 1px dashed #e8efec;
      border-radius: .75rem;
      padding: 1rem;
      margin-bottom: 1rem;
      background: #fcfefd;
    }

    .form-section .section-title {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      font-weight: 700;
      color: var(--primary-dark);
      margin-bottom: .75rem;
      font-size: .98rem;
    }

    .form-section .section-title .emoji-icon {
      font-size: 1rem;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1rem;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: .4rem;
    }

    .label-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .5rem;
    }

    .form-group label {
      font-weight: 700;
      font-size: .92rem;
      color: #2b3a32;
    }

    .hint {
      color: var(--muted-text);
      font-size: .8rem;
    }

    .input-wrap {
      position: relative;
      display: flex;
      align-items: center;
    }

    .emoji-leading {
      position: absolute;
      inset-inline-start: .6rem;
      font-size: 1.05rem;
      opacity: .85;
      pointer-events: none;
      line-height: 1;
    }

    .input-wrap input,
    .input-wrap select,
    .input-wrap textarea,
    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 10px 12px;
      padding-inline-start: 2.25rem;
      /* space for emoji */
      border: 1px solid #dfe5e2;
      border-radius: .65rem;
      font-size: 1rem;
      background-color: #fcfcfc;
      transition: border-color .2s, box-shadow .2s, background .2s;
    }

    .form-group textarea {
      min-height: 96px;
      resize: vertical;
    }

    .form-group input[type="text"]:read-only {
      cursor: pointer;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      border-color: var(--primary-color);
      outline: none;
      box-shadow: var(--focus-ring);
      background: #fff;
    }

    .req::after {
      content: " *";
      color: var(--danger-color);
      font-weight: 900;
    }

    /* Actions */
    .form-actions {
      position: sticky;
      bottom: 0;
      display: flex;
      gap: .75rem;
      justify-content: flex-end;
      padding: .9rem 1.25rem;
      background: linear-gradient(180deg, rgba(255, 255, 255, .7) 0%, rgba(255, 255, 255, .95) 60%, #fff 100%);
      border-top: 1px solid var(--border-color);
      backdrop-filter: blur(6px);
      z-index: 5;
    }

    .form-actions .btn {
      padding: 10px 16px;
      border: none;
      border-radius: .55rem;
      cursor: pointer;
      font-size: .98rem;
      font-weight: 800;
      color: #fff;
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      transition: transform .15s ease, box-shadow .2s ease, background-color .2s ease;
    }

    .btn-save {
      background-color: var(--primary-color);
      box-shadow: 0 6px 16px rgba(0, 174, 112, .22);
    }

    .btn-save:hover {
      background-color: var(--primary-dark);
      transform: translateY(-1px);
    }

    .btn-cancel {
      background-color: #6c757d;
    }

    .btn-cancel:hover {
      background-color: #5a6268;
    }

    /* Table header & table (unchanged) */
    .table-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    .btn-excel {
      background-color: #28a745;
      color: #fff;
      padding: .75rem 1.5rem;
      border: none;
      border-radius: .5rem;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 700;
      transition: all .2s;
      box-shadow: 0 4px 10px rgba(40, 167, 69, .2);
    }

    .btn-excel:hover {
      background-color: #218838;
      transform: translateY(-2px);
    }

    .table-container {
      width: 100%;
      overflow-x: auto;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      box-shadow: 0 2px 12px var(--shadow-color-light);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 1400px;
    }

    th,
    td {
      padding: 1rem 1.2rem;
      text-align: center;
      border-bottom: 1px solid var(--border-color);
      white-space: nowrap;
    }

    td.description-cell {
      white-space: normal;
      min-width: 300px;
      text-align: right;
    }

    thead th {
      background-color: var(--primary-light);
      color: var(--primary-dark);
      font-weight: 700;
      position: sticky;
      top: 0;
      z-index: 2;
    }

    tbody tr:last-child td {
      border-bottom: none;
    }

    tbody tr:hover {
      background-color: var(--primary-light);
    }

    .status {
      padding: .4em 1em;
      border-radius: 1rem;
      font-weight: 700;
      font-size: .85rem;
      color: #fff;
      min-width: 100px;
      display: inline-block;
    }

    .status-open {
      background-color: #dc3545;
    }

    .status-in-progress {
      background-color: #ffc107;
      color: #212529;
    }

    .status-resolved {
      background-color: #28a745;
    }

    /* DatePicker styles (for اسکریپت شمسی) */
    .jdp-popover {
      position: absolute;
      background: #fff;
      border: 1px solid var(--border-color);
      border-radius: .5rem;
      box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
      padding: .75rem;
      width: 280px;
      z-index: 9999;
    }

    .jdp-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: .5rem;
      font-weight: 700;
      color: var(--primary-dark);
    }

    .jdp-nav-btn {
      background: var(--primary-color);
      color: #fff;
      border: none;
      padding: .25rem .6rem;
      border-radius: .4rem;
      cursor: pointer;
    }

    .jdp-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 4px;
    }

    .jdp-weekday {
      text-align: center;
      font-size: .85rem;
      color: var(--secondary-text-color);
      padding: .3rem 0;
    }

    .jdp-day {
      text-align: center;
      padding: .4rem 0;
      border-radius: .4rem;
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

    /* ========= Action buttons in table "عملیات" ========= */
    .action-btn {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      padding: .5rem .9rem;
      border: none;
      border-radius: .5rem;
      font-size: .9rem;
      font-weight: 800;
      color: #fff;
      cursor: pointer;
      transition: transform .15s ease, box-shadow .2s ease, filter .2s ease, background-color .2s ease;
      box-shadow: 0 4px 10px var(--shadow-color-light);
      white-space: nowrap;
    }

    .action-btn:hover {
      transform: translateY(-1px);
      filter: saturate(1.05);
    }

    .action-btn:active {
      transform: translateY(0);
      filter: brightness(.98);
    }

    .action-btn:focus {
      outline: none;
    }

    .action-btn:focus-visible {
      box-shadow: 0 0 0 3px rgba(0, 174, 112, .18);
    }

    .edit-btn {
      background: #007bff;
    }

    .edit-btn:hover {
      background: #0069d9;
    }

    .delete-btn {
      background: #dc3545;
    }

    .delete-btn:hover {
      background: #c82333;
    }

    .edit-btn::before {
      content: "✏️";
      font-size: 1rem;
      line-height: 1;
    }

    .delete-btn::before {
      content: "🗑️";
      font-size: 1rem;
      line-height: 1;
    }

    .action-btn:disabled {
      opacity: .6;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
      filter: none;
    }

    /* Responsive */
    @media (max-width:992px) {
      .form-actions {
        justify-content: stretch;
      }

      .btn-excel {
        width: 100%;
      }
    }

    @media (max-width:768px) {
      main {
        padding: 1.5rem 1rem;
      }

      .page-intro h2 {
        font-size: 1.35rem;
      }

      .table-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
      }
    }

    @media (max-width:480px) {

      #today-date,
      #user-info {
        display: none;
      }

      .emoji-leading {
        display: none;
      }

      .input-wrap input,
      .input-wrap select,
      .input-wrap textarea {
        padding-inline-start: 12px;
      }
    }
  </style>
</head>

<body>
  <div id="header-placeholder"></div>

  <main>
    <!-- مقدمه بدون Badge مدت کل -->
    <div class="page-intro" aria-describedby="page-helper">
      <div>
        <h2>ثبت و ویرایش اختلالات</h2>
        <p id="page-helper">لطفاً فیلدهای ضروری را تکمیل کنید. تاریخ‌ها به صورت شمسی انتخاب می‌شوند و پس از تعیین زمان شروع/پایان، اطلاعات را ذخیره کنید.</p>
      </div>
    </div>

    <div class="form-container" role="region" aria-label="فرم ثبت اختلال">
      <div class="form-header">
        <div>
          <div class="title">
            <span class="emoji-icon">🧾</span>
            <span>اطلاعات اختلال</span>
          </div>
          <div class="subtitle">اطلاعات پایه، زمان‌بندی و توضیحات را وارد کنید.</div>
        </div>
      </div>

      <div class="form-content">
        <form id="disruptionForm" novalidate>
          <input type="hidden" id="recordId" name="id" />

          <!-- بخش: اطلاعات پایه -->
          <section class="form-section" aria-labelledby="sec-base">
            <div class="section-title" id="sec-base">
              <span class="emoji-icon">👤</span>
              <span>اطلاعات پایه</span>
            </div>
            <div class="form-grid">
              <div class="form-group">
                <div class="label-row">
                  <label for="dayOfWeek" class="req">روز هفته</label>
                  <span class="hint">به‌صورت خودکار از روی تاریخ شروع هم پر می‌شود.</span>
                </div>
                <div class="input-wrap">
                  <span class="emoji-leading" aria-hidden="true">🗓️</span>
                  <select id="dayOfWeek" name="dayOfWeek" required aria-required="true">
                    <option value="" disabled selected>انتخاب کنید</option>
                    <option value="شنبه">شنبه</option>
                    <option value="یکشنبه">یکشنبه</option>
                    <option value="دوشنبه">دوشنبه</option>
                    <option value="سه‌شنبه">سه‌شنبه</option>
                    <option value="چهارشنبه">چهارشنبه</option>
                    <option value="پنجشنبه">پنجشنبه</option>
                    <option value="جمعه">جمعه</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <div class="label-row">
                  <label for="subject" class="req">موضوع</label>
                  <span class="hint">قابل جستجو و انتخاب از لیست</span>
                </div>
                <div class="input-wrap">
                  <span class="emoji-leading" aria-hidden="true">📝</span>
                  <select id="subject" name="subject" required aria-required="true">
                    <option value="" disabled selected>انتخاب موضوع</option>
                    <!-- موارد توسط اسکریپت افزوده و با جستجو فیلتر می‌شود -->
                  </select>
                </div>
              </div>

              <div class="form-group">
                <div class="label-row">
                  <label for="status" class="req">وضعیت اختلال</label>
                  <span class="hint">وضعیت فعلی رسیدگی</span>
                </div>
                <div class="input-wrap">
                  <span class="emoji-leading" aria-hidden="true">✅</span>
                  <select id="status" name="status" required aria-required="true">
                    <option value="باز">باز</option>
                    <option value="درحال رسیدگی">درحال رسیدگی</option>
                    <option value="برطرف شده">برطرف شده</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <div class="label-row">
                  <label for="reportingTeam" class="req">تیم گزارش‌دهنده</label>
                  <span class="hint">منبع ثبت اختلال</span>
                </div>
                <div class="input-wrap">
                  <span class="emoji-leading" aria-hidden="true">👥</span>
                  <select id="reportingTeam" name="reportingTeam" required aria-required="true">
                    <option value="اعلام از سمت فنی">اعلام از سمت فنی</option>
                    <option value="اعلام از سمت مرکز تماس">اعلام از سمت مرکز تماس</option>
                  </select>
                </div>
              </div>
            </div>
          </section>

          <!-- بخش: زمان‌بندی (بدون «مدت کل») -->
          <section class="form-section" aria-labelledby="sec-time">
            <div class="section-title" id="sec-time">
              <span class="emoji-icon">🕒</span>
              <span>زمان‌بندی</span>
            </div>
            <div class="form-grid">
              <div class="form-group">
                <div class="label-row">
                  <label for="startDateDisplay" class="req">تاریخ شروع</label>
                  <span class="hint">برای انتخاب تاریخ کلیک کنید</span>
                </div>
                <div class="input-wrap">
                  <span class="emoji-leading" aria-hidden="true">📅</span>
                  <input type="text" id="startDateDisplay" placeholder="انتخاب تاریخ" autocomplete="off" readonly required aria-required="true" />
                </div>
                <input type="hidden" id="startDate" name="startDate" />
              </div>

              <div class="form-group">
                <div class="label-row">
                  <label for="startTime" class="req">ساعت شروع</label>
                  <span class="hint">برای انتخاب ساعت کلیک کنید</span>
                </div>
                <div class="input-wrap">
                  <span class="emoji-leading" aria-hidden="true">⏰</span>
                  <input type="time" id="startTime" name="startTime" required aria-required="true" />
                </div>
              </div>

              <div class="form-group">
                <div class="label-row">
                  <label for="endDateDisplay">تاریخ پایان</label>
                  <span class="hint">اختیاری</span>
                </div>
                <div class="input-wrap">
                  <span class="emoji-leading" aria-hidden="true">📅</span>
                  <input type="text" id="endDateDisplay" placeholder="انتخاب تاریخ" autocomplete="off" readonly />
                </div>
                <input type="hidden" id="endDate" name="endDate" />
              </div>

              <div class="form-group">
                <div class="label-row">
                  <label for="endTime">ساعت پایان</label>
                  <span class="hint">اختیاری</span>
                </div>
                <div class="input-wrap">
                  <span class="emoji-leading" aria-hidden="true">⏱️</span>
                  <input type="time" id="endTime" name="endTime" />
                </div>
              </div>
            </div>
          </section>

          <!-- بخش: توضیحات -->
          <section class="form-section" aria-labelledby="sec-desc">
            <div class="section-title" id="sec-desc">
              <span class="emoji-icon">💬</span>
              <span>توضیح اختلال</span>
            </div>
            <div class="form-group">
              <div class="label-row">
                <label for="description">توضیحات تکمیلی</label>
                <span class="hint">نمونه: دامنه اثر، سرویس درگیر، شماره تیکت و ...</span>
              </div>
              <div class="input-wrap">
                <span class="emoji-leading" aria-hidden="true">🗒️</span>
                <textarea id="description" name="description" rows="3" placeholder="توضیح مختصر و مفید درباره‌ی اختلال..."></textarea>
              </div>
            </div>
          </section>

          <!-- اکشن‌ها -->
          <div class="form-actions">
            <button type="submit" class="btn btn-save" title="ذخیره رکورد">
              <span aria-hidden="true">💾</span>
              <span>ذخیره رکورد</span>
            </button>
            <button type="button" id="clearBtn" class="btn btn-cancel" title="پاک کردن فرم">
              <span aria-hidden="true">🧹</span>
              <span>پاک کردن فرم</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ===== جدول (بدون تغییر در مارکاپ و نحوه نمایش) ===== -->
    <div class="table-header">
      <h2>لیست اختلالات ثبت‌شده</h2>
      <button id="exportExcelBtn" class="btn-excel">📊 خروجی Excel</button>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>روز</th>
            <th>موضوع</th>
            <th>وضعیت</th>
            <th>تاریخ شروع</th>
            <th>ساعت شروع</th>
            <th>تاریخ پایان</th>
            <th>ساعت پایان</th>
            <th>مجموع زمان</th>
            <th>تیم گزارش‌دهنده</th>
            <th>توضیح</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody id="disruptionTableBody"></tbody>
      </table>
    </div>
  </main>

  <div id="footer-placeholder"></div>
  <script src="/js/header.js"></script>
  <script>
    /* =======================
     Inject extra styles (TimePicker v2.1 & Subject Search)
     ======================= */
    (function injectExtraStyles() {
      if (document.getElementById("enhanced-popovers-styles")) return;
      const css = `
    /* ---- TimePicker v2.1 (fixed RTL order + LTR clock) ---- */
    .tp-popover{
      position:absolute;background:#fff;border:1px solid var(--border-color);
      border-radius:.75rem;box-shadow:0 10px 28px rgba(0,0,0,.12);
      padding:.75rem;z-index:10000;min-width:320px;max-width:360px
    }
    .tp-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem}
    .tp-title{font-weight:800;color:var(--primary-dark);display:flex;align-items:center;gap:.4rem}
    .tp-title .emoji{font-size:1rem}
    .tp-clock{font-weight:900;font-size:1.1rem;color:#2b3a32;direction:ltr;unicode-bidi:plaintext}
    .tp-columns{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;position:relative}
    .tp-col-heads{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin:.25rem 0 .35rem}
    .tp-col-head{ text-align:center;font-weight:800;color:#2b3a32;background:#f5f9f7;border:1px solid #e6ece9;border-radius:.5rem;padding:.3rem }
    /* ساعت ستون راست، دقیقه ستون چپ */
    .hours-col{ grid-column:2; }
    .minutes-col{ grid-column:1; }

    .tp-col{
      height:180px;overflow-y:auto;border:1px solid #e6ece9;border-radius:.6rem;background:#fafafa;
      scroll-snap-type: y mandatory; padding:.25rem
    }
    .tp-opt{
      height:36px;display:flex;align-items:center;justify-content:center;
      margin:.15rem 0;border-radius:.5rem;scroll-snap-align:center;cursor:pointer;user-select:none;direction:ltr
    }
    .tp-opt:hover{background:var(--primary-light)}
    .tp-opt.active{background:#e9fff6;border:1px solid #b9f0dc;font-weight:800}

    .tp-arrows{display:flex;justify-content:space-between;gap:.5rem;margin:.5rem 0}
    .tp-arrow-btn{
      flex:1;border:none;border-radius:.5rem;padding:.45rem .6rem;cursor:pointer;font-weight:800;background:#f0f3f2;color:#2b3a32
    }
    .tp-quick{display:flex;flex-wrap:wrap;gap:.4rem;margin:.4rem 0}
    .tp-quick-btn{border:none;border-radius:.5rem;padding:.35rem .6rem;background:#f4fbf8;color:#036f4b;font-weight:800;cursor:pointer}
    .tp-actions{display:flex;gap:.5rem;justify-content:space-between;margin-top:.5rem}
    .tp-btn{border:none;border-radius:.6rem;padding:.55rem .9rem;font-weight:800;cursor:pointer}
    .tp-btn-primary{background:var(--primary-color);color:#fff}
    .tp-btn-secondary{background:#f0f3f2;color:#2b3a32}
    .tp-btn-danger{background:#fbe9e8;color:#b3261e}
    .tp-btn:disabled{opacity:.6;cursor:not-allowed}

    /* ---- Subject search popover ---- */
    .sp-popover{
      position:absolute;background:#fff;border:1px solid var(--border-color);
      border-radius:.6rem;box-shadow:0 8px 24px rgba(0,0,0,.12);
      padding:.6rem;z-index:10000;min-width:260px;max-height:340px;overflow:auto
    }
    .sp-search{
      width:100%;padding:.55rem .75rem;border:1px solid #dfe5e2;border-radius:.55rem;margin-bottom:.5rem
    }
    .sp-item{
      padding:.5rem .6rem;border-radius:.5rem;cursor:pointer;white-space:nowrap
    }
    .sp-item:hover{background:var(--primary-light)}
    .sp-item.active{background:#e9fff6;border:1px solid #b9f0dc}
    `;
      const s = document.createElement("style");
      s.id = "enhanced-popovers-styles";
      s.textContent = css;
      document.head.appendChild(s);
    })();

    /* =======================
       Jalali Date Helpers
       ======================= */
    function jalaliToGregorian(jy, jm, jd) {
      var sal_a, gy, gm, gd, days;
      jy += 1595;
      days = -355668 +
        365 * jy +
        ~~(jy / 33) * 8 +
        ~~(((jy % 33) + 3) / 4) +
        jd +
        (jm < 7 ? (jm - 1) * 31 : (jm - 7) * 30 + 186);
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

    function formatJalaliDisplay(jy, jm, jd) {
      return `${jy}/${String(jm).padStart(2, "0")}/${String(jd).padStart(2, "0")}`;
    }

    function formatISO(date) {
      return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
    }

    function isJalaliLeap(jy) {
      return (((((((jy - 474) % 2820) + 2820) % 2820) + 474 + 38) * 682) % 2816) < 682;
    }

    function jalaliMonthLength(jy, jm) {
      if (jm <= 6) return 31;
      if (jm <= 11) return 30;
      return isJalaliLeap(jy) ? 30 : 29;
    }

    /* =======================
       Jalali DatePicker (no SVG)
       ======================= */
    class JalaliDatePicker {
      constructor(inputId, altId) {
        this.input = document.getElementById(inputId);
        this.alt = document.getElementById(altId);
        if (!this.input || !this.alt) return;
        const gNow = new Date();
        const [jy, jm] = toPersian(gNow);
        this.jy = jy;
        this.jm = jm;
        this.pop = document.createElement("div");
        this.pop.className = "jdp-popover jdp-hidden";
        document.body.appendChild(this.pop);
        this.boundClickOutside = (e) => {
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
        if (rect.left + 280 > window.innerWidth) this.pop.style.left = window.scrollX + rect.right - 280 + "px";
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
        let html = `
        <div class="jdp-header">
          <button type="button" class="jdp-nav-btn" data-nav="-1">«</button>
          <div>${new Intl.DateTimeFormat("fa-IR",{month:"long"}).format(firstG)}
              ${new Intl.DateTimeFormat("fa-IR-u-nu-latn",{year:"numeric"}).format(firstG)}</div>
          <button type="button" class="jdp-nav-btn" data-nav="1">»</button>
        </div>
        <div class="jdp-grid">
          ${weekDays.map(w=>`<div class="jdp-weekday">${w}</div>`).join("")}
      `;
        for (let i = 0; i < firstWeekday; i++) html += `<div class="jdp-day other"></div>`;
        for (let d = 1; d <= daysInMonth; d++) {
          html += `<div class="jdp-day" data-day="${d}">${new Intl.NumberFormat("fa-IR").format(d)}</div>`;
        }
        html += `</div>`;
        this.pop.innerHTML = html;
        this.pop.querySelectorAll("[data-nav]").forEach(btn => btn.addEventListener("click", (e) => this.nav(parseInt(e.currentTarget.dataset.nav, 10))));
        this.pop.querySelectorAll("[data-day]").forEach(cell => {
          cell.addEventListener("click", (e) => {
            const d = parseInt(e.currentTarget.dataset.day, 10);
            const gDate = jalaliToGregorian(this.jy, this.jm, d);
            this.input.value = formatJalaliDisplay(this.jy, this.jm, d);
            this.alt.value = formatISO(gDate);
            this.alt.dispatchEvent(new Event("change", {
              bubbles: true
            }));
            this.hide();
          });
        });
      }
    }

    /* =======================
       TimePicker v2.1 (scroll columns, RTL fixed)
       ======================= */
    class TimePicker {
      constructor(inputId) {
        this.input = document.getElementById(inputId);
        if (!this.input) return;
        this.input.readOnly = true; // جلوگیری از بازشدن پیکر بومی
        this.pop = document.createElement("div");
        this.pop.className = "tp-popover";
        this.pop.hidden = true;
        document.body.appendChild(this.pop);
        this.boundOutside = (e) => {
          if (!this.pop.contains(e.target) && e.target !== this.input) this.hide();
        };
        this.input.addEventListener("pointerdown", (e) => {
          e.preventDefault();
          this.show();
        });
        this.input.addEventListener("focus", () => this.show());
        this.input.addEventListener("click", () => this.show());
        window.addEventListener("resize", () => this.position());
      }
      parse() {
        const v = (this.input.value || "").trim();
        let h = 12,
          m = 0;
        if (/^\d{1,2}:\d{2}$/.test(v)) {
          const [hh, mm] = v.split(":").map(n => parseInt(n, 10));
          h = Math.max(0, Math.min(23, hh));
          m = Math.max(0, Math.min(59, mm));
        }
        this.h = h;
        this.m = m - (m % 5); // دقیقه‌ها ۵‌تایی
      }
      position() {
        const rect = this.input.getBoundingClientRect();
        this.pop.style.top = window.scrollY + rect.bottom + 6 + "px";
        this.pop.style.left = window.scrollX + rect.left + "px";
        const w = this.pop.offsetWidth || 340;
        if (rect.left + w > window.innerWidth) this.pop.style.left = window.scrollX + rect.right - w + "px";
      }
      show() {
        this.parse();
        this.render();
        this.position();
        this.pop.hidden = false;
        setTimeout(() => document.addEventListener("mousedown", this.boundOutside), 0);
      }
      hide() {
        this.pop.hidden = true;
        document.removeEventListener("mousedown", this.boundOutside);
      }
      setHour(h) {
        this.h = (h + 24) % 24;
        this.updateClock();
        this.highlight();
      }
      setMinute(m) {
        this.m = (m + 60) % 60;
        this.updateClock();
        this.highlight();
      }
      stepHour(d) {
        this.setHour(this.h + d);
        this.scrollToActive(this.hoursCol, this.h);
      }
      stepMinute(d) {
        this.setMinute(this.m + d * 5);
        this.scrollToActive(this.minutesCol, this.m / 5);
      }
      apply() {
        const val = `${String(this.h).padStart(2,"0")}:${String(this.m).padStart(2,"0")}`;
        this.input.value = val;
        this.input.dispatchEvent(new Event("change", {
          bubbles: true
        }));
        this.hide();
      }
      clear() {
        this.input.value = "";
        this.input.dispatchEvent(new Event("change", {
          bubbles: true
        }));
        this.hide();
      }
      now() {
        const d = new Date();
        this.h = d.getHours();
        this.m = Math.round(d.getMinutes() / 5) * 5 % 60;
        this.updateClock();
        this.highlight();
        this.scrollToActive(this.hoursCol, this.h);
        this.scrollToActive(this.minutesCol, this.m / 5);
      }
      updateClock() {
        if (this.clock) this.clock.textContent = `${String(this.h).padStart(2,"0")}:${String(this.m).padStart(2,"0")}`;
      }
      highlight() {
        this.pop.querySelectorAll(".tp-opt").forEach(el => el.classList.remove("active"));
        const hEl = this.pop.querySelector(`.tp-opt[data-hour="${this.h}"]`);
        const mEl = this.pop.querySelector(`.tp-opt[data-minute="${this.m}"]`);
        if (hEl) hEl.classList.add("active");
        if (mEl) mEl.classList.add("active");
      }
      makeColumn(type, items, extraClass) {
        const col = document.createElement("div");
        col.className = `tp-col ${extraClass||""}`;
        col.setAttribute("tabindex", "0");
        col.innerHTML = items.map(v => {
          const attr = type === "h" ? `data-hour="${v}"` : `data-minute="${v}"`;
          return `<div class="tp-opt" ${attr}>${String(v).padStart(2,"0")}</div>`;
        }).join("");
        col.addEventListener("click", (e) => {
          const t = e.target.closest(".tp-opt");
          if (!t) return;
          if (type === "h") this.setHour(parseInt(t.dataset.hour, 10));
          else this.setMinute(parseInt(t.dataset.minute, 10));
        });
        col.addEventListener("keydown", (e) => {
          if (type === "h") {
            if (e.key === "ArrowUp") {
              e.preventDefault();
              this.stepHour(-1);
            }
            if (e.key === "ArrowDown") {
              e.preventDefault();
              this.stepHour(1);
            }
          } else {
            if (e.key === "ArrowUp") {
              e.preventDefault();
              this.stepMinute(-1);
            }
            if (e.key === "ArrowDown") {
              e.preventDefault();
              this.stepMinute(1);
            }
          }
          if (e.key === "Enter") {
            e.preventDefault();
            this.apply();
          }
          if (e.key === "Escape") {
            e.preventDefault();
            this.hide();
          }
        });
        // snap-to-nearest after scroll stop
        col.addEventListener("scroll", () => {
          clearTimeout(this._snapt);
          this._snapt = setTimeout(() => {
            const idx = Math.round(col.scrollTop / 36); // ارتفاع هر گزینه ~36px
            const val = type === "h" ? idx : (idx * 5);
            if (type === "h") this.setHour(Math.max(0, Math.min(23, val)));
            else this.setMinute(Math.max(0, Math.min(55, val)));
          }, 120);
        });
        return col;
      }
      scrollToActive(col, index) {
        const y = index * 36;
        col.scrollTo({
          top: y,
          behavior: "smooth"
        });
      }
      render() {
        const hours = Array.from({
          length: 24
        }, (_, i) => i);
        const minutes = Array.from({
          length: 12
        }, (_, i) => i * 5);
        this.pop.innerHTML = `
        <div class="tp-header">
          <div class="tp-title"><span class="emoji">⏰</span><span>انتخاب ساعت</span></div>
          <div class="tp-clock"></div>
        </div>
        <div class="tp-col-heads">
          <div class="tp-col-head minutes-head">دقیقه</div>
          <div class="tp-col-head hours-head">ساعت</div>
        </div>
        <div class="tp-columns"></div>
        <div class="tp-arrows">
          <button type="button" class="tp-arrow-btn" data-m="-1">
            ⬆️ دقیقه
          </button>
          <button type="button" class="tp-arrow-btn" data-m="+1">
            ⬇️ دقیقه
          </button>
          <button type="button" class="tp-arrow-btn" data-h="-1">
            ⬆️ ساعت
          </button>
          <button type="button" class="tp-arrow-btn" data-h="+1">
            ⬇️ ساعت
          </button>
        </div>
        <div class="tp-quick">
          ${[0,15,30,45].map(v=>`<button type="button" class="tp-quick-btn" data-qm="${v}">:${String(v).padStart(2,"0")}</button>`).join("")}
        </div>
        <div class="tp-actions">
          <div style="display:flex;gap:.5rem">
            <button type="button" class="tp-btn tp-btn-secondary" data-now="1">🕒 اکنون</button>
            <button type="button" class="tp-btn tp-btn-danger" data-clear="1">🧹 پاک</button>
          </div>
          <button type="button" class="tp-btn tp-btn-primary" data-apply="1">✅ ثبت</button>
        </div>
      `;
        this.clock = this.pop.querySelector(".tp-clock");
        this.updateClock();

        const cols = this.pop.querySelector(".tp-columns");
        // ساعت ستون راست، دقیقه ستون چپ
        this.hoursCol = this.makeColumn("h", hours, "hours-col");
        this.minutesCol = this.makeColumn("m", minutes, "minutes-col");
        cols.appendChild(this.minutesCol);
        cols.appendChild(this.hoursCol);

        this.highlight();
        this.scrollToActive(this.hoursCol, this.h);
        this.scrollToActive(this.minutesCol, this.m / 5);

        this.pop.querySelectorAll("[data-h]").forEach(b => {
          b.addEventListener("click", () => this.stepHour(parseInt(b.dataset.h.replace("+", ""), 10)));
        });
        this.pop.querySelectorAll("[data-m]").forEach(b => {
          b.addEventListener("click", () => this.stepMinute(parseInt(b.dataset.m.replace("+", ""), 10)));
        });
        this.pop.querySelectorAll("[data-qm]").forEach(b => {
          b.addEventListener("click", () => {
            this.setMinute(parseInt(b.dataset.qm, 10));
            this.scrollToActive(this.minutesCol, this.m / 5);
          });
        });
        this.pop.querySelector("[data-apply]").addEventListener("click", () => this.apply());
        this.pop.querySelector("[data-clear]").addEventListener("click", () => this.clear());
        this.pop.querySelector("[data-now]").addEventListener("click", () => this.now());
      }
    }

    /* =======================
       SubjectPicker with search
       ======================= */
    class SubjectPicker {
      constructor(selectEl, items) {
        this.select = selectEl;
        this.items = items || [];
        this.pop = document.createElement("div");
        this.pop.className = "sp-popover";
        this.pop.hidden = true;
        document.body.appendChild(this.pop);
        this.boundOutside = (e) => {
          if (!this.pop.contains(e.target) && e.target !== this.select) this.hide();
        };
        this.select.addEventListener("mousedown", (e) => {
          e.preventDefault();
          this.show();
        });
        this.select.addEventListener("keydown", (e) => {
          if ([" ", "Enter", "ArrowDown", "ArrowUp"].includes(e.key)) {
            e.preventDefault();
            this.show();
          }
        });
        window.addEventListener("resize", () => this.position());
        this.render();
      }
      position() {
        const rect = this.select.getBoundingClientRect();
        this.pop.style.top = window.scrollY + rect.bottom + 6 + "px";
        this.pop.style.left = window.scrollX + rect.left + "px";
        const w = Math.max(260, rect.width);
        this.pop.style.minWidth = w + "px";
        if (rect.left + this.pop.offsetWidth > window.innerWidth) {
          this.pop.style.left = window.scrollX + rect.right - this.pop.offsetWidth + "px";
        }
      }
      show() {
        this.render();
        this.position();
        this.pop.hidden = false;
        this.searchInput.focus();
        setTimeout(() => document.addEventListener("mousedown", this.boundOutside), 0);
      }
      hide() {
        this.pop.hidden = true;
        document.removeEventListener("mousedown", this.boundOutside);
      }
      render(list) {
        const currentVal = this.select.value;
        const html = `
        <input class="sp-search" type="text" placeholder="🔎 جستجو در موضوعات..." aria-label="جستجو" />
        <div class="sp-list">
          ${(list||this.items).map(v=>{
            const active = v===currentVal ? "active":"";
            return `<div class="sp-item ${active}" data-val="${v}">${v}</div>`;
          }).join("")}
        </div>
      `;
        this.pop.innerHTML = html;
        this.searchInput = this.pop.querySelector(".sp-search");
        const makeList = (arr) => {
          const listHtml = arr.map(v => `<div class="sp-item ${v===this.select.value?"active":""}" data-val="${v}">${v}</div>`).join("");
          this.pop.querySelector(".sp-list").innerHTML = listHtml;
          this.pop.querySelectorAll(".sp-item").forEach(el => {
            el.addEventListener("click", () => this.pick(el.dataset.val));
          });
        };
        this.pop.querySelectorAll(".sp-item").forEach(el => el.addEventListener("click", () => this.pick(el.dataset.val)));
        this.searchInput.addEventListener("input", () => {
          const q = this.searchInput.value.trim();
          const filtered = !q ? this.items : this.items.filter(x => x.includes(q));
          makeList(filtered);
        });
        this.searchInput.addEventListener("keydown", (e) => {
          if (e.key === "Escape") {
            this.hide();
          }
        });
      }
      pick(val) {
        this.select.value = val;
        this.select.dispatchEvent(new Event("change", {
          bubbles: true
        }));
        this.hide();
      }
    }

    /* =======================
       Duration helper (hidden compute)
       ======================= */
    function computeDurationText(startDateISO, startTime, endDateISO, endTime) {
      if (!startDateISO || !startTime || !endDateISO || !endTime) return "";
      const start = new Date(`${startDateISO}T${startTime}`);
      const end = new Date(`${endDateISO}T${endTime}`);
      if (isNaN(start) || isNaN(end) || end < start) return "نامعتبر";

      let mins = Math.round((end - start) / 60000);
      const parts = [];
      if (mins >= 1440) {
        const d = Math.floor(mins / 1440);
        parts.push(`${d} روز`);
        mins -= d * 1440;
      }
      if (mins >= 60) {
        const h = Math.floor(mins / 60);
        parts.push(`${h} ساعت`);
        mins -= h * 60;
      }
      if (mins > 0) {
        parts.push(`${mins} دقیقه`);
      }
      return parts.length ? parts.join(" و ") : "کمتر از یک دقیقه";
    }

    /* =======================
       Main App Logic
       ======================= */
    document.addEventListener("DOMContentLoaded", () => {
      const API_URL = "/php/update-disruptions.php";

      const form = document.getElementById("disruptionForm");
      const tableBody = document.getElementById("disruptionTableBody");
      const recordIdInput = document.getElementById("recordId");
      const clearBtn = document.getElementById("clearBtn");
      const subjectSelect = document.getElementById("subject");
      const exportExcelBtn = document.getElementById("exportExcelBtn");

      const startDateInput = document.getElementById("startDate");
      const startTimeInput = document.getElementById("startTime");
      const endDateInput = document.getElementById("endDate");
      const endTimeInput = document.getElementById("endTime");

      const startDateDisplay = document.getElementById("startDateDisplay");
      const endDateDisplay = document.getElementById("endDateDisplay");
      const dayOfWeekSelect = document.getElementById("dayOfWeek");

      let currentRecords = [];

      /* ===== Subjects ===== */
      const subjects = [
        "اختلال در اپلیکیشن", "اختلال در ارتباط با واحد بازاریابی", "اختلال در ارتقاء/تغییر سطح",
        "اختلال در اعتبار سنجی", "اختلال در انتقال وجه", "اختلال در بانکداری ویدئویی", "اختلال در بیمه پاسارگاد",
        "اختلال در پنل CRM", "اختلال در تمامی تسهیلات", "اختلال در تسهیلات برآیند", "اختلال در تسهیلات پشتوانه",
        "اختلال در تسهیلات پیش درآمد", "اختلال در تسهیلات سازمانی", "اختلال در تسهیلات کاپ کارت",
        "اختلال در تسویه کارت خوان ها", "اختلال در تسویه معوقات", "اختلال در تغییر شماره تلفن همراه",
        "اختلال در تنظیمات امنیت حساب", "اختلال در چک", "اختلال چکاد", "اختلال در خدمات قبض", "اختلال در دریافت پیامک",
        "اختلال در دعوت از دوستان", "اختلال در سرویس درگاه پاد", "اختلال در سرویس مالی پاد", "کندی و قطعی پنل پاد",
        "اختلال در سرویس ثبت احوال", "اختلال در سرویس سمات", "اختلال در سرویس سیاح", "اختلال در سرویس شاهکار",
        "اختلال در سرویس شرکت ملی پست ایران", "اختلال در صندوق های سرمایه گذاری", "اختلال در طرح سرمایه گذاری جوانه",
        "اختلال در طرح سرمایه گذاری رویش", "اختلال در طرح کاوه", "اختلال در کارت فیزیکی", "اختلال در کارت و حساب دیجیتال",
        "اختلال در کارت و اعتبار هدیه دیجیتال", "اختلال در مسدودی و رفع مسدودی حساب", "اختلال در وی کلاب",
        "اختلال دستگاه پوز", "اختلال رمز دو عاملی", "اختلال کد شهاب", "مشکلات شعب", "سایر اختلالات",
        "اختلال در خرید شارژ و اینترنت", "اختلال ورود به برنامه", "اختلال در تسهیلات پیمان", "افزایش موجودی", "اختلال افتتاح حساب جاری"
      ];
      (function populateSubjects() {
        const hasPlaceholder = subjectSelect.options.length && subjectSelect.options[0].value === "";
        const frag = document.createDocumentFragment();
        subjects.forEach((subject) => {
          const option = document.createElement("option");
          option.value = subject;
          option.textContent = subject;
          frag.appendChild(option);
        });
        subjectSelect.appendChild(frag);
        if (hasPlaceholder) subjectSelect.selectedIndex = 0;
      })();
      new SubjectPicker(subjectSelect, subjects);

      /* ===== DatePickers ===== */
      new JalaliDatePicker("startDateDisplay", "startDate");
      new JalaliDatePicker("endDateDisplay", "endDate");

      /* ===== TimePickers (new) ===== */
      const startTP = new TimePicker("startTime");
      const endTP = new TimePicker("endTime");

      /* ===== Excel Export ===== */
      function exportToExcel() {
        if (!currentRecords || currentRecords.length === 0) {
          alert("هیچ داده‌ای برای خروجی وجود ندارد");
          return;
        }
        let excelContent = `
      <html xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns="http://www.w3.org/TR/REC-html40">
      <head>
        <meta charset="utf-8">
        <style>
          body { direction: rtl; }
          table { border-collapse: collapse; width: 100%; }
          th, td { border: 1px solid #000; padding: 8px; text-align: center; vertical-align: middle; }
          th { background-color: #e6f7f2; font-weight: bold; }
          .description-cell { text-align: right; max-width: 300px; }
        </style>
      </head>
      <body>
        <table>
          <thead>
            <tr>
              <th>روز</th>
              <th>موضوع</th>
              <th>وضعیت</th>
              <th>تاریخ شروع</th>
              <th>ساعت شروع</th>
              <th>تاریخ پایان</th>
              <th>ساعت پایان</th>
              <th>مجموع زمان</th>
              <th>تیم گزارش‌دهنده</th>
              <th>توضیح</th>
            </tr>
          </thead>
          <tbody>`;
        currentRecords.forEach((record) => {
          const computed = computeDurationText(record.startDate, record.startTime, record.endDate, record.endTime);
          const durationDisplay =
            (record.endDate && record.endTime) ?
            (record.totalDuration || computed || "—") :
            "—";
          const startDateJalali = record.startDate ? new Date(record.startDate).toLocaleDateString("fa-IR") : "—";
          const endDateJalali = record.endDate ? new Date(record.endDate).toLocaleDateString("fa-IR") : "—";
          excelContent += `
          <tr>
            <td>${record.dayOfWeek || "—"}</td>
            <td>${record.subject || "—"}</td>
            <td>${record.status || "—"}</td>
            <td>${startDateJalali}</td>
            <td>${record.startTime || "—"}</td>
            <td>${endDateJalali}</td>
            <td>${record.endTime || "—"}</td>
            <td>${durationDisplay}</td>
            <td>${record.reportingTeam || "—"}</td>
            <td class="description-cell">${record.description || "—"}</td>
          </tr>`;
        });
        excelContent += `</tbody></table></body></html>`;
        const blob = new Blob([excelContent], {
          type: "application/vnd.ms-excel;charset=utf-8"
        });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        const now = new Date();
        const jalaliDate = now.toLocaleDateString("fa-IR").replace(/\//g, "-");
        link.setAttribute("download", `اختلالات-مرکز-تماس-${jalaliDate}.xls`);
        link.style.visibility = "hidden";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
      }
      exportExcelBtn.addEventListener("click", exportToExcel);

      /* ===== Auto weekday from startDate ===== */
      function setWeekdayFromStartDate() {
        if (!startDateInput.value) return;
        const d = new Date(`${startDateInput.value}T00:00:00`);
        const map = ["یکشنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه", "پنجشنبه", "جمعه", "شنبه"]; // JS: 0=Sunday
        const name = map[d.getDay()];
        if (name && dayOfWeekSelect) dayOfWeekSelect.value = name;
      }

      function setBusy(btn, busyText) {
        btn.dataset.orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = busyText;
      }

      function clearBusy(btn) {
        if (btn.dataset.orig) {
          btn.innerHTML = btn.dataset.orig;
          delete btn.dataset.orig;
        }
        btn.disabled = false;
      }

      function resetForm() {
        form.reset();
        recordIdInput.value = "";
        startDateInput.value = endDateInput.value = "";
        startDateDisplay.value = endDateDisplay.value = "";
        if (subjectSelect.options.length && subjectSelect.options[0].value === "") subjectSelect.selectedIndex = 0;
      }

      /* ===== Data Fetch & Render ===== */
      async function loadRecords() {
        try {
          const response = await fetch(API_URL);
          if (!response.ok) throw new Error("خطا در دریافت اطلاعات از سرور");
          currentRecords = await response.json();
          renderTable(currentRecords);
        } catch (error) {
          console.error("Fetch Error:", error);
          alert(error.message);
        }
      }

      function renderTable(records) {
        tableBody.innerHTML = "";
        const statusClassMap = {
          باز: "status-open",
          "درحال رسیدگی": "status-in-progress",
          "برطرف شده": "status-resolved"
        };

        if (!records || records.length === 0) {
          const tr = tableBody.insertRow();
          const td = tr.insertCell();
          td.colSpan = 11;
          td.textContent = "هیچ رکوردی برای نمایش وجود ندارد.";
          td.style.textAlign = "center";
          td.style.padding = "2rem";
          td.style.color = "#555";
        } else {
          records.forEach((record) => {
            const tr = tableBody.insertRow();
            const statusClass = statusClassMap[record.status] || "";
            const computed = computeDurationText(record.startDate, record.startTime, record.endDate, record.endTime);
            const durationDisplay =
              (record.endDate && record.endTime) ?
              (record.totalDuration || computed || "—") :
              "—";
            const startDateJalali = record.startDate ? new Date(record.startDate).toLocaleDateString("fa-IR") : "—";
            const endDateJalali = record.endDate ? new Date(record.endDate).toLocaleDateString("fa-IR") : "—";
            tr.innerHTML = `
            <td>${record.dayOfWeek || "—"}</td>
            <td>${record.subject || "—"}</td>
            <td><span class="status ${statusClass}">${record.status || "—"}</span></td>
            <td>${startDateJalali}</td>
            <td>${record.startTime || "—"}</td>
            <td>${endDateJalali}</td>
            <td>${record.endTime || "—"}</td>
            <td>${durationDisplay}</td>
            <td>${record.reportingTeam || "—"}</td>
            <td class="description-cell">${record.description || "—"}</td>
            <td>
              <button class="action-btn edit-btn" data-id="${record.id}">ویرایش</button>
              <button class="action-btn delete-btn" data-id="${record.id}">حذف</button>
            </td>
          `;
          });
        }

        tableBody.querySelectorAll(".edit-btn").forEach((btn) =>
          btn.addEventListener("click", () => handleEdit(btn.dataset.id))
        );
        tableBody.querySelectorAll(".delete-btn").forEach((btn) =>
          btn.addEventListener("click", () => handleDelete(btn.dataset.id, btn))
        );
      }

      /* ===== CRUD Handlers ===== */
      form.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }
        const saveBtn = form.querySelector(".btn-save");
        setBusy(saveBtn, "⏳ در حال ذخیره...");

        const formData = new FormData(form);

        // ⬇️ محاسبه و افزودن مجموع زمان بدون نمایش در UI
        const durationText = computeDurationText(startDateInput.value, startTimeInput.value, endDateInput.value, endTimeInput.value);
        formData.set("totalDuration", (durationText === "نامعتبر" ? "" : durationText));

        try {
          const response = await fetch(API_URL, {
            method: "POST",
            body: formData
          });
          if (!response.ok) throw new Error("خطا در ذخیره سازی اطلاعات");
          const result = await response.json();
          console.log(result.message || "Saved");
          resetForm();
          await loadRecords();
          document.querySelector(".table-container").scrollIntoView({
            behavior: "smooth"
          });
        } catch (error) {
          console.error("Submit Error:", error);
          alert(error.message);
        } finally {
          clearBusy(saveBtn);
        }
      });

      async function handleDelete(id, btnEl) {
        if (!confirm("آیا از حذف این رکورد مطمئن هستید؟")) return;
        if (btnEl) setBusy(btnEl, "🗑️ حذف...");
        try {
          const formData = new FormData();
          formData.append("action", "delete");
          formData.append("id", id);
          const response = await fetch(API_URL, {
            method: "POST",
            body: formData
          });
          if (!response.ok) throw new Error("خطا در حذف رکورد");
          const result = await response.json();
          console.log(result.message || "Deleted");
          await loadRecords();
        } catch (error) {
          console.error("Delete Error:", error);
          alert(error.message);
        } finally {
          if (btnEl) clearBusy(btnEl);
        }
      }

      function handleEdit(id) {
        const record = currentRecords.find((r) => r.id === id);
        if (!record) return;

        recordIdInput.value = record.id;
        dayOfWeekSelect.value = record.dayOfWeek || "";
        subjectSelect.value = record.subject || "";
        form.querySelector("#status").value = record.status || "باز";
        form.querySelector("#reportingTeam").value = record.reportingTeam || "اعلام از سمت فنی";

        startDateInput.value = record.startDate || "";
        startDateDisplay.value = record.startDate ? new Date(record.startDate).toLocaleDateString("fa-IR") : "";

        endDateInput.value = record.endDate || "";
        endDateDisplay.value = record.endDate ? new Date(record.endDate).toLocaleDateString("fa-IR") : "";

        startTimeInput.value = record.startTime || "";
        endTimeInput.value = record.endTime || "";
        form.querySelector("#description").value = record.description || "";

        setWeekdayFromStartDate();
        window.scrollTo({
          top: 0,
          behavior: "smooth"
        });
      }

      // Auto weekday
      startDateInput.addEventListener("change", setWeekdayFromStartDate);

      // Clear form
      clearBtn.addEventListener("click", resetForm);

      // Initial load
      loadRecords();
    });
  </script>

</body>

</html>
