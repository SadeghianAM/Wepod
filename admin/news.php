<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>مدیریت اطلاعیه‌ها</title>
  <style>
    :root {
      --primary-color: #00ae70;
      --primary-dark: #089863;
      --primary-light: #e6f7f2;
      --bg-color: #f7f9fa;
      --text-color: #1a1a1a;
      --secondary-text-color: #555;
      --card-bg: #ffffff;
      --header-text: #ffffff;
      --border-color: #e9e9e9;
      --shadow-light: rgba(0, 120, 80, 0.06);
      --shadow-medium: rgba(0, 120, 80, 0.12);
      --border-radius: 0.75rem;

      /* Announcement Colors */
      --green-bg: #e6f7f2;
      --green-border: #00ae70;
      --green-text: #089863;
      --yellow-bg: #fffde0;
      --yellow-border: #ffd600;
      --yellow-text: #b38600;
      --red-bg: #fef2f2;
      --red-border: #ef4444;
      --red-text: #dc2626;
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

    footer {
      height: 60px;
      font-size: 0.85rem;
    }

    main {
      flex-grow: 1;
      padding: 2.5rem 2rem;
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
    }

    .page-header {
      margin-bottom: 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .page-title {
      font-size: 1.8rem;
      font-weight: 800;
      color: var(--primary-dark);
    }

    .page-subtitle {
      font-size: 1rem;
      font-weight: 400;
      color: var(--secondary-text-color);
      margin-top: 0.25rem;
    }

    #add-new-item-btn {
      background-color: var(--primary-color);
      color: white;
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-size: 1rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      box-shadow: 0 4px 15px rgba(0, 174, 112, 0.2);
      transition: all 0.2s;
    }

    #add-new-item-btn:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 174, 112, 0.25);
    }

    #item-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 1.5rem;
    }

    .announcement-card {
      background-color: var(--card-bg);
      border-radius: var(--border-radius);
      box-shadow: 0 4px 15px var(--shadow-light);
      border: 1px solid var(--border-color);
      border-top: 4px solid;
      transition: all 0.2s;
      display: flex;
      flex-direction: column;
    }

    .announcement-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 25px var(--shadow-medium);
    }

    .announcement-card.green {
      border-top-color: var(--green-border);
    }

    .announcement-card.yellow {
      border-top-color: var(--yellow-border);
    }

    .announcement-card.red {
      border-top-color: var(--red-border);
    }

    .card-header {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 1.25rem;
    }

    .card-icon {
      font-size: 1.5rem;
    }

    .card-title {
      font-size: 1.2rem;
      font-weight: 700;
    }

    .announcement-card.green .card-icon {
      color: var(--green-text);
    }

    .announcement-card.yellow .card-icon {
      color: var(--yellow-text);
    }

    .announcement-card.red .card-icon {
      color: var(--red-text);
    }

    .card-body {
      padding: 0 1.25rem 1.25rem;
      line-height: 1.7;
      flex-grow: 1;
    }

    .card-body p,
    .card-body ul {
      margin: 0;
    }

    .card-body ul {
      padding-right: 1.25rem;
    }

    .card-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      padding: 0.75rem 1.25rem;
      border-top: 1px solid var(--border-color);
      background-color: #fafbfc;
      font-size: 0.85rem;
      color: var(--secondary-text-color);
      border-bottom-left-radius: var(--border-radius);
      border-bottom-right-radius: var(--border-radius);
    }

    .card-actions {
      display: flex;
      gap: 0.5rem;
    }

    .card-actions button {
      background: none;
      border: 1px solid var(--border-color);
      color: var(--secondary-text-color);
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      cursor: pointer;
      font-size: 0.9rem;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      transition: all 0.2s;
    }

    .card-actions .edit-btn:hover {
      background-color: #e7f5ff;
      color: #007bff;
      border-color: #b8daff;
    }

    .card-actions .delete-btn:hover {
      background-color: #fef2f2;
      color: #dc3545;
      border-color: #f5b7bd;
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
      padding-top: 5vh;
    }

    .modal-content {
      background-color: var(--card-bg);
      margin: 0 auto;
      padding: 2rem;
      border: none;
      border-radius: var(--border-radius);
      width: 90%;
      max-width: 600px;
      box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
      position: relative;
    }

    .close-button {
      color: #aaa;
      position: absolute;
      left: 1rem;
      top: 1rem;
      font-size: 2rem;
      font-weight: bold;
      cursor: pointer;
      line-height: 1;
      transition: color 0.2s;
    }

    .close-button:hover {
      color: #333;
    }

    .modal-content label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #333;
    }

    .modal-content input,
    .modal-content select,
    .description-editor {
      width: 100%;
      padding: 0.75rem;
      margin-bottom: 1.25rem;
      border: 1px solid var(--border-color);
      border-radius: 0.5rem;
      font-size: 1rem;
      background-color: #fcfdff;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .modal-content input[type="text"][readonly] {
      cursor: pointer;
    }

    .modal-content input:focus,
    .modal-content select:focus,
    .description-editor:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(0, 174, 112, 0.15);
      outline: none;
    }

    .date-time-group {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
      align-items: end;
    }

    .date-time-group>div {
      flex: 1;
    }

    .description-editor {
      min-height: 150px;
      line-height: 1.6;
    }

    .editor-toolbar {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
      padding: 0.5rem;
      background-color: #f0f3f5;
      border-radius: 0.5rem;
      border: 1px solid var(--border-color);
    }

    .editor-toolbar button {
      background-color: var(--card-bg);
      border: 1px solid #ccc;
      width: 36px;
      height: 36px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1.2rem;
      display: grid;
      place-items: center;
      transition: all 0.2s;
      padding: 0;
      margin: 0;
    }

    .editor-toolbar button:hover {
      background-color: var(--primary-light);
      color: var(--primary-dark);
      border-color: var(--primary-color);
    }

    .modal-content .button-group {
      display: flex;
      justify-content: flex-end;
      gap: 0.75rem;
      margin-top: 1.5rem;
    }

    .modal-content .button-group button {
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      border-radius: var(--border-radius);
      transition: all 0.2s;
      cursor: pointer;
      font-size: 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    #save-item-btn {
      background-color: var(--primary-color);
      color: white;
      border: 1px solid transparent;
      box-shadow: 0 2px 8px rgba(0, 174, 112, 0.2);
    }

    #save-item-btn:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
    }

    #cancel-edit-btn {
      background-color: transparent;
      color: var(--secondary-text-color);
      border: 1px solid var(--border-color);
    }

    #cancel-edit-btn:hover {
      background-color: #f1f1f1;
      border-color: #ccc;
    }

    .back-link {
      display: block;
      margin-top: 2rem;
      text-align: center;
      color: var(--primary-color);
      font-weight: 500;
    }

    .back-link:hover {
      text-decoration: underline;
      color: var(--primary-dark);
    }

    /* --- [START] DATE & TIME PICKER STYLES (Kept from original) --- */
    .jdp-popover {
      position: absolute;
      background: #fff;
      border: 1px solid var(--border-color);
      border-radius: .5rem;
      box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
      padding: .75rem;
      width: 280px;
      z-index: 9999
    }

    .jdp-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: .5rem;
      font-weight: 700;
      color: var(--primary-dark)
    }

    .jdp-nav-btn {
      background: var(--primary-color);
      color: #fff;
      border: none;
      padding: .25rem .6rem;
      border-radius: .4rem;
      cursor: pointer
    }

    .jdp-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 4px
    }

    .jdp-weekday {
      text-align: center;
      font-size: .85rem;
      color: var(--secondary-text-color);
      padding: .3rem 0
    }

    .jdp-day {
      text-align: center;
      padding: .4rem 0;
      border-radius: .4rem;
      cursor: pointer;
      background: #fafafa;
      border: 1px solid #f0f0f0
    }

    .jdp-day:hover {
      background: var(--primary-light)
    }

    .jdp-day.other {
      color: #bbb;
      background: #f8f9fa
    }

    .jdp-hidden {
      display: none
    }

    .tp-popover {
      position: absolute;
      background: #fff;
      border: 1px solid var(--border-color);
      border-radius: .75rem;
      box-shadow: 0 10px 28px rgba(0, 0, 0, .12);
      padding: .75rem;
      z-index: 10000;
      min-width: 320px;
      max-width: 360px
    }

    .tp-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: .5rem
    }

    .tp-title {
      font-weight: 800;
      color: var(--primary-dark);
      display: flex;
      align-items: center;
      gap: .4rem
    }

    .tp-title .emoji {
      font-size: 1rem
    }

    .tp-clock {
      font-weight: 900;
      font-size: 1.1rem;
      color: #2b3a32;
      direction: ltr;
      unicode-bidi: plaintext
    }

    .tp-columns {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: .6rem;
      position: relative
    }

    .tp-col-heads {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: .6rem;
      margin: .25rem 0 .35rem
    }

    .tp-col-head {
      text-align: center;
      font-weight: 800;
      color: #2b3a32;
      background: #f5f9f7;
      border: 1px solid #e6ece9;
      border-radius: .5rem;
      padding: .3rem
    }

    .hours-col {
      grid-column: 2
    }

    .minutes-col {
      grid-column: 1
    }

    .tp-col {
      height: 180px;
      overflow-y: auto;
      border: 1px solid #e6ece9;
      border-radius: .6rem;
      background: #fafafa;
      scroll-snap-type: y mandatory;
      padding: .25rem
    }

    .tp-opt {
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: .15rem 0;
      border-radius: .5rem;
      scroll-snap-align: center;
      cursor: pointer;
      user-select: none;
      direction: ltr
    }

    .tp-opt:hover {
      background: var(--primary-light)
    }

    .tp-opt.active {
      background: #e9fff6;
      border: 1px solid #b9f0dc;
      font-weight: 800
    }

    .tp-arrows {
      display: flex;
      justify-content: space-between;
      gap: .5rem;
      margin: .5rem 0
    }

    .tp-arrow-btn {
      flex: 1;
      border: none;
      border-radius: .5rem;
      padding: .45rem .6rem;
      cursor: pointer;
      font-weight: 800;
      background: #f0f3f2;
      color: #2b3a32
    }

    .tp-quick {
      display: flex;
      flex-wrap: wrap;
      gap: .4rem;
      margin: .4rem 0
    }

    .tp-quick-btn {
      border: none;
      border-radius: .5rem;
      padding: .35rem .6rem;
      background: #f4fbf8;
      color: #036f4b;
      font-weight: 800;
      cursor: pointer
    }

    .tp-actions {
      display: flex;
      gap: .5rem;
      justify-content: space-between;
      margin-top: .5rem
    }

    .tp-btn {
      border: none;
      border-radius: .6rem;
      padding: .55rem .9rem;
      font-weight: 800;
      cursor: pointer
    }

    .tp-btn-primary {
      background: var(--primary-color);
      color: #fff
    }

    .tp-btn-secondary {
      background: #f0f3f2;
      color: #2b3a32
    }

    .tp-btn-danger {
      background: #fbe9e8;
      color: #b3261e
    }

    .tp-btn:disabled {
      opacity: .6;
      cursor: not-allowed
    }

    /* --- [END] DATE & TIME PICKER STYLES --- */
  </style>
