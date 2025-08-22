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
      --text-color: #1a1a1a;
      --secondary-text-color: #555;
      --card-bg: #ffffff;
      --header-text: #ffffff;
      /* From original styles */
      --border-color: #e9e9e9;
      --shadow-light: rgba(0, 120, 80, 0.06);
      --shadow-medium: rgba(0, 120, 80, 0.12);
      --border-radius: 0.75rem;

      /* Status Colors */
      --status-active-bg: #e6f7f2;
      --status-active-text: #089863;
      --status-disrupted-bg: #fff6e5;
      --status-disrupted-text: #f59e0b;
      --status-inactive-bg: #fef2f2;
      --status-inactive-text: #ef4444;
      --status-unknown-bg: #f3f4f6;
      --status-unknown-text: #6b7280;
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
    }

    /* --- [START] UNCHANGED HEADER & FOOTER STYLES --- */
    header,
    footer {
      background: var(--primary-color);
      color: var(--header-text);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      z-index: 10;
      box-shadow: var(--shadow-sm);
      flex-shrink: 0;
    }

    header {
      min-height: var(--header-h)
    }

    footer {
      min-height: var(--footer-h);
      font-size: .85rem
    }

    header h1 {
      font-weight: 700;
      font-size: clamp(1rem, 2.2vw, 1.2rem);
      white-space: nowrap;
      max-width: 60vw;
      text-overflow: ellipsis;
      overflow: hidden;
    }

    #today-date,
    #user-info {
      position: static !important;
      transform: none !important;
      white-space: nowrap;
      opacity: .9;
      font-weight: 500;
      font-size: clamp(.9rem, 2vw, 1rem);
    }

    #today-date {
      inset-inline-start: 1.5rem;
    }

    #user-info {
      inset-inline-end: 1.5rem;
      cursor: pointer;
      padding: 0.5rem 0.8rem;
      border-radius: 0.5rem;
      transition: background-color 0.2s;
    }

    #user-info:hover {
      background-color: rgba(255, 255, 255, 0.15);
    }

    /* --- [END] UNCHANGED HEADER & FOOTER STYLES --- */


    /* --- Main Content Styles (Redesigned) --- */
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
      transition: all 0.2s ease-in-out;
    }

    #add-new-item-btn:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 174, 112, 0.25);
    }

    #item-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
      gap: 1.5rem;
    }

    .service-card {
      background-color: var(--card-bg);
      border-radius: var(--border-radius);
      box-shadow: 0 4px 15px var(--shadow-light);
      border: 1px solid var(--border-color);
      transition: all 0.2s ease-in-out;
      display: flex;
      flex-direction: column;
    }

    .service-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 25px var(--shadow-medium);
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
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
    }

    .card-status.فعال {
      background-color: var(--status-active-bg);
      color: var(--status-active-text);
    }

    .card-status.غیرفعال {
      background-color: var(--status-inactive-bg);
      color: var(--status-inactive-text);
    }

    .card-status.اختلال-در-عملکرد {
      background-color: var(--status-disrupted-bg);
      color: var(--status-disrupted-text);
    }

    .card-status.unknown-status {
      background-color: var(--status-unknown-bg);
      color: var(--status-unknown-text);
    }

    .card-body {
      padding: 1.25rem;
      color: var(--secondary-text-color);
      flex-grow: 1;
    }

    .card-description {
      line-height: 1.7;
    }

    .card-description p {
      margin-bottom: 0.5em;
    }

    .card-description ul {
      padding-right: 1.5rem;
      margin-top: 0.5em;
    }

    .card-actions {
      display: flex;
      justify-content: flex-end;
      gap: 0.75rem;
      padding: 1rem 1.25rem;
      border-top: 1px solid var(--border-color);
      background-color: #fafbfc;
      border-bottom-left-radius: var(--border-radius);
      border-bottom-right-radius: var(--border-radius);
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
      background-color: #e9f5ff;
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

    .modal-content input[type="text"],
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

    .modal-content input[type="text"]:focus,
    .modal-content select:focus,
    .description-editor:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(0, 174, 112, 0.15);
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

    .modal-content button {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 0.5rem;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 600;
      transition: all 0.2s;
    }

    #save-item-btn {
      background-color: var(--primary-color);
      color: white;
    }

    #save-item-btn:hover {
      background-color: var(--primary-dark);
    }

    #cancel-edit-btn {
      background-color: #f1f1f1;
      color: #555;
      border: 1px solid #ccc;
    }

    #cancel-edit-btn:hover {
      background-color: #e7e7e7;
    }

    .back-link {
      display: block;
      margin-top: 2rem;
      text-align: center;
      color: var(--primary-color);
      text-decoration: none;
      font-size: 1rem;
      font-weight: 500;
    }

    .back-link:hover {
      text-decoration: underline;
      color: var(--primary-dark);
    }

    @media (max-width: 900px) {
      #item-list {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 480px) {
      main {
        padding: 1.5rem 1rem;
      }

      .page-header {
        text-align: center;
        justify-content: center;
      }

      .page-title {
        font-size: 1.5rem;
      }

      /* Original responsive rule for header elements */
      #today-date,
      #user-info {
        display: none;
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
      <button id="add-new-item-btn">✨ افزودن سرویس جدید</button>
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

        <label for="name">نام سرویس:</label>
        <input type="text" id="name" name="name" required />

        <label for="status">وضعیت:</label>
        <select id="status" name="status">
          <option value="فعال">🟢 فعال</option>
          <option value="غیرفعال">🔴 غیرفعال</option>
          <option value="اختلال در عملکرد">🟠 اختلال در عملکرد</option>
        </select>

        <label for="description-editor">توضیحات:</label>
        <div class="editor-toolbar">
          <button type="button" data-command="bold" title="ضخیم"><b>B</b></button>
        </div>
        <div id="description-editor" class="description-editor" contenteditable="true"></div>

        <div class="button-group">
          <button type="button" id="cancel-edit-btn">لغو</button>
          <button type="submit" id="save-item-btn">ذخیره</button>
        </div>
      </form>
    </div>
  </div>

  <div id="footer-placeholder"></div>

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

    async function saveDataToServer() {
      try {
        const response = await fetch("/data/save-service-status.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify(jsonData, null, 2),
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
          throw new Error(result.message || "خطای ناشناخته در سرور");
        }
        console.log("Data saved successfully:", result.message);
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
      currentItemIndex = -1;
      if (descriptionEditor) descriptionEditor.innerHTML = "";
    }

    closeButton.onclick = closeModal;
    window.onclick = (e) => {
      if (e.target == itemModal) closeModal();
    };
    cancelEditBtn.onclick = closeModal;

    function renderItems() {
      itemListDiv.innerHTML = "";
      if (jsonData.length === 0) {
        itemListDiv.innerHTML = '<p style="text-align:center; grid-column: 1 / -1; margin-top:50px;font-size:1.2rem;color:#555;">سرویسی برای نمایش وجود ندارد.</p>';
        return;
      }
      jsonData.forEach((item, index) => {
        const statusClass = item.status ? item.status.replace(/\s/g, "-") : "unknown-status";
        let statusIcon = '❔';
        if (item.status === 'فعال') statusIcon = '🟢';
        if (item.status === 'غیرفعال') statusIcon = '🔴';
        if (item.status === 'اختلال در عملکرد') statusIcon = '🟠';

        const card = document.createElement("div");
        card.className = "service-card";
        card.innerHTML = `
            <div class="card-header">
                <h3 class="card-title">${item.name}</h3>
                <span class="card-status ${statusClass}">${statusIcon} ${item.status || 'نامشخص'}</span>
            </div>
            <div class="card-body">
                <div class="card-description">${item.description || "<em>بدون توضیحات</em>"}</div>
            </div>
            <div class="card-actions">
                <button class="edit-btn" data-index="${index}">✏️ ویرایش</button>
                <button class="delete-btn" data-index="${index}">🗑️ حذف</button>
            </div>
            `;
        itemListDiv.appendChild(card);
      });

      document.querySelectorAll(".edit-btn").forEach(btn =>
        btn.addEventListener("click", (e) => editItem(+e.target.dataset.index))
      );
      document.querySelectorAll(".delete-btn").forEach(btn =>
        btn.addEventListener("click", (e) => deleteItem(+e.target.dataset.index))
      );
    }

    function editItem(index) {
      currentItemIndex = index;
      const item = jsonData[index];
      document.getElementById("itemId").value = index;
      document.getElementById("name").value = item.name;
      document.getElementById("status").value = item.status;
      descriptionEditor.innerHTML = item.description || "";
      modalTitle.textContent = "ویرایش سرویس";
      openModal();
    }

    addNewItemBtn.addEventListener("click", () => {
      currentItemIndex = -1;
      itemForm.reset();
      descriptionEditor.innerHTML = "";
      modalTitle.textContent = "افزودن سرویس جدید";
      openModal();
    });

    // --- [START] FIX FOR <br> TAG ---
    itemForm.addEventListener("submit", (e) => {
      e.preventDefault();

      // Clean up the description before saving
      let descriptionValue = descriptionEditor.innerHTML.trim();
      if (!descriptionEditor.textContent.trim() || descriptionValue === '<br>') {
        descriptionValue = '';
      }

      const newItem = {
        name: document.getElementById("name").value,
        status: document.getElementById("status").value,
        description: descriptionValue, // Use the cleaned value
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
    // --- [END] FIX FOR <br> TAG ---

    function deleteItem(index) {
      if (confirm("آیا از حذف این سرویس اطمینان دارید؟")) {
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
        const res = await fetch(`/data/service-status.json?v=${new Date().getTime()}`);
        if (res.ok) {
          jsonData = await res.json();
        } else if (res.status === 404) {
          jsonData = [];
        } else {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
      } catch (err) {
        console.error(err);
        alert("خطا در بارگذاری اطلاعات اولیه. ممکن است فایل داده وجود نداشته باشد.");
        jsonData = [];
      } finally {
        renderItems();
      }
    }
    document.addEventListener("DOMContentLoaded", loadInitialJson);
  </script>
  <script src="/js/header.js"></script>
</body>

</html>
