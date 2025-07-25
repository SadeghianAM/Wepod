let jsonData = [];
let currentItemIndex = -1;
let searchValue = "";

// دسته‌بندی‌های از پیش تعریف شده
const availableCategories = [
  "عمومی",
  "احراز هویت",
  "اعتبار سنجی",
  "تنظیمات امنیت حساب",
  "تغییر شماره تلفن همراه",
  "عدم دریافت پیامک",
  "کارت فیزیکی",
  "کارت و حساب دیجیتال",
  "مسدودی و رفع مسدودی حساب",
  "انتقال وجه",
  "خدمات قبض",
  "شارژ و بسته اینترنت",
  "تسهیلات برآیند",
  "تسهیلات برآیند چک یار",
  "تسهیلات پشتوانه",
  "تسهیلات پیش درآمد",
  "تسهیلات پیمان",
  "تسهیلات تکلیفی",
  "تسهیلات سازمانی",
  "بیمه پاسارگاد",
  "چک",
  "خدمات چکاد",
  "صندوق های سرمایه گذاری",
  "طرح سرمایه گذاری رویش",
  "دعوت از دوستان",
  "هدیه دیجیتال",
  "وی کلاب",
];

const itemListDiv = document.getElementById("item-list");
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

// --- تابع جدید برای ذخیره خودکار در سرور ---
async function saveDataToServer() {
  try {
    const response = await fetch("/data/save-wiki.php", {
      // ❗️ آدرس فایل PHP جدید
      method: "POST",
      headers: { "Content-Type": "application/json" },
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

// Modal functions
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
  if (categoriesCheckboxContainer) categoriesCheckboxContainer.innerHTML = "";
}

closeButton.onclick = closeModal;
window.onclick = function (event) {
  if (event.target == itemModal) closeModal();
};
cancelEditBtn.onclick = closeModal;

// Render items with search filter
function renderItems() {
  itemListDiv.innerHTML = "";
  let filtered = jsonData;
  if (searchValue.trim()) {
    const q = searchValue.trim().toLowerCase();
    filtered = jsonData.filter(
      (item) =>
        (item.title && item.title.toLowerCase().includes(q)) ||
        (item.description && item.description.toLowerCase().includes(q)) ||
        (item.categories &&
          Array.isArray(item.categories) &&
          item.categories.some((c) => c.toLowerCase().includes(q))) ||
        (item.id && String(item.id).includes(q))
    );
  }
  if (filtered.length === 0) {
    itemListDiv.innerHTML =
      '<p style="text-align: center; margin-top: 50px; font-size: 1.2rem; color: #555;">موردی برای نمایش وجود ندارد.</p>';
    return;
  }

  // مرتب‌سازی بر اساس ID
  filtered.sort((a, b) => (a.id > b.id ? 1 : -1));

  filtered.forEach((item) => {
    // پیدا کردن ایندکس واقعی آیتم در آرایه اصلی برای ویرایش و حذف
    const originalIndex = jsonData.findIndex(
      (originalItem) => originalItem.id === item.id
    );

    const card = document.createElement("div");
    card.classList.add("news-alert-box");
    const descriptionHtml = (item.description || "").replace(/\n/g, "<br>");
    card.innerHTML = `
      <h3 style="margin-bottom:6px">
        ${item.title || "بدون عنوان"}
        <span style="font-size:0.92rem; color:#999; margin-right:7px;">[ID: ${
          item.id || "-"
        }]</span>
      </h3>
      <p style="font-size:0.96rem; color:#7c7c7c; margin:0 0 7px 0;"><strong>دسته‌بندی‌ها:</strong> ${
        item.categories && item.categories.length
          ? item.categories.join("، ")
          : "-"
      }</p>
      <div style="margin-bottom:10px">${descriptionHtml}</div>
      <div class="actions">
        <button class="edit-btn" data-index="${originalIndex}">ویرایش</button>
        <button class="delete-btn" data-index="${originalIndex}">حذف</button>
      </div>
    `;
    itemListDiv.appendChild(card);
  });

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

// Search
searchInput.addEventListener("input", (e) => {
  searchValue = e.target.value;
  renderItems();
});

// Category Checkbox functions
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

// Edit
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

// Add
addNewItemBtn.addEventListener("click", () => {
  currentItemIndex = -1;
  itemForm.reset();
  descriptionTextarea.value = "";
  const maxId =
    jsonData.length > 0 ? Math.max(...jsonData.map((i) => i.id || 0)) : 0;
  idInput.value = maxId + 1;
  modalTitle.textContent = "افزودن پیام جدید";
  renderCategoryCheckboxes([]);
  openModal();
});

// --- فرم ذخیره با قابلیت ذخیره خودکار ---
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
  renderItems();
  closeModal();
  saveDataToServer(); // 🚀 ذخیره خودکار
});

// --- تابع حذف با قابلیت ذخیره خودکار ---
function deleteItem(index) {
  if (confirm("آیا مطمئن هستید که می‌خواهید این پیام را حذف کنید؟")) {
    jsonData.splice(index, 1);
    renderItems();
    saveDataToServer(); // 🚀 ذخیره خودکار
  }
}

// --- بارگذاری اولیه JSON ---
async function loadInitialJson() {
  try {
    const response = await fetch(`/data/wiki.json?v=${new Date().getTime()}`);
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
    renderItems();
  }
}

document.addEventListener("DOMContentLoaded", loadInitialJson);
