const postalInput = document.getElementById("postalInput");
const postalResult = document.getElementById("postalResult");

let postalData = [];

fetch("./data/postalcode.json")
  .then((res) => res.json())
  .then((data) => {
    postalData = data.provinces;
  })
  .catch((err) => {
    console.error("خطا در بارگذاری فایل JSON:", err);
    showResult("خطا در بارگذاری اطلاعات استان‌ها", "error");
  });

postalInput.addEventListener("input", () => {
  const cleaned = postalInput.value.replace(/\D/g, "").substring(0, 10);
  postalInput.value = cleaned;

  if (cleaned.length >= 5) {
    checkPostalCode(cleaned.substring(0, 5));
  } else {
    clearResult();
  }
});

function checkPostalCode(code) {
  let matched = null;

  for (const province of postalData) {
    for (const city of province.cities) {
      const start = parseInt(city.postal_code_start, 10);
      const end = parseInt(city.postal_code_end, 10);
      const input = parseInt(code, 10);

      if (input >= start && input <= end) {
        matched = {
          province: province.province,
          city: city.name,
        };
        break;
      }
    }
    if (matched) break;
  }

  if (matched) {
    showResult(
      `کدپستی بالا مربوط به استان ${matched.province} و منطقه پستی ${matched.city} می‌باشد.`,
      "success"
    );
  } else {
    showResult(
      "کدپستی وارد شده معتبر نیست یا در پایگاه داده یافت نشد.",
      "error"
    );
  }
}

function showResult(message, type) {
  postalResult.textContent = message;
  postalResult.className = `result ${type}`;
  postalResult.style.display = "block";
}

function clearResult() {
  postalResult.textContent = "";
  postalResult.className = "result";
  postalResult.style.display = "none";
}

function clearForm() {
  postalInput.value = "";
  clearResult();
  postalInput.focus();
}
