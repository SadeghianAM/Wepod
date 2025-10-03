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
      --warning-light: #fff8e7;
      --danger-light: #fbebec;
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
      flex-grow: 1;
      padding: 2.5rem 2rem;
      max-width: 1500px;
      width: 100%;
      margin: 0 auto;
    }

    footer {
      background: var(--primary-color);
      color: var(--header-text);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      height: 60px;
      font-size: 0.85rem;
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
      font-size: clamp(1.5rem, 3vw, 2rem);
      font-weight: 800;
      color: var(--primary-dark);
    }

    .page-subtitle {
      font-size: clamp(.95rem, 2.2vw, 1rem);
      color: var(--secondary-text);
      margin-top: 0.25rem;
    }

    .icon {
      width: 1.1em;
      height: 1.1em;
      stroke-width: 2.2;
      vertical-align: -0.15em;
    }

    .btn {
      position: relative;
      padding: .8em 1.5em;
      font-size: .95rem;
      font-weight: 600;
      border: 1.5px solid transparent;
      border-radius: var(--radius);
      cursor: pointer;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.6em;
    }

    .btn:hover:not(:disabled) {
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

    .btn-outline-danger {
      background: transparent;
      color: var(--danger-color);
      border-color: var(--danger-color);
    }

    .btn-outline-danger:hover {
      background: var(--danger-color);
      color: white;
    }

    .btn-outline-info {
      background: transparent;
      color: var(--info-color);
      border-color: var(--info-color);
    }

    .btn-outline-info:hover {
      background: var(--info-color);
      color: white;
    }

    #item-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 1.5rem;
    }

    .announcement-card {
      background-color: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-color);
      border-top: 4px solid;
      transition: all 0.2s;
      display: flex;
      flex-direction: column;
    }

    .announcement-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-md);
    }

    .announcement-card.green {
      border-top-color: var(--success-color);
    }

    .announcement-card.yellow {
      border-top-color: var(--warning-color);
    }

    .announcement-card.red {
      border-top-color: var(--danger-color);
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
      color: var(--success-color);
    }

    .announcement-card.yellow .card-icon {
      color: var(--warning-color);
    }

    .announcement-card.red .card-icon {
      color: var(--danger-color);
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
      background-color: var(--bg-color);
      font-size: 0.85rem;
      color: var(--secondary-text);
      border-bottom-left-radius: var(--radius);
      border-bottom-right-radius: var(--radius);
    }

    .card-actions {
      display: flex;
      gap: 0.5rem;
    }

    .card-actions .btn {
      padding: .5em 1em;
      font-size: .9rem;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(4px);
      align-items: center;
      justify-content: center;
    }

    .modal.visible {
      display: flex;
    }

    .modal-content {
      background-color: var(--card-bg);
      margin: auto;
      padding: 2rem;
      border-radius: var(--radius);
      width: 90%;
      max-width: 700px;
      box-shadow: var(--shadow-md);
      position: relative;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid var(--border-color);
    }

    .modal-header h2 {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary-dark);
    }

    .modal-content label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: var(--secondary-text);
      font-size: .9rem;
    }

    .modal-content input,
    .modal-content select,
    .description-editor {
      width: 100%;
      padding: .8em 1.2em;
      margin-bottom: 1.25rem;
      border: 1.5px solid var(--border-color);
      border-radius: var(--radius);
      font-size: 1rem;
      background-color: var(--card-bg);
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .modal-content input:focus,
    .modal-content select:focus,
    .description-editor:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(0, 174, 112, 0.15);
      outline: none;
    }

    .date-time-group {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
      align-items: end;
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
      background-color: var(--bg-color);
      border-radius: .5rem;
      border: 1px solid var(--border-color);
    }

    .editor-toolbar button {
      background-color: var(--card-bg);
      border: 1px solid var(--border-color);
      width: 36px;
      height: 36px;
      border-radius: 4px;
      cursor: pointer;
      display: grid;
      place-items: center;
      transition: all 0.2s;
      padding: 0;
      margin: 0;
      color: var(--secondary-text);
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

    .jdp-popover,
    .tp-popover {
      position: absolute;
      background: #fff;
      border: 1px solid var(--border-color);
      border-radius: var(--radius);
      box-shadow: var(--shadow-md);
      padding: .75rem;
      z-index: 10000;
    }

    .jdp-popover {
      width: 280px;
    }

    .tp-popover {
      min-width: 320px;
      max-width: 360px;
    }

    .jdp-header,
    .tp-title {
      font-weight: 800;
      color: var(--primary-dark);
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

    .toast-info {
      background-color: var(--info-color);
    }

    .toast-confirm {
      background-color: var(--card-bg);
      color: var(--text-color);
      border: 1px solid var(--border-color);
    }

    .toast-confirm .toast-message {
      margin-bottom: 1rem;
    }

    .toast-confirm .toast-buttons {
      display: flex;
      justify-content: center;
      gap: 1rem;
    }

    .toast-confirm .btn {
      font-size: 0.85rem;
      padding: 0.5em 1em;
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
      <button id="add-new-item-btn" class="btn btn-primary">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14" />
          <path d="M12 5v14" />
        </svg>
        <span>افزودن اطلاعیه</span>
      </button>
    </div>

    <div id="item-list"></div>
    <a href="/admin/index.php" class="back-link">بازگشت به پنل مدیریت</a>
  </main>

  <div id="itemModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="modalTitle"></h2>
        <button class="btn-icon" id="close-modal-btn" title="بستن">
          <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>
      <form id="itemForm">
        <input type="hidden" id="itemId" />
        <label for="title">عنوان:</label>
        <input type="text" id="title" name="title" required />
        <label for="description-editor">توضیحات:</label>
        <div class="editor-toolbar">
          <button type="button" data-command="bold" title="ضخیم">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z" />
              <path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z" />
            </svg>
          </button>
        </div>
        <div id="description-editor" class="description-editor" contenteditable="true"></div>
        <label for="color">رنگ:</label>
        <select id="color" name="color">
          <option value="green">سبز (اطلاع‌رسانی)</option>
          <option value="yellow">زرد (هشدار)</option>
          <option value="red">قرمز (بسیار مهم)</option>
        </select>
        <div class="date-time-group">
          <div>
            <label for="startDateDisplay">تاریخ شروع (اختیاری):</label>
            <input type="text" id="startDateDisplay" placeholder="برای انتخاب کلیک کنید" autocomplete="off" readonly />
            <input type="hidden" id="startDate" name="startDate" />
          </div>
          <div>
            <label for="startTime">ساعت شروع (اختیاری):</label>
            <input type="text" id="startTime" name="startTime" placeholder="--:--" autocomplete="off" />
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
            <input type="text" id="endTime" name="endTime" placeholder="--:--" autocomplete="off" />
          </div>
        </div>
        <div class="button-group">
          <button type="button" id="cancel-edit-btn" class="btn btn-secondary">لغو</button>
          <button type="submit" id="save-item-btn" class="btn btn-primary"></button>
        </div>
      </form>
    </div>
  </div>
  <div id="toast-container"></div>
  <div id="footer-placeholder"></div>
  <script src="/js/header.js"></script>
  <script>
    const API_SAVE_URL = "/data/save-news-alerts.php";
    const API_LOAD_URL = `/data/news-alerts.json?v=${new Date().getTime()}`;
    let jsonData = [];
    let currentItemIndex = -1;

    const itemListDiv = document.getElementById("item-list");
    const itemModal = document.getElementById("itemModal");
    const closeButton = document.getElementById("close-modal-btn");
    const itemForm = document.getElementById("itemForm");
    const addNewItemBtn = document.getElementById("add-new-item-btn");
    const modalTitle = document.getElementById("modalTitle");
    const descriptionEditor = document.getElementById("description-editor");
    const editorToolbar = document.querySelector(".editor-toolbar");
    const cancelEditBtn = document.getElementById("cancel-edit-btn");

    const ICONS = {
      add: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>`,
      save: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>`,
      edit: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>`,
      delete: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>`,
      calendar: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>`,
      info: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`,
      warning: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>`,
      danger: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`
    };

    function showToast(message, type = 'success', duration = 4000) {
      const container = document.getElementById('toast-container');
      const toast = document.createElement('div');
      toast.className = `toast toast-${type}`;
      toast.innerHTML = `<span>${message}</span>`;
      container.appendChild(toast);
      setTimeout(() => toast.classList.add('show'), 10);
      setTimeout(() => {
        toast.classList.remove('show');
        toast.addEventListener('transitionend', () => toast.remove());
      }, duration);
    }

    function showConfirmation(message, onConfirm) {
      const toastContainer = document.getElementById('toast-container');
      const toast = document.createElement('div');
      toast.className = 'toast toast-confirm';
      toast.innerHTML = `<div class="toast-message">${message}</div>
            <div class="toast-buttons">
                <button class="btn btn-danger" id="confirmAction">بله، حذف کن</button>
                <button class="btn btn-secondary" id="cancelAction">لغو</button>
            </div>`;
      const removeToast = () => {
        toast.classList.remove('show');
        toast.addEventListener('transitionend', () => toast.remove());
      };
      toast.querySelector('#confirmAction').onclick = () => {
        onConfirm();
        removeToast();
      };
      toast.querySelector('#cancelAction').onclick = removeToast;
      toastContainer.appendChild(toast);
      setTimeout(() => toast.classList.add('show'), 10);
    }

    async function saveDataToServer() {
      try {
        const response = await fetch(API_SAVE_URL, {
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
        showToast("خطا در ذخیره اطلاعات: " + error.message, 'error');
      }
    }

    const colorToIconMap = {
      green: ICONS.info,
      yellow: ICONS.warning,
      red: ICONS.danger
    };

    function formatDateTime(dateStr, timeStr) {
      if (!dateStr) return '';
      try {
        const date = new Date(dateStr);
        let formatted = new Intl.DateTimeFormat('fa-IR', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        }).format(date);
        if (timeStr) formatted += ` - ساعت ${timeStr}`;
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
                <span class="card-icon">${colorToIconMap[item.color] || ''}</span>
                <h3 class="card-title">${item.title}</h3>
            </div>
            <div class="card-body">${item.description}</div>
            <div class="card-footer">
                <span>${ICONS.calendar} ${timeInfo}</span>
                <div class="card-actions">
                    <button class="btn btn-outline-info edit-btn" data-index="${index}">${ICONS.edit} <span>ویرایش</span></button>
                    <button class="btn btn-outline-danger delete-btn" data-index="${index}">${ICONS.delete} <span>حذف</span></button>
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
      document.getElementById("startDateDisplay").value = item.startDate ? new Date(item.startDate).toLocaleDateString('fa-IR-u-nu-latn').replace(/\//g, '/') : '';
      document.getElementById("startTime").value = item.startTime;
      document.getElementById("endDate").value = item.endDate;
      document.getElementById("endDateDisplay").value = item.endDate ? new Date(item.endDate).toLocaleDateString('fa-IR-u-nu-latn').replace(/\//g, '/') : '';
      document.getElementById("endTime").value = item.endTime;
      modalTitle.textContent = "ویرایش اطلاعیه";
      document.getElementById('save-item-btn').innerHTML = `${ICONS.save} <span>ذخیره تغییرات</span>`;
      itemModal.classList.add('visible');
    }

    addNewItemBtn.addEventListener("click", () => {
      currentItemIndex = -1;
      itemForm.reset();
      descriptionEditor.innerHTML = "";
      modalTitle.textContent = "افزودن اطلاعیه جدید";
      document.getElementById('save-item-btn').innerHTML = `${ICONS.add} <span>افزودن</span>`;
      itemModal.classList.add('visible');
    });

    itemForm.addEventListener("submit", (e) => {
      e.preventDefault();
      let descriptionValue = descriptionEditor.innerHTML.trim();
      if (!descriptionEditor.textContent.trim() || descriptionValue === '<br>') descriptionValue = '';

      const newItem = {
        title: document.getElementById("title").value,
        description: descriptionValue,
        color: document.getElementById("color").value,
        startDate: document.getElementById("startDate").value,
        startTime: document.getElementById("startTime").value,
        endDate: document.getElementById("endDate").value,
        endTime: document.getElementById("endTime").value,
      };

      if (currentItemIndex === -1) jsonData.push(newItem);
      else jsonData[currentItemIndex] = newItem;

      renderItems();
      itemModal.classList.remove('visible');
      saveDataToServer();
      showToast('اطلاعیه با موفقیت ذخیره شد.', 'success');
    });

    function deleteItem(index) {
      showConfirmation("آیا از حذف این اطلاعیه اطمینان دارید؟", () => {
        jsonData.splice(index, 1);
        renderItems();
        saveDataToServer();
        showToast('اطلاعیه با موفقیت حذف شد.', 'info');
      });
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
        const response = await fetch(API_LOAD_URL);
        if (response.ok) jsonData = await response.json();
        else if (response.status === 404) jsonData = [];
        else throw new Error(`HTTP error! status: ${response.status}`);
      } catch (error) {
        showToast("خطا در بارگذاری اولیه فایل JSON.", 'error');
        jsonData = [];
      } finally {
        renderItems();
      }
    }

    document.querySelectorAll('.close-button, #cancel-edit-btn, #close-modal-btn').forEach(el => el.addEventListener('click', () => itemModal.classList.remove('visible')));
    itemModal.addEventListener('click', e => {
      if (e.target === itemModal) itemModal.classList.remove('visible');
    });

    loadInitialJson();
    new JalaliDatePicker("startDateDisplay", "startDate");
    new JalaliDatePicker("endDateDisplay", "endDate");
    new TimePicker("startTime");
    new TimePicker("endTime");
  </script>
</body>

</html> 
