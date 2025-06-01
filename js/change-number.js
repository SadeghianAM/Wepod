// فقط اعداد را بپذیر
function digitsOnly(e) {
  e.target.value = e.target.value.replace(/[^\d]/g, "");
}

document
  .getElementById("oldNumberInput")
  .addEventListener("input", function (e) {
    digitsOnly(e);
    validatePhone(e.target, "oldNumberMessage");
    updateCopyButton();
  });

document
  .getElementById("newNumberInput")
  .addEventListener("input", function (e) {
    digitsOnly(e);
    validatePhone(e.target, "newNumberMessage");
    updateCopyButton();
  });

document
  .getElementById("nationalIdInput")
  .addEventListener("input", function (e) {
    digitsOnly(e);
    validateNationalId(e.target, "nationalIdMessage");
    updateCopyButton();
  });

// اعتبارسنجی شماره موبایل
function validatePhone(input, msgId) {
  const val = input.value;
  const msgBox = document.getElementById(msgId);
  input.classList.remove("input-error");
  msgBox.className = "input-message";
  msgBox.innerText = "";
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
function validateNationalId(input, msgId) {
  const val = input.value;
  const msgBox = document.getElementById(msgId);
  input.classList.remove("input-error");
  msgBox.className = "input-message";
  msgBox.innerText = "";
  if (val && !/^\d{10}$/.test(val)) {
    msgBox.innerText = "کد ملی باید ۱۰ رقم باشد.";
    msgBox.classList.add("error");
    input.classList.add("input-error");
    return false;
  }
  if (val && !isValidIranianNationalCode(val)) {
    msgBox.innerText = "کد ملی وارد شده معتبر نیست.";
    msgBox.classList.add("error");
    input.classList.add("input-error");
    return false;
  }
  if (val && isValidIranianNationalCode(val)) {
    msgBox.innerText = "کد ملی معتبر است.";
    msgBox.classList.add("success");
  }
  return true;
}

// فعال/غیرفعال شدن دکمه کپی
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
  const btn = document.getElementById("copyBtn");
  if (
    (document.getElementById("oldNumberInput").value && !oldNumValid) ||
    (document.getElementById("newNumberInput").value && !newNumValid) ||
    (document.getElementById("nationalIdInput").value && !nationalIdValid)
  ) {
    btn.disabled = true;
  } else {
    btn.disabled = false;
  }
}

document
  .getElementById("nameInput")
  .addEventListener("input", updateCopyButton);

function copyRequest() {
  const name = document.getElementById("nameInput").value.trim() || "نامشخص";
  const oldNumber =
    document.getElementById("oldNumberInput").value.trim() || "نامشخص";
  const nationalId =
    document.getElementById("nationalIdInput").value.trim() || "نامشخص";
  const newNumber =
    document.getElementById("newNumberInput").value.trim() || "نامشخص";
  const text = `با سلام
  کاربر: ${name}
  با کد ملی: ${nationalId}
  قصد تغییر شماره موبایل از : ${oldNumber}
  به: ${newNumber} را دارند.`;

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
