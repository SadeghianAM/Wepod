<?php
require __DIR__ . '/../php/auth_check.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>مدیریت اختلالات مرکز تماس</title>

  <style>
    /* Base Styles */
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
    }

    @font-face {
      font-family: "Vazirmatn";
      src: url("/assets/fonts/Vazirmatn[wght].ttf") format("truetype");
      font-weight: 100 900;
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
      background-color: #f4fbf7;
      color: #222;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    header {
      background: #00ae70;
      color: white;
      padding: 1rem 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 8px rgba(0, 174, 112, 0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    header h1 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: 700;
    }

    main {
      flex-grow: 1;
      padding: 2rem;
      max-width: 1400px;
      width: 100%;
      margin: 0 auto;
    }

    h2 {
      font-size: 1.6rem;
      margin-bottom: 1.5rem;
      margin-top: 2rem;
      color: #00ae70;
      text-align: center;
      font-weight: 700;
    }

    .form-container {
      background-color: #ffffff;
      padding: 1.5rem 2rem;
      border-radius: 0.75rem;
      margin-bottom: 2rem;
      box-shadow: 0 2px 12px rgba(0, 174, 112, 0.07);
      border: 1px solid #e9e9e9;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
    }

    .form-group {
      display: flex;
      flex-direction: column;
    }

    .form-group.full-width {
      grid-column: 1 / -1;
    }

    .form-group label {
      font-weight: 600;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
      color: #333;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 0.5rem;
      font-size: 1rem;
      background-color: #fcfcfc;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-group input[type="text"]:read-only {
      cursor: pointer;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      border-color: #00ae70;
      outline: none;
      box-shadow: 0 0 0 3px rgba(0, 174, 112, 0.15);
    }

    .form-actions {
      margin-top: 1.5rem;
      display: flex;
      gap: 1rem;
    }

    .form-actions button {
      padding: 10px 20px;
      border: none;
      border-radius: 0.5rem;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 500;
      transition: all 0.2s;
      color: white;
    }

    .btn-save {
      background-color: #00ae70;
      box-shadow: 0 4px 10px rgba(0, 174, 112, 0.2);
    }

    .btn-save:hover {
      background-color: #089863;
      transform: translateY(-2px);
    }

    .btn-cancel {
      background-color: #6c757d;
    }

    .btn-cancel:hover {
      background-color: #5a6268;
    }

    .table-container {
      width: 100%;
      overflow-x: auto;
      border: 1px solid #e0e0e0;
      border-radius: 0.75rem;
      box-shadow: 0 2px 12px rgba(0, 174, 112, 0.07);
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
      border-bottom: 1px solid #e0e0e0;
      white-space: nowrap;
    }

    td.description-cell {
      white-space: normal;
      min-width: 300px;
      text-align: right;
    }

    thead th {
      background-color: #f0fdf7;
      color: #089863;
      font-weight: 700;
      position: sticky;
      top: 0;
      z-index: 2;
    }

    tbody tr:last-child td {
      border-bottom: none;
    }

    tbody tr:hover {
      background-color: #e6f7f2;
    }

    .status {
      padding: 0.4em 1em;
      border-radius: 1rem;
      font-weight: 500;
      font-size: 0.85rem;
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

    .action-btn {
      padding: 0.5rem 0.9rem;
      border: none;
      border-radius: 0.5rem;
      cursor: pointer;
      font-size: 0.9rem;
      color: white;
      transition: opacity 0.2s;
      margin: 0 0.2rem;
    }

    .action-btn:hover {
      opacity: 0.85;
    }

    .edit-btn {
      background-color: #007bff;
    }

    .delete-btn {
      background-color: #dc3545;
    }

    footer {
      background: #00ae70;
      color: #e0e7ff;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.9rem;
      box-shadow: 0 -2px 8px rgba(0, 174, 112, 0.08);
      margin-top: auto;
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

    @media (max-width: 768px) {
      main {
        padding: 1.5rem 1rem;
      }

      header h1 {
        font-size: 1.2rem;
      }

      h2 {
        font-size: 1.3rem;
      }
    }
  </style>
</head>

<body>
  <div id="header-placeholder"></div>

  <main>
    <div class="form-container">
      <form id="disruptionForm">
        <input type="hidden" id="recordId" name="id" />
        <div class="form-grid">
          <div class="form-group">
            <label for="dayOfWeek">روز هفته</label>
            <select id="dayOfWeek" name="dayOfWeek" required>
              <option value="شنبه">شنبه</option>
              <option value="یکشنبه">یکشنبه</option>
              <option value="دوشنبه">دوشنبه</option>
              <option value="سه‌شنبه">سه‌شنبه</option>
              <option value="چهارشنبه">چهارشنبه</option>
              <option value="پنجشنبه">پنجشنبه</option>
              <option value="جمعه">جمعه</option>
            </select>
          </div>
          <div class="form-group">
            <label for="subject">موضوع</label>
            <select id="subject" name="subject" required></select>
          </div>
          <div class="form-group">
            <label for="status">وضعیت اختلال</label>
            <select id="status" name="status" required>
              <option value="باز">باز</option>
              <option value="درحال رسیدگی">درحال رسیدگی</option>
              <option value="برطرف شده">برطرف شده</option>
            </select>
          </div>
          <div class="form-group">
            <label for="reportingTeam">تیم گزارش‌دهنده</label>
            <select id="reportingTeam" name="reportingTeam" required>
              <option value="اعلام از سمت فنی">اعلام از سمت فنی</option>
              <option value="اعلام از سمت مرکز تماس">
                اعلام از سمت مرکز تماس
              </option>
            </select>
          </div>
          <div class="form-group">
            <label for="startDateDisplay">تاریخ شروع</label>
            <input
              type="text"
              id="startDateDisplay"
              placeholder="انتخاب تاریخ"
              autocomplete="off"
              readonly
              required />
            <input type="hidden" id="startDate" name="startDate" />
          </div>
          <div class="form-group">
            <label for="startTime">ساعت شروع</label>
            <input type="time" id="startTime" name="startTime" required />
          </div>
          <div class="form-group">
            <label for="endDateDisplay">تاریخ پایان</label>
            <input
              type="text"
              id="endDateDisplay"
              placeholder="انتخاب تاریخ"
              autocomplete="off"
              readonly />
            <input type="hidden" id="endDate" name="endDate" />
          </div>
          <div class="form-group">
            <label for="endTime">ساعت پایان</label>
            <input type="time" id="endTime" name="endTime" />
          </div>
          <div class="form-group full-width">
            <label for="description">توضیح اختلال</label>
            <textarea id="description" name="description" rows="3"></textarea>
          </div>
          <input type="hidden" id="totalDuration" name="totalDuration" />
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-save">ذخیره رکورد</button>
          <button type="button" id="clearBtn" class="btn-cancel">
            پاک کردن فرم
          </button>
        </div>
      </form>
    </div>

    <h2>لیست اختلالات ثبت‌شده</h2>
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
    /* ===== Jalali DatePicker Code ===== */
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
      sal_a = [
        0,
        31,
        (gy % 4 === 0 && gy % 100 !== 0) || gy % 400 === 0 ? 29 : 28,
        31,
        30,
        31,
        30,
        31,
        31,
        30,
        31,
        30,
        31,
      ];
      for (gm = 0; gm < 13 && gd > sal_a[gm]; gm++) gd -= sal_a[gm];
      return new Date(gy, gm - 1, gd);
    }

    function toPersian(date) {
      const parts = date.toLocaleDateString("fa-IR-u-nu-latn").split("/");
      return parts.map((part) => parseInt(part, 10));
    }

    function formatJalaliDisplay(jy, jm, jd) {
      return `${jy}/${String(jm).padStart(2, "0")}/${String(jd).padStart(
          2,
          "0"
        )}`;
    }

    function formatISO(date) {
      return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(
          2,
          "0"
        )}-${String(date.getDate()).padStart(2, "0")}`;
    }

    function isJalaliLeap(jy) {
      return (
        ((((((jy - 474) % 2820) + 2820) % 2820) + 474 + 38) * 682) % 2816 <
        682
      );
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
        setTimeout(
          () =>
          document.addEventListener("mousedown", this.boundClickOutside),
          0
        );
      }
      hide() {
        this.pop.classList.add("jdp-hidden");
        document.removeEventListener("mousedown", this.boundClickOutside);
      }
      position() {
        const rect = this.input.getBoundingClientRect();
        this.pop.style.top = window.scrollY + rect.bottom + 6 + "px";
        this.pop.style.left = window.scrollX + rect.left + "px";
        if (rect.left + 280 > window.innerWidth) {
          this.pop.style.left = window.scrollX + rect.right - 280 + "px";
        }
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
        let html = `<div class="jdp-header"><button type="button" class="jdp-nav-btn" data-nav="-1">&rarr;</button><div>${new Intl.DateTimeFormat(
            "fa-IR",
            { month: "long" }
          ).format(firstG)} ${new Intl.DateTimeFormat("fa-IR-u-nu-latn", {
            year: "numeric",
          }).format(
            firstG
          )}</div><button type="button" class="jdp-nav-btn" data-nav="1">&larr;</button></div><div class="jdp-grid">${weekDays
            .map((w) => `<div class="jdp-weekday">${w}</div>`)
            .join("")}`;
        for (let i = 0; i < firstWeekday; i++) {
          html += `<div class="jdp-day other"></div>`;
        }
        for (let d = 1; d <= daysInMonth; d++) {
          html += `<div class="jdp-day" data-day="${d}">${new Intl.NumberFormat(
              "fa-IR"
            ).format(d)}</div>`;
        }
        html += `</div>`;
        this.pop.innerHTML = html;
        this.pop.querySelectorAll("[data-nav]").forEach((btn) => {
          btn.addEventListener("click", (e) =>
            this.nav(parseInt(e.currentTarget.dataset.nav, 10))
          );
        });
        this.pop.querySelectorAll("[data-day]").forEach((cell) => {
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

    /* ===== Main Application Logic ===== */
    document.addEventListener("DOMContentLoaded", () => {
      const API_URL = "/php/update-disruptions.php";

      const form = document.getElementById("disruptionForm");
      const tableBody = document.getElementById("disruptionTableBody");
      const recordIdInput = document.getElementById("recordId");
      const clearBtn = document.getElementById("clearBtn");
      const subjectSelect = document.getElementById("subject");

      const startDateInput = document.getElementById("startDate");
      const startTimeInput = document.getElementById("startTime");
      const endDateInput = document.getElementById("endDate");
      const endTimeInput = document.getElementById("endTime");
      const totalDurationInput = document.getElementById("totalDuration");

      let currentRecords = [];

      const subjects = [
        "اختلال در اپلیکیشن",
        "اختلال در ارتباط با واحد بازاریابی",
        "اختلال در ارتقاء/تغییر سطح",
        "اختلال در اعتبار سنجی",
        "اختلال در انتقال وجه",
        "اختلال در بانکداری ویدئویی",
        "اختلال در بیمه پاسارگاد",
        "اختلال در پنل CRM",
        "اختلال در تمامی تسهیلات",
        "اختلال در تسهیلات برآیند",
        "اختلال در تسهیلات پشتوانه",
        "اختلال در تسهیلات پیش درآمد",
        "اختلال در تسهیلات سازمانی",
        "اختلال در تسهیلات کاپ کارت",
        "اختلال در تسویه کارت خوان ها",
        "اختلال در تسویه معوقات",
        "اختلال در تغییر شماره تلفن همراه",
        "اختلال در تنظیمات امنیت حساب",
        "اختلال در چک",
        "اختلال چکاد",
        "اختلال در خدمات قبض",
        "اختلال در دریافت پیامک",
        "اختلال در دعوت از دوستان",
        "اختلال در سرویس درگاه پاد",
        "اختلال در سرویس مالی پاد",
        "کندی و قطعی پنل پاد",
        "اختلال در سرویس ثبت احوال",
        "اختلال در سرویس سمات",
        "اختلال در سرویس سیاح",
        "اختلال در سرویس شاهکار",
        "اختلال در سرویس شرکت ملی پست ایران",
        "اختلال در صندوق های سرمایه گذاری",
        "اختلال در طرح سرمایه گذاری جوانه",
        "اختلال در طرح سرمایه گذاری رویش",
        "اختلال در طرح کاوه",
        "اختلال در کارت فیزیکی",
        "اختلال در کارت و حساب دیجیتال",
        "اختلال در کارت و اعتبار هدیه دیجیتال",
        "اختلال در مسدودی و رفع مسدودی حساب",
        "اختلال در وی کلاب",
        "اختلال دستگاه پوز",
        "اختلال رمز دو عاملی",
        "اختلال کد شهاب",
        "مشکلات شعب",
        "سایر اختلالات",
        "اختلال در خرید شارژ و اینترنت",
        "اختلال ورود به برنامه",
        "اختلال در تسهیلات پیمان",
        "افزایش موجودی",
        "اختلال افتتاح حساب جاری",
      ];
      subjects.forEach((subject) => {
        const option = document.createElement("option");
        option.value = subject;
        option.textContent = subject;
        subjectSelect.appendChild(option);
      });

      new JalaliDatePicker("startDateDisplay", "startDate");
      new JalaliDatePicker("endDateDisplay", "endDate");

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

      form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(form);

        try {
          const response = await fetch(API_URL, {
            method: "POST",
            body: formData,
          });
          if (!response.ok) throw new Error("خطا در ذخیره سازی اطلاعات");
          const result = await response.json();
          console.log(result.message);
          resetForm();
          loadRecords(); // Refresh table from server
        } catch (error) {
          console.error("Submit Error:", error);
          alert(error.message);
        }
      });

      [startDateInput, startTimeInput, endDateInput, endTimeInput].forEach(
        (input) => {
          input.addEventListener("change", calculateDuration);
        }
      );

      clearBtn.addEventListener("click", resetForm);

      function calculateDuration() {
        if (
          startDateInput.value &&
          startTimeInput.value &&
          endDateInput.value &&
          endTimeInput.value
        ) {
          const startDate = new Date(
            `${startDateInput.value}T${startTimeInput.value}`
          );
          const endDate = new Date(
            `${endDateInput.value}T${endTimeInput.value}`
          );
          if (endDate < startDate) {
            totalDurationInput.value = "نامعتبر";
            return;
          }
          let totalMinutes = (endDate - startDate) / 60000;
          if (totalMinutes < 1) {
            totalDurationInput.value = "";
            return;
          }
          const parts = [];
          if (totalMinutes >= 1440) {
            const days = Math.floor(totalMinutes / 1440);
            parts.push(`${days} روز`);
            totalMinutes %= 1440;
          }
          if (totalMinutes >= 60) {
            const hours = Math.floor(totalMinutes / 60);
            parts.push(`${hours} ساعت`);
            totalMinutes %= 60;
          }
          const minutes = Math.round(totalMinutes);
          if (minutes > 0) {
            parts.push(`${minutes} دقیقه`);
          }
          totalDurationInput.value =
            parts.length > 0 ? parts.join(" و ") : "کمتر از یک دقیقه";
        } else {
          totalDurationInput.value = "";
        }
      }

      function renderTable(records) {
        tableBody.innerHTML = "";
        const statusClassMap = {
          باز: "status-open",
          "درحال رسیدگی": "status-in-progress",
          "برطرف شده": "status-resolved",
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
            const durationDisplay =
              record.endDate && record.endTime && record.totalDuration ?
              record.totalDuration :
              "—";
            const startDateJalali = record.startDate ?
              new Date(record.startDate).toLocaleDateString("fa-IR") :
              "—";
            const endDateJalali = record.endDate ?
              new Date(record.endDate).toLocaleDateString("fa-IR") :
              "—";
            tr.innerHTML = `
                        <td>${record.dayOfWeek || "—"}</td> <td>${
                record.subject || "—"
              }</td>
                        <td><span class="status ${statusClass}">${
                record.status || "—"
              }</span></td>
                        <td>${startDateJalali}</td> <td>${
                record.startTime || "—"
              }</td>
                        <td>${endDateJalali}</td> <td>${
                record.endTime || "—"
              }</td>
                        <td>${durationDisplay}</td> <td>${
                record.reportingTeam || "—"
              }</td>
                        <td class="description-cell">${
                          record.description || "—"
                        }</td>
                        <td>
                            <button class="action-btn edit-btn" data-id="${
                              record.id
                            }">ویرایش</button>
                            <button class="action-btn delete-btn" data-id="${
                              record.id
                            }">حذف</button>
                        </td>
                    `;
          });
        }

        tableBody
          .querySelectorAll(".edit-btn")
          .forEach((btn) =>
            btn.addEventListener("click", () => handleEdit(btn.dataset.id))
          );
        tableBody
          .querySelectorAll(".delete-btn")
          .forEach((btn) =>
            btn.addEventListener("click", () => handleDelete(btn.dataset.id))
          );
      }

      function handleEdit(id) {
        const record = currentRecords.find((r) => r.id === id);
        if (record) {
          recordIdInput.value = record.id;
          form.querySelector("#dayOfWeek").value = record.dayOfWeek;
          form.querySelector("#subject").value = record.subject;
          form.querySelector("#status").value = record.status;
          form.querySelector("#reportingTeam").value = record.reportingTeam;

          const startDateDisplay =
            document.getElementById("startDateDisplay");
          startDateInput.value = record.startDate;
          startDateDisplay.value = record.startDate ?
            new Date(record.startDate).toLocaleDateString("fa-IR") :
            "";

          const endDateDisplay = document.getElementById("endDateDisplay");
          endDateInput.value = record.endDate;
          endDateDisplay.value = record.endDate ?
            new Date(record.endDate).toLocaleDateString("fa-IR") :
            "";

          form.querySelector("#startTime").value = record.startTime;
          form.querySelector("#endTime").value = record.endTime;
          form.querySelector("#description").value = record.description;

          calculateDuration();
          window.scrollTo({
            top: 0,
            behavior: "smooth"
          });
        }
      }

      async function handleDelete(id) {
        if (confirm("آیا از حذف این رکورد مطمئن هستید؟")) {
          const formData = new FormData();
          formData.append("action", "delete");
          formData.append("id", id);
          try {
            const response = await fetch(API_URL, {
              method: "POST",
              body: formData,
            });
            if (!response.ok) throw new Error("خطا در حذف رکورد");
            const result = await response.json();
            console.log(result.message);
            loadRecords(); // Refresh table
          } catch (error) {
            console.error("Delete Error:", error);
            alert(error.message);
          }
        }
      }

      function resetForm() {
        form.reset();
        recordIdInput.value = "";
        totalDurationInput.value = "";
        document.getElementById("startDate").value = "";
        document.getElementById("endDate").value = "";
      }

      // Initial load of data from the server
      loadRecords();
    });
  </script>
</body>

</html>
