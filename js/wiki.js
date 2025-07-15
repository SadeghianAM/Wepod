function toPersianDigits(num) {
  return num.toString().replace(/\d/g, (d) => "۰۱۲۳۴۵۶۷۸۹"[d]);
}

let data = [];

const searchInput = document.getElementById("search-input");
const categoryFilter = document.getElementById("category-filter");
const resultsContainer = document.getElementById("results-container");

fetch("/data/wiki.json")
  .then((res) => res.json())
  .then((json) => {
    data = json;
    populateCategories(json);
    searchInput.placeholder = `جستجو از میان ${toPersianDigits(
      data.length
    )} متن آماده`;
  });

searchInput.addEventListener("input", filterResults);
categoryFilter.addEventListener("change", filterResults);

function populateCategories(data) {
  // همه دسته‌بندی‌های یکتا از آرایه categories هر آیتم
  const categoriesSet = new Set();
  data.forEach((item) => {
    (item.categories || []).forEach((cat) => categoriesSet.add(cat));
  });
  const categories = Array.from(categoriesSet);
  categories.forEach((cat) => {
    const option = document.createElement("option");
    option.value = cat;
    option.textContent = cat;
    categoryFilter.appendChild(option);
  });
}

function filterResults() {
  const term = searchInput.value.trim().toLowerCase();
  // گرفتن دسته‌بندی‌های انتخاب‌شده (چندگانه)
  const selectedCategories = Array.from(categoryFilter.selectedOptions)
    .map((opt) => opt.value)
    .filter((v) => v); // فقط گزینه‌های پر شده

  resultsContainer.innerHTML = "";

  const filtered = data.filter((item) => {
    const matchesTerm =
      item.title.toLowerCase().includes(term) ||
      item.description.toLowerCase().includes(term) ||
      (item.id && item.id.toString().includes(term));
    const matchesCategory =
      !selectedCategories.length ||
      (item.categories &&
        item.categories.some((cat) => selectedCategories.includes(cat)));
    return matchesTerm && matchesCategory;
  });

  if (filtered.length === 0) {
    resultsContainer.innerHTML =
      '<div class="no-result">نتیجه‌ای یافت نشد.</div>';
    return;
  }

  filtered.forEach((item) => {
    const box = document.createElement("div");
    box.className = "result-item";
    box.innerHTML = `
      <div class="result-title">${item.title}</div>
      <div class="result-category">دسته‌ها: ${(item.categories || []).join(
        "، "
      )}</div>
      <div class="result-id">آیدی: ${toPersianDigits(item.id)}</div>
      <div class="result-desc">${item.description.replace(/\n/g, "<br>")}</div>
    `;

    box.addEventListener("click", () => {
      navigator.clipboard
        .writeText(item.description)
        .then(() => {
          showToast("محتوا کپی شد!");
          box.style.backgroundColor = "#e6fff4";
          setTimeout(() => {
            box.style.backgroundColor = "#ffffff";
          }, 800);
        })
        .catch((err) => {
          console.error("خطا در کپی:", err);
          showToast("کپی نشد!");
        });
    });

    resultsContainer.appendChild(box);
  });
}

function showToast(message = "کپی شد!") {
  const toast = document.getElementById("toast");
  toast.textContent = message;
  toast.classList.add("show");
  toast.style.display = "block";
  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => {
      toast.style.display = "none";
    }, 400);
  }, 2000);
}
