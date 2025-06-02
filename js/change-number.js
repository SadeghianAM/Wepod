// تبدیل اعداد فارسی (۰–۹) به اعداد انگلیسی (0–9)
function normalizeDigits(text) {
  return text.replace(/[۰-۹]/g, (d) => "۰۱۲۳۴۵۶۷۸۹".indexOf(d).toString());
}

// فقط اعداد را بپذیر و شماره موبایل را به صورت 0912 345 6789 فرمت کن
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

// کد ملی را به صورت 509 027231 1 فرمت کن
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

// شماره قبلی
document
  .getElementById("oldNumberInput")
  .addEventListener("input", function (e) {
    const pureValue = formatMobileNumber(e.target);
    validatePhone(e.target, "oldNumberMessage", pureValue);
    updateCopyButton();
  });

// شماره جدید
document
  .getElementById("newNumberInput")
  .addEventListener("input", function (e) {
    const pureValue = formatMobileNumber(e.target);
    validatePhone(e.target, "newNumberMessage", pureValue);
    updateCopyButton();
  });

// کد ملی
document
  .getElementById("nationalIdInput")
  .addEventListener("input", function (e) {
    const pureValue = formatNationalId(e.target);
    validateNationalId(e.target, "nationalIdMessage", pureValue);

    // به‌روزرسانی نمایش استان و شهر بر اساس سه رقم اول
    updateProvinceDisplay(pureValue);

    updateCopyButton();
  });

// فقط متن (غیراز عدد) در فیلد نام
document.getElementById("nameInput").addEventListener("input", function (e) {
  let newValue = e.target.value.replace(/[0-9۰-۹]/g, "");
  if (e.target.value !== newValue) {
    e.target.value = newValue;
  }
  updateCopyButton();
});

// اعتبارسنجی شماره موبایل (مقدار ورودی عددی باید ۱۱ رقم و با ۰۹ شروع شود)
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

// فرمول اعتبارسنجی کد ملی ایران
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

// اعتبارسنجی کد ملی (عددی، ۱۰ رقمی، معتبر بودن)
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

// بررسی تکراری نبودن شماره‌ها
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

// فعال/غیرفعال شدن دکمه کپی (حالا با بررسی تکراری بودن)
function updateCopyButton() {
  const oldNumValid = validatePhone(
    document.getElementById("oldNumberInput"),
    "oldNumberMessage"
  );
  const newNumValid = validatePhone(
    document.getElementById("newNumberInput"),
    "newNumberMessage"
  );
  const nationalIdValid = validateNationalId(
    document.getElementById("nationalIdInput"),
    "nationalIdMessage"
  );
  const duplicateCheck = checkDuplicateNumbers();
  const btn = document.getElementById("copyBtn");
  if (
    (document.getElementById("oldNumberInput").value && !oldNumValid) ||
    (document.getElementById("newNumberInput").value && !newNumValid) ||
    (document.getElementById("nationalIdInput").value && !nationalIdValid) ||
    !duplicateCheck
  ) {
    btn.disabled = true;
  } else {
    btn.disabled = false;
  }
}

// تابع کپی با قالب دلخواه
function copyRequest() {
  const name = document.getElementById("nameInput").value.trim() || "نامشخص";
  const oldNumber =
    document.getElementById("oldNumberInput").value.replace(/[^\d]/g, "") ||
    "نامشخص";
  const nationalId =
    document.getElementById("nationalIdInput").value.replace(/[^\d]/g, "") ||
    "نامشخص";
  const newNumber =
    document.getElementById("newNumberInput").value.replace(/[^\d]/g, "") ||
    "نامشخص";

  const text =
    "با سلام\n" +
    "کاربر: " +
    name +
    "\n" +
    "با شماره موبایل: " +
    oldNumber +
    "\n" +
    "و کدملی: " +
    nationalId +
    "\n" +
    "درخواست تغییر شماره به: " +
    newNumber +
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

// پاک کردن فرم
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

// بررسی اولیه دکمه
updateCopyButton();

// لود فوتر از footer.html
fetch("footer.html")
  .then((res) => res.text())
  .then((data) => {
    document.getElementById("footer-placeholder").innerHTML = data;
  });

// ===== ۱. بارگذاری JSON شهری =====
let cityMapping = {};

fetch("data/NationalCode.json")
  .then((res) => res.json())
  .then((data) => {
    cityMapping = data;
  })
  .catch((err) => {
    console.error("خطا در بارگذاری cityMapping.json:", err);
  });

// ===== ۲. تابع به‌روزرسانی نمایش استان و شهر =====
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
    // در غیر این صورت فقط متن استان نامشخص
    provinceDiv.innerText = "❌ این کدملی در ثبت احوال وجود ندارد";
  }

  provinceDiv.style.display = "block";
}

