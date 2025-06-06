// ===== گرفتن المان‌ها =====
const amountInput = document.getElementById("amountInput");

// المان‌های مربوط به هر روش
const cardShetabi = document.getElementById("card-shetabi");
const cardPaya = document.getElementById("card-paya");
const cardSatna = document.getElementById("card-satna");
const cardPol = document.getElementById("card-pol");

const feeShetabi = document.getElementById("feeShetabi");
const feePaya = document.getElementById("feePaya");
const feeSatna = document.getElementById("feeSatna");
const feePol = document.getElementById("feePol");

// ===== مپ تبدیل اعداد لاتین به فارسی =====
const persianDigits = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
function toPersianNumber(str) {
  return str.replace(/\d/g, (d) => persianDigits[d]);
}

// ===== قالب‌بندی با جداکننده سه‌رقمی (٫) و ارقام فارسی =====
function formatWithSeparators(num) {
  let str = num.toString().replace(/^0+/, "");
  if (str === "") str = "0";
  let parts = [];
  while (str.length > 3) {
    parts.unshift(str.slice(-3));
    str = str.slice(0, -3);
  }
  parts.unshift(str);
  const joined = parts.join("٫");
  return toPersianNumber(joined);
}

// ===== پارس کردن مقدار ورودی (حذف جداکننده و تبدیل ارقام فارسی به لاتین) =====
function parseToNumber(persianStr) {
  let latin = persianStr.replace(/[۰-۹]/g, (d) => persianDigits.indexOf(d));
  latin = latin.replace(/٫/g, "");
  const n = parseInt(latin, 10);
  return isNaN(n) ? NaN : n;
}

// ===== تعریف حداقل و حداکثر هر روش =====
const config = {
  shetabi: { min: 1000, max: 10_000_000 },
  paya: { min: 1000, max: 200_000_000 },
  // برای ساتنا فقط حداقل تعریف شده؛ سقف حذف شده
  satna: { min: 100_000_000 },
  pol: { min: 1000, max: 50_000_000 },
};

// ===== تابع محاسبه کارمزد بر اساس قوانین =====
function calculateFee(method, amount) {
  switch (method) {
    case "shetabi": {
      if (amount < config.shetabi.min) {
        return {
          error: `حداقل مبلغ برای شتابی ${formatWithSeparators(
            config.shetabi.min
          )} تومان است.`,
        };
      }
      if (amount > config.shetabi.max) {
        return {
          error: `حداکثر مبلغ برای شتابی ${formatWithSeparators(
            config.shetabi.max
          )} تومان است.`,
        };
      }
      if (amount <= 1_000_000) {
        return { fee: 900 };
      }
      const extraMillions = Math.floor((amount - 1_000_000) / 1_000_000);
      const fee = 900 + extraMillions * 320;
      return { fee };
    }

    case "paya": {
      if (amount < config.paya.min) {
        return {
          error: `حداقل مبلغ برای پایا ${formatWithSeparators(
            config.paya.min
          )} تومان است.`,
        };
      }
      if (amount > config.paya.max) {
        return {
          error: `حداکثر مبلغ برای پایا ${formatWithSeparators(
            config.paya.max
          )} تومان است.`,
        };
      }
      // کارمزد = 0.01٪ مبلغ تراکنش
      let rawFee = amount * 0.0001;
      let fee = Math.ceil(rawFee);
      // حداقل 300 و حداکثر 7500
      if (fee < 300) fee = 300;
      if (fee > 7500) fee = 7500;
      return { fee };
    }

    case "satna": {
      if (amount < config.satna.min) {
        return {
          error: `حداقل مبلغ برای ساتنا ${formatWithSeparators(
            config.satna.min
          )} تومان است.`,
        };
      }
      // کارمزد = 0.02٪ مبلغ تراکنش، سقف 35000
      let rawFee = amount * 0.0002;
      let fee = Math.ceil(rawFee);
      if (fee > 35000) fee = 35000;
      return { fee };
    }

    case "pol": {
      if (amount < config.pol.min) {
        return {
          error: `حداقل مبلغ برای پل ${formatWithSeparators(
            config.pol.min
          )} تومان است.`,
        };
      }
      if (amount > config.pol.max) {
        return {
          error: `حداکثر مبلغ برای پل ${formatWithSeparators(
            config.pol.max
          )} تومان است.`,
        };
      }
      // کارمزد = 0.02٪ مبلغ تراکنش، حداقل 500
      let rawFee = amount * 0.0002;
      let fee = Math.ceil(rawFee);
      if (fee < 500) fee = 500;
      return { fee };
    }

    default:
      return { error: "روش انتقال نامعتبر است." };
  }
}

