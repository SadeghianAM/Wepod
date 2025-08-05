// گرفتن ارجاع به جعبه‌های نمایش نتایج
const resultBox = document.getElementById("resultBox");
const validationBox = document.getElementById("validationBox");

// متغیر برای ذخیره داده‌های bin بانک‌ها
let binData = [];

// لیست binهایی که برای تشخیص دقیق‌تر، 8 رقم اول لازم دارند (مثل ویپاد یا بلوبانک)
const requireFullBin = [
  "502229", // ویپاد / پاسارگاد
  "621986", // بلوبانک / سامان
];

// بارگذاری داده‌های bin بانک از فایل JSON
fetch("./data/bin-data.json")
  .then((res) => res.json())
  .then((data) => {
    // مرتب‌سازی binها بر اساس طول، تا binهای بلندتر اول بررسی شوند (برای موارد خاص)
    binData = data.sort((a, b) => b.bin.length - a.bin.length);
  })
  .catch((err) => {
    showResult("خطا در بارگذاری اطلاعات بانکی", "error");
    console.error(err);
  });

// اعتبارسنجی شماره کارت با الگوریتم لوهان (Luhn)
function validateLuhn(number) {
  let sum = 0;
  for (let i = 0; i < number.length; i++) {
    let digit = parseInt(number[i], 10);
    if (i % 2 === 0) {
      digit *= 2;
      if (digit > 9) digit -= 9;
    }
    sum += digit;
  }
  return sum % 10 === 0;
}

// پاک کردن نتایج نمایش داده شده
function clearResult() {
  resultBox.textContent = "";
  resultBox.className = "result";
  resultBox.style.display = "none";
}

// پاک کردن اعتبارسنجی
function clearValidation() {
  validationBox.textContent = "";
  validationBox.className = "result";
  validationBox.style.display = "none";
}

// نمایش نتیجه (موفقیت/خطا/هشدار) در جعبه نتیجه
function showResult(message, type, logoName = null, bankName = null) {
  resultBox.className = `result ${type}`;
  resultBox.style.display = "block";
  resultBox.innerHTML = "";

  // اگر بانک با موفقیت پیدا شد، لوگوی بانک هم نمایش داده شود
  if (logoName && bankName && type === "success" && message.includes("متعلق")) {
    const img = document.createElement("img");
    img.src = `./assets/logo/${logoName}.svg`;
    img.alt = "لوگوی بانک";
    img.onerror = () => (img.style.display = "none");
    resultBox.appendChild(document.createTextNode("این کارت متعلق به "));
    resultBox.appendChild(img);
    resultBox.appendChild(document.createTextNode(`${bankName} است.`));
  } else {
    resultBox.textContent = message;
  }
}

// پاکسازی فرم و فوکوس روی ورودی کارت
function clearForm() {
  const input = document.getElementById("binInput");
  input.value = "";
  input.focus();
  clearResult();
  clearValidation();
}

// شنیدن رویداد سفارشی برای پاک کردن جعبه‌ها (مثلاً وقتی ورودی کمتر از ۶ رقم شد)
window.addEventListener("clear-both", () => {
  clearResult();
  clearValidation();
});

// شنیدن رویداد سفارشی بررسی bin کارت (زمانی که ۶ رقم یا بیشتر وارد شد)
window.addEventListener("bin-check", (e) => {
  const cleaned = e.detail; // رشته فقط شامل ارقام
  const length = cleaned.length;

  clearValidation();

  let bankMatched = false;

  // بررسی حداقل ۶ رقم اول
  if (length >= 6) {
    const bin6 = cleaned.substring(0, 6);
    const bin7 = cleaned.substring(0, 7);
    const bin8 = cleaned.substring(0, 8);

    const requires8 = requireFullBin.includes(bin6);

    // اگر این bin نیاز به ۸ رقم دارد
    if (requires8) {
      if (length === 6) {
        showResult(
          "برای تشخیص دقیق‌تر، لطفاً دو رقم دیگر وارد کنید.",
          "warning"
        );
      } else if (length === 7) {
        showResult(
          "برای تشخیص دقیق‌تر، لطفاً یک رقم دیگر وارد کنید.",
          "warning"
        );
      } else {
        // مقایسه با binهای موجود
        const match = binData.find((entry) => bin8.startsWith(entry.bin));
        if (match) {
          bankMatched = true;
          showResult(
            `این کارت متعلق به ${match.name} است.`,
            "success",
            match.logo,
            match.name
          );
        } else {
          showResult("بانکی با این شماره پیدا نشد.", "warning");
        }
      }
    } else {
      // مقایسه با binهای معمولی (۶ رقم)
      const match = binData.find((entry) => bin6.startsWith(entry.bin));
      if (match) {
        bankMatched = true;
        showResult(
          `این کارت متعلق به ${match.name} است.`,
          "success",
          match.logo,
          match.name
        );
      } else {
        showResult("بانکی با این شماره پیدا نشد.", "warning");
      }
    }
  }

  // اگر بانکی پیدا نشد، ادامه نمی‌دهیم
  if (!bankMatched) {
    return;
  }

  // اعتبارسنجی طول کارت و سپس اعتبارسنجی الگوریتم لوهان
  if (length < 16) {
    validationBox.className = "result warning";
    validationBox.style.display = "block";
    validationBox.textContent = "برای اعتبارسنجی شماره کارت را کامل وارد کنید";
  } else {
    const isValid = validateLuhn(cleaned);
    if (isValid) {
      validationBox.className = "result success";
      validationBox.style.display = "block";
      validationBox.textContent = "✅ شماره کارت معتبر است.";
    } else {
      validationBox.className = "result error";
      validationBox.style.display = "block";
      validationBox.textContent = "❌ شماره کارت نامعتبر است.";
    }
  }
});

// ======= مدیریت ورودی کاربر و فرمت‌دهی شماره کارت =======

// هر بار که کاربر شماره وارد می‌کند:
// - فقط ارقام نگه داشته می‌شود
// - هر ۴ رقم فاصله ایجاد می‌شود
// - بعد از ۶ رقم اول، رویداد بررسی bin ارسال می‌شود
const input = document.getElementById("binInput");

input.addEventListener("input", (e) => {
  const rawValue = e.target.value.replace(/\D/g, "").substring(0, 16);
  const groups = rawValue.match(/.{1,4}/g) || [];
  e.target.value = groups.join(" ");
  const cleaned = rawValue;

  if (cleaned.length >= 6) {
    const event = new CustomEvent("bin-check", { detail: cleaned });
    window.dispatchEvent(event);
  } else {
    const clearEvent = new CustomEvent("clear-both");
    window.dispatchEvent(clearEvent);
  }
});
