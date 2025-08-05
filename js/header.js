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

function setupHeader(title) {
  const dateElem = document.getElementById("today-date");
  if (dateElem) {
    dateElem.innerText = getTodayPersianDate();
  }

  const timeElem = document.getElementById("current-time");
  if (timeElem) {
    const updateTime = () => {
      timeElem.innerText = getCurrentTimePersian();
    };
    updateTime();
    setInterval(updateTime, 60 * 1000);
  }

  if (title) {
    const pageTitleElem = document.getElementById("page-title");
    if (pageTitleElem) {
      pageTitleElem.innerText = title;
    }
  }
}

async function loadLayout() {
  try {
    const [headerRes, footerRes] = await Promise.all([
      fetch("/header.html"),
      fetch("/footer.html"),
    ]);

    const headerData = await headerRes.text();
    document.getElementById("header-placeholder").innerHTML = headerData;

    const pageTitle = document.querySelector("title")?.innerText;
    if (typeof setupHeader === "function") {
      setupHeader(pageTitle);
    }

    const footerData = await footerRes.text();
    document.getElementById("footer-placeholder").innerHTML = footerData;
  } catch (error) {
    console.error("Error loading layout components:", error);
  }
}

document.addEventListener("DOMContentLoaded", loadLayout);
