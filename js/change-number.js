// ===== تبدیل اعداد فارسی (۰–۹) به اعداد انگلیسی (0–9) =====
function normalizeDigits(text) {
  return text.replace(/[۰-۹]/g, (d) => "۰۱۲۳۴۵۶۷۸۹".indexOf(d).toString());
}

// ===== فقط اعداد را بپذیر و شماره موبایل را به فرمت 0912 345 6789 تبدیل کن =====
function formatMobileNumber(input) {
  let value = input.value.replace(/[^\d]/g, "");
  value = value.slice(0, 11);
  if (value.length >= 8) {
    input.value = value.replace(/(\d{4})(\d{3})(\d{0,4})/, "$1 $2 $3").trim();
  } else if (value.length >= 5) {
    input.value = value.replace(/(\d{4})(\d{0,3})/, "$1 $2").trim();
  } else {
    input.value = value;
  }
  return value;
}

// ===== کد ملی را به صورت 509 027231 1 فرمت کن =====
function formatNationalId(input) {
  let value = input.value.replace(/[^\d]/g, "").slice(0, 10);
  if (value.length >= 10) {
    input.value = value.replace(/(\d{3})(\d{6})(\d{1})/, "$1 $2 $3");
  } else if (value.length > 3) {
    input.value = value.replace(/(\d{3})(\d{0,6})/, "$1 $2");
  } else {
    input.value = value;
  }
  return value;
}

// ===== رویداد برای ورودی شماره قبلی: فرمت، اعتبارسنجی و به‌روزرسانی دکمه کپی =====
document
  .getElementById("oldNumberInput")
  .addEventListener("input", function (e) {
    const pureValue = formatMobileNumber(e.target);
    validatePhone(e.target, "oldNumberMessage", pureValue);
    updateCopyButton();
  });

// ===== رویداد برای ورودی شماره جدید: فرمت، اعتبارسنجی و به‌روزرسانی دکمه کپی =====
document
  .getElementById("newNumberInput")
  .addEventListener("input", function (e) {
    const pureValue = formatMobileNumber(e.target);
    validatePhone(e.target, "newNumberMessage", pureValue);
    updateCopyButton();
  });

// ===== رویداد برای ورودی کد ملی: فرمت، اعتبارسنجی و آپدیت استان/شهر =====
document
  .getElementById("nationalIdInput")
  .addEventListener("input", function (e) {
    const pureValue = formatNationalId(e.target);
    validateNationalId(e.target, "nationalIdMessage", pureValue);
    // نمایش استان/شهر بر اساس سه رقم اول کدملی
    updateProvinceDisplay(pureValue);
    updateCopyButton();
  });

// ===== جلوگیری از وارد کردن عدد در فیلد نام =====
document.getElementById("nameInput").addEventListener("input", function (e) {
  let newValue = e.target.value.replace(/[0-9۰-۹]/g, "");
  if (e.target.value !== newValue) {
    e.target.value = newValue;
  }
  updateCopyButton();
});

// ===== اعتبارسنجی شماره موبایل (عدد ۱۱ رقمی که با ۰۹ شروع می‌شود) =====
function validatePhone(input, msgId, pureValue) {
  const msgBox = document.getElementById(msgId);
  input.classList.remove("input-error");
  msgBox.className = "input-message";
  msgBox.innerText = "";
  let val =
    pureValue !== undefined ? pureValue : input.value.replace(/[^\d]/g, "");
  if (val && !/^09\d{9}$/.test(val)) {
    msgBox.innerText = "شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.";
    msgBox.classList.add("error");
    input.classList.add("input-error");
    return false;
  }
  return true;
}

// ===== الگوریتم اعتبارسنجی کد ملی ایران =====
function isValidIranianNationalCode(input) {
  if (!/^\d{10}$/.test(input)) return false;
  if (/^(\d)\1{9}$/.test(input)) return false;
  let check = +input[9];
  let sum = 0;
  for (let i = 0; i < 9; ++i) {
    sum += +input[i] * (10 - i);
  }
  let rem = sum % 11;
  return (rem < 2 && check == rem) || (rem >= 2 && check == 11 - rem);
}

// ===== اعتبارسنجی کد ملی =====
function validateNationalId(input, msgId, pureValue) {
  const msgBox = document.getElementById(msgId);
  input.classList.remove("input-error");
  msgBox.className = "input-message";
  msgBox.innerText = "";
  let val =
    pureValue !== undefined ? pureValue : input.value.replace(/[^\d]/g, "");
  if (val && !/^\d{10}$/.test(val)) {
    msgBox.innerText = "کدملی باید ۱۰ رقم باشد.";
    msgBox.classList.add("error");
    input.classList.add("input-error");
    return false;
  }
  if (val && !isValidIranianNationalCode(val)) {
    msgBox.innerText = "کدملی وارد شده معتبر نیست.";
    msgBox.classList.add("error");
    input.classList.add("input-error");
    return false;
  }
  if (val && isValidIranianNationalCode(val)) {
    msgBox.innerText = "کدملی معتبر است.";
    msgBox.classList.add("success");
  }
  return true;
}

