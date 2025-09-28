<?php

require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>مدیریت اسکریپت‌ها و پیام‌های آماده</title>
  <style>
    /* All previous styles remain unchanged */
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

    /* --- [START] UNCHANGED HEADER & FOOTER STYLES --- */

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


    footer {
      min-height: var(--footer-h);
      font-size: .85rem
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

    main {
      flex-grow: 1;
      padding: 2.5rem 2rem;
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
    }

    /* --- Page Header & Actions --- */
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

    /* --- Script Cards --- */
    #item-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 1.5rem;
    }

    .script-card {
      background-color: var(--card-bg);
      border-radius: var(--border-radius);
      box-shadow: 0 4px 15px var(--shadow-light);
      border: 1px solid var(--border-color);
      transition: all 0.2s ease-in-out;
      display: flex;
      flex-direction: column;
    }

    .script-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 25px var(--shadow-medium);
    }

    .card-header {
      padding: 1.25rem;
    }

    .card-title {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--text-color);
    }

    .card-meta {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.5rem 1rem;
      padding: 0 1.25rem 1.25rem;
      border-bottom: 1px solid var(--border-color);
      font-size: 0.9rem;
    }

    .card-id {
      color: var(--primary-dark);
      font-weight: 600;
    }

    .card-categories {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
    }

    .category-pill {
      background-color: #f3f4f6;
      color: #4b5563;
      padding: 0.2rem 0.6rem;
      border-radius: 999px;
      font-size: 0.8rem;
      font-weight: 500;
    }

    .card-body {
      padding: 1.25rem;
      flex-grow: 1;
    }

    .card-description {
      line-height: 1.7;
      color: var(--text-color);
      white-space: pre-wrap;
      word-wrap: break-word;
    }

    .card-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      padding: 0.75rem 1.25rem;
      border-top: 1px solid var(--border-color);
      background-color: #fafbfc;
      border-bottom-left-radius: var(--border-radius);
      border-bottom-right-radius: var(--border-radius);
    }

    .card-actions {
      display: flex;
      gap: 0.5rem;
      flex-shrink: 0;
    }

    .card-actions button {
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

    .card-actions .edit-btn:hover {
      background-color: #e9f5ff;
      color: #007bff;
    }

    .card-actions .delete-btn:hover {
      background-color: #fef2f2;
      color: #dc3545;
    }

    .copy-btn {
      background-color: var(--primary-light);
      color: var(--primary-dark);
      border: 1px solid var(--primary-color);
      flex-grow: 1;
      padding: 0.6rem 1rem;
      border-radius: 0.5rem;
      cursor: pointer;
      font-size: 0.95rem;
      font-weight: 600;
      text-align: center;
      transition: all 0.2s;
    }

    .copy-btn:hover {
      background-color: var(--primary-color);
      color: white;
    }

    .copy-btn.copied {
      background-color: #28a745;
      color: white;
      border-color: #28a745;
    }

    #load-more-btn {
      background-color: var(--blue-color);
      color: white;
      padding: 0.75rem 2rem;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-size: 1rem;
      font-weight: 600;
      display: block;
      margin: 2rem auto 0;
      box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
      transition: all 0.2s;
    }

    #load-more-btn:hover {
      background-color: var(--blue-dark);
      transform: translateY(-2px);
    }

    #load-more-btn:disabled {
      background-color: #ccc;
      cursor: not-allowed;
      box-shadow: none;
    }

    /* --- Modal Styles --- */
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

    @media (max-width: 768px) {
      #item-list {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 480px) {
      main {
        padding: 1.5rem 1rem;
      }

      .action-bar {
        justify-content: center;
      }

      .page-title {
        font-size: 1.5rem;
      }

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
      <h1 class="page-title">مدیریت پیام‌های آماده</h1>
      <p class="page-subtitle">پیام‌ها و اسکریپت‌های پاسخگویی را از اینجا جستجو، ویرایش و مدیریت کنید.</p>
    </div>
    <div class="action-bar">
      <button id="add-new-item-btn">✨ افزودن پیام جدید</button>
      <div class="search-container">
        <input type="text" id="search-input" placeholder="جستجو در عنوان، متن، دسته‌بندی یا ID..." />
      </div>
    </div>
    <div id="item-list" style="margin-top: 2rem;"></div>
    <div id="load-more-container"></div>
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
    // URL فایل API جدید شما
    const API_URL = "/admin/wiki/wiki-api.php";

    let jsonData = []; // این آرایه به عنوان یک حافظه موقت (cache) از داده‌ها عمل می‌کند
    let searchValue = "";
    let currentPage = 1;
    const itemsPerPage = 9;

    const availableCategories = [
      "عمومی", "احراز هویت", "اعتبار سنجی", "تنظیمات امنیت حساب", "تغییر شماره تلفن همراه",
      "عدم دریافت پیامک", "کارت فیزیکی", "کارت و حساب دیجیتال", "مسدودی و رفع مسدودی حساب",
      "انتقال وجه", "خدمات قبض", "شارژ و بسته اینترنت", "تسهیلات برآیند", "تسهیلات برآیند چک یار",
      "تسهیلات پشتوانه", "تسهیلات پیش درآمد", "تسهیلات پیمان", "تسهیلات تکلیفی", "تسهیلات سازمانی",
      "بیمه پاسارگاد", "چک", "خدمات چکاد", "صندوق های سرمایه گذاری", "طرح سرمایه گذاری رویش",
      "دعوت از دوستان", "هدیه دیجیتال", "وی کلاب"
    ];

    // --- تمامی Element-‌ها مثل قبل تعریف می‌شوند ---
    const itemListDiv = document.getElementById("item-list");
    const loadMoreContainer = document.getElementById("load-more-container");
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

    /**
     * تابع جدید برای ارسال درخواست به API
     * @param {string} action - نوع عملیات (create, update, delete)
     * @param {object} data - داده‌های مورد نیاز برای عملیات
     */
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
        console.log(result.message);
        return result;
      } catch (error) {
        console.error(`خطا در عملیات ${action}:`, error);
        alert(`خطا در عملیات ${action}: ` + error.message);
        throw error; // اجازه می‌دهد تا خطای اصلی در جای دیگر مدیریت شود
      }
    }

    // تابع copyToClipboard بدون تغییر باقی می‌ماند...
    function copyToClipboard(text, button) {
      navigator.clipboard.writeText(text).then(() => {
        const originalText = button.innerHTML;
        button.innerHTML = '✅ کپی شد!';
        button.classList.add('copied');
        setTimeout(() => {
          button.innerHTML = originalText;
          button.classList.remove('copied');
        }, 1500);
      }).catch(err => {
        console.error('ناموفق در کپی متن: ', err);
        alert('کپی متن انجام نشد.');
      });
    }

    // تابع renderItems با تغییرات جزئی برای استفاده از ایندکس اصلی
    function renderItems() {
      if (currentPage === 1) itemListDiv.innerHTML = "";
      loadMoreContainer.innerHTML = "";

      let filtered = jsonData;
      if (searchValue.trim()) {
        const q = searchValue.trim().toLowerCase();
        filtered = jsonData.filter(item =>
          (item.title && item.title.toLowerCase().includes(q)) ||
          (item.description && item.description.toLowerCase().includes(q)) ||
          (item.categories && Array.isArray(item.categories) && item.categories.some(c => c.toLowerCase().includes(q))) ||
          (item.id && String(item.id).includes(q))
        );
      }

      if (filtered.length === 0) {
        itemListDiv.innerHTML = '<p style="text-align: center; grid-column: 1 / -1; margin-top: 50px; font-size: 1.2rem; color: #555;">موردی برای نمایش نیست.</p>';
        return;
      }

      filtered.sort((a, b) => (a.id > b.id ? 1 : -1));
      const itemsToShow = filtered.slice(0, currentPage * itemsPerPage);

      const currentRenderedIds = new Set(Array.from(itemListDiv.querySelectorAll('.script-card')).map(card => card.dataset.id));
      const newItems = itemsToShow.filter(item => !currentRenderedIds.has(String(item.id)));

      newItems.forEach(item => {
        const card = document.createElement("div");
        card.className = "script-card";
        card.dataset.id = String(item.id);

        // ... بخش ساخت کارت بدون تغییر باقی می‌ماند ...
        // فقط در دکمه‌های edit و delete به جای ایندکس از ID استفاده می‌کنیم
        const originalIndex = jsonData.findIndex(originalItem => originalItem.id === item.id);
        const cardHeader = document.createElement('div');
        cardHeader.className = 'card-header';
        const cardTitle = document.createElement('h3');
        cardTitle.className = 'card-title';
        cardTitle.textContent = item.title || "بدون عنوان";
        cardHeader.appendChild(cardTitle);

        const cardMeta = document.createElement('div');
        cardMeta.className = 'card-meta';
        const cardId = document.createElement('span');
        cardId.className = 'card-id';
        cardId.textContent = `شناسه: ${item.id || "-"}`;
        const cardCategories = document.createElement('div');
        cardCategories.className = 'card-categories';
        if (item.categories && item.categories.length) {
          item.categories.forEach(cat => {
            const pill = document.createElement('span');
            pill.className = 'category-pill';
            pill.textContent = cat;
            cardCategories.appendChild(pill);
          });
        } else {
          const pill = document.createElement('span');
          pill.className = 'category-pill';
          pill.style.opacity = '0.7';
          pill.textContent = 'بدون دسته‌بندی';
          cardCategories.appendChild(pill);
        }
        cardMeta.appendChild(cardId);
        cardMeta.appendChild(cardCategories);

        const cardBody = document.createElement('div');
        cardBody.className = 'card-body';
        const cardDescription = document.createElement('div');
        cardDescription.className = 'card-description';
        cardDescription.textContent = item.description || "";
        cardBody.appendChild(cardDescription);

        const cardFooter = document.createElement('div');
        cardFooter.className = 'card-footer';
        const cardActions = document.createElement('div');
        cardActions.className = 'card-actions';

        const editButton = document.createElement('button');
        editButton.className = 'edit-btn';
        editButton.title = 'ویرایش';
        editButton.dataset.id = item.id; // استفاده از ID
        editButton.innerHTML = '✏️';

        const deleteButton = document.createElement('button');
        deleteButton.className = 'delete-btn';
        deleteButton.title = 'حذف';
        deleteButton.dataset.id = item.id; // استفاده از ID
        deleteButton.innerHTML = '🗑️';

        cardActions.appendChild(editButton);
        cardActions.appendChild(deleteButton);

        const copyButton = document.createElement('button');
        copyButton.className = 'copy-btn';
        copyButton.dataset.description = item.description;
        copyButton.innerHTML = '📋 کپی متن';

        cardFooter.appendChild(cardActions);
        cardFooter.appendChild(copyButton);

        card.appendChild(cardHeader);
        card.appendChild(cardMeta);
        card.appendChild(cardBody);
        card.appendChild(cardFooter);

        itemListDiv.appendChild(card);
      });

      if (itemsToShow.length < filtered.length) {
        const loadMoreBtn = document.createElement("button");
        loadMoreBtn.id = "load-more-btn";
        loadMoreBtn.textContent = "نمایش بیشتر";
        loadMoreBtn.onclick = () => {
          currentPage++;
          renderItems();
        };
        loadMoreContainer.appendChild(loadMoreBtn);
      }

      // Event listener ها برای دکمه‌های جدید
      document.querySelectorAll(".edit-btn").forEach(button => button.onclick = (e) => editItem(parseInt(e.currentTarget.dataset.id)));
      document.querySelectorAll(".delete-btn").forEach(button => button.onclick = (e) => deleteItem(parseInt(e.currentTarget.dataset.id)));
      document.querySelectorAll('.copy-btn').forEach(button => button.onclick = (e) => copyToClipboard(e.currentTarget.dataset.description, e.currentTarget));
    }

    // تابع openModal و closeModal بدون تغییر باقی می‌مانند...
    function openModal() {
      itemModal.style.display = "block";
      document.body.style.overflow = 'hidden';
      titleInput.focus();
    }

    function closeModal() {
      itemModal.style.display = "none";
      document.body.style.overflow = '';
      itemForm.reset();
      document.getElementById("itemId").value = ''; // پاک کردن ID مخفی
    }

    // جستجو مثل قبل کار می‌کند
    searchInput.addEventListener("input", (e) => {
      searchValue = e.target.value;
      currentPage = 1;
      renderItems();
    });

    // تابع renderCategoryCheckboxes بدون تغییر باقی می‌ماند...
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

      document.getElementById("itemId").value = item.id; // استفاده از فیلد مخفی
      idInput.value = item.id || "";
      idInput.readOnly = true; // شناسه در زمان ویرایش نباید تغییر کند
      titleInput.value = item.title || "";
      descriptionTextarea.value = item.description || "";
      modalTitle.textContent = "ویرایش پیام";
      renderCategoryCheckboxes(item.categories || []);
      openModal();
    }

    addNewItemBtn.addEventListener("click", () => {
      itemForm.reset();
      document.getElementById("itemId").value = ''; // حالت افزودن
      const maxId = jsonData.length > 0 ? Math.max(...jsonData.map(i => i.id || 0)) : 0;
      idInput.value = maxId + 1;
      idInput.readOnly = false;
      modalTitle.textContent = "افزودن پیام جدید";
      renderCategoryCheckboxes([]);
      openModal();
    });

    /**
     * مدیریت ثبت فرم برای افزودن و ویرایش
     */
    itemForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const editingItemId = parseInt(document.getElementById("itemId").value, 10);
      const isEditing = !!editingItemId;

      const selectedCategories = Array.from(categoriesCheckboxContainer.querySelectorAll('input[type="checkbox"]:checked')).map(cb => cb.value);
      if (!titleInput.value.trim() || selectedCategories.length === 0) {
        alert("عنوان و حداقل یک دسته‌بندی الزامی است.");
        return;
      }

      const newItemData = {
        id: parseInt(idInput.value, 10),
        title: titleInput.value,
        categories: selectedCategories,
        description: descriptionTextarea.value,
      };

      // چک کردن یکتا بودن ID فقط در حالت افزودن
      if (!isEditing && jsonData.some(item => item.id === newItemData.id)) {
        alert("این شناسه قبلاً استفاده شده است.");
        return;
      }

      try {
        if (isEditing) {
          // ویرایش
          await apiRequest('update', newItemData);
          const index = jsonData.findIndex(i => i.id === editingItemId);
          if (index !== -1) jsonData[index] = newItemData;
        } else {
          // افزودن
          await apiRequest('create', newItemData);
          jsonData.push(newItemData);
        }
        currentPage = 1;
        renderItems();
        closeModal();
      } catch (error) {
        // خطا قبلا نمایش داده شده است
      }
    });

    /**
     * تابع جدید برای حذف آیتم
     */
    async function deleteItem(id) {
      if (confirm("آیا از حذف این پیام مطمئن هستید؟")) {
        try {
          await apiRequest('delete', {
            id
          });
          jsonData = jsonData.filter(item => item.id !== id);
          currentPage = 1;
          renderItems();
        } catch (error) {
          // خطا قبلا نمایش داده شده است
        }
      }
    }

    /**
     * بارگذاری اولیه داده‌ها از API
     */
    async function loadInitialData() {
      try {
        const response = await fetch(`${API_URL}?v=${new Date().getTime()}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        jsonData = await response.json();
      } catch (error) {
        console.error("خطا در بارگذاری اولیه داده‌ها:", error);
        alert("خطا در ارتباط با سرور.");
        jsonData = [];
      } finally {
        currentPage = 1;
        renderItems();
      }
    }

    // --- Event Listeners نهایی ---
    closeButton.onclick = closeModal;
    window.onclick = function(event) {
      if (event.target == itemModal) closeModal();
    };
    cancelEditBtn.onclick = closeModal;
    document.addEventListener("DOMContentLoaded", loadInitialData);
  </script>
</body>

</html>
