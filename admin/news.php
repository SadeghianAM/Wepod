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
      --orange-bg: #fff6e5;
      --orange-border: #f59e0b;
      --orange-text: #d97706;
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

    /* --- [START] UNCHANGED HEADER & FOOTER STYLES --- */
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

    /* --- [END] UNCHANGED HEADER & FOOTER STYLES --- */

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

    .announcement-card.orange {
      border-top-color: var(--orange-border);
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

    .announcement-card.orange .card-icon {
      color: var(--orange-text);
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

    .modal-content input:focus,
    .modal-content select:focus,
    .description-editor:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(0, 174, 112, 0.15);
      outline: none;
    }

    .date-time-group {
      display: flex;
      gap: 1rem;
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

    /* --- [START] REVISED MODAL BUTTON STYLES --- */
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

    /* --- [END] REVISED MODAL BUTTON STYLES --- */

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
          <option value="orange">🟠 نارنجی (مهم)</option>
          <option value="red">🔴 قرمز (بسیار مهم)</option>
        </select>

        <div class="date-time-group">
          <div>
            <label for="startDate">تاریخ شروع (اختیاری):</label>
            <input type="date" id="startDate" name="startDate" />
          </div>
          <div>
            <label for="startTime">ساعت شروع (اختیاری):</label>
            <input type="time" id="startTime" name="startTime" />
          </div>
        </div>

        <div class="date-time-group">
          <div>
            <label for="endDate">تاریخ پایان (اختیاری):</label>
            <input type="date" id="endDate" name="endDate" />
          </div>
          <div>
            <label for="endTime">ساعت پایان (اختیاری):</label>
            <input type="time" id="endTime" name="endTime" />
          </div>
        </div>

        <div class="button-group">
          <button type="button" id="cancel-edit-btn">✖️ لغو</button>
          <button type="submit" id="save-item-btn">💾 ذخیره</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // JavaScript logic is identical to the previous version
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
      if (descriptionEditor) descriptionEditor.innerHTML = "";
    }

    const colorToIconMap = {
      green: '📢',
      yellow: '⚠️',
      orange: '⚠️',
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

    function renderItems() {
      itemListDiv.innerHTML = "";
      if (jsonData.length === 0) {
        itemListDiv.innerHTML = '<p style="text-align: center; margin-top: 50px; font-size: 1.2rem; color: #555;">اطلاعیه‌ای برای نمایش وجود ندارد.</p>';
        return;
      }
      jsonData.forEach((item, index) => {
        const card = document.createElement("div");
        card.className = `announcement-card ${item.color}`;

        const start = formatDateTime(item.startDate, item.startTime);
        const end = formatDateTime(item.endDate, item.endTime);
        let timeInfo = 'همیشه فعال';
        if (start && end) timeInfo = `از ${start} تا ${end}`;
        else if (start) timeInfo = `از ${start}`;
        else if (end) timeInfo = `تا ${end}`;

        card.innerHTML = `
                    <div class="card-header">
                        <span class="card-icon">${colorToIconMap[item.color] || '🔹'}</span>
                        <h3 class="card-title">${item.title}</h3>
                    </div>
                    <div class="card-body">${item.description}</div>
                    <div class="card-footer">
                        <span>🗓️ ${timeInfo}</span>
                        <div class="card-actions">
                            <button class="edit-btn" data-index="${index}">✏️ ویرایش</button>
                            <button class="delete-btn" data-index="${index}">🗑️ حذف</button>
                        </div>
                    </div>
                `;
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
      document.getElementById("startTime").value = item.startTime;
      document.getElementById("endDate").value = item.endDate;
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
        document.execCommand(button.dataset.command, false, button.dataset.value || null);
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
    document.addEventListener("DOMContentLoaded", loadInitialJson);
  </script>
  <div id="footer-placeholder"></div>
  <script src="/js/header.js"></script>
</body>

</html>