// ===== جلوگیری از تکراری بودن شماره قبلی و جدید =====
function checkDuplicateNumbers() {
  const oldNumber = document
    .getElementById("oldNumberInput")
    .value.replace(/[^\d]/g, "");
  const newNumber = document
    .getElementById("newNumberInput")
    .value.replace(/[^\d]/g, "");
  const msgBox = document.getElementById("newNumberMessage");
  if (oldNumber && newNumber && oldNumber === newNumber) {
    msgBox.innerText = "شماره قبلی و شماره جدید نباید یکسان باشند.";
    msgBox.className = "input-message error";
    document.getElementById("newNumberInput").classList.add("input-error");
    return false;
  }
  // اگر قبلا خطا بوده و حالا درست شد
  if (msgBox.innerText === "شماره قبلی و شماره جدید نباید یکسان باشند.") {
    msgBox.innerText = "";
    msgBox.className = "input-message";
    document.getElementById("newNumberInput").classList.remove("input-error");
  }
  return true;
}

// ===== فعال یا غیرفعال کردن دکمه کپی با توجه به اعتبار داده‌ها =====
function updateCopyButton() {
  const newNumberInput = document.getElementById("newNumberInput");
  const nationalIdInput = document.getElementById("nationalIdInput");

  const newNumValid = validatePhone(newNumberInput, "newNumberMessage");
  const nationalIdValid = validateNationalId(
    nationalIdInput,
    "nationalIdMessage"
  );
  const duplicateCheck = checkDuplicateNumbers();

  // فقط اگر شماره جدید و کدملی پر و معتبر باشند و شماره‌ها تکراری نباشند، دکمه فعال شود
  const btn = document.getElementById("copyBtn");
  if (
    newNumberInput.value.trim() &&
    newNumValid &&
    nationalIdInput.value.trim() &&
    nationalIdValid &&
    duplicateCheck
  ) {
    btn.disabled = false;
  } else {
    btn.disabled = true;
  }
}

// ===== کپی اطلاعات فرم در قالب متن آماده برای ارسال =====
function copyRequest() {
  const name = document.getElementById("nameInput").value.trim();
  const oldNumber = document
    .getElementById("oldNumberInput")
    .value.replace(/[^\d]/g, "");
  const nationalId = document
    .getElementById("nationalIdInput")
    .value.replace(/[^\d]/g, "");
  const newNumber = document
    .getElementById("newNumberInput")
    .value.replace(/[^\d]/g, "");

  const text =
    "با سلام\n" +
    "کاربر: " +
    (name ? name : "جای خالی") +
    "\n" +
    "با شماره موبایل: " +
    (oldNumber ? oldNumber : "جای خالی") +
    "\n" +
    "و کدملی: " +
    (nationalId ? nationalId : "جای خالی") +
    "\n" +
    "درخواست تغییر شماره به: " +
    (newNumber ? newNumber : "جای خالی") +
    " را دارند.";

  navigator.clipboard.writeText(text).then(
    () => {
      const res = document.getElementById("copyResult");
      res.innerText = "✅ متن با موفقیت کپی شد!";
      res.style.display = "block";
      setTimeout(() => {
        res.style.display = "none";
      }, 2000);
    },
    () => {
      const res = document.getElementById("copyResult");
      res.innerText = "❌ مشکلی در کپی کردن متن رخ داد.";
      res.style.display = "block";
    }
  );
}

// ===== پاک کردن فرم و ریست همه پیام‌ها و خطاها =====
function clearForm() {
  document.getElementById("autoFillInput").value = "";
  document.getElementById("nameInput").value = "";
  document.getElementById("oldNumberInput").value = "";
  document.getElementById("nationalIdInput").value = "";
  document.getElementById("newNumberInput").value = "";

  document.getElementById("oldNumberMessage").innerText = "";
  document.getElementById("oldNumberMessage").className = "input-message";
  document.getElementById("nationalIdMessage").innerText = "";
  document.getElementById("nationalIdMessage").className = "input-message";
  document.getElementById("newNumberMessage").innerText = "";
  document.getElementById("newNumberMessage").className = "input-message";

  // مخفی کردن باکس استان و شهر
  const provinceDiv = document.getElementById("provinceDisplay");
  provinceDiv.style.display = "none";
  provinceDiv.innerText = "";

  document.getElementById("copyResult").innerText = "";
  document.getElementById("copyResult").style.display = "none";

  document.getElementById("oldNumberInput").classList.remove("input-error");
  document.getElementById("nationalIdInput").classList.remove("input-error");
  document.getElementById("newNumberInput").classList.remove("input-error");

  updateCopyButton();
}

