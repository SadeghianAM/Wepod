<?php
require __DIR__ . '/../php/auth_check.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت برنامه شیفت‌ها</title>
    <style>
      /* --- General Styles --- */
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
    </style>
  </head>
  <body>
    <main>
      <h1>مدیریت برنامه شیفت‌ها</h1>
      <div class="filters-container">
        <div class="filter-group">
          <label for="startDate">از تاریخ:</label>
          <input type="date" id="startDate" />
        </div>
        <div class="filter-group">
          <label for="endDate">تا تاریخ:</label>
          <input type="date" id="endDate" />
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
              <option value="custom">
                سایر موارد (در کادر زیر بنویسید)
              </option>
            </select>
          </div>
          <div
            class="form-group"
            id="custom-status-group"
            style="display: none"
          >
            <label for="custom-shift-status">وضعیت سفارشی</label>
            <input
              type="text"
              id="custom-shift-status"
              placeholder="مثلا: ماموریت، آموزش، یا 10:00 - 19:00"
            />
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
      let currentEditingInfo = { expertId: null, date: null };

      const shiftColorMap = new Map();
      const colorPalette = [
        "#E3F2FD", "#E8F5E9", "#FFF3E0",
        "#F3E5F5", "#FFEBEE", "#E0F7FA",
      ];
      let nextColorIndex = 0;

      document.addEventListener("DOMContentLoaded", initializePage);

      async function initializePage() {
        const loader = document.getElementById("loader");
        loader.style.display = "block";
        try {
          const response = await fetch(
            "/data/shifts.json?v=" + new Date().getTime()
          );
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
          setupEventListeners();
          applyFilters();
        } catch (error) {
          loader.textContent = `خطا در بارگذاری اولیه: ${error.message}`;
        }
      }

      function setupEventListeners() {
        document
            .getElementById("startDate")
            .addEventListener("change", applyFilters);
        document
            .getElementById("endDate")
            .addEventListener("change", applyFilters);
        document
            .getElementById("expertSelect1")
            .addEventListener("change", applyFilters);
        document
            .getElementById("expertSelect2")
            .addEventListener("change", applyFilters);
        document
            .getElementById("modal-cancel-btn")
            .addEventListener("click", closeEditModal);
        document
            .getElementById("modal-save-btn")
            .addEventListener("click", saveShiftUpdate);

        document
            .getElementById("shift-status-select")
            .addEventListener("change", (e) => {
            document.getElementById("custom-status-group").style.display =
                e.target.value === "custom" ? "block" : "none";
            });

        document
            .getElementById("schedule-container")
            .addEventListener("click", (e) => {
            const cell = e.target.closest(".editable-cell");
            if (cell) {
                const { expertId, date, currentStatus } = cell.dataset;
                openEditModal(expertId, date, currentStatus);
            }
            });
    }

      function openEditModal(expertId, date, currentStatus) {
        currentEditingInfo = { expertId, date };
        const expert = allExperts.find((exp) => exp.id == expertId);

        document.getElementById("modal-expert-name").textContent = expert.name;
        document.getElementById("modal-shift-date").textContent = new Date(
          date
        ).toLocaleDateString("fa-IR");

        const statusSelect = document.getElementById("shift-status-select");
        const customStatusInput = document.getElementById("custom-shift-status");
        const customGroup = document.getElementById("custom-status-group");

        const standardStatuses = ["on-duty", "off", "leave"];
        if (standardStatuses.includes(currentStatus)) {
          statusSelect.value = currentStatus;
          customGroup.style.display = "none";
          customStatusInput.value = "";
        } else if (currentStatus && currentStatus !== "unknown") {
          statusSelect.value = "custom";
          customGroup.style.display = "block";
          customStatusInput.value = currentStatus;
        } else {
          statusSelect.value = "on-duty";
          customGroup.style.display = "none";
          customStatusInput.value = "";
        }
        modal.classList.add("visible");
      }

      function closeEditModal() {
        modal.classList.remove("visible");
      }

      async function saveShiftUpdate() {
        const { expertId, date } = currentEditingInfo;
        if (!expertId || !date) return;

        const statusSelect = document.getElementById("shift-status-select");
        let newStatus = statusSelect.value;

        if (newStatus === "custom") {
          newStatus = document
            .getElementById("custom-shift-status")
            .value.trim();
          if (!newStatus) {
            alert("لطفاً برای وضعیت سفارشی یک مقدار وارد کنید.");
            return;
          }
        }

        const token = localStorage.getItem("jwt");
        if (!token) {
          alert("توکن احراز هویت یافت نشد. لطفاً دوباره وارد شوید.");
          return;
        }

        try {
          const response = await fetch("/php/update-shift.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${token}`,
            },
            body: JSON.stringify({
              expertId: expertId,
              date: date,
              status: newStatus,
            }),
          });

          const result = await response.json();
          if (!response.ok) {
            throw new Error(result.message || "خطا در ذخیره‌سازی تغییرات.");
          }

          alert(result.message);
          closeEditModal();
          initializePage();
        } catch (error) {
          console.error("Error updating shift:", error);
          alert(`ذخیره ناموفق بود: ${error.message}`);
        }
      }

      function getShiftStyle(shiftTime) {
        if (!shiftTime) {
          return "";
        }
        if (!shiftColorMap.has(shiftTime)) {
          shiftColorMap.set(shiftTime, colorPalette[nextColorIndex]);
          nextColorIndex = (nextColorIndex + 1) % colorPalette.length;
        }
        return `style="background-color: ${shiftColorMap.get(shiftTime)};"`;
      }

      function renderTable(expertsToRender, datesToRender) {
        const container = document.getElementById("schedule-container");
        const loader = document.getElementById("loader");

        if (
          !expertsToRender ||
          !datesToRender ||
          datesToRender.length === 0
        ) {
          container.innerHTML = "";
          loader.textContent = "هیچ داده‌ای مطابق با فیلترهای شما یافت نشد.";
          loader.style.display = "block";
          return;
        }

        loader.style.display = "none";

        const dailyOnDutyCounts = {};
        const dailyOffDutyCounts = {};
        const dailyLeaveCounts = {};
        const totalOnDutyByDate = {};
        const totalOffDutyByDate = {};
        const totalLeaveByDate = {};

        const uniqueShiftTimes = [
          ...new Set(
            allExperts.map((e) => e["shifts-time"]).filter(Boolean)
          ),
        ].sort();

        datesToRender.forEach((date) => {
          dailyOnDutyCounts[date] = {};
          dailyOffDutyCounts[date] = {};
          dailyLeaveCounts[date] = {};
          uniqueShiftTimes.forEach((shift) => {
            dailyOnDutyCounts[date][shift] = 0;
            dailyOffDutyCounts[date][shift] = 0;
            dailyLeaveCounts[date][shift] = 0;
          });

          totalOnDutyByDate[date] = 0;
          totalOffDutyByDate[date] = 0;
          totalLeaveByDate[date] = 0;

          const allExpertsForCounting = allExperts;

          allExpertsForCounting.forEach((expert) => {
            const status = expert.shifts[date] || "unknown";
            const shiftTime = expert["shifts-time"];

            if (shiftTime) {
                switch (status) {
                    case 'off':
                        dailyOffDutyCounts[date][shiftTime]++;
                        break;
                    case 'leave':
                        dailyLeaveCounts[date][shiftTime]++;
                        break;
                    case 'unknown':
                        break;
                    default:
                        dailyOnDutyCounts[date][shiftTime]++;
                        break;
                }
            }

            switch (status) {
                case 'off':
                    totalOffDutyByDate[date]++;
                    break;
                case 'leave':
                    totalLeaveByDate[date]++;
                    break;
                case 'unknown':
                    break;
                default:
                    totalOnDutyByDate[date]++;
                    break;
            }
          });
        });

        let tableHtml =
          `<table><thead><tr><th>نام کارشناس</th><th>ساعت شیفت</th><th>تایم استراحت</th>`;
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
              let statusClass = "";
              let statusText = status;

              if (status === "on-duty") {
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

        const totalColumns = datesToRender.length + 3;

        if (uniqueShiftTimes.length > 0) {
          tableHtml += `<tr class="summary-separator"><td colspan="${totalColumns}">خلاصه وضعیت به تفکیک شیفت</td></tr>`;
          uniqueShiftTimes.forEach((shiftTime) => {
            const summaryRowStyle = getShiftStyle(shiftTime);
            tableHtml += `<tr class="summary-row"><td ${summaryRowStyle}>حاضرین در شیفت ${shiftTime}</td><td>-</td><td>-</td>`;
            datesToRender.forEach((date) => {
              const count = dailyOnDutyCounts[date][shiftTime] || 0;
              tableHtml += `<td><span class="summary-count">${count}</span></td>`;
            });
            tableHtml += `</tr>`;

            tableHtml += `<tr class="summary-row"><td ${summaryRowStyle}>عدم حضور در شیفت ${shiftTime}</td><td>-</td><td>-</td>`;
            datesToRender.forEach((date) => {
              const count = dailyOffDutyCounts[date][shiftTime] || 0;
              tableHtml += `<td><span class="summary-count" style="color: #dc3545;">${count}</span></td>`;
            });
            tableHtml += `</tr>`;

            tableHtml += `<tr class="summary-row"><td ${summaryRowStyle}>مرخصی در شیفت ${shiftTime}</td><td>-</td><td>-</td>`;
            datesToRender.forEach((date) => {
              const count = dailyLeaveCounts[date][shiftTime] || 0;
              tableHtml += `<td><span class="summary-count" style="color: #ffc107; text-shadow: 1px 1px 1px #fff;">${count}</span></td>`;
            });
            tableHtml += `</tr>`;
          });
        }

        tableHtml += `<tr class="summary-separator"><td colspan="${totalColumns}">جمع‌بندی کل روزانه</td></tr>`;

        tableHtml += `<tr class="summary-row"><td style="background-color: #E8F5E9;">مجموع کل کارشناسان حاضر</td><td>-</td><td>-</td>`;
        datesToRender.forEach((date) => {
          tableHtml += `<td><span class="summary-count">${totalOnDutyByDate[date] || 0}</span></td>`;
        });
        tableHtml += `</tr>`;

        tableHtml += `<tr class="summary-row"><td style="background-color: #FFEBEE;">مجموع کل عدم حضور</td><td>-</td><td>-</td>`;
        datesToRender.forEach((date) => {
          tableHtml += `<td><span class="summary-count" style="color: #dc3545;">${totalOffDutyByDate[date] || 0}</span></td>`;
        });
        tableHtml += `</tr>`;

        tableHtml += `<tr class="summary-row"><td style="background-color: #FFF3E0;">مجموع کل مرخصی</td><td>-</td><td>-</td>`;
        datesToRender.forEach((date) => {
          tableHtml += `<td><span class="summary-count" style="color: #ffc107; text-shadow: 1px 1px 1px #fff;">${totalLeaveByDate[date] || 0}</span></td>`;
        });
        tableHtml += `</tr>`;


        tableHtml += "</tbody></table>";
        container.innerHTML = tableHtml;
      }

      // --- START: MODIFIED FUNCTION ---
      function populateFilterControls() {
        const expertSelect1 = document.getElementById("expertSelect1");
        const expertSelect2 = document.getElementById("expertSelect2");
        let optionsHtml = '<option value="none">-- هیچکدام --</option>';
        allExperts
          .sort((a, b) => a.name.localeCompare(b.name, "fa"))
          .forEach((expert) => {
            optionsHtml += `<option value="${expert.id}">${expert.name}</option>`;
          });
        expertSelect1.innerHTML = optionsHtml;
        expertSelect2.innerHTML = optionsHtml;
        expertSelect1.value = "none";
        expertSelect2.value = "none";

        const allDatesSet = new Set();
        allExperts.forEach((expert) => {
          Object.keys(expert.shifts).forEach((date) => allDatesSet.add(date));
        });
        allAvailableDates = Array.from(allDatesSet).sort();

        if (allAvailableDates.length > 0) {
            // Helper function to format a Date object into YYYY-MM-DD string
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            const today = new Date();
            const futureDate = new Date();
            futureDate.setDate(today.getDate() + 7);

            const startDateValue = formatDate(today);
            const endDateValue = formatDate(futureDate);

            document.getElementById("startDate").value = startDateValue;
            document.getElementById("endDate").value = endDateValue;
        }
      }
      // --- END: MODIFIED FUNCTION ---

      function applyFilters() {
        const startDate = document.getElementById("startDate").value;
        const endDate = document.getElementById("endDate").value;
        const expert1 = document.getElementById("expertSelect1").value;
        const expert2 = document.getElementById("expertSelect2").value;

        const selectedExpertIds = new Set();
        if (expert1 !== "none") selectedExpertIds.add(expert1);
        if (expert2 !== "none") selectedExpertIds.add(expert2);

        const filteredDates = allAvailableDates.filter(
          (date) => date >= startDate && date <= endDate
        );

        let filteredExperts = allExperts;
        if (selectedExpertIds.size > 0) {
          filteredExperts = allExperts.filter((expert) =>
            selectedExpertIds.has(String(expert.id))
          );
        }

        filteredExperts.sort((a, b) =>
          (a["shifts-time"] || "").localeCompare(b["shifts-time"] || "")
        );
        renderTable(
            filteredExperts,
            filteredDates
        );
      }
    </script>
  </body>
</html>
