<?php
require __DIR__ . '/../php/auth_check.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت وضعیت سرویس‌ها</title>
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
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        align-items: start;
      }
      .column {
        background: transparent;
      }
      h2 {
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
        color: #00ae70;
        text-align: center;
        font-weight: 700;
      }
      ul {
        list-style: none;
        padding: 0;
        margin: 0;
      }
      li {
        background-color: #ffffff;
        border-radius: 0.75rem;
        padding: 1.2rem 1.5rem;
        margin-bottom: 1.2rem;
        box-shadow: 0 2px 12px rgba(0, 174, 112, 0.07);
        border-right: 4px solid #00ae70;
        transition: all 0.25s;
      }
      li:hover {
        transform: translateY(-3px);
        border-right-color: #089863;
        box-shadow: 0 6px 20px rgba(0, 174, 112, 0.12);
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
        .column {
          width: 100%;
        }
      }
      @media (max-width: 480px) {
        main {
          padding: 1rem;
        }
        a {
          font-size: 1rem;
        }
        li {
          padding: 1rem 1.2rem;
        }
        #today-date,
        #current-time {
          display: none;
        }
      }
      .news-alert-box {
        background: #eafff4;
        padding: 1.2rem 1.5rem;
        margin-bottom: 0.5rem;
        border-radius: 0.75rem;
        border-right: 4px solid;
        transition: background 0.3s, border-color 0.3s;
        font-size: 1rem;
        box-shadow: 0 2px 12px rgba(0, 174, 112, 0.07);
        position: relative;
      }
      .news-alert-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 174, 112, 0.12);
      }
      .news-alert-box.فعال {
        background-color: #eafff4;
        border-right-color: #00ae70;
      }
      .news-alert-box.غیرفعال {
        background-color: #fff0f3;
        border-right-color: #ff0040;
      }
      .news-alert-box.اختلال-در-عملکرد {
        background-color: #fff6e5;
        border-right-color: #ffa500;
      }
      .news-alert-box.unknown-status {
        background-color: #f0f0f0;
        border-right-color: #999;
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
      .modal-content select {
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
      .modal-content select:focus {
        border-color: #00ae70;
        outline: none;
      }
      .modal-content .description-editor {
        width: 100%;
        min-height: 150px;
        padding: 10px 12px;
        margin-bottom: 18px;
        border: 1px solid #ccc;
        border-radius: 0.5rem;
        font-size: 1rem;
        box-sizing: border-box;
        background-color: #fcfcfc;
        transition: border-color 0.2s;
        overflow-y: auto;
        line-height: 1.6;
      }
      .modal-content .description-editor:focus {
        border-color: #00ae70;
        outline: none;
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
        margin-bottom: 25px;
        display: block;
        width: fit-content;
        box-shadow: 0 4px 10px rgba(0, 174, 112, 0.2);
        transition: background-color 0.2s, transform 0.2s;
      }
      #add-new-item-btn:hover {
        background-color: #089863;
        transform: translateY(-2px);
      }
      .item-card .actions {
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
      .button-group-top {
        display: flex;
        justify-content: flex-start;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 25px;
      }
      .editor-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
        padding: 10px;
        background-color: #eaf4f1;
        border-radius: 0.5rem;
        border: 1px solid #d4e0db;
      }
      .editor-toolbar button {
        background-color: #00ae70;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.95rem;
        transition: background-color 0.2s;
      }
      .editor-toolbar button:hover {
        background-color: #089863;
      }
      .editor-toolbar button:active {
        background-color: #007c52;
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
    </style>
  </head>
  <body>
    <div id="header-placeholder"></div>
    <main>
      <div class="button-group-top">
        <button id="add-new-item-btn">➕ افزودن سرویس جدید</button>
      </div>

      <div id="item-list" class="main-content"></div>
      <a href="/admin/index.php" class="back-link">بازگشت به بخش مدیریت</a>
    </main>

    <div id="itemModal" class="modal">
      <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2
          id="modalTitle"
          style="text-align: center; margin-bottom: 25px"
        ></h2>
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

          <label for="description">توضیحات:</label>
          <div class="editor-toolbar">
            <button type="button" data-command="bold">Bold</button>
            <button type="button" data-command="italic">Italic</button>
            <button type="button" data-command="insertUnorderedList">
              List
            </button>
            <button type="button" data-command="insertHTML" data-value="<br>">
              New Line
            </button>
          </div>
          <div
            id="description-editor"
            class="description-editor"
            contenteditable="true"
            role="textbox"
            aria-multiline="true"
          ></div>

          <div class="button-group">
            <button type="submit" id="save-item-btn">ذخیره</button>
            <button type="button" id="cancel-edit-btn">لغو</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      let jsonData = [];
      let currentItemIndex = -1;

      const itemListDiv = document.getElementById("item-list");
      const itemModal = document.getElementById("itemModal");
      const closeButton = document.querySelector(".close-button");
      const itemForm = document.getElementById("itemForm");
      const saveItemBtn = document.getElementById("save-item-btn");
      const cancelEditBtn = document.getElementById("cancel-edit-btn");
      const addNewItemBtn = document.getElementById("add-new-item-btn");
      const modalTitle = document.getElementById("modalTitle");
      const descriptionEditor = document.getElementById("description-editor");
      const editorToolbar = document.querySelector(".editor-toolbar");

      // --- تابع جدید برای ذخیره اطلاعات در سرور ---
      async function saveDataToServer() {
        try {
          const response = await fetch("/data/save-service-status.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(jsonData, null, 2),
          });
          const result = await response.json();
          if (response.ok && result.success) {
            console.log(result.message);
            // میتوانید یک نوتیفیکیشن موفقیت آمیز اینجا نمایش دهید
          } else {
            throw new Error(result.message || "خطای ناشناخته در سرور");
          }
        } catch (error) {
          console.error("Error saving data:", error);
          alert("خطا در ذخیره اطلاعات: " + error.message);
        }
      }

      function openModal() {
        itemModal.style.display = "block";
        descriptionEditor && descriptionEditor.focus();
      }

      function closeModal() {
        itemModal.style.display = "none";
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
          itemListDiv.innerHTML =
            '<p style="text-align:center;margin-top:50px;font-size:1.2rem;color:#555;">سرویسی برای نمایش وجود ندارد. برای شروع، یک سرویس جدید اضافه کنید.</p>';
          return;
        }
        jsonData.forEach((item, index) => {
          const card = document.createElement("div");
          const statusClass = item.status
            ? item.status.replace(/\s/g, "-")
            : "unknown-status";
          card.classList.add("news-alert-box", statusClass);
          card.innerHTML = `
            <h3>${item.name}</h3>
            <p><strong>وضعیت:</strong> ${item.status}</p>
            <p>${item.description || "بدون توضیحات"}</p>
            <div class="actions">
              <button class="edit-btn" data-index="${index}">ویرایش</button>
              <button class="delete-btn" data-index="${index}">حذف</button>
            </div>
          `;
          itemListDiv.appendChild(card);
        });

        document.querySelectorAll(".edit-btn").forEach((btn) =>
          btn.addEventListener("click", (e) => {
            e.stopPropagation();
            editItem(+e.target.dataset.index);
          })
        );
        document.querySelectorAll(".delete-btn").forEach((btn) =>
          btn.addEventListener("click", (e) => {
            e.stopPropagation();
            deleteItem(+e.target.dataset.index);
          })
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

      // --- رویداد فرم با قابلیت ذخیره خودکار ---
      itemForm.addEventListener("submit", (e) => {
        e.preventDefault();
        let desc = descriptionEditor.innerHTML.trim();
        if (!descriptionEditor.textContent.trim() || desc === "<br>") {
          desc = "";
        }
        const newItem = {
          name: document.getElementById("name").value,
          status: document.getElementById("status").value,
          description: desc,
        };
        if (currentItemIndex === -1) {
          jsonData.push(newItem);
        } else {
          jsonData[currentItemIndex] = newItem;
        }
        renderItems();
        closeModal();
        saveDataToServer(); // ذخیره خودکار
      });

      // --- تابع حذف با قابلیت ذخیره خودکار ---
      function deleteItem(index) {
        if (confirm("آیا مطمئن هستید که می‌خواهید این سرویس را حذف کنید؟")) {
          jsonData.splice(index, 1);
          renderItems();
          saveDataToServer(); // ذخیره خودکار
        }
      }

      editorToolbar.addEventListener("click", (event) => {
        const cmd = event.target.dataset.command;
        const val = event.target.dataset.value;
        if (cmd) {
          document.execCommand(cmd, false, val);
          descriptionEditor.focus();
        }
      });

      descriptionEditor.addEventListener("input", () => {
        if (!descriptionEditor.textContent.trim()) {
          descriptionEditor.innerHTML = "";
        }
      });

      // --- بارگذاری اولیه اطلاعات از فایل JSON ---
      async function loadInitialJson() {
        try {
          const res = await fetch(
            `/data/service-status.json?v=${new Date().getTime()}`
          );
          if (res.ok) {
            jsonData = await res.json();
          } else if (res.status === 404) {
            jsonData = [];
          } else {
            throw new Error(`HTTP error! status: ${res.status}`);
          }
        } catch (err) {
          console.error(err);
          alert(
            "خطا در بارگذاری اولیه فایل JSON. ممکن است فایل وجود نداشته باشد یا خراب باشد."
          );
          jsonData = [];
        } finally {
          renderItems();
        }
      }

      document.addEventListener("DOMContentLoaded", loadInitialJson);
    </script>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js"></script>
  </body>
</html>