// ===== بررسی اولیه وضعیت دکمه کپی (در ابتدای بارگذاری) =====
updateCopyButton();

// ===== بارگذاری دیتابیس استان و شهر بر اساس کدملی (JSON) =====
let cityMapping = {};

fetch("data/NationalCode.json")
  .then((res) => res.json())
  .then((data) => {
    cityMapping = data;
  })
  .catch((err) => {
    console.error("خطا در بارگذاری cityMapping.json:", err);
  });

// ===== تابع به‌روزرسانی نمایش استان و شهر بر اساس سه رقم اول کدملی =====
function updateProvinceDisplay(pureValue) {
  const provinceDiv = document.getElementById("provinceDisplay");

  // اگر کمتر از ۳ رقم است، هیچ چیزی نشان نده
  if (!pureValue || pureValue.length < 3) {
    provinceDiv.style.display = "none";
    provinceDiv.innerText = "";
    return;
  }

  // سه رقم اول را جدا کن
  const prefix = pureValue.slice(0, 3);

  // اگر در mapping باشد، نام استان و شهر را بنویس
  if (cityMapping.hasOwnProperty(prefix)) {
    const prov = cityMapping[prefix].province;
    const city = cityMapping[prefix].city;
    provinceDiv.innerText =
      "استان محل صدور: " + prov + " | شهر محل صدور: " + city;
  } else {
    // اگر کد سه رقمی در mapping نبود
    provinceDiv.innerText = "❌ این کدملی در ثبت احوال وجود ندارد";
  }

  provinceDiv.style.display = "block";
}

// ===== تکمیل خودکار فرم از روی متن درخواست (با تشخیص اعداد فارسی و انگلیسی) =====
function autoFillForm() {
  // ۱. مقدار textarea را بگیریم و اعداد فارسی را به انگلیسی تبدیل کنیم
  const rawText = document.getElementById("autoFillInput").value.trim();
  if (!rawText) {
    alert("لطفاً ابتدا متن درخواست را وارد کنید.");
    return;
  }
  const text = normalizeDigits(rawText);

  // ۲. پاک‌سازی کامل فیلدهای ورودی و پیام‌ها
  const oldInput = document.getElementById("oldNumberInput");
  oldInput.value = "";
  oldInput.classList.remove("input-error");
  const oldMsg = document.getElementById("oldNumberMessage");
  oldMsg.innerText = "";
  oldMsg.className = "input-message";

  const newInput = document.getElementById("newNumberInput");
  newInput.value = "";
  newInput.classList.remove("input-error");
  const newMsg = document.getElementById("newNumberMessage");
  newMsg.innerText = "";
  newMsg.className = "input-message";

  const nationalInput = document.getElementById("nationalIdInput");
  nationalInput.value = "";
  nationalInput.classList.remove("input-error");
  const nationalMsg = document.getElementById("nationalIdMessage");
  nationalMsg.innerText = "";
  nationalMsg.className = "input-message";

  const provinceDiv = document.getElementById("provinceDisplay");
  provinceDiv.style.display = "none";
  provinceDiv.innerText = "";

  // ۳. جستجوی همه شماره‌های موبایل در متن (11 رقمی با 09)
  const phoneMatches = text.match(/09\d{9}/g) || [];

  // ۴. جستجوی کد ملی‌های 10 رقمی
  const nationalIdMatches = text.match(/\b\d{10}\b/g) || [];

  // ۵. مقداردهی خودکار شماره قبلی و جدید
  if (phoneMatches.length === 1) {
    newInput.value = phoneMatches[0];
    formatMobileNumber(newInput);
    validatePhone(newInput, "newNumberMessage", phoneMatches[0]);
  } else if (phoneMatches.length >= 2) {
    oldInput.value = phoneMatches[0];
    formatMobileNumber(oldInput);
    validatePhone(oldInput, "oldNumberMessage", phoneMatches[0]);

    newInput.value = phoneMatches[1];
    formatMobileNumber(newInput);
    validatePhone(newInput, "newNumberMessage", phoneMatches[1]);
  }
  // اگر هیچ شماره‌ای نبود، هیچ کاری انجام نشود

  // ۶. مقداردهی خودکار کدملی (در صورت وجود)
  if (nationalIdMatches.length >= 1) {
    nationalInput.value = nationalIdMatches[0];
    formatNationalId(nationalInput);
    validateNationalId(
      nationalInput,
      "nationalIdMessage",
      nationalIdMatches[0]
    );
    updateProvinceDisplay(nationalIdMatches[0]);
  }

  // ۷. به‌روزرسانی وضعیت دکمه کپی
  updateCopyButton();
}
