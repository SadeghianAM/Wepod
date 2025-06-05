// ==== تبدیل اعداد انگلیسی به فارسی ====
function toPersianDigits(text) {
  return text.replace(/[0-9]/g, (d) => "۰۱۲۳۴۵۶۷۸۹"[d]);
}

// ==== تبدیل اعداد فارسی به انگلیسی ====
function normalizeDigits(text) {
  return text.replace(/[۰-۹]/g, (d) => "۰۱۲۳۴۵۶۷۸۹".indexOf(d).toString());
}

let triedSubmit = false;

// ==== استخراج مبلغ مسدودی ====
function extractAmount(text) {
  let match = text.match(/مبلغ مسدودی[\s\-:]*([^\n\r]*)/i);
  if (match && match[1] !== undefined) {
    const rialRegex = /(.*?ریال)/;
    const rialMatch = match[1].match(rialRegex);
    let result = "";
    if (rialMatch) {
      result = rialMatch[1].trim();
    } else {
      result = match[1].trim();
    }
    const hasDigit = /[0-9۰-۹]/.test(result);
    if (!hasDigit) return "-";
    return result;
  }
  return "-";
}

// ==== اعتبارسنجی فیلدها ====
function validateFields() {
  let ok = true;
  const blockType = document.getElementById("blockTypeInput");
  const reason = document.getElementById("reasonInput");
  const letterNo = document.getElementById("letterNoInput");
  const date = document.getElementById("dateInput");
  const amount = document.getElementById("amountInput");

  [blockType, reason, letterNo, date, amount].forEach((field) => {
    field.classList.remove("input-error");
    if (triedSubmit && !field.value.trim()) {
      field.classList.add("input-error");
      ok = false;
    }
    if (!field.value.trim()) ok = false;
  });

  if (
    date.value &&
    !/^14\d{2}\/\d{1,2}\/\d{1,2}$/.test(normalizeDigits(date.value.trim()))
  ) {
    ok = false;
    if (triedSubmit) date.classList.add("input-error");
  }

  document.getElementById("copyBtn").disabled = !ok;
  return ok;
}

// ==== تکمیل خودکار ====
function autoFillForm() {
  const rawText = document.getElementById("autoFillInput").value.trim();
  if (!rawText) {
    alert("لطفاً ابتدا متن مسدودی را وارد کنید.");
    return;
  }
  const text = normalizeDigits(rawText);

  // نوع مسدودی (خط اول که "مسدودی ..." است)
  const blockTypeMatch = text.match(/^(مسدودی[^\n]*)/m);
  document.getElementById("blockTypeInput").value = blockTypeMatch
    ? blockTypeMatch[1].replace("مسدودی", "").trim()
    : "";

  // علت مسدودی (فقط تا قبل از عنوان نامه/شماره نامه یا انتهای خط)
  const reasonMatch = text.match(/علت مسدودی *: *([^\n]*)/);
  let reason = reasonMatch ? reasonMatch[1].trim() : "";
  reason = reason.replace(/(عنوان نامه|شماره نامه)[\s\S]*/g, "").trim();
  document.getElementById("reasonInput").value = reason;

  // شماره نامه
  const letterNoMatch = text.match(/شماره نامه مسدودی *: *([^\n]*)/);
  document.getElementById("letterNoInput").value = toPersianDigits(
    letterNoMatch ? letterNoMatch[1].trim() : ""
  );

  // تاریخ
  const dateMatch = text.match(/تاریخ مسدودی *: *([^\n]*)/);
  document.getElementById("dateInput").value = toPersianDigits(
    dateMatch ? dateMatch[1].trim() : ""
  );

  // مبلغ مسدودی
  const amount = extractAmount(text);
  document.getElementById("amountInput").value = toPersianDigits(amount);

  // توضیحات
  const descMatch = text.match(/توضیحات *: *([\s\S]*)/);
  document.getElementById("descInput").value = descMatch
    ? descMatch[1].trim()
    : "";

  validateFields();
}

// ==== اعتبارسنجی با هر تغییر ====
[
  "blockTypeInput",
  "reasonInput",
  "letterNoInput",
  "dateInput",
  "amountInput",
  "descInput",
].forEach((id) => {
  document.getElementById(id).addEventListener("input", function () {
    // تبدیل خودکار اعداد به فارسی برای فیلدهای عددی
    if (["letterNoInput", "dateInput", "amountInput"].includes(id)) {
      this.value = toPersianDigits(this.value);
    }
    validateFields();
  });
});

// ==== پاک کردن فرم ====
function clearForm() {
  triedSubmit = false;
  [
    "autoFillInput",
    "blockTypeInput",
    "reasonInput",
    "letterNoInput",
    "dateInput",
    "amountInput",
    "descInput",
  ].forEach((id) => {
    document.getElementById(id).value = "";
    document.getElementById(id).classList.remove("input-error");
  });
  document.getElementById("copyResult").innerText = "";
  document.getElementById("copyResult").style.display = "none";
  validateFields();
}

// ==== کپی متن گزارش ====
function copyRequest() {
  triedSubmit = true;
  if (!validateFields()) return;

  const blockType =
    document.getElementById("blockTypeInput").value.trim() || "نامشخص";
  const reason =
    document.getElementById("reasonInput").value.trim() || "نامشخص";
  const letterNo =
    document.getElementById("letterNoInput").value.trim() || "نامشخص";
  const date = document.getElementById("dateInput").value.trim() || "نامشخص";
  const amount =
    document.getElementById("amountInput").value.trim() || "نامشخص";
  const desc = document.getElementById("descInput").value.trim() || "-";

  const text =
    "باسلام و احترام\n" +
    `کاربرگرامی، طی بررسی انجام شده حساب شما دارای مسدودی ${blockType} است\n` +
    `حساب شما به علت : ${reason} و طی شماره نامه : ${toPersianDigits(
      letterNo
    )} در تاریخ : ${toPersianDigits(date)} به مبلغ : ${toPersianDigits(
      amount
    )} مسدود شده است\n` +
    `توضیحات  ${desc}\n` +
    "باتشکر از همراهی شما";

  navigator.clipboard.writeText(text).then(
    () => {
      const res = document.getElementById("copyResult");
      res.innerText = "✅ متن گزارش با موفقیت کپی شد!";
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

// ==== اعتبارسنجی اولیه ====
validateFields();
