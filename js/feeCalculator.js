// ===== گرفتن المان‌ها =====
const amountInput = document.getElementById("amountInput");
const amountWordsDiv = document.getElementById("amountWords");

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

// ===== تبدیل عدد (ریال) به حروف (فارسی) =====
// نقشه برای اعداد ۰ تا ۱۹
const persianNums = {
  0: "صفر",
  1: "یک",
  2: "دو",
  3: "سه",
  4: "چهار",
  5: "پنج",
  6: "شش",
  7: "هفت",
  8: "هشت",
  9: "نه",
  10: "ده",
  11: "یازده",
  12: "دوازده",
  13: "سیزده",
  14: "چهارده",
  15: "پانزده",
  16: "شانزده",
  17: "هفده",
  18: "هجده",
  19: "نوزده",
};

// نقشه برای دهگان (۲۰، ۳۰، …، ۹۰)
const persianTens = {
  20: "بیست",
  30: "سی",
  40: "چهل",
  50: "پنجاه",
  60: "شصت",
  70: "هفتاد",
  80: "هشتاد",
  90: "نود",
};

// نقشه برای صدگان (۱۰۰، ۲۰۰، …، ۹۰۰)
const persianHundreds = {
  100: "صد",
  200: "دویست",
  300: "سیصد",
  400: "چهارصد",
  500: "پانصد",
  600: "ششصد",
  700: "هفتصد",
  800: "هشتصد",
  900: "نهصد",
};

// تابعی که عدد سه‌رقمی (۰ تا ۹۹۹) را به حروف فارسی تبدیل می‌کند
function threeDigitToWords(n) {
  let str = "";
  if (n >= 100) {
    const h = Math.floor(n / 100) * 100;
    str += persianHundreds[h];
    n %= 100;
    if (n) str += " و ";
  }
  if (n >= 20) {
    const t = Math.floor(n / 10) * 10;
    str += persianTens[t];
    n %= 10;
    if (n) str += " و ";
  }
  if (n > 0 && n < 20) {
    str += persianNums[n];
  }
  return str;
}

// مقیاس‌های هزار به بالا
const scales = ["", "هزار", "میلیون", "میلیارد", "تریلیون", "کوادریلیون"];

// تابع اصلی برای تبدیل هر عدد صحیح (مثلاً 123456789) به حروف فارسی + واژه‌ی "ریال"
function convertNumberToPersianWords(num) {
  if (num === 0) return persianNums[0] + " ریال";

  let result = "";
  let scaleIdx = 0;

  while (num > 0) {
    const chunk = num % 1000;
    if (chunk) {
      const chunkWords = threeDigitToWords(chunk);
      const scaleWord = scales[scaleIdx];
      const section = chunkWords + (scaleWord ? " " + scaleWord : "");
      if (result) {
        result = section + " و " + result;
      } else {
        result = section;
      }
    }
    num = Math.floor(num / 1000);
    scaleIdx++;
  }

  return result + " ریال";
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

// ===== به‌روزرسانی متنِ مبلغ به حروف (ریال) =====
function updateAmountInWords(rialAmount) {
  if (isNaN(rialAmount) || rialAmount <= 0) {
    amountWordsDiv.textContent = "";
    return;
  }
  const words = convertNumberToPersianWords(rialAmount);
  amountWordsDiv.textContent = words;
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
      updateAmountInWords(NaN); // پاک کردن متنِ حروف
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
      updateAmountInWords(NaN);
    }
    return;
  }

  // در غیر این صورت، numeric یک عدد معتبر با حداکثر 11 رقم است
  // فرمت با جداکننده و ارقام فارسی و جایگزینی در input
  amountInput.value = formatWithSeparators(numeric);

  // به‌روزکردن مبلغ به حروف (ریال): هر «تومان» = ۱۰ ریال
  const rialAmount = numeric * 10;
  updateAmountInWords(rialAmount);

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
  updateAmountInWords(NaN);
});
