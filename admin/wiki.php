<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>مدیریت اسکریپت‌ها و پیام‌های آماده</title>
  <style>
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
    }

    body {
      background-color: #f4fbf7;
      color: #222;
      margin: 0;
      padding: 0;
      direction: rtl;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    main {
      flex-grow: 1;
      padding: 1.5rem;
      max-width: 1100px;
      width: 100%;
      margin: 0 auto;
      overflow-y: auto;
    }

    .main-content {
      /* display: grid;  <-- This was changed */
      /* grid-template-columns: 1fr 1fr; <-- This was changed */
      column-count: 2;
      column-gap: 2rem;
      align-items: start;
    }

    h2 {
      font-size: 1.3rem;
      margin-bottom: 1.5rem;
      color: #00ae70;
      text-align: center;
      font-weight: 700;
    }

    a {
      text-decoration: none;
      color: #00ae70;
      font-weight: 600;
      font-size: 1.1rem;
      display: block;
      transition: color 0.2s;
    }

    a:hover {
      color: #089863;
    }

    footer {
      background: #00ae70;
      color: #e0e7ff;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.85rem;
      box-shadow: 0 -2px 8px rgba(0, 174, 112, 0.08);
    }

    footer p {
      margin: 0;
    }

    @media (max-width: 900px) {
      .main-content {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }
    }

    @media (max-width: 480px) {
      main {
        padding: 1rem;
      }

      a {
        font-size: 1rem;
      }
    }

    .news-alert-box {
      background: #eafff4;
      padding: 1.2rem 1.5rem;
      border-radius: 0.75rem;
      border-right: 4px solid #00ae70;
      transition: background 0.3s, border-color 0.3s;
      font-size: 1rem;
      box-shadow: 0 2px 12px rgba(0, 174, 112, 0.07);
      cursor: pointer;
      position: relative;
      /* New properties for column layout */
      break-inside: avoid;
      margin-bottom: 1.5rem;
    }

    .news-alert-box:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 174, 112, 0.12);
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
      padding-top: 60px;
    }

    .modal-content {
      background-color: #fefefe;
      margin: 5% auto;
      padding: 25px;
      border: none;
      border-radius: 0.75rem;
      width: 90%;
      max-width: 600px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
      position: relative;
    }

    .close-button {
      color: #555;
      float: left;
      font-size: 32px;
      font-weight: normal;
      position: absolute;
      left: 15px;
      top: 10px;
      cursor: pointer;
    }

    .close-button:hover,
    .close-button:focus {
      color: #000;
      text-decoration: none;
    }

    .modal-content label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
      font-size: 0.95rem;
    }

    .modal-content input[type="text"],
    .modal-content input[type="number"] {
      width: 100%;
      padding: 10px 12px;
      margin-bottom: 18px;
      border: 1px solid #ccc;
      border-radius: 0.5rem;
      font-size: 1rem;
      box-sizing: border-box;
      background-color: #fcfcfc;
      transition: border-color 0.2s;
    }

    .modal-content input[type="text"]:focus,
    .modal-content input[type="number"]:focus,
    .modal-content textarea:focus {
      border-color: #00ae70;
      outline: none;
    }

    .modal-content textarea {
      width: 100%;
      min-height: 120px;
      padding: 10px 12px;
      margin-bottom: 18px;
      border: 1px solid #ccc;
      border-radius: 0.5rem;
      font-size: 1rem;
      box-sizing: border-box;
      background-color: #fcfcfc;
      transition: border-color 0.2s;
      resize: vertical;
      font-family: inherit;
      line-height: 1.7;
    }

    .modal-content .button-group {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
    }

    .modal-content button {
      padding: 10px 20px;
      border: none;
      border-radius: 0.5rem;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 500;
      transition: background-color 0.2s;
    }

    #save-item-btn {
      background-color: #00ae70;
      color: white;
    }

    #save-item-btn:hover {
      background-color: #089863;
    }

    #cancel-edit-btn {
      background-color: #6c757d;
      color: white;
    }

    #cancel-edit-btn:hover {
      background-color: #5a6268;
    }

    #add-new-item-btn {
      background-color: #00ae70;
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: 0.75rem;
      cursor: pointer;
      font-size: 1.1rem;
      font-weight: 600;
      box-shadow: 0 4px 10px rgba(0, 174, 112, 0.2);
      transition: background-color 0.2s, transform 0.2s;
    }

    #add-new-item-btn:hover {
      background-color: #089863;
      transform: translateY(-2px);
    }

    .news-alert-box .actions {
      margin-top: 15px;
      display: flex;
      justify-content: flex-start;
      gap: 10px;
      padding-top: 10px;
      border-top: 1px dashed #eee;
    }

    .news-alert-box .actions button {
      padding: 8px 15px;
      border: none;
      border-radius: 0.5rem;
      cursor: pointer;
      font-size: 0.9rem;
      font-weight: 500;
      transition: background-color 0.2s;
    }

    .news-alert-box .actions .edit-btn {
      background-color: #007bff;
      color: white;
    }

    .news-alert-box .actions .edit-btn:hover {
      background-color: #0056b3;
    }

    .news-alert-box .actions .delete-btn {
      background-color: #dc3545;
      color: white;
    }

    .news-alert-box .actions .delete-btn:hover {
      background-color: #c82333;
    }

    .back-link {
      display: block;
      margin-top: 2rem;
      text-align: center;
      color: #00ae70;
      text-decoration: none;
      font-size: 1rem;
    }

    .back-link:hover {
      text-decoration: underline;
      color: #089863;
    }

    #search-input {
      width: 100%;
      max-width: 100%;
      padding: 12px 16px;
      border: 1.5px solid #00ae70;
      border-radius: 0.75rem;
      font-size: 1.1rem;
      outline: none;
      transition: border-color 0.2s;
      background: #fcfcfc;
      box-sizing: border-box;
    }

    #search-input:focus {
      border-color: #089863;
    }

    #categories-checkbox-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      padding-right: 5px;
    }

    #categories-checkbox-container div {
      display: flex;
      align-items: center;
    }

    #categories-checkbox-container input[type="checkbox"] {
      margin-left: 8px;
      width: 18px;
      height: 18px;
      min-width: 18px;
      min-height: 18px;
      border: 1px solid #ccc;
      border-radius: 4px;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      cursor: pointer;
      outline: none;
      transition: all 0.2s ease-in-out;
      position: relative;
    }

    #categories-checkbox-container input[type="checkbox"]:checked {
      background-color: #00ae70;
      border-color: #00ae70;
    }

    #categories-checkbox-container input[type="checkbox"]:focus {
      box-shadow: 0 0 0 3px rgba(0, 174, 112, 0.3);
    }

    #categories-checkbox-container input[type="checkbox"]:checked::before {
      content: "✔";
      display: block;
      width: 100%;
      height: 100%;
      text-align: center;
      line-height: 18px;
      font-size: 14px;
      color: white;
      position: absolute;
      top: 0;
      left: 0;
    }

    #categories-checkbox-container label {
      margin-bottom: 0 !important;
      font-weight: normal !important;
      color: #555 !important;
      font-size: 0.95rem !important;
      cursor: pointer;
    }

    /* New style for the load more button */
    #load-more-btn {
      background-color: #007bff;
      color: white;
      padding: 12px 25px;
      border: none;
      border-radius: 0.75rem;
      cursor: pointer;
      font-size: 1.1rem;
      font-weight: 600;
      display: block;
      margin: 2rem auto 0;
      box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
      transition: background-color 0.2s, transform 0.2s;
    }

    #load-more-btn:hover {
      background-color: #0056b3;
      transform: translateY(-2px);
    }

    #load-more-btn:disabled {
      background-color: #ccc;
      cursor: not-allowed;
      box-shadow: none;
    }
  </style>