</head>

<body>
  <div id="header-placeholder"></div>
  <main>
    <div class="page-header">
      <div>
        <h1 class="page-title">مدیریت اطلاعیه‌ها</h1>
        <p class="page-subtitle">اطلاعیه‌های فعال در صفحه اصلی را از اینجا مدیریت کنید.</p>
      </div>
      <button id="add-new-item-btn">✨ افزودن اطلاعیه</button>
    </div>

    <div id="item-list"></div>
    <a href="/admin/index.php" class="back-link">بازگشت به پنل مدیریت</a>
  </main>

  <div id="itemModal" class="modal">
    <div class="modal-content">
      <span class="close-button">&times;</span>
      <h2 id="modalTitle" style="text-align: right; margin-bottom: 2rem; font-size: 1.5rem; font-weight: 700;"></h2>
      <form id="itemForm">
        <input type="hidden" id="itemId" />

        <label for="title">عنوان:</label>
        <input type="text" id="title" name="title" required />

        <label for="description-editor">توضیحات:</label>
        <div class="editor-toolbar">
          <button type="button" data-command="bold" title="ضخیم"><b>B</b></button>
        </div>
        <div id="description-editor" class="description-editor" contenteditable="true"></div>

        <label for="color">رنگ:</label>
        <select id="color" name="color">
          <option value="green">🟢 سبز (اطلاع‌رسانی)</option>
          <option value="yellow">🟡 زرد (هشدار)</option>
          <option value="red">🔴 قرمز (بسیار مهم)</option>
        </select>

        <div class="date-time-group">
          <div>
            <label for="startDateDisplay">تاریخ شروع (اختیاری):</label>
            <input type="text" id="startDateDisplay" placeholder="برای انتخاب کلیک کنید" autocomplete="off" readonly />
            <input type="hidden" id="startDate" name="startDate" />
          </div>
          <div>
            <label for="startTime">ساعت شروع (اختیاری):</label>
            <input type="text" id="startTime" name="startTime" placeholder="--:--" />
          </div>
        </div>

        <div class="date-time-group">
          <div>
            <label for="endDateDisplay">تاریخ پایان (اختیاری):</label>
            <input type="text" id="endDateDisplay" placeholder="برای انتخاب کلیک کنید" autocomplete="off" readonly />
            <input type="hidden" id="endDate" name="endDate" />
          </div>
          <div>
            <label for="endTime">ساعت پایان (اختیاری):</label>
            <input type="text" id="endTime" name="endTime" placeholder="--:--" />
          </div>
        </div>

        <div class="button-group">
          <button type="button" id="cancel-edit-btn">✖️ لغو</button>
          <button type="submit" id="save-item-btn">💾 ذخیره</button>
        </div>
      </form>
    </div>
  </div>

  <div id="footer-placeholder"></div>
  <script src="/js/header.js"></script>
  <script>
    /* ===================================================
     * START: JALALI DATE & TIME PICKER LOGIC (Kept from original)
     * =================================================== */
    function jalaliToGregorian(jy, jm, jd) {
      var sal_a, gy, gm, gd, days;
      jy += 1595;
      days = -355668 + 365 * jy + ~~(jy / 33) * 8 + ~~(((jy % 33) + 3) / 4) + jd + (jm < 7 ? (jm - 1) * 31 : (jm - 7) * 30 + 186);
      gy = 400 * ~~(days / 146097);
      days %= 146097;
      if (days > 355668) {
        gy += 100 * ~(--days / 36524);
        days %= 36524;
        if (days >= 365) days++
      }
      gy += 4 * ~~(days / 1461);
      days %= 1461;
      if (days > 365) {
        gy += ~~((days - 1) / 365);
        days = (days - 1) % 365
      }
      gd = days + 1;
      sal_a = [0, 31, (gy % 4 === 0 && gy % 100 !== 0) || gy % 400 === 0 ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
      for (gm = 0; gm < 13 && gd > sal_a[gm]; gm++) gd -= sal_a[gm];
      return new Date(gy, gm - 1, gd)
    }

    function toPersian(date) {
      const parts = date.toLocaleDateString("fa-IR-u-nu-latn").split("/");
      return parts.map((part) => parseInt(part, 10))
    }

    function formatJalaliDisplay(jy, jm, jd) {
      return `${jy}/${String(jm).padStart(2,"0")}/${String(jd).padStart(2,"0")}`
    }

    function formatISO(date) {
      return `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,"0")}-${String(date.getDate()).padStart(2,"0")}`
    }

    function isJalaliLeap(jy) {
      return (((((((jy - 474) % 2820) + 2820) % 2820) + 474 + 38) * 682) % 2816) < 682
    }

    function jalaliMonthLength(jy, jm) {
      if (jm <= 6) return 31;
      if (jm <= 11) return 30;
      return isJalaliLeap(jy) ? 30 : 29
    }
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
          if (!this.pop.contains(e.target) && e.target !== this.input) this.hide()
        };
        this.input.addEventListener("focus", () => this.show());
        this.input.addEventListener("click", () => this.show());
        window.addEventListener("resize", () => this.position())
      }
      show() {
        this.render();
        this.position();
        this.pop.classList.remove("jdp-hidden");
        setTimeout(() => document.addEventListener("mousedown", this.boundClickOutside), 0)
      }
      hide() {
        this.pop.classList.add("jdp-hidden");
        document.removeEventListener("mousedown", this.boundClickOutside)
      }
      position() {
        const rect = this.input.getBoundingClientRect();
        this.pop.style.top = (window.scrollY + rect.top - this.pop.offsetHeight - 6) + "px";
        this.pop.style.left = window.scrollX + rect.left + "px";
        if (rect.left + 280 > window.innerWidth) this.pop.style.left = window.scrollX + rect.right - 280 + "px"
      }
      nav(delta) {
        this.jm += delta;
        if (this.jm < 1) {
          this.jm = 12;
          this.jy--
        }
        if (this.jm > 12) {
          this.jm = 1;
          this.jy++
        }
        this.render()
      }
      render() {
        const weekDays = ["ش", "ی", "د", "س", "چ", "پ", "ج"];
        const firstG = jalaliToGregorian(this.jy, this.jm, 1);
        const firstWeekday = (firstG.getDay() + 1) % 7;
        const daysInMonth = jalaliMonthLength(this.jy, this.jm);
        let html = `<div class="jdp-header"><button type="button" class="jdp-nav-btn" data-nav="-1">«</button><div>${new Intl.DateTimeFormat("fa-IR",{month:"long"}).format(firstG)} ${new Intl.DateTimeFormat("fa-IR-u-nu-latn",{year:"numeric"}).format(firstG)}</div><button type="button" class="jdp-nav-btn" data-nav="1">»</button></div><div class="jdp-grid">${weekDays.map(w=>`<div class="jdp-weekday">${w}</div>`).join("")}`;
        for (let i = 0; i < firstWeekday; i++) html += `<div class="jdp-day other"></div>`;
        for (let d = 1; d <= daysInMonth; d++) {
          html += `<div class="jdp-day" data-day="${d}">${new Intl.NumberFormat("fa-IR").format(d)}</div>`
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
            this.hide()
          })
        })
      }
    }
    class TimePicker {
      constructor(inputId) {
        this.input = document.getElementById(inputId);
        if (!this.input) return;
        this.input.readOnly = true;
        this.pop = document.createElement("div");
        this.pop.className = "tp-popover";
        this.pop.hidden = true;
        document.body.appendChild(this.pop);
        this.boundOutside = (e) => {
          if (!this.pop.contains(e.target) && e.target !== this.input) this.hide()
        };
        this.input.addEventListener("pointerdown", (e) => {
          e.preventDefault();
          this.show()
        });
        this.input.addEventListener("focus", () => this.show());
        this.input.addEventListener("click", () => this.show());
        window.addEventListener("resize", () => this.position())
      }
      parse() {
        const v = (this.input.value || "").trim();
        let h = 12,
          m = 0;
        if (/^\d{1,2}:\d{2}$/.test(v)) {
          const [hh, mm] = v.split(":").map(n => parseInt(n, 10));
          h = Math.max(0, Math.min(23, hh));
          m = Math.max(0, Math.min(59, mm))
        }
        this.h = h;
        this.m = m - (m % 5)
      }
      position() {
        const rect = this.input.getBoundingClientRect();
        this.pop.style.top = (window.scrollY + rect.top - this.pop.offsetHeight - 6) + "px";
        this.pop.style.left = window.scrollX + rect.left + "px";
        const w = this.pop.offsetWidth || 340;
        if (rect.left + w > window.innerWidth) this.pop.style.left = window.scrollX + rect.right - w + "px"
      }
      show() {
        this.parse();
        this.render();
        this.position();
        this.pop.hidden = false;
        setTimeout(() => document.addEventListener("mousedown", this.boundOutside), 0)
      }
      hide() {
        this.pop.hidden = true;
        document.removeEventListener("mousedown", this.boundOutside)
      }
      setHour(h) {
        this.h = (h + 24) % 24;
        this.updateClock();
        this.highlight()
      }
      setMinute(m) {
        this.m = (m + 60) % 60;
        this.updateClock();
        this.highlight()
      }
      stepHour(d) {
        this.setHour(this.h + d);
        this.scrollToActive(this.hoursCol, this.h)
      }
      stepMinute(d) {
        this.setMinute(this.m + d * 5);
        this.scrollToActive(this.minutesCol, this.m / 5)
      }
      apply() {
        const val = `${String(this.h).padStart(2,"0")}:${String(this.m).padStart(2,"0")}`;
        this.input.value = val;
        this.input.dispatchEvent(new Event("change", {
          bubbles: true
        }));
        this.hide()
      }
      clear() {
        this.input.value = "";
        this.input.dispatchEvent(new Event("change", {
          bubbles: true
        }));
        this.hide()
      }
      now() {
        const d = new Date();
        this.h = d.getHours();
        this.m = Math.round(d.getMinutes() / 5) * 5 % 60;
        this.updateClock();
        this.highlight();
        this.scrollToActive(this.hoursCol, this.h);
        this.scrollToActive(this.minutesCol, this.m / 5)
      }
      updateClock() {
        if (this.clock) this.clock.textContent = `${String(this.h).padStart(2,"0")}:${String(this.m).padStart(2,"0")}`
      }
      highlight() {
        this.pop.querySelectorAll(".tp-opt").forEach(el => el.classList.remove("active"));
        const hEl = this.pop.querySelector(`.tp-opt[data-hour="${this.h}"]`);
        const mEl = this.pop.querySelector(`.tp-opt[data-minute="${this.m}"]`);
        if (hEl) hEl.classList.add("active");
        if (mEl) mEl.classList.add("active")
      }
      makeColumn(type, items, extraClass) {
        const col = document.createElement("div");
        col.className = `tp-col ${extraClass||""}`;
        col.setAttribute("tabindex", "0");
        col.innerHTML = items.map(v => {
          const attr = type === "h" ? `data-hour="${v}"` : `data-minute="${v}"`;
          return `<div class="tp-opt" ${attr}>${String(v).padStart(2,"0")}</div>`
        }).join("");
        col.addEventListener("click", (e) => {
          const t = e.target.closest(".tp-opt");
          if (!t) return;
          if (type === "h") this.setHour(parseInt(t.dataset.hour, 10));
          else this.setMinute(parseInt(t.dataset.minute, 10))
        });
        col.addEventListener("keydown", (e) => {
          if (type === "h") {
            if (e.key === "ArrowUp") {
              e.preventDefault();
              this.stepHour(-1)
            }
            if (e.key === "ArrowDown") {
              e.preventDefault();
              this.stepHour(1)
            }
          } else {
            if (e.key === "ArrowUp") {
              e.preventDefault();
              this.stepMinute(-1)
            }
            if (e.key === "ArrowDown") {
              e.preventDefault();
              this.stepMinute(1)
            }
          }
          if (e.key === "Enter") {
            e.preventDefault();
            this.apply()
          }
          if (e.key === "Escape") {
            e.preventDefault();
            this.hide()
          }
        });
        col.addEventListener("scroll", () => {
          clearTimeout(this._snapt);
          this._snapt = setTimeout(() => {
            const idx = Math.round(col.scrollTop / 36);
            const val = type === "h" ? idx : (idx * 5);
            if (type === "h") this.setHour(Math.max(0, Math.min(23, val)));
            else this.setMinute(Math.max(0, Math.min(55, val)))
          }, 120)
        });
        return col
      }
      scrollToActive(col, index) {
        const y = index * 36;
        col.scrollTo({
          top: y,
          behavior: "smooth"
        })
      }
      render() {
        const hours = Array.from({
          length: 24
        }, (_, i) => i);
        const minutes = Array.from({
          length: 12
        }, (_, i) => i * 5);
        this.pop.innerHTML = `<div class="tp-header"><div class="tp-title"><span class="emoji">⏰</span><span>انتخاب ساعت</span></div><div class="tp-clock"></div></div><div class="tp-col-heads"><div class="tp-col-head minutes-head">دقیقه</div><div class="tp-col-head hours-head">ساعت</div></div><div class="tp-columns"></div><div class="tp-arrows"><button type="button" class="tp-arrow-btn" data-m="-1">⬆️ دقیقه</button><button type="button" class="tp-arrow-btn" data-m="+1">⬇️ دقیقه</button><button type="button" class="tp-arrow-btn" data-h="-1">⬆️ ساعت</button><button type="button" class="tp-arrow-btn" data-h="+1">⬇️ ساعت</button></div><div class="tp-quick">${[0,15,30,45].map(v=>`<button type="button" class="tp-quick-btn" data-qm="${v}">:${String(v).padStart(2,"0")}</button>`).join("")}</div><div class="tp-actions"><div style="display:flex;gap:.5rem"><button type="button" class="tp-btn tp-btn-secondary" data-now="1">🕒 اکنون</button><button type="button" class="tp-btn tp-btn-danger" data-clear="1">🧹 پاک</button></div><button type="button" class="tp-btn tp-btn-primary" data-apply="1">✅ ثبت</button></div>`;
        this.clock = this.pop.querySelector(".tp-clock");
        this.updateClock();
        const cols = this.pop.querySelector(".tp-columns");
        this.hoursCol = this.makeColumn("h", hours, "hours-col");
        this.minutesCol = this.makeColumn("m", minutes, "minutes-col");
        cols.appendChild(this.minutesCol);
        cols.appendChild(this.hoursCol);
        this.highlight();
        this.scrollToActive(this.hoursCol, this.h);
        this.scrollToActive(this.minutesCol, this.m / 5);
        this.pop.querySelectorAll("[data-h]").forEach(b => {
          b.addEventListener("click", () => this.stepHour(parseInt(b.dataset.h.replace("+", ""), 10)))
        });
        this.pop.querySelectorAll("[data-m]").forEach(b => {
          b.addEventListener("click", () => this.stepMinute(parseInt(b.dataset.m.replace("+", ""), 10)))
        });
        this.pop.querySelectorAll("[data-qm]").forEach(b => {
          b.addEventListener("click", () => {
            this.setMinute(parseInt(b.dataset.qm, 10));
            this.scrollToActive(this.minutesCol, this.m / 5)
          })
        });
        this.pop.querySelector("[data-apply]").addEventListener("click", () => this.apply());
        this.pop.querySelector("[data-clear]").addEventListener("click", () => this.clear());
        this.pop.querySelector("[data-now]").addEventListener("click", () => this.now())
      }
    }
    /* ===================================================
     * END: JALALI DATE & TIME PICKER LOGIC
     * =================================================== */

    /* ===================================================
     * MAIN APPLICATION LOGIC (SECURED)
     * =================================================== */
    let jsonData = [];
    let currentItemIndex = -1;

    const itemListDiv = document.getElementById("item-list");
    const itemModal = document.getElementById("itemModal");
    const closeButton = document.querySelector(".close-button");
    const itemForm = document.getElementById("itemForm");
    const addNewItemBtn = document.getElementById("add-new-item-btn");
    const modalTitle = document.getElementById("modalTitle");
    const descriptionEditor = document.getElementById("description-editor");
    const editorToolbar = document.querySelector(".editor-toolbar");
    const cancelEditBtn = document.getElementById("cancel-edit-btn");

    function gregorianToJalaliDisplay(isoDate) {
      if (!isoDate || typeof isoDate !== 'string') return "";
      try {
        const parts = isoDate.split('-');
        const date = new Date(parts[0], parts[1] - 1, parts[2]);
        if (isNaN(date.getTime())) return "";
        return date.toLocaleDateString("fa-IR");
      } catch (e) {
        console.error("Error converting date:", e);
        return "";
      }
    }

    async function saveDataToServer() {
      try {
        const response = await fetch("/data/save-news-alerts.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify(jsonData, null, 2),
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || "خطای سرور");
        console.log(result.message);
      } catch (error) {
        console.error("Error saving data:", error);
        alert("خطا در ذخیره اطلاعات: " + error.message);
      }
    }

    function openModal() {
      itemModal.style.display = "block";
      document.body.style.overflow = 'hidden';
      descriptionEditor?.focus();
    }

    function closeModal() {
      itemModal.style.display = "none";
      document.body.style.overflow = '';
      itemForm.reset();
      document.getElementById('startDateDisplay').value = '';
      document.getElementById('endDateDisplay').value = '';
      if (descriptionEditor) descriptionEditor.innerHTML = "";
    }

    const colorToIconMap = {
      green: '📢',
      yellow: '⚠️',
      red: '❗'
    };

    function formatDateTime(dateStr, timeStr) {
      if (!dateStr) return '';
      try {
        const date = new Date(dateStr);
        const options = {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        };
        let formatted = new Intl.DateTimeFormat('fa-IR', options).format(date);
        if (timeStr) {
          formatted += ` - ساعت ${timeStr}`;
        }
        return formatted;
      } catch (e) {
        return dateStr;
      }
    }

    /**
     * SECURE RENDER FUNCTION
     */
    function renderItems() {
      itemListDiv.innerHTML = "";
      if (jsonData.length === 0) {
        itemListDiv.innerHTML = '<p style="text-align: center; margin-top: 50px; font-size: 1.2rem; color: #555;">اطلاعیه‌ای برای نمایش وجود ندارد.</p>';
        return;
      }

      jsonData.forEach((item, index) => {
        const card = document.createElement("div");
        card.className = `announcement-card ${item.color}`;

        const cardHeader = document.createElement('div');
        cardHeader.className = 'card-header';
        const cardIcon = document.createElement('span');
        cardIcon.className = 'card-icon';
        cardIcon.textContent = colorToIconMap[item.color] || '🔹';
        const cardTitle = document.createElement('h3');
        cardTitle.className = 'card-title';
        cardTitle.textContent = item.title;
        cardHeader.appendChild(cardIcon);
        cardHeader.appendChild(cardTitle);

        const cardBody = document.createElement('div');
        cardBody.className = 'card-body';
        cardBody.innerHTML = item.description;

        const start = formatDateTime(item.startDate, item.startTime);
        const end = formatDateTime(item.endDate, item.endTime);
        let timeInfo = 'همیشه فعال';
        if (start && end) timeInfo = `از ${start} تا ${end}`;
        else if (start) timeInfo = `از ${start}`;
        else if (end) timeInfo = `تا ${end}`;

        const cardFooter = document.createElement('div');
        cardFooter.className = 'card-footer';
        const timeInfoSpan = document.createElement('span');
        timeInfoSpan.textContent = `🗓️ ${timeInfo}`;

        const cardActions = document.createElement('div');
        cardActions.className = 'card-actions';

        const editButton = document.createElement('button');
        editButton.className = 'edit-btn';
        editButton.dataset.index = index;
        editButton.innerHTML = '✏️ ویرایش';

        const deleteButton = document.createElement('button');
        deleteButton.className = 'delete-btn';
        deleteButton.dataset.index = index;
        deleteButton.innerHTML = '🗑️ حذف';

        cardActions.appendChild(editButton);
        cardActions.appendChild(deleteButton);
        cardFooter.appendChild(timeInfoSpan);
        cardFooter.appendChild(cardActions);

        card.appendChild(cardHeader);
        card.appendChild(cardBody);
        card.appendChild(cardFooter);

        itemListDiv.appendChild(card);
      });

      document.querySelectorAll(".edit-btn").forEach(btn => btn.addEventListener("click", (e) => editItem(parseInt(e.currentTarget.dataset.index))));
      document.querySelectorAll(".delete-btn").forEach(btn => btn.addEventListener("click", (e) => deleteItem(parseInt(e.currentTarget.dataset.index))));
    }

    function editItem(index) {
      currentItemIndex = index;
      const item = jsonData[index];
      document.getElementById("itemId").value = index;
      document.getElementById("title").value = item.title;
      descriptionEditor.innerHTML = item.description;
      document.getElementById("color").value = item.color;
      document.getElementById("startDate").value = item.startDate;
      document.getElementById("startDateDisplay").value = gregorianToJalaliDisplay(item.startDate);
      document.getElementById("startTime").value = item.startTime;
      document.getElementById("endDate").value = item.endDate;
      document.getElementById("endDateDisplay").value = gregorianToJalaliDisplay(item.endDate);
      document.getElementById("endTime").value = item.endTime;
      modalTitle.textContent = "ویرایش اطلاعیه";
      openModal();
    }

    addNewItemBtn.addEventListener("click", () => {
      currentItemIndex = -1;
      itemForm.reset();
      descriptionEditor.innerHTML = "";
      modalTitle.textContent = "افزودن اطلاعیه جدید";
      openModal();
    });

    itemForm.addEventListener("submit", (e) => {
      e.preventDefault();
      let descriptionValue = descriptionEditor.innerHTML.trim();
      if (!descriptionEditor.textContent.trim() || descriptionValue === '<br>') {
        descriptionValue = '';
      }
      const newItem = {
        title: document.getElementById("title").value,
        description: descriptionValue,
        color: document.getElementById("color").value,
        startDate: document.getElementById("startDate").value,
        startTime: document.getElementById("startTime").value,
        endDate: document.getElementById("endDate").value,
        endTime: document.getElementById("endTime").value,
      };
      if (currentItemIndex === -1) {
        jsonData.push(newItem);
      } else {
        jsonData[currentItemIndex] = newItem;
      }
      renderItems();
      closeModal();
      saveDataToServer();
    });

    function deleteItem(index) {
      if (confirm("آیا از حذف این اطلاعیه اطمینان دارید؟")) {
        jsonData.splice(index, 1);
        renderItems();
        saveDataToServer();
      }
    }

    editorToolbar.addEventListener("click", (event) => {
      const button = event.target.closest('button');
      if (button && button.dataset.command) {
        document.execCommand(button.dataset.command, false, null);
        descriptionEditor.focus();
      }
    });

    async function loadInitialJson() {
      try {
        const response = await fetch(`/data/news-alerts.json?v=${new Date().getTime()}`);
        if (response.ok) {
          jsonData = await response.json();
        } else if (response.status === 404) {
          jsonData = [];
        } else {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
      } catch (error) {
        console.error("Error loading news-alerts.json:", error);
        alert("خطا در بارگذاری اولیه فایل JSON.");
        jsonData = [];
      } finally {
        renderItems();
      }
    }

    closeButton.onclick = closeModal;
    window.onclick = (event) => {
      if (event.target == itemModal) closeModal();
    };
    cancelEditBtn.onclick = closeModal;

    document.addEventListener("DOMContentLoaded", () => {
      loadInitialJson();
      new JalaliDatePicker("startDateDisplay", "startDate");
      new JalaliDatePicker("endDateDisplay", "endDate");
      new TimePicker("startTime");
      new TimePicker("endTime");
    });
  </script>
</body>

</html>
