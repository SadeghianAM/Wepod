<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta
    http-equiv="Cache-Control"
    content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <title>برنامه شیفت‌ها</title>
  <style>
    :root {
      --primary-color: #00ae70;
      --primary-dark: #089863;
      --primary-light: #e6f7f2;
      --bg-color: #f8fcf9;
      --text-color: #222;
      --secondary-text-color: #555;
      --card-bg: #ffffff;
      --header-text: #ffffff;
      --shadow-color-light: rgba(0, 174, 112, 0.07);
      --shadow-color-medium: rgba(0, 174, 112, 0.12);
      --border-radius: 0.75rem;
      --border-color: #e9e9e9;
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
      margin-bottom: 0;
      color: var(--header-text);
    }

    footer {
      height: 60px;
      font-size: 0.85rem;
      margin-top: auto;
    }

    main {
      width: 100%;
      flex-grow: 1;
      background-color: var(--card-bg);
      padding: 2rem;
      box-shadow: 0 4px 20px var(--shadow-color-light);
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

    .filter-group input[type="text"],
    .filter-group select {
      padding: 0.75rem;
      border-radius: 0.5rem;
      border: 1px solid var(--border-color);
      font-size: 1rem;
      width: 100%;
      background-color: var(--card-bg);
    }

    #loader {
      text-align: center;
      font-size: 1.2rem;
      color: var(--secondary-text-color);
      padding: 3rem;
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
      min-width: 960px;
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

    thead th:nth-child(1),
    tbody td:nth-child(1) {
      width: 60px;
      min-width: 60px;
      max-width: 60px;
    }

    thead th:nth-child(2),
    tbody td:nth-child(2) {
      width: 200px;
      min-width: 200px;
      max-width: 200px;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    thead th:nth-child(3),
    tbody td:nth-child(3) {
      width: 150px;
      min-width: 150px;
      max-width: 150px;
    }

    thead th:nth-child(4),
    tbody td:nth-child(4) {
      width: 150px;
      min-width: 150px;
      max-width: 150px;
    }

    thead th:nth-child(-n + 4),
    tbody td:nth-child(-n + 4) {
      position: sticky;
      z-index: 2;
    }

    thead th:nth-child(1),
    tbody td:nth-child(1) {
      right: 0;
    }

    thead th:nth-child(2),
    tbody td:nth-child(2) {
      right: 60px;
    }

    thead th:nth-child(3),
    tbody td:nth-child(3) {
      right: 260px;
    }

    thead th:nth-child(4),
    tbody td:nth-child(4) {
      right: 410px;
    }

    thead th:nth-child(-n + 4) {
      background-color: var(--primary-light);
      z-index: 4;
    }

    tbody td:nth-child(-n + 4) {
      background-color: var(--card-bg);
    }

    tbody tr:nth-child(even) td:nth-child(-n + 4) {
      background-color: var(--bg-color);
    }

    tbody tr:hover td:nth-child(-n + 4) {
      background-color: var(--primary-light);
    }

    tbody td:nth-child(2) {
      font-weight: 600;
      text-align: right;
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

    .status-remote {
      background-color: #ede7f6;
      color: #5e35b1;
    }

    .status-swap {
      background-color: var(--swap-color);
      color: var(--swap-text-color);
    }

    .tabs-container {
      display: flex;
      border-bottom: 2px solid var(--border-color);
      margin-bottom: 2rem;
    }

    .tab-button {
      padding: 1rem 1.5rem;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      border: none;
      background-color: transparent;
      color: var(--secondary-text-color);
      border-bottom: 3px solid transparent;
      transition: color 0.3s, border-color 0.3s;
      margin-left: 1rem;
    }

    .tab-button:hover {
      color: var(--primary-dark);
    }

    .tab-button.active {
      color: var(--primary-color);
      border-bottom-color: var(--primary-color);
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
      animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    .not-logged-in {
      text-align: center;
      padding: 3rem;
      background-color: #fff3cd;
      color: #856404;
      border: 1px solid #ffeeba;
      border-radius: var(--border-radius);
    }

    .not-logged-in a {
      color: var(--primary-dark);
      font-weight: bold;
    }

    #user-shift-info {
      background-color: var(--primary-light);
      padding: 1.5rem;
      border-radius: var(--border-radius);
      margin-bottom: 2rem;
      display: flex;
      justify-content: space-around;
      flex-wrap: wrap;
      gap: 1rem;
    }

    #user-shift-info span {
      font-weight: 700;
      color: var(--primary-dark);
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
      background-color: var(--card-bg);
      padding: 1rem;
      border-radius: var(--border-radius);
      border: 1px solid var(--border-color);
    }

    .calendar-header {
      text-align: center;
      font-weight: 600;
      padding: 0.8rem 0;
      color: var(--secondary-text-color);
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
    }

    .calendar-day.other-month {
      background-color: #f8f9fa;
      color: #ced4da;
    }

    .calendar-day .shift-info {
      padding: 0.4rem 0.5rem;
      border-radius: 0.3rem;
      color: white;
      text-align: center;
      font-weight: 500;
    }

    .shift-info.status-swap {
      color: var(--swap-text-color);
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
    }
  </style>
</head>

<body>
  <div id="header-placeholder"></div>
  <main>
    <div id="monthly-view">
      <h1>برنامه شیفت ماهانه</h1>
      <div class="filters-container">
        <div class="filter-group">
          <label for="startDate">از تاریخ:</label>
          <input
            type="text"
            id="startDate"
            placeholder="انتخاب تاریخ (جلالی)"
            autocomplete="off" />
          <input type="hidden" id="startDateAlt" />
        </div>
        <div class="filter-group">
          <label for="endDate">تا تاریخ:</label>
          <input
            type="text"
            id="endDate"
            placeholder="انتخاب تاریخ (جلالی)"
            autocomplete="off" />
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
    </div>
  </main>
  <div id="footer-placeholder"></div>

  <script src="/js/header.js?v=1.0"></script>
  <script src="/js/jalali-datepicker.js"></script>

  <script>
    function fetchNoCache(url, options = {}) {
      const timestamp = new Date().getTime();
      const separator = url.includes("?") ? "&" : "?";
      const urlWithCacheBust = `${url}${separator}t=${timestamp}`;
      return fetch(urlWithCacheBust, options);
    }

    let allExperts = [];
    let allAvailableDates = [];
    const shiftColorMap = new Map();
    const colorPalette = [
      "#E3F2FD",
      "#E8F5E9",
      "#FFF3E0",
      "#F3E5F5",
      "#FFEBEE",
      "#E0F7FA",
    ];
    let nextColorIndex = 0;

    document.addEventListener("DOMContentLoaded", initializePage);
    async function initializePage() {
      const loader = document.getElementById("loader");
      try {
        const response = await fetchNoCache("/php/get-shifts.php");
        if (!response.ok)
          throw new Error(
            `فایل shifts.json یافت نشد (کد: ${response.status})`
          );
        const data = await response.json();
        allExperts = data.experts || [];
        if (allExperts.length === 0) {
          loader.textContent = "هیچ اطلاعاتی برای نمایش وجود ندارد.";
          return;
        }
        populateFilterControls();
        document
          .getElementById("expertSelect1")
          .addEventListener("change", applyFilters);
        document
          .getElementById("expertSelect2")
          .addEventListener("change", applyFilters);
        applyFilters();
      } catch (error) {
        loader.textContent = `خطا: ${error.message}`;
        loader.style.color = "var(--error-color)";
      }
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
      const t = document.getElementById("expertSelect1"),
        e = document.getElementById("expertSelect2");
      let l = '<option value="none">-- هیچکدام --</option>';
      allExperts
        .sort((a, b) => a.name.localeCompare(b.name, "fa"))
        .forEach((t) => {
          l += `<option value="${t.id}">${t.name}</option>`;
        });
      t.innerHTML = l;
      e.innerHTML = l;
      t.value = "none";
      e.value = "none";
      const n = new Set();
      allExperts.forEach((t) => {
        Object.keys(t.shifts).forEach((d) => n.add(d));
      });
      allAvailableDates = Array.from(n).sort();
    }

    function applyFilters() {
      const t = document.getElementById("startDateAlt").value;
      const e = document.getElementById("endDateAlt").value;
      const l = document.getElementById("expertSelect1").value;
      const n = document.getElementById("expertSelect2").value;
      const o = new Set();
      if (l !== "none") o.add(l);
      if (n !== "none") o.add(n);
      const a = allAvailableDates.filter(
        (d) => (!t || d >= t) && (!e || d <= e)
      );
      let r =
        o.size > 0 ?
        allExperts.filter((x) => o.has(String(x.id))) : [...allExperts];
      r.sort((A, B) => {
        const stA = A["shifts-time"] || "",
          stB = B["shifts-time"] || "";
        const c1 = stA.localeCompare(stB, "fa");
        if (c1 !== 0) return c1;
        const btA = A["break-time"] || "",
          btB = B["break-time"] || "";
        return btA.localeCompare(btB, "fa");
      });
      renderTable(r, a);
    }

    function getShiftDetails(shiftEntry) {
      if (
        typeof shiftEntry === "object" &&
        shiftEntry !== null &&
        shiftEntry.status === "swap"
      ) {
        return {
          status: "swap",
          displayText: shiftEntry.displayText,
          isSwap: true,
          linkedTo: shiftEntry.linkedTo,
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
        linkedTo: null,
      };
    }

    function renderTable(expertsToRender, datesToRender) {
      const container = document.getElementById("schedule-container");
      const loader = document.getElementById("loader");
      if (
        !expertsToRender ||
        expertsToRender.length === 0 ||
        !datesToRender ||
        datesToRender.length === 0
      ) {
        container.innerHTML = "";
        loader.textContent = "هیچ داده‌ای مطابق با فیلترهای شما یافت نشد.";
        loader.style.display = "block";
        return;
      }
      loader.style.display = "none";
      let tableHtml = `<table><thead><tr><th>ردیف</th><th>نام کارشناس</th><th>ساعت شیفت</th><th>تایم استراحت</th>`;
      datesToRender.forEach((date) => {
        const d = new Date(date);
        const day = d.toLocaleDateString("fa-IR", {
          day: "numeric",
        });
        const month = d.toLocaleDateString("fa-IR", {
          month: "short",
        });
        const weekday = d.toLocaleDateString("fa-IR", {
          weekday: "short",
        });
        tableHtml += `<th>${weekday}<br>${day} ${month}<br><span style="font-size:.8rem;color:#6c757d;font-weight:400;">${date}</span></th>`;
      });
      tableHtml += "</tr></thead><tbody>";
      expertsToRender.forEach((expert, index) => {
        const shiftTime = expert["shifts-time"] || "-";
        const breakTime = expert["break-time"] || "-";
        const shiftStyle = getShiftStyle(shiftTime);
        tableHtml += `<tr><td>${new Intl.NumberFormat("fa-IR").format(
            index + 1
          )}</td><td>${
            expert.name
          }</td><td ${shiftStyle}>${shiftTime}</td><td>${breakTime}</td>`;
        datesToRender.forEach((date) => {
          const shiftDetails = getShiftDetails(expert.shifts[date]);
          let statusClass = "";
          if (shiftDetails.isSwap) {
            statusClass = "status-swap";
          } else {
            const classMap = {
              "on-duty": "status-on-duty",
              remote: "status-remote",
              off: "status-off",
              leave: "status-special",
              unknown: "status-unknown",
            };
            statusClass = classMap[shiftDetails.status] || "status-special";
          }
          tableHtml += `<td><span class="status ${statusClass}">${shiftDetails.displayText}</span></td>`;
        });
        tableHtml += "</tr>";
      });
      tableHtml += "</tbody></table>";
      container.innerHTML = tableHtml;
    }

    document.addEventListener("DOMContentLoaded", () => {
      // Set initial dates
      const today = new Date();
      const nextWeek = new Date();
      nextWeek.setDate(today.getDate() + 7);

      // Set start date inputs
      const [startJy, startJm, startJd] = toPersian(today);
      document.getElementById("startDate").value = formatJalaliDisplay(startJy, startJm, startJd);
      document.getElementById("startDateAlt").value = formatISO(today);

      // Set end date inputs
      const [endJy, endJm, endJd] = toPersian(nextWeek);
      document.getElementById("endDate").value = formatJalaliDisplay(endJy, endJm, endJd);
      document.getElementById("endDateAlt").value = formatISO(nextWeek);

      // Initialize date pickers
      new JalaliDatePicker("startDate", "startDateAlt");
      new JalaliDatePicker("endDate", "endDateAlt");

      // Add event listeners to trigger filtering when a date is selected
      document
        .getElementById("startDateAlt")
        .addEventListener("change", applyFilters);
      document
        .getElementById("endDateAlt")
        .addEventListener("change", applyFilters);
    });
  </script>
</body>

</html>
