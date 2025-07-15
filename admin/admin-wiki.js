let jsonData = [];
let currentItemIndex = -1;
let searchValue = "";

const itemListDiv = document.getElementById("item-list");
const itemModal = document.getElementById("itemModal");
const closeButton = document.querySelector(".close-button");
const itemForm = document.getElementById("itemForm");
const saveItemBtn = document.getElementById("save-item-btn");
const cancelEditBtn = document.getElementById("cancel-edit-btn");
const addNewItemBtn = document.getElementById("add-new-item-btn");
const fileInput = document.getElementById("file-input");
const copyJsonBtn = document.getElementById("copy-json-btn");
const modalTitle = document.getElementById("modalTitle");
const descriptionTextarea = document.getElementById("description-textarea");
const searchInput = document.getElementById("search-input");
const idInput = document.getElementById("id-input");
const copySingleJsonBtn = document.getElementById("copy-single-json-btn");
const categoriesInput = document.getElementById("categories-input");

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
  if (categoriesInput) categoriesInput.value = "";
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
    const q = searchValue.trim();
    filtered = jsonData.filter(
      (item) =>
        (item.title && item.title.includes(q)) ||
        (item.description && item.description.includes(q)) ||
        (item.categories &&
          Array.isArray(item.categories) &&
          item.categories.some((c) => c.includes(q))) ||
        (item.id && String(item.id).includes(q))
    );
  }
  if (filtered.length === 0) {
    itemListDiv.innerHTML =
      '<p style="text-align: center; margin-top: 50px; font-size: 1.2rem; color: #555;">موردی برای نمایش وجود ندارد. می‌توانید یک فایل JSON را بارگذاری یا پیام جدید اضافه کنید.</p>';
    return;
  }
  filtered.forEach((item, index) => {
    const card = document.createElement("div");
    card.classList.add("news-alert-box");
    // نمایش description با تبدیل \n به <br>
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
        <button class="edit-btn" data-index="${index}">ویرایش</button>
        <button class="delete-btn" data-index="${index}">حذف</button>
      </div>
    `;
    itemListDiv.appendChild(card);
  });
  document.querySelectorAll(".edit-btn").forEach((button) => {
    button.addEventListener("click", (e) => {
      e.stopPropagation();
      const index = parseInt(e.target.dataset.index);
      editItem(index);
    });
  });
  document.querySelectorAll(".delete-btn").forEach((button) => {
    button.addEventListener("click", (e) => {
      e.stopPropagation();
      const index = parseInt(e.target.dataset.index);
      deleteItem(index);
    });
  });
}
// جستجو
searchInput.addEventListener("input", (e) => {
  searchValue = e.target.value;
  renderItems();
});

// Edit
function editItem(index) {
  currentItemIndex = index;
  const item = jsonData[index];
  document.getElementById("itemId").value = index;
  idInput.value = item.id || "";
  document.getElementById("title").value = item.title || "";
  categoriesInput.value = (item.categories || []).join(", ");
  descriptionTextarea.value = item.description || "";
  modalTitle.textContent = "ویرایش پیام";
  openModal();
}
// Add
addNewItemBtn.addEventListener("click", () => {
  currentItemIndex = -1;
  itemForm.reset();
  descriptionTextarea.value = "";
  categoriesInput.value = "";
  // مقدار id را مقدار پیشفرض (id بزرگ‌تر +۱) ست کن:
  const maxId =
    jsonData.length > 0 ? Math.max(...jsonData.map((i) => i.id || 0)) : 0;
  idInput.value = maxId + 1;
  modalTitle.textContent = "افزودن پیام جدید";
  openModal();
});
// Save
itemForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const newItem = {
    id: parseInt(idInput.value, 10),
    title: document.getElementById("title").value,
    categories: categoriesInput.value
      .split(",")
      .map((s) => s.trim())
      .filter(Boolean),
    description: descriptionTextarea.value,
  };
  // چک کردن اینکه id تکراری نباشد
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
  alert(
    'تغییرات اعمال شد. برای ذخیره نهایی، دکمه "کپی JSON در کلیپ‌بورد" را بزنید و در فایل اصلی خود جایگذاری کنید.'
  );
});
// Delete
function deleteItem(index) {
  if (confirm("آیا مطمئن هستید که می‌خواهید این پیام را حذف کنید؟")) {
    jsonData.splice(index, 1);
    renderItems();
    alert(
      'پیام حذف شد. برای ذخیره نهایی، دکمه "کپی JSON در کلیپ‌بورد" را بزنید و در فایل اصلی خود جایگذاری کنید.'
    );
  }
}
// بارگذاری فایل
fileInput.addEventListener("change", (event) => {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      try {
        jsonData = JSON.parse(e.target.result);
        renderItems();
        alert(
          "فایل JSON با موفقیت بارگذاری شد. حالا می‌توانید آن را ویرایش کنید."
        );
      } catch (parseError) {
        alert(
          "خطا در خواندن یا تجزیه فایل JSON. لطفا مطمئن شوید که فایل یک JSON معتبر است."
        );
        console.error("Error parsing JSON:", parseError);
      }
    };
    reader.readAsText(file);
  }
});
// کپی JSON کل لیست
copyJsonBtn.addEventListener("click", () => {
  const jsonToCopy = JSON.stringify(jsonData, null, 2);
  navigator.clipboard
    .writeText(jsonToCopy)
    .then(() => {
      alert(
        "محتوای JSON به کلیپ‌بورد کپی شد. حالا می‌توانید آن را در فایل خود جایگذاری کنید."
      );
    })
    .catch((err) => {
      console.error("Failed to copy JSON to clipboard: ", err);
      alert("خطا در کپی کردن به کلیپ‌بورد. لطفا به صورت دستی کپی کنید.");
    });
});

// کپی فقط پیام فعلی به صورت JSON
copySingleJsonBtn.addEventListener("click", function () {
  // اطلاعات فعلی فرم را بخوان (چه در حالت افزودن، چه ویرایش)
  const item = {
    id: parseInt(idInput.value, 10),
    title: document.getElementById("title").value,
    categories: categoriesInput.value
      .split(",")
      .map((s) => s.trim())
      .filter(Boolean),
    description: descriptionTextarea.value,
  };
  const singleJson = JSON.stringify(item, null, 2);
  navigator.clipboard
    .writeText(singleJson)
    .then(() => {
      alert("پیام فعلی به صورت JSON در کلیپ‌بورد کپی شد!");
    })
    .catch((err) => {
      alert("خطا در کپی کردن به کلیپ‌بورد.");
    });
});

// بارگذاری اولیه
async function loadInitialJson() {
  try {
    const response = await fetch("/data/wiki.json");
    if (!response.ok) {
      if (response.status === 404) {
        console.warn(
          "scripts-messages.json not found. Starting with empty data."
        );
        jsonData = [];
      } else {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
    } else {
      jsonData = await response.json();
      console.log("JSON loaded successfully on page load.");
    }
  } catch (error) {
    console.error("Error loading scripts-messages.json:", error);
    alert(
      "خطا در بارگذاری اولیه فایل JSON. ممکن است فایل وجود نداشته باشد یا خراب باشد."
    );
    jsonData = [];
  } finally {
    renderItems();
  }
}
document.addEventListener("DOMContentLoaded", () => {
  loadInitialJson();
});
