// ========================================================================
// بخش ۱: توابع ابزاری مشترک (بدون تکرار)
// ========================================================================
const weekdays = [
  "یک‌شنبه",
  "دوشنبه",
  "سه‌شنبه",
  "چهارشنبه",
  "پنج‌شنبه",
  "جمعه",
  "شنبه",
];
const persianMonths = [
  "فروردین",
  "اردیبهشت",
  "خرداد",
  "تیر",
  "مرداد",
  "شهریور",
  "مهر",
  "آبان",
  "آذر",
  "دی",
  "بهمن",
  "اسفند",
];

function toJalali(gy, gm, gd) {
  const g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
  let jy = gy > 1600 ? 979 : 0;
  gy -= gy > 1600 ? 1600 : 621;
  const gy2 = gm > 2 ? gy + 1 : gy;
  let days =
    365 * gy +
    Math.floor((gy2 + 3) / 4) -
    Math.floor((gy2 + 99) / 100) +
    Math.floor((gy2 + 399) / 400) -
    80 +
    gd +
    g_d_m[gm - 1];
  jy += 33 * Math.floor(days / 12053);
  days %= 12053;
  jy += 4 * Math.floor(days / 1461);
  days %= 1461;
  if (days > 365) {
    jy += Math.floor((days - 1) / 365);
    days = (days - 1) % 365;
  }
  const jm =
    days < 186 ? 1 + Math.floor(days / 31) : 7 + Math.floor((days - 186) / 30);
  const jd = 1 + (days < 186 ? days % 31 : (days - 186) % 30);
  return [jy, jm, jd];
}

function toPersianDigits(str) {
  return str.toString().replace(/\d/g, (d) => "۰۱۲۳۴۵۶۷۸۹"[d]);
}

function getTodayPersianDate() {
  const today = new Date();
  const weekday = weekdays[today.getDay()];
  const [jy, jm, jd] = toJalali(
    today.getFullYear(),
    today.getMonth() + 1,
    today.getDate()
  );
  const pMonth = persianMonths[jm - 1];
  return toPersianDigits(`امروز ${weekday} ${jd} ${pMonth} ${jy}`);
}

function getCurrentTimePersian() {
  const now = new Date();
  const h = now.getHours().toString().padStart(2, "0");
  const m = now.getMinutes().toString().padStart(2, "0");
  return `ساعت ${toPersianDigits(h)}:${toPersianDigits(m)}`;
}

function pad(num) {
  return num.toString().padStart(2, "0");
}

function toPersianTimeStr(totalMin) {
  let h = Math.floor(totalMin / 60);
  let m = totalMin % 60;
  if (h > 0 && m > 0) {
    return `${toPersianDigits(h)} ساعت و ${toPersianDigits(m)} دقیقه`;
  } else if (h > 0) {
    return `${toPersianDigits(h)} ساعت`;
  } else {
    return `${toPersianDigits(m)} دقیقه`;
  }
}

// ========================================================================
// بخش ۲: توابع مخصوص ویجت‌ها (فقط در صورت وجود اجرا می‌شوند)
// ========================================================================

function setupToolsSearch() {
  const searchInput = document.getElementById("tools-search");
  if (!searchInput) return;
  const column = document.querySelector(".column-tools");
  const sections = [];
  let curTitle = null;

  Array.from(column.children).forEach((el) => {
    if (el.tagName === "H2") curTitle = el;
    if (el.tagName === "UL" && curTitle) {
      sections.push({ title: curTitle, list: el });
      curTitle = null;
    }
  });

  searchInput.addEventListener("input", function () {
    const value = searchInput.value.trim().toLowerCase();
    const showAll = !value;

    sections.forEach(({ title, list }) => {
      let hasVisible = false;
      Array.from(list.querySelectorAll("li")).forEach((li) => {
        const text = li.innerText.replace(/\s+/g, " ").toLowerCase();
        const matched = showAll || text.includes(value);
        li.style.display = matched ? "" : "none";
        if (matched) hasVisible = true;
      });
      title.style.display = hasVisible ? "" : "none";
      list.style.display = hasVisible ? "" : "none";
    });
  });
}

// ========================================================================
// بخش ۳: منطق اصلی برنامه (اجرا پس از بارگذاری کامل صفحه)
// ========================================================================
document.addEventListener("DOMContentLoaded", function () {
  // --- تابع بارگذاری هدر و فوتر ---
  const loadComponent = (path, placeholderId, callback) => {
    fetch(path)
      .then((res) => {
        if (!res.ok) throw new Error(`خطا در بارگذاری کامپوننت: ${path}`);
        return res.text();
      })
      .then((data) => {
        const placeholder = document.getElementById(placeholderId);
        if (placeholder) {
          placeholder.innerHTML = data;
          if (callback) callback();
        }
      })
      .catch((error) => console.error(error));
  };

  // بارگذاری فوتر
  loadComponent("/footer.html", "footer-placeholder");

  // بارگذاری هدر و اجرای منطق داخلی آن
  loadComponent("/header.html", "header-placeholder", () => {
    // این کد جایگزین تابع setupHeader می‌شود
    const dateElem = document.getElementById("today-date");
    if (dateElem) dateElem.innerText = getTodayPersianDate();

    const timeElem = document.getElementById("current-time");
    if (timeElem) {
      const updateTime = () => (timeElem.innerText = getCurrentTimePersian());
      updateTime();
      setInterval(updateTime, 60000); // به روز رسانی در هر دقیقه
    }

    const pageTitleElem = document.getElementById("page-title");
    if (pageTitleElem && document.title) {
      pageTitleElem.innerText = document.title;
    }
  });

  // --- اجرای توابع مخصوص صفحات (بر اساس وجود عنصر) ---

  if (document.getElementById("tools-search")) {
    setupToolsSearch();
  }
});
