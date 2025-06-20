// ===== گرفتن المان‌های ورودی و جعبه نمایش نتیجه =====
const input = document.getElementById("ibanInput");
const resultBox = document.getElementById("resultBox");
const validationBox = document.getElementById("ibanValidationBox");

let ibanData = []; // آرایه برای ذخیره اطلاعات بانک‌ها بر اساس کد شبای آن‌ها

// ===== بارگذاری دیتابیس بانک‌ها از فایل JSON =====
fetch("./data/iban-data.json")
  .then((res) => res.json())
  .then((data) => {
    ibanData = data;
  })
  .catch((err) => {
    showResult("خطا در بارگذاری اطلاعات بانکی", "error");
    console.error(err);
  });

// ===== رویداد input برای هر بار تغییر شماره شبا توسط کاربر =====
input.addEventListener("input", (e) => {
  // پاکسازی و تصحیح ورودی:
  // فقط IR و اعداد (هر چیز غیرمجاز حذف شود)
  let value = e.target.value
    .toUpperCase()
    .replace(/\s+/g, "")
    .replace(/[^A-Z0-9]/g, "");

  // همیشه با IR شروع شود
  if (!value.startsWith("IR")) {
    value = "IR" + value.replace(/[^0-9]/g, "");
  } else {
    value = "IR" + value.slice(2).replace(/[^0-9]/g, "");
  }
  e.target.value = value;

  const currentIban = value;
  const length = currentIban.length;

  clearValidation();

  let bankMatched = false;

  // ===== تشخیص بانک از روی کد 3 رقمی بین رقم 4 تا 6 =====
  if (length >= 7) {
    const bankCode = currentIban.substring(4, 7);
    const match = ibanData.find((entry) => entry.code === bankCode);

    if (match) {
      bankMatched = true;
      showResult(
        `این شماره شبا متعلق به ${match.name} است.`,
        "success",
        match.logo,
        match.name
      );
    } else {
      showResult("بانکی با این شماره شبا پیدا نشد.", "warning");
    }
  } else {
    clearResult();
    return;
  }

  if (!bankMatched) {
    return;
  }

  // ===== اعتبارسنجی فرمت شبا (در صورتی که کامل وارد شده باشد) =====
  if (length === 26 && /^IR\d{24}$/.test(currentIban)) {
    validateIban(currentIban);
  } else if (length > 2) {
    showValidation("برای اعتبارسنجی شماره شبا را کامل وارد کنید", "warning");
  } else {
    clearValidation();
  }
});

// ===== پشتیبانی از چسباندن شماره شبا با فرمت‌های مختلف (paste) =====
input.addEventListener("paste", (e) => {
  e.preventDefault();
  let pastedValue = e.clipboardData
    .getData("text")
    .toUpperCase()
    .replace(/\s+/g, "")
    .replace(/[^A-Z0-9]/g, "");

  // حذف IR ابتدای مقدار چسبانده شده (در صورت وجود)
  if (pastedValue.startsWith("IR")) {
    pastedValue = pastedValue.slice(2);
  }
  pastedValue = pastedValue.replace(/[^0-9]/g, "");
  input.value = "IR" + pastedValue;
  input.dispatchEvent(new Event("input"));
});

// ===== نمایش نتیجه بانک یا پیام خطا/هشدار =====
function showResult(message, type, logoName = null, bankName = null) {
  resultBox.className = `result ${type}`;
  resultBox.style.display = "block";
  resultBox.innerHTML = "";

  // نمایش لوگوی بانک در حالت موفقیت
  if (logoName && bankName) {
    const img = document.createElement("img");
    img.src = `./assets/logo/${logoName}.svg`;
    img.alt = "لوگوی بانک";
    img.style.width = "24px";
    img.style.height = "24px";
    img.style.marginLeft = "0.5rem";
    img.onerror = () => (img.style.display = "none");

    resultBox.appendChild(document.createTextNode("این شماره شبا متعلق به "));
    resultBox.appendChild(img);
    resultBox.appendChild(document.createTextNode(`${bankName} است.`));
  } else {
    resultBox.textContent = message;
  }
}

// ===== اعتبارسنجی صحت فرمت شماره شبا بر اساس استاندارد IBAN =====
function validateIban(iban) {
  // جابجایی ۴ کاراکتر اول به انتهای رشته
  const rearranged = iban.slice(4) + iban.slice(0, 4);
  // تبدیل حروف به اعداد (A=10 ... Z=35)
  const converted = rearranged.replace(
    /[A-Z]/g,
    (char) => char.charCodeAt(0) - 55
  );
  // گرفتن باقیمانده تقسیم بر ۹۷
  const remainder = BigInt(converted) % 97n;

  if (remainder === 1n) {
    showValidation("✅ فرمت شماره شبا صحیح است.", "success");
  } else {
    showValidation("❌ شماره شبا معتبر نیست.", "error");
  }
}

// ===== نمایش پیام اعتبارسنجی شبا =====
function showValidation(message, type) {
  validationBox.className = `result ${type}`;
  validationBox.style.display = "block";
  validationBox.textContent = message;
}

// ===== پاک کردن جعبه نمایش نتیجه بانک =====
function clearResult() {
  resultBox.textContent = "";
  resultBox.className = "result";
  resultBox.style.display = "none";
}

// ===== پاک کردن جعبه نمایش اعتبارسنجی شبا =====
function clearValidation() {
  validationBox.textContent = "";
  validationBox.className = "result";
  validationBox.style.display = "none";
}

// ===== پاک کردن کامل فرم و بازنشانی به مقدار اولیه =====
function clearForm() {
  input.value = "IR";
  input.focus();
  clearResult();
  clearValidation();
}
