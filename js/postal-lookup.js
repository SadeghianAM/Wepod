const postalInput = document.getElementById("postalInput");
const postalResult = document.getElementById("postalResult");

let postalData = [];

fetch("./data/postalcode.json")
  .then((res) => res.json())
  .then((data) => {
    // بارگذاری استان‌ها
    postalData = data.provinces;
    // مرتب‌سازی شهرها هر استان بر اساس طول بازه (موارد خاص‌تر اول)
    postalData.forEach((province) => {
      province.cities.sort((a, b) => {
        const rangeA =
          parseInt(a.postal_code_end, 10) - parseInt(a.postal_code_start, 10);
        const rangeB =
          parseInt(b.postal_code_end, 10) - parseInt(b.postal_code_start, 10);
        return rangeA - rangeB; // بازه‌های کوچک‌تر (جزئی‌تر) زودتر چک شوند
      });
    });
  })
  .catch((err) => {
    console.error("خطا در بارگذاری فایل JSON:", err);
    showResult("خطا در بارگذاری اطلاعات استان‌ها", "error");
  });

postalInput.addEventListener("input", () => {
  // Remove all non-digit characters
  const cleaned = postalInput.value.replace(/\D/g, "");

  // Format with spaces every 5 digits
  let formatted = "";
  for (let i = 0; i < cleaned.length; i++) {
    if (i > 0 && i % 5 === 0) {
      formatted += " ";
    }
    formatted += cleaned[i];
  }

  // Limit to 11 characters (10 digits + 1 space)
  postalInput.value = formatted.substring(0, 11);

  // Get the pure digits for checking
  const digitsOnly = cleaned.substring(0, 10);

  if (digitsOnly.length >= 5) {
    checkPostalCode(digitsOnly.substring(0, 5));
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