// ===== تابع تکمیل خودکار فرم (به‌روز‌شده برای تشخیص اعداد فارسی) =====
function autoFillForm() {
  // ۱. مقدار متن ورودی را بگیریم و اعداد فارسی را به انگلیسی تبدیل کنیم
  const rawText = document.getElementById("autoFillInput").value.trim();
  if (!rawText) {
    alert("لطفاً ابتدا متن درخواست را وارد کنید.");
    return;
  }
  const text = normalizeDigits(rawText);

  // ۲. پاک‌سازی کامل مقادیر و پیام‌های قبلیِ فیلدها (به‌جز textarea#autoFillInput)
  //    -- شماره قبلی
  const oldInput = document.getElementById("oldNumberInput");
  oldInput.value = "";
  oldInput.classList.remove("input-error");
  const oldMsg = document.getElementById("oldNumberMessage");
  oldMsg.innerText = "";
  oldMsg.className = "input-message";

  //    -- شماره جدید
  const newInput = document.getElementById("newNumberInput");
  newInput.value = "";
  newInput.classList.remove("input-error");
  const newMsg = document.getElementById("newNumberMessage");
  newMsg.innerText = "";
  newMsg.className = "input-message";

  //    -- کدملی
  const nationalInput = document.getElementById("nationalIdInput");
  nationalInput.value = "";
  nationalInput.classList.remove("input-error");
  const nationalMsg = document.getElementById("nationalIdMessage");
  nationalMsg.innerText = "";
  nationalMsg.className = "input-message";

  //    -- باکس نمایش استان/شهر
  const provinceDiv = document.getElementById("provinceDisplay");
  provinceDiv.style.display = "none";
  provinceDiv.innerText = "";

  // ۳. استخراج همهٔ تطابق‌های شمارهٔ موبایل (الگوی 11 رقمی با پیش‌شماره 09)
  const phoneMatches = text.match(/09\d{9}/g) || [];

  // ۴. استخراج همهٔ تطابق‌های ۱۰ رقمی دقیق (به عنوان کدملی)
  const nationalIdMatches = text.match(/\b\d{10}\b/g) || [];

  // ۵. پر کردن فیلدهای شماره قبلی و جدید
  if (phoneMatches.length === 1) {
    // فقط یک شماره پیدا شده → در «شماره جدید» قرار می‌گیرد
    newInput.value = phoneMatches[0];
    formatMobileNumber(newInput);
    validatePhone(newInput, "newNumberMessage", phoneMatches[0]);
  } else if (phoneMatches.length >= 2) {
    // حداقل دو شماره پیدا شده → شمارهٔ اول در old و شمارهٔ دوم در new
    oldInput.value = phoneMatches[0];
    formatMobileNumber(oldInput);
    validatePhone(oldInput, "oldNumberMessage", phoneMatches[0]);

    newInput.value = phoneMatches[1];
    formatMobileNumber(newInput);
    validatePhone(newInput, "newNumberMessage", phoneMatches[1]);
  }
  // اگر اصلاً شماره‌ای نیافتیم که هیچ فیلدی پر نشود

  // ۶. پر کردن فیلد کدملی (در صورت وجود حداقل یک تطابق 10 رقمی)
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
  // اگر اصلاً کدملی‌ای نیافتیم که فیلد کدملی خالی بماند

  // ۷. در نهایت، وضعیت دکمهٔ «کپی متن درخواست» را مجدداً بررسی می‌کنیم
  updateCopyButton();
}