</head>

<body>
  <div id="header-placeholder"></div>
  <main>
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 25px; flex-wrap: wrap;">
      <button id="add-new-item-btn">➕ افزودن پیام جدید</button>
      <div style="flex-grow: 1; min-width: 250px;">
        <input type="text" id="search-input" placeholder="جستجو در عنوان، متن، دسته‌بندی یا ID..." />
      </div>
    </div>

    <div id="item-list" class="main-content"></div>

    <div id="load-more-container"></div>

    <a href="/admin/index.php" class="back-link">بازگشت به بخش مدیریت</a>
  </main>

  <div id="itemModal" class="modal">
    <div class="modal-content">
      <span class="close-button">×</span>
      <h2 id="modalTitle" style="text-align: center; margin-bottom: 25px"></h2>
      <form id="itemForm">
        <input type="hidden" id="itemId" />
        <label for="id-input">ID:</label>
        <input type="number" id="id-input" name="id" required min="1" style="direction: ltr; text-align: left; margin-bottom: 18px" />
        <label for="title">عنوان پیام:</label>
        <input type="text" id="title" name="title" required />
        <label>دسته‌بندی‌ها:</label>
        <div id="categories-checkbox-container" style="margin-bottom: 18px"></div>
        <label for="description-textarea">متن پیام:</label>
        <textarea id="description-textarea" name="description" rows="7" required></textarea>
        <div class="button-group">
          <button type="submit" id="save-item-btn">ذخیره</button>
          <button type="button" id="cancel-edit-btn">لغو</button>
        </div>
      </form>
    </div>
  </div>

  <div id="footer-placeholder"></div>
  <script src="/js/header.js"></script>
  <script>
    let jsonData = [];
    let currentItemIndex = -1;
    let searchValue = "";
    let currentPage = 1;
    const itemsPerPage = 10;

    const availableCategories = [
      "عمومی", "احراز هویت", "اعتبار سنجی", "تنظیمات امنیت حساب", "تغییر شماره تلفن همراه",
      "عدم دریافت پیامک", "کارت فیزیکی", "کارت و حساب دیجیتال", "مسدودی و رفع مسدودی حساب",
      "انتقال وجه", "خدمات قبض", "شارژ و بسته اینترنت", "تسهیلات برآیند", "تسهیلات برآیند چک یار",
      "تسهیلات پشتوانه", "تسهیلات پیش درآمد", "تسهیلات پیمان", "تسهیلات تکلیفی",
      "تسهیلات سازمانی", "بیمه پاسارگاد", "چک", "خدمات چکاد", "صندوق های سرمایه گذاری",
      "طرح سرمایه گذاری رویش", "دعوت از دوستان", "هدیه دیجیتال", "وی کلاب",
    ];

    const itemListDiv = document.getElementById("item-list");
    const loadMoreContainer = document.getElementById("load-more-container");
    const itemModal = document.getElementById("itemModal");
    const closeButton = document.querySelector(".close-button");
    const itemForm = document.getElementById("itemForm");
    const cancelEditBtn = document.getElementById("cancel-edit-btn");
    const addNewItemBtn = document.getElementById("add-new-item-btn");
    const modalTitle = document.getElementById("modalTitle");
    const descriptionTextarea = document.getElementById("description-textarea");
    const searchInput = document.getElementById("search-input");
    const idInput = document.getElementById("id-input");
    const categoriesCheckboxContainer = document.getElementById(
      "categories-checkbox-container"
    );

    async function saveDataToServer() {
      try {
        const response = await fetch("/data/save-wiki.php", {
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
      if (descriptionTextarea) descriptionTextarea.focus();
    }

    function closeModal() {
      itemModal.style.display = "none";
      itemForm.reset();
      currentItemIndex = -1;
      if (descriptionTextarea) descriptionTextarea.value = "";
      if (idInput) idInput.value = "";
      if (categoriesCheckboxContainer)
        categoriesCheckboxContainer.innerHTML = "";
    }

    closeButton.onclick = closeModal;
    window.onclick = function(event) {
      if (event.target == itemModal) closeModal();
    };
    cancelEditBtn.onclick = closeModal;

    function renderItems() {
      // Clear only the list on re-render, button is handled separately
      itemListDiv.innerHTML = "";
      loadMoreContainer.innerHTML = "";

      let filtered = jsonData;
      if (searchValue.trim()) {
        const q = searchValue.trim().toLowerCase();
        filtered = jsonData.filter(
          (item) =>
          (item.title && item.title.toLowerCase().includes(q)) ||
          (item.description &&
            item.description.toLowerCase().includes(q)) ||
          (item.categories &&
            Array.isArray(item.categories) &&
            item.categories.some((c) => c.toLowerCase().includes(q))) ||
          (item.id && String(item.id).includes(q))
        );
      }

      if (filtered.length === 0) {
        itemListDiv.innerHTML =
          '<p style="text-align: center; column-span: all; margin-top: 50px; font-size: 1.2rem; color: #555;">موردی برای نمایش وجود ندارد.</p>';
        return;
      }

      filtered.sort((a, b) => (a.id > b.id ? 1 : -1));

      const itemsToShow = filtered.slice(0, currentPage * itemsPerPage);

      itemsToShow.forEach((item) => {
        const originalIndex = jsonData.findIndex(
          (originalItem) => originalItem.id === item.id
        );

        const card = document.createElement("div");
        card.classList.add("news-alert-box");
        const descriptionHtml = (item.description || "").replace(/\n/g, "<br>");
        const categoriesHtml =
          item.categories && item.categories.length ?
          item.categories.join("، ") :
          "بدون دسته‌بندی";

        card.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; gap: 10px;">
                <h3 style="margin: 0; flex-grow: 1; font-size: 1.15rem; font-weight: 700; color: #111;">
                    ${item.title || "بدون عنوان"}
                </h3>
                <div class="actions" style="margin-top: 0; padding-top: 0; border-top: none; flex-shrink: 0;">
                    <button class="edit-btn" data-index="${originalIndex}">ویرایش</button>
                    <button class="delete-btn" data-index="${originalIndex}">حذف</button>
                </div>
            </div>
            <div style="margin-bottom: 15px; font-size: 0.9rem; color: #555;">
                <span><strong>ID:</strong> <span style="color: #008250; font-weight: 600;">${item.id || "-"}</span></span>
                <span style="margin-right: 15px;"><strong>دسته‌بندی‌ها:</strong> ${categoriesHtml}</span>
            </div>
            <div style="line-height: 1.7; color: #333; font-size: 1rem; border-top: 1px dashed #c0e8d9; padding-top: 15px;">
                ${descriptionHtml}
            </div>
            `;
        itemListDiv.appendChild(card);
      });

      if (itemsToShow.length < filtered.length) {
        const loadMoreBtn = document.createElement("button");
        loadMoreBtn.id = "load-more-btn";
        loadMoreBtn.textContent = "بارگذاری بیشتر";
        loadMoreBtn.onclick = () => {
          currentPage++;
          // Instead of re-rendering everything, you can choose to append.
          // But re-rendering is simpler to manage with search.
          renderItems();
        };
        loadMoreContainer.appendChild(loadMoreBtn);
      }

      document.querySelectorAll(".edit-btn").forEach((button) => {
        button.addEventListener("click", (e) => {
          e.stopPropagation();
          editItem(parseInt(e.target.dataset.index));
        });
      });

      document.querySelectorAll(".delete-btn").forEach((button) => {
        button.addEventListener("click", (e) => {
          e.stopPropagation();
          deleteItem(parseInt(e.target.dataset.index));
        });
      });
    }

    searchInput.addEventListener("input", (e) => {
      searchValue = e.target.value;
      currentPage = 1; // Reset to the first page on new search
      renderItems();
    });

    function renderCategoryCheckboxes(selectedCategories = []) {
      categoriesCheckboxContainer.innerHTML = "";
      availableCategories.forEach((category) => {
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

    function editItem(index) {
      currentItemIndex = index;
      const item = jsonData[index];
      document.getElementById("itemId").value = index;
      idInput.value = item.id || "";
      document.getElementById("title").value = item.title || "";
      descriptionTextarea.value = item.description || "";
      modalTitle.textContent = "ویرایش پیام";
      renderCategoryCheckboxes(item.categories || []);
      openModal();
    }

    addNewItemBtn.addEventListener("click", () => {
      currentItemIndex = -1;
      itemForm.reset();
      descriptionTextarea.value = "";
      const maxId =
        jsonData.length > 0 ?
        Math.max(...jsonData.map((i) => i.id || 0)) :
        0;
      idInput.value = maxId + 1;
      modalTitle.textContent = "افزودن پیام جدید";
      renderCategoryCheckboxes([]);
      openModal();
    });

    itemForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const selectedCategories = Array.from(
        categoriesCheckboxContainer.querySelectorAll(
          'input[type="checkbox"]:checked'
        )
      ).map((cb) => cb.value);

      if (selectedCategories.length === 0) {
        alert("لطفاً حداقل یک دسته‌بندی را انتخاب کنید.");
        return;
      }
      const newItem = {
        id: parseInt(idInput.value, 10),
        title: document.getElementById("title").value,
        categories: selectedCategories,
        description: descriptionTextarea.value,
      };
      if (
        jsonData.some(
          (item, idx) => item.id === newItem.id && idx !== currentItemIndex
        )
      ) {
        alert("این ID قبلاً استفاده شده است. لطفاً یک ID یکتا وارد کنید.");
        return;
      }
      if (currentItemIndex === -1) {
        jsonData.push(newItem);
      } else {
        jsonData[currentItemIndex] = newItem;
      }
      currentPage = 1; // Reset to page 1 to see the new/edited item
      renderItems();
      closeModal();
      saveDataToServer();
    });

    function deleteItem(index) {
      if (confirm("آیا مطمئن هستید که می‌خواهید این پیام را حذف کنید؟")) {
        jsonData.splice(index, 1);
        renderItems();
        saveDataToServer();
      }
    }

    async function loadInitialJson() {
      try {
        const response = await fetch(
          `/data/wiki.json?v=${new Date().getTime()}`
        );
        if (!response.ok) {
          if (response.status === 404) jsonData = [];
          else throw new Error(`HTTP error! status: ${response.status}`);
        } else {
          jsonData = await response.json();
        }
      } catch (error) {
        console.error("Error loading wiki.json:", error);
        alert("خطا در بارگذاری اولیه فایل JSON.");
        jsonData = [];
      } finally {
        currentPage = 1;
        renderItems();
      }
    }

    document.addEventListener("DOMContentLoaded", loadInitialJson);
  </script>
</body>

</html>