// ===== تابع برای به‌روزرسانی وضعیت هر کارت =====
function updateCardStatus(cardElement, feeElement, methodKey, amount) {
  const result = calculateFee(methodKey, amount);

  if (result.error) {
    cardElement.classList.add("disabled");
    feeElement.classList.remove("error", "warning");
    feeElement.classList.add("disabled-text");
    feeElement.textContent = "غیرقابل انتقال";
  } else {
    cardElement.classList.remove("disabled");
    feeElement.classList.remove("disabled-text");
    feeElement.classList.remove("warning");
    feeElement.classList.add("success");
    const feeStr = formatWithSeparators(result.fee);
    feeElement.textContent = `کارمزد: ${feeStr} تومان`;
  }
}

// ===== رویداد تغییر ورودی =====
function handleInputChange() {
  const raw = amountInput.value;

  // تبدیل ورودی به رشتهٔ لاتین بدون جداکننده
  let latinDigitsOnly = raw
    .replace(/[۰-۹]/g, (d) => persianDigits.indexOf(d))
    .replace(/٫/g, "");

  // ===== محدودیت: حداکثر 11 رقم =====
  if (latinDigitsOnly.length > 11) {
    // برش تا 11 رقم
    latinDigitsOnly = latinDigitsOnly.slice(0, 11);
  }

  // تبدیل رشتهٔ برش‌خورده به عدد (یا NaN)
  const numeric = parseInt(latinDigitsOnly, 10);
  const isValidNumber = !isNaN(numeric);

  // اگر خالی یا غیرعددی باشد
  if (latinDigitsOnly === "" || !isValidNumber) {
    if (latinDigitsOnly === "") {
      // وقتی کاملاً خالی باشد، همهٔ کارت‌ها بدون متن و بدون غیرفعال شدن
      [cardShetabi, cardPaya, cardSatna, cardPol].forEach((card) =>
        card.classList.remove("disabled")
      );
      [feeShetabi, feePaya, feeSatna, feePol].forEach(
        (feeEl) => (feeEl.textContent = "")
      );
      amountInput.value = ""; // اگر خالی است، هیچی ننویس
    } else {
      // اگر غیرعددی است، همهٔ کارت‌ها غیرفعال شوند و پیام «مبلغ نامعتبر است»
      [cardShetabi, cardPaya, cardSatna, cardPol].forEach((card) => {
        card.classList.add("disabled");
      });
      [feeShetabi, feePaya, feeSatna, feePol].forEach((feeEl) => {
        feeEl.textContent = "مبلغ نامعتبر است";
        feeEl.classList.add("disabled-text");
      });
      amountInput.value = "";
    }
    return;
  }

  // در غیر این صورت، numeric یک عدد معتبر با حداکثر 11 رقم است
  // فرمت با جداکننده و ارقام فارسی و جایگزینی در input
  amountInput.value = formatWithSeparators(numeric);

  // به‌روزرسانی وضعیت هر کارت
  updateCardStatus(cardShetabi, feeShetabi, "shetabi", numeric);
  updateCardStatus(cardPaya, feePaya, "paya", numeric);
  updateCardStatus(cardSatna, feeSatna, "satna", numeric);
  updateCardStatus(cardPol, feePol, "pol", numeric);
}

// ===== اضافه کردن لیسنر =====
amountInput.addEventListener("input", () => {
  handleInputChange();
});

// ===== هنگام بارگذاری اولیه صفحه =====
window.addEventListener("DOMContentLoaded", () => {
  amountInput.value = "";
  [cardShetabi, cardPaya, cardSatna, cardPol].forEach((card) =>
    card.classList.remove("disabled")
  );
  [feeShetabi, feePaya, feeSatna, feePol].forEach(
    (feeEl) => (feeEl.textContent = "")
  );
});
