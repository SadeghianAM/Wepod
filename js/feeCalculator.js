// ===== گرفتن المان‌ها =====
const amountInput = document.getElementById("amountInput");
const resultBox = document.getElementById("resultBox");
const radioButtons = document.querySelectorAll('input[name="transferMethod"]');

// ===== مپ تبدیل اعداد لاتین به فارسی =====
const persianDigits = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
function toPersianNumber(str) {
  return str.replace(/\d/g, (d) => persianDigits[d]);
}

// ===== قالب‌بندی با جداکننده سه‌رقمی (٫) و ارقام فارسی =====
function formatWithSeparators(num) {
  // ابتدا رشته عددی پاک تا بدون صفر پیشرو باشد
  let str = num.toString().replace(/^0+/, "");
  if (str === "") str = "0";
  // جداسازی هر سه رقم از انتها
  let parts = [];
  while (str.length > 3) {
    parts.unshift(str.slice(-3));
    str = str.slice(0, -3);
  }
  parts.unshift(str);
  const joined = parts.join("٫");
  // تبدیل رقم‌های لاتین به فارسی
  return toPersianNumber(joined);
}

// ===== پارس کردن مقدار ورودی (حذف جداکننده و تبدیل ارقام فارسی به لاتین) =====
function parseToNumber(persianStr) {
  // تبدیل ارقام فارسی به لاتین
  let latin = persianStr.replace(/[۰-۹]/g, (d) => persianDigits.indexOf(d));
  // حذف جداکننده هزارگان
  latin = latin.replace(/٫/g, "");
  // اگر رشته خالی یا غیرعددی باشد، NaN بر می‌گرداند
  const n = parseInt(latin, 10);
  return isNaN(n) ? NaN : n;
}

// ===== تعریف حداقل و حداکثر هر روش =====
const config = {
  shetabi: {
    min: 1000,
    max: 10_000_000,
  },
  paya: {
    min: 1000,
    max: 200_000_000,
  },
  satna: {
    min: 100_000_000,
    max: 10_000_000_000,
  },
  pol: {
    min: 1000,
    max: 50_000_000,
  },
};

// ===== تابع محاسبه کارمزد بر اساس قوانین =====
function calculateFee(method, amount) {
  switch (method) {
    case "shetabi": {
      // کارت به کارت (شتابی)
      // مبلغ باید بین min و max باشد (1000 تا 10,000,000)
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
      // اگر مبلغ ≤ 1_000_000 تومان:
      if (amount <= 1_000_000) {
        return { fee: 900 };
      }
      // برای هر 1_000_000 تومان اضافه 320 تومان
      const extraMillions = Math.floor((amount - 1_000_000) / 1_000_000);
      const fee = 900 + extraMillions * 320;
      return { fee };
    }

    case "paya": {
      // پایا
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
      // کارمزد = 0.01% مبلغ
      let rawFee = amount * 0.0001;
      // حداقل 3000 و حداکثر 75000
      let fee = Math.ceil(rawFee);
      if (fee < 3000) fee = 3000;
      if (fee > 75000) fee = 75000;
      return { fee };
    }

    case "satna": {
      // ساتنا
      if (amount < config.satna.min) {
        return {
          error: `حداقل مبلغ برای ساتنا ${formatWithSeparators(
            config.satna.min
          )} تومان است.`,
        };
      }
      if (amount > config.satna.max) {
        return {
          error: `حداکثر مبلغ برای ساتنا ${formatWithSeparators(
            config.satna.max
          )} تومان است.`,
        };
      }
      // کارمزد = 0.02% مبلغ، سقف 350000
      let rawFee = amount * 0.0002;
      let fee = Math.ceil(rawFee);
      if (fee > 350000) fee = 350000;
      return { fee };
    }

    case "pol": {
      // پل
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
      // کارمزد = 0.02% مبلغ، حداقل 5000
      let rawFee = amount * 0.0002;
      let fee = Math.ceil(rawFee);
      if (fee < 5000) fee = 5000;
      return { fee };
    }

    default:
      return { error: "روش انتقال نامعتبر است." };
  }
}

// ===== نمایش پیام در resultBox =====
function showResult(message, type) {
  resultBox.className = `result ${type}`;
  resultBox.style.display = "block";
  resultBox.textContent = message;
}

function clearResult() {
  resultBox.textContent = "";
  resultBox.className = "result";
  resultBox.style.display = "none";
}

// ===== هنگام تغییر ورودی یا نوع انتقال =====
function handleInputChange() {
  clearResult();

  // خواندن مقدار ورودی با حذف کاراکترهای غیرعددی
  const raw = amountInput.value;
  const numeric = parseToNumber(raw);

  // اگر خالی یا غیرعدد باشد
  if (raw.trim() === "") {
    showResult("لطفاً مبلغ را وارد کنید.", "warning");
    return;
  }
  if (isNaN(numeric)) {
    showResult("مبلغ وارد شده معتبر نیست.", "error");
    return;
  }

  // قالب‌بندی مجدد با جداکننده و اعداد فارسی
  amountInput.value = formatWithSeparators(numeric);

  // تشخیص روش انتخاب‌شده
  const selectedMethod = document.querySelector(
    'input[name="transferMethod"]:checked'
  ).value;

  // محاسبه کارمزد
  const result = calculateFee(selectedMethod, numeric);
  if (result.error) {
    showResult(result.error, "error");
  } else {
    const feeStr = formatWithSeparators(result.fee);
    showResult(`کارمزد انتقال: ${feeStr} تومان`, "success");
  }
}

// ===== اضافه کردن لیسنر =====
amountInput.addEventListener("input", () => {
  // اجازه بدهیم فقط اعداد فارسی / لاتین و جداکننده وارد شود
  // اما اصلی‌ترین کار: نمایش فوری
  handleInputChange();
});

// وقتی روش انتقال عوض شود
radioButtons.forEach((rb) => {
  rb.addEventListener("change", () => {
    handleInputChange();
  });
});

// ===== در بارگذاری اولیه صفحه =====
window.addEventListener("DOMContentLoaded", () => {
  // اگر بخواهیم مبلغ پیش‌فرض چیزی باشد (مثلاً خالی)
  amountInput.value = "";
  clearResult();
});
