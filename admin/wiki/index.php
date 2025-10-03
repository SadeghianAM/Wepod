<?php

require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>مدیریت پیام‌های آماده</title>
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
      --blue-color: #007bff;
      --blue-dark: #0056b3;
      --shadow-sm: 0 2px 6px rgba(0, 120, 80, .06);
      --footer-h: 60px;
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
      min-height: var(--footer-h);
      font-size: .85rem
    }

    main {
      flex-grow: 1;
      padding: 2.5rem 2rem;
      max-width: 1400px;
      /* Max width increased for table */
      width: 100%;
      margin: 0 auto;
    }

    .page-header {
      margin-bottom: 2rem;
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
      margin-bottom: 2rem;
    }

    .action-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1.5rem;
      flex-wrap: wrap;
      margin-bottom: 2rem;
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

    .search-container {
      flex-grow: 1;
      min-width: 300px;
    }

    #search-input {
      width: 100%;
      padding: 0.75rem 1.25rem;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      font-size: 1rem;
      background-color: var(--card-bg);
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    #search-input:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(0, 174, 112, 0.15);
      outline: none;
    }

    /* --- [START] NEW TABLE STYLES --- */
    .table-container {
      background-color: var(--card-bg);
      border-radius: var(--border-radius);
      box-shadow: 0 4px 15px var(--shadow-light);
      border: 1px solid var(--border-color);
      overflow-x: auto;
      /* For responsiveness */
    }

    #item-table {
      width: 100%;
      border-collapse: collapse;
      text-align: right;
    }

    #item-table thead {
      background-color: #f9fafb;
    }

    #item-table th,
    #item-table td {
      padding: 1rem 1.25rem;
      vertical-align: middle;
    }

    #item-table th {
      font-weight: 600;
      color: var(--secondary-text-color);
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid var(--border-color);
    }

    #item-table tbody tr {
      border-bottom: 1px solid var(--border-color);
      transition: background-color 0.2s;
    }

    #item-table tbody tr:last-child {
      border-bottom: none;
    }

    #item-table tbody tr:hover {
      background-color: var(--primary-light);
    }

    .id-cell {
      font-weight: 600;
      color: var(--primary-dark);
      width: 5%;
    }

    .title-cell {
      font-weight: 600;
      color: var(--text-color);
      width: 20%;
    }

    .categories-cell {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      width: 25%;
    }

    .category-pill {
      background-color: #f3f4f6;
      color: #4b5563;
      padding: 0.2rem 0.6rem;
      border-radius: 999px;
      font-size: 0.8rem;
      font-weight: 500;
      white-space: nowrap;
    }

    .description-cell {
      max-width: 300px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      cursor: help;
    }

    .actions-cell {
      display: flex;
      gap: 0.5rem;
      justify-content: flex-start;
      width: 15%;
    }

    .actions-cell button {
      background: none;
      border: none;
      padding: 0.5rem;
      border-radius: 999px;
      width: 36px;
      height: 36px;
      display: grid;
      place-items: center;
      cursor: pointer;
      font-size: 1rem;
      color: var(--secondary-text-color);
      transition: all 0.2s;
    }

    .actions-cell .copy-btn {
      color: var(--primary-dark);
    }

    .actions-cell .edit-btn:hover {
      background-color: #e9f5ff;
      color: #007bff;
    }

    .actions-cell .delete-btn:hover {
      background-color: #fef2f2;
      color: #dc3545;
    }

    .actions-cell .copy-btn:hover {
      background-color: var(--primary-light);
    }

    /* --- [END] NEW TABLE STYLES --- */


    /* --- Modal Styles (Unchanged) --- */
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
    .modal-content input[type="number"],
    .modal-content textarea {
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
    .modal-content input[type="number"]:focus,
    .modal-content textarea:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(0, 174, 112, 0.15);
      outline: none;
    }

    .modal-content textarea {
      resize: vertical;
      min-height: 120px;
      line-height: 1.7;
    }

    #categories-checkbox-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 0.75rem;
      margin-bottom: 1.25rem;
      border: 1px solid var(--border-color);
      padding: 1rem;
      border-radius: 0.5rem;
    }

    #categories-checkbox-container div {
      display: flex;
      align-items: center;
    }

    #categories-checkbox-container input[type="checkbox"] {
      margin-left: 8px;
      cursor: pointer;
    }

    #categories-checkbox-container label {
      margin-bottom: 0 !important;
      font-weight: 500 !important;
      cursor: pointer;
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
  </style>
