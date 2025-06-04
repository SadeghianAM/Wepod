// المان‌های صفحه
const input = document.getElementById("ibanInput");
const resultBox = document.getElementById("resultBox");
const validationBox = document.getElementById("ibanValidationBox");

let ibanData = [];

fetch("./data/iban-data.json")
  .then((res) => res.json())
  .then((data) => {
    ibanData = data;
  })
  .catch((err) => {
    showResult("خطا در بارگذاری اطلاعات بانکی", "error");
    console.error(err);
  });

input.addEventListener("input", (e) => {
  let value = e.target.value
    .toUpperCase()
    .replace(/\s+/g, "")
    .replace(/[^A-Z0-9]/g, "");

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

  if (length === 26 && /^IR\d{24}$/.test(currentIban)) {
    validateIban(currentIban);
  } else if (length > 2) {
    showValidation("برای اعتبارسنجی شماره شبا را کامل وارد کنید", "warning");
  } else {
    clearValidation();
  }
});

input.addEventListener("paste", (e) => {
  e.preventDefault();
  let pastedValue = e.clipboardData
    .getData("text")
    .toUpperCase()
    .replace(/\s+/g, "")
    .replace(/[^A-Z0-9]/g, "");

  if (pastedValue.startsWith("IR")) {
    pastedValue = pastedValue.slice(2);
  }
  pastedValue = pastedValue.replace(/[^0-9]/g, "");
  input.value = "IR" + pastedValue;
  input.dispatchEvent(new Event("input"));
});

function showResult(message, type, logoName = null, bankName = null) {
  resultBox.className = `result ${type}`;
  resultBox.style.display = "block";
  resultBox.innerHTML = "";

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

function validateIban(iban) {
  const rearranged = iban.slice(4) + iban.slice(0, 4);
  const converted = rearranged.replace(
    /[A-Z]/g,
    (char) => char.charCodeAt(0) - 55
  );
  const remainder = BigInt(converted) % 97n;

  if (remainder === 1n) {
    showValidation("✅ فرمت شماره شبا صحیح است.", "success");
  } else {
    showValidation("❌ شماره شبا معتبر نیست.", "error");
  }
}

function showValidation(message, type) {
  validationBox.className = `result ${type}`;
  validationBox.style.display = "block";
  validationBox.textContent = message;
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
  input.value = "IR";
  input.focus();
  clearResult();
  clearValidation();
}
