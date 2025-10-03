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
      display: flex;
      flex-direction: column;
      min-height: 100vh;
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
      min-height: 60px;
      font-size: .85rem;
    }

    .page-header {
      margin-bottom: 2rem;
    }

    .page-title {
      font-size: clamp(1.5rem, 3vw, 2rem);
      font-weight: 800;
      color: var(--primary-dark);
    }

    .page-subtitle {
      font-size: clamp(.95rem, 2.2vw, 1rem);
      font-weight: 400;
      color: var(--secondary-text);
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
      white-space: nowrap;
    }

    .btn:hover:not(.loading) {
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

    .btn-icon {
      background: none;
      border: none;
      padding: .5rem;
      border-radius: 50%;
      cursor: pointer;
      color: var(--secondary-text);
      width: 40px;
      height: 40px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: background-color 0.2s, color 0.2s;
    }

    .btn-icon:hover {
      background-color: var(--border-color);
      color: var(--text-color);
    }

    .btn-icon .icon {
      width: 1.25rem;
      height: 1.25rem;
    }

    .search-container {
      flex-grow: 1;
      min-width: 300px;
    }

    #search-input {
      width: 100%;
      padding: 0.8em 1.2em;
      border: 1.5px solid var(--border-color);
      border-radius: var(--radius);
      font-size: 1rem;
      background-color: var(--card-bg);
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    #search-input:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(0, 174, 112, 0.15);
      outline: none;
    }

    .table-container {
      background-color: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-color);
      overflow-x: auto;
    }

    #item-table {
      width: 100%;
      border-collapse: collapse;
      text-align: right;
    }

    #item-table thead {
      background-color: var(--bg-color);
    }

    #item-table th,
    #item-table td {
      padding: 1.25rem;
      vertical-align: middle;
    }

    #item-table th {
      font-weight: 600;
      color: var(--secondary-text);
      font-size: 0.9rem;
      border-bottom: 1px solid var(--border-color);
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
    }

    .title-cell {
      font-weight: 600;
      color: var(--text-color);
    }

    .categories-cell {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 0.5rem;
    }

    .category-pill {
      background-color: var(--bg-color);
      color: var(--secondary-text);
      padding: 0.2rem 0.6rem;
      border-radius: 999px;
      font-size: 0.8rem;
      font-weight: 500;
      white-space: nowrap;
    }

    .description-cell {
      max-width: 450px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      cursor: help;
    }

    .actions-cell {
      display: flex;
      gap: 0.5rem;
      justify-content: flex-start;
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
      transition: opacity 0.3s ease;
    }

    .modal.visible {
      display: flex;
      opacity: 1;
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
      display: flex;
      flex-direction: column;
      max-height: 90vh;
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

    .form-group {
      margin-bottom: 1.25rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: var(--secondary-text);
      font-size: .9rem;
    }

    .modal-content input[type="text"],
    .modal-content input[type="number"],
    .modal-content textarea {
      width: 100%;
      padding: 0.8em 1.2em;
      margin-bottom: 1.25rem;
      border: 1.5px solid var(--border-color);
      border-radius: var(--radius);
      font-size: 1rem;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .modal-content input:focus-visible,
    .modal-content textarea:focus-visible {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(0, 174, 112, .15);
    }

    .modal-content textarea {
      resize: vertical;
      min-height: 150px;
      line-height: 1.7;
    }

    #categories-checkbox-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 0.75rem;
      margin-bottom: 1.25rem;
      border: 1px solid var(--border-color);
      padding: 1rem;
      border-radius: var(--radius);
      max-height: 250px;
      overflow-y: auto;
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

    .form-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--border-color);
    }

    .navigation-buttons {
      display: flex;
      gap: 10px;
    }

    .form-step {
      display: none;
    }

    .form-step.active-step {
      display: block;
      animation: fadeIn .5s;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    #step-indicator {
      font-size: .9rem;
      color: var(--secondary-text);
      font-weight: 500;
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

    .toast.success {
      background-color: var(--success-color);
    }

    .toast.error {
      background-color: var(--danger-color);
    }

    .toast.info {
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

    .spinner {
      border: 5px solid var(--bg-color);
      border-top: 5px solid var(--primary-color);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: 0 auto;
    }

    mark {
      background-color: #fef08a;
      padding: 0 2px;
      border-radius: 3px;
    }
  </style>
</head>

<body>
  <div id="header-placeholder"></div>
  <main>
    <div class="page-header">
      <div>
        <h1 class="page-title">مدیریت پیام‌های آماده</h1>
        <p class="page-subtitle">پیام‌ها و اسکریپت‌های پاسخگویی را از اینجا جستجو، ویرایش و مدیریت کنید.</p>
      </div>
    </div>
    <div class="action-bar">
      <button id="add-new-item-btn" class="btn btn-primary">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14" />
          <path d="M12 5v14" />
        </svg>
        <span>افزودن پیام جدید</span>
      </button>
      <div class="search-container">
        <input type="text" id="search-input" placeholder="جستجو در عنوان، متن، دسته‌بندی یا ID..." />
      </div>
    </div>
    <div class="table-container">
      <table id="item-table">
        <thead>
          <tr>
            <th style="width: 5%;">شناسه</th>
            <th style="width: 20%;">عنوان</th>
            <th style="width: 10%;">دسته‌بندی‌ها</th>
            <th>متن پیام</th>
            <th style="width: 15%;">عملیات</th>
          </tr>
        </thead>
        <tbody id="item-list-body">
          <tr id="loading-row">
            <td colspan="5" style="text-align: center; padding: 4rem;">
              <div class="spinner"></div>
              <p style="margin-top: 1rem; color: var(--secondary-text);">در حال بارگذاری اطلاعات...</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="no-results" style="display: none; text-align: center; padding: 3rem; font-size: 1.2rem; color: var(--secondary-text);">
      موردی برای نمایش یافت نشد.
    </div>
  </main>
  <div id="itemModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="modalTitle"></h2>
        <span id="step-indicator"></span>
      </div>
      <form id="itemForm">
        <input type="hidden" id="itemId" />
        <div class="form-step" data-step="1">
          <div class="form-group">
            <label for="id-input">شناسه (ID) یکتا:</label>
            <input type="number" id="id-input" name="id" required min="1" />
          </div>
          <div class="form-group">
            <label for="title">عنوان پیام:</label>
            <input type="text" id="title" name="title" required />
          </div>
        </div>
        <div class="form-step" data-step="2">
          <div class="form-group">
            <label>دسته‌بندی‌ها (حداقل یک مورد):</label>
            <div id="categories-checkbox-container"></div>
          </div>
        </div>
        <div class="form-step" data-step="3">
          <div class="form-group">
            <label for="description-textarea">متن پیام:</label>
            <textarea id="description-textarea" name="description" rows="7" required></textarea>
          </div>
        </div>
        <div class="form-actions">
          <button type="button" class="btn btn-secondary" id="cancel-edit-btn">لغو</button>
          <div class="navigation-buttons">
            <button type="button" id="prev-btn" class="btn btn-secondary">قبلی</button>
            <button type="button" id="next-btn" class="btn btn-primary">بعدی</button>
            <button type="submit" id="save-item-btn" class="btn btn-primary"></button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div id="toast-container"></div>
  <div id="footer-placeholder"></div>
  <script src="/js/header.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const API_URL = "/admin/wiki/wiki-api.php";
      let jsonData = [];
      let searchValue = "";
      let currentStep = 1;
      const totalSteps = 3;

      const availableCategories = ["عمومی", "اختلالات", "احراز هویت", "اعتبار سنجی", "تنظیمات امنیت حساب", "تغییر شماره تلفن همراه", "عدم دریافت پیامک", "کارت فیزیکی", "کارت و حساب دیجیتال", "مسدودی و رفع مسدودی حساب", "انتقال وجه", "خدمات قبض", "شارژ و بسته اینترنت", "تسهیلات برآیند", "تسهیلات برآیند چک یار", "تسهیلات پشتوانه", "تسهیلات پیمان", "تسهیلات تکلیفی", "تسهیلات سازمانی", "بیمه پاسارگاد", "چک", "خدمات چکاد", "صندوق های سرمایه گذاری", "طرح سرمایه گذاری رویش", "دعوت از دوستان", "هدیه دیجیتال", "وی کلاب"];

      const itemListBody = document.getElementById("item-list-body");
      const itemModal = document.getElementById("itemModal");
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
      const prevBtn = document.getElementById('prev-btn');
      const nextBtn = document.getElementById('next-btn');
      const saveBtn = document.getElementById('save-item-btn');
      const stepIndicator = document.getElementById('step-indicator');
      const steps = document.querySelectorAll('.form-step');

      const ICONS = {
        add: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>`,
        save: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>`,
        copy: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>`,
        edit: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>`,
        delete: `<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>`
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
          showToast(`خطا: ${error.message}`, 'error');
          throw error;
        }
      }

      function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
          showToast('متن با موفقیت کپی شد.', 'info');
        }).catch(err => {
          showToast('خطا در کپی کردن متن.', 'error');
        });
      }

      function highlightText(text, query) {
        if (!query || !text) return text;
        const regex = new RegExp(`(${query.replace(/[\.\+\*?\[\^\]\$\(\)\{\}\=\!\<\>\|\:\-]/g, "\\$&")})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
      }

      function createTableRow(item) {
        const row = document.createElement("tr");
        row.dataset.id = item.id;
        row.innerHTML = `
                    <td class="id-cell">${item.id}</td>
                    <td class="title-cell">${item.title}</td>
                    <td><div class="categories-cell">${(item.categories || []).map(cat => `<span class="category-pill">${cat}</span>`).join('')}</div></td>
                    <td class="description-cell" title="${item.description}">${item.description}</td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-icon" onclick="copyToClipboard(jsonData.find(i => i.id === ${item.id}).description)" title="کپی متن">${ICONS.copy}</button>
                            <button class="btn-icon" onclick="editItem(${item.id})" title="ویرایش">${ICONS.edit}</button>
                            <button class="btn-icon" onclick="deleteItem(${item.id})" title="حذف">${ICONS.delete}</button>
                        </div>
                    </td>
                `;
        return row;
      }

      function renderItems() {
        const loadingRow = document.getElementById('loading-row');
        if (loadingRow) loadingRow.style.display = 'none';
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
          if (searchValue.trim()) {
            const query = searchValue.trim();
            row.querySelector('.title-cell').innerHTML = highlightText(item.title, query);
            row.querySelector('.description-cell').innerHTML = highlightText(item.description, query);
            row.querySelectorAll('.category-pill').forEach(pill => {
              pill.innerHTML = highlightText(pill.textContent, query);
            });
          }
          itemListBody.appendChild(row);
        });
      }

      const openModal = () => {
        itemModal.style.display = "flex";
        document.body.style.overflow = 'hidden';
      };
      const closeModal = () => {
        itemModal.style.display = "none";
        document.body.style.overflow = '';
        itemForm.reset();
        document.getElementById("itemId").value = '';
      };

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

      window.editItem = (id) => {
        const item = jsonData.find(i => i.id === id);
        if (!item) return;
        document.getElementById("itemId").value = item.id;
        idInput.value = item.id;
        idInput.readOnly = true;
        titleInput.value = item.title;
        descriptionTextarea.value = item.description;
        modalTitle.textContent = "ویرایش پیام";
        renderCategoryCheckboxes(item.categories);
        currentStep = 1;
        updateStepUI();
        openModal();
      };

      window.deleteItem = (id) => {
        showConfirmation("آیا از حذف این پیام مطمئن هستید؟ این عملیات غیرقابل بازگشت است.", async () => {
          try {
            const result = await apiRequest('delete', {
              id
            });
            showToast(result.message || 'پیام با موفقیت حذف شد.', 'success');
            jsonData = jsonData.filter(item => item.id !== id);
            renderItems();
          } catch (error) {
            /* Error is handled by apiRequest */
          }
        });
      };

      addNewItemBtn.addEventListener("click", () => {
        itemForm.reset();
        document.getElementById("itemId").value = '';
        const maxId = jsonData.length > 0 ? Math.max(...jsonData.map(i => i.id || 0)) : 0;
        idInput.value = maxId + 1;
        idInput.readOnly = false;
        modalTitle.textContent = "افزودن پیام جدید";
        renderCategoryCheckboxes([]);
        currentStep = 1;
        updateStepUI();
        openModal();
      });

      itemForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        saveBtn.classList.add('loading');
        try {
          const editingItemId = parseInt(document.getElementById("itemId").value, 10);
          const isEditing = !!editingItemId;
          const selectedCategories = Array.from(categoriesCheckboxContainer.querySelectorAll('input:checked')).map(cb => cb.value);

          const itemData = {
            id: parseInt(idInput.value, 10),
            title: titleInput.value.trim(),
            categories: selectedCategories,
            description: descriptionTextarea.value.trim(),
          };

          if (isEditing) {
            const result = await apiRequest('update', itemData);
            showToast(result.message || 'پیام با موفقیت به‌روزرسانی شد.', 'success');
            const index = jsonData.findIndex(i => i.id === editingItemId);
            if (index !== -1) jsonData[index] = itemData;
          } else {
            const result = await apiRequest('create', itemData);
            showToast(result.message || 'پیام جدید با موفقیت ذخیره شد.', 'success');
            jsonData.push(itemData);
          }
          closeModal();
          renderItems();
        } catch (error) {
          /* Handled in apiRequest */
        } finally {
          saveBtn.classList.remove('loading');
        }
      });

      const updateStepUI = () => {
        steps.forEach(step => step.classList.toggle('active-step', parseInt(step.dataset.step) === currentStep));
        stepIndicator.textContent = `مرحله ${currentStep} از ${totalSteps}`;
        prevBtn.style.display = currentStep > 1 ? 'inline-flex' : 'none';
        nextBtn.style.display = currentStep < totalSteps ? 'inline-flex' : 'none';
        saveBtn.style.display = currentStep === totalSteps ? 'inline-flex' : 'none';
        if (currentStep === totalSteps) {
          saveBtn.innerHTML = `${ICONS.save} <span>ذخیره</span>`;
        }
      };

      const validateStep = (step) => {
        if (step === 1) {
          if (!idInput.value.trim() || !titleInput.value.trim()) {
            showToast("شناسه و عنوان پیام الزامی است.", "error");
            return false;
          }
          const isEditing = !!document.getElementById("itemId").value;
          if (!isEditing && jsonData.some(item => item.id === parseInt(idInput.value, 10))) {
            showToast("این شناسه قبلاً استفاده شده است.", "error");
            return false;
          }
        }
        if (step === 2) {
          const selectedCategories = Array.from(categoriesCheckboxContainer.querySelectorAll('input:checked')).length;
          if (selectedCategories === 0) {
            showToast("انتخاب حداقل یک دسته‌بندی الزامی است.", "error");
            return false;
          }
        }
        return true;
      };

      nextBtn.addEventListener('click', () => {
        if (validateStep(currentStep)) {
          currentStep++;
          updateStepUI();
        }
      });

      prevBtn.addEventListener('click', () => {
        currentStep--;
        updateStepUI();
      });

      async function loadInitialData() {
        const loadingRow = document.getElementById('loading-row');
        try {
          loadingRow.style.display = 'table-row';
          const response = await fetch(`${API_URL}?v=${new Date().getTime()}`);
          if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
          jsonData = await response.json();
          renderItems();
        } catch (error) {
          if (loadingRow) loadingRow.style.display = 'none';
          console.error("خطا در بارگذاری اولیه داده‌ها:", error);
          showToast("خطا در ارتباط با سرور.", "error");
        }
      }

      itemModal.addEventListener('click', (e) => {
        if (e.target === itemModal) closeModal();
      });
      cancelEditBtn.onclick = closeModal;
      searchInput.addEventListener("input", (e) => {
        searchValue = e.target.value;
        renderItems();
      });

      loadInitialData();
    });
  </script>
</body>

</html>
