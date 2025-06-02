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

// تابع کپی بدون فاصله‌های اضافی
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
    "با کد ملی : " +
    nationalId +
    " قصد تغییر شماره موبایل\n" +
    "از  : " +
    oldNumber +
    "\n" +
    "به : " +
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