</head>

<body>
  <div id="header-placeholder"></div>
  <main>
    <div class="page-header">
      <h1 class="page-title">مدیریت پیام‌های آماده</h1>
      <p class="page-subtitle">پیام‌ها و اسکریپت‌های پاسخگویی را از اینجا جستجو، ویرایش و مدیریت کنید.</p>
    </div>

    <div class="action-bar">
      <button id="add-new-item-btn">✨ افزودن پیام جدید</button>
      <div class="search-container">
        <input type="text" id="search-input" placeholder="جستجو در عنوان، متن، دسته‌بندی یا ID..." />
      </div>
    </div>

    <div class="table-container">
      <table id="item-table">
        <thead>
          <tr>
            <th class="id-cell">شناسه</th>
            <th class="title-cell">عنوان</th>
            <th class="categories-cell">دسته‌بندی‌ها</th>
            <th class="description-cell">متن پیام</th>
            <th class="actions-cell">عملیات</th>
          </tr>
        </thead>
        <tbody id="item-list-body">
        </tbody>
      </table>
    </div>
    <div id="no-results" style="display: none; text-align: center; padding: 3rem; font-size: 1.2rem; color: #555;">
      موردی برای نمایش یافت نشد.
    </div>
    <a href="/admin/index.php" class="back-link">بازگشت به پنل مدیریت</a>
  </main>
  <div id="itemModal" class="modal">
    <div class="modal-content">
      <span class="close-button">×</span>
      <h2 id="modalTitle" style="text-align: right; margin-bottom: 2rem; font-size: 1.5rem; font-weight: 700;"></h2>
      <form id="itemForm">
        <input type="hidden" id="itemId" />
        <label for="id-input">ID (شناسه یکتا):</label>
        <input type="number" id="id-input" name="id" required min="1" />
        <label for="title">عنوان پیام:</label>
        <input type="text" id="title" name="title" required />
        <label>دسته‌بندی‌ها (حداقل یک مورد):</label>
        <div id="categories-checkbox-container"></div>
        <label for="description-textarea">متن پیام:</label>
        <textarea id="description-textarea" name="description" rows="7" required></textarea>
        <div class="button-group">
          <button type="button" id="cancel-edit-btn">لغو</button>
          <button type="submit" id="save-item-btn">ذخیره</button>
        </div>
      </form>
    </div>
  </div>
  <div id="footer-placeholder"></div>
  <script src="/js/header.js"></script>
  <script>
    const API_URL = "/admin/wiki/wiki-api.php";
    let jsonData = [];
    let searchValue = "";

    const availableCategories = [
      "عمومی", "اختلالات", "احراز هویت", "اعتبار سنجی", "تنظیمات امنیت حساب", "تغییر شماره تلفن همراه",
      "عدم دریافت پیامک", "کارت فیزیکی", "کارت و حساب دیجیتال", "مسدودی و رفع مسدودی حساب",
      "انتقال وجه", "خدمات قبض", "شارژ و بسته اینترنت", "تسهیلات برآیند", "تسهیلات برآیند چک یار",
      "تسهیلات پشتوانه", "تسهیلات پیمان", "تسهیلات تکلیفی", "تسهیلات سازمانی",
      "بیمه پاسارگاد", "چک", "خدمات چکاد", "صندوق های سرمایه گذاری", "طرح سرمایه گذاری رویش",
      "دعوت از دوستان", "هدیه دیجیتال", "وی کلاب"
    ];

    const itemListBody = document.getElementById("item-list-body");
    const itemModal = document.getElementById("itemModal");
    const closeButton = document.querySelector(".close-button");
    const itemForm = document.getElementById("itemForm");
    const cancelEditBtn = document.getElementById("cancel-edit-btn");
    const addNewItemBtn = document.getElementById("add-new-item-btn");
    const modalTitle = document.getElementById("modalTitle");
    const searchInput = document.getElementById("search-input");
    const idInput = document.getElementById("id-input");
    const titleInput = document.getElementById("title");
    const descriptionTextarea = document.getElementById("description-textarea");
    const categoriesCheckboxContainer = document.getElementById("categories-checkbox-container");
    const noResultsDiv = document.getElementById("no-results");

    async function apiRequest(action, data) {
      try {
        const response = await fetch(API_URL, {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            action,
            data
          }),
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || "خطای سرور");
        return result;
      } catch (error) {
        console.error(`خطا در عملیات ${action}:`, error);
        alert(`خطا: ${error.message}`);
        throw error;
      }
    }

    function copyToClipboard(text) {
      navigator.clipboard.writeText(text).then(() => {
        alert('✅ متن کپی شد!');
      }).catch(err => {
        console.error('ناموفق در کپی متن: ', err);
        alert('کپی متن انجام نشد.');
      });
    }

    /**
     * [NEW] Creates a table row (<tr>) for an item.
     * @param {object} item - The data item.
     * @returns {HTMLTableRowElement} The created <tr> element.
     */
    function createTableRow(item) {
      const row = document.createElement("tr");
      row.dataset.id = item.id;

      // 1. ID Cell
      const idCell = document.createElement("td");
      idCell.className = "id-cell";
      idCell.textContent = item.id;
      row.appendChild(idCell);

      // 2. Title Cell
      const titleCell = document.createElement("td");
      titleCell.className = "title-cell";
      titleCell.textContent = item.title;
      row.appendChild(titleCell);

      // 3. Categories Cell
      const categoriesCell = document.createElement("td");
      const categoriesContainer = document.createElement("div");
      categoriesContainer.className = "categories-cell";
      (item.categories || []).forEach(cat => {
        const pill = document.createElement("span");
        pill.className = "category-pill";
        pill.textContent = cat;
        categoriesContainer.appendChild(pill);
      });
      categoriesCell.appendChild(categoriesContainer);
      row.appendChild(categoriesCell);

      // 4. Description Cell
      const descriptionCell = document.createElement("td");
      descriptionCell.className = "description-cell";
      descriptionCell.textContent = item.description;
      descriptionCell.title = item.description; // Show full text on hover
      row.appendChild(descriptionCell);

      // 5. Actions Cell
      const actionsCell = document.createElement("td");
      const actionsContainer = document.createElement("div");
      actionsContainer.className = "actions-cell";

      const editButton = document.createElement('button');
      editButton.className = 'edit-btn';
      editButton.title = 'ویرایش';
      editButton.innerHTML = '✏️';
      editButton.onclick = () => editItem(item.id);

      const deleteButton = document.createElement('button');
      deleteButton.className = 'delete-btn';
      deleteButton.title = 'حذف';
      deleteButton.innerHTML = '🗑️';
      deleteButton.onclick = () => deleteItem(item.id);

      const copyButton = document.createElement('button');
      copyButton.className = 'copy-btn';
      copyButton.title = 'کپی متن';
      copyButton.innerHTML = '📋';
      copyButton.onclick = () => copyToClipboard(item.description);

      actionsContainer.appendChild(copyButton);
      actionsContainer.appendChild(editButton);
      actionsContainer.appendChild(deleteButton);
      actionsCell.appendChild(actionsContainer);
      row.appendChild(actionsCell);

      return row;
    }

    /**
     * [MODIFIED] Renders items based on search filter.
     */
    function renderItems() {
      itemListBody.innerHTML = "";

      let filtered = jsonData;
      if (searchValue.trim()) {
        const q = searchValue.trim().toLowerCase();
        filtered = jsonData.filter(item =>
          (item.title && item.title.toLowerCase().includes(q)) ||
          (item.description && item.description.toLowerCase().includes(q)) ||
          (item.categories && item.categories.some(c => c.toLowerCase().includes(q))) ||
          (String(item.id).includes(q))
        );
      }

      noResultsDiv.style.display = filtered.length === 0 ? 'block' : 'none';

      filtered.sort((a, b) => a.id - b.id);

      filtered.forEach(item => {
        const row = createTableRow(item);
        itemListBody.appendChild(row);
      });
    }

    function openModal() {
      itemModal.style.display = "block";
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      itemModal.style.display = "none";
      document.body.style.overflow = '';
      itemForm.reset();
      document.getElementById("itemId").value = '';
    }

    searchInput.addEventListener("input", (e) => {
      searchValue = e.target.value;
      renderItems();
    });

    function renderCategoryCheckboxes(selectedCategories = []) {
      categoriesCheckboxContainer.innerHTML = "";
      availableCategories.forEach(category => {
        const div = document.createElement("div");
        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.id = `cat-${category}`;
        checkbox.name = "category";
        checkbox.value = category;
        checkbox.checked = selectedCategories.includes(category);
        const label = document.createElement("label");
        label.htmlFor = `cat-${category}`;
        label.textContent = category;
        div.appendChild(checkbox);
        div.appendChild(label);
        categoriesCheckboxContainer.appendChild(div);
      });
    }

    function editItem(id) {
      const item = jsonData.find(i => i.id === id);
      if (!item) return;
      document.getElementById("itemId").value = item.id;
      idInput.value = item.id;
      idInput.readOnly = true;
      titleInput.value = item.title;
      descriptionTextarea.value = item.description;
      modalTitle.textContent = "ویرایش پیام";
      renderCategoryCheckboxes(item.categories);
      openModal();
    }

    addNewItemBtn.addEventListener("click", () => {
      itemForm.reset();
      document.getElementById("itemId").value = '';
      const maxId = jsonData.length > 0 ? Math.max(...jsonData.map(i => i.id || 0)) : 0;
      idInput.value = maxId + 1;
      idInput.readOnly = false;
      modalTitle.textContent = "افزودن پیام جدید";
      renderCategoryCheckboxes([]);
      openModal();
    });

    /**
     * [MODIFIED] Handles form submission without full page reload.
     */
    itemForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const editingItemId = parseInt(document.getElementById("itemId").value, 10);
      const isEditing = !!editingItemId;

      const selectedCategories = Array.from(categoriesCheckboxContainer.querySelectorAll('input:checked')).map(cb => cb.value);
      if (!titleInput.value.trim() || selectedCategories.length === 0) {
        alert("عنوان و حداقل یک دسته‌بندی الزامی است.");
        return;
      }

      const itemData = {
        id: parseInt(idInput.value, 10),
        title: titleInput.value.trim(),
        categories: selectedCategories,
        description: descriptionTextarea.value.trim(),
      };

      if (!isEditing && jsonData.some(item => item.id === itemData.id)) {
        alert("این شناسه قبلاً استفاده شده است.");
        return;
      }

      try {
        if (isEditing) {
          // --- UPDATE LOGIC ---
          await apiRequest('update', itemData);
          const index = jsonData.findIndex(i => i.id === editingItemId);
          if (index !== -1) jsonData[index] = itemData; // Update local data

          // Update only the specific row in the table
          const rowToUpdate = itemListBody.querySelector(`tr[data-id="${itemData.id}"]`);
          if (rowToUpdate) {
            const newRow = createTableRow(itemData);
            rowToUpdate.innerHTML = newRow.innerHTML; // Replace content
          }
        } else {
          // --- CREATE LOGIC ---
          await apiRequest('create', itemData);
          jsonData.push(itemData); // Add to local data
          jsonData.sort((a, b) => a.id - b.id); // Keep it sorted
          renderItems(); // Re-render to place new item correctly
        }
        closeModal();
      } catch (error) {
        // Error is already alerted by apiRequest
      }
    });

    /**
     * [MODIFIED] Deletes an item without full page reload.
     */
    async function deleteItem(id) {
      if (confirm("آیا از حذف این پیام مطمئن هستید؟")) {
        try {
          await apiRequest('delete', {
            id
          });
          jsonData = jsonData.filter(item => item.id !== id); // Update local data

          // Remove only the specific row from the table
          const rowToDelete = itemListBody.querySelector(`tr[data-id="${id}"]`);
          if (rowToDelete) {
            rowToDelete.remove();
          }
        } catch (error) {
          // Error is already alerted by apiRequest
        }
      }
    }

    async function loadInitialData() {
      try {
        const response = await fetch(`${API_URL}?v=${new Date().getTime()}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        jsonData = await response.json();
        renderItems();
      } catch (error) {
        console.error("خطا در بارگذاری اولیه داده‌ها:", error);
        alert("خطا در ارتباط با سرور.");
      }
    }

    closeButton.onclick = closeModal;
    window.onclick = function(event) {
      if (event.target == itemModal) closeModal();
    };
    cancelEditBtn.onclick = closeModal;
    document.addEventListener("DOMContentLoaded", loadInitialData);
  </script>
</body>

</html>
