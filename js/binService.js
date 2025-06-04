const resultBox = document.getElementById("resultBox");
const validationBox = document.getElementById("validationBox");

let binData = [];

const requireFullBin = [
  "502229", // ویپاد / پاسارگاد
  "621986", // بلوبانک / سامان
];

fetch("./data/bin-data.json")
  .then((res) => res.json())
  .then((data) => {
    binData = data.sort((a, b) => b.bin.length - a.bin.length); // مرتب‌سازی از طولانی به کوتاه
  })
  .catch((err) => {
    showResult("خطا در بارگذاری اطلاعات بانکی", "error");
    console.error(err);
  });

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

window.addEventListener("clear-both", () => {
  clearResult();
  clearValidation();
});

window.addEventListener("bin-check", (e) => {
  const cleaned = e.detail; // فقط ارقام (رشته)
  const length = cleaned.length;

  clearValidation();

  let bankMatched = false;

  if (length >= 6) {
    const bin6 = cleaned.substring(0, 6);
    const bin7 = cleaned.substring(0, 7);
    const bin8 = cleaned.substring(0, 8);

    const requires8 = requireFullBin.includes(bin6);

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

  if (!bankMatched) {
    return;
  }

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

function showResult(message, type, logoName = null, bankName = null) {
  resultBox.className = `result ${type}`;
  resultBox.style.display = "block";
  resultBox.innerHTML = "";

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

function clearResult() {
  resultBox.textContent = "";
  resultBox.className = "result";
  resultBox.style.display = "none";
}

function clearValidation() {
  validationBox.textContent = "";
  validationBox.className = "result";
  validationBox.style.display = "none";
}

function clearForm() {
  const input = document.getElementById("binInput");
  input.value = "";
  input.focus();
  clearResult();
  clearValidation();
}
