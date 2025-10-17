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
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <title>شیفت نگار</title>
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

    .custom-select-container {
      position: relative;
    }

    .custom-select-input {
      padding: 0.75rem;
      border-radius: 0.5rem;
      border: 1px solid var(--border-color);
      font-size: 1rem;
      width: 100%;
      background-color: var(--card-bg);
      cursor: pointer;
    }

    .custom-select-options {
      display: none;
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background-color: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 0.5rem;
      max-height: 200px;
      overflow-y: auto;
      z-index: 100;
      margin-top: 4px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .custom-select-option {
      padding: 0.75rem;
      cursor: pointer;
      font-size: 1rem;
    }

    .custom-select-option:hover {
      background-color: var(--primary-light);
    }

    .custom-select-option.hidden {
      display: none;
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

    .status-remote {
      background-color: #d8b100;
    }

    .status-special {
      background-color: #5487df;
    }

    .status-leave {
      background-color: #ec7433;
    }

    .status-swap {
      background-color: var(--swap-color);
      color: var(--swap-text-color);
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
          <input type="text" id="startDate" placeholder="انتخاب تاریخ (جلالی)" autocomplete="off" />
          <input type="hidden" id="startDateAlt" />
        </div>
        <div class="filter-group">
          <label for="endDate">تا تاریخ:</label>
          <input type="text" id="endDate" placeholder="انتخاب تاریخ (جلالی)" autocomplete="off" />
          <input type="hidden" id="endDateAlt" />
        </div>
        <div class="filter-group">
          <label for="shiftFilterSelect">فیلتر بر اساس شیفت:</label>
          <select id="shiftFilterSelect"></select>
        </div>
        <div class="filter-group">
          <label for="expertSelect1-input">انتخاب کارشناس اول:</label>
          <div id="expertSelect1-container" class="custom-select-container"></div>
          <input type="hidden" id="expertSelect1-value" value="none">
        </div>
        <div class="filter-group">
          <label for="expertSelect2-input">انتخاب کارشناس دوم:</label>
          <div id="expertSelect2-container" class="custom-select-container"></div>
          <input type="hidden" id="expertSelect2-value" value="none">
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
    let allExperts = [];
    let allAvailableDates = [];
    const shiftColorMap = new Map();
    const colorPalette = ["#E3F2FD", "#E8F5E9", "#FFF3E0", "#F3E5F5", "#FFEBEE", "#E0F7FA"];
    let nextColorIndex = 0;

    function fetchNoCache(url, options = {}) {
      const timestamp = new Date().getTime();
      const separator = url.includes("?") ? "&" : "?";
      const urlWithCacheBust = `${url}${separator}t=${timestamp}`;
      return fetch(urlWithCacheBust, options);
    }

    function createCustomSelect(containerId, hiddenInputId, experts) {
      const container = document.getElementById(containerId);
      const hiddenInput = document.getElementById(hiddenInputId);
      container.innerHTML = `<input type="text" id="${containerId}-input" class="custom-select-input" placeholder="جستجو و انتخاب کارشناس..." autocomplete="off">
                               <div class="custom-select-options"></div>`;

      const textInput = container.querySelector('.custom-select-input');
      const optionsContainer = container.querySelector('.custom-select-options');

      let optionsHtml = '<div class="custom-select-option" data-value="none">-- هیچکدام --</div>';
      experts.forEach(expert => {
        optionsHtml += `<div class="custom-select-option" data-value="${expert.id}">${expert.name}</div>`;
      });
      optionsContainer.innerHTML = optionsHtml;

      textInput.addEventListener('focus', () => {
        optionsContainer.style.display = 'block';
      });

      textInput.addEventListener('input', () => {
        const filter = textInput.value.toLowerCase();
        optionsContainer.querySelectorAll('.custom-select-option').forEach(option => {
          const text = option.textContent.toLowerCase();
          option.classList.toggle('hidden', !text.includes(filter));
        });
      });

      optionsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('custom-select-option')) {
          const selectedValue = e.target.getAttribute('data-value');
          const selectedText = e.target.textContent;
          hiddenInput.value = selectedValue;
          textInput.value = selectedValue === 'none' ? '' : selectedText;
          optionsContainer.style.display = 'none';
          hiddenInput.dispatchEvent(new Event('change'));
        }
      });

      document.addEventListener('click', (e) => {
        if (!container.contains(e.target)) {
          optionsContainer.style.display = 'none';
        }
      });
    }

    document.addEventListener("DOMContentLoaded", initializePage);

    async function initializePage() {
      const loader = document.getElementById("loader");
      try {
        const response = await fetchNoCache("/php/get-shifts.php");
        if (!response.ok) throw new Error(`فایل shifts.json یافت نشد (کد: ${response.status})`);

        const data = await response.json();
        allExperts = data.experts || [];

        if (allExperts.length === 0) {
          loader.textContent = "هیچ اطلاعاتی برای نمایش وجود ندارد.";
          return;
        }

        const dateSet = new Set();
        allExperts.forEach((expert) => {
          Object.keys(expert.shifts).forEach((d) => dateSet.add(d));
        });
        allAvailableDates = Array.from(dateSet).sort();

        // START: Modified filter population and event listeners
        populateShiftFilter();
        populateExpertFilters();

        document.getElementById('shiftFilterSelect').addEventListener('change', () => {
          populateExpertFilters();
          applyFilters();
        });
        document.getElementById('expertSelect1-value').addEventListener('change', applyFilters);
        document.getElementById('expertSelect2-value').addEventListener('change', applyFilters);
        // END: Modified filter population and event listeners

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

    // START: New and modified filter functions
    function populateShiftFilter() {
      const shiftSelect = document.getElementById("shiftFilterSelect");
      const uniqueShifts = [...new Set(allExperts.map(e => e["shifts-time"]).filter(Boolean))].sort();
      let optionsHtml = '<option value="all">-- همه شیفت‌ها --</option>';
      uniqueShifts.forEach(shift => {
        optionsHtml += `<option value="${shift}">${shift}</option>`;
      });
      shiftSelect.innerHTML = optionsHtml;
    }

    function populateExpertFilters() {
      const selectedShift = document.getElementById("shiftFilterSelect").value;
      const expertsForDropdown = (selectedShift === 'all') ?
        allExperts :
        allExperts.filter(expert => expert["shifts-time"] === selectedShift);

      const sortedExperts = [...expertsForDropdown].sort((a, b) => a.name.localeCompare(b.name, "fa"));

      const oldVal1 = document.getElementById('expertSelect1-value').value;
      const oldVal2 = document.getElementById('expertSelect2-value').value;

      createCustomSelect('expertSelect1-container', 'expertSelect1-value', sortedExperts);
      createCustomSelect('expertSelect2-container', 'expertSelect2-value', sortedExperts);

      const expert1Exists = sortedExperts.some(e => String(e.id) === oldVal1);
      if (expert1Exists) {
        document.getElementById('expertSelect1-value').value = oldVal1;
        document.getElementById('expertSelect1-container-input').value = sortedExperts.find(e => String(e.id) === oldVal1).name;
      }

      const expert2Exists = sortedExperts.some(e => String(e.id) === oldVal2);
      if (expert2Exists) {
        document.getElementById('expertSelect2-value').value = oldVal2;
        document.getElementById('expertSelect2-container-input').value = sortedExperts.find(e => String(e.id) === oldVal2).name;
      }
    }

    function applyFilters() {
      const startDate = document.getElementById("startDateAlt").value;
      const endDate = document.getElementById("endDateAlt").value;
      const selectedShift = document.getElementById("shiftFilterSelect").value;
      const expert1 = document.getElementById("expertSelect1-value").value;
      const expert2 = document.getElementById("expertSelect2-value").value;

      let filteredExperts = [...allExperts];

      if (selectedShift !== 'all') {
        filteredExperts = filteredExperts.filter(expert => expert["shifts-time"] === selectedShift);
      }

      const selectedExpertIds = new Set();
      if (expert1 !== "none") selectedExpertIds.add(expert1);
      if (expert2 !== "none") selectedExpertIds.add(expert2);

      if (selectedExpertIds.size > 0) {
        filteredExperts = filteredExperts.filter((x) => selectedExpertIds.has(String(x.id)));
      }

      const filteredDates = allAvailableDates.filter(
        (d) => (!startDate || d >= startDate) && (!endDate || d <= endDate)
      );

      filteredExperts.sort((a, b) => {
        const stA = a["shifts-time"] || "",
          stB = b["shifts-time"] || "";
        const c1 = stA.localeCompare(stB, "fa");
        if (c1 !== 0) return c1;
        const btA = a["break-time"] || "",
          btB = b["break-time"] || "";
        return btA.localeCompare(btB, "fa");
      });

      renderTable(filteredExperts, filteredDates);
    }
    // END: New and modified filter functions

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

    function renderTable(expertsToRender, datesToRender) {
      const container = document.getElementById("schedule-container");
      const loader = document.getElementById("loader");

      if (!expertsToRender || expertsToRender.length === 0 || !datesToRender || datesToRender.length === 0) {
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
          day: "numeric"
        });
        const month = d.toLocaleDateString("fa-IR", {
          month: "short"
        });
        const weekday = d.toLocaleDateString("fa-IR", {
          weekday: "short"
        });
        tableHtml += `<th>${weekday}<br>${day} ${month}<br><span style="font-size:.8rem;color:#6c757d;font-weight:400;">${date}</span></th>`;
      });
      tableHtml += "</tr></thead><tbody>";

      expertsToRender.forEach((expert, index) => {
        const shiftTime = expert["shifts-time"] || "-";
        const breakTime = expert["break-time"] || "-";
        const shiftStyle = getShiftStyle(shiftTime);
        tableHtml += `<tr><td>${new Intl.NumberFormat("fa-IR").format(index + 1)}</td><td>${expert.name}</td><td ${shiftStyle}>${shiftTime}</td><td>${breakTime}</td>`;

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
              leave: "status-leave",
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
      const today = new Date();
      const nextWeek = new Date();
      nextWeek.setDate(today.getDate() + 7);

      const [startJy, startJm, startJd] = toPersian(today);
      document.getElementById("startDate").value = formatJalaliDisplay(startJy, startJm, startJd);
      document.getElementById("startDateAlt").value = formatISO(today);

      const [endJy, endJm, endJd] = toPersian(nextWeek);
      document.getElementById("endDate").value = formatJalaliDisplay(endJy, endJm, endJd);
      document.getElementById("endDateAlt").value = formatISO(nextWeek);

      new JalaliDatePicker("startDate", "startDateAlt");
      new JalaliDatePicker("endDate", "endDateAlt");

      document.getElementById("startDateAlt").addEventListener("change", applyFilters);
      document.getElementById("endDateAlt").addEventListener("change", applyFilters);
    });
  </script>
</body>

</html>
