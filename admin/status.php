<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>مدیریت وضعیت سرویس‌ها</title>
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

    a {
      text-decoration: none;
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
      transition: all 0.2s ease;
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
      grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
      gap: 1.5rem;
    }

    .service-card {
      background-color: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-color);
      border-top: 4px solid;
      transition: all 0.2s;
      display: flex;
      flex-direction: column;
    }

    .service-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-md);
    }

    .service-card.active {
      border-top-color: var(--success-color);
    }

    .service-card.disrupted {
      border-top-color: var(--warning-color);
    }

    .service-card.inactive {
      border-top-color: var(--danger-color);
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.25rem;
      border-bottom: 1px solid var(--border-color);
    }

    .card-title {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--text-color);
    }

    .card-status {
      padding: .4em .9em;
      border-radius: 20px;
      font-size: .85rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: .5em;
    }

    .card-status .icon {
      width: 1.2em;
      height: 1.2em;
    }

    .card-status.active {
      background-color: var(--success-light);
      color: var(--success-color);
    }

    .card-status.inactive {
      background-color: var(--danger-light);
      color: var(--danger-color);
    }

    .card-status.disrupted {
      background-color: var(--warning-light);
      color: #a17400;
    }

    .card-status.unknown {
      background-color: var(--border-color);
      color: var(--secondary-text);
    }

    .card-body {
      padding: 1.25rem;
      line-height: 1.7;
      flex-grow: 1;
      color: var(--secondary-text);
    }

    .card-body p,
    .card-body ul {
      margin: 0;
    }

    .card-body ul {
      padding-right: 1.25rem;
      margin-top: 0.5em;
    }

    .card-actions {
      display: flex;
      justify-content: flex-end;
      gap: 0.75rem;
      padding: 1rem 1.25rem;
      border-top: 1px solid var(--border-color);
      background-color: var(--bg-color);
      border-bottom-left-radius: var(--radius);
      border-bottom-right-radius: var(--radius);
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
      max-width: 600px;
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

    #modalTitle {
      font-size: 1.5rem;
      font-weight: 700;
    }

    .modal-content label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: var(--secondary-text);
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
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .modal-content input:focus,
    .modal-content select:focus,
    .description-editor:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(0, 174, 112, 0.15);
      outline: none;
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
      text-decoration: none;
    }

    .back-link:hover {
      text-decoration: underline;
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

    @media (max-width: 900px) {
      #item-list {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <div id="header-placeholder"></div>
  <main>
    <div class="page-header">
      <div>
        <h1 class="page-title">مدیریت وضعیت سرویس‌ها</h1>
        <p class="page-subtitle">سرویس‌های سامانه را اضافه، ویرایش یا حذف کنید.</p>
      </div>
      <button id="add-new-item-btn" class="btn btn-primary">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14" />
          <path d="M12 5v14" />
        </svg>
        <span>افزودن سرویس جدید</span>
      </button>
    </div>

    <div id="item-list"></div>
    <a href="/admin/index.php" class="back-link">بازگشت به پنل مدیریت</a>
  </main>

  <div id="itemModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="modalTitle"></h2>
        <button class="btn-icon close-button" title="بستن">
          <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>
      <form id="itemForm">
        <input type="hidden" id="itemId" />
        <label for="name">نام سرویس:</label>
        <input type="text" id="name" name="name" required />
        <label for="status">وضعیت:</label>
        <select id="status" name="status">
          <option value="فعال">فعال</option>
          <option value="غیرفعال">غیرفعال</option>
          <option value="اختلال در عملکرد">اختلال در عملکرد</option>
        </select>
        <label for="description-editor">توضیحات:</label>
        <div class="editor-toolbar">
          <button type="button" data-command="bold" title="ضخیم">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:1.2rem; height:1.2rem;">
              <path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z" />
              <path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z" />
            </svg>
          </button>
        </div>
        <div id="description-editor" class="description-editor" contenteditable="true"></div>
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
    let jsonData = [];
    let currentItemIndex = -1;

    const itemListDiv = document.getElementById("item-list");
    const itemModal = document.getElementById("itemModal");
    const closeButton = document.querySelector(".close-button");
    const itemForm = document.getElementById("itemForm");
    const cancelEditBtn = document.getElementById("cancel-edit-btn");
    const addNewItemBtn = document.getElementById("add-new-item-btn");
    const modalTitle = document.getElementById("modalTitle");
    const descriptionEditor = document.getElementById("description-editor");
    const editorToolbar = document.querySelector(".editor-toolbar");

    const ICONS = {
      add: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>`,
      save: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>`,
      edit: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>`,
      delete: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>`,
      active: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
      inactive: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
      disrupted: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>`
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
        const response = await fetch("/data/save-service-status.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify(jsonData),
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || "خطای ناشناخته در سرور");
        console.log("Data saved successfully:", result.message);
      } catch (error) {
        console.error("Error saving data:", error);
        showToast("خطا در ذخیره اطلاعات: " + error.message, 'error');
      }
    }

    function renderItems() {
      itemListDiv.innerHTML = "";
      if (jsonData.length === 0) {
        itemListDiv.innerHTML = '<p style="text-align:center; grid-column: 1 / -1; margin-top:50px;font-size:1.2rem;color:var(--secondary-text);">سرویسی برای نمایش وجود ندارد.</p>';
        return;
      }
      const statusMap = {
        'فعال': {
          class: 'active',
          icon: ICONS.active
        },
        'غیرفعال': {
          class: 'inactive',
          icon: ICONS.inactive
        },
        'اختلال در عملکرد': {
          class: 'disrupted',
          icon: ICONS.disrupted
        }
      };
      jsonData.forEach((item, index) => {
        const card = document.createElement("div");
        const statusInfo = statusMap[item.status] || {
          class: 'unknown',
          icon: ''
        };
        card.className = `service-card ${statusInfo.class}`;
        card.innerHTML = `
            <div class="card-header">
                <h3 class="card-title">${item.name}</h3>
                <span class="card-status ${statusInfo.class}">${statusInfo.icon} <span>${item.status || 'نامشخص'}</span></span>
            </div>
            <div class="card-body"><div class="card-description">${item.description || "<em>بدون توضیحات</em>"}</div></div>
            <div class="card-actions">
                <button class="btn btn-outline-info edit-btn" data-index="${index}">${ICONS.edit} <span>ویرایش</span></button>
                <button class="btn btn-outline-danger delete-btn" data-index="${index}">${ICONS.delete} <span>حذف</span></button>
            </div>`;
        itemListDiv.appendChild(card);
      });
      document.querySelectorAll(".edit-btn").forEach(btn => btn.addEventListener("click", (e) => editItem(+e.currentTarget.dataset.index)));
      document.querySelectorAll(".delete-btn").forEach(btn => btn.addEventListener("click", (e) => deleteItem(+e.currentTarget.dataset.index)));
    }

    function editItem(index) {
      currentItemIndex = index;
      const item = jsonData[index];
      document.getElementById("itemId").value = index;
      document.getElementById("name").value = item.name;
      document.getElementById("status").value = item.status;
      descriptionEditor.innerHTML = item.description || "";
      modalTitle.textContent = "ویرایش سرویس";
      document.getElementById('save-item-btn').innerHTML = `${ICONS.save} <span>ذخیره تغییرات</span>`;
      itemModal.classList.add('visible');
    }

    addNewItemBtn.addEventListener("click", () => {
      currentItemIndex = -1;
      itemForm.reset();
      descriptionEditor.innerHTML = "";
      modalTitle.textContent = "افزودن سرویس جدید";
      document.getElementById('save-item-btn').innerHTML = `${ICONS.add} <span>افزودن</span>`;
      itemModal.classList.add('visible');
    });

    itemForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const nameValue = document.getElementById("name").value.trim();
      if (!nameValue) {
        showToast("نام سرویس نمی‌تواند خالی باشد.", "error");
        return;
      }
      let descriptionValue = descriptionEditor.innerHTML.trim();
      if (!descriptionEditor.textContent.trim() || descriptionValue === '<br>') descriptionValue = '';
      const newItem = {
        name: nameValue,
        status: document.getElementById("status").value,
        description: descriptionValue,
      };
      if (currentItemIndex === -1) jsonData.push(newItem);
      else jsonData[currentItemIndex] = newItem;
      renderItems();
      itemModal.classList.remove('visible');
      saveDataToServer();
      showToast('اطلاعیه با موفقیت ذخیره شد.', 'success');
    });

    function deleteItem(index) {
      showConfirmation("آیا از حذف این سرویس اطمینان دارید؟", () => {
        jsonData.splice(index, 1);
        renderItems();
        saveDataToServer();
        showToast('سرویس با موفقیت حذف شد.', 'info');
      });
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
        const res = await fetch(`/data/service-status.json?v=${new Date().getTime()}`);
        if (res.ok) jsonData = await res.json();
        else if (res.status === 404) jsonData = [];
        else throw new Error(`HTTP error! status: ${res.status}`);
      } catch (err) {
        console.error(err);
        showToast("خطا در بارگذاری اطلاعات اولیه.", "error");
        jsonData = [];
      } finally {
        renderItems();
      }
    }

    document.querySelectorAll('.close-button, #cancel-edit-btn').forEach(el => el.addEventListener('click', () => itemModal.classList.remove('visible')));
    itemModal.addEventListener('click', e => {
      if (e.target === itemModal) itemModal.classList.remove('visible');
    });

    loadInitialJson();
    // JalaliDatePicker and TimePicker are not used on this page, so initializers are removed.
  </script>
</body>

</html>
